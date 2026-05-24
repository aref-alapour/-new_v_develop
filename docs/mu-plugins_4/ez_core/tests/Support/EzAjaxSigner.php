<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Tests\Support;

/**
 * HMAC signing for EZ AJAX Gateway (shared by sign.php, procedural smoke, and Pest).
 */
final class EzAjaxSigner
{
    public string $canonical;

    public string $signature;

    public int $expires_at;

    public int $timestamp;

    public string $nonce;

    /** @var array<string, string> */
    public array $headers;

    public function __construct(
        public readonly string $action,
        public readonly string $method,
        public readonly string $url,
        public readonly string $body,
        public readonly string $secret,
        public readonly string $kid = 'v1',
        public readonly string $client_kind = 'web-anon',
        public readonly string $client_id = '',
        public readonly int $ttl = 300,
    ) {
    }

    public static function randomClientId(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0F) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3F) | 0x80);
        $hex = bin2hex($bytes);

        return substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-'
            . substr($hex, 12, 4) . '-' . substr($hex, 16, 4) . '-'
            . substr($hex, 20, 12);
    }

    /**
     * @return self Signed signer ready for HTTP requests.
     */
    public static function forAction(
        string $action,
        string $method,
        string $url,
        string $body,
        string $secret,
    ): self {
        return (new self($action, $method, $url, $body, $secret))->sign();
    }

    public function sign(): self
    {
        $client_id = '' !== $this->client_id ? $this->client_id : self::randomClientId();
        $expires_at = time() + $this->ttl;
        $timestamp = time();
        $nonce = bin2hex(random_bytes(16));
        $body_hash = hash('sha256', $this->body, false);

        $path = parse_url($this->url, PHP_URL_PATH);
        if (! is_string($path) || '' === $path) {
            $path = '/ajax';
        }

        $sub_secret_raw = hash_hmac(
            'sha256',
            $this->kid . '|' . $client_id . '|' . (string) $expires_at,
            $this->secret,
            true
        );
        $this->canonical = 'v1|' . $this->method . '|' . $path . '|' . $this->action . '|'
            . $client_id . '|' . $this->client_kind . '|' . (string) $timestamp . '|'
            . $nonce . '|' . $body_hash;
        $signature_raw = hash_hmac('sha256', $this->canonical, $sub_secret_raw, true);
        $this->signature = rtrim(strtr(base64_encode($signature_raw), '+/', '-_'), '=');
        $this->expires_at = $expires_at;
        $this->timestamp = $timestamp;
        $this->nonce = $nonce;
        $this->headers = [
            'X-EZ-Action' => $this->action,
            'X-EZ-Kid' => $this->kid,
            'X-EZ-Client-Id' => $client_id,
            'X-EZ-Client-Kind' => $this->client_kind,
            'X-EZ-Sub-Expires' => (string) $expires_at,
            'X-EZ-Timestamp' => (string) $timestamp,
            'X-EZ-Nonce' => $nonce,
            'X-EZ-Signature' => $this->signature,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json, text/html;q=0.9',
        ];

        return $this;
    }

    public function withBadSignature(): self
    {
        $this->headers['X-EZ-Signature'] = 'invalid-signature-for-test';

        return $this;
    }

    public function toCurlCommand(): string
    {
        $curl = 'curl -sS -w "\\nHTTP %{http_code} | TTFB %{time_starttransfer}s | total %{time_total}s\\n" \\' . PHP_EOL;
        $curl .= '  -X ' . $this->method . ' \\' . PHP_EOL;
        foreach ($this->headers as $name => $value) {
            $curl .= '  -H ' . escapeshellarg($name . ': ' . $value) . ' \\' . PHP_EOL;
        }
        if ('GET' !== $this->method) {
            $curl .= '  --data ' . escapeshellarg($this->body) . ' \\' . PHP_EOL;
        }
        $curl .= '  ' . escapeshellarg($this->url);

        return $curl;
    }
}

<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Tests\Support;

/**
 * Portable HTTP client for gateway smoke tests (stream_context, no shell curl).
 */
final class GatewayHttpClient
{
    /**
     * @param array<string, string> $headers
     * @return array{status:int, body:string, headers:array<string, string>, error:?string}
     */
    public function post(string $url, string $body, array $headers, int $timeout = 8): array
    {
        $header_lines = [];
        foreach ($headers as $name => $value) {
            $header_lines[] = $name . ': ' . $value;
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $header_lines),
                'content' => $body,
                'timeout' => $timeout,
                'ignore_errors' => true,
            ],
        ]);

        /** @var array<int, string> $http_response_header */
        $http_response_header = [];
        $raw = @file_get_contents($url, false, $context);
        if (false === $raw) {
            $err = error_get_last();

            return [
                'status' => 0,
                'body' => '',
                'headers' => [],
                'error' => $err['message'] ?? 'HTTP request failed',
            ];
        }

        $status = 0;
        $parsed = [];
        if (isset($http_response_header) && is_array($http_response_header)) {
            foreach ($http_response_header as $line) {
                if (preg_match('#^HTTP/\S+\s+(\d{3})#', $line, $m)) {
                    $status = (int) $m[1];
                } elseif (str_contains($line, ':')) {
                    [$k, $v] = explode(':', $line, 2);
                    $parsed[strtolower(trim($k))] = trim($v);
                }
            }
        }

        return [
            'status' => $status,
            'body' => (string) $raw,
            'headers' => $parsed,
            'error' => null,
        ];
    }
}

<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Tests;

use EscapeZoom\Core\Core\Bootstrap;
use EscapeZoom\Core\Database\CapsuleBoot;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase
{
    private static bool $environmentBooted = false;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootTestEnvironment();
    }

    protected static function bootTestEnvironment(): void
    {
        if (self::$environmentBooted) {
            return;
        }

        $ezCoreDir = dirname(__DIR__);
        $muPluginsDir = dirname($ezCoreDir);
        $wpRoot = dirname($muPluginsDir, 2);
        $wpConfig = $wpRoot . '/wp-config.php';

        if (! is_readable($wpConfig)) {
            return;
        }

        $secretsBootstrap = $muPluginsDir . '/ez-ajax-gateway/secrets-bootstrap.php';
        if (is_readable($secretsBootstrap)) {
            require_once $secretsBootstrap;
            if (function_exists('ez_ajax_gateway_secrets_bootstrap')) {
                ez_ajax_gateway_secrets_bootstrap($wpConfig);
            }
        }

        $autoload = $ezCoreDir . '/vendor/autoload.php';
        if (is_readable($autoload)) {
            require_once $autoload;
        }

        if (class_exists(Bootstrap::class)) {
            Bootstrap::bootDataLayerOnly();
        }

        self::$environmentBooted = true;
    }

    protected function sharedSecret(): string
    {
        if (defined('EZ_AJAX_SHARED_SECRET') && is_string(EZ_AJAX_SHARED_SECRET) && '' !== EZ_AJAX_SHARED_SECRET) {
            return EZ_AJAX_SHARED_SECRET;
        }

        return '';
    }

    protected function gatewayBaseUrl(): string
    {
        $env = getenv('EZ_AJAX_TEST_BASE_URL');

        return is_string($env) && '' !== $env ? $env : 'http://wo.escapezoom.local/ajax';
    }

    protected function skipUnlessDb(string $message = 'Database / Capsule not available'): void
    {
        if (! extension_loaded('pdo_mysql')) {
            $this->markTestSkipped('pdo_mysql extension not loaded — run tests inside Docker PHP');
        }

        if (! self::isDbHostReachable()) {
            $this->markTestSkipped('Database host not reachable from CLI — run inside Docker');
        }

        if (! CapsuleBoot::isBooted()) {
            $this->markTestSkipped($message);
        }
    }

    protected function skipUnlessSecret(string $message = 'EZ_AJAX_SHARED_SECRET not defined'): void
    {
        if ('' === $this->sharedSecret()) {
            $this->markTestSkipped($message);
        }
    }

    protected function skipUnlessHttp(string $message = 'HTTP gateway base URL not reachable'): void
    {
        if (! self::isHttpBaseReachable($this->gatewayBaseUrl(), 3.0)) {
            $this->markTestSkipped($message);
        }
    }

    protected static function isDbHostReachable(float $timeoutSeconds = 3.0): bool
    {
        if (! defined('DB_HOST')) {
            return false;
        }

        $host = (string) DB_HOST;
        $port = 3306;
        if (str_contains($host, ':')) {
            [$host, $portStr] = explode(':', $host, 2);
            $port = (int) $portStr;
        } elseif (str_contains($host, '/')) {
            $host = explode('/', $host, 2)[0];
        }

        $fp = @fsockopen($host, $port > 0 ? $port : 3306, $errno, $errstr, $timeoutSeconds);

        if (is_resource($fp)) {
            fclose($fp);

            return true;
        }

        return false;
    }

    protected static function isHttpBaseReachable(string $baseUrl, float $timeoutSeconds = 3.0): bool
    {
        $parts = parse_url($baseUrl);
        if (! is_array($parts)) {
            return false;
        }

        $scheme = $parts['scheme'] ?? 'http';
        $host = $parts['host'] ?? '';
        $port = (int) ($parts['port'] ?? ('https' === $scheme ? 443 : 80));
        if ('' === $host) {
            return false;
        }

        $target = ('https' === $scheme ? 'ssl://' : '') . $host;
        $fp = @fsockopen($target, $port, $errno, $errstr, $timeoutSeconds);

        if (is_resource($fp)) {
            fclose($fp);

            return true;
        }

        return false;
    }

    /**
     * @return array{ok:bool, code:?string}
     */
    public static function parseGatewayJsonError(string $body): array
    {
        $data = json_decode($body, true);
        if (! is_array($data)) {
            return ['ok' => false, 'code' => null];
        }
        if (! empty($data['ok'])) {
            return ['ok' => true, 'code' => null];
        }

        $code = $data['error']['code'] ?? null;

        return [
            'ok' => false,
            'code' => is_string($code) ? $code : null,
        ];
    }
}

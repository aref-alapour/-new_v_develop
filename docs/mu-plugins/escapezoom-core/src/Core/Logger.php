<?php

namespace EscapeZoom\Core\Core;

final class Logger
{
    private const PREFIX = '[EZ_CORE] ';

    public static function info(string $message): void
    {
        error_log(self::PREFIX . $message);
    }

    public static function warning(string $message): void
    {
        error_log(self::PREFIX . 'WARN: ' . $message);
    }

    public static function error(string $message): void
    {
        error_log(self::PREFIX . 'ERROR: ' . $message);
    }
}

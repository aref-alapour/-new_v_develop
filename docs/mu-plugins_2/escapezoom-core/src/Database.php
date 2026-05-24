<?php

namespace EscapeZoom\Core;

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;

/**
 * کلاس Database برای مدیریت اتصالات Eloquent ORM
 * 
 * این کلاس دو اتصال دیتابیس را راه‌اندازی می‌کند:
 * - default: دیتابیس اصلی WordPress
 * - external: دیتابیس queries (escapezo_queries)
 */
class Database
{
    /**
     * @var Capsule|null
     */
    protected static ?Capsule $capsule = null;

    /**
     * @var bool
     */
    protected static bool $booted = false;

    /**
     * بوت کردن Eloquent با دو connection
     * 
     * @return void
     */
    public static function boot(): void
    {
        if (static::$booted) {
            return;
        }

        static::$capsule = new Capsule;

        $wp_prefix = $GLOBALS['table_prefix'] ?? 'wp_';

        // اتصال پیش‌فرض (دیتابیس اصلی WordPress، بدون prefix برای جداول سفارشی مثل wp_cancellation_requests)
        static::$capsule->addConnection([
            'driver'    => 'mysql',
            'host'      => DB_HOST,
            'database'  => DB_NAME,
            'username'  => DB_USER,
            'password'  => DB_PASSWORD,
            'charset'   => defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4',
            'collation' => defined('DB_COLLATE') && DB_COLLATE ? DB_COLLATE : 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => false,
            'engine'    => null,
        ], 'default');

        // اتصال جداول اصلی وردپرس (با prefix برای posts, users, terms و غیره)
        static::$capsule->addConnection([
            'driver'    => 'mysql',
            'host'      => DB_HOST,
            'database'  => DB_NAME,
            'username'  => DB_USER,
            'password'  => DB_PASSWORD,
            'charset'   => defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4',
            'collation' => defined('DB_COLLATE') && DB_COLLATE ? DB_COLLATE : 'utf8mb4_unicode_ci',
            'prefix'    => $wp_prefix,
            'strict'    => false,
            'engine'    => null,
        ], 'wordpress');

        // اتصال دوم (دیتابیس external - escapezo_queries)
        if (defined('DB_EXT_NAME') && defined('DB_EXT_USER') && defined('DB_EXT_PASSWORD')) {
            static::$capsule->addConnection([
                'driver'    => 'mysql',
                'host'      => defined('DB_EXT_HOST') ? DB_EXT_HOST : DB_HOST,
                'database'  => DB_EXT_NAME,
                'username'  => DB_EXT_USER,
                'password'  => DB_EXT_PASSWORD,
                'charset'   => defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4',
                'collation' => defined('DB_COLLATE') && DB_COLLATE ? DB_COLLATE : 'utf8mb4_unicode_ci',
                'prefix'    => '',
                'strict'    => false,
                'engine'    => null,
            ], 'external');
        }

        // تنظیم Event Dispatcher برای رویدادهای Eloquent
        static::$capsule->setEventDispatcher(new Dispatcher(new Container));

        // راه‌اندازی Eloquent به صورت global
        static::$capsule->setAsGlobal();
        static::$capsule->bootEloquent();

        static::$booted = true;
    }

    /**
     * دریافت instance Capsule
     * 
     * @return Capsule|null
     */
    public static function getCapsule(): ?Capsule
    {
        if (!static::$booted) {
            static::boot();
        }

        return static::$capsule;
    }

    /**
     * دریافت connection manager
     * اگر اتصال wordpress درخواست شده و هنوز ثبت نشده (مثلاً در مسیر ez-ajax)، آن را اضافه می‌کند.
     *
     * @param string|null $name
     * @return \Illuminate\Database\Connection
     */
    public static function connection(?string $name = null)
    {
        if (!static::$booted) {
            static::boot();
        }

        if ($name === 'wordpress') {
            static::ensureWordpressConnection();
            try {
                return static::$capsule->getConnection($name);
            } catch (\Throwable $e) {
                if (strpos($e->getMessage(), 'not configured') !== false) {
                    static::ensureWordpressConnection();
                    return static::$capsule->getConnection($name);
                }
                throw $e;
            }
        }

        return static::$capsule->getConnection($name);
    }

    /**
     * ثبت اتصال wordpress روی Capsule (برای مسیر ez-ajax که گاهی فقط default ثبت شده).
     * اگر قبلاً ثبت شده باشد، فراخوانی مجدد بی‌اثر است (addConnection همان نام را بازنویسی می‌کند).
     *
     * @return void
     */
    public static function ensureWordpressConnection(): void
    {
        $wp_prefix = isset($GLOBALS['table_prefix']) ? $GLOBALS['table_prefix'] : 'wp_';
        static::$capsule->addConnection([
            'driver'    => 'mysql',
            'host'      => DB_HOST,
            'database'  => DB_NAME,
            'username'  => DB_USER,
            'password'  => DB_PASSWORD,
            'charset'   => defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4',
            'collation' => defined('DB_COLLATE') && DB_COLLATE ? DB_COLLATE : 'utf8mb4_unicode_ci',
            'prefix'    => $wp_prefix,
            'strict'    => false,
            'engine'    => null,
        ], 'wordpress');
    }

    /**
     * اجرای query مستقیم روی اتصال default
     * 
     * @param string $query
     * @param array $bindings
     * @return mixed
     */
    public static function raw(string $query, array $bindings = [])
    {
        return static::connection('default')->select($query, $bindings);
    }

    /**
     * اجرای query مستقیم روی اتصال external
     * 
     * @param string $query
     * @param array $bindings
     * @return mixed
     */
    public static function rawExternal(string $query, array $bindings = [])
    {
        return static::connection('external')->select($query, $bindings);
    }
}

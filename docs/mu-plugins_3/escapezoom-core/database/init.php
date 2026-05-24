<?php

declare(strict_types=1);

/**
 * EscapeZoom Core – Database init (v3, entity-based)
 *
 * منبع واحد حقیقت برای اسکیمای جداول wp_ez_*:
 * - database/entities/*.sql  (01_ تا 09_ به ترتیب نام)
 * - database/infrastructure/action-scheduler.sql
 *
 * این فایل مسئول اجرای SQLها (CREATE TABLE IF NOT EXISTS و ALTERها) است.
 * اجرای مایگریشن‌ها بر اساس نسخه در wp_options کنترل می‌شود.
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * نسخه فعلی اسکیمای دیتابیس EscapeZoom.
 * هر بار که ساختار جداول wp_ez_* تغییر می‌کند، این نسخه را افزایش دهید.
 *
 * v3.1.0: افزودن جدول wp_ez_redirects (Entity 08_redirects.sql).
 * v4.0.0: دیکشنری + آرشیوساز — 02 taxonomies (۴ جدول، بدون تگ)، 04 cities+areas، 05 city_id/product_areas بدون تگ، 06 last_minute city_id، 09 archives_map.
 * v4.1.0: افزودن جدول wp_ez_redirect_404_logs برای لاگ 404 (گزارش‌گیری ماژول ریدایرکت‌ها).
 * v4.2.0: هم‌راستاسازی wp_ez_brands با query.sql — logo, score, reputation (مایگریشن برای دیتابیس‌های موجود).
 * v4.3.0: آرشیوساز Relation-Based — جدول wp_ez_archives_map سبک؛ wp_ez_archive_filters برای فیلترها؛ مایگریشن از ساختار قدیمی.
 * v4.3.1: ایندکس filter_type روی wp_ez_archive_filters برای JOINها و پرفورمنس.
 * v4.3.2: اضافه شدن ستون title به wp_ez_archives_map (عنوان نمایشی در ادمین).
 */
if (! defined('ESCAPEZOOM_DB_VERSION')) {
    define('ESCAPEZOOM_DB_VERSION', '4.3.2');
}

if (! function_exists('ez_core_database_init')) {

    /**
     * Entry point برای ساخت/به‌روزرسانی اسکیمای دیتابیس.
     *
     * - فایل‌های entities را به ترتیب نام (01_ تا 07_) اجرا می‌کند.
     * - سپس فایل زیرساختی Action Scheduler را اجرا می‌کند.
     * - نسخهٔ دیتابیس را در wp_options ذخیره می‌کند تا از اجرای بی‌مورد جلوگیری شود.
     */
    function ez_core_database_init(): void
    {
        // فقط در محیط وردپرس با $wpdb و توابع option_* اجرا شود.
        if (! function_exists('get_option')) {
            return;
        }

        $optionName   = 'escapezoom_db_version';
        $currentValue = get_option($optionName);

        // اگر نسخه فعلی با نسخهٔ تعریف‌شده یکسان است، نیازی به اجرای مجدد نیست.
        if ($currentValue === ESCAPEZOOM_DB_VERSION) {
            return;
        }

        $baseDir     = __DIR__;
        $entitiesDir = $baseDir . '/entities';
        $infraFile   = $baseDir . '/infrastructure/action-scheduler.sql';

        // اجرای فایل‌های entity به ترتیب نام (01_ تا 09_)
        if (is_dir($entitiesDir)) {
            $files = glob($entitiesDir . '/*.sql');
            if (is_array($files)) {
                sort($files, SORT_NATURAL | SORT_FLAG_CASE);
                foreach ($files as $filePath) {
                    ez_core_database_run_sql_file($filePath);
                }
            }
        }

        // اجرای فایل Action Scheduler (در صورت وجود)
        if (is_readable($infraFile)) {
            ez_core_database_run_sql_file($infraFile);
        }

        // مایگریشن برای دیتابیس‌های موجود (ستون‌های جدید در جداول از قبل ساخته‌شده)
        ez_core_database_run_migrations($currentValue);

        // پس از موفقیت، نسخهٔ دیتابیس را به‌روزرسانی کن.
        update_option($optionName, ESCAPEZOOM_DB_VERSION);
    }
}

/**
 * مایگریشن‌های افزایشی برای دیتابیس‌های موجود.
 * فقط وقتی نسخهٔ ذخیره‌شده قدیمی‌تر از نسخهٔ فعلی است اجرا می‌شود.
 * @param string|false $savedVersion نسخهٔ ذخیره‌شده در wp_options (false = نصب تازه).
 */
if (! function_exists('ez_core_database_run_migrations')) {
    function ez_core_database_run_migrations($savedVersion): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ez_brands';
        $savedVersion = $savedVersion === false ? '' : (string) $savedVersion;

        // v4.2.0: wp_ez_brands — اضافه کردن logo, score, reputation در صورت نبود
        if (version_compare($savedVersion, '4.2.0', '<')) {
            if ($wpdb->get_var($wpdb->prepare('SELECT 1 FROM information_schema.tables WHERE table_schema = %s AND table_name = %s', DB_NAME, $wpdb->prefix . 'ez_brands'))) {
                $columns = $wpdb->get_col("SHOW COLUMNS FROM `{$table}`");
                if (! in_array('logo', $columns, true)) {
                    $wpdb->query("ALTER TABLE `{$table}` ADD COLUMN `logo` varchar(255) DEFAULT NULL AFTER `slug`");
                }
                if (! in_array('score', $columns, true)) {
                    $wpdb->query("ALTER TABLE `{$table}` ADD COLUMN `score` decimal(3,1) DEFAULT 0 AFTER `address`");
                }
                if (! in_array('reputation', $columns, true)) {
                    $wpdb->query("ALTER TABLE `{$table}` ADD COLUMN `reputation` bigint unsigned DEFAULT 0 AFTER `score`");
                }
            }
        }

        // v4.3.0: wp_ez_archives_map سبک + wp_ez_archive_filters؛ مایگریشن از جدول wide
        $mapTable   = $wpdb->prefix . 'ez_archives_map';
        $filtersTable = $wpdb->prefix . 'ez_archive_filters';
        if (version_compare($savedVersion, '4.3.0', '<')) {
            $mapExists = $wpdb->get_var($wpdb->prepare(
                'SELECT 1 FROM information_schema.tables WHERE table_schema = %s AND table_name = %s',
                DB_NAME,
                $mapTable
            ));
            if ($mapExists) {
                $mapCols = $wpdb->get_col("SHOW COLUMNS FROM `{$mapTable}`");
                $hasOld = in_array('city_id', $mapCols, true) || in_array('type_id', $mapCols, true);
                if ($hasOld) {
                    $filtersExists = $wpdb->get_var($wpdb->prepare(
                        'SELECT 1 FROM information_schema.tables WHERE table_schema = %s AND table_name = %s',
                        DB_NAME,
                        $filtersTable
                    ));
                    if (! $filtersExists) {
                        $wpdb->query("CREATE TABLE IF NOT EXISTS `{$filtersTable}` (
                          `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                          `archive_map_id` bigint unsigned NOT NULL,
                          `filter_type` varchar(50) NOT NULL,
                          `filter_value` bigint unsigned NOT NULL,
                          PRIMARY KEY (`id`),
                          UNIQUE KEY `ez_archive_filters_map_type_unique` (`archive_map_id`,`filter_type`),
                          KEY `ez_archive_filters_filter_value_index` (`filter_value`),
                          CONSTRAINT `ez_archive_filters_archive_map_id_foreign` FOREIGN KEY (`archive_map_id`) REFERENCES `{$mapTable}` (`id`) ON DELETE CASCADE
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                    }
                    $rows = $wpdb->get_results("SELECT id, type_id, city_id, area_id, genre_id, mood_id, theme_id FROM `{$mapTable}`");
                    $filterTypes = ['type_id' => 'type_id', 'city_id' => 'city_id', 'area_id' => 'area_id', 'genre_id' => 'genre_id', 'mood_id' => 'mood_id', 'theme_id' => 'theme_id'];
                    foreach ($rows as $row) {
                        foreach ($filterTypes as $col => $ft) {
                            $val = $row->{$col} ?? null;
                            if ($val !== null && $val !== '' && (int) $val > 0) {
                                $wpdb->insert($filtersTable, [
                                    'archive_map_id' => $row->id,
                                    'filter_type'    => $ft,
                                    'filter_value'   => (int) $val,
                                ], ['%d', '%s', '%d']);
                            }
                        }
                    }
                    $constraints = $wpdb->get_col($wpdb->prepare(
                        "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND CONSTRAINT_TYPE = 'FOREIGN KEY'",
                        DB_NAME,
                        $mapTable
                    ));
                    foreach ($constraints as $cn) {
                        $wpdb->query("ALTER TABLE `{$mapTable}` DROP FOREIGN KEY `" . esc_sql($cn) . "`");
                    }
                    $dropCols = ['city_id', 'area_id', 'type_id', 'genre_id', 'mood_id', 'theme_id'];
                    foreach ($dropCols as $col) {
                        if (in_array($col, $mapCols, true)) {
                            $wpdb->query("ALTER TABLE `{$mapTable}` DROP COLUMN `{$col}`");
                        }
                    }
                }
            }
        }

        // v4.3.1: ایندکس filter_type روی wp_ez_archive_filters (برای نصب‌های قبلی که جدول بدون این ایندکس ساخته شده)
        $filtersTable = $wpdb->prefix . 'ez_archive_filters';
        if (version_compare($savedVersion, '4.3.1', '<')) {
            $tExists = $wpdb->get_var($wpdb->prepare(
                'SELECT 1 FROM information_schema.tables WHERE table_schema = %s AND table_name = %s',
                DB_NAME,
                $filtersTable
            ));
            if ($tExists) {
                $indexes = $wpdb->get_col($wpdb->prepare(
                    "SELECT INDEX_NAME FROM information_schema.statistics WHERE table_schema = %s AND table_name = %s AND INDEX_NAME = %s",
                    DB_NAME,
                    $filtersTable,
                    'ez_archive_filters_filter_type_index'
                ));
                if (empty($indexes)) {
                    $wpdb->query("ALTER TABLE `{$filtersTable}` ADD KEY `ez_archive_filters_filter_type_index` (`filter_type`)");
                }
            }
        }

        // v4.3.2: ستون title در wp_ez_archives_map (برای نصب‌های قبلی)
        $mapTable = $wpdb->prefix . 'ez_archives_map';
        if (version_compare($savedVersion, '4.3.2', '<')) {
            $mapExists = $wpdb->get_var($wpdb->prepare(
                'SELECT 1 FROM information_schema.tables WHERE table_schema = %s AND table_name = %s',
                DB_NAME,
                $mapTable
            ));
            if ($mapExists) {
                $mapCols = $wpdb->get_col("SHOW COLUMNS FROM `{$mapTable}`");
                if (! in_array('title', $mapCols, true)) {
                    $wpdb->query("ALTER TABLE `{$mapTable}` ADD COLUMN `title` varchar(255) DEFAULT NULL COMMENT 'عنوان نمایشی در ادمین' AFTER `id`");
                }
            }
        }
    }
}

if (! function_exists('ez_core_database_run_sql_file')) {
    /**
     * خواندن و اجرای یک فایل SQL (شامل چندین دستور).
     *
     * توجه: این پیاده‌سازی ساده است و فرض می‌کند درون فایل:
     * - از DELIMITER سفارشی استفاده نشده است.
     * - پروسیجر/فانکشن با ; در میانه بدنه تعریف نشده است.
     * اسکیمای فعلی EscapeZoom فقط شامل CREATE/ALTER ساده و کامنت‌هاست.
     */
    function ez_core_database_run_sql_file(string $filePath): void
    {
        global $wpdb;

        if (! is_readable($filePath)) {
            return;
        }

        $sql = file_get_contents($filePath);
        if ($sql === false) {
            return;
        }

        // جدا کردن دستورات بر اساس ; در انتهای خط (CREATE ...; \n)
        $statements = preg_split('/;[\r\n]+/', $sql);
        if (! is_array($statements)) {
            return;
        }

        foreach ($statements as $statement) {
            $statement = trim($statement);

            // پرش از خطوط خالی یا کامنت‌های تک‌خطی
            if ($statement === '' || str_starts_with($statement, '--')) {
                continue;
            }

            // اجرای دستور؛ در صورت خطا، در WP_DEBUG لاگ کن اما اجرای بقیه ادامه یابد.
            $result = $wpdb->query($statement);

            if ($result === false && defined('WP_DEBUG') && WP_DEBUG) {
                error_log(
                    sprintf(
                        '[EscapeZoom DB] SQL error on file %s: %s',
                        $filePath,
                        $wpdb->last_error
                    )
                );
            }
        }
    }
}

// اجرای مایگریشن‌ها روی init (پس از بارگذاری وردپرس).
add_action('init', 'ez_core_database_init', 1);


<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Redirects;

/**
 * وارد کردن ریدایرکت از فایل Excel/CSV و دانلود فایل نمونه.
 */
final class RedirectImportExport
{
    public const NONCE_IMPORT   = 'ez_redirect_import';
    public const NONCE_SAMPLE   = 'ez_redirect_sample';
    public const NONCE_EXPORT    = 'ez_redirect_export';
    private const ALLOWED_MATCH  = ['exact', 'prefix', 'regex'];

    public static function register(): void
    {
        add_action('admin_post_ez_import_redirects', [self::class, 'handleImport']);
        add_action('admin_post_ez_download_redirects_sample', [self::class, 'handleDownloadSample']);
        add_action('admin_post_ez_export_redirects', [self::class, 'handleExportRedirects']);
    }

    /**
     * خروجی فایل نمونه برای دانلود (CSV با BOM برای باز شدن در اکسل با encoding درست).
     */
    public static function handleDownloadSample(): void
    {
        if (! current_user_can('manage_options')) {
            wp_die(__('دسترسی ندارید.', 'escapezoom-core'));
        }
        if (
            ! isset($_GET['_wpnonce'])
            || ! wp_verify_nonce((string) $_GET['_wpnonce'], self::NONCE_SAMPLE)
        ) {
            wp_die(__('اعتبارسنجی امنیتی ناموفق.', 'escapezoom-core'));
        }

        $format = isset($_GET['format']) ? sanitize_key((string) $_GET['format']) : 'csv';
        if ($format === 'xlsx' && self::hasPhpSpreadsheet()) {
            self::sendSampleXlsx();
            return;
        }
        self::sendSampleCsv();
    }

    private static function sendSampleCsv(): void
    {
        $headers = ['from_path', 'to_url', 'status_code', 'match_type', 'is_active'];
        $rows    = [
            ['/old-page/', '/new-page/', 301, 'exact', 1],
            ['/blog/old/', '/blog/new/', 302, 'prefix', 1],
        ];

        $filename = 'ez-redirects-sample.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        $out = fopen('php://output', 'w');
        if ($out === false) {
            return;
        }
        fprintf($out, "\xEF\xBB\xBF");
        fputcsv($out, $headers);
        foreach ($rows as $row) {
            fputcsv($out, $row);
        }
        fclose($out);
        exit;
    }

    public static function hasPhpSpreadsheet(): bool
    {
        return class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class);
    }

    private static function sendSampleXlsx(): void
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle(__('ریدایرکت‌ها', 'escapezoom-core'));
        $sheet->fromArray(
            [
                ['from_path', 'to_url', 'status_code', 'match_type', 'is_active'],
                ['/old-page/', '/new-page/', 301, 'exact', 1],
                ['/blog/old/', '/blog/new/', 302, 'prefix', 1],
            ],
            null,
            'A1'
        );

        $filename = 'ez-redirects-sample.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * خروجی گرفتن از ریدایرکت‌ها (CSV). در صورت فیلتر کد وضعیت در لیست، همان فیلتر اعمال می‌شود.
     */
    public static function handleExportRedirects(): void
    {
        if (! current_user_can('manage_options')) {
            wp_die(__('دسترسی ندارید.', 'escapezoom-core'));
        }
        if (
            ! isset($_GET['_wpnonce'])
            || ! wp_verify_nonce((string) $_GET['_wpnonce'], self::NONCE_EXPORT)
        ) {
            wp_die(__('اعتبارسنجی امنیتی ناموفق.', 'escapezoom-core'));
        }

        global $wpdb;
        $table  = $wpdb->prefix . 'ez_redirects';
        $where  = 'WHERE 1=1';
        $params = [];

        $filter_status = isset($_GET['status_code']) ? (int) $_GET['status_code'] : 0;
        if ($filter_status > 0 && RedirectStatusCodes::isValid($filter_status)) {
            $where .= ' AND status_code = %d';
            $params[] = $filter_status;
        }

        $sql = "SELECT id, from_path, to_url, status_code, match_type, is_active, hits, last_hit_at FROM {$table} {$where} ORDER BY from_path";
        $rows = $params !== []
            ? $wpdb->get_results($wpdb->prepare($sql, ...$params), ARRAY_A)
            : $wpdb->get_results($sql, ARRAY_A);

        $headers = ['from_path', 'to_url', 'status_code', 'match_type', 'is_active', 'hits', 'last_hit_at'];
        $filename = 'ez-redirects-export-' . gmdate('Y-m-d-His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        $out = fopen('php://output', 'w');
        if ($out === false) {
            return;
        }
        fprintf($out, "\xEF\xBB\xBF");
        fputcsv($out, $headers);
        foreach ($rows as $row) {
            fputcsv($out, [
                $row['from_path'] ?? '',
                $row['to_url'] ?? '',
                $row['status_code'] ?? 301,
                $row['match_type'] ?? 'exact',
                isset($row['is_active']) ? (int) $row['is_active'] : 1,
                $row['hits'] ?? 0,
                isset($row['last_hit_at']) ? $row['last_hit_at'] : '',
            ]);
        }
        fclose($out);
        exit;
    }

    /**
     * پردازش آپلود و وارد کردن ریدایرکت‌ها از فایل.
     */
    public static function handleImport(): void
    {
        if (! current_user_can('manage_options')) {
            wp_die(__('دسترسی ندارید.', 'escapezoom-core'));
        }
        if (
            ! isset($_POST['_wpnonce'])
            || ! wp_verify_nonce((string) $_POST['_wpnonce'], self::NONCE_IMPORT)
        ) {
            wp_die(__('اعتبارسنجی امنیتی ناموفق.', 'escapezoom-core'));
        }

        $file = isset($_FILES['ez_redirects_file']) ? $_FILES['ez_redirects_file'] : null;
        if (! $file || empty($file['tmp_name']) || ! is_uploaded_file($file['tmp_name'])) {
            wp_safe_redirect(admin_url('admin.php?page=' . RedirectAdmin::PAGE_SLUG . '&error=no_file'));
            exit;
        }

        $ext = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        $rows = [];
        if ($ext === 'xlsx' || $ext === 'xls') {
            if (! self::hasPhpSpreadsheet()) {
                wp_safe_redirect(admin_url('admin.php?page=' . RedirectAdmin::PAGE_SLUG . '&error=no_excel'));
                exit;
            }
            $rows = self::parseXlsx($file['tmp_name']);
        } else {
            $rows = self::parseCsv($file['tmp_name']);
        }

        if ($rows === []) {
            wp_safe_redirect(admin_url('admin.php?page=' . RedirectAdmin::PAGE_SLUG . '&error=empty'));
            exit;
        }

        $result = self::insertRows($rows);
        RedirectAdmin::invalidateFragmentCache();
        $url    = admin_url('admin.php?page=' . RedirectAdmin::PAGE_SLUG);
        $url    = add_query_arg([
            'message'  => 'imported',
            'imported' => $result['imported'],
            'skipped'  => $result['skipped'],
            'errors'   => $result['errors'],
        ], $url);
        wp_safe_redirect($url);
        exit;
    }

    /**
     * @return list<array{from_path?: string, to_url?: string, status_code?: int, match_type?: string, is_active?: int}>
     */
    private static function parseCsv(string $path): array
    {
        $rows = [];
        $h    = fopen($path, 'r');
        if ($h === false) {
            return [];
        }
        $header = fgetcsv($h);
        if ($header === false) {
            fclose($h);
            return [];
        }
        $header = array_map('trim', $header);
        $idx    = [
            'from_path'   => array_search('from_path', $header, true),
            'to_url'      => array_search('to_url', $header, true),
            'status_code' => array_search('status_code', $header, true),
            'match_type'  => array_search('match_type', $header, true),
            'is_active'   => array_search('is_active', $header, true),
        ];
        if ($idx['from_path'] === false || $idx['to_url'] === false) {
            fclose($h);
            return [];
        }
        while (($row = fgetcsv($h)) !== false) {
            $from = isset($row[$idx['from_path']]) ? trim((string) $row[$idx['from_path']]) : '';
            $to   = isset($row[$idx['to_url']]) ? trim((string) $row[$idx['to_url']]) : '';
            if ($from === '' && $to === '') {
                continue;
            }
            if ($from !== '' && $from[0] !== '/') {
                $from = '/' . $from;
            }
            $rows[] = [
                'from_path'   => $from,
                'to_url'      => $to,
                'status_code' => isset($row[$idx['status_code']]) ? (int) $row[$idx['status_code']] : 301,
                'match_type'  => isset($row[$idx['match_type']]) ? trim((string) $row[$idx['match_type']]) : 'exact',
                'is_active'   => isset($row[$idx['is_active']]) ? (int) $row[$idx['is_active']] : 1,
            ];
        }
        fclose($h);
        return $rows;
    }

    /**
     * @return list<array{from_path?: string, to_url?: string, status_code?: int, match_type?: string, is_active?: int}>
     */
    private static function parseXlsx(string $path): array
    {
        if (! self::hasPhpSpreadsheet()) {
            return [];
        }
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($path);
        $sheet       = $spreadsheet->getActiveSheet();
        $data        = $sheet->toArray();
        if (count($data) < 2) {
            return [];
        }
        $header = array_map('trim', array_map('strval', $data[0]));
        $idx    = [
            'from_path'   => array_search('from_path', $header, true),
            'to_url'      => array_search('to_url', $header, true),
            'status_code' => array_search('status_code', $header, true),
            'match_type'  => array_search('match_type', $header, true),
            'is_active'   => array_search('is_active', $header, true),
        ];
        if ($idx['from_path'] === false || $idx['to_url'] === false) {
            return [];
        }
        $rows = [];
        for ($i = 1, $c = count($data); $i < $c; $i++) {
            $row = $data[$i];
            $from = $idx['from_path'] !== false && isset($row[$idx['from_path']]) ? trim((string) $row[$idx['from_path']]) : '';
            $to   = $idx['to_url'] !== false && isset($row[$idx['to_url']]) ? trim((string) $row[$idx['to_url']]) : '';
            if ($from === '' && $to === '') {
                continue;
            }
            if ($from !== '' && $from[0] !== '/') {
                $from = '/' . $from;
            }
            $rows[] = [
                'from_path'   => $from,
                'to_url'      => $to,
                'status_code' => $idx['status_code'] !== false && isset($row[$idx['status_code']]) ? (int) $row[$idx['status_code']] : 301,
                'match_type'  => $idx['match_type'] !== false && isset($row[$idx['match_type']]) ? trim((string) $row[$idx['match_type']]) : 'exact',
                'is_active'   => $idx['is_active'] !== false && isset($row[$idx['is_active']]) ? (int) $row[$idx['is_active']] : 1,
            ];
        }
        return $rows;
    }

    /**
     * @param list<array{from_path?: string, to_url?: string, status_code?: int, match_type?: string, is_active?: int}> $rows
     * @return array{imported: int, skipped: int, errors: int}
     */
    private static function insertRows(array $rows): array
    {
        global $wpdb;
        $table   = $wpdb->prefix . 'ez_redirects';
        $imported = 0;
        $skipped  = 0;
        $errors   = 0;
        $now      = current_time('mysql');

        foreach ($rows as $r) {
            $from_path = isset($r['from_path']) ? $r['from_path'] : '';
            $to_url    = isset($r['to_url']) ? $r['to_url'] : '';
            if ($from_path === '' || $to_url === '') {
                $skipped++;
                continue;
            }
            $status_code = isset($r['status_code']) && RedirectStatusCodes::isValid((int) $r['status_code'])
                ? (int) $r['status_code'] : 301;
            $match_type = isset($r['match_type']) && in_array($r['match_type'], self::ALLOWED_MATCH, true)
                ? $r['match_type'] : 'exact';
            $is_active = isset($r['is_active']) ? (int) $r['is_active'] : 1;
            if ($match_type === 'regex') {
                if (@preg_match($from_path, '/test') === false) {
                    $errors++;
                    continue;
                }
            }

            // پرش ردیف‌های تکراری (همان from_path/match_type/status_code)
            $duplicateSql = $wpdb->prepare(
                "SELECT id FROM {$table} WHERE from_path = %s AND match_type = %s AND status_code = %d LIMIT 1",
                $from_path,
                $match_type,
                $status_code
            );
            $existingId = $wpdb->get_var($duplicateSql);
            if ($existingId !== null) {
                $skipped++;
                continue;
            }

            $ok = $wpdb->insert(
                $table,
                [
                    'from_path'   => $from_path,
                    'to_url'      => $to_url,
                    'status_code' => $status_code,
                    'match_type'  => $match_type,
                    'is_active'   => $is_active ? 1 : 0,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ],
                ['%s', '%s', '%d', '%s', '%d', '%s', '%s']
            );
            if ($ok) {
                $imported++;
            } else {
                $errors++;
            }
        }

        return ['imported' => $imported, 'skipped' => $skipped, 'errors' => $errors];
    }

    /**
     * رندر صفحه ورود از Excel/CSV و لینک دانلود نمونه.
     */
    public static function renderImportPage(): void
    {
        if (! current_user_can('manage_options')) {
            wp_die(__('دسترسی ندارید.', 'escapezoom-core'));
        }

        $sample_csv = admin_url('admin-post.php');
        $sample_csv = add_query_arg([
            'action'   => 'ez_download_redirects_sample',
            'format'   => 'csv',
            '_wpnonce' => wp_create_nonce(self::NONCE_SAMPLE),
        ], $sample_csv);

        $sample_xlsx = admin_url('admin-post.php');
        $sample_xlsx = add_query_arg([
            'action'   => 'ez_download_redirects_sample',
            'format'   => 'xlsx',
            '_wpnonce' => wp_create_nonce(self::NONCE_SAMPLE),
        ], $sample_xlsx);

        $has_excel = self::hasPhpSpreadsheet();

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('وارد کردن ریدایرکت از Excel / CSV', 'escapezoom-core') . '</h1>';

        if (isset($_GET['error'])) {
            $err = sanitize_key((string) $_GET['error']);
            $msg = [
                'no_file'  => __('فایلی انتخاب نشده یا آپلود ناموفق بود.', 'escapezoom-core'),
                'no_excel' => __('برای ورود فایل Excel پکیج phpoffice/phpspreadsheet باید نصب باشد (composer require phpoffice/phpspreadsheet).', 'escapezoom-core'),
                'empty'    => __('هیچ ردیف معتبری در فایل یافت نشد.', 'escapezoom-core'),
            ];
            if (isset($msg[$err])) {
                echo '<div class="notice notice-error"><p>' . esc_html($msg[$err]) . '</p></div>';
            }
        }

        if (isset($_GET['imported']) || isset($_GET['skipped']) || isset($_GET['errors'])) {
            $i = (int) ($_GET['imported'] ?? 0);
            $s = (int) ($_GET['skipped'] ?? 0);
            $e = (int) ($_GET['errors'] ?? 0);
            echo '<div class="notice notice-success"><p>';
            printf(
                __('ورود انجام شد: %d ریدایرکت اضافه شد، %d ردیف رد شد، %d خطا.', 'escapezoom-core'),
                $i,
                $s,
                $e
            );
            echo '</p></div>';
        }

        echo '<div class="card" style="max-width: 600px; padding: 1em;">';
        echo '<h2 class="title">' . esc_html__('دانلود فایل نمونه', 'escapezoom-core') . '</h2>';
        echo '<p>' . esc_html__('ستون‌ها: from_path, to_url, status_code, match_type, is_active. سطر اول عنوان ستون‌ها است.', 'escapezoom-core') . '</p>';
        echo '<p><a href="' . esc_url($sample_csv) . '" class="button button-secondary">' . esc_html__('دانلود نمونه CSV', 'escapezoom-core') . '</a>';
        if ($has_excel) {
            echo ' <a href="' . esc_url($sample_xlsx) . '" class="button button-secondary">' . esc_html__('دانلود نمونه Excel (xlsx)', 'escapezoom-core') . '</a>';
        } else {
            echo ' <span class="description">' . esc_html__('(نمونه Excel با نصب phpoffice/phpspreadsheet فعال می‌شود)', 'escapezoom-core') . '</span>';
        }
        echo '</p></div>';

        echo '<div class="card" style="max-width: 600px; padding: 1em; margin-top: 1em;">';
        echo '<h2 class="title">' . esc_html__('آپلود فایل', 'escapezoom-core') . '</h2>';
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" enctype="multipart/form-data">';
        echo '<input type="hidden" name="action" value="ez_import_redirects">';
        wp_nonce_field(self::NONCE_IMPORT, '_wpnonce');
        echo '<p><input type="file" name="ez_redirects_file" accept=".csv,.xlsx,.xls" required></p>';
        echo '<p class="description">' . esc_html__('فرمت‌های مجاز: CSV، XLSX، XLS.', 'escapezoom-core') . '</p>';
        submit_button(__('وارد کردن ریدایرکت‌ها', 'escapezoom-core'));
        echo '</form>';
        echo '</div>';
        echo '</div>';
    }
}

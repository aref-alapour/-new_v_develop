<?php

namespace EscapeZoom\Core\Modules\DomainAliases;

final class AdminSettings
{
    private const MENU_SLUG = 'ez_domain_aliases';

    public static function register(): void
    {
        add_action('admin_menu', [self::class, 'menu']);
    }

    public static function menu(): void
    {
        add_options_page(
            'دامنه‌های Escapezoom',
            'دامنه‌ها (EZ)',
            'manage_options',
            self::MENU_SLUG,
            [self::class, 'render']
        );
    }

    public static function render(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (isset($_POST['ez_domain_aliases_save']) && check_admin_referer('ez_domain_aliases_save', 'ez_domain_aliases_nonce')) {
            self::saveFromPost();
            echo '<div class="notice notice-success is-dismissible"><p>تنظیمات ذخیره شد.</p></div>';
        }

        $cfg = Runtime::loadConfig();
        $main = esc_attr((string) ($cfg['main_host'] ?? ''));
        $fallback = (string) ($cfg['fallback'] ?? 'accept_all');
        if ($fallback !== 'strict') {
            $fallback = 'accept_all';
        }
        $fsGl = !empty($cfg['force_ssl_global']);
        /** @var array<string, array<string, mixed>> $domains */
        $domains = isset($cfg['domains']) && is_array($cfg['domains']) ? $cfg['domains'] : [];

        ?>
        <div class="wrap">
            <h1>دامنه‌های اضافه (Escapezoom Core)</h1>
            <p class="description" style="max-width: 52rem;">
                این بخش فقط <strong>بازنویسی آدرس‌ها به میزبان فعلی</strong> یا <strong>ریدایرکت دامنه</strong> است و
                <strong>آپشن‌های «خواندن»</strong> (مثل <code>show_on_front</code>) را عوض نمی‌کند؛ با <code>show_on_front = posts</code>
                خانه همان ایندکس وبلاگ و قالب <code>home.php</code> تم است.
                اگر چند دامنهٔ عمومی همان محتوا را نشان می‌دهند و «قبول همه» فعال است، برای جلوگیری از
                <strong>محتوای تکراری</strong> در Yoast (یا افزونهٔ کانونیکال) دامنهٔ کانونیکال مشخص کنید یا از حالت «سخت‌گیرانه» استفاده کنید.
            </p>

            <form method="post" action="">
                <?php wp_nonce_field('ez_domain_aliases_save', 'ez_domain_aliases_nonce'); ?>

                <h2 class="title">سراسری</h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="ez_main_host">دامنهٔ اصلی (canonical)</label></th>
                        <td>
                            <input name="main_host" id="ez_main_host" type="text" class="regular-text" value="<?php echo $main; ?>"
                                   placeholder="escapezoom.ir"/>
                            <p class="description">فقط نام میزبان، بدون <code>https://</code> (مثلاً <code>escapezoom.ir</code>).</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="ez_fallback">دامنهٔ ناشناس</label></th>
                        <td>
                            <select name="fallback" id="ez_fallback">
                                <option value="accept_all" <?php selected($fallback, 'accept_all'); ?>>
                                    قبول همه — آدرس‌های سایت با میزبان فعلی بازنویسی شوند
                                </option>
                                <option value="strict" <?php selected($fallback, 'strict'); ?>>
                                    سخت‌گیرانه — فقط دامنهٔ اصلی و دامنه‌های ثبت‌شده؛ بقیه به خانهٔ ذخیره‌شده در وردپرس ریدایرکت ۳۰۱ می‌شوند
                                </option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">HTTPS</th>
                        <td>
                            <label>
                                <input name="force_ssl_global" type="checkbox" value="1" <?php checked($fsGl); ?> />
                                در حالت بازنویسی URL، پیوندها را به <code>https</code> اجبار کن
                            </label>
                        </td>
                    </tr>
                </table>

                <h2 class="title">دامنه‌های اضافی</h2>
                <p class="description">کلید هر ردیف: نام میزبان خالص (مثلاً <code>www.escapezoom.co</code>). «ریدایرکت» در صورت پر بودن، کل ترافیک آن میزبان را به همان URL می‌فرستد (بدون تغییر آپشن‌های خواندن).</p>

                <table class="widefat striped">
                    <thead>
                    <tr>
                        <th>میزبان</th>
                        <th>ریدایرکت به (اختیاری)</th>
                        <th>اجبار SSL برای URLهای بازنویسی‌شده</th>
                        <th>حذف</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $i = 0;
                    foreach ($domains as $hostKey => $row) {
                        if (!is_array($row)) {
                            $row = [];
                        }
                        $rk = esc_attr(Runtime::canonicalHostKey((string) $hostKey));
                        $red = isset($row['redirect_url']) ? esc_attr((string) $row['redirect_url']) : '';
                        $fs = !empty($row['force_ssl']);
                        ?>
                        <tr>
                            <td><input type="text" class="regular-text" name="domains_existing[<?php echo (int) $i; ?>][host]" value="<?php echo $rk; ?>"/></td>
                            <td><input type="url" class="large-text" name="domains_existing[<?php echo (int) $i; ?>][redirect_url]" value="<?php echo $red; ?>" placeholder="https://"/></td>
                            <td><input type="checkbox" name="domains_existing[<?php echo (int) $i; ?>][force_ssl]" value="1" <?php checked($fs); ?>/></td>
                            <td><label><input type="checkbox" name="domains_existing[<?php echo (int) $i; ?>][delete]" value="1"/> حذف</label></td>
                        </tr>
                        <?php
                        ++$i;
                    }
                    ?>
                    <tr>
                        <td><input type="text" class="regular-text" name="domains_new[host]" value="" placeholder="میزبان جدید"/></td>
                        <td><input type="url" class="large-text" name="domains_new[redirect_url]" value="" placeholder="https://"/></td>
                        <td><input type="checkbox" name="domains_new[force_ssl]" value="1"/></td>
                        <td>—</td>
                    </tr>
                    </tbody>
                </table>

                <?php submit_button('ذخیره', 'primary', 'ez_domain_aliases_save'); ?>
            </form>
        </div>
        <?php
    }

    private static function saveFromPost(): void
    {
        $main = isset($_POST['main_host']) ? sanitize_text_field(wp_unslash($_POST['main_host'])) : '';
        $main = Runtime::canonicalHostKey($main);

        $fb = isset($_POST['fallback']) && $_POST['fallback'] === 'strict' ? 'strict' : 'accept_all';
        $fsG = isset($_POST['force_ssl_global']);

        $domains = [];

        if (isset($_POST['domains_existing']) && is_array($_POST['domains_existing'])) {
            foreach ($_POST['domains_existing'] as $row) {
                if (!is_array($row)) {
                    continue;
                }
                if (!empty($row['delete'])) {
                    continue;
                }
                $h = isset($row['host']) ? Runtime::canonicalHostKey(sanitize_text_field(wp_unslash($row['host']))) : '';
                if ($h === '') {
                    continue;
                }
                $red = isset($row['redirect_url']) ? esc_url_raw(wp_unslash($row['redirect_url'])) : '';
                $domains[$h] = [
                    'redirect_url' => $red,
                    'force_ssl' => !empty($row['force_ssl']),
                ];
            }
        }

        if (isset($_POST['domains_new']) && is_array($_POST['domains_new'])) {
            $n = $_POST['domains_new'];
            $h = isset($n['host']) ? Runtime::canonicalHostKey(sanitize_text_field(wp_unslash($n['host']))) : '';
            if ($h !== '') {
                $red = isset($n['redirect_url']) ? esc_url_raw(wp_unslash($n['redirect_url'])) : '';
                $domains[$h] = [
                    'redirect_url' => $red,
                    'force_ssl' => !empty($n['force_ssl']),
                ];
            }
        }

        update_option(Runtime::optionKey(), [
            'main_host' => $main,
            'fallback' => $fb,
            'force_ssl_global' => $fsG,
            'domains' => $domains,
        ], false);
    }
}

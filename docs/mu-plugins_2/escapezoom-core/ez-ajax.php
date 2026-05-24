<?php
/**
 * درگاه واحد AJAX – فقط هندل کردن درخواست
 *
 * تعریف callback یا هندلر اینجا نیست؛ قالب/پلاگین با کلاس Ajax ثبت می‌کنند.
 */
if (!defined('ABSPATH')) {
    exit;
}

\EscapeZoom\Core\Ajax::handle();

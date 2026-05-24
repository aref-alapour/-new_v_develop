# پاک‌سازی دارایی‌های فرانت

یادداشت داخلی برای وضعیت فعلی بارگذاری CSS/JS در فرانت تم `escapezoom-v2`.

## هدف

- دارایی‌های تم فقط از باندل build شده بیایند: `dist/front.css` و `dist/front.js`.
- استایل و اسکریپت پلاگین‌ها روی فرانت عمومی تا حد ممکن حذف شوند.
- در صفحات فروشگاهی ضروری، فقط اسکریپت‌های ووکامرس لازم بمانند؛ CSS پیش‌فرض ووکامرس و Blocks لود نشود.

## فایل‌های مرتبط

| فایل | نقش |
|------|-----|
| `functions.php` | enqueue تم (`main-css` / `main-js`) و `require` فایل cleanup |
| `inc/theme/front-asset-cleanup.php` | dequeue/deregister دارایی‌های پلاگین و هستهٔ غیرضروری |
| `header.php` | لود شرطی MediaAd |
| `ahmadreza/init.php` | فیلتر قدیمی `woocommerce_enqueue_styles` با `__return_empty_array` |

## باندل تم

- CSS: `dist/front.css` با handle `main-css`
- JS: `dist/front.js` با handle `main-js` و `type="module"`
- وابستگی `main-js` به `jquery` هنوز برقرار است.

## منطق cleanup

فقط روی فرانت عمومی اجرا می‌شود: `! is_admin() && ! wp_doing_ajax() && ! wp_doing_cron()`.

هوک‌ها:

- `wp_enqueue_scripts` با اولویت `999`
- `wp_print_styles` و `wp_print_scripts` با اولویت `1` (برای enqueueهای دیرهنگام مثل `wc-blocks-style` در `wp_head`)
- فیلتر `woocommerce_enqueue_styles` برای خالی کردن CSS پیش‌فرض ووکامرس

### شرط‌های نگه‌داشتن دارایی

| تابع | چه زمانی `true` است |
|------|---------------------|
| `ez_theme_needs_wc_frontend_assets()` | `is_woocommerce()` یا سبد، چک‌اوت، حساب، صفحهٔ تشکر |
| `ez_theme_needs_wc_order_attribution_assets()` | سبد، چک‌اوت، صفحهٔ تشکر |
| `ez_theme_needs_brand_assets()` | صفحهٔ محصول یا taxonomy برند (`yith_product_brand` / `product_brand`) |
| `ez_theme_needs_wallet_assets()` | صفحهٔ حساب کاربری |
| `ez_theme_should_load_mediaad()` | فقط دامنه‌های `escapezoom.co` / `escapezoom.ir` (با و بدون `www`) |

## پلاگین‌ها و دارایی‌های هدف

| منبع | دارایی | رفتار فعلی |
|------|--------|------------|
| WooCommerce | `woocommerce`, `jquery-blockui`, `js-cookie` و اسکریپت‌های `wc-*` | خارج از صفحات فروشگاهی |
| WooCommerce Blocks | `wc-blocks-style`, `wc-blocks-style-*`, `wc-blocks-*` | همیشه حذف از فرانت |
| WooCommerce Brands | `brands-styles` | خارج از محصول/برند |
| YITH Brands | `yith-wcbr` | خارج از محصول/برند |
| Woo Wallet | `woo-wallet-style` و استایل‌های datatables/ui | خارج از حساب کاربری |
| WP-PostViews | `wp-postviews-cache` | همیشه حذف |
| Akismet | `akismet-frontend` | همیشه حذف |
| Comments Like Dislike | `cld-frontend`, `cld-font-awesome` | همیشه حذف |
| Rate My Post | `rate-my-post`, `rmp-recaptcha` | همیشه حذف |
| Wordfence | `wordfenceAJAXjs`, `wfi18njs` | همیشه حذف |
| MediaAd | `retargeting.js` | فقط پروداکشن؛ در لوکال لود نمی‌شود |
| mega-menu (قدیمی) | `mega-menu-script` | همیشه حذف |

## نتیجهٔ تست (لوکال)

### صفحهٔ اصلی، مهمان

- CSS: `main-css-css`
- JS: `jquery`, `jquery-migrate`, `dist/front.js`

### چک‌اوت

- اسکریپت‌های ووکامرس و attribution همان‌جا می‌مانند.
- CSS پلاگین/Blocks همچنان حذف است؛ فقط `front.css` تم.

## خارج از این فایل

این cleanup فقط enqueue وردپرس را هدف می‌گیرد. این‌ها هنوز ممکن است در HTML باشند:

- GTM در `header.php` / `footer.php`
- JSON-LD یواست
- اسکریپت/استایل inline دکمهٔ بله در `footer.php`
- `lottie-player` شرطی در `functions.php` برای `product_tag`

## ریسک‌های شناخته‌شده

- شمارش بازدید PostViews در حالت cache/ajax ممکن است از کار بیفتد.
- لایک/دیسلایک کامنت و امتیاز Rate My Post بدون JS پلاگین کار نمی‌کنند.
- اگر صفحه‌ای واقعاً به CSS/JS پلاگین وابسته بود، handle را از لیست حذف در `front-asset-cleanup.php` بردار یا شرط `ez_theme_needs_*` را گسترش بده.

## اگر خواستیم دوباره باز کنیم

1. handle را از `ez_theme_get_front_plugin_script_handles()` یا `ez_theme_get_front_plugin_style_handles()` حذف کن.
2. برای prefixها، `ez_theme_forget_matching_assets()` را محدود کن.
3. برای MediaAd، `ez_theme_should_load_mediaad()` یا شرط `header.php` را تغییر بده.
4. بعد از تغییر، View Source مهمان و یک صفحهٔ فروشگاهی/چک‌اوت را چک کن.

## تاریخ

- 2026-05-11: پیاده‌سازی cleanup گسترده + محدودسازی MediaAd

## صفحه CRM تیم (`/team`) – راهنمای دیباگ روی سرور

این فایل توضیح می‌دهد صفحه `/team` چطور کار می‌کند و برای رفع خطای 404 باید **کدام فایل‌ها و مسیرها** را روی سرور بررسی کنی.

---

## 1. روت و ریرایت `/team`

- **مسیر فایل:**  
  `wp-content/themes/escapezoom-v2/template/team/init.php`

- **کدهایی که باید چک کنی:**
  - ثبت تگ ریرایت و روت سفارشی:
    - `add_rewrite_tag('%team_page%', '([^&]+)');`
    - `add_rewrite_rule('^team/([^/]+)/?', 'index.php?team_page=$matches[1]', 'top');`
  - اکشن اصلی:
    - `add_action('template_redirect', function () { ... });`

- **نکات روی سرور:**
  - مطمئن شو همین فایل با همین محتوا روی سرور دیپلوی شده است.
  - بعد از هر دیپلوی یا تغییر ریرایت، در پنل وردپرس سرور برو به:
    - `Settings > Permalinks` → بدون تغییر، فقط **Save** را بزن تا rewriteها فلش شوند.

---

## 2. رول‌های مجاز و 404 شدن از سمت کد

- **مسیر فایل:**  
  `wp-content/themes/escapezoom-v2/template/team/init.php`

- **بخش‌هایی که باید ببینی:**
  - شرط فیلتر کردن درخواست‌های غیر مربوط به تیم:
    - شرطی که `$requested_path` را با `'team'` مقایسه می‌کند.
  - تعریف رول‌های مجاز:
    - آرایه‌ی `$allowed_roles = ['administrator', 'supervisor', 'poshtiban', 'accounting', 'sales', 'marketing', 'team_admin'];`
  - چک کردن دسترسی و صدا زدن `page_404();` اگر کاربر رول مجاز نداشته باشد.

- **کارهایی که روی سرور باید انجام دهی:**
  - با یک کاربر دارای رول `administrator` وارد سایت شو و `/team` را تست کن.
  - اگر برای ادمین درست بود ولی برای دیگران 404 شد، مشکل از رول‌هاست.

---

## 3. منوی داخلی تیم و اسلاگ‌های معتبر (`team_page`)

- **مسیر فایل:**  
  `wp-content/themes/escapezoom-v2/template/team/init.php`

- **توابع مهم:**
  - `get_team_menu_items()`
  - `get_accessible_team_menu_items()`

- **چه چیزهایی را باید چک کنی:**
  - آرایه‌ی برگشتی `get_team_menu_items()` شامل اسلاگ‌های همه صفحات است (مثل `orders`, `orders2`, `sms`, `withdrawals` و ...).
  - تابع `get_accessible_team_menu_items()` بر اساس رول کاربر، بعضی اسلاگ‌ها را حذف می‌کند.
  - هنگام تست روی سرور:
    - اگر URL مثل `/team/orders2` است، مطمئن شو:
      - اسلاگ `orders2` در `get_team_menu_items()` وجود دارد.
      - برای رول کاربرت در `get_accessible_team_menu_items()` حذف نمی‌شود.

---

## 4. رفتار `/team` بدون اسلاگ (ریدایرکت به صفحه پیش‌فرض)

- **مسیر فایل:**  
  `wp-content/themes/escapezoom-v2/template/team/layout.php`

- **بخش‌های مهم برای بررسی:**
  - گرفتن `team_page`:
    - `$active = get_query_var('team_page');`
  - اگر `$active` خالی باشد:
    - صدا زدن `get_default_team_page_for_user($current_user);`
    - ریدایرکت به `home_url('/team/' . $default_slug);`

- **تابع پیش‌فرض صفحه‌ی هر نقش:**
  - **مسیر تعریف تابع:**  
    `wp-content/themes/escapezoom-v2/template/team/init.php`
  - نام تابع: `get_default_team_page_for_user($user = null): ?string`

- **چک‌لیست روی سرور:**
  - ببین برای نقش کاربری که با آن تست می‌کنی، این تابع چه اسلاگی برمی‌گرداند.
  - مطمئن شو آن اسلاگ:
    - در `get_team_menu_items()` وجود دارد.
    - در `get_accessible_team_menu_items()` برای آن رول قابل دسترس است.

---

## 5. وجود فایل‌های محتوای هر صفحه تیم

- **مسیر پوشه فایل‌های محتوا:**  
  `wp-content/themes/escapezoom-v2/template/team/pages/`

- **الگوی نام فایل‌ها:**
  - برای هر اسلاگ منو (مثلاً `orders2`) باید یک فایل به نام:
    - `wp-content/themes/escapezoom-v2/template/team/pages/orders2.php`
    - و برای بقیه اسلاگ‌ها به همین شکل (`{slug}.php`) وجود داشته باشد.

- **کد مربوطه (برای چک کردن):**
  - مسیر فایل: `template/team/layout.php`
  - بخش:
    - ساختن `$content_file` با استفاده از `$active`.
    - چک `file_exists($content_file)` و در غیر این صورت صدا زدن `page_404();`.

- **روی سرور مطمئن شو که:**
  - همه فایل‌های `pages/*.php` که روی لوکال داری، روی سرور هم وجود دارند و پرمیشن درست دارند.

---

## 6. لود شدن استایل و اسکریپت‌های CRM برای صفحه تیم

- **مسیر فایل:**  
  `wp-content/themes/escapezoom-v2/functions.php`

- **بخش مرتبط:**
  - تابع `add_link_theme_scripts()`
  - شرط:
    - `if (is_page('team')) { wp_enqueue_style('crm-css'); wp_enqueue_script('crm-js'); }`

- **نکته مهم:**
  - این قسمت فقط روی CSS/JS تأثیر دارد (اگر کار نکند، صفحه بدون استایل می‌آید، نه لزوماً 404).
  - با این حال، روی سرور می‌توانی چک کنی:
    - آیا برگه‌ای با اسلاگ `team` در دیتابیس وردپرس سرور هست؟

---

## 7. نقطه ورود AJAX فوق‌سریع برای پنل تیم (در صورت نیاز)

این بخش فقط در صورتی مهم است که در خود پنل `/team` درخواست‌های AJAX مخصوص CRM را دیباگ می‌کنی.

- **مسیر فایل:**  
  `wp-content/ez-ajax.php`

- **بخش‌های مهم:**
  - بررسی توکن:  
    - `ez_ajax_token_verify($data['t']);` با scope = `'team'`
  - اکشن ۱ و ۰ و سایر اکشن‌های مرتبط با تیم:
    - مسیر کال‌بک‌ها:  
      `wp-content/themes/escapezoom-v2/app/ajax/callbacks/team/`

اگر خود صفحه‌ی `/team` باز می‌شود ولی بعضی لیست‌ها/گزارش‌ها خطا می‌دهند، این فایل و کال‌بک‌های داخل پوشه‌ی بالا را روی سرور چک کن.

---

## 8. چک‌لیست کوتاه تفاوت لوکال و سرور

روی **سرور** این مراحل را به ترتیب چک کن:

1. **نقش کاربر**
   - با کاربر `administrator` وارد شو و `/team` را باز کن.

2. **Permalinks**
   - در پنل وردپرس → `Settings > Permalinks` → دکمه **Save**.

3. **فایل‌های زیر واقعاً همان نسخه‌ی لوکال باشند:**
   - `wp-content/themes/escapezoom-v2/template/team/init.php`
   - `wp-content/themes/escapezoom-v2/template/team/layout.php`
   - پوشه‌ی `wp-content/themes/escapezoom-v2/template/team/pages/` (همه فایل‌ها)
   - `wp-content/themes/escapezoom-v2/functions.php`
   - در صورت نیاز به دیباگ AJAX:  
     `wp-content/ez-ajax.php`

4. **URL دقیق تست**
   - `https://your-domain.com/team`
   - و همچنین:
   - `https://your-domain.com/team/{اسلاگ پیش‌فرض نقش}` (از تابع `get_default_team_page_for_user()` در `init.php` بخوان که برای هر نقش چیست).


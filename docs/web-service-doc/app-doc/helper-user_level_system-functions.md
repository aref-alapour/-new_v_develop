# helper/user_level_system/functions.php

**مسیر:** `wp-content/themes/escapezoom-v2/app/functions/helper/user_level_system/functions.php`  
**بارگذاری:** از طریق `app/functions/init.php` (include_once)، قبل از `user_level_system/actions-points.php`.

---

## خلاصه

توابع **سطح کاربر** بر اساس جمع امتیاز: نمایش نشان (badge)، محاسبه سطح، تخفیف سطح، قدرت رتبه‌دهی و دسترسی به قابلیت‌ها (کالکشن، دعوت، لایک، بیو، آواتار). همچنین تابع **add_new_point** برای درج امتیاز در جدول `points` که توسط `actions-points.php` استفاده می‌شود.

---

## توابع

### 1) `user_badge_by_level( $user_data, $classes = '', $arg_type = 'user_id' ): void`

**خطوط تقریبی:** ۳–۲۹.

نمایش یک span با رنگ و پس‌زمینه و متن سطح کاربر (تازه‌وارد، نوپا، با تجربه، کارکشته).

| پارامتر | نوع | توضیح |
|---------|-----|--------|
| `$user_data` | mixed | شناسه کاربر یا عدد سطح (بسته به $arg_type) |
| `$classes` | string | کلاس‌های اضافی برای span |
| `$arg_type` | string | `user_id` یعنی $user_data شناسه کاربر است؛ `user_level` یعنی $user_data خود سطح است |

سطح ۱ → خاکستری، تازه وارد؛ ۲ → سبز، نوپا؛ ۳ → آبی، با تجربه؛ ۴ → نارنجی، کارکشته. خروجی با `echo` چاپ می‌شود.

---

### 2) `get_user_level( $user_id = null ): int`

**خطوط تقریبی:** ۳۱–۴۸.

محاسبه سطح (۱–۴) بر اساس جمع امتیاز کاربر از `get_user_points`. اگر `$user_id` null باشد از `get_current_user_id()` استفاده می‌شود.

آستانه‌ها:  
- تا ۱۵۰ → سطح ۱  
- تا ۷۰۰ → سطح ۲  
- تا ۷۰۰۰ → سطح ۳  
- بالاتر → سطح ۴  

---

### 3) `get_user_discount( int $order_id = 0, $user_id = null ): array`

**خطوط تقریبی:** ۵۰–۸۲.

برگرداندن درصد تخفیف و برچسب (مثلاً برای نمایش در چکاوت). اگر قبلاً در متای سفارش با کلید `user_level_discount` ذخیره شده باشد همان برگردانده می‌شود؛ وگرنه بر اساس سطح محاسبه و در متا ذخیره می‌شود.

- سطح ۳ → ۵٪  
- سطح ۴ → ۱۰٪  
- سطح ۱ و ۲ → در کد فعلی `$discount_percentage` و `$discount_label` فقط برای ۳ و ۴ ست می‌شوند؛ برای ۱ و ۲ باید مقدار پیش‌فرض (مثلاً ۰) در نظر گرفته شود وگرنه ممکن است Notice ایجاد شود.

خروجی: `['percentage' => int, 'label' => string]`.

---

### 4) `get_user_rating_power( $user_id = null ): int`

**خطوط تقریبی:** ۸۴–۹۵.

تعداد رتبه/امتیازی که کاربر می‌تواند بدهد (مثلاً برای نظرات یا ریتینگ). نقشه: سطح ۱ → ۱، ۲ → ۲، ۳ → ۷، ۴ → ۲۰.

---

### 5) `user_features_access( $source ): bool`

**خطوط تقریبی:** ۹۷–۱۶۴.

بررسی دسترسی کاربر به یک قابلیت بر اساس سطح. `$source` یکی از: `collection`, `invitation`, `collection_like`, `bio`, `avatar`.

- سطح ۱: همه false.  
- سطح ۲: collection=1، invitation=3، collection_like=true.  
- سطح ۳: collection=3، invitation=10، collection_like=true، bio=true.  
- سطح ۴: collection=7، invitation=true، collection_like=true، bio=true، avatar=true.

برای `collection` و `invitation` و `collection_like` تعداد واقعی از جداول `collections` و `invitations` خوانده می‌شود و با سقف مقایسه می‌شود؛ در صورت رسیدن به سقف false برمی‌گردد.

---

### 6) `add_new_point( $new_point ): void`

**خطوط تقریبی:** ۱۶۶–۱۷۱.

درج یک رکورد در جدول `points`. آرایه `$new_point` باید شامل ستون‌های جدول (مثلاً user_id, point, action, description) باشد؛ `created_at` در همین تابع با `time()` ست می‌شود. از `$wpdb->insert( 'points', $new_point )` استفاده می‌شود.

---

## استفاده در سایت

- نمایش نشان سطح در پروفایل، کارت کاربر، نظرات.
- اعمال تخفیف سطح در چکاوت یا سبد.
- محدود کردن تعداد کالکشن، دعوت و لایک و نمایش/عدم نمایش بیو و آواتار بر اساس سطح.
- قدرت رتبه‌دهی در بخش نظرات یا امتیازدهی.

---

## وابستگی

- **get_user_points:** در `helper/get_user_points.php` تعریف شده.
- جدول‌های `points`, `collections`, `invitations`.

---

## تداخل با helper/api.php

در `api.php` تابعی به نام `add_new_point` هم وجود دارد با همان نقش (درج در points). اگر هر دو فایل در یک درخواست لود شوند تداخل تعریف پیش می‌آید. بهتر است فقط یک نسخه (اینجا) استفاده شود و در api حذف یا به این تابع ارجاع داده شود.

---

## نحوه تغییر

- **آستانه سطح یا رنگ badge:** مقادیر در `get_user_level` و `user_badge_by_level` را ویرایش کنید.
- **تخفیف سطح ۱ و ۲:** قبل از return در `get_user_discount` برای سطح ۱ و ۲ مقدار پیش‌فرض (مثلاً ۰ و برچسب خالی) ست کنید تا از Notice جلوگیری شود.
- **سقف قابلیت‌ها:** آرایه‌های `$access_map` در `user_features_access` را تغییر دهید.

---

## بهینه‌سازی پیشنهادی

1. در `get_user_discount` برای سطح ۱ و ۲ مقدار پیش‌فرض تعریف کنید.
2. در صورت استفاده مکرر از `get_user_level` در یک درخواست، کش کردن سطح کاربر (مثلاً در متا یا ترنزینت) را در نظر بگیرید.

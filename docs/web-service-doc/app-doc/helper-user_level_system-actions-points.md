# helper/user_level_system/actions-points.php

**مسیر:** `wp-content/themes/escapezoom-v2/app/functions/helper/user_level_system/actions-points.php`  
**بارگذاری:** از طریق `app/functions/init.php` (include_once)، بعد از `user_level_system/functions.php`.

---

## خلاصه

تعریف **نقشه امتیازها** (`$points_map`) و **هوک‌های ووکامرس/کالکشن** برای دادن امتیاز به کاربر در رویدادهای «ثبت سفارش»، «ثبت نظر»، «ایجاد کالکشن» و «لایک کالکشن». امتیازها از طریق تابع `add_new_point` (تعریف‌شده در `user_level_system/functions.php`) در جدول `points` ذخیره می‌شوند.

---

## نقشه امتیازها (`$points_map`)

**خطوط تقریبی:** ۲–۳۷.

| کلید | امتیاز | action (فارسی) | description (پیش‌فرض) |
|------|--------|-----------------|------------------------|
| place_order_leader | 50 | رزرو بازی | رزرو بازی |
| place_order_members | 5 | امتیاز هم گروهی | هم گروهی برای بازی |
| comment_submission | 30 | ثبت نظر | ثبت نظر برای |
| collection_add | 70 | ایجاد کالکشن | ایجاد کالکشن شماره |
| collection_getting_like | 20 | لایک گرفتن کالکشن | لایک گرفتن کالکشن |
| user_registration | 50 | ثبت نام | ثبت نام در سایت |
| user_completing_info | 50 | تکمیل اطلاعات کاربری | تکمیل اطلاعات کاربری |

---

## هوک‌ها و توابع

### 1) بعد از ثبت سفارش (woocommerce_thankyou)

- **هوک:** `woocommerce_thankyou`
- **تابع:** `action_point_order_completed( $order_id )` — خطوط حدود ۴۰–۵۵.
- **منطق:** از سفارش آیتم‌ها و `_customer_user` را می‌خواند؛ یک رکورد امتیاز با نقشه `place_order_leader` و description شامل عنوان آخرین محصول به `add_new_point` می‌دهد. **نکته:** در حلقه فقط `$product_id` آخرین محصول ذخیره می‌شود؛ اگر چند محصول باشد فقط یکی در description می‌آید.

---

### 2) بعد از ثبت نظر (comment_post)

- **هوک:** `comment_post`
- **تابع:** `action_point_comment_leaving( $comment_ID, $comment_approved )` — خطوط حدود ۵۸–۶۸.
- **منطق:** فعلاً فقط `saeed_store( [ $comment_ID, $comment_approved ] )` فراخوانی می‌شود؛ امتیاز واقعی در این تابع داده نمی‌شود (کد امتیاز نظر در شرط‌ها کامنت شده).

---

### 3) ایجاد کالکشن (collection_add)

- **هوک:** `collection_add` (اکشن سفارشی)
- **تابع:** `action_point_collection_add( $user_id )` — خطوط حدود ۷۱–۸۲.
- **منطق:** تعداد کالکشن‌های کاربر از جدول `collections` شمارش می‌شود؛ اگر اولین کالکشن باشد description برابر «ایجاد اولین کالکشن»، وگرنه «ایجاد کالکشن شماره N»؛ امتیاز از `collection_add` نقشه داده می‌شود.

---

### 4) لایک کالکشن (collection_like)

- **هوک:** `collection_like` (اکشن سفارشی)
- **تابع:** `action_point_collection_get_like( $user_id )` — خطوط حدود ۸۴–۹۴.
- **منطق:** یک رکورد امتیاز با نقشه `collection_getting_like` به `add_new_point` داده می‌شود.

---

## استفاده در سایت

- بعد از تکمیل خرید، ثبت نظر، ایجاد کالکشن و لایک کالکشن امتیاز به کاربر تعلق می‌گیرد و در جدول `points` ذخیره می‌شود.
- جمع این امتیازها مبنای سطح کاربر و تخفیف در `user_level_system/functions.php` است.

---

## وابستگی

- **add_new_point:** در `user_level_system/functions.php` تعریف شده؛ باید قبل از این فایل لود شود (در init ترتیب درست است).
- **saeed_store:** برای نظر؛ احتمالاً در جای دیگری تعریف شده (ذخیره/لاگ).
- جدول‌های `points` و `collections`.

---

## توابع/کد مشابه

- `helper/add-point.php`: تابع `add_point` با فیلتر `points_list` و کلیدهای متفاوت (مثلاً place-order-leader با خط تیره). می‌توان نقشه امتیاز را در یک جا (مثلاً فیلتر یا همین نقشه) متمرکز کرد تا تداخل نداشته باشد.

---

## نحوه تغییر

- **تغییر امتیاز یا متن:** مقدارهای `$points_map` را ویرایش کنید.
- **امتیاز برای نظر:** شرط‌های کامنت‌شده در `action_point_comment_leaving` را فعال و با نقشه `comment_submission` و `add_new_point` وصل کنید.
- **چند محصول در سفارش:** در `action_point_order_completed` می‌توان برای هر آیتم یک بار امتیاز داد یا description را به صورت «چند محصول» تنظیم کرد.

---

## بهینه‌سازی پیشنهادی

1. در `action_point_order_completed` مشخص کنید آیا فقط یک امتیاز برای کل سفارش است یا به ازای هر محصول؛ در صورت دوم حلقه را کامل کنید و از دوباره‌کاری با محصول آخر جلوگیری کنید.
2. یکسان‌سازی کلیدهای امتیاز با `add-point.php` (خط تیره یا آندرلاین) و متمرکز کردن لیست در یک منبع.

# helper/custom_product-tag-image_field.php

**مسیر:** `wp-content/themes/escapezoom-v2/app/functions/helper/custom_product-tag-image_field.php`  
**بارگذاری:** از طریق `app/functions/init.php` (include_once).

---

## خلاصه

اضافه کردن فیلد **تصویر** به تاکسونومی **product_tag** (برچسب محصول ووکامرس): در فرم افزودن و ویرایش برچسب، فیلد انتخاب/حذف تصویر نمایش داده می‌شود و مقدار در term meta با کلید `tag-image-id` ذخیره می‌شود. اسکریپت مدیا فقط در صفحات ویرایش/لیست برچسب‌ها و برای تاکسونومی `product_tag` لود می‌شود.

---

## هوک‌ها و توابع

### فرم افزودن برچسب (Add)

- **هوک:** `product_tag_add_form_fields`
- **تابع:** `custom_product_tag_image_field_add( $taxonomy )` — خطوط حدود ۴–۱۶.
- خروجی: یک div با label «تصویر برچسب»، input مخفی `tag-image-id`، div برای پیش‌نمایش، دکمه‌های «انتخاب تصویر» و «حذف تصویر» با کلاس‌های `tag_media_button` و `tag_media_remove`.

### فرم ویرایش برچسب (Edit)

- **هوک:** `product_tag_edit_form_fields`
- **تابع:** `custom_product_tag_image_field_edit( $term, $taxonomy )` — خطوط حدود ۱۹–۳۶.
- مقدار فعلی از `get_term_meta( $term->term_id, 'tag-image-id', true )` خوانده می‌شود؛ در صورت وجود، تصویر با `wp_get_attachment_image( $image_id, 'thumbnail' )` نمایش داده می‌شود.

### ذخیره

- **هوکها:** `created_product_tag`, `edited_product_tag`
- **تابع:** `save_custom_product_tag_image( $term_id, $tt_id )` — خطوط حدود ۴۱–۴۷.
- اگر `$_POST['tag-image-id']` ست باشد، با `absint` در term meta ذخیره می‌شود؛ وگرنه رشته خالی ذخیره می‌شود.

### اسکریپت‌های ادمین

- **هوک:** `admin_enqueue_scripts`
- **تابع:** `custom_product_tag_admin_scripts( $hook )` — خطوط حدود ۵۱–۵۷.
- فقط وقتی `$hook` برابر `edit-tags.php` یا `term.php` و `$_GET['taxonomy'] === 'product_tag'` باشد، Media و اسکریپت `get_stylesheet_directory_uri() . '/js/custom-tag-media.js'` لود می‌شود. این اسکریپت معمولاً دکمه انتخاب مدیا و مقداردهی به input مخفی و حذف تصویر را انجام می‌دهد.

---

## استفاده در سایت

- در پنل ادمین، بخش محصولات → برچسب‌ها: هنگام افزودن یا ویرایش برچسب، فیلد تصویر نمایش داده می‌شود.
- در فرانت، تصویر برچسب را می‌توان با `get_term_meta( $term_id, 'tag-image-id', true )` گرفت و با `wp_get_attachment_image` نمایش داد.

---

## نحوه تغییر

- **تغییر کلید متا:** همه جا `tag-image-id` را با کلید جدید عوض کنید (هم در input و هم در get/update_term_meta).
- **تغییر متن/برچسب:** رشته‌های داخل `_e( '...', 'textdomain' )` را ویرایش کنید.
- **سایز تصویر در ویرایش:** آرگومان دوم `wp_get_attachment_image` را به سایز دلخواه (مثلاً medium) تغییر دهید.

---

## وابستگی

- تم یا پلاگین باید فایل JS در مسیر `js/custom-tag-media.js` (نسبت به دایرکتوری تم) داشته باشد و دکمه‌ها و input مخفی را به مدیا آپلودر وابسته کند. بدون این اسکریپت، دکمه «انتخاب تصویر» ممکن است کار نکند.

---

## بهینه‌سازی پیشنهادی

1. یک بار چک کنید که `custom-tag-media.js` واقعاً در تم وجود دارد و در همان شرایط بالا لود می‌شود.
2. در صورت تمایل به چندزبانگی، `textdomain` را به slug تم یا پلاگین واحد تغییر دهید.
3. برای جلوگیری از ذخیره مقادیر غیرمجاز، در `save_custom_product_tag_image` بررسی کنید مقدار عددی معتبر attachment است (مثلاً با `wp_attachment_is_image`).

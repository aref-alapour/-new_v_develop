# تحلیل و بررسی wp_postmeta - شناسایی موارد قابل حذف

## خلاصه اجرایی
این فایل SQL شامل لیست meta_key های موجود در جدول wp_postmeta به همراه تعداد رکوردها و حجم هر کدام است. در ادامه مواردی که احتمالاً قابل حذف هستند شناسایی شده‌اند.

---

## ⚠️ هشدار مهم
**قبل از حذف هر چیزی، حتماً از دیتابیس بکاپ بگیرید!**

---

## دسته‌بندی meta_key های مشکوک

### 1. Meta Keys با استفاده بسیار کم (1-3 رکورد) - احتمالاً تست یا داده‌های قدیمی

#### Meta Keys با فقط 1 رکورد:
```
- alternative_name (1)
- satisfaction_rate (1)
- _enabled (1)
- _test (1)
- slider__archive_0_arc_slider_nameroom (1)
- slider__archive_0_arc_slider_img_desktop (1)
- slider__archive_0_arc_slider_img_mobile (1)
- slider__archive_0_arc_slider_insert_in (1)
- slider__archive_0_arc_slider_insert_in_shahr (1)
- slider__archive (1)
- slider_tag_0_arc_slider_nameroom (1)
- slider_tag (1)
- poshtibanha_0_poshtiban_name (1)
- poshtibanha_0_phone_pohstiban (1)
- poshtibanha_0_poshtiban_active (1)
- poshtibanha_0_poshtiban_joda_konnade (1)
- poshtibanha_1_* (همه با 1 رکورد)
- poshtibanha_2_* (همه با 1 رکورد)
- poshtibanha_3_* (همه با 1 رکورد)
- poshtibanha_4_* (همه با 1 رکورد)
- poshtibanha_5_* (همه با 1 رکورد)
- poshtibanha_6_* (همه با 1 رکورد)
- poshtibanha (1)
- main_features (1)
- elementor_font_files (1)
- elementor_font_face (1)
- _wpcode_auto_insert_number (1)
- _wpcode_library_id (1)
- status_icon (1)
- background_color (1)
- is_status_paid (1)
- _enable_action_status (1)
- _enable_bulk (1)
- _email_type (1)
- icon_code (1)
- _elementor_global_widget_included_posts (1)
- _filters (1)
- _wp_attachment_context (1)
- special_day (3)
- instant_off (1)
- _wp_suggested_privacy_policy_content (5)
- _yoast_wpseo_is_cornerstone (1)
- ticket_rate (1)
- _yoast_wpseo_meta-robots-adv (2)
- _yoast_wpseo_twitter-image-id (4)
- _rocket_exclude_lazyload (2)
- tedad_sale_not_used (1)
- _elementor_popup_display_settings (3)
- _elementor_conditions (1)
- _slider_home (3)
- _elementor_template_widget_type (2)
- bunch1 (1)
- bunch2 (1)
- _yoast_wpseo_schema_article_type (3)
- offer_shomal_tehran (1)
- offer_gharb_tehran (1)
- offer_sharq_tehran (1)
- offer_karaj (1)
- other_karaj (1)
- add_to_sms_line1 (1)
- add_to_sms_line2 (1)
- add_to_sms_line3 (1)
- meta-box-text (1)
- lg_poster_meta_id (1)
- lg_poster_meta (1)
- sm_poster_meta_id (1)
- sm_poster_meta (1)
- title_meta (1)
- expert_meta (1)
- game_genre_meta (1)
- game_theme_meta (1)
- game_time_meta (1)
- game_age_meta (1)
- game_hard_meta (1)
- game_expert_meta (1)
- teaser_game_repeat_group (1)
- gallery_game_repeat_group (1)
- feather1_active (1)
- feather2_active (1)
- feather3_active (1)
- cast_game_repeat_group (1)
- game_title_content_meta (1)
- game_expert_content_meta (1)
- game_img_cart1_content_meta_id (1)
- game_img_cart1_content_meta (1)
- game_title_cart1_content_meta (1)
- game_content_cart1_content_meta (1)
- game_img_cart2_content_meta_id (1)
- game_img_cart2_content_meta (1)
- game_title_cart2_content_meta (1)
- game_content_cart2_content_meta (1)
- game_img_cart3_content_meta_id (1)
- game_img_cart3_content_meta (1)
- game_title_cart3_content_meta (1)
- game_content_cart3_content_meta (1)
- feather1_title (1)
- feather1_content (1)
- feather2_poster_id (1)
- feather2_poster (1)
- feather2_title (1)
- feather2_content (1)
- feather2_btn_link (1)
- feather3_poster_id (1)
- feather3_poster (1)
- feather3_title (1)
- feather3_content (1)
- feather3_btn_link (1)
- faq_game_fields_group (1)
- feather1_back_lg_id (1)
- feather1_back_lg (1)
- feather1_back_sm_id (1)
- feather1_back_sm (1)
- lg_background_meta_id (1)
- lg_background_meta (1)
- sm_background_meta_id (1)
- sm_background_meta (1)
- poshtiban_n1 (1)
- poshtiban_n2 (1)
- poshtiban_n3 (1)
- poshtiban_n4 (1)
- poshtibanha_0_tozihat_poshtiban (1)
- poshtibanha_1_tozihat_poshtiban (1)
- poshtibanha_2_tozihat_poshtiban (1)
- poshtibanha_3_tozihat_poshtiban (1)
- poshtibanha_4_tozihat_poshtiban (1)
- poshtibanha_5_tozihat_poshtiban (1)
- poshtibanha_6_tozihat_poshtiban (1)
- sans_vije_6_* (همه با 1 رکورد)
- _wc_order_attribution_utm_campaign (1)
- _wc_order_attribution_utm_content (1)
- تصویر_کوچک (1)
- _تصویر_کوچک (1)
- متن_کوتاه (1)
- _متن_کوتاه (1)
- اسلایدر (1)
- _اسلایدر (1)
- آدرس (1)
- _آدرس (2)
```

**توصیه:** این موارد احتمالاً داده‌های تست یا پست‌های قدیمی هستند. قبل از حذف، بررسی کنید که آیا پست‌های مربوطه هنوز فعال هستند یا نه.

---

### 2. Meta Keys مربوط به Product ID خاص (product-5304-*)

```
- product-5304-product-review (32)
- bsf-schema-pro-rating-5304 (1)
- bsf-schema-pro-review-counts-5304 (1)
- bsf-schema-pro-reviews-5304 (1)
- product-5304-name-fieldtype (32)
- product-5304-name (32)
- product-5304-brand-name-fieldtype (32)
- product-5304-brand-name (32)
- product-5304-image-fieldtype (32)
- product-5304-image (32)
- product-5304-url-fieldtype (32)
- product-5304-url (32)
- product-5304-description-fieldtype (32)
- product-5304-description (32)
- product-5304-sku-fieldtype (32)
- product-5304-sku (32)
- product-5304-mpn-fieldtype (32)
- product-5304-mpn (29)
- product-5304-avail-fieldtype (32)
- product-5304-avail (13)
- product-5304-price-valid-until-fieldtype (32)
- product-5304-price-valid-until (32)
- product-5304-price-fieldtype (32)
- product-5304-price (32)
- product-5304-currency-fieldtype (32)
- product-5304-currency (32)
- product-5304-rating-fieldtype (32)
- product-5304-rating (32)
- product-5304-review-count-fieldtype (32)
- product-5304-review-count (32)
```

**توصیه:** اینها مربوط به یک محصول خاص (ID: 5304) هستند. اگر این محصول دیگر استفاده نمی‌شود یا پلاگین مربوطه غیرفعال است، می‌توانید حذف کنید.

---

### 3. Meta Keys تکراری (با و بدون underscore)

برخی meta_key ها هم با `_` و هم بدون آن وجود دارند. معمولاً نسخه با `_` نسخه اصلی است:

#### موارد تکراری مشکوک:
```
- room_address (1972) و _room_address (1981) - احتمالاً یکی کافی است
- room_video (1849) و _room_video (1980)
- room_video_embed (1853) و _room_video_embed (1980)
- room_loc (1971) و _room_loc (1981)
- room_tedad (1973) و _room_tedad (1981)
- room_level (1981) و _room_level (1981)
- room_age_limit (1981) و _room_age_limit (1981)
- room_lat (1957) و _room_lat (1981)
- room_long (1957) و _room_long (1981)
- room_phone (1949) و _room_phone (1981)
- room_phone_2 (1859) و _room_phone_2 (1981)
- room_callnumber (1888) و _room_callnumber (1981)
- room_duration (1972) و _room_duration (1981)
- room_scary (288) و _room_scary (288)
- room_cover (93) و _room_cover (453)
- room_strengths (28) و _room_strengths (453)
- special_room (1971) و _special_room (1966)
- sale_active (1962) و _sale_active (1963)
- ez_viewpost (1975) و _ez_viewpost (1964)
- user_ebtal (1900) و _user_ebtal (1962)
- start_time (1893) و _start_time (1948)
- end_time (1893) و _end_time (1948)
- auto_disable (1896) و _auto_disable (1947)
- price_asli (1893) و _price_asli (1948)
- off_days_rep (1858) و _off_days_rep (1948)
- gap (1947) و _gap (1947)
- sheba (1849) و _sheba (1946)
- pish_pardakht (1860) و _pish_pardakht (1943)
- email_compiler (1849) و _email_compiler (1943)
- whatsapp_api (1849) و _whatsapp_api (1943)
- scenario (1874) و _scenario (1943)
- sans_vije (1867) و _sans_vije (1943)
- descktop__banner (1852) و _descktop__banner (1940)
- mobile__banner (1852) و _mobile__banner (1940)
- darsad (1940) و _darsad (1940)
- payamak_2 (1850) و _payamak_2 (1935)
- maj_telegram (1850) و _maj_telegram (1935)
- norouzaneh (1932) و _norouzaneh (1932)
- shakes_room (2164) و _shakes_room (1948)
- product_state (1920) و _product_state (1920)
- ez_plus (1927) و _ez_plus (1947)
- ez_sale_term (1382) و _ez_sale_term (1579)
- ez_sale_price (86) و _ez_sale_price (89)
- ez_room_price (89) و _ez_room_price (357)
- ez_room_sale_price (89) و _ez_room_sale_price (357)
- tedad_sale (497) و _tedad_sale (20)
- tedad_not_ebtal (1024) و _tedad_not_ebtal (1271)
- all_sale (1024) و _all_sale (1271)
- job_brand (246) و _job_brand (151)
- job_desc (261) و _job_desc (151)
- job_tamas (257) و _job_tamas (151)
- pish_pardakht_per_person (1790) و _pish_pardakht_per_person (1785)
- sans_manager (1570) و _sans_manager (1570)
```

**توصیه:** بررسی کنید که کدام نسخه در کد استفاده می‌شود. معمولاً نسخه با `_` نسخه اصلی است و نسخه بدون `_` ممکن است قدیمی باشد.

---

### 4. Meta Keys مربوط به پلاگین‌های قدیمی یا غیرفعال

#### Gravity Forms Advanced Post Creation:
```
- _gform-form-id (113)
- _gravityformsadvancedpostcreation_entry_id (113)
- _gravityformsadvancedpostcreation_feed_id (113)
```
**بررسی:** اگر از این پلاگین استفاده نمی‌کنید، قابل حذف است.

#### WooCommerce Deposits:
```
- _enable_deposit (1)
- _force_deposit (1)
- _create_balance_orders (1)
- _deposit_default (1)
- _deposit_expiration_product_fallback (1)
- _wc_deposits_enable_deposit (3)
- _wc_deposits_force_deposit (3)
- _wc_deposits_amount_type (3)
- _wc_deposits_deposit_amount (3)
- _wc_deposits_payment_plans (3)
```
**بررسی:** اگر از قابلیت پیش‌پرداخت استفاده نمی‌کنید، قابل حذف است.

#### WP Rocket:
```
- _rocket_exclude_lazyload_iframes (9)
- _rocket_exclude_minify_css (3)
- _rocket_exclude_lazyload (2)
```
**بررسی:** اگر از WP Rocket استفاده نمی‌کنید، قابل حذف است.

#### Rate My Post:
```
- rmp_vote_count (191)
- rmp_rating_val_sum (191)
- rmp_avg_rating (191)
```
**بررسی:** اگر از این پلاگین استفاده نمی‌کنید، قابل حذف است.

---

### 5. Meta Keys با حجم بالا که ممکن است قدیمی باشند

#### comment_ids - حجم بسیار بالا (2369.66 MB):
```
- comment_ids (1365 رکورد، 2369.66 MB)
```
**توصیه:** این حجم بسیار بالاست. بررسی کنید که آیا این داده‌ها هنوز استفاده می‌شوند یا خیر.

#### comments_blacklist و comments_blacklist_new:
```
- comments_blacklist (1114 رکورد، 2.06 MB)
- comments_blacklist_new (580 رکورد، 0.32 MB)
```
**توصیه:** اگر `comments_blacklist_new` جایگزین `comments_blacklist` شده، می‌توانید نسخه قدیمی را حذف کنید.

---

### 6. Meta Keys مربوط به off_days_rep (تکرارهای زیاد)

این meta_key ها برای روزهای تعطیل تکرار شده‌اند:
```
- off_days_rep_0_* (270 رکورد)
- off_days_rep_1_* (271 رکورد)
- off_days_rep_2_* (271 رکورد)
- off_days_rep_3_* (268 رکورد)
- off_days_rep_4_* (266 رکورد)
- off_days_rep_5_* (240 رکورد)
- off_days_rep_6_* (240 رکورد)
```

**توصیه:** اینها احتمالاً داده‌های معتبر هستند، اما اگر سیستم تغییر کرده و دیگر استفاده نمی‌شوند، قابل حذف هستند.

---

### 7. Meta Keys مربوط به sans_vije (تکرارهای زیاد)

```
- sans_vije_0_* (577 رکورد)
- sans_vije_1_* (296 رکورد)
- sans_vije_2_* (102 رکورد)
- sans_vije_3_* (31 رکورد)
- sans_vije_4_* (9 رکورد)
- sans_vije_5_* (3 رکورد)
- sans_vije_6_* (1 رکورد)
```

**توصیه:** اینها احتمالاً داده‌های معتبر هستند. فقط نسخه 6 با 1 رکورد مشکوک است.

---

## دستورات SQL پیشنهادی برای حذف

### ⚠️ هشدار: قبل از اجرا، حتماً بکاپ بگیرید!

```sql
-- 1. حذف meta keys با 1 رکورد (بعد از بررسی)
-- ابتدا بررسی کنید که پست‌های مربوطه هنوز فعال هستند یا نه

-- 2. حذف product-5304-* (اگر محصول دیگر استفاده نمی‌شود)
DELETE FROM wp_postmeta WHERE meta_key LIKE 'product-5304-%';

-- 3. حذف bsf-schema-pro-5304-* (اگر پلاگین غیرفعال است)
DELETE FROM wp_postmeta WHERE meta_key LIKE 'bsf-schema-pro-%';

-- 4. حذف game_* meta keys (اگر فقط 1 رکورد دارند و استفاده نمی‌شوند)
DELETE FROM wp_postmeta WHERE meta_key LIKE 'game_%' AND meta_key IN (
    'game_genre_meta', 'game_theme_meta', 'game_time_meta', 
    'game_age_meta', 'game_hard_meta', 'game_expert_meta'
);

-- 5. حذف poshtibanha_* (اگر فقط 1 رکورد دارند)
DELETE FROM wp_postmeta WHERE meta_key LIKE 'poshtibanha%' 
AND meta_key NOT IN (
    SELECT meta_key FROM (
        SELECT meta_key, COUNT(*) as cnt 
        FROM wp_postmeta 
        WHERE meta_key LIKE 'poshtibanha%' 
        GROUP BY meta_key 
        HAVING cnt > 1
    ) AS subquery
);

-- 6. حذف slider__archive_* و slider_tag_* (اگر فقط 1 رکورد دارند)
DELETE FROM wp_postmeta WHERE meta_key LIKE 'slider__archive%' OR meta_key LIKE 'slider_tag%';

-- 7. حذف meta keys مربوط به پلاگین‌های غیرفعال (بعد از بررسی)
-- مثال: اگر Gravity Forms Advanced Post Creation غیرفعال است:
-- DELETE FROM wp_postmeta WHERE meta_key LIKE '_gform-%' OR meta_key LIKE '_gravityformsadvancedpostcreation%';
```

---

## مراحل پیشنهادی برای پاکسازی

1. **بکاپ کامل دیتابیس** بگیرید
2. **بررسی پلاگین‌های فعال:** لیست پلاگین‌های فعال را بررسی کنید
3. **جستجوی استفاده در کد:** برای هر meta_key مشکوک، در کد جستجو کنید
4. **تست در محیط توسعه:** ابتدا در محیط تست اجرا کنید
5. **حذف تدریجی:** به جای حذف یکجا، به صورت دسته‌ای حذف کنید
6. **مانیتورینگ:** بعد از حذف، سایت را بررسی کنید

---

## Meta Keys که باید نگه دارید (استفاده می‌شوند)

بر اساس بررسی کد، این meta_key ها در حال استفاده هستند:

- `players_phone` - استفاده در checkout و thankyou
- `product_scenario` - استفاده در single-product و API
- `product_rules` - استفاده در single-product و API
- `room_address` - استفاده در single-product و API
- `room_video_embed` - استفاده در single-product و API
- `comments_blacklist` - استفاده در saeed-codes.php
- `product_introduction_text` - استفاده در API
- `product_rules` - استفاده در API
- و سایر meta_key های مربوط به WooCommerce و Elementor

---

## نتیجه‌گیری

**تخمین حجم قابل آزادسازی:**
- Meta keys با 1 رکورد: حدود 0.1-0.2 MB
- product-5304-*: حدود 0.5-1 MB
- Meta keys تکراری (اگر نسخه قدیمی حذف شود): 5-10 MB
- comment_ids (اگر قدیمی باشد): 2369.66 MB ⚠️

**توصیه نهایی:** 
1. ابتدا meta keys با 1 رکورد را بررسی و حذف کنید
2. سپس product-5304-* را بررسی کنید
3. در نهایت comment_ids را بررسی کنید (حجم بسیار بالاست)

---

**تاریخ تحلیل:** 2025-12-24
**نسخه:** 1.0


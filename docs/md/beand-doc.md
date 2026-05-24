گزارش کامل بخش برندها در پروژه EscapeZoom

۱. آدرس‌ها و ساختار URL

آدرسنقش/blog/product-brands/{slug}/ (مثلاً terrible-escape)صفحه تک‌برند (آرشیو تاکسونومی)/brands/لیست برندها (صفحه سفارشی، نه آرشیو تاکسونومی)/wp-admin/term.php?taxonomy=yith_product_brand&post_type=product&tag_ID={id}ویرایش برند در ادمین





product-brands از پلاگین YITH می‌آید: $brands_rewrite = 'product-brands' در class-yith-wcbr.php.

blog از تنظیم Permalink وردپرس است (با with_front => true پیشوند ساختار لینک اضافه می‌شود؛ اگر ساختار با blog باشد، نهایی می‌شود: blog/product-brands/slug).





۲. هویت برند در سیستم

برند = تاکسونومی وردپرس با نام yith_product_brand (ثبت‌شده توسط پلاگین YITH WooCommerce Brands Add-On).

ذخیره‌سازی:

wp_terms: term_id, name, slug

wp_term_taxonomy: term_id, taxonomy = 'yith_product_brand'

wp_term_relationships: اتصال محصول (object_id) به برند (term_taxonomy_id)

wp_termmeta: برای هر ترم برند فقط thumbnail_id (لوگو) از طریق YITH ذخیره می‌شود.

اتصال به کاربر: در کد فعلی هیچ رابطهٔ مستقیم کاربر ↔ برند نیست. رابطه این است: کاربر (مجموعه‌دار) ↔ مالک محصول (user_ebtal) و محصول ↔ برند (ترم). پس برند فقط از طریق محصول به کاربر وصل است.





۳. دیتایی که برای هر برند ذخیره می‌شود

۳.۱ دیتای استاندارد (وردپرس + YITH)

منبعفیلدتوضیحwp_termsname, slugنام و اسلاگ برندwp_term_taxonomydescriptionتوضیحات (در ویرایش ترم در ادمین)wp_termmetathumbnail_idشناسه attachment لوگو (ثبت توسط YITH در فرم افزودن/ویرایش برند)





۳.۲ دیتای اضافه (ACF برای ترم برند)

در قالب فقط خواندن این فیلدها با get_field(..., "{$taxonomy}_{$id}") انجام شده؛ پس محل ذخیره در ACF است (گروه فیلد برای تاکسونومی yith_product_brand):

فیلد ACFکاربرد در قالبteamآرایهٔ اعضا: هر عضو avatar (ID تصویر), name, position — در صفحه برند بخش «اعضاء»brand_type_gamesآرایه: هر آیتم عنوان_تایپ (مثل اتاق فرار، سینما ترس) — زیر عنوان برندbrands_location_addمتن آدرس/موقعیت — کنار آیکون مکان





۳.۳ متادیتای سفارشی تم (wp_termmeta)

meta_keyمنبعکاربردbrand_reputationsaeed-codes.php: جمع tickets_sold محصولات آن برندمرتب‌سازی برندها در صفحه «برندها» (محبوب‌ترین / جدیدترین)









۴. نحوهٔ ساخت صفحات برند

۴.۱ صفحه تک‌برند: /blog/product-brands/{slug}/

قالب: taxonomy-yith_product_brand.php

جریان:

وردپرس با rewrite تاکسونومی product-brands و slug، ترم را تشخیص می‌دهد و get_queried_object() همان ترم برند است.

از ترم: term_id, name, description.

از wp_termmeta: thumbnail_id → تصویر لوگو.

از ACF ترم: team, brand_type_games, brands_location_add.

لیست محصولات برند: فراخوانی web-service با type => 'sort_products_get' و params => ['brand_id' => $id]؛ خروجی HTML سوییپر از web-service.

بخش معرفی از $brand->description؛ بخش اعضا از $team.

۴.۲ صفحه لیست برندها: /brands/

فایل: page-brands.php (صفحه با اسلاگ brands).

داده: get_terms('yith_product_brand', ...) با ترتیب بر اساس meta_key => 'brand_reputation' (یا بدون آن برای «جدیدترین‌ها» با ?order=new).

نمایش: گرید کارت‌ها؛ هر کارت: لینک به get_term_link($brand)، تصویر از thumbnail_id، نام، تعداد محصول ($brand->count).

۴.۳ ادمین: ویرایش برند

URL: term.php?taxonomy=yith_product_brand&post_type=product&tag_ID=691

منو: محصولات → برندها (از YITH).

فرم استاندارد وردپرس: نام، slug، توضیحات.

YITH: فیلد آپلود لوگو → ذخیره در wp_termmeta با کلید thumbnail_id.

ACF: اگر گروه فیلد برای این تاکسونومی تعریف شده باشد، فیلدهای team, brand_type_games, brands_location_add در همان صفحه ویرایش ترم نمایش و ذخیره می‌شوند.





۵. ارتباط با بقیهٔ بخش‌های پروژه

۵.۱ محصول (ووکامرس)

هر محصول با wp_term_relationships به یک (یا چند) برند وصل است.

در تم/سینک یک کپی هم در wp_postmeta با کلید product_brand (مقدار = term_id) نگه داشته می‌شود:

پر شدن: در saeed-codes.php هنگام ذخیره محصول از tax_input['yith_product_brand'] و در یک اسکریپت با update_product_brand.

استفاده: در سینک به products_data (فیلد brand_id) و در wp_products_search (ستون product_brand به صورت JSON: id, name, slug, image).

۵.۲ جستجو

main-search-ajax.php: از wp_products_search خوانده می‌شود؛ product_brand به صورت JSON دیکد و برای نمایش/لینک برند در نتایج جستجو استفاده می‌شود.

۵.۳ web-service (دیتابیس queries)

products_data: ستون brand_id (عدد = term_id) از سینک وردپرس پر می‌شود.

sort_products_get: فیلتر با brand_id؛ محصولات همان برند برگردانده می‌شوند (برای صفحه برند و هر جای دیگری که لیست محصولات برند لازم است).

۵.۴ پنل تیم / گزارش‌ها

withdrawals, withdrawals_search: برای هر تراکنش، برند از طریق محصول مرتبط با آن تراکنش با get_the_terms(..., 'yith_product_brand') گرفته و نمایش داده می‌شود (ستون «برند» برای مجموعه‌دار).

collections_owners_wallet_get, cancellation_requests_get, games_info_get, user_cancellation_: همین الگو؛ نام/اطلاعات برند فقط برای نمایش از روی محصول.

games_info.php: لینک ویرایش برند به term.php?taxonomy=yith_product_brand&post_type=product&tag_ID=....

۵.۵ چک‌اوت، بلیط، thank you

thankyou.php, ticket.php: نام برند بازی با get_the_terms($product_id, 'yith_product_brand') برای نمایش در رسید/بلیط.

۵.۶ API (inc/api/callbacks.php)

brand_get_api: یک برند با id یا slug؛ محصولاتش با sort_products_get و brand_id.

brand_get_all_api: لیست برندها با get_terms('yith_product_brand') و صفحه‌بندی.

در سایر APIها که محصول برمی‌گردانند، گاهی نام/اطلاعات برند با get_the_terms(..., 'yith_product_brand') اضافه می‌شود.

۵.۷ لینک کوتاه (api-shortener)

هوک‌های created_yith_product_brand و edited_yith_product_brand: بعد از ایجاد/ویرایش برند، URL اصلی get_term_link(..., 'yith_product_brand') به سرویس لینک کوتاه ارسال می‌شود.

۵.۸ محبوبیت برند (brand_reputation)

در saeed-codes.php با یک اسکریپت/درخواست خاص: برای یک برند، جمع tickets_sold (postmeta) محصولات آن برند محاسبه و در wp_termmeta با کلید brand_reputation ذخیره می‌شود؛ بعد در page-brands.php برای مرتب‌سازی «محبوب‌ترین‌ها» استفاده می‌شود.





۶. خلاصه ارتباطات (نقشه)





وردپرس

├── تاکسونومی yith_product_brand (ثبت توسط YITH)

│   ├── wp_terms (name, slug)

│   ├── wp_term_taxonomy (description)

│   ├── wp_termmeta: thumbnail_id (YITH), brand_reputation (تم)

│   └── ACF ترم: team, brand_type_games, brands_location_add

├── محصول ← term_relationships → برند

├── wp_postmeta محصول: product_brand = term_id (کپی برای سرعت)

└── قالب‌ها

    ├── taxonomy-yith_product_brand.php → صفحه تک‌برند

    ├── page-brands.php → لیست برندها (get_terms + brand_reputation)

    └── لینک ویرایش → term.php?taxonomy=yith_product_brand&tag_ID=...



سینک/جستجو

├── wp_products_search.product_brand (JSON: id,name,slug,image) ← auto-sync-products

├── products_data.brand_id (int) ← saeed-codes / cron

└── main-search-ajax: خواندن از wp_products_search



web-service

├── sort_products_get با brand_id → فیلتر از products_data

└── دادهٔ اولیه products_data از وردپرس (brand_id از product_brand)



پنل تیم / گزارش

└── برند همیشه از روی محصول: get_the_terms($product_id, 'yith_product_brand')













۷. اگر برند را جدا و بدون افزونه/ووکامرس بخواهی

برای «دیتای برندها کامل جداگانه» و «خودت با کد، بدون افزونه و بدون brand ووکامرس» این کارها لازم است:

جدول/هویت جدید برند

مثلاً جدول ez_brands (id, name, slug, image_url, description, reputation, meta/json برای team و نوع بازی و آدرس و هر چیز دیگر). دیگر از yith_product_brand و wp_terms استفاده نشود.

اتصال محصول به برند

به‌جای term_relationships: یک FK در جدول محصولات (مثلاً در ez_products یا در wp_postmeta با کلید ثابت مثل ez_brand_id) به ez_brands.id. در حالت فعلی همان product_brand (term_id) را می‌توانی در یک فاز مهاجرت به ez_brand_id نگاشت کنی.

صفحات و URL

یک CPT یا صفحهٔ ثابت + query var برای «برند» با ساختار URL دلخواه (مثلاً همان /blog/product-brands/{slug}/ یا /brands/{slug}/) و یک template که از ez_brands (یا مدل Eloquent برند) بخواند.

صفحه لیست برندها همان منطق page-brands.php با مرتب‌سازی بر اساس reputation/تاریخ، ولی داده از ez_brands.

ادمین

منوی سفارشی یا صفحهٔ ادمین برای CRUD برند (نام، slug، لوگو، توضیح، team، نوع بازی، آدرس، reputation) که مستقیم روی ez_brands (و در صورت نیاز یک جدول/متا برای team و نوع بازی) کار کند؛ بدون استفاده از term.php و بدون taxonomy ووکامرس.

جایگزینی همهٔ نقاط استفاده

هر جا که الان get_the_terms(..., 'yith_product_brand') یا get_term_meta(..., 'thumbnail_id') یا get_field(..., "yith_product_brand_{$id}") استفاده شده (لیست بالا: جستجو، پنل تیم، چک‌اوت، بلیط، API، سینک products_data و wp_products_search، لینک کوتاه، brand_reputation) باید به «خواندن از ez_brands / مدل برند» یا از همان postmeta/جدول محصول با ez_brand_id عوض شود.

سینک و web-service

wp_products_search: به‌جای JSON برند از تاکسونومی، از جدول ez_brands پر شود (یا از همان ez_products.brand_id + join).

products_data: به‌جای brand_id به term_id، به ez_brands.id (یا همان شناسهٔ جدول جدید) اشاره کند و sort_products_get با همین شناسه فیلتر کند.

حذف وابستگی

غیرفعال/حذف پلاگین YITH و حذف استفاده از taxonomy ووکامرس برای برند (در ووکامرس دیگر از «برند» به‌عنوان taxonomy محصول استفاده نشود).

اگر بخوای، مرحلهٔ بعد می‌تواند یک لیست دقیق فایل‌به‌فایل برای جایگزینی (با نام تابع و خط تقریبی) یا یک طرح مهاجرت داده از wp_terms/wp_termmeta به ez_brands باشد.
# ستون‌های لیست سفارش ووکامرس

## `custom_shop_order_column`

| مورد | توضیح |
|------|--------|
| **تابع در saeed-codes.php** | `custom_shop_order_column( $columns )` |
| **جایگزین چه تابعی** | تابع اصلی است. |
| **کارایی** | روی فیلتر `manage_edit-shop_order_columns` (اولویت ۲۰) است. بعد از ستون `order_status` دو ستون «سانس» و «سپرده» به لیست سفارش‌های ووکامرس در ادمین اضافه می‌کند. |
| **بهینه‌سازی** | ۱) متن‌های ترجمه‌پذیر را داخل فایل زبان قالب بگذار. ۲) اگر در آینده ستون دیگری اضافه شد، آن‌ها را از یک آرایهٔ config بخوان تا یک نقطهٔ تغییر باشد. |

---

## `custom_shop_order_sortable_columns`

| مورد | توضیح |
|------|--------|
| **تابع در saeed-codes.php** | `custom_shop_order_sortable_columns( $sortable_columns )` |
| **جایگزین چه تابعی** | تابع اصلی است. |
| **کارایی** | ستون‌های `sans_time` و `deposit` را به لیست ستون‌های قابل مرتب‌سازی اضافه می‌کند تا کاربر بتواند لیست سفارش را بر اساس سانس یا سپرده مرتب کند. |
| **بهینه‌سازی** | اگر مرتب‌سازی واقعی (ORDER BY) در کوئری لیست سفارش پیاده نشده، این فیلتر فقط ظاهری است؛ در آن صورت یا ORDER BY را در `pre_get_posts` / فیلتر مناسب اضافه کن یا sortable را بردار تا گیج‌کننده نباشد. |

---

## `custom_orders_list_column_content`

| مورد | توضیح |
|------|--------|
| **تابع در saeed-codes.php** | `custom_orders_list_column_content( $column, $post_id )` |
| **جایگزین چه تابعی** | تابع اصلی است. |
| **کارایی** | روی اکشن `manage_shop_order_posts_custom_column` (۲ آرگومان) است. برای ستون `sans_time`: با cache استاتیک و در صورت وجود Medoo از آن جدول `wp_zb_booking_history` می‌خواند، وگرنه با `ez_reservation( query_execution )`؛ سپس زمان را با `wp_date` فرمت می‌کند. برای ستون `deposit`: از متای `_order_total_2` یا `_order_total` استفاده و با `number_format` و «تومان» نمایش می‌دهد. |
| **بهینه‌سازی** | ۱) برای کاهش درخواست به reservation، در محیطی که Medoo در دسترس است همیشه از Medoo استفاده کن و fallback به ez_reservation را فقط وقتی Medoo نبود. ۲) مقدار deposit را در یک متای واحد (مثلاً فقط `_order_total_2` با fallback به `_order_total`) نرمال کن تا یک نقطهٔ حقیقت باشد. ۳) اگر لیست سفارش خیلی طولانی است، برای ستون سانس از یک bulk query برای همهٔ post_idهای صفحه استفاده کن به‌جای یکی‌یکی. |

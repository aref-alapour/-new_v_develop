# تحلیل کامل تابع calculate_and_update_order_financials

## مقدمه
این تابع برای محاسبه و آپدیت اطلاعات مالی یک سفارش طراحی شده است. تمام اطلاعات از جدول `wp_markting` خوانده می‌شود و محاسبات بر اساس آن انجام می‌شود.

## هدف تابع
- محاسبه `order_finall_price` (قیمت نهایی)
- محاسبه `order_net_profit` (درآمد/کمیسیون)
- محاسبه `order_tax` (مالیات بر ارزش افزوده)
- واریز به کیف پول owner (صاحب بازی - game_user_ebtal_id)
- تغییر status سفارش به `wc-walletx`
- آپدیت متا محصول
- امتیازدهی به سرگروه و همگروهی‌ها

---

## تحلیل خط به خط

### خط 1023: تعریف تابع
```php
function calculate_and_update_order_financials($order_id)
```
- **پارامتر**: `$order_id` - شناسه سفارش
- **هدف**: محاسبه و آپدیت اطلاعات مالی یک سفارش
- **نکته مهم**: تابع مستقل از global variables است
  - وابستگی به `$wldb` حذف شده است
  - تمام عملیات با استفاده از medoo انجام می‌شود

### خطوط 1027-1031: اتصال به دیتابیس
```php
$medoo = medoo();
if (!$medoo) {
    error_log("Failed to connect to database using medoo in calculate_and_update_order_financials for order_id: $order_id");
    return false;
}
```
- ایجاد instance از medoo
- اگر اتصال برقرار نشد، error log می‌کند و `false` برمی‌گرداند

### خطوط 1033-1045: دریافت اطلاعات از wp_markting
```php
$order_data = $medoo->get('wp_markting', [
    'order_paid',
    'order_payment_type',
    'order_prepaid_tickets',
    'order_tickets_quantity',
    'game_product_type',
    'game_user_ebtal_id',
    'game_id',
    'customer_id',
    'customer_phone',
    'order_phones'
], ['order_id' => $order_id]);
```
- **فیلدهای دریافت شده**:
  - `order_paid`: مبلغ پرداختی (پیش پرداخت)
  - `order_payment_type`: نوع پرداخت (partial/full)
  - `order_prepaid_tickets`: تعداد تیکت پیش پرداخت
  - `order_tickets_quantity`: تعداد کل تیکت‌ها
  - `game_product_type`: نوع محصول (برای تعیین نرخ کمیسیون)
  - `game_user_ebtal_id`: شناسه owner (صاحب بازی - از user_ebtal)
  - `game_id`: شناسه بازی
  - `customer_id`: شناسه مشتری
  - `customer_phone`: شماره تلفن مشتری (سرگروه)
  - `order_phones`: شماره تلفن‌های بازیکنان

### خطوط 1047-1050: بررسی وجود سفارش
```php
if (!$order_data) {
    error_log("Order not found in wp_markting table for order_id: $order_id");
    return false;
}
```
- اگر سفارش در `wp_markting` پیدا نشد، error log می‌کند و `false` برمی‌گرداند

### خطوط 1052-1062: استخراج اطلاعات
```php
$order_paid = $order_data['order_paid'] ?? null;
$order_payment_type = $order_data['order_payment_type'] ?? null;
$order_prepaid_tickets = $order_data['order_prepaid_tickets'] ?? 1;
$order_tickets_quantity = $order_data['order_tickets_quantity'] ?? 0;
$game_product_type = $order_data['game_product_type'] ?? null;
$game_user_ebtal_id = $order_data['game_user_ebtal_id'] ?? null; // owner (صاحب بازی - از user_ebtal)
$game_id = $order_data['game_id'] ?? null;
$customer_id = $order_data['customer_id'] ?? null;
$customer_phone = $order_data['customer_phone'] ?? null;
$order_phones = $order_data['order_phones'] ?? null;
```
- استخراج تمام فیلدها از نتیجه query
- استفاده از `??` برای مقدار پیش‌فرض
- `order_prepaid_tickets` پیش‌فرض 1 است

### خطوط 1064-1068: بررسی اطلاعات لازم
```php
if (!$order_paid || $order_tickets_quantity <= 0) {
    error_log("Missing required data for financial calculation for order_id: $order_id - order_paid: $order_paid, quantity: $order_tickets_quantity");
    return false;
}
```
- چک می‌کند که `order_paid` و `order_tickets_quantity` موجود باشند
- اگر نبودند، error log می‌کند و `false` برمی‌گرداند

### خطوط 1070-1080: محاسبه قیمت نهایی (order_finall_price)

#### خط 1072: تعریف متغیر
```php
$order_finall_price = null;
```

#### خطوط 1073-1076: محاسبه برای پرداخت جزئی
```php
if ($order_payment_type === 'partial' && $order_prepaid_tickets > 0 && $order_paid) {
    // اگر پرداخت جزئی است، قیمت هر تیکت = order_paid / order_prepaid_tickets
    $ticket_price = $order_paid / $order_prepaid_tickets;
    $order_finall_price = $ticket_price * $order_tickets_quantity;
}
```
- **شرط**: اگر `order_payment_type === 'partial'` و `order_prepaid_tickets > 0` و `order_paid` موجود باشد
- **فرمول**: 
  - `ticket_price = order_paid / order_prepaid_tickets` (قیمت هر تیکت)
  - `order_finall_price = ticket_price * order_tickets_quantity` (قیمت کل)

#### خطوط 1077-1079: محاسبه برای پرداخت کامل
```php
else if ($order_paid) {
    // اگر پرداخت کامل است
    $order_finall_price = $order_paid;
}
```
- اگر پرداخت کامل باشد، `order_finall_price = order_paid`

### خطوط 1082-1086: بررسی محاسبه order_finall_price
```php
if (!$order_finall_price) {
    error_log("Failed to calculate order_finall_price for order_id: $order_id");
    return false;
}
```
- اگر `order_finall_price` محاسبه نشد، error log می‌کند و `false` برمی‌گرداند

### خطوط 1088-1092: تعیین نرخ کمیسیون
```php
$commission_rate = 0.10; // پیش‌فرض 10%
if ($game_product_type == 'لیزرتگ' || $game_product_type == 'اتاق خشم') {
    $commission_rate = 0.20; // 20% برای لیزرتگ و اتاق خشم
}
```
- **پیش‌فرض**: 10%
- **لیزرتگ و اتاق خشم**: 20%
- **نکته**: این نرخ‌ها با تابع قدیمی متفاوت است (قدیمی: 11% و 22%)

### خط 1095: محاسبه درآمد (order_net_profit)
```php
$order_net_profit = $order_finall_price * $commission_rate;
```
- **فرمول**: `قیمت نهایی * نرخ کمیسیون`
- این همان کمیسیون/درآمد سایت است

### خطوط 1097-1099: محاسبه مالیات بر ارزش افزوده (order_tax)
```php
// مالیات = 10% از درآمد (کمیسیون)
$order_tax = $order_net_profit * 0.10;
```
- **فرمول**: `درآمد * 0.10`
- مالیات 10% از درآمد محاسبه می‌شود

### خط 1102: استانداردسازی status
```php
$standardized_status = standardize_order_status('wc-walletx');
```
- تبدیل status به فرمت استاندارد

### خطوط 1105-1113: آماده‌سازی و آپدیت در دیتابیس
```php
try {
    $update_data = [
        'order_finall_price' => $order_finall_price,
        'order_net_profit' => $order_net_profit,
        'order_tax' => $order_tax,
        'order_status' => $standardized_status
    ];

    $updated = $medoo->update('wp_markting', $update_data, ['order_id' => $order_id]);
```
- ساخت آرایه داده‌ها برای آپدیت
- آپدیت در جدول `wp_markting`

### خط 1115: بررسی موفقیت آپدیت
```php
if ($updated !== false) {
```
- اگر آپدیت موفق بود، ادامه می‌دهد

### خط 1116: لاگ موفقیت
```php
error_log("Successfully updated financial data for order_id: $order_id - order_finall_price: $order_finall_price, order_net_profit: $order_net_profit, order_tax: $order_tax");
```
- ثبت لاگ موفقیت با جزئیات

### خط 1118: تعریف global wpdb
```php
global $wpdb;
```
- برای استفاده در بخش‌های بعدی

---

## بخش واریز به کیف پول owner

### خطوط 1120-1146: واریز به کیف پول owner (صاحب بازی - game_user_ebtal_id)

#### خط 1121: بررسی شرایط
```php
if ($wldb && !empty($game_user_ebtal_id) && $order_paid) {
```
- چک می‌کند که:
  - `$wldb` موجود باشد
  - `game_user_ebtal_id` خالی نباشد
  - `order_paid` موجود باشد

#### خط 1124: محاسبه مبلغ واریز
```php
$owner_amount = $order_paid - $order_net_profit;
```
- **فرمول**: `پیش پرداخت - درآمد`
- این مبلغ به owner داده می‌شود

#### خط 1126: بررسی غیر صفر بودن مبلغ
```php
if ($owner_amount != 0) {
```
- فقط اگر مبلغ صفر نباشد، تراکنش ثبت می‌شود

#### خطوط 1130-1131: ساخت description
```php
$game_title = $game_id ? get_the_title($game_id) : 'بازی';
$owner_description = 'فروش تیکت بازی ' . $game_title . ' - سفارش: ' . $order_id;
```

#### خطوط 1133-1140: چک کردن وجود تراکنش قبلی
```php
$existing_transaction = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM `wallet_transactions` WHERE `description` = %s",
    $owner_description
), ARRAY_A);
```
- چک می‌کند که آیا تراکنش با همین `description` قبلاً ثبت شده یا نه
- استفاده از `$wpdb->prepare` برای جلوگیری از SQL injection
- اگر تراکنش وجود داشت، `$existing_transaction` خالی نخواهد بود

#### خط 1142: بررسی و ثبت تراکنش
```php
if (empty($existing_transaction)) {
```
- اگر تراکنش وجود نداشت، ادامه می‌دهد

#### خطوط 1171-1179: دریافت موجودی و محاسبه موجودی جدید
```php
// دریافت موجودی فعلی از آخرین تراکنش کاربر
$last_transaction = $medoo->get('wallet_transactions', ['balance'], [
    'user_id' => $game_user_ebtal_id
], [
    'ORDER' => ['ID' => 'DESC'],
    'LIMIT' => 1
]);
$owner_current_balance = $last_transaction ? (int)$last_transaction['balance'] : 0;
$owner_balance = $owner_current_balance + $owner_amount;
```
- **خطوط 1171-1177**: دریافت آخرین تراکنش کاربر از جدول `wallet_transactions`
  - فیلتر بر اساس `user_id`
  - مرتب‌سازی بر اساس `ID DESC` (آخرین تراکنش)
  - محدود به 1 رکورد
- **خط 1178**: اگر تراکنش وجود داشت، `balance` آن را می‌گیرد، در غیر این صورت `0`
- **خط 1179**: محاسبه موجودی جدید = `موجودی فعلی + مبلغ واریز`

**نکته مهم:**
- در کد قدیمی از `$wldb->get_balance()` استفاده می‌شد که وابسته به global variable بود
- در کد جدید مستقیماً از `medoo` استفاده می‌شود و مستقل است

#### خطوط 1181-1188: ثبت تراکنش در دیتابیس
```php
// ثبت تراکنش در جدول wallet_transactions
$medoo->insert('wallet_transactions', [
    'user_id'           => $game_user_ebtal_id,
    'amount'            => $owner_amount,
    'balance'           => $owner_balance,
    'description'       => $owner_description,
    'unique_description'=> $owner_description,
    'type'              => 'transaction'
]);
```
- ثبت مستقیم در جدول `wallet_transactions` با استفاده از medoo
- **نکته مهم:**
  - در کد قدیمی از `$wldb->insert()` استفاده می‌شد که وابسته به global variable بود
  - در کد جدید مستقیماً از `medoo->insert()` استفاده می‌شود و مستقل است
  - **`created_at` حذف شده است**: در کد قدیمی هم `created_at` در آرایه قرار نمی‌گرفت
    - کلاس `EZ_Transaction_CRUD` در متد `insert()` خودش `created_at` را اضافه می‌کرد
    - یا اگر جدول `created_at` با default value تعریف شده باشد، MySQL خودش مقدار می‌دهد

#### خطوط 1155-1157: مدیریت تراکنش تکراری
```php
} else {
    error_log("Wallet transaction already exists for owner user_id: $game_user_ebtal_id, order_id: $order_id, description: $owner_description");
}
```
- اگر تراکنش قبلاً وجود داشت، فقط لاگ می‌کند
- از ثبت تراکنش تکراری جلوگیری می‌کند

---

## بخش تغییر status و آپدیت متا

### خطوط 1177-1182: تغییر status سفارش
```php
$order = wc_get_order($order_id);
if ($order) {
    $order->update_status('wc-walletx');
    $wpdb->update('wp_posts', array('post_status' => 'wc-walletx'), array('ID' => $order_id));
}
```
- دریافت order از WooCommerce
- تغییر status در WooCommerce
- تغییر status در `wp_posts` (برای اطمینان)

### خطوط 1184-1191: آپدیت متا محصول
```php
if ($game_id && $order_tickets_quantity > 0) {
    $current_total_income = (int)get_post_meta($game_id, 'total_income', true);
    $current_tickets_sold = (int)get_post_meta($game_id, 'tickets_sold', true);
    
    update_post_meta($game_id, 'total_income', $current_total_income + $order_finall_price);
    update_post_meta($game_id, 'tickets_sold', $current_tickets_sold + $order_tickets_quantity);
}
```
- **شرط**: اگر `game_id` و `order_tickets_quantity > 0` موجود باشند
- **آپدیت `total_income`**: `total_income + order_finall_price`
- **آپدیت `tickets_sold`**: `tickets_sold + order_tickets_quantity`

---

## بخش امتیازدهی

### خطوط 1193-1207: امتیازدهی به سرگروه

#### خط 1194: بررسی وجود customer_id
```php
if ($customer_id) {
```

#### خطوط 1195-1196: ساخت description
```php
$game_title = $game_id ? get_the_title($game_id) : 'بازی';
$point_desc = 'رزرو بازی ' . $game_title;
```

#### خطوط 1198-1202: چک کردن وجود امتیاز قبلی
```php
$already_exists = $wpdb->get_var($wpdb->prepare("
    SELECT COUNT(*) FROM points
    WHERE user_id = %d
    AND description = %s
", $customer_id, $point_desc));
```
- چک می‌کند که آیا قبلاً به این کاربر برای این بازی امتیاز داده شده یا نه

#### خطوط 1204-1206: اضافه کردن امتیاز
```php
if (!$already_exists && function_exists('add_point')) {
    add_point('place-order-leader', $customer_id, $point_desc);
}
```
- اگر امتیاز وجود نداشت و تابع `add_point` موجود بود، امتیاز اضافه می‌کند

### خطوط 1209-1266: امتیازدهی به همگروهی‌ها

#### خط 1210: بررسی شرایط
```php
if ($order_phones && is_array($order_phones) && $customer_id && $game_id) {
```
- چک می‌کند که:
  - `order_phones` موجود و آرایه باشد
  - `customer_id` موجود باشد
  - `game_id` موجود باشد

#### خطوط 1214-1219: پاکسازی لیست بازیکنان
```php
$clean_players = [];
foreach ($order_phones as $p) {
    if (is_array($p) && isset($p['phone'])) {
        $clean_players[] = $p['phone'];
    }
}
```
- استخراج شماره تلفن‌ها از آرایه

#### خطوط 1221-1228: حذف شماره سرگروه
```php
if ($customer_phone) {
    foreach ($clean_players as $k => $phone) {
        if ($phone == $customer_phone) {
            unset($clean_players[$k]);
        }
    }
}
```
- حذف شماره سرگروه (`customer_phone`) از لیست بازیکنان

#### خط 1230: بررسی وجود بازیکن
```php
if (!empty($clean_players)) {
```

#### خط 1231: دریافت عنوان بازی
```php
$game_title = get_the_title($game_id);
```

#### خط 1232: حلقه روی بازیکنان
```php
foreach ($clean_players as $phone) {
```

#### خطوط 1233-1235: نرمالایز کردن شماره تلفن
```php
try {
    $phone_normalized = ltrim(preg_replace('/[^0-9]/', '', $phone), '0');
    $teammate = get_user_by('login', $phone_normalized);
```
- حذف کاراکترهای غیر عددی
- حذف صفرهای اول
- پیدا کردن کاربر با شماره تلفن

#### خط 1237: بررسی پیدا شدن کاربر
```php
if ($teammate) {
```

#### خط 1238: دریافت teammate_id
```php
$teammate_id = $teammate->ID;
```

#### خطوط 1240-1247: اضافه کردن محصول به لیست teammate_products
```php
$products = get_user_meta($teammate_id, 'teammate_products', true);
if (!is_array($products)) $products = [];

if (!in_array($game_id, $products)) {
    $products[] = $game_id;
    update_user_meta($teammate_id, 'teammate_products', $products);
}
```
- دریافت لیست محصولات همگروهی
- اگر محصول در لیست نبود، اضافه می‌کند

#### خطوط 1249-1255: چک کردن وجود امتیاز
```php
$point_desc = 'رزرو بازی ' . $game_title . ' - همگروهی';
$already_exists = $wpdb->get_var($wpdb->prepare("
    SELECT COUNT(*) FROM points
    WHERE user_id = %d
    AND description = %s
", $teammate_id, $point_desc));
```

#### خطوط 1257-1259: اضافه کردن امتیاز
```php
if (!$already_exists && function_exists('add_point')) {
    add_point('place-order-teammate', $teammate_id, $point_desc);
}
```

#### خطوط 1261-1263: مدیریت خطا
```php
} catch (Throwable $e) {
    error_log("teammate points problem - order_id: $order_id - " . $e->getMessage());
}
```

---

## بخش بازگشت نتیجه

### خط 1268: بازگشت موفقیت
```php
return true;
```
- اگر همه چیز موفق بود، `true` برمی‌گرداند

### خطوط 1269-1271: بازگشت خطا
```php
} else {
    error_log("Failed to update financial data for order_id: $order_id");
    return false;
}
```
- اگر آپدیت ناموفق بود، error log می‌کند و `false` برمی‌گرداند

### خطوط 1273-1276: مدیریت exception
```php
} catch (PDOException $e) {
    error_log("Error updating financial data for order_id $order_id: " . $e->getMessage());
    return false;
}
```
- اگر exception رخ داد، error log می‌کند و `false` برمی‌گرداند

---

## خلاصه عملکرد تابع

### ورودی:
- `$order_id`: شناسه سفارش

### خروجی:
- `true`: در صورت موفقیت
- `false`: در صورت خطا

### عملیات انجام شده:
1. ✅ محاسبه `order_finall_price`
2. ✅ محاسبه `order_net_profit` (درآمد/کمیسیون)
3. ✅ محاسبه `order_tax` (مالیات)
4. ✅ آپدیت در `wp_markting`
5. ✅ واریز به کیف پول owner
7. ✅ تغییر status به `wc-walletx`
8. ✅ آپدیت متا محصول
9. ✅ امتیازدهی به سرگروه
10. ✅ امتیازدهی به همگروهی‌ها

### تفاوت‌های مهم با تابع قدیمی:
1. **نرخ کمیسیون**: 10%/20% به جای 11%/22%
2. **مالیات**: اضافه شده (10% از درآمد)
4. **استفاده از جدول**: همه اطلاعات از `wp_markting` (بهینه‌تر)
5. **امتیازدهی**: اضافه شده



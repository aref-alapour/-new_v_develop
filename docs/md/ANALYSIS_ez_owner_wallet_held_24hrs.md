# تحلیل کامل تابع ez_owner_wallet_held_24hrs

## مقدمه
این تابع به صورت cron job هر ساعت اجرا می‌شود و وظیفه پردازش سفارش‌هایی که 24 ساعت از زمان booking گذشته و وضعیت `wc-partially-paid` دارند را بر عهده دارد.

## ساختار کلی تابع

تابع به دو بخش اصلی تقسیم می‌شود:
1. **بخش اول (خطوط 908-981)**: فیلتر کردن و جمع‌آوری سفارش‌های واجد شرایط
2. **بخش دوم (خطوط 983-1150)**: پردازش نهایی هر سفارش

---

## بخش اول: فیلتر کردن سفارش‌ها (خطوط 899-981)

### خط 899: تعریف تابع
```php
function ez_owner_wallet_held_24hrs() {
```
- تابع بدون پارامتر تعریف شده است
- به صورت cron job فراخوانی می‌شود

### خط 900: تعریف متغیرهای global
```php
global $wpdb, $wldb;
```
- `$wpdb`: برای دسترسی به دیتابیس WordPress
- `$wldb`: برای دسترسی به سیستم کیف پول (wallet database)

### خط 904: اتصال به دیتابیس
```php
$medoo = medoo();
```
- ایجاد instance از medoo برای دسترسی به دیتابیس

### خط 906: تعریف آرایه برای ذخیره سفارش‌ها
```php
$order_data = [];
```
- آرایه خالی برای ذخیره اطلاعات سفارش‌های واجد شرایط

### خطوط 908-910: دریافت سفارش‌های partially-paid
```php
$temp = $wpdb->get_results("SELECT ID FROM wp_posts WHERE post_status = 'wc-partially-paid' ORDER BY wp_posts.ID", ARRAY_A);
$partially_orders = array_column($temp, 'ID');
if (empty($partially_orders)) return;
```
- **خط 908**: دریافت تمام IDهای سفارش‌هایی که وضعیت `wc-partially-paid` دارند
- **خط 909**: تبدیل نتایج به آرایه ساده از IDها
- **خط 910**: اگر سفارشی نبود، تابع خروج می‌کند

### خط 912: تبدیل آرایه به رشته
```php
$partially_orders_str = implode(',', $partially_orders);
```
- تبدیل آرایه IDها به رشته برای استفاده در SQL query

### خطوط 914-917: دریافت اطلاعات booking
```php
$rows = json_decode(ez_reservation([
    'type' => 'query_execution',
    'data' => ['query' => "SELECT wc_order_id as ID, booking_time FROM wp_zb_booking_history WHERE `wc_order_id` IN ($partially_orders_str)"]
]), true);
```
- دریافت `wc_order_id` و `booking_time` از جدول `wp_zb_booking_history`
- فقط برای سفارش‌هایی که در لیست `$partially_orders` هستند

### خطوط 919-927: داده‌های استاتیک (احتمالاً برای تست)
```php
$orders_data = [
    769082 => ['product_id' =>744926,'quantity' => 5,'owner_id' => 88469],
    ...
];
$orderssx = [769082,769081,769080,768552,768546];
```
- آرایه `$orders_data`: اطلاعات سفارش‌های خاص (احتمالاً برای تست یا سفارش‌های مشکل‌دار)
- آرایه `$orderssx`: لیست سفارش‌هایی که باید skip شوند

### خطوط 929-981: حلقه فیلتر کردن سفارش‌ها

#### خط 929: شروع حلقه
```php
foreach ($rows as $row) {
```
- حلقه روی تمام booking‌های دریافت شده

#### خط 930: چک کردن 24 ساعت
```php
if ($row['booking_time'] < time() - 24 * 3600) {
```
- **شرط اصلی**: چک می‌کند که `booking_time` کمتر از 24 ساعت قبل باشد
- یعنی 24 ساعت از زمان booking گذشته باشد

#### خط 932: دریافت order_id
```php
$order_id = $row['ID'];
```
- استخراج `order_id` از نتیجه query

#### خطوط 934-935: Skip کردن سفارش‌های خاص
```php
if ( in_array($order_id, $orderssx) )
    continue;
```
- اگر سفارش در لیست skip باشد، از پردازش رد می‌شود

#### خطوط 937-938: دریافت order از WooCommerce
```php
$order = wc_get_order($order_id);
if (!$order) continue;
```
- دریافت order object از WooCommerce
- اگر order وجود نداشت، ادامه نمی‌دهد

#### خطوط 940-954: دریافت product_id و quantity
```php
$product_id     = null;
$item_quantity  = null;

$items = $order->get_items();
if ( ! empty($items) ) {
    foreach ($items as $item) {
        $product_id    = $item->get_product_id();
        $item_quantity = $item->get_quantity();
        break;
    }
} elseif ( isset($orders_data[$order_id]) ) {
    $product_id    = (int) $orders_data[$order_id]['product_id'];
    $item_quantity = (int) $orders_data[$order_id]['quantity'];
}
```
- **خطوط 940-941**: تعریف متغیرها
- **خط 943**: دریافت items سفارش
- **خطوط 944-949**: اگر items وجود داشت:
  - از اولین item، `product_id` و `quantity` را می‌گیرد
  - `break` می‌زند (فقط اولین محصول)
- **خطوط 951-954**: اگر items وجود نداشت:
  - از آرایه `$orders_data` استفاده می‌کند (برای سفارش‌های خاص)

#### خطوط 956-957: بررسی وجود product_id و quantity
```php
if ( ! $product_id || ! $item_quantity )
    continue;
```
- اگر `product_id` یا `quantity` نباشد، ادامه نمی‌دهد

#### خط 959: ساخت description
```php
$description = 'فروش تیکت بازی ' . get_the_title($product_id) . ' - سفارش: ' . $order_id;
```
- ساخت description برای تراکنش کیف پول

#### خطوط 960-962: چک کردن وجود تراکنش قبلی
```php
$if_exists = $wpdb->get_results("SELECT * FROM `wallet_transactions` WHERE `description` LIKE '{$description}'", ARRAY_A);
if (!empty($if_exists)) {
    $wpdb->update('wp_posts', array('post_status' => 'wc-walletx'), array('ID' => $order_id));
}
```
- **خط 960**: چک می‌کند که آیا تراکنش با این description قبلاً ثبت شده یا نه
- **خطوط 961-962**: اگر تراکنش وجود داشت:
  - فقط status سفارش را به `wc-walletx` تغییر می‌دهد
  - از پردازش بیشتر رد می‌شود

#### خطوط 963-979: اضافه کردن به لیست پردازش
```php
else {
    $already_added = false;
    foreach ($order_data as $data) {
        if ($data['order_id'] == $order_id) {
            $already_added = true;
            break;
        }
    }

    if (!$already_added) {
        $order_data[] = [
            'order_id' => $order_id,
            'description' => $description
        ];
    }
}
```
- **خط 963**: اگر تراکنش وجود نداشت
- **خطوط 964-971**: چک می‌کند که آیا این `order_id` قبلاً به لیست اضافه شده یا نه
- **خطوط 973-978**: اگر اضافه نشده بود:
  - به آرایه `$order_data` اضافه می‌کند
  - فقط `order_id` و `description` را ذخیره می‌کند

---

## بخش دوم: پردازش نهایی هر سفارش (خطوط 983-1150)

### خط 983: چک کردن وجود سفارش برای پردازش
```php
if (!empty($order_data)) {
```
- اگر سفارشی برای پردازش وجود داشت، ادامه می‌دهد

### خط 984: شروع حلقه پردازش
```php
foreach ($order_data as $data) {
```
- حلقه روی تمام سفارش‌های جمع‌آوری شده

### خطوط 986-995: دریافت اطلاعات اولیه

#### خطوط 986-987: استخراج داده‌ها
```php
$order_id       = $data['order_id'];
$description    = $data['description'];
```
- دریافت `order_id` و `description` از آرایه

#### خطوط 988-989: دریافت order و user_id
```php
$order          = wc_get_order($order_id);
$user_id        = $order->get_user_id();
```
- دریافت order object از WooCommerce
- دریافت `user_id` (مشتری که سفارش را ثبت کرده)

#### خطوط 991-995: دریافت اطلاعات محصول
```php
foreach ($order->get_items() as $item) {
    $product_id     = $item->get_product_id();
    $item_quantity  = $item->get_quantity();
    $product_title  = $item->get_name();
}
```
- از order items:
  - `product_id`: شناسه محصول
  - `item_quantity`: تعداد تیکت‌ها
  - `product_title`: نام محصول

### خطوط 997-1024: سیستم محاسبه کمیسیون

#### خطوط 1000-1006: دریافت نوع محصول
```php
$terms = get_the_terms($product_id, 'product_cat');
if ( count( $terms ) > 1 ) {
    foreach ( $terms as $term )
        if ( $term->parent == 0 )
            $product_type = $term->name;
} else
    $product_type = get_term($terms[0]->parent)->name;
```
- **خط 1000**: دریافت دسته‌بندی‌های محصول
- **خطوط 1001-1004**: اگر بیش از یک دسته‌بندی داشت:
  - دسته‌بندی parent (اصلی) را پیدا می‌کند
- **خط 1005-1006**: اگر یک دسته‌بندی داشت:
  - parent آن را می‌گیرد

#### خطوط 1008-1017: تعیین نرخ کمیسیون
```php
if ( $product_type == 'اتاق فرار' )
    $commission = 11;
elseif ( $product_type == 'سینما ترس' )
    $commission = 11;
elseif ( $product_type == 'لیزرتگ' )
    $commission = 22;
elseif ( $product_type == 'اتاق خشم' )
    $commission = 22;
else
    $commission = 11;
```
- **اتاق فرار**: 11%
- **سینما ترس**: 11%
- **لیزرتگ**: 22%
- **اتاق خشم**: 22%
- **بقیه**: 11% (پیش‌فرض)

#### خطوط 1019-1023: Override کمیسیون از تنظیمات
```php
if ( $commission_items = get_option('ez_home')['commission_items'] )
    foreach ( $commission_items as $commission_item )
        if ( in_array($product_id, $commission_item['products']) )
            $commission = $commission_item['percent'];
```
- اگر در تنظیمات (`ez_home['commission_items']`) کمیسیون خاصی برای این محصول تعریف شده باشد
- از آن استفاده می‌کند (override می‌کند)

### خطوط 1027-1033: محاسبه مبالغ

#### خطوط 1027-1029: دریافت تعداد تیکت پیش پرداخت
```php
$pish_per_person    = get_post_meta($order_id, 'ticket_tedad', true);
$pish_per_person    = !empty($pish_per_person) ? $pish_per_person : get_post_meta($product_id, 'pish_pardakht_per_person', true);
$pish_per_person    = !empty($pish_per_person) ? $pish_per_person : 1;
```
- **خط 1027**: از meta سفارش (`ticket_tedad`) می‌گیرد
- **خط 1028**: اگر نبود، از meta محصول (`pish_pardakht_per_person`) می‌گیرد
- **خط 1029**: اگر باز هم نبود، پیش‌فرض 1 است

#### خط 1030: دریافت مبلغ پیش پرداخت
```php
$prepaid            = get_post_meta($order_id, "prepaid", true);
```
- مبلغ پیش پرداخت از meta سفارش

#### خط 1031: محاسبه مبلغ نهایی پیش پرداخت
```php
$pish_final         = $prepaid ?: (get_post_meta($order_id, "_order_total_2", true) ?: get_post_meta($order_id, "_order_total", true));
```
- اگر `prepaid` موجود بود، از آن استفاده می‌کند
- در غیر این صورت از `_order_total_2` استفاده می‌کند
- اگر آن هم نبود، از `_order_total` استفاده می‌کند

#### خط 1032: محاسبه قیمت کل تیکت‌ها
```php
$total              = $pish_final / $pish_per_person * $item_quantity;
```
- **فرمول**: `(مبلغ پیش پرداخت / تعداد تیکت پیش پرداخت) * تعداد کل تیکت‌ها`
- این همان `order_finall_price` است

#### خط 1033: محاسبه کمیسیون
```php
$porsant            = $total * ($commission / 100);
```
- **فرمول**: `قیمت کل * (نرخ کمیسیون / 100)`
- این همان `order_net_profit` است (اما با نرخ قدیمی)

### خطوط 1035-1055: واریز به کیف پول owner

#### خط 1038: دریافت owner_id
```php
$owner_id = get_owner_id_by_product_id($product_id);
```
- `get_owner_id_by_product_id` از meta محصول `user_ebtal` را برمی‌گرداند
- این همان `game_user_ebtal_id` است

#### خطوط 1041-1043: محاسبه مبلغ واریز
```php
$current_balance    = $wldb->get_balance($owner_id);
$amount             = $pish_final - $porsant;
$balance            = $current_balance + $amount;
```
- **خط 1041**: دریافت موجودی فعلی owner
  - **تحلیل `$wldb->get_balance($owner_id)`:**
    - این تابع از کلاس `EZ_Transaction_CRUD` استفاده می‌کند
    - آخرین تراکنش کاربر را از جدول `wallet_transactions` می‌گیرد (ORDER BY ID DESC LIMIT 1)
    - اگر تراکنشی وجود نداشت، `0` برمی‌گرداند
    - اگر تراکنش وجود داشت، فیلد `balance` آن تراکنش را برمی‌گرداند
    - **نکته مهم**: این تابع وابسته به `$wldb` (global variable) است
- **خط 1042**: محاسبه مبلغ واریز = `مبلغ پیش پرداخت - کمیسیون`
- **خط 1043**: محاسبه موجودی جدید

#### خطوط 1045-1053: ساخت تراکنش
```php
$new_transaction = array(
    'user_id'           => $owner_id,
    'amount'            => $amount,
    'balance'           => $balance,
    'description'       => $description,
    'unique_description'=> $description,
    'type'              => 'transaction',
);
```
- ساخت آرایه تراکنش با تمام اطلاعات لازم
- **نکته**: `created_at` در آرایه قرار نمی‌گیرد
  - کلاس `EZ_Transaction_CRUD` در متد `insert()` خودش `created_at` را اضافه می‌کند
  - یا اگر جدول `created_at` با default value تعریف شده باشد، MySQL خودش مقدار می‌دهد

#### خط 1055: ثبت تراکنش
```php
$wldb->insert($new_transaction);
```
- ثبت تراکنش در دیتابیس کیف پول

### خطوط 1057-1060: محاسبه درآمد و مالیات (اضافه شده بعداً)
```php
// محاسبه درآمد، مالیات و واریز به کیف پول مجموعه‌دار
if (function_exists('calculate_and_update_order_financials')) {
    calculate_and_update_order_financials($order_id);
}
```
- فراخوانی تابع جدید برای محاسبه درآمد و مالیات
- این بخش بعداً اضافه شده بود

#### تحلیل کد قدیمی (قبل از اضافه شدن این بخش):

**در کد قدیمی، محاسبات به این صورت انجام می‌شد:**

1. **محاسبه قیمت کل تیکت‌ها (خط 1032)**:
   ```php
   $total = $pish_final / $pish_per_person * $item_quantity;
   ```
   - `$pish_final`: مبلغ پیش پرداخت (یا order_total_2 یا order_total)
   - `$pish_per_person`: تعداد تیکت پیش پرداخت
   - `$item_quantity`: تعداد کل تیکت‌ها
   - **نتیجه**: قیمت کل تمام تیکت‌ها

2. **محاسبه کمیسیون (خط 1033)**:
   ```php
   $porsant = $total * ($commission / 100);
   ```
   - `$total`: قیمت کل تیکت‌ها (از مرحله قبل)
   - `$commission`: نرخ کمیسیون (11% یا 22% - نرخ قدیمی)
   - **نتیجه**: کمیسیون/درآمد سایت

3. **محاسبه مبلغ واریز به owner (خط 1042)**:
   ```php
   $amount = $pish_final - $porsant;
   ```
   - `$pish_final`: مبلغ پیش پرداخت
   - `$porsant`: کمیسیون محاسبه شده
   - **نتیجه**: مبلغی که به owner داده می‌شود

**تفاوت‌های مهم با تابع جدید:**

| مورد | کد قدیمی | تابع جدید |
|------|----------|------------|
| **نرخ کمیسیون** | 11% یا 22% | 10% یا 20% |
| **مالیات** | محاسبه نمی‌شد | 10% از درآمد محاسبه می‌شود |
| **آپدیت wp_markting** | انجام نمی‌شد | `order_finall_price`, `order_net_profit`, `order_tax` آپدیت می‌شوند |
| **منبع داده** | از post_meta و WooCommerce | از جدول `wp_markting` |
| **محاسبه total** | `pish_final / pish_per_person * item_quantity` | `order_paid / order_prepaid_tickets * order_tickets_quantity` (همان منطق) |

**نکته مهم:**
- در کد قدیمی، `$total` همان `order_finall_price` است
- در کد قدیمی، `$porsant` همان `order_net_profit` است (اما با نرخ قدیمی)
- در کد قدیمی، `$amount` برای owner همان `order_paid - order_net_profit` است
- **در کد قدیمی، هیچ آپدیتی در `wp_markting` برای فیلدهای مالی انجام نمی‌شد**
- **در کد قدیمی، مالیات محاسبه نمی‌شد**

---

## تحلیل `$wldb->get_balance()` و جایگزینی آن

### عملکرد `$wldb->get_balance($user_id)` در کد قدیمی:

```php
public function get_balance($user_id) {
    $transaction = array (
        'user_id' => $user_id,
    );
    
    $balance = $this->get($transaction, 1, true);
    
    // if it's the user's first transaction so balance is 0
    if ( empty( $balance ) )
        return 0;
    
    return (int)$balance->balance;
}
```

**عملکرد:**
1. آخرین تراکنش کاربر را از جدول `wallet_transactions` می‌گیرد
2. با شرط `user_id = $user_id` و `ORDER BY ID DESC LIMIT 1`
3. اگر تراکنشی وجود نداشت، `0` برمی‌گرداند
4. اگر تراکنش وجود داشت، فیلد `balance` آن تراکنش را برمی‌گرداند

**وابستگی:**
- وابسته به `$wldb` (global variable)
- وابسته به کلاس `EZ_Transaction_CRUD`

### جایگزینی در تابع جدید:

```php
// دریافت موجودی فعلی از آخرین تراکنش کاربر
$last_transaction = $medoo->get('wallet_transactions', ['balance'], [
    'user_id' => $game_user_ebtal_id
], [
    'ORDER' => ['ID' => 'DESC'],
    'LIMIT' => 1
]);
$owner_current_balance = $last_transaction ? (int)$last_transaction['balance'] : 0;
```

**مزایا:**
1. ✅ مستقل از `$wldb` و کلاس‌های خارجی
2. ✅ استفاده مستقیم از medoo
3. ✅ منطق یکسان: آخرین تراکنش کاربر را می‌گیرد
4. ✅ اگر تراکنشی وجود نداشت، `0` برمی‌گرداند
5. ✅ اگر تراکنش وجود داشت، فیلد `balance` را برمی‌گرداند

**تفاوت:**
- در کد قدیمی: از `$wldb->get_balance()` استفاده می‌شد
- در کد جدید: مستقیماً از `medoo->get()` استفاده می‌شود

### خطوط 1062-1063: تغییر status سفارش
```php
$order->update_status( 'wc-walletx' );
$wpdb->update('wp_posts', array('post_status' => 'wc-walletx'), array('ID' => $order_id));
```
- **خط 1062**: تغییر status در WooCommerce
- **خط 1063**: تغییر status در `wp_posts` (برای اطمینان)

### خطوط 1065-1076: امتیازدهی به سرگروه

#### خط 1068: ساخت description امتیاز
```php
$point_desc = 'رزرو بازی ' . $product_title;
```

#### خطوط 1069-1073: چک کردن وجود امتیاز قبلی
```php
$already_exists = $wpdb->get_var( $wpdb->prepare("
    SELECT COUNT(*) FROM points
    WHERE user_id = %d
    AND description = %s
", $user_id, $point_desc) );
```
- چک می‌کند که آیا قبلاً به این کاربر برای این بازی امتیاز داده شده یا نه

#### خطوط 1075-1076: اضافه کردن امتیاز
```php
if ( ! $already_exists )
    add_point('place-order-leader', $user_id, $point_desc);
```
- اگر امتیاز وجود نداشت، امتیاز `place-order-leader` اضافه می‌کند

### خطوط 1078-1131: امتیازدهی به همگروهی‌ها

#### خطوط 1081-1082: دریافت شماره تلفن‌ها
```php
$billing_phone = get_post_meta($order_id, '_billing_phone', true);
$players       = get_post_meta($order_id, 'players_phone', true);
```
- شماره تلفن سرگروه و لیست بازیکنان

#### خطوط 1084-1090: پاکسازی لیست بازیکنان
```php
if (!empty($players)) {
    $clean_players = [];
    foreach ($players as $p)
        if (is_array($p) && isset($p['phone']))
            $clean_players[] = $p['phone'];
    
    // حذف شماره سرگروهی
    foreach ($clean_players as $k => $phone)
        if ($phone == $billing_phone)
            unset($clean_players[$k]);
}
```
- **خطوط 1086-1089**: استخراج شماره تلفن‌ها از آرایه
- **خطوط 1092-1095**: حذف شماره سرگروه از لیست

#### خطوط 1097-1130: پردازش هر بازیکن
```php
if (!empty($clean_players)) {
    foreach ($clean_players as $phone) {
        try {
            $phone_normalized = ltrim(preg_replace('/[^0-9]/', '', $phone), '0');
        } catch (Throwable $e) {
            saeed_store("teammate points problem - order_id: $order_id");
        }
        
        $teammate = get_user_by('login', $phone_normalized);
        
        if ($teammate) {
            $teammate_id = $teammate->ID;
            
            // اضافه کردن محصول به لیست teammate_products
            $products = get_user_meta($teammate_id, 'teammate_products', true);
            if (!is_array($products)) $products = [];
            
            if (!in_array($product_id, $products)) {
                $products[] = $product_id;
                update_user_meta($teammate_id, 'teammate_products', $products);
            }
            
            // اضافه کردن امتیاز
            $point_desc = 'رزرو بازی ' . $product_title . ' - همگروهی';
            $already_exists = $wpdb->get_var( $wpdb->prepare("
                SELECT COUNT(*) FROM points
                WHERE user_id = %d
                AND description = %s
            ", $teammate_id, $point_desc) );
            
            if ( ! $already_exists )
                add_point('place-order-teammate', $teammate_id, $point_desc);
        }
    }
}
```
- **خطوط 1100-1104**: نرمالایز کردن شماره تلفن (حذف کاراکترهای غیر عددی و صفرهای اول)
- **خط 1106**: پیدا کردن کاربر با شماره تلفن
- **خط 1108**: اگر کاربر پیدا شد:
  - **خطوط 1111-1117**: اضافه کردن محصول به لیست `teammate_products`
  - **خطوط 1119-1127**: چک کردن و اضافه کردن امتیاز `place-order-teammate`

### خطوط 1135-1147: آپدیت status در wp_markting
```php
if (!empty($order_id)) {
    $existing_order = $medoo->get('wp_markting', '*', ['order_id' => $order_id]);
    if ($existing_order) {
        $medoo->update('wp_markting', [
            'order_status' => 'wc-walletx'
        ], [
            'order_id' => $order_id
        ]);
    }
} else {
    saeed_store('order_id is missing in : ' . $order_id);
}
```
- **خط 1136**: چک می‌کند که سفارش در `wp_markting` وجود دارد
- **خطوط 1137-1142**: اگر وجود داشت، status را به `wc-walletx` آپدیت می‌کند

### خطوط 1149-1150: آپدیت متا محصول
```php
update_post_meta($product_id, 'total_income', (int)get_post_meta($product_id, 'total_income', true) + $total);
update_post_meta($product_id, 'tickets_sold', (int)get_post_meta($product_id, 'tickets_sold', true) + $item_quantity);
```
- **خط 1149**: آپدیت `total_income` = `total_income + total` (قیمت کل تیکت‌ها)
- **خط 1150**: آپدیت `tickets_sold` = `tickets_sold + item_quantity` (تعداد تیکت‌های فروخته شده)

---

## خلاصه عملکرد تابع

### ورودی:
- هیچ پارامتری نمی‌گیرد
- به صورت cron job هر ساعت اجرا می‌شود

### خروجی:
- پردازش سفارش‌های `wc-partially-paid` که 24 ساعت از booking گذشته
- واریز به کیف پول owner
- تغییر status به `wc-walletx`
- امتیازدهی به سرگروه و همگروهی‌ها
- آپدیت متا محصول

### نکات مهم:
1. **کمیسیون قدیمی**: از نرخ 11% یا 22% استفاده می‌کرد (نه 10% یا 20%)
2. **مالیات**: مالیات محاسبه نمی‌شد
3. **مجموعه‌دار**: واریز به کیف پول مجموعه‌دار انجام نمی‌شد
4. **محاسبه total**: از فرمول `pish_final / pish_per_person * item_quantity` استفاده می‌کرد
5. **واریز owner**: مبلغ `pish_final - porsant` به owner واریز می‌شد





function ez_owner_wallet_held_24hrs() {
    global $wpdb, $wldb;
//    return;
//    saeed_store('wallet_cron');

    $medoo = medoo();

    $order_data = []; // آرایه نهایی که اطلاعات سفارش‌ها رو ذخیره می‌کنه

    $temp = $wpdb->get_results("SELECT ID FROM wp_posts WHERE post_status = 'wc-partially-paid' ORDER BY wp_posts.ID", ARRAY_A);
    $partially_orders = array_column($temp, 'ID'); // روش ساده‌تر گرفتن فقط IDها
    if (empty($partially_orders)) return;

    $partially_orders_str = implode(',', $partially_orders);

    $rows = json_decode(ez_reservation([
            'type' => 'query_execution',
            'data' => ['query' => "SELECT wc_order_id as ID, booking_time FROM wp_zb_booking_history WHERE `wc_order_id` IN ($partially_orders_str)"]
    ]), true);

    foreach ($rows as $row) {
        if ($row['booking_time'] < time() - 24 * 3600) {

            $order_id = $row['ID'];

            $order = wc_get_order($order_id);
            if (!$order) continue;

            $product_id     = null;
            $item_quantity  = null;

            $items = $order->get_items();
            if ( ! empty($items) ) {
                foreach ($items as $item) {
                    $product_id    = $item->get_product_id();
                    $item_quantity = $item->get_quantity();
                    break;
                }

            } elseif ( isset($orders_data[$order_id]) ) {
                $product_id    = (int) $orders_data[$order_id]['product_id'];
                $item_quantity = (int) $orders_data[$order_id]['quantity'];
            }

            if ( ! $product_id || ! $item_quantity )
                continue;

            $description = 'فروش تیکت بازی ' . get_the_title($product_id) . ' - سفارش: ' . $order_id;
            $if_exists = $wpdb->get_results("SELECT * FROM `wallet_transactions` WHERE `description` LIKE '{$description}'", ARRAY_A);
            if (!empty($if_exists)) {
                $wpdb->update('wp_posts', array('post_status' => 'wc-walletx'), array('ID' => $order_id));
            } else {
                // اگر این order_id قبلاً اضافه نشده، ذخیره شود
                $already_added = false;
                foreach ($order_data as $data) {
                    if ($data['order_id'] == $order_id) {
                        $already_added = true;
                        break;
                    }
                }

                if (!$already_added) {
                    $order_data[] = [
                            'order_id' => $order_id,
                            'description' => $description
                    ];
                }
            }
        }
    }

    if (!empty($order_data)) {
        foreach ($order_data as $data) {

            $order_id       = $data['order_id'];
            $description    = $data['description'];
            $order          = wc_get_order($order_id);
            $user_id        = $order->get_user_id();

            foreach ($order->get_items() as $item) {
                $product_id     = $item->get_product_id();
                $item_quantity  = $item->get_quantity();
                $product_title  = $item->get_name();
            }

            /*********************************************/
            // سیستم محاسبه کمسیون

            $terms = get_the_terms($product_id, 'product_cat');
            if ( count( $terms ) > 1 ) {
                foreach ( $terms as $term )
                    if ( $term->parent == 0 )
                        $product_type = $term->name;
            } else
                $product_type = get_term($terms[0]->parent)->name;

            if ( $product_type == 'اتاق فرار' )
                $commission = 11;
            elseif ( $product_type == 'سینما ترس' )
                $commission = 11;
            elseif ( $product_type == 'لیزرتگ' )
                $commission = 22;
            elseif ( $product_type == 'اتاق خشم' )
                $commission = 22;
            else
                $commission = 11;

            // تغییر دیفالت کمسیونی که در بالا تعریف شده است، بر اساس انتخاب مدیریت برای گروه محصولات
            if ( $commission_items = get_option('ez_home')['commission_items'] )
                foreach ( $commission_items as $commission_item )
                    if ( in_array($product_id, $commission_item['products']) )
                        $commission = $commission_item['percent'];

            /*********************************************/

            $pish_per_person    = get_post_meta($order_id, 'ticket_tedad', true);
            $pish_per_person    = !empty($pish_per_person) ? $pish_per_person : get_post_meta($product_id, 'pish_pardakht_per_person', true);
            $pish_per_person    = !empty($pish_per_person) ? $pish_per_person : 1;
            $prepaid            = get_post_meta($order_id, "prepaid", true);
            $pish_final         = $prepaid ?: (get_post_meta($order_id, "_order_total_2", true) ?: get_post_meta($order_id, "_order_total", true));
            $total              = $pish_final / $pish_per_person * $item_quantity;
            $porsant            = $total * ($commission / 100);

            /*===========================*/
            // owner transaction adding

            $owner_id = get_owner_id_by_product_id($product_id);
//            $owner_id = $orders_data[$order_id]['owner_id'];

            $current_balance    = $wldb->get_balance($owner_id);
            $amount             = $pish_final - $porsant;
            $balance            = $current_balance + $amount;

            $new_transaction = array(
                    'user_id'           => $owner_id,
                    'amount'            => $amount,
                    'balance'           => $balance,
                    'description'       => $description,
                    'unique_description'=> $description,
                    'type'              => 'transaction',
            );
            $wldb->insert($new_transaction);

            $total_datas[$order_id] = [
                'prepaid'       => $prepaid,
                'quantity'      => $item_quantity,
                'ticket_tedad'  => $pish_per_person,
                'total'         => $total,
                'commission'    => $commission,
                'porsant'       => $porsant,
                'amount'        => $amount,
            ];

            $order->update_status( 'wc-walletx' );
            $wpdb->update('wp_posts', array('post_status' => 'wc-walletx'), array('ID' => $order_id)); // اطمینان از اینکه حتما وضعیت درست ست شده باشه

            /*********************************/
            // امتیازدهی سرگروهی

            $point_desc = 'رزرو بازی ' . $product_title;
            $already_exists = $wpdb->get_var( $wpdb->prepare("
                SELECT COUNT(*) FROM points
                WHERE user_id = %d
                AND description = %s
            ", $user_id, $point_desc) );

            if ( ! $already_exists )
                add_point('place-order-leader', $user_id, $point_desc);

            /*********************************/
            // امتیازدهی همگروهی ها

            $billing_phone = get_post_meta($order_id, '_billing_phone', true);
            $players       = get_post_meta($order_id, 'players_phone', true);

            if (!empty($players)) {

                $clean_players = [];

                foreach ($players as $p)
                    if (is_array($p) && isset($p['phone']))
                        $clean_players[] = $p['phone'];

                // حذف شماره سرگروهی
                foreach ($clean_players as $k => $phone)
                    if ($phone == $billing_phone)
                        unset($clean_players[$k]);

                if (!empty($clean_players)) {
                    foreach ($clean_players as $phone) {

                        try {
                            $phone_normalized = ltrim(preg_replace('/[^0-9]/', '', $phone), '0');
                        } catch (Throwable $e) {
                            saeed_store("teammate points problem - order_id: $order_id");
                        }

                        $teammate = get_user_by('login', $phone_normalized);

                        if ($teammate) { // فقط اکانت دارها امتیاز میگیرن
                            $teammate_id = $teammate->ID;

                            $products = get_user_meta($teammate_id, 'teammate_products', true);
                            if (!is_array($products)) $products = [];

                            if (!in_array($product_id, $products)) {
                                $products[] = $product_id;
                                update_user_meta($teammate_id, 'teammate_products', $products);
                            }

                            $point_desc = 'رزرو بازی ' . $product_title . ' - همگروهی';
                            $already_exists = $wpdb->get_var( $wpdb->prepare("
                                SELECT COUNT(*) FROM points
                                WHERE user_id = %d
                                AND description = %s
                            ", $teammate_id, $point_desc) );

                            if ( ! $already_exists )
                                add_point('place-order-teammate', $teammate_id, $point_desc);
                        }
                    }
                }
            }
            /*********************************/

            if (!empty($order_id)) {
                $existing_order = $medoo->get('wp_markting', '*', ['order_id' => $order_id]);
                if ($existing_order) {
                    $medoo->update('wp_markting', [
                            'order_status' => 'wc-walletx'
                    ], [
                            'order_id' => $order_id
                    ]);
                }

            } else {
                saeed_store('order_id is missing in : ' . $order_id);
            }

            update_post_meta($product_id, 'total_income', (int)get_post_meta($product_id, 'total_income', true) + $total); // آپدیت فروش کل این محصول
            update_post_meta($product_id, 'tickets_sold', (int)get_post_meta($product_id, 'tickets_sold', true) + $item_quantity); // آپدیت بلیط های فروخته شده این محصول
//            break;
//            return;
        }
        saeed_store($total_datas);
    }
}
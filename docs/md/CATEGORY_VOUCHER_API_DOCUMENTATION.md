# 🎮 Category-Based Voucher API Documentation
### Created by eng.Aref Alapour [a Lord from AFB]

## نمای کلی (Overview)
این API برای ساخت کدهای تخفیف اختصاصی بر اساس دسته‌بندی بازی‌ها طراحی شده است. هر کاربر یک کد تخفیف منحصر به فرد دریافت می‌کند که فقط برای دسته‌بندی خاصی از بازی‌ها معتبر است.

## آدرس پایه (Base URL)
```
https://escapezoom.ir/api/v1/
```

---

## 📍 API Endpoint

### ایجاد کد تخفیف دسته‌بندی‌محور
**POST** `/vouchers/create_category`

این endpoint کدهای تخفیف اختصاصی برای کاربران خاص در یک دسته‌بندی بازی خاص ایجاد می‌کند.

---

## 📥 درخواست (Request)

### Headers
| پارامتر | نوع | الزامی | توضیحات |
|---------|-----|--------|---------|
| `amountVoucher` | number | خیر | مبلغ ثابت تخفیف (به تومان). پیش‌فرض: 10000 |
| `Content-Type` | string | بله | باید `application/json` باشد |

### Body Parameters
```json
{
    "userIds": ["123", "456"],
    "voucherPoolName": "escaperoom"
}
```

| پارامتر | نوع | الزامی | توضیحات |
|---------|-----|--------|---------|
| `userIds` | array | بله | آرایه‌ای از شناسه‌های کاربران |
| `voucherPoolName` | string | بله | نام دسته‌بندی بازی |

### مقادیر مجاز برای `voucherPoolName`

| مقدار | نام فارسی | توضیحات |
|-------|-----------|---------|
| `escaperoom` | اتاق فرار | کد تخفیف برای تمام دسته‌بندی‌های اتاق فرار |
| `scary_cinema` | سینما ترس | کد تخفیف برای تمام دسته‌بندی‌های سینما ترس |
| `lasertag` | لیزرتگ | کد تخفیف برای تمام دسته‌بندی‌های لیزرتگ |
| `rageroom` | اتاق خشم | کد تخفیف برای تمام دسته‌بندی‌های اتاق خشم |
| `all` | همه بازی‌ها | کد تخفیف برای **تمام** دسته‌بندی‌های بازی (اتاق فرار، سینما ترس، لیزرتگ، اتاق خشم) |

---

## 📤 پاسخ (Response)

### پاسخ موفق (Success Response)
**Status Code:** `200 OK`

```json
{
    "success": true,
    "data": [
        {
            "userId": "123",
            "voucherCode": "escaperoom_123_1727689234_5678"
        },
        {
            "userId": "456",
            "voucherCode": "escaperoom_456_1727689234_9012"
        }
    ]
}
```

### ساختار Response
| فیلد | نوع | توضیحات |
|------|-----|---------|
| `success` | boolean | وضعیت موفقیت |
| `data` | array | آرایه‌ای از نتایج |
| `data[].userId` | string | شناسه کاربر |
| `data[].voucherCode` | string | کد تخفیف اختصاصی |

---

## ⚠️ خطاها (Error Responses)

### 1. لیست کاربران خالی یا نامعتبر
```json
{
    "success": false,
    "data": {
        "message": "لیست کاربران الزامی است"
    }
}
```
**Status Code:** `400 Bad Request`

### 2. نام دسته‌بندی خالی
```json
{
    "success": false,
    "data": {
        "message": "نام دسته‌بندی الزامی است"
    }
}
```
**Status Code:** `400 Bad Request`

### 3. نام دسته‌بندی نامعتبر
```json
{
    "success": false,
    "data": {
        "message": "نام دسته‌بندی نامعتبر است. مقادیر مجاز: escaperoom, scary_cinema, lasertag, rageroom, all"
    }
}
```
**Status Code:** `400 Bad Request`

### 4. کاربر یافت نشد
```json
{
    "success": true,
    "data": [
        {
            "userId": "999",
            "voucherCode": null,
            "error": "کاربر یافت نشد"
        }
    ]
}
```
**Status Code:** `200 OK` (با error در آیتم خاص)

---

## 🔧 نمونه‌های استفاده (Usage Examples)

### مثال 1: ایجاد کد تخفیف 20000 تومان برای اتاق فرار

#### cURL
```bash
curl -X POST "https://escapezoom.ir/api/v1/vouchers/create_category" \
  -H "Content-Type: application/json" \
  -H "amountVoucher: 20000" \
  -d '{
    "userIds": ["123", "456", "789"],
    "voucherPoolName": "escaperoom"
  }'
```

#### JavaScript (Fetch API)
```javascript
fetch('https://escapezoom.ir/api/v1/vouchers/create_category', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'amountVoucher': '20000'
  },
  body: JSON.stringify({
    userIds: ['123', '456', '789'],
    voucherPoolName: 'escaperoom'
  })
})
.then(response => response.json())
.then(data => console.log(data));
```

#### PHP
```php
$ch = curl_init('https://escapezoom.ir/api/v1/vouchers/create_category');

$data = [
    'userIds' => ['123', '456', '789'],
    'voucherPoolName' => 'escaperoom'
];

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'amountVoucher: 20000'
    ],
    CURLOPT_POSTFIELDS => json_encode($data)
]);

$response = curl_exec($ch);
$result = json_decode($response, true);
curl_close($ch);

print_r($result);
```

---

### مثال 2: ایجاد کد تخفیف 15000 تومان برای سینما ترس

```bash
curl -X POST "https://escapezoom.ir/api/v1/vouchers/create_category" \
  -H "Content-Type: application/json" \
  -H "amountVoucher: 15000" \
  -d '{
    "userIds": ["100", "101"],
    "voucherPoolName": "scary_cinema"
  }'
```

---

### مثال 3: ایجاد کد تخفیف 25000 تومان برای لیزرتگ

```bash
curl -X POST "https://escapezoom.ir/api/v1/vouchers/create_category" \
  -H "Content-Type: application/json" \
  -H "amountVoucher: 25000" \
  -d '{
    "userIds": ["200"],
    "voucherPoolName": "lasertag"
  }'
```

---

### مثال 4: ایجاد کد تخفیف 30000 تومان برای اتاق خشم

```bash
curl -X POST "https://escapezoom.ir/api/v1/vouchers/create_category" \
  -H "Content-Type: application/json" \
  -H "amountVoucher: 30000" \
  -d '{
    "userIds": ["300", "301", "302"],
    "voucherPoolName": "rageroom"
  }'
```

---

### مثال 5: ایجاد کد تخفیف 50000 تومان برای همه بازی‌ها

```bash
curl -X POST "https://escapezoom.ir/api/v1/vouchers/create_category" \
  -H "Content-Type: application/json" \
  -H "amountVoucher: 50000" \
  -d '{
    "userIds": ["400", "401"],
    "voucherPoolName": "all"
  }'
```

---

## 📝 نکات مهم (Important Notes)

### 1. ساختار کد تخفیف
کدهای تخفیف با فرمت زیر ساخته می‌شوند:
```
{voucherPoolName}_{userId}_{timestamp}_{randomNumber}
```
**مثال:** `escaperoom_123_1727689234_5678`

### 2. محدودیت‌های کد تخفیف
- هر کد تخفیف فقط **یک بار** قابل استفاده است
- هر کد فقط توسط **کاربر مشخص شده** (از طریق ایمیل) قابل استفاده است
- کد تخفیف فقط برای **دسته‌بندی مشخص شده** معتبر است
- نوع تخفیف: **مبلغ ثابت روی کل سبد خرید (fixed_cart)**

### 3. دسته‌بندی‌های شامل شده

#### اتاق فرار (escaperoom)
- شامل تمام دسته‌بندی‌های شهرها (15, 162, 121, 122, ...)
- شامل تمام ژانرها (346, 843, 128, 344, ...)

#### سینما ترس (scary_cinema)
- دسته‌بندی‌ها: 1217, 1199, 918, 1134, 1141, 913, 1004, 925, 1072, 1176, 1009, 926, 1208, 904

#### لیزرتگ (lasertag)
- دسته‌بندی‌ها: 1151, 1175, 1148, 1196, 1147, 1219, 1158, 1218, 1149, 1150, 1156

#### اتاق خشم (rageroom)
- دسته‌بندی‌ها: 1186, 1074

#### همه بازی‌ها (all)
- شامل **تمام** دسته‌بندی‌های چهار نوع بازی بالا
- مجموع دسته‌بندی‌های: اتاق فرار + سینما ترس + لیزرتگ + اتاق خشم
- این گزینه برای کدهای تخفیف عمومی مناسب است

### 4. مقدار پیش‌فرض تخفیف
اگر header `amountVoucher` ارسال نشود، مقدار پیش‌فرض **10000 تومان** در نظر گرفته می‌شود.

### 5. احراز هویت
احراز هویت برای این endpoint **اختیاری** است (`permission_callback => '__return_true'`)، اما توصیه می‌شود در محیط production احراز هویت مناسب پیاده‌سازی شود.

---

## 🔍 تست API

### تست با Postman
1. URL: `https://escapezoom.ir/api/v1/vouchers/create_category`
2. Method: `POST`
3. Headers:
   - `Content-Type: application/json`
   - `amountVoucher: 20000`
4. Body (raw JSON):
```json
{
    "userIds": ["1"],
    "voucherPoolName": "escaperoom"
}
```

---

## 🚀 یکپارچه‌سازی (Integration)

### استفاده در WordPress
```php
// Example: Create 15000 Toman discount for user 123 on laser tag
$response = wp_remote_post('https://escapezoom.ir/api/v1/vouchers/create_category', [
    'headers' => [
        'Content-Type' => 'application/json',
        'amountVoucher' => '15000'
    ],
    'body' => json_encode([
        'userIds' => ['123'],
        'voucherPoolName' => 'lasertag'
    ])
]);

$result = json_decode(wp_remote_retrieve_body($response), true);

if ($result['success']) {
    $voucher_code = $result['data'][0]['voucherCode'];
    echo "کد تخفیف شما: " . $voucher_code;
}
```

---

## 🔗 API های مرتبط

این API با سیستم voucher موجود یکپارچه شده و می‌توانید از APIهای زیر برای مدیریت کامل استفاده کنید:

1. **Validate Voucher:** `POST /vouchers/validate`
2. **List Vouchers:** `GET /vouchers/list`
3. **Create Voucher:** `POST /vouchers/create`
4. **Assign Voucher:** `POST /vouchers/assign`

برای اطلاعات بیشتر به مستند اصلی مراجعه کنید:
📄 `VOUCHER_API_DOCUMENTATION.md`

---

## 📧 پشتیبانی
برای هرگونه سوال یا مشکل، با تیم توسعه تماس بگیرید.

**Developer:** eng.Aref Alapour  
**Role:** Backend API Developer [a Lord from AFB]

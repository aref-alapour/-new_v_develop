# 🎟 Voucher Management API Documentation create by eng.Aref Alapour [a Lord from AFB]

## Overview
This API provides comprehensive voucher/coupon management functionality for WooCommerce, allowing you to create, assign, validate, and manage discount codes dynamically.

## Base URL
```
https://escapezoom.ir/api/v1/
```

## Authentication (It is not necessary)
All endpoints require proper authentication. Include the following headers:
```
Content-Type: application/json
Authorization: Bearer <your-token>
```

---

## 📋 API Endpoints

### 1. Create Voucher
**POST** `/vouchers/create`

Creates a new voucher/coupon with specified parameters.

#### Request Body
```json
{
  "code": "SUMMER2025",
  "description": "تخفیف تابستانی ۲۰۲۵",
  "discount_type": "percent",
  "discount_amount": 20,
  "expiry_date": "2025-12-31",
  "usage_limit": 100,
  "usage_limit_per_user": 1,
  "minimum_amount": 50000,
  "maximum_amount": 100000,
  "product_categories": [15, 20, 25],
  "excluded_product_categories": [30],
  "product_ids": [100, 101, 102],
  "excluded_product_ids": [200],
  "email_restrictions": ["user@example.com"],
  "free_shipping": "no",
  "exclude_sale_items": "no"
}
```

#### Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `code` | string | Yes | Unique voucher code |
| `description` | string | No | Voucher description |
| `discount_type` | string | Yes | Type: `percent`, `fixed_cart`, `fixed_product` |
| `discount_amount` | number | Yes | Discount amount |
| `expiry_date` | string | No | Expiry date (YYYY-MM-DD) |
| `usage_limit` | number | No | Total usage limit |
| `usage_limit_per_user` | number | No | Usage limit per user |
| `minimum_amount` | number | No | Minimum order amount |
| `maximum_amount` | number | No | Maximum order amount |
| `product_categories` | array | No | Allowed product categories |
| `excluded_product_categories` | array | No | Excluded product categories |
| `product_ids` | array | No | Allowed product IDs |
| `excluded_product_ids` | array | No | Excluded product IDs |
| `email_restrictions` | array | No | Allowed email addresses |
| `free_shipping` | string | No | Enable free shipping (`yes`/`no`) |
| `exclude_sale_items` | string | No | Exclude sale items (`yes`/`no`) |

#### Response
```json
{
  "success": true,
  "data": {
    "message": "کد تخفیف با موفقیت ایجاد شد",
    "coupon_id": 123,
    "code": "SUMMER2025"
  }
}
```

---

### 2. Assign Voucher to Users
**POST** `/vouchers/assign`

Assigns vouchers from a pool to specific users.

#### Request Body
```json
{
  "userIds": ["123", "456", "789"],
  "voucherPoolName": "summer_campaign",
  "discount_type": "percent",
  "discount_amount": 15,
  "expiry_date": "2025-12-31",
  "product_categories": [15, 20],
  "minimum_amount": 30000
}
```

#### Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `userIds` | array | Yes | Array of user IDs |
| `voucherPoolName` | string | Yes | Name of the voucher pool |
| `discount_type` | string | No | Type: `percent`, `fixed_cart`, `fixed_product` (default: `percent`) |
| `discount_amount` | number | No | Discount amount (default: 10) |
| `expiry_date` | string | No | Expiry date (YYYY-MM-DD) |
| `product_categories` | array | No | Allowed product categories |
| `excluded_product_categories` | array | No | Excluded product categories |
| `product_ids` | array | No | Allowed product IDs |
| `excluded_product_ids` | array | No | Excluded product IDs |
| `minimum_amount` | number | No | Minimum order amount |
| `maximum_amount` | number | No | Maximum order amount |
| `free_shipping` | string | No | Enable free shipping (`yes`/`no`) |
| `exclude_sale_items` | string | No | Exclude sale items (`yes`/`no`) |

#### Response
```json
{
  "success": true,
  "data": [
    {
      "userId": "123",
      "voucherCode": "summer_campaign_123_1703123456_7890"
    },
    {
      "userId": "456",
      "voucherCode": "summer_campaign_456_1703123456_7891"
    },
    {
      "userId": "789",
      "voucherCode": "summer_campaign_789_1703123456_7892"
    }
  ]
}
```

---

### 3. Validate Voucher
**POST** `/vouchers/validate`

Validates a voucher code and returns its details.

#### Request Body
```json
{
  "code": "SUMMER2025"
}
```

#### Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `code` | string | Yes | Voucher code to validate |

#### Response
```json
{
  "success": true,
  "data": {
    "valid": true,
    "code": "SUMMER2025",
    "discount_type": "percent",
    "discount_amount": "20",
    "minimum_amount": "50000",
    "maximum_amount": "100000",
    "product_categories": [15, 20, 25],
    "excluded_product_categories": [30],
    "product_ids": [100, 101, 102],
    "excluded_product_ids": [200],
    "customer_email": ["user@example.com"],
    "free_shipping": "no",
    "usage_count": "5",
    "usage_limit": "100",
    "expiry_date": "2025-12-31"
  }
}
```

---

### 4. List Vouchers
**GET** `/vouchers/list`

Retrieves a list of all vouchers with pagination and search.

#### Query Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `page` | number | No | Page number (default: 1) |
| `per_page` | number | No | Items per page (default: 20) |
| `search` | string | No | Search term |

#### Example Request
```
GET /vouchers/list?page=1&per_page=10&search=SUMMER
```

#### Response
```json
{
  "success": true,
  "data": {
    "vouchers": [
      {
        "id": 123,
        "code": "SUMMER2025",
        "description": "تخفیف تابستانی ۲۰۲۵",
        "discount_type": "percent",
        "discount_amount": "20",
        "usage_count": "5",
        "usage_limit": "100",
        "expiry_date": "2025-12-31",
        "minimum_amount": "50000",
        "maximum_amount": "100000",
        "product_categories": [15, 20, 25],
        "excluded_product_categories": [30],
        "product_ids": [100, 101, 102],
        "excluded_product_ids": [200],
        "customer_email": ["user@example.com"],
        "free_shipping": "no",
        "created_date": "2024-12-21 10:30:00"
      }
    ],
    "total": 50,
    "page": 1,
    "per_page": 10
  }
}
```

---

## 🔧 Usage Examples

### Example 1: Create a Simple Percentage Discount
```bash
curl -X POST "https://escapezoom.ir/api/v1/vouchers/create" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer your-token" \
  -d '{
    "code": "WELCOME20",
    "description": "تخفیف خوش آمدید ۲۰ درصد",
    "discount_type": "percent",
    "discount_amount": 20,
    "expiry_date": "2025-06-30",
    "usage_limit": 1000
  }'
```

### Example 2: Create a Fixed Amount Discount for Specific Categories
```bash
curl -X POST "https://escapezoom.ir/api/v1/vouchers/create" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer your-token" \
  -d '{
    "code": "FIXED50K",
    "description": "تخفیف ثابت ۵۰ هزار تومان",
    "discount_type": "fixed_cart",
    "discount_amount": 50000,
    "product_categories": [15, 20],
    "minimum_amount": 200000
  }'
```

### Example 3: Assign Vouchers to Multiple Users
```bash
curl -X POST "https://escapezoom.ir/api/v1/vouchers/assign" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer your-token" \
  -d '{
    "userIds": ["123", "456", "789"],
    "voucherPoolName": "loyalty_reward",
    "discount_type": "percent",
    "discount_amount": 15,
    "expiry_date": "2025-12-31",
    "product_categories": [15, 20, 25]
  }'
```

### Example 4: Validate a Voucher
```bash
curl -X POST "https://escapezoom.ir/api/v1/vouchers/validate" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer your-token" \
  -d '{
    "code": "WELCOME20"
  }'
```

---

## ⚠️ Error Handling

All endpoints return appropriate HTTP status codes and error messages:

- **400 Bad Request**: Invalid parameters or validation errors
- **404 Not Found**: Voucher not found
- **500 Internal Server Error**: Server-side errors

### Error Response Format
```json
{
  "success": false,
  "data": {
    "message": "Error description in Persian"
  }
}
```

---

## 📝 Notes

1. **Optional Parameters**: All parameters except the required ones are optional. If not provided, they won't be set for the voucher.

2. **Date Format**: Use `YYYY-MM-DD` format for dates.

3. **Array Parameters**: Can be provided as arrays or comma-separated strings.

4. **User-Specific Vouchers**: When assigning vouchers, each user gets a unique code with the format: `{poolName}_{userId}_{timestamp}_{randomNumber}`.

5. **WooCommerce Integration**: All created vouchers are fully compatible with WooCommerce's coupon system and will appear in the admin panel.

6. **Security**: Make sure to implement proper authentication and authorization for production use.

---

## 🚀 Getting Started

1. Ensure WooCommerce is installed and activated
2. Make sure the API endpoints are properly registered
3. Test the endpoints using the provided examples
4. Integrate with your frontend application

For any issues or questions, please refer to the WooCommerce documentation or contact the development team.

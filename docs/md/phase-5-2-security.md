# فاز ۵.۲ — امنیت پنل و محدودسازی legacy team sans

## CSRF — `v2_ajax_handler`

| لایه | فایل |
|------|------|
| Core | [PanelAjaxSecurityService.php](../../wp-content/mu-plugins/ez_core/src/Modules/Booking/Services/Panel/PanelAjaxSecurityService.php) |
| Theme | [app/ajax/init.php](../../wp-content/themes/escapezoom-v2/app/ajax/init.php) |

- همه callbackهای `panel_*` به‌جز لیست read-only در `READ_ONLY_CALLBACKS` نیاز به `check_ajax_referer('v2-ajax-nonce')` دارند.
- callbackهای عمومی قبلی (`post_order_comment`, …) بدون تغییر.
- در `v2_ajax_handler` یک guard مرکزی ownership اضافه شد که در صورت وجود `product_id`/`room_id` روی callbackهای `panel_*`، دسترسی مالک/مدیر را با `PanelProductAuthorizationService` بررسی می‌کند.

### P0 حساس (nonce اجباری)

- `panel_wallet_withdrawal`
- `panel_sans_settings_update`
- `panel_profile_save`
- `panel_auto_disable_update`

## IDOR — تنظیمات سانس و پاسخ کامنت

- [panel_sans_settings_update.php](../../wp-content/themes/escapezoom-v2/app/ajax/callbacks/panel_sans_settings_update.php)
- [panel_auto_disable_update.php](../../wp-content/themes/escapezoom-v2/app/ajax/callbacks/panel_auto_disable_update.php)
- [panel_comments_reply_add.php](../../wp-content/themes/escapezoom-v2/app/ajax/callbacks/panel_comments_reply_add.php)

همه از [PanelProductAuthorizationService.php](../../wp-content/mu-plugins/ez_core/src/Modules/Booking/Services/Panel/PanelProductAuthorizationService.php):

1. `products_data.owner_id` / `manager_id` (external)
2. fallback: postmeta `sans_manager`, `user_ebtal`, `administrator`

## Legacy HTTP — `web-service/team/sans_management.php`

درخواست مستقیم HTTP برای این typeها **403** (مگر `EZ_BOOKING_INTERNAL_CALL`):

- `sans_management_web`, `open_sans`, `close_sans`, `open_all_sanses`, `close_all_sanses`
- `bulk_date_range`, `game_search`, `check_playing`

CRM باید از:

- `POST /ajax` → `booking.*`
- `admin-ajax` → `ez_team_sans_game_search`

به‌روزرسانی: callback جستجوی بازی در `template/team/pages/comments.php` نیز از مسیر مستقیم `web-service/team/sans_management.php` به مسیر داخلی `admin-ajax` منتقل شد.

## Writes در core

| Action gateway | سرویس |
|----------------|--------|
| `booking.open_sans` / `close_sans` | [TeamSansWriteService.php](../../wp-content/mu-plugins/ez_core/src/Modules/Booking/Services/Team/TeamSansWriteService.php) |
| `booking.open_all_sanses` / `close_all_sanses` | همان |
| `booking.bulk_date_range` | [TeamSansBridge::bulkDateRange](../../wp-content/mu-plugins/ez_core/src/Modules/Booking/Services/Team/TeamSansBridge.php) |

پس از write: [BookingCacheInvalidator.php](../../wp-content/mu-plugins/ez_core/src/Modules/Booking/Services/BookingCacheInvalidator.php).

## Crypto policy (سبک)

- Encryption فقط برای write actions فعال است.
- read actions (`sans_day_json`, `sans_week`, `game_search`, `check_playing`) فقط با HMAC signature محافظت می‌شوند.
- این سیاست سربار CPU مسیر read را نزدیک صفر نگه می‌دارد.

## SQLi میان‌مدت

فایل legacy هنوز `$conn->query` دارد؛ مسیر HTTP برای typeهای migrate شده بسته است. بازنویسی کامل SQL در فاز بعدی.

## تست

```bash
cd wp-content/mu-plugins/ez_core && vendor/bin/pest --filter=PanelAjaxSecurity
```

بدون nonce → WordPress `-1` / 403 در مرورگر برای `panel_sans_settings_update`.

## وضعیت نهایی فاز ۵.۲

- read pathها (`booking.sans_day_json`, `booking.sans_week`, `booking.sans_management_web`) signature-only باقی می‌مانند.
- write pathها (`booking.open_sans`, `booking.close_sans`, `booking.open_all_sanses`, `booking.close_all_sanses`, `booking.bulk_date_range`) با encryption policy write محافظت می‌شوند.
- fallback legacy در read رزرو حذف شده و mixed-mode در مسیرهای اصلی رزرو بسته شده است.

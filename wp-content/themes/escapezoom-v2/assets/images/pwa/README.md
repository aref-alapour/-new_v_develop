# راهنمای آیکون‌های PWA

برای تکمیل PWA، نیاز به آیکون‌های زیر دارید:

## آیکون‌های اصلی (Any Purpose)
- `icon-72x72.png` - 72×72 پیکسل
- `icon-96x96.png` - 96×96 پیکسل
- `icon-128x128.png` - 128×128 پیکسل
- `icon-144x144.png` - 144×144 پیکسل
- `icon-152x152.png` - 152×152 پیکسل
- `icon-192x192.png` - 192×192 پیکسل (حداقل مورد نیاز)
- `icon-384x384.png` - 384×384 پیکسل
- `icon-512x512.png` - 512×512 پیکسل (توصیه شده)

## آیکون‌های Maskable (برای Android)
- `icon-maskable-192x192.png` - 192×192 پیکسل
- `icon-maskable-512x512.png` - 512×512 پیکسل

برای آیکون‌های Maskable، لوگو باید در مرکز و با حاشیه امن (Safe Zone) قرار بگیرد.

## اسکرین‌شات‌ها
- `screenshot-wide.png` - 1280×720 پیکسل (برای دسکتاپ)
- `screenshot-mobile.png` - 750×1334 پیکسل (برای موبایل)

## آیکون‌های Shortcut
- `shortcut-reserve.png` - 96×96 پیکسل
- `shortcut-discount.png` - 96×96 پیکسل
- `shortcut-profile.png` - 96×96 پیکسل

## روش ساخت آیکون‌ها

### با استفاده از اسکریپت خودکار:
```bash
# نصب ImageMagick (اگر نصب نشده)
sudo apt-get install imagemagick

# اجرای اسکریپت
bash generate-icons.sh
```

### یا استفاده از ابزارهای آنلاین:
1. [PWA Asset Generator](https://www.pwabuilder.com/imageGenerator)
2. [Favicon Generator](https://realfavicongenerator.net/)
3. [Maskable.app](https://maskable.app/editor) - برای آیکون‌های Maskable

## نکات مهم:
- از فرمت PNG با پس‌زمینه شفاف یا رنگی استفاده کنید
- برای آیکون‌های Maskable، 20% حاشیه امن اضافه کنید
- کیفیت بالا (بدون فشرده‌سازی شدید) توصیه می‌شود
- لوگو باید در تمام سایزها خوانا باشد


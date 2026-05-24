#!/usr/bin/env python3
"""
اسکریپت ساخت خودکار آیکون‌های PWA
نیاز به Pillow دارد: pip3 install Pillow
"""

import os
import sys
from pathlib import Path

try:
    from PIL import Image, ImageDraw
except ImportError:
    print("❌ خطا: کتابخانه Pillow نصب نیست!")
    print("لطفاً با دستور زیر نصب کنید:")
    print("pip3 install Pillow")
    sys.exit(1)

# تنظیمات
LOGO_PATH = "../logo.png"
FAVICON_PATH = "../fav-icon.png"
OUTPUT_DIR = "."
BG_COLOR = "#fd7013"  # رنگ نارنجی برند

def create_icon(source_img, size, output_path, padding=0):
    """ایجاد آیکون با سایز مشخص"""
    try:
        # محاسبه سایز با padding
        content_size = int(size * (1 - padding))
        
        # باز کردن تصویر اصلی
        img = Image.open(source_img)
        
        # تبدیل به RGBA اگر نیست
        if img.mode != 'RGBA':
            img = img.convert('RGBA')
        
        # تغییر سایز با حفظ نسبت تصویر
        img.thumbnail((content_size, content_size), Image.Resampling.LANCZOS)
        
        # ایجاد یک کانواس شفاف
        final_img = Image.new('RGBA', (size, size), (255, 255, 255, 0))
        
        # قرار دادن تصویر در مرکز
        offset = ((size - img.width) // 2, (size - img.height) // 2)
        final_img.paste(img, offset, img)
        
        # ذخیره
        final_img.save(output_path, 'PNG', optimize=True)
        print(f"✅ تولید شد: {os.path.basename(output_path)}")
        return True
    except Exception as e:
        print(f"❌ خطا در تولید {os.path.basename(output_path)}: {e}")
        return False

def create_maskable_icon(source_img, size, output_path):
    """ایجاد آیکون Maskable با پس‌زمینه رنگی"""
    try:
        # باز کردن تصویر اصلی
        img = Image.open(source_img)
        
        # تبدیل به RGBA
        if img.mode != 'RGBA':
            img = img.convert('RGBA')
        
        # سایز محتوا (80% برای safe zone)
        content_size = int(size * 0.8)
        
        # تغییر سایز
        img.thumbnail((content_size, content_size), Image.Resampling.LANCZOS)
        
        # ایجاد کانواس با پس‌زمینه رنگی
        # تبدیل رنگ hex به RGB
        bg_color = tuple(int(BG_COLOR.lstrip('#')[i:i+2], 16) for i in (0, 2, 4))
        final_img = Image.new('RGB', (size, size), bg_color)
        
        # قرار دادن تصویر در مرکز
        offset = ((size - img.width) // 2, (size - img.height) // 2)
        
        # اگر تصویر آلفا داشت، از آن استفاده کن
        if img.mode == 'RGBA':
            final_img.paste(img, offset, img)
        else:
            final_img.paste(img, offset)
        
        # ذخیره
        final_img.save(output_path, 'PNG', optimize=True)
        print(f"✅ تولید شد: {os.path.basename(output_path)}")
        return True
    except Exception as e:
        print(f"❌ خطا در تولید {os.path.basename(output_path)}: {e}")
        return False

def main():
    print("🎨 شروع تولید آیکون‌های PWA...")
    
    # بررسی وجود فایل لوگو
    if not os.path.exists(LOGO_PATH):
        print(f"⚠️  هشدار: فایل {LOGO_PATH} یافت نشد. از {FAVICON_PATH} استفاده می‌شود.")
        source = FAVICON_PATH
        
        if not os.path.exists(source):
            print("❌ خطا: هیچ فایل تصویری برای تبدیل یافت نشد!")
            return False
    else:
        source = LOGO_PATH
    
    print(f"📁 استفاده از فایل: {source}")
    
    # تولید آیکون‌های استاندارد
    print("\n🔨 تولید آیکون‌های استاندارد...")
    sizes = [72, 96, 128, 144, 152, 192, 384, 512]
    for size in sizes:
        output = f"{OUTPUT_DIR}/icon-{size}x{size}.png"
        create_icon(source, size, output)
    
    # تولید آیکون‌های Maskable
    print("\n🎭 تولید آیکون‌های Maskable...")
    maskable_sizes = [192, 512]
    for size in maskable_sizes:
        output = f"{OUTPUT_DIR}/icon-maskable-{size}x{size}.png"
        create_maskable_icon(source, size, output)
    
    # تولید آیکون‌های Shortcut
    print("\n⚡ تولید آیکون‌های Shortcut...")
    shortcuts = ['reserve', 'discount', 'profile']
    for shortcut in shortcuts:
        output = f"{OUTPUT_DIR}/shortcut-{shortcut}.png"
        create_icon(source, 96, output)
    
    # تولید Apple Touch Icon
    print("\n🍎 تولید Apple Touch Icon...")
    create_icon(source, 180, f"{OUTPUT_DIR}/apple-touch-icon.png")
    
    # تولید Favicon
    print("\n🌐 تولید Favicon...")
    create_icon(source, 32, f"{OUTPUT_DIR}/favicon-32x32.png")
    create_icon(source, 16, f"{OUTPUT_DIR}/favicon-16x16.png")
    
    print("\n✨ تمام آیکون‌ها با موفقیت تولید شدند!")
    print("\n⚠️  یادآوری:")
    print("1. اسکرین‌شات‌های PWA را به صورت دستی از سایت بگیرید")
    print("2. فایل‌ها را بررسی کنید و در صورت نیاز ویرایش کنید")
    print("3. برای نتیجه بهتر، از تصویر با کیفیت بالاتر استفاده کنید")
    print()
    
    return True

if __name__ == "__main__":
    try:
        success = main()
        sys.exit(0 if success else 1)
    except KeyboardInterrupt:
        print("\n\n⚠️  عملیات توسط کاربر لغو شد.")
        sys.exit(1)
    except Exception as e:
        print(f"\n❌ خطای غیرمنتظره: {e}")
        sys.exit(1)


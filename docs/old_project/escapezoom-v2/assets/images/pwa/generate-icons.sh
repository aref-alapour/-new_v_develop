#!/bin/bash

# اسکریپت ساخت خودکار آیکون‌های PWA از روی لوگو
# نیاز به ImageMagick دارد: sudo apt-get install imagemagick

LOGO="../logo.png"
FAVICON="../fav-icon.png"
OUTPUT_DIR="."

# رنگ پس‌زمینه برای آیکون‌های Maskable (نارنجی برند)
BG_COLOR="#fd7013"

echo "🎨 شروع تولید آیکون‌های PWA..."

# بررسی وجود ImageMagick
if ! command -v convert &> /dev/null; then
    echo "❌ خطا: ImageMagick نصب نیست!"
    echo "لطفاً با دستور زیر نصب کنید:"
    echo "sudo apt-get install imagemagick"
    exit 1
fi

# بررسی وجود فایل لوگو
if [ ! -f "$LOGO" ]; then
    echo "⚠️  هشدار: فایل logo.png یافت نشد. از fav-icon.png استفاده می‌شود."
    LOGO="$FAVICON"
    
    if [ ! -f "$LOGO" ]; then
        echo "❌ خطا: هیچ فایل تصویری برای تبدیل یافت نشد!"
        exit 1
    fi
fi

echo "📁 استفاده از فایل: $LOGO"

# تابع تولید آیکون ساده
generate_icon() {
    size=$1
    output="${OUTPUT_DIR}/icon-${size}x${size}.png"
    
    convert "$LOGO" -resize ${size}x${size} -background transparent -gravity center -extent ${size}x${size} "$output"
    
    if [ $? -eq 0 ]; then
        echo "✅ تولید شد: icon-${size}x${size}.png"
    else
        echo "❌ خطا در تولید: icon-${size}x${size}.png"
    fi
}

# تابع تولید آیکون Maskable (با Safe Zone)
generate_maskable_icon() {
    size=$1
    safe_zone_size=$(( size * 80 / 100 ))  # 80% of size (20% padding)
    output="${OUTPUT_DIR}/icon-maskable-${size}x${size}.png"
    
    # ساخت تصویر با پس‌زمینه رنگی و لوگو در مرکز
    convert -size ${size}x${size} xc:"$BG_COLOR" \
        \( "$LOGO" -resize ${safe_zone_size}x${safe_zone_size} \) \
        -gravity center -composite "$output"
    
    if [ $? -eq 0 ]; then
        echo "✅ تولید شد: icon-maskable-${size}x${size}.png"
    else
        echo "❌ خطا در تولید: icon-maskable-${size}x${size}.png"
    fi
}

# تولید آیکون‌های استاندارد
echo ""
echo "🔨 تولید آیکون‌های استاندارد..."
generate_icon 72
generate_icon 96
generate_icon 128
generate_icon 144
generate_icon 152
generate_icon 192
generate_icon 384
generate_icon 512

# تولید آیکون‌های Maskable
echo ""
echo "🎭 تولید آیکون‌های Maskable..."
generate_maskable_icon 192
generate_maskable_icon 512

# تولید آیکون‌های Shortcut
echo ""
echo "⚡ تولید آیکون‌های Shortcut..."

# آیکون رزرو (استفاده از لوگو)
convert "$LOGO" -resize 96x96 -background transparent -gravity center -extent 96x96 "${OUTPUT_DIR}/shortcut-reserve.png"
echo "✅ تولید شد: shortcut-reserve.png"

# آیکون تخفیف (استفاده از لوگو با افکت)
convert "$LOGO" -resize 96x96 -background transparent -gravity center -extent 96x96 \
    -fill "#ff0000" -annotate +60+10 "%" \
    "${OUTPUT_DIR}/shortcut-discount.png" 2>/dev/null || \
    convert "$LOGO" -resize 96x96 -background transparent -gravity center -extent 96x96 "${OUTPUT_DIR}/shortcut-discount.png"
echo "✅ تولید شد: shortcut-discount.png"

# آیکون پروفایل (استفاده از لوگو)
convert "$LOGO" -resize 96x96 -background transparent -gravity center -extent 96x96 "${OUTPUT_DIR}/shortcut-profile.png"
echo "✅ تولید شد: shortcut-profile.png"

# تولید تصویر برای Apple Touch Icon
echo ""
echo "🍎 تولید Apple Touch Icon..."
convert "$LOGO" -resize 180x180 -background white -gravity center -extent 180x180 "${OUTPUT_DIR}/apple-touch-icon.png"
echo "✅ تولید شد: apple-touch-icon.png"

# تولید Favicon
echo ""
echo "🌐 تولید Favicon..."
convert "$LOGO" -resize 32x32 -background transparent -gravity center -extent 32x32 "${OUTPUT_DIR}/favicon-32x32.png"
echo "✅ تولید شد: favicon-32x32.png"

convert "$LOGO" -resize 16x16 -background transparent -gravity center -extent 16x16 "${OUTPUT_DIR}/favicon-16x16.png"
echo "✅ تولید شد: favicon-16x16.png"

echo ""
echo "✨ تمام آیکون‌ها با موفقیت تولید شدند!"
echo ""
echo "⚠️  یادآوری:"
echo "1. اسکرین‌شات‌های PWA را به صورت دستی از سایت بگیرید"
echo "2. فایل‌ها را بررسی کنید و در صورت نیاز ویرایش کنید"
echo "3. برای نتیجه بهتر، از تصویر با کیفیت بالاتر استفاده کنید"
echo ""


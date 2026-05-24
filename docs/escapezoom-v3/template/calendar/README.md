# تقویم شمسی - Persian Calendar Component

این کامپوننت یک تقویم شمسی کامل و قابل استفاده مجدد است که می‌تواند در هر صفحه‌ای استفاده شود.

## فایل‌های موجود

- `calendar-layout.php` - ساختار HTML و CSS تقویم
- `calendar-module.js` - ماژول JavaScript تقویم
- `calendar-example.php` - مثال استفاده از تقویم

## نحوه استفاده

### 1. اضافه کردن به صفحه

```php
<!-- Include Calendar Layout -->
<?php include get_template_directory() . '/template/calendar/calendar-layout.php'; ?>

<!-- Include Calendar Module -->
<script src="<?php echo get_template_directory_uri(); ?>/assets/js/calendar-module.js"></script>
```

### 2. ایجاد دکمه تقویم

```html
<button type="button" id="calendar-btn" class="your-button-classes">
    <span>انتخاب تاریخ</span>
    <!-- آیکون تقویم -->
</button>
```

### 3. راه‌اندازی JavaScript

```javascript
document.addEventListener('DOMContentLoaded', function() {
    const calendar = new PersianCalendar({
        onDateRangeSelected: function(dateRange) {
            // هنگامی که بازه تاریخی انتخاب شد
            console.log('تاریخ شروع:', dateRange.startDate);
            console.log('تاریخ پایان:', dateRange.endDate);
            console.log('تاریخ شروع میلادی:', dateRange.startGregorian);
            console.log('تاریخ پایان میلادی:', dateRange.endGregorian);
        },
        onDateRangeCleared: function() {
            // هنگامی که انتخاب پاک شد
            console.log('انتخاب پاک شد');
        }
    });
});
```

## API ماژول

### متدهای اصلی

#### `new PersianCalendar(options)`
ایجاد نمونه جدید از تقویم

**پارامترها:**
- `options.onDateRangeSelected` - تابع callback برای انتخاب بازه تاریخی
- `options.onDateRangeCleared` - تابع callback برای پاک کردن انتخاب

#### `calendar.openCalendarModal()`
باز کردن مودال تقویم

#### `calendar.closeCalendarModal()`
بستن مودال تقویم

#### `calendar.getSelectedDateRange()`
دریافت بازه تاریخی انتخاب شده

**خروجی:**
```javascript
{
    startDate: { year: 1403, month: 6, day: 15 },
    endDate: { year: 1403, month: 6, day: 20 },
    startGregorian: Date object,
    endGregorian: Date object
}
```

#### `calendar.setDateRange(startDate, endDate)`
تنظیم بازه تاریخی به صورت دستی

**پارامترها:**
- `startDate` - تاریخ شروع (شکل: `{year: 1403, month: 6, day: 15}`)
- `endDate` - تاریخ پایان (شکل: `{year: 1403, month: 6, day: 20}`)

#### `calendar.reset()`
پاک کردن تمام انتخاب‌ها و بازگشت به حالت اولیه

#### `calendar.formatPersianDate(date)`
تبدیل تاریخ میلادی به شمسی

**پارامتر:**
- `date` - تاریخ میلادی (Date object)

**خروجی:**
- رشته تاریخ شمسی (مثال: "15 خرداد 1403")

## ویژگی‌ها

- ✅ تقویم شمسی کامل
- ✅ انتخاب بازه تاریخی
- ✅ تبدیل دقیق تاریخ شمسی به میلادی و برعکس
- ✅ رابط کاربری زیبا و ریسپانسیو
- ✅ قابل استفاده مجدد در هر صفحه
- ✅ پشتیبانی از سال‌های کبیسه
- ✅ نمایش امروز
- ✅ ناوبری ماه‌ها

## مثال کامل

برای مشاهده مثال کامل، فایل `calendar-example.php` را بررسی کنید.

## نکات مهم

1. حتماً دکمه تقویم باید `id="calendar-btn"` داشته باشد
2. مودال تقویم باید `id="calendar-modal"` داشته باشد
3. برای استفاده در صفحات مختلف، فقط فایل‌های layout و module را include کنید
4. تمام توابع تبدیل تاریخ در ماژول موجود است و نیازی به تعریف مجدد نیست

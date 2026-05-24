<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Redirects;

/**
 * کدهای وضعیت HTTP برای ریدایرکت و توضیح هر کد.
 * منبع واحد برای ادمین، ایمپورت/اکسپورت و اجرای ریدایرکت.
 */
final class RedirectStatusCodes
{
    /** @var array<int, array{label: string, desc: string}> */
    private static array $codes = [
        300 => [
            'label' => '300 Multiple Choices',
            'desc'  => 'چند گزینه؛ کلاینت باید یکی را انتخاب کند (کم‌کاربرد).',
        ],
        301 => [
            'label' => '301 Moved Permanently',
            'desc'  => 'انتقال دائمی. برای سئو مناسب است؛ موتورهای جستجو اعتبار را به آدرس جدید منتقل می‌کنند.',
        ],
        302 => [
            'label' => '302 Found (موقت)',
            'desc'  => 'ریدایرکت موقت. مرورگر ممکن است درخواست را با GET تکرار کند (حتی اگر POST بوده).',
        ],
        303 => [
            'label' => '303 See Other',
            'desc'  => 'بعد از ارسال فرم؛ مرورگر با GET به آدرس جدید می‌رود. برای پاسخ به POST مناسب است.',
        ],
        307 => [
            'label' => '307 Temporary Redirect',
            'desc'  => 'موقت با حفظ متد (POST همچنان POST). برای نگهداری یا تغییر موقت آدرس مناسب است.',
        ],
        308 => [
            'label' => '308 Permanent Redirect',
            'desc'  => 'دائمی با حفظ متد (POST همچنان POST). مثل 301 ولی روش درخواست عوض نمی‌شود.',
        ],
    ];

    /** @return list<int> */
    public static function getAllCodes(): array
    {
        return array_keys(self::$codes);
    }

    /**
     * آیا این کد برای ریدایرکت معتبر است؟
     */
    public static function isValid(int $code): bool
    {
        return isset(self::$codes[$code]);
    }

    /**
     * @return array<int, array{label: string, desc: string}>
     */
    public static function getList(): array
    {
        return self::$codes;
    }

    public static function getLabel(int $code): string
    {
        return self::$codes[$code]['label'] ?? (string) $code;
    }

    public static function getDescription(int $code): string
    {
        return self::$codes[$code]['desc'] ?? '';
    }
}

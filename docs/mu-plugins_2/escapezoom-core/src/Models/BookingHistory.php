<?php

namespace EscapeZoom\Core\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * مدل BookingHistory - جدول wp_zb_booking_history در دیتابیس external
 * 
 * این جدول اطلاعات رزروها و سانس‌های بازی را نگهداری می‌کند
 */
class BookingHistory extends Model
{
    /**
     * نام اتصال دیتابیس
     *
     * @var string
     */
    protected $connection = 'external';

    /**
     * نام جدول
     *
     * @var string
     */
    protected $table = 'wp_zb_booking_history';

    /**
     * کلید اصلی جدول
     *
     * @var string
     */
    protected $primaryKey = 'booking_id';

    /**
     * آیا timestamps فعال است؟
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * فیلدهای قابل fill
     *
     * @var array
     */
    protected $fillable = [
        'customer_id',
        'wc_order_id',
        'status',
        'room_id',
        'booking_time',
        'booked_time',
        'name',
        'phone',
        'quantity',
        'level',
    ];

    /**
     * فیلدهای که باید cast شوند
     *
     * @var array
     */
    protected $casts = [
        'customer_id' => 'integer',
        'wc_order_id' => 'integer',
        'status' => 'integer',
        'room_id' => 'integer',
        'booking_time' => 'integer',
        'booked_time' => 'integer',
        'quantity' => 'integer',
        'level' => 'integer',
    ];

    /**
     * مقادیر پیش‌فرض برای attribute ها
     *
     * @var array
     */
    protected $attributes = [
        'status' => 1,
    ];

    /**
     * Scope برای فیلتر رزروهای یک سفارش خاص
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $orderId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByOrder($query, int $orderId)
    {
        return $query->where('wc_order_id', $orderId);
    }

    /**
     * Scope برای فیلتر رزروهای یک مشتری
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $customerId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Scope برای فیلتر رزروهای یک اتاق/محصول
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $roomId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByRoom($query, int $roomId)
    {
        return $query->where('room_id', $roomId);
    }

    /**
     * Scope برای فیلتر بر اساس وضعیت (1=رزرو شده، 2=باز)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStatus($query, int $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope برای رزروهای فعال (رزرو شده)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBooked($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope برای رزروهای باز (قابل رزرو)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 2);
    }

    /**
     * Scope برای فیلتر بر اساس بازه زمانی
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $from
     * @param int $to
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByTimeRange($query, int $from, int $to)
    {
        return $query->whereBetween('booking_time', [$from, $to]);
    }

    /**
     * Accessor برای تبدیل timestamp به تاریخ خوانا
     *
     * @return string|null
     */
    public function getBookingDateAttribute(): ?string
    {
        return $this->booking_time ? date('Y-m-d H:i:s', $this->booking_time) : null;
    }

    /**
     * Accessor برای تبدیل timestamp ثبت به تاریخ خوانا
     *
     * @return string|null
     */
    public function getBookedDateAttribute(): ?string
    {
        return $this->booked_time ? date('Y-m-d H:i:s', $this->booked_time) : null;
    }

    // Relations
    
    /**
     * رابطه با سفارش WooCommerce
     */
    public function order()
    {
        return $this->belongsTo(\Corcel\Model\Post::class, 'wc_order_id', 'ID');
    }

    /**
     * رابطه با مشتری
     */
    public function customer()
    {
        return $this->belongsTo(\Corcel\Model\User::class, 'customer_id', 'ID');
    }

    /**
     * رابطه با محصول WordPress
     */
    public function product()
    {
        return $this->belongsTo(\Corcel\Model\Post::class, 'room_id', 'ID');
    }

    /**
     * رابطه با اطلاعات محصول (external)
     */
    public function productData()
    {
        return $this->belongsTo(ProductData::class, 'room_id', 'product_id');
    }

    /**
     * رابطه با اطلاعات مارکتینگ سفارش
     */
    public function marketing()
    {
        return $this->belongsTo(Marketing::class, 'wc_order_id', 'order_id');
    }
}

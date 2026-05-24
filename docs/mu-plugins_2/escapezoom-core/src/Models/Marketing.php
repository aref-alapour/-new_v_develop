<?php

namespace EscapeZoom\Core\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * مدل Marketing - جدول wp_markting در دیتابیس default
 * 
 * این جدول اطلاعات مارکتینگ و گزارش‌های مالی سفارشات را نگهداری می‌کند
 */
class Marketing extends Model
{
    /**
     * نام اتصال دیتابیس (همیشه default تا در ez-ajax هم بدون اتصال wordpress کار کند)
     *
     * @var string
     */
    protected $connection = 'default';

    /**
     * نام جدول (بدون پیشوند؛ نام نهایی در getTable() با table_prefix ساخته می‌شود)
     *
     * @var string
     */
    protected $table = 'markting';

    /**
     * نام جدول برای کوئری: اگر اتصال پیشوند دارد فقط «markting» تا پیشوند یک‌بار اعمال شود؛ وگرنه «wp_markting».
     *
     * @return string
     */
    public function getTable()
    {
        $prefix = $this->getConnection()->getTablePrefix();
        if ($prefix !== '' && $prefix !== null) {
            return $this->table; // پیشوند اتصال اضافه می‌شود → wp_markting
        }
        return (isset($GLOBALS['table_prefix']) ? $GLOBALS['table_prefix'] : 'wp_') . $this->table;
    }

    /**
     * کلید اصلی جدول
     *
     * @var string
     */
    protected $primaryKey = 'order_id';

    /**
     * آیا auto increment است؟
     *
     * @var bool
     */
    public $incrementing = false;

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
        'order_id',
        'customer_id',
        'customer_firstname',
        'customer_lastname',
        'customer_phone',
        'customer_registered_at',
        'customer_level',
        'order_status',
        'order_created_at',
        'order_phones',
        'order_prepaid_tickets',
        'order_tickets_quantity',
        'order_refrerr',
        'order_coupon_used',
        'order_coupon_amount',
        'order_coupon_type',
        'order_transaction_id',
        'order_happycall',
        'order_paid',
        'order_online_paid',
        'order_payment_gateway',
        'order_payment_type',
        'order_user_level_discount',
        'order_is_satisfied',
        'order_deposit',
        'order_finall_price',
        'order_net_profit',
        'order_tax',
        'order_sans_time',
        'order_sans_day',
        'order_sans_date',
        'game_id',
        'game_name',
        'game_city',
        'game_area',
        'game_product_type',
        'game_genres',
        'game_duration',
        'game_brand',
        'game_sans_manager_id',
        'game_user_ebtal_id',
        'game_created_at',
        'order_financials_calculated',
        'complete_change_flag',
    ];

    /**
     * فیلدهای که باید cast شوند
     *
     * @var array
     */
    protected $casts = [
        'order_id' => 'integer',
        'customer_id' => 'integer',
        'customer_level' => 'integer',
        'customer_registered_at' => 'datetime',
        'order_created_at' => 'datetime',
        'order_phones' => 'array',
        'order_prepaid_tickets' => 'integer',
        'order_tickets_quantity' => 'integer',
        'order_coupon_amount' => 'decimal:2',
        'order_happycall' => 'integer',
        'order_paid' => 'decimal:2',
        'order_online_paid' => 'decimal:2',
        'order_user_level_discount' => 'decimal:2',
        'order_is_satisfied' => 'integer',
        'order_deposit' => 'decimal:2',
        'order_finall_price' => 'decimal:2',
        'order_net_profit' => 'decimal:2',
        'order_tax' => 'decimal:2',
        'order_sans_date' => 'date',
        'game_id' => 'integer',
        'game_duration' => 'integer',
        'game_sans_manager_id' => 'integer',
        'game_user_ebtal_id' => 'integer',
        'game_created_at' => 'datetime',
        'order_financials_calculated' => 'integer',
        'complete_change_flag' => 'integer',
    ];

    /**
     * Scope برای فیلتر بر اساس وضعیت سفارش
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStatus($query, string $status)
    {
        // اضافه کردن wc- اگر ندارد
        if (strpos($status, 'wc-') !== 0) {
            $status = 'wc-' . $status;
        }
        return $query->where('order_status', $status);
    }

    /**
     * Scope برای فیلتر بر اساس مشتری
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
     * Scope برای فیلتر بر اساس بازی/محصول
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $gameId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByGame($query, int $gameId)
    {
        return $query->where('game_id', $gameId);
    }

    /**
     * Scope برای فیلتر بر اساس شهر
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $city
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCity($query, string $city)
    {
        return $query->where('game_city', $city);
    }

    /**
     * Scope برای فیلتر بر اساس نوع محصول
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByProductType($query, string $type)
    {
        return $query->where('game_product_type', $type);
    }

    /**
     * Scope برای فیلتر بر اساس منبع ارجاع
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $referrer
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByReferrer($query, string $referrer)
    {
        return $query->where('order_refrerr', $referrer);
    }

    /**
     * Scope برای فیلتر بر اساس بازه تاریخ سفارش
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $from
     * @param string $to
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByDateRange($query, string $from, string $to)
    {
        return $query->whereBetween('order_created_at', [$from, $to]);
    }

    /**
     * Scope برای سفارشاتی که محاسبات مالی انجام شده
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFinancialsCalculated($query)
    {
        return $query->where('order_financials_calculated', 1);
    }

    /**
     * Scope برای سفارشاتی که محاسبات مالی انجام نشده
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFinancialsNotCalculated($query)
    {
        return $query->where('order_financials_calculated', '!=', 1)
                     ->orWhereNull('order_financials_calculated');
    }

    /**
     * Scope برای سفارشاتی که happy call انجام شده
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithHappyCall($query)
    {
        return $query->where('order_happycall', 1);
    }

    /**
     * Scope برای سفارشات با کد تخفیف
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithCoupon($query)
    {
        return $query->whereNotNull('order_coupon_used')
                     ->where('order_coupon_used', '!=', '');
    }

    /**
     * Accessor برای دریافت نام کامل مشتری
     *
     * @return string
     */
    public function getCustomerFullnameAttribute(): string
    {
        return trim($this->customer_firstname . ' ' . $this->customer_lastname);
    }

    /**
     * Accessor برای چک کردن رضایت مشتری
     *
     * @return bool|null
     */
    public function getIsSatisfiedAttribute(): ?bool
    {
        if ($this->attributes['order_is_satisfied'] === null || $this->attributes['order_is_satisfied'] == 0) {
            return null;
        }
        return $this->attributes['order_is_satisfied'] == 1;
    }

    // Relations
    
    /**
     * رابطه با سفارش WooCommerce
     */
    public function order()
    {
        return $this->belongsTo(\Corcel\Model\Post::class, 'order_id', 'ID');
    }

    /**
     * رابطه با مشتری
     */
    public function customer()
    {
        return $this->belongsTo(\Corcel\Model\User::class, 'customer_id', 'ID');
    }

    /**
     * رابطه با محصول/بازی
     */
    public function product()
    {
        return $this->belongsTo(\Corcel\Model\Post::class, 'game_id', 'ID');
    }

    /**
     * رابطه با مدیر سانس
     */
    public function sansManager()
    {
        return $this->belongsTo(\Corcel\Model\User::class, 'game_sans_manager_id', 'ID');
    }

    /**
     * رابطه با کاربر ابطال
     */
    public function ebtalUser()
    {
        return $this->belongsTo(\Corcel\Model\User::class, 'game_user_ebtal_id', 'ID');
    }

    /**
     * رابطه با رزرو
     */
    public function booking()
    {
        return $this->hasOne(BookingHistory::class, 'wc_order_id', 'order_id');
    }
}

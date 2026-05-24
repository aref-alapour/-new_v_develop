<?php

namespace EscapeZoom\Core\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * مدل Slot - جدول ez_slots
 *
 * سانس‌های قابل رزرو. روز کاری ۰۸:۰۰ تا ۰۷:۵۹ فردا؛
 * سانس بعد از نیمه‌شب با تاریخ همان روز ذخیره می‌شود.
 */
class Slot extends Model
{
    protected $connection = 'default';
    protected $table = 'ez_slots';

    protected $fillable = [
        'product_id',
        'slot_start_at',
        'slot_end_at',
        'status',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'slot_start_at' => 'datetime',
        'slot_end_at' => 'datetime',
    ];

    public const STATUS_AVAILABLE = 'available';
    public const STATUS_BOOKED = 'booked';
    public const STATUS_BLOCKED = 'blocked';
    public const STATUS_CANCELLED = 'cancelled';

    // Relations

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'slot_id');
    }
}

<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Games\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * UserContact (جدول wp_ez_user_contacts). مخاطبین هم‌تیمی — برای پیشنهاد در چک‌اوت.
 */
class UserContact extends Model
{
    public $timestamps = false;

    protected $connection = 'default';
    protected $table = 'ez_user_contacts';

    protected $fillable = [
        'user_id',
        'phone',
        'display_name',
        'usage_count',
        'last_used_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'usage_count' => 'integer',
        'last_used_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(EzUser::class, 'user_id');
    }
}

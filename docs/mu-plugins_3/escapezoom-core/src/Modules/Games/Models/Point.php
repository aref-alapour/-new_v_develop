<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Games\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Point (جدول wp_ez_points). یک امتیاز یونیک به ازای هر (user_id, reason, related_type, related_id).
 * user_id = کاربر دریافت‌کننده (wp_users.ID).
 */
class Point extends Model
{
    const UPDATED_AT = null;

    protected $connection = 'default';
    protected $table = 'ez_points';

    protected $fillable = [
        'user_id',
        'point',
        'reason',
        'action',
        'description',
        'related_type',
        'related_id',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'point' => 'integer',
        'related_id' => 'integer',
    ];
}

<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Games\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * AdvanceLog (جدول wp_ez_advance_log). لاگ درخواست‌های HTTP، admin-ajax و غیره.
 */
class AdvanceLog extends Model
{
    public $timestamps = false;

    protected $connection = 'default';
    protected $table = 'ez_advance_log';

    protected $fillable = [
        'request_url',
        'source_page',
        'duration',
        'log_time',
        'request_type',
        'action_name',
    ];

    protected $casts = [
        'duration' => 'float',
        'log_time' => 'datetime',
    ];
}

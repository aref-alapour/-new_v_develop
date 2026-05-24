<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Games\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * AffiliateClick (جدول wp_ez_affiliate_clicks). کلیک‌های وابسته — برای گزارش کمیسیون و لندینگ.
 * affiliate_id = wp_users.ID یا ez_users.id وابسته.
 */
class AffiliateClick extends Model
{
    protected $connection = 'default';
    protected $table = 'ez_affiliate_clicks';

    protected $fillable = [
        'affiliate_id',
        'ip_address',
        'landing_url',
        'session_id',
    ];

    protected $casts = [
        'affiliate_id' => 'integer',
    ];
}

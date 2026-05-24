<?php

namespace EscapeZoom\Core\Modules\Comments\Models;

use EscapeZoom\Core\Modules\Common\Models\BaseModel;

class CrmCommentAudit extends BaseModel
{
    protected $table = 'wp_ez_crm_comment_audit';

    protected $fillable = [
        'comment_id',
        'order_id',
        'user_id',
        'action',
        'payload',
        'created_at',
    ];
}

<?php

namespace EscapeZoom\Core\Modules\Comments\Services;

use EscapeZoom\Core\Modules\Comments\Models\CrmCommentAudit;

class CommentAuditService
{
    public function listRecent(int $limit = 50)
    {
        return CrmCommentAudit::query()->orderByDesc('id')->limit($limit)->get();
    }
}

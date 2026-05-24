<?php
global $wpdb;
$audit_actor_options = array();
if ( function_exists( 'ez_crm_comment_audit_table_name' ) ) {
    $audit_table = ez_crm_comment_audit_table_name();
    $audit_actor_options = $wpdb->get_results(
        "SELECT actor_user_id, actor_user_login, actor_display_name
         FROM `{$audit_table}`
         GROUP BY actor_user_id, actor_user_login, actor_display_name
         ORDER BY actor_display_name ASC, actor_user_id ASC",
        ARRAY_A
    );
}
?>
<div class="mb-6">
    <h1 class="text-base font-extrabold lg:text-2xl">گزارش عملیات کامنت</h1>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4 items-end">
    <div>
        <label class="block text-xs font-bold text-navyBlue mb-1">شناسه محصول (بازی)</label>
        <input type="number" id="audit_product_id" class="w-full border border-slate-105 rounded-lg px-3 py-2 text-sm" min="0" placeholder="0">
    </div>
    <div>
        <label class="block text-xs font-bold text-navyBlue mb-1">عنوان بازی (جستجو)</label>
        <input type="text" id="audit_product_title" class="w-full border border-slate-105 rounded-lg px-3 py-2 text-sm" placeholder="بخشی از عنوان">
    </div>
    <div class="md:col-span-2">
        <label class="block text-xs font-bold text-navyBlue mb-1">جستجوی نویسنده کامنت</label>
        <input type="text" id="audit_comment_user_query" class="w-full border border-slate-105 rounded-lg px-3 py-2 text-sm" placeholder="شناسه، نام، نام خانوادگی یا موبایل">
    </div>
    <div class="md:col-span-2">
        <label class="block text-xs font-bold text-navyBlue mb-1">عامل (ادمین)</label>
        <select id="audit_actor_user_id" class="w-full border border-slate-105 rounded-lg px-3 py-2 text-sm">
            <option value="">همه عامل‌ها</option>
            <?php foreach ( (array) $audit_actor_options as $opt ) : ?>
                <?php
                $actor_id    = (int) ( $opt['actor_user_id'] ?? 0 );
                $actor_login = (string) ( $opt['actor_user_login'] ?? '' );
                $actor_name  = (string) ( $opt['actor_display_name'] ?? '' );
                if ( $actor_id <= 0 && $actor_login === '' && $actor_name === '' ) {
                    continue;
                }
                $label = $actor_id . ' — ' . ( $actor_name !== '' ? $actor_name : '—' ) . ' (' . ( $actor_login !== '' ? $actor_login : '—' ) . ')';
                ?>
                <option value="<?php echo esc_attr( (string) $actor_id ); ?>"><?php echo esc_html( $label ); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="block text-xs font-bold text-navyBlue mb-1">نوع عملیات</label>
        <select id="audit_filter_action" class="w-full border border-slate-105 rounded-lg px-3 py-2 text-sm">
            <option value="">همه</option>
            <option value="approve">انتشار</option>
            <option value="hold">عدم نمایش</option>
            <option value="auto_hold">عدم نمایش سیستمی</option>
            <option value="trash">حذف</option>
            <option value="edit">ویرایش</option>
        </select>
    </div>
    <div class="md:col-span-2">
        <label class="block text-xs font-bold text-navyBlue mb-1">تاریخ ثبت کامنت (شمسی)</label>
        <div class="relative border border-slate-105 rounded-lg bg-white">
            <div class="flex items-center justify-between px-3 py-2 cursor-pointer" id="audit-comment-date-trigger">
                <span class="audit-comment-date-range-text text-sm placeholder text-gray-400">انتخاب بازه یا یک روز (تقویم)</span>
                <div class="flex items-center gap-2">
                    <button type="button" id="audit-clear-comment-dates" class="hidden text-gray-400 hover:text-gray-600 p-1" title="پاک کردن">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none" class="shrink-0"><path d="M1.5 9C1.5 6.17175 1.5 4.75725 2.379 3.879C3.258 3.00075 4.67175 3 7.5 3H10.5C13.3282 3 14.7427 3 15.621 3.879C16.4992 4.758 16.5 6.17175 16.5 9V10.5C16.5 13.3282 16.5 14.7427 15.621 15.621C14.742 16.4992 13.3282 16.5 10.5 16.5H7.5C4.67175 16.5 3.25725 16.5 2.379 15.621C1.50075 14.742 1.5 13.3282 1.5 10.5V9Z" stroke="#FF6900" stroke-width="1.5"/><path d="M5.25 3V1.875M12.75 3V1.875M1.875 6.75H16.125" stroke="#FF6900" stroke-width="1.5" stroke-linecap="round"/></svg>
                </div>
            </div>
        </div>
        <input type="hidden" id="audit_comment_created_from" value="">
        <input type="hidden" id="audit_comment_created_to" value="">
    </div>
    <div class="flex items-end">
        <button type="button" id="audit_filter_btn" class="w-full md:w-auto bg-primary-2 hover:bg-primary-deep text-white font-bold py-2 px-8 rounded-lg cursor-pointer">
            اعمال فیلتر
        </button>
    </div>
</div>

<?php include get_template_directory() . '/template/calendar/calendar-layout.php'; ?>

<div id="comment-audit-results"></div>

<div id="audit-detail-overlay" class="bg-black/40" style="display: none; position: fixed; inset: 0; z-index: 10050; align-items: center; justify-content: center; padding: 1rem;">
    <div id="audit-detail-modal" class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-modal overflow-hidden flex flex-col">
        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-105">
            <h2 class="text-lg font-yekan-bold text-navyBlue">جزئیات عملیات</h2>
            <button type="button" id="audit-detail-close" class="text-gray-500 hover:text-gray-800 p-1" aria-label="بستن">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div id="audit-detail-modal-content" class="p-4 overflow-y-auto text-right"></div>
    </div>
</div>

<script src="<?php echo esc_url( get_template_directory_uri() . '/assets/js/calendar-module.js' ); ?>"></script>
<script>
jQuery(document).ready(function($) {
    $('#audit-detail-overlay').appendTo('body');
    var tableState = {
        page: 1,
        sortBy: 'operated_at',
        sortDir: 'desc'
    };

    function auditFmtYmd(d) {
        if (!d || !(d instanceof Date) || isNaN(d.getTime())) return '';
        var y = d.getFullYear();
        var m = String(d.getMonth() + 1).padStart(2, '0');
        var day = String(d.getDate()).padStart(2, '0');
        return y + '-' + m + '-' + day;
    }

    var auditCommentCalendar = new PersianCalendar({
        onDateRangeSelected: function(dr) {
            if (!dr || !dr.startGregorian || !dr.endGregorian) return;
            $('#audit_comment_created_from').val(auditFmtYmd(dr.startGregorian));
            $('#audit_comment_created_to').val(auditFmtYmd(dr.endGregorian));
            var persianMonths = ['فروردین','اردیبهشت','خرداد','تیر','مرداد','شهریور','مهر','آبان','آذر','دی','بهمن','اسفند'];
            var s = dr.startDate;
            var e = dr.endDate;
            var startStr = s.day + ' ' + persianMonths[s.month - 1] + ' ' + s.year;
            var endStr = e.day + ' ' + persianMonths[e.month - 1] + ' ' + e.year;
            $('.audit-comment-date-range-text').text(startStr + ' تا ' + endStr).removeClass('placeholder text-gray-400').addClass('text-navyBlue');
            $('#audit-clear-comment-dates').removeClass('hidden');
        },
        onDateRangeCleared: function() {
            $('#audit_comment_created_from').val('');
            $('#audit_comment_created_to').val('');
            $('.audit-comment-date-range-text').text('انتخاب بازه یا یک روز (تقویم)').addClass('placeholder text-gray-400').removeClass('text-navyBlue');
            $('#audit-clear-comment-dates').addClass('hidden');
        }
    });

    $('#audit-comment-date-trigger').on('click', function(e) {
        e.stopPropagation();
        auditCommentCalendar.openCalendarModal();
    });

    $('#audit-clear-comment-dates').on('click', function(e) {
        e.stopPropagation();
        auditCommentCalendar.clearSelection();
    });

    function loadAuditPage(page) {
        tableState.page = page;
        var $box = $('#comment-audit-results');
        $box.html('<div class="py-10 text-center text-gray-500">در حال بارگذاری...</div>');
        $.ajax({
            type: 'POST',
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            data: {
                action: 'team_ajax_handler',
                nonce: '<?php echo wp_create_nonce('team-ajax-nonce'); ?>',
                callback: 'comment_audit_list',
                page: tableState.page,
                product_id: $('#audit_product_id').val(),
                product_title: $('#audit_product_title').val(),
                comment_user_query: $('#audit_comment_user_query').val(),
                actor_user_id: $('#audit_actor_user_id').val(),
                filter_action: $('#audit_filter_action').val(),
                comment_created_from: $('#audit_comment_created_from').val(),
                comment_created_to: $('#audit_comment_created_to').val(),
                sort_by: tableState.sortBy,
                sort_dir: tableState.sortDir
            },
            success: function(html) {
                $box.html(html);
            },
            error: function() {
                $box.html('<div class="text-red-500 text-center py-8">خطا در دریافت گزارش.</div>');
            }
        });
    }

    loadAuditPage(1);

    $('#audit_filter_btn').on('click', function() {
        tableState.page = 1;
        loadAuditPage(1);
    });

    $('body').on('click', '#comment-audit-results .audit-page-link', function(e) {
        e.preventDefault();
        var p = parseInt($(this).data('page'), 10);
        loadAuditPage(isNaN(p) ? 1 : p);
    });

    $('body').on('click', '#comment-audit-results .audit-sort-link', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var sortBy = String($btn.data('sort-by') || '');
        var sortDir = String($btn.data('sort-dir') || '').toLowerCase();
        if (!sortBy) return;
        tableState.sortBy = sortBy;
        tableState.sortDir = (sortDir === 'asc' || sortDir === 'desc') ? sortDir : 'desc';
        tableState.page = 1;
        loadAuditPage(1);
    });

    function closeAuditDetailModal() {
        $('#audit-detail-overlay').css('display', 'none');
        $('#audit-detail-modal-content').empty();
    }

    $('body').on('click', '.audit-detail-btn', function() {
        var id = $(this).data('audit-id');
        var $content = $('#audit-detail-modal-content');
        $content.html('<div class="py-8 text-center text-gray-500">در حال بارگذاری...</div>');
        $('#audit-detail-overlay').css('display', 'flex');
        $.ajax({
            type: 'POST',
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            data: {
                action: 'team_ajax_handler',
                nonce: '<?php echo wp_create_nonce('team-ajax-nonce'); ?>',
                callback: 'comment_audit_detail',
                audit_id: id
            },
            success: function(html) {
                $content.html(html);
            },
            error: function() {
                $content.html('<p class="text-red-600 text-center">خطا در دریافت جزئیات.</p>');
            }
        });
    });

    $('#audit-detail-close').on('click', closeAuditDetailModal);

    $('#audit-detail-overlay').on('click', function(e) {
        if (e.target === this) {
            closeAuditDetailModal();
        }
    });

    $('#audit-detail-modal').on('click', function(e) {
        e.stopPropagation();
    });
});
</script>

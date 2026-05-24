<?php
global $wpdb;

if ( ! function_exists( 'ez_crm_comment_audit_table_name' ) ) {
    echo '<p class="text-center text-gray-500 py-8">ماژول لاگ کامنت بارگذاری نشده است.</p>';
    return;
}

$table = ez_crm_comment_audit_table_name();

$page     = max( 1, intval( $_POST['page'] ?? 1 ) );
$per_page = 25;
$offset   = ( $page - 1 ) * $per_page;
$sort_by  = isset( $_POST['sort_by'] ) ? sanitize_key( wp_unslash( $_POST['sort_by'] ) ) : 'operated_at';
$sort_dir = isset( $_POST['sort_dir'] ) ? strtolower( sanitize_text_field( wp_unslash( $_POST['sort_dir'] ) ) ) : 'desc';

$w = array();
$p = array();

if ( $v = intval( $_POST['product_id'] ?? 0 ) ) {
    $w[] = 'product_id = %d';
    $p[] = $v;
}

$pt = isset( $_POST['product_title'] ) ? sanitize_text_field( wp_unslash( $_POST['product_title'] ) ) : '';
if ( $pt !== '' ) {
    $w[] = 'product_title LIKE %s';
    $p[] = '%' . $wpdb->esc_like( $pt ) . '%';
}

$cuq = isset( $_POST['comment_user_query'] ) ? sanitize_text_field( wp_unslash( $_POST['comment_user_query'] ) ) : '';
if ( $cuq === '' && ! empty( $_POST['comment_user_id'] ) ) {
    $cuq = (string) intval( $_POST['comment_user_id'] );
}
$cu_ids = function_exists( 'ez_crm_comment_audit_resolve_comment_author_ids' )
    ? ez_crm_comment_audit_resolve_comment_author_ids( $cuq )
    : ez_crm_comment_audit_resolve_user_ids( $cuq );
if ( is_array( $cu_ids ) ) {
    ez_crm_comment_audit_where_user_ids( 'comment_user_id', $cu_ids, $w, $p );
}

$actor_user_id = isset( $_POST['actor_user_id'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['actor_user_id'] ) ) : 0;
if ( $actor_user_id > 0 ) {
    $w[] = 'actor_user_id = %d';
    $p[] = $actor_user_id;
}

$action = isset( $_POST['filter_action'] ) ? sanitize_text_field( wp_unslash( $_POST['filter_action'] ) ) : '';
if ( $action !== '' && in_array( $action, array( 'approve', 'hold', 'auto_hold', 'trash', 'edit' ), true ) ) {
    $w[] = 'action = %s';
    $p[] = $action;
}

$cc_from = isset( $_POST['comment_created_from'] ) ? sanitize_text_field( wp_unslash( $_POST['comment_created_from'] ) ) : '';
$cc_to   = isset( $_POST['comment_created_to'] ) ? sanitize_text_field( wp_unslash( $_POST['comment_created_to'] ) ) : '';
if ( $cc_from !== '' && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $cc_from ) ) {
    $w[] = 'comment_created_at IS NOT NULL AND DATE(comment_created_at) >= %s';
    $p[] = $cc_from;
}
if ( $cc_to !== '' && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $cc_to ) ) {
    $w[] = 'comment_created_at IS NOT NULL AND DATE(comment_created_at) <= %s';
    $p[] = $cc_to;
}

$sql_where = empty( $w ) ? '1=1' : implode( ' AND ', $w );

$sortable_map = array(
    'actor'              => 'actor_display_name',
    'product'            => 'product_title',
    'action'             => 'action',
    'comment_created_at' => 'comment_created_at',
    'operated_at'        => 'operated_at',
);

if ( ! isset( $sortable_map[ $sort_by ] ) ) {
    $sort_by = 'operated_at';
}
if ( ! in_array( $sort_dir, array( 'asc', 'desc' ), true ) ) {
    $sort_dir = 'desc';
}
$order_by = $sortable_map[ $sort_by ] . ' ' . strtoupper( $sort_dir ) . ', id DESC';

$count_sql = "SELECT COUNT(*) FROM `{$table}` WHERE {$sql_where}";
$total     = empty( $p ) ? (int) $wpdb->get_var( $count_sql ) : (int) $wpdb->get_var( $wpdb->prepare( $count_sql, $p ) );

$total_pages = max( 1, (int) ceil( $total / $per_page ) );

$data_sql = "SELECT * FROM `{$table}` WHERE {$sql_where} ORDER BY {$order_by} LIMIT %d OFFSET %d";
$p_data   = $p;
$p_data[] = $per_page;
$p_data[] = $offset;

$rows = $wpdb->get_results( $wpdb->prepare( $data_sql, $p_data ), ARRAY_A );

$action_labels = array(
    'approve' => 'انتشار',
    'hold'    => 'عدم نمایش',
    'auto_hold' => 'عدم نمایش سیستمی',
    'trash'   => 'حذف',
    'edit'    => 'ویرایش',
);

$sortable_th = static function ( $key, $label ) use ( $sort_by, $sort_dir ) {
    $is_active = ( $sort_by === $key );
    $next_dir  = ( $is_active && $sort_dir === 'asc' ) ? 'desc' : 'asc';
    $arrow     = '↕';
    if ( $is_active ) {
        $arrow = ( $sort_dir === 'asc' ) ? '↑' : '↓';
    }
    return sprintf(
        '<button type="button" class="audit-sort-link inline-flex items-center gap-1 hover:text-[#344054]" data-sort-by="%1$s" data-sort-dir="%2$s">%3$s <span class="text-[11px]">%4$s</span></button>',
        esc_attr( $key ),
        esc_attr( $next_dir ),
        esc_html( $label ),
        esc_html( $arrow )
    );
};
?>

<div class="overflow-x-auto border border-[#E4EBF0] rounded-xl mt-4">
    <table class="min-w-full text-sm font-yekan-bold">
        <thead class="bg-gray-100 text-[#90A1B9]">
            <tr>
                <th class="px-3 py-2 text-right whitespace-nowrap">ردیف</th>
                <th class="px-3 py-2 text-right">کامنت</th>
                <th class="px-3 py-2 text-right"><?php echo $sortable_th( 'product', 'بازی' ); ?></th>
                <th class="px-3 py-2 text-right">نویسنده</th>
                <th class="px-3 py-2 text-right"><?php echo $sortable_th( 'actor', 'عامل' ); ?></th>
                <th class="px-3 py-2 text-right"><?php echo $sortable_th( 'action', 'نوع' ); ?></th>
                <th class="px-3 py-2 text-right"><?php echo $sortable_th( 'comment_created_at', 'تاریخ کامنت' ); ?></th>
                <th class="px-3 py-2 text-right"><?php echo $sortable_th( 'operated_at', 'زمان عملیات' ); ?></th>
                <th class="px-3 py-2 text-right">دلیل</th>
                <th class="px-3 py-2 text-right whitespace-nowrap">جزئیات</th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $rows ) ) : ?>
                <tr>
                    <td colspan="10" class="px-3 py-8 text-center text-gray-500">رکوردی یافت نشد.</td>
                </tr>
            <?php else : ?>
                <?php foreach ( $rows as $idx => $row ) : ?>
                    <?php
                    $row_num   = $offset + (int) $idx + 1;
                    $act       = (string) ( $row['action'] ?? '' );
                    $badge_st  = ez_crm_comment_audit_action_badge_style( $act );
                    $lbl       = $action_labels[ $act ] ?? $act;
                    ?>
                    <tr class="border-t border-[#E8EDF1] hover:bg-gray-50/80">
                        <td class="px-3 py-2 align-top whitespace-nowrap font-bold"><?php echo (int) $row_num; ?></td>
                        <td class="px-3 py-2"><?php echo (int) $row['comment_id']; ?></td>
                        <td class="px-3 py-2 max-w-[180px] truncate" title="<?php echo esc_attr( $row['product_title'] ); ?>"><?php echo esc_html( $row['product_title'] ); ?></td>
                        <td class="px-3 py-2"><?php echo (int) $row['comment_user_id']; ?><br><span class="text-xs text-gray-500 font-normal"><?php echo esc_html( $row['comment_author_name'] ); ?></span></td>
                        <td class="px-3 py-2"><?php echo (int) $row['actor_user_id']; ?><br><span class="text-xs text-gray-500 font-normal"><?php echo esc_html( $row['actor_display_name'] ); ?></span></td>
                        <td class="px-3 py-2">
                            <span class="inline-block px-2 py-0.5 rounded-md text-xs font-bold" style="<?php echo esc_attr( $badge_st ); ?>"><?php echo esc_html( $lbl ); ?></span>
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap"><?php echo esc_html( ez_crm_comment_audit_format_jalali_datetime( $row['comment_created_at'] ?? null ) ); ?></td>
                        <td class="px-3 py-2 whitespace-nowrap"><?php echo esc_html( ez_crm_comment_audit_format_jalali_datetime( $row['operated_at'] ?? null ) ); ?></td>
                        <td class="px-3 py-2 max-w-[200px] truncate" title="<?php echo esc_attr( $row['reason'] ); ?>"><?php echo esc_html( wp_trim_words( (string) $row['reason'], 12 ) ); ?></td>
                        <td class="px-3 py-2 align-top whitespace-nowrap">
                            <button type="button" class="audit-detail-btn text-xs text-[#FD7013] hover:underline cursor-pointer font-yekan-bold" data-audit-id="<?php echo (int) $row['id']; ?>">
                                مشاهده
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ( $total_pages > 1 ) : ?>
    <div class="flex flex-wrap justify-center gap-2 mt-6 mb-4 comment-audit-pagination">
        <?php
        $start = max( 1, $page - 2 );
        $end   = min( $total_pages, $page + 2 );
        if ( $page > 1 ) {
            echo '<button type="button" class="audit-page-link px-3 py-1 rounded border border-[#E4EBF0] hover:bg-gray-50 text-sm" data-page="' . (int) ( $page - 1 ) . '">قبلی</button>';
        }
        for ( $i = $start; $i <= $end; $i++ ) {
            $cls = ( $i === $page ) ? 'bg-[#FD7013] text-white border-[#FD7013]' : 'border-[#E4EBF0] hover:bg-gray-50';
            echo '<button type="button" class="audit-page-link px-3 py-1 rounded border text-sm ' . esc_attr( $cls ) . '" data-page="' . (int) $i . '">' . (int) $i . '</button>';
        }
        if ( $page < $total_pages ) {
            echo '<button type="button" class="audit-page-link px-3 py-1 rounded border border-[#E4EBF0] hover:bg-gray-50 text-sm" data-page="' . (int) ( $page + 1 ) . '">بعدی</button>';
        }
        ?>
    </div>
<?php endif; ?>

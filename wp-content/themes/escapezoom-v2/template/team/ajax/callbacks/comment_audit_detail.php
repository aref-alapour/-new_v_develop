<?php
global $wpdb;

if ( ! function_exists( 'ez_crm_comment_audit_table_name' ) ) {
    echo '<p class="text-red-600">ماژول لاگ بارگذاری نشده.</p>';
    return;
}

$id = (int) ( $_POST['audit_id'] ?? 0 );
if ( $id <= 0 ) {
    echo '<p class="text-red-600">شناسه نامعتبر است.</p>';
    return;
}

$table = ez_crm_comment_audit_table_name();
$row   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$table}` WHERE id = %d", $id ), ARRAY_A );

if ( ! $row ) {
    echo '<p class="text-gray-600">رکورد یافت نشد.</p>';
    return;
}

$action_labels = array(
    'approve' => 'انتشار',
    'hold'    => 'عدم نمایش',
    'auto_hold' => 'عدم نمایش سیستمی',
    'trash'   => 'حذف',
    'edit'    => 'ویرایش',
);
$act_lbl = $action_labels[ $row['action'] ] ?? $row['action'];

$reason  = isset( $row['reason'] ) ? (string) $row['reason'] : '';
$details = isset( $row['details'] ) ? (string) $row['details'] : '';
?>
<div class="space-y-4 text-right text-sm font-yekan-bold text-navyBlue">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs text-gray-600">
        <div><span class="font-bold text-gray-800">شناسه لاگ:</span> <?php echo (int) $row['id']; ?></div>
        <div><span class="font-bold text-gray-800">نوع عملیات:</span> <?php echo esc_html( $act_lbl ); ?></div>
        <div><span class="font-bold text-gray-800">عامل:</span> <?php echo (int) $row['actor_user_id']; ?> — <?php echo esc_html( $row['actor_display_name'] ); ?> (<?php echo esc_html( $row['actor_user_login'] ); ?>)</div>
        <div><span class="font-bold text-gray-800">زمان عملیات:</span> <?php echo esc_html( ez_crm_comment_audit_format_jalali_datetime( $row['operated_at'] ?? null ) ); ?></div>
    </div>
    <div>
        <p class="font-bold text-gray-800 mb-1">دلیل (ادمین)</p>
        <div class="bg-gray-50 border border-[#E4EBF0] rounded-lg p-3 whitespace-pre-wrap"><?php echo esc_html( $reason !== '' ? $reason : '—' ); ?></div>
    </div>
    <div>
        <p class="font-bold text-gray-800 mb-1">جزئیات عملیات</p>
        <pre class="bg-gray-50 border border-[#E4EBF0] rounded-lg p-3 text-xs font-normal whitespace-pre-wrap break-words max-w-full max-h-80 overflow-y-auto overflow-x-hidden" style="text-wrap: auto;"><?php echo esc_html( $details !== '' ? $details : '—' ); ?></pre>
    </div>
</div>

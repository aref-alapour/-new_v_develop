<?php
global $wpdb;

$user   = sanitize_text_field($_POST['user'] ?? '');
$id     = intval($_POST['id'] ?? 0);

if (empty($user) || $id <= 0)
    return;

$item = $wpdb->get_row($wpdb->prepare("SELECT `read` FROM `notifications` WHERE id = %d", $id));

if (!$item)
    return;

$read = $item->read !== null ? unserialize($item->read) : [];

if ( ! in_array( $user, $read ) ) {
	$read[] = $user;

    $result = $wpdb->update( 'notifications', [ 'read' => serialize( $read ) ], [ 'id' => $id ] );
    if (false === $result)
        error_log('عدم ذخیره سین شدن نوتیف برای این آی دی : ' . $id);
}

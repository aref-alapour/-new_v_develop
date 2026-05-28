<?php
use EscapeZoom\Core\Support\TehranTime;
function zr_get_today_start_timestamp() { return TehranTime::tehranMidnightUnix(time()); }
function zr_get_sans_display_data($pid, $timestamp = null) {
    if (!$timestamp) $timestamp = time();
    $dayTs = TehranTime::tehranMidnightUnix($timestamp);
    global $wpdb;
    $p = $wpdb->get_row($wpdb->prepare("SELECT * FROM zb_product_list WHERE product_id = %d", $pid));
    if (!$p) return [];
    $booked = $wpdb->get_col($wpdb->prepare("SELECT booking_time FROM zb_booking_history WHERE room_id = %d AND booking_time >= %d AND booking_time < %d", $pid, $dayTs, $dayTs + 86400));
    $sched = @unserialize($p->schedule);
    $dayType = (new \EscapeZoom\Core\Modules\Booking\Domain\DayTypeResolver())->resolve($dayTs);
    $sanses = $sched[$dayType] ?? [];
    $res = [];
    foreach ($sanses as $s) {
        $sts = TehranTime::slotTimestamp($dayTs, $s['time']);
        $res[] = ['time' => $s['time'], 'price' => $s['price'], 'full' => in_array($sts, $booked), 'ts' => $sts];
    }
    return $res;
}

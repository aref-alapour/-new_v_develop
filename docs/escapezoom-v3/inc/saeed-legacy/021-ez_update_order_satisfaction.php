<?php
/**
 * ez_update_order_satisfaction
 *
 * توابع: ez_update_order_satisfaction
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 1129-1155)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ez_update_order_satisfaction($order_id, $new_value) {
    if (empty($order_id) || !is_numeric($order_id))
        return false;

    $new_value = intval($new_value);

    if (!in_array($new_value, [0,1], true))
        return false;

    if (!function_exists('ez_order_satisfaction_set_status'))
        return false;

    $status = $new_value === 1 ? EZ_ORDER_SATISFACTION_SATISFIED : EZ_ORDER_SATISFACTION_DISSATISFIED;

    $game_id = (int) get_post_meta($order_id, 'code_otagh', true);
    if ($game_id < 1) {
        $medoo = medoo();
        if ($medoo) {
            $mrow = $medoo->get('wp_markting', ['game_id'], ['order_id' => (int) $order_id]);
            if ($mrow && !empty($mrow['game_id'])) {
                $game_id = (int) $mrow['game_id'];
            }
        }
    }

    return ez_order_satisfaction_set_status((int) $order_id, $status, 'legacy_ez_update_order_satisfaction', ['game_id' => $game_id]);
}

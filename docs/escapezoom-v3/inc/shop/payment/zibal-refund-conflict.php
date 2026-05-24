<?php
/**
 * Shop module (migrated from saeed-codes.php).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/****************************************************************************************************************************************/
function ez_refund ( $track_id ) {

    $refund_msg = 'بابت استرداد';

    $res = wp_remote_post( 'https://api.zibal.ir/v1/wallet/refund', array (
        'method'        => 'POST',
        'timeout'       => 45,
        'redirection'   => 5,
        'httpversion'   => '1.0',
        'blocking'      => true,
        'headers'       => array('Authorization' => 'Bearer 453addf8358f4140ac087308f14c6d45'),
        'body'          => array('trackId' => $track_id, 'description' => $refund_msg, "wageMode" => 2),
        'cookies'       => array()
    ) );

    return $res;
}
/****************************************************************************************************************************************/
//add_shortcode('order_conflict_oops_page', 'order_conflict_oops_page'); // tadakhol
function order_conflict_oops_page () {
    do_action('order_conflict_happened', base64_url_decode( base64_url_decode( $_GET['order_id'] ) ));
}
/****************************************************************************************************************************************/
//add_action('order_conflict_happened', 'order_conflict_handling', 10, 1);
function order_conflict_handling ($order_id) {

    $res = ez_refund( get_post_meta($order_id, '_transaction_id', true) );
    $obj = json_decode($res['body']);

    if ( $obj->status == 1 && $obj->result == 1 ) {

        $amount         = number_format( (int)(substr($obj->data->amount, 0, -1)) );
        $predictedTime  = explode( 'T', $obj->data->predictedTime );

        $date = jdate('l j F', strtotime($predictedTime[0]));
        $time = jdate('H:i', strtotime($predictedTime[1]));

        $txt = "$amount;$time;$date";
        add_to_sms_queue (434391,substr(get_post_meta($order_id, '_billing_phone', true), 1), $txt, $order_id, 'refund'); ?>

        <div id="ez_refund_msg_header_img"><img src="http://escapezoom.ir/wp-content/uploads/2023/12/refund_page.png"></div>

        <div id="ez_refund_msg_header">اوه! با اینکه پرداخت شما انجام شد اما یکی سریع تر از تو بود و زودتر این سانس رو رزرو کرد!</div>

        <div id="ez_refund_msg_wrapper">اما نگران نباش! مبلغ <?php echo $amount; ?> تومان تا حداکثر ساعت <?php echo $time; ?> روز <?php echo $date; ?> به حسابت برمیگرده.</div>

        <div id="ez_refund_msg_agency_phone">
            <p>اگه سوالی داری با شماره زیر تماس بگیر:</p>
            <p><a href="tel:+982191307900" style="color: #ffffff;background: #f96f0c;padding: 5px;border-radius: 8px;text-align: center;width: 160px;">02191307900</a></p>
        </div>

        <?php
    } elseif ( $obj->result == 10 ) { ?>
        <div id="ez_refund_msg_wrapper">مبلغ برای شما مسترد شده است. لطفا صبور باشید.</div>
        <?php
    } ?>

    <style>
        #ez_refund_msg_header {
            line-height: 2;
            font-size: 18px;
            margin: 15px 0;
            text-align: center;
        }
        #ez_refund_msg_header_img {
            text-align: center;
        }
        #ez_refund_msg_header_img img{
            border-radius: 8px;
        }
        #ez_refund_msg_wrapper {
            text-align: center;
            font-weight: bold;
            background: #f96f0c;
            padding: 20px;
            border-radius: 8px;
            color: #fff;
            font-size: 16px;
            text-align: justify;
            line-height: 37px;
            text-align: center;
        }
        #post-30102 .entry-title {
            display: none;
        }
        #ez_refund_msg_agency_phone {
            background: #e3e3e3;
            margin: 10px 0;
            border-radius: 8px;
            padding: 20px 10px 0;
            border: 1px solid #c9c9c9;
            text-align: center;
        }
    </style>

    <?php
}
/****************************************************************************************************************************************/

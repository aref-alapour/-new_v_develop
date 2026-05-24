<?php
/** lines 120-169 → shop/core/reservation-core.php */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ez_webservice( $data ) {
    if ( $_SERVER['HTTP_HOST'] == 'wo.escapezoom.local' ) {
        $base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/web-service/web-service.php';
    } elseif ( $_SERVER['HTTP_HOST'] == 'wo.escapezoom.local' ) {
        $base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/web-service/web-service.php';
    } else {
        $base_url = 'https://' . $_SERVER['HTTP_HOST'] . '/web-service/web-service.php';
    }
    $response = wp_remote_post( $base_url, array(
        'method'        => 'POST',
        'timeout'       => 45,
        'redirection'   => 5,
        'httpversion'   => '1.0',
        'blocking'      => true,
        'headers'       => ['Content-Type' => 'application/json'],
        'body'          => json_encode($data),
        'cookies'       => array()
    ) );

    if ( is_array($response) ){
        return $response['body'];
    }
}
/****************************************************************************************************************************************/
function ez_reservation( $data ) {
    if ( $_SERVER['HTTP_HOST'] == 'wo.escapezoom.local' ) {
        $base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/web-service/reservation.php';
    } elseif ( $_SERVER['HTTP_HOST'] == 'wo.escapezoom.local' ) {
        $base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/web-service/reservation.php';
    } else {
        $base_url = 'https://' . $_SERVER['HTTP_HOST'] . '/web-service/reservation.php';
    }

    $response = wp_remote_post( $base_url, array(
        'method'        => 'POST',
        'timeout'       => 45,
        'redirection'   => 5,
        'httpversion'   => '1.0',
        'blocking'      => true,
        'headers'       => array(),
        'body'          => $data,
        'cookies'       => array()
    ) );

    if ( wp_remote_retrieve_response_code( $response ) == 200 )
        if ( is_array($response) )
            return $response['body'];
        else
            return ['error' => wp_remote_retrieve_response_code( $response )];
}

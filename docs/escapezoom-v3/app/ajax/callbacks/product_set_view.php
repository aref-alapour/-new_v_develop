<?php
$medoo = medoo();

$product_id     = (int) $_POST['product_id'];
$ip             = $_POST['ip'];
$today          = date('Y-m-d');
$active_state   = get_field('product_state', $product_id);

if ( $active_state == 'expired' or $active_state == 'deactivated' )
    return;

if ($ip) {
    $check = $medoo->get('ip_checker', '*', [
        'product_id'    => $product_id,
        'ip'            => $ip,
        'date'          => $today
    ]);

    if ($check)
        $medoo->update('ip_checker', [
            'count[+]' => 1
        ], [
            'id' => $check['id']
        ]);

    else {

        $medoo->insert('ip_checker', [
            'product_id'    => $product_id,
            'ip'            => $ip,
            'date'          => $today,
            'count'         => 1
        ]);

        $view_row = $medoo->get('product_views', '*', [
            'product_id'    => $product_id,
            'date'          => $today
        ]);

        if ($view_row)
            $medoo->update('product_views', [
                'count[+]' => 1
            ], [
                'id' => $view_row['id']
            ]);

        else
            $medoo->insert('product_views', [
                'product_id'    => $product_id,
                'date'          => $today,
                'count'         => 1
            ]);
    }

    if ( $product_id > 0 ) {
        do_action( 'ez_ranking_recalculate', $product_id, [ 'popular', 'hottest' ] );
    }
}
<?php
function array_msort($array, $cols) {
    $colarr = array();
    foreach ($cols as $col => $order) {
        $colarr[$col] = array();
        foreach ($array as $k => $row) {
            $colarr[$col]['_' . $k] = strtolower($row[$col]);
        }
    }

    $eval = 'array_multisort(';
    foreach ($cols as $col => $order) {
        $eval .= '$colarr[\'' . $col . '\'],' . $order . ',';
    }

    $eval = substr($eval, 0, -1) . ');';
    eval($eval);
    $ret = array();
    foreach ($colarr as $col => $arr) {
        foreach ($arr as $k => $v) {
            $k = substr($k, 1);
            if (!isset($ret[$k])) $ret[$k] = $array[$k];
            $ret[$k][$col] = $array[$k][$col];
        }
    }

    $sorted_cat_arr['tehran']   = array();
    $sorted_cat_arr['alborz']   = array();
    $sorted_cat_arr['esfehan']  = array();
    $sorted_cat_arr['others']   = array();

    foreach ( $ret as $ord ) {

        if ( $ord['cat_id'] == 15 )
            $sorted_cat_arr['tehran'][] = $ord;

        else if ( $ord['cat_id'] == 162 )
            $sorted_cat_arr['alborz'][] = $ord;

        else if ( $ord['cat_id'] == 122 )
            $sorted_cat_arr['esfehan'][] = $ord;

        else
            $sorted_cat_arr['others'][] = $ord;

    }

    return $sorted_cat_arr;
}

$prevMonth = date('m', mktime(0, 0, 0, date('m'), 0, date('Y')));
$today = getdate();
function sale_mount_ago() {
    $args = array (
        'post_type'         => 'shop_order',
        'posts_per_page'    => -1,
        'post_status'       => 'any',
        'post_status'       => array('wc-partially-paid', 'wc-completed'),
        'date_query'        => array (
            array (
                'after' => date('Y-m-d', strtotime('-30 days'))
            ),
        )
    );

    $the_query = new WP_Query($args);
    if ($the_query->have_posts()) {
        $result = [];
        $status_order = ['partially-paid', 'processing', 'completed'];
        while ($the_query->have_posts()) {
            $the_query->the_post();
            $order = wc_get_order(get_the_ID());
            if (in_array($order->get_status(), $status_order)) {
                foreach ($order->get_items() as $item) {
                    $product_id = $item->get_product_id(); // the Product id
                    $item_name  = $item->get_name(); // Name of the product
                    $quantity   = $item->get_quantity();
                    $line_total = $item->get_total(); // Line total (discounted)
                    $cat_name   = wp_get_post_terms( $product_id, 'product_cat', array('fields' => 'names') );
                    $cat_id     = wp_get_post_terms( $product_id, 'product_cat', array('fields' => 'ids') );

                    if (array_key_exists($product_id, $result)) {
                        $result[$product_id]["count"] = $result[$product_id]["count"] + $quantity;
                        $result[$product_id]["total"] = $result[$product_id]["total"] + $line_total;
                    } else {
                        $result[$product_id]["id"]          = $product_id;
                        $result[$product_id]["count"]       = $quantity;
                        $result[$product_id]["name"]        = $item_name;
                        $result[$product_id]["total"]       = $line_total;
                        $result[$product_id]["cat_name"]    = $cat_name[0];
                        $result[$product_id]["cat_id"]      = $cat_id[0];
                    }
                }
            }
        }

        if ($result)
            update_option('sale_mount_ago1', array_msort($result, array('count' => SORT_DESC)));
    }
}

if (isset($_GET["update_list"]) && !empty($_GET["update_list"])) {
    sale_mount_ago();
    wp_redirect("/wp-admin/admin.php?page=month_best_sell");
}

$sale_mount_ago = get_option('sale_mount_ago1');

$province_names = array(
    'tehran'    => 'تهران',
    'alborz'    => 'البرز',
    'esfehan'   => 'اصفهان',
    'others'    => 'دیگر استانها',
); ?>

<div class="wrap">
    <h1 style="margin: 0 0 10px;">پرفروش ترین های ماه اخیر</h1>
    <a href="<?= $_SERVER["REQUEST_URI"] ?>&update_list=true" class="page-title-action">تازه سازی</a>
    <a href="<?php echo home_url('?update_list_hottest') ?>" class="page-title-action">داغ ترین ها</a>
    <a href="<?php echo home_url('?update_comments_stars') ?>" class="page-title-action">کامنت ها</a>
    <a href="<?php echo home_url('?update_list_popular') ?>" class="page-title-action">محبوب ها</a>
    <a href="<?php echo home_url('?update_list_topsale') ?>" class="page-title-action">پرفروش ها</a>
    <a href="<?php echo home_url('?update_recent') ?>" class="page-title-action">جدیدها</a>
    <a href="<?php echo home_url('?ez_owner_wallet_held_24hrs') ?>" class="page-title-action">کیف پول</a>
    <a href="<?php echo home_url('?update_product_data') ?>" class="page-title-action">داده ها</a>
    <a href="<?php echo home_url('?update_marketing_data') ?>" class="page-title-action">مارکتینگ</a>
    <!--    <a href="--><?//= $_SERVER["REQUEST_URI"] ?><!--&update_product_schedule=true" class="page-title-action">تازه سازی "زمان سانسها"</a>-->
    <style>
        #admin-top-rooms-tables-wrapper {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: baseline;
        }
        #admin-top-rooms-tables-wrapper td {
            vertical-align: middle;
        }
        #admin-top-rooms-tables-wrapper td span {
            text-align: center !important;
            display: block;
        }
        #admin-top-rooms-tables-wrapper th {
            text-align: center !important;
        }
        a.page-title-action {
            margin: 10px;
            display: block;
            width: 100px;
            float: right;
            text-align: center;
        }
        .page-title-action:hover {
            transform: scale(1.05);
        }
    </style>

    <?php
    echo " اطلاعات از تاریخ " . $today['year'] . "-" . $today['mon'] . "-" . $today['mday'] . " تا " . $today['year'] . "-" . (int)$prevMonth . "-" . $today['mday']; ?>

    <div id="admin-top-rooms-tables-wrapper">

        <?php
        foreach ( $sale_mount_ago as $province => $data ) : ?>
            <table class="wp-list-table widefat fixed striped posts admin-top-rooms-tables">
                <thead>
                <tr>
                    <th>کد</th>
                    <th>نام</th>
                    <th>استان</th>
                    <th>تعداد</th>
                    <th>قیمت کل</th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($data as $item_key => $item) { ?>
                    <tr class="document_user">
                        <td><span><?= $item['id'] ?></span></td>
                        <td><span><?= $item['name'] ?></span></td>
                        <td><span><?= $item['cat_name'] ?></span></td>
                        <td><span><?= number_format($item['count']) ?></span></td>
                        <td><span><?= number_format($item['total']) ?></span></td>
                    </tr>
                    <?php
                } ?>
                </tbody>
            </table>
        <?php
        endforeach; ?>
    </div>

    <?php
    wp_reset_postdata(); ?>
</div>
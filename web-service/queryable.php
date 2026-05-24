<?php

// Allow CORS for all origins
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Authorization, Accept, Origin, DNT, X-CustomHeader, Keep-Alive, User-Agent, X-Requested-With, If-Modified-Since, Cache-Control, Content-Type, Content-Range, Range");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE, PATCH");
header("Access-Control-Max-Age: 1728000");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(204);
    exit;
}

error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);

global $conn;

if ( !($_SERVER['HTTP_HOST'] == 'escapezoom.ir' || $_SERVER['HTTP_HOST'] == 'escapezoom.co' || $_SERVER['HTTP_HOST'] == 'bak.escapezoom.ir' || $_SERVER['HTTP_HOST'] == 'dev-api.escapezoom.ir'|| $_SERVER['HTTP_HOST'] == 'zoom.escapezoom.ir'|| $_SERVER['HTTP_HOST'] == 'w2.razriazi.ir' || $_SERVER['HTTP_HOST'] == 'localhost') ) {
    $conn->query(sprintf("INSERT INTO hackers (host, referer) VALUES ('%s', '%s')", $_SERVER['HTTP_HOST'], $_SERVER['HTTP_REFERER']));
    die('Get outta here');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content_type = isset($_SERVER['CONTENT_TYPE']) ? trim($_SERVER['CONTENT_TYPE']) : '';

    if ( str_contains($content_type, 'application/json') )
        $data = json_decode( file_get_contents("php://input") );

    elseif ( str_contains($content_type, 'application/x-www-form-urlencoded') )
        $data = json_decode( json_encode( $_POST ) );

    else {
        http_response_code(415);
        echo json_encode(['error' => 'Unsupported Media Type']);
    }

} else {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid Request Method']);
}

session_start();
//$home_url = 'https://escapezoom.ir';

if ( isset( $data ) ) {
    require 'db-connect.php';

    $term = $data->term;

    if ( $_SERVER['HTTP_HOST'] == 'localhost' )
        $home_url = 'http://localhost/escapezoom_wp';
    else
        $home_url = 'https://' . ($data->url ?? 'escapezoom.ir');

    $term_parts = explode(' ', $term);
    if ( count( $term_parts ) == 2 && !empty( $term_parts[0] ) ) {

        $res1 = get_search_result_func( $term_parts[0] );
        $res2 = get_search_result_func( $term_parts[1] );

        $ids_arr1 = [];
        $products_temp = [];
        foreach ( $res1 as $res ) {
            $ids_arr1[] = $res['product_id'];
            $products_temp[$res['product_id']] = $res;
        }

        $ids_arr2 = [];
        foreach ( $res2 as $res ) {
            $ids_arr2[] = $res['product_id'];
            $products_temp[$res['product_id']] = $res;
        }

        if ( !empty( $term_parts[1] ) )
            foreach ( array_intersect($ids_arr1, $ids_arr2) as $product_id )
                $products[] = $products_temp[$product_id];
        else
            $products = $products_temp;

    } else
        $products = get_search_result_func( $term );

    if ( isset( $data->source ) ) {
        if ( $data->source == 'invitation' ) {
            foreach ( @array_slice((array)$products, 0, 30, true) as $product ) { // limit 20
                $api_data[] = [
                    'product_id'    => (int)$product['product_id'],
                    'title'         => $product['title'],
                    'image'         => $home_url . '/wp-content/uploads/' . unserialize($product['data'])['image'],
                    'city'          => unserialize($product['data'])['city'],
                ];
            }

        } elseif ( $data->source == 'collection' ) {
            $type = $data->type;

            $products = array_filter($products, function ($product) use ($type) { // فیلتر کردن فقط یک تایپ ( فقط اتاق فرار ها یا فقط سینماترس ها...)
                return $product['type'] === $type;
            });

            foreach ( @array_slice((array)$products, 0, 30, true) as $product )  // limit 20
                $api_data[] = [
                    'product_id'    => (int)$product['product_id'],
                    'title'         => $product['title'],
                    'image'         => $home_url . '/wp-content/uploads/' . unserialize($product['data'])['image'],
                ];

        } elseif ( $data->source == 'home_header_search' ) {
            foreach ( @array_slice((array)$products, 0, 30, true) as $product ) { // limit 20
                $api_data[] = [
                    'product_id'    => (int)$product['product_id'],
                    'title'         => $product['title'],
                    'image'         => $home_url . '/wp-content/uploads/' . unserialize($product['data'])['image'],
                    'city'          => unserialize($product['data'])['city'],
                    'url'           => $product['url'],
                ];
            }
        }

        echo json_encode($api_data);
        exit;
    }

    $result = '';
    foreach ( @array_slice((array)$products, 0, 30, true) as $product ) { // limit 20
        $data = unserialize($product['data']);

        if ( !empty( $data->url ) )
            $home_url   = 'https://' . $data->url;

        $name   = $product['title'];
        $city   = $data['city'];
        $image  = $home_url . '/wp-content/uploads/' . $data['image'];
        $url    = "$home_url/room/" . urlencode( $product['url'] ) . "/";

        $result .= '<p><img class=ax-search src="' . $image . '" > <a href="' . $url . '">    <span style="color: #202020 !important;font-weight: bold;" >' . $name . '</span></a> </p>';
    }
    echo json_encode($result);

} else die('Fuck off');
/************************************************************************************/
function get_search_result_func( $term ) {
    global $conn;

//    $sort_type = 'popular';
//
//    $result = $conn->query(sprintf("SELECT %s from products_order",  $sort_type));
//
//    if ($result->num_rows > 0)
//        $row = $result->fetch_assoc();
//
//    $products_id = unserialize( $row[$sort_type] );

    $result = $conn->query("SELECT * FROM products_data ");
    if ($result->num_rows > 0)
        while($row = $result->fetch_assoc())
            $products_data[$row['product_id']] = $row;

//    $sorted_product_list = [];
//    foreach ( $products_id as $product_id )
//        @$sorted_product_list[] = $products_data[$product_id];

    $sorted_product_list = $products_data;

    $products = [];
    foreach ( $sorted_product_list as $product ) {

        $temp = [];
        if ( @strpos($product['title'], $term ) !== false ) {

            @$temp['product_id']    = $product['product_id'];
            @$temp['type']          = $product['product_type'];
            @$temp['url']           = $product['url'];
            @$temp['title']         = $product['title'];
            @$temp['data']          = serialize( ['city' => $product['city_name'], 'image' => $product['image']] );

            $products[] = $temp;
        }
    }

    return $products;
}

function logintotag($thingtolog) {
    global $conn;
    $conn->query(sprintf("INSERT INTO tags (tag_id,tag_title,products) VALUES ('%s', '%s', '%s')", 1, 1, serialize($thingtolog)));
}
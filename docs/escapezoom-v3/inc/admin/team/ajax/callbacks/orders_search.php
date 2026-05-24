<?php
if ( ! function_exists( 'medoo' ) ) {
	require_once get_template_directory() . '/inc/medoo/init.php';
}

$medoo = medoo();

$term = $_POST['term'];
$term = trim($term);

if (strpos($term, ' ') !== false) {
    
    // چندکلمه‌ای (اسم و فامیل)
    $parts      = preg_split('/\s+/', $term, 2); 
    $firstname  = $parts[0];
    $lastname   = isset($parts[1]) ? $parts[1] : '';

    $data = $medoo->select("wp_markting", "order_id", [
        "OR"    => [
            // حالت اسم + فامیل
            "AND" => [
                "customer_firstname[~]" => $firstname,
                "customer_lastname[~]"  => $lastname,
            ],
            // حالت اسم بازی
            "game_name[~]" => $term
        ],
        "ORDER" => [
            "order_id" => "DESC"
        ],
        "LIMIT" => 50
    ]);
} else {
    
    $phone_term = $term;
    if (preg_match('/^0?\d{10}$/', $term)) 
        if (strpos($term, '0') === 0) 
            $phone_term = substr($term, 1); 
        else
            $phone_term = '0' . $term; 

    $data = $medoo->select("wp_markting", "order_id", [
        "OR"    => [
            "order_id[~]"           => $term,
            "customer_firstname[~]" => $term,
            "customer_lastname[~]"  => $term,
            "customer_phone[~]"     => [$term, $phone_term],
            "game_name[~]"          => $term
        ],
        "ORDER" => [
            "order_id" => "DESC"
        ],
        "LIMIT" => 50
    ]);
}

echo json_encode($data);
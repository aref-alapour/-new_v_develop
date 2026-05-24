<?php
require_once get_template_directory() . '/inc/medoo/init.php';
$medoo = medoo();

$term = $_POST['term'];

echo json_encode($term);
exit();

$term = trim($term);

if (strpos($term, ' ') !== false) {
    // چندکلمه‌ای
    $parts = preg_split('/\s+/', $term, 2); // فقط به دو قسمت تقسیم کن: اسم و فامیل
    $firstname = $parts[0];
    $lastname  = isset($parts[1]) ? $parts[1] : '';

    $data = $medoo->select("wp_markting", "order_id", [
        "OR" => [
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
    // وقتی یه کلمه است
    $data = $medoo->select("wp_markting", "order_id", [
        "OR" => [
            "order_id[~]"           => $term,
            "customer_firstname[~]" => $term,
            "customer_lastname[~]"  => $term,
            "customer_phone[~]"     => $term,
            "game_name[~]"          => $term
        ],
        "ORDER" => [
            "order_id" => "DESC"
        ],
        "LIMIT" => 50
    ]);
}


echo json_encode($data);
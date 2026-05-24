<?php
// فایل: main-search-ajax.php
header('Content-Type: application/json; charset=utf-8');
// 1. دریافت کلمه جستجو شده
$search_query = isset($_POST['term']) ? trim($_POST['term']) : (isset($_GET['term']) ? trim($_GET['term']) : '');

if (mb_strlen($search_query) < 2) {
    echo json_encode(['status' => 'empty', 'message' => 'لطفا حداقل 2 حرف وارد کنید.', 'data' => []]);
    exit;
}

// 2. تابع یکسان‌سازی حروف فارسی (عربی به فارسی، حذف فاصله‌های مجازی)
function normalize_persian($str) {
    if (empty($str)) return '';
    $str = str_replace(['ي', 'ك', '‌', 'آ'], ['ی', 'ک', ' ', 'ا'], $str);
    return mb_strtolower(trim($str), 'UTF-8');
}

$q = normalize_persian($search_query);
// برای جستجوی ترکیبی و چندکلمه‌ای (حذف فضاهای خالی اضافی)
$tokens = array_values(array_filter(explode(' ', $q))); 

// 3. خواندن فایل CSV
$csv_file = __DIR__ . '/wp_products_search.csv';
if (!file_exists($csv_file)) {
    echo json_encode(['status' => 'error', 'message' => 'فایل داده‌ها یافت نشد.']);
    exit;
}

$products = [];
$handle = fopen($csv_file, "r");
$headers = fgetcsv($handle);
while (($row = fgetcsv($handle)) !== FALSE) {
    $products[] = array_combine($headers, $row);
}
fclose($handle);

// 4. تابع محاسبه امتیاز برای رشته‌های کامل (Scoring)
function calc_score($field_value, $query, $base_weight) {
    if (empty($field_value)) return 0;
    $val = normalize_persian($field_value);
    
    if ($val === $query) return $base_weight * 1.5; 
    if (mb_strpos($val, $query) === 0) return $base_weight * 1.2; 
    if (mb_strpos($val, $query) !== false) return $base_weight * 1.0; 
    
    return 0;
}

$results = [];

// 5. پردازش رکوردها و اعمال اولویت‌ها
foreach ($products as $p) {
    
    // دیکد کردن مقادیر JSON
    $brand = json_decode($p['product_brand'], true) ?: [];
    $city  = json_decode($p['product_city'], true) ?: [];
    $tags  = json_decode($p['product_tags'], true) ?: [];
    
    $brand_name   = $brand['name'] ?? '';
    $brand_slug   = $brand['slug'] ?? '';
    $city_name    = $city['name'] ?? '';
    $city_slug    = $city['slug'] ?? '';
    $type_name    = $p['product_type'];
    $hood_name    = $p['product_hood'];
    $product_name = $p['product_name'];

    // --- قانون 3: بررسی ترکیبی تایپ و شهر (مثل لیزر تگ کرج) ---
    $is_compound_match = false;
    $city_matched = false;
    $type_matched = false;
    
    foreach ($tokens as $token) {
        if (mb_strlen($token) >= 2) {
            if (mb_strpos(normalize_persian($city_name), $token) !== false) $city_matched = true;
            if (mb_strpos(normalize_persian($type_name), $token) !== false) $type_matched = true;
        }
    }
    if ($city_matched && $type_matched && count($tokens) >= 2) {
        $is_compound_match = true;
    }

    // --- جستجوی چند کلمه‌ای هوشمند برای محصول (مثل "تهران مطهری") ---
    $multi_word_product_score = 0;
    $is_multi_word_match = false;
    
    if (count($tokens) > 1) {
        $matched_all_tokens = true;
        
        foreach ($tokens as $token) {
            if (mb_strlen($token) < 2) continue; // کلمات تک‌حرفی را نادیده بگیر
            
            $token_matched = false;
            $token_max_score = 0;
            
            // بررسی می‌کنیم این کلمه در کدام بخش از دیتای محصول است و بالاترین وزنش را می‌گیریم
            if (mb_strpos(normalize_persian($product_name), $token) !== false) { $token_max_score = max($token_max_score, 40); $token_matched = true; }
            if (mb_strpos(normalize_persian($city_name), $token) !== false) { $token_max_score = max($token_max_score, 30); $token_matched = true; }
            if (mb_strpos(normalize_persian($hood_name), $token) !== false) { $token_max_score = max($token_max_score, 20); $token_matched = true; }
            if (mb_strpos(normalize_persian($type_name), $token) !== false) { $token_max_score = max($token_max_score, 60); $token_matched = true; }
            if (mb_strpos(normalize_persian($brand_name), $token) !== false) { $token_max_score = max($token_max_score, 10); $token_matched = true; }
            
            foreach ($tags as $tag) {
                if (mb_strpos(normalize_persian($tag['title'] ?? ''), $token) !== false) {
                    $token_max_score = max($token_max_score, 70);
                    $token_matched = true;
                }
            }
            
            // اگر حتی یکی از کلماتی که کاربر نوشته در دیتای این محصول نبود، کلاً حسابش نکن
            if (!$token_matched) {
                $matched_all_tokens = false;
                break; 
            } else {
                $multi_word_product_score += $token_max_score; // جمع امتیاز کلمات
            }
        }
        
        if ($matched_all_tokens) {
            $is_multi_word_match = true;
        }
    }

    // اولویت 1: برچسب‌ها (وزن 70)
    foreach ($tags as $tag) {
        $tag_title = $tag['title'] ?? '';
        $score = calc_score($tag_title, $q, 70);
        if ($score > 0) {
            $results['tag_'.$tag['url']] = [
                'type'  => 'tag',
                'title' => 'بازی‌های ' . $tag_title,
                'url'   => $tag['url'],
                'score' => $score,
                'ui'    => 'link'
            ];
        }
    }

    // اولویت 2: تایپ بازی (وزن 60)
    $score = calc_score($type_name, $q, 60);
    if ($score > 0) {
        $results['type_'.$type_name] = [
            'type'  => 'game_type',
            'title' => $type_name,
            'url'   => '/city/' . urlencode($type_name),
            'score' => $score,
            'ui'    => 'link'
        ];
    }

    // اولویت 3: تایپ شهر ترکیبی (وزن 50)
    if ($is_compound_match) {
        $results['city_type_'.$city_name.'_'.$type_name] = [
            'type'  => 'city_type',
            'title' => $city_name,
            'url'   => '/city/' . $city_slug,
            'score' => 50 * 1.5,
            'ui'    => 'link'
        ];
    }

    // اولویت 4 و 6: کارت محصول (اسم پروداکت، محله یا جستجوی چند کلمه‌ای)
    $score_name = calc_score($product_name, $q, 40);
    $score_hood = calc_score($hood_name, $q, 20);
    
    // بالاترین امتیاز را بین اسم محصول، محله یا مچ شدن چندکلمه‌ای انتخاب می‌کنیم
    $final_product_score = max($score_name, $score_hood, $is_multi_word_match ? $multi_word_product_score : 0);
    
    // اعمال قانون ترکیبی دوم (اگر ترکیبی بود اما نام محصول مچ نبود، امتیاز پایه بگیرد)
    if ($is_compound_match && $final_product_score == 0) {
        $final_product_score = 40 * 1.2; 
    }
    
    if ($final_product_score > 0) {
        if (!isset($results['prod_'.$p['product_id']]) || $results['prod_'.$p['product_id']]['score'] < $final_product_score) {
            $results['prod_'.$p['product_id']] = [
                'type'  => 'product',
                'title' => $product_name,
                'image' => $p['product_image_url'],
                'url'   => $p['product_url'],
                'hood'  => $hood_name,
                'city'  => $city_name,
                'brand' => $brand_name,
                'product_type' => $type_name,
                'score' => $final_product_score,
                'ui'    => 'card'
            ];
        }
    }

    // اولویت 5: بازی‌های شهر (وزن 30)
    $score = calc_score($city_name, $q, 30);
    if ($score > 0) {
        $results['city_'.$city_name] = [
            'type'  => 'city',
            'title' => 'بازی‌های شهر ' . $city_name,
            'url'   => '/city/' . $city_slug,
            'score' => $score,
            'ui'    => 'link'
        ];
    }

    // اولویت 7: برند (وزن 10)
    $score = calc_score($brand_name, $q, 10);
    if ($score > 0) {
        $results['brand_'.$brand_slug] = [
            'type'  => 'brand',
            'title' => 'مجموعه: ' . $brand_name,
            'url'   => '/blog/product-brands/' . $brand_slug,
            'score' => $score,
            'ui'    => 'link'
        ];
    }
}

// 6. مرتب‌سازی نتایج بر اساس بالاترین امتیاز
$final_results = array_values($results);
usort($final_results, function($a, $b) {
    return $b['score'] <=> $a['score'];
});

// فقط 20 نتیجه برتر
$final_results = array_slice($final_results, 0, 20);

// 7. خروجی نهایی
if (empty($final_results)) {
    echo json_encode([
        'status' => 'success',
        'has_results' => false,
        'html' => '<div class="no-results-msg" style="padding:20px; text-align:center; color:#888;">هیچ نتیجه‌ای یافت نشد.</div>'
    ]);
} else {
    echo json_encode([
        'status' => 'success',
        'has_results' => true,
        'data' => $final_results
    ]);
}
?>

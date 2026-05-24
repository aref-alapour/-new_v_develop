<?php

/**
 * Main Search AJAX Handler - نسخه نهایی، پترن‌محور و هوشمند
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    if (!defined('ABSPATH')) {
        require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
    }

    $medoo_init_path = get_template_directory() . '/inc/medoo/init.php';
    require_once($medoo_init_path);
    $medoo = medoo();

    $search_term = isset($_POST['term']) ? sanitize_text_field($_POST['term']) : '';

} catch (Exception $e) {
    die(json_encode(['success' => false]));
}

if (empty($search_term)) {
    echo json_encode(['success' => false]);
    exit;
}

function normalizeText($text) {
    static $trans = ['‌' => ' ', '‒' => ' ', '–' => ' ', '—' => ' ', '־' => ' '];
    return trim(preg_replace('/\s+/', ' ', mb_strtolower(strtr(html_entity_decode($text ?? '', ENT_QUOTES, 'UTF-8'), $trans), 'UTF-8')));
}

function matchExact($needle, $haystack) {
    return normalizeText($needle) === normalizeText($haystack);
}

function matchScore($needle, $haystack) {
    $needle_norm = normalizeText($needle);
    $haystack_norm = normalizeText($haystack);
    
    if (empty($needle_norm) || empty($haystack_norm)) {
        return 0;
    }
    
    // Exact match
    if ($needle_norm === $haystack_norm) {
        return 100;
    }
    
    // Contains match
    if (strpos($haystack_norm, $needle_norm) !== false) {
        return min(95, (strlen($needle_norm) / strlen($haystack_norm)) * 100);
    }
    
    // Similarity using similar_text
    similar_text($needle_norm, $haystack_norm, $percent);
    
    return round($percent);
}

/* ==================== کش ثابت ==================== */

static $cache = null;
$cache = null; // برای تست موقتاً uncomment کن
if ($cache === null) {
    $products = $medoo->select('wp_products_search', '*');

    $cache = [
        'products' => $products,
        'types'    => [],
        'cities'   => [],
        'areas'    => [],
        'hoods'    => [],
        'name_map' => [] // نام دقیق بازی → لیست محصولات
    ];

    foreach ($products as $p) {
        $cache['types'][$p['product_type']] = true;

        if (!empty($p['product_city'])) {
            $c = json_decode($p['product_city'], true);
            if ($c && isset($c['name'])) {
                $norm = normalizeText($c['name']);
                $cache['cities'][$norm] = $c['name'];
            }
        }

        if (!empty($p['product_area'])) {
            $a = json_decode($p['product_area'], true);
            if ($a && isset($a['title'])) {
                $norm = normalizeText($a['title']);
                $cache['areas'][$norm] = $a;
            }
        }

        if (!empty($p['product_hood'])) {
            $norm = normalizeText($p['product_hood']);
            $cache['hoods'][$norm] = $p['product_hood'];
        }

        $name_norm = normalizeText($p['product_name']);
        if (!isset($cache['name_map'][$name_norm])) $cache['name_map'][$name_norm] = [];
        $cache['name_map'][$name_norm][] = $p;
    }

    $cache['types'] = array_keys($cache['types']);
}

/* ==================== اولویت اول: match دقیق نام بازی ==================== */

$term_norm = normalizeText($search_term);

if (isset($cache['name_map'][$term_norm])) {
    $matches = $cache['name_map'][$term_norm];
    if (count($matches) === 1) {
        $html = generateResultsHTML([], [$matches[0]]);
        echo json_encode(['success' => true, 'html' => $html, 'count' => 1], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

/* ==================== اولویت دوم: تشخیص نوع + مکان (مثل "سینما ترس اصفهان") ==================== */

$detected_type = null;
foreach ($cache['types'] as $type) {
    if (matchExact($type, $search_term) || strpos($term_norm, normalizeText($type)) === 0 || matchScore($search_term, $type) === 100) {
        $detected_type = $type;
        break;
    }
}

$detected_location = null;
$detected_location_type = null; // 'city' یا 'area'

$remaining_term = $detected_type ? trim(str_ireplace($detected_type, '', $search_term)) : $search_term;
$remaining_norm = normalizeText($remaining_term);

// اول چک city
foreach ($cache['cities'] as $norm => $name) {
    if ($norm === $remaining_norm || matchExact($remaining_term, $name)) {
        $detected_location = $name;
        $detected_location_type = 'city';
        break;
    }
}

// بعد چک area
if (!$detected_location) {
    foreach ($cache['areas'] as $norm => $data) {
        if ($norm === $remaining_norm || matchExact($remaining_term, $data['title'])) {
            $detected_location = $data['title'];
            $detected_location_type = 'area';
            break;
        }
    }
}

// اگر نوع + مکان داشتیم
if ($detected_type && $detected_location) {
    $main_category = [];
    if ($detected_location_type === 'city') {
        $main_cat_url = home_url("/type/{$detected_type}-{$detected_location}/");
    } else {
        $main_cat_url = home_url($cache['areas'][normalizeText($detected_location)]['url'] ?? '#');
    }

    $main_category[] = [
        'type'  => 'main_category',
        'title' => "{$detected_type} {$detected_location}",
        'url'   => $main_cat_url
    ];

    $filtered_products = [];
    foreach ($cache['products'] as $p) {
        if ($p['product_type'] !== $detected_type) continue;

        $match = false;
        if ($detected_location_type === 'city') {
            $c = json_decode($p['product_city'] ?? '', true);
            if (isset($c['name']) && matchExact($c['name'], $detected_location)) $match = true;
        } else {
            $a = json_decode($p['product_area'] ?? '', true);
            if (isset($a['title']) && normalizeText($a['title']) === normalizeText($detected_location)) $match = true;
        }

        if ($match) $filtered_products[] = $p;
    }

    usort($filtered_products, fn($a, $b) => matchScore($search_term, $b['product_name'] ?? '') <=> matchScore($search_term, $a['product_name'] ?? ''));

    $final_products = array_slice($filtered_products, 0, 10);

    $html = generateResultsHTML($main_category, $final_products);
    echo json_encode(['success' => true, 'html' => $html, 'count' => count($final_products) + 1], JSON_UNESCAPED_UNICODE);
    exit;
}

/* ==================== اولویت سوم: فقط شهر (مثل "اصفهان") ==================== */

$detected_city_only = null;
foreach ($cache['cities'] as $norm => $name) {
    if ($norm === $term_norm || matchExact($search_term, $name)) {
        $detected_city_only = $name;
        break;
    }
}

if ($detected_city_only) {
    $city = $detected_city_only;

    $html = '<ul class="space-y-6">';
    foreach ($cache['types'] as $type) {
        $cat_url = home_url("/type/{$type}-{$city}/");
        $html .= '<li><a href="' . esc_url($cat_url) . '" class="ez-search-result flex items-center justify-between"
                         data-search-type="category" data-search-value="' . esc_attr("{$type} {$city}") . '"
                         data-url="' . esc_url($cat_url) . '">
                    <span class="flex items-center gap-x-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 22 22" fill="none">
                            <circle cx="5.56627" cy="5.30357" r="3.92857" stroke="#90A1B9" stroke-width="2"/>
                            <circle cx="5.76158" cy="15.7143" r="3.92857" stroke="#90A1B9" stroke-width="2"/>
                            <circle cx="16.5663" cy="5.30357" r="3.92857" stroke="#90A1B9" stroke-width="2"/>
                            <circle cx="16.4999" cy="15.7143" r="3.92857" stroke="#90A1B9" stroke-width="2"/>
                        </svg>
                        <span class="font-bold text-[#62748E]">' . esc_html("{$type} {$city}") . '</span>
                    </span>
                    <span><svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 22 22" fill="none">
                        <path d="M7.12836 5.46042C6.80727 5.46062 ..." fill="#90A1B9"/>
                    </svg></span>
                  </a></li>';
    }

    // اضافه کردن بازی‌هایی که نام دقیقشون نام شهر است
    $extra_products = [];
    $city_norm = normalizeText($city);
    if (isset($cache['name_map'][$city_norm])) {
        $extra_products = $cache['name_map'][$city_norm];
    }

    if (!empty($extra_products)) {
        $html .= '<hr class="my-4 border-gray-100">';
        $html .= '<ul class="space-y-6">';
        foreach ($extra_products as $prod) {
            $city_data  = !empty($prod['product_city']) ? json_decode($prod['product_city'], true) : null;
            $brand_data = !empty($prod['product_brand']) ? json_decode($prod['product_brand'], true) : null;
            $product_url = home_url($prod['product_url']);

            $html .= '<li><a href="' . esc_url($product_url) . '" class="ez-search-result flex items-center justify-between"
                             data-search-type="product" data-search-value="' . esc_attr($prod['product_name']) . '"
                             data-url="' . esc_attr($product_url) . '">
                        <span class="flex items-center gap-x-4">';

            if (!empty($prod['product_image_url'])) {
                $html .= '<img src="' . esc_url($prod['product_image_url']) . '" alt="" class="w-7 h-8.5 rounded object-cover">';
            } elseif ($brand_data && !empty($brand_data['image'])) {
                $html .= '<img src="' . esc_url($brand_data['image']) . '" alt="" class="w-7 h-8.5 rounded object-cover">';
            } else {
                $html .= '<div class="w-7 h-8.5 rounded bg-gray-200"></div>';
            }

            $html .= '<span class="space-x-1 space-x-reverse">
                        <span class="font-bold text-sm text-[#62748E] inline-block">' . esc_html($prod['product_type']) . '</span>
                        <span class="text-[#09192D] font-bold inline-block">' . esc_html($prod['product_name']) . '</span>
                      </span>
                      </span>';

            if ($city_data || !empty($prod['product_hood'])) {
                $html .= '<span class="flex items-center gap-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
                              <path d="M6.99967 1.16675C4.43301 1.16675 ..." fill="#90A1B9"/>
                            </svg>
                            <span class="flex items-center gap-x-2 text-[#62748E] text-4xs">';
                if ($city_data && isset($city_data['name'])) $html .= '<span>' . esc_html($city_data['name']) . '</span>';
                if ($city_data && !empty($prod['product_hood'])) $html .= '<span><svg xmlns="http://www.w3.org/2000/svg" width="3" height="4" viewBox="0 0 3 4" fill="none"><circle cx="1.5" cy="2" r="1.5" fill="#90A1B9"/></svg></span>';
                if (!empty($prod['product_hood'])) $html .= '<span>' . esc_html($prod['product_hood']) . '</span>';
                $html .= '</span></span>';
            }

            $html .= '</a></li>';
        }
        $html .= '</ul>';
    }

    $html .= '</ul>';

    echo json_encode(['success' => true, 'html' => $html, 'count' => count($cache['types']) + count($extra_products)], JSON_UNESCAPED_UNICODE);
    exit;
}

/* ==================== اولویت چهارم: area کامل یا hood ==================== */

$detected_location = null;
$detected_location_type = null;

$term_full_norm = $term_norm;
foreach ($cache['areas'] as $norm => $data) {
    if ($norm === $term_full_norm || matchExact($search_term, $data['title'])) {
        $detected_location = $data['title'];
        $detected_location_type = 'area';
        break;
    }
}

if (!$detected_location) {
    $term_words = preg_split('/\s+/', $term_norm);
    for ($i = count($term_words) - 1; $i >= 0; $i--) {
        $norm_word = normalizeText($term_words[$i]);
        if (isset($cache['areas'][$norm_word])) {
            $detected_location = $cache['areas'][$norm_word]['title'];
            $detected_location_type = 'area';
            break;
        }
        if (isset($cache['hoods'][$norm_word]) && !isset($cache['cities'][$norm_word])) {
            $detected_location = $cache['hoods'][$norm_word];
            $detected_location_type = 'hood';
            break;
        }
    }
}

if ($detected_location) {
    $categories = [];
    $filtered_products = [];

    if ($detected_location_type === 'area') {
        $area_data = $cache['areas'][normalizeText($detected_location)];
        $categories[] = [
            'type'  => 'area',
            'title' => $area_data['title'],
            'url'   => home_url($area_data['url'] ?? '#')
        ];
    }

    foreach ($cache['products'] as $p) {
        $add = false;

        if ($detected_location_type === 'area') {
            $a = json_decode($p['product_area'] ?? '', true);
            if ($a && normalizeText($a['title'] ?? '') === normalizeText($detected_location)) {
                $add = true;
            }
        } else {
            if (normalizeText($p['product_hood'] ?? '') === normalizeText($detected_location)) {
                $add = true;
            }
        }

        if ($add) $filtered_products[] = $p;
    }

    usort($filtered_products, fn($a, $b) => matchScore($search_term, $b['product_name'] ?? '') <=> matchScore($search_term, $a['product_name'] ?? ''));

    $final_products = array_slice($filtered_products, 0, 10);

    $html = generateResultsHTML($categories, $final_products);
    echo json_encode(['success' => true, 'html' => $html, 'count' => count($final_products) + count($categories)], JSON_UNESCAPED_UNICODE);
    exit;
}

/* ==================== جستجوی عادی ==================== */

$scored_products = [];
foreach ($cache['products'] as $p) {
    $score = matchScore($search_term, $p['product_name'] ?? '') * 2.0;
    $score += matchScore($search_term, $p['product_hood'] ?? '') * 1.2;

    if ($score >= 50) $scored_products[] = ['product' => $p, 'score' => $score];
}

usort($scored_products, fn($a, $b) => $b['score'] <=> $a['score']);

$final_products = array_slice(array_column($scored_products, 'product'), 0, 10);

$html = generateResultsHTML([], $final_products);
echo json_encode(['success' => true, 'html' => $html, 'count' => count($final_products)], JSON_UNESCAPED_UNICODE);

exit;

/* ==================== تابع ساخت HTML ==================== */

function generateResultsHTML($categories, $products)
{
    $html = '';

    if (!empty($categories)) {
        $html .= '<ul class="space-y-6">';
        foreach ($categories as $cat) {
            $html .= '<li><a href="' . esc_url($cat['url']) . '" class="ez-search-result flex items-center justify-between"
                             data-search-type="' . esc_attr($cat['type'] ?? 'category') . '"
                             data-search-value="' . esc_attr($cat['title']) . '"
                             data-url="' . esc_url($cat['url']) . '">
                        <span class="flex items-center gap-x-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 22 22" fill="none">
                                <circle cx="5.56627" cy="5.30357" r="3.92857" stroke="#90A1B9" stroke-width="2"/>
                                <circle cx="5.76158" cy="15.7143" r="3.92857" stroke="#90A1B9" stroke-width="2"/>
                                <circle cx="16.5663" cy="5.30357" r="3.92857" stroke="#90A1B9" stroke-width="2"/>
                                <circle cx="16.4999" cy="15.7143" r="3.92857" stroke="#90A1B9" stroke-width="2"/>
                            </svg>
                            <span class="font-bold text-[#62748E]">' . esc_html($cat['title']) . '</span>
                        </span>
                        <span><svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 22 22" fill="none">
                            <path d="M7.12836 5.46042C6.80727 5.46062 ..." fill="#90A1B9"/>
                        </svg></span>
                      </a></li>';
        }
        $html .= '</ul>';

        if (!empty($products)) $html .= '<hr class="my-4 border-gray-100">';
    }

    if (!empty($products)) {
        $html .= '<ul class="space-y-6">';
        foreach ($products as $prod) {
            $city_data = !empty($prod['product_city']) ? json_decode($prod['product_city'], true) : null;
            $brand_data = !empty($prod['product_brand']) ? json_decode($prod['product_brand'], true) : null;
            $product_url = home_url($prod['product_url']);

            $html .= '<li><a href="' . esc_url($product_url) . '" class="ez-search-result flex items-center justify-between"
                             data-search-type="product" data-search-value="' . esc_attr($prod['product_name']) . '"
                             data-url="' . esc_attr($product_url) . '">
                        <span class="flex items-center gap-x-4">';

            if (!empty($prod['product_image_url'])) {
                $html .= '<img src="' . esc_url($prod['product_image_url']) . '" alt="" class="w-7 h-8.5 rounded object-cover">';
            } elseif ($brand_data && !empty($brand_data['image'])) {
                $html .= '<img src="' . esc_url($brand_data['image']) . '" alt="" class="w-7 h-8.5 rounded object-cover">';
            } else {
                $html .= '<div class="w-7 h-8.5 rounded bg-gray-200"></div>';
            }

            $html .= '<span class="space-x-1 space-x-reverse">
                        <span class="font-bold text-sm text-[#62748E] inline-block">' . esc_html($prod['product_type']) . '</span>
                        <span class="text-[#09192D] font-bold inline-block">' . esc_html($prod['product_name']) . '</span>
                      </span>
                      </span>';

            if ($city_data || !empty($prod['product_hood'])) {
                $html .= '<span class="flex items-center gap-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
                              <path d="M6.99967 1.16675C4.43301 1.16675 ..." fill="#90A1B9"/>
                            </svg>
                            <span class="flex items-center gap-x-2 text-[#62748E] text-4xs">';
                if ($city_data && isset($city_data['name'])) $html .= '<span>' . esc_html($city_data['name']) . '</span>';
                if ($city_data && !empty($prod['product_hood'])) $html .= '<span><svg xmlns="http://www.w3.org/2000/svg" width="3" height="4" viewBox="0 0 3 4" fill="none"><circle cx="1.5" cy="2" r="1.5" fill="#90A1B9"/></svg></span>';
                if (!empty($prod['product_hood'])) $html .= '<span>' . esc_html($prod['product_hood']) . '</span>';
                $html .= '</span></span>';
            }

            $html .= '</a></li>';
        }
        $html .= '</ul>';
    }

    if (empty($html)) {
        $html = '<p class="text-center text-gray-500 py-4">نتیجه‌ای یافت نشد</p>';
    }

    return $html;
}
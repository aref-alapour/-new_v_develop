<?php
if (!defined('ABSPATH')) {
	exit;
}

// Function to get all cities data
function get_all_cities()
{
    $cities = get_option('cities_ids_settings', []);
    if (!is_array($cities)) {
        return [];
    }
    return $cities;
}

// Function to get city data by slug
function get_city_by_slug($slug)
{
    if (empty($slug) || !is_string($slug)) {
        return null;
    }
    $cities = get_option('cities_ids_settings', []);
    if (!is_array($cities)) {
        return null;
    }
    foreach ($cities as $city) {
        if (isset($city['slug']) && $city['slug'] === $slug) {
            return $city;
        }
    }
    return null;
}

// Function to get city data by Persian name
function get_city_by_persian_name($persian_name)
{
    if (empty($persian_name)) {
        return null;
    }

    $cities = get_option('cities_ids_settings', []);
    if (!is_array($cities)) {
        return null;
    }

    foreach ($cities as $city) {
        if (isset($city['name']) && $city['name'] === $persian_name) {
            return [
                'name' => $city['name'],
                'slug' => $city['slug']
            ];
        }
    }
    return null;
}

// Function to get featured cities
function get_featured_cities()
{
    $cities = get_option('cities_ids_settings', []);
    if (!is_array($cities)) {
        return [];
    }
    $featured_cities = array_filter($cities, function ($city) {
        return isset($city['is_featured']) && $city['is_featured'];
    });
    return array_values($featured_cities);
}

// Function to get all cities with featured ones first
function get_cities_sorted_by_featured()
{
    $cities = get_option('cities_ids_settings', []);
    if (!is_array($cities)) {
        return [];
    }
    $featured = [];
    $non_featured = [];
    foreach ($cities as $city) {
        if (isset($city['is_featured']) && $city['is_featured']) {
            $featured[] = $city;
        } else {
            $non_featured[] = $city;
        }
    }
    return array_merge($featured, $non_featured);
}


function get_cities_with_city_id()
{
    $all_cities = get_option('cities_ids_settings', []);
    $cities_with_id = [];

    if (!empty($all_cities) && is_array($all_cities)) {
        foreach ($all_cities as $city) {
            // Only include cities that have city_id
            if (!empty($city['city_id'])) {
                $cities_with_id[] = [
                    'name' => $city['name'] ?? '',
                    'slug' => $city['slug'] ?? '',
                    'city_id' => $city['city_id'],
                    'is_featured' => isset($city['is_featured']) && $city['is_featured'] ? true : false
                ];
            }
        }
    }

    return $cities_with_id;
}


function get_cities_with_city_id_and_children()
{
    $all_cities = get_option('cities_ids_settings', []);
    $cities_with_id = [];

    if (!empty($all_cities) && is_array($all_cities)) {
        foreach ($all_cities as $city) {
            // Only include cities that have city_id
            if (!empty($city['city_id'])) {
                $cities_with_id[] = [
                    'name' => $city['name'] ?? '',
                    'slug' => $city['slug'] ?? '',
                    'city_id' => $city['city_id'],
                    'is_featured' => isset($city['is_featured']) && $city['is_featured'] ? true : false,
                    'children' => !empty($city['children']) && is_array($city['children']) ? $city['children'] : []
                ];
            }
        }
    }

    return $cities_with_id;
}

/**
 * Get city label (name) by slug, city_id, or name
 * این تابع با توجه به slug، city_id، یا نام شهر، نام (label) شهر را برمی‌گرداند
 *
 * @param string|int $city_identifier می‌تواند slug، city_id یا نام شهر باشد
 * @return string نام شهر یا رشته خالی در صورت عدم یافتن
 */
function get_city_label_by_identifier($city_identifier)
{
    // اگر مقدار خالی است، رشته خالی برگرداند
    if (empty($city_identifier)) {
        return '';
    }

    // اگر از قبل نام فارسی شهر است، همان را برگردان
    $city_by_name = get_city_by_persian_name($city_identifier);
    if ($city_by_name && isset($city_by_name['name'])) {
        return $city_by_name['name'];
    }

    // اگر slug است، با استفاده از تابع موجود get_city_by_slug نام را بگیر
    $city = get_city_by_slug($city_identifier);
    if ($city && isset($city['name'])) {
        return $city['name'];
    }

    // اگر city_id عددی است، در شهرها جستجو کن
    if (is_numeric($city_identifier)) {
        $all_cities = get_option('cities_ids_settings', []);
        if (!empty($all_cities) && is_array($all_cities)) {
            foreach ($all_cities as $city) {
                if (isset($city['city_id']) && $city['city_id'] == $city_identifier) {
                    return $city['name'] ?? '';
                }

                // بررسی در children
                if (!empty($city['children']) && is_array($city['children'])) {
                    foreach ($city['children'] as $child) {
                        if (isset($child['id']) && $child['id'] == $city_identifier) {
                            return $child['label'] ?? '';
                        }
                    }
                }
            }
        }
    }

    // اگر هیچ مطابقتی پیدا نشد، رشته خالی برگردان
    return '';
}

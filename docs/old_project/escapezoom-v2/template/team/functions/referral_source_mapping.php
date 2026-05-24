<?php

/**
 * Referral Source Mapping Functions
 * 
 * This file contains functions for mapping referral sources to readable labels
 * and handling grouped source filtering for the marketing report system.
 */

/**
 * Map referral source to readable label
 * @param string $source The referral source value
 * @return string The mapped label
 */
function map_referral_source_to_label($source)
{
    if (empty($source)) {
        return 'مستقیم';
    }

    switch ($source) {
        case 'google':
        case 'bing.com':
        case 'search.yahoo.com':
        case 'duckduckgo.com':
        case 'gerdoo.me':
        case 'presearch.com':
        case 'r.search.yahoo.com':
        case 'petalsearch.com':
        case 'zarebin.ir':
        case 'search.brave.com':
        case 'search.pawxy.com':
        case 'web.splus.ir':
        case 'search.app':
        case 'com.google.android.googlequicksearchbox':
            return 'سئو';
        case '(direct)':
            return 'دایرکت';
        case 'escapezoom.co':
            return 'ADs';
        case 'ir.medu.shad':
        case 'bpm.shaparak.ir':
        case 'sep.shaparak.ir':
        case 'sepehr.shaparak.ir':
        case 'asan.shaparak.ir':
        case 'ikc.shaparak.ir':
            return 'درگاه بانک';
        case 'instagram.com':
        case 'l.instagram.com':
        case 'org.telegram.messenger':
        case 'shaadbin.ir':
        case 'ir.eitaa.messenger':
        case 'org.telegram.plus':
        case 'org.telegram.messenger.web':
        case 'ir.ilmili.telegraph':
        case 'the.best.gram':
        case 'org.telegram.messenges':
        case 'com.xplus.messenger':
        case 'com.rahamessenger.pro':
        case 'instagram':
        case 'com.ita.plus.tel':
        case 'insta':
        case 'com.skygram.bestt':
        case 'web.telegram.org':
            return 'سوشال';
        default:
            return $source;
    }
}

/**
 * Get all referral sources grouped by their labels
 * @return array Array of grouped sources with label as key and array of source values as value
 */
function get_grouped_referral_sources()
{
    return [
        'seo' => [
            'google',
            'bing.com',
            'search.yahoo.com',
            'duckduckgo.com',
            'gerdoo.me',
            'presearch.com',
            'r.search.yahoo.com',
            'petalsearch.com',
            'zarebin.ir',
            'search.brave.com',
            'search.pawxy.com',
            'web.splus.ir',
            'search.app',
            'com.google.android.googlequicksearchbox'
        ],
        'direct' => ['(direct)'],
        'ads' => ['escapezoom.co'],
        'bank' => [
            'ir.medu.shad',
            'bpm.shaparak.ir',
            'sep.shaparak.ir',
            'sepehr.shaparak.ir',
            'asan.shaparak.ir',
            'ikc.shaparak.ir'
        ],
        'social' => [
            'instagram.com',
            'l.instagram.com',
            'org.telegram.messenger',
            'shaadbin.ir',
            'ir.eitaa.messenger',
            'org.telegram.plus',
            'org.telegram.messenger.web',
            'ir.ilmili.telegraph',
            'the.best.gram',
            'org.telegram.messenges',
            'com.xplus.messenger',
            'com.rahamessenger.pro',
            'instagram',
            'com.ita.plus.tel',
            'insta',
            'com.skygram.bestt',
            'web.telegram.org'
        ]
    ];
}

/**
 * Get source values for a given label (for filtering)
 * @param string $label The label to get source values for
 * @return array Array of source values
 */
function get_source_values_by_label($label)
{
    $grouped_sources = get_grouped_referral_sources();
    return $grouped_sources[$label] ?? [$label];
}

/**
 * Get all available source labels for dropdown/select options
 * @return array Array of source labels
 */
function get_available_source_labels()
{
    return array_keys(get_grouped_referral_sources());
}

/**
 * Check if a source belongs to a specific label group
 * @param string $source The source value to check
 * @param string $label The label group to check against
 * @return bool True if source belongs to the label group
 */
function is_source_in_label_group($source, $label)
{
    $source_values = get_source_values_by_label($label);
    return in_array($source, $source_values);
}

/**
 * Map order status to readable Persian label
 * @param string $status The order status value
 * @return string The mapped Persian label
 */
function map_order_status_to_label($status)
{
    if (empty($status)) {
        return 'نامشخص';
    }

    switch ($status) {
        case 'wc-walletx':
        case 'partially-paid':
        case 'wc-completed':
        case 'wc-partially-paid':
            return 'تکمیل شده';
        case 'wc-pending':
        case 'pending':
            return 'در انتظار';
        case 'wc-cancelled':
        case 'wc-admin-cancelled':
        case 'trash':
            return 'لغو شده';
        case 'wc-conflict':
            return 'مغایرت';
        case 'wc-refunded':
        case 'refunded':
            return 'برگشت خورده';
        default:
            return $status;
    }
}

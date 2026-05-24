<?php
/**
 * Product autocomplete (products_snapshot via gateway).
 *
 * @var string $label
 * @var string $input_id
 * @var string $product_id_name
 * @var string $product_id_id
 * @var string $results_id
 * @var string $placeholder
 * @var string $value
 * @var int $product_id
 */

if (! defined('ABSPATH')) {
    exit;
}

$label = $label ?? 'جستجوی نام بازی';
$input_id = $input_id ?? 'ez_penalty_product_search';
$product_id_name = $product_id_name ?? 'product_id';
$product_id_id = $product_id_id ?? 'ez_penalty_filter_product_id';
$results_id = $results_id ?? 'ez_penalty_filter_results';
$placeholder = $placeholder ?? 'نام بازی ...';
$value = $value ?? '';
$product_id = (int) ($product_id ?? 0);
?>
<div class="ez-penalty-field ez-penalty-field--search relative">
    <span class="ez-penalty-field-label"><?php echo esc_html($label); ?></span>
    <input type="hidden" name="<?php echo esc_attr($product_id_name); ?>" id="<?php echo esc_attr($product_id_id); ?>" value="<?php echo esc_attr((string) $product_id); ?>">
    <input type="search"
           id="<?php echo esc_attr($input_id); ?>"
           class="input input-bordered w-full bg-base-100 ez-penalty-product-search-input"
           autocomplete="off"
           placeholder="<?php echo esc_attr($placeholder); ?>"
           value="<?php echo esc_attr($value); ?>"
           data-results-id="<?php echo esc_attr($results_id); ?>"
           data-product-id-field="<?php echo esc_attr($product_id_id); ?>">
    <div id="<?php echo esc_attr($results_id); ?>"
                class="ez-penalty-search-results absolute top-full left-0 right-0 z-50 mt-1 hidden max-h-72 overflow-auto rounded-box border border-base-200 bg-base-100 shadow-lg"
                role="listbox"
                aria-live="polite"></div>
</div>

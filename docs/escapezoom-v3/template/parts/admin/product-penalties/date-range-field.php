<?php
/**
 * Persian calendar range trigger (shared with marketing_report calendar-module).
 *
 * @var string $label
 * @var string $from_id
 * @var string $until_id
 * @var string $display_id
 * @var string $from_name
 * @var string $until_name
 * @var string $from_value
 * @var string $until_value
 * @var string $placeholder
 */

if (! defined('ABSPATH')) {
    exit;
}

$label = $label ?? '';
$from_id = $from_id ?? '';
$until_id = $until_id ?? '';
$display_id = $display_id ?? $from_id . '_display';
$from_name = $from_name ?? $from_id;
$until_name = $until_name ?? $until_id;
$from_value = $from_value ?? '';
$until_value = $until_value ?? '';
$placeholder = $placeholder ?? 'انتخاب بازه زمانی';
?>
<div class="ez-penalty-field w-full">
    <?php if ($label !== '') : ?>
        <span class="ez-penalty-field-label"><?php echo esc_html($label); ?></span>
    <?php endif; ?>
    <input type="hidden" name="<?php echo esc_attr($from_name); ?>" id="<?php echo esc_attr($from_id); ?>" value="<?php echo esc_attr($from_value); ?>">
    <input type="hidden" name="<?php echo esc_attr($until_name); ?>" id="<?php echo esc_attr($until_id); ?>" value="<?php echo esc_attr($until_value); ?>">
    <button type="button"
            class="ez-penalty-date-trigger input input-bordered w-full min-h-10 h-auto py-2 flex items-center justify-between gap-2 text-right cursor-pointer bg-base-100"
            data-ez-penalty-date-trigger
            data-from-id="<?php echo esc_attr($from_id); ?>"
            data-until-id="<?php echo esc_attr($until_id); ?>"
            data-display-id="<?php echo esc_attr($display_id); ?>">
        <span id="<?php echo esc_attr($display_id); ?>" class="truncate flex-1 text-sm <?php echo ($from_value !== '' && $until_value !== '') ? 'text-base-content' : 'text-base-content/50'; ?>">
            <?php echo ($from_value !== '' && $until_value !== '') ? esc_html($from_value . ' — ' . $until_value) : esc_html($placeholder); ?>
        </span>
        <span class="shrink-0 text-primary" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                <path d="M1.5 9C1.5 6.17175 1.5 4.75725 2.379 3.879C3.258 3.00075 4.67175 3 7.5 3H10.5C13.3282 3 14.7427 3 15.621 3.879C16.4992 4.758 16.5 6.17175 16.5 9V10.5C16.5 13.3282 16.5 14.7427 15.621 15.621C14.742 16.4992 13.3282 16.5 10.5 16.5H7.5C4.67175 16.5 3.25725 16.5 2.379 15.621C1.50075 14.742 1.5 13.3282 1.5 10.5V9Z" stroke="currentColor" stroke-width="1.5"/>
                <path d="M5.25 3V1.875M12.75 3V1.875M1.875 6.75H16.125" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
        </span>
    </button>
</div>

<?php
/**
 * Penalty modal form body (DaisyUI + Persian calendar).
 *
 * @var \EscapeZoom\Core\Modules\ProductRanking\Models\ProductPenalty|null $row
 * @var int $product_id
 * @var string $product_title
 * @var string $product_image_url
 * @var string $active_from_value
 * @var string $active_until_value
 */

if (! defined('ABSPATH')) {
    exit;
}

use EscapeZoom\Core\Modules\ProductRanking\Admin\ProductPenaltyAdminPresenter;

$isEdit = $row !== null;
$searchLabel = $product_title !== ''
    ? $product_title . ' (#' . $product_id . ')'
    : '';
$active_from_value = $active_from_value ?? '';
$active_until_value = $active_until_value ?? '';
?>
<div class="ez-penalty-modal-form">
    <header class="ez-penalty-modal-header">
        <div>
            <h3 class="ez-penalty-modal-title"><?php echo $isEdit ? 'ویرایش پنالتی' : 'افزودن پنالتی'; ?></h3>
            <p class="ez-penalty-modal-subtitle">محصول را از لیست انتخاب کنید و بازه و سورت‌ها را تنظیم کنید.</p>
        </div>
        <button type="button" class="btn btn-ghost btn-sm btn-circle shrink-0" @click="closeFormModal()" aria-label="بستن">✕</button>
    </header>

    <form class="ez-penalty-form"
          hx-post="/ajax"
          hx-vals='{"action":"penalty.save"}'
          hx-swap="none"
          hx-on::after-request="window.ezPenaltyOnSaveResponse(event)">
        <?php if ($isEdit) : ?>
            <input type="hidden" name="penalty_id" value="<?php echo (int) $row->id; ?>">
        <?php endif; ?>

        <section class="ez-penalty-form-section">
            <?php
            ProductPenaltyAdminPresenter::partial('product-search-field', [
                'label' => 'محصول (نام بازی)',
                'input_id' => 'ez_penalty_modal_search',
                'product_id_name' => 'product_id',
                'product_id_id' => 'ez_penalty_modal_product_id',
                'results_id' => 'ez_penalty_modal_results',
                'placeholder' => 'نام بازی ...',
                'value' => $searchLabel,
                'product_id' => $product_id,
            ]);
            ?>
            <div id="ez_penalty_modal_selected_preview" class="ez-penalty-selected-preview<?php echo $product_image_url !== '' ? '' : ' hidden'; ?>">
                <?php if ($product_image_url !== '') : ?>
                    <img src="<?php echo esc_url($product_image_url); ?>" alt="" class="ez-penalty-selected-preview__img" loading="lazy">
                    <span class="ez-penalty-selected-preview__title"><?php echo esc_html($product_title); ?></span>
                <?php endif; ?>
            </div>
        </section>

        <section class="ez-penalty-form-section">
            <span class="ez-penalty-field-label">پنالتی از سورت‌ها</span>
            <div class="ez-penalty-facet-box">
                <label class="ez-penalty-facet-item">
                    <input type="checkbox" name="exclude_hottest" value="1" class="checkbox checkbox-sm checkbox-primary" <?php checked($isEdit && (bool) $row->exclude_hottest); ?>>
                    <span>داغ‌ترین</span>
                </label>
                <label class="ez-penalty-facet-item">
                    <input type="checkbox" name="exclude_popular" value="1" class="checkbox checkbox-sm checkbox-primary" <?php checked($isEdit && (bool) $row->exclude_popular); ?>>
                    <span>محبوب‌ترین</span>
                </label>
                <label class="ez-penalty-facet-item">
                    <input type="checkbox" name="exclude_topsale" value="1" class="checkbox checkbox-sm checkbox-primary" <?php checked($isEdit && (bool) $row->exclude_topsale); ?>>
                    <span>پرفروش‌ترین</span>
                </label>
            </div>
        </section>

        <section class="ez-penalty-form-section">
            <?php
            ProductPenaltyAdminPresenter::partial('date-range-field', [
                'label' => 'بازه اعمال پنالتی (از / تا)',
                'from_id' => 'modal_active_from',
                'until_id' => 'modal_active_until',
                'display_id' => 'modal_active_range_display',
                'from_name' => 'active_from',
                'until_name' => 'active_until',
                'from_value' => $active_from_value,
                'until_value' => $active_until_value,
                'placeholder' => 'انتخاب بازه پنالتی',
            ]);
            ?>
        </section>

        <section class="ez-penalty-form-section">
            <div class="ez-penalty-toggle-row">
                <input type="checkbox" name="is_enabled" value="1" id="ez_penalty_modal_is_enabled" class="toggle toggle-primary toggle-sm"
                    <?php checked(! $isEdit || (bool) ($row->is_enabled ?? true)); ?>>
                <label for="ez_penalty_modal_is_enabled" class="ez-penalty-toggle-label">فعال (خاموش = غیرفعال دستی)</label>
            </div>
        </section>

        <section class="ez-penalty-form-section">
            <label class="ez-penalty-field" for="ez_penalty_modal_note">
                <span class="ez-penalty-field-label">توضیحات</span>
                <textarea id="ez_penalty_modal_note" name="note" rows="3" class="textarea textarea-bordered w-full" placeholder="دلیل پنالتی..."><?php echo esc_textarea((string) ($row?->note ?? '')); ?></textarea>
            </label>
        </section>

        <footer class="ez-penalty-form-footer">
            <button type="button" class="btn btn-ghost" @click="closeFormModal()">انصراف</button>
            <button type="submit" class="btn btn-primary">ذخیره</button>
        </footer>
    </form>
</div>

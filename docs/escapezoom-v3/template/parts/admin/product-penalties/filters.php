<?php

if (! defined('ABSPATH')) {
    exit;
}

use EscapeZoom\Core\Modules\ProductRanking\Admin\ProductPenaltyAdminPresenter;
?>
<form id="ez-penalty-filters-form" class="mb-6 block w-full">

    <div class="ez-penalty-filters-panel w-full space-y-4 pb-2">
        <div class="flex flex-wrap items-center gap-3">
            <h2 class="text-base font-semibold text-base-content m-0">فیلترها</h2>
            <button type="button" class="btn btn-ghost btn-sm" @click="resetFilters()">پاک کردن فیلترها</button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-[1fr_1fr_1fr_auto] gap-4 w-full items-end">
            <?php
            ProductPenaltyAdminPresenter::partial('product-search-field', [
                'label' => 'جستجوی نام بازی',
                'input_id' => 'ez_penalty_filter_search',
                'product_id_name' => 'product_id',
                'product_id_id' => 'ez_penalty_filter_product_id',
                'results_id' => 'ez_penalty_filter_results',
                'placeholder' => 'نام بازی ...',
            ]);
            ?>

            <div class="form-control w-full">
                <span class="label-text font-medium mb-2">نوع پنالتی</span>
                <div class="flex flex-wrap gap-x-5 gap-y-2 px-3 py-3 items-center">
                    <label class="label cursor-pointer gap-2 p-0 justify-start">
                        <input type="checkbox" name="facet[]" value="hottest" class="checkbox checkbox-sm checkbox-primary">
                        <span class="label-text">داغ‌ترین</span>
                    </label>
                    <label class="label cursor-pointer gap-2 p-0 justify-start">
                        <input type="checkbox" name="facet[]" value="popular" class="checkbox checkbox-sm checkbox-primary">
                        <span class="label-text">محبوب‌ترین</span>
                    </label>
                    <label class="label cursor-pointer gap-2 p-0 justify-start">
                        <input type="checkbox" name="facet[]" value="topsale" class="checkbox checkbox-sm checkbox-primary">
                        <span class="label-text">پرفروش‌ترین</span>
                    </label>
                </div>
            </div>

            <div class="w-full">
                <?php
                ProductPenaltyAdminPresenter::partial('date-range-field', [
                    'label' => 'بازه پنالتی',
                    'from_id' => 'penalty_from',
                    'until_id' => 'penalty_until',
                    'display_id' => 'penalty_range_display',
                    'from_name' => 'penalty_from',
                    'until_name' => 'penalty_until',
                ]);
                ?>
            </div>

            <div class="w-full lg:w-auto">
                <button type="button"
                        id="ez-penalty-apply-filters"
                        class="btn btn-primary w-full lg:w-auto min-w-[8.5rem]"
                        hx-post="/ajax"
                        hx-trigger="click"
                        hx-target="#ez-penalty-table-host"
                        hx-swap="innerHTML"
                        hx-include="#ez-penalty-filters-form"
                        hx-vals='{"action":"penalty.list"}'
                        hx-indicator="#ez-penalty-skeleton"
                        @click="onApplyFilters()">
                    اعمال فیلتر
                </button>
            </div>
        </div>
    </div>

    <input type="hidden" name="paged" value="1">
</form>

<?php
/**
 * Product penalties admin shell (DaisyUI + Persian calendar).
 *
 * @var bool $table_ready
 * @var string $initial_table_html
 * @var string $add_form_html
 */

if (! defined('ABSPATH')) {
    exit;
}

use EscapeZoom\Core\Modules\ProductRanking\ProductPenaltySchema;

$table_ready = ProductPenaltySchema::tablesVerified();
$initial_table_html = $initial_table_html ?? '';
$add_form_html = $add_form_html ?? '';
?>
<div id="ez-penalty-admin"
     class="ez-penalty-admin max-w-full box-border mt-6 ms-5 me-5 mb-10"
     data-theme="light"
     dir="rtl"
     x-data="ezPenaltyAdminPage()"
     @penalty-saved.window="closeFormModal(); refreshList()"
     @penalty-deleted.window="refreshList()"
     style="background: none;">

    <h1 class="text-2xl font-bold text-base-content leading-tight m-0 p-0">پنالتی محصولات</h1>
    <p class="text-sm text-base-content/60 mt-2 mb-0">مدیریت حذف از سورت‌های داغ‌ترین، محبوب‌ترین و پرفروش‌ترین</p>

    <?php if ($table_ready) : ?>
        <button type="button" class="btn btn-primary my-4" @click="openCreate()">افزودن پنالتی</button>
    <?php endif; ?>

    <?php if (! $table_ready) : ?>
        <div class="alert alert-warning mt-4">
            <div>
                <p class="font-medium">جدول penalties یافت نشد.</p>
                <p class="text-sm">DDL را از <code class="text-xs">ez_bootstrap_custom_tables.sql</code> اجرا کنید.</p>
            </div>
        </div>
        <pre class="mt-3 rounded-lg border border-base-300 bg-base-100 p-3 text-xs overflow-auto" dir="ltr">ALTER TABLE <?php echo esc_html(ProductPenaltySchema::table()); ?>
  ADD COLUMN is_enabled TINYINT(1) NOT NULL DEFAULT 1 AFTER exclude_topsale,
  ADD COLUMN active_from DATETIME DEFAULT NULL AFTER topsale_quantity_divisor,
  ADD KEY idx_ez_penalty_active_from (active_from);</pre>
    <?php else : ?>

        <?php include __DIR__ . '/filters.php'; ?>

        <?php include get_template_directory() . '/template/calendar/calendar-layout.php'; ?>

        <section class="relative w-full">
            <?php include __DIR__ . '/skeleton.php'; ?>
            <div id="ez-penalty-table-host"
                 class="w-full"
                 hx-post="/ajax"
                 hx-trigger="penalty-list-refresh from:body"
                 hx-target="this"
                 hx-swap="innerHTML"
                 hx-include="#ez-penalty-filters-form"
                 hx-vals='{"action":"penalty.list"}'
                 hx-indicator="#ez-penalty-skeleton"><?php echo $initial_table_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
        </section>

        <dialog id="ez-penalty-form-modal" class="modal" :class="{ 'modal-open': formOpen }">
            <div class="modal-box max-w-2xl w-[calc(100%-2rem)] p-0 overflow-visible">
                <div id="ez-penalty-form-modal-body" class="p-6"><?php echo $add_form_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button type="button" @click="closeFormModal()">بستن</button>
            </form>
        </dialog>

        <template id="ez-penalty-form-add-template"><?php echo $add_form_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></template>

        <dialog id="ez-penalty-note-modal" class="modal" :class="{ 'modal-open': noteOpen }">
            <div class="modal-box max-w-lg w-[calc(100%-2rem)]">
                <h3 class="font-bold text-lg mb-2">توضیحات پنالتی</h3>
                <p class="py-3 text-sm whitespace-pre-wrap text-base-content/80" x-text="noteText"></p>
                <div class="modal-action">
                    <button type="button" class="btn" @click="noteOpen = false">بستن</button>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button type="button" @click="noteOpen = false">بستن</button>
            </form>
        </dialog>

    <?php endif; ?>
</div>

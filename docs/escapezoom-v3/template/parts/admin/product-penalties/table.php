<?php
/**
 * @var list<\EscapeZoom\Core\Modules\ProductRanking\Models\ProductPenalty> $rows
 * @var int $total
 * @var int $page
 * @var int $per_page
 * @var int $offset
 * @var \EscapeZoom\Core\Modules\ProductRanking\Admin\ProductPenaltyAdminFilters $filters
 * @var array<int, array{product_id: int, product_name: string, product_image_url: string, product_url: string, city_name: string}> $snapshots
 */

if (! defined('ABSPATH')) {
    exit;
}

use EscapeZoom\Core\Modules\ProductRanking\Admin\ProductPenaltyAdminPresenter;
use EscapeZoom\Core\Modules\ProductRanking\Repositories\ProductPenaltyRepository;

$total_pages = max(1, (int) ceil($total / max(1, $per_page)));
$snapshots = $snapshots ?? [];
$gridCols = 'grid-cols-[2.5rem_minmax(11rem,1.4fr)_5rem_3.5rem_3.5rem_3.5rem_6.5rem_6.5rem_5rem_3rem_6rem_6rem_6.5rem]';
?>
<div class="bg-base-100 border border-base-200 shadow-sm w-full overflow-hidden ez-penalty-table-wrap">
    <div class="overflow-x-auto w-full">
        <div class="ez-penalty-grid min-w-[72rem] w-full">
            <div class="ez-penalty-grid-head <?php echo esc_attr($gridCols); ?> text-xs uppercase tracking-wide text-base-content/70 bg-base-200/70 border-b border-base-200">
                <span>#</span>
                <span>نام بازی</span>
                <span>شهر</span>
                <span class="text-center">داغ</span>
                <span class="text-center">محبوب</span>
                <span class="text-center">فروش</span>
                <span>از تاریخ</span>
                <span>تا تاریخ</span>
                <span>وضعیت</span>
                <span class="text-center">توضیح</span>
                <span>ثبت</span>
                <span>آپدیت</span>
                <span class="text-center">عملیات</span>
            </div>

            <?php if ($rows === []) : ?>
                <div class="ez-penalty-grid-empty py-12 text-center text-base-content/50">
                    ردیفی یافت نشد.
                </div>
            <?php endif; ?>

            <?php foreach ($rows as $index => $row) :
                $productId = (int) $row->product_id;
                $snap = $snapshots[$productId] ?? null;
                $title = $snap['product_name'] ?? '';
                if ($title === '' && function_exists('get_the_title')) {
                    $title = (string) get_the_title($productId);
                }
                if ($title === '') {
                    $title = '(بدون عنوان)';
                }
                $productUrl = $snap['product_url'] ?? '';
                $imageUrl = $snap['product_image_url'] ?? '';
                $cityName = $snap['city_name'] ?? ProductPenaltyRepository::cityNameForProduct($productId);
                $status = ProductPenaltyRepository::statusMeta($row);
                $note = (string) ($row->note ?? '');
                ?>
                <div class="ez-penalty-grid-row <?php echo esc_attr($gridCols); ?> border-b border-base-200/80 hover:bg-base-200/30 text-sm">
                    <span class="text-base-content/70"><?php echo (int) ($offset + $index + 1); ?></span>
                    <span class="font-medium min-w-0">
                        <span class="flex items-center gap-2 min-w-0">
                            <?php if ($imageUrl !== '') : ?>
                                <img src="<?php echo esc_url($imageUrl); ?>" alt="" class="w-8 h-8 rounded object-cover shrink-0" loading="lazy">
                            <?php else : ?>
                                <span class="w-8 h-8 rounded bg-base-300 shrink-0" aria-hidden="true"></span>
                            <?php endif; ?>
                            <?php if ($productUrl !== '') : ?>
                                <a href="<?php echo esc_url($productUrl); ?>" class="link link-primary truncate" target="_blank" rel="noopener"><?php echo esc_html($title); ?></a>
                            <?php else : ?>
                                <span class="truncate"><?php echo esc_html($title); ?></span>
                            <?php endif; ?>
                        </span>
                    </span>
                    <span class="truncate"><?php echo esc_html($cityName); ?></span>
                    <span class="text-center"><?php echo ProductPenaltyAdminPresenter::penaltyFlag((bool) $row->exclude_hottest); ?></span>
                    <span class="text-center"><?php echo ProductPenaltyAdminPresenter::penaltyFlag((bool) $row->exclude_popular); ?></span>
                    <span class="text-center"><?php echo ProductPenaltyAdminPresenter::penaltyFlag((bool) $row->exclude_topsale); ?></span>
                    <span class="whitespace-nowrap text-xs"><?php echo esc_html(ProductPenaltyAdminPresenter::formatDateTime($row->active_from)); ?></span>
                    <span class="whitespace-nowrap text-xs"><?php echo esc_html(ProductPenaltyAdminPresenter::formatDateTime($row->active_until)); ?></span>
                    <span><?php echo ProductPenaltyAdminPresenter::statusBadge($status); ?></span>
                    <span class="text-center">
                        <?php if ($note !== '') : ?>
                            <button type="button" class="btn btn-ghost btn-xs btn-circle"
                                    @click="$dispatch('penalty-show-note', { note: <?php echo wp_json_encode($note); ?> })"
                                    title="مشاهده توضیحات">
                                <span class="dashicons dashicons-info text-info"></span>
                            </button>
                        <?php else : ?>
                            <span class="text-base-content/30">—</span>
                        <?php endif; ?>
                    </span>
                    <span class="whitespace-nowrap text-xs text-base-content/70"><?php echo esc_html(ProductPenaltyAdminPresenter::formatDateTime($row->created_at)); ?></span>
                    <span class="whitespace-nowrap text-xs text-base-content/70"><?php echo esc_html(ProductPenaltyAdminPresenter::formatDateTime($row->updated_at)); ?></span>
                    <span>
                        <div class="flex items-center justify-center gap-1">
                            <button type="button" class="btn btn-ghost btn-xs" @click="openEdit(<?php echo (int) $row->id; ?>)">ویرایش</button>
                            <button type="button" class="btn btn-ghost btn-xs text-error"
                                    hx-post="/ajax"
                                    hx-vals='{"action":"penalty.delete","id":"<?php echo (int) $row->id; ?>"}'
                                    hx-confirm="حذف این پنالتی؟"
                                    hx-swap="none">حذف</button>
                        </div>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 border-t border-base-200 bg-base-200/30">
        <span class="text-sm text-base-content/60"><?php echo (int) $total; ?> مورد</span>
        <?php if ($total_pages > 1) : ?>
            <div class="join">
                <?php for ($p = 1; $p <= $total_pages; $p++) :
                    if ($p > 1 && $p < $total_pages && abs($p - $page) > 2) {
                        if ($p === 2 || $p === $total_pages - 1) {
                            echo '<button type="button" class="join-item btn btn-sm btn-disabled" disabled>…</button>';
                        }
                        continue;
                    }
                    ?>
                    <button type="button"
                            class="join-item btn btn-sm <?php echo $p === $page ? 'btn-primary' : 'btn-ghost'; ?>"
                            hx-post="/ajax"
                            hx-target="#ez-penalty-table-host"
                            hx-swap="innerHTML"
                            hx-include="#ez-penalty-filters-form"
                            hx-vals='{"action":"penalty.list","paged":"<?php echo $p; ?>"}'
                            hx-indicator="#ez-penalty-skeleton"><?php echo (int) $p; ?></button>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
/**
 * Rating buttons for my-reviews edit modal (same contract as single-product review form).
 *
 * @var string $product_type_name
 * @var array<int,int> $preset_rates Keys 1094..1098, values 20..100 for data-rate.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$is_escape = ( $product_type_name === 'اتاق فرار' );
$pr        = static function ( array $preset_rates, int $key ): int {
	return (int) ( $preset_rates[ $key ] ?? $preset_rates[ (string) $key ] ?? 100 );
};
?>
<?php if ( $is_escape ) : ?>
	<div class="mb-6 max-lg:mb-4 my-reviews-rating-block">
		<div class="space-y-4 max-lg:space-y-3">
			<div class="flex items-center justify-between max-lg:gap-2">
				<label class="text-sm font-semibold min-w-d140 max-lg:text-xs max-lg:min-w-0">فضاسازی</label>
				<div class="flex gap-2 max-lg:gap-1.5">
					<?php for ( $j = 1; $j <= 5; $j++ ) : ?>
						<?php
						$dr    = $j * 20;
						$active = ( $dr === $pr( $preset_rates, 1094 ) );
						?>
						<button type="button"
							class="my-reviews-rate-btn w-10 h-10 max-lg:w-9 max-lg:h-9 rounded-lg border-2 border-gray-200 font-bold text-sm max-lg:text-xs transition-all hover:scale-105 max-lg:hover:scale-100 <?php echo $active ? 'active' : ''; ?>"
							data-rate="<?php echo (int) $dr; ?>"
							data-rating-item="1094"><?php echo (int) $j; ?></button>
					<?php endfor; ?>
				</div>
			</div>
			<div class="flex items-center justify-between max-lg:gap-2">
				<label class="text-sm font-semibold min-w-d140 max-lg:text-xs max-lg:min-w-0">تازگی و خلاقیت</label>
				<div class="flex gap-2 max-lg:gap-1.5">
					<?php for ( $j = 1; $j <= 5; $j++ ) : ?>
						<?php
						$dr     = $j * 20;
						$active = ( $dr === $pr( $preset_rates, 1098 ) );
						?>
						<button type="button"
							class="my-reviews-rate-btn w-10 h-10 max-lg:w-9 max-lg:h-9 rounded-lg border-2 border-gray-200 font-bold text-sm max-lg:text-xs transition-all hover:scale-105 max-lg:hover:scale-100 <?php echo $active ? 'active' : ''; ?>"
							data-rate="<?php echo (int) $dr; ?>"
							data-rating-item="1098"><?php echo (int) $j; ?></button>
					<?php endfor; ?>
				</div>
			</div>
			<div class="flex items-center justify-between max-lg:gap-2">
				<label class="text-sm font-semibold min-w-d140 max-lg:text-xs max-lg:min-w-0">کیفیت معما</label>
				<div class="flex gap-2 max-lg:gap-1.5">
					<?php for ( $j = 1; $j <= 5; $j++ ) : ?>
						<?php
						$dr     = $j * 20;
						$active = ( $dr === $pr( $preset_rates, 1095 ) );
						?>
						<button type="button"
							class="my-reviews-rate-btn w-10 h-10 max-lg:w-9 max-lg:h-9 rounded-lg border-2 border-gray-200 font-bold text-sm max-lg:text-xs transition-all hover:scale-105 max-lg:hover:scale-100 <?php echo $active ? 'active' : ''; ?>"
							data-rate="<?php echo (int) $dr; ?>"
							data-rating-item="1095"><?php echo (int) $j; ?></button>
					<?php endfor; ?>
				</div>
			</div>
			<div class="flex items-center justify-between max-lg:gap-2">
				<label class="text-sm font-semibold min-w-d140 max-lg:text-xs max-lg:min-w-0">بازیگردانی و اکت</label>
				<div class="flex gap-2 max-lg:gap-1.5">
					<?php for ( $j = 1; $j <= 5; $j++ ) : ?>
						<?php
						$dr     = $j * 20;
						$active = ( $dr === $pr( $preset_rates, 1096 ) );
						?>
						<button type="button"
							class="my-reviews-rate-btn w-10 h-10 max-lg:w-9 max-lg:h-9 rounded-lg border-2 border-gray-200 font-bold text-sm max-lg:text-xs transition-all hover:scale-105 max-lg:hover:scale-100 <?php echo $active ? 'active' : ''; ?>"
							data-rate="<?php echo (int) $dr; ?>"
							data-rating-item="1096"><?php echo (int) $j; ?></button>
					<?php endfor; ?>
				</div>
			</div>
			<div class="flex items-center justify-between max-lg:gap-2">
				<label class="text-sm font-semibold min-w-d140 max-lg:text-xs max-lg:min-w-0">برخورد پرسنل</label>
				<div class="flex gap-2 max-lg:gap-1.5">
					<?php for ( $j = 1; $j <= 5; $j++ ) : ?>
						<?php
						$dr     = $j * 20;
						$active = ( $dr === $pr( $preset_rates, 1097 ) );
						?>
						<button type="button"
							class="my-reviews-rate-btn w-10 h-10 max-lg:w-9 max-lg:h-9 rounded-lg border-2 border-gray-200 font-bold text-sm max-lg:text-xs transition-all hover:scale-105 max-lg:hover:scale-100 <?php echo $active ? 'active' : ''; ?>"
							data-rate="<?php echo (int) $dr; ?>"
							data-rating-item="1097"><?php echo (int) $j; ?></button>
					<?php endfor; ?>
				</div>
			</div>
		</div>
	</div>
<?php else : ?>
	<div class="mb-6 max-lg:mb-4 my-reviews-rating-block">
		<div class="flex items-center justify-between max-lg:items-center max-lg:justify-between">
			<div class="flex items-center justify-between gap-2 max-lg:mb-0 lg:mb-3">
				<h3 class="text-sm max-lg:text-xs font-semibold">امتیاز</h3>
			</div>
			<div class="relative max-lg:[&_.overflow-x-auto]:pb-0.75">
				<div class="overflow-x-auto transition-all duration-200 scrollbar-hide">
					<div class="flex gap-2">
						<?php for ( $j = 1; $j <= 5; $j++ ) : ?>
							<?php
							$dr     = $j * 20;
							$active = ( $dr === $pr( $preset_rates, 1098 ) );
							?>
							<button type="button"
								class="my-reviews-rate-btn flex-shrink-0 px-3 py-1 text-lg font-semibold text-center transition-all duration-150 bg-white border text-nowrap focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 whitespace-nowrap rounded-2xl text-slate-350 border-gray-50 h-9 min-w-9 md:px-5 max-lg:rounded-md max-lg:text-slate-900 max-lg:shadow-13 lg:h-10 lg:py-2 lg:leading-4 <?php echo $active ? 'active' : ''; ?>"
								data-rate="<?php echo (int) $dr; ?>"
								data-rating-item="1098"><?php echo (int) $j; ?></button>
						<?php endfor; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>

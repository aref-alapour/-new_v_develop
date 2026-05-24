<?php
/**
 * HTMX loading skeleton for brands directory grid.
 */
defined( 'ABSPATH' ) || exit;
?>
<div
	id="ez-brands-htmx-skeleton"
	class="htmx-indicator pointer-events-none absolute inset-0 z-20 flex min-h-[16rem] items-start justify-center rounded-2xl bg-white/75 opacity-0 transition-opacity duration-150 backdrop-blur-[1px] [&.htmx-request]:opacity-100"
	aria-hidden="true"
	role="presentation">
	<div class="w-full p-2">
		<div class="relative grid w-full grid-cols-2 gap-6 gap-x-8 gap-y-10 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 2xl:grid-cols-8">
			<?php for ( $i = 0; $i < 24; $i++ ) : ?>
				<div class="min-w-0 animate-pulse">
					<div class="aspect-square rounded-xl bg-slate-200/90"></div>
					<div class="mt-3 h-4 max-w-[85%] rounded-md bg-slate-200/80"></div>
					<div class="mt-2 h-3 max-w-[45%] rounded-md bg-slate-100"></div>
				</div>
			<?php endfor; ?>
		</div>
	</div>
</div>

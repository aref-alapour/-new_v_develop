<?php

$query  = new WP_Query( [
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => 5,
	'post__in'            => get_option( 'sticky_posts' ),
	'ignore_sticky_posts' => true,
	'orderby'             => 'rand',
] );
$sticky = [];
if ( $query->have_posts() ) {
	while ( $query->have_posts() ) {
		$query->the_post();

		$sticky[] = [
			'title'    => get_the_title(),
			'image'    => get_post_thumbnail_id(),
			'link'     => get_permalink(),
			'excerpt'  => get_the_excerpt(),
			'author'   => get_the_author(),
			'views'    => get_post_meta( get_the_ID(), 'views', true ) * 3,
			'category' => get_the_category()[0]->name,
		];
	}

	wp_reset_postdata();
}

$query = new WP_Query( [
	'post_type'      => 'post',
	'post_status'    => 'publish',
	'posts_per_page' => 3,
	'category__in'   => [ 953 ],
] );
$news  = [];
if ( $query->have_posts() ) {
	while ( $query->have_posts() ) {
		$query->the_post();

		$news[] = [
			'title'    => get_the_title(),
			'image'    => get_post_thumbnail_id(),
			'link'     => get_permalink(),
			'excerpt'  => get_the_excerpt(),
			'author'   => get_the_author(),
			'views'    => get_post_meta( get_the_ID(), 'views', true ) * 3,
			'category' => get_the_category()[0]->name,
		];
	}

	wp_reset_postdata();
}


get_header(); ?>

    <section class="my-12 max-lg:my-8">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center">
                <li class="group">
                    <div class="flex items-center">
                        <a class="text-sm font-medium text-slate-310 hover:text-primary-600" href="/">صفحه اصلی</a>
                    </div>
                </li>
                <li class="group">
                    <div class="flex items-center">
                        <div class="mx-5 h-2 w-px bg-slate-110"></div>
                        <a class="text-sm font-medium text-slate-310 cursor-text" href="<?php echo site_url( 'blog' ) ?>">
                            بلاگ بازی
                        </a>
                    </div>
                </li>
            </ol>
        </nav>
    </section>

    <h1 class="text-32 font-bold mb-10">بلاگ بازی</h1>

    <div class="mb-15 mt-12 w-full border-t border-slate-100 max-lg:hidden"></div>

<?php if ( count( $sticky ) > 0 && ! wp_is_mobile() ) { ?>

    <section class="hidden max-h-[26.5rem] w-full auto-cols-max grid-cols-24 grid-rows-12 gap-5 lg:grid">

		<?php if ( isset( $sticky[0] ) ) { ?>
            <div class="col-span-7 row-span-12">
                <a class="relative block h-full w-full overflow-hidden rounded-lg shadow-2 lg:rounded-3xl" href="<?php echo esc_html( $sticky[0]['link'] ); ?>">

					<?php echo wp_get_attachment_image( $sticky[0]['image'], 'full', false, [
						'class' => 'h-full w-full object-cover',
					] ) ?>

                    <div class="absolute right-0 top-0 flex h-full w-full flex-col bg-slate-900/60 p-6 text-white/90 justify-center">

						<?php if ( isset( $sticky[0]['category'] ) ) { ?>
                            <div class="ez-post-category">
                                <span class="rounded-md border border-white/30 bg-white/5 px-2 py-1 text-xs font-medium">
									<?php echo esc_html( $sticky[0]['category'] ); ?>
                                </span>
                            </div>
						<?php } ?>

                        <div class="flex h-full flex-col justify-center text-center">
                            <h2 class="text-22 font-extrabold text-white"><?php echo esc_html( $sticky[0]['title'] ) ?></h2>
                            <p class="ez-post-desc mx-auto mt-3.5 max-w-[500px] text-12 leading-6 lg:mt-5 line-clamp-2"><?php echo esc_html( $sticky[0]['excerpt'] ) ?></p>
                            <div class="ez-post-info mt-4 flex items-center justify-center gap-5 text-xs lg:mt-6">
                                <span><?php echo esc_html( $sticky[0]['author'] ) ?></span>
                                <span class="h-3.5 border-l border-white/40"></span>
                                <span>
                                    <span class="flex items-center gap-1 text-base">
										<?php echo esc_html( $sticky[0]['views'] ) ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="24" viewBox="0 0 24 24" class="mx-auto w-5">
                                            <path clip-rule="evenodd" d="M15.161 12.053a3.162 3.162 0 11-6.323-.001 3.162 3.162 0 016.323.001z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                            <path clip-rule="evenodd" d="M11.998 19.355c3.808 0 7.291-2.738 9.252-7.302-1.961-4.564-5.444-7.302-9.252-7.302h.004c-3.808 0-7.291 2.738-9.252 7.302 1.961 4.564 5.444 7.302 9.252 7.302h-.004z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </svg>
                                    </span>
                                </span>
                            </div>
                        </div>

                    </div>
                </a>
            </div>
		<?php } ?>

		<?php if ( isset( $sticky[1] ) ) { ?>
            <div class="col-span-12 row-span-6">
                <a class="relative block h-full w-full overflow-hidden rounded-lg shadow-2 lg:rounded-3xl" href="<?php echo esc_html( $sticky[1]['link'] ); ?>">

					<?php echo wp_get_attachment_image( $sticky[1]['image'], 'full', false, [
						'class' => 'h-full w-full object-cover',
					] ) ?>

                    <div class="absolute right-0 top-0 flex h-full w-full flex-col bg-slate-900/60 p-6 text-white/90 justify-center">

						<?php if ( isset( $sticky[1]['category'] ) ) { ?>
                            <div class="ez-post-category">
                                <span class="rounded-md border border-white/30 bg-white/5 px-2 py-1 text-xs font-medium">
									<?php echo esc_html( $sticky[1]['category'] ); ?>
                                </span>
                            </div>
						<?php } ?>

                        <div class="flex h-full flex-col justify-center text-center">
                            <h2 class="text-22 font-extrabold text-white"><?php echo esc_html( $sticky[1]['title'] ) ?></h2>
                            <p class="ez-post-desc mx-auto mt-3.5 max-w-[500px] text-12 leading-6 lg:mt-5 line-clamp-2"><?php echo esc_html( $sticky[1]['excerpt'] ) ?></p>
                            <div class="ez-post-info mt-4 flex items-center justify-center gap-5 text-xs lg:mt-6">
                                <span><?php echo esc_html( $sticky[1]['author'] ) ?></span>
                                <span class="h-3.5 border-l border-white/40"></span>
                                <span>
                                    <span class="flex items-center gap-1 text-base">
										<?php echo esc_html( $sticky[1]['views'] ) ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="24" viewBox="0 0 24 24" class="mx-auto w-5">
                                            <path clip-rule="evenodd" d="M15.161 12.053a3.162 3.162 0 11-6.323-.001 3.162 3.162 0 016.323.001z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                            <path clip-rule="evenodd" d="M11.998 19.355c3.808 0 7.291-2.738 9.252-7.302-1.961-4.564-5.444-7.302-9.252-7.302h.004c-3.808 0-7.291 2.738-9.252 7.302 1.961 4.564 5.444 7.302 9.252 7.302h-.004z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </svg>
                                    </span>
                                </span>
                            </div>
                        </div>

                    </div>
                </a>
            </div>
		<?php } ?>

		<?php if ( isset( $sticky[2] ) ) { ?>
            <div class="col-span-5 row-span-12">
                <a class="relative block h-full w-full overflow-hidden rounded-lg shadow-2 lg:rounded-3xl" href="<?php echo esc_html( $sticky[2]['link'] ); ?>">

					<?php echo wp_get_attachment_image( $sticky[2]['image'], 'full', false, [
						'class' => 'h-full w-full object-cover',
					] ) ?>

                    <div class="absolute right-0 top-0 flex h-full w-full flex-col bg-slate-900/60 p-6 text-white/90 justify-center">

						<?php if ( isset( $sticky[2]['category'] ) ) { ?>
                            <div class="ez-post-category">
                                <span class="rounded-md border border-white/30 bg-white/5 px-2 py-1 text-xs font-medium">
									<?php echo esc_html( $sticky[2]['category'] ); ?>
                                </span>
                            </div>
						<?php } ?>

                        <div class="flex h-full flex-col justify-center text-center">
                            <h2 class="text-22 font-extrabold text-white"><?php echo esc_html( $sticky[2]['title'] ) ?></h2>
                            <p class="ez-post-desc mx-auto mt-3.5 max-w-[500px] text-12 leading-6 lg:mt-5 line-clamp-2"><?php echo esc_html( $sticky[2]['excerpt'] ) ?></p>
                            <div class="ez-post-info mt-4 flex items-center justify-center gap-5 text-xs lg:mt-6">
                                <span><?php echo esc_html( $sticky[2]['author'] ) ?></span>
                                <span class="h-3.5 border-l border-white/40"></span>
                                <span>
                                    <span class="flex items-center gap-1 text-base">
										<?php echo esc_html( $sticky[2]['views'] ) ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="24" viewBox="0 0 24 24" class="mx-auto w-5">
                                            <path clip-rule="evenodd" d="M15.161 12.053a3.162 3.162 0 11-6.323-.001 3.162 3.162 0 016.323.001z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                            <path clip-rule="evenodd" d="M11.998 19.355c3.808 0 7.291-2.738 9.252-7.302-1.961-4.564-5.444-7.302-9.252-7.302h.004c-3.808 0-7.291 2.738-9.252 7.302 1.961 4.564 5.444 7.302 9.252 7.302h-.004z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </svg>
                                    </span>
                                </span>
                            </div>
                        </div>

                    </div>
                </a>
            </div>
		<?php } ?>

		<?php if ( isset( $sticky[3] ) ) { ?>
            <div class="col-span-6 row-span-6">
                <a class="relative block h-full w-full overflow-hidden rounded-lg shadow-2 lg:rounded-3xl" href="<?php echo esc_html( $sticky[3]['link'] ); ?>">

					<?php echo wp_get_attachment_image( $sticky[3]['image'], 'full', false, [
						'class' => 'h-full w-full object-cover',
					] ) ?>

                    <div class="absolute right-0 top-0 flex h-full w-full flex-col bg-slate-900/60 p-6 text-white/90 justify-center">

						<?php if ( isset( $sticky[3]['category'] ) ) { ?>
                            <div class="ez-post-category">
                                <span class="rounded-md border border-white/30 bg-white/5 px-2 py-1 text-xs font-medium">
									<?php echo esc_html( $sticky[3]['category'] ); ?>
                                </span>
                            </div>
						<?php } ?>

                        <div class="flex h-full flex-col justify-center text-right">
                            <h2 class="text-22 font-extrabold text-white"><?php echo esc_html( $sticky[3]['title'] ) ?></h2>
                            <div class="ez-post-info mt-4 flex items-center justify-start gap-5 text-xs lg:mt-6">
                                <span><?php echo esc_html( $sticky[3]['author'] ) ?></span>
                                <span class="h-3.5 border-l border-white/40"></span>
                                <span>
                                    <span class="flex items-center gap-1 text-base">
										<?php echo esc_html( $sticky[3]['views'] ) ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="24" viewBox="0 0 24 24" class="mx-auto w-5">
                                            <path clip-rule="evenodd" d="M15.161 12.053a3.162 3.162 0 11-6.323-.001 3.162 3.162 0 016.323.001z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                            <path clip-rule="evenodd" d="M11.998 19.355c3.808 0 7.291-2.738 9.252-7.302-1.961-4.564-5.444-7.302-9.252-7.302h.004c-3.808 0-7.291 2.738-9.252 7.302 1.961 4.564 5.444 7.302 9.252 7.302h-.004z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </svg>
                                    </span>
                                </span>
                            </div>
                        </div>

                    </div>
                </a>
            </div>
		<?php } ?>

		<?php if ( isset( $sticky[4] ) ) { ?>
            <div class="col-span-6 row-span-6">
                <a class="relative block h-full w-full overflow-hidden rounded-lg shadow-2 lg:rounded-3xl" href="<?php echo esc_html( $sticky[4]['link'] ); ?>">

					<?php echo wp_get_attachment_image( $sticky[4]['image'], 'full', false, [
						'class' => 'h-full w-full object-cover',
					] ) ?>

                    <div class="absolute right-0 top-0 flex h-full w-full flex-col bg-slate-900/60 p-6 text-white/90 justify-center">

						<?php if ( isset( $sticky[4]['category'] ) ) { ?>
                            <div class="ez-post-category">
                                <span class="rounded-md border border-white/30 bg-white/5 px-2 py-1 text-xs font-medium">
									<?php echo esc_html( $sticky[4]['category'] ); ?>
                                </span>
                            </div>
						<?php } ?>

                        <div class="flex h-full flex-col justify-center text-right">
                            <h2 class="text-22 font-extrabold text-white"><?php echo esc_html( $sticky[4]['title'] ) ?></h2>
                            <div class="ez-post-info mt-4 flex items-center justify-start gap-5 text-xs lg:mt-6">
                                <span><?php echo esc_html( $sticky[4]['author'] ) ?></span>
                                <span class="h-3.5 border-l border-white/40"></span>
                                <span>
                                    <span class="flex items-center gap-1 text-base">
										<?php echo esc_html( $sticky[4]['views'] ) ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="24" viewBox="0 0 24 24" class="mx-auto w-5">
                                            <path clip-rule="evenodd" d="M15.161 12.053a3.162 3.162 0 11-6.323-.001 3.162 3.162 0 016.323.001z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                            <path clip-rule="evenodd" d="M11.998 19.355c3.808 0 7.291-2.738 9.252-7.302-1.961-4.564-5.444-7.302-9.252-7.302h.004c-3.808 0-7.291 2.738-9.252 7.302 1.961 4.564 5.444 7.302 9.252 7.302h-.004z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </svg>
                                    </span>
                                </span>
                            </div>
                        </div>

                    </div>
                </a>
            </div>
		<?php } ?>

    </section>

<?php } ?>

<?php if ( count( $sticky ) > 0 && wp_is_mobile() ) { ?>
    <section class="lg:hidden">
        <div class="relative w-full max-sm:max-w-[calc(100%+2rem)] max-sm:w-[calc(100%+2rem)] max-sm:-mr-4" dir="rtl">
            <div class="h-full w-full overflow-hidden">
                <div class="swiper news-carousel px-4">
                    <div class="swiper-wrapper">
						<?php foreach ( $sticky as $post ) { ?>
                            <div class="swiper-slide">
                                <div class="relative min-w-0 shrink-0 grow-0 basis-72 lg:basis-120 pl-px">
                                    <a class="relative block h-full w-full overflow-hidden shadow-2 lg:rounded-3xl min-h-72 rounded-none" href="<?php echo esc_url( $post['link'] ) ?>">

										<?php echo wp_get_attachment_image( $post['image'], 'large', false, [
											'class' => 'h-full w-full object-cover absolute',
										] ) ?>

                                        <div class="absolute right-0 top-0 flex h-full w-full flex-col bg-slate-900/60 p-6 text-white/90 justify-center">

											<?php if ( isset( $post['category'] ) ) { ?>
                                                <div class="ez-post-category">
                                                    <span class="rounded-md border border-white/30 bg-white/5 px-2 py-1 text-xs font-medium">
														<?php echo esc_html( $post['category'] ); ?>
                                                    </span>
                                                </div>
											<?php } ?>

                                            <div class="flex h-full flex-col justify-center text-center">
                                                <h2 class="text-22 font-extrabold text-white">
													<?php echo esc_html( $post['title'] ); ?>
                                                </h2>
                                                <p class="ez-post-desc mx-auto mt-3.5 max-w-[500px] text-12 leading-6 lg:mt-5 line-clamp-2"><?php echo esc_html( $post['excerpt'] ) ?></p>
                                                <div class="ez-post-info mt-4 flex items-center justify-center gap-5 text-xs lg:mt-6">
                                                    <span><?php echo esc_html( $post['author'] ); ?></span>
                                                    <span class="h-3.5 border-l border-white/40"></span>
                                                    <span>
                                                        <span class="flex items-center gap-1 text-base">
															<?php echo esc_html( $post['views'] ); ?>
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="24" viewBox="0 0 24 24" class="mx-auto w-5">
                                                                <path clip-rule="evenodd" d="M15.161 12.053a3.162 3.162 0 11-6.323-.001 3.162 3.162 0 016.323.001z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                                <path clip-rule="evenodd" d="M11.998 19.355c3.808 0 7.291-2.738 9.252-7.302-1.961-4.564-5.444-7.302-9.252-7.302h.004c-3.808 0-7.291 2.738-9.252 7.302 1.961 4.564 5.444 7.302 9.252 7.302h-.004z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            </svg>
                                                        </span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
						<?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script>
        jQuery(document).ready(function () {
            new Swiper(".news-carousel", {
                slidesPerView: 1.5,
                freeMode: true
            })
        })
    </script>
<?php } ?>

<?php if ( count( $news ) > 0 ) { ?>
    <section class="overflow-y-clip [&~div_.ez-footer-logo>path:last-of-type]:fill-gray-50">
        <div class="mt-9 bg-gray-50 pb-8 pt-9 text-textColor shadow-[0_0px_0_1000px_#f2f6fa] lg:mt-14 lg:pb-20 lg:pt-12">
            <div class="mb-6 md:mb-8">
                <div class="items-center justify-between gap-6 flex">
                    <h2 class="flex items-center gap-4">
                        <span class="font-bold md:text-lg">
                            <span class="text-20">جدیدترین اخبار</span>
                        </span>
                    </h2>
                    <a href="<?php echo get_term_link( 953 ) ?>">
                        <div class="flex items-center gap-1.5 text-14 lg:gap-3.5 hover:text-primary-500 transition">
                            مشاهده همه
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="20" viewBox="0 0 24 24" class="mx-0">
                                <path clip-rule="evenodd" d="M16.335 2.75h-8.67c-3.02 0-4.914 2.14-4.914 5.166v8.168c0 3.027 1.884 5.166 4.915 5.166h8.668c3.03 0 4.917-2.139 4.917-5.166V7.916c0-3.027-1.886-5.166-4.916-5.166z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                <path d="M7.52 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395zM12 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395zM16.48 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395z" fill="currentColor"></path>
                            </svg>
                        </div>
                    </a>
                </div>
            </div>
            <div class="relative w-full max-sm:max-w-[calc(100%+2rem)] max-sm:w-[calc(100%+2rem)] max-sm:-mr-4" dir="rtl">
                <div class="h-full w-full overflow-hidden max-sm:px-4 grid grid-cols-1 lg:grid-cols-3 lg:-mx-10">
					<?php foreach ( $news as $post ) { ?>
                        <div class="lg:px-10 lg:border-l lg:last-of-type:border-l-0 max-lg:border-b max-lg:py-10 max-lg:last-of-type:border-b-0">
                            <a href="<?php echo $post['link']; ?>" class="flex gap-4">
								<?php echo wp_get_attachment_image( $post['image'], 'medium', false, [
									'class' => 'rounded-2xl w-[150px] object-cover object-center',
								] ) ?>
                                <div class="flex flex-col">
                                    <h3 class="text-16 mb-2 line-clamp-2 font-bold"><?php echo $post['title']; ?></h3>
                                    <p class="mb-4 line-clamp-2 text-12"><?php echo $post['excerpt']; ?></p>
                                    <span class="flex items-center gap-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none" class="mx-0">
                                            <path d="M16.158 8.28375C16.386 8.60325 16.5 8.76375 16.5 9C16.5 9.237 16.386 9.39675 16.158 9.71625C15.1335 11.1532 12.5168 14.25 9 14.25C5.4825 14.25 2.8665 11.1525 1.842 9.71625C1.614 9.39675 1.5 9.23625 1.5 9C1.5 8.763 1.614 8.60325 1.842 8.28375C2.8665 6.84675 5.48325 3.75 9 3.75C12.5175 3.75 15.1335 6.8475 16.158 8.28375Z" stroke="#09192D" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M11.25 9C11.25 8.40326 11.0129 7.83097 10.591 7.40901C10.169 6.98705 9.59674 6.75 9 6.75C8.40326 6.75 7.83097 6.98705 7.40901 7.40901C6.98705 7.83097 6.75 8.40326 6.75 9C6.75 9.59674 6.98705 10.169 7.40901 10.591C7.83097 11.0129 8.40326 11.25 9 11.25C9.59674 11.25 10.169 11.0129 10.591 10.591C11.0129 10.169 11.25 9.59674 11.25 9Z" stroke="#09192D" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
										<?php echo $post['views']; ?>
                                    </span>
                                </div>
                            </a>
                        </div>
					<?php } ?>
                </div>
            </div>
        </div>
    </section>
<?php } ?>

    <section>
        <div class="md:mb-8 mb-8 lg:mb-0 mt-20 [&amp;>div]:items-start">
            <div class="flex justify-between">
                <div class="items-center gap-6 md:flex">
                    <h2 class="flex items-center gap-4">
                        <span class="text-base mb-4 font-bold md:text-lg">
                            <span class="text-20">جدیدترین پست های منتشر شده</span>
                        </span>
                    </h2>
                </div>
            </div>
        </div>
        <div class="mb-11.5 w-full border-t border-slate-100 max-lg:hidden"></div>

        <div id="posts">
            <div class="grid auto-cols-max grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3 lg:gap-11 2xl:grid-cols-4">
				<?php for ( $i = 0; $i < 12; $i ++ ) { ?>
                    <div>
                        <div class="skeleton w-full rounded-xl" style="height: 300px"></div>
                    </div>
				<?php } ?>
            </div>
        </div>

    </section>

    <script>
        jQuery(document).ready(function ($) {

            const GetPosts = (page = 1) => {
                $.ajax({
                    type: 'POST',
                    url: "<?php echo admin_url( 'admin-ajax.php' ) ?>",
                    data: {
                        'action': 'v2_ajax_handler',
                        'nonce': "<?php echo wp_create_nonce( 'v2-ajax-nonce' ) ?>",
                        'callback': 'page_blog_get_posts',
                        'page': page
                    },
                    beforeSend: function () {
                        let out = '<div class="grid auto-cols-max grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3 lg:gap-11 2xl:grid-cols-4">'
                        for (let i = 0; i < 12; i++) {
                            out += '<div><div class="skeleton w-full rounded-xl" style="height: 300px"></div></div>'
                        }
                        out += '</div>'
                        $("#posts").html(out)
                    },
                    success: function (response) {
                        $("#posts").html(response)
                    },
                })
            }

            GetPosts()

            $("body").on('click', '.pagination a', function (e) {
                e.preventDefault()

                let page = $(this).attr('href').split('?page=')[1] ? $(this).attr('href').split('?page=')[1] : 1

                GetPosts(page)
            })

        })
    </script>

<?php get_footer();

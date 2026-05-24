<?php

$page = sanitize_text_field( $_POST['page'] );
$term = sanitize_text_field( $_POST['term'] ) ?: 0;

$query  = new WP_Query( [
	'post_type'      => 'post',
	'post_status'    => 'publish',
	'posts_per_page' => 3,
	'category__in'   => [ $term ],
] );
$sticky = [];
if ( $query->have_posts() ) {
	while ( $query->have_posts() ) {
		$query->the_post();

		$sticky[] = get_the_ID();
	}

	wp_reset_postdata();
}

$query = new WP_Query( [
	'post_type'      => 'post',
	'post_status'    => 'publish',
	'posts_per_page' => 12,
	'post__not_in'   => $sticky,
	'paged'          => $page,
	'category__in'   => [ (int) $term ],
] );

if ( $query->have_posts() ) { ?>
    <div class="grid auto-cols-max grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3 lg:gap-11 2xl:grid-cols-4">

		<?php while ( $query->have_posts() ) {
			$query->the_post(); ?>
            <div class="w-full max-sm:border-b max-sm:border-slate-100 max-sm:pb-8">
                <a href="<?php the_permalink(); ?>">
                    <div class="h-44 w-full overflow-hidden rounded-md lg:h-54 lg:rounded-xlh lg:shadow-23">
						<?php echo get_the_post_thumbnail( get_the_ID(), 'large', [
							'class' => 'h-full w-full object-cover',
						] ) ?>
                    </div>
                    <div class="flex items-start justify-start gap-3 mt-4">
                        <span class="bg-secondary-600 px-1.5 rounded leading-5 text-white"><?php echo get_the_category( get_the_ID() )[0]->name; ?></span>
                        <h2 class="text-lg"><?php the_title(); ?></h2>
                    </div>
                </a>
            </div>
		<?php }
		wp_reset_postdata(); ?>

    </div>
<?php }

if ( $query->max_num_pages > 1 ) { ?>
    <div class="mb-9 flex w-full items-center justify-center gap-4">
        <div class="flex gap-4 max-lg:gap-2 mt-16 justify-start max-lg:justify-center pagination">
			<?php echo paginate_links( [
				'mid_size'  => 1,
				'base'      => get_pagenum_link( 1 ) . '%_%',
				'format'    => '?page=%#%',
				'current'   => $page,
				'total'     => $query->max_num_pages,
				'prev_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none" class="rotate-180 opacity-25"><path d="M5.08008 11.1602L1.51062 7.14452C1.17384 6.76563 1.17384 6.19468 1.51062 5.81579L5.08008 1.80016" stroke="#0A184A" stroke-width="2" stroke-linecap="round"></path></svg>',
				'next_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none"><path d="M5.08008 11.1602L1.51062 7.14452C1.17384 6.76563 1.17384 6.19468 1.51062 5.81579L5.08008 1.80016" stroke="#0A184A" stroke-width="2" stroke-linecap="round"></path></svg>',
			] ); ?>
        </div>
    </div>
<?php }


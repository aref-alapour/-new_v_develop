<?php

$term = get_queried_object();

$query  = new WP_Query( [
	'post_type'      => 'post',
	'post_status'    => 'publish',
	'posts_per_page' => 3,
	'category__in'   => [ $term->term_id ],
] );
$sticky = [];
if ( $query->have_posts() ) {
	while ( $query->have_posts() ) {
		$query->the_post();

		$sticky[] = [
			'id'       => get_the_ID(),
			'title'    => get_the_title(),
			'image'    => get_post_thumbnail_id(),
			'link'     => get_permalink(),
			'excerpt'  => get_the_excerpt(),
			'author'   => get_the_author(),
			'views'    => get_post_meta( get_the_ID(), 'views', true ),
			'category' => get_the_category()[0]->name,
			'time'     => human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) . ' پیش',
			'comments' => get_comments_number( get_the_ID() ),
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
                        <a class="text-2xs font-medium text-slate-310 hover:text-primary-600" href="/">صفحه اصلی</a>
                    </div>
                </li>
                <li class="group">
                    <div class="flex items-center">
                        <div class="mx-5 h-2 w-px bg-slate-110"></div>
                        <div class="text-2xs font-medium text-slate-310 cursor-text">
							<?php echo $term->name; ?>
                        </div>
                    </div>
                </li>
            </ol>
        </nav>
    </section>

    <h1 class="text-32 font-black mb-10"><?php echo $term->name; ?></h1>

<?php if ( count( $sticky ) > 0 ) { ?>

    <section class="grid grid-cols-2 gap-4 container mx-auto px-4 sm:px-6 md:px-8 max-lg:hidden">

		<?php if ( isset( $sticky[0] ) ) { ?>
            <a href="<?php echo $sticky[0]['link']; ?>" class="flex flex-col gap-4 group">
                <div class="thumbnail rounded-2xl overflow-hidden relative">
					<?php echo wp_get_attachment_image( $sticky[0]['image'], 'large', false, [
						'class' => 'h-d464 object-cover',
					] ) ?>
                </div>
                <div class="flex items-center gap-3">
                    <span class="bg-secondary-600 text-white px-1.5 rounded leading-5">جدید</span>
                    <h2 class="text-2xl"><?php echo $sticky[0]['title']; ?></h2>
                </div>
            </a>
		<?php } ?>

        <div class="flex flex-col gap-4">
			<?php if ( isset( $sticky[1] ) ) { ?>
                <a href="<?php echo $sticky[1]['link']; ?>" class="flex flex-col gap-4 group">
                    <div class="thumbnail rounded-2xl overflow-hidden relative">
						<?php echo wp_get_attachment_image( $sticky[1]['image'], 'large', false, [
							'class' => 'h-d200 object-cover',
						] ) ?>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="bg-secondary-600 text-white px-1.5 rounded leading-5">جدید</span>
                        <h2 class="text-2xl grow"><?php echo $sticky[1]['title']; ?></h2>
                        <span class="flex leading-4 text-gray-400">
                            <span class="border-l-2 pl-2 ml-2 text-nowrap"><?php echo $sticky[1]['views']; ?>نمایش
                            </span>
                            <span class="text-nowrap"><?php echo $sticky[1]['time']; ?></span>
                        </span>
                    </div>
                </a>
			<?php }
			if ( isset( $sticky[2] ) ) { ?>
                <a href="<?php echo $sticky[2]['link']; ?>" class="flex flex-col gap-4 group">
                    <div class="thumbnail rounded-2xl overflow-hidden relative">
						<?php echo wp_get_attachment_image( $sticky[2]['image'], 'large', false, [
							'class' => 'h-d200 object-cover',
						] ) ?>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="bg-secondary-600 text-white px-1.5 rounded leading-5">جدید</span>
                        <h2 class="text-2xl grow truncate"><?php echo $sticky[2]['title']; ?></h2>
                        <span class="flex leading-4 text-gray-400">
                            <span class="border-l-2 pl-2 ml-2 text-nowrap"><?php echo $sticky[2]['views']; ?>نمایش
                            </span>
                            <span class="text-nowrap"><?php echo $sticky[2]['time']; ?></span>
                        </span>
                    </div>
                </a>
			<?php } ?>
        </div>
    </section>

<?php } ?>

<?php if ( count( $sticky ) > 0 ) { ?>

    <section class="bg-gray-50 p-4 flex flex-col gap-8 lg:hidden max-sm:max-w-bleed-2 max-sm:w-bleed-2 -mx-4">

		<?php if ( isset( $sticky[0] ) ) { ?>
            <div class="container mx-auto sm:px-6 md:px-8">
                <a href="<?php echo $sticky[0]['link']; ?>" class="flex flex-col gap-4 group">
                    <div class="thumbnail rounded-2xl overflow-hidden relative">
						<?php echo wp_get_attachment_image( $sticky[0]['image'], 'large', false, [
							'class' => 'h-d464 object-cover',
						] ) ?>
                        <div class="flex flex-col items-start justify-start gap-3 absolute bottom-5 px-5 text-white">
                            <span class="bg-secondary-600 px-1.5 rounded leading-5">جدید</span>
                            <h2 class="text-2xl"><?php echo $sticky[0]['title'] ?></h2>
                        </div>
                    </div>
                </a>
            </div>
		<?php } ?>

        <div class="grid grid-cols-2 gap-4 py-2">
			<?php foreach ( array_slice( $sticky, 1, 2 ) as $item ) { ?>
                <a href="<?php echo $item['link'] ?>" class="flex flex-col gap-4 group flex-shrink-0 w-full rounded-2xl overflow-hidden relative shadow-101">
                    <div class="thumbnail">
						<?php echo wp_get_attachment_image( $item['image'], 'large', false, [
							'class' => 'h-d244 object-cover',
						] ) ?>
                        <div class="flex flex-col items-center justify-start gap-3 absolute bottom-5 px-5 text-white w-full text-center">
                            <h2 class="text-md line-clamp-2"><?php echo $item['title']; ?></h2>
                            <div class="leading-3 flex text-white/80">
                                <span class="border-l pl-2 ml-2"><?php echo $item['comments']; ?> دیدگاه</span>
                                <span><?php echo $item['author']; ?></span>
                            </div>
                        </div>
                    </div>
                </a>
			<?php } ?>
        </div>

    </section>

<?php } ?>

    <section>
        <div class="md:mb-8 mb-8 mt-8 lg:mb-0 mt-20 [&amp;>div]:items-start">
            <div class="flex justify-between">
                <div class="items-center gap-6 md:flex">
                    <h2 class="flex items-center gap-4">
                        <span class="text-base mb-4 font-bold md:text-lg">
                            <span class="text-">جدیدترین <?php echo $term->name ?> منتشر شده</span>
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
                        'callback': 'category_get_posts',
                        'term': '<?php echo $term->term_id; ?>',
                        'page': page,
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

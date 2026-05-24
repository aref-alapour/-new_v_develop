<!-- Header -->
<?php get_header() ?>

<?php
$user_id = get_current_user_id();

$post_id = get_the_ID();
$post    = get_post( $post_id );
//saeed_print( $post );

if ( ! $post ) {
	wp_send_json_error( null, 404 );
}

/*------------------------------------------------*/
//Categories

$category_titles = [];
$category_ids    = [];
foreach ( get_the_category( $post_id ) as $category ) {
	$category_titles[] = $category->name;
	$category_ids[]    = $category->term_id;
}

/*------------------------------------------------*/
//Related Posts

$number_of_related_posts = 20;
$args                    = [
	'category__in'   => $category_ids,
	'post__not_in'   => [ $post_id ],
	'posts_per_page' => $number_of_related_posts,
];

$query = new WP_Query( $args );
if ( $query->have_posts() ) {
	while ( $query->have_posts() ) {
		$query->the_post();

		$related_post_id = get_the_ID();

		$related_posts[] = [
			'title'         => get_the_title(),
			'image'         => wp_get_attachment_url( get_post_thumbnail_id( $related_post_id ) ),
			'author'        => get_user_by( 'id', get_post_field( 'post_author', $related_post_id ) )->data->display_name,
			'content'       => get_post_field( 'post_excerpt', $related_post_id ),
			'comment_count' => (int) get_comments_number(),
			'url'           => '/blog/' . get_post_field( 'post_name', $related_post_id ),
			"category"      => "اخبار",
		];
	}
	wp_reset_postdata();
}

/*------------------------------------------------*/
//Comments

$comments_per_page = 10;

$all_comments_count = count( get_comments( [
	'post_id' => $post_id,
	'status'  => 'approve',
	'parent'  => 0,
	'order'   => 'DESC',
] ) );

$total_comments = $all_comments_count;

$comments_list = get_comments( [
	'post_id' => $post_id,
	'status'  => 'approve',
	'parent'  => 0,
	'orderby' => 'comment_date',
	'order'   => 'DESC',
	'number'  => $comments_per_page,
] );

$comments = [];

if ( ! empty( $comments_list ) ) {
	foreach ( $comments_list as $comment ) {
		$comment_id = $comment->comment_ID;

		$replies = get_post_reply_comments( $comment_id );

		$comments[] = [
			'comment_id'   => (int) $comment_id,
			'parent'       => $comment->comment_parent,
			'author_title' => $comment->comment_author,
			'author_image' => get_user_meta( $comment->user_id, 'user_avatar', true ) ?: 'http://escapezoom.ir/wp-content/uploads/2024/04/male_avatar_level_1.png',
			'author_level' => get_user_meta( $comment->user_id, 'level', true ) ?: 1,
			'content'      => $comment->comment_content,
			'date'         => strtotime( $comment->comment_date ),
			'replies'      => $replies,
		];
	}
}

$data = [
	'id'             => $post_id,
	'title'          => $post->post_title,
	'image'          => wp_get_attachment_url( get_post_thumbnail_id( $post_id ) ),
	'author'         => get_user_by( 'id', $post->post_author )->data->display_name,
	'rewriter'       => get_user_by( 'id', get_post_meta( $post_id, 'rewrite_author', true ) )->data->display_name,
	'date'           => strtotime( $post->post_date ),
	'rewriting_date' => strtotime( $post->post_modified ),
	'views'          => (int) get_post_meta( $post_id, 'views', true ) * 3,
	'category'       => $category_titles,
	'content'        => $post->post_content,
	'rating'         => [
		'rate'         => get_post_meta( $post_id, 'rmp_avg_rating', true ),
		'count'        => (int) get_post_meta( $post_id, 'rmp_vote_count', true ),
		'rated'        => get_user_meta( $user_id, 'post_rated', true ),
		'rating_items' => [
			[
				"title" => "عالی بود",
				"value" => 5,
			],
			[
				"title" => "متوسط بود",
				"value" => 3,
			],
			[
				"title" => "بد بود",
				"value" => 1,
			],
		],
		'short_url'    => "https://escapezoom.ir/$post_id",
		'sharing_urls' => [
			[
				"title" => "واتس اپ",
				"url"   => "whatsapp://send?text=" . urlencode( get_permalink( $post_id ) ),
			],
			[
				"title" => "تلگرام",
				"url"   => "https://telegram.me/share/url?url=" . urlencode( get_permalink( $post_id ) ),
			],
			[
				"title" => "ایتا",
				"url"   => "https://eitaa.com/share/url?url=" . urlencode( get_permalink( $post_id ) ),
			],
			[
				"title" => "ایمیل",
				"url"   => "mailto:?subject=" . get_bloginfo( 'name' ) . "&body=" . urlencode( get_permalink( $post_id ) ),
			],
		],
	],
	'banner1'        => [
		'image' => 'http://escapezoom.ir/wp-content/uploads/2024/10/Tehran-Nights-forever.jpg',
		'url'   => 'https://escapezoom.ir/maps',
	],
	'related'        => $related_posts,
//	'banner2'        => [
//		'image' => 'http://escapezoom.ir/wp-content/uploads/2024/10/Tehran-Nights-forever.jpg',
//		'url'   => 'https://escapezoom.ir/',
//	],
	'comments'       => [
		'tabs'        => [],
		'count'       => $total_comments,
		'items'       => $comments,
		'total_pages' => ceil( $total_comments / $comments_per_page ),
	],
	'breadcrumb'     => [
		[
			'title' => 'صفحه اصلی',
			'url'   => '/',
		],
		[
			'title' => 'بلاگ',
			'url'   => '/blog',
		],
		[
			'title' => $post->post_title,
			'url'   => trim_home_url( get_permalink( $post_id ) ),
		],
	],
];
?>
<section class="lg:mx-75 lg:mt-15 mt-16">
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="inline-flex items-center">
			<?php if ( $data['breadcrumb'] ) {
				foreach ( $data['breadcrumb'] as $index => $breadcrumb ) {
					echo '<li class="group border-r border-r-slate-105 px-2 first:border-r-0 first:pr-0"><div class="flex items-center"><a class="text-2xs font-medium text-slate-310 hover:text-primary-600" href="' . ( ( $index == 0 ) ? home_url() : home_url() . $breadcrumb["url"] ) . '">' . $breadcrumb["title"] . '</a></div></li>';
				}
			} ?>
        </ol>
    </nav>
    <h1 class="my-8.5 text-lg lg:my-10 lg:text-3xl lg:font-black"><?= $data['title'] ?></h1>
    <div class="w-full overflow-hidden rounded-18 lg:rounded-4xl">
        <img alt="<?= $data['title'] ?>" loading="lazy" width="770" height="300" decoding="async" data-nimg="1" class="h-full w-full object-cover" src="<?= $data['image'] ?>">
    </div>
    <div class="mb-12.5 mt-10 lg:mb-15">
        <div class="flex items-center justify-between max-lg:gap-3 max-lg:flex-wrap">
            <div class="lg:border-r lg:border-r-slate-105 lg:pr-5">
                <div class="mb-2">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg" class="max-lg:hidden">
                        <g clip-path="url(#clip0_4133_11363)">
                            <rect x="1.04688" y="1.05005" width="15.9" height="15.9" rx="4.75" fill="white"
                                  stroke="currentColor" stroke-width="1.5"></rect>
                            <path d="M11.5766 5.7963L12.202 6.41947C12.3277 6.54462 12.4274 6.69335 12.4954 6.85711C12.5634 7.02087 12.5984 7.19644 12.5984 7.37376C12.5984 7.55108 12.5634 7.72666 12.4954 7.89042C12.4274 8.05418 12.3277 8.2029 12.202 8.32805L8.92359 11.6537C8.66532 11.9107 8.33548 12.0838 7.9772 12.1504L5.94658 12.5895C5.87282 12.6058 5.79613 12.6034 5.72357 12.5823C5.65101 12.5613 5.5849 12.5224 5.53132 12.4691C5.47775 12.4159 5.43843 12.35 5.41698 12.2776C5.39553 12.2052 5.39264 12.1285 5.40858 12.0547L5.84062 10.0443C5.90852 9.68751 6.08236 9.35844 6.33953 9.10239L9.6591 5.7963C9.91363 5.54261 10.2584 5.40015 10.6178 5.40015C10.9773 5.40015 11.322 5.54261 11.5766 5.7963Z"
                                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                  stroke-linejoin="round"></path>
                        </g>
                        <defs>
                            <clipPath id="clip0_4133_11363">
                                <rect width="18" height="18" fill="white"></rect>
                            </clipPath>
                        </defs>
                    </svg>
                </div>
                <div class="space-x-10 space-x-reverse">
                    <div class="flex flex-col">
                        <a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>" class="mb-2.5 text-16 font-bold text-slate-700 max-lg:order-1"><?= $data['author'] ?></a>
                        <div class="text-14 font-bold leading-none text-text-3">نویسنده</div>
                    </div>
                </div>
            </div>
            <div class="lg:border-r lg:border-r-slate-105 lg:pr-5">
                <div class="mb-2">
                    <svg width="18" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" class="max-lg:hidden">
                        <rect x="2.25" y="2.25" width="15.9" height="15.9" rx="4.75" fill="white"
                              stroke="currentColor" stroke-width="1.5"></rect>
                        <path d="M7.55263 2.15385V1M12.4474 2.15385V1M3 6H18" stroke="currentColor"
                              stroke-width="1.5" stroke-linecap="round"></path>
                        <ellipse cx="7.0625" cy="10.1" rx="1.0625" ry="1.1" fill="currentColor"></ellipse>
                        <ellipse cx="7.0625" cy="13.4" rx="1.0625" ry="1.1" fill="currentColor"></ellipse>
                        <ellipse cx="10.25" cy="10.1" rx="1.0625" ry="1.1" fill="currentColor"></ellipse>
                        <ellipse cx="10.25" cy="13.4" rx="1.0625" ry="1.1" fill="currentColor"></ellipse>
                        <ellipse cx="13.4375" cy="10.1" rx="1.0625" ry="1.1" fill="currentColor"></ellipse>
                        <ellipse cx="13.4375" cy="13.4" rx="1.0625" ry="1.1" fill="currentColor"></ellipse>
                    </svg>
                </div>
                <div class="space-x-10 space-x-reverse flex">
                    <div class="flex flex-col">
                        <div class="mb-2.5 text-16 font-bold text-slate-700 max-lg:order-1"><?= parsidate( 'Y.m.d', $data['date'], $lang = 'per' ) ?></div>
                        <div class="text-14 font-bold leading-none text-text-3">تاریخ انتشار</div>
                    </div>

					<?php if ( ! wp_is_mobile() ) { ?>
                        <div class="flex flex-col">
                            <div class="mb-2.5 text-16 font-bold text-slate-700 max-lg:order-1"><?= parsidate( 'Y.m.d', $data['rewriting_date'], $lang = 'per' ) ?></div>
                            <div class="text-14 font-bold leading-none text-text-3">تاریخ بازنویسی</div>
                        </div>
					<?php } ?>
                </div>
            </div>
            <div class="lg:border-r lg:border-r-slate-105 lg:pr-5">
                <div class="mb-2">
                    <svg width="18" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" class="max-lg:hidden">
                        <rect x="2.25" y="2.25" width="15.9" height="15.9" rx="4.75" fill="white"
                              stroke="currentColor" stroke-width="1.5"></rect>
                        <path d="M6 14V11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                        <path d="M10 14L10 6" stroke="currentColor" stroke-width="1.5"
                              stroke-linecap="round"></path>
                        <path d="M14 14L14 9" stroke="currentColor" stroke-width="1.5"
                              stroke-linecap="round"></path>
                    </svg>
                </div>
                <div>
                    <div class="flex flex-col">
                        <div class="mb-2.5 text-16 font-bold text-slate-700 max-lg:order-1"><?= number_format( $data['views'] ) ?></div>
                        <div class="text-14 font-bold leading-none text-text-3">بازدید</div>
                    </div>
                </div>
            </div>
            <div class="lg:border-r lg:border-r-slate-105 lg:pr-5">
                <div class="mb-2">
                    <svg width="18" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" class="max-lg:hidden">
                        <g clip-path="url(#clip0_4133_11391)">
                            <path d="M15 13.3335H5" stroke="currentColor" stroke-width="1.5"
                                  stroke-linecap="round"></path>
                            <path d="M1.66797 5.79175C1.66797 5.05591 1.66797 4.68841 1.7263 4.38175C1.85066 3.72328 2.17058 3.11758 2.64435 2.64367C3.11811 2.16976 3.72371 1.84964 4.38214 1.72508C4.68964 1.66675 5.05797 1.66675 5.79297 1.66675C6.11464 1.66675 6.2763 1.66675 6.4313 1.68091C7.09884 1.74361 7.73196 2.00615 8.24797 2.43425C8.36797 2.53341 8.4813 2.64675 8.70964 2.87508L9.16797 3.33341C9.84797 4.01341 10.188 4.35341 10.5946 4.57925C10.8182 4.70383 11.0553 4.80225 11.3013 4.87258C11.7496 5.00008 12.2305 5.00008 13.1913 5.00008H13.503C15.6963 5.00008 16.7938 5.00008 17.5063 5.64175C17.5724 5.70008 17.6346 5.7623 17.693 5.82841C18.3346 6.54091 18.3346 7.63841 18.3346 9.83175V11.6667C18.3346 14.8092 18.3346 16.3809 17.358 17.3567C16.3813 18.3326 14.8105 18.3334 11.668 18.3334H8.33464C5.19214 18.3334 3.62047 18.3334 2.64464 17.3567C1.6688 16.3801 1.66797 14.8092 1.66797 11.6667V5.79175Z"
                                  stroke="currentColor" stroke-width="1.5"></path>
                        </g>
                        <defs>
                            <clipPath id="clip0_4133_11391">
                                <rect width="20" height="20" fill="white"></rect>
                            </clipPath>
                        </defs>
                    </svg>
                </div>
                <div>
                    <div class="flex flex-col">
                        <div class="mb-2.5 text-16 font-bold text-slate-700 max-lg:order-1">
							<?php
							$cat    = $data['category'];
							$result = implode( "، ", $cat );
							echo $result; ?>
                        </div>
                        <div class="text-14 font-bold leading-none text-text-3">دسته بندی</div>
                    </div>
                </div>
            </div>
        </div>

		<?php if ( wp_is_mobile() ) { ?>
            <div class="border-t border-t-slate-105 pt-4 flex gap-2 items-center justify-end">
                <div class="text-16 font-bold text-slate-700 max-lg:order-1"><?= parsidate( 'Y.m.d', $data['rewriting_date'], $lang = 'per' ) ?></div>
                <div class="text-14 font-bold leading-none text-text-3">تاریخ بازنویسی</div>
            </div>
		<?php } ?>

    </div>
</section>
<section class="mt-12.5 flex gap-x-10 lg:mt-15 blog-contents">
    <div class="w-d300 shrink-0 max-lg:hidden">
        <div class="sticky top-0 z-10 flex flex-col items-end gap-4 pt-3">
            <a href="#comments"
               class="group -ml-5 flex flex-nowrap items-center gap-2 border-l-2 border-transparent pl-5 text-xs text-slate-700 transition-all duration-300 hover:border-l-2 hover:border-primary-500 hover:text-primary-500">
                <span class="h-4 max-w-0 overflow-hidden transition-all duration-300 group-hover:max-w-d200">دیدگاه
                    ها
                </span>
                <svg width="18" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M10 19C11.78 19 13.5201 18.4722 15.0001 17.4832C16.4802 16.4943 17.6337 15.0887 18.3149 13.4442C18.9961 11.7996 19.1743 9.99002 18.8271 8.24419C18.4798 6.49836 17.6226 4.89472 16.364 3.63604C15.1053 2.37737 13.5016 1.5202 11.7558 1.17294C10.01 0.82567 8.20038 1.0039 6.55585 1.68509C4.91131 2.36628 3.50571 3.51983 2.51677 4.99987C1.52784 6.47991 1 8.21997 1 10C1 11.488 1.36 12.89 2 14.127L1 19L5.873 18C7.109 18.639 8.513 19 10 19Z"
                          stroke="currentColor" stroke-width="2" stroke-linecap="round"
                          stroke-linejoin="round"></path>
                </svg>
            </a>
            <a href="#share"
               class="group -ml-5 flex flex-nowrap items-center gap-2 border-l-2 border-transparent pl-5 text-xs text-slate-700 transition-all duration-300 hover:border-l-2 hover:border-primary-500 hover:text-primary-500">
                <span class="h-4 max-w-0 overflow-hidden transition-all duration-300 group-hover:max-w-d200">اشتراک
                    با دوستان
                </span>
                <svg width="18" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M13.1202 17.023L8.92121 14.733C8.373 15.319 7.66119 15.7266 6.87828 15.9028C6.09537 16.0791 5.27756 16.0157 4.53113 15.721C3.7847 15.4263 3.14417 14.9139 2.69277 14.2504C2.24138 13.5869 2 12.803 2 12.0005C2 11.198 2.24138 10.414 2.69277 9.75052C3.14417 9.08701 3.7847 8.57461 4.53113 8.27992C5.27756 7.98523 6.09537 7.92187 6.87828 8.09807C7.66119 8.27428 8.373 8.6819 8.92121 9.26796L13.1212 6.97796C12.8831 6.03393 12.9975 5.03546 13.4429 4.16973C13.8884 3.304 14.6342 2.63045 15.5408 2.27533C16.4473 1.92021 17.4522 1.90791 18.3671 2.24074C19.2821 2.57356 20.0442 3.22866 20.5107 4.08323C20.9772 4.93779 21.116 5.93316 20.9011 6.88274C20.6862 7.83232 20.1323 8.67092 19.3433 9.24133C18.5543 9.81175 17.5843 10.0748 16.6152 9.98123C15.6462 9.88764 14.7445 9.44382 14.0792 8.73296L9.87921 11.023C10.0407 11.6643 10.0407 12.3356 9.87921 12.977L14.0792 15.267C14.7448 14.5564 15.6467 14.1131 16.6158 14.02C17.5849 13.9269 18.5547 14.1904 19.3434 14.7612C20.1322 15.3319 20.6856 16.1708 20.9001 17.1204C21.1146 18.0701 20.9754 19.0654 20.5085 19.9197C20.0417 20.774 19.2793 21.4288 18.3642 21.7612C17.4491 22.0937 16.4442 22.0809 15.5379 21.7255C14.6315 21.37 13.8859 20.6961 13.4408 19.8303C12.9958 18.9644 12.8818 17.9659 13.1202 17.022M6.00021 14C6.53064 14 7.03935 13.7892 7.41442 13.4142C7.78949 13.0391 8.00021 12.5304 8.00021 12C8.00021 11.4695 7.78949 10.9608 7.41442 10.5857C7.03935 10.2107 6.53064 9.99996 6.00021 9.99996C5.46977 9.99996 4.96107 10.2107 4.58599 10.5857C4.21092 10.9608 4.00021 11.4695 4.00021 12C4.00021 12.5304 4.21092 13.0391 4.58599 13.4142C4.96107 13.7892 5.46977 14 6.00021 14ZM17.0002 7.99996C17.5306 7.99996 18.0393 7.78925 18.4144 7.41418C18.7895 7.0391 19.0002 6.53039 19.0002 5.99996C19.0002 5.46953 18.7895 4.96082 18.4144 4.58575C18.0393 4.21068 17.5306 3.99996 17.0002 3.99996C16.4698 3.99996 15.9611 4.21068 15.586 4.58575C15.2109 4.96082 15.0002 5.46953 15.0002 5.99996C15.0002 6.53039 15.2109 7.0391 15.586 7.41418C15.9611 7.78925 16.4698 7.99996 17.0002 7.99996ZM17.0002 20C17.5306 20 18.0393 19.7892 18.4144 19.4142C18.7895 19.0391 19.0002 18.5304 19.0002 18C19.0002 17.4695 18.7895 16.9608 18.4144 16.5857C18.0393 16.2107 17.5306 16 17.0002 16C16.4698 16 15.9611 16.2107 15.586 16.5857C15.2109 16.9608 15.0002 17.4695 15.0002 18C15.0002 18.5304 15.2109 19.0391 15.586 19.4142C15.9611 19.7892 16.4698 20 17.0002 20Z"
                          fill="currentColor"></path>
                </svg>
            </a>
        </div>
    </div>
    <article class="w-full overflow-hidden">
        <div class="post-content">
            <span class="text-primaryColor">
                مقدمه
            </span>
			<?php the_excerpt(); ?>

			<?php the_content(); ?>
        </div>
        <section class="hidden border border-slate-120 px-8 py-4 flex items-center justify-center rounded-4xl pb-10 pt-13 shadow-13 max-sm:-mx-4 max-sm:rounded-none max-sm:border-none max-sm:bg-slate-50 max-sm:shadow-none lg:pb-18 lg:pt-23 mt-10 lg:mt-15">
            <div>
                <div class="mb-6 flex items-end justify-between gap-5 lg:gap-25">
                    <span class="max-lg:hidden">چه امتیازی به این پست و محتوای آن میدهید :</span>
                    <span class="text-xs lg:hidden">
                        <span class="block text-lg">امتیاز شما</span>
                        به این پست و محتوا :
                    </span>
                    <div class="text-base max-lg:min-w-40" dir="ltr">
                        <span class="mr-2 inline-flex items-baseline text-yellow-300">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="20" viewBox="0 0 24 24">
                                <path d="M17.918 14.32a1.1 1.1 0 00-.319.97l.89 4.92a1.08 1.08 0 01-.45 1.08 1.1 1.1 0 01-1.17.08l-4.43-2.31a1.13 1.13 0 00-.5-.13h-.27a.812.812 0 00-.27.09l-4.43 2.32c-.22.11-.468.15-.71.11a1.112 1.112 0 01-.89-1.27l.89-4.92a1.119 1.119 0 00-.32-.98l-3.61-3.5a1.08 1.08 0 01-.27-1.13c.134-.396.476-.685.89-.75l4.97-.72c.377-.04.71-.27.88-.61l2.19-4.49c.051-.1.118-.192.2-.27l.09-.07a.671.671 0 01.16-.13l.11-.04.17-.07h.42c.376.04.707.264.88.6l2.22 4.47c.16.327.47.554.83.61l4.97.72c.42.06.77.35.91.75.13.401.017.841-.29 1.13l-3.74 3.54z"
                                      fill="currentColor"></path>
                            </svg>
                        </span>
                        <span class="mr-1 text-[3.375rem]"><?= $data['rating']['rate'] ?></span>
                        <span class="inline-flex items-baseline px-1.5 text-sm text-slate-120">/</span>
                        <span class="inline-flex items-baseline">5</span>
                        <div class="text-3xs">از رای کاربران
                            <span class="mr-1.5 text-sm"><?= $data['rating']['count'] ?></span>
                        </div>
                    </div>
                </div>
                <div class="grid w-full grid-cols-3 gap-4">
                    <button type="button"
                            class="flex gap-4 items-center justify-center relative text-sm font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 transition-all duration-300 ease-in-out disabled:bg-slate-110 disabled:text-disabled disabled:cursor-not-allowed disabled:shadow-none w-full bg-gray-20 text-gray-900 shadow-13 border border-gray-100 h-12 min-w-12 px-6 py-1 rounded-lg hover:border-yellow-350 hover:bg-yellow-300 hover:shadow-none focus-visible:bg-yellow-300 max-lg:px-2 max-lg:text-xs">
                        <span class="truncate">عالی بود</span>
                    </button>
                    <button type="button"
                            class="flex gap-4 items-center justify-center relative text-sm font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 transition-all duration-300 ease-in-out disabled:bg-slate-110 disabled:text-disabled disabled:cursor-not-allowed disabled:shadow-none w-full bg-gray-20 text-gray-900 shadow-13 border border-gray-100 h-12 min-w-12 px-6 py-1 rounded-lg hover:border-yellow-350 hover:bg-yellow-300 hover:shadow-none focus-visible:bg-yellow-300 max-lg:px-2 max-lg:text-xs">
                        <span class="truncate">متوسط بود</span>
                    </button>
                    <button type="button"
                            class="flex gap-4 items-center justify-center relative text-sm font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 transition-all duration-300 ease-in-out disabled:bg-slate-110 disabled:text-disabled disabled:cursor-not-allowed disabled:shadow-none w-full bg-gray-20 text-gray-900 shadow-13 border border-gray-100 h-12 min-w-12 px-6 py-1 rounded-lg hover:border-yellow-350 hover:bg-yellow-300 hover:shadow-none focus-visible:bg-yellow-300 max-lg:px-2 max-lg:text-xs">
                        <span class="truncate">بد بود</span>
                    </button>
                </div>
            </div>
        </section>
        <div class="mt-5 items-center justify-between text-2xs lg:flex" id="share">
			<?php if ( $data['rating']['sharing_urls'] ): ?>
                <div class="flex items-center gap-5 max-lg:justify-between">
                    <span class="text-slate-350 text-12">اشتراک این محتوا با دوستان</span>
                    <div class="flex items-center gap-2.5 lg:gap-4.5">
						<?php foreach ( $data['rating']['sharing_urls'] as $sharing_url ): ?>
                            <a target="_blank" rel="noreferrer" href="<?= $sharing_url['url'] ?>" class="text-14"><?= $sharing_url['title'] ?></a>
						<?php endforeach; ?>
                    </div>
                </div>
			<?php endif; ?>
            <div class="flex items-center gap-4 max-lg:mt-3 max-lg:justify-between max-lg:border-t max-lg:border-slate-100 max-lg:pt-3">
                <span class="text-xs underline text-12 decoration-slate-120 underline-offset-4 max-lg:order-2"><?= $data['rating']['short_url'] ?></span>
                <span class="max-lg:order-1 text-12">آدرس کوتاه اشتراک</span>
            </div>
        </div>
		<?php if ( $data['banner1'] ): ?>
            <div class="flex justify-between max-lg:items-end overflow-hidden bg-cover bg-center bg-no-repeat p-8 my-10 items-center gap-6 rounded-lg bg-[url('/assets/img/Image.svg')] max-md:flex-col lg:my-19 lg:rounded-3xl lg:p-10"
                 style="background-image:url(<?= $data['banner1']['image'] ?>)">
                <div class="max-w-47">
                    <h2 class="text-2xl font-extrabold text-white">نزدیکتـرین
                        <span class="text-accent-400">اتـاق فـرار
                        </span>
                        را بر روی نقشه پیدا کن
                    </h2>
                </div>
                <a class="flex gap-4 items-center justify-center relative text-sm font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 transition-all duration-300 ease-in-out disabled:bg-slate-110 disabled:text-disabled disabled:cursor-not-allowed disabled:shadow-none text-white shadow-17 hover:bg-slate-900 focus-visible:outline-slate-900 h-16 min-w-16 px-9 py-2 rounded-2xl bg-slate-700 bg-opacity-90"
                   href="<?= $data['banner1']['url'] ?>">
                    <span class="truncate">مشاهده نقشه</span>
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="18" viewBox="0 0 24 24" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="3" class="text-accent-500">
                            <path d="M4.25 12.274h15m-8.95 6.025-6.05-6.024L10.3 6.25" vector-effect="non-scaling-stroke"></path>
                        </svg>
                    </span>
                </a>
            </div>
		<?php endif; ?>

        <div class="hidden">
            <div class="mb-6 md:mb-8 lg:[&>div]:justify-start">
                <div class="flex justify-between">
                    <div class="items-center gap-6 md:flex">
                        <h3 class="flex items-center gap-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="19" viewBox="0 0 20 19" fill="none">
                                <path d="M15.4987 12.25V6.75C15.4987 4.15767 15.4987 2.86058 14.6929 2.05575C13.8881 1.25 12.591 1.25 9.9987 1.25H6.33203C3.7397 1.25 2.44261 1.25 1.63778 2.05575C0.832031 2.86058 0.832031 4.15767 0.832031 6.75V12.25C0.832031 14.8423 0.832031 16.1394 1.63778 16.9443C2.44261 17.75 3.7397 17.75 6.33203 17.75H17.332M4.4987 5.83333H11.832M4.4987 9.5H11.832M4.4987 13.1667H8.16536" stroke="#09192D" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M15.5 5.8335H16.4167C17.7128 5.8335 18.3609 5.8335 18.7633 6.23683C19.1667 6.63925 19.1667 7.28733 19.1667 8.5835V15.9168C19.1667 16.4031 18.9735 16.8694 18.6297 17.2132C18.2859 17.557 17.8196 17.7502 17.3333 17.7502C16.8471 17.7502 16.3808 17.557 16.037 17.2132C15.6932 16.8694 15.5 16.4031 15.5 15.9168V5.8335Z" stroke="#09192D" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="text-base font-bold md:text-lg">
                                <span class="text-md">پست های پیشنهادی</span>
                            </span>
                        </h3>
                        <div class="hidden md:block"></div>
                    </div>
                </div>
            </div>
			<?php if ( $data['related'] ): ?>
                <div class="swiper relatedPostSwiper">
                    <div class="swiper-wrapper pb-12.5">
						<?php foreach ( $data['related'] as $item ): ?>
                            <div class="swiper-slide relative w-d264 h-d300">
                                <a class="relative block w-full overflow-hidden rounded-3xl h-82 max-w-77 shadow-8"
                                   href="<?= home_url() . $item['url'] ?>">
                                    <img alt="Product" loading="lazy" width="281" height="328" decoding="async"
                                         data-nimg="1" class="h-full w-full object-cover" style="color:transparent"
                                         src="<?= $item['image'] ?>">
                                    <div class="absolute right-0 top-0 flex h-full w-full flex-col justify-between bg-slate-900/60 p-6 text-white/90">
                                        <div class="ez-post-category">
                                            <span class="rounded-md border border-white/30 bg-white/5 px-2 py-1 text-xs font-medium"><?= $item['category'] ?></span>
                                        </div>
                                        <div class="text-center">
                                            <h3 class="text-lg font-extrabold text-white lg:text-xl line-clamp-2"><?= $item['title'] ?></h3>
                                            <p class="ez-post-desc mx-auto mt-3.5 max-w-d500 text-2xs leading-6 lg:mt-5 line-clamp-2" style="background-image: linear-gradient(#FFFFFF, rgba(255,255,255,.6));    color: transparent;background-clip: text;"><?= $item['content'] ?></p>
                                            <div class="ez-post-info mt-4 flex items-center justify-center gap-5 text-xs lg:mt-6">
                                                <span>
                                                    <span><?= ( $item['comment_count'] && $item['comment_count'] > 0 ) ?: 'بدون' ?></span>
                                                    <span> دیدگاه</span>
                                                </span>
                                                <span class="h-3.5 border-l border-white/40"></span>
                                                <span><?= $item['author'] ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
						<?php endforeach; ?>
                    </div>
                    <button class="products-carousel-prev trends-rooms-btn absolute right-0 max-lg:hidden top-1/2 -translate-y-1/2 rotate-180 z-50 cursor-pointer touch-manipulation appearance-none -mr-0.5"
                            type="button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" fill="none" viewBox="0 0 30 113">
                            <g clip-path="url(#arrow_aa)">
                                <path fill="#BFCBD9" fill-rule="evenodd" d="M0 3.75c0 28.814 30 32.928 30 52.823 0 21.023-30 26.414-30 56.595V3.75Z" clip-rule="evenodd"></path>
                                <path fill="#fff" fill-rule="evenodd" d="M0 1c0 28.814 27 33.679 27 53.573 0 21.022-27 23.914-27 54.094V1Z" clip-rule="evenodd"></path>
                                <path fill="#9FB3CB" fill-rule="evenodd" d="m13.815 50.977.125.142c.387.51.334 1.232-.124 1.677l-3.098 3.037 3.098 3.037.125.141a1.273 1.273 0 0 1-.128 1.68 1.286 1.286 0 0 1-1.804-.003l-4.025-3.946-.126-.142a1.276 1.276 0 0 1 .126-1.676l4.025-3.946.147-.124a1.29 1.29 0 0 1 1.659.123Z" clip-rule="evenodd"></path>
                            </g>
                            <defs>
                                <clipPath id="arrow_aa">
                                    <path fill="#fff" d="M0 0h30v113H0z"></path>
                                </clipPath>
                            </defs>
                        </svg>
                    </button>
                    <button class="products-carousel-next trends-rooms--btn absolute left-0 max-lg:hidden top-1/2 -translate-y-1/2 z-50 cursor-pointer touch-manipulation appearance-none -ml-0.5"
                            type="button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" fill="none" viewBox="0 0 30 113">
                            <g clip-path="url(#arrow_aa)">
                                <path fill="#BFCBD9" fill-rule="evenodd" d="M0 3.75c0 28.814 30 32.928 30 52.823 0 21.023-30 26.414-30 56.595V3.75Z" clip-rule="evenodd"></path>
                                <path fill="#fff" fill-rule="evenodd" d="M0 1c0 28.814 27 33.679 27 53.573 0 21.022-27 23.914-27 54.094V1Z" clip-rule="evenodd"></path>
                                <path fill="#9FB3CB" fill-rule="evenodd" d="m13.815 50.977.125.142c.387.51.334 1.232-.124 1.677l-3.098 3.037 3.098 3.037.125.141a1.273 1.273 0 0 1-.128 1.68 1.286 1.286 0 0 1-1.804-.003l-4.025-3.946-.126-.142a1.276 1.276 0 0 1 .126-1.676l4.025-3.946.147-.124a1.29 1.29 0 0 1 1.659.123Z" clip-rule="evenodd"></path>
                            </g>
                            <defs>
                                <clipPath id="arrow_aa">
                                    <path fill="#fff" d="M0 0h30v113H0z"></path>
                                </clipPath>
                            </defs>
                        </svg>
                    </button>
                </div>
			<?php endif; ?>
        </div>

		<?php if ( $data['banner2'] ): ?>
            <div class="flex justify-between overflow-hidden bg-cover bg-center bg-no-repeat p-8 my-10 items-center gap-6 rounded-lg bg-[url('/assets/img/Image.svg')] max-md:flex-col lg:my-19 lg:rounded-3xl lg:p-10"
                 style="background-image:url(<?= $data['banner2']['image'] ?>)">
                <div class="max-w-47">
                    <h2 class="text-2xl font-extrabold text-white">نزدیکتـرین
                        <span
                                class="text-accent-400">اتـاق فـرار
                        </span>
                        را بر روی نقشه پیدا کن
                    </h2>
                </div>
                <a class="flex gap-4 items-center justify-center relative text-sm font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 transition-all duration-300 ease-in-out disabled:bg-slate-110 disabled:text-disabled disabled:cursor-not-allowed disabled:shadow-none text-white shadow-17 hover:bg-slate-900 focus-visible:outline-slate-900 h-16 min-w-16 px-9 py-2 rounded-2xl bg-slate-700 bg-opacity-90"
                   href="<?= $data['banner2']['url'] ?>">
                    <span class="truncate">مشاهده نقشه</span>
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="18" viewBox="0 0 24 24" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="3" class="text-accent-500">
                            <path d="M4.25 12.274h15m-8.95 6.025-6.05-6.024L10.3 6.25" vector-effect="non-scaling-stroke"></path>
                        </svg>
                    </span>
                </a>
            </div>
		<?php endif; ?>
        <div class="my-8 lg:mt-15" id="comments">
            <div class="flex items-center justify-between ">
                <div class="flex items-center gap-7">
                    <h3 class="text-20 lg:text-xl">
                        دیدگاه کاربران
                    </h3>
                    <span class="max-lg:hidden">
                        <span class="text-base">
							<?= get_comments_number( $data['id'] ) ?>
                        </span>
                        دیدگاه
                    </span>
                </div>
                <button type="button" class="flex items-center gap-2 text-14">ارسال دیدگاه
                    <span class="relative w-8">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="20" viewBox="0 0 24 24">
                            <path fill="currentColor" fill-rule="evenodd" d="M2 12.015C2 6.747 6.21 2 12.02 2 17.7 2 22 6.657 22 11.985 22 18.165 16.96 22 12 22c-1.64 0-3.46-.44-4.92-1.302-.51-.31-.94-.54-1.49-.36l-2.02.6c-.51.16-.97-.24-.82-.78l.67-2.244c.11-.31.09-.641-.07-.902C2.49 15.43 2 13.697 2 12.015Zm8.7 0c0 .711.57 1.282 1.28 1.292a1.283 1.283 0 0 0 0-2.564c-.7-.01-1.28.571-1.28 1.272Zm4.61.01c0 .701.57 1.282 1.28 1.282a1.283 1.283 0 0 0 0-2.564c-.71 0-1.28.571-1.28 1.282Zm-7.94 1.282c-.7 0-1.28-.58-1.28-1.282 0-.711.57-1.282 1.28-1.282.71 0 1.28.571 1.28 1.282a1.29 1.29 0 0 1-1.28 1.282Z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="absolute -top-1 right-0 h-4 w-4 rounded-full bg-accent-450 text-center text-lg leading-[1] text-white">
                            +
                        </span>
                    </span>
                </button>
            </div>
            <div class="mt-5 w-full border-t border-slate-100 lg:mt-7"></div>

            <div id="comments-list">

				<?php if ( count( $data['comments']['items'] ) > 0 ) {
					get_replies( $data['comments']['items'] );
				} else { ?>
                    <div class="border rounded-2xl mt-7 py-15 flex flex-col justify-center items-center gap-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="106" height="106" viewBox="0 0 106 106" fill="none">
                            <g clip-path="url(#clip0_4133_11957)">
                                <path d="M28.9313 95.2617L28.956 95.2767L28.9808 95.2913C36.194 99.55 45.0382 101.667 52.9987 101.667C77.0342 101.667 101.665 83.0562 101.665 52.9339C101.665 27.0235 80.7645 4.3335 53.087 4.3335C24.7867 4.3335 4.33203 27.4712 4.33203 53.0664C4.33203 61.353 6.73888 69.7398 10.8409 77.2857L10.8969 77.3886L10.9083 77.4073C10.9064 77.4479 10.897 77.519 10.8628 77.6155L10.8245 77.7233L10.7918 77.8329L7.83262 87.7439L7.82026 87.7853L7.8087 87.8269C7.05123 90.5538 7.79569 93.3629 9.81213 95.2218C11.7681 97.025 14.5228 97.5683 17.0741 96.7822L25.9692 94.1401L26.0286 94.1224L26.0875 94.1031C26.3159 94.0284 26.4407 94.0246 26.7227 94.1113C27.1794 94.2516 27.7776 94.5604 28.9313 95.2617Z" stroke="#E4EBF0" stroke-width="9"/>
                                <path d="M52.9126 58.7726C49.7768 58.7285 47.2593 56.2066 47.2593 53.0663C47.2593 49.9702 49.8209 47.4041 52.9126 47.4483C54.3756 47.5061 55.7595 48.1279 56.7741 49.1835C57.7888 50.239 58.3555 51.6463 58.3555 53.1105C58.3555 54.5746 57.7888 55.9819 56.7741 57.0375C55.7595 58.093 54.3756 58.7149 52.9126 58.7726Z" fill="#E4EBF0"/>
                                <path d="M73.2734 58.7726C70.1376 58.7726 67.6201 56.2066 67.6201 53.1105C67.6201 49.9702 70.1376 47.4483 73.2734 47.4483C74.7364 47.5061 76.1203 48.1279 77.135 49.1835C78.1496 50.239 78.7163 51.6463 78.7163 53.1105C78.7163 54.5746 78.1496 55.9819 77.135 57.0375C76.1203 58.093 74.7364 58.7149 73.2734 58.7726Z" fill="#E4EBF0"/>
                                <path d="M26.8984 53.1105C26.8984 56.211 29.4601 58.7726 32.5518 58.7726C34.0491 58.7611 35.4816 58.1605 36.5396 57.1009C37.5976 56.0413 38.1959 54.6078 38.2051 53.1105C38.2051 49.9702 35.6876 47.4483 32.5518 47.4483C29.4159 47.4483 26.8984 49.9702 26.8984 53.1105Z" fill="#E4EBF0"/>
                            </g>
                            <defs>
                                <clipPath id="clip0_4133_11957">
                                    <rect width="106" height="106" fill="white"/>
                                </clipPath>
                            </defs>
                        </svg>
                        <span class="text-lg text-slate-105">
                            هنور دیدگاهی ثبت نشده
                        </span>
                        <a href="#ez-comment-form" class="flex gap-4 items-center justify-center relative font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 transition-all duration-300 ease-in-out disabled:bg-slate-110 disabled:text-disabled disabled:cursor-not-allowed disabled:shadow-none bg-accent-450 text-white hover:bg-accent-500 focus-visible:outline-accent-500 h-16 min-w-16 px-9 py-2 rounded-2xl max-lg:rounded-lg max-lg:h-12 max-lg:min-w-12 max-lg:px-6 max-lg:py-1 text-base shadow-23">
                            <span class="truncate"> اولین دیدگــاه را ارســـال کنید</span>
                        </a>
                    </div>
				<?php } ?>

				<?php if ( $data['comments']['total_pages'] > 1 ) { ?>
                    <div class="mb-9 mt-20 flex w-full items-center justify-start gap-4">
                        <div class="flex gap-4 max-lg:gap-2 justify-start max-lg:justify-start pagination">
							<?php echo paginate_links( [
								'mid_size'  => 1,
								'base'      => get_pagenum_link( 1 ) . '%_%',
								'format'    => '?comment_page=%#%',
								'current'   => 1,
								'total'     => $data['comments']['total_pages'],
								'prev_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none" class="rotate-180 opacity-25"><path d="M5.08008 11.1602L1.51062 7.14452C1.17384 6.76563 1.17384 6.19468 1.51062 5.81579L5.08008 1.80016" stroke="#0A184A" stroke-width="2" stroke-linecap="round"></path></svg>',
								'next_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none"><path d="M5.08008 11.1602L1.51062 7.14452C1.17384 6.76563 1.17384 6.19468 1.51062 5.81579L5.08008 1.80016" stroke="#0A184A" stroke-width="2" stroke-linecap="round"></path></svg>',
							] ); ?>
                        </div>
                    </div>
				<?php } ?>

            </div>

            <div class="my-5 w-full border-t border-slate-100 lg:my-10"></div>

            <form class="w-full" id="ez-comment-form">
                <input type="hidden" name="post" value="<?php echo $data['id']; ?>">
                <input type="hidden" name="parent" value="0">
                <div class="mb-8 flex gap-2 max-md:flex-col lg:items-center lg:gap-7">
                    <h3 class="text-20">ارسال دیدگاه</h3>
                    <span class="text-14">برای مقاله
                        <span class="pr-1">
							<?php echo $data['title']; ?>
                        </span>
                    </span>
                </div>
                <div class="mb-4 flex w-1/2 max-lg:w-full gap-4 max-sm:flex-col">
                    <div class="w-full">
                        <div class="relative">
                            <input id="author" name="author" type="text" class="text-gray-900 block w-full border-0 p-1.5 text-16 shadow-13 outline-none ring-1 ring-inset ring-gray-100 placeholder:text-right placeholder:text-slate-200 focus:shadow-none focus:ring-2 focus:ring-inset focus:ring-primary-500 h-16 py-2 px-6 rounded-2xl" placeholder="نام شما">
                        </div>
                    </div>
                </div>
                <div class="max-lg:w-full">
                    <div class="relative">
                        <textarea id="content" name="content" rows="8" class="text-gray-900 block w-full rounded-2xl border-0 p-6 text-16 shadow-13 outline-none ring-1 ring-inset ring-gray-100 placeholder:text-slate-200 focus:shadow-none focus:ring-2 focus:ring-inset focus:ring-primary-500" placeholder="متن کامنت خود را اینجا وارد نمایید ..."></textarea>
                    </div>
                </div>
                <button type="submit" class="flex gap-4 items-center justify-center relative font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 transition-all duration-300 ease-in-out disabled:bg-slate-110 disabled:text-disabled disabled:cursor-not-allowed disabled:shadow-none bg-accent-450 text-white hover:bg-accent-500 focus-visible:outline-accent-500 h-16 min-w-16 px-9 py-2 rounded-2xl max-lg:rounded-lg max-lg:h-12 max-lg:min-w-12 max-lg:px-6 max-lg:py-1 mt-6.5 text-base shadow-23">
                    <span class="truncate">دیدگاهتان را ارسال کنید</span>
                </button>
            </form>

        </div>
    </article>
    <div class="w-d300 shrink-0 max-lg:hidden">
        <div class="sticky top-0 pt-3">
            <div class="mb-7 flex items-center gap-4">
                <span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="20" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M16.286 2h3.266A2.46 2.46 0 0 1 22 4.47v3.294c0 1.363-1.096 2.47-2.448 2.47h-3.266a2.46 2.46 0 0 1-2.45-2.47V4.47A2.46 2.46 0 0 1 16.287 2ZM4.449 2h3.265a2.46 2.46 0 0 1 2.45 2.47v3.294a2.46 2.46 0 0 1-2.45 2.47H4.45A2.46 2.46 0 0 1 2 7.764V4.47A2.46 2.46 0 0 1 4.449 2Zm0 11.766h3.265a2.46 2.46 0 0 1 2.45 2.47v3.294A2.46 2.46 0 0 1 7.713 22H4.45A2.46 2.46 0 0 1 2 19.53v-3.293a2.46 2.46 0 0 1 2.449-2.471Zm11.837 0h3.266A2.46 2.46 0 0 1 22 16.236v3.294A2.46 2.46 0 0 1 19.552 22h-3.266a2.46 2.46 0 0 1-2.45-2.47v-3.293a2.46 2.46 0 0 1 2.45-2.471Z"
                              clip-rule="evenodd" vector-effect="non-scaling-stroke"></path>
                    </svg>
                </span>
                <span class="text-2xs">عناوین مهم این پست</span>
            </div>
			<?php
			$dom      = new DOMDocument;
			$list_tag = mb_convert_encoding( $data['content'], 'HTML-ENTITIES', 'UTF-8' );
			libxml_use_internal_errors( true );
			$dom->loadHTML( $list_tag );
			libxml_clear_errors();
			$h2_tags = $dom->getElementsByTagName( 'h2' );
			$links   = [];
			foreach ( $h2_tags as $h2 ) {
				$link_text = $h2->nodeValue;
				$link_id   = $h2->getAttribute( 'id' );
				$links[]   = '<a class="menu-list -mr-5 border-r-2 pr-5 border-transparent" href="#' . $link_id . '">' . $link_text . '</a>';
			}
			foreach ( $links as $link ) {
				echo $link . '<br>';
			}
			?>
        </div>
    </div>
</section>

<script>
    jQuery(document).ready(function ($) {
        $('a.menu-list').on('click', function (e) {
            e.preventDefault();
            var target = $(this).attr('href');
            $('a.menu-list').removeClass('text-primary-500 !border-primary-500');
            $(this).addClass('text-primary-500 !border-primary-500');
            $('html, body').animate({
                scrollTop: $(target).offset().top
            }, 500);
        });
        $(window).on('scroll', function () {
            $('h2').each(function () {
                var top_of_element = $(this).offset().top;
                var bottom_of_element = top_of_element + $(this).outerHeight();
                var bottom_of_screen = $(window).scrollTop() + $(window).height();
                var top_of_screen = $(window).scrollTop();
                if (bottom_of_screen > top_of_element && top_of_screen < bottom_of_element) {
                    $('a.menu-list').removeClass('text-primary-500 !border-primary-500');
                    var targetLink = $('a[href="#' + $(this).attr('id') + '"]');
                    targetLink.addClass('text-primary-500 !border-primary-500');
                }
            });
        });
    })
</script>
<!-- Footer -->
<?php get_footer() ?>
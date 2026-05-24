<?php

get_header();

global $wpdb;

$author = ( get_query_var( 'author_name' ) ) ? get_user_by( 'slug', get_query_var( 'author_name' ) ) : get_userdata( get_query_var( 'author' ) );

$posts_count = $wpdb->get_var(
    $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}posts WHERE post_author=%d AND post_status=%s AND post_type=%s", [
        $author->ID,
        'publish',
        'post',
    ] ),
);

$registered = strtotime( $author->user_registered );
$registered = human_time_diff( $registered, current_time( 'timestamp' ) );


/* ==================== Start: All Posts ==================== */
$popular_posts = [];

$query = new WP_Query( [
    'post_type'      => 'post',
    'author__in'     => [ $author->ID ],
    'posts_per_page' => 3,
    'meta_key'       => 'views',
    'orderby'        => 'meta_value_num',
    'order'          => 'DESC',
] );

if ( $query->have_posts() ) {
    while ( $query->have_posts() ) {
        $query->the_post();
        $popular_posts[] = get_the_ID();
    }
    wp_reset_postdata();
}

/* ==================== Start: All Posts ==================== */
$all_posts = [];

$posts_per_page = 16;

$query = new WP_Query( [
    'post_type'      => 'post',
    'post_status'    => 'publish',
    'author__in'     => [ $author->ID ],
    'posts_per_page' => $posts_per_page,
] );

if ( $query->have_posts() ) {
    while ( $query->have_posts() ) {
        $query->the_post();
        $all_posts[] = get_the_ID();
    }
    wp_reset_postdata();
}

$total_pages = ceil( $posts_count / ( $posts_per_page ) );

$data = [
    'ID'            => $author->ID,
    'name'          => $author->display_name,
    'url'           => get_the_author_meta( 'url', $author->ID ),
    'posts_count'   => $posts_count,
    'views'         => 90000,
    'registered'    => $registered,
    'likes'         => 12000,
    'popular_posts' => $popular_posts,
    'all_posts'     => $all_posts,
    'total_pages'   => $total_pages,
];

?>

    <section class="mb-10">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center">
                <li class="group">
                    <div class="flex items-center">
                        <a class="text-2xs font-medium text-slate-310 hover:text-primary-600" href="<?php echo home_url(); ?>">
                            صفحه اصلی
                        </a>
                    </div>
                </li>
                <li class="group">
                    <div class="flex items-center">
                        <div class="mx-5 h-2 w-px bg-slate-110"></div>
                        <a class="text-2xs font-medium text-slate-310 hover:text-primary-600" href="<?php echo home_url( 'blog' ); ?>">
                            مجله خبری
                        </a>
                    </div>
                </li>
                <li class="group">
                    <div class="flex items-center">
                        <div class="mx-5 h-2 w-px bg-slate-110"></div>
                        <a class="text-2xs font-medium text-slate-310 cursor-text" href="<?php echo get_the_author_meta( 'url', $author->ID ); ?>">
                            <?php echo esc_html( $author->display_name ); ?>
                        </a>
                    </div>
                </li>
            </ol>
        </nav>
    </section>

    <section class="flex gap-8 max-lg:flex-col max-lg:text-center mb-12">

        <div class="flex lg:flex-col items-center justify-start gap-8 shrink-0">
            <?php echo get_avatar( $data['ID'], 185, 'wavatar', $data['name'], [
                'class' => 'aspect-square object-cover object-top rounded-3xl w-d185 max-lg:w-d120 shadow-100',
            ] ); ?>
            <div class="flex max-lg:flex-col grow text-right">
                <span class="text-3xl max-lg:text-2xl font-black mb-3 lg:hidden"><?php echo esc_html( $author->display_name ); ?></span>
                <div class="flex w-full lg:justify-between gap-4">
                    <div class="flex flex-col leading-6 text-md">
                        <span class="font-black text-lg text-textColor"><?php echo esc_html( $data['posts_count'] ); ?></span>
                        <span class="text-gray-400 text-sm">مطلب نوشته شده</span>
                    </div>
                    <div class="border-l"></div>
                    <div class="flex flex-col leading-6 text-md">
                        <span class="font-black text-lg text-textColor"><?php echo esc_html( $data['registered'] ); ?></span>
                        <span class="text-gray-400 text-sm">سابقه عضویت</span>
                    </div>
                </div>
            </div>

        </div>

        <div class="grow">

            <div class="flex justify-between items-center max-lg:justify-center">
                <h2 class="text-3xl max-lg:text-2xl font-black mb-3 max-lg:hidden"><?php echo esc_html( $author->display_name ); ?></h2>
                <button type="button" class="hidden lg:flex">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <rect x="0.5" y="0.5" width="23" height="23" rx="5.5" stroke="#889BAD"/>
                        <path d="M13.3636 16.0174L9.82791 14.1856C9.3663 14.6544 8.76693 14.9805 8.10769 15.1214C7.44845 15.2624 6.75982 15.2117 6.1313 14.976C5.50278 14.7402 4.96343 14.3304 4.58334 13.7996C4.20325 13.2688 4 12.6418 4 11.9998C4 11.3579 4.20325 10.7308 4.58334 10.2C4.96343 9.66927 5.50278 9.25939 6.1313 9.02366C6.75982 8.78793 7.44845 8.73725 8.10769 8.8782C8.76693 9.01915 9.3663 9.34521 9.82791 9.81402L13.3645 7.98219C13.164 7.22703 13.2603 6.42834 13.6354 5.73582C14.0104 5.0433 14.6385 4.50451 15.4018 4.22044C16.1652 3.93637 17.0113 3.92653 17.7817 4.19277C18.5522 4.459 19.1939 4.98303 19.5867 5.66662C19.9795 6.35021 20.0964 7.14643 19.9154 7.90602C19.7344 8.66562 19.2681 9.33643 18.6037 9.79272C17.9393 10.249 17.1226 10.4594 16.3066 10.3846C15.4906 10.3097 14.7313 9.95469 14.1711 9.38606L10.6346 11.2179C10.7706 11.7309 10.7706 12.2679 10.6346 12.7809L14.1711 14.6128C14.7316 14.0444 15.491 13.6898 16.3071 13.6153C17.1231 13.5408 17.9397 13.7516 18.6038 14.2082C19.268 14.6647 19.734 15.3357 19.9146 16.0954C20.0952 16.8551 19.978 17.6512 19.5849 18.3346C19.1918 19.018 18.5498 19.5418 17.7793 19.8077C17.0087 20.0736 16.1626 20.0634 15.3994 19.7791C14.6362 19.4947 14.0084 18.9557 13.6336 18.2631C13.2588 17.5704 13.1628 16.7717 13.3636 16.0166M7.36832 13.5993C7.81497 13.5993 8.24332 13.4307 8.55914 13.1307C8.87497 12.8307 9.0524 12.4237 9.0524 11.9994C9.0524 11.5751 8.87497 11.1682 8.55914 10.8682C8.24332 10.5681 7.81497 10.3996 7.36832 10.3996C6.92168 10.3996 6.49333 10.5681 6.1775 10.8682C5.86168 11.1682 5.68425 11.5751 5.68425 11.9994C5.68425 12.4237 5.86168 12.8307 6.1775 13.1307C6.49333 13.4307 6.92168 13.5993 7.36832 13.5993ZM16.6307 8.79972C17.0774 8.79972 17.5057 8.63116 17.8216 8.33113C18.1374 8.0311 18.3148 7.62417 18.3148 7.19987C18.3148 6.77556 18.1374 6.36863 17.8216 6.0686C17.5057 5.76857 17.0774 5.60002 16.6307 5.60002C16.1841 5.60002 15.7557 5.76857 15.4399 6.0686C15.1241 6.36863 14.9467 6.77556 14.9467 7.19987C14.9467 7.62417 15.1241 8.0311 15.4399 8.33113C15.7557 8.63116 16.1841 8.79972 16.6307 8.79972ZM16.6307 18.3988C17.0774 18.3988 17.5057 18.2303 17.8216 17.9302C18.1374 17.6302 18.3148 17.2233 18.3148 16.799C18.3148 16.3747 18.1374 15.9677 17.8216 15.6677C17.5057 15.3677 17.0774 15.1991 16.6307 15.1991C16.1841 15.1991 15.7557 15.3677 15.4399 15.6677C15.1241 15.9677 14.9467 16.3747 14.9467 16.799C14.9467 17.2233 15.1241 17.6302 15.4399 17.9302C15.7557 18.2303 16.1841 18.3988 16.6307 18.3988Z" fill="#889BAD"/>
                    </svg>
                </button>
            </div>

            <p class="mb-4 text-justify text-textColor"><?php echo $author->description; ?></p>

        </div>
    </section>

<?php if ( ! empty( $data['popular_posts'] ) ) : ?>
    <section class="overflow-y-clip [&~div_.ez-footer-logo>path:last-of-type]:fill-gray-50">
        <div class="mt-9 bg-gray-50 pb-8 pt-9 text-textColor shadow-[0_0px_0_1000px_#f2f6fa] lg:mt-14 lg:pb-20 lg:pt-12">

            <div class="mb-6 md:mb-8">
                <div class="justify-between">
                    <div class="items-center gap-6 md:flex">
                        <h2 class="flex items-center gap-4">
                            <span class="font-bold md:text-lg">
                                <span class="text-xl">محبوب ترین مقالات <?php echo esc_html( $data['name'] ); ?></span>
                            </span>
                        </h2>
                    </div>
                </div>
            </div>

            <div class="relative w-full max-sm:max-w-bleed-2 max-sm:w-bleed-2 max-sm:-mr-4" dir="rtl">
                <div class="h-full w-full overflow-hidden max-sm:px-4 grid grid-cols-1 lg:grid-cols-3 lg:-mx-10">
                    <?php foreach ( $data['popular_posts'] as $post ): ?>
                        <div class="lg:px-10 lg:border-l lg:last-of-type:border-l-0 max-lg:border-b max-lg:py-10 max-lg:last-of-type:border-b-0">
                            <a href="<?php echo get_the_permalink( $post ); ?>" class="flex gap-4">
                                <?php echo get_the_post_thumbnail( $post, 'medium', [
                                    'class' => 'rounded-2xl w-d150 object-cover object-center',
                                ] ); ?>
                                <div class="flex flex-col">
                                    <h3 class="text-lg mb-2"><?php echo get_the_title( $post ); ?></h3>
                                    <p class="mb-4 line-clamp-2"><?php echo get_the_excerpt( $post ); ?></p>
                                    <span class="flex items-center gap-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                            <path d="M16.158 8.28375C16.386 8.60325 16.5 8.76375 16.5 9C16.5 9.237 16.386 9.39675 16.158 9.71625C15.1335 11.1532 12.5168 14.25 9 14.25C5.4825 14.25 2.8665 11.1525 1.842 9.71625C1.614 9.39675 1.5 9.23625 1.5 9C1.5 8.763 1.614 8.60325 1.842 8.28375C2.8665 6.84675 5.48325 3.75 9 3.75C12.5175 3.75 15.1335 6.8475 16.158 8.28375Z" stroke="#09192D" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M11.25 9C11.25 8.40326 11.0129 7.83097 10.591 7.40901C10.169 6.98705 9.59674 6.75 9 6.75C8.40326 6.75 7.83097 6.98705 7.40901 7.40901C6.98705 7.83097 6.75 8.40326 6.75 9C6.75 9.59674 6.98705 10.169 7.40901 10.591C7.83097 11.0129 8.40326 11.25 9 11.25C9.59674 11.25 10.169 11.0129 10.591 10.591C11.0129 10.169 11.25 9.59674 11.25 9Z" stroke="#09192D" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
										<?php $views = (int) get_post_meta( $post, 'views', true );
                                        echo esc_html( $views ); ?>
                                    </span>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>


<?php if ( ! empty( $data['all_posts'] ) ) : ?>

    <section class="all-posts">

        <div class="md:mb-8 mb-8 mt-8 lg:mb-0 mt-20 [&amp;>div]:items-start">
            <div class="flex justify-between">
                <div class="items-center gap-6 md:flex">
                    <h2 class="flex items-center gap-4">
                        <span class="text-base font-bold md:text-lg">
                            <span class="text-">مقالات منتشر شده توسط <?php echo esc_html( $data['name'] ); ?></span>
                        </span>
                    </h2>
                </div>
            </div>
        </div>

        <div class="mb-11.5 w-full border-t border-slate-100 max-lg:hidden"></div>

        <div class="grid auto-cols-max grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3 lg:gap-11 2xl:grid-cols-4">
            <?php foreach ( $data['all_posts'] as $post ): ?>
                <div class="w-full max-sm:border-b max-sm:border-slate-100 max-sm:pb-8">
                    <a href="<?php echo get_the_permalink( $post ); ?>">
                        <div class="h-44 w-full overflow-hidden lg:h-54 rounded-xlh">
                            <?php echo get_the_post_thumbnail( $post, 'medium_large', [
                                'class' => 'h-full w-full object-cover',
                            ] ); ?>
                        </div>
                        <div class="mt-8">
                            <h2 class="truncate text-base"><?php echo get_the_title( $post ); ?></h2>
                            <div class="mt-3 flex items-center gap-5 text-xs text-slate-350">
                                <span>
									<?php $views = (int) get_post_meta( $post, 'views', true );
                                    echo esc_html( $views ); ?>
                                    بازدید
                                </span>
                                <span>
									<?php $categories = get_the_category();
                                    if ( ! empty( $categories ) ) {
                                        echo esc_html( $categories[0]->name );
                                    } ?>
                                </span>
                                <time datetime="<?php echo get_post_timestamp( $post ); ?>" dir="ltr">
                                    <?php echo jdate( "Y . m . d", get_post_timestamp( $post ) ); ?>
                                </time>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mb-9 flex w-full items-center justify-center gap-4">
            <div class="flex gap-4 max-lg:gap-2 mt-16 justify-start max-lg:justify-center pagination">
                <?php echo paginate_links( [
                    'mid_size'  => 1,
                    'base'      => get_pagenum_link( 1 ) . '%_%',
                    'format'    => '?page=%#%',
                    'current'   => max( 1, get_query_var( 'paged' ) ),
                    'total'     => $total_pages,
                    'prev_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none" class="rotate-180 opacity-25"><path d="M5.08008 11.1602L1.51062 7.14452C1.17384 6.76563 1.17384 6.19468 1.51062 5.81579L5.08008 1.80016" stroke="#0A184A" stroke-width="2" stroke-linecap="round"></path></svg>',
                    'next_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none"><path d="M5.08008 11.1602L1.51062 7.14452C1.17384 6.76563 1.17384 6.19468 1.51062 5.81579L5.08008 1.80016" stroke="#0A184A" stroke-width="2" stroke-linecap="round"></path></svg>',
                ] ); ?>
            </div>
        </div>

        <script>
            jQuery(document).ready(function ($) {
                $(".pagination a.page-numbers").each((index, item) => {
                    let page = $(item).attr('href').split('?page=')[1]

                    $(item).on('click', function (e) {
                        e.preventDefault()

                        $.ajax({
                            type: 'POST',
                            url: "<?php echo admin_url( 'admin-ajax.php' ) ?>",
                            data: {
                                'action': 'v2_ajax_handler',
                                'nonce': "<?php echo wp_create_nonce( 'v2-ajax-nonce' ) ?>",
                                'callback': 'get_author_posts',
                                'author': <?php echo $data['ID']; ?>,
                                'base_url': $(location).attr('href'),
                                'page': page,
                                'posts_per_page': <?php echo $posts_per_page; ?>,
                                'total_pages': <?php echo $total_pages; ?>
                            },
                            success: function (data) {
                                $(".all-posts").html(data)
                            },
                        });
                    })
                })
            })
        </script>

    </section>

<?php endif; ?>

<?php get_footer();
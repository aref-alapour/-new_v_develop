<?php
/**
 * Template Name: Page Collections
 */

get_header();

global $wpdb;

$page_num = get_query_var( 'paged' ) ? : 1;

$sort = $sort ? : 'recent';

$items_per_page = 20;
$offset         = ($page_num - 1) * $items_per_page;

if ( $sort == -1 ) {
    $sort_by = 'RAND()';
} elseif ( $sort == 'recent' ) {
    $sort_by = 'ID';
} elseif ( $sort == 'popular' ) {
    $sort_by = 'users';
}

$collections = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM collections WHERE active LIKE 1 AND CHAR_LENGTH(items) > 1 AND items NOT LIKE \"a:0:{}\" ORDER BY {$sort_by} DESC LIMIT {$offset}, {$items_per_page}" ) );

$items = [];
foreach ( $collections as $collection ) {

    $images = [];

    foreach ( unserialize($collection->items) as $product_id ){
        $images[] = get_post_thumbnail_id( $product_id );
    }

    $items[] =  [
        'title'  => $collection->title,
        'likes'  => $collection->users ? count( unserialize( $collection->users) ) : 0,
        'user'   => $collection->user_id,
        'images' => $images,
    ];
}

$collections_count =  $wpdb->get_var("SELECT COUNT(*) FROM collections WHERE active LIKE 1 AND CHAR_LENGTH(items) > 1 AND items NOT LIKE \"a:0:{}\"");

$total_pages = ceil( $collections_count / $items_per_page); ?>

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
                        <a class="text-2xs font-medium text-slate-310 cursor-text" href="">کالکشن ها</a>
                    </div>
                </li>
            </ol>
        </nav>
    </section>

    <section class="flex justify-start gap-x-6 items-center w-full">
        <h1 class="text-2xl">
            کالکشن ها
        </h1>
        <span class="help" data-help="لیست بازی‌هایی که توسط بعضی از کاربرها، با توجه به تجربه‌شون ساخته شده و جنبۀ پیشنهادی برای بقیۀ پلیرها داره. "></span>
        <div class="flex leading-3 gap-3 hidden">
            <button type="button" data-sort="all" class="border py-3 px-4 rounded-xl text-white bg-primaryColor">همه</button>
            <button type="button" data-sort="new" class="border py-3 px-4 rounded-xl">جدیدترین</button>
            <button type="button" data-sort="popular" class="border py-3 px-4 rounded-xl">محبوب ترین</button>
        </div>
    </section>

    <section class="max-w-full py-4 md:py-5 lg:py-9">
        <div class="relative w-full grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-10">

            <?php foreach( $items as $item ) : ?>
                <div class="h-full w-full">
                    <div class="relative min-w-0 shrink-0 grow-0 basis-42 lg:basis-77 h-full">
                        <a class="flex w-full h-full flex-col justify-between gap-2.5 overflow-hidden rounded-lg border border-slate-120 px-3 py-4 shadow-22 lg:gap-5 lg:rounded-3xl lg:px-5 lg:py-6 lg:border-none lg:bg-slate-700 lg:text-white lg:shadow-6 lg:[&>div]:border-none" href="<?php echo site_url( '/profile/' . $item['user'] ); ?>">
                            <div class="items-center justify-between text-2xs lg:flex">
                                <h3 class="text-sm lg:text-lg"><?php echo esc_html( $item['title'] ); ?></h3>
                            </div>

                            <div class="grid min-w-28 grid-cols-3 gap-1 border-b border-t border-slate-100 px-2 lg:gap-3 grid-rows-2 max-lg:grid-rows-1">

                                <?php foreach ( array_slice( $item['images'] , 0, 6) as $image) : ?>
                                    <div class="w-9 lg:w-[52px] lg:[&:last-of-type>div>div]:flex max-lg:[&:nth-child(n+4)]:hidden max-lg:[&:nth-of-type(3)>div>div]:flex">
                                        <div class="relative overflow-hidden rounded-md shadow-2">

                                            <?php echo wp_get_attachment_image($image, 'thumbnail', true, [
                                                'class' => 'h-[66px] w-[52px] object-cover'
                                            ]);?>

                                            <?php if( count ( $item['images'] ) > 5 ) :?>
                                                <div class="aria-hidden absolute right-0 top-0 hidden h-full w-full items-center justify-center bg-primary-500/80 text-white">
                                                    <span class="text-xl" dir="ltr">+<?php echo count ( $item['images'] ) - 5; ?></span>
                                                </div>
                                            <?php endif; ?>

                                        </div>
                                    </div>
                                <?php endforeach; ?>

                            </div>

                            <div class="flex flex-col max-lg:flex-col-reverse gap-4 max-lg:gap-0">
                                <div class="inline-flex w-full items-center justify-between">
                                    <span class="text-sm font-bold text-white max-lg:text-textColor"><?php echo get_user_by( 'ID', $item['user'] )->display_name; ?></span>
                                    <?php user_badge_by_level($item['user'] , 'text-xs text-[#FD7013] bg-white max-lg:bg-[#FD701338] max-lg:text-[] p-2 max-lg:py-1 rounded-full text-nowrap'); ?>
                                </div>
                                <div class="inline-flex w-auto items-center justify-center gap-2 text-2xs max-lg:flex-row-reverse max-lg:justify-end">
                                    <span class="flex items-center gap-2 max-lg:flex-row-reverse">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="24" viewBox="0 0 24 24" class="mx-0 w-3.5 text-secondary-600 lg:w-4.5">
                                            <path fill="currentColor" fill-rule="evenodd" d="M15.85 2.5c.63 0 1.26.09 1.86.29 3.69 1.2 5.02 5.25 3.91 8.79a12.728 12.728 0 0 1-3.01 4.81 38.456 38.456 0 0 1-6.33 4.96l-.25.15-.26-.16a38.093 38.093 0 0 1-6.37-4.96 12.933 12.933 0 0 1-3.01-4.8c-1.13-3.54.2-7.59 3.93-8.81.29-.1.59-.17.89-.21h.12c.28-.04.56-.06.84-.06h.11c.63.02 1.24.13 1.83.33h.06c.04.02.07.04.09.06.22.07.43.15.63.26l.38.17c.092.05.195.125.284.19.056.04.107.077.146.1l.05.03c.085.05.175.102.25.16a6.263 6.263 0 0 1 3.85-1.3Zm2.66 7.2c.41-.01.76-.34.79-.76v-.12a3.3 3.3 0 0 0-2.11-3.16.8.8 0 0 0-1.01.5c-.14.42.08.88.5 1.03.64.24 1.07.87 1.07 1.57v.03a.86.86 0 0 0 .19.62c.14.17.35.27.57.29Z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="text-lg"><?php echo esc_html( $item['likes'] ); ?></span>
                                    </span>
                                    <span class="w-full max-lg:w-auto text-3xs lg:text-2xs">
                                        <span class="opacity-50">نفر پسندیدند</span>
                                    </span>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

<?php if ( $total_pages > 1 ) : ?>
    <div class="mb-9 flex w-full items-center justify-center gap-4">
        <div class="flex gap-4 max-lg:gap-2 mt-16 justify-start max-lg:justify-center">
            <?php echo paginate_links( array(
                'mid_size'  => 1,
                'base'      => get_pagenum_link(1) . '%_%',
                'format'    => 'page/%#%',
                'current'   => max( 1, get_query_var('paged') ),
                'total'     => $total_pages,
                'prev_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none" class="rotate-180 opacity-25"><path d="M5.08008 11.1602L1.51062 7.14452C1.17384 6.76563 1.17384 6.19468 1.51062 5.81579L5.08008 1.80016" stroke="#0A184A" stroke-width="2" stroke-linecap="round"></path></svg>',
                'next_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none"><path d="M5.08008 11.1602L1.51062 7.14452C1.17384 6.76563 1.17384 6.19468 1.51062 5.81579L5.08008 1.80016" stroke="#0A184A" stroke-width="2" stroke-linecap="round"></path></svg>',
            ) ); ?>
        </div>
    </div>
<?php endif; ?>

<?php get_footer();
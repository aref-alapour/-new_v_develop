<?php
$items_per_page = 10;

$collections = $wpdb->get_results(
	$wpdb->prepare(
		'SELECT * FROM collections WHERE active = %d AND items NOT LIKE %s ORDER BY LENGTH(users) DESC LIMIT %d',
		1,
		'a:0:{}',
		(int) $items_per_page
	)
);

$collection_items = [];

foreach ($collections as $collection) {

    $images = [];
    foreach (unserialize($collection->items) as $product_id) {
        $images[] = wp_get_attachment_url(get_post_thumbnail_id($product_id));
    }

    $collection_items[] = [
        'title'       => $collection->title,
        'user_id'     => $collection->user_id,
        'user_title'  => get_user_by('id', $collection->user_id)->display_name,
        'user_level'  => get_user_level($collection->user_id),
        'likes_count' => $collection->users ? count(unserialize($collection->users)) : 0,
        'url'         => "/profile/" . (int) $collection->user_id,
        'count'       => count(unserialize($collection->items)),
        'items'       => $images,
    ];
}
?>
<section class="max-w-full py-4 md:py-5 lg:py-9">
    <div class="mb-6 md:mb-8">
        <div class="flex justify-between">
            <div class="items-center gap-6 md:flex">
                <h3 class="flex items-center gap-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28" fill="none">
                        <path d="M11.4493 21.7253L7.92601 24.4294C7.67473 24.6326 7.37461 24.7613 7.0579 24.8015C6.74118 24.8418 6.41983 24.7922 6.12836 24.658C5.83689 24.5238 5.58631 24.3101 5.40354 24.0398C5.22076 23.7696 5.11269 23.453 5.09094 23.1241V9.00191C5.10922 8.48154 5.22656 7.97 5.43626 7.4965C5.64595 7.02299 5.94389 6.59682 6.31306 6.24232C6.68222 5.88782 7.11538 5.61194 7.58777 5.43045C8.06016 5.24896 8.56254 5.16541 9.0662 5.18457H16.0101C17.0269 5.14729 18.0165 5.52827 18.7618 6.24393C19.507 6.95958 19.9471 7.95146 19.9853 9.00191V23.1262C19.9641 23.4554 19.8563 23.7723 19.6736 24.0429C19.4909 24.3134 19.2401 24.5273 18.9484 24.6615C18.6567 24.7957 18.3351 24.8451 18.0182 24.8044C17.7014 24.7637 17.4013 24.6344 17.1503 24.4305L13.627 21.7264C13.3107 21.4883 12.9295 21.36 12.5381 21.36C12.1468 21.36 11.7656 21.4872 11.4493 21.7253Z" stroke="#09192D" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M9.34668 2.97815C9.72012 2.6208 10.1583 2.3427 10.6361 2.15975C11.114 1.97679 11.6222 1.89257 12.1317 1.91189H19.1559C20.1845 1.8743 21.1856 2.25835 21.9395 2.97977C22.6933 3.70118 23.1385 4.70104 23.1772 5.75995V12.8789V19.9979C23.1557 20.3297 23.0467 20.6492 22.8618 20.9219C22.677 21.1946 22.4234 21.4103 22.1283 21.5455" stroke="#09192D" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span class="text-base font-bold md:text-lg">
                        <span class="text-md">کالکشن های محبوب کاربران</span>
                    </span>
                </h3>
                <div class="hidden md:block"></div>
            </div>
            <div class="flex items-center gap-6">
                <div class="hidden md:block"></div>
                <a href="/collections/">
                    <div class="flex items-center gap-1.5 text-2xs lg:gap-3.5 lg:text-xs hover:text-primary-500 transition">
                        مشاهده همه
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="20" viewBox="0 0 24 24" class="max-lg:hidden">
                            <path clip-rule="evenodd"
                                d="M16.335 2.75h-8.67c-3.02 0-4.914 2.14-4.914 5.166v8.168c0 3.027 1.884 5.166 4.915 5.166h8.668c3.03 0 4.917-2.139 4.917-5.166V7.916c0-3.027-1.886-5.166-4.916-5.166z"
                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round"></path>
                            <path d="M7.52 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395zM12 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395zM16.48 13.197a1.199 1.199 0 010-2.395 1.199 1.199 0 010 2.395z"
                                fill="currentColor"></path>
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="12" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lg:hidden">
                            <path d="M15.5 19l-7-7 7-7" vector-effect="non-scaling-stroke"></path>
                        </svg>
                    </div>
                </a>
            </div>
        </div>
    </div>
    <div class="embla_normal relative overflow-hidden">
        <div class="embla__viewport">
            <div class="embla__container flex gap-x-3.5 md:gap-x-8 py-5 child:w-d168 md:child:w-d236 child:grow-0 child:shrink-0">
                <?php foreach ($collection_items as $item): ?>
                    <div class="embla__slide relative collection-item">
                        <a class="flex w-full h-full flex-col justify-between gap-2.5 overflow-hidden rounded-lg border border-slate-120 px-3 py-4 shadow-22 lg:gap-5 lg:rounded-3xl lg:px-5 lg:py-6 lg:border-none lg:bg-slate-700 lg:text-white lg:shadow-6 lg:[&>div]:border-none" href="<?= home_url() . $item['url'] ?>">
                            <div class="items-center justify-between text-2xs lg:flex">
                                <h4 class="text-sm lg:text-lg line-clamp-1"><?= $item['title'] ?></h4>
                            </div>
                            <div class="grid min-w-28 grid-cols-3 gap-1 lg:gap-3 grid-rows-2 max-lg:grid-rows-1">
                                <?php foreach (array_slice($item['items'], 0, 6) as $image) : ?>
                                    <div class="w-9 lg:w-d52 lg:[&:last-of-type>div>div]:flex max-lg:[&:nth-child(n+4)]:hidden max-lg:[&:nth-of-type(3)>div>div]:flex max-md:w-d37 max-md:h-d47">
                                        <div class="relative overflow-hidden rounded-md shadow-2">
                                            <img src="<?= $image ?>" alt="<?= $item['title'] ?>" class="max-md:w-d37 max-md:h-d47 h-d66 w-d52 object-cover">
                                            <?php if (count($item['items']) > 5) : ?>
                                                <div class="aria-hidden absolute right-0 top-0 hidden h-full w-full items-center justify-center bg-primary-500/80 text-white">
                                                    <span class="text-xl" dir="ltr">
                                                        +<?php echo count($item['items']) - 5; ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="flex flex-col max-lg:flex-col-reverse gap-4 max-lg:gap-0">
                                <div class="inline-flex w-full items-center justify-between">
                                    <span class="text-sm font-bold text-white max-lg:text-textColor">
                                        <?= $item['user_title'] ?>
                                    </span>
                                    <?php user_badge_by_level($item['user_id']); ?>
                                </div>
                                <div class="inline-flex w-auto items-center justify-center gap-2 text-2xs max-lg:flex-row-reverse max-lg:justify-end">
                                    <span class="flex items-center gap-2 max-lg:flex-row-reverse">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="24" viewBox="0 0 24 24" class="w-3.5 text-secondary-600 lg:w-4.5">
                                            <path fill="currentColor" fill-rule="evenodd" d="M15.85 2.5c.63 0 1.26.09 1.86.29 3.69 1.2 5.02 5.25 3.91 8.79a12.728 12.728 0 0 1-3.01 4.81 38.456 38.456 0 0 1-6.33 4.96l-.25.15-.26-.16a38.093 38.093 0 0 1-6.37-4.96 12.933 12.933 0 0 1-3.01-4.8c-1.13-3.54.2-7.59 3.93-8.81.29-.1.59-.17.89-.21h.12c.28-.04.56-.06.84-.06h.11c.63.02 1.24.13 1.83.33h.06c.04.02.07.04.09.06.22.07.43.15.63.26l.38.17c.092.05.195.125.284.19.056.04.107.077.146.1l.05.03c.085.05.175.102.25.16a6.263 6.263 0 0 1 3.85-1.3Zm2.66 7.2c.41-.01.76-.34.79-.76v-.12a3.3 3.3 0 0 0-2.11-3.16.8.8 0 0 0-1.01.5c-.14.42.08.88.5 1.03.64.24 1.07.87 1.07 1.57v.03a.86.86 0 0 0 .19.62c.14.17.35.27.57.29Z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="text-lg"><?= esc_html($item['likes_count']); ?></span>
                                    </span>
                                    <span class="w-full max-lg:w-auto text-3xs lg:text-2xs">
                                        <span class="opacity-50">نفر پسندیدند</span>
                                    </span>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <button class="embla__button embla__button--prev trends-rooms-btn absolute right-0 top-1/2 -translate-y-1/2 rotate-180 z-50 cursor-pointer touch-manipulation appearance-none -mr-px hidden" type="button">
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
        <button class="embla__button embla__button--next trends-rooms-btn absolute left-0 top-1/2 -translate-y-1/2 z-50 cursor-pointer touch-manipulation appearance-none -ml-px hidden" type="button">
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
</section>

<script>
    jQuery(document).ready(function($) {
        $('.collection-item').on('click', function() {
            var title = $(this).find('h4').text();
            var href = $(this).find('a').attr('href');
            var currentPage = window.location.href;
            var currentPageTitle = '<?= esc_js(get_the_title()) ?>';
            var currentPageId = <?= get_the_ID() ?>;
            zebline.event.track("collection_click", {
                "title": title,
                "href": href,
                "current_page": currentPage,
                "current_page_title": currentPageTitle,
                "current_page_id": currentPageId,
            });
        });
    });
</script>
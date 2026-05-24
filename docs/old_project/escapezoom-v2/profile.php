<?php
global $wpdb;

$profile_user_id    = get_query_var('profile');
$profile_user       = get_user_by('id', $profile_user_id);
$user_id            = get_current_user_id();
$user_meta          = get_userdata($user_id);
$user_roles         = $user_meta->roles;

if (empty($profile_user))
    page_404();

/******************************************************/
// بازی های انجام شده

// محصولات خریداری‌شده توسط کاربر از wp_markting
$medoo = medoo();
$purchased_products = [];

if ($medoo) {
    try {
        $where_conditions = [
            'customer_id' => (int) $profile_user_id,
            'order_status' => ['wc-partially-paid', 'wc-walletx', 'wc-completed']
        ];
        
        $orders = $medoo->select('wp_markting', [
            'game_id'
        ], $where_conditions);
        
        if (is_array($orders)) {
            foreach ($orders as $order_data) {
                if (!empty($order_data['game_id'])) {
                    $purchased_products[] = (int) $order_data['game_id'];
                }
            }
        }
    } catch (Exception $e) {
        error_log('Error in profile.php fetching from wp_markting: ' . $e->getMessage());
    }
}

//  محصولات teammate (هم‌گروهی)
$teammate_products = get_user_meta($profile_user_id, 'teammate_products', true);
if (!is_array($teammate_products))
    $teammate_products = [];

// برای نمایش HTML نیاز به بازی‌های unique داریم
$all_products_unique = array_unique(array_merge($purchased_products, $teammate_products));

// تعداد کل سفارشات (با احتساب تکرارها)
$total_orders_count = count($purchased_products);

if ($all_products_unique)
    $all_products_html = json_decode(ez_webservice([
        'type' => 'get_by_products_id',
        'data' => [
            'products_id' => $all_products_unique,
            'format'      => 'html_swiper',
        ],
    ]));

/******************************************************/
// لایک و دیس لایک مجموعه دار به کاربر

$liked = get_user_meta($profile_user_id, 'owners_like', true);
$disliked = get_user_meta($profile_user_id, 'owners_dislike', true);
$owner_feedback = get_user_meta($profile_user_id, 'owners_feedback', true);

/******************************************************/
// کالکشن ها

$collections = $wpdb->get_results($wpdb->prepare("SELECT * FROM collections WHERE user_id LIKE {$profile_user_id} AND active LIKE 1 AND items NOT LIKE \"a:0:{}\""));

$collection_items = [];
foreach ($collections as $collection) {
    $collection_products = json_decode(ez_webservice([
        'type' => 'get_by_products_id',
        'data' => [
            'products_id' => unserialize($collection->items),
            'format'      => 'html_swiper',
        ],
    ]));

    $liked_collections  = get_user_meta($user_id, 'liked_collections', true);
    $liked_collections  = is_array($liked_collections) ? $liked_collections : [];
    $collection_items[] = [
        'id'    => (int) $collection->ID,
        'title' => $collection->title,
        'type'  => $collection->type,
        'users' => $collection->users ? unserialize($collection->users) : [],
        'liked' => in_array($collection->ID, $liked_collections),
        'items' => $collection_products,
    ];
}

/******************************************************/

$data = [
    'ID'             => (int) $profile_user_id,
    'name'           => $profile_user->data->display_name,
    'banner'         => 'https://escapezoom.ir/wp-content/uploads/2024/05/profile-banner.png',
    'city'           => get_user_meta($profile_user_id, 'user_city', true) ?: '',
    'level'          => get_user_level($profile_user_id),
    'played_count'   => $total_orders_count,
    'points'         => get_user_points($profile_user_id),
    'bio'            => get_user_meta($profile_user_id)['description'][0],
    'recent_played'  => $all_products_html,
    'register_date'  => strtotime($profile_user->data->user_registered),
    'recent_comment' => get_comments([
        'user_id'   => (int) $profile_user_id,
        'number'    => 1,
        'status'    => 'approve',
        'post_type' => 'product',
        'parent'    => 0,
    ]),
    'collections'    => $collection_items,
];

add_filter('pre_get_document_title', function ($title) use ($data) {
    return 'پروفایل ' . $data['name'] . ' | کاربر اسکیپ زوم';
}, 999);

add_filter('wpseo_title', function ($title) use ($data) {
    return 'پروفایل ' . $data['name'] . ' | کاربر اسکیپ زوم';
}, 999);

get_header(); ?>

    <nav class="flex mt-10 max-md:hidden" aria-label="Breadcrumb">
        <ol class="inline-flex items-center">
            <li class="group">
                <div class="flex items-center">
                    <a class="font-medium text-2xs text-slate-310 hover:text-primary-600" href="
					<?php echo home_url(); ?>"> صفحه اصلی </a>
                </div>
            </li>
            <li class="group">
                <div class="flex items-center">
                    <div class="w-px h-2 mx-5 bg-slate-110"></div>
                    <a class="font-medium text-2xs text-slate-310 hover:text-primary-600" href="
					<?php echo site_url('profile/' . (int) $profile_user_id); ?>"> پروفایل <?php echo $data['name']; ?> </a>
                </div>
            </li>
        </ol>
    </nav>
    <div class="max-lg:mb-4.5 max-lg:flex max-lg:items-center max-lg:justify-between lg:mt-5.5 lg:text-left max-lg:hidden">
        <button type="button" class="hidden">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="4" viewBox="0 0 16 4" fill="none">
                <path d="M12 2C12 0.895431 12.8954 9.52512e-07 14 1.04908e-06C15.1046 1.14564e-06 16 0.895432 16 2C16 3.10457 15.1046 4 14 4C12.8954 4 12 3.10457 12 2Z" fill="#889BAD"></path>
                <path d="M6 2C6 0.895431 6.89543 4.27974e-07 8 5.24538e-07C9.10457 6.21103e-07 10 0.895431 10 2C10 3.10457 9.10457 4 8 4C6.89543 4 6 3.10457 6 2Z" fill="#889BAD"></path>
                <path d="M0 2C9.65645e-08 0.89543 0.895431 -9.65609e-08 2 0C3.10457 9.65682e-08 4 0.895431 4 2C4 3.10457 3.10457 4 2 4C0.89543 4 -9.65645e-08 3.10457 0 2Z" fill="#889BAD"></path>
            </svg>
        </button>
        <button type="button" class="flex h-8.5 w-8.5 items-center justify-center rounded-md border border-[#E8EDF1] shadow-13 lg:hidden">
            <svg class="mx-0 mr-px" xmlns="http://www.w3.org/2000/svg" width="9" height="14" viewBox="0 0 9 14" fill="none">
                <path d="M1.25462 8.01802L5.8706 12.9787C6.21525 13.2869 6.67133 13.4549 7.14266 13.4471C7.614 13.4393 8.06375 13.2563 8.39709 12.9368C8.73042 12.6173 8.92127 12.1862 8.92942 11.7345C8.93756 11.2827 8.76235 10.8455 8.44074 10.5152L5.10982 6.78627L8.44074 3.48296C8.76235 3.1526 8.93755 2.71544 8.92941 2.26366C8.92127 1.81189 8.73042 1.38079 8.39709 1.06128C8.06375 0.741783 7.614 0.558846 7.14266 0.551041C6.67133 0.543237 6.21525 0.711175 5.8706 1.01944L1.25462 5.55451C0.914074 5.88133 0.722791 6.32436 0.722791 6.78627C0.722791 7.24818 0.914074 7.6912 1.25462 8.01802Z" fill="#FD7013"></path>
            </svg>
        </button>
    </div>
    <section class="max-lg:relative max-lg:-mx-4 max-lg:h-62 max-lg:rounded-b-3xl max-lg:border-b max-lg:border-b-[#5091FB] max-lg:bg-[#EDF2F5] mt-16 lg:mt-[0px]">
        <div class="lg:flex lg:items-center lg:justify-between lg:border-b lg:pb-7.5">
            <div class="lg:flex lg:items-center lg:gap-x-9">
                <div class="max-lg:absolute max-lg:left-1/2 max-lg:-translate-x-1/2 max-lg:-translate-y-1/2">
                    <?php echo get_avatar($data['ID'], 60, false, $data['name'], [
                        'class' => 'h-15.5 w-15.5 rounded-[10px] max-lg:h-20 max-lg:w-20 max-lg:border max-lg:border-2 max-lg:border-[#5091FB] max-lg:drop-shad',
                    ]); ?>
                </div>
                <div>
                    <h2 class="mb-2 text-2xl font-extrabold max-lg:text-center lg:flex lg:items-center">
                    <span class="max-lg:absolute max-lg:left-1/2 max-lg:top-18 max-lg:-translate-x-1/2">
                        <?php echo esc_html($data['name']); ?>
                    </span> <?php user_badge_by_level($profile_user_id, 'rounded-full px-1.5 py-0.5 text-2xs font-medium text-white max-lg:absolute max-lg:left-5 max-lg:top-5 lg:mx-4'); ?>
                    </h2>
                    <div class="text-md text-[#889BAD] max-lg:absolute max-lg:left-1/2 max-lg:top-30 max-lg:-translate-x-1/2 max-lg:text-center lg:hidden"><?php echo get_city_label_by_identifier($data['city']); ?> </div>
                    <div class="flex items-center">
                    <span class="hidden font-medium text-2xs max-lg:hidden">
                        <span class="ml-1.5 text-base"> <?php echo esc_html($data['played_count']); ?> </span> بازی های انجام داده </span>
                        <!--                        <span class="mx-3.5 h-2.5 border-l border-l-slate-110"></span>-->
                        <span class="text-2xs font-medium max-lg:absolute max-lg:right-5 max-lg:top-5 max-lg:rounded max-lg:bg-[#EFC101] max-lg:px-1.5">
                        <span class="max-lg:ml-2 lg:hidden">امتیاز</span>
                        <span class="ml-1.5 text-base max-lg:text-lg max-lg:font-extrabold"> <?php echo esc_html($data['points']); ?> </span>
                        <span class="max-lg:hidden">امتیاز در اسکیپ زوم</span>
                    </span>
                    </div>
                </div>
            </div>
            <?php if ((int) $profile_user_id !== get_current_user_id() && is_user_logged_in()) { ?>
                <div class="z-50 hidden invite-modal">
                    <div class="fixed top-0 right-0 z-40 w-full h-full bg-black/40"></div>
                    <div class="fixed bg-white w-[430px] max-w-[100%] h-auto right-[50%] translate-x-[50%] top-[50%] -translate-y-[50%] p-6 rounded-xl border z-50 flex flex-col gap-4">
                        <div class="relative">
                            <input id="select-game" class="text-gray-900 block w-full border-0 p-1.5 text-sm shadow-13 outline-none ring-1 ring-inset ring-gray-100 placeholder:text-right placeholder:text-slate-200 focus:shadow-none focus:ring-2 focus:ring-inset focus:ring-primary-500 h-16 py-2 px-6 rounded-2xl" placeholder="انتخاب بازی" type="text">
                            <div id="search-result" class="items-center gap-x-4 rounded-[10px] border border-[#E8EDF1] bg-white px-6 py-3 shadow-13 absolute top-[calc(100%+10px)] right-0 w-full hidden"></div>
                        </div>
                        <button type="button" id="invite-user" class="p-4 text-white bg-primaryColor rounded-2xl shadow-primary-3 shadow-13"> دعوت <?php echo esc_html($data['name']); ?> </button>
                    </div>
                </div>
            <?php } ?>
            <div class="flex items-center max-lg:w-full max-lg:justify-center gap-x-4 max-lg:absolute max-lg:bottom-8 max-lg:left-1/2 max-lg:-translate-x-1/2">
                <?php if ((int) $profile_user_id !== get_current_user_id() && is_user_logged_in()) { ?>
                    <button id="invite-modal-button" type="button" aria-haspopup="dialog" aria-expanded="false" aria-controls="radix-:r9:" data-state="closed">
                        <div class="flex h-14 items-center gap-x-4 rounded-lg border border-[#E8EDF1] bg-white px-4.5 shadow-13">
                            <span class="text-lg leading-none">دعوت این کاربر</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="20" viewBox="0 0 24 24" class="-mt-1 text-primary-500">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m19.749 13.854-2.42-.001m-11.09-.007H4.215m5.459.002 4.821.003"></path>
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.996 18.697c-.001 1.823 1.456 3.3 3.255 3.3L9.844 22c0-1.206.96-2.176 2.15-2.176a2.161 2.161 0 0 1 2.15 2.178h2.593c1.8.002 3.258-1.474 3.26-3.295l.007-13.402c0-1.821-1.456-3.3-3.255-3.3L14.07 2c0 1.206-.877 2.176-2.067 2.176a2.159 2.159 0 0 1-2.149-2.178l-2.592-.002c-1.8 0-3.258 1.474-3.26 3.296l-.007 13.404Z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </button>
                    <span class="help" data-help="امکان دعوت از سایر کاربران برای ‌هم‌بازی شدن (کامل کردن تیم)"></span>
                <?php } ?>
                <button type="button" data-title="
        <?= $profile_user->display_name ?>" data-content="
        <?= $data['played_count'] ?> بازی انجام شده" data-url="
        <?= $_SERVER['REQUEST_URI']; ?>" class="share">
                    <div class="flex h-14 items-center gap-x-4 rounded-lg border border-[#E8EDF1] bg-white px-4.5 shadow-13">
                        <span class="text-lg leading-none lg:hidden">اشتراک گذاری</span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" viewBox="0 0 24 24">
                            <path d="M4 4m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"></path>
                            <path d="M7 17l0 .01"></path>
                            <path d="M14 4m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"></path>
                            <path d="M7 7l0 .01"></path>
                            <path d="M4 14m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"></path>
                            <path d="M17 7l0 .01"></path>
                            <path d="M14 14l3 0"></path>
                            <path d="M20 14l0 .01"></path>
                            <path d="M14 14l0 3"></path>
                            <path d="M14 20l3 0"></path>
                            <path d="M17 17l3 0"></path>
                            <path d="M20 17l0 3"></path>
                        </svg>
                    </div>
                </button>
            </div>
        </div>
    </section>
    <div class="grid-cols-12 pb-4 max-md:mt-6 lg:mt-10 lg:grid lg:gap-10">
        <div class="flex flex-col col-span-9 gap-10 max-3xl:col-span-8 max-2xl:col-span-7">
            <?php if ($all_products) { ?>
                <div>
                    <div class="mb-6 md:mb-8">
                        <div class="flex justify-between">
                            <div class="items-center gap-6 md:flex">
                                <h2 class="flex items-center gap-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="colorCurrent" width="20" height="20" viewBox="0 0 20 20" class="w-5 -mt-2 fill-primary-500">
                                        <path d="M15.3399 5.92242L14.9827 8.02112C14.8933 8.54654 14.4071 8.90919 13.878 8.84514C13.3116 8.77658 12.9176 8.24837 13.0133 7.68595L13.3544 5.68206C13.5485 4.07877 12.4063 2.69649 10.7186 2.49218C9.03094 2.28788 7.59195 3.3577 7.39786 4.96099L7.32633 5.43593C7.2458 5.97066 6.75461 6.34438 6.21777 6.2794C5.65844 6.21169 5.26556 5.69532 5.34947 5.13819L5.41236 4.72063C5.73203 2.07991 8.16726 0.269453 10.947 0.605953C13.7267 0.942453 15.6596 3.2817 15.3399 5.92242Z" fill="colorCurrent"></path>
                                        <path d="M2 10.5004C2 8.88539 3.3 7.65039 5 7.65039H15C16.7 7.65039 18 8.88539 18 10.5004V17.1504C18 18.7654 16.7 20.0004 15 20.0004H5C3.3 20.0004 2 18.7654 2 17.1504V10.5004Z" fill="colorCurrent"></path>
                                        <path d="M10 16.2031C10.6 16.2031 11 15.8231 11 15.2531V12.4031C11 11.8331 10.6 11.4531 10 11.4531C9.4 11.4531 9 11.8331 9 12.4031V15.2531C9 15.8231 9.4 16.2031 10 16.2031Z" fill="white"></path>
                                    </svg>
                                    <span class="text-base font-bold md:text-lg">آخرین بازی‌های انجام شده</span>
                                </h2>
                                <div class="hidden md:block"></div>
                            </div>
                            <div class="flex items-center gap-6">
                                <div class="hidden md:block"></div>
                            </div>
                        </div>
                    </div>
                    <div class="relative w-full max-sm:max-w-[calc(100%+2rem)] max-sm:w-[calc(100%+2rem)] max-sm:-mr-4" dir="rtl">
                        <section class="relative max-w-full py-4 md:py-5 lg:py-9 max-md:px-4">
                            <div class="relative overflow-hidden embla_normal">
                                <div class="embla__viewport">
                                    <div id="trends-rooms-slider" class="embla__container first:child:before:hidden child:before:w-px child:before:absolute child:before:bg-gradient-to-t child:before:from-white child:before:via-slate-110 child:before:to-white child:before:-right-3.5 child:lg:before:-right-6 child:before:h-full min-h-[200px] child:box-content lg:min-h-[300px] flex child:ml-7 md:child:ml-12  last:child:ml-0 child:relative child:shrink-0 child:grow-0 child:w-[156px] md:child:w-[200px] child:py-2.5"> <?php echo $data['recent_played']; ?> </div>
                                </div>
                                <button class="embla__button embla__button--prev trends-rooms-btn absolute right-0 top-1/2 translate-y-[-115px] rotate-180 z-50 cursor-pointer touch-manipulation appearance-none -mr-px hidden" type="button">
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
                                <button class="embla__button embla__button--next trends-rooms-btn absolute left-0 top-1/2 translate-y-[-115px] z-50 cursor-pointer touch-manipulation appearance-none -ml-px hidden" type="button">
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
                    </div>
                </div>
            <?php } ?>
            <?php foreach ($data['collections'] as $collection) { ?>
                <div>
                    <div class="mb-6 md:mb-8">
                        <div class="flex justify-between">
                            <div class="flex items-center justify-between w-full gap-6 md:flex">
                                <h2 class="flex items-center gap-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="21" viewBox="0 0 20 21" fill="none" class="mx-0">
                                        <path d="M8.33354 16.3943L5.93224 18.2373C5.76098 18.3758 5.55643 18.4635 5.34058 18.4909C5.12472 18.5183 4.9057 18.4845 4.70705 18.393C4.5084 18.3016 4.33762 18.1559 4.21305 17.9718C4.08848 17.7876 4.01482 17.5718 4 17.3476V7.72268C4.01246 7.36802 4.09243 7.01938 4.23535 6.69666C4.37827 6.37395 4.58133 6.08349 4.83293 5.84188C5.08454 5.60027 5.37975 5.41225 5.70171 5.28855C6.02367 5.16485 6.36607 5.10791 6.70933 5.12097H11.4419C12.1349 5.09556 12.8094 5.35522 13.3173 5.84298C13.8253 6.33073 14.1252 7.00674 14.1513 7.72268V17.3491C14.1368 17.5734 14.0633 17.7894 13.9388 17.9738C13.8143 18.1582 13.6434 18.304 13.4446 18.3955C13.2458 18.4869 13.0266 18.5206 12.8106 18.4929C12.5946 18.4651 12.3901 18.377 12.219 18.238L9.81772 16.395C9.60216 16.2328 9.34233 16.1453 9.07563 16.1453C8.80892 16.1453 8.54909 16.232 8.33354 16.3943Z" fill="#FD7013" stroke="#FD7013" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M7.19922 3.25493C7.45845 3.00256 7.76261 2.80616 8.09432 2.67695C8.42604 2.54775 8.77881 2.48827 9.13247 2.50191H14.0085C14.7225 2.47537 15.4174 2.74659 15.9407 3.25607C16.464 3.76555 16.773 4.47167 16.7999 5.21949V10.2471V15.2746C16.785 15.509 16.7093 15.7346 16.581 15.9272C16.4527 16.1198 16.2766 16.2721 16.0718 16.3676" stroke="#FD7013" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span class="text-base font-bold md:text-lg"> <?php echo esc_html($collection['title']); ?> </span>
                                </h2>
                                <div class="flex items-center gap-4 like-dislike-button"> <?php if (count($collection['users']) > 0) {
                                        echo count($collection['users']);
                                        echo (count($collection['users']) > 1) ? ' نفر پسندیدند' : ' نفر پسندید';
                                    } ?> <?php if ($collection['liked']) { ?> <button type="button" data-collection="
									<?php echo $collection['id']; ?>" data-action="like" class="flex items-center px-4 py-1 border gap-x-2 rounded-xl shadow-12 bg-slate-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15" fill="none">
                                            <path d="M13.6837 2.0689C14.4962 2.88129 14.9672 3.9737 15.0001 5.12221C15.033 6.27072 14.6254 7.38831 13.8607 8.2459L7.50073 14.6149L1.14223 8.2459C0.376673 7.38786 -0.0313664 6.26931 0.00188346 5.11988C0.0351333 3.97045 0.50715 2.87736 1.32103 2.06502C2.13491 1.25268 3.22889 0.782725 4.37838 0.751646C5.52787 0.720567 6.64565 1.13072 7.50223 1.8979C8.35932 1.13114 9.47744 0.721615 10.627 0.753416C11.7766 0.785216 12.8703 1.25593 13.6837 2.0689Z" fill="#F21543" />
                                        </svg> می پسندم </button> <?php } else { ?> <button type="button" data-collection="
									<?php echo $collection['id']; ?>" data-action="like" class="flex items-center px-4 py-1 border gap-x-2 rounded-xl shadow-12 bg-slate-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="15" viewBox="0 0 16 15" fill="none">
                                            <path d="M7.50186 2.45658L8.00195 2.90447L8.50229 2.45686C9.21653 1.8179 10.1483 1.47663 11.1063 1.50313C12.0642 1.52963 12.9756 1.92185 13.6534 2.59926C14.3305 3.27626 14.723 4.1866 14.7504 5.14369C14.7777 6.09404 14.4429 7.01899 13.8144 7.73163L8.0008 13.5534L2.18854 7.73156C1.55916 7.01853 1.22406 6.09274 1.25157 5.14157C1.27928 4.18371 1.67263 3.2728 2.35086 2.59585C3.02909 1.9189 3.94074 1.52727 4.89865 1.50137C5.85656 1.47547 6.78804 1.81727 7.50186 2.45658Z" stroke="#889BAD" stroke-width="1.5" />
                                        </svg> پسندیدن </button> <?php } ?> </div>
                            </div>
                            <div class="flex items-center gap-6">
                                <div class="hidden md:block"></div>
                            </div>
                        </div>
                    </div>
                    <div class="relative w-full max-sm:max-w-[calc(100%+2rem)] max-sm:w-[calc(100%+2rem)] max-sm:-mr-4" dir="rtl">
                        <div class="relative overflow-hidden embla_normal">
                            <div class="embla__viewport">
                                <div id="trends-rooms-slider" class="embla__container first:child:before:hidden child:before:w-px child:before:absolute child:before:bg-gradient-to-t child:before:from-white child:before:via-slate-110 child:before:to-white child:before:-right-3.5 child:lg:before:-right-6 child:before:h-full min-h-[200px] child:box-content lg:min-h-[300px] flex child:ml-7 md:child:ml-12 child:relative last:child:ml-0 child:shrink-0 child:grow-0 child:w-[156px] md:child:w-[200px] child:py-2.5"> <?php echo $collection['items']; ?> </div>
                            </div>
                            <button class="embla__button embla__button--prev trends-rooms-btn absolute right-0 top-1/2 translate-y-[-115px] rotate-180 z-50 cursor-pointer touch-manipulation appearance-none -mr-px hidden" type="button">
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
                            <button class="embla__button embla__button--next trends-rooms-btn absolute left-0 top-1/2 translate-y-[-115px] z-50 cursor-pointer touch-manipulation appearance-none -ml-px hidden" type="button">
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
                    </div>
                </div>
            <?php } ?>
        </div>
        <div id="ez-bio" class="col-span-3 max-3xl:col-span-4 max-2xl:col-span-5">
            <div class="my-6 extra-line md:hidden"></div>

            <?php if ($data['bio'] !== ''): ?>
                <div class="md:mb-10 md:rounded-3xl md:bg-slate-60 md:px-7 md:py-8">
                    <h4 class="flex items-center justify-between mb-4 text-base">
                        درمورد
                        <?php echo esc_html($data['name']); ?>
                        <span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="18" viewBox="0 0 24 24">
                            <circle cx="11.579" cy="7.278" r="4.778" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></circle>
                            <path clip-rule="evenodd" d="M4 18.701a2.215 2.215 0 01.22-.97c.457-.915 1.748-1.4 2.819-1.62a16.778 16.778 0 012.343-.33 25.04 25.04 0 014.385 0c.787.056 1.57.166 2.343.33 1.07.22 2.361.659 2.82 1.62a2.27 2.27 0 010 1.95c-.459.96-1.75 1.4-2.82 1.61-.772.172-1.555.286-2.343.34-1.188.1-2.38.118-3.57.054-.275 0-.54 0-.815-.055a15.417 15.417 0 01-2.334-.338c-1.08-.21-2.361-.65-2.828-1.611A2.28 2.28 0 014 18.7z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </span>
                    </h4>
                    <p class="leading-9 text-2xs"><?php echo $data['bio']; ?></p>
                </div>
            <?php endif; ?>
            <?php if ((!empty($owner_feedback)) && ($user_roles[0] === 'compiler' || $user_roles[0] === 'administrator')): ?>
            <div class="flex flex-col">
                <div class="flex flex-col w-full">
                    <span class="text-lg font-bold">
                        دیدگاه مجموعه داران در مورد <?php echo esc_html($data['name']) ?>
                    </span>
                    <div class="flex items-center justify-between mt-7.5 gap-x-4 rounded-xl border border-[#E8EDF1] bg-white p-4.5 shadow-13">

                        <div class="flex items-center transition-all duration-300 text-text-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="75" height="80" viewBox="0 0 75 80" fill="none" class="ml-3 -mb-8 -mr-6">
                                <g filter="url(#filter0_d_10452_1709)">
                                    <mask id="path-1-inside-1_10452_1709" fill="white">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M46.4561 34.4115C46.4378 34.1598 46.5162 33.9112 46.6703 33.7114C49.3883 30.1873 51 25.7999 51 21.0442C51 9.42179 41.3741 0 29.5 0C17.6259 0 8 9.42179 8 21.0442C8 32.6665 17.6259 42.0883 29.5 42.0883C31.568 42.0883 33.5679 41.8025 35.4605 41.2691C35.6996 41.2018 35.9552 41.2202 36.1795 41.327L45.7942 45.9019C46.4866 46.2314 47.2769 45.6911 47.2213 44.9264L46.4561 34.4115Z" />
                                    </mask>
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M46.4561 34.4115C46.4378 34.1598 46.5162 33.9112 46.6703 33.7114C49.3883 30.1873 51 25.7999 51 21.0442C51 9.42179 41.3741 0 29.5 0C17.6259 0 8 9.42179 8 21.0442C8 32.6665 17.6259 42.0883 29.5 42.0883C31.568 42.0883 33.5679 41.8025 35.4605 41.2691C35.6996 41.2018 35.9552 41.2202 36.1795 41.327L45.7942 45.9019C46.4866 46.2314 47.2769 45.6911 47.2213 44.9264L46.4561 34.4115Z" fill="white" />
                                    <path d="M35.4605 41.2691L35.3249 40.7879L35.4605 41.2691ZM36.1795 41.327L36.3943 40.8755L36.1795 41.327ZM45.7942 45.9019L45.5794 46.3534L45.7942 45.9019ZM47.2213 44.9264L47.7199 44.8901L47.2213 44.9264ZM46.6703 33.7114L47.0662 34.0167L46.6703 33.7114ZM46.4561 34.4115L46.9548 34.3752L46.4561 34.4115ZM50.5 21.0442C50.5 25.6848 48.9278 29.9656 46.2743 33.406L47.0662 34.0167C49.8487 30.4089 51.5 25.9151 51.5 21.0442H50.5ZM29.5 0.5C41.1081 0.5 50.5 9.70798 50.5 21.0442H51.5C51.5 9.13561 41.6401 -0.5 29.5 -0.5V0.5ZM8.5 21.0442C8.5 9.70798 17.8919 0.5 29.5 0.5V-0.5C17.3599 -0.5 7.5 9.13561 7.5 21.0442H8.5ZM29.5 41.5883C17.8919 41.5883 8.5 32.3804 8.5 21.0442H7.5C7.5 32.9527 17.3599 42.5883 29.5 42.5883V41.5883ZM35.3249 40.7879C33.476 41.309 31.5218 41.5883 29.5 41.5883V42.5883C31.6143 42.5883 33.6597 42.2961 35.5961 41.7504L35.3249 40.7879ZM46.0091 45.4504L36.3943 40.8755L35.9647 41.7785L45.5794 46.3534L46.0091 45.4504ZM45.9575 34.4478L46.7226 44.9627L47.7199 44.8901L46.9548 34.3752L45.9575 34.4478ZM35.5961 41.7504C35.7232 41.7146 35.854 41.7258 35.9647 41.7785L36.3943 40.8755C36.0565 40.7147 35.676 40.6889 35.3249 40.7879L35.5961 41.7504ZM45.5794 46.3534C46.618 46.8476 47.8034 46.0372 47.7199 44.8901L46.7226 44.9627C46.7504 45.345 46.3553 45.6152 46.0091 45.4504L45.5794 46.3534ZM46.2743 33.406C46.0481 33.6993 45.9299 34.0686 45.9575 34.4478L46.9548 34.3752C46.9458 34.251 46.9842 34.1231 47.0662 34.0167L46.2743 33.406Z" fill="#02C96F" mask="url(#path-1-inside-1_10452_1709)" />
                                </g>
                                <path d="M21 17.234L21.747 17.17C21.7298 16.9779 21.6392 16.7998 21.4942 16.6728C21.3491 16.5457 21.1606 16.4794 20.968 16.4876C20.7753 16.4959 20.5932 16.578 20.4595 16.717C20.3258 16.856 20.2508 17.0411 20.25 17.234L21 17.234ZM38.236 19.057L37.53 23.137L39.009 23.393L39.714 19.313L38.236 19.057ZM31.245 28.25L26.596 28.25L26.596 29.75L31.245 29.75L31.245 28.25ZM25.685 27.412L24.873 18.02L23.378 18.149L24.191 27.542L25.685 27.412ZM37.53 23.137C37.023 26.067 34.381 28.25 31.245 28.25L31.245 29.75C35.071 29.75 38.371 27.081 39.009 23.393L37.53 23.137ZM31.255 12.1L30.592 16.145L32.072 16.387L32.735 12.343L31.255 12.1ZM25.188 17.246L26.627 16.006L25.647 14.869L24.208 16.109L25.188 17.246ZM29.244 11.972L29.72 10.138L28.268 9.762L27.792 11.595L29.244 11.972ZM30.438 9.778L30.583 9.825L31.042 8.397L30.897 8.35L30.438 9.778ZM28.523 13.816C28.835 13.232 29.078 12.613 29.244 11.972L27.792 11.595C27.6542 12.1213 27.4555 12.6297 27.2 13.11L28.523 13.816ZM30.583 9.825C30.7255 9.86866 30.8547 9.94738 30.9588 10.054C31.063 10.1605 31.1387 10.2916 31.179 10.435L32.631 10.059C32.5281 9.6703 32.3284 9.31401 32.0505 9.02337C31.7727 8.73274 31.4257 8.51724 31.042 8.397L30.583 9.825ZM29.72 10.138C29.7396 10.0658 29.7749 9.99885 29.8233 9.94183C29.8716 9.8848 29.932 9.83911 30 9.808L29.349 8.457C29.0847 8.58269 28.851 8.7646 28.6643 8.99001C28.4775 9.21542 28.3423 9.47889 28.268 9.762L29.72 10.138ZM30 9.808C30.137 9.74303 30.2935 9.73231 30.438 9.778L30.897 8.35C30.3863 8.18673 29.8324 8.22501 29.349 8.457L30 9.808ZM32.154 17.984L37.334 17.984L37.334 16.484L32.154 16.484L32.154 17.984ZM22.719 28.406L21.747 17.17L20.253 17.299L21.223 28.535L22.719 28.406ZM21.75 28.513L21.75 17.234L20.25 17.234L20.25 28.513L21.75 28.513ZM21.223 28.535C21.2199 28.4987 21.2255 28.4621 21.2373 28.4276C21.2492 28.3931 21.2681 28.3614 21.2928 28.3346C21.3175 28.3078 21.3476 28.2865 21.381 28.2719C21.4144 28.2573 21.4505 28.2499 21.487 28.25L21.487 29.75C22.213 29.75 22.781 29.128 22.719 28.406L21.223 28.535ZM32.735 12.343C32.86 11.583 32.825 10.805 32.631 10.059L31.179 10.436C31.319 10.979 31.346 11.546 31.255 12.1L32.735 12.343ZM26.596 28.25C26.3669 28.2496 26.1462 28.1633 25.9775 28.0082C25.8089 27.853 25.7045 27.6403 25.685 27.412L24.191 27.542C24.243 28.1442 24.5189 28.7049 24.9641 29.1137C25.4093 29.5225 25.9916 29.7495 26.596 29.75L26.596 28.25ZM26.627 16.006C27.307 15.42 28.039 14.723 28.524 13.816L27.2 13.109C26.854 13.758 26.303 14.305 25.647 14.869L26.627 16.006ZM39.714 19.313C39.7742 18.966 39.7578 18.61 39.666 18.27C39.5741 17.93 39.4091 17.6142 39.1823 17.3447C38.9556 17.0751 38.6727 16.8585 38.3534 16.7098C38.0341 16.5611 37.6862 16.4841 37.334 16.484L37.334 17.984C37.4675 17.9841 37.5995 18.0133 37.7205 18.0697C37.8415 18.1262 37.9488 18.2084 38.0347 18.3106C38.1207 18.4128 38.1832 18.5326 38.218 18.6615C38.2527 18.7904 38.2589 18.9254 38.236 19.057L39.714 19.313ZM21.487 28.25C21.5568 28.25 21.6236 28.2777 21.673 28.327C21.7223 28.3764 21.75 28.4432 21.75 28.513L20.25 28.513C20.25 29.195 20.803 29.75 21.487 29.75L21.487 28.25ZM30.592 16.145C30.5547 16.3716 30.5672 16.6036 30.6285 16.8248C30.6899 17.0461 30.7986 17.2514 30.9472 17.4265C31.0958 17.6015 31.2807 17.7421 31.4891 17.8386C31.6975 17.935 31.9244 17.984 32.154 17.984L32.154 16.484C32.104 16.484 32.064 16.439 32.072 16.387L30.592 16.145ZM24.873 18.02C24.8608 17.8752 24.8821 17.7296 24.937 17.595C24.9919 17.4605 25.0779 17.3409 25.188 17.246L24.208 16.109C23.9181 16.3591 23.6918 16.6745 23.5475 17.0291C23.4032 17.3837 23.3451 17.7676 23.378 18.149L24.873 18.02Z" fill="#02C96F" />
                                <defs>
                                    <filter id="filter0_d_10452_1709" x="0" y="0" width="75" height="80" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                        <feFlood flood-opacity="0" result="BackgroundImageFix" />
                                        <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                        <feOffset dx="8" dy="18" />
                                        <feGaussianBlur stdDeviation="8" />
                                        <feComposite in2="hardAlpha" operator="out" />
                                        <feColorMatrix type="matrix" values="0 0 0 0 0.306354 0 0 0 0 0.36728 0 0 0 0 0.425 0 0 0 0.08 0" />
                                        <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_10452_1709" />
                                        <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_10452_1709" result="shape" />
                                    </filter>
                                </defs>
                            </svg>
                            راضی
                            <strong class="mr-1 text-lg font-bold text-textColor"><?= intval($liked) ?></strong>
                        </div>

                        <div class="flex items-center transition-all duration-300 text-text-3">
                            ناراضی
                            <strong class="ml-3 mr-1 text-lg font-bold text-textColor"><?= intval($disliked) ?> </strong>
                            <svg xmlns="http://www.w3.org/2000/svg" width="75" height="80" viewBox="0 0 75 80" fill="none" class="-mb-8 -mr-6">
                                <g filter="url(#filter0_d_10452_1700)">
                                    <mask id="path-1-inside-1_10452_1700" fill="white">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M46.4567 34.3974C46.4384 34.1455 46.5168 33.8966 46.6712 33.6967C49.3886 30.1773 51 25.796 51 21.0468C51 9.43915 41.3741 0.0292969 29.5 0.0292969C17.6259 0.0292969 8 9.43915 8 21.0468C8 32.6544 17.6259 42.0643 29.5 42.0643C31.5687 42.0643 33.5692 41.7787 35.4625 41.2456C35.7013 41.1783 35.9567 41.1968 36.1808 41.3033L45.7951 45.8722C46.4875 46.2013 47.2774 45.6609 47.2217 44.8964L46.4567 34.3974Z" />
                                    </mask>
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M46.4567 34.3974C46.4384 34.1455 46.5168 33.8966 46.6712 33.6967C49.3886 30.1773 51 25.796 51 21.0468C51 9.43915 41.3741 0.0292969 29.5 0.0292969C17.6259 0.0292969 8 9.43915 8 21.0468C8 32.6544 17.6259 42.0643 29.5 42.0643C31.5687 42.0643 33.5692 41.7787 35.4625 41.2456C35.7013 41.1783 35.9567 41.1968 36.1808 41.3033L45.7951 45.8722C46.4875 46.2013 47.2774 45.6609 47.2217 44.8964L46.4567 34.3974Z" fill="white" />
                                    <path d="M35.4625 41.2456L35.598 41.7268L35.4625 41.2456ZM36.1808 41.3033L35.9662 41.7549L36.1808 41.3033ZM47.2217 44.8964L46.723 44.9327L47.2217 44.8964ZM46.6712 33.6967L46.2754 33.3912L46.6712 33.6967ZM46.4567 34.3974L46.9554 34.3611L46.4567 34.3974ZM50.5 21.0468C50.5 25.6807 48.9283 29.9554 46.2754 33.3912L47.0669 34.0023C49.849 30.3992 51.5 25.9114 51.5 21.0468H50.5ZM29.5 0.529297C41.1087 0.529297 50.5 9.72593 50.5 21.0468H51.5C51.5 9.15237 41.6395 -0.470703 29.5 -0.470703V0.529297ZM8.5 21.0468C8.5 9.72593 17.8913 0.529297 29.5 0.529297V-0.470703C17.3605 -0.470703 7.5 9.15237 7.5 21.0468H8.5ZM29.5 41.5643C17.8913 41.5643 8.5 32.3677 8.5 21.0468H7.5C7.5 32.9412 17.3605 42.5643 29.5 42.5643V41.5643ZM35.327 40.7643C33.4775 41.2851 31.5225 41.5643 29.5 41.5643V42.5643C31.6149 42.5643 33.661 42.2723 35.598 41.7268L35.327 40.7643ZM46.0097 45.4206L36.3954 40.8517L35.9662 41.7549L45.5805 46.3238L46.0097 45.4206ZM45.958 34.4337L46.723 44.9327L47.7203 44.86L46.9554 34.3611L45.958 34.4337ZM35.598 41.7268C35.7249 41.6911 35.8555 41.7023 35.9662 41.7549L36.3954 40.8517C36.0579 40.6912 35.6777 40.6655 35.327 40.7643L35.598 41.7268ZM45.5805 46.3238C46.619 46.8174 47.8039 46.0069 47.7203 44.86L46.723 44.9327C46.7508 45.315 46.3559 45.5852 46.0097 45.4206L45.5805 46.3238ZM46.2754 33.3912C46.0489 33.6846 45.9304 34.0543 45.958 34.4337L46.9554 34.3611C46.9463 34.2368 46.9848 34.1087 47.0669 34.0023L46.2754 33.3912Z" fill="#F21543" mask="url(#path-1-inside-1_10452_1700)" />
                                </g>
                                <path d="M38 23.7953L37.253 23.8593C37.2702 24.0514 37.3608 24.2295 37.5058 24.3565C37.6509 24.4836 37.8394 24.5499 38.032 24.5417C38.2247 24.5334 38.4068 24.4513 38.5405 24.3123C38.6742 24.1733 38.7492 23.9882 38.75 23.7953L38 23.7953ZM20.764 21.9723L21.47 17.8923L19.991 17.6363L19.286 21.7163L20.764 21.9723ZM27.755 12.7793L32.404 12.7793L32.404 11.2793L27.755 11.2793L27.755 12.7793ZM33.315 13.6173L34.127 23.0093L35.622 22.8803L34.809 13.4873L33.315 13.6173ZM21.47 17.8923C21.977 14.9623 24.619 12.7793 27.755 12.7793L27.755 11.2793C23.929 11.2793 20.629 13.9483 19.991 17.6363L21.47 17.8923ZM27.745 28.9293L28.408 24.8843L26.928 24.6423L26.265 28.6863L27.745 28.9293ZM33.812 23.7833L32.373 25.0233L33.353 26.1603L34.792 24.9203L33.812 23.7833ZM29.756 29.0573L29.28 30.8913L30.732 31.2673L31.208 29.4343L29.756 29.0573ZM28.562 31.2513L28.417 31.2043L27.958 32.6323L28.103 32.6793L28.562 31.2513ZM30.477 27.2133C30.165 27.7973 29.922 28.4163 29.756 29.0573L31.208 29.4343C31.3458 28.908 31.5445 28.3996 31.8 27.9193L30.477 27.2133ZM28.417 31.2043C28.2745 31.1606 28.1453 31.0819 28.0412 30.9753C27.937 30.8688 27.8613 30.7377 27.821 30.5943L26.369 30.9703C26.4719 31.359 26.6716 31.7153 26.9495 32.0059C27.2273 32.2966 27.5743 32.5121 27.958 32.6323L28.417 31.2043ZM29.28 30.8913C29.2604 30.9635 29.2251 31.0304 29.1767 31.0875C29.1284 31.1445 29.068 31.1902 29 31.2213L29.651 32.5723C29.9153 32.4466 30.149 32.2647 30.3357 32.0393C30.5225 31.8139 30.6577 31.5504 30.732 31.2673L29.28 30.8913ZM29 31.2213C28.863 31.2863 28.7065 31.297 28.562 31.2513L28.103 32.6793C28.6137 32.8426 29.1676 32.8043 29.651 32.5723L29 31.2213ZM26.846 23.0453L21.666 23.0453L21.666 24.5453L26.846 24.5453L26.846 23.0453ZM36.281 12.6233L37.253 23.8593L38.747 23.7303L37.777 12.4943L36.281 12.6233ZM37.25 12.5163L37.25 23.7953L38.75 23.7953L38.75 12.5163L37.25 12.5163ZM37.777 12.4943C37.7801 12.5306 37.7745 12.5672 37.7627 12.6017C37.7508 12.6362 37.7319 12.6679 37.7072 12.6947C37.6825 12.7215 37.6524 12.7428 37.619 12.7574C37.5856 12.772 37.5495 12.7794 37.513 12.7793L37.513 11.2793C36.787 11.2793 36.219 11.9013 36.281 12.6233L37.777 12.4943ZM26.265 28.6863C26.14 29.4463 26.175 30.2243 26.369 30.9703L27.821 30.5933C27.681 30.0503 27.654 29.4833 27.745 28.9293L26.265 28.6863ZM32.404 12.7793C32.6331 12.7797 32.8538 12.866 33.0225 13.0211C33.1911 13.1763 33.2955 13.389 33.315 13.6173L34.809 13.4873C34.757 12.8851 34.4811 12.3243 34.0359 11.9156C33.5907 11.5068 33.0084 11.2798 32.404 11.2793L32.404 12.7793ZM32.373 25.0233C31.693 25.6093 30.961 26.3063 30.476 27.2133L31.8 27.9203C32.146 27.2713 32.697 26.7243 33.353 26.1603L32.373 25.0233ZM19.286 21.7163C19.2258 22.0633 19.2422 22.4193 19.334 22.7593C19.4259 23.0993 19.5909 23.4151 19.8177 23.6846C20.0444 23.9542 20.3273 24.1708 20.6466 24.3195C20.9659 24.4682 21.3138 24.5452 21.666 24.5453L21.666 23.0453C21.5325 23.0452 21.4005 23.016 21.2795 22.9596C21.1585 22.9031 21.0512 22.8209 20.9653 22.7187C20.8793 22.6165 20.8168 22.4967 20.782 22.3678C20.7473 22.2388 20.7411 22.1039 20.764 21.9723L19.286 21.7163ZM37.513 12.7793C37.4432 12.7793 37.3764 12.7516 37.327 12.7023C37.2777 12.6529 37.25 12.586 37.25 12.5163L38.75 12.5163C38.75 11.8343 38.197 11.2793 37.513 11.2793L37.513 12.7793ZM28.408 24.8843C28.4453 24.6577 28.4328 24.4257 28.3715 24.2045C28.3101 23.9832 28.2014 23.7779 28.0528 23.6028C27.9042 23.4278 27.7193 23.2871 27.5109 23.1907C27.3025 23.0943 27.0756 23.0453 26.846 23.0453L26.846 24.5453C26.896 24.5453 26.936 24.5903 26.928 24.6423L28.408 24.8843ZM34.127 23.0093C34.1392 23.1541 34.1179 23.2997 34.063 23.4343C34.0081 23.5688 33.9221 23.6884 33.812 23.7833L34.792 24.9203C35.0819 24.6702 35.3082 24.3548 35.4525 24.0002C35.5968 23.6456 35.6549 23.2617 35.622 22.8803L34.127 23.0093Z" fill="#F21543" />
                                <defs>
                                    <filter id="filter0_d_10452_1700" x="0" y="0.0292969" width="75" height="79.9414" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                        <feFlood flood-opacity="0" result="BackgroundImageFix" />
                                        <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                        <feOffset dx="8" dy="18" />
                                        <feGaussianBlur stdDeviation="8" />
                                        <feComposite in2="hardAlpha" operator="out" />
                                        <feColorMatrix type="matrix" values="0 0 0 0 0.306354 0 0 0 0 0.36728 0 0 0 0 0.425 0 0 0 0.08 0" />
                                        <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_10452_1700" />
                                        <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_10452_1700" result="shape" />
                                    </filter>
                                </defs>
                            </svg>

                        </div>

                    </div>
                </div>
                <div class="p-4 mt-8 rounded-md mb-14">
                    <div class="relative overflow-hidden embla_normal">
                        <div class="embla__viewport">
                            <div class="flex embla__container">
                                <?php foreach ($owner_feedback as $feedback): ?>
                                    <?php
                                    $product_title = get_the_title($feedback['room_id']);
                                    $product_url = get_the_permalink($feedback['room_id']);
                                    ?>
                                    <div class="w-full embla__slide shrink-0 grow-0 gap-x-4">
                                        <a href="<?= $product_url ?>" class="text-lg font-bold text-[#09192D] flex shrink-0 after:content-[''] after:w-full after:bg-[#D5DCE1] after:h-2 after:rounded gap-x-6 items-center after:-mt-2"><?= $product_title ?></a>
                                        <p class="text-sm text-[#889BAD] font-bold mt-8"><?= $feedback['owner_comment'] ?></p>

                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif ?>

                <div class="flex items-start gap-6 max-md:flex-col md:gap-8">
                    <div class="min-w-29 max-md:w-full">
                        <div class="max-md:flex max-md:items-center max-md:justify-between max-md:border-b max-md:border-slate-120 max-md:py-2 md:mb-5">
                            <span class="text-2xs text-slate-330">تاریخ عضویت</span>
                            <div class="leading-4 md:mt-2"><?php echo esc_html(jdate('j F Y', $data['register_date'])); ?></div>
                        </div>
                        <div class="max-md:flex max-md:items-center max-md:justify-between max-md:border-b max-md:border-slate-120 max-md:py-2 md:mb-5">
                            <span class="text-2xs text-slate-330">تعداد بازی های انجام شده</span>
                            <div class="leading-4 md:mt-2"><?php echo intval($data['played_count']) ?> بار</div>
                        </div>
                        <div class="max-md:flex max-md:items-center max-md:justify-between max-md:border-b max-md:border-slate-120 max-md:py-2 md:mb-5">
                            <span class="text-2xs text-slate-330">امتیاز در اسکیپ زوم</span>
                            <div class="leading-4 md:mt-2"><?php echo esc_html($data['points']); ?> امتیاز</div>
                        </div>
                    </div>
                    <?php if (! empty($data['recent_comment'])): ?>
                        <div>
                            <div class="mb-3">
                                <div class="flex items-center justify-between gap-2.5">
                                <span class="text-2xs text-slate-330">
                                    آخرین دیدگاه
                                    <?php echo esc_html($data['name']); ?>
                                </span>
                                    <div class="md:hidden">
                                        <div class="flex items-center gap-2.5 text-xs">
                                            <a href="<?php echo get_the_permalink($data['recent_comment'][0]->comment_post_ID); ?>" title="<?php echo get_the_title($data['recent_comment'][0]->comment_post_ID); ?>">
                                                <?php echo get_the_title($data['recent_comment'][0]->comment_post_ID); ?>
                                            </a>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="16" viewBox="0 0 24 24" class="mx-0">
                                                <path fill="currentColor" fill-rule="evenodd" d="M7.92 2h8.17C19.62 2 22 4.271 22 7.66v8.67c0 3.39-2.38 5.67-5.91 5.67H7.92C4.38 22 2 19.72 2 16.33V7.66C2 4.271 4.38 2 7.92 2Zm1.81 10.75h6.35c.42 0 .75-.34.75-.75 0-.42-.33-.75-.75-.75H9.73l2.48-2.47c.14-.14.22-.34.22-.53 0-.189-.08-.38-.22-.53a.754.754 0 0 0-1.06 0l-3.77 3.75c-.28.28-.28.78 0 1.06l3.77 3.75c.29.29.77.29 1.06 0 .29-.3.29-.77 0-1.07l-2.48-2.46Z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2 leading-7 text-2xs"><?php echo $data['recent_comment'][0]->comment_content ?></div>
                            </div>
                            <div class="max-md:hidden">
                                <div class="flex items-center gap-2.5 text-xs">
                                    <a href="<?php echo get_the_permalink($data['recent_comment'][0]->comment_post_ID); ?>" title="<?php echo get_the_title($data['recent_comment'][0]->comment_post_ID); ?>">
                                        <?php echo get_the_title($data['recent_comment'][0]->comment_post_ID); ?>
                                    </a>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="16" viewBox="0 0 24 24" class="mx-0">
                                        <path fill="currentColor" fill-rule="evenodd" d="M7.92 2h8.17C19.62 2 22 4.271 22 7.66v8.67c0 3.39-2.38 5.67-5.91 5.67H7.92C4.38 22 2 19.72 2 16.33V7.66C2 4.271 4.38 2 7.92 2Zm1.81 10.75h6.35c.42 0 .75-.34.75-.75 0-.42-.33-.75-.75-.75H9.73l2.48-2.47c.14-.14.22-.34.22-.53 0-.189-.08-.38-.22-.53a.754.754 0 0 0-1.06 0l-3.77 3.75c-.28.28-.28.78 0 1.06l3.77 3.75c.29.29.77.29 1.06 0 .29-.3.29-.77 0-1.07l-2.48-2.46Z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>

            </div>

        </div>

        <div class="absolute -mt-px top-full z-1 max-2xl:left-0 2xl:-right-12">
            <svg xmlns="http://www.w3.org/2000/svg" width="243" height="82" fill="none" viewBox="0 0 243 82" class="hidden ez-footer-logo 2xl:block">
                <path fill="#445769" fill-rule="evenodd" d="M0 1.167 242.483-.5c-73.042 0-104.352 81.874-138.398 81.874C66.922 81.374 59.345 1.167 0 1.167Z" clip-rule="evenodd" opacity=".102"></path>
                <path fill="#fff" fill-rule="evenodd" d="M6 0h224.483c-49.042 0-77.685 67.374-111.731 67.374C81.588 67.374 58.512 0 6 0Z" clip-rule="evenodd"></path>
            </svg>
            <svg xmlns="http://www.w3.org/2000/svg" width="157" height="61" fill="none" viewBox="0 0 157 61" class="block ez-footer-logo 2xl:hidden">
                <path fill="#445769" fill-rule="evenodd" d="M132.5 0H.5v61.02h44.616C79.748 61.02 82.881.385 139.504.385h17.009L132.5 0Z" clip-rule="evenodd" opacity=".2"></path>
                <path fill="#fff" fill-rule="evenodd" d="M127-.64H-.5v60.995h37.212C77.343 60.355 79 .262 130.543.262 151.106.262 173.5.5 173.5.5L127-.64Z" clip-rule="evenodd"></path>
            </svg>
            <svg xmlns="http://www.w3.org/2000/svg" width="32" fill="none" viewBox="0 0 73 84" class="absolute fill-primary-500 left-6 top-1 2xl:left-1/2 2xl:-translate-x-4 2xl:fill-slate-200">
                <g clip-path="url(#IconLogo_a_2zmxp46yotx)">
                    <path fill="url(#IconLogo_b_2zmxp46yotx)" d="M15 16h63v73H15z"></path>
                    <g filter="url(#IconLogo_c_2zmxp46yotx)">
                        <path fill-rule="evenodd" d="M67.209 32.755v10.389c0 17.379-14.089 31.468-31.468 31.468-17.379 0-31.468-14.089-31.468-31.468V32.755c0-17.38 14.089-31.469 31.468-31.469 17.379 0 31.468 14.089 31.468 31.469Z" clip-rule="evenodd"></path>
                    </g>
                    <path fill="#fff" fill-rule="evenodd" d="m37.92 43.206.014-.003a10.37 10.37 0 0 0 6.27-4.835c2.875-4.977 1.169-11.341-3.808-14.215-4.978-2.873-11.342-1.169-14.215 3.809-2.874 4.977-1.17 11.342 3.808 14.215.823.475 1.706.837 2.626 1.073l.15.039v8.008l-6.031-3.482c-8.09-4.671-10.863-15.019-6.193-23.109 4.67-8.09 15.02-10.863 23.11-6.192 8.09 4.67 10.862 15.02 6.192 23.109L37.62 62.794V43.286l.3-.08Z" clip-rule="evenodd"></path>
                </g>
                <defs>
                    <clipPath id="IconLogo_a_2zmxp46yotx">
                        <path fill="#fff" d="M0 0h73v84H0z"></path>
                    </clipPath>
                    <pattern id="IconLogo_b_2zmxp46yotx" width="1" height="1" patternContentUnits="objectBoundingBox">
                        <use href="#IconLogo_d_2zmxp46yotx" transform="matrix(.01634 0 0 .01406 -.111 -.055)"></use>
                    </pattern>
                </defs>
            </svg>
        </div>
    </div>

    <script>
        jQuery(document).ready(function($) {

            $('#invite-modal-button').on('click', function() {
                $(".invite-modal").removeClass('hidden')
            });

            $('.invite-modal > div:first-child').on('click', function() {
                $(".invite-modal").addClass('hidden')
            });

            $("#select-game").on('keyup', function() {
                let value = $(this).val()

                if (value !== '') {
                    $("#search-result").addClass('flex').removeClass('hidden')
                    $.ajax({
                        url: "<?php echo site_url('web-service/queryable.php') ?>",
                        type: "POST",
                        data: {
                            "source": "invitation",
                            "term": value
                        },
                        beforeSend: function() {
                            $("#search-result").text('در حال جست و جو')
                        },
                        success: function(res) {
                            let response = JSON.parse(res)

                            let out =
                                '<div class="flex flex-col w-full overflow-y-auto max-h-54 no-scrollbar">'
                            response.forEach(item => {
                                let title = item.title.replaceAll(value,
                                    `<mark class="text-white bg-primaryColor">${value}</mark>`
                                )

                                out +=
                                    `<button type="button" data-id="${item.product_id}" class="flex items-center gap-2 py-4 border-b select-game-button"><img src="${item.image}" alt="${item.title}" style="width: 20px; height: 24px; border-radius: 4px;"><span>${title}</span></button>`
                            })
                            out += '</div>'

                            $("#search-result").html(out)

                            $(".select-game-button").on('click', function() {
                                let name = $(this).find('span').text(),
                                    id = $(this).data('id')
                                $("#select-game").val(name).attr(
                                    'data-id', id)
                                $("#search-result").removeClass('flex')
                                    .addClass('hidden')
                            })
                        }
                    })
                } else {
                    $("#search-result").addClass('hidden').removeClass('flex')
                }
            })

            $("#invite-user").on('click', function() {
                const _ = $(this),
                    game = $("#select-game"),
                    id = game.data('id')

                $.ajax({
                    type: 'POST',
                    url: "<?php echo admin_url('admin-ajax.php') ?>",
                    data: {
                        'action': 'v2_ajax_handler',
                        'nonce': "<?php echo wp_create_nonce('v2-ajax-nonce') ?>",
                        'callback': 'profile_invite_user',
                        'user': '<?php echo $profile_user_id; ?>',
                        'product': id
                    },
                    beforeSend: function() {
                        _.attr('disabled', true).css('opacity', '.5')
                    },
                    success: function(response) {
                        Swal.mixin({
                            toast: true,
                            position: 'bottom-start',
                            showConfirmButton: false,
                            timer: 3000,
                        }).fire({
                            icon: response.success ? 'success' : 'error',
                            title: response.success ? response.data.message : response.data
                        })

                        // Send tracking data to Zabalin if invitation was successful
                        if (response.success && response.data.tracking_data) {
                            zebline.event.track("invitation_sent", response.data.tracking_data);
                        }

                        if (response.success) {
                            setTimeout(() => window.location.reload(), 3000)
                        } else {
                            setTimeout(() => _.attr('disabled', false).css(
                                'opacity', '1'), 3000)
                        }
                    },
                })
            })


            $("body").on('click', '[data-action]', function() {
                let _ = $(this)
                let collection = $(this).data('collection')

                let user = <?php echo is_user_logged_in() ? 1 : 0; ?>

                const Toast = Swal.mixin({
                    toast: true,
                    position: 'bottom-start',
                    showConfirmButton: false,
                    timer: 3000,
                })

                if (user) {
                    let currentPage = window.location.href;
                    zebline.event.track("like_collection", {
                        "collection_id": collection,
                        "current_page": currentPage,
                    });


                    $.ajax({
                        url: "<?php echo admin_url('admin-ajax.php'); ?>",
                        type: 'POST',
                        data: {
                            'action': 'v2_ajax_handler',
                            'nonce': "<?php echo wp_create_nonce('v2-ajax-nonce') ?>",
                            'callback': 'profile_like_dislike',
                            'collection': collection,
                        },
                        beforeSend: function() {
                            _.text("...")
                        },
                        success: function(response) {


                            Toast.fire({
                                icon: response.success ? "success" : "error",
                                title: response.data.text
                            })

                            _.parent().html(response.data.button)
                        }
                    })
                } else {
                    Swal.fire({
                        iconHtml: `<svg xmlns="http://www.w3.org/2000/svg" width="95" height="97" viewBox="0 0 95 97" fill="none" class="-mr-2.5">
                    <g filter="url(#filter0_d_23347_17729)">
                    <mask id="path-1-inside-1_23347_17729" fill="white">
                    <path d="M71 31.5C71 48.897 56.897 63 39.5 63C22.103 63 8 48.897 8 31.5C8 14.103 22.103 0 39.5 0C56.897 0 71 14.103 71 31.5Z"/>
                    </mask>
                    <path d="M71 31.5C71 48.897 56.897 63 39.5 63C22.103 63 8 48.897 8 31.5C8 14.103 22.103 0 39.5 0C56.897 0 71 14.103 71 31.5Z" fill="white"/>
                    <path d="M70.5 31.5C70.5 48.6208 56.6208 62.5 39.5 62.5V63.5C57.1731 63.5 71.5 49.1731 71.5 31.5H70.5ZM39.5 62.5C22.3792 62.5 8.5 48.6208 8.5 31.5H7.5C7.5 49.1731 21.8269 63.5 39.5 63.5V62.5ZM8.5 31.5C8.5 14.3792 22.3792 0.5 39.5 0.5V-0.5C21.8269 -0.5 7.5 13.8269 7.5 31.5H8.5ZM39.5 0.5C56.6208 0.5 70.5 14.3792 70.5 31.5H71.5C71.5 13.8269 57.1731 -0.5 39.5 -0.5V0.5Z" fill="#5091FB" mask="url(#path-1-inside-1_23347_17729)"/>
                    </g>
                    <g filter="url(#filter1_i_23347_17729)">
                    <circle cx="39.8016" cy="22.45" r="9.45" fill="url(#paint0_linear_23347_17729)"/>
                    </g>
                    <g filter="url(#filter2_i_23347_17729)">
                    <ellipse cx="39.8" cy="43.4496" rx="16.8" ry="7.35" fill="url(#paint1_linear_23347_17729)"/>
                    </g>
                    <defs>
                    <filter id="filter0_d_23347_17729" x="0" y="0" width="95" height="97" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                    <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                    <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
                    <feOffset dx="8" dy="18"/>
                    <feGaussianBlur stdDeviation="8"/>
                    <feComposite in2="hardAlpha" operator="out"/>
                    <feColorMatrix type="matrix" values="0 0 0 0 0.306354 0 0 0 0 0.36728 0 0 0 0 0.425 0 0 0 0.08 0"/>
                    <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_23347_17729"/>
                    <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_23347_17729" result="shape"/>
                    </filter>
                    <filter id="filter1_i_23347_17729" x="29.3516" y="11" width="19.8984" height="20.9004" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                    <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                    <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"/>
                    <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
                    <feOffset dx="-1" dy="-2"/>
                    <feGaussianBlur stdDeviation="1.5"/>
                    <feComposite in2="hardAlpha" operator="arithmetic" k2="-1" k3="1"/>
                    <feColorMatrix type="matrix" values="0 0 0 0 0.520833 0 0 0 0 0.689332 0 0 0 0 1 0 0 0 1 0"/>
                    <feBlend mode="normal" in2="shape" result="effect1_innerShadow_23347_17729"/>
                    </filter>
                    <filter id="filter2_i_23347_17729" x="22" y="34.0996" width="34.6016" height="16.7002" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                    <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                    <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"/>
                    <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
                    <feOffset dx="-1" dy="-2"/>
                    <feGaussianBlur stdDeviation="1.5"/>
                    <feComposite in2="hardAlpha" operator="arithmetic" k2="-1" k3="1"/>
                    <feColorMatrix type="matrix" values="0 0 0 0 0.501961 0 0 0 0 0.67451 0 0 0 0 0.996078 0 0 0 1 0"/>
                    <feBlend mode="normal" in2="shape" result="effect1_innerShadow_23347_17729"/>
                    </filter>
                    <linearGradient id="paint0_linear_23347_17729" x1="64.2409" y1="12.5616" x2="43.2367" y2="12.0588" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#5091FB"/>
                    <stop offset="1" stop-color="#3F7FF5"/>
                    </linearGradient>
                    <linearGradient id="paint1_linear_23347_17729" x1="83.2478" y1="35.7586" x2="45.9971" y2="33.7204" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#5091FB"/>
                    <stop offset="1" stop-color="#3F7FF5"/>
                    </linearGradient>
                    </defs>
                    </svg>`,
                        customClass: {
                            icon: 'border-0',
                            title: 'text-lg leading-5 pt-0',
                            actions: 'w-full px-4',
                            confirmButton: 'w-full bg-primaryColor text-white shadow-13 rounded-lg p-1',
                            popup: 'rounded-2xl'
                        },
                        confirmButtonText: 'ورود به حساب کاربری',
                        buttonsStyling: false,
                        title: 'برای لایک کالکشن، لطفاً ابتدا به حساب کاربری خود وارد شوید.',
                        width: 240,
                    }).then(result => {
                        if (result.isConfirmed) {
                            window.location.href = (window.location.href = window
                                .location.origin + '/panel?redirect=' + window
                                .location.href)
                        }
                    })
                }
            })
        });
    </script>

<?php get_footer(); ?>
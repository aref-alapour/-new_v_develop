<?php
global $wp, $wpdb;
// گرفتن id از url
$id = $wp->query_vars['reserve'];
if (!ctype_digit($id)) {
    $id = $wpdb->get_row($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_name=%s", $id))->ID;
}

// گرفتن title محصول
$product_title = get_the_title($id);

// گرفتن type محصول
$type = 'اتاق فرار';
$terms = get_the_terms($id, 'product_cat');
if ($terms && count($terms) > 1) {
    foreach ($terms as $term) {
        if ($term->parent == 0) {
            $type = $term->name;
        }
    }
} elseif ($terms) {
    $type = get_term($terms[0]->parent)->name;
}

// ساخت آرایه data_title
$data_title = [
    'title' => $product_title,
    'type'  => $type,
];

add_filter('pre_get_document_title', function ($title) use ($data_title) {
    return 'رزرو ' . $data_title['type'] . ' ' . $data_title['title'];
}, 999);
add_filter('wpseo_title', function ($title) use ($data_title) {
    return 'رزرو ' . $data_title['type'] . ' ' . $data_title['title'];
}, 999);
get_header();
global $wpdb;
$id = $wp->query_vars['reserve'];

if (! ctype_digit($id)) {
    $id = $wpdb->get_row($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_name=%s", $id))->ID;
}

$product = wc_get_product($id);
$brand   = get_the_terms($id, 'yith_product_brand')[0];

$city  = '';
$type  = 'اتاق فرار';
$terms = get_the_terms($id, 'product_cat');
if (count($terms) > 1) {
    foreach ($terms as $term) {
        if ($term->parent == 0) {
            $type = $term->name;
        } else {
            $city = $term->name;
        }
    }
} else {
    $type = get_term($terms[0]->parent)->name;
    $city = $terms[0]->name;
}

$product_rates     = get_post_meta($id, 'clone_product_rates', true);
$comments_count    = get_post_meta($id, 'clone_comments_count_new', true);
$comments_count_meta = get_comments(array(
    'post_id' => $id,
    'status' => 'approve',
    'parent'    => 0,
));
$comments_count_meta_number = count($comments_count_meta);
$decor    = (int) $comments_count !== 0 ? $product_rates[1094] / $comments_count / 20 : 0;
$moaama   = (int) $comments_count !== 0 ? $product_rates[1095] / $comments_count / 20 : 0;
$tazegi   = (int) $comments_count !== 0 ? $product_rates[1098] / $comments_count / 20 : 0;
$act      = (int) $comments_count !== 0 ? $product_rates[1096] / $comments_count / 20 : 0;
$barkhord = (int) $comments_count !== 0 ? $product_rates[1097] / $comments_count / 20 : 0;

$raw_rate = ($decor + $moaama + $tazegi + $act + $barkhord) / 5;

if ($type != 'اتاق فرار')
    $raw_rate = $raw_rate * 5; // امتیاز غیر اتاق فرارهارو در 5 ضرب کن تا استاندارد بشه

// مدیریت امتیازهایی مثل 4.995 که نباید 5 بشوند اما همچنان رند بودن حفظ بشه
if ($raw_rate == 5)
    $rate_final = 5;
elseif (round($raw_rate, 2) == 5) {
    $factor     = pow(10, 2);
    $rate_final = floor($raw_rate * $factor) / $factor;
} else
    $rate_final = number_format(round($raw_rate, 2), 2, '.', '');

$data = [

    'product' => [
        'title'    => $product->get_title(),
        'image'    => get_post_thumbnail_id($id),
        'rating'   => $rate_final,
        'comments' => $comments_count_meta_number,
    ],
    'brand'   => [
        'id'    => $brand->term_id,
        'name'  => $brand->name,
        'slug'  => $brand->slug,
        'image' => get_term_meta($brand->term_id, 'thumbnail_id', true),
        'city'  => $city,
        'hood'  => get_field("room_loc", $id),
    ],
];

$date = new DateTime('now', new DateTimeZone('Asia/Tehran'));
$date->setTime(0, 0, 0);
// شروع روز از نیمه‌شب امروز در تایم‌زون تهران (بدون جابجایی منفی که باعث نمایش دیروز می‌شد)
$startOfDay = $date->getTimestamp();
$lastWeek   = $startOfDay - 7 * 24 * 60 * 60;
$nextWeek   = $startOfDay + 7 * 24 * 60 * 60;
$lastMonth  = $startOfDay - 30 * 24 * 60 * 60;
$nextMonth  = $startOfDay + 30 * 24 * 60 * 60;

preg_match_all('/\d+/', get_field("room_tedad", $product->get_id()), $numbers);
$min = $numbers[0][0];
$max = $numbers[0][1];

wp_enqueue_script('persian-date');

?>

<div class="container mx-auto max-lg:pt-1 pt-20 mb-10 max-lg:mt-8">

    <div class="mb-8 flex items-center justify-between max-lg:hidden">
        <div class="flex items-center gap-5">
            <div class="inline-block">
                <?php echo wp_get_attachment_image($data['product']['image'], 'full', false, [
                    'class'  => 'h-29 w-23 rounded-xl object-cover shadow-96',
                    'width'  => '94',
                    'height' => '117',
                ]); ?>
            </div>
            <div class="flex flex-col gap-3">
                <p class="text-[#5b6e87] text-16 space-x-1">
                    در حال تهیه بلیت <span><?php echo $type ?></span>
                </p>
                <h1 class="mt-0 text-24 font-extrabold text-textColor">
                    <?php echo $data['product']['title'] ?>
                </h1>
                <div class="flex items-center gap-x-3">
                    <span class="inline-flex h-5 w-9 content-center rounded bg-[#EFC101] leading-none justify-center items-center">
                        <?= $rate_final ?>
                    </span>
                    <span class="text-[#889bad]">
                        میانگین امتیاز این <span><?php echo $type ?></span> از <?php echo $data['product']['comments']; ?> رای
                    </span>
                </div>
            </div>
        </div>
        <div class="flex flex-col items-end gap-5">

            <button class="share" data-title="<?php echo $data['product']['title']; ?>" data-content="میانگین امتیاز این بازی از <?php echo $data['product']['comments']; ?> رای" data-url="<?php echo site_url('/r/' . $id); ?>">
                <span class="inline-flex items-center gap-4 text-sm font-bold text-[#09192d]">اشتراک با
                    دوستان
                    <svg
                        width="19" height="20" viewBox="0 0 19 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11.1202 15.0232L6.92121 12.7332C6.373 13.3193 5.66119 13.7269 4.87828 13.9031C4.09537 14.0793 3.27756 14.0159 2.53113 13.7212C1.7847 13.4266 1.14417 12.9142 0.692774 12.2506C0.241381 11.5871 0 10.8032 0 10.0007C0 9.19821 0.241381 8.41427 0.692774 7.75076C1.14417 7.08725 1.7847 6.57486 2.53113 6.28017C3.27756 5.98548 4.09537 5.92211 4.87828 6.09832C5.66119 6.27452 6.373 6.68214 6.92121 7.2682L11.1212 4.97821C10.8831 4.03417 10.9975 3.0357 11.4429 2.16997C11.8884 1.30424 12.6342 0.630692 13.5408 0.275574C14.4473 -0.0795433 15.4522 -0.0918428 16.3671 0.240981C17.2821 0.573805 18.0442 1.2289 18.5107 2.08347C18.9772 2.93804 19.116 3.9334 18.9011 4.88299C18.6862 5.83257 18.1323 6.67116 17.3433 7.24158C16.5543 7.81199 15.5843 8.07506 14.6152 7.98147C13.6462 7.88789 12.7445 7.44406 12.0792 6.7332L7.87921 9.0232C8.04074 9.66452 8.04074 10.3359 7.87921 10.9772L12.0792 13.2672C12.7448 12.5567 13.6467 12.1133 14.6158 12.0202C15.5849 11.9271 16.5547 12.1906 17.3434 12.7614C18.1322 13.3322 18.6856 14.171 18.9001 15.1207C19.1146 16.0703 18.9754 17.0656 18.5085 17.9199C18.0417 18.7743 17.2793 19.429 16.3642 19.7615C15.4491 20.0939 14.4442 20.0812 13.5379 19.7257C12.6315 19.3702 11.8859 18.6964 11.4408 17.8305C10.9958 16.9646 10.8818 15.9661 11.1202 15.0222M4.00021 12.0002C4.53064 12.0002 5.03935 11.7895 5.41442 11.4144C5.78949 11.0393 6.00021 10.5306 6.00021 10.0002C6.00021 9.46977 5.78949 8.96106 5.41442 8.58599C5.03935 8.21092 4.53064 8.00021 4.00021 8.00021C3.46977 8.00021 2.96107 8.21092 2.58599 8.58599C2.21092 8.96106 2.00021 9.46977 2.00021 10.0002C2.00021 10.5306 2.21092 11.0393 2.58599 11.4144C2.96107 11.7895 3.46977 12.0002 4.00021 12.0002ZM15.0002 6.00021C15.5306 6.00021 16.0393 5.78949 16.4144 5.41442C16.7895 5.03935 17.0002 4.53064 17.0002 4.00021C17.0002 3.46977 16.7895 2.96107 16.4144 2.58599C16.0393 2.21092 15.5306 2.00021 15.0002 2.00021C14.4698 2.00021 13.9611 2.21092 13.586 2.58599C13.2109 2.96107 13.0002 3.46977 13.0002 4.00021C13.0002 4.53064 13.2109 5.03935 13.586 5.41442C13.9611 5.78949 14.4698 6.00021 15.0002 6.00021ZM15.0002 18.0002C15.5306 18.0002 16.0393 17.7895 16.4144 17.4144C16.7895 17.0393 17.0002 16.5306 17.0002 16.0002C17.0002 15.4698 16.7895 14.9611 16.4144 14.586C16.0393 14.2109 15.5306 14.0002 15.0002 14.0002C14.4698 14.0002 13.9611 14.2109 13.586 14.586C13.2109 14.9611 13.0002 15.4698 13.0002 16.0002C13.0002 16.5306 13.2109 17.0393 13.586 17.4144C13.9611 17.7895 14.4698 18.0002 15.0002 18.0002Z"
                            fill="#09192D" fill-opacity="0.8"></path>
                    </svg>
                </span>
            </button>

            <?php
            if ($data['brand']['id']) { ?>

                <a href="<?php echo get_term_link($data['brand']['id']); ?>" target="_blank" class="flex flex-row-reverse items-center gap-4">

                    <?php echo wp_get_attachment_image($data['brand']['image'], 'full', false, [
                        'class'  => 'h-10 w-10 rounded-xl object-cover shadow-96 shadow-13',
                        'width'  => '76',
                        'height' => '76',
                    ]); ?>

                    <h2 class="text-center text-md font-bold">
                        <?php echo esc_html($data['brand']['name']) ?>
                    </h2>

                </a>

            <?php
            } ?>

            <div class="rounded-xl bg-[#EDF2F5] w-full px-2 text-center">
                <?php echo $data['brand']['hood'] ?>
                .
                <?php echo $data['brand']['city'] ?>
            </div>

        </div>
    </div>

    <div class="mb-8 flex gap-x-4 items-stretch lg:hidden">
        <div class="grow flex flex-col relative">
            <p class="text-[#5b6e87] space-x-1">
                در حال تهیه بلیت <span><?php echo $type ?></span>
            </p>
            <h1 class="mt-0 text-2xl font-extrabold text-textColor mb-2">
                <?php echo $data['product']['title'] ?>
            </h1>
            <div class="flex justify-between">
                <div class="text-[#5b6e87] text-14">
                    <?php echo $data['brand']['hood'] ?>
                    .
                    <?php echo $data['brand']['city'] ?>
                </div>
                <div class="flex gap-x-2">
                    <span class="px-2 font-bold text-center content-center rounded-xl bg-[#EFC101] leading-none text-16">
                        <?= $rate_final ?>
                    </span>
                    <span class="space-x-1 font-bold space-x-reverse text-14">
                        <span><?php echo $data['product']['comments']; ?></span>
                        <span class="text-[#889BAD]">رأی</span>
                    </span>
                </div>
            </div>
            <div class="absolute left-0 top-0 text-center">
                <button class="share" data-title="<?php echo $data['product']['title']; ?>" data-content="میانگین امتیاز این <span><?php echo $type ?></span> از <?php echo $data['product']['comments']; ?> رای" data-url="<?php echo site_url('/r/' . $id); ?>">
                    <svg width="19" height="20" viewBox="0 0 19 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11.1202 15.0232L6.92121 12.7332C6.373 13.3193 5.66119 13.7269 4.87828 13.9031C4.09537 14.0793 3.27756 14.0159 2.53113 13.7212C1.7847 13.4266 1.14417 12.9142 0.692774 12.2506C0.241381 11.5871 0 10.8032 0 10.0007C0 9.19821 0.241381 8.41427 0.692774 7.75076C1.14417 7.08725 1.7847 6.57486 2.53113 6.28017C3.27756 5.98548 4.09537 5.92211 4.87828 6.09832C5.66119 6.27452 6.373 6.68214 6.92121 7.2682L11.1212 4.97821C10.8831 4.03417 10.9975 3.0357 11.4429 2.16997C11.8884 1.30424 12.6342 0.630692 13.5408 0.275574C14.4473 -0.0795433 15.4522 -0.0918428 16.3671 0.240981C17.2821 0.573805 18.0442 1.2289 18.5107 2.08347C18.9772 2.93804 19.116 3.9334 18.9011 4.88299C18.6862 5.83257 18.1323 6.67116 17.3433 7.24158C16.5543 7.81199 15.5843 8.07506 14.6152 7.98147C13.6462 7.88789 12.7445 7.44406 12.0792 6.7332L7.87921 9.0232C8.04074 9.66452 8.04074 10.3359 7.87921 10.9772L12.0792 13.2672C12.7448 12.5567 13.6467 12.1133 14.6158 12.0202C15.5849 11.9271 16.5547 12.1906 17.3434 12.7614C18.1322 13.3322 18.6856 14.171 18.9001 15.1207C19.1146 16.0703 18.9754 17.0656 18.5085 17.9199C18.0417 18.7743 17.2793 19.429 16.3642 19.7615C15.4491 20.0939 14.4442 20.0812 13.5379 19.7257C12.6315 19.3702 11.8859 18.6964 11.4408 17.8305C10.9958 16.9646 10.8818 15.9661 11.1202 15.0222M4.00021 12.0002C4.53064 12.0002 5.03935 11.7895 5.41442 11.4144C5.78949 11.0393 6.00021 10.5306 6.00021 10.0002C6.00021 9.46977 5.78949 8.96106 5.41442 8.58599C5.03935 8.21092 4.53064 8.00021 4.00021 8.00021C3.46977 8.00021 2.96107 8.21092 2.58599 8.58599C2.21092 8.96106 2.00021 9.46977 2.00021 10.0002C2.00021 10.5306 2.21092 11.0393 2.58599 11.4144C2.96107 11.7895 3.46977 12.0002 4.00021 12.0002ZM15.0002 6.00021C15.5306 6.00021 16.0393 5.78949 16.4144 5.41442C16.7895 5.03935 17.0002 4.53064 17.0002 4.00021C17.0002 3.46977 16.7895 2.96107 16.4144 2.58599C16.0393 2.21092 15.5306 2.00021 15.0002 2.00021C14.4698 2.00021 13.9611 2.21092 13.586 2.58599C13.2109 2.96107 13.0002 3.46977 13.0002 4.00021C13.0002 4.53064 13.2109 5.03935 13.586 5.41442C13.9611 5.78949 14.4698 6.00021 15.0002 6.00021ZM15.0002 18.0002C15.5306 18.0002 16.0393 17.7895 16.4144 17.4144C16.7895 17.0393 17.0002 16.5306 17.0002 16.0002C17.0002 15.4698 16.7895 14.9611 16.4144 14.586C16.0393 14.2109 15.5306 14.0002 15.0002 14.0002C14.4698 14.0002 13.9611 14.2109 13.586 14.586C13.2109 14.9611 13.0002 15.4698 13.0002 16.0002C13.0002 16.5306 13.2109 17.0393 13.586 17.4144C13.9611 17.7895 14.4698 18.0002 15.0002 18.0002Z" fill="#09192D" fill-opacity="0.8"></path>
                    </svg>
                </button>
            </div>
            <hr class="my-2">

            <?php
            if ($data['brand']['id']) { ?>

                <a href="<?php echo get_term_link($data['brand']['id']); ?>" target="_blank" class="flex flex-row-reverse items-center justify-between gap-2">

                    <?php echo wp_get_attachment_image($data['brand']['image'], 'full', false, [
                        'class'  => 'h-8 w-8 rounded object-cover shadow-96',
                        'width'  => '76',
                        'height' => '76',
                    ]); ?>

                    <h2 class="text-center text-sm font-bold">
                        <?php echo esc_html($data['brand']['name']) ?>
                    </h2>

                </a>

            <?php
            } ?>

        </div>
        <div class="w-[132px]">
            <?php echo wp_get_attachment_image($data['product']['image'], 'full', false, [
                'class'  => 'h-full w-full rounded-xl object-cover shadow-96',
                'width'  => '94',
                'height' => '117',
            ]); ?>
        </div>
    </div>

    <div class="mb-7 flex items-center justify-between gap-4 font-bold">
        <button type="button" data-timestamp="<?php echo esc_attr($lastMonth); ?>" class="max-lg:hidden last-month flex w-2/12 items-center justify-between gap-2 rounded-lg border-l border-r border-t border-l-[#E4EBF0] border-r-[#E4EBF0] border-t-[#E4EBF0] px-6 py-1 text-[#09192D] shadow-98 disabled:opacity-50 max-lg:text-16" disabled>
            <svg xmlns="http://www.w3.org/2000/svg" width="7" height="12" viewBox="0 0 7 12" fill="none" class="m-0">
                <path d="M2 2L4.55 5.4C4.81667 5.75556 4.81667 6.24444 4.55 6.6L2 10" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
            </svg>
            ماه پیش
        </button>
        <button type="button" data-timestamp="<?php echo esc_attr($lastWeek); ?>" class="last-week flex w-2/12 max-lg:grow max-lg:leading-4 items-center justify-between gap-2 rounded-lg border-l border-r border-t border-l-[#E4EBF0] border-r-[#E4EBF0] border-t-[#E4EBF0] px-6 py-1 text-[#09192D] shadow-98 disabled:opacity-50 max-lg:text-16" disabled>
            <svg xmlns="http://www.w3.org/2000/svg" width="7" height="12" viewBox="0 0 7 12" fill="none" class="m-0">
                <path d="M2 2L4.55 5.4C4.81667 5.75556 4.81667 6.24444 4.55 6.6L2 10" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
            </svg>
            هفته پیش
        </button>

        <div type="button" class="flex min-h-[41px] w-4/12 items-center justify-center gap-x-4 lg:gap-x-20 rounded-lg border-l border-r border-t border-l-[#E4EBF0] border-r-[#E4EBF0] border-t-[#E4EBF0] px-6 py-1 text-[#09192D] shadow-98 max-lg:flex-col max-lg:leading-none max-lg:gap-1 max-lg:text-16">
            <span class="flex items-center justify-center gap-3 selected-month max-lg:text-primaryColor" data-calendar="<?php echo esc_attr($startOfDay); ?>">
                <?php echo jdate("F", $startOfDay) ?>
            </span>
            <span class="flex items-center justify-center gap-3 selected-year" data-calendar="<?php echo esc_attr($startOfDay); ?>">
                <?php echo jdate("Y", $startOfDay); ?>
            </span>
        </div>

        <button type="button" data-timestamp="<?php echo esc_attr($nextWeek); ?>" class="next-week flex w-2/12 max-lg:grow max-lg:leading-4 items-center justify-between gap-2 rounded-lg border-l border-r border-t border-l-[#E4EBF0] border-r-[#E4EBF0] border-t-[#E4EBF0] px-6 py-1 text-[#09192D] shadow-98 max-lg:text-16">
            هـفته بعد
            <svg xmlns="http://www.w3.org/2000/svg" width="7" height="12" viewBox="0 0 7 12" fill="none" class="m-0">
                <path d="M5 2L2.45 5.4C2.18333 5.75556 2.18333 6.24444 2.45 6.6L5 10" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
            </svg>
        </button>
        <button type="button" data-timestamp="<?php echo esc_attr($nextMonth); ?>" class="max-lg:hidden next-month flex w-2/12 max-lg:w-4 items-center justify-between gap-2 rounded-lg border-l border-r border-t border-l-[#E4EBF0] border-r-[#E4EBF0] border-t-[#E4EBF0] px-6 py-1 text-[#09192D] shadow-98 max-lg:text-16">
            مــاه بعد
            <svg xmlns="http://www.w3.org/2000/svg" width="7" height="12" viewBox="0 0 7 12" fill="none" class="m-0">
                <path d="M5 2L2.45 5.4C2.18333 5.75556 2.18333 6.24444 2.45 6.6L5 10" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
            </svg>
        </button>
    </div>

</div>

<div id="table-of-sans" class="mb-7" data-product-id="<?php echo esc_attr( (string) $id ); ?>" data-day-start="<?php echo esc_attr( (string) $startOfDay ); ?>" data-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" data-ajax-nonce="<?php echo esc_attr( wp_create_nonce( 'v2-ajax-nonce' ) ); ?>"></div>

<script>
    jQuery(document).ready(function($) {
        const EZ_TZ = 'Asia/Tehran';

        function ezTehranFmt(unixSec, part) {
            const d = new Date(unixSec * 1000);
            const base = { timeZone: EZ_TZ };
            if (part === 'MMMM') {
                return new Intl.DateTimeFormat('fa-IR', Object.assign({ month: 'long' }, base)).format(d);
            }
            if (part === 'YYYY') {
                return new Intl.DateTimeFormat('fa-IR', Object.assign({ year: 'numeric' }, base)).format(d);
            }
            if (part === 'dddd') {
                return new Intl.DateTimeFormat('fa-IR', Object.assign({ weekday: 'long' }, base)).format(d);
            }
            if (part === 'DD') {
                return new Intl.DateTimeFormat('fa-IR', Object.assign({ day: 'numeric' }, base)).format(d);
            }
            if (part === 'HH:mm') {
                return new Intl.DateTimeFormat('en-GB', {
                    timeZone: EZ_TZ,
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false,
                }).format(d);
            }
            return '';
        }

        const BuildTable = (time) => {
            const root = document.getElementById('table-of-sans');
            const productId = parseInt(root?.dataset?.productId || '0', 10);
            const dayStart = parseInt(time, 10);
            if (root && productId > 0 && dayStart > 0) {
                root.dataset.dayStart = String(dayStart);
            }
            if (window.__EZ_BOOT__?.sub_secret && productId > 0 && dayStart > 0) {
                if (typeof window.ezBookingLoadWeek === 'function') {
                    window.ezBookingLoadWeek(productId, dayStart);
                    return;
                }
            }

            $.ajax({
                url: "<?php echo admin_url('admin-ajax.php'); ?>",
                type: 'POST',
                data: {
                    'action': 'v2_ajax_handler',
                    'nonce': "<?php echo wp_create_nonce('v2-ajax-nonce') ?>",
                    'callback': 'reserve_get_table',
                    'time': time,
                    'product': <?php echo $id; ?>
                },
                beforeSend: function() {
                    let out = "<div class='grid gap-3' style='grid-template-columns: repeat(7, minmax(0, 1fr))'>"
                    for (let i = 0; i < (7 * 4); i++) {
                        out += "<div class='skeleton aspect-square rounded-xl'></div>"
                    }
                    out += "<div>"
                    $("#table-of-sans").html(out)
                },
                success: function(response) {
                    $("#table-of-sans").html(response)
                }
            })
        }

        BuildTable(<?php echo $startOfDay; ?>)

        $("body")
            .on('click', '[data-timestamp]', function() {
                let today = <?php echo $startOfDay; ?>;

                let formula

                BuildTable($(this).attr('data-timestamp'))

                if ($(this).is('.next-week')) {
                    formula = 60 * 60 * 24 * 7
                } else if ($(this).is('.last-week')) {
                    formula = 60 * 60 * 24 * 7 * -1
                } else if ($(this).is('.next-month')) {
                    formula = 60 * 60 * 24 * 30
                } else if ($(this).is('.last-month')) {
                    formula = 60 * 60 * 24 * 30 * -1
                }

                $(".last-month,.last-week,.next-week,.next-month").attr('data-timestamp', function() {
                    return parseInt($(this).attr('data-timestamp')) + formula
                })

                if (parseInt($(".last-month").attr('data-timestamp')) < today) {
                    $(".last-month").attr('disabled', 'disabled')
                } else {
                    $(".last-month").removeAttr('disabled')
                }

                if (parseInt($(".last-week").attr('data-timestamp')) < today) {
                    $(".last-week").attr('disabled', 'disabled')
                } else {
                    $(".last-week").removeAttr('disabled')
                }

                $(".selected-month, .selected-year").attr('data-calendar', function() {
                    return parseInt($(this).attr('data-calendar')) + formula
                })

                $(".selected-month").text(function() {
                    return ezTehranFmt(parseInt($(".selected-month").attr('data-calendar'), 10), 'MMMM');
                });

                $(".selected-year").text(function() {
                    return ezTehranFmt(parseInt($(".selected-year").attr('data-calendar'), 10), 'YYYY');
                });
            })
            .on('click', ".box.open", function() {
                let front = $(this).find('.front'),
                    plus = front.find('[data-action="plus"]'),
                    minus = front.find('[data-action="minus"]'),
                    number = parseInt(front.find('span strong').text())

                $(".box.open").find('.front').removeClass('top-0 active').addClass('top-full')
                front.addClass('top-0 active').removeClass('top-full')

                plus.on('click', function() {
                    if (number < <?php echo $max; ?>) {
                        number += 1
                    }

                    if (number < <?php echo $max; ?>) {
                        plus.addClass('bg-accent-450').removeClass('bg-gray-400')
                    } else {
                        plus.removeClass('bg-accent-450').addClass('bg-gray-400')
                    }

                    minus.addClass('bg-accent-450').removeClass('bg-gray-400')

                    front.find('span strong').text(number)
                })

                minus.on('click', function() {
                    if (number > <?php echo $min; ?>) {
                        number -= 1
                    }

                    if (number > <?php echo $min; ?>) {
                        minus.addClass('bg-accent-450').removeClass('bg-gray-400')
                    } else {
                        minus.removeClass('bg-accent-450').addClass('bg-gray-400')
                    }

                    plus.addClass('bg-accent-450').removeClass('bg-gray-400')

                    front.find('span strong').text(number)
                })
            })
            .on('click', '[data-item-timestamp]', function() {
                let e = $(this),
                    timestamp = e.attr('data-item-timestamp'),
                    price = e.attr('data-item-sell-price')

                let result = $(".reserve-result")

                const ts = parseInt(timestamp, 10);

                result.removeClass('hidden').addClass('flex');

                result.find('.selected-date').html(`
                    <span class="flex gap-2">${ezTehranFmt(ts, 'dddd')} <strong class="text-primaryColor">${ezTehranFmt(ts, 'DD')}</strong> ${ezTehranFmt(ts, 'MMMM')}</span>
                    <span class="mr-3">${ezTehranFmt(ts, 'HH:mm')}</span>
                    `);

                let ticket = $(".front.active").find('> span > strong').text().trim()
                result.find('.ticket-count').html(`<span>${ticket} بلیت</span>`)

                result.find('a strong').html(`${price} تومان`)

                let url = new URL(window.location.origin + '/checkout/?add-to-cart=0&book=0&quantity=0')
                url.searchParams.set("add-to-cart", <?php echo $id; ?>)
                url.searchParams.set("book", timestamp)
                url.searchParams.set("quantity", ticket)

                result.find('a').attr('href', url)
            })
            .on('click', '[data-tab]', function() {
                let e = $(this)
                let target = e.data('tab')

                // استیت اکتیو روی دکمه روز (به‌خصوص روی موبایل)
                $("[data-tab]").removeClass('is-active').removeAttr('style')
                e.addClass('is-active').css({
                    "background": "#5091FB",
                    "border-color": "transparent",
                    "box-shadow": "0px 2px 0px 0px #3F7FF5",
                    "color": "#FFFFFF"
                })

                $(".tabs").addClass('max-lg:hidden')
                $(`#tab-${target}`).removeClass('max-lg:hidden')
            })
    })
</script>

<?php get_footer();

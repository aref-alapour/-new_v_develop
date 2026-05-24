<?php
global $wp, $wpdb, $wp_query;

if (empty($wp->query_vars['ticket']) || !function_exists('ez_ticket_row_for_slug')) {
	$wp_query->set_404();
	status_header(404);
	nocache_headers();
	get_header();
	echo '<section class="container mx-auto mt-16 px-4 text-center"><p>بلیت یافت نشد.</p></section>';
	get_footer();
	exit;
}

$ticket_row = ez_ticket_row_for_slug($wp->query_vars['ticket']);
if (!$ticket_row) {
	$wp_query->set_404();
	status_header(404);
	nocache_headers();
	get_header();
	echo '<section class="container mx-auto mt-16 px-4 text-center"><p>بلیت یافت نشد یا هنوز برای نمایش آماده نیست.</p></section>';
	get_footer();
	exit;
}

$order_id      = (int) $ticket_row['order_id'];
$product_id    = (int) $ticket_row['game_id'];
$item_quantity = (int) ($ticket_row['order_tickets_quantity'] ?? 0);

if ($order_id <= 0 || $product_id <= 0 || $item_quantity <= 0) {
	$wp_query->set_404();
	status_header(404);
	nocache_headers();
	get_header();
	echo '<section class="container mx-auto mt-16 px-4 text-center"><p>اطلاعات بلیت ناقص است.</p></section>';
	get_footer();
	exit;
}

$medoo_queries = function_exists('medoo_queries') ? medoo_queries() : null;
$sans_time     = ez_ticket_resolve_sans_ts($order_id, $ticket_row, $medoo_queries);
if ($sans_time <= 0) {
	$wp_query->set_404();
	status_header(404);
	nocache_headers();
	get_header();
	echo '<section class="container mx-auto mt-16 px-4 text-center"><p>زمان سانس برای این بلیت ثبت نشده است.</p></section>';
	get_footer();
	exit;
}

get_header();

$order_date = jdate('Y.m.d', $sans_time);
$order_time = jdate('H:i', $sans_time);

/*************** Product Info ***************/

$product = [
	'id'       => $product_id,
	'quantity' => $item_quantity,
	'title'    => get_the_title($product_id),
	'url'      => get_the_permalink($product_id),
	'meta'     => ez_get_product_meta($product_id),
	'rules'    => get_post_meta($product_id, 'product_rules', true)
];

/************** Order Informations *******************/

$brand_terms = get_the_terms($product['id'], 'product_brand');
$brand_data  = (!empty($brand_terms) && !is_wp_error($brand_terms)) ? $brand_terms[0] : null;
if (!$brand_data && !empty($ticket_row['game_brand'])) {
	$brand_data = (object) ['name' => $ticket_row['game_brand'], 'term_id' => 0];
}

if (!$brand_data) {
	echo '<section class="container mx-auto mt-16 px-4 text-center"><p>اطلاعات برند بلیت موجود نیست.</p></section>';
	get_footer();
	exit;
}

$address            = $product['meta']->city_name . '، ' . get_field('room_address', $product['id']);
$geo_directions     = home_url("/geo.php?g=" . get_field('room_lat', $product_id) . ',' . get_field('room_long', $product_id));

/************** Order Paid Informations *******************/

$pish_per_person = get_post_meta($order_id, 'ticket_tedad', true);
$pish_per_person = !empty($pish_per_person) ? $pish_per_person : ($ticket_row['order_prepaid_tickets'] ?? null);
$pish_per_person = !empty($pish_per_person) ? $pish_per_person : get_post_meta($product_id, 'pish_pardakht_per_person', true);
$pish_per_person = !empty($pish_per_person) ? $pish_per_person : 1;

// تبدیل به عدد
$pish_per_person = (int) $pish_per_person;

$prepaid = isset($ticket_row['order_paid']) && $ticket_row['order_paid'] !== '' && $ticket_row['order_paid'] !== null
	? (float) $ticket_row['order_paid']
	: (float) get_post_meta($order_id, 'prepaid', true);
$item_quantity = (int) $item_quantity;

// جلوگیری از تقسیم بر صفر
if ($pish_per_person > 0) {
	$item_total = ($prepaid / $pish_per_person) * $item_quantity;
} else {
	$item_total = 0;
} ?>

<section class="container mx-auto mt-16" style="overflow: hidden">

    <div class="flex flex-col py-12 px-16 bg-breserve border border-edge rounded-2xl max-lg:px-6 max-lg:py-10">
        <div class="flex max-lg:flex-col bg-white border border-edge rounded-2xl p-10 max-lg:p-6 lg:gap-12 gap-6 items-center">

            <div class="flex flex-col grow">

                <div class="flex max-lg:flex-col justify-between items-center border-b pb-5 lg:pb-10 mb-10 max-lg:pb-5">

                    <div class="flex lg:items-center">

                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64" fill="none" class="ml-3">
                            <g filter="url(#filter0_d_29370_12972)">
                                <rect x="8" y="4" width="44" height="44" rx="22" fill="url(#paint0_linear_29370_12972)" />
                                <g filter="url(#filter1_d_29370_12972)">
                                    <path d="M39.9894 39.8397C37.926 36.1926 35.8635 32.5445 33.7869 28.8744C34.6389 28.0023 35.2643 27.0098 35.6051 25.8415C36.7379 21.9555 34.3721 17.8632 30.5261 17.0647C26.5836 16.246 22.8687 18.9541 22.3772 23.0054C21.7986 27.7759 26.0181 31.6475 30.6506 30.5891C30.88 30.5365 31.0447 30.4917 31.1917 30.7582C31.951 32.129 32.7281 33.4883 33.4976 34.8533C33.5267 34.9049 33.5388 34.9679 33.5753 35.074C33.4452 35.0816 33.345 35.095 33.2449 35.0931C31.5147 35.0721 29.779 35.1361 28.0554 35.01C23.3087 34.6632 19.1987 30.848 18.3008 26.0526C17.1465 19.8874 20.9503 14.0814 26.9806 12.8071C33.0988 11.5137 39.1918 15.9432 40.0007 22.2794C40.0653 22.7828 40.1046 23.2938 40.1055 23.8011C40.113 29.0072 40.1102 34.2124 40.1093 39.4185C40.1093 39.5541 40.0934 39.6907 40.0849 39.8264C40.0531 39.8311 40.0213 39.8349 39.9894 39.8397Z" fill="url(#paint1_linear_29370_12972)" />
                                    <path d="M29.7584 18.531C28.678 19.6152 27.679 20.6153 26.6829 21.6183C26.3608 21.9422 26.0256 22.2545 25.7326 22.6032C25.462 22.9251 25.2383 23.2891 24.9162 23.7447C24.6709 23.7027 24.2655 23.632 23.8639 23.5632C23.9528 20.5026 26.7353 18.1393 29.7584 18.531Z" fill="url(#paint2_linear_29370_12972)" />
                                </g>
                            </g>
                            <defs>
                                <filter id="filter0_d_29370_12972" x="0" y="0" width="64" height="64" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                    <feFlood flood-opacity="0" result="BackgroundImageFix" />
                                    <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                    <feOffset dx="2" dy="6" />
                                    <feGaussianBlur stdDeviation="5" />
                                    <feComposite in2="hardAlpha" operator="out" />
                                    <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0" />
                                    <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_29370_12972" />
                                    <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_29370_12972" result="shape" />
                                </filter>
                                <linearGradient id="paint0_linear_29370_12972" x1="29.6986" y1="16.8966" x2="22.0968" y2="53.4794" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#FC6F13" />
                                    <stop offset="1" stop-color="#D75602" />
                                </linearGradient>
                                <linearGradient id="paint1_linear_29370_12972" x1="26.0105" y1="27.45" x2="23.6261" y2="33.6582" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="white" />
                                    <stop offset="1" stop-color="#DBDBDB" />
                                </linearGradient>
                                <linearGradient id="paint2_linear_29370_12972" x1="26.0105" y1="27.45" x2="23.6261" y2="33.6582" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="white" />
                                    <stop offset="1" stop-color="#DBDBDB" />
                                </linearGradient>
                            </defs>
                        </svg>

                        <div class="flex flex-col">

                            <span class="text-slate-350 text-lg"><?php echo $product['meta']->product_type ?></span>

                            <div class="lg:flex lg:items-center">

                                <a class="text-4xl font-extrabold text-slate-700 lg:mt-1.5 text-nowrap" href="<?php echo esc_url($product['url']); ?>">
                                    <?php echo esc_html($product['title']); ?>
                                </a>

                                <div class="flex items-center gap-x-4 lg:mr-4">

                                    <svg class="m-0 h-4 w-4 lg:-mt-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 17" fill="none">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M0.38999 1.05549C0.639775 0.805527 0.978512 0.665107 1.33171 0.665107C1.68491 0.665107 2.02364 0.805527 2.27343 1.05549L7.99167 6.77949L13.7099 1.05549C13.8328 0.928142 13.9798 0.826565 14.1423 0.756687C14.3048 0.686808 14.4796 0.650026 14.6564 0.648488C14.8333 0.646949 15.0087 0.680685 15.1724 0.747726C15.3361 0.814767 15.4848 0.913771 15.6099 1.03896C15.7349 1.16415 15.8338 1.31302 15.9008 1.47688C15.9678 1.64074 16.0015 1.81632 15.9999 1.99336C15.9984 2.1704 15.9617 2.34536 15.8919 2.50803C15.8221 2.6707 15.7206 2.81783 15.5934 2.94082L9.87511 8.66482L15.5934 14.3888C15.836 14.6403 15.9702 14.9771 15.9672 15.3267C15.9642 15.6763 15.8241 16.0107 15.5771 16.2579C15.3302 16.5051 14.9961 16.6453 14.6468 16.6484C14.2976 16.6514 13.9611 16.517 13.7099 16.2742L7.99167 10.5502L2.27343 16.2742C2.02221 16.517 1.68575 16.6514 1.3365 16.6484C0.987258 16.6453 0.653177 16.5051 0.406215 16.2579C0.159253 16.0107 0.0191681 15.6763 0.0161333 15.3267C0.0130984 14.9771 0.147356 14.6403 0.38999 14.3888L6.10824 8.66482L0.38999 2.94082C0.14028 2.69078 0 2.35171 0 1.99816C0 1.6446 0.14028 1.30553 0.38999 1.05549Z" fill="black"></path>
                                    </svg>

                                    <span class="text-4xl font-extrabold text-primary-2"><?php echo esc_html($product['quantity']); ?></span>

                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="flex flex-col text-2xl text-left w-full max-lg:flex-row-reverse max-lg:justify-evenly pt-5 lg:pt-0 max-lg:mt-5 max-lg:border-t">
                        <span><?php echo esc_html($order_time); ?></span>
                        <span class="text-primaryColor"><?php echo esc_html($order_date); ?></span>
                    </div>

                </div>

                <div class="flex max-lg:items-start justify-between items-center">

                    <div class="flex flex-col">

                        <div class="flex max-lg:flex-col max-lg:items-start gap-4 items-center">
                            <span class="text-xl"><?php echo esc_html($brand_data->name); ?></span>
                            <div class="flex gap-8 text-lg">
                                <a href="tel:<?php echo esc_html(get_field('room_phone', $product['id'])); ?>">
                                    <?php echo esc_html(get_field('room_phone', $product['id'])); ?>
                                </a>
                                <a href="tel:<?php echo esc_html(get_field('room_phone_2', $product['id'])); ?>">
                                    <?php echo esc_html(get_field('room_phone_2', $product['id'])); ?>
                                </a>
                            </div>
                        </div>

                        <div><?php echo $address; ?></div>

                    </div>

                    <div class="flex flex-col text-2xl text-left">
						<?php
						$ez_brand_thumb_id = ! empty( $brand_data->term_id ) ? (int) get_term_meta( $brand_data->term_id, 'thumbnail_id', true ) : 0;
						$ez_brand_thumb_url = $ez_brand_thumb_id ? wp_get_attachment_url( $ez_brand_thumb_id ) : '';
						if ( $ez_brand_thumb_url ) :
							?>
                        <img src="<?php echo esc_url( $ez_brand_thumb_url ); ?>" style="width: 70px; height: 70px;" alt="">
						<?php endif; ?>
                    </div>

                </div>

            </div>

            <div class="relative rounded-lg lg:min-w-[25rem] lg:rounded-2xl max-lg:w-full">

                <div id="map" class="relative max-lg:w-full" style="max-width: 450px; height: 292px;z-index: 3;"></div>

                <script>
                    var map = L.map('map').setView([<?= round((float) get_field('room_lat', $product['id']), 3) ?>, <?= round((float) get_field('room_long', $product['id']), 3) ?>], 16);
                    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
                    let primaryIcon = L.icon({
                        iconUrl: "<?= Theme_URL ?>assets/images/escapezoom-marker-icon.png",
                        iconSize: [28, 34]
                    });
                    L.marker({
                            lat: <?= round((float) get_field('room_lat', $product['id']), 3) ?>,
                            lon: <?= round((float) get_field('room_long', $product['id']), 3) ?>
                        }, {
                            icon: primaryIcon
                        }).addTo(map)
                        .bindPopup("لوکیشن اتاق فرار <br> <?= $product['title'] ?>")
                </script>

                <div class="flex w-full gap-4 mt-5 lg:hidden" style="z-index: 3;">

                    <a href="https://escapezoom.ir/geo.php?g=<?= get_field('room_lat', $product['id']) ?>,<?= get_field('room_long', $product['id']) ?>" class="w-full text-gray-900 relative flex flex-row-reverse h-14 min-w-16 items-center justify-between px-6 gap-4 rounded-lg border border-gray-100 bg-white px-0 py-2 text-sm font-semibold shadow-13 transition-all duration-300 ease-in-out focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 disabled:cursor-not-allowed disabled:bg-slate-110 disabled:text-disabled disabled:shadow-none lg:px-6 lg:py-3" style="z-index: 400">
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="20" viewBox="0 0 24 24" class="max-lg:text-primary-500">
                                <path fill="currentColor" fill-rule="evenodd" d="M2 12C2 6.48 6.47 2 12 2c5.52 0 10 4.48 10 10 0 5.53-4.48 10-10 10-5.53 0-10-4.47-10-10Zm12.23 1.83 1.62-5.12a.45.45 0 0 0-.56-.57l-5.12 1.6c-.21.07-.38.23-.44.44l-1.6 5.13c-.11.34.22.67.56.56l5.1-1.6c.21-.06.38-.23.44-.44Z" clip-rule="evenodd"></path>
                            </svg>
                        </span>
                        <span class="truncate">مسیریابی</span>
                    </a>

                </div>

            </div>

        </div>
        <div class="mt-4 lg:mt-9 flex lg:justify-between max-lg:flex-col-reverse lg:gap-x-20 gap-12">
            <div class="lg:grid lg:grid-cols-2 gap-4 max-lg:flex max-lg:flex-col max-lg:items-center">

                <?php
                $summery = " اتاق فرار " . $product['title'];
                $url = site_url('t/' . $wp->query_vars['ticket']);
                $dates = date("Ymd", $sans_time) . "T" . date("His", $sans_time) . "/" . date("Ymd", $sans_time) . "T" . date("His", ($sans_time + (60 * 15)));
                ?>
                <a href="https://calendar.google.com/calendar/render?action=TEMPLATE&text=<?= $summery; ?>&details=<?= $url; ?>&dates=<?= $dates ?>&ctz=Asia/Tehran&location=<?= $address; ?>" target="_blank" class="text-gray-900 relative flex h-14 min-w-16 items-center justify-center gap-4 rounded-lg border border-gray-100 bg-white px-0 py-2 text-sm font-semibold shadow-13 transition-all duration-300 ease-in-out focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 disabled:cursor-not-allowed disabled:bg-slate-110 disabled:text-disabled disabled:shadow-none px-6 py-3">
                    افزودن به تقویم گوگل
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="25" viewBox="0 0 24 25" fill="none">
                            <g clip-path="url(#clip0_4133_8923)">
                                <path d="M18.3151 6.33203H5.68359V18.9635H18.3151V6.33203Z" fill="white"></path>
                                <path d="M18.3156 24.6476L23.9998 18.9634L21.1577 18.4785L18.3156 18.9634L17.7969 21.5631L18.3156 24.6476Z" fill="#EA4335"></path>
                                <path d="M0 18.9634V22.7529C0 23.7998 0.847875 24.6476 1.89469 24.6476H5.68425L6.26784 21.8055L5.68425 18.9634L2.58741 18.4785L0 18.9634Z" fill="#188038"></path>
                                <path d="M23.9998 6.33269V2.54312C23.9998 1.49631 23.152 0.648438 22.1052 0.648438H18.3156C17.9698 2.05806 17.7969 3.09544 17.7969 3.76056C17.7969 4.42562 17.9698 5.283 18.3156 6.33269C19.5728 6.69269 20.5202 6.87269 21.1577 6.87269C21.7953 6.87269 22.7427 6.69275 23.9998 6.33269Z" fill="#1967D2"></path>
                                <path d="M24.0007 6.33203H18.3164V18.9635H24.0007V6.33203Z" fill="#FBBC04"></path>
                                <path d="M18.3151 18.9648H5.68359V24.6491H18.3151V18.9648Z" fill="#34A853"></path>
                                <path d="M18.3158 0.648438H1.89478C0.847875 0.648438 0 1.49631 0 2.54312V18.9642H5.68425V6.33269H18.3158V0.648438Z" fill="#4285F4"></path>
                                <path d="M8.27431 16.1313C7.80219 15.8124 7.47528 15.3467 7.29688 14.7307L8.39272 14.2792C8.49209 14.6582 8.66578 14.9518 8.91378 15.1603C9.16006 15.3687 9.46006 15.4713 9.81059 15.4713C10.169 15.4713 10.4768 15.3624 10.7342 15.1444C10.9917 14.9266 11.1211 14.6487 11.1211 14.3124C11.1211 13.9682 10.9853 13.6871 10.7138 13.4692C10.4422 13.2513 10.1011 13.1424 9.69378 13.1424H9.06059V12.0577H9.629C9.9795 12.0577 10.2747 11.9629 10.5147 11.7734C10.7547 11.584 10.8748 11.325 10.8748 10.995C10.8748 10.7013 10.7674 10.4676 10.5527 10.2924C10.338 10.1171 10.0663 10.0287 9.73634 10.0287C9.41422 10.0287 9.15847 10.114 8.969 10.286C8.77959 10.4586 8.63721 10.6765 8.55528 10.9192L7.47059 10.4676C7.61422 10.0603 7.87794 9.70028 8.26475 9.38922C8.65156 9.07816 9.14581 8.92188 9.74581 8.92188C10.1895 8.92188 10.589 9.00719 10.9427 9.17922C11.2963 9.35134 11.5742 9.58975 11.7747 9.89294C11.9754 10.1976 12.0747 10.5387 12.0747 10.9176C12.0747 11.3044 11.9817 11.6313 11.7954 11.8997C11.6091 12.1682 11.3801 12.3734 11.1085 12.5171V12.5818C11.4591 12.7264 11.7638 12.9639 11.9895 13.2687C12.2185 13.5766 12.3338 13.9444 12.3338 14.374C12.3338 14.8034 12.2247 15.1871 12.0069 15.5234C11.789 15.8598 11.4875 16.125 11.1053 16.3176C10.7217 16.5103 10.2906 16.6082 9.81219 16.6082C9.25794 16.6097 8.74644 16.4503 8.27431 16.1313ZM15.0052 10.6934L13.8021 11.5634L13.2005 10.6508L15.3589 9.09391H16.1862V16.4376H15.0052V10.6934Z" fill="#4285F4"></path>
                            </g>
                            <defs>
                                <clipPath id="clip0_4133_8923">
                                    <rect width="24" height="24" fill="white" transform="translate(0 0.648438)"></rect>
                                </clipPath>
                            </defs>
                        </svg>
                    </span>
                </a>

                <a href="<?= site_url("calendar.php?sans=$sans_time&description=$url&location=$address&summery=$summery"); ?>" class="text-gray-900 relative flex h-14 min-w-16 items-center justify-center gap-4 rounded-lg border border-gray-100 bg-white px-0 py-2 text-sm font-semibold shadow-13 transition-all duration-300 ease-in-out focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 disabled:cursor-not-allowed disabled:bg-slate-110 disabled:text-disabled disabled:shadow-none px-6 py-3">
                    افزودن به تقویم آیفون
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="25" viewBox="0 0 24 25" fill="none">
                            <path d="M17.9974 22.7658C16.8344 23.9267 15.5645 23.7434 14.3422 23.1935C13.0486 22.6313 11.8618 22.6069 10.497 23.1935C8.78802 23.9512 7.88607 23.7312 6.86544 22.7658C1.07395 16.6188 1.92843 7.25773 8.5032 6.91555C10.1054 7.0011 11.2209 7.81988 12.1585 7.8932C13.5589 7.59991 14.8999 6.75668 16.3953 6.86667C18.1873 7.01332 19.5403 7.74656 20.4303 9.06639C16.7276 11.3517 17.6058 16.3744 21 17.7797C20.3235 19.6128 19.4453 21.4337 17.9856 22.778L17.9974 22.7658ZM12.0398 6.84223C11.8618 4.11701 14.0099 1.86841 16.4784 1.64844C16.8225 4.80137 13.7013 7.14774 12.0398 6.84223Z" fill="black"></path>
                        </svg>
                    </span>
                </a>

            </div>
            <div class="grid grid-cols-2 gap-4">
                <button id="ticket_download" type="button" class="flex gap-4 items-center justify-center relative text-sm font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 transition-all duration-300 ease-in-out disabled:bg-slate-110 disabled:text-disabled disabled:cursor-not-allowed disabled:shadow-none bg-primary-600 text-white shadow-14 hover:bg-primary-500 focus-visible:outline-primary-600 min-w-16 py-2 h-14 rounded-lg px-0 lg:px-6">
                    <span class="truncate">دانلود بلیت</span>
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tabler-icon tabler-icon-download text-primary-500 text-white">
                            <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2"></path>
                            <path d="M7 11l5 5l5 -5"></path>
                            <path d="M12 4l0 12"></path>
                        </svg>
                    </span>
                </button>
                <?php $share_text = "$summery
تاریخ شروع: $order_date 
زمان شروع: $order_time
تعداد: {$product['quantity']}"; ?>
                <button type="button" data-title="<?= $summery; ?>" data-content="<?= $share_text; ?>" data-url="<?= $url; ?>" class="share flex gap-4 items-center justify-center relative text-sm font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 transition-all duration-300 ease-in-out disabled:bg-slate-110 disabled:text-disabled disabled:cursor-not-allowed disabled:shadow-none bg-gray-20 text-gray-900 shadow-13 border border-gray-100 min-w-16 py-2 h-14 rounded-lg px-0 lg:px-6 lg:py-3">
                    <span class="truncate">اشتراک گذاری</span>
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tabler-icon tabler-icon-share-3 text-primary-500">
                            <path d="M13 4v4c-6.575 1.028 -9.02 6.788 -10 12c-.037 .206 5.384 -5.962 10 -6v4l8 -7l-8 -7z"></path>
                        </svg>
                    </span>
                </button>
            </div>
        </div>
    </div>

    <?php if ($product['rules']) { ?>
        <div class="mt-10">
            <h2 class="mb-4 text-xl">قوانین و مقررات</h2>
            <div class="post-content"><?= $product['rules']; ?></div>
        </div>
    <?php } ?>

    <section style="margin-top: 500px;display: none;">
        <div id="ticket_to_download" style="display: none;width: 880px;background:#fff">
            <div class="relative justify-between rounded-xl border border-slate-120 shadow-13 flex px-8">
                <div class="flex flex-wrap items-center justify-between py-12">
                    <div class="flex gap-x-5 px-6">
                        <div class="mt-5">
                            <svg class="h-10 w-10 drop-shadow-[2px_6px_10px_rgba(0,0,0,0.25)]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 45" fill="none">
                                <rect y="0.648438" width="40" height="40" rx="20" fill="url(#paint0_linear_4133_31825)"></rect>
                                <g filter="url(#filter0_d_4133_31825)">
                                    <path d="M29.7203 33.945C27.8032 30.5566 25.887 27.1673 23.9578 23.7576C24.7493 22.9473 25.3304 22.0252 25.647 20.9399C26.6994 17.3296 24.5014 13.5276 20.9283 12.7857C17.2655 12.0251 13.8142 14.5411 13.3575 18.3049C12.82 22.737 16.7402 26.3339 21.044 25.3506C21.2571 25.3018 21.4102 25.2601 21.5467 25.5077C22.2521 26.7812 22.9741 28.0441 23.689 29.3123C23.716 29.3603 23.7273 29.4188 23.7612 29.5173C23.6403 29.5244 23.5473 29.5369 23.4542 29.5351C21.8468 29.5156 20.2342 29.575 18.6329 29.4579C14.223 29.1357 10.4045 25.5911 9.57038 21.136C8.49791 15.4082 12.0319 10.0141 17.6343 8.83021C23.3185 7.62856 28.9792 11.7438 29.7307 17.6304C29.7907 18.0981 29.8273 18.5729 29.8281 19.0442C29.8351 23.881 29.8325 28.7168 29.8316 33.5536C29.8316 33.6796 29.8168 33.8065 29.809 33.9325C29.7794 33.937 29.7498 33.9405 29.7203 33.945Z" fill="url(#paint1_linear_4133_31825)"></path>
                                    <path d="M20.2151 14.148C19.2113 15.1553 18.2832 16.0845 17.3577 17.0163C17.0585 17.3172 16.7471 17.6074 16.4749 17.9313C16.2235 18.2304 16.0156 18.5685 15.7164 18.9918C15.4885 18.9528 15.1119 18.8871 14.7388 18.8232C14.8214 15.9797 17.4065 13.7841 20.2151 14.148Z" fill="url(#paint2_linear_4133_31825)"></path>
                                </g>
                                <defs>
                                    <filter id="filter0_d_4133_31825" x="4.37891" y="8.62109" width="30.4531" height="36.3242" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                        <feFlood flood-opacity="0" result="BackgroundImageFix"></feFlood>
                                        <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"></feColorMatrix>
                                        <feOffset dy="6"></feOffset>
                                        <feGaussianBlur stdDeviation="2.5"></feGaussianBlur>
                                        <feComposite in2="hardAlpha" operator="out"></feComposite>
                                        <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0"></feColorMatrix>
                                        <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_4133_31825"></feBlend>
                                        <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_4133_31825" result="shape"></feBlend>
                                    </filter>
                                    <linearGradient id="paint0_linear_4133_31825" x1="19.726" y1="12.3726" x2="12.8153" y2="45.6297" gradientUnits="userSpaceOnUse">
                                        <stop stop-color="#FC6F13"></stop>
                                        <stop offset="1" stop-color="#D75602"></stop>
                                    </linearGradient>
                                    <linearGradient id="paint1_linear_4133_31825" x1="16.733" y1="22.4343" x2="14.5179" y2="28.202" gradientUnits="userSpaceOnUse">
                                        <stop stop-color="white"></stop>
                                        <stop offset="1" stop-color="#DBDBDB"></stop>
                                    </linearGradient>
                                    <linearGradient id="paint2_linear_4133_31825" x1="16.733" y1="22.4343" x2="14.5179" y2="28.202" gradientUnits="userSpaceOnUse">
                                        <stop stop-color="white"></stop>
                                        <stop offset="1" stop-color="#DBDBDB"></stop>
                                    </linearGradient>
                                </defs>
                            </svg>
                        </div>
                        <div class="space-y-4">
                            <div class="text-xl font-bold text-text-3"><?php echo $product['meta']->product_type ?></div>
                            <div class="space-y-4 flex items-center">
                                <a class="text-4xl font-extrabold text-slate-700 mt-1.5" href="<?php echo esc_url($product['url']); ?>"><?php echo esc_html($product['title']); ?></a>
                                <div class="flex items-center gap-x-4 -mt-2 mr-4">
                                    <svg class="m-0 h-4 w-4 lg:-mt-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 17" fill="none">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M0.38999 1.05549C0.639775 0.805527 0.978512 0.665107 1.33171 0.665107C1.68491 0.665107 2.02364 0.805527 2.27343 1.05549L7.99167 6.77949L13.7099 1.05549C13.8328 0.928142 13.9798 0.826565 14.1423 0.756687C14.3048 0.686808 14.4796 0.650026 14.6564 0.648488C14.8333 0.646949 15.0087 0.680685 15.1724 0.747726C15.3361 0.814767 15.4848 0.913771 15.6099 1.03896C15.7349 1.16415 15.8338 1.31302 15.9008 1.47688C15.9678 1.64074 16.0015 1.81632 15.9999 1.99336C15.9984 2.1704 15.9617 2.34536 15.8919 2.50803C15.8221 2.6707 15.7206 2.81783 15.5934 2.94082L9.87511 8.66482L15.5934 14.3888C15.836 14.6403 15.9702 14.9771 15.9672 15.3267C15.9642 15.6763 15.8241 16.0107 15.5771 16.2579C15.3302 16.5051 14.9961 16.6453 14.6468 16.6484C14.2976 16.6514 13.9611 16.517 13.7099 16.2742L7.99167 10.5502L2.27343 16.2742C2.02221 16.517 1.68575 16.6514 1.3365 16.6484C0.987258 16.6453 0.653177 16.5051 0.406215 16.2579C0.159253 16.0107 0.0191681 15.6763 0.0161333 15.3267C0.0130984 14.9771 0.147356 14.6403 0.38999 14.3888L6.10824 8.66482L0.38999 2.94082C0.14028 2.69078 0 2.35171 0 1.99816C0 1.6446 0.14028 1.30553 0.38999 1.05549Z" fill="black"></path>
                                    </svg>
                                    <span class="text-4xl font-extrabold text-primary-2"><?php echo esc_html($product['quantity']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="my-5 px-6">
                        <div class="flex justify-center gap-x-12 flex-col items-end gap-y-2"><span class="text-2xl font-bold text-primary-2 order-2"><?php echo esc_html($order_date); ?></span><bdo dir="ltr" class="text-2xl font-bold text-slate-700 order-1"><?php echo esc_html($order_time); ?></bdo></div>
                    </div>
                    <div class="flex justify-between gap-x-4 px-6 pt-12 mt-12.5 w-full border-t border-t-slate-105">
                        <div class="w-full">
                            <div class="flex items-center gap-x-5">
                                <h2 class="text-xl font-bold text-slate-700"><?php echo esc_html($brand_data->name); ?></h2>
                                <div class="space-x-4 space-x-reverse text-lg"><bdo dir="ltr"><?php echo esc_html(get_field('room_phone', $product['id'])); ?></bdo><bdo dir="ltr"></bdo></div>
                                <div class="space-x-4 space-x-reverse text-lg"><bdo dir="ltr"><?php echo esc_html(get_field('room_phone_2', $product['id'])); ?></bdo><bdo dir="ltr"></bdo></div>
                            </div>
                            <p class="font-bold my-2"><?php echo $address; ?></p>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-1 -my-4 flex-col px-10">
                    <div class="h-8 min-h-8 w-8 min-w-8 -rotate-45 rounded-full border border-b-slate-120 border-l-slate-120 border-r-white border-t-white bg-background"></div>
                    <div class="bg-dashed-vertical bg-[length:100%_16px] h-full w-0.5"></div>
                    <div class="h-8 min-h-8 w-8 min-w-8 rotate-45 rounded-full border border-b-white border-l-slate-120 border-r-white border-t-slate-120 bg-background shadow-[-1px_-1px_0_0px_#dce3ea]"></div>
                </div>
                <div class="px-6 content-stretch space-y-10 py-12">
                    <?php
                    // محاسبه مانده پرداخت؛ اگر منفی شد، صفر در نظر گرفته می‌شود
                    $remaining_payment = $item_total - (int) $prepaid;
                    if ($remaining_payment < 0) {
                        $remaining_payment = 0;
                    }
                    ?>
                    <div class="flex justify-between flex-col items-end gap-y-1">
                        <div class="text-nowrap text-sm font-bold">مبلغ کل</div>
                        <div class="space-y-1"><span class="text-xl font-bold block text-left"><?php echo number_format($item_total) ?></span><span class="mr-1.5 text-xs font-bold block text-left">تومان</span></div>
                    </div>
                    <div class="flex justify-between flex-col items-end gap-y-1">
                        <div class="text-nowrap text-sm font-bold">پیش پرداخت</div>
                        <div class="space-y-1"><span class="text-xl font-bold block text-left"><?php echo number_format($prepaid) ?></span><span class="mr-1.5 text-xs font-bold block text-left">تومان</span></div>
                    </div>
                    <?php if ($remaining_payment > 0) : ?>
                        <div class="flex justify-between flex-col items-end gap-y-1">
                            <div class="text-nowrap text-sm font-bold">مانده پرداخت</div>
                            <div class="space-y-1"><span class="text-xl font-bold block text-left"><?php echo number_format($remaining_payment); ?></span><span class="mr-1.5 text-xs font-bold block text-left">تومان</span></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/dom-to-image/2.6.0/dom-to-image.min.js"></script>
<script>
    jQuery(document).ready(function($) {

        setTimeout(function() {
            if (window.location.search.indexOf('download') !== -1)
                $('#ticket_download').click();
        }, 1);

        $('body').on('click', '#ticket_download', function() {

            var element = document.querySelector('#ticket_to_download');

            $(element).css('display', 'block');
            $(element).parent().css('display', 'block');

            domtoimage.toPng(element)
                .then(function(dataUrl) {
                    var img = new Image();
                    img.src = dataUrl;
                    img.onload = function() {
                        var canvas = document.createElement('canvas');
                        var context = canvas.getContext('2d');

                        canvas.width = img.height;
                        canvas.height = img.width;

                        context.translate(canvas.width / 2, canvas.height / 2);
                        context.rotate(90 * Math.PI / 180);
                        context.drawImage(img, -img.width / 2, -img.height / 2);

                        var link = document.createElement('a');
                        link.href = canvas.toDataURL('image/png');

                        const now = new Date();
                        const datePart = now.toISOString().split('T')[0]; // "2025-04-16"
                        const timePart = now.toTimeString().split(' ')[0].replace(/:/g, '-'); // "12-30-45"
                        link.download = `escapezoom_${datePart}__${timePart}.png`;

                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);

                        $(element).css('display', 'none');
                        $(element).parent().css('display', 'none');
                    };
                })
                .catch(function(error) {
                    console.error('Oops, something went wrong!', error);
                });
        });
    });
</script>

<?php get_footer();

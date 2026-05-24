<?php

/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.4.0
 * @var $checkout
 */

if (! defined('ABSPATH')) {
    exit;
}

global $wp;


// Redirect To Login Page And Back To Check Out Page
if (! is_user_logged_in()) {
    $url = site_url('panel?redirect=' . urlencode(home_url($wp->request) . '/?' . $_SERVER['QUERY_STRING']));
    wp_redirect($url, 301);
    exit();
}

if ($_GET['book'] < time()) {
    $reorder_url = home_url($_GET['add-to-cart']); ?>

    <section
        class="mt-5 rounded-xl border border-solid border-gray-200 px-6 pb-9 pt-7 shadow-card-lip lg:p-13 bg-breserve">
        <div class="flex flex-col justify-center items-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="258" height="259" viewBox="0 0 258 259" fill="none">
                <g filter="url(#filter0_d_31802_12693)">
                    <circle cx="121" cy="111.648" r="105" fill="url(#paint0_linear_31802_12693)"></circle>
                </g>
                <path
                    d="M79.999 88.149C74.9222 83.0722 74.9222 74.8411 79.999 69.7642C85.0758 64.6874 97.7086 70.3798 102.785 75.4566L160.499 130.649C165.576 134.726 170.1 147.149 163.597 153.653C158.52 158.729 150.289 158.729 145.212 153.653L79.999 88.149Z"
                    fill="#D11038"></path>
                <path
                    d="M161.499 95.149C168.999 86.149 163.999 71.649 158.499 68.149C153.422 63.0722 150.08 70.3798 145.003 75.4566L84.1916 136.268C79.1148 141.345 71.4999 139.649 84.1929 153.149C88.9999 157.649 97.5 159.149 102.576 154.653L161.499 95.149Z"
                    fill="#D11038"></path>
                <g filter="url(#filter1_i_31802_12693)">
                    <path
                        d="M140.619 68.456C145.696 63.3793 153.927 63.3792 159.004 68.456C164.08 73.5327 164.08 81.764 159.004 86.8407L138.2 107.643C137.81 108.034 137.81 108.667 138.2 109.058L159.004 129.861C164.08 134.938 164.08 143.169 159.004 148.246C153.927 153.323 145.696 153.323 140.619 148.246L119.815 127.442C119.425 127.052 118.792 127.052 118.401 127.442L98.1923 147.652C93.1155 152.729 84.8843 152.729 79.8075 147.652C74.7308 142.575 74.7309 134.344 79.8075 129.267L100.016 109.058C100.407 108.667 100.407 108.034 100.016 107.643L79.8075 87.4355C74.7308 82.3587 74.7309 74.1275 79.8075 69.0507C84.8843 63.9739 93.1155 63.9739 98.1923 69.0507L118.401 89.2587C118.792 89.6492 119.425 89.6492 119.815 89.2587L140.619 68.456Z"
                        fill="url(#paint1_linear_31802_12693)"></path>
                </g>
                <defs>
                    <filter id="filter0_d_31802_12693" x="0" y="0.648438" width="258" height="258"
                            filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                        <feFlood flood-opacity="0" result="BackgroundImageFix"></feFlood>
                        <feColorMatrix in="SourceAlpha" type="matrix"
                                       values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha">
                        </feColorMatrix>
                        <feOffset dx="8" dy="18"></feOffset>
                        <feGaussianBlur stdDeviation="12"></feGaussianBlur>
                        <feComposite in2="hardAlpha" operator="out"></feComposite>
                        <feColorMatrix type="matrix"
                                       values="0 0 0 0 0.306354 0 0 0 0 0.36728 0 0 0 0 0.425 0 0 0 0.08 0">
                        </feColorMatrix>
                        <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_31802_12693">
                        </feBlend>
                        <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_31802_12693"
                                 result="shape"></feBlend>
                    </filter>
                    <filter id="filter1_i_31802_12693" x="76" y="61.6484" width="86.8115" height="90.4043"
                            filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                        <feFlood flood-opacity="0" result="BackgroundImageFix"></feFlood>
                        <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape">
                        </feBlend>
                        <feColorMatrix in="SourceAlpha" type="matrix"
                                       values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha">
                        </feColorMatrix>
                        <feOffset dy="-3"></feOffset>
                        <feGaussianBlur stdDeviation="1.5"></feGaussianBlur>
                        <feComposite in2="hardAlpha" operator="arithmetic" k2="-1" k3="1"></feComposite>
                        <feColorMatrix type="matrix"
                                       values="0 0 0 0 0.819608 0 0 0 0 0.0627451 0 0 0 0 0.219608 0 0 0 1 0">
                        </feColorMatrix>
                        <feBlend mode="normal" in2="shape" result="effect1_innerShadow_31802_12693"></feBlend>
                    </filter>
                    <linearGradient id="paint0_linear_31802_12693" x1="88.1044" y1="47.5187" x2="-69.0633"
                                    y2="-181.421" gradientUnits="userSpaceOnUse">
                        <stop stop-color="white"></stop>
                        <stop offset="1" stop-color="#889BAD"></stop>
                    </linearGradient>
                    <linearGradient id="paint1_linear_31802_12693" x1="231.827" y1="63.3349" x2="135.119"
                                    y2="61.0114" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#F21543"></stop>
                        <stop offset="1" stop-color="#FD2F5A"></stop>
                    </linearGradient>
                </defs>
            </svg>
            <div class="text-bold-h5 font-bold">رزرو شما متاسفانه <span class="text-txError">ناموفق</span> بود!
            </div>
            <div
                class="text-text-bold-s3 font-bold flex justify-center items-center border border-solid border-gray-200 sahdow-1 lg:w-d556 w-d326 h-d86 rounded-xl shadow-1 mt-6">
                کد خطای ۳۵:&nbsp;زمان این سانس منقضی شده است.</div>
            <div class="flex flex-col lg:flex-row gap-5 mt-9">
                <a href="<?php echo $reorder_url; ?>" class="bg-bgPrimary shadow-btn-org w-d326 lg:w-d268 h-d56 flex justify-center items-center gap-11 text-white rounded-lg">
                    اقدام مجدد <svg xmlns="http://www.w3.org/2000/svg" width="24" height="25" viewBox="0 0 24 25" fill="none">
                        <path d="M11.8522 21.5373C11.8522 21.832 11.7401 22.1146 11.5404 22.323C11.3408 22.5314 11.07 22.6484 10.7877 22.6484C5.40561 22.6484 1 18.1966 1 12.6484C1 7.10029 5.40561 2.64844 10.7877 2.64844C15.1947 2.64844 18.9475 5.63362 20.1624 9.76103L21.0183 8.24992C21.1619 7.9961 21.3962 7.81222 21.6697 7.73873C21.9432 7.66524 22.2335 7.70817 22.4766 7.85807C22.7198 8.00796 22.896 8.25255 22.9664 8.53802C23.0368 8.8235 22.9957 9.12647 22.852 9.38029L20.5385 13.4618C20.3969 13.7121 20.1668 13.8946 19.8977 13.9699C19.6287 14.0453 19.342 14.0075 19.0993 13.8647L15.094 11.5077C14.9722 11.436 14.8651 11.34 14.7788 11.2251C14.6926 11.1102 14.6289 10.9787 14.5913 10.8381C14.5537 10.6975 14.5431 10.5506 14.5599 10.4057C14.5768 10.2608 14.6208 10.1208 14.6895 9.99362C14.7581 9.86648 14.8502 9.75471 14.9602 9.66468C15.0703 9.57466 15.1963 9.50814 15.331 9.46894C15.4657 9.42973 15.6064 9.4186 15.7453 9.43618C15.8841 9.45376 16.0182 9.49971 16.14 9.5714L18.2293 10.801C17.4146 7.4114 14.4099 4.87066 10.7891 4.87066C6.53398 4.87066 3.129 8.37733 3.129 12.6484C3.129 16.9195 6.53398 20.4262 10.7877 20.4262C11.07 20.4262 11.3408 20.5433 11.5404 20.7516C11.7401 20.96 11.8522 21.2426 11.8522 21.5373Z"
                              fill="white"></path>
                    </svg>
                </a>
                <button
                    class="bg-white shadow-1 w-d326 lg:w-d268 h-d56 flex justify-center items-center gap-11 rounded-lg">
                    تماس با پشتیبانی <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 25 25" fill="none">
                        <path
                            d="M16.1002 15.1707C13.7052 17.6907 7.5964 11.6366 10.0003 9.10661C11.4683 7.56159 9.81034 5.79657 8.89237 4.49756C7.16942 2.06254 3.38852 5.42457 3.50252 7.56359C3.86551 14.3097 11.1623 22.3037 18.2281 21.6057C20.438 21.3877 22.978 17.3957 20.442 15.9367C19.1751 15.2067 17.4341 13.7667 16.1002 15.1697M14.5002 3.64855C16.3567 3.64855 18.1371 4.38606 19.4498 5.69882C20.7625 7.01159 21.5 8.79209 21.5 10.6486M14.5002 7.64859C15.2958 7.64859 16.0589 7.96466 16.6215 8.52728C17.1841 9.08989 17.5001 9.85296 17.5001 10.6486"
                            stroke="#FD7013" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        </path>
                    </svg>
                </button>
            </div>
        </div>
    </section>

    <?php
    //    echo 'زمان این سانس گذشته است';
    return;
} ?>

<?php do_action('woocommerce_before_checkout_form', $checkout); ?>

<?php

$user       = wp_get_current_user();
$product    = wc_get_product(htmlspecialchars($_GET['add-to-cart']));
$time       = htmlspecialchars($_GET['book']);
$quantity   = isset($_GET['quantity']) ? (int) $_GET['quantity'] : 0;
$product_id = $product->get_id();

if ( $product_id == 5104 )
    if ( !in_array(get_current_user_id(), [3325, 3346, 2, 80,7620]) )
        die('این اتاق فرار به آمریکا منتقل شده!');

if ( $quantity <= 0 ) {
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item)
        $quantity = $cart_item['quantity'];
}

$terms = get_the_terms($product_id, 'product_cat');
if (count($terms) > 1) {
    foreach ($terms as $term) {
        if ($term->parent == 0) {
            $product_type           = $term->name;
            $product_parent_cat_url = get_term_link($term->term_id, "product_cat");
        } else {
            $city_name       = $term->name;
            $city_id         = $term->term_id;
            $product_cat_url = get_term_link($term->term_id, "product_cat");
        }
    }
} else {
    $product_type           = get_term($terms[0]->parent)->name;
    $city_name              = $terms[0]->name;
    $city_id                = $terms[0]->term_id;
    $product_parent_cat_url = get_term_link($terms[0]->parent, "product_cat");
    $product_cat_url        = get_term_link($terms[0]->term_id, "product_cat");
}
foreach (get_the_terms($product_id, 'product_tag') as $product_tag) {
    if (str_contains($product_tag->name, '|||||')) {
        $genres[] = [
            'title' => str_replace('|||||', '', $product_tag->name),
            'id'    => $product_tag->term_id,
            'url'   => get_term_link($product_tag->term_id),
        ];
    } else {
        $tags[] = [
            'title' => $product_tag->name,
            'id'    => $product_tag->term_id,
            'url'   => get_term_link($product_tag->term_id),
        ];
    }
}

$titles = [];
if ($genres)
    $titles = array_column($genres, 'title');

$genres_title = implode('-', $titles);

if ($time) {
    $uid = get_current_user_id();
    if ($uid && function_exists('ez_resolver_attempt_from_get_cart') && function_exists('ez_resolve_pending_booking_for_checkout')) {
        $resolver_attempt = ez_resolver_attempt_from_get_cart((int) $product_id, (string) $time, max(1, (int) $quantity));
        if (!empty($resolver_attempt)) {
            $slot_res = ez_resolve_pending_booking_for_checkout(
                [
                    'customer_id'       => (int) $uid,
                    'phone_normalized' => '',
                    'product_id'       => (int) $product_id,
                    'sans_ts'          => is_numeric($time) ? (string)(int)$time : '',
                    'exclude_order_id' => 0,
                    'attempt'          => $resolver_attempt,
                ]
            );
            if ('reuse' === $slot_res['status'] && !empty($slot_res['payment_url'])) {
                wp_safe_redirect($slot_res['payment_url']);
                exit();
            }
        } elseif ($uid && function_exists('ez_customer_pending_order_same_slot') && ez_customer_pending_order_same_slot($uid, $product_id, $time, 0)) { ?>

        <style>
            img.oops-img { display: block; margin: 0 auto; }
            .btn-poshtibani {
                background: #f96f0c; color: #fff !important; padding: 10px; border-radius: 5px;
                float: left; width: 132px; margin-left: 2%;
            }
            .btn-other {
                background: #00b350; color: #fff !important; padding: 10px; border-radius: 5px;
                float: right; width: 132px; margin-right: 2%;
            }
        </style>
        <div class="container">
            <div class="row mb-5">
                <div class="col-12 mb-3">
                    <img src="https://escapezoom.ir/wp-content/uploads/2022/01/oops.png" class="oops-img" alt="">
                    <p style="font-size: 1.3em; text-align: center; padding-top: 16px;">
                        برای این سانس یک سفارش در انتظار پرداخت از قبل ثبت کرده‌اید. همان را تکمیل کنید یا با پشتیبانی تماس بگیرید.
                    </p>
                </div>
                <div class="col-6"><a href="tel:+989925101507" class="btn-poshtibani">تماس با پشتیبانی</a></div>
                <div class="col-6"><a class="btn-other" href="<?php echo esc_url(wc_get_account_endpoint_url('orders')); ?>">سفارش‌های من</a></div>
            </div>
        </div>
        <?php
        get_footer();
        exit();
        }
    }

    $conflict_row_booking = function_exists( 'ez_booking_first_confirmed_conflict_row' )
        ? ez_booking_first_confirmed_conflict_row( (int) $product_id, (int) $time, 0 )
        : null;
    $row_check            = ! empty( $conflict_row_booking );
    $same_viewer_checkout = $row_check && $uid && function_exists( 'ez_booking_conflict_row_is_same_viewer' )
        ? ez_booking_conflict_row_is_same_viewer( $conflict_row_booking, (int) $uid, '' )
        : false;

    if (!empty($row_check)) { ?>

        <style>
            img.oops-img {
                display: block;
                margin: 0 auto;
            }
            .btn-poshtibani {
                background: #f96f0c;
                color: #fff !important;
                padding: 10px;
                border-radius: 5px;
                float: left;
                width: 132px;
                margin-left: 2%;
            }

            .btn-other {
                background: #00b350;
                color: #fff !important;
                padding: 10px;
                border-radius: 5px;
                float: right;
                width: 132px;
                margin-right: 2%;
            }

            .checkout_players_phone_field label {
                display: flex !important;
            }

            .woocommerce-checkout input {
                color: #202020 !important;
            }

            #main div>div>form>div>div.zardkooh-checkout-right>p:nth-child(2) {
                display: none;
            }

            .woocommerce-checkout #payment ul.payment_methods li label {
                font-family: 'dana';
            }

            .cart-discount td,
            .cart-discount th {
                color: green !important;
                font-size: 13px !important;
                font-weight: bold !important;
            }
        </style>

        <div class="container">
            <div class="row mb-5">
                <div class="col-12 mb-3">
                    <img src="https://escapezoom.ir/wp-content/uploads/2022/01/oops.png" class="oops-img">
                    <p style=" font-size: 1.3em; text-align: center; padding-top: 16px; ">
                        <?php if ( ! empty( $same_viewer_checkout ) ) : ?>
                            این سانس را قبلاً با یکی از سفارش‌های خودتان ثبت کرده‌اید. برای همان تاریخ و بازی امکان رزرو دوباره وجود ندارد؛ از بخش سفارش‌های حساب خود وضعیت را ببینید یا سانس دیگری انتخاب کنید.
                        <?php else : ?>
                            اوه! یکی سریع‌تر از شما بود و این سانس را زودتر رزرو کرد!
                        <?php endif; ?>
                    </p>
                </div>

                <div class="col-6"><a href="tel:+989925101507" class="btn-poshtibani">تماس با پشتیبانی</a></div>
                <div class="col-6"><a class="btn-other" href="<?php the_permalink("$product_id"); ?>">رزرو یک سانس دیگر</a>
                </div>
            </div>
        </div>
        <?php
        get_footer();
        exit();
    }
}


?>

    <style>
        .woocommerce-message {
            display: none;
        }

        ul.woocommerce-error {
            color: red;
        }
        .woocommerce-Price-currencySymbol,
        .woocommerce-remove-coupon {
            margin-right: 2px;
            font-size: 14px;
        }
    </style>

    <section>
        <div class="flex items-center gap-x-4 md:block">
            <div class="grow lg:mt-8">
                <h2 class="mr-4 ml-2 font-semibold text-2xl">تسویه حساب</h2>
            </div>
            <div class="border border-border-1 rounded-xl p-3 lg:py-6 flex items-center bg-white md:hidden shadow-13">
                <a href="<?php echo get_permalink($product->get_id()); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="29" height="28" viewBox="0 0 29 28" fill="none">
                        <path d="M9.30072 16.8569L15.4877 23.463C15.9496 23.8736 16.5609 24.0972 17.1927 24.0868C17.8244 24.0764 18.4272 23.8328 18.874 23.4073C19.3208 22.9818 19.5766 22.4078 19.5875 21.8061C19.5984 21.2045 19.3636 20.6223 18.9325 20.1824L14.468 15.2166L18.9325 10.8176C19.3636 10.3777 19.5984 9.79549 19.5875 9.19386C19.5766 8.59222 19.3208 8.01814 18.874 7.59264C18.4272 7.16717 17.8244 6.92355 17.1927 6.91316C16.5609 6.90276 15.9496 7.12641 15.4877 7.53693L9.30072 13.5763C8.84427 14.0115 8.58789 14.6015 8.58789 15.2166C8.58789 15.8317 8.84427 16.4217 9.30072 16.8569Z" fill="#FD7013" />
                    </svg>
                </a>
            </div>
        </div>
        <div class="md:flex md:gap-x-4 mt-5">
            <div class="border border-border-1 rounded-xl p-5 md:py-4 flex flex-wrap items-center justify-between lg:justify-around bg-white grow font-medium text-lg shadow-13">
                <div class="w-full lg:w-auto flex max-lg:flex-col gap-x-4 mb-5 lg:mb-0">
                    <div class="text-gray-600 font-semibold">
                        <span class="text-sm md:text-lg">تکمیل رزرو</span>
                        <span><?= $product_type ?></span>
                    </div>
                    <div class="font-bold"><?php echo $product->get_title(); ?></div>
                </div>
                <div class="flex gap-x-6 items-center">
                    <div>
                        <?php echo jdate('l', $time); ?>
                        <span class="text-primary-500"><?php echo jdate('j', $time); ?></span>
                        <?php echo jdate('F', $time); ?>
                    </div>
                    <bdo dir="ltr"><?php echo jdate('H:i', $time); ?></bdo>
                </div>
                <div><?php echo esc_html($quantity); ?> بلیت</div>
            </div>
            <div class="hidden md:border md:border-border-1 md:rounded-xl px-1 md:px-4 md:py-4 md:flex md:items-center md:bg-white shadow-13">
                <a href="<?php echo get_permalink($product->get_id()); ?>" class="flex items-center gap-x-1 lg:gap-x-4 text-neutral-888 hover:text-primary-2 transition">
                    <span class="font-medium mt-1 text-nowrap">بازگشت و ویرایش</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="29" height="28" viewBox="0 0 29 28" fill="none">
                        <path d="M9.30072 16.8569L15.4877 23.463C15.9496 23.8736 16.5609 24.0972 17.1927 24.0868C17.8244 24.0764 18.4272 23.8328 18.874 23.4073C19.3208 22.9818 19.5766 22.4078 19.5875 21.8061C19.5984 21.2045 19.3636 20.6223 18.9325 20.1824L14.468 15.2166L18.9325 10.8176C19.3636 10.3777 19.5984 9.79549 19.5875 9.19386C19.5766 8.59222 19.3208 8.01814 18.874 7.59264C18.4272 7.16717 17.8244 6.92355 17.1927 6.91316C16.5609 6.90276 15.9496 7.12641 15.4877 7.53693L9.30072 13.5763C8.84427 14.0115 8.58789 14.6015 8.58789 15.2166C8.58789 15.8317 8.84427 16.4217 9.30072 16.8569Z" fill="#FD7013" />
                    </svg>
                </a>
            </div>
        </div>

        <!--<div class="md:flex md:gap-x-4 mt-5">-->
        <!--    <div class="border border-border-1 rounded-xl p-5 md:py-4 flex flex-wrap items-center justify-between lg:justify-around bg-white grow font-medium text-lg shadow-13">-->
        <!--        <div style="color: red;">مهم: با توجه به قطعی سامانه های پیامکی، لازم است پس از خرید، تیکت خود را دانلود نموده و با شماره موبایل موجود در تیکت برای هماهنگی بیشتر  تماس بگیرید.</div>-->
        <!--    </div>-->
        <!--</div>-->

        <div id="payment-form" class="flex flex-col md:flex-row gap-5 mt-5">

            <div class="md:w-1/2 lg:w-7/12 flex flex-col gap-y-6">

                <div class="border border-border-1 rounded-xl px-4 py-4 lg:py-6 bg-white shadow-13">
                    <h2 class="font-semibold text-lg">جزئیات رزرو</h2>
                    <div class="grid lg:grid-cols-2 mt-5">
                        <div class="flex flex-col">
                            <span class="text-text-3 text-sm font-light-yekanbakh font-light">نام و نام خانوادگی</span>
                            <span>
                            <?php echo $user->first_name; ?>
                            <?php echo $user->last_name; ?>
                        </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-end gap-x-2">
                                <div class="flex flex-col">
                                    <span class="text-text-3 text-sm font-light-yekanbakh font-light">شماره همراه</span>
                                    <bdo dir="ltr"><?php echo $user->user_login ?></bdo>
                                </div>
                                <div class="bg-accent-20 rounded-md flex items-center justify-center gap-x-1.5 px-2 w-fit h-6 mb-2">
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="16" viewBox="0 0 15 16" fill="none">
                                        <circle cx="7.5" cy="8" r="7.5" fill="#02C96F" />
                                        <path d="M4 8.5L5.72397 10.1888C5.90512 10.3662 6.19043 10.38 6.3878 10.2208L11 6.5" stroke="white" stroke-width="1.5" stroke-linecap="round" />
                                    </svg>
                                </span>
                                    <span class="text-xs text-accent-450">تأیید شده</span>
                                </div>
                            </div>
                            <a href="<?php echo esc_url(site_url('panel/settings')); ?>" class="border border-slate-105 rounded-10 w-11 h-11 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <path d="M8.29419 8.05859H7.52946C7.12382 8.05859 6.7348 8.21973 6.44797 8.50656C6.16114 8.79339 6 9.18242 6 9.58806V16.4706C6 16.8763 6.16114 17.2653 6.44797 17.5521C6.7348 17.839 7.12382 18.0001 7.52946 18.0001H14.412C14.8177 18.0001 15.2067 17.839 15.4935 17.5521C15.7804 17.2653 15.9415 16.8763 15.9415 16.4706V15.7059" stroke="#889BAD" stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M15.1773 6.52955L17.4715 8.82374M18.5307 7.74164C18.8319 7.44046 19.0011 7.03196 19.0011 6.60602C19.0011 6.18008 18.8319 5.77158 18.5307 5.47039C18.2295 5.1692 17.821 5 17.3951 5C16.9691 5 16.5606 5.1692 16.2594 5.47039L9.82422 11.8827V14.1769H12.1184L18.5307 7.74164Z" stroke="#889BAD" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Team -->
                <div class="border border-border-1 rounded-xl px-4 py-4 lg:py-6 bg-white shadow-13">
                    <div class="mb-6">
                        <h3 class="font-semibold text-sm">سایر اعضای تیم</h3>
                        <p class="text-neutral-888 font-medium text-sm mt-2">
                            تنها هم‌گروهی‌هایی که پیش از این در سایت ثبت‌نام کرده‌اند مشمول دریافت امتیاز خواهند بود. برای هر حضور به عنوان هم‌گروهی، ۳۰ امتیاز به حساب کاربر افزوده می‌شود.
                        </p>
                    </div>
                    <div id="other-players" class="mt-5" data-count="<?php echo esc_attr($quantity); ?>">
                        <div class="mt-5">
                            <div class="lg:hidden font-medium">نفر 2</div>
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                <label class="relative bg-white border border-border-1 rounded-xl h-12 shadow-13 px-2 has-[input:placeholder-shown]:!border-border-1 has-[input:valid]:border-green-500 has-[input:invalid]:border-primary-2">
                                    <input class="peer focus:outline-0 w-full h-full bg-transparent" type="text" name="players_name_2" placeholder="" pattern="(?=.*[\u0600-\u06FF])[\u0600-\u06FF\s]{5,}" minlength="5">
                                    <span class="hidden peer-placeholder-shown:block absolute top-1/2 -translate-y-1/2 right-2 pointer-events-none">
                                    <span class="font-medium text-text-2">نام و نام خانوادگی</span>
                                </span>
                                    <p class="hidden peer-invalid:block peer-placeholder-shown:!hidden absolute -bottom-6 text-xs text-red-600">
                                        *لطفا نام و نام خانوادگی بازیکن را به فارسی وارد کنید.(حداقل 5 کاراکتر)</p>
                                </label>
                                <label class="relative bg-white border border-border-1 rounded-xl h-12 shadow-13 px-2 has-[input:placeholder-shown]:!border-border-1 has-[input:valid]:border-green-500 has-[input:invalid]:border-primary-2">
                                    <input class="peer focus:outline-0 w-full h-full bg-transparent" type="tel" name="players_phone_2" pattern="^(09\d{9}|(\+98)?9\d{9})$" inputmode="numeric" onkeypress="return event.charCode >= 48 && event.charCode <= 57" placeholder="" min="12" max="12">
                                    <span class="hidden peer-placeholder-shown:block absolute top-1/2 -translate-y-1/2 right-2 pointer-events-none">
                                    <span class="font-medium text-text-2">تلفن همراه</span>
                                </span>
                                    <p class="hidden peer-invalid:block peer-placeholder-shown:!hidden absolute -bottom-6 text-xs text-red-600">
                                        *لطفا شماره موبایل بازیکن را صحیح ( 11 رقم ) وارد کنید.</p>
                                </label>
                            </div>
                        </div>
                    </div>
                    <?php if ($quantity > 2) { ?>
                        <div class="mt-6 flex items-center gap-x-5 relative after:relative after:block after:content-[''] after:w-full after:h-px after:bg-border-1">
                            <button type="button" id="add-player" class="hover:scale-105 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" width="31" height="31" viewBox="0 0 31 31" fill="none">
                                    <g filter="url(#filter0_d_857_938)">
                                        <circle cx="15.5" cy="15.5" r="11" fill="#1ED982" stroke="#DADADA" />
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M16.4167 10.9167C16.4167 10.6736 16.3201 10.4404 16.1482 10.2685C15.9763 10.0966 15.7431 10 15.5 10C15.2569 10 15.0237 10.0966 14.8518 10.2685C14.6799 10.4404 14.5833 10.6736 14.5833 10.9167V14.5833H10.9167C10.6736 14.5833 10.4404 14.6799 10.2685 14.8518C10.0966 15.0237 10 15.2569 10 15.5C10 15.7431 10.0966 15.9763 10.2685 16.1482C10.4404 16.3201 10.6736 16.4167 10.9167 16.4167H14.5833V20.0833C14.5833 20.3264 14.6799 20.5596 14.8518 20.7315C15.0237 20.9034 15.2569 21 15.5 21C15.7431 21 15.9763 20.9034 16.1482 20.7315C16.3201 20.5596 16.4167 20.3264 16.4167 20.0833V16.4167H20.0833C20.3264 16.4167 20.5596 16.3201 20.7315 16.1482C20.9034 15.9763 21 15.7431 21 15.5C21 15.2569 20.9034 15.0237 20.7315 14.8518C20.5596 14.6799 20.3264 14.5833 20.0833 14.5833H16.4167V10.9167Z" fill="#ffffff" />
                                    </g>
                                    <defs>
                                        <filter id="filter0_d_857_938" x="0" y="0" width="31" height="31" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                            <feFlood flood-opacity="0" result="BackgroundImageFix" />
                                            <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                                            <feOffset />
                                            <feGaussianBlur stdDeviation="2" />
                                            <feComposite in2="hardAlpha" operator="out" />
                                            <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0" />
                                            <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_857_938" />
                                            <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_857_938" result="shape" />
                                        </filter>
                                    </defs>
                                </svg>
                            </button>
                        </div>
                    <?php } ?>
                </div>
                <!-- Team -->

            </div>

            <div class="border border-border-1 rounded-xl px-4 py-4 lg:py-6 bg-white md:w-1/2 lg:w-5/12 shadow-13">
                <h2 class="font-semibold text-lg">سفارش شما</h2>

                <div class="payment-options">

                    <div class="radio-container">
                        <label class="custom-radio-label">
                            <input type="radio" name="ez_payment_type" value="partial" checked>
                            <span class="custom-radio-button"></span>
                            بیعانه را آنلاین پرداخت میکنم.
                        </label>
                    </div>

                    <div class="radio-container">
                        <label class="custom-radio-label">
                            <input type="radio" name="ez_payment_type" value="complete">
                            <span class="custom-radio-button"></span>
                            کل مبلغ را پرداخت میکنم.
                        </label>
                    </div>
                </div>

                <style>
                    .radio-container {
                        background-color: #FAFAFA;
                        border-radius: 8px;
                        padding: 15px 20px;
                        margin-bottom: 15px;
                        display: flex;
                        align-items: center;
                        cursor: pointer;
                        width: 100%;
                    }

                    .custom-radio-label {
                        display: flex;
                        align-items: center;
                        cursor: pointer;
                        width: 100%;
                        /* To handle the right-to-left text from the image */
                        direction: rtl;
                        text-align: right;
                    }

                    /* Hide the default radio input */
                    .custom-radio-label input[type="radio"] {
                        position: absolute;
                        opacity: 0;
                        width: 0;
                        height: 0;
                    }

                    /* Style the custom radio button */
                    .custom-radio-button {
                        width: 24px;
                        /* Size of the custom checkbox/radio */
                        height: 24px;
                        border-radius: 4px;
                        /* Slightly rounded corners for the square */
                        border: 2px solid #ccc;
                        /* Default border color */
                        display: inline-block;
                        position: relative;
                        margin-left: 15px;
                        /* Space between text and custom radio */
                        flex-shrink: 0;
                        /* Prevent the button from shrinking */
                        background-color: #e9ecef;
                        /* Light background for unchecked state */
                    }

                    /* Style for the checked state (the blue background and checkmark) */
                    .custom-radio-label input[type="radio"]:checked+.custom-radio-button {
                        background-color: #4A90E2;
                        /* Blue background */
                        border-color: #4A90E2;
                        /* Blue border */
                    }

                    /* Create the checkmark inside the custom radio button */
                    .custom-radio-label input[type="radio"]:checked+.custom-radio-button::after {
                        content: '';
                        position: absolute;
                        top: 50%;
                        left: 50%;
                        width: 8px;
                        /* Width of the checkmark */
                        height: 14px;
                        /* Height of the checkmark */
                        border: solid white;
                        border-width: 0 3px 3px 0;
                        transform: translate(-50%, -50%) rotate(45deg);
                    }

                    /* Hover effect */
                    .custom-radio-label:hover .custom-radio-button {
                        border-color: #888;
                    }

                    /* Focus effect for accessibility */
                    .custom-radio-label input[type="radio"]:focus+.custom-radio-button {
                        outline: 2px solid #4A90E2;
                        outline-offset: 2px;
                    }
                </style>

                <script>
                    jQuery(function($) {
                        var ensurePaymentTypeHidden = function() {
                            var selected = $('input[name="ez_payment_type"]:checked').val() || 'partial';
                            if ($('#ez_payment_type_hidden').length === 0) {
                                $('<input>').attr({
                                    type: 'hidden',
                                    id: 'ez_payment_type_hidden',
                                    name: 'ez_payment_type',
                                    value: selected
                                }).appendTo('form.woocommerce-checkout');
                            } else {
                                $('#ez_payment_type_hidden').val(selected);
                            }
                        };

                        ensurePaymentTypeHidden();

                        $('input[name="ez_payment_type"]').change(function() {
                            ensurePaymentTypeHidden();
                            $('body').trigger('update_checkout');
                        });
                    });
                </script>
           
                <div class="my-4 lg:my-8 relative">
                    <form id="submit-coupon" method="post" class="transition-all duration-300">
                        <div class="bg-white rounded-xl border border-edge shadow-13 flex justify-between items-center px-1.5 py-2 md:p-2 gap-x-2 md:gap-x-4">
                            <div class="font-medium text-sm text-nowrap">اعمال کد تخفیف</div>
                            <div class="grow">
                                <input class="w-full focus:outline-0 bg-white border border-border-1 rounded-md h-8 px-2" type="text" name="coupon-code" id="coupon-code">
                            </div>
                            <div>
                                <button type="submit" disabled="disabled" id="order-off-code-btn" class="h-8 w-16 md:w-30 rounded-md font-semibold text-white text-nowrap" data-btn-status="submit-code" name="apply_coupon" value="<?php esc_attr_e('Apply coupon', 'woocommerce'); ?>">
                                    <?php esc_html_e('Apply coupon', 'woocommerce'); ?>
                                </button>
                            </div>
                        </div>
                        <p id="offerCode-message" style="display: none"></p>
                    </form>
                </div>

                <form name="checkout" id="payment-form" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__('Checkout', 'woocommerce'); ?>">
                    <input type="hidden" name="booking_details" value='<?php echo (json_encode($_GET)) ?>'>

                    <?php if ($checkout->get_checkout_fields()) : ?>

                        <?php do_action('woocommerce_checkout_before_customer_details'); ?>

                        <div class="hidden" id="customer_details">
                            <div class="col-1">
                                <?php do_action('woocommerce_checkout_billing'); ?>
                            </div>

                            <div class="col-2">
                                <?php do_action('woocommerce_checkout_shipping'); ?>
                            </div>
                        </div>

                        <?php do_action('woocommerce_checkout_after_customer_details'); ?>

                    <?php endif; ?>

                    <?php do_action('woocommerce_checkout_before_order_review'); ?>

                    <div id="order_review" class="woocommerce-checkout-review-order">
                        <?php do_action('woocommerce_checkout_order_review'); ?>
                    </div>
                    
                    <?php do_action('woocommerce_checkout_after_order_review'); ?>
                    <div class="rounded-lg" style="background-color: #ffe279; font-weight: 800; font-size: 12px; padding: 4px 8px; text-align: center; margin: 8px 0;">
                        بعد از پرداخت، شماره تماس اتاق فرار به شما نمایش داده میشه، حتما با اون شماره موبایل برای هماهنگی تماس بگیر!
                    </div>
                    <p class="text-sm text-neutral-888 font-medium">
                        ثبت رزرو شما به معنای پذیرش
                        <a href="https://escapezoom.ir/terms" class="underline underline-offset-4 text-ring-focus hover:text-info transition">قوانین و
                            مقررات
                        </a>
                        استفاده از سایت اسکیپ زوم است.
                    </p>

                </form>

            </div>
        </div>
    </section>

    <script>
        jQuery(document).ready(function($) {

            $("#other-players :is(input[type='text'],input[type='tel'])").on('input change', function() {
                let value = $(this).val(),
                    name = $(this).attr('name')

                $(`form[name="checkout"] #${name}`).val(value)
            })

            $('#add-player').click(function() {
                let playerMaxCount = parseInt($('#other-players').attr('data-count')) - 1;
                let playerCurrentCount = $('#other-players').children().length;
                if (playerCurrentCount < playerMaxCount) {
                    $('#other-players').append(`
         <div class="mt-5">
                    <div class="lg:hidden font-medium">نفر ${parseInt(playerCurrentCount) + 2}</div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="relative bg-white border border-border-1 rounded-xl shadow-13 h-12 px-2 has-[input:placeholder-shown]:!border-border-1 has-[input:valid]:border-green-500 has-[input:invalid]:border-primary-2">
                            <input class="peer focus:outline-0 w-full h-full bg-transparent" type="text" name="players_name_${parseInt(playerCurrentCount) + 2}" min="5" max="30" placeholder="" pattern="(?=.*[\u0600-\u06FF])[\u0600-\u06FF\\s]{5,}" minlength="5">
                            <div class="hidden peer-placeholder-shown:block absolute top-1/2 -translate-y-1/2 right-2 pointer-events-none">
                                <span class="font-medium text-text-2">نام و نام خانوادگی</span>
                            </div>
                            <p class="hidden peer-invalid:block peer-placeholder-shown:!hidden absolute -bottom-6 text-xs text-red-600">*لطفا نام و نام خانوادگی بازیکن را به فارسی وارد کنید.(حداقل 5 کاراکتر)</p>
                        </div>
                        <div class="relative bg-white border border-border-1 rounded-xl shadow-13 h-12 px-2 has-[input:placeholder-shown]:!border-border-1 has-[input:valid]:border-green-500 has-[input:invalid]:border-primary-2">
                            <input class="peer focus:outline-0 w-full h-full bg-transparent" type="tel" name="players_phone_${parseInt(playerCurrentCount) + 2}" pattern="^(09\\d{9}|(\\+98)?9\\d{9})$" inputmode="numeric" onkeypress="return event.charCode >= 48 && event.charCode <= 57" placeholder="" min="12" max="12">
                            <div class="hidden peer-placeholder-shown:block absolute top-1/2 -translate-y-1/2 right-2 pointer-events-none">
                                <span class="font-medium text-text-2">تلفن همراه</span>
                            </div>
                            <p class="hidden peer-invalid:block peer-placeholder-shown:!hidden absolute -bottom-6 text-xs text-red-600">*لطفا شماره موبایل بازیکن را صحیح (09121111111) وارد کنید.</p>
                        </div>
                    </div>
                </div>
        `);
                    if (playerCurrentCount === (playerMaxCount) - 1) {
                        $(this).parent().remove();
                    }
                }

                $("#other-players :is(input[type='text'],input[type='tel'])").on('input change', function() {
                    let value = $(this).val(),
                        name = $(this).attr('name')

                    $(`form[name="checkout"] #${name}`).val(value)
                })
            })

            $('#submit-coupon #coupon-code').on('input change', function() {
                let value = $(this).val()

                if (value !== '') {
                    $('#submit-coupon').find('button').removeAttr('disabled', 'disabled')
                } else {
                    $('#submit-coupon').find('button').attr('disabled', 'disabled')
                }

                $('form.checkout_coupon input[name="coupon_code"]').val(value)
            })

            $("body").on('submit', '#submit-coupon', function(event) {
                event.preventDefault()

                let _ = $(this)

                _.find("#offerCode-message").html('').hide()

                _.css('opacity', '.5')
                _.find('button,input').attr('disabled', 'disabled')

                $('form.checkout_coupon').submit();

                $(document).ajaxSuccess(function(event, xhr, settings) {
                    if (settings.url === '/?wc-ajax=apply_coupon') {
                        let response = xhr.responseText

                        _.css('opacity', '1')
                        _.find('button,input').removeAttr('disabled', 'disabled')

                        _.find("#offerCode-message").html($(response).text().trim()).show()
                    }
                });
            })
            let productId = '<?php echo esc_js($product_id); ?>';
            let productTitle = '<?php echo esc_js($product ? $product->get_title() : ''); ?>';
            let productType = '<?php echo esc_js($product_type . '|' . $genres_title); ?>';
            let quantity = <?php echo esc_js($quantity); ?>;
            $(window).on('load', function () {
                setTimeout(function () {
                    if (typeof window.zebline !== 'undefined' && window.zebline.event && typeof window.zebline.event.track === 'function') {
                        window.zebline.event.track('added_to_cart', {
                            product_id: productId,
                            quantity: quantity,
                            product: productTitle,
                            category: productType,
                        });
                    }
                }, 100);
            });
        })
    </script>

<?php do_action('woocommerce_after_checkout_form', $checkout); ?>
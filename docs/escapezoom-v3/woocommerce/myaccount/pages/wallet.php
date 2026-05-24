<?php

global $wldb;

$user = wp_get_current_user();

$name = get_user_meta( $user->ID, 'withdrawal_owner_name', true ) ?: $user->display_name;

$shaba = get_user_meta( $user->ID, 'withdrawal_owner_shaba', true ) ?: "IR000000000000000000000000";
$shaba = str_replace( 'IR', '', $shaba );

$balance = $wldb->get_balance( $user->ID );

$active_withdraw = $wldb->get( [
	'user_id' => $user->ID,
	'type'    => 'withdraw',
	'status'  => 'در حال پردازش',
], - 1 );

?>

<div class="lg:col-span-8 2xl:col-span-9">

    <section class="rounded-2xl border border-slate-120 px-8 shadow-12 max-lg:mb-0 max-lg:rounded-none max-lg:px-0 max-lg:shadow-none mb-8 py-12 max-lg:border-0 max-lg:py-0">

        <div class="md:mb-8 mb-0 lg:mb-8 max-lg:border-b max-lg:border-slate-120 max-lg:pb-6">
            <div class="flex justify-start">
                <div class="items-center gap-6 md:flex">
                    <h2 class="flex items-center gap-4">
                        <span class="text-base font-bold md:text-lg">
                            <span class="text-xl">کیف پول</span>
                        </span>
                    </h2>
                    <div class="hidden md:block"></div>
                </div>
            </div>
        </div>

        <div class="m-auto flex gap-8 max-lg:flex-col max-lg:items-center max-lg:py-10" style="max-width: 786px">

            <div class="w-auto">
                <div class="rounded-2xl flex flex-col p-4 text-center items-center justify-end text-md pt-13" style="width: 260px; height: 168px;background-image: url('<?php echo bloginfo( 'template_url' ); ?>/assets/images/card-background.jpg')">
                    <div class="text-white">IR <?php echo chunk_split( $shaba, 4, ' ' ) ?></div>
                    <div class="text-white"><?php echo $name; ?></div>
                    <div class="text-white text-xl mt-4">
						<?php echo number_format( $balance ); ?>
                        <span class="text-primaryColor">تومـان</span>
                    </div>
                </div>
            </div>

            <div class="grow max-lg:w-full">
                <div class="flex flex-col gap-4">
                    <div class="payment-accordion bg-slate-100 rounded-2xl">
                        <div class="payment-accordion-title flex items-center gap-2 p-5 cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 21 21" fill="none">
                                <circle cx="10.5" cy="10.5" r="10" fill="white" stroke="#D5DCE1"/>
                                <path d="M11.3149 5.31797C11.0988 5.11436 10.8058 5 10.5004 5C10.195 5 9.90202 5.11436 9.68591 5.31797L5.33768 9.41759C5.12147 9.62155 5 9.89818 5 10.1866C5 10.4751 5.12147 10.7517 5.33768 10.9557C5.5539 11.1596 5.84715 11.2742 6.15293 11.2742C6.4587 11.2742 6.75196 11.1596 6.96817 10.9557L9.34782 8.71161L9.34782 15.9128C9.34782 16.2011 9.46925 16.4777 9.6854 16.6816C9.90155 16.8855 10.1947 17 10.5004 17C10.8061 17 11.0992 16.8855 11.3154 16.6816C11.5315 16.4777 11.6529 16.2011 11.6529 15.9128L11.6529 8.71161L14.0318 10.9557C14.1389 11.0567 14.266 11.1368 14.4059 11.1914C14.5457 11.2461 14.6957 11.2742 14.8471 11.2742C14.9985 11.2742 15.1484 11.2461 15.2883 11.1914C15.4282 11.1368 15.5553 11.0567 15.6623 10.9557C15.7694 10.8547 15.8543 10.7348 15.9122 10.6028C15.9702 10.4709 16 10.3295 16 10.1866C16 10.0438 15.9702 9.90238 15.9122 9.77043C15.8543 9.63848 15.7694 9.51858 15.6623 9.41759L11.3149 5.31797Z" fill="#1ED982"/>
                            </svg>
                            <span class="grow" style="color: #5091FB">افزایش موجودی</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="6" viewBox="0 0 10 6" fill="none" class="transition duration-150">
                                <path d="M9 1L5.70711 4.29289C5.31658 4.68342 4.68342 4.68342 4.29289 4.29289L1 1" stroke="#09192D" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </div>

                        <?php
                        if ( get_current_user_id() == 3325) {

                            function recursive_array_filter($array) {
                                foreach ($array as $key => &$value) {
                                    if (is_array($value)) {
                                        $value = recursive_array_filter($value);
                                        if (empty($value)) {
                                            unset($array[$key]);
                                        }
                                    } else {
                                        if (is_null($value) || $value === '') {
                                            unset($array[$key]);
                                        }
                                    }
                                }
                                return $array;
                            }

                            function sendRequest($endpoint, $data) {

                                $baseUrl = 'https://payment.zarinpal.com/pg/v4/payment/';

                                $url = $baseUrl . $endpoint;
                                $args = array(
                                        'body' => json_encode($data),
                                        'headers' => array(
                                                'Content-Type' => 'application/json',
                                                'User-Agent' => 'ZarinPalSdk/v1 WooCommerce Plugin/v.5.0.14',
                                        ),
                                        'timeout' => 15,
                                        'data_format' => 'body',
                                );
                                $response = wp_remote_post($url, $args);
                                if (is_wp_error($response)) {
                                    throw new Exception('خطا در ارتباط با سرور: ' . $response->get_error_message());
                                }
                                $response_body = wp_remote_retrieve_body($response);
                                $result = json_decode($response_body, true);
                                return $result;
                            }

                            if ( isset( $_POST['wallet_amount'] ) ) {

                                $settings = get_option('woocommerce_WC_ZPal_settings');
                                $merchantCode = isset($settings['merchantcode']) ? $settings['merchantcode'] : '';
                                $sandbox = (isset($settings['sandbox']) && $settings['sandbox'] === 'yes');
                                $accessToken = isset($settings['access_token']) ? $settings['access_token'] : '';

                                $payment_amount = $_POST['wallet_amount'];
                                $callback_url = home_url();
                                $description = 'شارژ کیف پول';
                                $metadata = array(
                                    'email' => '',
                                    'mobile' => '9353316152',
                                );

                                $cart_data = array(
                                    'items' => '',
                                    'discount' => '',
                                    'total' => $payment_amount,
                                );

                                $cart_json = json_encode($cart_data);
                                $referrer_id = 35;

                                try {

                                    $data = array(
                                        'merchant_id' => $merchantCode,
                                        'amount' => $payment_amount,
                                        'callback_url' => $callback_url,
                                        'description' => $description,
                                        'metadata' => $metadata,
                                        'invoices' => $cart_json,
                                        'referrer_id' => $referrer_id,
                                    );
                                    $data = recursive_array_filter($data);
                                    $response = sendRequest('request.json', $data);
                                    if (isset($response['data']['code']) && $response['data']['code'] == 100) {
                                        $authority = $response['data']['authority'];
                                    } else {
                                        $errorMessage = $response['errors']['message'] ?? 'خطای ناشناخته';
                                        throw new Exception($errorMessage);
                                    }

                                    wp_redirect('https://sandbox.zarinpal.com/pg/StartPay/'. $authority);
                                    exit;
                                } catch (Exception $e) {
                                    wc_add_notice(__('خطا در اتصال به درگاه پرداخت: ', WC_ZPAL_TEXT_DOMAIN) . $e->getMessage(), 'error');
                                    return;
                                }
                            } ?>

                            <form method="post">
                                <input type="number" name="wallet_amount" required placeholder="مبلغ (ریال)">
                                <button type="submit">پرداخت</button>
                            </form>

                        <?php
                        } else { ?>

                            <div class="payment-accordion-content p-5 pt-0" style="display: none">
                                <span class="text-md opacity-50">این بخش در حال حاضر غیرفعال است.</span>
                                <form action="#" method="post" class="flex flex-col gap-3 hidden">
                                    <input class="text-gray-900 block w-full border-0 p-1.5 text-sm shadow-13 outline-none ring-1 ring-inset ring-gray-100 placeholder:text-right placeholder:text-slate-200 focus:shadow-none focus:ring-2 focus:ring-inset focus:ring-primary-500 h-16 py-2 px-6 rounded-2xl" placeholder="مبلغ درخواست افزایش" type="number" name="increase-value">
                                    <button class="bg-payment-title text-white shadow text-xl h-16 py-2 px-6 rounded-2xl">
                                        پرداخت
                                    </button>
                                </form>
                            </div>

                        <?php
                        } ?>

                    </div>
                    <div class="payment-accordion bg-slate-100 rounded-2xl">
                        <div class="payment-accordion-title flex items-center gap-2 p-5 cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" width="21" height="22" viewBox="0 0 21 22" fill="none">
                                <circle cx="10.5" cy="11" r="10" fill="white" stroke="#D5DCE1"/>
                                <path d="M9.68514 17.182C9.90124 17.3856 10.1942 17.5 10.4996 17.5C10.805 17.5 11.098 17.3856 11.3141 17.182L15.6623 13.0824C15.8785 12.8785 16 12.6018 16 12.3134C16 12.0249 15.8785 11.7483 15.6623 11.5443C15.4461 11.3404 15.1528 11.2258 14.8471 11.2258C14.5413 11.2258 14.248 11.3404 14.0318 11.5443L11.6522 13.7884L11.6522 6.58724C11.6522 6.29889 11.5307 6.02234 11.3146 5.81845C11.0985 5.61455 10.8053 5.5 10.4996 5.5C10.1939 5.5 9.90078 5.61455 9.68463 5.81845C9.46849 6.02234 9.34706 6.29889 9.34706 6.58724L9.34705 13.7884L6.96817 11.5443C6.86111 11.4433 6.73401 11.3632 6.59413 11.3086C6.45425 11.2539 6.30433 11.2258 6.15293 11.2258C6.00152 11.2258 5.8516 11.2539 5.71172 11.3086C5.57184 11.3632 5.44474 11.4433 5.33768 11.5443C5.23062 11.6453 5.1457 11.7652 5.08776 11.8972C5.02982 12.0291 5 12.1705 5 12.3134C5 12.4562 5.02982 12.5976 5.08776 12.7296C5.1457 12.8615 5.23062 12.9814 5.33768 13.0824L9.68514 17.182Z" fill="#F21543"/>
                            </svg>
                            <span class="grow" style="color: #5091FB">تسویه حساب</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="6" viewBox="0 0 10 6" fill="none" class="transition duration-150">
                                <path d="M9 1L5.70711 4.29289C5.31658 4.68342 4.68342 4.68342 4.29289 4.29289L1 1" stroke="#09192D" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <div class="payment-accordion-content p-5 pt-0" style="display: none">
							<?php if ( ! empty( $active_withdraw ) ) { ?>
                                <span class="text-md opacity-50">
                                    شما یک درخواست تسویه فعال دارید. لطفا تا تسویه کامل آن منتظر بمانید.
                                </span>
							<?php } else { ?>
                                <form action="#" id="withdrawal-form" method="post" class="flex flex-col gap-3">
                                    <div class="flex gap-2">
                                        <button type="button" class="bg-primaryColor text-white shadow h-16 py-2 px-6 rounded-2xl leading-5" id="get-all-balance">
                                            کل <br>
                                            موجودی
                                        </button>
                                        <input class="text-gray-900 block w-full border-0 p-1.5 text-sm shadow-13 outline-none ring-1 ring-inset ring-gray-100 placeholder:text-right placeholder:text-slate-200 focus:shadow-none focus:ring-2 focus:ring-inset focus:ring-primary-500 h-16 py-2 px-6 rounded-2xl" placeholder="مبلغ درخواست برداشت" type="number" name="decrease-value">
                                    </div>
                                    <button type="submit" class="text-white shadow text-xl h-16 py-2 px-6 rounded-2xl" style="background: #1ED982">
                                        برداشت
                                    </button>
                                </form>
							<?php } ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </section>

    <div class="mb-8">
        <div class="space-x-6 max-lg:order-1 space-x-reverse max-lg:w-full lg:grow overflow-hidden max-lg:grid max-lg:h-12 max-lg:grid-cols-2 max-lg:rounded-10 max-lg:border max-lg:border-slate-105">
            <button type="button" data-list="settlement" class="change-table active max-lg:bg-primary-500 max-lg:text-white lg:border-b lg:border-b-primary-500">
                لیست تسویه حساب
            </button>
            <button type="button" data-list="my-transactions" class="change-table text-text-3">
                تراکنش های من
            </button>
        </div>
    </div>

    <section id="tables" class="rounded-2xl border border-slate-120 px-8 shadow-12 max-lg:mb-0 max-lg:rounded-none max-lg:px-0 max-lg:shadow-none py-12 max-lg:border-0 max-lg:py-0"></section>

</div>

<script>
    jQuery(document).ready(function ($) {
        const Toast = Swal.mixin({
            toast: true,
            position: 'bottom-start',
            showConfirmButton: false,
            timer: 3000,
        })

        $(".payment-accordion-title").on('click', function () {
            $(this).next().slideToggle(150)
            $(this).find('svg:last-of-type').toggleClass('rotate-180')
        })

        const BuildTables = (list, page = 1, status = -1) => {
            $.ajax({
                url: "<?php echo admin_url( 'admin-ajax.php' ) ?>",
                type: 'POST',
                data: {
                    'action': 'v2_ajax_handler',
                    'nonce': "<?php echo wp_create_nonce( 'v2-ajax-nonce' ) ?>",
                    'callback': 'panel_wallet_lists_get',
                    'list': list,
                    'page': page,
                    'status': status,
                },
                beforeSend: function () {
                    $("#tables").html(() => {
                        let out = '<div class="w-full h-8 rounded-xl mb-8 skeleton"></div>'
                        for (let i = 0; i < 10; i++) out += '<div class="w-full h-16 rounded-xl mb-2 skeleton"></div>'
                        return out
                    })
                },
                success: function (response) {
                    $("#tables").html(response)
                },
            })
        }

        BuildTables('settlement')

        $("body")
            .on('click', '#open-filter-menu', function () {
                $(this).next().toggleClass('hidden')
            })
            .on('click', '#filter-list a', function (e) {
                e.preventDefault()
                $('#filter-list a').addClass('border-transparent opacity-50').removeClass('border-primaryColor')
                $(this).removeClass('border-transparent opacity-50').addClass('border-primaryColor')

                let state = $(this).data('state')

                BuildTables('settlement', 1, state)
            })
            .on('click', '.change-table', function (e) {
                $('.change-table')
                    .addClass('text-text-3')
                    .removeClass('active max-lg:bg-primary-500 max-lg:text-white lg:border-b lg:border-b-primary-500')
                $(this)
                    .removeClass('text-text-3')
                    .addClass('active max-lg:bg-primary-500 max-lg:text-white lg:border-b lg:border-b-primary-500')

                let list = $(this).data('list')

                BuildTables(list)
            })
            .on('click', '.pagination a', function (e) {
                e.preventDefault()

                let page = $(this).attr('href').split('?page=')[1] ? $(this)
                    .attr('href')
                    .split('?page=')[1] : 1

                let activeList = $(".change-table.active").data('list')

                BuildTables(activeList, page)

            })

        $('[name="decrease-value"]').on('keyup', function () {
            let value = parseInt($(this).val())

            if (value > <?php echo $balance?> ) {
                $(this).val("<?php echo $balance?>")
                Toast.fire({
                    icon: "error",
                    title: "مقدار وارد شده بیشتر از کیف پول شماست.",
                })
            }
        })

        $("#get-all-balance").on('click', function () {
            $('[name="decrease-value"]').val("<?php echo $balance?>")
        })

        $("#withdrawal-form").on('submit', function (e) {
            e.preventDefault()

            let amount = parseInt($(this).find('[name="decrease-value"]').val()),
                button = $(this).find('[type="submit"]')

            if (amount > 0) {
                $.ajax({
                    type: 'POST',
                    url: "<?php echo admin_url( 'admin-ajax.php' ) ?>",
                    data: {
                        'action': 'v2_ajax_handler',
                        'nonce': "<?php echo wp_create_nonce( 'v2-ajax-nonce' ) ?>",
                        'callback': 'panel_wallet_withdrawal',
                        'amount': amount,
                    },
                    beforeSend: function () {
                        button.attr('disabled', 'disabled').css('opacity', '.5')
                    },
                    success: function (response) {
                        if (!response.success) {
                            button.removeAttr('disabled').css('opacity', '1')
                        }

                        Toast.fire({
                            icon: response.success ? 'success' : 'error',
                            title: response.data,
                        })

                        setTimeout(() => window.location.reload(), 3000)
                    },
                })
            } else {
                Toast.fire({
                    icon: 'info',
                    title: 'لطفا مبلغ مورد نظر را وارد کنید.',
                })
            }
        })

        $(document).on('click', function (e) {
            if (!$(e.target).closest('#open-filter-menu').length) {
                $('#filter-list').addClass('hidden');
            }
        });

    })
</script>
<?php

/*=========================================================================================================*/
//Telegram functions

function telegram_send_code_api($request)
{


    $params = $request->get_params();
    $mobile     = $params['phone'];
    $chat_id    = $params['chat_id'];

    if (substr($mobile, 0, 1) === '0') $mobile = substr($mobile, 1);

    $user       = get_user_by('login', $mobile);
    $sms_code   = wp_rand('1000', '9999');

    if ($user) {
        if (in_array('compiler', (array)$user->roles) || in_array('sans_manager', (array)$user->roles)) {

            $otp_send_time = get_user_meta($user->ID, 'otp_send_time', true);
            $otp_send_time = $otp_send_time ?: 0;

            if (current_time('timestamp') - $otp_send_time < 2)
                throw new Exception('پیامک برای شما ارسال شده است لطفا منتظر باشید و اگر پیامکی دریافت نکردید بعد از یک دقیقه دوباره امتحان کنید');

            update_user_meta($user->ID, 'otp_send_time', current_time('timestamp'));
            update_user_meta($user->ID, 'one_time_password', $sms_code);
            update_user_meta($user->ID, 'temp_chat_id', $chat_id);

            try {
                ez_sendpayamak3($mobile, 'کد تایید شما: ' . $sms_code . "\n\n اسکیپ زوم", '2191307900');

                wp_send_json_success($sms_code);
            } catch (Exception $e) {
                wp_send_json_error(array('error' => $e->getMessage()), 400);
            }
        } else // اگه مجموعه دار نبود
            wp_send_json_error('خطا: شما مجموعه دار نیستید!');
    } else // اگه همچین شماره ای ثبت نشده بود
        wp_send_json_error('خطا: شماره شما ثبت نشده است!');
}
//**********************************************************************************************************/
function telegram_verify_code_api($request)
{

    $params = $request->get_params();
    $code       = $params['code'];
    $chat_id    = $params['chat_id'];

    $user = get_users(array(
        'meta_key'      => 'temp_chat_id',
        'meta_value'    => $chat_id
    ));
    $user = $user[0];

    if ($user) {
        if (in_array('compiler', (array)$user->roles) || in_array('sans_manager', (array)$user->roles)) {

            if (get_user_meta($user->ID, 'one_time_password', true) != $code)
                wp_send_json_error('خطا: کد وارد شده صحیح نمی باشد!');

            delete_user_meta($user->ID, 'otp_send_time');
            delete_user_meta($user->ID, 'one_time_password');
            delete_user_meta($user->ID, 'temp_chat_id');
            update_user_meta($user->ID, 'chat_id', $chat_id);

            wp_send_json_success('تبریک! اطلاع رسانی اتاق فرارهای شما از طریق تلگرام فعال شد. 🎉🎉🎉');
        } else // اگه مجموعه دار نبود
            wp_send_json_error('خطا: شما مجموعه دار نیستید!');
    } else // اگه همچین شماره ای ثبت نشده بود
        wp_send_json_error('خطا: شماره شما ثبت نشده است!');
}

/*=========================================================================================================*/
//User functions

function user_dashboard_api($request)
{
    global $wpdb;

    $user_id = get_user_id_by_token(ez_authorization(true));

    $user_role  = get_user_role($user_id);
    if ($user_role == 'sans_manager')
        $user_products = $wpdb->get_results("SELECT *  FROM `wp_postmeta` WHERE `meta_key` LIKE 'sans_manager' AND `meta_value` LIKE {$user_id}", ARRAY_A);
    elseif ($user_role == 'compiler')
        $user_products = $wpdb->get_results("SELECT *  FROM `wp_postmeta` WHERE `meta_key` LIKE 'user_ebtal' AND `meta_value` LIKE {$user_id}", ARRAY_A);

    $products_count = count($user_products);

    $data[] = [
        'type'  => 'dashboard',
        'title' => '',
        'data'  => [
            'items' => [
                [
                    'type'  => 'sells',
                    'title' => 'فروش من',
                    'items' => [
                        [
                            'product_title' => 'اتاق فرار فتنه',
                            'tickets_count' => 5,
                            'purchase_time' => 17046546878,
                            'image'         => '',
                            'status'        => 'پرداخت شده',
                            'url'           => '/room/اتاق-فرار-فتنه/',
                        ],
                        [
                            'product_title' => 'اتاق فرار فتنه',
                            'tickets_count' => 5,
                            'purchase_time' => 17046546878,
                            'image'         => '',
                            'status'        => 'پرداخت شده',
                            'url'           => '/room/اتاق-فرار-فتنه/',
                        ],
                    ]
                ],
                [
                    'type'  => 'state',
                    'title' => 'وضعیت من',
                    'items' => [
                        [
                            'title' => 'اتاق فرار های من',
                            'value' => $products_count,
                            'url'   => '/panel/products',
                            'unit'   => '',
                        ],
                        [
                            'title' => 'موجودی قابل تسویه',
                            'value' => 1532500,
                            'url'   => '/panel/my_rooms',
                            'unit'   => '',
                        ],
                        [
                            'title' => 'مجموع رزرو اتاق های من',
                            'value' => 192,
                            'url'   => '',
                            'unit'   => 'مرتبه',
                        ],
                        [
                            'title' => 'مجموع فروش من',
                            'value' => 19265665,
                            'url'   => '',
                            'unit'   => 'تومان',
                        ],
                    ]
                ],
                [
                    'type'  => 'orders',
                    'title' => 'رزروهای من',
                    'items' => [
                        [
                            'product_title' => 'اتاق فرار فتنه',
                            'tickets_count' => 5,
                            'purchase_time' => 17046546878,
                            'image'         => '',
                            'status'        => 'پرداخت شده',
                            'url'           => '/room/اتاق-فرار-فتنه/',
                        ],
                        [
                            'product_title' => 'اتاق فرار فتنه',
                            'tickets_count' => 5,
                            'purchase_time' => 17046546878,
                            'image'         => '',
                            'status'        => 'پرداخت شده',
                            'url'           => '/room/اتاق-فرار-فتنه/',
                        ],
                    ]
                ],
                [
                    'type'  => 'collections',
                    'title' => 'محبوب های من',
                    'items' => [
                        [
                            'title'             => 'اتاق فرار فتنه',
                            'collection_title'  => 'بازی های خوفناک',
                            'image'             => '',
                            'hood_name'         => 'سعادت آباد',
                            'city_name'         => 'تهران',
                            'url'               => '/room/اتاق-فرار-فتنه/',
                            'genres'            => [
                                [
                                    'title' => 'ترسناک',
                                    'id'    => 124,
                                ],
                                [
                                    'title' => 'هیجانی',
                                    'id'    => 136,
                                ],
                            ],
                        ],
                        [
                            'title'             => 'اتاق فرار فتنه',
                            'collection_title'  => 'بازی های خوفناک',
                            'image'             => '',
                            'hood_name'         => 'سعادت آباد',
                            'url'               => '/room/اتاق-فرار-فتنه/',
                            'genres'            => [
                                [
                                    'title' => 'ترسناک',
                                    'id'    => 124,
                                ],
                                [
                                    'title' => 'هیجانی',
                                    'id'    => 136,
                                ],
                            ],
                        ],
                    ]
                ],
                [
                    'type'  => 'invitation',
                    'title' => 'آخرین دعوت های من',
                    'items' => [
                        [
                            'title'         => 'اتاق فرار فتنه',
                            'inviter_title' => 'ندا نیکو',
                            'inviter_url'   => '/user/سعید زمانی/',
                            'inviter_image' => '',
                        ],
                        [
                            'title'         => 'اتاق فرار فتنه',
                            'inviter_title' => 'سعید زمانی',
                            'inviter_url'   => '/user/سعید زمانی/',
                            'inviter_image' => '',
                        ],
                    ]
                ],
            ],
        ]
    ];

    wp_send_json_success($data);
}
//**********************************************************************************************************/
function user_sells_total_invoice_api($request)
{

    global $wpdb;

    $user_id = get_user_id_by_token(ez_authorization(true));

    $params = $request->get_params();
    $date_range = $params['date_range'];

    $totals = [
        "total_tickets" => 0,
        "total_income"  => 0,
        "total_prepaid" => 0,
        "total_credit"  => 0,
    ];

    $args = ['wc-partially-paid', 'wc-walletx', 'wc-completed'];

    $user_role  = get_user_role($user_id);
    if ($user_role == 'sans_manager')
        $user_products = $wpdb->get_results("SELECT *  FROM `wp_postmeta` WHERE `meta_key` LIKE 'sans_manager' AND `meta_value` LIKE {$user_id}", ARRAY_A);
    elseif ($user_role == 'compiler')
        $user_products = $wpdb->get_results("SELECT *  FROM `wp_postmeta` WHERE `meta_key` LIKE 'user_ebtal' AND `meta_value` LIKE {$user_id}", ARRAY_A);

    foreach ($user_products as $user_product)
        $products_id[] = $user_product['post_id'];

    $date_range = explode(',', $date_range);

    $orders_id = get_orders_ids_by_product_id($products_id, $args, $date_range);

    foreach ($orders_id as $order_id) {
        $order = wc_get_order($order_id);

        $args = [
            "single_value"  => true,
            "query"         => "SELECT * FROM `wp_zb_booking_history` WHERE `wc_order_id` = $order_id",
        ];
        $row = (array)json_decode(ez_reservation(array('type' => 'query_execution', 'data' => $args)));

        if (!$row['booked_time']) // order has a bug
            continue;

        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            $quantity   = $item->get_quantity();
        }

        $pish_per_person    = get_post_meta($order_id, 'ticket_tedad', true);
        $pish_per_person    = !empty($pish_per_person) ? $pish_per_person : get_post_meta($product_id, 'pish_pardakht_per_person', true);
        $pish_per_person    = !empty($pish_per_person) ? $pish_per_person : 1;

        $pish       = get_post_meta($order_id, "_order_total_2", true);
        $pish_final = $pish ?? get_post_meta($order_id, "_order_total", true);

        $item_total = $pish_final / $pish_per_person * $quantity;

        $totals['total_tickets']    += $quantity;
        $totals['total_income']     += $item_total;
        $totals['total_prepaid']    += $pish_final;
    }

    $commission = 10;
    $tax        = 10;
    $tax_free = [2762, 21755, 353952, 87471, 145024];
    if (in_array($product_id, $tax_free))
        $tax = 0;
    $totals['total_credit'] = ceil($totals['total_prepaid'] - ($totals['total_income'] * ($commission / 100) * (1 + $tax / 100)));

    $data[] = [
        'type'  => 'sells',
        'title' => 'فروش من',
        'data'  => [
            'items'         => [
                "statistics" => $totals,
            ],
        ]
    ];

    wp_send_json_success($data);
}
//**********************************************************************************************************/
function user_sells_api($request)
{
    global $wpdb;

    $user_id = get_user_id_by_token(ez_authorization(true));

    $params = $request->get_params();
    $status     = $params['status'];
    $page_num   = (int)$params['page'];
    $date_range = $params['date_range'];

    $status     = $status ?? -1;
    $page_num   = $page_num ?? 1;

    $items_per_page = 20;

    if ($status == -1)
        $args = ['wc-partially-paid', 'wc-walletx', 'wc-completed'];
    elseif ($status == 'holding')
        $args = ['wc-partially-paid'];
    elseif ($status == 'held')
        $args = ['wc-walletx', 'wc-completed'];

    $user_role  = get_user_role($user_id);
    if ($user_role == 'sans_manager')
        $user_products = $wpdb->get_results("SELECT *  FROM `wp_postmeta` WHERE `meta_key` LIKE 'sans_manager' AND `meta_value` LIKE {$user_id}", ARRAY_A);
    elseif ($user_role == 'compiler')
        $user_products = $wpdb->get_results("SELECT *  FROM `wp_postmeta` WHERE `meta_key` LIKE 'user_ebtal' AND `meta_value` LIKE {$user_id}", ARRAY_A);

    foreach ($user_products as $user_product)
        $products_id[] = $user_product['post_id'];

    if (!empty($date_range))
        $date_range = explode(',', $date_range);

    $orders_id = get_orders_ids_by_product_id($products_id, $args, $date_range, $items_per_page, $page_num);

    $max_page_num = ceil((int)(get_orders_ids_by_product_id($products_id, $args, $date_range, $items_per_page, $page_num, true)[0]) / $items_per_page);

    foreach ($orders_id as $order_id) {
        $order = wc_get_order($order_id);

        $args = [
            "single_value"  => true,
            "query"         => "SELECT * FROM `wp_zb_booking_history` WHERE `wc_order_id` = $order_id",
        ];
        $row = (array)json_decode(ez_reservation(array('type' => 'query_execution', 'data' => $args)));

        if (!$row['booked_time']) // order has a bug
            continue;

        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            $quantity   = $item->get_quantity();
        }

        $pish_per_person    = get_post_meta($order_id, 'ticket_tedad', true);
        $pish_per_person    = !empty($pish_per_person) ? $pish_per_person : get_post_meta($product_id, 'pish_pardakht_per_person', true);
        $pish_per_person    = !empty($pish_per_person) ? $pish_per_person : 1;

        $pish       = get_post_meta($order_id, "_order_total_2", true);
        $pish_final = $pish ?? get_post_meta($order_id, "_order_total", true);

        $item_total = $pish_final / $pish_per_person * $quantity;

        $items[] = [
            'order_id'      => (int)$order_id,
            'product_title' => get_the_title($product_id),
            'tickets_count' => $quantity,
            'purchase_time' => (int)$row['booked_time'],
            'sans_time'     => (int)$row['booking_time'],
            'total_payment' => (int)$item_total,
            'prepaid'       => (int)$pish_final,
            'status'        => time() > $row['booking_time'] ? 'بازی کرده اند' : 'در راه شروع بازی',
            'product_url'   => trim_home_url(get_permalink($product_id)),
        ];
    }

    $data[] = [
        'type'  => 'sells',
        'title' => 'فروش من',
        'data'  => [
            'tabs'          => [
                [
                    'type'  => 'status',
                    'title' => '',
                    'key'   => 'status',
                    'items' => [
                        [
                            'title' => 'همه',
                            'id'    => '-1',
                        ],
                        [
                            'title' => 'رزرو شده',
                            'id'    => 'holding',
                        ],
                        [
                            'title' => 'کنسل شده', // استردادی ها
                            'id'    => 'cancelled',
                        ],
                        [
                            'title' => 'برگزار شده',
                            'id'    => 'held',
                        ],
                    ],
                ],
            ],
            'items'         => [
                "sells" => $items
            ],
            'pagination'    => [
                'current_page'  => $page_num,
                'total_pages'   => $max_page_num,
            ]
        ]
    ];

    wp_send_json_success($data);
}
//**********************************************************************************************************/
function user_orders_api($request)
{

    $user_id = get_user_id_by_token(ez_authorization(true));

    $params = $request->get_params();
    $status     = $params['status'];
    $page_num   = (int)$params['page'];

    $status     = $status ?: -1;
    $page_num   = $page_num ?: 1;

    $items_per_page = 10;

    $args = [
        'numberposts' => -1,
        'meta_key'    => '_customer_user',
        'meta_value'  => $user_id,
        'post_type'   => 'shop_order',
    ];

    if ($status == -1)
        $args['post_status'] = ['wc-partially-paid', 'wc-walletx', 'wc-completed', 'wc-admin-cancelled', 'wc-refunded', 'wc-conflict'];
    elseif ($status == 'reserved')
        $args['post_status'] = ['wc-partially-paid'];
    elseif ($status == 'held')
        $args['post_status'] = ['wc-walletx', 'wc-completed'];
    elseif ($status == 'cancelled')
        $args['post_status'] = ['wc-admin-cancelled', 'wc-refunded', 'wc-conflict'];

    $orders = get_posts($args);

    $max_page_num = ceil(count($orders) / $items_per_page);

    $orders = array_slice($orders, ($page_num - 1) * $items_per_page, $items_per_page);

    foreach ($orders as $order) {
        $order_id = $order->ID;
        $order = wc_get_order($order_id);

        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            $quantity   = $item->get_quantity();
        }

        $pish_per_person    = get_post_meta($order_id, 'ticket_tedad', true);
        $pish_per_person    = !empty($pish_per_person) ? $pish_per_person : get_post_meta($product_id, 'pish_pardakht_per_person', true);
        $pish_per_person    = !empty($pish_per_person) ? $pish_per_person : 1;

        $pish       = get_post_meta($order_id, "_order_total_2", true);
        $pish_final = $pish ?: get_post_meta($order_id, "_order_total", true);

        $item_total = $pish_final / $pish_per_person * $quantity;

        $args = [
            "single_value"  => true,
            "query"         => "SELECT * FROM `wp_zb_booking_history` WHERE `wc_order_id` = $order_id",
        ];
        $response = ez_reservation(array('type' => 'query_execution', 'data' => $args));
        $row = (array)json_decode($response);

        $items[] = [
            'order_id'      => (int)$order_id,
            'product_title' => get_the_title($product_id),
            'tickets_count' => $quantity,
            'purchase_time' => (int)$row['booked_time'],
            'sans_time'     => (int)$row['booking_time'],
            'total_payment' => (int)$item_total,
            'prepaid'       => (int)$pish_final,
            //            'status'        => time() > $row['booking_time'] ? 'بازی کرده اند' : 'در راه شروع بازی',
            'status'        => $order->get_status(),
            'product_url'   => trim_home_url(get_permalink($product_id)),
        ];
    }

    $data[] = [
        'type'  => 'orders',
        'title' => 'رزروهای من',
        'data'  => [
            'tabs'          => [
                [
                    'type'  => 'status',
                    'title' => '',
                    'key'   => 'status',
                    'items' => [
                        [
                            'title' => 'همه',
                            'id'    => '-1',
                        ],
                        [
                            'title' => 'رزرو شده',
                            'id'    => 'reserved',
                        ],
                        [
                            'title' => 'برگزار شده',
                            'id'    => 'held',
                        ],
                        [
                            'title' => 'لغو شده',
                            'id'    => 'cancelled',
                        ],
                    ],
                ],
            ],
            'items'         => $items,
            'pagination'    => [
                'current_page'  => $page_num,
                'total_pages'   => $max_page_num,
            ]
        ]
    ];

    wp_send_json_success($data);
}
//**********************************************************************************************************/
function user_products_api($request)
{
    global $wpdb;

    $user_id = get_user_id_by_token(ez_authorization(true));

    $params = $request->get_params();
    $page_num = (int)$params['page'];

    $page_num       = $page_num ?: 1;
    $items_per_page = 10;

    $user_role = get_user_role($user_id);

    if ($user_role == 'sans_manager') {
        $max_page_num   = ceil((int)($wpdb->get_var("SELECT COUNT(*) FROM `wp_postmeta` WHERE `meta_key` LIKE 'sans_manager' AND `meta_value` LIKE {$user_id}")) / $items_per_page);
        $offset         = ($page_num - 1) * $items_per_page;

        $user_products = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM `wp_postmeta` WHERE `meta_key` = 'sans_manager' AND `meta_value` = %s ORDER BY `meta_value` DESC LIMIT %d, %d",
                (string) $user_id,
                (int) $offset,
                (int) $items_per_page
            )
        );
    } elseif ($user_role == 'compiler') {
        $max_page_num   = ceil((int)($wpdb->get_var("SELECT COUNT(*) FROM `wp_postmeta` WHERE `meta_key` LIKE 'user_ebtal' AND `meta_value` LIKE {$user_id}")) / $items_per_page);
        $offset         = ($page_num - 1) * $items_per_page;

        $user_products = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM `wp_postmeta` WHERE `meta_key` = 'user_ebtal' AND `meta_value` = %s ORDER BY `meta_value` DESC LIMIT %d, %d",
                (string) $user_id,
                (int) $offset,
                (int) $items_per_page
            )
        );
    }

    foreach ($user_products as $user_product) {
        $product_id = $user_product->post_id;

        $votes_count    = (int)get_post_meta($product_id, 'comments_count_new', true);
        $pending_orders = json_decode(ez_reservation(array('type' => 'get_pending_sanses', 'data' => array('product_id' => $product_id))), true);

        $query = "
            SELECT COUNT(*)   
            FROM {$wpdb->prefix}woocommerce_order_items AS order_items  
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_itemmeta   
            ON order_items.order_item_id = order_itemmeta.order_item_id  
            INNER JOIN {$wpdb->prefix}posts AS posts   
            ON order_items.order_id = posts.ID  
            WHERE order_itemmeta.meta_key = '_product_id'   
            AND order_itemmeta.meta_value = %d  
            AND posts.post_status IN ('wc-walletx')
        ";
        $done_orders_count = (int)$wpdb->get_var($wpdb->prepare($query, $product_id)) ?: 0;

        $items[] = [
            'product_id'            => (int)$product_id,
            'title'                 => get_the_title($product_id),
            'image'                 => wp_get_attachment_url(get_post_thumbnail_id($product_id)),
            'done_orders_count'     => $done_orders_count,
            'pending_orders_count'  => $pending_orders ? count($pending_orders) : 0,
            'total_income'          => (int)get_post_meta($product_id, 'total_income', true),
            'average_rate'          => number_format(round(array_sum(get_post_meta($product_id, 'product_rates', true)) / $votes_count / 20 / 5, 2), 2, '.', ''),
            'votes_count'           => $votes_count,
            'active'                => get_post_meta($product_id, 'product_state', true) == 'active' ? 1 : 0,
            'url'                   => trim_home_url(get_permalink($product_id)),
        ];
    }

    $data[] = [
        'type'  => 'products',
        'title' => 'اتاق های من',
        'data'  => [
            'items'         => $items,
            'pagination'    => [
                'current_page'  => $page_num,
                'total_pages'   => $max_page_num,
            ]
        ]
    ];

    wp_send_json_success($data);
}
//**********************************************************************************************************/
function user_tickets_api($request)
{

    $user_id = get_user_id_by_token(ez_authorization(true));

    $params = $request->get_params();
    $status     = $params['status'];
    $page_num   = (int)$params['page'];

    $status     = $status ?: -1;
    $page_num   = $page_num ?: 1;

    $items_per_page = 20;

    $args = [
        'posts_per_page'    => $items_per_page,
        'post_type'         => 'ticketing',
        'author'            => $user_id,
        'post_status'       => 'any',
        'paged'             => $page_num,
    ];

    if ($status != -1)
        if ($status == 'open') {
            $args['meta_query'] = array(
                'relation' => 'AND',
                array( // تیکت بسته نشده باشد.
                    'key'       => 'ticket_closed',
                    'value'     => 1,
                    'compare'   => '!='
                ),
                array( // تیکت در حالت بررسی شده قرار نگرفته باشد.
                    'key'       => 'admin_seen',
                    'value'     => 1,
                    'compare'   => '!='
                ),
                array( // آخرین پیام از سمت یوز باشد.
                    'key'       => 'respond_user_role',
                    'value'     => 'user',
                    'compare'   => '='
                ),
            );
        } elseif ($status == 'closed') {
            $args['meta_key']   = 'ticket_closed';
            $args['meta_value'] = 1;
        } elseif ($status == 'pending') {
            $args['meta_query'] = array(
                'relation' => 'AND',
                array( // تیکت بسته نشده باشد.
                    'key'       => 'ticket_closed',
                    'value'     => 1,
                    'compare'   => '!='
                ),
                array( // تیکت در حالت بررسی شده قرار گرفته باشد.
                    'key'       => 'admin_seen',
                    'value'     => 1,
                    'compare'   => '='
                ),
                array( // آخرین پیام از سمت یوز باشد.
                    'key'       => 'respond_user_role',
                    'value'     => 'user',
                    'compare'   => '='
                ),
            );
        } elseif ($status == 'respond') {
            $args['meta_query'] = array(
                'relation' => 'AND',
                array( // تیکت بسته نشده باشد.
                    'key'       => 'ticket_closed',
                    'value'     => 1,
                    'compare'   => '!='
                ),
                array( // آخرین پیام از سمت ادمین باشد.
                    'key'       => 'respond_user_role',
                    'value'     => 'admin',
                    'compare'   => '='
                ),
            );
        }

    $query = new WP_Query($args);
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            global $post;

            $ticket_id = $post->ID;

            $messages = get_post_meta($ticket_id, 'messages', true);

            $items[] = [
                'id'            => $ticket_id,
                'title'         => get_the_title($ticket_id),
                'sent_time'     => strtotime($post->post_date),
                'updated_time'  => end($messages)['date'],
                'type'          => $post->post_content,
                'status'        => get_ticket_status($ticket_id),
                'rate'          => isset($post->ticket_rate) ? (int)$post->ticket_rate : null,
            ];
        }
        $max_page_num = $query->max_num_pages;
        wp_reset_postdata();
    }

    $data[] = [
        'type'  => 'tickets',
        'title' => 'تیکت های پشتیبانی',
        'data'  => [
            'tabs'          => [
                [
                    'type'  => 'status',
                    'title' => '',
                    'key'   => 'status',
                    'items' => [
                        [
                            'title' => 'همه',
                            'id'    => '-1',
                        ],
                        [
                            'title' => 'باز',
                            'id'    => 'open',
                        ],
                        [
                            'title' => 'بسته شده',
                            'id'    => 'closed',
                        ],
                        [
                            'title' => 'در حال بررسی',
                            'id'    => 'pending',
                        ],
                        [
                            'title' => 'پاسخ داده شده',
                            'id'    => 'respond',
                        ],
                    ],
                ],
            ],
            'items'         => $items,
            'pagination'    => [
                'current_page'  => $page_num,
                'total_pages'   => $max_page_num,
            ]
        ]
    ];

    wp_send_json_success($data);
}
//**********************************************************************************************************/
function user_settings_api($request)
{
    global $wldb;

    $user_id = get_user_id_by_token(ez_authorization(true));

    $user_settings = get_user_meta($user_id, 'user_settings', true);

    if (empty($user_settings))
        $user_settings = [];

    $user_settings['avatar']    = 'http://escapezoom.ir/wp-content/uploads/2024/04/male_avatar_level_1.png';
    $user_settings['user_id']   = $user_id;
    $user_settings['balance']   = $wldb->get_balance($user_id);
    $user_settings['points']    = (int)get_user_points($user_id);
    $user_settings['role']      = (get_userdata($user_id)->roles)[0] == 'customer' ? 'customer' : 'owner';

    $cities_temp    = json_decode('[{"id":1,"name":"اسکو","province_id":1},{"id":2,"name":"اهر","province_id":1},{"id":3,"name":"ایلخچی","province_id":1},{"id":4,"name":"آبش احمد","province_id":1},{"id":5,"name":"آذرشهر","province_id":1},{"id":6,"name":"آقکند","province_id":1},{"id":7,"name":"باسمنج","province_id":1},{"id":8,"name":"بخشایش","province_id":1},{"id":9,"name":"بستان آباد","province_id":1},{"id":10,"name":"بناب","province_id":1},{"id":11,"name":"بناب جدید","province_id":1},{"id":12,"name":"تبریز","province_id":1},{"id":13,"name":"ترک","province_id":1},{"id":14,"name":"ترکمانچای","province_id":1},{"id":15,"name":"تسوج","province_id":1},{"id":16,"name":"تیکمه داش","province_id":1},{"id":17,"name":"جلفا","province_id":1},{"id":18,"name":"خاروانا","province_id":1},{"id":19,"name":"خامنه","province_id":1},{"id":20,"name":"خراجو","province_id":1},{"id":21,"name":"خسروشهر","province_id":1},{"id":22,"name":"خضرلو","province_id":1},{"id":23,"name":"خمارلو","province_id":1},{"id":24,"name":"خواجه","province_id":1},{"id":25,"name":"دوزدوزان","province_id":1},{"id":26,"name":"زرنق","province_id":1},{"id":27,"name":"زنوز","province_id":1},{"id":28,"name":"سراب","province_id":1},{"id":29,"name":"سردرود","province_id":1},{"id":30,"name":"سهند","province_id":1},{"id":31,"name":"سیس","province_id":1},{"id":32,"name":"سیه رود","province_id":1},{"id":33,"name":"شبستر","province_id":1},{"id":34,"name":"شربیان","province_id":1},{"id":35,"name":"شرفخانه","province_id":1},{"id":36,"name":"شندآباد","province_id":1},{"id":37,"name":"صوفیان","province_id":1},{"id":38,"name":"عجب شیر","province_id":1},{"id":39,"name":"قره آغاج","province_id":1},{"id":40,"name":"کشکسرای","province_id":1},{"id":41,"name":"کلوانق","province_id":1},{"id":42,"name":"کلیبر","province_id":1},{"id":43,"name":"کوزه کنان","province_id":1},{"id":44,"name":"گوگان","province_id":1},{"id":45,"name":"لیلان","province_id":1},{"id":46,"name":"مراغه","province_id":1},{"id":47,"name":"مرند","province_id":1},{"id":48,"name":"ملکان","province_id":1},{"id":49,"name":"ملک کیان","province_id":1},{"id":50,"name":"ممقان","province_id":1},{"id":51,"name":"مهربان","province_id":1},{"id":52,"name":"میانه","province_id":1},{"id":53,"name":"نظرکهریزی","province_id":1},{"id":54,"name":"هادی شهر","province_id":1},{"id":55,"name":"هرگلان","province_id":1},{"id":56,"name":"هریس","province_id":1},{"id":57,"name":"هشترود","province_id":1},{"id":58,"name":"هوراند","province_id":1},{"id":59,"name":"وایقان","province_id":1},{"id":60,"name":"ورزقان","province_id":1},{"id":61,"name":"یامچی","province_id":1},{"id":62,"name":"ارومیه","province_id":2},{"id":63,"name":"اشنویه","province_id":2},{"id":64,"name":"ایواوغلی","province_id":2},{"id":65,"name":"آواجیق","province_id":2},{"id":66,"name":"باروق","province_id":2},{"id":67,"name":"بازرگان","province_id":2},{"id":68,"name":"بوکان","province_id":2},{"id":69,"name":"پلدشت","province_id":2},{"id":70,"name":"پیرانشهر","province_id":2},{"id":71,"name":"تازه شهر","province_id":2},{"id":72,"name":"تکاب","province_id":2},{"id":73,"name":"چهاربرج","province_id":2},{"id":74,"name":"خوی","province_id":2},{"id":75,"name":"دیزج دیز","province_id":2},{"id":76,"name":"ربط","province_id":2},{"id":77,"name":"سردشت","province_id":2},{"id":78,"name":"سرو","province_id":2},{"id":79,"name":"سلماس","province_id":2},{"id":80,"name":"سیلوانه","province_id":2},{"id":81,"name":"سیمینه","province_id":2},{"id":82,"name":"سیه چشمه","province_id":2},{"id":83,"name":"شاهین دژ","province_id":2},{"id":84,"name":"شوط","province_id":2},{"id":85,"name":"فیرورق","province_id":2},{"id":86,"name":"قره ضیاءالدین","province_id":2},{"id":87,"name":"قطور","province_id":2},{"id":88,"name":"قوشچی","province_id":2},{"id":89,"name":"کشاورز","province_id":2},{"id":90,"name":"گردکشانه","province_id":2},{"id":91,"name":"ماکو","province_id":2},{"id":92,"name":"محمدیار","province_id":2},{"id":93,"name":"محمودآباد","province_id":2},{"id":94,"name":"مهاباد","province_id":2},{"id":95,"name":"میاندوآب","province_id":2},{"id":96,"name":"میرآباد","province_id":2},{"id":97,"name":"نالوس","province_id":2},{"id":98,"name":"نقده","province_id":2},{"id":99,"name":"نوشین","province_id":2},{"id":100,"name":"اردبیل","province_id":3},{"id":101,"name":"اصلاندوز","province_id":3},{"id":102,"name":"آبی بیگلو","province_id":3},{"id":103,"name":"بیله سوار","province_id":3},{"id":104,"name":"پارس آباد","province_id":3},{"id":105,"name":"تازه کند","province_id":3},{"id":106,"name":"تازه کندانگوت","province_id":3},{"id":107,"name":"جعفرآباد","province_id":3},{"id":108,"name":"خلخال","province_id":3},{"id":109,"name":"رضی","province_id":3},{"id":110,"name":"سرعین","province_id":3},{"id":111,"name":"عنبران","province_id":3},{"id":112,"name":"فخرآباد","province_id":3},{"id":113,"name":"کلور","province_id":3},{"id":114,"name":"کوراییم","province_id":3},{"id":115,"name":"گرمی","province_id":3},{"id":116,"name":"گیوی","province_id":3},{"id":117,"name":"لاهرود","province_id":3},{"id":118,"name":"مشگین شهر","province_id":3},{"id":119,"name":"نمین","province_id":3},{"id":120,"name":"نیر","province_id":3},{"id":121,"name":"هشتجین","province_id":3},{"id":122,"name":"هیر","province_id":3},{"id":123,"name":"ابریشم","province_id":4},{"id":124,"name":"ابوزیدآباد","province_id":4},{"id":125,"name":"اردستان","province_id":4},{"id":126,"name":"اژیه","province_id":4},{"id":127,"name":"اصفهان","province_id":4},{"id":128,"name":"افوس","province_id":4},{"id":129,"name":"انارک","province_id":4},{"id":130,"name":"ایمانشهر","province_id":4},{"id":131,"name":"آران وبیدگل","province_id":4},{"id":132,"name":"بادرود","province_id":4},{"id":133,"name":"باغ بهادران","province_id":4},{"id":134,"name":"بافران","province_id":4},{"id":135,"name":"برزک","province_id":4},{"id":136,"name":"برف انبار","province_id":4},{"id":137,"name":"بهاران شهر","province_id":4},{"id":138,"name":"بهارستان","province_id":4},{"id":139,"name":"بوئین و میاندشت","province_id":4},{"id":140,"name":"پیربکران","province_id":4},{"id":141,"name":"تودشک","province_id":4},{"id":142,"name":"تیران","province_id":4},{"id":143,"name":"جندق","province_id":4},{"id":144,"name":"جوزدان","province_id":4},{"id":145,"name":"جوشقان و کامو","province_id":4},{"id":146,"name":"چادگان","province_id":4},{"id":147,"name":"چرمهین","province_id":4},{"id":148,"name":"چمگردان","province_id":4},{"id":149,"name":"حبیب آباد","province_id":4},{"id":150,"name":"حسن آباد","province_id":4},{"id":151,"name":"حنا","province_id":4},{"id":152,"name":"خالدآباد","province_id":4},{"id":153,"name":"خمینی شهر","province_id":4},{"id":154,"name":"خوانسار","province_id":4},{"id":155,"name":"خور","province_id":4},{"id":157,"name":"خورزوق","province_id":4},{"id":158,"name":"داران","province_id":4},{"id":159,"name":"دامنه","province_id":4},{"id":160,"name":"درچه","province_id":4},{"id":161,"name":"دستگرد","province_id":4},{"id":162,"name":"دهاقان","province_id":4},{"id":163,"name":"دهق","province_id":4},{"id":164,"name":"دولت آباد","province_id":4},{"id":165,"name":"دیزیچه","province_id":4},{"id":166,"name":"رزوه","province_id":4},{"id":167,"name":"رضوانشهر","province_id":4},{"id":168,"name":"زاینده رود","province_id":4},{"id":169,"name":"زرین شهر","province_id":4},{"id":170,"name":"زواره","province_id":4},{"id":171,"name":"زیباشهر","province_id":4},{"id":172,"name":"سده لنجان","province_id":4},{"id":173,"name":"سفیدشهر","province_id":4},{"id":174,"name":"سگزی","province_id":4},{"id":175,"name":"سمیرم","province_id":4},{"id":176,"name":"شاهین شهر","province_id":4},{"id":177,"name":"شهرضا","province_id":4},{"id":178,"name":"طالخونچه","province_id":4},{"id":179,"name":"عسگران","province_id":4},{"id":180,"name":"علویجه","province_id":4},{"id":181,"name":"فرخی","province_id":4},{"id":182,"name":"فریدونشهر","province_id":4},{"id":183,"name":"فلاورجان","province_id":4},{"id":184,"name":"فولادشهر","province_id":4},{"id":185,"name":"قمصر","province_id":4},{"id":186,"name":"قهجاورستان","province_id":4},{"id":187,"name":"قهدریجان","province_id":4},{"id":188,"name":"کاشان","province_id":4},{"id":189,"name":"کرکوند","province_id":4},{"id":190,"name":"کلیشاد و سودرجان","province_id":4},{"id":191,"name":"کمشچه","province_id":4},{"id":192,"name":"کمه","province_id":4},{"id":193,"name":"کهریزسنگ","province_id":4},{"id":194,"name":"کوشک","province_id":4},{"id":195,"name":"کوهپایه","province_id":4},{"id":196,"name":"گرگاب","province_id":4},{"id":197,"name":"گزبرخوار","province_id":4},{"id":198,"name":"گلپایگان","province_id":4},{"id":199,"name":"گلدشت","province_id":4},{"id":200,"name":"گلشهر","province_id":4},{"id":201,"name":"گوگد","province_id":4},{"id":202,"name":"لای بید","province_id":4},{"id":203,"name":"مبارکه","province_id":4},{"id":204,"name":"مجلسی","province_id":4},{"id":205,"name":"محمدآباد","province_id":4},{"id":206,"name":"مشکات","province_id":4},{"id":207,"name":"منظریه","province_id":4},{"id":208,"name":"مهاباد","province_id":4},{"id":209,"name":"میمه","province_id":4},{"id":210,"name":"نائین","province_id":4},{"id":211,"name":"نجف آباد","province_id":4},{"id":212,"name":"نصرآباد","province_id":4},{"id":213,"name":"نطنز","province_id":4},{"id":214,"name":"نوش آباد","province_id":4},{"id":215,"name":"نیاسر","province_id":4},{"id":216,"name":"نیک آباد","province_id":4},{"id":217,"name":"هرند","province_id":4},{"id":218,"name":"ورزنه","province_id":4},{"id":219,"name":"ورنامخواست","province_id":4},{"id":220,"name":"وزوان","province_id":4},{"id":221,"name":"ونک","province_id":4},{"id":222,"name":"اسارا","province_id":5},{"id":223,"name":"اشتهارد","province_id":5},{"id":224,"name":"تنکمان","province_id":5},{"id":225,"name":"چهارباغ","province_id":5},{"id":226,"name":"سعید آباد","province_id":5},{"id":227,"name":"شهر جدید هشتگرد","province_id":5},{"id":228,"name":"طالقان","province_id":5},{"id":229,"name":"کرج","province_id":5},{"id":230,"name":"کمال شهر","province_id":5},{"id":231,"name":"کوهسار","province_id":5},{"id":232,"name":"گرمدره","province_id":5},{"id":233,"name":"ماهدشت","province_id":5},{"id":234,"name":"محمدشهر","province_id":5},{"id":235,"name":"مشکین دشت","province_id":5},{"id":236,"name":"نظرآباد","province_id":5},{"id":237,"name":"هشتگرد","province_id":5},{"id":238,"name":"ارکواز","province_id":6},{"id":239,"name":"ایلام","province_id":6},{"id":240,"name":"ایوان","province_id":6},{"id":241,"name":"آبدانان","province_id":6},{"id":242,"name":"آسمان آباد","province_id":6},{"id":243,"name":"بدره","province_id":6},{"id":244,"name":"پهله","province_id":6},{"id":245,"name":"توحید","province_id":6},{"id":246,"name":"چوار","province_id":6},{"id":247,"name":"دره شهر","province_id":6},{"id":248,"name":"دلگشا","province_id":6},{"id":249,"name":"دهلران","province_id":6},{"id":250,"name":"زرنه","province_id":6},{"id":251,"name":"سراب باغ","province_id":6},{"id":252,"name":"سرابله","province_id":6},{"id":253,"name":"صالح آباد","province_id":6},{"id":254,"name":"لومار","province_id":6},{"id":255,"name":"مهران","province_id":6},{"id":256,"name":"مورموری","province_id":6},{"id":257,"name":"موسیان","province_id":6},{"id":258,"name":"میمه","province_id":6},{"id":259,"name":"امام حسن","province_id":7},{"id":260,"name":"انارستان","province_id":7},{"id":261,"name":"اهرم","province_id":7},{"id":262,"name":"آب پخش","province_id":7},{"id":263,"name":"آبدان","province_id":7},{"id":264,"name":"برازجان","province_id":7},{"id":265,"name":"بردخون","province_id":7},{"id":266,"name":"بندردیر","province_id":7},{"id":267,"name":"بندردیلم","province_id":7},{"id":268,"name":"بندرریگ","province_id":7},{"id":269,"name":"بندرکنگان","province_id":7},{"id":270,"name":"بندرگناوه","province_id":7},{"id":271,"name":"بنک","province_id":7},{"id":272,"name":"بوشهر","province_id":7},{"id":273,"name":"تنگ ارم","province_id":7},{"id":274,"name":"جم","province_id":7},{"id":275,"name":"چغادک","province_id":7},{"id":276,"name":"خارک","province_id":7},{"id":277,"name":"خورموج","province_id":7},{"id":278,"name":"دالکی","province_id":7},{"id":279,"name":"دلوار","province_id":7},{"id":280,"name":"ریز","province_id":7},{"id":281,"name":"سعدآباد","province_id":7},{"id":282,"name":"سیراف","province_id":7},{"id":283,"name":"شبانکاره","province_id":7},{"id":284,"name":"شنبه","province_id":7},{"id":285,"name":"عسلویه","province_id":7},{"id":286,"name":"کاکی","province_id":7},{"id":287,"name":"کلمه","province_id":7},{"id":288,"name":"نخل تقی","province_id":7},{"id":289,"name":"وحدتیه","province_id":7},{"id":290,"name":"ارجمند","province_id":8},{"id":291,"name":"اسلامشهر","province_id":8},{"id":292,"name":"اندیشه","province_id":8},{"id":293,"name":"آبسرد","province_id":8},{"id":294,"name":"آبعلی","province_id":8},{"id":295,"name":"باغستان","province_id":8},{"id":296,"name":"باقرشهر","province_id":8},{"id":297,"name":"بومهن","province_id":8},{"id":298,"name":"پاکدشت","province_id":8},{"id":299,"name":"پردیس","province_id":8},{"id":300,"name":"پیشوا","province_id":8},{"id":301,"name":"تهران","province_id":8},{"id":302,"name":"جوادآباد","province_id":8},{"id":303,"name":"چهاردانگه","province_id":8},{"id":304,"name":"حسن آباد","province_id":8},{"id":305,"name":"دماوند","province_id":8},{"id":306,"name":"دیزین","province_id":8},{"id":307,"name":"شهر ری","province_id":8},{"id":308,"name":"رباط کریم","province_id":8},{"id":309,"name":"رودهن","province_id":8},{"id":310,"name":"شاهدشهر","province_id":8},{"id":311,"name":"شریف آباد","province_id":8},{"id":312,"name":"شمشک","province_id":8},{"id":313,"name":"شهریار","province_id":8},{"id":314,"name":"صالح آباد","province_id":8},{"id":315,"name":"صباشهر","province_id":8},{"id":316,"name":"صفادشت","province_id":8},{"id":317,"name":"فردوسیه","province_id":8},{"id":318,"name":"فشم","province_id":8},{"id":319,"name":"فیروزکوه","province_id":8},{"id":320,"name":"قدس","province_id":8},{"id":321,"name":"قرچک","province_id":8},{"id":322,"name":"کهریزک","province_id":8},{"id":323,"name":"کیلان","province_id":8},{"id":324,"name":"گلستان","province_id":8},{"id":325,"name":"لواسان","province_id":8},{"id":326,"name":"ملارد","province_id":8},{"id":327,"name":"میگون","province_id":8},{"id":328,"name":"نسیم شهر","province_id":8},{"id":329,"name":"نصیرآباد","province_id":8},{"id":330,"name":"وحیدیه","province_id":8},{"id":331,"name":"ورامین","province_id":8},{"id":332,"name":"اردل","province_id":9},{"id":333,"name":"آلونی","province_id":9},{"id":334,"name":"باباحیدر","province_id":9},{"id":335,"name":"بروجن","province_id":9},{"id":336,"name":"بلداجی","province_id":9},{"id":337,"name":"بن","province_id":9},{"id":338,"name":"جونقان","province_id":9},{"id":339,"name":"چلگرد","province_id":9},{"id":340,"name":"سامان","province_id":9},{"id":341,"name":"سفیددشت","province_id":9},{"id":342,"name":"سودجان","province_id":9},{"id":343,"name":"سورشجان","province_id":9},{"id":344,"name":"شلمزار","province_id":9},{"id":345,"name":"شهرکرد","province_id":9},{"id":346,"name":"طاقانک","province_id":9},{"id":347,"name":"فارسان","province_id":9},{"id":348,"name":"فرادنبه","province_id":9},{"id":349,"name":"فرخ شهر","province_id":9},{"id":350,"name":"کیان","province_id":9},{"id":351,"name":"گندمان","province_id":9},{"id":352,"name":"گهرو","province_id":9},{"id":353,"name":"لردگان","province_id":9},{"id":354,"name":"مال خلیفه","province_id":9},{"id":355,"name":"ناغان","province_id":9},{"id":356,"name":"نافچ","province_id":9},{"id":357,"name":"نقنه","province_id":9},{"id":358,"name":"هفشجان","province_id":9},{"id":359,"name":"ارسک","province_id":10},{"id":360,"name":"اسدیه","province_id":10},{"id":361,"name":"اسفدن","province_id":10},{"id":362,"name":"اسلامیه","province_id":10},{"id":363,"name":"آرین شهر","province_id":10},{"id":364,"name":"آیسک","province_id":10},{"id":365,"name":"بشرویه","province_id":10},{"id":366,"name":"بیرجند","province_id":10},{"id":367,"name":"حاجی آباد","province_id":10},{"id":368,"name":"خضری دشت بیاض","province_id":10},{"id":369,"name":"خوسف","province_id":10},{"id":370,"name":"زهان","province_id":10},{"id":371,"name":"سرایان","province_id":10},{"id":372,"name":"سربیشه","province_id":10},{"id":373,"name":"سه قلعه","province_id":10},{"id":374,"name":"شوسف","province_id":10},{"id":375,"name":"طبس ","province_id":10},{"id":376,"name":"فردوس","province_id":10},{"id":377,"name":"قاین","province_id":10},{"id":378,"name":"قهستان","province_id":10},{"id":379,"name":"محمدشهر","province_id":10},{"id":380,"name":"مود","province_id":10},{"id":381,"name":"نهبندان","province_id":10},{"id":382,"name":"نیمبلوک","province_id":10},{"id":383,"name":"احمدآباد صولت","province_id":11},{"id":384,"name":"انابد","province_id":11},{"id":385,"name":"باجگیران","province_id":11},{"id":386,"name":"باخرز","province_id":11},{"id":387,"name":"بار","province_id":11},{"id":388,"name":"بایگ","province_id":11},{"id":389,"name":"بجستان","province_id":11},{"id":390,"name":"بردسکن","province_id":11},{"id":391,"name":"بیدخت","province_id":11},{"id":392,"name":"تایباد","province_id":11},{"id":393,"name":"تربت جام","province_id":11},{"id":394,"name":"تربت حیدریه","province_id":11},{"id":395,"name":"جغتای","province_id":11},{"id":396,"name":"جنگل","province_id":11},{"id":397,"name":"چاپشلو","province_id":11},{"id":398,"name":"چکنه","province_id":11},{"id":399,"name":"چناران","province_id":11},{"id":400,"name":"خرو","province_id":11},{"id":401,"name":"خلیل آباد","province_id":11},{"id":402,"name":"خواف","province_id":11},{"id":403,"name":"داورزن","province_id":11},{"id":404,"name":"درگز","province_id":11},{"id":405,"name":"در رود","province_id":11},{"id":406,"name":"دولت آباد","province_id":11},{"id":407,"name":"رباط سنگ","province_id":11},{"id":408,"name":"رشتخوار","province_id":11},{"id":409,"name":"رضویه","province_id":11},{"id":410,"name":"روداب","province_id":11},{"id":411,"name":"ریوش","province_id":11},{"id":412,"name":"سبزوار","province_id":11},{"id":413,"name":"سرخس","province_id":11},{"id":414,"name":"سفیدسنگ","province_id":11},{"id":415,"name":"سلامی","province_id":11},{"id":416,"name":"سلطان آباد","province_id":11},{"id":417,"name":"سنگان","province_id":11},{"id":418,"name":"شادمهر","province_id":11},{"id":419,"name":"شاندیز","province_id":11},{"id":420,"name":"ششتمد","province_id":11},{"id":421,"name":"شهرآباد","province_id":11},{"id":422,"name":"شهرزو","province_id":11},{"id":423,"name":"صالح آباد","province_id":11},{"id":424,"name":"طرقبه","province_id":11},{"id":425,"name":"عشق آباد","province_id":11},{"id":426,"name":"فرهادگرد","province_id":11},{"id":427,"name":"فریمان","province_id":11},{"id":428,"name":"فیروزه","province_id":11},{"id":429,"name":"فیض آباد","province_id":11},{"id":430,"name":"قاسم آباد","province_id":11},{"id":431,"name":"قدمگاه","province_id":11},{"id":432,"name":"قلندرآباد","province_id":11},{"id":433,"name":"قوچان","province_id":11},{"id":434,"name":"کاخک","province_id":11},{"id":435,"name":"کاریز","province_id":11},{"id":436,"name":"کاشمر","province_id":11},{"id":437,"name":"کدکن","province_id":11},{"id":438,"name":"کلات","province_id":11},{"id":439,"name":"کندر","province_id":11},{"id":440,"name":"گلمکان","province_id":11},{"id":441,"name":"گناباد","province_id":11},{"id":442,"name":"لطف آباد","province_id":11},{"id":443,"name":"مزدآوند","province_id":11},{"id":444,"name":"مشهد","province_id":11},{"id":445,"name":"ملک آباد","province_id":11},{"id":446,"name":"نشتیفان","province_id":11},{"id":447,"name":"نصرآباد","province_id":11},{"id":448,"name":"نقاب","province_id":11},{"id":449,"name":"نوخندان","province_id":11},{"id":450,"name":"نیشابور","province_id":11},{"id":451,"name":"نیل شهر","province_id":11},{"id":452,"name":"همت آباد","province_id":11},{"id":453,"name":"یونسی","province_id":11},{"id":454,"name":"اسفراین","province_id":12},{"id":455,"name":"ایور","province_id":12},{"id":456,"name":"آشخانه","province_id":12},{"id":457,"name":"بجنورد","province_id":12},{"id":458,"name":"پیش قلعه","province_id":12},{"id":459,"name":"تیتکانلو","province_id":12},{"id":460,"name":"جاجرم","province_id":12},{"id":461,"name":"حصارگرمخان","province_id":12},{"id":462,"name":"درق","province_id":12},{"id":463,"name":"راز","province_id":12},{"id":464,"name":"سنخواست","province_id":12},{"id":465,"name":"شوقان","province_id":12},{"id":466,"name":"شیروان","province_id":12},{"id":467,"name":"صفی آباد","province_id":12},{"id":468,"name":"فاروج","province_id":12},{"id":469,"name":"قاضی","province_id":12},{"id":470,"name":"گرمه","province_id":12},{"id":471,"name":"لوجلی","province_id":12},{"id":472,"name":"اروندکنار","province_id":13},{"id":473,"name":"الوان","province_id":13},{"id":474,"name":"امیدیه","province_id":13},{"id":475,"name":"اندیمشک","province_id":13},{"id":476,"name":"اهواز","province_id":13},{"id":477,"name":"ایذه","province_id":13},{"id":478,"name":"آبادان","province_id":13},{"id":479,"name":"آغاجاری","province_id":13},{"id":480,"name":"باغ ملک","province_id":13},{"id":481,"name":"بستان","province_id":13},{"id":482,"name":"بندرامام خمینی","province_id":13},{"id":483,"name":"بندرماهشهر","province_id":13},{"id":484,"name":"بهبهان","province_id":13},{"id":485,"name":"ترکالکی","province_id":13},{"id":486,"name":"جایزان","province_id":13},{"id":487,"name":"چمران","province_id":13},{"id":488,"name":"چویبده","province_id":13},{"id":489,"name":"حر","province_id":13},{"id":490,"name":"حسینیه","province_id":13},{"id":491,"name":"حمزه","province_id":13},{"id":492,"name":"حمیدیه","province_id":13},{"id":493,"name":"خرمشهر","province_id":13},{"id":494,"name":"دارخوین","province_id":13},{"id":495,"name":"دزآب","province_id":13},{"id":496,"name":"دزفول","province_id":13},{"id":497,"name":"دهدز","province_id":13},{"id":498,"name":"رامشیر","province_id":13},{"id":499,"name":"رامهرمز","province_id":13},{"id":500,"name":"رفیع","province_id":13},{"id":501,"name":"زهره","province_id":13},{"id":502,"name":"سالند","province_id":13},{"id":503,"name":"سردشت","province_id":13},{"id":504,"name":"سوسنگرد","province_id":13},{"id":505,"name":"شادگان","province_id":13},{"id":506,"name":"شاوور","province_id":13},{"id":507,"name":"شرافت","province_id":13},{"id":508,"name":"شوش","province_id":13},{"id":509,"name":"شوشتر","province_id":13},{"id":510,"name":"شیبان","province_id":13},{"id":511,"name":"صالح شهر","province_id":13},{"id":512,"name":"صفی آباد","province_id":13},{"id":513,"name":"صیدون","province_id":13},{"id":514,"name":"قلعه تل","province_id":13},{"id":515,"name":"قلعه خواجه","province_id":13},{"id":516,"name":"گتوند","province_id":13},{"id":517,"name":"لالی","province_id":13},{"id":518,"name":"مسجدسلیمان","province_id":13},{"id":520,"name":"ملاثانی","province_id":13},{"id":521,"name":"میانرود","province_id":13},{"id":522,"name":"مینوشهر","province_id":13},{"id":523,"name":"هفتگل","province_id":13},{"id":524,"name":"هندیجان","province_id":13},{"id":525,"name":"هویزه","province_id":13},{"id":526,"name":"ویس","province_id":13},{"id":527,"name":"ابهر","province_id":14},{"id":528,"name":"ارمغان خانه","province_id":14},{"id":529,"name":"آب بر","province_id":14},{"id":530,"name":"چورزق","province_id":14},{"id":531,"name":"حلب","province_id":14},{"id":532,"name":"خرمدره","province_id":14},{"id":533,"name":"دندی","province_id":14},{"id":534,"name":"زرین آباد","province_id":14},{"id":535,"name":"زرین رود","province_id":14},{"id":536,"name":"زنجان","province_id":14},{"id":537,"name":"سجاس","province_id":14},{"id":538,"name":"سلطانیه","province_id":14},{"id":539,"name":"سهرورد","province_id":14},{"id":540,"name":"صائین قلعه","province_id":14},{"id":541,"name":"قیدار","province_id":14},{"id":542,"name":"گرماب","province_id":14},{"id":543,"name":"ماه نشان","province_id":14},{"id":544,"name":"هیدج","province_id":14},{"id":545,"name":"امیریه","province_id":15},{"id":546,"name":"ایوانکی","province_id":15},{"id":547,"name":"آرادان","province_id":15},{"id":548,"name":"بسطام","province_id":15},{"id":549,"name":"بیارجمند","province_id":15},{"id":550,"name":"دامغان","province_id":15},{"id":551,"name":"درجزین","province_id":15},{"id":552,"name":"دیباج","province_id":15},{"id":553,"name":"سرخه","province_id":15},{"id":554,"name":"سمنان","province_id":15},{"id":555,"name":"شاهرود","province_id":15},{"id":556,"name":"شهمیرزاد","province_id":15},{"id":557,"name":"کلاته خیج","province_id":15},{"id":558,"name":"گرمسار","province_id":15},{"id":559,"name":"مجن","province_id":15},{"id":560,"name":"مهدی شهر","province_id":15},{"id":561,"name":"میامی","province_id":15},{"id":562,"name":"ادیمی","province_id":16},{"id":563,"name":"اسپکه","province_id":16},{"id":564,"name":"ایرانشهر","province_id":16},{"id":565,"name":"بزمان","province_id":16},{"id":566,"name":"بمپور","province_id":16},{"id":567,"name":"بنت","province_id":16},{"id":568,"name":"بنجار","province_id":16},{"id":569,"name":"پیشین","province_id":16},{"id":570,"name":"جالق","province_id":16},{"id":571,"name":"چابهار","province_id":16},{"id":572,"name":"خاش","province_id":16},{"id":573,"name":"دوست محمد","province_id":16},{"id":574,"name":"راسک","province_id":16},{"id":575,"name":"زابل","province_id":16},{"id":576,"name":"زابلی","province_id":16},{"id":577,"name":"زاهدان","province_id":16},{"id":578,"name":"زهک","province_id":16},{"id":579,"name":"سراوان","province_id":16},{"id":580,"name":"سرباز","province_id":16},{"id":581,"name":"سوران","province_id":16},{"id":582,"name":"سیرکان","province_id":16},{"id":583,"name":"علی اکبر","province_id":16},{"id":584,"name":"فنوج","province_id":16},{"id":585,"name":"قصرقند","province_id":16},{"id":586,"name":"کنارک","province_id":16},{"id":587,"name":"گشت","province_id":16},{"id":588,"name":"گلمورتی","province_id":16},{"id":589,"name":"محمدان","province_id":16},{"id":590,"name":"محمدآباد","province_id":16},{"id":591,"name":"محمدی","province_id":16},{"id":592,"name":"میرجاوه","province_id":16},{"id":593,"name":"نصرت آباد","province_id":16},{"id":594,"name":"نگور","province_id":16},{"id":595,"name":"نوک آباد","province_id":16},{"id":596,"name":"نیک شهر","province_id":16},{"id":597,"name":"هیدوچ","province_id":16},{"id":598,"name":"اردکان","province_id":17},{"id":599,"name":"ارسنجان","province_id":17},{"id":600,"name":"استهبان","province_id":17},{"id":601,"name":"اشکنان","province_id":17},{"id":602,"name":"افزر","province_id":17},{"id":603,"name":"اقلید","province_id":17},{"id":604,"name":"امام شهر","province_id":17},{"id":605,"name":"اهل","province_id":17},{"id":606,"name":"اوز","province_id":17},{"id":607,"name":"ایج","province_id":17},{"id":608,"name":"ایزدخواست","province_id":17},{"id":609,"name":"آباده","province_id":17},{"id":610,"name":"آباده طشک","province_id":17},{"id":611,"name":"باب انار","province_id":17},{"id":612,"name":"بالاده","province_id":17},{"id":613,"name":"بنارویه","province_id":17},{"id":614,"name":"بهمن","province_id":17},{"id":615,"name":"بوانات","province_id":17},{"id":616,"name":"بیرم","province_id":17},{"id":617,"name":"بیضا","province_id":17},{"id":618,"name":"جنت شهر","province_id":17},{"id":619,"name":"جهرم","province_id":17},{"id":620,"name":"جویم","province_id":17},{"id":621,"name":"زرین دشت","province_id":17},{"id":622,"name":"حسن آباد","province_id":17},{"id":623,"name":"خان زنیان","province_id":17},{"id":624,"name":"خاوران","province_id":17},{"id":625,"name":"خرامه","province_id":17},{"id":626,"name":"خشت","province_id":17},{"id":627,"name":"خنج","province_id":17},{"id":628,"name":"خور","province_id":17},{"id":629,"name":"داراب","province_id":17},{"id":630,"name":"داریان","province_id":17},{"id":631,"name":"دبیران","province_id":17},{"id":632,"name":"دژکرد","province_id":17},{"id":633,"name":"دهرم","province_id":17},{"id":634,"name":"دوبرجی","province_id":17},{"id":635,"name":"رامجرد","province_id":17},{"id":636,"name":"رونیز","province_id":17},{"id":637,"name":"زاهدشهر","province_id":17},{"id":638,"name":"زرقان","province_id":17},{"id":639,"name":"سده","province_id":17},{"id":640,"name":"سروستان","province_id":17},{"id":641,"name":"سعادت شهر","province_id":17},{"id":642,"name":"سورمق","province_id":17},{"id":643,"name":"سیدان","province_id":17},{"id":644,"name":"ششده","province_id":17},{"id":645,"name":"شهرپیر","province_id":17},{"id":646,"name":"شهرصدرا","province_id":17},{"id":647,"name":"شیراز","province_id":17},{"id":648,"name":"صغاد","province_id":17},{"id":649,"name":"صفاشهر","province_id":17},{"id":650,"name":"علامرودشت","province_id":17},{"id":651,"name":"فدامی","province_id":17},{"id":652,"name":"فراشبند","province_id":17},{"id":653,"name":"فسا","province_id":17},{"id":654,"name":"فیروزآباد","province_id":17},{"id":655,"name":"قائمیه","province_id":17},{"id":656,"name":"قادرآباد","province_id":17},{"id":657,"name":"قطب آباد","province_id":17},{"id":658,"name":"قطرویه","province_id":17},{"id":659,"name":"قیر","province_id":17},{"id":660,"name":"کارزین (فتح آباد)","province_id":17},{"id":661,"name":"کازرون","province_id":17},{"id":662,"name":"کامفیروز","province_id":17},{"id":663,"name":"کره ای","province_id":17},{"id":664,"name":"کنارتخته","province_id":17},{"id":665,"name":"کوار","province_id":17},{"id":666,"name":"گراش","province_id":17},{"id":667,"name":"گله دار","province_id":17},{"id":668,"name":"لار","province_id":17},{"id":669,"name":"لامرد","province_id":17},{"id":670,"name":"لپویی","province_id":17},{"id":671,"name":"لطیفی","province_id":17},{"id":672,"name":"مبارک آباددیز","province_id":17},{"id":673,"name":"مرودشت","province_id":17},{"id":674,"name":"مشکان","province_id":17},{"id":675,"name":"مصیری","province_id":17},{"id":676,"name":"مهر","province_id":17},{"id":677,"name":"میمند","province_id":17},{"id":678,"name":"نوبندگان","province_id":17},{"id":679,"name":"نوجین","province_id":17},{"id":680,"name":"نودان","province_id":17},{"id":681,"name":"نورآباد","province_id":17},{"id":682,"name":"نی ریز","province_id":17},{"id":683,"name":"وراوی","province_id":17},{"id":684,"name":"ارداق","province_id":18},{"id":685,"name":"اسفرورین","province_id":18},{"id":686,"name":"اقبالیه","province_id":18},{"id":687,"name":"الوند","province_id":18},{"id":688,"name":"آبگرم","province_id":18},{"id":689,"name":"آبیک","province_id":18},{"id":690,"name":"آوج","province_id":18},{"id":691,"name":"بوئین زهرا","province_id":18},{"id":692,"name":"بیدستان","province_id":18},{"id":693,"name":"تاکستان","province_id":18},{"id":694,"name":"خاکعلی","province_id":18},{"id":695,"name":"خرمدشت","province_id":18},{"id":696,"name":"دانسفهان","province_id":18},{"id":697,"name":"رازمیان","province_id":18},{"id":698,"name":"سگزآباد","province_id":18},{"id":699,"name":"سیردان","province_id":18},{"id":700,"name":"شال","province_id":18},{"id":701,"name":"شریفیه","province_id":18},{"id":702,"name":"ضیاآباد","province_id":18},{"id":703,"name":"قزوین","province_id":18},{"id":704,"name":"کوهین","province_id":18},{"id":705,"name":"محمدیه","province_id":18},{"id":706,"name":"محمودآباد نمونه","province_id":18},{"id":707,"name":"معلم کلایه","province_id":18},{"id":708,"name":"نرجه","province_id":18},{"id":709,"name":"جعفریه","province_id":19},{"id":710,"name":"دستجرد","province_id":19},{"id":711,"name":"سلفچگان","province_id":19},{"id":712,"name":"قم","province_id":19},{"id":713,"name":"قنوات","province_id":19},{"id":714,"name":"کهک","province_id":19},{"id":715,"name":"آرمرده","province_id":20},{"id":716,"name":"بابارشانی","province_id":20},{"id":717,"name":"بانه","province_id":20},{"id":718,"name":"بلبان آباد","province_id":20},{"id":719,"name":"بوئین سفلی","province_id":20},{"id":720,"name":"بیجار","province_id":20},{"id":721,"name":"چناره","province_id":20},{"id":722,"name":"دزج","province_id":20},{"id":723,"name":"دلبران","province_id":20},{"id":724,"name":"دهگلان","province_id":20},{"id":725,"name":"دیواندره","province_id":20},{"id":726,"name":"زرینه","province_id":20},{"id":727,"name":"سروآباد","province_id":20},{"id":728,"name":"سریش آباد","province_id":20},{"id":729,"name":"سقز","province_id":20},{"id":730,"name":"سنندج","province_id":20},{"id":731,"name":"شویشه","province_id":20},{"id":732,"name":"صاحب","province_id":20},{"id":733,"name":"قروه","province_id":20},{"id":734,"name":"کامیاران","province_id":20},{"id":735,"name":"کانی دینار","province_id":20},{"id":736,"name":"کانی سور","province_id":20},{"id":737,"name":"مریوان","province_id":20},{"id":738,"name":"موچش","province_id":20},{"id":739,"name":"یاسوکند","province_id":20},{"id":740,"name":"اختیارآباد","province_id":21},{"id":741,"name":"ارزوئیه","province_id":21},{"id":742,"name":"امین شهر","province_id":21},{"id":743,"name":"انار","province_id":21},{"id":744,"name":"اندوهجرد","province_id":21},{"id":745,"name":"باغین","province_id":21},{"id":746,"name":"بافت","province_id":21},{"id":747,"name":"بردسیر","province_id":21},{"id":748,"name":"بروات","province_id":21},{"id":749,"name":"بزنجان","province_id":21},{"id":750,"name":"بم","province_id":21},{"id":751,"name":"بهرمان","province_id":21},{"id":752,"name":"پاریز","province_id":21},{"id":753,"name":"جبالبارز","province_id":21},{"id":754,"name":"جوپار","province_id":21},{"id":755,"name":"جوزم","province_id":21},{"id":756,"name":"جیرفت","province_id":21},{"id":757,"name":"چترود","province_id":21},{"id":758,"name":"خاتون آباد","province_id":21},{"id":759,"name":"خانوک","province_id":21},{"id":760,"name":"خورسند","province_id":21},{"id":761,"name":"درب بهشت","province_id":21},{"id":762,"name":"دهج","province_id":21},{"id":763,"name":"رابر","province_id":21},{"id":764,"name":"راور","province_id":21},{"id":765,"name":"راین","province_id":21},{"id":766,"name":"رفسنجان","province_id":21},{"id":767,"name":"رودبار","province_id":21},{"id":768,"name":"ریحان شهر","province_id":21},{"id":769,"name":"زرند","province_id":21},{"id":770,"name":"زنگی آباد","province_id":21},{"id":771,"name":"زیدآباد","province_id":21},{"id":772,"name":"سیرجان","province_id":21},{"id":773,"name":"شهداد","province_id":21},{"id":774,"name":"شهربابک","province_id":21},{"id":775,"name":"صفائیه","province_id":21},{"id":776,"name":"عنبرآباد","province_id":21},{"id":777,"name":"فاریاب","province_id":21},{"id":778,"name":"فهرج","province_id":21},{"id":779,"name":"قلعه گنج","province_id":21},{"id":780,"name":"کاظم آباد","province_id":21},{"id":781,"name":"کرمان","province_id":21},{"id":782,"name":"کشکوئیه","province_id":21},{"id":783,"name":"کهنوج","province_id":21},{"id":784,"name":"کوهبنان","province_id":21},{"id":785,"name":"کیانشهر","province_id":21},{"id":786,"name":"گلباف","province_id":21},{"id":787,"name":"گلزار","province_id":21},{"id":788,"name":"لاله زار","province_id":21},{"id":789,"name":"ماهان","province_id":21},{"id":790,"name":"محمدآباد","province_id":21},{"id":791,"name":"محی آباد","province_id":21},{"id":792,"name":"مردهک","province_id":21},{"id":793,"name":"مس سرچشمه","province_id":21},{"id":794,"name":"منوجان","province_id":21},{"id":795,"name":"نجف شهر","province_id":21},{"id":796,"name":"نرماشیر","province_id":21},{"id":797,"name":"نظام شهر","province_id":21},{"id":798,"name":"نگار","province_id":21},{"id":799,"name":"نودژ","province_id":21},{"id":800,"name":"هجدک","province_id":21},{"id":801,"name":"یزدان شهر","province_id":21},{"id":802,"name":"ازگله","province_id":22},{"id":803,"name":"اسلام آباد غرب","province_id":22},{"id":804,"name":"باینگان","province_id":22},{"id":805,"name":"بیستون","province_id":22},{"id":806,"name":"پاوه","province_id":22},{"id":807,"name":"تازه آباد","province_id":22},{"id":808,"name":"جوان رود","province_id":22},{"id":809,"name":"حمیل","province_id":22},{"id":810,"name":"ماهیدشت","province_id":22},{"id":811,"name":"روانسر","province_id":22},{"id":812,"name":"سرپل ذهاب","province_id":22},{"id":813,"name":"سرمست","province_id":22},{"id":814,"name":"سطر","province_id":22},{"id":815,"name":"سنقر","province_id":22},{"id":816,"name":"سومار","province_id":22},{"id":817,"name":"شاهو","province_id":22},{"id":818,"name":"صحنه","province_id":22},{"id":819,"name":"قصرشیرین","province_id":22},{"id":820,"name":"کرمانشاه","province_id":22},{"id":821,"name":"کرندغرب","province_id":22},{"id":822,"name":"کنگاور","province_id":22},{"id":823,"name":"کوزران","province_id":22},{"id":824,"name":"گهواره","province_id":22},{"id":825,"name":"گیلانغرب","province_id":22},{"id":826,"name":"میان راهان","province_id":22},{"id":827,"name":"نودشه","province_id":22},{"id":828,"name":"نوسود","province_id":22},{"id":829,"name":"هرسین","province_id":22},{"id":830,"name":"هلشی","province_id":22},{"id":831,"name":"باشت","province_id":23},{"id":832,"name":"پاتاوه","province_id":23},{"id":833,"name":"چرام","province_id":23},{"id":834,"name":"چیتاب","province_id":23},{"id":835,"name":"دهدشت","province_id":23},{"id":836,"name":"دوگنبدان","province_id":23},{"id":837,"name":"دیشموک","province_id":23},{"id":838,"name":"سوق","province_id":23},{"id":839,"name":"سی سخت","province_id":23},{"id":840,"name":"قلعه رئیسی","province_id":23},{"id":841,"name":"گراب سفلی","province_id":23},{"id":842,"name":"لنده","province_id":23},{"id":843,"name":"لیکک","province_id":23},{"id":844,"name":"مادوان","province_id":23},{"id":845,"name":"مارگون","province_id":23},{"id":846,"name":"یاسوج","province_id":23},{"id":847,"name":"انبارآلوم","province_id":24},{"id":848,"name":"اینچه برون","province_id":24},{"id":849,"name":"آزادشهر","province_id":24},{"id":850,"name":"آق قلا","province_id":24},{"id":851,"name":"بندرترکمن","province_id":24},{"id":852,"name":"بندرگز","province_id":24},{"id":853,"name":"جلین","province_id":24},{"id":854,"name":"خان ببین","province_id":24},{"id":855,"name":"دلند","province_id":24},{"id":856,"name":"رامیان","province_id":24},{"id":857,"name":"سرخنکلاته","province_id":24},{"id":858,"name":"سیمین شهر","province_id":24},{"id":859,"name":"علی آباد کتول","province_id":24},{"id":860,"name":"فاضل آباد","province_id":24},{"id":861,"name":"کردکوی","province_id":24},{"id":862,"name":"کلاله","province_id":24},{"id":863,"name":"گالیکش","province_id":24},{"id":864,"name":"گرگان","province_id":24},{"id":865,"name":"گمیش تپه","province_id":24},{"id":866,"name":"گنبدکاووس","province_id":24},{"id":867,"name":"مراوه","province_id":24},{"id":868,"name":"مینودشت","province_id":24},{"id":869,"name":"نگین شهر","province_id":24},{"id":870,"name":"نوده خاندوز","province_id":24},{"id":871,"name":"نوکنده","province_id":24},{"id":872,"name":"ازنا","province_id":25},{"id":873,"name":"اشترینان","province_id":25},{"id":874,"name":"الشتر","province_id":25},{"id":875,"name":"الیگودرز","province_id":25},{"id":876,"name":"بروجرد","province_id":25},{"id":877,"name":"پلدختر","province_id":25},{"id":878,"name":"چالانچولان","province_id":25},{"id":879,"name":"چغلوندی","province_id":25},{"id":880,"name":"چقابل","province_id":25},{"id":881,"name":"خرم آباد","province_id":25},{"id":882,"name":"درب گنبد","province_id":25},{"id":883,"name":"دورود","province_id":25},{"id":884,"name":"زاغه","province_id":25},{"id":885,"name":"سپیددشت","province_id":25},{"id":886,"name":"سراب دوره","province_id":25},{"id":887,"name":"فیروزآباد","province_id":25},{"id":888,"name":"کونانی","province_id":25},{"id":889,"name":"کوهدشت","province_id":25},{"id":890,"name":"گراب","province_id":25},{"id":891,"name":"معمولان","province_id":25},{"id":892,"name":"مومن آباد","province_id":25},{"id":893,"name":"نورآباد","province_id":25},{"id":894,"name":"ویسیان","province_id":25},{"id":895,"name":"احمدسرگوراب","province_id":26},{"id":896,"name":"اسالم","province_id":26},{"id":897,"name":"اطاقور","province_id":26},{"id":898,"name":"املش","province_id":26},{"id":899,"name":"آستارا","province_id":26},{"id":900,"name":"آستانه اشرفیه","province_id":26},{"id":901,"name":"بازار جمعه","province_id":26},{"id":902,"name":"بره سر","province_id":26},{"id":903,"name":"بندرانزلی","province_id":26},{"id":906,"name":"پره سر","province_id":26},{"id":907,"name":"تالش","province_id":26},{"id":908,"name":"توتکابن","province_id":26},{"id":909,"name":"جیرنده","province_id":26},{"id":910,"name":"چابکسر","province_id":26},{"id":911,"name":"چاف و چمخاله","province_id":26},{"id":912,"name":"چوبر","province_id":26},{"id":913,"name":"حویق","province_id":26},{"id":914,"name":"خشکبیجار","province_id":26},{"id":915,"name":"خمام","province_id":26},{"id":916,"name":"دیلمان","province_id":26},{"id":917,"name":"رانکوه","province_id":26},{"id":918,"name":"رحیم آباد","province_id":26},{"id":919,"name":"رستم آباد","province_id":26},{"id":920,"name":"رشت","province_id":26},{"id":921,"name":"رضوانشهر","province_id":26},{"id":922,"name":"رودبار","province_id":26},{"id":923,"name":"رودبنه","province_id":26},{"id":924,"name":"رودسر","province_id":26},{"id":925,"name":"سنگر","province_id":26},{"id":926,"name":"سیاهکل","province_id":26},{"id":927,"name":"شفت","province_id":26},{"id":928,"name":"شلمان","province_id":26},{"id":929,"name":"صومعه سرا","province_id":26},{"id":930,"name":"فومن","province_id":26},{"id":931,"name":"کلاچای","province_id":26},{"id":932,"name":"کوچصفهان","province_id":26},{"id":933,"name":"کومله","province_id":26},{"id":934,"name":"کیاشهر","province_id":26},{"id":935,"name":"گوراب زرمیخ","province_id":26},{"id":936,"name":"لاهیجان","province_id":26},{"id":937,"name":"لشت نشا","province_id":26},{"id":938,"name":"لنگرود","province_id":26},{"id":939,"name":"لوشان","province_id":26},{"id":940,"name":"لولمان","province_id":26},{"id":941,"name":"لوندویل","province_id":26},{"id":942,"name":"لیسار","province_id":26},{"id":943,"name":"ماسال","province_id":26},{"id":944,"name":"ماسوله","province_id":26},{"id":945,"name":"مرجقل","province_id":26},{"id":946,"name":"منجیل","province_id":26},{"id":947,"name":"واجارگاه","province_id":26},{"id":948,"name":"امیرکلا","province_id":27},{"id":949,"name":"ایزدشهر","province_id":27},{"id":950,"name":"آلاشت","province_id":27},{"id":951,"name":"آمل","province_id":27},{"id":952,"name":"بابل","province_id":27},{"id":953,"name":"بابلسر","province_id":27},{"id":954,"name":"بلده","province_id":27},{"id":955,"name":"بهشهر","province_id":27},{"id":956,"name":"بهنمیر","province_id":27},{"id":957,"name":"پل سفید","province_id":27},{"id":958,"name":"تنکابن","province_id":27},{"id":959,"name":"جویبار","province_id":27},{"id":960,"name":"چالوس","province_id":27},{"id":961,"name":"چمستان","province_id":27},{"id":962,"name":"خرم آباد","province_id":27},{"id":963,"name":"خلیل شهر","province_id":27},{"id":964,"name":"خوش رودپی","province_id":27},{"id":965,"name":"دابودشت","province_id":27},{"id":966,"name":"رامسر","province_id":27},{"id":967,"name":"رستمکلا","province_id":27},{"id":968,"name":"رویان","province_id":27},{"id":969,"name":"رینه","province_id":27},{"id":970,"name":"زرگرمحله","province_id":27},{"id":971,"name":"زیرآب","province_id":27},{"id":972,"name":"ساری","province_id":27},{"id":973,"name":"سرخرود","province_id":27},{"id":974,"name":"سلمان شهر","province_id":27},{"id":975,"name":"سورک","province_id":27},{"id":976,"name":"شیرگاه","province_id":27},{"id":977,"name":"شیرود","province_id":27},{"id":978,"name":"عباس آباد","province_id":27},{"id":979,"name":"فریدونکنار","province_id":27},{"id":980,"name":"فریم","province_id":27},{"id":981,"name":"قائم شهر","province_id":27},{"id":982,"name":"کتالم","province_id":27},{"id":983,"name":"کلارآباد","province_id":27},{"id":984,"name":"کلاردشت","province_id":27},{"id":985,"name":"کله بست","province_id":27},{"id":986,"name":"کوهی خیل","province_id":27},{"id":987,"name":"کیاسر","province_id":27},{"id":988,"name":"کیاکلا","province_id":27},{"id":989,"name":"گتاب","province_id":27},{"id":990,"name":"گزنک","province_id":27},{"id":991,"name":"گلوگاه","province_id":27},{"id":992,"name":"محمودآباد","province_id":27},{"id":993,"name":"مرزن آباد","province_id":27},{"id":994,"name":"مرزیکلا","province_id":27},{"id":995,"name":"نشتارود","province_id":27},{"id":996,"name":"نکا","province_id":27},{"id":997,"name":"نور","province_id":27},{"id":998,"name":"نوشهر","province_id":27},{"id":999,"name":"اراک","province_id":28},{"id":1000,"name":"آستانه","province_id":28},{"id":1001,"name":"آشتیان","province_id":28},{"id":1002,"name":"پرندک","province_id":28},{"id":1003,"name":"تفرش","province_id":28},{"id":1004,"name":"توره","province_id":28},{"id":1005,"name":"جاورسیان","province_id":28},{"id":1006,"name":"خشکرود","province_id":28},{"id":1007,"name":"خمین","province_id":28},{"id":1008,"name":"خنداب","province_id":28},{"id":1009,"name":"داودآباد","province_id":28},{"id":1010,"name":"دلیجان","province_id":28},{"id":1011,"name":"رازقان","province_id":28},{"id":1012,"name":"زاویه","province_id":28},{"id":1013,"name":"ساروق","province_id":28},{"id":1014,"name":"ساوه","province_id":28},{"id":1015,"name":"سنجان","province_id":28},{"id":1016,"name":"شازند","province_id":28},{"id":1017,"name":"غرق آباد","province_id":28},{"id":1018,"name":"فرمهین","province_id":28},{"id":1019,"name":"قورچی باشی","province_id":28},{"id":1020,"name":"کرهرود","province_id":28},{"id":1021,"name":"کمیجان","province_id":28},{"id":1022,"name":"مامونیه","province_id":28},{"id":1023,"name":"محلات","province_id":28},{"id":1024,"name":"مهاجران","province_id":28},{"id":1025,"name":"میلاجرد","province_id":28},{"id":1026,"name":"نراق","province_id":28},{"id":1027,"name":"نوبران","province_id":28},{"id":1028,"name":"نیمور","province_id":28},{"id":1029,"name":"هندودر","province_id":28},{"id":1030,"name":"ابوموسی","province_id":29},{"id":1031,"name":"بستک","province_id":29},{"id":1032,"name":"بندرجاسک","province_id":29},{"id":1033,"name":"بندرچارک","province_id":29},{"id":1034,"name":"بندرخمیر","province_id":29},{"id":1035,"name":"بندرعباس","province_id":29},{"id":1036,"name":"بندرلنگه","province_id":29},{"id":1037,"name":"بیکا","province_id":29},{"id":1038,"name":"پارسیان","province_id":29},{"id":1039,"name":"تخت","province_id":29},{"id":1040,"name":"جناح","province_id":29},{"id":1041,"name":"حاجی آباد","province_id":29},{"id":1042,"name":"درگهان","province_id":29},{"id":1043,"name":"دهبارز","province_id":29},{"id":1044,"name":"رویدر","province_id":29},{"id":1045,"name":"زیارتعلی","province_id":29},{"id":1046,"name":"سردشت","province_id":29},{"id":1047,"name":"سندرک","province_id":29},{"id":1048,"name":"سوزا","province_id":29},{"id":1049,"name":"سیریک","province_id":29},{"id":1050,"name":"فارغان","province_id":29},{"id":1051,"name":"فین","province_id":29},{"id":1052,"name":"قشم","province_id":29},{"id":1053,"name":"قلعه قاضی","province_id":29},{"id":1054,"name":"کنگ","province_id":29},{"id":1055,"name":"کوشکنار","province_id":29},{"id":1056,"name":"کیش","province_id":29},{"id":1057,"name":"گوهران","province_id":29},{"id":1058,"name":"میناب","province_id":29},{"id":1059,"name":"هرمز","province_id":29},{"id":1060,"name":"هشتبندی","province_id":29},{"id":1061,"name":"ازندریان","province_id":30},{"id":1062,"name":"اسدآباد","province_id":30},{"id":1063,"name":"برزول","province_id":30},{"id":1064,"name":"بهار","province_id":30},{"id":1065,"name":"تویسرکان","province_id":30},{"id":1066,"name":"جورقان","province_id":30},{"id":1067,"name":"جوکار","province_id":30},{"id":1068,"name":"دمق","province_id":30},{"id":1069,"name":"رزن","province_id":30},{"id":1070,"name":"زنگنه","province_id":30},{"id":1071,"name":"سامن","province_id":30},{"id":1072,"name":"سرکان","province_id":30},{"id":1073,"name":"شیرین سو","province_id":30},{"id":1074,"name":"صالح آباد","province_id":30},{"id":1075,"name":"فامنین","province_id":30},{"id":1076,"name":"فرسفج","province_id":30},{"id":1077,"name":"فیروزان","province_id":30},{"id":1078,"name":"قروه درجزین","province_id":30},{"id":1079,"name":"قهاوند","province_id":30},{"id":1080,"name":"کبودر آهنگ","province_id":30},{"id":1081,"name":"گل تپه","province_id":30},{"id":1082,"name":"گیان","province_id":30},{"id":1083,"name":"لالجین","province_id":30},{"id":1084,"name":"مریانج","province_id":30},{"id":1085,"name":"ملایر","province_id":30},{"id":1086,"name":"نهاوند","province_id":30},{"id":1087,"name":"همدان","province_id":30},{"id":1088,"name":"ابرکوه","province_id":31},{"id":1089,"name":"احمدآباد","province_id":31},{"id":1090,"name":"اردکان","province_id":31},{"id":1091,"name":"اشکذر","province_id":31},{"id":1092,"name":"بافق","province_id":31},{"id":1093,"name":"بفروئیه","province_id":31},{"id":1094,"name":"بهاباد","province_id":31},{"id":1095,"name":"تفت","province_id":31},{"id":1096,"name":"حمیدیا","province_id":31},{"id":1097,"name":"خضرآباد","province_id":31},{"id":1098,"name":"دیهوک","province_id":31},{"id":1099,"name":"زارچ","province_id":31},{"id":1100,"name":"شاهدیه","province_id":31},{"id":1101,"name":"طبس","province_id":31},{"id":1103,"name":"عقدا","province_id":31},{"id":1104,"name":"مروست","province_id":31},{"id":1105,"name":"مهردشت","province_id":31},{"id":1106,"name":"مهریز","province_id":31},{"id":1107,"name":"میبد","province_id":31},{"id":1108,"name":"ندوشن","province_id":31},{"id":1109,"name":"نیر","province_id":31},{"id":1110,"name":"هرات","province_id":31},{"id":1111,"name":"یزد","province_id":31},{"id":1116,"name":"پرند","province_id":8},{"id":1117,"name":"فردیس","province_id":5},{"id":1118,"name":"مارلیک","province_id":5},{"id":1119,"name":"سادات شهر","province_id":27},{"id":1121,"name":"زیباکنار","province_id":26},{"id":1135,"name":"کردان","province_id":5},{"id":1137,"name":"ساوجبلاغ","province_id":5},{"id":1138,"name":"تهران دشت","province_id":5},{"id":1150,"name":"گلبهار","province_id":11},{"id":1153,"name":"قیامدشت","province_id":8},{"id":1155,"name":"بینالود","province_id":11},{"id":1159,"name":"پیربازار","province_id":26},{"id":1160,"name":"رضوانشهر","province_id":31}]');
    foreach ($cities_temp as $city)
        $all_cities[$city->province_id][] = [
            'id'    => $city->id,
            'title' => $city->name,
        ];

    $data[] = [
        'type'  => 'settings',
        'title' => 'تنظیمات حساب کاربری',
        'data'  => [
            'cities'    => $all_cities[$user_settings['province_id']],
            'items'     => $user_settings,
        ],
    ];

    wp_send_json_success($data);
}
//**********************************************************************************************************/
function user_get_cities_api($request)
{
    global $wldb;

    $user_id = get_user_id_by_token(ez_authorization(true));

    $params = $request->get_params();
    $province_id = (int)$params['id'];

    $cities_temp = json_decode('[{"id":1,"name":"اسکو","province_id":1},{"id":2,"name":"اهر","province_id":1},{"id":3,"name":"ایلخچی","province_id":1},{"id":4,"name":"آبش احمد","province_id":1},{"id":5,"name":"آذرشهر","province_id":1},{"id":6,"name":"آقکند","province_id":1},{"id":7,"name":"باسمنج","province_id":1},{"id":8,"name":"بخشایش","province_id":1},{"id":9,"name":"بستان آباد","province_id":1},{"id":10,"name":"بناب","province_id":1},{"id":11,"name":"بناب جدید","province_id":1},{"id":12,"name":"تبریز","province_id":1},{"id":13,"name":"ترک","province_id":1},{"id":14,"name":"ترکمانچای","province_id":1},{"id":15,"name":"تسوج","province_id":1},{"id":16,"name":"تیکمه داش","province_id":1},{"id":17,"name":"جلفا","province_id":1},{"id":18,"name":"خاروانا","province_id":1},{"id":19,"name":"خامنه","province_id":1},{"id":20,"name":"خراجو","province_id":1},{"id":21,"name":"خسروشهر","province_id":1},{"id":22,"name":"خضرلو","province_id":1},{"id":23,"name":"خمارلو","province_id":1},{"id":24,"name":"خواجه","province_id":1},{"id":25,"name":"دوزدوزان","province_id":1},{"id":26,"name":"زرنق","province_id":1},{"id":27,"name":"زنوز","province_id":1},{"id":28,"name":"سراب","province_id":1},{"id":29,"name":"سردرود","province_id":1},{"id":30,"name":"سهند","province_id":1},{"id":31,"name":"سیس","province_id":1},{"id":32,"name":"سیه رود","province_id":1},{"id":33,"name":"شبستر","province_id":1},{"id":34,"name":"شربیان","province_id":1},{"id":35,"name":"شرفخانه","province_id":1},{"id":36,"name":"شندآباد","province_id":1},{"id":37,"name":"صوفیان","province_id":1},{"id":38,"name":"عجب شیر","province_id":1},{"id":39,"name":"قره آغاج","province_id":1},{"id":40,"name":"کشکسرای","province_id":1},{"id":41,"name":"کلوانق","province_id":1},{"id":42,"name":"کلیبر","province_id":1},{"id":43,"name":"کوزه کنان","province_id":1},{"id":44,"name":"گوگان","province_id":1},{"id":45,"name":"لیلان","province_id":1},{"id":46,"name":"مراغه","province_id":1},{"id":47,"name":"مرند","province_id":1},{"id":48,"name":"ملکان","province_id":1},{"id":49,"name":"ملک کیان","province_id":1},{"id":50,"name":"ممقان","province_id":1},{"id":51,"name":"مهربان","province_id":1},{"id":52,"name":"میانه","province_id":1},{"id":53,"name":"نظرکهریزی","province_id":1},{"id":54,"name":"هادی شهر","province_id":1},{"id":55,"name":"هرگلان","province_id":1},{"id":56,"name":"هریس","province_id":1},{"id":57,"name":"هشترود","province_id":1},{"id":58,"name":"هوراند","province_id":1},{"id":59,"name":"وایقان","province_id":1},{"id":60,"name":"ورزقان","province_id":1},{"id":61,"name":"یامچی","province_id":1},{"id":62,"name":"ارومیه","province_id":2},{"id":63,"name":"اشنویه","province_id":2},{"id":64,"name":"ایواوغلی","province_id":2},{"id":65,"name":"آواجیق","province_id":2},{"id":66,"name":"باروق","province_id":2},{"id":67,"name":"بازرگان","province_id":2},{"id":68,"name":"بوکان","province_id":2},{"id":69,"name":"پلدشت","province_id":2},{"id":70,"name":"پیرانشهر","province_id":2},{"id":71,"name":"تازه شهر","province_id":2},{"id":72,"name":"تکاب","province_id":2},{"id":73,"name":"چهاربرج","province_id":2},{"id":74,"name":"خوی","province_id":2},{"id":75,"name":"دیزج دیز","province_id":2},{"id":76,"name":"ربط","province_id":2},{"id":77,"name":"سردشت","province_id":2},{"id":78,"name":"سرو","province_id":2},{"id":79,"name":"سلماس","province_id":2},{"id":80,"name":"سیلوانه","province_id":2},{"id":81,"name":"سیمینه","province_id":2},{"id":82,"name":"سیه چشمه","province_id":2},{"id":83,"name":"شاهین دژ","province_id":2},{"id":84,"name":"شوط","province_id":2},{"id":85,"name":"فیرورق","province_id":2},{"id":86,"name":"قره ضیاءالدین","province_id":2},{"id":87,"name":"قطور","province_id":2},{"id":88,"name":"قوشچی","province_id":2},{"id":89,"name":"کشاورز","province_id":2},{"id":90,"name":"گردکشانه","province_id":2},{"id":91,"name":"ماکو","province_id":2},{"id":92,"name":"محمدیار","province_id":2},{"id":93,"name":"محمودآباد","province_id":2},{"id":94,"name":"مهاباد","province_id":2},{"id":95,"name":"میاندوآب","province_id":2},{"id":96,"name":"میرآباد","province_id":2},{"id":97,"name":"نالوس","province_id":2},{"id":98,"name":"نقده","province_id":2},{"id":99,"name":"نوشین","province_id":2},{"id":100,"name":"اردبیل","province_id":3},{"id":101,"name":"اصلاندوز","province_id":3},{"id":102,"name":"آبی بیگلو","province_id":3},{"id":103,"name":"بیله سوار","province_id":3},{"id":104,"name":"پارس آباد","province_id":3},{"id":105,"name":"تازه کند","province_id":3},{"id":106,"name":"تازه کندانگوت","province_id":3},{"id":107,"name":"جعفرآباد","province_id":3},{"id":108,"name":"خلخال","province_id":3},{"id":109,"name":"رضی","province_id":3},{"id":110,"name":"سرعین","province_id":3},{"id":111,"name":"عنبران","province_id":3},{"id":112,"name":"فخرآباد","province_id":3},{"id":113,"name":"کلور","province_id":3},{"id":114,"name":"کوراییم","province_id":3},{"id":115,"name":"گرمی","province_id":3},{"id":116,"name":"گیوی","province_id":3},{"id":117,"name":"لاهرود","province_id":3},{"id":118,"name":"مشگین شهر","province_id":3},{"id":119,"name":"نمین","province_id":3},{"id":120,"name":"نیر","province_id":3},{"id":121,"name":"هشتجین","province_id":3},{"id":122,"name":"هیر","province_id":3},{"id":123,"name":"ابریشم","province_id":4},{"id":124,"name":"ابوزیدآباد","province_id":4},{"id":125,"name":"اردستان","province_id":4},{"id":126,"name":"اژیه","province_id":4},{"id":127,"name":"اصفهان","province_id":4},{"id":128,"name":"افوس","province_id":4},{"id":129,"name":"انارک","province_id":4},{"id":130,"name":"ایمانشهر","province_id":4},{"id":131,"name":"آران وبیدگل","province_id":4},{"id":132,"name":"بادرود","province_id":4},{"id":133,"name":"باغ بهادران","province_id":4},{"id":134,"name":"بافران","province_id":4},{"id":135,"name":"برزک","province_id":4},{"id":136,"name":"برف انبار","province_id":4},{"id":137,"name":"بهاران شهر","province_id":4},{"id":138,"name":"بهارستان","province_id":4},{"id":139,"name":"بوئین و میاندشت","province_id":4},{"id":140,"name":"پیربکران","province_id":4},{"id":141,"name":"تودشک","province_id":4},{"id":142,"name":"تیران","province_id":4},{"id":143,"name":"جندق","province_id":4},{"id":144,"name":"جوزدان","province_id":4},{"id":145,"name":"جوشقان و کامو","province_id":4},{"id":146,"name":"چادگان","province_id":4},{"id":147,"name":"چرمهین","province_id":4},{"id":148,"name":"چمگردان","province_id":4},{"id":149,"name":"حبیب آباد","province_id":4},{"id":150,"name":"حسن آباد","province_id":4},{"id":151,"name":"حنا","province_id":4},{"id":152,"name":"خالدآباد","province_id":4},{"id":153,"name":"خمینی شهر","province_id":4},{"id":154,"name":"خوانسار","province_id":4},{"id":155,"name":"خور","province_id":4},{"id":157,"name":"خورزوق","province_id":4},{"id":158,"name":"داران","province_id":4},{"id":159,"name":"دامنه","province_id":4},{"id":160,"name":"درچه","province_id":4},{"id":161,"name":"دستگرد","province_id":4},{"id":162,"name":"دهاقان","province_id":4},{"id":163,"name":"دهق","province_id":4},{"id":164,"name":"دولت آباد","province_id":4},{"id":165,"name":"دیزیچه","province_id":4},{"id":166,"name":"رزوه","province_id":4},{"id":167,"name":"رضوانشهر","province_id":4},{"id":168,"name":"زاینده رود","province_id":4},{"id":169,"name":"زرین شهر","province_id":4},{"id":170,"name":"زواره","province_id":4},{"id":171,"name":"زیباشهر","province_id":4},{"id":172,"name":"سده لنجان","province_id":4},{"id":173,"name":"سفیدشهر","province_id":4},{"id":174,"name":"سگزی","province_id":4},{"id":175,"name":"سمیرم","province_id":4},{"id":176,"name":"شاهین شهر","province_id":4},{"id":177,"name":"شهرضا","province_id":4},{"id":178,"name":"طالخونچه","province_id":4},{"id":179,"name":"عسگران","province_id":4},{"id":180,"name":"علویجه","province_id":4},{"id":181,"name":"فرخی","province_id":4},{"id":182,"name":"فریدونشهر","province_id":4},{"id":183,"name":"فلاورجان","province_id":4},{"id":184,"name":"فولادشهر","province_id":4},{"id":185,"name":"قمصر","province_id":4},{"id":186,"name":"قهجاورستان","province_id":4},{"id":187,"name":"قهدریجان","province_id":4},{"id":188,"name":"کاشان","province_id":4},{"id":189,"name":"کرکوند","province_id":4},{"id":190,"name":"کلیشاد و سودرجان","province_id":4},{"id":191,"name":"کمشچه","province_id":4},{"id":192,"name":"کمه","province_id":4},{"id":193,"name":"کهریزسنگ","province_id":4},{"id":194,"name":"کوشک","province_id":4},{"id":195,"name":"کوهپایه","province_id":4},{"id":196,"name":"گرگاب","province_id":4},{"id":197,"name":"گزبرخوار","province_id":4},{"id":198,"name":"گلپایگان","province_id":4},{"id":199,"name":"گلدشت","province_id":4},{"id":200,"name":"گلشهر","province_id":4},{"id":201,"name":"گوگد","province_id":4},{"id":202,"name":"لای بید","province_id":4},{"id":203,"name":"مبارکه","province_id":4},{"id":204,"name":"مجلسی","province_id":4},{"id":205,"name":"محمدآباد","province_id":4},{"id":206,"name":"مشکات","province_id":4},{"id":207,"name":"منظریه","province_id":4},{"id":208,"name":"مهاباد","province_id":4},{"id":209,"name":"میمه","province_id":4},{"id":210,"name":"نائین","province_id":4},{"id":211,"name":"نجف آباد","province_id":4},{"id":212,"name":"نصرآباد","province_id":4},{"id":213,"name":"نطنز","province_id":4},{"id":214,"name":"نوش آباد","province_id":4},{"id":215,"name":"نیاسر","province_id":4},{"id":216,"name":"نیک آباد","province_id":4},{"id":217,"name":"هرند","province_id":4},{"id":218,"name":"ورزنه","province_id":4},{"id":219,"name":"ورنامخواست","province_id":4},{"id":220,"name":"وزوان","province_id":4},{"id":221,"name":"ونک","province_id":4},{"id":222,"name":"اسارا","province_id":5},{"id":223,"name":"اشتهارد","province_id":5},{"id":224,"name":"تنکمان","province_id":5},{"id":225,"name":"چهارباغ","province_id":5},{"id":226,"name":"سعید آباد","province_id":5},{"id":227,"name":"شهر جدید هشتگرد","province_id":5},{"id":228,"name":"طالقان","province_id":5},{"id":229,"name":"کرج","province_id":5},{"id":230,"name":"کمال شهر","province_id":5},{"id":231,"name":"کوهسار","province_id":5},{"id":232,"name":"گرمدره","province_id":5},{"id":233,"name":"ماهدشت","province_id":5},{"id":234,"name":"محمدشهر","province_id":5},{"id":235,"name":"مشکین دشت","province_id":5},{"id":236,"name":"نظرآباد","province_id":5},{"id":237,"name":"هشتگرد","province_id":5},{"id":238,"name":"ارکواز","province_id":6},{"id":239,"name":"ایلام","province_id":6},{"id":240,"name":"ایوان","province_id":6},{"id":241,"name":"آبدانان","province_id":6},{"id":242,"name":"آسمان آباد","province_id":6},{"id":243,"name":"بدره","province_id":6},{"id":244,"name":"پهله","province_id":6},{"id":245,"name":"توحید","province_id":6},{"id":246,"name":"چوار","province_id":6},{"id":247,"name":"دره شهر","province_id":6},{"id":248,"name":"دلگشا","province_id":6},{"id":249,"name":"دهلران","province_id":6},{"id":250,"name":"زرنه","province_id":6},{"id":251,"name":"سراب باغ","province_id":6},{"id":252,"name":"سرابله","province_id":6},{"id":253,"name":"صالح آباد","province_id":6},{"id":254,"name":"لومار","province_id":6},{"id":255,"name":"مهران","province_id":6},{"id":256,"name":"مورموری","province_id":6},{"id":257,"name":"موسیان","province_id":6},{"id":258,"name":"میمه","province_id":6},{"id":259,"name":"امام حسن","province_id":7},{"id":260,"name":"انارستان","province_id":7},{"id":261,"name":"اهرم","province_id":7},{"id":262,"name":"آب پخش","province_id":7},{"id":263,"name":"آبدان","province_id":7},{"id":264,"name":"برازجان","province_id":7},{"id":265,"name":"بردخون","province_id":7},{"id":266,"name":"بندردیر","province_id":7},{"id":267,"name":"بندردیلم","province_id":7},{"id":268,"name":"بندرریگ","province_id":7},{"id":269,"name":"بندرکنگان","province_id":7},{"id":270,"name":"بندرگناوه","province_id":7},{"id":271,"name":"بنک","province_id":7},{"id":272,"name":"بوشهر","province_id":7},{"id":273,"name":"تنگ ارم","province_id":7},{"id":274,"name":"جم","province_id":7},{"id":275,"name":"چغادک","province_id":7},{"id":276,"name":"خارک","province_id":7},{"id":277,"name":"خورموج","province_id":7},{"id":278,"name":"دالکی","province_id":7},{"id":279,"name":"دلوار","province_id":7},{"id":280,"name":"ریز","province_id":7},{"id":281,"name":"سعدآباد","province_id":7},{"id":282,"name":"سیراف","province_id":7},{"id":283,"name":"شبانکاره","province_id":7},{"id":284,"name":"شنبه","province_id":7},{"id":285,"name":"عسلویه","province_id":7},{"id":286,"name":"کاکی","province_id":7},{"id":287,"name":"کلمه","province_id":7},{"id":288,"name":"نخل تقی","province_id":7},{"id":289,"name":"وحدتیه","province_id":7},{"id":290,"name":"ارجمند","province_id":8},{"id":291,"name":"اسلامشهر","province_id":8},{"id":292,"name":"اندیشه","province_id":8},{"id":293,"name":"آبسرد","province_id":8},{"id":294,"name":"آبعلی","province_id":8},{"id":295,"name":"باغستان","province_id":8},{"id":296,"name":"باقرشهر","province_id":8},{"id":297,"name":"بومهن","province_id":8},{"id":298,"name":"پاکدشت","province_id":8},{"id":299,"name":"پردیس","province_id":8},{"id":300,"name":"پیشوا","province_id":8},{"id":301,"name":"تهران","province_id":8},{"id":302,"name":"جوادآباد","province_id":8},{"id":303,"name":"چهاردانگه","province_id":8},{"id":304,"name":"حسن آباد","province_id":8},{"id":305,"name":"دماوند","province_id":8},{"id":306,"name":"دیزین","province_id":8},{"id":307,"name":"شهر ری","province_id":8},{"id":308,"name":"رباط کریم","province_id":8},{"id":309,"name":"رودهن","province_id":8},{"id":310,"name":"شاهدشهر","province_id":8},{"id":311,"name":"شریف آباد","province_id":8},{"id":312,"name":"شمشک","province_id":8},{"id":313,"name":"شهریار","province_id":8},{"id":314,"name":"صالح آباد","province_id":8},{"id":315,"name":"صباشهر","province_id":8},{"id":316,"name":"صفادشت","province_id":8},{"id":317,"name":"فردوسیه","province_id":8},{"id":318,"name":"فشم","province_id":8},{"id":319,"name":"فیروزکوه","province_id":8},{"id":320,"name":"قدس","province_id":8},{"id":321,"name":"قرچک","province_id":8},{"id":322,"name":"کهریزک","province_id":8},{"id":323,"name":"کیلان","province_id":8},{"id":324,"name":"گلستان","province_id":8},{"id":325,"name":"لواسان","province_id":8},{"id":326,"name":"ملارد","province_id":8},{"id":327,"name":"میگون","province_id":8},{"id":328,"name":"نسیم شهر","province_id":8},{"id":329,"name":"نصیرآباد","province_id":8},{"id":330,"name":"وحیدیه","province_id":8},{"id":331,"name":"ورامین","province_id":8},{"id":332,"name":"اردل","province_id":9},{"id":333,"name":"آلونی","province_id":9},{"id":334,"name":"باباحیدر","province_id":9},{"id":335,"name":"بروجن","province_id":9},{"id":336,"name":"بلداجی","province_id":9},{"id":337,"name":"بن","province_id":9},{"id":338,"name":"جونقان","province_id":9},{"id":339,"name":"چلگرد","province_id":9},{"id":340,"name":"سامان","province_id":9},{"id":341,"name":"سفیددشت","province_id":9},{"id":342,"name":"سودجان","province_id":9},{"id":343,"name":"سورشجان","province_id":9},{"id":344,"name":"شلمزار","province_id":9},{"id":345,"name":"شهرکرد","province_id":9},{"id":346,"name":"طاقانک","province_id":9},{"id":347,"name":"فارسان","province_id":9},{"id":348,"name":"فرادنبه","province_id":9},{"id":349,"name":"فرخ شهر","province_id":9},{"id":350,"name":"کیان","province_id":9},{"id":351,"name":"گندمان","province_id":9},{"id":352,"name":"گهرو","province_id":9},{"id":353,"name":"لردگان","province_id":9},{"id":354,"name":"مال خلیفه","province_id":9},{"id":355,"name":"ناغان","province_id":9},{"id":356,"name":"نافچ","province_id":9},{"id":357,"name":"نقنه","province_id":9},{"id":358,"name":"هفشجان","province_id":9},{"id":359,"name":"ارسک","province_id":10},{"id":360,"name":"اسدیه","province_id":10},{"id":361,"name":"اسفدن","province_id":10},{"id":362,"name":"اسلامیه","province_id":10},{"id":363,"name":"آرین شهر","province_id":10},{"id":364,"name":"آیسک","province_id":10},{"id":365,"name":"بشرویه","province_id":10},{"id":366,"name":"بیرجند","province_id":10},{"id":367,"name":"حاجی آباد","province_id":10},{"id":368,"name":"خضری دشت بیاض","province_id":10},{"id":369,"name":"خوسف","province_id":10},{"id":370,"name":"زهان","province_id":10},{"id":371,"name":"سرایان","province_id":10},{"id":372,"name":"سربیشه","province_id":10},{"id":373,"name":"سه قلعه","province_id":10},{"id":374,"name":"شوسف","province_id":10},{"id":375,"name":"طبس ","province_id":10},{"id":376,"name":"فردوس","province_id":10},{"id":377,"name":"قاین","province_id":10},{"id":378,"name":"قهستان","province_id":10},{"id":379,"name":"محمدشهر","province_id":10},{"id":380,"name":"مود","province_id":10},{"id":381,"name":"نهبندان","province_id":10},{"id":382,"name":"نیمبلوک","province_id":10},{"id":383,"name":"احمدآباد صولت","province_id":11},{"id":384,"name":"انابد","province_id":11},{"id":385,"name":"باجگیران","province_id":11},{"id":386,"name":"باخرز","province_id":11},{"id":387,"name":"بار","province_id":11},{"id":388,"name":"بایگ","province_id":11},{"id":389,"name":"بجستان","province_id":11},{"id":390,"name":"بردسکن","province_id":11},{"id":391,"name":"بیدخت","province_id":11},{"id":392,"name":"تایباد","province_id":11},{"id":393,"name":"تربت جام","province_id":11},{"id":394,"name":"تربت حیدریه","province_id":11},{"id":395,"name":"جغتای","province_id":11},{"id":396,"name":"جنگل","province_id":11},{"id":397,"name":"چاپشلو","province_id":11},{"id":398,"name":"چکنه","province_id":11},{"id":399,"name":"چناران","province_id":11},{"id":400,"name":"خرو","province_id":11},{"id":401,"name":"خلیل آباد","province_id":11},{"id":402,"name":"خواف","province_id":11},{"id":403,"name":"داورزن","province_id":11},{"id":404,"name":"درگز","province_id":11},{"id":405,"name":"در رود","province_id":11},{"id":406,"name":"دولت آباد","province_id":11},{"id":407,"name":"رباط سنگ","province_id":11},{"id":408,"name":"رشتخوار","province_id":11},{"id":409,"name":"رضویه","province_id":11},{"id":410,"name":"روداب","province_id":11},{"id":411,"name":"ریوش","province_id":11},{"id":412,"name":"سبزوار","province_id":11},{"id":413,"name":"سرخس","province_id":11},{"id":414,"name":"سفیدسنگ","province_id":11},{"id":415,"name":"سلامی","province_id":11},{"id":416,"name":"سلطان آباد","province_id":11},{"id":417,"name":"سنگان","province_id":11},{"id":418,"name":"شادمهر","province_id":11},{"id":419,"name":"شاندیز","province_id":11},{"id":420,"name":"ششتمد","province_id":11},{"id":421,"name":"شهرآباد","province_id":11},{"id":422,"name":"شهرزو","province_id":11},{"id":423,"name":"صالح آباد","province_id":11},{"id":424,"name":"طرقبه","province_id":11},{"id":425,"name":"عشق آباد","province_id":11},{"id":426,"name":"فرهادگرد","province_id":11},{"id":427,"name":"فریمان","province_id":11},{"id":428,"name":"فیروزه","province_id":11},{"id":429,"name":"فیض آباد","province_id":11},{"id":430,"name":"قاسم آباد","province_id":11},{"id":431,"name":"قدمگاه","province_id":11},{"id":432,"name":"قلندرآباد","province_id":11},{"id":433,"name":"قوچان","province_id":11},{"id":434,"name":"کاخک","province_id":11},{"id":435,"name":"کاریز","province_id":11},{"id":436,"name":"کاشمر","province_id":11},{"id":437,"name":"کدکن","province_id":11},{"id":438,"name":"کلات","province_id":11},{"id":439,"name":"کندر","province_id":11},{"id":440,"name":"گلمکان","province_id":11},{"id":441,"name":"گناباد","province_id":11},{"id":442,"name":"لطف آباد","province_id":11},{"id":443,"name":"مزدآوند","province_id":11},{"id":444,"name":"مشهد","province_id":11},{"id":445,"name":"ملک آباد","province_id":11},{"id":446,"name":"نشتیفان","province_id":11},{"id":447,"name":"نصرآباد","province_id":11},{"id":448,"name":"نقاب","province_id":11},{"id":449,"name":"نوخندان","province_id":11},{"id":450,"name":"نیشابور","province_id":11},{"id":451,"name":"نیل شهر","province_id":11},{"id":452,"name":"همت آباد","province_id":11},{"id":453,"name":"یونسی","province_id":11},{"id":454,"name":"اسفراین","province_id":12},{"id":455,"name":"ایور","province_id":12},{"id":456,"name":"آشخانه","province_id":12},{"id":457,"name":"بجنورد","province_id":12},{"id":458,"name":"پیش قلعه","province_id":12},{"id":459,"name":"تیتکانلو","province_id":12},{"id":460,"name":"جاجرم","province_id":12},{"id":461,"name":"حصارگرمخان","province_id":12},{"id":462,"name":"درق","province_id":12},{"id":463,"name":"راز","province_id":12},{"id":464,"name":"سنخواست","province_id":12},{"id":465,"name":"شوقان","province_id":12},{"id":466,"name":"شیروان","province_id":12},{"id":467,"name":"صفی آباد","province_id":12},{"id":468,"name":"فاروج","province_id":12},{"id":469,"name":"قاضی","province_id":12},{"id":470,"name":"گرمه","province_id":12},{"id":471,"name":"لوجلی","province_id":12},{"id":472,"name":"اروندکنار","province_id":13},{"id":473,"name":"الوان","province_id":13},{"id":474,"name":"امیدیه","province_id":13},{"id":475,"name":"اندیمشک","province_id":13},{"id":476,"name":"اهواز","province_id":13},{"id":477,"name":"ایذه","province_id":13},{"id":478,"name":"آبادان","province_id":13},{"id":479,"name":"آغاجاری","province_id":13},{"id":480,"name":"باغ ملک","province_id":13},{"id":481,"name":"بستان","province_id":13},{"id":482,"name":"بندرامام خمینی","province_id":13},{"id":483,"name":"بندرماهشهر","province_id":13},{"id":484,"name":"بهبهان","province_id":13},{"id":485,"name":"ترکالکی","province_id":13},{"id":486,"name":"جایزان","province_id":13},{"id":487,"name":"چمران","province_id":13},{"id":488,"name":"چویبده","province_id":13},{"id":489,"name":"حر","province_id":13},{"id":490,"name":"حسینیه","province_id":13},{"id":491,"name":"حمزه","province_id":13},{"id":492,"name":"حمیدیه","province_id":13},{"id":493,"name":"خرمشهر","province_id":13},{"id":494,"name":"دارخوین","province_id":13},{"id":495,"name":"دزآب","province_id":13},{"id":496,"name":"دزفول","province_id":13},{"id":497,"name":"دهدز","province_id":13},{"id":498,"name":"رامشیر","province_id":13},{"id":499,"name":"رامهرمز","province_id":13},{"id":500,"name":"رفیع","province_id":13},{"id":501,"name":"زهره","province_id":13},{"id":502,"name":"سالند","province_id":13},{"id":503,"name":"سردشت","province_id":13},{"id":504,"name":"سوسنگرد","province_id":13},{"id":505,"name":"شادگان","province_id":13},{"id":506,"name":"شاوور","province_id":13},{"id":507,"name":"شرافت","province_id":13},{"id":508,"name":"شوش","province_id":13},{"id":509,"name":"شوشتر","province_id":13},{"id":510,"name":"شیبان","province_id":13},{"id":511,"name":"صالح شهر","province_id":13},{"id":512,"name":"صفی آباد","province_id":13},{"id":513,"name":"صیدون","province_id":13},{"id":514,"name":"قلعه تل","province_id":13},{"id":515,"name":"قلعه خواجه","province_id":13},{"id":516,"name":"گتوند","province_id":13},{"id":517,"name":"لالی","province_id":13},{"id":518,"name":"مسجدسلیمان","province_id":13},{"id":520,"name":"ملاثانی","province_id":13},{"id":521,"name":"میانرود","province_id":13},{"id":522,"name":"مینوشهر","province_id":13},{"id":523,"name":"هفتگل","province_id":13},{"id":524,"name":"هندیجان","province_id":13},{"id":525,"name":"هویزه","province_id":13},{"id":526,"name":"ویس","province_id":13},{"id":527,"name":"ابهر","province_id":14},{"id":528,"name":"ارمغان خانه","province_id":14},{"id":529,"name":"آب بر","province_id":14},{"id":530,"name":"چورزق","province_id":14},{"id":531,"name":"حلب","province_id":14},{"id":532,"name":"خرمدره","province_id":14},{"id":533,"name":"دندی","province_id":14},{"id":534,"name":"زرین آباد","province_id":14},{"id":535,"name":"زرین رود","province_id":14},{"id":536,"name":"زنجان","province_id":14},{"id":537,"name":"سجاس","province_id":14},{"id":538,"name":"سلطانیه","province_id":14},{"id":539,"name":"سهرورد","province_id":14},{"id":540,"name":"صائین قلعه","province_id":14},{"id":541,"name":"قیدار","province_id":14},{"id":542,"name":"گرماب","province_id":14},{"id":543,"name":"ماه نشان","province_id":14},{"id":544,"name":"هیدج","province_id":14},{"id":545,"name":"امیریه","province_id":15},{"id":546,"name":"ایوانکی","province_id":15},{"id":547,"name":"آرادان","province_id":15},{"id":548,"name":"بسطام","province_id":15},{"id":549,"name":"بیارجمند","province_id":15},{"id":550,"name":"دامغان","province_id":15},{"id":551,"name":"درجزین","province_id":15},{"id":552,"name":"دیباج","province_id":15},{"id":553,"name":"سرخه","province_id":15},{"id":554,"name":"سمنان","province_id":15},{"id":555,"name":"شاهرود","province_id":15},{"id":556,"name":"شهمیرزاد","province_id":15},{"id":557,"name":"کلاته خیج","province_id":15},{"id":558,"name":"گرمسار","province_id":15},{"id":559,"name":"مجن","province_id":15},{"id":560,"name":"مهدی شهر","province_id":15},{"id":561,"name":"میامی","province_id":15},{"id":562,"name":"ادیمی","province_id":16},{"id":563,"name":"اسپکه","province_id":16},{"id":564,"name":"ایرانشهر","province_id":16},{"id":565,"name":"بزمان","province_id":16},{"id":566,"name":"بمپور","province_id":16},{"id":567,"name":"بنت","province_id":16},{"id":568,"name":"بنجار","province_id":16},{"id":569,"name":"پیشین","province_id":16},{"id":570,"name":"جالق","province_id":16},{"id":571,"name":"چابهار","province_id":16},{"id":572,"name":"خاش","province_id":16},{"id":573,"name":"دوست محمد","province_id":16},{"id":574,"name":"راسک","province_id":16},{"id":575,"name":"زابل","province_id":16},{"id":576,"name":"زابلی","province_id":16},{"id":577,"name":"زاهدان","province_id":16},{"id":578,"name":"زهک","province_id":16},{"id":579,"name":"سراوان","province_id":16},{"id":580,"name":"سرباز","province_id":16},{"id":581,"name":"سوران","province_id":16},{"id":582,"name":"سیرکان","province_id":16},{"id":583,"name":"علی اکبر","province_id":16},{"id":584,"name":"فنوج","province_id":16},{"id":585,"name":"قصرقند","province_id":16},{"id":586,"name":"کنارک","province_id":16},{"id":587,"name":"گشت","province_id":16},{"id":588,"name":"گلمورتی","province_id":16},{"id":589,"name":"محمدان","province_id":16},{"id":590,"name":"محمدآباد","province_id":16},{"id":591,"name":"محمدی","province_id":16},{"id":592,"name":"میرجاوه","province_id":16},{"id":593,"name":"نصرت آباد","province_id":16},{"id":594,"name":"نگور","province_id":16},{"id":595,"name":"نوک آباد","province_id":16},{"id":596,"name":"نیک شهر","province_id":16},{"id":597,"name":"هیدوچ","province_id":16},{"id":598,"name":"اردکان","province_id":17},{"id":599,"name":"ارسنجان","province_id":17},{"id":600,"name":"استهبان","province_id":17},{"id":601,"name":"اشکنان","province_id":17},{"id":602,"name":"افزر","province_id":17},{"id":603,"name":"اقلید","province_id":17},{"id":604,"name":"امام شهر","province_id":17},{"id":605,"name":"اهل","province_id":17},{"id":606,"name":"اوز","province_id":17},{"id":607,"name":"ایج","province_id":17},{"id":608,"name":"ایزدخواست","province_id":17},{"id":609,"name":"آباده","province_id":17},{"id":610,"name":"آباده طشک","province_id":17},{"id":611,"name":"باب انار","province_id":17},{"id":612,"name":"بالاده","province_id":17},{"id":613,"name":"بنارویه","province_id":17},{"id":614,"name":"بهمن","province_id":17},{"id":615,"name":"بوانات","province_id":17},{"id":616,"name":"بیرم","province_id":17},{"id":617,"name":"بیضا","province_id":17},{"id":618,"name":"جنت شهر","province_id":17},{"id":619,"name":"جهرم","province_id":17},{"id":620,"name":"جویم","province_id":17},{"id":621,"name":"زرین دشت","province_id":17},{"id":622,"name":"حسن آباد","province_id":17},{"id":623,"name":"خان زنیان","province_id":17},{"id":624,"name":"خاوران","province_id":17},{"id":625,"name":"خرامه","province_id":17},{"id":626,"name":"خشت","province_id":17},{"id":627,"name":"خنج","province_id":17},{"id":628,"name":"خور","province_id":17},{"id":629,"name":"داراب","province_id":17},{"id":630,"name":"داریان","province_id":17},{"id":631,"name":"دبیران","province_id":17},{"id":632,"name":"دژکرد","province_id":17},{"id":633,"name":"دهرم","province_id":17},{"id":634,"name":"دوبرجی","province_id":17},{"id":635,"name":"رامجرد","province_id":17},{"id":636,"name":"رونیز","province_id":17},{"id":637,"name":"زاهدشهر","province_id":17},{"id":638,"name":"زرقان","province_id":17},{"id":639,"name":"سده","province_id":17},{"id":640,"name":"سروستان","province_id":17},{"id":641,"name":"سعادت شهر","province_id":17},{"id":642,"name":"سورمق","province_id":17},{"id":643,"name":"سیدان","province_id":17},{"id":644,"name":"ششده","province_id":17},{"id":645,"name":"شهرپیر","province_id":17},{"id":646,"name":"شهرصدرا","province_id":17},{"id":647,"name":"شیراز","province_id":17},{"id":648,"name":"صغاد","province_id":17},{"id":649,"name":"صفاشهر","province_id":17},{"id":650,"name":"علامرودشت","province_id":17},{"id":651,"name":"فدامی","province_id":17},{"id":652,"name":"فراشبند","province_id":17},{"id":653,"name":"فسا","province_id":17},{"id":654,"name":"فیروزآباد","province_id":17},{"id":655,"name":"قائمیه","province_id":17},{"id":656,"name":"قادرآباد","province_id":17},{"id":657,"name":"قطب آباد","province_id":17},{"id":658,"name":"قطرویه","province_id":17},{"id":659,"name":"قیر","province_id":17},{"id":660,"name":"کارزین (فتح آباد)","province_id":17},{"id":661,"name":"کازرون","province_id":17},{"id":662,"name":"کامفیروز","province_id":17},{"id":663,"name":"کره ای","province_id":17},{"id":664,"name":"کنارتخته","province_id":17},{"id":665,"name":"کوار","province_id":17},{"id":666,"name":"گراش","province_id":17},{"id":667,"name":"گله دار","province_id":17},{"id":668,"name":"لار","province_id":17},{"id":669,"name":"لامرد","province_id":17},{"id":670,"name":"لپویی","province_id":17},{"id":671,"name":"لطیفی","province_id":17},{"id":672,"name":"مبارک آباددیز","province_id":17},{"id":673,"name":"مرودشت","province_id":17},{"id":674,"name":"مشکان","province_id":17},{"id":675,"name":"مصیری","province_id":17},{"id":676,"name":"مهر","province_id":17},{"id":677,"name":"میمند","province_id":17},{"id":678,"name":"نوبندگان","province_id":17},{"id":679,"name":"نوجین","province_id":17},{"id":680,"name":"نودان","province_id":17},{"id":681,"name":"نورآباد","province_id":17},{"id":682,"name":"نی ریز","province_id":17},{"id":683,"name":"وراوی","province_id":17},{"id":684,"name":"ارداق","province_id":18},{"id":685,"name":"اسفرورین","province_id":18},{"id":686,"name":"اقبالیه","province_id":18},{"id":687,"name":"الوند","province_id":18},{"id":688,"name":"آبگرم","province_id":18},{"id":689,"name":"آبیک","province_id":18},{"id":690,"name":"آوج","province_id":18},{"id":691,"name":"بوئین زهرا","province_id":18},{"id":692,"name":"بیدستان","province_id":18},{"id":693,"name":"تاکستان","province_id":18},{"id":694,"name":"خاکعلی","province_id":18},{"id":695,"name":"خرمدشت","province_id":18},{"id":696,"name":"دانسفهان","province_id":18},{"id":697,"name":"رازمیان","province_id":18},{"id":698,"name":"سگزآباد","province_id":18},{"id":699,"name":"سیردان","province_id":18},{"id":700,"name":"شال","province_id":18},{"id":701,"name":"شریفیه","province_id":18},{"id":702,"name":"ضیاآباد","province_id":18},{"id":703,"name":"قزوین","province_id":18},{"id":704,"name":"کوهین","province_id":18},{"id":705,"name":"محمدیه","province_id":18},{"id":706,"name":"محمودآباد نمونه","province_id":18},{"id":707,"name":"معلم کلایه","province_id":18},{"id":708,"name":"نرجه","province_id":18},{"id":709,"name":"جعفریه","province_id":19},{"id":710,"name":"دستجرد","province_id":19},{"id":711,"name":"سلفچگان","province_id":19},{"id":712,"name":"قم","province_id":19},{"id":713,"name":"قنوات","province_id":19},{"id":714,"name":"کهک","province_id":19},{"id":715,"name":"آرمرده","province_id":20},{"id":716,"name":"بابارشانی","province_id":20},{"id":717,"name":"بانه","province_id":20},{"id":718,"name":"بلبان آباد","province_id":20},{"id":719,"name":"بوئین سفلی","province_id":20},{"id":720,"name":"بیجار","province_id":20},{"id":721,"name":"چناره","province_id":20},{"id":722,"name":"دزج","province_id":20},{"id":723,"name":"دلبران","province_id":20},{"id":724,"name":"دهگلان","province_id":20},{"id":725,"name":"دیواندره","province_id":20},{"id":726,"name":"زرینه","province_id":20},{"id":727,"name":"سروآباد","province_id":20},{"id":728,"name":"سریش آباد","province_id":20},{"id":729,"name":"سقز","province_id":20},{"id":730,"name":"سنندج","province_id":20},{"id":731,"name":"شویشه","province_id":20},{"id":732,"name":"صاحب","province_id":20},{"id":733,"name":"قروه","province_id":20},{"id":734,"name":"کامیاران","province_id":20},{"id":735,"name":"کانی دینار","province_id":20},{"id":736,"name":"کانی سور","province_id":20},{"id":737,"name":"مریوان","province_id":20},{"id":738,"name":"موچش","province_id":20},{"id":739,"name":"یاسوکند","province_id":20},{"id":740,"name":"اختیارآباد","province_id":21},{"id":741,"name":"ارزوئیه","province_id":21},{"id":742,"name":"امین شهر","province_id":21},{"id":743,"name":"انار","province_id":21},{"id":744,"name":"اندوهجرد","province_id":21},{"id":745,"name":"باغین","province_id":21},{"id":746,"name":"بافت","province_id":21},{"id":747,"name":"بردسیر","province_id":21},{"id":748,"name":"بروات","province_id":21},{"id":749,"name":"بزنجان","province_id":21},{"id":750,"name":"بم","province_id":21},{"id":751,"name":"بهرمان","province_id":21},{"id":752,"name":"پاریز","province_id":21},{"id":753,"name":"جبالبارز","province_id":21},{"id":754,"name":"جوپار","province_id":21},{"id":755,"name":"جوزم","province_id":21},{"id":756,"name":"جیرفت","province_id":21},{"id":757,"name":"چترود","province_id":21},{"id":758,"name":"خاتون آباد","province_id":21},{"id":759,"name":"خانوک","province_id":21},{"id":760,"name":"خورسند","province_id":21},{"id":761,"name":"درب بهشت","province_id":21},{"id":762,"name":"دهج","province_id":21},{"id":763,"name":"رابر","province_id":21},{"id":764,"name":"راور","province_id":21},{"id":765,"name":"راین","province_id":21},{"id":766,"name":"رفسنجان","province_id":21},{"id":767,"name":"رودبار","province_id":21},{"id":768,"name":"ریحان شهر","province_id":21},{"id":769,"name":"زرند","province_id":21},{"id":770,"name":"زنگی آباد","province_id":21},{"id":771,"name":"زیدآباد","province_id":21},{"id":772,"name":"سیرجان","province_id":21},{"id":773,"name":"شهداد","province_id":21},{"id":774,"name":"شهربابک","province_id":21},{"id":775,"name":"صفائیه","province_id":21},{"id":776,"name":"عنبرآباد","province_id":21},{"id":777,"name":"فاریاب","province_id":21},{"id":778,"name":"فهرج","province_id":21},{"id":779,"name":"قلعه گنج","province_id":21},{"id":780,"name":"کاظم آباد","province_id":21},{"id":781,"name":"کرمان","province_id":21},{"id":782,"name":"کشکوئیه","province_id":21},{"id":783,"name":"کهنوج","province_id":21},{"id":784,"name":"کوهبنان","province_id":21},{"id":785,"name":"کیانشهر","province_id":21},{"id":786,"name":"گلباف","province_id":21},{"id":787,"name":"گلزار","province_id":21},{"id":788,"name":"لاله زار","province_id":21},{"id":789,"name":"ماهان","province_id":21},{"id":790,"name":"محمدآباد","province_id":21},{"id":791,"name":"محی آباد","province_id":21},{"id":792,"name":"مردهک","province_id":21},{"id":793,"name":"مس سرچشمه","province_id":21},{"id":794,"name":"منوجان","province_id":21},{"id":795,"name":"نجف شهر","province_id":21},{"id":796,"name":"نرماشیر","province_id":21},{"id":797,"name":"نظام شهر","province_id":21},{"id":798,"name":"نگار","province_id":21},{"id":799,"name":"نودژ","province_id":21},{"id":800,"name":"هجدک","province_id":21},{"id":801,"name":"یزدان شهر","province_id":21},{"id":802,"name":"ازگله","province_id":22},{"id":803,"name":"اسلام آباد غرب","province_id":22},{"id":804,"name":"باینگان","province_id":22},{"id":805,"name":"بیستون","province_id":22},{"id":806,"name":"پاوه","province_id":22},{"id":807,"name":"تازه آباد","province_id":22},{"id":808,"name":"جوان رود","province_id":22},{"id":809,"name":"حمیل","province_id":22},{"id":810,"name":"ماهیدشت","province_id":22},{"id":811,"name":"روانسر","province_id":22},{"id":812,"name":"سرپل ذهاب","province_id":22},{"id":813,"name":"سرمست","province_id":22},{"id":814,"name":"سطر","province_id":22},{"id":815,"name":"سنقر","province_id":22},{"id":816,"name":"سومار","province_id":22},{"id":817,"name":"شاهو","province_id":22},{"id":818,"name":"صحنه","province_id":22},{"id":819,"name":"قصرشیرین","province_id":22},{"id":820,"name":"کرمانشاه","province_id":22},{"id":821,"name":"کرندغرب","province_id":22},{"id":822,"name":"کنگاور","province_id":22},{"id":823,"name":"کوزران","province_id":22},{"id":824,"name":"گهواره","province_id":22},{"id":825,"name":"گیلانغرب","province_id":22},{"id":826,"name":"میان راهان","province_id":22},{"id":827,"name":"نودشه","province_id":22},{"id":828,"name":"نوسود","province_id":22},{"id":829,"name":"هرسین","province_id":22},{"id":830,"name":"هلشی","province_id":22},{"id":831,"name":"باشت","province_id":23},{"id":832,"name":"پاتاوه","province_id":23},{"id":833,"name":"چرام","province_id":23},{"id":834,"name":"چیتاب","province_id":23},{"id":835,"name":"دهدشت","province_id":23},{"id":836,"name":"دوگنبدان","province_id":23},{"id":837,"name":"دیشموک","province_id":23},{"id":838,"name":"سوق","province_id":23},{"id":839,"name":"سی سخت","province_id":23},{"id":840,"name":"قلعه رئیسی","province_id":23},{"id":841,"name":"گراب سفلی","province_id":23},{"id":842,"name":"لنده","province_id":23},{"id":843,"name":"لیکک","province_id":23},{"id":844,"name":"مادوان","province_id":23},{"id":845,"name":"مارگون","province_id":23},{"id":846,"name":"یاسوج","province_id":23},{"id":847,"name":"انبارآلوم","province_id":24},{"id":848,"name":"اینچه برون","province_id":24},{"id":849,"name":"آزادشهر","province_id":24},{"id":850,"name":"آق قلا","province_id":24},{"id":851,"name":"بندرترکمن","province_id":24},{"id":852,"name":"بندرگز","province_id":24},{"id":853,"name":"جلین","province_id":24},{"id":854,"name":"خان ببین","province_id":24},{"id":855,"name":"دلند","province_id":24},{"id":856,"name":"رامیان","province_id":24},{"id":857,"name":"سرخنکلاته","province_id":24},{"id":858,"name":"سیمین شهر","province_id":24},{"id":859,"name":"علی آباد کتول","province_id":24},{"id":860,"name":"فاضل آباد","province_id":24},{"id":861,"name":"کردکوی","province_id":24},{"id":862,"name":"کلاله","province_id":24},{"id":863,"name":"گالیکش","province_id":24},{"id":864,"name":"گرگان","province_id":24},{"id":865,"name":"گمیش تپه","province_id":24},{"id":866,"name":"گنبدکاووس","province_id":24},{"id":867,"name":"مراوه","province_id":24},{"id":868,"name":"مینودشت","province_id":24},{"id":869,"name":"نگین شهر","province_id":24},{"id":870,"name":"نوده خاندوز","province_id":24},{"id":871,"name":"نوکنده","province_id":24},{"id":872,"name":"ازنا","province_id":25},{"id":873,"name":"اشترینان","province_id":25},{"id":874,"name":"الشتر","province_id":25},{"id":875,"name":"الیگودرز","province_id":25},{"id":876,"name":"بروجرد","province_id":25},{"id":877,"name":"پلدختر","province_id":25},{"id":878,"name":"چالانچولان","province_id":25},{"id":879,"name":"چغلوندی","province_id":25},{"id":880,"name":"چقابل","province_id":25},{"id":881,"name":"خرم آباد","province_id":25},{"id":882,"name":"درب گنبد","province_id":25},{"id":883,"name":"دورود","province_id":25},{"id":884,"name":"زاغه","province_id":25},{"id":885,"name":"سپیددشت","province_id":25},{"id":886,"name":"سراب دوره","province_id":25},{"id":887,"name":"فیروزآباد","province_id":25},{"id":888,"name":"کونانی","province_id":25},{"id":889,"name":"کوهدشت","province_id":25},{"id":890,"name":"گراب","province_id":25},{"id":891,"name":"معمولان","province_id":25},{"id":892,"name":"مومن آباد","province_id":25},{"id":893,"name":"نورآباد","province_id":25},{"id":894,"name":"ویسیان","province_id":25},{"id":895,"name":"احمدسرگوراب","province_id":26},{"id":896,"name":"اسالم","province_id":26},{"id":897,"name":"اطاقور","province_id":26},{"id":898,"name":"املش","province_id":26},{"id":899,"name":"آستارا","province_id":26},{"id":900,"name":"آستانه اشرفیه","province_id":26},{"id":901,"name":"بازار جمعه","province_id":26},{"id":902,"name":"بره سر","province_id":26},{"id":903,"name":"بندرانزلی","province_id":26},{"id":906,"name":"پره سر","province_id":26},{"id":907,"name":"تالش","province_id":26},{"id":908,"name":"توتکابن","province_id":26},{"id":909,"name":"جیرنده","province_id":26},{"id":910,"name":"چابکسر","province_id":26},{"id":911,"name":"چاف و چمخاله","province_id":26},{"id":912,"name":"چوبر","province_id":26},{"id":913,"name":"حویق","province_id":26},{"id":914,"name":"خشکبیجار","province_id":26},{"id":915,"name":"خمام","province_id":26},{"id":916,"name":"دیلمان","province_id":26},{"id":917,"name":"رانکوه","province_id":26},{"id":918,"name":"رحیم آباد","province_id":26},{"id":919,"name":"رستم آباد","province_id":26},{"id":920,"name":"رشت","province_id":26},{"id":921,"name":"رضوانشهر","province_id":26},{"id":922,"name":"رودبار","province_id":26},{"id":923,"name":"رودبنه","province_id":26},{"id":924,"name":"رودسر","province_id":26},{"id":925,"name":"سنگر","province_id":26},{"id":926,"name":"سیاهکل","province_id":26},{"id":927,"name":"شفت","province_id":26},{"id":928,"name":"شلمان","province_id":26},{"id":929,"name":"صومعه سرا","province_id":26},{"id":930,"name":"فومن","province_id":26},{"id":931,"name":"کلاچای","province_id":26},{"id":932,"name":"کوچصفهان","province_id":26},{"id":933,"name":"کومله","province_id":26},{"id":934,"name":"کیاشهر","province_id":26},{"id":935,"name":"گوراب زرمیخ","province_id":26},{"id":936,"name":"لاهیجان","province_id":26},{"id":937,"name":"لشت نشا","province_id":26},{"id":938,"name":"لنگرود","province_id":26},{"id":939,"name":"لوشان","province_id":26},{"id":940,"name":"لولمان","province_id":26},{"id":941,"name":"لوندویل","province_id":26},{"id":942,"name":"لیسار","province_id":26},{"id":943,"name":"ماسال","province_id":26},{"id":944,"name":"ماسوله","province_id":26},{"id":945,"name":"مرجقل","province_id":26},{"id":946,"name":"منجیل","province_id":26},{"id":947,"name":"واجارگاه","province_id":26},{"id":948,"name":"امیرکلا","province_id":27},{"id":949,"name":"ایزدشهر","province_id":27},{"id":950,"name":"آلاشت","province_id":27},{"id":951,"name":"آمل","province_id":27},{"id":952,"name":"بابل","province_id":27},{"id":953,"name":"بابلسر","province_id":27},{"id":954,"name":"بلده","province_id":27},{"id":955,"name":"بهشهر","province_id":27},{"id":956,"name":"بهنمیر","province_id":27},{"id":957,"name":"پل سفید","province_id":27},{"id":958,"name":"تنکابن","province_id":27},{"id":959,"name":"جویبار","province_id":27},{"id":960,"name":"چالوس","province_id":27},{"id":961,"name":"چمستان","province_id":27},{"id":962,"name":"خرم آباد","province_id":27},{"id":963,"name":"خلیل شهر","province_id":27},{"id":964,"name":"خوش رودپی","province_id":27},{"id":965,"name":"دابودشت","province_id":27},{"id":966,"name":"رامسر","province_id":27},{"id":967,"name":"رستمکلا","province_id":27},{"id":968,"name":"رویان","province_id":27},{"id":969,"name":"رینه","province_id":27},{"id":970,"name":"زرگرمحله","province_id":27},{"id":971,"name":"زیرآب","province_id":27},{"id":972,"name":"ساری","province_id":27},{"id":973,"name":"سرخرود","province_id":27},{"id":974,"name":"سلمان شهر","province_id":27},{"id":975,"name":"سورک","province_id":27},{"id":976,"name":"شیرگاه","province_id":27},{"id":977,"name":"شیرود","province_id":27},{"id":978,"name":"عباس آباد","province_id":27},{"id":979,"name":"فریدونکنار","province_id":27},{"id":980,"name":"فریم","province_id":27},{"id":981,"name":"قائم شهر","province_id":27},{"id":982,"name":"کتالم","province_id":27},{"id":983,"name":"کلارآباد","province_id":27},{"id":984,"name":"کلاردشت","province_id":27},{"id":985,"name":"کله بست","province_id":27},{"id":986,"name":"کوهی خیل","province_id":27},{"id":987,"name":"کیاسر","province_id":27},{"id":988,"name":"کیاکلا","province_id":27},{"id":989,"name":"گتاب","province_id":27},{"id":990,"name":"گزنک","province_id":27},{"id":991,"name":"گلوگاه","province_id":27},{"id":992,"name":"محمودآباد","province_id":27},{"id":993,"name":"مرزن آباد","province_id":27},{"id":994,"name":"مرزیکلا","province_id":27},{"id":995,"name":"نشتارود","province_id":27},{"id":996,"name":"نکا","province_id":27},{"id":997,"name":"نور","province_id":27},{"id":998,"name":"نوشهر","province_id":27},{"id":999,"name":"اراک","province_id":28},{"id":1000,"name":"آستانه","province_id":28},{"id":1001,"name":"آشتیان","province_id":28},{"id":1002,"name":"پرندک","province_id":28},{"id":1003,"name":"تفرش","province_id":28},{"id":1004,"name":"توره","province_id":28},{"id":1005,"name":"جاورسیان","province_id":28},{"id":1006,"name":"خشکرود","province_id":28},{"id":1007,"name":"خمین","province_id":28},{"id":1008,"name":"خنداب","province_id":28},{"id":1009,"name":"داودآباد","province_id":28},{"id":1010,"name":"دلیجان","province_id":28},{"id":1011,"name":"رازقان","province_id":28},{"id":1012,"name":"زاویه","province_id":28},{"id":1013,"name":"ساروق","province_id":28},{"id":1014,"name":"ساوه","province_id":28},{"id":1015,"name":"سنجان","province_id":28},{"id":1016,"name":"شازند","province_id":28},{"id":1017,"name":"غرق آباد","province_id":28},{"id":1018,"name":"فرمهین","province_id":28},{"id":1019,"name":"قورچی باشی","province_id":28},{"id":1020,"name":"کرهرود","province_id":28},{"id":1021,"name":"کمیجان","province_id":28},{"id":1022,"name":"مامونیه","province_id":28},{"id":1023,"name":"محلات","province_id":28},{"id":1024,"name":"مهاجران","province_id":28},{"id":1025,"name":"میلاجرد","province_id":28},{"id":1026,"name":"نراق","province_id":28},{"id":1027,"name":"نوبران","province_id":28},{"id":1028,"name":"نیمور","province_id":28},{"id":1029,"name":"هندودر","province_id":28},{"id":1030,"name":"ابوموسی","province_id":29},{"id":1031,"name":"بستک","province_id":29},{"id":1032,"name":"بندرجاسک","province_id":29},{"id":1033,"name":"بندرچارک","province_id":29},{"id":1034,"name":"بندرخمیر","province_id":29},{"id":1035,"name":"بندرعباس","province_id":29},{"id":1036,"name":"بندرلنگه","province_id":29},{"id":1037,"name":"بیکا","province_id":29},{"id":1038,"name":"پارسیان","province_id":29},{"id":1039,"name":"تخت","province_id":29},{"id":1040,"name":"جناح","province_id":29},{"id":1041,"name":"حاجی آباد","province_id":29},{"id":1042,"name":"درگهان","province_id":29},{"id":1043,"name":"دهبارز","province_id":29},{"id":1044,"name":"رویدر","province_id":29},{"id":1045,"name":"زیارتعلی","province_id":29},{"id":1046,"name":"سردشت","province_id":29},{"id":1047,"name":"سندرک","province_id":29},{"id":1048,"name":"سوزا","province_id":29},{"id":1049,"name":"سیریک","province_id":29},{"id":1050,"name":"فارغان","province_id":29},{"id":1051,"name":"فین","province_id":29},{"id":1052,"name":"قشم","province_id":29},{"id":1053,"name":"قلعه قاضی","province_id":29},{"id":1054,"name":"کنگ","province_id":29},{"id":1055,"name":"کوشکنار","province_id":29},{"id":1056,"name":"کیش","province_id":29},{"id":1057,"name":"گوهران","province_id":29},{"id":1058,"name":"میناب","province_id":29},{"id":1059,"name":"هرمز","province_id":29},{"id":1060,"name":"هشتبندی","province_id":29},{"id":1061,"name":"ازندریان","province_id":30},{"id":1062,"name":"اسدآباد","province_id":30},{"id":1063,"name":"برزول","province_id":30},{"id":1064,"name":"بهار","province_id":30},{"id":1065,"name":"تویسرکان","province_id":30},{"id":1066,"name":"جورقان","province_id":30},{"id":1067,"name":"جوکار","province_id":30},{"id":1068,"name":"دمق","province_id":30},{"id":1069,"name":"رزن","province_id":30},{"id":1070,"name":"زنگنه","province_id":30},{"id":1071,"name":"سامن","province_id":30},{"id":1072,"name":"سرکان","province_id":30},{"id":1073,"name":"شیرین سو","province_id":30},{"id":1074,"name":"صالح آباد","province_id":30},{"id":1075,"name":"فامنین","province_id":30},{"id":1076,"name":"فرسفج","province_id":30},{"id":1077,"name":"فیروزان","province_id":30},{"id":1078,"name":"قروه درجزین","province_id":30},{"id":1079,"name":"قهاوند","province_id":30},{"id":1080,"name":"کبودر آهنگ","province_id":30},{"id":1081,"name":"گل تپه","province_id":30},{"id":1082,"name":"گیان","province_id":30},{"id":1083,"name":"لالجین","province_id":30},{"id":1084,"name":"مریانج","province_id":30},{"id":1085,"name":"ملایر","province_id":30},{"id":1086,"name":"نهاوند","province_id":30},{"id":1087,"name":"همدان","province_id":30},{"id":1088,"name":"ابرکوه","province_id":31},{"id":1089,"name":"احمدآباد","province_id":31},{"id":1090,"name":"اردکان","province_id":31},{"id":1091,"name":"اشکذر","province_id":31},{"id":1092,"name":"بافق","province_id":31},{"id":1093,"name":"بفروئیه","province_id":31},{"id":1094,"name":"بهاباد","province_id":31},{"id":1095,"name":"تفت","province_id":31},{"id":1096,"name":"حمیدیا","province_id":31},{"id":1097,"name":"خضرآباد","province_id":31},{"id":1098,"name":"دیهوک","province_id":31},{"id":1099,"name":"زارچ","province_id":31},{"id":1100,"name":"شاهدیه","province_id":31},{"id":1101,"name":"طبس","province_id":31},{"id":1103,"name":"عقدا","province_id":31},{"id":1104,"name":"مروست","province_id":31},{"id":1105,"name":"مهردشت","province_id":31},{"id":1106,"name":"مهریز","province_id":31},{"id":1107,"name":"میبد","province_id":31},{"id":1108,"name":"ندوشن","province_id":31},{"id":1109,"name":"نیر","province_id":31},{"id":1110,"name":"هرات","province_id":31},{"id":1111,"name":"یزد","province_id":31},{"id":1116,"name":"پرند","province_id":8},{"id":1117,"name":"فردیس","province_id":5},{"id":1118,"name":"مارلیک","province_id":5},{"id":1119,"name":"سادات شهر","province_id":27},{"id":1121,"name":"زیباکنار","province_id":26},{"id":1135,"name":"کردان","province_id":5},{"id":1137,"name":"ساوجبلاغ","province_id":5},{"id":1138,"name":"تهران دشت","province_id":5},{"id":1150,"name":"گلبهار","province_id":11},{"id":1153,"name":"قیامدشت","province_id":8},{"id":1155,"name":"بینالود","province_id":11},{"id":1159,"name":"پیربازار","province_id":26},{"id":1160,"name":"رضوانشهر","province_id":31}]');
    foreach ($cities_temp as $city)
        $all_cities[$city->province_id][] = [
            'id'    => $city->id,
            'title' => $city->name,
        ];

    $province_cities = $all_cities[$province_id];
    if (!$province_cities)
        wp_send_json_error(null, 404);

    wp_send_json_success($province_cities);
}
//**********************************************************************************************************/
function user_invitations_api($request)
{
    global $wpdb;

    $user_id = get_user_id_by_token(ez_authorization(true));

    $params = $request->get_params();
    $status     = $params['status'];
    $page_num   = (int)$params['page'];

    $page_num   = $page_num ?: 1;
    $status     = $status ?: 'was_invited';

    $items_per_page = 10;

    if ($status == 'was_invited') { // دعوت شدم

        $max_page_num   = ceil((int)($wpdb->get_var("SELECT COUNT(*) FROM invitations WHERE invited_id LIKE {$user_id}")) / $items_per_page);
        $offset         = ($page_num - 1) * $items_per_page;

        $invitations = $wpdb->get_results(
            $wpdb->prepare(
                'SELECT * FROM invitations WHERE invited_id = %d ORDER BY created_at DESC LIMIT %d, %d',
                (int) $user_id,
                (int) $offset,
                (int) $items_per_page
            )
        );
        foreach ($invitations as $invitation) {

            $product_id             = $invitation->product_id;
            $invitation_person_id   = $invitation->inviter_id;

            $invitation_person = get_user_by('id', $invitation_person_id);

            foreach (get_the_terms($product_id, 'product_tag') as $product_tag)
                $genres[] = [
                    'title' => str_replace('|||||', '', $product_tag->name),
                    'id'    => $product_tag->term_id,
                ];

            $invitation_status = $invitation->status;
            if ($invitation_status == 'pending')
                if (time() - (int)$invitation->created_at > 2 * 7 * 24 * 60 * 60) // دو هفته
                    $invitation_status = 'expired';

            $items[] = [
                'id'                => (int)$invitation->ID,
                'product_title'     => get_the_title($product_id),
                'product_level'     => (int)get_field("room_level", $product_id),
                'product_image'     => wp_get_attachment_url(get_post_thumbnail_id($product_id)),
                'product_hood_name' => get_field("room_loc", $product_id),
                'product_city_name' => get_the_terms($product_id, 'product_cat')[0]->name,
                'product_url'       => trim_home_url(get_permalink($product_id)),
                'product_genres'    => $genres,
                'product_rate'      => number_format(round(array_sum(get_post_meta($product_id, 'product_rates', true)) / get_post_meta($product_id, 'comments_count_new', true) / 20 / 5, 2), 2, '.', ''),
                'invitation_title'  => $invitation_person->data->display_name,
                'invitation_url'    => "profile/$invitation_person_id",
                'invitation_image'  => '',
                'invitation_status' => $invitation_status,
                'invitation_phone'  => null,
                'date'              => (int)$invitation->created_at,
            ];
        }
    } elseif ($status == 'invited') { // دعوت کردم

        $max_page_num   = ceil((int)($wpdb->get_var("SELECT COUNT(*) FROM invitations WHERE inviter_id LIKE {$user_id}")) / $items_per_page);
        $offset         = ($page_num - 1) * $items_per_page;

        $invitations = $wpdb->get_results(
            $wpdb->prepare(
                'SELECT * FROM invitations WHERE inviter_id = %d ORDER BY created_at DESC LIMIT %d, %d',
                (int) $user_id,
                (int) $offset,
                (int) $items_per_page
            )
        );
        foreach ($invitations as $invitation) {

            $product_id = $invitation->product_id;
            $invitation_person_id = $invitation->invited_id;

            $invitation_person = get_user_by('id', $invitation_person_id);

            foreach (get_the_terms($product_id, 'product_tag') as $product_tag)
                $genres[] = [
                    'title' => str_replace('|||||', '', $product_tag->name),
                    'id'    => $product_tag->term_id,
                ];

            $invitation_status = $invitation->status;
            if ($invitation_status == 'pending')
                if (time() - (int)$invitation->created_at > 2 * 7 * 24 * 60 * 60) // دو هفته
                    $invitation_status = 'expired';

            $invitation_phone = null;
            if ($invitation_status == 'approved')
                $invitation_phone = $invitation_person->data->user_login;

            $items[] = [
                'id'                => (int)$invitation->ID,
                'product_title'     => get_the_title($product_id),
                'product_level'     => (int)get_field("room_level", $product_id),
                'product_image'     => wp_get_attachment_url(get_post_thumbnail_id($product_id)),
                'product_hood_name' => get_field("room_loc", $product_id),
                'product_city_name' => get_the_terms($product_id, 'product_cat')[0]->name,
                'product_url'       => trim_home_url(get_permalink($product_id)),
                'product_genres'    => $genres,
                'product_rate'      => number_format(round(array_sum(get_post_meta($product_id, 'product_rates', true)) / get_post_meta($product_id, 'comments_count_new', true) / 20 / 5, 2), 2, '.', ''),
                'invitation_title'  => $invitation_person->data->display_name,
                'invitation_url'    => "profile/$invitation_person_id",
                'invitation_image'  => '',
                'invitation_status' => $invitation_status,
                'invitation_phone'  => $invitation_phone,
                'date'              => (int)$invitation->created_at,
            ];
        }
    }

    $data[] = [
        'type'  => 'invitations',
        'title' => 'دعوت های من',
        'data'  => [
            'tabs'          => [
                [
                    'type'  => 'status',
                    'title' => '',
                    'key'   => 'status',
                    'items' => [
                        [
                            'title' => 'دعوت شدم',
                            'id'    => 'was_invited',
                        ],
                        [
                            'title' => 'دعوت کردم',
                            'id'    => 'invited',
                        ],
                    ],
                ],
            ],
            'items'         => $items,
            'pagination'    => [
                'current_page'  => $page_num,
                'total_pages'   => $max_page_num,
            ]
        ],
    ];

    wp_send_json_success($data);
}
//**********************************************************************************************************/
function user_invitation_status_api($request)
{
    global $wpdb;

    $user_id = get_user_id_by_token(ez_authorization(true));

    $params = $request->get_params();
    $invitation_id  = $params['ID'];
    $status         = $params['status'];

    if (empty($invitation_id) || !$invitation_id)
        wp_send_json_error('آی دی دعوت را وارد کنید.', 400);

    if (empty($status) || !$status)
        wp_send_json_error('وضعیت دعوت را وارد کنید.', 400);

    $invitations_valid = $wpdb->get_results(
        $wpdb->prepare(
            'SELECT * FROM invitations WHERE ID = %d AND invited_id = %d',
            (int) $invitation_id,
            (int) $user_id
        )
    );
    if (empty($invitations_valid) || !$invitations_valid)
        wp_send_json_error('این دعوتنامه متعلق به شما نیست.', 400);

    $wpdb->update('invitations', ['status' => $status], array('ID' => $invitation_id));

    wp_send_json_success(true);
}
//**********************************************************************************************************/
function user_inviting_api($request)
{
    global $wpdb;

    $user_id = get_user_id_by_token(ez_authorization(true));

    $params = $request->get_params();
    $invited_id = $params['user_id'];
    $product_id = $params['product_id'];

    if (empty($invited_id) || !$invited_id)
        wp_send_json_error('آی دی کاربر را وارد کنید.', 400);

    if (empty($product_id) || !$product_id)
        wp_send_json_error('محصول را وارد کنید.', 400);

    $new_inviting = [
        'inviter_id'    => $user_id,
        'invited_id'    => $invited_id,
        'product_id'    => (int)$product_id,
        'status'        => 'pending',
    ];
    $wpdb->insert('invitations', $new_inviting);

    wp_send_json_success(true);
}
//**********************************************************************************************************/
function user_points_api($request)
{
    global $wpdb;

    $user_id = get_user_id_by_token(ez_authorization(true));

    $params = $request->get_params();
    $page_num = (int)$params['page'];

    $page_num = $page_num ?: 1;

    $items_per_page = 81;

    $max_page_num = ceil((int)($wpdb->get_var("SELECT COUNT(*) FROM points WHERE user_id LIKE {$user_id}")) / $items_per_page);

    $offset = ($page_num - 1) * $items_per_page;
    $points = $wpdb->get_results(
        $wpdb->prepare(
            'SELECT * FROM points WHERE user_id = %d ORDER BY created_at DESC LIMIT %d, %d',
            (int) $user_id,
            (int) $offset,
            (int) $items_per_page
        )
    );
    foreach ($points as $point) {

        $items[] = [
            'ID'            => (int)$point->ID,
            'description'   => $point->description,
            'action'        => $point->action,
            'point'         => (int)$point->point,
            'time'          => (int)$point->created_at,
        ];
    }

    $data[] = [
        'type'  => 'points',
        'title' => 'امتیاز من',
        'data'  => [
            'total'         => (int)get_user_points($user_id),
            'items'         => $items,
            'pagination'    => [
                'current_page'  => $page_num,
                'total_pages'   => $max_page_num,
            ]
        ]
    ];

    wp_send_json_success($data);
}
//**********************************************************************************************************/
function user_wallet_get_api($request)
{
    global $wldb, $wpdb;

    $user_id = get_user_id_by_token(ez_authorization(true));

    //    $user_role  = get_user_role($user_id);
    //    if ($user_role == 'sans_manager')
    //        $user_products = $wpdb->get_results( "SELECT *  FROM `wp_postmeta` WHERE `meta_key` LIKE 'sans_manager' AND `meta_value` LIKE {$user_id}", ARRAY_A );
    //    elseif ($user_role == 'compiler')
    //        $user_products = $wpdb->get_results( "SELECT *  FROM `wp_postmeta` WHERE `meta_key` LIKE 'user_ebtal' AND `meta_value` LIKE {$user_id}", ARRAY_A );
    //
    //    foreach ( $user_products as $user_product ) {
    //        $product_id = $user_product['post_id'];
    //
    //        $is_active = get_post_meta($product_id, 'sale_active', true);
    //        $post_type = get_post_type($product_id);
    //
    //        if ( $is_active && $post_type == 'product' ) {
    //            $brand_title = (get_the_terms($product_id, 'product_brand')[0])->name;
    //            break;
    //        }
    //    }

    $withdrawal_owner_name_value    = get_user_meta($user_id, 'withdrawal_owner_name', true);
    $withdrawal_owner_shaba_value   = get_user_meta($user_id, 'withdrawal_owner_shaba', true);

    $data[] = [
        'type'  => 'wallet',
        'title' => 'کیف پول',
        'data'  => [
            'owner_name'        => $withdrawal_owner_name_value,
            'owner_shaba'       => $withdrawal_owner_shaba_value,
            'balance'           => $wldb->get_balance($user_id),
        ]
    ];

    wp_send_json_success($data);
}
//**********************************************************************************************************/
function user_wallet_transactions_api($request)
{
    global $wldb, $wpdb;

    $user_id = get_user_id_by_token(ez_authorization(true));

    $params = $request->get_params();
    $status     = $params['status'];
    $page_num   = (int)$params['page'];

    $page_num   = $page_num ?: 1;
    $status     = $status ?: -1;

    $items_per_page = 10;
    $user_id        = (int) $user_id;
    $offset         = ( $page_num - 1 ) * $items_per_page;

    if ( $status === 'withdraws' ) {
        $count_sql = 'SELECT COUNT(*) FROM wallet_transactions WHERE user_id = %d AND amount < 0';
        $data_sql  = 'SELECT * FROM wallet_transactions WHERE user_id = %d AND amount < 0 ORDER BY created_at DESC LIMIT %d, %d';
    } elseif ( $status === 'deposits' ) {
        $count_sql = 'SELECT COUNT(*) FROM wallet_transactions WHERE user_id = %d AND amount > 0';
        $data_sql  = 'SELECT * FROM wallet_transactions WHERE user_id = %d AND amount > 0 ORDER BY created_at DESC LIMIT %d, %d';
    } else {
        $count_sql = 'SELECT COUNT(*) FROM wallet_transactions WHERE user_id = %d';
        $data_sql  = 'SELECT * FROM wallet_transactions WHERE user_id = %d ORDER BY created_at DESC LIMIT %d, %d';
    }

    $max_page_num = ceil( (int) $wpdb->get_var( $wpdb->prepare( $count_sql, $user_id ) ) / $items_per_page );
    $transactions = $wpdb->get_results( $wpdb->prepare( $data_sql, $user_id, $offset, $items_per_page ) );
    if (!empty($transactions)) :
        foreach ($transactions as $key => $trans) {
            $items[] = [
                'id'                => $key + 1,
                'transaction_id'    => (int)$trans->ID,
                'request_time'      => (int)$trans->created_at,
                'amount'            => (int)$trans->amount,
                'prev_balance'      => (int)$trans->balance - $trans->amount,
                'balance'           => (int)$trans->balance,
                'description'       => $trans->description,
                'status'            => $trans->status ?: null,
            ];
        }
    endif;

    $data[] = [
        'type'  => 'wallet_transactions',
        'title' => 'تراکنش های کیف پول',
        'data'  => [
            'tabs'          => [
                [
                    'type'  => 'status',
                    'title' => '',
                    'key'   => 'status',
                    'items' => [
                        [
                            'title' => 'همه',
                            'id'    => -1
                        ],
                        [
                            'title' => 'واریزی ها',
                            'id'    => 'deposits',
                        ],
                        [
                            'title' => 'برداشت ها',
                            'id'    => 'withdraws',
                        ],
                    ],
                ],
            ],
            'items'         => $items,
            'pagination'    => [
                'current_page'  => $page_num,
                'total_pages'   => $max_page_num,
            ]
        ]
    ];

    wp_send_json_success($data);
}
//**********************************************************************************************************/
function user_wallet_withdrawals_api($request)
{
    global $wpdb;

    $user_id = get_user_id_by_token(ez_authorization(true));

    $params = $request->get_params();
    $status     = $params['status'];
    $page_num   = (int)$params['page'];

    $page_num   = $page_num ?: 1;
    $status     = $status ?: -1;

    if ($status == -1)
        $type = -1;
    elseif ($status == 'processing')
        $type = "در حال پردازش";
    elseif ($status == 'rejected')
        $type = "رد شده";
    elseif ($status == 'done')
        $type = "انجام شد";

    $items_per_page = 10;
    $user_id        = (int) $user_id;
    $offset         = ( $page_num - 1 ) * $items_per_page;

    if ( $status === -1 ) {
        $count_sql = "SELECT COUNT(*) FROM wallet_transactions WHERE user_id = %d AND type = 'withdraw'";
        $data_sql  = "SELECT * FROM wallet_transactions WHERE user_id = %d AND type = 'withdraw' ORDER BY created_at DESC LIMIT %d, %d";
        $max_page_num = ceil( (int) $wpdb->get_var( $wpdb->prepare( $count_sql, $user_id ) ) / $items_per_page );
        $transactions = $wpdb->get_results( $wpdb->prepare( $data_sql, $user_id, $offset, $items_per_page ) );
    } else {
        $count_sql = "SELECT COUNT(*) FROM wallet_transactions WHERE user_id = %d AND type = 'withdraw' AND status = %s";
        $data_sql  = "SELECT * FROM wallet_transactions WHERE user_id = %d AND type = 'withdraw' AND status = %s ORDER BY created_at DESC LIMIT %d, %d";
        $max_page_num = ceil( (int) $wpdb->get_var( $wpdb->prepare( $count_sql, $user_id, $type ) ) / $items_per_page );
        $transactions = $wpdb->get_results( $wpdb->prepare( $data_sql, $user_id, $type, $offset, $items_per_page ) );
    }
    if (!empty($transactions)) :
        foreach ($transactions as $key => $trans) {
            $items[] = [
                'id'                => $key + 1,
                'transaction_id'    => (int)$trans->ID,
                'request_time'      => (int)$trans->created_at,
                'amount'            => (int)$trans->amount * -1,
                'status'            => $trans->status ?: null,
            ];
        }
    endif;

    $data[] = [
        'type'  => 'wallet_transactions',
        'title' => 'تراکنش های کیف پول',
        'data'  => [
            'tabs'          => [
                [
                    'type'  => 'status',
                    'title' => '',
                    'key'   => 'status',
                    'items' => [
                        [
                            'title' => 'همه',
                            'id'    => -1
                        ],
                        [
                            'title' => 'در حال پردازش',
                            'id'    => 'processing',
                        ],
                        [
                            'title' => 'رد شده',
                            'id'    => 'rejected',
                        ],
                        [
                            'title' => 'انجام شد',
                            'id'    => 'done',
                        ],
                    ],
                ],
            ],
            'items'         => $items,
            'pagination'    => [
                'current_page'  => $page_num,
                'total_pages'   => $max_page_num,
            ]
        ]
    ];

    wp_send_json_success($data);
}
//**********************************************************************************************************/
function user_wallet_withdrawal_api($request)
{
    global $wldb;

    $user_id = get_user_id_by_token(ez_authorization(true));

    $params = $request->get_params();
    $amount = $params['amount'];

    $current_balance    = $wldb->get_balance($user_id);
    $amount             = $amount * (-1);
    $balance            = $current_balance + $amount;

    if (empty($amount) || !$amount)
        wp_send_json_error('مبلغ درخواست شده صحیح نمی باشد.', 400);

    if ($balance < 0)
        wp_send_json_error('مبلغ درخواست شده بیشتر از موجودی شماست.', 400);

    if (!empty($wldb->get(array('user_id' => $user_id, 'type' => 'withdraw', 'status' => 'در حال پردازش'), -1)))
        wp_send_json_error('شما یک درخواست تسویه فعال دارید. لطفا تا تسویه کامل آن درخواست دیگری انجام ندهید.', 400);

    $new_transaction = array(
        'user_id'       => $user_id,
        'amount'        => $amount,
        'balance'       => $balance,
        'description'   => 'درخواست تسویه حساب',
        'type'          => 'withdraw',
        'status'        => 'در حال پردازش',
        'origin'        => 2,
    );
    $res = $wldb->insert($new_transaction);

    wp_send_json_success($res);
}
//**********************************************************************************************************/
function user_sans_management_api($request)
{
    global $wpdb;

    $user_id = get_user_id_by_token(ez_authorization(true));

    $user_role  = get_user_role($user_id);
    if ($user_role == 'sans_manager')
        $user_products = $wpdb->get_results("SELECT *  FROM `wp_postmeta` WHERE `meta_key` LIKE 'sans_manager' AND `meta_value` LIKE {$user_id}", ARRAY_A);
    elseif ($user_role == 'compiler')
        $user_products = $wpdb->get_results("SELECT *  FROM `wp_postmeta` WHERE `meta_key` LIKE 'user_ebtal' AND `meta_value` LIKE {$user_id}", ARRAY_A);

    foreach ($user_products as $user_product) {

        $sale_status = get_post_meta($user_product['post_id'], 'product_state', true);
        $is_active  = ($sale_status == 'active' or $sale_status == 'updated') ? 1 : 0;
        $post_type  = get_post_type($user_product['post_id']);

        if ($is_active && $post_type == 'product')
            $active_products[] = $user_product['post_id'];
    }

    if (empty($user_products) || empty($active_products))
        wp_send_json_error('شما هیچ اتاق فعالی برای نمایش ندارید.', 400);

    else {
        foreach ($active_products as $user_product) {
            $product = wc_get_product($user_product);

            $items[] = [
                'id'    => $product->get_id(),
                'title' => $product->get_title(),
                'image' => wp_get_attachment_url(get_post_thumbnail_id($product->get_id())),
                'url'   => $product->get_permalink(),
            ];
        }
    }

    $data[] = [
        'type'  => 'sans_management',
        'title' => 'مدیریت سانس ها',
        'data'  => [
            'items'         => $items,
            'empty_message' => 'شما هیچ اتاق فرار فعالی ندارید.'
        ]
    ];

    wp_send_json_success($data);
}
//**********************************************************************************************************/
function user_collections_api($request)
{
    global $wpdb;

    $user_id = get_user_id_by_token(ez_authorization(true));

    $params = $request->get_params();
    $collection_id  = $params['collection_id'];
    $page_num       = (int)$params['page'];

    $page_num = $page_num ?: 1;

    $items_per_page = 3;

    if ($collection_id) {

        $collections = $wpdb->get_results(
            $wpdb->prepare(
                'SELECT * FROM collections WHERE user_id = %d AND ID = %d',
                (int) $user_id,
                (int) $collection_id
            )
        );
        if (empty($collections) || !$collections)
            wp_send_json_error('این کالکشن متعلق به شما نیست!', 400);
    } else {
        $collections = $wpdb->get_results(
            $wpdb->prepare(
                'SELECT * FROM collections WHERE user_id = %d',
                (int) $user_id
            )
        );

        foreach ($collections as $c)
            $tabs[] = [
                'title' => $c->title,
                'id'    => (int)$c->ID,
            ];
    }

    $collection = $collections[0];

    $product_ids    = unserialize($collection->items);
    $max_page_num   = ceil(count($product_ids) / $items_per_page);
    $product_ids    = array_slice($product_ids, ($page_num - 1) * $items_per_page, $items_per_page);

    foreach ($product_ids as $product_id) {

        foreach (get_the_terms($product_id, 'product_tag') as $product_tag)
            $genres[] = [
                'title' => str_replace('|||||', '', $product_tag->name),
                'id'    => $product_tag->term_id,
            ];

        $products[] = [
            'product_id'    => $product_id,
            'title'         => get_the_title($product_id),
            'image'         => '',
            'level'         => get_field("room_level", $product_id),
            'hood_name'     => get_the_terms($product_id, 'product_cat')[0]->name,
            'url'           => trim_home_url(get_permalink($product_id)),
            'genres'        => $genres,
        ];
    }

    $data[] = [
        'type'  => 'collections',
        'title' => 'کالکشن های من',
        'data'  => [
            'tabs'          => [
                [
                    'type'  => 'collection_id',
                    'title' => '',
                    'key'   => 'collection_id',
                    'items' => $tabs,
                ],
            ],
            'items'         => [
                'id'            => (int)$collection->ID,
                'title'         => $collection->title,
                'type'          => $collection->type,
                'likes_count'   => (int)$collection->likes_count,
                'items'         => $products
            ],
            'pagination'    => [
                'current_page'  => $page_num,
                'total_pages'   => $max_page_num,
            ]
        ]
    ];

    wp_send_json_success($data);
}
//**********************************************************************************************************/
function user_add_collection_api($request)
{
    global $wpdb;

    $user_id = get_user_id_by_token(ez_authorization(true));

    $params = $request->get_params();
    $title  = $params['title'];
    $type   = $params['type'];

    if (!isset($title) || empty($title))
        wp_send_json_error(array('error' => 'عنوان را وارد کنید.'), 400);

    if (!isset($type) || empty($type))
        wp_send_json_error(array('error' => 'عنوان را وارد کنید.'), 400);

    $new_collection = [
        'user_id'       => $user_id,
        'title'         => $title,
        'likes_count'   => 0,
        'items'         => [],
        'active'        => 0,
        'type'          => $type,
        'created_at'    => time(),
    ];

    do_action('collection_add', $user_id);

    $wpdb->insert('collections', $new_collection);

    wp_send_json_success($wpdb->insert_id);
}
//**********************************************************************************************************/
function user_update_collection_api($request)
{
    global $wpdb;

    $user_id = get_user_id_by_token(ez_authorization(true));

    $params = $request->get_params();
    $collection_id  = $params['ID'];
    $title          = $params['title'];
    $item           = $params['item'];

    if (!isset($collection_id) || empty($collection_id))
        wp_send_json_error(array('error' => 'آی دی کالکشن را وارد کنید.'), 400);

    $collection = $wpdb->get_results(
        $wpdb->prepare(
            'SELECT * FROM collections WHERE user_id = %d AND ID = %d',
            (int) $user_id,
            (int) $collection_id
        )
    )[0];
    if (empty($collection) || !$collection)
        wp_send_json_error('این کالکشن متعلق به شما نیست!', 400);

    if ($title) // عنوان نیاز به آپدیت دارد
        $update_collection['title'] = $title;

    if (!empty($item)) { // آیتم ها نیاز به آپدیت دارند
        $current_items = unserialize($collection->items) ?: [];

        if ($item['state'] === 'add' && !in_array($item['product_id'], $current_items))
            $current_items[] = $item['product_id'];

        if ($item['state'] === 'remove')
            unset($current_items[array_search($item['product_id'], $current_items)]);

        if (empty($current_items)) // کالکشن خالی نمیتواند فعال بماند
            $update_collection['active'] = 0;

        $update_collection['items'] = serialize($current_items);
    }

    $wpdb->update('collections', $update_collection, array('ID' => $collection_id));

    wp_send_json_success(true);
}
//**********************************************************************************************************/
function user_active_deactivated_collection_api($request)
{
    global $wpdb;

    $user_id = get_user_id_by_token(ez_authorization(true));

    $params = $request->get_params();
    $collection_id  = $params['ID'];
    $active         = $params['active'];

    if (!isset($collection_id) || empty($collection_id))
        wp_send_json_error(array('error' => 'آی دی کالکشن را وارد کنید.'), 400);

    if (!isset($active) || ($active === '' || $active === null))
        wp_send_json_error(array('error' => 'وضعیت کالکشن را وارد کنید.'), 400);

    $collection = $wpdb->get_results(
        $wpdb->prepare(
            'SELECT * FROM collections WHERE user_id = %d AND ID = %d',
            (int) $user_id,
            (int) $collection_id
        )
    )[0];
    if (empty($collection) || !$collection)
        wp_send_json_error('این کالکشن متعلق به شما نیست!', 400);

    if (empty(unserialize($collection->items)))
        wp_send_json_error('این کالشن خالی است و نمیتواند فعال شود!', 400);

    $wpdb->update('collections', ['active' => $active], ['ID' => $collection_id]);

    wp_send_json_success(true);
}
//**********************************************************************************************************/
function user_like_collection_api($request)
{
    global $wpdb;

    $user_id = get_user_id_by_token(ez_authorization(true));

    $params = $request->get_params();
    $collection_id = $params['ID'];

    $collection = $wpdb->get_results(
        $wpdb->prepare(
            'SELECT * FROM collections WHERE ID = %d',
            (int) $collection_id
        )
    )[0];
    if (!$collection)
        wp_send_json_error(null, 404);

    $liked_collections = get_user_meta($user_id, 'liked_collections', true);

    if (empty($liked_collections))
        $liked_collections = [];

    if ($collection->user_id == $user_id)
        wp_send_json_error('شما نمیتوانید کالکشن خود را لایک کنید.', 400);

    if (in_array($collection_id, $liked_collections))
        wp_send_json_error('شما قبلا این کالکشن را لایک کرده اید.', 400);

    $liked_collections[] = $collection_id;
    update_user_meta($user_id, 'liked_collections', $liked_collections);

    $likes_count = ++$collection->likes_count;
    $wpdb->update('collections', ['likes_count' => $likes_count], array('ID' => $collection_id));

    wp_send_json_success($likes_count);
}
//**********************************************************************************************************/
function user_order_details_api($request)
{
    global $wpdb;

    $user_id = get_user_id_by_token(ez_authorization(true));

    $params = $request->get_params();
    $order_id = $params['id'];

    $order_user_id = get_post_meta($order_id, '_customer_user', true);
    if ($order_user_id != $user_id)
        wp_send_json_error('این سفارش متعلق به شما نیست!');

    $order = wc_get_order($order_id);

    foreach ($order->get_items() as $item) {
        $product_id     = $item->get_product_id();
        $quantity       = $item->get_quantity();
    }

    $pish_per_person    = get_post_meta($order_id, 'ticket_tedad', true);
    $pish_per_person    = !empty($pish_per_person) ? $pish_per_person : get_post_meta($product_id, 'pish_pardakht_per_person', true);
    $pish_per_person    = !empty($pish_per_person) ? $pish_per_person : 1;

    $pish       = get_post_meta($order_id, "_order_total_2", true);
    $pish_final = $pish ?: get_post_meta($order_id, "_order_total", true);

    $item_total = $pish_final / $pish_per_person * $quantity;

    $args = [
        "single_value"  => true,
        "query"         => "SELECT * FROM `wp_zb_booking_history` WHERE `wc_order_id` = $order_id",
    ];
    $response = ez_reservation(array('type' => 'query_execution', 'data' => $args));
    $row = (array)json_decode($response);

    $brand_data = get_the_terms($product_id, 'product_brand')[0];

    $data = [
        'order_id'      => (int)$order_id,
        'product_title' => get_the_title($product_id),
        'brand_data'    => [
            'title'     => $brand_data->name,
            'logo'      => wp_get_attachment_url(get_term_meta($brand_data->term_id, 'thumbnail_id', true)),
            'phones'    => [
                get_field('room_phone', $product_id),
                get_field('room_phone_2', $product_id),
            ],
        ],
        'tickets_count' => $quantity,
        'purchase_time' => (int)$row['booked_time'],
        'sans_time'     => (int)$row['booking_time'],
        'total_payment' => (int)$item_total,
        'prepaid'       => (int)$pish_final,
        'address'       => get_field('room_address', $product_id),
        'product_url'   => trim_home_url(get_permalink($product_id)),
        'qrcode_data'   => "/geo.php?g=" . get_field('room_lat', $product_id) . ',' . get_field('room_long', $product_id),
    ];

    wp_send_json_success($data);
}
//**********************************************************************************************************/
function user_add_ticket_api($request)
{

    $user_id = get_user_id_by_token(ez_authorization(true));

    $params = $request->get_params();
    $title      = $params['title'];
    $body       = $params['body'];
    $attachment = $params['attachment'];
    $type       = $params['type'];

    if (!isset($title) || empty($title))
        wp_send_json_error(array('error' => 'عنوان را وارد کنید.'), 400);

    if (!isset($body) || empty($body))
        wp_send_json_error(array('error' => 'توضیحات را وارد کنید.'), 400);

    if (!isset($type) || empty($type))
        wp_send_json_error(array('error' => 'دپارتمان مربوطه را انتخاب کنید.'), 400);

    $ticket_id = wp_insert_post(array(
        'post_type'         => 'ticketing',
        'post_author'       => $user_id,
        'post_title'        => $title,
        'post_content'      => $type,
        'post_status'       => 'pending',
        'comment_status'    => 'closed',
        'ping_status'       => 'closed',
    ));

    if ($ticket_id) {

        $messages[] = [
            'body'          => $body,
            'user_type'     => 'user',
            'date'          => time(),
            'attachment'    => $attachment,
        ];

        update_post_meta($ticket_id, 'messages', $messages);
        update_post_meta($ticket_id, 'respond_user_role', 'user');
        update_post_meta($ticket_id, 'ticket_closed', 0);
        update_post_meta($ticket_id, 'admin_seen', 0);

        wp_send_json_success($ticket_id);
    } else
        wp_send_json_error(array('error' => 'مشکلی پیش آمده دوباره تلاش کنید.'), 400);
}
//**********************************************************************************************************/
function user_get_ticket_api($request)
{

    $user_id = get_user_id_by_token(ez_authorization(true));

    $params = $request->get_params();
    $ticket_id = $params['id'];

    if (!ticket_verify($ticket_id, $user_id))
        wp_send_json_error(array('error' => 'این تیکت متعلق به شما نیست!'), 400);

    delete_post_meta($ticket_id, "user_seen");

    $messages = get_post_meta($ticket_id, 'messages', true);
    foreach (array_reverse($messages) as $message) {
        $items[] = [
            'body'          => $message['body'],
            'author_type'   => $message['user_type'],
            'sent_time'     => $message['date'],
            'attachment'    => $message['attachment'],
        ];
    }

    $ticket = get_post($ticket_id);
    $ticket_details = [
        'id'            => (int)$ticket_id,
        'title'         => get_the_title($ticket_id),
        'sent_time'     => strtotime($ticket->post_date),
        'updated_time'  => end($messages)['date'],
        'type'          => $ticket->post_content,
        'status'        => get_ticket_status($ticket_id),
        'rate'          => isset($ticket->ticket_rate) ? (int)$ticket->ticket_rate : null,
        'items'         => $items,
    ];

    wp_send_json_success($ticket_details);
}
//**********************************************************************************************************/
function user_add_message_api($request)
{

    $user_id = get_user_id_by_token(ez_authorization(true));

    $params = $request->get_params();
    $ticket_id  = $params['ID'];
    $msg        = $params['body'];
    $attachment = $params['attachment'];

    if (!isset($ticket_id) || empty($ticket_id))
        wp_send_json_error(array('error' => 'آی دی تیکت را وارد کنید.'), 400);

    if (!isset($msg) || empty($msg))
        wp_send_json_error(array('error' => 'پیام را وارد کنید.'), 400);

    if (!ticket_verify($ticket_id, $user_id))
        wp_send_json_error(array('error' => 'این پشتیبانی توسط شما درخواست نشده است.'), 400);

    $messages = get_post_meta($ticket_id, 'messages', true);

    $messages[] = [
        'body'          => $msg,
        'user_type'     => 'user',
        'date'          => time(),
        'attachment'    => $attachment,
    ];

    update_post_meta($ticket_id, 'messages', $messages);
    update_post_meta($ticket_id, 'ticket_closed', 0);
    update_post_meta($ticket_id, 'respond_user_role', 'user');
    update_post_meta($ticket_id, 'admin_seen', 0);

    // برای هندل کردن نوتیف ها
    wp_update_post(array(
        'ID'            => $ticket_id,
        'post_status'   => 'pending'
    ));

    wp_send_json_success(true);
}
//**********************************************************************************************************/
function user_upload_api($request)
{

    //    $user_id = get_user_id_by_token( ez_authorization(true) );

    $params = $request->get_file_params();

    customer_files_self_destruct_function(); // remove old files (10 days ago)

    $links = [];
    foreach ($params as $file) :
        if (!empty($file)) {

            // size controlling
            $max_file_size = 200 * 1024 * 1024;
            if ($file['size'] > $max_file_size)
                wp_send_json_error(array('error' => 'سایز فایل شما بیشتر از حد مجاز می باشد. '));

            // format controlling
            $allowed_extensions = array('mp4', 'pdf', 'jpg', 'png', 'zip');
            $file_extension     = pathinfo($file['name'], PATHINFO_EXTENSION);
            if (!in_array(strtolower($file_extension), $allowed_extensions))
                wp_send_json_error(array('error' => 'فرمت فایل ارسالی غیرمجاز می باشد.'));

            if (!function_exists('wp_handle_upload'))
                require_once(ABSPATH . 'wp-admin/includes/file.php');

            add_filter('upload_dir', 'change_default_upload_dir_for_customer_files');
            $customer_file = wp_handle_upload($file, array('test_form' => false, 'unique_filename_callback' => 'customer_files_name'));
            remove_filter('upload_dir', 'change_default_upload_dir_for_customer_files');

            if ($customer_file && !isset($customer_file['error'])) {
                $file = $customer_file['url'];
                wp_send_json_success($file);
            } else
                wp_send_json_error($customer_file['error']);
        }
    endforeach;

    wp_send_json_success($links);
}
//**********************************************************************************************************/
function user_upload_self_destruct_api($request)
{

    //    $user_id = get_user_id_by_token( ez_authorization(true) );

    $params = $request->get_file_params();

    customer_files_self_destruct_function(); // remove old files (10 days ago)

    $links = [];
    foreach ($params as $file) :
        if (!empty($file)) {

            // size controlling
            $max_file_size = 200 * 1024 * 1024;
            if ($file['size'] > $max_file_size)
                wp_send_json_error(array('error' => 'سایز فایل شما بیشتر از حد مجاز می باشد. '));

            // format controlling
            $allowed_extensions = array('mp4', 'pdf', 'jpg', 'png', 'zip');
            $file_extension     = pathinfo($file['name'], PATHINFO_EXTENSION);
            if (!in_array(strtolower($file_extension), $allowed_extensions))
                wp_send_json_error(array('error' => 'فرمت فایل ارسالی غیرمجاز می باشد.'));

            if (!function_exists('wp_handle_upload'))
                require_once(ABSPATH . 'wp-admin/includes/file.php');

            add_filter('upload_dir', 'change_default_upload_dir_for_customer_files_self_destruct');
            $customer_file = wp_handle_upload($file, array('test_form' => false, 'unique_filename_callback' => 'customer_files_name'));
            remove_filter('upload_dir', 'change_default_upload_dir_for_customer_files_self_destruct');

            if ($customer_file && !isset($customer_file['error'])) {
                $file = $customer_file['url'];
                wp_send_json_success($file);
            } else
                wp_send_json_error($customer_file['error']);
        }
    endforeach;

    wp_send_json_success($links);
}
//**********************************************************************************************************/
function user_rate_ticket_api($request)
{

    $user_id = get_user_id_by_token(ez_authorization(true));

    $params = $request->get_params();
    $ticket_id  = $params['ID'];
    $rate       = $params['rate'];

    if (!isset($ticket_id) || empty($ticket_id))
        wp_send_json_error(array('error' => 'شماره تیکت مشخص نیست.'), 400);

    if (!isset($rate) || empty($rate))
        wp_send_json_error(array('error' => 'امتیاز شما مشخص نیست.'), 400);

    if (!ticket_verify($ticket_id, $user_id))
        wp_send_json_error('این تیکت متعلق به شما نیست!');

    $messages = get_post_meta($ticket_id, 'messages', true);

    $admin_respond_flag = false;
    foreach ($messages as $msg)
        if ($msg['user_type'] == 'admin') {
            $admin_respond_flag = true;
            break;
        }

    if ($admin_respond_flag)
        add_post_meta($ticket_id, 'ticket_rate', $rate, true);
    else
        wp_send_json_error('هنوز پاسخی از اسکیپ زوم دریافت نکرده اید.');

    wp_send_json_success(true);
}
//**********************************************************************************************************/
function user_close_ticket_api($request)
{

    $user_id = get_user_id_by_token(ez_authorization(true));

    $params = $request->get_params();
    $ticket_id = $params['ID'];

    if (!ticket_verify($ticket_id, $user_id))
        wp_send_json_error('این تیکت متعلق به شما نیست!');

    update_post_meta($ticket_id, 'ticket_closed', 1);

    wp_send_json_success(true);
}
//**********************************************************************************************************/
function user_profile_api($request)
{
    global $wpdb;

    $user_id = get_user_id_by_token(ez_authorization(false));

    $params = $request->get_params();
    $profile_user_id  = $params['id'];

    $profile_user = get_user_by('id', $profile_user_id);

    if (empty($profile_user))
        wp_send_json_error(null, 404);

    $posts_per_page = 5;
    $sort_type      = 'popular';
    $params = [
        'city_id' => [15],
    ];
    $args = [
        'params'        => $params,
        'image_type'    => 'url',
        'limit'         => $posts_per_page,
        'page'          => 1,
        'max_num_pages' => false,
        "format"        => 'api',
        'sort_type'     => $sort_type,
        'unpin_ads'     => false,
        'badge_ads'     => false,
        'random'        => false,
        'random_memory' => '',
        'show_more'     => 0,
    ];
    $products = json_decode(ez_webservice(array('type' => 'sort_products_get', 'data' => $args)));

    /******************************************************/
    // کالکشن ها

    $collections = $wpdb->get_results(
        $wpdb->prepare(
            'SELECT * FROM collections WHERE user_id = %d AND active = %d ORDER BY likes_count DESC',
            (int) $profile_user_id,
            1
        )
    );
    foreach ($collections as $collection) {

        $collection_products = json_decode(ez_webservice(array('type' => 'get_by_products_id', 'data' => ['products_id' => unserialize($collection->items)])));

        $collection_items[] = [
            'id'            => (int)$collection->ID,
            'title'         => $collection->title,
            'type'          => $collection->type,
            'likes_count'   => (int)$collection->likes_count,
            'liked'         => in_array($collection->ID, get_user_meta($user_id, 'liked_collections', true)),
            'items'         => $collection_products
        ];
    }

    /******************************************************/

    $data = [
        'user_id'       => (int)$profile_user_id,
        'name'          => $profile_user->data->display_name,
        'banner'        => 'https://escapezoom.ir/wp-content/uploads/2024/05/profile-banner.png',
        'city'          => 'تهران',
        'level'         => 2,
        'played_count'  => 303,
        'points'        => 606,
        'bio'           => 'لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ، و با استفاده از طراحان گرافیک است، چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است، و برای شرایط فعلی تکنولوژی مورد نیاز، و کاربردهای متنوع با هدف بهبود ابزارهای کاربردی می باشد، کتابهای زیادی در شصت و سه درصد گذشته حال و آینده، شناخت فراوان جامعه و متخصصان را می طلبد، تا با نرم افزارها شناخت بیشتری را برای طراحان رایانه ای علی الخصوص طراحان خلاقی، و فرهنگ پیشرو در زبان فارسی ایجاد کرد، در این صورت می توان امید داشت که تمام و دشواری موجود در ارائه راهکارها، و شرایط سخت تایپ به پایان رسد و زمان مورد نیاز شامل حروفچینی دستاوردهای اصلی، و جوابگوی سوالات پیوسته اهل دنیای موجود طراحی اساسا مورد استفاده قرار گیرد.',
        'recent_played' => $products,
        'register_date' => 1714907343,
        'recent_comment' => [
            'title'         => 'آخرین دیدگاه فاطمه خداپرست',
            'content'       => 'لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ، و با استفاده از طراحان گرافیک است، چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است، و برای شرایط فعلی تکنولوژی مورد نیاز، و کاربردهای متنوع با هدف بهبود ابزارهای کاربردی می باشد، کتابهای زیادی در شصت و سه درصد گذشته حال و آینده، شناخت فراوان جامعه و متخصصان را می طلبد، تا با نرم افزارها شناخت بیشتری را برای طراحان رایانه ای علی الخصوص طراحان خلاقی، و فرهنگ پیشرو در زبان فارسی ایجاد کرد، در این صورت می توان امید داشت که تمام و دشواری موجود در ارائه راهکارها، و شرایط سخت تایپ به پایان رسد و زمان مورد نیاز شامل حروفچینی دستاوردهای اصلی، و جوابگوی سوالات پیوسته اهل دنیای موجود طراحی اساسا مورد استفاده قرار گیرد.',
            'product_name'  => 'بازی فتنه عفریت',
            'product_url'   => '',
        ],
        'collections'   => $collection_items,
        'breadcrumb'    => [
            [
                'title' => 'صفحه اصلی',
                'url'   => '/',
            ],
            [
                'title' => 'پروفایل فاطمه',
                'url'   => '/',
            ],
        ],
    ];

    wp_send_json_success($data);
}
//**********************************************************************************************************/
function user_set_location_api($request)
{

    $user_id = get_user_id_by_token(ez_authorization(true));

    $params = $request->get_params();
    $lat    = $params['lat'];
    $long   = $params['long'];

    update_user_meta($user_id, 'geolocation', [$lat, $long]);

    wp_send_json_success(true);
}
//**********************************************************************************************************/
function user_set_settings_api($request)
{

    $user_id = get_user_id_by_token(ez_authorization(true));

    $params = $request->get_params();
    //    $name               = $params['name'];
    //    $phone              = $params['phone'];
    //    $email              = $params['email'];
    //    $province           = $params['province'];
    //    $city               = $params['city'];
    //    $bank_name          = $params['bank_name'];
    //    $credit_card_number = $params['credit_card_number'];
    //    $shaba              = $params['shaba'];
    //    $address            = $params['address'];

    update_user_meta($user_id, 'user_settings', $params);

    wp_send_json_success(true);
}
//**********************************************************************************************************/
function user_comments_api($request)
{
    global $wpdb;

    $user_id = get_user_id_by_token(ez_authorization(true));

    $params = $request->get_params();
    $page_num = (int)$params['page'];

    $page_num = $page_num ?: 1;

    $comments_per_page = 30;

    $user_role  = get_user_role($user_id);
    if ($user_role == 'sans_manager')
        $user_products = $wpdb->get_results("SELECT *  FROM `wp_postmeta` WHERE `meta_key` LIKE 'sans_manager' AND `meta_value` LIKE {$user_id}", ARRAY_A);
    elseif ($user_role == 'compiler')
        $user_products = $wpdb->get_results("SELECT *  FROM `wp_postmeta` WHERE `meta_key` LIKE 'user_ebtal' AND `meta_value` LIKE {$user_id}", ARRAY_A);

    foreach ($user_products as $user_product) {
        $post_type = get_post_type($user_product['post_id']);

        if ($post_type == 'product')
            $active_products[] = $user_product['post_id'];
    }

    if (empty($user_products) || empty($active_products))
        wp_send_json_error('شما هیچ اتاق فعالی برای نمایش ندارید.', 400);

    else {
        $product_ids_str = implode(',', $active_products);

        $total_comments = $wpdb->get_var("SELECT COUNT(*) FROM wp_comments WHERE comment_post_ID IN ($product_ids_str) AND comment_approved = 1");
        $total_pages    = ($total_comments > 0) ? ceil($total_comments / $comments_per_page) : 1;

        $args = array(
            'post_type' => 'product',
            'post__in'  => $active_products,
            'status'    => 'approve',
            'number'    => $comments_per_page,
            'paged'     => $page_num,
            'parent'    => 0,
        );
        $comments_query = new WP_Comment_Query;
        $comments = $comments_query->query($args);

        if ($comments) {
            foreach ($comments as $comment) {

                $comment_id = $comment->comment_ID;

                $replies_args = array(
                    'parent'    => $comment_id,
                    'status'    => 'approve',
                    'type'      => 'comment',
                );

                if (ctype_digit($comment->comment_author))
                    $author_title = str_replace(substr($comment->comment_author, 3, 5), "×××××", $comment->comment_author);

                $items[] = [
                    'id'            => (int)$comment_id,
                    'author_title'  => $author_title,
                    'product_image' => wp_get_attachment_url(get_post_thumbnail_id($comment->comment_post_ID)),
                    'author_level'  => 1,
                    'product_title' => get_the_title($comment->comment_post_ID),
                    'content'       => $comment->comment_content,
                    'reported'      => !empty(get_comment_meta($comment_id, 'report_reason', true)) ? true : false,
                    'date'          => strtotime($comment->comment_date),
                    'reply'         => (get_comments($replies_args)[0])->comment_content
                ];
            }
        }
    }

    $data[] = [
        'type'  => 'comment',
        'title' => 'نظرات من',
        'data'  => [
            'comment_count' => (int)$total_comments,
            'items'         => $items,
            'pagination'    => [
                'current_page'  => $page_num,
                'total_pages'   => $total_pages,
            ]
        ]
    ];

    wp_send_json_success($data);
}
//**********************************************************************************************************/
function user_comment_report_api($request)
{
    global $wpdb;

    $user_id = get_user_id_by_token(ez_authorization(true));

    $params = $request->get_params();
    $comment_id = (int)$params['ID'];
    $reason     = $params['reason'];

    if (!isset($comment_id) || empty($comment_id))
        wp_send_json_error(array('error' => 'شماره کامنت مشخص نیست.'), 400);

    if (!isset($reason) || empty($reason))
        wp_send_json_error(array('error' => 'علت ریپورت مشخص نیست.'), 400);

    $user_role  = get_user_role($user_id);
    if ($user_role == 'sans_manager')
        $user_products = $wpdb->get_results("SELECT *  FROM `wp_postmeta` WHERE `meta_key` LIKE 'sans_manager' AND `meta_value` LIKE {$user_id}", ARRAY_A);
    elseif ($user_role == 'compiler')
        $user_products = $wpdb->get_results("SELECT *  FROM `wp_postmeta` WHERE `meta_key` LIKE 'user_ebtal' AND `meta_value` LIKE {$user_id}", ARRAY_A);

    foreach ($user_products as $user_product) {
        $post_type = get_post_type($user_product['post_id']);

        if ($post_type == 'product')
            $active_products[] = $user_product['post_id'];
    }

    if (empty($user_products) || empty($active_products))
        wp_send_json_error('شما هیچ اتاق فعالی برای نمایش ندارید.', 400);

    if (!in_array(get_comment($comment_id)->comment_post_ID, $active_products))
        wp_send_json_error('این کامنت متعلق به شما نیست!', 400);

    if (!empty(get_comment_meta($comment_id, 'report_reason', true)))
        wp_send_json_error('این کامنت قبلا ریپورت شده است.', 400);

    add_comment_meta($comment_id, 'report_reason', $reason);

    wp_send_json_success(true);
}
//**********************************************************************************************************/
function user_comment_reply_api($request)
{
    global $wpdb;

    $user_id = get_user_id_by_token(ez_authorization(true));

    $params = $request->get_params();
    $comment_id = (int)$params['ID'];
    $reply     = $params['reply'];

    if (!isset($comment_id) || empty($comment_id))
        wp_send_json_error(array('error' => 'شماره کامنت مشخص نیست.'), 400);

    if (!isset($reply) || empty($reply))
        wp_send_json_error(array('error' => 'پاسخ شما مشخص نیست.'), 400);

    $user_role  = get_user_role($user_id);
    if ($user_role == 'sans_manager')
        $user_products = $wpdb->get_results("SELECT *  FROM `wp_postmeta` WHERE `meta_key` LIKE 'sans_manager' AND `meta_value` LIKE {$user_id}", ARRAY_A);
    elseif ($user_role == 'compiler')
        $user_products = $wpdb->get_results("SELECT *  FROM `wp_postmeta` WHERE `meta_key` LIKE 'user_ebtal' AND `meta_value` LIKE {$user_id}", ARRAY_A);

    foreach ($user_products as $user_product) {
        $post_type = get_post_type($user_product['post_id']);

        if ($post_type == 'product')
            $active_products[] = $user_product['post_id'];
    }

    if (empty($user_products) || empty($active_products))
        wp_send_json_error('شما هیچ اتاق فعالی برای نمایش ندارید.', 400);

    $product_id = get_comment($comment_id)->comment_post_ID;
    if (!in_array($product_id, $active_products))
        wp_send_json_error('این کامنت متعلق به شما نیست!', 400);

    $has_reply = $wpdb->get_results("SELECT *  FROM `wp_comments` WHERE `comment_parent` LIKE {$comment_id}", ARRAY_A);
    if (!empty($has_reply))
        wp_send_json_error('این کامنت قبلا پاسخ داده شده است.', 400);

    $comment_data = array(
        'comment_post_ID'   => $product_id,
        'comment_author'    => get_user_by('id', $user_id)->user_login,
        'comment_content'   => $reply,
        'comment_type'      => 'comment',
        'comment_parent'    => $comment_id,
        'user_id'           => $user_id,
        'comment_approved'  => 1,
    );
    $comment_id = wp_insert_comment($comment_data);

    if (!$comment_id)
        wp_send_json_error('ثبت نشد دوباره امتحان کنید.', 400);

    wp_send_json_success(true);
}

/*=========================================================================================================*/
//Product functions

function product_get_api($request)
{
    global $wpdb;

    //    $user_id = get_user_id_by_token( ez_authorization(false) );

    $params = $request->get_params();
    $param  = $params['param'];

    if (is_numeric($param)) {
        $product_obj = get_post($param);
        $product_id = (int)$param;
    } else {
        $product_obj = get_page_by_path($param, OBJECT, 'product');
        $product_id = $product_obj->ID;
    }

    if (!$product_obj)
        wp_send_json_error(null, 404);

    $brand_data = get_the_terms($product_id, 'product_brand')[0];

    $posts_per_page = 10;
    $sort_type      = 'popular';
    $params         = [
        'brand_id' => $brand_data->term_id,
    ];
    $args           = [
        'params'        => $params,
        'image_type'    => 'url',
        'limit'         => $posts_per_page,
        'page'          => 1,
        'max_num_pages' => false,
        "format"        => 'api',
        'sort_type'     => $sort_type,
        'unpin_ads'     => false,
        'badge_ads'     => false,
        'random'        => true,
        'random_memory' => '',
        'show_more'     => 0,
    ];
    $brand_products = json_decode(ez_webservice(array('type' => 'sort_products_get', 'data' => $args)));

    /**************************************/
    // پروداکت تایپ

    $terms = get_the_terms($product_id, 'product_cat');
    if (count($terms) > 1) {

        foreach ($terms as $term) {
            if ($term->parent == 0) {
                $product_type = $term->name;
                $product_parent_cat_url = get_term_link($term->term_id, "product_cat");
            } else {
                $city_name  = $term->name;
                $city_id    = $term->term_id;
                $product_cat_url        = get_term_link($term->term_id, "product_cat");
            }
        }
    } else {
        $product_type   = get_term($terms[0]->parent)->name;
        $city_name      = $terms[0]->name;
        $city_id        = $terms[0]->term_id;
        $product_parent_cat_url = get_term_link($terms[0]->parent, "product_cat");
        $product_cat_url        = get_term_link($terms[0]->term_id, "product_cat");
    }

    /**************************************/
    // ژانر

    foreach (get_the_terms($product_id, 'product_tag') as $product_tag) {

        if (str_contains($product_tag->name, '|||||'))
            $genres[] = [
                'title' => str_replace('|||||', '', $product_tag->name),
                'id'    => $product_tag->term_id
            ];
        else
            $tags[] = [
                'title' => $product_tag->name,
                'id'    => $product_tag->term_id
            ];
    }

    /**************************************/
    // گالری

    $product = wc_get_product($product_id);
    foreach ($product->get_gallery_image_ids() as $gallery_image)
        $gallery[] = wp_get_attachment_url($gallery_image);

    /**************************************/
    // کامنت ها

    $comments_count = get_post_meta($product_id, 'comments_count_new', true);

    $axes_avg       = ez_product_rating_resolve_axis_averages_for_display($product_id);
    $decor          = $axes_avg[1094];
    $moaama         = $axes_avg[1095];
    $tazegi         = $axes_avg[1098];
    $act            = $axes_avg[1096];
    $barkhord       = $axes_avg[1097];

    $comments_per_page  = 10;
    $total_pages        = ($comments_count > 0) ? ceil($comments_count / $comments_per_page) : 1;

    $args = array(
        'post_type' => 'product',
        'post_id'   => $product_id,
        'status'    => 'approve',
        'number'    => $comments_per_page,
        'parent'    => 0,
    );
    $comments_query = new WP_Comment_Query;
    $comments = $comments_query->query($args);

    if ($comments) {
        foreach ($comments as $comment) {
            $comment_id = $comment->comment_ID;

            $replies_args = array(
                'parent'    => $comment_id,
                'status'    => 'approve',
                'type'      => 'comment',
            );

            if (ctype_digit($comment->comment_author))
                $author_title = str_replace(substr($comment->comment_author, 3, 5), "×××××", $comment->comment_author);

            $comment_rating = get_comment_meta($comment_id, 'comment_rating', true);

            $comment_items[] = [
                'id'            => (int)$comment_id,
                'author_title'  => $author_title,
                'author_image'  => get_user_meta($comment->user_id, 'user_avatar', true) ?: 'http://escapezoom.ir/wp-content/uploads/2024/04/male_avatar_level_1.png',
                'author_level'  => 1,
                'content'       => $comment->comment_content,
                'date'          => strtotime($comment->comment_date),
                'reply'         => (get_comments($replies_args)[0])->comment_content,
                'votes_count'   => ((int)get_comment_meta($comment_id, 'cld_like_count', true) - (int)get_comment_meta($comment_id, 'cld_dislike_count', true)),
                'rating_items'  => $comment_rating ? array_map(fn($value) => $value / 20, get_comment_meta($comment_id, 'comment_rating', true)) : 0,
                'user_feeling'  => round(get_comment_meta($comment_id, "rating", true)),
            ];
        }
    }

    /**************************************/
    // تعداد بلیط های فروخته شده

    //    $query = $wpdb->prepare("
    //        SELECT SUM(CASE WHEN item_meta.meta_key = '_qty' THEN item_meta.meta_value END) AS total_quantity  
    //        FROM wp_posts AS posts   
    //        INNER JOIN wp_woocommerce_order_items AS order_items ON posts.ID = order_items.order_id   
    //        INNER JOIN wp_woocommerce_order_itemmeta AS item_meta ON order_items.order_item_id = item_meta.order_item_id  
    //        WHERE posts.post_type = 'shop_order'   
    //              AND posts.post_status = 'wc-walletx'  -- Filter for specific post_status  
    //              AND item_meta.meta_key IN ('_product_id', '_qty')  
    //              AND item_meta.order_item_id IN (  
    //                  SELECT order_item_id   
    //                  FROM wp_woocommerce_order_itemmeta   
    //                  WHERE meta_key = '_product_id'   
    //                  AND meta_value = %d
    //              )
    //    ", $product_id);
    //
    //    $tickets_sold = (int)($wpdb->get_col($query))[0];
    //
    //    $increments = [5, 10, 20, 50, 100, 300, 500, 1000, 1500, 2000, 2500, 3000, 5000];
    //    $nearest_increment = 0;
    //    foreach ($increments as $increment)
    //        if ($tickets_sold >= $increment)
    //            $nearest_increment = $increment;
    //
    //    $tickets_sold = $nearest_increment . '+';

    /**************************************/
    // امکانات و ویژگی های سرگرمی

    $number_min = !empty($numbers[0]) ? (int)min($numbers[0]) : 0;
    $number_max = !empty($numbers[0]) ? (int)max($numbers[0]) : 0;

    $options = get_post_meta($product_id, 'product_options', true);

    if ($product_type == 'اتاق فرار')
        $properties = [
            [
                'id'    => 'genre',
                'value' => $genres
            ],
            [
                'id'    => 'capacity',
                'value' => $number_min . ' تا ' . $number_max . ' کاربر ',
            ],
            [
                'id'    => 'duration',
                'value' => (int)get_post_meta($product_id, "room_duration", true)
            ],
            [
                'id'    => 'age',
                'value' => (int)get_post_meta($product_id, "room_age_limit", true)
            ],
            [
                'id'    => 'tickets_sold',
                'value' => $tickets_sold
            ],
            [
                'id'    => 'level',
                'value' => (int)get_post_meta($product_id, "room_level", true)
            ],
        ];

    elseif ($product_type == 'سینما ترس')
        $properties = [
            [
                'id'    => 'display_type',
                'value' => get_post_meta($product_id, "display_type", true)
            ],
            [
                'id'    => 'capacity',
                'value' => $number_min . ' تا ' . $number_max . ' کاربر ',
            ],
            [
                'id'    => 'duration',
                'value' => (int)get_post_meta($product_id, "room_duration", true)
            ],
            [
                'id'    => 'chair_type',
                'value' => get_post_meta($product_id, "chair_type", true)
            ],
            [
                'id'    => 'age',
                'value' => (int)get_post_meta($product_id, "room_age_limit", true)
            ],
            [
                'id'    => 'tickets_sold',
                'value' => $tickets_sold
            ],
        ];

    elseif ($product_type == 'لیزرتگ')
        $properties = [
            [
                'id'    => 'capacity',
                'value' => $number_min . ' تا ' . $number_max . 'کاربر',
            ],
            [
                'id'    => 'duration',
                'value' => (int)get_post_meta($product_id, "room_duration", true)
            ],
            [
                'id'    => 'age',
                'value' => (int)get_post_meta($product_id, "room_age_limit", true)
            ],
            [
                'id'    => 'tickets_sold',
                'value' => $tickets_sold
            ],
        ];

    elseif ($product_type == 'اتاق خشم')
        $properties = [
            [
                'id'    => 'capacity',
                'value' => $number_min . ' تا ' . $number_max . 'کاربر',
            ],
            [
                'id'    => 'duration',
                'value' => (int)get_post_meta($product_id, "room_duration", true)
            ],
            [
                'id'    => 'age',
                'value' => (int)get_post_meta($product_id, "room_age_limit", true)
            ],
            [
                'id'    => 'tickets_sold',
                'value' => $tickets_sold
            ],
            [
                'id'    => 'safety',
                'value' => (int)get_post_meta($product_id, "safety", true)
            ],
        ];

    /**************************************/
    // تعداد

    preg_match_all('/\d+/', get_field("room_tedad", $product_id), $numbers); // get numbers from string

    /**************************************/

    $data = [
        'product_id'        => $product_id,
        'type'              => get_product_type_equivalent($product_type),
        'title'             => $product->get_title(),
        'price'             => !empty(get_post_meta($product_id, 'min_price', true)) ? (int)get_post_meta($product_id, 'min_price', true) : (int)get_field("price_asli", $product_id),
        'ads'               => get_field("special_room", $product_id) ? true : false,
        'image'             => wp_get_attachment_url(get_post_thumbnail_id($product_id)),
        'age'               => (int)get_post_meta($product_id, "room_age_limit", true),
        'tickets_sold'      => $tickets_sold,
        'level'             => (int)get_field("room_level", $product_id),
        'duration'          => (int)get_post_meta($product_id, "room_duration", true),
        'active'            => get_post_meta($product_id, 'product_state', true) == 'active' ? 1 : 0,
        'city_id'           => $city_id,
        'city_name'         => $city_name,
        'hood_name'         => get_field("room_loc", $product_id),
        'nearest_subway'    => 'میدان کتاب',
        'nearest_brt'       => 'شهید دادمان',
        'genres'            => $genres,
        'tags'              => $tags,
        'number_min'        => !empty($numbers[0]) ? (int)min($numbers[0]) : 0,
        'number_max'        => !empty($numbers[0]) ? (int)max($numbers[0]) : 0,
        'count_down'        => null,
        'brand'             => [
            'title' => $brand_data->name,
            'image' => wp_get_attachment_url(get_term_meta($brand_data->term_id, 'thumbnail_id', true)),
            'url'   => trim_home_url(get_term_link($brand_data->term_id)),
        ],
        'properties'        => $properties,
        'options'           => $options,
        'introduction_text' => get_post_meta($product_id, 'product_introduction_text', true),
        'scenario'          => get_post_meta($product_id, 'product_scenario', true),
        'rules'             => get_post_meta($product_id, 'product_rules', true),
        'trailer_video'     => get_field('room_video_embed', $product_id),
        'introduction_video' => get_post_meta($product_id, 'product_introduction_video', true),
        'criticism'         => $product_obj->post_excerpt,
        'address_info'      => [
            'address'   => get_field('room_address', $product_id),
            'lat'       => get_field('room_lat', $product_id),
            'long'      => get_field('room_long', $product_id),
        ],
        'gallery'           => $gallery,
        'comments'          => [
            'tabs'  => [
                'type'  => 'product_comment',
                'title' => '',
                'key'   => 'sort_type',
                'items' => [
                    [
                        'title' => 'جدیدترین',
                        'id'    => 'recent',
                    ],
                    [
                        'title' => 'محبوب ترین',
                        'id'    => 'best',
                    ],
                    [
                        'title' => 'قدیمی ترین',
                        'id'    => 'oldest',
                    ],
                ],
            ],
            'rate'              => ez_product_rating_format_overall_display( ez_product_rating_overall_from_axes( $axes_avg, $product_type ) ),
            'comments_count'    => (int)$comments_count,
            'rating_items'      => [
                1 => number_format($decor, 2, '.', ''),
                2 => number_format($moaama, 2, '.', ''),
                3 => number_format($tazegi, 2, '.', ''),
                4 => number_format($act, 2, '.', ''),
                5 => number_format($barkhord, 2, '.', ''),
            ],
            'items'             => $comment_items,
            'total_pages'       => $total_pages,
        ],
        'breadcrumb'        => [
            [
                'title' => 'صفحه اصلی',
                'url'   => '/',
            ],
            [
                'title' => $product_type,
                'url'   => trim_home_url($product_parent_cat_url),
            ],
            [
                'title' => $city_name,
                'url'   => trim_home_url($product_cat_url),
            ],
            [
                'title' => $product->post_title,
                'url'   => '',
            ],
        ],
        'brand_products'    => $brand_products,
    ];

    wp_send_json_success($data);
}
//**********************************************************************************************************/
function product_reservation_api($request)
{
    global $wpdb;

    //    $user_id = get_user_id_by_token( ez_authorization(false) );

    $params = $request->get_params();
    $param  = $params['param'];

    if (is_numeric($param)) {
        $product_obj = get_post($param);
        $product_id = (int)$param;
    } else {
        $product_obj = get_page_by_path($param, OBJECT, 'product');
        $product_id = $product_obj->ID;
    }

    if (!$product_obj)
        wp_send_json_error(null, 404);

    $brand_data = get_the_terms($product_id, 'product_brand')[0];

    /**************************************/
    // پروداکت تایپ

    $terms = get_the_terms($product_id, 'product_cat');
    if (count($terms) > 1) {

        foreach ($terms as $term) {
            if ($term->parent == 0) {
                $product_type = $term->name;
            } else {
                $city_name  = $term->name;
            }
        }
    } else {
        $product_type   = get_term($terms[0]->parent)->name;
        $city_name      = $terms[0]->name;
    }

    /**************************************/
    // کامنت ها

    $comments_count = get_post_meta($product_id, 'comments_count_new', true);

    $axes_avg       = ez_product_rating_resolve_axis_averages_for_display($product_id);

    /**************************************/

    $data = [
        'product_id'    => $product_id,
        'type'          => get_product_type_equivalent($product_type),
        'title'         => get_the_title($product_id),
        'image'         => wp_get_attachment_url(get_post_thumbnail_id($product_id)),
        'city_name'     => $city_name,
        'hood_name'     => get_field("room_loc", $product_id),
        'brand'         => [
            'title' => $brand_data->name,
            'image' => wp_get_attachment_url(get_term_meta($brand_data->term_id, 'thumbnail_id', true)),
            'url'   => trim_home_url(get_term_link($brand_data->term_id)),
        ],
        'rate'          => ez_product_rating_format_overall_display( ez_product_rating_overall_from_axes( $axes_avg, $product_type ) ),
        'votes_count'   => (int)$comments_count,
    ];

    wp_send_json_success($data);
}
//**********************************************************************************************************/
function product_add_comment_api($request)
{

    $user_id = get_user_id_by_token(ez_authorization(false));

    $params = $request->get_params();
    $post_id        = $params['product_id'];
    $comment_id     = $params['comment_id'];
    $content        = $params['content'];
    $author_name    = $params['author_name'];
    $author_mail    = $params['author_mail'];

    if (!isset($post_id) || empty($post_id))
        wp_send_json_error(array('error' => 'شماره محصول مشخص نیست.'), 400);

    if (!isset($content) || empty($content))
        wp_send_json_error(array('error' => 'پاسخ شما مشخص نیست.'), 400);

    if (!get_post($post_id))
        wp_send_json_error(null, 404);

    $comment_data = array(
        'comment_post_ID'       => $post_id,
        'comment_author'        => $author_name,
        'comment_author_email'  => $author_mail,
        'comment_content'       => $content,
        'comment_approved'      => 1,
    );

    if ($comment_id) {
        $parent_comment = get_comment($comment_id);

        if (!$parent_comment)
            wp_send_json_error(null, 404);

        $comment_data['comment_parent'] = $comment_id;
    }

    $comment_id = wp_insert_comment($comment_data);

    if (is_wp_error($comment_id))
        wp_send_json_error(null, 403);

    wp_send_json_success('نظر شما ثبت شد! پس از تایید نمایش داده می شود.');
}
//**********************************************************************************************************/
function product_add_comment_feedback_api($request)
{

    $user_id = get_user_id_by_token(ez_authorization(false));

    $params = $request->get_params();
    $comment_id     = $params['comment_id'];
    $type           = $params['type'];

    if (!isset($comment_id) || empty($comment_id))
        wp_send_json_error(array('error' => 'شماره کامنت مشخص نیست.'), 400);

    //    if ( !get_post($post_id) )
    //        wp_send_json_error(null, 404);

    if (!$user_id)
        wp_send_json_error(array('error' => 'جهت رای دادن به این کامنت ابتدا وارد شوید.'), 400);

    $feedback_users = get_comment_meta($comment_id, 'cld_users', true);
    $feedback_users = empty($feedback_users) ? [] : $feedback_users;

    if (in_array($user_id, $feedback_users)) { // already user reacted on this comment

        $feedback_users_info = get_comment_meta($comment_id, 'cld_users_info', true);

        $prev_type = $feedback_users_info[$user_id];

        if ($prev_type == $type) { // undo

            if ($type == 'like') {
                $like_count = get_comment_meta($comment_id, 'cld_like_count', true);

                update_comment_meta($comment_id, 'cld_like_count', --$like_count);

                $res = [$type => $like_count];
            } else {
                $dislike_count = get_comment_meta($comment_id, 'cld_dislike_count', true);

                update_comment_meta($comment_id, 'cld_dislike_count', --$dislike_count);

                $res = [$type => $dislike_count];
            }

            unset($feedback_users_info[$user_id]);

            update_comment_meta($comment_id, 'cld_users', array_diff($feedback_users, [$user_id]));
            update_comment_meta($comment_id, 'cld_users_info', $feedback_users_info);
        } else { // undo + do new action

            if ($type == 'like') {
                $like_count         = get_comment_meta($comment_id, 'cld_like_count', true);
                $cld_dislike_count  = get_comment_meta($comment_id, 'cld_dislike_count', true);

                update_comment_meta($comment_id, 'cld_like_count', ++$like_count);
                update_comment_meta($comment_id, 'cld_dislike_count', --$cld_dislike_count);

                $res = [$type => $like_count, $prev_type => $cld_dislike_count];
            } else {
                $like_count         = get_comment_meta($comment_id, 'cld_like_count', true);
                $cld_dislike_count  = get_comment_meta($comment_id, 'cld_dislike_count', true);

                update_comment_meta($comment_id, 'cld_like_count', --$like_count);
                update_comment_meta($comment_id, 'cld_dislike_count', ++$cld_dislike_count);

                $res = [$type => $cld_dislike_count, $prev_type => $like_count];
            }

            unset($feedback_users_info[$user_id]);
            $feedback_users_info[$user_id]  = $type;

            update_comment_meta($comment_id, 'cld_users_info', $feedback_users_info);
        }
    } else { // user reactions for first time

        if ($type == 'like') {
            $like_count = get_comment_meta($comment_id, 'cld_like_count', true);

            if (empty($like_count))
                $like_count = 0;

            update_comment_meta($comment_id, 'cld_like_count', ++$like_count);

            $res = [$type => $like_count];
        } else {
            $dislike_count = get_comment_meta($comment_id, 'cld_dislike_count', true);

            if (empty($dislike_count))
                $dislike_count = 0;

            update_comment_meta($comment_id, 'cld_dislike_count', ++$dislike_count);

            $res = [$type => $dislike_count];
        }

        $feedback_users_info = get_comment_meta($comment_id, 'cld_users_info', true);
        $feedback_users_info = (empty($feedback_users_info)) ? array() : $feedback_users_info;

        $feedback_users[]               = $user_id;
        $feedback_users_info[$user_id]  = $type;

        update_comment_meta($comment_id, 'cld_users', $feedback_users);
        update_comment_meta($comment_id, 'cld_users_info', $feedback_users_info);
    }

    wp_send_json_success($res);
}
//**********************************************************************************************************/
function product_get_comments_api($request)
{

    $user_id = get_user_id_by_token(ez_authorization(false));

    $params = $request->get_params();
    $param      = $params['param'];
    $page       = $params['page'];
    $sort_type  = $params['sort_type'];

    if (is_numeric($param)) {
        $product_obj = get_post($param);
        $product_id = (int)$param;
    } else {
        $product_obj = get_page_by_path($param, OBJECT, 'product');
        $product_id = $product_obj->ID;
    }

    if (!$product_obj)
        wp_send_json_error(null, 404);

    $comments_count = get_post_meta($product_id, 'comments_count_new', true);

    $comments_per_page = 10;

    $args = [
        'post_type' => 'product',
        'post_id'   => $product_id,
        'status'    => 'approve',
        'number'    => $comments_per_page,
        'parent'    => 0,
        'paged'     => $page,
        'order'     => 'DESC',
    ];

    if ($sort_type == 'best') {
        $args['meta_query'] = array(
            array(
                'key'     => 'cld_like_count',
                'value'   => 5,
                'compare' => '>',
                'type'    => 'NUMERIC'
            ),
        );
    } elseif ($sort_type == 'oldest') {
        $args['orderby'] = 'date';
        $args['order'] = 'ASC';
    } else
        $args['orderby'] = 'date';

    $comments_query = new WP_Comment_Query;
    $comments = $comments_query->query($args);

    if ($comments) {
        foreach ($comments as $comment) {
            $comment_id = $comment->comment_ID;

            $replies_args = array(
                'parent'    => $comment_id,
                'status'    => 'approve',
                'type'      => 'comment',
            );

            if (ctype_digit($comment->comment_author))
                $author_title = str_replace(substr($comment->comment_author, 3, 5), "×××××", $comment->comment_author);

            $comment_rating = get_comment_meta($comment_id, 'comment_rating', true);

            $comment_items[] = [
                'id'            => (int)$comment_id,
                'author_title'  => $author_title,
                'author_image'  => get_user_meta($comment->user_id, 'user_avatar', true) ?: 'http://escapezoom.ir/wp-content/uploads/2024/04/male_avatar_level_1.png',
                'author_level'  => 1,
                'content'       => $comment->comment_content,
                'date'          => strtotime($comment->comment_date),
                'reply'         => (get_comments($replies_args)[0])->comment_content,
                'votes_count'   => ((int)get_comment_meta($comment_id, 'cld_like_count', true) - (int)get_comment_meta($comment_id, 'cld_dislike_count', true)),
                'rating_items'  => $comment_rating ? array_map(fn($value) => $value / 20, get_comment_meta($comment_id, 'comment_rating', true)) : 0,
                'user_feeling'  => round(get_comment_meta($comment_id, "rating", true)),
            ];
        }
    }

    $data = [
        'items'         => $comment_items,
        'pagination'    => [
            'current_page'  => (int)$page,
            'total_pages'   => ($comments_count > 0) ? ceil($comments_count / $comments_per_page) : 1,
        ],
    ];

    wp_send_json_success($data);
}
//**********************************************************************************************************/
function product_city_page_api($request)
{ // صفحه شهر مثلا صفحه تهران، صفحه کرج و ...
    global $wpdb;

    $user_id = get_user_id_by_token(ez_authorization(false));

    $params = $request->get_params();
    $param  = $params['param'];

    if (is_numeric($param))
        $city_id = (int)$param;
    else
        $city_id = get_page_by_path('ir/' . $param, OBJECT, 'page')->ID;

    echo $city_id;

    if (!$city_id)
        wp_send_json_error(null, 404);

    if (!get_post_meta($city_id, 'assign_as_city_page', true)) // این صفحه به عنوان یک شهر ایجاده شده است؟
        wp_send_json_error(null, 404);

    $city_name          = get_the_title($city_id);
    $city_categories    = get_post_meta($city_id, 'city_page_product_categories', true);

    if (!$city_categories) // برای این صفحه حتما باید دست کم یک دسته بندی لحاظ شده باشد.
        wp_send_json_error(null, 404);

    $product_types = [
        'escaperoom'    => 'اتاق فرار',
        'cinema'        => 'سینما ترس',
        'lasertag'      => 'لیزرتگ',
        'rageroom'      => 'اتاق خشم',
    ];

    foreach ($city_categories as $city_category) // شناسایی تایپ کتگوری های متصل به این شهر
        $city_type_cats_id[array_search(get_parent_category_name_by_child_id($city_category), $product_types)] = (int)$city_category;

    /*===============================================================*/
    // اسلایدشو + متن عکس

    $data[] = [
        'type'  => 'slideshow_text_img',
        'title' => '',
        'data'  => [
            'slideshow' => [
                'slide_time'    => 5,
                'items'         => [
                    [
                        'image' => 'http://escapezoom.ir/wp-content/uploads/2024/12/city_tehran_back.jpg',
                        'url'   => '/',
                    ],
                    [
                        'image' => 'http://escapezoom.ir/wp-content/uploads/2024/12/city_tehran_back.jpg',
                        'url'   => '/',
                    ],
                    [
                        'image' => 'http://escapezoom.ir/wp-content/uploads/2024/12/city_tehran_back.jpg',
                        'url'   => '/',
                    ],
                ],
            ],
            'text_img' => [
                'img'           => 'http://escapezoom.ir/wp-content/uploads/2024/12/city_tehran_back2.png',
                'title'         => 'سرگرمی های تهران',
                'description'   => 'باشگاههای لیزرتگ استان تهران شامل تهران پردیس شهریار اندیشه، رباط کریم فیروزکوه',
            ],
        ]
    ];

    /*===============================================================*/
    // اتاق فرار

    $params = [
        'tag' => -1,
    ];
    $args = [
        'source'    => 'city_page_product_' . $city_type_cats_id['escaperoom'],
        'params'    => $params,
    ];
    $products = json_decode(ez_webservice(array('type' => 'sort_products_get', 'data' => $args)));

    $data[] = [
        'source' => 'city_page_product_' . $city_type_cats_id['escaperoom'],
        'type'  => 'products_slider',
        'title' => 'اتاق فرار های ' . '<b>' . $city_name . '</b>',
        'icon'  => '',
        'url'   => trim_home_url(get_term_link($city_type_cats_id['escaperoom'])),
        'data'  => [
            'tabs'  => [
                'type'  => 'order',
                'title' => '',
                'key'   => 'sort_type',
                'items' => [
                    [
                        'title' => 'همه',
                        'id'    => -1,
                    ],
                    [
                        'title' => 'ترسناک',
                        'id'    => 124,
                    ],
                    [
                        'title' => 'هیجانی',
                        'id'    => 124,
                    ],
                    [
                        'title' => 'معمامحور',
                        'id'    => 124,
                    ],
                    [
                        'title' => 'علمی تخیلی',
                        'id'    => 342,
                    ],
                ],
            ],
            'items' => $products,
        ]
    ];

    /*===============================================================*/
    // تخفیف ویژه

    $args = [
        'source' => 'city_page_discounts_event_' . implode(',', $city_type_cats_id),
    ];
    $products = json_decode(ez_webservice(array('type' => 'sort_products_get', 'data' => $args)));

    $data[] = [
        'source' => 'city_page_discounts_event_' . implode(',', $city_type_cats_id),
        'type'  => 'event',
        'title' => '<b>تخفیف های ویژه</b> و دارای سانس',
        'icon'  => ez_theme_asset_uri('images/genres/Takhfif.svg'),
        'url'   => '',
        'data'  => [
            'color' => '#eee',
            'items' => $products,
            'tabs'  => [
                'type'  => 'schedule',
                'title' => '',
                'key'   => 'schedule',
                'items' => [
                    [
                        'title' => 'همه',
                        'min'   => -1,
                        'max'   => -1,
                    ],
                    [
                        'title' => 'فقط امروز',
                        'min'   => 'dynamic',
                        'max'   => 'dynamic',
                    ],
                    [
                        'title' => 'فقط فردا',
                        'min'   => 'dynamic',
                        'max'   => 'dynamic',
                    ],
                    [
                        'title' => 'فقط پس فردا',
                        'min'   => 'dynamic',
                        'max'   => 'dynamic',
                    ],
                ],
            ],
        ]
    ];

    /*===============================================================*/
    // سایر سرگرمی ها

    foreach ($city_type_cats_id as $cat_type => $city_type_cat_id) :

        if ($cat_type == 'escaperoom') // اتاق فرار بالاتر ایجاده شده است.
            continue;

        $params = [
            'tag' => -1,
        ];
        $args = [
            'source'    => 'city_page_product_' . $city_type_cat_id,
            'params'    => $params,
        ];
        $products = json_decode(ez_webservice(array('type' => 'sort_products_get', 'data' => $args)));

        if (is_null($products->products)) // اگه یک کتگوری هیچ محصولی نداشت به فرانت نفرست
            continue;

        $data[] = [
            'source' => 'city_page_product_' . $city_type_cat_id,
            'type'  => 'products_slider',
            'title' => get_product_type_equivalent($cat_type) . ' های ' . '<b>' . $city_name . '</b>',
            'icon'  => '',
            'url'   => trim_home_url(get_term_link($city_type_cat_id)),
            'data'  => [
                'tabs'  => [],
                'items' => $products,
            ]
        ];

    endforeach;

    /*===============================================================*/
    // کالکشن ها

    $items_per_page = 10;

    $collections = $wpdb->get_results(
        $wpdb->prepare(
            'SELECT * FROM collections WHERE active = %d ORDER BY likes_count DESC LIMIT %d',
            1,
            (int) $items_per_page
        )
    );
    foreach ($collections as $collection) {

        $images = [];
        foreach (unserialize($collection->items) as $product_id)
            $images[] = wp_get_attachment_url(get_post_thumbnail_id($product_id));

        $collection_items[] =  [
            'title'         => $collection->title,
            'user_title'    => 'فاطمه خداپرست',
            'user_level'    => 2,
            'likes_count'   => (int)$collection->likes_count,
            'url'           => "/profile/" . (int)$collection->user_id,
            'count'         => count(unserialize($collection->items)),
            'items'         => $images,
        ];
    }

    $data[] = [
        'type'  => 'collections',
        'title' => 'کالکشن های محبوب کاربران',
        'icon'  => '',
        'url'   => '/collections/',
        'data'  => [
            'items' => $collection_items,
        ]
    ];

    /*===============================================================*/
    // محبوب ترین برندها

    $brands = get_terms([
        'taxonomy'      => 'product_brand',
        'hide_empty'    => false,
        'number'        => 500,
    ]);

    shuffle($brands);
    $brands = array_slice($brands, 0, 15);

    foreach ($brands as $brand) {
        $brand_id = $brand->term_id;

        $brand_img_id = get_term_meta($brand_id, 'thumbnail_id', true);
        if ($brand_img_id > 0)
            $image = wp_get_attachment_image_src($brand_img_id, 'full')[0];

        $brand_items[] = [
            'id'    => $brand_id,
            'title' => $brand->name,
            'image' => $image,
            'url'   => trim_home_url(get_term_link($brand)),
            'count' => 5,
        ];
    }

    $data[] = [
        'type'  => 'owners',
        'title' => 'میزبان های اسکیپ زوم',
        'icon'  => '',
        'url'   => '/brands/',
        'data'  => [
            'slide_time'    => 5,
            'items'         => $brand_items,
        ]
    ];

    /*===============================================================*/
    // کامنت ها

    $comments_per_page = 10;
    $args = array(
        'post_type'   => 'product',
        'status'      => 'approve',
        'number'      => $comments_per_page,
        'orderby'     => 'comment_date',
        'order'       => 'DESC',
        'parent'      => 0,
    );
    $comments_query = new WP_Comment_Query;
    $comments = $comments_query->query($args);

    $comment_items = [];

    if ($comments) {
        foreach ($comments as $comment) {
            $comment_id = $comment->comment_ID;

            $replies_args = array(
                'parent' => $comment_id,
                'status' => 'approve',
                'type'   => 'comment',
            );

            $author_title = $comment->comment_author;

            if (ctype_digit($comment->comment_author))
                $author_title = str_replace(substr($comment->comment_author, 3, 5), "×××××", $comment->comment_author);

            $comment_rating = get_comment_meta($comment_id, 'comment_rating', true);

            $comment_items[] = [
                'id'            => (int)$comment_id,
                'author'        => $author_title,
                'author_image'  => get_user_meta($comment->user_id, 'user_avatar', true) ?: 'http://escapezoom.ir/wp-content/uploads/2024/04/male_avatar_level_1.png',
                'author_level'  => '',
                'product_title' => get_the_title($comment->comment_post_ID),
                'product_url'   => trim_home_url(get_permalink($comment->comment_post_ID)),
                'content'       => $comment->comment_content,
                'date'          => strtotime($comment->comment_date),
                'reply'         => isset(get_comments($replies_args)[0]) ? get_comments($replies_args)[0]->comment_content : null,
                'votes_count'   => ((int)get_comment_meta($comment_id, 'cld_like_count', true) - (int)get_comment_meta($comment_id, 'cld_dislike_count', true)),
                'rating_items'  => $comment_rating ? array_map(fn($value) => $value / 20, get_comment_meta($comment_id, 'comment_rating', true)) : 0,
            ];
        }
    }
    $data[] = [
        'type'  => 'comments',
        'title' => '',
        'icon'  => '',
        'url'   => '',
        'data'  => [
            'slide_time'    => 5,
            'items'         => $comment_items
        ]
    ];

    wp_send_json_success($data);
}
//**********************************************************************************************************/
function product_type_page_api($request)
{ // صفحه سرگرمی مثلا صفحه اتاق فرار، صفحه سینماترس و ...
    global $wpdb;

    $user_id = get_user_id_by_token(ez_authorization(false));

    $params = $request->get_params();
    $param  = $params['param'];

    $type_term = get_term_by('slug', $param, 'product_cat');

    if (is_numeric($param))
        $type_id = (int)$param;
    else
        $type_id = $type_term->term_id;

    if (!$type_id)
        wp_send_json_error(null, 404);

    $product_type       = $type_term->name;
    $product_type_equ   = get_product_type_equivalent($product_type);

    $is_escaperoom = false;
    if ($product_type == 'اتاق فرار')
        $is_escaperoom = true;

    /*===============================================================*/
    // ویدئو + متن

    $data[] = [
        'type'  => 'video_text',
        'title' => '',
        'data'  => [
            'title' => 'اتاق فرار EscapeRoom',
            'text'  => 'لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ، و با استفاده از طراحان گرافیک است، چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است، و برای شرایط فعلی تکنولوژی مورد نیاز، و کاربردهای متنوع با هدف بهبود ابزارهای کاربردی می باشد، کتابهای زیادی در شصت و سه درصد گذشته حال و آینده، شناخت فراوان جامعه و متخصصان را می طلبد، تا با نرم افزارها شناخت بیشتری را برای طراحان رایانه ای علی الخصوص طراحان خلاقی، و فرهنگ پیشرو در زبان فارسی ایجاد کرد، در این صورت می توان امید داشت که تمام و دشواری موجود در ارائه راهکارها، و شرایط سخت تایپ به پایان رسد و زمان مورد نیاز شامل حروفچینی دستاوردهای اصلی، و جوابگوی سوالات پیوسته اهل دنیای موجود طراحی اساسا مورد استفاده قرار گیرد.',
            'video' => '<style>.r1_iframe_embed {position: relative; overflow: hidden; width: 100%; height: auto; padding-top: 56.25%; } .r1_iframe_embed iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0; }</style><div class="r1_iframe_embed"><iframe src="https://player.arvancloud.ir/index.html?config=https://ez.arvanvod.ir/2MP5ZV5a1r/0WMB6w3q2E/origin_config.json&skin=shaka" style="border:0 #ffffff none;" name="cirota2.mp4" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowFullScreen="true" webkitallowfullscreen="true" mozallowfullscreen="true"></iframe></div>',
        ]
    ];

    /*===============================================================*/
    // اتاق فرارهای ایران

    if ($is_escaperoom) :

        $args = [
            'source'    => 'type_page_cat_' . $product_type_equ . '_-1',
            'params'    => $params,
        ];
        $products = json_decode(ez_webservice(array('type' => 'sort_products_get', 'data' => $args)));

        $data[] = [
            'source' => 'type_page_cat_' . $product_type_equ . '_-1',
            'type'  => 'products_slider',
            'title' => 'اتاق فرار های <b>ایران</b>',
            'icon'  => '',
            'url'   => '',
            'data'  => [
                'tabs'  => [
                    'type'  => 'order',
                    'title' => '',
                    'key'   => 'sort_type',
                    'items' => [
                        [
                            'title' => 'همه',
                            'id'    => -1,
                        ],
                        [
                            'title' => 'محبوب ترین',
                            'id'    => 'popular',
                        ],
                        [
                            'title' => 'پرفروش ترین',
                            'id'    => 'topsale',
                        ],
                        [
                            'title' => 'جدیدترین',
                            'id'    => 'recent',
                        ],
                    ],
                ],
                'items' => $products,
            ]
        ];

    endif;

    /*===============================================================*/
    // اتاق فرارهای تهران

    if ($is_escaperoom) :

        $args = [
            'source'    => 'type_page_cat_' . $product_type_equ . '_15',
            'params'    => $params,
        ];
        $products = json_decode(ez_webservice(array('type' => 'sort_products_get', 'data' => $args)));

        $data[] = [
            'source' => 'type_page_cat_' . $product_type_equ . '_15',
            'type'  => 'products_slider',
            'title' => 'اتاق فرار های <b>ایران</b> و دارای سانس',
            'icon'  => '',
            'url'   => '',
            'data'  => [
                'tabs'  => [
                    [
                        'type'  => 'city_id',
                        'title' => 'شهر مورد نظر',
                        'key'   => 'city_id',
                        'items' => [
                            [
                                'title' => 'تهران',
                                'value' => 15,
                            ],
                            [
                                'title' => 'کرج',
                                'value' => 162,
                            ],
                            [
                                'title' => 'اصفهان',
                                'value' => 122,
                            ],
                            [
                                'title' => 'مشهد',
                                'value' => 121,
                            ],
                            [
                                'title' => 'کرمانشاه',
                                'value' => 293,
                            ],
                            [
                                'title' => 'قزوین',
                                'value' => 270,
                            ],
                            [
                                'title' => 'کاشان',
                                'value' => 304,
                            ],
                        ],
                    ],
                    [
                        'type'  => 'tag',
                        'title' => 'سبک بازی',
                        'key'   => 'tag',
                        'items' => [
                            [
                                'title' => 'ترسناک',
                                'value' => 124,
                            ],
                            [
                                'title' => 'اکشن',
                                'value' => 346,
                            ],
                            [
                                'title' => 'درام',
                                'value' => 342,
                            ],
                            [
                                'title' => 'دلهره آور',
                                'value' => 126,
                            ],
                            [
                                'title' => 'غیرترسناک',
                                'value' => 125,
                            ],
                            [
                                'title' => 'هیجانی',
                                'value' => 178,
                            ],
                            [
                                'title' => 'جنایی',
                                'value' => 127,
                            ],
                        ],
                    ],
                    [
                        'type'  => 'order',
                        'title' => 'براساس',
                        'key'   => 'sort_type',
                        'items' => [
                            [
                                'title' => 'همه',
                                'id'    => -1,
                            ],
                            [
                                'title' => 'محبوب ترین',
                                'id'    => 'popular',
                            ],
                            [
                                'title' => 'پرفروش ترین',
                                'id'    => 'topsale',
                            ],
                            [
                                'title' => 'جدیدترین',
                                'id'    => 'recent',
                            ],
                        ],
                    ],
                ],
                'items' => $products,
            ]
        ];

    endif;

    /*===============================================================*/
    // سرگرمی های مختلف در شهرهای مختلف (سینماترس تهران، اتاق خشم تهران ....)

    if (!$is_escaperoom) :

        $type_city_list = [
            'lasertag'  => [1149, 1158],
            'rageroom'  => [1186, 1074],
            'cinema'    => [913, 1009],
        ];

        foreach ($type_city_list[$product_type_equ] as $type_city_item) {

            $args = [
                'source'    => 'type_page_cat_' . $product_type_equ . '_' . $type_city_item,
                'params'    => $params,
            ];
            $products = json_decode(ez_webservice(array('type' => 'sort_products_get', 'data' => $args)));

            $data[] = [
                'source' => 'type_page_cat_' . $product_type_equ . '_' . $type_city_item,
                'type'  => 'products_slider',
                'title' => $product_type . ' های <b>' . get_term($type_city_item)->name . '</b>' . 'و دارای سانس',
                'icon'  => '',
                'url'   => '',
                'data'  => [
                    'tabs'  => [
                        'type'  => 'order',
                        'title' => '',
                        'key'   => 'sort_type',
                        'items' => [
                            [
                                'title' => 'همه',
                                'id'    => -1,
                            ],
                            [
                                'title' => 'محبوب ترین',
                                'id'    => 'popular',
                            ],
                            [
                                'title' => 'پرفروش ترین',
                                'id'    => 'topsale',
                            ],
                            [
                                'title' => 'جدیدترین',
                                'id'    => 'recent',
                            ],
                        ],
                    ],
                    'items' => $products,
                ]
            ];
        }

    endif;

    /*===============================================================*/
    // تخفیف ویژه برای سرگرمی جاری (مشترک)

    $args = [
        'source' => 'type_page_discounts_event_' . $product_type_equ,
    ];
    $products = json_decode(ez_webservice(array('type' => 'sort_products_get', 'data' => $args)));

    $data[] = [
        'source' => 'type_page_discounts_event_' .  $product_type_equ,
        'type'  => 'event',
        'title' => '<b>تخفیف های ویژه</b> و دارای سانس',
        'icon'  => ez_theme_asset_uri('images/genres/Takhfif.svg'),
        'url'   => '',
        'data'  => [
            'color' => '#eee',
            'items' => $products,
            'tabs'  => [
                'type'  => 'schedule',
                'title' => '',
                'key'   => 'schedule',
                'items' => [
                    [
                        'title' => 'همه',
                        'min'   => -1,
                        'max'   => -1,
                    ],
                    [
                        'title' => 'فقط امروز',
                        'min'   => 'dynamic',
                        'max'   => 'dynamic',
                    ],
                    [
                        'title' => 'فقط فردا',
                        'min'   => 'dynamic',
                        'max'   => 'dynamic',
                    ],
                    [
                        'title' => 'فقط پس فردا',
                        'min'   => 'dynamic',
                        'max'   => 'dynamic',
                    ],
                ],
            ],
        ]
    ];

    /*===============================================================*/
    // باکس شهرها برای سرگرمی های غیر اتاق فرار

    if (!$is_escaperoom) :

        $data[] = [
            'type'  => 'genres',
            'title' => '',
            'icon'  => '',
            'data'  => [
                'items' => [
                    [
                        'image'     => ez_theme_asset_uri('images/genres/action.svg'),
                        'title'     => 'اکشن',
                        'popular'   => true,
                        'url'       => '/type/%D8%A7%DA%A9%D8%B4%D9%86/',
                    ],
                    [
                        'image'     => ez_theme_asset_uri('images/genres/non-scary.svg'),
                        'title'     => 'غیرترسناک',
                        'popular'   => false,
                        'url'       => '/type/%D8%A7%D8%AA%D8%A7%D9%82-%D9%81%D8%B1%D8%A7%D8%B1-%D8%AA%D8%B1%D8%B3%D9%86%D8%A7%DA%A9/',
                    ],
                    [
                        'image'     => ez_theme_asset_uri('images/genres/scary.svg'),
                        'title'     => 'ترسناک',
                        'popular'   => true,
                        'url'       => '/type/%D8%A7%DA%A9%D8%B4%D9%86/',
                    ],
                    [
                        'image'     => ez_theme_asset_uri('images/genres/dram.svg'),
                        'title'     => 'درام',
                        'popular'   => false,
                        'url'       => '/type/%D8%A7%D8%AA%D8%A7%D9%82-%D9%81%D8%B1%D8%A7%D8%B1-%D8%AA%D8%B1%D8%B3%D9%86%D8%A7%DA%A9/',
                    ],
                    [
                        'image'     => ez_theme_asset_uri('images/genres/exciting.svg'),
                        'title'     => 'هیجانی',
                        'popular'   => false,
                        'url'       => '/type/%D8%A7%DA%A9%D8%B4%D9%86/',
                    ],
                ],
            ]
        ];

    endif;

    /*===============================================================*/
    // اتاق فرارهای ترسناک

    if ($is_escaperoom) :

        $args = [
            'source' => 'type_page_escaperoom_genre_horror',
        ];
        $products = json_decode(ez_webservice(array('type' => 'sort_products_get', 'data' => $args)));

        $data[] = [
            'source' => 'type_page_escaperoom_genre_horror',
            'type'  => 'products_slider',
            'title' => 'اتاق فرارهای ترسناک',
            'data'  => [
                'tabs'  => [
                    'type'  => 'order',
                    'title' => '',
                    'key'   => 'sort_type',
                    'items' => [
                        [
                            'title' => 'همه',
                            'id'    => -1,
                        ],
                        [
                            'title' => 'محبوب ها',
                            'id'    => 'popular',
                        ],
                        [
                            'title' => 'پرفروش ها',
                            'id'    => 'topsale',
                        ],
                        [
                            'title' => 'جدید ها',
                            'id'    => 'recent',
                        ],
                    ],
                ],
                'items' => $products,
            ]
        ];

    endif;

    /*===============================================================*/
    // اتاق فرارهای غیرترسناک

    if ($is_escaperoom) :

        $args = [
            'source' => 'type_page_escaperoom_genre_nonhorror',
        ];
        $products = json_decode(ez_webservice(array('type' => 'sort_products_get', 'data' => $args)));

        $data[] = [
            'source' => 'type_page_escaperoom_genre_horror',
            'type'  => 'products_slider',
            'title' => 'اتاق فرارهای غیرترسناک و هیجانی',
            'data'  => [
                'tabs'  => [
                    'type'  => 'order',
                    'title' => '',
                    'key'   => 'sort_type',
                    'items' => [
                        [
                            'title' => 'همه',
                            'id'    => -1,
                        ],
                        [
                            'title' => 'محبوب ها',
                            'id'    => 'popular',
                        ],
                        [
                            'title' => 'پرفروش ها',
                            'id'    => 'topsale',
                        ],
                        [
                            'title' => 'جدید ها',
                            'id'    => 'recent',
                        ],
                    ],
                ],
                'items' => $products,
            ]
        ];

    endif;

    /*===============================================================*/
    // ژانرهای اتاق فرار

    if ($is_escaperoom) :

        $data[] = [
            'type'  => 'genres',
            'title' => '',
            'icon'  => '',
            'data'  => [
                'items' => [
                    [
                        'image'     => ez_theme_asset_uri('images/genres/action.svg'),
                        'title'     => 'اکشن',
                        'popular'   => true,
                        'url'       => '/type/%D8%A7%DA%A9%D8%B4%D9%86/',
                    ],
                    [
                        'image'     => ez_theme_asset_uri('images/genres/non-scary.svg'),
                        'title'     => 'غیرترسناک',
                        'popular'   => false,
                        'url'       => '/type/%D8%A7%D8%AA%D8%A7%D9%82-%D9%81%D8%B1%D8%A7%D8%B1-%D8%AA%D8%B1%D8%B3%D9%86%D8%A7%DA%A9/',
                    ],
                    [
                        'image'     => ez_theme_asset_uri('images/genres/scary.svg'),
                        'title'     => 'ترسناک',
                        'popular'   => true,
                        'url'       => '/type/%D8%A7%DA%A9%D8%B4%D9%86/',
                    ],
                    [
                        'image'     => ez_theme_asset_uri('images/genres/dram.svg'),
                        'title'     => 'درام',
                        'popular'   => false,
                        'url'       => '/type/%D8%A7%D8%AA%D8%A7%D9%82-%D9%81%D8%B1%D8%A7%D8%B1-%D8%AA%D8%B1%D8%B3%D9%86%D8%A7%DA%A9/',
                    ],
                    [
                        'image'     => ez_theme_asset_uri('images/genres/exciting.svg'),
                        'title'     => 'هیجانی',
                        'popular'   => false,
                        'url'       => '/type/%D8%A7%DA%A9%D8%B4%D9%86/',
                    ],
                ],
            ]
        ];

    endif;

    /*===============================================================*/
    // زوم کلاب

    $params = [
        'city_id'       => -1,
        'monopoly'      => 1,
        'product_type'  => $product_type,
    ];

    $args = [
        'params'        => $params,
        'image_type'    => 'url',
        'limit'         => 20,
        'page'          => 1,
        'max_num_pages' => true,
        "format"        => 'api',
        'is_mobile'     => wp_is_mobile(),
        'sort_type'     => 'popular',
        'exclude_ads'   => false,
        'unpin_ads'     => true,
        'badge_ads'     => false,
        'show_more'     => 0,
        'random'        => true
    ];
    $products = json_decode(ez_webservice(array('type' => 'sort_products_get', 'data' => $args)));

    $data[] = [
        'source' => '',
        'type'  => 'products_slider',
        'title' => 'زوم کلاب',
        'data'  => [
            'tabs'  => [],
            'items' => $products,
        ]
    ];

    /*===============================================================*/
    // کالکشن ها

    $items_per_page = 10;

    $collections = $wpdb->get_results(
        $wpdb->prepare(
            'SELECT * FROM collections WHERE active = %d ORDER BY likes_count DESC LIMIT %d',
            1,
            (int) $items_per_page
        )
    );
    foreach ($collections as $collection) {

        $images = [];
        foreach (unserialize($collection->items) as $product_id)
            $images[] = wp_get_attachment_url(get_post_thumbnail_id($product_id));

        $collection_items[] =  [
            'title'         => $collection->title,
            'user_title'    => 'فاطمه خداپرست',
            'user_level'    => 2,
            'likes_count'   => (int)$collection->likes_count,
            'url'           => "/profile/" . (int)$collection->user_id,
            'count'         => count(unserialize($collection->items)),
            'items'         => $images,
        ];
    }

    $data[] = [
        'type'  => 'collections',
        'title' => 'کالکشن های محبوب کاربران',
        'icon'  => '',
        'url'   => '/collections/',
        'data'  => [
            'items' => $collection_items,
        ]
    ];

    /*===============================================================*/
    // FAQ

    $data[] = [
        'type'  => 'faq',
        'title' => 'سوالات متداول',
        'icon'  => '',
        'url'   => '',
        'data'  => [
            'items' => [
                [
                    'question'  => 'لیزرتگ ترسناک است؟',
                    'answer'    => 'لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ، و با استفاده از طراحان گرافیک است، چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است، و برای شرایط فعلی تکنولوژی مورد نیاز، و کاربردهای متنوع با هدف بهبود ابزارهای کاربردی می باشد، کتابهای زیادی در شصت و سه درصد گذشته حال و آینده، شناخت فراوان جامعه و متخصصان را می طلبد، تا با نرم افزارها شناخت بیشتری را برای طراحان رایانه ای علی الخصوص طراحان خلاقی، و فرهنگ پیشرو در زبان فارسی ایجاد کرد، در این صورت می توان امید داشت که تمام و دشواری موجود در ارائه راهکارها، و شرایط سخت تایپ به پایان رسد و زمان مورد نیاز شامل حروفچینی دستاوردهای اصلی، و جوابگوی سوالات پیوسته اهل دنیای موجود طراحی اساسا مورد استفاده قرار گیرد.',
                ],
                [
                    'question'  => 'لیزرتگ چیست؟',
                    'answer'    => 'لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ، و با استفاده از طراحان گرافیک است، چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است، و برای شرایط فعلی تکنولوژی مورد نیاز، و کاربردهای متنوع با هدف بهبود ابزارهای کاربردی می باشد، کتابهای زیادی در شصت و سه درصد گذشته حال و آینده، شناخت فراوان جامعه و متخصصان را می طلبد، تا با نرم افزارها شناخت بیشتری را برای طراحان رایانه ای علی الخصوص طراحان خلاقی، و فرهنگ پیشرو در زبان فارسی ایجاد کرد، در این صورت می توان امید داشت که تمام و دشواری موجود در ارائه راهکارها، و شرایط سخت تایپ به پایان رسد و زمان مورد نیاز شامل حروفچینی دستاوردهای اصلی، و جوابگوی سوالات پیوسته اهل دنیای موجود طراحی اساسا مورد استفاده قرار گیرد.',
                ],
                [
                    'question'  => 'آیا در لیزرتگ مثل پینت بال آسیب وجود دارد؟',
                    'answer'    => 'لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ، و با استفاده از طراحان گرافیک است، چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است، و برای شرایط فعلی تکنولوژی مورد نیاز، و کاربردهای متنوع با هدف بهبود ابزارهای کاربردی می باشد، کتابهای زیادی در شصت و سه درصد گذشته حال و آینده، شناخت فراوان جامعه و متخصصان را می طلبد، تا با نرم افزارها شناخت بیشتری را برای طراحان رایانه ای علی الخصوص طراحان خلاقی، و فرهنگ پیشرو در زبان فارسی ایجاد کرد، در این صورت می توان امید داشت که تمام و دشواری موجود در ارائه راهکارها، و شرایط سخت تایپ به پایان رسد و زمان مورد نیاز شامل حروفچینی دستاوردهای اصلی، و جوابگوی سوالات پیوسته اهل دنیای موجود طراحی اساسا مورد استفاده قرار گیرد.',
                ],
                [
                    'question'  => 'مهارت محوری دوره چطوره انجام میشود؟',
                    'answer'    => 'لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ، و با استفاده از طراحان گرافیک است، چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است، و برای شرایط فعلی تکنولوژی مورد نیاز، و کاربردهای متنوع با هدف بهبود ابزارهای کاربردی می باشد، کتابهای زیادی در شصت و سه درصد گذشته حال و آینده، شناخت فراوان جامعه و متخصصان را می طلبد، تا با نرم افزارها شناخت بیشتری را برای طراحان رایانه ای علی الخصوص طراحان خلاقی، و فرهنگ پیشرو در زبان فارسی ایجاد کرد، در این صورت می توان امید داشت که تمام و دشواری موجود در ارائه راهکارها، و شرایط سخت تایپ به پایان رسد و زمان مورد نیاز شامل حروفچینی دستاوردهای اصلی، و جوابگوی سوالات پیوسته اهل دنیای موجود طراحی اساسا مورد استفاده قرار گیرد.',
                ],
                [
                    'question'  => 'دسترسی به جزوات دانشگاهی چگونه است؟',
                    'answer'    => 'لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ، و با استفاده از طراحان گرافیک است، چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است، و برای شرایط فعلی تکنولوژی مورد نیاز، و کاربردهای متنوع با هدف بهبود ابزارهای کاربردی می باشد، کتابهای زیادی در شصت و سه درصد گذشته حال و آینده، شناخت فراوان جامعه و متخصصان را می طلبد، تا با نرم افزارها شناخت بیشتری را برای طراحان رایانه ای علی الخصوص طراحان خلاقی، و فرهنگ پیشرو در زبان فارسی ایجاد کرد، در این صورت می توان امید داشت که تمام و دشواری موجود در ارائه راهکارها، و شرایط سخت تایپ به پایان رسد و زمان مورد نیاز شامل حروفچینی دستاوردهای اصلی، و جوابگوی سوالات پیوسته اهل دنیای موجود طراحی اساسا مورد استفاده قرار گیرد.',
                ],
                [
                    'question'  => 'دسترسی به جزوات دانشگاهی چگونه است؟',
                    'answer'    => 'لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ، و با استفاده از طراحان گرافیک است، چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است، و برای شرایط فعلی تکنولوژی مورد نیاز، و کاربردهای متنوع با هدف بهبود ابزارهای کاربردی می باشد، کتابهای زیادی در شصت و سه درصد گذشته حال و آینده، شناخت فراوان جامعه و متخصصان را می طلبد، تا با نرم افزارها شناخت بیشتری را برای طراحان رایانه ای علی الخصوص طراحان خلاقی، و فرهنگ پیشرو در زبان فارسی ایجاد کرد، در این صورت می توان امید داشت که تمام و دشواری موجود در ارائه راهکارها، و شرایط سخت تایپ به پایان رسد و زمان مورد نیاز شامل حروفچینی دستاوردهای اصلی، و جوابگوی سوالات پیوسته اهل دنیای موجود طراحی اساسا مورد استفاده قرار گیرد.',
                ],
            ],
        ]
    ];

    /*===============================================================*/
    // محتوای انتهای صفحه

    $data[] = [
        'type'  => 'html',
        'title' => '',
        'icon'  => '',
        'url'   => '',
        'data'  => $type_term->description
    ];

    wp_send_json_success($data);
}
//**********************************************************************************************************/
function product_typecity_page_api($request)
{

    $user_id = get_user_id_by_token(ez_authorization(false));

    $params = $request->get_params();
    $param  = $params['param'];

    if (is_numeric($param))
        $city_id = get_term_by('id', $param, 'product_cat')->term_id;
    else
        $city_id = get_term_by('slug', $param, 'product_cat')->term_id;

    if (!$city_id)
        wp_send_json_error(null, 404);

    $posts_per_page = 30;

    $data = array();

    /*===============================================================*/
    // اسلایدشو + متن عکس

    $data[] = [
        'type'  => 'slideshow_text_img',
        'title' => '',
        'data'  => [
            'slideshow' => [
                'slide_time'    => 5,
                'items'         => [
                    [
                        'image' => 'https://escapezoom.ir/wp-content/uploads/2024/04/slider-A-orginal-1.jpg',
                        'url'   => '/',
                    ],
                    [
                        'image' => 'https://escapezoom.ir/wp-content/uploads/2024/04/slider-A-orginal-1.jpg',
                        'url'   => '/',
                    ],
                    [
                        'image' => 'https://escapezoom.ir/wp-content/uploads/2024/04/slider-A-orginal-1.jpg',
                        'url'   => '/',
                    ],
                ],
            ],
            'text_img' => [
                'img'   => '/wp-content/uploads/2021/11/logo-new.png',
                'text'  => 'لورم ایپسوم',
            ],
        ]
    ];

    /*===============================================================*/
    // اسلایدر تبلیغات

    $params = [
        'city_id' => [$city_id],
    ];

    $args = [
        'params'        => $params,
        'image_type'    => 'url',
        'limit'         => $posts_per_page,
        'page'          => 1,
        'max_num_pages' => false,
        "format"        => 'api',
        'is_mobile'     => true,
        'sort_type'     => 'popular',
        'only_ads'      => true,
        'show_more'     => 0,
    ];

    $products = json_decode(ez_webservice(array('type' => 'sort_products_get', 'data' => $args)));

    $data[] = [
        'type'  => 'products_slider',
        'title' => 'تبلیغات',
        'ui'    => 1,
        'data'  => [
            'tabs'  => [],
            'items' => $products,
        ]
    ];

    /*===============================================================*/
    // اسلایدر ترندها

    $params = [
        'city_id' => [$city_id],
    ];

    $args = [
        "params" => $params,
        "source" => "cat_trends"
    ];
    $products = json_decode(ez_webservice(array('type' => 'sort_products_get', 'data' => $args)))->products;

    $data[] = [
        'type'  => 'products_slider',
        'title' => 'اتاق فرارهای <b>ترند</b>',
        'icon'  => '',
        'url'   => '/trends',
        'data'  => [
            'tabs'  => [
                [
                    'type'  => 'schedule',
                    'title' => 'سانس آزاد برای',
                    'key'   => 'schedule',
                    'items' => [
                        [
                            'title' => 'همه',
                            'min'   => -1,
                            'max'   => -1,
                        ],
                        [
                            'title' => 'فقط امروز',
                            'min'   => 'dynamic',
                            'max'   => 'dynamic',
                        ],
                        [
                            'title' => 'فقط فردا',
                            'min'   => 'dynamic',
                            'max'   => 'dynamic',
                        ],
                        [
                            'title' => 'فقط پس فردا',
                            'min'   => 'dynamic',
                            'max'   => 'dynamic',
                        ],
                    ],
                ]
            ],
            'items' => $products,
        ]
    ];

    /*===============================================================*/
    // اسلایدر غیرترسناک + تخفیف ویژه

    $params = [
        'city_id'   => [$city_id],
        'tag'       => -124,
    ];

    $args = [
        'params'        => $params,
        'image_type'    => 'url',
        'limit'         => $posts_per_page,
        'page'          => 1,
        'max_num_pages' => false,
        "format"        => 'api',
        'sort_type'     => 'popular',
        'exclude_ads'   => false,
        'unpin_ads'     => true,
        'badge_ads'     => false,
        'show_more'     => 0,
    ];

    $products = json_decode(ez_webservice(array('type' => 'sort_products_get', 'data' => $args)));

    $data[] = [
        'type'  => 'products_slider_event',
        'title' => 'اتاق فرارهای غیرترسناک',
        'data'  => [
            'products_slider' => [
                'ui'    => 2,
                'tabs'  => [
                    'type'  => 'order',
                    'title' => '',
                    'key'   => 'sort_type',
                    'items' => [
                        [
                            'title' => 'محبوب ترین ها',
                            'id'    => 'popular',
                        ],
                        [
                            'title' => 'پرفروش ترین ها',
                            'id'    => 'topsale',
                        ],
                        [
                            'title' => 'جدیدترین ها',
                            'id'    => 'recent',
                        ],
                    ],
                ],
                'items' => $products,
            ],
            'event' => [
                'slide_time'    => 5,
                'color'         => 'red',
                'event_time'    => 0,
                'items'         => $products,
            ],
        ]
    ];

    /*===============================================================*/
    // اسلایدر ترسناک

    $sort_type = 'popular';
    $params = [
        'city_id' => [$city_id],
    ];
    $args = [
        "source"        => "cat_horror",
        'params'        => $params,
        'sort_type'     => $sort_type,
    ];
    $products = json_decode(ez_webservice(array('type' => 'sort_products_get', 'data' => $args)));

    $data[] = [
        'source' => 'cat_horror',
        'type'  => 'products_slider',
        'title' => 'اتاق فرارهای ترسناک',
        'data'  => [
            'tabs'  => [
                'type'  => 'order',
                'title' => '',
                'key'   => 'sort_type',
                'items' => [
                    [
                        'title' => 'همه',
                        'id'    => -1,
                    ],
                    [
                        'title' => 'محبوب ها',
                        'id'    => 'popular',
                    ],
                    [
                        'title' => 'پرفروش ها',
                        'id'    => 'topsale',
                    ],
                    [
                        'title' => 'جدید ها',
                        'id'    => 'recent',
                    ],
                ],
            ],
            'items' => $products,
        ]
    ];

    /*===============================================================*/
    // سانس یاب

    $data[] = [
        'type'  => 'sans_yab',
        'title' => 'سانس یاب',
        'items'  => [
            [
                'type'  => 'schedule',
                'title' => 'سانس‌های موجود',
                'key'   => 'schedule',
                'items' => [
                    [
                        'title' => 'همه',
                        'min'   => -1,
                        'max'   => -1,
                    ],
                    [
                        'title' => 'فقط امروز',
                        'min'   => 'dynamic',
                        'max'   => 'dynamic',
                    ],
                    [
                        'title' => 'فقط فردا',
                        'min'   => 'dynamic',
                        'max'   => 'dynamic',
                    ],
                    [
                        'title' => 'فقط پس فردا',
                        'min'   => 'dynamic',
                        'max'   => 'dynamic',
                    ],
                ],
            ],
            [
                'type'  => 'count',
                'title' => 'تعداد نفرات',
                'key'   => 'count',
                'items' => [
                    'default'   => [
                        'title' => 'همه',
                        'value' => -1,
                    ],
                    'min'       => 1,
                    'max'       => 16,
                ],
            ],
            [
                'type'  => 'price',
                'title' => 'قیمت',
                'key'   => 'price',
                'items' => [
                    'min' => 50000,
                    'max' => 400000,
                ],
            ],
            [
                'type'  => 'city_id',
                'title' => 'شهر',
                'key'   => 'city_id',
                'items' => [
                    [
                        'title' => 'تهران',
                        'value' => 15,
                    ],
                    [
                        'title' => 'کرج',
                        'value' => 162,
                    ],
                    [
                        'title' => 'اصفهان',
                        'value' => 122,
                    ],
                    [
                        'title' => 'مشهد',
                        'value' => 121,
                    ],
                    [
                        'title' => 'کرمانشاه',
                        'value' => 293,
                    ],
                    [
                        'title' => 'قزوین',
                        'value' => 270,
                    ],
                    [
                        'title' => 'کاشان',
                        'value' => 304,
                    ],
                ],
            ],
            [
                'type'  => 'tag',
                'title' => 'ژانر',
                'key'   => 'tag',
                'items' => [
                    [
                        'title' => 'ترسناک',
                        'value' => 124,
                    ],
                    [
                        'title' => 'اکشن',
                        'value' => 346,
                    ],
                    [
                        'title' => 'درام',
                        'value' => 342,
                    ],
                    [
                        'title' => 'دلهره آور',
                        'value' => 126,
                    ],
                    [
                        'title' => 'غیرترسناک',
                        'value' => 125,
                    ],
                    [
                        'title' => 'هیجانی',
                        'value' => 178,
                    ],
                    [
                        'title' => 'جنایی',
                        'value' => 127,
                    ],
                ],
            ],
            [
                'type'  => 'age',
                'title' => 'رده سنی',
                'key'   => 'age',
                'items' => [
                    [
                        'title' => 'همه',
                        'value' => -1,
                    ],
                    [
                        'title' => '+12',
                        'value' => 12,
                    ],
                    [
                        'title' => '+13',
                        'value' => 13,
                    ],
                    [
                        'title' => '+14',
                        'value' => 14,
                    ],
                    [
                        'title' => '+15',
                        'value' => 15,
                    ],
                    [
                        'title' => '+16',
                        'value' => 16,
                    ],
                    [
                        'title' => '+17',
                        'value' => 17,
                    ],
                    [
                        'title' => '+18',
                        'value' => 18,
                    ],
                ],
            ],
            [
                'type'  => 'duration',
                'title' => 'زمان بازی',
                'key'   => 'duration',
                'items' => [
                    [
                        'title' => 'همه',
                        'value' => -1,
                    ],
                    [
                        'title' => '60',
                        'value' => 60,
                    ],
                    [
                        'title' => '70',
                        'value' => 70,
                    ],
                    [
                        'title' => '80',
                        'value' => 80,
                    ],
                    [
                        'title' => '90',
                        'value' => 90,
                    ],
                    [
                        'title' => '100',
                        'value' => 100,
                    ],
                    [
                        'title' => '110',
                        'value' => 110,
                    ],
                    [
                        'title' => '120',
                        'value' => 120,
                    ],
                    [
                        'title' => '130',
                        'value' => 130,
                    ],
                    [
                        'title' => '140',
                        'value' => 140,
                    ],
                    [
                        'title' => '150',
                        'value' => 150,
                    ],
                    [
                        'title' => '160',
                        'value' => 160,
                    ],
                    [
                        'title' => '170',
                        'value' => 170,
                    ],
                    [
                        'title' => '180',
                        'value' => 180,
                    ],
                    [
                        'title' => '190',
                        'value' => 190,
                    ],
                    [
                        'title' => '200',
                        'value' => 200,
                    ],
                ],
            ],
            [
                'type'  => 'level',
                'title' => 'سطح سختی',
                'key'   => 'level',
                'items' => [
                    [
                        'title' => 'همه',
                        'value' => -1,
                    ],
                    [
                        'title' => '1 از 4',
                        'value' => 1,
                    ],
                    [
                        'title' => '2 از 4',
                        'value' => 2,
                    ],
                    [
                        'title' => '3 از 4',
                        'value' => 3,
                    ],
                    [
                        'title' => '4 از 4',
                        'value' => 4,
                    ],
                ],
            ],
        ],
        'tabs'  => [
            'type'  => 'order',
            'title' => 'ترتیب نمایش',
            'key'   => 'sort_type',
            'items' => [
                [
                    'title' => 'محبوب ترین ها',
                    'id'    => 'popular',
                ],
                [
                    'title' => 'پرفروش ترین ها',
                    'id'    => 'topsale',
                ],
                [
                    'title' => 'جدیدترین ها',
                    'id'    => 'recent',
                ],
            ],
        ],
    ];

    wp_send_json_success($data);
}
//**********************************************************************************************************/
function product_genre_page_api($request)
{ // صفحه سرگرمی مثلا صفحه اتاق فرار، صفحه سینماترس و ...
    global $wpdb;

    $user_id = get_user_id_by_token(ez_authorization(false));

    $params = $request->get_params();
    $param  = $params['param'];

    $type_term = get_term_by('slug', $param, 'product_cat');

    if (is_numeric($param))
        $type_id = (int)$param;
    else
        $type_id = $type_term->term_id;

    if (!$type_id)
        wp_send_json_error(null, 404);

    $product_type       = $type_term->name;
    $product_type_equ   = get_product_type_equivalent($product_type);

    $is_escaperoom = false;
    if ($product_type == 'اتاق فرار')
        $is_escaperoom = true;

    /*===============================================================*/
    // ویدئو + متن

    $data[] = [
        'type'  => 'video_text',
        'title' => '',
        'data'  => [
            'title' => 'اتاق فرار EscapeRoom',
            'text'  => 'لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ، و با استفاده از طراحان گرافیک است، چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است، و برای شرایط فعلی تکنولوژی مورد نیاز، و کاربردهای متنوع با هدف بهبود ابزارهای کاربردی می باشد، کتابهای زیادی در شصت و سه درصد گذشته حال و آینده، شناخت فراوان جامعه و متخصصان را می طلبد، تا با نرم افزارها شناخت بیشتری را برای طراحان رایانه ای علی الخصوص طراحان خلاقی، و فرهنگ پیشرو در زبان فارسی ایجاد کرد، در این صورت می توان امید داشت که تمام و دشواری موجود در ارائه راهکارها، و شرایط سخت تایپ به پایان رسد و زمان مورد نیاز شامل حروفچینی دستاوردهای اصلی، و جوابگوی سوالات پیوسته اهل دنیای موجود طراحی اساسا مورد استفاده قرار گیرد.',
            'video' => '<style>.r1_iframe_embed {position: relative; overflow: hidden; width: 100%; height: auto; padding-top: 56.25%; } .r1_iframe_embed iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0; }</style><div class="r1_iframe_embed"><iframe src="https://player.arvancloud.ir/index.html?config=https://ez.arvanvod.ir/2MP5ZV5a1r/0WMB6w3q2E/origin_config.json&skin=shaka" style="border:0 #ffffff none;" name="cirota2.mp4" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowFullScreen="true" webkitallowfullscreen="true" mozallowfullscreen="true"></iframe></div>',
        ]
    ];

    /*===============================================================*/
    // اتاق فرارهای ایران

    if ($is_escaperoom) :

        $args = [
            'source'    => 'type_page_cat_' . $product_type_equ . '_-1',
            'params'    => $params,
        ];
        $products = json_decode(ez_webservice(array('type' => 'sort_products_get', 'data' => $args)));

        $data[] = [
            'source' => 'type_page_cat_' . $product_type_equ . '_-1',
            'type'  => 'products_slider',
            'title' => 'اتاق فرار های <b>ایران</b>',
            'icon'  => '',
            'url'   => '',
            'data'  => [
                'tabs'  => [
                    'type'  => 'order',
                    'title' => '',
                    'key'   => 'sort_type',
                    'items' => [
                        [
                            'title' => 'همه',
                            'id'    => -1,
                        ],
                        [
                            'title' => 'محبوب ترین',
                            'id'    => 'popular',
                        ],
                        [
                            'title' => 'پرفروش ترین',
                            'id'    => 'topsale',
                        ],
                        [
                            'title' => 'جدیدترین',
                            'id'    => 'recent',
                        ],
                    ],
                ],
                'items' => $products,
            ]
        ];

    endif;

    /*===============================================================*/
    // اتاق فرارهای تهران

    if ($is_escaperoom) :

        $args = [
            'source'    => 'type_page_cat_' . $product_type_equ . '_15',
            'params'    => $params,
        ];
        $products = json_decode(ez_webservice(array('type' => 'sort_products_get', 'data' => $args)));

        $data[] = [
            'source' => 'type_page_cat_' . $product_type_equ . '_15',
            'type'  => 'products_slider',
            'title' => 'اتاق فرار های <b>ایران</b> و دارای سانس',
            'icon'  => '',
            'url'   => '',
            'data'  => [
                'tabs'  => [
                    [
                        'type'  => 'city_id',
                        'title' => 'شهر مورد نظر',
                        'key'   => 'city_id',
                        'items' => [
                            [
                                'title' => 'تهران',
                                'value' => 15,
                            ],
                            [
                                'title' => 'کرج',
                                'value' => 162,
                            ],
                            [
                                'title' => 'اصفهان',
                                'value' => 122,
                            ],
                            [
                                'title' => 'مشهد',
                                'value' => 121,
                            ],
                            [
                                'title' => 'کرمانشاه',
                                'value' => 293,
                            ],
                            [
                                'title' => 'قزوین',
                                'value' => 270,
                            ],
                            [
                                'title' => 'کاشان',
                                'value' => 304,
                            ],
                        ],
                    ],
                    [
                        'type'  => 'tag',
                        'title' => 'سبک بازی',
                        'key'   => 'tag',
                        'items' => [
                            [
                                'title' => 'ترسناک',
                                'value' => 124,
                            ],
                            [
                                'title' => 'اکشن',
                                'value' => 346,
                            ],
                            [
                                'title' => 'درام',
                                'value' => 342,
                            ],
                            [
                                'title' => 'دلهره آور',
                                'value' => 126,
                            ],
                            [
                                'title' => 'غیرترسناک',
                                'value' => 125,
                            ],
                            [
                                'title' => 'هیجانی',
                                'value' => 178,
                            ],
                            [
                                'title' => 'جنایی',
                                'value' => 127,
                            ],
                        ],
                    ],
                    [
                        'type'  => 'order',
                        'title' => 'براساس',
                        'key'   => 'sort_type',
                        'items' => [
                            [
                                'title' => 'همه',
                                'id'    => -1,
                            ],
                            [
                                'title' => 'محبوب ترین',
                                'id'    => 'popular',
                            ],
                            [
                                'title' => 'پرفروش ترین',
                                'id'    => 'topsale',
                            ],
                            [
                                'title' => 'جدیدترین',
                                'id'    => 'recent',
                            ],
                        ],
                    ],
                ],
                'items' => $products,
            ]
        ];

    endif;

    /*===============================================================*/
    // سرگرمی های مختلف در شهرهای مختلف (سینماترس تهران، اتاق خشم تهران ....)

    if (!$is_escaperoom) :

        $type_city_list = [
            'lasertag'  => [1149, 1158],
            'rageroom'  => [1186, 1074],
            'cinema'    => [913, 1009],
        ];

        foreach ($type_city_list[$product_type_equ] as $type_city_item) {

            $args = [
                'source'    => 'type_page_cat_' . $product_type_equ . '_' . $type_city_item,
                'params'    => $params,
            ];
            $products = json_decode(ez_webservice(array('type' => 'sort_products_get', 'data' => $args)));

            $data[] = [
                'source' => 'type_page_cat_' . $product_type_equ . '_' . $type_city_item,
                'type'  => 'products_slider',
                'title' => $product_type . ' های <b>' . get_term($type_city_item)->name . '</b>' . 'و دارای سانس',
                'icon'  => '',
                'url'   => '',
                'data'  => [
                    'tabs'  => [
                        'type'  => 'order',
                        'title' => '',
                        'key'   => 'sort_type',
                        'items' => [
                            [
                                'title' => 'همه',
                                'id'    => -1,
                            ],
                            [
                                'title' => 'محبوب ترین',
                                'id'    => 'popular',
                            ],
                            [
                                'title' => 'پرفروش ترین',
                                'id'    => 'topsale',
                            ],
                            [
                                'title' => 'جدیدترین',
                                'id'    => 'recent',
                            ],
                        ],
                    ],
                    'items' => $products,
                ]
            ];
        }

    endif;

    /*===============================================================*/
    // تخفیف ویژه برای سرگرمی جاری (مشترک)

    $args = [
        'source' => 'type_page_discounts_event_' . $product_type_equ,
    ];
    $products = json_decode(ez_webservice(array('type' => 'sort_products_get', 'data' => $args)));

    $data[] = [
        'source' => 'type_page_discounts_event_' .  $product_type_equ,
        'type'  => 'event',
        'title' => '<b>تخفیف های ویژه</b> و دارای سانس',
        'icon'  => ez_theme_asset_uri('images/genres/Takhfif.svg'),
        'url'   => '',
        'data'  => [
            'color' => '#eee',
            'items' => $products,
            'tabs'  => [
                'type'  => 'schedule',
                'title' => '',
                'key'   => 'schedule',
                'items' => [
                    [
                        'title' => 'همه',
                        'min'   => -1,
                        'max'   => -1,
                    ],
                    [
                        'title' => 'فقط امروز',
                        'min'   => 'dynamic',
                        'max'   => 'dynamic',
                    ],
                    [
                        'title' => 'فقط فردا',
                        'min'   => 'dynamic',
                        'max'   => 'dynamic',
                    ],
                    [
                        'title' => 'فقط پس فردا',
                        'min'   => 'dynamic',
                        'max'   => 'dynamic',
                    ],
                ],
            ],
        ]
    ];

    /*===============================================================*/
    // باکس شهرها برای سرگرمی های غیر اتاق فرار

    if (!$is_escaperoom) :

        $data[] = [
            'type'  => 'genres',
            'title' => '',
            'icon'  => '',
            'data'  => [
                'items' => [
                    [
                        'image'     => ez_theme_asset_uri('images/genres/action.svg'),
                        'title'     => 'اکشن',
                        'popular'   => true,
                        'url'       => '/type/%D8%A7%DA%A9%D8%B4%D9%86/',
                    ],
                    [
                        'image'     => ez_theme_asset_uri('images/genres/non-scary.svg'),
                        'title'     => 'غیرترسناک',
                        'popular'   => false,
                        'url'       => '/type/%D8%A7%D8%AA%D8%A7%D9%82-%D9%81%D8%B1%D8%A7%D8%B1-%D8%AA%D8%B1%D8%B3%D9%86%D8%A7%DA%A9/',
                    ],
                    [
                        'image'     => ez_theme_asset_uri('images/genres/scary.svg'),
                        'title'     => 'ترسناک',
                        'popular'   => true,
                        'url'       => '/type/%D8%A7%DA%A9%D8%B4%D9%86/',
                    ],
                    [
                        'image'     => ez_theme_asset_uri('images/genres/dram.svg'),
                        'title'     => 'درام',
                        'popular'   => false,
                        'url'       => '/type/%D8%A7%D8%AA%D8%A7%D9%82-%D9%81%D8%B1%D8%A7%D8%B1-%D8%AA%D8%B1%D8%B3%D9%86%D8%A7%DA%A9/',
                    ],
                    [
                        'image'     => ez_theme_asset_uri('images/genres/exciting.svg'),
                        'title'     => 'هیجانی',
                        'popular'   => false,
                        'url'       => '/type/%D8%A7%DA%A9%D8%B4%D9%86/',
                    ],
                ],
            ]
        ];

    endif;

    /*===============================================================*/
    // اتاق فرارهای ترسناک

    if ($is_escaperoom) :

        $args = [
            'source' => 'type_page_escaperoom_genre_horror',
        ];
        $products = json_decode(ez_webservice(array('type' => 'sort_products_get', 'data' => $args)));

        $data[] = [
            'source' => 'type_page_escaperoom_genre_horror',
            'type'  => 'products_slider',
            'title' => 'اتاق فرارهای ترسناک',
            'data'  => [
                'tabs'  => [
                    'type'  => 'order',
                    'title' => '',
                    'key'   => 'sort_type',
                    'items' => [
                        [
                            'title' => 'همه',
                            'id'    => -1,
                        ],
                        [
                            'title' => 'محبوب ها',
                            'id'    => 'popular',
                        ],
                        [
                            'title' => 'پرفروش ها',
                            'id'    => 'topsale',
                        ],
                        [
                            'title' => 'جدید ها',
                            'id'    => 'recent',
                        ],
                    ],
                ],
                'items' => $products,
            ]
        ];

    endif;

    /*===============================================================*/
    // اتاق فرارهای غیرترسناک

    if ($is_escaperoom) :

        $args = [
            'source' => 'type_page_escaperoom_genre_nonhorror',
        ];
        $products = json_decode(ez_webservice(array('type' => 'sort_products_get', 'data' => $args)));

        $data[] = [
            'source' => 'type_page_escaperoom_genre_horror',
            'type'  => 'products_slider',
            'title' => 'اتاق فرارهای غیرترسناک و هیجانی',
            'data'  => [
                'tabs'  => [
                    'type'  => 'order',
                    'title' => '',
                    'key'   => 'sort_type',
                    'items' => [
                        [
                            'title' => 'همه',
                            'id'    => -1,
                        ],
                        [
                            'title' => 'محبوب ها',
                            'id'    => 'popular',
                        ],
                        [
                            'title' => 'پرفروش ها',
                            'id'    => 'topsale',
                        ],
                        [
                            'title' => 'جدید ها',
                            'id'    => 'recent',
                        ],
                    ],
                ],
                'items' => $products,
            ]
        ];

    endif;

    /*===============================================================*/
    // ژانرهای اتاق فرار

    if ($is_escaperoom) :

        $data[] = [
            'type'  => 'genres',
            'title' => '',
            'icon'  => '',
            'data'  => [
                'items' => [
                    [
                        'image'     => ez_theme_asset_uri('images/genres/action.svg'),
                        'title'     => 'اکشن',
                        'popular'   => true,
                        'url'       => '/type/%D8%A7%DA%A9%D8%B4%D9%86/',
                    ],
                    [
                        'image'     => ez_theme_asset_uri('images/genres/non-scary.svg'),
                        'title'     => 'غیرترسناک',
                        'popular'   => false,
                        'url'       => '/type/%D8%A7%D8%AA%D8%A7%D9%82-%D9%81%D8%B1%D8%A7%D8%B1-%D8%AA%D8%B1%D8%B3%D9%86%D8%A7%DA%A9/',
                    ],
                    [
                        'image'     => ez_theme_asset_uri('images/genres/scary.svg'),
                        'title'     => 'ترسناک',
                        'popular'   => true,
                        'url'       => '/type/%D8%A7%DA%A9%D8%B4%D9%86/',
                    ],
                    [
                        'image'     => ez_theme_asset_uri('images/genres/dram.svg'),
                        'title'     => 'درام',
                        'popular'   => false,
                        'url'       => '/type/%D8%A7%D8%AA%D8%A7%D9%82-%D9%81%D8%B1%D8%A7%D8%B1-%D8%AA%D8%B1%D8%B3%D9%86%D8%A7%DA%A9/',
                    ],
                    [
                        'image'     => ez_theme_asset_uri('images/genres/exciting.svg'),
                        'title'     => 'هیجانی',
                        'popular'   => false,
                        'url'       => '/type/%D8%A7%DA%A9%D8%B4%D9%86/',
                    ],
                ],
            ]
        ];

    endif;

    /*===============================================================*/
    // زوم کلاب

    $params = [
        'city_id'       => -1,
        'monopoly'      => 1,
        'product_type'  => $product_type,
    ];

    $args = [
        'params'        => $params,
        'image_type'    => 'url',
        'limit'         => 20,
        'page'          => 1,
        'max_num_pages' => true,
        "format"        => 'api',
        'is_mobile'     => wp_is_mobile(),
        'sort_type'     => 'popular',
        'exclude_ads'   => false,
        'unpin_ads'     => true,
        'badge_ads'     => false,
        'show_more'     => 0,
        'random'        => true
    ];
    $products = json_decode(ez_webservice(array('type' => 'sort_products_get', 'data' => $args)));

    $data[] = [
        'source' => '',
        'type'  => 'products_slider',
        'title' => 'زوم کلاب',
        'data'  => [
            'tabs'  => [],
            'items' => $products,
        ]
    ];

    /*===============================================================*/
    // کالکشن ها

    $items_per_page = 10;

    $collections = $wpdb->get_results(
        $wpdb->prepare(
            'SELECT * FROM collections WHERE active = %d ORDER BY likes_count DESC LIMIT %d',
            1,
            (int) $items_per_page
        )
    );
    foreach ($collections as $collection) {

        $images = [];
        foreach (unserialize($collection->items) as $product_id)
            $images[] = wp_get_attachment_url(get_post_thumbnail_id($product_id));

        $collection_items[] =  [
            'title'         => $collection->title,
            'user_title'    => 'فاطمه خداپرست',
            'user_level'    => 2,
            'likes_count'   => (int)$collection->likes_count,
            'url'           => "/profile/" . (int)$collection->user_id,
            'count'         => count(unserialize($collection->items)),
            'items'         => $images,
        ];
    }

    $data[] = [
        'type'  => 'collections',
        'title' => 'کالکشن های محبوب کاربران',
        'icon'  => '',
        'url'   => '/collections/',
        'data'  => [
            'items' => $collection_items,
        ]
    ];

    /*===============================================================*/
    // FAQ

    $data[] = [
        'type'  => 'faq',
        'title' => 'سوالات متداول',
        'icon'  => '',
        'url'   => '',
        'data'  => [
            'items' => [
                [
                    'question'  => 'لیزرتگ ترسناک است؟',
                    'answer'    => 'لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ، و با استفاده از طراحان گرافیک است، چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است، و برای شرایط فعلی تکنولوژی مورد نیاز، و کاربردهای متنوع با هدف بهبود ابزارهای کاربردی می باشد، کتابهای زیادی در شصت و سه درصد گذشته حال و آینده، شناخت فراوان جامعه و متخصصان را می طلبد، تا با نرم افزارها شناخت بیشتری را برای طراحان رایانه ای علی الخصوص طراحان خلاقی، و فرهنگ پیشرو در زبان فارسی ایجاد کرد، در این صورت می توان امید داشت که تمام و دشواری موجود در ارائه راهکارها، و شرایط سخت تایپ به پایان رسد و زمان مورد نیاز شامل حروفچینی دستاوردهای اصلی، و جوابگوی سوالات پیوسته اهل دنیای موجود طراحی اساسا مورد استفاده قرار گیرد.',
                ],
                [
                    'question'  => 'لیزرتگ چیست؟',
                    'answer'    => 'لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ، و با استفاده از طراحان گرافیک است، چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است، و برای شرایط فعلی تکنولوژی مورد نیاز، و کاربردهای متنوع با هدف بهبود ابزارهای کاربردی می باشد، کتابهای زیادی در شصت و سه درصد گذشته حال و آینده، شناخت فراوان جامعه و متخصصان را می طلبد، تا با نرم افزارها شناخت بیشتری را برای طراحان رایانه ای علی الخصوص طراحان خلاقی، و فرهنگ پیشرو در زبان فارسی ایجاد کرد، در این صورت می توان امید داشت که تمام و دشواری موجود در ارائه راهکارها، و شرایط سخت تایپ به پایان رسد و زمان مورد نیاز شامل حروفچینی دستاوردهای اصلی، و جوابگوی سوالات پیوسته اهل دنیای موجود طراحی اساسا مورد استفاده قرار گیرد.',
                ],
                [
                    'question'  => 'آیا در لیزرتگ مثل پینت بال آسیب وجود دارد؟',
                    'answer'    => 'لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ، و با استفاده از طراحان گرافیک است، چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است، و برای شرایط فعلی تکنولوژی مورد نیاز، و کاربردهای متنوع با هدف بهبود ابزارهای کاربردی می باشد، کتابهای زیادی در شصت و سه درصد گذشته حال و آینده، شناخت فراوان جامعه و متخصصان را می طلبد، تا با نرم افزارها شناخت بیشتری را برای طراحان رایانه ای علی الخصوص طراحان خلاقی، و فرهنگ پیشرو در زبان فارسی ایجاد کرد، در این صورت می توان امید داشت که تمام و دشواری موجود در ارائه راهکارها، و شرایط سخت تایپ به پایان رسد و زمان مورد نیاز شامل حروفچینی دستاوردهای اصلی، و جوابگوی سوالات پیوسته اهل دنیای موجود طراحی اساسا مورد استفاده قرار گیرد.',
                ],
                [
                    'question'  => 'مهارت محوری دوره چطوره انجام میشود؟',
                    'answer'    => 'لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ، و با استفاده از طراحان گرافیک است، چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است، و برای شرایط فعلی تکنولوژی مورد نیاز، و کاربردهای متنوع با هدف بهبود ابزارهای کاربردی می باشد، کتابهای زیادی در شصت و سه درصد گذشته حال و آینده، شناخت فراوان جامعه و متخصصان را می طلبد، تا با نرم افزارها شناخت بیشتری را برای طراحان رایانه ای علی الخصوص طراحان خلاقی، و فرهنگ پیشرو در زبان فارسی ایجاد کرد، در این صورت می توان امید داشت که تمام و دشواری موجود در ارائه راهکارها، و شرایط سخت تایپ به پایان رسد و زمان مورد نیاز شامل حروفچینی دستاوردهای اصلی، و جوابگوی سوالات پیوسته اهل دنیای موجود طراحی اساسا مورد استفاده قرار گیرد.',
                ],
                [
                    'question'  => 'دسترسی به جزوات دانشگاهی چگونه است؟',
                    'answer'    => 'لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ، و با استفاده از طراحان گرافیک است، چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است، و برای شرایط فعلی تکنولوژی مورد نیاز، و کاربردهای متنوع با هدف بهبود ابزارهای کاربردی می باشد، کتابهای زیادی در شصت و سه درصد گذشته حال و آینده، شناخت فراوان جامعه و متخصصان را می طلبد، تا با نرم افزارها شناخت بیشتری را برای طراحان رایانه ای علی الخصوص طراحان خلاقی، و فرهنگ پیشرو در زبان فارسی ایجاد کرد، در این صورت می توان امید داشت که تمام و دشواری موجود در ارائه راهکارها، و شرایط سخت تایپ به پایان رسد و زمان مورد نیاز شامل حروفچینی دستاوردهای اصلی، و جوابگوی سوالات پیوسته اهل دنیای موجود طراحی اساسا مورد استفاده قرار گیرد.',
                ],
                [
                    'question'  => 'دسترسی به جزوات دانشگاهی چگونه است؟',
                    'answer'    => 'لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ، و با استفاده از طراحان گرافیک است، چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است، و برای شرایط فعلی تکنولوژی مورد نیاز، و کاربردهای متنوع با هدف بهبود ابزارهای کاربردی می باشد، کتابهای زیادی در شصت و سه درصد گذشته حال و آینده، شناخت فراوان جامعه و متخصصان را می طلبد، تا با نرم افزارها شناخت بیشتری را برای طراحان رایانه ای علی الخصوص طراحان خلاقی، و فرهنگ پیشرو در زبان فارسی ایجاد کرد، در این صورت می توان امید داشت که تمام و دشواری موجود در ارائه راهکارها، و شرایط سخت تایپ به پایان رسد و زمان مورد نیاز شامل حروفچینی دستاوردهای اصلی، و جوابگوی سوالات پیوسته اهل دنیای موجود طراحی اساسا مورد استفاده قرار گیرد.',
                ],
            ],
        ]
    ];

    /*===============================================================*/
    // محتوای انتهای صفحه

    $data[] = [
        'type'  => 'html',
        'title' => '',
        'icon'  => '',
        'url'   => '',
        'data'  => $type_term->description
    ];

    wp_send_json_success($data);
}

/*=========================================================================================================*/
//Post functions

function post_get_api($request)
{

    $user_id = get_user_id_by_token(ez_authorization(false));

    $params = $request->get_params();
    $param  = $params['param'];

    if (is_numeric($param)) {
        $post = get_post($param);
        $post_id = (int)$param;
    } else {
        $post = get_page_by_path($param, OBJECT, 'post');
        $post_id = $post->ID;
    }

    if (!$post)
        wp_send_json_error(null, 404);

    /*------------------------------------------------*/
    //Categories

    $category_titles    = [];
    $category_ids       = [];
    foreach (get_the_category($post_id) as $category) {
        $category_titles[]  = $category->name;
        $category_ids[]     = $category->term_id;
    }

    /*------------------------------------------------*/
    //Related Posts

    $number_of_related_posts = 20;
    $args = [
        'category__in'      => $category_ids,
        'post__not_in'      => [$post_id],
        'posts_per_page'    => $number_of_related_posts,
    ];

    $query = new WP_Query($args);
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();

            $related_post_id = get_the_ID();

            $related_posts[] =  [
                'title'         => get_the_title(),
                'image'         => wp_get_attachment_url(get_post_thumbnail_id($related_post_id)),
                'author'        => get_user_by('id', get_post_field('post_author', $related_post_id))->data->display_name,
                'content'       => get_post_field('post_excerpt', $related_post_id),
                'comment_count' => (int)get_comments_number(),
                'url'           => '/blog/' . get_post_field('post_name', $related_post_id),
                "category"      => "اخبار"
            ];
        }
        wp_reset_postdata();
    }

    /*------------------------------------------------*/
    //Comments

    $comments_per_page  = 10;
    $total_comments     = wp_count_comments($post_id)->approved;

    $comments_list = get_comments(array(
        'post_id'   => $post_id,
        'status'    => 'approve',
        'parent'    => 0,
        'orderby'   => 'comment_date',
        'order'     => 'DESC',
        'number'    => $comments_per_page,
    ));

    if (!empty($comments_list)) {
        foreach ($comments_list as $comment) {
            $comment_id = $comment->comment_ID;

            $replies = get_post_reply_comments($comment_id);

            $comments[] = [
                'comment_id'    => (int)$comment_id,
                'author_title'  => get_user_by('id', $comment->user_id)->data->display_name ?: $comment->comment_author,
                'author_image'  => get_user_meta($comment->user_id, 'user_avatar', true) ?: 'http://escapezoom.ir/wp-content/uploads/2024/04/male_avatar_level_1.png',
                'author_level'  => get_user_meta($comment->user_id, 'level', true) ?: 1,
                'content'       => $comment->comment_content,
                'date'          => strtotime($comment->comment_date),
                'replies'       => $replies
            ];
        }
    }

    /*------------------------------------------------*/

    $data = [
        'id'            => $post_id,
        'title'         => $post->post_title,
        'image'         => wp_get_attachment_url(get_post_thumbnail_id($post_id)),
        'author'        => get_user_by('id', $post->post_author)->data->display_name,
        'rewriter'      => get_user_by('id', get_post_meta($post_id, 'rewrite_author', true))->data->display_name,
        'date'          => strtotime($post->post_date),
        'rewriting_date' => strtotime($post->post_modified),
        'views'         => (int)get_post_meta($post_id, 'views', true),
        'category'      => $category_titles,
        'content'       => $post->post_content,
        'rating'        => [
            'rate'          => get_post_meta($post_id, 'rmp_avg_rating', true),
            'count'         => (int)get_post_meta($post_id, 'rmp_vote_count', true),
            'rated'         => get_user_meta($user_id, 'post_rated', true),
            'rating_items'  => [
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
            'short_url'     => "https://escapezoom.ir/$post_id",
            'sharing_urls'  => [
                [
                    "title" => "اینستاگرام",
                    "url"   => "http://www.instagram.com/sharer/sharer.php?u=" . urlencode(get_permalink($post_id)),
                ],
                [
                    "title" => "توییتر",
                    "url"   => "http://www.twitter.com/sharer/sharer.php?u=" . urlencode(get_permalink($post_id)),
                ],
                [
                    "title" => "یوتوب",
                    "url"   => "http://www.youtube.com/sharer/sharer.php?u=" . urlencode(get_permalink($post_id)),
                ],
            ]
        ],
        'banner1'       => [
            'image' => 'http://escapezoom.ir/wp-content/uploads/2024/10/Tehran-Nights-forever.jpg',
            'url'   => 'https://escapezoom.ir/',
        ],
        'related'       => $related_posts,
        'banner2'       => [
            'image' => 'http://escapezoom.ir/wp-content/uploads/2024/10/Tehran-Nights-forever.jpg',
            'url'   => 'https://escapezoom.ir/',
        ],
        'comments'      => [
            'tabs'          => [],
            'count'         => $total_comments,
            'items'         => $comments,
            'total_pages'   => ceil($total_comments / $comments_per_page),
        ],
        'breadcrumb'    => [
            [
                'title' => 'صفحه اصلی',
                'url'   => '/',
            ],
            [
                'title' => 'بلاگ',
                'url'   => '/blog',
            ],
            [
                'title' => 'ترسناک',
                'url'   => '/blog/category/ترسناک',
            ],
            [
                'title' => $post->post_title,
                'url'   => '',
            ],
        ],
    ];

    wp_send_json_success($data);
}
//**********************************************************************************************************/
function post_get_product_api($request)
{

    $user_id = get_user_id_by_token(ez_authorization(false));

    $params = $request->get_params();
    $param  = $params['param'];

    $product = get_post($param);
    $product_id = (int)$param;

    if (!$product)
        wp_send_json_error(null, 404);

    foreach (get_the_terms($product_id, 'product_tag') as $product_tag)
        if (str_contains($product_tag->name, '|||||'))
            $genres[] = str_replace('|||||', '', $product_tag->name);

    $product = wc_get_product($product_id);

    $data = [
        'product_id'    => $product_id,
        'title'         => $product->get_title(),
        'image'         => wp_get_attachment_url(get_post_thumbnail_id($product_id)),
        'level'         => (int)get_field("room_level", $product_id),
        'genre_hood'    => get_field("room_loc", $product_id) . '،' . $genres[0],
        'rate'          => 5,
    ];

    wp_send_json_success($data);
}
//**********************************************************************************************************/
function post_get_post_api($request)
{

    $user_id = get_user_id_by_token(ez_authorization(false));

    $params = $request->get_params();
    $param  = $params['param'];

    $post = get_post($param);
    $post_id = (int)$param;

    if (!$post)
        wp_send_json_error(null, 404);

    $data = [
        'post_id'   => $post_id,
        'title'     => get_the_title($post_id),
        'image'     => wp_get_attachment_url(get_post_thumbnail_id($post_id)),
        'url'       => trim_home_url(get_permalink($post_id)),
    ];

    wp_send_json_success($data);
}
//**********************************************************************************************************/
function post_category_api($request)
{
    $user_id = get_user_id_by_token(ez_authorization(false));

    $params = $request->get_params();
    $param      = $params['param'];
    $page_num   = $params['page'];

    $page_num = $page_num ?: 1;

    if (is_numeric($param))
        $category = get_term_by('id', $param, 'category');
    else
        $category = get_term_by('slug', $param, 'category');

    if (!$category)
        wp_send_json_error(null, 404);

    $term_id = $category->term_id;

    $posts_per_page = 10;

    $args = array(
        'post_type'         => 'post',
        'post_status'       => 'publish',
        'posts_per_page'    => 3,
        'tax_query'         => array(
            array(
                'taxonomy'  => 'category',
                'field'     => 'term_id',
                'terms'     => array($term_id),
            ),
        ),
    );
    $query = new WP_Query($args);
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            global $post;
            $post_id = $post->ID;

            $exclude_posts[] = $post_id;

            $post_categories = get_the_category($post_id);

            $items_header[] = [
                'id'            => $post_id,
                'title'         => $post->post_title,
                'image'         => wp_get_attachment_url(get_post_thumbnail_id($post_id)),
                'content_type'  => $post_categories[0]->term_id == 805 ? 'video' : 'text',
                'category'      => !empty($post_categories) ? $post_categories[0]->name : '',
            ];
        }
        wp_reset_postdata();
    }

    $args_new = array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => $posts_per_page,
        'tax_query'      => array(
            array(
                'taxonomy' => 'category',
                'field'    => 'term_id',
                'terms'    => array($term_id),
            ),
        ),
        'post__not_in'   => $exclude_posts,
        'paged'          => $page_num,
    );
    $query_new = new WP_Query($args_new);
    if ($query_new->have_posts()) {
        while ($query_new->have_posts()) {
            $query_new->the_post();
            global $post;
            $post_id = $post->ID;

            $post_categories = get_the_category($post_id);

            $items_body[] = [
                'id'            => $post_id,
                'title'         => $post->post_title,
                'image'         => wp_get_attachment_url(get_post_thumbnail_id($post_id)),
                'content_type'  => $post_categories[0]->term_id == 805 ? 'video' : 'text',
                'category'      => !empty($post_categories) ? $post_categories[0]->name : '',
            ];
        }
        wp_reset_postdata();
    }

    $data[] = [
        'title' => $category->name,
        'items' => [
            'header'    => $items_header,
            'body'      => $items_body,
        ],
        'pagination'    => [
            'current_page'  => (int)$page_num,
            'total_pages'   => $query_new->max_num_pages
        ],
        'breadcrumb'    => [
            [
                'title' => 'صفحه اصلی',
                'url'   => '/',
            ],
            [
                'title' => 'بلاگ',
                'url'   => '/blog',
            ],
            [
                'title' => $category->name,
                'url'   => '',
            ],
        ],
    ];

    wp_send_json_success($data);
}
//**********************************************************************************************************/
function post_blog_api($request)
{
    global $wpdb;

    $user_id = get_user_id_by_token(ez_authorization(false));

    $params = $request->get_params();
    $page_num   = $params['page'];
    $cat_id     = $params['cat_id'];

    $page_num = $page_num ?: 1;

    /*===========================================================*/
    // Header 5 posts

    $header_posts = [493278, 484634, 482942, 479049, 481196];
    $args = array(
        'post_type'         => 'post',
        'post_status'       => 'publish',
        'posts_per_page'    => 5,
        'post__in'          => $header_posts,
    );
    $query = new WP_Query($args);
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            global $post;
            $post_id = $post->ID;

            $post_categories = get_the_category($post_id);

            $items_header[] = [
                'id'            => $post_id,
                'title'         => $post->post_title,
                'content'       => $post->post_excerpt,
                'author'        => $post->post_author,
                'views'         => 999,
                'image'         => wp_get_attachment_url(get_post_thumbnail_id($post_id)),
                'content_type'  => !empty($post_categories) ? ($post_categories[0]->term_id == 805 ? 'video' : 'text') : 'text',
                'category'      => !empty($post_categories) ? $post_categories[0]->name : '',
            ];
        }
        wp_reset_postdata();
    }

    /*===========================================================*/
    // Body recent posts

    $posts_per_page_body = 12;
    $args = array(
        'post_type'         => 'post',
        'post_status'       => 'publish',
        'posts_per_page'    => $posts_per_page_body,
        'post__not_in'      => $header_posts,
        'paged'             => $page_num,
    );

    if ($cat_id != -1)
        if (!empty($cat_id))
            $args['tax_query'][] = [
                'taxonomy' => 'category',
                'field' => 'term_id',
                'terms' => array($cat_id),
            ];

    $query_body = new WP_Query($args);
    if ($query_body->have_posts()) {
        while ($query_body->have_posts()) {
            $query_body->the_post();
            global $post;
            $post_id = $post->ID;

            $post_categories = get_the_category($post_id);

            $items_recent[] = [
                'id'            => $post_id,
                'title'         => $post->post_title,
                'author'        => $post->post_author,
                'date'          => strtotime($post->post_date),
                'views'         => 999,
                'image'         => wp_get_attachment_url(get_post_thumbnail_id($post_id)),
                'content_type'  => !empty($post_categories) ? ($post_categories[0]->term_id == 805 ? 'video' : 'text') : 'text',
                'category'      => !empty($post_categories) ? $post_categories[0]->name : '',
            ];
        }
        wp_reset_postdata();
    }

    /*===========================================================*/
    // Video posts

    $items_per_page = 10;

    $videos_data = $wpdb->get_results(
        $wpdb->prepare(
            'SELECT * FROM escapezoom_videos ORDER BY created_at DESC LIMIT 0, %d',
            (int) $items_per_page
        )
    );

    foreach ($videos_data as $video_data) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://napi.arvancloud.ir/vod/2.0/videos/" . $video_data->video_id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array('Authorization: apikey bf22fa73-07a1-5429-a049-95ddc7c65a5e'),
        ));
        $response = json_decode(curl_exec($curl));
        curl_close($curl);

        $video_duration = $response->data->file_info->general->duration;

        $videos[] = [
            'title'     => $video_data->video_title,
            'cover'     => $response->data->thumbnail_url,
            'tag'       => $video_data->video_tag,
            'duration'  => sprintf("%02d:%02d", floor(($video_duration % 3600) / 60), $video_duration % 60),
            'src'       => "https://player.arvancloud.ir/index.html?config=" . $response->data->config_url,
        ];
    }

    /*===========================================================*/

    $data[] = [
        'title' => 'بلاگ',
        'tabs'  => [
            [
                'type'  => 'category',
                'title' => 'موضوعات سایت',
                'key'   => 'cat_id',
                'items' => [
                    [
                        'title' => 'همه',
                        'value' => -1,
                    ],
                    [
                        'title' => 'اخبار',
                        'value' => 1,
                    ],
                    [
                        'title' => 'نقدو بررسی',
                        'value' => 805,
                    ],
                ],
            ]
        ],
        'items' => [
            'header'    => $items_header,
            'recent'    => $items_recent,
            'videos'    => [
                'see_all_url'   => '/blog/ویدئو/',
                'items'         => $videos
            ],
        ],
        'pagination'    => [
            'current_page'  => (int)$page_num,
            'total_pages'   => $query_body->max_num_pages
        ],
        'breadcrumb'    => [
            [
                'title' => 'صفحه اصلی',
                'url'   => '/',
            ],
            [
                'title' => 'بلاگ',
                'url'   => '/blog',
            ],
        ],
    ];

    wp_send_json_success($data);
}
//**********************************************************************************************************/
function post_get_comments_api($request)
{

    $user_id = get_user_id_by_token(ez_authorization(false));

    $params = $request->get_params();
    $param  = $params['param'];
    $page   = $params['page'];

    if (is_numeric($param)) {
        $post = get_post($param);
        $post_id = (int)$param;
    } else {
        $post = get_page_by_path($param, OBJECT, 'post');
        $post_id = $post->ID;
    }

    if (!$post)
        wp_send_json_error(null, 404);

    $comments_per_page  = 5;
    $total_comments     = wp_count_comments($post_id)->approved;

    $comments_list = get_comments(array(
        'post_id'   => $post_id,
        'status'    => 'approve',
        'parent'    => 0,
        'orderby'   => 'comment_date',
        'order'     => 'DESC',
        'number'    => $comments_per_page,
        'offset'    => ($page - 1) * $comments_per_page,
    ));

    if (!empty($comments_list)) {
        foreach ($comments_list as $comment) {
            $comment_id = $comment->comment_ID;

            $replies = get_post_reply_comments($comment_id);

            $comments[] = [
                'comment_id'    => (int)$comment_id,
                'author_title'  => get_user_by('id', $comment->user_id)->data->display_name ?: $comment->comment_author,
                'author_image'  => get_user_meta($comment->user_id, 'user_avatar', true) ?: 'http://escapezoom.ir/wp-content/uploads/2024/04/male_avatar_level_1.png',
                'author_level'  => get_user_meta($comment->user_id, 'level', true) ?: 1,
                'content'       => $comment->comment_content,
                'date'          => strtotime($comment->comment_date),
                'replies'       => $replies
            ];
        }
    }

    $data = [
        'items'         => $comments,
        'pagination'    => [
            'current_page'  => (int)$page,
            'total_pages'   => ceil($total_comments / $comments_per_page),
        ],
    ];

    wp_send_json_success($data);
}
//**********************************************************************************************************/
function post_add_comment_api($request)
{

    $user_id = get_user_id_by_token(ez_authorization(false));

    $params = $request->get_params();
    $post_id        = $params['post_id'];
    $comment_id     = $params['comment_id'];
    $content        = $params['content'];
    $author_name    = $params['author_name'];

    if (!isset($post_id) || empty($post_id))
        wp_send_json_error(array('error' => 'شماره مقاله مشخص نیست.'), 400);

    if (!isset($content) || empty($content))
        wp_send_json_error(array('error' => 'پاسخ شما مشخص نیست.'), 400);

    if (!isset($author_name) || empty($author_name))
        wp_send_json_error(array('error' => 'نام خود را وارد کنید.'), 400);

    if (!get_post($post_id))
        wp_send_json_error(null, 404);

    $comment_data = array(
        'comment_post_ID'       => $post_id,
        'comment_author'        => $author_name,
        'comment_author_email'  => 'nothing@escapezoom.ir',
        'comment_content'       => $content,
        'comment_approved'      => 1,
    );

    if ($comment_id) {
        $parent_comment = get_comment($comment_id);

        if (!$parent_comment)
            wp_send_json_error(null, 404);

        $comment_data['comment_parent'] = $comment_id;
    }

    $comment_id = wp_insert_comment($comment_data);

    if (is_wp_error($comment_id))
        wp_send_json_error(null, 403);

    wp_send_json_success('نظر شما ثبت شد! پس از تایید نمایش داده می شود.');
}
//**********************************************************************************************************/
function post_add_rate_api($request)
{

    $user_id = get_user_id_by_token(ez_authorization(false));

    $params = $request->get_params();
    $post_id    = $params['post_id'];
    $rate       = $params['rate'];

    if (!isset($post_id) || empty($post_id))
        wp_send_json_error(array('error' => 'شماره مقاله مشخص نیست.'), 400);

    if (!isset($rate) || empty($rate))
        wp_send_json_error(array('error' => 'امتیاز شما مشخص نیست.'), 400);

    if (!get_post($post_id))
        wp_send_json_error(null, 404);

    $rate = intval($rate);
    if ($rate < 1 || $rate > 5)
        wp_send_json_error(null, 404);

    $current_sum    = (int) get_post_meta($post_id, 'rmp_rating_val_sum', true);
    $current_count  = (int) get_post_meta($post_id, 'rmp_vote_count', true);

    if ($current_count === 0) {
        $current_sum    = 0;
        $current_count  = 0;
    }

    $new_sum    = $current_sum + $rate;
    $new_count  = $current_count + 1;

    $avg_rating = $new_sum / $new_count;

    update_post_meta($post_id, 'rmp_rating_val_sum', $new_sum);
    update_post_meta($post_id, 'rmp_vote_count', $new_count);
    update_post_meta($post_id, 'rmp_avg_rating', $avg_rating);

    wp_send_json_success('امتیاز شما ثبت شد! تشکر از شما');
}
//**********************************************************************************************************/
function post_videos_api($request)
{
    global $wpdb;

    $user_id = get_user_id_by_token(ez_authorization(false));

    $params = $request->get_params();
    $page_num = $params['page'];

    $page_num = $page_num ?: 1;

    $items_per_page = 10;
    $offset         = ($page_num - 1) * $items_per_page;

    $max_page_num = ceil((int)($wpdb->get_var("SELECT COUNT(*) FROM  `escapezoom_videos`")) / $items_per_page);

    $videos_data = $wpdb->get_results(
        $wpdb->prepare(
            'SELECT * FROM escapezoom_videos ORDER BY created_at DESC LIMIT %d, %d',
            (int) $offset,
            (int) $items_per_page
        )
    );

    foreach ($videos_data as $video_data) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://napi.arvancloud.ir/vod/2.0/videos/" . $video_data->video_id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array('Authorization: apikey bf22fa73-07a1-5429-a049-95ddc7c65a5e'),
        ));
        $response = json_decode(curl_exec($curl));
        curl_close($curl);

        $video_duration = $response->data->file_info->general->duration;

        $videos[] = [
            'title'     => $video_data->video_title,
            'cover'     => $response->data->thumbnail_url,
            'tag'       => $video_data->video_tag,
            'duration'  => sprintf("%02d:%02d", floor(($video_duration % 3600) / 60), $video_duration % 60),
            'src'       => "https://player.arvancloud.ir/index.html?config=" . $response->data->config_url,
        ];
    }

    $data[] = [
        'title'         => 'ویدئوها',
        'tabs'          => [],
        'items'         => $videos,
        'pagination'    => [
            'current_page'  => (int)$page_num,
            'total_pages'   => $max_page_num
        ],
        'breadcrumb'    => [
            [
                'title' => 'صفحه اصلی',
                'url'   => '/',
            ],
            [
                'title' => 'بلاگ',
                'url'   => '/blog',
            ],
            [
                'title' => 'ویدئوها',
                'url'   => '/blog/videos',
            ],
        ],
    ];

    wp_send_json_success($data);
}

/*=========================================================================================================*/
//Auth functions

function auth_login_api($request)
{
    global $wldb;

    $params = $request->get_params();
    $mobile     = $params['phone'];
    $username   = $params['username'];
    $password   = $params['password'];

    if (isset($mobile)) :

        if (!isset($mobile) || empty($mobile))
            wp_send_json_error(array('error' => 'موبایل را وارد کنید'), 400);

        try {
            $mobile = ez_validate_mobile(trim($mobile));
        } catch (Exception $e) {
            wp_send_json_error(array('error' => $e->getMessage()), 400);
        }

        try {
            $verify = wp_rand('1000', '9999');

            $mobile = substr($mobile, 0, 1) === '0' ? substr($mobile, 1) : $mobile;

            $user = get_user_by('login', $mobile);
            if ($user) { // یوزر از قبل موجود است پس otp براش فرستاده میشه
                $otp_send_time = get_user_meta($user->ID, 'otp_send_time', true);
                $otp_send_time = $otp_send_time ?: 0;

                if (($diff_time = (time() - $otp_send_time - 60) * -1) < 0)
                    throw new Exception("پیامک برای شما ارسال شده است لطفا منتظر باشید و اگر پیامکی دریافت نکردید بعد از $diff_time ثانیه دوباره امتحان کنید");

                update_user_meta($user->ID, 'otp_send_time', time());
                update_user_meta($user->ID, 'otp', $verify);
            } else {

                $user_id = wp_create_user($mobile, wp_generate_password(), "$mobile@" . ez_get_domain());

                (new WP_User($user_id))->set_role('customer');

                update_user_meta($user_id, 'otp_send_time', time());
                update_user_meta($user_id, 'otp', $verify);
                update_user_meta($user_id, 'billing_phone', '0' . $mobile);
            }

            try {
                ez_sendpayamak3($mobile, 'کد تایید شما: ' . $verify . "\n\n اسکیپ زوم");
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }

            wp_send_json_success(true);
        } catch (Exception $e) {
            wp_send_json_error(array('error' => $e->getMessage()), 400);
        }

    elseif (isset($username)) :

        if (empty($username))
            wp_send_json_error(array('error' => 'نام کاربری را وارد کنید'), 400);

        if (!isset($password) || empty($password))
            wp_send_json_error(array('error' => 'رمز عبور را وارد کنید'), 400);

        $username = substr($username, 0, 1) === '0' ? substr($username, 1) : $username;

        $user = wp_authenticate_username_password(null, $username, $password);

        if (is_wp_error($user))
            if (strpos($user->get_error_message(), 'lost') !== false)
                wp_send_json_error(array('error' => 'رمز وارد شده صحیح نیست.'), 400);
            elseif (strpos($user->get_error_message(), 'اگر از نام کاربری خود مطمئن نیستید،') !== false)
                wp_send_json_error(array('error' => 'نام کاربری یافت نشد.'), 400);

        $data = generate_jwt_token($user);

        $user_data = get_user_meta($user->ID, 'user_settings', true);
        if (empty($user_data))
            $user_data = [];

        $role = (get_userdata($user->ID)->roles)[0];
        if ($role == 'customer')
            $role = 'customer';
        elseif ($role == 'compiler')
            $role = 'owner';
        elseif ($role == 'sans_manager')
            $role = 'sans_manager';

        $user_data['avatar']    = 'http://escapezoom.ir/wp-content/uploads/2024/04/male_avatar_level_1.png';
        $user_data['user_id']   = $user->ID;
        $user_data['name']      = $user->user_nicename;
        $user_data['phone']     = $user->user_login;
        $user_data['balance']   = $wldb->get_balance($user->ID);
        $user_data['points']    = (int)get_user_points($user->ID);
        $user_data['role']      = $role;
        $user_data['token']     = apply_filters('jwt_auth_token_before_dispatch', $data, $user)['token'];

        wp_send_json_success($user_data);
    endif;
}
/**********************************************************************************************************/
function auth_verify_api($request)
{
    global $wldb;

    $params = $request->get_params();
    $mobile = $params['phone'];
    $otp    = $params['otp'];
    $web    = $params['web'];

    if (!isset($mobile) || empty($mobile))
        wp_send_json_error(array('error' => 'موبایل را وارد کنید'), 400);

    if (!isset($otp) || empty($otp))
        wp_send_json_error(array('error' => 'کد ارسال شده را وارد کنید'), 400);

    try {
        $mobile = ez_validate_mobile($mobile);
    } catch (Exception $e) {
        wp_send_json_error(array('error' => $e->getMessage()), 400);
    }

    try {

        $user = get_user_by('login', $mobile);
        if ($user) {
            $saved_otp = get_user_meta($user->ID, 'otp', true);

            if ($saved_otp != $otp)
                throw new Exception('کد وارد شده اشتباه است.');
        } else
            throw new Exception('کاربر پیدا نشد');

        if ($web) {

            $billing_first_name = get_user_meta($user->ID, 'billing_first_name', true);

            if (isset($billing_first_name) and !empty($billing_first_name))
                wp_send_json_success('old_user');

            wp_send_json_success('new_user');
        } else {

            $data = generate_jwt_token($user);

            $user_data = get_user_meta($user->ID, 'user_settings', true);
            if (empty($user_data))
                $user_data = [];

            $role = (get_userdata($user->ID)->roles)[0];

            $user_data['avatar']    = 'http://escapezoom.ir/wp-content/uploads/2024/04/male_avatar_level_1.png';
            $user_data['user_id']   = $user->ID;
            $user_data['name']      = $user->user_nicename;
            $user_data['phone']     = $user->user_login;
            $user_data['balance']   = $wldb->get_balance($user->ID);
            $user_data['points']    = (int)get_user_points($user->ID);
            $user_data['role']      = $role;
            $user_data['token']     = apply_filters('jwt_auth_token_before_dispatch', $data, $user)['token'];

            wp_send_json_success($user_data);
        }
    } catch (Exception $e) {
        wp_send_json_error(array('error' => $e->getMessage()), 400);
    }
}
/**********************************************************************************************************/
function auth_info_api($request)
{
    global $wldb;

    $params = $request->get_params();
    $mobile     = $params['phone'];
    $first_name = $params['first_name'];
    $last_name  = $params['last_name'];

    if (!isset($mobile) || empty($mobile))
        wp_send_json_error(array('error' => 'موبایل را وارد کنید'), 400);

    if (!isset($first_name) || empty($first_name))
        wp_send_json_error(array('error' => 'نام را وارد کنید.'), 400);

    if (!isset($last_name) || empty($last_name))
        wp_send_json_error(array('error' => 'نام خانوادگی را وارد کنید.'), 400);

    try {
        $mobile = ez_validate_mobile($mobile);
    } catch (Exception $e) {
        wp_send_json_error(array('error' => $e->getMessage()), 400);
    }

    try {
        $user = get_user_by('login', $mobile);

        if (!$user)
            throw new Exception('کاربر پیدا نشد!');

        update_user_meta($user->ID, 'billing_first_name', $first_name);
        update_user_meta($user->ID, 'billing_last_name', $last_name);

        if (ez_login_automatically($user))
            wp_send_json_success('true');

        wp_send_json_error(array('error' => $e->getMessage()), 400);
    } catch (Exception $e) {
        wp_send_json_error(array('error' => $e->getMessage()), 400);
    }
}
/**********************************************************************************************************/
function auth_login_owners_api($request)
{
    global $wldb;

    $params = $request->get_params();
    $mobile     = $params['phone'];
    $username   = $params['username'];
    $password   = $params['password'];

    if (isset($mobile)) :

        if (!isset($mobile) || empty($mobile))
            wp_send_json_error(array('error' => 'موبایل را وارد کنید'), 400);

        try {
            $mobile = ez_validate_mobile(trim($mobile));
        } catch (Exception $e) {
            wp_send_json_error(array('error' => $e->getMessage()), 400);
        }

        try {

            $verify = wp_rand('1000', '9999');

            $mobile = substr($mobile, 0, 1) === '0' ? substr($mobile, 1) : $mobile;

            $user = get_user_by('login', $mobile);

            if ($user) {

                $role = (get_userdata($user->ID)->roles)[0];
                if ($role != 'compiler')
                    wp_send_json_error(array('error' => 'این شماره به هیچ مجموعه داری تعلق ندارد!'), 400);

                $otp_send_time = get_user_meta($user->ID, 'otp_send_time', true);
                $otp_send_time = $otp_send_time ?: 0;

                if (($diff_time = (time() - $otp_send_time - 60) * -1) > 0)
                    throw new Exception("پیامک برای شما ارسال شده است لطفا منتظر باشید و اگر پیامکی دریافت نکردید بعد از $diff_time ثانیه دوباره امتحان کنید");

                update_user_meta($user->ID, 'otp_send_time', time());
                update_user_meta($user->ID, 'otp', $verify);
            } else
                wp_send_json_error(array('error' => 'این شماره وجود ندارد!'), 400);

            try {
                add_to_sms_queue($mobile,  'کد تایید شما: ' . $verify . "\n\n اسکیپ زوم", 0, 'owner_otp');
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }

            wp_send_json_success(true);
        } catch (Exception $e) {
            wp_send_json_error(array('error' => $e->getMessage()), 400);
        }

    elseif (isset($username)) :

        if (empty($username))
            wp_send_json_error(array('error' => 'نام کاربری را وارد کنید'), 400);

        if (!isset($password) || empty($password))
            wp_send_json_error(array('error' => 'رمز عبور را وارد کنید'), 400);

        $username = substr($username, 0, 1) === '0' ? substr($username, 1) : $username;

        $user = wp_authenticate_username_password(null, $username, $password);

        if (is_wp_error($user))
            if (strpos($user->get_error_message(), 'lost') !== false)
                wp_send_json_error(array('error' => 'رمز وارد شده صحیح نیست.'), 400);
            elseif (strpos($user->get_error_message(), 'اگر از نام کاربری خود مطمئن نیستید،') !== false)
                wp_send_json_error(array('error' => 'نام کاربری یافت نشد.'), 400);

        $data = generate_jwt_token($user);

        $user_data = get_user_meta($user->ID, 'user_settings', true);
        if (empty($user_data))
            $user_data = [];

        $role = (get_userdata($user->ID)->roles)[0];
        if ($role == 'customer')
            $role = 'customer';
        elseif ($role == 'compiler')
            $role = 'owner';
        elseif ($role == 'sans_manager')
            $role = 'sans_manager';

        $user_data['avatar']    = 'http://escapezoom.ir/wp-content/uploads/2024/04/male_avatar_level_1.png';
        $user_data['user_id']   = $user->ID;
        $user_data['name']      = $user->user_nicename;
        $user_data['phone']     = $user->user_login;
        $user_data['balance']   = $wldb->get_balance($user->ID);
        $user_data['points']    = (int)get_user_points($user->ID);
        $user_data['role']      = $role;
        $user_data['token']     = apply_filters('jwt_auth_token_before_dispatch', $data, $user)['token'];

        wp_send_json_success($user_data);
    endif;
}

/*=========================================================================================================*/
//Brand functions

function brand_get_api($request)
{

    $params = $request->get_params();
    $param  = $params['param'];

    if (is_numeric($param))
        $brand = get_term_by('id', $param, 'product_brand');
    else
        $brand = get_term_by('slug', $param, 'product_brand');

    if (!$brand)
        wp_send_json_error(null, 404);

    $brand_id = $brand->term_id;

    $brand_img_id = get_term_meta($brand_id, 'thumbnail_id', true);
    if ($brand_img_id > 0)
        $image = wp_get_attachment_image_src($brand_img_id, 'full')[0];

    $posts_per_page = 10;
    $sort_type      = 'popular';
    $params = [
        'brand_id' => $brand_id,
    ];
    $args = [
        'params'        => $params,
        'image_type'    => 'url',
        'limit'         => $posts_per_page,
        'page'          => 1,
        'max_num_pages' => false,
        "format"        => 'api',
        'sort_type'     => $sort_type,
        'unpin_ads'     => false,
        'badge_ads'     => false,
        'random'        => false,
        'random_memory' => '',
        'show_more'     => 0,
    ];
    $products = json_decode(ez_webservice(array('type' => 'sort_products_get', 'data' => $args)));

    $data = [
        'id'            => $brand_id,
        'title'         => $brand->name,
        'image'         => $image,
        'description'   => $brand->description,
        'team'          => [
            'title' => 'اعضا',
            'items' => [
                [
                    'name'      => 'حسین یعقوبی',
                    'position'  => 'مدیرعامل مجموعه',
                    'image'     => 'https://escapezoom.ir/wp-content/uploads/2024/03/signs-of-exorcism.jpg',
                ],
                [
                    'name'      => 'حسین یعقوبی',
                    'position'  => 'مدیرعامل مجموعه',
                    'image'     => 'https://escapezoom.ir/wp-content/uploads/2024/03/signs-of-exorcism.jpg',
                ],
                [
                    'name'      => 'حسین یعقوبی',
                    'position'  => 'مدیرعامل مجموعه',
                    'image'     => 'https://escapezoom.ir/wp-content/uploads/2024/03/signs-of-exorcism.jpg',
                ],
                [
                    'name'      => 'حسین یعقوبی',
                    'position'  => 'مدیرعامل مجموعه',
                    'image'     => 'https://escapezoom.ir/wp-content/uploads/2024/03/signs-of-exorcism.jpg',
                ],
            ],
        ],
        'products'      => [
            'title' => 'اتاق فرارهای این برند',
            'items' => $products,
        ],
        'breadcrumb'    => [
            [
                'title' => 'صفحه اصلی',
                'url'   => '/',
            ],
            [
                'title' => 'برندها',
                'url'   => '/brands',
            ],
            [
                'title' => $brand->name,
                'url'   => '',
            ],
        ],
    ];

    wp_send_json_success($data);
}
/**********************************************************************************************************/
function brand_get_all_api($request)
{

    $params = $request->get_params();
    $page_num = isset($params['page']) ? (int) $params['page'] : 1;
    $page_num = max(1, $page_num);

    $order_mode = (isset($params['order']) && $params['order'] === 'new') ? 'new' : 'popular';

    $terms_per_page = 24;

    $items = [];

    if (!taxonomy_exists('product_brand')) {
        wp_send_json_success([
            'title' => 'برندها',
            'items' => [],
            'pagination' => [
                'current_page' => 1,
                'total_pages' => 0,
            ],
            'breadcrumb' => [
                [
                    'title' => 'صفحه اصلی',
                    'url' => '/',
                ],
                [
                    'title' => 'برندها',
                    'url' => '/brands/',
                ],
            ],
        ]);
        return;
    }

    $args = ez_brands_directory_terms_query_args($page_num, $terms_per_page, $order_mode);
    $brands = get_terms($args);

    if (is_wp_error($brands)) {
        $brands = [];
    }

    foreach ($brands as $brand) {

        $brand_id = $brand->term_id;

        $brand_img_id = get_term_meta($brand_id, 'thumbnail_id', true);
        $image = '';
        if ($brand_img_id > 0) {
            $src = wp_get_attachment_image_src((int) $brand_img_id, 'full');
            if (is_array($src) && isset($src[0])) {
                $image = $src[0];
            }
        }

        $items[] = [
            'id'    => $brand_id,
            'title' => $brand->name,
            'image' => $image,
            'url'   => trim_home_url(get_term_link($brand)),
        ];
    }

    $total_terms = ez_brands_directory_count_terms($order_mode);
    $total_pages = $terms_per_page > 0 ? (int) ceil($total_terms / $terms_per_page) : 1;

    $data = [
        'title'         => 'برندها',
        'items'         => $items,
        'pagination'    => [
            'current_page'  => (int)$page_num,
            'total_pages'   => $total_pages,
            'order'         => $order_mode === 'new' ? 'new' : 'popular',
        ],
        'breadcrumb'    => [
            [
                'title' => 'صفحه اصلی',
                'url'   => '/',
            ],
            [
                'title' => 'برندها',
                'url'   => '/brands/',
            ],
        ],
    ];

    wp_send_json_success($data);
}

/*=========================================================================================================*/
//Other functions

function home_api($request)
{
    global $wpdb;

    $user_id = get_user_id_by_token(ez_authorization(false));

    $ez_admin_settings = get_option('ez_admin_settings');

    foreach (get_terms(['taxonomy' => 'product_cat']) as $category)
        $cities[] = ['id' => $category->term_id, 'title' => $category->name];

    foreach (get_terms('product_tag') as $tag)
        $tags[] = ['id' => $tag->term_id, 'title' => $tag->name];

    $data = [];

    /*===============================================================*/
    // اسلایدشو

    $data[] = [
        'type'  => 'slideshow',
        'title' => '',
        'icon'  => '',
        'data'  => [
            'slide_time'    => 5,
            'items'         => [
                [
                    'image'         => 'https://escapezoom.ir/wp-content/uploads/2024/06/12.png',
                    'color'         => '#edf2f5',
                    'title'         => 'اتاق فرارتو همین الان رزرو کن',
                    'description'   => 'لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ، و با استفاده از طراحان گرافیک است، چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است، و برای شرایط فعلی تکنولوژی مورد نیاز، و کاربردهای متنوع با هدف بهبود ابزارهای کاربردی می باشد، کتابهای زیادی در شصت و سه درصد گذشته حال و آینده، شناخت فراوان جامعه و متخصصان را می طلبد، تا با نرم افزارها شناخت بیشتری را برای طراحان رایانه ای علی الخصوص طراحان خلاقی، و فرهنگ پیشرو در زبان فارسی ایجاد کرد، در این صورت می توان امید داشت که تمام و دشواری موجود در ارائه راهکارها، و شرایط سخت تایپ به پایان رسد و زمان مورد نیاز شامل حروفچینی دستاوردهای اصلی، و جوابگوی سوالات پیوسته اهل دنیای موجود طراحی اساسا مورد استفاده قرار گیرد.',
                    'url'           => '/city/%d8%a7%d8%aa%d8%a7%d9%82-%d9%81%d8%b1%d8%a7%d8%b1/',
                    'items'         => [
                        [
                            'title' => 'تهران',
                            'url'   => '/city/%D8%A7%D8%AA%D8%A7%D9%82-%D9%81%D8%B1%D8%A7%D8%B1-%D8%AA%D9%87%D8%B1%D8%A7%D9%86/',
                        ],
                        [
                            'title' => 'کرج',
                            'url'   => '/city/%DA%A9%D8%B1%D8%AC/',
                        ],
                        [
                            'title' => 'اراک',
                            'url'   => '/city/%D8%A7%D8%B1%D8%A7%DA%A9/',
                        ],
                        [
                            'title' => 'مشهد',
                            'url'   => '/city/%D9%85%D8%B4%D9%87%D8%AF/',
                        ],
                        [
                            'title' => 'اصفهان',
                            'url'   => '/city/%D8%A7%D8%B5%D9%81%D9%87%D8%A7%D9%86/',
                        ],
                        [
                            'title' => 'کاشان',
                            'url'   => '/city/%DA%A9%D8%A7%D8%B4%D8%A7%D9%86/',
                        ],
                        [
                            'title' => 'کرمانشاه',
                            'url'   => '/city/%DA%A9%D8%B1%D9%85%D8%A7%D9%86%D8%B4%D8%A7%D9%87/',
                        ],
                        [
                            'title' => 'قم',
                            'url'   => '/city/%D9%82%D9%85/',
                        ],
                        [
                            'title' => 'اهواز',
                            'url'   => '/city/%D8%A7%D9%87%D9%88%D8%A7%D8%B2/',
                        ],
                    ]
                ],
            ],
        ]
    ];

    /*===============================================================*/
    // پیشنهادها برای شما

    //    $posts_per_page = 5;
    //    $sort_type      = 'popular';
    //    $params = [
    //        'city_id' => [15],
    //    ];
    //    $args = [
    //        'params'        => $params,
    //        'image_type'    => 'url',
    //        'limit'         => $posts_per_page,
    //        'page'          => 1,
    //        'max_num_pages' => false,
    //        "format"        => 'api',
    //        'sort_type'     => $sort_type,
    //        'unpin_ads'     => false,
    //        'badge_ads'     => false,
    //        'random'        => false,
    //        'random_memory' => '',
    //        'show_more'     => 0,
    //    ];
    //    $products = json_decode( ez_webservice( array ('type' => 'sort_products_get', 'data' => $args) ) );
    //
    //    $data[] = [
    //        'type'  => 'products_slider',
    //        'title' => 'پیشنهادها برای شما',
    //        'icon'  => '',
    //        'url'   => '/city/%d8%a7%d8%aa%d8%a7%d9%82-%d9%81%d8%b1%d8%a7%d8%b1/',
    //        'data'  => [
    //            'tabs'  => [],
    //            'items' => $products,
    //        ]
    //    ];

    /*===============================================================*/
    // اتاق فرارهای ترند

    $args = [
        "source" => "home_trends"
    ];
    $products = json_decode(ez_webservice(array('type' => 'sort_products_get', 'data' => $args)))->products;

    $data[] = [
        'type'  => 'products_slider',
        'source' => 'home_trends',
        'title' => 'اتاق فرارهای <b>ترند</b> و دارای سانس',
        'icon'  => '',
        'url'   => '',
        'data'  => [
            'tabs'  => [
                [
                    'type'  => 'schedule',
                    'title' => 'سانس آزاد برای',
                    'key'   => 'schedule',
                    'items' => [
                        [
                            'title' => 'همه',
                            'min'   => -1,
                            'max'   => -1,
                        ],
                        [
                            'title' => 'فقط امروز',
                            'min'   => 'dynamic',
                            'max'   => 'dynamic',
                        ],
                        [
                            'title' => 'فقط فردا',
                            'min'   => 'dynamic',
                            'max'   => 'dynamic',
                        ],
                        [
                            'title' => 'فقط پس فردا',
                            'min'   => 'dynamic',
                            'max'   => 'dynamic',
                        ],
                    ],
                ]
            ],
            'items' => $products,
        ]
    ];

    /*===============================================================*/
    // جستجو سریع

    $data[] = [
        'type'  => 'quick_search',
        'source' => 'home_quick_search',
        'title' => 'جستجو هوشمند',
        'icon'  => '',
        'data'  => [
            'tabs' => [
                [
                    'type'  => 'product_type',
                    'title' => 'نوع سرگرمی',
                    'key'   => 'product_type',
                    'items' => [
                        [
                            'title' => 'همه',
                            'value' => -1,
                        ],
                        [
                            'title' => 'اتاق فرار',
                            'value' => 'اتاق فرار',
                        ],
                        [
                            'title' => 'لیزرتگ',
                            'value' => 'لیزرتگ',
                        ],
                        [
                            'title' => 'سینماترس',
                            'value' => 'سینماترس',
                        ]
                    ],
                ],
                [
                    'type'  => 'city_id',
                    'title' => 'شهر',
                    'key'   => 'city_id',
                    'items' => [
                        [
                            'title' => 'همه',
                            'value' => -1,
                        ],
                        [
                            'title' => 'تهران',
                            'value' => 15,
                        ],
                        [
                            'title' => 'کرج',
                            'value' => 162,
                        ],
                        [
                            'title' => 'اصفهان',
                            'value' => 122,
                        ],
                        [
                            'title' => 'مشهد',
                            'value' => 121,
                        ],
                        [
                            'title' => 'کرمانشاه',
                            'value' => 293,
                        ],
                        [
                            'title' => 'قزوین',
                            'value' => 270,
                        ],
                        [
                            'title' => 'کاشان',
                            'value' => 304,
                        ],
                    ],
                ],
                [
                    'type'  => 'count',
                    'title' => 'تعداد نفرات',
                    'key'   => 'count',
                    'items' => [
                        'default'   => [
                            'title' => 'همه',
                            'value' => -1,
                        ],
                        'min'       => 1,
                        'max'       => 16,
                    ],
                ],
                [
                    'type'  => 'schedule',
                    'title' => '',
                    'key'   => 'schedule',
                    'items' => [
                        [
                            'title' => 'امروز',
                            'min'   => 'dynamic',
                            'max'   => 'dynamic',
                        ],
                        [
                            'title' => 'فردا',
                            'min'   => 'dynamic',
                            'max'   => 'dynamic',
                        ],
                        [
                            'title' => 'پس فردا',
                            'min'   => 'dynamic',
                            'max'   => 'dynamic',
                        ],
                    ],
                ],
            ],
        ]
    ];

    /*===============================================================*/
    // اتاق فرارهای تهران

    $params = [
        'city_id' => [15],
    ];
    $args = [
        "source"    => "home_cities_escaperoom",
        'params'    => $params,
        'sort_type' => 'popular',
    ];
    $products = json_decode(ez_webservice(array('type' => 'sort_products_get', 'data' => $args)));

    $data[] = [
        'type'  => 'products_slider',
        'source' => 'home_cities_escaperoom',
        'title' => 'اتاق فرارهای <b>تهران</b> و دارای سانس',
        'icon'  => '',
        'url'   => '/city/%D8%A7%D8%AA%D8%A7%D9%82-%D9%81%D8%B1%D8%A7%D8%B1-%D8%AA%D9%87%D8%B1%D8%A7%D9%86/',
        'data'  => [
            'slide_time'    => 5,
            'items'         => $products,
            'tabs'          => [
                [
                    'type'  => 'city_id',
                    'title' => 'شهر مورد نظر',
                    'key'   => 'city_id',
                    'items' => [
                        [
                            'title' => 'تهران',
                            'value' => 15,
                        ],
                        [
                            'title' => 'کرج',
                            'value' => 162,
                        ],
                        [
                            'title' => 'اصفهان',
                            'value' => 122,
                        ],
                        [
                            'title' => 'مشهد',
                            'value' => 121,
                        ],
                        [
                            'title' => 'کرمانشاه',
                            'value' => 293,
                        ],
                        [
                            'title' => 'قزوین',
                            'value' => 270,
                        ],
                        [
                            'title' => 'کاشان',
                            'value' => 304,
                        ],
                    ],
                ],
                [
                    'type'  => 'tag',
                    'title' => 'سبک بازی',
                    'key'   => 'tag',
                    'items' => [
                        [
                            'title' => 'ترسناک',
                            'value' => 124,
                        ],
                        [
                            'title' => 'اکشن',
                            'value' => 346,
                        ],
                        [
                            'title' => 'درام',
                            'value' => 342,
                        ],
                        [
                            'title' => 'دلهره آور',
                            'value' => 126,
                        ],
                        [
                            'title' => 'غیرترسناک',
                            'value' => 125,
                        ],
                        [
                            'title' => 'هیجانی',
                            'value' => 178,
                        ],
                        [
                            'title' => 'جنایی',
                            'value' => 127,
                        ],
                    ],
                ],
                [
                    'type'  => 'order',
                    'title' => 'براساس',
                    'key'   => 'sort_type',
                    'items' => [
                        [
                            'title' => 'محبوب ترین ها',
                            'id'    => 'popular',
                        ],
                        [
                            'title' => 'پرفروش ترین ها',
                            'id'    => 'topsale',
                        ],
                        [
                            'title' => 'جدیدترین ها',
                            'id'    => 'recent',
                        ],
                    ],
                ],
            ],
        ]
    ];

    /*===============================================================*/
    // بنر

    $data[] = [
        'type'  => 'banner1',
        'title' => '',
        'icon'  => '',
        'data'  => [
            'image' => 'http://escapezoom.ir/wp-content/uploads/2024/10/Tehran-Nights-forever.jpg',
            'url'   => '',
            'text'  => 'نزدیکترین <b style="color=#f00">اتاق فرار</b> را بر روی نقشه پیدا کنید',
            'btn'   => [
                'text'  => 'مشاهده نقشه',
                'url'   => '/city/%D8%A7%D8%AA%D8%A7%D9%82-%D9%81%D8%B1%D8%A7%D8%B1-%D8%AA%D9%87%D8%B1%D8%A7%D9%86/',
                'color' => '#02ff8e'
            ],
            'counter'   => [
                'text'  => 'مجموع اتاق فرارهای ایران',
                'count' => '1366',
            ],
        ]
    ];

    /*===============================================================*/
    // ژانرها

    $data[] = [
        'type'  => 'genres',
        'title' => '',
        'icon'  => '',
        'data'  => [
            'items' => [
                [
                    'image'     => ez_theme_asset_uri('images/genres/action.svg'),
                    'title'     => 'اکشن',
                    'popular'   => true,
                    'url'       => '/type/%D8%A7%DA%A9%D8%B4%D9%86/',
                ],
                [
                    'image'     => ez_theme_asset_uri('images/genres/non-scary.svg'),
                    'title'     => 'غیرترسناک',
                    'popular'   => false,
                    'url'       => '/type/%D8%A7%D8%AA%D8%A7%D9%82-%D9%81%D8%B1%D8%A7%D8%B1-%D8%AA%D8%B1%D8%B3%D9%86%D8%A7%DA%A9/',
                ],
                [
                    'image'     => ez_theme_asset_uri('images/genres/scary.svg'),
                    'title'     => 'ترسناک',
                    'popular'   => true,
                    'url'       => '/type/%D8%A7%DA%A9%D8%B4%D9%86/',
                ],
                [
                    'image'     => ez_theme_asset_uri('images/genres/dram.svg'),
                    'title'     => 'درام',
                    'popular'   => false,
                    'url'       => '/type/%D8%A7%D8%AA%D8%A7%D9%82-%D9%81%D8%B1%D8%A7%D8%B1-%D8%AA%D8%B1%D8%B3%D9%86%D8%A7%DA%A9/',
                ],
                [
                    'image'     => ez_theme_asset_uri('images/genres/exciting.svg'),
                    'title'     => 'هیجانی',
                    'popular'   => false,
                    'url'       => '/type/%D8%A7%DA%A9%D8%B4%D9%86/',
                ],
            ],
        ]
    ];

    /*===============================================================*/
    // تخفیف ویژه

    $args = [
        "source" => "home_discounts_event",
    ];
    $products = json_decode(ez_webservice(array('type' => 'sort_products_get', 'data' => $args)));

    $data[] = [
        'type'  => 'event',
        'source' => 'home_discounts_event',
        'title' => '<b>تخفیف های ویژه</b> و دارای سانس',
        'icon'  => ez_theme_asset_uri('images/genres/Takhfif.svg'),
        'url'   => '',
        'data'  => [
            'color' => '#eee',
            'items' => $products,
            'tabs'  => [
                'type'  => 'schedule',
                'title' => '',
                'key'   => 'schedule',
                'items' => [
                    [
                        'title' => 'همه',
                        'min'   => -1,
                        'max'   => -1,
                    ],
                    [
                        'title' => 'فقط امروز',
                        'min'   => 'dynamic',
                        'max'   => 'dynamic',
                    ],
                    [
                        'title' => 'فقط فردا',
                        'min'   => 'dynamic',
                        'max'   => 'dynamic',
                    ],
                    [
                        'title' => 'فقط پس فردا',
                        'min'   => 'dynamic',
                        'max'   => 'dynamic',
                    ],
                ],
            ],
        ]
    ];

    /*===============================================================*/
    // سینماترس های تهران

    $params = [
        'city_id' => [913],
    ];
    $args = [
        "source"    => "home_cities_cinema",
        'params'    => $params,
        'sort_type' => 'popular',
    ];
    $products = json_decode(ez_webservice(array('type' => 'sort_products_get', 'data' => $args)));

    $data[] = [
        'type'  => 'products_slider',
        'source' => 'home_cities_cinema',
        'title' => 'سینماترس های <b>تهران</b> و دارای سانس',
        'icon'  => '',
        'url'   => 'city/سینما-ترس/',
        'data'  => [
            'slide_time'    => 5,
            'items'         => $products,
            'tabs'          => [
                [
                    'type'  => 'city_id',
                    'title' => 'شهر مورد نظر',
                    'key'   => 'city_id',
                    'items' => [
                        [
                            'title' => 'تهران',
                            'value' => 913,
                        ],
                        [
                            'title' => 'کرج',
                            'value' => 1009,
                        ],
                        [
                            'title' => 'اصفهان',
                            'value' => 918,
                        ],
                        [
                            'title' => 'مشهد',
                            'value' => 904,
                        ],
                        [
                            'title' => 'کرمانشاه',
                            'value' => 926,
                        ],
                        [
                            'title' => 'سنندج',
                            'value' => 925,
                        ],
                        [
                            'title' => 'رشت',
                            'value' => 1004,
                        ],
                    ],
                ],
                [
                    'type'  => 'order',
                    'title' => 'براساس',
                    'key'   => 'sort_type',
                    'items' => [
                        [
                            'title' => 'محبوب ترین ها',
                            'id'    => 'popular',
                        ],
                        [
                            'title' => 'پرفروش ترین ها',
                            'id'    => 'topsale',
                        ],
                        [
                            'title' => 'جدیدترین ها',
                            'id'    => 'recent',
                        ],
                    ],
                ],
            ],
        ]
    ];

    /*===============================================================*/
    // جشنواره ها

    if (0) :

        $posts_per_page = 5;
        $sort_type      = 'popular';
        $args = [
            'params'        => [],
            'image_type'    => 'url',
            'limit'         => $posts_per_page,
            'page'          => 1,
            'max_num_pages' => false,
            "format"        => 'api',
            'sort_type'     => $sort_type,
            'only_events'   => true,
            'unpin_ads'     => false,
            'badge_ads'     => false,
            'random'        => false,
            'random_memory' => '',
            'show_more'     => 0,
        ];
        $products = json_decode(ez_webservice(array('type' => 'sort_products_get', 'data' => $args)));

        $data[] = [
            'type'  => 'event',
            'title' => '<b>جشنواره زمستانه</b>',
            'icon'  => 'http://escapezoom.ir/wp-content/uploads/2024/04/event_ico-1.png',
            'url'   => '',
            'data'  => [
                'color' => '#FF0000',
                'items' => $products,
                'tabs'  => [
                    'type'  => 'schedule',
                    'title' => '',
                    'key'   => 'schedule',
                    'items' => [
                        [
                            'title' => 'همه',
                            'min'   => -1,
                            'max'   => -1,
                        ],
                        [
                            'title' => 'فقط امروز',
                            'min'   => 'dynamic',
                            'max'   => 'dynamic',
                        ],
                        [
                            'title' => 'فقط فردا',
                            'min'   => 'dynamic',
                            'max'   => 'dynamic',
                        ],
                        [
                            'title' => 'فقط پس فردا',
                            'min'   => 'dynamic',
                            'max'   => 'dynamic',
                        ],
                    ],
                ],
            ]
        ];

    endif;

    /*===============================================================*/
    // محبوب ترین مجموعه ها

    $brands = get_terms([
        'taxonomy'      => 'product_brand',
        'hide_empty'    => false,
        'number'        => 500,
    ]);

    shuffle($brands);
    $brands = array_slice($brands, 0, 15);

    foreach ($brands as $brand) {
        $brand_id = $brand->term_id;

        $brand_img_id = get_term_meta($brand_id, 'thumbnail_id', true);
        if ($brand_img_id > 0)
            $image = wp_get_attachment_image_src($brand_img_id, 'full')[0];

        $brand_items[] = [
            'id'    => $brand_id,
            'title' => $brand->name,
            'image' => $image,
            'url'   => trim_home_url(get_term_link($brand)),
            'count' => 5,
        ];
    }

    $data[] = [
        'type'  => 'owners',
        'title' => 'میزبان های اسکیپ زوم',
        'icon'  => '',
        'url'   => '/brands/',
        'data'  => [
            'slide_time'    => 5,
            'items'         => $brand_items,
        ]
    ];

    /*===============================================================*/
    // بنر

    $data[] = [
        'type'  => 'banner2',
        'title' => '',
        'icon'  => '',
        'data'  => [
            'image'     => 'http://escapezoom.ir/wp-content/uploads/2024/10/Tehran-Nights-forever.jpg',
            'text'      => 'اتاق فرار دارید؟',
            'sub_text'  => 'اگر در شهر خود یک یا چندین اتاق فرار دارید با اسکیپ زوم می توانید فروش چندبرابری داشته باشید.',
            'btn'       => [
                'text'  => 'ثبت اتاق',
                'url'   => '/city/%D8%A7%D8%AA%D8%A7%D9%82-%D9%81%D8%B1%D8%A7%D8%B1-%D8%AA%D9%87%D8%B1%D8%A7%D9%86/',
                'color' => '#02ff8e'
            ],
        ]
    ];

    /*===============================================================*/
    // لیزرتگ های تهران

    $params = [
        'city_id' => [1147],
    ];
    $args = [
        "source"    => "home_cities_lasertag",
        'params'    => $params,
        'sort_type' => 'popular',
    ];
    $products = json_decode(ez_webservice(array('type' => 'sort_products_get', 'data' => $args)));

    $data[] = [
        'type'  => 'products_slider',
        'source' => 'home_cities_lasertag',
        'title' => 'لیزرتگ های <b>تهران</b> و دارای سانس',
        'icon'  => '',
        'url'   => '/city/%D8%A7%D8%AA%D8%A7%D9%82-%D9%81%D8%B1%D8%A7%D8%B1-%D8%AA%D9%87%D8%B1%D8%A7%D9%86/',
        'data'  => [
            'slide_time'    => 5,
            'items'         => $products,
            'tabs'          => [
                [
                    'type'  => 'city_id',
                    'title' => 'شهر مورد نظر',
                    'key'   => 'city_id',
                    'items' => [
                        [
                            'title' => 'تهران',
                            'value' => 1147,
                        ],
                        [
                            'title' => 'کرج',
                            'value' => 1149,
                        ],
                        [
                            'title' => 'اصفهان',
                            'value' => 1148,
                        ],
                        [
                            'title' => 'مشهد',
                            'value' => 1156,
                        ],
                        [
                            'title' => 'اردبیل',
                            'value' => 1151,
                        ],
                        [
                            'title' => 'قم',
                            'value' => 1158,
                        ],
                        [
                            'title' => 'گرگان',
                            'value' => 1150,
                        ],
                    ],
                ],
                [
                    'type'  => 'order',
                    'title' => 'براساس',
                    'key'   => 'sort_type',
                    'items' => [
                        [
                            'title' => 'محبوب ترین ها',
                            'id'    => 'popular',
                        ],
                        [
                            'title' => 'پرفروش ترین ها',
                            'id'    => 'topsale',
                        ],
                        [
                            'title' => 'جدیدترین ها',
                            'id'    => 'recent',
                        ],
                    ],
                ],
            ],
        ]
    ];

    /*===============================================================*/
    // کالکشن های محبوب

    $items_per_page = 10;

    $collections = $wpdb->get_results(
        $wpdb->prepare(
            'SELECT * FROM collections WHERE active = %d ORDER BY likes_count DESC LIMIT %d',
            1,
            (int) $items_per_page
        )
    );
    foreach ($collections as $collection) {

        $images = [];
        foreach (unserialize($collection->items) as $product_id)
            $images[] = wp_get_attachment_url(get_post_thumbnail_id($product_id));

        $collection_items[] =  [
            'title'         => $collection->title,
            'user_title'    => 'فاطمه خداپرست',
            'user_level'    => 2,
            'likes_count'   => (int)$collection->likes_count,
            'url'           => "/profile/" . (int)$collection->user_id,
            'count'         => count(unserialize($collection->items)),
            'items'         => $images,
        ];
    }

    $data[] = [
        'type'  => 'collections',
        'title' => 'کالکشن های محبوب کاربران',
        'icon'  => '',
        'url'   => '/collections/',
        'data'  => [
            'items' => $collection_items,
        ]
    ];

    /*===============================================================*/
    // مجله خبری

    $data[] = [
        'type'  => 'blog',
        'title' => 'مجله خبری و تصویری',
        'icon'  => '',
        'url'   => '/blog/',
        'data'  => [
            'slide_time'    => 5,
            'tabs'          => [
                [
                    'type'  => 'all',
                    'title' => '',
                    'key'   => 'blog_type',
                    'items' => [
                        [
                            'title' => 'وبلاگ',
                            'id'    => 'blog',
                        ],
                        [
                            'title' => 'ویدیو تیزرها',
                            'id'    => 'teasers',
                        ]
                    ],
                ]
            ],
            'items'         => [
                [
                    'type'              => 'blog',
                    'type_title'        => 'وبلاگ',
                    'title'             => 'ماجراجویی منحصر به فرد',
                    'excerpt'           => 'تاق قرار روستایی به اتاق فرارهایی میگن که توی به فضای روستایی و سنتی، داستان بازی شکل می گیره و پیش میره قدیمی و حس اسرار آمیز فضا',
                    'comments_count'    => 20,
                    'author'            => 'سعید زمانی',
                    'url'               => '/blog/78',
                    'image'             => 'http://escapezoom.ir/wp-content/uploads/2023/10/horrible-mobile-escaperoom.png',
                ],
                [
                    'type'          => 'video',
                    'type_title'    => 'تیزر اتاق',
                    'title'         => 'ماجراجویی منحصر به فرد',
                    'excerpt'       => 'ورود افراد با حالت  طبیعی به این اتاق ممنوع است.',
                    'url'           => '/blog/78',
                    'cover'         => 'http://escapezoom.ir/wp-content/uploads/2023/10/horrible-mobile-escaperoom.png',
                ],
            ],
        ]
    ];

    /*===============================================================*/
    // آخرین کامنت ها

    $comments_per_page = 10;
    $args = array(
        'post_type'   => 'product',
        'status'      => 'approve',
        'number'      => $comments_per_page,
        'orderby'     => 'comment_date',
        'order'       => 'DESC',
        'parent'      => 0,
    );
    $comments_query = new WP_Comment_Query;
    $comments = $comments_query->query($args);

    $comment_items = [];

    if ($comments) {
        foreach ($comments as $comment) {
            $comment_id = $comment->comment_ID;

            $replies_args = array(
                'parent' => $comment_id,
                'status' => 'approve',
                'type'   => 'comment',
            );

            $author_title = $comment->comment_author;

            if (ctype_digit($comment->comment_author))
                $author_title = str_replace(substr($comment->comment_author, 3, 5), "×××××", $comment->comment_author);

            $comment_rating = get_comment_meta($comment_id, 'comment_rating', true);

            $comment_items[] = [
                'id'            => (int)$comment_id,
                'author'        => $author_title,
                'author_image'  => get_user_meta($comment->user_id, 'user_avatar', true) ?: 'http://escapezoom.ir/wp-content/uploads/2024/04/male_avatar_level_1.png',
                'author_level'  => '',
                'product_title' => get_the_title($comment->comment_post_ID),
                'product_url'   => trim_home_url(get_permalink($comment->comment_post_ID)),
                'content'       => $comment->comment_content,
                'date'          => strtotime($comment->comment_date),
                'reply'         => isset(get_comments($replies_args)[0]) ? get_comments($replies_args)[0]->comment_content : null,
                'votes_count'   => ((int)get_comment_meta($comment_id, 'cld_like_count', true) - (int)get_comment_meta($comment_id, 'cld_dislike_count', true)),
                'rating_items'  => $comment_rating ? array_map(fn($value) => $value / 20, get_comment_meta($comment_id, 'comment_rating', true)) : 0,
            ];
        }
    }
    $data[] = [
        'type'  => 'comments',
        'title' => '',
        'icon'  => '',
        'url'   => '',
        'data'  => [
            'slide_time'    => 5,
            'items'         => $comment_items
        ]
    ];

    /*===============================================================*/

    wp_send_json_success($data);
}
//**********************************************************************************************************/
function aboutus_api($request)
{

    $data = [
        'title'         => 'درباره اسکیپ زوم',
        'subtitle'      => 'اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.',
        'text'          => 'اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است. اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است. اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است. اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است. اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است. اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است. اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است. اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است. اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است. اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است. اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است. اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است. اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است. اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است. اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است. اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است. اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است. اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است. اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است. اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است. اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است. اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است. اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است. اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است. اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.',
        'image'         => 'http://escapezoom.ir/wp-content/uploads/2024/10/aboutus11.png',
        'goals'         => [
            'title' => 'اهداف اسکیپ زوم',
            'text'  => 'اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.',
        ],
        'honors'        => [
            'title' => 'افتخارات اسکیپ زوم',
            'text'  => 'اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.',
            'items' => [
                [
                    'title' => 'بهترین سایت جشنواره ملی',
                    'year'  => 1402,
                    'image' => 'http://escapezoom.ir/wp-content/uploads/2024/10/prize.png',
                ],
                [
                    'title' => 'بهترین سایت جشنواره ملی',
                    'year'  => 1402,
                    'image' => 'http://escapezoom.ir/wp-content/uploads/2024/10/prize.png',
                ],
                [
                    'title' => 'بهترین سایت جشنواره ملی',
                    'year'  => 1402,
                    'image' => 'http://escapezoom.ir/wp-content/uploads/2024/10/prize.png',
                ],
                [
                    'title' => 'بهترین سایت جشنواره ملی',
                    'year'  => 1402,
                    'image' => 'http://escapezoom.ir/wp-content/uploads/2024/10/prize.png',
                ],
            ],
        ],
        'team'          => [
            'title' => 'اهداف اسکیپ زوم',
            'text'  => 'اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.',
            'items' => [
                [
                    'name'      => 'حسین یعقوبی',
                    'position'  => 'مدیرعامل مجموعه',
                    'image'     => 'https://escapezoom.ir/wp-content/uploads/2024/03/signs-of-exorcism.jpg',
                    'socials'   => [
                        [
                            'title' => 'instagram',
                            'url'   => 'instagram.com',
                        ],
                        [
                            'title' => 'linkedin',
                            'url'   => 'linkedin.com',
                        ],
                    ]
                ],
                [
                    'name'      => 'حسین یعقوبی',
                    'position'  => 'مدیرعامل مجموعه',
                    'image'     => 'https://escapezoom.ir/wp-content/uploads/2024/03/signs-of-exorcism.jpg',
                    'socials'   => [
                        [
                            'title' => 'instagram',
                            'url'   => 'instagram.com',
                        ],
                        [
                            'title' => 'linkedin',
                            'url'   => 'linkedin.com',
                        ],
                    ]
                ],
                [
                    'name'      => 'حسین یعقوبی',
                    'position'  => 'مدیرعامل مجموعه',
                    'image'     => 'https://escapezoom.ir/wp-content/uploads/2024/03/signs-of-exorcism.jpg',
                    'socials'   => [
                        [
                            'title' => 'instagram',
                            'url'   => 'instagram.com',
                        ],
                        [
                            'title' => 'linkedin',
                            'url'   => 'linkedin.com',
                        ],
                    ]
                ],
                [
                    'name'      => 'حسین یعقوبی',
                    'position'  => 'مدیرعامل مجموعه',
                    'image'     => 'https://escapezoom.ir/wp-content/uploads/2024/03/signs-of-exorcism.jpg',
                    'socials'   => [
                        [
                            'title' => 'instagram',
                            'url'   => 'instagram.com',
                        ],
                        [
                            'title' => 'linkedin',
                            'url'   => 'linkedin.com',
                        ],
                    ]
                ],
            ],
        ],
        'breadcrumb'    => [
            [
                'title' => 'صفحه اصلی',
                'url'   => '/',
            ],
            [
                'title' => 'درباره ما',
                'url'   => '/',
            ],
        ],
    ];

    wp_send_json_success($data);
}
//**********************************************************************************************************/
function contactus_api($request)
{

    $data = [
        'title'         => 'تماس با اسکیپ زوم',
        'subtitle'      => 'اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.',
        'text'          => 'اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است. اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است. اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است. اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است. اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است. اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است. اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است. اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است. اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است. اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.',
        'aboutus'       => [
            'title'     => 'درباره ما',
            'text'      => 'اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است. اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است. اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است. اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است. اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است. اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است. اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است. اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است. اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است. اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.',
            'url_text'  => 'آشنایی با تیم ما >',
            'url'       => '/aboutus',
        ],
        'contactus'     => [
            'mobile'    => '02191307900',
            'time'      => 'ساعات کاری 10 تا 24',
            'email'     => 'escapezoom@gmail.com',
            'form'      => 'escapezoom@gmail.com',
            'socials'   => [
                'telegram'  => 'https://t.me/escape_zoom',
                'twitter'   => 'https://twitter.com/escape_zoom',
                'instagram' => 'https://instagram.com/escape_zoom',
                'aparat'    => 'https://aparat.com/escape_zoom',
                'youtube'   => 'https://youtube.come/escape_zoom',
                'form'      => 'https://youtube.come/escape_zoom',
            ],
            'address'     => 'تهران خیابان بهشتی، خیابان سرافراز، کوچه دوم خبرنگار',
            'google_map'  => [
                'lat'   => '35.729854',
                'long'  => '51.420412',
            ],
        ],
        'breadcrumb'    => [
            [
                'title' => 'صفحه اصلی',
                'url'   => '/',
            ],
            [
                'title' => 'ارتباط ما',
                'url'   => '',
            ],
        ],
    ];

    wp_send_json_success($data);
}
//**********************************************************************************************************/
function contactus_form_api($request)
{

    $params = $request->get_params();
    $name           = $params['name'];
    $phone          = $params['phone'];
    $subject        = $params['subject'];
    $description    = $params['description'];

    if (!isset($name) || empty($name))
        wp_send_json_error(array('error' => 'نام را وارد کنید'), 400);

    if (!isset($phone) || empty($phone))
        wp_send_json_error(array('error' => 'موبایل را وارد کنید'), 400);

    if (!isset($subject) || empty($subject))
        wp_send_json_error(array('error' => 'موضوع را وارد کنید'), 400);

    if (!isset($description) || empty($description))
        wp_send_json_error(array('error' => 'توضحیات را وارد کنید'), 400);

    $post_id = wp_insert_post(array(
        'post_type'         => 'contacting',
        'post_author'       => 0,
        'post_title'        => $subject,
        'post_content'      => $description,
        'post_status'       => 'pending',
        'comment_status'    => 'closed',
        'ping_status'       => 'closed',
    ));

    add_post_meta($post_id, 'name', $name);
    add_post_meta($post_id, 'phone', $phone);

    wp_send_json_success('با موفقیت ارسال شد.');
}
//**********************************************************************************************************/
function collection_get_all_api($request)
{
    global $wpdb;

    $params = $request->get_params();
    $sort       = $params['sort'];
    $page_num   = $params['page'];

    $page_num   = $page_num ?: 1;
    $sort       = $sort ?: 'popular';

    $items_per_page = 2;
    $offset         = ($page_num - 1) * $items_per_page;

    if ($sort == -1) {
        $collections = $wpdb->get_results(
            $wpdb->prepare(
                'SELECT * FROM collections ORDER BY RAND() DESC LIMIT %d, %d',
                (int) $offset,
                (int) $items_per_page
            )
        );
    } else {
        $sort_by = ( $sort === 'recent' ) ? 'ID' : 'likes_count';
        $collections = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM collections ORDER BY {$sort_by} DESC LIMIT %d, %d",
                (int) $offset,
                (int) $items_per_page
            )
        );
    }
    foreach ($collections as $collection) {

        $images = [];
        foreach (unserialize($collection->items) as $product_id)
            $images[] = 'https://escapezoom.ir/wp-content/uploads/2021/07/The_Sun_by_the_Atmospheric_Imaging_Assembly_of_NASAs_Solar_Dynamics_Observatory_-_20100819.jpg';

        $items[] =  [
            'title'         => $collection->title,
            'likes_count'   => (int)$collection->likes_count,
            'url'           => '/profile/347895',
            'count'         => count($images),
            'items'         => $images,
        ];
    }

    $data = [
        'title'         => 'کالکشن های پیشنهادی کاربران',
        'subtitle'      => 'اسکیپ زوم، پلتفرم معرفی و رزرو آنلاین اتاق فرار در ایران است.',
        'tabs'          => [
            'type'  => 'sort_type',
            'title' => '',
            'key'   => 'sort_type',
            'items' => [
                [
                    'title' => 'همه',
                    'id'    => '-1',
                ],
                [
                    'title' => 'جدیدترین',
                    'id'    => 'recent',
                ],
                [
                    'title' => 'پرطرفدارترین',
                    'id'    => 'popular',
                ],
            ],
        ],
        'items'         => $items,
        'breadcrumb'    => [
            [
                'title' => 'صفحه اصلی',
                'url'   => '/',
            ],
            [
                'title' => 'کالکشن ها',
                'url'   => '/collections',
            ],
        ],
        'pagination'    => [
            'current_page'  => (int)$page_num,
            'total_pages'   => 5,
        ]
    ];

    wp_send_json_success($data);
}
//**********************************************************************************************************/
function static_get_api($request)
{
    global $wpdb;

    $data['product_types'] = [
        'escaperoom'    => 'اتاق فرار',
        'cinema'        => 'سینما ترس',
        'lasertag'      => 'لیزرتگ',
        'rageroom'      => 'اتاق خشم',
    ];

    $data['provinces'] = [
        'type'  => 'provinces',
        'desc'  => 'لیست استان های ایران',
        'items' =>  json_decode('[{"id":1,"name":"آذربایجان شرقی"},{"id":2,"name":"آذربایجان غربی"},{"id":3,"name":"اردبیل"},{"id":4,"name":"اصفهان"},{"id":5,"name":"البرز"},{"id":6,"name":"ایلام"},{"id":7,"name":"بوشهر"},{"id":8,"name":"تهران"},{"id":9,"name":"چهارمحال و بختیاری"},{"id":10,"name":"خراسان جنوبی"},{"id":11,"name":"خراسان رضوی"},{"id":12,"name":"خراسان شمالی"},{"id":13,"name":"خوزستان"},{"id":14,"name":"زنجان"},{"id":15,"name":"سمنان"},{"id":16,"name":"سیستان و بلوچستان"},{"id":17,"name":"فارس"},{"id":18,"name":"قزوین"},{"id":19,"name":"قم"},{"id":20,"name":"کردستان"},{"id":21,"name":"کرمان"},{"id":22,"name":"کرمانشاه"},{"id":23,"name":"کهگیلویه و بویراحمد"},{"id":24,"name":"گلستان"},{"id":25,"name":"لرستان"},{"id":26,"name":"گیلان"},{"id":27,"name":"مازندران"},{"id":28,"name":"مرکزی"},{"id":29,"name":"هرمزگان"},{"id":30,"name":"همدان"},{"id":31,"name":"یزد"}]')
    ];

    $data['modules_source'] = [
        'type'  => 'modules_source',
        'desc'  => 'پارامتر source برای برخی ماژول ها',
        'items' =>  [
            [
                'type'      => 'search via map',
                'source'    => 'map_search',
            ],
        ]
    ];

    $data['city_id'] = [
        'type'  => 'product_cities',
        'desc'  => 'شهرهای مربوط به محصولات',
        'items' => [
            [
                'title' => 'تهران',
                'id'    => 15,
            ],
            [
                'title' => 'کرج',
                'id'    => 162,
            ],
            [
                'title' => 'اصفهان',
                'id'    => 122,
            ],
            [
                'title' => 'مشهد',
                'id'    => 121,
            ],
            [
                'title' => 'کرمانشاه',
                'id'    => 293,
            ],
            [
                'title' => 'رشت',
                'id'    => 285,
            ],
            [
                'title' => 'تبریز',
                'id'    => 416,
            ],
            [
                'title' => 'قزوین',
                'id'    => 270,
            ],
        ]
    ];

    $data['user_level'] = [
        'type'  => 'user_level_label',
        'desc'  => 'اطلاعات مربوط به سطح بندی کاربران',
        'items' => [
            1 => [
                'title' => 'اینکاره',
                'color' => '#008000',
                'image' => 'https://escapezoom.ir/wp-content/uploads/2024/06/level1.png',
            ],
            2 => [
                'title' => 'تازه کار',
                'color' => '#F00',
                'image' => 'https://escapezoom.ir/wp-content/uploads/2024/06/level1.png',
            ],
        ]
    ];

    $data['rating_items'] = [
        'type'  => 'product_rating_items',
        'desc'  => 'اطلاعات مربوط به آیتم های امتیاز دهی در کامنت های سینگل محصول',
        'items' => [
            1 => 'فضاسازی',
            2 => 'کیفیت معما',
            3 => 'تازگی و خلاقیت',
            4 => 'بازیگردانی و اکت',
            5 => 'برخورد پرسنل',
        ]
    ];

    $data['user_minimum_points'] = [
        'type'  => 'user_action_minimum_points',
        'desc'  => 'حداقل امتیاز کاربر برای انجام اکشن های مختلف',
        'items' => [
            [
                'route'         => '/api/v1/user/add_ticket/',
                'minimum_point' => 30,
            ],
        ]
    ];

    $data['escapezoom_info'] = [
        'type'  => 'escapezoom_info',
        'desc'  => 'شماره ها و شبکه های اجتماعی و اطلاعات اسکیپ زوم',
        'items' => [
            [
                'title' => 'پشتیبانی',
                'value' => '02191307900',
                'type'  => 'phone',
            ],
            [
                'title' => 'شماره ثانویه پشتیبانی',
                'value' => '02191307900',
                'type'  => 'phone',
            ],
            [
                'title' => 'تلگرام اسکیپ زوم',
                'value' => 'https://t.me/escapezoom',
                'type'  => 'url',
            ],
        ]
    ];

    $data['ticketing_departments'] = [
        'type'  => 'ticketing_departments',
        'desc'  => 'واحدهای تیکتینگ و پشتیبانی سایت',
        'items' => [
            'مالی',
            'فنی',
            'شکایات',
            'تبلیغات',
        ]
    ];

    $data['banks_list'] = [
        'type'  => 'banks_list',
        'desc'  => 'بانک های موجود',
        'items' => [
            'سپه',
            'پاسارگاد',
            'سامان',
            'صادرات',
        ]
    ];

    $data['product_properties'] = [
        'type'  => 'product_properties',
        'desc'  => 'ویژگی های سرگرمی ها',
        'items' => [
            'escaperoom'    => [
                [
                    'id'    => 'genre',
                    'value' => 'ژانر',
                    'icon'  => 'genre',
                ],
                [
                    'id'    => 'capacity',
                    'value' => 'ظرفیت',
                    'icon'  => 'capacity',
                ],
                [
                    'id'    => 'duration',
                    'value' => 'مدت سانس',
                    'icon'  => 'time',
                ],
                [
                    'id'    => 'age',
                    'value' => 'مناسب سن',
                    'icon'  => 'age_range',
                ],
                [
                    'id'    => 'tickets_sold',
                    'value' => 'دفعات رزرو',
                    'icon'  => 'counter',
                ],
                [
                    'id'    => 'level',
                    'value' => 'میزان سختی',
                    'icon'  => 'level',
                ],
            ],
            'lasertag'      => [
                [
                    'id'    => 'capacity',
                    'value' => 'ظرفیت',
                    'icon'  => 'capacity',
                ],
                [
                    'id'    => 'duration',
                    'value' => 'مدت سانس',
                    'icon'  => 'time',
                ],
                [
                    'id'    => 'age',
                    'value' => 'مناسب سن',
                    'icon'  => 'age_range',
                ],
                [
                    'id'    => 'tickets_sold',
                    'value' => 'دفعات رزرو',
                    'icon'  => 'counter',
                ],
            ],
            'rageroom'      => [
                [
                    'id'    => 'capacity',
                    'value' => 'ظرفیت',
                    'icon'  => 'capacity',
                ],
                [
                    'id'    => 'duration',
                    'value' => 'مدت سانس',
                    'icon'  => 'time',
                ],
                [
                    'id'    => 'age',
                    'value' => 'مناسب سن',
                    'icon'  => 'age_range',
                ],
                [
                    'id'    => 'tickets_sold',
                    'value' => 'دفعات رزرو',
                    'icon'  => 'counter',
                ],
                [
                    'id'    => 'safety',
                    'value' => 'سطح ایمنی',
                    'icon'  => 'safety',
                ],
            ],
            'cinema'        => [
                [
                    'id'    => 'display_type',
                    'value' => 'نوع نمایش',
                    'icon'  => 'display',
                ],
                [
                    'id'    => 'capacity',
                    'value' => 'ظرفیت',
                    'icon'  => 'capacity',
                ],
                [
                    'id'    => 'duration',
                    'value' => 'مدت سانس',
                    'icon'  => 'time',
                ],
                [
                    'id'    => 'chair_type',
                    'value' => 'نوع صندلی',
                    'icon'  => 'level',
                ],
                [
                    'id'    => 'age',
                    'value' => 'مناسب سن',
                    'icon'  => 'age_range',
                ],
                [
                    'id'    => 'tickets_sold',
                    'value' => 'دفعات رزرو',
                    'icon'  => 'counter',
                ],
            ],
        ]
    ];

    $data['product_facilities'] = [
        'type'  => 'product_facilities',
        'desc'  => 'امکانات سرگرمی ها',
        'items' => [
            'escaperoom'    => [
                [
                    'title' => 'پارکینگ',
                    'icon'  => 'parking',
                ],
                [
                    'title' => 'کافی شاپ',
                    'icon'  => 'coffeeshop',
                ],
                [
                    'title' => 'وای فای',
                    'icon'  => 'wifi',
                ],
                [
                    'title' => 'پیش آموزش',
                    'icon'  => 'teaching',
                ],
                [
                    'title' => 'امکان برگزاری تولد',
                    'icon'  => 'birthday',
                ],
                [
                    'title' => 'دسترسی مترو',
                    'icon'  => 'subway',
                ],
                [
                    'title' => 'پشتیبانی ویژه',
                    'icon'  => 'support',
                ],
                [
                    'title' => 'قیمت مناسب2',
                    'icon'  => 'affordable ',
                ],
            ],
        ]
    ];

    $data['product_options'] = [
        'type'  => 'product_facilities',
        'desc'  => 'امکانات سرگرمی ها',
        'items' => [
            'escaperoom'    => [
                [
                    'id'    => 'easy_park',
                    'value' => 'جای پارک آسان',
                    'icon'  => 'parking',
                ],
                [
                    'id'    => 'cafe',
                    'value' => 'کافه',
                    'icon'  => 'parking',
                ],
                [
                    'id'    => 'surprise',
                    'value' => 'سورپرایز تولد',
                    'icon'  => 'parking',
                ],
                [
                    'id'    => 'transport',
                    'value' => 'مترو یا BRT',
                    'icon'  => 'parking',
                ],
                [
                    'id'    => 'waiting_room',
                    'value' => 'اتاق انتظار همراه',
                    'icon'  => 'parking',
                ],
                [
                    'id'    => 'wc',
                    'value' => 'سرویس بهداشتی',
                    'icon'  => 'parking',
                ],
                [
                    'id'    => 'ventilation',
                    'value' => 'تهویه مناسب',
                    'icon'  => 'parking',
                ],

            ],
            'lasertag'      => [
                [
                    'id'    => 'easy_park',
                    'value' => 'جای پارک آسان',
                    'icon'  => 'parking',
                ],
                [
                    'id'    => 'cafe',
                    'value' => 'کافه',
                    'icon'  => 'parking',
                ],
                [
                    'id'    => 'surprise',
                    'value' => 'سورپرایز تولد',
                    'icon'  => 'parking',
                ],
                [
                    'id'    => 'transport',
                    'value' => 'مترو یا BRT',
                    'icon'  => 'parking',
                ],
                [
                    'id'    => 'waiting_room',
                    'value' => 'اتاق انتظار همراه',
                    'icon'  => 'parking',
                ],
                [
                    'id'    => 'wc',
                    'value' => 'سرویس بهداشتی',
                    'icon'  => 'parking',
                ],
                [
                    'id'    => 'ventilation',
                    'value' => 'تهویه مناسب',
                    'icon'  => 'parking',
                ],

            ],
            'rageroom'      => [
                [
                    'id'    => 'easy_park',
                    'value' => 'جای پارک آسان',
                    'icon'  => 'parking',
                ],
                [
                    'id'    => 'cafe',
                    'value' => 'کافه',
                    'icon'  => 'parking',
                ],
                [
                    'id'    => 'surprise',
                    'value' => 'سورپرایز تولد',
                    'icon'  => 'parking',
                ],
                [
                    'id'    => 'transport',
                    'value' => 'مترو یا BRT',
                    'icon'  => 'parking',
                ],
                [
                    'id'    => 'waiting_room',
                    'value' => 'اتاق انتظار همراه',
                    'icon'  => 'parking',
                ],
                [
                    'id'    => 'wc',
                    'value' => 'سرویس بهداشتی',
                    'icon'  => 'parking',
                ],
                [
                    'id'    => 'ventilation',
                    'value' => 'تهویه مناسب',
                    'icon'  => 'parking',
                ],

            ],
            'cinema'        => [
                [
                    'id'    => 'easy_park',
                    'value' => 'جای پارک آسان',
                    'icon'  => 'parking',
                ],
                [
                    'id'    => 'cafe',
                    'value' => 'کافه',
                    'icon'  => 'parking',
                ],
                [
                    'id'    => 'surprise',
                    'value' => 'سورپرایز تولد',
                    'icon'  => 'parking',
                ],
                [
                    'id'    => 'transport',
                    'value' => 'مترو یا BRT',
                    'icon'  => 'parking',
                ],
                [
                    'id'    => 'waiting_room',
                    'value' => 'اتاق انتظار همراه',
                    'icon'  => 'parking',
                ],
                [
                    'id'    => 'wc',
                    'value' => 'سرویس بهداشتی',
                    'icon'  => 'parking',
                ],
                [
                    'id'    => 'ventilation',
                    'value' => 'تهویه مناسب',
                    'icon'  => 'parking',
                ],

            ],
        ]
    ];

    $data['header_navbar'] = [
        'type'  => 'header_navbar',
        'desc'  => 'منوهای هدر',
        'items' => [
            [
                'title' => 'شهرها',
                'url'   => '#',
                'items' => [
                    [
                        'title' => 'تهران',
                        'url'   => '/ir/%D8%AA%D9%87%D8%B1%D8%A7%D9%86/',
                        'items' => [],
                    ],
                    [
                        'title' => 'کرج',
                        'url'   => '/ir/%DA%A9%D8%B1%D8%AC/',
                        'items' => [],
                    ],
                    [
                        'title' => 'اراک',
                        'url'   => '/ir/%D8%A7%D8%B1%D8%A7%DA%A9/',
                        'items' => [],
                    ],
                    [
                        'title' => 'مشهد',
                        'url'   => '/ir/%D9%85%D8%B4%D9%87%D8%AF/',
                        'items' => [],
                    ],
                    [
                        'title' => 'اصفهان',
                        'url'   => '/ir/%D8%A7%D8%B5%D9%81%D9%87%D8%A7%D9%86/',
                        'items' => [],
                    ],
                    [
                        'title' => 'کاشان',
                        'url'   => '/ir/%DA%A9%D8%A7%D8%B4%D8%A7%D9%86/',
                        'items' => [],
                    ],
                    [
                        'title' => 'کرمانشاه',
                        'url'   => '/ir/%DA%A9%D8%B1%D9%85%D8%A7%D9%86%D8%B4%D8%A7%D9%87/',
                        'items' => [],
                    ],
                    [
                        'title' => 'قم',
                        'url'   => '/ir/%D9%82%D9%85/',
                        'items' => [],
                    ],
                    [
                        'title' => 'اهواز',
                        'url'   => '/ir/%D8%A7%D9%87%D9%88%D8%A7%D8%B2/',
                        'items' => [],
                    ],
                ]
            ],
            [
                'title' => 'اتاق فرار',
                'url'   => '',
                'items' => [
                    [
                        'title' => 'تهرانی ها',
                        'url'   => '#',
                        'items' => [
                            [
                                'title' => 'غرب',
                                'url'   => '/type/%D8%A7%D8%AA%D8%A7%D9%82-%D9%81%D8%B1%D8%A7%D8%B1-%D8%BA%D8%B1%D8%A8-%D8%AA%D9%87%D8%B1%D8%A7%D9%86/',
                                'items' => [],
                            ],
                        ]
                    ],
                    [
                        'title' => 'اتاق فرار',
                        'url'   => '#',
                        'items' => [
                            [
                                'title' => 'اصفهان',
                                'url'   => '/city/%D8%A7%D8%B5%D9%81%D9%87%D8%A7%D9%86/',
                                'items' => []
                            ],
                            [
                                'title' => 'مشهد',
                                'url'   => '/city/%D9%85%D8%B4%D9%87%D8%AF/',
                                'items' => []
                            ],
                            [
                                'title' => 'کاشان',
                                'url'   => '/city/%DA%A9%D8%A7%D8%B4%D8%A7%D9%86/',
                                'items' => []
                            ],
                        ]
                    ],
                ]
            ],
            [
                'title' => 'لیزرتگ',
                'url'   => '#',
                'items' => [
                    [
                        'title' => 'اصفهان',
                        'url'   => '/city/%D8%A7%D8%B5%D9%81%D9%87%D8%A7%D9%86/',
                        'items' => []
                    ],
                    [
                        'title' => 'مشهد',
                        'url'   => '/city/%D9%85%D8%B4%D9%87%D8%AF/',
                        'items' => []
                    ],
                    [
                        'title' => 'کاشان',
                        'url'   => '/city/%DA%A9%D8%A7%D8%B4%D8%A7%D9%86/',
                        'items' => []
                    ],
                ]
            ],
            [
                'title' => 'سینماترس',
                'url'   => '#',
                'items' => [
                    [
                        'title' => 'اصفهان',
                        'url'   => '/city/%D8%A7%D8%B5%D9%81%D9%87%D8%A7%D9%86/',
                        'items' => []
                    ],
                    [
                        'title' => 'مشهد',
                        'url'   => '/city/%D9%85%D8%B4%D9%87%D8%AF/',
                        'items' => []
                    ],
                    [
                        'title' => 'کاشان',
                        'url'   => '/city/%DA%A9%D8%A7%D8%B4%D8%A7%D9%86/',
                        'items' => []
                    ],
                ]
            ],
            [
                'title' => 'مجله سرگرمی',
                'url'   => '/blog/',
                'items' => []
            ],
        ]
    ];

    $data['footer'] = [
        'type'  => 'footer',
        'desc'  => 'فوتر',
        'items' => [
            [
                'header'    => [
                    [
                        'id'    => 1,
                        'title' => 'اسکیپ زوم، جستجو مقایسه و رزرو اتاق فرار',
                        'value' => 'www.escapezoom.ir'
                    ],
                    [
                        'id'    => 2,
                        'title' => 'ایمیل پشتیبانی',
                        'value' => 'info@escapezoom.ir'
                    ],
                    [
                        'id'    => 3,
                        'title' => 'پشتیبان همیشگی شما هستیم',
                        'value' => '0219130700'
                    ],
                ],
                'right'     => [
                    'description' => [
                        'id'            => 1,
                        'description'   => 'سایت اسکیپ زوم امـــکان جســتجو و رزرو اتـاق فرار در کامل‌ترین آرشیو اتاق فرارهای ایران را برای شما فراهم کرده است در وبسایت اسـکیپ زوم می توانید محتوای جــذاب و سرگرم کننده در حوزه بازی اتاق فرار و بازی‌های معمایی را ببینید و نــکات حرفه‌ای شـدن در این حوزه را بیاموزید.وبســایت اسـکیپ زوم این قابلیت را برای شما فراهم کرده تا بتوانید اتاق های فرار را بر اساس شهر، منطـــقه، تعـداد نفرات و قیــمت آن‌ها فیـــلتر کرده، بـازی مناســـب خود را انــــتخاب نمــوده و به ســادگی رزرو نمــایید.بازی های اسکــیپ رومی معـــمولا پذیرای سنین 12 تا 60 سال هستند. اگر زیر 18 یا بـالای 50 ســـال سن دارید باید در انتخــاب ژانــر اتاق فرار دقت بیشــتری داشته باشید چراکه ژانرهای ترسـناک، دلهره آور ممکن است برای شما...'
                    ],
                    'licenses' => [
                        [
                            'id'    => 1,
                            'url'   => 'https://trustseal.enamad.ir/?id=346259&Code=fbGkMCGn9UU31loYShJV',
                            'logo'  => ez_theme_asset_uri('images/license/enamad.svg'),
                        ],
                        [
                            'id'    => 2,
                            'url'   => 'https://eanjoman.ir/member/NAAK5SE04K2A9snx2E3kKqg47',
                            'logo'  => ez_theme_asset_uri('images/license/digital.svg'),
                        ],
                        [
                            'id'    => 3,
                            'url'   => 'javascript:',
                            'logo'  => ez_theme_asset_uri('images/license/senfi.svg'),
                        ],
                        [
                            'id'    => 4,
                            'url'   => 'javascript:',
                            'logo'  => ez_theme_asset_uri('images/license/etehaiye.svg'),
                        ],
                    ],
                ],
                'left'      => [
                    'column1' => [
                        'id'    => 1,
                        'title' => 'بخش های مهم سایت',
                        'items' => [
                            [
                                'id'    => 1,
                                'title' => 'صفحه اصلی',
                                'url'   => '#',
                            ],
                            [
                                'id'    => 2,
                                'title' => 'پر فروش ترین ها',
                                'url'   => '#',
                            ],
                            [
                                'id'    => 3,
                                'title' => 'مجله خبری وبلاگ',
                                'url'   => '#',
                            ],
                            [
                                'id'    => 4,
                                'title' => 'ویدیو و تیزرها',
                                'url'   => '#',
                            ],
                            [
                                'id'    => 5,
                                'title' => 'ثبت اتاق',
                                'url'   => '#',
                            ],
                            [
                                'id'    => 6,
                                'title' => 'پشتیبانی و فروش',
                                'url'   => '#',
                            ],
                        ]
                    ],
                    'column2' => [
                        'id'    => 2,
                        'title' => 'محبوب ترین های کاربران',
                        'items' => [
                            [
                                'id'    => 1,
                                'title' => 'سانس های آزاد امروز',
                                'url'   => '#',
                            ],
                            [
                                'id'    => 2,
                                'title' => 'سانس های آزاد فردا',
                                'url'   => '#',
                            ],
                            [
                                'id'    => 3,
                                'title' => 'سانس های آزاد پس فردا',
                                'url'   => '#',
                            ],
                            [
                                'id'    => 4,
                                'title' => 'بازی های خیلی سخت',
                                'url'   => '#',
                            ],
                            [
                                'id'    => 5,
                                'title' => 'بازی های سخت و دلهره آور',
                                'url'   => '#',
                            ],
                            [
                                'id'    => 6,
                                'title' => 'بازی های ترسناک و خیلی سخت',
                                'url'   => '#',
                            ],
                        ]
                    ],
                    'column3' => [
                        'id'    => 3,
                        'title' => 'ژانرهای محبوب',
                        'items' => [
                            [
                                'id'    => 1,
                                'title' => 'ترسناک',
                                'url'   => 'https://escapezoom.ir/type/%D8%A7%D8%AA%D8%A7%D9%82-%D9%81%D8%B1%D8%A7%D8%B1-%D8%AA%D8%B1%D8%B3%D9%86%D8%A7%DA%A9/',
                            ],
                            [
                                'id'    => 2,
                                'title' => 'اکشن',
                                'url'   => 'https://escapezoom.ir/type/%D8%A7%DA%A9%D8%B4%D9%86/',
                            ],
                            [
                                'id'    => 3,
                                'title' => 'درام',
                                'url'   => 'https://escapezoom.ir/type/%D8%AF%D8%B1%D8%A7%D9%85/',
                            ],
                            [
                                'id'    => 4,
                                'title' => 'دلهره آور',
                                'url'   => 'https://escapezoom.ir/type/%d9%85%d8%b9%d9%85%d8%a7%db%8c%db%8c/',
                            ],
                            [
                                'id'    => 5,
                                'title' => 'غیر ترسناک',
                                'url'   => 'https://escapezoom.ir/type/%d9%85%d8%b9%d9%85%d8%a7%db%8c%db%8c/',
                            ],
                            [
                                'id'    => 6,
                                'title' => 'هیجانی',
                                'url'   => 'https://escapezoom.ir/type/%D9%87%DB%8C%D8%AC%D8%A7%D9%86%DB%8C/',
                            ],
                        ]
                    ],
                    'column4' => [
                        'id'    => 4,
                        'title' => 'محبوب ترین شهرها',
                        'items' => [
                            [
                                'id'    => 1,
                                'title' => 'تهران',
                                'url'   => 'https://escapezoom.ir/city/%D8%A7%D8%AA%D8%A7%D9%82-%D9%81%D8%B1%D8%A7%D8%B1-%D8%AA%D9%87%D8%B1%D8%A7%D9%86/',
                            ],
                            [
                                'id'    => 2,
                                'title' => 'رشت',
                                'url'   => 'https://escapezoom.ir/city/%D8%B1%D8%B4%D8%AA/',
                            ],
                            [
                                'id'    => 3,
                                'title' => 'کیش',
                                'url'   => '#',
                            ],
                            [
                                'id'    => 4,
                                'title' => 'قم',
                                'url'   => 'https://escapezoom.ir/city/%D9%82%D9%85/',
                            ],
                            [
                                'id'    => 5,
                                'title' => 'مشهد',
                                'url'   => 'https://escapezoom.ir/city/%D9%85%D8%B4%D9%87%D8%AF/',
                            ],
                            [
                                'id'    => 6,
                                'title' => 'اصفهان',
                                'url'   => 'https://escapezoom.ir/city/%D8%A7%D8%B5%D9%81%D9%87%D8%A7%D9%86/',
                            ],
                            [
                                'id'    => 7,
                                'title' => 'کرج',
                                'url'   => 'https://escapezoom.ir/city/%DA%A9%D8%B1%D8%AC/',
                            ],
                            [
                                'id'    => 8,
                                'title' => 'کاشان',
                                'url'   => 'https://escapezoom.ir/city/%DA%A9%D8%A7%D8%B4%D8%A7%D9%86/',
                            ],
                            [
                                'id'    => 9,
                                'title' => 'کرمانشاه',
                                'url'   => 'https://escapezoom.ir/city/%DA%A9%D8%B1%D9%85%D8%A7%D9%86%D8%B4%D8%A7%D9%87/',
                            ],
                            [
                                'id'    => 10,
                                'title' => 'قزوین',
                                'url'   => 'https://escapezoom.ir/city/%D9%82%D8%B2%D9%88%DB%8C%D9%86/',
                            ],
                            [
                                'id'    => 11,
                                'title' => 'اراک',
                                'url'   => 'https://escapezoom.ir/city/%D8%A7%D8%B1%D8%A7%DA%A9/',
                            ],
                            [
                                'id'    => 12,
                                'title' => 'کل ایران',
                                'url'   => '#',
                            ],
                        ]
                    ],
                ],
                'socials'   => [
                    [
                        'id'    => 1,
                        'title' => 'تلگرام',
                        'url'   => 'https://t.me/escape_zoom',
                    ],
                    [
                        'id'    => 2,
                        'title' => 'توییتر',
                        'url'   => 'https://twitter.com/escape_zoom',
                    ],
                    [
                        'id'    => 3,
                        'title' => 'اینستاگرام',
                        'url'   => 'https://instagram.com/escape_zoom',
                    ],
                    [
                        'id'    => 4,
                        'title' => 'آپارات',
                        'url'   => 'https://aparat.com/escape_zoom',
                    ],
                    [
                        'id'    => 5,
                        'title' => 'یوتیوب',
                        'url'   => 'https://youtube.come/escape_zoom',
                    ],
                ],
                'last_part' => [
                    'id'            => 1,
                    'title'         => 'اسکیپ زوم چیست؟',
                    'description'   => 'سایت اسکیپ زوم امکان جستجو و رزرو اتاق فرار در کامل‌ترین آرشیو اتاق فرارهای ایران را برای شما فراهم کرده است، در وبسایت اسکیپ زوم می توانید محتوای جذاب و سرگرم کننده در حوزه بازی اتاق فرار و بازی‌های معمایی را ببینید و نکات حرفه‌ای شدن در این حوزه را بیاموزید.وبسایت اسکیپ زوم این قابلیت را برای شما فراهم کرده تا بتوانید اتاق های فرار را بر اساس شهر، منطقه، تعداد نفرات و قیمت آن‌ها فیلتر کرده، بازی مناسب خود را انتخاب نموده و به سادگی رزرو نمایید.اگر تاکنون تجربه اسکیپ روم نداشته‌اید بهتر است بخش مقالات سایت را مطالعه نموده و قدری بیشتر از اتاق فرارها بدانید. همچنین در اینستاگرام اسکیپ زوم آموزش‌های کوتاه و مختصری منتشر شده اند که سریعاً می‌توانید به آشنایی و آمادگی خوبی در این حوزه برسید. در اسکیپ زوم می توانید اتاق فرارهای ایران را با جزئیات کامل جستجو کنید، محتوای جذاب و سرگرم کننده در موضوع اتاق فرار و بازی‌های معمایی ببینید و نکات حرفه‌ای شدن در این حوزه را بیاموزید.'
                ]
            ]
        ]
    ];

    $data['tickets_status'] = [
        'type'  => 'tickets_status',
        'desc'  => 'وضعیت های تیکت ها',
        'items' => [
            [
                'key'   => 'open',
                'value' => 'باز',
                'color' => '#c90303',
            ],
            [
                'key'   => 'closed',
                'value' => 'بسته شده',
                'color' => '#0a0a0a',
            ],
            [
                'key'   => 'pending',
                'value' => 'در حال بررسی',
                'color' => '#ffb326',
            ],
            [
                'key'   => 'respond',
                'value' => 'پاسخ داده شده',
                'color' => '#02ae02',
            ],
        ]
    ];

    $data['reserving_status'] = [
        'type'  => 'reserving_status',
        'desc'  => 'وضعیت ها و رنگ ها و کدهای سیستم رزرو',
        'items' => [
            'user' => [
                [
                    'key'   => 'reserved',
                    'title' => 'رزرو شده',
                    'color' => '#c00000',
                ],
                [
                    'key'   => 'reserving',
                    'title' => 'در حال رزرو',
                    'color' => '#FFD700',
                ],
                [
                    'key'   => 'reservable',
                    'title' => 'قابل رزرو',
                    'color' => '#00b350',
                ],
                [
                    'key'   => 'non_reservable',
                    'title' => 'غیرقابل رزرو',
                    'color' => '#4e4e4e',
                ],
            ],
            'owner' => [
                [
                    'key'   => 'reserved',
                    'title' => 'رزرو شده',
                    'color' => '#4e4e4e',
                ],
                [
                    'key'   => 'reserving',
                    'title' => 'در حال رزرو',
                    'color' => '#FFD700',
                ],
                [
                    'key'   => 'closeable',
                    'title' => 'بستن',
                    'color' => '#c00000',
                ],
                [
                    'key'   => 'openable',
                    'title' => 'باز کردن',
                    'color' => '#00b350',
                ],
            ]
        ]
    ];

    $data['orders_status'] = [
        'type'  => 'orders_status',
        'desc'  => 'وضعیت های رزروهای من(سفارشات من)',
        'items' => [
            [
                'key'   => 'played',
                'value' => 'بازی کرده اند',
                'color' => '#c90303',
            ],
            [
                'key'   => 'playing',
                'value' => 'در راه شروع بازی',
                'color' => '#0a0a0a',
            ],
        ]
    ];

    $data['sells_status'] = [
        'type'  => 'sells_status',
        'desc'  => 'وضعیت های فروش های من',
        'items' => [
            [
                'key'   => 'played',
                'value' => 'بازی کرده اند',
                'color' => '#c90303',
            ],
            [
                'key'   => 'playing',
                'value' => 'در راه شروع بازی',
                'color' => '#0a0a0a',
            ],
        ]
    ];

    $data['user_feeling'] = [
        'type'  => 'user_feeling',
        'desc'  => 'رضایت کاربر از بازی به صورت ایموجی',
        'items' => [
            [
                'key'   => 5,
                'value' => 'عالی بود',
                'color' => '#c90303',
                'icon'  => 'happy',
            ],
            [
                'key'   => 4,
                'value' => 'معمولی بود',
                'color' => '#c90303',
                'icon'  => 'happy',
            ],
            [
                'key'   => 3,
                'value' => 'خوب نبود',
                'color' => '#c90303',
                'icon'  => 'happy',
            ],
            [
                'key'   => 2,
                'value' => 'ضعیف بود',
                'color' => '#c90303',
                'icon'  => 'happy',
            ],
            [
                'key'   => 1,
                'value' => 'افتضاح بود',
                'color' => '#c90303',
                'icon'  => 'happy',
            ],
        ]
    ];


    // لینک صفحه قوانین سایت

    wp_send_json_success($data);
}
//**********************************************************************************************************/
function app_static_get_api($request)
{
    global $wpdb;

    $data['user_level'] = [
        'type'  => 'user_level_label',
        'desc'  => 'اطلاعات مربوط به سطح بندی کاربران',
        'items' => [
            1 => [
                'title' => 'اینکاره',
                'color' => '#008000',
            ],
            2 => [
                'title' => 'تازه کار',
                'color' => '#F00',
            ],
        ]
    ];

    $data['days_count'] = [
        'type'  => 'days_count',
        'desc'  => 'تعداد روزها برای نمایش در مدیریت سانس ها',
        'items' => 21
    ];

    $data['otp_resend_seconds'] = [
        'type'  => 'otp_resend_seconds',
        'desc'  => 'ثانیه های معکوس تا ارسال مجدد پیامک',
        'items' => 120
    ];

    $data['ticketing_departments'] = [
        'type'  => 'ticketing_departments',
        'desc'  => 'واحدهای تیکتینگ و پشتیبانی سایت',
        'items' => [
            'مالی',
            'فنی',
            'شکایات',
            'تبلیغات',
            'فروش',
        ]
    ];

    $data['tickets_status'] = [
        'type'  => 'tickets_status',
        'desc'  => 'وضعیت های تیکت ها',
        'items' => [
            [
                'key'   => 'open',
                'value' => 'باز',
                'color' => '#c90303',
            ],
            [
                'key'   => 'closed',
                'value' => 'بسته شده',
                'color' => '#0a0a0a',
            ],
            [
                'key'   => 'pending',
                'value' => 'در حال بررسی',
                'color' => '#ffb326',
            ],
            [
                'key'   => 'respond',
                'value' => 'پاسخ داده شده',
                'color' => '#02ae02',
            ],
        ]
    ];

    $data['reserving_status'] = [
        'type'  => 'reserving_status',
        'desc'  => 'وضعیت ها و رنگ ها و کدهای سیستم رزرو',
        'items' => [
            [
                'key'   => 'reserved',
                'title' => 'رزرو شده',
                'color' => '#4e4e4e',
            ],
            [
                'key'   => 'reserving',
                'title' => 'در حال رزرو',
                'color' => '#FFD700',
            ],
            [
                'key'   => 'closeable',
                'title' => 'بستن',
                'color' => '#c00000',
            ],
            [
                'key'   => 'openable',
                'title' => 'باز کردن',
                'color' => '#00b350',
            ],
        ]
    ];

    $data['withdraws_status'] = [
        'type'  => 'withdraws_status',
        'desc'  => 'وضعیت های درخواست های تسویه حساب',
        'items' => [
            [
                'key'   => 'انجام شده',
                'value' => 'انجام شد',
                'color' => "#34c200",
            ],
            [
                'key'   => 'رد شده',
                'value' => 'رد شده',
                'color' => '#c90303',
            ],
            [
                'key'   => 'در حال پردازش',
                'value' => 'در حال پردازش',
                'color' => '#f2c035',
            ],
        ]
    ];

    $data['comment_report_reasons'] = [
        'type'  => 'comment_report_reasons',
        'desc'  => 'آیتم های ریپورت یک کامنت',
        'items' => [
            ' کنسلی سانس و عدم بازی',
            ' اسپویل بازی',
            'نشراکاذیب و تهمت',
            ' الفاظ رکیک و توهین',
            ' عدم رعایت قوانین توسط پلیر',
        ]
    ];

    $data['minimum_withdrawal_amount'] = [
        'type'  => 'minimum_withdrawal_amount',
        'desc'  => 'کمترین مقدار برای تسویه حساب',
        'items' => 1000000
    ];

    $data['upload_accepted_formats'] = [
        'type'  => 'upload_accepted_formats',
        'desc'  => 'فرمت های مجاز برای آپلود',
        'items' => [
            [
                'format'    => 'pdf',
                'size'      => 2000,
            ],
            [
                'format'    => 'jpg',
                'size'      => 2000,
            ],
            [
                'format'    => 'png',
                'size'      => 2000,
            ],
            [
                'format'    => 'mp4',
                'size'      => 50000,
            ],
        ]
    ];

    $data['force_update'] = [
        "force_update"      => false,
        "update_message"    => "لطفا اپلیکیشن را آپدیت کنید",
        "update_url"        => "https://escapezoom.ir/app/",
    ];

    $data['jwt_revoked_error'] = [
        "jwt_revoked_error" => 'Signature verification failed',
    ];

    wp_send_json_success($data);
}

/*=========================================================================================================*/
//Checkout functions

//**********************************************************************************************************/
if ( ! function_exists( 'ez_api_checkout_compute_amounts' ) ) {
    function ez_api_checkout_compute_amounts( $product_id, $quantity, $sans_time, $payment_type = 'partial' ) {
        $product_id = (int) $product_id;
        $quantity = (int) $quantity;
        $sans_time = (int) $sans_time;
        $payment_type = in_array( $payment_type, [ 'partial', 'complete' ], true ) ? $payment_type : 'partial';

        if ( $product_id <= 0 || $quantity <= 0 || $sans_time <= 0 || ! function_exists( 'ez_checkout_compute_amounts' ) ) {
            return [ 'valid' => false ];
        }

        if ( function_exists( 'ez_checkout_store_booking_context' ) ) {
            ez_checkout_store_booking_context( $sans_time, $quantity, $product_id );
        }

        $ctx = [
            'book_timestamp'     => $sans_time,
            'quantity'           => $quantity,
            'requested_quantity' => $quantity,
            'cart_quantity'      => $quantity,
            'effective_quantity' => $quantity,
            'product_id'         => $product_id,
            'valid'              => true,
            'source'             => 'api_checkout',
        ];

        return ez_checkout_compute_amounts( $ctx, $payment_type );
    }
}
//**********************************************************************************************************/
function checkout_get_api($request)
{
    global $wldb;

    $user_id = get_user_id_by_token(ez_authorization(false));

    $params = $request->get_params();

    //    $coupon_code    = $params['coupon_code'];
    $product_id     = $params['product_id'];
    $quantity       = $params['quantity'];
    $sans_time      = $params['sans_time'];

    if (!isset($product_id) || empty($product_id))
        wp_send_json_error(array('error' => 'product_id نیاز است!'), 400);

    if (!isset($quantity) || empty($quantity))
        wp_send_json_error(array('error' => 'quantity بلیط نیاز است!'), 400);

    if (!isset($sans_time) || empty($sans_time))
        wp_send_json_error(array('error' => 'sans_time سانس نیاز است!'), 400);

    /***************************************************************************************/

    wp_set_current_user($user_id);
    wc_load_cart();
    $cart = WC()->cart;
    $cart->empty_cart();

    $cart->add_to_cart($product_id, $quantity);

    //    WC()->session->set('sans_time', $sans_time); // pass to the woocommerce_calculated_total action callback function

    /***************************************************************************************/

    $auto_disable = time() + (int)(get_post_meta($product_id, 'auto_disable', true)) * 60;
    if ($sans_time >= $auto_disable) { // first check sans status. this will meet next step if there is no error!

        $bookings_objs = json_decode(ez_reservation(array('type' => 'get_sans_lock', 'data' => array('product_id' => $product_id))));
        if (!empty($bookings_objs))
            foreach ($bookings_objs as $booking)
                $bookings[] = $booking->booking_time;

        $args = [
            "single_value"  => true,
            "query"         => "SELECT * FROM `wp_zb_booking_history` WHERE `room_id` like $product_id AND `booking_time` = $sans_time",
        ];
        $sans_obj = (array)json_decode(ez_reservation(array('type' => 'query_execution', 'data' => $args)));

        if ($sans_obj['status'] == 2)
            wp_send_json_error('سانس توسط مجموعه دار مسدود شده است.');

        elseif ($sans_obj['status'] == 1) {
            $msg_booked = function_exists('ez_api_message_sans_already_booked_confirmed')
                ? ez_api_message_sans_already_booked_confirmed(
                    (array) $sans_obj,
                    isset($wc_customer_id) ? (int) $wc_customer_id : (int) ($user_id ?? 0),
                    isset($phone_digits) ? (string) $phone_digits : ''
                )
                : 'سانس توسط شخص دیگری رزرو شده است.';
            wp_send_json_error($msg_booked);
        }

        elseif (in_array($sans_time, (array)$bookings))
            wp_send_json_error('سانس توسط شخص دیگری در حال رزرو است.');
    } else
        wp_send_json_error('سانس منقضی شده است.');

    $computed = ez_api_checkout_compute_amounts( $product_id, $quantity, $sans_time, 'partial' );
    if ( empty( $computed['valid'] ) ) {
        wp_send_json_error( 'خطا در محاسبه مبلغ سانس. لطفا دوباره تلاش کنید.' );
    }

    $total = (float) ( $computed['gross_total'] ?? 0 );
    $prepaid = (float) ( $computed['prepaid_amount'] ?? 0 );
    $rest = (float) ( $computed['offline_payable'] ?? 0 );
    $amount_to_pay = (float) ( $computed['online_payable'] ?? 0 );

    /***************************************************************************************/

    $wallet_balance     = ( is_object( $wldb ) && method_exists( $wldb, 'get_balance' ) ) ? (float) $wldb->get_balance( $user_id ) : 0.0;
    $wallet_enable      = true;
    $wallet_expiration  = get_user_meta($user_id, 'wallet_expiration', true);

    if ($wallet_expiration) {
        $wallet_enable = false;

        if (time() >= $wallet_expiration) {
            delete_user_meta($user_id, 'wallet_expiration');
            $wallet_enable = true;
        }
    }

    WC()->session->set('prepaid', $amount_to_pay); // pass to the woocommerce_calculated_total action callback function

    WC()->cart->calculate_totals();

    //    $prepaid = $cart->get_total( 'edit' );

    /***************************************************************************************/

    $coupon_credit = 0;

    /***************************************************************************************/

    $payment_methods = [];
    foreach (WC()->payment_gateways->payment_gateways as $gateway) {
        if ($gateway->enabled == 'yes') {

            $icon = '';
            if ($gateway->id == 'WC_Zibal')
                $icon = 'http://escapezoom.ir/wp-content/uploads/2024/10/zibal_ico.png';

            elseif ($gateway->id == 'wallet')
                continue;

            $payment_methods[] = [
                'code'         => $gateway->id,
                'title'        => $gateway->title,
                'description'  => $gateway->description,
                'icon'         => $icon,
            ];
        }
    }

    /***************************************************************************************/

    $data = [
        'total'             => (int) $total,
        'prepaid'           => (int) $prepaid,
        'coupon_credit'     => $coupon_credit,
        'coupon_code'       => 0,
        'wallet'            => $wallet_balance,
        'rest'              => (int) $rest,
        'amount_to_pay'     => $amount_to_pay,
        'payment_methods'   => $payment_methods,
        'messages'          => [
            $wallet_enable ? '' : 'کیف پول شما موقتا غیرفعال شده است.',
        ],
    ];

    wp_send_json_success($data);
}
//**********************************************************************************************************/
function checkout_check_coupon_api($request)
{

    $user_id = get_user_id_by_token(ez_authorization(false));

    $params = $request->get_params();

    $coupon_code = $params['coupon_code'];

    if (!isset($coupon_code) || empty($coupon_code))
        wp_send_json_error(array('error' => 'کد تخفیف را وارد کنید!'), 400);

    /***************************************************************************************/

    wp_set_current_user($user_id);
    wc_load_cart();
    $cart = WC()->cart;

    WC()->cart->calculate_totals();

    wc_clear_notices();

    $result = $cart->add_discount($coupon_code);

    //    if ( !$result ) {
    //        foreach ( wc_get_notices('error') as $error ) {
    //
    //        }
    //    }


    //    $data = [
    //        'coupon_code'   => $cart->coupon_discount_totals,
    //        'errors'        => wc_get_notices('error')
    //    ];


    wp_send_json_success(WC()->cart->get_total());

    /***************************************************************************************/

    $prepaid = WC()->session->get('prepaid');

    /***************************************************************************************/

    WC()->session->set('prepaid', $prepaid); // pass to the woocommerce_calculated_total action callback function

    WC()->cart->calculate_totals();

    /***************************************************************************************/

    wp_send_json_success($data);
}
//**********************************************************************************************************/
function checkout_place_order_api($request)
{
    global $wldb;

    $user_id = get_user_id_by_token(ez_authorization(false));

    $params = $request->get_params();

    $payment_method = $params['payment_method'];
    $players_phone  = $params['players_phone'];

    wp_set_current_user($user_id);
    wc_load_cart();
    $cart = WC()->cart;
    $get_cart = $cart->get_cart();

    $prepaid    = WC()->session->get('prepaid');
    $wallet     = WC()->session->get('wallet');

    $_POST['_wpnonce']              = wp_create_nonce('woocommerce-process_checkout');
    $_POST['billing_first_name']    = 'تست2';
    $_POST['billing_last_name']     = 'اسکیپ زوم';
    $_POST['billing_phone']         = '09353316152';
    $_POST['payment_method']        = 'WC_Zibal';

    $checkout = new Escapezoom_Checkout();
    $order_id = $checkout->process_checkout();

    if ($order_id) {
        $order = wc_get_order($order_id);

        if ($order) {
            $order->set_status('pending');
            $order->set_total($prepaid);
            $order->save();
        }

        $result['order']['id']              = $order->get_id();
        $result['order']['order_number']    = (int)$order->get_order_number();
        $result['order']['needs_payment']   = $order->needs_payment();
        $result['order']['pay_url']         = $order->needs_payment() ? str_replace('pay_for_order=true&', '', $order->get_checkout_payment_url()) : null;

        //        WC()->cart->empty_cart();

        wp_send_json_success($result);
    }

    wp_send_json_success($checkout);

    //    update_post_meta($order_id, 'players_phone', $players_phone);
    //    update_post_meta($order_id, '_order_total_2', $pre_paid);
    //    update_post_meta($order_id, 'sans_time', $sans_time);
    //    update_post_meta($order_id, 'order_method', 'api');
    //
    //    $payment_url = $order->needs_payment() ? str_replace('pay_for_order=true&', '', $order->get_checkout_payment_url(true)) : null;

    //    wp_send_json_success($payment_url);
}
//**********************************************************************************************************/
function checkout_place_order_api2($request)
{
    global $wldb;

    $token_uid        = get_user_id_by_token( ez_authorization( false ) );
    $wc_customer_id   = ( $token_uid !== false && $token_uid !== null && (int) $token_uid > 0 ) ? (int) $token_uid : 0;

    $params = $request->get_params();

    $product_id     = isset( $params['product_id'] ) ? absint( $params['product_id'] ) : 0;
    $quantity       = isset( $params['quantity'] ) ? max( 1, absint( $params['quantity'] ) ) : 1;
    $sans_time      = isset( $params['sans_time'] ) ? $params['sans_time'] : '';
    $players_phone  = isset( $params['players_phone'] ) ? sanitize_text_field( wp_unslash( $params['players_phone'] ) ) : '';
    $billing_first  = isset( $params['billing_first_name'] ) ? sanitize_text_field( wp_unslash( $params['billing_first_name'] ) ) : '';
    $billing_last   = isset( $params['billing_last_name'] ) ? sanitize_text_field( wp_unslash( $params['billing_last_name'] ) ) : '';

    if ( $product_id <= 0 ) {
        wp_send_json_error( 'شناسه محصول نامعتبر است.' );
    }

    $phone_digits = preg_replace( '/\D+/', '', $players_phone );
    if ( $phone_digits !== '' && strlen( $phone_digits ) === 10 && str_starts_with( $phone_digits, '9' ) ) {
        $phone_digits = '0' . $phone_digits;
    }

    /**************************/

    $bookings       = [];
    $auto_disable   = time() + (int)(get_post_meta($product_id, 'auto_disable', true)) * 60;
    if ($sans_time >= $auto_disable) { // first check sans status. it will meet next step if there is no error!

        $bookings_objs = json_decode(ez_reservation(array('type' => 'get_sans_lock', 'data' => array('product_id' => $product_id))));
        if (!empty($bookings_objs))
            foreach ($bookings_objs as $booking)
                $bookings[] = $booking->booking_time;

        $args = [
            "single_value"  => true,
            "query"         => "SELECT * FROM `wp_zb_booking_history` WHERE `room_id` like $product_id AND `booking_time` = $sans_time",
        ];
        $sans_obj = (array)json_decode(ez_reservation(array('type' => 'query_execution', 'data' => $args)));

        if ($sans_obj['status'] == 2)
            wp_send_json_error('سانس توسط مجموعه دار مسدود شده است.');

        elseif ($sans_obj['status'] == 1) {
            $msg_booked = function_exists('ez_api_message_sans_already_booked_confirmed')
                ? ez_api_message_sans_already_booked_confirmed(
                    (array) $sans_obj,
                    isset($wc_customer_id) ? (int) $wc_customer_id : (int) ($user_id ?? 0),
                    isset($phone_digits) ? (string) $phone_digits : ''
                )
                : 'سانس توسط شخص دیگری رزرو شده است.';
            wp_send_json_error($msg_booked);
        }

        elseif (in_array($sans_time, (array)$bookings))
            wp_send_json_error('سانس توسط شخص دیگری در حال رزرو است.');
    } else
        wp_send_json_error('سانس منقضی شده است.');

    $computed = ez_api_checkout_compute_amounts( $product_id, $quantity, $sans_time, 'partial' );
    if ( empty( $computed['valid'] ) ) {
        wp_send_json_error( 'خطا در محاسبه مبلغ سانس. لطفا دوباره تلاش کنید.' );
    }

    $sans_for_resolver = is_numeric( $sans_time ) ? (string) (int) $sans_time : '';
    $api_attempt       = [];
    if ( function_exists( 'ez_resolver_attempt_from_api_place_order' ) ) {
        $api_attempt = ez_resolver_attempt_from_api_place_order( $computed, $quantity, $sans_for_resolver, 'partial' );
    }

    if ( ! empty( $api_attempt ) && function_exists( 'ez_resolve_pending_booking_for_checkout' ) ) {
        $resolver_out = ez_resolve_pending_booking_for_checkout(
            [
                'customer_id'       => $wc_customer_id,
                'phone_normalized' => $wc_customer_id > 0 ? '' : $phone_digits,
                'product_id'       => $product_id,
                'sans_ts'          => $sans_for_resolver,
                'exclude_order_id' => 0,
                'attempt'          => $api_attempt,
                'use_replace_lock' => false,
            ]
        );

        if ( 'reuse' === $resolver_out['status'] && ! empty( $resolver_out['payment_url'] ) ) {
            wp_send_json_success(
                [
                    'mode'         => 'reuse',
                    'order_id'     => (int) ( $resolver_out['order_id'] ?? 0 ),
                    'payment_url'  => $resolver_out['payment_url'],
                    'pay_url'      => $resolver_out['payment_url'],
                ]
            );
        }
    } elseif (
        ( $wc_customer_id > 0 && function_exists( 'ez_customer_pending_order_same_slot' ) && ez_customer_pending_order_same_slot( $wc_customer_id, $product_id, $sans_time, 0 ) )
        ||
        (
            $wc_customer_id <= 0 && strlen( $phone_digits ) === 11 && str_starts_with( $phone_digits, '09' )
            && function_exists( 'ez_guest_pending_order_same_slot_by_phone' )
            && ez_guest_pending_order_same_slot_by_phone( $phone_digits, $product_id, $sans_time, 0 )
        )
    ) {
        wp_send_json_error( 'برای این سانس یک سفارش در انتظار پرداخت از قبل ثبت شده است.' );
    }

    /**************************/

    $wallet_balance = 0.0;
    if ( $wc_customer_id > 0 && is_object( $wldb ) && method_exists( $wldb, 'get_balance' ) ) {
        $wallet_balance = (float) $wldb->get_balance( $wc_customer_id );
    }
    $pre_paid       = (float) ( $computed['prepaid_amount'] ?? 0 );
    $amount_to_pay  = (float) ( $computed['online_payable'] ?? 0 );

    $wallet_enable = true;
    if ( $wc_customer_id > 0 ) {
        $wallet_expiration = get_user_meta( $wc_customer_id, 'wallet_expiration', true );
        if ( $wallet_expiration ) {
            $wallet_enable = false;

            if ( time() >= $wallet_expiration ) {
                delete_user_meta( $wc_customer_id, 'wallet_expiration' );
                $wallet_enable = true;
            }
        }

        update_user_meta( $wc_customer_id, 'wallet_expiration', time() + 5 * 60 );
    }

    /**************************/

    $bill_phone_row = strlen( $phone_digits ) === 11 ? $phone_digits : $players_phone;
    $address = [
        'first_name'    => $billing_first !== '' ? $billing_first : '',
        'last_name'     => $billing_last !== '' ? $billing_last : '',
        'phone'         => $bill_phone_row !== '' ? $bill_phone_row : '',
    ];

    $order_data = [
        'status'        => 'pending',
        'customer_id'   => $wc_customer_id,
    ];

    $order      = wc_create_order($order_data);
    $order_id   = $order->get_id();

    $product = new WC_Product($product_id);

    $order->add_product(
        $product,
        $quantity,
        [
            'totals' => [
                'total' => $amount_to_pay,
            ]
        ]
    );

    $order->set_address( $address, 'billing' );

    $order->calculate_totals();

    update_post_meta($order_id, 'players_phone', $players_phone);
    update_post_meta($order_id, '_order_total_2', $pre_paid);
    update_post_meta($order_id, 'sans_time', $sans_time);
    update_post_meta($order_id, 'order_method', 'api');
    update_post_meta($order_id, 'ez_payment_type', 'partial');
    update_post_meta($order_id, 'prepaid', $pre_paid);
    update_post_meta($order_id, 'total_payment', (float) ( $computed['gross_total'] ?? 0 ) );

    if ( function_exists( 'save_to_markting_table' ) ) {
        save_to_markting_table( $order_id, array(), $order );
    }
    if ( function_exists( 'ez_remove_checkout_intents_for_order' ) ) {
        ez_remove_checkout_intents_for_order( $order_id );
    }

    $payment_url = $order->needs_payment() ? str_replace('pay_for_order=true&', '', $order->get_checkout_payment_url(true)) : null;

    wp_send_json_success($payment_url);
}
//**********************************************************************************************************/
function checkout_thankyou_api($request)
{

    $user_id = get_user_id_by_token(ez_authorization(false));

    $params = $request->get_params();
    $order_id = $params['ID'];

    if (!isset($order_id) || empty($order_id))
        wp_send_json_error(array('error' => 'order_id Required'), 400);

    //    if ( get_post_meta( $order_id, '_customer_user', true ) != $user_id )
    //        wp_send_json_error(array ('error' => 'این سفارش متعلق به شما نیست!'), 400);

    $order = wc_get_order($order_id);

    foreach ($order->get_items() as $item) {
        $product_id     = $item['product_id'];
        $product_name   = $item['name'];
        $quantity       = $item['quantity'];
    }

    $order_status = $order->get_status() == 'partially-paid' ? 'success' : 'fail';
    if ($order_status == 'success') {

        $pish_per_person    = get_post_meta($order_id, 'ticket_tedad', true);
        $pish_per_person    = !empty($pish_per_person) ? $pish_per_person : get_post_meta($product_id, 'pish_pardakht_per_person', true);
        $pish_per_person    = !empty($pish_per_person) ? $pish_per_person : 1;

        $pish       = get_post_meta($order_id, "_order_total_2", true);
        $pish_final = $pish ?: get_post_meta($order_id, "_order_total", true);

        $item_total = $pish_final / $pish_per_person * $quantity;

        $args = [
            "single_value"  => true,
            "query"         => "SELECT * FROM `wp_zb_booking_history` WHERE `wc_order_id` = $order_id",
        ];
        $response = ez_reservation(array('type' => 'query_execution', 'data' => $args));
        $row = (array)json_decode($response);

        $brand_data = get_the_terms($product_id, 'product_brand')[0];

        $product_meta = ez_get_product_meta($product_id);

        $data = [
            'order_id'      => (int)$order_id,
            'order_status'  => $order_status,
            'tickets_count' => $quantity,
            'sans_time'     => (int)$row['booking_time'],
            'total_payment' => (int)$item_total,
            'prepaid'       => (int)$pish_final,
            'rest'          => (int)$item_total - $pish_final,
            'product_type'  => $product_meta->product_type,
            'product_url'   => trim_home_url(get_permalink($product_id)),
            'product_title' => $product_name,
            'address_info'  => [
                'city'      => $product_meta->city_name,
                'address'   => get_field('room_address', $product_id),
                'lat'       => get_field('room_lat', $product_id),
                'long'      => get_field('room_long', $product_id),
            ],
            'brand_data'    => [
                'title'     => $brand_data->name,
                'logo'      => wp_get_attachment_url(get_term_meta($brand_data->term_id, 'thumbnail_id', true)),
                'phones'    => [
                    get_field('room_phone', $product_id),
                    get_field('room_phone_2', $product_id),
                ],
            ],
            'qrcode_data'   => "/geo.php?g=" . get_field('room_lat', $product_id) . ',' . get_field('room_long', $product_id),
        ];
    } else
        $data = [
            'order_id'      => (int)$order_id,
            'order_status'  => $order_status,
            'error_code'    => 25,
            'message'       => 'متاسفانه تداخلی در سانس پیش آمده و مبلغ شما مسترد خواهد شد. لطفا سانس دیگری رزرو فرمایید.',
            'return_url'    => '/product/5104/reservation',
        ];

    wp_send_json_success($data);
}
//**********************************************************************************************************/
function checkout_get_api2($request)
{
    global $wldb, $woocommerce;

    $user_id = get_user_id_by_token(ez_authorization(false));

    $params = $request->get_params();
    $product_id     = $params['product_id'];
    $quantity       = $params['quantity'];
    $sans_time      = $params['sans_time'];
    $auto_disable   = time() + (int)(get_post_meta($product_id, 'auto_disable', true)) * 60;

    if ($sans_time >= $auto_disable) { // first check sans status. it will meet next step if there is no error!

        $bookings_objs = json_decode(ez_reservation(array('type' => 'get_sans_lock', 'data' => array('product_id' => $product_id))));
        if (!empty($bookings_objs))
            foreach ($bookings_objs as $booking)
                $bookings[] = $booking->booking_time;

        $args = [
            "single_value"  => true,
            "query"         => "SELECT * FROM `wp_zb_booking_history` WHERE `room_id` like $product_id AND `booking_time` = $sans_time",
        ];
        $sans_obj = (array)json_decode(ez_reservation(array('type' => 'query_execution', 'data' => $args)));

        if ($sans_obj['status'] == 2)
            wp_send_json_error('سانس توسط مجموعه دار مسدود شده است.');

        elseif ($sans_obj['status'] == 1) {
            $msg_booked = function_exists('ez_api_message_sans_already_booked_confirmed')
                ? ez_api_message_sans_already_booked_confirmed(
                    (array) $sans_obj,
                    isset($wc_customer_id) ? (int) $wc_customer_id : (int) ($user_id ?? 0),
                    isset($phone_digits) ? (string) $phone_digits : ''
                )
                : 'سانس توسط شخص دیگری رزرو شده است.';
            wp_send_json_error($msg_booked);
        }

        elseif (in_array($sans_time, (array)$bookings))
            wp_send_json_error('سانس توسط شخص دیگری در حال رزرو است.');
    } else
        wp_send_json_error('سانس منقضی شده است.');

    $computed = ez_api_checkout_compute_amounts( $product_id, $quantity, $sans_time, 'partial' );
    if ( empty( $computed['valid'] ) ) {
        wp_send_json_error( 'خطا در محاسبه مبلغ سانس. لطفا دوباره تلاش کنید.' );
    }

    $total = (float) ( $computed['gross_total'] ?? 0 );
    $pre_paid = (float) ( $computed['prepaid_amount'] ?? 0 );
    $rest = (float) ( $computed['offline_payable'] ?? 0 );
    $amount_to_pay = (float) ( $computed['online_payable'] ?? 0 );
    $wallet_balance = ( is_object( $wldb ) && method_exists( $wldb, 'get_balance' ) ) ? (float) $wldb->get_balance($user_id) : 0.0;

    $wallet_enable      = true;
    $wallet_expiration  = get_user_meta($user_id, 'wallet_expiration', true);
    if ($wallet_expiration) {
        $wallet_enable = false;

        if (time() >= $wallet_expiration) {
            delete_user_meta($user_id, 'wallet_expiration');
            $wallet_enable = true;
        }
    }

    $payment_methods = [];
    foreach ($woocommerce->payment_gateways->payment_gateways as $gateway) {
        if ($gateway->enabled == 'yes') {

            $icon = '';
            if ($gateway->id == 'WC_Zibal')
                $icon = 'http://escapezoom.ir/wp-content/uploads/2024/10/zibal_ico.png';

            elseif ($gateway->id == 'wallet')
                $icon = 'http://escapezoom.ir/wp-content/uploads/2024/10/wallet_ico.png';

            $payment_methods[] = [
                'code'         => $gateway->id,
                'title'        => $gateway->title,
                'description'  => $gateway->description,
                'icon'         => $icon,
            ];
        }
    }

    $data = [
        'total'             => (int) $total,
        'prepaid'           => (int) $pre_paid,
        'rest'              => (int) $rest,
        'wallet'            => $wallet_balance,
        'amount_to_pay'     => $amount_to_pay,
        'payment_methods'   => $payment_methods,
        'messages'          => [
            $wallet_enable ? '' : 'کیف پول شما موقتا غیرفعال شده است.',
            $wallet_enable ? '' : 'کیف پول شما موقتا غیرفعال شده است.',
        ],
    ];

    wp_send_json_success($data);
} // نسخه اولیه چک اوت
//**********************************************************************************************************/
function ez_validate_mobile($mobile)
{

    if (ctype_digit($mobile)) {

        if (strlen($mobile) == 11 && substr($mobile, 0, 2) == "09") {
            return substr($mobile, 1);
        } elseif (strlen($mobile) == 10 && substr($mobile, 0, 1) == "9") {
            return $mobile;
        } else {
            throw new Exception('شماره موبایل صحیح نیست');
        }
    } else {
        throw new Exception('شماره موبایل صحیح نیست');
    }
}
//**********************************************************************************************************/
function get_ticket_status($ticket_id)
{

    if (get_post_meta($ticket_id, 'ticket_closed', true))
        $status = 'closed';
    else
        if (get_post_meta($ticket_id, 'respond_user_role', true) == "admin")
        $status = 'respond';

    elseif (get_post_meta($ticket_id, 'respond_user_role', true) == "user" && get_post_meta($ticket_id, 'admin_seen', true))
        $status = 'pending';

    else
        $status = 'open';

    return $status;
}
//**********************************************************************************************************/
function ticket_verify($ticket_id, $user_id)
{

    if (get_post($ticket_id)->post_author == $user_id)
        return true;

    return false;
}

//**********************************************************************************************************/
// Voucher API Functions

/**
 * Create a new voucher/coupon
 * POST /api/v1/vouchers/create
 */
function vouchers_create_api($request)
{
    try {
        $params = $request->get_params();

        // Validate required parameters
        if (empty($params['code'])) {
            wp_send_json_error(array('message' => 'کد تخفیف الزامی است'), 400);
        }

        if (empty($params['discount_type']) || !in_array($params['discount_type'], ['percent', 'fixed_cart', 'fixed_product'])) {
            wp_send_json_error(array('message' => 'نوع تخفیف باید percent، fixed_cart یا fixed_product باشد'), 400);
        }

        if (empty($params['discount_amount']) || !is_numeric($params['discount_amount']) || $params['discount_amount'] <= 0) {
            wp_send_json_error(array('message' => 'مبلغ تخفیف باید عدد مثبت باشد'), 400);
        }

        // Check if coupon code already exists
        $existing_coupon = get_page_by_title($params['code'], OBJECT, 'shop_coupon');
        if ($existing_coupon) {
            wp_send_json_error(array('message' => 'کد تخفیف قبلاً وجود دارد'), 400);
        }

        // Create coupon
        $coupon_data = array(
            'post_title' => $params['code'],
            'post_content' => $params['description'] ?? '',
            'post_status' => 'publish',
            'post_author' => 1,
            'post_type' => 'shop_coupon'
        );

        $coupon_id = wp_insert_post($coupon_data);

        if (is_wp_error($coupon_id)) {
            wp_send_json_error(array('message' => 'خطا در ایجاد کد تخفیف'), 500);
        }

        // Set coupon meta data
        update_post_meta($coupon_id, 'discount_type', $params['discount_type']);
        update_post_meta($coupon_id, 'coupon_amount', $params['discount_amount']);
        update_post_meta($coupon_id, 'individual_use', 'yes');
        update_post_meta($coupon_id, 'usage_limit', $params['usage_limit'] ?? '');
        update_post_meta($coupon_id, 'usage_limit_per_user', $params['usage_limit_per_user'] ?? '');
        update_post_meta($coupon_id, 'limit_usage_to_x_items', $params['limit_usage_to_x_items'] ?? '');
        update_post_meta($coupon_id, 'usage_count', 0);
        update_post_meta($coupon_id, 'expiry_date', $params['expiry_date'] ?? '');
        update_post_meta($coupon_id, 'apply_before_tax', 'yes');
        update_post_meta($coupon_id, 'free_shipping', $params['free_shipping'] ?? 'no');
        update_post_meta($coupon_id, 'exclude_sale_items', $params['exclude_sale_items'] ?? 'no');
        update_post_meta($coupon_id, 'minimum_amount', $params['minimum_amount'] ?? '');
        update_post_meta($coupon_id, 'maximum_amount', $params['maximum_amount'] ?? '');

        // Set product categories if provided
        if (!empty($params['product_categories'])) {
            $categories = is_array($params['product_categories']) ? $params['product_categories'] : explode(',', $params['product_categories']);
            update_post_meta($coupon_id, 'product_categories', $categories);
        }

        // Set excluded product categories if provided
        if (!empty($params['excluded_product_categories'])) {
            $excluded_categories = is_array($params['excluded_product_categories']) ? $params['excluded_product_categories'] : explode(',', $params['excluded_product_categories']);
            update_post_meta($coupon_id, 'exclude_product_categories', $excluded_categories);
        }

        // Set products if provided
        if (!empty($params['product_ids'])) {
            $products = is_array($params['product_ids']) ? $params['product_ids'] : explode(',', $params['product_ids']);
            update_post_meta($coupon_id, 'product_ids', $products);
        }

        // Set excluded products if provided
        if (!empty($params['excluded_product_ids'])) {
            $excluded_products = is_array($params['excluded_product_ids']) ? $params['excluded_product_ids'] : explode(',', $params['excluded_product_ids']);
            update_post_meta($coupon_id, 'exclude_product_ids', $excluded_products);
        }

        // Set email restrictions if provided
        if (!empty($params['email_restrictions'])) {
            $emails = is_array($params['email_restrictions']) ? $params['email_restrictions'] : explode(',', $params['email_restrictions']);
            update_post_meta($coupon_id, 'customer_email', $emails);
        }

        wp_send_json_success(array(
            'message' => 'کد تخفیف با موفقیت ایجاد شد',
            'coupon_id' => $coupon_id,
            'code' => $params['code']
        ));
    } catch (Exception $e) {
        wp_send_json_error(array('message' => $e->getMessage()), 500);
    }
}

/**
 * Assign voucher to users
 * POST /api/v1/vouchers/assign
 */
function vouchers_assign_api($request)
{
    try {
        $params = $request->get_params();

        // Validate required parameters
        if (empty($params['userIds']) || !is_array($params['userIds'])) {
            wp_send_json_error(array('message' => 'لیست کاربران الزامی است'), 400);
        }

        if (empty($params['voucherPoolName'])) {
            wp_send_json_error(array('message' => 'نام پول ووچر الزامی است'), 400);
        }

        $results = array();

        foreach ($params['userIds'] as $user_id) {
            // Validate user exists
            $user = get_user_by('ID', $user_id);
            if (!$user) {
                $results[] = array(
                    'userId' => $user_id,
                    'voucherCode' => null,
                    'error' => 'کاربر یافت نشد'
                );
                continue;
            }

            // Generate unique voucher code
            $voucher_code = $params['voucherPoolName'] . '_' . $user_id . '_' . time() . '_' . wp_rand(1000, 9999);

            // Create coupon for this user
            $coupon_data = array(
                'post_title' => $voucher_code,
                'post_content' => 'کد تخفیف اختصاصی برای کاربر ' . $user->display_name,
                'post_status' => 'publish',
                'post_author' => 1,
                'post_type' => 'shop_coupon'
            );

            $coupon_id = wp_insert_post($coupon_data);

            if (is_wp_error($coupon_id)) {
                $results[] = array(
                    'userId' => $user_id,
                    'voucherCode' => null,
                    'error' => 'خطا در ایجاد کد تخفیف'
                );
                continue;
            }

            // Set coupon meta data
            update_post_meta($coupon_id, 'discount_type', $params['discount_type'] ?? 'percent');
            update_post_meta($coupon_id, 'coupon_amount', $params['discount_amount'] ?? 10);
            update_post_meta($coupon_id, 'individual_use', 'yes');
            update_post_meta($coupon_id, 'usage_limit', 1);
            update_post_meta($coupon_id, 'usage_limit_per_user', 1);
            update_post_meta($coupon_id, 'usage_count', 0);
            update_post_meta($coupon_id, 'expiry_date', $params['expiry_date'] ?? '');
            update_post_meta($coupon_id, 'apply_before_tax', 'yes');
            update_post_meta($coupon_id, 'free_shipping', $params['free_shipping'] ?? 'no');
            update_post_meta($coupon_id, 'exclude_sale_items', $params['exclude_sale_items'] ?? 'no');
            update_post_meta($coupon_id, 'minimum_amount', $params['minimum_amount'] ?? '');
            update_post_meta($coupon_id, 'maximum_amount', $params['maximum_amount'] ?? '');

            // Set product categories if provided
            if (!empty($params['product_categories'])) {
                $categories = is_array($params['product_categories']) ? $params['product_categories'] : explode(',', $params['product_categories']);
                update_post_meta($coupon_id, 'product_categories', $categories);
            }

            // Set excluded product categories if provided
            if (!empty($params['excluded_product_categories'])) {
                $excluded_categories = is_array($params['excluded_product_categories']) ? $params['excluded_product_categories'] : explode(',', $params['excluded_product_categories']);
                update_post_meta($coupon_id, 'exclude_product_categories', $excluded_categories);
            }

            // Set products if provided
            if (!empty($params['product_ids'])) {
                $products = is_array($params['product_ids']) ? $params['product_ids'] : explode(',', $params['product_ids']);
                update_post_meta($coupon_id, 'product_ids', $products);
            }

            // Set excluded products if provided
            if (!empty($params['excluded_product_ids'])) {
                $excluded_products = is_array($params['excluded_product_ids']) ? $params['excluded_product_ids'] : explode(',', $params['excluded_product_ids']);
                update_post_meta($coupon_id, 'exclude_product_ids', $excluded_products);
            }

            // Set email restriction for this specific user
            update_post_meta($coupon_id, 'customer_email', array($user->user_email));

            $results[] = array(
                'userId' => $user_id,
                'voucherCode' => $voucher_code
            );
        }

        wp_send_json_success($results);
    } catch (Exception $e) {
        wp_send_json_error(array('message' => $e->getMessage()), 500);
    }
}

/**
 * Validate voucher code
 * POST /api/v1/vouchers/validate
 */
function vouchers_validate_api($request)
{
    try {
        $params = $request->get_params();

        if (empty($params['code'])) {
            wp_send_json_error(array('message' => 'کد تخفیف الزامی است'), 400);
        }

        // Get coupon by code
        $coupon = get_page_by_title($params['code'], OBJECT, 'shop_coupon');

        if (!$coupon) {
            wp_send_json_error(array('message' => 'کد تخفیف یافت نشد'), 404);
        }

        $coupon_id = $coupon->ID;

        // Check if coupon is active
        if ($coupon->post_status !== 'publish') {
            wp_send_json_error(array('message' => 'کد تخفیف غیرفعال است'), 400);
        }

        // Check expiry date
        $expiry_date = get_post_meta($coupon_id, 'expiry_date', true);
        if (!empty($expiry_date) && strtotime($expiry_date) < current_time('timestamp')) {
            wp_send_json_error(array('message' => 'کد تخفیف منقضی شده است'), 400);
        }

        // Check usage limit
        $usage_limit = get_post_meta($coupon_id, 'usage_limit', true);
        $usage_count = get_post_meta($coupon_id, 'usage_count', true);

        if (!empty($usage_limit) && $usage_count >= $usage_limit) {
            wp_send_json_error(array('message' => 'کد تخفیف به حد مجاز استفاده رسیده است'), 400);
        }

        // Get coupon details
        $discount_type = get_post_meta($coupon_id, 'discount_type', true);
        $discount_amount = get_post_meta($coupon_id, 'coupon_amount', true);
        $minimum_amount = get_post_meta($coupon_id, 'minimum_amount', true);
        $maximum_amount = get_post_meta($coupon_id, 'maximum_amount', true);
        $product_categories = get_post_meta($coupon_id, 'product_categories', true);
        $excluded_product_categories = get_post_meta($coupon_id, 'exclude_product_categories', true);
        $product_ids = get_post_meta($coupon_id, 'product_ids', true);
        $excluded_product_ids = get_post_meta($coupon_id, 'exclude_product_ids', true);
        $customer_email = get_post_meta($coupon_id, 'customer_email', true);
        $free_shipping = get_post_meta($coupon_id, 'free_shipping', true);

        wp_send_json_success(array(
            'valid' => true,
            'code' => $params['code'],
            'discount_type' => $discount_type,
            'discount_amount' => $discount_amount,
            'minimum_amount' => $minimum_amount,
            'maximum_amount' => $maximum_amount,
            'product_categories' => $product_categories,
            'excluded_product_categories' => $excluded_product_categories,
            'product_ids' => $product_ids,
            'excluded_product_ids' => $excluded_product_ids,
            'customer_email' => $customer_email,
            'free_shipping' => $free_shipping,
            'usage_count' => $usage_count,
            'usage_limit' => $usage_limit,
            'expiry_date' => $expiry_date
        ));
    } catch (Exception $e) {
        wp_send_json_error(array('message' => $e->getMessage()), 500);
    }
}

/**
 * List vouchers
 * GET /api/v1/vouchers/list
 */
function vouchers_list_api($request)
{
    try {
        $params = $request->get_params();

        $args = array(
            'post_type' => 'shop_coupon',
            'post_status' => 'publish',
            'posts_per_page' => $params['per_page'] ?? 20,
            'paged' => $params['page'] ?? 1,
            'orderby' => 'date',
            'order' => 'DESC'
        );

        // Add search if provided
        if (!empty($params['search'])) {
            $args['s'] = $params['search'];
        }

        $coupons = get_posts($args);
        $total_coupons = wp_count_posts('shop_coupon')->publish;

        $vouchers = array();

        foreach ($coupons as $coupon) {
            $coupon_id = $coupon->ID;

            $vouchers[] = array(
                'id' => $coupon_id,
                'code' => $coupon->post_title,
                'description' => $coupon->post_content,
                'discount_type' => get_post_meta($coupon_id, 'discount_type', true),
                'discount_amount' => get_post_meta($coupon_id, 'coupon_amount', true),
                'usage_count' => get_post_meta($coupon_id, 'usage_count', true),
                'usage_limit' => get_post_meta($coupon_id, 'usage_limit', true),
                'expiry_date' => get_post_meta($coupon_id, 'expiry_date', true),
                'minimum_amount' => get_post_meta($coupon_id, 'minimum_amount', true),
                'maximum_amount' => get_post_meta($coupon_id, 'maximum_amount', true),
                'product_categories' => get_post_meta($coupon_id, 'product_categories', true),
                'excluded_product_categories' => get_post_meta($coupon_id, 'exclude_product_categories', true),
                'product_ids' => get_post_meta($coupon_id, 'product_ids', true),
                'excluded_product_ids' => get_post_meta($coupon_id, 'exclude_product_ids', true),
                'customer_email' => get_post_meta($coupon_id, 'customer_email', true),
                'free_shipping' => get_post_meta($coupon_id, 'free_shipping', true),
                'created_date' => $coupon->post_date
            );
        }

        wp_send_json_success(array(
            'vouchers' => $vouchers,
            'total' => $total_coupons,
            'page' => $params['page'] ?? 1,
            'per_page' => $params['per_page'] ?? 20
        ));
    } catch (Exception $e) {
        wp_send_json_error(array('message' => $e->getMessage()), 500);
    }
}


/**
 * Voucher Pool Functions - Helper functions for each game category
 * These functions return category IDs for each game type
 */

/**
 * Get category IDs for Escape Room (اتاق فرار)
 */
function voucher_pool_escaperoom()
{
    // Return all escape room category IDs
    $cities = explode(',', '15,162,121,122,293,1121,435,580,1090,1192,123,321,355,277,1142,637,416,1023,1026,269,909,1201,482,285,569,1039,332,495,387,570,190,1036,781,1126,270,187,304,982,272,1133,289,544,259,1143,919,1097,746');
    $genres = explode(',', '346,843,128,344,124,512,127,345,342,126,754,459,564,343,571,125,178');

    // Return all escape room categories
    return array_merge($cities, $genres);
}

/**
 * Get category IDs for Scary Cinema (سینما ترس)
 */
function voucher_pool_scary_cinema()
{
    // Return all scary cinema category IDs
    return explode(',', '1217,1199,918,1134,1141,913,1004,925,1072,1176,1009,926,1208,904');
}

/**
 * Get category IDs for Laser Tag (لیزرتگ)
 */
function voucher_pool_lasertag()
{
    // Return all laser tag category IDs
    return explode(',', '1151,1175,1148,1196,1147,1219,1158,1218,1149,1150,1156');
}

/**
 * Get category IDs for Rage Room (اتاق خشم)
 */
function voucher_pool_rageroom()
{
    // Return all rage room category IDs
    return array(1186, 1074);
}

/**
 * Get category IDs for All Games (همه بازی‌ها)
 */
function voucher_pool_all()
{
    // Return all category IDs from all game types
    $all_categories = array_merge(
        voucher_pool_escaperoom(),
        voucher_pool_scary_cinema(),
        voucher_pool_lasertag(),
        voucher_pool_rageroom()
    );

    // Remove duplicates and return
    return array_unique($all_categories);
}

/**
 * Create category-based vouchers for users
 * POST /api/v1/vouchers/create_category
 * 
 * Request Headers:
 * - amountVoucher: Fixed discount amount (number in Toman - مبلغ ثابت تخفیف به تومان)
 * 
 * Request Body:
 * {
 *   "userIds": ["123", "456"],
 *   "voucherPoolName": "escaperoom" | "scary_cinema" | "lasertag" | "rageroom" | "all"
 * }
 * 
 * Response:
 * [
 *   {
 *     "userId": "123",
 *     "voucherCode": "escaperoom_123_1234567890_1234"
 *   }
 * ]
 */
function vouchers_create_category_api($request)
{
    try {
        $params = $request->get_params();
        $headers = $request->get_headers();

        // Validate required parameters
        if (empty($params['userIds']) || !is_array($params['userIds'])) {
            wp_send_json_error(array('message' => 'لیست کاربران الزامی است'), 400);
        }

        if (empty($params['voucherPoolName'])) {
            wp_send_json_error(array('message' => 'نام دسته‌بندی الزامی است'), 400);
        }

        // Get discount amount from header (fixed amount in Toman)
        $discount_amount = 100000; // Default value: 100000 Toman
        if (!empty($headers['amountvoucher'])) {
            $discount_amount = floatval($headers['amountvoucher'][0]);
        }

        // Map voucherPoolName to function
        $pool_functions = array(
            'escaperoom' => 'voucher_pool_escaperoom',
            'scary_cinema' => 'voucher_pool_scary_cinema',
            'lasertag' => 'voucher_pool_lasertag',
            'rageroom' => 'voucher_pool_rageroom',
            'all' => 'voucher_pool_all'
        );

        // Validate voucherPoolName
        if (!isset($pool_functions[$params['voucherPoolName']])) {
            wp_send_json_error(array(
                'message' => 'نام دسته‌بندی نامعتبر است. مقادیر مجاز: escaperoom, scary_cinema, lasertag, rageroom, all'
            ), 400);
        }

        // Get category IDs for this pool
        $category_ids = call_user_func($pool_functions[$params['voucherPoolName']]);

        $results = array();

        foreach ($params['userIds'] as $user_id) {
            // Validate user exists
            $user = get_user_by('ID', $user_id);
            if (!$user) {
                $results[] = array(
                    'userId' => $user_id,
                    'voucherCode' => null,
                    'error' => 'کاربر یافت نشد'
                );
                continue;
            }

            // Generate unique voucher code
            $voucher_code = $params['voucherPoolName'] . '_' . $user_id . '_' . time() . '_' . wp_rand(1000, 9999);

            // Create coupon for this user
            $coupon_data = array(
                'post_title' => $voucher_code,
                'post_content' => 'کد تخفیف اختصاصی برای ' . $params['voucherPoolName'] . ' - کاربر ' . $user->display_name,
                'post_status' => 'publish',
                'post_author' => 1,
                'post_type' => 'shop_coupon'
            );

            $coupon_id = wp_insert_post($coupon_data);

            if (is_wp_error($coupon_id)) {
                $results[] = array(
                    'userId' => $user_id,
                    'voucherCode' => null,
                    'error' => 'خطا در ایجاد کد تخفیف'
                );
                continue;
            }

            // Set coupon meta data
            update_post_meta($coupon_id, 'discount_type', 'fixed_cart');
            update_post_meta($coupon_id, 'coupon_amount', $discount_amount);
            update_post_meta($coupon_id, 'individual_use', 'yes');
            update_post_meta($coupon_id, 'usage_limit', 1);
            update_post_meta($coupon_id, 'usage_limit_per_user', 1);
            update_post_meta($coupon_id, 'usage_count', 0);
            update_post_meta($coupon_id, 'apply_before_tax', 'yes');
            update_post_meta($coupon_id, 'free_shipping', 'no');
            update_post_meta($coupon_id, 'exclude_sale_items', 'no');

            // Set product categories for this voucher pool
            update_post_meta($coupon_id, 'product_categories', $category_ids);

            // Set email restriction for this specific user
            update_post_meta($coupon_id, 'customer_email', array($user->user_email));

            $results[] = array(
                'userId' => $user_id,
                'voucherCode' => $voucher_code
            );
        }

        wp_send_json_success($results);
    } catch (Exception $e) {
        wp_send_json_error(array('message' => $e->getMessage()), 500);
    }
}

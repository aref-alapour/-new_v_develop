<?php

function write_log($data)
{
    if (true === WP_DEBUG) {
        if (is_array($data) || is_object($data)) {
            error_log(print_r($data, true));
        } else {
            error_log($data);
        }
    }
}

/**
 * Check if the 'jdate' function does not already exist, then include the 'jdate.php' file.
 */
if (! function_exists('jdate')) {
    require_once __DIR__ . '/jdate.php';
}

/**
 * Adds support for the title tag in the theme after setup.
 *
 * @param string $tag The tag to add support for.
 *
 * @return void
 */
add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
});

/**
 * Flush rewrite rules on theme activation
 */
add_action('after_switch_theme', function () {
    flush_rewrite_rules();
    update_option('escapezoom_rewrite_rules_flushed', '1');
});

/**
 * Remove a previously added action from a hook.
 *
 * @param string $hook The name of the hook to remove the action from.
 * @param string $callback The callback function to be removed from the hook.
 */
remove_action('woocommerce_account_content', 'woocommerce_account_content');

/**
 * Adds a custom action to the 'woocommerce_account_content' hook.
 *
 * This function checks the query variables in the global $wp object and loads the corresponding template file
 * based on the query variable key. If the key is 'pagename', it skips to the next iteration.
 * If the key is 'page', it sets the page variable to 'dashboard'.
 * It then includes the template file based on the page key and passes the current user information to it.
 *
 * @param callable $function The callback function to execute when the action is triggered.
 * @param int $priority The priority at which the action should be executed.
 */
add_action('woocommerce_account_content', function () {
    global $wp;

    if (! empty($wp->query_vars)) {
        foreach ($wp->query_vars as $key => $value) {
            if ('pagename' === $key) {
                continue;
            }

            $page = $key == 'page' ? 'dashboard' : $key;

            wc_get_template('myaccount/pages/' . $page . '.php', [
                'current_user' => get_user_by('id', get_current_user_id()),
            ]);

            echo "</div>";

            return;
        }
    }
}, 100);

/**
 * Adds custom rewrite endpoints to WordPress during the 'init' action.
 *
 * This function adds multiple custom rewrite endpoints to WordPress, allowing for custom URLs to be handled.
 * Each endpoint is associated with a specific page in the WordPress site.
 *
 * Endpoints added:
 * - sans-manager
 * - sells
 * - wallet
 * - notices
 * - offers
 * - products
 * - orders
 * - invitation
 * - my-collections
 * - tickets
 * - settings
 *
 * @return void
 */
add_action('init', function () {
    add_rewrite_endpoint('sans-manager', EP_ROOT | EP_PAGES);
    add_rewrite_endpoint('sells', EP_ROOT | EP_PAGES);
    add_rewrite_endpoint('wallet', EP_ROOT | EP_PAGES);
    add_rewrite_endpoint('notices', EP_ROOT | EP_PAGES);
    add_rewrite_endpoint('credit', EP_ROOT | EP_PAGES);
    add_rewrite_endpoint('sans-settings', EP_ROOT | EP_PAGES);
    add_rewrite_endpoint('offers', EP_ROOT | EP_PAGES);
    add_rewrite_endpoint('products', EP_ROOT | EP_PAGES);
    add_rewrite_endpoint('orders', EP_ROOT | EP_PAGES);
    add_rewrite_endpoint('comments', EP_ROOT | EP_PAGES);
    add_rewrite_endpoint('invitation', EP_ROOT | EP_PAGES);
    add_rewrite_endpoint('my-collections', EP_ROOT | EP_PAGES);
    //	add_rewrite_endpoint('tickets', EP_ROOT | EP_PAGES);
    add_rewrite_endpoint('settings', EP_ROOT | EP_PAGES);
    add_rewrite_endpoint('points', EP_ROOT | EP_PAGES);
    add_rewrite_endpoint('cancellation-requests', EP_ROOT | EP_PAGES);
    add_rewrite_endpoint('cancellation-history', EP_ROOT | EP_PAGES);

    // Flush rewrite rules if this is the first time endpoints are added
    if (get_option('escapezoom_rewrite_rules_flushed') !== '1') {
        flush_rewrite_rules();
        update_option('escapezoom_rewrite_rules_flushed', '1');
    }
});

/**
 * Modify the WooCommerce account menu items by adding, removing, or updating specific items.
 *
 * @param array $items An array of WooCommerce account menu items.
 *
 * @return array The modified array of WooCommerce account menu items.
 */
add_filter('woocommerce_account_menu_items', function ($items) {
    unset($items['woo-wallet']);
    unset($items['edit-account']);
    unset($items['customer-logout']);

    if (! has_role('customer')) {
        $items['sans-manager'] = 'مدیریت سانس';
    }

    if (! has_role('customer')) {
        $items['sells'] = 'فروش های من';
    }

    if (! has_role('customer')) {
        $items['comments'] = 'کامنت ها';
    }

    if (! has_role('sans_manager')) {
        $items['wallet'] = 'کیف پول';
    }

    if (! has_role('customer')) {
        $items['products'] = 'بازی های من';
    }

    if (in_array(get_current_user_id(), [3325])) {
        $items['sans-settings'] = 'تنظیمات سانس';
    }

    $items['notices'] = 'اطلاعیه ها';

    if (in_array(get_current_user_id(), [8075, 17033, 26681, 3325])) {
        $items['credit'] = 'اعتبار تخفیف';
    }

    $items['orders'] = 'رزرو های من';

    $items['invitation'] = 'دعوت ها';

    $items['my-collections'] = 'کالکشن های من';

    //	$items['tickets']         = 'تیکت های پشتیبانی';

    // Add cancellation pages for administrators and owners
    if (current_user_can('administrator') || has_role('compiler')) {
        $items['cancellation-requests'] = 'درخواست ها';
    }

    $items['settings'] = 'تنظیمات حساب کاربری';

    $items['customer-logout'] = 'خروج';

    return $items;
});

// Add icons to WooCommerce account menu items
add_filter('woocommerce_account_menu_item_classes', function ($classes, $endpoint) {
    if ($endpoint === 'cancellation-requests') {
        $classes[] = 'requests-icon';
    }
    return $classes;
}, 10, 2);

// Make cancellation-requests active when on cancellation-history page
add_filter('woocommerce_account_menu_item_classes', function ($classes, $endpoint) {
    global $wp;

    // Check if we're on the cancellation-history page and this is the cancellation-requests endpoint
    if (isset($wp->query_vars['cancellation-history']) && $endpoint === 'cancellation-requests') {
        // Ensure is-active class is present
        if (!in_array('is-active', $classes)) {
            $classes[] = 'is-active';
        }
    }

    return $classes;
}, 10, 2);

// Override wc_is_current_account_menu_item to return true for cancellation-requests when on cancellation-history page
add_filter('woocommerce_account_menu_item_classes', function ($classes, $endpoint) {
    global $wp;

    // Check if we're on the cancellation-history page and this is the cancellation-requests endpoint
    if (isset($wp->query_vars['cancellation-history']) && $endpoint === 'cancellation-requests') {
        // Force the is-active class to make it appear as the current page
        $classes = array_filter($classes, function ($class) {
            return $class !== 'is-active';
        });
        $classes[] = 'is-active';
    }

    return $classes;
}, 5, 2);

// Override wc_is_current_account_menu_item function to return true for cancellation-requests when on cancellation-history page
add_filter('woocommerce_account_menu_item_classes', function ($classes, $endpoint) {
    global $wp;

    // Check if we're on the cancellation-history page and this is the cancellation-requests endpoint
    if (isset($wp->query_vars['cancellation-history']) && $endpoint === 'cancellation-requests') {
        // This will make wc_is_current_account_menu_item return true
        $classes[] = 'is-active';
    }

    return $classes;
}, 15, 2);

// Override wc_is_current_account_menu_item function to return true for cancellation-requests when on cancellation-history page
add_filter('woocommerce_account_menu_item_classes', function ($classes, $endpoint) {
    global $wp;

    // Check if we're on the cancellation-history page and this is the cancellation-requests endpoint
    if (isset($wp->query_vars['cancellation-history']) && $endpoint === 'cancellation-requests') {
        // Force the is-active class to make it appear as the current page
        $classes = array_filter($classes, function ($class) {
            return $class !== 'is-active';
        });
        $classes[] = 'is-active';
    }

    return $classes;
}, 20, 2);

// Override wc_is_current_account_menu_item function to return true for cancellation-requests when on cancellation-history page
add_filter('woocommerce_account_menu_item_classes', function ($classes, $endpoint) {
    global $wp;

    // Check if we're on the cancellation-history page and this is the cancellation-requests endpoint
    if (isset($wp->query_vars['cancellation-history']) && $endpoint === 'cancellation-requests') {
        // This will make the navigation template think this is the current page
        $classes[] = 'is-active';
    }

    return $classes;
}, 25, 2);

// Override wc_is_current_account_menu_item function to return true for cancellation-requests when on cancellation-history page
add_filter('woocommerce_account_menu_item_classes', function ($classes, $endpoint) {
    global $wp;

    // Check if we're on the cancellation-history page and this is the cancellation-requests endpoint
    if (isset($wp->query_vars['cancellation-history']) && $endpoint === 'cancellation-requests') {
        // This will make the navigation template think this is the current page
        $classes[] = 'is-active';
    }

    return $classes;
}, 30, 2);

// Override wc_is_current_account_menu_item function to return true for cancellation-requests when on cancellation-history page
add_filter('woocommerce_account_menu_item_classes', function ($classes, $endpoint) {
    global $wp;

    // Check if we're on the cancellation-history page and this is the cancellation-requests endpoint
    if (isset($wp->query_vars['cancellation-history']) && $endpoint === 'cancellation-requests') {
        // Force the is-active class to make it appear as the current page
        $classes = array_filter($classes, function ($class) {
            return $class !== 'is-active';
        });
        $classes[] = 'is-active';
    }

    return $classes;
}, 35, 2);

// Override wc_is_current_account_menu_item function to return true for cancellation-requests when on cancellation-history page
add_filter('woocommerce_account_menu_item_classes', function ($classes, $endpoint) {
    global $wp;

    // Check if we're on the cancellation-history page and this is the cancellation-requests endpoint
    if (isset($wp->query_vars['cancellation-history']) && $endpoint === 'cancellation-requests') {
        // Force the is-active class to make it appear as the current page
        $classes = array_filter($classes, function ($class) {
            return $class !== 'is-active';
        });
        $classes[] = 'is-active';
    }

    return $classes;
}, 40, 2);

// Override wc_is_current_account_menu_item function to return true for cancellation-requests when on cancellation-history page
add_filter('woocommerce_account_menu_item_classes', function ($classes, $endpoint) {
    global $wp;

    // Check if we're on the cancellation-history page and this is the cancellation-requests endpoint
    if (isset($wp->query_vars['cancellation-history']) && $endpoint === 'cancellation-requests') {
        // This will make the navigation template think this is the current page
        $classes[] = 'is-active';
    }

    return $classes;
}, 45, 2);

// Override wc_is_current_account_menu_item function to return true for cancellation-requests when on cancellation-history page
add_filter('woocommerce_account_menu_item_classes', function ($classes, $endpoint) {
    global $wp;

    // Check if we're on the cancellation-history page and this is the cancellation-requests endpoint
    if (isset($wp->query_vars['cancellation-history']) && $endpoint === 'cancellation-requests') {
        // Force the is-active class to make it appear as the current page
        $classes = array_filter($classes, function ($class) {
            return $class !== 'is-active';
        });
        $classes[] = 'is-active';
    }

    return $classes;
}, 50, 2);

// Clean solution: Override wc_is_current_account_menu_item to return true for cancellation-requests when on cancellation-history page
add_filter('woocommerce_account_menu_item_classes', function ($classes, $endpoint) {
    global $wp;

    // Check if we're on the cancellation-history page and this is the cancellation-requests endpoint
    if (isset($wp->query_vars['cancellation-history']) && $endpoint === 'cancellation-requests') {
        // Force the is-active class to make it appear as the current page
        $classes = array_filter($classes, function ($class) {
            return $class !== 'is-active';
        });
        $classes[] = 'is-active';
    }

    return $classes;
}, 100, 2);

/**
 * Registers and enqueues Persian Datepicker scripts and styles on WordPress frontend.
 *
 * This function adds actions to enqueue necessary scripts and styles for the Persian Datepicker
 * on the 'wp_enqueue_scripts' hook. It registers and enqueues the required CSS and JS files
 * for the Persian Datepicker plugin, including dependencies.
 *
 * @return void
 */

/**
 * Adds a custom rewrite rule for the 'profile' endpoint.
 *
 * This function adds a rewrite rule that maps the URL pattern 'profile/([a-z0-9-]+)' to the query parameter 'profile'.
 * When the URL matches this pattern, it will be redirected to 'index.php?profile=$matches[1]'.
 *
 * @param string $pattern The regex pattern to match in the URL.
 * @param string $query The query string to redirect to.
 * @param string $position The position of the rule in the rewrite rules list.
 */
add_action('init', function () {
    add_rewrite_rule('profile/([a-z0-9-]+)[/]?$', 'index.php?profile=$matches[1]', 'top');
    add_rewrite_rule('r/([^/]*)[/]?$', 'index.php?reserve=$matches[1]', 'top');
    add_rewrite_rule('t/([^/]*)[/]?$', 'index.php?ticket=$matches[1]', 'top');
});

/**
 * Adds a new query variable 'profile' to the list of query variables.
 *
 * @param array $query_vars The array of existing query variables.
 *
 * @return array The updated array of query variables with 'profile' added.
 */
add_filter('query_vars', function ($query_vars) {
    $query_vars[] = 'profile';
    $query_vars[] = 'reserve';
    $query_vars[] = 'ticket';

    return $query_vars;
});

/**
 * Filters the path of the template file to include.
 *
 * This function checks the WordPress query variables and modifies the template file path accordingly.
 *
 * @param string $template The path of the template file to include.
 *
 * @return string The modified path of the template file to include.
 */
add_filter('template_include', function ($template) {

    global $wp;

    if (! empty($wp->query_vars)) {

        if (
            $wp->query_vars['pagename'] == 'profile' ||
            $wp->query_vars['pagename'] == 'reserve' ||
            $wp->query_vars['pagename'] == 'ticket'
        ) {
            return get_template_directory() . '/404.php';
        } elseif (isset($wp->query_vars['profile'])) {
            return get_template_directory() . '/profile.php';
        } elseif (isset($wp->query_vars['reserve'])) {
            return get_template_directory() . '/reserve.php';
        } elseif (isset($wp->query_vars['ticket'])) {
            return get_template_directory() . '/ticket.php';
        }
    }

    return $template;
});

add_filter('get_avatar', function ($avatar, $id_or_email, $size, $default, $alt) {
    $custom_avatar = get_user_meta($id_or_email, 'user_avatar', true);

    if ($custom_avatar == '' || $custom_avatar == 'default-avatar.svg') {
        $url = get_bloginfo('template_url') . '/assets/images/default-avatar.png';
    } else {
        $url = get_bloginfo('template_url') . '/assets/images/avatars/' . $custom_avatar;
    }

    $avatar = preg_replace_callback(
        '/src=[\'"]([^\'"]+)[\'"]/',
        function ($matches) use ($url) {
            return 'src="' . $url . '"';
        },
        $avatar,
    );

    $avatar = preg_replace_callback(
        '/srcset=[\'"]([^\'"]+)[\'"]/',
        function ($matches) use ($url) {
            return 'srcset="' . $url . '"';
        },
        $avatar,
    );

    return $avatar;
}, 10, 5);

/**
 * Disable all woocommerce styles
 */
add_filter('woocommerce_enqueue_styles', '__return_empty_array');

/**
 * Check if current user has specific roles.
 *
 * @param ...$roles
 *
 * @return bool
 */
function has_role(...$roles): bool
{
    $user = wp_get_current_user();
    if (empty($user->roles)) {
        return false;
    }
    return in_array($user->roles[0], $roles);
}

function is_wc_login_page(): bool
{
    return (is_account_page() && ! is_user_logged_in());
}

add_action('init', function () {
    if (isset($_GET['add-to-cart'])) {
        WC()->cart->empty_cart();

        if (! is_user_logged_in()) {
            wp_redirect(home_url(''));
        }
    }
});

remove_action('woocommerce_before_checkout_form', 'woocommerce_output_all_notices', 10);

// soi_output_buffer
add_action('init', function () {
    ob_start();

    if (! session_id()) {
        session_start();
    }
});


//// Disable Woocommerce Checkout fields
add_filter('woocommerce_checkout_fields', function ($fields) {
    // Remove billing fields
    unset($fields['billing']['billing_company']);
    unset($fields['billing']['billing_postcode']);
    unset($fields['billing']['billing_country']);
    unset($fields['billing']['billing_state']);
    unset($fields['billing']['billing_city']);
    unset($fields['billing']['billing_address_1']);
    unset($fields['billing']['billing_address_2']);
    unset($fields['billing']['billing_email']);

    // Remove shipping fields
    unset($fields['shipping']['shipping_company']);
    unset($fields['shipping']['shipping_postcode']);

    return $fields;
});

add_filter('woocommerce_checkout_fields', function ($fields) {
    if (isset($_GET['quantity'])) {
        for ($i = 2; $i <= (int) $_GET['quantity']; $i++) {
            $fields['billing']['players_name_' . $i]  = [
                'label'       => 'نام و نام خانوادگی',
                'placeholder' => 'نام و نام خانوادگی',
                'required'    => false,
                'class'       => ['form-row-wide'],
                'clear'       => true,
            ];
            $fields['billing']['players_phone_' . $i] = [
                'label'       => 'تلفن همراه',
                'placeholder' => 'تلفن همراه',
                'required'    => false,
                'class'       => ['form-row-wide'],
                'clear'       => true,
            ];
        }
    }

    if (isset($_GET['book'])) {
        $fields['billing']['book'] = [
            'label'       => 'تایم رزرو',
            'placeholder' => 'تایم رزرو',
            'required'    => false,
            'class'       => ['form-row-wide'],
            'clear'       => true,
            'default'     => $_GET['book'],
        ];
    }

    return $fields;
});

add_action('woocommerce_checkout_update_order_meta', function ($order_id) {
    $order = wc_get_order($order_id);

    $items = $order->get_items();
    $item  = reset($items);

    $players = [];
    for ($i = 2; $i <= (int) $item->get_quantity(); $i++) {
        if (! empty($_POST['players_name_' . $i]) && ! empty($_POST['players_phone_' . $i])) {
            $players[] = [
                'name'  => $_POST['players_name_' . $i],
                'phone' => $_POST['players_phone_' . $i],
            ];
        }
    }

    if (isset($_POST['book'])) {
        $order->update_meta_data('sans_time', $_POST['book']);
    }

    $order->update_meta_data('players_phone', $players);

    $order->save_meta_data();
}, 10, 2);

add_action('add_meta_boxes', function () {
    add_meta_box(
        'custom_other_field',
        'تلفن افراد حاضر',
        function ($post) {
            $order   = wc_get_order($post->ID); // Get the WC_Order object
            $players = get_post_meta($order->get_id(), 'players_phone', true);
?>

        <?php if (! empty($players)) { ?>
            <ul>
                <?php foreach ($players as $player) { ?>
                    <li>
                        <a href="tel:<?php echo $player['phone'] ?>"><?php echo $player['name'] ?></a>
                    </li>
                <?php } ?>
            </ul>
        <?php } ?>
    <?php },
        'shop_order',
        'normal',
        'core',
    );
});

add_theme_support('woocommerce');

function get_replies($items): void
{
    foreach ($items as $comment) { ?>
        <div class="comment" id="comment-<?php echo $comment['comment_id']; ?>">
            <p class="text-20 font-bold mb-5 mt-8 text-black flex items-center gap-8">

                <?php if ($comment['parent'] != 0) { ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="14" viewBox="0 0 16 14" fill="none">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M5.70679 13.7072C5.89426 13.5197 5.99957 13.2654 5.99957 13.0002C5.99957 12.735 5.89426 12.4807 5.70679 12.2932L3.41379 10.0002H8.99979C10.8563 10.0002 12.6368 9.26272 13.9495 7.94996C15.2623 6.63721 15.9998 4.85673 15.9998 3.00021V1.00021C15.9998 0.734997 15.8944 0.480642 15.7069 0.293106C15.5194 0.10557 15.265 0.000213623 14.9998 0.000213623C14.7346 0.000213623 14.4802 0.10557 14.2927 0.293106C14.1051 0.480642 13.9998 0.734997 13.9998 1.00021V3.00021C13.9998 4.3263 13.473 5.59807 12.5353 6.53575C11.5976 7.47343 10.3259 8.00021 8.99979 8.00021H3.41379L5.70679 5.70721C5.8023 5.61497 5.87848 5.50462 5.93089 5.38262C5.9833 5.26061 6.01088 5.12939 6.01204 4.99661C6.01319 4.86384 5.98789 4.73216 5.93761 4.60926C5.88733 4.48636 5.81307 4.37471 5.71918 4.28082C5.62529 4.18693 5.51364 4.11267 5.39074 4.06239C5.26784 4.01211 5.13616 3.98681 5.00339 3.98796C4.87061 3.98912 4.73939 4.0167 4.61738 4.06911C4.49538 4.12152 4.38503 4.1977 4.29279 4.29321L0.292786 8.29321C0.105315 8.48074 0 8.73505 0 9.00021C0 9.26538 0.105315 9.51969 0.292786 9.70721L4.29279 13.7072C4.48031 13.8947 4.73462 14 4.99979 14C5.26495 14 5.51926 13.8947 5.70679 13.7072Z" fill="#889BAD" />
                    </svg>
                <?php } ?>

                <?php echo esc_html($comment['author_title']) ?>

            </p>
            <div class="comment-content text-16 pr-10 pb-6 border-r flex flex-col text-gray-500">
                <?php echo esc_html($comment['content']) ?>
                <button type="button" data-name="<?php echo esc_html($comment['author_title']) ?>" data-id="<?php echo $comment['comment_id']; ?>" class="reply-comment w-fit mt-4 text-black text-14" style="text-decoration: underline">
                    پاسخ دهید
                </button>
                <?php get_replies($comment['replies']); ?>
            </div>
        </div>
    <?php }
}

add_shortcode('esadv', function ($atts) {
    $value = shortcode_atts([
        'id'   => null,
        'desc' => null,
    ], $atts);

    $post = get_post(intval($value['id']));

    ob_start(); ?>

    <div class="flex items-center gap-2 lg:gap-8 border-t border-b py-2 lg:py-8 my-2 lg:my-8">

        <div class="w-24 lg:w-49">
            <a href="<?php echo get_the_permalink($post->ID); ?>">
                <?php echo get_the_post_thumbnail($post->ID, 'full', [
                    'class' => 'w-auto h-full object-cover rounded-xl',
                ]); ?>
            </a>
        </div>

        <div class="grow flex flex-col max-lg:items-center gap-1 lg:gap-4">
            <a href="<?php echo get_the_permalink($post->ID); ?>" class="font-extrabold lg:text-2xl"><?php echo get_the_title($post->ID); ?></a>
            <div class="leading-5"><?php echo substr($value['desc'], 125) ?? get_the_excerpt($post->ID) ?></div>
            <a href="<?php echo get_the_permalink($post->ID); ?>" class="lg:bg-accent-450 px-3 py-1 lg:px-4 lg:py-2 lg:shadow-23 lg:rounded-2xl max-lg:rounded-md lg:text-white w-fit mr-auto max-lg:text-green-600 max-lg:border max-lg:border-accent-450 max-lg:text-sm max-lg:font-extrabold">
                مشاهده بیشتر
            </a>
        </div>

    </div>

<?php
    return ob_get_clean();
});

foreach (glob(__DIR__ . '/acf/*.php') as $file) {
    require $file;
}

add_filter('woocommerce_order_is_paid_statuses', function ($statuses) {
    $statuses[] = 'held';
    $statuses[] = 'walletx';

    return $statuses;
});

foreach (glob(__DIR__ . '/shortcodes/*.php') as $file) {
    require_once($file);
}

function GetYoastTitle()
{
    if (! is_singular()) {
        return '';
    }

    // از تابع Yoast برای گرفتن عنوان سئو استفاده می‌کنیم
    if (defined('WPSEO_VERSION')) {
        $yoast_title = WPSEO_Frontend::get_instance()->title('');

        return $yoast_title;
    }

    // fallback به title پیشفرض
    return get_the_title();
}

add_action('wp_head', function () {

    if (! is_product()) {
        return;
    }

    global $product;

    $product_id = get_the_ID();

    $comments_count = (int)get_post_meta($product_id, 'comments_count_new', true);

    $comments_count_meta = get_comments([
        'post_id' => $product_id,
        'status'  => 'approve',
        'parent'  => 0,
    ]);

    $comments_count_meta_number = count($comments_count_meta);

    $terms = get_the_terms($product_id, 'product_cat');
    if ($terms && !is_wp_error($terms) && count($terms) > 1) {
        foreach ($terms as $term) {
            if ($term->parent == 0) {
                $product_type = $term->name;
            }
        }
    } elseif ($terms && !is_wp_error($terms) && count($terms) == 1) {
        $product_type = get_term($terms[0]->parent)->name;
    } else {
        $product_type = 'نامشخص';
    }

    $axes_avg    = ez_product_rating_resolve_axis_averages_for_display($product_id);
    $final_score = ez_product_rating_overall_from_axes($axes_avg, $product_type);

    $PublishDate = get_the_date('Y-m-d', $product_id);

    $ProductURL  = get_permalink($product_id);

?>

    <script type="application/ld+json">
        {
            "@context": "https://schema.org/",
            "@type": "Product",
            "name": "<?= GetYoastTitle(); ?>",
            "url": "<?php echo esc_url($ProductURL); ?>",
            "releaseDate": "<?php echo esc_js($PublishDate); ?>",
            <?php if (($final_score) > 1) { ?> "aggregateRating": {
                    "@type": "AggregateRating",
                    "ratingValue": "<?= number_format(round($final_score, 2), 2, '.', ''); ?>",
                    "reviewCount": "<?= $comments_count_meta_number; ?>"
                }
            <?php } ?>
        }
    </script>
<?php });

add_action('init', function () {
    $url = parse_url(home_url());
    $url = $url['scheme'] . "://" . $url['host'] . add_query_arg(null, null);
    $url = strtok($url, '?');

    if (
        isset($_GET["comment_page"]) ||
        isset($_GET["e"]) ||
        isset($_GET["_g"])
    ) {
        wp_redirect($url);
    }
});

function change_schema_date_published($data)
{
    global $post;

    $data['datePublished'] = date(DATE_ATOM, strtotime($post->post_date));

    return $data;
}
add_filter('wpseo_schema_article', 'change_schema_date_published');
add_filter('wpseo_schema_webpage', 'change_schema_date_published');

add_action('init', function () {
    register_post_type('notification', [
        'label'                 => 'اعلان',
        'description'           => 'اعلان‌های سایت',
        'labels'                => [
            'name'                  => 'اعلان‌ها',
            'singular_name'         => 'اعلان',
            'menu_name'             => 'اعلان‌ها',
            'name_admin_bar'        => 'اعلان',
            'all_items'             => 'تمام اعلان‌ها',
            'add_new'               => 'افزودن جدید',
            'add_new_item'          => 'افزودن اعلان جدید',
            'edit_item'             => 'ویرایش اعلان',
            'new_item'              => 'اعلان جدید',
            'view_item'             => 'مشاهده اعلان',
            'search_items'          => 'جست‌وجوی اعلان',
            'not_found'             => 'اعلان یافت نشد',
            'not_found_in_trash'    => 'اعلانی در زباله‌دان نیست',
        ],
        'supports'              => ['title'],
        'public'                => true,
        'show_ui'               => true,
        'menu_icon'             => 'dashicons-megaphone',
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'exclude_from_search'   => true,
        'publicly_queryable'    => true,
        'has_archive'           => false,
        'rewrite'               => false,
        'capability_type'       => 'page',
        'show_in_rest'          => false,
    ]);
});

add_action('save_post_notification', function ($post_id, $post) {
    global $wpdb;

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $title      = sanitize_text_field($post->post_title);
    $content    = get_field('notification-content', $post_id);
    $method     = get_field('notification-send-method', $post_id);

    $users = [];

    if ($method === 'users')
        $users = array_map('intval', (array)get_field('method-users', $post_id));

    elseif ($method === 'roles') {
        $roles = (array)get_field('method-roles', $post_id);

        foreach ($roles as $role) {
            $role_users = get_users(['role' => sanitize_text_field($role), 'fields' => 'ID']);
            $users = array_merge($users, $role_users);
        }
    }

    $users = array_unique($users);

    $data = [
        'notification_id'   => $post_id,
        'title'             => $title,
        'content'           => $content,
        'users'             => maybe_serialize($users),
    ];

    $exists = (int)$wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM notifications WHERE notification_id = %d', $post_id));

    if ($exists > 0)
        $wpdb->update('notifications', $data, ['notification_id' => $post_id]);
    else
        $wpdb->insert('notifications', $data);

}, 10, 2);

add_action('before_delete_post', function ($post_id) {
    global $wpdb;

    $post_type = get_post_type($post_id);
    if ($post_type !== 'notification')
        return;

    $wpdb->query($wpdb->prepare('DELETE FROM notifications WHERE notification_id = %d', $post_id));
});

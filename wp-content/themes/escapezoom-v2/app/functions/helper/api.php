<?php

//**********************************************************************************************************/
function get_orders_ids_by_product_id( $products_id, $order_status, $date_range=false, $limit=10000000, $page=1, $total=false ) {
    global $wpdb;

    $query = "";

    if ( $date_range && !empty( $date_range ) ) {
        $start_date = date("Y-m-d", (int)$date_range[0]);
        $end_date   = date("Y-m-d", (int)$date_range[1]);

        $query = "AND posts.post_date BETWEEN '$start_date' AND '$end_date'";
    }

    $query .= "AND (";
    $i = 0;
    foreach ( $products_id as $product_id ) {
        if ( !$i )
            $query .= "order_item_meta.meta_value = $product_id ";
        else
            $query .= "OR order_item_meta.meta_value = $product_id ";

        $i++;
    }
    $query .= ")";

    $offset = ($page - 1) * $limit;

    if ( $total ) {
        $results = $wpdb->get_col("
        SELECT SUM(total) AS total FROM (
            SELECT COUNT(*) AS total  
            FROM {$wpdb->prefix}woocommerce_order_items AS order_items
            LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
            LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
            WHERE posts.post_type = 'shop_order'
            AND posts.post_status IN ( '" . implode( "','", $order_status ) . "' )
            AND order_items.order_item_type = 'line_item'
            AND order_item_meta.meta_key = '_product_id'
            $query
            GROUP BY posts.post_date DESC
        ) AS total;
    ");

    } else {

        $results = $wpdb->get_col("
            SELECT order_items.order_id
            FROM {$wpdb->prefix}woocommerce_order_items AS order_items
            LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
            LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
            WHERE posts.post_type = 'shop_order'
            AND posts.post_status IN ( '" . implode( "','", $order_status ) . "' )
            AND order_items.order_item_type = 'line_item'
            AND order_item_meta.meta_key = '_product_id'
            $query
            GROUP BY posts.post_date DESC
            LIMIT $limit
            OFFSET $offset;
        ");
    }

    return $results;
}
//**********************************************************************************************************/
function get_owner_id_by_product_id ($product_id) {
    return get_post_meta($product_id, 'user_ebtal', true);
}
//**********************************************************************************************************/
function add_new_point ($new_point) {
    global $wpdb;

    $new_point['created_at'] = time();
    $res = $wpdb->insert( 'points', $new_point);
}
//**********************************************************************************************************/
function ticket_verify($ticket_id, $user_id) {

    if ( get_post($ticket_id)->post_author == $user_id )
        return true;

    return false;
}
//**********************************************************************************************************/
function get_ticket_status ($ticket_id) {

    if ( get_post_meta($ticket_id, 'ticket_closed', true) )
        $status = 'closed';
    else
        if ( get_post_meta($ticket_id, 'respond_user_role', true) == "admin" )
            $status = 'respond';

        elseif ( get_post_meta($ticket_id, 'respond_user_role', true) == "user" && get_post_meta($ticket_id, 'admin_seen', true) )
            $status = 'pending';

        else
            $status = 'open';

    return $status;
}
//**********************************************************************************************************/
function ez_validate_mobile($mobile) {

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
/**********************************************************************************************************/
function ez_verify_user_by($username, $verify_param, $by) {

    $user = get_user_by('login', $username);

    if ( $user ) {

        if ( $by == 'verify_code' ) {

            $otp = get_user_meta($user->ID, 'otp', true);
            if ( $otp != $verify_param )
                throw new Exception('invalid_code');

            delete_user_meta($user->ID, 'otp_send_time');
            delete_user_meta($user->ID, 'otp');

        } elseif ( $by == 'password' ) {

            if ( !wp_check_password($verify_param, $user->user_pass, $user->ID) )
                throw new Exception('invalid_password');
        }

    } else {
        throw new Exception('user_not_found');
    }

    return $user;
}
/**********************************************************************************************************/
function ez_get_domain() {

    $url = get_site_url();
    $find = array('http://', 'https://');
    $replace = '';

    return str_replace($find, $replace, $url);
}
//**********************************************************************************************************/
function get_user_points($user_id) {
    global $wpdb;

    return $wpdb->get_results( $wpdb->prepare( "SELECT SUM(point) as total FROM points WHERE user_id LIKE {$user_id}" ) )[0]->total;
}
//**********************************************************************************************************/
function get_post_reply_comments ($comment_id) {

    $comments_list = get_comments(array(
        'parent'    => $comment_id,
        'status'    => 'approve',
        'type'      => 'comment',
        'orderby'   => 'comment_date',
        'order'     => 'DESC'
    ));

    if (!empty($comments_list)) {
        foreach ($comments_list as $comment) {

            $replies = get_post_reply_comments($comment->comment_ID);

            $replies[] = [
                'comment_id'    => (int)$comment->comment_ID,
                'parent'        => $comment->comment_parent,
                'author_title'  => get_user_by( 'id', $comment->user_id )->data->display_name ? : $comment->comment_author,
                'author_image'  => get_user_meta($comment->user_id, 'user_avatar', true) ? : 'http://escapezoom.ir/wp-content/uploads/2024/04/male_avatar_level_1.png',
                'author_level'  => get_user_meta($comment->user_id, 'level', true) ? : 1,
                'content'       => $comment->comment_content,
                'date'          => strtotime($comment->comment_date),
                'replies'       => $replies
            ];
        }
    }

    return $replies;
}
//**********************************************************************************************************/
/**
 * Main checkout class.
 *
 * The WooCommerce checkout class handles the checkout process, collecting user data and processing the payment.
 *
 * @class    WC_Checkout
 * @package  WooCommerce/Classes
 * @category Class
 * @author   WooThemes
 */
class Escapezoom_Checkout
{

    /**
     * The single instance of the class.
     *
     * @var Tavalode_Checkout|null
     */
    protected static $instance = null;

    /**
     * Checkout fields are stored here.
     *
     * @var array|null
     */
    protected $fields = null;

    /**
     * Gets the main Tavalode_Checkout Instance.
     *
     * @return Tavalode_Checkout Main instance
     * @since 2.1
     * @static
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();

            // Hook in actions once.
            add_action('woocommerce_checkout_billing', array(self::$instance, 'checkout_form_billing'));
            add_action('woocommerce_checkout_shipping', array(self::$instance, 'checkout_form_shipping'));

            // woocommerce_checkout_init action is ran once when the class is first constructed.
            do_action('woocommerce_checkout_init', self::$instance);
        }
        return self::$instance;
    }

    /**
     * See if variable is set. Used to support legacy public variables which are no longer defined.
     *
     * @param string $key
     * @return bool
     */
    public function __isset($key)
    {
        return in_array($key, array(
            'enable_signup',
            'enable_guest_checkout',
            'must_create_account',
            'checkout_fields',
            'posted',
            'shipping_method',
            'payment_method',
            'customer_id',
            'shipping_methods',
        ));
    }

    /**
     * Sets the legacy public variables for backwards compatibility.
     *
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        switch ($key) {
            case 'enable_signup' :
                $bool_value = wc_string_to_bool($value);

                if ($bool_value !== $this->is_registration_enabled()) {
                    remove_filter('woocommerce_checkout_registration_enabled', '__return_true', 0);
                    remove_filter('woocommerce_checkout_registration_enabled', '__return_false', 0);
                    add_filter('woocommerce_checkout_registration_enabled', $bool_value ? '__return_true' : '__return_false', 0);
                }
                break;
            case 'enable_guest_checkout' :
                $bool_value = wc_string_to_bool($value);

                if ($bool_value !== $this->is_registration_required()) {
                    remove_filter('woocommerce_checkout_registration_required', '__return_true', 0);
                    remove_filter('woocommerce_checkout_registration_required', '__return_false', 0);
                    add_filter('woocommerce_checkout_registration_required', $bool_value ? '__return_false' : '__return_true', 0);
                }
                break;
            case 'checkout_fields' :
                $this->fields = $value;
                break;
            case 'shipping_methods' :
                WC()->session->set('chosen_shipping_methods', $value);
                break;
            case 'legacy_posted_data' :
                $this->legacy_posted_data = $value;
                break;
        }
    }

    /**
     * Gets the legacy public variables for backwards compatibility.
     *
     * @param string $key
     * @return string
     */
    public function __get($key)
    {
        if (in_array($key, array('posted', 'shipping_method', 'payment_method')) && !$this->legacy_posted_data) {
            $this->legacy_posted_data = $this->get_posted_data();
        }
        switch ($key) {
            case 'enable_signup' :
                return $this->is_registration_enabled();
            case 'enable_guest_checkout' :
                return !$this->is_registration_required();
            case 'must_create_account' :
                return $this->is_registration_required() && !is_user_logged_in();
            case 'checkout_fields' :
                return $this->get_checkout_fields();
            case 'posted' :
                return $this->legacy_posted_data;
            case 'shipping_method' :
                return $this->legacy_posted_data['shipping_method'];
            case 'payment_method' :
                return $this->legacy_posted_data['payment_method'];
            case 'customer_id' :
                return apply_filters('woocommerce_checkout_customer_id', get_current_user_id());
            case 'shipping_methods' :
                return (array)WC()->session->get('chosen_shipping_methods');
        }
    }

    /**
     * Cloning is forbidden.
     */
    public function __clone()
    {
        wc_doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'woocommerce'), '2.1');
    }

    /**
     * Unserializing instances of this class is forbidden.
     */
    public function __wakeup()
    {
        wc_doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'woocommerce'), '2.1');
    }

    /**
     * Is registration required to checkout?
     *
     * @return boolean
     * @since  3.0.0
     */
    public function is_registration_required()
    {
        return apply_filters('woocommerce_checkout_registration_required', 'yes' !== get_option('woocommerce_enable_guest_checkout'));
    }

    /**
     * Is registration enabled on the checkout page?
     *
     * @return boolean
     * @since  3.0.0
     */
    public function is_registration_enabled()
    {
        return apply_filters('woocommerce_checkout_registration_enabled', 'yes' === get_option('woocommerce_enable_signup_and_login_from_checkout'));
    }

    /**
     * Get an array of checkout fields.
     *
     * @param string $fieldset to get.
     * @return array
     */
    public function get_checkout_fields($fieldset = '')
    {
        if (is_null($this->fields)) {
            $this->fields = array(
                'billing' => WC()->countries->get_address_fields($this->get_value('billing_country'), 'billing_'),
                'shipping' => WC()->countries->get_address_fields($this->get_value('shipping_country'), 'shipping_'),
                'account' => array(),
                'order' => array(
                    'order_comments' => array(
                        'type' => 'textarea',
                        'class' => array('notes'),
                        'label' => __('Order notes', 'woocommerce'),
                        'placeholder' => esc_attr__('Notes about your order, e.g. special notes for delivery.', 'woocommerce'),
                    ),
                ),
            );
            if ('no' === get_option('woocommerce_registration_generate_username')) {
                $this->fields['account']['account_username'] = array(
                    'type' => 'text',
                    'label' => __('Account username', 'woocommerce'),
                    'required' => true,
                    'placeholder' => esc_attr__('Username', 'woocommerce'),
                );
            }

            if ('no' === get_option('woocommerce_registration_generate_password')) {
                $this->fields['account']['account_password'] = array(
                    'type' => 'password',
                    'label' => __('Account password', 'woocommerce'),
                    'required' => true,
                    'placeholder' => esc_attr__('Password', 'woocommerce'),
                );
            }

            $this->fields = apply_filters('woocommerce_checkout_fields', $this->fields);
        }
        if ($fieldset) {
            return $this->fields[$fieldset];
        } else {
            return $this->fields;
        }
    }

    /**
     * When we process the checkout, lets ensure cart items are rechecked to prevent checkout.
     */
    public function check_cart_items()
    {
        do_action('woocommerce_check_cart_items');
    }

    /**
     * Output the billing form.
     */
    public function checkout_form_billing()
    {
        wc_get_template('checkout/form-billing.php', array('checkout' => $this));
    }

    /**
     * Output the shipping form.
     */
    public function checkout_form_shipping()
    {
        wc_get_template('checkout/form-shipping.php', array('checkout' => $this));
    }

    /**
     * Create an order. Error codes:
     *        520 - Cannot insert order into the database.
     *        521 - Cannot get order after creation.
     *        522 - Cannot update order.
     *        525 - Cannot create line item.
     *        526 - Cannot create fee item.
     *        527 - Cannot create shipping item.
     *        528 - Cannot create tax item.
     *        529 - Cannot create coupon item.
     *
     * @param  $data Posted data.
     * @return int|WP_ERROR
     * @throws Exception
     */
    public function create_order($data)
    {
        // Give plugins the opportunity to create an order themselves.
        if ($order_id = apply_filters('woocommerce_create_order', null, $this)) {
            return $order_id;
        }

        try {
            $order_id = absint(WC()->session->get('order_awaiting_payment'));
            $cart_hash = md5(json_encode(wc_clean(WC()->cart->get_cart_for_session())) . WC()->cart->total);
            $available_gateways = WC()->payment_gateways->get_available_payment_gateways();

            /**
             * If there is an order pending payment, we can resume it here so
             * long as it has not changed. If the order has changed, i.e.
             * different items or cost, create a new order. We use a hash to
             * detect changes which is based on cart items + order total.
             */
            if ($order_id && ($order = wc_get_order($order_id)) && $order->has_cart_hash($cart_hash) && $order->has_status(array('pending', 'failed'))) {
                // Action for 3rd parties.
                do_action('woocommerce_resume_order', $order_id);

                // Remove all items - we will re-add them later.
                $order->remove_order_items();
            } else {
                $order = new WC_Order();
            }

            foreach ($data as $key => $value) {
                if (is_callable(array($order, "set_{$key}"))) {
                    $order->{"set_{$key}"}($value);

                    // Store custom fields prefixed with wither shipping_ or billing_. This is for backwards compatibility with 2.6.x.
                } elseif (0 === stripos($key, 'billing_') || 0 === stripos($key, 'shipping_')) {
                    $order->update_meta_data('_' . $key, $value);
                }
            }

            $order->set_created_via('checkout');
            $order->set_cart_hash($cart_hash);
            $order->set_customer_id(apply_filters('woocommerce_checkout_customer_id', get_current_user_id()));
            $order->set_currency(get_woocommerce_currency());
            $order->set_prices_include_tax('yes' === get_option('woocommerce_prices_include_tax'));
            $order->set_customer_ip_address(WC_Geolocation::get_ip_address());
            $order->set_customer_user_agent(wc_get_user_agent());
            $order->set_customer_note(isset($data['order_comments']) ? $data['order_comments'] : '');
            $order->set_payment_method(isset($available_gateways[$data['payment_method']]) ? $available_gateways[$data['payment_method']] : $data['payment_method']);
            $order->set_shipping_total(WC()->cart->shipping_total);
            $order->set_discount_total(WC()->cart->get_cart_discount_total());
            $order->set_discount_tax(WC()->cart->get_cart_discount_tax_total());
            $order->set_cart_tax(WC()->cart->tax_total);
            $order->set_shipping_tax(WC()->cart->shipping_tax_total);
            $order->set_total(WC()->cart->total);
            $this->create_order_line_items($order, WC()->cart);
            $this->create_order_fee_lines($order, WC()->cart);
            $this->create_order_shipping_lines($order, WC()->session->get('chosen_shipping_methods'), WC()->shipping->get_packages());
            $this->create_order_tax_lines($order, WC()->cart);
            $this->create_order_coupon_lines($order, WC()->cart);

            /**
             * Action hook to adjust order before save.
             * @since 3.0.0
             */
            do_action('woocommerce_checkout_create_order', $order, $data);

            // Save the order.
            $order_id = $order->save();

            do_action('woocommerce_checkout_update_order_meta', $order_id, $data);

            return $order_id;
        } catch (Exception $e) {
            return new WP_Error('checkout-error', $e->getMessage());
        }
    }

    /**
     * Add line items to the order.
     *
     * @param WC_Order $order
     */
    public function create_order_line_items(&$order, $cart)
    {
        foreach ($cart->get_cart() as $cart_item_key => $values) {
            $product = $values['data'];
            $item = new WC_Order_Item_Product();
            $item->legacy_values = $values; // @deprecated For legacy actions.
            $item->legacy_cart_item_key = $cart_item_key; // @deprecated For legacy actions.
            $item->set_props(array(
                'quantity' => $values['quantity'],
                'variation' => $values['variation'],
                'subtotal' => $values['line_subtotal'],
                'total' => $values['line_total'],
                'subtotal_tax' => $values['line_subtotal_tax'],
                'total_tax' => $values['line_tax'],
                'taxes' => $values['line_tax_data'],
            ));
            if ($product) {
                $item->set_props(array(
                    'name' => $product->get_name(),
                    'tax_class' => $product->get_tax_class(),
                    'product_id' => $product->is_type('variation') ? $product->get_parent_id() : $product->get_id(),
                    'variation_id' => $product->is_type('variation') ? $product->get_id() : 0,
                ));
            }
            $item->set_backorder_meta();

            /**
             * Action hook to adjust item before save.
             * @since 3.0.0
             */
            do_action('woocommerce_checkout_create_order_line_item', $item, $cart_item_key, $values, $order);

            // Add item to order and save.
            $order->add_item($item);
        }
    }

    /**
     * Add fees to the order.
     *
     * @param WC_Order $order
     */
    public function create_order_fee_lines(&$order, $cart)
    {
        foreach ($cart->get_fees() as $fee_key => $fee) {
            $item = new WC_Order_Item_Fee();
            $item->legacy_fee = $fee; // @deprecated For legacy actions.
            $item->legacy_fee_key = $fee_key; // @deprecated For legacy actions.
            $item->set_props(array(
                'name' => $fee->name,
                'tax_class' => $fee->taxable ? $fee->tax_class : 0,
                'total' => $fee->amount,
                'total_tax' => $fee->tax,
                'taxes' => array(
                    'total' => $fee->tax_data,
                ),
            ));

            /**
             * Action hook to adjust item before save.
             * @since 3.0.0
             */
            do_action('woocommerce_checkout_create_order_fee_item', $item, $fee_key, $fee, $order);

            // Add item to order and save.
            $order->add_item($item);
        }
    }

    /**
     * Add shipping lines to the order.
     *
     * @param WC_Order $order
     */
    public function create_order_shipping_lines(&$order, $chosen_shipping_methods, $packages)
    {
        foreach ($packages as $package_key => $package) {
            if (isset($chosen_shipping_methods[$package_key], $package['rates'][$chosen_shipping_methods[$package_key]])) {
                /** @var WC_Shipping_Rate $shipping_rate */
                $shipping_rate = $package['rates'][$chosen_shipping_methods[$package_key]];
                $item = new WC_Order_Item_Shipping();
                $item->legacy_package_key = $package_key; // @deprecated For legacy actions.
                $item->set_props(array(
                    'method_title' => $shipping_rate->label,
                    'method_id' => $shipping_rate->id,
                    'total' => wc_format_decimal($shipping_rate->cost),
                    'taxes' => array(
                        'total' => $shipping_rate->taxes,
                    ),
                ));

                foreach ($shipping_rate->get_meta_data() as $key => $value) {
                    $item->add_meta_data($key, $value, true);
                }

                /**
                 * Action hook to adjust item before save.
                 * @since 3.0.0
                 */
                do_action('woocommerce_checkout_create_order_shipping_item', $item, $package_key, $package, $order);

                // Add item to order and save.
                $order->add_item($item);
            }
        }
    }

    /**
     * Add tax lines to the order.
     *
     * @param WC_Order $order
     */
    public function create_order_tax_lines(&$order, $cart)
    {
        foreach (array_keys($cart->taxes + $cart->shipping_taxes) as $tax_rate_id) {
            if ($tax_rate_id && apply_filters('woocommerce_cart_remove_taxes_zero_rate_id', 'zero-rated') !== $tax_rate_id) {
                $item = new WC_Order_Item_Tax();
                $item->set_props(array(
                    'rate_id' => $tax_rate_id,
                    'tax_total' => $cart->get_tax_amount($tax_rate_id),
                    'shipping_tax_total' => $cart->get_shipping_tax_amount($tax_rate_id),
                    'rate_code' => WC_Tax::get_rate_code($tax_rate_id),
                    'label' => WC_Tax::get_rate_label($tax_rate_id),
                    'compound' => WC_Tax::is_compound($tax_rate_id),
                ));

                /**
                 * Action hook to adjust item before save.
                 * @since 3.0.0
                 */
                do_action('woocommerce_checkout_create_order_tax_item', $item, $tax_rate_id, $order);

                // Add item to order and save.
                $order->add_item($item);
            }
        }
    }

    /**
     * Add coupon lines to the order.
     *
     * @param WC_Order $order
     */
    public function create_order_coupon_lines(&$order, $cart)
    {
        foreach ($cart->get_coupons() as $code => $coupon) {
            $item = new WC_Order_Item_Coupon();
            $item->set_props(array(
                'code' => $code,
                'discount' => $cart->get_coupon_discount_amount($code),
                'discount_tax' => $cart->get_coupon_discount_tax_amount($code),
            ));

            /**
             * Action hook to adjust item before save.
             * @since 3.0.0
             */
            do_action('woocommerce_checkout_create_order_coupon_item', $item, $code, $coupon, $order);

            // Add item to order and save.
            $order->add_item($item);
        }
    }

    /**
     * See if a fieldset should be skipped.
     *
     * @param string $fieldset_key
     * @param array $data
     * @since 3.0.0
     */
    protected function maybe_skip_fieldset($fieldset_key, $data)
    {
        if ('shipping' === $fieldset_key && (!$data['ship_to_different_address'] || !WC()->cart->needs_shipping_address())) {
            return true;
        }
        if ('account' === $fieldset_key && (is_user_logged_in() || (!$this->is_registration_required() && empty($data['createaccount'])))) {
            return true;
        }
        return false;
    }

    /**
     * Get posted data from the checkout form.
     *
     * @return array of data.
     * @since  3.0.0
     */
    protected function get_posted_data()
    {
        $skipped = array();
        $data = array(
            'terms' => (int)isset($_POST['terms']),
            'createaccount' => (int)!empty($_POST['createaccount']),
            'payment_method' => isset($_POST['payment_method']) ? wc_clean($_POST['payment_method']) : '',
            'shipping_method' => isset($_POST['shipping_method']) ? wc_clean($_POST['shipping_method']) : '',
            'ship_to_different_address' => !empty($_POST['ship_to_different_address']) && !wc_ship_to_billing_address_only(),
            'woocommerce_checkout_update_totals' => isset($_POST['woocommerce_checkout_update_totals']),
        );
        foreach ($this->get_checkout_fields() as $fieldset_key => $fieldset) {
            if ($this->maybe_skip_fieldset($fieldset_key, $data)) {
                $skipped[] = $fieldset_key;
                continue;
            }
            foreach ($fieldset as $key => $field) {
                $type = sanitize_title(isset($field['type']) ? $field['type'] : 'text');

                switch ($type) {
                    case 'checkbox' :
                        $value = (int)isset($_POST[$key]);
                        break;
                    case 'multiselect' :
                        $value = isset($_POST[$key]) ? implode(', ', wc_clean($_POST[$key])) : '';
                        break;
                    case 'textarea' :
                        $value = isset($_POST[$key]) ? wc_sanitize_textarea($_POST[$key]) : '';
                        break;
                    default :
                        $value = isset($_POST[$key]) ? wc_clean($_POST[$key]) : '';
                        break;
                }

                $data[$key] = apply_filters('woocommerce_process_checkout_' . $type . '_field', apply_filters('woocommerce_process_checkout_field_' . $key, $value));
            }
        }

        if (in_array('shipping', $skipped) && WC()->cart->needs_shipping_address()) {
            foreach ($this->get_checkout_fields('shipping') as $key => $field) {
                $data[$key] = isset($data['billing_' . substr($key, 9)]) ? $data['billing_' . substr($key, 9)] : '';
            }
        }

        return $data;
    }

    /**
     * Validates the posted checkout data based on field properties.
     *
     * @param array $data An array of posted data.
     * @param WP_Error $errors
     * @since  3.0.0
     */
    protected function validate_posted_data(&$data, &$errors)
    {
        foreach ($this->get_checkout_fields() as $fieldset_key => $fieldset) {
            if ($this->maybe_skip_fieldset($fieldset_key, $data)) {
                continue;
            }
            foreach ($fieldset as $key => $field) {
                if (!isset($data[$key])) {
                    continue;
                }
                $required = !empty($field['required']);
                $format = array_filter(isset($field['validate']) ? (array)$field['validate'] : array());
                $field_label = isset($field['label']) ? $field['label'] : '';

                switch ($fieldset_key) {
                    case 'shipping' :
                        /* translators: %s: field name */
                        $field_label = sprintf(__('Shipping %s', 'woocommerce'), strtolower($field_label));
                        break;
                    case 'billing' :
                        /* translators: %s: field name */
                        $field_label = sprintf(__('Billing %s', 'woocommerce'), strtolower($field_label));
                        break;
                }

                if (in_array('postcode', $format)) {
                    $country = isset($data[$fieldset_key . '_country']) ? $data[$fieldset_key . '_country'] : WC()->customer->{"get_{$fieldset_key}_country"}();
                    $data[$key] = wc_format_postcode($data[$key], $country);

                    if ('' !== $data[$key] && !WC_Validation::is_postcode($data[$key], $country)) {
                        $errors->add('validation', __('Please enter a valid postcode / ZIP.', 'woocommerce'));
                    }
                }

                if (in_array('phone', $format)) {
                    $data[$key] = wc_format_phone_number($data[$key]);

                    if ('' !== $data[$key] && !WC_Validation::is_phone($data[$key])) {
                        /* translators: %s: phone number */
                        $errors->add('validation', sprintf(__('%s is not a valid phone number.', 'woocommerce'), '<strong>' . $field_label . '</strong>'));
                    }
                }

                if (in_array('email', $format) && '' !== $data[$key]) {
                    $data[$key] = sanitize_email($data[$key]);

                    if (!is_email($data[$key])) {
                        /* translators: %s: email address */
                        $errors->add('validation', sprintf(__('%s is not a valid email address.', 'woocommerce'), '<strong>' . $field_label . '</strong>'));
                    }
                }

                if ('' !== $data[$key] && in_array('state', $format)) {
                    $country = isset($data[$fieldset_key . '_country']) ? $data[$fieldset_key . '_country'] : WC()->customer->{"get_{$fieldset_key}_country"}();
                    $valid_states = WC()->countries->get_states($country);

                    if (!empty($valid_states) && is_array($valid_states) && sizeof($valid_states) > 0) {
                        $valid_state_values = array_flip(array_map('strtolower', $valid_states));

                        // Convert value to key if set
                        if (isset($valid_state_values[strtolower($data[$key])])) {
                            $data[$key] = $valid_state_values[strtolower($data[$key])];
                        }

                        if (!in_array($data[$key], array_keys($valid_states))) {
                            /* translators: 1: state field 2: valid states */
                            $errors->add('validation', sprintf(__('%1$s is not valid. Please enter one of the following: %2$s', 'woocommerce'), '<strong>' . $field_label . '</strong>', implode(', ', $valid_states)));
                        }
                    }
                }

                if ($required && '' === $data[$key]) {
                    /* translators: %s: field name */
                    $errors->add('required-field', apply_filters('woocommerce_checkout_required_field_notice', sprintf(__('%s is a required field.', 'woocommerce'), '<strong>' . $field_label . '</strong>'), $field_label));
                }
            }
        }
    }

    /**
     * Validates that the checkout has enough info to proceed.
     *
     * @param array $data An array of posted data.
     * @param WP_Error $errors
     * @since  3.0.0
     */
    protected function validate_checkout(&$data, &$errors)
    {
        //$this->validate_posted_data( $data, $errors );
        $this->check_cart_items();

        if (empty($data['woocommerce_checkout_update_totals']) && empty($data['terms']) && apply_filters('woocommerce_checkout_show_terms', wc_get_page_id('terms') > 0)) {
            $errors->add('terms', __('You must accept our Terms &amp; Conditions.', 'woocommerce'));
        }

        if (WC()->cart->needs_shipping()) {
            $shipping_country = WC()->customer->get_shipping_country();

            if (empty($shipping_country)) {
                $errors->add('shipping', __('Please enter an address to continue.', 'woocommerce'));
            } elseif (!in_array(WC()->customer->get_shipping_country(), array_keys(WC()->countries->get_shipping_countries()))) {
                $errors->add('shipping', sprintf(__('Unfortunately <strong>we do not ship %s</strong>. Please enter an alternative shipping address.', 'woocommerce'), WC()->countries->shipping_to_prefix() . ' ' . WC()->customer->get_shipping_country()));
            } else {
                $chosen_shipping_methods = WC()->session->get('chosen_shipping_methods');

                foreach (WC()->shipping->get_packages() as $i => $package) {
                    if (!isset($chosen_shipping_methods[$i], $package['rates'][$chosen_shipping_methods[$i]])) {
                        $errors->add('shipping', __('No shipping method has been selected. Please double check your address, or contact us if you need any help.', 'woocommerce'));
                    }
                }
            }
        }

        if (WC()->cart->needs_payment()) {
            $available_gateways = WC()->payment_gateways->get_available_payment_gateways();

            if (!isset($available_gateways[$data['payment_method']])) {
                $errors->add('payment', __('Invalid payment method.', 'woocommerce'));
            } else {
                $available_gateways[$data['payment_method']]->validate_fields();
            }
        }

        do_action('woocommerce_after_checkout_validation', $data, $errors);
    }

    /**
     * Update customer and session data from the posted checkout data.
     *
     * @param array $data
     * @since  3.0.0
     */
    protected function update_session($data)
    {
        if (isset($data['billing_country'])) {
            WC()->customer->set_billing_country($data['billing_country']);
            WC()->customer->set_shipping_country($data['billing_country']);
        }
        if (isset($data['billing_state'])) {
            WC()->customer->set_billing_state($data['billing_state']);
            WC()->customer->set_shipping_state($data['billing_state']);
        }
        if (isset($data['billing_postcode'])) {
            WC()->customer->set_billing_postcode($data['billing_postcode']);
            WC()->customer->set_shipping_postcode($data['billing_postcode']);
        }
        if (isset($data['shipping_country'])) {
            WC()->customer->set_shipping_country($data['shipping_country']);
        }
        if (isset($data['shipping_state'])) {
            WC()->customer->set_shipping_state($data['shipping_state']);
        }
        if (isset($data['shipping_postcode'])) {
            WC()->customer->set_shipping_postcode($data['shipping_postcode']);
        }
        WC()->customer->save();

        // Update customer shipping and payment method to posted method
        $chosen_shipping_methods = WC()->session->get('chosen_shipping_methods');

        if (is_array($data['shipping_method'])) {
            foreach ($data['shipping_method'] as $i => $value) {
                $chosen_shipping_methods[$i] = $value;
            }
        }

        WC()->session->set('chosen_shipping_methods', $chosen_shipping_methods);
        WC()->session->set('chosen_payment_method', $data['payment_method']);

        // Update cart totals now we have customer address.
        WC()->cart->calculate_totals();
    }


    /**
     * Process an order that does require payment.
     *
     * @param int $order_id
     * @param string $payment_method
     * @since  3.0.0
     */
    protected function process_order_payment($order_id, $payment_method)
    {
        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();

        if (!isset($available_gateways[$payment_method])) {
            return;
        }

        // Store Order ID in session so it can be re-used after payment failure
        WC()->session->set('order_awaiting_payment', $order_id);

        // Process Payment
        $result = $available_gateways[$payment_method]->process_payment($order_id);

        // Redirect to success/confirmation/payment page
        if (isset($result['result']) && 'success' === $result['result']) {
            $result = apply_filters('woocommerce_payment_successful_result', $result, $order_id);

            return $order_id;
            /*if ( is_ajax() ) {
                wp_send_json( $result );
            } else {
                wp_redirect( $result['redirect'] );
                exit;
            }*/
        }
    }

    /**
     * Process an order that doesn't require payment.
     *
     * @param int $order_id
     * @since  3.0.0
     */
    protected function process_order_without_payment($order_id)
    {
        $order = wc_get_order($order_id);
        $order->payment_complete();
        wc_empty_cart();

        return $order_id;
        /*if ( is_ajax() ) {
            wp_send_json( array(
                'result'   => 'success',
                'redirect' => apply_filters( 'woocommerce_checkout_no_payment_needed_redirect', $order->get_checkout_order_received_url(), $order ),
            ) );
        } else {
            wp_safe_redirect(
                apply_filters( 'woocommerce_checkout_no_payment_needed_redirect', $order->get_checkout_order_received_url(), $order )
            );
            exit;
        }*/
    }

    /**
     * Create a new customer account if needed.
     * @param array $data
     * @throws Exception
     */
    protected function process_customer($data)
    {
        $customer_id = get_current_user_id();

        if (!is_user_logged_in() && ($this->is_registration_required() || !empty($data['createaccount']))) {
            $username = !empty($data['account_username']) ? $data['account_username'] : '';
            $password = !empty($data['account_password']) ? $data['account_password'] : '';
            $customer_id = wc_create_new_customer($data['billing_email'], $username, $password);

            if (is_wp_error($customer_id)) {
                throw new Exception($customer_id->get_error_message());
            }

            wp_set_current_user($customer_id);
            wc_set_customer_auth_cookie($customer_id);

            // As we are now logged in, checkout will need to refresh to show logged in data
            WC()->session->set('reload_checkout', true);

            // Also, recalculate cart totals to reveal any role-based discounts that were unavailable before registering
            WC()->cart->calculate_totals();
        }

        // Add customer info from other fields.
        if ($customer_id && apply_filters('woocommerce_checkout_update_customer_data', true, $this)) {
            $customer = new WC_Customer($customer_id);

            if (!empty($data['billing_first_name'])) {
                $customer->set_first_name($data['billing_first_name']);
            }

            if (!empty($data['billing_last_name'])) {
                $customer->set_last_name($data['billing_last_name']);
            }

            foreach ($data as $key => $value) {
                // Use setters where available.
                if (is_callable(array($customer, "set_{$key}"))) {
                    $customer->{"set_{$key}"}($value);

                    // Store custom fields prefixed with wither shipping_ or billing_.
                } elseif (0 === stripos($key, 'billing_') || 0 === stripos($key, 'shipping_')) {
                    $customer->update_meta_data($key, $value);
                }
            }

            /**
             * Action hook to adjust customer before save.
             * @since 3.0.0
             */
            do_action('woocommerce_checkout_update_customer', $customer, $data);

            $customer->save();
        }

        do_action('woocommerce_checkout_update_user_meta', $customer_id, $data);
    }

    /**
     * If checkout failed during an AJAX call, send failure response.
     */
    protected function send_ajax_failure_response()
    {
        if (is_ajax()) {
            // only print notices if not reloading the checkout, otherwise they're lost in the page reload
            if (!isset(WC()->session->reload_checkout)) {
                ob_start();
                wc_print_notices();
                $messages = ob_get_clean();
            }

            $response = array(
                'result' => 'failure',
                'messages' => isset($messages) ? $messages : '',
                'refresh' => isset(WC()->session->refresh_totals),
                'reload' => isset(WC()->session->reload_checkout),
            );

            unset(WC()->session->refresh_totals, WC()->session->reload_checkout);

            wp_send_json($response);
        }
    }

    /**
     * Process the checkout after the confirm order button is pressed.
     */
    public function process_checkout()
    {
        try {
            if (empty($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'woocommerce-process_checkout')) {
                WC()->session->set('refresh_totals', true);
                throw new Exception(__('We were unable to process your order, please try again.', 'woocommerce'));
            }

            wc_maybe_define_constant('WOOCOMMERCE_CHECKOUT', true);
            wc_set_time_limit(0);

            do_action('woocommerce_before_checkout_process');

            if (WC()->cart->is_empty()) {
                throw new Exception(sprintf(__('Sorry, your session has expired. <a href="%s" class="wc-backward">Return to shop</a>', 'woocommerce'), esc_url(wc_get_page_permalink('shop'))));
            }

            do_action('woocommerce_checkout_process');

            $errors = new WP_Error();
            $posted_data = $this->get_posted_data();

            // Update session for customer and totals.
            $this->update_session($posted_data);

            // Validate posted data and cart items before proceeding.
            $this->validate_checkout($posted_data, $errors);

            foreach ($errors->get_error_messages() as $message) {
                wc_add_notice($message, 'error');
            }

            if (empty($posted_data['woocommerce_checkout_update_totals']) && 0 === wc_notice_count('error')) {
                $this->process_customer($posted_data);
                $order_id = $this->create_order($posted_data);
                $order = wc_get_order($order_id);

                if (is_wp_error($order_id)) {
                    throw new Exception($order_id->get_error_message());
                }

                do_action('woocommerce_checkout_order_processed', $order_id, $posted_data, $order);

                $payable = max(0, (int) $order->get_total());
                if ($order->needs_payment() && $payable <= 0) {
                    throw new Exception(
                        __('مبلغ پرداخت آنلاین نامعتبر است. لطفاً دوباره تلاش کنید یا با پشتیبانی تماس بگیرید.', 'escapezoom-v2')
                    );
                }

                if (WC()->cart->needs_payment()) {
                    return $this->process_order_payment($order_id, $posted_data['payment_method']);
                } else {
                    return $this->process_order_without_payment($order_id);
                }
            }
        } catch (Exception $e) {
            wc_add_notice($e->getMessage(), 'error');
        }
        return false;
        //$this->send_ajax_failure_response();
    }

    /**
     * Get a posted address field after sanitization and validation.
     *
     * @param string $key
     * @param string $type billing for shipping
     * @return string
     */
    public function get_posted_address_data($key, $type = 'billing')
    {
        if ('billing' === $type || false === $this->posted_data['ship_to_different_address']) {
            $return = isset($this->legacy_posted_data['billing_' . $key]) ? $this->legacy_posted_data['billing_' . $key] : '';
        } else {
            $return = isset($this->legacy_posted_data['shipping_' . $key]) ? $this->legacy_posted_data['shipping_' . $key] : '';
        }
        return $return;
    }

    /**
     * Gets the value either from the posted data, or from the users meta data.
     *
     * @param string $input
     * @return string
     */
    public function get_value($input)
    {
        if (!empty($_POST[$input])) {
            return wc_clean($_POST[$input]);

        } else {

            $value = apply_filters('woocommerce_checkout_get_value', null, $input);

            if (null !== $value) {
                return $value;
            }

            if (is_callable(array(WC()->customer, "get_$input"))) {
                $value = WC()->customer->{"get_$input"}() ? WC()->customer->{"get_$input"}() : null;
            } elseif (WC()->customer->meta_exists($input)) {
                $value = WC()->customer->get_meta($input, true);
            }

            return apply_filters('default_checkout_' . $input, $value, $input);
        }
    }
}
//**********************************************************************************************************/
//**********************************************************************************************************/
//**********************************************************************************************************/

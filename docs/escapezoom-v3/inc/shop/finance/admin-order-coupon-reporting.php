<?php
/**
 * Shop module (migrated from saeed-codes.php).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*===============================================================================*/
if(!class_exists('WC_Filter_Orders_By_Coupon')){
    class WC_Filter_Orders_By_Coupon {
        const VERSION = '1.0.0';

        protected static $instance;

        public function __construct() {
            if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
                add_filter( 'post_row_actions', [ $this, 'coupons_row_actions' ], 10, 2 );
                add_action( 'restrict_manage_posts', [ $this, 'filter_orders_by_coupon_used' ] );
                add_filter( 'posts_join', [ $this, 'add_order_items_join' ] );
                add_filter( 'posts_where', [ $this, 'add_filterable_where' ] );
            }
        }

        public function coupons_row_actions( $actions, $post ) {
            if ( $post->post_type == 'shop_coupon' ) {

                $url = admin_url( 'edit.php?post_type=shop_order&_coupons_used=' . $post->post_title );

                $actions = array_merge( $actions, [
                    'orders' => sprintf( '<a href="%1$s" target="_blank">%2$s</a>', esc_url( $url ), 'گزارش سفارشات' ),
                ] );

            }

            return $actions;
        }

        public function filter_orders_by_coupon_used() {
            global $typenow;

            if ( 'shop_order' === $typenow ) {
                $args = [
                    'posts_per_page' => - 1,
                    'orderby'        => 'title',
                    'order'          => 'asc',
                    'post_type'      => 'shop_coupon',
                    'post_status'    => 'publish',
                ];

                $coupons = get_posts( $args );

                if ( ! empty( $coupons ) ) : ?>
                    <select name="_coupons_used" id="dropdown_coupons_used">
                        <option value="">
                            فیلتر بر اساس کد تخفیف
                        </option>
                        <?php foreach ( $coupons as $coupon ) : ?>
                            <option value="<?php echo esc_attr( $coupon->post_title ); ?>" <?php echo esc_attr( isset( $_GET['_coupons_used'] ) ? selected( $coupon->post_title, $_GET['_coupons_used'], false ) : '' ); ?>>
                                <?php echo esc_html( $coupon->post_title ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <script>
                        jQuery(document).ready(function ($) {
                            $("#dropdown_coupons_used").select2()
                        })
                    </script>
                <?php endif;
            }
        }

        public function add_order_items_join( $join ) {
            global $typenow, $wpdb;

            if ( 'shop_order' === $typenow && isset( $_GET['_coupons_used'] ) && ! empty( $_GET['_coupons_used'] ) ) {
                $join .= "LEFT JOIN {$wpdb->prefix}woocommerce_order_items woi ON {$wpdb->posts}.ID = woi.order_id";
            }

            return $join;
        }

        public function add_filterable_where( $where ) {
            global $typenow, $wpdb;

            if ( 'shop_order' === $typenow && isset( $_GET['_coupons_used'] ) && ! empty( $_GET['_coupons_used'] ) ) {
                $where .= $wpdb->prepare( " AND woi.order_item_type='coupon' AND woi.order_item_name='%s'", wc_clean( $_GET['_coupons_used'] ) );
            }

            return $where;
        }

        public static function instance(): ?WC_Filter_Orders_By_Coupon {
            if ( is_null( self::$instance ) ) {
                self::$instance = new self();
            }

            return self::$instance;
        }
    }

    WC_Filter_Orders_By_Coupon::instance();
}

add_action('init', function(){
    $role = get_role( 'shopist' );

    if ( $role ){
        $role->add_cap( 'edit_job');
        $role->add_cap( 'edit_jobs');
        $role->add_cap( 'edit_other_jobs');
        $role->add_cap( 'publish_jobs');
        $role->add_cap( 'read_job');
        $role->add_cap( 'read_private_jobs');
        $role->add_cap( 'delete_job');

        $role->add_cap( 'edit_shop_coupon' );
        $role->add_cap( 'read_shop_coupon' );
        $role->add_cap( 'delete_shop_coupon' );
        $role->add_cap( 'edit_shop_coupons' );
        $role->add_cap( 'edit_others_shop_coupons' );
        $role->add_cap( 'publish_shop_coupons' );
        $role->add_cap( 'read_private_shop_coupons' );
        $role->add_cap( 'delete_shop_coupons' );
        $role->add_cap( 'delete_private_shop_coupons' );
        $role->add_cap( 'delete_published_shop_coupons' );
        $role->add_cap( 'delete_others_shop_coupons' );
        $role->add_cap( 'edit_private_shop_coupons' );
        $role->add_cap( 'edit_published_shop_coupons' );
    }
});

if( isset($_GET['_coupons_used']) ){

    add_filter( 'manage_edit-shop_order_columns', function ( $columns ) {
        $columns['city']    = 'شهر';
        $columns['income']  = 'درآمد';

        return $columns;
    } );

    add_action( 'manage_shop_order_posts_custom_column', function ( $column ) {
        global $post;

        $order = wc_get_order( $post->ID );

        if ( $column == 'city' ) {
            foreach( $order->get_items() as $item ){
                $product = $item->get_product();
                $id      = $product->get_id();
                $meta    = ez_get_product_meta( $id );

                echo $meta->city_name;
            }
        }

        if ( $column == 'income' ) {

            foreach ($order->get_items() as $item) {
                $order_id = $post->ID;
                $product_id = $item->get_product_id();
                $item_quantity = $item->get_quantity();
            }

            $pish_per_person = get_post_meta( $order_id, 'ticket_tedad', true );
            $pish_per_person = !empty( $pish_per_person ) ? $pish_per_person : get_post_meta( $product_id, 'pish_pardakht_per_person', true );
            $pish_per_person = !empty( $pish_per_person ) ? $pish_per_person : 1;

            $pish = get_post_meta( $order_id, "_order_total_2", true );

            $pish_final = $pish ? : get_post_meta( $order_id, "_order_total", true );
            $item_total = $pish_final / $pish_per_person * $item_quantity;
            $tax = 10;
            $commission = 10;
            if (get_post_meta($product_id, "darsad", true)){
                $commission = get_post_meta($product_id, "darsad", true);
            }
            $porsant = $item_total * ($commission / 100);

            echo number_format($porsant) . ' تومان';
        }
    } );
}

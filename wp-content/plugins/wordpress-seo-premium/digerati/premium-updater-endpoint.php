<?php
if ( class_exists( 'Yoast_WP_SEO_DZHK_Updater' ) ) {
	return;
}

class Yoast_WP_SEO_DZHK_Updater {

	/**
	 * Name of the product
	 *
	 */
	private $product = null;
	/**
	 * Name of the store
	 *
	 */
	private $store = null;

	/**
	 * Name of the transient
	 *
	 */
	private $transient = null;

	/**
	 * URL of promotions json file
	 *
	 */
	private $remote_url = null;

	/**
	 * Current version of product
	 *
	 */
	private $version = null;

	/**
	 * The single instance of the class.
	 *
	 * @var Yoast_WP_SEO_DZHK_Updater
	 */
	protected static $_instance = null;

	/**
	 * ZHK_Refund_Order constructor.
	 */
	public function __construct( $product_name, $product_version, $promotion_url, $store_name ) {
		$this->product    = $product_name;
		$this->store      = $store_name;
		$this->transient  = $store_name . '_' . $product_name;
		$this->remote_url = $promotion_url;
		$this->version    = $product_version;

		add_action( 'admin_notices', array( $this, 'admin_notice' ), 15 );
	}

	/**
	 * Main Class Instance.
	 *
	 * Ensures only one instance of this class is loaded or can be loaded.
	 *
	 * @static
	 * @return Yoast_WP_SEO_DZHK_Updater - Main instance.
	 */
	public static function instance( $product_name, $product_version, $promotion_url, $store_name ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $product_name, $product_version, $promotion_url, $store_name );
		}

		return self::$_instance;
	}

	public function admin_notice() {
		$promotions = get_transient( $this->transient );


    if ( $promotions === false ) {
      $response = wp_remote_get( $this->remote_url, array( 'sslverify' => false, 'timeout' => 2 ) );
      if ( ! is_wp_error( $response ) ) {
        $body       = wp_remote_retrieve_body( $response );
        $promotions = json_decode( $body, true );
        set_transient( $this->transient, $promotions, 86400 );
      }else{
        set_transient( $this->transient, [], 10800 );
      }
    }

		$show_promotions = [];
		$update_messages = [];

		// Get general promotions
		if ( isset( $promotions['general'] ) && ! empty( $promotions['general'] ) ) {
			foreach ( $promotions['general'] as $promotion ) {
				$show_promotions[] = $promotion;
			}
		}

		// Get specific product promotions
		if ( isset( $promotions['products'] ) && ! empty( $promotions['products'] ) ) {
			foreach ( $promotions['products'] as $product_promotion ) {
				if ( $product_promotion['product'] === $this->product ) {
					$show_promotions[] = $product_promotion;
				}
			}
		}

		// Get update messages
		if ( isset( $promotions['update'] ) && ! empty( $promotions['update'] ) ) {
			if ( array_key_exists( $this->product, $promotions['update'] ) ) {
				if ( version_compare( $this->version, $promotions['update'][ $this->product ]['version'], '<' ) ) {
					$update_messages[] = $promotions['update'][ $this->product ];
				}
			}
		}

		if ( ! empty( $show_promotions ) ) {
			foreach ( $show_promotions as $show_promotion ) {
				$class           = $this->store . '_promotions notice notice-' . $show_promotion['type'];
				$unique_promo_id = $this->store . '-notice-' . md5( $show_promotion['id'] );
				if ( isset( $_COOKIE[ $unique_promo_id ] ) && ! empty( $_COOKIE[ $unique_promo_id ] ) && $_COOKIE[ $unique_promo_id ] === 'hide' ) {
					continue;
				}
				if ( isset( $show_promotion['expire'] ) && ! empty( $show_promotion['expire'] ) && time() > strtotime( $show_promotion['expire'] ) ) {
					continue;
				}
				if ( isset( $show_promotion['start'] ) && ! empty( $show_promotion['start'] ) && time() < strtotime( $show_promotion['start'] ) ) {
					continue;
				}
				?>
                <div class="<?php echo $class ?>" style="position: relative;">
					<?php if ( isset( $show_promotion['image'] ) && ! empty( $show_promotion['image'] ) ): ?>
                        <img src="<?php echo $show_promotion['image']; ?>" alt=""
                             style="float: right;  height: auto; margin: 10px 0 10px 20px;">
					<?php endif; ?>
                    <h3><?php echo $show_promotion['title'] ?></h3>
                    <p>
						<?php echo $show_promotion['description'] ?>
                        <button type="button" class="notice-dismiss" data-id="<?php echo $unique_promo_id ?>">
                            <span class="screen-reader-text">Dismiss this notice.</span>
                        </button>
                    </p>
                    <p class="submit">
                        <a href="<?php echo esc_url( $show_promotion['button_url'] ); ?>" class="button-primary"
                           target="_blank">
							<?php echo $show_promotion['button_text']; ?>
                        </a>
                    </p>
					<?php if ( isset( $show_promotion['image'] ) && ! empty( $show_promotion['image'] ) ): ?>
                        <div style="clear: both;"></div>
					<?php endif; ?>

                </div>
				<?php
			}
			?>
            <script>
              (function ($) {
                $(document).on('click', '.<?php echo $this->store; ?>_promotions .notice-dismiss', function () {
                  var promoId = $(this).data('id');
                  document.cookie = promoId + "=hide;path=/";
                  $(this).parents('.<?php echo $this->store; ?>_promotions').hide(300);
                });
              })(jQuery);

            </script>
			<?php
		}

		if ( ! empty( $update_messages ) ) {
			foreach ( $update_messages as $update_message ) {
				?>
                <div class="update-nag">
					<?php echo $update_message['description'] ?>
					<?php if ( isset( $update_message['button_url'] ) && ! empty( $update_message['button_url'] ) ): ?>
                    <a href="<?php echo esc_url( $update_message['button_url'] ); ?>" class="button-secondary"
                       target="_blank">
						<?php echo $update_message['button_text']; ?>
                    </a>
					<?php endif; ?>
                </div>
				<?php
			}
		}
	}
}
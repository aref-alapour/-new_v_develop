<?php
defined('ABSPATH') || exit ("no access");
if( empty($this->c07e9280f75be71202a) ): ?>
    <div class="notice notice-error">
        <?php if (version_compare(PHP_VERSION, '7.0.0') >= 0):?>
        <p>
            <?php printf(esc_html__( 'To activating %s, please insert your license key', 'guard-gn-d34c8e874c20f416dbd1e58f1f7c86af' ), esc_html__($this->c3708393e0480c755f3a4b1b641e, 'guard-gn-d34c8e874c20f416dbd1e58f1f7c86af')); ?>
            <a href="<?php echo admin_url( 'admin.php?page='.$this->f6ce1867e31bb3fc2d9307248b1c ); ?>" class="button button-primary"><?php _e('Active License', 'guard-gn-d34c8e874c20f416dbd1e58f1f7c86af'); ?></a>
        </p>
        <?php else:?>
            <p>
                <?php printf(esc_html__( 'The PHP version of the website is lower than 7.0. Ask your host administrator to upgrade PHP version to activate %s. ', 'guard-gn-d34c8e874c20f416dbd1e58f1f7c86af' ), esc_html__($this->c3708393e0480c755f3a4b1b641e, 'guard-gn-d34c8e874c20f416dbd1e58f1f7c86af')); ?>
            </p>
    <?php endif; ?>
    </div>
<?php elseif( $this->b8526736a6c7f4b079d205f54b88dac===true ): ?>
    <div class="notice notice-error">
        <p>
            <?php printf(esc_html__( 'Something is wrong with your %s license. Please check it.', 'guard-gn-d34c8e874c20f416dbd1e58f1f7c86af' ), esc_html__($this->c3708393e0480c755f3a4b1b641e, 'guard-gn-d34c8e874c20f416dbd1e58f1f7c86af')); ?>
            <a href="<?php echo admin_url( 'admin.php?page='.$this->f6ce1867e31bb3fc2d9307248b1c ); ?>" class="button button-primary"><?php _e('Check Now', 'guard-gn-d34c8e874c20f416dbd1e58f1f7c86af'); ?></a>
        </p>
    </div>
<?php endif; ?>
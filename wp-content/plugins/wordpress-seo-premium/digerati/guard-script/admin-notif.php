<?php
defined('ABSPATH') || exit ("no access");
if( empty($this->c96d3a0861c1ae155f8de452696e15f) ): ?>
    <div class="notice notice-error">
        <?php if (version_compare(PHP_VERSION, '7.0.0') >= 0):?>
            <p>
                <?php printf(esc_html__( 'To activating your %s please insert you license key', 'zhaket-guard' ), $this->cea43a35894b4d1f8f71f713e); ?>
                <a href="<?php echo admin_url( 'admin.php?page='.$this->dfef17edb6cb0ce530a6874ff ); ?>" class="button button-primary"><?php _e('Register Activate Code', 'zhaket-guard'); ?></a>
            </p>
        <?php else:?>
            <p>
                <?php printf(esc_html__( 'Your PHP version is lower than 7. for active yoast it must be updated.', 'zhaket-guard' ), $this->cea43a35894b4d1f8f71f713e); ?>
            </p>
    <?php endif; ?>
    </div>
<?php elseif( $this->b5239b7cb852405177fec6015f===true ): ?>
    <div class="notice notice-error">
        <p>
            <?php printf(esc_html__( 'There is something wrong with your %s license. please check it.', 'zhaket-guard' ), $this->cea43a35894b4d1f8f71f713e); ?>
            <a href="<?php echo admin_url( 'admin.php?page='.$this->dfef17edb6cb0ce530a6874ff ); ?>" class="button button-primary"><?php _e('Check Now', 'zhaket-guard'); ?></a>
        </p>
    </div>
<?php endif; ?>
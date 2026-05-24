<?php
defined('ABSPATH') || exit ("no access");
if( empty($this->f3869b31103e8e0ef1f3f993bd284c0c) ): ?>
    <div class="notice notice-error">
        <?php if (version_compare(PHP_VERSION, '7.0.0') >= 0):?>
        <p>
            <?php printf(esc_html__( 'To activating %s, please insert your license key', 'guard-gn-b25d318256a9ae6063b3778ed197b' ), esc_html__($this->e324d204c01709ca9194dd8a, 'guard-gn-b25d318256a9ae6063b3778ed197b')); ?>
            <a href="<?php echo admin_url( 'admin.php?page='.$this->f30db8d67a2479ff75b4c5e91c7f3da ); ?>" class="button button-primary"><?php _e('Active License', 'guard-gn-b25d318256a9ae6063b3778ed197b'); ?></a>
        </p>
        <?php else:?>
            <p>
                <?php printf(esc_html__( 'The PHP version of the website is lower than 7.0. Ask your host administrator to upgrade PHP version to activate %s. ', 'guard-gn-b25d318256a9ae6063b3778ed197b' ), esc_html__($this->e324d204c01709ca9194dd8a, 'guard-gn-b25d318256a9ae6063b3778ed197b')); ?>
            </p>
    <?php endif; ?>
    </div>
<?php elseif( $this->c594408f728ca06b5bd7f22275fb7bff===true ): ?>
    <div class="notice notice-error">
        <p>
            <?php printf(esc_html__( 'Something is wrong with your %s license. Please check it.', 'guard-gn-b25d318256a9ae6063b3778ed197b' ), esc_html__($this->e324d204c01709ca9194dd8a, 'guard-gn-b25d318256a9ae6063b3778ed197b')); ?>
            <a href="<?php echo admin_url( 'admin.php?page='.$this->f30db8d67a2479ff75b4c5e91c7f3da ); ?>" class="button button-primary"><?php _e('Check Now', 'guard-gn-b25d318256a9ae6063b3778ed197b'); ?></a>
        </p>
    </div>
<?php endif; ?>
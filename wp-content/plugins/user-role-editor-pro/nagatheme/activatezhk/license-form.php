<?php defined('ABSPATH') || exit ("no access");  ?>
<style>
	<?php include __DIR__.'/assets/style.css' ?>
</style>
<div class="license-input">
    <script>
        var zhaket_guard=<?php echo json_encode(array(
                                                    'ajax_url' => admin_url('admin-ajax.php'),
                                                    'confirm_msg' => esc_html__('Are you sure?', 'guard-gn-b25d318256a9ae6063b3778ed197b'),
                                                    'wrong_license_message' => esc_html__('Something goes wrong, please try again.', 'guard-gn-b25d318256a9ae6063b3778ed197b'),
                                                    'this_slug' => $this->f30db8d67a2479ff75b4c5e91c7f3da,
                                                    'view_problem_console_log' => esc_html__('Something is wrong, please check the console log for details',
                                                                                             'guard-gn-b25d318256a9ae6063b3778ed197b'),
                                                    'please_add_valid_license' => esc_html__('License key is not valid, Please enter valid license key.',
                                                                                             'guard-gn-b25d318256a9ae6063b3778ed197b'),
                                                    'nonce' => wp_create_nonce('guard-gn-b25d318256a9ae6063b3778ed197b'),
                                                )) ?>
    </script>
    <script>
        <?php include __DIR__.'/assets/script.js' ?>
    </script>
    <h1> <?php printf(esc_html__('%s Activation', 'guard-gn-b25d318256a9ae6063b3778ed197b'), esc_html__($this->e324d204c01709ca9194dd8a, 'guard-gn-b25d318256a9ae6063b3778ed197b')); ?></h1>
    <?php if ($this->f3869b31103e8e0ef1f3f993bd284c0c): ?>
        <h3><?php esc_html_e('Your activation key:', 'guard-gn-b25d318256a9ae6063b3778ed197b') ?></h3>
        <code id="code-style"><?php echo $this->ab66f2127bff22fd55c78646f37d() ?></code>
        <div class="text-left">
            <span id="recheck-license" onclick="recheck_licence(this)"><?php esc_html_e('Recheck license', 'guard-gn-b25d318256a9ae6063b3778ed197b') ?></span>
            <span id="remove-license" onclick="remove_licence(this)"><?php esc_html_e('Remove / Change key', 'guard-gn-b25d318256a9ae6063b3778ed197b') ?></span>
        </div>
        <div id="license-message" style="display: flex; <?php echo ($this->c594408f728ca06b5bd7f22275fb7bff===true)? 'background:red;':''?>">
            <div class="result" style=""><?php echo $this->c7ecd03cfef5536eebb28fcf9d('last_message'); ?></div>
        </div>
		<?php if($this->c594408f728ca06b5bd7f22275fb7bff===true): ?>
			<div id="license-warning" style="display: flex; background:#90e5ff; color:black">
				<div><?php esc_html_e('Your license is active but need to revalidate. if has error on revalidate you can test after 24 hours.',
									  'guard-gn-b25d318256a9ae6063b3778ed197b') ?></div>
			</div>
		<?php endif; ?>
        <!-- /#license-message -->
    <?php else: ?>
        <h3><?php esc_html_e('Enter your activation key:', 'guard-gn-b25d318256a9ae6063b3778ed197b') ?></h3>
        <input id="license-input" type="text" value="">
        <div class="text-left">
                    <span id="install-license" onclick="install_licence(this)"><?php esc_html_e('Activate',
                                                                                                'guard-gn-b25d318256a9ae6063b3778ed197b') ?></span>
        </div>
        <div id="license-message">
        </div>
    <?php endif; ?>

    <!-- /#license-message -->
    <div id="license-help">
        <strong><?php esc_html_e('Manual:', 'guard-gn-b25d318256a9ae6063b3778ed197b') ?></strong>
        <ul>
            <?php if ($this->f3869b31103e8e0ef1f3f993bd284c0c): ?>
                <li>
                    <?php esc_html_e('Your key is used on this website, and it is not possible to use on another website.',
                                     'guard-gn-b25d318256a9ae6063b3778ed197b') ?>
                </li>
                <li>
                    <?php esc_html_e('If you want to transfer a license to another domain, click on the "Remove / Change key", after that login to your account of zhaket.com and go to the download section and click on change domain button. Enter your new domain name and use the license key on your desired domain.',
                                     'guard-gn-b25d318256a9ae6063b3778ed197b') ?>
                </li>
            <?php else: ?>
                <li>
                    <?php esc_html_e('To use the product, you should enter the license key, to find your license key, login to your account of zhaket.com and go to downloads section, after than select product and copy your license key or click on create license button and copy your license key.',
                                     'guard-gn-b25d318256a9ae6063b3778ed197b') ?>
                </li>
                <li>
                    <?php esc_html_e('Each license can be activated only for one website', 'guard-gn-b25d318256a9ae6063b3778ed197b') ?>
                </li>
                <li>
                    <?php esc_html_e('If your license is activated on another domain, first click on the "Remove / Change key" on the old website, then login to your account of zhaket.com and go to the download section and click on the change domain button, enter your website domain name and use the license key to activate.',
                                     'guard-gn-b25d318256a9ae6063b3778ed197b') ?>
                </li>
            <?php endif; ?>
        </ul>
        <?php
        if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
            echo '<hr>';
            echo sprintf( esc_html__( 'The %s constant is set to true. WP-Cron spawning is disabled.', 'guard-gn-b25d318256a9ae6063b3778ed197b' ), 'DISABLE_WP_CRON' );
        }
        if ( defined( 'ALTERNATE_WP_CRON' ) && ALTERNATE_WP_CRON ) {
            echo '<hr>';
            echo sprintf( esc_html__( 'The %s constant is set to true.', 'guard-gn-b25d318256a9ae6063b3778ed197b' ), 'ALTERNATE_WP_CRON'
            );
        }

        ?>
        <hr>
        <span style="display: block;direction: ltr;text-align:left;font-size: 10px">version:3.1</span>
    </div>


</div>
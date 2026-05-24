<?php defined('ABSPATH') || exit ("no access");  ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;700&display=swap" rel="stylesheet">
<style>
    <?php include __DIR__.'/assets/style.css' ?>
</style>
<script>
var zhaket_guard=<?php echo json_encode(array(
                                            'ajax_url' => admin_url('admin-ajax.php'),
                                            'confirm_msg' => esc_html__('Are you sure?', 'zhaket-guard'),
                                            'wrong_license_message' => esc_html__('Something goes wrong please try again.', 'zhaket-guard'),
                                            'this_slug' => $this->dfef17edb6cb0ce530a6874ff,
                                            'view_problem_console_log' => esc_html__('some things is wrong, for view details please check console log',
                                                                                     'zhaket-guard'),
                                            'please_add_valid_license' => esc_html__('your license is not valid, please add valid license',
                                                                                     'zhaket-guard'),
                                            'nonce' => wp_create_nonce('zhaket-guard'),
                                        )) ?>
</script>
<script>
    <?php include __DIR__.'/assets/script.js' ?>
</script>
<div id="main-guard-inner">
    <div class="license-input">
        <h1> <?php printf(esc_html__('Activation %s', 'zhaket-guard'), $this->cea43a35894b4d1f8f71f713e); ?></h1>
        <?php if ($this->c96d3a0861c1ae155f8de452696e15f): ?>
            <h3><?php esc_html_e('Your activation key:', 'zhaket-guard') ?></h3>
            <code id="code-style"><?php echo $this->c336c910921683f74cf3fc8915f5dd() ?></code>
            <div class="text-left">
                    <span id="recheck-license" onclick="recheck_licence(this)"><?php esc_html_e('recheck license', 'zhaket-guard') ?></span>
                    <span id="remove-license" onclick="remove_licence(this)"><?php esc_html_e('remove / change key', 'zhaket-guard') ?></span>
            </div>
            <div id="license-message" style="display: flex; <?php echo ($this->b5239b7cb852405177fec6015f===true)? 'background:red;':''?>">
                <div class="result" style=""><?php echo $this->bb28038196a7b2f10cc2ee8bcb4320a('last_message'); ?></div>
            </div>
            <?php if($this->b5239b7cb852405177fec6015f===true): ?>
				<div id="license-warning" style="display: flex; background:#90e5ff; color:black">
					<div><?php esc_html_e('Your license is active but need to revalidate. if has error on revalidate you can test after 24 hours.',
                                          'zhaket-guard') ?></div>
				</div>
            <?php endif; ?>
            <!-- /#license-message -->
        <?php else: ?>
            <h3><?php esc_html_e('Enter your activation key:', 'zhaket-guard') ?></h3>
            <input id="license-input" type="text" value="">
            <div class="text-left">
                    <span id="install-license" onclick="install_licence(this)"><?php esc_html_e('Activate',
                            'zhaket-guard') ?></span>
            </div>
            <div id="license-message">
            </div>
        <?php endif; ?>

        <!-- /#license-message -->

        <div id="license-help">
            <strong><?php esc_html_e('manual:', 'zhaket-guard') ?></strong>
            <ul>
                <?php if ($this->c96d3a0861c1ae155f8de452696e15f): ?>
                    <li>
                        <?php esc_html_e('Your key is used in this website and it is not possible to your on other website.',
                            'zhaket-guard') ?>
                    </li>
                    <li>
                        <?php esc_html_e('if you want to move this license to another website, first use button "remove / change key" in this website, and next login in your zhaket website account and with click on change domain button , enter new website domain and now you can use from this key in new website.',
                            'zhaket-guard') ?>
                    </li>
                <?php else: ?>
                    <li>
                        <?php esc_html_e('For use from this product , must enter license key. for find your license key, login in your zhaket website account and go to download product section , and find this product and copy your license key or click on create license button and copy your license key.',
                            'zhaket-guard') ?>
                    </li>
                    <li>
                        <?php esc_html_e('every license activation is for one website only.', 'zhaket-guard') ?>
                    </li>
                    <li>
                        <?php esc_html_e('if your license if activated on another website, first use button "remove / change key" on another website, and next login in your zhaket website account and with click on change domain button , enter this website domain and now you can use this key in this website.',
                            'zhaket-guard') ?>
                    </li>
                <?php endif; ?>
            </ul>
            <?php
            if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
                echo '<hr>';
                echo sprintf( esc_html__( 'The %s constant is set to true. WP-Cron spawning is disabled.', '###text-domain###' ), 'DISABLE_WP_CRON' );
            }
            if ( defined( 'ALTERNATE_WP_CRON' ) && ALTERNATE_WP_CRON ) {
                echo '<hr>';
                echo sprintf( esc_html__( 'The %s constant is set to true.', '###text-domain###' ), 'ALTERNATE_WP_CRON'
                );
            }

            ?>
            <hr>
            <span style="display: block;direction: ltr;text-align:left;font-size: 10px">version:3.1</span>
        </div>


    </div>
    <!-- /.license-input -->
    <div class="background-status">
        <?php if ($this->c96d3a0861c1ae155f8de452696e15f): ?>
            <?php include __DIR__.'/assets/unlocked.svg' ?>
        <?php else: ?>
            <?php include __DIR__.'/assets/lock.svg' ?>
        <?php endif; ?>
    </div>
    <!-- /.background-status -->
</div>
<!-- /#main-guard-inner -->
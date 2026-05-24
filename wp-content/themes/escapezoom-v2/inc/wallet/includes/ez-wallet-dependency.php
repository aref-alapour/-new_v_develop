<?php


class EZ_Wallet_Dependency
{
    public function check()
    {
        if ( !self::is_woocommerce_active() ) {
            add_action('admin_notices', array($this, 'ez_wallet_admin_notice'), 15);
            return true;
        }
    }

    public function is_woocommerce_active()
    {
        $plugins = (array)get_option('active_plugins', array());
        return in_array('woocommerce/woocommerce.php', $plugins) || array_key_exists('woocommerce/woocommerce.php', $plugins);
    }

    public function ez_wallet_admin_notice()
    {
        echo '<div class="error"><p>';
        echo 'پلاگین کیف پول، بدون ووکامرس کار نمیکند!!';
        echo '</p></div>';
    }
}
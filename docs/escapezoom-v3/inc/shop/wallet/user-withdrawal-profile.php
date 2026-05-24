<?php
/**
 * Shop module (migrated from saeed-codes.php).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/********************************************************************************************************************************/
add_action('show_user_profile', 'withdrawal_owner_profile_fields');
add_action('edit_user_profile', 'withdrawal_owner_profile_fields');
function withdrawal_owner_profile_fields($user) { ?>
    <h3>اطلاعات حساب کاربر</h3>

    <table class="form-table">
        <tr>
            <th><label for="withdrawal_owner_name">نام مالک حساب</label></th>
            <td><input type="text" name="withdrawal_owner_name" id="withdrawal_owner_name" value="<?php echo esc_attr(get_the_author_meta('withdrawal_owner_name', $user->ID)); ?>" class="regular-text" /></td>
        </tr>
        <tr>
            <th><label for="withdrawal_owner_shaba">شبا مالک حساب</label></th>
            <td><input type="text" name="withdrawal_owner_shaba" id="withdrawal_owner_shaba" value="<?php echo esc_attr(get_the_author_meta('withdrawal_owner_shaba', $user->ID)); ?>" class="regular-text" /></td>
        </tr>
        <tr>
            <th><label for="withdrawal_owner_shaba">کدملی مالک حساب</label></th>
            <td><input type="text" name="withdrawal_owner_identity_card" id="withdrawal_owner_identity_card" value="<?php echo esc_attr(get_the_author_meta('withdrawal_owner_identity_card', $user->ID)); ?>" class="regular-text" /></td>
        </tr>
    </table>
    <?php
}
/*-----------------------------------------------------------------------------------------------*/
add_action('personal_options_update', 'save_withdrawal_owner_profile_fields');
add_action('edit_user_profile_update', 'save_withdrawal_owner_profile_fields');
function save_withdrawal_owner_profile_fields($user_id) {
    if ( !current_user_can('edit_user', $user_id) )
        return false;

    update_user_meta($user_id, 'withdrawal_owner_name', sanitize_text_field($_POST['withdrawal_owner_name']));
    update_user_meta($user_id, 'withdrawal_owner_shaba', sanitize_text_field($_POST['withdrawal_owner_shaba']));
    update_user_meta($user_id, 'withdrawal_owner_identity_card', sanitize_text_field($_POST['withdrawal_owner_identity_card']));
}

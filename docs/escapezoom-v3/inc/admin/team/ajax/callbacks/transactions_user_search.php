<?php
global $wpdb, $wldb;

$medoo = medoo();

$phone = sanitize_text_field($_POST['phone']);

$phone = ltrim($phone, '0');

$users = $medoo->select('wp_users', '*', [
    "user_login[~]" => "%{$phone}%"
]);

if ( !empty($users) ) :
    foreach ( $users as $user ) : ?>

        <a href="javascript:;" data-id="<?php echo htmlspecialchars($user['ID']) ?>" class="team_trans_user_search_item flex items-center gap-x-2 py-2">
            <span><?= htmlspecialchars($user['display_name']) ?></span>
            <span><?= htmlspecialchars($user['user_login']) ?></span>
        </a>

    <?php
    endforeach;

else : ?>

    <a href="javascript:;" class="team_sans_game_search_item flex items-center gap-x-2 py-2">کاربری یافت نشد!</a>
<?php
endif;



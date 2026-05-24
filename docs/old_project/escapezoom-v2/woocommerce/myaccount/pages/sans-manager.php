<?php

global $wpdb;

$user_id = get_current_user_id();

$user_role = get_user_role($user_id);
if ($user_role == 'sans_manager') {
    $user_products = $wpdb->get_results("SELECT *  FROM `wp_postmeta` WHERE `meta_key` LIKE 'sans_manager' AND `meta_value` LIKE {$user_id}", ARRAY_A);
} else {
    $user_products = $wpdb->get_results("SELECT *  FROM `wp_postmeta` WHERE `meta_key` LIKE 'user_ebtal' AND `meta_value` LIKE {$user_id}", ARRAY_A);
}
$active_products = [];
foreach ($user_products as $user_product) {
    $post_id = $user_product['post_id'];

    $sale_status    = get_post_meta($post_id, 'product_state', true);
    $is_active      = ($sale_status == 'active' or $sale_status == 'updated') ? 1 : 0;

    if ($user_id)

        $post_type = get_post_type($post_id);
    $post_status = get_post_status($post_id);

    if ($is_active && $post_type == 'product' && $post_status == 'publish')
        $active_products[] = $post_id;
}
$current_date = strtotime(date('Y-m-d 00:00:00'));

$dates = [];
for ($i = 1; $i <= 15; $i++) {
    $dates[] = $current_date + (60 * 60 * 24 * $i);
}

// اطلاعیهٔ پرداخت اعتباری (همان منطق dashboard — فقط برای مجموعه‌دار)
$show_credit_notification = false;
$credit_notification_is_new = false;
if ( function_exists( 'has_role' ) && has_role( 'compiler' ) ) {
	$credit_table = $wpdb->prefix . 'creadit_form';
	$credit_form_record = $wpdb->get_row( $wpdb->prepare(
		"SELECT id, is_view, canceled FROM `{$credit_table}` WHERE owner_id = %d",
		$user_id
	) );
	if ( ! $credit_form_record || (int) $credit_form_record->canceled !== 1 ) {
		$show_credit_notification = true;
		$credit_notification_is_new = ! $credit_form_record || (int) $credit_form_record->is_view !== 1;
	}
}
if ( $show_credit_notification ) {
	$user = wp_get_current_user();
	$first_name = get_user_meta( $user->ID, 'first_name', true );
	$last_name  = get_user_meta( $user->ID, 'last_name', true );
	$credit_display_name = trim( $first_name . ' ' . $last_name ) ?: $user->display_name;
	$credit_phone = get_user_meta( $user->ID, 'billing_phone', true ) ?: $user->user_login;
}

?>
<svg class="hidden">
    <symbol id="comment_icon1" viewBox="0 0 43 46" fill="none" xmlns="http://www.w3.org/2000/svg">
        <mask id="path-1-inside-1_10452_1700" fill="white">
            <path fill-rule="evenodd" clip-rule="evenodd"
                d="M38.4567 34.3974C38.4384 34.1455 38.5168 33.8966 38.6712 33.6967C41.3886 30.1773 43 25.796 43 21.0468C43 9.43915 33.3741 0.0292969 21.5 0.0292969C9.62588 0.0292969 0 9.43915 0 21.0468C0 32.6544 9.62588 42.0643 21.5 42.0643C23.5687 42.0643 25.5692 41.7787 27.4625 41.2456C27.7013 41.1783 27.9567 41.1968 28.1808 41.3033L37.7951 45.8722C38.4875 46.2013 39.2774 45.6609 39.2217 44.8964L38.4567 34.3974Z" />
        </mask>
        <path fill-rule="evenodd" clip-rule="evenodd"
            d="M38.4567 34.3974C38.4384 34.1455 38.5168 33.8966 38.6712 33.6967C41.3886 30.1773 43 25.796 43 21.0468C43 9.43915 33.3741 0.0292969 21.5 0.0292969C9.62588 0.0292969 0 9.43915 0 21.0468C0 32.6544 9.62588 42.0643 21.5 42.0643C23.5687 42.0643 25.5692 41.7787 27.4625 41.2456C27.7013 41.1783 27.9567 41.1968 28.1808 41.3033L37.7951 45.8722C38.4875 46.2013 39.2774 45.6609 39.2217 44.8964L38.4567 34.3974Z"
            fill="white" />
        <path d="M27.4625 41.2456L27.598 41.7268L27.4625 41.2456ZM28.1808 41.3033L27.9662 41.7549L28.1808 41.3033ZM39.2217 44.8964L38.723 44.9327L39.2217 44.8964ZM38.6712 33.6967L38.2754 33.3912L38.6712 33.6967ZM38.4567 34.3974L38.9554 34.3611L38.4567 34.3974ZM42.5 21.0468C42.5 25.6807 40.9283 29.9554 38.2754 33.3912L39.0669 34.0023C41.849 30.3992 43.5 25.9114 43.5 21.0468H42.5ZM21.5 0.529297C33.1087 0.529297 42.5 9.72593 42.5 21.0468H43.5C43.5 9.15237 33.6395 -0.470703 21.5 -0.470703V0.529297ZM0.5 21.0468C0.5 9.72593 9.89126 0.529297 21.5 0.529297V-0.470703C9.3605 -0.470703 -0.5 9.15237 -0.5 21.0468H0.5ZM21.5 41.5643C9.89126 41.5643 0.5 32.3677 0.5 21.0468H-0.5C-0.5 32.9412 9.3605 42.5643 21.5 42.5643V41.5643ZM27.327 40.7643C25.4775 41.2851 23.5225 41.5643 21.5 41.5643V42.5643C23.6149 42.5643 25.661 42.2723 27.598 41.7268L27.327 40.7643ZM38.0097 45.4206L28.3954 40.8517L27.9662 41.7549L37.5805 46.3238L38.0097 45.4206ZM37.958 34.4337L38.723 44.9327L39.7203 44.86L38.9554 34.3611L37.958 34.4337ZM27.598 41.7268C27.7249 41.6911 27.8555 41.7023 27.9662 41.7549L28.3954 40.8517C28.0579 40.6912 27.6777 40.6655 27.327 40.7643L27.598 41.7268ZM37.5805 46.3238C38.619 46.8174 39.8039 46.0069 39.7203 44.86L38.723 44.9327C38.7508 45.315 38.3559 45.5852 38.0097 45.4206L37.5805 46.3238ZM38.2754 33.3912C38.0489 33.6846 37.9304 34.0543 37.958 34.4337L38.9554 34.3611C38.9463 34.2368 38.9848 34.1087 39.0669 34.0023L38.2754 33.3912Z"
            fill="#F21543" mask="url(#path-1-inside-1_10452_1700)" />
        <path d="M30 23.7953L29.253 23.8593C29.2702 24.0514 29.3608 24.2295 29.5058 24.3565C29.6509 24.4836 29.8394 24.5499 30.032 24.5417C30.2247 24.5334 30.4068 24.4513 30.5405 24.3123C30.6742 24.1733 30.7492 23.9882 30.75 23.7953L30 23.7953ZM12.764 21.9723L13.47 17.8923L11.991 17.6363L11.286 21.7163L12.764 21.9723ZM19.755 12.7793L24.404 12.7793L24.404 11.2793L19.755 11.2793L19.755 12.7793ZM25.315 13.6173L26.127 23.0093L27.622 22.8803L26.809 13.4873L25.315 13.6173ZM13.47 17.8923C13.977 14.9623 16.619 12.7793 19.755 12.7793L19.755 11.2793C15.929 11.2793 12.629 13.9483 11.991 17.6363L13.47 17.8923ZM19.745 28.9293L20.408 24.8843L18.928 24.6423L18.265 28.6863L19.745 28.9293ZM25.812 23.7833L24.373 25.0233L25.353 26.1603L26.792 24.9203L25.812 23.7833ZM21.756 29.0573L21.28 30.8913L22.732 31.2673L23.208 29.4343L21.756 29.0573ZM20.562 31.2513L20.417 31.2043L19.958 32.6323L20.103 32.6793L20.562 31.2513ZM22.477 27.2133C22.165 27.7973 21.922 28.4163 21.756 29.0573L23.208 29.4343C23.3458 28.908 23.5445 28.3996 23.8 27.9193L22.477 27.2133ZM20.417 31.2043C20.2745 31.1606 20.1453 31.0819 20.0412 30.9753C19.937 30.8688 19.8613 30.7377 19.821 30.5943L18.369 30.9703C18.4719 31.359 18.6716 31.7153 18.9495 32.0059C19.2273 32.2966 19.5743 32.5121 19.958 32.6323L20.417 31.2043ZM21.28 30.8913C21.2604 30.9635 21.2251 31.0304 21.1767 31.0875C21.1284 31.1445 21.068 31.1902 21 31.2213L21.651 32.5723C21.9153 32.4466 22.149 32.2647 22.3357 32.0393C22.5225 31.8139 22.6577 31.5504 22.732 31.2673L21.28 30.8913ZM21 31.2213C20.863 31.2863 20.7065 31.297 20.562 31.2513L20.103 32.6793C20.6137 32.8426 21.1676 32.8043 21.651 32.5723L21 31.2213ZM18.846 23.0453L13.666 23.0453L13.666 24.5453L18.846 24.5453L18.846 23.0453ZM28.281 12.6233L29.253 23.8593L30.747 23.7303L29.777 12.4943L28.281 12.6233ZM29.25 12.5163L29.25 23.7953L30.75 23.7953L30.75 12.5163L29.25 12.5163ZM29.777 12.4943C29.7801 12.5306 29.7745 12.5672 29.7627 12.6017C29.7508 12.6362 29.7319 12.6679 29.7072 12.6947C29.6825 12.7215 29.6524 12.7428 29.619 12.7574C29.5856 12.772 29.5495 12.7794 29.513 12.7793L29.513 11.2793C28.787 11.2793 28.219 11.9013 28.281 12.6233L29.777 12.4943ZM18.265 28.6863C18.14 29.4463 18.175 30.2243 18.369 30.9703L19.821 30.5933C19.681 30.0503 19.654 29.4833 19.745 28.9293L18.265 28.6863ZM24.404 12.7793C24.6331 12.7797 24.8538 12.866 25.0225 13.0211C25.1911 13.1763 25.2955 13.389 25.315 13.6173L26.809 13.4873C26.757 12.8851 26.4811 12.3243 26.0359 11.9156C25.5907 11.5068 25.0084 11.2798 24.404 11.2793L24.404 12.7793ZM24.373 25.0233C23.693 25.6093 22.961 26.3063 22.476 27.2133L23.8 27.9203C24.146 27.2713 24.697 26.7243 25.353 26.1603L24.373 25.0233ZM11.286 21.7163C11.2258 22.0633 11.2422 22.4193 11.334 22.7593C11.4259 23.0993 11.5909 23.4151 11.8177 23.6846C12.0444 23.9542 12.3273 24.1708 12.6466 24.3195C12.9659 24.4682 13.3138 24.5452 13.666 24.5453L13.666 23.0453C13.5325 23.0452 13.4005 23.016 13.2795 22.9596C13.1585 22.9031 13.0512 22.8209 12.9653 22.7187C12.8793 22.6165 12.8168 22.4967 12.782 22.3678C12.7473 22.2388 12.7411 22.1039 12.764 21.9723L11.286 21.7163ZM29.513 12.7793C29.4432 12.7793 29.3764 12.7516 29.327 12.7023C29.2777 12.6529 29.25 12.586 29.25 12.5163L30.75 12.5163C30.75 11.8343 30.197 11.2793 29.513 11.2793L29.513 12.7793ZM20.408 24.8843C20.4453 24.6577 20.4328 24.4257 20.3715 24.2045C20.3101 23.9832 20.2014 23.7779 20.0528 23.6028C19.9042 23.4278 19.7193 23.2871 19.5109 23.1907C19.3025 23.0943 19.0756 23.0453 18.846 23.0453L18.846 24.5453C18.896 24.5453 18.936 24.5903 18.928 24.6423L20.408 24.8843ZM26.127 23.0093C26.1392 23.1541 26.1179 23.2997 26.063 23.4343C26.0081 23.5688 25.9221 23.6884 25.812 23.7833L26.792 24.9203C27.0819 24.6702 27.3082 24.3548 27.4525 24.0002C27.5968 23.6456 27.6549 23.2617 27.622 22.8803L26.127 23.0093Z"
            fill="#F21543" />
    </symbol>
    <symbol id="comment_icon2" viewBox="0 0 43 46" fill="none" xmlns="http://www.w3.org/2000/svg">
        <mask id="path-1-inside-1_10452_1709" fill="white">
            <path fill-rule="evenodd" clip-rule="evenodd"
                d="M38.4561 34.4115C38.4378 34.1598 38.5162 33.9112 38.6703 33.7114C41.3883 30.1873 43 25.7999 43 21.0442C43 9.42179 33.3741 0 21.5 0C9.62588 0 0 9.42179 0 21.0442C0 32.6665 9.62588 42.0883 21.5 42.0883C23.568 42.0883 25.5679 41.8025 27.4605 41.2691C27.6996 41.2018 27.9552 41.2202 28.1795 41.327L37.7942 45.9019C38.4866 46.2314 39.2769 45.6911 39.2213 44.9264L38.4561 34.4115Z" />
        </mask>
        <path fill-rule="evenodd" clip-rule="evenodd"
            d="M38.4561 34.4115C38.4378 34.1598 38.5162 33.9112 38.6703 33.7114C41.3883 30.1873 43 25.7999 43 21.0442C43 9.42179 33.3741 0 21.5 0C9.62588 0 0 9.42179 0 21.0442C0 32.6665 9.62588 42.0883 21.5 42.0883C23.568 42.0883 25.5679 41.8025 27.4605 41.2691C27.6996 41.2018 27.9552 41.2202 28.1795 41.327L37.7942 45.9019C38.4866 46.2314 39.2769 45.6911 39.2213 44.9264L38.4561 34.4115Z"
            fill="white" />
        <path d="M27.4605 41.2691L27.3249 40.7879L27.4605 41.2691ZM28.1795 41.327L28.3943 40.8755L28.1795 41.327ZM37.7942 45.9019L37.5794 46.3534L37.7942 45.9019ZM39.2213 44.9264L39.7199 44.8901L39.2213 44.9264ZM38.6703 33.7114L39.0662 34.0167L38.6703 33.7114ZM38.4561 34.4115L38.9548 34.3752L38.4561 34.4115ZM42.5 21.0442C42.5 25.6848 40.9278 29.9656 38.2743 33.406L39.0662 34.0167C41.8487 30.4089 43.5 25.9151 43.5 21.0442H42.5ZM21.5 0.5C33.1081 0.5 42.5 9.70798 42.5 21.0442H43.5C43.5 9.13561 33.6401 -0.5 21.5 -0.5V0.5ZM0.5 21.0442C0.5 9.70798 9.89187 0.5 21.5 0.5V-0.5C9.35989 -0.5 -0.5 9.13561 -0.5 21.0442H0.5ZM21.5 41.5883C9.89187 41.5883 0.5 32.3804 0.5 21.0442H-0.5C-0.5 32.9527 9.35989 42.5883 21.5 42.5883V41.5883ZM27.3249 40.7879C25.476 41.309 23.5218 41.5883 21.5 41.5883V42.5883C23.6143 42.5883 25.6597 42.2961 27.5961 41.7504L27.3249 40.7879ZM38.0091 45.4504L28.3943 40.8755L27.9647 41.7785L37.5794 46.3534L38.0091 45.4504ZM37.9575 34.4478L38.7226 44.9627L39.7199 44.8901L38.9548 34.3752L37.9575 34.4478ZM27.5961 41.7504C27.7232 41.7146 27.854 41.7258 27.9647 41.7785L28.3943 40.8755C28.0565 40.7147 27.676 40.6889 27.3249 40.7879L27.5961 41.7504ZM37.5794 46.3534C38.618 46.8476 39.8034 46.0372 39.7199 44.8901L38.7226 44.9627C38.7504 45.345 38.3553 45.6152 38.0091 45.4504L37.5794 46.3534ZM38.2743 33.406C38.0481 33.6993 37.9299 34.0686 37.9575 34.4478L38.9548 34.3752C38.9458 34.251 38.9842 34.1231 39.0662 34.0167L38.2743 33.406Z"
            fill="#02C96F" mask="url(#path-1-inside-1_10452_1709)" />
        <path d="M13 17.234L13.747 17.17C13.7298 16.9779 13.6392 16.7998 13.4942 16.6728C13.3491 16.5457 13.1606 16.4794 12.968 16.4876C12.7753 16.4959 12.5932 16.578 12.4595 16.717C12.3258 16.856 12.2508 17.0411 12.25 17.234L13 17.234ZM30.236 19.057L29.53 23.137L31.009 23.393L31.714 19.313L30.236 19.057ZM23.245 28.25L18.596 28.25L18.596 29.75L23.245 29.75L23.245 28.25ZM17.685 27.412L16.873 18.02L15.378 18.149L16.191 27.542L17.685 27.412ZM29.53 23.137C29.023 26.067 26.381 28.25 23.245 28.25L23.245 29.75C27.071 29.75 30.371 27.081 31.009 23.393L29.53 23.137ZM23.255 12.1L22.592 16.145L24.072 16.387L24.735 12.343L23.255 12.1ZM17.188 17.246L18.627 16.006L17.647 14.869L16.208 16.109L17.188 17.246ZM21.244 11.972L21.72 10.138L20.268 9.762L19.792 11.595L21.244 11.972ZM22.438 9.778L22.583 9.825L23.042 8.397L22.897 8.35L22.438 9.778ZM20.523 13.816C20.835 13.232 21.078 12.613 21.244 11.972L19.792 11.595C19.6542 12.1213 19.4555 12.6297 19.2 13.11L20.523 13.816ZM22.583 9.825C22.7255 9.86866 22.8547 9.94738 22.9588 10.054C23.063 10.1605 23.1387 10.2916 23.179 10.435L24.631 10.059C24.5281 9.6703 24.3284 9.31401 24.0505 9.02337C23.7727 8.73274 23.4257 8.51724 23.042 8.397L22.583 9.825ZM21.72 10.138C21.7396 10.0658 21.7749 9.99885 21.8233 9.94183C21.8716 9.8848 21.932 9.83911 22 9.808L21.349 8.457C21.0847 8.58269 20.851 8.7646 20.6643 8.99001C20.4775 9.21542 20.3423 9.47889 20.268 9.762L21.72 10.138ZM22 9.808C22.137 9.74303 22.2935 9.73231 22.438 9.778L22.897 8.35C22.3863 8.18673 21.8324 8.22501 21.349 8.457L22 9.808ZM24.154 17.984L29.334 17.984L29.334 16.484L24.154 16.484L24.154 17.984ZM14.719 28.406L13.747 17.17L12.253 17.299L13.223 28.535L14.719 28.406ZM13.75 28.513L13.75 17.234L12.25 17.234L12.25 28.513L13.75 28.513ZM13.223 28.535C13.2199 28.4987 13.2255 28.4621 13.2373 28.4276C13.2492 28.3931 13.2681 28.3614 13.2928 28.3346C13.3175 28.3078 13.3476 28.2865 13.381 28.2719C13.4144 28.2573 13.4505 28.2499 13.487 28.25L13.487 29.75C14.213 29.75 14.781 29.128 14.719 28.406L13.223 28.535ZM24.735 12.343C24.86 11.583 24.825 10.805 24.631 10.059L23.179 10.436C23.319 10.979 23.346 11.546 23.255 12.1L24.735 12.343ZM18.596 28.25C18.3669 28.2496 18.1462 28.1633 17.9775 28.0082C17.8089 27.853 17.7045 27.6403 17.685 27.412L16.191 27.542C16.243 28.1442 16.5189 28.7049 16.9641 29.1137C17.4093 29.5225 17.9916 29.7495 18.596 29.75L18.596 28.25ZM18.627 16.006C19.307 15.42 20.039 14.723 20.524 13.816L19.2 13.109C18.854 13.758 18.303 14.305 17.647 14.869L18.627 16.006ZM31.714 19.313C31.7742 18.966 31.7578 18.61 31.666 18.27C31.5741 17.93 31.4091 17.6142 31.1823 17.3447C30.9556 17.0751 30.6727 16.8585 30.3534 16.7098C30.0341 16.5611 29.6862 16.4841 29.334 16.484L29.334 17.984C29.4675 17.9841 29.5995 18.0133 29.7205 18.0697C29.8415 18.1262 29.9488 18.2084 30.0347 18.3106C30.1207 18.4128 30.1832 18.5326 30.218 18.6615C30.2527 18.7904 30.2589 18.9254 30.236 19.057L31.714 19.313ZM13.487 28.25C13.5568 28.25 13.6236 28.2777 13.673 28.327C13.7223 28.3764 13.75 28.4432 13.75 28.513L12.25 28.513C12.25 29.195 12.803 29.75 13.487 29.75L13.487 28.25ZM22.592 16.145C22.5547 16.3716 22.5672 16.6036 22.6285 16.8248C22.6899 17.0461 22.7986 17.2514 22.9472 17.4265C23.0958 17.6015 23.2807 17.7421 23.4891 17.8386C23.6975 17.935 23.9244 17.984 24.154 17.984L24.154 16.484C24.104 16.484 24.064 16.439 24.072 16.387L22.592 16.145ZM16.873 18.02C16.8608 17.8752 16.8821 17.7296 16.937 17.595C16.9919 17.4605 17.0779 17.3409 17.188 17.246L16.208 16.109C15.9181 16.3591 15.6918 16.6745 15.5475 17.0291C15.4032 17.3837 15.3451 17.7676 15.378 18.149L16.873 18.02Z"
            fill="#02C96F" />
    </symbol>
</svg>
<section class="border-[#E8EDF1] lg:h-full lg:rounded-3xl lg:border lg:p-10">

    <?php if ( $show_credit_notification ) : ?>
    <div id="ez-credit-notification-card" role="button" tabindex="0" class="ez-credit-notification-card notification-card cursor-pointer relative flex gap-x-2 rounded-xlh border-2 border-red-500 bg-red-50 p-3 shadow-13 font-bold lg:items-center lg:p-4.5 mb-6 ring-2 ring-red-200">
        <?php if ( $credit_notification_is_new ) : ?><span id="ez-credit-notification-dot" class="absolute right-2 top-2 h-2.5 w-2.5 rounded-full bg-red-500 shadow-md"></span><?php endif; ?>
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-500/20 max-lg:mt-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <div class="lg:flex lg:w-full lg:justify-between lg:gap-x-1">
            <div>
                <div class="leading-normal text-16 text-red-800">اطلاعیه مهم: درگاه پرداخت اعتباری (اقساطی)</div>
                <div class="font-semibold text-red-700 text-12 line-clamp-1">برای مطالعه و درخواست غیرفعالسازی روی این اطلاعیه کلیک کنید.</div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (empty($user_products) || empty($active_products)) { ?>

        <div class="h-[464px] flex items-center justify-center text-3xl">
            شما در حال حاضر هیچ اتاق فعالی ندارید
        </div>

    <?php } else { ?>

        <div class="relative flex flex-col items-center justify-center">
            <div class="mb-3.5 text-xl font-bold">سانس‌های</div>

            <div class="flex items-end gap-2 mt-5">
                <button type="button" class="mb-2 product-title-carousel-prev-button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="8" height="12" viewBox="0 0 8 12"
                        fill="none">
                        <path d="M1 1L7 6L1 11" stroke="#0A192B" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </button>
                <div class="text-xl font-extrabold text-primary-500">
                    <div class="swiper product-title-carousel">
                        <div class="swiper-wrapper" style="max-width: 200px">
                            <?php foreach ($active_products as $user_product) {
                                $product = wc_get_product($user_product); ?>
                                <div class="text-center swiper-slide"
                                    data-id="<?php echo $product->get_id(); ?>">
                                    <?php echo wp_get_attachment_image(get_post_thumbnail_id($product->get_id()), 'large', false, [
                                        'class' => 'mx-auto mb-4 h-[45px] w-[36px] rounded-md object-cover lg:h-[93px] lg:w-[74px]',
                                    ]); ?>
                                    <?php echo $product->get_title(); ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <button type="button" class="mb-2 product-title-carousel-next-button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="8" height="12" viewBox="0 0 8 12"
                        fill="none">
                        <path d="M7 1L1 6L7 11" stroke="#0A192B" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="flex items-center py-2 mt-8 overflow-x-auto">

            <button type="button" data-datepicker="<?php echo esc_attr($current_date); ?>"
                class="flex flex-col items-center justify-center w-16 h-16 leading-none text-white border active shrink-0 gap-y-2 rounded-xl border-primary-700 bg-primary-500 lg:h-21">
                <span class="lg:text-[22px] lg:font-extrabold">
                    امروز
                </span>
            </button>

            <div class="swiper date-picker">
                <div class="swiper-wrapper">
                    <?php foreach ($dates as $index => $date) { ?>
                        <div class="swiper-slide" dir="ltr">
                            <button type="button" data-datepicker="<?php echo esc_attr($date); ?>"
                                class="flex h-16 w-16 shrink-0 flex-col items-center justify-center gap-y-2 rounded-xl border border-[#DBE2EA] bg-white leading-none lg:h-21">
                                <span class="lg:order-1 lg:text-[34px] lg:font-extrabold">
                                    <?php echo esc_html(jdate('d', $date)) ?>
                                </span>
                                <span class="lg:font-extrabold">
                                    <?php echo esc_html(jdate('l', $date)) ?>
                                </span>
                            </button>
                        </div>
                    <?php } ?>
                </div>
            </div>

        </div>

        <div id="sans" class="mt-8 grid grid-cols-2 gap-x-4.5 gap-y-8 lg:grid-cols-4 lg:gap-x-12.5">
        </div>

        <!-- Modal for user info and cancellation -->
        <div id="userInfoModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 items-center justify-center">
            <div class="bg-white rounded-2xl p-6 w-88 max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
                <div id="userInfoContent" class="space-y-4">
                    <!-- User info will be populated here -->
                </div>
                <div class="w-full h-[1px] bg-[#E4EBF0] my-4"></div>

                <!-- Regular cancellation button (shown when no cancellation request) -->
                <button id="cancelSansBtn" class="w-full flex justify-center items-center text-[#F21543] gap-1 bg-[#EDF2F5] text-center rounded-sm">
                    لغوسانس
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="10" viewBox="0 0 11 10" fill="none" class="mx-0">
                        <path d="M8.16016 1.62793C8.25364 1.62796 8.34623 1.64591 8.43262 1.68164C8.51915 1.71749 8.59783 1.77068 8.66406 1.83691C8.73018 1.90306 8.78351 1.98099 8.81934 2.06738C8.85518 2.15392 8.87305 2.24715 8.87305 2.34082C8.87304 2.43428 8.85503 2.52691 8.81934 2.61328C8.78351 2.69978 8.73025 2.77851 8.66406 2.84473L6.50977 4.99902L8.66406 7.1543C8.79782 7.28806 8.87305 7.47001 8.87305 7.65918C8.87299 7.84827 8.79778 8.02937 8.66406 8.16309C8.53035 8.2968 8.34925 8.37202 8.16016 8.37207C7.97099 8.37207 7.78903 8.29685 7.65527 8.16309L5.5 6.00879L3.3457 8.16309C3.21196 8.29678 3.03091 8.37206 2.8418 8.37207C2.65268 8.37207 2.47164 8.29677 2.33789 8.16309C2.20418 8.02937 2.12896 7.84827 2.12891 7.65918C2.12891 7.47001 2.20413 7.28806 2.33789 7.1543L4.49121 4.99902L2.33789 2.84473H2.33691C2.20354 2.71097 2.12891 2.52973 2.12891 2.34082C2.12891 2.15192 2.20355 1.97068 2.33691 1.83691L2.33789 1.83594C2.47165 1.70257 2.65289 1.62793 2.8418 1.62793C3.0307 1.62794 3.21194 1.70256 3.3457 1.83594V1.83691L5.5 3.99023L7.65527 1.83691C7.72146 1.77073 7.80025 1.71748 7.88672 1.68164C7.97325 1.6458 8.06649 1.62793 8.16016 1.62793Z" fill="#F21543" stroke="#F21543" stroke-width="0.2"></path>
                    </svg>
                </button>

                <!-- Cancellation request handling (shown when data-reqid exists) -->
                <div id="cancellationRequestHandling" class="hidden">
                    <p class="text-sm font-bold text-[#69737F] text-center">
                        پلیر درخواست لغو این سانس را ثبت کرده است. آیا با لغو موافقت می‌کنید؟
                    </p>
                    <div class="flex gap-3 mt-4">
                        <button id="rejectCancellationBtn" class="flex-1 bg-slate-100 text-[#9AA8B7] py-2 px-4 rounded-[10px] font-bold text-center">
                            رد کردن
                        </button>
                        <button id="approveCancellationBtn" class="flex-1 bg-primary-500 hover:bg-primary-600 text-white py-2 px-4 rounded-[10px] font-bold text-center">
                            تایید و لغو سانس
                        </button>
                    </div>
                </div>

                <!-- Cancellation Reason Dropdown -->
                <div id="cancellationReasonDropdown" class="mt-4 overflow-hidden transition-all duration-300 ease-in-out opacity-0" style="max-height: 0;">
                    <div class="p-4">
                        <form id="cancellationForm">
                            <p class="text-base font-bold mt-3">چرا می خواهید سانس را لغو کنید؟ </p>

                            <p class="text-sm font-bold text-[#889BAD] mt-1">لطفاً یک گزینه را انتخاب کنید:</p>

                            <div class="w-full h-[1px] bg-[#E4EBF0] my-4"></div>

                            <div class="space-y-4 mb-4">

                                <?php
                                foreach ( cancellation_reasons() as $key => $reason ) : ?>

                                    <label class="flex gap-3 cursor-pointer">
                                        <input type="radio" name="reason_id" value="<?php echo $key ?>" class="hidden">
                                        <div class="w-5 h-5 border-2 border-gray-300 rounded-full flex items-center justify-center transition-all duration-200 radio-checkbox">
                                            <svg class="w-3 h-3 text-white opacity-0 transition-opacity duration-200" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                        <p class="text-sm font-bold"><?php echo $reason ?></p>
                                    </label>

                                <?php
                                endforeach; ?>

                            </div>

                            <div class="flex gap-2 mb-6">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" class="mx-0 shrink-0">
                                    <path opacity="0.4" d="M8.35815 3.04953C9.07148 1.75953 10.9256 1.75953 11.639 3.04953L18.094 14.717C18.7856 15.967 17.8815 17.5004 16.4531 17.5004H3.54482C2.11565 17.5004 1.21148 15.967 1.90315 14.717L8.35815 3.04953Z" fill="#EFC101"></path>
                                    <path d="M10.775 13.8404C10.8152 13.9444 10.8341 14.0555 10.8306 14.167C10.8238 14.3831 10.7331 14.5881 10.5778 14.7386C10.4225 14.889 10.2147 14.9732 9.99852 14.9732C9.78228 14.9732 9.57453 14.889 9.41924 14.7386C9.26394 14.5881 9.17327 14.3831 9.16643 14.167C9.1629 14.0555 9.18182 13.9444 9.22204 13.8404C9.26227 13.7364 9.323 13.6415 9.40061 13.5614C9.47823 13.4813 9.57115 13.4176 9.67386 13.3741C9.77657 13.3306 9.88697 13.3081 9.99852 13.3081C10.1101 13.3081 10.2205 13.3306 10.3232 13.3741C10.4259 13.4176 10.5188 13.4813 10.5964 13.5614C10.674 13.6415 10.7348 13.7364 10.775 13.8404Z" fill="#09192D"></path>
                                    <path d="M10.3899 7.22305C10.5128 7.32298 10.5932 7.46587 10.6148 7.62282L10.6206 7.70699L10.6239 11.4587C10.6241 11.6171 10.5641 11.7697 10.456 11.8855C10.348 12.0014 10.2 12.0719 10.0419 12.0828C9.8839 12.0937 9.72761 12.0442 9.60468 11.9443C9.48175 11.8443 9.40136 11.7014 9.37977 11.5445L9.37393 11.4595L9.3706 7.70865C9.37044 7.55023 9.43044 7.39765 9.53848 7.28178C9.64652 7.16591 9.79453 7.09539 9.95258 7.08449C10.1106 7.07359 10.2669 7.12311 10.3899 7.22305Z" fill="#09192D"></path>
                                </svg>

                                <p class="text-sm font-bold text-[#BF9A00]">مجموعه‌دار عزیز، در صورت لغو کمتر از ۲۴ ساعت پیش از سانس، امتیاز و عملکرد برند شما تحت تأثیر قرار می‌گیرد.</p>
                            </div>

                            <button type="submit" class="bg-primary-500 rounded-lg h-[48px] w-full shadow-[0_2px_0_0_#CA5608] text-white">
                                ثبت درخواست
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <hr class="mt-10 mb-7.5">
        <div id="comment_order" class="space-y-4"></div>
    <?php } ?>

</section>
<?php
date_default_timezone_set('Asia/Tehran');
$thisTime = intval(time());
$product_id = sanitize_text_field($active_products);
?>
<script>
    jQuery(document).ready(function($) {
        const BuildSans = (room, day) => {
            $.ajax({
                type: 'POST',
                url: "<?php echo site_url('web-service/reservation.php') ?>",
                data: {
                    "type": "sans_management_web",
                    "data": {
                        "day_start_time": day,
                        "product_id": room
                    }
                },
                beforeSend: function() {
                    $(`[data-datepicker="${day}"]`).attr('disabled', 'disabled')
                    let out = ""
                    for (let i = 0; i < 8; i++) {
                        out +=
                            "<div class='w-full h-29 skeleton rounded-xl'></div>"
                    }

                    $("#sans").html(out)
                },
                success: function(response) {
                    $(`[data-datepicker="${day}"]`).removeAttr('disabled')
                    $("#sans").html(response)
                }
            })
        }
        BuildSans("<?php echo $active_products[0] ?>", "<?php echo $current_date; ?>")
        new Swiper('.date-picker', {
            slidesPerView: 4.5,
            freeMode: true,
            breakpoints: {
                540: {
                    slidesPerView: 5.5,
                },
                650: {
                    slidesPerView: 6.5,
                },
                1280: {
                    slidesPerView: 9.6,
                },
            },
        })

        $("body")
            .on('click', "[data-datepicker]", function() {
                $("[data-datepicker]").removeClass(
                    'active border-primary-700 bg-primary-500 text-white').addClass(
                    'border-[#DBE2EA] bg-white')
                $(this).removeClass('border-[#DBE2EA] bg-white').addClass(
                    'active border-primary-700 bg-primary-500 text-white')
                let date = $(this).data('datepicker')

                setTimeout(() => {
                    let id = $(".swiper-slide-active").data('id')
                    BuildSans(id, date)
                }, 5)
            })
            .on('click', "[data-room-action]", function() {
                let _ = $(this),
                    action = _.data('room-action'),
                    product = _.data('product'),
                    currentDate = _.data('timestamp').split('.')[1],
                    time = _.data('timestamp').split('.')[0]
                $.ajax({
                    type: 'POST',
                    url: "<?php echo site_url('web-service/reservation.php') ?>",
                    data: {
                        "type": `${action}_sans`,
                        "data": {
                            "sans_time": parseInt(time),
                            "product_id": parseInt(product)
                        }
                    },
                    beforeSend: function() {
                        _.attr('disabled', 'disabled')
                        _.html(
                            "<div class='spinner' style='margin: 11px auto 0;width: 16px;border: 2px solid rgba(127 127 127 / 50%);display: inline-flex;'></div>"
                        )
                    },
                    success: function() {
                        BuildSans(product, currentDate)
                    }
                })
            })
            .on('submit', ".comment_form", function(e) {
                e.preventDefault();
                let canSendMessage = false;
                let sectionID = $(this).attr('data-id');
                let commentMessage = $(this).find('textarea[name="comment_message"]').val();
                let voteValue = $(this).find('input[name="vote"]:checked').val();
                let user_id = $(this).find('input[name="user_id"]').val();
                let order_id = $(this).find('input[name="order_id"]').val();
                let room_id = $(this).find('input[name="room_id"]').val();
                let button = $(this).find('button[type="submit"]');
                let errorForm = $(this).find('#comment_error');
                let showError = function(message) {
                    errorForm.empty();
                    errorForm.text(message);
                }
                if (!commentMessage.trim()) {
                    showError('متن کامنت ضروری است.')
                    return;
                }
                if (!voteValue) {
                    showError('لایک و دیس لایک ضروری است.');
                    return;
                }
                errorForm.empty();
                button.attr('disabled', 'disabled');
                button.html(
                    "<span class='spinner' style='width: 16px;border: 2px solid rgba(127 127 127 / 50%);display: inline-flex;'></span><span>لطفا منتظر بمانید</span>"
                );
                let dataPost = {
                    user_id,
                    order_id,
                    commentMessage,
                    voteValue,
                    room_id
                }
                let dataCheck = {
                    order_id,
                    room_id
                }
                $.ajax({
                    type: "POST",
                    url: "<?php echo admin_url('admin-ajax.php') ?>",
                    data: {
                        'action': 'v2_ajax_handler',
                        'nonce': "<?php echo wp_create_nonce('v2-ajax-nonce') ?>",
                        'callback': 'check_comment_form',
                        data: dataCheck
                    },
                    success: function(response) {
                        if (response.data === 'success') {
                            $.ajax({
                                type: "POST",
                                url: "<?php echo admin_url('admin-ajax.php') ?>",
                                data: {
                                    action: 'v2_ajax_handler',
                                    nonce: "<?php echo wp_create_nonce('v2-ajax-nonce') ?>",
                                    callback: 'post_order_comment',
                                    data: dataPost
                                },
                                success: function(response) {
                                    $(`#${sectionID}`).remove();
                                    Swal.fire({
                                        position: "bottom-start",
                                        icon: "success",
                                        text: 'نظر شما با موفقیت ثبت شد.',
                                        showConfirmButton: false,
                                        timer: 2000
                                    });
                                },
                                error: function(xhr, status, error) {
                                    console.error('AJAX Error:',
                                        error);
                                }
                            });

                        } else if (response.data === 'error') {
                            $(`#${sectionID}`).remove();
                            Swal.fire({
                                position: "bottom-start",
                                icon: "error",
                                title: 'خطا',
                                text: 'زمان ارسال کامنت به اتمام رسیده است.',
                                showConfirmButton: false,
                                timer: 2000
                            });

                        } else {
                            Swal.fire({
                                position: "bottom-start",
                                icon: "error",
                                title: 'خطا',
                                text: 'خطایی رخ داده است.',
                                showConfirmButton: false,
                                timer: 2000
                            });
                        }

                    },
                    error: function(xhr, status, error) {
                        console.log('AJAX Error:', error);
                    }
                });

            })

        const ProductsTitleCarousel = new Swiper('.product-title-carousel', {
            slidesPerView: 1,
            navigation: {
                nextEl: ".product-title-carousel-next-button",
                prevEl: ".product-title-carousel-prev-button",
            },
        })

        ProductsTitleCarousel.on('slideChange', function() {
            $("[data-datepicker]").get(0).click()
        })

        function BuildComments() {
            $.ajax({
                type: 'POST',
                url: "<?php echo site_url('web-service/comments-order.php') ?>",
                data: {
                    "type": "comments_order",
                    "data": {
                        "time": <?= $thisTime ?>,
                        "product_id": <?= json_encode($active_products) ?>
                    }
                },
                success: function(response) {
                    if (response !== 'null') {
                        let data = typeof response === 'string' ? JSON.parse(
                            response) : response;
                        if (Array.isArray(data)) {
                            $.ajax({
                                type: "POST",
                                url: "<?php echo admin_url('admin-ajax.php') ?>",
                                data: {
                                    'action': 'v2_ajax_handler',
                                    'nonce': "<?php echo wp_create_nonce('v2-ajax-nonce') ?>",
                                    'callback': 'get_order_comment_statuses',
                                    data: data
                                },
                                success: function(response) {
                                    if (response.success &&
                                        response.data) {
                                        $('#comment_order').empty();
                                        response.data.forEach(function(
                                            order) {
                                            if (order
                                                .has_comment) {
                                                return;
                                            }
                                            let userLevel =
                                                order
                                                .user_level;
                                            let userLevelText;
                                            let userColor;
                                            let userBackground;
                                            switch (userLevel) {
                                                case 1:
                                                    userLevelText
                                                        =
                                                        'تازه وارد';
                                                    userColor =
                                                        '#959798';
                                                    userBackground
                                                        =
                                                        '#2527281A';
                                                    break;
                                                case 2:
                                                    userLevelText
                                                        =
                                                        'نوپا';
                                                    userColor =
                                                        '#049654';
                                                    userBackground
                                                        =
                                                        '#02C96F4D';
                                                    break;
                                                case 3:
                                                    userLevelText
                                                        =
                                                        'با تجربه';
                                                    userColor =
                                                        '#3F7FF5';
                                                    userBackground
                                                        =
                                                        '#5091FB4D';
                                                    break;
                                                case 4:
                                                    userLevelText
                                                        =
                                                        'کارکشته';
                                                    userColor =
                                                        '#FD7013';
                                                    userBackground
                                                        =
                                                        '#FD701338';
                                                    break;
                                                default:
                                                    userLevelText
                                                        =
                                                        'تازه وارد';
                                                    userColor =
                                                        '#959798';
                                                    userBackground
                                                        =
                                                        '#2527281A';
                                            }
                                            let form = `
                                        <div class="border p-7.5 max-lg:p-5 rounded-2xl" id="order_id_${order.order_id}"><div class="grid grid-cols-12 max-lg:gap-y-3.5"><div class="flex items-center justify-start col-span-4 text-base font-semibold max-lg:col-span-8 max-lg:order-1"><img class="h-[42px] w-[34px] rounded-md ml-5" src="${order.product_image}" /><span class="text-text-3 ml-1.5">${order.product_type}</span><span class="text-black">${order.product_title}</span></div><div class="flex items-center justify-start col-span-5 font-semibold max-lg:col-span-9 max-lg:order-3 "><span class="text-text-3 ml-1.5 text-base">توسط</span><span class="ml-5 text-base text-black max-lg:ml-1">${order.user_name}</span><span style="font-size: 12px; font-weight: 800;"><span class="flex items-center gap-2 px-3 leading-6 rounded-full" style="color:${userColor}; background:${userBackground}">${userLevelText}</span></span></div><div class="flex items-center justify-center col-span-1 text-base font-semibold text-black max-lg:col-span-3 max-lg:order-4 max-lg:justify-end ">${order.order_quantity} بلیت</div><div class="flex items-center justify-end col-span-2 text-lg text-black max-lg:col-span-4 max-lg:order-2 max-lg:text-base" style="font-weight: 800;"><span class="bg-accent-450/10 text-accent-950 py-2.5 px-3.5 leading-3 rounded-lg max-sm:text-xs">در حال بازی</span></div></div><hr class="my-5"><form method="post" class="comment_form grid grid-cols-12 items-end gap-9.5 max-lg:gap-0" data-id="order_id_${order.order_id}"><input type="hidden" name="user_id" value="${order.customer_id}" /><input type="hidden" name="order_id" value="${order.order_id}" /><input type="hidden" name="room_id" value="${order.room_id}" /><div class="relative col-span-7 max-lg:col-span-12 max-lg:mb-5"><label class="flex w-full"><textarea name="comment_message" class="border border-13 shadow-13 rounded-[14px] w-full px-7.5 py-7 text-base h-[122px]" rows="4" placeholder="دیدگاه خود در مورد این پلیر را بنویسید..."></textarea></label><p class="absolute" style="color:red;font-size:12px" id="comment_error"></p></div><div class="flex flex-col col-span-5 gap-8 max-lg:col-span-12"><div class="flex items-center justify-evenly"><label class="cursor-pointer"><input type="radio" name="vote" value="like" class="hidden [&:checked+span]:grayscale-0 [&:hover+span]:grayscale-0 [&:checked+span]:text-black [&:hover+span]:text-black"><span class="flex items-center transition-all duration-300 grayscale text-text-3"><svg class="w-12 h-12 ml-3" style="filter: drop-shadow(8px 18px 16px rgba(78, 94, 108, 0.08));"><use href="#comment_icon2"></use></svg>راضی</span></label><label class="cursor-pointer"><input type="radio" name="vote" value="dislike" class="hidden [&:checked+span]:grayscale-0 [&:hover+span]:grayscale-0 [&:checked+span]:text-black [&:hover+span]:text-black"><span class="flex items-center transition-all duration-300 grayscale text-text-3"><svg class="w-12 h-12 ml-3" style="filter: drop-shadow(8px 18px 16px rgba(78, 94, 108, 0.08));"><use href="#comment_icon1"></use></svg>ناراضی</span></label></div><button type="submit" class="flex gap-4 items-center justify-center relative font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 transition-all duration-300 ease-in-out disabled:bg-slate-110 disabled:text-[#ccc] disabled:cursor-not-allowed disabled:shadow-none w-full bg-accent-450 text-white shadow-16 hover:bg-accent-500 focus-visible:outline-accent-500 h-[47px] min-w-16 px-9 py-2 rounded-[10px] text-lg" style="font-weight: 800;">ارســـال دیدگــاه</button></div></form></div>
`;

                                            $('#comment_order')
                                                .append(form);
                                        })
                                    }
                                }
                            })
                        }
                    }
                },
                error: function(xhr, status,
                    error) {
                    console.log('AJAX Error:',
                        error);
                }
            });
        }
        BuildComments();

        // Cancellation functionality
        let currentOrderData = null;

        // Helper function to convert hex to RGB
        function hexToRgb(hex) {
            const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
            return result ? {
                r: parseInt(result[1], 16),
                g: parseInt(result[2], 16),
                b: parseInt(result[3], 16)
            } : null;
        }

        // Handle eye button click to show user info modal
        $('body').on('click', '[data-order-id]', function() {
            const orderData = {
                customer_id: $(this).data('customer-id'),
                phone: $(this).data('phone'),
                quantity: $(this).data('quantity'),
                order_id: $(this).data('order-id'),
                level_color: $(this).data('level-color'),
                level_text: $(this).data('level-text'),
                name: $(this).data('name'),
                time: $(this).data('time'),
                booked_time: $(this).data('booked-time'),
                reqid: $(this).data('reqid') // Add cancellation request ID
            };

            currentOrderData = orderData;

            // Convert hex color to RGB with opacity
            let backgroundColor = orderData.level_color;
            if (backgroundColor.startsWith('[#') && backgroundColor.endsWith(']')) {
                const hexColor = backgroundColor.slice(2, -1); // Remove [ and ]
                const rgb = hexToRgb(hexColor);
                backgroundColor = `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, 0.2)`;
            } else if (backgroundColor === 'primary-500') {
                // Fallback for primary-500 (orange color)
                backgroundColor = 'rgba(253, 112, 19, 0.2)';
            }

            // Populate user info modal
            const userInfoHtml = `
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <div class="flex gap-3 items-center">
                            <p class="text-base font-bold">${orderData.name}</p>
                            <div class="rounded-[24px] h-5.5 px-2.5 leading-none content-center text-2xs text-center text-${orderData.level_color}" style="background-color: ${backgroundColor}">
                                ${orderData.level_text}
                            </div>
                        </div>
                        <a class="text-base font-extrabold text-[#62748E]" href="tel:${window.formatPhoneForTel(orderData.phone)}">${orderData.phone}</a>
                    </div>

                    <div class="grid grid-cols-2 items-center gap-x-3.5">
                        <a href="${
                            (location.hostname === 'localhost')
                                ? `http://localhost/escapezoom_wp/profile/${orderData.customer_id}`
                                : `https://escapezoom.ir/profile/${orderData.customer_id}`
                            }" class="flex w-full text-sm gap-2 rounded-md bg-[#F3F4F6] py-1 px-4 h-8 items-center justify-center gap-2" target="_blank" rel="noopener" target="_blank">
                                <p class="text-sm font-bold text-[#90A1B9]">مشاهده پروفایل</p>
                                <svg xmlns="http://www.w3.org/2000/svg" class="mx-0 w-4 h-4" viewBox="0 0 19 19" fill="none">
                                    <rect x="0.75" y="0.5" width="18" height="18" rx="4" fill="#FD7013"></rect>
                                    <path d="M15.1897 8.95604C15.363 9.19883 15.4496 9.3208 15.4496 9.50033C15.4496 9.68043 15.363 9.80183 15.1897 10.0446C14.4112 11.1366 12.4227 13.4899 9.7502 13.4899C7.07717 13.4899 5.08921 11.1361 4.31067 10.0446C4.13741 9.80183 4.05078 9.67986 4.05078 9.50033C4.05078 9.32023 4.13741 9.19883 4.31067 8.95604C5.08921 7.86403 7.07774 5.51074 9.7502 5.51074C12.4232 5.51074 14.4112 7.8646 15.1897 8.95604Z" stroke="white" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M11.4607 9.49986C11.4607 9.04639 11.2805 8.61149 10.9599 8.29083C10.6392 7.97018 10.2043 7.79004 9.75084 7.79004C9.29737 7.79004 8.86247 7.97018 8.54181 8.29083C8.22116 8.61149 8.04102 9.04639 8.04102 9.49986C8.04102 9.95334 8.22116 10.3882 8.54181 10.7089C8.86247 11.0295 9.29737 11.2097 9.75084 11.2097C10.2043 11.2097 10.6392 11.0295 10.9599 10.7089C11.2805 10.3882 11.4607 9.95334 11.4607 9.49986Z" stroke="white" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                        </a>
                        <a href="tel:${window.formatPhoneForTel(orderData.phone)}" class="flex gap-2 rounded-md bg-[#02C96F] py-1   px-4 h-8 items-center justify-center gap-2 w-full text-sm">
                            <p class="text-sm font-bold text-white">تماس با پلیر</p>
                            <svg class="mx-0 w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 25 25" fill="none">
                                <g filter="url(#filter0_d_45299_14979)">
                                    <path d="M17.0705 16.4999C16.3733 16.4999 15.3938 16.2477 13.927 15.4284C12.1435 14.4284 10.7639 13.5052 8.98996 11.7362C7.27961 10.0273 6.4473 8.92084 5.28242 6.80153C3.96643 4.40864 4.19077 3.15434 4.44153 2.61827C4.74016 1.97754 5.18097 1.59432 5.75073 1.21396C6.07435 1.00197 6.41682 0.82025 6.77379 0.671099C6.80952 0.655742 6.84274 0.641099 6.87239 0.627884C7.04921 0.548241 7.31712 0.427882 7.65648 0.556455C7.88295 0.641456 8.08513 0.815387 8.40163 1.12789C9.05069 1.7679 9.93766 3.19327 10.2649 3.89328C10.4846 4.36507 10.6299 4.6765 10.6303 5.02579C10.6303 5.43473 10.4245 5.75009 10.1748 6.09045C10.1281 6.15438 10.0816 6.21545 10.0366 6.27474C9.76476 6.63189 9.70511 6.7351 9.7444 6.91939C9.82406 7.28975 10.4181 8.39226 11.3944 9.3662C12.3707 10.3401 13.4416 10.8966 13.8134 10.9759C14.0056 11.0169 14.111 10.9548 14.4797 10.6734C14.5325 10.633 14.5868 10.5912 14.6436 10.5494C15.0244 10.2662 15.3252 10.0659 15.7246 10.0659H15.7267C16.0743 10.0659 16.3718 10.2166 16.8648 10.4651C17.5078 10.7894 18.9763 11.6648 19.6203 12.3145C19.9336 12.6302 20.1083 12.8316 20.1937 13.0577C20.3223 13.398 20.2012 13.6648 20.1222 13.8434C20.109 13.873 20.0944 13.9055 20.079 13.9416C19.9287 14.2979 19.7458 14.6396 19.5328 14.9623C19.1531 15.5302 18.7684 15.9699 18.1261 16.2688C17.7963 16.4248 17.4354 16.5038 17.0705 16.4999Z" fill="white"></path>
                                </g>
                                <defs>
                                    <filter id="filter0_d_45299_14979" x="0.25" y="0.5" width="24" height="24" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                        <feFlood flood-opacity="0" result="BackgroundImageFix"></feFlood>
                                        <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"></feColorMatrix>
                                        <feOffset dy="4"></feOffset>
                                        <feGaussianBlur stdDeviation="2"></feGaussianBlur>
                                        <feComposite in2="hardAlpha" operator="out"></feComposite>
                                        <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0"></feColorMatrix>
                                        <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_45299_14979"></feBlend>
                                        <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_45299_14979" result="shape"></feBlend>
                                    </filter>
                                </defs>
                            </svg>
                        </a>
                    </div>

                    <div class="w-full h-[1px] bg-[#E4EBF0] my-4"></div>

                    <div class="flex justify-between">
                        <div class="flex gap-2">
                            <p class="text-md font-bold text-[#889BAD]">کد رزرو</p>
                            <p class="text-md font-bold">${orderData.order_id}</p>
                        </div>

                        <div class="flex gap-2">
                            <p class="text-md font-bold text-[#889BAD]">تعداد</p>
                            <p class="text-md font-bold">${orderData.quantity} بلیت</p>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <p class="text-md font-bold text-[#889BAD]">تاریخ رزرو</p>
                        <p class="text-md font-bold">
                            ${(() => {
                                if (!orderData.booked_time) return '';
                                return window.formatToJalaliDateTime(orderData.booked_time);
                            })()}
                        </p>
                    </div>
                </div>
            `;

            $('#userInfoContent').html(userInfoHtml);

            // Show/hide buttons based on cancellation request status
            if (orderData.reqid) {
                // Show cancellation request handling buttons
                $('#cancelSansBtn').addClass('hidden');
                $('#cancellationRequestHandling').removeClass('hidden');
            } else {
                // Show regular cancellation button
                $('#cancelSansBtn').removeClass('hidden');
                $('#cancellationRequestHandling').addClass('hidden');
            }

            $('#userInfoModal').removeClass('hidden').addClass('flex');
        });

        // Close user info modal
        $('#closeUserInfoModal').on('click', function() {
            // Close dropdown if open
            const dropdown = $('#cancellationReasonDropdown');
            const icon = $('#dropdownIcon');
            dropdown.removeClass('opacity-100').addClass('opacity-0');
            dropdown.css('max-height', '0');
            icon.removeClass('rotate-180');
            $('#cancellationForm')[0].reset();

            // Reset button states
            $('#cancelSansBtn').removeClass('hidden');
            $('#cancellationRequestHandling').addClass('hidden');

            $('#userInfoModal').addClass('hidden').removeClass('flex');
        });

        // Handle cancel sans button click
        $('#cancelSansBtn').on('click', function() {
            const dropdown = $('#cancellationReasonDropdown');
            const icon = $('#dropdownIcon');
            const isOpen = !dropdown.hasClass('opacity-0');

            if (isOpen) {
                // Close dropdown
                dropdown.removeClass('opacity-100').addClass('opacity-0');
                dropdown.css('max-height', '0');
                icon.removeClass('rotate-180');
            } else {
                // Open dropdown
                dropdown.removeClass('opacity-0').addClass('opacity-100');
                dropdown.css('max-height', '500px');
                icon.addClass('rotate-180');
            }
        });

        // Close cancellation dropdown
        $('#cancelReasonBtn').on('click', function() {
            const dropdown = $('#cancellationReasonDropdown');
            const icon = $('#dropdownIcon');
            dropdown.removeClass('opacity-100').addClass('opacity-0');
            dropdown.css('max-height', '0');
            icon.removeClass('rotate-180');
            // Reset form
            $('#cancellationForm')[0].reset();
        });

        // Handle cancellation form submission
        $('#cancellationForm').on('submit', function(e) {
            e.preventDefault();

            if (!currentOrderData) {
                Swal.fire({
                    position: "bottom-start",
                    icon: "error",
                    text: 'خطا در دریافت اطلاعات سفارش',
                    showConfirmButton: false,
                    timer: 2000
                });
                return;
            }

            const reasonId = $('input[name="reason_id"]:checked').val();
            if (!reasonId) {
                Swal.fire({
                    title: 'خطا',
                    text: 'انتخاب یک گزینه ضروری است. لطفاً دلیل لغو سانس را انتخاب کنید.',
                    icon: 'error',
                    confirmButtonText: 'متوجه شدم',
                    confirmButtonColor: '#d33'
                });
                return;
            }

            // Show confirmation dialog
            Swal.fire({
                title: 'آیا مطمئن هستید؟',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'بله، لغو کن',
                cancelButtonText: 'انصراف',
                customClass: {
                    popup: 'swal2-smaller-popup',
                    title: 'swal2-smaller-title',
                    htmlContainer: 'swal2-smaller-html',
                    actions: 'swal2-smaller-actions',
                    confirmButton: 'swal2-smaller-confirm',
                    cancelButton: 'swal2-smaller-cancel'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Submit cancellation request
                    $.ajax({
                        type: 'POST',
                        url: "<?php echo admin_url('admin-ajax.php') ?>",
                        data: {
                            'action': 'team_ajax_handler',
                            'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                            'callback': 'cancellation_actions',
                            'function': 'create_cancellation_request',
                            'order_id': currentOrderData.order_id,
                            'reason_id': reasonId,
                            'requester_type': 'owner'
                        },
                        beforeSend: function() {
                            Swal.fire({
                                title: 'در حال ارسال...',
                                text: 'لطفاً منتظر بمانید',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                        },
                        success: function(response) {
                            // Close dropdown
                            const dropdown = $('#cancellationReasonDropdown');
                            const icon = $('#dropdownIcon');
                            dropdown.removeClass('opacity-100').addClass('opacity-0');
                            dropdown.css('max-height', '0');
                            icon.removeClass('rotate-180');
                            $('#cancellationForm')[0].reset();

                            if (response.success) {
                                Swal.fire({
                                    position: "bottom-start",
                                    icon: "success",
                                    text: response.data || 'درخواست کنسلی با موفقیت ثبت شد',
                                    showConfirmButton: false,
                                    timer: 2000
                                });

                                // Refresh the sans display
                                let date = $("[data-datepicker].active").data('datepicker');
                                let id = $(".swiper-slide-active").data('id');
                                if (date && id) {
                                    BuildSans(id, date);
                                }
                            } else {
                                Swal.fire({
                                    position: "bottom-start",
                                    icon: "error",
                                    text: response.data || 'خطا در ثبت درخواست کنسلی',
                                    showConfirmButton: false,
                                    timer: 2000
                                });
                            }
                        },
                        error: function() {
                            // Close dropdown
                            const dropdown = $('#cancellationReasonDropdown');
                            const icon = $('#dropdownIcon');
                            dropdown.removeClass('opacity-100').addClass('opacity-0');
                            dropdown.css('max-height', '0');
                            icon.removeClass('rotate-180');
                            Swal.fire({
                                position: "bottom-start",
                                icon: "error",
                                text: 'خطا در ارتباط با سرور',
                                showConfirmButton: false,
                                timer: 2000
                            });
                        }
                    });
                }
            });
        });

        // Handle approve cancellation request
        $('#approveCancellationBtn').on('click', function() {
            if (!currentOrderData || !currentOrderData.reqid) {
                Swal.fire({
                    position: "bottom-start",
                    icon: "error",
                    text: 'خطا در دریافت اطلاعات درخواست لغو',
                    showConfirmButton: false,
                    timer: 2000
                });
                return;
            }

            Swal.fire({
                title: 'آیا مطمئن هستید؟',
                text: 'آیا با لغو این سانس موافقت می‌کنید؟',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28A745',
                cancelButtonColor: '#DC3545',
                confirmButtonText: 'بله، تایید کن',
                cancelButtonText: 'انصراف'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'POST',
                        url: "<?php echo admin_url('admin-ajax.php') ?>",
                        data: {
                            'action': 'team_ajax_handler',
                            'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                            'callback': 'cancellation_actions',
                            'function': 'update_cancellation_status',
                            'reqid': currentOrderData.reqid,
                            'status': 'approved'
                        },
                        beforeSend: function() {
                            Swal.fire({
                                title: 'در حال پردازش...',
                                text: 'لطفاً منتظر بمانید',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    position: "bottom-start",
                                    icon: "success",
                                    text: 'درخواست لغو با موفقیت تایید شد',
                                    showConfirmButton: false,
                                    timer: 2000
                                });

                                // Close modal and refresh sans display
                                $('#userInfoModal').addClass('hidden').removeClass('flex');
                                let date = $("[data-datepicker].active").data('datepicker');
                                let id = $(".swiper-slide-active").data('id');
                                if (date && id) {
                                    BuildSans(id, date);
                                }
                            } else {
                                Swal.fire({
                                    position: "bottom-start",
                                    icon: "error",
                                    text: response.data || 'خطا در تایید درخواست لغو',
                                    showConfirmButton: false,
                                    timer: 2000
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                position: "bottom-start",
                                icon: "error",
                                text: 'خطا در ارتباط با سرور',
                                showConfirmButton: false,
                                timer: 2000
                            });
                        }
                    });
                }
            });
        });

        // Handle reject cancellation request
        $('#rejectCancellationBtn').on('click', function() {
            if (!currentOrderData || !currentOrderData.reqid) {
                Swal.fire({
                    position: "bottom-start",
                    icon: "error",
                    text: 'خطا در دریافت اطلاعات درخواست لغو',
                    showConfirmButton: false,
                    timer: 2000
                });
                return;
            }

            Swal.fire({
                title: 'آیا مطمئن هستید؟',
                text: 'آیا می‌خواهید درخواست لغو را رد کنید؟',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#DC3545',
                cancelButtonColor: '#28A745',
                confirmButtonText: 'بله، رد کن',
                cancelButtonText: 'انصراف'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'POST',
                        url: "<?php echo admin_url('admin-ajax.php') ?>",
                        data: {
                            'action': 'team_ajax_handler',
                            'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                            'callback': 'cancellation_actions',
                            'function': 'update_cancellation_status',
                            'reqid': currentOrderData.reqid,
                            'status': 'rejected'
                        },
                        beforeSend: function() {
                            Swal.fire({
                                title: 'در حال پردازش...',
                                text: 'لطفاً منتظر بمانید',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    position: "bottom-start",
                                    icon: "success",
                                    text: 'درخواست لغو با موفقیت رد شد',
                                    showConfirmButton: false,
                                    timer: 2000
                                });

                                // Close modal and refresh sans display
                                $('#userInfoModal').addClass('hidden').removeClass('flex');
                                let date = $("[data-datepicker].active").data('datepicker');
                                let id = $(".swiper-slide-active").data('id');
                                if (date && id) {
                                    BuildSans(id, date);
                                }
                            } else {
                                Swal.fire({
                                    position: "bottom-start",
                                    icon: "error",
                                    text: response.data || 'خطا در رد درخواست لغو',
                                    showConfirmButton: false,
                                    timer: 2000
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                position: "bottom-start",
                                icon: "error",
                                text: 'خطا در ارتباط با سرور',
                                showConfirmButton: false,
                                timer: 2000
                            });
                        }
                    });
                }
            });
        });

        // Close modals when clicking outside
        $(document).on('click', function(e) {
            if ($(e.target).hasClass('fixed') && $(e.target).attr('id') === 'userInfoModal') {
                // Close dropdown if open
                const dropdown = $('#cancellationReasonDropdown');
                const icon = $('#dropdownIcon');
                dropdown.removeClass('opacity-100').addClass('opacity-0');
                dropdown.css('max-height', '0');
                icon.removeClass('rotate-180');
                $('#cancellationForm')[0].reset();

                // Reset button states
                $('#cancelSansBtn').removeClass('hidden');
                $('#cancellationRequestHandling').addClass('hidden');

                $('#userInfoModal').addClass('hidden').removeClass('flex');
            }
        });
    });
</script>

<?php if ( $show_credit_notification ) : ?>
<div id="ez-credit-form-overlay" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50" style="display: none;">
	<div class="flex min-h-full w-full items-center justify-center p-4">
		<div id="ez-credit-form-modal" class="bg-white rounded-2xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto border border-slate-200 relative">
			<div class="p-6 font-yekan text-right">
				<h2 class="text-xl font-bold text-navyBlue mb-4">فرم غیرفعالسازی درگاه پرداخت اعتباری</h2>
				<div class="text-slate-700 text-sm leading-relaxed space-y-3">
					<p>مجموعه دار گرامی، به زودی درگاه پرداخت اعتباری (اقساطی) برای کلیه بازی ها با شرایط زیر فعال میشه:</p>
					<p>پرداخت اعتباری (اقساطی) به اینصورته که تعداد نفراتی که کاربر موقع رزرو روی سایت انتخاب می کنه رو از طریق درگاه های پرداخت اعتباری مثل «دیجی پی» رزرو میکنه و طی 4 قسط کل مبلغ رزرو شده رو به ما پرداخت میکنه.</p>
					<p>سانس هایی که از این طرف هر ماه فروخته بشن از 18 تا 22م ماه بعد به کیف پول شما اضافه میشن.</p>
					<p>دریافت اقساط از کاربر بر عهده درگاه پرداخت و اسکیپ زومه و شما بدون مشکل در بازه مشخص شده مبلغ معین شده رو دریافت می کنین.</p>
					<p>شما بابت این خدمات هیچ کارمزد اضافه ای پرداخت نمی کنید و پرداخت کامزد درگاه پرداخت اعتباری، کاملا بر عهده اسکیپ زومه.</p>
					<p>فعال بودن پرداخت اعتباری، شانس فروش شما رو نسبت به سایر بازی ها بالاتر می بره، خصوصا در شرایط فعلی توصیه می کنیم از این طرح استفاده کنین.</p>
					<p>در صورتیکه کاربر پس از رزرو، افزایش تعداد نفرات داشته باشه مابقی نفرات به صورت نقدی و حضوری تسویه میشن.</p>
					<p class="mt-4 pt-4 border-t border-slate-200" style="color: #1e40af; font-weight: bold;">در صورتیکه مایل هستین در این طرح حضور داشته باشین و از مزایاش بهره مند بشین این فرم رو ببندین، در غیراینصورت دکمه غیرفعالسازی رو بزنین و درخواست غیرفعالسازی پرداخت اعتباری رو برای ما بفرستین.</p>
					<p class="mt-4 pt-4 border-t border-slate-200">اینجانب <strong><?php echo esc_html( $credit_display_name ); ?></strong> با شماره همراه <strong><?php echo esc_html( $credit_phone ); ?></strong> درخواست کنسلی حضور در طرح درگاه پرداخت اعتباری اسکیپ زوم را دارم.</p>
				</div>
				<div class="flex gap-3 justify-center mt-6 flex-wrap">
					<button type="button" id="ez-credit-form-btn-canceled" class="px-6 py-2.5 rounded-xl bg-red-500 hover:bg-red-600 text-white font-yekan-bold text-sm">غیرفعالسازی</button>
					<button type="button" id="ez-credit-form-btn-close" class="px-6 py-2.5 rounded-xl bg-green-500 hover:bg-green-600 text-white font-yekan-bold text-sm">بستن</button>
				</div>
			</div>
		</div>
	</div>
	<div id="ez-credit-confirm-box" class="absolute inset-0 z-10 flex items-center justify-center p-4 bg-black/40 rounded-2xl" style="display: none;">
		<div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 font-yekan text-right border border-slate-200" onclick="event.stopPropagation();">
			<p class="text-slate-700 text-sm leading-relaxed mb-6">برای غیرفعالسازی پرداخت اعتباری برای مجموعه تون مطمئن هستین؟<br>این باعث میشه مزایای فروش اعتباری رو از دست بدین.</p>
			<div class="flex gap-3 justify-center flex-wrap">
				<button type="button" id="ez-credit-confirm-yes" class="px-6 py-2.5 rounded-xl bg-red-500 hover:bg-red-600 text-white font-yekan-bold text-sm">بله</button>
				<button type="button" id="ez-credit-confirm-no" class="px-6 py-2.5 rounded-xl bg-green-500 hover:bg-green-600 text-white font-yekan-bold text-sm">خیر</button>
			</div>
		</div>
	</div>
</div>
<script>
(function() {
	var overlay = document.getElementById('ez-credit-form-overlay');
	var modal = document.getElementById('ez-credit-form-modal');
	var card = document.getElementById('ez-credit-notification-card');
	var confirmBox = document.getElementById('ez-credit-confirm-box');
	if (!overlay || !modal) return;
	var btnClose = document.getElementById('ez-credit-form-btn-close');
	var btnCanceled = document.getElementById('ez-credit-form-btn-canceled');
	var btnConfirmYes = document.getElementById('ez-credit-confirm-yes');
	var btnConfirmNo = document.getElementById('ez-credit-confirm-no');
	var ajaxUrl = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';
	var nonce = '<?php echo esc_js( wp_create_nonce( 'v2-ajax-nonce' ) ); ?>';

	function openModal() {
		overlay.style.display = 'flex';
		document.body.style.overflow = 'hidden';
	}
	function hideModal() {
		overlay.style.display = 'none';
		document.body.style.overflow = '';
		if (confirmBox) confirmBox.style.display = 'none';
	}

	if (card) {
		card.addEventListener('click', function(e) {
			e.preventDefault();
			e.stopPropagation();
			openModal();
		});
		card.addEventListener('keydown', function(e) {
			if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openModal(); }
		});
	}

	function sendAction(actionParam, done) {
		var formData = new FormData();
		formData.append('action', 'v2_ajax_handler');
		formData.append('nonce', nonce);
		formData.append('callback', 'credit_form_action');
		formData.append('action_param', actionParam);
		var xhr = new XMLHttpRequest();
		xhr.open('POST', ajaxUrl, true);
		xhr.onreadystatechange = function() {
			if (xhr.readyState === 4 && done) done();
		};
		xhr.send(formData);
	}

	if (btnClose) {
		btnClose.addEventListener('click', function() {
			sendAction('mark_view', function() {
				hideModal();
				var dot = document.getElementById('ez-credit-notification-dot');
				if (dot) dot.style.display = 'none';
			});
		});
	}
	if (btnCanceled) {
		btnCanceled.addEventListener('click', function() {
			if (confirmBox) confirmBox.style.display = 'flex';
		});
	}
	if (btnConfirmYes) {
		btnConfirmYes.addEventListener('click', function() {
			if (confirmBox) confirmBox.style.display = 'none';
			sendAction('mark_canceled', function() {
				hideModal();
				if (card) card.style.display = 'none';
			});
		});
	}
	if (btnConfirmNo) {
		btnConfirmNo.addEventListener('click', function() {
			if (confirmBox) confirmBox.style.display = 'none';
		});
	}

	overlay.addEventListener('click', function(e) {
		if (e.target === overlay) hideModal();
	});
})();
</script>
<?php endif; ?>
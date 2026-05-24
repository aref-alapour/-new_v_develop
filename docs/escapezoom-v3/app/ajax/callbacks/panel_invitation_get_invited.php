<?php
global $wpdb;

$user = wp_get_current_user();

$page   = sanitize_text_field($_POST["page"]) ?: 2;
$status = sanitize_text_field($_POST['status']);

$limit  = 10;
$offset = ($page - 1) * $limit;

$count = (int) $wpdb->get_var(match ($status) {
    "was-invited" => $wpdb->prepare("SELECT COUNT(*) FROM invitations WHERE invited_id LIKE %d", $user->ID),
    "invited" => $wpdb->prepare("SELECT COUNT(*) FROM invitations WHERE inviter_id LIKE %d", $user->ID),
});

$total_pages = ceil($count / $limit);

$invitations = $wpdb->get_results(match ($status) {
    "was-invited" => $wpdb->prepare("SELECT * FROM invitations WHERE invited_id LIKE %d ORDER BY created_at DESC LIMIT %d, %d", $user->ID, $offset, $limit),
    "invited" => $wpdb->prepare("SELECT * FROM invitations WHERE inviter_id LIKE %d ORDER BY created_at DESC LIMIT %d, %d", $user->ID, $offset, $limit),
});

$items = [];
foreach ($invitations as $invitation) {
    $product = $invitation->product_id;

    $person = get_user_by('id', match ($status) {
        "was-invited" => (int) $invitation->inviter_id,
        "invited" => (int) $invitation->invited_id,
    });

    $genres = [];
    foreach (get_the_terms($product, 'product_tag') as $genre) {
        $genres[$genre->term_id] = str_replace('|||||', '', $genre->name);
    }


    $date = $invitation->created_at ?: 0;

    $invitation_status = $invitation->status;
    if ($invitation_status == 'pending') {
        if ((time() - (int) $date) > (30 * 24 * 60 * 60)) {
            $invitation_status = 'expired';
        }
    }

    $rate = 0;
    if (
        (int) array_sum(get_post_meta($product, 'product_rates', true)) !== 0 &&
        (int) get_post_meta($product, 'comments_count_new', true) !== 0
    ) {
        $rate = number_format(round(array_sum(get_post_meta($product, 'product_rates', true)) / get_post_meta($product, 'comments_count_new', true) / 20 / 5, 2), 2, '.', '');
    }

    $items[] = [
        'ID'         => (int) $invitation->ID,
        'product'    => [
            'title'  => get_the_title($product),
            'level'  => get_field("room_level", $product),
            'image'  => get_post_thumbnail_id($product),
            'hood'   => get_field("room_loc", $product),
            'city'   => get_the_terms($product, 'product_cat')[0]->name,
            'url'    => get_permalink($product),
            'genres' => $genres,
            'rate'   => $rate,
        ],
        'invitation' => [
            'title'  => $person->display_name,
            'url'    => site_url('profile/' . $person->ID),
            'status' => $invitation_status,
            'phone'  => $person->billing_phone,
            'date'   => $date,
        ],
    ];
}

$alert = match ($status) {
    "was-invited" => 'شما تابحال دعوت نشده اید.',
    "invited" => 'شما تابحال کسی را دعوت نکردید',
};
?>

<div class="max-lg:divide-y-2 lg:space-y-7">
    <?php if ($items) { ?>

        <?php foreach ($items as $item) { ?>
            <div class="max-lg:py-8 lg:grid lg:grid-cols-2 lg:rounded-xlh lg:border lg:border-slate-105 lg:p-5">

                <div class="flex max-lg:items-center max-lg:justify-between lg:gap-x-6">
                    <div class="lg:order-1">
                        <div class="lg:flex lg:items-center lg:gap-x-3">
                            <span class="-mt-1.5 flex h-6 w-10 items-center justify-center rounded bg-yellow-500 max-lg:hidden">
                                <?php echo esc_html($item['product']['rate']); ?>
                            </span>
                            <h2 class="mb-1 text-xl">
                                <?php echo esc_html($item['product']['title']) ?>
                            </h2>
                        </div>
                        <div class="my-3 space-x-px space-x-reverse text-4xs text-text-3 max-lg:hidden">
                            <span><?php echo esc_html($item['product']['hood']) ?></span>
                            .
                            <span><?php echo esc_html($item['product']['city']) ?></span>
                        </div>
                        <div class="space-x-px space-x-reverse text-4xs text-text-3">
                            <?php echo implode('.', $item['product']['genres']); ?>
                        </div>
                    </div>
                    <?php if ($item['product']['image']) { ?>
                        <div>
                            <a href="<?php echo $item['product']['url']; ?>">
                                <?php echo wp_get_attachment_image($item['product']['image'], 'large', '', [
                                    'class' => 'h-d52 w-d43 rounded-md object-cover lg:h-d115 lg:w-d95',
                                ]) ?>
                            </a>
                        </div>
                    <?php } ?>
                </div>

                <div class="lg:content-center lg:space-y-4">

                    <div class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2 max-lg:my-4">

                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-white shadow">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 9 9" fill="none">
                                <rect width="8.51999" height="8.51999" transform="translate(0.09375 0.336914)" fill="white"></rect>
                                <circle cx="4.25624" cy="2.62636" r="1.87343" fill="#FD7013"></circle>
                                <ellipse cx="4.25632" cy="6.78914" rx="3.33054" ry="1.45711" fill="#FD7013"></ellipse>
                            </svg>
                        </div>

                        <?php ?>

                        <div class="space-x-3 space-x-reverse">
                            <?php if ($status == 'was-invited') { ?>
                                <span class="inline-block text-14 text-text-3">'دعوت کننده</span>
                                <a href="<?php echo esc_html($item['invitation']['url']); ?>" class="inline-block text-base text-blue">
                                    <?php echo esc_html($item['invitation']['title']); ?>
                                </a>
                            <?php } else { ?>
                                <a href="<?php echo esc_html($item['invitation']['url']); ?>" class="inline-block text-base text-blue">
                                    <?php echo esc_html($item['invitation']['title']); ?>
                                </a>
                                <span class="inline-block text-14 text-text-3">
                                    دعوت کردید
                                </span>
                            <?php } ?>

                        </div>
                    </div>

                    <div id="item-<?php echo esc_attr($item['ID']); ?>-actions">
                        <?php if ($item['invitation']['status'] == 'pending') { ?>

                            <?php if ($status === 'was-invited') { ?>
                                <div class="grid grid-cols-2 gap-x-5">
                                    <button type="button" data-invite-action="approved" data-id="<?php echo esc_attr($item['ID']); ?>" class="flex h-12.5 w-full items-center justify-center gap-x-4 rounded-lg border border-slate-105">
                                        <span>می‌پذیرم</span>
                                        <span>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="21" height="20" viewBox="0 0 21 20" fill="none">
                                                <rect x="0.5" width="20" height="20" rx="4" fill="#02C96F"></rect>
                                                <path d="M9.68627 13.0541L14.4733 8.27492C14.6426 8.09376 14.7348 7.85403 14.7305 7.60629C14.7263 7.35855 14.6258 7.12215 14.4503 6.94694C14.2748 6.77173 14.038 6.67141 13.7899 6.66713C13.5417 6.66286 13.3016 6.75495 13.1201 6.92399L9.00971 11.0277L7.45234 9.4729C7.27089 9.30386 7.03077 9.21176 6.78263 9.21604C6.53448 9.22032 6.29769 9.32064 6.1222 9.49584C5.94671 9.67105 5.84623 9.90746 5.84194 10.1552C5.83765 10.4029 5.9299 10.6427 6.09922 10.8238L8.33315 13.0541C8.51266 13.2331 8.756 13.3337 9.00971 13.3337C9.26342 13.3337 9.50676 13.2331 9.68627 13.0541Z" fill="white"></path>
                                            </svg>
                                        </span>
                                    </button>
                                    <button type="button" data-invite-action="declined" data-id="<?php echo esc_attr($item['ID']); ?>" class="flex h-12.5 w-full items-center justify-center gap-x-4 rounded-lg border border-slate-105">
                                        <span>نمی‌پذیرم</span>
                                        <span>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                                <rect width="20" height="20" rx="4" fill="#F21543"></rect>
                                                <path d="M6.74558 6.74567L6.74567 6.74558C6.87948 6.61194 7.06086 6.53688 7.24997 6.53688C7.43909 6.53688 7.62047 6.61194 7.75428 6.74558L7.75431 6.74561L9.90906 8.89958L12.0638 6.74563L12.0638 6.74561L12.1345 6.81634C12.1915 6.75939 12.2591 6.71422 12.3335 6.6834C12.4079 6.65258 12.4876 6.63672 12.5682 6.63672C12.6487 6.63672 12.7284 6.65258 12.8028 6.6834L6.74558 6.74567ZM6.74558 6.74567C6.61194 6.87948 6.53688 7.06086 6.53688 7.24997C6.53688 7.43909 6.61194 7.62047 6.74558 7.75428L6.74561 7.75431L8.89958 9.90906L6.74563 12.0638C6.74562 12.0638 6.74562 12.0638 6.74561 12.0638C6.61186 12.1976 6.53672 12.379 6.53672 12.5682C6.53672 12.7573 6.61187 12.9387 6.74563 13.0725C6.87939 13.2063 7.06081 13.2814 7.24997 13.2814C7.43914 13.2814 7.62056 13.2063 7.75432 13.0725L9.90906 10.9186L12.0638 13.0725C12.0638 13.0725 12.0638 13.0725 12.0638 13.0725C12.1976 13.2063 12.379 13.2814 12.5682 13.2814C12.7573 13.2814 12.9387 13.2063 13.0725 13.0725C13.2063 12.9387 13.2814 12.7573 13.2814 12.5682C13.2814 12.379 13.2063 12.1976 13.0725 12.0638C13.0725 12.0638 13.0725 12.0638 13.0725 12.0638L10.9186 9.90906L13.0725 7.75432L13.0725 7.75431L13.0018 7.68361L6.74558 6.74567Z" fill="white" stroke="white" stroke-width="0.2"></path>
                                            </svg>
                                        </span>
                                    </button>
                                </div>
                            <?php } else { ?>
                                <div class="flex h-12.5 w-full items-center justify-center gap-x-5 rounded-lg bg-warn-surface-3">
                                    <span>در انتظار پذیرفتن</span>
                                    <span class="-mt-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                            <rect width="20" height="20" rx="4" fill="#02C96F"></rect>
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M10.7407 10.5C10.7407 10.1704 10.9782 9.89361 11.2902 9.78736C12.8663 9.2506 14 7.7577 14 6V5C14 4.44772 13.5523 4 13 4H7C6.44772 4 6 4.44772 6 5V6C6 7.75769 7.13371 9.25059 8.70978 9.78735C9.02176 9.89361 9.25926 10.1704 9.25926 10.5C9.25926 10.8296 9.02176 11.1064 8.70978 11.2126C7.13371 11.7494 6 13.2423 6 15V16C6 16.5523 6.44772 17 7 17H13C13.5523 17 14 16.5523 14 16V15C14 13.2423 12.8663 11.7494 11.2902 11.2126C10.9782 11.1064 10.7407 10.8296 10.7407 10.5Z" fill="white"></path>
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M10.2609 9C11.2214 9 12 8.22136 12 7.26087C12 7.1168 11.8832 7 11.7391 7H8.26087C8.1168 7 8 7.1168 8 7.26087C8 8.22136 8.77864 9 9.73913 9C9.84663 9 9.93183 9.0907 9.92513 9.19798L9.6211 14.0624C9.58816 14.5894 9.15111 15 8.62305 15H7.86957C7.38932 15 7 15.3893 7 15.8696C7 15.9416 7.0584 16 7.13044 16H10.3696H12.8696C12.9416 16 13 15.9416 13 15.8696C13 15.3893 12.6107 15 12.1304 15H11.377C10.8489 15 10.4118 14.5894 10.3789 14.0624L10.0749 9.19798C10.0682 9.0907 10.1534 9 10.2609 9Z" fill="#02C96F"></path>
                                        </svg>
                                    </span>
                                </div>
                            <?php } ?>

                        <?php } elseif ($item['invitation']['status'] == 'approved') { ?>

                            <?php if ($status === 'was-invited') { ?>
                                <div class="flex h-12.5 items-center justify-center gap-x-5 rounded-lg bg-accent-20">
                                    <span>پذیرفتم</span>
                                    <span class="-mt-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="9" height="8" viewBox="0 0 9 8" fill="none">
                                            <path d="M3.89916 7.05412L8.68616 2.27492C8.85548 2.09376 8.94772 1.85403 8.94343 1.60629C8.93915 1.35855 8.83867 1.12215 8.66318 0.946935C8.48768 0.771732 8.2509 0.671415 8.00275 0.667135C7.7546 0.662855 7.51449 0.754948 7.33303 0.923995L3.2226 5.02774L1.66523 3.4729C1.48378 3.30386 1.24366 3.21176 0.995517 3.21604C0.74737 3.22032 0.510584 3.32064 0.335087 3.49584C0.159598 3.67105 0.0591171 3.90746 0.0548305 4.1552C0.0505439 4.40294 0.142787 4.64267 0.312109 4.82382L2.54604 7.05412C2.72555 7.23312 2.96889 7.33366 3.2226 7.33366C3.47631 7.33366 3.71965 7.23312 3.89916 7.05412Z" fill="#02C96F"></path>
                                        </svg>
                                    </span>
                                </div>
                            <?php } else { ?>
                                <a href="tel:+98<?php echo str_starts_with(0, $item['invitation']['phone']) ? substr($item['invitation']['phone'], 0, 1) : $item['invitation']['phone']; ?>" class="flex h-12.5 items-center justify-center gap-x-5 rounded-lg bg-accent-450">
                                    <div class="flex items-center gap-x-4">
                                        <span class="text-14 text-slate-105">
                                            تماس با <?php echo esc_html($item['invitation']['title']); ?>
                                        </span>
                                        <bdo dir="ltr" class="text-base font-bold text-white">
                                            <?php echo $item['invitation']['phone']; ?>
                                        </bdo>
                                        <span class="-mt-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="16" viewBox="0 0 17 16" fill="none">
                                                <path d="M12.7186 14.9997C12.1086 14.9997 11.2517 14.7791 9.96861 14.0622C8.4083 13.1872 7.20142 12.3794 5.64955 10.8316C4.1533 9.33627 3.42517 8.36815 2.40611 6.51377C1.25486 4.42002 1.45111 3.32252 1.67049 2.85346C1.93174 2.29283 2.31736 1.95752 2.8158 1.62471C3.09891 1.43922 3.39851 1.28022 3.7108 1.14971C3.74205 1.13627 3.77111 1.12346 3.79705 1.1119C3.95174 1.04221 4.18611 0.936898 4.48299 1.0494C4.68111 1.12377 4.85799 1.27596 5.13486 1.5494C5.70267 2.1094 6.47861 3.35659 6.76486 3.96908C6.95705 4.3819 7.08424 4.6544 7.08455 4.96002C7.08455 5.31783 6.90455 5.59377 6.68611 5.89158C6.64517 5.94752 6.60455 6.00096 6.56517 6.05283C6.32736 6.36533 6.27517 6.45565 6.30955 6.6169C6.37924 6.94096 6.89892 7.90565 7.75299 8.75784C8.60705 9.61002 9.54392 10.0969 9.86924 10.1663C10.0374 10.2022 10.1295 10.1478 10.452 9.90158C10.4983 9.86627 10.5458 9.82971 10.5955 9.79315C10.9286 9.54534 11.1917 9.37002 11.5411 9.37002H11.543C11.847 9.37002 12.1074 9.5019 12.5386 9.7194C13.1011 10.0031 14.3858 10.7691 14.9492 11.3375C15.2233 11.6138 15.3761 11.79 15.4508 11.9878C15.5633 12.2856 15.4574 12.5191 15.3883 12.6753C15.3767 12.7013 15.3639 12.7297 15.3505 12.7613C15.2189 13.073 15.059 13.372 14.8727 13.6544C14.5405 14.1513 14.2039 14.536 13.642 14.7975C13.3535 14.934 13.0378 15.0032 12.7186 14.9997Z" fill="white"></path>
                                            </svg>
                                        </span>
                                    </div>
                                    <span class="-mt-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="9" height="8" viewBox="0 0 9 8" fill="none">
                                            <path d="M3.89916 7.05412L8.68616 2.27492C8.85548 2.09376 8.94772 1.85403 8.94343 1.60629C8.93915 1.35855 8.83867 1.12215 8.66318 0.946935C8.48768 0.771732 8.2509 0.671415 8.00275 0.667135C7.7546 0.662855 7.51449 0.754948 7.33303 0.923995L3.2226 5.02774L1.66523 3.4729C1.48378 3.30386 1.24366 3.21176 0.995517 3.21604C0.74737 3.22032 0.510584 3.32064 0.335087 3.49584C0.159598 3.67105 0.0591171 3.90746 0.0548305 4.1552C0.0505439 4.40294 0.142787 4.64267 0.312109 4.82382L2.54604 7.05412C2.72555 7.23312 2.96889 7.33366 3.2226 7.33366C3.47631 7.33366 3.71965 7.23312 3.89916 7.05412Z" fill="#02C96F"></path>
                                        </svg>
                                    </span>
                                </a>
                            <?php } ?>

                        <?php } elseif ($item['invitation']['status'] == 'declined') { ?>

                            <?php if ($status === 'was-invited') { ?>
                                <div class="flex h-12.5 items-center justify-center gap-x-5 rounded-lg bg-secondary-100">
                                    <span>نپذیرفتم</span>
                                    <span class="-mt-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="7" height="8" viewBox="0 0 7 8" fill="none">
                                            <path d="M0.337379 0.837467L0.337467 0.837379C0.471274 0.703738 0.652656 0.628674 0.84177 0.628674C1.03088 0.628674 1.21227 0.703738 1.34607 0.837379L1.3461 0.83741L3.50086 2.99137L5.6556 0.837423L5.65562 0.83741L5.72632 0.908134C5.78326 0.851188 5.85087 0.806016 5.92527 0.775197C5.99967 0.744378 6.07942 0.728516 6.15995 0.728516C6.24049 0.728516 6.32023 0.744378 6.39463 0.775197L0.337379 0.837467ZM0.337379 0.837467C0.203738 0.971274 0.128674 1.15266 0.128674 1.34177C0.128674 1.53088 0.203738 1.71227 0.337379 1.84607L0.33741 1.8461L2.49137 4.00086L0.337423 6.1556C0.337419 6.15561 0.337414 6.15561 0.33741 6.15562C0.203657 6.28938 0.128516 6.47079 0.128516 6.65995C0.128516 6.84912 0.203662 7.03054 0.337423 7.1643C0.471184 7.29806 0.652603 7.37321 0.84177 7.37321C1.03094 7.37321 1.21236 7.29806 1.34612 7.1643L3.50086 5.01035L5.6556 7.1643C5.65561 7.1643 5.65561 7.16431 5.65562 7.16431C5.78938 7.29806 5.97079 7.37321 6.15995 7.37321C6.34912 7.37321 6.53054 7.29806 6.6643 7.1643C6.79806 7.03054 6.87321 6.84912 6.87321 6.65995C6.87321 6.47079 6.79806 6.28938 6.66431 6.15562C6.66431 6.15561 6.6643 6.15561 6.6643 6.1556L4.51035 4.00086L6.6643 1.84612L6.66431 1.8461L6.59359 1.77541L0.337379 0.837467Z" fill="#F21543" stroke="#F21543" stroke-width="0.2"></path>
                                        </svg>
                                    </span>
                                </div>
                            <?php } else { ?>
                                <div class="flex h-12.5 items-center justify-center gap-x-5 rounded-lg bg-secondary-100">
                                    <span>دعوت شما پذیرفته نشد</span>
                                    <span class="-mt-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="7" height="8" viewBox="0 0 7 8" fill="none">
                                            <path d="M0.337379 0.837467L0.337467 0.837379C0.471274 0.703738 0.652656 0.628674 0.84177 0.628674C1.03088 0.628674 1.21227 0.703738 1.34607 0.837379L1.3461 0.83741L3.50086 2.99137L5.6556 0.837423L5.65562 0.83741L5.72632 0.908134C5.78326 0.851188 5.85087 0.806016 5.92527 0.775197C5.99967 0.744378 6.07942 0.728516 6.15995 0.728516C6.24049 0.728516 6.32023 0.744378 6.39463 0.775197L0.337379 0.837467ZM0.337379 0.837467C0.203738 0.971274 0.128674 1.15266 0.128674 1.34177C0.128674 1.53088 0.203738 1.71227 0.337379 1.84607L0.33741 1.8461L2.49137 4.00086L0.337423 6.1556C0.337419 6.15561 0.337414 6.15561 0.33741 6.15562C0.203657 6.28938 0.128516 6.47079 0.128516 6.65995C0.128516 6.84912 0.203662 7.03054 0.337423 7.1643C0.471184 7.29806 0.652603 7.37321 0.84177 7.37321C1.03094 7.37321 1.21236 7.29806 1.34612 7.1643L3.50086 5.01035L5.6556 7.1643C5.65561 7.1643 5.65561 7.16431 5.65562 7.16431C5.78938 7.29806 5.97079 7.37321 6.15995 7.37321C6.34912 7.37321 6.53054 7.29806 6.6643 7.1643C6.79806 7.03054 6.87321 6.84912 6.87321 6.65995C6.87321 6.47079 6.79806 6.28938 6.66431 6.15562C6.66431 6.15561 6.6643 6.15561 6.6643 6.1556L4.51035 4.00086L6.6643 1.84612L6.66431 1.8461L6.59359 1.77541L0.337379 0.837467Z" fill="#F21543" stroke="#F21543" stroke-width="0.2"></path>
                                        </svg>
                                    </span>
                                </div>
                            <?php } ?>

                        <?php } else { ?>
                            <div class="flex h-12.5 items-center justify-center gap-x-5 rounded-lg" style="background: #FD70131A;">
                                <span>منقضی شده</span>
                                <span class="-mt-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="3" height="10" viewBox="0 0 3 10" fill="none">
                                        <path d="M2.34702 2.49447C2.34702 2.93314 2.33769 3.3438 2.31902 3.72647C2.30036 4.0998 2.27702 4.45447 2.24902 4.79047C2.22102 5.12647 2.18836 5.44847 2.15102 5.75647C2.11369 6.06447 2.07636 6.36314 2.03902 6.65247H0.961023C0.92369 6.36314 0.88169 6.06914 0.835023 5.77047C0.788357 5.46247 0.746357 5.14047 0.709023 4.80447C0.67169 4.46847 0.639023 4.10914 0.611023 3.72647C0.592357 3.3438 0.583023 2.93314 0.583023 2.49447V0.730469H2.34702V2.49447ZM1.50702 9.27047C1.20836 9.27047 0.96569 9.1958 0.779023 9.04647C0.592357 8.8878 0.499023 8.64514 0.499023 8.31847C0.499023 7.97314 0.592357 7.7258 0.779023 7.57647C0.96569 7.42714 1.20836 7.35247 1.50702 7.35247C1.79636 7.35247 2.03436 7.42714 2.22102 7.57647C2.40769 7.7258 2.50102 7.97314 2.50102 8.31847C2.50102 8.64514 2.40769 8.8878 2.22102 9.04647C2.03436 9.1958 1.79636 9.27047 1.50702 9.27047Z" fill="#FD7013" />
                                    </svg>
                                </span>
                            </div>
                        <?php } ?>
                    </div>

                </div>

            </div>
        <?php } ?>

        <?php if ($total_pages > 1) { ?>
            <div class="mb-9 flex w-full items-center justify-center gap-4">
                <div class="flex gap-4 max-lg:gap-2 mt-2 justify-start max-lg:justify-center pagination">
                    <?php echo paginate_links([
                        'mid_size'  => 1,
                        'base'      => get_pagenum_link(1) . '%_%',
                        'format'    => '?page=%#%',
                        'current'   => max(1, $page),
                        'total'     => $total_pages,
                        'prev_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none" class="rotate-180 opacity-25"><path d="M5.08008 11.1602L1.51062 7.14452C1.17384 6.76563 1.17384 6.19468 1.51062 5.81579L5.08008 1.80016" stroke="#0A184A" stroke-width="2" stroke-linecap="round"></path></svg>',
                        'next_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none"><path d="M5.08008 11.1602L1.51062 7.14452C1.17384 6.76563 1.17384 6.19468 1.51062 5.81579L5.08008 1.80016" stroke="#0A184A" stroke-width="2" stroke-linecap="round"></path></svg>',
                    ]); ?>
                </div>
            </div>
        <?php } ?>

    <?php } else { ?>
        <div class="text-22 font-bold lg:text-lg text-center mt-16 mb-12 text-gray-500">
            <?php echo $alert; ?>
        </div>
    <?php } ?>

    <script>
        jQuery(document).ready(function($) {
            $("[data-invite-action]").on('click', function() {
                let _ = $(this),
                    action = _.data('invite-action'),
                    id = _.data('id')

                let temp = $(`#item-${id}-actions`).html()

                Swal.mixin({
                    iconHtml: `<svg xmlns="http://www.w3.org/2000/svg" width="95" height="97" viewBox="0 0 95 97" fill="none" class="-mr-2.5">
<g filter="url(#filter0_d_25138_8856)">
<mask id="path-1-inside-1_25138_8856" fill="white">
<path d="M71 31.5C71 48.897 56.897 63 39.5 63C22.103 63 8 48.897 8 31.5C8 14.103 22.103 0 39.5 0C56.897 0 71 14.103 71 31.5Z"/>
</mask>
<path d="M71 31.5C71 48.897 56.897 63 39.5 63C22.103 63 8 48.897 8 31.5C8 14.103 22.103 0 39.5 0C56.897 0 71 14.103 71 31.5Z" fill="white"/>
<path d="M70.5 31.5C70.5 48.6208 56.6208 62.5 39.5 62.5V63.5C57.1731 63.5 71.5 49.1731 71.5 31.5H70.5ZM39.5 62.5C22.3792 62.5 8.5 48.6208 8.5 31.5H7.5C7.5 49.1731 21.8269 63.5 39.5 63.5V62.5ZM8.5 31.5C8.5 14.3792 22.3792 0.5 39.5 0.5V-0.5C21.8269 -0.5 7.5 13.8269 7.5 31.5H8.5ZM39.5 0.5C56.6208 0.5 70.5 14.3792 70.5 31.5H71.5C71.5 13.8269 57.1731 -0.5 39.5 -0.5V0.5Z" fill="#EFC101" mask="url(#path-1-inside-1_25138_8856)"/>
</g>
<g filter="url(#filter1_i_25138_8856)">
<rect x="36" y="42" width="8" height="8" rx="4" fill="url(#paint0_linear_25138_8856)"/>
</g>
<g filter="url(#filter2_i_25138_8856)">
<rect x="36" y="11" width="8" height="27" rx="4" fill="url(#paint1_linear_25138_8856)"/>
</g>
<defs>
<filter id="filter0_d_25138_8856" x="0" y="0" width="95" height="97" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
<feFlood flood-opacity="0" result="BackgroundImageFix"/>
<feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
<feOffset dx="8" dy="18"/>
<feGaussianBlur stdDeviation="8"/>
<feComposite in2="hardAlpha" operator="out"/>
<feColorMatrix type="matrix" values="0 0 0 0 0.306354 0 0 0 0 0.36728 0 0 0 0 0.425 0 0 0 0.08 0"/>
<feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_25138_8856"/>
<feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_25138_8856" result="shape"/>
</filter>
<filter id="filter1_i_25138_8856" x="35" y="39" width="9" height="11" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
<feFlood flood-opacity="0" result="BackgroundImageFix"/>
<feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"/>
<feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
<feOffset dx="-1" dy="-5"/>
<feGaussianBlur stdDeviation="1.5"/>
<feComposite in2="hardAlpha" operator="arithmetic" k2="-1" k3="1"/>
<feColorMatrix type="matrix" values="0 0 0 0 1 0 0 0 0 0.881618 0 0 0 0 0.3875 0 0 0 1 0"/>
<feBlend mode="normal" in2="shape" result="effect1_innerShadow_25138_8856"/>
</filter>
<filter id="filter2_i_25138_8856" x="35" y="9" width="9" height="29" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
<feFlood flood-opacity="0" result="BackgroundImageFix"/>
<feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"/>
<feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
<feOffset dx="-1" dy="-2"/>
<feGaussianBlur stdDeviation="1.5"/>
<feComposite in2="hardAlpha" operator="arithmetic" k2="-1" k3="1"/>
<feColorMatrix type="matrix" values="0 0 0 0 1 0 0 0 0 0.881618 0 0 0 0 0.3875 0 0 0 1 0"/>
<feBlend mode="normal" in2="shape" result="effect1_innerShadow_25138_8856"/>
</filter>
<linearGradient id="paint0_linear_25138_8856" x1="50.3447" y1="41.8144" x2="41.454" y2="41.6016" gradientUnits="userSpaceOnUse">
<stop stop-color="#E4B903"/>
<stop offset="1" stop-color="#EFC101"/>
</linearGradient>
<linearGradient id="paint1_linear_25138_8856" x1="50.3447" y1="10.3737" x2="41.4494" y2="10.3106" gradientUnits="userSpaceOnUse">
<stop stop-color="#E4B903"/>
<stop offset="1" stop-color="#EFC101"/>
</linearGradient>
</defs>
</svg>`,
                    width: 280,
                    customClass: {
                        icon: 'border-0',
                        title: 'text-lg leading-6 pt-0',
                        actions: 'w-full px-4',
                        popup: 'rounded-xl',
                        confirmButton: 'bg-primaryColor p-2 text-white rounded-xl shadow-primary-3 shadow-12 ml-3 leading-5',
                        cancelButton: 'bg-slate-200 text-white p-3 rounded-xl shadow-13 leading-5'
                    },
                    buttonsStyling: false
                }).fire({
                    icon: 'info',
                    title: 'آیا از انجام اینکار اطمینان دارید ؟',
                    showCancelButton: true,
                    confirmButtonText: "بله",
                    cancelButtonText: "نه، میخوام بیشتر فکر کنم",
                }).then(result => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "<?php echo admin_url('admin-ajax.php') ?>",
                            type: 'POST',
                            data: {
                                'action': 'v2_ajax_handler',
                                'nonce': "<?php echo wp_create_nonce('v2-ajax-nonce') ?>",
                                'callback': 'panel_invitation_change_status',
                                'status': action,
                                'id': id,
                            },
                            beforeSend: function() {
                                $(`#item-${id}-actions`).html(`
                                <div class="flex h-12.5 w-full items-center justify-center gap-x-4 rounded-lg border border-slate-105">
                                    <div class="spinner" style="width: 20px;border: 3px solid #fd7013;"></div>
                                </div>
                                `)
                            },
                            success: function(response) {

                                Swal.mixin({
                                    toast: true,
                                    position: 'bottom-start',
                                    showConfirmButton: false,
                                    timer: 3000,
                                }).fire({
                                    icon: response.success ? 'success' : 'error',
                                    title: response.success ? response.data.message : response.data,
                                })

                                // Send tracking data to Zabalin if status change was successful
                                if (response.success && response.data.tracking_data) {
                                    zebline.event.track("invitation_response", response.data.tracking_data);
                                }

                                if (!response.success) {
                                    $(`#item-${id}-actions`).html(temp)
                                } else {
                                    let out = `
                                    <div class="flex h-12.5 items-center justify-center gap-x-5 rounded-lg bg-accent-20">
                                        <span>پذیرفتم</span>
                                        <span class="-mt-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="9" height="8" viewBox="0 0 9 8" fill="none">
                                                <path d="M3.89916 7.05412L8.68616 2.27492C8.85548 2.09376 8.94772 1.85403 8.94343 1.60629C8.93915 1.35855 8.83867 1.12215 8.66318 0.946935C8.48768 0.771732 8.2509 0.671415 8.00275 0.667135C7.7546 0.662855 7.51449 0.754948 7.33303 0.923995L3.2226 5.02774L1.66523 3.4729C1.48378 3.30386 1.24366 3.21176 0.995517 3.21604C0.74737 3.22032 0.510584 3.32064 0.335087 3.49584C0.159598 3.67105 0.0591171 3.90746 0.0548305 4.1552C0.0505439 4.40294 0.142787 4.64267 0.312109 4.82382L2.54604 7.05412C2.72555 7.23312 2.96889 7.33366 3.2226 7.33366C3.47631 7.33366 3.71965 7.23312 3.89916 7.05412Z" fill="#02C96F"></path>
                                            </svg>
                                        </span>
                                    </div>`

                                    if (action === 'declined') {
                                        out = `
                                        <div class="flex h-12.5 items-center justify-center gap-x-5 rounded-lg bg-secondary-100">
                                            <span>نپذیرفتم</span>
                                            <span class="-mt-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="7" height="8" viewBox="0 0 7 8" fill="none">
                                                    <path d="M0.337379 0.837467L0.337467 0.837379C0.471274 0.703738 0.652656 0.628674 0.84177 0.628674C1.03088 0.628674 1.21227 0.703738 1.34607 0.837379L1.3461 0.83741L3.50086 2.99137L5.6556 0.837423L5.65562 0.83741L5.72632 0.908134C5.78326 0.851188 5.85087 0.806016 5.92527 0.775197C5.99967 0.744378 6.07942 0.728516 6.15995 0.728516C6.24049 0.728516 6.32023 0.744378 6.39463 0.775197L0.337379 0.837467ZM0.337379 0.837467C0.203738 0.971274 0.128674 1.15266 0.128674 1.34177C0.128674 1.53088 0.203738 1.71227 0.337379 1.84607L0.33741 1.8461L2.49137 4.00086L0.337423 6.1556C0.337419 6.15561 0.337414 6.15561 0.33741 6.15562C0.203657 6.28938 0.128516 6.47079 0.128516 6.65995C0.128516 6.84912 0.203662 7.03054 0.337423 7.1643C0.471184 7.29806 0.652603 7.37321 0.84177 7.37321C1.03094 7.37321 1.21236 7.29806 1.34612 7.1643L3.50086 5.01035L5.6556 7.1643C5.65561 7.1643 5.65561 7.16431 5.65562 7.16431C5.78938 7.29806 5.97079 7.37321 6.15995 7.37321C6.34912 7.37321 6.53054 7.29806 6.6643 7.1643C6.79806 7.03054 6.87321 6.84912 6.87321 6.65995C6.87321 6.47079 6.79806 6.28938 6.66431 6.15562C6.66431 6.15561 6.6643 6.15561 6.6643 6.1556L4.51035 4.00086L6.6643 1.84612L6.66431 1.8461L6.59359 1.77541L0.337379 0.837467Z" fill="#F21543" stroke="#F21543" stroke-width="0.2"></path>
                                                </svg>
                                            </span>
                                        </div>`
                                    }

                                    $(`#item-${id}-actions`).html(out)
                                }
                            },
                        })
                    }
                })
            })
            $(".pagination a").on('click', function(e) {
                e.preventDefault()

                let page = $(this).attr('href').split('?page=')[1] ?? 1
                page = parseInt(page)

                $.ajax({
                    url: "<?php echo admin_url('admin-ajax.php') ?>",
                    type: 'POST',
                    data: {
                        'action': 'v2_ajax_handler',
                        'nonce': "<?php echo wp_create_nonce('v2-ajax-nonce') ?>",
                        'callback': 'panel_invitation_get_invited',
                        'page': page,
                        'status': "<?php echo esc_html($status) ?>",
                    },
                    beforeSend: function() {
                        $("#results").html(() => {
                            let out = ''
                            for (let i = 0; i < 3; i++) out += '<div class="w-full h-44 rounded-xl mb-8 skeleton"></div>'

                            return out
                        })
                    },
                    success: function(response) {
                        $("#results").html(response)
                    },
                })
            })
        })
    </script>

</div>
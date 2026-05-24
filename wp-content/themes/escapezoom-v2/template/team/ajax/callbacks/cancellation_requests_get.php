<?php
global $wpdb;

$medoo = medoo();

$now = time();

$requests_per_page  = 100;
$offset             = 0;

$conditions = [
    "status"    => "pending",
    "ORDER"     => ["created_at" => "DESC"],
    "LIMIT"     => [$offset, $requests_per_page]
];
$requests = $medoo->select("cancellation_requests", "*", $conditions);

foreach ($requests as $request) {

    $session_ts    = function_exists('ez_cancellation_session_ts') ? ez_cancellation_session_ts($request['sans_time']) : (int) strtotime($request['sans_time']);
    $hours_to_sans = ($session_ts - $now) / 3600;

//     تبدیل درخواست ها به موعد بررسی گذشت (فقط پلیر؛ درخواست مجموعه تا رسیدگی ادمین منقضی نمی‌شود)
    $should_expire = ($request['requester_type'] === 'customer' && $hours_to_sans <= TIME_TO_EXPIRE);

    if ($should_expire) {
        $medoo->update('cancellation_requests', [
            'status'        => 'expired',
            'updated_at'    => $now,
        ], ['ID' => $request['ID']]);

        $medoo->insert('cancellation_log', [
            'request_id'    => $request['ID'],
            'product_id'    => $request['product_id'],
            'user_id'       => 0,
            'user_role'     => 'system',
            'action'        => 'expire',
            'action_time'   => $now
        ]);
    }
}

$requests = $medoo->select("cancellation_requests", "*", $conditions); // بعد از پردازش بالا برای شناسایی موعد بررسی ها گذشت دوباره درخواست هارو رفرش میکنیم
foreach ($requests as $request) :

    if (($request['sans_time'] - $request['created_at']) / 3600 > CRISIS_TIME) {
        $request_info1_text     = 'لغوبالای 24 ساعت';
        $request_info1_class    = 'text-orangee';
        $under_24               = false;
    } else {
        $request_info1_text     = 'لغوزیر 24 ساعت';
        $request_info1_class    = 'text-pinkk';
        $under_24               = true;
    }

    if ($request['requester_type'] === 'customer') {
        $request_info2_text     = 'پلیر';
        $request_info2_class    = 'text-blueEscape';
        $request_cart_color     = 'bg-[#5091FB1A]';
        $request_type           = 'customer';
    } else {
        $request_info2_text     = 'مجموعه';
        $request_info2_class    = 'text-red-500';
        $request_cart_color     = 'bg-[#FFEDD4]';
        $request_type           = 'owner';
    }

    $order = wc_get_order($request['order_id']);
    foreach ($order->get_items() as $item) {
        $product_id = $item['product_id'];
        $quantity   = $item->get_quantity();
    }

    if ($order->get_billing_first_name() || $order->get_billing_last_name())
        $buyer = trim(sprintf(_x('%1$s %2$s', 'full name', 'woocommerce'), $order->get_billing_first_name(), $order->get_billing_last_name()));

    $brand_data = get_the_terms($product_id, 'yith_product_brand')[0];

    $cancellation_reasons = cancellation_reasons(); ?>

    <section class="request-card flex justify-center items-center mt-5">
        <input type="hidden" name="request_id" value="<?php echo $request['ID']; ?>">
        <div class="rounded-2.5xl overflow-hidden w-full pt-[20px] border border-[#DBE2EA] shadow-[0px_1px_0px_0px_#DBE2EA] <?php echo $request_cart_color ?>">
            <div class="flex justify-between px-[20px]">
                <div class="flex justify-start items-center gap-[5px] w-[168px]">
                    <p class="<?php echo $request_info1_class ?> text-base font-black"><?php echo $request_info1_text ?></p>

                    <?php
                    if ($hours_to_sans < URGENT_TIME) : ?>
                        <div class="bg-pinkk rounded-md py-[2px] px-[6px] text-white text-sx font-extrabold">فوری</div>
                    <?php
                    endif; ?>

                </div>
                <div class="flex justify-center items-center gap-[6px] bg-white rounded-[6px] py-[4px] px-2 ml-[120px]">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <rect width="20" height="20" rx="4" fill="white" />
                        <path d="M15 3.875C15 6.0716 13.5833 7.9363 11.6139 8.60743C11.2232 8.74057 10.9258 9.08723 10.9258 9.5C10.9258 9.91277 11.2232 10.2594 11.6139 10.3926C13.5833 11.0637 15 12.9284 15 15.125V16.625C15 17.1773 14.5523 17.625 14 17.625H6C5.44772 17.625 5 17.1773 5 16.625V15.125C5 12.9289 6.41599 11.0644 8.38467 10.3929C8.77562 10.2595 9.07324 9.91257 9.07324 9.4995C9.07324 9.08643 8.77564 8.73951 8.38469 8.60616C6.41606 7.93471 5 6.07103 5 3.875V2.375C5 1.82272 5.44772 1.375 6 1.375H14C14.5523 1.375 15 1.82272 15 2.375V3.875Z" fill="#889BAD" />
                        <path d="M12.1738 5.125C12.3539 5.125 12.5 5.27108 12.5 5.45117C12.5 6.65172 11.5267 7.62495 10.3262 7.625C10.1918 7.625 10.0852 7.73841 10.0936 7.87256L10.4883 14.1874C10.5212 14.7144 10.9583 15.125 11.4863 15.125H12.6631C13.2634 15.125 13.75 15.6116 13.75 16.2119C13.75 16.302 13.677 16.375 13.5869 16.375H6.41309C6.32304 16.375 6.25 16.302 6.25 16.2119C6.25002 15.6116 6.73664 15.125 7.33691 15.125H8.51367C9.04174 15.125 9.47879 14.7144 9.51173 14.1874L9.9064 7.87256C9.91479 7.73841 9.80824 7.625 9.67383 7.625C8.47328 7.62495 7.50005 6.65172 7.5 5.45117C7.5 5.27108 7.64608 5.125 7.82617 5.125H12.1738Z" fill="white" />
                    </svg>
                    <p class="text-[#889BAD] text-base font-extrabold"><?php echo human_time_diff($request['created_at'], $now) . ' قبل' ?></p>
                </div>
                <div class="flex justify-center bg-white gap-[6px] px-2 py-[4px] rounded-md">
                    <p class="text-base font-black"><?php echo $request_info2_text ?></p>
                    <?php if ($request_type == 'owner') : ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M14 7.50002V13.5C14 13.6326 13.9473 13.7598 13.8536 13.8536C13.7598 13.9473 13.6326 14 13.5 14H10C9.86739 14 9.74021 13.9473 9.64645 13.8536C9.55268 13.7598 9.5 13.6326 9.5 13.5V10.25C9.5 10.1837 9.47366 10.1201 9.42678 10.0732C9.37989 10.0264 9.3163 10 9.25 10H6.75C6.6837 10 6.62011 10.0264 6.57322 10.0732C6.52634 10.1201 6.5 10.1837 6.5 10.25V13.5C6.5 13.6326 6.44732 13.7598 6.35355 13.8536C6.25979 13.9473 6.13261 14 6 14H2.5C2.36739 14 2.24021 13.9473 2.14645 13.8536C2.05268 13.7598 2 13.6326 2 13.5V7.50002C2.00012 7.23486 2.10556 6.98059 2.29312 6.79315L7.29313 1.79315C7.48064 1.60576 7.7349 1.50049 8 1.50049C8.2651 1.50049 8.51936 1.60576 8.70687 1.79315L13.7069 6.79315C13.8944 6.98059 13.9999 7.23486 14 7.50002Z" fill="#FD7013" />
                        </svg>
                    <?php else : ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <circle cx="7.99941" cy="4.39999" r="3.6" fill="#2B7FFF" />
                            <ellipse cx="7.99961" cy="12.4" rx="6.4" ry="2.8" fill="#2B7FFF" />
                        </svg>
                    <?php endif; ?>
                </div>
            </div>
            <div class="flex justify-between px-[20px] my-[20px]">

                <p class="text-base font-extrabold">
                    درخواست لغو
                    <span class="text-[#889BAD] mx-2">سانس</span>
                    <?php echo parsidate('l', (int) $request['sans_time']); ?>
                    <span class="text-base font-black mx-2 text-orangee"><?php echo parsidate('j', (int) $request['sans_time']); ?></span>
                    <?php echo parsidate('F', (int) $request['sans_time']) . '-' . date('H:i', (int) $request['sans_time']); ?>
                    <span class="text-[#889BAD] mx-2"><?php echo ez_get_product_meta($product_id)->product_type; ?></span>
                    <?php echo get_the_title($product_id); ?>
                </p>

                <?php
                if ($request_type == 'customer') {
                    if ($under_24) { ?>
                        <p class="text-lg text-orangee font-extrabold">منتظر تایید مجموعه</p>

                    <?php
                    } else { ?>
                        <button class="openModalBtn admin_approval_btn text-base font-extrabold text-white bg-[#02C96F] px-4 py-3 rounded-xl cursor-pointer" data-request-id="<?php echo $request['ID']; ?>">تایید و لغو سانس</button>
                    <?php
                    }
                } else { ?>
                    <button class="openModalBtn text-base font-extrabold text-white bg-[#02C96F] px-4 py-3 rounded-xl cursor-pointer" data-request-id="<?php echo $request['ID']; ?>">تایید و لغو سانس!</button>
                <?php
                } ?>

            </div>
            <div class="flex items-center justify-center p-2 text-sm font-extrabold text-grayy gap-2 bg-[#F8FAFB] rounded-b-2.5xl cursor-pointer showDetailsBtn">
                مشاهده جزئیات
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-0 -mt-1" width="10" height="6" viewBox="0 0 10 6" fill="none">
                    <path d="M9 1L5.70711 4.29289C5.31658 4.68342 4.68342 4.68342 4.29289 4.29289L1 1" stroke="#0F172B" stroke-width="2" stroke-linecap="round" />
                </svg>
            </div>
            <div class="flex-col items-center justify-center bg-[#F8FAFB] px-[20px] pt-2 pb-[20px] description hidden">

                <div class="flex items-center justify-center p-[2px] text-sm font-extrabold text-grayy gap-[10px] text-right cursor-pointer mt-2 hideDetailsBtn">
                    بستن
                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-0 -mt-1" width="10" height="6" viewBox="0 0 10 6" fill="none">
                        <path d="M1 5L4.29289 1.70711C4.68342 1.31658 5.31658 1.31658 5.70711 1.70711L9 5" stroke="#0F172B" stroke-width="2" stroke-linecap="round" />
                    </svg>
                </div>
                <hr class="h-[3px] text-[#E4EBF0] my-[14px] mx-[20px]" />

                <div class="flex justify-between items-center mt-[14px] mx-[20px]">
                    <div class="flex justify-center gap-2">
                        <p class="text-sm font-extrabold text-grayy">کد رزرو</p>
                        <p class="text-base font-extrabold text-navyBlue"><?php echo $request['order_id'] ?></p>
                    </div>
                    <div class="flex justify-center gap-[13px]">
                        <p class="text-sm font-extrabold text-grayy">تاریخ رزرو</p>
                        <div class="flex justify-center gap-2">
                            <p class="text-base font-extrabold text-navyBlue"><?php echo parsidate('Y.m.d', $request['created_at'], 'fa') ?></p>
                            <p class="text-base font-extrabold text-navyBlue"><?php echo parsidate('H:i', $request['created_at'], 'fa') ?></p>
                        </div>
                    </div>
                    <div class="flex justify-center gap-2">
                        <p class="text-sm font-extrabold text-grayy">تعداد</p>
                        <p class="text-base font-extrabold text-navyBlue"><?php echo $quantity ?> بلیت</p>
                    </div>
                    <div class="flex">
                        <div class="flex flex-col">
                            <p class="text-lg font-extrabold text-navyBlue text-start"><?php echo $buyer ?></p>
                            <p class="text-base font-extrabold text-navyBlue text-start"><?php echo esc_html( $order->get_billing_phone() ); ?></p>
                        </div>

                        <?php
                        $badge_uid = (int) $order->get_user_id();
                        if ( $badge_uid > 0 && function_exists( 'ez_user_should_show_mojavezedar_badge' ) && ez_user_should_show_mojavezedar_badge( $badge_uid ) && function_exists( 'ez_get_mojavezedar_badge_display_parts' ) ) {
                            $m_parts    = ez_get_mojavezedar_badge_display_parts();
                            $themeColor = 'text-[#6D28D9]';
                            $themeText  = $m_parts['text'];
                            $badge_bg   = $m_parts['background'];
                        } else {
                            $user_level = get_user_level( $order->get_user_id() );
                            if ( $user_level == 1 ) {
                                $themeColor = 'text-[#858585]';
                                $themeText  = 'تازه وارد';
                            } elseif ( $user_level == 2 ) {
                                $themeColor = 'text-[#252728]';
                                $themeText  = 'نوپا';
                            } elseif ( $user_level == 3 ) {
                                $themeColor = 'text-[#00B2FF]';
                                $themeText  = 'با تجربه';
                            } else {
                                $themeColor = 'primary-500';
                                $themeText  = 'کارکشته';
                            }
                            $badge_bg = 'rgba(253, 112, 19, 0.2)';
                        }
                        ?>

                        <div class="rounded-[24px] h-5.5 px-2.5 leading-none content-center text-2xs text-center <?php echo esc_attr( $themeColor ); ?>" style="background-color: <?php echo esc_attr( $badge_bg ); ?>">
                            <?php echo esc_html( $themeText ); ?>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <p class="text-base font-extrabold text-navyBlue"><?php echo $brand_data->name ?></p>
                        <div class="flex justify-between">
                            <p class="text-base font-extrabold text-navyBlue"><?php echo get_field('room_phone', $product_id) ?></p>
                        </div>
                    </div>
                </div>

                <?php
                if ($request['reason_id']) : ?>

                    <hr class="h-[3px] text-[#E4EBF0] my-[14px] mx-[20px]" />
                    <div class="flex items-center gap-2 mx-[20px]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-0" width="20" height="21" viewBox="0 0 20 21" fill="none">
                            <path d="M10 11.9L12.9 14.8C13.0833 14.9833 13.3167 15.075 13.6 15.075C13.8833 15.075 14.1167 14.9833 14.3 14.8C14.4833 14.6167 14.575 14.3833 14.575 14.1C14.575 13.8167 14.4833 13.5833 14.3 13.4L11.4 10.5L14.3 7.6C14.4833 7.41667 14.575 7.18333 14.575 6.9C14.575 6.61667 14.4833 6.38333 14.3 6.2C14.1167 6.01667 13.8833 5.925 13.6 5.925C13.3167 5.925 13.0833 6.01667 12.9 6.2L10 9.1L7.1 6.2C6.91667 6.01667 6.68333 5.925 6.4 5.925C6.11667 5.925 5.88333 6.01667 5.7 6.2C5.51667 6.38333 5.425 6.61667 5.425 6.9C5.425 7.18333 5.51667 7.41667 5.7 7.6L8.6 10.5L5.7 13.4C5.51667 13.5833 5.425 13.8167 5.425 14.1C5.425 14.3833 5.51667 14.6167 5.7 14.8C5.88333 14.9833 6.11667 15.075 6.4 15.075C6.68333 15.075 6.91667 14.9833 7.1 14.8L10 11.9ZM10 20.5C8.61667 20.5 7.31667 20.2373 6.1 19.712C4.88334 19.1867 3.825 18.4743 2.925 17.575C2.025 16.6757 1.31267 15.6173 0.788001 14.4C0.263335 13.1827 0.000667933 11.8827 1.26582e-06 10.5C-0.000665401 9.11733 0.262001 7.81733 0.788001 6.6C1.314 5.38267 2.02633 4.32433 2.925 3.425C3.82367 2.52567 4.882 1.81333 6.1 1.288C7.318 0.762667 8.618 0.5 10 0.5C11.382 0.5 12.682 0.762667 13.9 1.288C15.118 1.81333 16.1763 2.52567 17.075 3.425C17.9737 4.32433 18.6863 5.38267 19.213 6.6C19.7397 7.81733 20.002 9.11733 20 10.5C19.998 11.8827 19.7353 13.1827 19.212 14.4C18.6887 15.6173 17.9763 16.6757 17.075 17.575C16.1737 18.4743 15.1153 19.187 13.9 19.713C12.6847 20.239 11.3847 20.5013 10 20.5Z" fill="#F21543" />
                        </svg>
                        <div class="flex items-center gap-[2px]">
                            <p class="text-sm font-extrabold text-grayy">دلیل لغو توسط مجموعه:</p>
                            <p class="text-navyBlue text-base font-extrabold"><?php echo $cancellation_reasons[$request['reason_id']] ?></p>
                        </div>
                    </div>

                <?php
                endif; ?>

            </div>
        </div>
    </section>

<?php endforeach; ?>
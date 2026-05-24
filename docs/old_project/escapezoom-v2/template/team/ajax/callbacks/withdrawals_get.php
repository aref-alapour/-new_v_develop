<?php
global $wpdb, $wldb;

$user_type  = sanitize_text_field($_POST['user_type']) ?: 'compiler';
$status     = sanitize_text_field($_POST['status']) ?: 'pending';
$page_num   = sanitize_text_field($_POST['page']) ?: 1;

$date_format = 'Y.m.d | H:i'; ?>

    <section class="mt-7">

        <?php
        if ($status == 'pending') : ?>

            <div id="tableHeader">
                <div class="grid grid-cols-[1fr,3fr,2fr,2fr,3fr,3fr,3fr,2fr,2fr,3fr] text-sm font-yekan-bold text-[#889BAD] font-semibold text-center bg-[#E8EDF1] w-full rounded-t-xl">
                    <p class="py-4">ردیف</p>

                    <?php
                    if ($user_type == 'compiler') : ?>
                        <p class="py-4">برند</p>
                    <?php
                    endif; ?>

                    <p class="py-4">آیدی کاربر</p>
                    <p class="py-4">شماره تراکنش</p>
                    <p class="py-4">زمان درخواست</p>
                    <p class="py-4">موجودی قبلی</p>
                    <p class="py-4 bg-[#FEAE1A] rounded-t-xl text-white">مبلغ درخواستی</p>
                    <p class="py-4">موجودی فعلی</p>
                    <p class="py-4">بابت</p>
                    <?php
                    if ( array_intersect( ['administrator', 'accounting'], wp_get_current_user()->roles ) ) : ?>
                        <p class="py-4">عملیات</p>
                    <?php
                    endif; ?>
                </div>
            </div>

            <div id="settlementTableBody" class="w-full h-full rounded-b-2.5xl">

                <?php
                $count = 0;

                $transactions = $wldb->get(array('type' => 'withdraw', 'status' => 'در حال پردازش'), 1000);
                if (!empty($transactions)) :

                    foreach ($transactions as $trans) :

                        $user_role = get_user_by('id', $trans->user_id)->roles[0];
                        if ($user_type != $user_role)
                            continue;

                        if ($user_role == 'customer')
                            $babat = $trans->user_id;

                        else {
                            $user_products = $wpdb->get_results("SELECT *  FROM `wp_postmeta` WHERE `meta_key` LIKE 'user_ebtal' AND `meta_value` LIKE {$trans->user_id}", ARRAY_A);

                            $active_products = [];
                            foreach ($user_products as $user_product) {
                                $product_id = $user_product['post_id'];

                                $is_active = get_post_meta($product_id, 'product_state', true) == 'active' ? 1 : 0;
                                $post_type = get_post_type($product_id);

                                if ($is_active && $post_type == 'product') {
                                    $active_products[]      = wc_get_product($product_id)->get_name();
                                    $active_products_id[$trans->ID][]   = $product_id;
                                }

                                $brand_data = get_the_terms($active_products_id[$trans->ID][0], 'yith_product_brand')[0];
                                $brand_names[$trans->ID] = $brand_data->name;
                            }

                            $babat = implode(' __ ', $active_products);
                        } ?>

                        <div data-id="<?php echo $trans->ID ?>" class="grid grid-cols-[1fr,3fr,2fr,2fr,3fr,3fr,3fr,2fr,2fr,3fr] text-sm font-yekan-bold text-grayy text-center">
                            <p class="py-4 text-[#889BAD]"><?php echo ++$count; ?></p>

                            <?php
                            if ($user_type == 'compiler') : ?>
                                <p class="py-4 text-navyBlue"><?php echo $brand_names[$trans->ID] ?></p>
                            <?php
                            endif; ?>

                            <p class="py-4 text-navyBlue"><a target="_blank" href="<?php echo home_url("team/transactions?user_id=$trans->user_id"); ?>"><?php echo $trans->user_id ?></a></p>
                            <p class="py-4 text-navyBlue"><?php echo $trans->ID ?></p>
                            <p class="py-4 text-navyBlue"><?php echo parsidate($date_format, $trans->created_at, 'fa') ?></p>
                            <p class="py-4 text-navyBlue"><?php echo number_format($trans->balance - $trans->amount) ?></p>
                            <p class="py-4 bg-[#FEAE1A66] text-navyBlue"><?php echo number_format(abs($trans->amount)) ?></p>
                            <p class="py-4 text-navyBlue"><?php echo number_format($trans->balance) ?></p>
                            <p class="py-4 text-navyBlue"><?php echo $babat ?></p>

                            <?php
                            if ( array_intersect( ['administrator', 'accounting'], wp_get_current_user()->roles ) ) : ?>

                                <div class="transaction_function flex justify-center py-4 gap-x-5">
                                    <button type="button" class="cursor-pointer shrink-0 grow-0 hover:opacity-80 w-9 h-9 btn-reject rounded-full border border-red-500/60 shadow-2 bg-white"
                                            data-trans_id="<?php echo $trans->ID; ?>"
                                            data-user_id=""
                                            data-role=""
                                            data-for=""
                                            data-op_type="refuse"
                                            title="رد">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                            <path d="M12.9961 1.36549C13.9831 0.378496 15.5833 0.378496 16.5703 1.36549C17.557 2.3525 17.5572 3.95281 16.5703 4.93971L12.3887 9.12135L16.5703 13.303C17.5572 14.2899 17.5571 15.8902 16.5703 16.8772C15.5833 17.8642 13.9831 17.8642 12.9961 16.8772L8.81446 12.6956L4.74806 16.762C3.76116 17.7489 2.16085 17.7487 1.17384 16.762C0.186846 15.775 0.186846 14.1747 1.17384 13.1878L5.24024 9.12135L1.17384 5.05494C0.186846 4.06795 0.186846 2.46771 1.17384 1.48072C2.16085 0.493963 3.76114 0.493808 4.74806 1.48072L8.81446 5.54713L12.9961 1.36549Z" fill="#FD2F5A" />
                                        </svg>
                                    </button>
                                    <button type="button" class="cursor-pointer shrink-0 grow-0 hover:opacity-80 w-9 h-9 btn-reject rounded-full border border-[#02C96F]/60 shadow-2 bg-white"
                                            data-trans_id="<?php echo $trans->ID; ?>"
                                            data-user_id="<?php echo $trans->user_id; ?>"
                                            data-role="<?php echo $user_role; ?>"
                                            data-for="<?php echo $babat; ?>"
                                            data-op_type="approve"
                                            title="تایید">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="21" height="16" viewBox="0 0 21 16" fill="none">
                                            <path d="M9.01161 15.1363L19.8361 3.83254C20.219 3.40406 20.4276 2.83707 20.4179 2.25111C20.4082 1.66514 20.181 1.10601 19.7842 0.6916C19.3873 0.277211 18.8519 0.0399398 18.2908 0.0298176C17.7297 0.0196954 17.1867 0.237513 16.7764 0.637342L7.48175 10.3435L3.96018 6.66602C3.54987 6.26619 3.00691 6.04837 2.44579 6.05849C1.88467 6.06861 1.34925 6.30588 0.952407 6.72027C0.555587 7.13468 0.328376 7.69381 0.318683 8.27978C0.30899 8.86574 0.517572 9.43273 0.900449 9.86121L5.95189 15.1363C6.35781 15.5597 6.90805 15.7975 7.48175 15.7975C8.05545 15.7975 8.60569 15.5597 9.01161 15.1363Z" fill="#02C96F" />
                                        </svg>
                                    </button>
                                </div>

                            <?php
                            endif; ?>

                        </div>

                    <?php
                    endforeach; ?>

                <?php
                else: ?>
                    <div class="col-span-11 text-center text-navyBlue font-yekan-bold py-4">هیچ درخواست تسویه حسابی نداریم!</div>
                <?php
                endif; ?>

            </div>

        <?php
        elseif ($status == 'paid') : ?>

            <div id="tableHeader">
                <div class="grid grid-cols-[1fr,4fr,2fr,2fr,4fr,4fr,6fr] text-sm font-yekan-bold text-[#889BAD] font-semibold text-center bg-[#E8EDF1] w-full rounded-t-xl">
                    <p class="py-4">ردیف</p>

                    <?php
                    if ($user_type == 'compiler') : ?>
                        <p class="py-4">برند</p>
                    <?php
                    endif; ?>

                    <p class="py-4">آیدی کاربر</p>
                    <p class="py-4">شماره تراکنش</p>
                    <p class="py-4">زمان درخواست</p>
                    <p class="py-4 bg-[#FEAE1A] rounded-t-xl text-white">مبلغ پرداخت شده</p>

                    <?php
                    if ($user_type == 'compiler') : ?>
                        <p class="py-4">بابت</p>
                    <?php
                    endif; ?>

                </div>
            </div>

            <div id="settlementTableBody" class="w-full h-full rounded-b-2.5xl">

                <?php
                $paid_trans_per_page = 100;
                $count = 0;

                // راه حلی به جز اضافه کردن ستون جدید به جدول کیف پول در دیتابیس جهت حل مشکل صفحه بندی در پرداخت شده هنگام تفکیک بر اساس یوز رول ها به ذهنم نرسید
                // که اصلا بهینه نیست. پس بر اساس دیتاهای قبلی با تقریب خوبی میدانیم که تقریبا تمام یوزر آی دی های زیر 10000 مجموعه دار هستند. و شرط دوم اینکه
                // پلیرها معمولا زیر 1 میلیون تومن درخواست برداشت دارند.
                if ($user_type == 'customer')
                    $total_pages  = ceil($wpdb->get_var("SELECT COUNT(*) FROM `wallet_transactions` WHERE `status` LIKE '%انجام شد%' AND user_id > 10000 AND amount <= 1000000") / $paid_trans_per_page);
                else if ($user_type == 'compiler')
                    $total_pages  = ceil($wpdb->get_var("SELECT COUNT(*) FROM `wallet_transactions` WHERE `status` LIKE '%انجام شد%' AND user_id <= 10000") / $paid_trans_per_page);

                $transactions = $wldb->get(array('type' => 'withdraw', 'status' => 'انجام شد'), $paid_trans_per_page, false, $page_num);
                foreach ($transactions as $trans) :
                    $user_products = $wpdb->get_results("SELECT *  FROM `wp_postmeta` WHERE `meta_key` LIKE 'user_ebtal' AND `meta_value` LIKE {$trans->user_id}", ARRAY_A);

                    $user_role = get_user_by('id', $trans->user_id)->roles[0];
                    if ($user_type != $user_role)
                        continue;

                    if ($user_role == 'customer')
                        $babat = $trans->user_id;

                    $active_products = [];
                    foreach ($user_products as $user_product) {
                        $product_id = $user_product['post_id'];

                        $is_active = get_post_meta($product_id, 'product_state', true) == 'active' ? 1 : 0;
                        $post_type = get_post_type($product_id);

                        if ($is_active && $post_type == 'product') {
                            $active_products[]      = wc_get_product($product_id)->get_name();
                            $active_products_id[$trans->ID][]   = $product_id;
                        }

                        $brand_data = get_the_terms($active_products_id[$trans->ID][0], 'yith_product_brand')[0];
                        $brand_names[$trans->ID] = $brand_data->name;
                    } ?>

                    <div data-id="<?php echo $trans->ID ?>" class="grid grid-cols-[1fr,4fr,2fr,2fr,4fr,4fr,6fr] text-sm font-yekan-bold text-grayy text-center">
                        <p class="py-4 text-[#889BAD]"><?php echo ++$count; ?></p>

                        <?php
                        if ($user_type == 'compiler') : ?>
                            <p class="py-4 text-navyBlue"><?php echo $brand_names[$trans->ID] ?></p>
                        <?php
                        endif; ?>

                        <p class="py-4 text-navyBlue"><?php echo $trans->user_id ?></p>
                        <p class="py-4 text-navyBlue"><?php echo $trans->ID ?></p>
                        <p class="py-4 text-navyBlue"><?php echo parsidate($date_format, $trans->created_at, 'fa') ?></p>
                        <p class="py-4 bg-[#FEAE1A66] text-navyBlue"><?php echo number_format(abs($trans->amount)) ?></p>

                        <?php
                        if ($user_type == 'compiler') : ?>
                            <p class="py-4 text-navyBlue"><?php echo implode(' __ ', $active_products) ?></p>
                        <?php
                        endif; ?>

                    </div>

                <?php
                endforeach; ?>
            </div>

        <?php
        elseif ($status == 'rejected') : ?>

            <div id="tableHeader">
                <div class="grid grid-cols-[1fr,4fr,2fr,2fr,4fr,4fr,6fr] text-sm font-yekan-bold text-[#889BAD] font-semibold text-center bg-[#E8EDF1] w-full rounded-t-xl">
                    <p class="py-4">ردیف</p>

                    <?php
                    if ($user_type == 'compiler') : ?>
                        <p class="py-4">برند</p>
                    <?php
                    endif; ?>

                    <p class="py-4">آیدی کاربر</p>
                    <p class="py-4">شماره تراکنش</p>
                    <p class="py-4">زمان درخواست</p>
                    <p class="py-4 bg-[#FEAE1A] rounded-t-xl text-white">مبلغ پرداخت شده</p>

                    <?php
                    if ($user_type == 'compiler') : ?>
                        <p class="py-4">بابت</p>
                    <?php
                    endif; ?>

                </div>
            </div>

            <div id="settlementTableBody" class="w-full h-full rounded-b-2.5xl">

                <?php
                $paid_trans_per_page = 100;
                $count = 0;

                // راه حلی به جز اضافه کردن ستون جدید به جدول کیف پول در دیتابیس جهت حل مشکل صفحه بندی در پرداخت شده هنگام تفکیک بر اساس یوز رول ها به ذهنم نرسید
                // که اصلا بهینه نیست. پس بر اساس دیتاهای قبلی با تقریب خوبی میدانیم که تقریبا تمام یوزر آی دی های زیر 10000 مجموعه دار هستند. و شرط دوم اینکه
                // پلیرها معمولا زیر 1 میلیون تومن درخواست برداشت دارند.
                if ($user_type == 'customer')
                    $total_pages  = ceil($wpdb->get_var("SELECT COUNT(*) FROM `wallet_transactions` WHERE `status` LIKE '%رد شده%' AND user_id > 10000 AND amount <= 1000000") / $paid_trans_per_page);
                else if ($user_type == 'compiler')
                    $total_pages  = ceil($wpdb->get_var("SELECT COUNT(*) FROM `wallet_transactions` WHERE `status` LIKE '%رد شده%' AND user_id <= 10000") / $paid_trans_per_page);

                $transactions = $wldb->get(array('type' => 'withdraw', 'status' => 'رد شده'), $paid_trans_per_page, false, $page_num);
                foreach ($transactions as $trans) :
                    $user_products = $wpdb->get_results("SELECT *  FROM `wp_postmeta` WHERE `meta_key` LIKE 'user_ebtal' AND `meta_value` LIKE {$trans->user_id}", ARRAY_A);

                    $user_role = get_user_by('id', $trans->user_id)->roles[0];
                    if ($user_type != $user_role)
                        continue;

                    if ($user_role == 'customer')
                        $babat = $trans->user_id;

                    $active_products = [];
                    foreach ($user_products as $user_product) {
                        $product_id = $user_product['post_id'];

                        $is_active = get_post_meta($product_id, 'product_state', true) == 'active' ? 1 : 0;
                        $post_type = get_post_type($product_id);

                        if ($is_active && $post_type == 'product') {
                            $active_products[]      = wc_get_product($product_id)->get_name();
                            $active_products_id[$trans->ID][]   = $product_id;
                        }

                        $brand_data = get_the_terms($active_products_id[$trans->ID][0], 'yith_product_brand')[0];
                        $brand_names[$trans->ID] = $brand_data->name;
                    } ?>

                    <div data-id="<?php echo $trans->ID ?>" class="grid grid-cols-[1fr,4fr,2fr,2fr,4fr,4fr,6fr] text-sm font-yekan-bold text-grayy text-center">
                        <p class="py-4 text-[#889BAD]"><?php echo ++$count; ?></p>

                        <?php
                        if ($user_type == 'compiler') : ?>
                            <p class="py-4 text-navyBlue"><?php echo $brand_names[$trans->ID] ?></p>
                        <?php
                        endif; ?>

                        <p class="py-4 text-navyBlue"><?php echo $trans->user_id ?></p>
                        <p class="py-4 text-navyBlue"><?php echo $trans->ID ?></p>
                        <p class="py-4 text-navyBlue"><?php echo parsidate($date_format, $trans->created_at, 'fa') ?></p>
                        <p class="py-4 bg-[#FEAE1A66] text-navyBlue"><?php echo number_format(abs($trans->amount)) ?></p>

                        <?php
                        if ($user_type == 'compiler') : ?>
                            <p class="py-4 text-navyBlue"><?php echo implode(' __ ', $active_products) ?></p>
                        <?php
                        endif; ?>

                    </div>

                <?php
                endforeach; ?>
            </div>

        <?php
        endif; ?>

    </section>

<?php if ($total_pages > 1) { ?>
    <div class="mb-9 flex w-full items-center justify-center gap-4">
        <div class="flex gap-4 max-lg:gap-2 mt-16 justify-start max-lg:justify-center pagination">
            <?php echo paginate_links([
                'mid_size'  => 1,
                'base'      => get_pagenum_link(1) . '%_%',
                'format'    => '?page=%#%',
                'current'   => max(1, $page_num),
                'total'     => $total_pages,
                'prev_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none" class="rotate-180 opacity-25"><path d="M5.08008 11.1602L1.51062 7.14452C1.17384 6.76563 1.17384 6.19468 1.51062 5.81579L5.08008 1.80016" stroke="#0A184A" stroke-width="2" stroke-linecap="round"></path></svg>',
                'next_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none"><path d="M5.08008 11.1602L1.51062 7.14452C1.17384 6.76563 1.17384 6.19468 1.51062 5.81579L5.08008 1.80016" stroke="#0A184A" stroke-width="2" stroke-linecap="round"></path></svg>',
            ]); ?>
        </div>
    </div>
<?php } ?>
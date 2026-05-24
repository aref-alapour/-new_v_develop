<?php
global $wpdb, $wldb;

$term       = sanitize_text_field($_POST['term']);
$user_type  = sanitize_text_field($_POST['user_type']) ? : 'compiler';
$status     = sanitize_text_field($_POST['status']) ? : 'pending';
$page_num   = sanitize_text_field($_POST['page']) ?: 1;

$date_format = 'Y.m.d | H:i';

// اگر آی دی محصول فرستاده شد به این معنی هست که از لیست نتایج جستجوی قبل یک مورد انتخاب شده. بعد از یافتن صاحب این بازی، آی دی صاحب بازی رو به عنوان عبارت جستجو جا میزنیم
if ( isset( $_POST['product_id'] ) )
    $term = get_post_meta($_POST['product_id'], 'user_ebtal', true);

if (!ctype_digit($term)) { // اگر عبارت جستجو عددی نبود ادمین دنبال اسم یک بازی است. 

    $like = '%' . $wpdb->esc_like($term) . '%';
    $results = $wpdb->get_results(
        $wpdb->prepare("
        SELECT ID, post_title FROM $wpdb->posts
        WHERE post_type = 'product'
        AND post_status = 'publish'
        AND post_title LIKE %s
    ", $like)
    );

    $res = '';
    if ($results)
        foreach ($results as $row)
            $res .= '<p style="cursor:pointer" data-product_id="' . $row->ID . '">' . esc_html($row->post_title) . '</p>';

    wp_send_json_success([
        'inline' => true,
        'html'   => $res
    ]);
}

?>

<section class="grid justify-center items-center mt-7">

    <?php
    if ( $status == 'pending' ) : ?>

        <div id="tableHeader" class="px-[34px]">
            <div class="grid grid-cols-[46px_140px_71px_91px_143px_99px_126px_99px_112px_63px_104px] text-sm font-yekan-bold text-grayy text-center bg-[#889BAD]">
                <p class="py-4">ردیف</p>

                <?php
                if ( $user_type == 'compiler' ) : ?>
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
                <p class="py-4">عملیات</p>
            </div>
        </div>

        <div id="settlementTableBody" class="w-full h-full px-[34px] rounded-b-2.5xl grid">

            <?php
            $count = 0;

            $transactions = $wpdb->get_results($wpdb->prepare("SELECT * FROM `wallet_transactions` WHERE `status` LIKE %s AND user_id = %d ORDER BY `ID` DESC", 'در حال پردازش', $term));
            if ( !empty($transactions) ) :

                foreach ( $transactions as $trans ) :

                    $user_role = get_user_by('id', $trans->user_id)->roles[0];
                    if ( $user_type != $user_role )
                        continue;

                    if ( $user_role == 'customer' )
                        $babat = $trans->user_id;

                    else {
                        $user_products = $wpdb->get_results( "SELECT *  FROM `wp_postmeta` WHERE `meta_key` LIKE 'user_ebtal' AND `meta_value` LIKE {$trans->user_id}", ARRAY_A );

                        $active_products = [];
                        foreach ( $user_products as $user_product ) {
                            $product_id = $user_product['post_id'];

                            $is_active = get_post_meta($product_id, 'product_state', true) == 'active' ? 1 : 0;
                            $post_type = get_post_type($product_id);

                            if ( $is_active && $post_type == 'product' ) {
                                $active_products[]      = wc_get_product($product_id)->get_name();
                                $active_products_id[$trans->ID][]   = $product_id;
                            }

                            $brand_data = get_the_terms($active_products_id[$trans->ID][0], 'yith_product_brand')[0];
                            $brand_names[$trans->ID] = $brand_data->name;
                        }

                        $babat = implode(' __ ', $active_products);
                    } ?>

                    <div data-id="<?php echo $trans->ID?>" class="grid grid-cols-[46px_140px_71px_91px_143px_99px_126px_99px_112px_63px_104px] text-sm font-yekan-bold text-grayy text-center">
                        <p class="py-4 text-[#889BAD]"><?php echo ++$count; ?></p>

                        <?php
                        if ( $user_type == 'compiler' ) : ?>
                            <p class="py-4 text-navyBlue"><?php echo $brand_names[$trans->ID] ?></p>
                        <?php
                        endif; ?>

                        <p class="py-4 text-navyBlue"><?php echo $trans->user_id ?></p>
                        <p class="py-4 text-navyBlue"><?php echo $trans->ID ?></p>
                        <p class="py-4 text-navyBlue"><?php echo parsidate($date_format, $trans->created_at, 'fa') ?></p>
                        <p class="py-4 text-navyBlue"><?php echo number_format( $trans->balance - $trans->amount ) ?></p>
                        <p class="py-4 bg-[#FEAE1A66] text-navyBlue"><?php echo number_format( abs($trans->amount) ) ?></p>
                        <p class="py-4 text-navyBlue"><?php echo number_format( $trans->balance ) ?></p>
                        <p class="py-4 text-navyBlue"><?php echo $babat ?></p>
                        <div class="transaction_function flex justify-center py-4 space-x-2">

                            <img src="./assets/images/close-red-minimal.svg" class="cursor-pointer hover:opacity-80 w-9 h-9 btn-reject"
                                data-trans_id="<?php echo $trans->ID; ?>"
                                data-user_id=""
                                data-role=""
                                data-for=""
                                data-op_type="refuse"
                                title="رد"
                            />

                            <img src="./assets/images/sucsses.svg" class="cursor-pointer hover:opacity-80 w-9 h-9 btn-approve"
                                data-trans_id="<?php echo $trans->ID; ?>"
                                data-user_id="<?php echo $trans->user_id; ?>"
                                data-role="<?php echo $user_role; ?>"
                                data-for="<?php echo $babat; ?>"
                                data-op_type="approve"
                                title="تایید"
                            />

                        </div>
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
    elseif ( $status == 'paid' ) : ?>

        <div id="tableHeader" class="px-[34px]">
            <div class="grid grid-cols-[46px_140px_71px_91px_143px_99px_126px_99px_112px_63px_104px] text-sm font-yekan-bold text-grayy text-center bg-[#889BAD]">
                <p class="py-4">ردیف</p>

                <?php
                if ( $user_type == 'compiler' ) : ?>
                    <p class="py-4">برند</p>
                <?php
                endif; ?>

                <p class="py-4">آیدی کاربر</p>
                <p class="py-4">شماره تراکنش</p>
                <p class="py-4">زمان درخواست</p>
                <p class="py-4 bg-[#FEAE1A] rounded-t-xl text-white">مبلغ پرداخت شده</p>

                <?php
                if ( $user_type == 'compiler' ) : ?>
                    <p class="py-4">بابت</p>
                <?php
                endif; ?>

            </div>
        </div>

        <div id="settlementTableBody" class="w-full h-full px-[34px] rounded-b-2.5xl grid">

            <?php
            $paid_trans_per_page = 500;
            $count = 0;

            $transactions = $wpdb->get_results($wpdb->prepare("SELECT * FROM `wallet_transactions` WHERE `status` LIKE %s AND user_id = %d ORDER BY `ID` DESC", 'انجام شد', $term));
            $total_pages  = ceil( $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM `wallet_transactions` WHERE `status` LIKE %s AND user_id = %d", 'انجام شد', $term) ) / $paid_trans_per_page );

            foreach ( $transactions as $trans ) :
                $user_products = $wpdb->get_results( "SELECT *  FROM `wp_postmeta` WHERE `meta_key` LIKE 'user_ebtal' AND `meta_value` LIKE {$trans->user_id}", ARRAY_A );

                $user_role = get_user_by('id', $trans->user_id)->roles[0];
                if ( $user_type != $user_role )
                    continue;

                if ( $user_role == 'customer' )
                    $babat = $trans->user_id;

                $active_products = [];
                foreach ( $user_products as $user_product ) {
                    $product_id = $user_product['post_id'];

                    $is_active = get_post_meta($product_id, 'product_state', true) == 'active' ? 1 : 0;
                    $post_type = get_post_type($product_id);

                    if ( $is_active && $post_type == 'product' ) {
                        $active_products[]      = wc_get_product($product_id)->get_name();
                        $active_products_id[$trans->ID][]   = $product_id;
                    }

                    $brand_data = get_the_terms($active_products_id[$trans->ID][0], 'yith_product_brand')[0];
                    $brand_names[$trans->ID] = $brand_data->name;
                } ?>

                <div data-id="<?php echo $trans->ID?>" class="grid grid-cols-[46px_140px_71px_91px_143px_99px_126px_99px_112px_63px_104px] text-sm font-yekan-bold text-grayy text-center">
                    <p class="py-4 text-[#889BAD]"><?php echo ++$count; ?></p>

                    <?php
                    if ( $user_type == 'compiler' ) : ?>
                        <p class="py-4 text-navyBlue"><?php echo $brand_names[$trans->ID] ?></p>
                    <?php
                    endif; ?>

                    <p class="py-4 text-navyBlue"><?php echo $trans->user_id ?></p>
                    <p class="py-4 text-navyBlue"><?php echo $trans->ID ?></p>
                    <p class="py-4 text-navyBlue"><?php echo parsidate($date_format, $trans->created_at, 'fa') ?></p>
                    <p class="py-4 bg-[#FEAE1A66] text-navyBlue"><?php echo number_format( abs($trans->amount) ) ?></p>

                    <?php
                    if ( $user_type == 'compiler' ) : ?>
                        <p class="py-4 text-navyBlue"><?php echo implode(' __ ', $active_products) ?></p>
                    <?php
                    endif; ?>

                </div>

            <?php
            endforeach; ?>
        </div>

    <?php
    elseif ( $status == 'rejected' ) : ?>

        <div id="tableHeader" class="px-[34px]">
            <div class="grid grid-cols-[46px_140px_71px_91px_143px_99px_126px_99px_112px_63px_104px] text-sm font-yekan-bold text-grayy text-center bg-[#889BAD]">
                <p class="py-4">ردیف</p>

                <?php
                if ( $user_type == 'compiler' ) : ?>
                    <p class="py-4">برند</p>
                <?php
                endif; ?>

                <p class="py-4">آیدی کاربر</p>
                <p class="py-4">شماره تراکنش</p>
                <p class="py-4">زمان درخواست</p>
                <p class="py-4 bg-[#FEAE1A] rounded-t-xl text-white">مبلغ پرداخت شده</p>

                <?php
                if ( $user_type == 'compiler' ) : ?>
                    <p class="py-4">بابت</p>
                <?php
                endif; ?>

            </div>
        </div>

        <div id="settlementTableBody" class="w-full h-full px-[34px] rounded-b-2.5xl grid">

            <?php
            $paid_trans_per_page = 100;
            $count = 0;

            $transactions = $wpdb->get_results($wpdb->prepare("SELECT * FROM `wallet_transactions` WHERE `status` LIKE %s AND user_id = %d ORDER BY `ID` DESC", 'رد شده', $term));
            $total_pages  = ceil( $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM `wallet_transactions` WHERE `status` LIKE %s AND user_id = %d", 'رد شده', $term) ) / $paid_trans_per_page );

            foreach ( $transactions as $trans ) :
                $user_products = $wpdb->get_results( "SELECT *  FROM `wp_postmeta` WHERE `meta_key` LIKE 'user_ebtal' AND `meta_value` LIKE {$trans->user_id}", ARRAY_A );

                $user_role = get_user_by('id', $trans->user_id)->roles[0];
                if ( $user_type != $user_role )
                    continue;

                if ( $user_role == 'customer' )
                    $babat = $trans->user_id;

                $active_products = [];
                foreach ( $user_products as $user_product ) {
                    $product_id = $user_product['post_id'];

                    $is_active = get_post_meta($product_id, 'product_state', true) == 'active' ? 1 : 0;
                    $post_type = get_post_type($product_id);

                    if ( $is_active && $post_type == 'product' ) {
                        $active_products[]      = wc_get_product($product_id)->get_name();
                        $active_products_id[$trans->ID][]   = $product_id;
                    }

                    $brand_data = get_the_terms($active_products_id[$trans->ID][0], 'yith_product_brand')[0];
                    $brand_names[$trans->ID] = $brand_data->name;
                } ?>

                <div data-id="<?php echo $trans->ID?>" class="grid grid-cols-[46px_140px_71px_91px_143px_99px_126px_99px_112px_63px_104px] text-sm font-yekan-bold text-grayy text-center">
                    <p class="py-4 text-[#889BAD]"><?php echo ++$count; ?></p>

                    <?php
                    if ( $user_type == 'compiler' ) : ?>
                        <p class="py-4 text-navyBlue"><?php echo $brand_names[$trans->ID] ?></p>
                    <?php
                    endif; ?>

                    <p class="py-4 text-navyBlue"><?php echo $trans->user_id ?></p>
                    <p class="py-4 text-navyBlue"><?php echo $trans->ID ?></p>
                    <p class="py-4 text-navyBlue"><?php echo parsidate($date_format, $trans->created_at, 'fa') ?></p>
                    <p class="py-4 bg-[#FEAE1A66] text-navyBlue"><?php echo number_format( abs($trans->amount) ) ?></p>

                    <?php
                    if ( $user_type == 'compiler' ) : ?>
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
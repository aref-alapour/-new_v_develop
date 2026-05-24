<?php

require_once __DIR__ . '/team_callback_bootstrap.php';

$medoo = medoo();

$user_id   = (int) ( $_POST['user_id'] ?? 0 );
$data_type = sanitize_text_field( $_POST['data_type'] ?? '' );
$page_num  = max( 1, (int) ( $_POST['page'] ?? 1 ) );

if ( $user_id <= 0 ) {
    echo '<div class="text-red-500 text-sm py-4">کاربر نامعتبر است.</div>';
    return;
}

/**
 * @param array<string,mixed>|object $row
 */
if ( ! function_exists( 'ez_team_trans_row_val' ) ) {
function ez_team_trans_row_val( $row, string $key ) {
    if ( is_array( $row ) ) {
        return $row[ $key ] ?? null;
    }
    if ( is_object( $row ) ) {
        return $row->$key ?? null;
    }
    return null;
}
}

if ( $data_type === 'user_info' ) :

    $user_row = $medoo->get(
        'wp_users',
        array( 'ID', 'user_login', 'display_name' ),
        array( 'ID' => $user_id )
    );

    if ( empty( $user_row ) ) {
        echo '<div class="text-red-500 text-sm">کاربر یافت نشد.</div>';
        return;
    }

    $meta_rows = $medoo->select(
        'wp_usermeta',
        array( 'meta_key', 'meta_value' ),
        array(
            'user_id'  => $user_id,
            'meta_key' => array(
                'first_name',
                'last_name',
                'billing_first_name',
                'billing_last_name',
                'billing_city',
            ),
        )
    );

    $meta = array();
    foreach ( (array) $meta_rows as $m ) {
        $meta[ (string) $m['meta_key'] ] = (string) $m['meta_value'];
    }

    $user_login = (string) $user_row['user_login'];

    $full_name_display = trim(
        ( $meta['first_name'] ?? '' ) . ' ' . ( $meta['last_name'] ?? '' )
    );
    if ( $full_name_display === '' ) {
        $full_name_display = trim(
            ( $meta['billing_first_name'] ?? '' ) . ' ' . ( $meta['billing_last_name'] ?? '' )
        );
    }
    if ( $full_name_display === '' ) {
        $full_name_display = (string) $user_row['display_name'];
    }

    $balance = (int) $medoo->get(
        'wallet_transactions',
        'balance',
        array(
            'user_id' => $user_id,
            'ORDER'   => array( 'ID' => 'DESC' ),
        )
    );

    $billing_city = trim( (string) ( $meta['billing_city'] ?? '' ) );
    if ( $billing_city === '' ) {
        $billing_city = '----------';
    }

    $mobile_display = ( strpos( $user_login, '0' ) === 0 ) ? $user_login : '0' . $user_login;
    ?>

    <div class="flex items-center gap-2">
        <p class="text-sm font-yekan-bold text-grayy text-nowrap">نام</p>
        <p class="text-base font-yekan-heavy text-nowrap"><?php echo esc_html( $full_name_display ); ?></p>
    </div>

    <svg xmlns="http://www.w3.org/2000/svg" width="2" height="32" viewBox="0 0 2 32" fill="none" class="mx-6">
        <path d="M1 0.999998L1 31" stroke="#E2E8F0" stroke-width="2" stroke-linecap="round" />
    </svg>

    <div class="flex items-center gap-2">
        <p class="text-sm font-yekan-bold text-grayy text-nowrap">اعتبار فعلی</p>
        <p class="text-base font-yekan-heavy"><?php echo number_format( $balance ); ?></p>
    </div>

    <svg xmlns="http://www.w3.org/2000/svg" width="2" height="32" viewBox="0 0 2 32" fill="none" class="mx-6">
        <path d="M1 0.999998L1 31" stroke="#E2E8F0" stroke-width="2" stroke-linecap="round" />
    </svg>

    <div class="flex items-center gap-2">
        <p class="text-sm font-yekan-bold text-grayy text-nowrap">شهر</p>
        <p class="text-base font-yekan-heavy text-nowrap"><?php echo esc_html( $billing_city ); ?></p>
    </div>

    <svg xmlns="http://www.w3.org/2000/svg" width="2" height="32" viewBox="0 0 2 32" fill="none" class="mx-6">
        <path d="M1 0.999998L1 31" stroke="#E2E8F0" stroke-width="2" stroke-linecap="round" />
    </svg>

    <div class="flex items-center gap-2">
        <p class="text-sm font-yekan-bold text-grayy text-nowrap">موبایل</p>
        <p class="text-base font-yekan-heavy text-nowrap"><?php echo esc_html( $mobile_display ); ?></p>
    </div>

    <?php
else :

    $export_all = ! empty( $_POST['export_all'] ) && $_POST['export_all'] === 'true';
    $per_page   = 50;
    $offset     = ( $page_num - 1 ) * $per_page;

    if ( $export_all ) {
        $transactions       = $medoo->select(
            'wallet_transactions',
            '*',
            array(
                'user_id' => $user_id,
                'ORDER'   => array( 'ID' => 'DESC' ),
            )
        );
        $transactions       = is_array( $transactions ) ? $transactions : array();
        $total_transactions = count( $transactions );
        $total_pages        = 1;
    } else {
        $total_transactions = (int) $medoo->count(
            'wallet_transactions',
            array( 'user_id' => $user_id )
        );
        $total_pages = $total_transactions > 0
            ? (int) ceil( $total_transactions / $per_page )
            : 1;

        $transactions = $medoo->select(
            'wallet_transactions',
            '*',
            array(
                'user_id' => $user_id,
                'ORDER'   => array( 'ID' => 'DESC' ),
                'LIMIT'   => array( $offset, $per_page ),
            )
        );
        $transactions = is_array( $transactions ) ? $transactions : array();
    }
    ?>

    <div class="w-full">
        <div class="flex justify-between items-center px-6 py-4 border-b border-[#E4EBF0]">
            <h2 class="text-base font-yekan-bold text-navyBlue">تراکنش‌های کاربر</h2>
            <button id="export-excel-user-transactions" class="px-4 py-2 bg-green-500 text-white rounded-lg text-sm font-yekan-bold hover:bg-green-600 transition-colors">
                خروجی اکسل
            </button>
        </div>
        <div class="w-full bg-[#F1F5F9] rounded-t-xl">
            <div class="grid grid-cols-[60px_180px_100px_120px_100px_120px_120px_120px_120px] gap-4 px-6 py-4 text-sm font-yekan-bold text-[#64748B]">
                <div class="text-center">ردیف</div>
                <div class="text-center">شماره تراکنش</div>
                <div class="text-center">زمان درخواست</div>
                <div class="text-center">اضافه/کسر</div>
                <div class="text-center">مبلغ</div>
                <div class="text-center">موجودی قبلی</div>
                <div class="text-center">موجودی فعلی</div>
                <div class="text-center">بابت</div>
                <div class="text-center">وضعیت</div>
            </div>
        </div>

    <?php
    if ( ! empty( $transactions ) ) :
        foreach ( $transactions as $key => $trans ) :
            $row_number = $offset + $key + 1;
            $trans_id   = (int) ez_team_trans_row_val( $trans, 'ID' );
            $amount     = (int) ez_team_trans_row_val( $trans, 'amount' );
            $balance    = (int) ez_team_trans_row_val( $trans, 'balance' );
            $created_at = ez_team_trans_row_val( $trans, 'created_at' );
            $description = (string) ez_team_trans_row_val( $trans, 'description' );
            $status_raw = ez_team_trans_row_val( $trans, 'status' );
            $status_text = $status_raw !== null && $status_raw !== '' ? (string) $status_raw : 'انجام شد';

            if ( $status_text === 'در حال پردازش' ) {
                $status_class = 'text-[#C29D04]';
            } elseif ( $status_text === 'انجام شد' ) {
                $status_class = 'text-[#16A34A]';
            } elseif ( $status_text === 'لغو شد' ) {
                $status_class = 'text-[#DC2626]';
            } else {
                $status_class = 'text-[#64748B]';
            }
            ?>

            <div class="data-row w-full" style="background-color: <?php echo $key % 2 === 0 ? '#FFFFFF' : '#F8FAFC'; ?>;">
                <div class="grid grid-cols-[60px_180px_100px_120px_100px_120px_120px_120px_120px] gap-4 px-6 py-4 text-sm font-yekan-bold">
                    <div class="text-center text-[#1E293B] flex items-center justify-center">
                        <?php echo (int) $row_number; ?>
                    </div>
                    <div class="text-center text-[#1E293B] flex items-center justify-center">
                        <?php echo (int) $trans_id; ?>
                    </div>
                    <div class="text-center text-[#1E293B] flex items-center justify-center">
                        <?php echo parsidate( 'j M | H:i', $created_at, 'fa' ); ?>
                    </div>
                    <div class="text-center flex items-center justify-center">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded text-white text-xs font-yekan-heavy <?php echo $amount > 0 ? 'bg-[#10B981]' : 'bg-[#EF4444]'; ?>" style="border-radius: 4px;">
                            <?php echo $amount > 0 ? '+' : '-'; ?>
                        </span>
                    </div>
                    <div class="text-center text-[#1E293B] flex items-center justify-center">
                        <?php echo ( $amount > 0 ? '+' : '-' ) . number_format( abs( $amount ) ); ?>
                    </div>
                    <div class="text-center text-[#1E293B] flex items-center justify-center">
                        <?php echo number_format( $balance - $amount ); ?>
                    </div>
                    <div class="text-center text-[#1E293B] flex items-center justify-center">
                        <?php echo number_format( $balance ); ?>
                    </div>
                    <div class="text-center text-[#1E293B] flex items-center justify-center">
                        <?php echo esc_html( $description ); ?>
                    </div>
                    <div class="text-center flex items-center justify-center">
                        <span class="text-xs font-yekan-bold <?php echo esc_attr( $status_class ); ?>">
                            <?php echo esc_html( $status_text ); ?>
                        </span>
                    </div>
                </div>
            </div>

        <?php
        endforeach;

        if ( $total_pages > 1 ) :
            ?>
            <div class="mb-9 flex w-full items-center justify-center gap-4">
                <div class="flex gap-4 max-lg:gap-2 mt-16 justify-start max-lg:justify-center pagination">

                    <?php if ( $page_num > 1 ) { ?>
                        <a href="javascript:void(0)" data-page="<?php echo (int) ( $page_num - 1 ); ?>" class="pagination-link flex items-center justify-center w-8 h-8 rounded-lg border border-[#E2E8F0] text-[#64748B] hover:bg-[#F1F5F9] transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none" class="rotate-180 opacity-25">
                                <path d="M5.08008 11.1602L1.51062 7.14452C1.17384 6.76563 1.17384 6.19468 1.51062 5.81579L5.08008 1.80016" stroke="#0A184A" stroke-width="2" stroke-linecap="round"></path>
                            </svg>
                        </a>
                    <?php } ?>

                    <?php
                    $start = max( 1, $page_num - 2 );
                    $end   = min( $total_pages, $page_num + 2 );

                    for ( $i = $start; $i <= $end; $i++ ) {
                        if ( $i === $page_num ) {
                            ?>
                            <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-[#FF6900] text-white text-sm font-yekan-bold"><?php echo (int) $i; ?></span>
                            <?php
                        } else {
                            ?>
                            <a href="javascript:void(0)" data-page="<?php echo (int) $i; ?>" class="pagination-link flex items-center justify-center w-8 h-8 rounded-lg border border-[#E2E8F0] text-[#64748B] hover:bg-[#F1F5F9] transition-colors text-sm font-yekan-bold"><?php echo (int) $i; ?></a>
                            <?php
                        }
                    }
                    ?>

                    <?php if ( $page_num < $total_pages ) { ?>
                        <a href="javascript:void(0)" data-page="<?php echo (int) ( $page_num + 1 ); ?>" class="pagination-link flex items-center justify-center w-8 h-8 rounded-lg border border-[#E2E8F0] text-[#64748B] hover:bg-[#F1F5F9] transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none">
                                <path d="M5.08008 11.1602L1.51062 7.14452C1.17384 6.76563 1.17384 6.19468 1.51062 5.81579L5.08008 1.80016" stroke="#0A184A" stroke-width="2" stroke-linecap="round"></path>
                            </svg>
                        </a>
                    <?php } ?>

                </div>
            </div>
            <?php
        endif;
    else :
        ?>
        <div class="px-6 py-8 text-center text-sm text-[#64748B]">تراکنشی یافت نشد.</div>
        <?php
    endif;
    ?>
    </div>
    <?php
endif;

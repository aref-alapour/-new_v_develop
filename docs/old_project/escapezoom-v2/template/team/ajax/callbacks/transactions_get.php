<?php
global $wpdb, $wldb;

$medoo = medoo();

$user_id    = sanitize_text_field($_POST['user_id']);
$data_type  = sanitize_text_field($_POST['data_type']);
$page_num   = max(1, intval(sanitize_text_field($_POST['page'] ?? 1)));

if ($data_type == 'user_info') :

    $user_info = get_userdata($user_id);

    $user_login = $user_info->user_login; ?>

    <div class="flex items-center gap-2">
        <p class="text-sm font-yekan-bold text-grayy text-nowrap">نام</p>
        <p class="text-base font-yekan-heavy text-nowrap"><?php echo $user_info->display_name ?></p>
    </div>

    <svg xmlns="http://www.w3.org/2000/svg" width="2" height="32" viewBox="0 0 2 32" fill="none" class="mx-6">
        <path d="M1 0.999998L1 31" stroke="#E2E8F0" stroke-width="2" stroke-linecap="round" />
    </svg>

    <div class="flex items-center gap-2">
        <p class="text-sm font-yekan-bold text-grayy text-nowrap">اعتبار فعلی</p>
        <p class="text-base font-yekan-heavy"><?php echo number_format($wldb->get_balance($user_id)); ?></p>
    </div>

    <svg xmlns="http://www.w3.org/2000/svg" width="2" height="32" viewBox="0 0 2 32" fill="none" class="mx-6">
        <path d="M1 0.999998L1 31" stroke="#E2E8F0" stroke-width="2" stroke-linecap="round" />
    </svg>

    <div class="flex items-center gap-2">
        <p class="text-sm font-yekan-bold text-grayy text-nowrap">شهر</p>
        <p class="text-base font-yekan-heavy text-nowrap"><?php echo get_user_meta($user_id, 'billing_city', true) ?: '----------' ?></p>
    </div>

    <svg xmlns="http://www.w3.org/2000/svg" width="2" height="32" viewBox="0 0 2 32" fill="none" class="mx-6">
        <path d="M1 0.999998L1 31" stroke="#E2E8F0" stroke-width="2" stroke-linecap="round" />
    </svg>

    <div class="flex items-center gap-2">
        <p class="text-sm font-yekan-bold text-grayy text-nowrap">موبایل</p>
        <p class="text-base font-yekan-heavy text-nowrap"><?php echo strpos($user_login, '0') !== 0 ? $user_login : '0' . $user_login; ?></p>
    </div>

<?php
else : ?>

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
    $export_all = isset($_POST['export_all']) && $_POST['export_all'] === 'true';
    $per_page = 50;
    $offset = ($page_num - 1) * $per_page;

    // First get all transactions to count total
    $all_transactions = $wldb->get(array('user_id' => $user_id), 500); // Get all transactions (original limit)
    $total_transactions = count($all_transactions);
    $total_pages = ceil($total_transactions / $per_page);

    // If export_all, use all transactions, otherwise slice for current page
    $transactions = $export_all ? $all_transactions : array_slice($all_transactions, $offset, $per_page);

    if (!empty($transactions)) :
        foreach ($transactions as $key => $trans) :
            $row_number = $offset + $key + 1; ?>

            <div class="data-row w-full" style="background-color: <?php echo $key % 2 === 0 ? '#FFFFFF' : '#F8FAFC'; ?>;">
                <div class="grid grid-cols-[60px_180px_100px_120px_100px_120px_120px_120px_120px] gap-4 px-6 py-4 text-sm font-yekan-bold">
                    <div class="text-center text-[#1E293B] flex items-center justify-center">
                        <?php echo $row_number ?>
                    </div>
                    <div class="text-center text-[#1E293B] flex items-center justify-center">
                        <?php echo $trans->ID ?>
                    </div>
                    <div class="text-center text-[#1E293B] flex items-center justify-center">
                        <?php echo parsidate('j M | H:i', $trans->created_at, 'fa') ?>
                    </div>
                    <div class="text-center flex items-center justify-center">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded text-white text-xs font-yekan-heavy <?php echo $trans->amount > 0 ? 'bg-[#10B981]' : 'bg-[#EF4444]' ?>" style="border-radius: 4px;">
                            <?php echo $trans->amount > 0 ? '+' : '-' ?>
                        </span>
                    </div>
                    <div class="text-center text-[#1E293B] flex items-center justify-center">
                        <?php echo ($trans->amount > 0 ? '+' : '-') . number_format(abs($trans->amount)) ?>
                    </div>
                    <div class="text-center text-[#1E293B] flex items-center justify-center">
                        <?php echo number_format($trans->balance - $trans->amount) ?>
                    </div>
                    <div class="text-center text-[#1E293B] flex items-center justify-center">
                        <?php echo number_format($trans->balance) ?>
                    </div>
                    <div class="text-center text-[#1E293B] flex items-center justify-center">
                        <?php echo $trans->description ?>
                    </div>
                    <div class="text-center flex items-center justify-center">
                        <?php
                        $status_text = $trans->status ?: 'انجام شد';
                        $status_class = '';

                        if ($status_text == 'در حال پردازش') {
                            $status_class = 'text-[#C29D04]';
                        } elseif ($status_text == 'انجام شد') {
                            $status_class = 'text-[#16A34A]';
                        } elseif ($status_text == 'لغو شد') {
                            $status_class = 'text-[#DC2626]';
                        } else {
                            $status_class = 'text-[#64748B]';
                        }
                        ?>
                        <span class="text-xs font-yekan-bold <?php echo $status_class ?>">
                            <?php echo $status_text ?>
                        </span>
                    </div>
                </div>
            </div>

        <?php
        endforeach;

        if ($total_pages > 1) { ?>
            <div class="mb-9 flex w-full items-center justify-center gap-4">
                <div class="flex gap-4 max-lg:gap-2 mt-16 justify-start max-lg:justify-center pagination">

                    <?php if ($page_num > 1) { ?>
                        <a href="javascript:void(0)" data-page="<?php echo $page_num - 1; ?>" class="pagination-link flex items-center justify-center w-8 h-8 rounded-lg border border-[#E2E8F0] text-[#64748B] hover:bg-[#F1F5F9] transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none" class="rotate-180 opacity-25">
                                <path d="M5.08008 11.1602L1.51062 7.14452C1.17384 6.76563 1.17384 6.19468 1.51062 5.81579L5.08008 1.80016" stroke="#0A184A" stroke-width="2" stroke-linecap="round"></path>
                            </svg>
                        </a>
                    <?php } ?>

                    <?php
                    $start = max(1, $page_num - 2);
                    $end = min($total_pages, $page_num + 2);

                    for ($i = $start; $i <= $end; $i++) { ?>
                        <?php if ($i == $page_num) { ?>
                            <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-[#FF6900] text-white text-sm font-yekan-bold"><?php echo $i; ?></span>
                        <?php } else { ?>
                            <a href="javascript:void(0)" data-page="<?php echo $i; ?>" class="pagination-link flex items-center justify-center w-8 h-8 rounded-lg border border-[#E2E8F0] text-[#64748B] hover:bg-[#F1F5F9] transition-colors text-sm font-yekan-bold"><?php echo $i; ?></a>
                        <?php } ?>
                    <?php } ?>

                    <?php if ($page_num < $total_pages) { ?>
                        <a href="javascript:void(0)" data-page="<?php echo $page_num + 1; ?>" class="pagination-link flex items-center justify-center w-8 h-8 rounded-lg border border-[#E2E8F0] text-[#64748B] hover:bg-[#F1F5F9] transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none">
                                <path d="M5.08008 11.1602L1.51062 7.14452C1.17384 6.76563 1.17384 6.19468 1.51062 5.81579L5.08008 1.80016" stroke="#0A184A" stroke-width="2" stroke-linecap="round"></path>
                            </svg>
                        </a>
                    <?php } ?>

                </div>
            </div>
        <?php }
    endif;
endif; ?>
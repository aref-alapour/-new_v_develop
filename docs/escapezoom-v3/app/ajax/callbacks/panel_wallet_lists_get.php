<?php
global $wpdb;

$user_id = get_current_user_id();

$list = sanitize_text_field( $_POST['list'] );

$page_num = sanitize_text_field( $_POST['page'] ) ?: 1;
$status   = sanitize_text_field( $_POST['status'] ) ?: - 1;

$items_per_page = 10;

$color = '#000000';

if ( $list == 'settlement' ) {

	if ( $status == - 1 ) {
		$type = - 1;
	} elseif ( $status == 'processing' ) {
		$type = "در حال پردازش";
	} elseif ( $status == 'rejected' ) {
		$type = "رد شده";
	} elseif ( $status == 'done' ) {
		$type = "انجام شد";
	}

	$type = $type == - 1 ? 1 : "status LIKE '$type'";

	$max_page_num = ceil( (int) ( $wpdb->get_var( "SELECT COUNT(*) FROM wallet_transactions WHERE user_id LIKE {$user_id} AND type LIKE 'withdraw' AND {$type}" ) ) / $items_per_page );

	$offset = ( $page_num - 1 ) * $items_per_page;

	$transactions = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM wallet_transactions WHERE user_id LIKE {$user_id} AND type LIKE 'withdraw' AND {$type} ORDER BY created_at DESC LIMIT {$offset}, {$items_per_page}" ) );

	?>

    <div class="md:mb-8 mb-0 lg:mb-8 max-lg:border-b max-lg:border-slate-120 max-lg:pb-6">
        <div class="flex justify-start max-lg:flex-wrap">
            <div class="items-center grow max-lg:py-4 max-lg:grow gap-6 md:flex">
                <h2 class="flex items-center gap-4">
                    <span class="text-base font-bold md:text-lg">
                        <span class="text-xl">لیست تسویه حساب</span>
                    </span>
                </h2>
                <div class="hidden md:block"></div>
            </div>
			<?php if ( ! empty( $transactions ) ) { ?>
                <div class="flex relative items-center">
                    <button type="button" id="open-filter-menu" class="flex ">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 22 22" fill="none">
                            <rect width="22" height="22" rx="6" fill="#FC6F13"/>
                            <path d="M5 8H17" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M5 14H17" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                            <circle cx="13" cy="8" r="2" fill="white"/>
                            <circle cx="9" cy="14" r="2" fill="white"/>
                        </svg>
                    </button>
                    <div id="filter-list" class="absolute flex z-50 hidden bg-white rounded-3xl shadow-100 flex-col top-full left-0 w-32 z-30 p-4 text-center leading-5 gap-2">
                        <a href="#" data-state="-1" class="text-nowrap border-b-2 <?php echo $status == "-1" ? "border-primary-500" : "border-transparent opacity-50" ?>">
                            همه
                        </a>
                        <a href="#" data-state="processing" class="text-nowrap border-b-2 <?php echo $status == "processing" ? "border-primary-500" : "border-transparent opacity-50" ?>">
                            در
                            حال پردازش
                        </a>
                        <a href="#" data-state="done" class="text-nowrap border-b-2 <?php echo $status == "done" ? "border-primary-500" : "border-transparent opacity-50" ?>">
                            پرداخت
                            شده
                        </a>
                        <a href="#" data-state="rejected" class="text-nowrap border-b-2 <?php echo $status == "rejected" ? "border-primary-500" : "border-transparent opacity-50" ?>">
                            لغو
                            شده
                        </a>
                    </div>
                </div>
			<?php } ?>
        </div>
    </div>
    <div class="relative">
        <div class="relative overflow-x-auto">
			<?php if ( ! empty( $transactions ) ) { ?>
                <table class="w-full text-right text-sm max-lg:hidden">
                    <thead class="border-b border-t border-slate-120 text-xs text-slate-350 max-lg:hidden">
                        <tr>
                            <th scope="col" class="text-nowrap py-6 first:pr-0 last:pl-0">
                                شماره
                            </th>
                            <th scope="col" class="text-nowrap text-right py-6 first:pr-0 last:pl-0">
                                مبلغ کل (تومان)
                            </th>
                            <th scope="col" class="text-nowrap text-right py-6 first:pr-0 last:pl-0">
                                تاریخ درخواست
                            </th>
                            <th scope="col" class="text-nowrap text-left py-6 first:pr-0 last:pl-0">
                                وضعیت
                            </th>
                        </tr>
                    </thead>
                    <tbody class="max-lg:flex flex-wrap max-lg:w-full">

						<?php foreach ( $transactions as $key => $trans ) { ?>
                            <tr class="font-bold text-md max-lg:flex max-lg:flex-wrap max-lg:w-full max-lg:border-b">
                                <td class="lg:border-b border-slate-120 lg:px-4 py-3 first:pr-0 last:pl-0 max-lg:w-1/2 order-1">
                                    <span class="text-slate-350 lg:hidden ml-2">کد رزرو</span>
									<?php echo esc_html( (int) $trans->ID ); ?>
                                </td>
                                <td class="lg:border-b text-right max-lg:text-right border-slate-120 lg:px-4 py-3 first:pr-0 last:pl-0 max-lg:w-1/2 order-3">
                                    <span class="text-slate-350 lg:hidden ml-2">مبلغ</span>
									<?php echo number_format((int) $trans->amount * - 1); ?> تومان
                                </td>
                                <td class="lg:border-b text-right max-lg:text-left border-slate-120 lg:px-4 py-3 first:pr-0 last:pl-0 max-lg:w-1/2 order-2">
                                    <span class="text-slate-350 lg:hidden ml-2">تاریخ درخواست</span>
									<?php echo jdate( 'Y.m.d', (int) $trans->created_at ) ?>
                                    &nbsp;&nbsp;&nbsp;&nbsp;
									<?php echo jdate( 'H:i', (int) $trans->created_at ) ?>
                                </td>
                                <td class="lg:border-b text-left border-slate-120 lg:px-4 py-3 first:pr-0 last:pl-0 max-lg:w-1/2 order-4">
									<?php
									$color = '#000000';
									$color = match ( $trans->status ) {
										"در حال پردازش" => '#fb9b0c',
										"رد شده" => '#f93d3d',
										'انجام شد' => '#049654',
									} ?>
                                    <span class="w-full rounded-xl py-2 justify-center inline-flex lg:hidden" style="background:<?php echo $color; ?>1A; color: <?php echo $color; ?>"><?php echo $trans->status; ?></span>
                                    <span class="max-lg:hidden" style="color: <?php echo $color; ?>"><?php echo $trans->status; ?></span>
                                </td>
                            </tr>
						<?php } ?>

                    </tbody>
                </table>

                <div class="flex flex-col lg:hidden">

					<?php foreach ( $transactions as $key => $trans ) { ?>
                        <div class="py-6 border-b grid grid-cols-2 gap-4 text-md">
                            <div class="flex items-center gap-4 justify-start">
                                <span class="text-slate-350">شماره</span>
                                <span><?php echo esc_html( (int) $trans->ID ); ?></span>
                            </div>
                            <div class="flex items-center gap-4 justify-end">
                                <span class="text-slate-350">تاریخ درخواست</span>
                                <span>
	                                <?php echo jdate( 'Y.m.d', (int) $trans->created_at ) ?>
                                    &nbsp;&nbsp;&nbsp;&nbsp;
	                                <?php echo jdate( 'H:i', (int) $trans->created_at ) ?>
                                </span>
                            </div>
                            <div class="flex items-center gap-4 justify-start">
                                <span class="text-slate-350">مبلغ کل</span>
                                <span><?php echo number_format((int) $trans->amount * - 1); ?> تومان</span>
                            </div>
	                        <?php
	                        $color = '#000000';
	                        $color = match ( $trans->status ) {
		                        "در حال پردازش" => '#fb9b0c',
		                        "رد شده" => '#f93d3d',
		                        'انجام شد' => '#049654',
	                        } ?>
                            <div class="flex items-center gap-4 justify-end">
                                <span class="text-slate-350 rounded-md text-center w-full p-2" style="color: <?php echo $color?>;background: <?php echo $color;?>1A"><?php echo $trans->status; ?></span>
                            </div>
                        </div>
					<?php } ?>

                </div>

			<?php } else { ?>
                <div class="text-22 font-bold lg:text-lg text-center lg:my-19 text-gray-500">
                    تا این لحظه هیچ درخواست تسویه ای
                    <br>
                    برای شما ثبت نشده است.
                </div>
			<?php } ?>
        </div>
    </div>

<?php } else {

	if ( $status == - 1 ) {
		$type = 1;
	} elseif ( $status == 'withdraws' ) {
		$type = "amount < 0";
	} elseif ( $status == 'deposits' ) {
		$type = "amount > 0";
	}

	$max_page_num = ceil( (int) ( $wpdb->get_var( "SELECT COUNT(*) FROM wallet_transactions WHERE user_id LIKE {$user_id} AND {$type}" ) ) / $items_per_page );

	$offset = ( $page_num - 1 ) * $items_per_page;

	$transactions = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM wallet_transactions WHERE user_id LIKE {$user_id} AND {$type} ORDER BY created_at DESC LIMIT {$offset}, {$items_per_page}" ) );

	?>

    <div class="md:mb-8 mb-8 lg:mb-8 max-lg:border-b max-lg:border-slate-120 max-lg:pb-6">
        <div class="flex justify-start max-lg:flex-wrap">
            <div class="items-center grow max-lg:py-4 max-lg:grow gap-6 md:flex">
                <h2 class="flex items-center gap-4">
                    <span class="text-base font-bold md:text-lg">
                        <span class="text-xl">تراکنش های من</span>
                    </span>
                </h2>
                <div class="hidden md:block"></div>
            </div>
        </div>
    </div>
    <div class="relative">
        <div class="relative overflow-x-auto">
			<?php if ( ! empty( $transactions ) ) { ?>

                <table class="w-full text-right text-sm max-lg:flex max-lg:hidden">
                    <thead class="border-b border-t border-slate-120 text-xs text-slate-350 max-lg:hidden">
                        <tr>
                            <th scope="col" class="text-nowrap py-6 first:pr-0 last:pl-0"></th>
                            <th scope="col" class="text-nowrap text-right py-6 first:pr-0 last:pl-0">
                                شماره تراکنش
                            </th>
                            <th scope="col" class="text-nowrap text-right py-6 first:pr-0 last:pl-0">
                                تاریخ درخواست
                            </th>
                            <th scope="col" class="text-nowrap text-right py-6 first:pr-0 last:pl-0">
                                عنوان
                            </th>
                            <th scope="col" class="text-nowrap text-right py-6 first:pr-0 last:pl-0">
                                مقدار
                            </th>
                            <th scope="col" class="text-nowrap text-left py-6 first:pr-0 last:pl-0">
                                موجودی قبل
                            </th>
                        </tr>
                    </thead>
                    <tbody class="max-lg:flex flex-wrap max-lg:w-full">
						<?php foreach ( $transactions as $key => $trans ) { ?>
                            <tr class="font-bold text-md max-lg:flex max-lg:flex-wrap max-lg:w-full max-lg:border-b items-center">
                                <td class="lg:border-b text-right border-slate-120 lg:px-4 py-3 first:pr-0 last:pl-0 order-1 max-lg:w-1/6">
									<?php if ( str_starts_with( $trans->amount, '-' ) ) { ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="38" height="38" viewBox="0 0 38 38" fill="none" class="m-0">
                                            <rect width="38" height="38" rx="8" fill="#F21543" fill-opacity="0.1"/>
                                            <path d="M11.3905 18.0565C11.6406 17.8065 11.9797 17.666 12.3333 17.666H25.6667C26.0203 17.666 26.3594 17.8065 26.6095 18.0565C26.8595 18.3066 27 18.6457 27 18.9993C27 19.353 26.8595 19.6921 26.6095 19.9422C26.3594 20.1922 26.0203 20.3327 25.6667 20.3327H12.3333C11.9797 20.3327 11.6406 20.1922 11.3905 19.9422C11.1405 19.6921 11 19.353 11 18.9993C11 18.6457 11.1405 18.3066 11.3905 18.0565Z"
                                                  fill="#F21543"/>
                                        </svg>
									<?php } else { ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="38" height="38" viewBox="0 0 38 38" fill="none" class="m-0">
                                            <rect width="38" height="38" rx="8" fill="#049654" fill-opacity="0.1"/>
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M20.3333 12.3333C20.3333 11.9797 20.1929 11.6406 19.9428 11.3905C19.6928 11.1405 19.3536 11 19 11C18.6464 11 18.3072 11.1405 18.0572 11.3905C17.8071 11.6406 17.6667 11.9797 17.6667 12.3333V17.6667H12.3333C11.9797 17.6667 11.6406 17.8071 11.3905 18.0572C11.1405 18.3072 11 18.6464 11 19C11 19.3536 11.1405 19.6928 11.3905 19.9428C11.6406 20.1929 11.9797 20.3333 12.3333 20.3333H17.6667V25.6667C17.6667 26.0203 17.8071 26.3594 18.0572 26.6095C18.3072 26.8595 18.6464 27 19 27C19.3536 27 19.6928 26.8595 19.9428 26.6095C20.1929 26.3594 20.3333 26.0203 20.3333 25.6667V20.3333H25.6667C26.0203 20.3333 26.3594 20.1929 26.6095 19.9428C26.8595 19.6928 27 19.3536 27 19C27 18.6464 26.8595 18.3072 26.6095 18.0572C26.3594 17.8071 26.0203 17.6667 25.6667 17.6667H20.3333V12.3333Z" fill="#02C96F"/>
                                        </svg>
									<?php } ?>
                                </td>
                                <td class="lg:border-b border-slate-120 lg:px-4 py-3 first:pr-0 last:pl-0 order-2 before:border-b before:max-lg:grow max-lg:w-3/6 max-lg:flex max-lg:flex-row-reverse max-lg:items-center max-lg:gap-3">
									<?php echo (int) $trans->ID; ?>
                                </td>
                                <td class="lg:border-b text-right max-lg:text-left border-slate-120 lg:px-4 py-3 first:pr-0 last:pl-0 order-3 max-lg:w-2/6">
									<?php echo jdate( 'Y.m.d', (int) $trans->created_at ) ?>
                                    &nbsp;&nbsp;&nbsp;&nbsp;
									<?php echo jdate( 'H:i', (int) $trans->created_at ) ?>
                                </td>
                                <td class="lg:border-b text-right max-lg:text-right border-slate-120 lg:px-4 py-3 first:pr-0 last:pl-0 order-4 max-lg:w-1/3 max-lg:flex max-lg:flex-col max-lg">
                                    <span class="text-slate-350 lg:hidden ml-2">عنوان</span>
									<?php echo $trans->description; ?>
                                </td>
                                <td class="lg:border-b text-right max-lg:text-right border-slate-120 lg:px-4 py-3 first:pr-0 last:pl-0 order-5 max-lg:w-1/3 max-lg:flex max-lg:flex-col max-lg">
                                    <span class="text-slate-350 lg:hidden ml-2">مقدار</span>
									<?php echo number_format( $trans->amount ) ?>
                                </td>
                                <td class="lg:border-b text-left border-slate-120 lg:px-4 py-3 first:pr-0 last:pl-0 order-6 max-lg:w-1/3 max-lg:flex max-lg:flex-col max-lg:text-right">
                                    <span class="text-slate-350 lg:hidden ml-2">موجودی قبل</span>
									<?php echo number_format( (int) $trans->balance - $trans->amount ) ?>
                                </td>
                            </tr>
						<?php } ?>
                    </tbody>
                </table>

                <div class="lg:hidden flex flex-col">

					<?php foreach ( $transactions as $key => $trans ) { ?>
                        <div class="flex flex-col font-xl pb-8">
                            <div class="flex w-full gap-x-4 mb-6">
								<?php if ( str_starts_with( $trans->amount, '-' ) ) { ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="38" height="38" viewBox="0 0 38 38" fill="none" class="m-0">
                                        <rect width="38" height="38" rx="8" fill="#F21543" fill-opacity="0.1"/>
                                        <path d="M11.3905 18.0565C11.6406 17.8065 11.9797 17.666 12.3333 17.666H25.6667C26.0203 17.666 26.3594 17.8065 26.6095 18.0565C26.8595 18.3066 27 18.6457 27 18.9993C27 19.353 26.8595 19.6921 26.6095 19.9422C26.3594 20.1922 26.0203 20.3327 25.6667 20.3327H12.3333C11.9797 20.3327 11.6406 20.1922 11.3905 19.9422C11.1405 19.6921 11 19.353 11 18.9993C11 18.6457 11.1405 18.3066 11.3905 18.0565Z"
                                              fill="#F21543"/>
                                    </svg>
								<?php } else { ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="38" height="38" viewBox="0 0 38 38" fill="none" class="m-0">
                                        <rect width="38" height="38" rx="8" fill="#049654" fill-opacity="0.1"/>
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M20.3333 12.3333C20.3333 11.9797 20.1929 11.6406 19.9428 11.3905C19.6928 11.1405 19.3536 11 19 11C18.6464 11 18.3072 11.1405 18.0572 11.3905C17.8071 11.6406 17.6667 11.9797 17.6667 12.3333V17.6667H12.3333C11.9797 17.6667 11.6406 17.8071 11.3905 18.0572C11.1405 18.3072 11 18.6464 11 19C11 19.3536 11.1405 19.6928 11.3905 19.9428C11.6406 20.1929 11.9797 20.3333 12.3333 20.3333H17.6667V25.6667C17.6667 26.0203 17.8071 26.3594 18.0572 26.6095C18.3072 26.8595 18.6464 27 19 27C19.3536 27 19.6928 26.8595 19.9428 26.6095C20.1929 26.3594 20.3333 26.0203 20.3333 25.6667V20.3333H25.6667C26.0203 20.3333 26.3594 20.1929 26.6095 19.9428C26.8595 19.6928 27 19.3536 27 19C27 18.6464 26.8595 18.3072 26.6095 18.0572C26.3594 17.8071 26.0203 17.6667 25.6667 17.6667H20.3333V12.3333Z" fill="#02C96F"/>
                                    </svg>
								<?php } ?>
                                <span class="after:border-b after:grow flex items-center gap-4 grow">
									<?php echo (int) $trans->ID; ?>
                                </span>
                                <span>
									<?php echo jdate( 'Y.m.d', (int) $trans->created_at ) ?>
                                    &nbsp;&nbsp;&nbsp;&nbsp;
									<?php echo jdate( 'H:i', (int) $trans->created_at ) ?>
                                </span>
                            </div>
                            <table class="w-full">
                                <thead class="text-xs text-slate-350">
                                    <tr>
                                        <th scope="col" class="text-nowrap text-right px-4 pb-2 first:pr-0 last:pl-0">
                                            عنوان
                                        </th>
                                        <th scope="col" class="text-nowrap text-left px-4 pb-2 first:pr-0 last:pl-0">
                                            موجودی قبل
                                        </th>
                                        <th scope="col" class="text-nowrap text-right px-4 pb-2 first:pr-0 last:pl-0">
                                            مقدار
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="font-bold text-md max-lg:border-b items-center">
                                        <td class="lg:border-b text-right pb-8 max-lg:text-right px-4 border-slate-120 lg:px-4 first:pr-0 last:pl-0 order-4">
											<?php echo $trans->description; ?>
                                        </td>
                                        <td class="lg:border-b text-left pb-8 border-slate-120 px-4 lg:px-4 first:pr-0 last:pl-0 order-6">
											<?php echo number_format( (int) $trans->balance - $trans->amount ) ?>
                                        </td>
                                        <td class="lg:border-b text-right pb-8 max-lg:text-right px-4 border-slate-120 lg:px-4 first:pr-0 last:pl-0 order-5">
											<?php echo number_format( $trans->amount ) ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
					<?php } ?>

                </div>

			<?php } else { ?>
                <div class="text-22 font-bold lg:text-lg text-center lg:my-19 text-gray-500">
                    تا این لحظه هیچ تراکنشی
                    <br>
                    برای شما ثبت نشده است.
                </div>
			<?php } ?>
        </div>
    </div>

<?php } ?>

<?php if ( $max_page_num > 1 ) { ?>
    <div class="mt-9 flex w-full items-center justify-center gap-4">
        <div class="flex gap-4 max-lg:gap-2 mt-2 justify-start max-lg:justify-center pagination">
			<?php echo paginate_links( [
				'mid_size'  => 1,
				'base'      => get_pagenum_link( 1 ) . '%_%',
				'format'    => '?page=%#%',
				'current'   => max( 1, $page_num ),
				'total'     => $max_page_num,
				'prev_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none" class="rotate-180 opacity-25"><path d="M5.08008 11.1602L1.51062 7.14452C1.17384 6.76563 1.17384 6.19468 1.51062 5.81579L5.08008 1.80016" stroke="#0A184A" stroke-width="2" stroke-linecap="round"></path></svg>',
				'next_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none"><path d="M5.08008 11.1602L1.51062 7.14452C1.17384 6.76563 1.17384 6.19468 1.51062 5.81579L5.08008 1.80016" stroke="#0A184A" stroke-width="2" stroke-linecap="round"></path></svg>',
			] ); ?>
        </div>
    </div>
<?php } ?>

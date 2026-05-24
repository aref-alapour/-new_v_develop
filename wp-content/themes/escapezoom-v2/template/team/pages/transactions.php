<input type="hidden" id="current_user_id">
<div class="flex justify-between items-center gap-4">

    <h1 class="text-base font-extrabold lg:text-2xl">تراکنش‌ها</h1>

    <svg xmlns="http://www.w3.org/2000/svg" width="4" height="34" viewBox="0 0 4 34" fill="none" class="mx-6">
        <path d="M2 2L2 32" stroke="#FF6900" stroke-width="4" stroke-linecap="round" />
    </svg>

    <div id="user_info" class="flex items-center justify-between" style="width: 100%"></div>

    <div class="relative flex items-center gap-2" style="z-index: 99; min-width: 360px;">
        <div class="relative flex-1 min-w-[220px]">
            <input id="trans_user_search" class="w-full h-[58px] border border-[#E4EBF0] bg-[#FAFDFF] rounded-xl outline-none pr-10 pl-4 py-5 text-xs font-yekan-bold text-navyBlue placeholder:text-end" placeholder="شماره موبایل — سپس جستجو" dir="ltr" autocomplete="off" />
            <button id="trans_user_search_clear" type="button" class="absolute top-5 right-3 w-6 h-6 flex items-center justify-center cursor-pointer opacity-0 pointer-events-none transition-opacity" style="display: none;" aria-label="پاک کردن">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
                    <path d="M13 1L1 13M1 1L13 13" stroke="#64748B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
        <button id="trans_user_search_btn" type="button" class="h-[58px] px-5 shrink-0 bg-orange-500 text-white text-xs font-yekan-heavy rounded-xl hover:bg-orange-600 transition-colors disabled:opacity-60 disabled:cursor-not-allowed" style="box-shadow: 0px 2px 0px 0px #CA5608;">جستجو</button>

        <div id="search-result-list" class="max-h-75 overflow-y-auto px-4 pt-2 pb-3" style="background: #f3f3f3; display: none; position: absolute; top: 62px; right: 0; left: 0; border-radius: 0 0 12px 12px; box-shadow: 0 8px 16px rgba(15, 23, 42, 0.12);">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-yekan-bold text-[#64748B]">نتایج جستجو</span>
                <button id="search_result_list_close" class="w-6 h-6 flex items-center justify-center rounded-full hover:bg-[#E2E8F0] transition-colors" type="button" aria-label="بستن">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 14 14" fill="none">
                        <path d="M13 1L1 13M1 1L13 13" stroke="#64748B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
            <div id="search-result-items" class="divide-y divide-[#E4EBF0]"></div>
        </div>
    </div>

</div>

<?php
if (array_intersect(['administrator', 'accounting'], wp_get_current_user()->roles)) : ?>

    <div class="flex justify-between items-center py-3 px-6 mt-7" style="border-radius: 14px;border: 1px solid var(--Border-Default, #E2E8F0);background: var(--slate-50, #F8FAFC);box-shadow: 0px 2px 0px 0px #E2E8F0;">
        <div class="flex items-center gap-3">
            <p class="text-sm font-yekan-bold">مبلغ شارژ(تومان)<span class=" text-pinkk">*</span></p>
            <input id="trans_operation_amount" class="w-[156px] h-[34px] bg-white border border-[#E4EBF0] rounded-lg outline-none px-6 py-3 text-xs font-yekan-bold text-navyBlue text-end" style="width: 156px;height: 34px;direction: ltr;text-align: left;" placeholder="" />
        </div>

        <div class="flex items-center gap-3">
            <p class="text-sm font-yekan-bold">توضیح<span class=" text-pinkk">*</span></p>
            <input id="trans_operation_desc" class="w-[398px] h-[34px] bg-white border border-[#E4EBF0] rounded-lg outline-none text-xs font-yekan-bold text-navyBlue px-2" style="width: 398px; height: 34px;" placeholder="" />
        </div>

        <div class="relative dropdown">
            <select id="trans_operation_control" class="w-[156px] h-[34px] bg-white border border-[#E4EBF0] rounded-lg outline-none px-3 py-2 text-xs font-yekan-bold text-navyBlue appearance-none cursor-pointer hover:border-[#FF6900] focus:border-[#FF6900] transition-colors" style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 4 5\'%3E%3Cpath fill=\'%23666\' d=\'M2 0L0 2h4zm0 5L0 3h4z\'/%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: left 8px center; background-size: 12px;">
                <option value="-1">انتخاب کنید...</option>
                <option value="p">اضافه شود</option>
                <option value="">کم شود</option>
            </select>
        </div>

        <button id="trans_operation_control_btn" class="w-[81px] h-[34px] flex justify-center items-center bg-orange-500 rounded-lg cursor-pointer text-white text-xs font-yekan-heavy hover:bg-orange-600 transition-colors" style="box-shadow: 0px 2px 0px 0px #CA5608; width: 81px; height: 34px;">اعمال</button>

    </div>
<?php
endif; ?>

<!-- Empty state / hint -->
<div id="transactions-empty-state" class="mt-6 bg-white rounded-xl shadow-sm px-6 py-10 text-center text-grayy">
    برای مشاهده تراکنش‌ها، یک کاربر را از طریق فیلد جست‌وجو انتخاب کنید.
</div>

<!-- User Transactions Table -->
<div class="relative bg-white rounded-xl overflow-hidden shadow-sm mt-6" id="data-list" style="display: none;"></div>

<script>
    jQuery(document).ready(function($) {

        const ezTeamTransAjax = {
            get: "<?php echo esc_url( get_template_directory_uri() ); ?>/template/team/ajax/callbacks/transactions_get.php",
            search: "<?php echo esc_url( get_template_directory_uri() ); ?>/template/team/ajax/callbacks/transactions_user_search.php",
            operations: "<?php echo esc_url( get_template_directory_uri() ); ?>/template/team/ajax/callbacks/transactions_operations.php",
        };

        function stripHtml(html) {
            if (!html) return '';
            const tmp = document.createElement('DIV');
            tmp.innerHTML = html;
            return tmp.textContent || tmp.innerText || '';
        }

        function formatNumber(num) {
            let cleanNum = num.replace(/[^\d]/g, '');
            return cleanNum.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }

        function exportUserTransactionsToExcel() {
            const user_id = $('#current_user_id').val();
            if (!user_id) {
                Swal.fire({
                    position: "bottom-start",
                    icon: "warning",
                    title: 'توجه',
                    text: 'لطفا ابتدا یک کاربر را جست و جو کنید',
                    showConfirmButton: false,
                    timer: 3000,
                    width: '350px',
                    toast: true
                });
                return;
            }

            Swal.fire({
                position: "bottom-start",
                icon: "info",
                title: 'در حال بارگذاری...',
                text: 'در حال دریافت تمام تراکنش‌ها',
                showConfirmButton: false,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                type: 'POST',
                url: ezTeamTransAjax.get,
                data: {
                    'user_id': user_id,
                    'data_type': 'user_trans',
                    'page': 1,
                    'export_all': 'true'
                },
                success: function(data) {
                    Swal.close();
                    const tempDiv = $('<div>').html(data);
                    const rows = tempDiv.find('.data-row');

                    if (rows.length === 0) {
                        Swal.fire({
                            position: "bottom-start",
                            icon: "warning",
                            title: 'توجه',
                            text: 'داده‌ای برای خروجی وجود ندارد',
                            showConfirmButton: false,
                            timer: 3000,
                            width: '350px',
                            toast: true
                        });
                        return;
                    }

                    let csv = '\uFEFF';
                    csv += 'ردیف,شماره تراکنش,زمان درخواست,اضافه/کسر,مبلغ,موجودی قبلی,موجودی فعلی,بابت,وضعیت\n';

                    rows.each(function() {
                        const cols = $(this).find('.grid > div');
                        if (cols.length >= 9) {
                            const row = [];
                            cols.each(function() {
                                let text = $(this).text().trim();
                                text = text.replace(/"/g, '""');
                                row.push('"' + text + '"');
                            });
                            csv += row.join(',') + '\n';
                        }
                    });

                    downloadCSV(csv, user_id);
                },
                error: function() {
                    Swal.close();
                    Swal.fire({
                        position: "bottom-start",
                        icon: "error",
                        title: 'خطا',
                        text: 'خطا در دریافت داده‌ها',
                        showConfirmButton: false,
                        timer: 3000,
                        width: '350px',
                        toast: true
                    });
                }
            });
        }

        function downloadCSV(csv, user_id) {
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', 'تراکنش_کاربر_' + user_id + '_' + new Date().toISOString().split('T')[0] + '.csv');
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        $(document).on('click', '#export-excel-user-transactions', function() {
            exportUserTransactionsToExcel();
        });

        $('body').on('input', "#trans_operation_amount", function() {
            let cursorPosition = this.selectionStart;
            let oldValue = $(this).val();
            let oldLength = oldValue.length;

            let formattedValue = formatNumber(oldValue);
            $(this).val(formattedValue);

            let newLength = formattedValue.length;
            let diff = newLength - oldLength;
            this.setSelectionRange(cursorPosition + diff, cursorPosition + diff);
        });

        function toggleClearButton() {
            const searchValue = $('#trans_user_search').val().trim();
            const clearBtn = $('#trans_user_search_clear');
            if (searchValue.length > 0) {
                clearBtn.css({
                    'display': 'flex',
                    'opacity': '1',
                    'pointer-events': 'auto'
                });
            } else {
                clearBtn.css({
                    'display': 'none',
                    'opacity': '0',
                    'pointer-events': 'none'
                });
            }
        }

        function resetTransactionsView() {
            $('#data-list').hide().html('');
            $('#transactions-empty-state').show();
            $('#user_info').html('');
            $('#current_user_id').val('');
            $('#search-result-items').empty();
            $('#search-result-list').hide();
        }

        function searchLoadingHtml() {
            let out = '<div class="py-4 text-center"><p class="text-xs text-[#64748B] mb-3">در حال جستجو...</p>';
            for (let i = 0; i < 3; i++) {
                out += '<div class="w-full h-8 rounded-lg skeleton mb-2"></div>';
            }
            out += '</div>';
            return out;
        }

        let searchXhr = null;

        function runUserSearch() {
            const searchValue = $('#trans_user_search').val().trim();

            if (searchValue.length < 3) {
                Swal.fire({
                    position: "bottom-start",
                    icon: "warning",
                    title: 'توجه',
                    text: 'حداقل ۳ رقم برای جستجو وارد کنید',
                    showConfirmButton: false,
                    timer: 3000,
                    width: '350px',
                    toast: true
                });
                return;
            }

            if (searchXhr) {
                searchXhr.abort();
            }

            $('#search-result-items').html(searchLoadingHtml());
            $('#search-result-list').show();

            const $btn = $('#trans_user_search_btn');
            $btn.prop('disabled', true).text('در حال جستجو...');

            searchXhr = $.ajax({
                type: 'POST',
                url: ezTeamTransAjax.search,
                data: {
                    'phone': searchValue,
                },
                success: function(data) {
                    $('#search-result-items').html(data);
                    $('#search-result-list').show();
                },
                error: function(xhr, status) {
                    if (status === 'abort') {
                        return;
                    }
                    $('#search-result-items').html('<div class="text-red-500 text-xs text-center py-4">خطا در جستجو. دوباره تلاش کنید.</div>');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('جستجو');
                    searchXhr = null;
                }
            });
        }

        $('#trans_user_search_btn').on('click', function(e) {
            e.preventDefault();
            runUserSearch();
        });

        $('#trans_user_search').on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                runUserSearch();
            }
        });

        $('body').on('input', "#trans_user_search", function() {
            toggleClearButton();
            if ($(this).val().trim() === '') {
                resetTransactionsView();
            }
        });

        $('#trans_user_search_clear').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $('#trans_user_search').val('');
            toggleClearButton();
            resetTransactionsView();
            $('#trans_user_search').focus();
        });

        $('body').on('click', '#search_result_list_close', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $('#search-result-items').empty();
            $('#search-result-list').hide();
        });

        $("body").on('click', ".team_trans_user_search_item", function(e) {
            e.preventDefault();

            let user_id = $(this).data('id');

            $('#transactions-empty-state').hide();
            $('#data-list').show();

            load_user_trans_info(user_id);
        });

        function load_user_trans_info(user_id, page = 1) {
            $('#search-result-items').empty();
            $('#search-result-list').hide();
            $('#current_user_id').val(user_id);

            $('#transactions-empty-state').hide();
            $('#data-list').show();

            $.ajax({
                type: 'POST',
                url: ezTeamTransAjax.get,
                data: {
                    'user_id': user_id,
                    'data_type': 'user_info',
                },
                beforeSend: function() {
                    let out = '<div class="w-full h-12 rounded-xl mb-2 skeleton"></div>';
                    $("#user_info").html(out);
                },
                success: function(data) {
                    $("#user_info").html(data);
                    load_transactions_data(user_id, page);
                }
            });
        }

        function load_transactions_data(user_id, page = 1) {
            $.ajax({
                type: 'POST',
                url: ezTeamTransAjax.get,
                data: {
                    'user_id': user_id,
                    'data_type': 'user_trans',
                    'page': page,
                },
                beforeSend: function() {
                    $("#data-list").html(function() {
                        let out = '<div class="w-full bg-[#F1F5F9] rounded-t-xl"><div class="grid grid-cols-[60px_180px_100px_120px_100px_120px_120px_120px_120px] gap-4 px-6 py-4 text-sm font-yekan-bold text-[#64748B]"><div class="text-center">ردیف</div><div class="text-center">شماره تراکنش</div><div class="text-center">زمان درخواست</div><div class="text-center">اضافه/کسر</div><div class="text-center">مبلغ</div><div class="text-center">موجودی قبلی</div><div class="text-center">موجودی فعلی</div><div class="text-center">بابت</div><div class="text-center">وضعیت</div></div></div>';
                        for (let i = 0; i < 15; i++) {
                            out += '<div class="w-full h-16 px-6 py-4"><div class="w-full h-8 rounded-lg skeleton"></div></div>';
                        }
                        return out;
                    });
                },
                error: function() {
                    $("#data-list").html('<div class="px-6 py-8 text-center text-sm text-red-500">خطا در بارگذاری تراکنش‌ها</div>');
                },
                success: function(data) {
                    $("#data-list").html(data);
                }
            });
        }

        const user_id_querystring = (new URLSearchParams(window.location.search)).get('user_id');
        if (user_id_querystring)
            load_user_trans_info(user_id_querystring);

        $("body").on('click', "#trans_operation_control_btn", function(e) {
            e.preventDefault();

            let amount = $('#trans_operation_amount').val().trim();
            let cleanAmount = amount.replace(/,/g, '');
            let user_id = $('#current_user_id').val();
            let description = $('#trans_operation_desc').val().trim();
            let operation = $('#trans_operation_control').val();

            if (!user_id) {
                Swal.fire({
                    position: "bottom-start",
                    icon: "warning",
                    title: 'توجه',
                    text: 'لطفا ابتدا یک کاربر را جست و جو کنید',
                    showConfirmButton: false,
                    timer: 3000,
                    width: '350px',
                    toast: true
                });
                return;
            }

            if (!cleanAmount) {
                Swal.fire({
                    position: "bottom-start",
                    icon: "warning",
                    title: 'مبلغ الزامی است',
                    text: 'لطفا مبلغ را وارد کنید',
                    showConfirmButton: false,
                    timer: 3000,
                    width: '350px',
                    toast: true
                });
                $('#trans_operation_amount').focus();
                return;
            }

            if (isNaN(cleanAmount) || parseFloat(cleanAmount) <= 0) {
                Swal.fire({
                    position: "bottom-start",
                    icon: "error",
                    title: 'مبلغ نامعتبر',
                    text: 'لطفا مبلغ معتبر وارد کنید',
                    showConfirmButton: false,
                    timer: 3000,
                    width: '350px',
                    toast: true
                });
                $('#trans_operation_amount').focus();
                return;
            }

            if (!description) {
                Swal.fire({
                    position: "bottom-start",
                    icon: "warning",
                    title: 'توضیح الزامی است',
                    text: 'لطفا توضیح را وارد کنید',
                    showConfirmButton: false,
                    timer: 3000,
                    width: '350px',
                    toast: true
                });
                $('#trans_operation_desc').focus();
                return;
            }

            if (operation === '-1') {
                Swal.fire({
                    position: "bottom-start",
                    icon: "warning",
                    title: 'نوع عملیات الزامی است',
                    text: 'لطفا نوع عملیات (اضافه/کسر) را انتخاب کنید',
                    showConfirmButton: false,
                    timer: 3000,
                    width: '350px',
                    toast: true
                });
                $('#trans_operation_control').focus();
                return;
            }

            $.ajax({
                type: 'POST',
                url: ezTeamTransAjax.operations,
                data: {
                    'user_id': user_id,
                    'amount': cleanAmount,
                    'description': description,
                    'operation': operation,
                },
                beforeSend: function() {
                    $('#trans_operation_control_btn').prop('disabled', true).text('در حال پردازش...');
                },
                success: function(response) {
                    console.log('Transaction operation response:', response);

                    let current_user = $('#current_user_id').val();

                    if (current_user) {
                        let current_page = $('.pagination span.text-white').first().text() || '1';
                        load_user_trans_info(current_user, parseInt(current_page, 10) || 1);
                    }

                    $('#trans_operation_amount').val('');
                    $('#trans_operation_desc').val('');
                    $('#trans_operation_control').val('-1');

                    Swal.fire({
                        position: "bottom-start",
                        icon: "success",
                        title: 'موفق',
                        text: 'عملیات با موفقیت انجام شد',
                        showConfirmButton: false,
                        timer: 3000,
                        width: '350px',
                        toast: true
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Transaction operation error:', error);
                    Swal.fire({
                        position: "bottom-start",
                        icon: "error",
                        title: 'خطا',
                        text: 'خطا در انجام عملیات. لطفا دوباره تلاش کنید',
                        showConfirmButton: false,
                        timer: 3000,
                        width: '350px',
                        toast: true
                    });
                },
                complete: function() {
                    $('#trans_operation_control_btn').prop('disabled', false).text('اعمال');
                }
            });
        });

        $("body").on('click', ".pagination-link", function(e) {
            e.preventDefault();

            const page = $(this).data('page');
            const user_id = $('#current_user_id').val();

            console.log('Pagination clicked - Page:', page, 'User ID:', user_id);

            if (user_id && page) {
                load_transactions_data(user_id, page);
            }
        });

    });
</script>

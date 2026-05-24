<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>گزارش‌گیری بازاریابی</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jalaali-js@1.2.6/dist/jalaali.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/fa.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.3.2/papaparse.min.js"></script>
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
    <style>
        body { font-family: 'Vazir', sans-serif; }
        .table-container { max-height: 500px; overflow-y: auto; }
        .select2-container { width: 100% !important; }
        .form-control, .form-select { padding: 0.5rem !important; }
        @media (max-width: 640px) {
            .table-container { overflow-x: auto; }
            table { min-width: 1200px; }
        }
        .flatpickr-calendar { font-family: 'Vazir', sans-serif; }
    </style>
</head>
<body class="bg-light">
    <div class="container my-4">
        <h1 class="text-center mb-4">گزارش‌گیری بازاریابی</h1>
        
        <!-- Filter Form -->
        <div class="card p-4 mb-4">
            <form id="filterForm" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">شناسه مشتری</label>
                    <input type="number" name="customer_id" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">نام</label>
                    <input type="text" name="customer_firstname" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">نام خانوادگی</label>
                    <input type="text" name="customer_lastname" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">شماره تلفن</label>
                    <input type="text" name="customer_phone" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">نوع تقویم</label>
                    <select name="calendar_type" class="form-select">
                        <option value="shamsi">شمسی</option>
                        <option value="gregorian">میلادی</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">تاریخ ثبت‌نام (از)</label>
                    <input type="text" name="customer_registered_at_from" class="form-control date-picker" placeholder="1404/05/15">
                </div>
                <div class="col-md-4">
                    <label class="form-label">تاریخ ثبت‌نام (تا)</label>
                    <input type="text" name="customer_registered_at_to" class="form-control date-picker" placeholder="1404/05/15">
                </div>
                <div class="col-md-4">
                    <label class="form-label">شناسه سفارش</label>
                    <input type="number" name="order_id" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">وضعیت سفارش</label>
                    <select name="order_status" class="form-select">
                        <option value="">همه</option>
                        <option value="successful">موفق</option>
                        <option value="unsuccessful">ناموفق</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">زمان سانس (از)</label>
                    <input type="text" name="order_sans_time_from" class="form-control time-picker">
                </div>
                <div class="col-md-4">
                    <label class="form-label">زمان سانس (تا)</label>
                    <input type="text" name="order_sans_time_to" class="form-control time-picker">
                </div>
                <div class="col-md-4">
                    <label class="form-label">روز سانس</label>
                    <select name="order_sans_day[]" class="form-select days-select" multiple="multiple">
                        <option value="شنبه">شنبه</option>
                        <option value="یکشنبه">یکشنبه</option>
                        <option value="دوشنبه">دوشنبه</option>
                        <option value="سه‌شنبه">سه‌شنبه</option>
                        <option value="چهارشنبه">چهارشنبه</option>
                        <option value="پنج‌شنبه">پنج‌شنبه</option>
                        <option value="جمعه">جمعه</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">تاریخ سانس (از)</label>
                    <input type="text" name="order_sans_date_from" class="form-control date-picker" placeholder="1404/05/15">
                </div>
                <div class="col-md-4">
                    <label class="form-label">تاریخ سانس (تا)</label>
                    <input type="text" name="order_sans_date_to" class="form-control date-picker" placeholder="1404/05/15">
                </div>
                <div class="col-md-4">
                    <label class="form-label">تعداد بلیط (از)</label>
                    <input type="number" name="order_tickets_quantity_from" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">تعداد بلیط (تا)</label>
                    <input type="number" name="order_tickets_quantity_to" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">منبع سفارش</label>
                    <input type="text" name="order_refrerr" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">تاریخ ایجاد سفارش (از)</label>
                    <input type="text" name="order_created_at_from" class="form-control date-picker" placeholder="1404/05/15">
                </div>
                <div class="col-md-4">
                    <label class="form-label">تاریخ ایجاد سفارش (تا)</label>
                    <input type="text" name="order_created_at_to" class="form-control date-picker" placeholder="1404/05/15">
                </div>
                <div class="col-md-4">
                    <label class="form-label">نام بازی</label>
                    <select name="game_id[]" class="form-select game-select" multiple="multiple"></select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">تعداد در صفحه</label>
                    <select name="per_page" class="form-select">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="500">500</option>
                        <option value="1000">1000</option>
                        <option value="5000">5000</option>
                        <option value="10000">10000</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">ردیف شروع</label>
                    <input type="number" name="offset_page" class="form-control">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary w-100">فیلتر</button>
                </div>
                <div class="col-12 text-center">
                    <button type="button" id="exportExcel" class="btn btn-success me-2">خروجی اکسل</button>
                    <button type="button" id="exportCsv" class="btn btn-success me-2">خروجی CSV</button>
                </div>
            </form>
        </div>

        <!-- Results Table -->
        <div class="card table-container">
            <table class="table table-bordered table-striped">
                <thead class="table-light sticky-top">
                    <tr>
                        <th>شناسه مشتری</th>
                        <th>نام</th>
                        <th>نام خانوادگی</th>
                        <th>تلفن</th>
                        <th>تاریخ ثبت‌نام</th>
                        <th>شناسه سفارش</th>
                        <th>وضعیت سفارش</th>
                        <th>زمان سانس</th>
                        <th>روز سانس</th>
                        <th>تاریخ سانس</th>
                        <th>تعداد بلیط</th>
                        <th>مبلغ پرداخت شده</th>
                        <th>درآمد سایت</th>
                        <th>شماره‌های سفارش</th>
                        <th>منبع</th>
                        <th>تاریخ ایجاد</th>
                        <th>شناسه بازی</th>
                        <th>نام بازی</th>
                        <th>شهر بازی</th>
                        <th>منطقه بازی</th>
                        <th>نوع بازی</th>
                        <th>ژانر بازی</th>
                        <th>مدت زمان بازی</th>
                        <th>تاریخ ساخت بازی</th>
                    </tr>
                </thead>
                <tbody id="results"></tbody>
            </table>
        </div>

        <!-- Pagination -->
        <nav class="mt-4">
            <ul id="pagination" class="pagination justify-content-center"></ul>
        </nav>
    </div>

    <script>
        let currentData = [];
        let currentPage = 1;
        let perPage = 10;

        // Initialize Flatpickr for date fields
        function initializeDatePickers() {
            const calendarType = $('[name="calendar_type"]').val();
            const config = {
                enableTime: false,
                dateFormat: calendarType === 'shamsi' ? 'Y/m/d' : 'Y-m-d',
                locale: calendarType === 'shamsi' ? 'fa' : 'en',
                altInput: true,
                altFormat: calendarType === 'shamsi' ? 'Y/m/d' : 'Y-m-d',
                weekNumbers: calendarType === 'shamsi',
                allowInput: true
            };

            // تخریب نمونه‌های قبلی
            $('.date-picker').each(function() {
                if (this._flatpickr) {
                    this._flatpickr.destroy();
                }
            });

            // مقداردهی اولیه تقویم برای هر فیلد تاریخ
            $('.date-picker').each(function() {
                flatpickr(this, {
                    ...config,
                    onReady: function(selectedDates, dateStr, instance) {
                        instance.element.value = dateStr;
                        instance.altInput.value = dateStr;
                    },
                    onChange: function(selectedDates, dateStr, instance) {
                        instance.element.value = dateStr;
                        instance.altInput.value = dateStr;
                    }
                });
            });
        }

        // Initialize Flatpickr for time fields
        flatpickr('.time-picker', {
            enableTime: true,
            noCalendar: true,
            dateFormat: 'H:i',
            time_24hr: true,
            allowInput: true
        });

        // Initialize Select2 for days and games
        $('.days-select').select2({
            placeholder: 'روزهای هفته را انتخاب کنید',
            allowClear: true
        });

        $('.game-select').select2({
            placeholder: 'بازی‌ها را انتخاب کنید',
            allowClear: true,
            ajax: {
                url: '/api.php?action=get_games',
                type: 'POST',
                dataType: 'json',
                delay: 150,
                cache: true, // فعال کردن کش
                data: function(params) {
                    const { token, timestamp } = generateToken();
        
                    // ساخت filters به صورت JSON رشته شده
                    const filters = {
                        game_name: params.term || ''
                    };
        
                    return {
                        filters: JSON.stringify(filters),
                        token: token,
                        timestamp: timestamp
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.map(game => ({
                            id: game.ID,
                            text: game.post_title
                        }))
                    };
                }
            }
        });



        // Convert Shamsi to Gregorian date
        function shamsiToGregorian(shamsiDate) {
            if (!shamsiDate) return '';
            const [year, month, day] = shamsiDate.split('/').map(Number);
            const gregorian = jalaali.toGregorian(year, month, day);
            return `${gregorian.gy}-${String(gregorian.gm).padStart(2, '0')}-${String(gregorian.gd).padStart(2, '0')}`;
        }

        // Convert Gregorian to Shamsi for display
        function gregorianToShamsi(gregorianDate) {
            if (!gregorianDate) return '';
            const [year, month, day] = gregorianDate.split(' ')[0].split('-').map(Number);
            const shamsi = jalaali.toJalaali(year, month, day);
            return `${shamsi.jy}/${String(shamsi.jm).padStart(2, '0')}/${String(shamsi.jd).padStart(2, '0')}`;
        }

        // Generate encrypted token
        function generateToken() {
            const secret = 'your-secret-key'; // Replace with a strong secret key
            const timestamp = Math.floor(Date.now() / 1000);
            const data = timestamp.toString();
            return {
                token: CryptoJS.HmacSHA256(data, secret).toString(),
                timestamp: timestamp
            };
        }

        // Render pagination
        function renderPagination(totalItems) {
            const totalPages = Math.ceil(totalItems / perPage);
            let html = '';
            if (totalPages > 1) {
                html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${currentPage - 1}">قبلی</a></li>`;
                for (let i = 1; i <= totalPages; i++) {
                    html += `<li class="page-item ${i === currentPage ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                }
                html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${currentPage + 1}">بعدی</a></li>`;
            }
            $('#pagination').html(html);
        }

        // Handle pagination click
        $('#pagination').on('click', '.page-link', function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            if (page && page !== currentPage) {
                currentPage = page;
                renderTable();
            }
        });

        // Render table
        function renderTable() {
            const calendarType = $('[name="calendar_type"]').val();
            const start = (currentPage - 1) * perPage;
            const end = start + perPage;
            const dataSlice = currentData.slice(start, end);
            let html = '';
            dataSlice.forEach(row => {
                let orderStatus = null;
                switch (row.order_status) {
                    case 'wc-walletx':
                        orderStatus = 'موفق - کیف پول';
                        break;
                    case 'wc-refunded':
                        orderStatus = 'موفق - مسترد شده';
                        break;
                    default:
                        orderStatus = row.order_status;
                        break;
                }
                html += `
                    <tr>
                        <td>${row.customer_id || ''}</td>
                        <td>${row.customer_firstname || ''}</td>
                        <td>${row.customer_lastname || ''}</td>
                        <td>${row.customer_phone || ''}</td>
                        <td>${calendarType === 'shamsi' ? gregorianToShamsi(row.customer_registered_at) : (row.customer_registered_at || '')}</td>
                        <td>${row.order_id || ''}</td>
                        <td>${orderStatus}</td>
                        <td>${row.order_sans_time || ''}</td>
                        <td>${row.order_sans_day || ''}</td>
                        <td>${calendarType === 'shamsi' ? gregorianToShamsi(row.order_sans_date) : (row.order_sans_date || '')}</td>
                        <td>${row.order_tickets_quantity || ''}</td>
                        <td>${(typeof row.order_paid === 'number') ? row.order_paid.toLocaleString() : ''}</td>
                        <td>${(typeof row.order_net_profit === 'number') ? row.order_net_profit.toLocaleString() : ''}</td>
                        <td>${row.order_phones || ''}</td>
                        <td>${row.order_refrerr || ''}</td>
                        <td>${calendarType === 'shamsi' ? gregorianToShamsi(row.order_created_at) : (row.order_created_at || '')}</td>
                        <td>${row.game_id || ''}</td>
                        <td>${row.game_name || ''}</td>
                        <td>${row.game_city || ''}</td>
                        <td>${row.game_area || ''}</td>
                        <td>${row.game_product_type || ''}</td>
                        <td>${row.game_genres || ''}</td>
                        <td>${row.game_duration || ''}</td>
                        <td>${calendarType === 'shamsi' ? gregorianToShamsi(row.game_created_at) : (row.game_created_at || '')}</td>
                    </tr>`;

            });
            $('#results').html(html);
            renderPagination(currentData.length);
        }

        // Handle form submission
        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serializeArray();
            const filters = {};
            const calendarType = $('[name="calendar_type"]').val();
            perPage = parseInt($('[name="per_page"]').val()) || 10;
            currentPage = 1;

            formData.forEach(item => {
                if (item.value) {
                    if (item.name.includes('_from') || item.name.includes('_to')) {
                        const field = item.name.replace('_from', '').replace('_to', '');
                        const suffix = item.name.includes('_from') ? '_from' : '_to';
                        filters[field + suffix] = calendarType === 'shamsi' ? shamsiToGregorian(item.value) : item.value;
                    } else if (item.name.includes('[]')) {
                        const field = item.name.replace('[]', '');
                        if (!filters[field]) filters[field] = [];
                        filters[field].push(item.value);
                    } else if (item.name === 'order_status') {
                        if (item.value === 'successful') {
                            filters[item.name] = ['partially-paid', 'completed', 'walletx'];
                        } else if (item.value === 'unsuccessful') {
                            filters[item.name] = ['wc-pending', 'wc-cancelled', 'wc-refunded'];
                        } else {
                            filters[item.name] = item.value;
                        }
                    } else {
                        filters[item.name] = item.value;
                    }
                }
            });

            const { token, timestamp } = generateToken();

            $.ajax({
                url: '/api.php',
                type: 'POST',
                data: { 
                    filters: JSON.stringify(filters),
                    token: token,
                    timestamp: timestamp
                },
                success: function(response) {
                    if (response.error) {
                        $('#results').html(`<tr><td colspan="24" class="text-center text-danger">${response.error}</td></tr>`);
                        $('#pagination').empty();
                        return;
                    }
                    currentData = response;
                    renderTable();
                },
                error: function(xhr) {
                    console.error('خطا در درخواست AJAX:', xhr.status, xhr.statusText);
                    $('#results').html(`<tr><td colspan="24" class="text-center text-danger">خطا در بارگذاری داده‌ها: ${xhr.statusText}</td></tr>`);
                    $('#pagination').empty();
                }
            });
        });

        // Update date pickers on calendar type change
        $('[name="calendar_type"]').on('change', function() {
            initializeDatePickers();
            $('.date-picker').each(function() {
                $(this).val(''); // پاک کردن مقادیر قبلی
            });
        });

        // Initialize date pickers on load
        initializeDatePickers();

        // Export to Excel
        $('#exportExcel').on('click', function() {
            const calendarType = $('[name="calendar_type"]').val();
            const data = currentData.map(row => {
                // محاسبه orderStatus برای هر row داخل map
                let orderStatus = null;
                switch (row.order_status) {
                    case 'wc-walletx':
                        orderStatus = 'موفق - کیف پول';
                        break;
                    case 'wc-refunded':
                        orderStatus = 'موفق - مسترد شده';
                        break;
                    default:
                        orderStatus = row.order_status || '';
                        break;
                }
                
                return {
                    'شناسه مشتری': row.customer_id || '',
                    'نام': row.customer_firstname || '',
                    'نام خانوادگی': row.customer_lastname || '',
                    'تلفن': row.customer_phone || '',
                    'تاریخ ثبت‌نام': calendarType === 'shamsi' ? gregorianToShamsi(row.customer_registered_at) : row.customer_registered_at || '',
                    'شناسه سفارش': row.order_id || '',
                    'وضعیت سفارش': orderStatus,
                    'زمان سانس': row.order_sans_time || '',
                    'روز سانس': row.order_sans_day || '',
                    'تاریخ سانس': calendarType === 'shamsi' ? gregorianToShamsi(row.order_sans_date) : row.order_sans_date || '',
                    'تعداد بلیط': row.order_tickets_quantity || '',
                    'مبلغ پرداخت شده': row.order_paid || '',
                    'درآمد سایت': row.order_net_profit || '',
                    'شماره‌های سفارش': row.order_phones || '',
                    'منبع': row.order_refrerr || '',
                    'تاریخ ایجاد': calendarType === 'shamsi' ? gregorianToShamsi(row.order_created_at) : row.order_created_at || '',
                    'شناسه بازی': row.game_id || '',
                    'نام بازی': row.game_name || '',
                    'شهر بازی': row.game_city || '',
                    'منطقه بازی': row.game_area || '',
                    'نوع بازی': row.game_product_type || '',
                    'ژانر بازی': row.game_genres || '',
                    'مدت زمان بازی': row.game_duration || '',
                    'تاریخ ساخت بازی': calendarType === 'shamsi' ? gregorianToShamsi(row.game_created_at) : row.game_created_at || ''
                };
            });
            const ws = XLSX.utils.json_to_sheet(data);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'گزارش');
            XLSX.writeFile(wb, 'report.xlsx');
        });

        // Export to CSV
        $('#exportCsv').on('click', function() {
            const calendarType = $('[name="calendar_type"]').val();
                        const data = currentData.map(row => {
                // محاسبه orderStatus برای هر row داخل map
                let orderStatus = null;
                switch (row.order_status) {
                    case 'wc-walletx':
                        orderStatus = 'موفق - کیف پول';
                        break;
                    case 'wc-refunded':
                        orderStatus = 'موفق - مسترد شده';
                        break;
                    default:
                        orderStatus = row.order_status || '';
                        break;
                }
                
                return {
                    'شناسه مشتری': row.customer_id || '',
                    'نام': row.customer_firstname || '',
                    'نام خانوادگی': row.customer_lastname || '',
                    'تلفن': row.customer_phone || '',
                    'تاریخ ثبت‌نام': calendarType === 'shamsi' ? gregorianToShamsi(row.customer_registered_at) : row.customer_registered_at || '',
                    'شناسه سفارش': row.order_id || '',
                    'وضعیت سفارش': orderStatus,
                    'زمان سانس': row.order_sans_time || '',
                    'روز سانس': row.order_sans_day || '',
                    'تاریخ سانس': calendarType === 'shamsi' ? gregorianToShamsi(row.order_sans_date) : row.order_sans_date || '',
                    'تعداد بلیط': row.order_tickets_quantity || '',
                    'مبلغ پرداخت شده': row.order_paid || '',
                    'درآمد سایت': row.order_net_profit || '',
                    'شماره‌های سفارش': row.order_phones || '',
                    'منبع': row.order_refrerr || '',
                    'تاریخ ایجاد': calendarType === 'shamsi' ? gregorianToShamsi(row.order_created_at) : row.order_created_at || '',
                    'شناسه بازی': row.game_id || '',
                    'نام بازی': row.game_name || '',
                    'شهر بازی': row.game_city || '',
                    'منطقه بازی': row.game_area || '',
                    'نوع بازی': row.game_product_type || '',
                    'ژانر بازی': row.game_genres || '',
                    'مدت زمان بازی': row.game_duration || '',
                    'تاریخ ساخت بازی': calendarType === 'shamsi' ? gregorianToShamsi(row.game_created_at) : row.game_created_at || ''
                };
            });
            const csv = Papa.unparse(data, {
                quotes: true,
                delimiter: ',',
                header: true
            });
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'report.csv';
            link.click();
        });
    </script>
</body>
</html>
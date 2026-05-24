<style>
    .filter-submit-btn {
        background-color: #f97316;
        color: white;
        padding: 0.5rem 1.5rem;
        border-radius: 0.5rem;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        height: 48px;
    }

    .filter-submit-btn:hover {
        background-color: #ea580c;
    }
</style>
<div>
    <h1 class="text-base font-extrabold lg:text-2xl mb-6">گزارش گیری پیامک‌ها</h1>
</div>

<!-- Filter Section -->
<form id="sms-report-form" class="filter-form">
    <input type="hidden" name="action" value="team_ajax_handler">
    <input type="hidden" name="callback" value="sms_report">
    <!-- از آنجایی که دراپ‌داون حذف شد، نوع پیامک را روی 2 (ارسالی) فیکس می‌کنیم -->
    <input type="hidden" name="location" value="2">
    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('team-ajax-nonce'); ?>">
    
    <!-- اینپوت مخفی برای مدیریت ایندکس صفحات -->
    <input type="hidden" id="api_index" name="api_index" value="0">

    <!-- اضافه کردن items-end برای هم‌تراز شدن دکمه با اینپوت‌ها در یک ردیف -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end mb-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">شماره خط</label>
            <input type="text" name="from" placeholder="مثال: 09120000000" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">از تاریخ</label>
            <input type="text" name="date_from" placeholder="مثلا 1403/01/01" class="date-picker w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">تا تاریخ</label>
            <input type="text" name="date_to" placeholder="مثلا 1403/05/01" class="date-picker w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>

        <!-- دکمه نمایش در همان ردیف -->
        <div>
            <button type="submit" class="filter-submit-btn" id="show-results-btn">
                نمایش
            </button>
        </div>
    </div>
</form>

<!-- جدول نتایج -->
<div id="sms-results-container" class="mt-6">
    <!-- دیتا اینجا پر می‌شود -->
</div>

<script>
jQuery(document).ready(function($) {
    let historyStack = [];

    // تابع تبدیل تاریخ میلادی به شمسی
    function toJalali(dateString) {
        if (!dateString) return '';
        try {
            const date = new Date(dateString);
            // تبدیل تاریخ به شمسی و زمان به فرمت 24 ساعته
            const jalaliDate = date.toLocaleDateString('fa-IR');
            const jalaliTime = date.toLocaleTimeString('fa-IR', { hour: '2-digit', minute: '2-digit' });
            return jalaliDate + ' - ' + jalaliTime;
        } catch (e) {
            return dateString; // اگر تاریخ نامعتبر بود، همان مقدار اصلی را برگردان
        }
    }

    // زمان ارسال فرم جستجو
    $('#sms-report-form').on('submit', function(e) {
        e.preventDefault();
        $('#api_index').val(0); 
        historyStack = [];      
        fetchData();
    });

    function fetchData() {
        const $form = $('#sms-report-form');
        const $container = $('#sms-results-container');
        
        $container.html('<div class="text-center py-8 text-indigo-600 font-bold">در حال بررسی و دریافت اطلاعات...</div>');

        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: $form.serialize(),
            success: function(response) {
                if (response.success) {
                    renderSmsTable(response.data.data, response.data.next_index, response.data.has_more);
                } else {
                    $container.html('<div class="text-red-500 text-center py-4">خطا: ' + response.data + '</div>');
                }
            },
            error: function() {
                $container.html('<div class="text-red-500 text-center py-4">خطا در ارتباط با سرور</div>');
            }
        });
    }

    function renderSmsTable(data, nextIndex, hasMore) {
        let html = '';
        
        // تولید ساختار دکمه‌های صفحه‌بندی
        let paginationHtml = '<div class="flex justify-between items-center my-4">';
        if (historyStack.length > 0) {
            paginationHtml += `<button type="button" class="btn-prev" style="padding: 5px 15px; font-size: 13px;border: 1px solid #d9d9d9;">صفحه قبل</button>`;
        } else {
            paginationHtml += `<div></div>`;
        }
        
        if (hasMore) {
            paginationHtml += `<button type="button" class="btn-next" data-next="${nextIndex}" style="padding: 5px 15px; font-size: 13px;border: 1px solid #d9d9d9;">صفحه بعدی</button>`;
        }
        paginationHtml += '</div>';

        if (!data || data.length === 0) {
            html = '<div class="text-center py-8 text-gray-500 bg-gray-50 rounded-lg">در این بازه رکوردی یافت نشد.</div>';
            $('#sms-results-container').html(html + (historyStack.length > 0 ? paginationHtml : ''));
            return;
        }

        // دکمه‌های صفحه‌بندی (بالای جدول)
        html += paginationHtml;

        html += `
            <div class="overflow-x-auto shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-900">ردیف</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-900">شناسه</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-900">شماره</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-900">متن پیام</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-900">تاریخ</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
        `;

        data.forEach((row, index) => {
            // اعمال تابع تاریخ شمسی روی تاریخ میلادی
            let shamsiDate = toJalali(row.date);

            html += `
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-bold">${index + 1}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${row.id}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono" dir="ltr">${row.mobile}</td>
                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="${row.body}">${row.body}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" dir="ltr">${shamsiDate}</td>
                </tr>
            `;
        });

        html += `</tbody></table></div>`;

        // دکمه‌های صفحه‌بندی (پایین جدول)
        html += paginationHtml;

        $('#sms-results-container').html(html);
    }

    $(document).on('click', '.btn-next', function() {
        let currentIndex = $('#api_index').val();
        historyStack.push(currentIndex); 
        
        let nextIndex = $(this).data('next');
        $('#api_index').val(nextIndex); 
        fetchData();
    });

    $(document).on('click', '.btn-prev', function() {
        let prevIndex = historyStack.pop(); 
        $('#api_index').val(prevIndex);
        fetchData();
    });
});
</script>

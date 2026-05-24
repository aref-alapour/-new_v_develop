<?php

/**
 * Calendar Usage Example
 * 
 * This file demonstrates how to use the Persian calendar component in other pages.
 * Copy this structure to any page where you need calendar functionality.
 */
?>

<!DOCTYPE html>
<html dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقویم شمسی - مثال استفاده</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">مثال استفاده از تقویم شمسی</h1>

        <!-- Calendar Button -->
        <div class="mb-6">
            <button type="button" id="calendar-btn" class="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                <span class="text-sm font-medium">انتخاب تاریخ</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="14" viewBox="0 0 15 14" fill="none">
                    <g clip-path="url(#clip0_6168_4144)">
                        <path d="M1.41602 6.99999C1.41602 4.80024 1.41602 3.70008 2.09968 3.01699C2.78335 2.33391 3.88293 2.33333 6.08268 2.33333H8.41602C10.6158 2.33333 11.7159 2.33333 12.399 3.01699C13.0821 3.70066 13.0827 4.80024 13.0827 6.99999V8.16666C13.0827 10.3664 13.0827 11.4666 12.399 12.1497C11.7153 12.8327 10.6158 12.8333 8.41602 12.8333H6.08268C3.88293 12.8333 2.78277 12.8333 2.09968 12.1497C1.4166 11.466 1.41602 10.3664 1.41602 8.16666V6.99999Z" stroke="white" stroke-width="1.5" />
                        <path d="M4.33203 2.33333V1.45833M10.1654 2.33333V1.45833M1.70703 5.25H12.7904" stroke="white" stroke-width="1.5" stroke-linecap="round" />
                        <path d="M10.75 9.91667C10.75 10.0714 10.6885 10.2197 10.5791 10.3291C10.4697 10.4385 10.3214 10.5 10.1667 10.5C10.012 10.5 9.86358 10.4385 9.75419 10.3291C9.64479 10.2197 9.58333 10.0714 9.58333 9.91667C9.58333 9.76196 9.64479 9.61358 9.75419 9.50419C9.86358 9.39479 10.012 9.33333 10.1667 9.33333C10.3214 9.33333 10.4697 9.39479 10.5791 9.50419C10.6885 9.61358 10.75 9.76196 10.75 9.91667ZM10.75 7.58333C10.75 7.73804 10.6885 7.88642 10.5791 7.99581C10.4697 8.10521 10.3214 8.16667 10.1667 8.16667C10.012 8.16667 9.86358 8.10521 9.75419 7.99581C9.64479 7.88642 9.58333 7.73804 9.58333 7.58333C9.58333 7.42862 9.64479 7.28025 9.75419 7.17085C9.86358 7.06146 10.012 7 10.1667 7C10.3214 7 10.4697 7.06146 10.5791 7.17085C10.6885 7.28025 10.75 7.42862 10.75 7.58333ZM7.83333 9.91667C7.83333 10.0714 7.77187 10.2197 7.66248 10.3291C7.55308 10.4385 7.40471 10.5 7.25 10.5C7.09529 10.5 6.94692 10.4385 6.83752 10.3291C6.72812 10.2197 6.66667 10.0714 6.66667 9.91667C6.66667 9.76196 6.72812 9.61358 6.83752 9.50419C6.94692 9.39479 7.09529 9.33333 7.25 9.33333C7.40471 9.33333 7.55308 9.39479 7.66248 9.50419C7.77187 9.61358 7.83333 9.76196 7.83333 9.91667ZM7.83333 7.58333C7.83333 7.73804 7.77187 7.88642 7.66248 7.99581C7.55308 8.10521 7.40471 8.16667 7.25 8.16667C7.09529 8.16667 6.94692 8.10521 6.83752 7.99581C6.72812 7.88642 6.66667 7.73804 6.66667 7.58333C6.66667 7.42862 6.72812 7.28025 6.83752 7.17085C6.94692 7.06146 7.09529 7 7.25 7C7.40471 7 7.55308 7.06146 7.66248 7.17085C7.77187 7.28025 7.83333 7.42862 7.83333 7.58333ZM4.91667 9.91667C4.91667 10.0714 4.85521 10.2197 4.74581 10.3291C4.63642 10.4385 4.48804 10.5 4.33333 10.5C4.17862 10.5 4.03025 10.4385 3.92085 10.3291C3.81146 10.2197 3.75 10.0714 3.75 9.91667C3.75 9.76196 3.81146 9.61358 3.92085 9.50419C4.03025 9.39479 4.17862 9.33333 4.33333 9.33333C4.48804 9.33333 4.63642 9.39479 4.74581 9.50419C4.85521 9.61358 4.91667 9.76196 4.91667 9.91667ZM4.91667 7.58333C4.91667 7.73804 4.85521 7.88642 4.74581 7.99581C4.63642 8.10521 4.48804 8.16667 4.33333 8.16667C4.17862 8.16667 4.03025 8.10521 3.92085 7.99581C3.81146 7.88642 3.75 7.73804 3.75 7.58333C3.75 7.42862 3.81146 7.28025 3.92085 7.17085C4.03025 7.06146 4.17862 7 4.33333 7C4.48804 7 4.63642 7.06146 4.74581 7.17085C4.85521 7.28025 4.91667 7.42862 4.91667 7.58333Z" fill="white" />
                    </g>
                    <defs>
                        <clipPath id="clip0_6168_4144">
                            <rect width="14" height="14" fill="white" transform="translate(0.25)" />
                        </clipPath>
                    </defs>
                </svg>
            </button>
        </div>

        <!-- Selected Date Range Display -->
        <div class="bg-white p-4 rounded-lg shadow mb-6">
            <h3 class="text-lg font-semibold mb-2">بازه انتخاب شده:</h3>
            <div id="selected-date-display" class="text-gray-600">تاریخی انتخاب نشده</div>
        </div>

        <!-- Results Display -->
        <div class="bg-white p-4 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-2">اطلاعات انتخاب شده:</h3>
            <div id="results-display" class="text-gray-600">هیچ اطلاعاتی نمایش داده نمی‌شود</div>
        </div>
    </div>

    <!-- Include Calendar Layout -->
    <?php include __DIR__ . '/calendar-layout.php'; ?>

    <!-- Include Calendar Module -->
    <script src="<?php echo get_template_directory_uri(); ?>/assets/js/calendar-module.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize calendar with custom callbacks
            const calendar = new PersianCalendar({
                onDateRangeSelected: function(dateRange) {
                    // Handle date range selection
                    console.log('Date range selected:', dateRange);

                    // Update display
                    const display = document.getElementById('selected-date-display');
                    const results = document.getElementById('results-display');

                    if (dateRange.startDate && dateRange.endDate) {
                        const persianMonths = [
                            'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
                            'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'
                        ];

                        const startStr = `${dateRange.startDate.day} ${persianMonths[dateRange.startDate.month - 1]} ${dateRange.startDate.year}`;
                        const endStr = `${dateRange.endDate.day} ${persianMonths[dateRange.endDate.month - 1]} ${dateRange.endDate.year}`;

                        display.textContent = `${startStr} تا ${endStr}`;

                        results.innerHTML = `
                            <div class="space-y-2">
                                <p><strong>تاریخ شروع:</strong> ${startStr}</p>
                                <p><strong>تاریخ پایان:</strong> ${endStr}</p>
                                <p><strong>تاریخ شروع میلادی:</strong> ${dateRange.startGregorian.toLocaleDateString('fa-IR')}</p>
                                <p><strong>تاریخ پایان میلادی:</strong> ${dateRange.endGregorian.toLocaleDateString('fa-IR')}</p>
                            </div>
                        `;
                    }
                },
                onDateRangeCleared: function() {
                    // Handle date range cleared
                    console.log('Date range cleared');

                    document.getElementById('selected-date-display').textContent = 'تاریخی انتخاب نشده';
                    document.getElementById('results-display').textContent = 'هیچ اطلاعاتی نمایش داده نمی‌شود';
                }
            });
        });
    </script>
</body>

</html>
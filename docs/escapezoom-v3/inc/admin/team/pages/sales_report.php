<?php
// Only initialize basic variables for UI
$search_query = '';
$time_range = 'one_week';
?>

<div class="sales-report-container">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-base font-extrabold lg:text-2xl">گزارش فروش</h1>
    </div>

    <!-- Search and Filter Section -->
    <div class="flex items-center justify-between gap-4">
        <!-- Hidden input for selected game ID -->
        <input type="hidden" id="selected-game-id" name="selected_game_id" value="">

        <!-- Search Bar -->
        <div class="search-container">
            <!-- Search Input (visible when no game selected) -->
            <div id="search-input-container" class="relative">
                <input type="text"
                    name="search_query"
                    value="<?php echo esc_attr($search_query); ?>"
                    placeholder="جستجو بازی"
                    class="search-input"
                    id="search-input">
                <button type="button" id="search-btn" class="search-btn">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </button>

                <!-- Search Results Dropdown -->
                <div id="search-results-sales" class="absolute top-full left-0 right-0 bg-white border border-gray-300 rounded-lg shadow-lg mt-1 hidden z-10 max-h-60 overflow-y-auto">
                    <!-- Search results will be inserted here -->
                </div>
            </div>

            <!-- Selected Game Tag (hidden by default) -->
            <div id="selected-game-tag" class="selected-game-tag flex hidden">
                <div class="selected-game-content">
                    <img id="selected-game-image" src="" alt="Game" class="selected-game-image">
                    <span id="selected-game-name-display" class="selected-game-name"></span>
                </div>
                <button type="button" id="clear-selected-game" class="clear-game-btn">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        <!-- Search Bar and Time Range Filters -->
        <div class="flex items-center justify-between gap-4">
            <!-- Time Range Filters -->
            <div class="time-range-filter">
                <div class="time-range-option cursor-pointer <?php echo $time_range === 'one_week' ? 'active' : ''; ?>">
                    <input type="radio" id="one_week" name="time_range" value="one_week"
                        <?php checked($time_range, 'one_week'); ?>>
                    <label for="one_week">یک هفته</label>
                </div>
                <div class="time-range-option cursor-pointer <?php echo $time_range === 'one_month' ? 'active' : ''; ?>">
                    <input type="radio" id="one_month" name="time_range" value="one_month"
                        <?php checked($time_range, 'one_month'); ?>>
                    <label for="one_month">یک ماه</label>
                </div>
                <div class="time-range-option cursor-pointer <?php echo $time_range === 'three_months' ? 'active' : ''; ?>">
                    <input type="radio" id="three_months" name="time_range" value="three_months"
                        <?php checked($time_range, 'three_months'); ?>>
                    <label for="three_months">سه ماه</label>
                </div>
                <button type="button" id="calendar-btn" class="flex items-center gap-2 mt-[-5px]">
                    <span class="text-sm font-medium text-blue-600">تقویم</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="14" viewBox="0 0 15 14" fill="none">
                        <g clip-path="url(#clip0_6168_4144)">
                            <path d="M1.41602 6.99999C1.41602 4.80024 1.41602 3.70008 2.09968 3.01699C2.78335 2.33391 3.88293 2.33333 6.08268 2.33333H8.41602C10.6158 2.33333 11.7159 2.33333 12.399 3.01699C13.0821 3.70066 13.0827 4.80024 13.0827 6.99999V8.16666C13.0827 10.3664 13.0827 11.4666 12.399 12.1497C11.7153 12.8327 10.6158 12.8333 8.41602 12.8333H6.08268C3.88293 12.8333 2.78277 12.8333 2.09968 12.1497C1.4166 11.466 1.41602 10.3664 1.41602 8.16666V6.99999Z" stroke="#1447E6" stroke-width="1.5" />
                            <path d="M4.33203 2.33333V1.45833M10.1654 2.33333V1.45833M1.70703 5.25H12.7904" stroke="#1447E6" stroke-width="1.5" stroke-linecap="round" />
                            <path d="M10.75 9.91667C10.75 10.0714 10.6885 10.2197 10.5791 10.3291C10.4697 10.4385 10.3214 10.5 10.1667 10.5C10.012 10.5 9.86358 10.4385 9.75419 10.3291C9.64479 10.2197 9.58333 10.0714 9.58333 9.91667C9.58333 9.76196 9.64479 9.61358 9.75419 9.50419C9.86358 9.39479 10.012 9.33333 10.1667 9.33333C10.3214 9.33333 10.4697 9.39479 10.5791 9.50419C10.6885 9.61358 10.75 9.76196 10.75 9.91667ZM10.75 7.58333C10.75 7.73804 10.6885 7.88642 10.5791 7.99581C10.4697 8.10521 10.3214 8.16667 10.1667 8.16667C10.012 8.16667 9.86358 8.10521 9.75419 7.99581C9.64479 7.88642 9.58333 7.73804 9.58333 7.58333C9.58333 7.42862 9.64479 7.28025 9.75419 7.17085C9.86358 7.06146 10.012 7 10.1667 7C10.3214 7 10.4697 7.06146 10.5791 7.17085C10.6885 7.28025 10.75 7.42862 10.75 7.58333ZM7.83333 9.91667C7.83333 10.0714 7.77187 10.2197 7.66248 10.3291C7.55308 10.4385 7.40471 10.5 7.25 10.5C7.09529 10.5 6.94692 10.4385 6.83752 10.3291C6.72812 10.2197 6.66667 10.0714 6.66667 9.91667C6.66667 9.76196 6.72812 9.61358 6.83752 9.50419C6.94692 9.39479 7.09529 9.33333 7.25 9.33333C7.40471 9.33333 7.55308 9.39479 7.66248 9.50419C7.77187 9.61358 7.83333 9.76196 7.83333 9.91667ZM7.83333 7.58333C7.83333 7.73804 7.77187 7.88642 7.66248 7.99581C7.55308 8.10521 7.40471 8.16667 7.25 8.16667C7.09529 8.16667 6.94692 8.10521 6.83752 7.99581C6.72812 7.88642 6.66667 7.73804 6.66667 7.58333C6.66667 7.42862 6.72812 7.28025 6.83752 7.17085C6.94692 7.06146 7.09529 7 7.25 7C7.40471 7 7.55308 7.06146 7.66248 7.17085C7.77187 7.28025 7.83333 7.42862 7.83333 7.58333ZM4.91667 9.91667C4.91667 10.0714 4.85521 10.2197 4.74581 10.3291C4.63642 10.4385 4.48804 10.5 4.33333 10.5C4.17862 10.5 4.03025 10.4385 3.92085 10.3291C3.81146 10.2197 3.75 10.0714 3.75 9.91667C3.75 9.76196 3.81146 9.61358 3.92085 9.50419C4.03025 9.39479 4.17862 9.33333 4.33333 9.33333C4.48804 9.33333 4.63642 9.39479 4.74581 9.50419C4.85521 9.61358 4.91667 9.76196 4.91667 9.91667ZM4.91667 7.58333C4.91667 7.73804 4.85521 7.88642 4.74581 7.99581C4.63642 8.10521 4.48804 8.16667 4.33333 8.16667C4.17862 8.16667 4.03025 8.10521 3.92085 7.99581C3.81146 7.88642 3.75 7.73804 3.75 7.58333C3.75 7.42862 3.81146 7.28025 3.92085 7.17085C4.03025 7.06146 4.17862 7 4.33333 7C4.48804 7 4.63642 7.06146 4.74581 7.17085C4.85521 7.28025 4.91667 7.42862 4.91667 7.58333Z" fill="#1447E6" />
                        </g>
                        <defs>
                            <clipPath id="clip0_6168_4144">
                                <rect width="14" height="14" fill="white" transform="translate(0.25)" />
                            </clipPath>
                        </defs>
                    </svg>
                </button>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-sm font-medium text-gray-700">بازه زمانی:</span>
            <span id="time-range-display" class="text-gray-500">-</span>
        </div>
    </div>

    <!-- Include Calendar Layout -->
    <?php include get_template_directory() . '/template/calendar/calendar-layout.php'; ?>

    <hr class="my-8">
    <!-- Results Section -->
    <div id="results-section" class="results-section">
        <!-- Initial Empty State -->
        <div class="mt-30 text-center">
            <div class="text-gray-500 space-y-2 font-bold">
                <p>برای مشاهده نتایج،</p>
                <p>در قسمت جستجوی بالا نام بازی مورد نظر را وارد</p>
                <p>نمایید، سپس بازه زمانی را مشخص کنید</p>
            </div>
        </div>
    </div>
</div>

<style>
    .sales-report-container {
        direction: rtl;
    }

    /* Time range filter styling */
    .time-range-filter {
        display: flex;
        align-items: center;
        gap: 24px;
    }

    .time-range-option {
        position: relative;
        cursor: pointer;
        padding-bottom: 4px;
        transition: all 0.3s ease;
    }

    .time-range-option:hover {
        color: #f97316;
    }

    .time-range-option.active {
        color: #f97316;
        border-bottom: 2px solid #f97316;
    }

    .time-range-option input[type="radio"] {
        display: none;
    }

    /* Search bar styling */
    .search-container {
        max-width: 400px;
        width: 100%;
    }

    .search-input {
        width: 100%;
        padding: 12px 16px 12px 16px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        background-color: #FAFDFF;
        transition: all 0.3s ease;
    }

    .search-input:focus {
        outline: none;
        border-color: #f97316;
        box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
    }

    .search-btn {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #9ca3af;
        cursor: pointer;
        padding: 4px;
        border-radius: 4px;
        transition: all 0.3s ease;
    }

    .search-btn:hover {
        color: #f97316;
        background-color: rgba(249, 115, 22, 0.1);
    }

    .product-image {
        position: absolute;
        right: 50px;
        top: 50%;
        transform: translateY(-50%);
    }

    .product-image img {
        border-radius: 4px;
        object-fit: cover;
    }


    /* Search results dropdown styling */
    .search-result-item {
        padding: 12px 16px;
        cursor: pointer;
        border-bottom: 1px solid #e5e7eb;
        transition: background-color 0.2s;
    }

    .search-result-item:hover {
        background-color: #f3f4f6;
    }

    .search-result-item:last-child {
        border-bottom: none;
    }

    .search-result-name {
        font-weight: 600;
        color: #374151;
        margin-bottom: 4px;
    }

    .search-result-image {
        width: 28px;
        height: 36px;
        border-radius: 4px;
        object-fit: cover;
        margin-left: 8px;
    }

    /* Selected game tag styling */
    .selected-game-tag {
        align-items: center;
        background-color: #f9fafb;
        border: 1px solid #d1d5db;
        border-radius: 12px;
        padding: 4px 12px;
        gap: 8px;
        max-width: 400px;
        width: 100%;
        height: 58px;
    }

    .clear-game-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        color: #6b7280;
        transition: color 0.2s;
        cursor: pointer;
        margin-left: 4px;
    }

    .clear-game-btn:hover {
        color: #374151;
    }

    .selected-game-content {
        display: flex;
        align-items: center;
        gap: 8px;
        flex: 1;
    }

    .selected-game-image {
        width: 28px;
        height: 36px;
        border-radius: 6px;
        object-fit: cover;
    }

    .selected-game-name {
        font-weight: 600;
        color: #374151;
        font-size: 14px;
    }

    /* Comparison UI styling */
    .comparison-container {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
    }

    .comparison-card {
        background: #f9fafb;
        border-radius: 8px;
        padding: 20px;
        border: 1px solid #e5e7eb;
    }

    .card-header {
        text-align: center;
        margin-bottom: 16px;
    }

    .card-content {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .current-value {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    .previous-value {
        text-align: center;
        padding-top: 8px;
        border-top: 1px solid #d1d5db;
    }

    .change-indicator {
        display: flex;
        align-items: center;
        gap: 4px;
        padding: 4px 8px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 12px;
    }

    .change-indicator.positive {
        background-color: #d1fae5;
        color: #065f46;
    }

    .change-indicator.negative {
        background-color: #fee2e2;
        color: #991b1b;
    }

    .change-indicator svg {
        width: 16px;
        height: 16px;
    }

    /* Table responsive styling */
    @media (max-width: 768px) {
        .overflow-x-auto {
            -webkit-overflow-scrolling: touch;
        }

        .time-range-filter {
            flex-wrap: wrap;
            gap: 16px;
        }

        .search-container {
            max-width: 100%;
        }
    }
</style>

<script>
    // Enhanced sales report functionality
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.querySelector('input[name="search_query"]');
        const timeRangeInputs = document.querySelectorAll('input[name="time_range"]');
        const form = document.querySelector('form');
        const resultsContainer = document.querySelector('.sales-report-container');
        const loadingSpinner = createLoadingSpinner();

        // Create loading spinner
        function createLoadingSpinner() {
            const spinner = document.createElement('div');
            spinner.className = 'loading-spinner hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
            spinner.innerHTML = `
            <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-orange-500"></div>
                <span class="text-gray-700">در حال بارگذاری...</span>
            </div>
        `;
            document.body.appendChild(spinner);
            return spinner;
        }

        // Show/hide loading
        function showLoading() {
            loadingSpinner.classList.remove('hidden');
        }

        function hideLoading() {
            loadingSpinner.classList.add('hidden');
        }

        // AJAX search function - only triggers when a game is selected (initial search)
        function performSearch() {
            const selectedGameId = document.getElementById('selected-game-id').value;

            // Only perform AJAX if a game is selected
            if (!selectedGameId || selectedGameId.trim() === '') {
                return;
            }

            // Reset comparison mode
            isComparisonMode = false;
            initialResults = null;
            comparisonResults = null;

            showLoading();

            const formData = new FormData();
            formData.append('action', 'team_ajax_handler');
            formData.append('callback', 'sales_report_search');
            formData.append('nonce', '<?php echo wp_create_nonce('team-ajax-nonce'); ?>');
            formData.append('game_id', selectedGameId);

            // Get the selected time range
            const timeRange = document.querySelector('input[name="time_range"]:checked').value;
            formData.append('time_range', timeRange);

            // If it's a calendar selection, add the date range
            if (timeRange === 'calendar') {
                const dateRange = calendar.getSelectedDateRange();
                if (dateRange.startDate && dateRange.endDate) {
                    const startDateStr = dateRange.startGregorian.getFullYear() + '-' +
                        String(dateRange.startGregorian.getMonth() + 1).padStart(2, '0') + '-' +
                        String(dateRange.startGregorian.getDate()).padStart(2, '0');
                    const endDateStr = dateRange.endGregorian.getFullYear() + '-' +
                        String(dateRange.endGregorian.getMonth() + 1).padStart(2, '0') + '-' +
                        String(dateRange.endGregorian.getDate()).padStart(2, '0');

                    formData.append('start_date', startDateStr);
                    formData.append('end_date', endDateStr);
                }
            }

            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    if (data.success) {
                        initialResults = data.data; // Store initial results
                        updateInitialResults(data.data);
                    } else {
                        showError(data.data || 'خطا در دریافت اطلاعات');
                    }
                })
                .catch(error => {
                    hideLoading();
                    console.error('Error:', error);
                    showError('خطا در ارتباط با سرور: ' + error.message);
                });
        }

        // Update initial results display
        function updateInitialResults(data) {
            const {
                summary
            } = data;

            // Clear results section
            const resultsSection = document.getElementById('results-section');
            if (resultsSection) {
                resultsSection.innerHTML = '';
            }

            if (!summary || (summary.total_tickets === 0 && summary.total_views === 0 && summary.total_comments === 0)) {
                showEmptyState();
                return;
            }

            // Show initial results with comparison button
            showInitialResults(summary);
        }

        // Show initial results with comparison button
        function showInitialResults(summary) {
            const resultsSection = document.getElementById('results-section');

            const initialResultsHTML = `
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6 summary-cards">
                        <!-- کامنت -->
                        <div class="bg-gray-100 rounded-lg p-6 summary-card">
                            <div class="text-center">
                                <p class="text-sm font-medium text-gray-600 mb-2">کامنت</p>
                                <p class="text-3xl font-bold text-gray-900 summary-value">${summary.total_comments.toLocaleString()}</p>
                            </div>
                        </div>

                        <!-- بازدید -->
                        <div class="bg-gray-100 rounded-lg p-6 summary-card">
                            <div class="text-center">
                                <p class="text-sm font-medium text-gray-600 mb-2">بازدید</p>
                                <p class="text-3xl font-bold text-gray-900 summary-value">${summary.total_views.toLocaleString()}</p>
                            </div>
                        </div>

                        <!-- تیکت -->
                        <div class="bg-gray-100 rounded-lg p-6 summary-card">
                            <div class="text-center">
                                <p class="text-sm font-medium text-gray-600 mb-2">تیکت</p>
                                <p class="text-3xl font-bold text-gray-900 summary-value">${summary.total_tickets.toLocaleString()}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Comparison Button -->
                <div class="flex items-center gap-x-4 after:relative after:block after:content-[''] after:w-full after:h-px after:bg-border-1">
                <button id="add-comparison-btn" class="flex items-center gap-x-4 shrink-0 font-bold">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <rect x="20" y="0.00012207" width="20" height="20" rx="4" transform="rotate(90 20 0.00012207)" fill="#F1F5F9"/>
                    <path d="M11.5 14.5001L11.5 11.5001L14.5 11.5001C14.8978 11.5001 15.2794 11.3421 15.5607 11.0608C15.842 10.7795 16 10.3979 16 10.0001C16 9.6023 15.842 9.22077 15.5607 8.93946C15.2794 8.65816 14.8978 8.50012 14.5 8.50012L11.5 8.55337L11.5 5.50012C11.5 5.1023 11.342 4.72077 11.0607 4.43946C10.7794 4.15816 10.3978 4.00012 10 4.00012C9.60218 4.00012 9.22065 4.15816 8.93934 4.43946C8.65804 4.72077 8.5 5.1023 8.5 5.50012L8.55325 8.55337L5.5 8.50012C5.10218 8.50012 4.72065 8.65816 4.43934 8.93946C4.15804 9.22077 4 9.6023 4 10.0001C4 10.3979 4.15804 10.7795 4.43934 11.0608C4.72065 11.3421 5.10218 11.5001 5.5 11.5001L8.55325 11.5001L8.5 14.5001C8.5 14.8979 8.65804 15.2795 8.93934 15.5608C9.22064 15.8421 9.60218 16.0001 10 16.0001C10.3978 16.0001 10.7794 15.8421 11.0607 15.5608C11.342 15.2795 11.5 14.8979 11.5 14.5001Z" fill="#62748E"/>
                </svg>
                    مقایسه با بازه زمانی دیگر
                </button>
                </div>
            `;

            resultsSection.innerHTML = initialResultsHTML;

            // Add event listener for comparison button
            const addComparisonBtn = document.getElementById('add-comparison-btn');
            if (addComparisonBtn) {
                addComparisonBtn.addEventListener('click', function() {
                    enableComparisonMode();
                });
            }
        }



        // Update time range display
        function updateTimeRangeDisplay() {
            const selectedRange = document.querySelector('input[name="time_range"]:checked').value;
            const timeRangeDisplay = document.getElementById('time-range-display');

            let displayText = '';
            const now = new Date();

            switch (selectedRange) {
                case 'one_week':
                    const weekAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);
                    displayText = `${formatPersianDate(weekAgo)}&nbsp;&nbsp;تا&nbsp;&nbsp;${formatPersianDate(now)}`;
                    break;
                case 'one_month':
                    const monthAgo = new Date(now.getTime() - 30 * 24 * 60 * 60 * 1000);
                    displayText = `${formatPersianDate(monthAgo)}&nbsp;&nbsp;تا&nbsp;&nbsp;${formatPersianDate(now)}`;
                    break;
                case 'three_months':
                    const threeMonthsAgo = new Date(now.getTime() - 90 * 24 * 60 * 60 * 1000);
                    displayText = `${formatPersianDate(threeMonthsAgo)}&nbsp;&nbsp;تا&nbsp;&nbsp;${formatPersianDate(now)}`;
                    break;
                default:
                    displayText = '-';
            }

            timeRangeDisplay.innerHTML = displayText;
        }

        // Format date to Persian calendar using calendar module
        function formatPersianDate(date) {
            return calendar.formatPersianDate(date);
        }


        // Show empty state
        function showEmptyState() {
            const resultsSection = document.getElementById('results-section');
            if (resultsSection) {
                resultsSection.innerHTML = `
                <div class="text-center mt-30">
                    <div class="text-gray-500 space-y-2">
                        <p>برای مشاهده نتایج،</p>
                        <p>در قسمت جستجوی بالا نام بازی مورد نظر را وارد</p>
                        <p>نمایید، سپس بازه زمانی را مشخص کنید</p>
                    </div>
                </div>
            `;
            }
        }

        // Show error message
        function showError(message) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4';
            errorDiv.textContent = message;

            const container = document.querySelector('.sales-report-container');
            const firstChild = container.firstElementChild;
            container.insertBefore(errorDiv, firstChild);

            setTimeout(() => {
                errorDiv.remove();
            }, 5000);
        }

        // Format date
        function formatDate(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            return date.toLocaleDateString('fa-IR') + ' ' + date.toLocaleTimeString('fa-IR', {
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // Auto-submit form when time range changes
        timeRangeInputs.forEach(input => {
            input.addEventListener('change', function() {
                // Uncheck calendar option when other time ranges are selected
                const calendarRadio = document.querySelector('input[name="time_range"][value="calendar"]');
                if (calendarRadio && this.value !== 'calendar') {
                    calendarRadio.checked = false;
                }

                // Update active class - remove from all options first
                document.querySelectorAll('.time-range-option').forEach(option => {
                    option.classList.remove('active');
                });

                // Add active class only to the selected option
                this.closest('.time-range-option').classList.add('active');

                // Always update time range display
                updateTimeRangeDisplay();

                // Only perform AJAX search if a game is selected
                const selectedGameId = document.getElementById('selected-game-id').value;
                if (selectedGameId && selectedGameId.trim() !== '') {
                    performSearch();
                }
            });
        });


        // Search button click handler
        const searchBtn = document.getElementById('search-btn');
        searchBtn.addEventListener('click', function() {
            performSearch();
        });

        // Search input change handler - perform search as user types
        searchInput.addEventListener('input', function(e) {
            const query = e.target.value.trim();

            if (query === '') {
                hideSearchResults();
            } else {
                // Perform search after a short delay
                clearTimeout(searchInput.searchTimeout);
                searchInput.searchTimeout = setTimeout(() => {
                    performGameSearch(query);
                }, 300);
            }
        });

        // Search input enter key handler
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                performSearch();
            }
        });

        // Form submit handler (prevent default form submission)
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            performSearch();
        });

        // Calendar functionality using the new module
        let isComparisonMode = false; // Track if we're in comparison mode
        let initialResults = null; // Store initial results
        let comparisonResults = null; // Store comparison results

        // Initialize calendar module
        const calendar = new PersianCalendar({
            onDateRangeSelected: function(dateRange) {
                applyCustomDateRange(dateRange);
            },
            onDateRangeCleared: function() {
                // Handle date range cleared if needed
            }
        });

        // Apply custom date range
        function applyCustomDateRange(dateRange) {
            if (isComparisonMode) {
                // For comparison mode, trigger comparison search
                performSearchWithComparison();
            } else {
                // For initial mode, update time range display and trigger initial search
                const persianMonths = [
                    'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
                    'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'
                ];

                const startStr = `${dateRange.startDate.day} ${persianMonths[dateRange.startDate.month - 1]} ${dateRange.startDate.year}`;
                const endStr = `${dateRange.endDate.day} ${persianMonths[dateRange.endDate.month - 1]} ${dateRange.endDate.year}`;

                document.getElementById('time-range-display').innerHTML = `${startStr}&nbsp;&nbsp;تا&nbsp;&nbsp;${endStr}`;

                // Create a hidden radio button for calendar selection if it doesn't exist
                let calendarRadio = document.querySelector('input[name="time_range"][value="calendar"]');
                if (!calendarRadio) {
                    calendarRadio = document.createElement('input');
                    calendarRadio.type = 'radio';
                    calendarRadio.name = 'time_range';
                    calendarRadio.value = 'calendar';
                    calendarRadio.id = 'time_range_calendar';
                    calendarRadio.style.display = 'none';
                    document.body.appendChild(calendarRadio);
                }

                // Uncheck all other time range options and remove active class
                document.querySelectorAll('input[name="time_range"]').forEach(radio => {
                    radio.checked = false;
                    // Remove active class from the corresponding option
                    const option = radio.closest('.time-range-option');
                    if (option) {
                        option.classList.remove('active');
                    }
                });

                // Check the calendar option
                calendarRadio.checked = true;

                // Check if game is selected before triggering AJAX
                const selectedGameId = document.getElementById('selected-game-id').value;
                if (selectedGameId && selectedGameId.trim() !== '') {
                    // Trigger AJAX with custom date range
                    performSearch();
                }
            }
        }

        // Game search functionality
        function performGameSearch(query) {
            const formData = new FormData();
            formData.append('action', 'team_ajax_handler');
            formData.append('callback', 'game_search');
            formData.append('nonce', '<?php echo wp_create_nonce('team-ajax-nonce'); ?>');
            formData.append('search_query', query);

            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displaySearchResults(data.data.games);
                    } else {
                        hideSearchResults();
                    }
                })
                .catch(error => {
                    console.error('Game search error:', error);
                    hideSearchResults();
                });
        }

        // Display search results
        function displaySearchResults(games) {
            const resultsContainer = document.getElementById('search-results-sales');

            if (games.length === 0) {
                hideSearchResults();
                return;
            }

            resultsContainer.innerHTML = '';

            games.forEach(game => {
                const resultItem = document.createElement('div');
                resultItem.className = 'search-result-item';
                resultItem.innerHTML = `
                    <div class="flex items-center">
                        ${game.image ? `<img src="${game.image}" alt="${game.name}" class="search-result-image">` : ''}
                        <div class="search-result-name">${game.name}</div>
                    </div>
                `;

                resultItem.addEventListener('click', () => selectGame(game));
                resultsContainer.appendChild(resultItem);
            });

            resultsContainer.classList.remove('hidden');
        }

        // Hide search results
        function hideSearchResults() {
            const resultsContainer = document.getElementById('search-results-sales');
            resultsContainer.classList.add('hidden');
        }

        // Select a game from search results
        function selectGame(game) {
            // Set hidden input value
            document.getElementById('selected-game-id').value = game.id;

            // Show selected game tag
            const selectedGameTag = document.getElementById('selected-game-tag');
            const searchInputContainer = document.getElementById('search-input-container');
            const selectedGameNameDisplay = document.getElementById('selected-game-name-display');
            const selectedGameImage = document.getElementById('selected-game-image');

            // Update selected game tag content
            selectedGameNameDisplay.textContent = game.name;
            if (game.image) {
                selectedGameImage.src = game.image;
                selectedGameImage.style.display = 'block';
            } else {
                selectedGameImage.style.display = 'none';
            }

            // Show selected game tag and hide search input
            selectedGameTag.classList.remove('hidden');
            searchInputContainer.classList.add('hidden');

            hideSearchResults();

            // Trigger search
            performSearch();
        }

        // Clear selected game
        function clearSelectedGame() {
            // Clear hidden input
            document.getElementById('selected-game-id').value = '';

            // Hide selected game tag and show search input
            const selectedGameTag = document.getElementById('selected-game-tag');
            const searchInputContainer = document.getElementById('search-input-container');
            const searchInput = document.getElementById('search-input');

            selectedGameTag.classList.add('hidden');
            searchInputContainer.classList.remove('hidden');
            searchInput.value = '';

            // Reset all time range selections
            resetTimeRangeSelections();

            // Reset comparison data
            isComparisonMode = false;
            initialResults = null;
            comparisonResults = null;
            previousResults = null;
            previousDateRange = null;

            // Reset calendar selections
            calendar.reset();

            // Show initial loading text in results section
            const resultsSection = document.getElementById('results-section');
            if (resultsSection) {
                resultsSection.innerHTML = `
                    <div class="mt-30 text-center">
                        <div class="text-gray-500 space-y-2">
                            <p>برای مشاهده نتایج،</p>
                            <p>در قسمت جستجوی بالا نام بازی مورد نظر را وارد</p>
                            <p>نمایید، سپس بازه زمانی را مشخص کنید</p>
                        </div>
                    </div>
                `;
            }
        }

        // Reset time range selections
        function resetTimeRangeSelections() {
            // Uncheck all time range radio buttons
            document.querySelectorAll('input[name="time_range"]').forEach(radio => {
                radio.checked = false;
            });

            // Remove active class from all time range options
            document.querySelectorAll('.time-range-option').forEach(option => {
                option.classList.remove('active');
            });

            // Set default to "one_week" and make it active
            const defaultRadio = document.querySelector('input[name="time_range"][value="one_week"]');
            if (defaultRadio) {
                defaultRadio.checked = true;
                defaultRadio.closest('.time-range-option').classList.add('active');
            }

            // Reset time range display to default
            updateTimeRangeDisplay();
        }

        // Helper functions for comparison
        function calculatePercentageChange(oldValue, newValue) {
            if (oldValue === 0) {
                return {
                    percentage: newValue > 0 ? 100 : 0,
                    direction: newValue > 0 ? 'up' : 'same'
                };
            }

            const percentage = Math.round(((newValue - oldValue) / oldValue) * 100);
            return {
                percentage: percentage,
                direction: percentage > 0 ? 'up' : percentage < 0 ? 'down' : 'same'
            };
        }

        function getCurrentDateRangeDisplay() {
            return document.getElementById('time-range-display').innerHTML;
        }

        function getInitialDateRangeDisplay() {
            return document.getElementById('time-range-display').innerHTML;
        }

        function getComparisonDateRangeDisplay() {
            const dateRange = calendar.getSelectedDateRange();
            if (dateRange.startDate && dateRange.endDate) {
                const persianMonths = [
                    'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
                    'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'
                ];

                const startStr = `${dateRange.startDate.day} ${persianMonths[dateRange.startDate.month - 1]} ${dateRange.startDate.year}`;
                const endStr = `${dateRange.endDate.day} ${persianMonths[dateRange.endDate.month - 1]} ${dateRange.endDate.year}`;

                return `${startStr} تا ${endStr}`;
            }
            return 'تاریخ انتخاب نشده';
        }

        function enableComparisonMode() {
            // Set comparison mode and show calendar modal
            isComparisonMode = true;
            calendar.openCalendarModal();
        }

        // Perform search with comparison mode
        function performSearchWithComparison() {
            const selectedGameId = document.getElementById('selected-game-id').value;

            // Only perform AJAX if a game is selected
            if (!selectedGameId || selectedGameId.trim() === '') {
                return;
            }

            showLoading();

            const formData = new FormData();
            formData.append('action', 'team_ajax_handler');
            formData.append('callback', 'sales_report_search');
            formData.append('nonce', '<?php echo wp_create_nonce('team-ajax-nonce'); ?>');
            formData.append('game_id', selectedGameId);

            // For comparison, always use calendar dates
            const dateRange = calendar.getSelectedDateRange();
            if (dateRange.startDate && dateRange.endDate) {
                const startDateStr = dateRange.startGregorian.getFullYear() + '-' +
                    String(dateRange.startGregorian.getMonth() + 1).padStart(2, '0') + '-' +
                    String(dateRange.startGregorian.getDate()).padStart(2, '0');
                const endDateStr = dateRange.endGregorian.getFullYear() + '-' +
                    String(dateRange.endGregorian.getMonth() + 1).padStart(2, '0') + '-' +
                    String(dateRange.endGregorian.getDate()).padStart(2, '0');

                formData.append('time_range', 'calendar');
                formData.append('start_date', startDateStr);
                formData.append('end_date', endDateStr);
            } else {
                hideLoading();
                return;
            }

            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    if (data.success) {
                        comparisonResults = data.data; // Store comparison results
                        showComparisonResults(initialResults, comparisonResults);
                    } else {
                        showError(data.data || 'خطا در دریافت اطلاعات');
                    }
                })
                .catch(error => {
                    hideLoading();
                    console.error('Error:', error);
                    showError('خطا در ارتباط با سرور: ' + error.message);
                });
        }

        // Show comparison results
        function showComparisonResults(initial, comparison) {
            const resultsSection = document.getElementById('results-section');

            if (!initial || !comparison) {
                showError('خطا در نمایش نتایج مقایسه');
                return;
            }

            const initialSummary = initial.summary;
            const comparisonSummary = comparison.summary;

            // Calculate percentage changes (based on initial as reference)
            const commentsChange = calculatePercentageChange(comparisonSummary.total_comments, initialSummary.total_comments);
            const viewsChange = calculatePercentageChange(comparisonSummary.total_views, initialSummary.total_views);
            const ticketsChange = calculatePercentageChange(comparisonSummary.total_tickets, initialSummary.total_tickets);

            const comparisonHTML = `
                <div class="comparison-container mb-6">
                    <!-- Header with date ranges -->
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex gap-8">
                            <!-- Initial Range -->
                            <div class="text-center">
                                <p class="text-sm font-medium text-gray-600 mb-2">بازه اولیه:</p>
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span id="initial-range-display">${getInitialDateRangeDisplay()}</span>
                                </div>
                            </div>
                            
                            <!-- Comparison Range -->
                            <div class="text-center">
                                <p class="text-sm font-medium text-gray-600 mb-2">بازه مقایسه:</p>
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span id="comparison-range-display">${getComparisonDateRangeDisplay()}</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- New Comparison Button -->
                        <button id="new-comparison-btn" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                            + مقایسه جدید
                        </button>
                    </div>
                    
                    <!-- Comparison Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- کامنت -->
                        <div class="comparison-card">
                            <div class="card-header">
                                <p class="text-sm font-medium text-gray-600 mb-2">کامنت</p>
                            </div>
                            <div class="card-content">
                                <div class="current-value">
                                    ${commentsChange.percentage > 0 ? 
                                        `<div class="change-indicator positive">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                            </svg>
                                            ${commentsChange.percentage}%
                                        </div>` : 
                                        `<div class="change-indicator negative">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                                            </svg>
                                            ${Math.abs(commentsChange.percentage)}%
                                        </div>`
                                    }
                                    <p class="text-2xl font-bold text-gray-900">${initialSummary.total_comments.toLocaleString()}</p>
                                </div>
                                <div class="previous-value">
                                    <p class="text-lg text-gray-600">${comparisonSummary.total_comments.toLocaleString()}</p>
                                </div>
                            </div>
                        </div>

                        <!-- بازدید -->
                        <div class="comparison-card">
                            <div class="card-header">
                                <p class="text-sm font-medium text-gray-600 mb-2">بازدید</p>
                            </div>
                            <div class="card-content">
                                <div class="current-value">
                                    ${viewsChange.percentage > 0 ? 
                                        `<div class="change-indicator positive">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                            </svg>
                                            ${viewsChange.percentage}%
                                        </div>` : 
                                        `<div class="change-indicator negative">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                                            </svg>
                                            ${Math.abs(viewsChange.percentage)}%
                                        </div>`
                                    }
                                    <p class="text-2xl font-bold text-gray-900">${initialSummary.total_views.toLocaleString()}</p>
                                </div>
                                <div class="previous-value">
                                    <p class="text-lg text-gray-600">${comparisonSummary.total_views.toLocaleString()}</p>
                                </div>
                            </div>
                        </div>

                        <!-- تیکت -->
                        <div class="comparison-card">
                            <div class="card-header">
                                <p class="text-sm font-medium text-gray-600 mb-2">تیکت</p>
                            </div>
                            <div class="card-content">
                                <div class="current-value">
                                    ${ticketsChange.percentage > 0 ? 
                                        `<div class="change-indicator positive">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                            </svg>
                                            ${ticketsChange.percentage}%
                                        </div>` : 
                                        `<div class="change-indicator negative">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                                            </svg>
                                            ${Math.abs(ticketsChange.percentage)}%
                                        </div>`
                                    }
                                    <p class="text-2xl font-bold text-gray-900">${initialSummary.total_tickets.toLocaleString()}</p>
                                </div>
                                <div class="previous-value">
                                    <p class="text-lg text-gray-600">${comparisonSummary.total_tickets.toLocaleString()}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            resultsSection.innerHTML = comparisonHTML;

            // Add event listener for new comparison button
            const newComparisonBtn = document.getElementById('new-comparison-btn');
            if (newComparisonBtn) {
                newComparisonBtn.addEventListener('click', enableComparisonMode);
            }
        }

        // Clear selected game button event listener
        document.addEventListener('click', function(e) {
            if (e.target.closest('#clear-selected-game')) {
                clearSelectedGame();
            }
        });

        // Hide search results when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.search-container')) {
                hideSearchResults();
            }
        });

        // Initialize time range display
        updateTimeRangeDisplay();
    });
</script>

<!-- Include Calendar Module -->
<script src="<?php echo get_template_directory_uri(); ?>/assets/js/calendar-module.js"></script>
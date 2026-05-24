<div id="loading" class="loading" style="display: none;">ذخیره کردن...</div>

<div class="flex justify-between items-center gap-4">
    <h1 class="text-base font-extrabold lg:text-2xl">سفارشات</h1>
    <!-- Search Bar -->
    <div id="orders_search_field" class="relative grow h-[48px]">
        <input
            class="w-full h-[48px] border border-[#E4EBF0] bg-[#FAFDFF] rounded-xl outline-none px-6 py-5 text-xs font-yekan-bold text-navyBlue"
            placeholder="کدین به نام شماره تماس بازی و با شماره تراکنش" />
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none" class="absolute top-4 left-6" style="cursor: pointer;">
            <path d="M15.2149 14.2756L17.8133 16.8727C17.9344 16.9981 18.0015 17.1661 18 17.3406C17.9985 17.515 17.9285 17.6818 17.8052 17.8052C17.6818 17.9285 17.515 17.9985 17.3406 18C17.1661 18.0015 16.9981 17.9344 16.8727 17.8133L14.2743 15.2149C12.5764 16.6697 10.381 17.4102 8.14876 17.2812C5.91656 17.1522 3.82111 16.1636 2.30209 14.5229C0.78307 12.8822 -0.0414283 10.7169 0.00160352 8.48138C0.0446353 6.24587 0.951852 4.11392 2.53289 2.53289C4.11392 0.951852 6.24587 0.0446353 8.48138 0.00160352C10.7169 -0.0414283 12.8822 0.78307 14.5229 2.30209C16.1636 3.82111 17.1522 5.91656 17.2812 8.14876C17.4102 10.381 16.6697 12.5764 15.2149 14.2743V14.2756ZM8.64792 15.9653C10.5886 15.9653 12.4498 15.1944 13.8221 13.8221C15.1944 12.4498 15.9653 10.5886 15.9653 8.64792C15.9653 6.70723 15.1944 4.84602 13.8221 3.47375C12.4498 2.10148 10.5886 1.33054 8.64792 1.33054C6.70723 1.33054 4.84602 2.10148 3.47375 3.47375C2.10148 4.84602 1.33054 6.70723 1.33054 8.64792C1.33054 10.5886 2.10148 12.4498 3.47375 13.8221C4.84602 15.1944 6.70723 15.9653 8.64792 15.9653Z" fill="#09192D" />
        </svg>
    </div>
    <!-- Filter Buttons Container -->
    <div class="flex items-center gap-4">
        <!-- Order Status Dropdown -->
        <div class="relative">
            <button id="orderStatusDropdown" class="flex items-center gap-2 px-4 py-3 bg-white border border-[#E4EBF0] rounded-xl text-sm font-yekan-bold text-navyBlue hover:bg-gray-50 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6,9 12,15 18,9"></polyline>
                </svg>
                وضعیت سفارش
            </button>
            <div id="orderStatusMenu" class="dropdown-menu hidden absolute top-full left-0 mt-2 w-48 bg-white border border-[#E4EBF0] rounded-xl shadow-lg z-10">
                <div class="py-2">
                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-value="all">همه سفارشات</a>
                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-value="pending">در انتظار</a>
                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-value="confirmed">تایید شده</a>
                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-value="cancelled">لغو شده</a>
                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-value="completed">تکمیل شده</a>
                </div>
            </div>
        </div>

        <!-- Problematic Sessions Dropdown -->
        <div class="relative">
            <button id="problematicSessionsDropdown" class="flex items-center gap-2 px-4 py-3 bg-white border border-[#E4EBF0] rounded-xl text-sm font-yekan-bold text-navyBlue hover:bg-gray-50 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6,9 12,15 18,9"></polyline>
                </svg>
                سانس های مشکل دار
            </button>
            <div id="problematicSessionsMenu" class="dropdown-menu hidden absolute top-full left-0 mt-2 w-48 bg-white border border-[#E4EBF0] rounded-xl shadow-lg z-10">
                <div class="py-2">
                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-value="all">همه سانس ها</a>
                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-value="problematic">مشکل دار</a>
                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-value="normal">عادی</a>
                </div>
            </div>
        </div>

        <!-- Number of People Dropdown -->
        <div class="relative">
            <button id="numberOfPeopleDropdown" class="flex items-center gap-2 px-4 py-3 bg-white border border-[#E4EBF0] rounded-xl text-sm font-yekan-bold text-navyBlue hover:bg-gray-50 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6,9 12,15 18,9"></polyline>
                </svg>
                تعداد نفرات
            </button>
            <div id="numberOfPeopleMenu" class="dropdown-menu hidden absolute top-full left-0 mt-2 w-48 bg-white border border-[#E4EBF0] rounded-xl shadow-lg z-10">
                <div class="py-2">
                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-value="all">همه</a>
                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-value="1-2">1-2 نفر</a>
                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-value="3-4">3-4 نفر</a>
                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-value="5-6">5-6 نفر</a>
                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-value="7+">7 نفر به بالا</a>
                </div>
            </div>
        </div>
        <!-- Refresh Button -->
        <button id="refreshButton" class="bg-slate-100 hover:bg-slate-120 rounded-lg flex items-center justify-center gap-x-1 h-12.5 w-28 px-[20px] text-[#1447E6]">
            <span id="refreshBtnText">بروزرسانی</span>
            <svg id="refreshBtnIcon" class="mx-0" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                <path d="M2.75935 9.75L2.3641 10.149C2.46945 10.2534 2.61177 10.312 2.7601 10.312C2.90843 10.312 3.05076 10.2534 3.15611 10.149L2.75935 9.75ZM4.41611 8.8995C4.52213 8.79428 4.582 8.65125 4.58257 8.50188C4.58313 8.3525 4.52433 8.20902 4.41911 8.103C4.367 8.05051 4.30507 8.00879 4.23685 7.98023C4.16862 7.95167 4.09544 7.93682 4.02148 7.93654C3.8721 7.93598 3.72863 7.99478 3.6226 8.1L4.41611 8.8995ZM1.8961 8.1C1.7896 7.9979 1.64724 7.94173 1.49971 7.9436C1.35218 7.94547 1.21129 8.00523 1.1074 8.11C1.00352 8.21477 0.944958 8.35616 0.944341 8.5037C0.943724 8.65124 1.0011 8.79312 1.1041 8.89875L1.8961 8.1ZM13.9621 5.54401C13.9998 5.60893 14.0501 5.66565 14.1101 5.71082C14.1701 5.756 14.2385 5.7887 14.3113 5.80702C14.3841 5.82534 14.4598 5.8289 14.5341 5.81748C14.6083 5.80606 14.6794 5.77991 14.7434 5.74055C14.8073 5.7012 14.8627 5.64944 14.9064 5.58833C14.95 5.52722 14.9809 5.458 14.9974 5.38474C15.0138 5.31149 15.0154 5.23568 15.0021 5.16179C14.9888 5.08789 14.9608 5.01742 14.9199 4.95451L13.9621 5.54401ZM9.05936 1.68751C5.27411 1.68751 2.19685 4.73326 2.19685 8.49975H3.32185C3.32185 5.36326 5.88686 2.81251 9.05936 2.81251V1.68751ZM2.19685 8.49975V9.75H3.32185V8.49975H2.19685ZM3.15685 10.1498L4.41611 8.8995L3.6226 8.1L2.3626 9.35025L3.15685 10.1498ZM3.15685 9.351L1.8961 8.1L1.10335 8.89875L2.36335 10.1483L3.15685 9.351ZM14.9199 4.95601C14.304 3.95568 13.442 3.12996 12.4161 2.55773C11.3903 1.98551 10.234 1.68589 9.05936 1.68751V2.81251C10.042 2.81061 11.0095 3.0608 11.8679 3.53916C12.7262 4.01752 13.4475 4.70805 13.9629 5.54475L14.9199 4.95601Z" fill="#1447E6" />
                <path opacity="0.5" d="M15.2351 8.25L15.6311 7.85025C15.5258 7.74605 15.3836 7.68761 15.2355 7.68761C15.0873 7.68761 14.9452 7.74605 14.8399 7.85025L15.2351 8.25ZM13.5754 9.09975C13.5228 9.1517 13.481 9.2135 13.4523 9.28161C13.4237 9.34971 13.4087 9.4228 13.4083 9.49669C13.4075 9.64592 13.4659 9.78938 13.5709 9.8955C13.6758 10.0016 13.8186 10.0617 13.9678 10.0626C14.117 10.0634 14.2605 10.0049 14.3666 9.9L13.5754 9.09975ZM16.1051 9.9C16.1573 9.95331 16.2197 9.99569 16.2885 10.0247C16.3572 10.0536 16.4311 10.0687 16.5057 10.0688C16.5804 10.069 16.6543 10.0542 16.7232 10.0255C16.7921 9.99683 16.8546 9.9547 16.907 9.9016C16.9595 9.8485 17.0009 9.7855 17.0287 9.71625C17.0566 9.64701 17.0704 9.57291 17.0693 9.49828C17.0682 9.42365 17.0523 9.34998 17.0225 9.28156C16.9927 9.21313 16.9495 9.15133 16.8956 9.09975L16.1051 9.9ZM3.98885 12.4545C3.91048 12.3275 3.78487 12.2368 3.63964 12.2024C3.49442 12.168 3.34148 12.1928 3.21448 12.2711C3.08747 12.3495 2.9968 12.4751 2.96241 12.6203C2.92802 12.7656 2.95273 12.9185 3.0311 13.0455L3.98885 12.4545ZM8.9126 16.3125C12.7091 16.3125 15.7976 13.269 15.7976 9.50025H14.6726C14.6726 12.6352 12.1001 15.1875 8.9126 15.1875V16.3125ZM15.7976 9.50025V8.25H14.6726V9.50025H15.7976ZM14.8399 7.85025L13.5754 9.09975L14.3666 9.9L15.6311 8.64975L14.8399 7.85025ZM14.8399 8.64975L16.1051 9.9L16.8956 9.09975L15.6311 7.85025L14.8399 8.64975ZM3.0311 13.0455C3.65108 14.0469 4.51705 14.8721 5.54656 15.4441C6.57607 16.0161 7.73486 16.315 8.9126 16.3125V15.1875C7.92687 15.19 6.95689 14.9402 6.09503 14.4618C5.23316 13.9834 4.50809 13.2924 3.98885 12.4545L3.0311 13.0455Z" fill="#1447E6" />
            </svg>
        </button>
    </div>
</div>
<div class="relative" id="data-list">
    <?php for ($i = 0; $i < 10; $i++) { ?>
        <div class="w-full h-12 rounded-xl mb-2 skeleton mt-7"></div>
    <?php } ?>
</div>

<script src="<?php echo get_template_directory_uri(); ?>/assets/vendor/sweetalert2/sweetalert2@11.js"></script>
<style>
    .loading {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: rgba(0, 0, 0, 0.7);
        color: #fff;
        padding: 20px;
        border-radius: 5px;
        z-index: 1000;
        display: none;
    }

    .quantity {
        direction: ltr;
        cursor: pointer;
    }

    p.editable:focus {
        /*outline: none;*/
    }

    .swal2-confirm-custom {
        background-color: #6B7280 !important;
        color: white !important;
        border: none !important;
        border-radius: 8px !important;
        font-weight: bold !important;
    }

    .swal2-cancel-custom {
        background-color: white !important;
        color: #374151 !important;
        border: 1px solid #D1D5DB !important;
        border-radius: 8px !important;
        font-weight: bold !important;
    }

    .swal2-popup {
        border-radius: 12px !important;
    }

    .swal2-title {
        font-size: 18px !important;
        font-weight: bold !important;
        color: #374151 !important;
    }

    /* Custom Radio Button Styles */
    .radio-custom {
        transition: all 0.2s ease;
    }

    input[type="radio"]:checked+.radio-custom {
        border-color: #3B82F6;
        background-color: #3B82F6;
    }

    input[type="radio"]:checked+.radio-custom .radio-check {
        display: block !important;
    }

    .radio-check {
        background-color: white !important;
        border-radius: 50%;
    }

    /* Dropdown Menu Styles */
    .dropdown-menu {
        transition: all 0.2s ease;
    }

    /* Spin animation for refresh button */
    .animate-spin {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }
</style>
<script>
    jQuery(document).ready(function($) {
        // Dropdown functionality for filter buttons
        let currentFilters = {
            orderStatus: 'all',
            problematicSessions: 'all',
            numberOfPeople: 'all'
        };

        // Toggle dropdown menus
        function toggleDropdown(dropdownId, menuId) {
            // Close all other dropdowns first
            $('.dropdown-menu').not('#' + menuId).addClass('hidden');

            // Toggle current dropdown
            $('#' + menuId).toggleClass('hidden');
        }

        // Handle dropdown button clicks
        $('#orderStatusDropdown').on('click', function(e) {
            e.stopPropagation();
            console.log('Order Status dropdown clicked');
            toggleDropdown('orderStatusDropdown', 'orderStatusMenu');
        });

        $('#problematicSessionsDropdown').on('click', function(e) {
            e.stopPropagation();
            console.log('Problematic Sessions dropdown clicked');
            toggleDropdown('problematicSessionsDropdown', 'problematicSessionsMenu');
        });

        $('#numberOfPeopleDropdown').on('click', function(e) {
            e.stopPropagation();
            console.log('Number of People dropdown clicked');
            toggleDropdown('numberOfPeopleDropdown', 'numberOfPeopleMenu');
        });

        // Handle dropdown menu item clicks
        $('#orderStatusMenu a').on('click', function(e) {
            e.preventDefault();
            const value = $(this).data('value');
            const text = $(this).text();

            console.log('Order Status filter changed:', {
                value: value,
                text: text
            });
            currentFilters.orderStatus = value;

            // Update button text
            $('#orderStatusDropdown').html(`
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6,9 12,15 18,9"></polyline>
                </svg>
                ${text}
            `);

            // Close dropdown
            $('#orderStatusMenu').addClass('hidden');

            // Trigger AJAX call with new filters
            applyFilters();
        });

        $('#problematicSessionsMenu a').on('click', function(e) {
            e.preventDefault();
            const value = $(this).data('value');
            const text = $(this).text();

            console.log('Problematic Sessions filter changed:', {
                value: value,
                text: text
            });
            currentFilters.problematicSessions = value;

            // Update button text
            $('#problematicSessionsDropdown').html(`
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6,9 12,15 18,9"></polyline>
                </svg>
                ${text}
            `);

            // Close dropdown
            $('#problematicSessionsMenu').addClass('hidden');

            // Trigger AJAX call with new filters
            applyFilters();
        });

        $('#numberOfPeopleMenu a').on('click', function(e) {
            e.preventDefault();
            const value = $(this).data('value');
            const text = $(this).text();

            console.log('Number of People filter changed:', {
                value: value,
                text: text
            });
            currentFilters.numberOfPeople = value;

            // Update button text
            $('#numberOfPeopleDropdown').html(`
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6,9 12,15 18,9"></polyline>
                </svg>
                ${text}
            `);

            // Close dropdown
            $('#numberOfPeopleMenu').addClass('hidden');

            // Trigger AJAX call with new filters
            applyFilters();
        });

        // Enhanced refresh functionality with loading states and messages
        function fetch_orders_data(showMessages = false) {
            // Show loading state
            $('#refreshButton').prop('disabled', true);
            $('#refreshBtnText').hide();
            $('#refreshBtnIcon').addClass('animate-spin');

            $.ajax({
                type: 'POST',
                url: "<?php echo admin_url('admin-ajax.php') ?>",
                data: {
                    'action': 'team_ajax_handler',
                    'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                    'callback': 'orders_get',
                    'state': 'all',
                    'page': 1,
                    'filters': currentFilters
                },
                beforeSend: function() {
                    $("#data-list").html(function() {
                        let out = '';
                        for (let i = 0; i < 10; i++) {
                            out += '<div class="w-full h-12 rounded-xl mb-2 skeleton mt-7"></div>';
                        }
                        return out;
                    })
                },
                success: function(data) {
                    $("#data-list").html(data);
                    // Show success message only if showMessages is true
                    if (showMessages) {
                        const successMsg = $('<div class="fixed bottom-4 left-4 bg-green-500 text-white px-4 py-2 rounded-lg z-50">بروزرسانی با موفقیت انجام شد</div>');
                        $('body').append(successMsg);
                        setTimeout(() => successMsg.fadeOut(500, () => successMsg.remove()), 2000);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching orders data:', error);
                    $("#data-list").html('<div class="text-center text-red-500 p-4">خطا در بارگذاری داده‌ها. لطفاً دوباره تلاش کنید.</div>');
                    // Show error message only if showMessages is true
                    if (showMessages) {
                        const errorMsg = $('<div class="fixed bottom-4 left-4 bg-red-500 text-white px-4 py-2 rounded-lg z-50">خطا در بارگذاری داده‌ها</div>');
                        $('body').append(errorMsg);
                        setTimeout(() => errorMsg.fadeOut(500, () => errorMsg.remove()), 3000);
                    }
                },
                complete: function() {
                    // Reset button state
                    $('#refreshButton').prop('disabled', false);
                    $('#refreshBtnText').show();
                    $('#refreshBtnIcon').removeClass('animate-spin');
                }
            });
        }

        // Handle refresh button click
        $('#refreshButton').on('click', function(e) {
            e.preventDefault();
            console.log('Refresh button clicked');
            console.log('Current filters:', currentFilters);
            fetch_orders_data(true);
        });

        // Function to apply all current filters
        function applyFilters() {
            console.log('Applying filters:', currentFilters);
            fetch_orders_data(false);
        }

        // Auto-refresh every 10 minutes
        // setInterval(fetch_orders_data, 10 * 60 * 1000);

        // Close dropdowns when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.relative').length) {
                $('.dropdown-menu').addClass('hidden');
            }
        });

        const get_data = (state, page) => {
            $.ajax({
                type: 'POST',
                url: "<?php echo admin_url('admin-ajax.php') ?>",
                data: {
                    'action': 'team_ajax_handler',
                    'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                    'callback': 'orders_get',
                    'state': state,
                    'page': page
                },
                beforeSend: function() {
                    $("#data-list").html(function() {
                        let out = '';
                        for (let i = 0; i < 10; i++) {
                            out += '<div class="w-full h-12 rounded-xl mb-2 skeleton mt-7"></div>';
                        }
                        return out;
                    })
                },
                success: function(data) {
                    $("#data-list").html(data);
                },
            });
        }

        fetch_orders_data(false);

        setTimeout(function() {
            if ((new URLSearchParams(window.location.search)).get('page'))
                get_data($("#filterTabs div.active").data('state'), (new URLSearchParams(window.location.search)).get('page'));
        }, 100);
        
        

        $("body").on('click', "#filterTabs div", function(e) {
            get_data($(this).data('state'), 1);
        });

        function search_in_orders() {
            $.ajax({
                type: 'POST',
                url: "<?php echo esc_url( get_template_directory_uri() ); ?>/template/team/ajax/callbacks/orders_search.php",
                data: {
                    'term': $("#orders_search_field input").val(),
                },
                dataType: "json",
                success: function(data) {

                    $.ajax({
                        type: 'POST',
                        url: "<?php echo admin_url('admin-ajax.php') ?>",
                        data: {
                            'action': 'team_ajax_handler',
                            'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                            'callback': 'orders_get',
                            'order_ids[]': data.length ? data : '',
                            'page': 1
                        },
                        beforeSend: function() {
                            $("#data-list").html(function() {
                                let out = '';
                                for (let i = 0; i < 10; i++) {
                                    out += '<div class="w-full h-12 rounded-xl mb-2 skeleton mt-7"></div>';
                                }
                                return out;
                            })
                        },
                        success: function(data) {
                            $("#data-list").html(data);
                        },
                    });

                },
            });
        }

        $("body").on("click", "#orders_search_field svg", function (e) {
            search_in_orders();
        });

        $("body").on("keypress", "#orders_search_field input", function (e) {
            if (e.which === 13) { // کلید Enter
                search_in_orders();
            }
        });

        $("body").on('click', ".pagination a", function(e) {
            e.preventDefault();

            const page = $(this).attr('href').split('?page=')[1];
            const active = $("#filterTabs div.active").data('state');

            get_data(active, page);
        });

        $('body').on('click', 'p.quantity', function() {
            const $this = $(this);
            const $old_quantity = $this.text();

            $this.attr('contenteditable', 'true').addClass('editable').focus();

            // پاک کردن قبلی‌ها و namespace
            $this.off('blur.quantityEdit keypress.quantityEdit keydown.quantityEdit');

            $this.on('blur.quantityEdit', function() {
                const $new_quantity = $this.text();

                $this.attr('contenteditable', 'false').removeClass('editable');

                if ($old_quantity == $new_quantity) {
                    return;
                }

                // ذخیره هدف محلی
                const $target = $this;

                $target.removeClass('quantity').addClass('quantity_dis');
                $('#loading').show();

                $.ajax({
                    type: 'POST',
                    url: "<?php echo admin_url('admin-ajax.php') ?>",
                    data: {
                        'action': 'team_ajax_handler',
                        'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                        'callback': 'orders_actions',
                        'operation': 'quantity_change',
                        'order_id': $("#orders_table_row").data('id'),
                        'new_quantity': $new_quantity,
                    },
                    dataType: "json",
                    success: function(data) {
                        console.log('AJAX success', data);
                        // فقط روی هدف محلی تغییر کلاس بده
                        $target.removeClass('quantity_dis').addClass('quantity');
                    },
                    error: function(xhr, status, err) {
                        console.error('AJAX error', status, err, xhr && xhr.responseText);
                        // در صورت خطا مقدار قبلی را بازگردان
                        $target.text($old_quantity);
                    },
                    complete: function() {
                        $('#loading').hide();
                        console.log('AJAX complete');
                    }
                });
            });

            $this.on('keypress.quantityEdit', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $this.blur();
                }
            });

            $this.on('keydown.quantityEdit', function(e) {
                if (e.which === 27) {
                    $this.text($old_quantity);
                    $this.blur();
                }
            });
        });
        $('body').on("click", ".order_status_change", function() {
            if (confirm('مطمئن هستید؟')) {
                let order_id = $(this).closest('#maliModal').data('id');
                if (!order_id) {
                    order_id = $(this).closest('#crmModal').data('id');
                }

                $.ajax({
                    type: 'POST',
                    url: "<?php echo admin_url('admin-ajax.php') ?>",
                    data: {
                        'action': 'team_ajax_handler',
                        'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                        'callback': 'orders_actions',
                        'operation': 'status_change',
                        'order_id': order_id,
                        'status': $(this).data('action'),

                    },
                    dataType: "json",
                    success: function(data) {
                        location.reload();
                    },
                });

            } else return false;
        });

        $('body').on('change', '.happycall', function(e) {
            // Check if this is a programmatic change (not user interaction)
            if (e.isTrigger || $(this).data('programmatic')) {
                return;
            }

            let happy_call_status = $(this).attr('data-happy-call') === '1';

            $('#loading').show();

            // Update visual state manually to avoid DOM errors
            const checkbox = this;
            const span = checkbox.nextElementSibling;

            if (checkbox.checked) {
                checkbox.style.background = '#5091FB';
                if (span) span.style.display = 'flex';
            } else {
                checkbox.style.background = '#fff';
                if (span) span.style.display = 'none';
            }

            $.ajax({
                type: 'POST',
                url: "<?php echo admin_url('admin-ajax.php') ?>",
                data: {
                    'action': 'team_ajax_handler',
                    'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                    'callback': 'orders_actions',
                    'operation': 'happy_call',
                    'order_id': $(this).closest("#orders_table_row").data('id') ,
                    'state': $(this).is(':checked') ? 1 : 0,
                },
                dataType: "json",
                success: function(data) {
                    $('#loading').hide();
                },
            });
        });

        $('body').on('click', '.cancellation_request', function() {
            let user_type = $(this).data('type');
            let order_id = $(this).closest('#crmModal').data('id');
            if (!order_id) {
                order_id = $(this).closest('#maliModal').data('id');
            }

            if (user_type === 'customer') {
                // Reset owner button color when customer cancellation is clicked
                $('.cancellation_request[data-type="owner"]').removeClass('bg-gray-800').addClass('bg-gray-500');
                // Close owner cancellation box if open
                $('#ownerCancellationBox').slideUp(300, function() {
                    $(this).addClass('hidden');
                });

                // Custom SweetAlert2 confirmation for customer cancellation
                Swal.fire({
                    title: 'آیا از لغو رزرو برای این پلیر مطمئن هستید؟',
                    icon: 'error',
                    iconColor: '#F21543',
                    showCancelButton: true,
                    confirmButtonText: 'بله',
                    cancelButtonText: 'خیر',
                    confirmButtonColor: '#6B7280',
                    cancelButtonColor: '#fff',
                    reverseButtons: true,
                    customClass: {
                        confirmButton: 'swal2-confirm-custom',
                        cancelButton: 'swal2-cancel-custom'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        var data_obj = {
                            action: 'team_ajax_handler',
                            nonce: "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                            callback: 'cancellation_actions',
                            function: 'create_cancellation_request',
                            order_id: order_id,
                            requester_type: user_type
                        };

                        $.ajax({
                            type: 'POST',
                            url: "<?php echo admin_url('admin-ajax.php') ?>",
                            data: data_obj,
                            success: function(data) {
                                console.log(data);
                                if (data.success) {
                                    Swal.fire({
                                        title: 'موفق',
                                        text: 'درخواست کنسلی با موفقیت ثبت شد',
                                        icon: 'success'
                                    });
                                }
                            },
                        });
                    }
                });
            } else if (user_type === 'owner') {
                // Show reason box for owner cancellation (sliding from bottom)
                $('#ownerCancellationBox').slideDown(300);
                // Reset form
                $('input[name="cancellationReason"]').prop('checked', false);
                // Change button color to dark
                $('.cancellation_request[data-type="owner"]').removeClass('bg-gray-500').addClass('bg-gray-800');
                // Close teammates dropdown if open
                $('#teammatesDropdown').slideUp(300, function() {
                    $(this).addClass('hidden');
                });
                $('#teammatesArrow').css('transform', 'rotate(0deg)');
            }
        });

        // Handle owner cancellation reason box
        $('body').on('click', '#submitCancellation', function() {
            let order_id = $('#crmModal').attr('data-id');
            if (!order_id) {
                order_id = $('#maliModal').attr('data-id');
            }

            // Get selected reason
            let selectedReason = $('input[name="cancellationReason"]:checked').val();

            // Validate form
            if (!selectedReason) {
                Swal.fire({
                    title: 'خطا',
                    text: 'لطفاً یک دلیل کنسلی انتخاب کنید',
                    icon: 'error'
                });
                return;
            }

            // Show confirmation modal
            Swal.fire({
                title: 'آیا از لغو رزرو برای این مجموعه مطمئن هستید؟',
                icon: 'error',
                iconColor: '#F21543',
                showCancelButton: true,
                confirmButtonText: 'بله',
                cancelButtonText: 'خیر',
                confirmButtonColor: '#6B7280',
                cancelButtonColor: '#fff',
                reverseButtons: true,
                customClass: {
                    confirmButton: 'swal2-confirm-custom',
                    cancelButton: 'swal2-cancel-custom'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    var data_obj = {
                        action: 'team_ajax_handler',
                        nonce: "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                        callback: 'cancellation_actions',
                        function: 'create_cancellation_request',
                        order_id: order_id,
                        requester_type: 'owner',
                        reason_id: selectedReason
                    };

                    $.ajax({
                        type: 'POST',
                        url: "<?php echo admin_url('admin-ajax.php') ?>",
                        data: data_obj,
                        success: function(data) {
                            console.log(data);
                            $('#ownerCancellationBox').slideUp(300);
                            if (data.success) {
                                Swal.fire({
                                    title: 'موفق',
                                    text: 'درخواست کنسلی با موفقیت ثبت شد',
                                    icon: 'success'
                                });
                            }
                        },
                    });
                }
            });
        });


        // Handle teammates dropdown toggle (using event delegation)
        $(document).on('click', '#teammatesToggle', function(e) {
            e.preventDefault();
            console.log('Teammates toggle clicked');
            const dropdown = $('#teammatesDropdown');
            const arrow = $('#teammatesArrow');

            if (dropdown.hasClass('hidden')) {
                console.log('Opening dropdown');
                dropdown.removeClass('hidden').hide().slideDown(300);
                arrow.css('transform', 'rotate(180deg)');
            } else {
                console.log('Closing dropdown');
                dropdown.slideUp(300, function() {
                    $(this).addClass('hidden');
                });
                arrow.css('transform', 'rotate(0deg)');
            }
        });
        // مدیریت باز و بسته شدن مدال‌های CRM و Mali
        $(document).on("click", ".openCrmModal", function() {
            let order_id = $(this).attr('data-id');
            let happy_call_status = $(this).attr('data-happy-call') === '1';

            $("#crmModal").attr('data-id', order_id).show();

            // Set happy call status from data attribute
            let checkbox = $('#crmModal .happycall');
            checkbox.prop('checked', happy_call_status);
            checkbox[0].style.background = happy_call_status ? '#5091FB' : '#fff';
            if (checkbox.next('span')[0]) {
                checkbox.next('span')[0].style.display = happy_call_status ? 'flex' : 'none';
            }
        });

        $(document).on("click", ".openMaliModal", function() {
            let order_id = $(this).attr('data-id');
            $("#maliModal").attr('data-id', order_id).show();
        });

        $(document).on("click", "#crmModal", function(e) {
            if ($(e.target).is("#crmModal")) {
                $(this).attr('data-id', '').hide();
                // Close both dropdowns when modal closes
                $('#teammatesDropdown').slideUp(300, function() {
                    $(this).addClass('hidden');
                });
                $('#ownerCancellationBox').slideUp(300, function() {
                    $(this).addClass('hidden');
                });
                // Reset button colors
                $('.cancellation_request[data-type="owner"]').removeClass('bg-gray-800').addClass('bg-gray-500');
                // Reset arrow rotation
                $('#teammatesArrow').css('transform', 'rotate(0deg)');
            }
        });

        $(document).on("click", "#maliModal", function(e) {
            if ($(e.target).is("#maliModal")) {
                $(this).attr('data-id', '').hide();
            }
        });

        $("input").on("input", function() {
            searchQuery = $(this).val();
            currentPage = 1;
        });

        $(".taborder").click(function() {
            $(".taborder").removeClass("active")
            $(this).addClass("active")
            filterType = $(this).text().includes("بد") ? "bad" : "all";
            get_data(filterType, 1);
        });

        $(document).on("click", ".pagination-numbers > div", function() {
            const page = $(this).data("page");
            if (page === "prev" && currentPage > 1) {
                currentPage--;
            } else if (page === "next" && currentPage < Math.ceil(filteredOrders.length / pageSize)) {
                currentPage++;
            } else if (!isNaN(parseInt(page))) {
                currentPage = parseInt(page);
            }
            renderTable();
            renderPagination();
        });

        (function() {
            var input = document.currentScript.previousElementSibling.previousElementSibling;
            var svg = document.currentScript.previousElementSibling;

            function updateCheck() {
                if (input.checked) {
                    input.style.background = '#5091FB';
                    svg.style.display = 'block';
                } else {
                    input.style.background = '#fff';
                    svg.style.display = 'none';
                }
            }
            input.addEventListener('change', updateCheck);
            updateCheck();
        })();
    })
</script>
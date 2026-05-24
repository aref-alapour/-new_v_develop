<?php

/**
 * Cancellation History Page
 * 
 * This page displays cancellation history for administrators and owners
 * Shows only history related to games owned by the current user
 */

$user_id = get_current_user_id();
$user = wp_get_current_user();

// Check if user has permission to view this page
if (!current_user_can('administrator') && !has_role('compiler')) {
    wp_die('شما دسترسی لازم برای مشاهده این صفحه را ندارید.');
}
?>

<div class="lg:col-span-8 2xl:col-span-9">
    <section class="border-[#E8EDF1] lg:h-full lg:rounded-3xl lg:border lg:p-8">
        <!-- Header Section -->
        <div class="flex justify-between items-center mb-6">
            <div class="flex gap-x-10 items-center">
                <h1 class="text-xl font-extrabold">تاریخچه لغو</h1>
                <!-- Desktop Filter Tabs -->
                <div class="hidden lg:flex gap-6" id="filterTabs">
                    <div class="tab active text-sm font-bold text-primaryColor py-3 border-b-2 border-primaryColor" data-status="all">همه</div>
                    <div class="tab text-sm font-bold text-gray-500 py-3 border-b-2 border-transparent hover:text-gray-700 hover:border-gray-300" data-status="approved">تایید و لغو سانس</div>
                    <div class="tab text-sm font-bold text-gray-500 py-3 border-b-2 border-transparent hover:text-gray-700 hover:border-gray-300" data-status="pending">در انتظار بررسی</div>
                    <div class="tab text-sm font-bold text-gray-500 py-3 border-b-2 border-transparent hover:text-gray-700 hover:border-gray-300" data-status="rejected">رد شده ها</div>
                    <div class="tab text-sm font-bold text-gray-500 py-3 border-b-2 border-transparent hover:text-gray-700 hover:border-gray-300" data-status="expired">موعد بررسی گذشت</div>
                </div>
            </div>
        <a href="<?= home_url('/panel/cancellation-requests/') ?>" class="flex items-center justify-center gap-2 max-lg:h-[45px] max-lg:w-[45px] lg:h-auto lg:w-auto text-gray-700 font-extrabold text-base cursor-pointer hover:text-gray-900 transition-colors lg:bg-transparent lg:shadow-none max-lg:bg-[#F9FAFB] max-lg:hover:bg-[#d8d8d8] rounded-xl max-lg:shadow-[0px_2px_0px_0px_#e2e8f0]">
                <span class="hidden lg:inline text-[#62748E]">بازگشت</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" class="text-orange-500 lg:hidden">
                    <path d="M12.5 15L7.5 10L12.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" class="text-orange-500 hidden lg:block">
                    <path d="M12.5 15L7.5 10L12.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </a>
        </div>

        <!-- Mobile Filter Buttons -->
        <div class="lg:hidden mb-4">
            <div class="grid grid-cols-5 bg-white rounded-lg overflow-hidden border">
                <div class="mobile-tab active text-center content-center text-sm font-bold text-white px-2 h-12.5 cursor-pointer transition-all duration-200" data-status="all">همه</div>
                <div class="mobile-tab text-center content-center text-sm font-bold px-2 h-12.5 cursor-pointer transition-all duration-200" data-status="approved">تایید شده</div>
                <div class="mobile-tab text-center content-center text-sm font-bold px-2 h-12.5 cursor-pointer transition-all duration-200" data-status="pending">در انتظار</div>
                <div class="mobile-tab text-center content-center text-sm font-bold px-2 h-12.5 cursor-pointer transition-all duration-200" data-status="rejected">رد شده</div>
                <div class="mobile-tab text-center content-center text-sm font-bold px-2 h-12.5 cursor-pointer transition-all duration-200" data-status="expired">منقضی</div>
            </div>
        </div>

        <div class="relative" id="data-list">
            <?php for ($i = 0; $i < 10; $i++) { ?>
                <div class="w-full h-12 rounded-xl mb-2 skeleton mt-2"></div>
            <?php } ?>
        </div>

        <style>
            .text-pinkk {
                color: #F21543;
            }

            .text-orangee {
                color: #FD7013;
            }

            .text-blueEscape {
                color: #2B7FFF;
            }

            .text-grayy {
                color: #62748E;
            }

            .text-navyBlue {
                color: #0F172B;
            }

            .bg-pinkk {
                background-color: #F21543;
            }

            .tab {
                transition: all 0.3s ease;
                cursor: pointer;
            }

            .tab:hover {
                transform: translateY(-1px);
            }

            /* Mobile button styles */
            .mobile-tab {
                transition: all 0.2s ease;
                color: #889BAD;
            }

            .mobile-tab.active {
                background-color: var(--primary-500, #FD7013);
                color: white;
            }

            .mobile-tab:hover {
                transform: translateY(-1px);
            }

            /* Desktop table styles */
            .desktop-table {
                display: block;
            }

            .mobile-cards {
                display: none;
            }

            @media (max-width: 1024px) {
                .desktop-table {
                    display: none;
                }

                .mobile-cards {
                    display: block;
                }

                .mobile-cards .request-card {
                    margin-top: 0;
                }

                .mobile-cards .request-card>div {
                    border-radius: 0;
                    border-top: 1px solid #E4EBF0;
                    padding: 20px;
                }
            }
        </style>

        <script>
            jQuery(document).ready(function($) {
                let currentStatus = 'all';
                let currentPage = 1;
                let searchQuery = '';

                const get_data = (status, page, search = '') => {
                    $.ajax({
                        type: 'POST',
                        url: "<?php echo admin_url('admin-ajax.php') ?>",
                        data: {
                            'action': 'v2_ajax_handler',
                            'nonce': "<?php echo wp_create_nonce('v2-ajax-nonce') ?>",
                            'callback': 'user_cancellation_history_get',
                            'status': status,
                            'page': page,
                            'search': search
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
                        error: function(xhr, status, error) {
                            console.error('Error:', error);
                            $("#data-list").html('<div class="text-center text-red-500 py-4">خطا در بارگذاری داده‌ها</div>');
                        }
                    });
                }

                // Initial load
                get_data(currentStatus, currentPage, searchQuery);

                // Auto refresh every 10 minutes
                setInterval(() => {
                    get_data(currentStatus, currentPage, searchQuery);
                }, 10 * 60 * 1000);

                // Handle desktop tab clicks
                $("body").on('click', "#filterTabs div", function(e) {
                    $('#filterTabs div').removeClass('active text-primaryColor border-primaryColor').addClass('text-gray-500 border-transparent');
                    $(this).removeClass('text-gray-500 border-transparent').addClass('active text-primaryColor border-primaryColor');

                    currentStatus = $(this).data('status');
                    currentPage = 1;
                    get_data(currentStatus, currentPage, searchQuery);
                });

                // Handle mobile button clicks
                $("body").on('click', ".mobile-tab", function(e) {
                    e.preventDefault();

                    // Update mobile button styling
                    $('.mobile-tab').removeClass('active');
                    $(this).addClass('active');

                    // Update desktop tabs to match
                    $('#filterTabs div').removeClass('active text-primaryColor border-primaryColor').addClass('text-gray-500 border-transparent');
                    $(`#filterTabs div[data-status="${$(this).data('status')}"]`).removeClass('text-gray-500 border-transparent').addClass('active text-primaryColor border-primaryColor');

                    currentStatus = $(this).data('status');
                    currentPage = 1;
                    get_data(currentStatus, currentPage, searchQuery);
                });

                // Handle show details functionality
                $(document).on('click', '.showDetailsBtn', function(e) {
                    e.preventDefault();
                    const $section = $(this).closest('.request-card');
                    $section.find('.description').slideDown(300);
                    $(this).slideUp(400);
                });

                // Handle hide details functionality
                $(document).on('click', '.hideDetailsBtn', function(e) {
                    e.preventDefault();
                    const $section = $(this).closest('.request-card');
                    $section.find('.description').slideUp(300, function() {
                        $section.find('.showDetailsBtn').slideDown(400);
                    });
                });

                // Handle pagination
                $("body").on('click', ".pagination a", function(e) {
                    e.preventDefault();
                    const page = $(this).attr('href').split('?page=')[1];
                    currentPage = parseInt(page);
                    get_data(currentStatus, currentPage, searchQuery);
                });

                // Handle search
                $('#searchInput').on('input', function() {
                    searchQuery = $(this).val();
                    currentPage = 1;
                    get_data(currentStatus, currentPage, searchQuery);
                });
            });
        </script>
</div>
</section>
</div>
</div>
<?php

/**
 * Cancellation Requests Page
 * 
 * This page displays cancellation requests for administrators and owners
 * Shows only requests related to games owned by the current user
 */

$user_id = get_current_user_id();
$user = wp_get_current_user();

// Check if user has permission to view this page
if (!current_user_can('administrator') && !has_role('compiler')) {
    wp_die('شما دسترسی لازم برای مشاهده این صفحه را ندارید.');
}
?>

<div class="lg:col-span-8 2xl:col-span-9">
    <section class="border-edge lg:h-full lg:rounded-3xl lg:border lg:p-8">
        <!-- Header Section -->
        <div class="flex justify-between items-center mb-6">
            <div class="flex gap-x-10 items-center">
                <h1 class="text-xl font-extrabold">درخواست ها</h1>
                <!-- Desktop Filter Tabs -->
                <div class="hidden lg:flex gap-6" id="filterTabs">
                    <div class="tab active text-sm font-bold text-primaryColor px-4 py-3 border-b-2 border-primaryColor" data-status="all">نمایش همه لغوها</div>
                    <div class="tab text-sm font-bold text-gray-500 px-4 py-3 border-b-2 border-transparent hover:text-gray-700 hover:border-gray-300" data-status="urgent">فوری</div>
                    <div class="tab text-sm font-bold text-gray-500 px-4 py-3 border-b-2 border-transparent hover:text-gray-700 hover:border-gray-300" data-status="expired">موعد بررسی گذشت</div>
                </div>
            </div>
            <a href="<?= home_url('/panel/cancellation-history/'); ?>" class="block text-center content-center h-10 w-28 px-d20 text-primaryColor font-extrabold text-base bg-breserve hover:bg-gray-200 rounded-xl cursor-pointer"
                style="box-shadow: 0px 2px 0px 0px #e2e8f0">تاریخچه لغو</a>
        </div>

        <!-- Mobile Filter Buttons -->
        <div class="lg:hidden mb-4">
            <div class="grid grid-cols-4 bg-white rounded-lg overflow-hidden border">
                <div class="mobile-tab active text-center content-center text-sm font-bold text-white px-4 h-12.5 cursor-pointer transition-all duration-200" data-status="all">همه</div>
                <div class="mobile-tab text-center content-center text-sm font-bold px-4 h-12.5 cursor-pointer transition-all duration-200" data-status="urgent">فوری</div>
                <div class="mobile-tab text-center content-center text-sm font-bold px-4 h-12.5 cursor-pointer transition-all duration-200 col-span-2" data-status="expired">موعد بررسی گذشته</div>
            </div>
        </div>
        <!-- Info Banner -->
        <div class="bg-yellow-100 rounded-lg py-1.5 px-2.5 w-fit mr-auto">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-sm text-yellow-800">از بخش تاریخچه لغو میتوانید تمام سوابق لغو و درخواست ها را مشاهده کنید.</span>
            </div>
        </div>
        <div id="cardsContainer" class="space-y-8 mt-4"></div>

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
        </style>

        <script>
            jQuery(document).ready(function($) {
                let currentStatus = 'all';
                let currentPage = 1;

                function fetch_cancellation_requests(status = 'all', page = 1) {
                    $.ajax({
                        type: 'POST',
                        url: "<?php echo admin_url('admin-ajax.php') ?>",
                        data: {
                            'action': 'v2_ajax_handler',
                            'nonce': "<?php echo wp_create_nonce('v2-ajax-nonce') ?>",
                            'callback': 'user_cancellation_requests_get',
                            'status': status,
                            'page': page
                        },
                        beforeSend: function() {
                            $("#data-list").html(function() {
                                let out = '';
                                for (let i = 0; i < 10; i++) {
                                    out += '<div class="w-full h-12 rounded-xl mb-2 skeleton"></div>';
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
                fetch_cancellation_requests(currentStatus, currentPage);

                // Auto refresh every 10 minutes
                setInterval(() => {
                    fetch_cancellation_requests(currentStatus, currentPage);
                }, 10 * 60 * 1000);

                // Handle desktop tab clicks
                $("body").on('click', "#filterTabs div", function(e) {
                    $('#filterTabs div').removeClass('active text-primaryColor border-primaryColor').addClass('text-gray-500 border-transparent');
                    $(this).removeClass('text-gray-500 border-transparent').addClass('active text-primaryColor border-primaryColor');

                    currentStatus = $(this).data('status');
                    currentPage = 1;
                    fetch_cancellation_requests(currentStatus, currentPage);
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
                    fetch_cancellation_requests(currentStatus, currentPage);
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
                    fetch_cancellation_requests(currentStatus, currentPage);
                });

                // Handle modal opening when buttons are clicked
                $(document).on('click', '.openModalBtn', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    var requestId = $(this).data('request-id');
                    console.log('Opening modal for request ID:', requestId);

                    // Set the request_id in the modal
                    $('#modalRequestId').val(requestId);

                    // Show the modal
                    $('#myModal').removeClass('hidden').show();
                });

                // Handle modal closing
                $(document).on('click', '.closeModalBtn', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Closing modal');
                    $('#myModal').addClass('hidden').hide();
                });

                // Handle approve button
                $("body").on('click', ".approve-btn", function(e) {
                    e.preventDefault();
                    const requestId = $(this).data('request-id');

                    Swal.fire({
                        title: 'آیا مطمئن هستید؟',
                        text: 'آیا با لغو این سانس موافقت می‌کنید؟',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#28A745',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'بله، تایید کن',
                        cancelButtonText: 'انصراف'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            processRequest(requestId, 'approve');
                        }
                    });
                });

                // Handle reject button
                $("body").on('click', ".reject-btn", function(e) {
                    e.preventDefault();
                    const requestId = $(this).data('request-id');

                    Swal.fire({
                        title: 'آیا مطمئن هستید؟',
                        text: 'آیا می‌خواهید درخواست لغو را رد کنید؟',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#DC3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'بله، رد کن',
                        cancelButtonText: 'انصراف'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            processRequest(requestId, 'reject');
                        }
                    });
                });

                // Function to process request
                function processRequest(requestId, action) {
                    $.ajax({
                        type: 'POST',
                        url: "<?php echo admin_url('admin-ajax.php') ?>",
                        data: {
                            'action': 'team_ajax_handler',
                            'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                            'callback': 'cancellation_actions',
                            'function': 'update_cancellation_status',
                            'reqid': requestId,
                            'status': action === 'approve' ? 'approved' : 'rejected'
                        },
                        beforeSend: function() {
                            Swal.fire({
                                title: 'در حال پردازش...',
                                text: 'لطفاً منتظر بمانید',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    position: "bottom-start",
                                    icon: "success",
                                    text: action === 'approve' ? 'درخواست لغو با موفقیت تایید شد' : 'درخواست لغو با موفقیت رد شد',
                                    showConfirmButton: false,
                                    timer: 2000
                                });
                                // Refresh the data
                                fetch_cancellation_requests(currentStatus, currentPage);
                            } else {
                                Swal.fire({
                                    position: "bottom-start",
                                    icon: "error",
                                    text: response.data || 'خطا در پردازش درخواست',
                                    showConfirmButton: false,
                                    timer: 2000
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                position: "bottom-start",
                                icon: "error",
                                text: 'خطا در ارتباط با سرور',
                                showConfirmButton: false,
                                timer: 2000
                            });
                        }
                    });
                }

            });
        </script>
</div>
</section>
</div>
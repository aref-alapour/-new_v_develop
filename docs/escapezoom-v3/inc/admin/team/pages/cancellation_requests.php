<div class="flex justify-between items-center">
    <div class="flex justify-center items-center gap-d44">
        <h1 class="text-base font-extrabold lg:text-2xl">درخواست ها</h1>
    </div>
    <div class="flex items-center gap-x-7 divide-x">
        <a href="<?= home_url('/team/cancellation_history/'); ?>" target="_blank" class="block text-center content-center h-12.5 w-28 px-d20 bg-primary-500 text-white hover:bg-primary-600 font-extrabold text-base rounded-xl cursor-pointer">تاریخچه لغو</a>
        <button id="updateBtn" class="bg-slate-100 hover:bg-slate-120 rounded-lg flex items-center justify-center gap-x-1 h-12.5 w-28 px-d20 text-blue-link">
            <span id="updateBtnText">بروزرسانی</span>
            <svg id="updateBtnIcon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                <path d="M2.75935 9.75L2.3641 10.149C2.46945 10.2534 2.61177 10.312 2.7601 10.312C2.90843 10.312 3.05076 10.2534 3.15611 10.149L2.75935 9.75ZM4.41611 8.8995C4.52213 8.79428 4.582 8.65125 4.58257 8.50188C4.58313 8.3525 4.52433 8.20902 4.41911 8.103C4.367 8.05051 4.30507 8.00879 4.23685 7.98023C4.16862 7.95167 4.09544 7.93682 4.02148 7.93654C3.8721 7.93598 3.72863 7.99478 3.6226 8.1L4.41611 8.8995ZM1.8961 8.1C1.7896 7.9979 1.64724 7.94173 1.49971 7.9436C1.35218 7.94547 1.21129 8.00523 1.1074 8.11C1.00352 8.21477 0.944958 8.35616 0.944341 8.5037C0.943724 8.65124 1.0011 8.79312 1.1041 8.89875L1.8961 8.1ZM13.9621 5.54401C13.9998 5.60893 14.0501 5.66565 14.1101 5.71082C14.1701 5.756 14.2385 5.7887 14.3113 5.80702C14.3841 5.82534 14.4598 5.8289 14.5341 5.81748C14.6083 5.80606 14.6794 5.77991 14.7434 5.74055C14.8073 5.7012 14.8627 5.64944 14.9064 5.58833C14.95 5.52722 14.9809 5.458 14.9974 5.38474C15.0138 5.31149 15.0154 5.23568 15.0021 5.16179C14.9888 5.08789 14.9608 5.01742 14.9199 4.95451L13.9621 5.54401ZM9.05936 1.68751C5.27411 1.68751 2.19685 4.73326 2.19685 8.49975H3.32185C3.32185 5.36326 5.88686 2.81251 9.05936 2.81251V1.68751ZM2.19685 8.49975V9.75H3.32185V8.49975H2.19685ZM3.15685 10.1498L4.41611 8.8995L3.6226 8.1L2.3626 9.35025L3.15685 10.1498ZM3.15685 9.351L1.8961 8.1L1.10335 8.89875L2.36335 10.1483L3.15685 9.351ZM14.9199 4.95601C14.304 3.95568 13.442 3.12996 12.4161 2.55773C11.3903 1.98551 10.234 1.68589 9.05936 1.68751V2.81251C10.042 2.81061 11.0095 3.0608 11.8679 3.53916C12.7262 4.01752 13.4475 4.70805 13.9629 5.54475L14.9199 4.95601Z" fill="#1447E6" />
                <path opacity="0.5" d="M15.2351 8.25L15.6311 7.85025C15.5258 7.74605 15.3836 7.68761 15.2355 7.68761C15.0873 7.68761 14.9452 7.74605 14.8399 7.85025L15.2351 8.25ZM13.5754 9.09975C13.5228 9.1517 13.481 9.2135 13.4523 9.28161C13.4237 9.34971 13.4087 9.4228 13.4083 9.49669C13.4075 9.64592 13.4659 9.78938 13.5709 9.8955C13.6758 10.0016 13.8186 10.0617 13.9678 10.0626C14.117 10.0634 14.2605 10.0049 14.3666 9.9L13.5754 9.09975ZM16.1051 9.9C16.1573 9.95331 16.2197 9.99569 16.2885 10.0247C16.3572 10.0536 16.4311 10.0687 16.5057 10.0688C16.5804 10.069 16.6543 10.0542 16.7232 10.0255C16.7921 9.99683 16.8546 9.9547 16.907 9.9016C16.9595 9.8485 17.0009 9.7855 17.0287 9.71625C17.0566 9.64701 17.0704 9.57291 17.0693 9.49828C17.0682 9.42365 17.0523 9.34998 17.0225 9.28156C16.9927 9.21313 16.9495 9.15133 16.8956 9.09975L16.1051 9.9ZM3.98885 12.4545C3.91048 12.3275 3.78487 12.2368 3.63964 12.2024C3.49442 12.168 3.34148 12.1928 3.21448 12.2711C3.08747 12.3495 2.9968 12.4751 2.96241 12.6203C2.92802 12.7656 2.95273 12.9185 3.0311 13.0455L3.98885 12.4545ZM8.9126 16.3125C12.7091 16.3125 15.7976 13.269 15.7976 9.50025H14.6726C14.6726 12.6352 12.1001 15.1875 8.9126 15.1875V16.3125ZM15.7976 9.50025V8.25H14.6726V9.50025H15.7976ZM14.8399 7.85025L13.5754 9.09975L14.3666 9.9L15.6311 8.64975L14.8399 7.85025ZM14.8399 8.64975L16.1051 9.9L16.8956 9.09975L15.6311 7.85025L14.8399 8.64975ZM3.0311 13.0455C3.65108 14.0469 4.51705 14.8721 5.54656 15.4441C6.57607 16.0161 7.73486 16.315 8.9126 16.3125V15.1875C7.92687 15.19 6.95689 14.9402 6.09503 14.4618C5.23316 13.9834 4.50809 13.2924 3.98885 12.4545L3.0311 13.0455Z" fill="#1447E6" />
            </svg>
        </button>
    </div>
</div>
<div id="cardsContainer" class="space-y-8 mt-7"></div>

<div class="relative" id="data-list">
    <?php for ($i = 0; $i < 10; $i++) { ?>
        <div class="w-full h-12 rounded-xl mb-2 skeleton mt-2"></div>
    <?php } ?>
</div>
<div id="myModal" class="hidden" style="display: none;">
    <div class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center">
        <form method="POST" id="cancellationForm" class="p-d30 rounded-xl bg-white w-d234 h-d213 border border-rail shadow-rail-lip flex flex-col items-center justify-center">
            <input type="hidden" name="request_id" id="modalRequestId" value="">
            <svg xmlns="http://www.w3.org/2000/svg" width="38" height="37" viewBox="0 0 38 37" fill="none">
                <rect x="0.5" width="37" height="37" rx="18.5" fill="#F21543" />
                <path d="M9.60179 12.7514C8.43091 11.5805 8.43091 9.68216 9.60179 8.51128C10.7727 7.3404 13.6862 8.65325 14.8571 9.82413L28.1678 22.5533C29.3387 23.4936 30.3821 26.3588 28.8821 27.8587C27.7113 29.0296 25.8129 29.0296 24.642 27.8587L9.60179 12.7514Z" fill="#C6C6C6" />
                <path d="M28.3984 14.3659C30.1282 12.2902 28.975 8.94598 27.7065 8.13876C26.5356 6.96788 25.7647 8.65325 24.5938 9.82413L10.5687 23.8492C9.39785 25.0201 7.64162 24.629 10.569 27.7426C11.6777 28.7804 13.6381 29.1264 14.8089 28.0893L28.3984 14.3659Z" fill="#C6C6C6" />
                <g filter="url(#filter0_i_1714_67)">
                    <path d="M23.5822 8.21077C24.7531 7.04006 26.6516 7.03996 27.8224 8.21077C28.9932 9.3816 28.9931 11.2801 27.8224 12.451L23.5677 16.7048C23.1771 17.0953 23.1771 17.7286 23.5677 18.1191L27.8224 22.3739C28.9932 23.5447 28.9932 25.4432 27.8224 26.6141C26.6515 27.785 24.7531 27.7849 23.5822 26.6141L19.3274 22.3594C18.9369 21.9688 18.3037 21.9688 17.9132 22.3594L13.797 26.4764C12.6261 27.6471 10.7276 27.6472 9.55678 26.4764C8.38597 25.3056 8.38607 23.4071 9.55678 22.2362L13.6731 18.1191C14.0635 17.7285 14.0635 17.0954 13.673 16.7049L9.55678 12.5887C8.38615 11.4178 8.38598 9.51926 9.55678 8.34847C10.7276 7.17774 12.6261 7.17786 13.797 8.34847L17.9132 12.4647C18.3037 12.8552 18.9368 12.8552 19.3274 12.4647L23.5822 8.21077Z" fill="url(#paint0_linear_1714_67)" />
                </g>
                <defs>
                    <filter id="filter0_i_1714_67" x="8.67871" y="4.3327" width="20.0215" height="23.1595" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                        <feFlood flood-opacity="0" result="BackgroundImageFix" />
                        <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape" />
                        <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                        <feOffset dy="-3" />
                        <feGaussianBlur stdDeviation="1.5" />
                        <feComposite in2="hardAlpha" operator="arithmetic" k2="-1" k3="1" />
                        <feColorMatrix type="matrix" values="0 0 0 0 0.776471 0 0 0 0 0.776471 0 0 0 0 0.776471 0 0 0 1 0" />
                        <feBlend mode="normal" in2="shape" result="effect1_innerShadow_1714_67" />
                    </filter>
                    <linearGradient id="paint0_linear_1714_67" x1="44.6181" y1="7.02975" x2="22.3137" y2="6.49387" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#C6C6C6" />
                        <stop offset="1" stop-color="white" />
                    </linearGradient>
                </defs>
            </svg>
            <h3 class="text-base font-yekan-bold mt-4 mb-6 px-d7 text-center">آیا از تأیید و لغو رزرو این سانس مطمئن هستید؟</h3>
            <div class="flex flex-row justify-between gap-d10">
                <button class="text-slate-250 text-base bg-slate-100 rounded-xl w-d78 h-d33 font-yekan-bold confirmStatusBtn" type="submit">
                    بله
                </button>
                <button class="text-slate-250 text-base bg-slate-100 rounded-xl w-d78 h-d33 font-yekan-bold closeModalBtn" type="button">
                    بستن
                </button>
            </div>
        </form>
    </div>
</div>

<audio id="beep_sound" preload="auto">
    <source src="<?php echo get_template_directory_uri(); ?>/assets/sounds/beep.wav" type="audio/mpeg">
</audio>

<script>
    jQuery(document).ready(function($) {

        // Toast notification system
        function showToast(message, type = 'success', duration = 3000) {
            const toastId = 'toast-' + Date.now();
            const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
            const icon = type === 'success' ?
                '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>' :
                '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>';

            const toast = $(`
                <div id="${toastId}" class="fixed top-4 right-4 ${bgColor} text-white px-6 py-4 rounded-lg shadow-lg z-50 flex items-center gap-3 min-w-d300 max-w-d400 transform translate-x-full transition-transform duration-300 ease-in-out">
                    <div class="flex-shrink-0">
                        ${icon}
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium">${message}</p>
                    </div>
                    <button class="flex-shrink-0 ml-2 text-white hover:text-gray-200 transition-colors" onclick="closeToast('${toastId}')">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            `);

            $('body').append(toast);

            // Animate in
            setTimeout(() => {
                toast.removeClass('translate-x-full');
            }, 100);

            // Auto remove
            setTimeout(() => {
                closeToast(toastId);
            }, duration);
        }

        // Close toast function
        window.closeToast = function(toastId) {
            const toast = $('#' + toastId);
            toast.addClass('translate-x-full');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }

        function fetch_cancellation_requests(showMessages = false) {
            // Show loading state
            $('#updateBtn').prop('disabled', true);
            $('#updateBtnText').hide();
            $('#updateBtnIcon').addClass('animate-spin');

            $.ajax({
                type: 'POST',
                url: "<?php echo admin_url('admin-ajax.php') ?>",
                data: {
                    'action': 'team_ajax_handler',
                    'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                    'callback': 'cancellation_requests_get',
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

                    if (data && $.trim(data).length > 0) {
                        const audio = new Audio("<?php echo get_template_directory_uri(); ?>/assets/sounds/beep.wav");
                        audio.play();
                        startBlinkingTitle("🔔 درخواست جدید!");

                    } else {
                        stopBlinkingTitle();
                    }

                    // Show success message only if showMessages is true
                    if (showMessages) {
                        const successMsg = $('<div class="fixed bottom-4 left-4 bg-green-500 text-white px-4 py-2 rounded-lg z-50">بروزرسانی با موفقیت انجام شد</div>');
                        $('body').append(successMsg);
                        setTimeout(() => successMsg.fadeOut(500, () => successMsg.remove()), 2000);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching cancellation requests:', error);
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
                    $('#updateBtn').prop('disabled', false);
                    $('#updateBtnText').show();
                    $('#updateBtnIcon').removeClass('animate-spin');
                }
            });
        }

        let titleBlinkInterval = null;
        const originalTitle = document.title;

        function startBlinkingTitle(newTitle) {
            if (titleBlinkInterval) return;

            let visible = false;
            titleBlinkInterval = setInterval(() => {
                document.title = visible ? newTitle : " ";
                visible = !visible;
            }, 1000);
        }

        function stopBlinkingTitle() {
            if (titleBlinkInterval) {
                clearInterval(titleBlinkInterval);
                titleBlinkInterval = null;
                document.title = originalTitle;
            }
        }

        fetch_cancellation_requests();

        setInterval(fetch_cancellation_requests, 3 * 60 * 1000);

        // Handle update button click
        $('#updateBtn').on('click', function() {
            fetch_cancellation_requests(true);
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

        // Handle form submission
        $('#cancellationForm').on('submit', function(e) {
            e.preventDefault();

            var requestId = $('#modalRequestId').val();
            console.log('Request ID:', requestId);

            // Here you can add your AJAX call or other processing
            $.ajax({
                type: 'POST',
                url: "<?php echo admin_url('admin-ajax.php') ?>",
                data: {
                    'action': 'team_ajax_handler',
                    'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                    'callback': 'cancellation_actions',
                    'function': 'update_cancellation_status',
                    'reqid': requestId,
                    'status': 'approved'
                },
                success: function(data) {
                    console.log(data);

                    // Check if the response indicates success
                    let responseData;
                    try {
                        responseData = typeof data === 'string' ? JSON.parse(data) : data;
                    } catch (e) {
                        responseData = {
                            success: true,
                            message: 'عملیات با موفقیت انجام شد'
                        };
                    }

                    if (responseData.success !== false) {
                        // Show success toast
                        showToast(responseData.message || 'درخواست لغو با موفقیت تأیید شد', 'success', 4000);

                        // Close modal after successful submission
                        $('#myModal').addClass('hidden').hide();

                        // Refresh the data after successful action
                        fetch_cancellation_requests();
                    } else {
                        // Show error toast for failed response
                        showToast(responseData.message || 'خطا در تأیید درخواست لغو', 'error', 5000);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);

                    let errorMessage = 'خطا در پردازش درخواست. لطفاً دوباره تلاش کنید.';

                    // Try to get error message from response
                    if (xhr.responseText) {
                        try {
                            const responseData = JSON.parse(xhr.responseText);
                            if (responseData.message) {
                                errorMessage = responseData.message;
                            }
                        } catch (e) {
                            // Use default error message
                        }
                    }

                    showToast(errorMessage, 'error', 5000);
                }
            });
        });

        // Close modal when clicking outside of it
        $(document).on('click', '#myModal', function(e) {
            if (e.target === this || $(e.target).hasClass('fixed')) {
                console.log('Closing modal by clicking outside');
                $('#myModal').addClass('hidden').hide();
            }
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
    });
</script>
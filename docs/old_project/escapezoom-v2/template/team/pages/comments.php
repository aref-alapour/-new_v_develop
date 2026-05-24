<input type="hidden" id="current_product_id">
<div class="flex justify-between items-center">
    <div class="flex justify-center items-center gap-x-16">
        <h1 class="text-base font-extrabold lg:text-2xl">کامنت ها</h1>
        <div class="flex gap-5" id="filterTabs">
            <div class="tab text-sm font-bold text-grayy active" data-status="all"> همه </div>
            <div class="tab text-sm font-bold text-grayy" data-status="trash"> حذف شده ها </div>
            <div class="tab text-sm font-bold text-grayy" data-status="moderated"> عدم نمایش ها </div>
        </div>
    </div>
    <div class="flex items-center gap-x-6">
        <div class="relative w-full min-w-75 h-[58px]" style="z-index: 99;">
            <input id="gameSearch" class="w-full min-w-75 h-[58px] border border-[#E4EBF0] bg-[#FAFDFF] rounded-xl outline-none px-6 py-5 text-xs font-bold text-navyBlue" placeholder="جست و جو بازی" />
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none" class="absolute top-5 left-6">
                <path d="M15.2149 14.2756L17.8133 16.8727C17.9344 16.9981 18.0015 17.1661 18 17.3406C17.9985 17.515 17.9285 17.6818 17.8052 17.8052C17.6818 17.9285 17.515 17.9985 17.3406 18C17.1661 18.0015 16.9981 17.9344 16.8727 17.8133L14.2743 15.2149C12.5764 16.6697 10.381 17.4102 8.14876 17.2812C5.91656 17.1522 3.82111 16.1636 2.30209 14.5229C0.78307 12.8822 -0.0414283 10.7169 0.00160352 8.48138C0.0446353 6.24587 0.951852 4.11392 2.53289 2.53289C4.11392 0.951852 6.24587 0.0446353 8.48138 0.00160352C10.7169 -0.0414283 12.8822 0.78307 14.5229 2.30209C16.1636 3.82111 17.1522 5.91656 17.2812 8.14876C17.4102 10.381 16.6697 12.5764 15.2149 14.2743V14.2756ZM8.64792 15.9653C10.5886 15.9653 12.4498 15.1944 13.8221 13.8221C15.1944 12.4498 15.9653 10.5886 15.9653 8.64792C15.9653 6.70723 15.1944 4.84602 13.8221 3.47375C12.4498 2.10148 10.5886 1.33054 8.64792 1.33054C6.70723 1.33054 4.84602 2.10148 3.47375 3.47375C2.10148 4.84602 1.33054 6.70723 1.33054 8.64792C1.33054 10.5886 2.10148 12.4498 3.47375 13.8221C4.84602 15.1944 6.70723 15.9653 8.64792 15.9653Z" fill="#0F172B" />
            </svg>
            <div id="lg-search-result-list" class="max-h-75 divide-y divide-[#E4EBF0] overflow-y-auto px-4 py-5" style="background: #f3f3f3;display: none"></div>
        </div>
        <div class="flex items-center justify-between mx-auto">
            <div class="flex items-center gap-2">
                <input id="term_filter_text" type="text" placeholder="جستجو دیدگاه" class="h-[58px] w-[179px] px-4 py-2.5 rounded-lg text-xs font-bold border border-[#E4EBF0] bg-[#FAFDFF] text-navyBlue outline-none" />
                <button id="term_filter_btn" class="h-[58px] text-orange-500 border border-orange-400 px-4 py-2 rounded-lg hover:bg-orange-50 text-base font-bold cursor-pointer text-nowrap"> اعمال فیلتر </button>
            </div>
        </div>
    </div>
</div>

<div class="relative" id="data-list">
    <?php for ($i = 0; $i < 10; $i++) { ?>
        <div class="w-full h-12 rounded-xl mb-2 skeleton mt-7"></div>
    <?php } ?>
</div>

<!-- Modals -->
<!-- Open Modal -->
<div id="modalOverlay" class="fixed inset-0 z-50 backdrop-blur-sm bg-white/30"
    style="display: none;z-index: 999">
    <div id="modalContent" class="bg-white rounded-lg w-full max-w-xl p-6 shadow-xl absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">

        <!-- Hidden input to store data-id -->
        <input type="hidden" id="currentCommentId" value="">

        <div class="flex flex-wrap justify-center items-center gap-3 mb-1">
            <button id="publishBtn"
                class="modal-action-btn bg-[#02A159] hover:bg-green-700 text-white py-2 rounded-lg w-[103px] h-[38px] text-base font-yekan-bold cursor-pointer">انتشار</button>
            <button id="editCommentBtn"
                class="modal-action-btn bg-[#2B7FFF] hover:bg-blue-700 text-white py-2 rounded-lg w-[103px] h-[38px] text-base font-yekan-bold cursor-pointer">ویرایش
                کامنت</button>
            <button id="hideBtn"
                class="modal-action-btn bg-[#D08700] hover:bg-yellow-700 text-white py-2 rounded-lg w-[103px] h-[38px] text-base font-yekan-bold cursor-pointer">عدم
                نمایش</button>
            <button id="showDeleteReason"
                class="modal-action-btn bg-red-500 hover:bg-red-700 text-white py-2 rounded-lg w-[103px] h-[38px] text-base font-yekan-bold cursor-pointer">حذف</button>
        </div>

        <div id="deleteReasonBox" style="display: none;">

            <hr class="text-[#E4EBF0] my-3" />
            <p class="text-base font-yekan-bold text-navyBlue mb-1">علت حذف این کامنت را مشخص کنید.</p>
            <p class="text-sm font-yekan-bold text-grayy mb-4">لطفاً یک گزینه را انتخاب کنید:</p>
            <hr class="text-[#E4EBF0] my-3" />

            <form class="space-y-3">
                <label class="flex items-center gap-2 text-sm font-yekan-bold">
                    <input type="radio" name="reason" value="حاوی توهین یا تهمت" class="accent-blue-500 w-6 h-6" /> حاوی توهین یا تهمت
                </label>

                <label class="flex items-center gap-2 text-sm font-yekan-bold">
                    <input type="radio" name="reason" value="حاوی الفاظ رکیک" class="accent-blue-500 w-6 h-6" /> حاوی الفاظ رکیک
                </label>

                <label class="flex items-center gap-2 text-sm font-yekan-bold">
                    <input type="radio" name="reason" value="عدم مطابقت با واقعیت" class="accent-blue-500 w-6 h-6" /> عدم مطابقت با واقعیت
                </label>

                <label class="flex items-center gap-2 text-sm font-yekan-bold">
                    <input type="radio" name="reason" value="لو دادن بخشی از بازی" class="accent-blue-500 w-6 h-6" /> لو دادن بخشی از بازی
                </label>

                <label class="flex items-center gap-2 text-sm font-yekan-bold">
                    <input type="radio" name="reason" value="عدم تجربه کامل این بازی" class="accent-blue-500 w-6 h-6" checked /> عدم تجربه کامل این بازی
                </label>

                <textarea placeholder="دلیل لغو را بنویسید..." id="reason_text" rows="3" class="w-full border border-[#E8EDF1] rounded-md p-2 text-sm resize-none mt-2 outline-none"></textarea>

                <button type="button"
                    class="w-full bg-orange-500 hover:bg-orange-600 text-white py-2 rounded-lg mt-4 text-lg font-yekan-heavy"
                    style="box-shadow: 0px 2px 0px 0px #CA5608;">ثبت و حذف</button>
            </form>
        </div>

        <!-- Edit Comment Section -->
        <div id="editCommentBox" style="display: none;">
            <hr class="text-[#E4EBF0] mt-3 mb-5" />
            <form>
                <!-- Edit Username -->
                <label class="block text-sm font-yekan-bold text-navyBlue mb-2">
                    <span>ویرایش نام کاربر</span>
                    <span class="text-sm font-yekan-bold text-grayy">(کامنت گذار)</span>
                </label>
                <input type="text" id="editUsername" placeholder="نام کاربر را وارد کنید..."
                    class="w-full border border-[#E8EDF1] rounded-xl p-3 shadow-3 text-sm outline-none">
                <hr class="text-[#E4EBF0] border-2 my-5" />
                <!-- Edit Comment Text -->
                <label class="block text-sm font-yekan-bold text-navyBlue mb-2">ویرایش متن کامنت</label>
                <textarea id="editCommentText" placeholder="متن کامنت را وارد کنید..." rows="4"
                    class="w-full border border-[#E8EDF1] rounded-xl p-3 shadow-3 text-sm outline-none"></textarea>
                <hr class="text-[#E4EBF0] border-2 my-5" />
                <!-- Rating Sections -->
                <div class="grid grid-cols-5 gap-2">
                    <!-- بازیگردانی‌واکت -->
                    <div class="flex flex-col gap-y-2">
                        <label class="text-sm font-yekan-bold text-navyBlue">بازیگردانی‌واکت</label>
                        <div class="relative w-full">
                            <select id="act"
                                class="appearance-none bg-white border border-[#E8EDF1] rounded-[10px] px-3 py-2 pl-6 text-sm font-yekan-bold focus:outline-none focus:ring-2 focus:ring-[#D08700] w-full shadow-3"
                                style="color: #D08700;">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                            </select>
                            <div class="absolute inset-y-0 left-0 flex items-center px-2 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- کیفیت معما -->
                    <div class="flex flex-col gap-y-2">
                        <label class="text-sm font-yekan-bold text-navyBlue">کیفیت معما</label>
                        <div class="relative w-full">
                            <select id="moama"
                                class="appearance-none bg-white border border-[#E8EDF1] rounded-[10px] px-3 py-2 pl-6 text-sm font-yekan-bold focus:outline-none focus:ring-2 focus:ring-[#D08700] w-full shadow-3"
                                style="color: #D08700;">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                            </select>
                            <div class="absolute inset-y-0 left-0 flex items-center px-2 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- فضاسازی -->
                    <div class="flex flex-col gap-y-2">
                        <label class="text-sm font-yekan-bold text-navyBlue">فضاسازی</label>
                        <div class="relative w-full">
                            <select id="fazasazi"
                                class="appearance-none bg-white border border-[#E8EDF1] rounded-[10px] px-3 py-2 pl-6 text-sm font-yekan-bold focus:outline-none focus:ring-2 focus:ring-[#D08700] w-full shadow-3"
                                style="color: #D08700;">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                            </select>
                            <div class="absolute inset-y-0 left-0 flex items-center px-2 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- تازگی و خلاقیت -->
                    <div class="flex flex-col gap-y-2">
                        <label class="text-sm font-yekan-bold text-navyBlue">تازگی و خلاقیت</label>
                        <div class="relative w-full">
                            <select id="tazegi"
                                class="appearance-none bg-white border border-[#E8EDF1] rounded-[10px] px-3 py-2 pl-6 text-sm font-yekan-bold focus:outline-none focus:ring-2 focus:ring-[#D08700] w-full shadow-3"
                                style="color: #D08700;">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                            </select>
                            <div class="absolute inset-y-0 left-0 flex items-center px-2 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- برخورد پرسنل -->
                    <div class="flex flex-col gap-y-2">
                        <label class="text-sm font-yekan-bold text-navyBlue">برخورد پرسنل</label>
                        <div class="relative w-full">
                            <select id="personel"
                                class="appearance-none bg-white border border-[#E8EDF1] rounded-[10px] px-3 py-2 pl-6 text-sm font-yekan-bold focus:outline-none focus:ring-2 focus:ring-[#D08700] w-full shadow-3"
                                style="color: #D08700;">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                            </select>
                            <div class="absolute inset-y-0 left-0 flex items-center px-2 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="button"
                    class="w-full bg-[#FD7013] hover:bg-[#CA5608] text-white py-2 rounded-lg mt-4 text-lg font-yekan-heavy cursor-pointer"
                    style="box-shadow: 0px 2px 0px 0px #CA5608;">ثبت ویرایش</button>
            </form>
        </div>
    </div>
</div>
<!-- Open Modal Level -->
<div id="modalOverlaylevel" class="fixed inset-0 z-50 backdrop-blur-sm bg-white/30"
    style="display: none;">
    <div id="modalContentlevel" class="bg-white rounded-lg max-w-xl p-6 shadow-xl w-[400px] min-h-[300px] absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
        <div class="flex flex-col">
            <div class="flex justify-between mb-6">
                <div class="flex items-center gap-2">
                    <p class="font-yekan-bold text-lg">عالی بود</p>
                    <img src="./assets/images/Smaller.svg" alt="">
                </div>
                <div class="flex items-center gap-2">
                    <p class="font-yekan-bold text-lg" id="totalRating">4.83</p>
                    <img src="./assets/images/mage_star.svg" alt="">
                </div>
            </div>



            <!-- Progress Bars -->
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <p class="text-sm font-yekan-bold text-[#4E5C6D] w-32">بازیگردانی‌واکت</p>
                    <div class="flex-1 mx-4">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div
                                class="progress-bar bg-gradient-to-r from-pink-500 to-pink-600 h-2 rounded-full transition-all duration-1000 ease-out"
                                data-rating="0" style="width: 0%"></div>
                        </div>
                    </div>
                    <p class="text-sm font-yekan-bold text-[#D08700] w-6 text-center" id="actingRating">0</p>
                </div>

                <div class="flex justify-between items-center">
                    <p class="text-sm font-yekan-bold text-[#4E5C6D] w-32">کیفیت معما</p>
                    <div class="flex-1 mx-4">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div
                                class="progress-bar bg-gradient-to-r from-green-500 to-green-600 h-2 rounded-full transition-all duration-1000 ease-out"
                                data-rating="0" style="width: 0%"></div>
                        </div>
                    </div>
                    <p class="text-sm font-yekan-bold text-[#D08700] w-6 text-center" id="puzzleRating">0</p>
                </div>

                <div class="flex justify-between items-center">
                    <p class="text-sm font-yekan-bold text-[#4E5C6D] w-32">فضاسازی</p>
                    <div class="flex-1 mx-4">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div
                                class="progress-bar bg-gradient-to-r from-purple-500 to-purple-600 h-2 rounded-full transition-all duration-1000 ease-out"
                                data-rating="0" style="width: 0%"></div>
                        </div>
                    </div>
                    <p class="text-sm font-yekan-bold text-[#D08700] w-6 text-center" id="atmosphereRating">0</p>
                </div>

                <div class="flex justify-between items-center">
                    <p class="text-sm font-yekan-bold text-[#4E5C6D] w-32">تازگی و خلاقیت</p>
                    <div class="flex-1 mx-4">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div
                                class="progress-bar bg-gradient-to-r from-orange-500 to-orange-600 h-2 rounded-full transition-all duration-1000 ease-out"
                                data-rating="0" style="width: 0%"></div>
                        </div>
                    </div>
                    <p class="text-sm font-yekan-bold text-[#D08700] w-6 text-center" id="creativityRating">0</p>
                </div>

                <div class="flex justify-between items-center">
                    <p class="text-sm font-yekan-bold text-[#4E5C6D] w-32">برخورد پرسنل</p>
                    <div class="flex-1 mx-4">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div
                                class="progress-bar bg-gradient-to-r from-red-500 to-red-600 h-2 rounded-full transition-all duration-1000 ease-out"
                                data-rating="0" style="width: 0%"></div>
                        </div>
                    </div>
                    <p class="text-sm font-yekan-bold text-[#D08700] w-6 text-center" id="staffRating">0</p>
                </div>
            </div>

        </div>
    </div>
</div>
<script>
    jQuery(document).ready(function($) {

        const get_data = (status, product_id, term, page) => {
            $.ajax({
                type: 'POST',
                url: "<?php echo admin_url('admin-ajax.php') ?>",
                data: {
                    'action': 'team_ajax_handler',
                    'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                    'callback': 'comments_get',
                    'status': status,
                    'product_id': product_id,
                    'term': term,
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

        get_data('all', '', '', 1);

        $("body").on('click', "#filterTabs div", function(e) {
            $('#filterTabs div').removeClass('active');
            $(this).addClass('active');

            let status = $("#filterTabs div.active").data('status');
            let product_id = $("#current_product_id").val();
            let term = $("#term_filter_text").val();

            get_data(status, product_id, term, 1);
        });

        $("body").on('click', ".pagination a", function(e) {
            e.preventDefault();

            const page = $(this).attr('href').split('?page=')[1];

            let status = $("#filterTabs div.active").data('status');
            let product_id = $("#current_product_id").val();
            let term = $("#term_filter_text").val();

            get_data(status, product_id, term, page);
        });

        $('body').on('input', "#gameSearch", function() {

            $('#lg-search-result-list').html('').hide();
            $("#current_product_id").val('');

            let term = $(this).val();

            if (term == '')
                return;

            $.ajax({
                type: 'POST',
                url: "<?php echo site_url('web-service/team/sans_management.php') ?>",
                data: {
                    "type": `game_search`,
                    "data": {
                        "term": term,
                    }
                },
                success: function(data) {
                    $('#lg-search-result-list').show().html(data);
                }
            });
        });

        $("body").on('click', "#term_filter_btn", function(e) {

            let status = $("#filterTabs div.active").data('status');
            let product_id = $("#current_product_id").val();
            let term = $("#term_filter_text").val();

            get_data(status, product_id, term, 1);
        });

        $('body').on('click', ".team_sans_game_search_item", function() {

            let product_id = $(this).data('id');
            let title = $(this).data('title');
            let status = $("#filterTabs div.active").data('status');
            let term = $("#term_filter_text").val();

            $("#current_product_id").val(product_id);

            $('#lg-search-result-list').html('').hide();
            $('#gameSearch').val(title);

            get_data(status, product_id, term, 1);
        });

        $('body').on('click', "#deleteReasonBox button", function() {
            const $button = $(this);
            const originalText = $button.text();

            // Disable button and show loading state
            $button.prop('disabled', true).html(`
                <div class="flex items-center justify-center gap-2">
                    <svg class="animate-spin h-4 w-4 text-white mx-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>لطفاً منتظر بمانید...</span>
                </div>
            `);

            let reason = $("input[name='reason']:checked").val();
            if (reason === "other")
                reason = $("#reason_text").val();

            $.ajax({
                type: 'POST',
                url: "<?php echo admin_url('admin-ajax.php') ?>",
                data: {
                    'action': 'team_ajax_handler',
                    'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                    'callback': 'comments_actions',
                    'operation': 'trash',
                    'reason': reason,
                    'comment_id': $("#currentCommentId").val(),
                },
                success: function(data) {
                    closeModal();
                    showToast('کامنت با موفقیت حذف شد', 'success', 4000);

                    // Refresh data to show changes
                    let status = $("#filterTabs div.active").data('status');
                    let product_id = $("#current_product_id").val();
                    let term = $("#term_filter_text").val();
                    get_data(status, product_id, term, 1);
                },
                error: function() {
                    // Reset button state
                    $button.prop('disabled', false).text(originalText);
                    showToast('خطا در حذف کامنت. لطفاً دوباره تلاش کنید', 'error', 5000);
                }
            });
        });

        $('body').on('click', "#editCommentBox button", function() {
            const $button = $(this);
            const originalText = $button.text();

            // Disable button and show loading state
            $button.prop('disabled', true).html(`
                <div class="flex items-center justify-center gap-2">
                    <svg class="animate-spin h-4 w-4 text-white mx-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>لطفاً منتظر بمانید...</span>
                </div>
            `);

            let ratings = {
                act: $("#act").val(),
                moama: $("#moama").val(),
                fazasazi: $("#fazasazi").val(),
                tazegi: $("#tazegi").val(),
                personel: $("#personel").val()
            };

            $.ajax({
                type: 'POST',
                url: "<?php echo admin_url('admin-ajax.php') ?>",
                data: {
                    'action': 'team_ajax_handler',
                    'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                    'callback': 'comments_actions',
                    'operation': 'edit',
                    'author': $("#editUsername").val(),
                    'content': $("#editCommentText").val(),
                    'ratings': ratings,
                    'comment_id': $("#currentCommentId").val(),
                },
                success: function(data) {
                    closeModal();
                    showToast('کامنت با موفقیت ویرایش شد', 'success', 4000);

                    // Refresh data to show changes
                    let status = $("#filterTabs div.active").data('status');
                    let product_id = $("#current_product_id").val();
                    let term = $("#term_filter_text").val();
                    get_data(status, product_id, term, 1);
                },
                error: function() {
                    // Reset button state
                    $button.prop('disabled', false).text(originalText);
                    showToast('خطا در ویرایش کامنت. لطفاً دوباره تلاش کنید', 'error', 5000);
                }
            });
        });
        // Open level modal
        $('body').on('click', ".openModalLevel", function() {
            const commentId = $(this).data('comment-id');
            const acting = $(this).data('acting');
            const puzzle = $(this).data('puzzle');
            const atmosphere = $(this).data('atmosphere');
            const creativity = $(this).data('creativity');
            const staff = $(this).data('staff');
            // Calculate total rating
            const totalRating = ((acting + puzzle + atmosphere + creativity + staff) / 5).toFixed(2);

            // Set total rating
            $("#totalRating").text(totalRating);

            // Animate progress bars
            setTimeout(() => {
                animateProgressBar('#actingRating', '.progress-bar:eq(0)', acting);
                animateProgressBar('#puzzleRating', '.progress-bar:eq(1)', puzzle);
                animateProgressBar('#atmosphereRating', '.progress-bar:eq(2)', atmosphere);
                animateProgressBar('#creativityRating', '.progress-bar:eq(3)', creativity);
                animateProgressBar('#staffRating', '.progress-bar:eq(4)', staff);
            }, 100);

            // Show modal
            $("#modalOverlaylevel").show();
        });

        // Close level modal
        $('body').on('click', "#modalOverlaylevel", function(e) {
            if (e.target === this) {
                closeLevelModal();
            }
        });

        $('body').on('click', "#modalContentlevel", function(e) {
            e.stopPropagation();
        });

        // Function to animate progress bars
        function animateProgressBar(ratingId, progressBarSelector, rating) {
            const progressBar = $(progressBarSelector);
            const ratingElement = $(ratingId);

            // Set rating text
            ratingElement.text(rating);

            // Calculate width percentage (rating out of 5)
            const widthPercentage = (rating / 5) * 100;

            // Animate progress bar
            progressBar.attr('data-rating', rating);
            progressBar.css('width', widthPercentage + '%');
        }

        // Function to close level modal and clear data
        function closeLevelModal() {
            $("#modalOverlaylevel").hide();

            // Reset progress bars
            $('.progress-bar').css('width', '0%').attr('data-rating', '0');
            $('#actingRating, #puzzleRating, #atmosphereRating, #creativityRating, #staffRating').text('0');
            $("#totalRating").text('0.00');
        }

        // Function to close main modal
        function closeModal() {
            $("#modalOverlay").hide().removeClass("flex");
            $("#deleteReasonBox").hide();
            $("#editCommentBox").hide();
            $("#currentCommentId").val("");
            // Reset button states
            $(".modal-action-btn").removeClass("opacity-50 cursor-not-allowed").prop("disabled", false);

            // Reset all buttons to original state
            $("#publishBtn").prop('disabled', false).text('انتشار');
            $("#hideBtn").prop('disabled', false).text('عدم نمایش');
            $("#editCommentBox button").prop('disabled', false).text('ثبت ویرایش');
            $("#deleteReasonBox button").prop('disabled', false).text('ثبت و حذف');
        }

        // Toast notification system
        function showToast(message, type = 'success', duration = 3000) {
            const toastId = 'toast-' + Date.now();
            const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
            const icon = type === 'success' ? '✓' : type === 'error' ? '✕' : 'ℹ';

            const toast = $(`
                <div id="${toastId}" class="fixed bottom-4 left-4 ${bgColor} text-white px-6 py-4 rounded-lg shadow-lg z-[9999] flex items-center gap-3 min-w-[300px] max-w-[400px] transform -translate-x-full transition-transform duration-300 ease-in-out">
                    <span class="text-xl">${icon}</span>
                    <span class="flex-1 text-sm font-yekan-bold">${message}</span>
                    <button class="flex-shrink-0 ml-2 text-white hover:text-gray-200 transition-colors" onclick="closeToast('${toastId}')">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            `);

            $('body').append(toast);

            // Animate in
            setTimeout(() => {
                toast.removeClass('-translate-x-full');
            }, 100);

            // Auto remove
            setTimeout(() => {
                closeToast(toastId);
            }, duration);
        }

        // Close toast function
        window.closeToast = function(toastId) {
            const toast = $('#' + toastId);
            toast.addClass('-translate-x-full');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }
        //  -----modal-taeed-sanse--------------------------
        // نمایش مدال
        $('body').on('click', "#openModalBtn", function() {
            $("#myModal").fadeIn(100);
        });

        // بستن مدال با دکمه "بستن"
        $('body').on('click', ".closeModalBtn", function() {
            $("#myModal").fadeOut(300);
        });

        // بستن مدال با کلیک روی بک‌گراند سیاه
        $('body').on('click', "#myModal", function(e) {
            if ($(e.target).is("#myModal")) {
                $(this).fadeOut(300);
            }
        });

        $('body').on('click', $(document), function(event) {
            if (!$(event.target).closest(".dropdown").length) {
                $(".dropdown-content").hide();
            }
        });

        $('body').on('click', ".dropdown > button", function(e) {
            e.stopPropagation();
            const dropdown = $(this).siblings(".dropdown-content");
            $(".dropdown-content").not(dropdown).hide();
            dropdown.toggle();
        });

        //   -----modal-page-comments---------------------------------------
        // Open modal and set data-id
        $('body').on('click', ".openModal", function() {
            const commentId = $(this).data('id');
            const username = $(this).data('username');
            const comment = $(this).data('comment');
            const acting = $(this).data('acting');
            const puzzle = $(this).data('puzzle');
            const atmosphere = $(this).data('atmosphere');
            const creativity = $(this).data('creativity');
            const staff = $(this).data('staff');

            $("#currentCommentId").val(commentId);
            $("#editUsername").val(username);
            $("#editCommentText").val(comment);

            // Set rating values in edit comment section
            $("#act").val(acting);
            $("#moama").val(puzzle);
            $("#fazasazi").val(atmosphere);
            $("#tazegi").val(creativity);
            $("#personel").val(staff);

            $("#modalOverlay").show().addClass("flex");
            $("#deleteReasonBox").hide();
            $("#editCommentBox").hide();

            // Reset button states
            $(".modal-action-btn").removeClass("opacity-50 cursor-not-allowed").prop("disabled", false);
        })

        // Close modal function - removed global body click handler that was interfering

        $('body').on('click', "#modalOverlay", function(e) {
            if (e.target === this) {
                $("#modalOverlay").hide().removeClass("flex");
                $("#deleteReasonBox").hide();
                $("#editCommentBox").hide();
                $("#currentCommentId").val("");
                // Reset button states
                $(".modal-action-btn").removeClass("opacity-50 cursor-not-allowed").prop("disabled", false);
            }
        });

        $('body').on('click', "#modalContent", function(e) {
            e.stopPropagation();
        });

        // Button state management
        $('body').on('click', ".modal-action-btn", function(e) {
            // Gray out other buttons
            $(".modal-action-btn").not(this).addClass("opacity-50 cursor-not-allowed").prop("disabled", true);
            // Keep current button active
            $(this).removeClass("opacity-50 cursor-not-allowed").prop("disabled", false);

            // Hide edit and delete boxes when clicking publish or hide buttons
            if ($(this).attr('id') === 'publishBtn' || $(this).attr('id') === 'hideBtn') {
                $("#deleteReasonBox").hide();
                $("#editCommentBox").hide();
            }
        });

        // Publish button with confirmation
        $('body').on('click', "#publishBtn", function() {
            Swal.fire({
                title: '<strong>تأیید انتشار</strong>',
                text: 'آیا مطمئن هستید که می‌خواهید این کامنت را منتشر کنید؟',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#02A159',
                cancelButtonColor: '#d33',
                confirmButtonText: 'بله، منتشر کن',
                cancelButtonText: 'انصراف',
                reverseButtons: true,
                width: '400px',
                customClass: {
                    title: 'font-yekan-bold text-lg',
                    content: 'font-yekan-bold text-sm'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const $button = $(this);
                    const originalText = $button.text();

                    // Disable button and show loading state with fixed width
                    $button.prop('disabled', true).html(`
                        <div class="flex items-center justify-center w-full">
                            <svg class="animate-spin h-4 w-4 text-white mx-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    `);

                    $.ajax({
                        type: 'POST',
                        url: "<?php echo admin_url('admin-ajax.php') ?>",
                        data: {
                            'action': 'team_ajax_handler',
                            'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                            'callback': 'comments_actions',
                            'operation': 'approve_actions',
                            'approve_type': 'approve',
                            'comment_id': $("#currentCommentId").val(),
                        },
                        success: function(data) {
                            closeModal();
                            showToast('کامنت با موفقیت منتشر شد', 'success', 4000);

                            // Refresh data to show changes
                            let status = $("#filterTabs div.active").data('status');
                            let product_id = $("#current_product_id").val();
                            let term = $("#term_filter_text").val();
                            get_data(status, product_id, term, 1);
                        },
                        error: function() {
                            // Reset button state
                            $button.prop('disabled', false).text(originalText);
                            showToast('خطا در انتشار کامنت. لطفاً دوباره تلاش کنید', 'error', 5000);
                        }
                    });

                } else {
                    // Reset button state if cancelled
                    $(".modal-action-btn").removeClass("opacity-50 cursor-not-allowed").prop("disabled", false);
                }
            });
        });

        // Hide button with confirmation
        $('body').on('click', "#hideBtn", function() {
            Swal.fire({
                title: '<strong>تأیید عدم نمایش</strong>',
                text: 'آیا مطمئن هستید که می‌خواهید این کامنت را از نمایش پنهان کنید؟',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#D08700',
                cancelButtonColor: '#d33',
                confirmButtonText: 'بله، پنهان کن',
                cancelButtonText: 'انصراف',
                reverseButtons: true,
                width: '400px',
                customClass: {
                    title: 'font-yekan-bold text-lg',
                    content: 'font-yekan-bold text-sm'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const $button = $(this);
                    const originalText = $button.text();

                    // Disable button and show loading state with fixed width
                    $button.prop('disabled', true).html(`
                        <div class="flex items-center justify-center w-full">
                            <svg class="animate-spin h-4 w-4 text-white mx-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    `);

                    $.ajax({
                        type: 'POST',
                        url: "<?php echo admin_url('admin-ajax.php') ?>",
                        data: {
                            'action': 'team_ajax_handler',
                            'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                            'callback': 'comments_actions',
                            'operation': 'approve_actions',
                            'approve_type': 'hold',
                            'comment_id': $("#currentCommentId").val(),
                        },
                        success: function(data) {
                            closeModal();
                            showToast('کامنت با موفقیت پنهان شد', 'success', 4000);

                            // Refresh data to show changes
                            let status = $("#filterTabs div.active").data('status');
                            let product_id = $("#current_product_id").val();
                            let term = $("#term_filter_text").val();
                            get_data(status, product_id, term, 1);
                        },
                        error: function() {
                            // Reset button state
                            $button.prop('disabled', false).text(originalText);
                            showToast('خطا در پنهان کردن کامنت. لطفاً دوباره تلاش کنید', 'error', 5000);
                        }
                    });

                } else {
                    $(".modal-action-btn").removeClass("opacity-50 cursor-not-allowed").prop("disabled", false);
                }
            });
        });

        // Show delete reason box
        $('body').on('click', "#showDeleteReason", function() {
            $("#editCommentBox").hide();
            $("#deleteReasonBox").slideDown(500);
            // Reset button states when showing delete box
            $(".modal-action-btn").removeClass("opacity-50 cursor-not-allowed").prop("disabled", false);
        });

        // Show edit comment box
        $('body').on('click', "#editCommentBtn", function() {
            $("#deleteReasonBox").hide();
            $("#editCommentBox").slideDown(500);
            // Reset button states when showing edit box
            $(".modal-action-btn").removeClass("opacity-50 cursor-not-allowed").prop("disabled", false);
        });

        //  -----modalLevel-in-page-comment-----------------------
        // Duplicate handler removed - already handled above

        //  ----close-all-sass-----------------------------

        let isOn = true;

        $('body').on('click', "#toggleSwitch", function() {
            isOn = !isOn;

            if (isOn) {
                $(this).removeClass("bg-gray-300").addClass("bg-on");
                $("#knob").addClass("knob-on");
            } else {
                $(this).removeClass("bg-on").addClass("bg-gray-300");
                $("#knob").removeClass("knob-on");
            }
        });

        // ---modal-ezerv-sans-info------------------------

        $('body').on('click', ".openModalInfo", function() {
            $("#modalOverlayInfo").show();
        });

        $('body').on('click', "#modalOverlayInfo", function(e) {
            if (!$(e.target).closest("#modalContentInfo").length) {
                $("#modalOverlayInfo").hide();
            }
        });

        // EmblaCarousel code removed - not used in this page

        $('body').on('click', ".toggle-btn", function() {
            let isOpen = $(this).text().trim() === "باز";

            if (isOpen) {
                $(this)
                    .text("بسته")
                    .removeClass("bg-[#04B968] text-white")
                    .addClass("bg-[#DBE2EA] text-black");
            } else {
                $(this)
                    .text("باز")
                    .removeClass("bg-[#DBE2EA] text-black")
                    .addClass("bg-[#04B968] text-white");
            }
        });
    });
</script>
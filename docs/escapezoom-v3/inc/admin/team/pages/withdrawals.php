<style>
    #settlementTableBody>div:nth-child(odd) {
        background-color: #f9f9f9;
    }

    #settlementTableBody>div:nth-child(even) {
        background-color: #ffffff;
    }
</style>
<div class="flex justify-between items-center flex-wrap gap-8">
    <div class="flex justify-center items-center gap-d50 flex-wrap">
        <h1 class="text-base font-extrabold lg:text-2xl">تسویه حساب ها</h1>
        <!-- فیلتر نوع کاربر -->
        <div class="flex gap-2" id="filterTabsType">
            <div class="taborder text-sm font-yekan-bold p-2 text-center w-d80 bg-white border border-slate-105  text-grayy rounded-xl cursor-pointer active" data-type="compiler"> مجموعه دار </div>
            <div class="taborder text-sm font-yekan-bold text-center text-grayy w-d93 p-2 bg-white border border-slate-105 rounded-xl cursor-pointer" data-type="customer"> پلیر </div>
        </div>
    </div>
    <!-- فیلتر وضعیت -->
    <div class="flex gap-9 pt-2 flex-wrap" id="filterTabsStatus">
        <div class="tab text-sm text-grayy cursor-pointer active" data-status="pending"> در انتظار پرداخت </div>
        <div class="tab text-sm font-yekan-bold text-grayy cursor-pointer" data-status="paid"> پرداخت شده ها </div>
        <div class="tab text-sm font-yekan-bold text-grayy cursor-pointer" data-status="rejected"> رد شده ها </div>
    </div>
    <!-- جست‌وجو -->
    <div id="withdrawal_search" class="relative w-d304 h-d58" style="z-index: 99">
        <input type="text" id="searchInput" class="w-d304 h-d58 border border-slate-105 bg-white rounded-xl outline-none px-6 py-5 text-xs font-yekan-bold text-navyBlue" placeholder="جست و جو" />
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none" class="absolute top-5 left-6" style="cursor: pointer;">
            <path d="M15.2149 14.2756L17.8133 16.8727C17.9344 16.9981 18.0015 17.1661 18 17.3406C17.9985 17.515 17.9285 17.6818 17.8052 17.8052C17.6818 17.9285 17.515 17.9985 17.3406 18C17.1661 18.0015 16.9981 17.9344 16.8727 17.8133L14.2743 15.2149C12.5764 16.6697 10.381 17.4102 8.14876 17.2812C5.91656 17.1522 3.82111 16.1636 2.30209 14.5229C0.78307 12.8822 -0.0414283 10.7169 0.00160352 8.48138C0.0446353 6.24587 0.951852 4.11392 2.53289 2.53289C4.11392 0.951852 6.24587 0.0446353 8.48138 0.00160352C10.7169 -0.0414283 12.8822 0.78307 14.5229 2.30209C16.1636 3.82111 17.1522 5.91656 17.2812 8.14876C17.4102 10.381 16.6697 12.5764 15.2149 14.2743V14.2756ZM8.64792 15.9653C10.5886 15.9653 12.4498 15.1944 13.8221 13.8221C15.1944 12.4498 15.9653 10.5886 15.9653 8.64792C15.9653 6.70723 15.1944 4.84602 13.8221 3.47375C12.4498 2.10148 10.5886 1.33054 8.64792 1.33054C6.70723 1.33054 4.84602 2.10148 3.47375 3.47375C2.10148 4.84602 1.33054 6.70723 1.33054 8.64792C1.33054 10.5886 2.10148 12.4498 3.47375 13.8221C4.84602 15.1944 6.70723 15.9653 8.64792 15.9653Z" fill="#09192D" />
        </svg>
    </div>
</div>

<div class="relative" id="data-list">
    <?php for ($i = 0; $i < 10; $i++) { ?>
        <div class="w-full h-12 rounded-xl mb-2 skeleton mt-7"></div>
    <?php } ?>
</div>

<script>
    jQuery(document).ready(function($) {

        const get_data = (user_type, status, page) => {
            $.ajax({
                type: 'POST',
                url: "<?php echo admin_url('admin-ajax.php') ?>",
                data: {
                    'action': 'team_ajax_handler',
                    'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                    'callback': 'withdrawals_get',
                    'user_type': user_type,
                    'status': status,
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

        get_data('compiler', 'pending', 1);

        $("body").on('click', "#filterTabsType div", function(e) {
            $('#filterTabsType div').removeClass('active');
            $(this).addClass('active');

            get_data($(this).data('type'), $("#filterTabsStatus div.active").data('status'), 1);
        });

        $("body").on('click', "#filterTabsStatus div", function(e) {
            $('#filterTabsStatus div').removeClass('active');
            $(this).addClass('active');

            get_data($("#filterTabsType div.active").data('type'), $(this).data('status'), 1);
        });

        $("body").on('click', ".pagination a", function(e) {
            e.preventDefault();

            const page = $(this).attr('href').split('?page=')[1];

            get_data($("#filterTabsType div.active").data('type'), $("#filterTabsStatus div.active").data('status'), page);
        });

        $("body").on('click', ".transaction_function [type=button]", function(e) {

            if ($(this).val() != "-1")
                if (!confirm($(this).attr('title') + ' شود؟'))
                    return false;

            $.ajax({
                type: 'POST',
                url: "<?php echo admin_url('admin-ajax.php') ?>",
                dataType: 'json',
                data: {
                    'action': 'team_ajax_handler',
                    'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                    'callback': 'withdrawals_operations',
                    'op_type': $(this).data('op_type'),
                    'trans_id': $(this).data('trans_id'),
                    'user_id': $(this).data('user_id'),
                    'role': $(this).data('role'),
                    'for': $(this).data('for'),
                },
                success: function(data) {
                    if (data.success && data.data === true)
                        get_data($("#filterTabsType div.active").data('type'), $("#filterTabsStatus div.active").data('status'), 1);
                    else
                        alert(JSON.stringify(data.data));
                },
                error: function(xhr, status, error) {
                    alert('An unexpected error occurred: ' + error);
                }
            });
        });

        $("body").on('click', "#withdrawal_search img", function(e) {

            $.ajax({
                type: 'POST',
                url: "<?php echo admin_url('admin-ajax.php') ?>",
                data: {
                    'action': 'team_ajax_handler',
                    'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                    'callback': 'withdrawals_search',
                    'term': $("#searchInput").val(),
                    'user_type': $("#filterTabsType div.active").data('type'),
                    'status': $("#filterTabsStatus div.active").data('status'),
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

                    if (typeof data === 'object' && data.data.inline) {
                        $("#withdrawal_search_res").html(data.data.html);
                    } else {
                        $("#data-list").html(data);
                    }
                }
            });
        });

        $("body").on('click', "#withdrawal_search_res p", function(e) {

            $("#withdrawal_search_res").html('');

            $.ajax({
                type: 'POST',
                url: "<?php echo admin_url('admin-ajax.php') ?>",
                data: {
                    'action': 'team_ajax_handler',
                    'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                    'callback': 'withdrawals_search',
                    'product_id': $(this).data('product_id'),
                    'term': '',
                    'user_type': $("#filterTabsType div.active").data('type'),
                    'status': $("#filterTabsStatus div.active").data('status'),
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
                }
            });
        });

    })
</script>
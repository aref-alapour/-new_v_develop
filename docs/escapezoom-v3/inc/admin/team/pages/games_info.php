<div class="flex justify-between items-center">
    <h1 class="text-base font-extrabold lg:text-2xl">اطلاعات بازی ها</h1>
    <div class="flex items-center gap-x-4">
        <div class="relative w-d304 h-d58">
            <input class="searchInput w-d304 h-d58 border border-slate-105 bg-white rounded-xl outline-none px-6 py-5 text-xs font-yekan-bold text-navyBlue" placeholder="جست و جو" />
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none" class="absolute top-5 left-6 cursor-pointer">
                <path d="M15.2149 14.2756L17.8133 16.8727C17.9344 16.9981 18.0015 17.1661 18 17.3406C17.9985 17.515 17.9285 17.6818 17.8052 17.8052C17.6818 17.9285 17.515 17.9985 17.3406 18C17.1661 18.0015 16.9981 17.9344 16.8727 17.8133L14.2743 15.2149C12.5764 16.6697 10.381 17.4102 8.14876 17.2812C5.91656 17.1522 3.82111 16.1636 2.30209 14.5229C0.78307 12.8822 -0.0414283 10.7169 0.00160352 8.48138C0.0446353 6.24587 0.951852 4.11392 2.53289 2.53289C4.11392 0.951852 6.24587 0.0446353 8.48138 0.00160352C10.7169 -0.0414283 12.8822 0.78307 14.5229 2.30209C16.1636 3.82111 17.1522 5.91656 17.2812 8.14876C17.4102 10.381 16.6697 12.5764 15.2149 14.2743V14.2756ZM8.64792 15.9653C10.5886 15.9653 12.4498 15.1944 13.8221 13.8221C15.1944 12.4498 15.9653 10.5886 15.9653 8.64792C15.9653 6.70723 15.1944 4.84602 13.8221 3.47375C12.4498 2.10148 10.5886 1.33054 8.64792 1.33054C6.70723 1.33054 4.84602 2.10148 3.47375 3.47375C2.10148 4.84602 1.33054 6.70723 1.33054 8.64792C1.33054 10.5886 2.10148 12.4498 3.47375 13.8221C4.84602 15.1944 6.70723 15.9653 8.64792 15.9653Z" fill="#09192D" />
            </svg>
        </div>
        <div class="relative w-d304 h-d58">
            <input class="searchInput w-d304 h-d58 border border-slate-105 bg-white rounded-xl outline-none px-6 py-5 text-xs font-yekan-bold text-navyBlue" placeholder="جست و جو" />
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none" class="absolute top-5 left-6 cursor-pointer">
                <path d="M15.2149 14.2756L17.8133 16.8727C17.9344 16.9981 18.0015 17.1661 18 17.3406C17.9985 17.515 17.9285 17.6818 17.8052 17.8052C17.6818 17.9285 17.515 17.9985 17.3406 18C17.1661 18.0015 16.9981 17.9344 16.8727 17.8133L14.2743 15.2149C12.5764 16.6697 10.381 17.4102 8.14876 17.2812C5.91656 17.1522 3.82111 16.1636 2.30209 14.5229C0.78307 12.8822 -0.0414283 10.7169 0.00160352 8.48138C0.0446353 6.24587 0.951852 4.11392 2.53289 2.53289C4.11392 0.951852 6.24587 0.0446353 8.48138 0.00160352C10.7169 -0.0414283 12.8822 0.78307 14.5229 2.30209C16.1636 3.82111 17.1522 5.91656 17.2812 8.14876C17.4102 10.381 16.6697 12.5764 15.2149 14.2743V14.2756ZM8.64792 15.9653C10.5886 15.9653 12.4498 15.1944 13.8221 13.8221C15.1944 12.4498 15.9653 10.5886 15.9653 8.64792C15.9653 6.70723 15.1944 4.84602 13.8221 3.47375C12.4498 2.10148 10.5886 1.33054 8.64792 1.33054C6.70723 1.33054 4.84602 2.10148 3.47375 3.47375C2.10148 4.84602 1.33054 6.70723 1.33054 8.64792C1.33054 10.5886 2.10148 12.4498 3.47375 13.8221C4.84602 15.1944 6.70723 15.9653 8.64792 15.9653Z" fill="#09192D" />
            </svg>
        </div>
    </div>
</div>

<div class="relative" id="data-list">
    <?php for ($i = 0; $i < 10; $i++) { ?>
        <div class="w-full h-12 rounded-xl mb-2 skeleton mt-7"></div>
    <?php } ?>
</div>

<style>
    /* استایل‌های جدول بازی‌ها */
    #gamesSection .res_row {
        padding: 11px 34px;
        border-bottom: 1px solid #f0f0f0;
    }

    /* رنگ متناوب ردیف‌ها */
    #gamesSection .res_row:nth-child(odd) {
        background-color: #f9f9f9;
    }

    #gamesSection .res_row:nth-child(even) {
        background-color: #ffffff;
    }

    /* تراز محتوای ستون‌ها - همه وسط چین به جز ستون اول */
    #gamesSection .res_row>*:not(:first-child) {
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
    }

    /* ستون اول راست چین (برای فارسی) */
    #gamesSection .res_row>*:first-child {
        text-align: right;
        display: flex;
        align-items: center;
        justify-content: flex-start;
    }

    /* استایل هدر جدول */
    #gamesSection .grid.gap-4 .grid p {
        font-weight: bold;
        text-align: center;
    }

    /* حذف padding اضافی از px-d34 در res_row چون در CSS اضافه کردیم */
    #gamesSection .res_row.px-\[34px\] {
        padding-left: 34px !important;
        padding-right: 34px !important;
    }
</style>
<script>
    jQuery(document).ready(function($) {

        $.ajax({
            type: 'POST',
            url: "<?php echo admin_url('admin-ajax.php') ?>",
            data: {
                'action': 'team_ajax_handler',
                'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                'callback': 'games_info_get',
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

        $('body').on('input', '.searchInput, .searchInput', function() {

            const search1 = $(".searchInput:eq(0)").val().toLowerCase();
            const search2 = $(".searchInput:eq(1)").val().toLowerCase();

            $('.res_row').each(function() {
                const rowText = $(this).text().toLowerCase();

                const match_search1 = rowText.includes(search1);
                const match_search2 = rowText.includes(search2);

                if (match_search1 && match_search2) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

    });
</script>
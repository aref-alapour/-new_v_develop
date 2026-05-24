<div class="flex justify-between items-center">
    <div class="flex items-center gap-x-8">
        <h1 class="text-base font-extrabold lg:text-2xl"> تاریخچه لغو </h1>
        <div class="flex gap-6" id="filterTabs">
            <div class="tab active text-sm font-bold border-b-2 border-orangee text-navyBlue" data-status="all"> همه </div>
            <div class="tab text-sm font-bold text-grayy" data-status="approved"> تایید و لغو سانس </div>
            <div class="tab text-sm font-bold text-grayy" data-status="pending"> در انتظار بررسی </div>
            <div class="tab text-sm font-bold text-grayy" data-status="rejected"> رد شده ها </div>
            <div class="tab text-sm font-bold text-grayy" data-status="expired"> موعد گذشت </div>
        </div>
    </div>
    <div class="flex items-center justify-between gap-x-25">
        <div id="cancellation_history_search" class="relative w-d304 h-d58">
            <input class="w-d304 h-d58 border border-slate-105 bg-white rounded-xl outline-none px-6 py-5 text-xs font-bold text-navyBlue" placeholder="جست و جو" id="searchInput" />
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none" class="absolute top-5 left-6">
                <path d="M15.2149 14.2756L17.8133 16.8727C17.9344 16.9981 18.0015 17.1661 18 17.3406C17.9985 17.515 17.9285 17.6818 17.8052 17.8052C17.6818 17.9285 17.515 17.9985 17.3406 18C17.1661 18.0015 16.9981 17.9344 16.8727 17.8133L14.2743 15.2149C12.5764 16.6697 10.381 17.4102 8.14876 17.2812C5.91656 17.1522 3.82111 16.1636 2.30209 14.5229C0.78307 12.8822 -0.0414283 10.7169 0.00160352 8.48138C0.0446353 6.24587 0.951852 4.11392 2.53289 2.53289C4.11392 0.951852 6.24587 0.0446353 8.48138 0.00160352C10.7169 -0.0414283 12.8822 0.78307 14.5229 2.30209C16.1636 3.82111 17.1522 5.91656 17.2812 8.14876C17.4102 10.381 16.6697 12.5764 15.2149 14.2743V14.2756ZM8.64792 15.9653C10.5886 15.9653 12.4498 15.1944 13.8221 13.8221C15.1944 12.4498 15.9653 10.5886 15.9653 8.64792C15.9653 6.70723 15.1944 4.84602 13.8221 3.47375C12.4498 2.10148 10.5886 1.33054 8.64792 1.33054C6.70723 1.33054 4.84602 2.10148 3.47375 3.47375C2.10148 4.84602 1.33054 6.70723 1.33054 8.64792C1.33054 10.5886 2.10148 12.4498 3.47375 13.8221C4.84602 15.1944 6.70723 15.9653 8.64792 15.9653Z" fill="#09192D" />
            </svg>
        </div>
        <a href="<?= home_url('/team/cancellation_requests/') ?>" class="font-extrabold text-steel flex items-center gap-x-4">
            بازگشت
            <svg xmlns="http://www.w3.org/2000/svg" width="8" height="14" viewBox="0 0 8 14" fill="none" class="-mt-0.5">
                <path d="M0.509441 7.96925L4.9287 12.6879C5.25867 12.9811 5.69531 13.1409 6.14656 13.1334C6.5978 13.126 7.02839 12.952 7.34753 12.6481C7.66664 12.3442 7.84937 11.9341 7.85716 11.5044C7.86496 11.0746 7.69722 10.6588 7.38931 10.3446L4.20035 6.79759L7.38931 3.65543C7.69721 3.34119 7.86495 2.92536 7.85716 2.49562C7.84936 2.06588 7.66664 1.65582 7.34752 1.3519C7.02839 1.04799 6.5978 0.873974 6.14656 0.86655C5.69531 0.859126 5.25867 1.01887 4.9287 1.3121L0.509441 5.62593C0.183409 5.9368 0.00028014 6.35821 0.000280121 6.79759C0.000280102 7.23696 0.183409 7.65837 0.509441 7.96925Z" fill="#FF6900" />
            </svg>
        </a>
    </div>
</div>

<div class="relative" id="data-list">
    <?php for ($i = 0; $i < 10; $i++) { ?>
        <div class="w-full h-12 rounded-xl mb-2 skeleton mt-7"></div>
    <?php } ?>
</div>

<script>
    jQuery(document).ready(function($) {

        const get_data = (status, page) => {
            $.ajax({
                type: 'POST',
                url: "<?php echo admin_url('admin-ajax.php') ?>",
                data: {
                    'action': 'team_ajax_handler',
                    'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                    'callback': 'cancellation_history_get',
                    'status': status,
                    'page': page,
                    'term': $("#cancellation_history_search input").val(),
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

        get_data('all', 1);

        setInterval(() => {
            get_data('all', 1);
        }, 10 * 60 * 1000);

        $("body").on('click', "#filterTabs div", function(e) {
            $('#filterTabsStatus div').removeClass('active');
            $(this).addClass('active');

            get_data($(this).data('status'), 1);
        });

        $("body").on('click', ".pagination a", function(e) {
            e.preventDefault();

            const page = $(this).attr('href').split('?page=')[1];

            get_data($("#filterTabs div.active").data('status'), page);
        });

        function search_in_cancellation_history() {
            get_data($("#filterTabs div.active").data('status'), 1);
        }

        $("body").on("click", "#cancellation_history_search svg", function (e) {
            search_in_cancellation_history();
        });

        $("body").on("keypress", "#cancellation_history_search input", function (e) {
            if (e.which === 13) // کلید Enter
                get_data($("#filterTabs div.active").data('status'), 1);
        });

    });
</script>
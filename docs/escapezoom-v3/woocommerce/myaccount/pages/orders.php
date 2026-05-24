<?php
$statuses = [
	'all'       => 'همه',
	'held'      => 'برگزار شد',
	'reserved'  => 'در راه بازی',
	'cancelled' => 'لغو شد',
];
?>
<div class="lg:col-span-8 2xl:col-span-9">
    <section class="rounded-2xl border border-slate-120 px-8 shadow-12 max-lg:mb-0 max-lg:rounded-none max-lg:px-0 max-lg:shadow-none py-12 max-lg:border-0 max-lg:py-0">
        <div class="md:mb-8 mb-0 lg:mb-8 max-lg:border-b max-lg:border-slate-120 max-lg:pb-6">
            <div class="flex max-lg:flex-col justify-start gap-6 lg:gap-12">
                <div class="items-center gap-6 md:flex">
                    <h2 class="flex items-center gap-4">
                        <span class="text-base font-bold md:text-lg">
                            <span class="text-xl">رزرو های من</span>
                        </span>
                    </h2>
                    <div class="hidden md:block"></div>
                </div>
                <div class="space-x-6 space-x-reverse overflow-hidden max-lg:flex grow max-lg:rounded-10 max-lg:border max-lg:border-slate-105">
					<?php foreach ( $statuses as $status => $label ) { ?>
                        <button type="button"
                                data-status="<?php echo esc_attr( $status ); ?>"
                                class="grow max-lg:p-2.5 <?php echo esc_attr( $status == 'all' ? 'active max-lg:bg-primary-500 max-lg:text-white lg:border-b lg:border-b-primary-500' : 'text-text-3' ); ?>">
							<?php echo esc_html( $label ); ?>
                        </button>
					<?php } ?>
                </div>
            </div>
        </div>

        <div class="relative" id="orders-list">
			<?php for ( $i = 0; $i < 3; $i ++ ) { ?>
                <div class="w-full h-12 rounded-xl mb-2 skeleton"></div>
			<?php } ?>
        </div>

        <script>
            jQuery(document).ready(function ($) {

                const GetOrders = (status, page) => {
                    $.ajax({
                        type: 'POST',
                        url: "<?php echo admin_url( 'admin-ajax.php' ) ?>",
                        data: {
                            'action': 'v2_ajax_handler',
                            'nonce': "<?php echo wp_create_nonce( 'v2-ajax-nonce' ) ?>",
                            'callback': 'panel_orders_get',
                            'status': status,
                            'page': page
                        },
                        beforeSend: function () {
                            $("#orders-list").html(function () {
                                let out = ''
                                for (let i = 0; i < 10; i++) {
                                    out += '<div class="w-full h-12 rounded-xl mb-2 skeleton"></div>'
                                }
                                return out
                            })
                        },
                        success: function (data) {
                            $("#orders-list").html(data)
                        },
                    });
                }

                GetOrders('all', 1)

                $("button[data-status]").on('click', function () {
                    // Remove Active Classes From All Active Buttons
                    $("button[data-status]")
                        .removeClass('active grow max-lg:bg-primary-500 max-lg:text-white lg:border-b lg:border-b-primary-500')
                        .addClass('grow text-text-3')

                    // Add Active Class To Current Button
                    $(this)
                        .removeClass('grow text-text-3')
                        .addClass('active grow max-lg:bg-primary-500 max-lg:text-white lg:border-b lg:border-b-primary-500')

                    GetOrders($(this).data('status'))
                })

                $("body")
                    .on('click', ".pagination a", function (e) {
                        e.preventDefault()

                        const page = $(this).attr('href').split('?page=')[1]
                        const active = $("button[data-status].active").data('status')

                        GetOrders(active, page)
                    })
                    .on('click', ".show-more", function () {
                        $(this).find('span').text($(this).find('span').text() === 'مشاهده جزئیات بیشتر' ? 'مشاهده جزئیات کمتر' : 'مشاهده جزئیات بیشتر')
                        $(this).find('svg').toggleClass('rotate-180')
                        $(this).next().slideToggle(150)
                    })
            })
        </script>

    </section>
</div>

<?php
if ( get_current_user_id() == 3325 ) : ?>

    <script>
        jQuery(document).ready(function($) {
            $('body').on('click', '.my_orders_res_row', function (){
                $.ajax({
                    type: 'POST',
                    url: "<?php echo admin_url('admin-ajax.php') ?>",
                    data: {
                        'action': 'team_ajax_handler',
                        'nonce': "<?php echo wp_create_nonce('team-ajax-nonce') ?>",
                        'callback': 'cancellation_actions',
                        'function': 'create_cancellation_request',
                        'order_id': $(this).data('orderid'),
                        'requester_type': 'customer'
                    },
                    success: function (data) {
                        console.log(data);
                    },
                });
            });
        });
    </script>

<?php
endif; ?>
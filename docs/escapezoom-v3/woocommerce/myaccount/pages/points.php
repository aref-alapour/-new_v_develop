<section class="rounded-2xl border border-slate-120 px-8 shadow-12 max-lg:mb-0 max-lg:rounded-none max-lg:px-0 max-lg:shadow-none py-12 max-lg:border-0 max-lg:py-0">
    <div class="md:mb-8 mb-0 lg:mb-8 max-lg:border-b max-lg:border-slate-120 max-lg:pb-6">
        <div class="flex justify-between">
            <div class="items-center gap-6 md:flex">
                <h2 class="flex items-center gap-4">
                    <span class="text-base font-bold md:text-lg">
                        <span class="text-xl">امتیاز من</span>
                    </span>
                </h2>
                <div class="hidden md:block"></div>
            </div>
            <div class="flex items-center gap-6">
                <div class="hidden md:block"></div>
                <div class="flex items-center gap-1.5 text-2xs lg:gap-3.5 lg:text-xs max-lg:hidden">
                    امتیاز من:
                    <strong class="text-2xl font-bold">
						<?php echo esc_html( get_user_points( get_current_user_id() ) ); ?>
                    </strong>
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 22 22" fill="none">
                        <rect width="22" height="22" rx="11" fill="#FEAE1A" fill-opacity="0.6"/>
                        <rect x="2" y="2" width="18" height="18" rx="9" fill="#FEAE1A"/>
                        <g filter="url(#filter0_d_5769_149)">
                            <path d="M15.5279 17.4163C14.5828 15.6992 13.6382 13.9816 12.6871 12.2537C13.0773 11.8431 13.3637 11.3758 13.5198 10.8258C14.0386 8.9962 12.9551 7.06949 11.1936 6.6935C9.38794 6.30807 7.68648 7.5831 7.46137 9.49047C7.19637 11.7365 9.12895 13.5593 11.2506 13.061C11.3557 13.0363 11.4311 13.0151 11.4985 13.1406C11.8462 13.786 12.2021 14.426 12.5546 15.0687C12.5679 15.093 12.5735 15.1226 12.5902 15.1726C12.5306 15.1762 12.4847 15.1825 12.4388 15.1816C11.6464 15.1717 10.8514 15.2018 10.062 15.1424C7.88802 14.9792 6.00561 13.1829 5.59439 10.9252C5.06569 8.0225 6.80788 5.28894 9.56975 4.68898C12.3719 4.08002 15.1625 6.1655 15.533 9.14866C15.5626 9.38568 15.5806 9.62629 15.581 9.86511C15.5845 12.3162 15.5832 14.7669 15.5827 17.218C15.5827 17.2819 15.5755 17.3462 15.5716 17.41C15.557 17.4123 15.5424 17.4141 15.5279 17.4163Z" fill="white"/>
                            <path d="M10.842 7.38386C10.3472 7.89432 9.88963 8.3652 9.43339 8.83744C9.28589 8.9899 9.13238 9.13697 8.99817 9.30113C8.87425 9.45269 8.77176 9.62404 8.62426 9.83857C8.51191 9.81878 8.32625 9.7855 8.14229 9.75312C8.18303 8.31213 9.45741 7.19946 10.842 7.38386Z" fill="white"/>
                        </g>
                        <defs>
                            <filter id="filter0_d_5769_149" x="3.5" y="3.58301" width="14.083" height="16.833" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                                <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
                                <feOffset dy="1"/>
                                <feGaussianBlur stdDeviation="1"/>
                                <feComposite in2="hardAlpha" operator="out"/>
                                <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0"/>
                                <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_5769_149"/>
                                <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_5769_149" result="shape"/>
                            </filter>
                        </defs>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="relative" id="points-list"></div>

</section>

<script>
    jQuery(document).ready(function ($) {
        const GetPointsList = page => {
            $.ajax({
                type: 'POST',
                url: "<?php echo admin_url( 'admin-ajax.php' ) ?>",
                data: {
                    'action': 'v2_ajax_handler',
                    'nonce': "<?php echo wp_create_nonce( 'v2-ajax-nonce' ) ?>",
                    'callback': 'panel_points_get',
                    'page': page
                },
                beforeSend: function () {
                    $("#points-list").html(function () {
                        let out = ''
                        for (let i = 0; i < 10; i++) out += '<div class="w-full h-12 rounded-xl mb-2 skeleton"></div>'
                        return out
                    })
                },
                success: function (data) {
                    $("#points-list").html(data)
                },
            });
        }

        GetPointsList(1)

        $("body")
            .on('click', ".pagination a", function (e) {
                e.preventDefault()

                const page = $(this).attr('href').split('?page=')[1]

                $.ajax({
                    type: 'POST',
                    url: "<?php echo admin_url( 'admin-ajax.php' ) ?>",
                    data: {
                        'action': 'v2_ajax_handler',
                        'nonce': "<?php echo wp_create_nonce( 'v2-ajax-nonce' ) ?>",
                        'callback': 'panel_points_get',
                        'page': page
                    },
                    beforeSend: function () {
                        $("#points-list").html(function () {
                            let out = ''
                            for (let i = 0; i < 10; i++) out += '<div class="w-full h-12 rounded-xl mb-2 skeleton"></div>'
                            return out
                        })
                    },
                    success: function (data) {
                        $("#points-list").html(data)
                    },
                })
            })
            .on('click', ".more-button", function () {
                $(".tooltip").addClass('hidden')
                $(this).next().removeClass('hidden');
            })

        $(document).on('click', function (e) {
            if (!$(e.target).closest('.more-button').length) {
                $('.tooltip').addClass('hidden');
            }
        });
    })
</script>
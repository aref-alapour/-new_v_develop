<div class="lg:col-span-8 2xl:col-span-9">
    <section class="tab border-edge lg:h-full lg:rounded-3xl lg:border lg:p-10">
        <div class="flex max-lg:flex-col max-lg:gap-y-6 lg:items-center">

            <div class="text-lg max-lg:hidden lg:ml-8">دعوت‌ها</div>

            <div class="space-x-6 space-x-reverse overflow-hidden max-lg:grid max-lg:h-12.5 max-lg:grid-cols-2 max-lg:rounded-10 max-lg:border max-lg:border-slate-105">
                <button type="button" data-show-tab="was-invited" class="max-lg:bg-primary-500 max-lg:text-white lg:border-b lg:border-b-primary-500">
                    دعوت شدم
                </button>
                <button type="button" data-show-tab="invited" class="text-text-3">دعوت کردم</button>
            </div>

            <div class="flex gap-x-2.5 rounded-lg bg-warn-surface px-3 py-2 lg:mr-auto lg:items-center">
                <svg class="shrink-0 max-lg:mt-1" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
                    <mask id="mask0_7572_2461" maskUnits="userSpaceOnUse" x="0" y="0" width="14" height="14">
                        <path d="M6.99999 13C7.78806 13.001 8.56856 12.8462 9.29664 12.5446C10.0247 12.243 10.686 11.8005 11.2426 11.2426C11.8005 10.686 12.243 10.0247 12.5446 9.29664C12.8462 8.56856 13.001 7.78806 13 6.99999C13.001 6.21192 12.8462 5.43142 12.5446 4.70334C12.243 3.97526 11.8005 3.31395 11.2426 2.7574C10.686 2.19945 10.0247 1.75697 9.29664 1.45538C8.56856 1.15379 7.78806 0.999033 6.99999 1C6.21192 0.999033 5.43142 1.15379 4.70334 1.45538C3.97526 1.75697 3.31395 2.19945 2.7574 2.7574C2.19945 3.31395 1.75697 3.97526 1.45538 4.70334C1.15379 5.43142 0.999033 6.21192 1 6.99999C0.999033 7.78806 1.15379 8.56856 1.45538 9.29664C1.75697 10.0247 2.19945 10.686 2.7574 11.2426C3.31395 11.8005 3.97526 12.243 4.70334 12.5446C5.43142 12.8462 6.21192 13.001 6.99999 13Z" fill="white" stroke="white" stroke-linejoin="round"></path>
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M7 3.09961C7.19891 3.09961 7.38968 3.17863 7.53033 3.31928C7.67098 3.45993 7.75 3.6507 7.75 3.84961C7.75 4.04852 7.67098 4.23928 7.53033 4.37994C7.38968 4.52059 7.19891 4.59961 7 4.59961C6.80109 4.59961 6.61032 4.52059 6.46967 4.37994C6.32902 4.23928 6.25 4.04852 6.25 3.84961C6.25 3.6507 6.32902 3.45993 6.46967 3.31928C6.61032 3.17863 6.80109 3.09961 7 3.09961Z" fill="black"></path>
                        <path d="M7.14961 9.9998V5.7998H6.54961M6.09961 9.9998H8.19961" stroke="black" stroke-linecap="round" stroke-linejoin="round"></path>
                    </mask>
                    <g mask="url(#mask0_7572_2461)">
                        <path d="M-0.200195 -0.200195H14.1998V14.1998H-0.200195V-0.200195Z" fill="#BF9A00"></path>
                    </g>
                </svg>
                <p class="text-md font-bold text-yellow-900">پس‌از پذیرفتن دعوت، شماره تماس فرد دعوت‌شده، جهت ایجاد هماهنگی برای فرد دعوت‌کننده نمایش داده می‌شود.</p>
            </div>

        </div>

        <hr class="my-8 max-lg:hidden">

        <div id="results" class="mt-8 lg:px-17"></div>

    </section>

</div>

<script>
    jQuery(document).ready(function ($) {
        let tabs = $("[data-show-tab]"),
            first = $(tabs[0]).data('show-tab')

        const GetInvitations = status => {
            $.ajax({
                url: "<?php echo admin_url( 'admin-ajax.php' ) ?>",
                type: 'POST',
                data: {
                    'action': 'v2_ajax_handler',
                    'nonce': "<?php echo wp_create_nonce( 'v2-ajax-nonce' ) ?>",
                    'callback': 'panel_invitation_get_invited',
                    'page': 1,
                    'status': status
                },
                beforeSend: function () {
                    $("#results").html(() => {
                        let out = ''
                        for (let i = 0; i < 3; i++) out += '<div class="w-full h-44 rounded-xl mb-8 skeleton"></div>'

                        return out
                    })
                },
                success: function (response) {
                    $("#results").html(response)
                }
            })
        }

        GetInvitations(first)

        tabs.on('click', function () {
            let $this = $(this)

            tabs
                .removeClass('max-lg:bg-primary-500 max-lg:text-white lg:border-b lg:border-b-primary-500')
                .addClass('text-text-3')

            $this
                .removeClass('text-text-3')
                .addClass('max-lg:bg-primary-500 max-lg:text-white lg:border-b lg:border-b-primary-500')


            GetInvitations($this.data('show-tab'))
        })
    })
</script>
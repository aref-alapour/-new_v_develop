<?php get_header(); ?>

    <section class="container mx-auto px-4 sm:px-6 md:px-8">

        <div class="flex flex-row flex-wrap justify-start items-center my-d30 lg:my-d53 gap-d28">
            <div class="flex items-center gap-d14">
                <svg width="42" height="43" viewBox="0 0 42 43" fill="none" xmlns="http://www.w3.org/2000/svg" class="m-0">
                    <g id="Horror rooms icon" filter="url(#filter0_d_13940_4169)">
                        <rect id="Rectangle 20" x="4" y="0.5" width="34" height="34" rx="12" fill="#FD7013"/>
                        <g id="Group 100">
                            <circle id="Ellipse 40" cx="20.0314" cy="11.0803" r="5.08026" fill="white"/>
                            <ellipse id="Ellipse 41" cx="20.0316" cy="22.3732" rx="9.03158" ry="3.95131" fill="white"/>
                        </g>
                        <path id="Vector" d="M26.9267 20.9284L26.9269 20.9279C27.6078 19.6916 29.391 19.69 30.0724 20.9275L26.9267 20.9284ZM26.9267 20.9284C26.5956 21.5303 26.3097 22.1556 26.0711 22.7992M26.9267 20.9284L26.0711 22.7992M26.0711 22.7992C25.5395 22.8338 25.0101 22.897 24.4851 22.9886L26.0711 22.7992ZM23.7282 26.1788L23.7274 26.178C23.6634 26.1172 23.6 26.056 23.5372 25.9945C22.4347 24.9154 23.138 23.2236 24.4849 22.9887L23.7282 26.1788ZM23.7282 26.1788C24.0603 26.4934 24.4059 26.7934 24.7639 27.0778M23.7282 26.1788L24.7639 27.0778M24.7639 27.0778C24.586 27.7067 24.4532 28.3476 24.3665 28.9959L24.3664 28.9963C24.2672 29.7401 24.6259 30.3715 25.1565 30.7121C25.6674 31.04 26.3561 31.1128 26.9651 30.8003C26.9652 30.8002 26.9654 30.8001 26.9655 30.8001L26.5088 29.9105L24.7639 27.0778ZM32.6335 28.996L32.6336 28.9963C32.7328 29.7401 32.3741 30.3715 31.8435 30.7121C31.3328 31.0399 30.6445 31.1127 30.0357 30.8007L32.6335 28.996ZM32.6335 28.996C32.5469 28.3478 32.4142 27.7067 32.2365 27.0779M32.6335 28.996L32.2365 27.0779M30.9293 22.7992C30.6908 22.155 30.4042 21.5294 30.0725 20.9278L30.9293 22.7992ZM30.9293 22.7992C31.4607 22.8338 31.99 22.897 32.5149 22.9886L30.9293 22.7992ZM32.2365 27.0779C32.6635 26.7386 33.0728 26.3772 33.4625 25.9953L32.2365 27.0779ZM28.4997 29.8901C28.9921 30.225 29.505 30.5291 30.0355 30.8006L28.4997 29.8901ZM33.4629 25.9949C34.5646 24.9159 33.8628 23.2238 32.5151 22.9887L33.4629 25.9949Z" fill="white" stroke="#FD7013" stroke-width="2"/>
                    </g>
                    <defs>
                        <filter id="filter0_d_13940_4169" x="0" y="0.5" width="42" height="42" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                            <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                            <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
                            <feOffset dy="4"/>
                            <feGaussianBlur stdDeviation="2"/>
                            <feComposite in2="hardAlpha" operator="out"/>
                            <feColorMatrix type="matrix" values="0 0 0 0 0.992157 0 0 0 0 0.439216 0 0 0 0 0.0745098 0 0 0 0.25 0"/>
                            <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_13940_4169"/>
                            <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_13940_4169" result="shape"/>
                        </filter>
                    </defs>
                </svg>

                <h1 class="text-xl font-bold">کاربران
                    <span class="font-black">فعال و برتر</span>
                </h1>
            </div>

            <div class="flex flex-row gap-2">
                <button type="button" data-time="1-week" class="time-option rounded-12 p-d10 text-xs font-bold bg-primaryColor text-white active" style="border: 1px solid #E4EBF0;">
                    در یک هفته
                </button>
                <button type="button" data-time="1-month" class="time-option rounded-12 p-d10 text-xs font-bold text-text-3" style="border: 1px solid #E4EBF0;">
                    در یک ماه
                </button>
                <button type="button" data-time="3-month" class="time-option rounded-12 p-d10 text-xs font-bold text-text-3" style="border: 1px solid #E4EBF0;">
                    در سه ماه
                </button>
                <button type="button" data-time="1-year" class="time-option rounded-12 p-d10 text-xs font-bold text-text-3" style="border: 1px solid #E4EBF0;">
                    در یک سال
                </button>
            </div>

        </div>
    </section>

    <section class="container mx-auto lg:mb-d70 lg:px-8">
        <div class="flex flex-col gap-d14 lg:rounded-20 bg-edge h-full w-full px-d30 lg:px-d100 py-10 rounded-xl mb-8 results">

        </div>
    </section>

    <script>
        jQuery(document).ready(function ($) {

            const BuildUsersTable = (period = '1-week') => {
                $.ajax({
                    url: "<?php echo admin_url( 'admin-ajax.php' ) ?>",
                    type: "POST",
                    data: {
                        'action': 'v2_ajax_handler',
                        'nonce': "<?php echo wp_create_nonce( 'v2-ajax-nonce' ) ?>",
                        'callback': 'top_users_get_list',
                        'period': period
                    },
                    beforeSend: function () {
                        let out = ''
                        for (let i = 0; i < 15; i++) {
                            out += "<div class='skeleton w-full h-20 rounded-xl'></div>"
                        }

                        $(".results").html(out)
                    },
                    success: function (response) {
                        $(".results").html(response)
                        
                        $(".help").each((index, item) => {
                            let content = $(item).data('help')
                            tippy(item, {
                                content: content,
                                animation: 'perspective-extreme',
                            });
                        })
                    }
                })
            }

            BuildUsersTable()

            $("body").on('click', '.time-option', function () {
                $(".time-option").removeClass('bg-primaryColor text-white active').addClass('text-text-3')
                $(this).addClass('bg-primaryColor text-white active').removeClass('text-text-3')

                let time = $(this).data('time')
                BuildUsersTable(time)
            })
        })
    </script>

<?php get_footer();
<?php get_header();?>

<div class="woocommerce">
    <div class="woocommerce-message woocommerce-error" style="text-align:center; padding:40px;">
        <?php
        $order_id = isset($_GET['order']) ? intval($_GET['order']) : 0;
        if ($order_id) {
            $order = wc_get_order($order_id);
            if ($order) {

                $sans_time = get_post_meta($order_id, 'sans_time', true);

                foreach ($order->get_items() as $item) {
                    $product_id = $item->get_product_id();
                    $quantity   = $item->get_quantity();
                }

                $reorder_url = home_url("checkout/?add-to-cart=$product_id&book=$sans_time&quantity=$quantity");
                ?>

                <section
                        class="mt-5 rounded-xl border border-solid border-gray-200 px-6 pb-9 pt-7 shadow-card-lip lg:p-13 bg-breserve">
                    <div class="flex flex-col justify-center items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="258" height="259" viewBox="0 0 258 259" fill="none">
                            <g filter="url(#filter0_d_31802_12693)">
                                <circle cx="121" cy="111.648" r="105" fill="url(#paint0_linear_31802_12693)"></circle>
                            </g>
                            <path
                                    d="M79.999 88.149C74.9222 83.0722 74.9222 74.8411 79.999 69.7642C85.0758 64.6874 97.7086 70.3798 102.785 75.4566L160.499 130.649C165.576 134.726 170.1 147.149 163.597 153.653C158.52 158.729 150.289 158.729 145.212 153.653L79.999 88.149Z"
                                    fill="#D11038"></path>
                            <path
                                    d="M161.499 95.149C168.999 86.149 163.999 71.649 158.499 68.149C153.422 63.0722 150.08 70.3798 145.003 75.4566L84.1916 136.268C79.1148 141.345 71.4999 139.649 84.1929 153.149C88.9999 157.649 97.5 159.149 102.576 154.653L161.499 95.149Z"
                                    fill="#D11038"></path>
                            <g filter="url(#filter1_i_31802_12693)">
                                <path
                                        d="M140.619 68.456C145.696 63.3793 153.927 63.3792 159.004 68.456C164.08 73.5327 164.08 81.764 159.004 86.8407L138.2 107.643C137.81 108.034 137.81 108.667 138.2 109.058L159.004 129.861C164.08 134.938 164.08 143.169 159.004 148.246C153.927 153.323 145.696 153.323 140.619 148.246L119.815 127.442C119.425 127.052 118.792 127.052 118.401 127.442L98.1923 147.652C93.1155 152.729 84.8843 152.729 79.8075 147.652C74.7308 142.575 74.7309 134.344 79.8075 129.267L100.016 109.058C100.407 108.667 100.407 108.034 100.016 107.643L79.8075 87.4355C74.7308 82.3587 74.7309 74.1275 79.8075 69.0507C84.8843 63.9739 93.1155 63.9739 98.1923 69.0507L118.401 89.2587C118.792 89.6492 119.425 89.6492 119.815 89.2587L140.619 68.456Z"
                                        fill="url(#paint1_linear_31802_12693)"></path>
                            </g>
                            <defs>
                                <filter id="filter0_d_31802_12693" x="0" y="0.648438" width="258" height="258"
                                        filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                    <feFlood flood-opacity="0" result="BackgroundImageFix"></feFlood>
                                    <feColorMatrix in="SourceAlpha" type="matrix"
                                                   values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha">
                                    </feColorMatrix>
                                    <feOffset dx="8" dy="18"></feOffset>
                                    <feGaussianBlur stdDeviation="12"></feGaussianBlur>
                                    <feComposite in2="hardAlpha" operator="out"></feComposite>
                                    <feColorMatrix type="matrix"
                                                   values="0 0 0 0 0.306354 0 0 0 0 0.36728 0 0 0 0 0.425 0 0 0 0.08 0">
                                    </feColorMatrix>
                                    <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_31802_12693">
                                    </feBlend>
                                    <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_31802_12693"
                                             result="shape"></feBlend>
                                </filter>
                                <filter id="filter1_i_31802_12693" x="76" y="61.6484" width="86.8115" height="90.4043"
                                        filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                    <feFlood flood-opacity="0" result="BackgroundImageFix"></feFlood>
                                    <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape">
                                    </feBlend>
                                    <feColorMatrix in="SourceAlpha" type="matrix"
                                                   values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha">
                                    </feColorMatrix>
                                    <feOffset dy="-3"></feOffset>
                                    <feGaussianBlur stdDeviation="1.5"></feGaussianBlur>
                                    <feComposite in2="hardAlpha" operator="arithmetic" k2="-1" k3="1"></feComposite>
                                    <feColorMatrix type="matrix"
                                                   values="0 0 0 0 0.819608 0 0 0 0 0.0627451 0 0 0 0 0.219608 0 0 0 1 0">
                                    </feColorMatrix>
                                    <feBlend mode="normal" in2="shape" result="effect1_innerShadow_31802_12693"></feBlend>
                                </filter>
                                <linearGradient id="paint0_linear_31802_12693" x1="88.1044" y1="47.5187" x2="-69.0633"
                                                y2="-181.421" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="white"></stop>
                                    <stop offset="1" stop-color="#889BAD"></stop>
                                </linearGradient>
                                <linearGradient id="paint1_linear_31802_12693" x1="231.827" y1="63.3349" x2="135.119"
                                                y2="61.0114" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#F21543"></stop>
                                    <stop offset="1" stop-color="#FD2F5A"></stop>
                                </linearGradient>
                            </defs>
                        </svg>
                        <div class="text-bold-h5 font-bold">رزرو شما متاسفانه <span class="text-txError">ناموفق</span> بود!
                        </div>
                        <div
                                class="text-text-bold-s3 font-bold flex justify-center items-center border border-solid border-gray-200 sahdow-1 lg:w-d556 w-d326 h-d86 rounded-xl shadow-1 mt-6">
                            کد خطای ۹۰:&nbsp;ارتباط با مرکز قطع شده است.</div>
                        <div class="flex flex-col lg:flex-row gap-5 mt-9">
                            <a href="<?php echo $reorder_url ?>" class="bg-bgPrimary shadow-btn-org w-d326 lg:w-d268 h-d56 flex justify-center items-center gap-11 text-white rounded-lg">
                                اقدام مجدد <svg xmlns="http://www.w3.org/2000/svg" width="24" height="25" viewBox="0 0 24 25" fill="none">
                                    <path d="M11.8522 21.5373C11.8522 21.832 11.7401 22.1146 11.5404 22.323C11.3408 22.5314 11.07 22.6484 10.7877 22.6484C5.40561 22.6484 1 18.1966 1 12.6484C1 7.10029 5.40561 2.64844 10.7877 2.64844C15.1947 2.64844 18.9475 5.63362 20.1624 9.76103L21.0183 8.24992C21.1619 7.9961 21.3962 7.81222 21.6697 7.73873C21.9432 7.66524 22.2335 7.70817 22.4766 7.85807C22.7198 8.00796 22.896 8.25255 22.9664 8.53802C23.0368 8.8235 22.9957 9.12647 22.852 9.38029L20.5385 13.4618C20.3969 13.7121 20.1668 13.8946 19.8977 13.9699C19.6287 14.0453 19.342 14.0075 19.0993 13.8647L15.094 11.5077C14.9722 11.436 14.8651 11.34 14.7788 11.2251C14.6926 11.1102 14.6289 10.9787 14.5913 10.8381C14.5537 10.6975 14.5431 10.5506 14.5599 10.4057C14.5768 10.2608 14.6208 10.1208 14.6895 9.99362C14.7581 9.86648 14.8502 9.75471 14.9602 9.66468C15.0703 9.57466 15.1963 9.50814 15.331 9.46894C15.4657 9.42973 15.6064 9.4186 15.7453 9.43618C15.8841 9.45376 16.0182 9.49971 16.14 9.5714L18.2293 10.801C17.4146 7.4114 14.4099 4.87066 10.7891 4.87066C6.53398 4.87066 3.129 8.37733 3.129 12.6484C3.129 16.9195 6.53398 20.4262 10.7877 20.4262C11.07 20.4262 11.3408 20.5433 11.5404 20.7516C11.7401 20.96 11.8522 21.2426 11.8522 21.5373Z" fill="white"></path>
                                </svg>
                            </a>
                            <button
                                    class="bg-white shadow-1 w-d326 lg:w-d268 h-d56 flex justify-center items-center gap-11 rounded-lg">
                                تماس با پشتیبانی <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 25 25" fill="none">
                                    <path
                                            d="M16.1002 15.1707C13.7052 17.6907 7.5964 11.6366 10.0003 9.10661C11.4683 7.56159 9.81034 5.79657 8.89237 4.49756C7.16942 2.06254 3.38852 5.42457 3.50252 7.56359C3.86551 14.3097 11.1623 22.3037 18.2281 21.6057C20.438 21.3877 22.978 17.3957 20.442 15.9367C19.1751 15.2067 17.4341 13.7667 16.1002 15.1697M14.5002 3.64855C16.3567 3.64855 18.1371 4.38606 19.4498 5.69882C20.7625 7.01159 21.5 8.79209 21.5 10.6486M14.5002 7.64859C15.2958 7.64859 16.0589 7.96466 16.6215 8.52728C17.1841 9.08989 17.5001 9.85296 17.5001 10.6486"
                                            stroke="#FD7013" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    </path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </section>

            <?php
            } else {
                echo '<h2>سفارش مورد نظر یافت نشد.</h2>';
            }
        } else {
            echo '<h2>شناسه سفارش مشخص نیست.</h2>';
        }
        ?>
    </div>
</div>

<?php get_footer(); ?>

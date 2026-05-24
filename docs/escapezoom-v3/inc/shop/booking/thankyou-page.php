<?php
/** lines 3952-4010 → shop/booking/thankyou-page.php */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( isset($_GET['utm_source']) && $_GET['utm_source'] == 'co' and 0 ) {
    session_start();
    $_SESSION["co_referrer"] = true;
}
/****************************************************************************************************************************************/
add_shortcode('thankyouco', 'thankyouco');
function thankyouco () { ?>

    <div class="woocommerce-order">
        <section class="woocommerce-checkout-alert">
            <div class="woocommerce-checkout-alert-icon success"><i></i></div>
        </section>

        <section class="woocommerce-checkout-details">
            <div class="woocommerce-checkout-details-title">
                <div class="woocommerce-checkout-details-row">
                    <style> .woocommerce-checkout-details-col-table {
                            display: none;
                        }
                        .coupon-frame {
                            display: flex;
                            justify-content: center;
                            width: 100%;
                            flex-wrap: wrap;
                        }
                        .coupon-code {
                            background-color: #ffffff;
                            width: 150px;
                            margin: 10px;
                            text-align: center;
                            line-height: 40px;
                            font-family: sans-serif;
                        }
                        .coupon-code > img {
                            border-bottom: 3px dashed #eee;
                        }
                        section.woocommerce-customer-details {
                            display: none;
                        }
                        #content > div > div > div > section.woocommerce-checkout-alert > div.woocommerce-checkout-alert-content > p, div#tpbr_topbar {
                            display: none !important;
                        }
                        .coupon-frame, h4.tac {
                            display: none !important;
                        }
                    </style>
                    <h2 class="tac" style=" font-size: 1.3em; ">سانس شما رزرو شد. اوقات خوشی رو برای شما آرزومندیم.<br>

                        نام اتاق فرار رزرو شده بهمراه ساعت و تاریخ آن برای شما پیامک شده است.

                        جهت دریافت آدرس دقیق، می توانید با شماره تلفنی که برای شما پیامک شده است  تماس بگیرید.</h2>

                    </p>
                </div>
            </div>
        </section>
    </div>
    <?php
}

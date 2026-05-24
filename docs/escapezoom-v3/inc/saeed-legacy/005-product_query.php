<?php
/**
 * product_query
 *
 * توابع: product_query
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 49-130)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_shortcode('product_query', 'product_query');
function product_query() { ?>

    <form action="https://escapezoom.ir/shop/?s=" method="get" target="_blank" id="search-form">
        <input id="search_top2" name="s" type="search" placeholder=" جستجو سرگرمی..." autocomplete="off" autofocus >
        <input type="hidden" id="post_type" name="post_type" value="product">
    </form>
    <p id="search_result2" style="display:none"></p>

    <script>
        var $ = jQuery;
        jQuery(document).ready(function () {

            $("#search-form").on("submit", function(e){
                e.preventDefault()
            })

            jQuery('body').on('keyup', '#search_top2', function (){
                var $this = jQuery(this);

                if ( jQuery("#search_top2").val() == 'null' || jQuery("#search_top2").val() == '' || jQuery("#search_top2").val() == '0' ) {
                    jQuery("#search_result2").hide();
                    return;
                }

                $.ajax({
                    type: 'POST',
                    url: 'https://escapezoom.ir/web-service/queryable.php',
                    data: {
                        'term'  : $this.val(),
                        'url'   : '<?php echo $_SERVER['HTTP_HOST'] ?>'
                    },
                    dataType: "json",
                    success: function (data) {

                        if ( data === '' ) {
                            jQuery("#search_result2").hide();
                            return;
                        }

                        jQuery("#search_result2").html(data);
                        jQuery("#search_result2").show();
                    }
                });
            });
        });
    </script>
    <style>
        p#search_result2 {
            margin-top: -17px !important;
        }

        p#search_result2 {
            width: 100%;
            background: #fff;
            padding: 5px;
            margin-top: -3px;
            padding-right: 12px;
            z-index: 9999!important;
            position: relative;
            box-shadow: -5px 15px 29px -3px;
            overflow: scroll;
            height: 350px;
        }
        input#search_top2 {
            width: 100%;
            margin-top: 13px;
            height: 36px;
            border: none!important;
            outline: none!important;
            border-radius: 4px;
            padding: 10px;
            text-align: right;
            direction: rtl;
        }
        #search_result2>p {
            margin: 8px 0;
            padding-bottom: 2px;
        }
    </style>
    <?php
}

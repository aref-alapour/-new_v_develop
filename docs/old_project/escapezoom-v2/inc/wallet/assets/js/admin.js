jQuery(document).ready(function ($) {

var ajax_url = tav_wallet_js_var.ajax_url;
var currentRequest = null;
/********************************************************************************************************************/
$('body').on('keyup', '#tav_wallet_admin_main_search', function () {

    var $this = $(this);

    if ( $this.val().length >= 3 ) {

        $('#tav_wallet_admin_main_search_spinner').show();

        currentRequest = $.ajax({
            type    : 'POST',
            url     : ajax_url,
            data    : {
                'action'                : 'ez_wallet_admin_ajax_handler',
                '_search_by_number_'    : true,
                'number'                : $this.val(),
            },
            beforeSend : function()    {
                if(currentRequest != null) {
                    currentRequest.abort();
                }
            },
            dataType: "json",
            success: function(res) {
                $('#tav_wallet_admin_main_search_spinner').hide();
                $('#tav_wallet_admin_main_search_res_wrapper').empty().slideDown();

                if ( res.success ) {
                    $.each(res.data, (index, item) => {
                        var id = item.id;
                        var phone = item.phone;
                        var name = item.name;

                        var row = $('<a href="' + tav_wallet_js_var.current_url + '&add_charge=true&id=' + id + '" class="tav-search-res"><span class="tav-search-res-name">' + name + '</span><span class="tav-search-res-phone">' + phone + '</span></a>');

                        row.appendTo('#tav_wallet_admin_main_search_res_wrapper');
                    });

                } else {
                    $('<span id="tav_search_empty_res">' + res.data + '</span>').appendTo('#tav_wallet_admin_main_search_res_wrapper');
                }

            },
        });

    } else {
        $('#tav_wallet_admin_main_search_res_wrapper').empty().slideUp();
    }
});
/********************************************************************************************************************/
$('body').on('keyup', '#tav_wallet_add_charge', function () {
    var $this = $(this);
    var res = parseInt( $this.val().replace(/[^0-9]/g,'') ).toLocaleString();

    if( res == 'NaN' ) {
        $this.val('');
    } else {
        $this.val(res);
    }
});
/********************************************************************************************************************/
$('body').on('submit', '#tav_wallet_admin_user_page_add_charge_wrapper form', function (e) {

    // if ( $('#tav_wallet_add_charge_positive_or_negative').val() == 'n' ) {
    //     var balance = $('#tav_wallet_admin_user_page_details_balance').attr('data');
    //     var amount = $('#tav_wallet_add_charge').val().replace(/[^0-9]/g,'');
    //
    //     if ( parseInt(amount) > parseInt(balance)  ) {
    //         $('#tav_wallet_add_charge').addClass('tav-error-field');
    //         $('.error-feedback').text('مبلغی که قصد کسر آن را دارید از موجودی مشتری بیشتر است!');
    //         return false;
    //
    //     } else {
    //         $('#tav_wallet_add_charge').removeClass('tav-error-field');
    //         $('.error-feedback').text('');
    //     }
    //
    // } else {
    //     $('#tav_wallet_add_charge').removeClass('tav-error-field');
    //     $('.error-feedback').text('');
    // }

});

}); // End JQUERY
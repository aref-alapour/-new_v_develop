let now = "";
jQuery(function(){
    let $ = jQuery;
    
    // Initialize display cards function
    function initDisplayCards() {
        var selectedValue = $('#type').val();
        $('.display-card').removeClass('active');
        $('.display-card[data-value="' + selectedValue + '"]').addClass('active');
        
        // Show/hide options based on the selected type
        if(selectedValue == "iframe"){
            $(".dialog.settings .iframe_opts").show();
            $(".dialog.settings .btn_opts").hide();
        } else {
            $(".dialog.settings .iframe_opts").hide();
            $(".dialog.settings .btn_opts").show();
        }
    }
    
    // Handle dimension inputs and units
    function initDimensionInputs() {
        // Parse width value
        var widthValue = $('#ifm_width').val();
        if (widthValue === 'auto') {
            $('#ifm_width_value').val('').prop('disabled', true);
            $('#ifm_width_unit').val('auto');
        } else if (widthValue.indexOf('%') > -1) {
            $('#ifm_width_value').val(widthValue.replace('%', ''));
            $('#ifm_width_unit').val('%');
        } else if (widthValue.indexOf('px') > -1) {
            $('#ifm_width_value').val(widthValue.replace('px', ''));
            $('#ifm_width_unit').val('px');
        }
        
        // Parse height value
        var heightValue = $('#ifm_height').val();
        if (heightValue === 'auto') {
            $('#ifm_height_value').val('').prop('disabled', true);
            $('#ifm_height_unit').val('auto');
        } else if (heightValue.indexOf('%') > -1) {
            $('#ifm_height_value').val(heightValue.replace('%', ''));
            $('#ifm_height_unit').val('%');
        } else if (heightValue.indexOf('px') > -1) {
            $('#ifm_height_value').val(heightValue.replace('px', ''));
            $('#ifm_height_unit').val('px');
        }
    }
    $(document).on("click",'.dialog.settings .btn_opts .icon .dropdown img',function(){
        $('.dialog.settings .btn_opts .icon span.holder').html(`<img src="${$(this).attr("src")}">`);
        $("#icon").val($(this).data("key"));
        $('.dialog.settings .btn_opts .icon .dropdown').hide();
    })
    $(document).on("click",'.dialog.settings .btn_opts .icon span.holder',function(){
        $('.dialog.settings .btn_opts .icon .dropdown').toggle();
    });
    $(document).on("click",'.dialog.settings .removeicon',function(){
        $('.dialog.settings .btn_opts .icon span.holder').html('');
        $("#icon").val("");
    });
    // Update hidden input when dimension value or unit changes
    $(document).on('change keyup', '.dimension-value, .dimension-unit', function() {
        var valueInput = $(this).hasClass('dimension-value') ? 
                        $(this) : 
                        $(this).siblings('.dimension-value');
        
        var unitSelect = $(this).hasClass('dimension-unit') ? 
                        $(this) : 
                        $(this).siblings('.dimension-unit');
        
        var hiddenInput = $(this).siblings('input[type="hidden"]');
        
        if (unitSelect.val() === 'auto') {
            valueInput.val('').prop('disabled', true);
            hiddenInput.val('auto');
        } else {
            valueInput.prop('disabled', false);
            hiddenInput.val(valueInput.val() + unitSelect.val());
        }
    });
    
    // Handle card click
    $(document).on('click', '.display-card', function() {
        var value = $(this).data('value');
        
        // Update the hidden select
        $('#type').val(value);
        
        // Update active state
        $('.display-card').removeClass('active');
        $(this).addClass('active');
        
        // Trigger the change event on the select
        $('#type').trigger('change');
    });
    
    // Make sure this is properly called when the settings dialog is opened
    $(".porsline .content .item .options button.settings").click(function(){
        $(".lighter").addClass("loading").show();
        now = $(this).data("code");
        $("body").css("overflow","hidden");
        // Update the shortcode input value with the correct code
        $(".porsline-shortcode-input").val("[porsline " + now + "]");
        
        $.post(ajaxurl,{action:"prs_load",code:now,nonce:prs_adm_nonce},function(res){
            res = JSON.parse(res);
            for(var key in res){
                $(".dialog.settings").find(`input[name='${key}'] , select[name='${key}']`).val(res[key]);
            }
            $(".colorpicker").each(function () {
                $(this).siblings('.wp-picker-container').remove(); 
            });
            $(".colorpicker").wpColorPicker();
            $(".lighter").removeClass("loading");
            
            // Initialize display cards after loading settings
				initDisplayCards();
            
            // Initialize dimension inputs
            initDimensionInputs();
            if(res['icon'] && res['icon'] != ""){
                $('.dialog.settings .btn_opts .icon span.holder').html(`<img src="${icons_url+res['icon']}.svg">`);
            } else {
                $('.dialog.settings .btn_opts .icon span.holder').html('');
            }
            if(res['type'] == "widget"){
                $(".iconkeeper").show();
            } else {
                $(".iconkeeper").hide();
            }
            $(".dialog.settings").css("display","flex");
        });
    });
    
    // Save button handler - update to include hidden inputs
    $(".dialog.settings button.save").click(function(e){
        e.preventDefault();
        var inps = $(".dialog.settings input:not([type='hidden']):not(.dimension-value):not(.dimension-unit), .dialog.settings select:not(.dimension-unit)");
        var hiddenInputs = $(".dialog.settings input[type='hidden']");
        var send = {"action":"prs_save", "code":now};
        
        for(var i=0; i<inps.length; i++){
            send[inps.eq(i).attr("name")] = inps.eq(i).val();
        }
        
        for(var i=0; i<hiddenInputs.length; i++){
            send[hiddenInputs.eq(i).attr("name")] = hiddenInputs.eq(i).val();
        }
        
        send['nonce'] = prs_adm_nonce;
        $(".dialog.settings").hide();
		$("body").css("overflow","auto");
        $(".lighter").addClass("loading");
        $.post(ajaxurl, send, function(res){
            $(".lighter").hide();
        });
    });
    
    $(".lighter").click(function(e){
		if($(e['target']).parents(".lighter").length == 0){
			if(!$(this).hasClass("loading")){
				$(".lighter , .dialog.settings").hide();
				$("body").css("overflow","auto");
			}
		}
    });
    
    // Initialize on document ready
    $(document).ready(function() {
        initDisplayCards();
    });
    
    // Update the type change handler
    $("#type").change(function(){
        if($(this).val() == "iframe"){
            $(".dialog.settings .iframe_opts").show();
            $(".dialog.settings .btn_opts").hide();
        } else {
            if($(this).val() == "widget"){
                $(".iconkeeper").show();
            } else {
                $(".iconkeeper").hide();
            }
            $(".dialog.settings .iframe_opts").hide();
            $(".dialog.settings .btn_opts").show();
        }
    });
//     $(".porsline .tabs .tab").click(function(){
//         $(".porsline .contents .content").hide();
//         $("#"+$(this).data("tab")).show();
//     });
	$(".porsline .tabs .tab").click(function(){
        $(".porsline .tabs .tab").removeClass("active");
        $(this).addClass("active");
        $(".porsline .contents .content").hide();
        $("#" + $(this).data("tab")).show();
    });
    $(".porsline-copy-btn").click(function(e){
        e.preventDefault();
        navigator.clipboard.writeText($(".porsline-shortcode-input").val());
        $(this).addClass("ok");
        setTimeout(()=>{ $(this).removeClass("ok") },1500);
    })
});
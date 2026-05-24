jQuery(document).ready(function($){
    var mediaUploader;
    
    function open_media_uploader_image(){
        mediaUploader = wp.media({
            title: 'انتخاب تصویر برچسب',
            button: { text: 'انتخاب' },
            multiple: false
        }).on('select', function(){
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#tag-image-id').val(attachment.id);
            $('#tag-image-wrapper').html('<img src="'+attachment.sizes.thumbnail.url+'" style="max-width:100px;">');
        }).open();
    }

    $('#tag_media_button').on('click', function(e){
        e.preventDefault();
        open_media_uploader_image();
    });

    $('#tag_media_remove').on('click', function(){
        $('#tag-image-id').val('');
        $('#tag-image-wrapper').html('');
    });
});

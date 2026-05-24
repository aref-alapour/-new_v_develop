jQuery(document).ready(function ($) {
    if (typeof PostJsObject === 'undefined') {
        return;
    }

    const swiper = new Swiper('.relatedPostSwiper', {
        slidesPerView: 'auto',
        spaceBetween: 30,
        freeMode: true
    });

    const Toast = Swal.mixin({
        toast: true,
        position: 'bottom-start',
        showConfirmButton: false,
        timer: 3000,
    })

    const GetPostComments = (page = 1) => {
        $.ajax({
            url: PostJsObject.admin_ajax,
            type: 'POST',
            data: {
                'action': 'v2_ajax_handler',
                'nonce': PostJsObject.nonce,
                'callback': 'post_get_comments',
                'page': page,
                'post_id': PostJsObject.post_id
            },
            beforeSend: function () {
                let out = ''
                for (let i = 0; i < 10; i++) {
                    out += '<div class="skeleton w-full h-d200 mb-4 rounded-xl"></div>'
                }
                $("#comments-list").html(out)
            },
            success: function (data) {
                $("#comments-list").html(data)
            }
        })
    }


    let temp = $("#ez-comment-form > div:nth-of-type(1) > span").html()

    $("body")
        .on('click', ".pagination a", function (e) {
            e.preventDefault()
            const page = $(this).attr('href').split('?comment_page=')[1]
            GetPostComments(page)
        })
        .on('submit', '#ez-comment-form', function (e) {
            e.preventDefault()

            let _ = $(this)

            let values = {};
            $.each(_.serializeArray(), function (i, field) {
                values[field.name] = field.value;
            });



            $.ajax({
                url: PostJsObject.admin_ajax,
                type: 'POST',
                data: {
                    'action': 'v2_ajax_handler',
                    'nonce': PostJsObject.nonce,
                    'callback': 'post_add_comment',
                    ...values
                },
                beforeSend: function () {
                    _.find('button[type="submit"]')
                        .attr('disabled', 'disabled')
                        .html('<span class="spinner w-4 border-2 border-white"></span>')
                },
                success: function (response) {

                    Toast.fire({
                        icon: response.success ? 'success' : 'error',
                        title: response.data
                    })

                    if (!response.success) {
                        _.find('button[type="submit"]')
                            .removeAttr('disabled')
                            .text('دیدگاهتان را ارسال کنید')
                    } else {
                        setTimeout(() => window.location.reload(), 3000)
                    }
                }
            })
        })
        .on('click', '.reply-comment', function () {
            $([document.documentElement, document.body]).animate({
                scrollTop: $("#ez-comment-form").offset().top - 20
            }, 2000);

            let name = $(this).data('name'),
                id = $(this).data('id')

            $("#ez-comment-form input[name='parent']").val(id)
            $("#ez-comment-form > div:nth-of-type(1) > span").html(`شما در حال پاسخ به دیدگاه ${name} هستید. <button type="button" class="cancel-reply">انصراف</button>`)
        })
        .on('click', '.cancel-reply', function () {
            $("#ez-comment-form input[name='parent']").val(0)
            $("#ez-comment-form > div:nth-of-type(1) > span").html(temp)
        })
})
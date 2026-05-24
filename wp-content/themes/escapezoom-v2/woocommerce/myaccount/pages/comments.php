<?php
//global $wpdb;
//
//$user = wp_get_current_user();
//
//$user_id = get_current_user_id();
//
//$page_num = sanitize_text_field( $_POST['page'] ) ?: 1;
//
//$comments_per_page = 30;
//
//$user_role = get_user_role( $user_id );
//if ( $user_role == 'sans_manager' ) {
//	$user_products = $wpdb->get_results( "SELECT *  FROM `wp_postmeta` WHERE `meta_key` LIKE 'sans_manager' AND `meta_value` LIKE {$user_id}", ARRAY_A );
//} elseif ( $user_role == 'compiler' ) {
//	$user_products = $wpdb->get_results( "SELECT *  FROM `wp_postmeta` WHERE `meta_key` LIKE 'user_ebtal' AND `meta_value` LIKE {$user_id}", ARRAY_A );
//}
//
//foreach ( $user_products as $user_product ) {
//	$post_type = get_post_type( $user_product['post_id'] );
//
//	if ( $post_type == 'product' ) {
//		$active_products[] = $user_product['post_id'];
//	}
//}
//
//if ( empty( $user_products ) || empty( $active_products ) ) {
//	wp_send_json_error( 'شما هیچ اتاق فعالی برای نمایش ندارید.', 400 );
//} else {
//	$product_ids_str = implode( ',', $active_products );
//
//	$total_comments = $wpdb->get_var( "SELECT COUNT(*) FROM wp_comments WHERE comment_post_ID IN ($product_ids_str) AND comment_approved = 1" );
//	$total_pages    = ( $total_comments > 0 ) ? ceil( $total_comments / $comments_per_page ) : 1;
//
//	$args           = [
//		'post_type' => 'product',
//		'post__in'  => $active_products,
//		'status'    => 'approve',
//		'number'    => $comments_per_page,
//		'paged'     => $page_num,
//		'parent'    => 0,
//	];
//	$comments_query = new WP_Comment_Query;
//	$comments       = $comments_query->query( $args );
//
//	if ( $comments ) {
//		foreach ( $comments as $comment ) {
//
//			$comment_id = $comment->comment_ID;
//
//			$replies_args = [
//				'parent' => $comment_id,
//				'status' => 'approve',
//				'type'   => 'comment',
//			];
//
//			if ( ctype_digit( $comment->comment_author ) ) {
//				$author_title = str_replace( substr( $comment->comment_author, 3, 5 ), "×××××", $comment->comment_author );
//			}
//
//			// راهنمایی
//			$data['rating_items'] = [
//				'type'  => 'product_rating_items',
//				'desc'  => 'اطلاعات مربوط به آیتم های امتیاز دهی در کامنت های سینگل محصول',
//				'items' => [
//					1 => 'فضاسازی',
//					2 => 'کیفیت معما',
//					3 => 'تازگی و خلاقیت',
//					4 => 'بازیگردانی و اکت',
//					5 => 'برخورد پرسنل',
//				],
//			];
//
//			$comment_rate = get_comment_meta( $comment_id, 'comment_rating' )[0];
//
//			$items[] = [
//				'id'            => (int) $comment_id,
//				'author_title'  => $author_title,
//				'author_level'  => 1,
//				'product_title' => get_the_title( $comment->comment_post_ID ),
//				'content'       => $comment->comment_content,
//				'reported'      => ! empty( get_comment_meta( $comment_id, 'report_reason', true ) ) ? true : false,
//				'date'          => strtotime( $comment->comment_date ),
//				'sans_time'     => '',
//				'rate'          => [
//					1 => $comment_rate[1094] / 20,
//					2 => $comment_rate[1095] / 20,
//					3 => $comment_rate[1098] / 20,
//					4 => $comment_rate[1096] / 20,
//					5 => $comment_rate[1097] / 20,
//				],
//				'reply'         => ( get_comments( $replies_args )[0] )->comment_content,
//			];
//		}
//	}
//}
?>
<div class="lg:col-span-8 2xl:col-span-9">
    <section class="rounded-2xl border border-slate-120 px-8 shadow-12 max-lg:mb-0 max-lg:rounded-none max-lg:px-0 max-lg:shadow-none py-12 max-lg:border-0 max-lg:py-0 h-auto">

        <h2 class="text-base font-bold md:text-lg mb-[33px]">
            <span class="text-xl">کامنت‌ها</span>
        </h2>

        <div id="comments-list"></div>

    </section>
</div>

<script>
    jQuery(document).ready(function ($) {
        /**
         * Initialize Toast
         */
        const Toast = Swal.mixin({
            toast: true,
            position: 'bottom-start',
            showConfirmButton: false,
            timer: 3000,
        })

        const GetComments = (page = 1) => {
            $.ajax({
                url: "<?php echo admin_url( 'admin-ajax.php' ) ?>",
                type: 'POST',
                data: {
                    'action': 'v2_ajax_handler',
                    'nonce': "<?php echo wp_create_nonce( 'v2-ajax-nonce' ) ?>",
                    'callback': 'panel_comments_list_get',
                    'page': page
                }, beforeSend: function () {
                    $("#comments-list").html(() => {
                        let out = ""
                        for (let i = 0; i < 10; i++) {
                            out += "<div class='skeleton h-44 w-full rounded-xl mb-4'></div>"
                        }
                        return out
                    })
                }, success: function (response) {
                    $("#comments-list").html(response)
                }
            })
        }

        GetComments()

        $("body")
            .on('click', '.reply-button', function () {
                let _ = $(this)

                let form = _.data('target-form')

                _.addClass('hidden')
                $(`.close-reply-button[data-target-form=${form}]`).removeClass('hidden')
                $(`[data-form="${form}"]`).slideDown()
            })
            .on('click', '.close-reply-button', function () {
                let _ = $(this)

                let form = _.data('target-form')

                _.addClass('hidden')
                $(`.reply-button[data-target-form=${form}]`).removeClass('hidden')
                $(`[data-form="${form}"]`).slideUp()
            })
            .on('submit', '.submit-reply-form', function (e) {
                e.preventDefault()

                let _ = $(this)
                let data = {
                    'action': 'v2_ajax_handler',
                    'nonce': "<?php echo wp_create_nonce( 'v2-ajax-nonce' ) ?>",
                    'callback': 'panel_comments_reply_add',
                };

                $.each(_.serializeArray(), function (i, field) {
                    data[field.name] = field.value;
                });

                $.ajax({
                    url: "<?php echo admin_url( 'admin-ajax.php' ) ?>",
                    type: 'POST',
                    data: data,
                    beforeSend: function () {
                        _.find('button[type="submit"]').attr('disabled', 'disabled').html('<div class="spinner" style="width: 33px;border-color: #FFF;border-width: 4px;"></div>')
                    },
                    success: function (response) {
                        Toast.fire({
                            icon: response.success ? 'success' : 'error',
                            title: response.data.message
                        })

                        if (response.success) {
                            _.parent().html(response.data.reply)
                        }
                    }
                })
            })
            .on('click', '.pagination a', function (e) {
                e.preventDefault()

                let page = $(this).attr('href').split('?page=')[1] ? $(this)
                    .attr('href')
                    .split('?page=')[1] : 1

                GetComments(page)
            })
            .on('click', '.comment-content .show-more', function () {
                let _ = $(this),
                    wrap = _.parent()
                _.remove()
                wrap.html(wrap.data('full-text')).removeAttr('data-full-text')
            })
            .on('click', '.more-details', function () {
                let _ = $(this),
                    target = _.prev(),
                    textWrapper = _.find('span')

                target.slideToggle(300)

                textWrapper.text(function (i, text) {
                    return text === 'مشاهده بیشتر' ? 'مشاهده کمتر' : 'مشاهده بیشتر'
                })

                _.find('svg').toggleClass('rotate-180')
            })
    })
</script>
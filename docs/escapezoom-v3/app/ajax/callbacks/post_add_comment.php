<?php

$post    = sanitize_text_field( $_POST['post'] );
$parent  = sanitize_text_field( $_POST['parent'] );
$author  = sanitize_text_field( $_POST['author'] );
$content = sanitize_textarea_field( $_POST['content'] );

if ( ! $author || $author == '' ) {
	wp_send_json_error( 'نام ضروری میباشد.' );
}

if ( ! $content || $content == '' ) {
	wp_send_json_error( 'دیدگاه ضروری میباشد.' );
}

$comment = wp_insert_comment( [
	'comment_author'   => $author,
	'comment_content'  => $content,
	'comment_post_ID'  => $post,
	'comment_parent'   => $parent,
	'comment_approved' => 0,
] );

if ( is_wp_error( $comment ) ) {
	wp_send_json_error( 'خطایی در هنگام ثبت دیدگاه بوجود آمده، لطفا دوباره تلاش کنید.' );
}

wp_send_json_success( 'دیدگاه با موفقیت ثبت شد و در انتظار بازبینی میباشد و پس از تایید نمایش داده خواهد شد.' );
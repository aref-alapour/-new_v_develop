<?php

use EscapeZoom\Core\Modules\AjaxGateway\Exception\GatewayAuthException;
use EscapeZoom\Core\Modules\Booking\Services\Panel\PanelProductAuthorizationService;

$user = wp_get_current_user();

$comment_id      = isset( $_POST['comment'] ) ? (int) sanitize_text_field( wp_unslash( (string) $_POST['comment'] ) ) : -1;
$product_id      = isset( $_POST['product'] ) ? (int) sanitize_text_field( wp_unslash( (string) $_POST['product'] ) ) : -1;
$comment_content = isset( $_POST['comment_content'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['comment_content'] ) ) : '';

if ( $comment_id <= 0 ) {
	wp_send_json_error( 'شماره کامنت مشخص نیست.' );
}

if ( $product_id <= 0 ) {
	wp_send_json_error( 'محصول مشخص نیست.' );
}

try {
	PanelProductAuthorizationService::assertCanManageProduct( $product_id );
} catch ( GatewayAuthException $e ) {
	wp_send_json_error( 'این کامنت متعلق به شما نیست!', 403 );
}

$comment = wp_insert_comment(
	array(
		'comment_post_ID'  => $product_id,
		'comment_author'   => $user->user_login,
		'comment_content'  => $comment_content,
		'comment_type'     => 'comment',
		'comment_parent'   => $comment_id,
		'user_id'          => $user->ID,
		'comment_approved' => 1,
	)
);

if ( ! $comment ) {
	wp_send_json_error( 'پاسخ ثبت نشد دوباره امتحان کنید.' );
}

$reply = '<div class="flex flex-col gap-4">
	<div class="text-black font-bold text-lg">پاسخ شما</div>
	<div class="flex items-start gap-4">
		<svg xmlns="http://www.w3.org/2000/svg" width="16" height="14" viewBox="0 0 16 14" fill="none" class="mx-0 mt-2">
		<path fill-rule="evenodd" clip-rule="evenodd" d="M5.70679 13.7072C5.89426 13.5197 5.99957 13.2654 5.99957 13.0002C5.99957 12.735 5.89426 12.4807 5.70679 12.2932L3.41379 10.0002H8.99979C10.8563 10.0002 12.6368 9.26272 13.9495 7.94996C15.2623 6.63721 15.9998 4.85673 15.9998 3.00021V1.00021C15.9998 0.734997 15.8944 0.480642 15.7069 0.293106C15.5194 0.10557 15.265 0.000213623 14.9998 0.000213623C14.7346 0.000213623 14.4802 0.10557 14.2927 0.293106C14.1051 0.480642 13.9998 0.734997 13.9998 1.00021V3.00021C13.9998 4.3263 13.473 5.59807 12.5353 6.53575C11.5976 7.47343 10.3259 8.00021 8.99979 8.00021H3.41379L5.70679 5.70721C5.8023 5.61497 5.87848 5.50462 5.93089 5.38262C5.9833 5.26061 6.01088 5.12939 6.01204 4.99661C6.01319 4.86384 5.98789 4.73216 5.93761 4.60926C5.88733 4.48636 5.81307 4.37471 5.71918 4.28082C5.62529 4.18693 5.51364 4.11267 5.39074 4.06239C5.26784 4.01211 5.13616 3.98681 5.00339 3.98796C4.87061 3.98912 4.73939 4.0167 4.61738 4.06911C4.49538 4.12152 4.38503 4.1977 4.29279 4.29321L0.292786 8.29321C0.105315 8.48074 0 8.73505 0 9.00021C0 9.26538 0.105315 9.51969 0.292786 9.70721L4.29279 13.7072C4.48031 13.8947 4.73462 14 4.99979 14C5.26495 14 5.51926 13.8947 5.70679 13.7072Z" fill="#889BAD"/>
		</svg>
		<span class="text-gray-500">' . esc_html( $comment_content ) . '</span>
	</div>
</div>';

wp_send_json_success(
	array(
		'message' => 'پاسخ شما با موفقیت ثبت شد.',
		'reply'   => $reply,
	)
);

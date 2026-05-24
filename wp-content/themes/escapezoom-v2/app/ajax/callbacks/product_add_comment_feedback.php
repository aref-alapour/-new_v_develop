<?php
$user_id = get_current_user_id();

$comment_id = $_POST['comment_id'];
$type       = $_POST['type'];

if ( ! isset( $comment_id ) || empty( $comment_id ) ) {
	wp_send_json_error( 'آیدی کامنت مشخص نیست' );
}

if ( ! $user_id ) {
	wp_send_json_error( 'جهت رای دادن به این کامنت ابتدا وارد شوید' );
}

$feedback_users = get_comment_meta( $comment_id, 'cld_users', true );
$feedback_users = empty( $feedback_users ) ? [] : $feedback_users;

if ( in_array( $user_id, $feedback_users ) ) { // already user reacted on this comment

	$feedback_users_info = get_comment_meta( $comment_id, 'cld_users_info', true );

	$prev_type = $feedback_users_info[ $user_id ];

	if ( $prev_type == $type ) { // undo

		if ( $type == 'like' ) {

			$like_count = get_comment_meta( $comment_id, 'cld_like_count', true );

			update_comment_meta( $comment_id, 'cld_like_count', -- $like_count );

			$res = [ $type => $like_count ];

		} else {
			$dislike_count = get_comment_meta( $comment_id, 'cld_dislike_count', true );

			update_comment_meta( $comment_id, 'cld_dislike_count', -- $dislike_count );

			$res = [ $type => $dislike_count ];
		}

		unset( $feedback_users_info[ $user_id ] );

		update_comment_meta( $comment_id, 'cld_users', array_diff( $feedback_users, [ $user_id ] ) );
		update_comment_meta( $comment_id, 'cld_users_info', $feedback_users_info );

	} else { // undo + do new action

		if ( $type == 'like' ) {

			$like_count        = get_comment_meta( $comment_id, 'cld_like_count', true );
			$cld_dislike_count = get_comment_meta( $comment_id, 'cld_dislike_count', true );

			update_comment_meta( $comment_id, 'cld_like_count', ++ $like_count );
			update_comment_meta( $comment_id, 'cld_dislike_count', -- $cld_dislike_count );

			$res = [ $type => $like_count, $prev_type => $cld_dislike_count ];

		} else {

			$like_count        = get_comment_meta( $comment_id, 'cld_like_count', true );
			$cld_dislike_count = get_comment_meta( $comment_id, 'cld_dislike_count', true );

			update_comment_meta( $comment_id, 'cld_like_count', -- $like_count );
			update_comment_meta( $comment_id, 'cld_dislike_count', ++ $cld_dislike_count );

			$res = [ $type => $cld_dislike_count, $prev_type => $like_count ];

		}

		unset( $feedback_users_info[ $user_id ] );
		$feedback_users_info[ $user_id ] = $type;

		update_comment_meta( $comment_id, 'cld_users_info', $feedback_users_info );
	}

} else { // user reactions for first time

	if ( $type == 'like' ) {
		$like_count = get_comment_meta( $comment_id, 'cld_like_count', true );

		if ( empty( $like_count ) ) {
			$like_count = 0;
		}

		update_comment_meta( $comment_id, 'cld_like_count', ++ $like_count );

		$res = [ $type => $like_count ];

	} else {
		$dislike_count = get_comment_meta( $comment_id, 'cld_dislike_count', true );

		if ( empty( $dislike_count ) ) {
			$dislike_count = 0;
		}

		update_comment_meta( $comment_id, 'cld_dislike_count', ++ $dislike_count );

		$res = [ $type => $dislike_count ];
	}

	$feedback_users_info = get_comment_meta( $comment_id, 'cld_users_info', true );
	$feedback_users_info = ( empty( $feedback_users_info ) ) ? [] : $feedback_users_info;

	$feedback_users[]                = $user_id;
	$feedback_users_info[ $user_id ] = $type;

	update_comment_meta( $comment_id, 'cld_users', $feedback_users );
	update_comment_meta( $comment_id, 'cld_users_info', $feedback_users_info );

}

wp_send_json_success( ( (int) get_comment_meta( $comment_id, 'cld_like_count', true ) - (int) get_comment_meta( $comment_id, 'cld_dislike_count', true ) ) );
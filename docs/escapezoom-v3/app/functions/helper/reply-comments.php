<?php
function get_post_reply_comments( $comment_id ) {

    $comments_list = get_comments( [
        'parent'  => $comment_id,
        'status'  => 'approve',
        'type'    => 'comment',
        'orderby' => 'comment_date',
        'order'   => 'DESC',
    ] );

    if ( ! empty( $comments_list ) ) {
        foreach ( $comments_list as $comment ) {

            $replies = get_post_reply_comments( $comment->comment_ID );

            $replies[] = [
                'comment_id'   => (int) $comment->comment_ID,
                'parent'       => $comment->comment_parent,
                'author_title' => $comment->comment_author,
                'author_image' => get_user_meta( $comment->user_id, 'user_avatar', true ) ?: 'http://escapezoom.ir/wp-content/uploads/2024/04/male_avatar_level_1.png',
                'author_level' => get_user_meta( $comment->user_id, 'level', true ) ?: 1,
                'content'      => $comment->comment_content,
                'date'         => strtotime( $comment->comment_date ),
                'replies'      => $replies,
            ];
        }
    }

    return $replies;
}
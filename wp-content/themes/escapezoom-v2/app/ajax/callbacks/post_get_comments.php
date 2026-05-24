<?php
$user_id = get_current_user_id();

$page    = sanitize_text_field( $_POST['page'] ) ?: 1;
$post_id = sanitize_text_field( $_POST['post_id'] );

$comments_per_page = 10;

$all_comments_count = count( get_comments( [
	'post_id' => $post_id,
	'status'  => 'approve',
	'parent'  => 0,
	'order'   => 'DESC',
] ) );

$comments_list = get_comments( [
	'post_id' => $post_id,
	'status'  => 'approve',
	'parent'  => 0,
	'orderby' => 'comment_date',
	'order'   => 'DESC',
	'number'  => $comments_per_page,
	'offset'  => ( $page - 1 ) * $comments_per_page,
] );

$total_comments = $all_comments_count;

$comments = [];

if ( ! empty( $comments_list ) ) {
	foreach ( $comments_list as $comment ) {
		$comment_id = $comment->comment_ID;

		$replies = get_post_reply_comments( $comment_id );

		$comments[] = [
			'comment_id'   => (int) $comment_id,
			'parent'       => $comment->comment_parent,
			'author_title' => get_user_by( 'id', $comment->user_id )->data->display_name ?: $comment->comment_author,
			'author_image' => get_user_meta( $comment->user_id, 'user_avatar', true ) ?: 'http://escapezoom.ir/wp-content/uploads/2024/04/male_avatar_level_1.png',
			'author_level' => get_user_meta( $comment->user_id, 'level', true ) ?: 1,
			'content'      => $comment->comment_content,
			'date'         => strtotime( $comment->comment_date ),
			'replies'      => $replies,
		];
	}
}

$data = [
	'items'      => $comments,
	'pagination' => [
		'current_page' => (int) $page,
		'total_pages'  => ceil( $total_comments / $comments_per_page ),
	],
];

get_replies( $comments );

if ( $data['pagination']['total_pages'] > 1 ) { ?>
    <div class="mb-9 mt-20 flex w-full items-center justify-start gap-4">
        <div class="flex gap-4 max-lg:gap-2 justify-start max-lg:justify-start pagination">
			<?php echo paginate_links( [
				'mid_size'  => 1,
				'base'      => get_pagenum_link( 1 ) . '%_%',
				'format'    => '?comment_page=%#%',
				'current'   => $page,
				'total'     => $data['pagination']['total_pages'],
				'prev_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none" class="rotate-180 opacity-25"><path d="M5.08008 11.1602L1.51062 7.14452C1.17384 6.76563 1.17384 6.19468 1.51062 5.81579L5.08008 1.80016" stroke="#0A184A" stroke-width="2" stroke-linecap="round"></path></svg>',
				'next_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none"><path d="M5.08008 11.1602L1.51062 7.14452C1.17384 6.76563 1.17384 6.19468 1.51062 5.81579L5.08008 1.80016" stroke="#0A184A" stroke-width="2" stroke-linecap="round"></path></svg>',
			] ); ?>
        </div>
    </div>
<?php }

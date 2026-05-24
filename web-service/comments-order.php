<?php
header( 'Content-Type: application/json; charset=utf-8' );
http_response_code( 410 );
echo json_encode(
	[
		'error'   => 'Endpoint deprecated',
		'message' => 'Use v2_ajax_handler callback get_comments_order_list',
	]
);

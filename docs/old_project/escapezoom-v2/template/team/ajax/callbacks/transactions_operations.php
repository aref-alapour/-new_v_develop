<?php
global $wldb;

$user_id        = sanitize_text_field($_POST['user_id']);
$amount         = filter_var($_POST['amount'], FILTER_SANITIZE_NUMBER_INT);
$description    = sanitize_text_field($_POST['description']);
$operation      = sanitize_text_field($_POST['operation']);

if ( $operation == -1 || !$user_id || !$amount )
    return;

$current_balance    = $wldb->get_balance($user_id);
$amount             = $operation == 'p' ? $amount : $amount * (-1);
$balance            = $current_balance + (int)$amount;

$new_transaction = array (
    'user_id'       => $user_id,
    'amount'        => $amount,
    'balance'       => $balance,
    'description'   => $description,
    'type'          => 'admin',
    'status'        => '',
);

$wldb->insert($new_transaction);
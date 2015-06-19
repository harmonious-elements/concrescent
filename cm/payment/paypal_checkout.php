<?php

require_once dirname(__FILE__).'/payment.php';

$result = $_SESSION['cart'];
$expected_hash = md5(serialize($result));
$actual_hash = $_SESSION['cart_hash'];
$state = $_SESSION['cart_state'];

if ($result && $expected_hash == $actual_hash && $state == 'ready') {
	$conn = get_db_connection();
	
	$paypal_items = array(paypal_item($result['real_name'].' - '.$result['name'], $result['payment_price']));
	$paypal_pretotal = $result['payment_price'];
	$paypal_total = $result['payment_price'];
	
	if ($paypal_total > 0) {
		$api_url = get_paypal_api_url();
		$token = get_paypal_token();
		$return_url = dirname(get_page_url()).'/paypal_return.php';
		$cancel_url = dirname(get_page_url()).'/paypal_cancel.php';
		$transaction = array(
			'amount' => paypal_total($paypal_total),
			'description' => $event_name,
			'item_list' => array('items' => $paypal_items),
		);
		$payment = paypal_payment($api_url, $token, $return_url, $cancel_url, $transaction);
		$approval_url = paypal_link_approval_url($payment);
		
		$_SESSION['paypal_items'] = $paypal_items;
		$_SESSION['paypal_pretotal'] = $paypal_pretotal;
		$_SESSION['paypal_total'] = $paypal_total;
		$_SESSION['api_url'] = $api_url;
		$_SESSION['token'] = $token;
		$_SESSION['payment_id'] = $payment['id'];
		$_SESSION['cart_state'] = 'approval';
		paypal_redirect($approval_url);
	} else {
		$return_url = dirname(get_page_url()).'/paypal_return.php';
		
		$_SESSION['paypal_items'] = $paypal_items;
		$_SESSION['paypal_pretotal'] = $paypal_pretotal;
		$_SESSION['paypal_total'] = $paypal_total;
		$_SESSION['api_url'] = null;
		$_SESSION['token'] = null;
		$_SESSION['payment_id'] = null;
		$_SESSION['cart_state'] = 'approval';
		paypal_redirect($return_url);
	}
} else {
	header('Location: index.php');
	exit(0);
}
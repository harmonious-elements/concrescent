<?php

require_once dirname(__FILE__).'/application.php';

$cart = get_cart();
$expected_hash = md5(serialize($cart));
$actual_hash = $_SESSION['cart_hash'];
$state = $_SESSION['cart_state'];

if ($cart && count($cart) && $expected_hash == $actual_hash && $state == 'ready') {
	$conn = get_db_connection();
	db_require_table('staffer_badges', $conn);
	db_require_table('staffers', $conn);
	$badge_names = get_staffer_badge_names($conn);
	
	$staffer_ids = array();
	$paypal_items = array();
	$paypal_total = 0;
	foreach ($cart as $item) {
		$staffer_ids[] = $item['id'];
		$paypal_items[] = paypal_item($item['badge_name'], $item['payment_price']);
		$paypal_total += $item['payment_price'];
	}
	
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
		
		$_SESSION['staffer_ids'] = $staffer_ids;
		$_SESSION['paypal_items'] = $paypal_items;
		$_SESSION['paypal_total'] = $paypal_total;
		$_SESSION['api_url'] = $api_url;
		$_SESSION['token'] = $token;
		$_SESSION['payment_id'] = $payment['id'];
		$_SESSION['cart_state'] = 'approval';
		paypal_redirect($approval_url);
	} else {
		$return_url = dirname(get_page_url()).'/paypal_return.php';
		
		$_SESSION['staffer_ids'] = $staffer_ids;
		$_SESSION['paypal_items'] = $paypal_items;
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
<?php

require_once dirname(__FILE__).'/application.php';

$cart = get_cart();
$expected_hash = md5(serialize($cart));
$actual_hash = $_SESSION['cart_hash'];
$state = $_SESSION['cart_state'];

if ($cart && count($cart) && $expected_hash == $actual_hash && $state == 'approval') {
	$conn = get_db_connection();
	db_require_table('eventlets', $conn);
	
	$eventlet_ids = $_SESSION['eventlet_ids'];
	$paypal_items = $_SESSION['paypal_items'];
	$paypal_pretotal = $_SESSION['paypal_pretotal'];
	$paypal_total = $_SESSION['paypal_total'];
	$api_url = $_SESSION['api_url'];
	$token = $_SESSION['token'];
	$payment_id = $_SESSION['payment_id'];
	
	foreach ($eventlet_ids as $id) {
		$set = encode_eventlet(array(
			'payment_status' => 'Cancelled',
			'payment_type' => 'PayPal',
			'payment_txn_id' => null,
			'payment_original_price' => $paypal_pretotal,
			'payment_final_price' => $paypal_total,
			'payment_date' => 'NOW()',
			'payment_details' => null,
		));
		$q = 'UPDATE '.db_table_name('eventlets').' SET '.$set.' WHERE `id` = '.$id;
		mysql_query($q, $conn);
	}
	
	destroy_cart();
	
	render_application_head('Payment Cancelled');
	render_application_body('Payment Cancelled');
	echo '<div class="card">';
		echo '<div class="card-title">Payment Cancelled</div>';
		echo '<div class="card-content">';
			echo '<p>You have cancelled your payment.</p>';
		echo '</div>';
	echo '</div>';
	render_application_tail();
} else {
	header('Location: index.php');
	exit(0);
}
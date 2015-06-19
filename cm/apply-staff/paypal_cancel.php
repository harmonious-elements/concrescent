<?php

require_once dirname(__FILE__).'/application.php';

$cart = get_cart();
$expected_hash = md5(serialize($cart));
$actual_hash = $_SESSION['cart_hash'];
$state = $_SESSION['cart_state'];

if ($cart && count($cart) && $expected_hash == $actual_hash && $state == 'approval') {
	$conn = get_db_connection();
	db_require_table('staffers', $conn);
	
	$staffer_ids = $_SESSION['staffer_ids'];
	$paypal_items = $_SESSION['paypal_items'];
	$paypal_total = $_SESSION['paypal_total'];
	$api_url = $_SESSION['api_url'];
	$token = $_SESSION['token'];
	$payment_id = $_SESSION['payment_id'];
	
	foreach ($staffer_ids as $id) {
		$set = encode_staffer(array(
			'payment_status' => 'Cancelled',
			'payment_type' => 'PayPal',
			'payment_txn_id' => null,
			'payment_date' => 'NOW()',
			'payment_details' => null,
		));
		$q = 'UPDATE '.db_table_name('staffers').' SET '.$set.' WHERE `id` = '.$id;
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
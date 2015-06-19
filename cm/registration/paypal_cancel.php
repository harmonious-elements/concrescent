<?php

require_once dirname(__FILE__).'/registration.php';

$cart = get_cart();
$expected_hash = md5(serialize($cart));
$actual_hash = $_SESSION['cart_hash'];
$state = $_SESSION['cart_state'];

if ($cart && count($cart) && $expected_hash == $actual_hash && $state == 'approval') {
	$conn = get_db_connection();
	db_require_table('attendees', $conn);
	
	$attendee_ids = $_SESSION['attendee_ids'];
	$paypal_items = $_SESSION['paypal_items'];
	$paypal_total = $_SESSION['paypal_total'];
	$api_url = $_SESSION['api_url'];
	$token = $_SESSION['token'];
	$payment_id = $_SESSION['payment_id'];
	
	foreach ($attendee_ids as $id) {
		$set = encode_attendee(array(
			'payment_status' => 'Cancelled',
			'payment_type' => 'PayPal',
			'payment_txn_id' => null,
			'payment_total_price' => $paypal_total,
			'payment_date' => 'NOW()',
			'payment_details' => null,
		));
		$q = 'UPDATE '.db_table_name('attendees').' SET '.$set.' WHERE `id` = '.$id;
		mysql_query($q, $conn);
	}
	
	destroy_cart();
	
	render_registration_head('Payment Cancelled');
	render_registration_body('Payment Cancelled');
	echo '<div class="card">';
		echo '<div class="card-title">Payment Cancelled</div>';
		echo '<div class="card-content">';
			echo '<p>You have cancelled your payment.</p>';
		echo '</div>';
		echo '<div class="card-buttons">';
			echo '<a href="register.php" role="button" class="a-button register-button">Start a New Registration</a>';
		echo '</div>';
	echo '</div>';
	render_registration_tail();
} else {
	header('Location: index.php');
	exit(0);
}
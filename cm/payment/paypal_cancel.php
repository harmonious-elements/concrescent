<?php

require_once dirname(__FILE__).'/payment.php';

$result = $_SESSION['cart'];
$expected_hash = md5(serialize($result));
$actual_hash = $_SESSION['cart_hash'];
$state = $_SESSION['cart_state'];

if ($result && $expected_hash == $actual_hash && $state == 'approval') {
	$conn = get_db_connection();
	
	$paypal_items = $_SESSION['paypal_items'];
	$paypal_pretotal = $_SESSION['paypal_pretotal'];
	$paypal_total = $_SESSION['paypal_total'];
	$api_url = $_SESSION['api_url'];
	$token = $_SESSION['token'];
	$payment_id = $_SESSION['payment_id'];
	
	set_payment_cancelled($result['id'], 'PayPal', $paypal_total, $conn);
	unset($_SESSION['cart']);
	unset($_SESSION['cart_hash']);
	unset($_SESSION['cart_state']);
	session_destroy();
	
	render_payment_head('Payment Cancelled');
	render_payment_body('Payment Cancelled');
	echo '<div class="card">';
		echo '<div class="card-title">Payment Cancelled</div>';
		echo '<div class="card-content">';
			echo '<p>You have cancelled your payment.</p>';
		echo '</div>';
	echo '</div>';
	render_payment_tail();
} else {
	header('Location: index.php');
	exit(0);
}
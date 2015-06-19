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
	$payer_id = isset($_GET['PayerID']) ? $_GET['PayerID'] : null;
	
	if ($api_url || $token || $payment_id || $payer_id) {
		$sale = paypal_execute($api_url, $token, $payment_id, $payer_id);
		$transaction_id = paypal_transaction_id($sale);
	} else {
		$sale = array();
		$transaction_id = uniqid();
	}
	
	if ($transaction_id) {
		set_payment_completed($result['id'], 'PayPal', $transaction_id, $paypal_total, $sale, $conn);
		$email_template = get_mail_template('payment_paid', $conn);
		if ($email_template && trim($email_template['body'])) {
			$result = get_payment($result['id'], $conn);
			$result['transaction_id'] = $transaction_id;
			mail_send($result['email_address'], $email_template, $result);
		}
		unset($_SESSION['cart']);
		unset($_SESSION['cart_hash']);
		unset($_SESSION['cart_state']);
		session_destroy();
		
		render_payment_head('Payment Complete');
		render_payment_body('Payment Complete');
		echo '<div class="card">';
			echo '<div class="card-title">Payment Complete</div>';
			echo '<div class="card-content">';
				echo '<p>Your payment has been accepted.</p>';
				echo '<p>You can <b><a href="'.htmlspecialchars($result['review_order_url']).'">review your order</a></b> at any time.</p>';
			echo '</div>';
		echo '</div>';
		render_payment_tail();
	} else {
		set_payment_failed($result['id'], 'PayPal', $sale, $conn);
		unset($_SESSION['cart']);
		unset($_SESSION['cart_hash']);
		unset($_SESSION['cart_state']);
		session_destroy();
		
		render_payment_head('Payment Refused');
		render_payment_body('Payment Refused');
		echo '<div class="card">';
			echo '<div class="card-title">Payment Refused</div>';
			echo '<div class="card-content spaced">';
				echo '<p>PayPal has refused this transaction.</p>';
				if ($sale['message']) {
					echo '<p>PayPal says: ' . htmlspecialchars($sale['message']) . '</p>';
				}
				echo '<p>Unfortunately, that is all we know. Please try again later.</p>';
			echo '</div>';
		echo '</div>';
		render_payment_tail();
	}
} else {
	header('Location: index.php');
	exit(0);
}
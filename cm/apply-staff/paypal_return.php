<?php

require_once dirname(__FILE__).'/application.php';
require_once dirname(__FILE__).'/../lib/dal/mail.php';
require_once dirname(__FILE__).'/../lib/ui/mail.php';

$cart = get_cart();
$expected_hash = md5(serialize($cart));
$actual_hash = $_SESSION['cart_hash'];
$state = $_SESSION['cart_state'];

if ($cart && count($cart) && $expected_hash == $actual_hash && $state == 'approval') {
	$conn = get_db_connection();
	db_require_table('staffer_badges', $conn);
	db_require_table('staffers', $conn);
	$badge_names = get_staffer_badge_names($conn);
	
	$staffer_ids = $_SESSION['staffer_ids'];
	$paypal_items = $_SESSION['paypal_items'];
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
		$email_template = get_mail_template('staff_paid', $conn);
		$has_email_template = $email_template && trim($email_template['body']);
		$first = true;
		
		foreach ($staffer_ids as $id) {
			$set = encode_staffer(array(
				'payment_status' => 'Completed',
				'payment_type' => 'PayPal',
				'payment_txn_id' => $transaction_id,
				'payment_date' => 'NOW()',
				'payment_details' => json_encode($sale),
			));
			$q = 'UPDATE '.db_table_name('staffers').' SET '.$set.' WHERE `id` = '.$id;
			mysql_query($q, $conn);
			
			if ($has_email_template || $first) {
				// This must execute at least once so we can get the review_order_url.
				$results = mysql_query('SELECT * FROM '.db_table_name('staffers').' WHERE `id` = '.$id, $conn);
				$result = mysql_fetch_assoc($results);
				$result = decode_staffer($result, $badge_names);
				$result['transaction_id'] = $transaction_id;
			}
			if ($has_email_template) mail_send($result['email_address'], $email_template, $result);
			
			$first = false;
		}
		
		destroy_cart();
		
		render_application_head('Payment Complete');
		render_application_body('Payment Complete');
		echo '<div class="card">';
			echo '<div class="card-title">Payment Complete</div>';
			echo '<div class="card-content">';
				echo '<p>Your payment has been accepted.</p>';
				echo '<p>You can <b><a href="'.htmlspecialchars($result['review_order_url']).'">review your order</a></b> at any time.</p>';
			echo '</div>';
		echo '</div>';
		render_application_tail();
	} else {
		foreach ($staffer_ids as $id) {
			$set = encode_staffer(array(
				'payment_status' => 'Incomplete',
				'payment_type' => 'PayPal',
				'payment_txn_id' => null,
				'payment_date' => 'NOW()',
				'payment_details' => json_encode($sale),
			));
			$q = 'UPDATE '.db_table_name('staffers').' SET '.$set.' WHERE `id` = '.$id;
			mysql_query($q, $conn);
		}
		
		destroy_cart();
		
		render_application_head('Payment Refused');
		render_application_body('Payment Refused');
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
		render_application_tail();
	}
} else {
	header('Location: index.php');
	exit(0);
}
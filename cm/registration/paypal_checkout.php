<?php

require_once dirname(__FILE__).'/registration.php';
require_once dirname(__FILE__).'/../lib/dal/questions.php';
require_once dirname(__FILE__).'/../lib/dal/mail.php';
require_once dirname(__FILE__).'/../lib/cmbase/util.php';

$cart = get_cart();
$expected_hash = md5(serialize($cart));
$actual_hash = $_SESSION['cart_hash'];
$state = $_SESSION['cart_state'];

if ($cart && count($cart) && $expected_hash == $actual_hash && $state == 'ready') {
	$conn = get_db_connection();
	db_require_table('attendee_badges', $conn);
	db_require_table('attendee_extension_questions', $conn);
	db_require_table('attendees', $conn);
	db_require_table('attendee_extension_answers', $conn);
	$badge_names = get_attendee_badge_names($conn);
	$extension_questions = get_extension_questions('attendee', $conn);
	
	$blacklisted = false;
	$attendee_ids = array();
	$paypal_items = array();
	$paypal_total = 0;
	foreach ($cart as $id => $item) {
		$bl = attendee_is_blacklisted($item, $conn);
		$set = encode_attendee(array_merge($item, array(
			'payment_status' => ($bl ? 'Pulled' : 'Incomplete'),
			'payment_type' => 'PayPal',
			'payment_txn_id' => null,
			'payment_total_price' => null,
			'payment_date' => null,
			'payment_details' => null,
			'payment_lookup_key' => 'UUID()',
		)));
		$q = 'INSERT INTO '.db_table_name('attendees').' SET '.$set.', `date_created` = NOW()';
		mysql_query($q, $conn);
		$attendee_id = (int)mysql_insert_id($conn);
		set_extension_answers('attendee', $attendee_id, $item['extension_answers'], $conn);
		if ($bl) $blacklisted = true;
		$attendee_ids[] = $attendee_id;
		$paypal_items[] = paypal_item($badge_names[$item['badge_id']], $item['payment_final_price']);
		$paypal_total += $item['payment_final_price'];
	}
	
	if ($blacklisted) {
		destroy_cart();
		$contact = get_mail_contact('attendee_paid', $conn);
		if ($contact) {
			$attendee_page_base = get_base_url() . 'admin/attendee.php?id=';
			$body = 'The following attendee registrations were just blacklisted:'."\r\n\r\n";
			foreach ($attendee_ids as $id) {
				$body .= $attendee_page_base . $id . "\r\n";
			}
			$body .= "\r\n";
			$body .= 'Please verify that these registrations were intended to be blacklisted and assist the registrant if necessary.';
			mail($contact, 'Blacklisted Attendee Registration', $body, 'From: '.$contact);
			// error_log('blacklisted attendee email is: '.$body);
		}
		
		render_registration_head('Could Not Complete Registration');
		render_registration_body('Could Not Complete Registration');
		echo '<div class="card">';
			echo '<div class="card-title">Could Not Complete Registration</div>';
			echo '<div class="card-content">';
				echo '<p>We\'re sorry, there was an issue with your registration and your registration could not be completed.</p>';
				if ($contact) {
					echo '<p>If you think this is an error, please ';
					echo '<b><a href="mailto:'.htmlspecialchars($contact).'">contact us</a></b>';
					echo '.</p>';
				}
			echo '</div>';
			echo '<div class="card-buttons">';
				echo '<a href="register.php" role="button" class="a-button register-button">Start a New Registration</a>';
			echo '</div>';
		echo '</div>';
		render_registration_tail();
	} else if ($paypal_total > 0) {
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
		
		$_SESSION['attendee_ids'] = $attendee_ids;
		$_SESSION['paypal_items'] = $paypal_items;
		$_SESSION['paypal_total'] = $paypal_total;
		$_SESSION['api_url'] = $api_url;
		$_SESSION['token'] = $token;
		$_SESSION['payment_id'] = $payment['id'];
		$_SESSION['cart_state'] = 'approval';
		paypal_redirect($approval_url);
	} else {
		$return_url = dirname(get_page_url()).'/paypal_return.php';
		
		$_SESSION['attendee_ids'] = $attendee_ids;
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
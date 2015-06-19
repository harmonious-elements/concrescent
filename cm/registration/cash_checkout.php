<?php

require_once dirname(__FILE__).'/registration.php';
require_once dirname(__FILE__).'/../lib/dal/questions.php';
require_once dirname(__FILE__).'/../lib/dal/mail.php';
require_once dirname(__FILE__).'/../lib/ui/mail.php';
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
	foreach ($cart as $id => $item) {
		$bl = attendee_is_blacklisted($item, $conn);
		$set = encode_attendee(array_merge($item, array(
			'payment_status' => ($bl ? 'Pulled' : 'Incomplete'),
			'payment_type' => 'Cash',
			'payment_txn_id' => uniqid(),
			'payment_total_price' => null,
			'payment_date' => 'NOW()',
			'payment_details' => null,
			'payment_lookup_key' => 'UUID()',
		)));
		$q = 'INSERT INTO '.db_table_name('attendees').' SET '.$set.', `date_created` = NOW()';
		mysql_query($q, $conn);
		$attendee_id = (int)mysql_insert_id($conn);
		set_extension_answers('attendee', $attendee_id, $item['extension_answers'], $conn);
		if ($bl) $blacklisted = true;
		$attendee_ids[] = $attendee_id;
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
	} else {
		$email_template = get_mail_template('attendee_paid', $conn);
		$has_email_template = $email_template && trim($email_template['body']);
		$first = true;
		foreach ($attendee_ids as $id) {
			if ($has_email_template || $first) {
				// This must execute at least once so we can get the order_url.
				$results = mysql_query('SELECT * FROM '.db_table_name('attendees').' WHERE `id` = '.$id, $conn);
				$result = mysql_fetch_assoc($results);
				$result = decode_attendee($result, $badge_names);
				$result['transaction_id'] = $transaction_id;
			}
			if ($has_email_template) mail_send($result['email_address'], $email_template, $result);
			$first = false;
		}
		
		destroy_cart();
		
		render_registration_head('Registration Complete');
		render_registration_body('Registration Complete');
		echo '<div class="card">';
			echo '<div class="card-title">Registration Complete</div>';
			echo '<div class="card-content">';
				echo '<p>Your registration has been submitted. You will need to pay at the door.</p>';
				echo '<p>You can <b><a href="'.htmlspecialchars($result['order_url']).'">review your order</a></b> at any time.</p>';
			echo '</div>';
			echo '<div class="card-buttons">';
				echo '<a href="register.php" role="button" class="a-button register-button">Start a New Registration</a>';
			echo '</div>';
		echo '</div>';
		render_registration_tail();
	}
} else {
	header('Location: index.php');
	exit(0);
}
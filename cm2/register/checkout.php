<?php

require_once dirname(__FILE__).'/../lib/util/util.php';
require_once dirname(__FILE__).'/../lib/util/slack.php';
require_once dirname(__FILE__).'/../lib/util/paypal.php';
require_once dirname(__FILE__).'/register.php';

$site_url = get_site_url(true);

if (!$_GET) {
	if (!cm_reg_cart_check_state('ready')) {
		header('Location: index.php');
		exit(0);
	}

	$group_uuid = $db->uuid();
	$total_price = cm_reg_cart_total();
	$payment_date = $db->now();
	$attendee_ids = array();
	$blacklisted = false;
	for ($i = 0, $n = cm_reg_cart_count(); $i < $n; $i++) {
		$item = cm_reg_cart_get($i);
		$item['payment-group-uuid'] = $group_uuid;
		$item['payment-txn-id'] = $group_uuid;
		$item['payment-txn-amt'] = $total_price;
		$item['payment-date'] = $payment_date;
		if (isset($item['addons']) && $item['addons']) {
			for ($j = 0, $m = count($item['addons']); $j < $m; $j++) {
				$item['addons'][$j]['payment-status'] = 'Incomplete';
				$item['addons'][$j]['payment-txn-id'] = $group_uuid;
				$item['addons'][$j]['payment-txn-amt'] = $total_price;
				$item['addons'][$j]['payment-date'] = $payment_date;
			}
		}
		$attendee_ids[] = $atdb->create_attendee($item, $fdb);
		if ($atdb->is_blacklisted($item)) $blacklisted = true;
	}

	if ($blacklisted) {
		foreach ($attendee_ids as $id) {
			$atdb->update_payment_status($id, 'Incomplete', 'Blacklisted', $group_uuid, 'Blacklisted');
			$attendee = $atdb->get_attendee($id, false, $name_map, $fdb);
		}
		cm_reg_cart_destroy();

		if ($contact_address) {
			$body = 'The following attendee registrations were just blacklisted:'."\r\n";
			foreach ($attendee_ids as $id) {
				$body .= "\r\n".$site_url.'/admin/attendee/edit.php?id='.$id;
			}
			mail(
				$contact_address, 'Blacklisted Attendee Registration',
				$body, 'From: '.$contact_address
			);
		}

		$slack = new cm_slack();
		if ($slack->get_hook_url('attendee-blacklisted')) {
			$body = 'The following attendee registrations were just blacklisted:';
			foreach ($attendee_ids as $id) {
				$body .= ' '.$slack->make_link($site_url.'/admin/attendee/edit.php?id='.$id, 'A'.$id);
			}
			$slack->post_message('attendee-blacklisted', $body);
		}

		cm_reg_message(
			'Could Not Complete Registration',
			'blacklisted',
			'We\'re sorry, there was an issue with your registration '.
			'and your registration could not be completed.<br><br>'.
			'If you think this is an error, please '.
			'<b><a href="mailto:[[contact-address]]">contact us</a></b>.',
			$attendee
		);
		exit(0);
	}

	if ($total_price <= 0) {
		foreach ($attendee_ids as $id) {
			$atdb->update_payment_status($id, 'Completed', 'Free Ride', $group_uuid, 'Free Ride');
			$attendee = $atdb->get_attendee($id, false, $name_map, $fdb);
			$template = $mdb->get_mail_template('attendee-paid');
			$mdb->send_mail($attendee['email-address'], $template, $attendee);
		}
		cm_reg_cart_destroy();

		cm_reg_message(
			'Payment Complete',
			'payment-complete',
			'Your payment has been accepted.<br><br>'.
			'You can <b><a href="[[review-link]]">review your order</a></b> at any time.',
			$attendee
		);
		exit(0);
	}

	if ($_SESSION['payment_method'] == 'cash') {
		foreach ($attendee_ids as $id) {
			$atdb->update_payment_status($id, 'Incomplete', 'Cash', $group_uuid, 'Cash');
			$attendee = $atdb->get_attendee($id, false, $name_map, $fdb);
			$template = $mdb->get_mail_template('attendee-paid');
			$mdb->send_mail($attendee['email-address'], $template, $attendee);
		}
		cm_reg_cart_destroy();

		cm_reg_message(
			'Registration Complete',
			'registration-complete',
			'Your registration has been submitted. You will need to pay at the door.<br><br>'.
			'You can <b><a href="[[review-link]]">review your order</a></b> at any time.',
			$attendee
		);
		exit(0);
	}

	if ($_SESSION['payment_method'] == 'paypal') {
		$paypal = new cm_paypal();
		$token = $paypal->get_token();

		$items = array();
		for ($i = 0, $n = cm_reg_cart_count(); $i < $n; $i++) {
			$item = cm_reg_cart_get($i);
			$badge_type_id = (int)$item['badge-type-id'];
			$badge_type_name = isset($name_map[$badge_type_id]) ? $name_map[$badge_type_id] : $badge_type_id;
			$items[] = $paypal->create_item($badge_type_name, $item['payment-promo-price']);
			if (isset($item['addons']) && $item['addons']) {
				foreach ($item['addons'] as $addon) {
					$items[] = $paypal->create_item($addon['name'], $addon['price']);
				}
			}
		}
		$total = $paypal->create_total($total_price);
		$txn = $paypal->create_transaction($items, $total);

		$payment = $paypal->create_payment_pp(
			$site_url.'/register/checkout.php?return',
			$site_url.'/register/checkout.php?cancel',
			$txn
		);
		$url = $paypal->get_payment_approval_url($payment);
		if (!$url) {
			cm_reg_message(
				'Communication Failure',
				'communication-failure',
				'Failed to communicate with PayPal.<br><br>'.
				'If you are the site administrator, check the '.
				'config file and/or your version of OpenSSL.<br><br>'.
				'If you are not the site administrator, please '.
				'<b><a href="mailto:[[contact-address]]">contact us</a></b> '.
				'and report this error.'
			);
			exit(0);
		}

		$_SESSION['group_uuid'] = $group_uuid;
		$_SESSION['attendee_ids'] = $attendee_ids;
		$_SESSION['paypal_token'] = $token;
		$_SESSION['payment_id'] = $payment['id'];
		cm_reg_cart_set_state('approval');
		header('Location: ' . $url);
		exit(0);
	}

	header('Location: index.php');
	exit(0);
}

if (isset($_GET['return'])) {
	if (!cm_reg_cart_check_state('approval')) {
		header('Location: index.php');
		exit(0);
	}

	$group_uuid = $_SESSION['group_uuid'];
	$attendee_ids = $_SESSION['attendee_ids'];
	$token = $_SESSION['paypal_token'];
	$paypal = new cm_paypal($token);

	$payment_id = $_SESSION['payment_id'];
	$payer_id = isset($_GET['PayerID']) ? $_GET['PayerID'] : null;
	$sale = $paypal->execute_payment($payment_id, $payer_id);
	$transaction_id = $paypal->get_transaction_id($sale);
	$details = json_encode($sale);

	if ($transaction_id) {
		foreach ($attendee_ids as $id) {
			$atdb->update_payment_status($id, 'Completed', 'PayPal', $transaction_id, $details);
			$attendee = $atdb->get_attendee($id, false, $name_map, $fdb);
			$template = $mdb->get_mail_template('attendee-paid');
			$mdb->send_mail($attendee['email-address'], $template, $attendee);
		}
		cm_reg_cart_destroy();

		cm_reg_message(
			'Payment Complete',
			'payment-complete',
			'Your payment has been accepted.<br><br>'.
			'You can <b><a href="[[review-link]]">review your order</a></b> at any time.',
			$attendee
		);
		exit(0);
	} else {
		foreach ($attendee_ids as $id) {
			$atdb->update_payment_status($id, 'Rejected', 'PayPal', $group_uuid, $details);
			$attendee = $atdb->get_attendee($id, false, $name_map, $fdb);
		}
		cm_reg_cart_destroy();

		cm_reg_message(
			'Payment Refused',
			'payment-refused',
			'PayPal has refused this transaction.<br><br>'.
			'PayPal says: [[payment-txn-msg]]<br><br>'.
			'Unfortunately, that is all we know. Please try again later.',
			array_merge($attendee, array('payment-txn-msg' => $sale['message']))
		);
		exit(0);
	}
}

if (isset($_GET['cancel'])) {
	if (!cm_reg_cart_check_state('approval')) {
		header('Location: index.php');
		exit(0);
	}

	$group_uuid = $_SESSION['group_uuid'];
	$attendee_ids = $_SESSION['attendee_ids'];

	foreach ($attendee_ids as $id) {
		$atdb->update_payment_status($id, 'Cancelled', 'PayPal', $group_uuid, 'Cancelled');
		$attendee = $atdb->get_attendee($id, false, $name_map, $fdb);
	}
	cm_reg_cart_destroy();

	cm_reg_message(
		'Payment Cancelled',
		'payment-cancelled',
		'You have cancelled your payment.',
		$attendee
	);
	exit(0);
}

header('Location: index.php');
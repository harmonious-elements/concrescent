<?php

require_once dirname(__FILE__).'/application.php';
require_once dirname(__FILE__).'/../lib/dal/mail.php';

$conn = get_db_connection();
db_require_table('eventlet_badges', $conn);
db_require_table('eventlets', $conn);
db_require_table('eventlet_staffers', $conn);
db_require_table('attendees', $conn);
$badge_names = get_eventlet_badge_names($conn);
$eventlet_info = get_eventlet_info($conn, $badge_names);

function create_cart(&$cart, $eventlet, $eventlet_info, $conn) {
	$badge = null;
	$badge_results = mysql_query('SELECT * FROM '.db_table_name('eventlet_badges').' WHERE `id` = '.$eventlet['badge_id'], $conn);
	if ($badge_result = mysql_fetch_assoc($badge_results)) {
		$badge = decode_eventlet_badge($badge_result);
	}
	$staffers = array();
	$staffer_results = mysql_query('SELECT * FROM '.db_table_name('eventlet_staffers').' WHERE `eventlet_id` = '.$eventlet['id'].' ORDER BY `id`', $conn);
	while ($staffer_result = mysql_fetch_assoc($staffer_results)) {
		$staffers[] = decode_eventlet_staffer($staffer_result, $eventlet_info);
	}
	$baseprice = $badge['price_per_eventlet'];
	$stafferprice = $badge['price_per_staffer'];
	$staffersfree = $badge['staffers_in_eventlet_price'];
	$eventlet['is_eventlet'] = true;
	$eventlet['display_name'] = $eventlet['eventlet_name'];
	$eventlet['payment_original_price'] = $baseprice;
	$eventlet['payment_original_price_string'] = price_string($baseprice);
	$eventlet['payment_final_price'] = $baseprice;
	$eventlet['payment_final_price_string'] = price_string($baseprice);
	$totalprice = $baseprice;
	for ($i = 0; $i < count($staffers); $i++) {
		$price = ($i < $staffersfree) ? 0 : $stafferprice;
		$staffers[$i]['application_status'] = $eventlet['application_status'];
		$staffers[$i]['application_status_string'] = $eventlet['application_status_string'];
		$staffers[$i]['payment_status'] = $eventlet['payment_status'];
		$staffers[$i]['payment_status_string'] = $eventlet['payment_status_string'];
		$staffers[$i]['payment_original_price'] = $price;
		$staffers[$i]['payment_original_price_string'] = price_string($price);
		$staffers[$i]['payment_final_price'] = $price;
		$staffers[$i]['payment_final_price_string'] = price_string($price);
		$totalprice += $price;
	}
	$maxpreregdiscount = $badge['max_prereg_discount'];
	switch ($maxpreregdiscount) {
		case 'StafferPrice': $maxpreregdiscount = $stafferprice; break;
		case 'EventletPrice': $maxpreregdiscount = $baseprice; break;
		case 'TotalPrice': $maxpreregdiscount = $totalprice; break;
		default: $maxpreregdiscount = 0; break;
	}
	for ($i = 0; $i < count($staffers); $i++) {
		if ($staffers[$i]['attendee_id']) {
			$attendee_results = mysql_query('SELECT * FROM '.db_table_name('attendees').' WHERE `id` = '.$staffers[$i]['attendee_id'].' AND `payment_status` = \'Completed\'', $conn);
			if ($attendee_result = mysql_fetch_assoc($attendee_results)) {
				$discount = min((float)$attendee_result['payment_final_price'], $maxpreregdiscount, $totalprice);
				$price = $staffers[$i]['payment_original_price'] - $discount;
				$staffers[$i]['payment_final_price'] = $price;
				$staffers[$i]['payment_final_price_string'] = price_string($price);
				$totalprice -= $discount;
			}
		}
	}
	$cart[] = $eventlet;
	for ($i = 0; $i < count($staffers); $i++) {
		$cart[] = $staffers[$i];
	}
}

function render_cart($cart) {
	echo '<table border="0" cellpadding="0" cellspacing="0" class="cart">';
		echo '<thead>';
			echo '<tr>';
				echo '<th>Item</th>';
				echo '<th class="td-numeric">Price</th>';
				echo '<th>Application Status</th>';
				echo '<th>Payment Status</th>';
			echo '</tr>';
		echo '</thead>';
		echo '<tbody>';
			$total = 0;
			foreach ($cart as $id => $item) {
				$total += $item['payment_final_price'];
				echo '<tr>';
					echo '<td>';
						echo '<b>' . htmlspecialchars($item['display_name']) . '</b>';
						echo '<br>' . htmlspecialchars($item['badge_name']);
						if ($item['is_eventlet']) {
							echo ' - Panel/Activity Registration Fee';
						} else {
							echo ' - Panelist/Host Badge Registration Fee';
						}
					echo '</td>';
					echo '<td class="td-numeric">';
						if ($item['payment_final_price'] != $item['payment_original_price']) {
							echo '<s>&nbsp;'.htmlspecialchars($item['payment_original_price_string']).'&nbsp;</s>';
							echo '<br><b>&nbsp;'.htmlspecialchars($item['payment_final_price_string']).'&nbsp;</b>';
						} else {
							echo htmlspecialchars($item['payment_final_price_string']);
						}
					echo '</td>';
					echo '<td class="application-status application-status-';
						echo htmlspecialchars(strtolower($item['application_status']));
						echo '">'.htmlspecialchars($item['application_status_string']);
					echo '</td>';
					echo '<td class="payment-status payment-status-';
						echo htmlspecialchars(strtolower($item['payment_status']));
						echo '">'.htmlspecialchars($item['payment_status_string']);
					echo '</td>';
				echo '</tr>';
			}
		echo '</tbody>';
		echo '<tfoot>';
			echo '<tr>';
				echo '<th>Total:</th>';
				echo '<th class="td-numeric">'.price_string($total).'</th>';
				echo '<th colspan="2"></th>';
			echo '</tr>';
		echo '</tfoot>';
	echo '</table>';
}

if (isset($_POST['action'])) {
	switch ($_POST['action']) {
		case 'checkout':
			$id = isset($_POST['id']) ? trim($_POST['id']) : null;
			$key = isset($_POST['key']) ? trim($_POST['key']) : null;
			if ($id && $key) {
				$cart = array();
				$results = mysql_query(
					('SELECT * FROM '.db_table_name('eventlets').
					' WHERE `id` = '.q_int($id).
					' AND `application_status` = \'Accepted\''.
					' AND (`payment_status` = \'Incomplete\' OR `payment_status` = \'Cancelled\')'.
					' AND `payment_lookup_key` = '.q_string($key).
					' ORDER BY `id`'),
					$conn
				);
				while ($result = mysql_fetch_assoc($results)) {
					create_cart($cart, decode_eventlet($result, $badge_names), $eventlet_info, $conn);
				}
				if (count($cart)) {
					$_SESSION['cart'] = $cart;
					$_SESSION['cart_hash'] = md5(serialize($cart));
					$_SESSION['cart_state'] = 'ready';
					header('Location: paypal_checkout.php');
					exit(0);
				}
			}
			break;
	}
}

$id = isset($_GET['id']) ? trim($_GET['id']) : null;
$txn = isset($_GET['txn']) ? trim($_GET['txn']) : null;
$key = isset($_GET['key']) ? trim($_GET['key']) : null;

if ($id && !$txn && $key) {
	$cart = array();
	$results = mysql_query(
		('SELECT * FROM '.db_table_name('eventlets').
		' WHERE `id` = '.q_int($id).
		' AND (`application_status` = \'Accepted\' OR (`application_status` = \'Cancelled\' AND NOT (`replaced_by` IS NULL OR `replaced_by` = 0)))'.
		' AND (`payment_status` = \'Incomplete\' OR `payment_status` = \'Cancelled\')'.
		' AND `payment_lookup_key` = '.q_string($key).
		' ORDER BY `id`'),
		$conn
	);
	while ($result = mysql_fetch_assoc($results)) {
		if ($result['replaced_by']) {
			$rr = mysql_query('SELECT * FROM '.db_table_name('eventlets').' WHERE `id` = '.q_int($result['replaced_by']), $conn);
			if ($rr = mysql_fetch_assoc($rr)) {
				header('Location: order.php?id=' . urlencode($rr['id']) . '&key=' . urlencode($rr['payment_lookup_key']));
				exit(0);
			}
		}
		create_cart($cart, decode_eventlet($result, $badge_names), $eventlet_info, $conn);
	}
	if (count($cart)) {
		render_application_head('Panel/Activity Registration Confirmation & Payment');
		render_application_body('Panel/Activity Registration Confirmation & Payment');
		echo '<div class="card">';
			echo '<div class="card-title">Panel/Activity Registration Confirmation & Payment</div>';
			echo '<div class="card-content">';
				echo '<p class="cart-count">';
					echo 'Please review your panel or activity registration below.';
					echo ' Your registration is not complete until you click <b>CONFIRM &amp; PAY</b>.';
					if ($contact = get_mail_contact('eventlet_accepted', $conn)) {
						echo ' If you have any questions, feel free to';
						echo ' <b><a href="mailto:'.htmlspecialchars($contact).'">contact us</a></b>.';
					}
				echo '</p>';
				render_cart($cart);
			echo '</div>';
			echo '<div class="card-buttons">';
				echo '<form action="order.php" method="post">';
					echo '<input type="hidden" name="action" value="checkout">';
					echo '<input type="hidden" name="id" value="' . htmlspecialchars($id) . '">';
					echo '<input type="hidden" name="key" value="' . htmlspecialchars($key) . '">';
					echo '<input type="submit" name="submit" value="Confirm &amp; Pay" class="register-button">';
				echo '</form>';
			echo '</div>';
		echo '</div>';
		render_application_tail();
	} else {
		header('Location: index.php');
		exit(0);
	}
} else if (!$id && $txn && $key) {
	$results = mysql_query(
		('SELECT * FROM '.db_table_name('eventlets').
		' WHERE `payment_txn_id` = '.q_string($txn).
		' AND `payment_lookup_key` = '.q_string($key).
		' ORDER BY `id`'),
		$conn
	);
	if (mysql_fetch_assoc($results)) {
		$cart = array();
		$results = mysql_query(
			('SELECT * FROM '.db_table_name('eventlets').
			' WHERE `payment_txn_id` = '.q_string($txn).
			' ORDER BY `id`'),
			$conn
		);
		while ($result = mysql_fetch_assoc($results)) {
			create_cart($cart, decode_eventlet($result, $badge_names), $eventlet_info, $conn);
		}
		if (count($cart)) {
			render_application_head('Review Order');
			render_application_body('Review Order');
			echo '<div class="card">';
				echo '<div class="card-title">Review Order</div>';
				echo '<div class="card-content">';
					echo '<p class="cart-count">';
						echo 'Here are the details of the <b>'.((count($cart) == 1) ? '1 item' : (count($cart).' items')).'</b>';
						echo ' you ordered on <b>'.htmlspecialchars($cart[0]['payment_date']).'</b>.';
						if ($contact = get_mail_contact('eventlet_paid', $conn)) {
							echo ' If you have any questions, feel free to';
							echo ' <b><a href="mailto:'.htmlspecialchars($contact).'">contact us</a></b>.';
						}
					echo '</p>';
					render_cart($cart);
				echo '</div>';
			echo '</div>';
			render_application_tail();
		} else {
			header('Location: index.php');
			exit(0);
		}
	} else {
		header('Location: index.php');
		exit(0);
	}
} else {
	header('Location: index.php');
	exit(0);
}
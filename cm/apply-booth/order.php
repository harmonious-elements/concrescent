<?php

require_once dirname(__FILE__).'/application.php';
require_once dirname(__FILE__).'/../lib/dal/mail.php';

$conn = get_db_connection();
db_require_table('booth_badges', $conn);
db_require_table('booths', $conn);
db_require_table('booth_staffers', $conn);
db_require_table('attendees', $conn);
$badge_names = get_booth_badge_names($conn);
$booth_info = get_booth_info($conn, $badge_names);

function create_cart(&$cart, $booth, $booth_info, $permit_number, $conn) {
	$badge = null;
	$badge_results = mysql_query('SELECT * FROM '.db_table_name('booth_badges').' WHERE `id` = '.$booth['badge_id'], $conn);
	if ($badge_result = mysql_fetch_assoc($badge_results)) {
		$badge = decode_booth_badge($badge_result);
	}
	$staffers = array();
	$staffer_results = mysql_query('SELECT * FROM '.db_table_name('booth_staffers').' WHERE `booth_id` = '.$booth['id'].' ORDER BY `id`', $conn);
	while ($staffer_result = mysql_fetch_assoc($staffer_results)) {
		$staffers[] = decode_booth_staffer($staffer_result, $booth_info);
	}
	$numtables = $booth['num_tables'];
	$baseprice = $badge['price_per_table'];
	$stafferprice = $badge['price_per_staffer'];
	$staffersfree = $badge['staffers_in_table_price'];
	$booth['is_booth'] = true;
	$booth['display_name'] = $booth['booth_name'];
	$booth['permit_number'] = $permit_number;
	$booth['payment_original_price'] = $baseprice * $numtables;
	$booth['payment_original_price_string'] = price_string($baseprice * $numtables);
	$booth['payment_final_price'] = $baseprice * $numtables;
	$booth['payment_final_price_string'] = price_string($baseprice * $numtables);
	$totalprice = $baseprice * $numtables;
	$totalstaffersfree = $staffersfree * $numtables;
	for ($i = 0; $i < count($staffers); $i++) {
		$price = ($i < $totalstaffersfree) ? 0 : $stafferprice;
		$staffers[$i]['application_status'] = $booth['application_status'];
		$staffers[$i]['application_status_string'] = $booth['application_status_string'];
		$staffers[$i]['payment_status'] = $booth['payment_status'];
		$staffers[$i]['payment_status_string'] = $booth['payment_status_string'];
		$staffers[$i]['payment_original_price'] = $price;
		$staffers[$i]['payment_original_price_string'] = price_string($price);
		$staffers[$i]['payment_final_price'] = $price;
		$staffers[$i]['payment_final_price_string'] = price_string($price);
		$totalprice += $price;
	}
	$maxpreregdiscount = $badge['max_prereg_discount'];
	switch ($maxpreregdiscount) {
		case 'StafferPrice': $maxpreregdiscount = $stafferprice; break;
		case 'TablePrice': $maxpreregdiscount = $baseprice; break;
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
	$cart[] = $booth;
	for ($i = 0; $i < count($staffers); $i++) {
		$cart[] = $staffers[$i];
	}
	return $badge;
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
						if ($item['is_booth']) {
							echo ' - Table Registration Fee';
						} else {
							echo ' - Table Staffer Badge Registration Fee';
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
	$id = isset($_POST['id']) ? trim($_POST['id']) : null;
	$txn = isset($_POST['txn']) ? trim($_POST['txn']) : null;
	$key = isset($_POST['key']) ? trim($_POST['key']) : null;
	$permit_number = isset($_POST['permit_number']) ? trim($_POST['permit_number']) : null;
	$errors = array();
	
	switch ($_POST['action']) {
		case 'checkout':
			if ($id && $key) {
				$cart = array();
				$results = mysql_query(
					('SELECT * FROM '.db_table_name('booths').
					' WHERE `id` = '.q_int($id).
					' AND `application_status` = \'Accepted\''.
					' AND (`payment_status` = \'Incomplete\' OR `payment_status` = \'Cancelled\')'.
					' AND `payment_lookup_key` = '.q_string($key).
					' ORDER BY `id`'),
					$conn
				);
				while ($result = mysql_fetch_assoc($results)) {
					$booth = decode_booth($result, $badge_names);
					$badge = create_cart($cart, $booth, $booth_info, $permit_number, $conn);
					if ($badge['require_permit'] && !$permit_number) {
						$errors['permit_number'] = 'This type of table requires a permit number.';
					}
				}
				if (count($cart) && !count($errors)) {
					$_SESSION['cart'] = $cart;
					$_SESSION['cart_hash'] = md5(serialize($cart));
					$_SESSION['cart_state'] = 'ready';
					header('Location: paypal_checkout.php');
					exit(0);
				}
			}
			break;
	}
} else {
	$id = isset($_GET['id']) ? trim($_GET['id']) : null;
	$txn = isset($_GET['txn']) ? trim($_GET['txn']) : null;
	$key = isset($_GET['key']) ? trim($_GET['key']) : null;
	$permit_number = isset($_GET['permit_number']) ? trim($_GET['permit_number']) : null;
	$errors = array();
}

if ($id && !$txn && $key) {
	$cart = array();
	$results = mysql_query(
		('SELECT * FROM '.db_table_name('booths').
		' WHERE `id` = '.q_int($id).
		' AND (`application_status` = \'Accepted\' OR (`application_status` = \'Cancelled\' AND NOT (`replaced_by` IS NULL OR `replaced_by` = 0)))'.
		' AND (`payment_status` = \'Incomplete\' OR `payment_status` = \'Cancelled\')'.
		' AND `payment_lookup_key` = '.q_string($key).
		' ORDER BY `id`'),
		$conn
	);
	while ($result = mysql_fetch_assoc($results)) {
		if ($result['replaced_by']) {
			$rr = mysql_query('SELECT * FROM '.db_table_name('booths').' WHERE `id` = '.q_int($result['replaced_by']), $conn);
			if ($rr = mysql_fetch_assoc($rr)) {
				header('Location: order.php?id=' . urlencode($rr['id']) . '&key=' . urlencode($rr['payment_lookup_key']));
				exit(0);
			}
		}
		$booth = decode_booth($result, $badge_names);
		$badge = create_cart($cart, $booth, $booth_info, $permit_number, $conn);
	}
	if (count($cart)) {
		render_application_head('Table Registration Confirmation & Payment');
		render_application_body('Table Registration Confirmation & Payment');
		echo '<div class="card">';
			echo '<form action="order.php" method="post">';
				echo '<div class="card-title">Table Registration Confirmation & Payment</div>';
				echo '<div class="card-content spaced">';
					echo '<p>';
						echo 'Please review your table registration below.';
						echo ' Your registration is not complete until you click <b>CONFIRM &amp; PAY</b>.';
						if ($contact = get_mail_contact('booth_accepted', $conn)) {
							echo ' If you have any questions, feel free to';
							echo ' <b><a href="mailto:'.htmlspecialchars($contact).'">contact us</a></b>.';
						}
					echo '</p>';
					render_cart($cart);
					echo '<hr>';
					echo '<p>';
						if ($badge['require_permit']) {
							echo 'This type of table requires a seller\'s permit. Please enter the permit number below.';
						} else {
							echo 'This type of table does not require a seller\'s permit. However, if you have one, you may enter the permit number below.';
						}
					echo '</p>';
					echo '<table class="form">';
						echo '<tr>';
							echo '<th>Permit Number:</th>';
							echo '<td>';
								echo '<input type="text" name="permit_number" value="';
								if ($permit_number) echo htmlspecialchars($permit_number);
								echo '">';
								if (isset($errors['permit_number'])) {
									echo '<span class="error">'.htmlspecialchars($errors['permit_number']).'</span>';
								}
							echo '</td>';
						echo '</tr>';
					echo '</table>';
				echo '</div>';
				echo '<div class="card-buttons">';
					echo '<input type="hidden" name="action" value="checkout">';
					echo '<input type="hidden" name="id" value="' . htmlspecialchars($id) . '">';
					echo '<input type="hidden" name="key" value="' . htmlspecialchars($key) . '">';
					echo '<input type="submit" name="submit" value="Confirm &amp; Pay" class="register-button">';
				echo '</div>';
			echo '</form>';
		echo '</div>';
		render_application_tail();
	} else {
		header('Location: index.php');
		exit(0);
	}
} else if (!$id && $txn && $key) {
	$results = mysql_query(
		('SELECT * FROM '.db_table_name('booths').
		' WHERE `payment_txn_id` = '.q_string($txn).
		' AND `payment_lookup_key` = '.q_string($key).
		' ORDER BY `id`'),
		$conn
	);
	if (mysql_fetch_assoc($results)) {
		$cart = array();
		$results = mysql_query(
			('SELECT * FROM '.db_table_name('booths').
			' WHERE `payment_txn_id` = '.q_string($txn).
			' ORDER BY `id`'),
			$conn
		);
		while ($result = mysql_fetch_assoc($results)) {
			$booth = decode_booth($result, $badge_names);
			$badge = create_cart($cart, $booth, $booth_info, $permit_number, $conn);
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
						if ($contact = get_mail_contact('booth_paid', $conn)) {
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
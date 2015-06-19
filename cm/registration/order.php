<?php

require_once dirname(__FILE__).'/registration.php';
require_once dirname(__FILE__).'/../lib/dal/mail.php';

$txn = isset($_GET['txn']) ? trim($_GET['txn']) : null;
$key = isset($_GET['key']) ? trim($_GET['key']) : null;

if ($txn && $key) {
	$conn = get_db_connection();
	db_require_table('attendee_badges', $conn);
	db_require_table('attendees', $conn);
	$badge_names = get_attendee_badge_names($conn);
	
	$results = mysql_query(
		('SELECT * FROM '.db_table_name('attendees').
		' WHERE `payment_txn_id` = '.q_string($txn).
		' AND `payment_lookup_key` = '.q_string($key).
		' ORDER BY `id`'),
		$conn
	);
	
	if (mysql_fetch_assoc($results)) {
		$cart = array();
		$results = mysql_query(
			('SELECT * FROM '.db_table_name('attendees').
			' WHERE `payment_txn_id` = '.q_string($txn).
			' ORDER BY `id`'),
			$conn
		);
		while ($result = mysql_fetch_assoc($results)) {
			$cart[] = decode_attendee($result, $badge_names);
		}
		
		if (count($cart)) {
			render_registration_head('Review Order');
			render_registration_body('Review Order');
			
			echo '<div class="card">';
				echo '<div class="card-title">Review Order</div>';
				echo '<div class="card-content">';
					echo '<p class="cart-count">';
						echo 'Here are the details of the <b>'.((count($cart) == 1) ? '1 item' : (count($cart).' items')).'</b>';
						echo ' you ordered on <b>'.htmlspecialchars($cart[0]['payment_date']).'</b>.';
						if ($contact = get_mail_contact('attendee_paid', $conn)) {
							echo ' If you have any questions, feel free to';
							echo ' <b><a href="mailto:'.htmlspecialchars($contact).'">contact us</a></b>.';
						}
					echo '</p>';
					echo '<table border="0" cellpadding="0" cellspacing="0" class="cart">';
						echo '<thead>';
							echo '<tr>';
								echo '<th>Item</th>';
								echo '<th class="td-numeric">Price</th>';
								echo '<th>Status</th>';
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
										if ($item['payment_promo_code']) {
											echo '<br><b>Promo Code:</b> '.htmlspecialchars($item['payment_promo_code']);
										}
										if ($errors[$id]) {
											echo '<br><span class="error">'.htmlspecialchars($errors[$id]).'</span>';
										}
									echo '</td>';
									echo '<td class="td-numeric">';
										if ($item['payment_promo_code']) {
											echo '<s>&nbsp;'.htmlspecialchars($item['payment_original_price_string']).'&nbsp;</s>';
											echo '<br><b>&nbsp;'.htmlspecialchars($item['payment_final_price_string']).'&nbsp;</b>';
										} else {
											echo htmlspecialchars($item['payment_final_price_string']);
										}
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
								echo '<th></th>';
							echo '</tr>';
						echo '</tfoot>';
					echo '</table>';
				echo '</div>';
			echo '</div>';
			
			render_registration_tail();
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
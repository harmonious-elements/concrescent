<?php

require_once dirname(__FILE__).'/payment.php';

function render_cart($result) {
	echo '<table border="0" cellpadding="0" cellspacing="0" class="cart">';
		echo '<thead>';
			echo '<tr>';
				echo '<th>Item</th>';
				echo '<th class="td-numeric">Price</th>';
				echo '<th>Payment Status</th>';
			echo '</tr>';
		echo '</thead>';
		echo '<tbody>';
			echo '<tr>';
				echo '<td>';
					echo '<b>' . htmlspecialchars($result['real_name']) . '</b>';
					echo '<br>' . htmlspecialchars($result['name']);
				echo '</td>';
				echo '<td class="td-numeric">';
					echo htmlspecialchars($result['payment_price_string']);
				echo '</td>';
				echo '<td class="payment-status payment-status-';
					echo htmlspecialchars(strtolower($result['payment_status']));
					echo '">'.htmlspecialchars($result['payment_status_string']);
				echo '</td>';
			echo '</tr>';
		echo '</tbody>';
		echo '<tfoot>';
			echo '<tr>';
				echo '<th>Total:</th>';
				echo '<th class="td-numeric">'.$result['payment_price_string'].'</th>';
				echo '<th></th>';
			echo '</tr>';
		echo '</tfoot>';
	echo '</table>';
}

$conn = get_db_connection();

if (isset($_POST['action'])) {
	switch($_POST['action']) {
		case 'checkout':
			$id = isset($_POST['id']) ? trim($_POST['id']) : null;
			$key = isset($_POST['key']) ? trim($_POST['key']) : null;
			if ($id && $key) {
				$result = get_payment_for_payment($id, $key, $conn);
				if ($result) {
					$_SESSION['cart'] = $result;
					$_SESSION['cart_hash'] = md5(serialize($result));
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
	$result = get_payment_for_payment($id, $key, $conn);
	if ($result) {
		render_payment_head('Make a Payment');
		render_payment_body('Make a Payment');
		echo '<div class="card">';
			echo '<div class="card-title">Make a Payment</div>';
			echo '<div class="card-content">';
				echo '<p class="cart-count">';
					echo 'Please review your payment below. Your payment is not complete until you click <b>CONFIRM &amp; PAY</b>.';
					if ($contact = get_mail_contact('payment_requested', $conn)) {
						echo ' If you have any questions, feel free to';
						echo ' <b><a href="mailto:'.htmlspecialchars($contact).'">contact us</a></b>.';
					}
				echo '</p>';
				render_cart($result);
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
	$result = get_payment_for_review($txn, $key, $conn);
	if ($result) {
		render_payment_head('Review Order');
		render_payment_body('Review Order');
		echo '<div class="card">';
			echo '<div class="card-title">Review Order</div>';
			echo '<div class="card-content">';
				echo '<p class="cart-count">';
					echo 'Here are the details of the <b>1 item</b> you ordered on ';
					echo '<b>'.htmlspecialchars($result['payment_date']).'</b>.';
					if ($contact = get_mail_contact('payment_paid', $conn)) {
						echo ' If you have any questions, feel free to';
						echo ' <b><a href="mailto:'.htmlspecialchars($contact).'">contact us</a></b>.';
					}
				echo '</p>';
				render_cart($result);
			echo '</div>';
		echo '</div>';
		render_payment_tail();
	} else {
		header('Location: index.php');
		exit(0);
	}
} else {
	header('Location: index.php');
	exit(0);
}
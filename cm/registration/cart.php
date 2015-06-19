<?php

require_once dirname(__FILE__).'/registration.php';

function build_price_sorter($cart) {
	return function($a, $b) use ($cart) {
		return $cart[$b]['payment_original_price'] - $cart[$a]['payment_original_price'];
	};
}

function apply_promo_code($conn, $badge_names, $code, &$errors) {
	$code = strtoupper(preg_replace('/[^A-Za-z0-9!@#$%&*?]/', '', $code));
	$results = mysql_query(
		('SELECT * FROM '.db_table_name('promo_codes').
		' WHERE `active`'.
		' AND (`start_date` IS NULL OR `start_date` <= CURDATE())'.
		' AND (`end_date` IS NULL OR `end_date` >= CURDATE())'.
		' AND `code` = '.q_string($code)),
		$conn
	);
	if ($result = mysql_fetch_assoc($results)) {
		$result = decode_promo_code($result, $badge_names);
		$applicable_items = array();
		$cart = get_cart();
		foreach ($cart as $id => $item) {
			if ((!$result['badge_id']) || ($result['badge_id'] == $item['badge_id'])) {
				$applicable_items[] = $id;
			}
		}
		if (count($applicable_items)) {
			usort($applicable_items, build_price_sorter($cart));
			if ($result['limit'] && count($applicable_items) > $result['limit']) {
				$applicable_items = array_slice($applicable_items, 0, $result['limit']);
			}
			reset_promo_code();
			foreach ($applicable_items as $id) {
				$item = $cart[$id];
				$original_price = $item['payment_original_price'];
				if ($result['percentage']) {
					$final_price = $original_price * (100.0 - $result['price']) / 100.0;
				} else {
					$final_price = $original_price - $result['price'];
				}
				if ($final_price < 0) $final_price = 0;
				if ($final_price > $original_price) $final_price = $original_price;
				$item['payment_final_price'] = $final_price;
				$item['payment_promo_code'] = $code;
				replace_in_cart($id + 1, $item);
			}
		} else {
			$errors['code'] = 'This promo code does not apply to anything in your cart.';
		}
	} else {
		$errors['code'] = 'This is not a valid promo code.';
	}
}

function checkout_registration($conn, &$errors, $payment_method) {
	$badge_info = get_valid_attendee_badges($conn);
	$cart = get_cart();
	foreach ($cart as $id => $item) {
		$date_of_birth = $item['date_of_birth'];
		$badge_id = $item['badge_id'];
		if (isset($badge_info[$badge_id])) {
			$badge = $badge_info[$badge_id];
			if ($date_of_birth && (
				($badge['min_birthdate'] && $date_of_birth < $badge['min_birthdate']) ||
				($badge['max_birthdate'] && $date_of_birth > $badge['max_birthdate'])
			)) {
				$errors[$id] = 'The badge you selected is no longer applicable.';
			}
		} else {
			$errors[$id] = 'The badge you selected is no longer available.';
		}
	}
	if (!count($errors)) {
		$_SESSION['cart_hash'] = md5(serialize($cart));
		$_SESSION['cart_state'] = 'ready';
		switch ($payment_method) {
		case 'paypal':
			header('Location: paypal_checkout.php');
			break;
		case 'cash':
			header('Location: cash_checkout.php');
			break;
		default:
			header('Location: cart.php');
			break;
		}
		exit(0);
	}
}

$conn = get_db_connection();
db_require_table('attendee_badges', $conn);
db_require_table('attendees', $conn);
db_require_table('promo_codes', $conn);
$badge_names = get_attendee_badge_names($conn);

$errors = array();
if (isset($_POST['action'])) {
	switch ($_POST['action']) {
		case 'redeem':
			apply_promo_code($conn, $badge_names, $_POST['code'], $errors);
			break;
		case 'remove':
			reset_promo_code();
			remove_from_cart((int)$_POST['id']);
			break;
		case 'removeall':
			destroy_cart();
			break;
		case 'checkout':
			checkout_registration($conn, $errors, $_POST['payment_method']);
			break;
	}
}

$cart = get_cart();
if (!count($cart)) {
	render_registration_head('Shopping Cart');
	render_registration_body('Shopping Cart');
	echo '<div class="card">';
		echo '<div class="card-title">Shopping Cart</div>';
		echo '<div class="card-content">';
			echo '<p class="empty-cart">Your shopping cart is empty.</p>';
		echo '</div>';
		echo '<div class="card-buttons right">';
			echo '<a href="register.php" role="button" class="a-button register-button">Add a Badge</a>';
		echo '</div>';
	echo '</div>';
	render_registration_tail();
	exit(0);
}

render_registration_head('Shopping Cart');
render_registration_body('Shopping Cart');

echo '<div class="card">';
	echo '<div class="card-title">Shopping Cart</div>';
	echo '<div class="card-content">';
		echo '<p class="cart-count">';
			echo 'Your shopping cart has <b>'.((count($cart) == 1) ? '1 item' : (count($cart).' items')).'</b>.';
			echo ' Your registration is not complete until you click <b>PLACE ORDER</b>.';
		echo '</p>';
		echo '<table border="0" cellpadding="0" cellspacing="0" class="cart">';
			echo '<thead>';
				echo '<tr>';
					echo '<th>Item</th>';
					echo '<th class="td-numeric">Price</th>';
					echo '<th>Actions</th>';
				echo '</tr>';
			echo '</thead>';
			echo '<tbody>';
				$total = 0;
				foreach ($cart as $id => $item) {
					$total += $item['payment_final_price'];
					echo '<tr>';
						echo '<td>';
							echo '<b>' . htmlspecialchars(attendee_display_name($item)) . '</b>';
							echo '<br>' . htmlspecialchars($badge_names[$item['badge_id']]);
							if ($item['payment_promo_code']) {
								echo '<br><b>Promo Code:</b> '.htmlspecialchars($item['payment_promo_code']);
							}
							if ($errors[$id]) {
								echo '<br><span class="error">'.htmlspecialchars($errors[$id]).'</span>';
							}
						echo '</td>';
						echo '<td class="td-numeric">';
							if ($item['payment_promo_code']) {
								echo '<s>&nbsp;'.htmlspecialchars(price_string($item['payment_original_price'])).'&nbsp;</s>';
								echo '<br><b>&nbsp;'.htmlspecialchars(price_string($item['payment_final_price'])).'&nbsp;</b>';
							} else {
								echo htmlspecialchars(price_string($item['payment_final_price']));
							}
						echo '</td>';
						echo '<td class="td-actions" style="text-align: right;">';
							echo '<a href="register.php?id='.($id + 1).'" role="button" class="a-button edit-button">Edit</a>';
							echo '<form action="cart.php" method="post">';
								echo '<input type="hidden" name="action" value="remove">';
								echo '<input type="hidden" name="id" value="'.($id + 1).'">';
								echo '<input type="submit" name="submit" value="Remove">';
							echo '</form>';
						echo '</td>';
					echo '</tr>';
				}
			echo '</tbody>';
			echo '<tfoot>';
				echo '<tr>';
					echo '<th>Total:</th>';
					echo '<th class="td-numeric">'.price_string($total).'</th>';
					echo '<th class="td-actions" style="text-align: right;">';
						echo '<form action="cart.php" method="post">';
							echo '<input type="hidden" name="action" value="removeall">';
							echo '<input type="submit" name="submit" value="Remove All">';
						echo '</form>';
					echo '</th>';
				echo '</tr>';
			echo '</tfoot>';
		echo '</table>';
	echo '</div>';
	echo '<div class="card-buttons right">';
		echo '<a href="register.php" role="button" class="a-button register-button">Add Another Badge</a>';
	echo '</div>';
echo '</div>';

echo '<div class="card">';
	echo '<form action="cart.php" method="post">';
		echo '<div class="card-title">Redeem Promo Code</div>';
		echo '<div class="card-content">';
			echo '<p>';
				echo 'Enter Promo Code:';
				echo '&nbsp;&nbsp;&nbsp;&nbsp;';
				echo '<input type="text" name="code" value="';
				if ($errors['code'] && isset($_POST['code'])) {
					echo htmlspecialchars($_POST['code']);
				}
				echo '">';
				if ($errors['code']) {
					echo '&nbsp;&nbsp;&nbsp;&nbsp;';
					echo '<span class="error">'.htmlspecialchars($errors['code']).'</span>';
				}
			echo '</p>';
			echo '<p>&nbsp;</p>';
			echo '<p>';
				echo 'Only one promo code can be used at a time. ';
				echo 'Also, changing the contents of your shopping cart ';
				echo 'in any way will remove the promo code; you will then ';
				echo 'need to enter the promo code again.';
			echo '</p>';
		echo '</div>';
		echo '<div class="card-buttons right">';
			echo '<input type="hidden" name="action" value="redeem">';
			echo '<input type="submit" name="submit" value="Redeem Code" class="register-button">';
		echo '</div>';
	echo '</form>';
echo '</div>';

echo '<div class="card">';
	echo '<form action="cart.php" method="post">';
		echo '<div class="card-title">Place Order</div>';
		echo '<div class="card-content spaced">';
			echo '<p><b>Please select a payment method:</b></p>';
			echo '<ul style="list-style: none; text-indent: -2em; margin-left: 2em;">';
			$payment_methods =
				(isset($_COOKIE['payment_methods']) && $_COOKIE['payment_methods']) ?
				explode(',', $_COOKIE['payment_methods']) :
				array('paypal', 'cash');
			$first = true;
			if (in_array('paypal', $payment_methods)) {
				echo '<li><label><input type="radio" name="payment_method" value="paypal"';
				if ($first) { echo ' checked="checked"'; $first = false; }
				echo '> Pay with PayPal</label></li>';
			}
			if (in_array('cash', $payment_methods)) {
				echo '<li><label><input type="radio" name="payment_method" value="cash"';
				if ($first) { echo ' checked="checked"'; $first = false; }
				echo '> Pay with cash at the event</label>';
				echo ' (this option should not be used if purchasing a badge type which may not be available on the day of the event)</li>';
			}
			echo '</ul>';
			echo '<p>Your registration is not complete until you click <b>PLACE ORDER</b>.</p>';
		echo '</div>';
		echo '<div class="card-buttons right">';
			echo '<input type="hidden" name="action" value="checkout">';
			echo '<input type="submit" name="submit" value="Place Order" class="register-button">';
		echo '</div>';
	echo '</form>';
echo '</div>';

render_registration_tail();
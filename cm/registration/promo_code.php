<?php

require_once dirname(__FILE__).'/registration.php';

function build_price_sorter($cart) {
	return function($a, $b) use ($cart) {
		return $cart[$b]['payment_original_price'] - $cart[$a]['payment_original_price'];
	};
}

if (isset($_POST['code']) && $_POST['code']) {
	$code = strtoupper(preg_replace('/[^A-Za-z0-9!@#$%&*?]/', '', $_POST['code']));
	$error = null;
	
	$conn = get_db_connection();
	db_require_table('attendee_badges', $conn);
	db_require_table('promo_codes', $conn);
	$badge_names = get_attendee_badge_names($conn);
	
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
			header('Location: cart.php');
			exit(0);
		} else {
			$error = 'This promo code does not apply to anything in your cart.';
		}
	} else {
		$error = 'This is not a valid promo code.';
	}
} else {
	$code = null;
	$error = null;
}

render_registration_head('Redeem Promo Code');
render_registration_body('Redeem Promo Code');

echo '<div class="card">';
	echo '<form action="promo_code.php" method="post">';
		echo '<div class="card-title">Redeem Promo Code</div>';
		echo '<div class="card-content">';
			echo '<p>';
				echo 'Enter Promo Code:';
				echo '&nbsp;&nbsp;&nbsp;&nbsp;';
				echo '<input type="text" name="code" value="'.htmlspecialchars($code).'">';
				if ($error) {
					echo '&nbsp;&nbsp;&nbsp;&nbsp;';
					echo '<span class="error">'.htmlspecialchars($error).'</span>';
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
		echo '<div class="card-buttons">';
			echo '<input type="submit" name="submit" value="Redeem Code" class="register-button">';
		echo '</div>';
	echo '</form>';
echo '</div>';

render_registration_tail();
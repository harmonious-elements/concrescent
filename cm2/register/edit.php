<?php

require_once dirname(__FILE__).'/../lib/util/util.php';
require_once dirname(__FILE__).'/../lib/util/cmforms.php';
require_once dirname(__FILE__).'/register.php';

$onsite_only = isset($_COOKIE['onsite_only']) && $_COOKIE['onsite_only'];
$active_badge_types = $atdb->list_badge_types(true, false, $onsite_only);
$sellable_badge_types = $atdb->list_badge_types(true, true, $onsite_only);
if (!$sellable_badge_types) cm_reg_closed();

$new = !isset($_GET['index']);
$index = $new ? -1 : (int)$_GET['index'];
$item = $new ? array() : cm_reg_cart_get($index);
$errors = array();

if (isset($_POST['submit'])) {
	$item['first-name'] = trim($_POST['first-name']);
	if (!$item['first-name']) $errors['first-name'] = 'First name is required.';
	$item['last-name'] = trim($_POST['last-name']);
	if (!$item['last-name']) $errors['last-name'] = 'Last name is required.';

	$item['fandom-name'] = trim($_POST['fandom-name']);
	$item['name-on-badge'] = $item['fandom-name'] ? trim($_POST['name-on-badge']) : 'Real Name Only';
	$valid_names_on_badge = array(
		'Fandom Name Large, Real Name Small',
		'Real Name Large, Fandom Name Small',
		'Fandom Name Only', 'Real Name Only'
	);
	if (!in_array($item['name-on-badge'], $valid_names_on_badge)) {
		$errors['name-on-badge'] = 'Name on badge is required.';
	}

	$item['date-of-birth'] = parse_date(trim($_POST['date-of-birth']));
	if (!$item['date-of-birth']) $errors['date-of-birth'] = 'Date of birth is required.';
	$item['badge-type-id'] = (int)$_POST['badge-type-id'];
	$found_badge_type = false;
	foreach ($sellable_badge_types as $badge_type) {
		if ($badge_type['id'] == $item['badge-type-id']) {
			$found_badge_type = $badge_type;
			if ($item['date-of-birth'] && (
				($badge['min-birthdate'] && $item['date-of-birth'] < $badge['min-birthdate']) ||
				($badge['max-birthdate'] && $item['date-of-birth'] > $badge['max-birthdate'])
			)) $errors['badge-type-id'] = 'The badge you selected is not applicable.';
		}
	}
	if (!$found_badge_type) {
		$errors['badge-type-id'] = 'The badge you selected is not available.';
	}

	$item['email-address'] = trim($_POST['email-address']);
	if (!$item['email-address']) $errors['email-address'] = 'Email address is required.';
	$item['subscribed'] = isset($_POST['subscribed']) && $_POST['subscribed'];
	$item['phone-number'] = trim($_POST['phone-number']);
	if (!$item['phone-number']) $errors['phone-number'] = 'Phone number is required.';
	else if (strlen($item['phone-number']) < 7) $errors['phone-number'] = 'Phone number is too short.';

	$item['address-1'] = trim($_POST['address-1']);
	if (!$item['address-1']) $errors['address-1'] = 'Address is required.';
	$item['address-2'] = trim($_POST['address-2']);
	$item['city'] = trim($_POST['city']);
	if (!$item['city']) $errors['city'] = 'City is required.';
	$item['state'] = trim($_POST['state']);
	$item['zip-code'] = trim($_POST['zip-code']);
	$item['country'] = trim($_POST['country']);

	$item['ice-name'] = trim($_POST['ice-name']);
	$item['ice-relationship'] = trim($_POST['ice-relationship']);
	$item['ice-email-address'] = trim($_POST['ice-email-address']);
	$item['ice-phone-number'] = trim($_POST['ice-phone-number']);

	$item['payment-status'] = 'Incomplete';
	$item['payment-badge-price'] = $found_badge_type ? $found_badge_type['price'] : 0;
	$item['payment-promo-code'] = null;
	$item['payment-promo-price'] = $found_badge_type ? $found_badge_type['price'] : 0;

	$item['form-answers'] = array();
	foreach ($questions as $question) {
		if ($question['active'] && $fdb->question_is_visible($question, $item['badge-type-id'])) {
			$answer = cm_form_posted_answer($question['question-id'], $question['type']);
			$item['form-answers'][$question['question-id']] = $answer;
			if ($fdb->question_is_required($question, $item['badge-type-id']) && !$answer) {
				$errors['form-answer-'.$question['question-id']] = 'This question is required.';
			}
		}
	}

	if (!$errors) {
		cm_reg_cart_reset_promo_code();
		if ($new) cm_reg_cart_add($item);
		else cm_reg_cart_set($index, $item);
		header('Location: cart.php');
		exit(0);
	}
}

cm_reg_head($new ? 'Add Badge' : 'Edit Badge');
cm_reg_body(($new ? 'Add Badge' : 'Edit Badge'), true);

echo '<article>';
	$url = $new ? 'edit.php' : ('edit.php?index=' . $index);
	echo '<form action="' . $url . '" method="post" class="card cm-reg-edit cm-reg-edit-' . ($errors ? 'has' : 'no') . '-errors">';
		echo '<div class="card-title">';
			echo 'Register for ' . htmlspecialchars($event_name);
		echo '</div>';
		echo '<div class="card-content">';
			if ($errors) {
				echo '<div class="cm-error-box">';
					echo '<h2>You\'re not done yet!</h2>';
					echo '<p>';
						echo 'Some information was missing from your registration. ';
						echo 'Please address the issues in red and try submitting again. ';
						echo '<b>Your registration is not complete</b> until you see ';
						echo 'the message &ldquo;Payment Complete.&rdquo;';
					echo '</p>';
				echo '</div>';
			} else {
				echo '<div class="cm-reg-badge-types">';
					echo '<h2>Choose Your Badge Type</h2>';
					echo '<hr>';
					foreach ($active_badge_types as $badge) {
						$sellable = (is_null($badge['quantity-remaining']) || $badge['quantity-remaining'] > 0);
						echo $sellable ? ('<div class="cm-reg-badge-type" id="cm-reg-badge-type-' . (int)$badge['id'] . '">') : '<div>';
						echo '<h2>' . htmlspecialchars($badge['name']) . '</h2>';
						if ($badge['start-date'] || $badge['end-date']) {
							echo '<p><label><b>Dates Available:</b></label> ';
							echo date_range_string($badge['start-date'], $badge['end-date']);
							echo '</p>';
						}
						if ($badge['min-age'] || $badge['max-age']) {
							echo '<p><label><b>For Ages:</b></label> ';
							echo age_range_string($badge['min-age'], $badge['max-age']);
							echo '</p>';
						}
						if (!is_null($badge['quantity'])) {
							echo '<p><label><b>Quantity Available:</b></label> ';
							if ($badge['quantity-remaining'] <= 0) {
								echo '<span class="limited">SOLD OUT!</span>';
							} else if ($badge['quantity-sold'] > 0) {
								echo '<span class="limited">Only ' . $badge['quantity-remaining'] . ' available!</span>';
							} else {
								echo $badge['quantity'];
							}
							echo '</p>';
						}
						echo '<p><label><b>Price:</b></label> ';
						echo price_string($badge['price']);
						echo '</p>';
						if ($badge['description']) {
							echo safe_html_string($badge['description'], true);
						}
						if ($badge['rewards']) {
							echo '<p><label><b>Rewards:</b></label></p>';
							echo '<ul>';
							foreach ($badge['rewards'] as $reward) {
								echo '<li>' . safe_html_string($reward) . '</li>';
							}
							echo '</ul>';
						}
						echo '</div>';
						echo '<hr>';
					}
				echo '</div>';
			}
			echo '<table border="0" cellpadding="0" cellspacing="0" class="cm-form-table">';

				$text = $fdb->get_custom_text('main');
				if ($text) {
					echo '<tr><td colspan="2"><p>' . safe_html_string($text) . '</p></td></tr>';
					echo '<tr><td colspan="2"><hr></td></tr>';
				}
				echo '<tr><td colspan="2"><h2>Personal Information</h2></td></tr>';
				$text = $fdb->get_custom_text('personal');
				if ($text) echo '<tr><td colspan="2"><p>' . safe_html_string($text) . '</p></td></tr>';

				echo '<tr>';
					$value = isset($item['first-name']) ? htmlspecialchars($item['first-name']) : '';
					$error = isset($errors['first-name']) ? htmlspecialchars($errors['first-name']) : '';
					echo '<th><label for="first-name">First Name</label></th>';
					echo '<td><input type="text" id="first-name" name="first-name" value="' . $value . '">';
					if ($error) echo '<span class="error">' . $error . '</span>'; echo '</td>';
				echo '</tr>';

				echo '<tr>';
					$value = isset($item['last-name']) ? htmlspecialchars($item['last-name']) : '';
					$error = isset($errors['last-name']) ? htmlspecialchars($errors['last-name']) : '';
					echo '<th><label for="last-name">Last Name</label></th>';
					echo '<td><input type="text" id="last-name" name="last-name" value="' . $value . '">';
					if ($error) echo '<span class="error">' . $error . '</span>'; echo '</td>';
				echo '</tr>';

				echo '<tr>';
					$value = isset($item['fandom-name']) ? htmlspecialchars($item['fandom-name']) : '';
					$error = isset($errors['fandom-name']) ? htmlspecialchars($errors['fandom-name']) : '';
					echo '<th><label for="fandom-name">Fandom Name</label></th>';
					echo '<td><input type="text" id="fandom-name" name="fandom-name" value="' . $value . '">';
					if ($error) echo '<span class="error">' . $error . '</span>'; echo '</td>';
				echo '</tr>';

				/* TODO */

				echo '<tr><td colspan="2"><hr></td></tr>';
				echo '<tr><td colspan="2"><h2>Contact Information</h2></td></tr>';
				$text = $fdb->get_custom_text('contact');
				if ($text) echo '<tr><td colspan="2"><p>' . safe_html_string($text) . '</p></td></tr>';

				/* TODO */

				$first = true;
				foreach ($questions as $question) {
					if ($question['active']) {
						if ($first) {
							echo '<tr><td colspan="2"><hr></td></tr>';
							echo '<tr><td colspan="2"><h2>Additional Information</h2></td></tr>';
						}
						$answer = (
							isset($item['form-answers']) &&
							isset($item['form-answers'][$question['question-id']]) ?
							$item['form-answers'][$question['question-id']] :
							array()
						);
						echo cm_form_row($question, $answer);
						$first = false;
					}
				}

				echo '<tr><td colspan="2"><hr></td></tr>';
				echo '<tr><td colspan="2"><h2>Emergency Contact Information</h2></td></tr>';
				$text = $fdb->get_custom_text('ice');
				if ($text) echo '<tr><td colspan="2"><p>' . safe_html_string($text) . '</p></td></tr>';

				/* TODO */

			echo '</table>';
		echo '</div>';
		echo '<div class="card-buttons">';
			echo '<input type="submit" name="submit" value="Register">';
		echo '</div>';
	echo '</form>';
echo '</article>';

cm_reg_tail();
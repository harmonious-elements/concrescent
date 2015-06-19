<?php

require_once dirname(__FILE__).'/registration.php';
require_once dirname(__FILE__).'/../lib/base/util.php';
require_once dirname(__FILE__).'/../lib/dal/mail.php';
require_once dirname(__FILE__).'/../lib/dal/questions.php';
require_once dirname(__FILE__).'/../lib/ui/questions.php';

$conn = get_db_connection();
db_require_table('attendee_badges', $conn);
db_require_table('attendee_extension_questions', $conn);
db_require_table('attendees', $conn);
$badge_info = get_valid_attendee_badges($conn);
$badge_info_plus_other_crap = get_valid_attendee_badges_plus_other_crap($conn);
$extension_questions = get_active_extension_questions('attendee', $conn);

$id = 0;
$errors = array();
if (isset($_POST['action'])) {
	$id = (int)$_POST['id'];
	switch ($_POST['action']) {
		case 'save':
			$first_name = trim($_POST['first_name']);
			if (!$first_name) {
				$errors['first_name'] = 'First name is required.';
			}
			$last_name = trim($_POST['last_name']);
			if (!$last_name) {
				$errors['last_name'] = 'Last name is required.';
			}
			$fandom_name = trim($_POST['fandom_name']);
			$name_on_badge = $fandom_name ? trim($_POST['name_on_badge']) : 'RealOnly';
			if (!(
				$name_on_badge == 'FandomReal' ||
				$name_on_badge == 'RealFandom' ||
				$name_on_badge == 'FandomOnly' ||
				$name_on_badge == 'RealOnly'
			)) {
				$errors['name_on_badge'] = 'Name on badge is required.';
			}
			$date_of_birth = parse_date(trim($_POST['date_of_birth']));
			if (!$date_of_birth) {
				$errors['date_of_birth'] = 'Date of birth is required.';
			}
			$badge_id = (int)$_POST['badge_id'];
			if (isset($badge_info[$badge_id])) {
				$badge = $badge_info[$badge_id];
				if ($date_of_birth && (
					($badge['min_birthdate'] && $date_of_birth < $badge['min_birthdate']) ||
					($badge['max_birthdate'] && $date_of_birth > $badge['max_birthdate'])
				)) {
					$errors['badge_id'] = 'The badge you selected is not applicable.';
				}
			} else {
				$errors['badge_id'] = 'The badge you selected is not available.';
			}
			$email_address = trim($_POST['email_address']);
			if (!$email_address) {
				$errors['email_address'] = 'Email address is required.';
			}
			$do_not_spam = !$_POST['on_mailing_list'];
			$on_mailing_list = !$do_not_spam;
			$phone_number = trim($_POST['phone_number']);
			if (!$phone_number) {
				$errors['phone_number'] = 'Phone number is required.';
			} else if (strlen($phone_number) < 7) {
				$errors['phone_number'] = 'Phone number is too short.';
			}
			$address_1 = trim($_POST['address_1']);
			if (!$address_1) {
				$errors['address_1'] = 'Address is required.';
			}
			$address_2 = trim($_POST['address_2']);
			$city = trim($_POST['city']);
			if (!$city) {
				$errors['city'] = 'City is required.';
			}
			$state = trim($_POST['state']);
			$zip_code = trim($_POST['zip_code']);
			$country = trim($_POST['country']);
			$ice_name = trim($_POST['ice_name']);
			$ice_relationship = trim($_POST['ice_relationship']);
			$ice_email_address = trim($_POST['ice_email_address']);
			$ice_phone_number = trim($_POST['ice_phone_number']);
			$payment_original_price = $badge ? $badge['price'] : 0;
			$extension_answers = get_posted_extension_answers($extension_questions, $errors);
			$result = array(
				'first_name' => $first_name,
				'last_name' => $last_name,
				'fandom_name' => $fandom_name,
				'name_on_badge' => $name_on_badge,
				'date_of_birth' => $date_of_birth,
				'badge_id' => $badge_id,
				'email_address' => $email_address,
				'do_not_spam' => $do_not_spam,
				'on_mailing_list' => $on_mailing_list,
				'phone_number' => $phone_number,
				'address_1' => $address_1,
				'address_2' => $address_2,
				'city' => $city,
				'state' => $state,
				'zip_code' => $zip_code,
				'country' => $country,
				'ice_name' => $ice_name,
				'ice_relationship' => $ice_relationship,
				'ice_email_address' => $ice_email_address,
				'ice_phone_number' => $ice_phone_number,
				'payment_original_price' => $payment_original_price,
				'payment_promo_code' => null,
				'payment_final_price' => $payment_original_price,
				'extension_answers' => $extension_answers,
			);
			if (!count($errors)) {
				reset_promo_code();
				replace_in_cart($id, $result);
				header('Location: cart.php');
				exit(0);
			}
			break;
		default:
			$result = get_from_cart($id);
			$extension_answers = $result['extension_answers'];
			break;
	}
} else if (isset($_GET['id'])) {
	$id = (int)$_GET['id'];
	$result = get_from_cart($id);
	$extension_answers = $result['extension_answers'];
} else {
	$result = array();
	$extension_answers = array();
}

function render_attendee_badges($connection) {
	global $badge_info_plus_other_crap;
	echo '<div class="badge-types">';
		echo '<table border="0" cellpadding="0" cellspacing="0">';
			echo '<thead class="badge-types-h"><tr><th colspan="2">Badge Types</th></tr></thead>';
			foreach ($badge_info_plus_other_crap as $id => $badge) {
				echo '<thead class="badge-types-t"><tr><th colspan="2">'.htmlspecialchars($badge['name']).'</th></tr></thead>';
				echo '<tbody>';
					$start_date = $badge['start_date'];
					$end_date = $badge['end_date'];
					if ($start_date || $end_date) {
						echo '<tr>';
							echo '<th>Dates Available:</th>';
							if ($start_date && $end_date) {
								echo '<td>'.htmlspecialchars($start_date).' &mdash; '.htmlspecialchars($end_date).'</td>';
							} else if ($start_date) {
								echo '<td>Starting '.htmlspecialchars($start_date).'</td>';
							} else if ($end_date) {
								echo '<td>Ending '.htmlspecialchars($end_date).'</td>';
							} else {
								echo '<td>Forever</td>';
							}
						echo '</tr>';
					}
					$min_age = $badge['min_age'];
					$max_age = $badge['max_age'];
					if ($min_age || $max_age) {
						echo '<tr>';
							echo '<th>For Ages:</th>';
							if ($min_age && $max_age) {
								echo '<td>'.$min_age.' &mdash; '.$max_age.'</td>';
							} else if ($min_age) {
								echo '<td>'.$min_age.' and over</td>';
							} else if ($max_age) {
								echo '<td>'.$max_age.' and under</td>';
							} else {
								echo '<td>All Ages</td>';
							}
						echo '</tr>';
					}
					$count = $badge['count'];
					if ($count) {
						$purchased = get_purchased_attendee_badge_count($id, $connection);
						echo '<tr>';
							echo '<th>Number Available:</th>';
							echo '<td>';
								if ($purchased >= $count) {
									echo '<b class="limited">SOLD OUT!</b>';
								} else if ($purchased) {
									echo '<b class="limited">Only '.($count-$purchased).' left!</b>';
								} else {
									echo $count;
								}
							echo '</td>';
						echo '</tr>';
					}
					echo '<tr><th>Price:</th><td>'.htmlspecialchars($badge['price_string']).'</td></tr>';
					$description = $badge['description'];
					if ($description) {
						echo '<tr><td colspan="2">' . safe_html_string($description) . '</td></tr>';
					}
				echo '</tbody>';
			}
		echo '</table>';
	echo '</div>';
}

render_registration_head('Register');

echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('jquery.js')) . '"></script>';
echo '<script>';
	echo 'var badge_ids = '.json_encode(array_keys($badge_info)).';';
	echo 'var badge_info = '.json_encode($badge_info).';';
echo '</script>';
echo '<script type="text/javascript" src="register.js"></script>';

render_registration_body('Register');

echo '<div class="card">';
	if (!count($badge_info)) {
		echo '<div class="card-content">';
			echo '<p class="registration-closed">';
				echo 'Registration for <b>'.htmlspecialchars($event_name).'</b> is currently closed.';
				if ($contact = get_mail_contact('attendee_paid', $conn)) {
					echo ' Please <b><a href="mailto:'.htmlspecialchars($contact).'">contact us</a></b> if you have any questions.';
				}
			echo '</p>';
		echo '</div>';
	} else {
		if (!count($errors)) {
			render_attendee_badges($conn);
		}
		echo '<form action="register.php" method="post">';
			echo '<div class="card-title">';
				echo 'Register for ' . htmlspecialchars($event_name);
			echo '</div>';
			echo '<div class="card-content">';
				if (count($errors)) {
					echo '<div class="error-notification">';
					echo '<h2>You\'re not done yet!</h2>';
					echo '<p>';
					echo 'Some information was missing from your registration.';
					echo ' Please address the issues in red and try submitting again.';
					echo ' Your registration is not complete until you see the message &ldquo;Payment Complete.&rdquo;';
					echo '</p>';
					echo '</div>';
				}
				echo '<table border="0" cellpadding="0" cellspacing="0" class="form entity-record attendee-record">';
					echo '<thead><tr><th colspan="2">Attendee Personal Information</th></tr></thead>';
					echo '<tbody class="sh-per">';
						echo '<tr>';
							echo '<th><label for="first_name">First Name:</label></th>';
							echo '<td>';
								echo '<input type="text" name="first_name" value="';
								if ($result) echo htmlspecialchars($result['first_name']);
								echo '">';
								if (isset($errors['first_name'])) {
									echo '<span class="error">'.htmlspecialchars($errors['first_name']).'</span>';
								}
							echo '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th><label for="last_name">Last Name:</label></th>';
							echo '<td>';
								echo '<input type="text" name="last_name" value="';
								if ($result) echo htmlspecialchars($result['last_name']);
								echo '">';
								if (isset($errors['last_name'])) {
									echo '<span class="error">'.htmlspecialchars($errors['last_name']).'</span>';
								}
							echo '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th><label for="fandom_name">Fandom Name:</label></th>';
							echo '<td>';
								echo '<input type="text" name="fandom_name" value="';
								if ($result) echo htmlspecialchars($result['fandom_name']);
								echo '" class="input-fandom-name">';
								if (isset($errors['fandom_name'])) {
									echo '<span class="error">'.htmlspecialchars($errors['fandom_name']).'</span>';
								}
							echo '</td>';
						echo '</tr>';
						echo '<tr class="tr-name-on-badge">';
							echo '<th><label for="name_on_badge">Name on Badge:</label></th>';
							echo '<td>';
								echo '<select name="name_on_badge" class="select-name-on-badge">';
									echo '<option value="FandomReal"';
										if ($result && $result['name_on_badge'] == 'FandomReal') echo ' selected="selected"';
										echo '>Fandom Name Large, Real Name Small</option>';
									echo '<option value="RealFandom"';
										if ($result && $result['name_on_badge'] == 'RealFandom') echo ' selected="selected"';
										echo '>Real Name Large, Fandom Name Small</option>';
									echo '<option value="FandomOnly"';
										if ($result && $result['name_on_badge'] == 'FandomOnly') echo ' selected="selected"';
										echo '>Fandom Name Only</option>';
									echo '<option value="RealOnly"';
										if ($result && $result['name_on_badge'] == 'RealOnly') echo ' selected="selected"';
										echo '>Real Name Only</option>';
								echo '</select>';
								if (isset($errors['name_on_badge'])) {
									echo '<span class="error">'.htmlspecialchars($errors['name_on_badge']).'</span>';
								}
							echo '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th><label for="date_of_birth">Date of Birth:</label></th>';
							echo '<td>';
								echo '<input type="date" name="date_of_birth" value="';
								if ($result) echo htmlspecialchars($result['date_of_birth']);
								echo '" class="input-date-of-birth">';
								if (isset($errors['date_of_birth'])) {
									echo '<span class="error">'.htmlspecialchars($errors['date_of_birth']).'</span>';
								} else if (!ua('Chrome')) {
									echo ' (YYYY-MM-DD)';
								}
							echo '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th><label for="badge_id">Badge Type:</label></th>';
							echo '<td>';
								echo '<select name="badge_id" class="select-badge-id">';
									foreach ($badge_info as $badge_id => $badge) {
										echo '<option value="'.$badge_id.'"';
										if ($result && (int)$result['badge_id'] == $badge_id) echo ' selected="selected"';
										echo '>';
										echo htmlspecialchars($badge['name']);
										echo ' - ';
										echo htmlspecialchars($badge['price_string']);
										echo '</option>';
									}
								echo '</select>';
								if (isset($errors['badge_id'])) {
									echo '<span class="error">'.htmlspecialchars($errors['badge_id']).'</span>';
								}
							echo '</td>';
						echo '</tr>';
						echo '<tr class="tr-badge-description hidden">';
							echo '<th></th>';
							echo '<td><div class="td-badge-description"></div></td>';
						echo '</tr>';
					echo '</tbody>';
					echo '<thead><tr><th colspan="2">Attendee Contact Information</th></tr></thead>';
					echo '<tbody class="sh-con">';
						echo '<tr>';
							echo '<th><label for="email_address">Email Address:</label></th>';
							echo '<td>';
								echo '<input type="email" name="email_address" value="';
								if ($result) echo htmlspecialchars($result['email_address']);
								echo '">';
								if (isset($errors['email_address'])) {
									echo '<span class="error">'.htmlspecialchars($errors['email_address']).'</span>';
								}
							echo '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th></th>';
							echo '<td>';
								echo '<label><input type="checkbox" name="on_mailing_list" value="1"';
								if (!($result && $result['do_not_spam'])) echo ' checked="checked"';
								echo '>You may contact me with promotional emails.</label>';
								echo '<br>(You can always <a href="unsubscribe.php" target="_blank"><b>unsubscribe</b></a> at any time.)';
							echo '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th><label for="phone_number">Phone Number:</label></th>';
							echo '<td>';
								echo '<input type="text" name="phone_number" value="';
								if ($result) echo htmlspecialchars($result['phone_number']);
								echo '">';
								if (isset($errors['phone_number'])) {
									echo '<span class="error">'.htmlspecialchars($errors['phone_number']).'</span>';
								}
							echo '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th><label for="address_1">Street Address:</label></th>';
							echo '<td>';
								echo '<input type="text" name="address_1" value="';
								if ($result) echo htmlspecialchars($result['address_1']);
								echo '">';
								if (isset($errors['address_1'])) {
									echo '<span class="error">'.htmlspecialchars($errors['address_1']).'</span>';
								}
							echo '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th><label for="address_2">Address Line 2:</label></th>';
							echo '<td>';
								echo '<input type="text" name="address_2" value="';
								if ($result) echo htmlspecialchars($result['address_2']);
								echo '">';
								if (isset($errors['address_2'])) {
									echo '<span class="error">'.htmlspecialchars($errors['address_2']).'</span>';
								}
							echo '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th><label for="city">City:</label></th>';
							echo '<td>';
								echo '<input type="text" name="city" value="';
								if ($result) echo htmlspecialchars($result['city']);
								echo '">';
								if (isset($errors['city'])) {
									echo '<span class="error">'.htmlspecialchars($errors['city']).'</span>';
								}
							echo '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th><label for="state">State or Province:</label></th>';
							echo '<td>';
								echo '<input type="text" name="state" value="';
								if ($result) echo htmlspecialchars($result['state']);
								echo '">';
								if (isset($errors['state'])) {
									echo '<span class="error">'.htmlspecialchars($errors['state']).'</span>';
								}
							echo '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th><label for="zip_code">ZIP or Postal Code:</label></th>';
							echo '<td>';
								echo '<input type="text" name="zip_code" value="';
								if ($result) echo htmlspecialchars($result['zip_code']);
								echo '">';
								if (isset($errors['zip_code'])) {
									echo '<span class="error">'.htmlspecialchars($errors['zip_code']).'</span>';
								}
							echo '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th><label for="country">Country:</label></th>';
							echo '<td>';
								echo '<input type="text" name="country" value="';
								if ($result) echo htmlspecialchars($result['country']);
								echo '">';
								if (isset($errors['country'])) {
									echo '<span class="error">'.htmlspecialchars($errors['country']).'</span>';
								}
							echo '</td>';
						echo '</tr>';
					echo '</tbody>';
					if (count($extension_questions)) {
						echo '<thead><tr><th colspan="2">Additional Information</th></tr></thead>';
						echo '<tbody class="sh-ext">';
						echo render_extension_answers_form($extension_questions, $extension_answers, $errors);
						echo '</tbody>';
					}
					echo '<thead><tr><th colspan="2">Emergency Contact Information</th></tr></thead>';
					echo '<tbody class="sh-ice">';
						echo '<tr>';
							echo '<th><label for="ice_name">Emergency Contact Name:</label></th>';
							echo '<td>';
								echo '<input type="text" name="ice_name" value="';
								if ($result) echo htmlspecialchars($result['ice_name']);
								echo '">';
								if (isset($errors['ice_name'])) {
									echo '<span class="error">'.htmlspecialchars($errors['ice_name']).'</span>';
								}
							echo '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th><label for="ice_relationship">Emergency Contact Relationship:</label></th>';
							echo '<td>';
								echo '<input type="text" name="ice_relationship" value="';
								if ($result) echo htmlspecialchars($result['ice_relationship']);
								echo '">';
								if (isset($errors['ice_relationship'])) {
									echo '<span class="error">'.htmlspecialchars($errors['ice_relationship']).'</span>';
								}
							echo '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th><label for="ice_email_address">Emergency Contact Email Address:</label></th>';
							echo '<td>';
								echo '<input type="text" name="ice_email_address" value="';
								if ($result) echo htmlspecialchars($result['ice_email_address']);
								echo '">';
								if (isset($errors['ice_email_address'])) {
									echo '<span class="error">'.htmlspecialchars($errors['ice_email_address']).'</span>';
								}
							echo '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th><label for="ice_phone_number">Emergency Contact Phone Number:</label></th>';
							echo '<td>';
								echo '<input type="text" name="ice_phone_number" value="';
								if ($result) echo htmlspecialchars($result['ice_phone_number']);
								echo '">';
								if (isset($errors['ice_phone_number'])) {
									echo '<span class="error">'.htmlspecialchars($errors['ice_phone_number']).'</span>';
								}
							echo '</td>';
						echo '</tr>';
					echo '</tbody>';
				echo '</table>';
			echo '</div>';
			echo '<div class="card-buttons">';
				echo '<input type="hidden" name="action" value="save">';
				echo '<input type="hidden" name="id" value="'.$id.'">';
				echo '<input type="submit" name="submit" value="Register" class="register-button">';
			echo '</div>';
		echo '</form>';
	}
echo '</div>';

render_registration_tail();
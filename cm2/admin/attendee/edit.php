<?php

require_once dirname(__FILE__).'/../../lib/database/attendee.php';
require_once dirname(__FILE__).'/../../lib/database/forms.php';
require_once dirname(__FILE__).'/../../lib/database/mail.php';
require_once dirname(__FILE__).'/../../lib/util/util.php';
require_once dirname(__FILE__).'/../../lib/util/cmforms.php';
require_once dirname(__FILE__).'/../admin.php';

cm_admin_check_permission('attendees', array('||', 'attendees-view', 'attendees-edit'));
$can_edit = $adb->user_has_permission($admin_user, 'attendees-edit');

$atdb = new cm_attendee_db($db);
$name_map = $atdb->get_badge_type_name_map();

$fdb = new cm_forms_db($db, 'attendee');
$questions = $fdb->list_questions();

$new = !isset($_GET['id']);
$id = $new ? -1 : (int)$_GET['id'];
$item = $new ? array() : $atdb->get_attendee($id, false, $name_map, $fdb);
$submitted = $can_edit && isset($_POST['submit']);
$changed = false;

if ($submitted) {
	/* Basic Information */
	$item['first-name'] = trim($_POST['first-name']);
	$item['last-name'] = trim($_POST['last-name']);
	$item['fandom-name'] = trim($_POST['fandom-name']);
	$item['name-on-badge'] = trim($_POST['name-on-badge']);
	$item['date-of-birth'] = parse_date(trim($_POST['date-of-birth']));
	$item['badge-type-id'] = (int)$_POST['badge-type-id'];
	$item['email-address'] = trim($_POST['email-address']);
	$item['subscribed'] = isset($_POST['subscribed']) && $_POST['subscribed'];
	$item['phone-number'] = trim($_POST['phone-number']);
	$item['address-1'] = trim($_POST['address-1']);
	$item['address-2'] = trim($_POST['address-2']);
	$item['city'] = trim($_POST['city']);
	$item['state'] = trim($_POST['state']);
	$item['zip-code'] = trim($_POST['zip-code']);
	$item['country'] = trim($_POST['country']);
	$item['ice-name'] = trim($_POST['ice-name']);
	$item['ice-relationship'] = trim($_POST['ice-relationship']);
	$item['ice-email-address'] = trim($_POST['ice-email-address']);
	$item['ice-phone-number'] = trim($_POST['ice-phone-number']);
	$item['notes'] = $_POST['notes'];
	/* Payment Information */
	if (
		$new
		|| (        $item['payment-status'     ] !=        $_POST['payment-status'     ] )
		|| ( (float)$item['payment-badge-price'] != (float)$_POST['payment-badge-price'] )
		|| (        $item['payment-promo-code' ] !=        $_POST['payment-promo-code' ] )
		|| ( (float)$item['payment-promo-price'] != (float)$_POST['payment-promo-price'] )
		|| (        $item['payment-type'       ] !=        $_POST['payment-type'       ] )
		|| (        $item['payment-txn-id'     ] !=        $_POST['payment-txn-id'     ] )
		|| ( (float)$item['payment-txn-amt'    ] != (float)$_POST['payment-txn-amt'    ] )
		|| (        $item['payment-details'    ] !=        $_POST['payment-details'    ] )
	) {
		$item['payment-status'] = trim($_POST['payment-status']);
		$item['payment-badge-price'] = (float)$_POST['payment-badge-price'];
		$item['payment-promo-code'] = trim($_POST['payment-promo-code']);
		$item['payment-promo-price'] = (float)$_POST['payment-promo-price'];
		$item['payment-type'] = trim($_POST['payment-type']);
		$item['payment-txn-id'] = trim($_POST['payment-txn-id']);
		$item['payment-txn-amt'] = (float)$_POST['payment-txn-amt'];
		$item['payment-details'] = $_POST['payment-details'];
		$item['payment-group-uuid'] = $db->uuid();
		$item['payment-date'] = $db->now();
	}
	/* Custom Questions */
	$item['form-answers'] = array();
	foreach ($questions as $question) {
		if ($question['active']) {
			$answer = cm_form_posted_answer($question['question-id'], $question['type']);
			$item['form-answers'][$question['question-id']] = $answer;
		}
	}
	/* Write Changes */
	if ($new) {
		$id = $atdb->create_attendee($item);
		$new = ($id === false);
		$changed = ($id !== false);
	} else {
		$changed = $atdb->update_attendee($item);
	}
	if ($changed) {
		foreach ($item['form-answers'] as $qid => $answer) $fdb->set_answer($id, $qid, $answer);
		if (isset($_POST['print']) && $_POST['print']) $atdb->attendee_printed($id);
		if (isset($_POST['checkin']) && $_POST['checkin']) $atdb->attendee_checked_in($id);
		$item = $atdb->get_attendee($id, false, $name_map, $fdb);
		if (isset($_POST['add-to-blacklist']) && $_POST['add-to-blacklist']) {
			$blacklist_entry = $item;
			$blacklist_entry['added-by'] = trim($_POST['add-to-blacklist-added-by']);
			$atdb->create_blacklist_entry($blacklist_entry);
		}
		if (isset($_POST['resend-email']) && $_POST['resend-email']) {
			$mdb = new cm_mail_db($db);
			$template = $mdb->get_mail_template('attendee-paid');
			$mdb->send_mail($item['email-address'], $template, $item);
		}
	}
}

$name = isset($item['display-name']) ? $item['display-name'] : null;
cm_admin_head($new ? 'Add Attendee' : ($name ? ('Edit Attendee - ' . $name) : 'Edit Attendee'));
echo '<script type="text/javascript" src="edit.js"></script>';
cm_admin_body($new ? 'Add Attendee' : 'Edit Attendee');
cm_admin_nav('attendees');

echo '<article>';
	$url = $new ? 'edit.php' : ('edit.php?id=' . $id);
	echo '<form action="' . $url . '" method="post" class="card cm-reg-edit">';
		echo '<div class="card-content">';
			if ($submitted) {
				if ($changed) {
					echo '<p class="cm-success-box">Changes saved.</p>';
				} else {
					echo '<p class="cm-error-box">Save failed. Please try again.</p>';
				}
			}
			if (($blacklisted = $atdb->is_blacklisted($item))) {
				echo '<div class="cm-error-box">';
					echo '<h1>This record matches an entry on the attendee blacklist.</h1>';
					echo '<p>Please contact an executive staff member before proceeding.</p>';
					if ($blacklisted['added-by']) {
						echo '<p>The point of contact for the matched entry is ';
						echo '<b>' . $blacklisted['added-by'] . '</b>.</p>';
					}
				echo '</div>';
			}
			echo '<table border="0" cellpadding="0" cellspacing="0" class="cm-form-table">';

				echo '<tr><td colspan="2"><h2>Personal Information</h2></td></tr>';

				echo '<tr>';
					$value = isset($item['first-name']) ? htmlspecialchars($item['first-name']) : '';
					echo '<th><label for="first-name">First Name</label></th>';
					echo '<td><input type="text" id="first-name" name="first-name" value="' . $value . '"></td>';
				echo '</tr>';

				echo '<tr>';
					$value = isset($item['last-name']) ? htmlspecialchars($item['last-name']) : '';
					echo '<th><label for="last-name">Last Name</label></th>';
					echo '<td><input type="text" id="last-name" name="last-name" value="' . $value . '"></td>';
				echo '</tr>';

				echo '<tr>';
					$value = isset($item['fandom-name']) ? htmlspecialchars($item['fandom-name']) : '';
					echo '<th><label for="fandom-name">Fandom Name</label></th>';
					echo '<td><input type="text" id="fandom-name" name="fandom-name" value="' . $value . '"></td>';
				echo '</tr>';

				echo '<tr>';
					$value = isset($item['name-on-badge']) ? htmlspecialchars($item['name-on-badge']) : '';
					echo '<th><label for="name-on-badge">Name on Badge</label></th>';
					echo '<td>';
						echo '<select id="name-on-badge" name="name-on-badge">';
							foreach ($atdb->names_on_badge as $nob) {
								$hnob = htmlspecialchars($nob);
								echo '<option value="' . $hnob;
								echo ($value == $hnob) ? '" selected>' : '">';
								echo $hnob . '</option>';
							}
						echo '</select>';
					echo '</td>';
				echo '</tr>';

				echo '<tr>';
					$value = isset($item['date-of-birth']) ? htmlspecialchars($item['date-of-birth']) : '';
					echo '<th><label for="date-of-birth">Date of Birth</label></th>';
					echo '<td><input type="date" id="date-of-birth" name="date-of-birth" value="' . $value . '">';
					if (!ua('Chrome')) echo ' (YYYY-MM-DD)'; echo '</td>';
				echo '</tr>';

				echo '<tr>';
					$value = isset($item['badge-type-id']) ? htmlspecialchars($item['badge-type-id']) : '';
					echo '<th><label for="badge-type-id">Badge Type</label></th>';
					echo '<td>';
						echo '<select id="badge-type-id" name="badge-type-id">';
							$badge_types = $atdb->list_badge_types();
							foreach ($badge_types as $bt) {
								$btid = htmlspecialchars($bt['id']);
								$btname = htmlspecialchars($bt['name']);
								$btprice = htmlspecialchars(price_string($bt['price']));
								echo '<option value="' . $btid;
								echo ($value == $btid) ? '" selected>' : '">';
								echo $btname . ' &mdash; ' . $btprice . '</option>';
							}
						echo '</select>';
					echo '</td>';
				echo '</tr>';

				echo '<tr class="cm-add-to-blacklist">';
					echo '<th></th>';
					echo '<td><label><input type="checkbox" name="add-to-blacklist" value="1">Add to Blacklist</label></td>';
				echo '</tr>';
				echo '<tr class="cm-add-to-blacklist-added-by hidden">';
					echo '<th>Added/Approved By</th>';
					echo '<td><input type="text" id="add-to-blacklist-added-by" name="add-to-blacklist-added-by"></td>';
				echo '</tr>';

				echo '<tr><td colspan="2"><hr></td></tr>';
				echo '<tr><td colspan="2"><h2>Contact Information</h2></td></tr>';

				echo '<tr>';
					$value = isset($item['email-address']) ? htmlspecialchars($item['email-address']) : '';
					echo '<th><label for="email-address">Email Address</label></th>';
					echo '<td><input type="email" id="email-address" name="email-address" value="' . $value . '"></td>';
				echo '</tr>';
				
				echo '<tr>';
					$value = isset($item['subscribed']) ? $item['subscribed'] : true;
					echo '<th></th><td><label>';
						echo '<input type="checkbox" name="subscribed" value="1"' . ($value ? ' checked>' : '>');
						echo 'You may contact me with promotional emails.';
					echo '</label></td>';
				echo '</tr>';

				$value = isset($item['unsubscribe-link']) ? htmlspecialchars($item['unsubscribe-link']) : '';
				if ($value) {
					echo '<tr>';
						echo '<th><label>Unsubscribe Link</label></th>';
						echo '<td><a href="' . $value . '">' . $value . '</a></td>';
					echo '</tr>';
				}

				echo '<tr>';
					$value = isset($item['phone-number']) ? htmlspecialchars($item['phone-number']) : '';
					echo '<th><label for="phone-number">Phone Number</label></th>';
					echo '<td><input type="text" id="phone-number" name="phone-number" value="' . $value . '"></td>';
				echo '</tr>';

				echo '<tr>';
					$value = isset($item['address-1']) ? htmlspecialchars($item['address-1']) : '';
					echo '<th><label for="address-1">Street Address</label></th>';
					echo '<td><input type="text" id="address-1" name="address-1" value="' . $value . '"></td>';
				echo '</tr>';

				echo '<tr>';
					$value = isset($item['address-2']) ? htmlspecialchars($item['address-2']) : '';
					echo '<th></th><td><input type="text" id="address-2" name="address-2" value="' . $value . '"></td>';
				echo '</tr>';

				echo '<tr>';
					$value = isset($item['city']) ? htmlspecialchars($item['city']) : '';
					echo '<th><label for="city">City</label></th>';
					echo '<td><input type="text" id="city" name="city" value="' . $value . '"></td>';
				echo '</tr>';

				echo '<tr>';
					$value = isset($item['state']) ? htmlspecialchars($item['state']) : '';
					echo '<th><label for="state">State or Province</label></th>';
					echo '<td><input type="text" id="state" name="state" value="' . $value . '"></td>';
				echo '</tr>';

				echo '<tr>';
					$value = isset($item['zip-code']) ? htmlspecialchars($item['zip-code']) : '';
					echo '<th><label for="zip-code">ZIP or Postal Code</label></th>';
					echo '<td><input type="text" id="zip-code" name="zip-code" value="' . $value . '"></td>';
				echo '</tr>';

				echo '<tr>';
					$value = isset($item['country']) ? htmlspecialchars($item['country']) : '';
					echo '<th><label for="country">Country</label></th>';
					echo '<td><input type="text" id="country" name="country" value="' . $value . '"></td>';
				echo '</tr>';

				$first = true;
				foreach ($questions as $question) {
					if ($question['active'] && $question['type'] != 'p') {
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

				echo '<tr>';
					$value = isset($item['ice-name']) ? htmlspecialchars($item['ice-name']) : '';
					echo '<th><label for="ice-name">Emergency Contact Name</label></th>';
					echo '<td><input type="text" id="ice-name" name="ice-name" value="' . $value . '"></td>';
				echo '</tr>';

				echo '<tr>';
					$value = isset($item['ice-relationship']) ? htmlspecialchars($item['ice-relationship']) : '';
					echo '<th><label for="ice-relationship">Emergency Contact Relationship</label></th>';
					echo '<td><input type="text" id="ice-relationship" name="ice-relationship" value="' . $value . '"></td>';
				echo '</tr>';

				echo '<tr>';
					$value = isset($item['ice-email-address']) ? htmlspecialchars($item['ice-email-address']) : '';
					echo '<th><label for="ice-email-address">Emergency Contact Email Address</label></th>';
					echo '<td><input type="email" id="ice-email-address" name="ice-email-address" value="' . $value . '"></td>';
				echo '</tr>';

				echo '<tr>';
					$value = isset($item['ice-phone-number']) ? htmlspecialchars($item['ice-phone-number']) : '';
					echo '<th><label for="ice-phone-number">Emergency Contact Phone Number</label></th>';
					echo '<td><input type="text" id="ice-phone-number" name="ice-phone-number" value="' . $value . '"></td>';
				echo '</tr>';

				echo '<tr><td colspan="2"><hr></td></tr>';
				echo '<tr><td colspan="2"><h2>Payment Information</h2></td></tr>';

				echo '<tr>';
					$value = isset($item['payment-status']) ? htmlspecialchars($item['payment-status']) : '';
					echo '<th><label for="payment-status">Payment Status</label></th>';
					echo '<td>';
						echo '<select id="payment-status" name="payment-status">';
							foreach ($atdb->payment_statuses as $ps) {
								$hps = htmlspecialchars($ps);
								echo '<option value="' . $hps;
								echo ($value == $hps) ? '" selected>' : '">';
								echo $hps . '</option>';
							}
						echo '</select>';
					echo '</td>';
				echo '</tr>';

				echo '<tr>';
					$value = isset($item['payment-badge-price']) ? htmlspecialchars($item['payment-badge-price']) : '';
					echo '<th><label for="payment-badge-price">Payment Badge Price</label></th>';
					echo '<td><input type="number" id="payment-badge-price" name="payment-badge-price" value="' . $value . '" min="0" step="0.01"></td>';
				echo '</tr>';

				echo '<tr>';
					$value = isset($item['payment-promo-code']) ? htmlspecialchars($item['payment-promo-code']) : '';
					echo '<th><label for="payment-promo-code">Payment Promo Code</label></th>';
					echo '<td><input type="text" id="payment-promo-code" name="payment-promo-code" value="' . $value . '"></td>';
				echo '</tr>';

				echo '<tr>';
					$value = isset($item['payment-promo-price']) ? htmlspecialchars($item['payment-promo-price']) : '';
					echo '<th><label for="payment-promo-price">Payment Promo Price</label></th>';
					echo '<td><input type="number" id="payment-promo-price" name="payment-promo-price" value="' . $value . '" min="0" step="0.01"></td>';
				echo '</tr>';

				$value = isset($item['payment-group-uuid']) ? htmlspecialchars($item['payment-group-uuid']) : '';
				if ($value) {
					echo '<tr>';
						echo '<th><label>Payment Group UUID</label></th>';
						echo '<td><tt>' . $value . '</tt></td>';
					echo '</tr>';
				}

				echo '<tr>';
					$value = isset($item['payment-type']) ? htmlspecialchars($item['payment-type']) : '';
					echo '<th><label for="payment-type">Payment Type</label></th>';
					echo '<td><input type="text" id="payment-type" name="payment-type" value="' . $value . '"></td>';
				echo '</tr>';

				echo '<tr>';
					$value = isset($item['payment-txn-id']) ? htmlspecialchars($item['payment-txn-id']) : '';
					echo '<th><label for="payment-txn-id">Payment Transaction ID</label></th>';
					echo '<td><input type="text" id="payment-txn-id" name="payment-txn-id" value="' . $value . '"></td>';
				echo '</tr>';

				echo '<tr>';
					$value = isset($item['payment-txn-amt']) ? htmlspecialchars($item['payment-txn-amt']) : '';
					echo '<th><label for="payment-txn-amt">Payment Transaction Amount</label></th>';
					echo '<td><input type="number" id="payment-txn-amt" name="payment-txn-amt" value="' . $value . '" min="0" step="0.01"></td>';
				echo '</tr>';

				$value = isset($item['payment-date']) ? htmlspecialchars($item['payment-date']) : '';
				if ($value) {
					echo '<tr>';
						echo '<th><label>Payment Date</label></th>';
						echo '<td>' . $value . '</td>';
					echo '</tr>';
				}

				echo '<tr>';
					$value = isset($item['payment-details']) ? htmlspecialchars($item['payment-details']) : '';
					echo '<th><label for="payment-details">Payment Details</label></th>';
					echo '<td><textarea id="payment-details" name="payment-details">' . $value . '</textarea></td>';
				echo '</tr>';

				$value = isset($item['review-link']) ? htmlspecialchars($item['review-link']) : '';
				if ($value) {
					echo '<tr>';
						echo '<th><label>Review Order Link</label></th>';
						echo '<td><a href="' . $value . '">' . $value . '</a></td>';
					echo '</tr>';
				}

				echo '<tr>';
					echo '<th></th>';
					echo '<td><label><input type="checkbox" name="resend-email" value="1">Resend Registration Completed Email</label></td>';
				echo '</tr>';

				echo '<tr><td colspan="2"><hr></td></tr>';
				echo '<tr><td colspan="2"><h2>Record Information</h2></td></tr>';

				$value = isset($item['id-string']) ? htmlspecialchars($item['id-string']) : '';
				if ($value) {
					echo '<tr>';
						echo '<th><label>ID Number</label></th>';
						echo '<td>' . $value . '</td>';
					echo '</tr>';
				}

				$value = isset($item['uuid']) ? htmlspecialchars($item['uuid']) : '';
				if ($value) {
					echo '<tr>';
						echo '<th><label>UUID</label></th>';
						echo '<td><tt>' . $value . '</tt></td>';
					echo '</tr>';
				}

				$value = isset($item['qr-data']) ? htmlspecialchars($item['qr-data']) : '';
				if ($value) {
					echo '<tr>';
						echo '<th><label>QR Code</label></th>';
						echo '<td><img src="https://chart.googleapis.com/chart?cht=qr&chs=150x150&chl=' . $value . '"></td>';
					echo '</tr>';
				}

				$value = isset($item['date-created']) ? htmlspecialchars($item['date-created']) : '';
				if ($value) {
					echo '<tr>';
						echo '<th><label>Date Created</label></th>';
						echo '<td>' . $value . '</td>';
					echo '</tr>';
				}

				$value = isset($item['date-modified']) ? htmlspecialchars($item['date-modified']) : '';
				if ($value) {
					echo '<tr>';
						echo '<th><label>Date Modified</label></th>';
						echo '<td>' . $value . '</td>';
					echo '</tr>';
				}

				if ($new) {
					echo '<tr>';
						echo '<th></th>';
						echo '<td><label><input type="checkbox" name="print" value="1">Mark Printed</label></td>';
					echo '</tr>';
				} else {
					$count = isset($item['print-count']) ? htmlspecialchars($item['print-count']) : '';
					$first = isset($item['print-first-time']) ? htmlspecialchars($item['print-first-time']) : '';
					$last = isset($item['print-last-time']) ? htmlspecialchars($item['print-last-time']) : '';
					echo '<tr>';
						echo '<th><label>Printed</label></th>';
						echo '<td>';
							if ($count) {
								echo $count . (($count == 1) ? ' time' : ' times');
								echo '&nbsp;&nbsp;&nbsp;&nbsp;(First: ' . $first . ')';
								echo '&nbsp;&nbsp;&nbsp;&nbsp;(Last: ' . $last . ')';
							} else {
								echo 'never';
							}
							echo '&nbsp;&nbsp;&nbsp;&nbsp;';
							echo '<label><input type="checkbox" name="print" value="1">Mark Printed</label>';
						echo '</td>';
					echo '</tr>';
				}

				if ($new) {
					echo '<tr>';
						echo '<th></th>';
						echo '<td><label><input type="checkbox" name="checkin" value="1">Mark Checked In</label></td>';
					echo '</tr>';
				} else {
					$count = isset($item['checkin-count']) ? htmlspecialchars($item['checkin-count']) : '';
					$first = isset($item['checkin-first-time']) ? htmlspecialchars($item['checkin-first-time']) : '';
					$last = isset($item['checkin-last-time']) ? htmlspecialchars($item['checkin-last-time']) : '';
					echo '<tr>';
						echo '<th><label>Checked In</label></th>';
						echo '<td>';
							if ($count) {
								echo $count . (($count == 1) ? ' time' : ' times');
								echo '&nbsp;&nbsp;&nbsp;&nbsp;(First: ' . $first . ')';
								echo '&nbsp;&nbsp;&nbsp;&nbsp;(Last: ' . $last . ')';
							} else {
								echo 'never';
							}
							echo '&nbsp;&nbsp;&nbsp;&nbsp;';
							echo '<label><input type="checkbox" name="checkin" value="1">Mark Checked In</label>';
						echo '</td>';
					echo '</tr>';
				}

				echo '<tr>';
					$value = isset($item['notes']) ? htmlspecialchars($item['notes']) : '';
					echo '<th><label for="notes">Notes</label></th>';
					echo '<td><textarea id="notes" name="notes">' . $value . '</textarea></td>';
				echo '</tr>';

			echo '</table>';
		echo '</div>';
		if ($can_edit) {
			echo '<div class="card-buttons">';
				echo '<input type="submit" name="submit" value="Save Changes">';
			echo '</div>';
		}
	echo '</form>';
echo '</article>';

cm_admin_dialogs();
cm_admin_tail();
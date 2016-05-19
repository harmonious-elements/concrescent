<?php

require_once dirname(__FILE__).'/../lib/util/util.php';
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
	/* TODO */
	$errors[] = 'derp';
	/* TODO */
}

cm_reg_head($new ? 'Add Badge' : 'Edit Badge');
cm_reg_body(($new ? 'Add Badge' : 'Edit Badge'), true);

echo '<article>';
	$url = $new ? 'edit.php' : ('edit.php?index=' . $index);
	echo '<form action="' . $url . '" method="post" class="card cm-reg-edit">';
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
								echo '<b class="limited">SOLD OUT!</b>';
							} else if ($badge['quantity-sold'] > 0) {
								echo '<b class="limited">Only ' . $badge['quantity-remaining'] . ' available!</b>';
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
				echo '<tr><td colspan="2"><h2>Personal Information</h2></td></tr>';
				echo '<tr><td colspan="2"><h2>Contact Information</h2></td></tr>';
				echo '<tr><td colspan="2"><h2>Additional Information</h2></td></tr>';
				echo '<tr><td colspan="2"><h2>Emergency Contact Information</h2></td></tr>';
				/* TODO */
			echo '</table>';
		echo '</div>';
		echo '<div class="card-buttons">';
			echo '<input type="submit" name="submit" value="Register">';
		echo '</div>';
	echo '</form>';
echo '</article>';

cm_reg_tail();
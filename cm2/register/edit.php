<?php

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
	echo '<form action="' . $url . '" method="post" class="card">';
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
			}
			echo '<table border="0" cellpadding="0" cellspacing="0" class="cm-form-table">';
				echo '<tr><td colspan="2"><h2>Personal Information</h2></td></tr>';
				/* TODO */
			echo '</table>';
		echo '</div>';
		echo '<div class="card-buttons">';
			echo '<input type="submit" name="submit" value="Register">';
		echo '</div>';
	echo '</form>';
echo '</article>';

cm_reg_tail();
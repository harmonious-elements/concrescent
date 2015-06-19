<?php

require_once dirname(__FILE__).'/../lib/common.php';
require_once dirname(__FILE__).'/../lib/attendees.php';
require_once theme_file_path('public.php');

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$found = false;

if ($email) {
	$conn = get_db_connection();
	db_require_table('attendees', $conn);
	mysql_query(
		('UPDATE '.db_table_name('attendees').
		' SET `do_not_spam` = TRUE'.
		' WHERE `email_address` LIKE '.q_string($email)),
		$conn
	);
	$found = mysql_affected_rows($conn);
}

render_head('Unsubscribe');
render_body('Unsubscribe');

echo '<div class="card">';
	echo '<form action="unsubscribe.php" method="post">';
		echo '<div class="card-title">Unsubscribe from Promotional Emails</div>';
		echo '<div class="card-content">';
			echo '<p>';
				echo 'Enter Email Address:';
				echo '&nbsp;&nbsp;&nbsp;&nbsp;';
				$getemail = $email ? $email : (isset($_GET['email']) ? trim($_GET['email']) : '');
				echo '<input type="email" name="email" value="'.htmlspecialchars($getemail).'">';
			echo '</p>';
			if ($email) {
				echo '<p>&nbsp;</p>';
				if ($found) {
					echo '<p>You have been successfully unsubscribed.</p>';
				} else {
					echo '<p class="error">Could not find this email address.</p>';
				}
			}
		echo '</div>';
		echo '<div class="card-buttons">';
			echo '<input type="submit" name="submit" value="Unsubscribe" class="register-button">';
		echo '</div>';
	echo '</form>';
echo '</div>';

render_tail();
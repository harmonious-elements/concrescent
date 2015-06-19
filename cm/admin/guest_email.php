<?php

require_once dirname(__FILE__).'/admin.php';
require_once dirname(__FILE__).'/../lib/dal/mail.php';
require_once dirname(__FILE__).'/../lib/ui/mail.php';

$conn = get_db_connection();

$changed = false;
if (isset($_POST['action'])) {
	switch ($_POST['action']) {
		case 'save':
			set_mail_template('guest_submitted', get_posted_mail_template('guest_submitted'), $conn);
			set_mail_template('guest_accepted' , get_posted_mail_template('guest_accepted' ), $conn);
			set_mail_template('guest_maybe'    , get_posted_mail_template('guest_maybe'    ), $conn);
			set_mail_template('guest_rejected' , get_posted_mail_template('guest_rejected' ), $conn);
			$changed = true;
			break;
	}
}

render_admin_head('Guest Form Letters');
render_admin_body('Guest Form Letters');

echo '<div class="card admin-edit-email">';
	echo '<form action="guest_email.php" method="post">';
		echo '<div class="card-content spaced">';
			if ($changed) echo '<div class="notification">Changes saved.</div>';
			render_mail_editor('guest_submitted', 'Application Submitted' , get_mail_template('guest_submitted', $conn));
			render_mail_editor('guest_accepted' , 'Application Accepted'  , get_mail_template('guest_accepted' , $conn));
			render_mail_editor('guest_maybe'    , 'Application Waitlisted', get_mail_template('guest_maybe'    , $conn));
			render_mail_editor('guest_rejected' , 'Application Rejected'  , get_mail_template('guest_rejected' , $conn));
			echo '<h3>Mail Merge Fields:</h3>';
			echo '<table border="0" cellpadding="0" cellspacing="0">';
				echo '<tr><td><code>[[event_name]]</code></td><td>The name of the event.</tr>';
				echo '<tr><td><code>[[event_date_start]]</code></td><td>The start date of the event.</tr>';
				echo '<tr><td><code>[[event_date_end]]</code></td><td>The end date of the event.</tr>';
				echo '<tr><td><code>[[contact_first_name]]</code></td><td>The primary contact\'s first name.</tr>';
				echo '<tr><td><code>[[contact_last_name]]</code></td><td>The primary contact\'s last name.</tr>';
				echo '<tr><td><code>[[contact_real_name]]</code></td><td>The primary contact\'s first and last name.</tr>';
				echo '<tr><td><code>[[badge_name]]</code></td><td>The type of guest.</tr>';
				echo '<tr><td><code>[[guest_name]]</code></td><td>The name of the guest.</tr>';
			echo '</table>';
			echo '<p>If you do not wish to send out a form letter automatically, you can leave it blank and no email will be sent.</p>';
		echo '</div>';
		echo '<div class="card-buttons">';
			echo '<input type="hidden" name="action" value="save">';
			echo '<input type="submit" name="submit" value="Save Changes">';
		echo '</div>';
	echo '</form>';
echo '</div>';

render_admin_dialogs();
render_admin_tail();
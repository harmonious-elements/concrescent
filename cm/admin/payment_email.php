<?php

require_once dirname(__FILE__).'/admin.php';
require_once dirname(__FILE__).'/../lib/dal/mail.php';
require_once dirname(__FILE__).'/../lib/ui/mail.php';

$conn = get_db_connection();

$changed = false;
if (isset($_POST['action'])) {
	switch ($_POST['action']) {
		case 'save':
			set_mail_template('payment_requested', get_posted_mail_template('payment_requested'), $conn);
			set_mail_template('payment_paid'     , get_posted_mail_template('payment_paid')     , $conn);
			$changed = true;
			break;
	}
}

render_admin_head('Payment Form Letters');
render_admin_body('Payment Form Letters');

echo '<div class="card admin-edit-email">';
	echo '<form action="payment_email.php" method="post">';
		echo '<div class="card-content spaced">';
			if ($changed) echo '<div class="notification">Changes saved.</div>';
			render_mail_editor('payment_requested', 'Payment Requested', get_mail_template('payment_requested', $conn));
			render_mail_editor('payment_paid'     , 'Payment Completed', get_mail_template('payment_paid'     , $conn));
			echo '<h3>Mail Merge Fields:</h3>';
			echo '<table border="0" cellpadding="0" cellspacing="0">';
				echo '<tr><td><code>[[event_name]]</code></td><td>The name of the event.</tr>';
				echo '<tr><td><code>[[event_date_start]]</code></td><td>The start date of the event.</tr>';
				echo '<tr><td><code>[[event_date_end]]</code></td><td>The end date of the event.</tr>';
				echo '<tr><td><code>[[name]]</code></td><td>The short description of the requested payment.</tr>';
				echo '<tr><td><code>[[description]]</code></td><td>The long description of the requested payment.</tr>';
				echo '<tr><td><code>[[first_name]]</code></td><td>The attendee\'s first name.</tr>';
				echo '<tr><td><code>[[last_name]]</code></td><td>The attendee\'s last name.</tr>';
				echo '<tr><td><code>[[real_name]]</code></td><td>The attendee\'s first and last name.</tr>';
				echo '<tr><td><code>[[payment_price_string]]</code></td><td>The requested amount to be paid.</tr>';
				echo '<tr><td><code>[[transaction_id]]</code></td><td>The PayPal transaction ID.</tr>';
				echo '<tr><td><code>[[order_url]]</code></td><td>The URL of the page to make or review a payment.</tr>';
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
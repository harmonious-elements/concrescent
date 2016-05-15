<?php

require_once dirname(__FILE__).'/../../config/config.php';
require_once dirname(__FILE__).'/../../lib/database/attendee.php';
require_once dirname(__FILE__).'/../../lib/database/forms.php';
require_once dirname(__FILE__).'/../../lib/util/util.php';
require_once dirname(__FILE__).'/../../lib/util/cmforms.php';
require_once dirname(__FILE__).'/../admin.php';

cm_admin_check_permission('attendee-questions', 'attendee-questions');

$atdb = new cm_attendee_db($db);
$name_list = $atdb->list_badge_type_names();

$form_def = array(
	'ajax-url' => get_site_url(false) . '/admin/attendee/questions.php',
	'context' => 'attendee',
	'subcontext' => $name_list
);

$fdb = new cm_forms_db($db, $form_def['context']);
cm_form_edit_process_requests($fdb);

cm_admin_head('Attendee Questions');
cm_form_edit_head($form_def);
cm_admin_body('Attendee Questions');
cm_admin_nav('attendee-questions');

echo '<article>';
cm_form_edit_body($form_def, array(
	array('type' => 'h1', 'text' => 'Register for ' . $cm_config['event']['name']),
	array('type' => 'custom-text', 'name' => 'main'),
	array('type' => 'hr'),
	array('type' => 'h2', 'text' => 'Personal Information'),
	array('type' => 'custom-text', 'name' => 'personal'),
	array('type' => 'text', 'text' => 'First Name'),
	array('type' => 'text', 'text' => 'Last Name'),
	array('type' => 'text', 'text' => 'Fandom Name'),
	array('type' => 'select', 'text' => 'Name on Badge', 'values' => array('Fandom Name Large, Real Name Small')),
	array('type' => 'text', 'text' => 'Date of Birth'),
	array('type' => 'select', 'text' => 'Badge Type', 'values' => array($name_list ? $name_list[0]['name'] : '')),
	array('type' => 'hr'),
	array('type' => 'h2', 'text' => 'Contact Information'),
	array('type' => 'custom-text', 'name' => 'contact'),
	array('type' => 'text', 'text' => 'Email Address'),
	array('type' => 'text', 'text' => 'Phone Number'),
	array('type' => 'text', 'text' => 'Street Address'),
	array('type' => 'text', 'text' => 'City'),
	array('type' => 'text', 'text' => 'State or Province'),
	array('type' => 'text', 'text' => 'ZIP or Postal Code'),
	array('type' => 'text', 'text' => 'Country'),
	array('type' => 'hr'),
	array('type' => 'h2', 'text' => 'Additional Information'),
	array('type' => 'custom-questions'),
	array('type' => 'hr'),
	array('type' => 'h2', 'text' => 'Emergency Contact Information'),
	array('type' => 'custom-text', 'name' => 'ice'),
	array('type' => 'text', 'text' => 'Emergency Contact Name'),
	array('type' => 'text', 'text' => 'Emergency Contact Relationship'),
	array('type' => 'text', 'text' => 'Emergency Contact Email Address'),
	array('type' => 'text', 'text' => 'Emergency Contact Phone Number'),
));
cm_form_edit_body($form_def, array(
	array('type' => 'h1', 'text' => 'Payment Complete <i>(Attendee successfully paid online.)</i>'),
	array('type' => 'custom-text', 'name' => 'payment-complete', 'default' =>
		'Your payment has been accepted.<br><br>'.
		'You can <b><a href="[[review-link]]">review your order</a></b> at any time.'
	),
));
cm_form_edit_body($form_def, array(
	array('type' => 'h1', 'text' => 'Registration Complete <i>(Attendee opted to pay on-site.)</i>'),
	array('type' => 'custom-text', 'name' => 'registration-complete', 'default' =>
		'Your registration has been submitted. You will need to pay at the door.<br><br>'.
		'You can <b><a href="[[review-link]]">review your order</a></b> at any time.'
	),
));
cm_form_edit_body($form_def, array(
	array('type' => 'h1', 'text' => 'Payment Refused <i>(Payment did not go through.)</i>'),
	array('type' => 'custom-text', 'name' => 'payment-refused', 'default' =>
		'PayPal has refused this transaction.<br><br>'.
		'PayPal says: [[payment-txn-msg]]<br><br>'.
		'Unfortunately, that is all we know. Please try again later.'
	),
));
cm_form_edit_body($form_def, array(
	array('type' => 'h1', 'text' => 'Payment Cancelled <i>(Attendee cancelled payment.)</i>'),
	array('type' => 'custom-text', 'name' => 'payment-cancelled', 'default' =>
		'You have cancelled your payment.'
	),
));
cm_form_edit_body($form_def, array(
	array('type' => 'h1', 'text' => 'Could Not Complete Registration <i>(Attendee has been blacklisted.)</i>'),
	array('type' => 'custom-text', 'name' => 'blacklisted', 'default' =>
		'We\'re sorry, there was an issue with your registration '.
		'and your registration could not be completed.<br><br>'.
		'If you think this is an error, please '.
		'<b><a href="mailto:[[contact-address]]">contact us</a></b>.'
	),
));
echo '</article>';

cm_admin_dialogs();
cm_form_edit_dialogs();
cm_admin_tail();
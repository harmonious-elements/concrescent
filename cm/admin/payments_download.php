<?php

require_once dirname(__FILE__).'/admin.php';
require_once dirname(__FILE__).'/../lib/dal/lists.php';
require_once dirname(__FILE__).'/../lib/dal/payments.php';

if (isset($_POST['download'])) {
	$conn = get_db_connection();
	
	header('Content-Type: text/csv');
	header('Content-Disposition: attachment; filename=payments.csv');
	header('Pragma: no-cache');
	header('Expires: 0');
	$out = fopen('php://output', 'w');
	
	$row = array(
		'ID',
		'ID String',
		'Payment For',
		'Description',
		'First Name',
		'Last Name',
		'Real Name',
		'Email Address',
		'Payment Status',
		'Payment Type',
		'Transaction ID',
		'Payment Amount',
		'Payment Date',
		'Payment Details',
		'Lookup Key',
		'Confirm Payment Link',
		'Review Order Link',
	);
	fputcsv($out, $row);
	
	$results = get_entities('payments', 'id', 0, 0, false, $conn);
	while ($result = get_entity($results)) {
		$result = decode_payment($result);
		
		$row = array(
			$result['id'],
			$result['id_string'],
			$result['name'],
			$result['description'],
			$result['first_name'],
			$result['last_name'],
			$result['real_name'],
			$result['email_address'],
			$result['payment_status_string'],
			$result['payment_type'],
			$result['payment_txn_id'],
			$result['payment_price'],
			$result['payment_date'],
			$result['payment_details'],
			$result['payment_lookup_key'],
			$result['confirm_payment_url'],
			$result['review_order_url'],
		);
		fputcsv($out, $row);
	}
	
	fclose($out);
	exit(0);
}

render_admin_head('Download Payment Records');
render_admin_body('Download Payment Records');

echo '<div class="card">';
	echo '<div class="card-content spaced">';
		echo '<p><b>Notice:</b> Downloaded CSV data should be used for reporting purposes only.</p>';
		echo '<form action="payments_download.php" method="post">';
			echo '<p><input type="submit" name="download" value="Download CSV"></p>';
		echo '</form>';
	echo '</div>';
echo '</div>';

render_admin_dialogs();
render_admin_tail();
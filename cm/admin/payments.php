<?php

require_once dirname(__FILE__).'/admin.php';
require_once dirname(__FILE__).'/../lib/cmbase/util.php';
require_once dirname(__FILE__).'/../lib/dal/lists.php';
require_once dirname(__FILE__).'/../lib/dal/mail.php';
require_once dirname(__FILE__).'/../lib/dal/payments.php';
require_once dirname(__FILE__).'/../lib/ui/lists.php';
require_once dirname(__FILE__).'/../lib/ui/mail.php';
require_once dirname(__FILE__).'/../lib/ui/payments.php';

$conn = get_db_connection();

if (isset($_POST['start_id'])) {
	header('Content-type: text/plain');
	$payments = array();
	$start_id = (int)$_POST['start_id'];
	$end_id = $start_id;
	$batch_size = 100;
	
	$results = get_entities('payments', 'id', $start_id, $batch_size, false, $conn);
	while ($result = get_entity($results)) {
		$result = decode_payment($result);
		$html_content = render_list_row(
			array(
				$result['real_name'],
				array('html' => email_link($result['email_address'])),
				$result['name'],
				$result['payment_price_string'],
				array('html' => payment_status_html($result['payment_status'])),
				($result['payment_date'] ? $result['payment_date'] : 'never'),
			),
			array(
				'ea-id' => $result['id'],
				'ea-name' => $result['name'],
				'ea-description' => $result['description'],
				'ea-first-name' => $result['first_name'],
				'ea-last-name' => $result['last_name'],
				'ea-email-address' => $result['email_address'],
				'ea-payment-status' => $result['payment_status'],
				'ea-payment-type' => $result['payment_type'],
				'ea-payment-txn-id' => $result['payment_txn_id'],
				'ea-payment-price' => $result['payment_price'],
				'ea-payment-date' => $result['payment_date'],
				'ea-payment-details' => $result['payment_details'],
				'ea-payment-lookup-key' => $result['payment_lookup_key'],
				'ea-confirm-payment-url' => $result['confirm_payment_url'],
				'ea-review-order-url' => $result['review_order_url'],
			),
			/*  selectable = */ false,
			/*  switchable = */ false,
			/*      active = */ false,
			/*  deleteable = */ false,
			/* reorderable = */ false,
			/*        edit = */ true,
			/*      review = */ false
		);
		$payments[] = array(
			'id' => $result['id'],
			'search_content' => $result['search_content'],
			'html_content' => $html_content,
		);
		$end_id = $result['id'];
	}
	
	$response = array(
		'start_id' => $start_id,
		'end_id' => $end_id,
		'next_start_id' => $end_id + 1,
		'batch_size' => $batch_size,
		'entities' => $payments,
	);
	echo json_encode($response);
	exit(0);
}

if (isset($_POST['action'])) {
	switch ($_POST['action']) {
		case 'save':
			$id = upsert_unordered_entity('payments', $_POST['id'], encode_payment($_POST), $conn);
			$email_template = get_mail_template('payment_requested', $conn);
			if ($email_template && trim($email_template['body'])) {
				$results = get_entities('payments', 'id', $id, 1, false, $conn);
				$result = get_entity($results);
				$result = decode_payment($result);
				if ($result['payment_status'] == 'Incomplete') {
					mail_send($result['email_address'], $email_template, $result);
				}
			}
			break;
	}
	exit(0);
}

render_admin_head('Payments');

echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('cmlists.js')) . '"></script>';
?><script type="text/javascript">listPage({
	ajaxUrl: 'payments.php',
	entityType: 'payments',
	progressive: true,
	searchable: true,
	maxResults: 20,
	editDialog: true,
	editDialogTitle: 'Edit Payment',
	editDialogStart: function(self, id, name) {
		$('.hide-on-add').removeClass('hidden');
		$('.edit-id').val(id);
		$('.edit-name').val(name);
		$('.edit-description').val(self.find('.ea-description').val());
		$('.edit-first-name').val(self.find('.ea-first-name').val());
		$('.edit-last-name').val(self.find('.ea-last-name').val());
		$('.edit-email-address').val(self.find('.ea-email-address').val());
		$('.edit-payment-status').val(self.find('.ea-payment-status').val());
		$('.edit-payment-type').val(self.find('.ea-payment-type').val());
		$('.edit-payment-txn-id').val(self.find('.ea-payment-txn-id').val());
		$('.edit-payment-price').val(self.find('.ea-payment-price').val());
		$('.edit-payment-date').val(self.find('.ea-payment-date').val().replace(
			/^([0-9]{4}-[0-9]{2}-[0-9]{2}) ([0-9]{2}:[0-9]{2}):[0-9]{2}$/, '$1T$2'));
		$('.edit-payment-details').val(self.find('.ea-payment-details').val());
		$('.edit-payment-lookup-key-value').text(self.find('.ea-payment-lookup-key').val());
		$('.edit-payment-lookup-key-new').attr('checked', false);
		$('.edit-payment-lookup-key-clear').attr('checked', false);
		$('.edit-payment-lookup-key-keep').attr('checked', true);
		if (self.find('.ea-payment-lookup-key').val()) {
			if (self.find('.ea-payment-txn-id').val()) {
				$('.edit-confirm-payment-url').addClass('hidden');
				$('.edit-review-order-url').removeClass('hidden');
				var rou = self.find('.ea-review-order-url').val();
				$('a.edit-review-order-url').attr('href', rou).text(rou);
			} else {
				$('.edit-confirm-payment-url').removeClass('hidden');
				$('.edit-review-order-url').addClass('hidden');
				var cpu = self.find('.ea-confirm-payment-url').val();
				$('a.edit-confirm-payment-url').attr('href', cpu).text(cpu);
			}
		} else {
			$('.edit-confirm-payment-url').addClass('hidden');
			$('.edit-review-order-url').addClass('hidden');
		}
	},
	addDialog: true,
	addDialogTitle: 'Add Payment',
	addDialogStart: function() {
		$('.hide-on-add').addClass('hidden');
		$('.edit-id').val('');
		$('.edit-name').val('');
		$('.edit-description').val('');
		$('.edit-first-name').val('');
		$('.edit-last-name').val('');
		$('.edit-email-address').val('');
		$('.edit-payment-status').val('Incomplete');
		$('.edit-payment-type').val('');
		$('.edit-payment-txn-id').val('');
		$('.edit-payment-price').val('');
		$('.edit-payment-date').val('');
		$('.edit-payment-details').val('');
		$('.edit-payment-lookup-key-value').text('Not Set');
		$('.edit-payment-lookup-key-keep').attr('checked', false);
		$('.edit-payment-lookup-key-clear').attr('checked', false);
		$('.edit-payment-lookup-key-new').attr('checked', true);
		$('.edit-confirm-payment-url').addClass('hidden');
		$('.edit-review-order-url').addClass('hidden');
	},
	addEditDialogGetSaveData: function(id, name) {
		return {
			'id': id,
			'name': name,
			'description': $('.edit-description').val(),
			'first_name': $('.edit-first-name').val(),
			'last_name': $('.edit-last-name').val(),
			'email_address': $('.edit-email-address').val(),
			'payment_status': $('.edit-payment-status').val(),
			'payment_type': $('.edit-payment-type').val(),
			'payment_txn_id': $('.edit-payment-txn-id').val(),
			'payment_price': $('.edit-payment-price').val(),
			'payment_date': $('.edit-payment-date').val(),
			'payment_details': $('.edit-payment-details').val(),
			'payment_lookup_key': (
				$('.edit-payment-lookup-key-new').attr('checked') ? 'new' :
				$('.edit-payment-lookup-key-clear').attr('checked') ? 'clear' :
				'keep'
			),
		};
	},
});</script><?php

render_admin_body('Payments');

echo '<div class="card">';
render_list_search('name, contact info, or transaction ID', 'card-content-only');
echo '</div>';

echo '<div class="card entity-list-card">';
render_list_table(array(
	'Real Name', 'Email Address', 'Payment For',
	'Payment Amount', 'Payment Status', 'Payment Date'
), null, true, $conn);
echo '</div>';

render_admin_dialogs();

render_edit_dialog_start();
render_payment_editor();
render_edit_dialog_end();

render_admin_tail();
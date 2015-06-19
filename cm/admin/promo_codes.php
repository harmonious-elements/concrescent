<?php

require_once dirname(__FILE__).'/admin.php';
require_once dirname(__FILE__).'/../lib/dal/lists.php';
require_once dirname(__FILE__).'/../lib/ui/lists.php';
require_once dirname(__FILE__).'/../lib/ui/attendees.php';

$conn = get_db_connection();
db_require_table('attendee_badges', $conn);
db_require_table('promo_codes', $conn);
db_require_table('attendees', $conn);
$badge_names = get_attendee_badge_names($conn);

function render_promo_codes($connection) {
	global $badge_names;
	$results = mysql_query('SELECT * FROM '.db_table_name('promo_codes').' ORDER BY `code`', $connection);
	while ($result = mysql_fetch_assoc($results)) {
		$result = decode_promo_code($result, $badge_names);
		$use_count = get_promo_code_use_count($result['code'], $connection);
		echo render_list_row(
			array(
				$result['code'],
				($result['badge_id'] ? $result['badge_name'] : 'all'),
				array('html' => date_range_string($result['start_date'], $result['end_date'])),
				array('class' => 'numeric', 'value' => $use_count),
				array('class' => 'numeric', 'value' => ($result['limit'] ? $result['limit'] : 'unlimited')),
				array('class' => 'numeric', 'html' => (
					$result['percentage'] ?
					(number_format($result['price'], 2, '.', ',').'<b>%</b>') :
					('<b>$</b>'.number_format($result['price'], 2, '.', ','))
				)),
			),
			array(
				'ea-id' => $result['id'],
				'ea-code' => $result['code'],
				'ea-description' => $result['description'],
				'ea-badge-id' => $result['badge_id'],
				'ea-limit' => $result['limit'],
				'ea-start-date' => $result['start_date'],
				'ea-end-date' => $result['end_date'],
				'ea-active' => $result['active'],
				'ea-price' => number_format($result['price'], 2, '.', ''),
				'ea-percentage' => $result['percentage'],
			),
			/*  selectable = */ false,
			/*  switchable = */ true,
			/*      active = */ $result['active'],
			/*  deleteable = */ true,
			/* reorderable = */ false,
			/*        edit = */ true,
			/*      review = */ false
		);
	}
}

if (isset($_POST['action'])) {
	$id = (int)$_POST['id'];
	switch ($_POST['action']) {
		case 'activate': activate_entity('promo_codes', $id, $conn); break;
		case 'deactivate': deactivate_entity('promo_codes', $id, $conn); break;
		case 'delete': delete_entity('promo_codes', $id, $conn); break;
		case 'save': upsert_unordered_entity('promo_codes', $id, encode_promo_code($_POST), $conn); break;
	}
	render_promo_codes($conn);
	exit(0);
}

render_admin_head('Promo Codes');

echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('cmlists.js')) . '"></script>';
?><script type="text/javascript">listPage({
	ajaxUrl: 'promo_codes.php',
	listItemNameSelector: '.ea-code',
	switchable: true,
	deleteable: true,
	editDialog: true,
	editDialogTitle: 'Edit Promo Code',
	editDialogStart: function(self, id, code) {
		$('.edit-id').val(id);
		$('.edit-code').val(code);
		$('.edit-description').val(self.find('.ea-description').val());
		$('.edit-badge-id').val((1 * self.find('.ea-badge-id').val()) || '');
		$('.edit-limit').val((1 * self.find('.ea-limit').val()) || '');
		$('.edit-start-date').val(self.find('.ea-start-date').val());
		$('.edit-end-date').val(self.find('.ea-end-date').val());
		$('.edit-active').attr('checked', !!self.find('.ea-active').val());
		$('.edit-price').val(self.find('.ea-price').val());
		var percent = (self.find('.ea-percentage').val() ? 'true' : 'false');
		$('.edit-percentage-' + percent).attr('checked', true);
	},
	addDialog: true,
	addDialogTitle: 'Add Promo Code',
	addDialogStart: function() {
		$('.edit-id').val('');
		$('.edit-code').val('');
		$('.edit-description').val('');
		$('.edit-badge-id').val('');
		$('.edit-limit').val('');
		$('.edit-start-date').val('');
		$('.edit-end-date').val('');
		$('.edit-active').attr('checked', true);
		$('.edit-price').val('0.00');
		$('.edit-percentage-false').attr('checked', true);
	},
	addEditDialogNameSelector: '.edit-code',
	addEditDialogGetSaveData: function(id, code) {
		return {
			'id': id,
			'code': code,
			'description': $('.edit-description').val(),
			'badge_id': $('.edit-badge-id').val(),
			'limit': $('.edit-limit').val(),
			'start_date': $('.edit-start-date').val(),
			'end_date': $('.edit-end-date').val(),
			'active': ($('.edit-active').attr('checked') ? 1 : 0),
			'price': $('.edit-price').val(),
			'percentage': ($('.edit-percentage-true').attr('checked') ? 1 : 0),
		};
	},
	addEditDialogGetSaveMessage: function(id, code) {
		var price = String(Math.round($('.edit-price').val()));
		var pattern = new RegExp('^[A-Za-z]+'+price+'$');
		return (pattern.test(code)) ? 'Next time be a little more imaginative.' : 'Changes saved.';
	},
});</script><?php

render_admin_body('Promo Codes');

echo '<div class="card entity-list-card">';
render_list_table(array(
	'Code', 'Valid For', 'Dates Available',
	array('class' => 'numeric', 'name' => '# Used'),
	array('class' => 'numeric', 'name' => 'Limit Per Customer'),
	array('class' => 'numeric', 'name' => 'Discount'),
), 'render_promo_codes', true, $conn);
echo '</div>';

render_admin_dialogs();

render_delete_dialog('promo code', true);

render_edit_dialog_start();
render_promo_code_editor($badge_names);
render_edit_dialog_end();

render_admin_tail();
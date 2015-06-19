<?php

require_once dirname(__FILE__).'/admin.php';
require_once dirname(__FILE__).'/../lib/dal/lists.php';
require_once dirname(__FILE__).'/../lib/ui/lists.php';
require_once dirname(__FILE__).'/../lib/ui/eventlets.php';

$conn = get_db_connection();
db_require_table('eventlet_badges', $conn);
db_require_table('eventlets', $conn);

function render_eventlet_badges($connection) {
	$results = mysql_query('SELECT * FROM '.db_table_name('eventlet_badges').' ORDER BY `order`', $connection);
	while ($result = mysql_fetch_assoc($results)) {
		$result = decode_eventlet_badge($result);
		$accepted = get_accepted_eventlet_badge_count($result['id'], $connection);
		echo render_list_row(
			array(
				$result['name'],
				array('html' => date_range_string($result['start_date'], $result['end_date'])),
				array('class' => 'numeric', 'value' => $accepted),
				array('class' => 'numeric', 'value' => ($result['count'] ? ($result['count'] - $accepted) : 'unlimited')),
				array('class' => 'numeric', 'value' => ($result['count'] ? $result['count'] : 'unlimited')),
				array('class' => 'numeric', 'value' => $result['price_per_eventlet_string']),
				array('class' => 'numeric', 'value' => $result['price_per_staffer_string']),
			),
			array(
				'ea-id' => $result['id'],
				'ea-name' => $result['name'],
				'ea-description' => $result['description'],
				'ea-start-date' => $result['start_date'],
				'ea-end-date' => $result['end_date'],
				'ea-count' => $result['count'],
				'ea-active' => $result['active'],
				'ea-max-staffers' => $result['max_staffers'],
				'ea-price-per-eventlet' => number_format($result['price_per_eventlet'], 2, '.', ''),
				'ea-price-per-staffer' => number_format($result['price_per_staffer'], 2, '.', ''),
				'ea-staffers-in-eventlet-price' => $result['staffers_in_eventlet_price'],
				'ea-max-prereg-discount' => $result['max_prereg_discount'],
				'ea-order' => $result['order'],
			),
			/*  selectable = */ false,
			/*  switchable = */ true,
			/*      active = */ $result['active'],
			/*  deleteable = */ true,
			/* reorderable = */ true,
			/*        edit = */ true,
			/*      review = */ false
		);
	}
}

if (isset($_POST['action'])) {
	$id = (int)$_POST['id'];
	switch ($_POST['action']) {
		case 'activate': activate_entity('eventlet_badges', $id, $conn); break;
		case 'deactivate': deactivate_entity('eventlet_badges', $id, $conn); break;
		case 'delete': delete_entity('eventlet_badges', $id, $conn); break;
		case 'reorder': reorder_entities('eventlet_badges', $id, (int)$_POST['direction'], $conn); break;
		case 'save': upsert_ordered_entity('eventlet_badges', $id, encode_eventlet_badge($_POST), $conn); break;
	}
	render_eventlet_badges($conn);
	exit(0);
}

render_admin_head('Panel/Activity Types');

echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('cmlists.js')) . '"></script>';
?><script type="text/javascript">listPage({
	ajaxUrl: 'eventlet_badges.php',
	switchable: true,
	deleteable: true,
	reorderable: true,
	editDialog: true,
	editDialogTitle: 'Edit Badge Type',
	editDialogStart: function(self, id, name) {
		$('.edit-id').val(id);
		$('.edit-name').val(name);
		$('.edit-description').val(self.find('.ea-description').val());
		$('.edit-start-date').val(self.find('.ea-start-date').val());
		$('.edit-end-date').val(self.find('.ea-end-date').val());
		$('.edit-count').val((1 * self.find('.ea-count').val()) || '');
		$('.edit-active').attr('checked', !!self.find('.ea-active').val());
		$('.edit-max-staffers').val((1 * self.find('.ea-max-staffers').val()) || '');
		$('.edit-price-per-eventlet').val(self.find('.ea-price-per-eventlet').val());
		$('.edit-price-per-staffer').val(self.find('.ea-price-per-staffer').val());
		$('.edit-staffers-in-eventlet-price').val(1 * self.find('.ea-staffers-in-eventlet-price').val());
		$('.edit-max-prereg-discount').val(self.find('.ea-max-prereg-discount').val());
	},
	addDialog: true,
	addDialogTitle: 'Add Badge Type',
	addDialogStart: function() {
		$('.edit-id').val('');
		$('.edit-name').val('');
		$('.edit-description').val('');
		$('.edit-start-date').val('');
		$('.edit-end-date').val('');
		$('.edit-count').val('');
		$('.edit-active').attr('checked', true);
		$('.edit-max-staffers').val('');
		$('.edit-price-per-eventlet').val('0.00');
		$('.edit-price-per-staffer').val('0.00');
		$('.edit-staffers-in-eventlet-price').val('0');
		$('.edit-max-prereg-discount').val('None');
	},
	addEditDialogGetSaveData: function(id, name) {
		return {
			'id': id,
			'name': name,
			'description': $('.edit-description').val(),
			'start_date': $('.edit-start-date').val(),
			'end_date': $('.edit-end-date').val(),
			'count': $('.edit-count').val(),
			'active': ($('.edit-active').attr('checked') ? 1 : 0),
			'max_staffers': $('.edit-max-staffers').val(),
			'price_per_eventlet': $('.edit-price-per-eventlet').val(),
			'price_per_staffer': $('.edit-price-per-staffer').val(),
			'staffers_in_eventlet_price': $('.edit-staffers-in-eventlet-price').val(),
			'max_prereg_discount': $('.edit-max-prereg-discount').val(),
		};
	},
});</script><?php

render_admin_body('Panel/Activity Types');

echo '<div class="card entity-list-card">';
render_list_table(array(
	'Name', 'Dates Available',
	array('class' => 'numeric', 'name' => '# Accepted'),
	array('class' => 'numeric', 'name' => '# Left'),
	array('class' => 'numeric', 'name' => '# Total'),
	array('class' => 'numeric', 'name' => 'Price/Application'),
	array('class' => 'numeric', 'name' => 'Price/Host'),
), 'render_eventlet_badges', true, $conn);
echo '</div>';

render_admin_dialogs();

render_delete_dialog('panel/activity type', true);

render_edit_dialog_start();
render_eventlet_badge_editor();
render_edit_dialog_end();

render_admin_tail();
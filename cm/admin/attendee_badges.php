<?php

require_once dirname(__FILE__).'/admin.php';
require_once dirname(__FILE__).'/../lib/dal/lists.php';
require_once dirname(__FILE__).'/../lib/ui/lists.php';
require_once dirname(__FILE__).'/../lib/ui/attendees.php';

$conn = get_db_connection();
db_require_table('attendee_badges', $conn);
db_require_table('attendees', $conn);

function render_attendee_badges($connection) {
	$results = mysql_query('SELECT * FROM '.db_table_name('attendee_badges').' ORDER BY `order`', $connection);
	while ($result = mysql_fetch_assoc($results)) {
		$result = decode_attendee_badge($result);
		$purchased = get_purchased_attendee_badge_count($result['id'], $connection);
		echo render_list_row(
			array(
				$result['name'],
				array('html' => date_range_string($result['start_date'], $result['end_date'])),
				array('html' => age_range_string($result['min_age'], $result['max_age'])),
				array('class' => 'numeric', 'value' => $purchased),
				array('class' => 'numeric', 'value' => ($result['count'] ? ($result['count'] - $purchased) : 'unlimited')),
				array('class' => 'numeric', 'value' => ($result['count'] ? $result['count'] : 'unlimited')),
				array('class' => 'numeric', 'value' => $result['price_string']),
			),
			array(
				'ea-id' => $result['id'],
				'ea-name' => $result['name'],
				'ea-description' => $result['description'],
				'ea-start-date' => $result['start_date'],
				'ea-end-date' => $result['end_date'],
				'ea-min-age' => $result['min_age'],
				'ea-max-age' => $result['max_age'],
				'ea-count' => $result['count'],
				'ea-active' => $result['active'],
				'ea-price' => number_format($result['price'], 2, '.', ''),
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
		case 'activate': activate_entity('attendee_badges', $id, $conn); break;
		case 'deactivate': deactivate_entity('attendee_badges', $id, $conn); break;
		case 'delete': delete_entity('attendee_badges', $id, $conn); break;
		case 'reorder': reorder_entities('attendee_badges', $id, (int)$_POST['direction'], $conn); break;
		case 'save': upsert_ordered_entity('attendee_badges', $id, encode_attendee_badge($_POST), $conn); break;
	}
	render_attendee_badges($conn);
	exit(0);
}

render_admin_head('Attendee Badges');

echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('cmlists.js')) . '"></script>';
?><script type="text/javascript">listPage({
	ajaxUrl: 'attendee_badges.php',
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
		$('.edit-min-age').val((1 * self.find('.ea-min-age').val()) || '');
		$('.edit-max-age').val((1 * self.find('.ea-max-age').val()) || '');
		$('.edit-count').val((1 * self.find('.ea-count').val()) || '');
		$('.edit-active').attr('checked', !!self.find('.ea-active').val());
		$('.edit-price').val(self.find('.ea-price').val());
	},
	addDialog: true,
	addDialogTitle: 'Add Badge Type',
	addDialogStart: function() {
		$('.edit-id').val('');
		$('.edit-name').val('');
		$('.edit-description').val('');
		$('.edit-start-date').val('');
		$('.edit-end-date').val('');
		$('.edit-min-age').val('');
		$('.edit-max-age').val('');
		$('.edit-count').val('');
		$('.edit-active').attr('checked', true);
		$('.edit-price').val('0.00');
	},
	addEditDialogGetSaveData: function(id, name) {
		return {
			'id': id,
			'name': name,
			'description': $('.edit-description').val(),
			'start_date': $('.edit-start-date').val(),
			'end_date': $('.edit-end-date').val(),
			'min_age': $('.edit-min-age').val(),
			'max_age': $('.edit-max-age').val(),
			'count': $('.edit-count').val(),
			'active': ($('.edit-active').attr('checked') ? 1 : 0),
			'price': $('.edit-price').val(),
		};
	},
});</script><?php

render_admin_body('Attendee Badges');

echo '<div class="card entity-list-card">';
render_list_table(array(
	'Name', 'Dates Available', 'Age Range',
	array('class' => 'numeric', 'name' => '# Sold'),
	array('class' => 'numeric', 'name' => '# Left'),
	array('class' => 'numeric', 'name' => '# Total'),
	array('class' => 'numeric', 'name' => 'Price'),
), 'render_attendee_badges', true, $conn);
echo '</div>';

render_admin_dialogs();

render_delete_dialog('badge type', true);

render_edit_dialog_start();
render_attendee_badge_editor();
render_edit_dialog_end();

render_admin_tail();
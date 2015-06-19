<?php

require_once dirname(__FILE__).'/admin.php';
require_once dirname(__FILE__).'/../lib/dal/lists.php';
require_once dirname(__FILE__).'/../lib/ui/lists.php';
require_once dirname(__FILE__).'/../lib/ui/attendees.php';

$conn = get_db_connection();
db_require_table('attendee_blacklist', $conn);

function render_attendee_blacklist($connection) {
	$results = mysql_query('SELECT * FROM '.db_table_name('attendee_blacklist').' ORDER BY `normalized_real_name`', $connection);
	while ($result = mysql_fetch_assoc($results)) {
		$result = decode_attendee_blacklist($result);
		echo render_list_row(
			array(
				$result['real_name'],
				$result['fandom_name'],
				$result['email_address'],
				$result['phone_number'],
			),
			array(
				'ea-id' => $result['id'],
				'ea-first-name' => $result['first_name'],
				'ea-last-name' => $result['last_name'],
				'ea-real-name' => $result['real_name'],
				'ea-fandom-name' => $result['fandom_name'],
				'ea-email-address' => $result['email_address'],
				'ea-phone-number' => $result['phone_number'],
			),
			/*  selectable = */ false,
			/*  switchable = */ false,
			/*      active = */ false,
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
		case 'delete': delete_entity('attendee_blacklist', $id, $conn); break;
		case 'save': upsert_unordered_entity('attendee_blacklist', $id, encode_attendee_blacklist($_POST), $conn); break;
	}
	render_attendee_blacklist($conn);
	exit(0);
}

render_admin_head('Attendee Blacklist');

echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('cmlists.js')) . '"></script>';
?><script type="text/javascript">listPage({
	ajaxUrl: 'attendee_blacklist.php',
	listItemNameSelector: '.ea-real-name',
	deleteable: true,
	editDialog: true,
	editDialogTitle: 'Edit Attendee Blacklist',
	editDialogStart: function(self, id) {
		$('.edit-id').val(id);
		$('.edit-first-name').val(self.find('.ea-first-name').val());
		$('.edit-last-name').val(self.find('.ea-last-name').val());
		$('.edit-fandom-name').val(self.find('.ea-fandom-name').val());
		$('.edit-email-address').val(self.find('.ea-email-address').val());
		$('.edit-phone-number').val(self.find('.ea-phone-number').val());
	},
	addDialog: true,
	addDialogTitle: 'Add Attendee Blacklist',
	addDialogStart: function() {
		$('.edit-id').val('');
		$('.edit-first-name').val('');
		$('.edit-last-name').val('');
		$('.edit-fandom-name').val('');
		$('.edit-email-address').val('');
		$('.edit-phone-number').val('');
	},
	addEditDialogGetSaveData: function(id) {
		return {
			'id': id,
			'first_name': $('.edit-first-name').val(),
			'last_name': $('.edit-last-name').val(),
			'fandom_name': $('.edit-fandom-name').val(),
			'email_address': $('.edit-email-address').val(),
			'phone_number': $('.edit-phone-number').val(),
		};
	},
});</script><?php

render_admin_body('Attendee Blacklist');

echo '<div class="card entity-list-card">';
render_list_table(array(
	'Real Name', 'Fandom Name', 'Email Address', 'Phone Number',
), 'render_attendee_blacklist', true, $conn);
echo '</div>';

render_admin_dialogs();

render_delete_dialog('attendee blacklist', false);

render_edit_dialog_start();
render_attendee_blacklist_editor();
render_edit_dialog_end();

render_admin_tail();
<?php

require_once dirname(__FILE__).'/admin.php';
require_once dirname(__FILE__).'/../lib/ui/lists.php';
require_once dirname(__FILE__).'/../lib/ui/admin.php';

$conn = get_db_connection();
db_require_table('admin_users', $conn);

function render_admin_users($connection) {
	$results = get_admin_users($connection);
	while ($result = get_admin_user($results)) {
		$result = decode_admin_user($result);
		echo render_list_row(
			array(
				$result['name'],
				$result['username'],
			),
			array(
				'ea-id' => $result['username'],
				'ea-name' => $result['name'],
				'ea-username' => $result['username'],
				'ea-permissions' => $result['permissions'],
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
	switch ($_POST['action']) {
		case 'delete': delete_admin_user($_POST['id'], $conn); break;
		case 'save': upsert_admin_user($_POST['id'], encode_admin_user($_POST, !$_POST['id']), $conn); break;
	}
	render_admin_users($conn);
	exit(0);
}

render_admin_head('Admin Users');

echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('cmlists.js')) . '"></script>';
?><script type="text/javascript">listPage({
	ajaxUrl: 'admin_users.php',
	deleteable: true,
	editDialog: true,
	editDialogTitle: 'Edit User',
	editDialogStart: function(self, id, name) {
		$('.edit-id').val(id);
		$('.edit-name').val(name);
		$('.edit-username').val(self.find('.ea-username').val());
		$('.edit-password').val('');
		var permissions = self.find('.ea-permissions').val().split(',');
		$('.edit-permissions').each(function() {
			var name = $(this).attr('name');
			if (name.substring(0, 17) == 'edit-permissions-') {
				$(this).attr('checked', permissions.indexOf(name.substring(17)) >= 0);
			}
		});
	},
	addDialog: true,
	addDialogTitle: 'Add User',
	addDialogStart: function() {
		$('.edit-id').val('');
		$('.edit-name').val('');
		$('.edit-username').val('');
		$('.edit-password').val('');
		$('.edit-permissions').each(function() {
			$(this).attr('checked', false);
		});
	},
	addEditDialogGetSaveData: function(id, name) {
		var permissions = [];
		$('.edit-permissions').each(function() {
			if ($(this).attr('checked')) {
				var name = $(this).attr('name');
				if (name.substring(0, 17) == 'edit-permissions-') {
					permissions.push(name.substring(17));
				}
			}
		});
		return {
			'id': id,
			'name': name,
			'username': $('.edit-username').val(),
			'password': $('.edit-password').val(),
			'permissions': permissions.join(','),
		};
	},
});</script><?php

render_admin_body('Admin Users');

echo '<div class="card entity-list-card">';
render_list_table(array('Name', 'Username'), 'render_admin_users', true, $conn);
echo '</div>';

render_admin_dialogs();

render_delete_dialog('user', false);

render_edit_dialog_start();
render_admin_user_editor($admin_links);
render_edit_dialog_end();

render_admin_tail();
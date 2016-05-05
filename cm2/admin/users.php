<?php

require_once dirname(__FILE__).'/../lib/util/util.php';
require_once dirname(__FILE__).'/admin.php';
require_once dirname(__FILE__).'/admin-lists.php';
require_once dirname(__FILE__).'/admin-perms.php';

cm_admin_check_permission('admin-users', 'admin-users');

$list_def = array(
	'ajax-url' => get_site_url(false) . '/admin/users.php',
	'entity-type' => 'user',
	'entity-type-pl' => 'users',
	'search-criteria' => 'name or username',
	'columns' => array(
		array(
			'name' => 'Name',
			'key' => 'name',
			'type' => 'text'
		),
		array(
			'name' => 'Username',
			'key' => 'username',
			'type' => 'text'
		),
	),
	'sort-order' => array(0),
	'row-key' => 'username',
	'name-key' => 'name',
	'row-actions' => array('edit', 'delete'),
	'table-actions' => array('add'),
	'add-title' => 'Add User',
	'edit-title' => 'Edit User',
	'delete-title' => 'Delete User',
);
$list_def['edit-clear-function'] = <<<END
	function() {
		$('#ea-name').val('');
		$('#ea-username').val('');
		$('#ea-password').val('');
		$('.ea-permissions').attr('checked', false);
	}
END;
$list_def['edit-load-function'] = <<<END
	function(id, e) {
		$('#ea-name').val(e['name']);
		$('#ea-username').val(e['username']);
		$('#ea-password').val('');
		$('.ea-permissions').each(function() {
			var name = $(this).attr('id').substring(15);
			$(this).attr('checked', e['permissions'].indexOf(name) >= 0);
		});
	}
END;
$list_def['edit-save-function'] = <<<END
	function(id, e) {
		var ne = {
			'name': $('#ea-name').val(),
			'username': $('#ea-username').val(),
			'password': $('#ea-password').val(),
			'permissions': []
		};
		$('.ea-permissions').each(function() {
			if ($(this).is(':checked')) {
				var name = $(this).attr('id').substring(15);
				ne['permissions'].push(name);
			}
		});
		return ne;
	}
END;

if (isset($_POST['cm-list-action'])) {
	header('Content-type: text/plain');
	switch ($_POST['cm-list-action']) {
		case 'list':
			$response = array('ok' => true, 'rows' => array());
			$users = $adb->list_users();
			foreach ($users as $user) {
				$response['rows'][] = array(
					'entity' => $user,
					'html' => cm_admin_list_row($list_def, $user),
					'search' => $user['search-content']
				);
			}
			echo json_encode($response);
			break;
		case 'create':
			$user = json_decode($_POST['cm-list-entity'], true);
			$ok = $adb->create_user($user);
			$response = array('ok' => $ok);
			if ($ok) {
				$user = $adb->get_user($user['username']);
				if ($user) $response['row'] = array(
					'entity' => $user,
					'html' => cm_admin_list_row($list_def, $user),
					'search' => $user['search-content']
				);
			}
			echo json_encode($response);
			break;
		case 'update':
			$username = $_POST['cm-list-key'];
			$user = json_decode($_POST['cm-list-entity'], true);
			$ok = $adb->update_user($username, $user);
			$response = array('ok' => $ok);
			if ($ok) {
				if (isset($user['username']) && $user['username']) {
					$username = $user['username'];
				}
				$user = $adb->get_user($username);
				if ($user) $response['row'] = array(
					'entity' => $user,
					'html' => cm_admin_list_row($list_def, $user),
					'search' => $user['search-content']
				);
			}
			echo json_encode($response);
			break;
		case 'delete':
			$username = $_POST['cm-list-key'];
			$ok = $adb->delete_user($username);
			$response = array('ok' => $ok);
			echo json_encode($response);
			break;
	}
	exit(0);
}

cm_admin_head('Admin Users');
cm_admin_list_page_head($list_def);
cm_admin_body('Admin Users');
cm_admin_nav('admin-users');

echo '<article>';
cm_admin_search_box($list_def);
cm_admin_list_table($list_def);
echo '</article>';

cm_admin_dialogs();
cm_admin_edit_dialog_start();

echo '<table border="0" cellpadding="0" cellspacing="0" class="cm-form-table cm-user-editor">';
	echo '<tr>';
		echo '<th><label for="ea-name">Name:</label></th>';
		echo '<td><input type="text" name="ea-name" id="ea-name"></td>';
	echo '</tr>';
	echo '<tr>';
		echo '<th><label for="ea-username">User Name:</label></th>';
		echo '<td><input type="text" name="ea-username" id="ea-username"></td>';
	echo '</tr>';
	echo '<tr>';
		echo '<th><label for="ea-password">Password:</label></th>';
		echo '<td><input type="password" name="ea-password" id="ea-password"></td>';
	echo '</tr>';
	echo '<tr>';
		echo '<th></th>';
		echo '<td>';
			$first_group = true;
			foreach ($cm_admin_perms as $group) {
				$first_link = true;
				foreach ($group as $perm) {
					if ($first_link && !$first_group) echo '<hr>';
					echo '<div><label';
					if (isset($perm['description']) && $perm['description']) {
						echo ' title="';
						echo htmlspecialchars($perm['description']);
						echo '"';
					}
					echo '>';
					$id = htmlspecialchars('ea-permissions-' . $perm['id']);
					echo '<input type="checkbox"';
					echo ' name="' . $id . '"';
					echo ' id="' . $id . '"';
					echo ' class="ea-permissions">';
					echo htmlspecialchars($perm['name']);
					echo '</label></div>';
					$first_link = false;
				}
				if (!$first_link) $first_group = false;
			}
		echo '</td>';
	echo '</tr>';
echo '</table>';

cm_admin_edit_dialog_end();
cm_admin_delete_dialog($list_def);
cm_admin_tail();
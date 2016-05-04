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
	'row-key' => 'username',
	'name-key' => 'name',
	'row-actions' => array('edit', 'delete'),
	'table-actions' => array('add'),
	'add-title' => 'Add User',
	'edit-title' => 'Edit User',
	'delete-title' => 'Delete User',
	'edit-clear-function' => 'function() { console.log("clear"); }',
	'edit-load-function' => 'function(i, e) { console.log("load", i, e); }',
	'edit-save-function' => 'function(i, e) { console.log("save", i, e); }',
);

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
			$user = json_decode($_POST['cm-list-entity']);
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
			$user = json_decode($_POST['cm-list-entity']);
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

echo '<p>Editor goes here.</p>';

cm_admin_edit_dialog_end();
cm_admin_delete_dialog($list_def);
cm_admin_tail();
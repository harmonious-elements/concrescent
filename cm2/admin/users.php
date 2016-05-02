<?php

require_once dirname(__FILE__).'/../lib/util/util.php';
require_once dirname(__FILE__).'/admin.php';
require_once dirname(__FILE__).'/admin-lists.php';
require_once dirname(__FILE__).'/admin-perms.php';

cm_admin_check_permission('admin-users', 'admin-users');

$list_def = array(
	'ajax-url' => get_site_url(false) . '/admin/users.php',
	'search-criteria' => 'name',
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
	'row-actions' => array('edit', 'delete'),
	'table-actions' => array('add')
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
					'search' => array($user['name'], $user['username'])
				);
			}
			echo json_encode($response);
			break;
		case 'create':
			$user = json_decode($_POST['cm-list-entity']);
			$ok = $adb->create_user($user);
			$response = array('ok' => $ok);
			echo json_encode($response);
			break;
		case 'update':
			$username = $_POST['cm-list-key'];
			$user = json_decode($_POST['cm-list-entity']);
			$ok = $adb->update_user($username, $user);
			$response = array('ok' => $ok);
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
cm_admin_tail();
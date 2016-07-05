<?php

require_once dirname(__FILE__).'/../../config/config.php';
require_once dirname(__FILE__).'/../../lib/database/application.php';
require_once dirname(__FILE__).'/../../lib/util/util.php';
require_once dirname(__FILE__).'/../../lib/util/cmlists.php';
require_once dirname(__FILE__).'/../admin.php';

$context = (isset($_GET['c']) ? trim($_GET['c']) : null);
if (!$context) {
	header('Location: ../');
	exit(0);
}
$ctx_lc = strtolower($context);
$ctx_uc = strtoupper($context);
$ctx_info = (
	isset($cm_config['application_types'][$ctx_uc]) ?
	$cm_config['application_types'][$ctx_uc] : null
);
if (!$ctx_info) {
	header('Location: ../');
	exit(0);
}
$ctx_name = $ctx_info['nav_prefix'];
$ctx_name_lc = strtolower($ctx_name);

cm_admin_check_permission('application-blacklist-'.$ctx_lc, 'application-blacklist-'.$ctx_lc);

$apdb = new cm_application_db($db, $context);

$list_def = array(
	'ajax-url' => get_site_url(false) . '/admin/application/app-blacklist.php?c=' . $ctx_lc,
	'entity-type' => 'blacklist entry',
	'entity-type-pl' => 'blacklist entries',
	'search-criteria' => 'business or application name',
	'columns' => array(
		array(
			'name' => 'Business Name',
			'key' => 'business-name',
			'type' => 'text'
		),
		array(
			'name' => 'Application Name',
			'key' => 'application-name',
			'type' => 'text'
		),
		array(
			'name' => 'Added/Approved By',
			'key' => 'added-by',
			'type' => 'text'
		),
	),
	'sort-order' => array(0),
	'row-key' => 'id',
	'name-key' => 'application-name',
	'row-actions' => array('edit', 'delete'),
	'table-actions' => array('add'),
	'add-title' => 'Add Blacklist Entry',
	'edit-title' => 'Edit Blacklist Entry',
	'delete-title' => 'Delete Blacklist Entry'
);
$list_def['edit-clear-function'] = <<<END
	function() {
		$('#ea-business-name').val('');
		$('#ea-application-name').val('');
		$('#ea-added-by').val('');
	}
END;
$list_def['edit-load-function'] = <<<END
	function(id, e) {
		$('#ea-business-name').val(e['business-name']);
		$('#ea-application-name').val(e['application-name']);
		$('#ea-added-by').val(e['added-by']);
	}
END;
$list_def['edit-save-function'] = <<<END
	function(id, e) {
		return {
			'business-name': $('#ea-business-name').val(),
			'application-name': $('#ea-application-name').val(),
			'added-by': $('#ea-added-by').val()
		};
	}
END;

if (isset($_POST['cm-list-action'])) {
	header('Content-type: text/plain');
	switch ($_POST['cm-list-action']) {
		case 'list':
			$application_blacklist_entries = $apdb->list_application_blacklist_entries();
			$response = cm_list_process_entities($list_def, $application_blacklist_entries);
			echo json_encode($response);
			break;
		case 'create':
			$application_blacklist_entry = json_decode($_POST['cm-list-entity'], true);
			$id = $apdb->create_application_blacklist_entry($application_blacklist_entry);
			$ok = ($id !== false);
			$response = array('ok' => $ok);
			if ($ok) {
				$application_blacklist_entry = $apdb->get_application_blacklist_entry($id);
				if ($application_blacklist_entry) {
					$response['row'] = cm_list_make_row($list_def, $application_blacklist_entry);
				}
			}
			echo json_encode($response);
			break;
		case 'update':
			$application_blacklist_entry = json_decode($_POST['cm-list-entity'], true);
			$application_blacklist_entry['id'] = $_POST['cm-list-key'];
			$ok = $apdb->update_application_blacklist_entry($application_blacklist_entry);
			$response = array('ok' => $ok);
			if ($ok) {
				$application_blacklist_entry = $apdb->get_application_blacklist_entry($application_blacklist_entry['id']);
				if ($application_blacklist_entry) {
					$response['row'] = cm_list_make_row($list_def, $application_blacklist_entry);
				}
			}
			echo json_encode($response);
			break;
		case 'delete':
			$id = $_POST['cm-list-key'];
			$ok = $apdb->delete_application_blacklist_entry($id);
			$response = array('ok' => $ok);
			echo json_encode($response);
			break;
	}
	exit(0);
}

cm_admin_head($ctx_name . ' App Blacklist');
cm_list_head($list_def);
cm_admin_body($ctx_name . ' App Blacklist');
cm_admin_nav('application-blacklist-' . $ctx_lc);

echo '<article>';
cm_list_search_box($list_def);
cm_list_table($list_def);
echo '</article>';

cm_admin_dialogs();
cm_list_edit_dialog_start();

echo '<table border="0" cellpadding="0" cellspacing="0" class="cm-form-table">';
	echo '<tr>';
		echo '<th><label for="ea-business-name">Business Name:</label></th>';
		echo '<td><input type="text" name="ea-business-name" id="ea-business-name"></td>';
	echo '</tr>';
	echo '<tr>';
		echo '<th><label for="ea-application-name">Application Name:</label></th>';
		echo '<td><input type="text" name="ea-application-name" id="ea-application-name"></td>';
	echo '</tr>';
	echo '<tr>';
		echo '<th><label for="ea-added-by">Added/Approved By:</label></th>';
		echo '<td><input type="text" name="ea-added-by" id="ea-added-by"></td>';
	echo '</tr>';
echo '</table>';

cm_list_edit_dialog_end();
cm_list_dialogs($list_def);
cm_admin_tail();
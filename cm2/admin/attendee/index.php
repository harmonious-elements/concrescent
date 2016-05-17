<?php

require_once dirname(__FILE__).'/../../lib/database/attendee.php';
require_once dirname(__FILE__).'/../../lib/database/forms.php';
require_once dirname(__FILE__).'/../../lib/util/util.php';
require_once dirname(__FILE__).'/../../lib/util/cmlists.php';
require_once dirname(__FILE__).'/../admin.php';

cm_admin_check_permission('attendees', array('||', 'attendees', 'attendees-view', 'attendees-edit', 'attendees-delete'));
$can_view = $adb->user_has_permission($admin_user, 'attendees-view');
$can_edit = $adb->user_has_permission($admin_user, 'attendees-edit');
$can_delete = $adb->user_has_permission($admin_user, 'attendees-delete');

$atdb = new cm_attendee_db($db);
$name_map = $atdb->get_badge_type_name_map();

$fdb = new cm_forms_db($db, 'attendee');
$questions = $fdb->list_questions();

$columns = array(
	array(
		'name' => 'ID',
		'key' => 'id-string',
		'type' => 'text'
	),
	array(
		'name' => 'Real Name',
		'key' => 'real-name',
		'type' => 'text'
	),
	array(
		'name' => 'Fandom Name',
		'key' => 'fandom-name',
		'type' => 'text'
	),
	array(
		'name' => 'Badge Type',
		'key' => 'badge-type-name',
		'type' => 'text'
	),
	array(
		'name' => 'Email Address',
		'key' => 'email-address',
		'type' => 'email-subbed'
	),
);
foreach ($questions as $question) {
	if (!$question['active'] || !$question['listed']) continue;
	$is_array_type = ($question['type'] == 'checkbox');
	$key_prefix = 'form-answer-' . ($is_array_type ? 'array' : 'string') . '-';
	$column_type = ($is_array_type ? 'array-short' : 'text');
	$columns[] = array(
		'name' => $question['text'],
		'key' => $key_prefix . $question['question-id'],
		'type' => $column_type
	);
}
$columns = array_merge($columns, array(
	array(
		'name' => 'Payment Status',
		'key' => 'payment-status',
		'type' => 'status-label'
	),
	array(
		'name' => 'Promo Code',
		'key' => 'payment-promo-code',
		'type' => 'text'
	),
	array(
		'name' => 'Payment Date',
		'key' => 'payment-date',
		'type' => 'text'
	),
));
$list_def = array(
	'loader' => 'server-side',
	'ajax-url' => get_site_url(false) . '/admin/attendee/index.php',
	'entity-type' => 'attendee',
	'entity-type-pl' => 'attendees',
	'search-criteria' => 'name, badge type, contact info, or transaction ID',
	'search-delay' => 500,
	'qr' => 'auto',
	'columns' => $columns,
	'sort-order' => array(~0),
	'row-key' => 'id',
	'name-key' => 'display-name',
	'row-actions' => array(
		(($can_view || $can_edit) ? 'edit' : null),
		($can_delete ? 'delete' : null)
	),
	'table-actions' => array(($can_edit ? 'add' : null)),
	'edit-label' => ($can_edit ? 'Edit' : 'View'),
	'add-url' => get_site_url(false) . '/admin/attendee/edit.php',
	'edit-url' => get_site_url(false) . '/admin/attendee/edit.php?id=',
	'delete-title' => 'Delete Attendee'
);

if (isset($_POST['cm-list-action'])) {
	header('Content-type: text/plain');
	switch ($_POST['cm-list-action']) {
		case 'list':
			$attendees = $atdb->list_attendees(
				null, null, null, null, $name_map, $fdb
			);
			$rows = array();
			foreach ($attendees as $attendee) {
				$rows[] = array(
					'entity' => $attendee,
					'html' => cm_list_row($list_def, $attendee),
					'search' => $attendee['search-content']
				);
			}
			$response = cm_list_process($list_def, $rows);
			echo json_encode($response);
			break;
		case 'delete':
			$id = $_POST['cm-list-key'];
			$ok = $atdb->delete_attendee($id);
			$response = array('ok' => $ok);
			echo json_encode($response);
			break;
	}
	exit(0);
}

cm_admin_head('Attendees');
cm_list_head($list_def);
cm_admin_body('Attendees');
cm_admin_nav('attendees');

echo '<article>';
cm_list_search_box($list_def);
cm_list_table($list_def);
echo '</article>';

cm_admin_dialogs();
cm_list_dialogs($list_def);
cm_admin_tail();
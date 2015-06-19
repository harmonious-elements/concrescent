<?php

require_once dirname(__FILE__).'/admin.php';
require_once dirname(__FILE__).'/../lib/dal/lists.php';
require_once dirname(__FILE__).'/../lib/ui/lists.php';
require_once dirname(__FILE__).'/../lib/ui/questions.php';

$conn = get_db_connection();
db_require_table('booth_extension_questions', $conn);

function render_extension_questions($connection) {
	$results = mysql_query('SELECT * FROM '.db_table_name('booth_extension_questions').' ORDER BY `order`', $connection);
	while ($result = mysql_fetch_assoc($results)) {
		$result = decode_extension_question('booth', $result);
		echo render_list_row(
			array(
				$result['question'],
				$result['type_string'],
				($result['required'] ? 'Yes' : 'No'),
			),
			array(
				'ea-id' => $result['id'],
				'ea-question' => $result['question'],
				'ea-description' => $result['description'],
				'ea-type' => $result['type'],
				'ea-type-values' => $result['type_values'],
				'ea-required' => $result['required'],
				'ea-in-list' => $result['in_list'],
				'ea-active' => $result['active'],
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
		case 'activate': activate_entity('booth_extension_questions', $id, $conn); break;
		case 'deactivate': deactivate_entity('booth_extension_questions', $id, $conn); break;
		case 'delete': delete_entity('booth_extension_questions', $id, $conn); break;
		case 'reorder': reorder_entities('booth_extension_questions', $id, (int)$_POST['direction'], $conn); break;
		case 'save': upsert_ordered_entity('booth_extension_questions', $id, encode_extension_question('booth', $_POST), $conn); break;
	}
	render_extension_questions($conn);
	exit(0);
}

render_admin_head('Table Extra Questions');

echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('cmlists.js')) . '"></script>';
?><script type="text/javascript">
typeChanged = function() {
	var type = $('.edit-type').val();
	switch (type) {
		case 'radio':
		case 'checkbox':
		case 'select':
			$('.tr-type-values').removeClass('hidden');
			break;
		default:
			$('.tr-type-values').addClass('hidden');
			break;
	}
};
listPage({
	ajaxUrl: 'booth_extension_questions.php',
	listItemNameSelector: '.ea-question',
	switchable: true,
	deleteable: true,
	reorderable: true,
	editDialog: true,
	editDialogTitle: 'Edit Question',
	editDialogStart: function(self, id, question) {
		$('.edit-id').val(id);
		$('.edit-question').val(question);
		$('.edit-description').val(self.find('.ea-description').val());
		$('.edit-type').val(self.find('.ea-type').val());
		$('.edit-type-values').val(self.find('.ea-type-values').val());
		typeChanged();
		$('.edit-required').attr('checked', !!self.find('.ea-required').val());
		$('.edit-in-list').attr('checked', !!self.find('.ea-in-list').val());
		$('.edit-active').attr('checked', !!self.find('.ea-active').val());
	},
	addDialog: true,
	addDialogTitle: 'Add Question',
	addDialogStart: function() {
		$('.edit-id').val('');
		$('.edit-question').val('');
		$('.edit-description').val('');
		$('.edit-type').val('text');
		$('.edit-type-values').val('');
		typeChanged();
		$('.edit-required').attr('checked', false);
		$('.edit-in-list').attr('checked', false);
		$('.edit-active').attr('checked', true);
	},
	addEditDialogInit: function() {
		$('.edit-type').change(typeChanged);
		$('.edit-type').keyup(typeChanged);
		$('.edit-type').keydown(typeChanged);
	},
	addEditDialogNameSelector: '.edit-question',
	addEditDialogGetSaveData: function(id, question) {
		return {
			'id': id,
			'question': question,
			'description': $('.edit-description').val(),
			'type': $('.edit-type').val(),
			'type_values': $('.edit-type-values').val(),
			'required': ($('.edit-required').attr('checked') ? 1 : 0),
			'in_list': ($('.edit-in-list').attr('checked') ? 1 : 0),
			'active': ($('.edit-active').attr('checked') ? 1 : 0),
		};
	},
});
</script><?php

render_admin_body('Table Extra Questions');

echo '<div class="card entity-list-card">';
render_list_table(array('Question', 'Type', 'Required'), 'render_extension_questions', true, $conn);
echo '</div>';

render_admin_dialogs();

render_delete_dialog('question', true);

render_edit_dialog_start();
render_extension_question_editor();
render_edit_dialog_end();

render_admin_tail();
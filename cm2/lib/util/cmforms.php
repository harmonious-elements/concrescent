<?php

require_once dirname(__FILE__).'/res.php';
require_once dirname(__FILE__).'/util.php';

function cm_form_label($id, $text) {
	$htmlid = $id ? ('cm-question-' . htmlspecialchars($id)) : $id;
	$out = '<label';
	if ($htmlid) $out .= ' for="' . $htmlid . '"';
	return $out . '>' . htmlspecialchars($text) . '</label>';
}

function cm_form_input($id, $type, $values, $answer, $required = false, $disabled = false) {
	$htmlid = $id ? ('cm-question-' . htmlspecialchars($id)) : $id;
	switch ($type) {
		case 'text':
		case 'url':
		case 'email':
			$out = '<input type="' . $type . '"';
			if ($htmlid) $out .= ' id="' . $htmlid . '"';
			if ($htmlid) $out .= ' name="' . $htmlid . '"';
			if ($answer) $out .= ' value="' . htmlspecialchars(implode(' ', $answer)) . '"';
			if ($required) $out .= ' required';
			if ($disabled) $out .= ' disabled';
			return $out . '>';
		case 'textarea':
			$out = '<textarea';
			if ($htmlid) $out .= ' id="' . $htmlid . '"';
			if ($htmlid) $out .= ' name="' . $htmlid . '"';
			if ($required) $out .= ' required';
			if ($disabled) $out .= ' disabled';
			$out .= '>';
			if ($answer) $out .= htmlspecialchars(implode("\n", $answer));
			return $out . '</textarea>';
		case 'radio':
			$out = '';
			foreach ($values as $i => $value) {
				$valueid = $htmlid ? ($htmlid . '-' . htmlspecialchars($i)) : $htmlid;
				if ($out) $out .= '<br>';
				$out .= '<label><input type="radio"';
				if ($valueid) $out .= ' id="' . $valueid . '"';
				if ($htmlid) $out .= ' name="' . $htmlid . '"';
				$out .= ' value="' . htmlspecialchars($value) . '"';
				if ($answer && in_array($value, $answer)) $out .= ' checked';
				if ($disabled) $out .= ' disabled';
				$out .= '>' . htmlspecialchars($value) . '</label>';
			}
			return $out;
		case 'checkbox':
			$out = '';
			foreach ($values as $i => $value) {
				$valueid = $htmlid ? ($htmlid . '-' . htmlspecialchars($i)) : $htmlid;
				if ($out) $out .= '<br>';
				$out .= '<label><input type="checkbox"';
				if ($valueid) $out .= ' id="' . $valueid . '"';
				if ($valueid) $out .= ' name="' . $valueid . '"';
				$out .= ' value="' . htmlspecialchars($value) . '"';
				if ($answer && in_array($value, $answer)) $out .= ' checked';
				if ($disabled) $out .= ' disabled';
				$out .= '>' . htmlspecialchars($value) . '</label>';
			}
			return $out;
		case 'select':
			$out = '<select';
			if ($htmlid) $out .= ' id="' . $htmlid . '"';
			if ($htmlid) $out .= ' name="' . $htmlid . '"';
			if ($required) $out .= ' required';
			if ($disabled) $out .= ' disabled';
			$out .= '>';
			foreach ($values as $value) {
				$out .= '<option value="' . htmlspecialchars($value) . '"';
				if ($answer && in_array($value, $answer)) $out .= ' selected';
				$out .= '>' . htmlspecialchars($value) . '</option>';
			}
			return $out . '</select>';
		default:
			return '';
	}
}

function cm_form_posted_answer($id, $type) {
	$htmlid = 'cm-question-' . $id;
	switch ($type) {
		case 'text':
		case 'url':
		case 'email':
		case 'radio':
		case 'select':
			if (!isset($_POST[$htmlid])) return array();
			return array($_POST[$htmlid]);
		case 'textarea':
			if (!isset($_POST[$htmlid])) return array();
			$answer = $_POST[$htmlid];
			$answer = str_replace("\r\n", "\n", $answer);
			$answer = str_replace("\r", "\n", $answer);
			return ($answer ? explode("\n", $answer) : array());
		case 'checkbox':
			$answer = array();
			$prefix = $htmlid . '-';
			$prefixlen = strlen($prefix);
			foreach ($_POST as $k => $v) {
				if (substr($k, 0, $prefixlen) === $prefix) {
					$answer[] = $v;
				}
			}
			return $answer;
		default:
			return array();
	}
}

function cm_form_edit_head(&$form_def) {
	echo '<script type="text/javascript">';
		echo 'cm_form_def = (' . json_encode($form_def) . ');';
	echo '</script>';
	echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('cmforms.js', false)) . '"></script>';
}

function cm_form_edit_start() {
	echo '<div class="cm-form-editor">';
	echo '<table border="0" cellpadding="0" cellspacing="0" class="cm-form-table">';
}

function cm_form_edit_row($question, $answer) {
	switch ($question['type']) {
		case 'h1':
		case 'h2':
		case 'h3':
		case 'p':
			echo '<tr><td colspan="2">';
			echo '<' . $question['type'] . '>';
			echo safe_html_string($question['text']);
			echo '</' . $question['type'] . '>';
			echo '</td></tr>';
			break;
		case 'hr':
			echo '<tr><td colspan="2"><hr></td></tr>';
			break;
		default:
			echo '<tr><th>';
			echo cm_form_label(
				(isset($question['question-id']) ? $question['question-id'] : ''),
				(isset($question['text']) ? $question['text'] : '')
			);
			echo '</th><td>';
			echo cm_form_input(
				(isset($question['question-id']) ? $question['question-id'] : ''),
				(isset($question['type']) ? $question['type'] : ''),
				(isset($question['values']) ? $question['values'] : ''),
				$answer, false, true
			);
			echo '</td></tr>';
			break;
	}
}

function cm_form_edit_static_section($questions) {
	echo '<tbody class="cm-form-editor-static-section">';
	foreach ($questions as $question) {
		cm_form_edit_row($question, array('Question provided by system'));
	}
	echo '</tbody>';
}

function cm_form_edit_custom_text_section($name) {
	echo '<tbody class="cm-form-editor-custom-text-section"';
	echo ' id="customtextid-' . htmlspecialchars($name) . '">';
		echo '<tr title="Click to edit explanatory text." class="view-row">';
			echo '<td colspan="2">';
				echo '<p class="view-area"></p>';
			echo '</td>';
		echo '</tr>';
		echo '<tr class="edit-row hidden">';
			echo '<td colspan="2">';
				echo '<textarea></textarea>';
			echo '</td>';
		echo '</tr>';
		echo '<tr class="edit-row hidden">';
			echo '<td colspan="2" class="td-actions">';
				echo '<button class="cancel-edit-button">Cancel</button>';
				echo '<button class="confirm-edit-button">Save</button>';
			echo '</td>';
		echo '</tr>';
	echo '</tbody>';
}

function cm_form_edit_dynamic_section(&$form_def) {
	echo '<tbody class="cm-form-editor-dynamic-section"></tbody>';
	echo '<tbody class="cm-form-editor-dynamic-section-actions">';
		echo '<tr><td colspan="2" class="td-actions">';
			echo '<button class="add-button">Add New Question</button>';
		echo '</td></tr>';
	echo '</tbody>';
	echo '<tbody class="cm-form-editor-dynamic-section-editor hidden">';
		echo '<tr class="cm-form-editor-row-editor-row"><th><label>Type</label></th><td>';
			echo '<select class="ea-type">';
				echo '<option value="h1">Large Title</option>';
				echo '<option value="h2">Medium Title</option>';
				echo '<option value="h3">Small Title</option>';
				echo '<option value="p">Explanatory Text</option>';
				echo '<option value="hr">Section Break</option>';
				echo '<option value="text" selected>Short Answer</option>';
				echo '<option value="textarea">Long Answer</option>';
				echo '<option value="url">URL</option>';
				echo '<option value="email">Email Address</option>';
				echo '<option value="radio">Multiple Choice</option>';
				echo '<option value="checkbox">Checkboxes</option>';
				echo '<option value="select">Choose from a List</option>';
			echo '</select>';
		echo '</td></tr>';
		echo '<tr class="cm-form-editor-row-editor-row ear-text-short"><th><label>Label</label></th><td><input type="text" class="ea-text-short"></td></tr>';
		echo '<tr class="cm-form-editor-row-editor-row ear-text-long hidden"><th><label>Text</label></th><td><textarea class="ea-text-long"></textarea></td></tr>';
		echo '<tr class="cm-form-editor-row-editor-row ear-values hidden"><th><label>Values</label></th><td><textarea class="ea-values"></textarea></td></tr>';
		echo '<tr class="cm-form-editor-row-editor-row"><th><label>Active</label></th><td><label><input type="checkbox" checked class="ea-active">Question and answer appear on Review and Edit detail pages.</label></td></tr>';
		echo '<tr class="cm-form-editor-row-editor-row"><th><label>In List</label></th><td><label><input type="checkbox" class="ea-listed">Answers appear in a column on Review and Edit list pages.</label></td></tr>';
		echo '<tr class="cm-form-editor-row-editor-row"><th><label>Visible</label></th><td><label><input type="checkbox" checked class="ea-visible">Question appears on Register and Apply pages.</label> <a href="#" class="ea-visible-advanced">Advanced...</a></td></tr>';
		echo '<tr class="cm-form-editor-row-editor-row ear-visible-advanced hidden"><th></th><td>';
			if (isset($form_def['subcontext'])) {
				foreach ($form_def['subcontext'] as $subcontext) {
					echo '<label><input type="checkbox" checked class="ea-visible-' . htmlspecialchars($subcontext['id']) . '">' . htmlspecialchars($subcontext['name']) . '</label>';
				}
			}
		echo '</td></tr>';
		echo '<tr class="cm-form-editor-row-editor-row"><th><label>Required</label></th><td><label><input type="checkbox" class="ea-required">Question must be answered in order to submit.</label> <a href="#" class="ea-required-advanced">Advanced...</a></td></tr>';
		echo '<tr class="cm-form-editor-row-editor-row ear-required-advanced hidden"><th></th><td>';
			if (isset($form_def['subcontext'])) {
				foreach ($form_def['subcontext'] as $subcontext) {
					echo '<label><input type="checkbox" class="ea-required-' . htmlspecialchars($subcontext['id']) . '">' . htmlspecialchars($subcontext['name']) . '</label>';
				}
			}
		echo '</td></tr>';
		echo '<tr class="cm-form-editor-row-editor-row"><td colspan="2" class="td-actions">';
			echo '<button class="delete-button">Delete</button>';
			echo '<button class="up-button">&#x2191;</button>';
			echo '<button class="down-button">&#x2193;</button>';
			echo '<button class="cancel-edit-button">Cancel</button>';
			echo '<button class="confirm-edit-button">Save</button>';
		echo '</td></tr>';
	echo '</tbody>';
}

function cm_form_edit_end() {
	echo '</table>';
	echo '</div>';
}

function cm_form_edit_delete_dialog() {
	echo '<div class="dialog delete-dialog hidden">';
		echo '<div class="dialog-title">Delete Question</div>';
		echo '<div class="dialog-content">';
			echo '<p>Are you sure you want to delete the question <b class="delete-name"></b>?</p>';
			echo '<p>This action cannot be undone. If you are unsure, it is better to mark it inactive.</p>';
		echo '</div>';
		echo '<div class="dialog-buttons">';
			echo '<button class="cancel-delete-button">Cancel</button>';
			echo '<button class="soft-delete-button">Mark Inactive</button>';
			echo '<button class="confirm-delete-button">Delete</button>';
		echo '</div>';
	echo '</div>';
}

function cm_form_edit_process_requests($db) {
	if (isset($_POST['cm-form-action'])) {
		header('Content-type: text/plain');
		switch ($_POST['cm-form-action']) {
			case 'load-custom-text':
				$name = $_POST['cm-form-ct-name'];
				$text = $db->get_custom_text($name);
				if ($text === false) $text = '';
				$response = array('ok' => true, 'text' => $text);
				echo json_encode($response);
				break;
			case 'save-custom-text':
				$name = $_POST['cm-form-ct-name'];
				$text = $_POST['cm-form-ct-text'];
				$ok = $db->set_custom_text($name, $text);
				$response = array('ok' => $ok);
				echo json_encode($response);
				break;
			case 'render-dynamic-row':
				$question = json_decode($_POST['cm-form-question'], true);
				$answer = array('Answer provided by user');
				cm_form_edit_row($question, $answer);
				break;
			case 'get-question':
				$id = $_POST['cm-form-question-id'];
				$question = $db->get_question($id);
				$ok = ($question !== false);
				$response = array('ok' => $ok);
				if ($ok) $response['question'] = $question;
				echo json_encode($response);
				break;
			case 'list-questions':
				$questions = $db->list_questions();
				$ok = ($questions !== false);
				$response = array('ok' => $ok);
				if ($ok) $response['questions'] = $questions;
				echo json_encode($response);
				break;
			case 'create-question':
				$question = json_decode($_POST['cm-form-question'], true);
				$id = $db->create_question($question);
				$ok = ($id !== false);
				$response = array('ok' => $ok);
				if ($ok) {
					$response['question-id'] = $id;
					$question = $db->get_question($id);
					if ($question !== false) $response['question'] = $question;
				}
				echo json_encode($response);
				break;
			case 'update-question':
				$question = json_decode($_POST['cm-form-question'], true);
				$ok = $db->update_question($question);
				$response = array('ok' => $ok);
				if ($ok) {
					$id = $question['question-id'];
					$response['question-id'] = $id;
					$question = $db->get_question($id);
					if ($question !== false) $response['question'] = $question;
				}
				echo json_encode($response);
				break;
			case 'delete-question':
				$id = $_POST['cm-form-question-id'];
				$ok = $db->delete_question($id);
				$response = array('ok' => $ok);
				echo json_encode($response);
				break;
			case 'get-question-order':
				$order = $db->get_question_order();
				$ok = ($order !== false);
				$response = array('ok' => $ok);
				if ($ok) $response['order'] = $order;
				echo json_encode($response);
				break;
			case 'set-question-order':
				$order = json_decode($_POST['cm-form-question-order'], true);
				$order = $db->set_question_order($order);
				$ok = ($order !== false);
				$response = array('ok' => $ok);
				if ($ok) $response['order'] = $order;
				echo json_encode($response);
				break;
		}
		exit(0);
	}
}
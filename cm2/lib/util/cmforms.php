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
		}
		exit(0);
	}
}

function cm_form_edit_head($context) {
	echo '<script type="text/javascript">';
		echo 'cm_form_context = (' . json_encode($context) . ');';
	echo '</script>';
	echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('cmforms.js', false)) . '"></script>';
}

function cm_form_edit_start() {
	echo '<div class="cm-form-editor">';
	echo '<table border="0" cellpadding="0" cellspacing="0" class="cm-form-table">';
}

function cm_form_edit_static_section($questions) {
	echo '<tbody class="cm-form-editor-static-section">';
	foreach ($questions as $question) {
		switch ($question['type']) {
			case 'h1':
			case 'h2':
			case 'h3':
			case 'p':
				echo '<tr><td colspan="3">';
				echo '<' . $question['type'] . '>';
				echo safe_html_string($question['text']);
				echo '</' . $question['type'] . '>';
				echo '</td></tr>';
				break;
			case 'hr':
				echo '</tbody>';
				echo '</table>';
				echo '<hr>';
				echo '<table border="0" cellpadding="0" cellspacing="0" class="cm-form-table">';
				echo '<tbody class="cm-form-editor-static-section">';
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
					array('Question provided by system'), false, true
				);
				echo '</td></tr>';
				break;
		}
	}
	echo '</tbody>';
}

function cm_form_edit_custom_text_section($name) {
	echo '<tbody class="cm-form-editor-custom-text-section"';
	echo ' id="customtextid-' . htmlspecialchars($name) . '">';
		echo '<tr class="view-row">';
			echo '<td colspan="3">';
				echo '<p title="Click to edit explanatory text" class="view-area"></p>';
			echo '</td>';
		echo '</tr>';
		echo '<tr class="edit-row hidden">';
			echo '<td colspan="3">';
				echo '<textarea></textarea>';
			echo '</td>';
		echo '</tr>';
		echo '<tr class="edit-row hidden">';
			echo '<td colspan="3" class="td-actions">';
				echo '<button class="cancel-edit-button">Cancel</button>';
				echo '<button class="confirm-edit-button">Save</button>';
			echo '</td>';
		echo '</tr>';
	echo '</tbody>';
}

function cm_form_edit_end() {
	echo '</table>';
	echo '</div>';
}
<?php

require_once dirname(__FILE__).'/../base/util.php';

function render_extension_answers_h3_p($extension_questions, $extension_answers) {
	$out = '';
	foreach ($extension_questions as $question_id => $question) {
		$out .= '<h3>' . htmlspecialchars($question['question']) . '</h3>';
		$out .= '<p>';
		if (isset($extension_answers[$question_id])) {
			switch ($question['type']) {
				case 'url': $out .= url_link($extension_answers[$question_id]); break;
				case 'email': $out .= email_link($extension_answers[$question_id]); break;
				default: $out .= paragraph_string($extension_answers[$question_id]); break;
			}
		}
		$out .= '</p>';
	}
	return $out;
}

function render_extension_answer_input($question_id, $question, $extension_answers) {
	$out = '';
	switch ($question['type']) {
		case 'text':
			$out .= '<input type="text" name="extension_answer_'.$question_id.'" value="';
			if (isset($extension_answers[$question_id])) $out .= htmlspecialchars($extension_answers[$question_id]);
			$out .= '">';
			break;
		case 'textarea':
			$out .= '<textarea name="extension_answer_'.$question_id.'">';
			if (isset($extension_answers[$question_id])) $out .= htmlspecialchars($extension_answers[$question_id]);
			$out .= '</textarea>';
			break;
		case 'url':
			$out .= '<input type="url" name="extension_answer_'.$question_id.'" value="';
			if (isset($extension_answers[$question_id])) $out .= htmlspecialchars($extension_answers[$question_id]);
			$out .= '">';
			break;
		case 'email':
			$out .= '<input type="email" name="extension_answer_'.$question_id.'" value="';
			if (isset($extension_answers[$question_id])) $out .= htmlspecialchars($extension_answers[$question_id]);
			$out .= '">';
			break;
		case 'radio':
			$values = trim($question['type_values']);
			$values = str_replace("\r", "\n", $values);
			$values = preg_replace('/\n+/', "\n", $values);
			$values = explode("\n", $values);
			$first = true;
			foreach ($values as $value) {
				if ($first) $first = false; else $out .= '<br>';
				$out .= '<label><input type="radio" name="extension_answer_'.$question_id.'" value="'.htmlspecialchars($value).'"';
				if (isset($extension_answers[$question_id]) && $extension_answers[$question_id] == $value) $out .= ' checked="checked"';
				$out .= '>'.htmlspecialchars($value).'</label>';
			}
			break;
		case 'checkbox':
			$values = trim($question['type_values']);
			$values = str_replace("\r", "\n", $values);
			$values = preg_replace('/\n+/', "\n", $values);
			$values = explode("\n", $values);
			$answers = array();
			if (isset($extension_answers[$question_id]) && $extension_answers[$question_id]) {
				$answers = trim($extension_answers[$question_id]);
				$answers = str_replace("\r", "\n", $answers);
				$answers = preg_replace('/\n+/', "\n", $answers);
				$answers = explode("\n", $answers);
			}
			$i = 0;
			foreach ($values as $value) {
				if ($i) $out .= '<br>';
				$out .= '<label><input type="checkbox" name="extension_answer_'.$question_id.'_'.$i.'" value="'.htmlspecialchars($value).'"';
				if (in_array($value, $answers)) $out .= ' checked="checked"';
				$out .= '>'.htmlspecialchars($value).'</label>';
				$i++;
			}
			break;
		case 'select':
			$out .= '<select name="extension_answer_'.$question_id.'">';
			$values = trim($question['type_values']);
			$values = str_replace("\r", "\n", $values);
			$values = preg_replace('/\n+/', "\n", $values);
			$values = explode("\n", $values);
			foreach ($values as $value) {
				$out .= '<option value="'.htmlspecialchars($value).'"';
				if (isset($extension_answers[$question_id]) && $extension_answers[$question_id] == $value) $out .= ' selected="selected"';
				$out .= '>'.htmlspecialchars($value).'</option>';
			}
			$out .= '</select>';
			break;
	}
	return $out;
}

function render_extension_answers_editor($extension_questions, $extension_answers) {
	$out = '';
	foreach ($extension_questions as $question_id => $question) {
		$out .= '<tr><th><label for="extension_answer_'.$question_id.'">'.htmlspecialchars($question['question']).':</label></th>';
		$out .= '<td>'.render_extension_answer_input($question_id, $question, $extension_answers).'</td></tr>';
	}
	return $out;
}

function render_extension_answers_form($extension_questions, $extension_answers, $errors) {
	$out = '';
	foreach ($extension_questions as $question_id => $question) {
		if ($question['description_html']) {
			$out .= '<tr><th></th><td><b><label for="extension_answer_'.$question_id.'">'.htmlspecialchars($question['question']).':</label></b></td></tr>';
			$out .= '<tr><th></th><td>'.safe_html_string($question['description_html']).'</td></tr><tr><th></th><td>';
		} else {
			$out .= '<tr><th><label for="extension_answer_'.$question_id.'">'.htmlspecialchars($question['question']).':</label></th><td>';
		}
		$out .= render_extension_answer_input($question_id, $question, $extension_answers);
		if ($question['description_html']) {
			$out .= '</td></tr>';
			if (isset($errors['extension_answer_'.$question_id])) {
				$out .= '<tr><th></th><td><span class="error error-line">'.htmlspecialchars($errors['extension_answer_'.$question_id]).'</span></td></tr>';
			}
		} else {
			if (isset($errors['extension_answer_'.$question_id])) {
				switch ($question['type']) {
					case 'textarea':
					case 'radio':
					case 'checkbox':
						$out .= '<span class="error error-line">'.htmlspecialchars($errors['extension_answer_'.$question_id]).'</span>';
						break;
					default:
						$out .= '<span class="error">'.htmlspecialchars($errors['extension_answer_'.$question_id]).'</span>';
						break;
				}
			}
			$out .= '</td></tr>';
		}
	}
	return $out;
}

function get_posted_extension_answers($extension_questions, &$errors = null) {
	$extension_answers = array();
	foreach ($extension_questions as $question_id => $question) {
		switch ($question['type']) {
			case 'checkbox':
				$values = trim($question['type_values']);
				$values = str_replace("\r", "\n", $values);
				$values = preg_replace('/\n+/', "\n", $values);
				$values = explode("\n", $values);
				$answer = array();
				for ($i = 0; $i < count($values); $i++) {
					$v = 'extension_answer_' . $question_id . '_' . $i;
					if (isset($_POST[$v]) && $_POST[$v]) {
						$answer[] = $_POST[$v];
					}
				}
				$answer = implode("\n", $answer);
				break;
			default:
				$v = 'extension_answer_' . $question_id;
				$answer = (isset($_POST[$v]) ? trim($_POST[$v]) : null);
				break;
		}
		if ($answer) {
			$extension_answers[$question_id] = $answer;
		} else if ($question['required'] && $errors !== null) {
			$errors['extension_answer_' . $question_id] = 'This field is required.';
		}
	}
	return $extension_answers;
}

function extension_question_names($extension_questions) {
	$row = array();
	foreach ($extension_questions as $question_id => $question) {
		$row[] = $question['question'];
	}
	return $row;
}

function extension_question_names_in_list($extension_questions) {
	$row = array();
	foreach ($extension_questions as $question_id => $question) {
		if ($question['in_list']) {
			$row[] = $question['question'];
		}
	}
	return $row;
}

function extension_answer_values($extension_questions, $extension_answers) {
	$row = array();
	foreach ($extension_questions as $question_id => $question) {
		if (isset($extension_answers[$question_id])) {
			$row[] = $extension_answers[$question_id];
		} else {
			$row[] = '';
		}
	}
	return $row;
}

function extension_answer_values_in_list($extension_questions, $extension_answers) {
	$row = array();
	foreach ($extension_questions as $question_id => $question) {
		if ($question['in_list']) {
			if (isset($extension_answers[$question_id])) {
				switch ($question['type']) {
					case 'url': $row[] = array('html' => url_link_short($extension_answers[$question_id])); break;
					case 'email': $row[] = array('html' => email_link_short($extension_answers[$question_id])); break;
					default: $row[] = array('html' => paragraph_string($extension_answers[$question_id])); break;
				}
			} else {
				$row[] = '';
			}
		}
	}
	return $row;
}

function render_extension_question_editor() {
	echo '<input type="hidden" name="edit-id" class="edit-id">';
	echo '<tr>';
		echo '<th><label for="edit-question">Question:</label></th>';
		echo '<td><input type="text" name="edit-question" class="edit-question"></td>';
	echo '</tr>';
	echo '<tr>';
		echo '<th><label for="edit-description">Description:</label></th>';
		echo '<td><textarea name="edit-description" class="edit-description"></textarea></td>';
	echo '</tr>';
	echo '<tr>';
		echo '<th><label for="edit-type">Type:</label></th>';
		echo '<td>';
			echo '<select name="edit-type" class="edit-type">';
				echo '<option value="text">Text</option>';
				echo '<option value="textarea">Paragraph Text</option>';
				echo '<option value="url">URL</option>';
				echo '<option value="email">Email Address</option>';
				echo '<option value="radio">Multiple Choice</option>';
				echo '<option value="checkbox">Checkboxes</option>';
				echo '<option value="select">Choose from a List</option>';
			echo '</select>';
		echo '</td>';
	echo '</tr>';
	echo '<tr class="tr-type-values">';
		echo '<th><label for="edit-type-values">Values:</label></th>';
		echo '<td><textarea name="edit-type-values" class="edit-type-values"></textarea></td>';
	echo '</tr>';
	echo '<tr>';
		echo '<th><label for="edit-active">Active:</label></th>';
		echo '<td><label><input type="checkbox" name="edit-active" class="edit-active">This question appears on the form.</label></td>';
	echo '</tr>';
	echo '<tr>';
		echo '<th><label for="edit-required">Required:</label></th>';
		echo '<td><label><input type="checkbox" name="edit-required" class="edit-required">This question must be answered in order to submit.</label></td>';
	echo '</tr>';
	echo '<tr>';
		echo '<th><label for="edit-in-list">In List:</label></th>';
		echo '<td><label><input type="checkbox" name="edit-in-list" class="edit-in-list">Answers appear in a column on Review and Edit lists.</label></td>';
	echo '</tr>';
}
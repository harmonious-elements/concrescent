<?php

require_once dirname(__FILE__).'/dal.php';
require_once dirname(__FILE__).'/../schema/questions.php';
require_once dirname(__FILE__).'/../base/sql.php';
require_once dirname(__FILE__).'/../base/util.php';

function decode_extension_question($type, $result) {
	$id = (int)$result['id'];
	$question = unpurify_string($result['question']);
	$description = unpurify_string($result['description']);
	$description_html = safe_html_string($description);
	$type = $result['type'];
	switch ($type) {
		case 'text'    : $type_string = 'Text'              ; break;
		case 'textarea': $type_string = 'Paragraph Text'    ; break;
		case 'url'     : $type_string = 'URL'               ; break;
		case 'email'   : $type_string = 'Email Address'     ; break;
		case 'radio'   : $type_string = 'Multiple Choice'   ; break;
		case 'checkbox': $type_string = 'Checkboxes'        ; break;
		case 'select'  : $type_string = 'Choose from a List'; break;
	}
	$type_values = unpurify_string($result['type_values']);
	$required = !!$result['required'];
	$in_list = !!$result['in_list'];
	$active = !!$result['active'];
	$order = (int)$result['order'];
	return array(
		'id' => $id,
		'question' => $question,
		'description' => $description,
		'description_html' => $description_html,
		'type' => $type,
		'type_string' => $type_string,
		'type_values' => $type_values,
		'required' => $required,
		'in_list' => $in_list,
		'active' => $active,
		'order' => $order,
	);
}

function encode_extension_question($type, $result) {
	$set = array();
	if (isset($result['question'   ])) $set[] = '`question` = '    . q_string        ($result['question'   ]);
	if (isset($result['description'])) $set[] = '`description` = ' . q_string_or_null($result['description']);
	if (isset($result['type'       ])) $set[] = '`type` = '        . q_string        ($result['type'       ]);
	if (isset($result['type_values'])) $set[] = '`type_values` = ' . q_string_or_null($result['type_values']);
	if (isset($result['required'   ])) $set[] = '`required` = '    . q_boolean       ($result['required'   ]);
	if (isset($result['in_list'    ])) $set[] = '`in_list` = '     . q_boolean       ($result['in_list'    ]);
	if (isset($result['active'     ])) $set[] = '`active` = '      . q_boolean       ($result['active'     ]);
	return implode(', ', $set);
}

function decode_extension_answer($type, $result) {
	$entity_id = (int)$result[$type.'_id'];
	$question_id = (int)$result['question_id'];
	$answer = unpurify_string($result['answer']);
	return array(
		($type.'_id') => $entity_id,
		'question_id' => $question_id,
		'answer' => $answer,
	);
}

function encode_extension_answer($type, $result) {
	$set = array();
	if (isset($result[$type.'_id'  ])) $set[] = '`'.$type.'_id` = ' . q_int   ($result[$type.'_id'  ]);
	if (isset($result['question_id'])) $set[] = '`question_id` = '  . q_int   ($result['question_id']);
	if (isset($result['answer'     ])) $set[] = '`answer` = '       . q_string($result['answer'     ]);
	return implode(', ', $set);
}

function get_extension_questions($type, $connection) {
	db_require_table($type.'_extension_questions', $connection);
	$extension_questions = array();
	$results = mysql_query('SELECT * FROM '.db_table_name($type.'_extension_questions').' WHERE `question` NOT LIKE \'{{%}}\' ORDER BY `order`', $connection);
	while ($result = mysql_fetch_assoc($results)) {
		$question_id = (int)$result['id'];
		$extension_questions[$question_id] = decode_extension_question($type, $result);
	}
	return $extension_questions;
}

function get_active_extension_questions($type, $connection) {
	db_require_table($type.'_extension_questions', $connection);
	$extension_questions = array();
	$results = mysql_query('SELECT * FROM '.db_table_name($type.'_extension_questions').' WHERE `active` AND `question` NOT LIKE \'{{%}}\' ORDER BY `order`', $connection);
	while ($result = mysql_fetch_assoc($results)) {
		$question_id = (int)$result['id'];
		$extension_questions[$question_id] = decode_extension_question($type, $result);
	}
	return $extension_questions;
}

function get_active_question_descriptions($type, $connection) {
	db_require_table($type.'_extension_questions', $connection);
	$descriptions = array();
	$results = mysql_query('SELECT * FROM '.db_table_name($type.'_extension_questions').' WHERE `active` AND `question` LIKE \'{{%}}\' ORDER BY `order`', $connection);
	while ($result = mysql_fetch_assoc($results)) {
		$question = unpurify_string($result['question']);
		$description = unpurify_string($result['description']);
		$description_html = safe_html_string($description);
		$descriptions[substr($question, 2, -2)] = $description_html;
	}
	return $descriptions;
}

function get_extension_answers($type, $entity_id, $connection) {
	db_require_table($type.'_extension_answers', $connection);
	$extension_answers = array();
	$results = mysql_query('SELECT * FROM '.db_table_name($type.'_extension_answers').' WHERE `'.$type.'_id` = '.(int)$entity_id, $connection);
	while ($result = mysql_fetch_assoc($results)) {
		$question_id = (int)$result['question_id'];
		$answer = unpurify_string($result['answer']);
		$extension_answers[$question_id] = $answer;
	}
	return $extension_answers;
}

function set_extension_answers($type, $entity_id, $extension_answers, $connection) {
	db_require_table($type.'_extension_answers', $connection);
	mysql_query('DELETE FROM '.db_table_name($type.'_extension_answers').' WHERE `'.$type.'_id` = '.(int)$entity_id, $connection);
	foreach ($extension_answers as $question_id => $answer) {
		if ($answer) {
			$set = encode_extension_answer($type, array(
				($type.'_id') => $entity_id,
				'question_id' => $question_id,
				'answer' => $answer,
			));
			mysql_query('INSERT INTO '.db_table_name($type.'_extension_answers').' SET '.$set, $connection);
		}
	}
}
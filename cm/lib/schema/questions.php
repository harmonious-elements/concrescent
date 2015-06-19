<?php

require_once dirname(__FILE__).'/schema.php';

function extension_qa_schema($type) {
	db_schema(array(
		($type.'_extension_questions') => (
			'`id` INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,'.
			'`question` VARCHAR(255) NOT NULL,'.
			'`description` TEXT NULL,'.
			'`type` ENUM(\'text\',\'textarea\',\'url\',\'email\',\'radio\',\'checkbox\',\'select\') NOT NULL,'.
			'`type_values` TEXT NULL,'.
			'`required` BOOLEAN NOT NULL,'.
			'`in_list` BOOLEAN NOT NULL,'.
			'`active` BOOLEAN NOT NULL,'.
			'`order` INTEGER NOT NULL'
		),
		($type.'_extension_answers') => (
			'`'.$type.'_id` INTEGER NOT NULL,'.
			'`question_id` INTEGER NOT NULL,'.
			'`answer` TEXT NOT NULL'
		),
	));
}
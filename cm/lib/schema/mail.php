<?php

require_once dirname(__FILE__).'/schema.php';

db_schema(array(
	'mail_templates' => (
		'`name` VARCHAR(255) NOT NULL PRIMARY KEY,'.
		'`contact_address` VARCHAR(255) NOT NULL,'.
		'`from` VARCHAR(255) NOT NULL,'.
		'`bcc` VARCHAR(255) NULL,'.
		'`subject` VARCHAR(255) NOT NULL,'.
		'`body` TEXT NOT NULL'
	),
));
<?php

require_once dirname(__FILE__).'/schema.php';

db_schema(array(
	'admin_users' => (
		'`name` VARCHAR(255) NOT NULL,'.
		'`username` VARCHAR(255) NOT NULL,'.
		'`password` VARCHAR(255) NOT NULL,'.
		'`permissions` TEXT NOT NULL'
	),
));
<?php

require_once dirname(__FILE__).'/schema.php';

db_schema(array(
	'badge_artwork' => (
		'`id` INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,'.
		'`filename` VARCHAR(255) NOT NULL UNIQUE KEY,'.
		'`vertical` BOOLEAN NOT NULL'
	),
	'badge_artwork_field' => (
		'`badge_artwork_id` INTEGER NOT NULL,'.
		'`field_type` ENUM('.
			'\'id\',\'id-string\','.
			'\'first-name\',\'last-name\',\'real-name\',\'fandom-name\','.
			'\'only-name\',\'large-name\',\'small-name\',\'display-name\','.
			'\'badge-id\',\'badge-id-string\',\'badge-name\','.
			'\'group-id\',\'group-id-string\',\'group-name\','.
			'\'assigned-position\''.
		') NOT NULL,'.
		'`top` INTEGER NULL,'.
		'`left` INTEGER NULL,'.
		'`right` INTEGER NULL,'.
		'`bottom` INTEGER NULL,'.
		'`font_size` INTEGER NULL,'.
		'`font_family` VARCHAR(255) NULL,'.
		'`font_weight_bold` BOOLEAN NULL,'.
		'`font_style_italic` BOOLEAN NULL,'.
		'`color` VARCHAR(255) NULL,'.
		'`background` VARCHAR(255) NULL,'.
		'`color_minors` VARCHAR(255) NULL,'.
		'`background_minors` VARCHAR(255) NULL'
	),
	'badge_artwork_map' => (
		'`badge_id_string` VARCHAR(255) NOT NULL,'.
		'`badge_artwork_id` INTEGER NOT NULL'
	),
));
<?php

require_once dirname(__FILE__).'/schema.php';
require_once dirname(__FILE__).'/questions.php';

db_schema(array(
	'staffer_badges' => (
		'`id` INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,'.
		'`name` VARCHAR(255) NOT NULL,'.
		'`description` TEXT NULL,'.
		'`start_date` DATE NULL,'.
		'`end_date` DATE NULL,'.
		'`min_age` INTEGER NULL,'.
		'`max_age` INTEGER NULL,'.
		'`count` INTEGER NULL,'.
		'`active` BOOLEAN NOT NULL,'.
		'`price` DECIMAL(7,2) NOT NULL,'.
		'`order` INTEGER NOT NULL'
	),
	'staffers' => (
		'`id` INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,'.
		'`replaced_by` INTEGER NULL,'.
		'`first_name` VARCHAR(255) NOT NULL,'.
		'`last_name` VARCHAR(255) NOT NULL,'.
		'`fandom_name` VARCHAR(255) NULL,'.
		'`date_of_birth` DATE NOT NULL,'.
		'`badge_id` INTEGER NOT NULL,'.
		'`email_address` VARCHAR(255) NOT NULL,'.
		'`phone_number` VARCHAR(255) NULL,'.
		'`address_1` VARCHAR(255) NULL,'.
		'`address_2` VARCHAR(255) NULL,'.
		'`city` VARCHAR(255) NULL,'.
		'`state` VARCHAR(255) NULL,'.
		'`zip_code` VARCHAR(255) NULL,'.
		'`country` VARCHAR(255) NULL,'.
		'`dates_available` TEXT NOT NULL,'.
		'`application_status` ENUM(\'Submitted\',\'Accepted\',\'Maybe\',\'Rejected\',\'Cancelled\',\'Pulled\') NOT NULL,'.
		'`assigned_position` VARCHAR(255) NULL,'.
		'`notes` TEXT NULL,'.
		'`ice_name` VARCHAR(255) NULL,'.
		'`ice_relationship` VARCHAR(255) NULL,'.
		'`ice_email_address` VARCHAR(255) NULL,'.
		'`ice_phone_number` VARCHAR(255) NULL,'.
		'`payment_status` ENUM(\'Incomplete\',\'Cancelled\',\'Completed\',\'Refunded\',\'Pulled\') NOT NULL,'.
		'`payment_type` VARCHAR(255) NULL,'.
		'`payment_txn_id` VARCHAR(255) NULL,'.
		'`payment_price` DECIMAL(7,2) NULL,'.
		'`payment_date` DATETIME NULL,'.
		'`payment_details` TEXT NULL,'.
		'`payment_lookup_key` VARCHAR(255) NULL,'.
		'`print_count` INTEGER NULL,'.
		'`print_time` DATETIME NULL,'.
		'`checkin_count` INTEGER NULL,'.
		'`checkin_time` DATETIME NULL,'.
		'`date_created` DATETIME NOT NULL,'.
		'`date_modified` DATETIME NOT NULL'
	),
));

extension_qa_schema('staffer');
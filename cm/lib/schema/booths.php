<?php

require_once dirname(__FILE__).'/schema.php';
require_once dirname(__FILE__).'/questions.php';

db_schema(array(
	'booth_tables' => (
		'`id` VARCHAR(255) NOT NULL PRIMARY KEY,'.
		'`x` INTEGER NOT NULL,'.
		'`y` INTEGER NOT NULL'
	),
	'booth_badges' => (
		'`id` INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,'.
		'`name` VARCHAR(255) NOT NULL,'.
		'`description` TEXT NULL,'.
		'`start_date` DATE NULL,'.
		'`end_date` DATE NULL,'.
		'`count` INTEGER NULL,'.
		'`active` BOOLEAN NOT NULL,'.
		'`max_tables` INTEGER NULL,'.
		'`max_staffers` INTEGER NULL,'.
		'`price_per_table` DECIMAL(7,2) NOT NULL,'.
		'`price_per_staffer` DECIMAL(7,2) NOT NULL,'.
		'`staffers_in_table_price` INTEGER NOT NULL,'.
		'`max_prereg_discount` ENUM(\'None\',\'StafferPrice\',\'TablePrice\',\'TotalPrice\') NOT NULL,'.
		'`require_permit` BOOLEAN NOT NULL,'.
		'`order` INTEGER NOT NULL'
	),
	'booths' => (
		'`id` INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,'.
		'`replaced_by` INTEGER NULL,'.
		'`contact_first_name` VARCHAR(255) NOT NULL,'.
		'`contact_last_name` VARCHAR(255) NOT NULL,'.
		'`contact_email_address` VARCHAR(255) NOT NULL,'.
		'`contact_phone_number` VARCHAR(255) NULL,'.
		'`badge_id` INTEGER NOT NULL,'.
		'`business_name` VARCHAR(255) NOT NULL,'.
		'`booth_name` VARCHAR(255) NOT NULL,'.
		'`num_tables` INTEGER NOT NULL,'.
		'`num_staffers` INTEGER NOT NULL,'.
		'`application_status` ENUM(\'Submitted\',\'Accepted\',\'Maybe\',\'Rejected\',\'Cancelled\',\'Pulled\') NOT NULL,'.
		'`table_id` VARCHAR(255) NULL,'.
		'`permit_number` VARCHAR(255) NULL,'.
		'`payment_status` ENUM(\'Incomplete\',\'Cancelled\',\'Completed\',\'Refunded\',\'Pulled\') NOT NULL,'.
		'`payment_type` VARCHAR(255) NULL,'.
		'`payment_txn_id` VARCHAR(255) NULL,'.
		'`payment_original_price` DECIMAL(7,2) NULL,'.
		'`payment_final_price` DECIMAL(7,2) NULL,'.
		'`payment_date` DATETIME NULL,'.
		'`payment_details` TEXT NULL,'.
		'`payment_lookup_key` VARCHAR(255) NULL,'.
		'`date_created` DATETIME NOT NULL,'.
		'`date_modified` DATETIME NOT NULL'
	),
	'booth_staffers' => (
		'`id` INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,'.
		'`booth_id` INTEGER NOT NULL,'.
		'`first_name` VARCHAR(255) NOT NULL,'.
		'`last_name` VARCHAR(255) NOT NULL,'.
		'`fandom_name` VARCHAR(255) NULL,'.
		'`name_on_badge` ENUM(\'FandomReal\',\'RealFandom\',\'FandomOnly\',\'RealOnly\') NOT NULL,'.
		'`date_of_birth` DATE NOT NULL,'.
		'`email_address` VARCHAR(255) NOT NULL,'.
		'`phone_number` VARCHAR(255) NULL,'.
		'`attendee_id` INTEGER NULL,'.
		'`address_1` VARCHAR(255) NULL,'.
		'`address_2` VARCHAR(255) NULL,'.
		'`city` VARCHAR(255) NULL,'.
		'`state` VARCHAR(255) NULL,'.
		'`zip_code` VARCHAR(255) NULL,'.
		'`country` VARCHAR(255) NULL,'.
		'`ice_name` VARCHAR(255) NULL,'.
		'`ice_relationship` VARCHAR(255) NULL,'.
		'`ice_email_address` VARCHAR(255) NULL,'.
		'`ice_phone_number` VARCHAR(255) NULL,'.
		'`print_count` INTEGER NULL,'.
		'`print_time` DATETIME NULL,'.
		'`checkin_count` INTEGER NULL,'.
		'`checkin_time` DATETIME NULL,'.
		'`date_created` DATETIME NOT NULL,'.
		'`date_modified` DATETIME NOT NULL'
	),
));

extension_qa_schema('booth');
<?php

require_once dirname(__FILE__).'/schema.php';
require_once dirname(__FILE__).'/questions.php';

db_schema(array(
	'attendee_badges' => (
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
	'promo_codes' => (
		'`id` INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,'.
		'`code` VARCHAR(255) NOT NULL UNIQUE KEY,'.
		'`description` TEXT NULL,'.
		'`badge_id` INTEGER NULL,'.
		'`limit` INTEGER NULL,'.
		'`start_date` DATE NULL,'.
		'`end_date` DATE NULL,'.
		'`active` BOOLEAN NOT NULL,'.
		'`price` DECIMAL(7,2) NOT NULL,'.
		'`percentage` BOOLEAN NOT NULL'
	),
	'attendee_blacklist' => (
		'`id` INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,'.
		'`first_name` VARCHAR(255) NULL,'.
		'`last_name` VARCHAR(255) NULL,'.
		'`fandom_name` VARCHAR(255) NULL,'.
		'`email_address` VARCHAR(255) NULL,'.
		'`phone_number` VARCHAR(255) NULL,'.
		'`normalized_real_name` VARCHAR(255) NULL,'.
		'`normalized_reversed_name` VARCHAR(255) NULL,'.
		'`normalized_fandom_name` VARCHAR(255) NULL,'.
		'`normalized_email_address` VARCHAR(255) NULL,'.
		'`normalized_phone_number` VARCHAR(255) NULL'
	),
	'attendees' => (
		'`id` INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,'.
		'`first_name` VARCHAR(255) NOT NULL,'.
		'`last_name` VARCHAR(255) NOT NULL,'.
		'`fandom_name` VARCHAR(255) NULL,'.
		'`name_on_badge` ENUM(\'FandomReal\',\'RealFandom\',\'FandomOnly\',\'RealOnly\') NOT NULL,'.
		'`date_of_birth` DATE NOT NULL,'.
		'`badge_id` INTEGER NOT NULL,'.
		'`do_not_spam` BOOLEAN NOT NULL,'.
		'`email_address` VARCHAR(255) NOT NULL,'.
		'`phone_number` VARCHAR(255) NULL,'.
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
		'`payment_status` ENUM(\'Incomplete\',\'Cancelled\',\'Completed\',\'Refunded\',\'Pulled\') NOT NULL,'.
		'`payment_type` VARCHAR(255) NULL,'.
		'`payment_txn_id` VARCHAR(255) NULL,'.
		'`payment_original_price` DECIMAL(7,2) NULL,'.
		'`payment_promo_code` VARCHAR(255) NULL,'.
		'`payment_final_price` DECIMAL(7,2) NULL,'.
		'`payment_total_price` DECIMAL(7,2) NULL,'.
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

extension_qa_schema('attendee');
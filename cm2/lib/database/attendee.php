<?php

require_once dirname(__FILE__).'/database.php';

class cm_attendee_db {

	public $cm_db;

	public function __construct($cm_db) {
		$this->cm_db = $cm_db;
		$this->cm_db->table_def('attendee_badge_types', (
			'`id` INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,'.
			'`order` INTEGER NOT NULL,'.
			'`name` VARCHAR(255) NOT NULL,'.
			'`description` TEXT NULL,'.
			'`rewards` TEXT NULL,'.
			'`price` DECIMAL(7,2) NOT NULL,'.
			'`payable_onsite` BOOLEAN NOT NULL,'.
			'`active` BOOLEAN NOT NULL,'.
			'`quantity` INTEGER NULL,'.
			'`start_date` DATE NULL,'.
			'`end_date` DATE NULL,'.
			'`min_age` INTEGER NULL,'.
			'`max_age` INTEGER NULL'
		));
		$this->cm_db->table_def('attendee_promo_codes', (
			'`id` INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,'.
			'`code` VARCHAR(255) NOT NULL UNIQUE KEY,'.
			'`description` TEXT NULL,'.
			'`price` DECIMAL(7,2) NOT NULL,'.
			'`percentage` BOOLEAN NOT NULL,'.
			'`active` BOOLEAN NOT NULL,'.
			'`badge_type_ids` TEXT NULL,'.
			'`quantity` INTEGER NULL,'.
			'`start_date` DATE NULL,'.
			'`end_date` DATE NULL'
		));
		$this->cm_db->table_def('attendee_blacklist', (
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
		));
		$this->cm_db->table_def('attendees', (
			/* Record Info */
			'`id` INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,'.
			'`uuid` VARCHAR(255) NOT NULL UNIQUE KEY,'.
			'`date_created` DATETIME NOT NULL,'.
			'`date_modified` DATETIME NOT NULL,'.
			'`print_count` INTEGER NULL,'.
			'`print_first_time` DATETIME NULL,'.
			'`print_last_time` DATETIME NULL,'.
			'`checkin_count` INTEGER NULL,'.
			'`checkin_first_time` DATETIME NULL,'.
			'`checkin_last_time` DATETIME NULL,'.
			'`badge_type_id` INTEGER NOT NULL,'.
			'`notes` TEXT NULL,'.
			/* Personal Info */
			'`first_name` VARCHAR(255) NOT NULL,'.
			'`last_name` VARCHAR(255) NOT NULL,'.
			'`fandom_name` VARCHAR(255) NULL,'.
			'`name_on_badge` ENUM('.
				'\'Fandom Name Large, Real Name Small\','.
				'\'Real Name Large, Fandom Name Small\','.
				'\'Fandom Name Only\','.
				'\'Real Name Only\''.
			') NOT NULL,'.
			'`date_of_birth` DATE NOT NULL,'.
			/* Contact Info */
			'`subscribed` BOOLEAN NOT NULL,'.
			'`email_address` VARCHAR(255) NOT NULL,'.
			'`phone_number` VARCHAR(255) NULL,'.
			'`address_1` VARCHAR(255) NULL,'.
			'`address_2` VARCHAR(255) NULL,'.
			'`city` VARCHAR(255) NULL,'.
			'`state` VARCHAR(255) NULL,'.
			'`zip_code` VARCHAR(255) NULL,'.
			'`country` VARCHAR(255) NULL,'.
			/* Emergency Contact Info */
			'`ice_name` VARCHAR(255) NULL,'.
			'`ice_relationship` VARCHAR(255) NULL,'.
			'`ice_email_address` VARCHAR(255) NULL,'.
			'`ice_phone_number` VARCHAR(255) NULL,'.
			/* Payment Info */
			'`payment_status` ENUM('.
				'\'Incomplete\','.
				'\'Cancelled\','.
				'\'Rejected\','.
				'\'Completed\','.
				'\'Refunded\''.
			') NOT NULL,'.
			'`payment_badge_price` DECIMAL(7,2) NULL,'.
			'`payment_promo_code` VARCHAR(255) NULL,'.
			'`payment_promo_price` DECIMAL(7,2) NULL,'.
			'`payment_type` VARCHAR(255) NULL,'.
			'`payment_txn_id` VARCHAR(255) NULL,'.
			'`payment_txn_amt` DECIMAL(7,2) NULL,'.
			'`payment_date` DATETIME NULL,'.
			'`payment_details` TEXT NULL'
		));
	}

}
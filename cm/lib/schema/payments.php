<?php

require_once dirname(__FILE__).'/schema.php';

db_schema(array(
	'payments' => (
		'`id` INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,'.
		'`name` VARCHAR(255) NOT NULL,'.
		'`description` TEXT NULL,'.
		'`first_name` VARCHAR(255) NOT NULL,'.
		'`last_name` VARCHAR(255) NOT NULL,'.
		'`email_address` VARCHAR(255) NOT NULL,'.
		'`payment_status` ENUM(\'Incomplete\',\'Cancelled\',\'Completed\',\'Refunded\',\'Pulled\') NOT NULL,'.
		'`payment_type` VARCHAR(255) NULL,'.
		'`payment_txn_id` VARCHAR(255) NULL,'.
		'`payment_price` DECIMAL(7,2) NULL,'.
		'`payment_date` DATETIME NULL,'.
		'`payment_details` TEXT NULL,'.
		'`payment_lookup_key` VARCHAR(255) NULL'
	),
));
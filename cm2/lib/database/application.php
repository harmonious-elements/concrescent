<?php

require_once dirname(__FILE__).'/../../config/config.php';
require_once dirname(__FILE__).'/../util/util.php';
require_once dirname(__FILE__).'/../util/res.php';
require_once dirname(__FILE__).'/database.php';
require_once dirname(__FILE__).'/lists.php';
require_once dirname(__FILE__).'/forms.php';

class cm_application_db {

	public $max_prereg_discounts = array(
		'No Discount',
		'Price per Applicant',
		'Price per Assignment',
		'Total Price'
	);
	public $application_statuses = array(
		'Submitted',
		'Cancelled',
		'Accepted',
		'Waitlisted',
		'Rejected'
	);
	public $payment_statuses = array(
		'Incomplete',
		'Cancelled',
		'Rejected',
		'Completed',
		'Refunded'
	);
	public $names_on_badge = array(
		'Fandom Name Large, Real Name Small',
		'Real Name Large, Fandom Name Small',
		'Fandom Name Only',
		'Real Name Only'
	);

	public $event_info;
	public $cm_db;
	public $ctx_uc;
	public $ctx_lc;
	public $ctx_info;
	public $cm_anldb;
	public $cm_atldb;

	public function __construct($cm_db, $context) {
		$this->event_info = $GLOBALS['cm_config']['event'];
		$this->cm_db = $cm_db;
		$this->cm_db->table_def('rooms_and_tables', (
			'`id` VARCHAR(255) NOT NULL PRIMARY KEY,'.
			'`x1` DECIMAL(7,6) NOT NULL,'.
			'`y1` DECIMAL(7,6) NOT NULL,'.
			'`x2` DECIMAL(7,6) NOT NULL,'.
			'`y2` DECIMAL(7,6) NOT NULL'
		));
		$this->cm_db->table_def('room_and_table_assignments', (
			'`context` VARCHAR(255) NOT NULL,'.
			'`context_id` INTEGER NOT NULL,'.
			'`room_or_table_id` VARCHAR(255) NOT NULL,'.
			'`start_time` DATETIME NOT NULL,'.
			'`end_time` DATETIME NOT NULL'
		));
		if ($context) {
			$this->ctx_uc = strtoupper($context);
			$this->ctx_lc = strtolower($context);
			$this->ctx_info = $GLOBALS['cm_config']['application_types'][$this->ctx_uc];
			$this->cm_anldb = new cm_lists_db($this->cm_db, 'application_search_index_'.$this->ctx_lc);
			$this->cm_atldb = new cm_lists_db($this->cm_db, 'applicant_search_index_'.$this->ctx_lc);
			$this->cm_db->table_def('application_badge_types_'.$this->ctx_lc, (
				'`id` INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,'.
				'`order` INTEGER NOT NULL,'.
				'`name` VARCHAR(255) NOT NULL,'.
				'`description` TEXT NULL,'.
				'`rewards` TEXT NULL,'.
				'`max_applicant_count` INTEGER NULL,'.
				'`max_assignment_count` INTEGER NULL,'.
				'`base_price` DECIMAL(7,2) NOT NULL,'.
				'`base_applicant_count` INTEGER NOT NULL,'.
				'`base_assignment_count` INTEGER NOT NULL,'.
				'`price_per_applicant` DECIMAL(7,2) NOT NULL,'.
				'`price_per_assignment` DECIMAL(7,2) NOT NULL,'.
				'`max_prereg_discount` ENUM('.
					'\'No Discount\','.
					'\'Price per Applicant\','.
					'\'Price per Assignment\','.
					'\'Total Price\''.
				') NOT NULL,'.
				'`use_permit` BOOLEAN NOT NULL,'.
				'`require_permit` BOOLEAN NOT NULL,'.
				'`require_contract` BOOLEAN NOT NULL,'.
				'`active` BOOLEAN NOT NULL,'.
				'`quantity` INTEGER NULL,'.
				'`start_date` DATE NULL,'.
				'`end_date` DATE NULL,'.
				'`min_age` INTEGER NULL,'.
				'`max_age` INTEGER NULL'
			));
			$this->cm_db->table_def('application_blacklist_'.$this->ctx_lc, (
				'`id` INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,'.
				'`business_name` VARCHAR(255) NULL,'.
				'`application_name` VARCHAR(255) NULL,'.
				'`added_by` VARCHAR(255) NULL,'.
				'`normalized_business_name` VARCHAR(255) NULL,'.
				'`normalized_application_name` VARCHAR(255) NULL'
			));
			$this->cm_db->table_def('applicant_blacklist_'.$this->ctx_lc, (
				'`id` INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,'.
				'`first_name` VARCHAR(255) NULL,'.
				'`last_name` VARCHAR(255) NULL,'.
				'`fandom_name` VARCHAR(255) NULL,'.
				'`email_address` VARCHAR(255) NULL,'.
				'`phone_number` VARCHAR(255) NULL,'.
				'`added_by` VARCHAR(255) NULL,'.
				'`normalized_real_name` VARCHAR(255) NULL,'.
				'`normalized_reversed_name` VARCHAR(255) NULL,'.
				'`normalized_fandom_name` VARCHAR(255) NULL,'.
				'`normalized_email_address` VARCHAR(255) NULL,'.
				'`normalized_phone_number` VARCHAR(255) NULL'
			));
			$this->cm_db->table_def('applications_'.$this->ctx_lc, (
				/* Record Info */
				'`id` INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,'.
				'`uuid` VARCHAR(255) NOT NULL UNIQUE KEY,'.
				'`date_created` DATETIME NOT NULL,'.
				'`date_modified` DATETIME NOT NULL,'.
				'`badge_type_id` INTEGER NOT NULL,'.
				'`notes` TEXT NULL,'.
				/* Contact Info */
				'`contact_first_name` VARCHAR(255) NOT NULL,'.
				'`contact_last_name` VARCHAR(255) NOT NULL,'.
				'`contact_subscribed` BOOLEAN NOT NULL,'.
				'`contact_email_address` VARCHAR(255) NOT NULL,'.
				'`contact_phone_number` VARCHAR(255) NULL,'.
				'`contact_address_1` VARCHAR(255) NULL,'.
				'`contact_address_2` VARCHAR(255) NULL,'.
				'`contact_city` VARCHAR(255) NULL,'.
				'`contact_state` VARCHAR(255) NULL,'.
				'`contact_zip_code` VARCHAR(255) NULL,'.
				'`contact_country` VARCHAR(255) NULL,'.
				/* Application Info */
				'`business_name` VARCHAR(255) NOT NULL,'.
				'`application_name` VARCHAR(255) NOT NULL,'.
				'`applicant_count` INTEGER NOT NULL,'.
				'`assignment_count` INTEGER NOT NULL,'.
				'`application_status` ENUM('.
					'\'Submitted\','.
					'\'Cancelled\','.
					'\'Accepted\','.
					'\'Waitlisted\','.
					'\'Rejected\''.
				') NOT NULL,'.
				'`permit_number` VARCHAR(255) NULL,'.
				/* Payment Info */
				'`payment_status` ENUM('.
					'\'Incomplete\','.
					'\'Cancelled\','.
					'\'Rejected\','.
					'\'Completed\','.
					'\'Refunded\''.
				') NOT NULL,'.
				'`payment_badge_price` DECIMAL(7,2) NULL,'.
				'`payment_group_uuid` VARCHAR(255) NOT NULL,'.
				'`payment_type` VARCHAR(255) NULL,'.
				'`payment_txn_id` VARCHAR(255) NULL,'.
				'`payment_txn_amt` DECIMAL(7,2) NULL,'.
				'`payment_date` DATETIME NULL,'.
				'`payment_details` TEXT NULL'
			));
			$this->cm_db->table_def('applicants_'.$this->ctx_lc, (
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
				'`application_id` INTEGER NOT NULL,'.
				'`attendee_id` INTEGER NULL,'.
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
				'`ice_phone_number` VARCHAR(255) NULL'
			));
		} else {
			$this->ctx_uc = null;
			$this->ctx_lc = null;
			$this->ctx_info = null;
			$this->cm_anldb = null;
			$this->cm_atldb = null;
		}
	}

	public function get_room_or_table($id) {
		if (!$id) return false;
		$stmt = $this->cm_db->connection->prepare(
			'SELECT `id`, `x1`, `y1`, `x2`, `y2`'.
			' FROM '.$this->cm_db->table_name('rooms_and_tables').
			' WHERE `id` = ? LIMIT 1'
		);
		$stmt->bind_param('s', $id);
		$stmt->execute();
		$stmt->bind_result($id, $x1, $y1, $x2, $y2);
		if ($stmt->fetch()) {
			$result = array(
				'id' => $id,
				'x1' => $x1,
				'y1' => $y1,
				'x2' => $x2,
				'y2' => $y2
			);
			$stmt->close();
			return $result;
		}
		$stmt->close();
		return false;
	}

	public function list_rooms_and_tables() {
		$rooms_and_tables = array();
		$stmt = $this->cm_db->connection->prepare(
			'SELECT `id`, `x1`, `y1`, `x2`, `y2`'.
			' FROM '.$this->cm_db->table_name('rooms_and_tables')
		);
		$stmt->execute();
		$stmt->bind_result($id, $x1, $y1, $x2, $y2);
		while ($stmt->fetch()) {
			$rooms_and_tables[] = array(
				'id' => $id,
				'x1' => $x1,
				'y1' => $y1,
				'x2' => $x2,
				'y2' => $y2
			);
		}
		$stmt->close();
		usort($rooms_and_tables, function($a, $b) {
			return strnatcasecmp($a['id'], $b['id']);
		});
		return $rooms_and_tables;
	}

	public function create_room_or_table($rt) {
		if (!$rt || !isset($rt['id']) || !$rt['id']) return false;
		$stmt = $this->cm_db->connection->prepare(
			'INSERT INTO '.$this->cm_db->table_name('rooms_and_tables').' SET '.
			'`id` = ?, `x1` = ?, `y1` = ?, `x2` = ?, `y2` = ?'.
			' ON DUPLICATE KEY UPDATE '.
			'`id` = ?, `x1` = ?, `y1` = ?, `x2` = ?, `y2` = ?'
		);
		$stmt->bind_param(
			'sddddsdddd',
			$rt['id'], $rt['x1'], $rt['y1'], $rt['x2'], $rt['y2'],
			$rt['id'], $rt['x1'], $rt['y1'], $rt['x2'], $rt['y2']
		);
		$success = $stmt->execute();
		$stmt->close();
		return $success;
	}

	public function update_room_or_table($id, $rt) {
		if (!$id || !$rt || !isset($rt['id']) || !$rt['id']) return false;
		$stmt = $this->cm_db->connection->prepare(
			'UPDATE '.$this->cm_db->table_name('rooms_and_tables').' SET '.
			'`id` = ?, `x1` = ?, `y1` = ?, `x2` = ?, `y2` = ?'.
			' WHERE `id` = ? LIMIT 1'
		);
		$stmt->bind_param(
			'sdddds',
			$rt['id'], $rt['x1'], $rt['y1'], $rt['x2'], $rt['y2'],
			$id
		);
		$success = $stmt->execute();
		$stmt->close();
		return $success;
	}

	public function delete_room_or_table($id) {
		if (!$id) return false;
		$stmt = $this->cm_db->connection->prepare(
			'DELETE FROM '.$this->cm_db->table_name('rooms_and_tables').
			' WHERE `id` = ? LIMIT 1'
		);
		$stmt->bind_param('s', $id);
		$success = $stmt->execute();
		$stmt->close();
		return $success;
	}

	public function upload_rooms_and_tables($file) {
		if (!$file) return false;
		$in = fopen($file, 'r');
		if (!$in) return false;
		while (($row = fgetcsv($in))) {
			if (count($row) < 5) continue;
			$this->create_room_or_table(array(
				'x1' => (float)$row[1],
				'y1' => (float)$row[2],
				'x2' => (float)$row[3],
				'y2' => (float)$row[4],
				'id' => trim($row[0])
			));
		}
		fclose($in);
		return true;
	}

	public function download_rooms_and_tables() {
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename=rooms-and-tables.csv');
		header('Pragma: no-cache');
		header('Expires: 0');
		$out = fopen('php://output', 'w');
		$rooms_and_tables = $this->list_rooms_and_tables();
		foreach ($rooms_and_tables as $rt) {
			$row = array($rt['id'], $rt['x1'], $rt['y1'], $rt['x2'], $rt['y2']);
			fputcsv($out, $row);
		}
		fclose($out);
		exit(0);
	}

	public function delete_rooms_and_tables() {
		$stmt = $this->cm_db->connection->prepare(
			'DELETE FROM '.$this->cm_db->table_name('rooms_and_tables')
		);
		$success = $stmt->execute();
		$stmt->close();
		return $success;
	}

	public function get_badge_type($id) {
		if (!$id) return false;
		$stmt = $this->cm_db->connection->prepare(
			'SELECT b.`id`, b.`order`, b.`name`, b.`description`, b.`rewards`,'.
			' b.`max_applicant_count`, b.`max_assignment_count`, b.`base_price`,'.
			' b.`base_applicant_count`, b.`base_assignment_count`,'.
			' b.`price_per_applicant`, b.`price_per_assignment`,'.
			' b.`max_prereg_discount`, b.`use_permit`, b.`require_permit`,'.
			' b.`require_contract`, b.`active`, b.`quantity`,'.
			' b.`start_date`, b.`end_date`, b.`min_age`, b.`max_age`,'.
			' (SELECT COUNT(*) FROM '.$this->cm_db->table_name('applications_'.$this->ctx_lc).' a'.
			' WHERE a.`badge_type_id` = b.`id` AND a.`payment_status` = \'Completed\') c'.
			' FROM '.$this->cm_db->table_name('application_badge_types_'.$this->ctx_lc).' b'.
			' WHERE `id` = ? LIMIT 1'
		);
		$stmt->bind_param('i', $id);
		$stmt->execute();
		$stmt->bind_result(
			$id, $order, $name, $description, $rewards,
			$max_applicant_count, $max_assignment_count, $base_price,
			$base_applicant_count, $base_assignment_count,
			$price_per_applicant, $price_per_assignment,
			$max_prereg_discount, $use_permit, $require_permit,
			$require_contract, $active, $quantity,
			$start_date, $end_date, $min_age, $max_age,
			$quantity_sold
		);
		if ($stmt->fetch()) {
			$event_start_date = $this->event_info['start_date'];
			$event_end_date   = $this->event_info['end_date'  ];
			$min_birthdate = $max_age ? (((int)$event_start_date - $max_age - 1) . substr($event_start_date, 4)) : null;
			$max_birthdate = $min_age ? (((int)$event_end_date   - $min_age    ) . substr($event_end_date  , 4)) : null;
			$result = array(
				'id' => $id,
				'order' => $order,
				'name' => $name,
				'description' => $description,
				'rewards' => ($rewards ? explode("\n", $rewards) : array()),
				'max-applicant-count' => $max_applicant_count,
				'max-assignment-count' => $max_assignment_count,
				'base-price' => $base_price,
				'base-applicant-count' => $base_applicant_count,
				'base-assignment-count' => $base_assignment_count,
				'price-per-applicant' => $price_per_applicant,
				'price-per-assignment' => $price_per_assignment,
				'max-prereg-discount' => $max_prereg_discount,
				'use-permit' => !!$use_permit,
				'require-permit' => !!$require_permit,
				'require-contract' => !!$require_contract,
				'active' => !!$active,
				'quantity' => $quantity,
				'quantity-sold' => $quantity_sold,
				'quantity-remaining' => (is_null($quantity) ? null : ($quantity - $quantity_sold)),
				'start-date' => $start_date,
				'end-date' => $end_date,
				'min-age' => $min_age,
				'max-age' => $max_age,
				'min-birthdate' => $min_birthdate,
				'max-birthdate' => $max_birthdate,
				'search-content' => array($name, $description, $rewards)
			);
			$stmt->close();
			return $result;
		}
		$stmt->close();
		return false;
	}

	public function get_badge_type_name_map() {
		$badge_types = array();
		$stmt = $this->cm_db->connection->prepare(
			'SELECT `id`, `name`'.
			' FROM '.$this->cm_db->table_name('application_badge_types_'.$this->ctx_lc).
			' ORDER BY `order`'
		);
		$stmt->execute();
		$stmt->bind_result($id, $name);
		while ($stmt->fetch()) {
			$badge_types[$id] = $name;
		}
		$stmt->close();
		return $badge_types;
	}

	public function list_badge_type_names() {
		$badge_types = array();
		$stmt = $this->cm_db->connection->prepare(
			'SELECT `id`, `name`'.
			' FROM '.$this->cm_db->table_name('application_badge_types_'.$this->ctx_lc).
			' ORDER BY `order`'
		);
		$stmt->execute();
		$stmt->bind_result($id, $name);
		while ($stmt->fetch()) {
			$badge_types[] = array(
				'id' => $id,
				'name' => $name
			);
		}
		$stmt->close();
		return $badge_types;
	}

	public function list_badge_types($active_only = false, $unsold_only = false) {
		$badge_types = array();
		$query = (
			'SELECT b.`id`, b.`order`, b.`name`, b.`description`, b.`rewards`,'.
			' b.`max_applicant_count`, b.`max_assignment_count`, b.`base_price`,'.
			' b.`base_applicant_count`, b.`base_assignment_count`,'.
			' b.`price_per_applicant`, b.`price_per_assignment`,'.
			' b.`max_prereg_discount`, b.`use_permit`, b.`require_permit`,'.
			' b.`require_contract`, b.`active`, b.`quantity`,'.
			' b.`start_date`, b.`end_date`, b.`min_age`, b.`max_age`,'.
			' (SELECT COUNT(*) FROM '.$this->cm_db->table_name('applications_'.$this->ctx_lc).' a'.
			' WHERE a.`badge_type_id` = b.`id` AND a.`payment_status` = \'Completed\') c'.
			' FROM '.$this->cm_db->table_name('application_badge_types_'.$this->ctx_lc).' b'
		);
		$first = true;
		if ($active_only) {
			$query .= (
				($first ? ' WHERE' : ' AND').' b.`active`'.
				' AND (b.`start_date` IS NULL OR b.`start_date` <= CURDATE())'.
				' AND (b.`end_date` IS NULL OR b.`end_date` >= CURDATE())'
			);
			$first = false;
		}
		$stmt = $this->cm_db->connection->prepare($query . ' ORDER BY b.`order`');
		$stmt->execute();
		$stmt->bind_result(
			$id, $order, $name, $description, $rewards,
			$max_applicant_count, $max_assignment_count, $base_price,
			$base_applicant_count, $base_assignment_count,
			$price_per_applicant, $price_per_assignment,
			$max_prereg_discount, $use_permit, $require_permit,
			$require_contract, $active, $quantity,
			$start_date, $end_date, $min_age, $max_age,
			$quantity_sold
		);
		$event_start_date = $this->event_info['start_date'];
		$event_end_date   = $this->event_info['end_date'  ];
		while ($stmt->fetch()) {
			if ($unsold_only && !(is_null($quantity) || $quantity > $quantity_sold)) continue;
			$min_birthdate = $max_age ? (((int)$event_start_date - $max_age - 1) . substr($event_start_date, 4)) : null;
			$max_birthdate = $min_age ? (((int)$event_end_date   - $min_age    ) . substr($event_end_date  , 4)) : null;
			$badge_types[] = array(
				'id' => $id,
				'order' => $order,
				'name' => $name,
				'description' => $description,
				'rewards' => ($rewards ? explode("\n", $rewards) : array()),
				'max-applicant-count' => $max_applicant_count,
				'max-assignment-count' => $max_assignment_count,
				'base-price' => $base_price,
				'base-applicant-count' => $base_applicant_count,
				'base-assignment-count' => $base_assignment_count,
				'price-per-applicant' => $price_per_applicant,
				'price-per-assignment' => $price_per_assignment,
				'max-prereg-discount' => $max_prereg_discount,
				'use-permit' => !!$use_permit,
				'require-permit' => !!$require_permit,
				'require-contract' => !!$require_contract,
				'active' => !!$active,
				'quantity' => $quantity,
				'quantity-sold' => $quantity_sold,
				'quantity-remaining' => (is_null($quantity) ? null : ($quantity - $quantity_sold)),
				'start-date' => $start_date,
				'end-date' => $end_date,
				'min-age' => $min_age,
				'max-age' => $max_age,
				'min-birthdate' => $min_birthdate,
				'max-birthdate' => $max_birthdate,
				'search-content' => array($name, $description, $rewards)
			);
		}
		$stmt->close();
		return $badge_types;
	}

	public function create_badge_type($badge_type) {
		if (!$badge_type) return false;
		$this->cm_db->connection->autocommit(false);
		$stmt = $this->cm_db->connection->prepare(
			'SELECT IFNULL(MAX(`order`),0)+1 FROM '.
			$this->cm_db->table_name('application_badge_types_'.$this->ctx_lc)
		);
		$stmt->execute();
		$stmt->bind_result($order);
		$stmt->fetch();
		$stmt->close();
		$name = (isset($badge_type['name']) ? $badge_type['name'] : '');
		$description = (isset($badge_type['description']) ? $badge_type['description'] : '');
		$rewards = (isset($badge_type['rewards']) ? implode("\n", $badge_type['rewards']) : '');
		$max_applicant_count = (isset($badge_type['max-applicant-count']) ? $badge_type['max-applicant-count'] : null);
		$max_assignment_count = (isset($badge_type['max-assignment-count']) ? $badge_type['max-assignment-count'] : null);
		$base_price = (isset($badge_type['base-price']) ? (float)$badge_type['base-price'] : 0);
		$base_applicant_count = (isset($badge_type['base-applicant-count']) ? $badge_type['base-applicant-count'] : 0);
		$base_assignment_count = (isset($badge_type['base-assignment-count']) ? $badge_type['base-assignment-count'] : 0);
		$price_per_applicant = (isset($badge_type['price-per-applicant']) ? (float)$badge_type['price-per-applicant'] : 0);
		$price_per_assignment = (isset($badge_type['price-per-assignment']) ? (float)$badge_type['price-per-assignment'] : 0);
		$max_prereg_discount = (isset($badge_type['max-prereg-discount']) ? $badge_type['max-prereg-discount'] : 'No Discount');
		$use_permit = (isset($badge_type['use-permit']) ? ($badge_type['use-permit'] ? 1 : 0) : 0);
		$require_permit = (isset($badge_type['require-permit']) ? ($badge_type['require-permit'] ? 1 : 0) : 0);
		$require_contract = (isset($badge_type['require-contract']) ? ($badge_type['require-contract'] ? 1 : 0) : 0);
		$active = (isset($badge_type['active']) ? ($badge_type['active'] ? 1 : 0) : 1);
		$quantity = (isset($badge_type['quantity']) ? $badge_type['quantity'] : null);
		$start_date = (isset($badge_type['start-date']) ? $badge_type['start-date'] : null);
		$end_date = (isset($badge_type['end-date']) ? $badge_type['end-date'] : null);
		$min_age = (isset($badge_type['min-age']) ? $badge_type['min-age'] : null);
		$max_age = (isset($badge_type['max-age']) ? $badge_type['max-age'] : null);
		$stmt = $this->cm_db->connection->prepare(
			'INSERT INTO '.$this->cm_db->table_name('application_badge_types_'.$this->ctx_lc).' SET '.
			'`order` = ?, `name` = ?, `description` = ?, `rewards` = ?, '.
			'`max_applicant_count` = ?, `max_assignment_count` = ?, `base_price` = ?, '.
			'`base_applicant_count` = ?, `base_assignment_count` = ?, '.
			'`price_per_applicant` = ?, `price_per_assignment` = ?, '.
			'`max_prereg_discount` = ?, `use_permit` = ?, `require_permit` = ?, '.
			'`require_contract` = ?, `active` = ?, `quantity` = ?, '.
			'`start_date` = ?, `end_date` = ?, `min_age` = ?, `max_age` = ?'
		);
		$stmt->bind_param(
			'isssiidiiddsiiiiissii',
			$order, $name, $description, $rewards,
			$max_applicant_count, $max_assignment_count, $base_price,
			$base_applicant_count, $base_assignment_count,
			$price_per_applicant, $price_per_assignment,
			$max_prereg_discount, $use_permit, $require_permit,
			$require_contract, $active, $quantity,
			$start_date, $end_date, $min_age, $max_age
		);
		$id = $stmt->execute() ? $this->cm_db->connection->insert_id : false;
		$stmt->close();
		$this->cm_db->connection->autocommit(true);
		return $id;
	}

	public function update_badge_type($badge_type) {
		if (!$badge_type || !isset($badge_type['id']) || !$badge_type['id']) return false;
		$name = (isset($badge_type['name']) ? $badge_type['name'] : '');
		$description = (isset($badge_type['description']) ? $badge_type['description'] : '');
		$rewards = (isset($badge_type['rewards']) ? implode("\n", $badge_type['rewards']) : '');
		$max_applicant_count = (isset($badge_type['max-applicant-count']) ? $badge_type['max-applicant-count'] : null);
		$max_assignment_count = (isset($badge_type['max-assignment-count']) ? $badge_type['max-assignment-count'] : null);
		$base_price = (isset($badge_type['base-price']) ? (float)$badge_type['base-price'] : 0);
		$base_applicant_count = (isset($badge_type['base-applicant-count']) ? $badge_type['base-applicant-count'] : 0);
		$base_assignment_count = (isset($badge_type['base-assignment-count']) ? $badge_type['base-assignment-count'] : 0);
		$price_per_applicant = (isset($badge_type['price-per-applicant']) ? (float)$badge_type['price-per-applicant'] : 0);
		$price_per_assignment = (isset($badge_type['price-per-assignment']) ? (float)$badge_type['price-per-assignment'] : 0);
		$max_prereg_discount = (isset($badge_type['max-prereg-discount']) ? $badge_type['max-prereg-discount'] : 'No Discount');
		$use_permit = (isset($badge_type['use-permit']) ? ($badge_type['use-permit'] ? 1 : 0) : 0);
		$require_permit = (isset($badge_type['require-permit']) ? ($badge_type['require-permit'] ? 1 : 0) : 0);
		$require_contract = (isset($badge_type['require-contract']) ? ($badge_type['require-contract'] ? 1 : 0) : 0);
		$active = (isset($badge_type['active']) ? ($badge_type['active'] ? 1 : 0) : 1);
		$quantity = (isset($badge_type['quantity']) ? $badge_type['quantity'] : null);
		$start_date = (isset($badge_type['start-date']) ? $badge_type['start-date'] : null);
		$end_date = (isset($badge_type['end-date']) ? $badge_type['end-date'] : null);
		$min_age = (isset($badge_type['min-age']) ? $badge_type['min-age'] : null);
		$max_age = (isset($badge_type['max-age']) ? $badge_type['max-age'] : null);
		$stmt = $this->cm_db->connection->prepare(
			'UPDATE '.$this->cm_db->table_name('application_badge_types_'.$this->ctx_lc).' SET '.
			'`name` = ?, `description` = ?, `rewards` = ?, '.
			'`max_applicant_count` = ?, `max_assignment_count` = ?, `base_price` = ?, '.
			'`base_applicant_count` = ?, `base_assignment_count` = ?, '.
			'`price_per_applicant` = ?, `price_per_assignment` = ?, '.
			'`max_prereg_discount` = ?, `use_permit` = ?, `require_permit` = ?, '.
			'`require_contract` = ?, `active` = ?, `quantity` = ?, '.
			'`start_date` = ?, `end_date` = ?, `min_age` = ?, `max_age` = ?'.
			' WHERE `id` = ? LIMIT 1'
		);
		$stmt->bind_param(
			'sssiidiiddsiiiiissiii',
			$name, $description, $rewards,
			$max_applicant_count, $max_assignment_count, $base_price,
			$base_applicant_count, $base_assignment_count,
			$price_per_applicant, $price_per_assignment,
			$max_prereg_discount, $use_permit, $require_permit,
			$require_contract, $active, $quantity,
			$start_date, $end_date, $min_age, $max_age,
			$badge_type['id']
		);
		$success = $stmt->execute();
		$stmt->close();
		return $success;
	}

	public function delete_badge_type($id) {
		if (!$id) return false;
		$stmt = $this->cm_db->connection->prepare(
			'DELETE FROM '.$this->cm_db->table_name('application_badge_types_'.$this->ctx_lc).
			' WHERE `id` = ? LIMIT 1'
		);
		$stmt->bind_param('i', $id);
		$success = $stmt->execute();
		$stmt->close();
		return $success;
	}

	public function activate_badge_type($id, $active) {
		if (!$id) return false;
		$active = $active ? 1 : 0;
		$stmt = $this->cm_db->connection->prepare(
			'UPDATE '.$this->cm_db->table_name('application_badge_types_'.$this->ctx_lc).
			' SET `active` = ? WHERE `id` = ? LIMIT 1'
		);
		$stmt->bind_param('ii', $active, $id);
		$success = $stmt->execute();
		$stmt->close();
		return $success;
	}

	public function reorder_badge_type($id, $direction) {
		if (!$id || !$direction) return false;
		$this->cm_db->connection->autocommit(false);
		$ids = array();
		$index = -1;
		$stmt = $this->cm_db->connection->prepare(
			'SELECT `id` FROM '.
			$this->cm_db->table_name('application_badge_types_'.$this->ctx_lc).
			' ORDER BY `order`'
		);
		$stmt->execute();
		$stmt->bind_result($cid);
		while ($stmt->fetch()) {
			$cindex = count($ids);
			$ids[] = $cid;
			if ($id == $cid) $index = $cindex;
		}
		$stmt->close();
		if ($index >= 0) {
			while ($direction < 0 && $index > 0) {
				$ids[$index] = $ids[$index - 1];
				$ids[$index - 1] = $id;
				$direction++;
				$index--;
			}
			while ($direction > 0 && $index < (count($ids) - 1)) {
				$ids[$index] = $ids[$index + 1];
				$ids[$index + 1] = $id;
				$direction--;
				$index++;
			}
			foreach ($ids as $cindex => $cid) {
				$stmt = $this->cm_db->connection->prepare(
					'UPDATE '.$this->cm_db->table_name('application_badge_types_'.$this->ctx_lc).
					' SET `order` = ? WHERE `id` = ? LIMIT 1'
				);
				$ni = $cindex + 1;
				$stmt->bind_param('ii', $ni, $cid);
				$stmt->execute();
				$stmt->close();
			}
		}
		$this->cm_db->connection->autocommit(true);
		return ($index >= 0);
	}

}
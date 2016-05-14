<?php

require_once dirname(__FILE__).'/../../config/config.php';
require_once dirname(__FILE__).'/database.php';

class cm_attendee_db {

	public $event_info;
	public $cm_db;

	public function __construct($cm_db) {
		$this->event_info = $GLOBALS['cm_config']['event'];
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
			'`limit_per_customer` INTEGER NULL,'.
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
			'`added_by` VARCHAR(255) NULL,'.
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
			'`payment_group_uuid` VARCHAR(255) NOT NULL,'.
			'`payment_type` VARCHAR(255) NULL,'.
			'`payment_txn_id` VARCHAR(255) NULL,'.
			'`payment_txn_amt` DECIMAL(7,2) NULL,'.
			'`payment_date` DATETIME NULL,'.
			'`payment_details` TEXT NULL'
		));
	}

	public function get_badge_type($id) {
		if (!$id) return false;
		$stmt = $this->cm_db->connection->prepare(
			'SELECT b.`id`, b.`order`, b.`name`, b.`description`, b.`rewards`,'.
			' b.`price`, b.`payable_onsite`, b.`active`, b.`quantity`,'.
			' b.`start_date`, b.`end_date`, b.`min_age`, b.`max_age`,'.
			' (SELECT COUNT(*) FROM '.$this->cm_db->table_name('attendees').' a'.
			' WHERE a.`badge_type_id` = b.`id` AND a.`payment_status` = \'Completed\') c'.
			' FROM '.$this->cm_db->table_name('attendee_badge_types').' b'.
			' WHERE `id` = ? LIMIT 1'
		);
		$stmt->bind_param('i', $id);
		$stmt->execute();
		$stmt->bind_result(
			$id, $order, $name, $description, $rewards,
			$price, $payable_onsite, $active, $quantity,
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
				'price' => $price,
				'payable-onsite' => !!$payable_onsite,
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
			' FROM '.$this->cm_db->table_name('attendee_badge_types').
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
			' FROM '.$this->cm_db->table_name('attendee_badge_types').
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

	public function list_badge_types($active_only = false, $unsold_only = false, $onsite_only = false) {
		$badge_types = array();
		$query = (
			'SELECT b.`id`, b.`order`, b.`name`, b.`description`, b.`rewards`,'.
			' b.`price`, b.`payable_onsite`, b.`active`, b.`quantity`,'.
			' b.`start_date`, b.`end_date`, b.`min_age`, b.`max_age`,'.
			' (SELECT COUNT(*) FROM '.$this->cm_db->table_name('attendees').' a'.
			' WHERE a.`badge_type_id` = b.`id` AND a.`payment_status` = \'Completed\') c'.
			' FROM '.$this->cm_db->table_name('attendee_badge_types').' b'
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
		if ($onsite_only) {
			$query .= ($first ? ' WHERE' : ' AND').' b.`payable_onsite`';
			$first = false;
		}
		$stmt = $this->cm_db->connection->prepare($query . ' ORDER BY b.`order`');
		$stmt->execute();
		$stmt->bind_result(
			$id, $order, $name, $description, $rewards,
			$price, $payable_onsite, $active, $quantity,
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
				'price' => $price,
				'payable-onsite' => !!$payable_onsite,
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
			$this->cm_db->table_name('attendee_badge_types')
		);
		$stmt->execute();
		$stmt->bind_result($order);
		$stmt->fetch();
		$stmt->close();
		$name = (isset($badge_type['name']) ? $badge_type['name'] : '');
		$description = (isset($badge_type['description']) ? $badge_type['description'] : '');
		$rewards = (isset($badge_type['rewards']) ? implode("\n", $badge_type['rewards']) : '');
		$price = (isset($badge_type['price']) ? (float)$badge_type['price'] : 0);
		$payable_onsite = (isset($badge_type['payable-onsite']) ? ($badge_type['payable-onsite'] ? 1 : 0) : 0);
		$active = (isset($badge_type['active']) ? ($badge_type['active'] ? 1 : 0) : 1);
		$quantity = (isset($badge_type['quantity']) ? $badge_type['quantity'] : null);
		$start_date = (isset($badge_type['start-date']) ? $badge_type['start-date'] : null);
		$end_date = (isset($badge_type['end-date']) ? $badge_type['end-date'] : null);
		$min_age = (isset($badge_type['min-age']) ? $badge_type['min-age'] : null);
		$max_age = (isset($badge_type['max-age']) ? $badge_type['max-age'] : null);
		$stmt = $this->cm_db->connection->prepare(
			'INSERT INTO '.$this->cm_db->table_name('attendee_badge_types').' SET '.
			'`order` = ?, `name` = ?, `description` = ?, `rewards` = ?, '.
			'`price` = ?, `payable_onsite` = ?, `active` = ?, `quantity` = ?, '.
			'`start_date` = ?, `end_date` = ?, `min_age` = ?, `max_age` = ?'
		);
		$stmt->bind_param(
			'isssdiiissii',
			$order, $name, $description, $rewards,
			$price, $payable_onsite, $active, $quantity,
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
		$price = (isset($badge_type['price']) ? (float)$badge_type['price'] : 0);
		$payable_onsite = (isset($badge_type['payable-onsite']) ? ($badge_type['payable-onsite'] ? 1 : 0) : 0);
		$active = (isset($badge_type['active']) ? ($badge_type['active'] ? 1 : 0) : 1);
		$quantity = (isset($badge_type['quantity']) ? $badge_type['quantity'] : null);
		$start_date = (isset($badge_type['start-date']) ? $badge_type['start-date'] : null);
		$end_date = (isset($badge_type['end-date']) ? $badge_type['end-date'] : null);
		$min_age = (isset($badge_type['min-age']) ? $badge_type['min-age'] : null);
		$max_age = (isset($badge_type['max-age']) ? $badge_type['max-age'] : null);
		$stmt = $this->cm_db->connection->prepare(
			'UPDATE '.$this->cm_db->table_name('attendee_badge_types').' SET '.
			'`name` = ?, `description` = ?, `rewards` = ?, '.
			'`price` = ?, `payable_onsite` = ?, `active` = ?, `quantity` = ?, '.
			'`start_date` = ?, `end_date` = ?, `min_age` = ?, `max_age` = ?'.
			' WHERE `id` = ? LIMIT 1'
		);
		$stmt->bind_param(
			'sssdiiissiii',
			$name, $description, $rewards,
			$price, $payable_onsite, $active, $quantity,
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
			'DELETE FROM '.$this->cm_db->table_name('attendee_badge_types').
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
			'UPDATE '.$this->cm_db->table_name('attendee_badge_types').
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
			$this->cm_db->table_name('attendee_badge_types').
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
					'UPDATE '.$this->cm_db->table_name('attendee_badge_types').
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

	public function promo_code_normalize($code) {
		return strtoupper(preg_replace('/[^A-Za-z0-9!@#$%&*?]/', '', $code));
	}

	public function promo_code_price_html($promo_code) {
		if (!isset($promo_code['price']) || !$promo_code['price']) return 'NONE';
		$price = htmlspecialchars(number_format($promo_code['price'], 2, '.', ','));
		$percentage = isset($promo_code['percentage']) && $promo_code['percentage'];
		return $percentage ? ($price . '<b>%</b>') : ('<b>$</b>' . $price);
	}

	public function promo_code_applies($promo_code, $badge_type_id) {
		return ($promo_code && $promo_code['badge-type-ids'] && (
			in_array('*', $promo_code['badge-type-ids']) ||
			in_array($badge_type_id, $promo_code['badge-type-ids'])
		));
	}

	public function get_promo_code($id, $is_code = false, $name_map = null) {
		if (!$id) return false;
		if ($is_code) $id = $this->promo_code_normalize($id);
		if (!$name_map) $name_map = $this->get_badge_type_name_map();
		$stmt = $this->cm_db->connection->prepare(
			'SELECT p.`id`, p.`code`, p.`description`, p.`price`,'.
			' p.`percentage`, p.`active`, p.`badge_type_ids`,'.
			' p.`limit_per_customer`, p.`start_date`, p.`end_date`,'.
			' (SELECT COUNT(*) FROM '.$this->cm_db->table_name('attendees').' a'.
			' WHERE a.`payment_promo_code` = p.`code`'.
			' AND a.`payment_status` = \'Completed\') c'.
			' FROM '.$this->cm_db->table_name('attendee_promo_codes').' p'.
			' WHERE p.`'.($is_code ? 'code' : 'id').'` = ? LIMIT 1'
		);
		$stmt->bind_param(($is_code ? 's' : 'i'), $id);
		$stmt->execute();
		$stmt->bind_result(
			$id, $code, $description, $price, $percentage,
			$active, $badge_type_ids, $limit_per_customer,
			$start_date, $end_date, $quantity_used
		);
		if ($stmt->fetch()) {
			$result = array(
				'id' => $id,
				'code' => $code,
				'description' => $description,
				'price' => $price,
				'percentage' => !!$percentage,
				'price-html' => '?',
				'active' => !!$active,
				'badge-type-ids' => ($badge_type_ids ? explode(',', $badge_type_ids) : array()),
				'badge-type-names' => array(),
				'limit-per-customer' => $limit_per_customer,
				'start-date' => $start_date,
				'end-date' => $end_date,
				'quantity-used' => $quantity_used,
				'search-content' => array($code, $description)
			);
			$result['price-html'] = $this->promo_code_price_html($result);
			foreach ($result['badge-type-ids'] as $btid) {
				$result['badge-type-names'][] = isset($name_map[$btid]) ? $name_map[$btid] : $btid;
			}
			$stmt->close();
			return $result;
		}
		$stmt->close();
		return false;
	}

	public function list_promo_codes($name_map = null) {
		if (!$name_map) $name_map = $this->get_badge_type_name_map();
		$promo_codes = array();
		$stmt = $this->cm_db->connection->prepare(
			'SELECT p.`id`, p.`code`, p.`description`, p.`price`,'.
			' p.`percentage`, p.`active`, p.`badge_type_ids`,'.
			' p.`limit_per_customer`, p.`start_date`, p.`end_date`,'.
			' (SELECT COUNT(*) FROM '.$this->cm_db->table_name('attendees').' a'.
			' WHERE a.`payment_promo_code` = p.`code`'.
			' AND a.`payment_status` = \'Completed\') c'.
			' FROM '.$this->cm_db->table_name('attendee_promo_codes').' p'.
			' ORDER BY p.`code`'
		);
		$stmt->execute();
		$stmt->bind_result(
			$id, $code, $description, $price, $percentage,
			$active, $badge_type_ids, $limit_per_customer,
			$start_date, $end_date, $quantity_used
		);
		while ($stmt->fetch()) {
			$result = array(
				'id' => $id,
				'code' => $code,
				'description' => $description,
				'price' => $price,
				'percentage' => !!$percentage,
				'price-html' => '?',
				'active' => !!$active,
				'badge-type-ids' => ($badge_type_ids ? explode(',', $badge_type_ids) : array()),
				'badge-type-names' => array(),
				'limit-per-customer' => $limit_per_customer,
				'start-date' => $start_date,
				'end-date' => $end_date,
				'quantity-used' => $quantity_used,
				'search-content' => array($code, $description)
			);
			$result['price-html'] = $this->promo_code_price_html($result);
			foreach ($result['badge-type-ids'] as $btid) {
				$result['badge-type-names'][] = isset($name_map[$btid]) ? $name_map[$btid] : $btid;
			}
			$promo_codes[] = $result;
		}
		$stmt->close();
		return $promo_codes;
	}

	public function create_promo_code($promo_code) {
		if (!$promo_code || !isset($promo_code['code']) || !$promo_code['code']) return false;
		$code = $this->promo_code_normalize($promo_code['code']);
		$description = (isset($promo_code['description']) ? $promo_code['description'] : '');
		$price = (isset($promo_code['price']) ? (float)$promo_code['price'] : 0);
		$percentage = (isset($promo_code['percentage']) ? ($promo_code['percentage'] ? 1 : 0) : 0);
		$active = (isset($promo_code['active']) ? ($promo_code['active'] ? 1 : 0) : 1);
		$badge_type_ids = (isset($promo_code['badge-type-ids']) ? implode(',', $promo_code['badge-type-ids']) : '*');
		$limit_per_customer = (isset($promo_code['limit-per-customer']) ? $promo_code['limit-per-customer'] : null);
		$start_date = (isset($promo_code['start-date']) ? $promo_code['start-date'] : null);
		$end_date = (isset($promo_code['end-date']) ? $promo_code['end-date'] : null);
		$stmt = $this->cm_db->connection->prepare(
			'INSERT INTO '.$this->cm_db->table_name('attendee_promo_codes').' SET '.
			'`code` = ?, `description` = ?, `price` = ?, '.
			'`percentage` = ?, `active` = ?, `badge_type_ids` = ?, '.
			'`limit_per_customer` = ?, `start_date` = ?, `end_date` = ?'
		);
		$stmt->bind_param(
			'ssdiisiss',
			$code, $description, $price, $percentage, $active,
			$badge_type_ids, $limit_per_customer, $start_date, $end_date
		);
		$id = $stmt->execute() ? $this->cm_db->connection->insert_id : false;
		$stmt->close();
		return $id;
	}

	public function update_promo_code($promo_code) {
		if (!$promo_code || !isset($promo_code['id']) || !$promo_code['id'] ||
		    !isset($promo_code['code']) || !$promo_code['code']) return false;
		$code = $this->promo_code_normalize($promo_code['code']);
		$description = (isset($promo_code['description']) ? $promo_code['description'] : '');
		$price = (isset($promo_code['price']) ? (float)$promo_code['price'] : 0);
		$percentage = (isset($promo_code['percentage']) ? ($promo_code['percentage'] ? 1 : 0) : 0);
		$active = (isset($promo_code['active']) ? ($promo_code['active'] ? 1 : 0) : 1);
		$badge_type_ids = (isset($promo_code['badge-type-ids']) ? implode(',', $promo_code['badge-type-ids']) : '*');
		$limit_per_customer = (isset($promo_code['limit-per-customer']) ? $promo_code['limit-per-customer'] : null);
		$start_date = (isset($promo_code['start-date']) ? $promo_code['start-date'] : null);
		$end_date = (isset($promo_code['end-date']) ? $promo_code['end-date'] : null);
		$stmt = $this->cm_db->connection->prepare(
			'UPDATE '.$this->cm_db->table_name('attendee_promo_codes').' SET '.
			'`code` = ?, `description` = ?, `price` = ?, '.
			'`percentage` = ?, `active` = ?, `badge_type_ids` = ?, '.
			'`limit_per_customer` = ?, `start_date` = ?, `end_date` = ?'.
			' WHERE `id` = ? LIMIT 1'
		);
		$stmt->bind_param(
			'ssdiisissi',
			$code, $description, $price, $percentage, $active,
			$badge_type_ids, $limit_per_customer, $start_date, $end_date,
			$promo_code['id']
		);
		$success = $stmt->execute();
		$stmt->close();
		return $success;
	}

	public function delete_promo_code($id) {
		if (!$id) return false;
		$stmt = $this->cm_db->connection->prepare(
			'DELETE FROM '.$this->cm_db->table_name('attendee_promo_codes').
			' WHERE `id` = ? LIMIT 1'
		);
		$stmt->bind_param('i', $id);
		$success = $stmt->execute();
		$stmt->close();
		return $success;
	}

	public function activate_promo_code($id, $active) {
		if (!$id) return false;
		$active = $active ? 1 : 0;
		$stmt = $this->cm_db->connection->prepare(
			'UPDATE '.$this->cm_db->table_name('attendee_promo_codes').
			' SET `active` = ? WHERE `id` = ? LIMIT 1'
		);
		$stmt->bind_param('ii', $active, $id);
		$success = $stmt->execute();
		$stmt->close();
		return $success;
	}

	public function get_blacklist_entry($id) {
		if (!$id) return false;
		$stmt = $this->cm_db->connection->prepare(
			'SELECT `id`, `first_name`, `last_name`, `fandom_name`,'.
			' `email_address`, `phone_number`, `added_by`,'.
			' `normalized_real_name`,'.
			' `normalized_reversed_name`,'.
			' `normalized_fandom_name`,'.
			' `normalized_email_address`,'.
			' `normalized_phone_number`'.
			' FROM '.$this->cm_db->table_name('attendee_blacklist').
			' WHERE `id` = ? LIMIT 1'
		);
		$stmt->bind_param('i', $id);
		$stmt->execute();
		$stmt->bind_result(
			$id, $first_name, $last_name, $fandom_name,
			$email_address, $phone_number, $added_by,
			$normalized_real_name,
			$normalized_reversed_name,
			$normalized_fandom_name,
			$normalized_email_address,
			$normalized_phone_number
		);
		if ($stmt->fetch()) {
			$real_name = trim(trim($first_name) . ' ' . trim($last_name));
			$reversed_name = trim(trim($last_name) . ' ' . trim($first_name));
			$result = array(
				'id' => $id,
				'first-name' => $first_name,
				'last-name' => $last_name,
				'real-name' => $real_name,
				'reversed-name' => $reversed_name,
				'fandom-name' => $fandom_name,
				'email-address' => $email_address,
				'phone-number' => $phone_number,
				'added-by' => $added_by,
				'normalized-real-name' => $normalized_real_name,
				'normalized-reversed-name' => $normalized_reversed_name,
				'normalized-fandom-name' => $normalized_fandom_name,
				'normalized-email-address' => $normalized_email_address,
				'normalized-phone-number' => $normalized_phone_number,
				'search-content' => array(
					$first_name, $last_name, $real_name, $reversed_name,
					$fandom_name, $email_address, $phone_number, $added_by
				)
			);
			$stmt->close();
			return $result;
		}
		$stmt->close();
		return false;
	}

	public function list_blacklist_entries() {
		$blacklist = array();
		$stmt = $this->cm_db->connection->prepare(
			'SELECT `id`, `first_name`, `last_name`, `fandom_name`,'.
			' `email_address`, `phone_number`, `added_by`,'.
			' `normalized_real_name`,'.
			' `normalized_reversed_name`,'.
			' `normalized_fandom_name`,'.
			' `normalized_email_address`,'.
			' `normalized_phone_number`'.
			' FROM '.$this->cm_db->table_name('attendee_blacklist').
			' ORDER BY `first_name`, `last_name`'
		);
		$stmt->execute();
		$stmt->bind_result(
			$id, $first_name, $last_name, $fandom_name,
			$email_address, $phone_number, $added_by,
			$normalized_real_name,
			$normalized_reversed_name,
			$normalized_fandom_name,
			$normalized_email_address,
			$normalized_phone_number
		);
		while ($stmt->fetch()) {
			$real_name = trim(trim($first_name) . ' ' . trim($last_name));
			$reversed_name = trim(trim($last_name) . ' ' . trim($first_name));
			$blacklist[] = array(
				'id' => $id,
				'first-name' => $first_name,
				'last-name' => $last_name,
				'real-name' => $real_name,
				'reversed-name' => $reversed_name,
				'fandom-name' => $fandom_name,
				'email-address' => $email_address,
				'phone-number' => $phone_number,
				'added-by' => $added_by,
				'normalized-real-name' => $normalized_real_name,
				'normalized-reversed-name' => $normalized_reversed_name,
				'normalized-fandom-name' => $normalized_fandom_name,
				'normalized-email-address' => $normalized_email_address,
				'normalized-phone-number' => $normalized_phone_number,
				'search-content' => array(
					$first_name, $last_name, $real_name, $reversed_name,
					$fandom_name, $email_address, $phone_number, $added_by
				)
			);
		}
		$stmt->close();
		return $blacklist;
	}

	public function create_blacklist_entry($entry) {
		if (!$entry) return false;
		$first_name = (isset($entry['first-name']) ? $entry['first-name'] : '');
		$last_name = (isset($entry['last-name']) ? $entry['last-name'] : '');
		$fandom_name = (isset($entry['fandom-name']) ? $entry['fandom-name'] : '');
		$email_address = (isset($entry['email-address']) ? $entry['email-address'] : '');
		$phone_number = (isset($entry['phone-number']) ? $entry['phone-number'] : '');
		$added_by = (isset($entry['added-by']) ? $entry['added-by'] : '');
		$normalized_real_name = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '', $first_name . $last_name));
		$normalized_reversed_name = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '', $last_name . $first_name));
		$normalized_fandom_name = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '', $fandom_name));
		$normalized_email_address = strtoupper(preg_replace('/\\+.*@|[^A-Za-z0-9]+/', '', $email_address));
		$normalized_phone_number = preg_replace('/[^0-9]+/', '', $phone_number);
		if (!$first_name) $first_name = null;
		if (!$last_name) $last_name = null;
		if (!$fandom_name) $fandom_name = null;
		if (!$email_address) $email_address = null;
		if (!$phone_number) $phone_number = null;
		if (!$added_by) $added_by = null;
		if (!$normalized_real_name) $normalized_real_name = null;
		if (!$normalized_reversed_name) $normalized_reversed_name = null;
		if (!$normalized_fandom_name) $normalized_fandom_name = null;
		if (!$normalized_email_address) $normalized_email_address = null;
		if (!$normalized_phone_number) $normalized_phone_number = null;
		$stmt = $this->cm_db->connection->prepare(
			'INSERT INTO '.$this->cm_db->table_name('attendee_blacklist').' SET '.
			'`first_name` = ?, `last_name` = ?, `fandom_name` = ?, '.
			'`email_address` = ?, `phone_number` = ?, `added_by` = ?, '.
			'`normalized_real_name` = ?, '.
			'`normalized_reversed_name` = ?, '.
			'`normalized_fandom_name` = ?, '.
			'`normalized_email_address` = ?, '.
			'`normalized_phone_number` = ?'
		);
		$stmt->bind_param(
			'sssssssssss',
			$first_name, $last_name, $fandom_name,
			$email_address, $phone_number, $added_by,
			$normalized_real_name,
			$normalized_reversed_name,
			$normalized_fandom_name,
			$normalized_email_address,
			$normalized_phone_number
		);
		$id = $stmt->execute() ? $this->cm_db->connection->insert_id : false;
		$stmt->close();
		$this->cm_db->connection->autocommit(true);
		return $id;
	}

	public function update_blacklist_entry($entry) {
		if (!$entry || !isset($entry['id']) || !$entry['id']) return false;
		$first_name = (isset($entry['first-name']) ? $entry['first-name'] : '');
		$last_name = (isset($entry['last-name']) ? $entry['last-name'] : '');
		$fandom_name = (isset($entry['fandom-name']) ? $entry['fandom-name'] : '');
		$email_address = (isset($entry['email-address']) ? $entry['email-address'] : '');
		$phone_number = (isset($entry['phone-number']) ? $entry['phone-number'] : '');
		$added_by = (isset($entry['added-by']) ? $entry['added-by'] : '');
		$normalized_real_name = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '', $first_name . $last_name));
		$normalized_reversed_name = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '', $last_name . $first_name));
		$normalized_fandom_name = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '', $fandom_name));
		$normalized_email_address = strtoupper(preg_replace('/\\+.*@|[^A-Za-z0-9]+/', '', $email_address));
		$normalized_phone_number = preg_replace('/[^0-9]+/', '', $phone_number);
		if (!$first_name) $first_name = null;
		if (!$last_name) $last_name = null;
		if (!$fandom_name) $fandom_name = null;
		if (!$email_address) $email_address = null;
		if (!$phone_number) $phone_number = null;
		if (!$added_by) $added_by = null;
		if (!$normalized_real_name) $normalized_real_name = null;
		if (!$normalized_reversed_name) $normalized_reversed_name = null;
		if (!$normalized_fandom_name) $normalized_fandom_name = null;
		if (!$normalized_email_address) $normalized_email_address = null;
		if (!$normalized_phone_number) $normalized_phone_number = null;
		$stmt = $this->cm_db->connection->prepare(
			'UPDATE '.$this->cm_db->table_name('attendee_blacklist').' SET '.
			'`first_name` = ?, `last_name` = ?, `fandom_name` = ?, '.
			'`email_address` = ?, `phone_number` = ?, `added_by` = ?, '.
			'`normalized_real_name` = ?, '.
			'`normalized_reversed_name` = ?, '.
			'`normalized_fandom_name` = ?, '.
			'`normalized_email_address` = ?, '.
			'`normalized_phone_number` = ?'.
			' WHERE `id` = ? LIMIT 1'
		);
		$stmt->bind_param(
			'sssssssssssi',
			$first_name, $last_name, $fandom_name,
			$email_address, $phone_number, $added_by,
			$normalized_real_name,
			$normalized_reversed_name,
			$normalized_fandom_name,
			$normalized_email_address,
			$normalized_phone_number,
			$entry['id']
		);
		$success = $stmt->execute();
		$stmt->close();
		return $success;
	}

	public function delete_blacklist_entry($id) {
		if (!$id) return false;
		$stmt = $this->cm_db->connection->prepare(
			'DELETE FROM '.$this->cm_db->table_name('attendee_blacklist').
			' WHERE `id` = ? LIMIT 1'
		);
		$stmt->bind_param('i', $id);
		$success = $stmt->execute();
		$stmt->close();
		return $success;
	}

	public function is_blacklisted($person) {
		if (!$person) return false;
		$first_name = (isset($person['first-name']) ? $person['first-name'] : '');
		$last_name = (isset($person['last-name']) ? $person['last-name'] : '');
		$fandom_name = (isset($person['fandom-name']) ? $person['fandom-name'] : '');
		$email_address = (isset($person['email-address']) ? $person['email-address'] : '');
		$phone_number = (isset($person['phone-number']) ? $person['phone-number'] : '');
		$normalized_real_name = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '', $first_name . $last_name));
		$normalized_reversed_name = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '', $last_name . $first_name));
		$normalized_fandom_name = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '', $fandom_name));
		$normalized_email_address = strtoupper(preg_replace('/\\+.*@|[^A-Za-z0-9]+/', '', $email_address));
		$normalized_phone_number = preg_replace('/[^0-9]+/', '', $phone_number);
		$query_params = array();
		$bind_params = array('');
		if ($normalized_real_name) {
			$query_params[] = '`normalized_real_name` = ?';
			$query_params[] = '`normalized_reversed_name` = ?';
			$query_params[] = '`normalized_fandom_name` = ?';
			$bind_params[0] .= 'sss';
			$bind_params[] = &$normalized_real_name;
			$bind_params[] = &$normalized_real_name;
			$bind_params[] = &$normalized_real_name;
		}
		if ($normalized_reversed_name) {
			$query_params[] = '`normalized_real_name` = ?';
			$query_params[] = '`normalized_reversed_name` = ?';
			$query_params[] = '`normalized_fandom_name` = ?';
			$bind_params[0] .= 'sss';
			$bind_params[] = &$normalized_reversed_name;
			$bind_params[] = &$normalized_reversed_name;
			$bind_params[] = &$normalized_reversed_name;
		}
		if ($normalized_fandom_name) {
			$query_params[] = '`normalized_real_name` = ?';
			$query_params[] = '`normalized_reversed_name` = ?';
			$query_params[] = '`normalized_fandom_name` = ?';
			$bind_params[0] .= 'sss';
			$bind_params[] = &$normalized_fandom_name;
			$bind_params[] = &$normalized_fandom_name;
			$bind_params[] = &$normalized_fandom_name;
		}
		if ($normalized_email_address) {
			$query_params[] = '`normalized_email_address` = ?';
			$bind_params[0] .= 's';
			$bind_params[] = &$normalized_email_address;
		}
		if ($normalized_phone_number) {
			$query_params[] = '`normalized_phone_number` = ?';
			$bind_params[0] .= 's';
			$bind_params[] = &$normalized_phone_number;
		}
		if (!$query_params) return false;
		$stmt = $this->cm_db->connection->prepare(
			'SELECT `id` FROM '.
			$this->cm_db->table_name('attendee_blacklist').
			' WHERE '.implode(' OR ', $query_params).' LIMIT 1'
		);
		call_user_func_array(array($stmt, 'bind_param'), $bind_params);
		$stmt->execute();
		$stmt->bind_result($id);
		$success = $stmt->fetch();
		$stmt->close();
		return $success ? $this->get_blacklist_entry($id) : false;
	}

}
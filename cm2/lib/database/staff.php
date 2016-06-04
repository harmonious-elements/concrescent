<?php

require_once dirname(__FILE__).'/../../config/config.php';
require_once dirname(__FILE__).'/../util/util.php';
require_once dirname(__FILE__).'/database.php';
require_once dirname(__FILE__).'/lists.php';
require_once dirname(__FILE__).'/forms.php';

class cm_staff_db {

	public $mail_depths = array(
		'Executive',
		'Staff',
		'Recursive'
	);
	public $names_on_badge = array(
		'Fandom Name Large, Real Name Small',
		'Real Name Large, Fandom Name Small',
		'Fandom Name Only',
		'Real Name Only'
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

	public $event_info;
	public $cm_db;
	public $cm_ldb;

	public function __construct($cm_db) {
		$this->event_info = $GLOBALS['cm_config']['event'];
		$this->cm_db = $cm_db;
		$this->cm_db->table_def('staff_departments', (
			'`id` INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,'.
			'`parent_id` INTEGER NULL,'.
			'`name` VARCHAR(255) NOT NULL,'.
			'`description` TEXT NULL,'.
			'`mail_alias_1` VARCHAR(255) NULL,'.
			'`mail_alias_2` VARCHAR(255) NULL,'.
			'`mail_depth` ENUM('.
				'\'Executive\','.
				'\'Staff\','.
				'\'Recursive\''.
			') NULL,'.
			'`active` BOOLEAN NOT NULL'
		));
		$this->cm_db->table_def('staff_positions', (
			'`id` INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,'.
			'`parent_id` INTEGER NOT NULL,'.
			'`order` INTEGER NOT NULL,'.
			'`name` VARCHAR(255) NOT NULL,'.
			'`description` TEXT NULL,'.
			'`executive` BOOLEAN NOT NULL,'.
			'`active` BOOLEAN NOT NULL'
		));
		if (
			$this->cm_db->table_is_empty('staff_departments') &&
			$this->cm_db->table_is_empty('staff_positions')
		) {
			$this->create_department(array(
				'name' => 'Board',
				'description' => (
					'A default department automatically created '.
					'by CONcrescent during installation. Feel free '.
					'to modify or delete according to your needs.'
				),
				'mail-alias-1' => ('board@' . $_SERVER['SERVER_NAME']),
				'mail-depth' => 'Staff',
				'positions' => array(
					array('name' => 'President', 'executive' => true),
					array('name' => 'Vice President', 'executive' => true)
				)
			));
			$this->create_department(array(
				'name' => 'Chair',
				'description' => (
					'A default department automatically created '.
					'by CONcrescent during installation. Feel free '.
					'to modify or delete according to your needs.'
				),
				'mail-alias-1' => ('chair@' . $_SERVER['SERVER_NAME']),
				'mail-alias-2' => ('chairs@' . $_SERVER['SERVER_NAME']),
				'mail-depth' => 'Staff',
				'positions' => array(
					array('name' => 'Chair', 'executive' => true),
					array('name' => 'Vice Chair', 'executive' => true)
				)
			));
		}
		$this->cm_db->table_def('staff_badge_types', (
			'`id` INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,'.
			'`order` INTEGER NOT NULL,'.
			'`name` VARCHAR(255) NOT NULL,'.
			'`description` TEXT NULL,'.
			'`rewards` TEXT NULL,'.
			'`price` DECIMAL(7,2) NOT NULL,'.
			'`active` BOOLEAN NOT NULL,'.
			'`quantity` INTEGER NULL,'.
			'`start_date` DATE NULL,'.
			'`end_date` DATE NULL,'.
			'`min_age` INTEGER NULL,'.
			'`max_age` INTEGER NULL'
		));
		if ($this->cm_db->table_is_empty('staff_badge_types')) {
			$this->create_badge_type(array(
				'name' => 'Staff',
				'description' => (
					'A default badge type automatically created '.
					'by CONcrescent during installation. Feel free '.
					'to modify or delete according to your needs.'
				)
			));
		}
		$this->cm_db->table_def('staff_blacklist', (
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
		$this->cm_db->table_def('staff', (
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
			/* Application Info */
			'`application_status` ENUM('.
				'\'Submitted\','.
				'\'Cancelled\','.
				'\'Accepted\','.
				'\'Waitlisted\','.
				'\'Rejected\''.
			') NOT NULL,'.
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
		$this->cm_db->table_def('staff_assigned_positions', (
			'`staff_id` INTEGER NOT NULL,'.
			'`order` INTEGER NOT NULL,'.
			'`department_id` INTEGER NULL,'.
			'`department_name` VARCHAR(255) NULL,'.
			'`position_id` INTEGER NULL,'.
			'`position_name` VARCHAR(255) NULL,'.
			'PRIMARY KEY (`staff_id`, `order`)'
		));
		$this->cm_ldb = new cm_lists_db($this->cm_db, 'staff_search_index');
	}

	public function get_department($id) {
		if (!$id) return false;
		$stmt = $this->cm_db->connection->prepare(
			'SELECT `id`, `parent_id`, `name`, `description`,'.
			' `mail_alias_1`, `mail_alias_2`, `mail_depth`, `active`'.
			' FROM '.$this->cm_db->table_name('staff_departments').
			' WHERE `id` = ? LIMIT 1'
		);
		$stmt->bind_param('i', $id);
		$stmt->execute();
		$stmt->bind_result(
			$id, $parent_id, $name, $description,
			$mail_alias_1, $mail_alias_2, $mail_depth, $active
		);
		if ($stmt->fetch()) {
			$result = array(
				'id' => $id,
				'parent-id' => $parent_id,
				'name' => $name,
				'description' => $description,
				'mail-alias-1' => $mail_alias_1,
				'mail-alias-2' => $mail_alias_2,
				'mail-depth' => $mail_depth,
				'active' => !!$active,
				'hierarchy' => array($id => $name),
				'positions' => array(),
				'search-content' => array(
					$name, $description,
					$mail_alias_1, $mail_alias_2
				)
			);
			$stmt->close();

			while ($parent_id && !isset($result['hierarchy'][$parent_id])) {
				$stmt = $this->cm_db->connection->prepare(
					'SELECT `id`, `parent_id`, `name`'.
					' FROM '.$this->cm_db->table_name('staff_departments').
					' WHERE `id` = ? LIMIT 1'
				);
				$stmt->bind_param('i', $parent_id);
				$stmt->execute();
				$stmt->bind_result($id, $parent_id, $name);
				if ($stmt->fetch()) {
					$result['hierarchy'][$id] = $name;
					$result['search-content'][] = $name;
				} else {
					$parent_id = null;
				}
				$stmt->close();
			}
			$result['hierarchy'] = array_keys_values(
				array_reverse($result['hierarchy'], true),
				'id', 'name'
			);

			$stmt = $this->cm_db->connection->prepare(
				'SELECT `id`, `parent_id`, `order`, `name`,'.
				' `description`, `executive`, `active`'.
				' FROM '.$this->cm_db->table_name('staff_positions').
				' WHERE `parent_id` = ?'.
				' ORDER BY `order`'
			);
			$stmt->bind_param('i', $result['id']);
			$stmt->execute();
			$stmt->bind_result(
				$id, $parent_id, $order, $name,
				$description, $executive, $active
			);
			while ($stmt->fetch()) {
				$result['positions'][] = array(
					'id' => $id,
					'parent-id' => $parent_id,
					'order' => $order,
					'name' => $name,
					'description' => $description,
					'executive' => !!$executive,
					'active' => !!$active
				);
				$result['search-content'][] = $name;
				$result['search-content'][] = $description;
			}
			$stmt->close();

			return $result;
		}
		$stmt->close();
		return false;
	}

	public function list_departments() {
		$departments = array();
		$stmt = $this->cm_db->connection->prepare(
			'SELECT `id`, `parent_id`, `name`, `description`,'.
			' `mail_alias_1`, `mail_alias_2`, `mail_depth`, `active`'.
			' FROM '.$this->cm_db->table_name('staff_departments').
			' ORDER BY `name`'
		);
		$stmt->execute();
		$stmt->bind_result(
			$id, $parent_id, $name, $description,
			$mail_alias_1, $mail_alias_2, $mail_depth, $active
		);
		while ($stmt->fetch()) {
			$departments[$id] = array(
				'id' => $id,
				'parent-id' => $parent_id,
				'name' => $name,
				'description' => $description,
				'mail-alias-1' => $mail_alias_1,
				'mail-alias-2' => $mail_alias_2,
				'mail-depth' => $mail_depth,
				'active' => !!$active,
				'hierarchy' => array($id => $name),
				'positions' => array(),
				'search-content' => array(
					$name, $description,
					$mail_alias_1, $mail_alias_2
				)
			);
		}
		$stmt->close();

		foreach (array_keys($departments) as $id) {
			$parent_id = $departments[$id]['parent-id'];
			while ($parent_id && !isset($departments[$id]['hierarchy'][$parent_id]) && isset($departments[$parent_id])) {
				$departments[$id]['hierarchy'][$parent_id] = $departments[$parent_id]['name'];
				$departments[$id]['search-content'][] = $departments[$parent_id]['name'];
				$parent_id = $departments[$parent_id]['parent-id'];
			}
			$departments[$id]['hierarchy'] = array_keys_values(
				array_reverse($departments[$id]['hierarchy'], true),
				'id', 'name'
			);
		}

		$stmt = $this->cm_db->connection->prepare(
			'SELECT `id`, `parent_id`, `order`, `name`,'.
			' `description`, `executive`, `active`'.
			' FROM '.$this->cm_db->table_name('staff_positions').
			' ORDER BY `order`'
		);
		$stmt->execute();
		$stmt->bind_result(
			$id, $parent_id, $order, $name,
			$description, $executive, $active
		);
		while ($stmt->fetch()) {
			$departments[$parent_id]['positions'][] = array(
				'id' => $id,
				'parent-id' => $parent_id,
				'order' => $order,
				'name' => $name,
				'description' => $description,
				'executive' => !!$executive,
				'active' => !!$active
			);
			$departments[$parent_id]['search-content'][] = $name;
			$departments[$parent_id]['search-content'][] = $description;
		}
		$stmt->close();

		return array_values($departments);
	}

	public function create_department($department) {
		if (!$department) return false;
		$this->cm_db->connection->autocommit(false);

		$parent_id = ((isset($department['parent-id']) && (int)$department['parent-id']) ? (int)$department['parent-id'] : null);
		$name = (isset($department['name']) ? $department['name'] : '');
		$description = (isset($department['description']) ? $department['description'] : '');
		$mail_alias_1 = ((isset($department['mail-alias-1']) && $department['mail-alias-1']) ? $department['mail-alias-1'] : null);
		$mail_alias_2 = ((isset($department['mail-alias-2']) && $department['mail-alias-2']) ? $department['mail-alias-2'] : null);
		$mail_depth = ((isset($department['mail-depth']) && $department['mail-depth']) ? $department['mail-depth'] : null);
		$active = (isset($department['active']) ? ($department['active'] ? 1 : 0) : 1);
		$stmt = $this->cm_db->connection->prepare(
			'INSERT INTO '.$this->cm_db->table_name('staff_departments').' SET '.
			'`parent_id` = ?, `name` = ?, `description` = ?, '.
			'`mail_alias_1` = ?, `mail_alias_2` = ?, `mail_depth` = ?, `active` = ?'
		);
		$stmt->bind_param(
			'isssssi',
			$parent_id, $name, $description,
			$mail_alias_1, $mail_alias_2, $mail_depth, $active
		);
		$id = $stmt->execute() ? $this->cm_db->connection->insert_id : false;
		$stmt->close();

		if ($id !== false && isset($department['positions']) && $department['positions']) {
			$order = 0;
			foreach ($department['positions'] as $position) {
				$order++;
				$name = (isset($position['name']) ? $position['name'] : '');
				$description = (isset($position['description']) ? $position['description'] : '');
				$executive = (isset($position['executive']) ? ($position['executive'] ? 1 : 0) : 0);
				$active = (isset($position['active']) ? ($position['active'] ? 1 : 0) : 1);
				$stmt = $this->cm_db->connection->prepare(
					'INSERT INTO '.$this->cm_db->table_name('staff_positions').' SET '.
					'`parent_id` = ?, `order` = ?, `name` = ?, '.
					'`description` = ?, `executive` = ?, `active` = ?'
				);
				$stmt->bind_param(
					'iissii',
					$id, $order, $name,
					$description, $executive, $active
				);
				$stmt->execute();
				$stmt->close();
			}
		}

		$this->cm_db->connection->autocommit(true);
		return $id;
	}

	public function update_department($department) {
		if (!$department || !isset($department['id']) || !$department['id']) return false;
		$this->cm_db->connection->autocommit(false);

		$parent_id = ((isset($department['parent-id']) && (int)$department['parent-id']) ? (int)$department['parent-id'] : null);
		$name = (isset($department['name']) ? $department['name'] : '');
		$description = (isset($department['description']) ? $department['description'] : '');
		$mail_alias_1 = ((isset($department['mail-alias-1']) && $department['mail-alias-1']) ? $department['mail-alias-1'] : null);
		$mail_alias_2 = ((isset($department['mail-alias-2']) && $department['mail-alias-2']) ? $department['mail-alias-2'] : null);
		$mail_depth = ((isset($department['mail-depth']) && $department['mail-depth']) ? $department['mail-depth'] : null);
		$active = (isset($department['active']) ? ($department['active'] ? 1 : 0) : 1);
		$stmt = $this->cm_db->connection->prepare(
			'UPDATE '.$this->cm_db->table_name('staff_departments').' SET '.
			'`parent_id` = ?, `name` = ?, `description` = ?, '.
			'`mail_alias_1` = ?, `mail_alias_2` = ?, `mail_depth` = ?, `active` = ?'.
			' WHERE `id` = ? LIMIT 1'
		);
		$stmt->bind_param(
			'isssssii',
			$parent_id, $name, $description,
			$mail_alias_1, $mail_alias_2, $mail_depth, $active,
			$department['id']
		);
		$success = $stmt->execute();
		$stmt->close();

		if ($success) {
			$stmt = $this->cm_db->connection->prepare(
				'DELETE FROM '.$this->cm_db->table_name('staff_positions').
				' WHERE `parent_id` = ?'
			);
			$stmt->bind_param('i', $department['id']);
			$stmt->execute();
			$stmt->close();
			if (isset($department['positions']) && $department['positions']) {
				$order = 0;
				foreach ($department['positions'] as $position) {
					$order++;
					$name = (isset($position['name']) ? $position['name'] : '');
					$description = (isset($position['description']) ? $position['description'] : '');
					$executive = (isset($position['executive']) ? ($position['executive'] ? 1 : 0) : 0);
					$active = (isset($position['active']) ? ($position['active'] ? 1 : 0) : 1);
					$id = ((isset($position['id']) && (int)$position['id']) ? (int)$position['id'] : null);
					$stmt = $this->cm_db->connection->prepare(
						'INSERT INTO '.$this->cm_db->table_name('staff_positions').' SET '.
						'`id` = ?, `parent_id` = ?, `order` = ?, `name` = ?, '.
						'`description` = ?, `executive` = ?, `active` = ?'
					);
					$stmt->bind_param(
						'iiissii',
						$id, $department['id'], $order, $name,
						$description, $executive, $active
					);
					$stmt->execute();
					$stmt->close();
				}
			}
		}

		$this->cm_db->connection->autocommit(true);
		return $success;
	}

	public function delete_department($id) {
		if (!$id) return false;
		$this->cm_db->connection->autocommit(false);

		$stmt = $this->cm_db->connection->prepare(
			'SELECT `parent_id`'.
			' FROM '.$this->cm_db->table_name('staff_departments').
			' WHERE `id` = ? LIMIT 1'
		);
		$stmt->bind_param('i', $id);
		$stmt->execute();
		$stmt->bind_result($parent_id);
		if (!$stmt->fetch()) $parent_id = null;
		$stmt->close();

		$stmt = $this->cm_db->connection->prepare(
			'DELETE FROM '.$this->cm_db->table_name('staff_departments').
			' WHERE `id` = ? LIMIT 1'
		);
		$stmt->bind_param('i', $id);
		$success = $stmt->execute();
		$stmt->close();

		if ($success) {
			$stmt = $this->cm_db->connection->prepare(
				'UPDATE '.$this->cm_db->table_name('staff_departments').
				' SET `parent_id` = ?'.
				' WHERE `parent_id` = ?'
			);
			$stmt->bind_param('ii', $parent_id, $id);
			$stmt->execute();
			$stmt->close();

			$stmt = $this->cm_db->connection->prepare(
				'DELETE FROM '.$this->cm_db->table_name('staff_positions').
				' WHERE `parent_id` = ?'
			);
			$stmt->bind_param('i', $id);
			$stmt->execute();
			$stmt->close();
		}

		$this->cm_db->connection->autocommit(true);
		return $success;
	}

	public function activate_department($id, $active) {
		if (!$id) return false;
		$active = $active ? 1 : 0;
		$stmt = $this->cm_db->connection->prepare(
			'UPDATE '.$this->cm_db->table_name('staff_departments').
			' SET `active` = ? WHERE `id` = ? LIMIT 1'
		);
		$stmt->bind_param('ii', $active, $id);
		$success = $stmt->execute();
		$stmt->close();
		return $success;
	}

	public function get_badge_type($id) {
		if (!$id) return false;
		$stmt = $this->cm_db->connection->prepare(
			'SELECT b.`id`, b.`order`, b.`name`, b.`description`, b.`rewards`,'.
			' b.`price`, b.`active`, b.`quantity`,'.
			' b.`start_date`, b.`end_date`, b.`min_age`, b.`max_age`,'.
			' (SELECT COUNT(*) FROM '.$this->cm_db->table_name('staff').' a'.
			' WHERE a.`badge_type_id` = b.`id` AND a.`payment_status` = \'Completed\') c'.
			' FROM '.$this->cm_db->table_name('staff_badge_types').' b'.
			' WHERE `id` = ? LIMIT 1'
		);
		$stmt->bind_param('i', $id);
		$stmt->execute();
		$stmt->bind_result(
			$id, $order, $name, $description, $rewards,
			$price, $active, $quantity,
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
			' FROM '.$this->cm_db->table_name('staff_badge_types').
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
			' FROM '.$this->cm_db->table_name('staff_badge_types').
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
			' b.`price`, b.`active`, b.`quantity`,'.
			' b.`start_date`, b.`end_date`, b.`min_age`, b.`max_age`,'.
			' (SELECT COUNT(*) FROM '.$this->cm_db->table_name('staff').' a'.
			' WHERE a.`badge_type_id` = b.`id` AND a.`payment_status` = \'Completed\') c'.
			' FROM '.$this->cm_db->table_name('staff_badge_types').' b'
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
			$price, $active, $quantity,
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
			$this->cm_db->table_name('staff_badge_types')
		);
		$stmt->execute();
		$stmt->bind_result($order);
		$stmt->fetch();
		$stmt->close();
		$name = (isset($badge_type['name']) ? $badge_type['name'] : '');
		$description = (isset($badge_type['description']) ? $badge_type['description'] : '');
		$rewards = (isset($badge_type['rewards']) ? implode("\n", $badge_type['rewards']) : '');
		$price = (isset($badge_type['price']) ? (float)$badge_type['price'] : 0);
		$active = (isset($badge_type['active']) ? ($badge_type['active'] ? 1 : 0) : 1);
		$quantity = (isset($badge_type['quantity']) ? $badge_type['quantity'] : null);
		$start_date = (isset($badge_type['start-date']) ? $badge_type['start-date'] : null);
		$end_date = (isset($badge_type['end-date']) ? $badge_type['end-date'] : null);
		$min_age = (isset($badge_type['min-age']) ? $badge_type['min-age'] : null);
		$max_age = (isset($badge_type['max-age']) ? $badge_type['max-age'] : null);
		$stmt = $this->cm_db->connection->prepare(
			'INSERT INTO '.$this->cm_db->table_name('staff_badge_types').' SET '.
			'`order` = ?, `name` = ?, `description` = ?, `rewards` = ?, '.
			'`price` = ?, `active` = ?, `quantity` = ?, '.
			'`start_date` = ?, `end_date` = ?, `min_age` = ?, `max_age` = ?'
		);
		$stmt->bind_param(
			'isssdiissii',
			$order, $name, $description, $rewards,
			$price, $active, $quantity,
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
		$active = (isset($badge_type['active']) ? ($badge_type['active'] ? 1 : 0) : 1);
		$quantity = (isset($badge_type['quantity']) ? $badge_type['quantity'] : null);
		$start_date = (isset($badge_type['start-date']) ? $badge_type['start-date'] : null);
		$end_date = (isset($badge_type['end-date']) ? $badge_type['end-date'] : null);
		$min_age = (isset($badge_type['min-age']) ? $badge_type['min-age'] : null);
		$max_age = (isset($badge_type['max-age']) ? $badge_type['max-age'] : null);
		$stmt = $this->cm_db->connection->prepare(
			'UPDATE '.$this->cm_db->table_name('staff_badge_types').' SET '.
			'`name` = ?, `description` = ?, `rewards` = ?, '.
			'`price` = ?, `active` = ?, `quantity` = ?, '.
			'`start_date` = ?, `end_date` = ?, `min_age` = ?, `max_age` = ?'.
			' WHERE `id` = ? LIMIT 1'
		);
		$stmt->bind_param(
			'sssdiissiii',
			$name, $description, $rewards,
			$price, $active, $quantity,
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
			'DELETE FROM '.$this->cm_db->table_name('staff_badge_types').
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
			'UPDATE '.$this->cm_db->table_name('staff_badge_types').
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
			$this->cm_db->table_name('staff_badge_types').
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
					'UPDATE '.$this->cm_db->table_name('staff_badge_types').
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
			' FROM '.$this->cm_db->table_name('staff_blacklist').
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
			' FROM '.$this->cm_db->table_name('staff_blacklist').
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
			'INSERT INTO '.$this->cm_db->table_name('staff_blacklist').' SET '.
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
			'UPDATE '.$this->cm_db->table_name('staff_blacklist').' SET '.
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
			'DELETE FROM '.$this->cm_db->table_name('staff_blacklist').
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
			$this->cm_db->table_name('staff_blacklist').
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
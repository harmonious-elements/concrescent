<?php

require_once dirname(__FILE__).'/../util/util.php';
require_once dirname(__FILE__).'/database.php';

class cm_staff_db {

	public $mail_depths = array(
		'Executive',
		'Staff',
		'Recursive'
	);

	public $cm_db;

	public function __construct($cm_db) {
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

}
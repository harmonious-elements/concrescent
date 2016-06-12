<?php

require_once dirname(__FILE__).'/database.php';

class cm_misc_db {

	public $cm_db;

	public function __construct($cm_db) {
		$this->cm_db = $cm_db;
		$this->cm_db->table_def('config_misc', (
			'`key` VARCHAR(255) NOT NULL PRIMARY KEY,'.
			'`value` TEXT NULL'
		));
	}

	public function getval($key, $def = null) {
		if (!$key) return $def;
		$stmt = $this->cm_db->connection->prepare(
			'SELECT `value` FROM '.$this->cm_db->table_name('config_misc').
			' WHERE `key` = ? LIMIT 1'
		);
		$stmt->bind_param('s', $key);
		$stmt->execute();
		$stmt->bind_result($value);
		$success = $stmt->fetch() && $value;
		$stmt->close();
		return $success ? $value : $def;
	}

	public function setval($key, $value) {
		if (!$key) return false;
		$stmt = $this->cm_db->connection->prepare(
			'INSERT INTO '.$this->cm_db->table_name('config_misc').
			' SET `key` = ?, `value` = ?'.
			' ON DUPLICATE KEY UPDATE `value` = ?'
		);
		$stmt->bind_param('sss', $key, $value, $value);
		$success = $stmt->execute();
		$stmt->close();
		return $success;
	}

	public function clearval($key) {
		if (!$key) return false;
		$stmt = $this->cm_db->connection->prepare(
			'DELETE FROM '.$this->cm_db->table_name('config_misc').
			' WHERE `key` = ? LIMIT 1'
		);
		$stmt->bind_param('s', $key);
		$success = $stmt->execute();
		$stmt->close();
		return $success;
	}

}
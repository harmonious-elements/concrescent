<?php

require_once dirname(__FILE__).'/../../config/config.php';

class cm_db {

	public $table_prefix;
	public $connection;
	public $known_tables;

	public function __construct() {
		/* Load configuration */
		$config = $GLOBALS['cm_config']['database'];
		$this->table_prefix = $config['prefix'];

		/* Connect to database */
		$this->connection = new mysqli(
			$config['host'], $config['username'],
			$config['password'], $config['database']
		);

		/* Set text encoding */
		$this->connection->query('set names utf8');
		$this->connection->query('set character set utf8');

		/* Set time zone */
		$stmt = $this->connection->prepare('set time_zone = ?');
		$stmt->bind_param('s', $config['timezone']);
		$stmt->execute();
		$stmt->close();

		/* Load known tables */
		$this->known_tables = array();
		$stmt = $this->connection->prepare(
			'SELECT table_name '.
			'FROM information_schema.tables '.
			'WHERE table_schema = ?'
		);
		$stmt->bind_param('s', $config['database']);
		$stmt->execute();
		$stmt->bind_result($table);
		while ($stmt->fetch()) {
			$this->known_tables[$table] = true;
		}
		$stmt->close();
	}

	public function table_def($table, $def) {
		$tn = $this->table_prefix . $table;
		if (!isset($this->known_tables[$tn])) {
			$this->known_tables[$tn] = true;
			$this->connection->query(
				'CREATE TABLE IF NOT EXISTS '.
				'`' . $tn . '` '.
				'(' . $def . ')'
			);
		}
	}

	public function table_name($table) {
		return '`' . $this->table_prefix . $table . '`';
	}

	public function table_is_empty($table) {
		$tn = $this->table_name($table);
		$result = $this->connection->query('SELECT 1 FROM ' . $tn . ' LIMIT 1');
		if ($result) {
			$is_empty = !$result->num_rows;
			$result->close();
			return $is_empty;
		} else {
			return true;
		}
	}

}
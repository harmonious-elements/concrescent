<?php

require_once dirname(__FILE__).'/../../config/config.php';
require_once dirname(__FILE__).'/../util/password.php';
require_once dirname(__FILE__).'/database.php';

class cm_admin_db {

	public $cm_db;

	public function __construct($cm_db) {
		$this->cm_db = $cm_db;
		$this->cm_db->table_def('admin_users', (
			'`name` VARCHAR(255) NOT NULL,'.
			'`username` VARCHAR(255) NOT NULL PRIMARY KEY,'.
			'`password` VARCHAR(255) NOT NULL,'.
			'`permissions` TEXT NOT NULL'
		));
		if ($this->cm_db->table_is_empty('admin_users')) {
			$config = $GLOBALS['cm_config']['default_admin'];
			$password = password_hash($config['password'], PASSWORD_DEFAULT);
			$permissions = '*';
			$stmt = $this->cm_db->connection->prepare(
				'INSERT INTO '.$this->cm_db->table_name('admin_users').' SET '.
				'`name` = ?, `username` = ?, `password` = ?, `permissions` = ?'
			);
			$stmt->bind_param(
				'ssss',
				$config['name'],
				$config['username'],
				$password,
				$permissions
			);
			$stmt->execute();
			$stmt->close();
		}
	}

	public function logged_in_user() {
		$username = isset($_SESSION['admin_username']);
		$password = isset($_SESSION['admin_password']);
		if (!$username || !$password) return false;
		$username = $_SESSION['admin_username'];
		$password = $_SESSION['admin_password'];
		if (!$username || !$password) return false;
		$stmt = $this->cm_db->connection->prepare(
			'SELECT `name`, `username`, `password`, `permissions`'.
			' FROM '.$this->cm_db->table_name('admin_users').
			' WHERE `username` = ? LIMIT 1'
		);
		$stmt->bind_param('s', $username);
		$stmt->execute();
		$stmt->bind_result($name, $username, $hash, $permissions);
		if ($stmt->fetch()) {
			if (password_verify($password, $hash)) {
				$result = array(
					'name' => $name,
					'username' => $username,
					'permissions' => explode(',', $permissions)
				);
				$stmt->close();
				return $result;
			}
		}
		$stmt->close();
		return false;
	}

	public function log_in($username, $password) {
		$_SESSION['admin_username'] = $username;
		$_SESSION['admin_password'] = $password;
		return $this->logged_in_user();
	}

	public function log_out() {
		unset($_SESSION['admin_username']);
		unset($_SESSION['admin_password']);
		session_destroy();
	}

	public function user_has_permission($user, $permission) {
		return ($user && $user['permissions'] && (
			in_array('*', $user['permissions']) ||
			in_array($permission, $user['permissions'])
		));
	}

	public function get_user($username) {
		if (!$username) return false;
		$stmt = $this->cm_db->connection->prepare(
			'SELECT `name`, `username`, `permissions`'.
			' FROM '.$this->cm_db->table_name('admin_users').
			' WHERE `username` = ? LIMIT 1'
		);
		$stmt->bind_param('s', $username);
		$stmt->execute();
		$stmt->bind_result($name, $username, $permissions);
		if ($stmt->fetch()) {
			$result = array(
				'name' => $name,
				'username' => $username,
				'permissions' => explode(',', $permissions)
			);
			$stmt->close();
			return $result;
		}
		$stmt->close();
		return false;
	}

	public function list_users() {
		$users = array();
		$stmt = $this->cm_db->connection->prepare(
			'SELECT `name`, `username`, `permissions`'.
			' FROM '.$this->cm_db->table_name('admin_users').
			' ORDER BY `name`'
		);
		$stmt->execute();
		$stmt->bind_result($name, $username, $permissions);
		while ($stmt->fetch()) {
			$users[] = array(
				'name' => $name,
				'username' => $username,
				'permissions' => explode(',', $permissions)
			);
		}
		$stmt->close();
		return $users;
	}

	public function create_user($user) {
		if (!$user) return false;
		if (!isset($user['username']) || !$user['username']) return false;
		if (!isset($user['password']) || !$user['password']) return false;
		/* Get field values */
		$name = isset($user['name']) ? $user['name'] : '';
		$username = $user['username'];
		$password = password_hash($user['password'], PASSWORD_DEFAULT);
		$permissions = (
			(isset($user['permissions']) && $user['permissions']) ?
			implode(',', $user['permissions']) : ''
		);
		/* Create and execute query */
		$stmt = $this->cm_db->connection->prepare(
			'INSERT INTO '.$this->cm_db->table_name('admin_users').' SET '.
			'`name` = ?, `username` = ?, `password` = ?, `permissions` = ?'
		);
		$stmt->bind_param(
			'ssss',
			$name,
			$username,
			$password,
			$permissions
		);
		$success = $stmt->execute();
		$stmt->close();
		return $success;
	}

	public function update_user($username, $user) {
		if (!$username || !$user) return false;
		/* Get field values */
		$new_password = '';
		$new_permissions = '';
		$query_params = array();
		$bind_params = array('');
		if (isset($user['name']) && $user['name']) {
			$query_params[] = '`name` = ?';
			$bind_params[0] .= 's';
			$bind_params[] = &$user['name'];
		}
		if (isset($user['username']) && $user['username']) {
			$query_params[] = '`username` = ?';
			$bind_params[0] .= 's';
			$bind_params[] = &$user['username'];
		}
		if (isset($user['password']) && $user['password']) {
			$new_password = password_hash($user['password'], PASSWORD_DEFAULT);
			$query_params[] = '`password` = ?';
			$bind_params[0] .= 's';
			$bind_params[] = &$new_password;
		}
		if (isset($user['permissions']) && $user['permissions']) {
			$new_permissions = implode(',', $user['permissions']);
			$query_params[] = '`permissions` = ?';
			$bind_params[0] .= 's';
			$bind_params[] = &$new_permissions;
		}
		$bind_params[0] .= 's';
		$bind_params[] = &$username;
		/* Create and execute query */
		$stmt = $this->cm_db->connection->prepare(
			'UPDATE '.$this->cm_db->table_name('admin_users').' SET '.
			implode(', ', $query_params).' WHERE `username` = ? LIMIT 1'
		);
		call_user_func_array(array($stmt, 'bind_param'), $bind_params);
		$success = $stmt->execute();
		$stmt->close();
		return $success;
	}

	public function delete_user($username) {
		if (!$username) return false;
		$stmt = $this->cm_db->connection->prepare(
			'DELETE FROM '.$this->cm_db->table_name('admin_users').
			' WHERE `username` = ? LIMIT 1'
		);
		$stmt->bind_param('s', $username);
		$success = $stmt->execute();
		$stmt->close();
		return $success;
	}

}
<?php

require_once dirname(__FILE__).'/dal.php';
require_once dirname(__FILE__).'/../schema/admin.php';
require_once dirname(__FILE__).'/../base/sql.php';
require_once dirname(__FILE__).'/../base/password.php';

function db_require_admin_users_table($connection) {
	db_require_table('admin_users', $connection);
	$results = mysql_query('SELECT COUNT(*) FROM '.db_table_name('admin_users'), $connection);
	$result = mysql_fetch_assoc($results);
	$count = (int)$result['COUNT(*)'];
	if (!$count) {
		mysql_query(
			('INSERT INTO '.db_table_name('admin_users').' SET'.
			' `name` = '.q_string('Administrator').','.
			' `username` = '.q_string('admin').','.
			' `password` = '.q_string(password_hash('password', PASSWORD_DEFAULT)).','.
			' `permissions` = '.q_string('*')),
			$connection
		);
	}
}

function admin_logged_in($connection) {
	$username = isset($_SESSION['admin_username']);
	$password = isset($_SESSION['admin_password']);
	if ($username && $password) {
		$username = $_SESSION['admin_username'];
		$password = $_SESSION['admin_password'];
		if ($username && $password) {
			db_require_admin_users_table($connection);
			$results = mysql_query('SELECT * FROM '.db_table_name('admin_users').' WHERE `username` = '.q_string($username), $connection);
			if ($result = mysql_fetch_assoc($results)) {
				$hash = unpurify_string($result['password']);
				if (password_verify($password, $hash)) {
					return array(
						'name' => unpurify_string($result['name']),
						'username' => unpurify_string($result['username']),
						'permissions' => explode(',', unpurify_string($result['permissions'])),
					);
				} else {
					return false;
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
	} else {
		return false;
	}
}

function admin_has_permission($admin, $permission) {
	return ($admin && $admin['permissions'] && (in_array('*', $admin['permissions']) || in_array($permission, $admin['permissions'])));
}

function admin_log_in($connection, $username, $password) {
	$_SESSION['admin_username'] = $username;
	$_SESSION['admin_password'] = $password;
	return admin_logged_in($connection);
}

function admin_log_out() {
	unset($_SESSION['admin_username']);
	unset($_SESSION['admin_password']);
	session_destroy();
}

function decode_admin_user($result) {
	$name = unpurify_string($result['name']);
	$username = unpurify_string($result['username']);
	$permissions = unpurify_string($result['permissions']);
	return array(
		'name' => $name,
		'username' => $username,
		'permissions' => $permissions,
	);
}

function encode_admin_user($result, $new) {
	$set = array();
	if (isset($result['name']) && ($new || $result['name'])) {
		$set[] = '`name` = ' . q_string($result['name']);
	}
	if (isset($result['username']) && ($new || $result['username'])) {
		$set[] = '`username` = ' . q_string($result['username']);
	}
	if (isset($result['password']) && ($new || $result['password'])) {
		$set[] = '`password` = ' . q_string(password_hash($result['password'], PASSWORD_DEFAULT));
	}
	if (isset($result['permissions']) && ($new || $result['permissions'])) {
		$set[] = '`permissions` = ' . q_string($result['permissions']);
	}
	return implode(', ', $set);
}

function get_admin_users($connection) {
	db_require_table('admin_users', $connection);
	return mysql_query('SELECT * FROM '.db_table_name('admin_users').' ORDER BY `name`', $connection);
}

function get_admin_user($results) {
	return mysql_fetch_assoc($results);
}

function upsert_admin_user($user, $set, $connection) {
	db_require_table('admin_users', $connection);
	if ($user) {
		$q = 'UPDATE '.db_table_name('admin_users').' SET '.$set.' WHERE `username` = '.q_string($user);
	} else {
		$q = 'INSERT INTO '.db_table_name('admin_users').' SET '.$set;
	}
	mysql_query($q, $connection);
}

function delete_admin_user($user, $connection) {
	db_require_table('admin_users', $connection);
	mysql_query('DELETE FROM '.db_table_name('admin_users').' WHERE `username` = '.q_string($user), $connection);
}
<?php

$cm_admin_nav = array(
	array(
		array(
			'id' => 'home',
			'href' => '/admin/index.php',
			'name' => 'Home',
			'description' => '',
			'permission' => ''
		),
	),
	array(
		array(
			'id' => 'attendee-mail',
			'href' => '/admin/attendee/mail.php',
			'name' => 'Attendee Form Letters',
			'description' => 'Write form letters to be emailed to attendees.',
			'permission' => 'attendee-mail'
		),
	),
	array(
		array(
			'id' => 'admin-users',
			'href' => '/admin/users.php',
			'name' => 'Admin Users',
			'description' => 'Manage CONcrescent administrators and their permissions.',
			'permission' => 'admin-users'
		),
	),
);
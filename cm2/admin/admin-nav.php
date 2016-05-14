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
			'id' => 'attendee-badge-types',
			'href' => '/admin/attendee/badge-types.php',
			'name' => 'Attendee Badge Types',
			'description' => 'Create or modify the types of badges available to attendees.',
			'permission' => 'attendee-badge-types'
		),
		array(
			'id' => 'attendee-promo-codes',
			'href' => '/admin/attendee/promo-codes.php',
			'name' => 'Attendee Promo Codes',
			'description' => 'Add or remove codes for discounts on badges for attendees.',
			'permission' => 'attendee-promo-codes'
		),
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
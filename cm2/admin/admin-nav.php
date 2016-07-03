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
		array(
			'id' => 'statistics',
			'href' => '/admin/statistics.php',
			'name' => 'Statistics',
			'description' => 'Get a statistical overview of people registered and checked in.',
			'permission' => 'statistics'
		),
	),
	array(
		array(
			'id' => 'attendees',
			'href' => '/admin/attendee/index.php',
			'name' => 'Attendees',
			'description' => 'View and modify attendee registration records.',
			'permission' => array('||', 'attendees', 'attendees-view', 'attendees-edit', 'attendees-delete')
		),
		array(
			'id' => 'attendee-badge-types',
			'href' => '/admin/attendee/badge-types.php',
			'name' => 'Attendee Badge Types',
			'description' => 'Create or modify the types of badges available to attendees.',
			'permission' => 'attendee-badge-types'
		),
		array(
			'id' => 'attendee-questions',
			'href' => '/admin/attendee/questions.php',
			'name' => 'Attendee Questions',
			'description' => 'Add explanatory text and questions to the attendee registration form.',
			'permission' => 'attendee-questions'
		),
		array(
			'id' => 'attendee-promo-codes',
			'href' => '/admin/attendee/promo-codes.php',
			'name' => 'Attendee Promo Codes',
			'description' => 'Add or remove codes for discounts on badges for attendees.',
			'permission' => 'attendee-promo-codes'
		),
		array(
			'id' => 'attendee-blacklist',
			'href' => '/admin/attendee/blacklist.php',
			'name' => 'Attendee Blacklist',
			'description' => 'Block certain people from being able to register as attendees.',
			'permission' => 'attendee-blacklist'
		),
		array(
			'id' => 'attendee-mail',
			'href' => '/admin/attendee/mail.php',
			'name' => 'Attendee Form Letters',
			'description' => 'Write form letters to be emailed to attendees.',
			'permission' => 'attendee-mail'
		),
		array(
			'id' => 'attendee-csv',
			'href' => '/admin/attendee/csv.php',
			'name' => 'Attendee CSV',
			'description' => 'Download a CSV file of attendee registration records.',
			'permission' => 'attendee-csv'
		),
	),
	array(
		array(
			'id' => 'rooms-and-tables',
			'href' => '/admin/rooms-and-tables.php',
			'name' => 'Rooms & Tables',
			'description' => 'Upload an event space floor plan and tag rooms and tables with their identifiers.',
			'permission' => 'rooms-and-tables'
		),
	),
	array(
		array(
			'id' => 'staff-departments',
			'href' => '/admin/staff/departments.php',
			'name' => 'Departments',
			'description' => 'Organize the departments and positions that make up the event staff org chart.',
			'permission' => 'staff-departments'
		),
		array(
			'id' => 'staff-orgchart',
			'href' => '/admin/staff/orgchart.php',
			'name' => 'Org Chart',
			'description' => 'View the event staff org chart.',
			'permission' => 'staff-orgchart'
		),
		array(
			'id' => 'staff-maillist',
			'href' => '/admin/staff/maillist.php',
			'name' => 'Mailing Lists',
			'description' => 'Generate mailing list memberships based on staff applications and the org chart.',
			'permission' => 'staff-maillist'
		),
	),
	array(
		array(
			'id' => 'staff',
			'href' => '/admin/staff/index.php',
			'name' => 'Staff Applications',
			'description' => 'Review, approve, or modify staff applications.',
			'permission' => array('||', 'staff', 'staff-view', 'staff-review', 'staff-edit', 'staff-delete')
		),
		array(
			'id' => 'staff-badge-types',
			'href' => '/admin/staff/badge-types.php',
			'name' => 'Staff Badge Types',
			'description' => 'Create or modify the types of badges available for staff members.',
			'permission' => 'staff-badge-types'
		),
		array(
			'id' => 'staff-questions',
			'href' => '/admin/staff/questions.php',
			'name' => 'Staff Questions',
			'description' => 'Add explanatory text and questions to the staff application form.',
			'permission' => 'staff-questions'
		),
		array(
			'id' => 'staff-blacklist',
			'href' => '/admin/staff/blacklist.php',
			'name' => 'Staff Blacklist',
			'description' => 'Inform people reviewing staff applications of certain people who should not be accepted.',
			'permission' => 'staff-blacklist'
		),
		array(
			'id' => 'staff-mail',
			'href' => '/admin/staff/mail.php',
			'name' => 'Staff Form Letters',
			'description' => 'Write form letters to be emailed to staff members.',
			'permission' => 'staff-mail'
		),
		array(
			'id' => 'staff-csv',
			'href' => '/admin/staff/csv.php',
			'name' => 'Staff CSV',
			'description' => 'Download a CSV file of staff application records.',
			'permission' => 'staff-csv'
		),
	),
	array(
		array(
			'id' => 'admin-user',
			'href' => '/admin/user.php',
			'name' => 'Account Settings',
			'description' => 'Change your user name or password.',
			'permission' => ''
		),
		array(
			'id' => 'admin-users',
			'href' => '/admin/users.php',
			'name' => 'Admin Users',
			'description' => 'Manage CONcrescent administrators and their permissions.',
			'permission' => 'admin-users'
		),
	),
);
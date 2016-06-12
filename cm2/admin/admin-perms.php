<?php

$cm_admin_perms = array(
	array(
		array(
			'id' => 'statistics',
			'name' => 'Statistics',
			'description' => 'Get a statistical overview of people registered and checked in.'
		),
	),
	array(
		array(
			'id' => 'attendees',
			'name' => 'Attendees',
			'description' => 'View the list of attendee registration records.'
		),
		array(
			'id' => 'attendees-view',
			'name' => 'Attendees - View',
			'description' => 'View individual attendee registration records.'
		),
		array(
			'id' => 'attendees-edit',
			'name' => 'Attendees - Edit',
			'description' => 'Modify attendee registration records.'
		),
		array(
			'id' => 'attendees-delete',
			'name' => 'Attendees - Delete',
			'description' => 'Delete attendee registration records.'
		),
	),
	array(
		array(
			'id' => 'attendee-badge-types',
			'name' => 'Attendee Badge Types',
			'description' => 'Create or modify the types of badges available to attendees.'
		),
		array(
			'id' => 'attendee-questions',
			'name' => 'Attendee Questions',
			'description' => 'Add explanatory text and questions to the attendee registration form.'
		),
		array(
			'id' => 'attendee-promo-codes',
			'name' => 'Attendee Promo Codes',
			'description' => 'Add or remove codes for discounts on badges for attendees.'
		),
		array(
			'id' => 'attendee-blacklist',
			'name' => 'Attendee Blacklist',
			'description' => 'Block certain people from being able to register as attendees.'
		),
		array(
			'id' => 'attendee-mail',
			'name' => 'Attendee Form Letters',
			'description' => 'Write form letters to be emailed to attendees.'
		),
		array(
			'id' => 'attendee-csv',
			'name' => 'Attendee CSV',
			'description' => 'Download a CSV file of attendee registration records.'
		),
	),
	array(
		array(
			'id' => 'staff-departments',
			'name' => 'Departments',
			'description' => 'Organize the departments and positions that make up the event staff org chart.'
		),
		array(
			'id' => 'staff-orgchart',
			'name' => 'Org Chart',
			'description' => 'View the event staff org chart.'
		),
		array(
			'id' => 'staff-maillist',
			'name' => 'Mailing Lists',
			'description' => 'Generate mailing list memberships based on staff applications and the org chart.'
		),
	),
	array(
		array(
			'id' => 'staff',
			'name' => 'Staff Applications',
			'description' => 'View the list of staff applications.'
		),
		array(
			'id' => 'staff-view',
			'name' => 'Staff Applications - View',
			'description' => 'View individual staff applications.'
		),
		array(
			'id' => 'staff-review',
			'name' => 'Staff Applications - Review',
			'description' => 'Review and approve staff applications.'
		),
		array(
			'id' => 'staff-edit',
			'name' => 'Staff Applications - Edit',
			'description' => 'Modify staff applications.'
		),
		array(
			'id' => 'staff-delete',
			'name' => 'Staff Applications - Delete',
			'description' => 'Delete staff applications.'
		),
	),
	array(
		array(
			'id' => 'staff-badge-types',
			'name' => 'Staff Badge Types',
			'description' => 'Create or modify the types of badges available for staff members.'
		),
		array(
			'id' => 'staff-questions',
			'name' => 'Staff Questions',
			'description' => 'Add explanatory text and questions to the staff application form.'
		),
		array(
			'id' => 'staff-blacklist',
			'name' => 'Staff Blacklist',
			'description' => 'Inform people reviewing staff applications of certain people who should not be accepted.'
		),
		array(
			'id' => 'staff-mail',
			'name' => 'Staff Form Letters',
			'description' => 'Write form letters to be emailed to staff members.'
		),
		array(
			'id' => 'staff-csv',
			'name' => 'Staff CSV',
			'description' => 'Download a CSV file of staff application records.'
		),
	),
	array(
		array(
			'id' => 'admin-users',
			'name' => 'Admin Users',
			'description' => 'Manage CONcrescent administrators and their permissions.'
		),
	),
	array(
		array(
			'id' => '*',
			'name' => 'ALL',
			'description' => 'Grant all possible permissions to this user.'
		),
	),
);
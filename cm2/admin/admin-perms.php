<?php

$cm_admin_perms = array(
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
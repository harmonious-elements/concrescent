<?php

/* PHP error reporting. In production, this should be 0. In testing, this may be -1. */
error_reporting(0);

/* Time zone PHP should use for date calculations (e.g. when badges are available). */
date_default_timezone_set('America/Los_Angeles');

/* This is the default configuration for CONcrescent. Replace all values in this file. */
$cm_config = array(

	/* Database Configuration */
	'database' => array(

		/* Host name or IP address of the MySQL server. Typically 'localhost' or '127.0.0.1'. */
		'host' => 'localhost',

		/* MySQL user name. */
		'username' => 'cm_user',

		/* MySQL user password. */
		'password' => 'cm_pass',

		/* Name of the MySQL database to use for this application. */
		'database' => 'cm_db',

		/* A string to prepend to MySQL table names for this application. */
		'prefix' => 'cm_',

		/* Time zone MySQL should use for date calculations (e.g. when badges are available). */
		'timezone' => 'SYSTEM',

	),

	/* PayPal Configuration */
	'paypal' => array(

		/* URL of the PayPal API server.
		   In production, this is 'api.paypal.com'.
		   In testing, this is 'api.sandbox.paypal.com'. */
		'api_url' => 'api.sandbox.paypal.com',

		/* The Client ID from your PayPal app's REST API credentials. */
		'client_id' => '',

		/* The Secret from your PayPal app's REST API credentials. */
		'secret' => '',

		/* The currency code for all monetary amounts. */
		'currency' => 'USD',

	),

	/* Slack Integration Configuration */
	'slack' => array(

		/* Slack notification hooks. */
		'hook_url' => array(

			/* Notification hook for staff application submission and approval. */
			'staff' => '',

		),

	),

	/* Event Configuration */
	'event' => array(

		/* The name of the event. */
		'name' => 'CONcrescent Test Event',

		/* The first date requiring availability of staff members, in YYYY-MM-DD format. */
		'staff_start_date' => '2015-12-31',

		/* The first date of the event, in YYYY-MM-DD format. */
		'start_date' => '2015-12-31',

		/* The last date of the event, in YYYY-MM-DD format. */
		'end_date' => '2015-12-31',

		/* The last date requiring availability of staff members, in YYYY-MM-DD format. */
		'staff_end_date' => '2015-12-31',

	),

	/* Badge Printing Configuration */
	'badge_printing' => array(

		/* The size of the image to be sent to the badge printer. */
		'width' => '324px',
		'height' => '204px',
		'vertical' => false,

		/* Any external stylesheets to load. */
		'stylesheet' => array(),

	),

	/* Default Admin User Configuration */
	'default_admin' => array(

		/* Real name of the default admin user, which is created if no users exist. */
		'name' => 'Administrator',

		/* User name of the default admin user, which is created if no users exist. */
		'username' => 'admin',

		/* Password for the default admin user, which is created if no users exist. */
		'password' => 'password',

	),

	/* Theme Configuration */
	'theme' => array(

		/* Location of the theme directory. */
		'location' => 'themes/luna',

	),

);
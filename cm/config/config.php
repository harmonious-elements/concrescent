<?php

/*
	This is the default configuration file for CONcrescent.
	Replace all values below with values appropriate
	for your web server, PayPal account, and event.
*/

/*
	PHP error reporting.
	In testing, this should be -1.
	In production, this should be 0.
*/
error_reporting(0);

/* Time zone PHP should use for date calculations (e.g. when badges are available). */
date_default_timezone_set('America/Los_Angeles');

/*========================*/
/* Database Configuration */
/*========================*/

/* Host name or IP address of the MySQL server. Typically 'localhost' or '127.0.0.1'. */
$db_host = '127.0.0.1';

/* MySQL user name. */
$db_username = '';

/* MySQL user password. */
$db_password = '';

/* Name of the MySQL database to use for this application. */
$db_name = '';

/* A string to prepend to MySQL table names for this application. */
$db_table_prefix = 'cm_';

/* Time zone MySQL should use for date calculations (e.g. when badges are available). */
$db_time_zone = 'SYSTEM';

/*======================*/
/* PayPal Configuration */
/*======================*/

/*
	URL of the PayPal API server.
	In testing, this is 'api.sandbox.paypal.com'.
	In production, this is 'api.paypal.com'.
*/
$paypal_api_url = 'api.sandbox.paypal.com';

/* The Client ID from your PayPal app's REST API credentials. */
$paypal_client_id = '';

/* The Secret from your PayPal app's REST API credentials. */
$paypal_secret = '';

/* The currency code for all monetary amounts. */
$paypal_currency = 'USD';

/*=====================*/
/* Event Configuration */
/*=====================*/

/* The name of the event. */
$event_name = 'CONCRESCENT TEST EVENT';

/* The staff start date, in YYYY-MM-DD format. */
$event_date_start_staff = '2015-12-31';

/* The start date of the event, in YYYY-MM-DD format. */
$event_date_start = '2015-12-31';

/* The end date of the event, in YYYY-MM-DD format. */
$event_date_end = '2015-12-31';

/* The staff end date, in YYYY-MM-DD format. */
$event_date_end_staff = '2015-12-31';

/*==============================*/
/* Badge Printing Configuration */
/*==============================*/

/* The size of the image to be sent to the badge printer. */
$badge_printing_width = '324px';
$badge_printing_height = '204px';
$badge_printing_vertical = false;

/* An external stylesheet to load if you wish to use a webfont. */
$badge_printing_external_stylesheet = '';

/*=====================*/
/* Theme Configuration */
/*=====================*/

/* Location of the theme directory. */
$theme_base = 'themes/luna';
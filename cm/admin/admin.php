<?php

session_name('PHPSESSID_CMADMIN');
session_start();
require_once dirname(__FILE__).'/../lib/common.php';
require_once dirname(__FILE__).'/../lib/admin.php';
require_once dirname(__FILE__).'/../lib/attendees.php';
require_once dirname(__FILE__).'/../lib/booths.php';
require_once dirname(__FILE__).'/../lib/eventlets.php';
require_once dirname(__FILE__).'/../lib/guests.php';
require_once dirname(__FILE__).'/../lib/staffers.php';
require_once theme_file_path('admin.php');

$conn = get_db_connection();
$admin_user = admin_logged_in($conn);
if (!$admin_user) {
	header('Location: login.php?page=' . urlencode($_SERVER['REQUEST_URI']));
	exit(0);
}

$admin_links = array(
	array (
		'href' => 'attendee_overview.php',
		'text' => 'Attendee Overview',
		'related' => array(),
		'description' => 'Get an overview of attendees registered and checked in.',
	),
	array(
		'href' => 'attendee_badges.php',
		'text' => 'Attendee Badges',
		'related' => array(),
		'description' => 'Create or modify the types of badges available for attendees.',
	),
	array(
		'href' => 'attendee_extension_questions.php',
		'text' => 'Attendee Extra Questions',
		'related' => array(),
		'description' => 'Add additional questions to the attendee registration form.',
	),
	array(
		'href' => 'promo_codes.php',
		'text' => 'Promo Codes',
		'related' => array(),
		'description' => 'Add or remove codes for discounts on badges for attendees.',
	),
	array(
		'href' => 'attendee_blacklist.php',
		'text' => 'Attendee Blacklist',
		'related' => array(),
		'description' => 'Block certain people from being able to register as attendees.',
	),
	array(
		'href' => 'attendee_email.php',
		'text' => 'Attendee Form Letters',
		'related' => array(),
		'description' => 'Write form letters to be emailed to attendees.',
	),
	array(
		'href' => 'attendees.php',
		'text' => 'Edit Attendees',
		'related' => array('attendee.php'),
		'description' => 'View and modify attendee registration records.',
	),
	array(
		'href' => 'attendees_download.php',
		'text' => 'Download Attendees CSV',
		'related' => array(),
		'description' => 'Download a CSV file of attendee registration records.',
	),
	array (
		'---' => '---',
	),
	array(
		'href' => 'review_eventlets.php',
		'text' => 'Review Panel/Activity Apps',
		'related' => array('review_eventlet.php'),
		'description' => 'Review and approve panel and activity applications.',
	),
	array(
		'href' => 'eventlet_badges.php',
		'text' => 'Panel/Activity Types',
		'related' => array(),
		'description' => 'Create or modify the types of panels and activities available for panel and activity applicants.',
	),
	array(
		'href' => 'eventlet_extension_questions.php',
		'text' => 'Panel/Activity Questions',
		'related' => array(),
		'description' => 'Add additional questions to the panel and activity application form.',
	),
	array(
		'href' => 'eventlet_email.php',
		'text' => 'Panel/Activity Form Letters',
		'related' => array(),
		'description' => 'Write form letters to be emailed to panel and activity applicants.',
	),
	array(
		'href' => 'eventlets.php',
		'text' => 'Edit Panel/Activity Apps',
		'related' => array('eventlet.php'),
		'description' => 'View and modify panel and activity application records.',
	),
	array(
		'href' => 'eventlet_staffers.php',
		'text' => 'Edit Panelists/Hosts',
		'related' => array('eventlet_staffer.php'),
		'description' => 'View and modify panelist and host registration records.',
	),
	array(
		'href' => 'eventlets_download.php',
		'text' => 'Download Panels/Acts CSV',
		'related' => array(),
		'description' => 'Download a CSV file of panel and activity application or panelist and host registration records.',
	),
	array (
		'---' => '---',
	),
	array(
		'href' => 'review_booths.php',
		'text' => 'Review Table Applications',
		'related' => array('review_booth.php'),
		'description' => 'Review and approve table applications.',
	),
	array(
		'href' => 'booth_tables.php',
		'text' => 'Table Floor Plan',
		'related' => array('booth_table_map.php'),
		'description' => 'Upload an event floor plan and tag tables with their identifiers.',
	),
	array(
		'href' => 'booth_badges.php',
		'text' => 'Table Types',
		'related' => array(),
		'description' => 'Create or modify the types of tables available for table applicants.',
	),
	array(
		'href' => 'booth_extension_questions.php',
		'text' => 'Table Extra Questions',
		'related' => array(),
		'description' => 'Add additional questions to the table application form.',
	),
	array(
		'href' => 'booth_email.php',
		'text' => 'Table Form Letters',
		'related' => array(),
		'description' => 'Write form letters to be emailed to table applicants.',
	),
	array(
		'href' => 'booths.php',
		'text' => 'Edit Table Applications',
		'related' => array('booth.php'),
		'description' => 'View and modify table application records.',
	),
	array(
		'href' => 'booth_staffers.php',
		'text' => 'Edit Table Staffers',
		'related' => array('booth_staffer.php'),
		'description' => 'View and modify table staffer registration records.',
	),
	array(
		'href' => 'booths_download.php',
		'text' => 'Download Tables CSV',
		'related' => array(),
		'description' => 'Download a CSV file of table application or table staffer records.',
	),
	array (
		'---' => '---',
	),
	array(
		'href' => 'review_guests.php',
		'text' => 'Review Guest Applications',
		'related' => array('review_guest.php'),
		'description' => 'Review and approve guest applications.',
	),
	array(
		'href' => 'guest_badges.php',
		'text' => 'Guest Badges',
		'related' => array(),
		'description' => 'Create or modify the types of badges available for guest applicants.',
	),
	array(
		'href' => 'guest_extension_questions.php',
		'text' => 'Guest Extra Questions',
		'related' => array(),
		'description' => 'Add additional questions to the guest application form.',
	),
	array(
		'href' => 'guest_email.php',
		'text' => 'Guest Form Letters',
		'related' => array(),
		'description' => 'Write form letters to be emailed to guest applicants.',
	),
	array(
		'href' => 'guests.php',
		'text' => 'Edit Guest Applications',
		'related' => array('guest.php'),
		'description' => 'View and modify guest application records.',
	),
	array(
		'href' => 'guest_supporters.php',
		'text' => 'Edit Guests/Supporters',
		'related' => array('guest_supporter.php'),
		'description' => 'View and modify guest and supporter registration records.',
	),
	array(
		'href' => 'guests_download.php',
		'text' => 'Download Guests CSV',
		'related' => array(),
		'description' => 'Download a CSV file of guest application or guest and supporter registration records.',
	),
	array (
		'---' => '---',
	),
	array(
		'href' => 'review_staffers.php',
		'text' => 'Review Staff Applications',
		'related' => array('review_staffer.php'),
		'description' => 'Review and approve staff applications.',
	),
	array(
		'href' => 'staffer_badges.php',
		'text' => 'Staff Badges',
		'related' => array(),
		'description' => 'Create or modify the types of badges available for staff members.',
	),
	array(
		'href' => 'staffer_extension_questions.php',
		'text' => 'Staff Extra Questions',
		'related' => array(),
		'description' => 'Add additional questions to the staff application form.',
	),
	array(
		'href' => 'staffer_email.php',
		'text' => 'Staff Form Letters',
		'related' => array(),
		'description' => 'Write form letters to be emailed to staff members.',
	),
	array(
		'href' => 'staffers.php',
		'text' => 'Edit Staff Applications',
		'related' => array('staffer.php'),
		'description' => 'View and modify staff application records.',
	),
	array(
		'href' => 'staffers_download.php',
		'text' => 'Download Staffers CSV',
		'related' => array(),
		'description' => 'Download a CSV file of staff application records.',
	),
	array(
		'---' => '---',
	),
	array(
		'href' => 'badge_checkin.php',
		'text' => 'Badge Check-In',
		'related' => array('badge_print.php'),
		'description' => 'Check in people at Registration.',
	),
	array(
		'href' => 'badge_artwork.php',
		'text' => 'Badge Artwork',
		'related' => array('badge_artwork_edit.php'),
		'description' => 'Upload badge artwork and add fields for names and ID numbers.',
	),
	array(
		'href' => 'badge_preprinting.php',
		'text' => 'Badge Pre-Printing',
		'related' => array('badge_print.php'),
		'description' => 'Print badges covered by CONcrescent by badge type.',
	),
	array(
		'href' => 'badge_oneoffprinting.php',
		'text' => 'One-Off Badge Printing',
		'related' => array('badge_print.php'),
		'description' => 'Print one-off badges for purposes not covered by CONcrescent.',
	),
	array(
		'href' => 'badge_printing_setup.php',
		'text' => 'Badge Printing Setup',
		'related' => array(),
		'description' => 'Change badge size and other settings for badge printing.',
	),
	array(
		'---' => '---',
	),
	array(
		'href' => 'payments.php',
		'text' => 'Payments',
		'related' => array(),
		'description' => 'Request payments from individuals for reasons not otherwise covered.',
	),
	array(
		'href' => 'payment_email.php',
		'text' => 'Payment Form Letters',
		'related' => array(),
		'description' => 'Write form letters to be emailed to request payment.',
	),
	array(
		'href' => 'payments_download.php',
		'text' => 'Download Payments CSV',
		'related' => array(),
		'description' => 'Download a CSV file of requested payment records.',
	),
	array (
		'---' => '---',
	),
	array(
		'href' => 'admin_users.php',
		'text' => 'Admin Users',
		'related' => array(),
		'description' => 'Manage CONcrescent administrators and their permissions.',
	),
);

$page_url = get_page_filename();
$page_authorized = ($page_url == 'index.php');
$admin_links_authorized = array();
foreach ($admin_links as $admin_link) {
	if (isset($admin_link['---'])) {
		$c = count($admin_links_authorized);
		if ($c && !isset($admin_links_authorized[$c - 1]['---'])) {
			$admin_links_authorized[] = $admin_link;
		}
	} else {
		if (admin_has_permission($admin_user, $admin_link['href'])) {
			$admin_links_authorized[] = $admin_link;
			if ($page_url == $admin_link['href'] || in_array($page_url, $admin_link['related'])) {
				$page_authorized = true;
			}
		}
	}
};
$c = count($admin_links_authorized);
if ($c && isset($admin_links_authorized[$c - 1]['---'])) {
	unset($admin_links_authorized[$c - 1]);
}

if (!$page_authorized) {
	render_head('Unauthorized');
	render_body('Unauthorized', $admin_links_authorized, $admin_user);
	echo '<div class="card">';
		echo '<div class="card-content">';
			echo '<p>You do not have permission to view this page.</p>';
		echo '</div>';
	echo '</div>';
	render_dialogs();
	render_tail();
	exit(0);
}

function render_admin_head($title) {
	render_head($title);
	echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('jquery.js')) . '"></script>';
	echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('cmui.js')) . '"></script>';
}

function render_admin_body($title) {
	global $admin_links_authorized, $admin_user;
	render_body($title, $admin_links_authorized, $admin_user);
}

function render_admin_dialogs() {
	render_dialogs();
}

function render_admin_tail() {
	render_tail();
}

function date_range_string($start_date, $end_date) {
	if ($start_date && $end_date) {
		return htmlspecialchars($start_date).' &mdash; '.htmlspecialchars($end_date);
	} else if ($start_date) {
		return 'starting '.htmlspecialchars($start_date);
	} else if ($end_date) {
		return 'ending '.htmlspecialchars($end_date);
	} else {
		return 'forever';
	}
}

function age_range_string($min_age, $max_age) {
	if ($min_age && $max_age) {
		return (int)$min_age.' &mdash; '.(int)$max_age;
	} else if ($min_age) {
		return (int)$min_age.' and over';
	} else if ($max_age) {
		return (int)$max_age.' and under';
	} else {
		return 'all ages';
	}
}
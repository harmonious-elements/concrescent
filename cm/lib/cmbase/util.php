<?php

require_once dirname(__FILE__).'/../base/util.php';

function get_base_url() {
	return preg_replace('/\\/(admin|apply-[a-z]+|config|lib|payment|registration|themes)\\/.*$/', '/', get_page_url());
}

function payment_status_string($payment_status) {
	switch ($payment_status) {
		case 'Incomplete': return 'Incomplete'; break;
		case 'Cancelled': return 'Cancelled Payment'; break;
		case 'Completed': return 'Completed'; break;
		case 'Refunded': return 'Refunded'; break;
		case 'Pulled': return 'Badge Pulled'; break;
		default: return $payment_status;
	}
}

function payment_status_html($payment_status) {
	return '<span class="payment-status payment-status-'
	     . htmlspecialchars(strtolower($payment_status)).'">'
	     . htmlspecialchars(payment_status_string($payment_status))
	     . '</span>';
}

function application_status_string($application_status) {
	switch ($application_status) {
		case 'Submitted': return 'Submitted'; break;
		case 'Accepted': return 'Accepted'; break;
		case 'Maybe': return 'Waitlisted'; break;
		case 'Rejected': return 'Rejected'; break;
		case 'Cancelled': return 'Cancelled by Applicant'; break;
		case 'Pulled': return 'Badge Pulled'; break;
		default: return $application_status; break;
	}
}

function application_status_html($application_status) {
	return '<span class="application-status application-status-'
	     . htmlspecialchars(strtolower($application_status)).'">'
	     . htmlspecialchars(application_status_string($application_status))
	     . '</span>';
}

function contract_status_string($contract_status) {
	switch ($contract_status) {
		case 'Incomplete': return 'Incomplete'; break;
		case 'Cancelled': return 'Cancelled by Applicant'; break;
		case 'Completed': return 'Completed'; break;
		case 'Refunded': return 'Refunded'; break;
		case 'Pulled': return 'Badge Pulled'; break;
		default: return $contract_status; break;
	}
}

function contract_status_html($contract_status) {
	return '<span class="contract-status contract-status-'
	     . htmlspecialchars(strtolower($contract_status)).'">'
	     . htmlspecialchars(contract_status_string($contract_status))
	     . '</span>';
}
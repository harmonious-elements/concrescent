checkInInProgress = false;
checkInBadgeHolderType = null;
checkInBadgeHolderId = 0;

checkInAjax = function(req, callback) {
	jQuery.post('badge_checkin.php', req, function(r) {
		$('.checkin-state').addClass('hidden');
		var dlg = $('.checkin-state.' + r['next_state']);
		if (r['form_values']) {
			for (var key in r['form_values']) {
				var val = r['form_values'][key];
				dlg.find('#' + key).val(val);
				dlg.find('#' + key + '_o').val(val);
				if (key == 'fandom_name') onFandomNameChange();
			}
		}
		if (callback) callback(r, dlg);
		dlg.removeClass('hidden');
		if (!!r['finished']) {
			checkInInProgress = false;
			checkInBadgeHolderType = null;
			checkInBadgeHolderId = 0;
		}
	}, 'json');
};

startCheckIn = function(t, id) {
	if (checkInInProgress) {
		if (!window.confirm('The current checkin has not been finished. Are you sure you want to start a new one anyway?')) {
			return;
		}
	}
	checkInInProgress = true;
	checkInBadgeHolderType = t;
	checkInBadgeHolderId = id;
	checkInAjax({
		'action': 'startCheckIn',
		't': t,
		'id': id
	});
};

checkInAgain = function() {
	if (!checkInInProgress) return;
	checkInAjax({
		'action': 'startCheckIn',
		't': checkInBadgeHolderType,
		'id': checkInBadgeHolderId,
		'force_checkin': 1
	});
};

paymentCollected = function() {
	if (!checkInInProgress) return;
	checkInAjax({
		'action': 'paymentCollected',
		't': checkInBadgeHolderType,
		'id': checkInBadgeHolderId,
		'badge_id': $('#badge_id').val()
	});
};

infoVerified = function() {
	if (!checkInInProgress) return;
	var firstName = $('#first_name').val();
	var lastName = $('#last_name').val();
	var fandomName = $('#fandom_name').val();
	var nameOnBadge = $('#name_on_badge').val();
	var dateOfBirth = $('#date_of_birth').val();
	if (!firstName) { window.alert('First name is required.'); return; }
	if (!lastName) { window.alert('Last name is required.'); return; }
	if (!dateOfBirth) { window.alert('Date of birth is required.'); return; }
	var changed = (
		firstName != $('#first_name_o').val() ||
		lastName != $('#last_name_o').val() ||
		fandomName != $('#fandom_name_o').val() ||
		nameOnBadge != $('#name_on_badge_o').val() ||
		dateOfBirth != $('#date_of_birth_o').val()
	);
	checkInAjax({
		'action': 'infoVerified',
		't': checkInBadgeHolderType,
		'id': checkInBadgeHolderId,
		'first_name': firstName,
		'last_name': lastName,
		'fandom_name': fandomName,
		'name_on_badge': nameOnBadge,
		'date_of_birth': dateOfBirth,
		'changed': (changed ? 1 : 0)
	}, setBadgeArtwork);
};

printAgain = function() {
	if (!checkInInProgress) return;
	checkInAjax({
		'action': 'infoVerified',
		't': checkInBadgeHolderType,
		'id': checkInBadgeHolderId,
		'force_print': 1
	}, setBadgeArtwork);
};

startNewAttendee = function() {
	if (checkInInProgress) {
		if (!window.confirm('The current checkin has not been finished. Are you sure you want to start a new one anyway?')) {
			return;
		}
	}
	checkInInProgress = true;
	checkInBadgeHolderType = 'a';
	checkInBadgeHolderId = 0;
	$('.checkin-state').addClass('hidden');
	var dlg = $('.checkin-state.new-attendee');
	dlg.find('#first_name_n').val('');
	dlg.find('#last_name_n').val('');
	dlg.find('#fandom_name_n').val('');
	onFandomNameChangeN();
	dlg.find('#name_on_badge_n').val('');
	dlg.find('#date_of_birth_n').val('');
	dlg.find('#badge_id_n').val('');
	dlg.find('#email_address_n').val('');
	dlg.find('#phone_number_n').val('');
	dlg.removeClass('hidden');
};

newAttendeeCheckIn = function() {
	if (!checkInInProgress) return;
	var firstName = $('#first_name_n').val();
	var lastName = $('#last_name_n').val();
	var fandomName = $('#fandom_name_n').val();
	var nameOnBadge = $('#name_on_badge_n').val();
	var dateOfBirth = $('#date_of_birth_n').val();
	var badgeId = $('#badge_id_n').val();
	var emailAddress = $('#email_address_n').val();
	var phoneNumber = $('#phone_number_n').val();
	if (!firstName) { window.alert('First name is required.'); return; }
	if (!lastName) { window.alert('Last name is required.'); return; }
	if (!dateOfBirth) { window.alert('Date of birth is required.'); return; }
	if (!emailAddress) { window.alert('Email address is required.'); return; }
	if (!phoneNumber) { window.alert('Phone number is required.'); return; }
	checkInAjax({
		'action': 'newAttendeeCheckIn',
		'first_name': firstName,
		'last_name': lastName,
		'fandom_name': fandomName,
		'name_on_badge': nameOnBadge,
		'date_of_birth': dateOfBirth,
		'badge_id': badgeId,
		'email_address': emailAddress,
		'phone_number': phoneNumber
	}, setBadgeArtwork);
};

setBadgeArtwork = function(r, dlg) {
	if (r['badge_info']) {
		dlg.find('.badge-preprinted-type').text(r['badge_info']['badge_name']);
		dlg.find('.badge-preprinted-id').text(r['badge_info']['id_string']);
		dlg.find('.badge-preprinted-name').text(r['badge_info']['display_name']);
	}
	if (r['artwork_html']) {
		dlg.find('.badge-printing-artwork').html(r['artwork_html']);
	}
};

cancelCheckIn = function() {
	$('.checkin-state').addClass('hidden');
	checkInInProgress = false;
	checkInBadgeHolderType = null;
	checkInBadgeHolderId = 0;
};

onFandomNameChange = function() {
	var hasFandomName = !!$('#fandom_name').val();
	if (hasFandomName) {
		if ($('.tr-name-on-badge').hasClass('hidden')) {
			$('.tr-name-on-badge').removeClass('hidden');
			$('#name_on_badge').val('FandomReal');
		}
	} else {
		$('.tr-name-on-badge').addClass('hidden');
		$('#name_on_badge').val('RealOnly');
	}
};

onFandomNameChangeN = function() {
	var hasFandomNameN = !!$('#fandom_name_n').val();
	if (hasFandomNameN) {
		if ($('.tr-name-on-badge-n').hasClass('hidden')) {
			$('.tr-name-on-badge-n').removeClass('hidden');
			$('#name_on_badge_n').val('FandomReal');
		}
	} else {
		$('.tr-name-on-badge-n').addClass('hidden');
		$('#name_on_badge_n').val('RealOnly');
	}
};

$(document).ready(function() {
	$('#fandom_name').unbind('change').unbind('keydown').unbind('keyup');
	$('#fandom_name').bind('change', onFandomNameChange);
	$('#fandom_name').bind('keydown', onFandomNameChange);
	$('#fandom_name').bind('keyup', onFandomNameChange);
	onFandomNameChange();
	$('#fandom_name_n').unbind('change').unbind('keydown').unbind('keyup');
	$('#fandom_name_n').bind('change', onFandomNameChangeN);
	$('#fandom_name_n').bind('keydown', onFandomNameChangeN);
	$('#fandom_name_n').bind('keyup', onFandomNameChangeN);
	onFandomNameChangeN();
});
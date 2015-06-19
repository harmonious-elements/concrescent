$(document).ready(function() {
	var onFandomNameChange = function() {
		var hasFandomName = !!$('.input-fandom-name').val();
		if (hasFandomName) {
			if ($('.tr-name-on-badge').hasClass('hidden')) {
				$('.tr-name-on-badge').removeClass('hidden');
				$('.select-name-on-badge').val('FandomReal');
			}
		} else {
			$('.tr-name-on-badge').addClass('hidden');
			$('.select-name-on-badge').val('RealOnly');
		}
	};
	$('.input-fandom-name').unbind('change');
	$('.input-fandom-name').unbind('keydown');
	$('.input-fandom-name').unbind('keyup');
	$('.input-fandom-name').bind('change', onFandomNameChange);
	$('.input-fandom-name').bind('keydown', onFandomNameChange);
	$('.input-fandom-name').bind('keyup', onFandomNameChange);
	onFandomNameChange();
	
	var onBadgeIdChange = function() {
		var badgeId = 1 * $('.select-badge-id').val();
		var badge = badge_info[badgeId];
		if (badge['description']) {
			$('.td-badge-description').html(badge['description_html']);
			$('.tr-badge-description').removeClass('hidden');
		} else {
			$('.tr-badge-description').addClass('hidden');
		}
	};
	$('.select-badge-id').unbind('change');
	$('.select-badge-id').unbind('keydown');
	$('.select-badge-id').unbind('keyup');
	$('.select-badge-id').unbind('mousedown');
	$('.select-badge-id').unbind('mouseup');
	$('.select-badge-id').bind('change', onBadgeIdChange);
	$('.select-badge-id').bind('keydown', onBadgeIdChange);
	$('.select-badge-id').bind('keyup', onBadgeIdChange);
	$('.select-badge-id').bind('mousedown', onBadgeIdChange);
	$('.select-badge-id').bind('mouseup', onBadgeIdChange);
	onBadgeIdChange();
	
	var onDateOfBirthChange = function() {
		var dateOfBirth = $('.input-date-of-birth').val();
		var badgeId = 1 * $('.select-badge-id').val();
		$('.select-badge-id').empty();
		for (var i = 0; i < badge_ids.length; i++) {
			var id = badge_ids[i];
			var badge = badge_info[id];
			var minSat = (dateOfBirth && badge['min_birthdate']) ? (dateOfBirth >= badge['min_birthdate']) : true;
			var maxSat = (dateOfBirth && badge['max_birthdate']) ? (dateOfBirth <= badge['max_birthdate']) : true;
			if (minSat && maxSat) {
				var option = $('<option/>');
				option.attr('value', id);
				option.attr('selected', badgeId == id);
				option.text(badge['name'] + ' - ' + badge['price_string']);
				$('.select-badge-id').append(option);
			}
		}
		onBadgeIdChange();
	};
	$('.input-date-of-birth').unbind('change');
	$('.input-date-of-birth').unbind('keydown');
	$('.input-date-of-birth').unbind('keyup');
	$('.input-date-of-birth').unbind('mousedown');
	$('.input-date-of-birth').unbind('mouseup');
	$('.input-date-of-birth').bind('change', onDateOfBirthChange);
	$('.input-date-of-birth').bind('keydown', onDateOfBirthChange);
	$('.input-date-of-birth').bind('keyup', onDateOfBirthChange);
	$('.input-date-of-birth').bind('mousedown', onDateOfBirthChange);
	$('.input-date-of-birth').bind('mouseup', onDateOfBirthChange);
	onDateOfBirthChange();
});
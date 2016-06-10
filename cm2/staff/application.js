(function($,window,document,cmui,badgeTypes){
	$(document).ready(function() {
		var fandomName = $('#fandom-name');
		var fandomNameChanged = function() {
			if (fandomName.val()) {
				if ($('#name-on-badge-row').hasClass('hidden')) {
					$('#name-on-badge-row').removeClass('hidden');
					$('#name-on-badge').val('Fandom Name Large, Real Name Small');
				}
			} else {
				$('#name-on-badge-row').addClass('hidden');
				$('#name-on-badge').val('Real Name Only');
			}
		};
		fandomName.bind('change', fandomNameChanged);
		fandomName.bind('keydown', fandomNameChanged);
		fandomName.bind('keyup', fandomNameChanged);
		fandomNameChanged();

		var badgeType = $('#badge-type-id');
		var badgeTypeChanged = function() {
			var badgeId = 1 * badgeType.val();
			$('.cm-reg-inline-badge-type').addClass('hidden');
			$('#cm-reg-inline-badge-type-' + badgeId).removeClass('hidden');
			$('.cm-question-row').addClass('hidden');
			$('.cm-question-row-' + badgeId).removeClass('hidden');
			$('.cm-question-row-all').removeClass('hidden');
		};
		badgeType.bind('change', badgeTypeChanged);
		badgeType.bind('keydown', badgeTypeChanged);
		badgeType.bind('keyup', badgeTypeChanged);
		badgeType.bind('mousedown', badgeTypeChanged);
		badgeType.bind('mouseup', badgeTypeChanged);
		badgeTypeChanged();

		var dateOfBirth = $('#date-of-birth');
		var dateOfBirthOldVal = null;
		var dateOfBirthChanged = function() {
			var dateOfBirthNewVal = dateOfBirth.val();
			if (dateOfBirthNewVal != dateOfBirthOldVal) {
				dateOfBirthOldVal = dateOfBirthNewVal;
				var dob = cmui.formatDate(cmui.parseDate(dateOfBirthNewVal));
				var oldId = 1 * badgeType.val();
				badgeType.empty();
				for (var i = 0, n = badgeTypes.length; i < n; ++i) {
					var badge = badgeTypes[i];
					var minSat = (
						(dob && badge['min-birthdate']) ?
						(dob >= badge['min-birthdate']) : true
					);
					var maxSat = (
						(dob && badge['max-birthdate']) ?
						(dob <= badge['max-birthdate']) : true
					);
					if (minSat && maxSat) {
						var id = 1 * badge['id'];
						var name = badge['name'];
						var price = cmui.priceString(badge['price']);
						var option = $('<option/>');
						option.attr('value', id);
						option.attr('selected', id == oldId);
						option.text(name + ' \u2014 ' + price);
						badgeType.append(option);
					}
				}
				badgeTypeChanged();
			}
		};
		dateOfBirth.bind('change', dateOfBirthChanged);
		dateOfBirth.bind('keydown', dateOfBirthChanged);
		dateOfBirth.bind('keyup', dateOfBirthChanged);
		dateOfBirth.bind('mousedown', dateOfBirthChanged);
		dateOfBirth.bind('mouseup', dateOfBirthChanged);
		dateOfBirthChanged();

		$('body').bind('keydown', function(event) {
			if (event.which != 83) return;
			if (!event.shiftKey || !(event.ctrlKey || event.metaKey)) return;
			var e = $('input[type=submit]');
			if (e.length == 1) e.click();
			event.stopPropagation();
			event.preventDefault();
		});
	});
})(jQuery,window,document,cmui,cm_badge_type_info);
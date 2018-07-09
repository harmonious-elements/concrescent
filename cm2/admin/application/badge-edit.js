(function($,window,document,cmui){
	$(document).ready(function() {
		$('.attendee-select-button').bind('click', function(event) {
			cmui.showDialog('attendee-select');
			event.stopPropagation();
			event.preventDefault();
		});
		$('.attendee-clear-button').bind('click', function(event) {
			$('#attendee-id').val('');
			event.stopPropagation();
			event.preventDefault();
		});

		var addToBlacklistFields = $('.cm-add-to-blacklist-fields');
		var addToAttendeeBlacklist = $('input[type=checkbox][name=add-to-attendee-blacklist]');
		var addToApplicantBlacklist = $('input[type=checkbox][name=add-to-applicant-blacklist]');
		var addToBlacklistChanged = function() {
			var checked = (
				addToAttendeeBlacklist.is(':checked') ||
				addToApplicantBlacklist.is(':checked')
			);
			if (checked) addToBlacklistFields.removeClass('hidden');
			else addToBlacklistFields.addClass('hidden');
		};
		addToAttendeeBlacklist.bind('click', addToBlacklistChanged);
		addToApplicantBlacklist.bind('click', addToBlacklistChanged);

		$('.attendee-select-dialog .cancel-select-button').bind('click', cmui.hideDialog);

		$('body').bind('keydown', function(event) {
			if (!$('.dialog-cover').hasClass('hidden')) return;
			switch (event.which) {
				case 27:
					window.close();
					break;
				case 83:
					if (!event.shiftKey || !(event.ctrlKey || event.metaKey)) return;
					var e = $('input[type=submit]');
					if (e.length == 1) e.click();
					break;
				default:
					return;
			}
			event.stopPropagation();
			event.preventDefault();
		});
	});
})(jQuery,window,document,cmui);
(function($,window,document,cmui,listdef,badgeTypes,badgeArtwork){
	$(document).ready(function() {

		/* RPCs */

		var createAttendee = function(done) {
			cmui.showButterbar('Working...');
			var request = {
				'action': 'create-attendee',
				'first-name': $('#checkin-new-attendee-first-name').val(),
				'last-name': $('#checkin-new-attendee-last-name').val(),
				'fandom-name': $('#checkin-new-attendee-fandom-name').val(),
				'name-on-badge': $('#checkin-new-attendee-name-on-badge').val(),
				'date-of-birth': $('#checkin-new-attendee-date-of-birth').val(),
				'badge-type-id': $('#checkin-new-attendee-badge-type-id').val(),
				'email-address': $('#checkin-new-attendee-email-address').val(),
				'phone-number': $('#checkin-new-attendee-phone-number').val(),
				'notes': $('#checkin-new-attendee-notes').val()
			};
			$.post('checkin.php', request, function(response) {
				var errors = response['errors'];
				$('#checkin-new-attendee-first-name-error').text(errors && errors['first-name'] || '');
				$('#checkin-new-attendee-last-name-error').text(errors && errors['last-name'] || '');
				$('#checkin-new-attendee-fandom-name-error').text(errors && errors['fandom-name'] || '');
				$('#checkin-new-attendee-name-on-badge-error').text(errors && errors['name-on-badge'] || '');
				$('#checkin-new-attendee-date-of-birth-error').text(errors && errors['date-of-birth'] || '');
				$('#checkin-new-attendee-badge-type-id-error').text(errors && errors['badge-type-id'] || '');
				$('#checkin-new-attendee-email-address-error').text(errors && errors['email-address'] || '');
				$('#checkin-new-attendee-phone-number-error').text(errors && errors['phone-number'] || '');
				$('#checkin-new-attendee-notes-error').text(errors && errors['notes'] || '');
				cmui.hideButterbar();
				if (done) done(response);
			}, 'json');
		};
		var getBadgeHolder = function(context, contextId, done) {
			cmui.showButterbar('Working...');
			var request = {
				'action': 'get-badge-holder',
				'context': context,
				'context-id': contextId
			};
			$.post('checkin.php', request, function(response) {
				cmui.hideButterbar();
				if (done) done(response);
			}, 'json');
		};
		var completePayment = function(context, contextId, done) {
			cmui.showButterbar('Working...');
			var request = {
				'action': 'complete-payment',
				'context': context,
				'context-id': contextId,
				'badge-type-id': $('#checkin-payment-incomplete-badge-type-id').val()
			};
			$.post('checkin.php', request, function(response) {
				var errors = response['errors'];
				$('#checkin-payment-incomplete-badge-type-id-error').text(errors && errors['badge-type-id'] || '');
				cmui.hideButterbar();
				if (done) done(response);
			}, 'json');
		};
		var updateInfo = function(context, contextId, done) {
			cmui.showButterbar('Working...');
			var request = {
				'action': 'update-info',
				'context': context,
				'context-id': contextId,
				'first-name': $('#checkin-verify-info-first-name').val(),
				'last-name': $('#checkin-verify-info-last-name').val(),
				'fandom-name': $('#checkin-verify-info-fandom-name').val(),
				'name-on-badge': $('#checkin-verify-info-name-on-badge').val(),
				'date-of-birth': $('#checkin-verify-info-date-of-birth').val(),
				'notes': $('#checkin-verify-info-notes').val()
			};
			$.post('checkin.php', request, function(response) {
				var errors = response['errors'];
				$('#checkin-verify-info-first-name-error').text(errors && errors['first-name'] || '');
				$('#checkin-verify-info-last-name-error').text(errors && errors['last-name'] || '');
				$('#checkin-verify-info-fandom-name-error').text(errors && errors['fandom-name'] || '');
				$('#checkin-verify-info-name-on-badge-error').text(errors && errors['name-on-badge'] || '');
				$('#checkin-verify-info-date-of-birth-error').text(errors && errors['date-of-birth'] || '');
				$('#checkin-verify-info-notes-error').text(errors && errors['notes'] || '');
				cmui.hideButterbar();
				if (done) done(response);
			}, 'json');
		};
		var checkedIn = function(context, contextId, done) {
			cmui.showButterbar('Working...');
			var request = {
				'action': 'checked-in',
				'context': context,
				'context-id': contextId
			};
			$.post('checkin.php', request, function(response) {
				cmui.hideButterbar();
				if (done) done(response);
			}, 'json');
		};

		/* View */

		var hideAll = function() {
			$('.checkin-state').addClass('hidden');
			$('.checkin-exec-override').addClass('hidden');
		};
		var showNewAttendee = function() {
			hideAll();
			$('#checkin-new-attendee-first-name').val('');
			$('#checkin-new-attendee-first-name-error').text('');
			$('#checkin-new-attendee-last-name').val('');
			$('#checkin-new-attendee-last-name-error').text('');
			$('#checkin-new-attendee-fandom-name').val('');
			$('#checkin-new-attendee-fandom-name-error').text('');
			$('.checkin-new-attendee-name-on-badge-row').addClass('hidden');
			$('#checkin-new-attendee-name-on-badge').val('Real Name Only');
			$('#checkin-new-attendee-name-on-badge-error').text('');
			$('#checkin-new-attendee-date-of-birth').val('');
			$('#checkin-new-attendee-date-of-birth-error').text('');
			$('#checkin-new-attendee-badge-type-id').val('');
			$('#checkin-new-attendee-badge-type-id-error').text('');
			$('#checkin-new-attendee-email-address').val('');
			$('#checkin-new-attendee-email-address-error').text('');
			$('#checkin-new-attendee-phone-number').val('');
			$('#checkin-new-attendee-phone-number-error').text('');
			$('#checkin-new-attendee-notes').val('Added in-person at the event.');
			$('#checkin-new-attendee-notes-error').text('');
			$('.checkin-state-new-attendee').removeClass('hidden');
		};
		var showAlreadyCheckedIn = function() {
			hideAll();
			$('.checkin-state-already-checked-in').removeClass('hidden');
		};
		var showBadgeHolderBlacklisted = function(blacklisted) {
			hideAll();
			$('.checkin-state-badge-holder-blacklisted .cm-error-box').addClass('hidden');
			$('.checkin-state-badge-holder-blacklisted .cm-error-box .added-by').addClass('hidden');
			for (var key in blacklisted) {
				if (blacklisted.hasOwnProperty(key)) {
					$('.checkin-blacklisted-' + key).removeClass('hidden');
					if (blacklisted[key]['added-by']) {
						$('.checkin-blacklisted-' + key + ' .added-by').removeClass('hidden');
						$('.checkin-blacklisted-' + key + ' .added-by b').text(blacklisted[key]['added-by']);
					}
				}
			}
			$('.checkin-state-badge-holder-blacklisted').removeClass('hidden');
		};
		var showApplicationDenied = function() {
			hideAll();
			$('.checkin-state-application-denied').removeClass('hidden');
		};
		var showApplicationUnpaid = function() {
			hideAll();
			$('.checkin-state-application-unpaid').removeClass('hidden');
		};
		var showPaymentIncomplete = function(badgeTypeId) {
			hideAll();
			$('#checkin-payment-incomplete-badge-type-id').val(badgeTypeId);
			$('#checkin-payment-incomplete-badge-type-id-error').text('');
			$('.checkin-state-payment-incomplete').removeClass('hidden');
		};
		var showVerifyInfo = function(badgeContext, badgeContextId, holder, isNew) {
			hideAll();
			if (isNew) {
				$('.checkin-verify-info-row').addClass('hidden');
			} else {
				$('.checkin-verify-info-row').removeClass('hidden');
			}
			$('#checkin-verify-info-first-name').val(holder['first-name']);
			$('#checkin-verify-info-first-name-error').text('');
			$('#checkin-verify-info-last-name').val(holder['last-name']);
			$('#checkin-verify-info-last-name-error').text('');
			$('#checkin-verify-info-fandom-name').val(holder['fandom-name']);
			$('#checkin-verify-info-fandom-name-error').text('');
			if (holder['fandom-name']) {
				$('.checkin-verify-info-name-on-badge-row').removeClass('hidden');
			} else {
				$('.checkin-verify-info-name-on-badge-row').addClass('hidden');
			}
			$('#checkin-verify-info-name-on-badge').val(holder['name-on-badge']);
			$('#checkin-verify-info-name-on-badge-error').text('');
			$('#checkin-verify-info-date-of-birth').val(holder['date-of-birth']);
			$('#checkin-verify-info-date-of-birth-error').text('');
			$('#checkin-verify-info-notes').val(holder['notes']);
			$('#checkin-verify-info-notes-error').text('');
			$('.checkin-rewards-row').addClass('hidden');
			$('.checkin-rewards').empty();
			for (var i = 0, n = badgeTypes.length; i < n; ++i) {
				if (
					badgeTypes[i]['context'] == badgeContext &&
					badgeTypes[i]['context-id'] == badgeContextId &&
					badgeTypes[i]['rewards'] &&
					badgeTypes[i]['rewards'].length
				) {
					$('.checkin-rewards-row').removeClass('hidden');
					var rewards = badgeTypes[i]['rewards'];
					for (var j = 0, m = rewards.length; j < m; ++j) {
						var reward = cmui.safeHtmlString(rewards[j]);
						var li = $('<li/>').html(reward);
						$('.checkin-rewards').append(li);
					}
				}
			}
			$('.checkin-state-verify-info').removeClass('hidden');
			return (
				!$('.checkin-verify-info-row').hasClass('hidden') ||
				!$('.checkin-rewards-row').hasClass('hidden')
			);
		};
		var showBadgeAlreadyPrinted = function(holder) {
			hideAll();
			$('.checkin-preprinted-badge-type').text(holder['badge-type-name']);
			$('.checkin-preprinted-badge-id').text(holder['id-string']);
			$('.checkin-preprinted-badge-name').text(holder['display-name']);
			$('.checkin-state-badge-already-printed').removeClass('hidden');
		};
		var showBadgePrinting = function(badgeContext, badgeContextId, holderContext, holderContextId) {
			hideAll();
			$('.cm-badge-artwork').each(function() {
				var self = $(this);
				var key = 1 * self.attr('id').substring(8);
				var artwork = badgeArtwork[key];
				var map = artwork['map'];
				self.addClass('hidden');
				for (var i = 0, n = map.length; i < n; ++i) {
					if (map[i]['context'] == badgeContext && map[i]['context-id'] == badgeContextId) {
						self.removeClass('hidden');
					}
				}
				var name = encodeURIComponent(artwork['file-name']);
				var context = encodeURIComponent(holderContext);
				var contextId = encodeURIComponent(holderContextId);
				var href = 'print.php?a=' + name + '&c=' + context + '&i=' + contextId;
				self.attr('href', href);
			});
			$('.checkin-state-badge-printing').removeClass('hidden');
		};
		var showError = function() {
			hideAll();
			$('.checkin-state-error').removeClass('hidden');
		};
		var execOverride = function() {
			$('.checkin-exec-override').removeClass('hidden');
		};
		var confirmRestart = function(continueAction) {
			$('.cancel-restart-button').unbind('click').bind('click', function() {
				cmui.hideDialog();
			});
			$('.continue-restart-button').unbind('click').bind('click', function() {
				cmui.hideDialog();
				if (continueAction) continueAction();
			});
			cmui.showDialog('confirm-restart');
		};

		/* Controller */

		var inProgress = false;
		var isNew = false;
		var badgeContext = null;
		var badgeContextId = null;
		var holderContext = null;
		var holderContextId = null;
		var holder = null;
		var blacklisted = false;

		var checkInProgress = function(continueAction) {
			if (inProgress) confirmRestart(continueAction);
			else if (continueAction) continueAction();
		};

		var addNewAttendee = function() {
			checkInProgress(function() {
				inProgress = true;
				isNew = true;
				badgeContext = 'attendee';
				badgeContextId = null;
				holderContext = 'attendee';
				holderContextId = null;
				holder = null;
				blacklisted = false;
				showNewAttendee();
			});
		};
		$('.add-button').unbind('click').bind('click', addNewAttendee);

		var createNewAttendee = function() {
			createAttendee(function(response) {
				if (response['ok']) {
					var context = response['context'];
					var contextId = response['context-id'];
					getBadgeHolder(context, contextId, function(response) {
						if (response['ok']) {
							var context = response['context'];
							var contextId = response['context-id'];
							badgeContext = context.replace('applicant-', 'application-');
							badgeContextId = response['holder']['badge-type-id'];
							holderContext = context.replace('application-', 'applicant-');
							holderContextId = contextId;
							holder = response['holder'];
							blacklisted = response['blacklisted'];
							continueCheckIn();
						} else {
							showError();
						}
					});
				} else if (!response['errors']) {
					showError();
				}
			});
		};
		$('.checkin-new-attendee-button').bind('click', createNewAttendee);

		var selectBadgeHolder = function(id) {
			checkInProgress(function() {
				var o = id.lastIndexOf('-');
				var context = id.substring(0, o);
				var contextId = id.substring(o + 1);
				inProgress = true;
				isNew = false;
				badgeContext = context.replace('applicant-', 'application-');
				badgeContextId = null;
				holderContext = context.replace('application-', 'applicant-');
				holderContextId = null;
				holder = null;
				blacklisted = false;
				getBadgeHolder(context, contextId, function(response) {
					if (response['ok']) {
						var context = response['context'];
						var contextId = response['context-id'];
						badgeContext = context.replace('applicant-', 'application-');
						badgeContextId = response['holder']['badge-type-id'];
						holderContext = context.replace('application-', 'applicant-');
						holderContextId = contextId;
						holder = response['holder'];
						blacklisted = response['blacklisted'];
						continueCheckIn();
					} else {
						showError();
					}
				});
			});
		};
		listdef['select-function'] = selectBadgeHolder;

		var continueCheckIn = function() {
			if (holder['checkin-count']) {
				showAlreadyCheckedIn();
				return;
			}
			if (blacklisted) {
				showBadgeHolderBlacklisted(blacklisted);
				return;
			}
			if (holderContext != 'attendee' && holder['application-status'] != 'Accepted') {
				showApplicationDenied();
				return;
			}
			if (holderContext != 'attendee' && holder['payment-status'] != 'Completed') {
				showApplicationUnpaid();
				return;
			}
			if (holderContext == 'attendee' && holder['payment-status'] != 'Completed') {
				showPaymentIncomplete(badgeContextId);
				return;
			}
			if (!showVerifyInfo(badgeContext, badgeContextId, holder, isNew)) {
				checkedIn(holderContext, holderContextId, function() {
					if (response['ok']) completeCheckIn();
					else showError();
				});
			}
		};

		var skipCheckedIn = function() {
			holder['checkin-count'] = 0;
			holder['print-count'] = 0;
			continueCheckIn();
		};
		$('.checkin-skip-checkedin-button').bind('click', skipCheckedIn);

		var skipBlacklisted = function() {
			blacklisted = false;
			continueCheckIn();
		};
		$('.checkin-skip-blacklisted-button').bind('click', skipBlacklisted);

		var skipApplicationDenied = function() {
			holder['application-status'] = 'Accepted';
			continueCheckIn();
		};
		$('.checkin-skip-app-denied-button').bind('click', skipApplicationDenied);

		var skipApplicationUnpaid = function() {
			holder['payment-status'] = 'Completed';
			continueCheckIn();
		};
		$('.checkin-skip-app-unpaid-button').bind('click', skipApplicationUnpaid);

		var paymentCollected = function() {
			completePayment(holderContext, holderContextId, function(response) {
				if (response['ok']) {
					if ($('#checkin-payment-incomplete-badge-type-id').val() != badgeContextId) {
						badgeContextId = $('#checkin-payment-incomplete-badge-type-id').val();
						holder['print-count'] = 0;
					}
					if (!showVerifyInfo(badgeContext, badgeContextId, holder, isNew)) {
						checkedIn(holderContext, holderContextId, function() {
							if (response['ok']) completeCheckIn();
							else showError();
						});
					}
				} else if (!response['errors']) {
					showError();
				}
			});
		};
		$('.checkin-payment-collected-button').bind('click', paymentCollected);

		var infoVerified = function() {
			updateInfo(holderContext, holderContextId, function(response) {
				if (response['ok']) {
					if (
						$('#checkin-verify-info-first-name').val() != holder['first-name'] ||
						$('#checkin-verify-info-last-name').val() != holder['last-name'] ||
						$('#checkin-verify-info-fandom-name').val() != holder['fandom-name'] ||
						$('#checkin-verify-info-name-on-badge').val() != holder['name-on-badge'] ||
						$('#checkin-verify-info-date-of-birth').val() != holder['date-of-birth']
					) {
						holder['print-count'] = 0;
					}
					checkedIn(holderContext, holderContextId, function() {
						if (response['ok']) completeCheckIn();
						else showError();
					});
				} else if (!response['errors']) {
					showError();
				}
			});
		};
		$('.checkin-info-verified-button').bind('click', infoVerified);

		var completeCheckIn = function() {
			if (holder['print-count']) {
				showBadgeAlreadyPrinted(holder);
			} else {
				showBadgePrinting(
					badgeContext, badgeContextId,
					holderContext, holderContextId
				);
			}
		};

		var printAgain = function() {
			showBadgePrinting(
				badgeContext, badgeContextId,
				holderContext, holderContextId
			);
		};
		$('.checkin-print-again-button').bind('click', printAgain);

		var cancelCheckIn = function() {
			inProgress = false;
			isNew = false;
			badgeContext = null;
			badgeContextId = null;
			holderContext = null;
			holderContextId = null;
			holder = null;
			blacklisted = false;
			hideAll();
		};
		$('.checkin-cancel-button').bind('click', cancelCheckIn);

		/* Non-Check-In-Related */

		var newAttendeeFandomName = $('#checkin-new-attendee-fandom-name');
		var newAttendeeNameOnBadge = $('#checkin-new-attendee-name-on-badge');
		var newAttendeeNameOnBadgeRow = $('.checkin-new-attendee-name-on-badge-row');
		var newAttendeeFandomNameChanged = function() {
			if (newAttendeeFandomName.val()) {
				if (newAttendeeNameOnBadgeRow.hasClass('hidden')) {
					newAttendeeNameOnBadgeRow.removeClass('hidden');
					newAttendeeNameOnBadge.val('Fandom Name Large, Real Name Small');
				}
			} else {
				newAttendeeNameOnBadgeRow.addClass('hidden');
				newAttendeeNameOnBadge.val('Real Name Only');
			}
		};
		newAttendeeFandomName.bind('change', newAttendeeFandomNameChanged);
		newAttendeeFandomName.bind('keydown', newAttendeeFandomNameChanged);
		newAttendeeFandomName.bind('keyup', newAttendeeFandomNameChanged);

		var verifyInfoFandomName = $('#checkin-verify-info-fandom-name');
		var verifyInfoNameOnBadge = $('#checkin-verify-info-name-on-badge');
		var verifyInfoNameOnBadgeRow = $('.checkin-verify-info-name-on-badge-row');
		var verifyInfoFandomNameChanged = function() {
			if (verifyInfoFandomName.val()) {
				if (verifyInfoNameOnBadgeRow.hasClass('hidden')) {
					verifyInfoNameOnBadgeRow.removeClass('hidden');
					verifyInfoNameOnBadge.val('Fandom Name Large, Real Name Small');
				}
			} else {
				verifyInfoNameOnBadgeRow.addClass('hidden');
				verifyInfoNameOnBadge.val('Real Name Only');
			}
		};
		verifyInfoFandomName.bind('change', verifyInfoFandomNameChanged);
		verifyInfoFandomName.bind('keydown', verifyInfoFandomNameChanged);
		verifyInfoFandomName.bind('keyup', verifyInfoFandomNameChanged);

		$('body').bind('keydown', function(event) {
			if (!$('.dialog-cover').hasClass('hidden')) return;
			switch (event.which) {
				case 88:
					if (!event.shiftKey || !(event.ctrlKey || event.metaKey)) return;
					execOverride();
					break;
				default:
					return;
			}
			event.stopPropagation();
			event.preventDefault();
		});

		$(window).bind('beforeunload', function() {
			if (inProgress) {
				return 'The current check-in has not been finished.';
			}
		});

	});
})(jQuery,window,document,cmui,cm_list_def,cm_badge_type_info,cm_badge_artwork);
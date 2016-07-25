(function($,window,document,cmui,ctxInfo,badgeTypes){
	$(document).ready(function() {
		var prepFandomName = function() {
			$('tbody.applicant-rows').each(function(i) {
				var fandomName = $('#fandom-name-' + i);
				var fandomNameChanged = function() {
					if (fandomName.val()) {
						if ($('#name-on-badge-row-' + i).hasClass('hidden')) {
							$('#name-on-badge-row-' + i).removeClass('hidden');
							$('#name-on-badge-' + i).val('Fandom Name Large, Real Name Small');
						}
					} else {
						$('#name-on-badge-row-' + i).addClass('hidden');
						$('#name-on-badge-' + i).val('Real Name Only');
					}
				};
				fandomName.unbind('change').bind('change', fandomNameChanged);
				fandomName.unbind('keydown').bind('keydown', fandomNameChanged);
				fandomName.unbind('keyup').bind('keyup', fandomNameChanged);
				fandomNameChanged();
			});
		};
		prepFandomName();

		var setApplicantCount = function(count) {
			var i = count;
			var e = $('tbody.applicant-rows-' + i);
			while (e.length) {
				e.remove();
				i++;
				e = $('tbody.applicant-rows-' + i);
			}
			i = 0;
			e = $('tbody.applicant-rows-' + i);
			while (e.length) {
				i++;
				e = $('tbody.applicant-rows-' + i);
			}
			while (i < count) {
				var h = (
					ctxInfo['empty_applicant_template']
						.replace(/1/g, i+1)
						.replace(/0/g, i)
				);
				$('tbody.applicant-rows:last').after(h);
				i++;
			}
			prepFandomName();
		};
		var applicantCount = $('#applicant-count');
		var applicantCountChanged = function() {
			var count = 1 * applicantCount.val();
			if (count < 1) count = 1;
			setApplicantCount(count);
		};
		applicantCount.bind('change', applicantCountChanged);
		applicantCount.bind('keydown', applicantCountChanged);
		applicantCount.bind('keyup', applicantCountChanged);
		applicantCount.bind('mousedown', applicantCountChanged);
		applicantCount.bind('mouseup', applicantCountChanged);

		var badgeType = $('#badge-type-id');
		var badgeTypeChanged = function() {
			var badgeId = 1 * badgeType.val();
			$('.cm-reg-inline-badge-type').addClass('hidden');
			$('#cm-reg-inline-badge-type-' + badgeId).removeClass('hidden');
			$('.cm-question-row').addClass('hidden');
			$('.cm-question-row-' + badgeId).removeClass('hidden');
			$('.cm-question-row-all').removeClass('hidden');
			var badge = {};
			for (var i = 0, n = badgeTypes.length; i < n; ++i) {
				if (badgeTypes[i]['id'] == badgeId) {
					badge = badgeTypes[i];
				}
			}
			var assignmentCountRate = '';
			var applicantCountRate = '';
			if (badge['price-per-assignment']) {
				assignmentCountRate += ' @ $' + badge['price-per-assignment'] + ' each';
				if (badge['base-assignment-count']) {
					assignmentCountRate += ' (' + badge['base-assignment-count'] + ' included in base price)';
				}
			}
			if (badge['price-per-applicant']) {
				applicantCountRate += ' @ $' + badge['price-per-applicant'] + ' each';
				if (badge['base-applicant-count']) {
					applicantCountRate += ' (' + badge['base-applicant-count'] + ' included in ' + ctxInfo['assignment_term'][0].toLowerCase() + ' price)';
				}
			}
			if (badge['max-assignment-count']) {
				assignmentCountRate += ' (maximum ' + badge['max-assignment-count'] + ')';
				$('#assignment-count').attr('max', badge['max-assignment-count']);
				if (1 * $('#assignment-count').val() > badge['max-assignment-count']) {
					$('#assignment-count').val(badge['max-assignment-count']);
				}
			} else {
				$('#assignment-count').removeAttr('max');
			}
			if (badge['max-applicant-count']) {
				applicantCountRate += ' (maximum ' + badge['max-applicant-count'] + ')';
				applicantCount.attr('max', badge['max-applicant-count']);
				if (1 * applicantCount.val() > badge['max-applicant-count']) {
					applicantCount.val(badge['max-applicant-count']);
					applicantCountChanged();
				}
			} else {
				applicantCount.removeAttr('max');
			}
			if (assignmentCountRate) {
				$('.assignment-count-rate').text(assignmentCountRate);
				$('.assignment-count-rate').removeClass('hidden');
			} else {
				$('.assignment-count-rate').addClass('hidden');
			}
			if (applicantCountRate) {
				$('.applicant-count-rate').text(applicantCountRate);
				$('.applicant-count-rate').removeClass('hidden');
			} else {
				$('.applicant-count-rate').addClass('hidden');
			}
			if (badge['max-assignment-count'] == 1) {
				$('.assignment-count-row').addClass('hidden');
			} else {
				$('.assignment-count-row').removeClass('hidden');
			}
			if (badge['max-applicant-count'] == 1) {
				$('.applicant-count-row').addClass('hidden');
			} else {
				$('.applicant-count-row').removeClass('hidden');
			}
		};
		badgeType.bind('change', badgeTypeChanged);
		badgeType.bind('keydown', badgeTypeChanged);
		badgeType.bind('keyup', badgeTypeChanged);
		badgeType.bind('mousedown', badgeTypeChanged);
		badgeType.bind('mouseup', badgeTypeChanged);
		badgeTypeChanged();

		$('body').bind('keydown', function(event) {
			if (event.which != 83) return;
			if (!event.shiftKey || !(event.ctrlKey || event.metaKey)) return;
			var e = $('input[type=submit]');
			if (e.length == 1) e.click();
			event.stopPropagation();
			event.preventDefault();
		});
	});
})(jQuery,window,document,cmui,cm_app_context_info,cm_badge_type_info);
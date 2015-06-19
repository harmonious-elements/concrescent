$(document).ready(function() {
	var onBadgeIdChange = function() {
		var badgeId = 1 * $('.select-badge-id').val();
		var badge = badge_info[badgeId];
		if (badge['description']) {
			$('.td-badge-description').html(badge['description_html']);
			$('.tr-badge-description').removeClass('hidden');
		} else {
			$('.tr-badge-description').addClass('hidden');
		}
		rate_num_staffers = '';
		if (badge['price_per_staffer']) {
			rate_num_staffers += ' @ ' + badge['price_per_staffer_string'] + ' each';
			if (badge['staffers_in_eventlet_price']) {
				rate_num_staffers += ' (' + badge['staffers_in_eventlet_price'] + ' included per application)';
			}
		}
		if (badge['max_staffers']) {
			rate_num_staffers += ' (maximum ' + badge['max_staffers'] + ')';
			$('.epa-num-staffers').attr('max', badge['max_staffers']);
		} else {
			$('.epa-num-staffers').removeAttr('max');
		}
		$('.rate-num-staffers').text(rate_num_staffers);
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
	
	var prepNameOnBadgePopups = function() {
		$('tbody.sh-stf').each(function() {
			var f = $(this);
			var onFandomNameChange = function() {
				var hasFandomName = !!f.find('.input-fandom-name').val();
				if (hasFandomName) {
					if (f.find('.tr-name-on-badge').hasClass('hidden')) {
						f.find('.tr-name-on-badge').removeClass('hidden');
						f.find('.select-name-on-badge').val('FandomReal');
					}
				} else {
					f.find('.tr-name-on-badge').addClass('hidden');
					f.find('.select-name-on-badge').val('RealOnly');
				}
			};
			f.find('.input-fandom-name').unbind('change');
			f.find('.input-fandom-name').unbind('keydown');
			f.find('.input-fandom-name').unbind('keyup');
			f.find('.input-fandom-name').bind('change', onFandomNameChange);
			f.find('.input-fandom-name').bind('keydown', onFandomNameChange);
			f.find('.input-fandom-name').bind('keyup', onFandomNameChange);
			onFandomNameChange();
		});
	};
	prepNameOnBadgePopups();
	
	var setStafferCount = function(count) {
		var i = count;
		var e = $('.sh-stf-' + i);
		while (e.length) {
			e.remove();
			i++;
			e = $('.sh-stf-' + i);
		}
		i = 0;
		e = $('.sh-stf-' + i);
		while (e.length) {
			i++;
			e = $('.sh-stf-' + i);
		}
		var a = function(i, count) {
			if (i < count) {
				jQuery.ajax({
					'type': 'POST',
					'url': 'apply.php',
					'data': {'render_new_staffer_form': i},
					'async': false,
					'success': function(r) {
						$('.sh-stf:last').after(r);
						a(i + 1, count);
					}
				});
			} else {
				prepNameOnBadgePopups();
			}
		};
		a(i, count);
	};
	var onStafferCountChange = function() {
		var num_staffers = 1 * $('.epa-num-staffers').val();
		if (num_staffers < 1) num_staffers = 1;
		setStafferCount(num_staffers);
	};
	$('.epa-num-staffers').unbind('change');
	$('.epa-num-staffers').unbind('keydown');
	$('.epa-num-staffers').unbind('keyup');
	$('.epa-num-staffers').unbind('mousedown');
	$('.epa-num-staffers').unbind('mouseup');
	$('.epa-num-staffers').bind('change', onStafferCountChange);
	$('.epa-num-staffers').bind('keydown', onStafferCountChange);
	$('.epa-num-staffers').bind('keyup', onStafferCountChange);
	$('.epa-num-staffers').bind('mousedown', onStafferCountChange);
	$('.epa-num-staffers').bind('mouseup', onStafferCountChange);
});
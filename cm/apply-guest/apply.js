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
		if (badge['max_supporters']) {
			$('.rate-num-supporters').text(' (maximum ' + badge['max_supporters'] + ')');
			$('.epa-num-supporters').attr('max', badge['max_supporters']);
		} else {
			$('.rate-num-supporters').text('');
			$('.epa-num-supporters').removeAttr('max');
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
	
	var setSupporterCount = function(count) {
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
					'data': {'render_new_supporter_form': i},
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
	var onSupporterCountChange = function() {
		var num_supporters = 1 * $('.epa-num-supporters').val();
		if (num_supporters < 1) num_supporters = 1;
		setSupporterCount(num_supporters);
	};
	$('.epa-num-supporters').unbind('change');
	$('.epa-num-supporters').unbind('keydown');
	$('.epa-num-supporters').unbind('keyup');
	$('.epa-num-supporters').unbind('mousedown');
	$('.epa-num-supporters').unbind('mouseup');
	$('.epa-num-supporters').bind('change', onSupporterCountChange);
	$('.epa-num-supporters').bind('keydown', onSupporterCountChange);
	$('.epa-num-supporters').bind('keyup', onSupporterCountChange);
	$('.epa-num-supporters').bind('mousedown', onSupporterCountChange);
	$('.epa-num-supporters').bind('mouseup', onSupporterCountChange);
});
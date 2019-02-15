(function($,window,document,cmui,badgeTypes,addons){
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
		
		var addonsChanged = function() {
			var totalPrice = 0;
			var badgeType = $('input[name=badge-type-id]:checked');
			var badgeId = 1 * badgeType.attr('value');
			for (var i = 0, n = badgeTypes.length; i < n; i++) {
				var badgeType = badgeTypes[i];
				var checked = (badgeType['id'] == badgeId);
				var priceDiff = 1 * badgeType['price-diff'];
				if (checked && priceDiff) totalPrice += priceDiff;
			}
			for (var i = 0, n = addons.length; i < n; i++) {
				var addon = addons[i];
				var checked = $('#addon-' + addon['id']).is(':checked');
				var price = 1 * addon['price'];
				if (checked && price) totalPrice += price;
			}
			if (totalPrice) {
				$('#save-changes-card').addClass('hidden');
				$('#place-order-card').removeClass('hidden');
			} else {
				$('#save-changes-card').removeClass('hidden');
				$('#place-order-card').addClass('hidden');
			}
			$('.edit-order-total').text(cmui.priceString(totalPrice));
		};
		var addonCheckbox = $('.cm-reg-addon input');
		addonCheckbox.bind('click', addonsChanged);
		addonsChanged();

		var badgeTypeChanged = function() {
			var badgeType = $('input[name=badge-type-id]:checked');
			var badgeId = 1 * badgeType.attr('value');
			$('.cm-reg-addons').addClass('hidden');
			$('.cm-reg-addon').addClass('hidden');
			for (var i = 0, n = addons.length; i < n; i++) {
				var addon = addons[i];
				var badgeSat = (
					addon['badge-type-ids'].indexOf('*') >= 0 ||
					addon['badge-type-ids'].indexOf(badgeId) >= 0 ||
					addon['badge-type-ids'].indexOf(badgeId.toString()) >= 0
				);
				if (badgeSat) {
					$('.cm-reg-addons').removeClass('hidden');
					$('#cm-reg-addon-' + addon['id']).removeClass('hidden');
				} else {
					$('#addon-' + addon['id']).prop('checked', false);
				}
			}
			addonsChanged();
		};
		var badgeType = $('input[name=badge-type-id]');
		badgeType.bind('click', badgeTypeChanged);
		badgeTypeChanged();
	});
})(jQuery,window,document,cmui,cm_badge_type_info,cm_addon_info);
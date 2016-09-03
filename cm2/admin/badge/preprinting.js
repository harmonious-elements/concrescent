(function($,window,document,cmui,badgeArtwork){
	$(document).ready(function() {

		var loadBadgeHolder = function(badgeContext, badgeContextId, holderContext, holderContextId) {
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
		};

		var unloadBadgeHolder = function() {
			$('.cm-badge-artwork').addClass('hidden');
			$('.cm-badge-artwork').attr('href', '');
		};

		var loadBadgeType = function(badgeContext, badgeContextId, criteria) {
			cmui.showButterbar('Loading badge holders...');
			var query = ['&'];

			if (badgeContext.substring(0, 12) == 'application-') {
				query.push(['=', 'type', ['"', 'applicant']]);
				query.push(['=', 'app-ctx', ['"', badgeContext.substring(12)]]);
				query.push(['=', 'badge-type-id', ['"', badgeContextId]]);
			} else {
				query.push(['=', 'type', ['"', badgeContext]]);
				query.push(['=', 'badge-type-id', ['"', badgeContextId]]);
			}

			if (criteria == 'paid') {
				query.push(['=', 'payment-status', ['"', 'Completed']]);
			} else if (criteria == 'accepted' && badgeContext != 'attendee') {
				query.push(['=', 'application-status', ['"', 'Accepted']]);
			}

			var request = {
				'cm-list-action': 'list',
				'cm-list-search-query': JSON.stringify(query),
				'cm-list-sort-order': JSON.stringify([0]),
				'cm-list-page-offset': 0,
				'cm-list-page-length': 0
			};
			$.post('preprinting.php', request, function(data) {
				if (!data['ok']) {
					cmui.showButterbarPersistent('An error occurred. Please reload the page.');
				} else {
					var html = '';
					if (data['rows']) {
						for (var i = 0, n = data['rows'].length; i < n; ++i) {
							html += data['rows'][i]['html'];
						}
					}
					$('.badge-holders-tbody').html(html);

					if (data['time'] > 0) {
						var time = (Math.round(data['time'] * 1000) / 1000);
						var timeString = 'loaded in ' + time + ' seconds';
						$('th.td-actions').attr('title', timeString);
					}

					$('.badge-holders-tbody tr').each(function() {
						var row = $(this);
						var holderContextString = row.attr('id').substring(6);
						var o = holderContextString.lastIndexOf('-');
						var holderContext = holderContextString.substring(0, o);
						var holderContextId = holderContextString.substring(o + 1);
						row.find('.select-button').bind('click', function() {
							loadBadgeHolder(badgeContext, badgeContextId, holderContext, holderContextId);
						});
					});

					cmui.hideButterbar();
				}
			}, 'json');
		};

		var badgeTypeEvent = function() {
			var badgeType = $('input[name=badge-type]:checked');
			var badgeContext = badgeType.attr('data-context');
			var badgeContextId = badgeType.attr('data-context-id');
			var criteria = $('input[name=criteria]:checked').val();
			loadBadgeType(badgeContext, badgeContextId, criteria);
			unloadBadgeHolder();
		};

		$('input[name=badge-type]').bind('click', badgeTypeEvent);
		$('input[name=criteria]').bind('click', badgeTypeEvent);

	});
})(jQuery,window,document,cmui,cm_badge_artwork);
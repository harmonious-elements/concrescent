(function($,window,document,fieldKeys,badgeArtwork){
	$(document).ready(function() {
		var setPrintUrl = function(artworkName) {
			var name = encodeURIComponent(artworkName);
			var data = {};
			$('.badge-info-tbody input').each(function() {
				var self = $(this);
				var key = self.attr('name');
				var value = self.val();
				data[key] = value;
			});
			data['age'] = $('input[name=age]:checked').val();
			data = encodeURIComponent(JSON.stringify(data));
			var href = 'print.php?a=' + name + '&e=' + data;
			$('.print-button').attr('href', href);
		};
		var openEditor = function(artwork) {
			var eventHandler = function() {
				setPrintUrl(artwork['file-name']);
			};
			var eventBind = function(input) {
				input.bind('change', eventHandler);
				input.bind('keydown', eventHandler);
				input.bind('keyup', eventHandler);
			};
			var tbody = $('.badge-info-tbody').empty();
			if (artwork['fields'] && artwork['fields'].length) {
				for (var i = 0, n = artwork['fields'].length; i < n; ++i) {
					var field = artwork['fields'][i];
					var key = field['field-key'];
					var input = $('<input>').attr('type', 'text').attr('name', key);
					var td = $('<td/>').append(input);
					var th = $('<th/>').text((fieldKeys[key] || key) + ':');
					var tr = $('<tr/>').append(th).append(td);
					tbody.append(tr);
					eventBind(input);
				}
			}
			setPrintUrl(artwork['file-name']);
			$('.badge-info').removeClass('hidden');
			setTimeout(function() { $('.badge-info-tbody input:first').focus(); }, 100);
		};
		var closeEditor = function() {
			$('.badge-info-tbody').empty();
			$('.print-button').attr('href', '');
			$('.badge-info').addClass('hidden');
		};

		$('.cm-badge-artwork').each(function() {
			var self = $(this);
			var key = 1 * self.attr('id').substring(8);
			var artwork = badgeArtwork[key];
			self.bind('mousedown', function() {
				$('.cm-badge-artwork').removeClass('active');
				self.addClass('active');
				openEditor(artwork);
			});
		});
		$('.cancel-button').bind('click', function() {
			$('.cm-badge-artwork').removeClass('active');
			closeEditor();
		});

		var artworkPrev = function() {
			var prev = $('.cm-badge-artwork.active').prev('.cm-badge-artwork');
			if (!prev.length) prev = $('.cm-badge-artwork:last');
			prev.mousedown();
		};
		var artworkNext = function() {
			var next = $('.cm-badge-artwork.active').next('.cm-badge-artwork');
			if (!next.length) next = $('.cm-badge-artwork:first');
			next.mousedown();
		};

		$('body').bind('keydown', function(event) {
			if (!$('.dialog-cover').hasClass('hidden')) return;
			switch (event.which) {
				case 27:
					$('.cancel-button').click();
					break;
				case 37: case 38:
					if (cmui.focusedOnInput() && (!event.shiftKey || !(event.ctrlKey || event.metaKey))) return;
					artworkPrev();
					break;
				case 39: case 40:
					if (cmui.focusedOnInput() && (!event.shiftKey || !(event.ctrlKey || event.metaKey))) return;
					artworkNext();
					break;
				case 80:
					if (!event.shiftKey || !(event.ctrlKey || event.metaKey)) return;
					$('.print-button')[0].click();
					break;
				case 191:
					if (!event.shiftKey || !(event.ctrlKey || event.metaKey)) return;
					cmui.showDialog('shortcuts');
					break;
				default:
					return;
			}
			event.stopPropagation();
			event.preventDefault();
		});
	});
})(jQuery,window,document,cm_badge_artwork_field_keys,cm_badge_artwork);
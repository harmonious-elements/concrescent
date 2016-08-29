cmui = (function($,window,document){
	var butterbarTimeout, showButterbar, showButterbarPersistent, hideButterbar;
	butterbarTimeout = null;
	showButterbar = function(text) {
		if (butterbarTimeout) clearTimeout(butterbarTimeout);
		butterbarTimeout = setTimeout(function() {
			$('.butterbar').text(text || 'Working...');
			$('.butterbar').removeClass('hidden');
		}, 1000);
	};
	showButterbarPersistent = function(text) {
		if (butterbarTimeout) clearTimeout(butterbarTimeout);
		$('.butterbar').text(text || 'Done.');
		$('.butterbar').removeClass('hidden');
		butterbarTimeout = setTimeout(hideButterbar, 5000);
	};
	hideButterbar = function() {
		if (butterbarTimeout) {
			clearTimeout(butterbarTimeout);
			butterbarTimeout = null;
		}
		$('.butterbar').addClass('hidden');
	};

	var showDialog, hideDialog, escapeDialog;
	showDialog = function(name) {
		$('.dialog-cover').removeClass('hidden');
		$('.dialog').addClass('hidden');
		$('.'+name+'-dialog').removeClass('hidden');
		$('*').blur();
		$('.'+name+'-dialog input:eq(0)').focus();
		$('body').bind('keydown', escapeDialog);
	};
	hideDialog = function() {
		$('.dialog').addClass('hidden');
		$('.dialog-cover').addClass('hidden');
		$('body').unbind('keydown', escapeDialog);
	};
	escapeDialog = function(event) {
		switch (event.which) {
			case 27:
				hideDialog();
				break;
			case 68:
				if (!event.shiftKey || !(event.ctrlKey || event.metaKey)) return;
				var e = $('.dialog:not(.hidden) .confirm-delete-button');
				if (e.length == 1) e.click();
				break;
			case 83:
				if (!event.shiftKey || !(event.ctrlKey || event.metaKey)) return;
				var e = $('.dialog:not(.hidden) .confirm-edit-button');
				if (e.length == 1) e.click();
				break;
			case 88:
				if (!event.shiftKey || !(event.ctrlKey || event.metaKey)) return;
				var e = $('.dialog:not(.hidden) .soft-delete-button');
				if (e.length == 1) e.click();
				break;
			default:
				return;
		}
		event.stopPropagation();
		event.preventDefault();
	};

	var calculateTextSize, fitText;
	calculateTextSize = function(text, fontFamily, fontSize, fontWeight, fontStyle) {
		var e = $('#calculateTextSize');
		if (!e.length) {
			e = $('<div id="calculateTextSize"/>');
			e.css({
				'position': 'absolute',
				'top': '0',
				'left': '0',
				'visibility': 'hidden',
				'white-space': 'nowrap'
			});
			$('body').append(e);
		}
		e.css({
			'font-family': fontFamily,
			'font-size': fontSize + 'px',
			'font-weight': fontWeight,
			'font-style': fontStyle
		});
		e.text(text);
		return [e.width(), e.height()];
	};
	fitText = function(e) {
		var text = e.text();
		var fontFamily = e.css('font-family');
		var fontWeight = e.css('font-weight');
		var fontStyle = e.css('font-style');
		var w = e.innerWidth() - 2;
		var h = e.innerHeight() - 2;
		var min = 0;
		var cur = h;
		var max = h*2;
		while (Math.abs(max-min) > 0.01) {
			var ts = calculateTextSize(text, fontFamily, cur, fontWeight, fontStyle);
			var tw = ts[0];
			var th = ts[1];
			if (tw > w || th > h) {
				// Too big, need to make it smaller.
				max = cur;
				cur = (min+max)/2;
			} else if (tw < w && th < h) {
				// Too small, need to make it bigger.
				min = cur;
				cur = (min+max)/2;
			} else {
				break;
			}
		}
		e.css('font-size', cur + 'px');
		return cur;
	};

	var focusedOnInput;
	focusedOnInput = function() {
		var e = document.activeElement;
		if (e) {
			var n = e.nodeName;
			if (n) {
				var nn = n.toLowerCase();
				return (nn == 'input' || nn == 'textarea' || nn == 'select');
			}
		}
		return false;
	};

	var htmlSpecialChars = function(s) {
		s = s.replace(/&/g, '&amp;');
		s = s.replace(/"/g, '&quot;');
		s = s.replace(/</g, '&lt;');
		s = s.replace(/>/g, '&gt;');
		return s;
	};
	var paragraphString = function(s) {
		s = htmlSpecialChars(s);
		s = s.replace(/\r\n/g, '<br>');
		s = s.replace(/\r/g, '<br>');
		s = s.replace(/\n/g, '<br>');
		return s;
	};
	var safeHtmlString = function(s, paragraph) {
		s = paragraphString(s);
		s = s.replace(/&lt;a href=&quot;(([^"'&<>]|&amp;)*?)&quot;( target=&quot;(([^"'&<>]|&amp;)*?)&quot;)?&gt;(.*?)&lt;\/a&gt;/g, '<a href="$1" target="_blank">$6</a>');
		s = s.replace(/&lt;img src=&quot;(([^"'&<>]|&amp;)*?)&quot;&gt;/g, '<img src="$1">');
		s = s.replace(/&lt;(b|i|u|s|q|tt|em|strong|sup|sub|big|small|ins|del|abbr|cite|code|dfn|kbd|samp|var)&gt;(.*?)&lt;\/\1&gt;/g, '<$1>$2</$1>');
		s = s.replace(/&lt;(br|wbr)&gt;/g, '<$1>');
		if (paragraph) {
			var ptag = ((paragraph === true) ? '<p>' : ('<p class="' + paragraph + '">'));
			s = ptag + s.replace(/(<br>){2,}/g, '</p>' + ptag) + '</p>';
		}
		return s;
	};
	var priceString = function(price) {
		price = Number(price);
		if (!price) return 'FREE';
		price = String(price).split('.');
		price[1] = ((price[1] || '') + '00').substring(0, 2);
		for (var i = price[0].length - 3; i > 0; i -= 3) {
			price[0] = price[0].substring(0, i) + ',' + price[0].substring(i);
		}
		return '$' + price[0] + '.' + price[1];
	};
	var formatDate = function(date) {
		if (!date) return null;
		var year = date.getFullYear();
		if (!isFinite(year)) return null;
		var month = ('00' + (date.getMonth() + 1)).substr(-2);
		var day = ('00' + date.getDate()).substr(-2);
		return year + '-' + month + '-' + day;
	};
	var parseDate = function(s) {
		if (isFinite(Date.parse(s))) return new Date(s);
		s = s.replace(/ /g, '');
		if (isFinite(Date.parse(s))) return new Date(s);
		s = s.replace(/-/g, '/');
		if (isFinite(Date.parse(s))) return new Date(s);
		return null;
	};

	return {
		showButterbar: showButterbar,
		showButterbarPersistent: showButterbarPersistent,
		hideButterbar: hideButterbar,
		showDialog: showDialog,
		hideDialog: hideDialog,
		fitText: fitText,
		focusedOnInput: focusedOnInput,
		htmlSpecialChars: htmlSpecialChars,
		paragraphString: paragraphString,
		safeHtmlString: safeHtmlString,
		priceString: priceString,
		formatDate: formatDate,
		parseDate: parseDate
	};
})(jQuery,window,document);
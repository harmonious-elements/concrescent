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
		$('body').bind('keydown', escapeDialog);
	};
	hideDialog = function() {
		$('.dialog').addClass('hidden');
		$('.dialog-cover').addClass('hidden');
		$('body').unbind('keydown', escapeDialog);
	};
	escapeDialog = function(event) {
		if (event.which == 27) {
			hideDialog();
			event.stopPropagation();
			event.preventDefault();
		}
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
	var safeHtmlString = function(s) {
		s = paragraphString(s);
		s = s.replace(/&lt;a href=&quot;(([^"'&<>]+|&amp;)*)&quot;&gt;(.*?)&lt;\/a&gt;/g, '<a href="$1" target="_blank">$3</a>');
		s = s.replace(/&lt;img src=&quot;(([^"'&<>]+|&amp;)*)&quot;&gt;/g, '<img src="$1">');
		s = s.replace(/&lt;(b|i|u|s|q|tt|em|strong|sup|sub|big|small|ins|del|abbr|cite|code|dfn|kbd|samp|var)&gt;(.*?)&lt;\/\1&gt;/g, '<$1>$2</$1>');
		s = s.replace(/&lt;(br|wbr)&gt;/g, '<$1>');
		return s;
	};

	return {
		showButterbar: showButterbar,
		showButterbarPersistent: showButterbarPersistent,
		hideButterbar: hideButterbar,
		showDialog: showDialog,
		hideDialog: hideDialog,
		htmlSpecialChars: htmlSpecialChars,
		paragraphString: paragraphString,
		safeHtmlString: safeHtmlString
	};
})(jQuery,window,document);
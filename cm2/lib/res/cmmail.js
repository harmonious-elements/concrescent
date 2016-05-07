(function($,window,document){
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

	$(document).ready(function() {
		$('.cm-mail-editor').each(function() {
			var self = $(this);
			var type = self.find('.cm-mail-type');
			var body = self.find('.cm-mail-body');
			var iframe = self.find('iframe');
			var updatePreview = function() {
				var preview;
				switch (type.val()) {
					case 'Full HTML':
						preview = body.val();
						break;
					case 'Simple HTML':
						preview = (
							'<html><head><style>' +
							'body{font-family:sans-serif;font-size:12px;}' +
							'</style></head><body>' +
							safeHtmlString(body.val()) +
							'</body></html>'
						);
						break;
					default:
						preview = (
							'<html><head><style>' +
							'body{font-family:sans-serif;font-size:12px;}' +
							'</style></head><body>' +
							paragraphString(body.val()) +
							'</body></html>'
						);
						break;
				}
				iframe.attr('src', 'data:text/html;charset=UTF-8,' + escape(preview));
			};

			var typeOldValue = type.val();
			var typeChanged = function() {
				var typeNewValue = type.val();
				if (typeNewValue != typeOldValue) {
					typeOldValue = typeNewValue;
					updatePreview();
				}
			};
			type.bind('change', typeChanged);
			type.bind('keydown', typeChanged);
			type.bind('keyup', typeChanged);
			type.bind('mousedown', typeChanged);
			type.bind('mouseup', typeChanged);

			var bodyOldValue = body.val();
			var bodyChanged = function() {
				var bodyNewValue = body.val();
				if (bodyNewValue != bodyOldValue) {
					bodyOldValue = bodyNewValue;
					updatePreview();
				}
			};
			body.bind('change', bodyChanged);
			body.bind('keydown', bodyChanged);
			body.bind('keyup', bodyChanged);

			updatePreview();
		});
	});
})(jQuery,window,document);
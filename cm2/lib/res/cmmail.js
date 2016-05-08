(function($,window,document,cmui){
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
							cmui.safeHtmlString(body.val()) +
							'</body></html>'
						);
						break;
					default:
						preview = (
							'<html><head><style>' +
							'body{font-family:sans-serif;font-size:12px;}' +
							'</style></head><body>' +
							cmui.paragraphString(body.val()) +
							'</body></html>'
						);
						break;
				}
				iframe.attr('srcdoc', preview);
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
})(jQuery,window,document,cmui);
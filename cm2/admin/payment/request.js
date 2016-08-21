(function($,window,document,cmui,templates){
	$(document).ready(function() {
		var templateName = $('#mail-template');
		var iframe = $('.cm-mail-editor iframe');
		var updatePreview = function() {
			var preview = '';
			var template = templates[templateName.val()];
			if (template) {
				var templateType = template['requested']['type'];
				var templateBody = template['requested']['body'];
				switch (templateType) {
					case 'Full HTML':
						preview = templateBody;
						break;
					case 'Simple HTML':
						preview = (
							'<html><head><style>' +
							'body{font-family:sans-serif;font-size:12px;}' +
							'</style></head><body>' +
							cmui.safeHtmlString(templateBody) +
							'</body></html>'
						);
						break;
					default:
						preview = (
							'<html><head><style>' +
							'body{font-family:sans-serif;font-size:12px;}' +
							'</style></head><body>' +
							cmui.paragraphString(templateBody) +
							'</body></html>'
						);
						break;
				}
			}
			iframe.attr('srcdoc', preview);
			iframe.attr('src', 'data:text/html;charset=UTF-8,' + escape(preview));
		};

		var templateNameOldVal = templateName.val();
		var templateNameChanged = function() {
			var templateNameNewVal = templateName.val();
			if (templateNameNewVal != templateNameOldVal) {
				templateNameOldVal = templateNameNewVal;
				updatePreview();
			}
		};
		templateName.bind('change', templateNameChanged);
		templateName.bind('keydown', templateNameChanged);
		templateName.bind('keyup', templateNameChanged);
		templateName.bind('mousedown', templateNameChanged);
		templateName.bind('mouseup', templateNameChanged);

		updatePreview();

		$('body').bind('keydown', function(event) {
			if (event.which != 83) return;
			if (!event.shiftKey || !(event.ctrlKey || event.metaKey)) return;
			var e = $('input[type=submit]');
			if (e.length == 1) e.click();
			event.stopPropagation();
			event.preventDefault();
		});
	});
})(jQuery,window,document,cmui,cm_payment_request_templates);
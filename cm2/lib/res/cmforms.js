(function($,window,document,cmui,formdef){
	var doAjax = function(message, request, done) {
		cmui.showButterbar(message);
		$.post((formdef['ajax-url'] || ''), request, function(response) {
			if (!response['ok']) {
				cmui.showButterbarPersistent('An error occurred. Please try again.');
			} else {
				done(response);
				cmui.hideButterbar();
			}
		}, 'json');
	};

	$(document).ready(function() {
		/* Custom Text Sections */
		$('.cm-form-editor-custom-text-section').each(function() {
			var self = $(this);
			var id = self.attr('id').substring(13);
			var currentText, currentHtml;
			var loadContent = function() {
				doAjax('Loading...', {
					'cm-form-action': 'load-custom-text',
					'cm-form-ct-name': id
				}, function(response) {
					var text = response['text'];
					var html = cmui.safeHtmlString(text);
					self.find('.view-row .view-area').html(html);
					self.find('.edit-row textarea').val(text);
					currentText = text;
					currentHtml = html;
				});
			};
			var editContent = function() {
				self.addClass('editing');
				self.find('.edit-row').removeClass('hidden');
				self.find('.edit-row textarea').focus();
			};
			var previewContent = function() {
				var text = self.find('.edit-row textarea').val();
				var html = cmui.safeHtmlString(text);
				self.find('.view-row .view-area').html(html);
			};
			var revertContent = function() {
				self.removeClass('editing');
				self.find('.edit-row').addClass('hidden');
				self.find('.view-row .view-area').html(currentHtml);
				self.find('.edit-row textarea').val(currentText);
			};
			var saveContent = function() {
				var text = self.find('.edit-row textarea').val();
				var html = cmui.safeHtmlString(text);
				self.find('.view-row .view-area').html(html);
				doAjax('Saving...', {
					'cm-form-action': 'save-custom-text',
					'cm-form-ct-name': id,
					'cm-form-ct-text': text
				}, function(response) {
					self.removeClass('editing');
					self.find('.edit-row').addClass('hidden');
					currentText = text;
					currentHtml = html;
				});
			};
			/* Bind Events */
			self.find('.view-row').bind('click', editContent);
			var textArea = self.find('.edit-row textarea');
			var textAreaOldVal = textArea.val();
			var textAreaChanged = function() {
				var textAreaNewVal = textArea.val();
				if (textAreaNewVal != textAreaOldVal) {
					textAreaOldVal = textAreaNewVal;
					previewContent();
				}
			};
			textArea.bind('change', textAreaChanged);
			textArea.bind('keydown', textAreaChanged);
			textArea.bind('keyup', textAreaChanged);
			self.find('.cancel-edit-button').bind('click', revertContent);
			self.find('.confirm-edit-button').bind('click', saveContent);
			loadContent();
		});
	});
})(jQuery,window,document,cmui,cm_form_def);
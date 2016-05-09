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
	var setClass = function(e, c, b) {
		if (b) e.addClass(c);
		else e.removeClass(c);
	};
	var typeTakesTitle = function(type) {
		return (type == 'h1' || type == 'h2' || type == 'h3');
	};
	var typeTakesShortText = function(type) {
		return !(type == 'p' || type == 'hr');
	};
	var typeTakesLongText = function(type) {
		return (type == 'p');
	};
	var typeTakesValues = function(type) {
		return (type == 'radio' || type == 'checkbox' || type == 'select');
	};
	var pushToEditor = function(editor, question) {
		/* Type */
		var type = (question['type'] || 'text');
		editor.find('.ea-type').val(type);
		editor.find('.ear-text-short label').text(typeTakesTitle(type) ? 'Title' : 'Label');
		setClass(editor.find('.ear-text-short'), 'hidden', !typeTakesShortText(type));
		setClass(editor.find('.ear-text-long'), 'hidden', !typeTakesLongText(type));
		setClass(editor.find('.ear-values'), 'hidden', !typeTakesValues(type));
		/* Text */
		var text = (question['text'] || '');
		editor.find('.ea-text-short').val(text);
		editor.find('.ea-text-long').val(text);
		/* Values */
		var values = (question['values'] || []).join('\n');
		editor.find('.ea-values').val(values);
		/* Active & Listed */
		editor.find('.ea-active').prop('checked', !!question['active']);
		editor.find('.ea-listed').prop('checked', !!question['listed']);
		/* Visible */
		if (question['visible'] && question['visible'].indexOf('*') >= 0) {
			editor.find('.ea-visible').prop('checked', true);
			editor.find('.ear-visible-advanced').addClass('hidden');
			editor.find('.ear-visible-advanced input').prop('checked', true);
		} else {
			editor.find('.ea-visible').prop('checked', false);
			editor.find('.ear-visible-advanced').addClass('hidden');
			editor.find('.ear-visible-advanced input').prop('checked', false);
			for (var i = 0, n = formdef['subcontext'].length; i < n; ++i) {
				var id = formdef['subcontext'][i]['id'];
				var checked = (question['visible'] && question['visible'].indexOf(id) >= 0);
				editor.find('.ea-visible-' + id).prop('checked', checked);
				if (checked) editor.find('.ear-visible-advanced').removeClass('hidden');
			}
		}
		/* Required */
		if (question['required'] && question['required'].indexOf('*') >= 0) {
			editor.find('.ea-required').prop('checked', true);
			editor.find('.ear-required-advanced').addClass('hidden');
			editor.find('.ear-required-advanced input').prop('checked', true);
		} else {
			editor.find('.ea-required').prop('checked', false);
			editor.find('.ear-required-advanced').addClass('hidden');
			editor.find('.ear-required-advanced input').prop('checked', false);
			for (var i = 0, n = formdef['subcontext'].length; i < n; ++i) {
				var id = formdef['subcontext'][i]['id'];
				var checked = (question['required'] && question['required'].indexOf(id) >= 0);
				editor.find('.ea-required-' + id).prop('checked', checked);
				if (checked) editor.find('.ear-required-advanced').removeClass('hidden');
			}
		}
	};
	var pullFromEditor = function(editor, question) {
		/* Type, Text, Values, Active, Listed */
		if (!question) question = {};
		var type = (editor.find('.ea-type').val() || 'text');
		var text = typeTakesShortText(type) ? (editor.find('.ea-text-short').val() || '') :
		           typeTakesLongText(type) ? (editor.find('.ea-text-long').val() || '') : '';
		var values = typeTakesValues(type) ? (editor.find('.ea-values').val() || '') : '';
		values = values.replace(/\r\n/g, '\n');
		values = values.replace(/\r/g, '\n');
		values = values.replace(/\n+/g, '\n').trim();
		values = values ? values.split('\n') : [];
		question['type'] = type;
		question['text'] = text;
		question['values'] = values;
		question['active'] = editor.find('.ea-active').is(':checked');
		question['listed'] = editor.find('.ea-listed').is(':checked');
		/* Visible */
		if (editor.find('.ea-visible').is(':checked')) {
			question['visible'] = ['*'];
		} else {
			question['visible'] = [];
			for (var i = 0, n = formdef['subcontext'].length; i < n; ++i) {
				var id = formdef['subcontext'][i]['id'];
				var checked = editor.find('.ea-visible-' + id).is(':checked');
				if (checked) question['visible'].push(id);
			}
		}
		/* Required */
		if (editor.find('.ea-required').is(':checked')) {
			question['required'] = ['*'];
		} else {
			question['required'] = [];
			for (var i = 0, n = formdef['subcontext'].length; i < n; ++i) {
				var id = formdef['subcontext'][i]['id'];
				var checked = editor.find('.ea-required-' + id).is(':checked');
				if (checked) question['required'].push(id);
			}
		}
		return question;
	};
	var prepEditor = function(editor, onChange) {
		/* Type */
		var typeField = editor.find('.ea-type');
		var typeOldVal = typeField.val();
		var typeChanged = function() {
			var typeNewVal = typeField.val();
			if (typeNewVal != typeOldVal) {
				typeOldVal = typeNewVal;
				editor.find('.ear-text-short label').text(typeTakesTitle(typeNewVal) ? 'Title' : 'Label');
				setClass(editor.find('.ear-text-short'), 'hidden', !typeTakesShortText(typeNewVal));
				setClass(editor.find('.ear-text-long'), 'hidden', !typeTakesLongText(typeNewVal));
				setClass(editor.find('.ear-values'), 'hidden', !typeTakesValues(typeNewVal));
				if (onChange) onChange(editor, 'type', typeNewVal);
			}
		};
		typeField.bind('change', typeChanged);
		typeField.bind('keydown', typeChanged);
		typeField.bind('keyup', typeChanged);
		typeField.bind('mousedown', typeChanged);
		typeField.bind('mouseup', typeChanged);
		/* Short Text */
		var shortTextField = editor.find('.ea-text-short');
		var shortTextFieldOldVal = shortTextField.val();
		var shortTextFieldChanged = function() {
			var shortTextFieldNewVal = shortTextField.val();
			if (shortTextFieldNewVal != shortTextFieldOldVal) {
				shortTextFieldOldVal = shortTextFieldNewVal;
				editor.find('.ea-text-long').val(shortTextFieldNewVal);
				if (onChange) onChange(editor, 'text', shortTextFieldNewVal);
			}
		};
		shortTextField.bind('change', shortTextFieldChanged);
		shortTextField.bind('keydown', shortTextFieldChanged);
		shortTextField.bind('keyup', shortTextFieldChanged);
		/* Long Text */
		var longTextField = editor.find('.ea-text-long');
		var longTextFieldOldVal = longTextField.val();
		var longTextFieldChanged = function() {
			var longTextFieldNewVal = longTextField.val();
			if (longTextFieldNewVal != longTextFieldOldVal) {
				longTextFieldOldVal = longTextFieldNewVal;
				editor.find('.ea-text-short').val(longTextFieldNewVal);
				if (onChange) onChange(editor, 'text', longTextFieldNewVal);
			}
		};
		longTextField.bind('change', longTextFieldChanged);
		longTextField.bind('keydown', longTextFieldChanged);
		longTextField.bind('keyup', longTextFieldChanged);
		/* Values */
		var valuesField = editor.find('.ea-values');
		var valuesFieldOldVal = valuesField.val();
		var valuesFieldChanged = function() {
			var valuesFieldNewVal = valuesField.val();
			if (valuesFieldNewVal != valuesFieldOldVal) {
				valuesFieldOldVal = valuesFieldNewVal;
				if (onChange) onChange(editor, 'values', valuesFieldNewVal);
			}
		};
		valuesField.bind('change', valuesFieldChanged);
		valuesField.bind('keydown', valuesFieldChanged);
		valuesField.bind('keyup', valuesFieldChanged);
		/* Active & Listed */
		editor.find('.ea-active').bind('click', function() {
			if (onChange) onChange(editor, 'active', $(this).is(':checked'));
		});
		editor.find('.ea-listed').bind('click', function() {
			if (onChange) onChange(editor, 'listed', $(this).is(':checked'));
		});
		/* Visible */
		editor.find('.ea-visible').bind('click', function() {
			var checked = editor.find('.ea-visible').is(':checked');
			editor.find('.ear-visible-advanced input').prop('checked', checked);
			if (onChange) onChange(editor, 'visible', checked);
		});
		editor.find('.ea-visible-advanced').bind('click', function() {
			var checked = editor.find('.ear-visible-advanced input').is(':checked');
			var notChecked = editor.find('.ear-visible-advanced input').is(':not(:checked)');
			if (!(checked && notChecked)) editor.find('.ear-visible-advanced').toggleClass('hidden');
		});
		editor.find('.ear-visible-advanced input').bind('click', function() {
			var checked = !editor.find('.ear-visible-advanced input').is(':not(:checked)');
			editor.find('.ea-visible').prop('checked', checked);
			if (onChange) onChange(editor, 'visible', checked);
		});
		/* Required */
		editor.find('.ea-required').bind('click', function() {
			var checked = editor.find('.ea-required').is(':checked');
			editor.find('.ear-required-advanced input').prop('checked', checked);
			if (onChange) onChange(editor, 'required', checked);
		});
		editor.find('.ea-required-advanced').bind('click', function() {
			var checked = editor.find('.ear-required-advanced input').is(':checked');
			var notChecked = editor.find('.ear-required-advanced input').is(':not(:checked)');
			if (!(checked && notChecked)) editor.find('.ear-required-advanced').toggleClass('hidden');
		});
		editor.find('.ear-required-advanced input').bind('click', function() {
			var checked = !editor.find('.ear-required-advanced input').is(':not(:checked)');
			editor.find('.ea-required').prop('checked', checked);
			if (onChange) onChange(editor, 'required', checked);
		});
	};
	var renderQuestion = function(question, done) {
		cmui.showButterbar('Loading...');
		$.post((formdef['ajax-url'] || ''), {
			'cm-form-action': 'render-dynamic-row',
			'cm-form-question': JSON.stringify(question)
		}, function(response) {
			if (!response) {
				cmui.showButterbarPersistent('An error occurred. Please try again.');
			} else {
				done(response);
				cmui.hideButterbar();
			}
		});
	};

	$(document).ready(function() {
		/* Custom Text Sections */
		$('.cm-form-editor-custom-text-section').each(function() {
			var self = $(this);
			var id = self.attr('id').substring(13);
			var defaultHtml = self.find('.view-row .view-area').html();
			var currentText, currentHtml;
			var loadContent = function() {
				doAjax('Loading...', {
					'cm-form-action': 'load-custom-text',
					'cm-form-ct-name': id
				}, function(response) {
					var text = response['text'];
					var html = cmui.safeHtmlString(text);
					self.find('.view-row .view-area').html(html || defaultHtml);
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
				self.find('.view-row .view-area').html(html || defaultHtml);
			};
			var revertContent = function() {
				self.removeClass('editing');
				self.find('.edit-row').addClass('hidden');
				self.find('.view-row .view-area').html(currentHtml || defaultHtml);
				self.find('.edit-row textarea').val(currentText);
			};
			var saveContent = function() {
				var text = self.find('.edit-row textarea').val();
				var html = cmui.safeHtmlString(text);
				self.find('.view-row .view-area').html(html || defaultHtml);
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

		/* Dynamic Form Section */

		/* Keyboard Navigation */
		$('body').bind('keydown', function(event) {
			if (event.which == 27) {
				if ($('.dialog-cover').hasClass('hidden')) {
					$('.cancel-edit-button').click();
					event.stopPropagation();
					event.preventDefault();
				}
			}
		});
	});
})(jQuery,window,document,cmui,cm_form_def);
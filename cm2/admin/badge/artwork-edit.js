(function($,window,document,cmui){
	var exampleBadgeHolder = {
		'id': 12345,
		'id-string': 'A12345',
		'uuid': '89abcdef-0123-4567-89ab-cdef01234567',
		'qr-data': 'CM*A12345*89ABCDEF-0123-4567-89AB-CDEF01234567',
		'qr-url': '../../lib/res/barcode.php?s=qr&w=300&h=300&d=CM*A12345*89ABCDEF-0123-4567-89AB-CDEF01234567',
		'badge-type-id': 1,
		'badge-type-name': 'Badge Type',
		'application-id': 123,
		'application-name': 'Application Name',
		'first-name': 'Firstname',
		'last-name': 'Lastname',
		'real-name': 'Firstname Lastname',
		'fandom-name': 'FandomName12345',
		'only-name': 'Only Name',
		'large-name': 'Large Name',
		'small-name': 'Small Name',
		'display-name': 'Firstname Lastname (FandomName12345)',
		'assigned-room-or-table-id': 'RT12',
		'assigned-department-id': 12,
		'assigned-department-name': 'Registration',
		'assigned-position-id': 1234,
		'assigned-position-name': 'Dynamic Lead',
		'assigned-position-name-s': 'Registration Dynamic Lead',
		'assigned-position-name-h': 'Registration - Dynamic Lead'
	};
	var newField = function(x1, y1, x2, y2) {
		return {
			'id': null,
			'x1': Math.min(x1, x2),
			'y1': Math.min(y1, y2),
			'x2': Math.max(x1, x2),
			'y2': Math.max(y1, y2),
			'field-key': 'only-name',
			'font-size': 0,
			'font-family': 'Helvetica',
			'font-weight-bold': 0,
			'font-style-italic': 0,
			'color': 'black',
			'background': 'transparent',
			'color-minors': 'white',
			'background-minors': 'black'
		};
	};

	var getXY = function(self, event) {
		var offset = self.offset();
		var x = (event.pageX - offset.left) / (self.innerWidth() - 1);
		var y = (event.pageY - offset.top) / (self.innerHeight() - 1);
		if (x < 0) x = 0; if (x > 1) x = 1;
		if (y < 0) y = 0; if (y > 1) y = 1;
		return [x, y];
	};
	var setRect = function(element, x1, y1, x2, y2) {
		element.css('top', (Math.min(y1, y2) * 100) + '%');
		element.css('left', (Math.min(x1, x2) * 100) + '%');
		element.css('right', ((1 - Math.max(x1, x2)) * 100) + '%');
		element.css('bottom', ((1 - Math.max(y1, y2)) * 100) + '%');
	};
	var doAjax = function(request, done, message, errorMessage) {
		cmui.showButterbar(message || 'Saving changes...');
		$.post(
			'', request,
			function(response) {
				if (!response['ok']) {
					cmui.showButterbarPersistent(errorMessage || 'An error occurred. Please try again.');
				} else {
					if (done) done(response);
					cmui.hideButterbar();
				}
			},
			'json'
		);
	};

	$(document).ready(function() {
		var fieldId = null;
		var fieldRect = [0, 0, 0, 0];
		var artworkArea = $('.badge-artwork');
		var fieldArea = $('.badge-artwork-fields');
		var fieldMarquee = $('.badge-artwork-field-marquee');
		var fieldRectEditor = $('.badge-artwork-field-editor');
		var fieldRectEditorContent = $('.badge-artwork-field-editor-content');
		var fieldPropEditor = $('.field-editor-card');
		var artworkEditor = $('.artwork-editor-card');

		var fieldKey = $('#field-editor-field-key');
		var fieldFontFamily = $('#field-editor-font-family');
		var fieldFontWeightBold = $('#field-editor-font-weight-bold');
		var fieldFontStyleItalic = $('#field-editor-font-style-italic');
		var fieldColor = $('#field-editor-color');
		var fieldBackground = $('#field-editor-background');
		var fieldColorMinors = $('#field-editor-color-minors');
		var fieldBackgroundMinors = $('#field-editor-background-minors');

		var pushModelToView = function(model, view, viewContent) {
			setRect(view, model['x1'], model['y1'], model['x2'], model['y2']);
			if (model['field-key'].substring(0, 8) == 'img-src=') {
				(viewContent || view).text('');
				view.css({
					'background': 'url(\'' + exampleBadgeHolder[model['field-key'].substring(8)] + '\') no-repeat center',
					'background-size': 'contain'
				});
			} else {
				(viewContent || view).text(exampleBadgeHolder[model['field-key']]);
				view.css({
					'font-size': '0',
					'font-family': model['font-family'],
					'font-weight': (model['font-weight-bold'] ? 'bold' : 'normal'),
					'font-style': (model['font-style-italic'] ? 'italic' : 'normal'),
					'color': model['color'],
					'background': model['background']
				});
				cmui.fitText(view);
				window.setTimeout(function() { cmui.fitText(view); }, 1);
			}
		}

		var openMarquee = function() {
			setRect(fieldMarquee, fieldRect[0], fieldRect[1], fieldRect[2], fieldRect[3]);
			fieldMarquee.removeClass('hidden');
		};
		var resizeMarquee = function() {
			setRect(fieldMarquee, fieldRect[0], fieldRect[1], fieldRect[2], fieldRect[3]);
		};
		var closeMarquee = function() {
			fieldMarquee.addClass('hidden');
		};

		var pushToEditor = function(field) {
			fieldId = field['id'];
			fieldRect[0] = Math.min(field['x1'], field['x2']);
			fieldRect[1] = Math.min(field['y1'], field['y2']);
			fieldRect[2] = Math.max(field['x1'], field['x2']);
			fieldRect[3] = Math.max(field['y1'], field['y2']);
			pushModelToView(field, fieldRectEditor, fieldRectEditorContent);
			fieldKey.val(field['field-key']);
			fieldFontFamily.val(field['font-family']);
			fieldFontWeightBold.prop('checked', !!field['font-weight-bold']);
			fieldFontStyleItalic.prop('checked', !!field['font-style-italic']);
			fieldColor.val(field['color']);
			fieldBackground.val(field['background']);
			fieldColorMinors.val(field['color-minors']);
			fieldBackgroundMinors.val(field['background-minors']);
		};
		var pullFromEditor = function() {
			return {
				'id': fieldId,
				'x1': Math.min(fieldRect[0], fieldRect[2]),
				'y1': Math.min(fieldRect[1], fieldRect[3]),
				'x2': Math.max(fieldRect[0], fieldRect[2]),
				'y2': Math.max(fieldRect[1], fieldRect[3]),
				'field-key': fieldKey.val(),
				'font-size': 0,
				'font-family': fieldFontFamily.val(),
				'font-weight-bold': (fieldFontWeightBold.is(':checked') ? 1 : 0),
				'font-style-italic': (fieldFontStyleItalic.is(':checked') ? 1 : 0),
				'color': fieldColor.val(),
				'background': fieldBackground.val(),
				'color-minors': fieldColorMinors.val(),
				'background-minors': fieldBackgroundMinors.val()
			};
		};

		var openEditor = function(model, view) {
			pushToEditor(model);
			fieldArea.find('.badge-artwork-field').removeClass('hidden');
			if (view) view.addClass('hidden');
			fieldRectEditor.removeClass('hidden');
			fieldPropEditor.removeClass('hidden');
			artworkEditor.addClass('hidden');
		};
		var resizeEditor = function(finalize) {
			if (finalize) {
				fieldRect = [
					Math.min(fieldRect[0], fieldRect[2]),
					Math.min(fieldRect[1], fieldRect[3]),
					Math.max(fieldRect[0], fieldRect[2]),
					Math.max(fieldRect[1], fieldRect[3])
				];
			}
			setRect(fieldRectEditor, fieldRect[0], fieldRect[1], fieldRect[2], fieldRect[3]);
			cmui.fitText(fieldRectEditor);
			window.setTimeout(function() { cmui.fitText(fieldRectEditor); }, 1);
		};
		var updateEditor = function() {
			pushModelToView(pullFromEditor(), fieldRectEditor, fieldRectEditorContent);
		};
		var closeEditor = function() {
			fieldArea.find('.badge-artwork-field').removeClass('hidden');
			fieldRectEditor.addClass('hidden');
			fieldPropEditor.addClass('hidden');
			artworkEditor.removeClass('hidden');
		};

		var artworkMouseBind = function(element) {
			var artworkMouseDown, artworkMouseDrag, artworkMouseUp;
			artworkMouseDown = function(event) {
				if (fieldPropEditor.hasClass('hidden')) {
					var xy = getXY(artworkArea, event);
					fieldId = null;
					fieldRect[0] = fieldRect[2] = xy[0];
					fieldRect[1] = fieldRect[3] = xy[1];
					closeEditor();
					openMarquee();
					$(document).unbind('mousemove').bind('mousemove', artworkMouseDrag);
					$(document).unbind('mouseup').bind('mouseup', artworkMouseUp);
				}
				event.stopPropagation();
				event.preventDefault();
			};
			artworkMouseDrag = function(event) {
				var xy = getXY(artworkArea, event);
				fieldRect[2] = xy[0];
				fieldRect[3] = xy[1];
				resizeMarquee();
				event.stopPropagation();
				event.preventDefault();
			};
			artworkMouseUp = function(event) {
				var xy = getXY(artworkArea, event);
				fieldRect[2] = xy[0];
				fieldRect[3] = xy[1];
				closeMarquee();
				if (
					Math.abs(fieldRect[0] - fieldRect[2]) > 0.02 &&
					Math.abs(fieldRect[1] - fieldRect[3]) > 0.02
				) {
					openEditor(newField(
						fieldRect[0], fieldRect[1],
						fieldRect[2], fieldRect[3]
					));
				}
				$(document).unbind('mousemove');
				$(document).unbind('mouseup');
				event.stopPropagation();
				event.preventDefault();
			};
			element.bind('mousedown', artworkMouseDown);
		};
		artworkMouseBind(artworkArea);

		var fieldsMouseBind = function(element, field) {
			var fieldsMouseEvent = function(event) {
				if (fieldPropEditor.hasClass('hidden')) {
					openEditor(field, element);
				}
				event.stopPropagation();
				event.preventDefault();
			};
			element.bind('mousedown', fieldsMouseEvent);
		};

		var fieldsLoad = function(done) {
			doAjax(
				{'action': 'list-fields'},
				function(response) {
					fieldArea.empty();
					var fields = response['fields'];
					if (fields && fields.length) {
						for (var i = 0, n = fields.length; i < n; ++i) {
							var field = $('<div/>').addClass('badge-artwork-field');
							pushModelToView(fields[i], field, field);
							fieldArea.append(field);
							fieldsMouseBind(field, fields[i]);
						}
					}
					if (done) done(response);
				},
				'Loading...',
				'An error occurred. Please reload the page.'
			);
		};

		var marqueeMouseBind = function(element) {
			var marqueeMouseEvent = function(event) {
				event.stopPropagation();
				event.preventDefault();
			};
			element.bind('mousedown', marqueeMouseEvent);
		};
		marqueeMouseBind(fieldRectEditor);

		var handleMouseBind = function(element, flags) {
			var doResize;
			if (flags) {
				doResize = function(xy) {
					if (flags & 1) fieldRect[0] = xy[0];
					if (flags & 2) fieldRect[1] = xy[1];
					if (flags & 4) fieldRect[2] = xy[0];
					if (flags & 8) fieldRect[3] = xy[1];
				};
			} else {
				doResize = function(xy) {
					var dx = (fieldRect[2] - fieldRect[0]) / 2;
					var dy = (fieldRect[3] - fieldRect[1]) / 2;
					fieldRect[0] = xy[0] - dx;
					fieldRect[1] = xy[1] - dy;
					fieldRect[2] = xy[0] + dx;
					fieldRect[3] = xy[1] + dy;
				};
			}
			var handleMouseDown, handleMouseDrag, handleMouseUp;
			handleMouseDown = function(event) {
				var xy = getXY(artworkArea, event);
				doResize(xy);
				resizeEditor();
				$(document).unbind('mousemove').bind('mousemove', handleMouseDrag);
				$(document).unbind('mouseup').bind('mouseup', handleMouseUp);
				event.stopPropagation();
				event.preventDefault();
			};
			handleMouseDrag = function(event) {
				var xy = getXY(artworkArea, event);
				doResize(xy);
				resizeEditor();
				event.stopPropagation();
				event.preventDefault();
			};
			handleMouseUp = function(event) {
				var xy = getXY(artworkArea, event);
				doResize(xy);
				resizeEditor(true);
				$(document).unbind('mousemove');
				$(document).unbind('mouseup');
				event.stopPropagation();
				event.preventDefault();
			};
			element.bind('mousedown', handleMouseDown);
		};
		handleMouseBind(fieldRectEditor.find('.badge-artwork-field-editor-handle-nw'), 3);
		handleMouseBind(fieldRectEditor.find('.badge-artwork-field-editor-handle-n'), 2);
		handleMouseBind(fieldRectEditor.find('.badge-artwork-field-editor-handle-ne'), 6);
		handleMouseBind(fieldRectEditor.find('.badge-artwork-field-editor-handle-e'), 4);
		handleMouseBind(fieldRectEditor.find('.badge-artwork-field-editor-handle-se'), 12);
		handleMouseBind(fieldRectEditor.find('.badge-artwork-field-editor-handle-s'), 8);
		handleMouseBind(fieldRectEditor.find('.badge-artwork-field-editor-handle-sw'), 9);
		handleMouseBind(fieldRectEditor.find('.badge-artwork-field-editor-handle-w'), 1);
		handleMouseBind(fieldRectEditor.find('.badge-artwork-field-editor-handle-center'), 0);

		fieldKey.bind('change', updateEditor);
		fieldKey.bind('mousedown', updateEditor);
		fieldKey.bind('mouseup', updateEditor);
		fieldKey.bind('keydown', updateEditor);
		fieldKey.bind('keyup', updateEditor);
		fieldFontFamily.bind('change', updateEditor);
		fieldFontFamily.bind('keydown', updateEditor);
		fieldFontFamily.bind('keyup', updateEditor);
		fieldFontWeightBold.bind('click', updateEditor);
		fieldFontStyleItalic.bind('click', updateEditor);
		fieldColor.bind('change', updateEditor);
		fieldColor.bind('keydown', updateEditor);
		fieldColor.bind('keyup', updateEditor);
		fieldBackground.bind('change', updateEditor);
		fieldBackground.bind('keydown', updateEditor);
		fieldBackground.bind('keyup', updateEditor);

		var editorSave = function() {
			if (fieldPropEditor.hasClass('hidden')) return;
			var request = pullFromEditor();
			request['action'] = (fieldId ? 'update-field' : 'create-field');
			doAjax(request, function() {
				fieldsLoad(function() {
					closeEditor();
				});
			});
		};
		var editorDelete = function() {
			if (fieldPropEditor.hasClass('hidden')) return;
			if (fieldId) {
				var request = {
					'action': 'delete-field',
					'id': fieldId
				};
				doAjax(request, function() {
					fieldsLoad(function() {
						closeEditor();
					});
				});
			} else {
				closeEditor();
			}
		};
		fieldPropEditor.find('.field-editor-save').bind('click', editorSave);
		fieldPropEditor.find('.field-editor-cancel').bind('click', closeEditor);
		fieldPropEditor.find('.field-editor-delete').bind('click', editorDelete);

		var editorPrev = function() {
			var prev = $('.badge-artwork-field.hidden').prev('.badge-artwork-field');
			if (!prev.length) prev = $('.badge-artwork-field:last');
			prev.mousedown();
		};
		var editorNext = function() {
			var next = $('.badge-artwork-field.hidden').next('.badge-artwork-field');
			if (!next.length) next = $('.badge-artwork-field:first');
			next.mousedown();
		};
		$('body').bind('keydown', function(event) {
			if (!$('.dialog-cover').hasClass('hidden')) return;
			switch (event.which) {
				case 27:
					closeEditor();
					break;
				case 37: case 38:
					if (cmui.focusedOnInput()) return;
					editorPrev();
					break;
				case 39: case 40:
					if (cmui.focusedOnInput()) return;
					editorNext();
					break;
				case 68:
					if (!event.shiftKey || !(event.ctrlKey || event.metaKey)) return;
					editorDelete();
					break;
				case 83:
					if (!event.shiftKey || !(event.ctrlKey || event.metaKey)) return;
					editorSave();
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

		fieldsLoad();
	});
})(jQuery,window,document,cmui);
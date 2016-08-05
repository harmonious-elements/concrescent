(function($,window,document,cmui){
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
			'rooms-and-tables.php',
			request,
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
		var tagId = null;
		var tagRect = [0, 0, 0, 0];
		var tagMap = $('.tag-map');
		var tagArea = $('.tags');
		var tagMarquee = $('.tag-marquee');
		var tagEditor = $('.tag-editor');
		var tagEditorId = tagEditor.find('.tag-editor-id');

		var openMarquee = function() {
			setRect(tagMarquee, tagRect[0], tagRect[1], tagRect[2], tagRect[3]);
			tagMarquee.removeClass('hidden');
		};
		var resizeMarquee = function() {
			setRect(tagMarquee, tagRect[0], tagRect[1], tagRect[2], tagRect[3]);
		};
		var closeMarquee = function() {
			tagMarquee.addClass('hidden');
		};
		var openEditor = function(tag) {
			tagArea.find('.tag').removeClass('hidden');
			if (tag) tag.addClass('hidden');
			tagEditorId.val(tagId);
			setRect(tagEditor, tagRect[0], tagRect[1], tagRect[2], tagRect[3]);
			tagEditor.removeClass('hidden');
			tagEditorId.focus();
		};
		var resizeEditor = function() {
			setRect(tagEditor, tagRect[0], tagRect[1], tagRect[2], tagRect[3]);
		};
		var closeEditor = function() {
			tagEditor.addClass('hidden');
			tagArea.find('.tag').removeClass('hidden');
		};

		var mapMouseBind = function(element) {
			var mapMouseDown, mapMouseDrag, mapMouseUp;
			mapMouseDown = function(event) {
				var xy = getXY(tagMap, event);
				tagId = null;
				tagRect[0] = tagRect[2] = xy[0];
				tagRect[1] = tagRect[3] = xy[1];
				closeEditor();
				openMarquee();
				$(document).unbind('mousemove').bind('mousemove', mapMouseDrag);
				$(document).unbind('mouseup').bind('mouseup', mapMouseUp);
				event.stopPropagation();
				event.preventDefault();
			};
			mapMouseDrag = function(event) {
				var xy = getXY(tagMap, event);
				tagRect[2] = xy[0];
				tagRect[3] = xy[1];
				resizeMarquee();
				event.stopPropagation();
				event.preventDefault();
			};
			mapMouseUp = function(event) {
				var xy = getXY(tagMap, event);
				tagRect[2] = xy[0];
				tagRect[3] = xy[1];
				closeMarquee();
				openEditor();
				$(document).unbind('mousemove');
				$(document).unbind('mouseup');
				event.stopPropagation();
				event.preventDefault();
			};
			element.bind('mousedown', mapMouseDown);
		};
		mapMouseBind(tagMap);

		var tagsMouseBind = function(element, tag) {
			var tagsMouseEvent = function(event) {
				tagId = tag['id'];
				tagRect[0] = tag['x1'];
				tagRect[1] = tag['y1'];
				tagRect[2] = tag['x2'];
				tagRect[3] = tag['y2'];
				openEditor(element);
				event.stopPropagation();
				event.preventDefault();
			};
			element.bind('mousedown', tagsMouseEvent);
			element.find('label').bind('mousedown', tagsMouseEvent);
		};

		var tagsLoad = function(done) {
			doAjax(
				{'action': 'list-tags'},
				function(response) {
					tagArea.empty();
					var tags = response['tags'];
					if (tags && tags.length) {
						for (var i = 0, n = tags.length; i < n; ++i) {
							var tag = $('<div/>').addClass('tag');
							setRect(tag, tags[i]['x1'], tags[i]['y1'], tags[i]['x2'], tags[i]['y2']);
							var stroke = $('<label/>').addClass('stroke').text(tags[i]['id']);
							tag.append(stroke);
							var fill = $('<label/>').addClass('fill').text(tags[i]['id']);
							tag.append(fill);
							if (tags[i]['assignments'] && tags[i]['assignments'].length) {
								fill.addClass('assigned');
								var title = tags[i]['assignments'][0]['application-name'];
								for (var j = 1, m = tags[i]['assignments'].length; j < m; ++j) {
									title += '\n' + tags[i]['assignments'][j]['application-name'];
								}
								fill.attr('title', title);
							}
							tagArea.append(tag);
							tagsMouseBind(tag, tags[i]);
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
		marqueeMouseBind(tagEditor);

		var handleMouseBind = function(element, flags) {
			var doResize;
			if (flags) {
				doResize = function(xy) {
					if (flags & 1) tagRect[0] = xy[0];
					if (flags & 2) tagRect[1] = xy[1];
					if (flags & 4) tagRect[2] = xy[0];
					if (flags & 8) tagRect[3] = xy[1];
				};
			} else {
				doResize = function(xy) {
					var dx = (tagRect[2] - tagRect[0]) / 2;
					var dy = (tagRect[3] - tagRect[1]) / 2;
					tagRect[0] = xy[0] - dx;
					tagRect[1] = xy[1] - dy;
					tagRect[2] = xy[0] + dx;
					tagRect[3] = xy[1] + dy;
				};
			}
			var handleMouseDown, handleMouseDrag, handleMouseUp;
			handleMouseDown = function(event) {
				var xy = getXY(tagMap, event);
				doResize(xy);
				resizeEditor();
				$(document).unbind('mousemove').bind('mousemove', handleMouseDrag);
				$(document).unbind('mouseup').bind('mouseup', handleMouseUp);
				event.stopPropagation();
				event.preventDefault();
			};
			handleMouseDrag = function(event) {
				var xy = getXY(tagMap, event);
				doResize(xy);
				resizeEditor();
				event.stopPropagation();
				event.preventDefault();
			};
			handleMouseUp = function(event) {
				var xy = getXY(tagMap, event);
				doResize(xy);
				resizeEditor();
				$(document).unbind('mousemove');
				$(document).unbind('mouseup');
				event.stopPropagation();
				event.preventDefault();
			};
			element.bind('mousedown', handleMouseDown);
		};
		handleMouseBind(tagEditor.find('.tag-editor-handle-nw'), 3);
		handleMouseBind(tagEditor.find('.tag-editor-handle-n'), 2);
		handleMouseBind(tagEditor.find('.tag-editor-handle-ne'), 6);
		handleMouseBind(tagEditor.find('.tag-editor-handle-e'), 4);
		handleMouseBind(tagEditor.find('.tag-editor-handle-se'), 12);
		handleMouseBind(tagEditor.find('.tag-editor-handle-s'), 8);
		handleMouseBind(tagEditor.find('.tag-editor-handle-sw'), 9);
		handleMouseBind(tagEditor.find('.tag-editor-handle-w'), 1);
		handleMouseBind(tagEditor.find('.tag-editor-handle-center'), 0);

		var editorMouseBind = function(element) {
			var editorMouseEvent = function(event) {
				event.stopPropagation();
			};
			element.bind('mousedown', editorMouseEvent);
		};
		editorMouseBind(tagEditor.find('.tag-editor-input'));
		editorMouseBind(tagEditor.find('.tag-editor-buttons'));

		var editorSave = function() {
			if (tagEditor.hasClass('hidden')) return;
			var request = {
				'action': (tagId ? 'update-tag' : 'create-tag'),
				'oldid': tagId,
				'x1': Math.min(tagRect[0], tagRect[2]),
				'y1': Math.min(tagRect[1], tagRect[3]),
				'x2': Math.max(tagRect[0], tagRect[2]),
				'y2': Math.max(tagRect[1], tagRect[3]),
				'newid': tagEditorId.val()
			};
			doAjax(request, function() {
				tagsLoad(function() {
					closeEditor();
				});
			});
		};
		var editorDelete = function() {
			if (tagEditor.hasClass('hidden')) return;
			if (tagId) {
				var request = {
					'action': 'delete-tag',
					'oldid': tagId
				};
				doAjax(request, function() {
					tagsLoad(function() {
						closeEditor();
					});
				});
			} else {
				closeEditor();
			}
		};
		tagEditor.find('.tag-editor-save').bind('click', editorSave);
		tagEditor.find('.tag-editor-cancel').bind('click', closeEditor);
		tagEditor.find('.tag-editor-delete').bind('click', editorDelete);

		$('body').bind('keydown', function(event) {
			if (!$('.dialog-cover').hasClass('hidden')) return;
			switch (event.which) {
				case 13:
					if (event.target != tagEditorId[0]) return;
					editorSave();
					break;
				case 27:
					closeEditor();
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

		tagsLoad();
	});
})(jQuery,window,document,cmui);
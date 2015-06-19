$(document).ready(function() {
	var example = {
		'id': 12345,
		'id_string': 'A12345',
		'first_name': 'Firstname',
		'last_name': 'Lastname',
		'real_name': 'Firstname Lastname',
		'fandom_name': 'FandomName12345',
		'only_name': 'Only Name',
		'large_name': 'Large Name',
		'small_name': 'Small Name',
		'display_name': 'Firstname Lastname (FandomName12345)',
		'badge_id': 1,
		'badge_id_string': 'AB1',
		'badge_name': 'Badge Type',
		'group_id': 123,
		'group_id_string': 'AA123',
		'group_name': 'Group Name',
		'assigned_position': 'Dynamic Lead'
	};
	var createModel = function(t, l, b, r) {
		return {
			'field_type': 'only_name',
			'top': t,
			'left': l,
			'right': r,
			'bottom': b,
			'font_size': 0,
			'font_family': 'Helvetica',
			'font_weight_bold': false,
			'font_style_italic': false,
			'color': 'black',
			'background': 'transparent',
			'color_minors': 'white',
			'background_minors': 'black'
		};
	};
	var createView = function() {
		var view = $('<div class="badge-artwork-field"/>');
		view.append($('<div class="grabby tl"/>'));
		view.append($('<div class="grabby t"/>'));
		view.append($('<div class="grabby tr"/>'));
		view.append($('<div class="grabby r"/>'));
		view.append($('<div class="grabby br"/>'));
		view.append($('<div class="grabby b"/>'));
		view.append($('<div class="grabby bl"/>'));
		view.append($('<div class="grabby l"/>'));
		view.append($('<span/>'));
		return view;
	};
	var resizeView = function(view, t, l, b, r) {
		view.css({
			'top': t+'%',
			'left': l+'%',
			'right': r+'%',
			'bottom': b+'%'
		});
	};
	var pushToView = function(view, model) {
		view.find('span').text(example[model['field_type']]);
		view.css({
			'top': model['top']+'%',
			'left': model['left']+'%',
			'right': model['right']+'%',
			'bottom': model['bottom']+'%',
			'font-family': model['font_family'],
			'font-weight': (model['font_weight_bold'] ? 'bold' : 'normal'),
			'font-style': (model['font_style_italic'] ? 'italic' : 'normal'),
			'color': model['color'],
			'background': model['background']
		});
		cmui.fitText(view);
	};
	var pushToEdit = function(model) {
		$('#field-type').val(model['field_type']);
		$('#font-family').val(model['font_family']);
		$('#font-weight-bold').attr('checked', !!model['font_weight_bold']);
		$('#font-style-italic').attr('checked', !!model['font_style_italic']);
		$('#color').val(model['color']);
		$('#background').val(model['background']);
		$('#color-minors').val(model['color_minors']);
		$('#background-minors').val(model['background_minors']);
	};
	var pullFromEdit = function(model) {
		model['field_type'] = $('#field-type').val();
		model['font_family'] = $('#font-family').val();
		model['font_weight_bold'] = !!$('#font-weight-bold').attr('checked');
		model['font_style_italic'] = !!$('#font-style-italic').attr('checked');
		model['color'] = $('#color').val();
		model['background'] = $('#background').val();
		model['color_minors'] = $('#color-minors').val();
		model['background_minors'] = $('#background-minors').val();
	};
	var pullBadgeTypes = function() {
		checked_badge_types = [];
		$('.badge-type-check').each(function() {
			var cb = $(this).find('input');
			var id = cb.attr('id').replace(/^badge-type-/, '');
			var checked = !!cb.attr('checked');
			if (checked) checked_badge_types.push(id);
		});
		return checked_badge_types;
	};
	
	var editingView = null;
	var editingModel = null;
	var startEditing = function(view, model) {
		editingView = null;
		editingModel = null;
		pushToEdit(model);
		$('.badge-artwork-field-form').removeClass('hidden');
		$('.badge-artwork-field').removeClass('active');
		view.addClass('active');
		editingView = view;
		editingModel = model;
	};
	var continueEditing = function() {
		if (editingView && editingModel) {
			pullFromEdit(editingModel);
			pushToView(editingView, editingModel);
		}
	};
	var finishEditing = function() {
		editingView = null;
		editingModel = null;
		$('.badge-artwork-field').removeClass('active');
		$('.badge-artwork-field-form').addClass('hidden');
	};
	
	var startResizing = function(view, model, event, t, l, b, r) {
		var p = $('.badge-artwork-fields');
		var o = p.offset();
		var y1 = model['top'];
		var x1 = model['left'];
		var x2 = 100.0-model['right'];
		var y2 = 100.0-model['bottom'];
		var xo = event.pageX - o.left;
		var yo = event.pageY - o.top;
		var continueResizing = function(event) {
			var xd = (event.pageX - o.left - xo) * 100.0 / (p.innerWidth() - 1);
			var yd = (event.pageY - o.top - yo) * 100.0 / (p.innerHeight() - 1);
			var ny1 = t ? Math.max(0, Math.min(100, (y1 + yd))) : y1;
			var nx1 = l ? Math.max(0, Math.min(100, (x1 + xd))) : x1;
			var nx2 = r ? Math.max(0, Math.min(100, (x2 + xd))) : x2;
			var ny2 = b ? Math.max(0, Math.min(100, (y2 + yd))) : y2;
			model['top'] = Math.min(ny1,ny2);
			model['left'] = Math.min(nx1,nx2);
			model['right'] = 100.0-Math.max(nx1,nx2);
			model['bottom'] = 100.0-Math.max(ny1,ny2);
			pushToView(view, model);
		};
		var finishResizing = function(event) {
			continueResizing(event);
			$('body').unbind('mousemove', continueResizing);
			$('body').unbind('mouseup', finishResizing);
		};
		$('body').bind('mousemove', continueResizing);
		$('body').bind('mouseup', finishResizing);
	};
	
	var viewBindings = [];
	var bindView = function(view, model) {
		view.unbind('mousedown').bind('mousedown', function(event) {
			startEditing(view, model);
			startResizing(view, model, event, true, true, true, true);
			event.stopPropagation();
			event.preventDefault();
		});
		view.find('.tl').unbind('mousedown').bind('mousedown', function(event) {
			startResizing(view, model, event, true, true, false, false);
			event.stopPropagation();
			event.preventDefault();
		});
		view.find('.t').unbind('mousedown').bind('mousedown', function(event) {
			startResizing(view, model, event, true, false, false, false);
			event.stopPropagation();
			event.preventDefault();
		});
		view.find('.tr').unbind('mousedown').bind('mousedown', function(event) {
			startResizing(view, model, event, true, false, false, true);
			event.stopPropagation();
			event.preventDefault();
		});
		view.find('.r').unbind('mousedown').bind('mousedown', function(event) {
			startResizing(view, model, event, false, false, false, true);
			event.stopPropagation();
			event.preventDefault();
		});
		view.find('.br').unbind('mousedown').bind('mousedown', function(event) {
			startResizing(view, model, event, false, false, true, true);
			event.stopPropagation();
			event.preventDefault();
		});
		view.find('.b').unbind('mousedown').bind('mousedown', function(event) {
			startResizing(view, model, event, false, false, true, false);
			event.stopPropagation();
			event.preventDefault();
		});
		view.find('.bl').unbind('mousedown').bind('mousedown', function(event) {
			startResizing(view, model, event, false, true, true, false);
			event.stopPropagation();
			event.preventDefault();
		});
		view.find('.l').unbind('mousedown').bind('mousedown', function(event) {
			startResizing(view, model, event, false, true, false, false);
			event.stopPropagation();
			event.preventDefault();
		});
		viewBindings.push([view, model]);
	};
	var unbindView = function(view, model) {
		view.unbind('mousedown');
		view.find('.grabby').unbind('mousedown');
		for (var i = viewBindings.length - 1; i >= 0; i--) {
			if (viewBindings[i][0] == view || viewBindings[i][1] == model) {
				viewBindings.splice(i);
			}
		}
	};
	var unbindAllViews = function() {
		for (var i = 0; i < viewBindings.length; i++) {
			var view = viewBindings[i][0];
			view.unbind('mousedown');
			view.find('.grabby').unbind('mousedown');
		}
		viewBindings = [];
	};
	var findView = function(model) {
		for (var i = 0; i < viewBindings.length; i++) {
			if (viewBindings[i][1] == model) {
				return viewBindings[i][0];
			}
		}
		return null;
	};
	
	var startCreateDrag = function(event) {
		finishEditing();
		var self = $(this);
		var offset = self.offset();
		var x1 = (event.pageX - offset.left) * 100.0 / (self.innerWidth() - 1);
		var y1 = (event.pageY - offset.top) * 100.0 / (self.innerHeight() - 1);
		var view = createView();
		var x2 = x1;
		var y2 = y1;
		$('.badge-artwork-fields').append(view);
		var continueCreateDrag = function(event) {
			x2 = Math.max(0, Math.min(100, (event.pageX - offset.left) * 100.0 / (self.innerWidth() - 1)));
			y2 = Math.max(0, Math.min(100, (event.pageY - offset.top) * 100.0 / (self.innerHeight() - 1)));
			resizeView(
				view, Math.min(y1,y2), Math.min(x1,x2),
				100.0-Math.max(y1,y2), 100.0-Math.max(x1,x2)
			);
		};
		var finishCreateDrag = function(event) {
			x2 = Math.max(0, Math.min(100, (event.pageX - offset.left) * 100.0 / (self.innerWidth() - 1)));
			y2 = Math.max(0, Math.min(100, (event.pageY - offset.top) * 100.0 / (self.innerHeight() - 1)));
			if (Math.abs(x2-x1) < 2 || Math.abs(y2-y1) < 2) {
				view.remove();
			} else {
				var model = createModel(
					Math.min(y1,y2), Math.min(x1,x2),
					100.0-Math.max(y1,y2), 100.0-Math.max(x1,x2)
				);
				badge_artwork_fields.push(model);
				pushToView(view, model);
				bindView(view, model);
				startEditing(view, model);
			}
			$('body').unbind('mousemove', continueCreateDrag);
			$('body').unbind('mouseup', finishCreateDrag);
		};
		$('body').bind('mousemove', continueCreateDrag);
		$('body').bind('mouseup', finishCreateDrag);
	};
	
	var selectPrev = function() {
		if (badge_artwork_fields.length) {
			var index = badge_artwork_fields.indexOf(editingModel);
			if (index <= 0) index = badge_artwork_fields.length;
			index--;
			var model = badge_artwork_fields[index];
			var view = findView(model);
			startEditing(view, model);
		}
	};
	var selectNext = function() {
		if (badge_artwork_fields.length) {
			var index = badge_artwork_fields.indexOf(editingModel);
			index++;
			if (index >= badge_artwork_fields.length) index = 0;
			var model = badge_artwork_fields[index];
			var view = findView(model);
			startEditing(view, model);
		}
	};
	var deleteSelected = function() {
		if (editingView && editingModel) {
			unbindView(editingView, editingModel);
			editingView.remove();
			var index = badge_artwork_fields.indexOf(editingModel);
			if (index >= 0) {
				badge_artwork_fields.splice(index, 1);
			}
			finishEditing();
		}
	};
	var deleteAllFields = function() {
		unbindAllViews();
		$('.badge-artwork-fields').empty();
		badge_artwork_fields = [];
		finishEditing();
	};
	
	var initViews = function() {
		for (var i = 0; i < badge_artwork_fields.length; i++) {
			var model = badge_artwork_fields[i];
			var view = createView();
			$('.badge-artwork-fields').append(view);
			pushToView(view, model);
			bindView(view, model);
		}
	};
	var refitTextFields = function() {
		$('.badge-artwork-field').each(function() {
			cmui.fitText($(this));
		});
	};
	var importLayout = function() {
		cmui.showButterbar('Loading...');
		var importId = 1 * $('#import-layout-select').val();
		var req = {'action': 'load', 'id': importId};
		jQuery.post('badge_artwork_edit.php', req, function(r) {
			if (r['badge_artwork_fields']) {
				deleteAllFields();
				badge_artwork_fields = r['badge_artwork_fields'];
				initViews();
				window.setTimeout(refitTextFields, 50);
				cmui.showButterbarPersistent('Layout imported.');
			} else {
				cmui.showButterbarPersistent('Bad response.');
			}
		}, 'json');
	};
	var saveChanges = function() {
		cmui.showButterbar('Saving...');
		var req = {
			'action': 'save',
			'id': badge_artwork_id,
			'checked_badge_types': JSON.stringify(pullBadgeTypes()),
			'badge_artwork_fields': JSON.stringify(badge_artwork_fields)
		};
		jQuery.post('badge_artwork_edit.php', req, cmui.showButterbarPersistent);
	};
	
	var bindInput = function(e, f) {
		e.change(f);
		e.keydown(f);
		e.keyup(f);
	};
	var bindSelect = function(e, f) {
		e.change(f);
		e.keydown(f);
		e.keyup(f);
		e.mousedown(f);
		e.mouseup(f);
	};
	
	var init = function() {
		finishEditing();
		initViews();
		window.setTimeout(refitTextFields, 50);
		$(window).resize(refitTextFields);
		$('.badge-artwork-container').mousedown(finishEditing);
		$('.badge-artwork-fields').mousedown(startCreateDrag);
		bindSelect($('#field-type'), continueEditing);
		bindInput($('#font-family'), continueEditing);
		bindInput($('#font-weight-bold'), continueEditing);
		bindInput($('#font-style-italic'), continueEditing);
		bindInput($('#color'), continueEditing);
		bindInput($('#background'), continueEditing);
		bindInput($('#color-minors'), continueEditing);
		bindInput($('#background-minors'), continueEditing);
		$('body').keydown(function(event) {
			if (event.ctrlKey || event.metaKey || cmui.focusedOnInput()) return;
			switch (event.which) {
				case 27: case 12:
					finishEditing();
					event.stopPropagation();
					event.preventDefault();
					break;
				case 8: case 46:
					deleteSelected();
					event.stopPropagation();
					event.preventDefault();
					break;
				case 37: case 38:
					selectPrev();
					event.stopPropagation();
					event.preventDefault();
					break;
				case 39: case 40:
					selectNext();
					event.stopPropagation();
					event.preventDefault();
					break;
			}
		});
		$('body').keyup(function(event) {
			if (event.ctrlKey || event.metaKey || cmui.focusedOnInput()) return;
			switch (event.which) {
				case 27: case 12:
				case 8: case 46:
				case 37: case 38:
				case 39: case 40:
					event.stopPropagation();
					event.preventDefault();
					break;
			}
		});
		$('#import-layout-button').click(importLayout);
		$('#save-changes').click(saveChanges);
	}
	init();
});
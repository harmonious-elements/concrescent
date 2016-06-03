cm_departments_ui = (function($,window,document,cmui){

	var loadParentDepartments = function(parentId) {
		cmui.showButterbar('Loading departments...');
		var parentIdSelect = $('#ea-parent-id');
		parentIdSelect.val(parentId || '');
		$.post(
			'departments.php',
			{'cm-list-action': 'list'},
			function(response) {
				if (!response['ok']) {
					cmui.showButterbarPersistent('An error occurred. Please reload the page.');
				} else {
					parentIdSelect.empty();
					parentIdSelect.append($('<option value="">(None)</option>'));
					for (var i = 0, n = response['rows'].length; i < n; ++i) {
						var id = response['rows'][i]['entity']['id'];
						var name = response['rows'][i]['entity']['name'];
						parentIdSelect.append($('<option/>').attr('value', id).text(name));
					}
					parentIdSelect.val(parentId || '');
					cmui.hideButterbar();
				}
			},
			'json'
		);
	};

	var saveParentDepartment = function() {
		var val = $('#ea-parent-id').val();
		return val ? (1 * val) : null;
	};

	var loadPositions = function(positions) {
		var positionsRow = $('#ea-positions');
		var moveUpRow = function(row) {
			return function() {
				var prevRow = row.prevAll('div:eq(0)');
				if (prevRow.length) prevRow.before(row);
			};
		};
		var moveDownRow = function(row) {
			return function() {
				var nextRow = row.nextAll('div:eq(0)');
				if (nextRow.length) nextRow.after(row);
			};
		};
		var deleteRow = function(row) {
			return function() {
				row.remove();
				if (!positionsRow.text()) {
					var e = $('#ea-position-none div').clone();
					positionsRow.append(e);
				}
			};
		};

		positionsRow.empty();
		if (positions && positions.length) {
			for (var i = 0, n = positions.length; i < n; ++i) {
				var position = positions[i];
				var e = $('#ea-position-template div').clone();
				e.find('.ea-position-name').val(position['name'] || '');
				e.find('.ea-position-executive').prop('checked', !!position['executive']);
				e.find('.ea-position-active').prop('checked', !!position['active']);
				e.find('.ea-position-id').val(position['id'] || '');
				positionsRow.append(e);
				e.find('.up-button').bind('click', moveUpRow(e));
				e.find('.down-button').bind('click', moveDownRow(e));
				e.find('.delete-button').bind('click', deleteRow(e));
			}
		} else {
			var e = $('#ea-position-none div').clone();
			positionsRow.append(e);
		}

		$('#ea-position-add .add-button').unbind('click').bind('click', function(event) {
			if (positionsRow.text() === 'None') positionsRow.empty();
			var e = $('#ea-position-template div').clone();
			if (event.altKey) e.find('.ea-position-name').val('Dynamic Lead');
			if (event.shiftKey) e.find('.ea-position-executive').prop('checked', true);
			positionsRow.append(e);
			e.find('.ea-position-name').focus();
			e.find('.up-button').bind('click', moveUpRow(e));
			e.find('.down-button').bind('click', moveDownRow(e));
			e.find('.delete-button').bind('click', deleteRow(e));
		});
	};

	var savePositions = function() {
		var positionsRow = $('#ea-positions');
		if (positionsRow.text() === 'None') return null;
		var positions = [];
		positionsRow.find('div').each(function() {
			var e = $(this);
			var name = e.find('.ea-position-name').val();
			var executive = e.find('.ea-position-executive').is(':checked');
			var active = e.find('.ea-position-active').is(':checked');
			var id = e.find('.ea-position-id').val();
			positions.push({
				'name': (name || ''),
				'executive': !!executive,
				'active': !!active,
				'id': (id ? (1 * id) : null)
			});
		});
		return positions;
	};

	var clear = function() {
		loadParentDepartments();
		$('#ea-name').val('');
		$('#ea-description').val('');
		$('#ea-mail-alias-1').val('');
		$('#ea-mail-alias-2').val('');
		$('#ea-mail-depth').val('Executive');
		$('#ea-active').prop('checked', true);
		loadPositions([
			{ 'name': 'Lead', 'executive': true, 'active': true },
			{ 'name': 'Second', 'executive': true, 'active': true }
		]);
	};

	var load = function(id, e) {
		loadParentDepartments(e['parent-id']);
		$('#ea-name').val(e['name']);
		$('#ea-description').val(e['description']);
		$('#ea-mail-alias-1').val(e['mail-alias-1']);
		$('#ea-mail-alias-2').val(e['mail-alias-2']);
		$('#ea-mail-depth').val(e['mail-depth']);
		$('#ea-active').prop('checked', !!e['active']);
		loadPositions(e['positions']);
	};

	var save = function(id, e) {
		return {
			'parent-id': saveParentDepartment(),
			'name': $('#ea-name').val(),
			'description': $('#ea-description').val(),
			'mail-alias-1': $('#ea-mail-alias-1').val() || null,
			'mail-alias-2': $('#ea-mail-alias-2').val() || null,
			'mail-depth': $('#ea-mail-depth').val() || null,
			'active': $('#ea-active').is(':checked'),
			'positions': savePositions()
		};
	};

	return { clear: clear, load: load, save: save };

})(jQuery,window,document,cmui);
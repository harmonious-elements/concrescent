(function($,window,document,positions){
	var loadPositions = function(positions) {
		var nextRowId = 1;
		var positionsRow = $('#ea-positions');
		var allDepartmentOptions = $('#ea-position-template .ea-department-id option');
		var allPositionOptions = $('#ea-position-template .ea-position-id option');
		var otherPositionOption = allPositionOptions.filter('[selected]');

		var moveUpRow = function(row) {
			return function(event) {
				var prevRow = row.prevAll('div:eq(0)');
				if (prevRow.length) prevRow.before(row);
				event.stopPropagation();
				event.preventDefault();
			};
		};
		var moveDownRow = function(row) {
			return function(event) {
				var nextRow = row.nextAll('div:eq(0)');
				if (nextRow.length) nextRow.after(row);
				event.stopPropagation();
				event.preventDefault();
			};
		};
		var deleteRow = function(row) {
			return function(event) {
				row.remove();
				if (!positionsRow.text()) {
					var e = $('#ea-position-none div').clone();
					positionsRow.append(e);
				}
				event.stopPropagation();
				event.preventDefault();
			};
		};

		var bindRow = function(row, position) {
			var departmentIdInput = row.find('.ea-department-id');
			var departmentNameInput = row.find('.ea-department-name');
			var positionIdInput = row.find('.ea-position-id');
			var positionNameInput = row.find('.ea-position-name');
			departmentIdInput.attr('name', 'assigned-department-id-' + nextRowId);
			departmentNameInput.attr('name', 'assigned-department-name-' + nextRowId);
			positionIdInput.attr('name', 'assigned-position-id-' + nextRowId);
			positionNameInput.attr('name', 'assigned-position-name-' + nextRowId);
			nextRowId++;

			departmentIdInput.val(position && position['department-id'] || '');
			var departmentIdOldVal = departmentIdInput.val();
			var departmentIdChanged = function(event) {
				var departmentIdNewVal = departmentIdInput.val();
				if (departmentIdNewVal != departmentIdOldVal || !event) {
					departmentIdOldVal = departmentIdNewVal;
					if (departmentIdNewVal) {
						departmentNameInput.val(allDepartmentOptions.filter('[value='+departmentIdNewVal+']').text());
						departmentNameInput.addClass('hidden');
					} else {
						departmentNameInput.val('');
						departmentNameInput.removeClass('hidden');
					}
					positionIdInput.empty();
					if (departmentIdNewVal) {
						positionIdInput.append(allPositionOptions.filter('[data-parent-id='+departmentIdNewVal+']').clone());
					}
					positionIdInput.append(otherPositionOption.clone());
					positionIdInput.val('');
					positionNameInput.val('');
					positionNameInput.removeClass('hidden');
				}
			};
			departmentIdInput.bind('change', departmentIdChanged);
			departmentIdInput.bind('keydown', departmentIdChanged);
			departmentIdInput.bind('keyup', departmentIdChanged);
			departmentIdInput.bind('mousedown', departmentIdChanged);
			departmentIdInput.bind('mouseup', departmentIdChanged);
			departmentIdChanged();
			departmentNameInput.val(position && position['department-name'] || '');

			positionIdInput.val(position && position['position-id'] || '');
			var positionIdOldVal = positionIdInput.val();
			var positionIdChanged = function(event) {
				var positionIdNewVal = positionIdInput.val();
				if (positionIdNewVal != positionIdOldVal || !event) {
					positionIdOldVal = positionIdNewVal;
					if (positionIdNewVal) {
						positionNameInput.val(allPositionOptions.filter('[value='+positionIdNewVal+']').text());
						positionNameInput.addClass('hidden');
					} else {
						positionNameInput.val('');
						positionNameInput.removeClass('hidden');
					}
				}
			};
			positionIdInput.bind('change', positionIdChanged);
			positionIdInput.bind('keydown', positionIdChanged);
			positionIdInput.bind('keyup', positionIdChanged);
			positionIdInput.bind('mousedown', positionIdChanged);
			positionIdInput.bind('mouseup', positionIdChanged);
			positionIdChanged();
			positionNameInput.val(position && position['position-name'] || '');

			row.find('.up-button').bind('click', moveUpRow(row));
			row.find('.down-button').bind('click', moveDownRow(row));
			row.find('.delete-button').bind('click', deleteRow(row));
		};

		positionsRow.empty();
		if (positions && positions.length) {
			for (var i = 0, n = positions.length; i < n; ++i) {
				var row = $('#ea-position-template div').clone();
				positionsRow.append(row);
				bindRow(row, positions[i]);
			}
		} else {
			var row = $('#ea-position-none div').clone();
			positionsRow.append(row);
		}

		$('#ea-position-add .add-button').unbind('click').bind('click', function(event) {
			if (positionsRow.text() === 'None') positionsRow.empty();
			var row = $('#ea-position-template div').clone();
			positionsRow.append(row);
			bindRow(row);
			if (event.altKey) {
				row.find('.ea-department-name').val('Academy of Brony Arts & Science');
				row.find('.ea-position-name').val('Dynamic Lead');
			}
			row.find('.ea-department-id').focus();
			event.stopPropagation();
			event.preventDefault();
		});
	};

	$(document).ready(function() {
		var addToBlacklistAddedBy = $('.cm-add-to-blacklist-added-by');
		var addToAttendeeBlacklist = $('input[type=checkbox][name=add-to-attendee-blacklist]');
		var addToStaffBlacklist = $('input[type=checkbox][name=add-to-staff-blacklist]');
		var addToBlacklistChanged = function() {
			var checked = (
				addToAttendeeBlacklist.is(':checked') ||
				addToStaffBlacklist.is(':checked')
			);
			if (checked) addToBlacklistAddedBy.removeClass('hidden');
			else addToBlacklistAddedBy.addClass('hidden');
		};
		addToAttendeeBlacklist.bind('click', addToBlacklistChanged);
		addToStaffBlacklist.bind('click', addToBlacklistChanged);

		var resendApplicationEmail = $('input[type=checkbox][name=resend-application-email]');
		var applicationStatus = $('#application-status');
		var applicationStatusOldVal = applicationStatus.val();
		var applicationStatusChanged = function() {
			var applicationStatusNewVal = applicationStatus.val();
			if (applicationStatusNewVal != applicationStatusOldVal) {
				applicationStatusOldVal = applicationStatusNewVal;
				resendApplicationEmail.prop('checked', (applicationStatusNewVal != 'Cancelled'));
			}
		};
		applicationStatus.bind('change', applicationStatusChanged);
		applicationStatus.bind('keydown', applicationStatusChanged);
		applicationStatus.bind('keyup', applicationStatusChanged);
		applicationStatus.bind('mousedown', applicationStatusChanged);
		applicationStatus.bind('mouseup', applicationStatusChanged);

		var resendPaymentEmail = $('input[type=checkbox][name=resend-payment-email]');
		var paymentStatus = $('#payment-status');
		var paymentStatusOldVal = paymentStatus.val();
		var paymentStatusChanged = function() {
			var paymentStatusNewVal = paymentStatus.val();
			if (paymentStatusNewVal != paymentStatusOldVal) {
				paymentStatusOldVal = paymentStatusNewVal;
				resendPaymentEmail.prop('checked', (paymentStatusNewVal == 'Completed'));
			}
		};
		paymentStatus.bind('change', paymentStatusChanged);
		paymentStatus.bind('keydown', paymentStatusChanged);
		paymentStatus.bind('keyup', paymentStatusChanged);
		paymentStatus.bind('mousedown', paymentStatusChanged);
		paymentStatus.bind('mouseup', paymentStatusChanged);

		loadPositions(positions);

		$('body').bind('keydown', function(event) {
			switch (event.which) {
				case 27:
					window.close();
					break;
				case 83:
					if (!event.shiftKey || !(event.ctrlKey || event.metaKey)) return;
					var e = $('input[type=submit]');
					if (e.length == 1) e.click();
					break;
				default:
					return;
			}
			event.stopPropagation();
			event.preventDefault();
		});
	});
})(jQuery,window,document,cm_assigned_positions);
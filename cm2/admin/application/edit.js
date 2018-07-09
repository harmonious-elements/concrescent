(function($,window,document,cmui,assignments){
	var reformatDateTime = function(s) {
		if (s) {
			var f = s.split(/[^0-9]+/g);
			if (f[0] && f[1] && f[2] && f[3] && f[4]) {
				var yr  = ('0000'+f[0]).substr(-4);
				var mon = ('00' + f[1]).substr(-2);
				var day = ('00' + f[2]).substr(-2);
				var hr  = ('00' + f[3]).substr(-2);
				var min = ('00' + f[4]).substr(-2);
				var date = yr + '-' + mon + '-' + day;
				var time = hr + ':' + min;
				return date + 'T' + time;
			}
		}
		return '';
	};

	var loadAssignments = function(assignments) {
		var nextRowId = 1;
		var assignmentsRow = $('#ea-assignments');
		var selectRow = function(row) {
			var roomOrTableIdInput = row.find('.ea-assignment-room-or-table-id');
			return function(event) {
				$('.room-table-select-dialog .cancel-select-button').unbind('click').bind('click', cmui.hideDialog);
				$('.room-table-select-dialog .confirm-select-button').each(function() {
					var self = $(this);
					self.unbind('click').bind('click', function() {
						roomOrTableIdInput.val(self.text());
						cmui.hideDialog();
					});
				});
				cmui.showDialog('room-table-select');
				event.stopPropagation();
				event.preventDefault();
			};
		};
		var deleteRow = function(row) {
			return function(event) {
				row.remove();
				if (!assignmentsRow.text()) {
					var e = $('#ea-assignment-none div').clone();
					assignmentsRow.append(e);
				}
				event.stopPropagation();
				event.preventDefault();
			};
		};
		var bindRow = function(row, assignment) {
			var roomOrTableIdInput = row.find('.ea-assignment-room-or-table-id');
			var startTimeInput = row.find('.ea-assignment-start-time');
			var endTimeInput = row.find('.ea-assignment-end-time');
			roomOrTableIdInput.attr('name', 'assignment-room-or-table-id-' + nextRowId);
			startTimeInput.attr('name', 'assignment-start-time-' + nextRowId);
			endTimeInput.attr('name', 'assignment-end-time-' + nextRowId);
			if (assignment) {
				roomOrTableIdInput.val(assignment['room-or-table-id']);
				startTimeInput.val(reformatDateTime(assignment['start-time']));
				endTimeInput.val(reformatDateTime(assignment['end-time']));
			}
			row.find('.select-button').bind('click', selectRow(row));
			row.find('.delete-button').bind('click', deleteRow(row));
			nextRowId++;
		};
		assignmentsRow.empty();
		if (assignments && assignments.length) {
			for (var i = 0, n = assignments.length; i < n; ++i) {
				var row = $('#ea-assignment-template div').clone();
				assignmentsRow.append(row);
				bindRow(row, assignments[i]);
			}
		} else {
			var row = $('#ea-assignment-none div').clone();
			assignmentsRow.append(row);
		}
		$('#ea-assignment-add .add-button').unbind('click').bind('click', function(event) {
			if (assignmentsRow.text() === 'None') assignmentsRow.empty();
			var row = $('#ea-assignment-template div').clone();
			assignmentsRow.append(row);
			bindRow(row);
			row.find('.ea-assignment-room-or-table-id').focus();
			event.stopPropagation();
			event.preventDefault();
		});
	};

	$(document).ready(function() {
		var addToBlacklistFields = $('.cm-add-to-blacklist-fields');
		var addToBlacklist = $('input[type=checkbox][name=add-to-blacklist]');
		addToBlacklist.bind('click', function() {
			var checked = addToBlacklist.is(':checked');
			if (checked) addToBlacklistFields.removeClass('hidden');
			else addToBlacklistFields.addClass('hidden');
		});

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

		loadAssignments(assignments);

		$('body').bind('keydown', function(event) {
			if (!$('.dialog-cover').hasClass('hidden')) return;
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
})(jQuery,window,document,cmui,cm_assigned_rooms_and_tables);
(function($,window,document,cmui,listdef,eventInfo,ctxInfo){
	var doAjax = function(request, done, message, errorMessage) {
		cmui.showButterbar(message);
		$.post(
			'', request,
			function(response) {
				if (!response['ok']) {
					cmui.showButterbarPersistent(errorMessage);
				} else {
					if (done) done(response);
					cmui.hideButterbar();
				}
			},
			'json'
		);
	};
	var setRect = function(element, x1, y1, x2, y2) {
		element.css('top', (Math.min(y1, y2) * 100) + '%');
		element.css('left', (Math.min(x1, x2) * 100) + '%');
		element.css('right', ((1 - Math.max(x1, x2)) * 100) + '%');
		element.css('bottom', ((1 - Math.max(y1, y2)) * 100) + '%');
	};
	var makeApplicationLink = function(assignment) {
		var contextUC = assignment['context'].toUpperCase();
		var contextLC = assignment['context'].toLowerCase();
		var contextID = assignment['context-id'];
		var appName = assignment['application-name'];
		var link = $('<a/>');
		link.attr('href', 'edit.php?c=' + contextLC + '&id=' + contextID);
		link.attr('target', '_blank');
		link.text(appName || ('[' + contextUC + 'A' + contextID + ']'));
		return link;
	};

	var dateTimeReformat = function(s) {
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
	var dateTimeMinuteDiff = function(a, b) {
		var aa = String(a).split(/[^0-9]+/g);
		var bb = String(b).split(/[^0-9]+/g);
		var ad = new Date(aa[0], aa[1], aa[2], aa[3]||0, aa[4]||0, aa[5]||0, aa[6]||0);
		var bd = new Date(bb[0], bb[1], bb[2], bb[3]||0, bb[4]||0, bb[5]||0, bb[6]||0);
		var diff = ad.getTime() - bd.getTime();
		return Math.round(diff / 60000);
	};
	var dateTimeCompare = function(a, b) {
		var aa = String(a).split(/[^0-9]+/g);
		var bb = String(b).split(/[^0-9]+/g);
		for (var i = 0; aa[i] && bb[i]; ++i) {
			var an = Number(aa[i]);
			var bn = Number(bb[i]);
			var cmp = an - bn;
			if (cmp) return cmp;
		}
		return aa.length - bb.length;
	};
	var assignmentTimeString = function(startTime, endTime) {
		if (!startTime) startTime = eventInfo['start_date'] + ' 00:00:00';
		if (!endTime) endTime = eventInfo['end_date'] + ' 23:59:59';
		if (
			dateTimeCompare(startTime, eventInfo['start_date'] + ' 00:02:00') < 0 &&
			dateTimeCompare(endTime, eventInfo['end_date'] + ' 23:58:00') >= 0
		) {
			return 'Ongoing';
		}
		startTime = startTime.split(/[Tt ]+/g);
		endTime = endTime.split(/[Tt ]+/g);
		if (
			dateTimeCompare(startTime[1], '00:02:00') < 0 &&
			dateTimeCompare(endTime[1], '23:58:00') >= 0
		) {
			if (dateTimeCompare(startTime[0], endTime[0]) == 0) {
				return startTime[0];
			} else {
				return startTime[0] + ' \u2014 ' + endTime[0];
			}
		}
		var startTime1 = startTime[1].split(/:/g);
		var endTime1 = endTime[1].split(/:/g);
		startTime[1] = (
			startTime1[0] + ':' + startTime1[1] + ' (' +
			((startTime1[0] % 12) ? (startTime1[0] % 12) : 12) + ':' +
			startTime1[1] + ((startTime1[0] < 12) ? ' AM' : ' PM') + ')'
		);
		endTime[1] = (
			endTime1[0] + ':' + endTime1[1] + ' (' +
			((endTime1[0] % 12) ? (endTime1[0] % 12) : 12) + ':' +
			endTime1[1] + ((endTime1[0] < 12) ? ' AM' : ' PM') + ')'
		);
		if (dateTimeCompare(startTime[0], endTime[0]) == 0) {
			return startTime[0] + ' ' + startTime[1] + ' \u2014 ' + endTime[1];
		} else {
			return startTime[0] + ' ' + startTime[1] + ' \u2014 ' + endTime[0] + ' ' + endTime[1];
		}
	};

	var naturalTokenize = function(text) {
		var tokens = [];
		var re = /[0-9]+|[^0-9]+/g;
		var m = re.exec(text);
		while (m) {
			tokens.push(m[0]);
			m = re.exec(text);
		}
		return tokens;
	};
	var naturalCompare = function(a, b) {
		var aa = naturalTokenize(String(a).toLowerCase());
		var bb = naturalTokenize(String(b).toLowerCase());
		for (var i = 0; aa[i] && bb[i]; ++i) {
			if (aa[i] != bb[i]) {
				var an = Number(aa[i]);
				var bn = Number(bb[i]);
				if (isFinite(an) && isFinite(bn)) {
					return (an < bn) ? -1 : 1;
				} else {
					return (aa[i] < bb[i]) ? -1 : 1;
				}
			}
		}
		return aa.length - bb.length;
	};

	$(document).ready(function() {
		var tagArea = $('.card-content .tags');
		var tbodyByRT = $('.cm-assignments-by-room-or-table');
		var tbodyByAN = $('.cm-assignments-by-application-name');
		var calendarArea = $('.calendar-body');
		var tagsLoad, assignmentsLoad;
		var pageKeyEvent, editorKeyEvent, selectAppKeyEvent, selectRTKeyEvent;

		var openEditor = function(assignment) {
			$('#ea-old-context').val(assignment['context'] || '');
			$('#ea-old-context-id').val(assignment['context-id'] || '');
			$('#ea-context').val(assignment['context'] || '');
			$('#ea-context-id').val(assignment['context-id'] || '');
			$('#ea-context-id-string').val(
				(assignment['context'] && assignment['context-id']) ?
				(assignment['context'] + 'A' + assignment['context-id']) : ''
			);
			$('#ea-old-room-or-table-id').val(assignment['room-or-table-id'] || '');
			$('#ea-old-start-time').val(assignment['start-time'] || '');
			$('#ea-old-end-time').val(assignment['end-time'] || '');
			$('#ea-room-or-table-id').val(assignment['room-or-table-id'] || '');
			$('#ea-start-time').val(dateTimeReformat(assignment['start-time']));
			$('#ea-end-time').val(dateTimeReformat(assignment['end-time']));
			cmui.showDialog('edit-assignment');
			$('body').unbind('keydown').bind('keydown', editorKeyEvent);
		};
		var pullFromEditor = function(doDelete, doCreate) {
			return {
				'action': 'update-assignment',
				'old-context': (doDelete && $('#ea-old-context').val() || ''),
				'old-context-id': (doDelete && $('#ea-old-context-id').val() || ''),
				'old-room-or-table-id': (doDelete && $('#ea-old-room-or-table-id').val() || ''),
				'old-start-time': (doDelete && $('#ea-old-start-time').val() || ''),
				'old-end-time': (doDelete && $('#ea-old-end-time').val() || ''),
				'context': (doCreate && $('#ea-context').val() || ''),
				'context-id': (doCreate && $('#ea-context-id').val() || ''),
				'room-or-table-id': (doCreate && $('#ea-room-or-table-id').val() || ''),
				'start-time': (doCreate && $('#ea-start-time').val() || ''),
				'end-time': (doCreate && $('#ea-end-time').val() || '')
			};
		};
		$('.edit-dialog-select-application-button').bind('click', function() {
			cmui.showDialog('select-application');
			$('#cm-search-input').focus();
			$('body').unbind('keydown').bind('keydown', selectAppKeyEvent);
		});
		$('.edit-dialog-select-room-or-table-button').bind('click', function() {
			cmui.showDialog('select-room-table');
			$('body').unbind('keydown').bind('keydown', selectRTKeyEvent);
		});
		$('.edit-dialog-delete-button').bind('click', function() {
			doAjax(
				pullFromEditor(true, false),
				function() {
					cmui.hideDialog();
					tagsLoad();
					assignmentsLoad();
					$('body').unbind('keydown').bind('keydown', pageKeyEvent);
				},
				'Deleting assignment...',
				'An error occurred. Please reload the page.'
			);
		});
		$('.edit-dialog-cancel-button').bind('click', function() {
			cmui.hideDialog();
			$('body').unbind('keydown').bind('keydown', pageKeyEvent);
		});
		$('.edit-dialog-save-button').bind('click', function() {
			doAjax(
				pullFromEditor(true, true),
				function() {
					cmui.hideDialog();
					tagsLoad();
					assignmentsLoad();
					$('body').unbind('keydown').bind('keydown', pageKeyEvent);
				},
				'Updating assignment...',
				'An error occurred. Please reload the page.'
			);
		});
		listdef['select-function'] = function(id) {
			$('#ea-context').val(ctxInfo['ctx_uc']);
			$('#ea-context-id').val(id);
			$('#ea-context-id-string').val(ctxInfo['ctx_uc'] + 'A' + id);
			cmui.showDialog('edit-assignment');
			$('body').unbind('keydown').bind('keydown', editorKeyEvent);
		};
		$('.select-application-dialog-cancel-button').bind('click', function() {
			cmui.showDialog('edit-assignment');
			$('body').unbind('keydown').bind('keydown', editorKeyEvent);
		});
		$('.select-room-table-dialog-select-button').each(function() {
			var self = $(this);
			self.bind('click', function() {
				$('#ea-room-or-table-id').val(self.text());
				cmui.showDialog('edit-assignment');
				$('body').unbind('keydown').bind('keydown', editorKeyEvent);
			});
		});
		$('.select-room-table-dialog-cancel-button').bind('click', function() {
			cmui.showDialog('edit-assignment');
			$('body').unbind('keydown').bind('keydown', editorKeyEvent);
		});

		var tagsMouseBind = function(element, tag) {
			var tagsMouseEvent = function(event) {
				openEditor({
					'room-or-table-id': tag['id'],
					'start-time': eventInfo['start_date'] + ' 00:00:00',
					'end-time': eventInfo['end_date'] + ' 23:59:59'
				});
				event.stopPropagation();
				event.preventDefault();
			};
			element.bind('mousedown', tagsMouseEvent);
			element.find('label').bind('mousedown', tagsMouseEvent);
		};
		tagsLoad = function(done) {
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
								var title = (
									tags[i]['assignments'][0]['application-name'] || (
										'[' + tags[i]['assignments'][0]['context'] + 'A' +
										tags[i]['assignments'][0]['context-id'] + ']'
									)
								);
								for (var j = 1, m = tags[i]['assignments'].length; j < m; ++j) {
									title += '\n' + (
										tags[i]['assignments'][j]['application-name'] || (
											'[' + tags[i]['assignments'][j]['context'] + 'A' +
											tags[i]['assignments'][j]['context-id'] + ']'
										)
									);
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

		var assignmentsMouseBind = function(element, assignment) {
			element.bind('mousedown', function(event) {
				openEditor(assignment);
				event.stopPropagation();
				event.preventDefault();
			});
			element.find('a').bind('mousedown', function(event) {
				event.stopPropagation();
			});
		};
		var calendarDayMouseBind = function(element, rtid) {
			var tempEvent = null;
			var tempEventContent = null;
			var firstClickY = 0;
			var maxTime = 1440 * eventInfo['dates'].length;
			var calendarDayMouseEvent = function(event) {
				if (tempEvent && tempEventContent) {
					var lastClickY = event.pageY - element.offset().top;
					var startTime = 15 * Math.floor(Math.min(firstClickY, lastClickY) / 15);
					var endTime = 15 * Math.ceil(Math.max(firstClickY, lastClickY) / 15);
					if (startTime < 0) startTime = 0;
					if (!(startTime % 1440)) startTime++;
					if (startTime > maxTime) startTime = maxTime;
					if (endTime > maxTime) endTime = maxTime;
					if (!(endTime % 1440)) endTime--;
					if (endTime < 0) endTime = 0;
					var startTimeString = (
						eventInfo['dates'][Math.floor(startTime / 1440)] + ' ' +
						('00' + Math.floor((startTime % 1440) / 60)).substr(-2) + ':' +
						('00' + (startTime % 60)).substr(-2) + ':00'
					);
					var endTimeString = (
						eventInfo['dates'][Math.floor(endTime / 1440)] + ' ' +
						('00' + Math.floor((endTime % 1440) / 60)).substr(-2) + ':' +
						('00' + (endTime % 60)).substr(-2) + ':00'
					);
					tempEvent.css('top', startTime + 'px');
					tempEvent.css('height', (endTime - startTime) + 'px');
					tempEventContent.text(assignmentTimeString(startTimeString, endTimeString));
					return [startTimeString, endTimeString];
				}
			};
			var calendarDayMouseDown, calendarDayMouseDrag, calendarDayMouseUp;
			calendarDayMouseDown = function(event) {
				tempEvent = $('<div/>').addClass('calendar-event');
				tempEventContent = $('<div/>').addClass('calendar-event-content');
				firstClickY = event.pageY - element.offset().top;
				calendarDayMouseEvent(event);
				tempEvent.append(tempEventContent);
				element.append(tempEvent);
				$(document).unbind('mousemove').bind('mousemove', calendarDayMouseDrag);
				$(document).unbind('mouseup').bind('mouseup', calendarDayMouseUp);
				event.stopPropagation();
				event.preventDefault();
			};
			calendarDayMouseDrag = function(event) {
				calendarDayMouseEvent(event);
				event.stopPropagation();
				event.preventDefault();
			};
			calendarDayMouseUp = function(event) {
				var time = calendarDayMouseEvent(event);
				if (tempEvent) { tempEvent.remove(); tempEvent = null; }
				if (tempEventContent) { tempEventContent.remove(); tempEventContent = null; }
				openEditor({
					'room-or-table-id': rtid,
					'start-time': time[0],
					'end-time': time[1]
				});
				$(document).unbind('mousemove');
				$(document).unbind('mouseup');
				event.stopPropagation();
				event.preventDefault();
			};
			element.bind('mousedown', calendarDayMouseDown);
		};
		assignmentsLoad = function(done) {
			doAjax(
				{'action': 'list-assignments'},
				function(response) {
					tbodyByRT.empty();
					tbodyByAN.empty();
					calendarArea.empty();
					var assignments = response['assignments'];
					if (assignments && assignments.length) {

						var lastRTID = null;
						for (var i = 0, n = assignments.length; i < n; ++i) {
							var assignment = assignments[i];
							var row = $('<tr/>');
							if (assignment['room-or-table-id'] === lastRTID) {
								row.append($('<td/>'));
							} else {
								lastRTID = assignment['room-or-table-id'];
								row.append($('<td/>').text(lastRTID));
							}
							row.append($('<td/>').text(
								assignmentTimeString(
									assignment['start-time'],
									assignment['end-time']
								)
							));
							var linkCell = $('<td/>');
							var link = makeApplicationLink(assignment);
							linkCell.append(link);
							row.append(linkCell);
							tbodyByRT.append(row);
							assignmentsMouseBind(row, assignment);
						}

						var calendarRTID = null;
						var calendarColumnBody = null;
						var eventStartTime = eventInfo['start_date'] + ' 00:00:00';
						var eventEndTime = eventInfo['end_date'] + ' 23:59:59';
						for (var i = 0, n = assignments.length; i < n; ++i) {
							var assignment = assignments[i];
							if (assignment['room-or-table-id'] !== calendarRTID) {
								calendarRTID = assignment['room-or-table-id'];
								var calendarColumnHeader = $('<div/>').addClass('calendar-column-header');
								calendarColumnBody = $('<div/>').addClass('calendar-column-body');
								var calendarColumn = $('<div/>').addClass('calendar-column');
								calendarColumnHeader.text(calendarRTID);
								calendarColumn.append(calendarColumnHeader);
								calendarColumn.append(calendarColumnBody);
								calendarArea.append(calendarColumn);
								calendarDayMouseBind(calendarColumnBody, calendarRTID);
							}
							var calendarEvent = $('<div/>').addClass('calendar-event');
							var calendarEventContent = $('<div/>').addClass('calendar-event-content');
							var link = makeApplicationLink(assignment);
							calendarEventContent.append(link);
							calendarEvent.append(calendarEventContent);
							calendarEvent.css('top', dateTimeMinuteDiff(
								assignment['start-time'] || eventStartTime,
								eventStartTime
							) + 'px');
							calendarEvent.css('height', dateTimeMinuteDiff(
								assignment['end-time'] || eventEndTime,
								assignment['start-time'] || eventStartTime
							) + 'px');
							calendarColumnBody.append(calendarEvent);
							assignmentsMouseBind(calendarEvent, assignment);
						}

						assignments.sort(function(a, b) {
							return (
								naturalCompare(a['application-name'], b['application-name']) ||
								(a['context-id'] - b['context-id']) ||
								dateTimeCompare(a['start-time'], b['start-time']) ||
								dateTimeCompare(a['end-time'], b['end-time']) ||
								naturalCompare(a['room-or-table-id'], b['room-or-table-id'])
							);
						});

						var lastCtxID = null;
						for (var i = 0, n = assignments.length; i < n; ++i) {
							var assignment = assignments[i];
							var row = $('<tr/>');
							if (assignment['context-id'] === lastCtxID) {
								row.append($('<td/>'));
							} else {
								lastCtxID = assignment['context-id'];
								var linkCell = $('<td/>');
								var link = makeApplicationLink(assignment);
								linkCell.append(link);
								row.append(linkCell);
							}
							row.append($('<td/>').text(assignment['room-or-table-id']));
							row.append($('<td/>').text(
								assignmentTimeString(
									assignment['start-time'],
									assignment['end-time']
								)
							));
							tbodyByAN.append(row);
							assignmentsMouseBind(row, assignment);
						}

					}
					if (done) done(response);
				},
				'Loading...',
				'An error occurred. Please reload the page.'
			);
		};

		pageKeyEvent = function(event) {
			switch (event.which) {
				case 191:
					if (!event.shiftKey || !(event.ctrlKey || event.metaKey)) return;
					cmui.showDialog('shortcuts');
					break;
				default:
					return;
			}
			event.stopPropagation();
			event.preventDefault();
		};
		editorKeyEvent = function(event) {
			switch (event.which) {
				case 27:
					$('.edit-dialog-cancel-button').click();
					break;
				case 65:
					if (!event.shiftKey || !(event.ctrlKey || event.metaKey)) return;
					$('.edit-dialog-select-application-button').click();
					break;
				case 68:
					if (!event.shiftKey || !(event.ctrlKey || event.metaKey)) return;
					$('.edit-dialog-delete-button').click();
					break;
				case 82:
					if (!event.shiftKey || !(event.ctrlKey || event.metaKey)) return;
					$('.edit-dialog-select-room-or-table-button').click();
					break;
				case 83:
					if (!event.shiftKey || !(event.ctrlKey || event.metaKey)) return;
					$('.edit-dialog-save-button').click();
					break;
				default:
					return;
			}
			event.stopPropagation();
			event.preventDefault();
		};
		selectAppKeyEvent = function(event) {
			switch (event.which) {
				case 27:
					$('.select-application-dialog-cancel-button').click();
					break;
				case 33:
					$('.cm-search-prev-page').click();
					break;
				case 34:
					$('.cm-search-next-page').click();
					break;
				case 35:
					$('.cm-search-last-page').click();
					break;
				case 36:
					$('.cm-search-first-page').click();
					break;
				case 83:
					if (!event.shiftKey || !(event.ctrlKey || event.metaKey)) return;
					var e = $('.select-button');
					if (e.length == 1) e.click();
					break;
				default:
					return;
			}
			event.stopPropagation();
			event.preventDefault();
		};
		selectRTKeyEvent = function(event) {
			switch (event.which) {
				case 27:
					$('.select-room-table-dialog-cancel-button').click();
					break;
				default:
					return;
			}
			event.stopPropagation();
			event.preventDefault();
		};

		tagsLoad();
		assignmentsLoad();
		$('body').unbind('keydown').bind('keydown', pageKeyEvent);
	});
})(jQuery,window,document,cmui,cm_list_def,cm_app_event_info,cm_app_context_info);
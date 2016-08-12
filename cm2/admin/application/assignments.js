(function($,window,document,cmui,eventInfo,ctxInfo){

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
		if (dateTimeCompare(startTime[0], endTime[0]) == 0) {
			return startTime[0] + ' ' + startTime[1] + ' \u2014 ' + endTime[1];
		} else {
			return startTime[0] + ' ' + startTime[1] + ' \u2014 ' + endTime[0] + ' ' + endTime[1];
		}
	};

	var setRect = function(element, x1, y1, x2, y2) {
		element.css('top', (Math.min(y1, y2) * 100) + '%');
		element.css('left', (Math.min(x1, x2) * 100) + '%');
		element.css('right', ((1 - Math.max(x1, x2)) * 100) + '%');
		element.css('bottom', ((1 - Math.max(y1, y2)) * 100) + '%');
	};

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

	$(document).ready(function() {
		var tagArea = $('.tags');
		var tbodyByRT = $('.cm-assignments-by-room-or-table');
		var tbodyByAN = $('.cm-assignments-by-application-name');
		var calendarArea = $('.calendar-body');

		var tagsMouseBind = function(element, tag) {
			var tagsMouseEvent = function(event) {
				tagId = tag['id'];
				console.log(tagId);
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

		var calendarMouseBind = function(element, assignment) {
			var calendarMouseEvent = function(event) {
				console.log(assignment);
				event.stopPropagation();
				event.preventDefault();
			};
			element.bind('mousedown', calendarMouseEvent);
			var calendarLinkMouseEvent = function(event) {
				event.stopPropagation();
			};
			element.find('a').bind('mousedown', calendarLinkMouseEvent);
		};

		var assignmentsLoad = function(done) {
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
							var link = $('<a/>').attr('href',
								'edit.php?c=' + assignment['context'].toLowerCase() +
								'&id=' + assignment['context-id']
							).attr('target', '_blank').text(
								assignment['application-name'] || (
									'[' + assignment['context'] + 'A' +
									assignment['context-id'] + ']'
								)
							);
							linkCell.append(link);
							row.append(linkCell);
							tbodyByRT.append(row);
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
							}
							var calendarEvent = $('<div/>').addClass('calendar-event');
							var calendarEventContent = $('<div/>').addClass('calendar-event-content');
							var link = $('<a/>').attr('href',
								'edit.php?c=' + assignment['context'].toLowerCase() +
								'&id=' + assignment['context-id']
							).attr('target', '_blank').text(
								assignment['application-name'] || (
									'[' + assignment['context'] + 'A' +
									assignment['context-id'] + ']'
								)
							);
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
							calendarMouseBind(calendarEvent, assignment);
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
								var link = $('<a/>').attr('href',
									'edit.php?c=' + assignment['context'].toLowerCase() +
									'&id=' + assignment['context-id']
								).attr('target', '_blank').text(
									assignment['application-name'] || (
										'[' + assignment['context'] + 'A' +
										assignment['context-id'] + ']'
									)
								);
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
						}

					}
					if (done) done(response);
				},
				'Loading...',
				'An error occurred. Please reload the page.'
			);
		};

		tagsLoad();
		assignmentsLoad();
	});

})(jQuery,window,document,cmui,cm_app_event_info,cm_app_context_info);
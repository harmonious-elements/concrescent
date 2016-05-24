(function($,window,document,google,statData,columnCount){
	var data, view, options, chart;
	var columns = [0];
	for (var i = 2; i < columnCount; i += 4) {
		columns.push(i);
	}

	google.charts.load('current', {'packages': ['corechart']});
	google.charts.setOnLoadCallback(function() {
		data = google.visualization.arrayToDataTable(statData);
		view = new google.visualization.DataView(data);
		options = {
			'width': 900,
			'height': 300,
			'lineWidth': 1,
			'chartArea': {
				'top': 20,
				'left': 50,
				'width': 550,
				'height': 250
			},
			'explorer': {
				'axis': 'horizontal',
				'keepInBounds': true,
				'maxZoomIn': 0.05,
				'maxZoomOut': 1
			}
		};
		chart = new google.visualization.LineChart($('#cm-stat-chart')[0]);
		view.setColumns(columns);
		chart.draw(view, options);
	});

	$(document).ready(function() {
		var columnSelected = function(col) {
			for (var i = col; i < columnCount; i += 4) {
				if (columns.indexOf(i) < 0) return false;
			}
			return true;
		};
		var rowSelected = function(row) {
			for (var i = 0; i < 4; ++i) {
				if (columns.indexOf(row + i) < 0) return false;
			}
			return true;
		};
		var updateSelection = function() {
			$('.cm-stat-col').removeClass('cm-stat-selected');
			$('.cm-stat-row').removeClass('cm-stat-selected');
			$('.cm-stat').removeClass('cm-stat-selected');
			for (var i = 1; i <= 4; ++i) {
				if (columnSelected(i)) {
					$('#cm-stat-col-' + i).addClass('cm-stat-selected');
				}
			}
			for (var i = 1; i < columnCount; i += 4) {
				if (rowSelected(i)) {
					$('#cm-stat-row-' + i).addClass('cm-stat-selected');
				}
			}
			for (var i = 1, n = columns.length; i < n; ++i) {
				$('#cm-stat-' + columns[i]).addClass('cm-stat-selected');
			}
		};
		updateSelection();

		var updateChart = function() {
			if (columns.length < 2) {
				columns = [0];
				for (var i = 2; i < columnCount; i += 4) {
					columns.push(i);
				}
			}
			if (view && chart) {
				view.setColumns(columns);
				chart.draw(view, options);
			}
			updateSelection();
		};
		var toggleColumn = function(col) {
			return function() {
				if (columnSelected(col)) {
					for (var i = col; i < columnCount; i += 4) {
						var o = columns.indexOf(i);
						if (o >= 0) columns.splice(o, 1);
					}
				} else {
					for (var i = col; i < columnCount; i += 4) {
						var o = columns.indexOf(i);
						if (o < 0) columns.push(i);
					}
					columns.sort(function(a,b){return a-b;});
				}
				updateChart();
			};
		};
		var toggleRow = function(row) {
			return function() {
				if (rowSelected(row)) {
					for (var i = 0; i < 4; ++i) {
						var o = columns.indexOf(row + i);
						if (o >= 0) columns.splice(o, 1);
					}
				} else {
					for (var i = 0; i < 4; ++i) {
						var o = columns.indexOf(row + i);
						if (o < 0) columns.push(row + i);
					}
					columns.sort(function(a,b){return a-b;});
				}
				updateChart();
			};
		};
		var toggleCell = function(i) {
			return function() {
				var o = columns.indexOf(i);
				if (o >= 0) {
					columns.splice(o, 1);
				} else {
					columns.push(i);
					columns.sort(function(a,b){return a-b;});
				}
				updateChart();
			};
		};
		for (var i = 1; i <= 4; ++i) {
			$('#cm-stat-col-' + i).bind('click', toggleColumn(i));
		}
		for (var i = 1; i < columnCount; i += 4) {
			$('#cm-stat-row-' + i).bind('click', toggleRow(i));
		}
		for (var i = 1; i < columnCount; ++i) {
			$('#cm-stat-' + i).bind('click', toggleCell(i));
		}
	});
})(jQuery,window,document,google,cm_stat_data,cm_stat_count);
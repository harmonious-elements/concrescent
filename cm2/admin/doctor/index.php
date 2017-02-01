<html>
<head>
<meta charset="utf-8">
<title>CONcrescent Doctor</title>
<style>
	th { width: 100px; }
	.ok { background: green; color: white; }
	.wn { background: yellow; color: black; }
	.ng { background: red; color: white; }
</style>
<script src="../../lib/res/jquery.js"></script>
<script>
	$(document).ready(function() {
		$('tr').each(function(index) {
			var self = $(this);
			var testfile = self.attr('id') + '.php';
			setTimeout(function() {
				$.ajax({
					'method': 'GET',
					'url': testfile,
					'cache': false,
					'dataType': 'text',
					'success': function(d) {
						switch (d.substring(0, 3)) {
							case 'OK ':
								self.addClass('ok');
								self.find('th').text('PASSED');
								self.find('td').text(d.substring(3));
								break;
							case 'WN ':
								self.addClass('wn');
								self.find('th').text('NOTICE');
								self.find('td').text(d.substring(3));
								break;
							case 'NG ':
								self.addClass('ng');
								self.find('th').text('FAILED');
								self.find('td').text(d.substring(3));
								break;
							default:
								self.addClass('ng');
								self.find('th').text('FAILED');
								self.find('td').text('Test failed to run. Check earlier tests.');
								break;
						}
					},
					'error': function() {
						self.addClass('ng');
						self.find('th').text('FAILED');
						self.find('td').text('Test failed to run. Check earlier tests.');
					}
				});
			}, (index+1)*10);
		});
	});
</script>
</head>
<body>
<table border="1" cellspacing="0" cellpadding="4">
	<tr id="https">
		<th>CHECKING</th>
		<td>Checking HTTPS...</td>
	</tr>
	<tr id="phpversion">
		<th>CHECKING</th>
		<td>Checking PHP version...</td>
	</tr>
	<tr id="magicquotes">
		<th>CHECKING</th>
		<td>Checking Magic Quotes...</td>
	</tr>
	<tr id="config1">
		<th>CHECKING</th>
		<td>Checking configuration file can be loaded...</td>
	</tr>
	<tr id="config2">
		<th>CHECKING</th>
		<td>Checking all configuration sections are present...</td>
	</tr>
	<tr id="config3">
		<th>CHECKING</th>
		<td>Checking database configuration...</td>
	</tr>
	<tr id="config4">
		<th>CHECKING</th>
		<td>Checking PayPal configuration...</td>
	</tr>
	<tr id="config5">
		<th>CHECKING</th>
		<td>Checking default administrator user...</td>
	</tr>
	<tr id="database1">
		<th>CHECKING</th>
		<td>Checking database connection...</td>
	</tr>
	<tr id="database2">
		<th>CHECKING</th>
		<td>Checking database connection through CONcrescent...</td>
	</tr>
	<tr id="database3">
		<th>CHECKING</th>
		<td>Checking database date and time...</td>
	</tr>
	<tr id="database4">
		<th>CHECKING</th>
		<td>Checking database character set...</td>
	</tr>
	<tr id="database5">
		<th>CHECKING</th>
		<td>Checking user accounts...</td>
	</tr>
	<tr id="curl">
		<th>CHECKING</th>
		<td>Checking cURL extension...</td>
	</tr>
	<tr id="paypal">
		<th>CHECKING</th>
		<td>Checking PayPal connection...</td>
	</tr>
	<tr id="mail">
		<th>CHECKING</th>
		<td>Checking email sending capability...</td>
	</tr>
	<tr id="gd">
		<th>CHECKING</th>
		<td>Checking GD library...</td>
	</tr>
	<tr id="theme">
		<th>CHECKING</th>
		<td>Checking theme stylesheet...</td>
	</tr>
</table>
</body>
</html>
<?php

if (isset($_REQUEST['date'])) {
	date_default_timezone_set($_REQUEST['timezone']);
	$date = strtotime($_REQUEST['date']);
	echo "<h4>PHP's strtotime Calculation:</h4>";
	$format = 'l jS \of F Y h:i:s A T';
	echo htmlspecialchars("Custom Format (\"$format\"):\n\t");
	echo '<strong>'.date($format, $date)."</strong>\n\n";
	echo "RFC 2822:\n\t";
	echo '<strong>'.date('r', $date)."</strong>\n\n";
	echo "ISO 8601:\n\t";
	echo '<strong>'.date('c', $date)."</strong>\n\n";
	echo "Unix Timestamp:\n\t";
	echo '<strong>'.date('U', $date)."</strong>\n\n";
	//echo "Input:\n\t<strong>".htmlspecialchars($_REQUEST['date'])."</strong>";
	exit;
}

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title>PHP Date String Tester</title>
		<style type="text/css">
			body {
				font: 12pt Arial;
			}
		</style>
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
		<script type="text/javascript">
			$(function(){
				var input = $("#input"), output = $("#output"), cur_jqXHR;
				var timezone = $("#timezone");
				var check = function(){
					if (input.val() == "") {
						cur_jqXHR.abort();
						output.html("PHP's output will be displayed here.");
						return;
					}
					$.ajax({
						"url": "",
						"method": "POST",
						"data": {"date": input.val(), "timezone": timezone.val()},
						"dataType": "text",
						"beforeSend": function(jqXHR){
							if (cur_jqXHR)
								cur_jqXHR.abort();
							cur_jqXHR = jqXHR;
						},
						"success": function(data){
							output.html(data);
						}
					});
				};
				input.keyup(check);
				setInterval(check, 1000);
			});
		</script>
	</head>
	<body>
		<div>
			<header>
				<h2>PHP Date String Tester</h2>
			</header>
			<section>
				<span>Enter a date string: </span>
				<input type="text" id="input" value="now" />
				<span>Select a timezone: </span>
				<select id="timezone">
					<?php $default = date_default_timezone_get(); foreach (timezone_identifiers_list() as $cur_timezone) { ?>
					<option value="<?php echo htmlspecialchars($cur_timezone); ?>"<?php echo ($default == $cur_timezone ? ' selected="selected"' : ''); ?>><?php echo htmlspecialchars($cur_timezone); ?></option>
					<?php } ?>
				</select>
			</section>
			<section>
				<pre id="output" style="font-family: 'Courier New',monospace;">PHP's output will be displayed here.</pre>
			</section>
		</div>
	</body>
</html>
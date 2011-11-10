<?php
header('content-type: application/xhtml+xml');
include 'bad_variable.php';
?>
<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title>JavaScript Escaping Tests</title>
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script>
	</head>
	<body>
		<a href=".">Back</a>
		<script>
			// <![CDATA[
			danger = "<?php echo addslashes($bad_var); ?>";
			$(function(){
				if (danger.match(/<\/script>/))
					$("#a_ok").css({
						"background": "green",
						"height": "100px",
						"width": "100px"
					}).html("Everything went swimmingly. No danger here.");
				else
					$("#a_ok").css({
						"background": "yellow",
						"height": "100px",
						"width": "100px"
					}).html("No danger, but the value's not right.");
			});
			// ]]>
		</script>
		<div id="a_ok"></div>
	</body>
</html>

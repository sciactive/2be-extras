<!DOCTYPE html>
<html>
	<head>
		<title></title>
		<meta charset="utf-8" />
		<script type="text/javascript">
			function call_up() {
				if (window.frameElement && window.frameElement.pines_upload_done)
					window.frameElement.pines_upload_done();
			}
		</script>
	</head>
	<body onload="call_up()">
		<?php
		var_dump($_FILES);
		?>
	</body>
</html>
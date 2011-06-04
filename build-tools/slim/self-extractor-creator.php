<?php

if (isset($_REQUEST['type'])) {
	include('archive.php');

	$arc = new slim;
	$arc->working_directory = $_REQUEST['working_directory'];
	$arc->stub = file_get_contents("self-extractor-template-{$_REQUEST['type']}.php");
	$arc->compression = $_REQUEST['compression'];
	$arc->header_compression = ($_REQUEST['header_compression'] == 'ON');
	$arc->file_integrity = ($_REQUEST['file_integrity'] == 'ON');
	$arc->preserve_owner = ($_REQUEST['preserve_owner'] == 'ON');
	$arc->preserve_mode = ($_REQUEST['preserve_mode'] == 'ON');
	$arc->preserve_times = ($_REQUEST['preserve_times'] == 'ON');
	if (!empty($_REQUEST['filter']))
		$arc->add_directory('.', true, true, "/{$_REQUEST['filter']}/");
	else
		$arc->add_directory('.');

	header('Content-Type: application/x-httpd-php');
	header('Content-Disposition: attachment; filename="self-extracting-archive.php"');
	$arc->write('php://output');
	exit;
}

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<title>Self Extracting Slim Creator</title>
		<style type="text/css">
			.item {
				margin: 16px 0;
			}
		</style>
	</head>
	<body>
		<form method="POST" action="">
			<div>
				<h1>Self Extracting Slim Creator</h1>
				<div class="item">
					<div><label for="working_directory">Compress Directory</label></div>
					<div><small>This directory's contents will be added as the root of the archive.</small></div>
					<div>
						<input type="text" name="working_directory" id="working_directory" value="" size="35" />
					</div>
				</div>
				<div class="item">
					<div><label for="filter">Exclusion Filter</label></div>
					<div><small>Any file(s) matching this regex pattern will not be included in the archive.</small></div>
					<div>
						<input type="text" name="filter" id="filter" value="" size="35" />
					</div>
				</div>
				<div class="item">
					<div>Extractor Type</div>
					<div>
						<label><input type="radio" name="type" value="basic" /> Basic</label>
						<br />
						<label><input type="radio" name="type" value="fancy" checked="checked" /> Fancy</label>
					</div>
				</div>
				<div class="item">
					<div>Compression</div>
					<div>
						<label><input type="radio" name="compression" value="" /> None</label>
						<br />
						<label><input type="radio" name="compression" value="deflate" checked="checked" /> Deflate</label>
						<br />
						<label><input type="radio" name="compression" value="bzip2" /> Bzip2</label>
					</div>
				</div>
				<div class="item">
					<div>Extra Option</div>
					<div>
						<label><input type="checkbox" name="header_compression" value="ON" checked="checked" /> Compress the archive header too.</label>
						<br />
						<label><input type="checkbox" name="file_integrity" value="ON" checked="checked" /> Check file integrity.</label>
						<br />
						<label><input type="checkbox" name="preserve_owner" value="ON" /> Preserve file/directory owner.</label>
						<br />
						<label><input type="checkbox" name="preserve_mode" value="ON" /> Preserve file/directory mode.</label>
						<br />
						<label><input type="checkbox" name="preserve_times" value="ON" checked="checked" /> Preserve file/directory times.</label>
					</div>
				</div>
				<div class="item">
					<div>
						<input type="submit" value="Build It" />
						<input type="reset" value="Reset" />
					</div>
				</div>
			</div>
		</form>
	</body>
</html>
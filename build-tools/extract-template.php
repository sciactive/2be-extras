<?php

$file = '#FILECONTENTS#';

if (isset($_REQUEST['directory'])) {
	// Clean the directory name.
	$directory = str_replace('..', 'fail-danger-dont-use-hack-attempt', $_REQUEST['directory']);
	if (empty($directory)) $directory = '.';
	// Make sure it exists, and is a directory.
	if (!file_exists($directory)) {
		mkdir($directory) or die('Unable to create the directory specified.');
	}
	is_dir($directory) or die ('Specified file path exists, but is not a directory.');
	// Make a tar from the encoded data.
	file_put_contents("$directory/temp-extract.tar.bz2", $file) or die('Can\'t write to archive file. Do you have permission?');
	// Extract it.
	system("cd $directory && tar -xjf temp-extract.tar.bz2", $return);
	($return < 2) or die('Error running tar commands. Do you have permission?');
	// Delete it.
	unlink("$directory/temp-extract.tar.bz2");
	unlink(__FILE__);
	header("Location: $directory");
	exit;
}

echo '<?xml version="1.0" encoding="UTF-8" standalone="no" ?>'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<title>PHP Self Extractor</title>
	<style type="text/css" media="all">
		/* <![CDATA[ */
		.wrapper {
			margin: 3em;
			font-family: sans;
			font-size: 80%;
		}
		.wrapper fieldset {
			border: 1px solid #040;
			-moz-border-radius: 10px;
		}
		.wrapper legend {
			padding: 0.5em 0.8em;
			border: 2px solid #040;
			color: #040;
			font-size: 120%;
			-moz-border-radius: 10px;
		}
		.wrapper label {
			display: block;
			text-align: right;
			margin-right: 60%;
		}
		.wrapper input {
			color: #040;
		}
		.wrapper .buttons {
			text-align: right;
		}
		/* ]]> */
	</style>
</head>
<body>
<div class="wrapper">
	<form action="" method="post">
		<fieldset>
			<legend>PHP Self Extractor</legend>
			<p>Please enter the directory (relative to this script) to which you would like to extract the files stored in this PHP Archive. If you leave this field blank, the current directory will be used. Please do not try to use parent directories, they will not work.</p>
				<label>Directory: <input type="text" name="directory" value="" /></label><br />
				<div class="buttons"><input type="submit" value="Extract" name="submit" /> <input type="reset" value="Reset" name="reset" /></div>
		</fieldset>
	</form>
	<p><small>This PHP Self Extractor was developed by Hunter Perrin as part of Pines.</small></p>
</div>
</body>
</html>
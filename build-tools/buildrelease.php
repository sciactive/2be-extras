<?php

if (!file_exists('release/'))
	die('You must have a release directory with releases in it. You can run assemblerelease.sh first.');

$releases = array_diff(scandir('release/'), array('.', '..'));
foreach ($releases as $key => $cur_release) {
	if (!is_dir('release/'.$cur_release))
		unset($releases[$key]);
}
$releases = array_values($releases);

if (empty($releases))
	die('No releases were found in your release directory.');

function clean_filename($filename) {
	return str_replace('..', 'fail-danger-dont-use-hack-attempt', $filename);
}
if (isset($_REQUEST['directory'])) {
	$directory = $_REQUEST['directory'];
	if (!in_array($directory, $releases))
		die('Directory must be an existing release directory.');
	$directory = 'release/'.$directory;
	if (!file_exists($directory))
		die('Directory doesn\'t exist.');
	$file = clean_filename($_REQUEST['file']);
	switch ($_REQUEST['submit']) {
		case "Build .php":
			include('slim/archive.php');

			$arc = new slim;
			$arc->stub = file_get_contents('extract-template.php');
			$arc->working_directory = $directory;
			$arc->file_integrity = true;
			if ($_REQUEST['remove_self_extract'] != 'ON')
				$arc->ext['keep_self'] = true;
			$arc->add_directory('.');
			header('Content-Type: application/octet-stream');
			header("Content-Disposition: attachment; filename=\"$file.php\"");

			$arc->write('php://output');
			break;
		case "Build .tar.gz":
			header('Content-Type: application/x-gzip');
			header("Content-Disposition: attachment; filename=\"$file.tar.gz\"");
			print `cd $directory && tar -czhf - --exclude-vcs *`;
			break;
		case "Build .tar.bz2":
			header('Content-Type: application/x-bzip-compressed-tar');
			header("Content-Disposition: attachment; filename=\"$file.tar.bz2\"");
			print `cd $directory && tar -cjhf - --exclude-vcs *`;
			break;
		case "Build .zip":
			header('Content-Type: application/zip');
			header("Content-Disposition: attachment; filename=\"$file.zip\"");
			print `cd $directory && find -L ./* | egrep -v ".svn" | zip - -@`;
			break;
	}
	exit;
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Release Builder</title>
	<link href='http://fonts.googleapis.com/css?family=EB+Garamond' rel='stylesheet' type='text/css'>
	<style type="text/css" media="all">
		html {
			font-size: 100%;
			-webkit-text-size-adjust: 100%;
			-ms-text-size-adjust: 100%;
		}
		body {
			margin: 0;
			font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
			font-size: 18px;
			line-height: 22px;
			color: #333;
			background: #ccc;
			background: -moz-linear-gradient(top,  #ccc 1%, #aaa 100%) repeat fixed 0 0 transparent;
			background: -webkit-gradient(linear, left top, left bottom, color-stop(1%,#ccc), color-stop(100%,#aaa)) repeat fixed 0 0 transparent;
			background: -webkit-linear-gradient(top,  #ccc 1%,#aaa 100%) repeat fixed 0 0 transparent;
			background: -o-linear-gradient(top,  #ccc 1%,#aaa 100%) repeat fixed 0 0 transparent;
			background: -ms-linear-gradient(top,  #ccc 1%,#aaa 100%) repeat fixed 0 0 transparent;
			background: linear-gradient(to bottom,  #ccc 1%,#aaa 100%) repeat fixed 0 0 transparent;
			filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#cccccc', endColorstr='#aaaaaa',GradientType=0 );
			text-rendering: optimizelegibility;
		}
		.wrapper {
			font-family: sans-serif;
			font-size: 13px;
			margin: 100px 125px;
			color: #333;
			background-color: #ecf7d6;
			-webkit-box-shadow: 0 1px 0 rgba(255, 255, 255, 0.8) inset, 0 -2px 0 rgba(10, 12, 15, 0.1) inset, 0 0 10px rgba(255, 255, 255, 0.5) inset, 0 0 0 1px rgba(10, 12, 15, 0.1), 0 2px 4px rgba(10, 12, 15, 0.15), inset -60px -90px 300px 10px #B4DC63;
			box-shadow: 0 1px 0 rgba(255, 255, 255, 0.8) inset, 0 -2px 0 rgba(10, 12, 15, 0.1) inset, 0 0 10px rgba(255, 255, 255, 0.5) inset, 0 0 0 1px rgba(10, 12, 15, 0.1), 0 2px 4px rgba(10, 12, 15, 0.15), inset -60px -90px 300px 10px #B4DC63;
			border-radius: 8px;
			padding: 40px;
		}
		.wrapper .header h1 {
			font-family: 'EB Garamond', serif;
			font-weight: normal;
			font-size: 40px;
			line-height: 1;
			margin: 0 0 5px;
			color: #507800;
			text-decoration: none;
			text-shadow: 0 0 4px #B4DC63;
			filter: dropshadow(color=#B4DC63, offx=0, offy=0);
		}
		.wrapper .header hr {
			margin: 6px -10px;
		}
		.wrapper p {
			margin: .4em 0 0;
			padding: 0;
		}
		.wrapper label {
			margin: 1em 0 0;
			display: block;
			text-align: right;
			margin-right: 60%;
		}
		.wrapper input[type=text] {
			padding: .2em;
			color: #67003A;
			background: #fff;
			border: 1px #67003A solid;
			border-radius: 3px;
			-webkit-transition: border linear 0.2s, box-shadow linear 0.2s;
			-moz-transition: border linear 0.2s, box-shadow linear 0.2s;
			-o-transition: border linear 0.2s, box-shadow linear 0.2s;
			transition: border linear 0.2s, box-shadow linear 0.2s;
		}
		.wrapper input[type=text]:focus {
			border-color: rgba(159, 40, 133, 0.8);
			outline: 0;
			outline: thin dotted \9;
			/* IE6-9 */

			-webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075), 0 0 8px rgba(159, 40, 133, 0.6);
			-moz-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075), 0 0 8px rgba(159, 40, 133, 0.6);
			box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075), 0 0 8px rgba(159, 40, 133, 0.6);
		}
		.wrapper .buttons {
			text-align: right;
		}
		.wrapper input[type=submit], .wrapper input[type=reset], .wrapper input[type=button], .wrapper button {
			color: #FFF;
			padding: 6px 10px;
			border: 1px #662E59 solid;
			border-radius: 3px;
			background: #cc5fb2; /* Old browsers */
			background: -moz-linear-gradient(top,  #cc5fb2 0%, #9f488c 6%, #662e59 100%); /* FF3.6+ */
			background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#cc5fb2), color-stop(6%,#9f488c), color-stop(100%,#662e59)); /* Chrome,Safari4+ */
			background: -webkit-linear-gradient(top,  #cc5fb2 0%,#9f488c 6%,#662e59 100%); /* Chrome10+,Safari5.1+ */
			background: -o-linear-gradient(top,  #cc5fb2 0%,#9f488c 6%,#662e59 100%); /* Opera 11.10+ */
			background: -ms-linear-gradient(top,  #cc5fb2 0%,#9f488c 6%,#662e59 100%); /* IE10+ */
			background: linear-gradient(to bottom,  #cc5fb2 0%,#9f488c 6%,#662e59 100%); /* W3C */
			filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#cc5fb2', endColorstr='#662e59',GradientType=0 ); /* IE6-9 */
		}
		.wrapper input[type=submit]:hover, .wrapper input[type=reset]:hover, .wrapper input[type=button]:hover, .wrapper button:hover {
			background: #cc5fb2; /* Old browsers */
			background: -moz-linear-gradient(top,  #cc5fb2 0%, #8c407a 6%, #662e59 100%); /* FF3.6+ */
			background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#cc5fb2), color-stop(6%,#8c407a), color-stop(100%,#662e59)); /* Chrome,Safari4+ */
			background: -webkit-linear-gradient(top,  #cc5fb2 0%,#8c407a 6%,#662e59 100%); /* Chrome10+,Safari5.1+ */
			background: -o-linear-gradient(top,  #cc5fb2 0%,#8c407a 6%,#662e59 100%); /* Opera 11.10+ */
			background: -ms-linear-gradient(top,  #cc5fb2 0%,#8c407a 6%,#662e59 100%); /* IE10+ */
			background: linear-gradient(to bottom,  #cc5fb2 0%,#8c407a 6%,#662e59 100%); /* W3C */
			filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#cc5fb2', endColorstr='#662e59',GradientType=0 ); /* IE6-9 */
		}


		.wrapper label {
			margin: 1em 0 0;
			display: block;
			text-align: left;
			margin-right: 0;
		}
		.wrapper label span {
			display: inline-block;
			width: 190px;
		}
	</style>
	<script type="text/javascript">
		function explain_archive() {
			alert("\
When you create a Slim PHP Self Extractor \"Build .php\", you have the\n\
option to make the Slim archive remove itself after extracting all of\n\
its files. This makes it easier for someone installing Pines, because\n\
they don't have to remove the file manually.");
		}
	</script>
</head>
<body>
<div class="wrapper">
	<div class="header">
		<h1>Pines Release Builder</h1>
		<hr />
	</div>
	<p>
		Use this release builder to build packages from the sources in the given
		directory in your releases directory. After you click one of the build
		options, you will be given the chance to save the file to your hard
		drive.
	</p>
	<form action="" method="post">
		<label>
			<span>Release directory:</span>
			<select name="directory">
				<?php foreach ($releases as $cur_release) { ?>
				<option value="<?php echo htmlspecialchars($cur_release); ?>"><?php echo htmlspecialchars($cur_release); ?></option>
				<?php } ?>
			</select>
		</label>
		<label>
			<span>Filename to save as:</span>
			<input type="text" name="file" value="pines-VERSION-STATE-DBBACKEND" />
		</label>
		<label>
			<span>Remove Slim extractor: <a href="javascript:void(0);" onclick="explain_archive();">(?)</a></span>
			<input type="checkbox" name="remove_self_extract" value="ON" />
		</label>
		<div class="buttons">
			<input type="submit" value="Build .php" name="submit" />
			<input type="submit" value="Build .tar.gz" name="submit" />
			<input type="submit" value="Build .tar.bz2" name="submit" />
			<input type="submit" value="Build .zip" name="submit" />
		</div>
	</form>
</div>
</body>
</html>
<?php
function clean_filename($filename) {
	return str_replace('..', 'fail-danger-dont-use-hack-attempt', $filename);
}
if (isset($_REQUEST['directory'])) {
	$directory = clean_filename($_REQUEST['directory']);
	if (empty($directory)) $directory = 'pines';
	$directory = '../../'.$directory;
	$file = clean_filename($_REQUEST['file']);
	switch ($_REQUEST['submit']) {
		case "Build .php":
			$archive = file_get_contents('extract-template.php');
			$tokens = token_get_all($archive);
			$archive = '';
			foreach ($tokens as $cur_token) {
				$comment_tokens = array(T_COMMENT);

				if (defined('T_DOC_COMMENT'))
					$comment_tokens[] = T_DOC_COMMENT;
				if (defined('T_ML_COMMENT'))
					$comment_tokens[] = T_ML_COMMENT;

				if (is_array($cur_token)) {
					if (in_array($cur_token[0], $comment_tokens))
						continue;

					$cur_token = $cur_token[1];
				}

				$archive .= $cur_token;
			}
			$archive = preg_replace('/\s+/s', ' ', $archive);
			$archive = str_replace('\'#FILECONTENTS#\'', str_replace('\' . "\0" . \'', '\'."\0".\'', var_export(`cd $directory && tar -cjf - --exclude-vcs *`, true)), $archive);
			//header('Content-Type: application/x-httpd-php');
			header('Content-Type: application/xhtml+xml');
			header("Content-Disposition: attachment; filename=\"$file.php\"");
			break;
		case "Build .tar.gz":
			$archive = `cd $directory && tar -czf - --exclude-vcs *`;
			header('Content-Type: application/x-gzip');
			header("Content-Disposition: attachment; filename=\"$file.tar.gz\"");
			break;
		case "Build .tar.bz2":
			$archive = `cd $directory && tar -cjf - --exclude-vcs *`;
			header('Content-Type: application/x-bzip-compressed-tar');
			header("Content-Disposition: attachment; filename=\"$file.tar.bz2\"");
			break;
		case "Build .zip":
			$archive = `cd $directory && find ./* | egrep -v ".svn" | zip - -@`;
			header('Content-Type: application/zip');
			header("Content-Disposition: attachment; filename=\"$file.zip\"");
			break;
	}
	print $archive;
	exit;
}
echo '<?xml version="1.0" encoding="UTF-8" standalone="no" ?>'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<title>Release Builder</title>
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
			<legend>Pines Release Builder</legend>
			<p>Use this release builder to build a package of the source from the given directory in the repository. After you click one of the build options, you will be given the chance to save the file to your hard drive.</p>
			<label>Repository directory to build from: <input type="text" name="directory" value="pines/" /></label><br />
			<label>Filename to save as: <input type="text" name="file" value="pines-VERSION-STATE" /></label><br />
			<div class="buttons"><input type="submit" value="Build .php" name="submit" /> <input type="submit" value="Build .tar.gz" name="submit" /> <input type="submit" value="Build .tar.bz2" name="submit" /> <input type="submit" value="Build .zip" name="submit" /> <input type="reset" value="Reset" name="reset" /></div>
		</fieldset>
	</form>
</div>
</body>
</html>
<?php
/**
 * Makes a CSS file from an icon theme.
 *
 * Because of the target audience of Pines, this file only regards PNG and GIF images.
 */

/**
 * Icon theme directory.
 *
 * End with a slash.
 *
 * @global string $working_dir
 */
$working_dir = '../../pines/system/css/tango-icon-theme/';


$working_dir_regex = '/'.preg_quote($working_dir, '/').'/';
$files = `find $working_dir -iname "*.png" -or -iname "*.gif"`;
$file_array = explode("\n", $files);
foreach ($file_array as $cur_file) {
	if (!empty($cur_file))
		echo preg_replace(array($working_dir_regex, '/\//', '/\.([Pp][Nn][Gg]|[Gg][Ii][Ff])$/', '/\./', '/^/'), array('', '_', '', '-', '.picon_'), $cur_file)
		.' {background-image: url("'
		.str_replace($working_dir, '', $cur_file)
		.'");}'
		."\n";
}

?>
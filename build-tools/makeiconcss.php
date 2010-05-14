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
$working_dir = '../../pines/components/com_oxygenicons/includes/oxygen/';

$dir_iterator = new RecursiveDirectoryIterator($working_dir);
$iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::CHILD_FIRST);

foreach ($iterator as $file_path) {
	if (!is_file($file_path) || !in_array(strtolower(substr($file_path, -4)), array('.jpg', '.jpe', 'jpeg', '.png', '.gif')))
		continue;
	$rel_file = substr($file_path, strlen($working_dir));
	$dir = substr($rel_file, 0, strpos($rel_file, '/'));
	$file = substr($rel_file, strrpos($rel_file, '/') + 1, -4);
    $styles["picon_{$dir}_{$file}"] = $rel_file;
}

foreach ($styles as $key => $value) {
	echo ".{$key} {background-image: url(\"{$value}\")}\n";
}

exit;

// Old code.
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
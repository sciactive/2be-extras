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
	$file = str_replace('+', '_', $file);
	if ($dir == '16')
		$styles[".picon-{$file}"] = $rel_file;
	else
		$styles[".picon-{$dir}.picon-{$file}"] = $rel_file;
}

ksort($styles);

foreach ($styles as $key => $value) {
	if (strpos($value, '+') === false)
		echo "{$key}{background:url({$value})}\n";
	else
		echo "{$key}{background:url(\"{$value}\")}\n";
}

?>
<?php
/**
 * Makes a sprite from images.
 */

echo 'Don\'t use this. The sprite image is too big to be useful.';
exit;

$sprite = new Imagick();
$sprite->newImage(1, 1, 'none');
$sprite->setImageFormat('png');
$sprite->setImageColorspace(imagick::COLORSPACE_RGB);
$sprite->setImageBackgroundColor(new ImagickPixel('transparent'));
$offset = (object) array('x' => 0, 'y' => 0);
$max_width = 800;
$cur_row_height = 0;

$working_dir = '../../2be/components/com_oxygenicons/includes/oxygen/';

$dir_iterator = new RecursiveDirectoryIterator($working_dir);
$iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::CHILD_FIRST);

foreach ($iterator as $file_path) {
	if (!is_file($file_path) || strpos($file_path, '/animations/') !== false || !in_array(strtolower(substr($file_path, -4)), array('.jpg', '.jpe', 'jpeg', '.png')))
		continue;
	// Open the image.
	$image = new Imagick();
	$image->readImage($file_path);
	$image->setImageColorspace(imagick::COLORSPACE_RGB);
	// Figure out where it goes.
	$width = $image->getImageWidth();
	$height = $image->getImageHeight();
	if ($offset->x + $width > $max_width) {
		$offset->y += $cur_row_height;
		$offset->x = 0;
		$cur_row_height = 0;
	}
	if ($height > $cur_row_height)
		$cur_row_height = $height;
	// Figure out if there's room.
	$swidth = $sprite->getImageWidth();
	$sheight = $sprite->getImageHeight();
	$change = false;
	if ($offset->x + $width > $swidth) {
		$change = true;
		$swidth = $offset->x + $width;
	}
	if ($offset->y + $height > $sheight) {
		$change = true;
		$sheight = $offset->y + $height;
	}
	// Make some room.
	if ($change)
		$sprite->setImageExtent($swidth, $sheight);
	// Paste in the image.
	$sprite->compositeImage($image, imagick::COMPOSITE_COPY, $offset->x, $offset->y);
	// Advance the offset.
	$offset->x += $width;
}

header('Content-Type: image/png');
echo $sprite;
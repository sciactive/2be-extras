<?php

include('archive.php');

symlink('testfile', 'testfile link');
$arc = new slim;
//$arc->preserve_mode = true;
//$arc->preserve_times = true;
$arc->file_integrity = true;
$arc->add_file('testfile');
$arc->add_file('testfile link');
$arc->add_file('slim format');

if ($arc->write('testfile-deflate.slm')) {
	echo 'Deflate archive written successfully. ';
} else {
	echo 'Error writing archive. ';
}

$arc->compression = 'bzip2';
if ($arc->write('testfile-bzip2.slm')) {
	echo 'Bzip2 archive written successfully. ';
} else {
	echo 'Error writing archive. ';
}

$arc->compression = '';
$arc->header_compression = false;
if ($arc->write('testfile-full.slm')) {
	echo 'Full text archive written successfully. ';
} else {
	echo 'Error writing archive. ';
}

unlink('testfile link');
unset($arc);

$arc = new slim;
if ($arc->read('testfile-deflate.slm')) {
	echo 'Archive read successfully. ';
} else {
	echo 'Error reading archive. ';
}

$arc->working_directory = 'test_extract';
//$arc->extract('slim format');
//$arc->extract('testfile');
if ($arc->extract()) { // testing extract all
	echo 'Archive extracted successfully. ';
} else {
	echo 'Error extracting archive. ';
}
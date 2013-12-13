<?php

include('archive.php');

$arc = new slim;
$arc->file_integrity = true;
$arc->add_file('testfile');

if ($arc->write('testinclude.slm')) {
	echo 'Archive written successfully. ';
} else {
	echo 'Error writing archive. ';
}
unset($arc);

$arc = new slim;
if ($arc->read('testinclude.slm')) {
	echo 'Archive read successfully. ';
} else {
	echo 'Error reading archive. ';
}

if (file_get_contents('testfile') == $arc->get_file('testfile')) {
	echo 'File retrieved successfully. ';
} else {
	echo 'Error retrieving file. ';
}

if (file_exists('testinclude.slm'))
	unlink('testinclude.slm');
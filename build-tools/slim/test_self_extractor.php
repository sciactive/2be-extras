<?php

include('archive.php');

$arc = new slim;
//$arc->compression = 'bzip2';
$arc->compression = '';
$arc->stub = file_get_contents('self_extract_template.php');
$arc->working_directory = '../../../pines/';
$arc->preserve_times = true;
$arc->file_integrity = true;
$arc->add_directory('.', true, true, '/components\/com_pdf/');

if ($arc->write('test_extract/pinestest.php')) {
	echo 'Archive written successfully. ';
} else {
	echo 'Error writing archive. ';
}

?>
<?php

include('archive.php');

$arc = new slim;
//$arc->stub = '#! /usr/bin/php
//<?php //slim1.0
//echo "This is a Slim archive. Please use a Slim program to read it.\n";
//__halt_compiler();';
$arc->stub = '#!/bin/sh
#slim1.0
echo This is a Slim archive. Please use a Slim program to read it.
exit';
$arc->metadata['title'] = 'test';
$arc->metadata['complex'] = 'How about

some complex

data, like

HEADER

and

STREAM

'.json_encode(array('see' => array('complex', 'data')));
$arc->working_directory = '../';
$arc->compression = '';
$arc->header_compression = false;
$arc->add_directory('archives', true, true, '/\.slm$/');

if ($arc->write('testrecursive.slm')) {
	echo 'Regular archive written successfully. ';
} else {
	echo 'Error writing archive. ';
}
unset($arc);

$arc = new slim;
if ($arc->read('testrecursive.slm')) {
	echo 'Archive read successfully. ';
} else {
	echo 'Error reading archive. ';
}

if ($arc->metadata['title'] == 'test') {
	echo 'Metadata read successfully. ';
} else {
	echo 'Error reading metadata. ';
}

$arc->working_directory = 'test_extract';
$arc->extract('archives', true, array('/\.php$/', '/blah/'));

?>
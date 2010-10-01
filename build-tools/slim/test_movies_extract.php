<?php

include('archive.php');

$arc = new slim;

if ($arc->read('movies.slm.gz')) {
	echo 'Archive read successfully. ';
} else {
	echo 'Error reading archive. ';
}

$arc->extract_file('Gamer.avi', 'Gamer.test-extract.avi');

?>
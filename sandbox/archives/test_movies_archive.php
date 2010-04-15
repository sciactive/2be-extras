<?php

include('archive.php');

$arc = new slim;
$arc->add_file('Bickford Shmeckler\'s Cool Ideas.avi');
$arc->add_file('Gamer.avi');

if ($arc->write('movies.slm.gz')) {
	echo 'Archive written successfully. ';
} else {
	echo 'Error writing archive. ';
}

?>
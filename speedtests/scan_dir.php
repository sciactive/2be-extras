<pre>
What's faster for scanning a directory?

<?php

$count = 10000;
$dir = '../../';

$test = array();
$i = 0;

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	$test = scandir($dir);
}
$end_time = microtime(true);
echo 'scandir() :  '.($end_time - $start_time)."\n";

$test = array();

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	$test = glob("$dir/*");
}
$end_time = microtime(true);
echo 'glob()    :  '.($end_time - $start_time)."\n";

$test = array();

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	$r = opendir($dir);
	$test = array();
	while (($entry = readdir($r)) !== false)
		$test[] = $entry;
	closedir($r);
}
$end_time = microtime(true);
echo 'readdir() :  '.($end_time - $start_time)."\n";

echo "\nTested $count iterations."

?>
</pre>
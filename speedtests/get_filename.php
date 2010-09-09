<pre>
What's faster for getting a filename?

<?php

$count = 1000000;

$test = 'this/is/path/to/file.php';
$test2 = '';

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	$test2 = basename($test);
}
$end_time = microtime(true);
echo 'basename() :  '.($end_time - $start_time)."\n";

$test2 = '';

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	$test2 = strrchr($test, '/');
}
$end_time = microtime(true);
echo 'strrchr()  :  '.($end_time - $start_time)."\n";

echo "\nTested $count iterations."

?>
</pre>
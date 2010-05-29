<pre>
What's faster for filling an array?

<?php

$count = 10000;

$test = array();
$i = 0;

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	$test = array();
	for ($i2 = 0; $i2 < 1000; $i2++) {
		$test[] = 0;
	}
}
$end_time = microtime(true);
echo '[] =         :  '.($end_time - $start_time)."\n";

$test = array();

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	$test = array();
	for ($i2 = 0; $i2 < 1000; $i2++) {
		array_push($test, 0);
	}
}
$end_time = microtime(true);
echo 'array_push() :  '.($end_time - $start_time)."\n";

echo "\nTested $count iterations."

?>
</pre>
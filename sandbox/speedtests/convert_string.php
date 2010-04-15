<pre>
What's faster for converting to string?

<?php

$count = 10000000;

$test = 100;
$test2 = '100';
$i = 0;

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	$test2 = strval($test);
}
$end_time = microtime(true);
echo 'strval()  :  '.($end_time - $start_time)."\n";

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	$test2 = "$test";
}
$end_time = microtime(true);
echo '"$var"    :  '.($end_time - $start_time)."\n";

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	$test2 = (string) $test;
}
$end_time = microtime(true);
echo 'type cast :  '.($end_time - $start_time)."\n";

echo "\nTested $count iterations."

?>
</pre>
<pre>
What's faster for determining arrays?

<?php

$count = 1000000;

$test = array('im', 'an', 'array');
$test2 = 'im not an array';
$test3 = (object) array('im' => 'not', 'going' => 'to be', 'an' => 'array');
$test4 = 42;
// Set this now so the first for loop doesn't do the extra work.
$i = $start_time = $end_time = 0;

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	if (!is_array($test) || is_array($test2) || is_array($test3) || is_array($test4)) {
		echo 'error';
		break;
	}
}
$end_time = microtime(true);
echo 'is_array  :  '.($end_time - $start_time)."\n";

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	if (!(array) $test === $test || (array) $test2 === $test2 || (array) $test3 === $test3 || (array) $test4 === $test4) {
		echo 'error';
		break;
	}
}
$end_time = microtime(true);
echo 'cast, === :  '.($end_time - $start_time)."\n";

echo "\nTested $count iterations.";

?>
</pre>
<pre>
What's faster for determining objects?

<?php

$count = 1000000;

$test = (object) array('i' => 'am', 'going' => 'to be', 'an' => 'object');
$test2 = 'im not an object';
$test3 = array('im', 'not', 'an', 'object');
$test4 = 42;
// Set this now so the first for loop doesn't do the extra work.
$i = $start_time = $end_time = 0;

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	if (!is_object($test) || is_object($test2) || is_object($test3) || is_object($test4)) {
		echo 'error';
		break;
	}
}
$end_time = microtime(true);
echo 'is_object :  '.($end_time - $start_time)."\n";

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	if ((object) $test !== $test || (object) $test2 === $test2 || (object) $test3 === $test3 || (object) $test4 === $test4) {
		echo 'error';
		break;
	}
}
$end_time = microtime(true);
echo 'cast, === :  '.($end_time - $start_time)."\n";

echo "\nTested $count iterations.";

?>
</pre>
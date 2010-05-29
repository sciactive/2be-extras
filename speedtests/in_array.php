<pre>
What's faster for searching arrays?

<?php

$count = 10000;

$test = range(1, 1000);
// Set this now so the first for loop doesn't do the extra work.
$i = $start_time = $end_time = 0;

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	if (!in_array(500, $test) || in_array('dog', $test)) {
		echo 'error';
		break;
	}
}
$end_time = microtime(true);
echo 'in_array     :  '.($end_time - $start_time)."\n";

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	if (array_search(500, $test) === false || array_search('dog', $test) !== false) {
		echo 'error';
		break;
	}
}
$end_time = microtime(true);
echo 'array_search :  '.($end_time - $start_time)."\n";

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	$found1 = $found2 = false;
	foreach ($test as $cur_val) {
		if ($cur_val == 500) {
			$found1 = true;
			break;
		}
	}
	foreach ($test as $cur_val) {
		if ($cur_val == 'dog') {
			$found2 = true;
			break;
		}
	}
	if (!$found1 || $found2) {
		echo 'error';
		break;
	}
}
$end_time = microtime(true);
echo 'foreach      :  '.($end_time - $start_time)."\n";

echo "\nTested $count iterations.";

?>
</pre>
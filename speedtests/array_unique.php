<pre>
What's faster for removing duplicates from an array?

<?php

$count = 100;

$test = array_merge(range(1, 1000), range(1, 1000));
$test2 = array();
$i = 0;

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	$test2 = array();
	$test2 = array_unique($test);
}
$end_time = microtime(true);
echo 'array_unique()           :  '.($end_time - $start_time)."\n";

$test2 = array();

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	$test2 = array();
	$test2 = array_keys(array_flip($test));
}
$end_time = microtime(true);
echo 'array_keys(array_flip()) :  '.($end_time - $start_time)."\n";

$test2 = array();

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	$test2 = array();
	foreach ($test as $key => $val) {
		$test2[$val] = true;
	}
	$test2 = array_keys($test2);
}
$end_time = microtime(true);
echo 'foreach, array_keys()    :  '.($end_time - $start_time)."\n";

echo "\nTested $count iterations."

?>
</pre>
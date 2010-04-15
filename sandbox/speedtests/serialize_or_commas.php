<pre>
What's faster for saving an array of strings?

<?php

$count = 1000000;

$test = array('test1', 'test2', 'test3', 'test4', 'test5', 'test6');
$i = 0;

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	$result = unserialize(serialize($test));
	if ($result != $test) {
		echo 'error';
		break;
	}
}
$end_time = microtime(true);
echo 'serialize :  '.($end_time - $start_time)."\n";

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	$result = explode(',', implode(',', $test));
	if ($result != $test) {
		echo 'error';
		break;
	}
}
$end_time = microtime(true);
echo 'implode   :  '.($end_time - $start_time)."\n";

echo "\nTested $count iterations."

?>
</pre>
<pre>
What's faster for deleting an array entry?

<?php

$count = 10000;

$test = range(1, 1000);
// Set this now so the first for loop doesn't do the extra work.
$i = $start_time = $end_time = 0;

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	$test = range(1, 1000);
	unset($test[50]);
	if (isset($test[50])) {
		echo 'error';
		break;
	}
}
$end_time = microtime(true);
echo 'unset   :  '.($end_time - $start_time)."\n";

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	$test = range(1, 1000);
	$test[50] = null;
	if (isset($test[50])) {
		echo 'error';
		break;
	}
}
$end_time = microtime(true);
echo '= null  :  '.($end_time - $start_time)."\n";

echo "\nTested $count iterations.";

?>
</pre>
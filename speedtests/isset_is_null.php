<pre>
What's faster for determining nulls?

<?php

$count = 10000000;

$test = null;
$test2 = 1;
$i = 0;

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	if (isset($test) || !isset($test2)) {
		echo 'error';
		break;
	}
}
$end_time = microtime(true);
echo 'isset     :  '.($end_time - $start_time)."\n";

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	if (!is_null($test) || is_null($test2)) {
		echo 'error';
		break;
	}
}
$end_time = microtime(true);
echo 'is_null   :  '.($end_time - $start_time)."\n";

echo "\nTested $count iterations."

?>
</pre>
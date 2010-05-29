<pre>
What's faster for checking array keys?

<?php

$count = 1000000;

// The null value in this array can't be checked using isset() because it
// returns false. To check for it, you must use array_key_exists().
$test = array('exists' => 'yay', 'isnull' => null);
$i = 0;

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	if (isset($test['noexists']) || !isset($test['exists'])) {
		echo 'error';
		break;
	}
}
$end_time = microtime(true);
echo 'isset     :  '.($end_time - $start_time)."\n";

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	if (array_key_exists('noexists', $test) || !array_key_exists('exists', $test)) {
		echo 'error';
		break;
	}
}
$end_time = microtime(true);
echo 'key_exists:  '.($end_time - $start_time)."\n";

echo "\nTested $count iterations."

?>
</pre>
<pre>
What's faster for conditional assignment?

<?php

$count = 10000000;

$test = '';
$i = 0;

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	$test = ($test ? '' : 'test');
}
$end_time = microtime(true);
echo 'ternary   :  '.($end_time - $start_time)."\n";

$test = '';

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	if ($test)
		$test = '';
	else
		$test = 'test';
}
$end_time = microtime(true);
echo 'if else   :  '.($end_time - $start_time)."\n";

echo "\nTested $count iterations."

?>
</pre>
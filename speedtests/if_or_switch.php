<pre>
What's faster for big conditionals?

<?php

$count = 10000000;

$test = 'yes';
$i = 0;

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	if ($test == 'yes') {
		$test = 'no';
	} elseif ($test == 'no') {
		$test = 'maybe';
	} elseif ($test == 'maybe') {
		$test = 'probably';
	} elseif ($test == 'probably') {
		$test = 'yes';
	}
}
$end_time = microtime(true);
echo 'if elseif :  '.($end_time - $start_time)."\n";

$test = 'yes';

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	switch ($test) {
		case 'yes':
			$test = 'no';
			break;
		case 'no':
			$test = 'maybe';
			break;
		case 'maybe':
			$test = 'probably';
			break;
		case 'probably':
			$test = 'yes';
			break;
	}
}
$end_time = microtime(true);
echo 'switch    :  '.($end_time - $start_time)."\n";

echo "\nTested $count iterations."

?>
</pre>
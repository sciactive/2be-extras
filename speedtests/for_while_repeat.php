<pre>
What's faster for repeating unconditional code?

<?php

$count = 10000;

// Array of 50,000 items.
$test = 1000;
$i = 0;

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	$i2 = 0;
	while (true) {
		$i2++;
		if ($i2 > $test) break;
	}
}
$end_time = microtime(true);
echo 'while(true) :  '.($end_time - $start_time)."\n";

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	$i2 = 0;
	for (;;) {
		$i2++;
		if ($i2 > $test) break;
	}
}
$end_time = microtime(true);
echo 'for(;;)     :  '.($end_time - $start_time)."\n";

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	$i2 = 0;
	do {
		$i2++;
		if ($i2 > $test) break;
	} while (true);
}
$end_time = microtime(true);
echo 'do while    :  '.($end_time - $start_time)."\n";

echo "\nTested $count iterations."

?>
</pre>
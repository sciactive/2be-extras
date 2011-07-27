<pre>
What's faster for repeating conditional code?

<?php

$count = 10000;

// Array of 50,000 items.
$test = 1000;
$i = 0;

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	$i2 = 0;
	while ($i2 < $test) {
		$i2++;
	}
}
$end_time = microtime(true);
echo 'while       :  '.($end_time - $start_time)."\n";

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	$i2 = 0;
	for (; $i2 < $test; ) {
		$i2++;
	}
}
$end_time = microtime(true);
echo 'for         :  '.($end_time - $start_time)."\n";

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	$i2 = 0;
	do {
		if (!($i2 < $test)) break;
		$i2++;
	} while (true);
}
$end_time = microtime(true);
echo 'do w/ break :  '.($end_time - $start_time)."\n";

echo "\nTested $count iterations."

?>
</pre>
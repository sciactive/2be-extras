<pre>
What's faster for changing array values?

<?php

$count = 1000;

// Array of 50,000 items.
$test = explode(',', str_pad('1', 9999, ',1'));
$i = 0;

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	foreach($test as $key => $value) {
		switch ($value) {
			case '1':
				$test[$key] = '0';
				break;
			case '0':
				$test[$key] = '1';
				break;
		}
	}
}
$end_time = microtime(true);
echo 'by key    :  '.($end_time - $start_time)."\n";

$test = explode(',', str_pad('1', 9999, ',1'));

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	foreach($test as $key => &$value) {
		switch ($value) {
			case '1':
				$value = '0';
				break;
			case '0':
				$value = '1';
				break;
		}
	}
	unset($value);
}
$end_time = microtime(true);
echo 'by ref    :  '.($end_time - $start_time)."\n";

echo "\nTested $count iterations."

?>
</pre>
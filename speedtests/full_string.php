<pre>
What's faster for determining a full string?

<?php

$count = 10000000;

$test = 'this string is full';
$i = 0;

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	if (!$test) {
		echo 'error';
		break;
	}
}
$end_time = microtime(true);
echo '(!)       :  '.($end_time - $start_time)."\n";

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	if ($test == '') {
		echo 'error';
		break;
	}
}
$end_time = microtime(true);
echo '==        :  '.($end_time - $start_time)."\n";

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	if (empty($test)) {
		echo 'error';
		break;
	}
}
$end_time = microtime(true);
echo 'empty()   :  '.($end_time - $start_time)."\n";

echo "\nTested $count iterations."

?>
</pre>
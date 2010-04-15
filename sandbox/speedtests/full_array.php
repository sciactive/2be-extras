<pre>
What's faster for determining a full array?

<?php

$count = 10000000;

$test = array('im full', 'of things', 'and stuff');
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
	if ($test == array()) {
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
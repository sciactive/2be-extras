<pre>
What's faster for generating random strings?

<?php

$count = 10000;

$test = '';
$i = 0;

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	$test = 'p_'.rand();
}
$end_time = microtime(true);
echo 'rand()    :  '.($end_time - $start_time)."\n";

$test = '';

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	$test = 'p_'.mt_rand();
}
$end_time = microtime(true);
echo 'mt_rand() :  '.($end_time - $start_time)."\n";

$test = '';

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	$test = uniqid('p_');
}
$end_time = microtime(true);
echo 'uniqid()  :  '.($end_time - $start_time)."\n";

echo "\nTested $count iterations."

?>
</pre>
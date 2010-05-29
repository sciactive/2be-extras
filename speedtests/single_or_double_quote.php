<pre>
What's faster for creating strings? (without variables)

<?php

$count = 100000000;

$test = 'This is a string. It is made of characters.';
$i = 0;

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	$test = 'This is a string. It is made of characters.';
}
$end_time = microtime(true);
echo 'single (\'):  '.($end_time - $start_time)."\n";

$test = "This is a string. It is made of characters.";

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	$test = "This is a string. It is made of characters.";
}
$end_time = microtime(true);
echo 'double ("):  '.($end_time - $start_time)."\n";

echo "\nTested $count iterations."

?>
</pre>
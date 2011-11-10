<pre>
What's faster for escaping a string to go in JavaScript?

<?php

$count = 1000000;

$test = '"; alert("This page is vulnerable to a XSS attack!");</script><div style="background: red; height: 200px; width: 200px;">This page is vulnerable to a XSS attack!</div><script>'."\0";
$test2 = '';
$i = 0;

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	$test2 = addcslashes($test, "/\"\n\r\0");
}
$end_time = microtime(true);
echo 'addcslashes :  '.($end_time - $start_time)."\n";

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	$test2 = json_encode($test);
}
$end_time = microtime(true);
echo 'json_encode :  '.($end_time - $start_time)."\n";

echo "\nTested $count iterations."

?>
</pre>
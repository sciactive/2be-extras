<pre>
What's faster for getting an object's class name?

<?php

$count = 500000;
class tester {
	static public function get_class() {
		return 'tester';
	}
}
$tester = new tester;

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	$class = 'tester';
}
$end_time = microtime(true);
echo 'just know it :  '.($end_time - $start_time)."\n";

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	$class = get_class($tester);
}
$end_time = microtime(true);
echo 'get_class    :  '.($end_time - $start_time)."\n";

$classes = array('fisherman', 'fishguts', 'fishtacos', 'fisheyes', 'fishpants', 'fishnets', 'tester');
$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	foreach ($classes as $cur_class) {
		if ($tester instanceof $cur_class) {
			$class = $cur_class;
			break;
		}
	}
}
$end_time = microtime(true);
echo 'array search :  '.($end_time - $start_time)."\n";

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	$class = tester::get_class();
}
$end_time = microtime(true);
echo 'late static  :  '.($end_time - $start_time)."\n";

echo "\nTested $count iterations."

?>
</pre>
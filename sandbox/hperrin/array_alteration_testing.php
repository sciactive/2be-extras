<?php

echo '<h3>Element Addition Testing</h3><p>Must show 10 items to pass.</p>';

echo '<h4>foreach</h4>';
$array = range(0, 9);
foreach ($array as $cur_elem) {
	if ($cur_elem == 3)
		$array[] = 10;
	echo $cur_elem.' ';
}

echo '<h4>for.. reset, key, next</h4>';
$array = range(0, 9);
for ($cur_elem = reset($array); key($array) !== null; $cur_elem = next($array)) {
	if ($cur_elem == 3)
		$array[] = 10;
	echo $cur_elem.' ';
}

echo '<h4>reset; while(list = each)</h4>';
$array = range(0, 9);
reset($array);
while (list($cur_key, $cur_elem) = each($array)) {
	if ($cur_elem == 3)
		$array[] = 10;
	echo $cur_elem.' ';
}


echo '<h3>Element Removal Testing</h3><p>Must show item 9 to pass.</p>';

echo '<h4>foreach</h4>';
$array = range(0, 9);
foreach ($array as $cur_elem) {
	if ($cur_elem == 8)
		unset($array[8]);
	echo $cur_elem.' ';
}

echo '<h4>for.. reset, key, next</h4>';
$array = range(0, 9);
for ($cur_elem = reset($array); key($array) !== null; $cur_elem = next($array)) {
	if ($cur_elem == 8)
		unset($array[8]);
	echo $cur_elem.' ';
}

echo '<h4>reset; while(list = each)</h4>';
$array = range(0, 9);
reset($array);
while (list($cur_key, $cur_elem) = each($array)) {
	if ($cur_elem == 8)
		unset($array[8]);
	echo $cur_elem.' ';
}


echo '<h3>Element Addition and Removal Testing</h3><p>Must show 10 items to pass.</p>';

echo '<h4>foreach</h4>';
$array = range(0, 9);
foreach ($array as $cur_elem) {
	if ($cur_elem == 3)
		$array[] = 10;
	if ($cur_elem == 8)
		unset($array[8]);
	echo $cur_elem.' ';
}

echo '<h4>for.. reset, key, next</h4>';
$array = range(0, 9);
for ($cur_elem = reset($array); key($array) !== null; $cur_elem = next($array)) {
	if ($cur_elem == 3)
		$array[] = 10;
	if ($cur_elem == 8)
		unset($array[8]);
	echo $cur_elem.' ';
}

echo '<h4>reset; while(list = each)</h4>';
$array = range(0, 9);
reset($array);
while (list($cur_key, $cur_elem) = each($array)) {
	if ($cur_elem == 3)
		$array[] = 10;
	if ($cur_elem == 8)
		unset($array[8]);
	echo $cur_elem.' ';
}

?>
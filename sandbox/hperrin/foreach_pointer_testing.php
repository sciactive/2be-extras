<?php

$array = range(0, 14);

echo '<h3>'.htmlspecialchars('for ($cur_elem = reset($array); key($array) !== null; $cur_elem = next($array))').'</h3>';
for ($cur_elem = reset($array); key($array) !== null; $cur_elem = next($array)) {
	var_dump(array('Current Pointer' => key($array), 'Current Value' => $cur_elem));
}
echo "<br />";
echo '<h3>'.htmlspecialchars('while (list($cur_key, $cur_elem) = each($array))').'</h3>';
reset($array);
while (list($cur_key, $cur_elem) = each($array)) {
	var_dump(array('Current Pointer' => key($array), 'Current Value' => $cur_elem));
}
echo "<br />";
echo '<h3>'.htmlspecialchars('foreach ($array as $cur_elem)').'</h3>';
foreach ($array as $cur_elem) {
	var_dump(array('Current Pointer' => key($array), 'Current Value' => $cur_elem));
}

?>
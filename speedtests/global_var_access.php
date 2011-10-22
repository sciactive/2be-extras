<pre>
What's faster for accessing a global variable?

<?php

$count = 500000;

class fishtaco {
	public $style = 'baja';
	public static final function &get() {
		return $GLOBALS['fishtaco'];
	}
}
$fishtaco = new fishtaco;
function &fishtaco() {
	return $GLOBALS['fishtaco'];
}

function use_global() {
	global $fishtaco;
	$fishtaco->style;
	return $fishtaco->style;
}

function use_function() {
	fishtaco()->style;
	return fishtaco()->style;
}

function use_static() {
	fishtaco::get()->style;
	return fishtaco::get()->style;
}

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	if ($fishtaco->style != 'baja') {
		echo 'oh no';
		break;
	}
}
$end_time = microtime(true);
echo 'global scope $var   :  '.($end_time - $start_time)."\n";

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	if (fishtaco()->style != 'baja') {
		echo 'oh no';
		break;
	}
}
$end_time = microtime(true);
echo 'global scope var()  :  '.($end_time - $start_time)."\n";

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	if (fishtaco::get()->style != 'baja') {
		echo 'oh no';
		break;
	}
}
$end_time = microtime(true);
echo 'global scope ::()   :  '.($end_time - $start_time)."\n";

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	if (use_global() != 'baja') {
		echo 'oh no';
		break;
	}
}
$end_time = microtime(true);
echo 'function scope $var :  '.($end_time - $start_time)."\n";

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	if (use_function() != 'baja') {
		echo 'oh no';
		break;
	}
}
$end_time = microtime(true);
echo 'function scope var():  '.($end_time - $start_time)."\n";

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	if (use_static() != 'baja') {
		echo 'oh no';
		break;
	}
}
$end_time = microtime(true);
echo 'function scope ::() :  '.($end_time - $start_time)."\n";

echo "\nTested $count iterations."

?>
</pre>
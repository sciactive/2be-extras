<pre>
What's faster for making function shortcuts?

<?php

$count = 500000;

class fishtaco {
	public $style = 'baja';
	public function get_style() {
		return $this->style;
	}
}
$fishtaco = new fishtaco;
function get_style() {
	global $fishtaco;
	return $fishtaco->get_style();
}

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	if ($fishtaco->get_style() != 'baja') {
		echo 'oh no';
		break;
	}
}
$end_time = microtime(true);
echo 'no shortcut   :  '.($end_time - $start_time)."\n";

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	if (get_style() != 'baja') {
		echo 'oh no';
		break;
	}
}
$end_time = microtime(true);
echo 'w/ shortcut   :  '.($end_time - $start_time)."\n";

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	if (call_user_func(array($fishtaco, 'get_style')) != 'baja') {
		echo 'oh no';
		break;
	}
}
$end_time = microtime(true);
echo 'call_user_func:  '.($end_time - $start_time)."\n";

echo "\nTested $count iterations."

?>
</pre>
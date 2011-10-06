<?php

class trans {
	public $message = '';
	public $arguments = array();

	public function __construct($message = null) {
		if (!isset($message))
			return;
		$this->message = $message;
		$this->arguments = func_get_args();
		unset($this->arguments[0]);
		$this->arguments = array_values($this->arguments);
	}

	public function __toString() {
		return $this->get_translation();
	}

	public function __invoke($message) {
		$this->message = $message;
		$this->arguments = func_get_args();
		unset($this->arguments[0]);
		$this->arguments = array_values($this->arguments);

		return $this->get_translation();
	}
	
	public function get_translation() {
		$trans = array(
			'Hi' => 'Hey',
			'Bye' => 'Boo',
			'Howdy %s. You\'ve been here %d times.' => 'You\'ve been here %2$d times, %1$s! %2$d times!!',
		);
		$i10n = isset($trans[$this->message]) ? $trans[$this->message] : $this->message;
		if ($this->arguments)
			$i10n = call_user_func_array('sprintf', array_merge(array($i10n), $this->arguments));
		return $i10n;
	}
}

$trans = new trans;
function pines_echo($message) {
	global $trans;
	$trans->message = $message;
	$trans->arguments = func_get_args();
	unset($trans->arguments[0]);
	$trans->arguments = array_values($trans->arguments);
	return $trans->get_translation();
}

echo (new trans('Hi'))."<br />";
echo $trans('Bye')."<br />";
echo pines_echo('Howdy %s. You\'ve been here %d times.', 'Bob', 9)."<br />";

?>
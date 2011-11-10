<?php

function print_entries($entry) {
	?>
	<li>
	Name: <a href="<?php echo isset($entry->ref) ? $entry->ref : '#'; ?>"><?php echo $entry->name; ?></a><br />
	<?php if (isset($entry->depends)) { ?>
		Depends: <?php var_dump($entry->depends); ?>
	<?php } ?>
	<?php if (isset($entry->entry)) { ?>
		<ul>
		<?php foreach ($entry->entry as $cur_entry) {
			print_entries($cur_entry);
		} ?>
		</ul>
	<?php } ?>
	</li>
	<?php

}

?>

<html>
	<head>
		<title>Menu Test</title>
	</head>
	<body>
<?php
class tep {
	function check($type = null, $value = null) {
		if ($type == 'ability' && $value == 'com_logger/view|com_logger/clear')
			return false;
		return true;
	}
}
$pines = new stdClass;
$pines->depend = new tep;

$position = 'main_menu';

$xmldoc = new DOMDocument();
$xmldoc->load('menu.xml');
$xsldoc = new DOMDocument();
$xsldoc->load('menu.xsl');

$xpath = new DOMXPath($xmldoc);
$menu_nodes = $xpath->query("/menus/menu[position=\"$position\"]");

$xslproc = new XSLTProcessor();
$xslproc->registerPHPFunctions();
$xslproc->importStyleSheet($xsldoc);
for ($i = 0; $i < $menu_nodes->length; $i++) {
	$cur_menu_node = $menu_nodes->item($i);
	$cur_menu_xml_doc = new DOMDocument();
	$cur_menu_xml_doc->loadXML($xmldoc->saveXML($cur_menu_node));
	echo $xslproc->transformToXML($cur_menu_xml_doc);
}

function pines_depend_xml($dom_array = null) {
	global $pines;
	if ( !is_array($dom_array) || is_null($dom_array[0]) )
		return true;
	$dom_element = $dom_array[0];
	if ( !$dom_element->hasChildNodes() )
		return true;
	$depends = $dom_element->getElementsByTagName('*');
	$return = true;
	for ($i = 0; $i < $depends->length; $i++) {
		$cur_depend = $depends->item($i);
		$return = $return && $pines->depend->check($cur_depend->nodeName, $cur_depend->nodeValue);
	}
	return $return;
}
?>
	</body>
</html>
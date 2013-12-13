<?php

	$xslDoc = new DOMDocument();
	$xslDoc->load("sales.xsl");

	$xmlDoc = new DOMDocument();
	$xmlDoc->load("sales.xml");

	$proc = new XSLTProcessor();
	$proc->importStylesheet($xslDoc);
	echo $proc->transformToXML($xmlDoc);
	
	/*
	echo '<hr /> XPATH: <br />';
	$xml = simplexml_load_file("sales.xml");
	$result = $xml->xpath("sale/items/item");
	print_r($result);
	 */
<?php

function check_cidr($ip, $cidr) {
	// Separate the CIDR notation.
	$ip_arr = explode('/', $cidr);
	// Fill in any missing ".0" parts, and turn the address into a long.
	$cidr_long = ip2long($ip_arr[0].str_repeat('.0', 3 - substr_count($ip_arr[0], '.')));
	$cidr_bits = (int) $ip_arr[1];
	// Turn the IP into a long.
	$ip_long = ip2long($ip);

	// Get the network part of the CIDR and the IP.
	$cidr_network = $cidr_long >> (32 - $cidr_bits);
	$ip_network = $ip_long >> (32 - $cidr_bits);

	// If the network parts are equal, return true.
	return ($cidr_network === $ip_network);
}

function check_subnet($ip, $network, $netmask) {
	// Turn the addresses into long format.
	$network_long = ip2long($network);
	$mask_long = ip2long($netmask);
	$ip_long = ip2long($ip);

	// Remove the host part of the addresses.
	$network_net_long = $network_long & $mask_long;
	$ip_net_long = $ip_long & $mask_long;

	// If the network parts are equal, return true.
	return ($network_net_long === $ip_net_long);
}

function check_range($ip, $from_ip, $to_ip) {
	// Turn the addresses into long format.
	$from_ip_long = ip2long($from_ip);
	$to_ip_long = ip2long($to_ip);
	$ip_long = ip2long($ip);

	// If the IP is between the two addresses, return true.
	return ($ip_long >= $from_ip_long && $ip_long <= $to_ip_long);
}


foreach (array('192.168.0.1', '192.168.1.1', '192.168.2.1') as $ip) {
	echo "<h2>IP: $ip</h2>\n";
	$cidr = '192.168/23';
	echo '<pre>';
	echo "CIDR Address:     $cidr\n";
	echo '</pre>';
	echo check_cidr($ip, $cidr) ? 'Same network.' : 'Different network.';

	$network = '192.168.0.0';
	$netmask = '255.255.254.0';
	echo '<pre>';
	echo "Network Address:  $network\n";
	echo "Subnet Mask:      $netmask\n";
	echo '</pre>';
	echo check_subnet($ip, $network, $netmask) ? 'Same network.' : 'Different network.';

	$from_ip = '192.168.0.0';
	$to_ip = '192.168.1.255';
	echo '<pre>';
	echo "IP Range:         $from_ip - $to_ip\n";
	echo '</pre>';
	echo check_range($ip, $from_ip, $to_ip) ? 'Same network.' : 'Different network.';
}

?>
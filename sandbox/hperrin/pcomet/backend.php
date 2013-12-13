<?php
// Make sure no time limit.
set_time_limit(0);

// This class is used to run an HTTP server.
// TODO: Implement SSL/TLS.
class http_server {
	public $socket;
	public function __construct($address = '127.0.0.1', $port = 80, $timeout = 20) {
		$this->start($address, $port, $timeout);
	}

	public function start($address, $port, $timeout = 20) {
		if (!$address || !$port)
			return false;
		$this->socket = stream_socket_server('tcp://'.$address.':'.$port, $errno, $errstr);
		if ($errno)
			return false;
		stream_set_timeout($this->socket, $timeout);
		stream_set_blocking($this->socket, 1);
		return true;
	}

	public function stop() {
		return stream_socket_shutdown($this->socket, STREAM_SHUT_RDWR);
	}

	public function get_request($required_peer = null) {
		$read = array($this->socket);
		$write = array();
		$except = array();
		if (!stream_select($read, $write, $except, 0))
			return false;
		$connection = @stream_socket_accept($this->socket, 0, $peername);
		if ($connection === false)
			return false;
		if ($required_peer && $required_peer != $peername)
			return false;
		$data = stream_socket_recvfrom($connection, 10*1024*1024); // 10 MB Limit.
		if (strpos($data, "\r\n\r\n") === false) // Not a valid HTTP request.
			return false;
		$lines = explode("\r\n", $data);
		if (!preg_match('/^(HEAD|GET|POST|PUT|DELETE|TRACE|OPTIONS|CONNECT|PATCH) (\/[\w\-.~!$&\'()*+,;=:@%\/]*) (http\/1\.[01])$/i', $lines[0], $matches)) // Sorry, only HTTP requests.
			return false;
		unset($lines[0]);
		$request = array(
			'connection' => $connection,
			'client' => $peername,
			'time' => time(),
			'method' => strtoupper($matches[1]),
			'path' => $matches[2],
			'http_version' => preg_replace('/http\//i', '', $matches[3]),
			'headers' => array()
		);
		$still_headers = true;
		$body_lines = array();
		foreach ($lines as $cur_line) {
			if ($cur_line == '')
				$still_headers = false;
			if ($still_headers) {
				list($name, $content) = explode(': ', $cur_line, 2);
				if (!isset($request['headers'][$name])) // New header, simply append.
					$request['headers'][$name] = $content;
				elseif ((array) $request['headers'][$name] === $request['headers'][$name]) // Third or more instance, append to array.
					$request['headers'][$name][] = $content;
				else // Second instance, turn into an array.
					$request['headers'][$name] = array($request['headers'][$name], $content);
			} else
				$body_lines[] = $cur_line;
		}
		// Put the body back together.
		$request['body'] = implode("\r\n", $body_lines);
		return $request;
	}

	public function send_response($connection, $code, $status, $headers, $body, $address = null, $leave_open = false) {
		$lines = array();
		$lines[] = 'HTTP/1.1 '.$code.' '.$status;
		foreach ($headers as $cur_name => $cur_content) {
			if ((array) $cur_content === $cur_content) {
				foreach ($cur_content as $cur_duplicate)
					$lines[] = $cur_name.': '.$cur_duplicate;
			} else
				$lines[] = $cur_name.': '.$cur_content;
		}
		$response = implode("\r\n", $lines);
		$response .= "\r\n\r\n".$body;
		if ($address)
			$result = stream_socket_sendto($connection, $response, 0, $address);
		else
			$result = stream_socket_sendto($connection, $response);
		if (!$leave_open)
			stream_socket_shutdown($connection, STREAM_SHUT_RDWR);
		return $result;
	}
}

// Send the packet delimiters in the HTTP headers.
header("X-Begin-Mark: [[DATABEGIN]]");
header("X-End-Mark: [[DATAEND]]");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
// Keep this connection open to allow server push.
header("Connection: Keep-Alive");
header("Keep-Alive: timeout=60, max=98");
header("Content-Length: 0");
//header("Content-Type: text/plain");
//header("Content-Type: application/octet-stream");
// Using this mime-type allows Chrome to show new data before connection
// terminates.
header("Content-Type: application/x-javascript");

$data_in = file_get_contents("php://input");

if ($data_in == '[[STREAMBEGIN]]') {
	session_start();
	$stream_id = uniqid('pcomet_stream_');
	$_SESSION[$stream_id] = '';
}
flush();

// Start a listening server, to accept data coming into the server on a
// different port.
$server = new http_server('127.0.0.1', 20000);

// Pretend to be a comment, just cause we can.
echo '/*';

ob_end_flush();
flush();

while (true) {
	// Check that the client hasn't disconnected.
	if (connection_aborted())
		break;
	// Each cycle, check if there is an incoming connection on the listening
	// server.
	// TODO: Only accept connections from the connected client.
	$request = $server->get_request();
	if ($request) {
		// Send a response.
		$server->send_response($request['connection'], 200, 'OK', array('Content-Type' => 'text/plain', 'Content-Length' => '1'), ' ', $request['client']);
		// Echo the received data. (For testing.)
		echo "[[DATABEGIN]]".print_r($request, true)."[[DATAEND]]";
	} else {
		if (rand(1, 3) === 1) {
			// Simulates new data available.
			echo "[[DATABEGIN]]".time()."[[DATAEND]]";
		} else {
			// Send something to keep the connection alive.
			echo "\n";
		}
	}
	// Push the waiting data to the client.
	flush();
	// Give the server some time to rest. (Don't use up too many resources.)
	sleep(4);
}

// The client disconnected, so stop the listening server.
$server->stop();

// This is totally unnecessary. ;)
echo '*/';
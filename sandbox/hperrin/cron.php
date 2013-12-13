<?php
switch ($pid = pcntl_fork()) {
	case -1:
		die('could not fork');
		break;
	case 0:
		// Child Process
		ignore_user_abort(true);
		set_time_limit(0);
		/* Continue after request cancelled.
		while(true) {
			echo date('c');
			flush();
			if(connection_aborted())
				break;
			sleep(1);
		}
		 */
		while(true) {
			file_put_contents('log', date('c'));
			sleep(1);
		}
		break;
	default:
		// Parent Process
		echo "Child process started. Process ID: $pid";
}
?>
<?php
/*function index() {
		function shutdown() {
			posix_kill(posix_getpid(), SIGHUP);
		}

		// Do some initial processing

		echo("Hello World");

		// Switch over to daemon mode.

		if ($pid = pcntl_fork())
			return;	 // Parent

		ob_end_clean(); // Discard the output buffer and close

		fclose(STDIN);  // Close all of the standard
		fclose(STDOUT); // file descriptors as we
		fclose(STDERR); // are running as a daemon.

		register_shutdown_function('shutdown');

		if (posix_setsid() < 0)
			return;

		if ($pid = pcntl_fork())
			return;	 // Parent

		// Now running as a daemon. This process will even survive
		// an apachectl stop.

		sleep(10);

		$fp = fopen("/tmp/sdf123", "w");
		fprintf($fp, "PID = %s\n", posix_getpid());
		fclose($fp);

		return;
}*/
<pre>
What's faster for accessing some vars from database?

<?php

$count = 10;
$db_items = 5000;
$vars = array('price', 'user', 'group', 'name', 'description');
$get_vars = 5;

$link = pg_connect('host=localhost dbname=dbtest user=dbtest password=password');

if ($_REQUEST['skip_setup'] != 'true') {
	// Set up a database.
	pg_query($link, "
	DROP TABLE test;
	CREATE TABLE test
	(
	  id bigint NOT NULL,
	  \"name\" text NOT NULL,
	  \"value\" text,
	  CONSTRAINT id_name PRIMARY KEY (id, name)
	)
	WITH (
	  OIDS=FALSE
	);");
	for ($id = 0; $id < $db_items; $id++) {
		$values = array();
		foreach ($vars as $cur_var) {
			$values[] = "($id, '$cur_var', '".uniqid()."')";
		}
		pg_query($link, "INSERT INTO test VALUES ".implode(',', $values).";");
	}
}

// Set this now so the first for loop doesn't do the extra work.
$i = $start_time = $end_time = 0;

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	for ($id = 0; $id < $db_items; $id++) {
		$varnames = array_slice($vars, 0, $get_vars);
		foreach ($varnames as $cur_var) {
			$result = pg_query($link, "SELECT * FROM test WHERE id=$id AND name='$cur_var';");
			$values = pg_fetch_assoc($result);
			//echo "{$values['name']} = {$values['value']}<br />";
		}
		//break;
	}
	//break;
}
$end_time = microtime(true);
echo 'by one :  '.($end_time - $start_time)."\n";

$start_time = microtime(true);
for ($i = 0; $i < $count; $i++) {
	for ($id = 0; $id < $db_items; $id++) {
		$varnames = array_slice($vars, 0, 5);
		$result = pg_query($link, "SELECT * FROM test WHERE id=$id AND (name='".implode("' OR name='", $varnames)."');");
		while ($values = pg_fetch_assoc($result)) {
			//echo "{$values['name']} = {$values['value']}<br />";
		}
		//break;
	}
	//break;
}
$end_time = microtime(true);
echo 'multi  :  '.($end_time - $start_time)."\n";

echo "\nTested $count iterations.";

?>
</pre>

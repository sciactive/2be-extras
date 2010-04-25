<html>
	<head><title>Index File Checker</title></head>
	<body>
		<p>The following source directories in pines/ don't have index.html files!</p>
		<p>
			<?php
			function check_index($dir) {
				$files = scandir($dir);
				if ($dir != '../../pines' && !in_array('index.html', $files))
					echo $dir."<br />\n";
				foreach ($files as $cur_file) {
					if (!in_array($cur_file, array('.', '..', '.svn', '.hg', 'includes')) && is_dir($dir.'/'.$cur_file))
						check_index($dir.'/'.$cur_file);
				}
			}
			check_index('../../pines');
			?>
		</p>
	</body>
</html>
<?php

/**
 * Find all the files under a directory that match a given pattern.
 * @param string $base_dir The directory to search.
 * @param string $pattern The pattern to match.
 * @return array An array of file names.
 */
function get_files($base_dir, $pattern) {
	$dir_iterator = new RecursiveDirectoryIterator($base_dir);
	$iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::CHILD_FIRST);
	$files = array();

	foreach ($iterator as $file_path) {
		if (!is_file($file_path) || !fnmatch($pattern, basename($file_path)))
			continue;
		$files[] = (string) $file_path;
	}
	sort($files);
	return $files;
}

if ($_REQUEST['q']) {
	header('Content-Type: application/json');

	$regex = '/'.$_REQUEST['search'].'/';
	if ($_REQUEST['mod_i'] == 'true')
		$regex .= 'i';
	if ($_REQUEST['mod_m'] == 'true')
		$regex .= 'm';
	if ($_REQUEST['mod_s'] == 'true')
		$regex .= 's';
	$limit = is_numeric($_REQUEST['per_file_limit']) ? (int) $_REQUEST['per_file_limit'] : -1;

	$files = get_files($_REQUEST['base_dir'], $_REQUEST['pattern']);
	$result = array('count' => count($files));
	foreach ($files as &$cur_file) {
		$contents = file_get_contents($cur_file);
		$success = true;
		switch ($_REQUEST['q']) {
			case 'search':
			default:
				$match_count = preg_match_all($regex, $contents, $matches);
				break;
			case 'replace':
				$new_contents = preg_replace($regex, $_REQUEST['replace'], $contents, $limit, $match_count);
				if ($new_contents) {
					if ($new_contents !== $contents)
						$success = (bool) file_put_contents($cur_file, $new_contents);
				} else
					$success = false;
				break;
		}
		$cur_file = array(
			'absolute' => (string) $cur_file,
			'path' => substr($cur_file, strlen($_REQUEST['base_dir'])+1),
			'size' => filesize($cur_file),
			'match_count' => $match_count,
			'matches' => $matches[0],
			'success' => $success
		);
	}
	unset($cur_file);
	$result['files'] = $files;
	echo json_encode($result);
	return;
}

?>
<!DOCTYPE html>
<html>
	<head>
		<title>RegEx Search Replace</title>
		<meta charset="utf-8" />
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.js"></script>
		<script type="text/javascript" src="http://current.bootstrapcdn.com/bootstrap-v204/js/bootstrap.min.js"></script>
		<link type="text/css" rel="stylesheet" href="http://current.bootstrapcdn.com/bootstrap-v204/css/bootstrap.min.css" />
		<link type="text/css" rel="stylesheet" href="http://current.bootstrapcdn.com/bootstrap-v204/css/bootstrap-responsive.css" />
		<script type="text/javascript">
			$(function(){
				var results_body = $("#results tbody");
				var clear_results = function(){
					results_body.children().remove();
				};
				var buttons = $("#search_btn, #replace_btn").click(function(){
					var q = $(this).is("#search_btn") ? 'search' : 'replace';
					$.ajax({
						url: <?php echo json_encode(basename(__FILE__)); ?>,
						type: 'POST',
						dataType: 'json',
						data: {
							'q': q,
							'base_dir': $("#base_dir").val(),
							'pattern': $("#pattern").val(),
							'search': $("#search_text").val(),
							'replace': $("#replace_text").val(),
							'per_file_limit': $("#per_file_limit").val(),
							'mod_i': $("#mod_i").is(":checked") ? 'true' : 'false',
							'mod_m': $("#mod_m").is(":checked") ? 'true' : 'false',
							'mod_s': $("#mod_s").is(":checked") ? 'true' : 'false'
						},
						beforeSend: function(){
							clear_results();
							$("#type_header").text(q == 'search' ? 'Matches' : 'Replacements');
							buttons.attr("disabled", "disabled");
						},
						complete: function(){
							buttons.removeAttr("disabled");
						},
						error: function(){
							alert("An error occured. :(");
						},
						success: function(data){
							$("#results_count").text(data.count+" files searched.");
							$.each(data.files, function(i, e){
								var row = $("<tr/>")
								.append($("<td/>").text(e.path))
								.append($("<td/>").text(e.size))
								.append($("<td/>").text(e.match_count));
								if (q == 'replace' && !e.success)
									row.addClass('alert-error');
								else if (e.match_count > 0)
									row.addClass('alert-success');
								else
									row.addClass('alert-info');
								results_body.append(row);
							});
						}
					});
				});
			});
		</script>
	</head>
	<body>
		<div class="container">
			<div class="page-header">
				<h1>Regular Expression Search & Replace
					<small>For Files</small></h1>
			</div>
			<p>
				There are much more efficient programs for regular expression
				searching in files, but this one has one big advantage. It loads
				the entire contents of the file before searching. (So try not to
				search huge files.) This allows you to search over multiple
				lines! <i class="icon-thumbs-up"></i>
			</p>
			<h3>Select Files</h3>
			<div class="row">
				<div class="span8">
					<div style="padding-right: 8px;">
						<label>Base Directory
							<input type="text" id="base_dir" style="width: 100%;" value="<?php //echo htmlspecialchars(__DIR__); ?>/home/hunter/htdocs/components/com_bootstrap/includes/themes" /></label>
					</div>
				</div>
				<div class="span4">
					<div style="padding-right: 8px;">
						<label>File Selection Pattern (Shell Style)
							<input type="text" id="pattern" style="width: 100%;" value="*.css" /></label>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="span6">
					<h3>Search</h3>
					<div style="padding-right: 8px;">
						<textarea rows="5" cols="24" style="width: 100%;" id="search_text">\[class\^="icon-"\].*\.icon-fullscreen.*?\}\n</textarea>
					</div>
					<div class="pull-right form-inline">
						<label style="margin: 0 8px;" class="checkbox"><input type="checkbox" id="mod_i" /> Ignore Case</label>
						<label style="margin: 0 8px;" class="checkbox"><input type="checkbox" id="mod_m" /> Match ^$ at New Lines</label>
						<label style="margin: 0 8px;" class="checkbox"><input type="checkbox" id="mod_s" checked="checked" /> Dot Matches All</label>
						<button class="btn btn-primary" id="search_btn">Search</button>
					</div>
				</div>
				<div class="span6">
					<h3>Replace</h3>
					<div style="padding-right: 8px;">
						<textarea rows="5" cols="24" style="width: 100%;" id="replace_text"></textarea>
					</div>
					<div class="pull-right form-inline">
						<label style="margin: 0 8px;">Per File Limit (Blank for no limit.): <input type="text" class="input-small" id="per_file_limit" /></label>
						<button class="btn btn-primary" id="replace_btn">Replace</button>
					</div>
				</div>
			</div>
			<div class="page-header">
				<h2>Results <small id="results_count">Click Search to see results.</small></h2>
			</div>
			<table class="table table-bordered table-condensed" id="results">
				<thead>
					<tr>
						<th>File</th>
						<th>Size</th>
						<th id="type_header">Matches</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
	</body>
</html>
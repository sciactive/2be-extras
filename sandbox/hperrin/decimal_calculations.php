<!DOCTYPE html>
<html>
	<head>
		<title>Working Around PHP's Floating Point Calculation Errors</title>
		<meta charset="UTF-8" />
		<style type="text/css">
			body {
				font-family: Arial, sans-serif;
				font-size: 10pt;
			}
			pre {
				background-color: beige;
				border: navy solid 1px;
				color: navy;
				padding: .3em;
				margin-left: 2em;
			}
		</style>
	</head>
	<body>
		<h1>Working Around PHP's Floating Point Calculation Errors</h1>

		<h2>Problem</h2>
		<p>PHP's internal representation of floats uses binary (duh), so simple rational numbers like .7 can't always be exactly represented.</p>
		<pre>serialize(.7) = <?php echo serialize(.7); ?></pre>
		<p>This means that when these numbers are used in calculations, the results may not be what is expected.</p>
		<pre>serialize((.7 + .1) * 10) = <?php echo serialize((.7 + .1) * 10); ?></pre>

		<h2>Solution</h2>
		<p>When a float is cast to a string, PHP is smart enough to realize that its binary (inexact) representation probably means the exact decimal representation.</p>
		<pre>serialize((string) .7) = <?php echo serialize((string) .7); ?></pre>
		<p>These string representations can be used in calculations just like floats.</p>
		<pre>serialize((string) (.7 + .1) * 10) = <?php echo serialize((string) (.7 + .1) * 10); ?></pre>

		<h2>Examples</h2>
		<h3>Without String Casting</h3>
		<pre>floor((.7 + .1) * 10) = <strong><?php echo floor((.7 + .1) * 10); ?></strong></pre>
		<h3>With String Casting</h3>
		<pre>floor((string) (.7 + .1) * 10) = <strong><?php echo floor((string) (.7 + .1) * 10); ?></strong></pre>
	</body>
</html>
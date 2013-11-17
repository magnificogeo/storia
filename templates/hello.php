<?php
//  hello there this is a photowall
?>

<html>

<head>
	<script src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
	<script src="http://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
	<script src="/storia/server/templates/js/freewall.js"></script>
	<link rel="stylesheet" type="text/css" href="/storia/server/templates/css/style.css">
</head>

<body>
	<ul id="container">
		<li class="item"></li>
		<li class="item"></li>
		<li class="item"></li>
		<li>
			<ul id="sub-container">
				<li class="item"></li>
				<li class="item"></li>
				<li class="item"></li>
				<li class="item"></li>
			</ul>
		</li>
	</ul>

	<script>
		$(function() {
			var wall = new freewall('#container, #sub-container');
			wall.fitWidth();
		});
	</script>
</body>
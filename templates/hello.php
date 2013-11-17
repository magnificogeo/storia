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
	<div id="freewall" class="free-wall"></div>

	<script>
		$(function() {
			var temp = "<div class='cell' style='width:{width}px; height: {height}px; background-image: url(i/photo/{index}.jpg)'></div>";
                        var w = 1, h = 1, html = '', limitItem = 49;
                        for (var i = 0; i < limitItem; ++i) {
                                h = 1 + 3 * Math.random() << 0;
                                w = 1 + 3 * Math.random() << 0;
                                html += temp.replace(/\{height\}/g, h*150).replace(/\{width\}/g, w*150).replace("{index}", i + 1);
                        }
                        $("#freewall").html(html);
                        
                        var ewall = new freewall("#freewall");
                        ewall.reset({
                                selector: '.cell',
                                animate: true,
                                cellW: 150,
                                cellH: 150,
                                onResize: function() {
                                        ewall.fitWidth();
                                }
                        });
                        ewall.fitWidth();
                        // for scroll bar appear;
                        $(window).trigger("resize");
		});
	</script>
</body>
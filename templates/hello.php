<?php
// hello there
?>

<html>

<head>
	<script src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
	<script src="http://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
</head>
<script type="text/javascript">
	jQuery(document).ready( function() {

		jQuery('#login-button').on( 'click', function() {

			var user_name = jQuery('#user-name').val();
			var password = jQuery('#user-password').val();

			var request_body = { "user_name": user_name, "password": password };

			jQuery.ajax({
				url:"http://localhost/storia/server/index.php/api/login",
				type:"POST",
				data: request_body,
				dataType: "json",
				success: function( data ) {

					console.log( data );

				}
			});

		});

	});
</script>



<body>

	<input type="text" id="user-name">

		<br />

	<input type="text" id="user-password">

	<input type="button" id="login-button" value="login">

</body>

</html>
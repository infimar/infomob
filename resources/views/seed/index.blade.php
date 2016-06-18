<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>

	<script   src="https://code.jquery.com/jquery-2.2.4.min.js"   integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44="   crossorigin="anonymous"></script>

	<script src="{{ asset('data/org.json') }}"></script>

	<script type="text/javascript">

		/**
		 * Seeds a category
		 * @param  {json} category to seed
		 * @return {void}          
		 */
		var seedCategory = function(category) {
			console.log(category);
		}

		//
		// MAIN
		//
		$(document).ready(function() {

			seedCategory(category);

		});
	</script>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
	<title>returnTrue</title>
	<!-- Bootstrap -->
	<link href="css/bootstrap.min.css" rel="stylesheet"	media="screen">
	<link href="css/style.css" rel="stylesheet" media="screen">
</head>
<body>
	<div class = "row" style = "height: 100px">
	</div>
	<div class = "row">
		<div class = "col-md-6 col-md-offset-3">
		<?php
			$m = new MongoClient();
   			$db = $m -> hindu;
   			$collection = $db -> chennai;
			$name = $_POST["name"];
			$content = $_POST["content"];
			$city = $_POST["city"];
			$locality = $_POST["locality"];
			$range = $_POST["range"];
			$rating = $_POST["rating"];
			$category = $_POST["category"];
			$classified = array("name" => $name, "content" => $content, "city" => $city, "locality" => $locality, "range" => $range, "rating" => $rating, "category" => $category);
			try
			{
				$collection -> insert($classified); 
				echo "Success!";

			}
			catch(MongoCursorException $e)
			{
				echo "Classified already exists!";
			}
		?>
		</div>
	</div>
</body>
</html>

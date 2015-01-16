<!DOCTYPE html>
<html>
<head>

	<title>returnTrue</title>
	<link href="css/styles.css" rel="stylesheet"	media="screen">
	<!-- Bootstrap -->
	<link href="css/bootstrap.min.css" rel="stylesheet"	media="screen">
	<link href="css/style.css" rel="stylesheet" media="screen">
</head>
<body>
	<?php		
		//config
		$m = new MongoClient();
   		$db = $m -> hindu;
   		$collection = $db -> chennai;
		$query = strtolower($_POST["query"]);
			
			
		//for location based queries
		$splitQuery = explode(" ", $query);
		if (in_array('at', $splitQuery) !== false)
		{
			$locationIndex =  array_search('at', $splitQuery) + 1;
			$location = $splitQuery[$locationIndex];
		}
		if (in_array('in', $splitQuery) !== false)
		{
			$locationIndex = array_search('in', $splitQuery) + 1;
			$location = $splitQuery[$locationIndex];
		}
		
		
		//perform  full text search and sort based on score
		
		//if location is set
		if (isset($location))
		{
			$result = $collection -> find(
					   array('locality' => $location, '$text' => array('$search' => "\\" . $query . "\\")), 
					   array('$score' => array( '$meta' => "textScore")))-> sort(array('$score' => array('$meta' => 'textScore')));
		}	   
		//if location is not specified
		else
		{		   
			$result = $collection -> find(
					   array('$text' => array('$search' => "\\" . $query . "\\")), 
					   array('$score' => array( '$meta' => "textScore")))-> sort(array('$score' => array('$meta' => 'textScore')));
		}	   
			
		//iterate over the result set
		foreach($result as $res)
		{
			/*to-do:
			  to be formatted for the user
			  add sorting
			  display date of posting that will also become a sorting option
			*/
			echo '<div class = "row">' . "\n";
			echo '<div class = "col-md-8 col-md-offset-2">' . "\n";
			echo "<div class = 'result'>";
			echo "</div>" . "\n";
			echo "<div class = 'resultTitle'>";
				echo $res['name'];
			echo "</div>" . "\n";
			echo "<div class = 'resultContent'>";
				echo $res['content'];
			echo "</div>" . "\n";
			echo "<div class = 'resultLocality'>";
				echo $res['locality'];
			echo "</div>" . "\n";
			echo "<div class = 'resultRating'>";
				while($res['rating'] > 0)
				{
					echo "&#x2605;";
					$res['rating'] -= 1;
				}
			echo "</div>" . "\n";
			echo "<div class = 'resultRange'>";
				echo $res['range'];
			echo "</div>" . "\n";
			echo "<div class = 'resultPhone'>";
				echo "<a href = 'tel:" . $res['phone'] . "'>" . $res['phone'] . "</a>";
			echo "</div>" . "\n";
			echo "</div>" . "\n";
			echo "</div>" . "\n";
		}
		?>
</body>
</html>


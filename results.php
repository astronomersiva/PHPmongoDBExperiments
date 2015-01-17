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
		$query = $_POST["query"];
		
		//for location based queries
		
		//add a city alternative
		//else xxx in chennai will not work
		//instead only xxx in adyar/* will work
		//also add error handler for 'in', 
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
		if (in_array('near', $splitQuery) !== false)
		{
			$locationIndex =  array_search('near', $splitQuery) + 1;
			$location = $splitQuery[$locationIndex];
		}
		
		
		//perform  full text search and sort based on score
		// '$text' => array('$search' => "\\" . $query . "\\")
		// add the above line to perform full phrase.
		//unable to decide if i shud go for it now
		//a larger dataset would make that clear
		//if location is set
		if (isset($location))
		{
			$result = $collection -> find(
					   array('locality' => new MongoRegex("/".$location."/i"), '$text' => array('$search' =>  "\\" . $query . "\\" )), 
					   array('$score' => array( '$meta' => "textScore")))-> sort(array('$score' => array('$meta' => 'textScore')));
		}	   
		//if location is not specified
		else
		{		   
			$result = $collection -> find(
					   array('$text' => array('$search' => "\\" . $query . "\\" )), 
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
				echo "Price range:&nbsp;&nbsp;&nbsp;&nbsp;" . $res['range'];
			echo "</div>" . "\n";
			echo "<div class = 'resultPhone'>";
				echo "Contact:&nbsp;&nbsp;&nbsp;&nbsp;<a href = 'tel:" . $res['phone'] . "'>" . $res['phone'] . "</a>";
			echo "</div>" . "\n";
			echo "</div>" . "\n";
			echo "</div>" . "\n";
		}
		?>
</body>
</html>


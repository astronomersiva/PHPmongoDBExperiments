<!DOCTYPE html>
<html>
<head>

	<title>returnTrue</title>
	<!-- Bootstrap -->
	<link href="css/bootstrap.min.css" rel="stylesheet"	media="screen">
	<link href="css/style.css" rel="stylesheet" media="screen">
</head>
<body>
	<div class = "row">
		<div class = "col-md-6 col-md-offset-3">
		<p id="demo"></p>
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
				echo $res['content'] . "<br>";
			}
			?>
		</div>
	</div>
</body>
</html>


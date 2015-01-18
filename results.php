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
		
		//debug flag
		$debug = 1;
		
		if($debug == 1)
		{
			echo "Debug mode. Assign 0 to the variable \$debug
					to toggle<br>";
		}
		
		//config
		$m = new MongoClient();
   		$db = $m -> hindu;
   		$collection = $db -> chennai;
		$query = $_POST["query"];
		
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
		//-1 for descending order
		if (isset($location))
		{
			if ($debug == 1)
			{
				echo "Location specified." . $location . "<br>";
			}
			$result = $collection -> find(
					   array('locality' => new MongoRegex("/".$location."/i"), 
					   	'$text' => array('$search' =>  "\\" . $query . "\\" )), 
					   array('$score' => array( '$meta' => "textScore")))->
					    sort(array('datePosted' => -1, '$score' => array('$meta' => 'textScore')));
			if ($debug == 1)
			{
				echo $result -> count() . " Matches found<br>";
			}
			//for city based queries		    
			if($result -> count() == 0)
			{
				if ($debug == 1)
				{
					echo "Trying by city<br>";
				}
				$result = $collection -> find(
					   array('city' => new MongoRegex("/".$location."/i"), 
					   	'$text' => array('$search' =>  "\\" . $query . "\\" )), 
					   array('$score' => array( '$meta' => "textScore")))->
					    sort(array('datePosted' => -1, '$score' => array('$meta' => 'textScore')));
			}
			
		}	   
		//if location is not specified
		else
		{		
			if ($debug == 1)
			{
				echo "No location";
			}
			$result = $collection -> find(
					   array('$text' => array('$search' => "\\" . $query . "\\" )), 
					   array('$score' => array( '$meta' => "textScore")))-> 
					   	sort(array('datePosted' => -1, '$score' => array('$meta' => 'textScore')));
		}	   
			
		//iterate over the result set
		foreach($result as $res)
		{
			
			/*to-do:
			  to be formatted for the user
			  add sorting
			  display date of posting that will also become a sorting option
			*/
			
			//more of an arbitrary value based on what i perceive from the results
			//change after seeing results for a larger dataset
			if ($res['$score'] >= 0.54)
			{
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
					echo "Contact:&nbsp;&nbsp;&nbsp;&nbsp;<a href = 'tel:" . $res['phone'] .
								 "'>" . $res['phone'] . "</a>";
				echo "</div>" . "\n";
				echo "</div>" . "\n";
				echo "</div>" . "\n";
			}
		}
		
		?>
</body>
</html>


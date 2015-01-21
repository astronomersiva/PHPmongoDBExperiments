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
		$debug = 0;
		if($debug == 1)
		{
			echo "Debug mode. Assign 0 to the variable \$debug
					to toggle<br>";
		}
		
		//config
		$m = new MongoClient();
   		$db = $m -> hindu;
   		$collection = $db -> chennai;
   		if(isset($_GET['query']))
		{
			$query = $_GET['query'];
		}
		else
		{
			$query = $_POST["query"];
		}
		
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
		//1 for ascending order
		
		
		if (isset($_GET['loc']))
		{
			$location = $_GET['loc'];
		}
		
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
					    sort(array('$score' => array('$meta' => 'textScore')));
				echo $result -> count() . "&nbsp;Matches found";
			}
			
		}	
		   
		//if location is not specified
		else
		{		
			if ($debug == 1)
			{
				echo "No location<br>";
			}
			$result = $collection -> find(
					   array('$text' => array('$search' => "\\" . $query . "\\" )), 
					   array('$score' => array( '$meta' => "textScore")))-> 
					   	sort(array('$score' => array('$meta' => 'textScore')));
			if($debug == 1)
			{
				echo $result -> count() . "&nbsp;Matches found";
			}
		}	   
		
		
		//sorting at client side
		if(isset($_GET['sort']))
		{
			$sort = $_GET['sort'];
			echo "<br>" . "Sorting by " . $sort;
			switch($sort)
			{
				case "price-low-high": $result -> sort(array('range' => 1));
									   break;
				case "price-high-low": $result -> sort(array('range' => -1));
									   break;
				case "rating": $result -> sort(array('rating' => -1));
							   break;
				case "date": $result -> sort(array('datePosted' => -1));
							   break;
			 }
		}
		
		
		if(isset($_GET['cat']))
		{
			if(isset($_GET['loc']))
			{
				$result = $collection -> find(
					   array('locality' => new MongoRegex("/".$location."/i"),
					   		  'category' => $_GET['cat'], 
					   	'$text' => array('$search' =>  "\\" . $query . "\\" )), 
					   array('$score' => array( '$meta' => "textScore")))->
					    sort(array('datePosted' => -1, '$score' => array('$meta' => 'textScore')));
			}
			else
			{
				echo $_GET['cat'];
				$result = $collection -> find(
					   array('category' => $_GET['cat'], 
					   			'$text' => array('$search' =>  "\\" . $query . "\\" )), 
					   array('$score' => array( '$meta' => "textScore")))->
					    sort(array('datePosted' => -1, '$score' => array('$meta' => 'textScore')));
			}
		}
		
		//sorting links
		echo '<div class = "row">' . "\n";
		echo '<div class = "col-md-8 col-md-offset-3">' . "\n";
		echo '<div class = "sort">' . "\n";
		echo 'Sort by <a href = "?sort=price-low-high&query=' . $query . '">Price(Low-High)</a>, 
					  <a href = "?sort=price-high-low&query=' . $query . '">Price(High-Low)</a>,
					  <a href = "?sort=rating&query=' . $query . '">Rating</a>, 
					  <a href = "?sort=date&query=' . $query . '">Date Posted</a>'; 
		echo "</div>" . "\n";
		echo "</div>" . "\n";
		echo "</div>" . "\n";	


		//start of results page
		echo '<div class = "row">' . "\n";
		
		
		//categories pane
		echo '<div class = "col-md-3 col-md-offset-1">' . "\n";
			echo "<div class = 'sidePane'>Category:" . "<br>" . "</div>" . "<br>" . "\n";
			$categoriesCollection = $db -> categories;
   			$categoriesCursor = $categoriesCollection -> find();
   			$catCount = 0;
   			foreach($categoriesCursor as $categoriesResult)
   			{		
   				if(isset($location))
   				{	
					echo '<a href = "?cat=' . $categoriesResult['name'] . '&query=' . $query .  '&loc=' . $location .'"> ' . 			$categoriesResult['name'] 
							. '</a><br>';
				}
				else
				{
					echo '<a href = "?cat=' . $categoriesResult['name'] . '&query=' . $query . '"> ' . $categoriesResult['name'] 
							. '</a><br>';
				}
   			}
		echo "</div>" . "\n";	
		
		
		//results pane
		echo '<div class = "col-md-6">' . "\n";		
		
		//iterate over the result set
		foreach($result as $res)
		{
			
			//more of an arbitrary value based on what i perceive from the results
			//change after seeing results for a larger dataset
			if ($res['$score'] >= 0.55)
			{			

				echo $res['category'];
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
					switch($res['range'])
					{
						case 1: $correctedRange = "Low";
								break;
						case 2: $correctedRange = "Medium";
								break;
						case 3: $correctedRange = "High";
								break;
					}
					echo "Price range:&nbsp;&nbsp;&nbsp;&nbsp;" . $correctedRange;
				echo "</div>" . "\n";
				echo "<div class = 'resultPhone'>";
					echo "Contact:&nbsp;&nbsp;&nbsp;&nbsp;<a href = 'tel:" . $res['phone'] .
								 "'>" . $res['phone'] . "</a>";
				echo "</div>" . "\n";
				echo "<div class = 'resultPost'>";
					echo "Posted at: &nbsp;" . date('d-M-y H:i', $res['datePosted'] -> sec) . "<br>";
				echo "</div>" . "\n";
				if ($debug == 1)
				{
					echo $res['$score'];
				}
				
			}
		}
		echo "</div>" . "\n";
		echo "</div>" . "\n";
		?>
</body>
</html>


<!DOCTYPE html>
<html>
<head>
	<title>returnTrue</title>
	<link href="css/styles.css" rel="stylesheet" media="screen">
	<link href="css/bootstrap.min.css" rel="stylesheet"	media="screen">
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


   		if(isset($_GET['query']))
		{
			$query = $_GET['query'];
		}
		else
		{
			$query = $_POST["query"];
		}

		
		//split the query for further operations
		$splitQuery = explode(" ", $query);
		
		/*custom stop words to overcome
		  certain mismatches. MongoDB
		  doesn't support this as of now.
		  So implementing this in PHP as a
		  roundabout. Typical Indian jugaad
		  -siva(26/1)
		*/
		$customStop = array("find", "somebody", "some", "one", "just");
		$query = str_replace($customStop, "", $query);
		
		/*custom language recog
		  will add more as the data set increases.
		  Reduces the load on the server as the processing is less.
		  Will be a plus point for us as the server will face a lot
		  of load when put on the Hindu(assuming we win :P) compared
		  to what it faces in localhost.		  
		*/
		
		$education = array("home", "tuitions", "tuition", "classes", "tutor", "teach");
		$matrimony = array("marry", "bride", "groom");
		$packers = array("pack", "packers", "move", "movers");
		$cook = array("cook", "cooking");
		$realEstate = array("land", "plot", "CMDA", "cmda", "home", "house");
		$dining = array("dining", "hotel", "food", "restaurants", "restaurant", "eat", "dine");
		
		if (count(array_intersect($realEstate, $splitQuery)) > 0)
		{
			$queryCatFlag = 1;
   			$_GET['cat'] = "real estate";
		}
		else if (count(array_intersect($education, $splitQuery)) > 1)
		{
			$queryCatFlag = 1;
   			$_GET['cat'] = "education";
		}
		else if (count(array_intersect($matrimony, $splitQuery)) > 0)
		{
			$queryCatFlag = 1;
   			$_GET['cat'] = "matrimony";
		}
		else if (count(array_intersect($cook, $splitQuery)) > 0)
		{
			$queryCatFlag = 1;
   			$_GET['cat'] = "services";
		}
		else if (count(array_intersect($packers, $splitQuery)) > 0)
		{
			$queryCatFlag = 1;
   			$_GET['cat'] = "packers and movers";
		}
		else if (count(array_intersect($dining, $splitQuery)) > 0)
		{
			$queryCatFlag = 1;
   			$_GET['cat'] = "dining";
		}
		
		
		
		
		/*look for category in the query.
		  a little more exhaustive technique.
		  The final filter. Just in case 
		  anything had escaped the previous check.
		  Which I am sure, many would.
		*/
		
		if (in_array('packers', $splitQuery) !== false)
		{
			array_push($splitQuery, "packers and movers");
		}
		if (in_array('movers', $splitQuery) !== false)
		{
			array_push($splitQuery, "packers and movers");
		}
		if (in_array('real', $splitQuery) !== false)
		{
			array_push($splitQuery, "real estate");
		}
   		$categoriesCollection = $db -> categories;
   		$categoriesCursor = $categoriesCollection -> find(array(), array("_id" => 0, "name" => 1));
		$catArray = array();
		foreach($categoriesCursor as $cats)
		{
			array_push($catArray, $cats["name"]);
		}
   		$match = array_intersect($catArray, $splitQuery);
   		if (count($match) > 0)
   		{
   			$queryCatFlag = 1;
   			$match = $match[0];
   		}
   		
   		
   				
		//for location based queries 
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
		
		/*perform  full text search and sort based on score
		 '$text' => array('$search' => "\\" . $query . "\\")
		  add the above line to perform full phrase.
		  unable to decide if i shud go for it now
		  a larger dataset would make that clear
		  if location is set
		  -1 for descending order
		  1 for ascending order
		*/
		
		if (isset($location))
		{
			if ($debug == 1)
			{
				echo "Location specified." . $location . "<br>";
			}
			
			if(isset($_GET['cat']))
			{
				$result = $collection -> find(
					   array('category' => $_GET['cat'], 'locality' => new MongoRegex("/".$location."/i"), 
					   	'$text' => array('$search' =>  "\\" . $query . "\\" )), 
					   array('$score' => array( '$meta' => "textScore")))->
					    sort(array('datePosted' => -1, '$score' => array('$meta' => 'textScore')));
			}
			else
			{
				if(isset($queryCatFlag))
				{
					$result = $collection -> find(
					   array('category' => $match, 'locality' => new MongoRegex("/".$location."/i"), 
					   	'$text' => array('$search' =>  "\\" . $query . "\\" )), 
					   array('$score' => array( '$meta' => "textScore")))->
					    sort(array('datePosted' => -1, '$score' => array('$meta' => 'textScore')));
				}
				else
				{
					$result = $collection -> find(
					   array('locality' => new MongoRegex("/".$location."/i"), 
					   	'$text' => array('$search' =>  "\\" . $query . "\\" )), 
					   array('$score' => array( '$meta' => "textScore")))->
					    sort(array('datePosted' => -1, '$score' => array('$meta' => 'textScore')));
				}
			}
			
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
				if(isset($_GET['cat']))
				{
					$result = $collection -> find(
					   array('category' => $_GET['cat'], 'city' => new MongoRegex("/".$location."/i"), 
					   	'$text' => array('$search' =>  "\\" . $query . "\\" )), 
					   array('$score' => array( '$meta' => "textScore")))->
					    sort(array('$score' => array('$meta' => 'textScore')));
					if ($debug == 1)
					{
						echo $result -> count() . "&nbsp;Matches found";
					}
				}
				else
				{
					if(isset($queryCatFlag))
					{	
						$result = $collection -> find(
					   		array('category' => $match, 'city' => new MongoRegex("/".$location."/i"), 
					   		'$text' => array('$search' =>  "\\" . $query . "\\" )), 
					   		array('$score' => array( '$meta' => "textScore")))->
					    		sort(array('$score' => array('$meta' => 'textScore')));
					}
					else
					{
						$result = $collection -> find(
					   		array('city' => new MongoRegex("/".$location."/i"), 
					   		'$text' => array('$search' =>  "\\" . $query . "\\" )), 
					   		array('$score' => array( '$meta' => "textScore")))->
					    		sort(array('$score' => array('$meta' => 'textScore')));
					}	
					if ($debug == 1)
					{
						echo $result -> count() . "&nbsp;Matches found";
					}
				}
			}
			
		}	
		   
		//if location is not specified
		else
		{		
			if ($debug == 1)
			{
				echo "No location<br>";
			}
			if(isset($_GET['cat']))
			{
				$result = $collection -> find(
					   array('category' => $_GET['cat'], '$text' => array('$search' => "\\" . $query . "\\" )), 
					   array('$score' => array( '$meta' => "textScore")))-> 
					   	sort(array('$score' => array('$meta' => 'textScore')));
			}
			else
			{
				if(isset($queryCatFlag))
				{
					$result = $collection -> find(
					   array('category' => $match, '$text' => array('$search' => "\\" . $query . "\\" )), 
					   array('$score' => array( '$meta' => "textScore")))-> 
					   	sort(array('$score' => array('$meta' => 'textScore')));
				}
				else
				{
					$result = $collection -> find(
					   array('$text' => array('$search' => "\\" . $query . "\\" )), 
					   array('$score' => array( '$meta' => "textScore")))-> 
					   	sort(array('$score' => array('$meta' => 'textScore')));
				}
			}
			if($debug == 1)
			{
				echo $result -> count() . "&nbsp;Matches found";
			}
		}	   
		
		
		//sorting at client side
		if(isset($_GET['sort']))
		{
			$sort = $_GET['sort'];
			if($debug == 1)
			{
				echo "<br>" . "Sorting by " . $sort;
			}
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
		
		//sorting links
		echo '<div class = "row">' . "\n";
		echo '<div class = "col-md-8 col-md-offset-3">' . "\n";
		echo '<div class = "sort">' . "\n";
		if(isset($_GET['cat']))
		{
			if(isset($location))
			{
				echo 'Sort by <a href = "?sort=price-low-high&query=' . $query . '&cat=' . $_GET['cat'] . '&loc=' . $location. '">Price(Low-High)</a>, 
					  	<a href = "?sort=price-high-low&query=' . $query . '&cat=' . $_GET['cat'] . '&loc=' . $location. '">Price(High-Low)</a>,
					  	<a href = "?sort=rating&query=' . $query . '&cat=' . $_GET['cat'] . '&loc=' . $location. '">Rating</a>, 
					  	<a href = "?sort=date&query=' . $query . '&cat=' . $_GET['cat'] . '&loc=' . $location. '">Date Posted</a>';
			
			}
			else
			{
				echo 'Sort by <a href = "?sort=price-low-high&query=' . $query . '&cat=' . $_GET['cat'] . '">Price(Low-High)</a>, 
					  	<a href = "?sort=price-high-low&query=' . $query . '&cat=' . $_GET['cat'] . '">Price(High-Low)</a>,
					  	<a href = "?sort=rating&query=' . $query . '&cat=' . $_GET['cat'] . '">Rating</a>, 
					  	<a href = "?sort=date&query=' . $query . '&cat=' . $_GET['cat'] . '">Date Posted</a>';
			}
		}
		else 
		{
			if(isset($location))
			{
				echo 'Sort by <a href = "?sort=price-low-high&query=' . $query . '&loc=' . $location. '">Price(Low-High)</a>, 
					  	<a href = "?sort=price-high-low&query=' . $query . '&loc=' . $location. '">Price(High-Low)</a>,
					  	<a href = "?sort=rating&query=' . $query . '&loc=' . $location. '">Rating</a>, 
					  	<a href = "?sort=date&query=' . $query  . '&loc=' . $location. '">Date Posted</a>'; 
			}
			else
			{
				echo 'Sort by <a href = "?sort=price-low-high&query=' . $query . '">Price(Low-High)</a>, 
					  	<a href = "?sort=price-high-low&query=' . $query . '">Price(High-Low)</a>,
					  	<a href = "?sort=rating&query=' . $query . '">Rating</a>, 
					  	<a href = "?sort=date&query=' . $query . '">Date Posted</a>';
			}
		}
		echo "</div>" . "\n";
		echo "</div>" . "\n";
		echo "</div>" . "\n";	
		
		
		//search box
		echo '<div class = "row search">' . "\n";
		echo '<div class = "col-md-8 col-md-offset-3">' . "\n";
		echo '
		<form method = "post" action = "results.php">
  				<div class="form-group">
    				<input type="text" name = "query" class="form-control" id="query"  placeholder="What are you looking for?">
  				</div>
			</form>';
		echo "</div>" . "\n";
		echo "</div>" . "\n";
		
		
		
		//side pane for categories
		echo '<div class = "row">' . "\n";
		echo '<div class = "col-md-2 col-md-offset-1 sidePane">' . "\n";
		echo "<div class = 'sidePane'><span class = 'cat'>Category:" . "</span><br>" . "</div>" . "<br>" . "\n";
			$categoriesCollection = $db -> categories;
   			$categoriesCursor = $categoriesCollection -> find();
   			foreach($categoriesCursor as $categoriesResult)
   			{	
   				echo '<a href = "?cat=' . $categoriesResult['name'] . '&query=' . $query . '"> ' . $categoriesResult['name'] 
							. '</a><br>';
   			}
   		echo "</div>" . "\n";		
		echo '<div class = "col-md-8">' . "\n";
		
		//iterate over the result set
		if($result -> count() == 0)
		{
			echo "Exact matches not found.";
			if(isset($queryCatFlag))
			{
				if($debug == 1)
				{
					echo "\ngoing for category fetch\n";
					echo $_GET['cat'];
				}
				if (isset($location))
				{
					if ($debug == 1)
					{
						echo "Location specified." . $location . "<br>";
					}
					$result = $collection -> find(
					 	  array('category' => $_GET['cat'], 'locality' => new MongoRegex("/".$location."/i")))->
					  	  sort(array('datePosted' => -1));
					if ($debug == 1)
					{
						echo $result -> count() . " Matches found<br>";
					}
			
					//for city based queries		    
					if($result -> count() == 0)
					{
						if ($debug == 1)
						{
							echo "City specified." . $location . "<br>";
						}
						$result = $collection -> find(
					 	  	array('category' => $_GET['cat'], 'city' => new MongoRegex("/".$location."/i")))->
					  	  	sort(array('datePosted' => -1));
						if ($debug == 1)
						{
							echo $result -> count() . "&nbsp;Matches found";
						}
					}
				}
			}
			if($result -> count() == 0)
			{
				if (isset($_GET['cat']))
				{
					echo "<br>Displaying ads in this category.";
					$result = $collection -> find(
					   	array('category' => $_GET['cat']));
				}
				else
				{
					//i dont think anything is needed here. should wait n see.
				}
			}
		}	
		
		foreach($result as $res)
		{
			
			/*more of an arbitrary value based on what I perceive from the results
			  change after seeing results for a larger dataset
			  also, after adding our custom filters,
			  this threshold can be relaxed a bit to display
			  little less relevant but matching results.
			*/
			if(!isset($res['$score']))
			{
				$res['$score'] = 0.55;
			}
			
			if ($res['$score'] >= 0.55)
			{
					
				//results pane.
				echo "<div class = 'row'>";
				echo "<div class = 'resultTitle'>";
					echo ucwords($res['name']);
				echo "</div>" . "\n";
				echo "<div class = 'resultContent'>";
					echo ucwords($res['content']);
				echo "</div>" . "\n";
				echo "<div class = 'resultLocality'>";
					echo $res['locality'];
				echo "</div>" . "\n";
				echo "<div class = 'resultRating'>";
				echo "<span style = 'color: #333'>Rating:</span>&nbsp;";
					while($res['rating'] > 0)
					{
						echo "&#x2605;";
						$res['rating'] -= 1;
					}
				echo "</div>" . "\n";
				
				/*Removing the price range category.
				  Don't feel that it is an essential
				  thing to display - Siva
				  @loki, @viki..feel free to uncomment this.
				  Committing on Github.
				*/
				
				/*
				echo "<div class = 'resultRange'>";
					switch($res['range'])
					{
						case 1: $correctedRange = "Low";
								break;
						case 2: $correctedRange = "Medium";
								break;
						case 3: $correctedRange = "High";
								break;
						default: $correctedRange = "NA";
					}
					echo "Price range:&nbsp;&nbsp;&nbsp;&nbsp;" . $correctedRange;
				echo "</div>" . "\n";
				*/
				
				echo "<div class = 'resultPhone'>";
					echo "Contact:&nbsp;&nbsp;&nbsp;&nbsp;<a href = 'tel:" . $res['phone'] .
								 "'>" . $res['phone'] . "</a>";
				echo "</div>" . "\n";
				echo "<div class = 'resultPost'>";
					echo "Posted on: &nbsp;" . date('d-M-y H:i', $res['datePosted'] -> sec) . "<br>";
				echo "</div>" . "\n";
				if ($debug == 1)
				{
					echo $res['$score'];
				}
				echo "</div>" . "\n";
			}
		}
		echo "</div>" . "\n";
		echo "</div>" . "\n";
		?>
</body>
</html>


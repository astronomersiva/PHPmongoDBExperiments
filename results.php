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
			$m = new MongoClient();
   			$db = $m -> hindu;
   			$collection = $db -> chennai;
			$query = $_POST["query"];
			$params = array('$text' => array('$search' => $query));
			$result = $collection -> find($params);
			foreach($result as $res)
			{
				echo $res['content'] . "<br>";
			}
		/*
			$userInput = $_POST["query"];
			$splitQuery = explode(" ", $userInput);
			$m = new MongoClient();
			$db = $m -> hindu;
			$collection = $db -> categories;
			foreach ($splitQuery as $word)
			{
				$condition = array("sub" => $word);
				$cursor = $collection -> find($condition);
				if( $cursor -> count() > 0 )
				{
					foreach( $cursor as $result )
					{
						echo $result["name"] . '&nbsp;';
					}
					echo "<br>";
				}	
			}
		?>
			<?php

				// connect
				$m = new MongoClient();

				// select a database
				$db = $m->hindu;

				// select a collection (analogous to a relational database's table)
				$collection = $db->chennai;
				$catCollection = $db->categories;
				$query = $_POST["query"];
				$splitQ = explode(" ", $query);
				foreach ($splitQ as $word)
				{
					$condition = array( "keywords" => $word );
					$cursor = $catCollection -> find($condition);
					foreach ($cursor as $document)
					{
						$type = $document["name"];
						$cond2 = array( "category" => $type);
						$cursor1 = $collection->find( $cond2 );
						
						foreach ($cursor1 as $document1)
						{
	    					echo $document1["name"];
						}
					}
				} 

*/
			?>
		</div>
	</div>
</body>
</html>


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
			<?php

	// connect
	$m = new MongoClient();

	// select a database
	$db = $m->hindu;

	// select a collection (analogous to a relational database's table)
	$collection = $db->chennai;

	// add a record
	//$document = array( "title" => "Calvin and Hobbes", "author" => "Bill Watterson" );
	//$collection->insert($document);

	// add another record, with a different "shape"
	//$document = array( "title" => "XKCD", "online" => true );
	//$collection->insert($document);
	$name=$_POST["query"];
	// find everything in the collection
	$conditions = array( "content" => new MongoRegex("/$name/"));
	$cursor = $collection->find($conditions);
	// iterate through the results
	foreach ($cursor as $document) {
	    echo $document["content"] . "\n";
	}

?>

		</div>
	</div>
</body>
</html>

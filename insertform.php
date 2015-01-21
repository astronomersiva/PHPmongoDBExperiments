
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
			<form method = "post" action = "insert.php">
  				<div class="form-group">
    				<input type="text" name = "name" class="form-control" placeholder="Name">
    				<input type="text" name = "content" class="form-control" placeholder="Content">
    				<input type="text" name = "phone" class="form-control" placeholder="Contact Number">
    				<input type="text" name = "city" class="form-control" placeholder="City">
    				<input type="text" name = "locality" class="form-control" placeholder="Locality">
    				<input type="text" name = "range" class="form-control" placeholder="Price range(Low/Medium/High)">
    				<input type="text" name = "rating" class="form-control" placeholder="Rating">
    				<select name = "category">
    					<option value="">Choose the category</option>
    					<?php
   							$m = new MongoClient();
   							$db = $m -> hindu;
   							$collection = $db -> categories;
   							$cursor = $collection -> find();
   							foreach($cursor as $result)
   							{	
   								echo "<option value='$result[name]'>$result[name]</option>";
   							}
    					?>
					</select>
					<button class="btn btn-success btn-block" type="submit">
                                Insert
                    </button>
  				</div>
			</form>
		</div>
	</div>
</body>
</html>

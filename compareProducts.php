<html>
<head>
	<title>Compare Phones</title>
</head>
<body>
	<?php
	$categoryErr = ""; //storing error for the drop-down menu
	$minErr = ""; //storing error for the minimum value
	$maxErr = ""; //storing error for the maximum value
	$dataValid = true;


 	//this function takes the any file from the pathname identified with extention ".csv", reads it by its parameters like model, os, price ..
	//it takes parameters divided by "," splits it and loads data in the datebase tables
	function loadFile($link ,$fileName) 
	{
 		$loadQuery = "LOAD DATA LOCAL INFILE '/home/int322_161a01/apache1/cgi-bin/assign/" . $fileName . ".csv' 
					  INTO TABLE phone 
					  FIELDS TERMINATED BY ','  
					  ENCLOSED BY '\"'
					  LINES TERMINATED BY '\n'
					  IGNORE 1 LINES;";

		mysqli_query($link, $loadQuery) or die('query failed'. mysqli_error($link)); //execute the query and display error if something goes wrong 
	}

	if ($_POST) 
	{
		if ($_POST['category'] == "") { //check if a category is chosen
			$categoryErr = "<span style=\"color:red\">Error - you must choose a category!</span>";//display error if the category is not selected
			$dataValid = false; //assigns valid to false which means data did not pass validation
		}

		if ($_POST['min'] == "") { //check if a minimum value is entered
			$minErr = "<span style=\"color:red\">Error - you must enter a minimum price!</span>"; //display error if min value is missing
			$dataValid = false; //assigns valid to false which means data did not pass validation
		} 

		elseif (!preg_match("/^ *\d*\.?\d* *$/", $_POST['min'])) { //check if a min value is number
			$minErr = "<span style=\"color:red\">Error - you can only enter digits here!</span>"; //dispalys error if min value is not a number
			$dataValid = false; //assigns valid to false which means data did not pass validation
        }
		
		elseif (!preg_match("/^[0-9]*\.[0-9]{2}$/", $_POST['min'])) { //check if min value is in correct format and has two decimal points
			$minErr = "<span style=\"color:red\">Error - you should enter two decimals here!</span>"; //displays error if min value has no decimals
			$dataValid = false;  //assigns valid to false which means data did not pass validation
		}

		if ($_POST['max'] == "") { //check if a maximum value is entered
			$maxErr = "<span style=\"color:red\">Error - you must enter a maximum price!</span>"; //dispalys error if max value is missing
			$dataValid = false;//assigns valid to false which means data did not pass validation
		} 
		
        elseif (!preg_match("/^ *\d*\.?\d* *$/", $_POST['max'])) { //check if a maximum value is number
			$maxErr = "<span style=\"color:red\">Error - you can only enter digits here!</span>"; //dispalys error if max value is not a number
			$dataValid = false; //assigns valid to false which means data did not pass validation
        }

	    elseif (!preg_match("/^[0-9]*\.[0-9]{2}$/", $_POST['max'])) { //check if the max is in correct format and has two decimals
			$maxErr = "<span style=\"color:red\">Error - you should enter two decimals here!</span>"; // dispalys error id max value has no decimals
			$dataValid = false;//assigns valid to false which means data did not pass validation
		} 

		elseif ($_POST['min'] >= $_POST['max']) { //check if min is not greater than max value
			$maxErr = "<span style=\"color:red\">Error - maximum price must be greater than minimum price!</span>"; // display error if min is bigger than the max value
			$dataValid = false; //assigns valid to false which means data did not pass validation
		}
	}

	if ($_POST && $dataValid) //if posted and if the date passed the validation acccept the input and populate the database
	{
		//store the posted and valid input to the local variables
		$category = $_POST['category'];
		$min = $_POST['min'];
		$max = $_POST['max'];

		//mySQLi connection info
		$lines = file('/home/int322_161a01/secret/topsecret'); //takes the file from the home directory and reads the lines and stores the values in the variables
		$dbserver = trim($lines[0]);
		$uid = trim($lines[1]);
		$pw = trim($lines[2]);
		$dbname = trim($lines[3]);

		//Connect to the mysql server and get back our link_identifier
 		$link = mysqli_connect($dbserver, $uid, $pw, $dbname) or die('Could not connect: ' . mysqli_error($link));

		//drop table every time we access database so that there is no "table exist" error
		$drop = "Drop table phone";
		$dropTable = mysqli_query($link, $drop) or die('query failed'. mysqli_error($link));

		//create one table that would store all the info from the files
		$createTable = "CREATE TABLE phone(
				id int zerofill not null primary key auto_increment,
				itemName varchar(40) not null,
				model varchar(60) not null,
				os varchar(40) not null,
				price decimal(10,2) not null)";
       
		$table = mysqli_query($link, $createTable) or die('query failed'. mysqli_error($link)); //execute the query

 		//load CSV files and populate the table
 		loadFile($link, $category); //call loadCSV function and load the file depending on the category selected

 		//run the SELECT query and select only information that is in between ranges identified by user
		$selectQuery = "SELECT * FROM phone WHERE price BETWEEN " . $min . " AND " . $max . " ORDER BY price;";

		 //stores the selected information into the result variable
		$result = mysqli_query($link, $selectQuery) or die('query failed'. mysqli_error($link));
		?>

		<h3>Product Comparision</h3>
		<table border="1">  <!--display the table with all the information from the files-->
				<tr>
					<th>Id</th><th>Phone Name</th><th>Model</th><th>OS</th><th>Price</th><!--print the column headers-->
				</tr>
			<?php
					//display the date
					$date = 'SELECT CURDATE()'; //default curdate fuction call
					$query = mysqli_query($link, $date) or die('query failed'. mysqli_error($link)); //date query definition
					
					//store result
					$date = mysqli_fetch_assoc($query); //execute the date query
				    	echo('<p>' . 'Date: ' . $date['CURDATE()'] . '</p>');  //print the date above the table


				//loop through the result and display the information stores in the correct table and row
 				while($row = mysqli_fetch_assoc($result)) 
				{
			?>
					<tr>
   						<td><?php print $row['id']; ?></td> <!--display id row-->
                        <td><?php print $row['itemName']; ?></td><!--display item name row-->
						<td><?php print $row['model']; ?></td><!--display model row-->
						<td><?php print $row['os']; ?></td><!--display os row-->
						<td><?php print $row['price']; ?></td><!--display price row-->		
					</tr>
			<?php
 				}
			?>
		</table>
		
		<?php
		// Free result
		mysqli_free_result($result);
		//Close the MySQL database
 		mysqli_close($link);
	} 

	//window with the input fields
	else 
	{
		?>
		<h3>Product Comparision</h3>
		<form method="post" action="">
		 	Category:
			<select name="category"><!--display the category field with options to choose from-->
	     			<option name="choose" value="">--Please choose--</option> 
	     			<option name="android" value="android" <?php if ($_POST['category'] == "android") echo "SELECTED"; ?>>Android</option>
	     			<option name="Iphone" value="Iphone" <?php if ($_POST['category'] == "Iphone") echo "SELECTED"; ?>>iPhone</option>
	     			<option name="Microsoft" value="Microsoft" <?php if ($_POST['category'] == "Microsoft") echo "SELECTED"; ?>>Windows Phone</option>
	     			<option name="BlackBerry" vaulue="BlackBerry" <?php if ($_POST['category'] == "BlackBerry") echo "SELECTED"; ?>>BlackBerry</option>
		 	</select>
			<?php echo $categoryErr;?> <!--display the error message if one occurs-->
			<br/>
			
			<!--input field for min price-->
			Minimum Price:
			<input type="text" name="min" value="<?php if (isset($_POST['min'])) echo $_POST['min']; ?>">
			<?php echo $minErr;?><!--display the error message if one occurs-->
			<br/>

			<!--input field for max price-->
			Maximum Price:
			<input type="text" name="max" value="<?php if (isset($_POST['max'])) echo $_POST['max']; ?>">
			<?php echo $maxErr;?><!--display the error message if one occurs-->
			<br/>
			
			<!--submit the form-->
			<input type="submit" name="submit">
		</form>
		<?php
	}
	?>

</body>
</html> <!--close html tags-->

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">      
    <title>Video Inventory</title>
    <link rel="stylesheet" type="text/css" href="stylesheet.css">
</head>
  
<body>
    <div id= "title">
        <h1>Video Store Database</h1>
    </div>  
    <div id="addVideo">
        <H2>Add Video:</H2>
        <form action= "index.php" method= 'POST'>
            Video Title:<br>
            <input type= 'text' name= 'name'><br>
            Video Length:<br>
            <input type= 'number' name= 'length' min='0'><br>
            Video Genre:<br>
            <input type= 'text' name='category'> <br><br>
            <input type= 'hidden' name='type' value='add'>
            <input type= 'submit' value ='Add Video' />
        </form><br>
            <form action="index.php" method='GET'>
            <input type='hidden' name='clear' value='all'>
            <input type='submit' value="Clear List">
        </form>
    </div>

<?php
$errorFree = true;
include 'databaseInfo.php';
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    if($_POST['type'] == 'add'){
        $name = $_POST['name'];
        $category = $_POST['category'];
        $length = $_POST['length'];
        if(!(strlen($name) > 0)){
            echo "Error: Video Name Empty.\n";
            $errorFree = false;
        }
    }
}
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    if($_POST['type'] == 'remove'){
        
        $deleteName = $_POST['ToRemove'];
        $conn=mysqli_connect("oniddb.cws.oregonstate.edu","shena-db",$myPassword,"shena-db");
   
        if (mysqli_connect_errno($con)){
                echo "Failed to connect to MySQL: " . mysqli_connect_error();
        }    
        
        $sql = "DELETE FROM video_inventory WHERE name='$deleteName'";
        if ($conn->query($sql) === TRUE) {
            echo "Video Removed";
        } else {
            echo "Error Removing Video: " . $conn->error;
        }
        $conn->close(); 
    }
}
$mysqli = new mysqli("oniddb.cws.oregonstate.edu", "shena-db", $myPassword, "shena-db");
if(!$mysqli || $mysqli->connect_errno){
    echo "Connection error ".$mysqli->connect_errno . "".$mysqli->connect_error;
}
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    if($_POST['type'] == 'edit'){
        $editName = $_POST['ToEdit'];
        $rentalStatus = $_POST['stockStatus'];
        if($rentalStatus == 'Available')
            $isRented = 1;
        else
            $isRented = 0;
        
        $lineInput = "UPDATE video_inventory SET rented='$isRented' WHERE name='$editName'";
    if ($mysqli->query($lineInput) === TRUE) {
            echo "    ";
        } 
        else {
            echo "Error Updating: " . $mysqli->error;
        }  
    }
}
if($errorFree == true && $_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['type'] == 'add'){
    
    if((strlen($category) > 0) && ($_POST['length'] == NULL)){
        
        if (!($stmt = $mysqli->prepare("INSERT INTO video_inventory(name, category) VALUES (?, ?)"))) {
            echo "Error Preparing: (" . $mysqli->errno . ") " . $mysqli->error;
        }   
        if (!$stmt->bind_param("ss", $name, $category)) {
        echo "Error Binding: (" . $stmt->errno . ") " . $stmt->error;
        }
    }
    
    else if((strlen($category) > 0) && ($_POST['length'] != NULL)){
        
        if (!($stmt = $mysqli->prepare("INSERT INTO video_inventory(name, length, category) VALUES (?, ?, ?)"))) {
            echo "Error Preparing: (" . $mysqli->errno . ") " . $mysqli->error;
        }   
        if (!$stmt->bind_param("sis", $name, $length, $category)) {
        echo "Error Binding: (" . $stmt->errno . ") " . $stmt->error;
        }
        if (!$stmt->execute()) {
        echo "Error Executing: (" . $stmt->errno . ") " . $stmt->error;
        }
    }
    else if((strlen($category) == 0) && ($_POST['length'] == NULL)){
        if (!($stmt = $mysqli->prepare("INSERT INTO video_inventory(name) VALUES (?)"))) {
            echo "Error Preparing: (" . $mysqli->errno . ") " . $mysqli->error;
        }   
        if (!$stmt->bind_param("s", $name)) {
        echo "Error Binding: (" . $stmt->errno . ") " . $stmt->error;
        }
        if (!$stmt->execute()) {
        echo "Error Executing: (" . $stmt->errno . ") " . $stmt->error;
        }
    }
    else if((strlen($category) == 0) && ($_POST['length'] != NULL)){
        if (!($stmt = $mysqli->prepare("INSERT INTO video_inventory(name, length) VALUES (?, ?)"))) {
            echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
        }   
        if (!$stmt->bind_param("si", $name, $length)) {
        echo "Error Binding: (" . $stmt->errno . ") " . $stmt->error;
        }
        if (!$stmt->execute()) {
        echo "Error Executing: (" . $stmt->errno . ") " . $stmt->error;
        }
    }
}

if($_SERVER['REQUEST_METHOD'] == 'GET'){
    if($_GET['clear'] == 'all'){
        $connDel=mysqli_connect("oniddb.cws.oregonstate.edu","shena-db",$myPassword,"shena-db");
        if (mysqli_connect_errno($connDel))
        {
            echo "Error connection to database: " . mysqli_connect_error();
        }    
        $sql = "TRUNCATE TABLE video_inventory";
        mysqli_query($connDel, $sql) or die(mysqli_error());
    }
}
echo "  <div id='inventoryDiv'>
        <table><caption><h2>Video Inventory</h2></caption>
            <tr><th>Name<th>Category<th>Length<th>Check Out Video</th>";
    
    $categories = 'SELECT DISTINCT category FROM video_inventory';
    if($row = $mysqli->query($categories)){
        echo "<form action= 'index.php' method='POST'>";
        echo "<input type= 'hidden' name='type' value='filter'>";
        echo "<select name='var' onchange='this.form.submit()'>";
        $all = 'All Videos';
        $choice = 'Filter Results';
        echo "<option value='".$choice."'>".$choice."</option>";
        echo "<option value='".$all."'>".$all."</option>";
        while($distinctCategory = $row->fetch_array(MYSQL_NUM)){
            if(strlen($distinctCategory[0]) > 0)
            echo "<option value='".$distinctCategory[0]."'>".$distinctCategory[0]."</option>";
        }
        echo'</select></form>';
    }
    $selection = "SELECT name, category, length, rented FROM video_inventory";
    $selectGenre = false;
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        if($_POST['type'] == 'filter' || $_POST['type'] == 'edit'){
            if($_POST['var'] == "All Videos"){
                $selection = "SELECT name, category, length, rented FROM video_inventory";
            }   
            else{
                $choice = $_POST['var'];
                $selection = "SELECT name, category, length, rented FROM video_inventory WHERE category='$choice'";
                $selectGenre = true;
            }
        }
    }
    $queryResults = $mysqli->query($selection);
    while($row = $queryResults->fetch_row()){
        if($row[3] == 0)
            $isAvailable = "Available";
        else
            $isAvailable = "Checked Out";
        echo "<tr><td>$row[0]<td>$row[1]<td>$row[2]";
        $editName = $row[0];
        echo "<td><form action= 'index.php' method='POST'><input type= 'hidden' name='type' value='edit'>";
        if($selectGenre == false)
            $setGenre = "All Videos";
        else
            $setGenre = $_POST['var'];
        echo "<input type='hidden' name='var' value='$setGenre'>";
        echo "<input type='hidden' name='ToEdit' value='$editName'><input type='hidden' name='stockStatus' value='$isAvailable'><input type='submit' value='$isAvailable'>
            </form>";
        $removeName = $row[0];
        echo "<td><form action= 'index.php' method='POST'><input type= 'hidden' name='type' value='remove'>
            <input type='hidden' name='ToRemove' value='$removeName'><input type='submit' value='Delete'>
            </form></td></tr>";
    }   
?>
        </table>
    
    </div>

    <div id= "footer">
        Programmed by Andrew Shen (shena@engr.oregonstate.edu)<br>
        CS290 - Web Development - Assignment 4-2<br>
        Spring 2015
    </div>
</body>

<html>
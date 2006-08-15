<?php
require_once('db_connect.php');

$search = $_POST['user_name'];
if(strlen($search) < "3"){ return;} // Must Prevent Infinite Loops!!!!1 :-)
echo "<ul>";
$sql = "SELECT name FROM users WHERE name LIKE '%$search%'";
$result = mysql_query($sql);
while(list($name) = mysql_fetch_row($result)) { 
	echo "<li>$name</li>";
}

echo "</ul>";
?>
</ul>


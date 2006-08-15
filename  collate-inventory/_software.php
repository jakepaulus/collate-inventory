<?php
require_once('db_connect.php');

$search = $_POST['search_software'];
if(strlen($search) < "3"){ return;} // Must Prevent Infinite Loops!!!!1 :-)
echo "<ul>";
$sql = "SELECT title FROM softwares WHERE title LIKE '%$search%'";
$result = mysql_query($sql);
while(list($title) = mysql_fetch_row($result)) { 
	echo "<li>$title</li>";
}

echo "</ul>";
?>
</ul>


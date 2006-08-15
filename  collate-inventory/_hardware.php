<?php
require_once('db_connect.php');

$search = $_POST['search_asset'];
if(strlen($search) < "3"){ return;} // Must Prevent Infinite Loops!!!!1 :-)
echo "<ul>";
$sql = "SELECT asset FROM hardware WHERE asset LIKE '%$search%'";
$result = mysql_query($sql);
while(list($asset) = mysql_fetch_row($result)) { 
	echo "<li>$asset</li>";
}

echo "</ul>";
?>
</ul>


<?php
/*
 * This script takes a POSTed value from hardware.php's add_hardare function (the new hardware form) and returns an unordered list of distict hardware types.
 * This functionality is enabled by the script.aculo.us ajax library.
 */

require_once('include/common.php');
$search = clean($_POST['category']);

if(strlen($search) < "3"){  // Prevent infinite loops and other bad stuff.
  return;
}
  
echo "<ul>";

$sql = "SELECT DISTINCT category FROM hardwares WHERE category LIKE '%$search%' LIMIT 0, 5";
$result = mysql_query($sql);
  
while(list($username) = mysql_fetch_row($result)) { 
  echo "<li>$username</li>";
}
echo "</ul>";
?>


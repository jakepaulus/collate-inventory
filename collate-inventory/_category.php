<?php
  /* This script takes a posted value from nav.php (included in every page) and returns 
    * an unordered list of search results to be updated into a div.
    */

  require_once('include/db_connect.php');

  $search = $_POST['category'];

  if(strlen($search) < "3"){ return;} // Prevent infinite loops and other bad stuff.
  
  echo "<ul>";
  
  $sql = "SELECT DISTINCT category FROM hardwares WHERE category LIKE '%$search%' LIMIT 0, 5";
  
  $result = mysql_query($sql);
  
  while(list($username) = mysql_fetch_row($result)) { 
    echo "<li>$username</li>";
  }

  echo "</ul>";

?>

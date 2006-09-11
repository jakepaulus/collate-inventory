<?php
  /* This script takes a posted value from nav.php (included in every page) and returns 
    * an unordered list of search results to be updated into a div.
    */

  require_once('include/db_connect.php');

  $search = $_POST['user_name'];

  if(strlen($search) < "3"){ return;} // Prevent infinite loops and other bad stuff.
  
  echo "<ul>";
  
  $sql = "SELECT firstname, lastname FROM users WHERE firstname LIKE '%$search%' OR lastname LIKE '%$search%'";
  
  $result = mysql_query($sql);
  
  while(list($firstname, $lastname) = mysql_fetch_row($result)) { 
    echo "<li>$firstname $lastname</li>";
  }

  echo "</ul>";

?>


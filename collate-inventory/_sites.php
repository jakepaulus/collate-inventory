<?php
  /* This script takes a posted value from nav.php (included in every page) and returns 
    * an unordered list of search results to be updated into a div.
    */

  require_once('include/db_connect.php');

  $search = $_POST['sitesearch'];

  if(strlen($search) < "3"){ exit(); } // Prevent infinite loops and other bad stuff.
  
  echo "<ul>";
  
  $sql = "select name from sites where name LIKE '%$search%' LIMIT 0, 5 ";
  
  $result = mysql_query($sql);
  
  while(list($site) = mysql_fetch_row($result)) { 
    echo "<li>$site</li>";
  }

  echo "</ul>";

?>


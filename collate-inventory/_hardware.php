<?php
  /* This script takes a posted value from nav.php (included in every page) and returns 
    * an unordered list of search results to be updated into a div.
    */
    
  require_once('include/db_connect.php');
  
  $search = $_POST['search_asset'];
  
  if(strlen($search) < "3"){ return;} // Prevent infinite loops and other bad stuff.
  
  echo "<ul>";
  
  $sql = "SELECT asset FROM hardware WHERE asset LIKE '%$search%'";

  $result = mysql_query($sql);
  
  while(list($asset) = mysql_fetch_row($result)) { // Probably shouldn't use fetch_row
    echo "<li>$asset</li>";
  }
  
  echo "</ul>";

?>

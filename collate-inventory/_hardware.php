<?php
  /* This script takes a posted value from nav.php (included in every page) and returns 
    * an unordered list of search results to be updated into a div.
    */
    
  require_once('./include/db_connect.php');
  
  $search = $_POST['search'];

  if(strlen($search) < "3"){ return;} // Prevent infinite loops and other bad stuff.
  
  echo "<ul>";
  
  $sql = "SELECT asset, serial FROM hardwares WHERE asset LIKE '%$search%' OR serial LIKE '%$search%'";

  $result = mysql_query($sql);
  
  while(list($asset, $serial) = mysql_fetch_row($result)) {
    if(stristr($asset, $search)) {  
      echo "<li>$asset</li>";
    }
    elseif(stristr($serial, $search)) {
      echo "<li>$serial</li>";
    }
  }
  echo "</ul>";
?>
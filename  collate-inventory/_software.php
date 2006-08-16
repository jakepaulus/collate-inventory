<?php
  /* This script takes a posted value from nav.php (included in every page) and returns 
    * an unordered list of search results to be updated into a div.
    */

  require_once('db_connect.php');

  $search = $_POST['search_software'];

  if(strlen($search) < "3"){ return;} // Prevent infinite loops and other bad stuff.
  
  echo "<ul>";
  
  $sql = "SELECT title FROM softwares WHERE title LIKE '%$search%'";
  
  $result = mysql_query($sql);
  
  while(list($title) = mysql_fetch_row($result)) { 
    echo "<li>$title</li>";
  }

  echo "</ul>";

?>



<?php
  /* This script takes a posted value from nav.php (included in every page) and returns 
    * an unordered list of search results to be updated into a div.
    */

  require_once('include/common.php');

  if(strlen(clean($_POST['title']) < "3")) {
    $search = clean($_POST['softwaresearch']); 
  }
  else{
    $search = clean($_POST['title']);
  }
 
  if(strlen($search) < "3"){ return;} // Prevent infinite loops and other bad stuff.
  
  echo "<ul>";
  
  $sql = "SELECT title FROM softwares WHERE title LIKE '%$search%' LIMIT 0, 5";
  
  $result = mysql_query($sql);
  
  while(list($title) = mysql_fetch_row($result)) { 
    echo "<li>$title</li>";
  }

  echo "</ul>";

?>



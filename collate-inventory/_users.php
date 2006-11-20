<?php
  /* This script takes a posted value from nav.php (included in every page) and returns 
    * an unordered list of search results to be updated into a div.
    */

  require_once('include/common.php');

   if(strlen(clean($_POST['usersearch'] < "3"))) {
    $search = clean($_POST['username']);
  }
  else {
    $search = clean($_POST['usersearch']);
  }

  if(strlen($search) < "3"){ return;} // Prevent infinite loops and other bad stuff.
  
  echo "<ul>";
  
  $sql = "SELECT username FROM users WHERE username LIKE '%$search%' LIMIT 0, 5";
  
  $result = mysql_query($sql);
  
  while(list($username) = mysql_fetch_row($result)) { 
    echo "<li>$username</li>";
  }

  echo "</ul>";

?>


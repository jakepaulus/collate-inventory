<?php
  /* This script takes a posted value from nav.php (included in every page) and returns 
    * an unordered list of search results to be updated into a div.
    */
    
  require_once('./include/common.php');
  
  // This if/else is necessary because we have more than one autocompleting
  // field on the same page POSTing to this script.
  
  if(strlen(clean($_POST['search']) < "3")) {
    $search = clean($_POST['hardwaresearch']);
  }
  else {
    $search = clean($_POST['search']);
  }

  if(strlen($search) < "3"){ exit();} // Prevent infinite loops and other bad stuff.
  
  echo "<ul>";
  
  $sql = "SELECT asset, serial FROM hardwares WHERE asset LIKE '%$search%' OR serial LIKE '%$search%' LIMIT 0, 5";

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
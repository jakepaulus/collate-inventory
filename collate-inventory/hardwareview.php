<?php
/**
 * This script contains functionality that will be used by every single page that is displayed.
 * It builds the CI array, creates the connection to the db that will be used by the rest of the
 * script, populates $CI['settings'] with settings from the db, and runs Access Control for the
 * program. 
 */
require_once('./include/common.php');


$op = $_GET['op'];

// make sure a sort variable is passed or set it to sort my name
if($_GET['sort'])
  $sort = $_GET['sort'];
else
  $sort = "hcat";

switch($op){
  case "view_all";
    list_hardwares($sort);
    break;
  
  default:
    view_details($search);
    break;
}


// And lastly, we'll need a footer
include_once('footer.php');



function view_details($search){
  global $CI;
  AccessControl("1"); // The access level required for this function is 1. Please see the documentation for this function in common.php.
 
  include_once('header.php'); // This has to be included after AccessControl in case it gets used by the error generator.
  
  $search = $_GET['search'];
  $row = mysql_query("SELECT * FROM hardwares WHERE asset='$search' OR serial='$search'");

  if(list($hid,$catid,$asset,$serial,$description,$value) = mysql_fetch_row($row)) { // User exists, display data
   echo "<div id=\"main\">".
	    "<h1>Details for Asset: $asset:</h1>".
            "<p><b>Description:</b><br />".
            "$description</p>".
            "<p><b>Other Details:</b><br />".
            "Serial Number: $serial <br /> Value: $value </p>";
  }
    // Display the history of this hardware
    echo "<h1>Good Title:</h1>".
           "</div>";
    
} // Ends view_details function

function list_hardwares($sort){
  global $CI;
  AccessControl("1"); // The Access Level for this function is 1. Please see the documentation in common.php.
  
  include_once('./header.php'); // This has to be included after AccessControl in case it gets used by the error generator.
          
  $limit = "25";    // This is the number of rows per page to be displayed. 
	               //This could be user-configurable, but I'm leaning away from it as it seem unnecessary.   
  $query_count   = "SELECT * FROM hardwares";
  $result_count   = mysql_query($query_count);    
  $totalrows       = mysql_num_rows($result_count);
  $numofpages   = round($totalrows/$limit, 0);  // This rounds the division result up to the nearest whole number.
  
  if(empty($_GET['page'])){
    $page = "1";
  }
  else {
    $page = $_GET['page']; // As this is never inserted into an SQL statement, it's safe to use without cleaning.
  }

  $limitvalue = $page * $limit - ($limit); 
  
  if($_GET['view'] == "printable"){ // This way, if you print, all users show up...I just hope they know what they're doing when they click print.
    $query = "SELECT * FROM hardwares ORDER by '$sort' ASC";
  }
  else {
    $query  = "SELECT * FROM hardwares ORDER BY '$sort' ASC LIMIT $limitvalue, $limit";
  }
  
 $result = mysql_query($query); 

  if(mysql_num_rows($result) == 0){
    $result = "No hardware assets were found in the database. Please click \"Add Hardware\" on the left to add assets to the database.";
    require_once('./infopage.php');
    exit();
  }
  else {
    echo "<div id=\"main\"><h1>All Hardware Assets</h1>";

    $bgcolor = "#E0E0E0"; // light gray
  
    echo "<table width=\"100%\">".
           "<tr><th align=\"left\"><a href=hardwareview.php?op=view_all&sort=hcat>Category</a></th>".
	   "<th align=\"left\"><a href=hardwareview.php?op=view_all&sort=deployed>Deployed?</a></th>".
           "<th align=\"left\"><a href=hardwareview.php?op=view_all&sort=asset>Asset Number</th></a>".
           "<th align=\"left\"><a href=hardwareview.php?op=view_all&sort=serial>Serial Number</th></a></tr>";
    
    while(list($hid,$catid,$asset,$serial,$description,$value) = mysql_fetch_row($result)){
      if ($bgcolor == "#E0E0E0"){  // This if - else rotates the background color of each row in the list.
        $bgcolor = "#FFFFFF";
      }
      else {
        $bgcolor = "#E0E0E0";
      }
      echo "<tr bgcolor=\"$bgcolor\"><td width=\"25%\">[Category]</td><td width=\"25%\">[Deployed?]</td><td width=\"25%\"><a href=\"hardwareview.php?op=view_details&search=$asset\">$asset</a></td><td width=\"25%\">$serial</td></tr>";
    }
  
    echo("</table>");
  
    if($_GET['view'] != "printable") {  
      if($page != "1"){ // Generate "Prev" link if there are previous pages to display.
        $pageprev = $page - "1";
        echo("<a href=\"softwareview.php?op=view_all&amp;page=$pageprev\"> Prev</a> "); 
      }
  
      $i = "1";
  
      if($page > $i){  // List all page numbers as links up to the current page if the page is after page 1.
        while($i < $page){
          echo " <a href=\"softwareview.php?op=view_all&amp;page=$i\">$i</a> ";
          $i++;
        }
      }
      if($numofpages > "1"){ // Only display the current page number if there is more than one page. 
         echo $page;
      }
      $i = $page + "1";
      if($numofpages-$page > "0"){ // Display all page numbers after the current page.
        while($i < $numofpages + "1"){
          echo " <a href=\"softwareview.php?op=view_all&amp;page=$i\">$i</a> ";
          $i++;
        }
      }
    
      if($page <= $numofpages){ // Display "Next" link if there is a page after the current one.
        $nextpage = $page + "1";
        echo " <a href=\"softwareview.php?op=view_all&amp;page=$nextpage\">Next</a>";
     }
  
      if($limitvalue + $limit < $totalrows){ 
        $upperlimit = $limitvalue + $limit;
      }
      else {
        $upperlimit = $totalrows;
      }
    
      if($limitvalue == "0"){ // The program is happy to start counting with 0, humans aren't.
        $lowerlimit = "1";
      }
      else {
        $lowerlimit = $limitvalue + "1";
      }
    }
    
    if($_GET['view'] != "printable"){
      echo "<br /><br />Showing $lowerlimit - $upperlimit out of $totalrows";
    }
    echo "</div>";
  } 
} // Ends list_hardwares function

?>

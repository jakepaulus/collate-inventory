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
  $sort = "title";

switch($op){
  case "view_all";
    list_softwares($sort);
    break;
  
  default: // Because the search form is submitted via GET (to make results linkable), the operation can't be directed via GET.
    view_details($title);
    break;
}

// And lastly, we'll need a footer
include_once('footer.php');

function view_details($title){
  global $CI;
  AccessControl("1"); // The access level required for this function is 1. Please see the documentation for this function in common.php.
 
  include_once('header.php'); // This has to be included after AccessControl in case it gets used by the error generator.
  
  $title = $_GET['software_title'];
  $row = mysql_query("SELECT * FROM softwares WHERE title='$title'");

  if(list($sid,$title,$description,$value,$total,$available) = mysql_fetch_row($row)) { // User exists, display data
    $deployedvalue = $value * ($total - $available);
    $totalvalue = $value * $total;
    $deployed = $total - $available;
    echo "<div id=\"main\">".
	    "<h1>Details for $title:</h1>".
            "<p><b>Description:</b><br />".
            "$description</p>".
            "<p><b>Other Details:</b><br />".
            "Cost Per license: $cost <br /> Value of deployed software: $deployedvalue <br /> Value of all licenses: $totalvalue <br />".
            "Licenses in use: $deployed <br /> Licenses available: $available </p>";
  }
    // Display the usernames of those who have licenses checked out
    echo "<h1>Good Title:</h1>".
           "</div>";
    
} // Ends view_details function


function list_softwares($sort){
  global $CI;
  AccessControl("1"); // The Access Level for this function is 1. Please see the documentation in common.php.
  
  include_once('./header.php'); // This has to be included after AccessControl in case it gets used by the error generator.
          
  $limit = "25";    // This is the number of rows per page to be displayed. 
	               //This could be user-configurable, but I'm leaning away from it as it seem unnecessary.   
  $query_count   = "SELECT * FROM softwares";
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
    $query = "SELECT * FROM softwares ORDER by '$sort' ASC";
  }
  else {
    $query  = "SELECT * FROM softwares ORDER BY '$sort' ASC LIMIT $limitvalue, $limit";
  }
  
 $result = mysql_query($query); 

  if(mysql_num_rows($result) == 0){
    $result = "No software titles were found in the database. Please click \"Add Software\" on the left to add software titles.";
    require_once('./infopage.php');
    exit();
  }
  else {
    echo "<div id=\"main\"><h1>All Software Titles</h1>";

    $bgcolor = "#E0E0E0"; // light gray
  
    echo "<table width=\"100%\">".
           "<tr><th align=\"left\"><a href=softwareview.php?op=view_all&sort=title>Title</a></th>".
           "<th align=\"left\"><a href=softwareview.php?op=view_all&sort=available>Available Licenses</th></a>".
           "<th align=\"left\"><a href=softwareview.php?op=view_all&sort=total>Total Licenses</th></a></tr>";
    
    while(list($sid,$title,$description,$value,$total,$available) = mysql_fetch_row($result)){
      if ($bgcolor == "#E0E0E0"){  // This if - else rotates the background color of each row in the list.
        $bgcolor = "#FFFFFF";
      }
      else {
        $bgcolor = "#E0E0E0";
      }
      echo "<tr bgcolor=\"$bgcolor\"><td width=\"25%\"><a href=\"softwareview.php?software_title=$title\">$title</a></td><td width=\"25%\">$available</td><td width=\"25%\">$total</td></tr>";
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
} // Ends list_softwares function

?>
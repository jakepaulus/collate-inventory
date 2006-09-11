<?php
/**
 * This script contains functionality that will be used by every single page that is displayed.
 * It builds the CI array, creates the connection to the db that will be used by the rest of the
 * script, populates $CI['settings'] with settings from the db, and runs Access Control for the
 * program. 
 */
require_once('./include/common.php');


$op = $_GET['op'];

switch($op){
  case "view_all";
    list_softwares();
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
  $row = mysql_query("SELECT sid, title, description, value, total, available FROM softwares WHERE title='$title'");

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


function list_softwares(){
  global $CI;
  AccessControl("1"); // The Access Level for this function is 1. Please see the documentation in common.php.
  
  include_once('./header.php'); // This has to be included after AccessControl in case it gets used by the error generator.
   
  // make sure a sort variable is passed or set it to sort my name
  if($_GET['sort']) {
    $sort = $_GET['sort'];
  }
  else {
  $sort = "title";
  }

  $limit = "25";    // This is the number of rows per page to be displayed. 
  $query_count   = "SELECT MAX(sid) FROM softwares";
  $result_count   = mysql_query($query_count);    
  $totalrows       = mysql_result($result_count, 0, 0);
  $numofpages   = round($totalrows/$limit, 0);  // This rounds the division result up to the nearest whole number.
  
  if(empty($_GET['page'])){
    $page = "1";
  }
  else {
    $page = $_GET['page']; // As this is never inserted into an SQL statement, it's safe to use without cleaning.
  }

  $lowerlimit = $page * $limit - $limit;   
  if($_GET['show'] == "all"){ // for print all, we really don't want to paginate, but we can still use this function
    $query = "SELECT title, total, available FROM softwares ORDER by $sort ASC";
  }
  else {
    // this is MUCH faster than using a lower limit because the primary key is indexed.
    $sql  = "SELECT title, total, available FROM softwares WHERE sid > $lowerlimit ORDER BY $sort LIMIT $limit";
  }
  
 $result = mysql_query($sql); 

  if(mysql_num_rows($result) == "0"){
    $result = "No software titles were found in the database. Please click \"Add Software\" on the left to add software titles.";
    require_once('./infopage.php');
    exit();
  }
  else {
    echo "<div id=\"main\"><h1>All Software Titles</h1>\n";
    $bgcolor = "#E0E0E0"; // light gray
    echo "<table width=\"100%\">\n".
            "<tr><th align=\"left\"><a href=\"softwareview.php?op=view_all&amp;sort=title\">Title</a></th>".
            "<th align=\"left\"><a href=\"softwareview.php?op=view_all&amp;sort=available\">Available Licenses</a></th>".
            "<th align=\"left\"><a href=\"softwareview.php?op=view_all&amp;sort=total\">Total Licenses</a></th></tr>\n";
    
    while(list($title,$total,$available) = mysql_fetch_row($result)){
      if ($bgcolor == "#E0E0E0"){  // This if - else rotates the background color of each row in the list.
        $bgcolor = "#FFFFFF";
      }
      else {
        $bgcolor = "#E0E0E0";
      }
      echo "<tr bgcolor=\"$bgcolor\"><td><a href=\"softwareview.php?software_title=$title\">$title</a></td><td>$available</td><td>$total</td></tr>\n";
    }
    echo("</table>");
  
 if(($_GET['show'] != "all") && ($numofpages > "1")) {
      if($page != "1") { // Generate Prev link only if previous pages exist.
        $pageprev = $page - "1";
	echo "<a href=\"softwareview.php?op=view_all&amp;page=$pageprev\"> Prev </a>";
      }
      $i = "1";
      while($i < $page) { // Build all page number links up to the current page
        echo "<a href=\"softwareview.php?op=view_all&amp;page=$i\"> $i </a>";
	$i++;
      }
      echo "[$page]"; // List Current page
      $i = $page + "1"; // Now we'll build all the page numbers after the current page if they exist.
      while(($numofpages-$page > "0") && ($i < $numofpages + "1")) {
        echo "<a href=\"softwareview.php?op=view_all&amp;page=$i\"> $i </a>";
        $i++;
      }
      if($page < $numofpages) { // Generate Next link if there is a page after this one
        $nextpage = $page + "1";
	echo "<a href=\"softwareview.php?op=view_all&amp;page=$nextpage\"> Next </a>";
      }
    }
    
    // Regardless of how many pages there are, well show how many records there are and what records we're displaying.
    if($lowerlimit + $limit < $totalrows) {
      $upperlimit = $lowerlimit + $limit;
    }
    else {
      $upperlimit = $totalrows;
    }
    if($lowerlimit == "0") { // The program is happy to start counting with 0, humans aren't.
      $lowerlimit = "1";
    }
    echo "<br />\n<br />\nShowing $lowerlimit - $upperlimit out of $totalrows<br />\n";
    if($_GET['show'] != "all") {
    echo "<a href=\"".$_SERVER['REQUEST_URI']."&amp;show=all\">Show all results on one page</a>";
    }
    echo "</div>";
  }  
} // Ends list_softwares function

?>
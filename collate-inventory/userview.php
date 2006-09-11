<?php
  /* This script shows user information that doesn't require field entered form submission. 
    * (A form may be used to remove hardware/software from a user's profile... I don't know yet.)
    */
    
    
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
  $sort = "lastname";

switch($op){
  case "view_all";
    list_users($sort);
    break;
  
  default: // Because the search form is submitted via GET (to make results linkable), the operation can't be directed via GET.
    view_details($user_name);
    break;
}

// And lastly, we'll need a footer
include_once('footer.php');


// Functions for this page are below
	
function view_details($user_name){
  global $CI;
  AccessControl("1"); // The access level required for this function is 1. Please see the documentation for this function in common.php.
 
  include_once('header.php'); // This has to be included after AccessControl in case it gets used by the error generator.

  $user_name = explode(" ",$_GET['user_name']);
  $firstname = rtrim($user_name[0]);
  $lastname = rtrim($user_name[1]);

  require_once('include/db_connect.php');
  $row = mysql_query("SELECT uid, firstname, lastname, phone, altphone, address, city, state, zip, lid, email FROM users WHERE firstname='$firstname' AND lastname='$lastname'");

  if(list($uid,$firstname,$lastname,$phone,$altphone,$address,$city,$state,$zip,$lid,$email) = mysql_fetch_row($row)) { // User exists, display data
    require_once('header.php');
    echo "<div id=\"main\">".
	    "<h1>Details for $firstname $lastname:</h1>".
            "<p><b>Address:</b><br />".
            "$address <br /> $city, $state $zip</p>".
            "<p><b>Telephone Numbers:</b><br />".
            "Primary: $phone<br />Alternate: $altphone</p>".
            "<p><b>Email Address:</b><br />".
            "<a href=\"mailto:$email\">$email</a>";
	 
    //Display hardware that belongs to the user:
    
        
    echo "</div>";
  }  
  else { // a row doesn't exist with that user's name: show error
    $result = "I'm sorry, you must supply a valid name in order for me to find what you're looking for.";
    require_once('infopage.php');
  } 

} // Ends view_details function

function list_users(){
  global $CI;
  AccessControl("1"); // The Access Level for this function is 1. Please see the documentation in common.php.
  
  include_once('header.php'); // This has to be included after AccessControl in case it gets used by the error generator.
  
  if($_GET['sort']) { // Determinte what to the list by.
    $sort = $_GET['sort'];
  }
  else {
    $sort = "lastname";
  }
  
  $limit = "25";
  $sql = "SELECT MAX(uid) FROM users"; // Determine the number of pages
  $result_count = mysql_query($sql);
  $totalrows = mysql_result($result_count, 0, 0);
  $numofpages = round($totalrows/$limit, 0); // This rounds the division result up to the nearest whole number.
  
  if(empty($_GET['page'])) { 
    $page = "1";
  }
  else {
    $page = $_GET['page']; 
  }
  
  $lowerlimit = $page * $limit - $limit;
  if($_GET['show'] == "all") { // for print all, we really don't want to paginate, but we can still use this function
    $sql = "SELECT firstname, lastname, city, email FROM users ORDER BY $sort ASC";
  }
  else {
    // this is MUCH faster than using a lower limit because the primary key is indexed.
    $sql = "SELECT firstname, lastname, city, email FROM users WHERE uid > $lowerlimit ORDER BY $sort LIMIT $limit"; 
  }
  $result = mysql_query($sql);
  
  if(mysql_num_rows($result) == "0") {
    $result = "No database records were found. Please add records using the \"Add..\" links to the left.";
    require_once('./infopage.php');
    exit();
  }
  else { 
    echo "<div id=\"main\">\n<h1>All Users</h1>\n";
    $bgcolor = "#E0E0E0"; // light gray
    echo "<table width=\"100%\">\n". // Here we actually build the HTML table
           "<tr><th align=\"left\"><a href=\"userview.php?op=view_all&sort=lastname\">Name</a></th>".
	   "<th align=\"left\"><a href=\"userview.php?op=view_all&sort=city\">City</a></th>".
	   "<th align=\"left\"><a href=\"userview.php?op=view_all&sort=email\">Email</a></th></tr>\n";
    
    while(list($firstname,$lastname,$city,$email) = mysql_fetch_row($result)) { 
      if ($bgcolor == "#E0E0E0"){  // This if - else rotates the background color of each row in the list.
        $bgcolor = "#FFFFFF";
      }
      else {
        $bgcolor = "#E0E0E0";
      }
      echo "<tr bgcolor=\"$bgcolor\"><td width=\"33%\"><a href=\"userview.php?op=view_details&user_name=$firstname $lastname\">$firstname $lastname</a></td><td width=\"33%\">$city</td><td width=\"33%\"><a href=\"mailto:$email\">$email</a></td></tr>\n";
    }
    echo "</table>"; // Here the HTML table ends. Below we're just building the Prev [page numbers] Next links.
    
    if(($_GET['show'] != "all") && ($numofpages > "1")) {
      if($page != "1") { // Generate Prev link only if previous pages exist.
        $pageprev = $page - "1";
	echo "<a href=\"userview.php?op=view_all&page=$pageprev\"> Prev</a>";
      }
      $i = "1";
      while($i < $page) { // Build all page number links up to the current page
        echo "<a href=\"userview.php?op=view_all&page=$i\">$i</a>";
	$i++;
      }
      echo "[$page]"; // List Current page
      $i = $page + "1"; // Now we'll build all the page numbers after the current page if they exist.
      while(($numofpages-$page > "0") && ($i < $numofpages + "1")) {
        echo "<a href=\"userview.php?op=view_all&page=$i\"> $i </a>";
        $i++;
      }
      if($page < $numofpages) { // Generate Next link if there is a page after this one
        $nextpage = $page + "1";
	echo "<a href=\"userview.php?op=view_all&page=$nextpage\"> Next </a>";
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
    echo "<a href=\"".$_SERVER['REQUEST_URI']."&show=all\">Show all results on one page</a>";
    }
    echo "</div>";
  }  
} // Ends list_users function
?>

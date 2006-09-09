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
  $row = mysql_query("SELECT * FROM users WHERE firstname='$firstname' AND lastname='$lastname'");

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
    echo "<h1>Hardware:</h1>"; 
    
    $sql = "SELECT * FROM hardware WHERE uid=$uid"; // Grab user's hardware information
    
    if(mysql_num_rows(mysql_query($sql)) < "1"){
      echo "This user has no hardware assigned to them.";
    }
    else {
    $row = mysql_query($sql);
      echo "<table cellspacing=\"7\">".
             "<tr><th>Name:</th><th>Asset ID:</th><th>Serial Number:</th></tr>";
      while(list($hid,$asset,$serial,$title,$desc,$value,$uid,$assigned,$returned) = mysql_fetch_row($row)){
        echo "<tr><td><a href=\"hardwareview.php?hid=$hid\">$title</a></td><td>$asset</td><td>$serial</td>";
      }
      echo "</table>";
    }
        
    echo "</div>";
    require_once('footer.php');
  }  
  else { // a row doesn't exist with that user's name: show error
    $result = "I'm sorry, you must supply a valid name in order for me to find what you're looking for.";
    require_once('infopage.php');
  } 

} // Ends view_details function

function list_users($sort){
  global $CI;
  AccessControl("1"); // The Access Level for this function is 1. Please see the documentation in common.php.
  
  include_once('header.php'); // This has to be included after AccessControl in case it gets used by the error generator.
          
  $limit = "25";    // This is the number of rows per page to be displayed. 
	               //This could be user-configurable, but I'm leaning away from it as it seem unnecessary.   
  $query_count   = "SELECT * FROM users";
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
    $query = "SELECT * FROM users ORDER by '$sort' ASC";
  }
  else {
    $query  = "SELECT * FROM users ORDER BY '$sort' ASC LIMIT $limitvalue, $limit";
  }
  
  $result = mysql_query($query); 

  if(mysql_num_rows($result) == 0){
    $result = "No users were found in the database. Please click \"Add User\" on the left to add users.";
    require_once('infopage.php');
    return;
  }
  else {

  echo "<div id=\"main\"><h1>All Users</h1>";

    $bgcolor = "#E0E0E0"; // light gray
  
    echo "<table width=\"75%\">".
           "<tr><th align=\"left\"><a href=userview.php?op=view_all&sort=name>Name</a></th>".
           "<th align=\"left\"><a href=userview.php?op=view_all&sort=city>City</th></a>".
           "<th align=\"left\"><a href=userview.php?op=view_all&sort=email>Email Address</th></a></tr>";
    
    while(list($uid,$firstname, $lastname,$phone,$altphone,$address,$city,$state,$zip,$lid,$email) = mysql_fetch_row($result)){
      if ($bgcolor == "#E0E0E0"){  // This if - else rotates the background color of each row in the list.
        $bgcolor = "#FFFFFF";
      }
      else {
        $bgcolor = "#E0E0E0";
      }
      echo "<tr bgcolor=\"$bgcolor\"><td width=\"25%\"><a href=\"userview.php?user_name=$firstname $lastname\">$firstname $lastname</a></td><td width=\"25%\">$city</td><td width=\"25%\"><a href=\"$email\">$email</a></td></tr>";
    }
  
    echo("</table>");
  
    if($_GET['view'] != "printable") {  
      if($page != "1"){ // Generate "Prev" link if there are previous pages to display.
        $pageprev = $page - "1";
        echo("<a href=\"userview.php?op=view_all&amp;page=$pageprev\"> Prev</a> "); 
      }
  
      $i = "1";
  
      if($page > $i){  // List all page numbers as links up to the current page if the page is after page 1.
        while($i < $page){
          echo " <a href=\"userview.php?op=view_all&amp;page=$i\">$i</a> ";
          $i++;
        }
      }
      if($numofpages > "1"){ // Only display the current page number if there is more than one page. 
         echo $page;
      }
      $i = $page + "1";
      if($numofpages-$page > "0"){ // Display all page numbers after the current page.
        while($i < $numofpages + "1"){
          echo " <a href=\"userview.php?op=view_all&amp;page=$i\">$i</a> ";
          $i++;
        }
      }
    
      if($page <= $numofpages){ // Display "Next" link if there is a page after the current one.
        $nextpage = $page + "1";
        echo " <a href=\"userview.php?op=view_all&amp;page=$nextpage\">Next</a>";
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
} // Ends list_users function
?>

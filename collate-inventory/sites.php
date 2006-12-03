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

  case "add"; 
  add_site();
  break;
  
  case "new";
  process_new_site();
  break;

  case "delete";
  delete_site();
  break;

  default:
  list_sites();
  break;

}

function list_sites() {
  global $CI;
  AccessControl('1');
  require_once('./include/header.php'); // This has to be included after AccessControl in case it gets used by the error generator.
  
  $limit = "5";
  $sql = "SELECT COUNT(*) FROM sites"; // To determine the number of pages
  $result_count = mysql_query($sql);
  $totalrows = mysql_result($result_count, 0, 0);
  $numofpages = ceil($totalrows/$limit);
  
  
  echo "<h1>Sites</h1>\n".
	 "<p style=\"text-align: right;\"><a href=\"./sites.php?op=add\"><img src=\"./images/add.png\" alt=\"Add\" /> Add a Site </a></p><br />";


  if(empty($_GET['page'])) { 
    $page = "1";
  }
  else {
    $page = $_GET['page']; 
  }
  
  $lowerlimit = $page * $limit - $limit;
  if($_GET['show'] == "all") { // for print all, we really don't want to paginate, but we can still use this function
    $sql = "SELECT sid, name, address, city, state, zip FROM sites ORDER BY name ASC";
  }
  else {
    // this is MUCH faster than using a lower limit because the primary key is indexed.
    $sql = "SELECT sid, name, address, city, state, zip FROM sites ORDER BY name LIMIT $lowerlimit, $limit"; 
  }
  $result = mysql_query($sql);
  
  if($totalrows < "1") {
    $result = "No database records were found. Please add records using the \"Add..\" link above.";
    require_once('./include/infopage.php');
    exit();
  }
  else { 
  echo "<table width=\"100%\">\n";
  
  while(list($sid,$name,$address,$city,$state,$zip) = mysql_fetch_row($result)) {
   
    echo "<tr><td><b>$name</b></td><td><a href=\"./sites.php?op=delete&amp;name=$name\">".
           "<img src=\"./images/remove.png\" alt=\"remove\" /></a></td></tr><tr><td>$address</td></tr>".
	   "<tr><td>$city, $state $zip</td></tr><tr><td><hr class=\"division\" /></td></tr>\n";
  }
  echo "</table>\n";
   
    if(($_GET['show'] != "all") && ($numofpages > "1")) {
      if($page != "1") { // Generate Prev link only if previous pages exist.
        $pageprev = $page - "1";
	echo "<a href=\"sites.php?page=$pageprev\"> Prev</a>";
      }
      $i = "1";
      while($i < $page) { // Build all page number links up to the current page
        echo "<a href=\"sites.php?page=$i\">$i</a>";
	$i++;
      }
      echo "[$page]"; // List Current page
      $i = $page + "1"; // Now we'll build all the page numbers after the current page if they exist.
      while(($numofpages-$page > "0") && ($i < $numofpages + "1")) {
        echo "<a href=\"sites.php?page=$i\"> $i </a>";
        $i++;
      }
      if($page < $numofpages) { // Generate Next link if there is a page after this one
        $nextpage = $page + "1";
	echo "<a href=\"sites.php?page=$nextpage\"> Next </a>";
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
    if($_GET['show'] != "all" && $numofpages > "1") {
       echo "<a href=\"".$_SERVER['REQUEST_URI']; 
    if(stristr($_SERVER['REQUEST_URI'], "?") == TRUE){ 
      echo "&amp;"; 
    } 
    else {
      echo "?";
    }
    echo "show=all\">Show all results on one page</a>";
    }
  }  
require_once('./include/footer.php');
} // Ends list_sites function


function add_site(){
  global $CI;
  AccessControl('3');
  require_once('./include/header.php'); // This has to be included after AccessControl in case it gets used by the error generator.
  
  ?>
  <h1>Add a Site</h1>  
  <br />
  <form id="new_software" action="sites.php?op=new" method="post">
    <p>Site Name:<br />
    <input id="name" name="name" type="text" size="30" /></p>
    <p>Street Address:<br />
    <textarea id="address" name="address" rows="2" cols="30"></textarea></p>
    <p>City:<br />
    <input id="city" name="city" type="text" size="" /></p>
    <p>State/Province:<br />
    <input id="state" name="state" type="text" size="" /></p>
    <p>Postal Code:<br />
    <input id="zip" name="zip" type="text" size="" /></p>
    <input type="submit" value=" Submit " />
  </form>
  
  <?php
  require_once('./include/footer.php');
}// Ends add_site function

function  process_new_site() {
  global $CI;
  AccessControl('3');
  require_once('./include/header.php'); // This has to be included after AccessControl in case it gets used by the error generator.
  
  if (strlen($_POST['name']) < "1" || strlen($_POST['address']) < "1" || strlen($_POST['city']) < "1" || strlen($_POST['state']) < "1" || strlen($_POST['zip']) < "1" ){ 
    $result = "All fields except are required. Please go back and ensure all fields are completed."; 
    require_once('./include/infopage.php'); 
  } 
  else {

  $name = clean($_POST['name']);
  $address = clean($_POST['address']);
  $city = clean($_POST['city']);
  $state = clean($_POST['state']);
  $zip = clean($_POST['zip']);
  

  $sql = "INSERT INTO sites (sid, name, address, city, state, zip) VALUES(NULL, '$name', '$address', '$city', '$state', '$zip')";

  $result = mysql_query($sql);

  if (mysql_affected_rows() == "1"){
    $result = "The data has been succesfully added to the database.";
  }
  else {
    $result = "<b>Error:</b> The most likely cause for this failure is that the site's name already exists in the database.";
  }
    require_once('./include/infopage.php');
  }


  require_once('./include/footer.php');
} // Ends process_new_site function


function delete_site() {
  global $CI;
  AccessControl('3');
  require_once('./include/header.php'); // This has to be included after AccessControl in case it gets used by the error generator.
  
  $name = clean($_GET['name']);
  
  // We're using this same function to confirm the user's action and process the row drop in the database
  if($_GET['confirm'] != "yes") { // draw the confirmation page
  
    // First we check to see if there are any users or hardware still at this site. If there are, we wont let the user delete the site.
    $sql = "SELECT * FROM users WHERE site='$name'";
    $test = mysql_query($sql);
    if(mysql_num_rows($test) != "0") { // There are users at this site.
      $result = "There are users are users assigned to the site called \"$name\". This site cannot be deleted until these users are re-assigned to a different site. \n".
                   "This can be done in the Manage Users section of the Control Panel."; // This should also include a link to the search results for a list of all of the users at this site.
      require_once('./include/infopage.php');
    }
    
    $sql = "SELECT * FROM hardware WHERE site='$name' AND cidate='0000-00-00 00:00:00'";
    $test = mysql_query($sql);
    if(mysql_num_rows($test) != "0") { // There is hardware at this site.
      $result = "There is hardware currently located at the site called \"$name\". This site cannot be deleted until this is corrected. You can use the Advanced Search \n".
                   "Page to find all hardware at this site and reassign it."; // I would like to replace this with a link to the search results for this.
      require_once('./include/infopage.php');
    }
        
    // There aren't any users or hardware at the site, we'll just make sure the user is sure they want to delete the site.
    $sql = "SELECT name, address, city, state, zip FROM sites WHERE name='$name'";
    $row = mysql_query($sql);
    while(list($name,$address,$city,$state,$zip) = mysql_fetch_row($row)) { // They are requesting deletion of a valid site
      $result = "Are you sure you'd like to delete the following site?<br />\n".
                   "<table><tr><td><b>$name</b></td></tr><tr><td>$address</td></tr><tr><td>$city, $state $zip</td></tr></table><br />".
		   "<a href=\"sites.php?op=delete&amp;name=$name&amp;confirm=yes\"><img src=\"./images/apply.png\" alt=\"confirm\" /></a> &nbsp; <a href=\"sites.php\"><img src=\"./images/cancel.png\" alt=\"cancel\" /></a>";
      require_once('./include/infopage.php');
    }
    $result = "The site you're attempting to delete is not a valid site in the database. Please go back and use the buttons ".
                 "provided to delete a site. If you believe you have reached this page in error, please notify ". $CI['adminname'];
    require_once('./include/infopage.php');
  }
  else { // delete the row, they are sure
    $sql = "DELETE FROM sites WHERE name='$name'";
    $result = mysql_query($sql);
       
    if (mysql_affected_rows() == "1"){
      $result = "The site \"$name\" has been removed from the database.";
    }
    else {
      $result = "Something went wrong. I suspect you didn't click the confirm link but instead tried to edit the URL manually.";
    }
  }
  require_once('./include/infopage.php');
} // Ends delete_site function
?>
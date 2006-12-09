<?php
/**
 * This script contains functionality that will be used by every single page that is displayed.
 * It builds the CI array, creates the connection to the db that will be used by the rest of the
 * script, populates $CI['settings'] with settings from the db, and runs Access Control for the
 * program. 
 */
require_once('./include/common.php');
require_once('./include/header.php');

if(isset($_GET['op'])){
  $op = $_GET['op'];
}
else {
  $op = "list_all";
}


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
  $accesslevel = "1";
  $message = "site list viewed";
  AccessControl($accesslevel, $message); 
  
  $limit = "5";
  $sql = "SELECT COUNT(*) FROM sites"; // To determine the number of pages
  $result_count = mysql_query($sql);
  $totalrows = mysql_result($result_count, 0, 0);
  $numofpages = ceil($totalrows/$limit);
    
  echo "<h1>Sites</h1>\n".
	 "<p style=\"text-align: right;\"><a href=\"./sites.php?op=add\"><img src=\"./images/add.gif\" alt=\"Add\" /> Add a Site </a></p><br />";


  if(empty($_GET['page'])) { 
    $page = "1";
  }
  else {
    $page = $_GET['page']; 
  }
  
  $lowerlimit = $page * $limit - $limit;
  // this is MUCH faster than using a lower limit because the primary key is indexed.
  $sql = "SELECT sid, name, address, city, state, zip FROM sites ORDER BY name LIMIT $lowerlimit, $limit"; 
  
  $result = mysql_query($sql);
  
  if($totalrows < "1") {
    $result = "No database records were found. Please add records using the \"Add..\" link above.";
    require_once('./include/infopage.php');
    exit();
  }

  echo "<table width=\"100%\">\n";
  
  while(list($sid,$name,$address,$city,$state,$zip) = mysql_fetch_row($result)) {
   
    echo "<tr><td><b>$name</b></td><td><a href=\"./sites.php?op=delete&amp;name=$name\">".
           "<img src=\"./images/remove.gif\" alt=\"remove\" /></a></td></tr><tr><td>$address</td></tr>".
	   "<tr><td>$city, $state $zip</td></tr><tr><td><hr class=\"division\" /></td></tr>\n";
  }
  echo "</table>\n";
   
    if($numofpages > "1") {
    if($page != "1") { // Generate Prev link only if previous pages exist.
      $pageprev = $page - "1";
       echo "<a href=\"".$_SERVER['REQUEST_URI']."&amp;page=$pageprev\"> Prev </a>";
    }
    
	if($numofpages < "10"){
	  $i = "1";
      while($i < $page) { // Build all page number links up to the current page
        echo "<a href=\"".$_SERVER['REQUEST_URI']."&amp;page=$i\"> $i </a>";
	    $i++;
      }
	}
	else {
	  if($page > "4") {
	    echo "...";
	  }
	  $i = $page - "3";
	  while($i < $page ) { // Build all page number links up to the current page
	    if($i > "0"){
          echo "<a href=\"".$_SERVER['REQUEST_URI']."&amp;page=$i\"> $i </a>";
	    }
		$i++;
      }
	}
    echo "[$page]"; // List Current page
	
	if($numofpages < "10"){	
      $i = $page + "1"; // Now we'll build all the page numbers after the current page if they exist.
      while(($numofpages-$page > "0") && ($i < $numofpages + "1")) {
        echo "<a href=\"".$_SERVER['REQUEST_URI']."&amp;page=$i\"> $i </a>";
        $i++;
      }
	}
	else{
	  $i = $page + "1";
	  $j = "1";
	  while(($numofpages-$page > "0") && ($i <= $numofpages) && ($j <= "3")) {
        echo "<a href=\"".$_SERVER['REQUEST_URI']."&amp;page=$i\"> $i </a>";
        $i++;
		$j++;
      }
	  if($i <= $numofpages){
	    echo "...";
	  }
	}
    if($page < $numofpages) { // Generate Next link if there is a page after this one
      $nextpage = $page + "1";
	  echo "<a href=\"".$_SERVER['REQUEST_URI']."&amp;page=$nextpage\"> Next </a>";
	}
  }
    
    // Regardless of how many pages there are, well show how many records there are and what records we're displaying.
	
    if($lowerlimit == "0") { // The program is happy to start counting with 0, humans aren't.
      $lowerlimit = "1";
    }
	else{
	  $lowerlimit++;
	}
	$upperlimit = $lowerlimit + $limit - 1;
	if($upperlimit > $totalrows) {
	  $upperlimit = $totalrows;
	}
	if($result_count <= $totalrows){
	  $howmany = "$lowerlimit - $upperlimit out of";
	}
	else{
	  $howmany = "";
	}
    echo "<br />\n<br />\nShowing $howmany $totalrows results.<br />\n";  

require_once('./include/footer.php');
} // Ends list_sites function


function add_site(){
  global $CI;
  $accesslevel = "3";
  $message = "new site form accessed";
  AccessControl($accesslevel, $message); 
  
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
  $name = clean($_POST['name']);
  $accesslevel = "3";
  $message = "new site added: $name";
  AccessControl($accesslevel, $message); 
  
  if (strlen($_POST['name']) < "1" || strlen($_POST['address']) < "1" || strlen($_POST['city']) < "1" || strlen($_POST['state']) < "1" || strlen($_POST['zip']) < "1" ){ 
    $result = "All fields except are required. Please go back and ensure all fields are completed."; 
    require_once('./include/infopage.php'); 
  } 
  else {

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
  $name = clean($_GET['name']);
  $accesslevel = "3";
  $message = "site deleted: $name";
  AccessControl($accesslevel, $message); 
  
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
		   "<a href=\"sites.php?op=delete&amp;name=$name&amp;confirm=yes\"><img src=\"./images/apply.gif\" alt=\"confirm\" /></a> &nbsp; <a href=\"sites.php\"><img src=\"./images/cancel.gif\" alt=\"cancel\" /></a>";
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
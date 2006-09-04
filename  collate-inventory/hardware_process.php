<?php

$op = $_GET['op'];

if($_GET['sort'])
  $sort = $_GET['sort'];
else
  $sort = "name";

switch($op){
        case "view";
        view_hardware();
        break;

	case "list";
	list_hardware($sort);
	break;

	default:
	add_hardware();
	break;
}

// Table Columns: uid, name, phone, altphone, address, city, state, zip, email

function clean($variable){ // This function needs to be moved to a separate script that will house all user-input cleaning 

functions.
  $variable = trim(strip_tags(nl2br($variable))); 
  return $variable;
}

function add_hardware(){
  require_once('header.php');
  echo "<div id=\"main\">";
  // Display new-user form that posts to user_process.php 


  echo "<h1>Add New Hardware:</h1>".
         "<form name=\"new_user\" action=\"hardware_process.php?op=new\" method=\"post\">".
         "<p>Device Type:<br />".
         "<select name=\"hcat\">";

  require_once('include/db_connect.php');
  
  $row = mysql_query("SELECT catid,catname FROM hcats ORDER BY catname ASC");

  while(list($catid,$catname) = mysql_fetch_row($row)) {
    echo "<option value=\"$catid\">$catname";
  }
    
  echo "</select>".
         "<p>Model:<br />".
    <input id="model" name="model" type="text" size="" /></p>
    <p>Serial<br />
    <input id="serial" name="serial" type="text" size="" /></p>
    <p>User:<br />
   <select name="uid">
<?php
require_once('db_connect.php');
  $row = mysql_query("SELECT * FROM users ORDER BY lastname ASC");

  while(list($uid,$firstname,$lastname) = mysql_fetch_row($row)) { 

echo "<option value=\"$uid\">$lastname $firstname";}
?>
</select>
  <br /><br />
    <input type="submit" value=" Submit " />
  </form>
<?php
  echo "</div>";
  require_once('footer.php');
}

function list_hardware($sort){
  require_once('db_connect.php');
  require_once('header.php');
        
  echo "<div id=\"main\"><h1>All Hardware</h1>";
      
  $limit = "25";    // This is the number of rows per page to be displayed. 
	               //This could be user-configurable, but I'm leaning away from it as it seem unnecessary.   
  $query_count   = "SELECT * FROM hardware";
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

  $query  = "SELECT hid, name, model, serial FROM `hardware` LEFT JOIN hardware_category ON hardware.cid = hardware_category.cid  ORDER BY '$sort' ASC LIMIT $limitvalue, $limit";        
  $result = mysql_query($query); 

  if(mysql_num_rows($result) == 0){
    $result = "No users were found in the database. Please click \"Add User\" on the left to add users.";
    require_once('infopage.php');
    return;
  }

  $bgcolor = "#E0E0E0"; // light gray
  
  echo "<table>".
         "<tr><th><a href=hardware.php?op=list&sort=name>Type</a></th>".
	 "<th><a href=hardware.php?op=list&sort=model>Model</th></a>".
         "<th><a href=hardware.php?op=list&sort=serial>Serial</th></a></tr>";
    
  while(list($hid,$name,$model,$serial) = mysql_fetch_row($result)){
    if ($bgcolor == "#E0E0E0"){  // This if - else rotates the background color of each row in the list.
      $bgcolor = "#FFFFFF";
    }
    else {
      $bgcolor = "#E0E0E0";
    }
    echo "<tr bgcolor=\"$bgcolor\"><td>$name</td><td>$model</td><td>$serial</td></tr>";
  }
  
  echo("</table>");
  
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
  echo "<br /><br />Showing $lowerlimit - $upperlimit out of $totalrows";
  echo "</div>";
  require_once('footer.php');


}
function view_hardware() {
$hid = $_GET['hid'];

 require_once('db_connect.php');


  $row = mysql_query("SELECT name, model, serial FROM `hardware` LEFT JOIN hardware_category ON hardware.cid = hardware_category.cid WHERE hid='$hid'");

  if(list($name,$model,$serial) = mysql_fetch_row($row)) { // User exists, display dankta
    require_once('header.php');
    echo "<div id=\"main\">".
	    "<h1>Details for $model:</h1>".
            "<p><b>Type: </b> <br />".
            "$name</p>".
            "<p><b>Model: </b> <br />".
            "$model</p>".
            "<p><b>Serial:</b><br />".
            "$serial</a>";
	echo "</div>"; 
    require_once('footer.php');
    }
  else { // a row doesn't exist with that user's name: show error
    $result = "I'm sorry, you must supply a valid name in order for me to find what you're looking for.";
    require_once('infopage.php');
  } 
}
?>
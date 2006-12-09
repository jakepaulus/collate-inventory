<?php
/* This script is basically the view portion of user change forms. Input methods are supplied by functions
  * in this script and data is submitted to user_process.php before it hits the database.
  */

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

    case "delete";
	delete_software();
	break;

    case "update";
	update_software();
	break;

    case "release";
	release_license();
	break;

	case "assign";
	assign_software();
	break;
	
	case "add";
	add_software();
	break;
	
	case "new";
	process_new_software();
	break;
	
	case "show";
    view_details();
    break;
	
    default:
    list_softwares();
    break;
}

function delete_software() {
  global $CI;
  $title = clean($_GET['title']);
  $accesslevel = "3";
  $message = "software deleted: $title";
  AccessControl($accesslevel, $message); 
  
  // First check to make sure this is a valid title
  $sql = "SELECT inuse FROM softwares WHERE title='$title'";
  $inuse = mysql_query($sql);
  if(mysql_num_rows($inuse) < "1") {
    $result = "The title \"$title\" was not found in the database. Please try again. If the problem persists, please ".
	          "contact your administrator.";
	require_once('./include/infopage.php');
  }
  
  // Make sure the software isn't checked out to anyone
  $inuse = mysql_result($inuse, 0, 0);
  if($inuse != "0"){
    $result = "This software can not be deleted because licenses are currently assigned to hardware assets.";
	require_once('./include/infopage.php');
  }
  
  if($_GET['confirm'] != "yes") { // Show confirmation message or error 
    // This is a valid asset to be retired.
    echo "<h1>Delete $title?</h1><br />".
         "<p><b>Are you sure you'd like to delete this software title from the database?</b>\n".
	     "<br /><br /><a href=\"software.php?op=delete&amp;title=$title&amp;confirm=yes\">".
	     "<img src=\"./images/apply.gif\" alt=\"confirm\" /></a> &nbsp; <a href=\"software.php?op=show&amp;title=$title\">".
	     "<img src=\"./images/cancel.gif\" alt=\"cancel\" /></a>";
      require_once('./include/footer.php');
      exit();
  }
  
  // They are sure.
  $sql = "DELETE FROM softwares WHERE title='$title' LIMIT 1";
  mysql_query($sql);
  
  $result = "$title has successfully been deleted.";
  require_once('./include/infopage.php');
  
} // Ends delete_software function

function update_software() {
  global $CI;
  $title = clean($_GET['title']);
  $accesslevel = "3";
  $message = "software updated: $title";
  AccessControl($accesslevel, $message); 
  
  if(isset($_GET['action'])){
    $action = clean($_GET['action']);
  }
  else {
    $action = "show form";
  }
  $sql = "SELECT description, total FROM softwares WHERE title='$title'";
  $row = mysql_query($sql);
  
  if(mysql_num_rows($row) != "1"){
    $result = "No software title was found that matched your query. Please try again. If you feel you have reached ".
	          "this page in error, please contact your system administrator.";
	require_once('./include/infopage.php');
  }
  
  if($action != "update"){
  list($description,$total) = mysql_fetch_row($row);
  $description = preg_replace('/<br\\s*?\/??>/i', '', $description);
  ?>
  <h1>Update <?php echo $title; ?>:</h1>
  <br />
  <form action="software.php?op=update&amp;title=<?php echo $title; ?>&amp;action=update" method="post">
  <p>Description of Software:<br />
  <textarea id="desc" name="desc" rows="2" cols="30"><?php echo $description; ?></textarea></p>
  <p>Number of licenses you own:<br />
  <input id="total" name="total" type="text" size="10" value="<?php echo $total; ?>"/></p>
  <input type="submit" value=" Submit " />
  </form>
  
  <?php
  require_once('./include/footer.php');
  exit();
  }
  
  $description = nl2br(clean($_POST['desc']));
  $total = clean($_POST['total']);
  
  $sql = "UPDATE softwares SET description='$description', total='$total' WHERE title='$title'";
  mysql_query($sql);
  
  // If we've gotten this far, everything should have worked fine. 
  $notice = "Update successful";
  header("Location: software.php?op=show&title=$title&notice=$notice");
  
} // Ends update_software function

function release_license(){
  global $CI;
  $title = clean($_GET['title']);
  $accesslevel = "3";
  $message = "software license released: $title";
  AccessControl($accesslevel, $message); 
  
  $hardware = clean($_GET['hardware']);
  
  // Now we need the hardware id.
  $sql = "SELECT hid FROM hardwares WHERE asset='$hardware' OR serial='$hardware'";
  $row = mysql_query($sql);
  if(mysql_num_rows($row) != "1") {
    $result = "No hardware was found that matched your query. Please try again. If this problem persists, please contact your administrator.";
	require_once('./include/infopage.php');
  }
  $hid = mysql_result($row, 0, 0);
  
  // Release the license
  $sql = "UPDATE software SET cidate=NOW() WHERE title='$title' AND hid='$hid' AND cidate='0000-00-00 00:00:00'";
  mysql_query($sql);

  if(mysql_affected_rows() != "1"){
    $result = "An unknown error has occured. No licenses have been released. It is most likely that the license was already released.";
	require_once('./include/infopage.php'); // stop processing so we don't decrement the counter.
  }
  
  // Decrement the inuse counter
  $sql = "UPDATE softwares SET inuse=inuse-1 WHERE title='$title'";
  $test = mysql_query($sql);
  if(mysql_affected_rows() != "1"){
    $result = "An unknown error has occured. The license was released from the hardware but the inuse counter could not be decremented. ".
	          "Please contact your system administrator.";
	require_once('./include/infopage.php'); // stop processing so we don't decrement the counter.
  }
  
  // Everything should have worked if we get here.
  $notice = "One license for $title was successfully released.";
  header("Location: hardware.php?op=show&search=$hardware&notice=$notice");
} // Ends release_license function

function assign_software(){
  global $CI;
  $hardware = clean($_GET['hardwaresearch']);
  $title = clean($_GET['softwaresearch']);
  $accesslevel = "3";
  $message = "software license assigned: $title to: $hardware";
  AccessControl($accesslevel, $message); 
    
  $returnto = urldecode(clean($_GET['returnto']));
  
  // This is separated out like this so the error messages can be specific. I don't anticipate this being a problem as I doubt performance will be a requiremement because
  // this application will likely have a low number of users.
  
  // These could really be a subquery in the insert statement, but we need to do this check to make sure it is a valid title anyway.
  $sql = "SELECT sid FROM softwares WHERE title='$title'";
  $row = mysql_query($sql);
  if(mysql_num_rows($row) != "1"){
    $result = "No software was found that matched your query. Please try again. If this problem persists, please contact your administrator.";
	require_once('./include/infopage.php');
  }
  $sid = mysql_result($row, 0, 0);

  $sql = "SELECT hid FROM hardwares WHERE asset='$hardware' OR serial='$hardware'";
  $row = mysql_query($sql);
  if(mysql_num_rows($row) != "1") {
    $result = "No hardware was found that matched your query. Please try again. If this problem persists, please contact your administrator.";
	require_once('./include/infopage.php');
  }
  $hid = mysql_result($row, 0);
  
  // We need to make sure this hardware doesn't already have a license allocated to it.
  $sql = "SELECT coid FROM software WHERE title='$title' AND cidate='0000-00-00 00:00:00' AND hid='$hid'";
  $test = mysql_query($sql);

  if(mysql_num_rows($test) >= "1") {
    $notice = "A license for this software is already assigned to the hardware asset you specified.";
	header("Location: $returnto&notice=$notice");
	exit();
  }
  
  // Now we'll insert the new license record.
  $sql = "INSERT INTO software (title, hid, codate) VALUES('$title', '$hid', NOW())";
  mysql_query($sql);
  if(mysql_affected_rows() != "1") {
    $result = "An Error has occured that has prevented a license from being issued to this hardware. ".
	          "If this problem persists, please contact your administrator.";
	require_once('./include/infopage.php');
  }
  
  // Since we know a row was created, it is safe to update the inuse counter.
  $sql = "UPDATE softwares SET inuse=inuse+1 WHERE sid='$sid'";
  mysql_query($sql);
  
  // Everything should have worked if we get here.
  $notice = "A license for $title was successfully assigned.";
  header("Location: $returnto&notice=$notice");
  
} // Ends assign_software function


function add_software(){
  global $CI;
  $accesslevel = "3";
  $message = "new software form accessed";
  AccessControl($accesslevel, $message); 
  

?>
  <h1>Add Software To Your Library:</h1>
  <br />
  <form id="new_software" action="software.php?op=new" method="post">
    <p>Software Title:<br />
    <input id="title" name="title" type="text" size="30" /></p>
    <p>Description of Software:<br />
    <textarea id="desc" name="desc" rows="2" cols="30"></textarea></p>
    <p>Number of licenses you own:<br />
    <input id="total" name="total" type="text" size="10" /></p>
    <input type="submit" value=" Submit " />
  </form>
<?php
  require_once('./include/footer.php');
}

function process_new_software(){
  global $CI;
  
  $title = clean($_POST['title']);
  $description = clean($_POST['desc']);
  $total = clean($_POST['total']);
  
  $accesslevel = "3";
  $message = "software license assigned: $title to: $hardware";
  AccessControl($accesslevel, $message); 
  
  if (strlen($_POST['title']) < "1" || strlen($_POST['desc']) < "1" || strlen($_POST['total']) < "1" ){ 
    $result = "All fields except are required. Please go back and ensure all fields are completed."; 
    require_once('./include/infopage.php'); 
  } 
   
  // Make sure this is a new title.
  $sql = "SELECT title FROM softwares WHERE title='$title'";
  $test = mysql_query($sql);
  if(mysql_num_rows($test) >= "1") {
    $result = "The title you've entered already exists in the database. Please try again.";
	require_once('./include/infopage.php');
  }

  $sql = "INSERT INTO softwares (title, description, total) VALUES('$title', '$description', '$total')";
  $row = mysql_query($sql);

    if (mysql_affected_rows() == "1"){
      $result = "The data has been succesfully added to the database.";
    }
    else {
      $result = "Error: An unknown error has occured. If this problem continues please contact your administator.";
    }
    require_once('./include/infopage.php');

} // Ends insert() function


function view_details(){
  global $CI;
  $title = $_GET['title'];
  $accesslevel = "1";
  $message = "software details viewed";
  AccessControl($accesslevel, $message); 
  
  $row = mysql_query("SELECT sid, title, description, total, inuse FROM softwares WHERE title='$title'");
  
  if(mysql_num_rows($row) != "1") {
    $result = "No software was found that matched your search. Please try again.";
	require_once('./include/infopage.php');
  }
  
  list($sid,$title,$description,$total,$inuse) = mysql_fetch_row($row);

  if($inuse > $total){
    $inuse = "<b>".$inuse."</b>";
  }
  ?>
  <div id="hardwaretip" style="display: none;" class="tip">You can enter the serial or asset number of a hardware asset to assign a software license to the hardware.</div>
  <h1>Details for <?php echo $title; ?>:</h1>
  <p style="text-align: right;"><a href="software.php?op=update&amp;title=<?php echo $title; ?>">
  <img src="./images/modify.gif" alt="" /> Update</a></p>
  <p><b>Description:</b><br />
  <?php echo $description; ?></p>
  <p><b>Total Licenses:</b><br /> <?php echo $total; ?></p>
  <p><b>Licenses in use:</b><br /> <?php echo $inuse; ?></p>
  <form action="software.php" method="get">
  <p>
  <input type="hidden" name="op" id="op" value="assign" />
  <input type="hidden" name="softwaresearch" id="softwaresearch" value="<?php echo $title; ?>" />
  <input type="hidden" name="returnto" id="returnto" value="software.php?op=show&title=<?php echo $title; ?>" />
  <b>Assign a software license:</b><br />
  <input id="hardwaresearch" name="hardwaresearch" type="text" size="15" /> 
  <a href="#" onclick="new Effect.toggle($('hardwaretip'),'appear')"><img src="./images/help.gif" alt="[?]" /></a></p>
  <div id="hardwaresearch_update" class="autocomplete"></div>
  <script type="text/javascript" charset="utf-8">
  // <![CDATA[
   new Ajax.Autocompleter('hardwaresearch','hardwaresearch_update','_hardware.php');
  // ]]>      
  </script>
  <p><input type="submit" value=" Go " /></p>
  </form>
  
  <p><b>Find all hardware this software is assigned to:</b> <a href="<?php echo "search.php?op=search&first=1&second=software&search=$title&when=current"; ?>">
  <img src="images/search.gif" alt="search" /></a></p>
<?php
  require_once('./include/footer.php');
} // Ends view_details function




function list_softwares(){
  global $CI;
  $accesslevel = "1";
  $message = "software list viewed";
  AccessControl($accesslevel, $message); 
  
  if(isset($_GET['sort'])) {
    $sort = clean($_GET['sort']);
  }
  else {
  $sort = "title";
  }

  $limit = "25";    // This is the number of rows per page to be displayed. 
  $query_count   = "SELECT COUNT(*) FROM softwares";
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
  $sql  = "SELECT title, total, inuse FROM softwares ORDER BY $sort LIMIT $lowerlimit, $limit";

  $row = mysql_query($sql); 

  if($totalrows < "1"){
    $result = "No software titles were found in the database. Please click \"Add Software\" on the left to add software titles.";
    require_once('./include/infopage.php');
    exit();
  }

  echo "<h1>All Software Titles</h1><br />\n";

  echo "<table width=\"100%\">\n".
       "<tr><th align=\"left\"><a href=\"software.php?sort=title\">Title</a></th>".
       "<th align=\"left\"><a href=\"software.php?sort=inuse\">Licenses in Use</a></th>".
       "<th align=\"left\"><a href=\"software.php?sort=total\">Total Licenses</a></th></tr>\n".
       "<tr><td colspan=\"4\"><hr class=\"head\" /></td></tr>";
    
    while(list($title,$total,$inuse) = mysql_fetch_row($row)){
	  
	  if($inuse > $total){
	    $inuse = "<b>".$inuse."</b>";
	  }
	  
      echo "<tr><td><a href=\"software.php?op=show&amp;title=$title\">$title</a></td><td>$inuse</td>".
	       "<td>$total</td><td>";
	  if($CI['user']['accesslevel'] >= "3"){
	    echo "<a href=\"software.php?op=delete&amp;title=$title\">".
		     "<img src=\"images/remove.gif\" alt=\"X\" /></a>";
	  }
      echo "</td></tr>\n".
	       "<tr><td colspan=\"4\"><hr class=\"division\" /></td></tr>";
    }
    echo("</table>");
  
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
} // Ends list_softwares function

?>

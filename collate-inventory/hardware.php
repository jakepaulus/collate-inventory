<?php
/**
 * This script contains functionality that will be used by every single page that is displayed.
 * It builds the CI array, creates the connection to the db that will be used by the rest of the
 * script, populates $CI['settings'] with settings from the db, and runs Access Control for the
 * program. It also contains any function that is common to more than two scripts (where the 
 * function is identical...such as clean().)
 */
require_once('./include/common.php');


$op = $_GET['op'];

switch($op){
	
	case "add";
	add_hardware();
	break;
	
	case "new";
	process_new_hardware();
	break;

	case "retire";
	retire_hardware();
	break;
	
	case "reassign";
	reassign_hardware(); 
	break;
	
	case "update";
	update_hardware();
	break;
	
	case "show";
	view_details();
	break;
	
	default:
	list_hardware();
	break;
}


function convertIntToAlphabet($int_wert) { // Used as part of generating asset numbers
  if($int_wert%27>=1) {
    $alpha_string=chr(($int_wert%27)+64).$alpha_string;
    $alpha_string=convertIntToAlphabet($int_wert/27).$alpha_string;
  }
  return $alpha_string;
}

function add_hardware(){
  global $CI;
  AccessControl('3');
  
  require_once('./include/header.php');
  // Display new-software form that posts to software_process.php 
  ?>
  <div id="tip" class="tip" style="display:none;"><i>This number is being generated automatically. You may remove it and type another if you wish as long as it is unique.</i><br /></div>
  <div class="tip" id="assigntip" style="display:none;"><i>You may optionally specify a username to assign this hardware to. Don't forget to allocate software licenses to this hardware once it is assigned.</i><br /></div>
  
  <h1>Add Hardware To Your Inventory:</h1>
  <br />
  <form id="new_hardware" action="hardware.php?op=new" method="post">
    <p>Hardware type:<br />
    <input id="category" name="category" type="text" size="30" /></p>
    <div id="category_update" class="autocomplete"></div>
      <script type="text/javascript" charset="utf-8">
      // <![CDATA[
        new Ajax.Autocompleter('category','category_update','_category.php');
      // ]]>      
     </script>
    <p>Asset Number: <br />
    
<?php
if($CI['settings']['autoasset'] == "1") { 
  $sql = 'SELECT MAX(hid) FROM hardwares';
  $result = mysql_query($sql);
  $max_hid = mysql_result($result, 0);
  $alpha = convertIntToAlphabet(date('m')); // script.aculo.us's autocomplete doesn't like strings that start with a number.
  
  $asset = $alpha.$max_hid."Y".date('y');
?>
    <input id="asset" name="asset" type="text" value="<?php echo $asset; ?>" size="30" /><a href="#" onclick="new Effect.toggle($('tip'),'appear')"><img src="./images/help.png" alt="[?]" /></a></p>
<?php
}
else { ?>
    <input id="asset" name="asset" type="text" size="30" /></p>
<?php } ?>
    <p>Serial Number:<br />
    <input id="serial" name="serial" type="text" size="30" /></p>
    <p>Description:<br />
    <textarea id="description" name="description" rows="4" cols="40"></textarea></p>
   <p>Assign Hardware: (optional)<br />
    <input id="hardwareassignment" name="hardwareassignment" type="text" size="15" /> <a href="#" onclick="new Effect.toggle($('assigntip'),'appear')"><img src="./images/help.png" alt="[?]" /></a></p>
    <div id="hardwareassignment_update" class="autocomplete"></div>
      <script type="text/javascript" charset="utf-8">
      // <![CDATA[
        new Ajax.Autocompleter('hardwareassignment','hardwareassignment_update','_users.php');
      // ]]>      
     </script>    
    <p><input type="submit" value=" Go " /></p>
  </form>
  
<?php
  require_once('./include/footer.php');
} // This ends the add_hardware function


function process_new_hardware() {
  global $CI;
  AccessControl("3"); 
  require_once('./include/header.php');
  
  $category = clean($_POST['category']);
  $asset = clean($_POST['asset']);
  $serial = clean($_POST['serial']);
  $description = nl2br(clean($_POST['description']));
  $username = clean($_POST['hardwareassignment']);
  
  if(strlen($category) < "3" || strlen($asset) < "3" || strlen($serial) < "3" || strlen($description) < "3") {
    $result = "All fields except for the Assign Hardware field are required. Please go back and try ".
	          "again.";
	require_once('./include/infopage.php');
  }
  
  if($asset == $serial){
    $result = "The asset and serial numbers must be collectively unique. Please use a different asset number.";
	require_once('./include/infopage.php');
  }
  
  // Make sure asset and serial numbers are collectively unique for the new hardware
  $sql = "SELECT asset, serial FROM hardwares WHERE asset='$asset' OR asset='$serial' OR serial='$serial' OR serial='$asset'";
  $test = mysql_query($sql);
  if(mysql_num_rows($test) != "0"){
    $result = "The asset and serial number must be collectively unique throughout the database.".
	          "The asset or serial number you entered already exists in one of these fields in the".
			  "database. Please use a different asset number or verify that the asset you're trying".
			  "to add doesn't already exist.<br />";
	require_once('./include/infopage.php');
  }
  
  $sql = "INSERT INTO hardwares (category, asset, serial, description, username) ". 
          "VALUES('$category', '$asset', '$serial', '$description', 'system')";
  mysql_query($sql);
  
  $sql = "INSERT INTO hardware (username, hid, site, codate) ".
         "VALUES('system', (SELECT hid FROM hardwares WHERE asset='$asset'), '', NOW())";
  mysql_query($sql);
  
  $result = "The hardware has successfully been added to the database.<br />";
  
  // If they entered a username, we'll assign the hardware.
  if(strlen($username) > "3"){
    $sql = "SELECT username FROM users WHERE username='$username'";
	$test = mysql_query($sql);
	if(mysql_num_rows($test) != "1") {
	  $result .= "The username you entered is not valid. This hardware is still assigned to the ".
	             "\"system\" user.<br />";
	}
	else {
	  $sql = "UPDATE hardwares SET username='$username' WHERE asset='$asset'";
	  mysql_query($sql);
	  
	  $sql = "UPDATE hardware SET cidate=NOW() WHERE hid=(SELECT hid FROM hardwares WHERE asset='$asset') AND cidate='0000-00-00 00:00:00'";
	  mysql_query($sql);
	  
	  $sql = "INSERT INTO hardware (username, hid, site, codate) ".
         "VALUES('$username', (SELECT hid FROM hardwares WHERE asset='$asset'), ".
		 "(SELECT site FROM users WHERE username='$username'), NOW())";
	  mysql_query($sql);
	  
	  $result .= "The hardware has been assigned to $username.<br />";
	}
  }  
  require_once('./include/infopage.php');
}


function retire_hardware(){
  global $CI;
  AccessControl('3');
  require_once('./include/header.php');
  
  $search = clean($_GET['hardwaresearch']);
  

  // This part is down outside of the lower if condition so that the $hid variable can be used to match for software assignments
  $sql = "SELECT hid, category, asset, serial, description FROM hardwares WHERE asset='$search' OR serial='$search'";
  $row = mysql_query($sql);
  list($hid,$category,$asset,$serial,$description) = mysql_fetch_row($row);
   
  if(mysql_num_rows($row) != "1") {
    $result = "You entered \"$search\" to be retired. There is not exactly one asset in the database that matches this value. \n".
                 "This can happen when you entered the serial number or asset number incorrectly (there are zero matches) or \n".
	         "when more than one asset has the same asset or serial number (which must be collectively unique.)\n";
    require_once('./include/infopage.php');
  }
    
  if($_GET['confirm'] != "yes") { // Show confirmation message or error 
    // This is a valid asset to be retired.
    echo "<h1>Retire Asset: $asset:</h1><br />".
         "<p><b>Description:</b><br />".
         "$description</p>".
         "<p><b>Other Details:</b><br />".
         "Serial Number: $serial</p>".
         "<b>Are you sure you would like to retire this asset?</b> This action will perminantly retire \n".
		 "this hardware. The records for this hardware will still be viewable using the search feature. \n".
	     "<br /><br /><a href=\"hardware.php?op=retire&amp;hardwaresearch=$search&amp;confirm=yes\">".
	     "<img src=\"./images/apply.png\" alt=\"confirm\" /></a> &nbsp; <a href=\"hardware.php?op=show&amp;search=$search\">".
	     "<img src=\"./images/cancel.png\" alt=\"cancel\" /></a>";
      require_once('./include/footer.php');
      exit();
  }

  // They've confirmed they would like this hardware retired.

  $sql = "UPDATE hardware SET cidate=NOW() WHERE asset='$search' OR serial='$search'";
  mysql_query($sql);
  if(mysq_affected_rows() == "1") {
    $result = "The hardware was successfully checked in.<br />";
  }
  else {
    $result = "The hardware was NOT successfully checked in!<br />";
  }

  $sql = "UPDATE software SET hid=NULL WHERE hid='$hid'";
  mysql_query($sql);
  if(mysq_affected_rows() > "0") {
    $result .= "Software was successfully returned to inventory.<br />";
  }
  else {
    $result .= "There appears to be no software to return to inventory for this hardware.<br />";
  }
  

  $sql = "UPDATE hardwares SET username=NULL WHERE asset='$search' OR serial='$search'";
  mysql_query($sql);
  if(mysq_affected_rows() == "1") {
    $result .= "The hardware was successfully unassigned.<br />";
  }
  else {
    $result .= "The hardware was NOT successfully unassigned!<br />";
  }
  
  require_once('./include/infopage.php');

} // This ends the manage_hardware function


function reassign_hardware() {
  global $CI;
  AccessControl('3');
  require_once('./include/header.php');
  
  $username = clean($_GET['username']);
  $search = clean($_GET['hardwaresearch']);
  
  if(strlen($username) < "3") {
    $result = "The username you have entered is not valid. Please try again.";
	require_once('./include/infopage.php');
  }
  
  $sql = "SELECT asset, serial, description, username FROM hardwares WHERE asset='$search' OR serial='$search'";
  $row = mysql_query($sql);
  
  if(mysql_num_rows($row) != "1"){
    $result = "The asset or serial number you entered did not return exactly one result. Please go back and try \n".
	          "again. If you feel you have reached this page in error, please contact your administrator.";
	require_once('./include/infopage.php');
  }

  $sql = "UPDATE hardwares SET username='$username' WHERE asset='$search' OR serial='$search'";
  mysql_query($sql);
  if(mysql_affected_rows() != "1"){
    $result = "There was a problem changing the assigned username for this hardware. If this problem persists, please \n".
	          "contact your administrator.";
	require_once('./include/infopage.php');
  }
	
  $sql = "UPDATE hardware SET cidate=NOW() WHERE hid=(SELECT hid FROM hardwares WHERE asset='$search' OR serial='$search') ".
         "AND cidate='0000-00-00 00:00:00'";
  mysql_query($sql); 
  if(mysql_affected_rows() != "1"){
    $result = "There was a problem setting the return date for the old user on this hardware. If this problem persists, please \n".
	          "contact your administrator.";
	require_once('./include/infopage.php');
  }
	
  $sql = "INSERT INTO hardware (username, hid, site, codate) ".
         "VALUES('$username', (SELECT hid FROM hardwares WHERE asset='$search' OR serial='$search'), ".
	     "(SELECT site FROM users WHERE username='$username'), NOW())";
  mysql_query($sql);
  if(mysql_affected_rows() != "1"){
    $result = "There was a problem inserting a new checkout record for this hardware. If this problem persists, please \n".
	          "contact your administrator.";
	require_once('./include/infopage.php');
  }
  // Everything should have worked find if we've gotten this far.
  if(!empty($_GET['search'])){
    $part2 = "&search=".clean($_GET['search']);
  }
  if(!empty($_GET['username'])){
    $part2 = "&usersearch=".clean($_GET['username']);
  }
  $goto = clean($_GET['returnto']).$part2;
  header("Location: $goto");

} // Ends reassign_hardware function

function update_hardware(){
  global $CI;
  AccessControl('3');
  require_once('./include/header.php');
  
  $search = clean($_GET['hardwaresearch']);
  
  $sql = "SELECT category, asset, serial, description FROM hardwares WHERE asset='$search'";
  $row = mysql_query($sql);
  
  if(mysql_num_rows($row) != "1") {
    $result = "There is no asset in the database that matches the one you are trying to modify. Please use the \n".
	          "buttons provided to modify hardware details.";
	require_once('./include/infopage.php');
  }
  
  if($_GET['action'] != "update") {
    list($category,$asset,$serial,$description) = mysql_fetch_row($row);
  
    echo "<h1>Update Description for Asset: $asset:</h1> \n".
         "<form action=\"hardware.php?op=update&amp;hardwaresearch=$search&amp;action=update\" method=\"post\"> \n".
	     "<p>Description:<br /> \n".
	     "<textarea id=\"description\" name=\"description\" rows=\"4\" cols=\"40\">".
		 preg_replace('/<br\\s*?\/??>/i', '', $description)."</textarea></p> \n".
	     "<p><input type=\"submit\" value=\" Go \" /></p> \n".
	     "</form>";
	
      require_once('./include/footer.php');
      exit();	
  }
  
  $description = nl2br(clean($_POST['description']));
  
  $sql = "UPDATE hardwares SET description='$description' WHERE asset='$search'";
  mysql_query($sql);
  
  // If we've gotten this far, everything should have worked fine. 
  header("Location: hardware.php?op=show&search=$search");
  
  
}


function view_details(){
  global $CI;
  AccessControl("1"); // The access level required for this function is 1. Please see the documentation for this function in common.php.
 
  include_once('./include/header.php'); // This has to be included after AccessControl in case it gets used by the error generator.
  
  $search = $_GET['search'];
  $row = mysql_query("SELECT hid, category, asset, serial, description, username FROM hardwares WHERE asset='$search' OR serial='$search'");

  if(mysql_num_rows($row) != "1") {
    $result = "No hardware was found in the database that matched the asset or serial number you have entered. Please try again.";
	require_once('./include/infopage.php');
  }
  
  list($hid,$category,$asset,$serial,$description,$username) = mysql_fetch_row($row);
  echo "<div class=\"tip\" id=\"assignhardwaretip\" style=\"display: none;\">You may specify a username to ".
       "assign this hardware to. Don't forget to allocate software licenses to this hardware once it is assigned.<br /></div>".
	   "<div class=\"tip\" id=\"assignsoftwaretip\" style=\"display: none;\">You may specify a software title to ".
       "assign to this hardware. <br /></div>".
       "<h1>Details for Asset: $asset:</h1>".
       "<table width=\"100%\"><tr><td align=\"left\">";
	  
  if($username === NULL) {
    echo "<b>This hardware is retired</b>";
  }
	  
  echo "</td><td align=\"right\">".
	   "<a href=\"hardware.php?op=update&amp;hardwaresearch=$asset\"><img src=\"./images/modify.png\" alt=\"update\" />".
       "Update Description</a></td></tr></table>".
       "<p><b>Description:</b><br />".
       "$description</p>".
       "<p><b>Other Details:</b><br />".
       "Serial Number: $serial</p>";


  if($CI['settings']['checklevel3perms'] == "0" || $CI['user']['accesslevel'] == "3") { ?>
  <div style="float: left; width: 45%;">
  <form action="hardware.php" method="get">  
  <p><b>Re-assign Hardware:</b><br />
	<input name="op" value="reassign" type="hidden" />
	<input name="hardwaresearch" type="hidden" value="<?php echo $asset; ?>" />
	<input name="returnto" type="hidden" value="hardware.php?op=show&search=<?php echo $asset; ?>" />
    <input id="username" name="username" type="text" size="15" /> 
	<a href="#" onclick="new Effect.toggle($('assignhardwaretip'),'appear')"><img src="./images/help.png" alt="[?]" /></a></p>
    <div id="username_update" class="autocomplete"></div>
      <script type="text/javascript" charset="utf-8">
      // <![CDATA[
        new Ajax.Autocompleter('username','username_update','_users.php');
      // ]]>      
     </script>    
    <p><input type="submit" value=" Go " /></p>
  </form>
  </div>
  <div style="float: left; width: 45%;">
  <form action="software.php" method="get">
  <p><input type="hidden" id="op" name="op" value="assign" />
  <input type="hidden" name="hardwaresearch" value="<?php echo $asset; ?>" />
  <input type="hidden" name="returnto" value="hardware.php?op=show&search=<?php echo $asset; ?>" />
  <b>Assign a Software License:</b><br />
  <input id="softwaresearch" name="softwaresearch" type="text" size="15" />
  <a href="#" onclick="new Effect.toggle($('assignsoftwaretip'),'appear')"><img src="./images/help.png" alt="[?]" /></a></p>
  <div id="softwaresearch_update" class="autocomplete"></div>
      <script type="text/javascript" charset="utf-8">
      // <![CDATA[
        new Ajax.Autocompleter('softwaresearch','softwaresearch_update','_software.php');
      // ]]>      
     </script>   
  <p style="clear: left;"><input type="submit" value=" Go " /></p>
  </form>
  </div>
  
  
  
  <h1>Assigned Software:</h1>
  <?php
  }
  $sql = "SELECT title, codate FROM software WHERE hid='$hid' AND cidate='0000-00-00 00:00:00'";
  $row = mysql_query($sql);
  if(mysql_num_rows($row) < "1") {
    echo "<p>No software is assigned to this hardware.</p>";
  }
  else {
  echo "<table width=\"70%\">".
       "<tr><th>Title</th><th>Check-out Date</th></tr>";
	   
  while(list($title,$codate) = mysql_fetch_row($row)) {
    echo "<tr><td><a href=\"software.php?op=show&amp;title=$title\">$title</a></td><td>$codate</td>";
	if($CI['settings']['checklevel3perms'] == "0" || $CI['user']['accesslevel'] == "3") {
	  echo "<td><a href=\"./software.php?op=release&amp;title=$title&amp;hardware=$asset\"><img src=\"./images/remove.png\" alt=\"X\" /></a></td>";
	}
	echo "</tr>";
  }
  echo "</table><br />";
  }

 
  

  // Display the history of this hardware
  echo "<h1>History</h1>".
	 "<table width=\"100%\"><tr><th>Username</th><th>Location</th><th>Date Out</th><th>Date In</th></tr>";
	   
  $row = mysql_query("SELECT username, site, codate, cidate FROM hardware WHERE hid='$hid' ORDER BY codate DESC");	   
  while(list($username,$location,$codate,$cidate) = mysql_fetch_row($row)){
    echo "<tr><td><a href=\"users.php?op=show&amp;usersearch=$username\">$username</a></td><td>$location</td><td>$codate</td><td>$cidate</td></tr>";
  }
  echo "</table>";
  require_once('./include/footer.php');
    
} // Ends view_details function

function list_hardware(){
  global $CI;
  AccessControl("1"); // The Access Level for this function is 1. Please see the documentation in common.php.
  
  require_once('./include/header.php'); // This has to be included after AccessControl in case it gets used by the error generator.

  if($_GET['sort']) { // Determinte what to the list by.
    $sort = $_GET['sort'];
  }
  else {
    $sort = "category";
  }
  
  $limit = "25";
  $sql = "SELECT COUNT(*) FROM hardwares"; // Determine the number of pages
  $result_count = mysql_query($sql);
  $totalrows = mysql_result($result_count, 0, 0);
  $numofpages = ceil($totalrows/$limit);
  
  if(empty($_GET['page'])) { 
    $page = "1";
  }
  else {
    $page = $_GET['page']; 
  }
  
  $lowerlimit = $page * $limit - $limit;
  if($_GET['view'] == "all") { // for show all, we really don't want to paginate, but we can still use this function
    $sql = "SELECT hid, category, asset, serial FROM hardwares ORDER BY $sort ASC";
  }
  else {
    $sql = "SELECT hid, category, asset, serial, username FROM hardwares WHERE username IS NOT NULL ORDER BY $sort LIMIT $lowerlimit, $limit"; 
  }
  $row = mysql_query($sql);
  
  if(mysql_num_rows($row) == "0") {
    $result = "No database records were found. Please add records using the \"Add..\" links to the left.";
    require_once('./include/infopage.php');
    exit();
  }
  else { 
    echo "<h1>All Hardware Assets</h1>\n";
    $bgcolor = "#E0E0E0"; // light gray
    echo "<table width=\"100%\">\n". // Here we actually build the HTML table
           "<tr><td align=\"right\" colspan=\"5\"><a href=\"hardware.php?op=add\"><img src=\"./images/add.png\" alt=\"add\" /> Add Hardware</a></td></tr>".
	   "<tr><td>&nbsp;</td></tr>".
	   "<tr><th align=\"left\"><a href=\"hardware.php?sort=category&amp;page=$page\">Category</a></th>".
	   "<th align=\"left\"><a href=\"hardware.php?sort=asset&amp;page=$page\">Asset Number</a></th>".
	   "<th align=\"left\"><a href=\"hardware.php?sort=serial&amp;page=$page\">Serial Number</a></th>".
	   "<th align=\"left\"><a href=\"hardware.php?sort=username&amp;page=$page\">Issued to</a></th></tr>\n".
	   "<tr><td colspan=\"5\"><hr class=\"head\" /></td></tr>\n";
    
    while(list($hid,$category,$asset,$serial,$username) = mysql_fetch_row($row)) { 

      echo "<tr><td>$category</td><td><a href=\"hardware.php?op=show&amp;search=$asset\">$asset</a></td>".
	     "<td><a href=\"hardware.php?op=show&amp;search=$serial\">$serial</a></td><td>$username</td><td>";
      if($CI['settings']['checklevel3perms'] == "0" || $CI['user']['accesslevel'] > "2") { 
        echo "<a href=\"./hardware.php?op=retire&amp;hardwaresearch=$asset\"><img src=\"./images/remove.png\" alt=\"X\" /></a>";
      }
      echo "</td></tr><tr><td colspan=\"5\"><hr class=\"division\" /></td></tr>\n";
    }
    echo "</table>"; // Here the HTML table ends. Below we're just building the Prev [page numbers] Next links.
  }
    
    if(($_GET['show'] != "all") && ($numofpages > "1")) {
      if($page != "1") { // Generate Prev link only if previous pages exist.
        $pageprev = $page - "1";
	echo "<a href=\"hardware.php?sort=$sort&amp;page=$pageprev\"> Prev</a>";
      }
      $i = "1";
      while($i < $page) { // Build all page number links up to the current page
        echo "<a href=\"hardware.php?sort=$sort&amp;page=$i\">$i</a>";
	$i++;
      }
      echo "[$page]"; // List Current page
      $i = $page + "1"; // Now we'll build all the page numbers after the current page if they exist.
      while(($numofpages-$page > "0") && ($i < $numofpages + "1")) {
        echo "<a href=\"hardware.php?sort=$sort&amp;page=$i\"> $i </a>";
        $i++;
      }
      if($page < $numofpages) { // Generate Next link if there is a page after this one
        $nextpage = $page + "1";
	echo "<a href=\"hardware.php?op=sort=$sort&amp;page=$nextpage\"> Next </a>";
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
    echo "<a href=\"".$_SERVER['REQUEST_URI']."&amp;view=all\">Show all results on one page</a>";
    }
  require_once('./include/footer.php');
} // Ends list_hardwares function







?>

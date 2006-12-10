<?php
/**
 * This script contains functionality that will be used by every single page that is displayed.
 * It builds the CI array, creates the connection to the db that will be used by the rest of the
 * script, populates $CI['settings'] with settings from the db, and runs Access Control for the
 * program. It also contains any function that is common to more than two scripts (where the 
 * function is identical...such as clean().)
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
    $alpha_string=chr(($int_wert%27)+64);
  }
  return $alpha_string;
}

function add_hardware(){
  global $CI;
  $accesslevel = "3";
  $message = "add hardware form accessed";
  AccessControl($accesslevel, $message); 
  
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
    <input id="asset" name="asset" type="text" value="<?php echo $asset; ?>" size="30" /><a href="#" onclick="new Effect.toggle($('tip'),'appear')"><img src="./images/help.gif" alt="[?]" /></a></p>
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
    <input id="hardwareassignment" name="hardwareassignment" type="text" size="15" /> <a href="#" onclick="new Effect.toggle($('assigntip'),'appear')"><img src="./images/help.gif" alt="[?]" /></a></p>
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
  $asset = clean($_POST['asset']);
  $accesslevel = "3";
  $message = "new hardware added. asset: $asset";
  AccessControl($accesslevel, $message); 
  
  $category = clean($_POST['category']);
  
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
  $search = clean($_GET['hardwaresearch']);
  $accesslevel = "3";
  $message = "hardware retired: $search";
  AccessControl($accesslevel, $message); 
  

  // This part is down outside of the lower if condition so that the $hid variable can be used to match for software assignments
  $sql = "SELECT hid, category, asset, serial, description, username FROM hardwares WHERE asset='$search' OR serial='$search'";
  $row = mysql_query($sql);
  list($hid,$category,$asset,$serial,$description,$username) = mysql_fetch_row($row);
   
  if(mysql_num_rows($row) != "1") {
    $result = "You entered \"$search\" to be retired. There is not exactly one asset in the database that matches this value. \n".
                 "This can happen when you entered the serial number or asset number incorrectly (there are zero matches) or \n".
	         "when more than one asset has the same asset or serial number (which must be collectively unique.)\n";
    require_once('./include/infopage.php');
  }
  
  $sql = "SELECT title FROM software WHERE hid='$hid' AND cidate='0000-00-00 00:00:00'";
  $row = mysql_query($sql);
  if(mysql_num_rows($row) >= "1"){
    $result = "You cannot retire this hardware because there is software still assigned to it. Please click ".
	          "<a href=\"hardware.php?op=show&amp;search=$search\"> here</a> to view the details for this hardware.";
	require_once('./include/infopage.php');
  }
    
  if($_GET['confirm'] != "yes" && $username != NULL) { // Show confirmation message or error 
    // This is a valid asset to be retired.
    echo "<h1>Retire Asset: $asset:</h1><br />".
         "<p><b>Description:</b><br />".
         "$description</p>".
         "<p><b>Other Details:</b><br />".
         "Serial Number: $serial</p>".
         "<b>Are you sure you would like to retire this asset?</b> This action will remove the hardware from \n".
		 "the list all page. The hardware will also no longer show as assigned to any particular user. The records ".
		 "for this hardware will still be viewable using the search feature. \n".
	     "<br /><br /><a href=\"hardware.php?op=retire&amp;hardwaresearch=$search&amp;confirm=yes\">".
	     "<img src=\"./images/apply.gif\" alt=\"confirm\" /></a> &nbsp; <a href=\"hardware.php?op=show&amp;search=$search\">".
	     "<img src=\"./images/cancel.gif\" alt=\"cancel\" /></a>";
      require_once('./include/footer.php');
      exit();
  }
  elseif($_GET['confirm'] != "yes" && $username === NULL){
    // This is a valid asset to be reinstated
    echo "<h1>Reinstate Asset: $asset:</h1><br />".
         "<p><b>Description:</b><br />".
         "$description</p>".
         "<p><b>Other Details:</b><br />".
         "Serial Number: $serial</p>".
         "<b>Are you sure you would like to reinstate this asset?</b> This action will assign this hardware \n".
		 "back to the system user to make the hardware available for assignment again.\n".
	     "<br /><br /><a href=\"hardware.php?op=retire&amp;hardwaresearch=$search&amp;confirm=yes\">".
	     "<img src=\"./images/apply.gif\" alt=\"confirm\" /></a> &nbsp; <a href=\"hardware.php?op=show&amp;search=$search\">".
	     "<img src=\"./images/cancel.gif\" alt=\"cancel\" /></a>";
      require_once('./include/footer.php');
      exit();
  }


  // They've confirmed they would like this hardware retired.
  if($username != NULL){
    $sql = "UPDATE hardware SET cidate=NOW() WHERE hid='$hid'";
    mysql_query($sql);
 
    $sql = "UPDATE hardwares SET username=NULL WHERE asset='$search' OR serial='$search'";
    mysql_query($sql);
    
	header("Location: hardware.php?op=show&search=$search");
  }
  else {
    $sql = "INSERT INTO hardware (username, hid, codate) VALUES('system', '$hid', NOW())";
	mysql_query($sql);
	
	$sql = "UPDATE hardwares set username='system' WHERE hid='$hid'";
	mysql_query($sql);
	
    header("Location: hardware.php?op=show&search=$search");
  }
} // This ends the manage_hardware function


function reassign_hardware() {
  global $CI;
  $username = clean($_GET['username']);
  $search = clean($_GET['hardwaresearch']);
  $accesslevel = "3";
  $message = "hardware assigned: $search to: $username";
  AccessControl($accesslevel, $message); 
  
  $returnto = urldecode($_GET['returnto']);
  
  if(strlen($username) < "3") {
    $result = "The username you have entered is not valid. Please try again.";
	require_once('./include/infopage.php');
  }
  
  $sql = "SELECT asset, serial, description, username FROM hardwares WHERE asset='$search' OR serial='$search'";
  $row = mysql_query($sql);
  
  if(mysql_num_rows($row) != "1"){
    $notice = "The asset or serial number you entered did not return exactly one result. Please try try again.";
	header("Location: $returnto&notice=$notice");
	exit();
  }

  $sql = "UPDATE hardwares SET username='$username' WHERE asset='$search' OR serial='$search'";
  mysql_query($sql);
  if(mysql_affected_rows() != "1"){
    $notice = "There was a problem changing the assigned username for this hardware. If this problem persists, please \n".
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
  $notice = "The hardware was sucessfully reassigned.";
  if(strlen($returnto) > "3"){
   header("Location: $returnto&notice=$notice");
  }
  else {
    header("Location: hardware.php");
  }

} // Ends reassign_hardware function

function update_hardware(){
  global $CI;
  $search = clean($_GET['hardwaresearch']);
  $accesslevel = "3";
  $message = "hardware updated: $search";
  AccessControl($accesslevel, $message); 
  if(isset($_GET['action'])) {
    $action = $_GET['action'];
  }
  else {
    $action = "show form";
  }
  
  $sql = "SELECT category, asset, serial, description FROM hardwares WHERE asset='$search'";
  $row = mysql_query($sql);
  
  if(mysql_num_rows($row) != "1") {
    $result = "There is no asset in the database that matches the one you are trying to modify. Please use the \n".
	          "buttons provided to modify hardware details.";
	require_once('./include/infopage.php');
  }
  
  if($action != "update") {
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
  $notice = "Description successfully updated";
  header("Location: hardware.php?op=show&search=$search&notice=$notice");
  
  
}


function view_details(){
  global $CI;
  $accesslevel = "1";
  $message = "hardware details viewed";
  AccessControl($accesslevel, $message); 
  
  $search = $_GET['search'];
  $returnto = urlencode($_SERVER['REQUEST_URI']);
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
       "<h1>Details for Asset: $asset:</h1>";
       
	  
  if($username == NULL) {
    echo "<table width=\"100%\"><tr><td align=\"left\">".
	     "<a href=\"./hardware.php?op=retire&amp;hardwaresearch=$asset\"><img src=\"./images/add.gif\" alt=\"+\" /> Reinstate this hardware</a>".
         "</td><td align=\"right\">".
	     "<b>This hardware is retired</b></td></tr></table>";
  }
  else{
    echo "<table width=\"100%\"><tr><td align=\"left\">".
	     "<a href=\"./hardware.php?op=retire&amp;hardwaresearch=$asset\"><img src=\"./images/remove.gif\" alt=\"X\" /> Retire this hardware</a>".
         "</td><td align=\"right\">".
	     "<a href=\"hardware.php?op=update&amp;hardwaresearch=$asset\"><img src=\"./images/modify.gif\" alt=\"update\" /> ".
         "Update Description</a></td></tr></table>";
	   
  } 
  echo  "<br /><p><b>Description:</b><br />".
        "$description</p>".
        "<p><b>Other Details:</b><br />".
        "Serial Number: $serial</p>";


  if(($CI['settings']['checklevel3perms'] == "0" || $CI['user']['accesslevel'] >= "3") && $username != NULL) { ?>
  <div style="float: left; width: 45%;">
  <form action="hardware.php" method="get">  
  <p><b>Re-assign Hardware:</b><br />
	<input name="op" value="reassign" type="hidden" />
	<input name="hardwaresearch" type="hidden" value="<?php echo $asset; ?>" />
	<input name="returnto" type="hidden" value="<?php echo $returnto ?>" />
    <input id="username" name="username" type="text" size="15" /> 
	<a href="#" onclick="new Effect.toggle($('assignhardwaretip'),'appear')"><img src="./images/help.gif" alt="[?]" /></a></p>
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
  <input type="hidden" name="returnto" value="<?php echo $returnto; ?>" />
  <b>Assign a Software License:</b><br />
  <input id="softwaresearch" name="softwaresearch" type="text" size="15" />
  <a href="#" onclick="new Effect.toggle($('assignsoftwaretip'),'appear')"><img src="./images/help.gif" alt="[?]" /></a></p>
  <div id="softwaresearch_update" class="autocomplete"></div>
      <script type="text/javascript" charset="utf-8">
      // <![CDATA[
        new Ajax.Autocompleter('softwaresearch','softwaresearch_update','_software.php');
      // ]]>      
     </script>   
  <p style="clear: left;"><input type="submit" value=" Go " /></p>
  </form>
  </div>
  <?php
  }
  
  if($username != NULL){
    echo "<h1>Assigned Software:</h1>";

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
	      echo "<td><a href=\"./software.php?op=release&amp;title=$title&amp;hardware=$asset\">".
	           "<img src=\"./images/remove.gif\" alt=\"X\" /></a></td>";
	    }
	    echo "</tr>";
      }
      echo "</table><br />";
    }
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
  $accesslevel = "1";
  $message = "hardware list viewed";
  AccessControl($accesslevel, $message); 

  if(isset($_GET['sort'])) { // Determinte what to the list by.
    $sort = $_GET['sort'];
  }
  else {
    $sort = "category";
  }
  
  $limit = "25";
  $sql = "SELECT hid FROM hardwares WHERE username IS NOT NULL"; // Determine the number of pages
  $result_count = mysql_query($sql);
  $totalrows = mysql_num_rows($result_count);
  if($totalrows < "1") {
    $result = "No active hardware was found. Please add records using the \"Add..\" links to the left.";
    require_once('./include/infopage.php');
  }
  
  $numofpages = ceil($totalrows/$limit);
  
  if(empty($_GET['page'])) { 
    $page = "1";
  }
  else {
    $page = $_GET['page']; 
  }
  
  $lowerlimit = $page * $limit - $limit;
  $sql = "SELECT hid, category, asset, serial, username FROM hardwares WHERE username IS NOT NULL ORDER BY $sort LIMIT $lowerlimit, $limit"; 
  $row = mysql_query($sql);
  

    echo "<h1>All Hardware Assets</h1>\n";
    $bgcolor = "#E0E0E0"; // light gray
    echo "<table width=\"100%\">\n". // Here we actually build the HTML table
           "<tr><td align=\"right\" colspan=\"5\"><a href=\"hardware.php?op=add\"><img src=\"./images/add.gif\" alt=\"add\" /> Add Hardware</a></td></tr>".
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
        echo "<a href=\"./hardware.php?op=retire&amp;hardwaresearch=$asset\"><img src=\"./images/remove.gif\" alt=\"X\" /></a>";
      }
      echo "</td></tr><tr><td colspan=\"5\"><hr class=\"division\" /></td></tr>\n";
    }
    echo "</table>"; // Here the HTML table ends. Below we're just building the Prev [page numbers] Next links.

    
  $goto = $_SERVER['REQUEST_URI'];
  if(stristr($_SERVER['REQUEST_URI'], "page")){
    $goto = preg_replace("{[&]*page=[0-9]*}", '', $goto); // Matches a string containing page=[zero or more numeric characters] and replaces with nothing
  }
  if(preg_match("/\?[a-zA-Z]/", $goto)){  // At this point there could be a ? mark, but it might not be followed by anything...in which case we don't want to append an &.
    $goto = $goto."&amp;";
  }
  elseif(!stristr($goto, "?")){
    $goto = $goto."?";
  }  
    
  if($numofpages > "1") {
    if($page != "1") { // Generate Prev link only if previous pages exist.
      $pageprev = $page - "1";
       echo "<a href=\"{$goto}page=$pageprev\"> Prev </a>";
    }
    
	if($numofpages < "10"){
	  $i = "1";
      while($i < $page) { // Build all page number links up to the current page
        echo "<a href=\"{$goto}page=$i\"> $i </a>";
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
          echo "<a href=\"{$goto}page=$i\"> $i </a>";
	    }
		$i++;
      }
	}
    echo "[$page]"; // List Current page
	
	if($numofpages < "10"){	
      $i = $page + "1"; // Now we'll build all the page numbers after the current page if they exist.
      while(($numofpages-$page > "0") && ($i < $numofpages + "1")) {
        echo "<a href=\"{$goto}page=$i\"> $i </a>";
        $i++;
      }
	}
	else{
	  $i = $page + "1";
	  $j = "1";
	  while(($numofpages-$page > "0") && ($i <= $numofpages) && ($j <= "3")) {
        echo "<a href=\"{$goto}page=$i\"> $i </a>";
        $i++;
		$j++;
      }
	  if($i <= $numofpages){
	    echo "...";
	  }
	}
    if($page < $numofpages) { // Generate Next link if there is a page after this one
      $nextpage = $page + "1";
	  echo "<a href=\"{$goto}page=$nextpage\"> Next </a>";
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
	
  require_once('./include/footer.php');
} // Ends list_hardwares function

?>

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
	add_user();
	break;
	
	case "new"; 
	process_new_user();
	break;
	
	case "update"; 
	update_user();
	break;
		
	case "delete";
	delete_user();
	break;
	
	case "show";
	view_details();
	break;

	default:
	list_users();
	break;
}

function view_details(){
  global $CI;
  AccessControl("1"); // The access level required for this function is 1. Please see the documentation for this function in common.php.
  require_once('./include/header.php'); // This has to be included after AccessControl in case it gets used by the error generator.

  
  $username = strtolower($_GET['usersearch']);
  

  $row = mysql_query("SELECT uid, username, accesslevel, phone, altphone, address, city, state, zip, site, email FROM users WHERE username='$username'");
  if(mysql_num_rows($row) != "1") { // This is not a valid username
    $result = "Please enter a valid username.";
    require_once('./include/infopage.php');
    exit();
  } 

  list($uid,$username,$accesslevel,$phone,$altphone, $address, $city,$state,$zip,$site,$email) = mysql_fetch_row($row);
    require_once('./include/header.php');
   
    if($username != "system") {
      echo "<div id=\"hardwaretip\" class=\"tip\" style=\"display: none;\">You may specify the asset or serial number ".
	       "of an asset to assign it to this user. Don't forget to allocate software licenses to this hardware once it is assigned.<br /></div>";
	  
      if($accesslevel === "0") {
	    $perms = "<b>no</b>";
      }
      if($accesslevel === "1") {
	    $perms = "<b>read-only</b>";
      }
      if($accesslevel === "3") {
	    $perms = "<b>read+write</b>";
      }
      if($accesslevel === "5") {
	    $perms = "<b>administrator</b>";
      }	  
	?>
	
<script type="text/javascript" charset="utf-8">
// <![CDATA[

  Effect.divSwap = function(element,container){
    var div = document.getElementById(container);
    var nodeList = div.childNodes;
    var queue = Effect.Queues.get('menuScope');

    if(queue.toArray().length<1){
        if(Element.visible(element)==false){
            for(i=0;i<nodeList.length;i++){
                if(nodeList.item(i).nodeName=="DIV" && nodeList.item(i).id!=element){
                    if(Element.visible(nodeList.item(i))==true){
                        Effect.BlindUp(nodeList.item(i), {duration: 0.1}, {queue:{position:'end',scope:'menuScope',limit:2}})
                    }
                }
            }
            Effect.BlindDown(element, {duration: 0.1}, {queue:{position:'end',scope:'menuScope',limit:2}}, {duration: 0.2})
       }
    }
  }
     // ]]>      
</script>
    <div id="container">
	  <div id="details">
	  <h1>Details for <?php echo $username; ?>:</h1>
      <table width="100%"><tr><td>
	  <p>This user has <?php echo $perms; ?> application permissions.</p>
      <p><b>Site:</b><br /><?php echo $site; ?></p>
      </td><td align="right" valign="top"><a href="#" onclick="Effect.divSwap('edit','container');">
      <img src="./images/modify.png" alt="modify" /> Update User Information</a></td></tr></table>
      <p><b>Address:</b><br />
      <?php echo $address; ?> <br /> <?php echo "$city, $state $zip"; ?></p>
      <p><b>Telephone Numbers:</b><br />
      Primary: <?php echo $phone; ?><br />Alternate: <?php echo $altphone; ?></p>
      <p><b>Email Address:</b><br />
      <a href="mailto:<?php echo "$email\">$email"; ?></a></p>
	 
	 <?php
      //Display hardware that belongs to the user:
     if(($CI['settings']['checklevel3perms'] == "0" || $CI['user']['accesslevel'] == "3") && $username != "system") { ?>
     <form id="assign_hardware" action="hardware.php" method="get">
     <p><b>Assign Hardware:</b><br />
    
	<input name="op" value="reassign" type="hidden" />
	<input name="username" value="<?php echo $username; ?>" type="hidden" />
	<input name="returnto" type="hidden" value="users.php?op=show&username=<?php echo $username; ?>" />
    <input id="hardwaresearch" name="hardwaresearch" type="text" size="15" /> <a href="#" onclick="new Effect.toggle($('hardwaretip'),'appear')"><img src="./images/help.png" alt="[?]" /></a></p>
    <div id="hardwaresearch_update" class="autocomplete"></div>
      <script type="text/javascript" charset="utf-8">
      // <![CDATA[
        new Ajax.Autocompleter('hardwaresearch','hardwaresearch_update','_hardware.php');
      // ]]>      
     </script>
      <p><input type="submit" value=" Go " /></p>
     </form>
	 
      <?php
      }
      echo "<h1>Currently Assigned Hardware:</h1>";
    }
    else {
      echo "<div><div><h1>System</h1>".
             "<p>System is a special account used to assign assets to that are considered \"In Inventory\"</p>".
             "<h1>Hardware in Inventory:</h1>";
    }
    $row = mysql_query("SELECT hid, category, asset, serial FROM hardwares WHERE username='$username' ORDER BY asset ASC");
    if(mysql_num_rows($row) < "1") {
      echo "<p>There is no hardware in the database assigned to this user.</p>";
    }
    else {
      echo "<table width=\"100%\">\n". 
             "<tr><th align=\"left\">Category</th><th align=\"left\">Asset</th><th align=\"left\">Serial</th></tr>\n";
  
      while(list($hid,$category,$asset,$serial) = mysql_fetch_row($row)) { 
      echo "<tr><td>$category</td><td><a href=\"hardware.php?op=show&amp;search=$asset\">$asset</a></td>".
             "<td><a href=\"hardware.php?op=show&amp;search=$serial\">$serial</a></td><td>";
      if(($CI['settings']['checklevel3perms'] == "0" || $CI['user']['accesslevel'] > "2") && $username != "system") { 
        echo "<a href=\"./hardware.php?op=reassign&amp;hardwaresearch=$asset&amp;username=system\"><img src=\"./images/remove.png\" alt=\"X\" /></a>"; 
      }
      echo "</td></tr>\n";
    }
    echo "</table>";
  }
  
  ?>
  </div>
	 <div style="display: none;" id="edit">
	   <div id="passwordtip" style="display: none;" class="tip">Setting a new password will allow this user to login with either their current password (in which case the temporary password would be removed) or the new password (which will prompt the user to reset their password.)</div>
	   <h1>Update <?php echo $username; ?>:</h1>
       <p style="text-align: right;"><a href="#" onclick="Effect.divSwap('details','container');">
	   <img src="./images/modify.png" alt="modify" /> Cancel Update</a></p>
	   
	   <form id="new_user" action="users.php?op=update&amp;username=<?php echo $username; ?>" method="post">

 <div style="float: left; width: 45%; border-right: 1px solid #000;">
      <p>Telephone Number:<br />
      <input id="phone" name="phone" type="text" value="<?php echo $phone; ?>" /></p>
      <p>Alt. Telephone Number: (optional)<br />
      <input id="altphone" name="altphone" type="text" value="<?php echo $altphone; ?>" /></p>    
      <p>Email Address: (optional)<br />
      <input id="email" name="email" type="text" value="<?php echo $email; ?>" /></p>
	  <?php if($CI['settings']['checklevel5perms'] === "0" || $CI['user']['accesslevel'] === "5") { ?>
	  <p>User's Permissions:<br />
	    <input type="radio" name="perms" value="0" <?php if($accesslevel === "0"){ echo "checked=\"checked\""; } ?> /> None<br />
        <input type="radio" name="perms" value="1" <?php if($accesslevel === "1"){ echo "checked=\"checked\""; } ?> />Read-Only<br />
        <input type="radio" name="perms" value="3" <?php if($accesslevel === "3"){ echo "checked=\"checked\""; } ?> />Read+Write<br />
	    <input type="radio" name="perms" value="5" <?php if($accesslevel === "5"){ echo "checked=\"checked\""; } ?> />Admin<br />
      </p>
	  <?php } 
	  if($CI['settings']['ldapauth'] === "0"){ ?>
		<p>Set a New Temporary Password:<br />
	    <input id="tmppasswd" name="tmppasswd" type="text" size="30" />
		<a href="#" onclick="new Effect.toggle($('passwordtip'),'appear')"><img src="./images/help.png" alt="[?]" /></a></p>
	    <?php } ?>
	</div>
    <div style="float: left; width: 45%; padding-left: 5px;">
	  <p>Please choose the location type:<br />
      <a href="#" onclick="Effect.divSwap('address_fields','swapable');"><img src="./images/home_small.png" alt="Remote" /></a> &nbsp; 
      <a href="#" onclick="Effect.divSwap('site','swapable');"><img src="./images/sites_small.png" alt="Site" /></a></p>
      <div id="swapable">
        <div id="site">
          <p>Site:<br />
          <input id="sitesearch" name="sitesearch" type="text" size="15" value="<?php echo $site; ?>" />
		  <a href="#" onclick="new Effect.toggle($('sitetip'),'appear')"><img src="./images/help.png" alt="[?]" /></a></p>
          <div id="sitesearch_update" class="autocomplete"></div>
          <script type="text/javascript" charset="utf-8">
            // <![CDATA[
              new Ajax.Autocompleter('sitesearch','sitesearch_update','_sites.php');
            // ]]>      
          </script>
         </div>
         <div id="address_fields" style="display:none;">
           <p>Street Address:<br />
           <textarea id="address" name="address" rows="2" cols="25"><?php echo preg_replace('/<br\\s*?\/??>/i', '',$address); ?></textarea></p>
           <p>City:<br />
           <input id="city" name="city" type="text" value="<?php echo $city; ?>" /></p>
           <p>State/Province:<br />
           <input id="state" name="state" type="text" value="<?php echo $state; ?>" /></p>
           <p>Postal Code:<br />
           <input id="zip" name="zip" type="text" value="<?php echo $zip; ?>" /></p>
        </div>
 	  </div>
	</div>
  <p style="clear: left;"><input type="submit" value=" Go " /></p>
  </form>
	 </div>
    </div>	 
  <?php  
  require_once('./include/footer.php');
} // Ends view_details function


function update_user() {
  global $CI;
  AccessControl("3"); // The Access Level for this function is 1. Please see the documentation in common.php.
  require_once('./include/header.php'); // This has to be included after AccessControl in case it gets used by the error generator.
  
  if(strlen($_POST['phone']) < "2") {
    $result = "You must include a contact number for the user. Please go back and try again.";
    require_once('./include/infopage.php');
    exit();
  }
  
  // First make sure the user submitting the form has permission to set user permissions for the new user
  // The defaults in the database table are safe if these variables are not set...so no harm done.
  if($CI['settings']['checklevel5perms'] === "0" || $CI['user']['accesslevel'] === "5"){
    $perms = clean($_POST['perms']);
	
	// We'll only set the temporary password if the new user's permissions and CI Settings require us to.
    if((($perms >= "1" && $CI['settings']['checklevel1perms'] === "1") ||
      ($perms >= "3" && $CI['settings']['checklevel3perms'] === "1") ||
	  ($perms >= "5" && $CI['settings']['checklevel5perms'] === "1")) &&
	  $CI['settings']['ldapauth'] === "0") {
	 
	  $tmppasswd = clean($_POST['tmppasswd']);
	}
  }
  
  // If they entered location information, we'll process the form. Otherwise, they get an error.
  if((strlen($_POST['address']) > "4" or 
      strlen($_POST['city']) > "3" or 
      strlen($_POST['state']) > "2" or 
      strlen($_POST['zip']) > "5") || 
      strlen($_POST['sitesearch']) > "2") { 

    $username = clean($_GET['username']);
    $tmppasswd = clean($_POST['tmppasswd']);
	$perms = clean($_POST['perms']);
	$phone = clean($_POST['phone']);
    $altphone = clean($_POST['altphone']);
    $address = nl2br(clean($_POST['address']));
    $city = clean($_POST['city']);
    $state = clean($_POST['state']);
    $zip = clean($_POST['zip']);
    $email = clean($_POST['email']);
    $site = clean($_POST['sitesearch']);
	
    if(strlen($site) > "2" && $site != "Remote") { // They entered a site name, we'll ignore address details if present and overwide with site information.
      $sql = "SELECT address, city, state, zip FROM sites WHERE name='$site'";
      $row = mysql_query($sql);
      if(mysql_num_rows($row) != "1") {
        $result = "The name of the site you provided is not valid. Please enter a valid site name or enter the full address for the user. ".
	              "You must clear the site field or leave \"Remote\" entered in order for the address fields to be processed.";
        require_once('./include/infopage.php');
      }
      list($address,$city,$state,$zip) = mysql_fetch_row($row);
    }
    else { // They entered no site information, we'll call their location "remote" so that the hardware has a location on the list hardware page.
      $site = "remote";
    }
	if($CI['user']['accesslevel'] === "5" || $CI['settings']['checklevel5perms'] === "0"){
      if(!empty($tmppasswd)){ 
        $sql = "UPDATE users SET tmppasswd='$tmppasswd', accesslevel='$perms', phone='$phone', altphone='$altphone',".
               "address='$address', city='$city', state='$state', zip='$zip', site='$site', email='$email' WHERE username='$username'";
	  }
      else { // We don't want to set a blank tmppasswd if one was already set...the user wont be able to login.
	    $sql = "UPDATE users SET accesslevel='$perms', phone='$phone', altphone='$altphone',address='$address', city='$city', ".
	           "state='$state', zip='$zip', site='$site', email='$email' WHERE username='$username'";
      }
	}
	else { // We don't want users updating permissions or passwords if they don't have permission.
	  $sql = "UPDATE users SET phone='$phone', altphone='$altphone',address='$address', city='$city', ".
	         "state='$state', zip='$zip', site='$site', email='$email' WHERE username='$username'";
	}
    mysql_query($sql);
	if(mysql_affected_rows === "1"){
      $result = "User information has been updated.<br />";
	}
	else {
	  $result = "An error has occured. User information has not been updated.";
	}
	
    // Now we need to update the hardware assigned to the user with new location details if the location has changed.
    $sql = "UPDATE hardware SET site='$site' WHERE username='$username' AND cidate='0000-00-00 00:00:00'";
    mysql_query($sql);
	if(mysql_affected_rows <= "1"){
	  $result .= "Hardware currently assigned to the user has had its location information updated.";
	}
	require_once('./include/infopage.php');
  }
  else { // No location details were entered
    $result = "You must specify details about the location this user will be at. You can enter the address or provide the name ".
	          "of the site the user works out of. Please go back and try again.";
    require_once('./include/infopage.php');
  }

} // Ends update_user function


function list_users(){
  global $CI;
  AccessControl("1"); // The Access Level for this function is 1. Please see the documentation in common.php.
  
  require_once('./include/header.php'); // This has to be included after AccessControl in case it gets used by the error generator.
  
  if($_GET['sort']) { // Determinte what to the list by.
    $sort = $_GET['sort'];
  }
  else {
    $sort = "username";
  }
  
  $limit = "7";
  $sql = "SELECT COUNT(*) FROM users WHERE uid > '0'"; // Determine the number of pages, > 0 keeps the system account out of the math.
  $result_count = mysql_query($sql);
  $totalrows = mysql_result($result_count, 0, 0);
  $numofpages = ceil($totalrows/$limit); 
  
  if(!isset($_GET['page'])) { 
    $page = "1";
  }
  else {
    $page = $_GET['page']; 
  }
  
  $lowerlimit = $page * $limit - $limit;
  if($lowerlimit < "1") {$lowerlimit = "1";} // This prevents the system account from showing up in the user list.
  
  if($_GET['view'] == "all") { // for print all, we really don't want to paginate, but we can still use this function
    $sql = "SELECT username, city, state, zip, site, email FROM users WHERE uid>='$lowerlimit' ORDER BY $sort ASC";
  }
  else {
    // this is MUCH faster than using a lower limit because the primary key is indexed.
    $sql = "SELECT username, city, state, zip, site, email FROM users WHERE uid>='$lowerlimit' ORDER BY $sort LIMIT $limit"; 
  }
  $row = mysql_query($sql);
  
  if($totalrows < "1") {
    $result = "No database records were found. Please add records using the \"Add..\" links to the left.";
    require_once('./include/infopage.php');
    exit();
  }
  else { 
    echo "<h1>All Users</h1>\n";
    $bgcolor = "#E0E0E0"; // light gray
    echo "<p style=\"text-align: right;\"><a href=\"users.php?op=add\"><img src=\"./images/add.png\" alt=\"Add\"/> Add a User </a></p>".
            "<table width=\"100%\">\n". // Here we actually build the HTML table	   
	    "<tr><th align=\"left\"><a href=\"users.phpsort=username\">Username</a></th>".
	   "<th align=\"left\"><a href=\"users.php?sort=city\">Site</a></th>".
	   "<th align=\"left\">Email Address</th></tr><tr><td colspan=\"3\"><hr class=\"head\" /></td></tr>\n";
    
    while(list($username,$city,$state,$zip,$site,$email) = mysql_fetch_row($row)) { 
      echo "<tr><td><b><a href=\"users.php?op=show&amp;usersearch=$username\">$username</a></b></td>".
             "<td colspan=\"2\" align=\"right\"><a href=\"./users.php?op=delete&amp;username=$username\">";
	     
      if(($CI['settings']['checklevel3perms'] == "0" || $CI['user']['accesslevel'] > "2") && $username != "system") {
        echo "<img src=\"./images/remove.png\" alt=\"remove\" /></a>";
      }
      
      echo "</td></tr><tr><td>$city, $state</td><td>$site</td>".
	     "<td><a href=\"mailto:$email\">$email</a></td></tr><tr><td colspan=\"3\"><hr class=\"division\" /></td></tr>\n";
    }
    echo "</table><br />"; // Here the HTML table ends. Below we're just building the Prev [page numbers] Next links.
    
    if(($_GET['show'] != "all") && ($numofpages > "1")) {
      if($page != "1") { // Generate Prev link only if previous pages exist.
        $pageprev = $page - "1";
	echo "<a href=\"users.php?page=$pageprev\"> Prev </a>";
      }
      $i = "1";
      while($i < $page) { // Build all page number links up to the current page
        echo "<a href=\"users.php?page=$i\">$i</a>";
	$i++;
      }
      echo "[$page]"; // List Current page
      $i = $page + "1"; // Now we'll build all the page numbers after the current page if they exist.
      while(($numofpages-$page > "0") && ($i < $numofpages + "1")) {
        echo "<a href=\"users.php?page=$i\"> $i </a>";
        $i++;
      }
      if($page < $numofpages) { // Generate Next link if there is a page after this one
        $nextpage = $page + "1";
	echo "<a href=\"users.php?page=$nextpage\"> Next </a>";
      }
    }
    
    // Regardless of how many pages there are, well show how many records there are and what records we're displaying.
	$upperlimit = $totalrows;
	
    if($lowerlimit == "0") { // The program is happy to start counting with 0, humans aren't.
      $lowerlimit = "1";
    }
    echo "<br />\n<br />\nShowing $lowerlimit - $upperlimit out of $totalrows<br />\n";
    if($_GET['show'] != "all" && $numofpages > "1") {
    echo "<a href=\"".$_SERVER['REQUEST_URI']."&amp;view=all\">Show all results on one page</a>";
    }
  } 
  require_once('./include/footer.php');  
} // Ends list_users function


function delete_user() {
  global $CI;
  AccessControl('3');
  require_once('./include/header.php'); // This has to be included after AccessControl in case it gets used by the error generator.
  
  $username = clean($_GET['username']);
  
  if($username == "system") {
    $result = "You cannot delete the system account.";
    require_once('./include/infopage.php');
  }
  
  if($_GET['confirm'] != "yes") { // draw the confirmation page or error
  
    // Check to see if there is any hardware assigned to this user..
    $sql = "SELECT * FROM hardwares WHERE username='$username'";
    $test = mysql_query($sql);
    if(mysql_num_rows($test) != "0") { // There are users at this site.
      $result = "The user \"$username\" currently has hardware assigned to them. This user cannot be deleted until the hardware is re-assigned to a different user. \n".
                   "Please click <a href=\"users.php?op=show&amp;usersearch=$username\">here</a> to see the hardware assigned to this user."; 
      require_once('./include/infopage.php');
    }
        
    // There aren't any users or hardware at the site, we'll just make sure the user is sure they want to delete the site.
    $sql = "SELECT username FROM users WHERE username='$username'";
    $row = mysql_query($sql);
    while(list($name) = mysql_fetch_row($row)) { // They are requesting deletion of a valid user
      $result = "Are you sure you'd like to delete the user \"$username\"?<br /><br />\n".
		   "<a href=\"users.php?op=delete&amp;username=$name&amp;confirm=yes\">".
		   "<img src=\"./images/apply.png\" alt=\"confirm\" /></a> &nbsp; <a href=\"users.php\"><img src=\"./images/cancel.png\" alt=\"cancel\" /></a>";
      require_once('./include/infopage.php');
    }
    $result = "There is no user in the database called \"$username\". Please go back and use the buttons ".
                 "provided to delete a user. If you believe you have reached this page in error, please notify ". $CI['adminname'];
    require_once('./include/infopage.php');
  }
  else { // delete the row, they are sure
    $sql = "DELETE FROM users WHERE username='$username'";
    $result = mysql_query($sql);
       
    if (mysql_affected_rows() == "1"){
      $result = "The user \"$username\" has been removed from the database.";
    }
    else {
      $result = "Something went wrong. I suspect you didn't click the confirm link but instead tried to edit the URL manually.";
    }
  }
  require_once('./include/infopage.php');
} // Ends delete_site function


function add_user(){
  global $CI;
  AccessControl('3'); // This access level of this script is 3. Please see the documentation in common.php.
  require_once('./include/header.php');
  ?>

 <script type="text/javascript" charset="utf-8">
 // <![CDATA[

  Effect.divSwap = function(element,container){
    var div = document.getElementById(container);
    var nodeList = div.childNodes;
    var queue = Effect.Queues.get('menuScope');

    if(queue.toArray().length<1){
        if(Element.visible(element)==false){
            for(i=0;i<nodeList.length;i++){
                if(nodeList.item(i).nodeName=="DIV" && nodeList.item(i).id!=element){
                    if(Element.visible(nodeList.item(i))==true){
                        Effect.BlindUp(nodeList.item(i), {duration: 0.2}, {queue:{position:'end',scope:'menuScope',limit:2}})
                    }
                }
            }
            Effect.BlindDown(element, {duration: 0.2}, {queue:{position:'end',scope:'menuScope',limit:2}}, {duration: 0.2})
       }
    }
  }
     // ]]>      
</script>
  <div id="hardwaretip" style="display: none;" class="tip">You may optionally specify the asset/serial number of an asset to assign it to this user. Don't forget to allocate software licenses to this hardware once it is assigned.<br /></div>
  <div id="sitetip" style="display: none;" class="tip">This field will autocomplete. If there is no site already listed for the one you'd like to associate this user with, please add the site using Manage Sites in the Control Panel.<br /></div>
  <div id="passwordtip" style="display: none;" class="tip">A temporary password must be set for this user to login for the first time. The user will 
  be prompted to change their password the first time they login.<br /></div>
  <h1>Add a user:</h1>
  <br />
  <form id="new_user" action="users.php?op=new" method="post">
 <div style="float: left; width: 45%; border-right: 1px solid #000;">
      <p>Username:<br />
      <input id="username" name="username" type="text"  /></p>
      <p>Telephone Number:<br />
      <input id="phone" name="phone" type="text"  /></p>
      <p>Alt. Telephone Number: (optional)<br />
      <input id="altphone" name="altphone" type="text"  /></p>    
      <p>Email Address: (optional)<br />
      <input id="email" name="email" type="text" /></p>
      <p>Assign Hardware: (optional)<br />
      <input id="hardwaresearch" name="hardwaresearch" type="text" size="15" /> 
	  <a href="#" onclick="new Effect.toggle($('hardwaretip'),'appear')"><img src="./images/help.png" alt="[?]" /></a></p>
      <div id="hardwaresearch_update" class="autocomplete"></div>
      <script type="text/javascript" charset="utf-8">
        // <![CDATA[
          new Ajax.Autocompleter('hardwaresearch','hardwaresearch_update','_hardware.php');
        // ]]>      
      </script>
	  <?php if($CI['settings']['checklevel5perms'] === "0" || $CI['user']['accesslevel'] === "5") { ?>
	  <p>User's Permissions:<br />
	  
	  <?php
	    $show = "onclick=\"new Effect.BlindDown('extraforms', {duration: 0.2})\"";
		$show1 = "onclick=\"new Effect.BlindUp('extraforms', {duration: 0.2})\"";
		$show3 = "onclick=\"new Effect.BlindUp('extraforms', {duration: 0.2})\"";
		$show5 = "onclick=\"new Effect.BlindUp('extraforms', {duration: 0.2})\"";
		
		if($CI['settings']['checklevel1perms'] === "1"){
		  $show1 = $show;
		}
		if($CI['settings']['checklevel3perms'] === "1"){
		  $show3 = $show;
		}
		if($CI['settings']['checklevel5perms'] === "1"){
		  $show5 = $show;
		}
	  ?>
	    <input type="radio" name="perms" onclick="new Effect.BlindUp('extraforms', {duration: 0.2})" value="0" checked="checked" /> None<br />
        <input type="radio" name="perms" <?php echo $show1; ?> value="1" />Read-Only<br />
        <input type="radio" name="perms" <?php echo $show3; ?> value="3" />Read+Write<br />
	    <input type="radio" name="perms" <?php echo $show5; ?> value="5" />Admin<br />
      </p>
	  <?php } ?>
	  <div id="extraforms" style="display: none;">   
	    <?php if($CI['settings']['ldapauth'] === "0"){ ?>
	    <p>Temporary Password:<br />
	    <input id="tmppasswd" name="tmppasswd" type="text" size="30" />
		<a href="#" onclick="new Effect.toggle($('passwordtip'),'appear')"><img src="./images/help.png" alt="[?]" /></a></p>
	    <?php } ?>
	  </div>
	</div>
    <div style="float: left; width: 45%; padding-left: 5px;">
	  <p>Please choose the location type:<br />
      <a href="#" onclick="Effect.divSwap('address_fields','swapable');"><img src="./images/home_small.png" alt="Remote" /></a> &nbsp; 
      <a href="#" onclick="Effect.divSwap('site','swapable');"><img src="./images/sites_small.png" alt="Site" /></a></p>
      <div id="swapable">
        <div id="site">
          <p>Site:<br />
          <input id="sitesearch" name="sitesearch" type="text" size="15" />
		  <a href="#" onclick="new Effect.toggle($('sitetip'),'appear')"><img src="./images/help.png" alt="[?]" /></a></p>
          <div id="sitesearch_update" class="autocomplete"></div>
          <script type="text/javascript" charset="utf-8">
            // <![CDATA[
              new Ajax.Autocompleter('sitesearch','sitesearch_update','_sites.php');
            // ]]>      
          </script>
         </div>
         <div id="address_fields" style="display:none;">
           <p>Street Address:<br />
           <textarea id="address" name="address" rows="2" cols="25"></textarea></p>
           <p>City:<br />
           <input id="city" name="city" type="text" /></p>
           <p>State/Province:<br />
           <input id="state" name="state" type="text" /></p>
           <p>Postal Code:<br />
           <input id="zip" name="zip" type="text" /></p>
        </div>
 	  </div>
	</div>
  <p style="clear: left;"><input type="submit" value=" Go " /></p>
  </form>
<?php
  require_once('./include/footer.php');
}





function process_new_user(){
/*
 * Step 1. Retrieve site information if necessary to add user to db with correct address and site info
 * Step 2. Add user to users table
 * Step 3. Add hardware record if applicable with correct site info from Step 1.
 */
  global $CI;
  AccessControl("3"); // The access level required for this function is 1. Please see the documentation for this function in common.php.
 
  include_once('./include/header.php'); // This has to be included after AccessControl in case it gets used by the error generator.    
  if (strlen($_POST['username']) < "4" ){ 
    $result = "The username must be four characters or longer. Please go back and try again."; 
    require_once('./include/infopage.php'); 
    exit();
  } 
  
  if(strlen($_POST['phone']) < "2") {
    $result = "You must include a contact number for the user. Please go back and try again.";
    require_once('./include/infopage.php');
    exit();
  }
  
  // First make sure the user submitting the form has permission to set user permissions for the new user
  // The defaults in the database table are safe if these variables are not set...so no harm done.
  if($CI['settings']['checklevel5perms'] === "0" || $CI['user']['accesslevel'] === "5"){
    $perms = clean($_POST['perms']);
	
	// We'll only set the temporary password if the new user's permissions and CI Settings require us to.
    if((($perms >= "1" && $CI['settings']['checklevel1perms'] === "1") ||
      ($perms >= "3" && $CI['settings']['checklevel3perms'] === "1") ||
	  ($perms >= "5" && $CI['settings']['checklevel5perms'] === "1")) &&
	  $CI['settings']['ldapauth'] === "0") {
	 
	  $tmppasswd = clean($_POST['tmppasswd']);
	}
  }
  
  // If they entered location information, we'll process the form. Otherwise, they get an error.
  if((strlen($_POST['address']) > "4" or 
      strlen($_POST['city']) > "3" or 
      strlen($_POST['state']) > "2" or 
      strlen($_POST['zip']) > "5") || 
      strlen($_POST['sitesearch']) > "2") { 

    $username = clean($_POST['username']);
    $phone = clean($_POST['phone']);
    $altphone = clean($_POST['altphone']);
    $address = nl2br(clean($_POST['address']));
    $city = clean($_POST['city']);
    $state = clean($_POST['state']);
    $zip = clean($_POST['zip']);
    $email = clean($_POST['email']);
    $site = clean($_POST['sitesearch']);
    $hardware = clean($_POST['hardwaresearch']);
  
    $test = mysql_query("SELECT uid FROM users WHERE username='$username'");
    if(mysql_num_rows($test) > "0") {
      $result = "This user already exists in the database. Please use a unique username.";
      require_once('./include/infopage.php');
      exit();
    }
  
    if(strlen($site) > "2") { // They entered a site name, we'll ignore address details if present and overwide with site information.
      $sql = "SELECT name, address, city, state, zip FROM sites WHERE name='$site'";
      $row = mysql_query($sql);
      if(mysql_num_rows($row) != "1") {
        $result = "The name of the site you provided is not valid. Please enter a valid site name or enter the full address for the user.";
        require_once('./include/infopage.php');
        exit();
      }
      list($site,$address,$city,$state,$zip) = mysql_fetch_row($row);
    }
    else { // They entered no site information, we'll call their location "remote" so that the hardware has a location on the list hardware page.
      $site = "remote";
    }
  
    $sql = "INSERT INTO users (username, tmppasswd, accesslevel, phone, altphone, address, city, state, zip, site, email) 
              VALUES('$username', '$tmppasswd', '$perms', '$phone', '$altphone', '$address', '$city', '$state', '$zip', '$site', '$email')";
    mysql_query($sql);
    $result = "The user has been added to the database.<br />";
  
     
    if(strlen($hardware) > "3") {
      // First get the hardware ID and asset number so that the two queries we do will run will be less complicated and we will be able to use $asset in the $result statement.
      $sql = "SELECT hid, asset FROM hardwares WHERE serial='$hardware' OR asset='$hardware'";
      $row = mysql_query($sql);
      if(mysql_num_rows != "1") { // They entered an invalid asset/serial number. We have already added the user to the database, we just wont assign the hardware.
        $result .= "The asset number or serial number you provided is not valid. No hardware has been assigned to this new user.<br />";
        require_once('./include/infopage.php');
        exit();
      }
      list($hid,$asset) = mysql_fetch_row($row);
    
      // Mark the hardware return date for the current record as now.  
      $sql = "UPDATE hardware SET cidate=NOW() WHERE hid='$hid' AND cidate='0000-00-00 00:00:00'";
      mysql_query($sql);
    
      // Create a new record for the hardware being assigned to $user and change the username field to $user in hardwares for this asset.
      $sql = "INSERT INTO hardware (coid, username, hid, site, codate, cidate ) 
                VALUES ( 'NULL', '$username', '$hid', '$site', NOW(), '0000-00-00 00:00:00')";
      mysql_query($sql);
  
      $sql = "UPDATE hardwares SET username='$username' WHERE hid='$hid'";
      mysql_query($sql);
  
      $result .= "Asset number $asset has been assigned to $username.<br />";
    }

    require_once('./include/header.php');
    require_once('./include/infopage.php');
  }
  else { // No location details were entered
    $result = "You must specify details about the location this user will be at. You can enter the address or provide the name of the site the user works out of.".
                 " Please go back and try again.";
    require_once('./include/infopage.php');
    exit();
  }
} // Ends process_new_user function

?>

<?php
/*
 * This script contains functions that enable search and search result export to excel
 *
 * Please see /include/common.php for documentation on common.php and the $CI global array used by this application as well as the AccessControl function used widely.
 */
require_once('./include/common.php');

if(isset($_GET['op'])){
  $op = $_GET['op'];
}
else {
  $op = "show_form";
}

switch($op){

  case "download";
  download();
  break;
  
  case "search";
  require_once('./include/header.php');
  search();
  break;
  
  default: 
  require_once('./include/header.php');
  show_form();
  break;
}

/*
 * The download function takes the same GET inputs as the search function but outputs to an excel file that the user can download.
 * Excel can interpret html, so we're just outputting everything the search function does from <table> to </table> and then forcing
 * a save dialog using the header() function. The download function has to be a separate page because we've already produced output 
 * to the browser in the search function that we don't want in the spreadsheet by the time we get to the actual search results.
 */

function download(){
  require_once('./include/common.php');
  $accesslevel = "1";
  $message = "search exported";
  AccessControl($accesslevel, $message); 
    
  $first = clean($_GET['first']);
  $second = clean($_GET['second']);
  $search = clean($_GET['search']);
  $fromdate = clean($_GET['from_year'])."-".clean($_GET['from_month'])."-".clean($_GET['from_day']);
  $todate = clean($_GET['to_year'])."-".clean($_GET['to_month'])."-".clean($_GET['to_day']);
  $when = clean($_GET['when']);
  

  if(strlen($search) <= "3"){
    require_once('./include/header.php');
    $result = "You must enter a search phrase of four characters or more in order to find results.";
	require_once('./include/infopage.php');
  }
  elseif($first == "0") { // they're looking for users at a site
    $first = "users";
	$link = "users.php?op=show&amp;usersearch=";
    $First = "Username";
	$Second = "Location";
    $sql = "SELECT DISTINCT username, site FROM users WHERE site LIKE '%$search%' ORDER BY site";
  }
  elseif($first == "1"){ // they're looking for all hardware assigned to a user or location or all hardware with a particular software title assigned to it, possibly with a date range.
    $First = "Hardware Asset";
	$first = "hardware assets";
	if($when == 'all'){
      if($second == "username" || $second == "site"){
        $sql = "SELECT DISTINCT hardwares.asset, hardware.$second, hardware.codate, hardware.cidate FROM hardwares, hardware ".
	           "WHERE hardwares.hid=hardware.hid AND hardware.$second LIKE '%$search%'";
	  }
      else {
	    $First = "Softare Title";
		$Second = "Asset Number";
	    $sql = "SELECT DISTINCT software.title, hardwares.asset, software.codate, software.cidate FROM software, hardwares WHERE ".
	           "software.hid=hardwares.hid AND title LIKE '%$search%'";
	  }
    }elseif($when == "dates"){ // They are looking within a date range.
      if($second == "username" || $second == "site"){
	    $extrasearchdescription = "and the hardware was checked out from $fromdate to $todate";
        $sql = "SELECT DISTINCT hardwares.asset, hardware.$second, hardware.codate, hardware.cidate FROM hardwares, hardware ".
	           "WHERE hardwares.hid=hardware.hid AND hardware.$second LIKE '%$search%' AND codate>='$fromdate 00:00:00' AND cidate<='$todate 23:59:59'";
	  }
      else {
	    $First = "Softare Title";
		$Second = "Asset Number";
		$extrasearchdescription = "and $second was checked out to the hardware from $fromdate to $todate";
	    $sql = "SELECT DISTINCT software.title, hardwares.asset, software.codate, software.cidate FROM software, hardwares WHERE ".
	           "software.hid=hardwares.hid AND title LIKE '%$search%' AND codate>='$fromdate 00:00:00' AND cidate<='$todate 23:59:59'";
		
	  }
	}elseif($when == "current") { 
	  if($second == "username" || $second == "site"){
		$extrasearchdescription = "and the hardware is currently checked out.";
        $sql = "SELECT DISTINCT hardwares.asset, hardware.$second, hardware.codate, hardware.cidate FROM hardwares, hardware ".
	           "WHERE hardwares.hid=hardware.hid AND hardware.$second LIKE '%$search%' AND cidate='0000-00-00 00:00:00'";

	  }
      else {
	    $First = "Softare Title";
		$Second = "Asset Number";
		$extrasearchdescription = "and $second is currently checked out to the hardware";
	    $sql = "SELECT DISTINCT software.title, hardwares.asset, software.codate, software.cidate FROM software, hardwares WHERE ".
	           "software.hid=hardwares.hid AND title LIKE '%$search%' AND cidate='0000-00-00 00:00:00'";
	  }
	}
	else {
	  require_once('./include/header.php');
	  $result = "The query you have entered was not formed properly.";
	  require_once('./include/infopage.php');
	}
  }
  elseif($first == "2"){ // They're trying to search logs
    $first = "logs";
	$First = "Logs";
	$Second = ucfirst($second);
	if($when == "dates"){
	  $extrasearchdescription = "and the event occured between $fromdate and $todate";
	  $sql = "SELECT occuredat, username, ipaddress, level, message FROM logs WHERE $second LIKE '%$search%' AND ".
	         "occuredat<'$fromdate 00:00:00' AND occuredat>'$todate 23:59:59' ORDER BY lid DESC";
	}
	else{
	  $sql = "SELECT occuredat, username, ipaddress, level, message FROM logs WHERE $second LIKE '%$search%' ORDER BY lid DESC";
	}
  }
  if($second == "username"){
    $Second = "User";
  }elseif($second == "site") {
    $Second = "Location";
  }

  $row = mysql_query($sql);
  $totalrows = mysql_num_rows($row);
 

  if($totalrows < "1"){
    require_once('./include/header.php');
    $result = "No results were found that matched your search.";
	require_once('./include/infopage.php');
  }
  
  ob_start();

  if($first != "logs" && $first != "users"){
    echo "<table width=\"100%\"><tr><td><b>$First</b></td><td><b>$Second</b></td><td><b>Checked Out</b></td><td><b>Checked In</b></td></tr>";
    while(list($linkable,$searched,$from,$to) = mysql_fetch_row($row)){
      echo "<tr><td>$linkable</td><td>$searched</td><td>$from</td><td>$to</td></tr>";
    }  
  }
  elseif($first == "users"){
    echo "<table width=\"100%\"><tr><td><b>$First</b></td><td><b>$Second</b></td></tr>";
    while(list($linkable,$searched) = mysql_fetch_row($row)){
      echo "<tr><td>$linkable</td><td>$searched</td></tr>";
    }
  }
  elseif($first == "logs"){
    echo "<table width=\"100%\"><tr><td><b>Timestamp</b></td><td><b>Username</b></td><td><b>IP Address</b></td>".
         "<td><b>Severity</b></td><td><b>Message</b></td></tr>\n";
    while(list($occuredat,$username,$ipaddress, $level,$message) = mysql_fetch_row($row)){
      echo "<tr><td>$occuredat</td><td>$username</td><td>$ipaddress</td><td>$level</td><td>$message</td></tr>";
    }
  }
  echo "</table>";

  $fileout = ob_get_contents();
  ob_end_clean();
  $size = strlen(pack("A", $fileout));
  $size = ceil($size/8);
  header("Cache-Control: "); //keeps ie happy
  header("Pragma: "); //keeps ie happy
  header("Content-type: application/ms-excel"); // content type
  header("Content-Length: $size");
  header("Content-Disposition: attachment; filename=\"search.xls\"");
  echo $fileout;
}

function search(){
  global $CI;
  $accesslevel = "1";
  $message = "search conducted";
  AccessControl($accesslevel, $message); 
  if(isset($_GET['export'])){
    $export = clean($_GET['export']);
  }
  else{
    $export = "off";
  }
  
  if($export == "on"){ // The download function has to be a separate page because we've already produced output to the browser in this function that we don't want in the spreadsheet.
    $uri = $_SERVER['REQUEST_URI'];
	$uri = str_replace("op=search", "op=download", $uri);
	header("Location: $uri");
	exit();
  }
    
  $first = clean($_GET['first']);
  $second = clean($_GET['second']);
  $search = clean($_GET['search']);
  $fromdate = clean($_GET['from_year'])."-".clean($_GET['from_month'])."-".clean($_GET['from_day']);
  $todate = clean($_GET['to_year'])."-".clean($_GET['to_month'])."-".clean($_GET['to_day']);
  $when = clean($_GET['when']);
  
  echo "<h1>Search Results:</h1><br />";
  
  if(strlen($search) <= "3"){
    echo "<p>You must enter a search phrase of four characters or more in order to find results.</p>";
	require_once('./include/footer.php');
	exit();
  }
  elseif($first == "0") { // they're looking for users at a site
    $first = "users";
	$link = "users.php?op=show&amp;usersearch=";
    $First = "Username";
	$Second = "Location";
    $sql = "SELECT DISTINCT username, site FROM users WHERE site LIKE '%$search%' ORDER BY site";
  }
  elseif($first == "1"){ // they're looking for all hardware assigned to a user or location or all hardware with a particular software title assigned to it, possibly with a date range.
    if($second == "software") {
	  $link = "software.php?op=show&amp;title=";
	}
	else {
	  $link = "hardware.php?op=show&amp;search=";
	}
    $First = "Hardware Asset";
	$first = "hardware assets";
	if($when == 'all'){
      if($second == "username" || $second == "site"){
        $sql = "SELECT DISTINCT hardwares.asset, hardware.$second, hardware.codate, hardware.cidate FROM hardwares, hardware ".
	           "WHERE hardwares.hid=hardware.hid AND hardware.$second LIKE '%$search%'";
	  }
      else {
	    $First = "Softare Title";
		$Second = "Asset Number";
	    $sql = "SELECT DISTINCT software.title, hardwares.asset, software.codate, software.cidate FROM software, hardwares WHERE ".
	           "software.hid=hardwares.hid AND title LIKE '%$search%'";
	  }
    }elseif($when == "dates"){ // They are looking within a date range.
      if($second == "username" || $second == "site"){
	    $extrasearchdescription = "and the hardware was checked out from $fromdate to $todate";
        $sql = "SELECT DISTINCT hardwares.asset, hardware.$second, hardware.codate, hardware.cidate FROM hardwares, hardware ".
	           "WHERE hardwares.hid=hardware.hid AND hardware.$second LIKE '%$search%' AND codate>='$fromdate 00:00:00' AND cidate<='$todate 23:59:59'";
	  }
      else {
	    $First = "Softare Title";
		$Second = "Asset Number";
		$extrasearchdescription = "and $second was checked out to the hardware from $fromdate to $todate";
	    $sql = "SELECT DISTINCT software.title, hardwares.asset, software.codate, software.cidate FROM software, hardwares WHERE ".
	           "software.hid=hardwares.hid AND title LIKE '%$search%' AND codate>='$fromdate 00:00:00' AND cidate<='$todate 23:59:59'";
		
	  }
	}elseif($when == "current") { 
	  if($second == "username" || $second == "site"){
		$extrasearchdescription = "and the hardware is currently checked out.";
        $sql = "SELECT DISTINCT hardwares.asset, hardware.$second, hardware.codate, hardware.cidate FROM hardwares, hardware ".
	           "WHERE hardwares.hid=hardware.hid AND hardware.$second LIKE '%$search%' AND cidate='0000-00-00 00:00:00'";

	  }
      else {
	    $First = "Softare Title";
		$Second = "Asset Number";
		$extrasearchdescription = "and $second is currently checked out to the hardware";
	    $sql = "SELECT DISTINCT software.title, hardwares.asset, software.codate, software.cidate FROM software, hardwares WHERE ".
	           "software.hid=hardwares.hid AND title LIKE '%$search%' AND cidate='0000-00-00 00:00:00'";
	  }
	}
	else {
	  echo "<p>The query you have entered was not formed properly.</p>";
	  require_once('./include/footer.php');
	  exit();
	}
  }
  elseif($first == "2"){ // They're trying to search logs
    $first = "logs";
	$First = "Logs";
	$Second = ucfirst($second);
	if($when == "dates"){
	  $extrasearchdescription = "and the event occured between $fromdate and $todate";
	  $sql = "SELECT occuredat, username, ipaddress, level, message FROM logs WHERE $second LIKE '%$search%' AND ".
	         "occuredat>='$fromdate 00:00:00' AND occuredat<='$todate 23:59:59' ORDER BY lid DESC";
	}
	else{
	  $sql = "SELECT occuredat, username, ipaddress, level, message FROM logs WHERE $second LIKE '%$search%' ORDER BY lid DESC";
	}
  }
  if($second == "username"){
    $Second = "User";
  }elseif($second == "site") {
    $Second = "Location";
  }
  if(!isset($_GET['page'])) { 
    $page = "1";
  }
  else {
    $page = $_GET['page']; 
  }
  $limit = "10";
  $lowerlimit = $page * $limit - $limit;
  $totalrows = mysql_num_rows(mysql_query($sql));
  $sql .= " LIMIT $lowerlimit, $limit";
  $row = mysql_query($sql);
  $rows = mysql_num_rows($row);
  $numofpages = ceil($totalrows/$limit); 
  if(!isset($extrasearchdescription)){
    $extrasearchdescription = "";
  }
  
  echo "<p><b>You searched for:</b><br />All $first where \"$second\" is like \"$search\" $extrasearchdescription</p>".
       "<hr class=\"head\" />";

  if($totalrows < "1"){
    echo "<p><b>No results were found that matched your search.</b></p>";
	require_once('./include/footer.php');
	exit();
  }

  if($first != "logs" && $first != "users"){
    echo "<table width=\"100%\"><tr><td><b>$First</b></td><td><b>$Second</b></td><td><b>Checked Out</b></td><td><b>Checked In</b></td></tr>".
	     "<tr><td colspan=\"4\"><hr class=\"head\" /></td></tr>\n";
    while(list($linkable,$searched,$from,$to) = mysql_fetch_row($row)){
      echo "<tr><td><a href=\"$link$linkable\">$linkable</a></td><td>$searched</td><td>$from</td><td>$to</td></tr>".
	       "<tr><td colspan=\"4\"><hr class=\"division\" /></td></tr>";
    }
    echo "</table>";
  }
  elseif($first == "users"){
    echo "<table width=\"100%\"><tr><td><b>$First</b></td><td><b>$Second</b></td></tr>".
	     "<tr><td colspan=\"2\"><hr class=\"head\" /></td></tr>\n";
    while(list($linkable,$searched) = mysql_fetch_row($row)){
      echo "<tr><td><a href=\"$link$linkable\">$linkable</a></td><td>$searched</td></tr>".
	       "<tr><td colspan=\"4\"><hr class=\"division\" /></td></tr>";

    }
    echo "</table>";
  }
  elseif($first == "logs"){
    echo "<table width=\"100%\"><tr><td><b>Timestamp</b></td><td><b>Username</b></td><td><b>IP Address</b></td>".
         "<td><b>Severity</b></td><td><b>Message</b></td></tr>\n".
	     "<tr><td colspan=\"5\"><hr class=\"head\" /></td></tr>\n";
		 
    while(list($occuredat,$username,$ipaddress, $level,$message) = mysql_fetch_row($row)){
      if($level == "high"){
	    $level = "<b>$level</b>";
      }
	  echo "<tr><td>$occuredat</td><td>$username</td><td>$ipaddress</td><td>$level</td><td>$message</td></tr>".
	       "<tr><td colspan=\"5\"><hr class=\"division\" /></td></tr>";

    }
    echo "</table>";
  }
  

  
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
  
} // Ends search function

/*
 * The search form uses the script.aculo.us javascript library as well as options.js which is taken from
 * http://www.quirksmode.org/js/options.html. I have modified options.js to call scriptaculous functions that
 * enable actions on changes of drop down lists. Options.js enables dynamic drop-down list contents based on the
 * selection in a previous drop-down list.
 */

function show_form(){
  global $CI;
  $accesslevel = "1";
  $message = "search form accessed";
  AccessControl($accesslevel, $message); 
  
  ?>
  <script type="text/javascript" src="javascripts/options.js"></script>
  <script type="text/javascript">
    init();
  </script>
  <h1>Search:</h1>
  <br />
  <form action="search.php" method="get">
  <p><b>Search:</b><br />
  <input type="hidden" name="op" value="search" />
  <select name="first" onchange="populate();">
    <option value="0">users</option>
	<option value="1">hardware</option>
	<option value="2">logs</option>
  </select>
  matching
  <select name="second">
	<option value="location">location</option>
  </select>: <input name="search" type="text" /> &nbsp;
  <br />
  <div id="extraforms" style="display: none;">
  <div id="extraforms2" style="display: none;">
  <input type="radio" name="when" value="current" onclick="new Effect.Fade('extraextraforms', {duration: 0.2})" checked="checked" /> currently assigned<br />
  </div>  
  <input type="radio" name="when" value="all" onclick="new Effect.Fade('extraextraforms', {duration: 0.2})" /> in all records <br />
  <input type="radio" name="when" value="dates" onclick="new Effect.Appear('extraextraforms', {duration: 0.2})" /> specify a date range<br />
  <div id="extraextraforms" style="display: none;">
  <br />
  <b>From:</b><br />
    <select name="from_year">
    <?php
	  $year = "2006";
	  $currentyear = date('Y');
	  while($year <= $currentyear){
	    echo "<option value=\"$year\">$year</option>";
		$year++;
	  }
	?>
  </select> 
  <select name="from_month">
    <?php 
	  $month = "1";
	  while($month < "13"){
	    echo "<option value=\"$month\">$month</option>";
		$month++;
	  }
	?>
  </select>
  <select name="from_day">
    <?php 
	  $day = "1";
	  while($day < "32"){
	    echo "<option value=\"$day\">$day</option>";
		$day++;
	  }
	?>
  </select>
  <br /><br />
  <b>To:</b><br />
    <select name="to_year">
    <?php
	  $year = "2006";
	  $currentyear = date('Y');
	  while($year <= $currentyear){
	    echo "<option value=\"$year\">$year</option>";
		$year++;
	  }
	?>
  </select> 
  <select name="to_month">
    <?php 
	  $month = "1";
	  while($month < "13"){
	    echo "<option value=\"$month\">$month</option>";
		$month++;
	  }
	?>
  </select>
  <select name="to_day">
    <?php 
	  $day = "1";
	  while($day < "32"){
	    echo "<option value=\"$day\">$day</option>";
		$day++;
	  }
	?>
  </select>
  <br />
  </div>
  </div>
  <br />
  <input type="checkbox" name="export" /> Export Results as a Microsoft Excel spreadsheet<br />
  <br />
  <input type="submit" value=" Go " /></p>
  </form>
  <br />
  <?php
  require_once('./include/footer.php');
} // Ends list_searches function

?>

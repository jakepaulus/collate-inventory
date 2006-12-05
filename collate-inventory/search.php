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
  
  case "search";
  search();
  break;
  
  default: 
  show_form();
  break;
}

function search(){
  global $CI;
  AccessControl('1'); // The access level of this script is 1. Please see the documentation for this function in common.php.
  require_once('./include/header.php');
  
  $first = clean($_GET['first']);

  if($first == "1") {
    $first = "hardware";
  }
  if($first == "2") {
    $first = "software";
  }
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
    $first = "Username";
	$second = "Location";
    $sql = "SELECT username, site FROM users WHERE site LIKE '%$search%' ORDER BY site";
  }
  else{ // they're looking for all hardware assigned to a user or location or all hardware with a particular software title assigned to it, possibly with a date range.
    $first = "Hardware Asset";
	if($when == 'all'){
      if($second == "username" || $second == "site"){
        $sql = "SELECT hardwares.asset, hardware.$second FROM hardwares, hardware ".
	           "WHERE hardwares.hid=hardware.hid AND hardware.$second LIKE '%$search%'";
	  }
      else {
	    $first = "Softare Title";
		$second = "Asset Number";
	    $sql = "SELECT software.title, hardwares.asset FROM software, hardwares WHERE ".
	           "software.hid=hardwares.hid AND title LIKE '%$search%'";
	  }
    }elseif($when == "dates"){ // They are looking within a date range.
      if($second == "username" || $second == "site"){
        $sql = "SELECT hardwares.asset, hardware.$second FROM hardwares, hardware ".
	           "WHERE hardwares.hid=hardware.hid AND hardware.$second LIKE '%$search%' AND codate>='$fromdate' AND cidate<='$todate'";
	  }
      else {
	    $first = "Softare Title";
		$second = "Asset Number";
	    $sql = "SELECT software.title, hardwares.asset FROM software, hardwares WHERE ".
	           "software.hid=hardwares.hid AND title LIKE '%$search%' AND codate>='$fromdate' AND cidate<='$todate'";
	  }
	}elseif($when == "current") { 
	  if($second == "username" || $second == "site"){
        $sql = "SELECT hardwares.asset, hardware.$second FROM hardwares, hardware ".
	           "WHERE hardwares.hid=hardware.hid AND hardware.$second LIKE '%$search%' AND cidate='0000-00-00 00:00:00'";
	  }
      else {
	    $first = "Softare Title";
		$second = "Asset Number";
	    $sql = "SELECT software.title, hardwares.asset FROM software, hardwares WHERE ".
	           "software.hid=hardwares.hid AND title LIKE '%$search%' AND cidate='0000-00-00 00:00:00'";
	  }
	}
	else {
	  echo "<p>The query you have entered was not formed properly.</p>";
	  require_once('./include/footer.php');
	  exit();
	}
  }
  if($second == "username"){
    $second = "User";
  }elseif($second == "site") {
    $second = "Location";
  }
  $row = mysql_query($sql);
  $rowcount = mysql_num_rows($row);
  
  if($rowcount < "1"){
    echo "<p>No results were found that matched your search.</p>";
	require_once('./include/footer.php');
	exit();
  }
  echo "<table><tr><th>$first</th><th>$second</th></tr>";
  while(list($linkable,$searched) = mysql_fetch_row($row)){
    echo "<tr><td>$linkable</td><td>$searched</td></tr>";
  }
  echo "</table>";
	
  
  
  

  
  require_once('./include/footer.php');
  
} // Ends search function

function show_form(){
  global $CI;
  AccessControl('1'); // The access level of this script is 1. Please see the documentation for this function in common.php.
  require_once('./include/header.php');
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
  <select name="first" onchange="populate()">
    <option value="0">users</option>
	<option value="1">hardware</option>
  </select>
  matching
  <select name="second">
	<option value="location">location</option>
  </select>: <input name="search" type="text" /> &nbsp;
  <br />
  <div id="extraforms" style="display: none;">
  <input type="radio" name="when" value="current" onclick="new Effect.Fade('extraextraforms', {duration: 0.2})" checked="checked"> currently assigned<br />
  <input type="radio" name="when" value="all" onclick="new Effect.Fade('extraextraforms', {duration: 0.2})"> in all records <br />
  <input type="radio" name="when" value="dates" onclick="new Effect.Appear('extraextraforms', {duration: 0.2})"> specify a date range<br />
  <br />
  <div id="extraextraforms" style="display: none;">
  <b>From:</b><br />
    <select name="from_year">
    <?php
	  $year = "2006";
	  $currentyear = date('Y');
	  while($year <= $currentyear){
	    echo "<option>$year</option>";
		$year++;
	  }
	?>
  </select> 
  <select name="from_month">
    <?php 
	  $month = "1";
	  while($month < "13"){
	    echo "<option>$month</option>";
		$month++;
	  }
	?>
  </select>
  <select name="from_day">
    <?php 
	  $day = "1";
	  while($day < "32"){
	    echo "<option>$day</option>";
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
	    echo "<option>$year</option>";
		$year++;
	  }
	?>
  </select> 
  <select name="to_month">
    <?php 
	  $month = "1";
	  while($month < "13"){
	    echo "<option>$month</option>";
		$month++;
	  }
	?>
  </select>
  <select name="to_day">
    <?php 
	  $day = "1";
	  while($day < "32"){
	    echo "<option>$day</option>";
		$day++;
	  }
	?>
  </select>
  <br /><br />
  </div>
  </div>
  <input type="submit" value=" Go " /></p>
  </form>
  <?php
  require_once('./include/footer.php');
} // Ends list_searches function

?>

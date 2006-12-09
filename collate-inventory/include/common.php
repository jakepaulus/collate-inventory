<?php
session_start();
date_default_timezone_set ('UTC');
error_reporting('8191');

//------------- Build CI array var and put version number in it -----------------------------

$CI = array();
$CI['version'] = "alpha";

if(isset($_SESSION['accesslevel'])){
  $CI['user']['accesslevel'] = $_SESSION['accesslevel'];
}
else{
  $CI['user']['accesslevel'] = "0";
}



//---------- Populate CI['settings'] with settings from db ----------------------------------

// First get CI settings to see if we even need to check user's permissions
require_once('./include/db_connect.php'); 

$sql = "SELECT * FROM settings";
$result = mysql_query($sql);
  
while ($column = mysql_fetch_assoc($result)) {
  // $CI['settings']['setting_name'] will be set to the seting's value.
  $CI['settings'][$column['name']] = $column['value'];
}  


// --------------- Prevent Unwanted Access ---------------------------------------------------

/**
 * The goal of this section is to set $_SESSION['accesslevel'] with the appropriate 
 * value based on settings and user access. Each function will have a hard-coded value 
 * to check for to allow the function to run. When AccessControl has determined the
 * user has enough access for the function, it will stop further checks.
 * 
 * Access Level 0 = Access denied completely: User can see index.php and login.php
 * Access Level 1 = Read-Only access, no changes can be made
 * Access Level 3 = Changes to inventory can be made, but no changes to settings are allowed
 * Access Level 5 = Full control of the application including setting changes, user's access level modifications, and user password resets.
 */

 function AccessControl($accesslevel, $message) {
   global $CI;
  
  // If the person has setup this app. with no permission checks to make configuration
  // changes, we can safely assume they don't care if people view/add/remove inventory
  if($CI['settings']['checklevel5perms'] == "0") {
    return;
  }
  elseif($CI['settings']['checklevel3perms'] == "0" && $accesslevel < "4") {
    return;
  }
  elseif($CI['settings']['checklevel1perms'] == "0" && $accesslevel < "2") {
    return; 
  }
  // At this point, we're going to have to make the users start logging in. 
  
  elseif(!isset($_SESSION['username'])) { // the user isn't logged in.
    $returnto = urlencode($_SERVER['REQUEST_URI']); // return the user to where they came from with this var
    $notice = "The administrator of this application requires you to login to use this feature.";
    header("Location: login.php?notice=$notice&returnto=$returnto");
    exit(); // If we're requiring a login, we don't want any further script processing at all. 
  
  
  // If we've gottent his far, it means the user is already logged in. We'll check their access level and allow or deny access.
  // If access is allowed, but a permission check was required, we'll log what the user was doing.
  }elseif($_SESSION['accesslevel'] >= $accesslevel){
    if($accesslevel > "1"){
      ci_log($accesslevel, $message);
	}
    return; // Access is allowed
  }
  
  // Some basic info needed to say the access denied error properly.
  if(empty($CI['settings']['adminname'])){
    $adminname = "this application's administrator";
  }
  else {
    $adminname = $CI['settings']['adminname'];
  }
  if(!empty($CI['settings']['adminemail'])){
    $email = "You can email this person at <a href=\"mailto:".$CI['settings']['adminemail']."\"".$CI['settings']['adminemail']."</a>.";
  }
  if(!empty($CI['settings']['adminphone'])){
    $phone = "You can reach this person by telephone by dialing ".$CI['settings']['adminphone'];
  }  
  
  // if we've gotten this far in the function, we've not met any condition to allow access so access is denied.
  $result = "I'm sorry. You do not have sufficient access to use this resource. Please contact $adminname ".
               "to have have this issue addressed if you believe you should have access. $email $phone";
  require_once('./include/infopage.php');
  exit();  
  
} // Ends AccessControl function




// --------------- Clean Function ---------------------------------------------------

/**
 * This is a very simple sanitizing function to execute on user's input.
 */

function clean($variable){ 
  $variable = strip_tags(trim($variable)); 
  return $variable;
}

//------------Logging Function------------------------------------------------------
function ci_log($accesslevel, $message){
  $user = $_SESSION['username'];
  $ipaddress = $_SERVER['REMOTE_ADDR'];
  
  if($accesslevel == "1"){ $level = "low"; }
  if($accesslevel == "3"){ $level = "normal"; }
  if($accesslevel == "5"){ $level = "high"; }
  
  if(empty($user)){ $user = "system"; }
  
  $sql = "INSERT INTO logs (occuredat, username, ipaddress, level, message) VALUES(NOW(), '$user', '$ipaddress', '$level', '$message')";
  mysql_query($sql);
 
} // Ends ci_log function

?>
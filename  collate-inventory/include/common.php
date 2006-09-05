<?php

//------------- Build CI array var and put version number in it -----------------------------

$CI = array();
$CI['version'] = "alpha";



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
 * The goal of this section is to set $CI['user']['accesslevel'] with the appropriate 
 * value based on settings and user access. Each function will have a hard-coded value 
 * to check for to allow the function to run. When AccessControl has determined the
 * user has enough access for the function, it will stop further checks.
 * 
 * Access Level 0 = Access denied completely: User can see index.php and login.php
 * Access Level 1 = Read-Only access, no changes can be made
 * Access Level 3 = Changes to inventory can be made, but no changes to settings are allowed
 * Access Level 5 = Full control of the application.
 */

 function AccessControl($accesslevel) {
   global $CI, $extrameta;
 
  // The default Access Level is 0.
  $CI['user']['accesslevel'] = "0";
 
  // If the person has setup this app. with no permission checks to make configuration
  // changes, we can safely assume they don't care if people view/add/remove inventory
  if($CI['settings']['checklevel5perms'] == "0") {
    $CI['user']['accesslevel'] = "5"; // I hope they know what they're doing.
    return($CI['user']['accesslevel']);
  }
  
  if($CI['settings']['checklevel3perms'] == "0" && $accesslevel < "4") {
    $CI['user']['accesslevel'] = "3";  // We're allowing normal inventory use without a login 
    return($CI['user']['accesslevel']);
  }
  
  if($CI['settings']['checklevel1perms'] == "0" && $accesslevel < "2") {
    $CI['user']['accesslevel'] = "1"; // We're allowing inventory reading access without a login
    return($CI['user']['accesslevel']); 
  }

  // At this point, we're going to have to make the users start logging in. I haven't started the session until now
  // because passing cookies is rude if we don't need to.
  session_name("CollateInventory");
  session_start();
  if(!isset($_SESSION['username'])) { // the user isn't logged in.
    $_SESSION['returnpage'] = $_SERVER['REQUEST_URI']; // return the user to where they came from with this var
    $extrameta = "<meta http-equiv=\"refresh\" content=\"5;url=login.php\" />"; // We have to meta redirect instead of using header() because
													   // We're trying to pass session variables.
    $result = "The administrator of this application requires you to login to use this feature. Please click <a href=\"login.php\">here</a>";

    include_once('header.php'); // This is included way over here so that the extrameta var can be put within the html <head> and validate.
    require_once( "./infopage.php" );
    exit(); // If we're requiring a login, we don't want any further script processing at all. 
  }
  
  // If we've gottent his far, it means the user is already logged in. We'll check their access level and allow or deny access.
  $CI['user']['accesslevel'] = $_SESSION['accesslevel'];
  if($CI['user']['accesslevel'] <= $accesslevel){
  return($CI['user']['accesslevel']); // Access is allowed
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
  require_once('./infopage.php');
  exit();  
  
} // Ends AccessControl function

?>
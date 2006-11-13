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
	case "new";
	insert();
	break;

	case "update";
	update();
	break;

	default:
	break; // No user should try to display this directly.
}

function clean($variable){
  $variable = trim(strip_tags(nl2br($variable)));
  return $variable;
}


function insert(){
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
  
  // If they entered location information, we'll process the form. Otherwise, they get an error.
  if((strlen($_POST['address']) > "4" or 
      strlen($_POST['city']) > "3" or 
      strlen($_POST['state']) > "2" or 
      strlen($_POST['zip']) > "5") || 
      strlen($_POST['sitesearch']) > "2") { 

    $username = clean($_POST['username']);
    $phone = clean($_POST['phone']);
    $altphone = clean($_POST['altphone']);
    $address = clean($_POST['address']);
    $city = clean($_POST['city']);
    $state = clean($_POST['state']);
    $zip = clean($_POST['zip']);
    $email = clean($_POST['email']);
    $site = clean($_POST['sitesearch']);
    $hardware = clean($_POST['hardwaresearch']);
  
    $test = mysql_query("SELECT uid FROM users WHERE username='$username'");
    if(mysql_num_rows($test) > "0") {
      $result = "This user already exists in the database. If you have two people with the same first and last name, please insert their middle initial as part of the first name field.";
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
  
    $sql = "INSERT INTO users (uid, username, phone, altphone, address, city, state, zip, site, email) 
              VALUES(NULL, '$username', '$phone', '$altphone', '$address', '$city', '$state', '$zip', '$site', '$email')";
    mysql_query($sql);
    $result = "The user has been added to the database.<br />";
  
     
    if(strlen($hardware) > "5") {
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
} // Ends insert() function


function update(){
}

?>

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
  global $CI;
  AccessControl("3"); // The access level required for this function is 1. Please see the documentation for this function in common.php.
 
  include_once('header.php'); // This has to be included after AccessControl in case it gets used by the error generator.    
  if (strlen($_POST['username']) < "5" || 
      strlen($_POST['address']) < "5" || 
      strlen($_POST['city']) < "3" || 
      strlen($_POST['state']) < "2" || 
      strlen($_POST['zip']) < "5" || 
      strlen($_POST['phone']) < "2") { 
    $result = "All fields except Alt. Phone and email address are requird. Please go back and try again."; 
    require_once('./include/infopage.php'); 
  } 
  else {

  require_once('./include/db_connect.php');

  $username = clean($_POST['username']);
  $phone = clean($_POST['phone']);
  $altphone = clean($_POST['altphone']);
  $address = clean($_POST['address']);
  $city = clean($_POST['city']);
  $state = clean($_POST['state']);
  $zip = clean($_POST['zip']);
  $email = clean($_POST['email']);
  
  $test = mysql_query("SELECT uid FROM users WHERE username='$username'");
  if(mysql_num_rows($test) > "0") {
    $result = "This user already exists in the database. If you have two people with the same first and last name, please insert their middle initial as part of the first name field.";
    require_once('infopage.php');
    exit();
  }
  
  $sql = "INSERT INTO users (uid, username, phone, altphone, address, city, state, zip, email) VALUES(NULL, '$username', '$phone', '$altphone', '$address', '$city', '$state', '$zip', '$email')";

  $result = mysql_query($sql);

    if (mysql_affected_rows() == 1){
      $result = "The data has been succesfully added to the database.";
    }
    else {
      $result = "Something went wrong. If the problem persists, ask me if I can put a better error message here.";
    }
    require_once('./include/header.php');
    require_once('./include/infopage.php');
  }
} // Ends insert() function


function update(){
}

?>

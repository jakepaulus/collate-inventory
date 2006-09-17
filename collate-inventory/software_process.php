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
	case "edit";
	edit_user();
	break;
	
	case "new";
	insert_software();
	break;

	default:
	break;
}

function clean($variable){
  $variable = trim(strip_tags(nl2br($variable)));
  return $variable;
}

function insert_software(){
  global $CI;
  AccessControl("3"); // The access level required for this function is 3. Please see the documentation for this function in common.php.
  
  if (strlen($_POST['title']) < "1" || 
      strlen($_POST['desc']) < "1" || 
      strlen($_POST['total']) < "1" || 
      strlen($_POST['value']) < "1" || 
      strlen($_POST['inuse']) < "1") { 
    $result = "All fields except are required. Please go back and ensure all fields are completed."; 
    require_once('infopage.php'); 
    return;
  } 
  else {

  $title = clean($_POST['title']);
  $description = clean($_POST['desc']);
  $value = clean($_POST['value']);
  $total = clean($_POST['total']);
  $available = abs($total - $_POST['inuse']);
  
  if(!is_numeric($value)){
    $result = "\"$value\" contains more than just numbers. Please only enter numbers for the value.";
    require_once('infopage.php');
    exit();
  } 
  
  $sql = "INSERT INTO softwares (sid, title, description, value, total, available) VALUES(NULL, '$title', '$description', '$value', '$total', '$available')";

  $result = mysql_query($sql);

    if (mysql_affected_rows() == 1){
      $result = "The data has been succesfully added to the database.";
    }
    else {
      $result = "Something went wrong. Make sure you comleted all of the fields.";
    }
    require_once('infopage.php');
  }
} // Ends insert() function

?>
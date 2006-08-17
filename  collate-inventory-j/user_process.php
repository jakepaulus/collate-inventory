<?php
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
  if (strlen($_POST['name']) < "3" || 
      strlen($_POST['address']) < "5" || 
      strlen($_POST['city']) < "3" || 
      strlen($_POST['state']) < "2" || 
      strlen($_POST['zip']) < "5" || 
      strlen($_POST['phone']) < "2") { 
    $result = "All fields except Alt. Phone and email address are requird. Please go back and try again."; 
    require_once('infopage.php'); 
  } 
  else {

  require_once('include/db_connect.php');

  $name = clean($_POST['name']);
  $phone = clean($_POST['phone']);
  $altphone = clean($_POST['altphone']);
  $address = clean($_POST['address']);
  $city = clean($_POST['city']);
  $state = clean($_POST['state']);
  $zip = clean($_POST['zip']);
  $email = clean($_POST['email']);

  $sql = "INSERT INTO users (uid, name, phone, altphone, address, city, state, zip, email) VALUES(NULL, '$name', '$phone', '$altphone', '$address', '$city', '$state', '$zip', '$email')";

  $result = mysql_query($sql);

    if (mysql_affected_rows() == 1){
      $result = "The data has been succesfully added to the database.";
    }
    else {
      $result = "Something went wrong. Make sure the name doesn't already exists in the database! If the problem persists, ask me if I can put a better error message here.";
    }
    require_once('infopage.php');
  }
} // Ends insert() function


function update(){
}

?>

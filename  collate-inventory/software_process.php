<?php


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
  if (strlen($_POST['title']) < "1" || 
      strlen($_POST['desc']) < "1" || 
      strlen($_POST['value']) < "1" || 
      strlen($_POST['total']) < "1" || 
      strlen($_POST['inuse']) < "1") { 
    $result = "All fields except are required. Please go back and try again."; 
    require_once('infopage.php'); 
    return;
  } 
  else {

  require_once('include/db_connect.php');

  $title = clean($_POST['title']);
  $desc = clean($_POST['desc']);
  $value = clean($_POST['value']);
  $total = clean($_POST['total']);
  $available = abs($total - clean($_POST['inuse']));
  
  if(!is_numeric($value)){
    $result = "\"$value\" contains more than just numbers. Please only enter numbers for the value.";
    require_once('infopage.php');
    return;
  } 
  
  $sql = "INSERT INTO softwares (sid, title, description, value, total, available) VALUES(NULL, '$title', '$desc', $value, $total, $available)"; // "desc" is special in SQL.

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
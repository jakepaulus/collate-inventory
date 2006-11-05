<?php
/* This script is basically the view portion of user change forms. Input methods are supplied by functions
  * in this script and data is submitted to user_process.php before it hits the database.
  */
  
  
/**
 * This script contains functionality that will be used by every single page that is displayed.
 * It builds the CI array, creates the connection to the db that will be used by the rest of the
 * script, populates $CI['settings'] with settings from the db, and runs Access Control for the
 * program. 
 */
require_once('./include/common.php');
AccessControl(3); // This access level of this script is 3. Please see the documentation in common.php.

$op = $_GET['op'];

switch($op){
	case "edit";
	edit_user();
	break;

	default:
	add_user();
	break;
}

// Table Columns: uid, name, phone, altphone, address, city, state, zip, email

function clean($variable){ // This function needs to be moved to a separate script that will house all user-input cleaning functions.
  $variable = trim(strip_tags(nl2br($variable))); 
  return $variable;
}

function add_user(){
  global $CI;
  require_once('./include/header.php');
  echo "<div id=\"main\">";
  // Display new-user form that posts to user_process.php 
?>
  <h1>Add a user:</h1>
  <form id="new_user" action="user_process.php?op=new" method="post">
    <p>Username:<br />
    <input id="username" name="username" type="text" size="" /></p>
    <p>Telephone Number:<br />
    <input id="phone" name="phone" type="text" size="" /></p>
    <p>Alt. Telephone Number: (optional)<br />
    <input id="altphone" name="altphone" type="text" size="" /></p>
    <p>Street Address:<br />
    <textarea id="address" name="address" rows="2" cols="30"></textarea></p>
    <p>City:<br />
    <input id="city" name="city" type="text" size="" /></p>
    <p>State:<br />
    <input id="state" name="state" type="text" size="" /></p>
    <p>Postal Code:<br />
    <input id="zip" name="zip" type="text" size="" /></p>
    <p>Email Address: (optional)<br />
    <input id="email" name="email" type="text" size="" /></p>
    <input type="submit" value=" Submit " />
  </form>
<?php
  echo "</div>";
  require_once('./include/footer.php');
}

function edit_user(){
// Display pre-populated form that posts to user_process.php
}
?>

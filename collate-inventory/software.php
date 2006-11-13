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


$op = $_GET['op'];

switch($op){
	default:
	add_software();
	break;
}

function add_software(){
  require_once('./include/header.php');

  // Display new-software form that posts to software_process.php 
?>
  <h1>Add Software To Your Library:</h1>
  <br />
  <form id="new_software" action="software_process.php?op=new" method="post">
    <p>Software Title:<br />
    <input id="title" name="title" type="text" size="30" /></p>
    <p>Description of Software:<br />
    <textarea id="desc" name="desc" rows="2" cols="30"></textarea></p>
    <p>Number of licenses you own (0 for infinite):<br />
    <input id="total" name="total" type="text" size="" /></p>
    <input type="submit" value=" Submit " />
  </form>
<?php
  require_once('./include/footer.php');
}
?>

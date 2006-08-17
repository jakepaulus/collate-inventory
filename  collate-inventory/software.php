<?php
/* This script is basically the view portion of user change forms. Input methods are supplied by functions
  * in this script and data is submitted to user_process.php before it hits the database.
  */

$op = $_GET['op'];

switch($op){
	case "edit";
	edit_software();
	break;

	default:
	add_software();
	break;
}

function add_software(){
  require_once('header.php');
  echo "<div id=\"main\">";
  // Display new-software form that posts to software_process.php 
?>
  <h1>Add Software To Your Library:</h1>
  <form name="new_software" action="software_process.php?op=new" method="post">
    <p>Software Title:<br />
    <input id="title" name="title" type="text" size="" /></p>
    <p>Description of Software:<br />
    <textarea id="desc" name="desc" rows="2" cols="30"></textarea></p>
    <p>Value (per license, numbers only):<br />
    <input id="value" name="value" type="text" size="" /></p>
    <p>Number of licenses you own (0 for infinite):<br />
    <input id="total" name="total" type="text" size="" /></p>
    <p>Number of licenses in use:<br />
    <input id="inuse" name="inuse" type="text" size="" value="0" /></p>
    <input type="submit" value=" Submit " />
  </form>
<?php
  echo "</div>";
  require_once('footer.php');
}

function edit_user(){
// Display pre-populated form that posts to software_process.php
}
?>

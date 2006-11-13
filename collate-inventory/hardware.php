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
	
	case "manage";
	manage_hardware();
	break;
	
	default:
	add_hardware();
	break;
}

function add_hardware(){
  global $CI;
  AccessControl('3');
  
  require_once('./include/header.php');
  // Display new-software form that posts to software_process.php 
?>
  <h1>Add Hardware To Your Inventory:</h1>
  <br />
  <form id="new_hardware" action="hardware_process.php?op=new" method="post">
    <p>Hardware type:<br />
    <input id="category" name="category" type="text" size="30" /></p>
    <div id="category_update" class="autocomplete"></div>
      <script type="text/javascript" charset="utf-8">
      // <![CDATA[
        new Ajax.Autocompleter('category','category_update','_category.php');
      // ]]>      
     </script>
    <p>Asset Number: <br />
    <span id="tip" style="display:none;"><i>This number is being generated automatically. You may remove it and type another if you wish as long as it is unique.</i><br /></span>
<?php
if($CI['settings']['autoasset'] == "1") { 
  $sql = 'SELECT MAX(hid) FROM hardwares';
  $result = mysql_query($sql);
  $max_hid = mysql_result($result, 0);
  $asset = dechex($max_hid++)."Y".date('y');
  ?>
    <input id="asset" name="asset" type="text" value="<?php echo $asset; ?>" size="30" /><a href="#" onclick="new Effect.toggle($('tip'),'appear')"><img src="./images/help.png" alt="[?]" /></a></p>
<?php
}
else { ?>
    <input id="asset" name="asset" type="text" size="30" /></p>
<?php } ?>
    <p>Serial Number:<br />
    <input id="serial" name="serial" type="text" size="30" /></p>
    <p>Description:<br />
    <textarea id="description" name="description" rows="4" cols="40"></textarea></p>
   <p>Assign Hardware: (optional)<br />
    <span id="assigntip" style="display:none;"><i>You may optionally specify a username to assign this hardware to. Don't forget to allocate software licenses to this hardware once it is assigned.</i><br /></span>
    <input id="hardwareassignment" name="hardwareassignment" type="text" size="15" /> <a href="#" onclick="new Effect.toggle($('assigntip'),'appear')"><img src="./images/help.png" alt="[?]" /></a></p>
    <div id="hardwareassignment_update" class="autocomplete"></div>
      <script type="text/javascript" charset="utf-8">
      // <![CDATA[
        new Ajax.Autocompleter('hardwareassignment','hardwareassignment_update','_users.php');
      // ]]>      
     </script>    
    <p><input type="submit" value=" Submit " /></p>
  </form>
  
<?php
  require_once('./include/footer.php');
} // This ends the add_hardware function


function manage_hardware(){
global $CI;
  AccessControl('3');
require_once('./include/header.php');
?>

<h1>Retire/Reinstate Hardware</h1>
<br />
<p>Using the form below, you can retire old hardware so that it will no longer be listed in inventory. The hardware will always be present in the 
database and can be returned to service with all of it's history records in tact. If you enter the Asset Number or Serial Number of an asset that is
currently retired, it will be reinstated and assigned to "system."</p>

<form id="new_hardware" action="hardware_process.php?op=inandout" method="post">
   <input id="hardwaresearch" name="hardwaresearch" type="text" size="15" /></p>
    <div id="hardwaresearch_update" class="autocomplete"></div>
      <script type="text/javascript" charset="utf-8">
      // <![CDATA[
        new Ajax.Autocompleter('hardwaresearch','hardwaresearch_update','_hardware.php');
      // ]]>      
     </script>    
  <p><input type="submit" value=" Submit " /></p>
</form>
<?php
require_once('./include/footer.php');
} // This ends the manage_hardware function
?>

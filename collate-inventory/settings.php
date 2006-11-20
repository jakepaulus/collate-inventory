<?php
/**
 * This script contains functionality that will be used by every single page that is displayed.
 * It builds the CI array, creates the connection to the db that will be used by the rest of the
 * script, populates $CI['settings'] with settings from the db, and runs Access Control for the
 * program. 
 */
require_once('./include/common.php');
AccessControl("5"); // The access level required for this page is 5. Please see the documentation for this function in common.php.

$op = $_GET['op'];

switch($op) {
  case "modify";
  process();
  break;
  
  default:
  form();
}

require_once('./include/footer.php');



function form() {
global $CI;
require_once('./include/header.php');
?>

<h1>Settings</h1>
<br />
<p><b>Current Settings are shown by default. Click Reset at the bottom to see current settings again.</b></p>

<form id="settings" action="settings.php?op=modify" method="post">
  <p>Check permissions for a user to view inventory:<br />
  <select name="checklevel1perms">
    <option <?php if($CI['settings']['checklevel1perms'] == "1") { echo "selected=\"selected\""; } ?> value="1">Yes</option>
    <option <?php if($CI['settings']['checklevel1perms'] == "0") { echo "selected=\"selected\""; } ?> value="0">No</option>
  </select></p>
  
  <p>Check permissions for a user to modify inventory records:<br />
  <select name="checklevel3perms">
    <option <?php if($CI['settings']['checklevel3perms'] == "1") { echo "selected=\"selected\""; } ?> value="1">Yes</option>
    <option <?php if($CI['settings']['checklevel3perms'] == "0") { echo "selected=\"selected\""; } ?> value="0">No</option>
  </select></p>
  
  <p>Check permissions for a user to view/modify application settings:<br />
  <select name="checklevel5perms">
    <option <?php if($CI['settings']['checklevel5perms'] == "1") { echo "selected=\"selected\""; } ?> value="1">Yes</option>
    <option <?php if($CI['settings']['checklevel5perms'] == "0") { echo "selected=\"selected\""; } ?> value="0">No</option>
  </select></p>
  
  <p>Authentication type:<br />
  <select name="ldapauth">
    <option <?php if($CI['settings']['ldapauth'] == "1") { echo "selected=\"selected\""; } ?> value="1">LDAP</option>
    <option <?php if($CI['settings']['ldapauth'] == "0") { echo "selected=\"selected\""; } ?> value="0">DB</option>
  </select></p>
  
  <p>Automatically Generate Asset Numbers:<br />
  <select name="autoasset">
    <option <?php if($CI['settings']['autoasset'] == "1") { echo "selected=\"selected\""; } ?> value="1">Yes</option>
    <option <?php if($CI['settings']['autoasset'] == "0") { echo "selected=\"selected\""; } ?> value="0">No</option>
  </select></p>
  
  <p>Collate:Inventory administrator's name:<br />
  <input id="adminname" name="adminname" type="text" size="" value="<?php echo $CI['settings']['adminname']; ?>" /></p>
  
  <p>Collate:Inventory administrator's telephone number:<br />
  <input id="adminphone" name="adminphone" type="text" size="" value="<?php echo $CI['settings']['adminphone']; ?>" /></p>
  
  <p>Collate:Inventory administrator's email address:<br />
  <input id="adminemail" name="adminemail" type="text" size="40" value="<?php echo $CI['settings']['adminemail']; ?>" /></p>
  
  <p><input type="submit" value="Submit" /> <input type="reset" /></p>
  
</form>

<?php
require_once('./include/footer.php');
} // Ends form function

function process() {
  global $CI;
  require_once('./include/header.php');
 
  $checklevel1perms = clean($_POST['checklevel1perms']);
  $checklevel3perms = clean($_POST['checklevel3perms']);
  $checklevel5perms = clean($_POST['checklevel5perms']);
  $adminname = clean($_POST['adminname']);
  $adminphone = clean($_POST['adminphone']);
  $adminemail = clean($_POST['adminemail']);
  $ldapauth = clean($_POST['ldapauth']);
  $autoasset = clean($_POST['autoasset']);
  
  $result = "";
  
  // If someone set checklevel1perms or checklevel3perms to yes without setting higher level permission checks to yes
  // we should still change those higher level permission checks to ON and alert the user that this is taking place.
  if($checklevel1perms == "1" && ($checklevel3perms != "1" || $checklevel5perms != "1")){ 
    $checklevel3perms = "1";
    $checklevel5perms = "1";
    $extraalert = "<p>You have chosen to require permission checks to view inventory details without requiring permission checks to modify inventory data or ".
                      "application settings. To protect your application, permission checks will be required to modify inventory data as well as application settings.</p><br />";
  }
  if($checklevel3perms == "1" && $checklevel5perms != "1"){
    $checklevel5perms = "1";
    $extraalert = "<p>You have chosen to require permission checks to view/modify inventory data but not to change application settings. To protect your ".
		      "application, permission checks will be required to make setting changes to this application.</p><br />";
  }
  
  if($CI['settings']['checklevel1perms'] != $checklevel1perms) {
    if($checklevel1perms == "1") { $value = "WILL"; } else { $value = "WILL NOT"; }
    $sql = "UPDATE settings SET value='$checklevel1perms' WHERE name='checklevel1perms'";
    mysql_query($sql);
    $result .= "Permission $value be verified before allowing a user to view inventory.<br />";
  }
  if($CI['settings']['checklevel3perms'] != $checklevel3perms) {
    if($checklevel3perms == "1") { $value = "WILL"; } else { $value = "WILL NOT"; }
    $sql = "UPDATE settings SET value='$checklevel3perms' WHERE name='checklevel3perms'";
    mysql_query($sql);
    $result .= "Permission $value be verified before allowing a user to modify inventory.<br />";
  }
  if($CI['settings']['checklevel5perms'] != $checklevel5perms) {
    if($checklevel5perms == "1") { $value = "WILL"; } else { $value = "WILL NOT"; }
    $sql = "UPDATE settings SET value='$checklevel5perms' WHERE name='checklevel5perms'";
    mysql_query($sql);
    $result .= "Permission $value be verified before allowing a user to modify Collate:Inventory settings.<br />";
  }
  if($CI['settings']['adminname'] != $adminname) {
    $sql = "UPDATE settings SET value='$adminname' WHERE name='adminname'";
    mysql_query($sql);
    $result .= "The administrator's name is now recorded as \"$adminname\"<br />";
  }
  if($CI['settings']['adminphone'] != $adminphone) {
    $sql = "UPDATE settings SET value='$adminphone' WHERE name='adminphone'";
    mysql_query($sql);
    $result .= "The administrator's telephone number is now recorded as \"$adminphone\"<br />";
  }
  if($CI['settings']['adminemail'] != $adminemail) {
    $sql = "UPDATE settings SET value='$adminemail' WHERE name='adminemail'";
    mysql_query($sql);
    $result .= "The administrator's email address is now recorded as \"$adminemail\"<br />";
  }
  if($CI['settings']['ldapauth'] != $ldapauth) {
    if($ldapauth == "1") { $value = "LDAP"; } else { $value = "DB"; }
    $sql = "UPDATE settings SET value='$ldapauth' WHERE name='ldapauth'";
    mysql_query($sql);
    $result .= "Authenticatication type is now $value.<br />";
  }
  if($CI['settings']['autoasset'] != $autoasset) {
    if($autoasset == "1") { $value = "ON"; } else { $value = "OFF"; }
    $sql = "UPDATE settings SET value='$autoasset' WHERE name='autoasset'";
    mysql_query($sql);
    $result .= "Automatic asset number generation is now $value.<br />";
  }

  require_once('./include/infopage.php');

} // Ends process function
?>

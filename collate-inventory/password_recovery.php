<?php
require_once('./include/common.php');
require_once('./include/header.php');

// If the applications settings do not require any user to login, no one should bother with this page
if($CI['settings']['checklevel5perms'] === "0") { 
  $notice = "Your current application settings do not require a user to login to perform administrative tasks.";
  header("Location: index.php?notice=$notice"); 
}

if(isset($_GET['op'])){
  $op = $_GET['op'];
}
else {
  $op = "show form";
}
switch($op) { 
  default:
  ci_passwd_recovery();
  break;
}


function ci_passwd_recovery() {
  global $CI;
  if(isset($_GET['action'])){
    $action = clean($_GET['action']);
  }
  else{
    $action = "show form";
  }
  if(isset($_GET['returnto'])){
    $returnto = $_GET['returnto'];
  }
  else {
    $returnto = "";
  }
  
  if(isset($_SESSION['username'])) { // The user is already logged in
    $result = "You are already logged in as ".$CI['user']['username'].". If you would like to logout above.";
	require_once('./include/infopage.php');
  }
  
  if($action != "reset") {

  ?>
  <h1>Password Recovery:</h1>
  <br />
  <form action="password_recovery.php?action=reset" method="post">
  <p><b>Username:</b><br />
  <input name="username" type="text" size="15" /></p>
  <p><b>New Password:</b><br />
  <input name="password" type="password" size="15" /></p>  
  <p><b>Confirm New Password:</b><br />
  <input name="confirm" type="password" size="15" /></p>  
  <p><input type="submit" value=" Go " /></p>
  </form>
  <?php
  require_once('./include/footer.php');
  exit();
  }
  
  $username = clean($_POST['username']);
  $password = sha1(clean($_POST['password']));
  $confirm = sha1(clean($_POST['confirm']));
  
  if(strlen($username) < "4" || strlen($password) < $CI['settings']['passwdlength']){
    $notice = "The username and/or password you have entered is not long enough to be valid.";
    header("Location: password_recovery.php?notice=$notice");
	exit();
  }
  
  if($password != $confirm){
    $notice = "The new password and confirm password you've entered do not match. Please try again.";
    header("Location: login.php?notice=$notice");
	exit();
  }
  
  $sql = "SELECT uid FROM users WHERE username='$username'";
  $test = mysql_query($sql);
  if(mysql_num_rows($test) != "1"){
    $notice = "The username you have entered is not valid.";
    header("Location: login.php?notice=$notice");
	exit();
  }
  
  
 
  // If they have gotten this far, they entered a valid username and password
  $now = date('Y-m-d H:i:s');
  if($CI['settings']['accountexpire'] != "0"){
    $then = $CI['settings']['accountexpire']; // Get number of days from settings
    $expireat = strtotime("+$then days"); // strtotime is awesome!
	$expireat = date("Y-m-d H:i:s", $expireat); // Format the result to match MySQL's datetime format. 
  }
  else{
    $expireat = "0000-00-00 00:00:00";
  }
  $sql = "UPDATE users SET passwd='$password', accesslevel='5', loginattempts='0', passwdexpire='$expireat' WHERE username='$username'";
  $test = mysql_query($sql);
  if(mysql_affected_rows() != "1"){
    $notice = "There was a problem resetting the password for the username you entered. If this problem continues, you will need to ".
	          "manually change the settings for checklevel5perms to 0 in the database in order to get back in to your application.";
    header("Location: login.php?notice=$notice");
	exit();
  }

  $notice = "The password for $username was successfully changed. Please remove the password_recovery.php script from the web directory on your server.";
  header("Location: index.php?notice=$notice"); 

} // Ends ci_password_recovery function



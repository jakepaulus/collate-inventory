<?php
require_once('./include/common.php');
require_once('./include/header.php');
 $accesslevel = "1";
  $message = "control panel accessed";
  AccessControl($accesslevel, $message); 

?>
<br />
<table width="100%">
<tr>
<td align="center" style="width: 25%"><a href="./users.php"><img height="48" width="48" alt="Users" src="./images/users.gif" /><br /></a><b>Manage Users</b></td>
<td align="center" style="width: 25%"><a href="./hardware.php"><img height="48" width="48" alt="Hardware" src="./images/hardware.gif" /></a><br /><b>Manage Hardware</b></td>
<td align="center" style="width: 25%"><a href="./software.php"><img height="48" width="48" alt="Users" src="./images/software.gif" /><br /></a><b>Manage Software</b></td>
<td align="center" style="width: 25%"><a href="./sites.php"><img height="48" width="48" alt="Sites" src="./images/sites.gif" /></a><br /><b>Manage Sites</b></td>
</tr>
<tr><td><br /></td></tr>
<tr>
<td align="center" style="width: 25%"><a href="./docs/index.php"><img height="48" width="48" alt="[?]" src="./images/help_large.gif" /></a><br />
<b>Documentation</b></td>
<td align="center" style="width: 25%"><a href="./logs.php"><img height="48" width="48" alt="[?]" src="./images/logs.gif" /></a><br />
<b>Logs</b></td>

<?php if(isset($_SESSION['username'])){ ?>
<td align="center" style="width: 25%"><a href="./login.php?op=changepasswd"><img height="48" width="48" alt="[?]" src="./images/password.gif" /></a><br />
<b>Change Your Password</b></td>
<?php } ?>

<?php if($CI['user']['accesslevel'] == "5" || $CI['settings']['checklevel5perms'] === "0"){ ?>
<td align="center" style="width: 25%"><a href="./settings.php"><img height="48" width="48" alt="Settings" src="./images/settings.gif" /></a><br /><b>Settings</b></td>
<?php } ?>

</tr>
</table>
<br />
<?
require_once('./include/footer.php');
?>
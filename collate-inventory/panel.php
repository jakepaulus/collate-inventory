<?php
/**
 * This script contains functionality that will be used by every single page that is displayed.
 * It builds the CI array, creates the connection to the db that will be used by the rest of the
 * script, populates $CI['settings'] with settings from the db, and runs Access Control for the
 * program. 
 */
require_once('./include/common.php');
AccessControl('3'); // The access level of this script is 3. Please see the documentation for this function in common.php.
require_once('./include/header.php');
?>
<br />
<table width="100%">
<tr>
<td align="center" style="width: 25%"><a href="./users.php"><img height="48" width="48" alt="Users" src="./images/users.png" /><br /></a><b>Manage Users</b></td>
<td align="center" style="width: 25%"><a href="./hardware.php"><img height="48" width="48" alt="Hardware" src="./images/hardware.png" /></a><br /><b>Manage Hardware</b></td>
<td align="center" style="width: 25%"><a href="./software.php?op=manage"><img height="48" width="48" alt="Users" src="./images/software.png" /><br /></a><b>Manage Software</b></td>
<td align="center" style="width: 25%"><a href="./sites.php"><img height="48" width="48" alt="Sites" src="./images/sites.png" /></a><br /><b>Manage Sites</b></td>
</tr>
<tr><td><br /></td></tr>
<tr>
<td align="center" style="width: 25%"><a href="./docs/index.php"><img height="48" width="48" alt="[?]" src="./images/help_large.png" /></a><br />
<b>Documentation</b></td>
<td align="center" style="width: 25%"><a href="./logs.php"><img height="48" width="48" alt="[?]" src="./images/logs.png" /></a><br />
<b>Logs</b></td>
<td align="center" style="width: 25%"><?php if($CI['user']['accesslevel'] == "5" || $CI['settings']['checklevel5perms'] === "0"){ ?><a href="./settings.php"><img height="48" width="48" alt="Settings" src="./images/settings.png" /></a><br /><b>Settings</b><?php } ?></td>
</tr>
</table>
<br />
<?
require_once('./include/footer.php');
?>
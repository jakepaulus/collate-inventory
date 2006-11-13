<?php
/**
 * This script contains functionality that will be used by every single page that is displayed.
 * It builds the CI array, creates the connection to the db that will be used by the rest of the
 * script, populates $CI['settings'] with settings from the db, and runs Access Control for the
 * program. 
 */
require_once('./include/common.php');
require_once('./include/header.php');

require_once('./include/footer.php');
?>

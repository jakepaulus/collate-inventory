<?php 
/**
 * This script contains functionality that will be used by every single page that is displayed.
 * It builds the CI array, creates the connection to the db that will be used by the rest of the
 * script, populates $CI['settings'] with settings from the db, and runs Access Control for the
 * program. 
 */
require_once('./include/common.php');
AccessControl(0); // The access level of this script is 0. Please see the documentation for this function in common.php.

require_once('header.php'); 
?>

<div id="main">
  <h1>Welcome !</h1>
    <br />
    <h3>About Collate</h3>
      <p> 
      Collate is a collection of applications that will help people manage IT information. Future titles to look for are 
      Collate:Helpdesk and it's companion application Collate:KnowledgeBase.
      </p>
  
    <h3>About Collate:Inventory</h3>
      <p> 
      With this application you can easily organize your hardware and software inventory. This application runs great locally for a 
      single user or on a network with multiple users.
      </p>
      
    <h3>Documentation</h3>
      <p>
      Documentation for this application can be found in the docs directory that came with this distribution. If this directory was not
      deleted during installation, you can read the documenation by clicking <a href="docs/index.php">this link</a>. You can also
      view the documentation online at <a href="http://collate.info/">Collate.info</a>.
      </p>
      
</div>
<?php require_once('footer.php'); ?>

     

<?php 
require_once('./include/common.php');
require_once('./include/header.php');
 
?>

  <h1>Welcome <?php if(isset($_SESSION['username'])){ echo $_SESSION['username']; } ?>!</h1>
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
      Documentation for this application can be found in the docs directory that came with this distribution. You can read the 
	  documenation by clicking <a href="docs/documentation.txt">this link</a>. You can also view the documentation online at 
	  <a href="http://collate.info/">Collate.info</a>.
      </p>

<?php require_once('./include/footer.php'); ?>

     

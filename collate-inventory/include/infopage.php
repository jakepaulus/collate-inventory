<?php
// This page is used to display general error messages.
if(empty($result)){return;} // Make sure people don't access this page directly.
?>
<div id="main">
  <h1>Notice:</h1>
    <?php echo $extraalert; ?>
  <br />
  <p>
    <?php echo $result; ?>
  </p>	
</div>
<?php
require_once('./include/footer.php');
end;
?>

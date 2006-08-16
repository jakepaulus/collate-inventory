<?php
// This page is used to display general error messages.

if(empty($result)){return;} // Make sure people doesn't access this page directly.

require_once('header.php');
?>
<div id="main">
  <h1>Form Submission Result:</h1>
  <br />
  <p>
    <?php echo $result; ?>
  </p>	
</div>
<?php
require_once('footer.php');
end;
?>

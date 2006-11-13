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
AccessControl(3); // This access level of this script is 3. Please see the documentation in common.php.

$op = $_GET['op'];

switch($op){
	case "manage";
	manage_user();
	break;

	default:
	add_user();
	break;
}

// Table Columns: uid, name, phone, altphone, address, city, state, zip, email

function clean($variable){ // This function needs to be moved to a separate script that will house all user-input cleaning functions.
  $variable = trim(strip_tags(nl2br($variable))); 
  return $variable;
}

function add_user(){
  global $CI;
  require_once('./include/header.php');
  // Display new-user form that posts to user_process.php 
?>
 <script type="text/javascript" charset="utf-8">// <![CDATA[

Effect.divSwap = function(element,container){
    var div = document.getElementById(container);
    var nodeList = div.childNodes;
    var queue = Effect.Queues.get('menuScope');

    if(queue.toArray().length<1){
        if(Element.visible(element)==false){
            for(i=0;i<nodeList.length;i++){
                if(nodeList.item(i).nodeName=="DIV" && nodeList.item(i).id!=element){
                    if(Element.visible(nodeList.item(i))==true){
                        Effect.SwitchOff(nodeList.item(i),{queue:{position:'end',scope:'menuScope',limit:2}})
                    }
                }
            }
            Effect.Appear(element,{queue:{position:'end',scope:'menuScope',limit:2}})
       }
   }
}
     // ]]>      
</script>
  <h1>Add a user:</h1>
  <form id="new_user" action="user_process.php?op=new" method="post">
    <p>Username:<br />
    <input id="username" name="username" type="text" size="" /></p>
    <p>Telephone Number:<br />
    <input id="phone" name="phone" type="text" size="" /></p>
    <p>Alt. Telephone Number: (optional)<br />
    <input id="altphone" name="altphone" type="text" size="" /></p>
    <p>Please choose the location type:<br />
    <a href="#" onclick="Effect.divSwap('address_fields','swapable');"><img src="./images/home_small.png" alt="Remote" /></a> &nbsp; 
    <a href="#" onclick="Effect.divSwap('site','swapable');"><img src="./images/sites_small.png" alt="Site" /></a></p>
<div id="swapable">
    <div id="site">
       <p>Site:<br />
      <span id="sitetip" style="display:none;"><i>This field will autocomplete. If there is no site already listed for the one you'd like to associate this user with, please add the site using Manage Sites in the Control Panel.</i><br /></span>
      <input id="sitesearch" name="sitesearch" type="text" size="15" /><a href="#" onclick="new Effect.toggle($('sitetip'),'appear')"><img src="./images/help.png" alt="[?]" /></a></p>
      <div id="sitesearch_update" class="autocomplete"></div>
      <script type="text/javascript" charset="utf-8">
      // <![CDATA[
        new Ajax.Autocompleter('sitesearch','sitesearch_update','_sites.php');
      // ]]>      
     </script>
    </div>
    <div id="address_fields" style="display:none;">
      <p>Street Address:<br />
      <textarea id="address" name="address" rows="2" cols="30"></textarea></p>
      <p>City:<br />
      <input id="city" name="city" type="text" size="" /></p>
      <p>State/Province:<br />
      <input id="state" name="state" type="text" size="" /></p>
      <p>Postal Code:<br />
      <input id="zip" name="zip" type="text" size="" /></p>
    </div>
</div>    
    <p>Email Address: (optional)<br />
    <input id="email" name="email" type="text" size="" /></p>
    <p>Assign Hardware: (optional)<br />
    <span id="hardwaretip" style="display:none;"><i>You may optionally specify the asset/serial number of an asset to assign it to this user. Don't forget to allocate software licenses to this hardware once it is assigned.</i><br /></span>
    <input id="hardwaresearch" name="hardwaresearch" type="text" size="15" /> <a href="#" onclick="new Effect.toggle($('hardwaretip'),'appear')"><img src="./images/help.png" alt="[?]" /></a></p>
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
}

function manage_user(){
  global $CI;
  require_once('./include/header.php');
  ?>
  <h1>Manage User</h1>
  <?php
  require_once('./include/footer.php');
} // This ends the manage_user function
?>

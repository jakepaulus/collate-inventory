<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <title>Collate:Inventory</title>
    
    <meta http-equiv="content-type" content="application/xhtml+xml; charset=iso-8859-1" />
    
    <meta name="generator" content="Jake Paulus" />
    <meta name="description" content="Organize your hardware and software inventory records" />
    <meta name="keywords" content="hardware,software,inventory,users" />
   
<?php 
global $CI;
echo $extrameta;

// Make sure we supply the correct css for the view the user is requesting and we don't load those libraries if we don't have to.
if($_GET['view'] == "printable"){ ?>
<link rel="stylesheet" type="text/css" href="css/print.css" />
<?php } else { ?>
<link rel="stylesheet" type="text/css" href="css/bluesky.css" />
<script src="javascripts/scriptaculous.shrunk.js" type="text/javascript" charset="ISO-8859-1"></script>
<?php } ?>

</head>
<body id="collate-inventory">

<div id="page">
    
    <div id="header">
        <a href="index.php">Collate:Inventory</a>&nbsp;
    </div>
 

        <div id="content">
	
<div class="path">
  <table width="100%">
    <tr><td align="left">
      <?php if($CI['settings']['checklevel5perms'] == "0" || $CI['user']['accesslevel'] == "3") {  echo "<a href=\"panel.php\">Control Panel</a> | "; 
     }  
     ?><a href="search.php">Search</a> </td><td align="right"><a href="<?php
     // This little mess here makes sure that the print URL is formed properly.

    echo "http://".$_SERVER['SERVER_NAME'].htmlentities($_SERVER['REQUEST_URI']); 
    if(stristr($_SERVER['REQUEST_URI'], "?") == TRUE){ 
      echo "&amp;"; 
    } 
    else {
      echo "?";
    }
    ?>view=printable">printable</a>&nbsp;</td></tr>
</table>
    </div>
<div id="main">

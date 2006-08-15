<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <title>Collate:Inventory</title>
    
    <meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
    
    <meta name="generator" content="Jake Paulus" />
    <meta name="description" content="Organize your hardware and software inventory records" />
    <meta name="keywords" content="hardware,software,inventory,users" />
   
<?php if($_GET['view'] == "printable"){ ?>
<link rel="stylesheet" type="text/css" href="css/print.css" />
<?php } else { ?>
<link rel="stylesheet" type="text/css" href="css/bluesky.css" />
<script src="javascripts/prototype.js" type="text/javascript"></script>
<script src="javascripts/scriptaculous.js" type="text/javascript"></script>
<?php } ?>

</head>
<body id="collate-inventory">

<div id="page">
    
    <div id="header">
        <a href="index.php">Collate:Inventory</a>&nbsp;
    </div>
    <div id="wrapper"> 

        <div id="content">
	
<div class="path"><a href="<?php echo $_SERVER['REQUEST_URI']; if(stristr($_SERVER['REQUEST_URI'], "?") == TRUE){ echo "&amp;"; } else echo "?"; ?>view=printable">printable</a>&nbsp;
    </div>


<?php
connectToDB();

function connectToDB() {

//database host
$db_host = "localhost";

//database username
$db_user = "root";

//database password
$db_pass = "root";

//database
$db_name = "collate";

($link = mysql_pconnect("$db_host", "$db_user", "$db_pass")) || die("Couldn't connect to MySQL");

// select db:
mysql_select_db($db_name, $link) || die("Couldn't open db: $db_name. Error if any was: ".mysql_error() );
} 
?>

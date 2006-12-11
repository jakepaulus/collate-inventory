<?php

require_once('./include/db_connect.php');

$sql = 
"
DROP TABLE IF EXISTS `hardware`;;
CREATE TABLE `hardware` (
  `coid` int(11) NOT NULL auto_increment,
  `username` varchar(30) NOT NULL,
  `hid` int(11) NOT NULL,
  `site` varchar(50) NOT NULL default 'system',
  `codate` datetime NOT NULL,
  `cidate` datetime NOT NULL,
  PRIMARY KEY  (`coid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Hardware activity records' AUTO_INCREMENT=1 ;;

DROP TABLE IF EXISTS `hardwares`;;
CREATE TABLE `hardwares` (
  `hid` int(11) NOT NULL auto_increment,
  `category` varchar(50) NOT NULL,
  `asset` varchar(64) NOT NULL,
  `serial` varchar(128) NOT NULL,
  `description` text NOT NULL,
  `username` varchar(201) default 'system',
  PRIMARY KEY  (`hid`),
  UNIQUE KEY `asset` (`asset`),
  KEY `serial` (`serial`),
  FULLTEXT KEY `desccription` (`description`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='List of hardware' AUTO_INCREMENT=1 ;;

DROP TABLE IF EXISTS `logs`;;
CREATE TABLE `logs` (
  `lid` tinyint(11) NOT NULL auto_increment,
  `occuredat` datetime NOT NULL,
  `username` varchar(30) NOT NULL default 'system',
  `ipaddress` varchar(15) NOT NULL,
  `level` varchar(6) NOT NULL,
  `message` varchar(255) NOT NULL,
  PRIMARY KEY  (`lid`),
  KEY `username` (`username`,`message`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;;

DROP TABLE IF EXISTS `settings`;;
CREATE TABLE `settings` (
  `name` varchar(50) NOT NULL,
  `value` varchar(50) NOT NULL,
  PRIMARY KEY  (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Collate:Inventory settings';;

INSERT INTO `settings` VALUES ('checklevel5perms', '0');;
INSERT INTO `settings` VALUES ('checklevel3perms', '0');;
INSERT INTO `settings` VALUES ('checklevel1perms', '0');;
INSERT INTO `settings` VALUES ('adminname', '');;
INSERT INTO `settings` VALUES ('adminemail', '');;
INSERT INTO `settings` VALUES ('adminphone', '');;
INSERT INTO `settings` VALUES ('autoasset', '1');;
INSERT INTO `settings` VALUES ('ldapauth', '0');;
INSERT INTO `settings` VALUES ('passwdlength', '5');;
INSERT INTO `settings` VALUES ('accountexpire', '60');;
INSERT INTO `settings` VALUES ('loginattempts', '4');;
INSERT INTO `settings` VALUES ('version', '1.0');;

DROP TABLE IF EXISTS `sites`;;
CREATE TABLE `sites` (
  `sid` int(11) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(75) NOT NULL,
  `state` varchar(50) NOT NULL,
  `zip` varchar(15) NOT NULL,
  PRIMARY KEY  (`sid`),
  UNIQUE KEY `name` (`name`),
  KEY `city` (`city`,`state`,`zip`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='List of locations' AUTO_INCREMENT=1 ;;

DROP TABLE IF EXISTS `software`;;
CREATE TABLE `software` (
  `coid` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `hid` int(11) NOT NULL,
  `codate` datetime NOT NULL,
  `cidate` datetime NOT NULL,
  PRIMARY KEY  (`coid`),
  KEY `uid` (`hid`),
  KEY `sid` (`title`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Software activity records' AUTO_INCREMENT=1 ;;

DROP TABLE IF EXISTS `softwares`;;
CREATE TABLE `softwares` (
  `sid` int(11) NOT NULL auto_increment,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `total` int(11) NOT NULL,
  `inuse` int(11) NOT NULL default '0',
  PRIMARY KEY  (`sid`),
  UNIQUE KEY `title` (`title`),
  FULLTEXT KEY `description` (`description`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='List of software titles' AUTO_INCREMENT=1 ;;

DROP TABLE IF EXISTS `users`;;
CREATE TABLE `users` (
  `uid` int(11) NOT NULL auto_increment,
  `username` varchar(30) NOT NULL,
  `passwd` varchar(40) NOT NULL,
  `tmppasswd` varchar(40) default NULL,
  `accesslevel` tinyint(1) NOT NULL default '0',
  `phone` varchar(25) NOT NULL,
  `altphone` varchar(25) NOT NULL,
  `address` varchar(100) NOT NULL,
  `city` varchar(75) NOT NULL,
  `state` varchar(75) NOT NULL,
  `zip` varchar(10) NOT NULL,
  `site` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `loginattempts` tinyint(1) NOT NULL,
  `passwdexpire` datetime NOT NULL,
  PRIMARY KEY  (`uid`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='User Table' AUTO_INCREMENT=2 ;;

INSERT INTO `users` (uid, username) VALUES ('0', 'system');;
UPDATE `users` SET `uid` = '0' WHERE username='system'
";

$results = multiple_query($sql);

$tok = strtok($results, "<br />");

if($tok){ // There were erors.
    ?>
  <html>
  <head>
    <title>Error!</title>
  </head>
  <body>
  <h1>An error has occured.</h1>
  <p>Below you will find the mysql erros that prevented this application from installing properly.</p>
  <?php
  echo $results;
  ?>
  </body>
  </html>
  <?php
}
else{ // Everything went well.
  $notice = "This application has been successfully installed. Please delete install.php from your web server.";
  header("Location: index.php?notice=$notice");
}

function multiple_query($sql)
   {
   $tok = strtok($sql, ";;");
   while ($tok)
       {
       mysql_query($tok);
	   $results = mysql_error()."<br />$results<br /><br />";
       $tok = strtok(";;");
       }
   return $results;
   }
   
?>
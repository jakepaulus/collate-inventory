CREATE DATABASE `collate` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE collate;

CREATE TABLE `sites` (
  `sid` INT( 10 ) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR( 75 ) NOT NULL ,
  `address` VARCHAR( 255 ) NOT NULL ,
  `city` VARCHAR( 75 ) NOT NULL ,
  `state` VARCHAR( 75 ) NOT NULL ,
  `zip` VARCHAR( 25 ) NOT NULL ,
  PRIMARY KEY ( `lid` ) ,
  INDEX ( `city` , `state` , `zip` ) ,
  UNIQUE ( `name` )
) TYPE = MYISAM COMMENT = 'List of locations' ;


CREATE TABLE `hardware` (
  `coid` int(10) NOT NULL AUTO_INCREMENT,
  `username` varchar( 201 ) NOT NULL,
  `hid` int(10) NOT NULL,
  `location` varchar( 75 ) NOT NULL,
  `codate` datetime NOT NULL,
  `cidate` datetime NOT NULL,
  PRIMARY KEY (`coid`),
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Hardware activity records' ;


CREATE TABLE `hardwares` (
  `hid` int(10) NOT NULL auto_increment,
  `category` varchar(100) NOT NULL,
  `asset` varchar(255) NOT NULL,
  `serial` varchar(255) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY  (`hid`),
  UNIQUE KEY `asset` (`asset`),
  KEY `serial` (`serial`),
  FULLTEXT KEY `desccription` (`description`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='List of hardware' ;


CREATE TABLE `settings` (
  `name` VARCHAR( 255 ) NOT NULL,
  `value` VARCHAR( 255 ) NOT NULL,
PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Settings table' ;


CREATE TABLE `software` (
  `coid` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `uid` int(10) NOT NULL,
  `codate` datetime NOT NULL,
  `cidate` datetime NOT NULL,
  PRIMARY KEY  (`coid`),
  KEY `uid` (`uid`),
  KEY `sid` (`sid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Software activity records' ;


CREATE TABLE `softwares` (
  `sid` int(10) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `total` int(10) NOT NULL,
  `inuse` int(10) DEFAULT '0' NOT NULL,
  PRIMARY KEY  (`SID`),
  UNIQUE KEY `title` (`title`),
  FULLTEXT KEY `description` (`description`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='List of software titles' ;

CREATE TABLE `logs` (
`lid` TINYINT( 11 ) NOT NULL AUTO_INCREMENT ,
`date` DATETIME NOT NULL ,
`username` VARCHAR( 30 ) DEFAULT 'system' NOT NULL ,
`level` TINYINT( 1 ) NOT NULL ,
`message` VARCHAR( 255 ) NOT NULL ,
PRIMARY KEY ( `lid` ) ,
INDEX ( `username` , `message` )
) TYPE = MYISAM ;

CREATE TABLE `users` (
  `uid` int(10) NOT NULL auto_increment,
  `username` varchar(30) NOT NULL,
  `passwd` varchar(32) NOT NULL,
  `tmppasswd` varchar(32) NOT NULL,
  `accesslevel` TINYINT(1) DEFAULT '0' NOT NULL
  `phone` varchar(25) NOT NULL,
  `altphone` varchar(25) NOT NULL,
  `address` varchar(100) NOT NULL,
  `city` varchar(75) NOT NULL,
  `state` varchar(75) NOT NULL,
  `zip` varchar(10) NOT NULL,
  `site` int(10) NOT NULL,
  `email` varchar(255) NOT NULL,
  PRIMARY KEY  (`uid`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='User Table' ;

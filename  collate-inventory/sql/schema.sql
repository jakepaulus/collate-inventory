CREATE DATABASE `collate` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE collate;


CREATE TABLE `appusers` (
  `auid` INT( 10 ) NOT NULL AUTO_INCREMENT , 
  `uid` INT( 10 ) NOT NULL ,
  `username` VARCHAR( 75 ) NOT NULL,
  `password` VARCHAR( 75 ) NOT NULL,
  PRIMARY KEY ( `auid` ) , 
  UNIQUE ( `username` )
) TYPE = MYISAM COMMENT = 'List of C:I users' ;


CREATE TABLE `locations` (
  `lid` INT( 10 ) NOT NULL AUTO_INCREMENT ,
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
  `coid` int(10) NOT NULL,
  `uid` int(10) NOT NULL,
  `hid` int(10) NOT NULL,
  `codate` datetime NOT NULL,
  `cidate` datetime NOT NULL,
  PRIMARY KEY (`coid`),
  KEY `uid` (`uid`),
  KEY `hid` (`hid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Hardware activity records' ;


CREATE TABLE `hardwares` (
  `hid` int(10) NOT NULL auto_increment,
  `catid` int(10) NOT NULL,
  `asset` varchar(255) NOT NULL,
  `serial` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `value` varchar(50) NOT NULL,
  PRIMARY KEY  (`hid`),
  UNIQUE KEY `asset` (`asset`),
  KEY `serial` (`serial`),
  KEY `catid` (`catid`),
  FULLTEXT KEY `desccription` (`description`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='List of hardware' ;


CREATE TABLE `hcats` (
  `catid` INT( 10 ) NOT NULL AUTO_INCREMENT ,
  `catname` VARCHAR( 255 ) NOT NULL ,
  PRIMARY KEY ( `catid` ) ,
  UNIQUE ( `catname` )
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Hardware categories' ;


CREATE TABLE `settings` (
  `name` VARCHAR(100) NOT NULL,
  `value` VARCHAR(100) NOT NULL,
PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Settings table' ;


CREATE TABLE `software` (
  `coid` int(10) NOT NULL,
  `sid` int(10) NOT NULL,
  `uid` int(10) NOT NULL,
  `issued` datetime NOT NULL,
  `returned` datetime NOT NULL,
  PRIMARY KEY  (`coid`),
  KEY `uid` (`uid`),
  KEY `sid` (`sid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Software activity records' ;


CREATE TABLE `softwares` (
  `sid` int(10) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `total` int(10) NOT NULL,
  `available` int(10) NOT NULL,
  PRIMARY KEY  (`SID`),
  UNIQUE KEY `name` (`title`),
  FULLTEXT KEY `description` (`description`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='List of software titles' ;


CREATE TABLE `users` (
  `uid` int(10) NOT NULL auto_increment,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `phone` varchar(25) NOT NULL,
  `altphone` varchar(25) NOT NULL,
  `address` varchar(100) NOT NULL,
  `city` varchar(75) NOT NULL,
  `state` varchar(75) NOT NULL,
  `zip` varchar(25) NOT NULL,
  `lid` int(10) NOT NULL,
  `email` varchar(255) NOT NULL,
  PRIMARY KEY  (`uid`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='User Table' ;


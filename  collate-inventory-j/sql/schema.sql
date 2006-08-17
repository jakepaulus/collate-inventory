-- phpMyAdmin SQL Dump
-- version 2.6.4-pl4
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Aug 16, 2006 at 07:50 PM
-- Server version: 5.0.17
-- PHP Version: 5.1.1
-- 
-- Database: `collate`
-- 
CREATE DATABASE `collate` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE collate;

-- --------------------------------------------------------

-- 
-- Table structure for table `hardware`
-- 

CREATE TABLE `hardware` (
  `hid` int(10) NOT NULL auto_increment,
  `asset` varchar(255) NOT NULL,
  `serial` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `desc` text NOT NULL,
  `value` varchar(50) NOT NULL,
  `uid` int(10) NOT NULL,
  `assigned` datetime NOT NULL,
  `returned` datetime NOT NULL,
  PRIMARY KEY  (`hid`),
  UNIQUE KEY `asset` (`asset`),
  KEY `serial` (`serial`),
  FULLTEXT KEY `desc` (`desc`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Hardware Table' ;

-- --------------------------------------------------------

-- 
-- Table structure for table `software`
-- 

CREATE TABLE `software` (
  `sid` int(10) NOT NULL,
  `uid` int(10) NOT NULL,
  `issued` datetime NOT NULL,
  `returned` datetime NOT NULL,
  PRIMARY KEY  (`sid`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Software activity records' ;

-- --------------------------------------------------------

-- 
-- Table structure for table `softwares`
-- 

CREATE TABLE `softwares` (
  `SID` int(10) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `desc` text NOT NULL,
  `total` int(10) NOT NULL,
  `available` int(10) NOT NULL,
  PRIMARY KEY  (`SID`),
  UNIQUE KEY `name` (`title`),
  FULLTEXT KEY `desc` (`desc`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='List of software titles' ;

-- --------------------------------------------------------

-- 
-- Table structure for table `users`
-- 

CREATE TABLE `users` (
  `uid` int(10) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `phone` varchar(25) NOT NULL,
  `altphone` varchar(25) NOT NULL,
  `address` varchar(100) NOT NULL,
  `city` varchar(75) NOT NULL,
  `state` varchar(75) NOT NULL,
  `zip` varchar(25) NOT NULL,
  `email` varchar(255) NOT NULL,
  PRIMARY KEY  (`uid`),
  UNIQUE KEY `name` (`name`,`email`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='User Table' ;


-- phpMyAdmin SQL Dump
-- version 3.3.7deb7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 28, 2016 at 04:17 PM
-- Server version: 5.5.38
-- PHP Version: 5.4.45-1~dotdeb+6.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `spottedunitodev`
--

-- --------------------------------------------------------

--
-- Table structure for table `fifo`
--

CREATE TABLE IF NOT EXISTS `fifo` (
  `spotted_ID` int(10) unsigned NOT NULL,
  `spotter_ID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`spotted_ID`,`spotter_ID`),
  KEY `spotter_ID` (`spotter_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `spotted`
--

CREATE TABLE IF NOT EXISTS `spotted` (
  `spotted_ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `spotted_datetime` datetime NOT NULL,
  `spotted_message` text NOT NULL,
  `spotted_chat_id` int(10) NOT NULL COMMENT 'Created by Telegram chat_id',
  PRIMARY KEY (`spotted_ID`),
  KEY `spottedfifo_chat_id` (`spotted_chat_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=92 ;

-- --------------------------------------------------------

--
-- Table structure for table `spotter`
--

CREATE TABLE IF NOT EXISTS `spotter` (
  `spotter_ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `spotter_chat_id` int(11) NOT NULL COMMENT 'Telegram chat_id',
  `spotter_datetime` datetime NOT NULL COMMENT 'Subscribed in date',
  PRIMARY KEY (`spotter_ID`),
  KEY `spottedsubscriber_chat_ID` (`spotter_chat_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `fifo`
--
ALTER TABLE `fifo`
  ADD CONSTRAINT `fifo_ibfk_1` FOREIGN KEY (`spotted_ID`) REFERENCES `spotted` (`spotted_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fifo_ibfk_2` FOREIGN KEY (`spotter_ID`) REFERENCES `spotter` (`spotter_ID`) ON DELETE CASCADE;


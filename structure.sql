-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2014 年 10 月 15 日 11:46
-- 服务器版本: 5.5.37-log
-- PHP 版本: 5.5.16

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `cubingchina`
--

-- --------------------------------------------------------

--
-- 表的结构 `competition`
--

CREATE TABLE IF NOT EXISTS `competition` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` char(10) NOT NULL DEFAULT '',
  `wca_competition_id` char(32) NOT NULL DEFAULT '',
  `name` char(50) NOT NULL DEFAULT '',
  `name_zh` char(50) NOT NULL DEFAULT '',
  `alias` char(50) NOT NULL,
  `date` int(11) unsigned NOT NULL,
  `end_date` int(11) unsigned NOT NULL DEFAULT '0',
  `reg_end_day` int(11) unsigned NOT NULL DEFAULT '0',
  `province_id` smallint(3) unsigned NOT NULL DEFAULT '0',
  `city_id` smallint(3) unsigned NOT NULL DEFAULT '0',
  `venue` varchar(512) NOT NULL DEFAULT '',
  `venue_zh` varchar(512) NOT NULL DEFAULT '',
  `events` text NOT NULL,
  `entry_fee` smallint(3) unsigned NOT NULL DEFAULT '0',
  `alipay_url` varchar(512) NOT NULL DEFAULT '',
  `regulations` longtext,
  `regulations_zh` longtext,
  `information` longtext,
  `information_zh` longtext,
  `travel` longtext,
  `travel_zh` longtext,
  `person_num` mediumint(6) unsigned NOT NULL DEFAULT '0',
  `check_person` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `competition_delegate`
--

CREATE TABLE IF NOT EXISTS `competition_delegate` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `competition_id` int(10) unsigned NOT NULL,
  `delegate_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `competition_location`
--

CREATE TABLE IF NOT EXISTS `competition_location` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `competition_id` int(10) unsigned NOT NULL,
  `location_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `province_id` smallint(3) unsigned NOT NULL DEFAULT '0',
  `city_id` smallint(3) unsigned NOT NULL DEFAULT '0',
  `venue` varchar(512) NOT NULL DEFAULT '',
  `venue_zh` varchar(512) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `competition_id` (`competition_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `competition_organizer`
--

CREATE TABLE IF NOT EXISTS `competition_organizer` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `competition_id` int(10) unsigned NOT NULL,
  `organizer_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `competition_id` (`competition_id`),
  KEY `organizer_id` (`organizer_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `delegate`
--

CREATE TABLE IF NOT EXISTS `delegate` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(128) NOT NULL,
  `name_zh` char(128) NOT NULL,
  `email` char(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `logs`
--

CREATE TABLE IF NOT EXISTS `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `level` varchar(128) DEFAULT NULL,
  `category` varchar(128) DEFAULT NULL,
  `logtime` int(11) DEFAULT NULL,
  `message` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `mail`
--

CREATE TABLE IF NOT EXISTS `mail` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `to` text,
  `reply_to` text,
  `cc` text,
  `bcc` text,
  `subject` varchar(256) NOT NULL,
  `message` text NOT NULL,
  `sent` tinyint(1) NOT NULL DEFAULT '0',
  `add_time` int(11) NOT NULL,
  `update_time` int(11) NOT NULL,
  `sent_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `sent` (`sent`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `news`
--

CREATE TABLE IF NOT EXISTS `news` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL,
  `title` varchar(1024) NOT NULL,
  `title_zh` varchar(1024) NOT NULL,
  `content` longtext NOT NULL,
  `content_zh` longtext NOT NULL,
  `date` int(10) unsigned NOT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `Persons`
--

CREATE TABLE IF NOT EXISTS `Persons` (
  `id` varchar(10) NOT NULL DEFAULT '',
  `subId` tinyint(6) NOT NULL DEFAULT '1',
  `name` varchar(80) DEFAULT NULL,
  `countryId` varchar(50) NOT NULL DEFAULT '',
  `gender` char(1) NOT NULL DEFAULT '',
  `year` smallint(6) NOT NULL DEFAULT '0',
  `month` tinyint(4) NOT NULL DEFAULT '0',
  `day` tinyint(4) NOT NULL DEFAULT '0',
  `comments` varchar(40) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`,`subId`),
  KEY `fk_country` (`countryId`),
  KEY `id` (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `region`
--

CREATE TABLE IF NOT EXISTS `region` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` char(128) NOT NULL,
  `name_zh` char(128) NOT NULL DEFAULT '',
  `pid` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `registration`
--

CREATE TABLE IF NOT EXISTS `registration` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `competition_id` int(10) unsigned NOT NULL,
  `location_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL,
  `events` varchar(512) NOT NULL,
  `comments` varchar(2048) NOT NULL DEFAULT '',
  `paid` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `date` int(10) unsigned NOT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `competition_id` (`competition_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `rounds`
--

CREATE TABLE IF NOT EXISTS `rounds` (
  `id` char(1) NOT NULL DEFAULT '',
  `rank` int(11) NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL DEFAULT '',
  `cellName` varchar(45) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `schedule`
--

CREATE TABLE IF NOT EXISTS `schedule` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `competition_id` int(10) NOT NULL,
  `day` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `stage` char(10) NOT NULL DEFAULT 'main',
  `start_time` int(10) unsigned NOT NULL,
  `end_time` int(10) unsigned NOT NULL,
  `event` char(64) NOT NULL,
  `group` char(10) NOT NULL DEFAULT '',
  `format` char(10) NOT NULL,
  `round` char(10) NOT NULL,
  `cut_off` int(10) unsigned NOT NULL,
  `time_limit` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `competition_id` (`competition_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `wcaid` char(10) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `name` char(128) NOT NULL,
  `name_zh` char(128) NOT NULL DEFAULT '',
  `email` char(128) CHARACTER SET latin1 NOT NULL,
  `password` char(128) CHARACTER SET latin1 NOT NULL,
  `birthday` bigint(20) NOT NULL DEFAULT '0',
  `gender` tinyint(1) unsigned NOT NULL,
  `mobile` char(20) NOT NULL DEFAULT '',
  `country_id` smallint(3) unsigned NOT NULL DEFAULT '0',
  `province_id` smallint(3) unsigned NOT NULL DEFAULT '0',
  `city_id` smallint(3) unsigned NOT NULL DEFAULT '0',
  `role` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `reg_time` int(11) unsigned NOT NULL DEFAULT '0',
  `reg_ip` char(15) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `status` tinyint(4) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `wcaid` (`wcaid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `user_action`
--

CREATE TABLE IF NOT EXISTS `user_action` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `action` char(20) NOT NULL,
  `code` char(32) NOT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `date` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `action` (`action`,`code`),
  KEY `status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

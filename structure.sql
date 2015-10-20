-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2015 年 10 月 20 日 18:17
-- 服务器版本: 5.5.37-log
-- PHP 版本: 5.6.14

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

DROP TABLE IF EXISTS `competition`;
CREATE TABLE IF NOT EXISTS `competition` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` char(10) NOT NULL DEFAULT '',
  `wca_competition_id` char(32) NOT NULL DEFAULT '',
  `old_competition_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` char(128) NOT NULL DEFAULT '',
  `name_zh` char(50) NOT NULL DEFAULT '',
  `alias` char(128) NOT NULL,
  `date` int(11) unsigned NOT NULL,
  `end_date` int(11) unsigned NOT NULL DEFAULT '0',
  `reg_start` int(11) unsigned NOT NULL DEFAULT '0',
  `reg_end` int(11) unsigned NOT NULL DEFAULT '0',
  `province_id` smallint(3) unsigned NOT NULL DEFAULT '0',
  `city_id` smallint(3) unsigned NOT NULL DEFAULT '0',
  `venue` varchar(512) NOT NULL DEFAULT '',
  `venue_zh` varchar(512) NOT NULL DEFAULT '',
  `events` text NOT NULL,
  `entry_fee` smallint(3) unsigned NOT NULL DEFAULT '0',
  `second_stage_date` int(11) unsigned NOT NULL DEFAULT '0',
  `second_stage_ratio` float unsigned NOT NULL DEFAULT '0',
  `second_stage_all` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `online_pay` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `alipay_url` varchar(512) NOT NULL DEFAULT '',
  `regulations` longtext,
  `regulations_zh` longtext,
  `information` longtext,
  `information_zh` longtext,
  `travel` longtext,
  `travel_zh` longtext,
  `person_num` mediumint(6) unsigned NOT NULL DEFAULT '0',
  `check_person` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `paid` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `type` (`type`,`date`,`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `competition_delegate`
--

DROP TABLE IF EXISTS `competition_delegate`;
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

DROP TABLE IF EXISTS `competition_location`;
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

DROP TABLE IF EXISTS `competition_organizer`;
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
-- 表的结构 `faq`
--

DROP TABLE IF EXISTS `faq`;
CREATE TABLE IF NOT EXISTS `faq` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL,
  `category_id` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(1024) NOT NULL,
  `title_zh` varchar(1024) NOT NULL,
  `content` longtext NOT NULL,
  `content_zh` longtext NOT NULL,
  `date` int(10) unsigned NOT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `status_weight_date` (`status`,`date`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `faq_category`
--

DROP TABLE IF EXISTS `faq_category`;
CREATE TABLE IF NOT EXISTS `faq_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL,
  `name` varchar(128) NOT NULL,
  `name_zh` varchar(128) NOT NULL,
  `date` int(10) unsigned NOT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `status_weight_date` (`status`,`date`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `login_history`
--

DROP TABLE IF EXISTS `login_history`;
CREATE TABLE IF NOT EXISTS `login_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `ip` char(15) NOT NULL DEFAULT '',
  `date` int(10) unsigned NOT NULL,
  `from_cookie` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `logs`
--

DROP TABLE IF EXISTS `logs`;
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

DROP TABLE IF EXISTS `mail`;
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

DROP TABLE IF EXISTS `news`;
CREATE TABLE IF NOT EXISTS `news` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL,
  `title` varchar(1024) NOT NULL,
  `title_zh` varchar(1024) NOT NULL,
  `content` longtext NOT NULL,
  `content_zh` longtext NOT NULL,
  `weight` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '排序权重',
  `date` int(10) unsigned NOT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `status_weight_date` (`status`,`weight`,`date`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `news_template`
--

DROP TABLE IF EXISTS `news_template`;
CREATE TABLE IF NOT EXISTS `news_template` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `title` varchar(1024) NOT NULL,
  `title_zh` varchar(1024) NOT NULL,
  `content` longtext NOT NULL,
  `content_zh` longtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `old_competition`
--

DROP TABLE IF EXISTS `old_competition`;
CREATE TABLE IF NOT EXISTS `old_competition` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `delegate` varchar(255) NOT NULL,
  `delegate_zh` varchar(255) NOT NULL,
  `organizer` varchar(255) NOT NULL,
  `organizer_zh` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `pay`
--

DROP TABLE IF EXISTS `pay`;
CREATE TABLE IF NOT EXISTS `pay` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `channel` char(6) NOT NULL DEFAULT '',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `type_id` int(10) unsigned NOT NULL DEFAULT '0',
  `sub_type_id` int(10) unsigned NOT NULL DEFAULT '0',
  `order_no` char(32) NOT NULL,
  `order_name` char(50) NOT NULL,
  `amount` int(10) unsigned NOT NULL DEFAULT '0',
  `device_type` char(2) NOT NULL DEFAULT '',
  `pay_channel` char(4) NOT NULL DEFAULT '',
  `pay_account` varchar(64) NOT NULL DEFAULT '',
  `trade_no` varchar(64) NOT NULL DEFAULT '',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `type` (`type`,`type_id`),
  KEY `status` (`status`,`update_time`),
  KEY `order_id` (`order_no`),
  KEY `pay_channel` (`pay_channel`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `region`
--

DROP TABLE IF EXISTS `region`;
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

DROP TABLE IF EXISTS `registration`;
CREATE TABLE IF NOT EXISTS `registration` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `competition_id` int(10) unsigned NOT NULL,
  `location_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL,
  `events` varchar(512) NOT NULL,
  `total_fee` smallint(3) unsigned NOT NULL DEFAULT '0',
  `comments` varchar(2048) NOT NULL DEFAULT '',
  `paid` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ip` char(15) NOT NULL DEFAULT '',
  `date` int(10) unsigned NOT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `competition_id` (`competition_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `review`
--

DROP TABLE IF EXISTS `review`;
CREATE TABLE IF NOT EXISTS `review` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL,
  `organizer_id` int(10) unsigned NOT NULL DEFAULT '0',
  `competition_id` int(10) unsigned NOT NULL DEFAULT '0',
  `rank` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `comments` varchar(1024) NOT NULL DEFAULT '',
  `date` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `status_weight_date` (`date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `schedule`
--

DROP TABLE IF EXISTS `schedule`;
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
  `number` int(10) unsigned NOT NULL,
  `cut_off` int(10) unsigned NOT NULL,
  `time_limit` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `competition_id` (`competition_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `wcaid` char(10) NOT NULL DEFAULT '',
  `name` char(128) NOT NULL,
  `name_zh` char(128) NOT NULL DEFAULT '',
  `email` char(128) NOT NULL,
  `password` char(128) NOT NULL,
  `avatar_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '头像ID',
  `birthday` bigint(20) NOT NULL DEFAULT '0',
  `gender` tinyint(1) unsigned NOT NULL,
  `mobile` char(20) NOT NULL DEFAULT '',
  `country_id` smallint(3) unsigned NOT NULL DEFAULT '0',
  `province_id` smallint(3) unsigned NOT NULL DEFAULT '0',
  `city_id` smallint(3) unsigned NOT NULL DEFAULT '0',
  `role` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `identity` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `reg_time` int(11) unsigned NOT NULL DEFAULT '0',
  `reg_ip` char(15) NOT NULL DEFAULT '',
  `status` tinyint(4) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `wcaid` (`wcaid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `user_action`
--

DROP TABLE IF EXISTS `user_action`;
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

-- --------------------------------------------------------

--
-- 表的结构 `user_avatar`
--

DROP TABLE IF EXISTS `user_avatar`;
CREATE TABLE IF NOT EXISTS `user_avatar` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `user_id` int(10) unsigned NOT NULL COMMENT '用户ID',
  `md5` char(32) NOT NULL COMMENT '图片md5',
  `extension` char(10) NOT NULL COMMENT '扩展名',
  `width` mediumint(6) unsigned NOT NULL COMMENT '宽度',
  `height` mediumint(6) unsigned NOT NULL COMMENT '高度',
  `add_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上传时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `user_permission`
--

DROP TABLE IF EXISTS `user_permission`;
CREATE TABLE IF NOT EXISTS `user_permission` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `permission` char(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

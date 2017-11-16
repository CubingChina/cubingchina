DROP TABLE IF EXISTS `Competitions`;
CREATE TABLE IF NOT EXISTS `Competitions` (
  `id` varchar(32) NOT NULL DEFAULT '',
  `name` varchar(50) NOT NULL DEFAULT '',
  `cityName` varchar(50) NOT NULL DEFAULT '',
  `countryId` varchar(50) NOT NULL DEFAULT '',
  `information` mediumtext,
  `year` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `month` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `day` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `endMonth` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `endDay` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `eventSpecs` varchar(256) DEFAULT NULL,
  `wcaDelegate` text,
  `organiser` text,
  `venue` varchar(240) NOT NULL DEFAULT '',
  `venueAddress` varchar(120) DEFAULT NULL,
  `venueDetails` varchar(120) DEFAULT NULL,
  `external_website` varchar(200) DEFAULT NULL,
  `cellName` varchar(45) NOT NULL DEFAULT '',
  `latitude` int(11) DEFAULT NULL,
  `longitude` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `countryId` (`countryId`),
  KEY `year_month_day` (`year`,`month`,`day`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `Continents`;
CREATE TABLE IF NOT EXISTS `Continents` (
  `id` varchar(50) NOT NULL DEFAULT '',
  `name` varchar(50) NOT NULL DEFAULT '',
  `recordName` char(3) NOT NULL DEFAULT '',
  `latitude` int(11) NOT NULL DEFAULT '0',
  `longitude` int(11) NOT NULL DEFAULT '0',
  `zoom` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `Countries`;
CREATE TABLE IF NOT EXISTS `Countries` (
  `id` varchar(50) NOT NULL DEFAULT '',
  `name` varchar(50) NOT NULL DEFAULT '',
  `continentId` varchar(50) NOT NULL DEFAULT '',
  `iso2` char(2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `continentId` (`continentId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `Events`;
CREATE TABLE IF NOT EXISTS `Events` (
  `id` varchar(6) NOT NULL DEFAULT '',
  `name` varchar(54) NOT NULL DEFAULT '',
  `rank` int(11) NOT NULL DEFAULT '0',
  `format` varchar(10) NOT NULL DEFAULT '',
  `cellName` varchar(45) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `Formats`;
CREATE TABLE IF NOT EXISTS `Formats` (
  `id` char(1) NOT NULL DEFAULT '',
  `name` varchar(50) NOT NULL DEFAULT '',
  `sort_by` varchar(255) NOT NULL,
  `sort_by_second` varchar(255) NOT NULL,
  `expected_solve_count` int(11) NOT NULL,
  `trim_fastest_n` int(11) NOT NULL,
  `trim_slowest_n` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `Persons`;
CREATE TABLE IF NOT EXISTS `Persons` (
  `id` varchar(10) NOT NULL DEFAULT '',
  `subid` tinyint(6) NOT NULL DEFAULT '1',
  `name` varchar(80) DEFAULT NULL,
  `countryId` varchar(50) NOT NULL DEFAULT '',
  `gender` char(1) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`,`subid`),
  KEY `id` (`id`),
  KEY `countryId` (`countryId`),
  KEY `name` (`name`),
  KEY `gender` (`gender`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `RanksAverage`;
CREATE TABLE IF NOT EXISTS `RanksAverage` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `personId` varchar(10) NOT NULL DEFAULT '',
  `eventId` varchar(6) NOT NULL DEFAULT '',
  `best` int(11) NOT NULL DEFAULT '0',
  `worldRank` int(11) NOT NULL DEFAULT '0',
  `continentRank` int(11) NOT NULL DEFAULT '0',
  `countryRank` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `personId` (`personId`) USING BTREE,
  KEY `eventId` (`eventId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `RanksPenalty`;
CREATE TABLE IF NOT EXISTS `RanksPenalty` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `eventId` varchar(10) NOT NULL DEFAULT '',
  `countryId` varchar(50) DEFAULT '',
  `type` varchar(10) NOT NULL DEFAULT '',
  `penalty` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `type` (`type`,`countryId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `RanksSingle`;
CREATE TABLE IF NOT EXISTS `RanksSingle` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `personId` varchar(10) NOT NULL DEFAULT '',
  `eventId` varchar(6) NOT NULL DEFAULT '',
  `best` int(11) NOT NULL DEFAULT '0',
  `worldRank` int(11) NOT NULL DEFAULT '0',
  `continentRank` int(11) NOT NULL DEFAULT '0',
  `countryRank` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `personId` (`personId`) USING BTREE,
  KEY `eventId` (`eventId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `RanksSum`;
CREATE TABLE IF NOT EXISTS `RanksSum` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `personId` varchar(10) NOT NULL DEFAULT '',
  `countryId` varchar(50) NOT NULL DEFAULT '',
  `continentId` varchar(50) NOT NULL DEFAULT '',
  `type` varchar(10) NOT NULL,
  `countryRank` int(11) NOT NULL DEFAULT '0',
  `continentRank` int(11) NOT NULL DEFAULT '0',
  `worldRank` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `personId` (`personId`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `Results`;
CREATE TABLE IF NOT EXISTS `Results` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `competitionId` varchar(32) NOT NULL DEFAULT '',
  `eventId` varchar(6) NOT NULL DEFAULT '',
  `roundTypeId` char(1) NOT NULL DEFAULT '',
  `pos` smallint(6) NOT NULL DEFAULT '0',
  `best` int(11) NOT NULL DEFAULT '0',
  `average` int(11) NOT NULL DEFAULT '0',
  `personName` varchar(80) DEFAULT NULL,
  `personId` varchar(10) NOT NULL DEFAULT '',
  `personCountryId` varchar(50) DEFAULT NULL,
  `formatId` char(1) NOT NULL DEFAULT '',
  `value1` int(11) NOT NULL DEFAULT '0',
  `value2` int(11) NOT NULL DEFAULT '0',
  `value3` int(11) NOT NULL DEFAULT '0',
  `value4` int(11) NOT NULL DEFAULT '0',
  `value5` int(11) NOT NULL DEFAULT '0',
  `solve` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `attempt` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `regionalSingleRecord` char(3) DEFAULT NULL,
  `regionalAverageRecord` char(3) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `competitionId` (`competitionId`),
  KEY `eventId` (`eventId`),
  KEY `personId` (`personId`) USING BTREE,
  KEY `personCountryId` (`personCountryId`),
  KEY `regionalSingleRecord` (`regionalSingleRecord`),
  KEY `regionalAverageRecord` (`regionalAverageRecord`),
  KEY `event_round_pos` (`eventId`,`roundTypeId`,`pos`),
  KEY `event_best_person` (`eventId`,`best`,`personId`,`personCountryId`),
  KEY `event_avg_person` (`eventId`,`average`,`personId`,`personCountryId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `Rounds`;
CREATE TABLE IF NOT EXISTS `Rounds` (
  `sorry_message` varchar(172) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `RoundTypes`;
CREATE TABLE IF NOT EXISTS `RoundTypes` (
  `id` char(1) NOT NULL DEFAULT '',
  `rank` int(11) NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL DEFAULT '',
  `cellName` varchar(45) NOT NULL DEFAULT '',
  `final` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `Scrambles`;
CREATE TABLE IF NOT EXISTS `Scrambles` (
  `scrambleId` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `competitionId` varchar(32) NOT NULL,
  `eventId` varchar(6) NOT NULL,
  `roundTypeId` char(1) NOT NULL,
  `groupId` varchar(3) NOT NULL,
  `isExtra` tinyint(1) NOT NULL,
  `scrambleNum` int(11) NOT NULL,
  `scramble` text NOT NULL,
  PRIMARY KEY (`scrambleId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `championships`;
CREATE TABLE IF NOT EXISTS `championships` (
  `id` int(11) NOT NULL DEFAULT '0',
  `competition_id` varchar(32) NOT NULL,
  `championship_type` varchar(191) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `competition_id` (`competition_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `eligible_country_iso2s_for_championship`;
CREATE TABLE IF NOT EXISTS `eligible_country_iso2s_for_championship` (
  `id` bigint(20) NOT NULL DEFAULT '0',
  `championship_type` varchar(191) NOT NULL,
  `eligible_country_iso2` varchar(191) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `championship_type` (`championship_type`),
  KEY `eligible_country_iso2` (`eligible_country_iso2`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `Competitions` ADD PRIMARY KEY ( `id` );
ALTER TABLE `Competitions` ADD INDEX ( `countryId` );
ALTER TABLE `Competitions` ADD INDEX `year_month_day` (  `year` ,  `month` ,  `day` );
ALTER TABLE `Continents` ADD PRIMARY KEY ( `id` );
ALTER TABLE `Countries` ADD PRIMARY KEY ( `id` );
ALTER TABLE `Countries` ADD INDEX ( `continentId` );
ALTER TABLE `Events` ADD PRIMARY KEY ( `id` );
ALTER TABLE `Formats` ADD PRIMARY KEY ( `id` );
ALTER TABLE `Persons` ADD PRIMARY KEY ( `id` , `subid` );
ALTER TABLE `Persons` ADD INDEX ( `id` );
ALTER TABLE `Persons` ADD INDEX ( `countryId` );
ALTER TABLE `Persons` ADD INDEX ( `name` );
ALTER TABLE `Persons` ADD INDEX ( `gender` );
ALTER TABLE `Results` ADD `id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE `Results` ADD INDEX ( `competitionId` );
ALTER TABLE `Results` ADD INDEX ( `eventId` );
ALTER TABLE `Results` ADD INDEX USING BTREE( `personId` );
ALTER TABLE `Results` ADD INDEX ( `personCountryId` );
ALTER TABLE `Results` ADD INDEX ( `regionalSingleRecord` );
ALTER TABLE `Results` ADD INDEX ( `regionalAverageRecord` );
ALTER TABLE `Results` ADD INDEX `event_best_person` ( `eventId` , `best` , `personId` , `personCountryId` );
ALTER TABLE `Results` ADD INDEX `event_avg_person` ( `eventId` , `average` , `personId` , `personCountryId` );
ALTER TABLE `Rounds` ADD PRIMARY KEY ( `id` );
ALTER TABLE `RanksSingle` ADD `id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE `RanksSingle` ADD INDEX USING BTREE( `personId` );
ALTER TABLE `RanksSingle` ADD INDEX ( `eventId` );
ALTER TABLE `RanksSingle` ADD `competitionId` VARCHAR( 32 ) NOT NULL ;
ALTER TABLE `RanksAverage` ADD `id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE `RanksAverage` ADD INDEX USING BTREE( `personId` );
ALTER TABLE `RanksAverage` ADD INDEX ( `eventId` );
ALTER TABLE `RanksAverage` ADD `competitionId` VARCHAR( 32 ) NOT NULL ;

CREATE TABLE IF NOT EXISTS `ConciseAverageResults` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `resultId` int(10) NOT NULL DEFAULT '0',
  `average` int(11) NOT NULL DEFAULT '0',
  `competitionId` varchar(32) NOT NULL DEFAULT '',
  `personId` varchar(10) NOT NULL DEFAULT '',
  `eventId` varchar(6) NOT NULL DEFAULT '',
  `countryId` varchar(50) NOT NULL DEFAULT '',
  `continentId` varchar(50) NOT NULL DEFAULT '',
  `year` smallint(5) unsigned NOT NULL DEFAULT '0',
  `month` smallint(5) unsigned NOT NULL DEFAULT '0',
  `day` smallint(5) unsigned NOT NULL DEFAULT '0',
  `value1` int(11) NOT NULL DEFAULT '0',
  `value2` int(11) NOT NULL DEFAULT '0',
  `value3` int(11) NOT NULL DEFAULT '0',
  `value4` int(11) NOT NULL DEFAULT '0',
  `value5` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `personId` (`personId`),
  KEY `eventId` (`eventId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ConciseSingleResults` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `resultId` int(10) NOT NULL DEFAULT '0',
  `best` int(11) NOT NULL DEFAULT '0',
  `competitionId` varchar(32) NOT NULL DEFAULT '',
  `personId` varchar(10) NOT NULL DEFAULT '',
  `eventId` varchar(6) NOT NULL DEFAULT '',
  `countryId` varchar(50) NOT NULL DEFAULT '',
  `continentId` varchar(50) NOT NULL DEFAULT '',
  `year` smallint(5) unsigned NOT NULL DEFAULT '0',
  `month` smallint(5) unsigned NOT NULL DEFAULT '0',
  `day` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `personId` (`personId`),
  KEY `eventId` (`eventId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
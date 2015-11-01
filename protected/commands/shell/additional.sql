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
ALTER TABLE `Results` ADD INDEX `event_round_pos` ( `eventId` , `roundId` , `pos` );
ALTER TABLE `Results` ADD INDEX `event_best_person` ( `eventId` , `best` , `personId` , `personCountryId` );
ALTER TABLE `Results` ADD INDEX `event_avg_person` ( `eventId` , `average` , `personId` , `personCountryId` );
ALTER TABLE `Rounds` ADD PRIMARY KEY ( `id` );
ALTER TABLE `RanksSingle` ADD `id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE `RanksSingle` ADD INDEX USING BTREE( `personId` );
ALTER TABLE `RanksSingle` ADD INDEX ( `eventId` );
ALTER TABLE `RanksAverage` ADD `id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE `RanksAverage` ADD INDEX USING BTREE( `personId` );
ALTER TABLE `RanksAverage` ADD INDEX ( `eventId` );
ALTER TABLE `Scrambles` ADD PRIMARY KEY(`scrambleId`);
DROP TABLE IF EXISTS `RanksSum`;
CREATE TABLE IF NOT EXISTS `RanksSum` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
-- Sum or countryRank
-- Single
INSERT INTO `RanksSum` (`personId`, `countryId`, `continentId`, `type`, `countryRank`)
(
	SELECT
		`p`.`id`,
		`p`.`countryId`,
		`c`.`continentId`,
		'single',
		SUM(
			CASE WHEN
				`r`.`countryRank`=0 OR `r`.`countryRank` IS NULL
			THEN (CASE WHEN `rp`.`penalty` IS NULL THEN 1 ELSE `rp`.`penalty` END)
			ELSE `r`.`countryRank`
		END) AS `countryRank`
	FROM `Persons` `p`
	LEFT JOIN `Countries` `c` ON `p`.`countryId`=`c`.`id`
	LEFT JOIN `Events` `e` ON 1
	LEFT JOIN (
		SELECT
			`eventId`,
			`personCountryId` AS `countryId`,
			COUNT(DISTINCT `personId`) + 1 AS `penalty`
		FROM `Results`
		WHERE `best`>0
		GROUP BY `personCountryId`, `eventId`
	) `rp` ON `rp`.`eventId`=`e`.`id` AND `rp`.`countryId`=`p`.`countryId`
	LEFT JOIN  `RanksSingle` `r` ON `e`.`id`=`r`.`eventId` AND `p`.`id`= `r`.`personId`
	WHERE `p`.`subid`=1 AND `e`.`rank`<900
	GROUP BY `p`.`id`
);
-- Sum or countryRank
-- Average
INSERT INTO `RanksSum` (`personId`, `countryId`, `continentId`, `type`, `countryRank`)
(
	SELECT
		`p`.`id`,
		`p`.`countryId`,
		`c`.`continentId`,
		'average',
		SUM(
			CASE WHEN
				`r`.`countryRank`=0 OR `r`.`countryRank` IS NULL
			THEN (CASE WHEN `rp`.`penalty` IS NULL AND `rp`.`eventId` NOT IN ('444bf', '555bf', '333mbf') THEN 1 ELSE `rp`.`penalty` END)
			ELSE `r`.`countryRank`
		END) AS `countryRank`
	FROM `Persons` `p`
	LEFT JOIN `Countries` `c` ON `p`.`countryId`=`c`.`id`
	LEFT JOIN `Events` `e` ON 1
	LEFT JOIN (
		SELECT
			`eventId`,
			`personCountryId` AS `countryId`,
			COUNT(DISTINCT `personId`) + 1 AS `penalty`
		FROM `Results`
		WHERE `average`>0
		GROUP BY `personCountryId`, `eventId`
	) `rp` ON `rp`.`eventId`=`e`.`id` AND `rp`.`countryId`=`p`.`countryId`
	LEFT JOIN  `RanksAverage` `r` ON `e`.`id`=`r`.`eventId` AND `p`.`id`= `r`.`personId`
	WHERE `p`.`subid`=1 AND `e`.`rank`<900
	GROUP BY `p`.`id`
);


-- Sum or continentRank
-- Single
UPDATE `RanksSum` `sor` INNER JOIN
(
	SELECT
		`p`.`id` AS `personId`,
		`p`.`countryId`,
		`c`.`continentId`,
		SUM(
			CASE WHEN
				`r`.`continentRank`=0 OR `r`.`continentRank` IS NULL
			THEN (CASE WHEN `rp`.`penalty` IS NULL THEN 1 ELSE `rp`.`penalty` END)
			ELSE `r`.`continentRank`
		END) AS `continentRank`
	FROM `Persons` `p`
	LEFT JOIN `Countries` `c` ON `p`.`countryId`=`c`.`id`
	LEFT JOIN `Events` `e` ON 1
	LEFT JOIN (
		SELECT
			`eventId`,
			`c`.`continentId`,
			COUNT(DISTINCT `personId`) + 1 AS `penalty`
		FROM `Results` `r`
		LEFT JOIN `Countries` `c` ON `r`.`personCountryId`=`c`.`id`
		WHERE `best`>0
		GROUP BY `eventId`, `c`.`continentId`
	) `rp` ON `rp`.`eventId`=`e`.`id` AND `rp`.`continentId`=`c`.`continentId`
	LEFT JOIN  `RanksSingle` `r` ON `e`.`id`=`r`.`eventId` AND `p`.`id`= `r`.`personId`
	WHERE `p`.`subid`=1 AND `e`.`rank`<900
	GROUP BY `p`.`id`
) `t` ON `sor`.`personId`=`t`.`personId`
SET `sor`.`continentRank`=`t`.`continentRank` WHERE `sor`.`type`='single';
-- Sum or continentRank
-- Average
UPDATE `RanksSum` `sor` INNER JOIN
(
	SELECT
		`p`.`id` AS `personId`,
		`p`.`countryId`,
		`c`.`continentId`,
		SUM(
			CASE WHEN
				`r`.`continentRank`=0 OR `r`.`continentRank` IS NULL
			THEN (CASE WHEN `rp`.`penalty` IS NULL AND `rp`.`eventId` NOT IN ('444bf', '555bf', '333mbf') THEN 1 ELSE `rp`.`penalty` END)
			ELSE `r`.`continentRank`
		END) AS `continentRank`
	FROM `Persons` `p`
	LEFT JOIN `Countries` `c` ON `p`.`countryId`=`c`.`id`
	LEFT JOIN `Events` `e` ON 1
	LEFT JOIN (
		SELECT
			`eventId`,
			`c`.`continentId`,
			COUNT(DISTINCT `personId`) + 1 AS `penalty`
		FROM `Results` `r`
		LEFT JOIN `Countries` `c` ON `r`.`personCountryId`=`c`.`id`
		WHERE `average`>0
		GROUP BY `eventId`, `c`.`continentId`
	) `rp` ON `rp`.`eventId`=`e`.`id` AND `rp`.`continentId`=`c`.`continentId`
	LEFT JOIN  `RanksAverage` `r` ON `e`.`id`=`r`.`eventId` AND `p`.`id`= `r`.`personId`
	WHERE `p`.`subid`=1 AND `e`.`rank`<900
	GROUP BY `p`.`id`
) `t` ON `sor`.`personId`=`t`.`personId`
SET `sor`.`continentRank`=`t`.`continentRank` WHERE `sor`.`type`='average';


-- Sum or worldRank
-- Single
UPDATE `RanksSum` `sor` INNER JOIN
(
	SELECT
		`p`.`id` AS `personId`,
		SUM(
			CASE WHEN `r`.`worldRank` IS NULL
			THEN `rp`.`penalty`
			ELSE `r`.`worldRank`
		END) AS `worldRank`
	FROM `Persons` `p`
	LEFT JOIN `Events` `e` ON 1
	LEFT JOIN (
		SELECT
			`eventId`,
			COUNT(DISTINCT `personId`) + 1 AS `penalty`
		FROM `Results`
		WHERE `best`>0
		GROUP BY `eventId`
	) `rp` ON `rp`.`eventId`=`e`.`id`
	LEFT JOIN  `RanksSingle` `r` ON `e`.`id`=`r`.`eventId` AND `p`.`id`= `r`.`personId`
	WHERE `p`.`subid`=1 AND `e`.`rank`<900
	GROUP BY `p`.`id`
) `t` ON `sor`.`personId`=`t`.`personId`
SET `sor`.`worldRank`=`t`.`worldRank` WHERE `sor`.`type`='single';
-- Sum or worldRank
-- Average
UPDATE `RanksSum` `sor` INNER JOIN
(
	SELECT
		`p`.`id` AS `personId`,
		SUM(
			CASE WHEN `r`.`worldRank` IS NULL
			THEN `rp`.`penalty`
			ELSE `r`.`worldRank`
		END) AS `worldRank`
	FROM `Persons` `p`
	LEFT JOIN `Events` `e` ON 1
	LEFT JOIN (
		SELECT
			`eventId`,
			COUNT(DISTINCT `personId`) + 1 AS `penalty`
		FROM `Results`
		WHERE `average`>0
		GROUP BY `eventId`
	) `rp` ON `rp`.`eventId`=`e`.`id`
	LEFT JOIN  `RanksAverage` `r` ON `e`.`id`=`r`.`eventId` AND `p`.`id`= `r`.`personId`
	WHERE `p`.`subid`=1 AND `e`.`rank`<900
	GROUP BY `p`.`id`
) `t` ON `sor`.`personId`=`t`.`personId`
SET `sor`.`worldRank`=`t`.`worldRank` WHERE `sor`.`type`='average';

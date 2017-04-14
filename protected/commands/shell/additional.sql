UPDATE `Results` set
  `solve`=(CASE WHEN `value1`>0 THEN 1 ELSE 0 END)+
          (CASE WHEN `value2`>0 THEN 1 ELSE 0 END)+
          (CASE WHEN `value3`>0 THEN 1 ELSE 0 END)+
          (CASE WHEN `value4`>0 THEN 1 ELSE 0 END)+
          (CASE WHEN `value5`>0 THEN 1 ELSE 0 END),
  `attempt`=(CASE WHEN `value1`>-2 AND `value1`!=0 THEN 1 ELSE 0 END)+
            (CASE WHEN `value2`>-2 AND `value2`!=0 THEN 1 ELSE 0 END)+
            (CASE WHEN `value3`>-2 AND `value3`!=0 THEN 1 ELSE 0 END)+
            (CASE WHEN `value4`>-2 AND `value4`!=0 THEN 1 ELSE 0 END)+
            (CASE WHEN `value5`>-2 AND `value5`!=0 THEN 1 ELSE 0 END);

-- Single countryRanks penalty
INSERT INTO `RanksPenalty` (`eventId`, `countryId`, `type`, `penalty`)
(
  SELECT
    `eventId`,
    `personCountryId` AS `countryId`,
    'single' AS `type`,
    COUNT(DISTINCT `personId`) + 1 AS `penalty`
  FROM `Results`
  WHERE `best`>0
  GROUP BY `eventId`, `personCountryId`
);
-- Average countryRanks penalty
INSERT INTO `RanksPenalty` (`eventId`, `countryId`, `type`, `penalty`)
(
  SELECT
    `eventId`,
    `personCountryId` AS `countryId`,
    'average' AS `type`,
    COUNT(DISTINCT `personId`) + 1 AS `penalty`
  FROM `Results`
  WHERE `average`>0
  GROUP BY `eventId`, `personCountryId`
);
-- Single continentRanks penalty
INSERT INTO `RanksPenalty` (`eventId`, `countryId`, `type`, `penalty`)
(
  SELECT
    `eventId`,
    `c`.`continentId`,
    'single' AS `type`,
    COUNT(DISTINCT `personId`) + 1 AS `penalty`
  FROM `Results` `r`
  LEFT JOIN `Countries` `c` ON `r`.`personCountryId`=`c`.`id`
  WHERE `best`>0
  GROUP BY `eventId`, `c`.`continentId`
);
-- Average continentRanks penalty
INSERT INTO `RanksPenalty` (`eventId`, `countryId`, `type`, `penalty`)
(
  SELECT
    `eventId`,
    `c`.`continentId`,
    'average' AS `type`,
    COUNT(DISTINCT `personId`) + 1 AS `penalty`
  FROM `Results` `r`
  LEFT JOIN `Countries` `c` ON `r`.`personCountryId`=`c`.`id`
  WHERE `average`>0
  GROUP BY `eventId`, `c`.`continentId`
);
-- Single worldRanks penalty
INSERT INTO `RanksPenalty` (`eventId`, `countryId`, `type`, `penalty`)
(
  SELECT
    `eventId`,
    'World',
    'single' AS `type`,
    COUNT(DISTINCT `personId`) + 1 AS `penalty`
  FROM `Results`
  WHERE `best`>0
  GROUP BY `eventId`
);
-- Average worldRanks penalty
INSERT INTO `RanksPenalty` (`eventId`, `countryId`, `type`, `penalty`)
(
  SELECT
    `eventId`,
    'World',
    'average' AS `type`,
    COUNT(DISTINCT `personId`) + 1 AS `penalty`
  FROM `Results`
  WHERE `average`>0
  GROUP BY `eventId`
);
-- Sum of countryRank
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
  LEFT JOIN `RanksPenalty` `rp` ON `rp`.`type`='single' AND `rp`.`eventId`=`e`.`id` AND `rp`.`countryId`=`p`.`countryId`
  LEFT JOIN  `RanksSingle` `r` ON `e`.`id`=`r`.`eventId` AND `p`.`id`= `r`.`personId`
  WHERE `p`.`subid`=1 AND `e`.`rank`<900
  GROUP BY `p`.`id`
);
-- Sum of countryRank
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
      THEN (CASE WHEN `rp`.`penalty` IS NULL THEN (CASE WHEN `rp`.`eventId` NOT IN ('444bf', '555bf', '333mbf') THEN 1 ELSE 0 END) ELSE `rp`.`penalty` END)
      ELSE `r`.`countryRank`
    END) AS `countryRank`
  FROM `Persons` `p`
  LEFT JOIN `Countries` `c` ON `p`.`countryId`=`c`.`id`
  LEFT JOIN `Events` `e` ON 1
  LEFT JOIN `RanksPenalty` `rp` ON `rp`.`type`='average' AND `rp`.`countryId`=`p`.`countryId` AND `rp`.`eventId`=`e`.`id`
  LEFT JOIN  `RanksAverage` `r` ON `e`.`id`=`r`.`eventId` AND `p`.`id`= `r`.`personId`
  WHERE `p`.`subid`=1 AND `e`.`rank`<900
  GROUP BY `p`.`id`
);


-- Sum of continentRank
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
  LEFT JOIN `RanksPenalty` `rp` ON `rp`.`type`='single' AND `rp`.`countryId`=`c`.`continentId` AND `rp`.`eventId`=`e`.`id`
  LEFT JOIN  `RanksSingle` `r` ON `e`.`id`=`r`.`eventId` AND `p`.`id`= `r`.`personId`
  WHERE `p`.`subid`=1 AND `e`.`rank`<900
  GROUP BY `p`.`id`
) `t` ON `sor`.`personId`=`t`.`personId`
SET `sor`.`continentRank`=`t`.`continentRank` WHERE `sor`.`type`='single';
-- Sum of continentRank
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
      THEN (CASE WHEN `rp`.`penalty` IS NULL THEN (CASE WHEN `rp`.`eventId` NOT IN ('444bf', '555bf', '333mbf') THEN 1 ELSE 0 END) ELSE `rp`.`penalty` END)
      ELSE `r`.`continentRank`
    END) AS `continentRank`
  FROM `Persons` `p`
  LEFT JOIN `Countries` `c` ON `p`.`countryId`=`c`.`id`
  LEFT JOIN `Events` `e` ON 1
  LEFT JOIN `RanksPenalty` `rp` ON `rp`.`type`='average' AND `rp`.`countryId`=`c`.`continentId` AND `rp`.`eventId`=`e`.`id`
  LEFT JOIN  `RanksAverage` `r` ON `e`.`id`=`r`.`eventId` AND `p`.`id`= `r`.`personId`
  WHERE `p`.`subid`=1 AND `e`.`rank`<900
  GROUP BY `p`.`id`
) `t` ON `sor`.`personId`=`t`.`personId`
SET `sor`.`continentRank`=`t`.`continentRank` WHERE `sor`.`type`='average';


-- Sum of worldRank
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
  LEFT JOIN `RanksPenalty` `rp` ON `rp`.`type`='single' AND `rp`.`countryId`='World' AND `rp`.`eventId`=`e`.`id`
  LEFT JOIN  `RanksSingle` `r` ON `e`.`id`=`r`.`eventId` AND `p`.`id`= `r`.`personId`
  WHERE `p`.`subid`=1 AND `e`.`rank`<900
  GROUP BY `p`.`id`
) `t` ON `sor`.`personId`=`t`.`personId`
SET `sor`.`worldRank`=`t`.`worldRank` WHERE `sor`.`type`='single';
-- Sum of worldRank
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
  LEFT JOIN `RanksPenalty` `rp` ON `rp`.`type`='average' AND `rp`.`countryId`='World' AND `rp`.`eventId`=`e`.`id`
  LEFT JOIN  `RanksAverage` `r` ON `e`.`id`=`r`.`eventId` AND `p`.`id`= `r`.`personId`
  WHERE `p`.`subid`=1 AND `e`.`rank`<900
  GROUP BY `p`.`id`
) `t` ON `sor`.`personId`=`t`.`personId`
SET `sor`.`worldRank`=`t`.`worldRank` WHERE `sor`.`type`='average';

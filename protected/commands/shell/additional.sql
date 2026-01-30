ALTER TABLE `results` ADD COLUMN `solve` tinyint(1) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `results` ADD COLUMN `attempt` tinyint(1) UNSIGNED NOT NULL DEFAULT '0';

-- PRIMARY KEYS
ALTER TABLE `eligible_country_iso2s_for_championship` ADD PRIMARY KEY (`championship_type`, `eligible_country_iso2`);
ALTER TABLE `persons` ADD PRIMARY KEY (`wca_id`, `sub_id`);
ALTER TABLE `ranks_single` ADD PRIMARY KEY (`person_id`, `event_id`);
ALTER TABLE `ranks_average` ADD PRIMARY KEY (`person_id`, `event_id`);
ALTER TABLE `result_attempts` ADD PRIMARY KEY (`result_id`, `attempt_number`);

-- INDEXES
ALTER TABLE `results` ADD INDEX `competition_id` (`competition_id`);
ALTER TABLE `results` ADD INDEX `event_id` (`event_id`);
ALTER TABLE `results` ADD INDEX `person_id` (`person_id`) USING BTREE;
ALTER TABLE `results` ADD INDEX `person_country_id` (`person_country_id`);
ALTER TABLE `results` ADD INDEX `regional_single_record` (`regional_single_record`);
ALTER TABLE `results` ADD INDEX `regional_average_record` (`regional_average_record`);
ALTER TABLE `results` ADD INDEX `event_round_pos` (`event_id`,`round_type_id`,`pos`);
ALTER TABLE `results` ADD INDEX `event_best_person` (`event_id`,`best`,`person_id`,`person_country_id`);
ALTER TABLE `results` ADD INDEX `event_avg_person` (`event_id`,`average`,`person_id`,`person_country_id`);
ALTER TABLE `results` ADD INDEX `event_country_avg_person` (`event_id`, `person_country_id`, `average`, `person_id`);
ALTER TABLE `results` ADD INDEX `event_country_single_person` (`event_id`, `person_country_id`, `best`, `person_id`);
ALTER TABLE `competitions` ADD INDEX `country_id` (`country_id`);
ALTER TABLE `competitions` ADD INDEX `year_month_day` (`year`, `month`, `day`);
ALTER TABLE `countries` ADD INDEX `continent_id` (`continent_id`);
ALTER TABLE `persons` ADD INDEX `wca_id` (`wca_id`);
ALTER TABLE `persons` ADD INDEX `country_id` (`country_id`);
ALTER TABLE `persons` ADD INDEX `name` (`name`);
ALTER TABLE `persons` ADD INDEX `gender` (`gender`);
ALTER TABLE `ranks_average` ADD INDEX `event_id` (`event_id`);
ALTER TABLE `ranks_single` ADD INDEX `event_id` (`event_id`);
ALTER TABLE `championships` ADD INDEX `competition_id` (`competition_id`);
ALTER TABLE `scrambles` ADD INDEX `competition_id` (`competition_id`);
ALTER TABLE `ranks_penalty` ADD INDEX `type_event_country` (`type`, `event_id`, `country_id`);

UPDATE `results` r
LEFT JOIN (
  SELECT
    `result_id`,
    SUM(CASE WHEN `value` > 0 THEN 1 ELSE 0 END) AS solve_count,
    SUM(CASE WHEN `value` > -2 AND `value` != 0 THEN 1 ELSE 0 END) AS attempt_count
  FROM `result_attempts`
  GROUP BY `result_id`
) ra ON r.`id` = ra.`result_id`
SET
  r.`solve` = COALESCE(ra.solve_count, 0),
  r.`attempt` = COALESCE(ra.attempt_count, 0);

-- Single countryRanks penalty
INSERT INTO `ranks_penalty` (`event_id`, `country_id`, `type`, `penalty`)
(
  SELECT
    `event_id`,
    `person_country_id` AS `country_id`,
    'single' AS `type`,
    COUNT(DISTINCT `person_id`) + 1 AS `penalty`
  FROM `results`
  WHERE `best`>0
  GROUP BY `event_id`, `person_country_id`
);
-- Average countryRanks penalty
INSERT INTO `ranks_penalty` (`event_id`, `country_id`, `type`, `penalty`)
(
  SELECT
    `event_id`,
    `person_country_id` AS `country_id`,
    'average' AS `type`,
    COUNT(DISTINCT `person_id`) + 1 AS `penalty`
  FROM `results`
  WHERE `average`>0
  GROUP BY `event_id`, `person_country_id`
);
-- Single continentRanks penalty
INSERT INTO `ranks_penalty` (`event_id`, `country_id`, `type`, `penalty`)
(
  SELECT
    `event_id`,
    `c`.`continent_id`,
    'single' AS `type`,
    COUNT(DISTINCT `person_id`) + 1 AS `penalty`
  FROM `results` `r`
  LEFT JOIN `countries` `c` ON `r`.`person_country_id`=`c`.`id`
  WHERE `best`>0
  GROUP BY `event_id`, `c`.`continent_id`
);
-- Average continentRanks penalty
INSERT INTO `ranks_penalty` (`event_id`, `country_id`, `type`, `penalty`)
(
  SELECT
    `event_id`,
    `c`.`continent_id`,
    'average' AS `type`,
    COUNT(DISTINCT `person_id`) + 1 AS `penalty`
  FROM `results` `r`
  LEFT JOIN `countries` `c` ON `r`.`person_country_id`=`c`.`id`
  WHERE `average`>0
  GROUP BY `event_id`, `c`.`continent_id`
);
-- Single worldRanks penalty
INSERT INTO `ranks_penalty` (`event_id`, `country_id`, `type`, `penalty`)
(
  SELECT
    `event_id`,
    'World',
    'single' AS `type`,
    COUNT(DISTINCT `person_id`) + 1 AS `penalty`
  FROM `results`
  WHERE `best`>0
  GROUP BY `event_id`
);
-- Average worldRanks penalty
INSERT INTO `ranks_penalty` (`event_id`, `country_id`, `type`, `penalty`)
(
  SELECT
    `event_id`,
    'World',
    'average' AS `type`,
    COUNT(DISTINCT `person_id`) + 1 AS `penalty`
  FROM `results`
  WHERE `average`>0
  GROUP BY `event_id`
);
-- Sum of countryRank
-- Single
INSERT INTO `ranks_sum` (`person_id`, `country_id`, `continent_id`, `type`, `country_rank`)
(
  SELECT
    `p`.`wca_id`,
    `p`.`country_id`,
    `c`.`continent_id`,
    'single',
    SUM(
      CASE WHEN
        `r`.`country_rank`=0 OR `r`.`country_rank` IS NULL
      THEN (CASE WHEN `rp`.`penalty` IS NULL THEN 1 ELSE `rp`.`penalty` END)
      ELSE `r`.`country_rank`
    END) AS `country_rank`
  FROM `persons` `p`
  LEFT JOIN `countries` `c` ON `p`.`country_id`=`c`.`id`
  LEFT JOIN `events` `e` ON 1
  LEFT JOIN `ranks_penalty` `rp` ON `rp`.`type`='single' AND `rp`.`event_id`=`e`.`id` AND `rp`.`country_id`=`p`.`country_id`
  LEFT JOIN  `ranks_single` `r` ON `e`.`id`=`r`.`event_id` AND `p`.`wca_id`= `r`.`person_id`
  WHERE `p`.`sub_id`=1 AND `e`.`rank`<900
  GROUP BY `p`.`wca_id`
);
-- Sum of countryRank
-- Average
INSERT INTO `ranks_sum` (`person_id`, `country_id`, `continent_id`, `type`, `country_rank`)
(
  SELECT
    `p`.`wca_id`,
    `p`.`country_id`,
    `c`.`continent_id`,
    'average',
    SUM(
      CASE WHEN
        `r`.`country_rank`=0 OR `r`.`country_rank` IS NULL
      THEN (CASE WHEN `rp`.`penalty` IS NULL THEN (CASE WHEN `rp`.`event_id`!='333mbf' THEN 1 ELSE 0 END) ELSE `rp`.`penalty` END)
      ELSE `r`.`country_rank`
    END) AS `country_rank`
  FROM `persons` `p`
  LEFT JOIN `countries` `c` ON `p`.`country_id`=`c`.`id`
  LEFT JOIN `events` `e` ON 1
  LEFT JOIN `ranks_penalty` `rp` ON `rp`.`type`='average' AND `rp`.`country_id`=`p`.`country_id` AND `rp`.`event_id`=`e`.`id`
  LEFT JOIN  `ranks_average` `r` ON `e`.`id`=`r`.`event_id` AND `p`.`wca_id`= `r`.`person_id`
  WHERE `p`.`sub_id`=1 AND `e`.`rank`<900
  GROUP BY `p`.`wca_id`
);


-- Sum of continentRank
-- Single
UPDATE `ranks_sum` `sor` INNER JOIN
(
  SELECT
    `p`.`wca_id` AS `person_id`,
    `p`.`country_id`,
    `c`.`continent_id`,
    SUM(
      CASE WHEN
        `r`.`continent_rank`=0 OR `r`.`continent_rank` IS NULL
      THEN (CASE WHEN `rp`.`penalty` IS NULL THEN 1 ELSE `rp`.`penalty` END)
      ELSE `r`.`continent_rank`
    END) AS `continent_rank`
  FROM `persons` `p`
  LEFT JOIN `countries` `c` ON `p`.`country_id`=`c`.`id`
  LEFT JOIN `events` `e` ON 1
  LEFT JOIN `ranks_penalty` `rp` ON `rp`.`type`='single' AND `rp`.`country_id`=`c`.`continent_id` AND `rp`.`event_id`=`e`.`id`
  LEFT JOIN  `ranks_single` `r` ON `e`.`id`=`r`.`event_id` AND `p`.`wca_id`= `r`.`person_id`
  WHERE `p`.`sub_id`=1 AND `e`.`rank`<900
  GROUP BY `p`.`wca_id`
) `t` ON `sor`.`person_id`=`t`.`person_id`
SET `sor`.`continent_rank`=`t`.`continent_rank` WHERE `sor`.`type`='single';
-- Sum of continentRank
-- Average
UPDATE `ranks_sum` `sor` INNER JOIN
(
  SELECT
    `p`.`wca_id` AS `person_id`,
    `p`.`country_id`,
    `c`.`continent_id`,
    SUM(
      CASE WHEN
        `r`.`continent_rank`=0 OR `r`.`continent_rank` IS NULL
      THEN (CASE WHEN `rp`.`penalty` IS NULL THEN (CASE WHEN `rp`.`event_id`!='333mbf' THEN 1 ELSE 0 END) ELSE `rp`.`penalty` END)
      ELSE `r`.`continent_rank`
    END) AS `continent_rank`
  FROM `persons` `p`
  LEFT JOIN `countries` `c` ON `p`.`country_id`=`c`.`id`
  LEFT JOIN `events` `e` ON 1
  LEFT JOIN `ranks_penalty` `rp` ON `rp`.`type`='average' AND `rp`.`country_id`=`c`.`continent_id` AND `rp`.`event_id`=`e`.`id`
  LEFT JOIN  `ranks_average` `r` ON `e`.`id`=`r`.`event_id` AND `p`.`wca_id`= `r`.`person_id`
  WHERE `p`.`sub_id`=1 AND `e`.`rank`<900
  GROUP BY `p`.`wca_id`
) `t` ON `sor`.`person_id`=`t`.`person_id`
SET `sor`.`continent_rank`=`t`.`continent_rank` WHERE `sor`.`type`='average';


-- Sum of worldRank
-- Single
UPDATE `ranks_sum` `sor` INNER JOIN
(
  SELECT
    `p`.`wca_id` AS `person_id`,
    SUM(
      CASE WHEN `r`.`world_rank` IS NULL
      THEN `rp`.`penalty`
      ELSE `r`.`world_rank`
    END) AS `world_rank`
  FROM `persons` `p`
  LEFT JOIN `events` `e` ON 1
  LEFT JOIN `ranks_penalty` `rp` ON `rp`.`type`='single' AND `rp`.`country_id`='World' AND `rp`.`event_id`=`e`.`id`
  LEFT JOIN  `ranks_single` `r` ON `e`.`id`=`r`.`event_id` AND `p`.`wca_id`= `r`.`person_id`
  WHERE `p`.`sub_id`=1 AND `e`.`rank`<900
  GROUP BY `p`.`wca_id`
) `t` ON `sor`.`person_id`=`t`.`person_id`
SET `sor`.`world_rank`=`t`.`world_rank` WHERE `sor`.`type`='single';
-- Sum of worldRank
-- Average
UPDATE `ranks_sum` `sor` INNER JOIN
(
  SELECT
    `p`.`wca_id` AS `person_id`,
    SUM(
      CASE WHEN `r`.`world_rank` IS NULL
      THEN `rp`.`penalty`
      ELSE `r`.`world_rank`
    END) AS `world_rank`
  FROM `persons` `p`
  LEFT JOIN `events` `e` ON 1
  LEFT JOIN `ranks_penalty` `rp` ON `rp`.`type`='average' AND `rp`.`country_id`='World' AND `rp`.`event_id`=`e`.`id`
  LEFT JOIN  `ranks_average` `r` ON `e`.`id`=`r`.`event_id` AND `p`.`wca_id`= `r`.`person_id`
  WHERE `p`.`sub_id`=1 AND `e`.`rank`<900
  GROUP BY `p`.`wca_id`
) `t` ON `sor`.`person_id`=`t`.`person_id`
SET `sor`.`world_rank`=`t`.`world_rank` WHERE `sor`.`type`='average';


-- BestResults
-- Single
INSERT INTO `best_results` (`type`, `event_id`, `best`, `person_id`, `gender`, `country_id`, `continent_id`)
(
  SELECT
    'single',
    `rs`.`event_id`,
    MIN(`rs`.`best`) AS `best`,
    `rs`.`person_id`,
    `p`.`gender`,
    `rs`.`person_country_id` AS `country_id`,
    `country`.`continent_id`
  FROM `results` `rs`
  LEFT JOIN `persons` `p` ON `rs`.`person_id`=`p`.`wca_id` AND `p`.`sub_id`=1
  LEFT JOIN `countries` `country` ON `rs`.`person_country_id`=`country`.`id`
  WHERE `rs`.`best`>0
  GROUP BY `rs`.`event_id`, `rs`.`person_id`, `rs`.`person_country_id`
);
-- Average
INSERT INTO `best_results` (`type`, `event_id`, `best`, `person_id`, `gender`, `country_id`, `continent_id`)
(
  SELECT
    'average',
    `rs`.`event_id`,
    MIN(`rs`.`average`) AS `best`,
    `rs`.`person_id`,
    `p`.`gender`,
    `rs`.`person_country_id` AS `country_id`,
    `country`.`continent_id`
  FROM `results` `rs`
  LEFT JOIN `persons` `p` ON `rs`.`person_id`=`p`.`wca_id` AND `p`.`sub_id`=1
  LEFT JOIN `countries` `country` ON `rs`.`person_country_id`=`country`.`id`
  WHERE `rs`.`average`>0
  GROUP BY `rs`.`event_id`, `rs`.`person_id`, `rs`.`person_country_id`
);

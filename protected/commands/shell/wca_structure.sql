DROP TABLE IF EXISTS `ranks_penalty`;
CREATE TABLE IF NOT EXISTS `ranks_penalty` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id` varchar(10) NOT NULL DEFAULT '',
  `country_id` varchar(50) DEFAULT '',
  `type` varchar(10) NOT NULL DEFAULT '',
  `penalty` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `type` (`type`,`country_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `ranks_sum`;
CREATE TABLE IF NOT EXISTS `ranks_sum` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `person_id` varchar(10) NOT NULL DEFAULT '',
  `country_id` varchar(50) NOT NULL DEFAULT '',
  `continent_id` varchar(50) NOT NULL DEFAULT '',
  `type` varchar(10) NOT NULL,
  `country_rank` int(11) NOT NULL DEFAULT '0',
  `continent_rank` int(11) NOT NULL DEFAULT '0',
  `world_rank` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `person_id` (`person_id`),
  KEY `type` (`type`, `world_rank`),
  KEY `type_country` (`type`, `country_id`, `country_rank`),
  KEY `type_continent` (`type`, `continent_id`, `continent_rank`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `delegates`;
CREATE TABLE IF NOT EXISTS `delegates` (
  `wca_id` char(10) NOT NULL,
  `email` varchar(128) DEFAULT NULL,
  `name` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`wca_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `best_results`;
CREATE TABLE IF NOT EXISTS `best_results` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` varchar(10) NOT NULL,
  `event_id` varchar(6) NOT NULL DEFAULT '',
  `best` int(11) DEFAULT NULL,
  `person_id` varchar(10) NOT NULL DEFAULT '',
  `gender` char(1) DEFAULT '',
  `country_id` varchar(50) DEFAULT NULL,
  `continent_id` varchar(50) DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `type` (`type`,`event_id`,`best`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

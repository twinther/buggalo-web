CREATE TABLE IF NOT EXISTS `addon_exception` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `addon_name` varchar(255) NOT NULL,
  `addon_version` varchar(255) NOT NULL,
  `title` text NOT NULL,
  `json` text NOT NULL,
  `status` varchar(10) NOT NULL DEFAULT 'NEW',
  `ip` varchar(64) NOT NULL,
  `duplicate_of_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `duplicate_of_id` (`duplicate_of_id`)
);

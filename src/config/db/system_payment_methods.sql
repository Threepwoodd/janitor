CREATE TABLE `SITE_DB`.`system_payment_methods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `classname` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `gateway` varchar(50) DEFAULT NULL,
  `position` int(11) DEFAULT '0',

  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

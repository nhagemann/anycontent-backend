MySQLCache

cache:
  driver: {type: mysql, host: localhost, dbname: cxio, tablename: doctrine_cache, user: cxio, password: cxio, port: 3306}
  
  
CREATE TABLE `doctrine_cache` (
  `id` varchar(255) NOT NULL DEFAULT '',
  `data` longtext,
  `lifetime` int(11) NOT NULL DEFAULT '0',
  `creation` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`,`lifetime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;  
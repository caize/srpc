CREATE TABLE `redis_cachekey` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cachecreate` datetime NOT NULL,
  `expire` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `app` varchar(255) NOT NULL,
  `cachekey` varchar(255) NOT NULL COMMENT '包括app_key',
  `isvalid` tinyint(1) DEFAULT '1',
  `ctime` datetime NOT NULL,
  `mtime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_expire_cachekey` (`expire`,`cachekey`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8 COMMENT='redis cache明细'
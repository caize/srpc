/*
Navicat MySQL Data Transfer

Source Server         : 内网测试
Source Server Version : 50712
Source Host           : 10.0.5.102:3306
Source Database       : service_api

Target Server Type    : MYSQL
Target Server Version : 50712
File Encoding         : 65001

Date: 2017-06-09 17:13:51
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for errorlog_app
-- ----------------------------
DROP TABLE IF EXISTS `errorlog_app`;
CREATE TABLE `errorlog_app` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `datetime` varchar(32) DEFAULT NULL,
  `errorlevel` tinyint(4) DEFAULT NULL COMMENT '0:TRACE,1:INFO,2NOTICE,3WARN,4:ERROR',
  `remoteip` varchar(16) DEFAULT NULL,
  `errormsg` varchar(4096) NOT NULL,
  `ctime` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `mtime` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `isvalid` tinyint(3) unsigned DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_datetime` (`datetime`)
) ENGINE=InnoDB AUTO_INCREMENT=163318 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for errorlog_service
-- ----------------------------
DROP TABLE IF EXISTS `errorlog_service`;
CREATE TABLE `errorlog_service` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `datetime` varchar(32) DEFAULT NULL,
  `errorlevel` tinyint(4) DEFAULT NULL COMMENT '0:TRACE,1:INFO,2NOTICE,3WARN,4:ERROR',
  `serviceurl` varchar(256) NOT NULL,
  `appid` varchar(40) DEFAULT NULL,
  `remoteip` varchar(16) DEFAULT NULL,
  `code` varchar(512) NOT NULL COMMENT '状态码',
  `msg` varchar(1024) NOT NULL COMMENT '错误提示信息',
  `traces` varchar(4096) NOT NULL,
  `url` varchar(128) NOT NULL,
  `servicename` varchar(64) NOT NULL,
  `params` varchar(2048) NOT NULL,
  `time` double NOT NULL,
  `type` varchar(16) NOT NULL,
  `method` varchar(16) NOT NULL COMMENT 'http或其他',
  `send` varchar(32) NOT NULL COMMENT '调用哪个方法，预留备用',
  `access` tinyint(3) unsigned DEFAULT NULL COMMENT '0失败的日志，1成功的日志',
  `ctime` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `mtime` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `isvalid` tinyint(4) DEFAULT '1',
  PRIMARY KEY (`id`,`send`),
  KEY `idx_query1` (`datetime`,`serviceurl`,`remoteip`),
  KEY `idx_query2` (`datetime`,`appid`)
) ENGINE=InnoDB AUTO_INCREMENT=55098 DEFAULT CHARSET=utf8;

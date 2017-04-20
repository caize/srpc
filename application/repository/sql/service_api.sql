/*
Navicat MySQL Data Transfer

Source Server         : 10.0.5.102
Source Server Version : 50712
Source Host           : 10.0.5.102:3306
Source Database       : service_api

Target Server Type    : MYSQL
Target Server Version : 50712
File Encoding         : 65001

Date: 2017-04-12 20:05:05
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for api
-- ----------------------------
DROP TABLE IF EXISTS `api`;
CREATE TABLE `api` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT '接口名',
  `url` varchar(255) NOT NULL COMMENT 'api的地址',
  `parameter` varchar(255) DEFAULT NULL COMMENT '默认参数，json个数',
  `isauth` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否认证',
  `host` varchar(15) NOT NULL COMMENT '指定host',
  `groupid` int(11) NOT NULL DEFAULT '0' COMMENT '组ID',
  `desc` varchar(255) DEFAULT NULL,
  `status` tinyint(4) DEFAULT '1',
  `ctime` datetime DEFAULT NULL,
  `mtime` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_url` (`url`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COMMENT='接口地址表';

-- ----------------------------
-- Records of api
-- ----------------------------
INSERT INTO `api` VALUES ('1', '行情服务quote', 'http://l2-hq.10jqka.com.cn/quote', '{\"Method\":\"Quote\",\"Fuquan\":\"Q\",\"CodeList\":\"\",\"DataType\":\"\",\"DateTime\":\"\",\"DupCode\":0}', '1', '172.20.0.127', '0', null, '1', null, '2017-04-10 19:06:33');
INSERT INTO `api` VALUES ('5', '行情服务hexin', 'http://l2-hq.10jqka.com.cn/hexin', '{\"Method\":\"Quote\",\"Fuquan\":\"Q\",\"CodeList\":\"\",\"DataType\":\"\",\"DateTime\":\"\",\"DupCode\":0}', '0', '', '0', 'hexin 返回xml', '1', '2017-04-07 14:59:09', '2017-04-10 19:06:35');
INSERT INTO `api` VALUES ('6', 'test', 'http://l2-hq.10jqka.com.cn/hexin1', null, '0', '', '0', '', '1', '2017-04-07 15:08:41', '2017-04-07 15:08:41');
INSERT INTO `api` VALUES ('7', '本地认证', 'hosttest', null, '1', '', '0', '', '1', '2017-04-07 17:44:43', '2017-04-10 14:25:19');
INSERT INTO `api` VALUES ('8', '本地不认证', '', null, '0', '', '0', '返回认证token', '1', '2017-04-10 09:40:38', '2017-04-10 14:26:16');
INSERT INTO `api` VALUES ('10', 'find数据服务中站', 'http://dataservice/orcService2.php', null, '0', '172.20.0.127', '0', '', '1', '2017-04-10 15:19:44', '2017-04-10 15:19:44');
INSERT INTO `api` VALUES ('11', 'swoole下测试', 'swoolehost', null, '1', '127.0.0.1', '0', '测试', '1', '2017-04-12 09:43:27', '2017-04-12 09:43:27');
INSERT INTO `api` VALUES ('12', 'I问财openapi平台', 'http://10.0.32.171', null, '1', '', '0', '内网开发环境', '1', '2017-04-12 16:49:59', '2017-04-12 16:49:59');

-- ----------------------------
-- Table structure for app
-- ----------------------------
DROP TABLE IF EXISTS `app`;
CREATE TABLE `app` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `applyid` int(11) DEFAULT NULL,
  `appid` varchar(40) NOT NULL COMMENT '应用ID',
  `secret` varchar(255) NOT NULL DEFAULT '' COMMENT '秘钥',
  `appname` varchar(50) NOT NULL DEFAULT '' COMMENT '应用名称',
  `applyuser` int(11) DEFAULT NULL COMMENT '申请用户',
  `applydate` datetime DEFAULT NULL COMMENT '申请时间',
  `ctime` datetime DEFAULT NULL,
  `mtime` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `isvalid` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_appid` (`appid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='应用表';

-- ----------------------------
-- Records of app
-- ----------------------------
INSERT INTO `app` VALUES ('3', '1', '58eae00591d05', '079b18da78622fddc4a98d4f37fff3f2b5060289', '测试', null, '2017-04-10 09:27:26', '2017-04-10 09:29:41', '2017-04-10 09:29:41', '1');
INSERT INTO `app` VALUES ('4', '2', '58eae06423019', 'ca8adc3632878a53f824d2a3a897473a077fc1a8', '收费产品', null, '2017-04-10 09:30:36', '2017-04-10 09:31:16', '2017-04-10 09:31:16', '1');
INSERT INTO `app` VALUES ('5', '3', '58ed892162bee', 'f75729d82653a20a8faff857113cb72a91ffbdac', 'swoole', null, '2017-04-12 09:46:31', '2017-04-12 09:55:45', '2017-04-12 09:55:45', '1');

-- ----------------------------
-- Table structure for app_apply
-- ----------------------------
DROP TABLE IF EXISTS `app_apply`;
CREATE TABLE `app_apply` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `appname` varchar(50) NOT NULL DEFAULT '' COMMENT '应用名称',
  `applyuser` int(11) DEFAULT NULL COMMENT '申请用户',
  `status` tinyint(4) NOT NULL COMMENT '0 申请中 1 同意 2 驳回',
  `ctime` datetime DEFAULT NULL,
  `mtime` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `isvalid` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_appname` (`appname`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='应用申请表';

-- ----------------------------
-- Records of app_apply
-- ----------------------------
INSERT INTO `app_apply` VALUES ('1', '测试', null, '1', '2017-04-10 09:27:26', '2017-04-10 09:29:41', '1');
INSERT INTO `app_apply` VALUES ('2', '收费产品', null, '1', '2017-04-10 09:30:36', '2017-04-10 09:31:16', '1');
INSERT INTO `app_apply` VALUES ('3', 'swoole', null, '1', '2017-04-12 09:46:31', '2017-04-12 09:55:45', '1');

-- ----------------------------
-- Table structure for auth_resource
-- ----------------------------
DROP TABLE IF EXISTS `auth_resource`;
CREATE TABLE `auth_resource` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `appid` varchar(40) NOT NULL COMMENT '应用Id',
  `apiid` int(11) NOT NULL COMMENT 'Api.id',
  `isvalid` tinyint(1) NOT NULL COMMENT '是否有效',
  `ctime` datetime DEFAULT NULL,
  `mtime` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_appid` (`appid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='权限表';

-- ----------------------------
-- Records of auth_resource
-- ----------------------------
INSERT INTO `auth_resource` VALUES ('1', '58eae00591d05', '1', '1', '2017-04-10 09:32:17', '2017-04-10 09:32:17');
INSERT INTO `auth_resource` VALUES ('2', '58eae00591d05', '7', '1', '2017-04-10 09:32:17', '2017-04-10 09:32:17');
INSERT INTO `auth_resource` VALUES ('3', '58eae00591d05', '12', '1', '2017-04-12 17:10:29', '2017-04-12 17:10:29');

-- ----------------------------
-- Table structure for auth_third_bind
-- ----------------------------
DROP TABLE IF EXISTS `auth_third_bind`;
CREATE TABLE `auth_third_bind` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(20) NOT NULL,
  `appid` varchar(40) NOT NULL,
  `third_name` varchar(50) NOT NULL,
  `third_pwd` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `indx_appid_type` (`appid`,`type`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='第三方认证信息绑定';

-- ----------------------------
-- Records of auth_third_bind
-- ----------------------------
INSERT INTO `auth_third_bind` VALUES ('2', 'iwencai', '58eae00591d05', '0edA72641852', 'f4ac7af60a86cd62dad42a7f272ec4d7');

-- ----------------------------
-- Table structure for groups
-- ----------------------------
DROP TABLE IF EXISTS `groups`;
CREATE TABLE `groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupname` varchar(100) NOT NULL DEFAULT '' COMMENT '组名',
  `sort` int(11) NOT NULL DEFAULT '10000' COMMENT '排序',
  `isvalid` tinyint(1) NOT NULL DEFAULT '1',
  `ctime` datetime DEFAULT NULL,
  `mtime` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='服务分组';

-- ----------------------------
-- Records of groups
-- ----------------------------
INSERT INTO `groups` VALUES ('0', '其他', '10000', '1', null, '2017-04-07 17:14:29');

-- ----------------------------
-- Table structure for router_map
-- ----------------------------
DROP TABLE IF EXISTS `router_map`;
CREATE TABLE `router_map` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `router` varchar(255) NOT NULL DEFAULT '' COMMENT '本地路由key，模块+控制器+动作 如: test/test/test',
  `routername` varchar(50) DEFAULT NULL,
  `apiid` int(11) NOT NULL DEFAULT '0' COMMENT 'api.id',
  `isvalid` tinyint(1) NOT NULL COMMENT '是否有效',
  `ctime` datetime DEFAULT NULL,
  `mtime` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_router` (`router`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='本地路由映射接表';

-- ----------------------------
-- Records of router_map
-- ----------------------------
INSERT INTO `router_map` VALUES ('1', 'api/quote/quote', '行情服务quote', '1', '1', '2017-04-10 09:26:06', '2017-04-11 09:08:12');
INSERT INTO `router_map` VALUES ('2', 'api/quote/hexin', '行情服务hexin', '5', '1', '2017-04-10 09:26:18', '2017-04-11 09:08:18');
INSERT INTO `router_map` VALUES ('3', 'api/token/get', 'Token获取', '8', '1', '2017-04-10 09:42:42', '2017-04-11 09:08:28');
INSERT INTO `router_map` VALUES ('4', 'api/quote/formulacache', 'redis行情获取', '8', '1', '2017-04-10 14:26:45', '2017-04-11 09:08:45');
INSERT INTO `router_map` VALUES ('5', 'api/quote/ifinddataservice', 'ifind数据中心数据获取', '10', '1', '2017-04-10 15:20:22', '2017-04-11 09:08:55');
INSERT INTO `router_map` VALUES ('6', 'api/swoole/swoole', 'swoole测试', '11', '1', '2017-04-12 09:46:21', '2017-04-12 09:46:21');
INSERT INTO `router_map` VALUES ('7', 'api/iwencai/openapi', 'i问财api开放平台', '12', '1', '2017-04-12 16:54:39', '2017-04-12 16:54:39');

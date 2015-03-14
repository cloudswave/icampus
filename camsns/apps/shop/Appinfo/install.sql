/*
Navicat MySQL Data Transfer

Source Server         : 127.0.0.1
Source Server Version : 50516
Source Host           : 127.0.0.1:3306
Source Database       : sociax_team

Target Server Type    : MYSQL
Target Server Version : 50516
File Encoding         : 65001

Date: 2012-06-06 16:10:35
*/


SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for `ts_shop`
-- ----------------------------
DROP TABLE IF EXISTS `ts_shop`;
CREATE TABLE `ts_shop` (
  `sid` int(10) NOT NULL AUTO_INCREMENT,
  `shop_name` char(50) NOT NULL,
  `shop_ico` varchar(255) NOT NULL,
  `shop_num` int(11) NOT NULL,
  `use_cont` text NOT NULL,
  `credit_type` char(15) DEFAULT '0',
  `credit` int(10) DEFAULT '0',
  `people` int(10) DEFAULT '0',
  `endtime` int(11) NOT NULL,
  PRIMARY KEY (`sid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `ts_shop_convert`
-- ----------------------------
DROP TABLE IF EXISTS `ts_shop_convert`;
CREATE TABLE `ts_shop_convert` (
  `cid` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL,
  `sid` int(10) NOT NULL,
  `dateline` int(11) NOT NULL,
  `shop_num` int(5) DEFAULT '0',
  `get` int(2) DEFAULT '0',
  PRIMARY KEY (`cid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `ts_weiba_post`
-- ----------------------------
DROP TABLE IF EXISTS `ts_shop_user`;
CREATE TABLE `ts_shop_user` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL,
  `connum` int(10) NOT NULL,
  `usercre` int(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- ----------------------------
-- Records of sociax_contact_user
-- ----------------------------

-- /* 插入system_data数据表数据 */
INSERT INTO `ts_system_config` (`list`, `key`, `value`, `mtime`) VALUES ('pageKey', 'shop_Admin_index', 'a:4:{s:3:"key";a:9:{s:3:"sid";s:3:"sid";s:9:"shop_name";s:9:"shop_name";s:8:"shop_ico";s:8:"shop_ico";s:8:"shop_num";s:8:"shop_num";s:11:"credit_type";s:11:"credit_type";s:6:"credit";s:6:"credit";s:6:"people";s:6:"people";s:7:"endtime";s:7:"endtime";s:8:"DOACTION";s:8:"DOACTION";}s:8:"key_name";a:9:{s:3:"sid";s:8:"商品ID";s:9:"shop_name";s:9:"商品名";s:8:"shop_ico";s:12:"商品图片";s:8:"shop_num";s:12:"商品数量";s:11:"credit_type";s:12:"积分类型";s:6:"credit";s:12:"兑换积分";s:6:"people";s:12:"兑换人数";s:7:"endtime";s:12:"结束时间";s:8:"DOACTION";s:6:"操作";}s:10:"key_hidden";a:9:{s:3:"sid";s:1:"0";s:9:"shop_name";s:1:"0";s:8:"shop_ico";s:1:"0";s:8:"shop_num";s:1:"0";s:11:"credit_type";s:1:"0";s:6:"credit";s:1:"0";s:6:"people";s:1:"0";s:7:"endtime";s:1:"0";s:8:"DOACTION";s:1:"0";}s:14:"key_javascript";a:9:{s:3:"sid";s:0:"";s:9:"shop_name";s:0:"";s:8:"shop_ico";s:0:"";s:8:"shop_num";s:0:"";s:11:"credit_type";s:0:"";s:6:"credit";s:0:"";s:6:"people";s:0:"";s:7:"endtime";s:0:"";s:8:"DOACTION";s:0:"";}}', '2013-09-01 19:51:12');
INSERT INTO `ts_system_config` (`list`, `key`, `value`, `mtime`) VALUES ('pageKey', 'shop_Admin_addshop', 'a:6:{s:3:"key";a:7:{s:9:"shop_name";s:9:"shop_name";s:8:"shop_ico";s:8:"shop_ico";s:8:"shop_num";s:8:"shop_num";s:8:"use_cont";s:8:"use_cont";s:11:"credit_type";s:11:"credit_type";s:6:"credit";s:6:"credit";s:7:"endtime";s:7:"endtime";}s:8:"key_name";a:7:{s:9:"shop_name";s:12:"商品名称";s:8:"shop_ico";s:12:"商品图片";s:8:"shop_num";s:12:"商品数量";s:8:"use_cont";s:6:"介绍";s:11:"credit_type";s:18:"兑换积分类型";s:6:"credit";s:18:"兑换所需积分";s:7:"endtime";s:12:"结束时间";}s:8:"key_type";a:7:{s:9:"shop_name";s:4:"text";s:8:"shop_ico";s:5:"image";s:8:"shop_num";s:4:"text";s:8:"use_cont";s:6:"editor";s:11:"credit_type";s:5:"radio";s:6:"credit";s:4:"text";s:7:"endtime";s:4:"date";}s:11:"key_default";a:7:{s:9:"shop_name";s:0:"";s:8:"shop_ico";s:0:"";s:8:"shop_num";s:0:"";s:8:"use_cont";s:0:"";s:11:"credit_type";s:0:"";s:6:"credit";s:0:"";s:7:"endtime";s:0:"";}s:9:"key_tishi";a:7:{s:9:"shop_name";s:0:"";s:8:"shop_ico";s:0:"";s:8:"shop_num";s:0:"";s:8:"use_cont";s:0:"";s:11:"credit_type";s:0:"";s:6:"credit";s:0:"";s:7:"endtime";s:0:"";}s:14:"key_javascript";a:7:{s:9:"shop_name";s:0:"";s:8:"shop_ico";s:0:"";s:8:"shop_num";s:0:"";s:8:"use_cont";s:0:"";s:11:"credit_type";s:0:"";s:6:"credit";s:0:"";s:7:"endtime";s:0:"";}}', '2013-09-01 19:54:43');
INSERT INTO `ts_system_config` (`list`, `key`, `value`, `mtime`) VALUES ('pageKey', 'shop_Admin_editshop', 'a:6:{s:3:"key";a:8:{s:3:"sid";s:3:"sid";s:9:"shop_name";s:9:"shop_name";s:8:"shop_ico";s:8:"shop_ico";s:8:"shop_num";s:8:"shop_num";s:8:"use_cont";s:8:"use_cont";s:11:"credit_type";s:11:"credit_type";s:6:"credit";s:6:"credit";s:7:"endtime";s:7:"endtime";}s:8:"key_name";a:8:{s:3:"sid";s:8:"商品ID";s:9:"shop_name";s:9:"商品名";s:8:"shop_ico";s:12:"商品图片";s:8:"shop_num";s:12:"商品数量";s:8:"use_cont";s:6:"介绍";s:11:"credit_type";s:18:"兑换积分类型";s:6:"credit";s:18:"兑换所需积分";s:7:"endtime";s:12:"结束时间";}s:8:"key_type";a:8:{s:3:"sid";s:6:"hidden";s:9:"shop_name";s:4:"text";s:8:"shop_ico";s:5:"image";s:8:"shop_num";s:4:"text";s:8:"use_cont";s:6:"editor";s:11:"credit_type";s:5:"radio";s:6:"credit";s:4:"text";s:7:"endtime";s:4:"date";}s:11:"key_default";a:8:{s:3:"sid";s:0:"";s:9:"shop_name";s:0:"";s:8:"shop_ico";s:0:"";s:8:"shop_num";s:0:"";s:8:"use_cont";s:0:"";s:11:"credit_type";s:0:"";s:6:"credit";s:0:"";s:7:"endtime";s:0:"";}s:9:"key_tishi";a:8:{s:3:"sid";s:0:"";s:9:"shop_name";s:0:"";s:8:"shop_ico";s:0:"";s:8:"shop_num";s:0:"";s:8:"use_cont";s:0:"";s:11:"credit_type";s:0:"";s:6:"credit";s:0:"";s:7:"endtime";s:0:"";}s:14:"key_javascript";a:8:{s:3:"sid";s:0:"";s:9:"shop_name";s:0:"";s:8:"shop_ico";s:0:"";s:8:"shop_num";s:0:"";s:8:"use_cont";s:0:"";s:11:"credit_type";s:0:"";s:6:"credit";s:0:"";s:7:"endtime";s:0:"";}}', '2013-09-01 19:51:23');
INSERT INTO `ts_system_config` (`list`, `key`, `value`, `mtime`) VALUES ('pageKey', 'shop_Admin_convert', 'a:4:{s:3:"key";a:7:{s:3:"cid";s:3:"cid";s:3:"uid";s:3:"uid";s:3:"sid";s:3:"sid";s:8:"dateline";s:8:"dateline";s:8:"shop_num";s:8:"shop_num";s:3:"get";s:3:"get";s:6:"setget";s:6:"setget";}s:8:"key_name";a:7:{s:3:"cid";s:6:"序号";s:3:"uid";s:9:"用户名";s:3:"sid";s:8:"商品ID";s:8:"dateline";s:12:"兑换时间";s:8:"shop_num";s:12:"兑换数量";s:3:"get";s:12:"领取状态";s:6:"setget";s:6:"操作";}s:10:"key_hidden";a:7:{s:3:"cid";s:1:"0";s:3:"uid";s:1:"0";s:3:"sid";s:1:"0";s:8:"dateline";s:1:"0";s:8:"shop_num";s:1:"0";s:3:"get";s:1:"0";s:6:"setget";s:1:"0";}s:14:"key_javascript";a:7:{s:3:"cid";s:0:"";s:3:"uid";s:0:"";s:3:"sid";s:0:"";s:8:"dateline";s:0:"";s:8:"shop_num";s:0:"";s:3:"get";s:0:"";s:6:"setget";s:0:"";}}', '2013-04-12 22:49:43');
INSERT INTO `ts_system_config` (`list`, `key`, `value`, `mtime`) VALUES ('pageKey', 'shop_Admin_update', 'a:6:{s:3:"key";a:1:{s:8:"doupdate";s:8:"doupdate";}s:8:"key_name";a:1:{s:8:"doupdate";s:27:"确定要升级软件吗？";}s:8:"key_type";a:1:{s:8:"doupdate";s:5:"radio";}s:11:"key_default";a:1:{s:8:"doupdate";s:0:"";}s:9:"key_tishi";a:1:{s:8:"doupdate";s:84:"如果是最新版本，升级软件可能会造成数据丢失！请谨慎升级！";}s:14:"key_javascript";a:1:{s:8:"doupdate";s:0:"";}}', '2013-09-01 22:10:59');
INSERT INTO `ts_lang` (`key`,`appname`,`filetype`,`zh-cn`,`en`,`zh-tw`) VALUES ('PUBLIC_APPNAME_SHOP','PUBLIC','0','积分商城','shop','积分商城');

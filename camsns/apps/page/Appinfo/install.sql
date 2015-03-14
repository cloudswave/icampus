/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50525
Source Host           : 127.0.0.1:3306
Source Database       : t3

Target Server Type    : MYSQL
Target Server Version : 50525
File Encoding         : 65001

Date: 2012-11-08 10:18:11
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `ts_diy_canvas`
-- ----------------------------
DROP TABLE IF EXISTS `ts_diy_canvas`;
CREATE TABLE `ts_diy_canvas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `canvas_name` varchar(255) DEFAULT NULL,
  `data` text,
  `description` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ts_diy_canvas
-- ----------------------------
INSERT INTO `ts_diy_canvas` VALUES ('1', '首页', 'index.html', 'PGluY2x1ZGUgZmlsZT0iX19USEVNRV9fL3B1YmxpY19oZWFkZXIiIC8+DQo8ZGl2IHN0eWxlPSJoZWlnaHQ6MTAwcHgiPjwvZGl2Pg0KPGluY2x1ZGUgZmlsZT0iX19USEVNRV9fL2RpeV9oZWFkZXIiIC8+DQo8bGluayBocmVmPSJfX0FQUF9fL1B1YmxpYy9jc3MvZGl5X2FkYXB0YWJsZS5jc3MiIHJlbD0ic3R5bGVzaGVldCIgdHlwZT0idGV4dC9jc3MiIC8+DQo8bGluayBocmVmPSJfX0FQUF9fL1B1YmxpYy9jc3MvaW5kZXguY3NzIiByZWw9InN0eWxlc2hlZXQiIHR5cGU9InRleHQvY3NzIiAvPg0KPGxpbmsgaHJlZj0iX19BUFBfXy9QdWJsaWMvY3NzL3BvcF91cC5jc3MiIHJlbD0ic3R5bGVzaGVldCIgdHlwZT0idGV4dC9jc3MiIC8+DQo8ZGl2IGNsYXNzPSJkaXlfY29udGVudCBiZ19kaXkiPg0KeyRkYXRhfWRkZA0KICAgIDxkaXYgY2xhc3M9IkMiPg0KICAgIDwvZGl2Pg0KDQoNCjwvZGl2Pg0KDQo8aW5jbHVkZSBmaWxlPSJfX1RIRU1FX18vcHVibGljX2Zvb3RlciIgLz4=', '首页');
INSERT INTO `ts_diy_canvas` VALUES ('2', 'test', 'test1.html', 'PGluY2x1ZGUgZmlsZT0iX19USEVNRV9fL3B1YmxpY19oZWFkZXIiIC8+DQo8ZGl2IHN0eWxlPSJoZWlnaHQ6MTAwcHgiPjwvZGl2Pg0KPGluY2x1ZGUgZmlsZT0iX19USEVNRV9fL2RpeV9oZWFkZXIiIC8+DQo8bGluayBocmVmPSJfX0FQUF9fL1B1YmxpYy9jc3MvZGl5X2FkYXB0YWJsZS5jc3MiIHJlbD0ic3R5bGVzaGVldCIgdHlwZT0idGV4dC9jc3MiIC8+DQo8bGluayBocmVmPSJfX0FQUF9fL1B1YmxpYy9jc3MvaW5kZXguY3NzIiByZWw9InN0eWxlc2hlZXQiIHR5cGU9InRleHQvY3NzIiAvPg0KPGxpbmsgaHJlZj0iX19BUFBfXy9QdWJsaWMvY3NzL3BvcF91cC5jc3MiIHJlbD0ic3R5bGVzaGVldCIgdHlwZT0idGV4dC9jc3MiIC8+DQo8ZGl2IGNsYXNzPSJkaXlfY29udGVudCBiZ19kaXkiPg0KeyRkYXRhfQ0KICAgIDxkaXYgY2xhc3M9IkMiPg0KICAgIDwvZGl2Pg0KDQoNCjwvZGl2Pg0KDQo8aW5jbHVkZSBmaWxlPSJfX1RIRU1FX18vcHVibGljX2Zvb3RlciIgLz4=', 'sd');

-- ----------------------------
-- Table structure for `ts_diy_page`
-- ----------------------------
DROP TABLE IF EXISTS `ts_diy_page`;
CREATE TABLE `ts_diy_page` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `domain` varchar(100) NOT NULL,
  `page_name` varchar(30) NOT NULL,
  `layout_data` text,
  `widget_data` text,
  `canvas` varchar(255) DEFAULT NULL,
  `lock` tinyint(1) DEFAULT '0' COMMENT '是否锁定不可以删除',
  `status` tinyint(1) DEFAULT '1' COMMENT '是否开放用户访问',
  `guest` tinyint(1) DEFAULT '1' COMMENT '游客是否可以访问',
  `visit_count` int(11) unsigned DEFAULT '0',
  `uid` int(11) DEFAULT NULL,
  `manager` varchar(255) DEFAULT '',
  `ctime` int(11) DEFAULT NULL,
  `seo_title` varchar(255) DEFAULT NULL,
  `seo_keywords` varchar(255) DEFAULT NULL,
  `seo_description` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=98 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ts_diy_page
-- ----------------------------
INSERT INTO `ts_diy_page` VALUES ('97', 'test', 'test', '<div id=\'1F1352338954\' class=\'diy_1\'><div class=\"diy_1_C\"><div id=\"1F1352338954-diy_1_C-1\" rel = \"w:DiySendFrame\" class=\"mb10\" sign= \"50d903dca03ebf99\">[widget:50d903dca03ebf99]</div><div id=\"1F1352338954-diy_1_C-2\" rel = \"w:DiyWeibo\" class=\"mb10\" sign= \"d5fbb6d0ff42c49e\">[widget:d5fbb6d0ff42c49e]</div></div>\n    </div>', 'a:1:{s:12:\"1F1352338954\";a:1:{s:7:\"diy_1_C\";a:2:{i:0;s:16:\"50d903dca03ebf99\";i:1;s:16:\"d5fbb6d0ff42c49e\";}}}', 'test.html', '1', '1', '1', '56', '28199', '28206,10045', '1351561065', 'test', 'stset', 'setset');

-- ----------------------------
-- Table structure for `ts_diy_widget`
-- ----------------------------
DROP TABLE IF EXISTS `ts_diy_widget`;
CREATE TABLE `ts_diy_widget` (
  `widgetId` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `pluginId` varchar(255) NOT NULL,
  `pageId` int(11) NOT NULL,
  `channelId` int(11) NOT NULL,
  `taglib` text,
  `content` text,
  `ext` text,
  `cache` text,
  `cacheTime` int(11) NOT NULL DEFAULT '0',
  `cTime` int(11) DEFAULT NULL,
  `mTime` int(11) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`widgetId`)
) ENGINE=MyISAM AUTO_INCREMENT=2870 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ts_diy_widget
-- ----------------------------

/*
Navicat MySQL Data Transfer
Source Host     : localhost:3306
Source Database : sociax_2_0
Target Host     : localhost:3306
Target Database : sociax_2_0
Date: 2011-02-12 18:03:39
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for ts_poster
-- ----------------------------
DROP TABLE IF EXISTS `ts_poster`;
CREATE TABLE `ts_poster` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `type` int(11) DEFAULT NULL,
  `uid` int(11) NOT NULL,
  `address_city` int(11) DEFAULT NULL,
  `address_province` int(11) DEFAULT NULL,
  `address_area` int(11) DEFAULT NULL,
  `area` varchar(255) DEFAULT NULL,
  `cTime` int(11) DEFAULT NULL,
  `deadline` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `contact` varchar(255) DEFAULT NULL,
  `private` int(1) NOT NULL DEFAULT '0',
  `cover` varchar(255) DEFAULT NULL,
  `extra1` varchar(255) DEFAULT NULL,
  `extra2` varchar(255) DEFAULT NULL,
  `extra3` varchar(255) DEFAULT NULL,
  `extra4` varchar(255) DEFAULT NULL,
  `extra5` varchar(255) DEFAULT NULL,
  `recommend` int(1) DEFAULT '0',
  `attach_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for ts_poster_small_type
-- ----------------------------
DROP TABLE IF EXISTS `ts_poster_small_type`;
CREATE TABLE `ts_poster_small_type` (
  `id` int(11) NOT NULL auto_increment,
  `label` varchar(255) NOT NULL default '类别',
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- ----------------------------
-- Records of ts_poster_small_type
-- ----------------------------
INSERT INTO `ts_poster_small_type` VALUES ('1', '物品分类', '影碟音像');
INSERT INTO `ts_poster_small_type` VALUES ('2', '物品分类', '书籍资料');
INSERT INTO `ts_poster_small_type` VALUES ('3', '物品分类', '服装饰品');
INSERT INTO `ts_poster_small_type` VALUES ('4', '物品分类', '日用百货');
INSERT INTO `ts_poster_small_type` VALUES ('5', '物品分类', '化妆保健品');
INSERT INTO `ts_poster_small_type` VALUES ('6', '物品分类', '票务优惠券');
INSERT INTO `ts_poster_small_type` VALUES ('7', '物品分类', '电脑相关');
INSERT INTO `ts_poster_small_type` VALUES ('8', '物品分类', '数码通讯');
INSERT INTO `ts_poster_small_type` VALUES ('9', '物品分类', '电家具');
INSERT INTO `ts_poster_small_type` VALUES ('10', '物品分类', '婴童用品');
INSERT INTO `ts_poster_small_type` VALUES ('11', '物品分类', '工艺收藏');
INSERT INTO `ts_poster_small_type` VALUES ('12', '物品分类', '其它');
INSERT INTO `ts_poster_small_type` VALUES ('13', '团购种类', '我要发起一个新团购');
INSERT INTO `ts_poster_small_type` VALUES ('14', '团购种类', '我要告诉大家团购消息');
INSERT INTO `ts_poster_small_type` VALUES ('15', '房屋信息', '出租');
INSERT INTO `ts_poster_small_type` VALUES ('16', '房屋信息', '求租');

-- ----------------------------
-- Table structure for ts_poster_type
-- ----------------------------
DROP TABLE IF EXISTS `ts_poster_type`;
CREATE TABLE `ts_poster_type` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) default NULL,
  `templet` varchar(255) default NULL,
  `explain` varchar(255) NOT NULL,
  `ico` varchar(255) NOT NULL,
  `state` tinyint(1) default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ts_poster_type
-- ----------------------------
INSERT INTO `ts_poster_type` VALUES ('1', '分享物品', '物品分类', '12', '与好友分享你的闲置物品如书籍、影碟、生活用品等，赠送、交换或出借', 'icon_fx.gif', '0');
INSERT INTO `ts_poster_type` VALUES ('2', '出售二手物品', '物品分类', '13', '发布各类二手物品出售信息，可通过好友转发给更多的人', 'icon_rs.gif', '0');
INSERT INTO `ts_poster_type` VALUES ('3', '团购信息', '团购种类', null, '你发起或想要告诉好友的任何团购优惠', 'icon_tg.gif', '0');
INSERT INTO `ts_poster_type` VALUES ('4', '拼车', '物品类别', null, '发布拼车、搭顺风车相关信息', 'icon_pc.gif', '0');
INSERT INTO `ts_poster_type` VALUES ('5', '物品求购', '物品分类', '13', '发布你的生活物品等的求购信息', 'icon_qg.gif', '0');
INSERT INTO `ts_poster_type` VALUES ('6', '房屋求租、出租', '房屋信息', '14,15,16', '发布房屋出租或求租、合租等信息。', 'icon_house1.gif', '0');

-- ----------------------------
-- Table structure for ts_poster_widget
-- ----------------------------
DROP TABLE IF EXISTS `ts_poster_widget`;
CREATE TABLE `ts_poster_widget` (
  `id` int(11) NOT NULL auto_increment,
  `label` varchar(255) NOT NULL,
  `widget` varchar(255) NOT NULL,
  `data` text NOT NULL,
  `field` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ts_poster_widget
-- ----------------------------
INSERT INTO `ts_poster_widget` VALUES ('12', '分享方式', 'CheckBox', 'a:3:{i:0;s:16:\"赠送[selected]\";i:1;s:8:\"\r\n借用\";i:2;s:8:\"\r\n交换\";}', 'extra1');
INSERT INTO `ts_poster_widget` VALUES ('13', '价格', 'Input', 's:0:\"\";', 'extra1');
INSERT INTO `ts_poster_widget` VALUES ('14', '位置', 'Input', 's:0:\"\";', 'extra1');
INSERT INTO `ts_poster_widget` VALUES ('15', '面积', 'Input', 's:0:\"\";', 'extra2');
INSERT INTO `ts_poster_widget` VALUES ('16', '租金', 'Input', 's:0:\"\";', 'extra3');

#模板数据
DELETE FROM `ts_template` WHERE `type` = 'poster';
INSERT INTO `ts_template` (`name`, `alias`, `title`, `body`, `lang`, `type`, `type2`, `is_cache`, `ctime`) 
VALUES
    ('poster_create_weibo', '发布招贴', '','我发起了一份招贴：【{title}】{url}', 'zh', 'poster', 'weibo', 0, 1290417734),
    ('poster_share_weibo', '分享招贴', '', '分享@{author} 的招贴:【{title}】 {url}', 'zh',  'poster', 'weibo', 0, 1290595552);

# 增加默认积分配置
DELETE FROM `ts_credit_setting` WHERE `type` = 'poster';
INSERT INTO `ts_credit_setting` (`id`, `name`, `alias`, `type`, `info`, `score`, `experience`) 
VALUES
    ('', 'add_poster', '发起招贴', 'poster', '{action}{sign}了{score}{typecn}', '2', '2'),
    ('', 'delete_poster', '删除招贴', 'poster', '{action}{sign}了{score}{typecn}', '-2', '-2');

REPLACE INTO `ts_system_data` (`list`,`key`,`value`,`mtime`) 
VALUES 
    ('poster','version_number','s:5:"36263";','2012-07-12 00:00:00');


DELETE FROM `ts_lang` WHERE `key` = 'PUBLIC_APPNAME_POSTER';
INSERT INTO `ts_lang` (`key`,`appname`,`filetype`,`zh-cn`,`en`,`zh-tw`) VALUES ('PUBLIC_APPNAME_POSTER', 'PUBLIC', '0', '招贴', 'Poster', '');
SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `ts_gift`;

CREATE TABLE `ts_gift` (
  `id` int(11) NOT NULL auto_increment,
  `categoryId` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `num` int(11) NOT NULL,
  `price` int(11) NOT NULL,
  `img` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL default '1',
  `cTime` int(11) NOT NULL,
  -- `feed_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=79 DEFAULT CHARSET=utf8;

INSERT INTO `ts_gift` VALUES ('56', '2', '冰块', '998', '50', '4a6ff66da5b9f.gif', '1', '1248851565');
INSERT INTO `ts_gift` VALUES ('22', '1', '玫瑰', '943', '28', 'birth1.gif', '1', '1214839221');
INSERT INTO `ts_gift` VALUES ('23', '1', '开心蛋糕', '928', '38', 'birth2.gif', '1', '1214839221');
INSERT INTO `ts_gift` VALUES ('24', '1', '钻石', '958', '50', 'birth3.gif', '1', '1214839221');
INSERT INTO `ts_gift` VALUES ('25', '1', '金元宝', '979', '50', 'birth4.gif', '1', '1214839221');
INSERT INTO `ts_gift` VALUES ('26', '1', '宝贝熊', '988', '36', 'birth5.gif', '1', '1214839221');
INSERT INTO `ts_gift` VALUES ('27', '1', '香槟', '974', '22', 'birth6.gif', '1', '1214839221');
INSERT INTO `ts_gift` VALUES ('28', '1', '心愿', '999', '20', 'birth7.gif', '1', '1214839221');
INSERT INTO `ts_gift` VALUES ('29', '1', '浓情棒棒糖', '993', '20', 'birth8.gif', '1', '1214839221');
INSERT INTO `ts_gift` VALUES ('30', '1', '女人最爱', '956', '33', 'birth9.gif', '1', '1214839221');
INSERT INTO `ts_gift` VALUES ('31', '1', '男人期待', '980', '33', 'birth10.gif', '1', '1214839221');
INSERT INTO `ts_gift` VALUES ('32', '2', '衬衣', '999', '20', 'new1.gif', '1', '1214839221');
INSERT INTO `ts_gift` VALUES ('33', '2', '哇财', '988', '45', 'new2.gif', '1', '1214839221');
INSERT INTO `ts_gift` VALUES ('34', '2', '口红', '1000', '20', 'new3.gif', '1', '1214839221');
INSERT INTO `ts_gift` VALUES ('35', '2', '洗衣板', '1000', '22', 'new4.gif', '1', '1214839221');
INSERT INTO `ts_gift` VALUES ('36', '2', '性感肚兜', '999', '30', 'new5.gif', '1', '1214839221');
INSERT INTO `ts_gift` VALUES ('37', '2', '靓丽高跟鞋', '1000', '35', 'new6.gif', '1', '1214839221');
INSERT INTO `ts_gift` VALUES ('38', '2', '浓情红玫瑰', '1000', '26', 'new7.gif', '1', '1214839221');
INSERT INTO `ts_gift` VALUES ('39', '2', '剃须刀', '1000', '28', 'new8.gif', '1', '1214839221');
INSERT INTO `ts_gift` VALUES ('40', '2', '真爱冰激淋', '1000', '20', 'new9.gif', '1', '1214839221');
INSERT INTO `ts_gift` VALUES ('41', '2', '奶嘴', '997', '20', 'new10.gif', '1', '1214839221');
INSERT INTO `ts_gift` VALUES ('43', '1', '雷公', '872', '22', 'birth11.gif', '1', '1214839221');
INSERT INTO `ts_gift` VALUES ('44', '1', '电母', '885', '22', 'birth12.gif', '1', '1214839221');
INSERT INTO `ts_gift` VALUES ('45', '1', '协会', '885', '25', 'birth13.gif', '1', '1214839221');
INSERT INTO `ts_gift` VALUES ('46', '1', '雷语', '992', '22', 'birth14.gif', '1', '1214839221');
INSERT INTO `ts_gift` VALUES ('47', '1', '小队长', '888', '20', 'birth15.gif', '1', '1214839221');
INSERT INTO `ts_gift` VALUES ('48', '1', '中队长', '886', '20', 'birth16.gif', '1', '1214839221');
INSERT INTO `ts_gift` VALUES ('49', '1', '大队长', '878', '20', 'birth17.gif', '1', '1214839221');
INSERT INTO `ts_gift` VALUES ('50', '2', '帅哥证', '999', '26', 'new11.gif', '1', '1214839221');
INSERT INTO `ts_gift` VALUES ('51', '2', '美女证', '1000', '26', 'new12.gif', '1', '1214839221');
INSERT INTO `ts_gift` VALUES ('52', '2', '公章', '1000', '28', 'new13.gif', '1', '1214839221');
INSERT INTO `ts_gift` VALUES ('53', '2', '公章', '1000', '28', 'new14.gif', '1', '1214839221');
INSERT INTO `ts_gift` VALUES ('54', '2', '公章', '1000', '28', 'new15.gif', '1', '1214839221');
INSERT INTO `ts_gift` VALUES ('55', '1', '豪华跑车', '876', '45', 'birth18.gif', '1', '1214839221');
INSERT INTO `ts_gift` VALUES ('57', '2', '啤酒', '988', '50', '4a6ff694f1a7a.gif', '1', '1248851604');
INSERT INTO `ts_gift` VALUES ('58', '1', '礼物盒', '995', '50', '4a6ff7c85bd99.gif', '1', '1248851912');
INSERT INTO `ts_gift` VALUES ('59', '2', '乒乓球拍', '997', '50', '4a6ffa25bb600.gif', '1', '1248852517');
INSERT INTO `ts_gift` VALUES ('60', '2', '网球', '999', '50', '4a6ffa3b53591.gif', '1', '1248852539');
INSERT INTO `ts_gift` VALUES ('61', '2', '高尔夫球', '998', '50', '4a6ffa4e50ea3.gif', '1', '1248852558');
INSERT INTO `ts_gift` VALUES ('62', '2', '橄榄球', '999', '50', '4a6ffa69b46dd.gif', '1', '1248852585');
INSERT INTO `ts_gift` VALUES ('63', '2', '排球', '998', '50', '4a6ffa7c62a7a.gif', '1', '1248852604');
INSERT INTO `ts_gift` VALUES ('64', '2', '篮球', '996', '50', '4a6ffa94366a0.gif', '1', '1248852628');
INSERT INTO `ts_gift` VALUES ('65', '2', '足球', '987', '50', '4a6ffa9ee5d18.gif', '1', '1248852638');
INSERT INTO `ts_gift` VALUES ('66', '1', '红枣粽子', '997', '50', '4a6ffc7d10214.gif', '1', '1248853117');
INSERT INTO `ts_gift` VALUES ('67', '1', '运动鞋', '994', '100', '4a6ffe72c1046.gif', '1', '1248853618');
INSERT INTO `ts_gift` VALUES ('68', '1', '披萨', '987', '100', '4a700398492ca.gif', '1', '1248854936');
INSERT INTO `ts_gift` VALUES ('69', '1', '购物袋', '994', '100', '4a7004032f310.gif', '1', '1248855043');
INSERT INTO `ts_gift` VALUES ('70', '2', '吸血蝙蝠', '999', '100', '4a70046342824.gif', '1', '1248855139');
INSERT INTO `ts_gift` VALUES ('71', '1', 'MP3', '989', '100', '4a700508e3c92.gif', '1', '1248855304');
INSERT INTO `ts_gift` VALUES ('72', '1', '香水', '987', '100', '4a700724e1fa1.gif', '1', '1248855844');
INSERT INTO `ts_gift` VALUES ('73', '1', '游戏机', '998', '100', '4a70079505d66.gif', '1', '1248855957');
INSERT INTO `ts_gift` VALUES ('74', '1', '数码相机', '996', '200', '4a7007a6923ea.gif', '1', '1248855974');
INSERT INTO `ts_gift` VALUES ('75', '2', '小笼包', '997', '100', '4a700a2f649b4.gif', '1', '1248856623');
INSERT INTO `ts_gift` VALUES ('76', '2', '滑板', '997', '100', '4a700a42a35b1.gif', '1', '1248856642');
INSERT INTO `ts_gift` VALUES ('77', '1', '红色跑车', '84', '200', '4a700ae34514a.gif', '1', '1248856803');
INSERT INTO `ts_gift` VALUES ('78', '1', '急速跑车', '70', '200', '4a700afee7d2e.gif', '1', '1248856830');


DROP TABLE IF EXISTS `ts_gift_category`;

CREATE TABLE `ts_gift_category` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL default '1',
  `cTime` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

INSERT INTO `ts_gift_category` VALUES ('1', '热门礼物', '1', '0');
INSERT INTO `ts_gift_category` VALUES ('2', '最新上架', '1', '0');


DROP TABLE IF EXISTS `ts_gift_user`;
CREATE TABLE `ts_gift_user` (
  `id` int(11) NOT NULL auto_increment,
  `fromUserId` int(11) NOT NULL,
  `toUserId` int(11) NOT NULL,
  `giftPrice` int(11) NOT NULL,
  `giftImg` varchar(255) NOT NULL,
  `sendInfo` text NOT NULL,
  `sendWay` tinyint(1) NOT NULL,
  `cTime` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

#添加ts_system_data数据
REPLACE INTO `ts_system_data` (`list`,`key`,`value`,`mtime`)
VALUES
	('gift', 'credit', 's:5:\"score\";', '2010-12-24 11:22:17');

#模板数据
DELETE FROM `ts_template` WHERE  `name` = 'gift_send_weibo';
INSERT INTO `ts_template` (`name`, `alias`, `title`, `body`, `lang`, `type`, `type2`, `is_cache`, `ctime`) 
VALUES
	('gift_send_weibo', '礼物赠送', '','我送给{user} 一份礼物:【{title}】{content} 参与送礼{url}', 'zh', 'gift', 'weibo', 0, 1290417734);

#添加版本数据#
REPLACE INTO `ts_system_data` (`list`,`key`,`value`,`mtime`) 
VALUES 
    ('gift','version_number','s:5:"36263";','2012-07-12 00:00:00');

#添加系统通知节点#
DELETE FROM `ts_notify_node` WHERE `appname` = 'gift';
INSERT INTO `ts_notify_node` VALUES (0, 'gift_send', '赠送礼物', 'gift', 'NOTIFY_GIFT_SEND_CONTENT', 'NOTIFY_GIFT_SEND_TITLE', '0', '1', '1');

DELETE FROM `ts_lang` WHERE `key` = 'PUBLIC_APPNAME_GIFT';
INSERT INTO `ts_lang` (`key`,`appname`,`filetype`,`zh-cn`,`en`,`zh-tw`) VALUES ('PUBLIC_APPNAME_GIFT', 'PUBLIC', '0', '礼物', 'Gift', '');
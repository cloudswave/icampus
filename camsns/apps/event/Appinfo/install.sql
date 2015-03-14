/*
Navicat MySQL Data Transfer
Source Host     : localhost:3306
Source Database : sociax_2_0
Target Host     : localhost:3306
Target Database : sociax_2_0
Date: 2011-01-20 15:16:57
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for ts_event
-- ----------------------------
DROP TABLE IF EXISTS `ts_event`;
CREATE TABLE `ts_event` (
`id`  int(11) NOT NULL AUTO_INCREMENT COMMENT '活动Id' ,
`uid`  int(11) NOT NULL COMMENT '发起人uid' ,
`title`  text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '活动标题' ,
`explain`  text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '活动详细内容' ,
`contact`  varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '活动联系人' ,
`type`  tinyint(4) NOT NULL COMMENT '类型id，关联ts_event_type' ,
`sTime`  int(11) NULL DEFAULT NULL COMMENT '开始时间' ,
`eTime`  int(11) NULL DEFAULT NULL COMMENT '结束时间' ,
`address`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '活动地点' ,
`cTime`  int(11) NULL DEFAULT NULL COMMENT '创建时间' ,
`deadline`  int(11) NOT NULL COMMENT '有效时间' ,
`joinCount`  int(11) NOT NULL DEFAULT 0 COMMENT '已加入人数' ,
`attentionCount`  int(11) NOT NULL DEFAULT 0 COMMENT '关注数' ,
`limitCount`  int(11) NOT NULL DEFAULT 0 COMMENT '限制加入数' ,
`commentCount`  int(11) NOT NULL DEFAULT 0 COMMENT '评论数' ,
`coverId`  int(11) NOT NULL DEFAULT 0 COMMENT '封面id' ,
`optsId`  int(11) NOT NULL DEFAULT 0 COMMENT '投票选项，关联ts_event_opts' ,
`feed_id`  int(11) NOT NULL DEFAULT 0 COMMENT '微博ID' ,
PRIMARY KEY (`id`)
)ENGINE=MyISAM DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci CHECKSUM=0 ROW_FORMAT=Dynamic DELAY_KEY_WRITE=0;

-- ----------------------------
-- Table structure for ts_event_opts
-- ----------------------------
DROP TABLE IF EXISTS `ts_event_opts`;
CREATE TABLE `ts_event_opts` (
`id`  int(11) NOT NULL AUTO_INCREMENT ,
`cost`  char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '花费多少钱' ,
`costExplain`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '花费的描述' ,
`province`  char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '省' ,
`city`  char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '城市' ,
`area`  varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '地区' ,
`opts`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '选项' ,
`isHot`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否热门' ,
`rTime`  int(11) NULL DEFAULT NULL COMMENT '最后回复时间' ,
PRIMARY KEY (`id`)
)ENGINE=MyISAM DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci CHECKSUM=0 ROW_FORMAT=Dynamic DELAY_KEY_WRITE=0;

-- ----------------------------
-- Table structure for ts_event_photo
-- ----------------------------
DROP TABLE IF EXISTS `ts_event_photo`;
CREATE TABLE `ts_event_photo` (
`id`  int(11) NOT NULL AUTO_INCREMENT ,
`eventId`  int(11) NOT NULL COMMENT '活动ID，关键ts_event表' ,
`uid`  int(11) NOT NULL COMMENT '用户ID' ,
`filename`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '文件名' ,
`filepath`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '文件路径' ,
`savename`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '存储文件名' ,
`aid`  int(11) NOT NULL COMMENT '相册ID' ,
`cTime`  int(11) NULL DEFAULT NULL COMMENT '创建时间' ,
`commentCount`  int(11) NOT NULL DEFAULT 0 COMMENT '评论次数' ,
PRIMARY KEY (`id`)
)ENGINE=MyISAM DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci CHECKSUM=0 ROW_FORMAT=Dynamic DELAY_KEY_WRITE=0;

-- ----------------------------
-- Table structure for ts_event_type
-- ----------------------------
DROP TABLE IF EXISTS `ts_event_type`;
CREATE TABLE `ts_event_type` (
`id`  int(11) NOT NULL AUTO_INCREMENT ,
`name`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '类型名称' ,
PRIMARY KEY (`id`)
)ENGINE=MyISAM DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci CHECKSUM=0 ROW_FORMAT=Dynamic DELAY_KEY_WRITE=0;

-- ----------------------------
-- Records of ts_event_type
-- ----------------------------
INSERT INTO `ts_event_type` VALUES ('1', '音乐/演出');
INSERT INTO `ts_event_type` VALUES ('2', '展览');
INSERT INTO `ts_event_type` VALUES ('3', '电影');
INSERT INTO `ts_event_type` VALUES ('4', '讲座/沙龙');
INSERT INTO `ts_event_type` VALUES ('5', '戏剧/曲艺');
INSERT INTO `ts_event_type` VALUES ('8', '体育');
INSERT INTO `ts_event_type` VALUES ('9', '旅行');
INSERT INTO `ts_event_type` VALUES ('10', '公益');
INSERT INTO `ts_event_type` VALUES ('11', '其它');

-- ----------------------------
-- Table structure for ts_event_user
-- ----------------------------
DROP TABLE IF EXISTS `ts_event_user`;
CREATE TABLE `ts_event_user` (
`id`  int(11) NOT NULL AUTO_INCREMENT ,
`eventId`  int(11) NOT NULL COMMENT '活动ID' ,
`uid`  int(11) NOT NULL COMMENT '用户ID' ,
`contact`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '联系方式' ,
`action`  char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'attention' ,
`status`  tinyint(1) NOT NULL DEFAULT 1 ,
`cTime`  int(11) NOT NULL COMMENT '创建时间' ,
PRIMARY KEY (`id`)
)ENGINE=MyISAM DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci CHECKSUM=0 ROW_FORMAT=Dynamic DELAY_KEY_WRITE=0;


#模板数据
-- DELETE FROM `ts_template` WHERE `type` = 'event';
-- INSERT INTO `ts_template` (`name`, `alias`, `title`, `body`, `lang`, `type`, `type2`, `is_cache`, `ctime`) 
-- VALUES
--     ('event_create_weibo', '发起活动', '','我发起了一个活动：【{title}】{url}', 'zh', 'event', 'weibo', 0, 1290417734),
--     ('event_share_weibo', '分享活动', '', '分享@{author} 的活动:【{title}】 {url}', 'zh',  'event', 'weibo', 0, 1290595552);

# 增加默认积分配置
DELETE FROM `ts_credit_setting` WHERE `type` = 'event';
INSERT INTO `ts_credit_setting` (`id`,`name`, `alias`, `type`, `info`, `score`, `experience`) 
VALUES
    ('', 'add_event', '发起活动', 'event', '{action}{sign}了{score}{typecn}', '10', '10'),
    ('', 'delete_event', '删除活动', 'event', '{action}{sign}了{score}{typecn}', '-10', '-10'),
    ('', 'join_event', '参加活动', 'event', '{action}{sign}了{score}{typecn}', '3', '2'),
    ('', 'cancel_join_event', '取消参加活动', 'event', '{action}{sign}了{score}{typecn}', '-3', '-2');


#添加ts_system_data数据
DELETE FROM `ts_system_data` WHERE `list` = 'event';
INSERT INTO `ts_system_data` (`list`,`key`,`value`,`mtime`)
VALUES
    ('event', 'limitpage', 's:2:"10";', '2011-01-20 15:19:10'),
    ('event', 'canCreate', 's:1:"1";', '2011-01-20 15:19:10'),
    ('event', 'credit', 's:1:"0";', '2011-01-20 15:19:10'),
    ('event', 'credit_type', 's:10:"experience";', '2011-01-20 15:19:10'),
    ('event', 'limittime', 's:1:"0";', '2011-01-20 15:19:10'),
    ('event','version_number','s:5:"3.0.20130214";','2013-02-14 10:00:00');


DELETE FROM `ts_lang` WHERE `key` = 'PUBLIC_APPNAME_EVENT';
INSERT INTO `ts_lang` (`key`,`appname`,`filetype`,`zh-cn`,`en`,`zh-tw`) VALUES ('PUBLIC_APPNAME_EVENT', 'PUBLIC', '0', '活动', 'Vote', '');
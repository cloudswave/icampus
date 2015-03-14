/*
Navicat MySQL Data Transfer

Source Server         : 192.168.1.100
Source Server Version : 50527
Source Host           : 192.168.1.100:3306
Source Database       : uat_sociax

Target Server Type    : MYSQL
Target Server Version : 50527
File Encoding         : 65001

Date: 2013-04-24 19:51:28
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `ts_group`
-- ----------------------------
DROP TABLE IF EXISTS `ts_group`;
CREATE TABLE `ts_group` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) unsigned NOT NULL DEFAULT '0',
  `name` varchar(32) NOT NULL,
  `intro` text NOT NULL,
  `logo` varchar(255) NOT NULL,
  `announce` text NOT NULL,
  `cid0` smallint(6) unsigned NOT NULL,
  `cid1` smallint(6) unsigned NOT NULL,
  `membercount` smallint(6) unsigned NOT NULL DEFAULT '0',
  `threadcount` smallint(6) unsigned NOT NULL DEFAULT '0',
  `type` enum('open','limit','close') NOT NULL,
  `need_invite` tinyint(1) NOT NULL DEFAULT '2',
  `need_verify` tinyint(4) NOT NULL,
  `actor_level` tinyint(4) NOT NULL,
  `brower_level` tinyint(4) NOT NULL DEFAULT '-1',
  `openWeibo` tinyint(1) NOT NULL DEFAULT '1',
  `openBlog` tinyint(1) NOT NULL DEFAULT '1',
  `openUploadFile` tinyint(1) NOT NULL DEFAULT '1',
  `whoUploadFile` tinyint(1) NOT NULL DEFAULT '1',
  `whoDownloadFile` tinyint(1) NOT NULL DEFAULT '2',
  `openAlbum` tinyint(1) NOT NULL DEFAULT '1',
  `whoCreateAlbum` tinyint(1) NOT NULL DEFAULT '1',
  `whoUploadPic` tinyint(1) NOT NULL DEFAULT '0',
  `anno` tinyint(1) NOT NULL DEFAULT '0',
  `ipshow` tinyint(1) NOT NULL DEFAULT '0',
  `invitepriv` tinyint(1) NOT NULL DEFAULT '0',
  `createalbumpriv` tinyint(1) NOT NULL DEFAULT '1',
  `uploadpicpriv` tinyint(1) NOT NULL DEFAULT '0',
  `ctime` int(11) NOT NULL DEFAULT '0',
  `mtime` int(11) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `isrecom` tinyint(1) NOT NULL DEFAULT '0',
  `is_del` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=51 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `ts_group_album`
-- ----------------------------
DROP TABLE IF EXISTS `ts_group_album`;
CREATE TABLE `ts_group_album` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gid` int(11) NOT NULL,
  `userId` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `info` text,
  `cTime` int(11) unsigned DEFAULT NULL,
  `mTime` int(11) unsigned DEFAULT NULL,
  `coverImageId` int(11) NOT NULL,
  `coverImagePath` varchar(255) DEFAULT NULL,
  `photoCount` int(11) DEFAULT '0',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '1',
  `share` tinyint(1) NOT NULL DEFAULT '0',
  `is_del` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `uid` (`userId`),
  KEY `cTime` (`cTime`),
  KEY `mTime` (`mTime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ts_group_album
-- ----------------------------

-- ----------------------------
-- Table structure for `ts_group_atme`
-- ----------------------------
DROP TABLE IF EXISTS `ts_group_atme`;
CREATE TABLE `ts_group_atme` (
  `atme_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键，@我的编号',
  `app` char(15) NOT NULL COMMENT '所属应用',
  `table` char(15) NOT NULL COMMENT '存储应用内容的表名',
  `row_id` int(11) NOT NULL COMMENT '应用含有@的内容的编号',
  `gid` int(11) NOT NULL,
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '被@的用户的编号',
  PRIMARY KEY (`atme_id`),
  KEY `app_2` (`uid`,`table`),
  KEY `app_3` (`table`)
) ENGINE=MyISAM AUTO_INCREMENT=923 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `ts_group_attachment`
-- ----------------------------
DROP TABLE IF EXISTS `ts_group_attachment`;
CREATE TABLE `ts_group_attachment` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `gid` int(11) unsigned NOT NULL,
  `uid` int(11) unsigned NOT NULL,
  `attachId` int(11) unsigned NOT NULL,
  `name` varchar(32) NOT NULL,
  `note` text NOT NULL,
  `filesize` varchar(50) NOT NULL DEFAULT '0',
  `filetype` varchar(10) NOT NULL,
  `fileurl` varchar(255) NOT NULL,
  `totaldowns` mediumint(6) NOT NULL DEFAULT '0',
  `ctime` int(11) NOT NULL,
  `mtime` varchar(11) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `is_del` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `gid` (`gid`),
  KEY `gid_2` (`gid`,`attachId`)
) ENGINE=MyISAM AUTO_INCREMENT=59 DEFAULT CHARSET=utf8;


-- ----------------------------
-- Table structure for `ts_group_category`
-- ----------------------------
DROP TABLE IF EXISTS `ts_group_category`;
CREATE TABLE `ts_group_category` (
  `id` mediumint(5) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '1',
  `pid` mediumint(5) NOT NULL DEFAULT '0',
  `module` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=85 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ts_group_category
-- ----------------------------
INSERT INTO `ts_group_category` VALUES ('1', '明星粉丝', '1', '0', '');
INSERT INTO `ts_group_category` VALUES ('2', '行业交流', '1', '0', '');
INSERT INTO `ts_group_category` VALUES ('3', '兴趣爱好', '1', '0', '');
INSERT INTO `ts_group_category` VALUES ('4', '科教人文', '1', '0', '');
INSERT INTO `ts_group_category` VALUES ('5', '生活时尚', '1', '0', '');
INSERT INTO `ts_group_category` VALUES ('6', '同城会', '1', '0', '');
INSERT INTO `ts_group_category` VALUES ('7', '老友记', '1', '0', '');
INSERT INTO `ts_group_category` VALUES ('8', '房产汽车', '1', '0', '');
INSERT INTO `ts_group_category` VALUES ('10', '内地', '1', '1', '');
INSERT INTO `ts_group_category` VALUES ('11', '日韩', '1', '1', '');
INSERT INTO `ts_group_category` VALUES ('12', '欧美', '1', '1', '');
INSERT INTO `ts_group_category` VALUES ('13', '网络红人', '1', '1', '');
INSERT INTO `ts_group_category` VALUES ('14', '其它', '1', '1', '');
INSERT INTO `ts_group_category` VALUES ('15', 'IT互联网', '1', '2', '');
INSERT INTO `ts_group_category` VALUES ('16', '商业财经', '1', '2', '');
INSERT INTO `ts_group_category` VALUES ('17', '传媒公关', '1', '2', '');
INSERT INTO `ts_group_category` VALUES ('18', '机构&公益', '1', '2', '');
INSERT INTO `ts_group_category` VALUES ('19', '创意联盟', '1', '2', '');
INSERT INTO `ts_group_category` VALUES ('20', '其它行业', '1', '2', '');
INSERT INTO `ts_group_category` VALUES ('21', '第三方应用', '1', '2', '');
INSERT INTO `ts_group_category` VALUES ('22', '囧笑话', '1', '3', '');
INSERT INTO `ts_group_category` VALUES ('23', '动漫', '1', '3', '');
INSERT INTO `ts_group_category` VALUES ('24', '游戏', '1', '3', '');
INSERT INTO `ts_group_category` VALUES ('25', '体育', '1', '3', '');
INSERT INTO `ts_group_category` VALUES ('26', '购物', '1', '3', '');
INSERT INTO `ts_group_category` VALUES ('27', '旅游', '1', '3', '');
INSERT INTO `ts_group_category` VALUES ('28', '摄影', '1', '3', '');
INSERT INTO `ts_group_category` VALUES ('29', '音乐', '1', '3', '');
INSERT INTO `ts_group_category` VALUES ('30', '电影', '1', '3', '');
INSERT INTO `ts_group_category` VALUES ('31', '电视', '1', '3', '');
INSERT INTO `ts_group_category` VALUES ('32', '数码', '1', '3', '');
INSERT INTO `ts_group_category` VALUES ('33', '稀奇古怪', '1', '3', '');
INSERT INTO `ts_group_category` VALUES ('34', '文学阅读', '1', '4', '');
INSERT INTO `ts_group_category` VALUES ('35', '社科文艺', '1', '4', '');
INSERT INTO `ts_group_category` VALUES ('36', '科学技术', '1', '4', '');
INSERT INTO `ts_group_category` VALUES ('37', '教育考试', '1', '4', '');
INSERT INTO `ts_group_category` VALUES ('38', '历史军事', '1', '4', '');
INSERT INTO `ts_group_category` VALUES ('39', '潮流时尚', '1', '5', '');
INSERT INTO `ts_group_category` VALUES ('40', '七八九零', '1', '5', '');
INSERT INTO `ts_group_category` VALUES ('41', '帅哥美女', '1', '5', '');
INSERT INTO `ts_group_category` VALUES ('42', '情感', '1', '5', '');
INSERT INTO `ts_group_category` VALUES ('43', '健康', '1', '5', '');
INSERT INTO `ts_group_category` VALUES ('44', '星座', '1', '5', '');
INSERT INTO `ts_group_category` VALUES ('45', '宠物', '1', '5', '');
INSERT INTO `ts_group_category` VALUES ('46', '美食', '1', '5', '');
INSERT INTO `ts_group_category` VALUES ('47', '休闲', '1', '5', '');
INSERT INTO `ts_group_category` VALUES ('48', '家庭亲子', '1', '5', '');
INSERT INTO `ts_group_category` VALUES ('49', '生活信息', '1', '5', '');
INSERT INTO `ts_group_category` VALUES ('50', '北京', '1', '6', '');
INSERT INTO `ts_group_category` VALUES ('51', '上海', '1', '6', '');
INSERT INTO `ts_group_category` VALUES ('52', '广东', '1', '6', '');
INSERT INTO `ts_group_category` VALUES ('53', '江苏', '1', '6', '');
INSERT INTO `ts_group_category` VALUES ('54', '山东', '1', '6', '');
INSERT INTO `ts_group_category` VALUES ('55', '安徽', '1', '6', '');
INSERT INTO `ts_group_category` VALUES ('56', '浙江', '1', '6', '');
INSERT INTO `ts_group_category` VALUES ('57', '福建', '1', '6', '');
INSERT INTO `ts_group_category` VALUES ('58', '河北', '1', '6', '');
INSERT INTO `ts_group_category` VALUES ('59', '河南', '1', '6', '');
INSERT INTO `ts_group_category` VALUES ('60', '辽宁', '1', '6', '');
INSERT INTO `ts_group_category` VALUES ('61', '湖北', '1', '6', '');
INSERT INTO `ts_group_category` VALUES ('62', '四川', '1', '6', '');
INSERT INTO `ts_group_category` VALUES ('63', '同学', '1', '7', '');
INSERT INTO `ts_group_category` VALUES ('64', '老乡', '1', '7', '');
INSERT INTO `ts_group_category` VALUES ('65', '同事', '1', '7', '');
INSERT INTO `ts_group_category` VALUES ('66', '好友', '1', '7', '');
INSERT INTO `ts_group_category` VALUES ('67', '互粉', '1', '7', '');
INSERT INTO `ts_group_category` VALUES ('68', '小区', '1', '8', '');
INSERT INTO `ts_group_category` VALUES ('69', '房产家居', '1', '8', '');
INSERT INTO `ts_group_category` VALUES ('70', '汽车', '1', '8', '');


-- ----------------------------
-- Table structure for `ts_group_comment`
-- ----------------------------
DROP TABLE IF EXISTS `ts_group_comment`;
CREATE TABLE `ts_group_comment` (
  `comment_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键，评论编号',
  `gid` int(11) DEFAULT NULL,
  `app` char(15) NOT NULL COMMENT '所属应用',
  `table` char(15) NOT NULL COMMENT '被评论的内容所存储的表',
  `row_id` int(11) NOT NULL COMMENT '应用进行评论的内容的编号',
  `app_uid` int(11) NOT NULL DEFAULT '0' COMMENT '应用内进行评论的内容的作者的编号',
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '评论者编号',
  `content` text NOT NULL COMMENT '评论内容',
  `to_comment_id` int(11) NOT NULL DEFAULT '0' COMMENT '被回复的评论的编号',
  `to_uid` int(11) NOT NULL DEFAULT '0' COMMENT '被回复的评论的作者的编号',
  `data` text NOT NULL COMMENT '所评论的内容的相关参数（序列化存储）',
  `ctime` int(11) NOT NULL COMMENT '评论发布的时间',
  `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '标记删除（0：没删除，1：已删除）',
  `client_type` tinyint(2) NOT NULL COMMENT '客户端类型，0：网站；1：手机网页版；2：android；3：iphone',
  `is_audit` tinyint(1) DEFAULT '1' COMMENT '是否已审核 0-未审核 1-已审核',
  `storey` int(11) DEFAULT NULL,
  PRIMARY KEY (`comment_id`),
  KEY `app` (`table`,`is_del`,`row_id`),
  KEY `app_3` (`app_uid`,`to_uid`,`is_del`,`table`),
  KEY `app_2` (`uid`,`is_del`,`table`)
) ENGINE=MyISAM AUTO_INCREMENT=1925 DEFAULT CHARSET=utf8;


-- ----------------------------
-- Table structure for `ts_group_feed`
-- ----------------------------
DROP TABLE IF EXISTS `ts_group_feed`;
CREATE TABLE `ts_group_feed` (
  `feed_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '动态ID',
  `gid` int(11) NOT NULL,
  `uid` int(11) NOT NULL COMMENT '产生动态的用户UID',
  `type` char(50) DEFAULT NULL COMMENT 'feed类型.由发表feed的程序控制',
  `app` char(30) DEFAULT 'public' COMMENT 'feed来源的appname',
  `app_row_table` varchar(50) NOT NULL DEFAULT 'feed' COMMENT '关联资源所在的表',
  `app_row_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联的来源ID（如博客的id）',
  `publish_time` int(11) NOT NULL COMMENT '产生时间戳',
  `is_del` int(2) NOT NULL DEFAULT '0' COMMENT '是否删除 默认为0',
  `from` tinyint(2) NOT NULL DEFAULT '0' COMMENT '客户端类型，0：网站；1：手机网页版；2：android；3：iphone',
  `comment_count` int(10) DEFAULT '0' COMMENT '评论数',
  `repost_count` int(10) DEFAULT '0' COMMENT '分享数',
  `comment_all_count` int(10) DEFAULT '0' COMMENT '全部评论数目',
  `is_repost` int(2) DEFAULT '0' COMMENT '是否转发 0-否  1-是',
  `is_audit` int(2) DEFAULT '1' COMMENT '是否已审核 0-未审核 1-已审核',
  PRIMARY KEY (`feed_id`),
  KEY `is_del` (`is_del`,`publish_time`),
  KEY `uid` (`uid`,`is_del`,`publish_time`)
) ENGINE=MyISAM AUTO_INCREMENT=2844 DEFAULT CHARSET=utf8;



-- ----------------------------
-- Table structure for `ts_group_feed_data`
-- ----------------------------
DROP TABLE IF EXISTS `ts_group_feed_data`;
CREATE TABLE `ts_group_feed_data` (
  `feed_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '关联ts_feed表，feed_id',
  `feed_data` text COMMENT '关联ts_feed表，动态数据，序列化保存',
  `client_ip` char(15) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `feed_content` text,
  `from_data` text CHARACTER SET utf8 COLLATE utf8_bin,
  PRIMARY KEY (`feed_id`),
  KEY `feed_id` (`feed_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- ----------------------------
-- Table structure for `ts_group_invite_verify`
-- ----------------------------
DROP TABLE IF EXISTS `ts_group_invite_verify`;
CREATE TABLE `ts_group_invite_verify` (
  `invite_id` int(11) NOT NULL AUTO_INCREMENT,
  `gid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `is_used` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`invite_id`)
) ENGINE=MyISAM AUTO_INCREMENT=63 DEFAULT CHARSET=utf8;


-- ----------------------------
-- Table structure for `ts_group_log`
-- ----------------------------
DROP TABLE IF EXISTS `ts_group_log`;
CREATE TABLE `ts_group_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `gid` int(11) unsigned NOT NULL,
  `uid` int(11) unsigned NOT NULL,
  `type` varchar(10) NOT NULL,
  `content` text NOT NULL,
  `ctime` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=210 DEFAULT CHARSET=utf8;


-- ----------------------------
-- Table structure for `ts_group_member`
-- ----------------------------
DROP TABLE IF EXISTS `ts_group_member`;
CREATE TABLE `ts_group_member` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `gid` int(11) unsigned NOT NULL,
  `uid` int(11) unsigned NOT NULL DEFAULT '0',
  `name` char(10) NOT NULL,
  `reason` text NOT NULL,
  `status` tinyint(1) DEFAULT '1',
  `level` tinyint(2) unsigned DEFAULT '1',
  `ctime` int(11) NOT NULL DEFAULT '0',
  `mtime` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `gid` (`gid`,`uid`),
  KEY `mid` (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=120 DEFAULT CHARSET=utf8;


-- ----------------------------
-- Table structure for `ts_group_photo`
-- ----------------------------
DROP TABLE IF EXISTS `ts_group_photo`;
CREATE TABLE `ts_group_photo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gid` int(11) NOT NULL,
  `attachId` int(11) NOT NULL,
  `albumId` int(11) NOT NULL,
  `userId` int(11) DEFAULT NULL,
  `status` tinyint(2) unsigned NOT NULL DEFAULT '1',
  `name` varchar(255) NOT NULL,
  `cTime` int(11) unsigned DEFAULT NULL,
  `mTime` int(11) unsigned DEFAULT NULL,
  `info` text,
  `commentCount` int(11) unsigned DEFAULT '0',
  `readCount` int(11) unsigned DEFAULT '0',
  `savepath` varchar(255) NOT NULL,
  `size` int(11) NOT NULL DEFAULT '0',
  `tags` text,
  `order` int(11) NOT NULL,
  `is_del` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `gid` (`gid`,`albumId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ts_group_photo
-- ----------------------------

-- ----------------------------
-- Table structure for `ts_group_post`
-- ----------------------------
DROP TABLE IF EXISTS `ts_group_post`;
CREATE TABLE `ts_group_post` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `gid` int(11) unsigned NOT NULL,
  `uid` int(11) unsigned NOT NULL,
  `tid` int(11) unsigned NOT NULL,
  `content` text NOT NULL,
  `ip` char(16) NOT NULL,
  `istopic` tinyint(1) NOT NULL DEFAULT '0',
  `ctime` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `quote` int(11) unsigned NOT NULL DEFAULT '0',
  `is_del` varchar(1) NOT NULL DEFAULT '0',
  `attach` text,
  PRIMARY KEY (`id`),
  KEY `gid` (`gid`,`tid`)
) ENGINE=MyISAM AUTO_INCREMENT=87 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `ts_group_tag`
-- ----------------------------
DROP TABLE IF EXISTS `ts_group_tag`;
CREATE TABLE `ts_group_tag` (
  `group_tag_id` int(11) NOT NULL AUTO_INCREMENT,
  `gid` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`group_tag_id`)
) ENGINE=MyISAM AUTO_INCREMENT=44 DEFAULT CHARSET=utf8;


-- ----------------------------
-- Table structure for `ts_group_topic`
-- ----------------------------
DROP TABLE IF EXISTS `ts_group_topic`;
CREATE TABLE `ts_group_topic` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `gid` int(11) unsigned NOT NULL,
  `uid` int(11) unsigned NOT NULL,
  `name` varchar(36) NOT NULL,
  `title` varchar(255) NOT NULL,
  `cid` int(11) unsigned NOT NULL,
  `viewcount` smallint(6) unsigned NOT NULL DEFAULT '0',
  `replycount` smallint(6) unsigned NOT NULL DEFAULT '0',
  `dist` tinyint(1) NOT NULL DEFAULT '0',
  `top` tinyint(1) NOT NULL DEFAULT '0',
  `lock` tinyint(1) NOT NULL DEFAULT '0',
  `addtime` int(11) NOT NULL DEFAULT '0',
  `replytime` int(11) NOT NULL DEFAULT '0',
  `mtime` int(11) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `isrecom` tinyint(1) NOT NULL DEFAULT '0',
  `is_del` tinyint(1) NOT NULL DEFAULT '0',
  `attach` text,
  PRIMARY KEY (`id`),
  KEY `gid` (`gid`),
  KEY `gid_2` (`gid`,`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=46 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `ts_group_topic_category`
-- ----------------------------
DROP TABLE IF EXISTS `ts_group_topic_category`;
CREATE TABLE `ts_group_topic_category` (
  `id` mediumint(6) unsigned NOT NULL AUTO_INCREMENT,
  `gid` int(11) unsigned NOT NULL,
  `title` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=utf8;


-- ----------------------------
-- Table structure for `ts_group_topic_collect`
-- ----------------------------
DROP TABLE IF EXISTS `ts_group_topic_collect`;
CREATE TABLE `ts_group_topic_collect` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tid` int(11) unsigned NOT NULL DEFAULT '0',
  `mid` int(11) unsigned NOT NULL DEFAULT '0',
  `addtime` int(11) unsigned NOT NULL DEFAULT '0',
  `is_del` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `tid` (`tid`,`mid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ts_group_topic_collect
-- ----------------------------

-- ----------------------------
-- Table structure for `ts_group_user_count`
-- ----------------------------
DROP TABLE IF EXISTS `ts_group_user_count`;
CREATE TABLE `ts_group_user_count` (
  `uid` int(11) NOT NULL,
  `gid` int(11) DEFAULT '0',
  `atme` int(11) NOT NULL DEFAULT '0',
  `comment` int(11) NOT NULL DEFAULT '0',
  `topic` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `unkey_uid_gid` (`uid`,`gid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


#添加ts_system_data数据
REPLACE INTO `ts_system_data` (`list`,`key`,`value`,`mtime`) VALUES
('group', 'createGroup', 's:1:\"1\";', '2013-04-25 10:00:42'),
('group', 'createAudit', 's:1:\"0\";', '2013-04-25 10:00:42'),
('group', 'createMaxGroup', 's:1:\"5\";', '2013-04-25 10:00:42'),
('group', 'hotTags', 's:14:\"aaa|bbb|测试\";', '2013-04-24 16:39:27'),
('group', 'joinMaxGroup', 's:2:\"10\";', '2013-04-25 10:00:42'),
('group', 'weibo', 's:1:\"1\";', '2013-04-25 10:00:42'),
('group', 'discussion', 's:1:\"1\";', '2013-04-25 10:00:42'),
('group', 'uploadFile', 's:1:\"1\";', '2013-04-25 10:00:42'),
('group', 'simpleFileSize', 's:2:\"10\";', '2013-04-25 10:00:42'),
('group', 'spaceSize', 's:2:\"20\";', '2013-04-25 10:00:42'),
('group', 'uploadFileType', 's:19:\"jpg|gif|png|bmp|txt\";', '2013-04-25 10:00:42'),
('group', 'editSubmit', 's:1:\"1\";', '2013-04-25 10:00:42'),
('group', 'open_invite', 's:1:\"0\";', '2013-04-25 09:59:29'),
('group', 'close_invite', 's:1:\"1\";', '2013-04-25 09:59:29'),
('group', 'openWeibo', 's:1:\"1\";', '2013-04-25 09:59:29'),
('group', 'openBlog', 's:1:\"0\";', '2013-04-25 09:59:29'),
('group', 'openUploadFile', 's:1:\"0\";', '2013-04-25 09:59:29'),
('group', 'whoUploadFile', 's:1:\"3\";', '2013-04-25 09:59:29'),
('group', 'whoDownloadFile', 's:1:\"3\";', '2013-04-25 09:59:29');

#添加ts_lang数据
REPLACE INTO `ts_lang` (`key`,`appname`,`filetype`,`zh-cn`,`en`,`zh-tw`) VALUES ('PUBLIC_APPNAME_GROUP', 'PUBLIC', '0', '群组', 'Group', '群組');
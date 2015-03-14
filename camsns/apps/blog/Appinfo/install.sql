DROP TABLE IF EXISTS `ts_blog`;

CREATE TABLE `ts_blog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `name` varchar(20) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `category` mediumint(5) DEFAULT NULL,
  `category_title` varchar(255) default NULL,
  `cover` varchar(255) DEFAULT NULL,
  `content` longtext,
  `readCount` int(11) NOT NULL DEFAULT '0',
  `commentCount` int(11) NOT NULL DEFAULT '0',
  `recommendCount` int(11) NOT NULL DEFAULT '0',
  `tags` varchar(255) DEFAULT NULL,
  `cTime` int(11) DEFAULT NULL,
  `mTime` int(11) DEFAULT NULL,
  `rTime` int(11) NOT NULL DEFAULT '0',
  `isHot` varchar(1) NOT NULL DEFAULT '0',
  `type` int(1) DEFAULT NULL,
  `status` varchar(1) NOT NULL DEFAULT '1',
  `private` tinyint(1) NOT NULL DEFAULT '0',
  `private_data` varchar(255) DEFAULT NULL,
  `hot` int(11) NOT NULL DEFAULT '0',
  `canableComment` tinyint(1) NOT NULL DEFAULT '1',
  `attach` text,
  PRIMARY KEY (`id`),
  KEY `hot` (`hot`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_blog_category`;

CREATE TABLE `ts_blog_category` (
  `id` mediumint(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `pid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `ts_blog_category` (`name`,`uid`,`pid`) VALUES ('未分类',0,0);

#添加ts_system_data数据
REPLACE INTO `ts_system_data` (`list`,`key`,`value`,`mtime`)
VALUES
    ('blog','allorder','year','2010-12-02 18:18:16'),
    ('blog','savetime','5','2010-11-19 10:52:26'),
    ('blog','smiletype','mini','2010-11-19 10:52:38'),
    ('blog','leadingnum','100','2010-11-19 10:52:56'),
    ('blog','leadingin','1','2010-11-19 10:53:05'),
    ('blog','notifyfriend','1','2010-11-19 10:53:27'),
    ('blog','fileaway','0','2010-12-03 16:26:02'),
    ('blog','fileawaypage','6','2010-12-03 11:03:53'),
    ('blog','all','1','2010-12-02 19:05:40'),
    ('blog','delete','0','2010-12-02 19:05:40'),
    ('blog','suffix','...','2010-11-19 10:54:58'),
    ('blog','titleshort','200','2010-12-03 14:50:57'),
    ('blog','limitpage','20','2010-12-03 13:11:32');
    
# 模版数据
DELETE FROM `ts_template` WHERE `name` = 'blog_create_weibo' OR `name` = 'blog_share_weibo';
INSERT INTO `ts_template` (`name`, `alias`, `title`, `body`, `lang`, `type`, `type2`, `is_cache`, `ctime`) 
VALUES
    ('blog_create_weibo','发表日志','','我发表了一篇日志:【{title}】 {url}','zh','blog','weibo',0,1290417734),
    ('blog_share_weibo','分享日志','','分享@{author} 的日志:【{title}】 {url}','zh','blog','weibo',0,1290595552);

# 积分配置
DELETE FROM `ts_credit_setting` WHERE `type` = 'blog';
INSERT INTO `ts_credit_setting` (`id`,`name`, `alias`, `type`, `info`, `score`, `experience`) 
VALUES
    ('','add_blog','发表日志','blog','{action}{sign}了{score}{typecn}','5','5'),
    ('','delete_blog','删除日志','blog','{action}{sign}了{score}{typecn}','-5','-5');

REPLACE INTO `ts_system_data` (`list`,`key`,`value`,`mtime`) 
VALUES ('blog','version_number','s:5:"36263";','2012-07-12 00:00:00');

DELETE FROM `ts_lang` WHERE `key` = 'PUBLIC_APPNAME_BLOG';
REPLACE INTO `ts_lang` (`key`,`appname`,`filetype`,`zh-cn`,`en`,`zh-tw`) VALUES ('PUBLIC_APPNAME_BLOG', 'PUBLIC', '0', '日志', 'Blog', '日誌');

REPLACE INTO `ts_lang` (`key`,`appname`,`filetype`,`zh-cn`,`en`,`zh-tw`) VALUES ('ERROR_NO_ROLE', 'BLOG', '0', '无权限', 'No permissions', '');
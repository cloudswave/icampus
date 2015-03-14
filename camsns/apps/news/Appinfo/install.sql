CREATE TABLE `ts_news` (
  `news_id` int(11) NOT NULL AUTO_INCREMENT,
  `type_id` int(11) DEFAULT '0' COMMENT '分类ID',
  `uid` int(11) DEFAULT '0' COMMENT '用户ID',
  `news_title` varchar(255) NOT NULL COMMENT '标题',
  `news_content` text NOT NULL COMMENT '内容',
  `image` int(11) DEFAULT '0' COMMENT '缩略图',
  `state` int(2) DEFAULT '1' COMMENT '状态,0:隐藏,1:显示,-1:待审核',
  `is_top` tinyint(1) DEFAULT '0' COMMENT '是否置顶',
  `hits` int(11) DEFAULT '0' COMMENT '点击量',
  `is_del` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `created` int(10) DEFAULT NULL COMMENT '创建时间',
  `updated` int(10) DEFAULT NULL COMMENT '最后更新',
  PRIMARY KEY (`news_id`),
  KEY `state_idx` (`state`),
  KEY `type_idx` (`type_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
CREATE TABLE `ts_news_category` (
  `news_category_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '资讯分类ID',
  `title` varchar(225) NOT NULL COMMENT '频道分类名称',
  `pid` int(11) NOT NULL COMMENT '父分类ID',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序字段',
  `ext` text COMMENT '分类配置相关信息序列化',
  PRIMARY KEY (`news_category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
INSERT INTO `ts_system_config` VALUES ('', 'pageKey', 'news_Admin_setinfo', 'a:6:{s:3:\"key\";a:9:{s:7:\"type_id\";s:7:\"type_id\";s:10:\"news_title\";s:10:\"news_title\";s:5:\"image\";s:5:\"image\";s:12:\"news_content\";s:12:\"news_content\";s:5:\"state\";s:5:\"state\";s:6:\"is_top\";s:6:\"is_top\";s:4:\"hits\";s:4:\"hits\";s:7:\"news_id\";s:7:\"news_id\";s:3:\"uid\";s:3:\"uid\";}s:8:\"key_name\";a:9:{s:7:\"type_id\";s:6:\"分类\";s:10:\"news_title\";s:6:\"标题\";s:5:\"image\";s:9:\"缩略图\";s:12:\"news_content\";s:6:\"正文\";s:5:\"state\";s:6:\"状态\";s:6:\"is_top\";s:12:\"是否置顶\";s:4:\"hits\";s:9:\"浏览量\";s:7:\"news_id\";s:8:\"新闻ID\";s:3:\"uid\";s:8:\"用户ID\";}s:8:\"key_type\";a:9:{s:7:\"type_id\";s:6:\"select\";s:10:\"news_title\";s:4:\"text\";s:5:\"image\";s:5:\"image\";s:12:\"news_content\";s:6:\"editor\";s:5:\"state\";s:6:\"select\";s:6:\"is_top\";s:6:\"select\";s:4:\"hits\";s:4:\"text\";s:7:\"news_id\";s:6:\"hidden\";s:3:\"uid\";s:6:\"hidden\";}s:11:\"key_default\";a:9:{s:7:\"type_id\";s:0:\"\";s:10:\"news_title\";s:0:\"\";s:5:\"image\";s:0:\"\";s:12:\"news_content\";s:0:\"\";s:5:\"state\";s:0:\"\";s:6:\"is_top\";s:0:\"\";s:4:\"hits\";s:0:\"\";s:7:\"news_id\";s:0:\"\";s:3:\"uid\";s:0:\"\";}s:9:\"key_tishi\";a:9:{s:7:\"type_id\";s:0:\"\";s:10:\"news_title\";s:0:\"\";s:5:\"image\";s:0:\"\";s:12:\"news_content\";s:0:\"\";s:5:\"state\";s:0:\"\";s:6:\"is_top\";s:0:\"\";s:4:\"hits\";s:0:\"\";s:7:\"news_id\";s:0:\"\";s:3:\"uid\";s:0:\"\";}s:14:\"key_javascript\";a:9:{s:7:\"type_id\";s:0:\"\";s:10:\"news_title\";s:0:\"\";s:5:\"image\";s:0:\"\";s:12:\"news_content\";s:0:\"\";s:5:\"state\";s:0:\"\";s:6:\"is_top\";s:0:\"\";s:4:\"hits\";s:0:\"\";s:7:\"news_id\";s:0:\"\";s:3:\"uid\";s:0:\"\";}}', '2013-4-18 19:58:01');
INSERT INTO `ts_system_config` VALUES ('', 'pageKey', 'news_Admin_newslist', 'a:4:{s:3:\"key\";a:8:{s:5:\"image\";s:5:\"image\";s:10:\"news_title\";s:10:\"news_title\";s:12:\"news_content\";s:12:\"news_content\";s:5:\"state\";s:5:\"state\";s:6:\"is_top\";s:6:\"is_top\";s:4:\"hits\";s:4:\"hits\";s:4:\"date\";s:4:\"date\";s:8:\"DOACTION\";s:8:\"DOACTION\";}s:8:\"key_name\";a:8:{s:5:\"image\";s:9:\"缩略图\";s:10:\"news_title\";s:6:\"名称\";s:12:\"news_content\";s:6:\"正文\";s:5:\"state\";s:6:\"状态\";s:6:\"is_top\";s:12:\"是否置顶\";s:4:\"hits\";s:9:\"浏览量\";s:4:\"date\";s:6:\"时间\";s:8:\"DOACTION\";s:6:\"操作\";}s:10:\"key_hidden\";a:8:{s:5:\"image\";s:1:\"0\";s:10:\"news_title\";s:1:\"0\";s:12:\"news_content\";s:1:\"0\";s:5:\"state\";s:1:\"0\";s:6:\"is_top\";s:1:\"0\";s:4:\"hits\";s:1:\"0\";s:4:\"date\";s:1:\"0\";s:8:\"DOACTION\";s:1:\"0\";}s:14:\"key_javascript\";a:8:{s:5:\"image\";s:0:\"\";s:10:\"news_title\";s:0:\"\";s:12:\"news_content\";s:0:\"\";s:5:\"state\";s:0:\"\";s:6:\"is_top\";s:0:\"\";s:4:\"hits\";s:0:\"\";s:4:\"date\";s:0:\"\";s:8:\"DOACTION\";s:0:\"\";}}', '2013-4-18 19:56:47');
INSERT INTO `ts_system_config` VALUES ('', 'pageKey', 'news_Admin_index', 'a:6:{s:3:\"key\";a:3:{s:16:\"news_publish_uid\";s:16:\"news_publish_uid\";s:13:\"news_list_num\";s:13:\"news_list_num\";s:14:\"news_show_type\";s:14:\"news_show_type\";}s:8:\"key_name\";a:3:{s:16:\"news_publish_uid\";s:12:\"发布者UID\";s:13:\"news_list_num\";s:18:\"每页显示数量\";s:14:\"news_show_type\";s:12:\"显示方式\";}s:8:\"key_type\";a:3:{s:16:\"news_publish_uid\";s:4:\"text\";s:13:\"news_list_num\";s:4:\"text\";s:14:\"news_show_type\";s:6:\"select\";}s:11:\"key_default\";a:3:{s:16:\"news_publish_uid\";s:0:\"\";s:13:\"news_list_num\";s:0:\"\";s:14:\"news_show_type\";s:0:\"\";}s:9:\"key_tishi\";a:3:{s:16:\"news_publish_uid\";s:82:\"推荐绑定用户UID,标识是其发送，方便前台显示及以后功能扩展\";s:13:\"news_list_num\";s:0:\"\";s:14:\"news_show_type\";s:0:\"\";}s:14:\"key_javascript\";a:3:{s:16:\"news_publish_uid\";s:0:\"\";s:13:\"news_list_num\";s:0:\"\";s:14:\"news_show_type\";s:0:\"\";}}', '2013-4-18 19:55:34');
INSERT INTO `ts_lang` (`key`,`appname`,`filetype`,`zh-cn`,`en`,`zh-tw`) VALUES ('PUBLIC_APPNAME_NEWS','PUBLIC','0','资讯','news','资讯');
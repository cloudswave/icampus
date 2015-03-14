DROP TABLE IF EXISTS cs_user_apps;
CREATE TABLE `cs_user_apps` (
  `user_app_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '键主ID',
  `app_id` int(11) NOT NULL COMMENT '用应ID',
  `uid` int(11) NOT NULL COMMENT '安装者UID',
  `display_order` int(5) NOT NULL DEFAULT '0' COMMENT '装安的应用排序',
  PRIMARY KEY (`user_app_id`),
  KEY `app_id` (`app_id`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
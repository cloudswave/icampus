DROP TABLE IF EXISTS cs_user_password;
CREATE TABLE `cs_user_password` (
  `user_password_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '键主ID',
  `password` varchar(20) NOT NULL COMMENT '密码',
  `username` varchar(20) NOT NULL COMMENT '用户名',
  `cookies` varchar(500) COMMENT 'cookies',
  `uid` int(11) NOT NULL COMMENT '用户ID',
  `system_id` int(5) NOT NULL COMMENT '对应系统的类型，1为图书馆，2教务处',
  PRIMARY KEY (`user_password_id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
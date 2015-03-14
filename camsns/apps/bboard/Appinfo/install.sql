
SET FOREIGN_KEY_CHECKS=0;


CREATE TABLE IF NOT EXISTS `ts_bboard_topic` (
  `topic_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Id',
  `content` text NOT NULL COMMENT '内容',
  `topic_time` int(11) NOT NULL COMMENT '发布时间',
  `topic_uid` int(11) NOT NULL COMMENT '发布用户Id',
  PRIMARY KEY (`topic_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;


INSERT INTO `ts_lang` (`key`, `appname`, `filetype`, `zh-cn`, `en`, `zh-tw`) VALUES
( 'PUBLIC_APPNAME_BBOARD', 'PUBLIC', 0, '黑板报', 'bboard', '黑板报');

INSERT INTO `ts_system_config` ( `list`, `key`, `value`, `mtime`) VALUES
( 'pageKey', 'bboard_Admin_index', 'a:4:{s:3:"key";a:5:{s:8:"topic_id";s:8:"topic_id";s:7:"content";s:7:"content";s:10:"topic_time";s:10:"topic_time";s:9:"topic_uid";s:9:"topic_uid";s:8:"DOACTION";s:8:"DOACTION";}s:8:"key_name";a:5:{s:8:"topic_id";s:2:"ID";s:7:"content";s:6:"内容";s:10:"topic_time";s:12:"发布时间";s:9:"topic_uid";s:9:"发布者";s:8:"DOACTION";s:6:"操作";}s:10:"key_hidden";a:5:{s:8:"topic_id";s:1:"0";s:7:"content";s:1:"0";s:10:"topic_time";s:1:"0";s:9:"topic_uid";s:1:"0";s:8:"DOACTION";s:1:"0";}s:14:"key_javascript";a:5:{s:8:"topic_id";s:0:"";s:7:"content";s:0:"";s:10:"topic_time";s:0:"";s:9:"topic_uid";s:0:"";s:8:"DOACTION";s:0:"";}}', '2013-05-05 07:09:37'),
( 'pageKey', 'bboard_Admin_config', 'a:6:{s:3:"key";a:2:{s:8:"big_logo";s:8:"big_logo";s:3:"tip";s:3:"tip";}s:8:"key_name";a:2:{s:8:"big_logo";s:9:"大图片";s:3:"tip";s:6:"提示";}s:8:"key_type";a:2:{s:8:"big_logo";s:5:"image";s:3:"tip";s:4:"text";}s:11:"key_default";a:2:{s:8:"big_logo";s:0:"";s:3:"tip";s:0:"";}s:9:"key_tishi";a:2:{s:8:"big_logo";s:0:"";s:3:"tip";s:0:"";}s:14:"key_javascript";a:2:{s:8:"big_logo";s:0:"";s:3:"tip";s:0:"";}}', '2013-05-05 07:10:12'),
( 'pageKey', 'bboard_Admin_addtopic', 'a:6:{s:3:"key";a:1:{s:7:"content";s:7:"content";}s:8:"key_name";a:1:{s:7:"content";s:6:"内容";}s:8:"key_type";a:1:{s:7:"content";s:6:"editor";}s:11:"key_default";a:1:{s:7:"content";s:0:"";}s:9:"key_tishi";a:1:{s:7:"content";s:0:"";}s:14:"key_javascript";a:1:{s:7:"content";s:0:"";}}', '2013-05-05 07:10:36'),
( 'pageKey', 'bboard_Admin_editTopic', 'a:6:{s:3:"key";a:4:{s:8:"topic_id";s:8:"topic_id";s:10:"topic_time";s:10:"topic_time";s:9:"topic_uid";s:9:"topic_uid";s:7:"content";s:7:"content";}s:8:"key_name";a:4:{s:8:"topic_id";s:2:"ID";s:10:"topic_time";s:12:"发布时间";s:9:"topic_uid";s:14:"发布用户ID";s:7:"content";s:6:"内容";}s:8:"key_type";a:4:{s:8:"topic_id";s:4:"word";s:10:"topic_time";s:4:"date";s:9:"topic_uid";s:4:"word";s:7:"content";s:6:"editor";}s:11:"key_default";a:4:{s:8:"topic_id";s:0:"";s:10:"topic_time";s:0:"";s:9:"topic_uid";s:0:"";s:7:"content";s:0:"";}s:9:"key_tishi";a:4:{s:8:"topic_id";s:0:"";s:10:"topic_time";s:0:"";s:9:"topic_uid";s:0:"";s:7:"content";s:0:"";}s:14:"key_javascript";a:4:{s:8:"topic_id";s:0:"";s:10:"topic_time";s:0:"";s:9:"topic_uid";s:0:"";s:7:"content";s:0:"";}}', '2013-06-17 04:32:34');

DROP TABLE IF EXISTS `ts_vote`;
CREATE TABLE `ts_vote` (
`id`  int(11) NOT NULL AUTO_INCREMENT ,
`uid`  int(11) NOT NULL COMMENT '作者ID' ,
`title`  text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '标题' ,
`explain`  text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '介绍' ,
`type`  tinyint(4) NOT NULL COMMENT '型类(0单选、1多选)' ,
`glimit`  tinyint(4) NOT NULL DEFAULT 0 COMMENT '最多可以投几项' ,
`deadline`  int(11) NOT NULL COMMENT '截至时间' ,
`onlyfriend`  tinyint(4) NOT NULL COMMENT '是否只有好友可以投票' ,
`cTime`  int(11) NULL DEFAULT NULL COMMENT '创建时间' ,
`isHot`  varchar(1) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '是否热门' ,
`rTime`  int(11) NOT NULL COMMENT '访问时间' ,
`status`  varchar(1) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '是否禁用:0禁用,1激活' ,
`vote_num`  int(11) NOT NULL DEFAULT 0 COMMENT '投票人数' ,
`commentCount`  int(11) NOT NULL DEFAULT 0 COMMENT '投票评论数' ,
`feed_id`  int(11) NOT NULL DEFAULT 0 COMMENT '投票对应的微博ID' ,
PRIMARY KEY (`id`)
)
ENGINE=MyISAM DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci CHECKSUM=0 ROW_FORMAT=Dynamic DELAY_KEY_WRITE=0;
DROP TABLE IF EXISTS `ts_vote_opt`;
CREATE TABLE `ts_vote_opt` (
`id`  int(11) NOT NULL AUTO_INCREMENT ,
`vote_id`  int(11) NOT NULL COMMENT '投票ID' ,
`name`  text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '选项标题' ,
`num`  int(11) NOT NULL DEFAULT 0 COMMENT '选项被投次数' ,
PRIMARY KEY (`id`)
)ENGINE=MyISAM DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci CHECKSUM=0 ROW_FORMAT=Dynamic DELAY_KEY_WRITE=0;
DROP TABLE IF EXISTS `ts_vote_user`;
CREATE TABLE `ts_vote_user` (
`id`  int(11) NOT NULL AUTO_INCREMENT ,
`vote_id`  int(11) NOT NULL COMMENT '投票ID' ,
`uid`  int(11) NOT NULL COMMENT '用户ID' ,
`opts`  text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户所投选项' ,
`cTime`  int(11) NULL DEFAULT NULL COMMENT '创建时间' ,
PRIMARY KEY (`id`)
)ENGINE=MyISAM DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci CHECKSUM=0 ROW_FORMAT=Dynamic DELAY_KEY_WRITE=0;

#添加ts_system_data数据
DELETE FROM `ts_system_data` WHERE `list` = 'vote';
INSERT INTO `ts_system_data` (`list`,`key`,`value`,`mtime`)
VALUES
	('vote', 'limitpage', 's:2:\"20\";', '2010-12-03 13:11:32'),
	('vote', 'defaultTime', 's:7:\"7776000\";', '2010-12-02 18:18:16'),
	('vote', 'join', 's:3:\"all\";', '2010-12-02 18:18:16');

#模板数据
-- DELETE FROM `ts_template` WHERE `name` = 'vote_create_weibo' OR `name` = 'vote_share_weibo';
-- INSERT INTO `ts_template` (`name`, `alias`, `title`, `body`, `lang`, `type`, `type2`, `is_cache`, `ctime`) 
-- VALUES
-- 	('vote_create_weibo', '发起投票', '', '我发起了一个投票:【{title}】 {url}', 'zh', 'vote', 'weibo', 0, 1290417734),
-- 	('vote_share_weibo', '分享投票', '', '分享@{author} 的投票:【{title}】{url}', 'zh', 'vote', 'weibo', 0, 1290595552);

#积分配置
DELETE FROM `ts_credit_setting` WHERE `type` = 'vote';
INSERT INTO `ts_credit_setting` (`id`, `name`, `alias`, `type`, `info`, `score`, `experience`) 
VALUES
	('', 'add_vote', '发起投票', 'vote', '{action}{sign}了{score}{typecn}', '20', '20'),
	('', 'join_vote', '参与投票', 'vote', '{action}{sign}了{score}{typecn}', '1', '5'),
	('', 'joined_vote', '投票被参与', 'vote', '{action}{sign}了{score}{typecn}', '1', '1'),
	('', 'delete_vote', '删除投票', 'vote', '{action}{sign}了{score}{typecn}', '-20', '-20');

INSERT INTO `ts_system_data` (`list`,`key`,`value`,`mtime`) 
VALUES 
    ('vote','version_number','s:5:"36263";','2012-07-12 00:00:00');


DELETE FROM `ts_lang` WHERE `appname` = 'VOTE';
DELETE FROM `ts_lang` WHERE `key` = 'PUBLIC_APPNAME_VOTE';
INSERT INTO `ts_lang` (`key`,`appname`,`filetype`,`zh-cn`,`en`,`zh-tw`) VALUES 
 ('LEAST_TWO_OPTIONS', 'VOTE', '0', '至少填写2个选项', 'Fill in at least 2 options', ''),
 ('CLOCK', 'VOTE', '0', '时', 'hour', ''),
 ('A_YEAR', 'VOTE', '0', '一年', 'One year', ''),
 ('CUSTOM', 'VOTE', '0', '自定义', 'Custom', ''),
 ('A_MONTH', 'VOTE', '0', '一月', 'one month', ''),
 ('HALF_YEAR', 'VOTE', '0', '半年', 'half a year', ''),
 ('A_WEEK', 'VOTE', '0', '一周', 'a week', ''),
 ('CONFIRM', 'VOTE', '0', '确认', 'confirm', ''),
 ('CANCEL', 'VOTE', '0', '取消', 'cancel', ''),
 ('DEAD_LINE', 'VOTE', '0', '截止日期', 'deadline', ''),
 ('OPTIONS_MOST', 'VOTE', '0', '项', 'item', ''),
 ('ADVANCE_SETTING', 'VOTE', '0', '高级设置', 'Advanced settings', ''),
 ('SINGLE', 'VOTE', '0', '单选', 'single selection', ''),
 ('CAN_MULTI_SELECT', 'VOTE', '0', '可多选，最多', 'Multiple choice.most', ''),
 ('VOTE_SETTING', 'VOTE', '0', '投票设置', 'The vote is set', ''),
 ('OPTION', 'VOTE', '0', '候选项', 'The candidate item', ''),
 ('VOTE_TOPIC', 'VOTE', '0', '投票主题', 'Voting theme', ''),
 ('PUBLIC_APPNAME_VOTE', 'PUBLIC', '0', '投票', 'Vote', '');

 DELETE FROM `ts_lang` WHERE `key` = 'PUBLIC_APPNAME_VOTE';
INSERT INTO `ts_lang` (`key`,`appname`,`filetype`,`zh-cn`,`en`,`zh-tw`) VALUES ('PUBLIC_APPNAME_VOTE', 'PUBLIC', '0', '投票', 'Vote', '');
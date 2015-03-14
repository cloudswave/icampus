SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `ts_photo`
-- ----------------------------
DROP TABLE IF EXISTS `ts_photo`;
CREATE TABLE `ts_photo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `attachId` int(11) DEFAULT NULL COMMENT '附件ID',
  `albumId` int(11) DEFAULT NULL COMMENT '相册ID',
  `userId` int(11) DEFAULT NULL COMMENT '用户ID',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '照片状态（已弃用）',
  `name` varchar(255) DEFAULT NULL COMMENT '照片标题',
  `cTime` int(11) unsigned DEFAULT NULL COMMENT '上传时间',
  `mTime` int(11) unsigned DEFAULT NULL COMMENT '修改时间',
  `info` text COMMENT '照片描述',
  `commentCount` int(11) unsigned DEFAULT '0' COMMENT '评论次数',
  `readCount` int(11) unsigned DEFAULT '0' COMMENT '访问次数',
  `savepath` varchar(255) DEFAULT NULL COMMENT '存储路径',
  `size` int(11) NOT NULL DEFAULT '0' COMMENT '大小',
  `privacy` int(1) NOT NULL DEFAULT '1' COMMENT '隐私（已弃用）',
  `tags` text COMMENT '标签',
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序ID',
  `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除，0：否；1：是',
  `feed_id` int(11) NOT NULL DEFAULT '0' COMMENT '微博ID',
  PRIMARY KEY (`id`),
  KEY `albumId_order` (`albumId`,`order`,`id`) 
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;


-- ----------------------------
-- Table structure for `ts_photo_album`
-- ----------------------------
DROP TABLE IF EXISTS `ts_photo_album`;
CREATE TABLE `ts_photo_album` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) DEFAULT NULL COMMENT '用户ID',
  `name` varchar(255) DEFAULT NULL COMMENT '相册名',
  `info` text COMMENT '相册描述',
  `cTime` int(11) unsigned DEFAULT NULL COMMENT '创建时间',
  `mTime` int(11) unsigned DEFAULT NULL COMMENT '更新时间',
  `coverImageId` int(11) DEFAULT NULL COMMENT '封面图片ID',
  `coverImagePath` varchar(255) DEFAULT NULL COMMENT '封面图片路径',
  `photoCount` int(11) DEFAULT '0' COMMENT '照片数量',
  `readCount` int(11) DEFAULT '0' COMMENT '访问数量',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '相册状态（已弃用）',
  `isHot` varchar(1) NOT NULL DEFAULT '0' COMMENT '是否热门推荐',
  `rTime` int(11) NOT NULL DEFAULT '0' COMMENT '最后访问时间（已弃用）',
  `share` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否允许分享，0：不允许，1：允许。（已弃用）',
  `privacy` tinyint(1) DEFAULT NULL COMMENT '隐私设置（已弃用）',
  `privacy_data` text COMMENT '隐私数据（如需要密码访问时）（已弃用）',
  `isDel` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除，0：否；1：是',
  PRIMARY KEY (`id`),
  KEY `uid` (`userId`),
  KEY `cTime` (`cTime`),
  KEY `mTime` (`mTime`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;


-- ----------------------------
-- Table structure for `ts_photo_index`
-- ----------------------------
DROP TABLE IF EXISTS `ts_photo_index`;
CREATE TABLE `ts_photo_index` (
  `albumId` int(11) NOT NULL DEFAULT '0' COMMENT '相册ID',
  `photoId` int(11) NOT NULL DEFAULT '0' COMMENT '照片ID',
  `userId` int(11) DEFAULT NULL COMMENT '用户ID',
  `order` int(11) DEFAULT NULL COMMENT '排序',
  `privacy` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否私有，0：否；1：是',
  PRIMARY KEY (`albumId`,`photoId`),
  UNIQUE KEY `album_photo` (`albumId`,`photoId`) 
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

-- ----------------------------
-- Table structure for `ts_photo_mark`
-- ----------------------------
DROP TABLE IF EXISTS `ts_photo_mark`;
CREATE TABLE `ts_photo_mark` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `photoId` int(11) DEFAULT NULL COMMENT '照片ID',
  `userId` int(11) DEFAULT NULL COMMENT '用户ID',
  `userName` varchar(50) DEFAULT NULL COMMENT '用户名',
  `markedUserId` int(11) DEFAULT NULL COMMENT '马甲ID',
  `x` varchar(100) DEFAULT NULL COMMENT '照片的宽度',
  `y` varchar(100) NOT NULL COMMENT '照片的长度',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

#添加ts_system_data数据
REPLACE INTO `ts_system_data` (`list`,`key`,`value`,`mtime`) VALUES
 ('photo', 'max_flash_upload_num', 's:2:\"10\";', '2010-11-19 10:53:27'),
 ('photo', 'photo_raws', 's:1:\"8\";', '2010-11-19 10:52:26'),
 ('photo', 'photo_preview', 's:1:\"1\";', '2010-11-19 10:52:38'),
 ('photo', 'photo_max_size', 's:1:\"2\";', '2010-11-19 10:52:56'),
 ('photo', 'photo_file_ext', 's:16:\"jpeg,gif,jpg,png\";', '2010-11-19 10:53:05'),
 ('photo', 'album_raws', 's:1:\"6\";', '2010-12-02 18:18:16'),
 ('photo', 'version_number', 's:5:\"36263\";', '2013-04-23 00:00:00');

#添加ts_lang数据
REPLACE INTO `ts_lang` (`key`,`appname`,`filetype`,`zh-cn`,`en`,`zh-tw`) VALUES ('PUBLIC_APPNAME_PHOTO', 'PUBLIC', '0', '相册', 'Photo', '相冊');
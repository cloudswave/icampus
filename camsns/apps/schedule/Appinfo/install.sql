DROP TABLE IF EXISTS `ts_app_schedule`;
CREATE TABLE IF NOT EXISTS `ts_app_schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `date` text NOT NULL,
  `area1` int(11) NOT NULL,
  `area2` int(11) NOT NULL,
  `area4` text NOT NULL,
  `event` text NOT NULL,
  `ctime` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

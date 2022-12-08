CREATE TABLE `APP_live_activity` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '直播活動編號',
  `course_id` int(10) unsigned NOT NULL COMMENT '課程編號',
  `name` varchar(255) DEFAULT NULL COMMENT '直播名稱',
  `url` varchar(255) DEFAULT NULL COMMENT '直播網址',
  `status` enum('on','off') DEFAULT 'on' COMMENT '直播狀態(on:直播中|off:已結束)',
  `begin_time` datetime NOT NULL COMMENT '開始時間',
  `end_time` datetime NOT NULL COMMENT '結束時間',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='直播活動' AUTO_INCREMENT=1 ;
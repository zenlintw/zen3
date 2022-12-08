CREATE TABLE `APP_rollcall_base` (
  `rid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '點名編號 (流水號)',
  `course_id` int(10) unsigned NOT NULL COMMENT '課程編號',
  `creator` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '建立者帳號 (老師帳號)',
  `mode` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '點名方式 1: Nearby | 2: Beacon',
  `device_status` varchar(128) NOT NULL DEFAULT '' COMMENT '裝置狀態',
  `create_time` datetime NOT NULL COMMENT '建立時間',
  `begin_time` datetime NOT NULL COMMENT '該次點名啟用時間',
  `end_time` datetime NOT NULL COMMENT '該次點名結束時間',
  PRIMARY KEY (`rid`),
  KEY `course_id` (`course_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='點名資料基本檔';
CREATE TABLE `APP_note_share` (
  `share_key` varchar(32) NOT NULL COMMENT '筆記分享Key',
  `folder_id` varchar(32) NOT NULL,
  `msg_serial` bigint(20) NOT NULL,
  `owner` varchar(32) NOT NULL COMMENT '分享者',
  `due_time` int(10) NOT NULL COMMENT '分享結束時間',
  PRIMARY KEY (`share_key`,`msg_serial`,`owner`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

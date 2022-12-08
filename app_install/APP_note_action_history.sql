CREATE TABLE `APP_note_action_history` (
  `username` varchar(32) NOT NULL COMMENT '帳號',
  `log_time` int(10) NOT NULL COMMENT '動作時間',
  `action` set('A','M','D') NOT NULL COMMENT 'A新增,M修改,D刪除',
  `folder_id` varchar(32) NOT NULL COMMENT '筆記本ID',
  `msg_serial` bigint(20) NOT NULL COMMENT '筆記ID',
  `from` set('server','client') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
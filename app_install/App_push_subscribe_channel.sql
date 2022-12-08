CREATE TABLE `APP_push_subscribe_channel` (
  `app_uuid` char(36) NOT NULL COMMENT 'APP UUID',
  `devicetoken` varchar(255) NOT NULL COMMENT '裝置TOKEN',
  `channel` varchar(128) NOT NULL COMMENT '訂閱channel',
  `builder` varchar(32) NOT NULL DEFAULT 'SYSTEM' COMMENT '新增者',
  `create_datetime` datetime NOT NULL COMMENT '新增日時',
  `update_datetime` datetime NOT NULL COMMENT '更新日時',
  PRIMARY KEY (`app_uuid`,`devicetoken`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Message Queue 訂閱頻道';
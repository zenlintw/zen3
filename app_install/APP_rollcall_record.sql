CREATE TABLE `APP_rollcall_record` (
  `rid` int(11) NOT NULL COMMENT 'from APP_rollcall_base.rid',
  `username` varchar(32) NOT NULL COMMENT '學生帳號',
  `rollcall_time` datetime NOT NULL COMMENT '點名時間',
  `rollcall_status` int(11) NOT NULL COMMENT '點名狀態(1:已點名|2:未點名|3:遲到|4:早退|5:病假|6:公假|7:事假|8:喪假|9:裝置重複)',
  `device_ident` varchar(255) NOT NULL COMMENT '裝置識別碼',
  `device_status` varchar(128) NOT NULL DEFAULT '' COMMENT '裝置狀態',
  `memo` text NOT NULL COMMENT '備註',
  `modifier` varchar(32) NOT NULL COMMENT '修改點名狀態的老師帳號',
  `modify_time` datetime NOT NULL COMMENT '修改時間',
  PRIMARY KEY (`rid`,`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='學生的點名狀態';
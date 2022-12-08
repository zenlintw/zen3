CREATE TABLE `APP_notification_device` (
  `username` varchar(32) NOT NULL COMMENT 'channel帳號',
  `token_type` smallint(1) NOT NULL DEFAULT '0' COMMENT '1.APNS 2.GCM 3.JPUSH 4.FCM',
  `device_token_abandon` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'token是否已失效',
  `device_token` varchar(255) NOT NULL COMMENT '裝置推播TOKEN',
  `device_os` enum('IOS','ANDROID') DEFAULT 'IOS' COMMENT '裝置作業系統類型',
  `device_uuid` char(40) NOT NULL COMMENT '裝置UID',
  `device_name` varchar(255) NOT NULL COMMENT '裝置名稱',
  `device_model` varchar(100) NOT NULL COMMENT '裝置型號',
  `device_version` varchar(25) NOT NULL COMMENT '裝置版本',
  `device_user_agent` varchar(255) NOT NULL COMMENT '裝置User Agent',
  `app_uuid` char(36) NOT NULL COMMENT 'APP UUID',
  `app_name` varchar(255) NOT NULL COMMENT 'APP名稱',
  `app_version` varchar(25) DEFAULT NULL COMMENT 'APP版本',
  `app_type` enum('PRODUCTION','SANDBOX') NOT NULL DEFAULT 'PRODUCTION' COMMENT 'APP類別',
  `create_date_time` datetime NOT NULL COMMENT '新增日時',
  `update_date_time` datetime NOT NULL COMMENT '更新日時',
  PRIMARY KEY (`username`,`token_type`,`device_uuid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='裝置與推播資訊';
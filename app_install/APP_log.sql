CREATE TABLE `APP_log` (
  `idx` varchar(32) NOT NULL COMMENT 'session的idx',
  `username` varchar(32) NOT NULL COMMENT '帳號',
  `course_id` int(10) DEFAULT NULL COMMENT '課程編號',
  `instance_type` varchar(15) DEFAULT NULL COMMENT '素材型態',
  `instance` varchar(50) DEFAULT NULL COMMENT '素材編號',
  `log_time` datetime NOT NULL COMMENT '動作時間',
  `action` varchar(20) NOT NULL COMMENT '此次動作',
  `comment` varchar(255) DEFAULT NULL COMMENT '備註說明',
  `telecom` varchar(20) DEFAULT NULL COMMENT '電信商',
  `network_type` varchar(20) NOT NULL COMMENT '網路型態',
  `device_type` set('ios phone','ios pad','android phone','android pad') NOT NULL COMMENT '用戶User Agent',
  `device_brand` varchar(30) NOT NULL COMMENT '行動裝置廠牌與型號',
  `wifi_ssid` varchar(50) NOT NULL COMMENT '行動裝置所連線WIFI的SSID',
  `user_ip` varchar(39) NOT NULL COMMENT 'user ip(也可供IPv6使用)',
  PRIMARY KEY (`idx`,`username`,`log_time`,`action`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='紀錄APP操作行為';

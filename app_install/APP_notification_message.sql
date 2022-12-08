CREATE TABLE `APP_notification_message` (
  `sender` varchar(32) NOT NULL COMMENT '發送者帳號',
  `receiver` varchar(32) NOT NULL COMMENT '接收者帳號',
  `receiver_token` varchar(255) NOT NULL COMMENT '裝置推播TOKEN',
  `message_type` set('NEWS','EXAM','HOMEWORK','QUESTIONNAIRE','BULLETIN','FORUM','GRADE','COURSE') NOT NULL COMMENT '訊息種類',
  `message_id` varchar(50) NOT NULL COMMENT '推播來源(根據推播類型判定)',
  `title` varchar(255) NOT NULL COMMENT '訊息標題',
  `content` text NOT NULL COMMENT '訊息內容',
  `google_message` varchar(40) NOT NULL COMMENT 'GOOGLE回傳的MESSAGE ID/ERROR',
  `send_time` datetime NOT NULL COMMENT 'GOOGLE回傳的發送時間',
  `user_read_time` datetime DEFAULT NULL COMMENT '使用者已讀時間',
  PRIMARY KEY (`receiver`,`google_message`),
  KEY `hadRead` (`receiver`,`user_read_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='推播訊息';
use `WM_10001`;

CREATE TABLE `CO_meeting_list` (
  `course_id` int(10) NOT NULL COMMENT '課程編號',
  `confid` varchar(32) NOT NULL COMMENT '會議ID',
  `topic` varchar(255) NOT NULL COMMENT '會議名稱',
  `start_date` datetime NOT NULL COMMENT '會議開始時間',
  `end_date` datetime NOT NULL COMMENT '結束時間',
  `add_time` datetime NOT NULL COMMENT '建立會議時間',
  `creator` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '建立者',
  `url` varchar(255) NOT NULL COMMENT '會議URL',
  PRIMARY KEY (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='寶訊通會議列表';

CREATE TABLE `CO_meeting_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(32) NOT NULL,
  `password` varchar(32) NOT NULL DEFAULT '0000' COMMENT '寶訊通個人密碼',
  `add_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='寶訊通user';
CREATE TABLE `APP_term_path` (
  `course_id` int(10) NOT NULL,
  `update_time` datetime NOT NULL,
  `json_path` mediumtext NOT NULL,
  PRIMARY KEY (`course_id`),
  KEY `course_id` (`course_id`,`update_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
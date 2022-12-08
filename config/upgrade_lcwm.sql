use WM52_ARMY_10001;

CREATE TABLE `lcms_course_wizard` (
  `source_course_id` int(10) NOT NULL,
  `target_course_id` int(10) NOT NULL,
  `lcms_course_id` int(10) NOT NULL,
  `trans_course_path` longtext NOT NULL,
  `before_course_path` longtext NOT NULL,
  `creater` varchar(32) NOT NULL,
  `editor` varchar(32) NOT NULL,
  `post_data` text NOT NULL,
  `add_time` datetime NOT NULL,
  `upd_time` datetime NOT NULL,
  PRIMARY KEY (`target_course_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
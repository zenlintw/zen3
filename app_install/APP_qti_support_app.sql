CREATE TABLE `APP_qti_support_app` (
  `exam_id` int(10) NOT NULL,
  `type` set('exam','questionnaire','homework') NOT NULL,
  `course_id` int(10) NOT NULL DEFAULT '0',
  `support` enum('N','Y') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`exam_id`,`course_id`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

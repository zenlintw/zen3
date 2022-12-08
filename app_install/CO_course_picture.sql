CREATE TABLE IF NOT EXISTS `CO_course_picture` (
`course_id` int(10) unsigned NOT NULL,
`picture` mediumblob,
`mime_type` varchar(20) NOT NULL,
 PRIMARY KEY (`course_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
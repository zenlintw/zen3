CREATE TABLE `WM_save_temporary` (
  `function_id` CHAR(64) BINARY NOT NULL ,
  `username` varchar(32) binary NOT NULL ,
  `save_time` datetime NOT NULL ,
  `content` MEDIUMTEXT NOT NULL ,
  PRIMARY KEY (`function_id`, `username`)
) TYPE=MyISAM;



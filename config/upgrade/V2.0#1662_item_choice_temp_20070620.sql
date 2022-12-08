--
-- 資料表格式： `WM_qti_choice_template`
--

CREATE TABLE `WM_qti_choice_template` (
  `owner_id` int(10) unsigned NOT NULL,
  `create_time` datetime NOT NULL,
  `creator` varchar(32) character set utf8 collate utf8_bin NOT NULL,
  `title` varchar(255) NOT NULL,
  `type` tinyint(3) unsigned NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY  (`owner_id`,`create_time`),
  KEY `idx1` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

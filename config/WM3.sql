/*!40100 SET NAMES 'utf8' */;
/*!40100 SET CHARACTER SET utf8 */;

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `WM52_ARMY_MASTER` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `WM52_ARMY_MASTER`;


CREATE TABLE `WM_all_account` (
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `password` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `enable` enum('N','Y') NOT NULL default 'N',
  `first_name` varchar(32) character set utf8 collate utf8_bin default '',
  `last_name` varchar(32) character set utf8 collate utf8_bin default '',
  `gender` enum('F','M') NOT NULL default 'F',
  `birthday` date default NULL,
  `personal_id` varchar(32) default NULL,
  `email` varchar(64) NOT NULL default '',
  `homepage` varchar(64) default NULL,
  `home_tel` varchar(32) default NULL,
  `home_fax` varchar(32) default NULL,
  `home_address` varchar(255) default NULL,
  `office_tel` varchar(32) default NULL,
  `office_fax` varchar(32) default NULL,
  `office_address` varchar(255) default NULL,
  `cell_phone` varchar(32) default NULL,
  `company` varchar(255) default NULL,
  `department` varchar(64) default NULL,
  `title` varchar(32) default NULL,
  `language` varchar(32) default NULL,
  `theme` varchar(32) default 'default',
  `msg_reserved` tinyint(1) NOT NULL default '0',
  `hid` int(10) unsigned NOT NULL default '262075',
  PRIMARY KEY  (`username`),
  UNIQUE KEY `idx1` (`username`,`password`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_auth_ftp` (
  `userid` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `passwd` varchar(32) NOT NULL default '',
  `home` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`userid`,`home`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;


CREATE TABLE `WM_manager` (
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `school_id` int(10) unsigned NOT NULL default '0',
  `level` int(10) unsigned NOT NULL default '2048',
  `allow_ip` varchar(255) default NULL,
  PRIMARY KEY  (`username`,`school_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_prelogin` (
  `login_seed` varchar(32) NOT NULL default '',
  `uid` varchar(32) NOT NULL default '',
  `log_time` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`login_seed`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;


CREATE TABLE `WM_sch4user` (
  `school_id` int(10) unsigned NOT NULL default '0',
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `login_times` int(10) unsigned default '0',
  `last_login` datetime default NULL,
  `last_ip` varchar(16) character set utf8 collate utf8_bin default NULL,
  `reg_time` datetime default NULL,
  `begin_time` date default NULL,
  `expire_time` date default NULL,
  `total_time` int(10) unsigned default '0',
  `points` int(10) unsigned default '0',
  PRIMARY KEY  (`school_id`,`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_school` (
  `school_id` int(10) unsigned NOT NULL default '0',
  `school_host` varchar(64) NOT NULL default '',
  `school_name` varchar(64) NOT NULL default '',
  `feedback` int(10) unsigned default NULL,
  `language` varchar(32) default 'Big5',
  `theme` varchar(32) default 'default',
  `guest` enum('N','Y') default 'N',
  `multi_login` enum('N','Y') default 'Y',
  `canReg` enum('N','Y','C') default 'Y',
  `instructRequire` enum('noncheck','check','admonly') NOT NULL default 'check',
  `guestLimit` int(10) unsigned NOT NULL default '20',
  `courseQuota` int(10) unsigned NOT NULL default '102400',
  `quota_limit` int(10) unsigned NOT NULL default '102400',
  `quota_used` int(10) unsigned NOT NULL default '0',
  `school_mail` varchar(64) default NULL,
  `counter` double unsigned NOT NULL DEFAULT '0' COMMENT '網站累計人數',
  PRIMARY KEY  (`school_id`,`school_host`),
  UNIQUE KEY `school_host` (`school_host`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_status_apache` (
  `log_time` datetime NOT NULL,
  `amount` int(10) unsigned NOT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8;


CREATE TABLE `WM_status_cpu` (
  `log_time` datetime NOT NULL,
  `idle` float unsigned NOT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8;


CREATE TABLE `WM_status_http` (
  `log_time` datetime NOT NULL,
  `amount` int(10) unsigned NOT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8;


CREATE TABLE `WM_status_mem` (
  `log_time` datetime NOT NULL,
  `total` int(10) unsigned NOT NULL,
  `used` int(10) unsigned NOT NULL,
  `free` int(10) unsigned NOT NULL,
  `shared` int(10) unsigned NOT NULL,
  `buffers` int(10) unsigned NOT NULL,
  `cached` int(10) unsigned NOT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8;


CREATE TABLE `WM_status_mysql` (
  `log_time` datetime NOT NULL,
  `amount` int(10) unsigned NOT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8;


CREATE TABLE `WM_status_swap` (
  `log_time` datetime NOT NULL,
  `total` int(10) unsigned NOT NULL,
  `used` int(10) unsigned NOT NULL,
  `free` int(10) unsigned NOT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8;
/*!40000 ALTER TABLE `WM_all_account` DISABLE KEYS */;
INSERT INTO `WM_all_account` (`username`, `password`, `enable`, `first_name`, `last_name`, `gender`, `birthday`, `personal_id`, `email`, `homepage`, `home_tel`, `home_fax`, `home_address`, `office_tel`, `office_fax`, `office_address`, `cell_phone`, `company`, `department`, `title`, `language`, `theme`, `msg_reserved`, `hid`) VALUES 
    ('root','0353dd078b9eff95734218d252698ec9','Y','管理員','系統','M','1970-01-01','','root@localhost','','07-1234123','','','','','','','','','','Big5','default',0,130968);
/*!40000 ALTER TABLE `WM_all_account` ENABLE KEYS */;
/*!40000 ALTER TABLE `WM_manager` DISABLE KEYS */;
INSERT INTO `WM_manager` (`username`, `school_id`, `level`, `allow_ip`) VALUES 
    ('root',10001,8192,'192.168.11 220.133.229.253');
/*!40000 ALTER TABLE `WM_manager` ENABLE KEYS */;
/*!40000 ALTER TABLE `WM_sch4user` DISABLE KEYS */;
INSERT INTO `WM_sch4user` (`school_id`, `username`, `login_times`, `last_login`, `last_ip`, `reg_time`, `begin_time`, `expire_time`, `total_time`, `points`) VALUES 
    (10001,'root',0,NULL,'',NOW(),NULL,NULL,0,0);
/*!40000 ALTER TABLE `WM_sch4user` ENABLE KEYS */;
INSERT INTO WM_school (school_id, school_host, school_name, feedback, language, theme, guest, multi_login, canReg, instructRequire, guestLimit, courseQuota, quota_limit, quota_used, school_mail) VALUES (10001,'wm3.learn.com.tw','Wisdom Master Pro v5.0',NULL,'Big5','default','N','Y','Y','noncheck',1,204800,204800,70580,'webmaster@wm3.learn.com.tw');


CREATE DATABASE /*!32312 IF NOT EXISTS*/ `WM52_ARMY_10001` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `WM52_ARMY_10001`;


CREATE TABLE `WM_acl_bindfile` (
  `function_id` int(10) unsigned NOT NULL default '0',
  `binding_file` varchar(128) character set utf8 collate utf8_bin NOT NULL default '',
  PRIMARY KEY  (`binding_file`),
  UNIQUE KEY `idx1` (`function_id`,`binding_file`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_acl_function` (
  `function_id` bigint(20) unsigned NOT NULL default '0',
  `caption` varchar(255) character set big5 NOT NULL,
  `scope` set('learn','teach','direct','academic') NOT NULL default 'learn',
  `default_permission` set('enable','visible','readable','writable','modifiable','uploadable','removable','manageable','assignable') NOT NULL default 'enable,visible,readable,writable,modifiable,uploadable,removable',
  PRIMARY KEY  (`function_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_acl_list` (
  `acl_id` int(10) unsigned NOT NULL auto_increment,
  `permission` set('enable','visible','readable','writable','modifiable','uploadable','removable','manageable','assignable') NOT NULL default 'enable,visible,readable,writable,modifiable,uploadable,removable',
  `target` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `caption` text NOT NULL,
  `function_id` int(10) unsigned NOT NULL default '0',
  `unit_id` int(10) unsigned NOT NULL default '0',
  `instance` int(10) unsigned NOT NULL default '0',
  `priority` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`acl_id`),
  UNIQUE KEY `idx2` (`acl_id`,`permission`,`target`),
  KEY `idx` (`function_id`,`unit_id`,`instance`,`priority`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_acl_member` (
  `acl_id` int(10) unsigned NOT NULL auto_increment,
  `member` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  PRIMARY KEY  (`acl_id`,`member`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_auth_samba` (
  `logon_time` int(10) unsigned default NULL,
  `logoff_time` int(10) unsigned default NULL,
  `kickoff_time` int(10) unsigned default NULL,
  `pass_last_set_time` int(10) unsigned default NULL,
  `pass_can_change_time` int(10) unsigned default NULL,
  `pass_must_change_time` int(10) unsigned default NULL,
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `domain` varchar(64) default NULL,
  `nt_username` char(1) default NULL,
  `nt_fullname` char(1) default NULL,
  `home_dir` varchar(255) default NULL,
  `dir_drive` char(1) default NULL,
  `logon_script` char(1) default NULL,
  `profile_path` char(1) default NULL,
  `acct_desc` char(1) default NULL,
  `workstations` char(1) default NULL,
  `unknown_str` char(1) default NULL,
  `munged_dial` char(1) default NULL,
  `user_sid` varchar(255) default NULL,
  `group_sid` varchar(255) default NULL,
  `lm_pw` char(1) default NULL,
  `nt_pw` char(1) default NULL,
  `plaintext_pw` varchar(64) character set utf8 collate utf8_bin NOT NULL default '',
  `acct_ctrl` tinyint(3) unsigned default NULL,
  `unknown_3` tinyint(3) unsigned default NULL,
  `logon_divs` tinyint(3) unsigned default NULL,
  `hours_len` tinyint(3) unsigned default NULL,
  `bad_password_count` tinyint(3) unsigned default NULL,
  `logon_count` tinyint(3) unsigned default NULL,
  `unknown_6` tinyint(3) unsigned default NULL,
  PRIMARY KEY  (`username`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;


CREATE TABLE `WM_bbs_boards` (
  `board_id` bigint(20) unsigned NOT NULL auto_increment,
  `bname` text NOT NULL,
  `manager` varchar(32) character set utf8 collate utf8_bin default NULL,
  `title` varchar(255) default NULL,
  `owner_id` bigint(20) unsigned NOT NULL default '0',
  `open_time` datetime default NULL,
  `close_time` datetime default NULL,
  `share_time` datetime default NULL,
  `switch` set('mailfollow','multimedia') default NULL,
  `with_attach` enum('no','yes') default 'yes',
  `vpost` tinyint(1) NOT NULL default '0',
  `default_order` varchar(16) NOT NULL default 'pt',
  `post_times` int(10) unsigned NOT NULL default '0',
  `extras` varchar(255) default '',
  PRIMARY KEY  (`board_id`),
  KEY `idx1` (`owner_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_bbs_collecting` (
  `board_id` bigint(20) unsigned NOT NULL default '0',
  `node` varchar(64) NOT NULL default '',
  `site` int(10) unsigned NOT NULL default '0',
  `pt` datetime default NULL,
  `poster` varchar(32) NOT NULL default '',
  `realname` varchar(32) NOT NULL default '',
  `email` varchar(64) default NULL,
  `homepage` varchar(64) default NULL,
  `subject` varchar(255) NOT NULL default '',
  `content` text,
  `attach` text,
  `rcount` int(10) unsigned default NULL,
  `rank` float default NULL,
  `hit` int(10) unsigned default '0',
  `lang` tinyint(3) unsigned NOT NULL default '1',
  `ctime` datetime default NULL,
  `picker` varchar(32) NOT NULL default '',
  `path` varchar(255) character set utf8 collate utf8_bin NOT NULL default '',
  `type` enum('D','F') NOT NULL default 'F',
  `post_node` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`board_id`,`node`,`site`,`path`),
  KEY `idx1` (`board_id`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_bbs_order` (
  `board_id` bigint(20) unsigned NOT NULL default '0',
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  PRIMARY KEY  (`board_id`,`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_bbs_posts` (
  `board_id` bigint(20) unsigned NOT NULL default '0',
  `node` varchar(19) NOT NULL default '',
  `site` int(10) unsigned NOT NULL default '0',
  `pt` datetime default NULL,
  `poster` varchar(32) NOT NULL default '',
  `realname` varchar(32) NOT NULL default '',
  `email` varchar(64) default NULL,
  `homepage` varchar(64) default NULL,
  `subject` varchar(255) NOT NULL default '',
  `content` text,
  `attach` text,
  `rcount` int(10) unsigned default NULL,
  `rank` float default NULL,
  `hit` int(10) unsigned default '0',
  `lang` tinyint(3) unsigned NOT NULL default '1',
  PRIMARY KEY  (`board_id`,`node`,`site`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_bbs_ranking` (
  `type` enum('b','q') NOT NULL default 'b',
  `board_id` bigint(20) unsigned NOT NULL default '0',
  `node` varchar(80) character set utf8 collate utf8_bin NOT NULL default '',
  `site` int(10) unsigned NOT NULL default '0',
  `username` varchar(20) character set utf8 collate utf8_bin NOT NULL default '',
  `score` tinyint(3) unsigned default '0',
  `r_time` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`type`,`board_id`,`node`,`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_bbs_readed` (
  `type` enum('b','q') NOT NULL default 'b',
  `board_id` bigint(20) unsigned NOT NULL default '0',
  `node` varchar(19) NOT NULL default '',
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `read_time` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`type`,`board_id`,`node`,`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_blog_article` (
  `idx` int(10) unsigned NOT NULL auto_increment,
  `parent_id` int(10) unsigned NOT NULL default '0',
  `author` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `post_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `ip_addr` varchar(30) NOT NULL default '',
  `bname` varchar(32) character set utf8 collate utf8_bin NOT NULL default '0',
  `subject` varchar(50) NOT NULL default '',
  `content` text,
  PRIMARY KEY  (`idx`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_blog_boards` (
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `board_id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`board_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_cal_setting` (
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `also_show` set('course','class','school') default NULL,
  `login_alert` enum('Y','N') default 'Y',
  `alert_num` int(10) unsigned default NULL,
  `alert_date` date default NULL,
  PRIMARY KEY  (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_calendar` (
  `idx` int(10) unsigned NOT NULL auto_increment,
  `parent_idx` int(10) unsigned NOT NULL default '0',
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `type` enum('person','course','class','school') NOT NULL default 'person',
  `memo_date` date NOT NULL default '0000-00-00',
  `time_begin` time default NULL,
  `time_end` time default NULL,
  `repeat` enum('none','day','week','month') NOT NULL default 'none',
  `repeat_freq` tinyint(3) unsigned NOT NULL default '0',
  `repeat_end` date default '0000-00-00',
  `alert_type` set('none','login','email') NOT NULL default 'none',
  `alert_before` tinyint(3) unsigned default '0',
  `ishtml` enum('text','html') NOT NULL default 'text',
  `subject` varchar(255) character set utf8 collate utf8_bin default NULL,
  `content` text,
  `upd_time` datetime default NULL,
  PRIMARY KEY  (`idx`),
  KEY `parent_idx` (`parent_idx`),
  KEY `username` (`username`),
  KEY `idx1` (`type`),
  KEY `idx2` (`memo_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_calendar_exam` (
  `calendar_id` int(10) unsigned NOT NULL default '0',
  `exam_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`calendar_id`,`exam_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_chat_mmc` (
  `rid` varchar(16) character set utf8 collate utf8_bin NOT NULL default '',
  `owner` varchar(64) NOT NULL default '',
  `title` text,
  `meetingID` varchar(32) NOT NULL default '',
  `meetingType` varchar(16) NOT NULL default 'joinnet',
  `extra` varchar(32) NOT NULL default '',
  `creator` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`rid`),
  KEY `idx1` (`meetingID`),
  KEY `idx2` (`meetingType`,`extra`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_chat_msg` (
  `rid` varchar(50) NOT NULL default '',
  `seq` double unsigned NOT NULL default '0',
  `msgType` tinyint(1) NOT NULL default '1',
  `msg` text NOT NULL,
  KEY `rid_seq` (`rid`,`seq`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_chat_records` (
  `rec_id` int(10) unsigned NOT NULL auto_increment,
  `board_id` bigint(20) unsigned NOT NULL default '0',
  `type` enum('school','course','class','course_grp','class_grp','others') NOT NULL default 'others',
  `owner_id` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`rec_id`),
  KEY `idx2` (`type`,`owner_id`),
  KEY `idx1` (`board_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_chat_session` (
  `rid` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `idx` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `realname` varchar(64) character set utf8 collate utf8_bin default NULL,
  `host` enum('N','Y') default 'N',
  `voice` enum('allow','deny') default 'allow',
  `login` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`rid`,`username`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;


CREATE TABLE `WM_chat_setting` (
  `rid` varchar(16) character set utf8 collate utf8_bin NOT NULL default '',
  `owner` varchar(64) character set utf8 collate utf8_bin NOT NULL default '',
  `title` text,
  `host` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `get_host` enum('Y','N') default 'Y',
  `maximum` tinyint(8) unsigned default '0',
  `exit_action` set('forum','notebook','email') default 'forum',
  `jump` enum('deny','allow') default 'deny',
  `open_time` datetime default NULL,
  `close_time` datetime default NULL,
  `state` enum('disable','open','taonly') NOT NULL default 'open',
  `visibility` enum('visible','hidden') NOT NULL default 'visible',
  `media` enum('disable','enable') default 'disable',
  `ip` varchar(128) character set utf8 collate utf8_bin default NULL,
  `port` tinyint(8) unsigned default '255',
  `protocol` enum('TCP','UDP') default 'TCP',
  `permute` int(10) unsigned NOT NULL default '0',
  `tone` text,
  PRIMARY KEY  (`rid`),
  KEY `idx1` (`owner`,`state`,`visibility`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_chat_user_setting` (
  `username` varchar(64) character set utf8 collate utf8_bin NOT NULL default '',
  `exit_action` set('forum','notebook','email') default 'notebook',
  `inout_msg` enum('visible','hidden') default 'visible',
  PRIMARY KEY  (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_class_director` (
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `class_id` int(10) unsigned NOT NULL default '0',
  `role` int(10) unsigned NOT NULL default '0',
  `allow_ip` varchar(255) default NULL,
  PRIMARY KEY  (`username`,`class_id`),
  UNIQUE KEY `idx1` (`class_id`,`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_class_group` (
  `parent` int(10) unsigned NOT NULL default '0',
  `child` int(10) unsigned NOT NULL default '0',
  `permute` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`parent`,`child`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_class_main` (
  `class_id` int(10) unsigned NOT NULL auto_increment,
  `caption` text NOT NULL,
  `dep_id` varchar(30) default NULL,
  `director` varchar(20) default NULL,
  `sing_start` datetime default NULL,
  `sing_end` datetime default NULL,
  `class_start` datetime default NULL,
  `class_end` datetime default NULL,
  `status` tinyint(3) unsigned default '0',
  `people_limit` smallint(6) unsigned default '0',
  `quota_limit` int(10) unsigned NOT NULL default '102400',
  `quota_used` int(10) unsigned NOT NULL default '0',
  `discuss` int(10) unsigned default NULL,
  `bulletin` bigint(20) unsigned default NULL,
  `path` varchar(128) default NULL,
  `log_times` int(10) unsigned NOT NULL default '0',
  `post_times` int(10) unsigned NOT NULL default '0',
  `dsc_times` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`class_id`),
  KEY `idx1` (`dep_id`),
  KEY `idx2` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=1000001 DEFAULT CHARSET=utf8;


CREATE TABLE `WM_class_member` (
  `class_id` int(10) unsigned NOT NULL default '0',
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `role` int(10) unsigned NOT NULL default '0',
  `login_times` int(10) unsigned default '0',
  `post_times` int(10) unsigned default '0',
  `dsc_times` int(10) unsigned default '0',
  `last_login` datetime default NULL,
  PRIMARY KEY  (`class_id`,`username`),
  KEY `idx1` (`username`),
  KEY `idx2` (`role`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_content` (
  `content_id` int(10) unsigned NOT NULL auto_increment,
  `caption` text NOT NULL,
  `path` varchar(128) NOT NULL default '',
  `quota_used` int(10) unsigned NOT NULL default '0',
  `quota_limit` int(10) unsigned NOT NULL default '0',
  `status` enum('disable','readonly','modifiable') NOT NULL default 'readonly',
  `kind` enum('content','group') NOT NULL default 'content',
  `content_type` enum('traditional','digitization') NOT NULL default 'digitization',
  `content_form` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `content_note` text NOT NULL,
  `content_sn` varchar(32) NOT NULL default '0',
  PRIMARY KEY  (`content_id`)
) ENGINE=MyISAM AUTO_INCREMENT=100001 DEFAULT CHARSET=utf8;


CREATE TABLE `WM_content_group` (
  `parent` int(10) unsigned NOT NULL default '0',
  `child` int(10) unsigned NOT NULL default '0',
  `permute` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`parent`,`child`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_content_ta` (
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `content_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`username`,`content_id`),
  UNIQUE KEY `idx1` (`content_id`,`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_grade_item` (
  `grade_id` int(10) unsigned NOT NULL default '0',
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `score` float NOT NULL default '0',
  `comment` text,
  PRIMARY KEY  (`grade_id`,`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_grade_list` (
  `course_id` int(10) unsigned NOT NULL default '0',
  `grade_id` int(10) unsigned NOT NULL auto_increment,
  `title` text NOT NULL,
  `source` tinyint(3) unsigned NOT NULL default '0',
  `property` int(10) unsigned NOT NULL default '0',
  `percent` float NOT NULL default '0',
  `publish_begin` datetime NOT NULL default '1970-01-01 00:00:00',
  `publish_end` datetime NOT NULL default '9999-12-31 00:00:00',
  `permute` int(10) unsigned NOT NULL default '65535',
  PRIMARY KEY  (`grade_id`),
  KEY `idx1` (`course_id`),
  KEY `idx2` (`source`),
  KEY `idx3` (`property`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_grade_stat` (
  `course_id` int(10) unsigned NOT NULL default '0',
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `total` float default NULL,
  `average` float default NULL,
  `range` mediumint(8) unsigned default NULL,
  PRIMARY KEY  (`course_id`,`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_history_grade_stat` (
  `serial_no` int(20) unsigned NOT NULL auto_increment,
  `course_id` int(10) unsigned NOT NULL default '0',
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `total` float default NULL,
  `average` float default NULL,
  `range` mediumint(8) unsigned default NULL,
  PRIMARY KEY  (`serial_no`,`course_id`,`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_history_log_classroom` (
  `serial_no` int(20) unsigned NOT NULL auto_increment,
  `function_id` bigint(20) unsigned NOT NULL default '0',
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `log_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `department_id` int(10) unsigned NOT NULL default '0',
  `instance` bigint(20) unsigned NOT NULL default '0',
  `result_id` smallint(5) unsigned NOT NULL default '0',
  `note` varchar(255) default NULL,
  `remote_address` varchar(128) NOT NULL default '',
  `user_agent` smallint(5) unsigned NOT NULL default '0',
  `script_name` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`serial_no`,`function_id`,`username`,`log_time`),
  KEY `ip_index` (`remote_address`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_history_log_director` (
  `serial_no` int(20) unsigned NOT NULL auto_increment,
  `function_id` bigint(20) unsigned NOT NULL default '0',
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `log_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `department_id` int(10) unsigned NOT NULL default '0',
  `instance` bigint(20) unsigned NOT NULL default '0',
  `result_id` smallint(5) unsigned NOT NULL default '0',
  `note` varchar(255) default NULL,
  `remote_address` varchar(128) NOT NULL default '',
  `user_agent` smallint(5) unsigned NOT NULL default '0',
  `script_name` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`serial_no`,`function_id`,`username`,`log_time`),
  KEY `ip_index` (`remote_address`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_history_log_manager` (
  `serial_no` int(20) unsigned NOT NULL auto_increment,
  `function_id` bigint(20) unsigned NOT NULL default '0',
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `log_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `department_id` int(10) unsigned NOT NULL default '0',
  `instance` bigint(20) unsigned NOT NULL default '0',
  `result_id` smallint(5) unsigned NOT NULL default '0',
  `note` varchar(255) default NULL,
  `remote_address` varchar(128) NOT NULL default '',
  `user_agent` smallint(5) unsigned NOT NULL default '0',
  `script_name` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`serial_no`,`function_id`,`username`,`log_time`),
  KEY `ip_index` (`remote_address`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_history_log_others` (
  `serial_no` int(20) unsigned NOT NULL auto_increment,
  `function_id` bigint(20) unsigned NOT NULL default '0',
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `log_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `department_id` int(10) unsigned NOT NULL default '0',
  `instance` bigint(20) unsigned NOT NULL default '0',
  `result_id` smallint(5) unsigned NOT NULL default '0',
  `note` varchar(255) default NULL,
  `remote_address` varchar(128) NOT NULL default '',
  `user_agent` smallint(5) unsigned NOT NULL default '0',
  `script_name` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`serial_no`,`function_id`,`username`,`log_time`),
  KEY `ip_index` (`remote_address`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_history_log_teacher` (
  `serial_no` int(20) unsigned NOT NULL auto_increment,
  `function_id` bigint(20) unsigned NOT NULL default '0',
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `log_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `department_id` int(10) unsigned NOT NULL default '0',
  `instance` bigint(20) unsigned NOT NULL default '0',
  `result_id` smallint(5) unsigned NOT NULL default '0',
  `note` varchar(255) default NULL,
  `remote_address` varchar(128) NOT NULL default '',
  `user_agent` smallint(5) unsigned NOT NULL default '0',
  `script_name` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`serial_no`,`function_id`,`username`,`log_time`),
  KEY `ip_index` (`remote_address`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_history_qti_exam_result` (
  `serial_no` int(20) unsigned NOT NULL auto_increment,
  `exam_id` int(10) unsigned NOT NULL default '0',
  `examinee` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `time_id` int(10) unsigned NOT NULL default '1',
  `status` enum('submit','break','revised','publish','forTA') default NULL,
  `begin_time` datetime default NULL,
  `submit_time` datetime default NULL,
  `score` float default NULL,
  `comment` mediumtext,
  `content` mediumtext,
  `ref_url` varchar(128) default NULL,
  `ref_file` varchar(255) default NULL,
  PRIMARY KEY  (`serial_no`,`exam_id`,`examinee`,`time_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_history_qti_homework_result` (
  `serial_no` int(20) unsigned NOT NULL auto_increment,
  `exam_id` int(10) unsigned NOT NULL default '0',
  `examinee` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `time_id` int(10) unsigned NOT NULL default '1',
  `status` enum('submit','break','revised','publish','forTA') default NULL,
  `begin_time` datetime default NULL,
  `submit_time` datetime default NULL,
  `score` float default NULL,
  `comment` mediumtext,
  `content` mediumtext,
  `ref_url` varchar(128) default NULL,
  `ref_file` varchar(255) default NULL,
  PRIMARY KEY  (`serial_no`,`exam_id`,`examinee`,`time_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_history_qti_questionnaire_result` (
  `serial_no` int(20) unsigned NOT NULL auto_increment,
  `exam_id` int(10) unsigned NOT NULL default '0',
  `examinee` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `time_id` int(10) unsigned NOT NULL default '1',
  `status` enum('submit','break','revised','publish','forTA') default NULL,
  `begin_time` datetime default NULL,
  `submit_time` datetime default NULL,
  `score` float default NULL,
  `comment` mediumtext,
  `content` mediumtext,
  `ref_url` varchar(128) default NULL,
  `ref_file` varchar(255) default NULL,
  PRIMARY KEY  (`serial_no`,`exam_id`,`examinee`,`time_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_history_record_daily_course` (
  `serial_no` int(20) unsigned NOT NULL auto_increment,
  `course_id` int(10) unsigned NOT NULL default '0',
  `thatday` date NOT NULL default '0000-00-00',
  `reading_seconds` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`serial_no`,`course_id`,`thatday`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_history_record_daily_personal` (
  `serial_no` int(20) unsigned NOT NULL auto_increment,
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `course_id` int(10) unsigned NOT NULL default '0',
  `thatday` date NOT NULL default '0000-00-00',
  `reading_seconds` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`serial_no`,`username`,`course_id`,`thatday`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_history_record_reading` (
  `serial_no` int(20) unsigned NOT NULL default '0',
  `course_id` int(10) unsigned NOT NULL default '0',
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `begin_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `over_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `title` varchar(255) NOT NULL default '',
  `url` varchar(255) NOT NULL default '',
  `activity_id` varchar(255) default NULL,
  PRIMARY KEY  (`serial_no`,`course_id`,`username`,`begin_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_history_scorm_tracking` (
  `serial_no` int(20) unsigned NOT NULL auto_increment,
  `course_id` int(10) unsigned NOT NULL default '0',
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `activity_id` varchar(255) character set utf8 collate utf8_bin NOT NULL default '',
  `tm_data` mediumtext,
  PRIMARY KEY  (`serial_no`,`course_id`,`username`,`activity_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_history_term_major` (
  `serial_no` int(20) unsigned NOT NULL auto_increment,
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `course_id` int(10) unsigned NOT NULL default '0',
  `role` int(10) unsigned NOT NULL default '0',
  `post` mediumint(8) unsigned default '0',
  `hw` mediumint(8) unsigned default '0',
  `qp` mediumint(8) unsigned default '0',
  `exam` mediumint(8) unsigned default '0',
  `bookmark` int(10) unsigned default '0',
  `degree` int(10) unsigned default '0',
  `total_node` int(10) unsigned default '0',
  `login_times` int(10) unsigned default '0',
  `post_times` int(10) unsigned default '0',
  `dsc_times` int(10) unsigned default '0',
  `last_login` datetime default NULL,
  `add_time` datetime default NULL,
  PRIMARY KEY  (`serial_no`,`username`,`course_id`),
  KEY `idx1` (`course_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_history_user_account` (
  `serial_no` int(20) unsigned NOT NULL auto_increment,
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `password` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `enable` enum('N','Y') NOT NULL default 'N',
  `first_name` varchar(32) character set utf8 collate utf8_bin default '',
  `last_name` varchar(32) character set utf8 collate utf8_bin default '',
  `gender` enum('F','M') NOT NULL default 'F',
  `birthday` date default NULL,
  `personal_id` varchar(32) default NULL,
  `email` varchar(64) NOT NULL default '',
  `homepage` varchar(64) default NULL,
  `home_tel` varchar(32) default NULL,
  `home_fax` varchar(32) default NULL,
  `home_address` varchar(64) default NULL,
  `office_tel` varchar(32) default NULL,
  `office_fax` varchar(32) default NULL,
  `office_address` varchar(64) default NULL,
  `cell_phone` varchar(32) default NULL,
  `company` varchar(64) default NULL,
  `department` varchar(64) default NULL,
  `title` varchar(32) default NULL,
  `language` varchar(32) default NULL,
  `theme` varchar(32) default 'default',
  `msg_reserved` tinyint(1) NOT NULL default '0',
  `hid` int(10) unsigned NOT NULL default '262075',  
  PRIMARY KEY  (`serial_no`,`username`),
  UNIQUE KEY `idx1` (`serial_no`,`username`,`password`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_im_message` (
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `serial` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `sorder` tinyint(4) unsigned NOT NULL default '0',
  `sender` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `sender_name` varchar(64) character set utf8 collate utf8_bin default 'NULL',
  `reciver` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `send_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `talk` enum('Talk','Accept','Refuse','Alert') default NULL,
  `chat_id` varchar(32) character set utf8 collate utf8_bin default NULL,
  `message` varchar(254) default NULL,
  `ctype` enum('text','html') default 'text',
  `saw` enum('N','Y') default 'N',
  PRIMARY KEY  (`username`,`serial`,`sorder`),
  KEY `idx1` (`saw`),
  KEY `idx2` (`reciver`),
  KEY `idx3` (`serial`),
  KEY `idx4` (`sorder`,`talk`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_im_setting` (
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `recive` enum('N','Y') NOT NULL default 'Y',
  `talk` enum('N','Y') NOT NULL default 'Y',
  `status` enum('Offline','Online','Away','DND','Occupied','Chat','Invisible','Phone','Lunch') NOT NULL default 'Online',
  `contacts` text,
  PRIMARY KEY  (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_ipfilter` (
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `host` varchar(255) NOT NULL default '',
  `mode` enum('deny','allow') NOT NULL default 'deny',
  `priority` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`username`,`host`,`mode`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_log_classroom` (
  `function_id` bigint(20) unsigned NOT NULL default '0',
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `log_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `department_id` int(10) unsigned NOT NULL default '0',
  `instance` bigint(20) unsigned NOT NULL default '0',
  `result_id` smallint(5) unsigned NOT NULL default '0',
  `note` varchar(255) default NULL,
  `remote_address` varchar(128) NOT NULL default '',
  `user_agent` smallint(5) unsigned NOT NULL default '0',
  `script_name` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`function_id`,`username`,`log_time`),
  KEY `ip_index` (`remote_address`(15)),
  KEY `idx1` (`username`),
  KEY `idx2` (`department_id`,`instance`),
  KEY `idx3` (`result_id`),
  KEY `idx4` (`log_time`),
  KEY `idx5` (`note`(30))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_log_director` (
  `function_id` bigint(20) unsigned NOT NULL default '0',
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `log_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `department_id` int(10) unsigned NOT NULL default '0',
  `instance` bigint(20) unsigned NOT NULL default '0',
  `result_id` smallint(5) unsigned NOT NULL default '0',
  `note` varchar(255) default NULL,
  `remote_address` varchar(128) NOT NULL default '',
  `user_agent` smallint(5) unsigned NOT NULL default '0',
  `script_name` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`function_id`,`username`,`log_time`),
  KEY `ip_index` (`remote_address`(15))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_log_manager` (
  `function_id` bigint(20) unsigned NOT NULL default '0',
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `log_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `department_id` int(10) unsigned NOT NULL default '0',
  `instance` bigint(20) unsigned NOT NULL default '0',
  `result_id` smallint(5) unsigned NOT NULL default '0',
  `note` varchar(255) default NULL,
  `remote_address` varchar(128) NOT NULL default '',
  `user_agent` smallint(5) unsigned NOT NULL default '0',
  `script_name` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`function_id`,`username`,`log_time`),
  KEY `ip_index` (`remote_address`(15))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_log_others` (
  `function_id` bigint(20) unsigned NOT NULL default '0',
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `log_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `department_id` int(10) unsigned NOT NULL default '0',
  `instance` bigint(20) unsigned NOT NULL default '0',
  `result_id` smallint(5) unsigned NOT NULL default '0',
  `note` varchar(255) default NULL,
  `remote_address` varchar(128) NOT NULL default '',
  `user_agent` smallint(5) unsigned NOT NULL default '0',
  `script_name` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`function_id`,`username`,`log_time`),
  KEY `ip_index` (`remote_address`(15)),
  KEY `idx1` (`username`),
  KEY `idx2` (`department_id`,`instance`),
  KEY `idx3` (`result_id`),
  KEY `idx4` (`log_time`),
  KEY `idx5` (`note`(30))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_log_teacher` (
  `function_id` bigint(20) unsigned NOT NULL default '0',
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `log_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `department_id` int(10) unsigned NOT NULL default '0',
  `instance` bigint(20) unsigned NOT NULL default '0',
  `result_id` smallint(5) unsigned NOT NULL default '0',
  `note` varchar(255) default NULL,
  `remote_address` varchar(128) NOT NULL default '',
  `user_agent` smallint(5) unsigned NOT NULL default '0',
  `script_name` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`function_id`,`username`,`log_time`),
  KEY `ip_index` (`remote_address`(15))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_log_userAgent` (
  `agent_id` smallint(5) unsigned NOT NULL auto_increment,
  `agent_detail` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`agent_id`),
  UNIQUE KEY `idx1` (`agent_detail`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_mails` (
  `mail_serial` bigint(20) unsigned NOT NULL auto_increment,
  `function_id` int(10) unsigned NOT NULL default '0',
  `froms` varchar(32) NOT NULL default '',
  `tos` text,
  `submit_time` datetime default NULL,
  `send_status` enum('0','1') NOT NULL default '0',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`mail_serial`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_member_div` (
  `class_id` int(10) unsigned NOT NULL default '0',
  `group_id` mediumint(8) unsigned NOT NULL default '0',
  `team_id` mediumint(8) unsigned NOT NULL default '0',
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  PRIMARY KEY  (`class_id`,`group_id`,`team_id`,`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_member_group` (
  `class_id` int(10) unsigned NOT NULL default '0',
  `group_id` mediumint(8) unsigned NOT NULL default '0',
  `team_id` mediumint(8) unsigned NOT NULL default '0',
  `caption` text NOT NULL,
  `captain` varchar(32) character set utf8 collate utf8_bin default NULL,
  `board_id` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`class_id`,`group_id`,`team_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_msg_folder` (
  `username` varchar(32) NOT NULL default '',
  `content` text NOT NULL,
  PRIMARY KEY  (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_msg_message` (
  `msg_serial` bigint(20) unsigned NOT NULL auto_increment,
  `folder_id` varchar(32) NOT NULL default '',
  `sender` varchar(32) NOT NULL default '',
  `receiver` varchar(32) NOT NULL default '',
  `submit_time` datetime default NULL,
  `receive_time` datetime default NULL,
  `status` set('read','reply','forward') default NULL,
  `priority` tinyint(4) NOT NULL default '0',
  `subject` varchar(255) NOT NULL default '',
  `content` text NOT NULL,
  `attachment` text,
  `note` text,
  `content_type` enum('text','html') default 'text',
  PRIMARY KEY  (`msg_serial`),
  KEY `idx1` (`receiver`,`status`),
  KEY `idx2` (`folder_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_news_posts` (
  `news_id` int(10) unsigned NOT NULL default '0',
  `board_id` bigint(20) unsigned NOT NULL default '0',
  `node` varchar(19) NOT NULL default '',
  `open_time` datetime default NULL,
  `close_time` datetime default NULL,
  PRIMARY KEY  (`board_id`,`node`),
  KEY `idx1` (`news_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_news_subject` (
  `news_id` int(10) unsigned NOT NULL auto_increment,
  `board_id` bigint(20) unsigned NOT NULL default '0',
  `type` varchar(10) NOT NULL default 'news',
  `visibility` enum('visible','hidden') NOT NULL default 'visible',
  PRIMARY KEY  (`news_id`),
  KEY `idx1` (`board_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


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


CREATE TABLE `WM_qti_exam_item` (
  `ident` varchar(255) character set utf8 collate utf8_bin NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `course_id` int(10) unsigned NOT NULL default '0',
  `type` tinyint(3) unsigned NOT NULL default '0',
  `version` tinyint(3) unsigned default NULL,
  `volume` tinyint(3) unsigned default NULL,
  `chapter` tinyint(3) unsigned default NULL,
  `paragraph` tinyint(3) unsigned default NULL,
  `section` tinyint(3) unsigned default NULL,
  `level` tinyint(3) unsigned NOT NULL default '0',
  `language` tinyint(3) unsigned NOT NULL default '0',
  `author` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `create_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `last_modify` datetime NOT NULL default '0000-00-00 00:00:00',
  `content` text,
  `answer` varchar(255) default NULL,
  `attach` text,
  PRIMARY KEY  (`ident`),
  KEY `idx1` (`course_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_qti_exam_result` (
  `exam_id` int(10) unsigned NOT NULL default '0',
  `examinee` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `time_id` int(10) unsigned NOT NULL default '1',
  `status` enum('submit','break','revised','publish','forTA') default NULL,
  `begin_time` datetime default NULL,
  `submit_time` datetime default NULL,
  `score` float default NULL,
  `comment` mediumtext,
  `content` mediumtext,
  `ref_url` varchar(128) default NULL,
  `ref_file` varchar(255) default NULL,
  PRIMARY KEY  (`exam_id`,`examinee`,`time_id`),
  KEY `idx1` (`status`,`exam_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_qti_exam_test` (
  `exam_id` int(10) unsigned NOT NULL auto_increment,
  `course_id` int(10) unsigned NOT NULL default '0',
  `title` text NOT NULL,
  `sort` int(10) unsigned NOT NULL default '0',
  `type` tinyint(3) unsigned NOT NULL default '0',
  `modifiable` enum('N','Y') NOT NULL default 'N',
  `publish` enum('prepare','action','close') NOT NULL default 'prepare',
  `begin_time` datetime default NULL,
  `close_time` datetime default NULL,
  `count_type` enum('none','first','last','max','min','average') NOT NULL default 'first',
  `percent` float unsigned NOT NULL default '0',
  `do_times` smallint(5) unsigned NOT NULL default '1',
  `do_interval` smallint(5) unsigned NOT NULL default '60',
  `item_per_page` smallint(5) unsigned NOT NULL default '0',
  `ctrl_paging` enum('none','can_return','lock') NOT NULL default 'none',
  `ctrl_window` enum('none','lock') NOT NULL default 'none',
  `ctrl_timeout` enum('none','mark','auto_submit') NOT NULL default 'none',
  `announce_type` enum('never','now','close_time','user_define') NOT NULL default 'never',
  `announce_time` datetime default NULL,
  `item_cramble` set('enable','choice','item','section','random_pick') default NULL,
  `random_pick` mediumint(8) unsigned default NULL,
  `setting` set('upload','anonymity') NOT NULL default '',
  `notice` text,
  `content` mediumtext,
  PRIMARY KEY  (`exam_id`),
  KEY `idx1` (`course_id`,`publish`)
) ENGINE=MyISAM AUTO_INCREMENT=100000001 DEFAULT CHARSET=utf8;


CREATE TABLE `WM_qti_homework_item` (
  `ident` varchar(255) character set utf8 collate utf8_bin NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `course_id` int(10) unsigned NOT NULL default '0',
  `type` tinyint(3) unsigned NOT NULL default '0',
  `version` tinyint(3) unsigned default NULL,
  `volume` tinyint(3) unsigned default NULL,
  `chapter` tinyint(3) unsigned default NULL,
  `paragraph` tinyint(3) unsigned default NULL,
  `section` tinyint(3) unsigned default NULL,
  `level` tinyint(3) unsigned NOT NULL default '0',
  `language` tinyint(3) unsigned NOT NULL default '0',
  `author` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `create_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `last_modify` datetime NOT NULL default '0000-00-00 00:00:00',
  `content` text,
  `answer` varchar(255) default NULL,
  `attach` text,
  PRIMARY KEY  (`ident`),
  KEY `idx1` (`course_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_qti_homework_result` (
  `exam_id` int(10) unsigned NOT NULL default '0',
  `examinee` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `time_id` int(10) unsigned NOT NULL default '1',
  `status` enum('submit','break','revised','publish','forTA') default NULL,
  `begin_time` datetime default NULL,
  `submit_time` datetime default NULL,
  `score` float default NULL,
  `comment` mediumtext,
  `content` mediumtext,
  `ref_url` varchar(128) default NULL,
  `ref_file` varchar(255) default NULL,
  PRIMARY KEY  (`exam_id`,`examinee`,`time_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_qti_homework_test` (
  `exam_id` int(10) unsigned NOT NULL auto_increment,
  `course_id` int(10) unsigned NOT NULL default '0',
  `title` text NOT NULL,
  `sort` int(10) unsigned NOT NULL default '0',
  `type` tinyint(3) unsigned NOT NULL default '0',
  `modifiable` enum('N','Y') NOT NULL default 'N',
  `publish` enum('prepare','action','close') NOT NULL default 'prepare',
  `begin_time` datetime default NULL,
  `close_time` datetime default NULL,
  `count_type` enum('first','last','max','min','average') NOT NULL default 'first',
  `percent` float unsigned NOT NULL default '0',
  `do_times` smallint(5) unsigned NOT NULL default '1',
  `do_interval` smallint(5) unsigned NOT NULL default '60',
  `item_per_page` smallint(5) unsigned NOT NULL default '0',
  `ctrl_paging` enum('none','can_return','lock') NOT NULL default 'none',
  `ctrl_window` enum('none','lock') NOT NULL default 'none',
  `ctrl_timeout` enum('none','mark','auto_submit') NOT NULL default 'none',
  `announce_type` enum('never','now','close_time','user_define') NOT NULL default 'never',
  `announce_time` datetime default NULL,
  `item_cramble` set('enable','choice','item','section','random_pick') default NULL,
  `random_pick` mediumint(8) unsigned default NULL,
  `setting` set('upload','anonymity') NOT NULL default '',
  `notice` text,
  `content` mediumtext,
  PRIMARY KEY  (`exam_id`),
  KEY `idx1` (`course_id`,`publish`)
) ENGINE=MyISAM AUTO_INCREMENT=100000001 DEFAULT CHARSET=utf8;


CREATE TABLE `WM_qti_questionnaire_item` (
  `ident` varchar(255) character set utf8 collate utf8_bin NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `course_id` int(10) unsigned NOT NULL default '0',
  `type` tinyint(3) unsigned NOT NULL default '0',
  `version` tinyint(3) unsigned default NULL,
  `volume` tinyint(3) unsigned default NULL,
  `chapter` tinyint(3) unsigned default NULL,
  `paragraph` tinyint(3) unsigned default NULL,
  `section` tinyint(3) unsigned default NULL,
  `level` tinyint(3) unsigned NOT NULL default '0',
  `language` tinyint(3) unsigned NOT NULL default '0',
  `author` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `create_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `last_modify` datetime NOT NULL default '0000-00-00 00:00:00',
  `content` text,
  `answer` varchar(255) default NULL,
  `attach` text,
  PRIMARY KEY  (`ident`),
  KEY `idx1` (`course_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_qti_questionnaire_result` (
  `exam_id` int(10) unsigned NOT NULL default '0',
  `examinee` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `time_id` int(10) unsigned NOT NULL default '1',
  `status` enum('submit','break','revised','publish','forTA') default NULL,
  `begin_time` datetime default NULL,
  `submit_time` datetime default NULL,
  `score` float default NULL,
  `comment` mediumtext,
  `content` mediumtext,
  `ref_url` varchar(128) default NULL,
  `ref_file` varchar(255) default NULL,
  PRIMARY KEY  (`exam_id`,`examinee`,`time_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_qti_questionnaire_test` (
  `exam_id` int(10) unsigned NOT NULL auto_increment,
  `course_id` int(10) unsigned NOT NULL default '0',
  `title` text NOT NULL,
  `sort` int(10) unsigned NOT NULL default '0',
  `type` tinyint(3) unsigned NOT NULL default '0',
  `modifiable` enum('N','Y') NOT NULL default 'N',
  `publish` enum('prepare','action','close') NOT NULL default 'prepare',
  `begin_time` datetime default NULL,
  `close_time` datetime default NULL,
  `count_type` enum('first','last','max','min','average') NOT NULL default 'first',
  `percent` float unsigned NOT NULL default '0',
  `do_times` smallint(5) unsigned NOT NULL default '1',
  `do_interval` smallint(5) unsigned NOT NULL default '60',
  `item_per_page` smallint(5) unsigned NOT NULL default '0',
  `ctrl_paging` enum('none','can_return','lock') NOT NULL default 'none',
  `ctrl_window` enum('none','lock') NOT NULL default 'none',
  `ctrl_timeout` enum('none','mark','auto_submit') NOT NULL default 'none',
  `announce_type` enum('never','now','close_time','user_define') NOT NULL default 'never',
  `announce_time` datetime default NULL,
  `item_cramble` set('enable','choice','item','section','random_pick') default NULL,
  `random_pick` mediumint(8) unsigned default NULL,
  `setting` set('upload','anonymity') NOT NULL default '',
  `notice` text,
  `content` mediumtext,
  PRIMARY KEY  (`exam_id`),
  KEY `idx1` (`course_id`,`publish`)
) ENGINE=MyISAM AUTO_INCREMENT=100000001 DEFAULT CHARSET=utf8;


CREATE TABLE `WM_qti_share_item` (
  `serial_no` int(10) unsigned NOT NULL auto_increment,
  `category` enum('exam','homework','questionnaire') NOT NULL default 'exam',
  `ident` varchar(255) character set utf8 collate utf8_bin NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `course_id` int(10) unsigned NOT NULL default '0',
  `type` tinyint(3) unsigned NOT NULL default '0',
  `version` tinyint(3) unsigned default NULL,
  `volume` tinyint(3) unsigned default NULL,
  `chapter` tinyint(3) unsigned default NULL,
  `paragraph` tinyint(3) unsigned default NULL,
  `section` tinyint(3) unsigned default NULL,
  `level` tinyint(3) unsigned NOT NULL default '0',
  `language` tinyint(3) unsigned NOT NULL default '0',
  `author` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `create_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `last_modify` datetime NOT NULL default '0000-00-00 00:00:00',
  `content` text,
  `answer` varchar(255) default NULL,
  `attach` varchar(255) default NULL,
  PRIMARY KEY  (`serial_no`),
  UNIQUE KEY `idx1` (`ident`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_record_daily_course` (
  `course_id` int(10) unsigned NOT NULL default '0',
  `thatday` date NOT NULL default '0000-00-00',
  `reading_seconds` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`course_id`,`thatday`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_record_daily_personal` (
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `course_id` int(10) unsigned NOT NULL default '0',
  `thatday` date NOT NULL default '0000-00-00',
  `reading_seconds` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`username`,`course_id`,`thatday`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_record_learn_record` (
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `realname` varchar(64) character set utf8 collate utf8_bin NOT NULL default '',
  `total_course` int(11) NOT NULL default '0',
  `total_grade` int(11) NOT NULL default '0',
  `login_times` int(11) NOT NULL default '0',
  `post_times` int(11) NOT NULL default '0',
  `dsc_times` int(11) NOT NULL default '0',
  `total_readtime` int(11) NOT NULL default '0',
  `total_readpages` int(11) NOT NULL default '0',
  PRIMARY KEY  (`username`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;


CREATE TABLE `WM_record_reading` (
  `course_id` int(10) unsigned NOT NULL default '0',
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `begin_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `over_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `title` varchar(255) NOT NULL default '',
  `url` varchar(255) NOT NULL default '',
  `activity_id` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`course_id`,`username`,`begin_time`,`over_time`),
  KEY `idx1` (`begin_time`),
  KEY `idx2` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_review_flow` (
  `idx` bigint(20) unsigned NOT NULL auto_increment,
  `flow_serial` bigint(20) unsigned NOT NULL default '0',
  `username` varchar(32) NOT NULL default '',
  `create_time` datetime default NULL,
  `kind` varchar(32) NOT NULL default '',
  `discren_id` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `state` enum('open','close') default NULL,
  `param` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `result` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `content` text NOT NULL,
  PRIMARY KEY  (`idx`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_review_syscont` (
  `flow_serial` bigint(20) unsigned NOT NULL auto_increment,
  `kind` varchar(32) NOT NULL default '',
  `start` varchar(255) character set utf8 collate utf8_bin default NULL,
  `title` text NOT NULL,
  `content` text NOT NULL,
  `permute` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`flow_serial`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_review_sysidx` (
  `idx` bigint(20) unsigned NOT NULL auto_increment,
  `discren_id` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `flow_serial` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`idx`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_roll_call` (
  `serial_id` bigint(20) unsigned NOT NULL auto_increment,
  `course_id` int(10) unsigned NOT NULL default '0',
  `team_id` mediumint(8) unsigned default NULL,
  `group_id` mediumint(8) unsigned default NULL,
  `enable` enum('enable','disable') default 'enable',
  `role` enum('all','student','auditor') NOT NULL default 'student',
  `mtType` enum('login','lesson','progress','chat','post','homework','exam','questionnaire') NOT NULL default 'login',
  `mtFilter` enum('total','off','last','no','yes','some','page') NOT NULL default 'total',
  `mtOP` enum('equal','greater','smaller','greater_equal','smaller_equal','differ','yes','no') NOT NULL default 'equal',
  `mtVal` varchar(10) NOT NULL default '',
  `frequence` enum('once','day','week','month') NOT NULL default 'once',
  `freq_extra` varchar(10) default NULL,
  `begin_time` datetime default NULL,
  `end_time` datetime default NULL,
  `mail_subject` varchar(255) NOT NULL default '',
  `mail_content` text,
  `mail_attach` text,
  `mail_cc` tinyint(1) NOT NULL default '0',
  UNIQUE KEY `serial_id` (`serial_id`,`course_id`),
  KEY `idx2` (`course_id`,`enable`,`begin_time`,`end_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_save_temporary` (
  `function_id` varchar(64) character set utf8 collate utf8_bin NOT NULL default '',
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `save_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `content` mediumtext NOT NULL,
  PRIMARY KEY  (`function_id`,`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_scorm_cmi` (
  `course_id` int(10) unsigned NOT NULL default '0',
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `sco_id` varchar(255) character set utf8 collate utf8_bin NOT NULL default '',
  `cmi_data` mediumtext,
  PRIMARY KEY  (`course_id`,`username`,`sco_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_scorm_tracking` (
  `course_id` int(10) unsigned NOT NULL default '0',
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `activity_id` varchar(255) character set utf8 collate utf8_bin NOT NULL default '',
  `tm_data` mediumtext,
  PRIMARY KEY  (`course_id`,`username`,`activity_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_session` (
  `idx` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `username` varchar(32) character set utf8 collate utf8_bin default NULL,
  `realname` varchar(64) default NULL,
  `email` varchar(64) default NULL,
  `homepage` varchar(64) default NULL,
  `school_id` int(10) unsigned default NULL,
  `school_name` varchar(128) default NULL,
  `course_id` int(10) unsigned default NULL,
  `course_name` varchar(128) default NULL,
  `class_id` int(10) unsigned default NULL,
  `class_name` varchar(128) default NULL,
  `role` int(10) unsigned NOT NULL default '0',
  `ip` varchar(64) default NULL,
  `ticket` varchar(32) default NULL,
  `cur_func` int(10) unsigned NOT NULL default '0',
  `touch` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `chance` tinyint(3) unsigned NOT NULL default '0',
  `room_id` varchar(32) default NULL,
  `session` varchar(255) default NULL,
  `board_name` varchar(128) default NULL,
  `q_path` varchar(128) character set utf8 collate utf8_bin default NULL,
  `news_nodes` varchar(255) default '',
  `board_ownerid` bigint(20) unsigned NOT NULL default '0',
  `board_ownername` varchar(128) character set utf8 collate utf8_bin NOT NULL default '',
  PRIMARY KEY  (`idx`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;


CREATE TABLE `WM_student_div` (
  `course_id` int(10) unsigned NOT NULL default '0',
  `group_id` mediumint(8) unsigned NOT NULL default '0',
  `team_id` mediumint(8) unsigned NOT NULL default '0',
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `permute` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`course_id`,`team_id`,`username`),
  KEY `idx1` (`team_id`,`group_id`,`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_student_group` (
  `course_id` int(10) unsigned NOT NULL default '0',
  `group_id` mediumint(8) unsigned NOT NULL default '0',
  `team_id` mediumint(8) unsigned NOT NULL default '0',
  `caption` text NOT NULL,
  `captain` varchar(32) character set utf8 collate utf8_bin default NULL,
  `board_id` bigint(20) unsigned NOT NULL default '0',
  `permute` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`course_id`,`group_id`,`team_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_student_separate` (
  `course_id` int(10) unsigned NOT NULL default '0',
  `team_id` int(10) unsigned NOT NULL default '0',
  `team_name` text,
  `permute` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`course_id`,`team_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_term_course` (
  `course_id` int(10) unsigned NOT NULL auto_increment,
  `content_id` int(10) unsigned default NULL,
  `caption` text NOT NULL,
  `teacher` varchar(128) default NULL,
  `kind` enum('group','course') default 'course',
  `en_begin` date default NULL,
  `en_end` date default NULL,
  `st_begin` date default NULL,
  `st_end` date default NULL,
  `status` tinyint(3) unsigned default '0',
  `texts` varchar(255) default NULL,
  `url` varchar(255) default NULL,
  `content` text,
  `credit` tinyint(3) unsigned default NULL,
  `discuss` int(10) unsigned default NULL,
  `bulletin` bigint(20) unsigned default NULL,
  `n_limit` smallint(6) default '0',
  `a_limit` smallint(6) default '0',
  `quota_used` int(10) unsigned NOT NULL default '0',
  `quota_limit` int(10) unsigned NOT NULL default '102400',
  `path` varchar(128) NOT NULL default '',
  `login_times` int(10) unsigned NOT NULL default '0',
  `post_times` int(10) unsigned NOT NULL default '0',
  `dsc_times` int(10) unsigned NOT NULL default '0',
  `fair_grade` int(6) unsigned default '60',
  `ta_can_sets` set('content_id','caption','en_begin','en_end','st_begin','st_end','status','texts','url','content','n_limit','a_limit','fair_grade','review','cparent') NOT NULL default 'caption,st_begin,st_end,status,texts,url,content,n_limit,a_limit,fair_grade',
  PRIMARY KEY  (`course_id`),
  KEY `idx1` (`status`,`st_begin`,`st_end`),
  KEY `idx2` (`kind`)
) ENGINE=MyISAM AUTO_INCREMENT=10000001 DEFAULT CHARSET=utf8;


CREATE TABLE `WM_term_group` (
  `parent` int(10) unsigned NOT NULL default '0',
  `child` int(10) unsigned NOT NULL default '0',
  `permute` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`parent`,`child`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_term_introduce` (
  `course_id` int(10) unsigned NOT NULL default '0',
  `intro_type` enum('C','R','T') NOT NULL default 'C',
  `content` mediumtext,
  PRIMARY KEY  (`course_id`,`intro_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_term_major` (
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `course_id` int(10) unsigned NOT NULL default '0',
  `role` int(10) unsigned NOT NULL default '0',
  `post` mediumint(8) unsigned default '0',
  `hw` mediumint(8) unsigned default '0',
  `qp` mediumint(8) unsigned default '0',
  `exam` mediumint(8) unsigned default '0',
  `bookmark` int(10) unsigned default '0',
  `degree` int(10) unsigned default '0',
  `total_node` int(10) unsigned default '0',
  `login_times` int(10) unsigned default '0',
  `post_times` int(10) unsigned default '0',
  `dsc_times` int(10) unsigned default '0',
  `last_login` datetime default NULL,
  `add_time` datetime default NULL,
  PRIMARY KEY  (`username`,`course_id`),
  UNIQUE KEY `idx1` (`course_id`,`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_term_path` (
  `course_id` int(10) unsigned NOT NULL default '0',
  `serial` tinyint(3) unsigned NOT NULL default '0',
  `content` longtext,
  `username` varchar(32) NOT NULL,
  `update_time` datetime NOT NULL,
  PRIMARY KEY  (`course_id`,`serial`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_term_subject` (
  `course_id` int(10) unsigned NOT NULL default '0',
  `node_id` int(10) unsigned NOT NULL auto_increment,
  `board_id` bigint(20) unsigned NOT NULL default '0',
  `state` enum('disable','open','taonly','public') NOT NULL default 'open',
  `visibility` enum('visible','hidden') NOT NULL default 'visible',
  `permute` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`node_id`),
  UNIQUE KEY `idx1` (`course_id`,`node_id`,`board_id`,`state`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_term_teacher` (
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `course_id` int(10) unsigned NOT NULL default '0',
  `level` enum('assistant','instructor','teacher') NOT NULL default 'assistant',
  `allow_ip` varchar(255) default NULL,
  `enable` enum('false','true') NOT NULL default 'true',
  PRIMARY KEY  (`username`,`course_id`,`level`),
  UNIQUE KEY `idx` (`course_id`,`username`,`level`),
  KEY `idx2` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_tips` (
  `tip_id` int(10) unsigned NOT NULL auto_increment,
  `lang` varchar(16) character set utf8 collate utf8_bin NOT NULL default '',
  `subject` varchar(254) character set utf8 collate utf8_bin default NULL,
  `content` text,
  `visibility` enum('visible','hidden') default 'visible',
  `open_begin` date default NULL,
  `open_end` date default NULL,
  PRIMARY KEY  (`tip_id`),
  UNIQUE KEY `tip_id` (`tip_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_user_account` (
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `password` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `enable` enum('N','Y') NOT NULL default 'N',
  `first_name` varchar(32) character set utf8 collate utf8_bin default NULL,
  `last_name` varchar(32) character set utf8 collate utf8_bin default NULL,
  `gender` enum('F','M') NOT NULL default 'F',
  `birthday` date default NULL,
  `personal_id` varchar(32) default NULL,
  `email` varchar(64) NOT NULL default '',
  `homepage` varchar(64) default NULL,
  `home_tel` varchar(32) default NULL,
  `home_fax` varchar(32) default NULL,
  `home_address` varchar(255) default NULL,
  `office_tel` varchar(32) default NULL,
  `office_fax` varchar(32) default NULL,
  `office_address` varchar(255) default NULL,
  `cell_phone` varchar(32) default NULL,
  `company` varchar(255) default NULL,
  `department` varchar(64) default NULL,
  `title` varchar(32) default NULL,
  `language` varchar(32) default NULL,
  `theme` varchar(32) default 'default',
  `msg_reserved` tinyint(1) NOT NULL default '0',
  `hid` int(10) unsigned NOT NULL default '262075',
  PRIMARY KEY  (`username`),
  UNIQUE KEY `idx1` (`username`,`password`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_user_picture` (
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `picture` mediumblob,
  PRIMARY KEY  (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `WM_user_tagline` (
  `serial` int(10) unsigned NOT NULL auto_increment,
  `username` varchar(32) character set utf8 collate utf8_bin NOT NULL default '',
  `title` varchar(254) character set utf8 collate utf8_bin default NULL,
  `ctype` enum('text','html') NOT NULL default 'text',
  `tagline` text,
  PRIMARY KEY  (`serial`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `cmi_comments_from_learner` (
  `Course_ID` int(10) unsigned default NULL,
  `SCO_ID` varchar(50) default NULL,
  `User_ID` varchar(50) default NULL,
  `n` int(11) default NULL,
  `comment` text,
  `location` text,
  `timestamp` varchar(50) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `cmi_comments_from_lms` (
  `Course_ID` int(10) unsigned default NULL,
  `SCO_ID` varchar(50) default NULL,
  `User_ID` varchar(50) default NULL,
  `n` int(11) default NULL,
  `comment` text,
  `location` text,
  `timestamp` varchar(50) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `cmi_core` (
  `Course_ID` int(10) unsigned NOT NULL default '0',
  `SCO_ID` varchar(50) NOT NULL default '',
  `User_ID` varchar(50) NOT NULL default '',
  `Scorm_Type` varchar(50) default NULL,
  `lesson_location` text,
  `credit` varchar(50) default NULL,
  `lesson_status` varchar(50) default NULL,
  `duration` varchar(50) default NULL,
  `lesson_mode` varchar(50) default NULL,
  `exit_value` varchar(50) default NULL,
  `session_time` text,
  `suspend_data` text,
  `launch_data` text,
  `entry` varchar(50) default NULL,
  `score_raw` varchar(50) default NULL,
  `score_min` varchar(50) default NULL,
  `score_max` varchar(50) default NULL,
  `score_normalized` varchar(50) default NULL,
  `last_time` varchar(50) default NULL,
  `isSuspended` varchar(50) default NULL,
  `success_status` varchar(50) default NULL,
  `completion_status` varchar(50) default NULL,
  `attempt_count` varchar(50) default NULL,
  `isDisabled` varchar(50) default NULL,
  `isHiddenFromChoice` varchar(50) default NULL,
  `attempt_absolut_duration` varchar(50) default NULL,
  `attempt_experienced_duration` varchar(50) default NULL,
  `activity_absolut_duration` varchar(50) default NULL,
  `activity_experienced_duration` varchar(50) default NULL,
  `completion_threshold` varchar(50) default NULL,
  `progress_measure` varchar(50) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `cmi_interactions` (
  `Course_ID` int(10) unsigned NOT NULL default '0',
  `SCO_ID` varchar(50) NOT NULL default '',
  `User_ID` varchar(50) NOT NULL default '',
  `n` int(11) default NULL,
  `id` varchar(50) default NULL,
  `timestamp` varchar(50) default NULL,
  `type` varchar(50) default NULL,
  `weighting` varchar(50) default NULL,
  `learner_response` text,
  `result` varchar(50) default NULL,
  `latency` varchar(50) default NULL,
  `description` text,
  `objectives_count` int(11) default NULL,
  `correct_rese_count` int(11) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `cmi_interactions_correct_response` (
  `Course_ID` int(10) unsigned NOT NULL default '0',
  `SCO_ID` varchar(50) NOT NULL default '',
  `User_ID` varchar(50) NOT NULL default '',
  `n` int(11) NOT NULL default '0',
  `id` varchar(50) NOT NULL default '',
  `m` int(11) NOT NULL default '0',
  `pattern` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `cmi_interactions_objectives` (
  `Course_ID` int(10) unsigned NOT NULL default '0',
  `SCO_ID` varchar(50) NOT NULL default '',
  `User_ID` varchar(50) NOT NULL default '',
  `n` int(11) NOT NULL default '0',
  `id` varchar(50) NOT NULL default '',
  `c` int(11) NOT NULL default '0',
  `objectives_id` varchar(50) NOT NULL default ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `cmi_objectives` (
  `Course_ID` int(10) unsigned NOT NULL default '0',
  `SCO_ID` varchar(50) NOT NULL default '',
  `User_ID` varchar(50) NOT NULL default '',
  `n` int(11) NOT NULL default '0',
  `id` varchar(50) default NULL,
  `score_raw` int(11) default NULL,
  `score_min` int(11) default NULL,
  `score_max` int(11) default NULL,
  `status` varchar(50) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `cmi_student_preference` (
  `Course_ID` int(10) unsigned NOT NULL default '0',
  `SCO_ID` varchar(50) NOT NULL default '',
  `User_ID` varchar(50) NOT NULL default '',
  `audio` varchar(50) default NULL,
  `language` text,
  `speed` varchar(50) default NULL,
  `text` varchar(50) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `global_objectives` (
  `Course_ID` int(10) unsigned NOT NULL default '0',
  `SCO_ID` varchar(50) NOT NULL default '',
  `User_ID` varchar(50) NOT NULL default '',
  `id` varchar(50) NOT NULL default '',
  `global_to_system` varchar(50) NOT NULL default '',
  `ProgressStatus` varchar(50) default NULL,
  `SatisfiedStatus` varchar(50) default NULL,
  `MeasureStatus` varchar(50) default NULL,
  `NormalizedMeasure` varchar(50) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `sequencing_random_result` (
  `Course_ID` int(10) unsigned default NULL,
  `SCO_ID` varchar(50) default NULL,
  `User_ID` varchar(50) default NULL,
  `Random_Child_ID` varchar(50) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40000 ALTER TABLE `WM_user_account` DISABLE KEYS */;
INSERT INTO `WM_user_account` (`username`, `password`, `enable`, `first_name`, `last_name`, `gender`, `birthday`, `personal_id`, `email`, `homepage`, `home_tel`, `home_fax`, `home_address`, `office_tel`, `office_fax`, `office_address`, `cell_phone`, `company`, `department`, `title`, `language`, `theme`, `msg_reserved`, `hid`) VALUES 
    ('root','0353dd078b9eff95734218d252698ec9','Y','管理員','系統','M','1970-01-01','','root@localhost','','07-1234123','','','','','','','','','','Big5','default',0,130968);
/*!40000 ALTER TABLE `WM_user_account` ENABLE KEYS */;
/*!40000 ALTER TABLE `WM_acl_bindfile` DISABLE KEYS */;
INSERT INTO `WM_acl_bindfile` (`function_id`, `binding_file`) VALUES 
    (100100100,'/academic/dbcs/config.inc.php'),
    (100200100,'/academic/sys/sysop.php'),
    (100200100,'/academic/sys/sysop_get.php'),
    (100200200,'/academic/sys/sysop_save.php'),
    (100200300,'/academic/sys/sysop_del.php'),
    (100200300,'/academic/sys/sysop_del1.php'),
    (100300300,'/academic/index.php'),
    (100300500,'/academic/goto_school.php'),
    (500100100,'/academic/mailtogroup/course_group_mail.php'),
    (500100100,'/academic/mailtogroup/course_group_mail1.php'),
    (500100100,'/academic/mailtogroup/course_group_tree.php'),
    (500100100,'/academic/mailtogroup/course_set.php'),
    (600100100,'/index.php'),
    (600100100,'/login.php'),
    (600200100,'/academic/login.php'),
    (600200100,'/relogin.php'),
    (600200100,'/teach_relogin.php'),
    (700300100,'/academic/course/course_group_save.php'),
    (700300200,'/academic/course/course_priority.php'),
    (700300200,'/academic/course/course_save.php'),
    (700300400,'/academic/course/course_group.php'),
    (700300400,'/academic/course/course_group_get.php'),
    (700300400,'/academic/course/course_group_tools.php'),
    (700300400,'/academic/course/course_group_tree.php'),
    (700300400,'/academic/course/course_query.php'),
    (700400100,'/academic/course/course_delete.php'),
    (700400200,'/academic/course/course_set.php'),
    (700400300,'/academic/course/course_get.php'),
    (700400300,'/academic/course/course_tree.php'),
    (700400300,'/academic/course/sysbar.php'),
    (700400300,'/academic/course/sysbar_func.php'),
    (700400300,'/academic/course/sysbar_tools.php'),
    (700400300,'/academic/course/teach_sysbar.php'),
    (700500100,'/academic/course/content_save.php'),
    (700500300,'/academic/course/content_delete.php'),
    (700500400,'/academic/course/content_priority.php'),
    (700500500,'/academic/course/content_list.php'),
    (700500500,'/academic/course/content_query.php'),
    (1200100100,'/academic/explorer/getallfolder.php'),
    (1200100100,'/academic/explorer/index.php'),
    (1300500100,'/academic/course/class_sysbar.php'),
    (2400100100,'/academic/class/class_group_save.php'),
    (2400100300,'/academic/class/class_del.php'),
    (2400100400,'/academic/class/class_get.php'),
    (2400100400,'/academic/class/class_group.php'),
    (2400100400,'/academic/class/class_group_get.php'),
    (2400100400,'/academic/class/class_group_tools.php'),
    (2400100400,'/academic/class/class_group_tree.php'),
    (2400100400,'/academic/class/class_group_tree2.php'),
    (2400100400,'/academic/class/class_query.php'),
    (2400100400,'/academic/class/class_tree.php'),
    (2400100500,'/academic/class/class_get_grade.php'),
    (2400100500,'/academic/class/view_grade.php'),
    (2400300100,'/academic/class/attach_people.php'),
    (2400300200,'/academic/class/remove_people.php'),
    (2400300300,'/academic/class/move_member.php'),
    (2400300400,'/academic/class/switch_status.php'),
    (2400300500,'/academic/class/send_class_grade_mail.php'),
    (2400300500,'/academic/class/send_class_grade_mail1.php'),
    (2400300500,'/academic/class/send_class_mail.php'),
    (2400300500,'/academic/class/send_class_mail1.php'),
    (2400300500,'/academic/class/send_detail_grade.php'),
    (2400300600,'/academic/class/detail_grade.php'),
    (2400300600,'/academic/class/learn_result.php'),
    (2400300600,'/academic/class/people_query_grade.php'),
    (2400300700,'/academic/class/course_record.php'),
    (2400300900,'/academic/class/people_manager.php'),
    (2400300900,'/academic/class/people_query.php'),
    (2400300900,'/academic/class/showpic.php'),
    (2400300900,'/academic/class/stud_info.php'),
    (2400300900,'/academic/class/stud_info1.php'),
    (2400300900,'/academic/class/user_pic.php');
/*!40000 ALTER TABLE `WM_acl_bindfile` ENABLE KEYS */;
/*!40000 ALTER TABLE `WM_acl_function` DISABLE KEYS */;
INSERT INTO `WM_acl_function` (`function_id`, `caption`, `scope`, `default_permission`) VALUES 
    (100100100,'系統/學校管理→資料庫管理→瀏覽資料庫','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (100200100,'系統/學校管理→系統權限設定→新增身分','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (100200200,'系統/學校管理→系統權限設定→修改身分','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (100200300,'系統/學校管理→系統權限設定→刪除身分','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (100300100,'系統/學校管理→增刪學校→新增學校','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (100300200,'系統/學校管理→增刪學校→修改學校資料','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (100300300,'系統/學校管理→增刪學校→閱讀學校資料','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (100300400,'系統/學校管理→增刪學校→刪除學校','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (100300500,'系統/學校管理→增刪學校→切換學校','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (100400100,'系統/學校管理→管理者帳號設定→新增管理者','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (100400200,'系統/學校管理→管理者帳號設定→修改管理者','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (100400300,'系統/學校管理→管理者帳號設定→刪除管理者','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (100500100,'進入管理處','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (300100100,'教師管理→教師/助教管理→新增教師','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (300100200,'教師管理→教師/助教管理→刪除教師','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (300100300,'教師管理→教師/助教管理→切換教師/助教身分','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (300100400,'教師管理→教師/助教管理→新增助教','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (300100500,'教師管理→教師/助教管理→刪除助教','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (300100600,'教師管理→教師/助教管理→查詢校內教師及助教','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (400100100,'帳號模組→設定註冊模式→修改註冊模式','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (400200100,'帳號模組→註冊新帳號→使用者自行申請帳號','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (400300100,'帳號模組→帳號的新增與刪除→新增個別帳號','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (400300200,'帳號模組→帳號的新增與刪除→刪除個別帳號','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (400300300,'帳號模組→帳號的新增與刪除→新增連續帳號','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (400300400,'帳號模組→帳號的新增與刪除→刪除連續帳號','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (400300500,'帳號模組→帳號的新增與刪除→匯入帳號後新增','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (400300600,'帳號模組→帳號的新增與刪除→匯入帳號後刪除','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (400300700,'帳號模組→帳號的新增與刪除→審核申請者帳號','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (400400100,'帳號模組→使用者資料管理→查詢使用者資料','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (400400200,'帳號模組→使用者資料管理→匯出使用者資料','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (400400300,'帳號模組→使用者資料管理→修改使用者資料','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (400400400,'帳號模組→審核已註冊的帳號','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (400400500,'帳號模組→使用者資料管理→修改個人資料','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (400500100,'帳號模組→同學資訊→閱讀同學資訊','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (400500200,'帳號模組→同學資訊→匯出學員資料','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (500100100,'群組寄信→管理者寄給群組→管理者寄給群組','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (500200200,'群組寄信→寄給助教→寄給助教','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (500300300,'群組寄信→教師點名與寄信→教師點名與寄信','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (600100100,'登入→首頁登入→登入','','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (600200100,'登入→變換身分登入→登入','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (600300100,'登入→設定登入限制(校門管制)→設定檔除帳號及ip','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (700100100,'課程管理→設定開課模式→修改開課模式','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (700200100,'課程管理→申請開課→填寫開課申請書','','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (700300100,'課程管理→課程群組管理→新增課程群組','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (700300200,'課程管理→課程群組管理→修改課程群組','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (700300300,'課程管理→課程群組管理→刪除課程群組','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (700300400,'課程管理→課程群組管理→瀏覽課程群組','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (700400100,'課程管理→課程管理→新增/刪除課程','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (700400200,'課程管理→課程管理→修改課程基本資料(管理者)','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (700400300,'課程管理→課程管理→瀏覽課程資料','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (700400400,'課程管理→課程管理→設定課程與群組關聯','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (700400500,'課程管理→課程管理→包裝課程','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (700400600,'課程管理→課程管理→安裝課程','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (700400700,'課程管理→課程管理→修改課程基本資料(教師)','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (700500100,'課程管理→全校教材管理→新增教材','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (700500200,'課程管理→全校教材管理→修改教材','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (700500300,'課程管理→全校教材管理→刪除教材','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (700500400,'課程管理→全校教材管理→設定教材與課程關聯','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (700600100,'課程管理→課程教材管理→上傳教材檔案','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (700600200,'課程管理→課程教材管理→下載教材檔案','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (700600300,'課程管理→課程教材管理→設定教材分享','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (700700100,'課程管理→切換教室/辦公室→切換教室/辦公室','','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (800100100,'課程介紹→課程簡介→編輯課程簡介','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (800100200,'課程介紹→課程簡介→上傳課程簡介','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (800100300,'課程介紹→課程簡介→瀏覽課程簡介','','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (800200100,'課程介紹→課程安排→編輯課程安排','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (800200200,'課程介紹→課程安排→上傳課程安排','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (800200300,'課程介紹→課程安排→瀏覽課程安排','','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (800300100,'課程介紹→教師介紹→編輯教師介紹','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (800300200,'課程介紹→教師介紹→上傳教師介紹','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (800300300,'課程介紹→教師介紹→瀏覽教師介紹','','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (900100100,'討論版→討論版進階設定→新增討論版','teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (900100200,'討論版→討論版進階設定→刪除討論版','teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (900100300,'討論版→討論版進階設定→討論版主旨','teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (900100400,'討論版→討論版進階設定→討論版mail follow','teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (900100500,'討論版→討論版進階設定→討論版名稱','teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (900100600,'討論版→討論版進階設定→討論版ACL設定','teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (900100700,'討論版→討論版進階設定→匯出討論版文章','','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (900100800,'討論版→討論版進階設定→匯入討論版文章','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (900100900,'討論版→討論版進階設定→討論版期限設定','teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (900200100,'討論版→使用討論版→閱讀文章','','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (900200200,'討論版→使用討論版→搜尋文章','','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (900200300,'討論版→使用討論版→訂閱文章','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (900200400,'討論版→使用討論版→轉寄文章','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (900200500,'討論版→使用討論版→張貼文章','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (900200600,'討論版→使用討論版→修改文章','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (900200700,'討論版→使用討論版→刪除文章','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (900200800,'討論版→使用討論版→上傳夾檔','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (900200900,'討論版→使用討論版→收入精華區','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (900201000,'討論版→使用討論版→收入筆記本','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (900201100,'討論版→使用討論版→整批作業(刪除、搬移…)','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (900300100,'討論版→精華區管理與使用→新增目錄','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (900300200,'討論版→精華區管理與使用→刪除目錄','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (900300300,'討論版→精華區管理與使用→修改目錄','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (900300400,'討論版→精華區管理與使用→張貼文章','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (900300500,'討論版→精華區管理與使用→修改文章','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (900300600,'討論版→精華區管理與使用→刪除文章','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (900300700,'討論版→精華區管理與使用→搬移文章','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (900300800,'討論版→精華區管理與使用→轉寄文章','','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (900300900,'討論版→精華區管理與使用→閱讀文章','','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (900301000,'討論版→精華區管理與使用→整批作業','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1000100100,'學員分組→組次管理→新增組次','teach,direct','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1000100200,'學員分組→組次管理→修改組次','teach,direct','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1000100300,'學員分組→組次管理→刪除組次','teach,direct','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1000200100,'學員分組→設定各組的ACL→設定各組的ACL','teach,direct','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1000300100,'學員分組→分組管理→新增小組','teach,direct','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1000300200,'學員分組→分組管理→修改小組','teach,direct','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1000300300,'學員分組→分組管理→刪除小組','teach,direct','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1000300400,'學員分組→分組管理→設定組員','teach,direct','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1000300500,'學員分組→分組管理→設定組長','teach,direct','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1000400100,'學員分組→參與組內討論→閱讀內容','learn,teach','enable,visible,readable'),
    (1000400200,'學員分組→參與組內討論→參與討論','learn,teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1000400300,'學員分組→參與組內討論→小組督導','learn,teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1100100100,'選課→設定選課模式→設定選課模式','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1100200100,'選課→自行加退選→使用者自選課程','','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1100200200,'選課→自行加退選→使用者自行退課','','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1100300100,'選課→主管指派→指定修課人員','direct','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1100300200,'選課→主管指派→指定退選人員','direct','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1100400100,'選課→授課教師增刪學員→新增單不規則學員帳號','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1100400200,'選課→授課教師增刪學員→刪除不規則學員帳號','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1100400300,'選課→授課教師增刪學員→新增連續學員帳號','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1100400400,'選課→授課教師增刪學員→刪除連續的學員帳號','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1100400500,'選課→授課教師增刪學員→新增匯入的學員帳號','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1100400600,'選課→授課教師增刪學員→刪除匯入的學員帳號','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1100500100,'選課→審核加退選人員→審核加選人員','teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1100500200,'選課→審核加退選人員→審核退選人員','teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1200100100,'檔案管理→目錄管理→瀏覽目錄','teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1200100200,'檔案管理→目錄管理→新增目錄','teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1200100300,'檔案管理→目錄管理→修改目錄','teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1200100400,'檔案管理→目錄管理→刪除目錄','teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1200200100,'檔案管理→檔案管理→瀏覽檔案','teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1200200200,'檔案管理→檔案管理→上傳檔案','teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1200200300,'檔案管理→檔案管理→下載檔案','','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1200200400,'檔案管理→檔案管理→編輯檔案','teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1200200500,'檔案管理→檔案管理→搬移檔案','teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1200200600,'檔案管理→檔案管理→刪除檔案','teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1200200700,'檔案管理→檔案管理→修改檔案名稱','teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1300100100,'介面設定→首頁版面設定→首頁版面設定','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1300200100,'介面設定→教室版面設定→教室版面設定','teach,direct','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1300300100,'介面設定→管理者功能列設定→瀏覽功能選項','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1300300200,'介面設定→管理者功能列設定→使用功能選項','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1300300300,'介面設定→管理者功能列設定→新增功能選項','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1300300400,'介面設定→管理者功能列設定→修改功能選項','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1300300500,'介面設定→管理者功能列設定→刪除功能選項','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1300400100,'介面設定→教師環境功能列設定→瀏覽功能選項','teach,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1300400200,'介面設定→教師環境功能列設定→使用功能選項','teach,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1300400300,'介面設定→教師環境功能列設定→新增功能選項','teach,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1300400400,'介面設定→教師環境功能列設定→修改功能選項','teach,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1300400500,'介面設定→教師環境功能列設定→刪除功能選項','teach,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1300500100,'介面設定→教室功能列設定→瀏覽功能選項','direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1300500200,'介面設定→教室功能列設定→使用功能選項','direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1300500300,'介面設定→教室功能列設定→新增功能選項','direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1300500400,'介面設定→教室功能列設定→修改功能選項','direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1300500500,'介面設定→教室功能列設定→刪除功能選項','direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1400100100,'成績管理→成績項目管理→新增成績項目','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1400100200,'成績管理→成績項目管理→修改成績項目','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1400100300,'成績管理→成績項目管理→刪除成績項目','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1400200100,'成績管理→成績管理→查詢/瀏覽成績','learn,teach,direct','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1400200200,'成績管理→成績管理→上傳成績','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1400200300,'成績管理→成績管理→修改成績','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1400200400,'成績管理→成績管理→公佈(寄送)成績','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1400300100,'成績管理→成績資訊→閱讀成績資訊','learn,teach,direct','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1500100100,'學習記錄→全校人員學習記錄→全校人員記錄查詢','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1500200100,'學習記錄→學員檔案資料(學員管理)→閱讀學員統計資料','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1500300100,'學習記錄→修課排行→閱讀修課排行','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1500400100,'學習記錄→個人學習記錄→閱讀個人學習記錄','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1500500100,'學習記錄→班級成員管理→到課統計','direct','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1600100100,'考試→題庫管理→閱讀/搜尋題目','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1600100200,'考試→題庫管理→新增題目','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1600100300,'考試→題庫管理→修改題目','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1600100400,'考試→題庫管理→刪除題目','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1600100500,'考試→題庫管理→匯入試題','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1600100600,'考試→題庫管理→匯出試題','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1600100700,'考試→題庫管理→上傳檔案','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1600100800,'考試→題庫管理→分享題庫','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1600200100,'考試→試卷管理→新增考卷','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1600200200,'考試→試卷管理→修改考卷','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1600200300,'考試→試卷管理→刪除考卷','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1600200400,'考試→試卷管理→瀏覽作答統計','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1600300100,'考試→批改作業→批改考卷','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1600300200,'考試→批改作業→上傳檔案','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1600400100,'考試→填寫考卷→瀏覽考卷','learn','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1600400200,'考試→填寫考卷→填寫考卷','learn','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1600400300,'考試→填寫考卷→瀏覽個人考卷結果','learn','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1700100100,'作業→題庫管理→閱讀/搜尋題目','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1700100200,'作業→題庫管理→新增題目','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1700100300,'作業→題庫管理→修改題目','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1700100400,'作業→題庫管理→刪除題目','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1700100500,'作業→題庫管理→匯入試題','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1700100600,'作業→題庫管理→匯出試題','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1700100700,'作業→題庫管理→上傳檔案','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1700100800,'作業→題庫管理→分享題庫','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1700200100,'作業→作業管理→新增作業','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1700200200,'作業→作業管理→修改作業','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1700200300,'作業→作業管理→刪除作業','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1700300100,'作業→批改作業→批改作業','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1700300200,'作業→批改作業→上傳檔案','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1700400100,'作業→繳交作業→瀏覽老師出的作業','learn','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1700400200,'作業→繳交作業→繳交作業','learn','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1700400300,'作業→繳交作業→瀏覽已繳交的作業','learn','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1800100100,'問卷→題庫管理→閱讀/搜尋題目','teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1800100200,'問卷→題庫管理→新增題目','teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1800100300,'問卷→題庫管理→修改題目','teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1800100400,'問卷→題庫管理→刪除題目','teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1800200100,'問卷→問卷管理→新增問卷','teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1800200200,'問卷→問卷管理→修改問卷','teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1800200300,'問卷→問卷管理→刪除問卷','teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1800300100,'問卷→填寫問卷→瀏覽問卷','learn','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1800300200,'問卷→填寫問卷→填寫問卷','learn','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1800300300,'問卷→填寫問卷→瀏覽問卷結果','learn','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1800300400,'問卷→填寫問卷→瀏覽問卷統計','learn','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1900100100,'學習路徑→學習路徑管理→新增節點','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1900100200,'學習路徑→學習路徑管理→修改節點','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1900100300,'學習路徑→學習路徑管理→刪除節點','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (1900200100,'學習路徑→學習路徑閱讀→閱讀學習路徑','learn','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2000100100,'討論室→討論室管理→新增討論室','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2000100200,'討論室→討論室管理→刪除討論室','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2000100300,'討論室→討論室管理→修改討論室','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2000100400,'討論室→討論室管理→設定討論室主持人','learn,teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2000100500,'討論室→討論室管理→設定討論室語音','learn,teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2000100600,'觀看影音討論的錄影檔','teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2000200100,'討論室→參與討論→瀏覽討論室內容','learn,teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2000200200,'討論室→參與討論→發言','learn,teach','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2100100100,'即時傳訊→一對一傳訊→發送訊息','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2100100200,'即時傳訊→一對一傳訊→接收/閱讀訊息','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2100100300,'即時傳訊→一對一傳訊→設定勿打擾','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2100200100,'即時傳訊→一對多傳訊→發送訊息','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2100300100,'即時傳訊→歷史訊息→閱讀歷史訊息','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2100300200,'即時傳訊→歷史訊息→轉貼歷史訊息','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2200100100,'訊息中心→資料夾管理→新增資料夾','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2200100200,'訊息中心→資料夾管理→修改資料夾','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2200100300,'訊息中心→資料夾管理→刪除資料夾','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2200200100,'訊息中心→訊息管理→發送訊息','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2200200200,'訊息中心→訊息管理→回覆訊息','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2200200300,'訊息中心→訊息管理→轉寄訊息','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2200200400,'訊息中心→訊息管理→刪除訊息','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2200200500,'訊息中心→訊息管理→上傳夾檔','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2300100100,'行事曆→個人行事曆→新增事件','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2300100200,'行事曆→個人行事曆→修改事件','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2300100300,'行事曆→個人行事曆→刪除事件','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2300100400,'行事曆→個人行事曆→瀏覽行事曆','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2300200100,'行事曆→課程行事曆→新增事件','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2300200200,'行事曆→課程行事曆→修改事件','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2300200300,'行事曆→課程行事曆→刪除事件','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2300200400,'行事曆→課程行事曆→瀏覽行事曆','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2300300100,'行事曆→全校行事曆→新增事件','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2300300200,'行事曆→全校行事曆→修改事件','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2300300300,'行事曆→全校行事曆→刪除事件','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2300300400,'行事曆→全校行事曆→瀏覽行事曆','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2400100100,'班級/部門管理→班級/部門管理→新增班級/部門','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2400100200,'班級/部門管理→班級/部門管理→修改/班級部門','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2400100300,'班級/部門管理→班級/部門管理→刪除班級/部門','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2400200100,'班級/部門管理→設定導師/主管→新增導師/主管','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2400200200,'班級/部門管理→設定導師/主管→修改導師/主管','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2400200300,'班級/部門管理→設定導師/主管→刪除導師/主管','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2400300100,'班級/部門管理→班級/部門人員管理→加入班級/部門人員','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2400300200,'班級/部門管理→班級/部門人員管理→移除班級/部門人員','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2400300300,'班級/部門管理→班級/部門人員管理→調動班級/部門人員','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2400300400,'班級/部門管理→班級/部門人員管理→變換人員身份','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2400300500,'班級/部門管理→班級/部門人員管理→寄信給部門人員','direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2400400100,'進入導師辦公室','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2400500100,'班級/部門管理→匯入班級/部門人員→匯入班級/部門人員','academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2400600100,'導師環境→成員管理→設定助理','direct','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2500100100,'MySchool→個人課程→使用個人課程','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2500100200,'進入教室','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2500200100,'MySchool→教授課程→使用教授課程','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2500200200,'進入教師辦公室','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2500300100,'MySchool→全校課程→瀏覽全校課程','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2500400100,'MySchool→我的最愛→新增資料夾','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2500400200,'MySchool→我的最愛→新增我的最愛課程','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2500400300,'MySchool→我的最愛→瀏覽我的最愛','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2500500100,'MySchool→我的作業→瀏覽我的作業','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2500600100,'MySchool→我的考試→瀏覽我的考試','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2500700100,'MySchool→我的新聞→瀏覽我的新聞','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2600100100,'筆記本→資料夾管理→新增資料夾','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2600100200,'筆記本→資料夾管理→修改資料夾','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2600100300,'筆記本→資料夾管理→刪除資料夾','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2600200100,'筆記本→筆記管理→寫筆記','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2600200200,'筆記本→筆記管理→修改筆記','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2600200300,'筆記本→筆記管理→刪除筆記','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2600200400,'筆記本→筆記管理→上傳夾檔','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2600200500,'筆記本→筆記管理→搬移筆記','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable'),
    (2600200600,'筆記本→筆記管理→寄出筆記','learn,teach,direct,academic','enable,visible,readable,writable,modifiable,uploadable,removable');
/*!40000 ALTER TABLE `WM_acl_function` ENABLE KEYS */;
/*!40000 ALTER TABLE `WM_review_syscont` DISABLE KEYS */;
INSERT INTO `WM_review_syscont` (`flow_serial`, `kind`, `start`, `title`, `content`, `permute`) VALUES 
    (NULL,'course','','a:5:{s:4:\"Big5\";s:15:\"不需要審核\";s:6:\"GB2312\";s:15:\"不需要審核\";s:2:\"en\";s:19:\"Review not required\";s:6:\"EUC-JP\";s:0:\"\";s:11:\"user_define\";s:0:\"\";}','    <flow>\n        <activity id=\"WM_START\" type=\"to\" status=\"none\">\n            <description></description>\n            <to account=\"\" email=\"\">\n                <agent account=\"\" email=\"\"></agent>\n                <feedback param=\"\">\n                    <param value=\"ok\" activity=\"\"></param>\n                    <param value=\"deny\" activity=\"\"></param>\n                </feedback>\n                <comment type=\"\"></comment>\n                <arrive_time></arrive_time>\n                <receive_time></receive_time>\n                <decide_time></decide_time>\n            </to>\n        </activity>\n    </flow>',0),
    (NULL,'course','','a:5:{s:4:\"Big5\";s:12:\"教師審核\";s:6:\"GB2312\";s:12:\"教师审核\";s:2:\"en\";s:17:\"Instructor review\";s:6:\"EUC-JP\";s:0:\"\";s:11:\"user_define\";s:0:\"\";}','    <flow>\n        <activity id=\"WM_START\" type=\"to\" status=\"none\">\n            <description></description>\n            <to account=\"#teacher\" email=\"\">\n                <agent account=\"\" email=\"\"></agent>\n                <feedback param=\"\">\n                    <param value=\"ok\" activity=\"\"></param>\n                    <param value=\"deny\" activity=\"\"></param>\n                </feedback>\n                <comment type=\"\"></comment>\n                <arrive_time></arrive_time>\n                <receive_time></receive_time>\n                <decide_time></decide_time>\n            </to>\n        </activity>\n    </flow>',1),
    (NULL,'course','','a:5:{s:4:\"Big5\";s:20:\"導師(主管)審核\";s:6:\"GB2312\";s:20:\"导师(主管)审核\";s:2:\"en\";s:17:\"Superviser review\";s:6:\"EUC-JP\";s:0:\"\";s:11:\"user_define\";s:0:\"\";}','    <flow>\n        <activity id=\"WM_START\" type=\"to\" status=\"none\">\n            <description></description>\n            <to account=\"#director\" email=\"\">\n                <agent account=\"\" email=\"\"></agent>\n                <feedback param=\"\">\n                    <param value=\"ok\" activity=\"\"></param>\n                    <param value=\"deny\" activity=\"\"></param>\n                </feedback>\n                <comment type=\"\"></comment>\n                <arrive_time></arrive_time>\n                <receive_time></receive_time>\n                <decide_time></decide_time>\n            </to>\n        </activity>\n    </flow>',2),
    (NULL,'course','','a:5:{s:4:\"Big5\";s:21:\"一般管理者審核\";s:6:\"GB2312\";s:21:\"一般管理者审核\";s:2:\"en\";s:20:\"General Admin review\";s:6:\"EUC-JP\";s:0:\"\";s:11:\"user_define\";s:0:\"\";}','    <flow>\n        <activity id=\"WM_START\" type=\"to\" status=\"none\">\n            <description></description>\n            <to account=\"#manager\" email=\"\">\n                <agent account=\"\" email=\"\"></agent>\n                <feedback param=\"\">\n                    <param value=\"ok\" activity=\"\"></param>\n                    <param value=\"deny\" activity=\"\"></param>\n                </feedback>\n                <comment type=\"\"></comment>\n                <arrive_time></arrive_time>\n                <receive_time></receive_time>\n                <decide_time></decide_time>\n            </to>\n        </activity>\n    </flow>',3);
/*!40000 ALTER TABLE `WM_review_syscont` ENABLE KEYS */;
INSERT INTO WM_bbs_boards (board_id,bname,owner_id) VALUES (1000000001, 'a:5:{s:4:"Big5";s:15:"系統建議板";s:6:"GB2312";s:15:"系统建议板";s:2:"en";s:17:"System suggestion";s:6:"EUC-JP";s:0:"";s:11:"user_define";s:0:"";}', 10001);
INSERT INTO WM_news_subject (news_id, board_id, type, visibility) VALUES (1, 1000000001, 'suggest', 'visible');
INSERT INTO WM_term_subject (course_id,board_id) VALUES (10001, 1000000001);

GRANT SELECT, INSERT, UPDATE, DELETE, INDEX, ALTER, CREATE,CREATE VIEW,DROP,TRIGGER on `WM\_%`.*  TO wm3@localhost IDENTIFIED BY 'WmIiI';

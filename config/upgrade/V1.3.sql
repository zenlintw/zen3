/* #1 一般短期課 WM_term_course 新增 path_type 欄位，儲存學習節點類型 */
ALTER TABLE  `WM_term_course` ADD `path_type` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' 
COMMENT  '儲存學習節點類型，0:未設定；1:自訂課程；2:短期課程；3:一般課程' AFTER  `ta_can_sets` ;

/* #2 一般短期課 WM_term_course 新增 exam_num 欄位，儲存自我評量題數 */
ALTER TABLE  `WM_term_course` ADD `exam_num`  INT(5) UNSIGNED NOT NULL DEFAULT '0' 
COMMENT  '儲存自我評量題數，0:不啟用；1-n: 顯示幾題' AFTER  `path_type`;

/* #3 學習快通車-重點的筆記 */
/* DROP TABLE IF EXISTS `WM_user_note`; */
CREATE TABLE `WM_user_note` (
  `note_id` 	bigint(20)      unsigned NOT NULL AUTO_INCREMENT	COMMENT '筆記ID',
  `username` 	varchar(32) 	CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '帳號',  
  `course_id` 	int(10)         unsigned NOT NULL DEFAULT '0'		COMMENT '課程ID',
  `course_name` varchar(255) 	NOT NULL DEFAULT ''                 COMMENT '課程名稱',
  `sco_id`      varchar(255) 	NOT NULL DEFAULT ''                 COMMENT 'SCORM ID',
  `title`       varchar(255) 	NOT NULL DEFAULT ''                 COMMENT '筆記標題',
  `url`         varchar(255) 	NOT NULL DEFAULT ''                 COMMENT '素材網址',
  `point_time` 	int(11)         NOT NULL DEFAULT '0'                COMMENT '重點記錄時間',
  `image_name`	text                                                COMMENT '圖片名稱',
  `asset_id`	varchar(255)	NOT NULL DEFAULT ''                 COMMENT '素材ID',
  `memo`        text            NOT NULL DEFAULT ''                 COMMENT '筆記',
  `create_time`	datetime        NOT NULL DEFAULT '0000-00-00 00:00:00'	COMMENT '建立時間',
  `update_time`	datetime        NOT NULL DEFAULT '0000-00-00 00:00:00'	COMMENT '更新時間',
  `review_cnt`	int(5)          unsigned DEFAULT '0'                COMMENT '複習次數',
  PRIMARY KEY (`note_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='重點的筆記';

/* #4 學習快通車-張貼重點筆記至討論區，關聯表 */
/* DROP TABLE IF EXISTS `WM_user_note_post`; */
CREATE TABLE `WM_user_note_post` (
  `note_id`     int(10)         unsigned NOT NULL DEFAULT '0'		COMMENT '筆記ID',
  `board_id` 	bigint(20)      unsigned NOT NULL DEFAULT '0',
  `node`        varchar(19) 	NOT NULL DEFAULT '',
  `site`        int(10)         unsigned NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='張貼重點筆記';

/* #6 課程設定 */
ALTER TABLE `WM_term_course` ADD `course_type` varchar(10) DEFAULT NULL COMMENT '課程類型';
ALTER TABLE `WM_term_course` ADD `subhead` varchar(150) DEFAULT NULL COMMENT '副標題';
ALTER TABLE `WM_term_course` ADD `fee` int(10) DEFAULT NULL COMMENT '費用';
ALTER TABLE `WM_term_course` ADD `st_limit` tinyint(1) DEFAULT NULL COMMENT '非輔導期間是否對學員開放';
ALTER TABLE `WM_term_course` ADD `goal` text COMMENT '目標';
ALTER TABLE `WM_term_course` ADD `audience` text COMMENT '聽眾';
ALTER TABLE `WM_term_course` ADD `formal_pass` text COMMENT '一般生通過條件';
ALTER TABLE `WM_term_course` ADD `gallery_pass` text COMMENT '見習生通過條件';
ALTER TABLE `WM_term_course` ADD `is_use` text COMMENT '目標與對象是否啟用設定';
ALTER TABLE `WM_term_course` ADD `allow_comment` int(1) DEFAULT '0' COMMENT '是否開放評論區';
ALTER TABLE `WM_term_course` ADD `ref_title` text COMMENT '參考資料標題';
ALTER TABLE `WM_term_course` ADD `ref_url` text COMMENT '參考資料網址';

USE `WM_MASTER`;

CREATE TABLE `CO_all_course` (
  `course_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `content_id` int(10) unsigned DEFAULT NULL,
  `caption` text NOT NULL,
  `teacher` varchar(128) DEFAULT NULL,
  `kind` enum('group','course') DEFAULT 'course',
  `en_begin` date DEFAULT NULL,
  `en_end` date DEFAULT NULL,
  `st_begin` date DEFAULT NULL,
  `st_end` date DEFAULT NULL,
  `status` tinyint(3) unsigned DEFAULT '0',
  `texts` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `content` text,
  `credit` tinyint(3) unsigned DEFAULT NULL,
  `discuss` int(10) unsigned DEFAULT NULL,
  `bulletin` bigint(20) unsigned DEFAULT NULL,
  `n_limit` smallint(6) DEFAULT '0',
  `a_limit` smallint(6) DEFAULT '0',
  `quota_used` int(10) unsigned NOT NULL DEFAULT '0',
  `quota_limit` int(10) unsigned NOT NULL DEFAULT '102400',
  `path` varchar(128) NOT NULL DEFAULT '',
  `login_times` int(10) unsigned NOT NULL DEFAULT '0',
  `post_times` int(10) unsigned NOT NULL DEFAULT '0',
  `dsc_times` int(10) unsigned NOT NULL DEFAULT '0',
  `fair_grade` int(6) unsigned DEFAULT '60',
  `ta_can_sets` set('content_id','caption','en_begin','en_end','st_begin','st_end','status','texts','url','content','n_limit','a_limit','fair_grade','review','cparent') NOT NULL DEFAULT 'caption,st_begin,st_end,status,texts,url,content,n_limit,a_limit,fair_grade',
  `path_type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '儲存學習節點類型，0:未設定；1:自訂課程；2:短期課程；3:一般課程',
  `exam_num` int(5) unsigned NOT NULL DEFAULT '0' COMMENT '儲存自我評量題數，0:不啟用；1-n: 顯示幾題',
  `course_type` varchar(10) DEFAULT NULL COMMENT '課程類型',
  `subhead` varchar(150) DEFAULT NULL COMMENT '副標題',
  `fee` int(10) DEFAULT NULL COMMENT '費用',
  `st_limit` tinyint(1) DEFAULT NULL COMMENT '非輔導期間是否對學員開放',
  `goal` text COMMENT '目標',
  `audience` text COMMENT '聽眾',
  `formal_pass` text COMMENT '一般生通過條件',
  `gallery_pass` text COMMENT '見習生通過條件',
  `is_use` text COMMENT '目標與對象是否啟用設定',
  `allow_comment` int(1) DEFAULT '0' COMMENT '是否開放評論區',
  `ref_title` text COMMENT '參考資料標題',
  `ref_url` text COMMENT '參考資料網址',
  `school` int(10) NOT NULL,
  PRIMARY KEY (`course_id`,`school`),
  KEY `idx1` (`status`,`st_begin`,`st_end`),
  KEY `idx2` (`kind`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE `CO_all_major` (
  `username` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `course_id` int(10) unsigned NOT NULL DEFAULT '0',
  `role` int(10) unsigned NOT NULL DEFAULT '0',
  `post` mediumint(8) unsigned DEFAULT '0',
  `hw` mediumint(8) unsigned DEFAULT '0',
  `qp` mediumint(8) unsigned DEFAULT '0',
  `exam` mediumint(8) unsigned DEFAULT '0',
  `bookmark` int(10) unsigned DEFAULT '0',
  `degree` int(10) unsigned DEFAULT '0',
  `total_node` int(10) unsigned DEFAULT '0',
  `login_times` int(10) unsigned DEFAULT '0',
  `post_times` int(10) unsigned DEFAULT '0',
  `dsc_times` int(10) unsigned DEFAULT '0',
  `last_login` datetime DEFAULT NULL,
  `add_time` datetime DEFAULT NULL,
  `school` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`username`,`course_id`,`school`),
  UNIQUE KEY `idx1` (`course_id`,`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `CO_all_group` (
  `parent` int(10) unsigned NOT NULL DEFAULT '0',
  `child` int(10) unsigned NOT NULL DEFAULT '0',
  `permute` int(10) unsigned NOT NULL DEFAULT '0',
  `school` int(10) NOT NULL,
  PRIMARY KEY (`parent`,`child`,`school`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/* #7 修改個人設定預設值 */
use `WM_MASTER`;
ALTER TABLE  `CO_mooc_account` CHANGE  `gender`  `gender` ENUM(  'F',  'M',  'N' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'N';
ALTER TABLE  `WM_all_account` CHANGE  `gender`  `gender` ENUM(  'F',  'M',  'N' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'N';

use `WM_10001`;
ALTER TABLE  `WM_user_account` CHANGE  `gender`  `gender` ENUM(  'F',  'M',  'N' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'N';

/* #8 整批更新筆記本 `WM_msg_message`.`receive_time` 欄位 */
update `WM_msg_message` set `receive_time` = `submit_time` where `receive_time` is null;
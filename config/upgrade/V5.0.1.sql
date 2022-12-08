 ALTER TABLE `WM_cal_setting` CHANGE `also_show` `also_show` SET( 'course', 'person', 'school' ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL 

/* 課程路徑 */
/* 之前的版本似乎就已經有本資料表，故暫先移除 */
-- CREATE TABLE `WM_content` (
--   `content_id` int(10) unsigned NOT NULL,
--   `caption` text NOT NULL,
--   `path` varchar(128) NOT NULL DEFAULT ''
--   PRIMARY KEY (`content_id`),
--   KEY `content_id` (`content_id`)
-- ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

/* 老師編號與課程路徑對應表 */
CREATE TABLE `WM_content_ta` (
  `username` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `content_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`username`,`content_id`),
  UNIQUE KEY `idx1` (`content_id`,`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/* 補 WM_history_user_account 欄位，以利刪除帳號備份資料正確 */
ALTER TABLE `WM_history_user_account` 
    ADD `user_status` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
    ADD `education` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '學歷',
    ADD `country` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '國家';

/* 作業增加補繳期限 */
ALTER TABLE  `WM_qti_homework_test` ADD  `delay_time` DATETIME NULL DEFAULT '0000-00-00 00:00:00' COMMENT  '補繳期限' AFTER  `close_time` ;

/* 作業增加上傳附件必要選項 */
ALTER TABLE  `WM_qti_homework_test` CHANGE  `setting`  `setting` SET(  'upload',  'anonymity',  'required' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

/* 作業維護資料表，新增 create_time 欄位 */
ALTER TABLE  `WM_qti_homework_test` ADD  `create_time` DATETIME NOT NULL ;

/* 行事曆「事件類別」增加「作業逾期期限」 */
ALTER TABLE  `WM_calendar` CHANGE  `relative_type`  `relative_type` ENUM(  'course_begin',  'course_end',  'homework_begin',  'homework_end',  'exam_begin',  'exam_end',  'homework_delay' ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
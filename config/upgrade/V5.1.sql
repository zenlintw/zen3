SET NAMES utf8;
USE `WM_MASTER`;
/**
 * 記住我，保持登入
 */
CREATE TABLE `WM_persist_login` (
  `persist_idx` char(128) NOT NULL COMMENT '保持登入時的cookie值',
  `session_idx` char(32) NOT NULL,
  `username` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '帳號',
  `create_time` datetime NOT NULL COMMENT '建立日期',
  `expire_time` datetime NOT NULL COMMENT '過期日期',
  `create_ipaddress` varchar(128) NOT NULL COMMENT '建立時的ip位址',
  `user_agent` varchar(255) NOT NULL,
  PRIMARY KEY (`persist_idx`),
  KEY `username` (`username`),
  KEY `expire_time` (`expire_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='保持登入';

use `WM_10001`;

/* 修改索引名稱或增加索引以利dbms使用時辨識 */ 
ALTER TABLE `WM_log_classroom`
DROP INDEX `idx4`,
ADD INDEX `idx_WM_log_classroom_log_time` (`log_time`);

ALTER TABLE `WM_log_others`
DROP INDEX `idx4`,
ADD INDEX `idx_WM_log_others_log_time` (`log_time`);

ALTER TABLE `WM_log_manager` ADD INDEX `idx_WM_log_manager_log_time` (`log_time`);

ALTER TABLE `WM_log_teacher` ADD INDEX `idx_WM_log_teacher_log_time` (`log_time`);

/* 調整 WM_log_*.instance 欄位格式，以方便記錄log */
/* 資料表筆數巨大時，進行本指令會造成資料庫負載過大，應評估實際狀況考慮在離峰時間執行 */
 ALTER TABLE `WM_log_classroom` CHANGE `instance` `instance` VARCHAR( 20 ) NOT NULL DEFAULT '0';
 ALTER TABLE `WM_log_director` CHANGE `instance` `instance` VARCHAR( 20 ) NOT NULL DEFAULT '0';
 ALTER TABLE `WM_log_manager` CHANGE `instance` `instance` VARCHAR( 20 ) NOT NULL DEFAULT '0';
 ALTER TABLE `WM_log_others` CHANGE `instance` `instance` VARCHAR( 20 ) NOT NULL DEFAULT '0';
 ALTER TABLE `WM_log_teacher` CHANGE `instance` `instance` VARCHAR( 20 ) NOT NULL DEFAULT '0';

/* 新增 討論版/新增修改刪除留言 acl */
INSERT INTO `WM_acl_function` (`function_id`, `caption`, `scope`, `default_permission`)
VALUES ('2700200500', '討論版→使用討論版→張貼留言', 'learn,teach,academic', 'enable,visible,readable,writable,modifiable,uploadable,removable'), 
       ('2700200600', '討論版→使用討論版→修改留言', 'learn,teach,academic', 'enable,visible,readable,writable,modifiable,uploadable,removable'), 
       ('2700200700', '討論版→使用討論版→刪除留言', 'learn,teach,academic', 'enable,visible,readable,writable,modifiable,uploadable,removable');

/* #032682 點[未分組課程]會執行很久 */
ALTER TABLE `WM_term_group` ADD INDEX `child` (`child`);

/* 新版的課程複製精靈 */
CREATE TABLE `CO_qti_pack_log` (
  `serial_no` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `log_stime` datetime NOT NULL,
  `log_etime` datetime DEFAULT NULL,
  `source_course_id` int(10) unsigned NOT NULL,
  `source_caption` text NOT NULL,
  `destination_course_id` int(10) unsigned NOT NULL,
  `state` enum('0','1') NOT NULL DEFAULT '0',
  `note` text NOT NULL,
  PRIMARY KEY (`serial_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

/* 學員瀏覽lcms影片動作事件記錄 */
CREATE TABLE `LM_read_video_log` (
  `course_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '課程編號',
  `username` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '帳號',
  `url` varchar(255) NOT NULL COMMENT '教材路徑',
  `action_id` varchar(12) NOT NULL COMMENT '行為編號',
  `start_time` varchar(16) NOT NULL COMMENT '開始時間（影片時分秒）',
  `end_time` varchar(16) NOT NULL COMMENT '結束時間（影片時分秒）',
  `title` varchar(255) NOT NULL COMMENT '教材名稱',
  `duration` varchar(16) NOT NULL COMMENT '影片長度（影片時分秒）',
  `create_time` datetime NOT NULL COMMENT '事件時間',
  `activity_id` varchar(255) NOT NULL COMMENT '節點編號',
  `target_type` varchar(20) NOT NULL COMMENT '教材類型',
  `target_id` bigint(20) NOT NULL COMMENT '教材編號',
  `session_id` varchar(40) NOT NULL COMMENT 'session id',
  `system_ip` varchar(45) NOT NULL COMMENT '服務主機ip',
  `from_ip` varchar(45) NOT NULL COMMENT '使用者端ip',
  `begin_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '事件時間（廢除）',
  `begin_time_ms` int(6) NOT NULL COMMENT '（廢除）',
  `over_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '（廢除）',
  `over_time_ms` int(6) NOT NULL COMMENT '（廢除）',
  PRIMARY KEY (`course_id`,`username`,`target_type`,`target_id`,`action_id`,`begin_time`,`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='學員瀏覽lcms影片動作事件記錄';

/* *48572 雲科大 - 平台非常緩慢（於 WM_term_subject 多增加 board_id INDEX 來改善效能) */
ALTER TABLE  `WM_term_subject` ADD INDEX  `WM_term_subject_idx_boardid` (  `board_id` );

/*
加寬 user_agent 欄位寬度
應於夜間設排程調整資料表結構
*/
ALTER TABLE `WM_log_userAgent` CHANGE `agent_id` `agent_id` MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `WM_log_classroom` CHANGE `user_agent` `user_agent` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `WM_log_others` CHANGE `user_agent` `user_agent` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `WM_log_teacher` CHANGE `user_agent` `user_agent` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `WM_log_director` CHANGE `user_agent` `user_agent` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `WM_log_manager` CHANGE `user_agent` `user_agent` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0';

/*
調整版冊章節段，支援長度到 4 個字元
*/
ALTER TABLE `WM_qti_exam_item` CHANGE `version` `version` INT( 4 ) UNSIGNED NULL DEFAULT NULL ,
CHANGE `volume` `volume` INT( 4 ) UNSIGNED NULL DEFAULT NULL ,
CHANGE `chapter` `chapter` INT( 4 ) UNSIGNED NULL DEFAULT NULL ,
CHANGE `paragraph` `paragraph` INT( 4 ) UNSIGNED NULL DEFAULT NULL ,
CHANGE `section` `section` INT( 4 ) UNSIGNED NULL DEFAULT NULL;

ALTER TABLE `WM_qti_homework_item` CHANGE `version` `version` INT( 4 ) UNSIGNED NULL DEFAULT NULL ,
CHANGE `volume` `volume` INT( 4 ) UNSIGNED NULL DEFAULT NULL ,
CHANGE `chapter` `chapter` INT( 4 ) UNSIGNED NULL DEFAULT NULL ,
CHANGE `paragraph` `paragraph` INT( 4 ) UNSIGNED NULL DEFAULT NULL ,
CHANGE `section` `section` INT( 4 ) UNSIGNED NULL DEFAULT NULL;

ALTER TABLE `WM_qti_questionnaire_item` CHANGE `version` `version` INT( 4 ) UNSIGNED NULL DEFAULT NULL ,
CHANGE `volume` `volume` INT( 4 ) UNSIGNED NULL DEFAULT NULL ,
CHANGE `chapter` `chapter` INT( 4 ) UNSIGNED NULL DEFAULT NULL ,
CHANGE `paragraph` `paragraph` INT( 4 ) UNSIGNED NULL DEFAULT NULL ,
CHANGE `section` `section` INT( 4 ) UNSIGNED NULL DEFAULT NULL; 

/*
調整問卷寫入行事曆
*/
ALTER TABLE `WM_calendar` CHANGE `relative_type` `relative_type` ENUM( 'course_begin', 'course_end', 'homework_begin', 'homework_end', 'exam_begin', 'exam_end', 'homework_delay', 'questionnaire_begin', 'questionnaire_end' ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

/**
 * 資安擋除ip
 */
CREATE TABLE `WM_blocked_attack_ip` (
  `blocked_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水號',
  `username` varchar(32) NOT NULL COMMENT '封鎖當下的帳號',
  `start_time` datetime NOT NULL COMMENT '封鎖的起始時間',
  `end_time` datetime NOT NULL COMMENT '封鎖的結束時間',
  `blocked_ip_address` varchar(16) NOT NULL COMMENT '封鎖的ip',
  `create_time` datetime NOT NULL COMMENT '此筆資料建立時間',
  `blocked_count` int(11) NOT NULL COMMENT '封鎖期間被拒絕存取的次數',
  `blocked_log_filepath` varchar(200) NOT NULL COMMENT '拒絕存取時記錄使用者傳參的log',
  PRIMARY KEY (`blocked_id`),
  KEY `blocked_ip_address` (`blocked_ip_address`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='封鎖有攻擊行為的ip' AUTO_INCREMENT=1;

/**
 * 點名功能
 */
CREATE TABLE `APP_rollcall_base` (
  `rid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '點名編號 (流水號)',
  `course_id` int(10) unsigned NOT NULL COMMENT '課程編號',
  `creator` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '建立者帳號 (老師帳號)',
  `create_time` datetime NOT NULL COMMENT '建立時間',
  `begin_time` datetime NOT NULL COMMENT '該次點名啟用時間',
  `end_time` datetime NOT NULL COMMENT '該次點名結束時間',
  PRIMARY KEY (`rid`),
  KEY `course_id` (`course_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='點名資料基本檔' AUTO_INCREMENT=1;

CREATE TABLE `APP_rollcall_record` (
  `rid` int(11) NOT NULL COMMENT 'from APP_rollcall_base.rid',
  `username` varchar(32) NOT NULL COMMENT '學生帳號',
  `rollcall_time` datetime NOT NULL COMMENT '點名時間',
  `rollcall_status` int(11) NOT NULL COMMENT '點名狀態(1:已點名|2:未點名|3:遲到|4:早退|5:病假|6:公假|7:事假|8:喪假|9:裝置重複)',
  `device_ident` varchar(255) NOT NULL COMMENT '裝置識別碼',
  `memo` text NOT NULL COMMENT '備註',
  `modifier` varchar(32) NOT NULL COMMENT '修改點名狀態的老師帳號',
  `modify_time` datetime NOT NULL COMMENT '修改時間',
  PRIMARY KEY (`rid`,`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='學生的點名狀態';

INSERT INTO `WM_div_master` (`type_id`, `value`, `lang_code`, `type_name`, `value_name`, `show_order`) VALUES 
('rollcall_status', '0', 'Big5', '點名狀態', '未點名', 1),
('rollcall_status', '1', 'Big5', '點名狀態', '已到', 2),
('rollcall_status', '2', 'Big5', '點名狀態', '缺席', 3),
('rollcall_status', '3', 'Big5', '點名狀態', '遲到', 4),
('rollcall_status', '4', 'Big5', '點名狀態', '早退', 5),
('rollcall_status', '5', 'Big5', '點名狀態', '病假', 6),
('rollcall_status', '6', 'Big5', '點名狀態', '公假', 7),
('rollcall_status', '7', 'Big5', '點名狀態', '事假', 8),
('rollcall_status', '8', 'Big5', '點名狀態', '喪假', 9),
('rollcall_status', '9', 'Big5', '點名狀態', '裝置重複', 10);

CREATE TABLE `CO_adv` (
  `adv_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL DEFAULT '10001' COMMENT '站台id',
  `name` varchar(100) NOT NULL COMMENT '廣告名稱',
  `open_date_flag` varchar(1) NOT NULL,
  `open_date` date NOT NULL,
  `close_date_flag` varchar(1) NOT NULL,
  `close_date` date NOT NULL,
  `url` varchar(255) NOT NULL,
  `img_path` varchar(255) NOT NULL COMMENT '圖片路徑',
  `permute` int(10) NOT NULL,
  `create_datetime` datetime NOT NULL,
  `update_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `poster` varchar(32) NOT NULL,
  PRIMARY KEY (`adv_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
INSERT INTO `CO_adv` (`adv_id`, `school_id`, `name`, `open_date_flag`, `open_date`, `close_date_flag`, `close_date`, `url`, `img_path`, `permute`, `create_datetime`, `update_datetime`, `poster`) VALUES 
(1, 10001, 'wmpro51', '0', '0000-00-00', '0', '0000-00-00', '/mooc/index.php', '廣告輪播區/main.png', 1, NOW(), NOW(), 'root'),
(2, 10001, '愛上互動', '0', '0000-00-00', '0', '0000-00-00', 'http://wmpro.sun.net.tw/?p=product_isunfudon', '廣告輪播區/p3_愛上互動.jpg', 2, NOW(), NOW(), 'root'),
(3, 10001, '同儕互評', '0', '0000-00-00', '0', '0000-00-00', '', '廣告輪播區/p4_線上同儕互評.jpg', 5, NOW(), NOW(), 'root'),
(4, 10001, 'LCMS2', '0', '0000-00-00', '0', '0000-00-00', '', '廣告輪播區/p1_教學資料庫.jpg', 4, NOW(), NOW(), 'root');


CREATE TABLE `CO_links` (
  `links_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '廣告名稱',
  `open_date_flag` varchar(1) NOT NULL,
  `open_date` date NOT NULL,
  `close_date_flag` varchar(1) NOT NULL,
  `close_date` date NOT NULL,
  `url` varchar(255) NOT NULL,
  `img_path` varchar(255) NOT NULL COMMENT '圖片路徑',
  `permute` int(10) NOT NULL,
  `create_datetime` datetime NOT NULL,
  `update_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `poster` varchar(32) NOT NULL,
  `school_id` int( 11 ) NOT NULL DEFAULT '10001',
  PRIMARY KEY (`links_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


CREATE TABLE `CO_download` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水號',
  `title` varchar(255) NOT NULL COMMENT '下載名稱',
  `attach_path` text NOT NULL COMMENT '下載檔案',
  `open_date_flag` varchar(1) NOT NULL,
  `open_date` date NOT NULL,
  `close_date_flag` varchar(1) NOT NULL,
  `close_date` date NOT NULL,
  `kind` varchar(60) DEFAULT NULL COMMENT '下載類別',
  `creator` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `create_time` datetime NOT NULL,
  `modifier` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `modify_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `delete_flag` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否已刪除(0:否,1:是)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='下載管理' AUTO_INCREMENT=1;


ALTER TABLE `WM_term_subject` CHANGE `state` `state` ENUM( 'disable', 'open', 'taonly', 'public' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'open';

ALTER TABLE  `WM_qti_exam_test` CHANGE  `ctrl_window`  `ctrl_window` ENUM(  'none',  'lock',  'lock2' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'none';

ALTER TABLE  `WM_all_account` CHANGE  `homepage`  `homepage` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

INSERT INTO `WM_10001`.`WM_acl_function` (`function_id`, `caption`, `scope`, `default_permission`) VALUES ('2200200103', '討論室→離開討論室→系統於背景將討論紀錄轉貼到筆記本', 'learn,teach,direct,academic', 'enable,visible,readable,writable,modifiable,uploadable,removable');
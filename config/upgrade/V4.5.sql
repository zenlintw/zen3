SET NAMES utf8;

use `WM_10001`;
/* #1 個人設定 WM_user_account 新增 身分 學歷 國家來源 欄位，儲存給使用者統計報表用 */
ALTER TABLE `WM_user_account` ADD `user_status` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '身分(學生S, 在職W)' AFTER `hid` ,
ADD `education` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '學歷(小學P, 中學H, 高中S, 大學U, 碩士M, 博士D, 其他O)' AFTER `user_status` ,
ADD `country` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '國家來源(國碼)' AFTER `education` ;

use `WM_MASTER`;
/* #2 個人設定 WM_all_account 新增 身分 學歷 國家來源 欄位，儲存給使用者統計報表用 */
ALTER TABLE `WM_all_account` ADD `user_status` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '身分(學生S, 在職W)' AFTER `hid` ,
ADD `education` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '學歷(小學P, 中學H, 高中S, 大學U, 碩士M, 博士D, 其他O)' AFTER `user_status` ,
ADD `country` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '國家來源(國碼)' AFTER `education` ;

use `WM_10001`;
/* #3 整批更新筆記本 `WM_msg_message`.`receive_time` 欄位 */
update `WM_msg_message` set `receive_time` = `submit_time` where `receive_time` is null;

/* #4 筆記最後一次閱讀紀錄 */
CREATE TABLE `WM_notebook_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水號',
  `username` varchar(32) NOT NULL COMMENT 'WM_user_account.username',
  `fid` varchar(32) NOT NULL COMMENT 'WM_msg_message.folder_id',
  `mid` bigint(20) unsigned NOT NULL COMMENT 'WM_msg_message.msg_serial',
  `creator` varchar(32) NOT NULL,
  `create_time` datetime NOT NULL,
  `operator` varchar(32) NOT NULL,
  `upd_time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `username` (`username`,`fid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='筆記最後一次閱讀紀錄' AUTO_INCREMENT=1 ;

use `WM_MASTER`;
/* #5 修正 CO_all_major unique key */
ALTER TABLE  `CO_all_major` DROP INDEX  `idx1` ,
ADD UNIQUE  `idx1` (  `course_id` ,  `username` ,  `school` );

/* #6 增加管理者建立者及建立時間欄位 */
ALTER TABLE  `WM_manager` ADD  `creator` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT  '建立者' AFTER  `allow_ip` ,
ADD  `create_time` DATETIME NULL COMMENT  '建立時間' AFTER  `creator` ;

/* #7 新增課程建立者 */
use `WM_10001`;
ALTER TABLE  `WM_term_course` ADD  `creator` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_bin NULL COMMENT  '建立者' AFTER  `ta_can_sets` ;

use `WM_MASTER`;
ALTER TABLE  `CO_all_course` ADD  `creator` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_bin NULL COMMENT  '建立者' AFTER  `ta_can_sets` ;

use `WM_10001`;
/* #58676新版行事曆程式部分 begin*/
/* 增加repeat_begin欄位紀錄週期事件的開始日(不用透過parent_idx取得) */
ALTER TABLE `WM_calendar` ADD `repeat_begin` DATE NULL DEFAULT '0000-00-00' AFTER `repeat_freq` ;
/* 增加relative_type,relative_id欄位紀錄對應的課程或作業/測驗 */
ALTER TABLE `WM_calendar`
ADD `relative_type` ENUM( 'course_begin','course_end','homework_begin','homework_end','exam_begin','exam_end' ) NULL DEFAULT NULL ,
ADD `relative_id` INT( 10 ) NULL DEFAULT NULL ;
/* 行事曆是否要顯示需要儲存在DB(個人.課程.學校)(不會連動到Email提醒) */
ALTER TABLE `WM_cal_setting` CHANGE `also_show` `also_show` SET( 'person', 'course', 'school' ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
/* #58676新版行事曆程式部分 end*/

/* #9 筆記本 begin */
CREATE TABLE `APP_note_share` (
  `share_key` varchar(32) NOT NULL COMMENT '筆記分享Key',
  `folder_id` varchar(32) NOT NULL,
  `msg_serial` bigint(20) NOT NULL,
  `owner` varchar(32) NOT NULL COMMENT '分享者',
  `due_time` int(10) NOT NULL COMMENT '分享結束時間',
  PRIMARY KEY (`share_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `APP_note_action_history` (
  `username` varchar(32) NOT NULL COMMENT '帳號',
  `log_time` int(10) NOT NULL COMMENT '動作時間',
  `action` set('A','M','D') NOT NULL COMMENT 'A新增,M修改,D刪除',
  `folder_id` varchar(32) NOT NULL COMMENT '筆記本ID',
  `msg_serial` bigint(20) NOT NULL COMMENT '筆記ID',
  `from` set('server','client') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/* #9 筆記本 end */
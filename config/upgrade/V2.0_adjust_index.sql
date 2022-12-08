--
-- WM pro 2.0 之資料庫索引調整
--
-- @author : Wiseguy Liang
-- @sence  : 2007-05-23
-- @ident  : $Id: V2.0_adjust_index.sql,v 1.1 2010/02/24 02:38:57 saly Exp $
--

ALTER TABLE `WM_bbs_boards` ADD INDEX `idx1` ( `owner_id` );

ALTER TABLE `WM_calendar` DROP INDEX `username` ,
ADD INDEX `username` ( `username` ) ,
ADD INDEX `idx1` ( `type` ) ,
ADD INDEX `idx2` ( `memo_date` ) ;

ALTER TABLE `WM_chat_mmc` ADD INDEX `idx1` ( `meetingID` ) ,
ADD INDEX `idx2` ( `meetingType` , `extra` ) ;

ALTER TABLE `WM_chat_setting` ADD INDEX `idx1` ( `owner` , `state` , `visibility` ) ;

ALTER TABLE `WM_class_main` ADD INDEX `idx1` ( `dep_id` ) ,
ADD INDEX `idx2` ( `status` ) ;

ALTER TABLE `WM_class_member` DROP INDEX `idx` ,
ADD INDEX `idx1` ( `username` ),
ADD INDEX `idx2` ( `role` ) ;

ALTER TABLE `WM_grade_list` ADD INDEX `idx1` ( `course_id` ) ,
ADD INDEX `idx2` ( `source` ) ,
ADD INDEX `idx3` ( `property` );

ALTER TABLE `WM_im_message` ADD INDEX `idx1` ( `saw` ) ,
ADD INDEX `idx2` ( `reciver` ),
ADD INDEX `idx3` ( `serial` ),
ADD INDEX `idx4` ( `sorder` , `talk` );

ALTER TABLE `WM_log_classroom` DROP INDEX `ip_index` ,
ADD INDEX `ip_index` ( `remote_address` ( 15 ) ),
ADD INDEX `idx1` ( `username` ),
ADD INDEX `idx2` ( `department_id` , `instance` ),
ADD INDEX `idx3` ( `result_id` ),
ADD INDEX `idx4` ( `log_time` ),
ADD INDEX `idx5` ( `note` ( 30 ) );

ALTER TABLE `WM_log_others` DROP INDEX `ip_index` ,
ADD INDEX `ip_index` ( `remote_address` ( 15 ) ),
ADD INDEX `idx1` ( `username` ),
ADD INDEX `idx2` ( `department_id` , `instance` ),
ADD INDEX `idx3` ( `result_id` ),
ADD INDEX `idx4` ( `log_time` ),
ADD INDEX `idx5` ( `note` ( 30 ) );

ALTER TABLE `WM_log_director` DROP INDEX `ip_index` ,
ADD INDEX `ip_index` ( `remote_address` ( 15 ) );

ALTER TABLE `WM_log_manager` DROP INDEX `ip_index` ,
ADD INDEX `ip_index` ( `remote_address` ( 15 ) );

ALTER TABLE `WM_log_teacher` DROP INDEX `ip_index` ,
ADD INDEX `ip_index` ( `remote_address` ( 15 ) );

ALTER TABLE `WM_msg_message` ADD INDEX `idx1` ( `receiver` , `status` ) ,
ADD INDEX `idx2` ( `folder_id` );

ALTER TABLE `WM_qti_exam_item` DROP INDEX `idx1` ,
ADD INDEX `idx1` ( `course_id` );

ALTER TABLE `WM_qti_homework_item` ADD INDEX `idx1` ( `course_id` );

ALTER TABLE `WM_qti_questionnaire_item` ADD INDEX `idx1` ( `course_id` );

ALTER TABLE `WM_qti_exam_test` ADD INDEX `idx1` ( `course_id` , `publish` );
ALTER TABLE `WM_qti_homework_test` ADD INDEX `idx1` ( `course_id` , `publish` );
ALTER TABLE `WM_qti_questionnaire_test` ADD INDEX `idx1` ( `course_id` , `publish` );

ALTER TABLE `WM_record_reading` DROP INDEX `idx2`,
ADD INDEX `idx2` ( `username` );

ALTER TABLE `WM_term_course` ADD INDEX `idx1` ( `status` , `st_begin` , `st_end` ) ,
ADD INDEX `idx2` ( `kind` );

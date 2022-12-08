ALTER TABLE `WM_history_user_account`             ADD `msg_reserved` TINYINT(1) NOT NULL DEFAULT '0' AFTER `theme`;
ALTER TABLE `WM_history_qti_exam_result`          ADD `ref_url` varchar(128) default NULL, ADD `ref_file` varchar(255) default NULL;
ALTER TABLE `WM_history_qti_homework_result`      ADD `ref_url` varchar(128) default NULL, ADD `ref_file` varchar(255) default NULL;
ALTER TABLE `WM_history_qti_questionnaire_result` ADD `ref_url` varchar(128) default NULL, ADD `ref_file` varchar(255) default NULL;
ALTER TABLE `WM_history_record_reading`           ADD `title` varchar(255) NOT NULL default '' AFTER `over_time`;

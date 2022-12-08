SET NAMES utf8;
use `WM_10001`;

ALTER TABLE `WM_history_user_account` ADD `user_status` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '身分(學生S, 在職W)' AFTER `hid` ,
ADD `education` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '學歷(小學P, 中學H, 高中S, 大學U, 碩士M, 博士D, 其他O)' AFTER `user_status` ,
ADD `country` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '國家來源(國碼)' AFTER `education` ;

/* 管理者環境/課程列表 - 當資料量大時，查詢會緩慢，因為discren_id的型態不同於join的課程編號 */
ALTER TABLE  `WM_review_flow` CHANGE  `discren_id`  `discren_id` INT NOT NULL;
ALTER TABLE  `WM_review_flow` ADD INDEX (`discren_id`);
ALTER TABLE  `WM_review_sysidx` CHANGE  `discren_id`  `discren_id` INT NOT NULL;
ALTER TABLE  `WM_review_sysidx` ADD INDEX (`discren_id`);

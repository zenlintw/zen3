ALTER TABLE  `WM_qti_peer_result_eva` ADD  `score_type` TINYINT( 1 ) NOT NULL DEFAULT  '0' COMMENT  '互評或自評 (0: 互評, 1: 自評, 2:老師評)';
ALTER TABLE  `WM_qti_peer_result_eva` DROP PRIMARY KEY ,
ADD PRIMARY KEY (  `exam_id` ,  `examinee` ,  `time_id` ,  `creator` ,  `point_id` ,  `score_type` );

UPDATE `WM_qti_peer_result_eva` SET `score_type` = 1 WHERE examinee = creator;
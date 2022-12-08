ALTER TABLE `WM_qti_exam_test` ADD `setting` SET( 'upload', 'anonymity' ) NOT NULL DEFAULT 'upload' AFTER `random_pick`;
ALTER TABLE `WM_qti_exam_test` CHANGE `setting` `setting` SET( 'upload', 'anonymity' ) NOT NULL DEFAULT '';
ALTER TABLE `WM_qti_homework_test` ADD `setting` SET( 'upload', 'anonymity' ) NOT NULL DEFAULT 'upload' AFTER `random_pick`;
ALTER TABLE `WM_qti_homework_test` CHANGE `setting` `setting` SET( 'upload', 'anonymity' ) NOT NULL DEFAULT '';
ALTER TABLE `WM_qti_questionnaire_test` ADD `setting` SET( 'upload', 'anonymity' ) NOT NULL DEFAULT 'upload' AFTER `random_pick`;
ALTER TABLE `WM_qti_questionnaire_test` CHANGE `setting` `setting` SET( 'upload', 'anonymity' ) NOT NULL DEFAULT '';

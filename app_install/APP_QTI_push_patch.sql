ALTER TABLE  `WM_qti_exam_test` ADD  `push` ENUM(  'N',  'Y' ) NOT NULL DEFAULT  'N' COMMENT  '是否要推播';
ALTER TABLE  `WM_qti_homework_test` ADD  `push` ENUM(  'N',  'Y' ) NOT NULL DEFAULT  'N' COMMENT  '是否要推播';
ALTER TABLE  `WM_qti_questionnaire_test` ADD  `push` ENUM(  'N',  'Y' ) NOT NULL DEFAULT  'N' COMMENT  '是否要推播';
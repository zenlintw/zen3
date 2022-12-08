SET NAMES utf8;

use `WM_MASTER`;

/* 網站人數改用資料表記錄 */
ALTER TABLE  `WM_school` ADD  `counter` DOUBLE UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '網站累計人數';

use `WM_10001`;
/* LCMS課程複製精靈 acl_function */
INSERT INTO `WM_acl_function` (`function_id`, `caption`, `scope`, `default_permission`) VALUES 
(800400100, '課程管理→LCMS課程複製精靈', 'teach', 'enable,visible,readable,writable,modifiable,uploadable,removable');

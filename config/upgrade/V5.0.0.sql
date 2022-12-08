SET NAMES utf8;
use `WM_10001`;

/* 討論版設定新增「啟用 Facebook 留言」 */
ALTER TABLE `WM_bbs_boards` ADD `fb_comment` VARCHAR(1) CHARACTER
SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'N' COMMENT '啟用 Facebook 留言';

/* 性別新增未標示 */
 ALTER TABLE `WM_all_account` CHANGE `gender` `gender` ENUM( 'F', 'M', 'N' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'F';
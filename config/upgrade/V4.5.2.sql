SET NAMES utf8;

use `WM_10001`;

/* 增加資料表註解 */
ALTER TABLE `WM_bbs_readed` CHANGE `type` `type` ENUM( 'b', 'q' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'b' COMMENT 'b討論版q精華區';

/* 增加資料表索引 */
ALTER TABLE `WM_bbs_readed` ADD INDEX ( `board_id` , `username` );

/* 增加討論版開放的屬性 disable關閉 open開放 taonly限定老師 public公開，沒有登入也可以觀看*/
ALTER TABLE `WM_term_subject` CHANGE `state` `state` ENUM('disable', 'open', 'taonly', 'public') CHARACTER
SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'open';
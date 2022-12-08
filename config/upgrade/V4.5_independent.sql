SET NAMES utf8;

use `WM_10001`;
/* #7 需進系統wm3無權限，(獨立站)WM_user_account 改為取 WM_all_account join WM_sch4user 的view */
ALTER TABLE `WM_user_account` RENAME TO `WM_user_account_1`;
CREATE VIEW WM_user_account AS
SELECT a.*
FROM WM_MASTER.`WM_all_account` a
INNER JOIN  WM_MASTER.`WM_sch4user` b
ON a.`username` = b.`username` AND b.`school_id` = SCH_10001
WHERE 1;
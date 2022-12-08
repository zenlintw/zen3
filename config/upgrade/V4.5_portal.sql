SET NAMES utf8;

use `WM_10001`;
/* #7 需進系統wm3無權限，(入口網站&內容站)WM_user_account 改為取 WM_all_account 的view */
ALTER TABLE `WM_user_account` RENAME TO `WM_user_account_1`;
CREATE VIEW WM_user_account AS
SELECT *
FROM WM_MASTER.`WM_all_account`
WHERE 1;
SET NAMES utf8;

use `WM52_ARMY_10001`;

/* V1.3 課程上架需設定的trigger，portal及內容商都要執行，這sql只針對學校代號10001時，其他學校需換代號10001 改為該學校ID */
DELIMITER $$
DROP TRIGGER IF EXISTS after_insert_course $$

CREATE TRIGGER after_insert_course AFTER INSERT ON WM_term_course FOR EACH ROW
BEGIN
    INSERT INTO WM52_ARMY_MASTER.CO_all_course (select *,10001 as school from WM_term_course where course_id=NEW.course_id) ;
END $$
DELIMITER ;

DELIMITER $$
DROP TRIGGER IF EXISTS after_update_course $$

CREATE TRIGGER after_update_course AFTER UPDATE ON WM_term_course FOR EACH ROW
BEGIN
     
    DELETE FROM WM52_ARMY_MASTER.CO_all_course WHERE course_id=NEW.course_id and school=10001;
    INSERT INTO WM52_ARMY_MASTER.CO_all_course (select *,10001 as school from WM_term_course where course_id=NEW.course_id) ;

END $$
DELIMITER ;

DELIMITER $$
DROP TRIGGER IF EXISTS after_insert_major $$

CREATE TRIGGER after_insert_major AFTER INSERT ON WM_term_major FOR EACH ROW
BEGIN
    INSERT INTO WM52_ARMY_MASTER.CO_all_major (select *,10001 as school from WM_term_major where course_id=NEW.course_id and username=NEW.username) ;
END $$
DELIMITER ;

DELIMITER $$

DROP TRIGGER IF EXISTS after_update_major $$

CREATE TRIGGER after_update_major AFTER UPDATE ON WM_term_major FOR EACH ROW
BEGIN
     
    DELETE FROM WM52_ARMY_MASTER.CO_all_major WHERE course_id=NEW.course_id and username=NEW.username and school=10001;
    INSERT INTO WM52_ARMY_MASTER.CO_all_major (select *,10001 as school from WM_term_major where course_id=NEW.course_id and username=NEW.username) ;

END $$
DELIMITER ;

/* V4.5#7 需進系統wm3無權限，(入口網站&內容站)WM_user_account 改為取 WM_all_account 的view */
ALTER TABLE `WM_user_account` RENAME TO `WM_user_account_1`;
CREATE VIEW WM_user_account AS
SELECT *
FROM WM52_ARMY_MASTER.`WM_all_account`
WHERE 1;
/* 課程上架需設定的trigger，portal及內容商都要執行，這sql只針對學校代號10001時，其他學校需換代號SCH_10001 改為該學校ID */
use `WM_10001`;

DELIMITER $$
DROP TRIGGER IF EXISTS after_insert_course $$

CREATE TRIGGER after_insert_course AFTER INSERT ON WM_term_course FOR EACH ROW
BEGIN
    INSERT INTO WM_MASTER.CO_all_course (select *,SCH_10001 as school from WM_term_course where course_id=NEW.course_id) ;
END $$
DELIMITER ;

DELIMITER $$
DROP TRIGGER IF EXISTS after_update_course $$

CREATE TRIGGER after_update_course AFTER UPDATE ON WM_term_course FOR EACH ROW
BEGIN
     
    DELETE FROM WM_MASTER.CO_all_course WHERE course_id=NEW.course_id and school=SCH_10001;
    INSERT INTO WM_MASTER.CO_all_course (select *,SCH_10001 as school from WM_term_course where course_id=NEW.course_id) ;

END $$
DELIMITER ;

DELIMITER $$
DROP TRIGGER IF EXISTS after_insert_major $$

CREATE TRIGGER after_insert_major AFTER INSERT ON WM_term_major FOR EACH ROW
BEGIN
    INSERT INTO WM_MASTER.CO_all_major (select *,SCH_10001 as school from WM_term_major where course_id=NEW.course_id and username=NEW.username) ;
END $$
DELIMITER ;

DELIMITER $$

DROP TRIGGER IF EXISTS after_update_major $$

CREATE TRIGGER after_update_major AFTER UPDATE ON WM_term_major FOR EACH ROW
BEGIN
     
    DELETE FROM WM_MASTER.CO_all_major WHERE course_id=NEW.course_id and username=NEW.username and school=SCH_10001;
    INSERT INTO WM_MASTER.CO_all_major (select *,SCH_10001 as school from WM_term_major where course_id=NEW.course_id and username=NEW.username) ;

END $$
DELIMITER ;
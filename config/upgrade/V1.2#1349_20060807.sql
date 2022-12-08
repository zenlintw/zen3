ALTER TABLE `WM_10001`.`WM_user_account`
CHANGE `home_address` `home_address` VARCHAR( 255 ) DEFAULT NULL ,
CHANGE `office_address` `office_address` VARCHAR( 255 ) DEFAULT NULL ,
CHANGE `company` `company` VARCHAR( 255 ) DEFAULT NULL;

ALTER TABLE `WM_MASTER`.`WM_all_account`
CHANGE `home_address` `home_address` VARCHAR( 255 ) DEFAULT NULL ,
CHANGE `office_address` `office_address` VARCHAR( 255 ) DEFAULT NULL ,
CHANGE `company` `company` VARCHAR( 255 ) DEFAULT NULL;

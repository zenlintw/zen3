ALTER TABLE  `APP_rollcall_base` ADD `mode` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT  '點名方式 1: Nearby | 2: Beacon' AFTER  `creator`;
ALTER TABLE  `APP_rollcall_base` ADD `device_status` varchar(128) NOT NULL COMMENT  '裝置狀態' AFTER `mode`;
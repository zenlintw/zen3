CREATE DATABASE /*!32312 IF NOT EXISTS*/ `hongu` /*!40100 DEFAULT CHARACTER SET utf8 */;
GRANT SELECT, INSERT, UPDATE, DELETE, INDEX, ALTER, CREATE, DROP on `hongu`.*  TO wm3@192.168.10.155 IDENTIFIED BY 'WmIiI';
FLUSH PRIVILEGES;

USE `hongu`;

DROP TABLE IF EXISTS `hg_app_device_info`;
CREATE TABLE `hg_app_device_info` (
  `app_uuid` char(36) NOT NULL COMMENT 'APP UUID',
  `appname` varchar(255) NOT NULL COMMENT 'APP名稱',
  `appversion` varchar(25) DEFAULT NULL COMMENT 'APP版本',
  `deviceos` enum('IOS','ANDROID') DEFAULT 'IOS' COMMENT '裝置作業系統類型',
  `deviceuid` char(40) NOT NULL COMMENT '裝置UID',
  `devicetoken` varchar(255) NOT NULL COMMENT '裝置TOKEN',
  `type` smallint(3) DEFAULT NULL COMMENT '1.APNS 2.GCM 3.極光',
  `devicename` varchar(255) NOT NULL COMMENT '裝置名稱',
  `devicemodel` varchar(100) NOT NULL COMMENT '裝置型號',
  `deviceversion` varchar(25) NOT NULL COMMENT '裝置版本',
  `pushbadge` enum('ON','OFF') DEFAULT 'OFF' COMMENT '推送標記',
  `pushalert` enum('ON','OFF') DEFAULT 'OFF' COMMENT '推送訊息',
  `pushsound` enum('ON','OFF') DEFAULT 'OFF' COMMENT '推送聲音',
  `environment` enum('PRODUCTION','SANDBOX') NOT NULL DEFAULT 'PRODUCTION' COMMENT '環境狀態',
  `status` enum('ACTIVE','UNINSTALLED') NOT NULL DEFAULT 'ACTIVE' COMMENT '狀態',
  `builder` varchar(32) NOT NULL DEFAULT 'SYSTEM' COMMENT '新增者',
  `create_datetime` datetime NOT NULL COMMENT '新增日時',
  `update_datetime` datetime NOT NULL COMMENT '更新日時',
  PRIMARY KEY (`app_uuid`,`devicetoken`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='裝置資訊';

DROP TABLE IF EXISTS `hg_app_subscribe_channel`;
CREATE TABLE `hg_app_subscribe_channel` (
  `app_uuid` char(36) NOT NULL COMMENT 'APP UUID',
  `devicetoken` varchar(255) NOT NULL COMMENT '裝置TOKEN',
  `channel` varchar(128) NOT NULL COMMENT '訂閱channel',
  `builder` varchar(32) NOT NULL DEFAULT 'SYSTEM' COMMENT '新增者',
  `create_datetime` datetime NOT NULL COMMENT '新增日時',
  `update_datetime` datetime NOT NULL COMMENT '更新日時',
  PRIMARY KEY (`app_uuid`,`devicetoken`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Message Queue 訂閱頻道';
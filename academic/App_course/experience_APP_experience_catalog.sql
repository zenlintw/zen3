CREATE TABLE `APP_experience_catalog` (
  `catalog_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '類別序號',
  `caption` text NOT NULL COMMENT '類科名稱',
  `description` text COMMENT '類科介紹',
  `cover` varchar(255) DEFAULT NULL COMMENT '類科封面',
  `permute` int(10) unsigned NOT NULL COMMENT '排序 (由小到大)',
  `enable` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否啟用 (0: 停用，1: 啟用)',
  `begin_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '啟用時間',
  `end_date` datetime NOT NULL DEFAULT '9999-12-31 23:59:59' COMMENT '結束時間',
  `add_time` datetime NOT NULL COMMENT '加入時間',
  `update_time` datetime NOT NULL COMMENT '最後更新時間',
  PRIMARY KEY (`catalog_id`),
  KEY `idx1` (`catalog_id`,`permute`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='試聽課程 - 類科';

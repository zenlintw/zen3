CREATE TABLE `WM_experience_url` (
  `idx` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '節點流水號',
  `catalog_id` int(10) unsigned NOT NULL COMMENT '類別序號',
  `caption` text NOT NULL COMMENT '節點名稱',
  `url` text NOT NULL COMMENT '節點 URL',
  `permute` int(10) unsigned NOT NULL COMMENT '排序 (由小到大)',
  `enable` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否啟用 (0: 停用，1: 啟用)',
  `begin_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '開始時間',
  `end_date` datetime NOT NULL DEFAULT '9999-12-31 23:59:59' COMMENT '結束時間',
  `add_time` datetime NOT NULL COMMENT '加入時間',
  `update_time` datetime NOT NULL COMMENT '最後更新時間',
  PRIMARY KEY (`idx`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='試聽課程 - 類科內容節點';
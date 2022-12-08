CREATE TABLE `CO_activities` (
  `act_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '活動代碼',
  `caption` varchar(100) DEFAULT NULL COMMENT '備註',
  `status` enum('N','Y') NOT NULL DEFAULT 'Y' COMMENT '上架狀態,"Y"=上架中,"N"=下架',
  `permute` int(10) NOT NULL COMMENT '順序',
  `picture` varchar(255) COMMENT '活動圖片(1024x468)',
  PRIMARY KEY (`act_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='活動看板設定檔' AUTO_INCREMENT=1 ;
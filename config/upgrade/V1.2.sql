/*貼文按讚匯數總表*/
CREATE TABLE `WM_bbs_push` (
  `type` varchar(1) NOT NULL DEFAULT 'b',
  `board_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `node` varchar(80) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `site` int(10) unsigned NOT NULL DEFAULT '0',
  `push` int(5) unsigned DEFAULT '0',
  PRIMARY KEY (`board_id`,`node`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*討論版，增加發表人員、關閉後討論版要開啟或關閉的欄位 2015/1/30*/
ALTER TABLE  `WM_bbs_boards` ADD  `poster` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT  '發表人員',
ADD  `after_finish` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT  '結束後討論區開啟或關閉';
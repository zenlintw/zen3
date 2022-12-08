SET NAMES utf8;
SET foreign_key_checks = 1;

-- 建在 WM52_ARMY_MASTER 內
USE `WM52_ARMY_MASTER`;

/*MOOC註冊-FB帳號與WM帳號關連資料表*/
/* DROP TABLE IF EXISTS `CO_fb_account`; */
CREATE TABLE `CO_fb_account` (
  `id` varchar(32) NOT NULL,
  `username` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*MOOC註冊-首次註冊MOOC帳號基本資料表*/
/* DROP TABLE IF EXISTS `CO_mooc_account`; */
CREATE TABLE `CO_mooc_account` (
  `username` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `password` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `enable` enum('N','Y') NOT NULL DEFAULT 'N',
  `first_name` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `last_name` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `gender` enum('F','M') NOT NULL DEFAULT 'F',
  `birthday` date DEFAULT NULL,
  `personal_id` varchar(32) DEFAULT NULL,
  `email` varchar(64) NOT NULL DEFAULT '',
  `homepage` varchar(64) DEFAULT NULL,
  `home_tel` varchar(32) DEFAULT NULL,
  `home_fax` varchar(32) DEFAULT NULL,
  `home_address` varchar(255) DEFAULT NULL,
  `office_tel` varchar(32) DEFAULT NULL,
  `office_fax` varchar(32) DEFAULT NULL,
  `office_address` varchar(255) DEFAULT NULL,
  `cell_phone` varchar(32) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `department` varchar(64) DEFAULT NULL,
  `title` varchar(32) DEFAULT NULL,
  `language` varchar(32) DEFAULT NULL,
  `theme` varchar(32) DEFAULT 'default',
  `msg_reserved` tinyint(1) NOT NULL DEFAULT '0',
  `hid` int(10) unsigned NOT NULL DEFAULT '262075',
  PRIMARY KEY (`username`),
  UNIQUE KEY `idx1` (`username`,`password`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*MOOC-跨校包裝安裝課程*/
/* DROP TABLE IF EXISTS `CO_course_install`; */
CREATE TABLE `CO_course_install` (
  `id` int(8) NOT NULL AUTO_INCREMENT COMMENT '匯入編號',
  `src_school_id` int(5) NOT NULL COMMENT '來源學校',
  `tar_school_id` int(5) NOT NULL COMMENT '目的學校',
  `src_course_id` int(8) NOT NULL COMMENT '來源課程',
  `tar_course_id` int(8) NOT NULL COMMENT '目的課程',
  `import_params` text NOT NULL COMMENT '匯入參數',
  `state` varchar(20) NOT NULL COMMENT '匯入狀態',
  `reg_time` datetime NOT NULL,
  `finish_time` datetime DEFAULT NULL,
  `err_msg` text NOT NULL COMMENT '匯入失敗的訊息',
  `owner` varchar(20) NOT NULL COMMENT '執行者',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='跨校包裝安裝課程' AUTO_INCREMENT=1 ;

/*MOOC-學校設定*/
/* DROP TABLE IF EXISTS `CO_school`; */
CREATE TABLE `CO_school` (
  `school_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '學校ID',
  `student_mooc` tinyint(1) unsigned  NOT NULL DEFAULT '1'  COMMENT '設定學生環境UI為MOOC風格(1:是,0:否)',
  `social_share` SET('FB','PLURK','TWITTER','LINE','WECHAT') COMMENT '社群分享',
  `canReg_ext` SET('FB','GOOGLE') COMMENT '開放註冊-外部系統',
  `canReg_fb_id` varchar(64) COMMENT 'FB API-ID',
  `canReg_fb_secret` varchar(64) COMMENT 'FB API-SECRET',
  `banner_title1` varchar(128) NOT NULL COMMENT 'BANNER大標題',
  `banner_title2` varchar(128) NOT NULL COMMENT 'BANNER副標題',
  `banner_title3` text NOT NULL COMMENT 'BANNER下標',
  `footer_about` varchar(128) COMMENT '關於我們URL',
  `footer_contact` varchar(128) COMMENT '聯絡我們URL',
  `footer_faq` varchar(128) COMMENT '常見問題URL',
  `footer_info` varchar(255) COMMENT '其它資訊',
  `mycourse_view` enum('T','G') NOT NULL DEFAULT 'T' COMMENT '我的課程呈現方式，T:表示示文字,G:表示圖文',
  PRIMARY KEY (`school_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `CO_all_course` (
  `course_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `content_id` int(10) unsigned DEFAULT NULL,
  `caption` text NOT NULL,
  `teacher` varchar(128) DEFAULT NULL,
  `kind` enum('group','course') DEFAULT 'course',
  `en_begin` date DEFAULT NULL,
  `en_end` date DEFAULT NULL,
  `st_begin` date DEFAULT NULL,
  `st_end` date DEFAULT NULL,
  `status` tinyint(3) unsigned DEFAULT '0',
  `texts` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `content` text,
  `credit` tinyint(3) unsigned DEFAULT NULL,
  `discuss` int(10) unsigned DEFAULT NULL,
  `bulletin` bigint(20) unsigned DEFAULT NULL,
  `n_limit` smallint(6) DEFAULT '0',
  `a_limit` smallint(6) DEFAULT '0',
  `quota_used` int(10) unsigned NOT NULL DEFAULT '0',
  `quota_limit` int(10) unsigned NOT NULL DEFAULT '102400',
  `path` varchar(128) NOT NULL DEFAULT '',
  `login_times` int(10) unsigned NOT NULL DEFAULT '0',
  `post_times` int(10) unsigned NOT NULL DEFAULT '0',
  `dsc_times` int(10) unsigned NOT NULL DEFAULT '0',
  `fair_grade` int(6) unsigned DEFAULT '60',
  `ta_can_sets` set('content_id','caption','en_begin','en_end','st_begin','st_end','status','texts','url','content','n_limit','a_limit','fair_grade','review','cparent') NOT NULL DEFAULT 'caption,st_begin,st_end,status,texts,url,content,n_limit,a_limit,fair_grade',
  `path_type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '儲存學習節點類型，0:未設定；1:自訂課程；2:短期課程；3:一般課程',
  `exam_num` int(5) unsigned NOT NULL DEFAULT '0' COMMENT '儲存自我評量題數，0:不啟用；1-n: 顯示幾題',
  `course_type` varchar(10) DEFAULT NULL COMMENT '課程類型',
  `subhead` varchar(150) DEFAULT NULL COMMENT '副標題',
  `fee` int(10) DEFAULT NULL COMMENT '費用',
  `st_limit` tinyint(1) DEFAULT NULL COMMENT '非輔導期間是否對學員開放',
  `goal` text COMMENT '目標',
  `audience` text COMMENT '聽眾',
  `formal_pass` text COMMENT '一般生通過條件',
  `gallery_pass` text COMMENT '見習生通過條件',
  `is_use` text COMMENT '目標與對象是否啟用設定',
  `allow_comment` int(1) DEFAULT '0' COMMENT '是否開放評論區',
  `ref_title` text COMMENT '參考資料標題',
  `ref_url` text COMMENT '參考資料網址',
  `school` int(10) NOT NULL,
  PRIMARY KEY (`course_id`,`school`),
  KEY `idx1` (`status`,`st_begin`,`st_end`),
  KEY `idx2` (`kind`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE `CO_all_major` (
  `username` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `course_id` int(10) unsigned NOT NULL DEFAULT '0',
  `role` int(10) unsigned NOT NULL DEFAULT '0',
  `post` mediumint(8) unsigned DEFAULT '0',
  `hw` mediumint(8) unsigned DEFAULT '0',
  `qp` mediumint(8) unsigned DEFAULT '0',
  `exam` mediumint(8) unsigned DEFAULT '0',
  `bookmark` int(10) unsigned DEFAULT '0',
  `degree` int(10) unsigned DEFAULT '0',
  `total_node` int(10) unsigned DEFAULT '0',
  `login_times` int(10) unsigned DEFAULT '0',
  `post_times` int(10) unsigned DEFAULT '0',
  `dsc_times` int(10) unsigned DEFAULT '0',
  `last_login` datetime DEFAULT NULL,
  `add_time` datetime DEFAULT NULL,
  `school` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`username`,`course_id`,`school`),
  UNIQUE KEY `idx1` (`course_id`,`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `CO_all_group` (
  `parent` int(10) unsigned NOT NULL DEFAULT '0',
  `child` int(10) unsigned NOT NULL DEFAULT '0',
  `permute` int(10) unsigned NOT NULL DEFAULT '0',
  `school` int(10) NOT NULL,
  PRIMARY KEY (`parent`,`child`,`school`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/**
 * 記住我，保持登入
 */
CREATE TABLE `WM_persist_login` (
  `persist_idx` char(128) NOT NULL COMMENT '保持登入時的cookie值',
  `session_idx` char(32) NOT NULL,
  `username` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '帳號',
  `create_time` datetime NOT NULL COMMENT '建立日期',
  `expire_time` datetime NOT NULL COMMENT '過期日期',
  `create_ipaddress` varchar(128) NOT NULL COMMENT '建立時的ip位址',
  `user_agent` varchar(255) NOT NULL,
  PRIMARY KEY (`persist_idx`),
  KEY `username` (`username`),
  KEY `expire_time` (`expire_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='保持登入';

/* 修改個人設定預設值 */
ALTER TABLE  `CO_mooc_account` CHANGE  `gender`  `gender` ENUM(  'F',  'M',  'N' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'N';
ALTER TABLE  `WM_all_account` CHANGE  `gender`  `gender` ENUM(  'F',  'M',  'N' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'N';

/* 補 個人設定 WM_all_account 新增 身分 學歷 國家來源 欄位，儲存給使用者統計報表用 */
ALTER TABLE `WM_all_account` ADD `user_status` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '身分(學生S, 在職W)' AFTER `hid` ,
ADD `education` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '學歷(小學P, 中學H, 高中S, 大學U, 碩士M, 博士D, 其他O)' AFTER `user_status` ,
ADD `country` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '國家來源(國碼)' AFTER `education` ;

/* #5 修正 CO_all_major unique key */
ALTER TABLE  `CO_all_major` DROP INDEX  `idx1` ,
ADD UNIQUE  `idx1` (  `course_id` ,  `username` ,  `school` );

/* #6 增加管理者建立者及建立時間欄位 */
ALTER TABLE  `WM_manager` ADD  `creator` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT  '建立者' AFTER  `allow_ip` ,
ADD  `create_time` DATETIME NULL COMMENT  '建立時間' AFTER  `creator` ;


/* 4.5#7 新增課程建立者 */
ALTER TABLE  `CO_all_course` ADD  `creator` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_bin NULL COMMENT  '建立者' AFTER  `ta_can_sets` ;

/* 性別新增未標示 */
ALTER TABLE `WM_all_account` CHANGE `gender` `gender` ENUM( 'F', 'M', 'N' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'F';
 
ALTER TABLE  `WM_all_account` CHANGE  `homepage`  `homepage` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

-- 建在 WM_1000? 分校 內
USE `WM52_ARMY_10001`;

/*MOOC註冊-身份確認資料表*/
/* DROP TABLE IF EXISTS `CO_user_verify`; */
CREATE TABLE `CO_user_verify` (
  `type` varchar(32) NOT NULL DEFAULT '' COMMENT '類型',
  `username` varchar(32) NOT NULL COMMENT '使用者帳號',
  `email` varchar(64) NOT NULL COMMENT '電子信箱',
  `reg_time` datetime DEFAULT NULL COMMENT '建立時間',
  `verify_code` varchar(32) NOT NULL COMMENT '驗證碼',
  `verify_flag` varchar(1) NOT NULL DEFAULT 'N' COMMENT '驗證狀況Y驗證完成N尚未驗證',
  PRIMARY KEY (`type`,`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*MOOC&APP-課程圖片資料表*/
/* DROP TABLE IF EXISTS `CO_course_picture`; */
CREATE TABLE IF NOT EXISTS `CO_course_picture` (
  `course_id`   int(10)         unsigned NOT NULL DEFAULT '0'   COMMENT '課程ID',
  `picture` mediumblob COMMENT '圖片',
  `mime_type` varchar(20) NOT NULL COMMENT '圖片格式',
  PRIMARY KEY (`course_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/* MOOC同儕互評-成績TABLE同 WM_grade_list ,WM_grade_item ,WM_grade_stat 不變動 */
/* MOOC同儕互評-同儕作業試卷,同 WM_qti_homework_test,但又再加值一些欄位 */
CREATE TABLE `WM_qti_peer_test` (
  `exam_id`               int(10) unsigned                                         NOT NULL AUTO_INCREMENT      COMMENT '同儕作業ID',
  `course_id`             int(10) unsigned                                         NOT NULL DEFAULT '0'         COMMENT '課程ID',
  `title`                 text                                                     NOT NULL                     COMMENT '同儕作業名稱',
  `sort`                  int(10) unsigned                                         NOT NULL DEFAULT '0'         COMMENT '排序',
  `type`                  tinyint(3) unsigned                                      NOT NULL DEFAULT '0'         COMMENT '作業用途 (1:自我評量, 2:平時測驗, 3:正式考試, 4:線上測驗)',
  `modifiable`            enum('N','Y')                                            NOT NULL DEFAULT 'N'         COMMENT '可否重複繳交',
  `publish`               enum('prepare','action','close')                         NOT NULL DEFAULT 'prepare'   COMMENT '作業狀態 (prepare: 準備中, action: 作用中, close: 已關閉)',
  `begin_time`            datetime                                                 DEFAULT NULL                 COMMENT '作答開放日期',
  `close_time`            datetime                                                 DEFAULT NULL                 COMMENT '作答結束日期',
  `count_type`            enum('first','last','max','min','average')               NOT NULL DEFAULT 'first'     COMMENT '計分型態(first: 取第一次, last: 取最後一次, max: 取最高分, min: 取最低分, average: 取平均分)',
  `percent`               float unsigned                                           NOT NULL DEFAULT '0'         COMMENT '比重',
  `do_times`              smallint(5) unsigned                                     NOT NULL DEFAULT '1'         COMMENT '測驗次數 (用不到)',
  `do_interval`           smallint(5) unsigned                                     NOT NULL DEFAULT '60'        COMMENT '測驗時間 (用不到)',
  `item_per_page`         smallint(5) unsigned                                     NOT NULL DEFAULT '0'         COMMENT '每頁幾題 (用不到)',
  `ctrl_paging`           enum('none','can_return','lock')                         NOT NULL DEFAULT 'none'      COMMENT '翻頁控制 (用不到)',
  `ctrl_window`           enum('none','lock')                                      NOT NULL DEFAULT 'none'      COMMENT '視窗控制 (用不到)',
  `ctrl_timeout`          enum('none','mark','auto_submit')                        NOT NULL DEFAULT 'none'      COMMENT '逾時控制 (用不到)',
  `announce_type`         enum('never','now','close_time','user_define')           NOT NULL DEFAULT 'never'     COMMENT '開放觀摩(never: 不公布, now: 作答完公布, close_time: 關閉作業後公布, user_define: 自定時間)',
  `announce_time`         datetime                                                 DEFAULT NULL                 COMMENT '自訂成績公佈時間',
  `item_cramble`          set('enable','choice','item','section','random_pick')    DEFAULT NULL                 COMMENT '隨機排題 (用不到)',
  `random_pick`           mediumint(8) unsigned                                    DEFAULT NULL                 COMMENT '隨機選題 (用不到)',
  `setting`               set('upload','anonymity')                                NOT NULL DEFAULT ''          COMMENT '作業額外設定 (anonymity: 不記名作業, upload: 要以附件作答)',
  `notice`                text                                                                                  COMMENT '作答說明/師長叮嚀 (支援HTML)',
  `content`               mediumtext                                                                            COMMENT '作業卷內容 (XML格式)',
  `assess`                text                                                                                  COMMENT 'N-評分標準說明 (支援HTML)',
  `start_date`            datetime                                                 DEFAULT NULL                 COMMENT 'N-評分開放日期',
  `end_date`              datetime                                                 DEFAULT NULL                 COMMENT 'N-評分結束日期',
  `assess_type`           set('peer','self','teacher')                             NOT NULL DEFAULT 'teacher'   COMMENT 'N-評分人員 (peer: 互評, self: 自評, teacher: 老師評)',
  `peer_percent`          float unsigned                                           NOT NULL DEFAULT '0'         COMMENT 'N-互評比重',
  `self_percent`          float unsigned                                           NOT NULL DEFAULT '0'         COMMENT 'N-自評比重',
  `teacher_percent`       float unsigned                                           NOT NULL DEFAULT '0'         COMMENT 'N-老師評比重',
  `peer_times`            smallint(5) unsigned                                     DEFAULT NULL                 COMMENT 'N-互評最小份數',
  `assess_way`            int(10) unsigned                                         NOT NULL DEFAULT '0'         COMMENT 'N-評分方式 (0: 開放給分, 其它值: 評量表ID)',
  `assess_relation`       tinyint(1)                                               NOT NULL DEFAULT '0'         COMMENT 'N-評分的優先順序 (0:沒有優先順序, 1:先互評再自評, 2:先自評再互評)',
  `creator`               varchar(32)                                               NOT NULL                    COMMENT 'N-建立者',
  `operator`              varchar(32)                                               NOT NULL                    COMMENT 'N-修改者',
  `create_time`           datetime                                                  NOT NULL                    COMMENT 'N-新增日時',
  `upd_time`              datetime                                                  NOT NULL                    COMMENT 'N-更新日時',
  PRIMARY KEY (`exam_id`),
  KEY `idx1` (`course_id`,`publish`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='同儕作業試卷' AUTO_INCREMENT=100000002;


/* MOOC同儕互評-同儕作業繳交及結果,同 WM_qti_homework_result,但又再加值一些欄位 */
CREATE TABLE `WM_qti_peer_result` (
  `exam_id`               int(10) unsigned                                          NOT NULL default '0'        COMMENT '同儕作業ID',
  `examinee`              varchar(32)                                               NOT NULL default ''         COMMENT '應試者',
  `time_id`               int(10) unsigned                                          NOT NULL default '1'        COMMENT '作業次',
  `status`                enum('submit','break','revised','publish')                default NULL                COMMENT '作業狀態 (submit: 正常交卷, break: 不正常交卷, revised: 已批改, publish: 已公布)',
  `begin_time`            datetime                                                  default NULL                COMMENT '開始作業時間',
  `submit_time`           datetime                                                  default NULL                COMMENT '繳交作業時間',
  `score`                 float                                                     default NULL                COMMENT '分數 (老師評,可為小數點)',
  `comment`               mediumtext                                                                            COMMENT '老師講評 (支援HTML)',
  `content`               mediumtext                                                                            COMMENT '繳交內容 (XML格式)',
  `ref_url`               varchar(128)                                              default NULL                COMMENT '?考網址',
  `ref_file`              varchar(255)                                              default NULL                COMMENT '?考檔案',
  `creator`               varchar(32)                                               NOT NULL                    COMMENT 'N-批改者',
  `operator`              varchar(32)                                               NOT NULL                    COMMENT 'N-修改者',
  `create_time`           datetime                                                  NOT NULL                    COMMENT 'N-新增日時',
  `upd_time`              datetime                                                  NOT NULL                    COMMENT 'N-更新日時',
  `total_score`           float                                                     default NULL                COMMENT 'N-總分 (互評/自評/老師評的比重加乘總分)',
  `comment_txt`           mediumtext                                                                            COMMENT 'N-老師評語 (限純文字)',
  PRIMARY KEY  (`exam_id`,`examinee`,`time_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='同儕作業繳交及結果';


/* MOOC同儕互評-作業批改-互評&自評 */
CREATE TABLE `WM_qti_peer_result_score` (
  `exam_id`               int(10) unsigned                                          NOT NULL default '0'        COMMENT '同儕作業ID',
  `examinee`              varchar(32)                                               NOT NULL default ''         COMMENT '應試者 (帳號)',
  `time_id`               int(10) unsigned                                          NOT NULL default '1'        COMMENT '作業次',
  `score_type`            tinyint(1)                                                NOT NULL DEFAULT '0'        COMMENT '互評或自評 (0: 互評, 1: 自評)',
  `score`                 float                                                     default NULL                COMMENT '分數 (限正整數)',
  `comment_txt`           mediumtext                                                                            COMMENT '評語 (限純文字)',
  `creator`               varchar(32)                                               NOT NULL                    COMMENT '批改者',
  `operator`              varchar(32)                                               NOT NULL                    COMMENT '修改者',
  `create_time`           datetime                                                  NOT NULL                    COMMENT '新增日時',
  `upd_time`              datetime                                                  NOT NULL                    COMMENT '更新日時',
  PRIMARY KEY  (`exam_id`,`examinee`,`time_id`,`creator`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='作業批改-互評&自評';


/* MOOC同儕互評-作業批改-互評&自評(action機制) */
CREATE TABLE `WM_qti_peer_result_action` (
  `exam_id`               int(10) unsigned                                          NOT NULL default '0'        COMMENT '同儕作業ID',
  `examinee`              varchar(32)                                               NOT NULL default ''         COMMENT '應試者 (帳號)',
  `time_id`               int(10) unsigned                                          NOT NULL default '1'        COMMENT '作業次',
  `idx`                   varchar(32) character set utf8 collate utf8_bin           NOT NULL default ''         COMMENT 'SESSION IDX',
  `creator`               varchar(32)                                               NOT NULL                    COMMENT '批改者',
  `create_time`           datetime                                                  NOT NULL                    COMMENT '新增日時',
  PRIMARY KEY  (`exam_id`,`examinee`,`time_id`,`creator`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='作業批改-互評&自評(action機制)';


/* MOOC同儕互評-作業批改-評量表 */
CREATE TABLE `WM_qti_peer_result_eva` (
  `exam_id`               int(10) unsigned                                          NOT NULL default '0'        COMMENT '同儕作業ID',
  `examinee`              varchar(32)                                               NOT NULL default ''         COMMENT '應試者',
  `time_id`               int(10) unsigned                                          NOT NULL default '1'        COMMENT '作業次',
  `creator`               varchar(32)                                               NOT NULL                    COMMENT '批改者',
  `eva_id`                int(10) unsigned                                          NOT NULL                    COMMENT '評量表ID',
  `point_id`              int(10) unsigned                                          NOT NULL                    COMMENT '指標ID',
  `level_id`              int(10) unsigned                                          NOT NULL                    COMMENT '級距ID',
  `score_type`            tinyint(1)                                                NOT NULL DEFAULT '0'        COMMENT '互評或自評 (0: 互評, 1: 自評, 2:老師評)',
  PRIMARY KEY  (`exam_id`,`examinee`,`time_id`,`creator`,`point_id`,`score_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='作業批改-評量表';


/* MOOC同儕互評-評量表-主表 */
CREATE TABLE `WM_evaluation` (
  `eva_id`                int(10) unsigned                      NOT NULL AUTO_INCREMENT      COMMENT '評量表ID',
  `caption`               varchar(256)                          NOT NULL                     COMMENT '評量表名稱',
  `enable`                tinyint(1)                            NOT NULL DEFAULT '0'         COMMENT '暫存或啟用 (0: 暫存, 1: 啟用)',
  `creator`               varchar(32)                           NOT NULL                     COMMENT '建立者',
  `operator`              varchar(32)                           NOT NULL                     COMMENT '修改處理者',
  `create_time`           datetime                              NOT NULL                     COMMENT '新增日時',
  `upd_time`              datetime                              NOT NULL                     COMMENT '更新日時',
  PRIMARY KEY  (`eva_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='評量表-主表';


/* MOOC同儕互評-評量表-級距 */
CREATE TABLE `WM_evaluation_level` (
  `level_id`     int(10) unsigned      NOT NULL AUTO_INCREMENT  COMMENT '級距ID',
  `eva_id`       int(10) unsigned      NOT NULL                 COMMENT '評量表ID',
  `caption`      varchar(256)          NOT NULL                 COMMENT '級距名稱',
  `score`        float                 default NULL             COMMENT '分數',
  `permute`      int(4)  unsigned      NOT NULL                 COMMENT '排序',
  PRIMARY KEY (`level_id`, `eva_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='評量表-級距';


/* MOOC同儕互評-評量表-指標名稱 */
CREATE TABLE `WM_evaluation_point` (
  `point_id`     int(10) unsigned      NOT NULL AUTO_INCREMENT  COMMENT '指標ID',
  `eva_id`       int(10) unsigned      NOT NULL                 COMMENT '評量表ID',
  `caption`      varchar(256)          NOT NULL                 COMMENT '指標名稱',
  `permute`      int(4)  unsigned      NOT NULL                 COMMENT '排序',
  PRIMARY KEY (`point_id`, `eva_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='評量表-指標名稱';


/* MOOC同儕互評-評量表-指標說明 */
CREATE TABLE `WM_evaluation_point_note` (
  `point_id`     int(10) unsigned      NOT NULL                 COMMENT '指標ID',
  `level_id`     int(10) unsigned      NOT NULL                 COMMENT '級距ID',
  `note`         text                  NOT NULL                 COMMENT '說明',
  PRIMARY KEY (`point_id`, `level_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='評量表-指標說明';

/* 區分值 */
CREATE TABLE `WM_div_master` (
    `type_id`     varchar(32)            NOT NULL                 COMMENT '區分種別編號',
    `value`       varchar(20)            NOT NULL                 COMMENT '區分值',
    `lang_code`   varchar(12)            NOT NULL                 COMMENT '語言別',
    `type_name`   varchar(60)            NOT NULL                 COMMENT '區分種別名稱',
    `value_name`  varchar(100)           NOT NULL                 COMMENT '區分值名稱',
    `show_order`  tinyint(3) unsigned    NOT NULL                 COMMENT '呈現順序',
    PRIMARY KEY (`type_id`, `value`, `lang_code`),
    KEY `type_id_lang_code` (`type_id`, `lang_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='區分值';

/* 事件定義 */
CREATE TABLE `WM_event_type` (
    `code`           varchar(32)           NOT NULL                 COMMENT '事件代碼',
    `lang_code`      varchar(12)                                    COMMENT '語言',
    `caption`        varchar(32)           NOT NULL                 COMMENT '事件名稱',
    PRIMARY KEY (`code`, `lang_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='事件定義';

/* 事件記錄 */
CREATE TABLE `WM_event_log` (
    `event_code`     varchar(32)                          NOT NULL      COMMENT '事件代碼',
    `level`          varchar(4)                           NOT NULL      COMMENT '類型{LOG, INFO, WARN, ERR, REG}',
    `log`            varchar(128)                         NOT NULL      COMMENT '事件內容',
    `ip_address`     varchar(23)                          NOT NULL      COMMENT 'IP位置',
    `creator`        varchar(32)                          NOT NULL      COMMENT '建立者',
    `operator`       varchar(32)                          NOT NULL      COMMENT '修改處理者',
    `create_time`    datetime                             NOT NULL      COMMENT '建立時間',
    `upd_time`       datetime                             NOT NULL      COMMENT '更新時間',
    KEY `event_code` (`event_code`),
    KEY `create_time` (`create_time`),
    KEY `event_code_create_time` (`event_code`, `create_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='事件記錄';


/*LCMS模組-跟WMPRO或MOOC整合的課程複製精靈*/
/* DROP TABLE IF EXISTS `lcms_course_wizard`; */
CREATE TABLE `lcms_course_wizard` (
  `source_course_id` int(10) NOT NULL,
  `target_course_id` int(10) NOT NULL,
  `lcms_course_id` int(10) NOT NULL,
  `trans_course_path` longtext NOT NULL,
  `before_course_path` longtext NOT NULL,
  `creater` varchar(32) NOT NULL,
  `editor` varchar(32) NOT NULL,
  `post_data` text NOT NULL,
  `add_time` datetime NOT NULL,
  `upd_time` datetime NOT NULL,
  PRIMARY KEY (`target_course_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='LCMS的課程複製精靈';

/* 因應續考功能新增資料表*/
CREATE TABLE `WM_qti_exam_result_extra` (
  `exam_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '試卷編號',
  `examinee` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '帳號',
  `time_id` int(10) unsigned NOT NULL COMMENT '第幾次測驗',
  `subtime` int(10) NOT NULL DEFAULT '0' COMMENT '試卷剩餘測驗時間',
  `curpage` int(5) unsigned NOT NULL DEFAULT '1' COMMENT '最後離開的頁數',
  `content` mediumtext COMMENT '歷次測驗的時間',
  PRIMARY KEY (`exam_id`,`examinee`,`time_id`),
  KEY `idx1` (`subtime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*CN347-首頁portal*/
/* DROP TABLE IF EXISTS `WM_portal`; */
CREATE TABLE `WM_portal` (
  `portal_id` 		varchar(20)	NOT NULL DEFAULT '' COMMENT '設定項目(ex: adv001, 代碼保留三位)',
  `key` 	varchar(20)	NOT NULL DEFAULT ''  COMMENT '屬性',
  `value`	varchar(255)	NOT NULL DEFAULT '' COMMENT '值',
  PRIMARY KEY (`portal_id`,`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='首頁portal參數';

/* 討論版附註（留言） */
CREATE TABLE `WM_bbs_whispers` (
  `wid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '附註流水號',
  `site` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所屬校代碼',
  `board_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '討論版編號',
  `node` varchar(19) NOT NULL DEFAULT '' COMMENT '單篇文章編號',
  `content` text COMMENT '本文',
  `lang` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '張貼語言',
  `creator` varchar(32) NOT NULL DEFAULT '' COMMENT '建立者',
  `creator_realname` varchar(32) NOT NULL DEFAULT '' COMMENT '建立者姓名',
  `creator_email` varchar(64) DEFAULT NULL COMMENT '建立者 Email',
  `create_time` datetime DEFAULT NULL COMMENT '建立日期',
  `operator` varchar(32) NOT NULL COMMENT '異動者',
  `operator_realname` varchar(32) NOT NULL DEFAULT '' COMMENT '異動者姓名',
  `operator_email` varchar(64) DEFAULT NULL COMMENT '異動者 Email',
  `upd_time` datetime DEFAULT NULL COMMENT '異動日期',
  PRIMARY KEY (`wid`),
  KEY `site` (`site`,`board_id`,`node`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='討論版附註' AUTO_INCREMENT=1 ;

/* WM_term_course 新增 path_type 欄位，儲存學習節點類型 */
ALTER TABLE  `WM_term_course` ADD `path_type` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT  '儲存學習節點類型，0:未設定；1:自訂課程；2:短期課程；3:一般課程' AFTER  `ta_can_sets` ;

/* WM_term_course 新增 exam_num 欄位，儲存自我評量題數 */
ALTER TABLE  `WM_term_course` ADD `exam_num`  INT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT  '儲存自我評量題數，0:不啟用；1-n: 顯示幾題' AFTER  `path_type`;

/* CN347-重點的筆記 */
/* DROP TABLE IF EXISTS `WM_user_note`; */
CREATE TABLE `WM_user_note` (
  `note_id`   bigint(20)      unsigned NOT NULL AUTO_INCREMENT  COMMENT '筆記ID',
  `username`  varchar(32)   CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '帳號',  
  `course_id`   int(10)         unsigned NOT NULL DEFAULT '0'   COMMENT '課程ID',
  `course_name` varchar(255)  NOT NULL DEFAULT ''                 COMMENT '課程名稱',
  `sco_id`      varchar(255)  NOT NULL DEFAULT ''                 COMMENT 'SCORM ID',
  `title`       varchar(255)  NOT NULL DEFAULT ''                 COMMENT '筆記標題',
  `url`         varchar(255)  NOT NULL DEFAULT ''                 COMMENT '素材網址',
  `point_time`  int(11)         NOT NULL DEFAULT '0'                COMMENT '重點記錄時間',
  `image_name`  text                                                COMMENT '圖片名稱',
  `asset_id`  varchar(255)  NOT NULL DEFAULT ''                 COMMENT '素材ID',
  `memo`        text            NOT NULL DEFAULT ''                 COMMENT '筆記',
  `create_time` datetime        NOT NULL DEFAULT '0000-00-00 00:00:00'  COMMENT '建立時間',
  `update_time` datetime        NOT NULL DEFAULT '0000-00-00 00:00:00'  COMMENT '更新時間',
  `review_cnt`  int(5)          unsigned DEFAULT '0'                COMMENT '複習次數',
  PRIMARY KEY (`note_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='重點的筆記';

/* CN347-張貼重點筆記 */
/* DROP TABLE IF EXISTS `WM_user_note_post`; */
CREATE TABLE `WM_user_note_post` (
  `note_id`     int(10)         unsigned NOT NULL DEFAULT '0'   COMMENT '筆記ID',
  `board_id`  bigint(20)      unsigned NOT NULL DEFAULT '0',
  `node`        varchar(19)   NOT NULL DEFAULT '',
  `site`        int(10)         unsigned NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='張貼重點筆記';

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
ALTER TABLE  `WM_bbs_boards` 
ADD  `poster` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT  '發表人員',
ADD  `after_finish` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT  '結束後討論區開啟或關閉',
ADD  `fb_comment` VARCHAR(1) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'N' COMMENT '啟用 Facebook 留言';

/* 課程設定 */
ALTER TABLE `WM_term_course` ADD `course_type` varchar(10) DEFAULT NULL COMMENT '課程類型';
ALTER TABLE `WM_term_course` ADD `subhead` varchar(150) DEFAULT NULL COMMENT '副標題';
ALTER TABLE `WM_term_course` ADD `fee` int(10) DEFAULT NULL COMMENT '費用';
ALTER TABLE `WM_term_course` ADD `st_limit` tinyint(1) DEFAULT NULL COMMENT '非輔導期間是否對學員開放';
ALTER TABLE `WM_term_course` ADD `goal` text COMMENT '目標';
ALTER TABLE `WM_term_course` ADD `audience` text COMMENT '聽眾';
ALTER TABLE `WM_term_course` ADD `formal_pass` text COMMENT '一般生通過條件';
ALTER TABLE `WM_term_course` ADD `gallery_pass` text COMMENT '見習生通過條件';
ALTER TABLE `WM_term_course` ADD `is_use` text COMMENT '目標與對象是否啟用設定';
ALTER TABLE `WM_term_course` ADD `allow_comment` int(1) DEFAULT '0' COMMENT '是否開放評論區';
ALTER TABLE `WM_term_course` ADD `ref_title` text COMMENT '參考資料標題';
ALTER TABLE `WM_term_course` ADD `ref_url` text COMMENT '參考資料網址';


/* 修改個人設定預設值 */
ALTER TABLE  `WM_user_account` CHANGE  `gender`  `gender` ENUM(  'F',  'M',  'N' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'N';

/* 自我評量統計 (從LCMS同步回來的匯總資料)*/
CREATE TABLE `LM_quiz_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID（流水號）',
  `qid` bigint(20) unsigned NOT NULL COMMENT '題目代碼',
  `system_ip` varchar(45) NOT NULL COMMENT '記錄來源ip',
  `system_title` varchar(255) NOT NULL COMMENT '記錄來源標題',
  `user_id` varchar(255) NOT NULL COMMENT '帳號(LCMS或LMS)',
  `user_title` varchar(255) NOT NULL COMMENT '姓名',
  `from_ip` varchar(45) NOT NULL COMMENT '使用者做此記錄時的client ip',
  `time_start` datetime NOT NULL COMMENT '記錄問題開始的時間(在影片中看到問題的開始時間)',
  `time_end` datetime DEFAULT NULL COMMENT '記錄問題結束的時間(在影片中問題結束的時間，一般狀況下，也就是回答此問題的時間)',
  `answer` smallint(6) NOT NULL COMMENT '回答(-1.skip(預設),0.錯誤,1.正確)',
  `course_id` int(8) DEFAULT NULL COMMENT 'WMPro課程ID',
  `aid` bigint(20) NOT NULL COMMENT '素材編號',
  `subject` varchar(255) NOT NULL COMMENT '素材標題',
  `question` text NOT NULL COMMENT '題目',
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='學員答題記錄' ;

/* 個人設定 WM_user_account 新增 身分 學歷 國家來源 欄位，儲存給使用者統計報表用 */
ALTER TABLE `WM_user_account` ADD `user_status` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '身分(學生S, 在職W)' AFTER `hid` ,
ADD `education` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '學歷(小學P, 中學H, 高中S, 大學U, 碩士M, 博士D, 其他O)' AFTER `user_status` ,
ADD `country` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '國家來源(國碼)' AFTER `education` ;

/* #58676新版行事曆程式部分 begin*/
/* 增加repeat_begin欄位紀錄週期事件的開始日(不用透過parent_idx取得) */
ALTER TABLE `WM_calendar` ADD `repeat_begin` DATE NULL DEFAULT '0000-00-00' AFTER `repeat_freq` ;
/* 增加relative_type,relative_id欄位紀錄對應的課程或作業/測驗 */
ALTER TABLE `WM_calendar`
ADD `relative_type` ENUM( 'course_begin','course_end','homework_begin','homework_end','exam_begin','exam_end' ) NULL DEFAULT NULL ,
ADD `relative_id` INT( 10 ) NULL DEFAULT NULL ;
/* 行事曆是否要顯示需要儲存在DB(個人.課程.學校)(不會連動到Email提醒) */
ALTER TABLE `WM_cal_setting` CHANGE `also_show` `also_show` SET( 'person', 'course', 'school' ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
/* #58676新版行事曆程式部分 end*/


/* 筆記最後一次閱讀紀錄 */
CREATE TABLE `WM_notebook_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水號',
  `username` varchar(32) NOT NULL COMMENT 'WM_user_account.username',
  `fid` varchar(32) NOT NULL COMMENT 'WM_msg_message.folder_id',
  `mid` bigint(20) unsigned NOT NULL COMMENT 'WM_msg_message.msg_serial',
  `creator` varchar(32) NOT NULL,
  `create_time` datetime NOT NULL,
  `operator` varchar(32) NOT NULL,
  `upd_time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `username` (`username`,`fid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='筆記最後一次閱讀紀錄' AUTO_INCREMENT=1 ;

/* 新增課程建立者 */
ALTER TABLE  `WM_term_course` ADD  `creator` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_bin NULL COMMENT  '建立者' AFTER  `ta_can_sets` ;

/* 筆記本 begin */
CREATE TABLE `APP_note_share` (
  `share_key` varchar(32) NOT NULL COMMENT '筆記分享Key',
  `folder_id` varchar(32) NOT NULL,
  `msg_serial` bigint(20) NOT NULL,
  `owner` varchar(32) NOT NULL COMMENT '分享者',
  `due_time` int(10) NOT NULL COMMENT '分享結束時間',
  PRIMARY KEY (`share_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `APP_note_action_history` (
  `username` varchar(32) NOT NULL COMMENT '帳號',
  `log_time` int(10) NOT NULL COMMENT '動作時間',
  `action` set('A','M','D') NOT NULL COMMENT 'A新增,M修改,D刪除',
  `folder_id` varchar(32) NOT NULL COMMENT '筆記本ID',
  `msg_serial` bigint(20) NOT NULL COMMENT '筆記ID',
  `from` set('server','client') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/* 筆記本 end */

UPDATE `WM_bbs_boards` set `open_time`='0000-00-00 00:00:00' WHERE `open_time` is NULL;
UPDATE `WM_bbs_boards` set `close_time`='0000-00-00 00:00:00' WHERE `close_time` is NULL;
UPDATE `WM_bbs_boards` set `share_time`='0000-00-00 00:00:00' WHERE `share_time` is NULL;
UPDATE `WM_bbs_boards` set `poster`='student,assistant,teacher' WHERE `poster`='';

/* 修正新開課程或新增討論版的討論版沒有預設值 */
 ALTER TABLE `WM_bbs_boards` CHANGE `open_time` `open_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
CHANGE `close_time` `close_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
CHANGE `share_time` `share_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
CHANGE `poster` `poster` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'student,assistant,teacher' COMMENT '發表人員';

ALTER TABLE `WM_bbs_readed` ADD INDEX ( `board_id` , `username` );

/* 記錄使用者 - qti未進行的次數 */
CREATE TABLE IF NOT EXISTS `WM_qti_check_undo` (
  `username` varchar(32) NOT NULL,
  `homework` int(3) NOT NULL DEFAULT '0',
  `exam` int(3) NOT NULL DEFAULT '0',
  `check_time` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/* 記錄使用者 - 未讀文章檢查時間 */
CREATE TABLE IF NOT EXISTS `WM_board_check_undo` (
  `username` varchar(32) NOT NULL,
  `board` bigint(20) unsigned NOT NULL DEFAULT '0',
  `check_time` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/* 新版的課程複製精靈 */
CREATE TABLE `CO_qti_pack_log` (
  `serial_no` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `log_stime` datetime NOT NULL,
  `log_etime` datetime DEFAULT NULL,
  `source_course_id` int(10) unsigned NOT NULL,
  `source_caption` text NOT NULL,
  `destination_course_id` int(10) unsigned NOT NULL,
  `state` enum('0','1') NOT NULL DEFAULT '0',
  `note` text NOT NULL,
  PRIMARY KEY (`serial_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

/**
 * 資安擋除ip
 */
CREATE TABLE `WM_blocked_attack_ip` (
  `blocked_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水號',
  `username` varchar(32) NOT NULL COMMENT '封鎖當下的帳號',
  `start_time` datetime NOT NULL COMMENT '封鎖的起始時間',
  `end_time` datetime NOT NULL COMMENT '封鎖的結束時間',
  `blocked_ip_address` varchar(16) NOT NULL COMMENT '封鎖的ip',
  `create_time` datetime NOT NULL COMMENT '此筆資料建立時間',
  `blocked_count` int(11) NOT NULL COMMENT '封鎖期間被拒絕存取的次數',
  `blocked_log_filepath` varchar(200) NOT NULL COMMENT '拒絕存取時記錄使用者傳參的log',
  PRIMARY KEY (`blocked_id`),
  KEY `blocked_ip_address` (`blocked_ip_address`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='封鎖有攻擊行為的ip' AUTO_INCREMENT=1;

/* 增加索引-增加查詢速度 */
ALTER TABLE  `WM_term_subject` ADD INDEX `idx2` (`board_id`);

/* 作業增加補繳期限 */
ALTER TABLE  `WM_qti_homework_test` ADD  `delay_time` DATETIME NULL DEFAULT '0000-00-00 00:00:00' COMMENT  '補繳期限' AFTER  `close_time` ;

/* 作業增加上傳附件必要選項 */
ALTER TABLE  `WM_qti_homework_test` CHANGE  `setting`  `setting` SET(  'upload',  'anonymity',  'required' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

/* 作業維護資料表，新增 create_time 欄位 */
ALTER TABLE  `WM_qti_homework_test` ADD  `create_time` DATETIME NOT NULL ;
ALTER TABLE  `WM_qti_exam_test` ADD  `create_time` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ;
ALTER TABLE  `WM_qti_questionnaire_test` ADD  `create_time` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ;


/* 行事曆「事件類別」增加「作業逾期期限」 */
ALTER TABLE  `WM_calendar` CHANGE  `relative_type`  `relative_type` ENUM(  'course_begin',  'course_end',  'homework_begin',  'homework_end',  'exam_begin',  'exam_end',  'homework_delay' ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

ALTER TABLE `WM_log_manager` ADD INDEX `idx_WM_log_manager_log_time` (`log_time`);

ALTER TABLE `WM_log_teacher` ADD INDEX `idx_WM_log_teacher_log_time` (`log_time`);

/* 調整 WM_log_*.instance 欄位格式，以方便記錄log */
/* 資料表筆數巨大時，進行本指令會造成資料庫負載過大，應評估實際狀況考慮在離峰時間執行 */
 ALTER TABLE `WM_log_classroom` CHANGE `instance` `instance` VARCHAR( 20 ) NOT NULL DEFAULT '0';
 ALTER TABLE `WM_log_director` CHANGE `instance` `instance` VARCHAR( 20 ) NOT NULL DEFAULT '0';
 ALTER TABLE `WM_log_manager` CHANGE `instance` `instance` VARCHAR( 20 ) NOT NULL DEFAULT '0';
 ALTER TABLE `WM_log_others` CHANGE `instance` `instance` VARCHAR( 20 ) NOT NULL DEFAULT '0';
 ALTER TABLE `WM_log_teacher` CHANGE `instance` `instance` VARCHAR( 20 ) NOT NULL DEFAULT '0';

/* 新增 討論版/新增修改刪除留言 acl */
INSERT INTO `WM_acl_function` (`function_id`, `caption`, `scope`, `default_permission`)
VALUES ('2700200500', '討論版→使用討論版→張貼留言', 'learn,teach,academic', 'enable,visible,readable,writable,modifiable,uploadable,removable'), 
       ('2700200600', '討論版→使用討論版→修改留言', 'learn,teach,academic', 'enable,visible,readable,writable,modifiable,uploadable,removable'), 
       ('2700200700', '討論版→使用討論版→刪除留言', 'learn,teach,academic', 'enable,visible,readable,writable,modifiable,uploadable,removable');

/* #032682 點[未分組課程]會執行很久 */
ALTER TABLE `WM_term_group` ADD INDEX `child` (`child`);

/* *48572 雲科大 - 平台非常緩慢（於 WM_term_subject 多增加 board_id INDEX 來改善效能) */
ALTER TABLE  `WM_term_subject` ADD INDEX  `WM_term_subject_idx_boardid` (  `board_id` );

/*
加寬 user_agent 欄位寬度
應於夜間設排程調整資料表結構
*/
ALTER TABLE `WM_log_userAgent` CHANGE `agent_id` `agent_id` MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `WM_log_classroom` CHANGE `user_agent` `user_agent` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `WM_log_others` CHANGE `user_agent` `user_agent` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `WM_log_teacher` CHANGE `user_agent` `user_agent` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `WM_log_director` CHANGE `user_agent` `user_agent` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `WM_log_manager` CHANGE `user_agent` `user_agent` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0';

/*
調整版冊章節段，支援長度到 4 個字元
*/
ALTER TABLE `WM_qti_exam_item` CHANGE `version` `version` INT( 4 ) UNSIGNED NULL DEFAULT NULL ,
CHANGE `volume` `volume` INT( 4 ) UNSIGNED NULL DEFAULT NULL ,
CHANGE `chapter` `chapter` INT( 4 ) UNSIGNED NULL DEFAULT NULL ,
CHANGE `paragraph` `paragraph` INT( 4 ) UNSIGNED NULL DEFAULT NULL ,
CHANGE `section` `section` INT( 4 ) UNSIGNED NULL DEFAULT NULL;

ALTER TABLE `WM_qti_homework_item` CHANGE `version` `version` INT( 4 ) UNSIGNED NULL DEFAULT NULL ,
CHANGE `volume` `volume` INT( 4 ) UNSIGNED NULL DEFAULT NULL ,
CHANGE `chapter` `chapter` INT( 4 ) UNSIGNED NULL DEFAULT NULL ,
CHANGE `paragraph` `paragraph` INT( 4 ) UNSIGNED NULL DEFAULT NULL ,
CHANGE `section` `section` INT( 4 ) UNSIGNED NULL DEFAULT NULL;

ALTER TABLE `WM_qti_questionnaire_item` CHANGE `version` `version` INT( 4 ) UNSIGNED NULL DEFAULT NULL ,
CHANGE `volume` `volume` INT( 4 ) UNSIGNED NULL DEFAULT NULL ,
CHANGE `chapter` `chapter` INT( 4 ) UNSIGNED NULL DEFAULT NULL ,
CHANGE `paragraph` `paragraph` INT( 4 ) UNSIGNED NULL DEFAULT NULL ,
CHANGE `section` `section` INT( 4 ) UNSIGNED NULL DEFAULT NULL;

/*
調整問卷寫入行事曆
*/
ALTER TABLE `WM_calendar` CHANGE `relative_type` `relative_type` ENUM( 'course_begin', 'course_end', 'homework_begin', 'homework_end', 'exam_begin', 'exam_end', 'homework_delay', 'questionnaire_begin', 'questionnaire_end' ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

/**
 * 點名功能
 */
CREATE TABLE `APP_rollcall_base` (
  `rid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '點名編號 (流水號)',
  `course_id` int(10) unsigned NOT NULL COMMENT '課程編號',
  `creator` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '建立者帳號 (老師帳號)',
  `create_time` datetime NOT NULL COMMENT '建立時間',
  `begin_time` datetime NOT NULL COMMENT '該次點名啟用時間',
  `end_time` datetime NOT NULL COMMENT '該次點名結束時間',
  PRIMARY KEY (`rid`),
  KEY `course_id` (`course_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='點名資料基本檔' AUTO_INCREMENT=1;

CREATE TABLE `APP_rollcall_record` (
  `rid` int(11) NOT NULL COMMENT 'from APP_rollcall_base.rid',
  `username` varchar(32) NOT NULL COMMENT '學生帳號',
  `rollcall_time` datetime NOT NULL COMMENT '點名時間',
  `rollcall_status` int(11) NOT NULL COMMENT '點名狀態(1:已點名|2:未點名|3:遲到|4:早退|5:病假|6:公假|7:事假|8:喪假|9:裝置重複)',
  `device_ident` varchar(255) NOT NULL COMMENT '裝置識別碼',
  `memo` text NOT NULL COMMENT '備註',
  `modifier` varchar(32) NOT NULL COMMENT '修改點名狀態的老師帳號',
  `modify_time` datetime NOT NULL COMMENT '修改時間',
  PRIMARY KEY (`rid`,`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='學生的點名狀態';

INSERT INTO `WM_div_master` (`type_id`, `value`, `lang_code`, `type_name`, `value_name`, `show_order`) VALUES 
('rollcall_status', '0', 'Big5', '點名狀態', '未點名', 1),
('rollcall_status', '1', 'Big5', '點名狀態', '已到', 2),
('rollcall_status', '2', 'Big5', '點名狀態', '缺席', 3),
('rollcall_status', '3', 'Big5', '點名狀態', '遲到', 4),
('rollcall_status', '4', 'Big5', '點名狀態', '早退', 5),
('rollcall_status', '5', 'Big5', '點名狀態', '病假', 6),
('rollcall_status', '6', 'Big5', '點名狀態', '公假', 7),
('rollcall_status', '7', 'Big5', '點名狀態', '事假', 8),
('rollcall_status', '8', 'Big5', '點名狀態', '喪假', 9),
('rollcall_status', '9', 'Big5', '點名狀態', '裝置重複', 10);


CREATE TABLE `CO_adv` (
  `adv_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL DEFAULT '10001' COMMENT '站台id',
  `name` varchar(100) NOT NULL COMMENT '廣告名稱',
  `open_date_flag` varchar(1) NOT NULL,
  `open_date` date NOT NULL,
  `close_date_flag` varchar(1) NOT NULL,
  `close_date` date NOT NULL,
  `url` varchar(255) NOT NULL,
  `img_path` varchar(255) NOT NULL COMMENT '圖片路徑',
  `permute` int(10) NOT NULL,
  `create_datetime` datetime NOT NULL,
  `update_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `poster` varchar(32) NOT NULL,
  PRIMARY KEY (`adv_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
INSERT INTO `CO_adv` (`adv_id`, `school_id`, `name`, `open_date_flag`, `open_date`, `close_date_flag`, `close_date`, `url`, `img_path`, `permute`, `create_datetime`, `update_datetime`, `poster`) VALUES 
(1, 10001, 'wmpro51', '0', '0000-00-00', '0', '0000-00-00', '/mooc/index.php', '廣告輪播區/main.png', 1, NOW(), NOW(), 'root'),
(2, 10001, '愛上互動', '0', '0000-00-00', '0', '0000-00-00', 'http://wmpro.sun.net.tw/?p=product_isunfudon', '廣告輪播區/p3_愛上互動.jpg', 2, NOW(), NOW(), 'root'),
(3, 10001, '同儕互評', '0', '0000-00-00', '0', '0000-00-00', '', '廣告輪播區/p4_線上同儕互評.jpg', 5, NOW(), NOW(), 'root'),
(4, 10001, 'LCMS2', '0', '0000-00-00', '0', '0000-00-00', '', '廣告輪播區/p1_教學資料庫.jpg', 4, NOW(), NOW(), 'root');


CREATE TABLE `CO_links` (
  `links_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '廣告名稱',
  `open_date_flag` varchar(1) NOT NULL,
  `open_date` date NOT NULL,
  `close_date_flag` varchar(1) NOT NULL,
  `close_date` date NOT NULL,
  `url` varchar(255) NOT NULL,
  `img_path` varchar(255) NOT NULL COMMENT '圖片路徑',
  `permute` int(10) NOT NULL,
  `create_datetime` datetime NOT NULL,
  `update_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `poster` varchar(32) NOT NULL,
  `school_id` int( 11 ) NOT NULL DEFAULT '10001',
  PRIMARY KEY (`links_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


CREATE TABLE `CO_download` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '流水號',
  `title` varchar(255) NOT NULL COMMENT '下載名稱',
  `attach_path` text NOT NULL COMMENT '下載檔案',
  `open_date_flag` varchar(1) NOT NULL,
  `open_date` date NOT NULL,
  `close_date_flag` varchar(1) NOT NULL,
  `close_date` date NOT NULL,
  `kind` varchar(60) DEFAULT NULL COMMENT '下載類別',
  `creator` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `create_time` datetime NOT NULL,
  `modifier` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `modify_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `delete_flag` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否已刪除(0:否,1:是)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='下載管理' AUTO_INCREMENT=1;

/* 老師編號與課程路徑對應表 */
CREATE TABLE IF NOT EXISTS `WM_content_ta` (
  `username` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `content_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`username`,`content_id`),
  UNIQUE KEY `idx1` (`content_id`,`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `WM_term_subject` CHANGE `state` `state` ENUM( 'disable', 'open', 'taonly', 'public' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'open';

ALTER TABLE  `WM_qti_exam_test` CHANGE  `ctrl_window`  `ctrl_window` ENUM(  'none',  'lock',  'lock2' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'none';

ALTER TABLE `WM_history_user_account` ADD `user_status` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '身分(學生S, 在職W)' AFTER `hid` ,
ADD `education` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '學歷(小學P, 中學H, 高中S, 大學U, 碩士M, 博士D, 其他O)' AFTER `user_status` ,
ADD `country` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '國家來源(國碼)' AFTER `education` ;

/* 管理者環境/課程列表 - 當資料量大時，查詢會緩慢，因為discren_id的型態不同於join的課程編號 */
ALTER TABLE  `WM_review_flow` CHANGE  `discren_id`  `discren_id` INT NOT NULL;
ALTER TABLE  `WM_review_flow` ADD INDEX (`discren_id`);
ALTER TABLE  `WM_review_sysidx` CHANGE  `discren_id`  `discren_id` INT NOT NULL;
ALTER TABLE  `WM_review_sysidx` ADD INDEX (`discren_id`);

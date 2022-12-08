SET NAMES utf8;
SET foreign_key_checks = 1;

USE `WM52_ARMY_10001`;

/* 評量表-級距 */
INSERT INTO `WM_div_master` (`type_id`, `value`, `lang_code`, `type_name`, `value_name`, `show_order`) VALUES
  ('eva_level', '1', 'Big5', '類別', '傑出',1),
  ('eva_level', '2', 'Big5', '類別', '滿意',2),
  ('eva_level', '3', 'Big5', '類別', '尚可',3),
  ('eva_level', '4', 'Big5', '類別', '不滿意',4),
  ('eva_level', '1', 'GB2312', '类别', '杰出',1),
  ('eva_level', '2', 'GB2312', '类别', '满意',2),
  ('eva_level', '3', 'GB2312', '类别', '尚可',3),
  ('eva_level', '4', 'GB2312', '类别', '不满意',4),
  ('eva_level', '1', 'en', 'level', 'excess',1),
  ('eva_level', '2', 'en', 'level', 'satisfactory',2),
  ('eva_level', '3', 'en', 'level', 'low',3),
  ('eva_level', '4', 'en', 'level', 'unsatisfactory',4);

/* 評量表-級距的預設值 */
INSERT INTO `WM_div_master` (`type_id`, `value`, `lang_code`, `type_name`, `value_name`, `show_order`) VALUES
  ('eva_level_value', '1', 'Big5', '類別值', '20',1),
  ('eva_level_value', '2', 'Big5', '類別值', '15',2),
  ('eva_level_value', '3', 'Big5', '類別值', '10',3),
  ('eva_level_value', '4', 'Big5', '類別值', '5',4),
  ('eva_level_value', '1', 'GB2312', '类别值', '20',1),
  ('eva_level_value', '2', 'GB2312', '类别值', '15',2),
  ('eva_level_value', '3', 'GB2312', '类别值', '10',3),
  ('eva_level_value', '4', 'GB2312', '类别值', '5',4),
  ('eva_level_value', '1', 'en', 'level value', '20',1),
  ('eva_level_value', '2', 'en', 'level value', '15',2),
  ('eva_level_value', '3', 'en', 'level value', '10',3),
  ('eva_level_value', '4', 'en', 'level value', '5',4);

/* 評量表-指標 */
INSERT INTO `WM_div_master` (`type_id`, `value`, `lang_code`, `type_name`, `value_name`, `show_order`) VALUES
  ('eva_point', '1', 'Big5', '指標', '觀察',1),
  ('eva_point', '2', 'Big5', '指標', '觀察品質',2),
  ('eva_point', '3', 'Big5', '指標', '引用附件數量',3),
  ('eva_point', '4', 'Big5', '指標', '意見數量',4),
  ('eva_point', '5', 'Big5', '指標', '意見品質',5),
  ('eva_point', '1', 'GB2312', '指标', '观察',1),
  ('eva_point', '2', 'GB2312', '指标', '观察品质',2),
  ('eva_point', '3', 'GB2312', '指标', '引用附件数量',3),
  ('eva_point', '4', 'GB2312', '指标', '意见数量',4),
  ('eva_point', '5', 'GB2312', '指标', '意见品质',5),
  ('eva_point', '1', 'en', 'point', 'observation',1),
  ('eva_point', '2', 'en', 'point', 'quality observation',2),
  ('eva_point', '3', 'en', 'point', 'a reference to the number of attachments',3),
  ('eva_point', '4', 'en', 'point', 'the number of views',4),
  ('eva_point', '5', 'en', 'point', 'views of quality',5);

/* 評量表-指標說明 */
INSERT INTO `WM_div_master` (`type_id`, `value`, `lang_code`, `type_name`, `value_name`, `show_order`) VALUES
  ('eva_point_note', '1-1', 'Big5', '指標說明', '有超過5觀察',1),
  ('eva_point_note', '1-2', 'Big5', '指標說明', '有3-4觀察',2),
  ('eva_point_note', '1-3', 'Big5', '指標說明', '有1-2觀察',3),
  ('eva_point_note', '1-4', 'Big5', '指標說明', '只有低於1個觀察',4),
  ('eva_point_note', '2-1', 'Big5', '指標說明', '全部觀察具有洞悉特性',1),
  ('eva_point_note', '2-2', 'Big5', '指標說明', '大部分觀察具有洞悉特性',2),
  ('eva_point_note', '2-3', 'Big5', '指標說明', '部分觀察具有洞悉特性',3),
  ('eva_point_note', '2-4', 'Big5', '指標說明', '很少觀察具有洞悉特性',4),
  ('eva_point_note', '3-1', 'Big5', '指標說明', '附上3以上引用來源',1),
  ('eva_point_note', '3-2', 'Big5', '指標說明', '附上2-3引用來源',2),
  ('eva_point_note', '3-3', 'Big5', '指標說明', '附上1個引用來源',3),
  ('eva_point_note', '3-4', 'Big5', '指標說明', '沒有附上引用來源',4),
  ('eva_point_note', '4-1', 'Big5', '指標說明', '有超過15意見',1),
  ('eva_point_note', '4-2', 'Big5', '指標說明', '有10-15意見',2),
  ('eva_point_note', '4-3', 'Big5', '指標說明', '有5-10意見',3),
  ('eva_point_note', '4-4', 'Big5', '指標說明', '只有低於5個意見',4),
  ('eva_point_note', '5-1', 'Big5', '指標說明', '全部意見具有洞悉特性',1),
  ('eva_point_note', '5-2', 'Big5', '指標說明', '大部分意見具有洞悉特性',2),
  ('eva_point_note', '5-3', 'Big5', '指標說明', '部分意見具有洞悉特性',3),
  ('eva_point_note', '5-4', 'Big5', '指標說明', '很少意見具有洞悉特性',4),
  ('eva_point_note', '1-1', 'GB2312', '指标说明', '有超过5观察',1),
  ('eva_point_note', '1-2', 'GB2312', '指标说明', '有3-4观察',2),
  ('eva_point_note', '1-3', 'GB2312', '指标说明', '有1-2观察',3),
  ('eva_point_note', '1-4', 'GB2312', '指标说明', '只有低于1个观察',4),
  ('eva_point_note', '2-1', 'GB2312', '指标说明', '全部观察具有洞悉特性',1),
  ('eva_point_note', '2-2', 'GB2312', '指标说明', '大部分观察具有洞悉特性',2),
  ('eva_point_note', '2-3', 'GB2312', '指标说明', '部分观察具有洞悉特性',3),
  ('eva_point_note', '2-4', 'GB2312', '指标说明', '很少观察具有洞悉特性',4),
  ('eva_point_note', '3-1', 'GB2312', '指标说明', '附上3以上引用来源',1),
  ('eva_point_note', '3-2', 'GB2312', '指标说明', '附上2-3引用来源',2),
  ('eva_point_note', '3-3', 'GB2312', '指标说明', '附上1个引用来源',3),
  ('eva_point_note', '3-4', 'GB2312', '指标说明', '没有附上引用来源',4),
  ('eva_point_note', '4-1', 'GB2312', '指标说明', '有超过15意见',1),
  ('eva_point_note', '4-2', 'GB2312', '指标说明', '有10-15意见',2),
  ('eva_point_note', '4-3', 'GB2312', '指标说明', '有5-10意见',3),
  ('eva_point_note', '4-4', 'GB2312', '指标说明', '只有低于5个意见',4),
  ('eva_point_note', '5-1', 'GB2312', '指标说明', '全部意见具有洞悉特性',1),
  ('eva_point_note', '5-2', 'GB2312', '指标说明', '大部分意见具有洞悉特性',2),
  ('eva_point_note', '5-3', 'GB2312', '指标说明', '部分意见具有洞悉特性',3),
  ('eva_point_note', '5-4', 'GB2312', '指标说明', '很少意见具有洞悉特性',4),
  ('eva_point_note', '1-1', 'en', 'point note', 'has observed over 5',1),
  ('eva_point_note', '1-2', 'en', 'point note', 'observed 3-4',2),
  ('eva_point_note', '1-3', 'en', 'point note', 'observed 1-2',3),
  ('eva_point_note', '1-4', 'en', 'point note', 'observed only below a',4),
  ('eva_point_note', '2-1', 'en', 'point note', 'all of the characteristics observed with insight',1),
  ('eva_point_note', '2-2', 'en', 'point note', 'most observers have insight into the characteristics',2),
  ('eva_point_note', '2-3', 'en', 'point note', 'some observers have insight into the characteristics',3),
  ('eva_point_note', '2-4', 'en', 'point note', 'has little insight into the characteristics observed',4),
  ('eva_point_note', '3-1', 'en', 'point note', 'accompanied by three sources cited above',1),
  ('eva_point_note', '3-2', 'en', 'point note', 'attach 2-3 cite sources',2),
  ('eva_point_note', '3-3', 'en', 'point note', 'accompanied by a reference to the source',3),
  ('eva_point_note', '3-4', 'en', 'point note', 'not accompanied by a reference to the source',4),
  ('eva_point_note', '4-1', 'en', 'point note', 'has more than 15 views',1),
  ('eva_point_note', '4-2', 'en', 'point note', 'opinion 10-15',2),
  ('eva_point_note', '4-3', 'en', 'point note', '5-10 advice',3),
  ('eva_point_note', '4-4', 'en', 'point note', 'only less than 5 views',4),
  ('eva_point_note', '5-1', 'en', 'point note', 'all the comments have insight into the characteristics',1),
  ('eva_point_note', '5-2', 'en', 'point note', 'most of the ideas have insight into the characteristics',2),
  ('eva_point_note', '5-3', 'en', 'point note', 'partial views to discern characteristics',3),
  ('eva_point_note', '5-4', 'en', 'point note', 'rarely have insight into the characteristics of opinion',4);

/* 同儕互評-功能編號 */
INSERT INTO `WM_acl_function` (`function_id`, `caption`, `scope`, `default_permission`) VALUES
  (1710100100, '同儕互評→題庫管理→閱讀/搜尋題目', 'teach', 'enable,visible,readable,writable,modifiable,uploadable,removable'),
  (1710100200, '同儕互評→題庫管理→新增題目', 'teach', 'enable,visible,readable,writable,modifiable,uploadable,removable'),
  (1710100300, '同儕互評→題庫管理→修改題目', 'teach', 'enable,visible,readable,writable,modifiable,uploadable,removable'),
  (1710100400, '同儕互評→題庫管理→刪除題目', 'teach', 'enable,visible,readable,writable,modifiable,uploadable,removable'),
  (1710100500, '同儕互評→題庫管理→匯入試題', 'teach', 'enable,visible,readable,writable,modifiable,uploadable,removable'),
  (1710100600, '同儕互評→題庫管理→匯出試題', 'teach', 'enable,visible,readable,writable,modifiable,uploadable,removable'),
  (1710100700, '同儕互評→題庫管理→上傳檔案', 'teach', 'enable,visible,readable,writable,modifiable,uploadable,removable'),
  (1710100800, '同儕互評→題庫管理→分享題庫', 'teach', 'enable,visible,readable,writable,modifiable,uploadable,removable'),
  (1710200100, '同儕互評→同儕互評管理→新增同儕互評', 'teach', 'enable,visible,readable,writable,modifiable,uploadable,removable'),
  (1710200200, '同儕互評→同儕互評管理→修改同儕互評', 'teach', 'enable,visible,readable,writable,modifiable,uploadable,removable'),
  (1710200300, '同儕互評→同儕互評管理→刪除同儕互評', 'teach', 'enable,visible,readable,writable,modifiable,uploadable,removable'),
  (1710300100, '同儕互評→批改同儕互評→批改同儕互評', 'teach', 'enable,visible,readable,writable,modifiable,uploadable,removable'),
  (1710300200, '同儕互評→批改同儕互評→上傳檔案', 'teach', 'enable,visible,readable,writable,modifiable,uploadable,removable'),
  (1710400100, '同儕互評→繳交同儕互評→瀏覽老師出的同儕互評', 'learn', 'enable,visible,readable,writable,modifiable,uploadable,removable'),
  (1710400200, '同儕互評→繳交同儕互評→繳交同儕互評', 'learn', 'enable,visible,readable,writable,modifiable,uploadable,removable'),
  (1710400300, '同儕互評→繳交同儕互評→瀏覽已繳交的同儕互評', 'learn', 'enable,visible,readable,writable,modifiable,uploadable,removable');

/* 系統事件 */
INSERT INTO `WM_event_type` (`code`, `caption`, `lang_code`) VALUES
  ('INSERT_PEER_ASSIGNMENT' ,            '新增同儕作業',       'Big5'),
  ('INSERT_PEER_ASSIGNMENT' ,            '新增同侪作业',   'GB2312'),
  ('INSERT_PEER_ASSIGNMENT' ,            'New Peer Assignment',   'en'),
  ('UPDATE_PEER_ASSIGNMENT' ,            '修改同儕作業',       'Big5'),
  ('UPDATE_PEER_ASSIGNMENT' ,            '修改同侪作业',   'GB2312'),
  ('UPDATE_PEER_ASSIGNMENT' ,            'Modify Peer Assignment',   'en'),
  ('DELETE_PEER_ASSIGNMENT' ,            '刪除同儕作業',       'Big5'),
  ('DELETE_PEER_ASSIGNMENT' ,            '删除同侪作业',   'GB2312'),
  ('DELETE_PEER_ASSIGNMENT' ,            'Delete Peer Assignment',   'en'),
  ('INSERT_CORRECT_PEER_OPEN' ,          '新增批改同儕作業成績-互評(開放給分)',       'Big5'),
  ('INSERT_CORRECT_PEER_OPEN' ,          '新增批改同侪作业成绩-互评(开放给分)',   'GB2312'),
  ('INSERT_CORRECT_PEER_OPEN' ,          'New Peer Assignment Grade-PA(OPEN)',   'en'),
  ('INSERT_CORRECT_PEER_EVA' ,           '新增批改同儕作業成績-互評(評量表)',       'Big5'),
  ('INSERT_CORRECT_PEER_EVA' ,           '新增批改同侪作业成绩-互评(评量表)',   'GB2312'),
  ('INSERT_CORRECT_PEER_EVA' ,           'New Peer Assignment Grade-PA(EVA)',   'en'),
  ('INSERT_CORRECT_SELF_OPEN' ,          '新增批改同儕作業成績-自評(開放給分)',       'Big5'),
  ('INSERT_CORRECT_SELF_OPEN' ,          '新增批改同侪作业成绩-自评(开放给分)',   'GB2312'),
  ('INSERT_CORRECT_SELF_OPEN' ,          'New Peer Assignment Grade-SA(OPEN)',   'en'),
  ('INSERT_CORRECT_SELF_EVA' ,           '新增批改同儕作業成績-自評(評量表)',       'Big5'),
  ('INSERT_CORRECT_SELF_EVA' ,           '新增批改同侪作业成绩-自评(评量表)',   'GB2312'),
  ('INSERT_CORRECT_SELF_EVA' ,           'New Peer Assignment Grade-SA(EVA)',   'en'),
  ('INSERT_CORRECT_TEACHER_OPEN' ,       '新增批改同儕作業成績-老師評(開放給分)',       'Big5'),
  ('INSERT_CORRECT_TEACHER_OPEN' ,       '新增批改同侪作业成绩-老师评(开放给分)',   'GB2312'),
  ('INSERT_CORRECT_TEACHER_OPEN' ,       'New Peer Assignment Grade-TA(OPEN)',   'en'),
  ('INSERT_CORRECT_TEACHER_EVA' ,        '新增批改同儕作業成績-老師評(評量表)',       'Big5'),
  ('INSERT_CORRECT_TEACHER_EVA' ,        '新增批改同侪作业成绩-老师评(评量表)',   'GB2312'),
  ('INSERT_CORRECT_TEACHER_EVA' ,        'New Peer Assignment Grade-TA(EVA)',   'en'),
  ('INSERT_EVALUATION' ,                 '新增評量表',       'Big5'),
  ('INSERT_EVALUATION' ,                 '新增评量表',   'GB2312'),
  ('INSERT_EVALUATION' ,                 'New Evaluation',   'en'),
  ('UPDATE_EVALUATION' ,                 '修改評量表',       'Big5'),
  ('UPDATE_EVALUATION' ,                 '修改评量表',   'GB2312'),
  ('UPDATE_EVALUATION' ,                 'Modify Evaluation',   'en'),
  ('DELETE_EVALUATION' ,                 '刪除評量表',       'Big5'),
  ('DELETE_EVALUATION' ,                 '刪除评量表',   'GB2312'),
  ('DELETE_EVALUATION' ,                 'Delete Evaluation',   'en'),
  ('DELETE_PA_GRADE' ,                   '清除同儕作業成績',       'Big5'),
  ('DELETE_PA_GRADE' ,                   '清除同侪作业成绩',   'GB2312'),
  ('DELETE_PA_GRADE' ,                   'Delete Peer Assignment Grade',   'en'),
  ('UPDATE_PA_PROPORTION' ,              '修改同儕作業比重',       'Big5'),
  ('UPDATE_PA_PROPORTION' ,              '修改同侪作业比重',   'GB2312'),
  ('UPDATE_PA_PROPORTION' ,              'Modify Peer Assignment Proportion',   'en'),
  ('UPDATE_CORRECT_GRADE' ,              '修改同儕作業成績',       'Big5'),
  ('UPDATE_CORRECT_GRADE' ,              '修改同侪作业成绩',   'GB2312'),
  ('UPDATE_CORRECT_GRADE' ,              'Modify Peer Assignment Grade',   'en'),
  ('UPDATE_CORRECT_ITEM' ,               '修改同儕作業項目',       'Big5'),
  ('UPDATE_CORRECT_ITEM' ,               '修改同侪作业项目',   'GB2312'),
  ('UPDATE_CORRECT_ITEM' ,               'Modify Peer Assignment Item',   'en'),
  ('INSERT_DO_PA' ,                      '新增繳交的同儕作業',       'Big5'),
  ('INSERT_DO_PA' ,                      '新增缴交的同侪作业',   'GB2312'),
  ('INSERT_DO_PA' ,                      'New Do Peer Assignment',   'en'),
  ('UPDATE_DO_PA' ,                      '修改繳交的同儕作業',       'Big5'),
  ('UPDATE_DO_PA' ,                      '修改缴交的同侪作业',   'GB2312'),
  ('UPDATE_DO_PA' ,                      'Modify Do Peer Assignment',   'en');


/* portal預設塞值，10001 需改為當前學校ID*/
INSERT INTO `WM_portal` (`portal_id`, `key`, `value`) VALUES
  ('content_sw',  'searchbar',    'true'),
  ('content_sw',  'franchisee',   'false'),
  ('content_sw',  'courselist',   'true'),
  ('content_sw',  'forum',        'false'),
  ('content_sw',  'news',         'true'),
  ('content_sw',  'calendar',     'false'),
  ('content_sw',  'custom1',      'false'),
  ('content_sw',  'custom2',      'false'),

  ('content_pri', 'franchisee',   '1'),
  ('content_pri', 'courselist',   '2'),
  ('content_pri', 'forum',        '3'),
  ('content_pri', 'custom1',      '4'),
  ('content_pri', 'custom2',      '5'),

  ('quick_sw',    'onlinehelp',   'false'),

  ('quick_pri',   'onlinehelp',   '1'),

  ('represent',   'pic_path',     '/base/10001/door/tpl/rep_img.png'),
  ('brand',       'pic_path',     '/base/10001/door/tpl/brand_logo.png'),
  ('theme',       'style',        'orange'),
  ('banner',    'bg_img',         '/base/10001/door/tpl/banner_bg.png'),

  ('franchisee',  'title',        '品牌大街'), 
  ('courselist',  'title',        '熱門課程'),
  ('forum',       'title',        '社區互動'),
  ('news',        'title',        '最新消息'), 
  ('calendar',    'title',        '最新活動'),
  ('custom1',     'title',        '自訂區域1'),
  ('custom2',     'title',        '自訂區域2'),
  ('onlinehelp',  'title',        '線上客服'),

  ('searchbar',   'x',            '50'),
  ('searchbar',   'y',            '150'),

  ('ads001',    'pic_path',       '/base/10001/door/tpl/ad001.png'),
  ('ads001',    'url_type',       '3'),
  ('ads001',    'url_default',    ''),
  ('ads001',    'url',            ''),
  ('ads002',    'pic_path',       '/base/10001/door/tpl/ad002.png'),
  ('ads002',    'url_type',       '3'),
  ('ads002',    'url_default',    ''),
  ('ads002',    'url',            ''),
  ('ads003',    'pic_path',       '/base/10001/door/tpl/ad003.png'),
  ('ads003',    'url_type',       '3'),
  ('ads003',    'url_default',    ''),
  ('ads003',    'url',            ''),

  ('custom1',     'pic_path',     '/base/10001/door/tpl/cus001.png'),
  ('custom1',     'pic_style',    'full'),
  ('custom1',     'url_type',     '3'),
  ('custom1',     'url_default',  ''),
  ('custom1',     'url',          ''),

  ('custom2',     'pic_path',     '/base/10001/door/tpl/cus002.png'),
  ('custom2',     'pic_style',    'full'),
  ('custom2',     'url_type',     '3'),
  ('custom2',     'url_default',  ''),
  ('custom2',     'url',          '');

/* 整批更新筆記本 `WM_msg_message`.`receive_time` 欄位 */
update `WM_msg_message` set `receive_time` = `submit_time` where `receive_time` is null;

/* 觸發 trigger 將舊資料補進MASTER 的CO_all_major */
UPDATE `WM_term_major`
SET `last_login` = `last_login`
WHERE 1;

/* 觸發 trigger 將舊資料補進MASTER 的CO_all_course */
UPDATE `WM_term_course`
SET `teacher` = `teacher`
WHERE 1;

/* LCMS課程複製精靈 acl_function */
INSERT INTO `WM_acl_function` (`function_id`, `caption`, `scope`, `default_permission`) VALUES 
(800400100, '課程管理→LCMS課程複製精靈', 'teach', 'enable,visible,readable,writable,modifiable,uploadable,removable');

/*討論板閱讀權限更改*/
update WM_bbs_boards set share_time=close_time where close_time!='0000-00-00 00:00:00' and share_time='0000-00-00 00:00:00' and after_finish='public';
update WM_bbs_boards set share_time=close_time where close_time!='0000-00-00 00:00:00' and share_time='0000-00-00 00:00:00' and after_finish='';

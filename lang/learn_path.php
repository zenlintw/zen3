<?php
/**
 * 語系檔
 *
 * PHP 4.4.2+, MySQL 4.0.17+, Apache 1.3.34+
 *
 * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
 * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
 * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
 *
 * @package     WM3
 * @author      Wiseguy Liang
 * @copyright   2000-2006 SunNet Tech. INC.
 * @version     CVS: $
 * @link        http://demo.learn.com.tw/1000110138/index.html
 * @since       2006-4-3
 */

$MSG['enter_homework_error'] = array(
    'Big5' => '無法進入作業！可能原因如下：<br>1. 作業已做過且不允許修改<br>2. 作業未啟用<br>3. 未在作業繳交期間內',
    'GB2312' => '无法进入作业！可能原因如下：<br>1. 作业已做过且不允许修改<br>2. 作业未启用<br>3. 未在作业缴交期间内',
    'en' => 'Access denied! Three possible reasons: The assignment may have been done and modifications are not allowed; or, the assignment may not be open yet; or, this may not be the submission period.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['enter_exam_error'] = array(
    'Big5' => '無法進入測驗！可能原因如下：<br>1. 測驗已做次數已達且不允許修改<br>2. 測驗未啟用<br>3. 未在測驗施測期間內',
    'GB2312' => '无法进入测验！可能原因如下：<br>1. 测验已做次数已达且不允许修改<br>2. 测验未启用<br>3. 未在测验施测期间内',
    'en' => 'Access denied! Three possible reasons: You may have reached the number of redos allowed; or, the test may not be open yet; or, this maynot be the test period.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['enter_questionnaire_error'] = array(
    'Big5' => '無法進入問卷！可能原因如下：<br>1. 問卷已做過且不允許修改<br>2. 問卷未啟用<br>3. 未在問卷繳交期間內',
    'GB2312' => '无法进入问卷！可能原因如下：<br>1. 问卷已做过且不允许修改<br>2. 问卷未启用<br>3. 未在问卷缴交期间内',
    'en' => 'Access denied! Three possible reasons: The questionnaire may have been done and modifications are not allowed; or, the questionnaire maynot be open yet; or, this may not be the submission period.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['enter_homework_acl_error'] = array(
    'Big5' => '您不需要繳交這份作業，因為您不在繳交者的名單上。',
    'GB2312' => '您不需要缴交这份作业，因为您不在缴交者的名单上。',
    'en' => 'You are not included in the Students of assignment.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['enter_exam_acl_error'] = array(
    'Big5' => '您不需要參加這項考試，因為您不在應試者的名單上。',
    'GB2312' => '您不需要参加这项考试，因为您不在应试者的名单上。',
    'en' => 'You are not included in the Examinee List.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['enter_questionnaire_acl_error'] = array(
    'Big5' => '您不需要繳交這份問卷，因為您不在繳交者的名單上。',
    'GB2312' => '您不需要缴交这份问卷，因为您不在缴交者的名单上。',
    'en' => 'You are not included in the Participant list.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['enter_forum_error'] = array(
    'Big5' => '無法進入討論板。可能該討論板已關閉。',
    'GB2312' => '无法进入讨论板。可能该讨论板已关闭。',
    'en' => 'Access denied! The discussion forum may have been closed.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['enter_chatroom_error'] = array(
    'Big5' => '無法進入討論室。可能該討論室已關閉。',
    'GB2312' => '无法进入讨论室。可能该讨论室已关闭。',
    'en' => 'Can not enter discussion room. The discussion room may be closed.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['enter_chatroom_permission_denied'] = array(
    'Big5' => '不允許進入該討論室。',
    'GB2312' => '不允许进入该讨论室。',
    'en' => 'You are not allowed to enter this discussion room.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['enter_forum_permission_denied'] = array(
    'Big5' => '不允許進入該討論板。',
    'GB2312' => '不允许进入该讨论板。',
    'en' => 'You are not allowed to enter this discussion forum.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['file_exist'] = array(
    'Big5' => '本節點檔案已不存在。',
    'GB2312' => '本节点档案已不存在。',
    'en' => 'The file connected to this node no longer exists.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['node_error'] = array(
    'Big5' => '尚未建立節點',
    'GB2312' => '尚未建立节点',
    'en' => 'Nodes not created yet!',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['node_error1'] = array(
    'Big5' => '--= 尚未建立節點 =--',
    'GB2312' => '--= 尚未建立节点 =--',
    'en' => '--= Nodes not created yet! =--',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['catalog_error'] = array(
    'Big5' => '目錄結構有誤',
    'GB2312' => '目录结构有误',
    'en' => 'Incorrect structure!',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['catalog_error1'] = array(
    'Big5' => '--= 目錄結構有誤！ =--',
    'GB2312' => '--= 目录结构有误！ =--',
    'en' => '--= Incorrect structure! =--',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['wait_msg'] = array(
    'Big5' => '教材目錄產生中，請稍候 ...',
    'GB2312' => '教材目录产生中，请稍候 ...',
    'en' => 'Generating Path, please wait ...',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['js_msg01'] = array(
    'Big5' => '無任何可使用之教材節點。',
    'GB2312' => '无任何可使用之教材节点。',
    'en' => 'No content nodes available.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['it is at the'] = array(
    'Big5' => '已達',
    'GB2312' => '已达',
    'en' => 'it is at the ',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['end.'] = array(
    'Big5' => '底端',
    'GB2312' => '底端',
    'en' => 'end.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['outset.'] = array(
    'Big5' => '頂端',
    'GB2312' => '顶端',
    'en' => 'outset.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['btn_next'] = array(
    'Big5' => '下一節點',
    'GB2312' => '下一节点',
    'en' => 'Next Node',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['btn_prev'] = array(
    'Big5' => '上一節點',
    'GB2312' => '上一节点',
    'en' => 'Previous Node',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['btn_expand'] = array(
    'Big5' => '全部展開/全部收攏',
    'GB2312' => '全部展开/全部收拢',
    'en' => 'Expand All/Collapse All',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['btn_notebook'] = array(
    'Big5' => '撰寫筆記',
    'GB2312' => '撰写笔记',
    'en' => 'Notebook',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['btn_minimize'] = array(
    'Big5' => '縮小',
    'GB2312' => '缩小',
    'en' => 'Minimize',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['btn_maximize'] = array(
    'Big5' => '放大',
    'GB2312' => '放大',
    'en' => 'Maximize',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['load_finish'] = array(
    'Big5' => '教材目錄已載入完成，請點選左方節點開始進行課程。',
    'GB2312' => '教材目录已载入完成，请点选左方节点开始进行课程。',
    'en' => 'Path load finish. Please choose the node of left side to study.',
    'EUC-JP' => '',
    'user_define' => ''
);

/* mooc */
$MSG['btn_note'] = array(
    'Big5' => '筆記',
    'GB2312' => '笔记',
    'en' => 'Notebook',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['back_to_list'] = array(
    'Big5' => '回單元列表',
    'GB2312' => '回单元列表',
    'en' => 'Back to list',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['previous_unit'] = array(
    'Big5' => '上個單元',
    'GB2312' => '上个单元',
    'en' => 'Previous',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['next_unit'] = array(
    'Big5' => '下個單元',
    'GB2312' => '下个单元',
    'en' => 'Next',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['complete_schedule'] = array(
    'Big5' => '完成進度',
    'GB2312' => '完成进度',
    'en' => 'Complete schedule',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['self_assessment'] = array(
    'Big5' => '自我評量',
    'GB2312' => '自我评量',
    'en' => 'Self-assessment',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['msg_asset_order'] = array(
    'Big5' => '第%num%節',
    'GB2312' => '第%num%节',
    'en' => 'Section %num%',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['msg_unit_order'] = array(
    'Big5' => '第%num%章',
    'GB2312' => '第%num%章',
    'en' => 'Chapter %num%',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['no_course_content'] = array(
    'Big5' => '尚未有任何課程',
    'GB2312' => '尚未有任何课程',
    'en' => 'There is no  content.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['quick_review'] = array(
    'Big5' => '快照本',
    'GB2312' => '快照本',
    'en' => 'Snapshop Album',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['no_material'] = array(
    'Big5' => '已無此教材!',
    'GB2312' => '已无此教材！',
    'en' => 'No such teaching materials!',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['no_permission'] = array(
    'Big5' => '無權限存取！',
    'GB2312' => '无权限存取！',
    'en' => 'No permission to access!',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['download'] = array(
    'Big5' => '下載檔案，開始閱讀',
    'GB2312' => '下载档案，开始阅读',
    'en' => 'Download and read',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['msg_file_not_exist'] = array(
    'Big5' => '教材檔案 [ %s ] 不存在',
    'GB2312' => '教材档案 [ %s ] 不存在',
    'en' => 'File [ %s ] does not exist',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['need_flash'] = array(
    'Big5' => '缺少Flash套件，請至Adobe官網下載',
    'GB2312' => '缺少Flash套件，请至Adobe官网下载',
    'en' => 'To view this content, you need the lastest version of Adobe Flash Player.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['mobile_tip'] = array(
	'Big5' => '行動裝置不支援檔案上傳，需要附檔作答的%TYPE%，請改用電腦裝置操作。',
	'GB2312' => '行动装置不支援档案上传，需要附档作答的%TYPE%，请改用电脑装置操作。',
	'en' => 'The mobile device does not support file uploading. It requires %TYPE% of the attached file. Please use the computer device instead.',
	'EUC-JP' => '',
	'user_define' => ''
);

$MSG['homework_title'] = array(
    'Big5' => '作業 / 報告',
    'GB2312' => '作业 / 报告',
    'en' => 'Assignments',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['questionnaire_title'] = array(
	'Big5'			=> '問卷 / 投票',
	'GB2312'		=> '问卷 / 投票',
	'en'			=> 'Questionnaires / Polls',
	'EUC-JP'		=> '',
	'user_define'	=> ''
);
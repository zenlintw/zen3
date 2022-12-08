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
 * @version     CVS: $Id: homework_teach.php,v 1.1 2010/02/24 02:39:03 saly Exp $
 * @link        http://demo.learn.com.tw/1000110138/index.html
 * @since       2006-07-11
 */
require_once dirname(__FILE__) . '/exam_teach.php';

$MSG['already_examed'] = array(
    'Big5' => '注意！\\n已經有學員繳交作業！若您修改作業中的任何設定，可能會影響公平性。若您更動試卷中\\n的作業內容、題數、配分，只會對日後應試的學員有效，而已經繳交過的學員，其作業內\\n容和批改結果(分數)並不會改變。\\n若作業內容有重大變更，建議您可以在修改後，對作業做『重置』(清除學員繳交紀錄)，\\n並通知學員重新繳交。\\n要繼續修改請按「確定」，不修改請按「取消」。',
    'GB2312' => '注意！\\n已经有学员缴交作业！若您修改作业中的任何设定，可能会影响公平性。若您更动试卷中\\n的作业内容、题数、配分，只会对日后应试的学员有效，而已经缴交过的学员，其作业内\\n容和批改结果(分数)并不会改变。\\n若作业内容有重大变更，建议您可以在修改后，对作业做‘重置’(清除学员缴交纪录)，\\n并通知学员重新缴交。\\n要继续修改请按“确定”，不修改请按“取消”。',
    'en' => 'Attention!\\nSome students had deliver homework! If you adjust any setting of homework, it will affect its fairness.\\nIf you adjust any setting of homework. It will be effective for those student not delivered. The score and content of those who deliver their homework will not affected. \\n If you really want to adjust homework, we suggest you to reset this homework after adjusting and inform student to deliver again.\\n To continue adjusting, please choose CONFIRM, or choose CANCEL. ',
    'EUC-JP' => '',
    'user_define' => ''
);
$MSG['item_create']    = array(
    'Big5' => '新增題目',
    'GB2312' => '新增题目',
    'en' => 'Add Item',
    'EUC-JP' => '',
    'user_define' => ''
);
$MSG['send_paper']     = array(
    'Big5' => '強制繳交',
    'GB2312' => '强制缴交',
    'en' => 'Compulsory payment',
    'EUC-JP' => '',
    'user_define' => ''
);
$MSG['return_menu']    = array(
    'Big5' => '回維護題目清單',
    'GB2312' => '回维护题目清单',
    'en' => 'Back to Item Editing',
    'EUC-JP' => '',
    'user_define' => ''
);
$MSG['voice_colon']    = array(
    'Big5' => '口語發音：',
    'GB2312' => '口语发音',
    'en' => 'Oral language pronounciation',
    'EUC-JP' => '',
    'user_define' => ''
);
$MSG['item_modify']    = array(
    'Big5' => '修改題目',
    'GB2312' => '修改题目',
    'en' => 'Edit Item',
    'EUC-JP' => '',
    'user_define' => ''
);
$MSG['exam_type2']     = array(
    'Big5' => '自我練習',
    'GB2312' => '自我练习',
    'en' => 'Practice',
    'EUC-JP' => '',
    'user_define' => ''
);
$MSG['exam_type3']     = array(
    'Big5' => '平時作業',
    'GB2312' => '平时作业',
    'en' => 'Homework',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_type4'] = array(
    'Big5' => '正式報告',
    'GB2312' => '正式报告',
    'en' => 'Formal Paper',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_type5'] = array(
    'Big5' => '線上作業',
    'GB2312' => '线上作业',
    'en' => 'Online Assignment',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['publish_state2'] = array(
    'Big5' => '進行中',
    'GB2312' => '进行中',
    'en' => 'Ongoing',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['announce_type2'] = array(
    'Big5' => '繳交後公布',
    'GB2312' => '缴交后公布',
    'en' => 'Published after submission',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['announce_type3'] = array(
    'Big5' => '作業關閉後公布',
    'GB2312' => '作业关闭后公布',
    'en' => 'Published after assignment is closed.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_maintain'] = array(
    'Big5' => '作業維護',
    'GB2312' => '作业维护',
    'en' => 'Assignment Editing',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_result'] = array(
    'Big5' => '結果檢視',
    'GB2312' => '结果检查',
    'en' => 'Check Results',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_correct'] = array(
    'Big5' => '作業批改',
    'GB2312' => '作业批改',
    'en' => 'Assignment Grading',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_name'] = array(
    'Big5' => '作業名稱',
    'GB2312' => '作业名称',
    'en' => 'Assignment Name',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_use'] = array(
    'Big5' => '作業用途',
    'GB2312' => '作业用途',
    'en' => 'Purpose',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_duration1_1'] = array(
    'Big5' => '開放受測者進入作業的時間<br />不勾選「啟用」代表沒有限制日期',
    'GB2312' => '开放受测者进入作业的时间<br />不勾选“启用”代表没有限制日期',
    'en' => 'Assignment start date. <br/> If Enable is unchecked, it means no time limit.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_duration1_2'] = array(
    'Big5' => '結束受測者進入作業的時間<br />不勾選「啟用」代表沒有限制日期',
    'GB2312' => '结束受测者进入作业的时间<br />不勾选“启用”代表没有限制日期',
    'en' => 'Assignment end date.<br/> If Enable is unchecked, it means no time limit.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_toolbar'] = array(
    'Big5' => '作業維護工具列',
    'GB2312' => '作业维护工具列',
    'en' => 'Assignment Editing Tools',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['reset_confirm'] = array(
    'Big5' => '此動作將清除所有學員所繳交的內容以及作業分數。確定要繼續嗎？要繼續進行請按「確定」，要停止請按「取消」。',
    'GB2312' => '此动作将清除所有学员所缴交的内容以及作业分数。确定要继续吗？要继续进行请按“确定”，要停止请按“取消”。',
    'en' => 'System will clear all of students\' answers of the selected items. n Are you sure you want to reset?',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['delete_confirm'] = array(
    'Big5' => '刪除作業後，將一併刪除學員已經繳交的作業以及成績還有相關的行事曆項目。確定要繼續嗎？要繼續進行請按「確定」，要停止請按「取消」。',
    'GB2312' => '删除作业后，将一并删除学员已经缴交的作业以及成绩还有相关的行事历项目。确定要继续吗？要继续进行请按“确定”，要停止请按“取消”。',
    'en' => 'Are you sure you want to delete selected items?',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_paper'] = array(
    'Big5' => '作業',
    'GB2312' => '作业',
    'en' => 'Assignment',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_item'] = array(
    'Big5' => '題目',
    'GB2312' => '题目',
    'en' => 'Item',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_create'] = array(
    'Big5' => '建立作業',
    'GB2312' => '建立作业',
    'en' => 'Create Assignment',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_info'] = array(
    'Big5' => '作業資訊',
    'GB2312' => '作业资讯',
    'en' => 'Assignment Info',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_preview'] = array(
    'Big5' => '作業預覽',
    'GB2312' => '作业预览',
    'en' => 'Assignment Preview',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['pre-notice1'] = array(
    'Big5' => '顯示於進入作業前一頁',
    'GB2312' => '显示于进入作业前一页',
    'en' => 'Appear on the page before entering assignment.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_use1'] = array(
    'Big5' => '本作業是作何用途',
    'GB2312' => '本作业是作何用途',
    'en' => 'The purpose of this assignment.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['count_type_hint'] = array(
    'Big5' => '本作業如何計分',
    'GB2312' => '本作业如何计分',
    'en' => 'The grading formula of this assignment.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['examinee'] = array(
    'Big5' => '作業對象',
    'GB2312' => '作业对象',
    'en' => 'Assignee',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['corrector'] = array(
    'Big5' => '批閱人員',
    'GB2312' => '批阅人员',
    'en' => 'Assessor',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_times'] = array(
    'Big5' => '作業次數',
    'GB2312' => '作业次数',
    'en' => '# of attempts allowed',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_times_hint'] = array(
    'Big5' => '已經作業過的次數',
    'GB2312' => '已经作业过的次数',
    'en' => '# of attempts',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_duration_hint'] = array(
    'Big5' => '進行作業的時間',
    'GB2312' => '进行作业的时间',
    'en' => 'Time allowed to do the assignment',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['window_control1'] = array(
    'Big5' => '禁止切換至其它視窗',
    'GB2312' => '禁止切换至其它窗口',
    'en' => 'Switching windows not allowed',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['window_control_hint'] = array(
    'Big5' => '是否強制作業者不得使用其它軟體',
    'GB2312' => '是否强制作业者不得使用其它软体',
    'en' => 'Prevent asignees from using other software?',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['timeout_control1'] = array(
    'Big5' => '不自動繳交但標記逾時',
    'GB2312' => '不自动缴交但标记逾时',
    'en' => 'No autosubmit but mark Past Time',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['timeout_control2'] = array(
    'Big5' => '自動繳交',
    'GB2312' => '自动缴交',
    'en' => 'Autosubmit',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['timeout_control_hint'] = array(
    'Big5' => '繳交時間到後，LMS應作何處置',
    'GB2312' => '缴交时间到后，LMS应作何处置',
    'en' => 'What would you like LMS to do when assignment time is up?',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['score_publish2'] = array(
    'Big5' => '關閉作業後公布',
    'GB2312' => '关闭作业后公布',
    'en' => 'Published after assignment is closed',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['search_hint'] = array(
    'Big5' => '請勾選條件開始搜尋題目，並從搜尋結果中挑選題目加入到這份考卷內。',
    'GB2312' => '请勾选条件开始搜索题目，并从搜索结果中挑选题目加入到这份考卷内。',
    'en' => 'Please set up your search query and select items to add to this test.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['search_hint1'] = array(
    'Big5' => '請勾選以下條件開始搜尋題目，並從搜尋結果中挑選題目加入自己的題庫內使用。',
    'GB2312' => '请勾选以下条件开始搜索题目，并从搜索结果中挑选题目加入自己的题库内使用。',
    'en' => 'Please set up your search query and select items to add to your pool.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_list_help'] = array(
    'Big5' => '
	<li>搬移題目：勾選題目後，再點選【作業】或【大題】即可將勾選題目搬移至該大題中</li>
	<li>群組說明：勾選【作業】或【大題】後，點擊【大題文字】，可以編輯該大題的前置說明</li>
	<li>移除群組：勾選【大題】後，點擊【移除大題】，即可移除該大題及所含題目</li>
	<li>移除題目：勾選【題目】後，點擊【移除題目】，即可移除所勾選的題目</li>
	<li>指定分數：勾選【題目】後，點擊【指定分數】輸入分數，則勾選的題目即會指定成該分數。</li>
	<li>平均配分：按【平均配分】輸入總分數，會平均分配給所有題目 (若有餘數則加給最後一題)。</li>
	<li>移動位置：勾選【大題】或【題目】後，點擊【上移】或【下移】，即可移動位置</li>
	<li>題目的內容依序為：<font color="gray">[題型][配分]</font> 題目標題 <font color="gray">[版,冊,章,節,段][難易度]</font></li>
	',
    'GB2312' => '<li>搬移题目：勾选题目后，再点选【作业】或【大题】即可将勾选题目搬移至该大题中</li><li>群组说明：勾选【作业】或【大题】后，点击【大题文字】，可以编辑该大题的前置说明</li><li>移除群组：勾选【大题】后，点击【移除大题】，即可移除该大题及所含题目</li><li>移除题目：勾选【题目】后，点击【移除题目】，即可移除所勾选的题目</li><li>指定分数：勾选【题目】后，点击【指定分数】输入分数，则勾选的题目即会指定成该分数。</li><li>平均配分：按【平均配分】输入总分数，会平均分配给所有题目 (若有余数则加给最后一题)。</li><li>移动位置：勾选【大题】或【题目】后，点击【上移】或【下移】，即可移动位置</li><li>题目的内容依序为：<font color=gray>[题型][配分]</font> 题目标题 <font color=gray>[版,册,章,节,段][难易度]</font></li>',
    'en' => '<li>Move Items: Select items and then click Assignment or Section as destination for the selceted items.</li><li>Section Instruction: If you want to edit section instruction, select Assignment or Section and then click Section Instruction.</li><li>Remove Section: Select the section you want to remove and then click Remove Section. All the items in the selected section will be removed.</li><li>Remove Item: Select the items you want to remove and then click Remove Item. All the selected items will be removed.</li><li>Assign Points: Select items and then click Assign Points. Enter the number of points you want to assign to the selected items.</li><li>Average Weighting: Click Average Weighting and enter total points. The average will be assigned to all items. (Remainder will be added to the last item.)</li><li>Location: Select section or item, click Move Up or Move Down, and the selected section(s) or item(s) will be moved to the designated place.</li><li>Item contents will be displayed in the following order: <font color=gray>[Type][Weighting]</font> Title <font color=gray>[Edition, Book, Chapter, Unit, Paragraph][Level]</font></li>',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_import'] = array(
    'Big5' => '匯入作業',
    'GB2312' => '导入作业',
    'en' => 'Import Assignment',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['message'] = array(
    'Big5' => '<big><b>•即時隨機選題•</b></big><ol>
	<li>即是動態由下列您所選擇的條件，即時從題庫產生試卷，不必現在選擇試題。</li>
	<li>如果您啟用此功能，則【已選題目】清單中的題目將被忽略，且不儲存。</li>
	<li>如果符合條件的題目少於您所設定的題數，除非是 0 題，否則依然視為有效試卷。</li>
	<li>題數範圍是 1 &#8804; n &#8804; %num_limit%。超出此範圍則會以 %num_limit% 計。</li>
	<li>版、冊、章、節、段 若欲同時尋找多個章節，請用逗點隔開。</li>
	</ol>',
    'GB2312' => '<big><b>．即时随机选题．</b></big><ol><li>即是动态由下列您所选择的条件，即时从题库产生试卷，不必现在选择试题。</li><li>如果您启用此功能，则【已选题目】清单中的题目将被忽略，且不保存。</li><li>如果符合条件的题目少于您所设定的题数，除非是 0 题，否则依然视为有效试卷。</li><li>题数范围是 1 &le; n &le; 200。超出此范围则会以内定值 50 计。</li><li>版、册、章、节、段 若欲同时寻找多个章节，请用逗点隔开。</li></ol>',
    'en' => '<big><b>ERandom SelectionE</b></big><ol><li>System auto selects items from the pool based on your query.</li><li>If you enable this function, all the items on the list of selected items will be ignored and will not be saved.</li><li>If the number of matched items is more than zero, even if it&#039;s less than the number you want, this will still be considered as a valid test.</li><li>The number of items should range from 1 to 200. Any number out of this range will be automatically corrected to 50.</li><li>Multiple editions, books, etc. should be separated commas.</li></ol>',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exim_times_list'] = array(
    'Big5' => '作業別列表',
    'GB2312' => '作业别列表',
    'en' => 'Assignment List',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_state'] = array(
    'Big5' => '作業狀態',
    'GB2312' => '作业状态',
    'en' => 'Status',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_submit'] = array(
    'Big5' => '繳交時間',
    'GB2312' => '缴交时间',
    'en' => 'Due Time',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_context'] = array(
    'Big5' => '作業內容',
    'GB2312' => '作业内容',
    'en' => 'Assignment Content',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['file_size_limit2'] = array(
    'Big5' => '，總上傳檔案大小不得超過：',
    'GB2312' => '，总上传档案大小不得超过：',
    'en' => 'The total size of your uploaded files cannot exceed',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_statistics'] = array(
    'Big5' => '作業統計',
    'GB2312' => '作业统计',
    'en' => 'Assignment Stats',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['score_depend'] = array(
    'Big5' => '此為電腦批閱之自動計分。實際得分以教師公佈之正式分數為準。',
    'GB2312' => '此为电脑批阅之自动计分。实际得分以教师公布之正式分数为准。',
    'en' => 'This grade has been automatically calculated by system. The final grade will be issued by the instructor.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['score_remind'] = array(
    'Big5' => '警告！您未設定所有題目配分，將會造成（部分）作業結果是０分，確定存檔離開？',
    'GB2312' => '警告！您未设定所有题目配分，将会造成（部分）作业结果是０分，确定存档离开？',
    'en' => 'The total points for (part of) this assignment is 0. Still want to save?',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['changed_but_not_saved'] = array(
    'Big5' => '您尚未儲存修改過的作業順序。確定要離開嗎？',
    'GB2312' => '您尚未保存修改过的作业顺序。确定要离开吗？',
    'en' => 'You haven&#039;t saved modifications. Are you sure you want to exit?',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['attach'] = array(
    'Big5' => '下載全部學員附檔',
    'GB2312' => '下载全部学员附档',
    'en' => 'Download all assignment attachments.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['hw_import'] = array(
    'Big5' => '作業批改整批匯入',
    'GB2312' => '作业批改整批汇入',
    'en' => 'All import',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['att_upload'] = array(
    'Big5' => '作業包裝完成，請點選下列連結下載檔案',
    'GB2312' => '作业包装完成，请点选下列连结下载档案',
    'en' => 'Assignment packaging completed! Please click the following links to download files.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['all_homeowrk'] = array(
    'Big5' => '所有學生的作業附檔',
    'GB2312' => '所有学生的作业附档',
    'en' => 'All of students&#039; assignment attachments',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['msg1'] = array(
    'Big5' => '確定要一次下載「',
    'GB2312' => '确定要一次下载“',
    'en' => 'Are you sure you want to download all of [',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['msg2'] = array(
    'Big5' => '」學員的作業附檔嗎？',
    'GB2312' => '”学员的作业附档吗？',
    'en' => ']\\\'s assignments?',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['remove_fail_ref'] = array(
    'Big5' => '<font color="red">已被以下作業引用，請先到該作業刪除：</font>',
    'GB2312' => '<font color="red">已被以下作业引用，请先到该作业删除：</font>',
    'en' => '<font color=red>Already used by the following assignment. Please delete from there.</font>',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['copy to'] = array(
    'Big5' => '複製作業到...',
    'GB2312' => '复制作业到...',
    'en' => 'Copy to ...',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['group_name'] = array(
    'Big5' => '小組名稱',
    'GB2312' => '小组名称',
    'en' => 'Subgroup Name',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['assignment type'] = array(
    'Big5' => '作業型態',
    'GB2312' => '作业型态',
    'en' => 'Type',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['for personal'] = array(
    'Big5' => '個人',
    'GB2312' => '个人',
    'en' => 'Personal',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['for group'] = array(
    'Big5' => '群組',
    'GB2312' => '群组',
    'en' => 'Group',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['return list'] = array(
    'Big5' => '回作業列表',
    'GB2312' => '回作业列表',
    'en' => 'Return assignment list',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['edit instance'] = array(
    'Big5' => '修改此作業',
    'GB2312' => '修改此作业',
    'en' => 'Edit this assignment',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['score_publish_hint'] = array(
    'Big5' => '開放觀摩的時刻(<font color="blue">教師須在作業批改中指定優良作業以供學員觀摩</font>)',
    'GB2312' => '开放观摩的时刻(<font color="blue">教师须在作业批改中指定优良作业以供学员观摩</font>)',
    'en' => 'When to open study(<font color="blue">Teachers should choose commendable works to share for study in [Assignment Grading].</font>)',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['batch_revised_publish'] = array(
    'Big5' => '已批改作業全部『開放觀摩』',
    'GB2312' => '已批改作业全部『开放观摩』',
    'en' => 'All revised homeworks set ‘open for study’',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['batch_publish'] = array(
    'Big5' => '系統自動批改作業且『開放觀摩』',
    'GB2312' => '系统自动批改作业且『开放观摩』',
    'en' => 'System revises all homeworks automatically and ‘open for study’',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['batch_revised_publish_hint'] = array(
    'Big5' => '您是否要將所有已批改的作答紀錄於『%OPEN_TIME%』開放觀摩呢？',
    'GB2312' => '您是否要将所有已批改的作答纪录于『%OPEN_TIME%』开放观摩呢？',
    'en' => 'Do you want to open study for all revised homeworks after ‘%OPEN_TIME%’?',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['batch_publish_hint'] = array(
    'Big5' => '系統自動批改作答紀錄，且於『%OPEN_TIME%』開放觀摩？(可自動批改題型：是非、單選、多選、填充與配合題;學員填寫的填充題答案，須與教師所設定的答案一樣，包含空白、逗號、字形...等，系統才會自動給分)',
    'GB2312' => '系统自动批改作答纪录，且于『%OPEN_TIME%』开放观摩？(可自动批改题型：是非、单选、多选、填充与配合题;学员填写的填充题答案，须与教师所设定的答案一样，包含空白、逗号、字形...等，系统才会自动给分)',
    'en' => 'System revises homework records automatically and opens for study after ‘%OPEN_TIME%’. (System will revise True/False, Single choice, Multiple choice, Fill in the blank, Matching items. If item is ‘fill in the blank’, the assignees\&#039;s answers must match teacher\&#039;s, include spaces, commas, letters etc.)',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['msg_public_success'] = array(
    'Big5' => '開放觀摩成功 ',
    'GB2312' => '开放观摩成功',
    'en' => 'Open for study successful!',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['msg_public_fail'] = array(
    'Big5' => '開放觀摩失敗',
    'GB2312' => '开放观摩失败',
    'en' => 'Open for study failure!',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['msg_never_publish'] = array(
    'Big5' => '這份作業目前設定為不開放觀摩，若要允許學生觀摩其他人作業請到作業維護中設定開放觀摩。',
    'GB2312' => '这份作业目前设定为不开放观摩，若要允许学生观摩其他人作业请到作业维护中设定开放观摩。',
    'en' => 'This homework is not allow open for study. If you want to set open for study, please change setting in [Assignment Editing].',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['open_time_now'] = array(
    'Big5' => '學員繳交作業後',
    'GB2312' => '学员缴交作业后',
    'en' => 'submission by assignee',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['open_time_close'] = array(
    'Big5' => '作業截止後',
    'GB2312' => '作业截止后',
    'en' => 'assignment close',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['enable_duration'] = array(
    'Big5' => '作業繳交時間',
    'GB2312' => '作业缴交时间',
    'en' => '',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['export_no_items'] = array(
    'Big5' => '此作業中無任何題目，無法立即匯出作業。請先進入作業中「挑選題目」，再將您所選擇的作業「匯出」。',
    'GB2312' => '此作业中无任何题目，无法立即汇出作业。请先进入作业中“挑选题目”，再将您所选择的作业“汇出”。',
    'en' => 'There is no any item in the homework. To export this homework, please add some items to it and then export again.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['co_button_download_all'] = array(
    'Big5' => '下載作業',
    'GB2312' => '下载作业',
    'en' => 'Downloading',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['sync_to_calendar_msg'] = array(
    'Big5' => '會自動建立一個事件到課程行事曆中。如果需要，請到課程行事曆進行進階編輯。',
    'GB2312' => '会自动建立一个事件到课程行事历中。如果需要，请到课程行事历进行进阶编辑。',
    'en' => 'It will automatically create a calendar of events to the course. If necessary, go to the course calendar were advanced editing.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title_submit_time'] = array(
    'Big5' => '繳交時間',
    'GB2312' => '缴交时间',
    'en' => 'Submit Time',
    'EUC-JP' => '',
    'user_define' => ''
);

/*** CUSTOM (B) 作業、測驗或問卷之選擇題如單選或複選題的答案選項時，增加以下文字 ***/

$MSG['co_radio_1'] = array(
    'Big5' => '連續配置法',
    'GB2312' => '',
    'en' => '',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['co_radio_2'] = array(
    'Big5' => '連結配置法',
    'GB2312' => '',
    'en' => '',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['co_radio_3'] = array(
    'Big5' => '索引配置法',
    'GB2312' => '',
    'en' => '',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['co_radio_4'] = array(
    'Big5' => '以上皆非',
    'GB2312' => '',
    'en' => '',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['co_toolbtm01'] = array(
    'Big5' => '新增對象',
    'GB2312' => '新增',
    'en' => 'Add',
    'EUC-JP' => '',
    'user_define' => ''
);

/*** CUSTOM (E) ***/

/*** CUSTOM (B) by Yea ***/
$MSG['co_msg_leave']        = array(
    'Big5' => '離開此畫面之前，請記得儲存批改',
    'GB2312' => '离开此画面之前，请记得储存批改',
    'en' => 'Before leaving this screen, please remember that store marking',
    'EUC-JP' => '',
    'user_define' => ''
);
/*** CUSTOM (E) by Yea ***/
/*MIS#029785 補交處理 (b)*/
$MSG['co_enable_over_time'] = array(
    'Big5' => '附檔可否補交',
    'GB2312' => '',
    'en' => '',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['co_over_time_end'] = array(
    'Big5' => '最晚補交日期為：',
    'GB2312' => '',
    'en' => '',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['co_warning_over_time'] = array(
    'Big5' => '設定錯誤！最晚補交日期不得早於關閉作答日期。',
    'GB2312' => '',
    'en' => '',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['co_over_time_submit'] = array(
    'Big5' => '補交',
    'GB2312' => '',
    'en' => '',
    'EUC-JP' => '',
    'user_define' => ''
);
/*MIS#029785 補交處理 (e)*/

$MSG['tar_hw_wait'] = array(
    'Big5' => '作業檔案打包中，請稍候…',
    'GB2312' => '作业档案打包中，请稍候…',
    'en' => 'Job File Packing, please be waiting ...',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['tar_hw_tip']         = array(
    'Big5' => '作業檔案已改用 UTF-8 編碼，解壓縮若有亂碼問題，請看 <a href="javascript: return false;" onclick="handler()">說明</a>。',
    'GB2312' => '作业档案已改用 UTF-8 编码，解压缩若有乱码问题，请看 <a href="javascript: return false;" onclick="handler()">说明</a>。',
    'en' => 'File have changed coding UTF-8,if the file show gibberish,please read the <a href="javascript: return false;" onclick="handler()">direction</a>.',
    'EUC-JP' => '',
    'user_define' => ''
);
$MSG['grade_publish_time'] = array(
    'Big5' => '成績公布時間：',
    'GB2312' => '成绩公布时间：',
    'en' => 'Grade Publish Time:',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['announce_type1'] = array(
    'Big5' => '不公布',
    'GB2312' => '不公布',
    'en' => 'Will not be published',
    'EUC-JP' => '正解を表示しない',
    'user_define' => ''
);

$MSG['now'] = array(
    'Big5' => '即日起',
    'GB2312' => '即日起',
    'en' => 'Now',
    'EUC-JP' => '無制限',
    'user_define' => ''
);

$MSG['clear_before'] = array(
    'Big5' => '選取欲刪除的作業已有學員繳交記錄，如確定要刪除此作業前，\r\n請先執行畫面左方「作業維護工具列」中「清除作答記錄」後，\r\n再次進行刪除作業。',
    'GB2312' => '选取欲删除的作业已有学员缴交记录，如确定要删除此作业前，\r\n请先执行画面左方「作业维护工具列」中「清除作答记录」后，\r\n再次进行删除作业。',
    'en' => 'Select the job you want to delete. If you want to delete the job, please delete the job again after clearing the "Answer Record" in the "Job Maintenance Toolbar" on the left side of the screen.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['confirm_delete'] = array(
    'Big5' => '確定刪除作業嗎？確定請按「確定」，要停止請按「取消」。',
    'GB2312' => '确定删除作业吗？确定请按「确定」，要停止请按「取消」。',
    'en' => 'Are you sure you want to delete homeworks ?',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['pay_times'] = array(
    'Big5' => '繳交次數',
    'GB2312' => '缴交次数',
    'en' => 'The number of times to pay',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['once'] = array(
    'Big5' => '一次',
    'GB2312' => '一次',
    'en' => 'once',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['allow_repeated'] = array(
    'Big5' => '允許重覆繳交',
    'GB2312' => '允许重覆缴交',
    'en' => 'Allow repeated payment',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['whether_repay_within_delay_time'] = array(
    'Big5' => '是否可以在作答期限內繳交後，再進入重繳',
    'GB2312' => '是否可以在作答期限内缴交后，再进入重缴',
    'en' => 'Whether it can be paid within the delay time limit, and then enter the payback',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['msg_path_error'] = array(
    'Big5' => '檔案不存在。',
    'GB2312' => '档案不存在。',
    'en' => 'File does not exist.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['upload_file'] = array(
    'Big5' => '檔案',
    'GB2312' => '档案',
    'en' => 'File',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['upload_file_tip'] = array(
    'Big5' => '請選擇您所要匯入的作業壓縮包',
    'GB2312' => '请选择您所要汇入的作业压缩包',
    'en' => 'Please select the job archive you want to import.',
    'EUC-JP' => '',
    'user_define' => ''
);


$MSG['function_tip_title'] = array(
    'Big5' => '功能說明',
    'GB2312' => '功能说明',
    'en' => 'Function Description',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['function_tip'] = array(
    'Big5' => '1.須先執行「下載全部學員附檔」，下載取得此作業所有學員附檔的壓縮包，如：hw100000026.zip。<br/>2.將此作業壓縮包(hw100000026.zip)解壓縮後，進行作業附檔內容批改。<br/>3.完成批改後，將整份作業相關目錄再執行壓縮覆蓋回hw100000026.zip。<br/>4.匯入壓縮包後，會將各學員專屬目錄裡的檔案匯入各學員的參考檔案中，供學員查看。',
    'GB2312' => '1.须先执行「下载全部学员附档」，下载取得此作业所有学员附档的压缩包，如：hw100000026.zip。 <br/>2.将此作业压缩包(hw100000026.zip)解压缩后，进行作业附档内容批改。 <br/>3.完成批改后，将整份作业相关目录再执行压缩覆盖回hw100000026.zip。 <br/>4.汇入压缩包后，会将各学员专属目录里的档案汇入各学员的参考档案中，供学员查看。',
    'en' => '1. You must first execute the "Download All Student Attachments" to download the archives of all the students attached to this assignment, such as: hw100000026.zip. 2. Decompress the job archive (hw100000026.zip) and correct the job attachment content. 3. After the correction is completed, the entire job-related directory is further compressed and returned to hw100000026.zip. 4. After importing the compressed package, the files in the exclusive directory of each student will be imported into the reference files of each student for the students to view.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['btn_import'] = array(
    'Big5' => '匯入',
    'GB2312' => '汇入',
    'en' => 'Import',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['msg_error_filename'] = array(
    'Big5' => '您匯入的檔名錯誤',
    'GB2312' => '您汇入的档名错误',
    'en' => 'The file name you imported is incorrect.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['msg_error_type'] = array(
    'Big5' => '您匯入的檔案非支援的壓縮檔格式',
    'GB2312' => '您汇入的档案非支援的压缩档格式',
    'en' => 'Unsupported zip file format for files you import',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['batch_import_success'] = array(
    'Big5' => '作業批改整批匯入完成，請繼續進行作業評分！',
    'GB2312' => '作业批改整批汇入完成，请继续进行作业评分！',
    'en' => 'The job batch is completed and the batch is completed. Please continue to score the job!',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['overtime_delaytime'] = array(
    'Big5' => '逾時補繳',
    'GB2312' => '逾时补缴',
    'en' => 'OvertimeDelayTime',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['payback'] = array(
    'Big5' => '補繳',
    'GB2312' => '补缴',
    'en' => 'Payback',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['overtime_closetime'] = array(
    'Big5' => '逾時',
    'GB2312' => '逾时',
    'en' => 'OvertimeCloseTime',
    'EUC-JP' => '',
    'user_define' => ''
);



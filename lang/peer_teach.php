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
    'Big5' => '名稱',
    'GB2312' => '名称',
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
    'Big5' => '刪除作業後，將一併刪除學員已經繳交的作業以及成績。確定要繼續嗎？要繼續進行請按「確定」，要停止請按「取消」。',
    'GB2312' => '删除作业后，将一并删除学员已经缴交的作业以及成绩。确定要继续吗？要继续进行请按“确定”，要停止请按“取消”。',
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
    'Big5' => '預覽',
    'GB2312' => '预览',
    'en' => 'Preview',
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

$MSG['count_type_hint2'] = array(
    'Big5' => '本作業如何評分',
    'GB2312' => '本作业如何评分',
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

$MSG['required'] = array(
    'Big5' => '*顯示為必填',
    'GB2312' => '*显示为必填',
    'en' => '*This information is required',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['basic_info'] = array(
    'Big5' => '基本資料',
    'GB2312' => '基本资料',
    'en' => 'Basic information',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_rdoPublish_1'] = array(
    'Big5' => '準備中',
    'GB2312' => '准备中',
    'en' => 'In preparation.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['paying_answer'] = array(
    'Big5' => '繳交作答',
    'GB2312' => '缴交作答',
    'en' => 'Paying answer',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['response by attachment'] = array(
    'Big5' => '以附件作答',
    'GB2312' => '以附件作答',
    'en' => 'Response by attachment',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['rating_member'] = array(
    'Big5' => '評分人員',
    'GB2312' => '评分人员',
    'en' => 'Rated officers',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['peer_assessment'] = array(
    'Big5' => '互評',
    'GB2312' => '互评',
    'en' => 'Peer assessment',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['self_assessment'] = array(
    'Big5' => '自評',
    'GB2312' => '自评',
    'en' => 'Self assessment',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['teacher_rating'] = array(
    'Big5' => '老師評分',
    'GB2312' => '老师评分',
    'en' => 'Teacher rating',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['teacher_assessment'] = array(
    'Big5' => 'V&nbsp;&nbsp;老師評',
    'GB2312' => 'V&nbsp;&nbsp;老师评',
    'en' => 'V&nbsp;&nbsp;Teacher assessment',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['minimum_number'] = array(
    'Big5' => '最小份數',
    'GB2312' => '最小份数',
    'en' => 'The minimum number',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['rating_mode'] = array(
    'Big5' => '評分方式',
    'GB2312' => '评分方式',
    'en' => 'Rating mode',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['score_publish_peer'] = array(
    'Big5' => '開放觀摩',
    'GB2312' => '开放观摩',
    'en' => 'Open for Study',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['score_publish'] = array(
    'Big5' => '開放給分',
    'GB2312' => '开放给分',
    'en' => 'Open for score',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['chat_tone06'] = array(
    'Big5' => '公告',
    'GB2312' => '公告',
    'en' => 'Announce',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['achievement_results'] = array(
    'Big5' => '成績結果',
    'GB2312' => '成绩结果',
    'en' => 'Achievement results',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['msg_enable_begin'] = array(
    'Big5' => '開始',
    'GB2312' => '开始',
    'en' => 'Start',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['msg_enable_end'] = array(
    'Big5' => '結束',
    'GB2312' => '结束',
    'en' => 'End',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['msgPublish'] = array(
    'Big5' => '公布',
    'GB2312' => '公布',
    'en' => 'Publish',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['for_target'] = array(
    'Big5' => '對象',
    'GB2312' => '对象',
    'en' => 'Target',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['enable_duration1'] = array(
    'Big5' => '作答開放日期',
    'GB2312' => '作答开放日期',
    'en' => 'Test start date',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['enable_duration2'] = array(
    'Big5' => '作答結束日期',
    'GB2312' => '作答结束日期',
    'en' => 'Test close date',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['rehandin'] = array(
    'Big5' => '可重複繳交',
    'GB2312' => '可重复缴交',
    'en' => 'Whether hand in again',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['rating_notice'] = array(
    'Big5' => '評分標準說明',
    'GB2312' => '评分标准说明',
    'en' => 'Rating standards',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['th_open_time'] = array(
    'Big5' => '開放日期',
    'GB2312' => '开放日期',
    'en' => 'Open Date',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['msg_date_stop'] = array(
    'Big5' => '結束日期',
    'GB2312' => '结束日期',
    'en' => 'End date',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['rating criteria'] = array(
    'Big5' => '請寫出你的評分標準，例如「引用範例超過10個，為100分」',
    'GB2312' => '请写出你的评分标准，例如「引用范例超过10个，为100分」',
    'en' => 'Please write your rating criteria',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['acl_member_peer'] = array(
    'Big5' => '應繳者名單',
    'GB2312' => '应缴者名单',
    'en' => 'Students of assignment',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['acl_peer'] = array(
    'Big5' => '指定應繳者名單',
    'GB2312' => '指定应缴者名单',
    'en' => 'Assign students for the assignment',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['acl_member_help_peer'] = array(
    'Big5' => '勾選某種身份後，表示指定該身分之所有人員均應繳作業。若要增減個別人員，請按「個別帳號」。',
    'GB2312' => '勾选某种身份后，表示指定该身分之所有人员均应缴作业。若要增减个别人员，请按“个别帐号”。',
    'en' => 'If you choose some kind of roles, it mention that all members assign to this role must deliver homework. Please press "individual" button to add/remove individual member.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['rating_answer_required'] = array(
    'Big5' => '當有勾選互評或自評時，評分與作答起迄日期皆為必填，且評分開始日期要大於作答結束日期',
    'GB2312' => '当有勾选互评或自评时，评分与作答起迄日期皆为必填，且评分开始日期要大于作答结束日期',
    'en' => 'Rating date and answer date are required, and the rating start date is greater than answer end date.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['publish_greater_rating'] = array(
    'Big5' => '公布開始日期要大於評分結束日期',
    'GB2312' => '公布开始日期要大于评分结束日期',
    'en' => 'Publish start date is greater than the rating end date.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['publish_greater_answer'] = array(
    'Big5' => '公布開始日期要大於作答結束日期',
    'GB2312' => '成绩公布开始日期要大于作答结束日期',
    'en' => 'Score publish start date is greater than the answer end date.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['upload_attach'] = array(
    'Big5' => '附件繳交區',
    'GB2312' => '附件缴交区',
    'en' => 'Upload an attachment',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['peer_cede'] = array(
    'Big5' => '放棄此檔',
    'GB2312' => '放弃此档',
    'en' => 'Delete',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['attachement_msg'] = array(
    'Big5' => '每個檔案限%MIN_SIZE%，總和不得超過%MAX_SIZE%',
    'GB2312' => '每个档案限%MIN_SIZE%，总合不得超过%MAX_SIZE%',
    'en' => 'Each file cannot exceed %MIN_SIZE% , No more than %MAX_SIZE% in total.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['msg_answer_date_error'] = array(
    'Big5' => '作答結束日期必須大於作答開放日期，請重新設定。',
    'GB2312' => '作答结束日期必须大于作答开放日期，请重新设定。',
    'en' => 'Close date must be later than open date. Please reset.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['msg_rating_date_error'] = array(
    'Big5' => '評分結束日期必須大於開放日期，請重新設定。',
    'GB2312' => '评分结束日期必须大于开放日期，请重新设定。',
    'en' => 'Close date must be later than open date. Please reset.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['msg_score_date_error'] = array(
    'Big5' => '成績公告結束日期必須大於開始日期，請重新設定。',
    'GB2312' => '成绩公告结束日期必须大于开始日期，请重新设定。',
    'en' => 'Close date must be later than open date. Please reset.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['peer_order'] = array(
    'Big5' => '互評優先順序',
    'GB2312' => '互评优先顺序',
    'en' => 'Peer assessment order',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['no_priority'] = array(
    'Big5' => '沒有優先順序',
    'GB2312' => '没有优先顺序',
    'en' => 'No priority',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['peer_first'] = array(
    'Big5' => '先互評再自評',
    'GB2312' => '先互评再自评',
    'en' => 'Peer assessment First',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['self_first'] = array(
    'Big5' => '先自評再互評',
    'GB2312' => '先自评再互评',
    'en' => 'Self assessment First',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['now'] = array(
    'Big5' => '即日起',
    'GB2312' => '即日起',
    'en' => 'Now',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['forever'] = array(
    'Big5' => '無限期',
    'GB2312' => '无限期',
    'en' => 'Any Time',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['look_last'] = array(
    'Big5' => '觀看上次作業',
    'GB2312' => '观看上次作业',
    'en' => 'Look the last assignment',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['look_best'] = array(
    'Big5' => '觀看佳作',
    'GB2312' => '观看佳作',
    'en' => 'Look the best assignment',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['rd_student_homework'] = array(
    'Big5' => '繳交作業',
    'GB2312' => '缴交作业',
    'en' => 'Submitted Assignments',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['pay_period'] = array(
    'Big5' => '繳交期間',
    'GB2312' => '缴交期间',
    'en' => 'Submit period',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['rating'] = array(
    'Big5' => '進入評分',
    'GB2312' => '进入评分',
    'en' => 'Score',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['rating_period'] = array(
    'Big5' => '評分期間',
    'GB2312' => '评分期间',
    'en' => 'Score period',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['table_title9'] = array(
    'Big5' => '查看結果',
    'GB2312' => '查看结果',
    'en' => 'Check Results',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['announced_period'] = array(
    'Big5' => '公布期間',
    'GB2312' => '公布期间',
    'en' => 'Announced period',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['edit'] = array(
    'Big5' => '編輯',
    'GB2312' => '编辑',
    'en' => 'Edit',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['remove'] = array(
    'Big5' => '刪除',
    'GB2312' => '删除',
    'en' => 'Delete',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['clear'] = array(
    'Big5' => '清除作業成績',
    'GB2312' => '清除作业成绩',
    'en' => 'Clear assignments score',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['homeworkandreport'] = array(
    'Big5' => '作業與報告',
    'GB2312' => '作业与报告',
    'en' => 'Homework and report',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['addhomework'] = array(
    'Big5' => '新增作業',
    'GB2312' => '新增作业',
    'en' => 'New homework',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['homeworkpercent'] = array(
    'Big5' => '作業比重',
    'GB2312' => '作业比重',
    'en' => 'Homework percent',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['assignment_type'] = array(
    'Big5' => '作業型態',
    'GB2312' => '作业型态',
    'en' => 'Type',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['creator'] = array(
    'Big5' => '建立者',
    'GB2312' => '建立者',
    'en' => 'Creator',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['create_date'] = array(
    'Big5' => '建立時間',
    'GB2312' => '建立时间',
    'en' => 'Create date',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['th_status'] = array(
    'Big5' => '狀態',
    'GB2312' => '状态',
    'en' => 'Status',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['reference_count'] = array(
    'Big5' => '被引用<br>份數',
    'GB2312' => '被引用<br>份数',
    'en' => 'Reference',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['rating_count'] = array(
    'Big5' => '被評分<br>份數',
    'GB2312' => '被评分<br>份数',
    'en' => 'Rated',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['rating_scale_management'] = array(
    'Big5' => '評量表管理',
    'GB2312' => '评量表管理',
    'en' => 'Rating Scale Management',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['add'] = array(
    'Big5' => '新增',
    'GB2312' => '新增',
    'en' => 'Add',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['no_keyword'] = array(
    'Big5' => '沒有您要找的評量表，請重新搜尋!',
    'GB2312' => '没有您要找的评量表，请重新搜索!',
    'en' => 'No match found. Please try again.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['th_disable'] = array(
    'Big5' => '暫存',
    'GB2312' => '暂存',
    'en' => 'Disable',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['th_enable'] = array(
    'Big5' => '啟用',
    'GB2312' => '启用',
    'en' => 'Enable',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['th_modifying'] = array(
    'Big5' => '維護中',
    'GB2312' => '维护中',
    'en' => 'Modifying',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['add_checklist'] = array(
    'Big5' => '新增評量表',
    'GB2312' => '新增评量表',
    'en' => 'Add checklist',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['edit_checklist'] = array(
    'Big5' => '修改評量表',
    'GB2312' => '修改评量表',
    'en' => 'Modify checklist',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title6'] = array(
    'Big5' => '回評量表',
    'GB2312' => '回评量表',
    'en' => 'Return',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['store'] = array(
    'Big5' => '完成+存檔',
    'GB2312' => '完成+存档',
    'en' => 'Finish + Save',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['level'] = array(
    'Big5' => '級距',
    'GB2312' => '级距',
    'en' => 'Level',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['btn_set'] = array(
    'Big5' => '設定',
    'GB2312' => '设定',
    'en' => 'Setting',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['lnguage_hint'] = array(
    'Big5' => '最少要填寫其中一種語言，每種語言限填 254 字元',
    'GB2312' => '最少要填写其中一种语言，每种语言限填 254 字符',
    'en' => 'You have to select at least one language. No more than 254 characters in length.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['list_of_required'] = array(
    'Big5' => '應繳者名單為必填',
    'GB2312' => '应缴者名单为必填',
    'en' => 'List of Required',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title12'] = array(
    'Big5' => '請輸入評量表名稱',
    'GB2312' => '请输入评量表名称',
    'en' => 'Please enter the checklist\'s name.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title01'] = array(
    'Big5' => '請輸入級距得分',
    'GB2312' => '请输入级距得分',
    'en' => 'Please enter the value.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title02'] = array(
    'Big5' => '請輸入指標名稱',
    'GB2312' => '请输入指标名称',
    'en' => 'Please enter the indicator name.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title03'] = array(
    'Big5' => '請輸入指標說明',
    'GB2312' => '请输入指标说明',
    'en' => 'Please enter the indicator note.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title04'] = array(
    'Big5' => '級距分數最高分 * 指標項目數量 需等於100',
    'GB2312' => '级距分数最高分 * 指标项目数量 需等于100',
    'en' => 'The highest level score * indicators quantity need to be equal to 100.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title05'] = array(
    'Big5' => '級距分數需介於0-100之間',
    'GB2312' => '级距分数需介于0-100之间',
    'en' => 'Score must be between 0-100.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title06'] = array(
    'Big5' => '評量表點選「完成 + 存檔」後，即無法再還原成「暫存」的狀態，確定要繼續嗎？\n\n要繼續請按「確定」；要停止請按「取消」',
    'GB2312' => '评量表点选「完成 + 存档」后，即无法再还原成「暂存」的状态，确定要继续吗？\n\n要继续请按「确定」；要停止请按「取消」',
    'en' => 'System will not come back to status \'Disable\'.\n\nAre you sure you want to save ?',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title07'] = array(
    'Big5' => '注意！此評量表在以下作業中，已被引用且已被評分，請先「清除該作業成績」後才能修改「得分」欄位值',
    'GB2312' => '注意！此评量表在以下作业中，已被引用且已被评分，请先「清除该作业成绩」后才能修改「得分」栏位值',
    'en' => 'Please \'clear the assignment score\' first, in order to modify the level field values',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title08'] = array(
    'Big5' => '級距分數應為正整數',
    'GB2312' => '级距分数应为正整数',
    'en' => 'Score should be a positive integer.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title41'] = array(
    'Big5' => '刪除評量表後，將一併把有引用到的作業的「評分方式」欄位值改成「開放給分」\n\n要繼續請按「確定」；要停止請按「取消」',
    'GB2312' => '删除评量表后，将一并把有引用到的作业的「评分方式」栏位值改成「开放给分」\n\n要继续请按「确定」；要停止请按「取消」',
    'en' => 'Every assignment that referred this checklist will be changed to \'Open rating\'.\n\nAre you sure you want to delete ?',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['rd_student_homework'] = array(
    'Big5' => '繳交作業',
    'GB2312' => '缴交作业',
    'en' => 'Submitted Assignments',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['correct_less'] = array(
    'Big5' => ' 不需批改',
    'GB2312' => ' 不需批改',
    'en' => ' Without marking',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['checklist_del_mail'] = array(
    'Big5' => '你好：<br>' . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;以下作業有引用評量表「%EVALUATION%」來作為評分方式，<br>' . '但此評量表已被原作者- %ECREATOR% 刪除，故以下引用的作業的評分方式統一改成「開放給分」<br><br>' . '%HOMEWORKS%' . '<br>' . '若要改成其他評量方式，請到平台上動作！<br>' . '..............................................................<br>' . '本郵件為系統發送的信件，請勿直接回覆',
    'GB2312' => '你好：<br>' . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;以下作业有引用评量表「%EVALUATION%」来作为评分方式，<br>' . '但此评量表已被原作者- %ECREATOR% 删除，故以下引用的作业的评分方式统一改成「开放给分」<br><br>' . '%HOMEWORKS%' . '<br>' . '若要改成其他评量方式，请到平台上动作！ <br>' . '..............................................................<br>' . '本邮件为系统发送的信件，请勿直接回覆',
    'en' => '你好：<br>' . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;以下作業有引用評量表「%EVALUATION%」來作為評分方式，<br>' . '但此評量表已被原作者- %ECREATOR% 刪除，故以下引用的作業的評分方式統一改成「開放給分」<br><br>' . '%HOMEWORKS%' . '<br>' . '若要改成其他評量方式，請到平台上動作！<br>' . '..............................................................<br>' . '本郵件為系統發送的信件，請勿直接回覆',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['enter_peer_assessment'] = array(
    'Big5' => ' 同儕互評',
    'GB2312' => ' 同侪互评',
    'en' => ' Peer assessment',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['save_grade_success'] = array(
    'Big5' => '你已完成此次評分。',
    'GB2312' => '你已完成此次评分。',
    'en' => 'Grades saved.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['uploading_attachments_always'] = array(
    'Big5' => '統一以附件作答',
    'GB2312' => '统一以附件作答',
    'en' => 'Uploading attachments always',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['msg_max_100'] = array(
    'Big5' => '評分人員的比重總和不能超過100%',
    'GB2312' => '评分人员的比重总合不能超过100%',
    'en' => 'The proportion of the total combined score personnel can not exceed 100%',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['rating_open_date'] = array(
    'Big5' => '評分開放日期',
    'GB2312' => '评分开放日期',
    'en' => 'Rating Open Date',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['rating_close_date'] = array(
    'Big5' => '評分結束日期',
    'GB2312' => '评分结束日期',
    'en' => 'Rating End date',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['checklist_del_nochoice'] = array(
    'Big5' => '請勾選要刪除的評量表',
    'GB2312' => '请勾选要删除的评量表',
    'en' => 'Please select the checklist you want to delete.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['checklist_error_lengthlimit'] = array(
    'Big5' => '評量表名稱限填256個字元',
    'GB2312' => '评量表名称限填256个字元',
    'en' => 'No more than 256 characters in length.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['type'] = array(
    'Big5' => '類別',
    'GB2312' => '类别',
    'en' => 'Type',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['score'] = array(
    'Big5' => '得分',
    'GB2312' => '得分',
    'en' => 'Score',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['redo'] = array(
    'Big5' => '資料設定不正確，本筆請刪除重做',
    'GB2312' => '资料设定不正确，本笔请删除重做',
    'en' => 'Data set incorrectly, please delete it then redo',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['explanation'] = array(
    'Big5' => '說明',
    'GB2312' => '说明',
    'en' => 'Explanation',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['indicator'] = array(
    'Big5' => '指標',
    'GB2312' => '指标',
    'en' => 'Indicator',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['rating_over_100'] = array(
    'Big5' => '評分範圍需在0到100',
    'GB2312' => '评分范围需在0到100',
    'en' => 'Ratings range need to 0-100',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['back'] = array(
    'Big5' => '回上頁',
    'GB2312' => '回上页',
    'en' => 'Back',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['need_level_name'] = array(
    'Big5' => '請輸入級距名稱',
    'GB2312' => '请输入级距名称',
    'en' => 'Please enter the level name.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['need_rating_notice'] = array(
    'Big5' => '請輸入評分標準說明',
    'GB2312' => '请输入评分标准说明',
    'en' => 'Please enter a rating standard description.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['levelname_error_lengthlimit'] = array(
    'Big5' => '級距名稱限填256個字元',
    'GB2312' => '级距名称限填256个字元',
    'en' => 'No more than 256 characters in length.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['level_error_morethanprevious'] = array(
    'Big5' => '級距分數需小於前一個級距',
    'GB2312' => '级距分数需小于前一个级距',
    'en' => 'The level\'s score must be less than the previous one.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['pointname_error_lengthlimit'] = array(
    'Big5' => '指標名稱限填256個字元',
    'GB2312' => '指标名称限填256个字元',
    'en' => 'No more than 256 characters in length.',
    'EUC-JP' => '',
    'user_define' => ''
);

/*評量表級距分數設定*/
$MSG['no'] = array(
    'Big5' => '第',
    'GB2312' => '第',
    'en' => 'No. ',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['range_note1'] = array(
    'Big5' => '個',
    'GB2312' => '个',
    'en' => '',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['range_note2'] = array(
    'Big5' => '級距分數設定錯誤',
    'GB2312' => '级距分数设定错误',
    'en' => 'Scores level etting error',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['arabic_numerals'] = array(
    'Big5' => '正整數',
    'GB2312' => '正整数',
    'en' => 'Arabic numerals',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['arabic_numerals_range'] = array(
    'Big5' => '正整數數字範圍',
    'GB2312' => '正整数数字范围',
    'en' => 'Arabic numerals range',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['required_note'] = array(
    'Big5' => '必填',
    'GB2312' => '必填',
    'en' => 'Required',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['xss_attacks'] = array(
    'Big5' => '偵測到XSS攻擊',
    'GB2312' => '侦测到XSS攻击',
    'en' => 'Detect XSS attacks',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['need_peer_self'] = array(
    'Big5' => '自評或互評需擇一',
    'GB2312' => '自评或互评需择一',
    'en' => 'Please choose Self-assessment or peer assessment',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['need_peer_percent'] = array(
    'Big5' => '請填寫互評比重',
    'GB2312' => '请填写互评比重',
    'en' => 'Please fill in the proportion of mutual evaluation',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['need_self_percent'] = array(
    'Big5' => '請填寫自評比重',
    'GB2312' => '请填写自评比重',
    'en' => 'Please fill in the proportion of self assessment',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['tar_hw_wait'] = array(
		'Big5'			=> '作業檔案打包中，請稍候…',
		'GB2312'		=> '作业档案打包中，请稍候…',
		'en'			=> 'Job File Packing, please be waiting ...',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

$MSG['tar_hw_tip'] = array(
    'Big5'			=> '作業檔名已改用 UTF-8 編碼，解壓縮若有問題<br>，請看 <a href="javascript: return false;" onclick="handler()">說明</a>。',
	'GB2312'		=> '作业档名已改用 UTF-8 编码，解压缩若有问题<br>，请看 <a href="javascript: return false;" onclick="handler()">说明</a>。',
    'en' => 'File have changed coding UTF-8,please read the <a href="javascript: return false;" onclick="handler()">direction</a>,if the file have any problems.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['send_rating_confirm1'] = array(
    'Big5' => '成績',
    'GB2312' => '成绩',
    'en' => '',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['send_rating_confirm2'] = array(
    'Big5' => '分，確定嗎？',
    'GB2312' => '分，确定吗？',
    'en' => ', sure?',
    'EUC-JP' => '',
    'user_define' => ''
);
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
 * @version     CVS: $Id: questionnaire_teach.php,v 1.1 2010/02/24 02:39:03 saly Exp $
 * @link        http://demo.learn.com.tw/1000110138/index.html
 * @since       2006-07-11
 */
require_once dirname(__FILE__) . '/exam_teach.php';

$MSG['already_examed'] = array(
    'Big5' => '注意！\\n已經有學員繳交問卷！若您修改問卷中的任何設定，可能會造成調查結果或者統計結果上\\n的誤差，影響調查結果的信度。若您更動問卷中的題目、題數，只會對日後填寫的學員有\\n效，而已經繳過的學員，其問卷內容和填答結果並不會改變。\\n若問卷內容有重大變更，建議您可以在修改後對問卷做『重置』(清除學員作答紀錄)，並\\n通知學員重新作答。\\n要繼續修改請按「確定」，不修改請按「取消」。',
    'GB2312' => '注意！\\n已经有学员缴交问卷！若您修改问卷中的任何设定，可能会造成调查结果或者统计结果上\\n的误差，影响调查结果的信度。若您更动问卷中的题目、题数，只会对日后填写的学员有\\n效，而已经缴过的学员，其问卷内容和填答结果并不会改变。\\n若问卷内容有重大变更，建议您可以在修改后对问卷做‘重置’(清除学员作答纪录)，并\\n通知学员重新作答。\\n要继续修改请按“确定”，不修改请按“取消”。',
    'en' => 'Attention!\\nSome students had deliver questionaire! If you adjust any setting of questionaire, it will affect its fairness.\\nIf you adjust any setting of questionaire. It will be effective for those student not delivered. The score and content of those who deliver their questionaire will not affected. \\n If you really want to adjust questionaire, we suggest you to reset this questionaire after adjusting and inform student to deliver again.\\n To continue adjusting, please choose CONFIRM, or choose CANCEL. ',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['item_create'] = array(
    'Big5' => '新增題目',
    'GB2312' => '新增题目',
    'en' => 'Add Item',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['return_menu'] = array(
    'Big5' => '回維護題目清單',
    'GB2312' => '回维护题目清单',
    'en' => 'Back to Item Editing',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['voice_colon'] = array(
    'Big5' => '口語發音：',
    'GB2312' => '口语发音',
    'en' => 'Pronounce in voice:',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['candidate_item'] = array(
    'Big5' => '待選項目',
    'GB2312' => '待选项目',
    'en' => 'candidate item',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['item_modify'] = array(
    'Big5' => '修改題目',
    'GB2312' => '修改题目',
    'en' => 'Edit Item',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_type2'] = array(
    'Big5' => '自我評定',
    'GB2312' => '自我评定',
    'en' => 'Practice',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_type3'] = array(
    'Big5' => '平時問卷',
    'GB2312' => '平时问卷',
    'en' => 'Questionnaire',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_type4'] = array(
    'Big5' => '正式投票',
    'GB2312' => '正式投票',
    'en' => 'Formal Poll',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_type5'] = array(
    'Big5' => '線上問卷',
    'GB2312' => '线上问卷',
    'en' => 'Online Questionnaire',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_type6'] = array(
    'Big5'          => '愛上互動',
    'GB2312'        => '爱上互动',
    'en'            => 'Realtime Questionnaire',
    'EUC-JP'        => '',
    'user_define'   => ''
);

$MSG['publish_state2'] = array(
    'Big5' => '進行中',
    'GB2312' => '进行中',
    'en' => 'Ongoing',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['announce_type3'] = array(
    'Big5' => '問卷關閉後公布',
    'GB2312' => '问卷关闭后公布',
    'en' => 'Published after questionnaire is closed.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_maintain'] = array(
    'Big5' => '問卷維護',
    'GB2312' => '问卷维护',
    'en' => 'Questionnaire Editing',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_correct'] = array(
    'Big5' => '問卷檢視結果',
    'GB2312' => '问卷检查结果',
    'en' => 'Questionnaire Result',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_name'] = array(
    'Big5' => '問卷名稱',
    'GB2312' => '问卷名称',
    'en' => 'Questionnaire Name',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_use'] = array(
    'Big5' => '問卷用途',
    'GB2312' => '问卷用途',
    'en' => 'Purpose',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['count_type'] = array(
    'Big5' => '計分方式',
    'GB2312' => '计分方式',
    'en' => 'Calculating Formula',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_duration'] = array(
    'Big5' => '作答時間',
    'GB2312' => '作答时间',
    'en' => 'Questionnaire Duration',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_duration1_1'] = array(
    'Big5' => '開放受測者進入問卷的時間<br />不勾選「啟用」代表沒有限制日期',
    'GB2312' => '开放受测者进入问卷的时间<br />不勾选“启用”代表没有限制日期',
    'en' => 'Questionnaire start date. <br/> If Enable is unchecked, it means no time limit.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_duration1_2'] = array(
    'Big5' => '結束受測者進入問卷的時間<br />不勾選「啟用」代表沒有限制日期',
    'GB2312' => '结束受测者进入问卷的时间<br />不勾选“启用”代表没有限制日期',
    'en' => 'Questionnaire end date. <br/> If Enable is unchecked, it means no time limit.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['toolbtm06'] = array(
    'Big5' => '檢視結果',
    'GB2312' => '检查结果',
    'en' => 'View Results',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_toolbar'] = array(
    'Big5' => '問卷維護工具列',
    'GB2312' => '问卷维护工具列',
    'en' => 'Editing Tools',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['reset_confirm'] = array(
    'Big5' => '系統將會清除目前所選項目的全部學員填答的內容，\n確定要清除作答記錄嗎？',
    'GB2312' => '系统将会清除目前所选项目的全部学员填答的内容，\n确定要清除作答记录吗？',
    'en' => 'System will clear all of students&#039; answers of the selected items. \n Are you sure you want to reset?',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['delete_confirm'] = array(
    'Big5' => '您確定要刪除這些所選的項目嗎？',
    'GB2312' => '您确定要删除这些所选的项目吗？',
    'en' => 'Are you sure to delete these items?',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_paper'] = array(
    'Big5' => '問卷',
    'GB2312' => '问卷',
    'en' => 'Questionnaire',
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
    'Big5' => '建立問卷',
    'GB2312' => '建立问卷',
    'en' => 'Create Questionnaire',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_info'] = array(
    'Big5' => '問卷資訊',
    'GB2312' => '问卷资讯',
    'en' => 'Questionnaire Info',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_preview'] = array(
    'Big5' => '問卷預覽',
    'GB2312' => '问卷预览',
    'en' => 'Questionnaire Preview',
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

$MSG['pre-notice1'] = array(
    'Big5' => '顯示於進入問卷前一頁',
    'GB2312' => '显示于进入问卷前一页',
    'en' => 'Appear on the page before entering questionnaire.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_use1'] = array(
    'Big5' => '本問卷是作何用途',
    'GB2312' => '本问卷是作何用途',
    'en' => 'The purpose of this questionnaire',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['count_type_hint'] = array(
    'Big5' => '本問卷如何計分',
    'GB2312' => '本问卷如何计分',
    'en' => 'The calculating formula of this questionnaire.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_percent_hint'] = array(
    'Big5' => '佔學期總結果的比例',
    'GB2312' => '占学期总结果的比例',
    'en' => 'Percent of Final Result',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['examinee'] = array(
    'Big5' => '問卷對象',
    'GB2312' => '问卷对象',
    'en' => 'Questionnaire Target',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['corrector'] = array(
    'Big5' => '閱卷人員',
    'GB2312' => '阅卷人员',
    'en' => 'Evaluator',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_times'] = array(
    'Big5' => '問卷次數',
    'GB2312' => '问卷次数',
    'en' => '# of attempts allowed',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_times_hint'] = array(
    'Big5' => '已經問卷過的次數',
    'GB2312' => '已经问卷过的次数',
    'en' => '# of attempts',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_duration_hint'] = array(
    'Big5' => '進行問卷的時間',
    'GB2312' => '进行问卷的时间',
    'en' => 'Time allowed to answer the questionnaire questions',
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
    'Big5' => '是否強制問卷者不得使用其它軟體',
    'GB2312' => '是否强制问卷者不得使用其它软件',
    'en' => 'Prevent participants from using other software?',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['timeout_control_hint'] = array(
    'Big5' => '實施時間到後，LMS應作何處置',
    'GB2312' => '实施时间到后，LMS应作何处置',
    'en' => 'What would you like LMS to do when questionnaire time is up?',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['score_publish'] = array(
    'Big5' => '結果公布',
    'GB2312' => '结果公布',
    'en' => 'Results',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['score_publish2'] = array(
    'Big5' => '關閉問卷後公布',
    'GB2312' => '关闭问卷后公布',
    'en' => 'Published after questionnaire is closed',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['score_publish_hint'] = array(
    'Big5' => '公布結果的時刻',
    'GB2312' => '公布结果的时刻',
    'en' => 'When to publish grades',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['search_hint'] = array(
    'Big5' => '請勾選條件開始搜尋題目，並從搜尋結果中挑選題目加入到這份考卷內。',
    'GB2312' => '请勾选条件开始搜索题目，并从搜索结果中挑选题目加入到这份考卷内。',
    'en' => 'Please set up your search query and select items to add to this questionnaire.',
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
	<li>搬移題目：勾選題目後，再點選【問卷】或【大題】即可將勾選題目搬移至該大題中</li>
	<li>群組說明：勾選【問卷】或【大題】後，點擊【大題文字】，可以編輯該大題的前置說明</li>
	<li>移除群組：勾選【大題】後，點擊【移除大題】，即可移除該大題及所含題目</li>
	<li>移除題目：勾選【題目】後，點擊【移除題目】，即可移除所勾選的題目</li>
	<li>指定分數：勾選【題目】後，點擊【指定分數】輸入分數，則勾選的題目即會指定成該分數。</li>
	<li>平均配分：按【平均配分】輸入總分數，會平均分配給所有題目 (若有餘數則加給最後一題)。</li>
	<li>移動位置：勾選【大題】或【題目】後，點擊【上移】或【下移】，即可移動位置</li>
	<li>題目的內容依序為：<font color="gray">[題型][配分]</font> 題目標題 <font color="gray">[版,冊,章,節,段][難易度]</font></li>
	',
    'GB2312' => '<li>搬移题目：勾选题目后，再点选【问卷】或【大题】即可将勾选题目搬移至该大题中</li><li>群组说明：勾选【问卷】或【大题】后，点击【大题文字】，可以编辑该大题的前置说明</li><li>移除群组：勾选【大题】后，点击【移除大题】，即可移除该大题及所含题目</li><li>移除题目：勾选【题目】后，点击【移除题目】，即可移除所勾选的题目</li><li>指定分数：勾选【题目】后，点击【指定分数】输入分数，则勾选的题目即会指定成该分数。</li><li>平均配分：按【平均配分】输入总分数，会平均分配给所有题目 (若有余数则加给最后一题)。</li><li>移动位置：勾选【大题】或【题目】后，点击【上移】或【下移】，即可移动位置</li><li>题目的内容依序为：<font color=gray>[题型][配分]</font> 题目标题 <font color=gray>[版,册,章,节,段][难易度]</font></li>',
    'en' => '<li>Move Items: Select items and then click Assignment or Section as destination for the selceted items.</li><li>Section Instruction: If you want to edit section instruction, select Assignment or Section and then click Section Instruction.</li><li>Remove Section: Select the section you want to remove and then click Remove Section. All the items in the selected section will be removed.</li><li>Remove Item: Select the items you want to remove and then click Remove Item. All the selected items will be removed.</li><li>Assign Points: Select items and then click Assign Points. Enter the number of points you want to assign to the selected items.</li><li>Average Weighting: Click Average Weighting and enter total points. The average will be assigned to all items. (Remainder will be added to the last item.)</li><li>Location: Select section or item, click Move Up or Move Down, and the selected section(s) or item(s) will be moved to the designated place.</li><li>Item contents will be displayed in the following order: <font color=gray>[Type][Weighting]</font> Title <font color=gray>[Edition, Book, Chapter, Unit, Paragraph][Level]</font></li>',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_import'] = array(
    'Big5' => '匯入問卷',
    'GB2312' => '导入问卷',
    'en' => 'Import Questionnaire',
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

$MSG['msg_cancel_all'] = array(
    'Big5' => '全消',
    'GB2312' => '全消',
    'en' => 'Cancel',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exim_times_list'] = array(
    'Big5' => '問卷別列表',
    'GB2312' => '问卷别列表',
    'en' => 'Questionnaire List',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_state'] = array(
    'Big5' => '問卷發布/準備中',
    'GB2312' => '问卷发布/准备中',
    'en' => 'Status',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['save_grade_failure'] = array(
    'Big5' => '儲存結果發生錯誤：',
    'GB2312' => '保存结果发生错误：',
    'en' => 'Error while saving results:',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['save_grade_success'] = array(
    'Big5' => '儲存結果完成。',
    'GB2312' => '保存结果完成。',
    'en' => 'Results saved!',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['grade_no_mdify'] = array(
    'Big5' => '結果未更動。',
    'GB2312' => '结果未更动。',
    'en' => 'Results not changed.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['continue_correct'] = array(
    'Big5' => '請點選學員或次數別，繼續檢視結果。',
    'GB2312' => '请点选学员或次数别，继续检查结果。',
    'en' => 'Please select students to continue veiwing results.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['save_correct'] = array(
    'Big5' => '儲存檢視結果',
    'GB2312' => '保存检查结果',
    'en' => 'Save Results',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['enable_duration'] = array(
    'Big5' => '作答起訖時間',
    'GB2312' => '作答起讫时间',
    'en' => 'Questionnaire Period',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['enable_duration1'] = array(
    'Big5' => '開放作答日期',
    'GB2312' => '开放作答日期',
    'en' => 'Questionnaire Start Date',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['enable_duration2'] = array(
    'Big5' => '關閉作答日期',
    'GB2312' => '关闭作答日期',
    'en' => 'Questionnaire End Date',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['grade'] = array(
    'Big5' => '結果',
    'GB2312' => '结果',
    'en' => 'Results',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['earn'] = array(
    'Big5' => '得分：',
    'GB2312' => '得分：',
    'en' => 'Total:',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_context'] = array(
    'Big5' => '問卷內容',
    'GB2312' => '问卷内容',
    'en' => 'Questionaire ',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['status_break'] = array(
    'Big5' => '未繳',
    'GB2312' => '未缴',
    'en' => 'Questionnaire Content',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['status_revised'] = array(
    'Big5' => '已檢視結果',
    'GB2312' => '已检查结果',
    'en' => 'Results Checked',
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
    'Big5' => '問卷統計',
    'GB2312' => '问卷统计',
    'en' => 'Questionnaire Stats',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['score_depend'] = array(
    'Big5' => '此為電腦閱卷之自動計分。實際得分以教師公佈之正式分數為準。',
    'GB2312' => '此为电脑阅卷之自动计分。实际得分以教师公布之正式分数为准。',
    'en' => 'This grade has been automatically calculated by system. The final result will be issued by the instructor.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['score_remind'] = array(
    'Big5' => '警告！您未設定所有題目配分，將會造成（部分）問卷結果是０分，確定存檔離開？',
    'GB2312' => '警告！您未设定所有题目配分，将会造成（部分）问卷结果是０分，确定存档离开？',
    'en' => 'The total points for (part of) this questionnaire is 0. Still want to save?',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['changed_but_not_saved'] = array(
    'Big5' => '您尚未儲存修改過的問卷順序。確定要離開嗎？',
    'GB2312' => '您尚未保存修改过的问卷顺序。确定要离开吗？',
    'en' => 'You haven&#039;t saved modifications. Are you sure you want to exit?',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['tab_exam_times'] = array(
    'Big5' => '測驗次別',
    'GB2312' => '测验次别',
    'en' => 'Questionnaire No.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['item_remove'] = array(
    'Big5' => '刪除試題',
    'GB2312' => '删除试题',
    'en' => 'Remove item',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['remove_result'] = array(
    'Big5' => '刪除結果',
    'GB2312' => '删除结果',
    'en' => 'Delete result',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['remove_success'] = array(
    'Big5' => '刪除成功 ',
    'GB2312' => '删除成功 ',
    'en' => 'Delete successfully',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['remove_fail'] = array(
    'Big5' => '刪除失敗',
    'GB2312' => '删除失败',
    'en' => 'Delete failed',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['remove_fail_ref'] = array(
    'Big5' => '<font color="red">已被以下問卷引用，請先到該問卷刪除：</font>',
    'GB2312' => '<font color="red">已被以下问卷引用，请先到该问卷删除：</font>',
    'en' => 'This item is referred by those questionaires, please remove item from those questionaires.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['return_item_maintain'] = array(
    'Big5' => '回題庫維護',
    'GB2312' => '回题库维护',
    'en' => 'Return to maintainance of item',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['result description'] = array(
    'Big5' => '以下為本問卷中的「選擇題」(含是非題、單選題、複選題)之作答次數統計。<br>若要看非選擇題的作答內容，請按',
    'GB2312' => '以下为本问卷中的“选择题”(含是非题、单选题、复选题)之作答次数统计。<br>若要看非选择题的作答内容，请按',
    'en' => 'Those below are "choice"(include true/false, single choice and multi choice) result statistic.<br>If you want to see the result of non-choice, please click',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['total_quests'] = array(
    'Big5' => '總問卷數',
    'GB2312' => '总问卷数',
    'en' => 'Total Questionnaires',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['valid_quests'] = array(
    'Big5' => '有效問卷數',
    'GB2312' => '有效问卷数',
    'en' => 'Total number of valid Questionnaires',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['invalid_quests'] = array(
    'Big5' => '無效問卷數',
    'GB2312' => '无效问卷数',
    'en' => 'Total number of invalid Questionnaires',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['anonymous or not'] = array(
    'Big5' => '是否記名',
    'GB2312' => '是否记名',
    'en' => 'anonymous or not',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['anonymous'] = array(
    'Big5' => '不記名',
    'GB2312' => '不记名',
    'en' => 'anonymous',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['named'] = array(
    'Big5' => '記名',
    'GB2312' => '记名',
    'en' => 'named',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['anonymous or not hint'] = array(
    'Big5' => '是否記錄作答者的帳號姓名？',
    'GB2312' => '是否记录作答者的帐号姓名？',
    'en' => 'record the user\'s name or not ?',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['copy to'] = array(
    'Big5' => '複製問卷到...',
    'GB2312' => '复制问卷到...',
    'en' => 'Copy to ...',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['return list'] = array(
    'Big5' => '回問卷列表',
    'GB2312' => '回问卷列表',
    'en' => 'Return questionnaire list',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['edit instance'] = array(
    'Big5' => '修改此問卷',
    'GB2312' => '修改此问卷',
    'en' => 'Edit this questionnaire',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['btnClose'] = array(
    'Big5' => '關閉視窗',
    'GB2312' => '关闭视窗',
    'en' => 'Close window',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['private access tip'] = array(
    'Big5' => '封閉型問卷 (依下方填寫對象限制填寫者)',
    'GB2312' => '封闭型问卷 (依下方填写对象限制填写者)',
    'en' => 'private access in accordance with the target list below.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['public access tip'] = array(
    'Big5' => '開放型問卷 (URL 可供任何人直連進入填寫)',
    'GB2312' => '开放型问卷 (URL 可供任何人直连进入填写)',
    'en' => 'public access immediately for anyone (login is unnecessary).',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['access mode'] = array(
    'Big5' => '問卷類型',
    'GB2312' => '问卷类型',
    'en' => 'Access Mode',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['public access type'] = array(
    'Big5' => '開放型',
    'GB2312' => '开放型',
    'en' => 'PUBLIC ACCESS',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['export_no_items'] = array(
    'Big5' => '此問卷中無任何題目，無法立即匯出問卷。請先進入問卷中「挑選題目」，再將您所選擇的問卷「匯出」。',
    'GB2312' => '此问卷中无任何题目，无法立即汇出问卷。请先进入问卷中“挑选题目”，再将您所选择的问卷“汇出”。',
    'en' => 'There is no any item in the questionnaire. To export this questionnaire, please add some items to it and then export again.',
    'EUC-JP' => '',
    'user_define' => ''
);
    
$MSG['clear_before'] = array(
    'Big5' => '選取欲刪除的問卷已有學員繳交記錄，如確定要刪除此問卷前，\r\n請先執行畫面左方「問卷維護工具列」中「清除作答記錄」後，\r\n再次進行刪除問卷。',
    'GB2312' => '选取欲删除的问卷已有学员缴交记录，如确定要删除此问卷前，\r\n请先执行画面左方「问卷维护工具列」中「清除作答记录」后，\r\n再次进行删除问卷。',
    'en' => 'If you want to delete this questionnaire, please delete the questionnaire again after "clearing the answer record" in the "Questionnaire Maintenance Toolbar" on the left side of the screen.',
    'EUC-JP' => '',
    'user_define' => ''
);
    
$MSG['confirm_delete'] = array(
    'Big5' => '確定刪除問卷嗎？確定請按「確定」，要停止請按「取消」。',
    'GB2312' => '确定删除问卷吗？确定请按「确定」，要停止请按「取消」。',
    'en' => 'Are you sure you want to delete questionnaires ?',
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
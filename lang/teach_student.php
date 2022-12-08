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

$MSG['create_account'] = array(
    'Big5' => '增刪學員',
    'GB2312' => '增删学员',
    'en' => 'Add/Remove student(s)',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['addnormal_help'] = array(
    'Big5' => '範例：<br />每行一個帳號。<br />',
    'GB2312' => '范例：<br />每行一个帐号。<br />',
    'en' => 'Example:<br /> An account each line',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['import_format_help'] = array(
    'Big5' => '您的檔案使用何種語言編碼（字集）？',
    'GB2312' => '您的档案使用何种语言编码（字集）？',
    'en' => 'What character encoding do your files use?',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['msgBig5'] = array(
    'Big5' => '正體中文(Big5)',
    'GB2312' => '繁体中文(Big5)',
    'en' => 'Big5',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['msgGB2312'] = array(
    'Big5' => '簡體中文(GB2312)',
    'GB2312' => '简体中文(GB2312)',
    'en' => 'GB2312',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['msgen'] = array(
    'Big5' => '英文(en)',
    'GB2312' => '英文(en)',
    'en' => 'en',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['msgEUC_JP'] = array(
    'Big5' => '日文(EUC-JP)',
    'GB2312' => '日文(EUC-JP)',
    'en' => 'EUC-JP',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['msgUTF-8'] = array(
    'Big5' => 'UTF-8',
    'GB2312' => 'UTF-8',
    'en' => 'UTF-8',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['create_serial_account'] = array(
    'Big5' => '連續帳號',
    'GB2312' => '连续帐号',
    'en' => 'Serial Account',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['create_discrete_account'] = array(
    'Big5' => '不規則帳號',
    'GB2312' => '不规则帐号',
    'en' => 'Discrete Account',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['import_account'] = array(
    'Big5' => '匯入帳號',
    'GB2312' => '导入帐号',
    'en' => 'Import Account',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['header'] = array(
    'Big5' => '前置文字',
    'GB2312' => '前置文字',
    'en' => 'Prefix String',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['tail'] = array(
    'Big5' => '後置文字',
    'GB2312' => '后置文字',
    'en' => 'Suffix String',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['first'] = array(
    'Big5' => '從',
    'GB2312' => '从',
    'en' => 'from',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['last'] = array(
    'Big5' => '至',
    'GB2312' => '至',
    'en' => 'to',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['number'] = array(
    'Big5' => '帳號個數',
    'GB2312' => '帐号个数',
    'en' => 'Account beginning and end',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['length'] = array(
    'Big5' => '數字欄位',
    'GB2312' => '数字栏位',
    'en' => 'Number of digits',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['len'] = array(
    'Big5' => '位',
    'GB2312' => '位',
    'en' => 'digits',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['create_help01'] = array(
    'Big5' => '範例：<br />
例如要處理帳號 m89103001 ～ m89103050<br />
則上述欄位可填『m』『89103001』『89103050』『』『8』<br />
或者可填　　　『m89103』『1』『50』『』『3』',
    'GB2312' => '范例：<br />例如要处理帐号 m89103001 ～ m89103050<br />则上述栏位可填[m][89103001][89103050][][8]<br />或者可填　　　[m89103][1][50][][3]',
    'en' => 'Example:<br />If you want accounts from m89103001 to m89103050,<br />you can fill in[m][89103001][89103050][][8],<br />or[m89103][1][50][][3]',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['create_help04'] = array(
    'Big5' => '請選擇一個CSV格式的檔案。<div style="height: 0.3em;">&nbsp;</div>如何產生CSV檔案：<br />1.CSV格式須為每行一筆資料的純文字檔案<br />2.使用記事本編輯並儲存為.CSV檔<br />3.使用EXCEL編輯並另存新檔，其存檔類型選「*.csv」格式',
    'GB2312' => '请选择一个CSV格式的档案。 <div style="height: 0.3em;">&nbsp;</div>如何产生CSV档案：<br />1.CSV格式须为每行一笔资料的纯文字档案<br />2.使用记事本编辑并储存为.CSV档<br / >3.使用EXCEL编辑并另存新档，其存档类型选「*.csv」格式',
    'en' => 'Please select text-only files in which there is only one entry each line.<br /> Example: .CSV file<br />',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['js_msg01'] = array(
    'Big5' => '請必須填寫前置字元。',
    'GB2312' => '请必须填写前置字符。',
    'en' => 'Prefix characters are required.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['addrm'] = array(
    'Big5' => '新增/移除 學員',
    'GB2312' => '新增/移除 学员',
    'en' => 'Add/Remove student(s)',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['add_student'] = array(
    'Big5' => '新增正式生',
    'GB2312' => '新增正式生',
    'en' => 'Add Enrolled Student',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['add_auditor'] = array(
    'Big5' => '新增旁聽生',
    'GB2312' => '新增旁听生',
    'en' => 'Add Auditor',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['aud2stu'] = array(
    'Big5' => '旁聽生變正式生',
    'GB2312' => '旁听生变正式生',
    'en' => 'Auditors become enrolled students',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['stu2aud'] = array(
    'Big5' => '正式生變旁聽生',
    'GB2312' => '正式生变旁听生',
    'en' => 'Enrolled students become auditors',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['remove'] = array(
    'Big5' => '移除',
    'GB2312' => '移除',
    'en' => 'Remove',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['clean'] = array(
    'Big5' => '清除輸入',
    'GB2312' => '清除输入',
    'en' => 'Clear',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['process'] = array(
    'Big5' => '處理',
    'GB2312' => '处理',
    'en' => 'Process',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['result'] = array(
    'Big5' => '結果',
    'GB2312' => '结果',
    'en' => 'Results',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['and_student'] = array(
    'Big5' => '賦予正式生身分',
    'GB2312' => '赋予正式生身分',
    'en' => 'Permit Enrolled Student Access',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['and_auditor'] = array(
    'Big5' => '賦予旁聽生身分',
    'GB2312' => '赋予旁听生身分',
    'en' => 'Permit Auditor Access',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['already_is_student'] = array(
    'Big5' => '已經是正式生',
    'GB2312' => '已经是正式生',
    'en' => 'Already an enrolled student',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['already_is_auditor'] = array(
    'Big5' => '已經是旁聽生',
    'GB2312' => '已经是旁听生',
    'en' => 'Already an auditor',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['not_student'] = array(
    'Big5' => '非正式生，無法轉為旁聽生',
    'GB2312' => '非正式生，无法转为旁听生',
    'en' => 'This account is not enrolled student, and it can not switch into auditor',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['not_auditor'] = array(
    'Big5' => '非旁聽生，無法轉為正式生',
    'GB2312' => '非旁听生，无法转为正式生',
    'en' => 'This account is not auditor, and it can not switch into enrolled student',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['failure'] = array(
    'Big5' => '<font color="red">失敗</font>',
    'GB2312' => '<font color=red>失败</font>',
    'en' => '<font color=red>Failed</font>',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['unknown_user'] = array(
    'Big5' => '<font color="purple">查無此帳號</font>',
    'GB2312' => '<font color=purple>查无此帐号</font>',
    'en' => '<font color=purple>Account not found.</font>',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['format_incorrect'] = array(
    'Big5' => '<font color="red">格式不正確</font>',
    'GB2312' => '<font color=red>格式不正确</font>',
    'en' => '<font color=red>Invalid format</font>',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['not_student_of_curr_course'] = array(
    'Big5' => '<font color="red">非本課程學生，無法移除</font>',
    'GB2312' => '<font color=red>非本课程学生，无法移除</font>',
    'en' => '<font color="red">This account can not remove because it is not student of this courses.</font>',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['student_grouping'] = array(
    'Big5' => '學員分組管理',
    'GB2312' => '学员分组管理',
    'en' => 'Student Grouping',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['never_grouping'] = array(
    'Big5' => '尚未分組',
    'GB2312' => '尚未分组',
    'en' => 'Ungrouped',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['new_group'] = array(
    'Big5' => '新組別',
    'GB2312' => '新组别',
    'en' => 'New Group',
    'EUC-JP' => 'NEW_TEAM',
    'user_define' => 'NEW_TEAM'
);

$MSG['add_groupingtimes'] = array(
    'Big5' => '新增',
    'GB2312' => '新增',
    'en' => 'Add',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['new_grouping_times'] = array(
    'Big5' => '新增分組次',
    'GB2312' => '新增分组次',
    'en' => 'More Parent Group',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['grouping_times'] = array(
    'Big5' => '分組次 ',
    'GB2312' => '分组次',
    'en' => 'Parent Groups',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['rm_grouping_times'] = array(
    'Big5' => '刪除',
    'GB2312' => '删除',
    'en' => 'Delete',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['add_group'] = array(
    'Big5' => '新增群組',
    'GB2312' => '新增群组',
    'en' => 'Add Group',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['rm_group'] = array(
    'Big5' => '移除群組',
    'GB2312' => '移除群组',
    'en' => 'Remove Group',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['grouping_complete'] = array(
    'Big5' => '完成分組',
    'GB2312' => '完成分组',
    'en' => 'Finished',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['clean_grouping'] = array(
    'Big5' => '重新分組',
    'GB2312' => '重新分组',
    'en' => 'Reset',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['separating_sort'] = array(
    'Big5' => '分組次管理',
    'GB2312' => '分组次管理',
    'en' => 'Grouping Management',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['team_rename'] = array(
    'Big5' => '分組次更名',
    'GB2312' => '分组次更名',
    'en' => 'Rename',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['grouping_tip'] = array(
    'Big5' => '<ol style="color: white; font-weight: bold">
<li>勾選學員姓名後，再點小組名稱，就可以將學員分派至該組。</li>
<li>小組人員分派完成後，請按「完成分組」儲存結果才算分組完畢。</li>
<li>新增的小組中必須加入成員。若小組中沒有任何成員，將被視為無效的小組而無法成立。</li>
</ol>',
    'GB2312' => '<ol style=color: red; font-weight: bold><li>勾选学员姓名后，再点小组名称，就可以将学员分派至该组。</li><li>小组人员分派完成后，请按“完成分组”保存结果才算分组完毕。</li><li>新增的小组中必须加入成员。若小组中没有任何成员，将被视为无效的小组而无法成立。</li></ol>',
    'en' => '<ol style=color: red; font-weight: bold><li>Check the boxes next to the students and then click on a group name. The selected students will be assigned to the group.</li><li>After all students are grouped, click Finished.</li><li>You should assign members to the newly added group. If not, the new group will be considered invalid.</li></ol>',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['save_permuting'] = array(
    'Big5' => '儲存順序',
    'GB2312' => '保存顺序',
    'en' => 'Save',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['group_property'] = array(
    'Big5' => '小組內容',
    'GB2312' => '小组内容',
    'en' => 'Group Properties',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['team_name'] = array(
    'Big5' => '分組次名稱',
    'GB2312' => '分组次名称',
    'en' => 'Parent Group Name',
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

$MSG['captain'] = array(
    'Big5' => '組長',
    'GB2312' => '组长',
    'en' => 'Group Leader',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['other_member'] = array(
    'Big5' => '其它成員',
    'GB2312' => '其它成员',
    'en' => 'Other Members',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['complete'] = array(
    'Big5' => '確定',
    'GB2312' => '确定',
    'en' => 'OK',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['cancel'] = array(
    'Big5' => '取消',
    'GB2312' => '取消',
    'en' => 'Cancel',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['js_msg09'] = array(
    'Big5' => '執行重新分組，會將所有組內的人員歸入『尚未分組』的團體中。確定要真的重新分組嗎？',
    'GB2312' => '执行重新分组，会将所有组内的人员归入‘尚未分组’的团体中。确定要真的重新分组吗？',
    'en' => 'If you reset, all people will be moved to the Ungrouped area. Are you sure you want to reset?',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['chk_del'] = array(
    'Big5' => '確定刪除?',
    'GB2312' => '确定删除?',
    'en' => 'Are you sure you want to delete?',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['chk_delgroup'] = array(
    'Big5' => '移除群組的話，會將群組中的人員歸類到「尚未分組」的群體中，並將該群組的討論板與討論室刪除。確定要移除群組嗎？',
    'GB2312' => '移除群组的话，会将群组中的人员归类到“尚未分组”的群体中，并将该群组的讨论板与讨论室删除。确定要移除群组吗？',
    'en' => 'If you remove a group, all memebers in the group will be moved to the Ungrouped area. The discussion forum and chat room in the group will also be deleted. Are you sure you want to remove this group?',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['btm_modify'] = array(
    'Big5' => '修改',
    'GB2312' => '修改',
    'en' => 'Edit',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['save_ok'] = array(
    'Big5' => '新增分組次完成',
    'GB2312' => '新增分组次完成',
    'en' => 'New parent groups successfully added.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['move_down_error'] = array(
    'Big5' => '您所勾選的組次已經位於最後一位，請勿再往下移動',
    'GB2312' => '您所勾选的组次已经位于最后一位，请勿再往下移动',
    'en' => 'The selected group is already at the bottom. It cannot be moved down anymore.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['move_up_error'] = array(
    'Big5' => '您所勾選的組次已經位於第一位，請勿再往上移動',
    'GB2312' => '您所勾选的组次已经位于第一位，请勿再往上移动',
    'en' => 'The selected group is already on the top. It cannot be moved up anymore.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['group_div'] = array(
    'Big5' => '分組討論',
    'GB2312' => '分组讨论',
    'en' => 'Group Discussion',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['select_div'] = array(
    'Big5' => '請選擇分組任務',
    'GB2312' => '请选择分组任务',
    'en' => 'Please select group tasks.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['serial_1'] = array(
    'Big5' => '編號',
    'GB2312' => '编号',
    'en' => 'Number',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['peoples'] = array(
    'Big5' => '人數',
    'GB2312' => '人数',
    'en' => 'Total<br>number<br>of People',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['board'] = array(
    'Big5' => '討論板',
    'GB2312' => '讨论板',
    'en' => 'Discussion<br>Forum',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['discussion'] = array(
    'Big5' => '討論室',
    'GB2312' => '讨论室',
    'en' => 'Chat Room',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['mail_mem'] = array(
    'Big5' => '寄給組員',
    'GB2312' => '寄给组员',
    'en' => 'Email<br>Members',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['attri'] = array(
    'Big5' => '屬性',
    'GB2312' => '属性',
    'en' => 'Properties',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['note'] = array(
    'Big5' => '張貼',
    'GB2312' => '张贴',
    'en' => 'Post',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['mails'] = array(
    'Big5' => '寄信',
    'GB2312' => '寄信',
    'en' => 'Email',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['manage'] = array(
    'Big5' => '管理',
    'GB2312' => '管理',
    'en' => 'Edit',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['mem_list'] = array(
    'Big5' => '組員名單',
    'GB2312' => '组员名单',
    'en' => 'Memeber List',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['capacity'] = array(
    'Big5' => '身分',
    'GB2312' => '身分',
    'en' => 'Access',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['members'] = array(
    'Big5' => '組員',
    'GB2312' => '组员',
    'en' => 'Members',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['close'] = array(
    'Big5' => '關閉',
    'GB2312' => '关闭',
    'en' => 'Close',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['chose_mem'] = array(
    'Big5' => '請勾選收信人',
    'GB2312' => '请勾选收信人',
    'en' => 'Please select receivers.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['td_alt_sel'] = array(
    'Big5' => '全選或全消',
    'GB2312' => '全选或全消',
    'en' => 'Select All or Cancel Select',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['step1'] = array(
    'Big5' => '下一步',
    'GB2312' => '下一步',
    'en' => 'Next',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['group_notmem'] = array(
    'Big5' => '目前並無任何小組',
    'GB2312' => '目前并无任何小组',
    'en' => 'There are no groups now.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['manage1'] = array(
    'Big5' => '小組屬性設定',
    'GB2312' => '小组属性设定',
    'en' => 'Group Properties Setup',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['manage2'] = array(
    'Big5' => '小組討論板設定',
    'GB2312' => '小组讨论板设定',
    'en' => 'Group Discussion Forum Setup',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['manage3'] = array(
    'Big5' => '小組討論室設定',
    'GB2312' => '小组讨论室设定',
    'en' => 'Group Chatroom Setup',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['set_group_attri'] = array(
    'Big5' => '請設定本小組名稱及屬性。',
    'GB2312' => '请设定本小组名称及属性。',
    'en' => 'Please set group name and properties.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['set_chief'] = array(
    'Big5' => '指定組長',
    'GB2312' => '指定组长',
    'en' => 'Assign group leader',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['set_chief_memo'] = array(
    'Big5' => '(尚未有小組成員，請先指派小組成員之後再來設定組長。)',
    'GB2312' => '(尚未有小组成员，请先指派小组成员之后再来设定组长。)',
    'en' => 'No members in this group. Please assign members before assigning the group leader.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['forum_name'] = array(
    'Big5' => '討論板名稱',
    'GB2312' => '讨论板名称',
    'en' => 'Name of Discussion Forum',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title_help'] = array(
    'Big5' => '討論板主旨',
    'GB2312' => '讨论板主题',
    'en' => 'Discussion Topic',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title_mailfollow'] = array(
    'Big5' => '自動轉寄',
    'GB2312' => '自动转寄',
    'en' => 'Auto Forward',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title_yes'] = array(
    'Big5' => '是',
    'GB2312' => '是',
    'en' => 'Yes',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title_no'] = array(
    'Big5' => '否',
    'GB2312' => '否',
    'en' => 'No',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['with_attach'] = array(
    'Big5' => '附檔一併寄出',
    'GB2312' => '附件档一并寄出',
    'en' => 'With attachment(s)',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['vpost'] = array(
    'Big5' => '語音討論元件',
    'GB2312' => '语音讨论元件',
    'en' => 'voice component',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['whiteboard'] = array(
    'Big5' => '白板討論元件',
    'GB2312' => '白板讨论元件',
    'en' => 'white board',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title_sort'] = array(
    'Big5' => '預設排序的欄位',
    'GB2312' => '预设排序的栏位',
    'en' => 'Sort By',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['field_pt'] = array(
    'Big5' => '張貼時間',
    'GB2312' => '张贴时间',
    'en' => 'Posting Time',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['field_subject'] = array(
    'Big5' => '標題',
    'GB2312' => '标题',
    'en' => 'Subject',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['field_poster'] = array(
    'Big5' => '張貼者',
    'GB2312' => '张贴者',
    'en' => 'Author',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['field_rank'] = array(
    'Big5' => '星等/人數',
    'GB2312' => '星级/人数',
    'en' => 'Rating/Raters',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['field_hit'] = array(
    'Big5' => '點閱 ',
    'GB2312' => '点阅',
    'en' => 'Hits',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['chat_room_name'] = array(
    'Big5' => '討論室名稱',
    'GB2312' => '讨论室名称',
    'en' => 'Name of Chat Room',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['host_msg_exit'] = array(
    'Big5' => '討論室關閉後，將討論內容 ',
    'GB2312' => '讨论室关闭后，将讨论内容',
    'en' => 'Action options after chat room closed:',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['host_msg_allow_chg'] = array(
    'Big5' => '允許其他討論室中的人員轉換到這裡討論。',
    'GB2312' => '允许其他讨论室中的人员转换到这里讨论。',
    'en' => 'Allow members from other chat rooms to discuss here.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exit_act_none'] = array(
    'Big5' => '不保留',
    'GB2312' => '不保留',
    'en' => 'Delete messages',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exit_act_notebook'] = array(
    'Big5' => '轉貼到筆記本',
    'GB2312' => '转贴到笔记本',
    'en' => 'Forward messages to Notebook',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exit_act_forum'] = array(
    'Big5' => '張貼到討論板',
    'GB2312' => '张贴到讨论板',
    'en' => 'Forward messages to Discussion Forum',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['current_length'] = array(
    'Big5' => '目前長度:',
    'GB2312' => '目前长度:',
    'en' => 'Current length:',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['notice'] = array(
    'Big5' => '注意:',
    'GB2312' => '注意:',
    'en' => 'Note:',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['dont_exceed'] = array(
    'Big5' => '勿超過255字元!',
    'GB2312' => '勿超过255字符!',
    'en' => 'Do not exceed 255 characters!',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['student_info'] = array(
    'Big5' => '到課統計',
    'GB2312' => '到课统计',
    'en' => 'Attendance',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['select_role'] = array(
    'Big5' => '選擇身分：',
    'GB2312' => '选择身分：',
    'en' => 'Select Access:',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['serial'] = array(
    'Big5' => '序號',
    'GB2312' => '序号',
    'en' => 'Number',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['student'] = array(
    'Big5' => '正式生',
    'GB2312' => '正式生',
    'en' => 'Enrolled Student',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['auditor'] = array(
    'Big5' => '旁聽生',
    'GB2312' => '旁听生',
    'en' => 'Auditor',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['teacher'] = array(
    'Big5' => '教師',
    'GB2312' => '教师',
    'en' => 'Instructor',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['instructor'] = array(
    'Big5' => '講師',
    'GB2312' => '讲师',
    'en' => 'Guest Instructor',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['assistant'] = array(
    'Big5' => '助教',
    'GB2312' => '助教',
    'en' => 'TA',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['all'] = array(
    'Big5' => '全部',
    'GB2312' => '全部',
    'en' => 'All',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['account'] = array(
    'Big5' => '帳號',
    'GB2312' => '帐号',
    'en' => 'Username',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['login_times'] = array(
    'Big5' => '登入次數',
    'GB2312' => '登入次数',
    'en' => 'Number of Logins',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['last_login'] = array(
    'Big5' => '最近一次登入時間',
    'GB2312' => '最近一次登入时间',
    'en' => 'Last Login',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['class_times'] = array(
    'Big5' => '上課次數',
    'GB2312' => '上课次数',
    'en' => 'Course Attendance',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['last_class'] = array(
    'Big5' => '最近一次上課時間',
    'GB2312' => '最近一次上课时间',
    'en' => 'Last Entry',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['post_count'] = array(
    'Big5' => '張貼篇數',
    'GB2312' => '张贴篇数',
    'en' => 'Tally of Posts',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['chat_count'] = array(
    'Big5' => '討論次數',
    'GB2312' => '讨论次数',
    'en' => 'Discussion Participation',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['read_time'] = array(
    'Big5' => '閱讀時數',
    'GB2312' => '阅读时数',
    'en' => 'Total Study Time',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['read_page'] = array(
    'Big5' => '閱讀頁數',
    'GB2312' => '阅读页数',
    'en' => 'Pages Read',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['online_operate'] = array(
    'Big5' => '上站動作',
    'GB2312' => '上站动作',
    'en' => 'Action',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['mail_picked'] = array(
    'Big5' => '寄信給本頁勾選人員',
    'GB2312' => '寄信给本页勾选人员',
    'en' => 'Email the selected people',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['export_info'] = array(
    'Big5' => '匯出本頁資料',
    'GB2312' => '导出本页资料',
    'en' => 'Export data on this page',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['login_date'] = array(
    'Big5' => '日期',
    'GB2312' => '日期',
    'en' => 'Date',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['login_ip'] = array(
    'Big5' => '來源位址',
    'GB2312' => '来源位置',
    'en' => 'Source IP',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['login_action'] = array(
    'Big5' => '動作',
    'GB2312' => '动作',
    'en' => 'Action',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['no_login_data'] = array(
    'Big5' => '目前此學員無上站動作。 ',
    'GB2312' => '目前此学员无上站动作。',
    'en' => 'This student has no login data.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['mail_roll_call'] = array(
    'Big5' => '寄信與點名',
    'GB2312' => '寄信与点名',
    'en' => 'Email and Call the Roll',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['target_object'] = array(
    'Big5' => '對象',
    'GB2312' => '对象',
    'en' => 'Target',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['pick_target_role'] = array(
    'Big5' => '挑選學員身分',
    'GB2312' => '挑选学员身分',
    'en' => 'Select Role',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['target_group'] = array(
    'Big5' => '組次',
    'GB2312' => '组次',
    'en' => 'Group',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['pick_target_group'] = array(
    'Big5' => '挑選不同組次的學員',
    'GB2312' => '挑选不同组次的学员',
    'en' => 'Select students from different groups',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['filter_condition'] = array(
    'Big5' => '篩選條件',
    'GB2312' => '筛选条件',
    'en' => 'Filters',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['which1_1'] = array(
    'Big5' => '登入',
    'GB2312' => '登入',
    'en' => 'Login',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['which1_2'] = array(
    'Big5' => '上課',
    'GB2312' => '上课',
    'en' => 'Study',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['which1_3'] = array(
    'Big5' => '學習進度',
    'GB2312' => '学习进度',
    'en' => 'Learning Status',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['which1_4'] = array(
    'Big5' => '測驗',
    'GB2312' => '测验',
    'en' => 'Test',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['which1_5'] = array(
    'Big5' => '作業',
    'GB2312' => '作业',
    'en' => 'Assignment',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['which1_6'] = array(
    'Big5' => '問卷',
    'GB2312' => '问卷',
    'en' => 'Questionnaire',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['which1_7'] = array(
    'Big5' => '討論',
    'GB2312' => '讨论',
    'en' => 'Discuss',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['which1_8'] = array(
    'Big5' => '張貼',
    'GB2312' => '张贴',
    'en' => 'Post',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['call_hint2'] = array(
    'Big5' => '如果沒有勾選『篩選條件』<br />則列出所有對象',
    'GB2312' => '如果没有勾选‘筛选条件’<br />则列出所有对象',
    'en' => 'If Filters are not selected,<br />all members will be listed.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['start_pick'] = array(
    'Big5' => '開始挑選',
    'GB2312' => '开始挑选',
    'en' => 'Start',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['login_total'] = array(
    'Big5' => '登入總次數',
    'GB2312' => '登入总次数',
    'en' => 'Total Logins',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['login_not_days'] = array(
    'Big5' => '幾天未登入',
    'GB2312' => '几天未登入',
    'en' => 'Offline Days',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['login_last_login'] = array(
    'Big5' => '最後一次登入',
    'GB2312' => '最后一次登入',
    'en' => 'Last login',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['lesson_total'] = array(
    'Big5' => '上課總次數',
    'GB2312' => '上课总次数',
    'en' => 'Course Entries',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['lesson_not_days'] = array(
    'Big5' => '幾天未上課',
    'GB2312' => '几天未上课',
    'en' => 'Amount of Absence',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['lesson_last_login'] = array(
    'Big5' => '最後一次上課',
    'GB2312' => '最后一次上课',
    'en' => 'Last Entry',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['progress_total'] = array(
    'Big5' => '閱讀總時數',
    'GB2312' => '阅读总时数',
    'en' => 'Total Reading Time',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['progress_page'] = array(
    'Big5' => '閱讀頁數',
    'GB2312' => '阅读页数',
    'en' => 'Total Pages',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['chat_total'] = array(
    'Big5' => '討論總次數',
    'GB2312' => '讨论总次数',
    'en' => 'Total Discussions',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['post_total'] = array(
    'Big5' => '張貼總次數',
    'GB2312' => '张贴总次数',
    'en' => 'Total Posts',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['homework_not_do'] = array(
    'Big5' => '未做作業次數',
    'GB2312' => '未做作业次数',
    'en' => '# of undone assignments',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['homework_do'] = array(
    'Big5' => '已做作業次數',
    'GB2312' => '已做作业次数',
    'en' => '# of submissions',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['homework_some'] = array(
    'Big5' => '某次作業',
    'GB2312' => '某次作业',
    'en' => 'Certain',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_not_do'] = array(
    'Big5' => '未做測驗次數',
    'GB2312' => '未做测验次数',
    'en' => '# of undone tests',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_do'] = array(
    'Big5' => '已做測驗次數',
    'GB2312' => '已做测验次数',
    'en' => '# of tests done',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_some'] = array(
    'Big5' => '某次測驗',
    'GB2312' => '某次测验',
    'en' => 'Certain',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['questionnaire_not_do'] = array(
    'Big5' => '未做問卷次數',
    'GB2312' => '未做问卷次数',
    'en' => '# of undone questionnaires',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['questionnaire_do'] = array(
    'Big5' => '已做問卷次數',
    'GB2312' => '已做问卷次数',
    'en' => '# of questionnaires done',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['questionnaire_some'] = array(
    'Big5' => '某次問卷',
    'GB2312' => '某次问卷',
    'en' => 'Certain',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['do_finish'] = array(
    'Big5' => '已做',
    'GB2312' => '已做',
    'en' => 'Done',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['not_do'] = array(
    'Big5' => '未做',
    'GB2312' => '未做',
    'en' => 'Undone',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['tabs_filter_condition_result'] = array(
    'Big5' => '篩選結果',
    'GB2312' => '筛选结果',
    'en' => 'Result',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['rs_th_no'] = array(
    'Big5' => '序號',
    'GB2312' => '序号',
    'en' => 'No.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['rs_th_no_title'] = array(
    'Big5' => '序號',
    'GB2312' => '序号',
    'en' => 'No.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['rs_th_checkbox_title'] = array(
    'Big5' => '全部選取或全部取消',
    'GB2312' => '全部选取或全部取消',
    'en' => 'Select All or Cancel All',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['rs_th_name'] = array(
    'Big5' => '帳號',
    'GB2312' => '帐号',
    'en' => 'Username',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['rs_th_name_title'] = array(
    'Big5' => '帳號',
    'GB2312' => '帐号',
    'en' => 'Username',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['rs_th_last_login'] = array(
    'Big5' => '最近一次登入時間',
    'GB2312' => '最近一次登入时间',
    'en' => 'Last Login',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['rs_th_last_login_title'] = array(
    'Big5' => '最近一次登入時間',
    'GB2312' => '最近一次登入时间',
    'en' => 'Last Login',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['rs_th_last_study'] = array(
    'Big5' => '最近一次上課時間',
    'GB2312' => '最近一次上课时间',
    'en' => 'Last Entry',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['rs_th_last_study_title'] = array(
    'Big5' => '最近一次上課時間',
    'GB2312' => '最近一次上课时间',
    'en' => 'Last Entry',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['rs_th_login'] = array(
    'Big5' => '登入次數',
    'GB2312' => '登入次数',
    'en' => 'Logins',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['rs_th_login_title'] = array(
    'Big5' => '登入次數',
    'GB2312' => '登入次数',
    'en' => 'Logins',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['rs_th_study'] = array(
    'Big5' => '上課次數',
    'GB2312' => '上课次数',
    'en' => 'Attendance',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['rs_th_study_title'] = array(
    'Big5' => '上課次數',
    'GB2312' => '上课次数',
    'en' => 'Attendance',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['rs_th_post'] = array(
    'Big5' => '張貼篇數',
    'GB2312' => '张贴篇数',
    'en' => 'Tally of Posts',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['rs_th_post_title'] = array(
    'Big5' => '張貼篇數',
    'GB2312' => '张贴篇数',
    'en' => '# of posts',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['rs_th_chat'] = array(
    'Big5' => '討論次數',
    'GB2312' => '讨论次数',
    'en' => 'Discussion Participation',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['rs_th_chat_title'] = array(
    'Big5' => '討論次數',
    'GB2312' => '讨论次数',
    'en' => 'Discussion Participation',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['rs_need_data'] = array(
    'Big5' => '請輸入資料',
    'GB2312' => '请输入资料',
    'en' => 'Please enter data.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['rs_format_error'] = array(
    'Big5' => '輸入的格式錯誤，只能輸入數字',
    'GB2312' => '输入的格式错误，只能输入数字',
    'en' => 'Format error! Only numbers are allowed.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['rs_no_result'] = array(
    'Big5' => '無符合條件的相關人員',
    'GB2312' => '无符合条件的相关人员',
    'en' => 'No match found.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['rs_btn_send_mail'] = array(
    'Big5' => '寄送通知信',
    'GB2312' => '寄送通知信',
    'en' => 'Email Notice',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['days'] = array(
    'Big5' => '%1$d 天又 ',
    'GB2312' => '%1$d 天又',
    'en' => '%1$d days, ',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['time_str'] = array(
    'Big5' => '%2$02d 小時 %3$02d 分 %4$02d 秒',
    'GB2312' => '%2$02d 小时 %3$02d 分 %4$02d 秒',
    'en' => '%2$02d:%3$02d:%4$02d',
    'EUC-JP' => '%2$02d:%3$02d:%4$02d',
    'user_define' => '%2$02d:%3$02d:%4$02d'
);

$MSG['mailto_mail'] = array(
    'Big5' => '寄送通知信',
    'GB2312' => '寄送通知信',
    'en' => 'Send Mail',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['tabs_mail_send'] = array(
    'Big5' => '寄送結果',
    'GB2312' => '寄送结果',
    'en' => 'Sent Results',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['btn_return_mailto'] = array(
    'Big5' => '回寄信與點名',
    'GB2312' => '回寄信与点名',
    'en' => 'Back to Email and Call the Roll',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['ex_username'] = array(
    'Big5' => '帳號',
    'GB2312' => '帐号',
    'en' => 'Username',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['ex_first_name'] = array(
    'Big5' => '名',
    'GB2312' => '名',
    'en' => 'First Name',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['ex_last_name'] = array(
    'Big5' => '姓',
    'GB2312' => '姓',
    'en' => 'Last Name',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['ex_gender'] = array(
    'Big5' => '性別',
    'GB2312' => '性别',
    'en' => 'Gender',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['ex_birthday'] = array(
    'Big5' => '生日',
    'GB2312' => '生日',
    'en' => 'Birthday',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['ex_personal_id'] = array(
    'Big5' => '身分證號',
    'GB2312' => '身分证号',
    'en' => 'ID No.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['ex_email'] = array(
    'Big5' => '電子郵件',
    'GB2312' => '电子邮件',
    'en' => 'Email',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['ex_homepage'] = array(
    'Big5' => '個人網頁',
    'GB2312' => '个人网页',
    'en' => 'Homepage',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['ex_home_tel'] = array(
    'Big5' => '電話(家)',
    'GB2312' => '电话(家)',
    'en' => 'Telephone(H)',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['ex_home_fax'] = array(
    'Big5' => '傳真機(家)',
    'GB2312' => '传真机(家)',
    'en' => 'Fax(H)',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['ex_home_address'] = array(
    'Big5' => '地址(家)',
    'GB2312' => '地址(家)',
    'en' => 'Address(H)',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['ex_office_tel'] = array(
    'Big5' => '電話(公司)',
    'GB2312' => '电话(公司)',
    'en' => 'Telephone(O)',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['ex_office_fax'] = array(
    'Big5' => '傳真機(公司)',
    'GB2312' => '传真机(公司)',
    'en' => 'Fax(O)',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['ex_office_address'] = array(
    'Big5' => '地址(公司)',
    'GB2312' => '地址(公司)',
    'en' => 'Address(O)',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['ex_cell_phone'] = array(
    'Big5' => '手機號碼',
    'GB2312' => '手机号码',
    'en' => 'Mobile',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['ex_company'] = array(
    'Big5' => '公司',
    'GB2312' => '公司',
    'en' => 'Company',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['ex_department'] = array(
    'Big5' => '部門',
    'GB2312' => '部门',
    'en' => 'Department',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['ex_title'] = array(
    'Big5' => '職稱',
    'GB2312' => '职称',
    'en' => 'Job Title',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['ex_language'] = array(
    'Big5' => '使用語系',
    'GB2312' => '使用语系',
    'en' => 'Language',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['ex_theme'] = array(
    'Big5' => '使用布景',
    'GB2312' => '使用布景',
    'en' => 'Theme',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['student_export'] = array(
    'Big5' => '匯出學員資料',
    'GB2312' => '导出学员资料',
    'en' => 'Export student profile',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['select_fields'] = array(
    'Big5' => '選擇所要匯出的欄位',
    'GB2312' => '选择所要导出的栏位',
    'en' => 'Select fields to export',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['target_email'] = array(
    'Big5' => '輸入所要寄達的 E-mail',
    'GB2312' => '输入所要寄达的 E-mail',
    'en' => 'Enter destination Email address',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['student_role'] = array(
    'Big5' => '選擇匯出學員身分',
    'GB2312' => '选择导出学员身分',
    'en' => 'Select student role to export',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['export_type'] = array(
    'Big5' => '選擇匯出格式',
    'GB2312' => '选择导出格式',
    'en' => 'Select export format',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['sure_export'] = array(
    'Big5' => '匯出',
    'GB2312' => '导出',
    'en' => 'Export',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['error_msg'] = array(
    'Big5' => '請至少勾選一個匯出的欄位',
    'GB2312' => '请至少勾选一个导出的栏位',
    'en' => 'Please select at least one field to export',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['email_msg'] = array(
    'Big5' => '請在此輸入 email 地址。若要寄給多人，請用半形的逗點 , 分號 ; 或空白將email分開',
    'GB2312' => '请在此输入 email 地址。若要寄给多人，请用半形的逗点 , 分号 ; 或空白将email分开',
    'en' => 'Please enter email addresses here. To email more than one person, please put commas, colons, or space between addresses.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['role_title'] = array(
    'Big5' => '教師',
    'GB2312' => '教师',
    'en' => 'Instructor',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['role_title2'] = array(
    'Big5' => '講師',
    'GB2312' => '讲师',
    'en' => 'Guest Instructor',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['role_title3'] = array(
    'Big5' => '助教',
    'GB2312' => '助教',
    'en' => 'TA',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['role_title6'] = array(
    'Big5' => '正式生',
    'GB2312' => '正式生',
    'en' => 'Enrolled Student',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['role_title7'] = array(
    'Big5' => '旁聽生',
    'GB2312' => '旁听生',
    'en' => 'Auditor',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['file_type1'] = array(
    'Big5' => '.xml 檔 ',
    'GB2312' => '.xml 档',
    'en' => '.xml files',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['file_type2'] = array(
    'Big5' => '.html 檔',
    'GB2312' => '.html 档',
    'en' => '.html files',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['realname'] = array(
    'Big5' => '姓名 ',
    'GB2312' => '姓名',
    'en' => 'First Name',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['return_student_export'] = array(
    'Big5' => '回匯出學員資料',
    'GB2312' => '回导出学员资料',
    'en' => 'Export Student Profile',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['receiver'] = array(
    'Big5' => '收件者 ',
    'GB2312' => '收件者',
    'en' => 'Receiver',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['send_state'] = array(
    'Big5' => '已發送',
    'GB2312' => '已发送',
    'en' => 'Sent',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['input_email'] = array(
    'Big5' => '請輸入 email',
    'GB2312' => '请输入 email',
    'en' => 'Please enter email',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['course_name'] = array(
    'Big5' => '課程名稱：',
    'GB2312' => '课程名称：',
    'en' => 'Course Title:',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['detail_content'] = array(
    'Big5' => '詳細人員資料在本信件的夾檔中',
    'GB2312' => '详细人员资料在本信件的附件中',
    'en' => 'You can find detailed personal information in the attachment.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['no_data'] = array(
    'Big5' => '依照您所選擇的條件，沒有找到任何人員資料，請重新選擇。',
    'GB2312' => '按照您所选择的条件，没有找到任何人员资料，请重新选择。',
    'en' => 'No match found. Please try again.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['error_msg1'] = array(
    'Big5' => '請勾選欲匯出的身分,至少勾選一個。',
    'GB2312' => '请勾选欲导出的身分,至少勾选一个。',
    'en' => 'Please select at least a role to export.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['error_msg2'] = array(
    'Big5' => '請勾選欲匯出的資料格式,至少勾選一個',
    'GB2312' => '请勾选欲导出的资料格式,至少勾选一个',
    'en' => 'Please select at least an export format.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['no_self1'] = array(
    'Big5' => '請到 [',
    'GB2312' => '请到 [',
    'en' => 'Read sent mails in [',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['no_self2'] = array(
    'Big5' => '] 中讀取發送的備份信件，系統不再發送一份給自己',
    'GB2312' => '] 中读取发送的备份信件，系统不再发送一份给自己',
    'en' => ']. System will not send a copy to user.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['confirm_delete'] = array(
    'Big5' => '您確定要刪除嗎',
    'GB2312' => '您确定要删除吗',
    'en' => 'Are you sure you want to delete?',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['input_username'] = array(
    'Big5' => '請輸入帳號。 ',
    'GB2312' => '请输入帐号。',
    'en' => 'Please enter username.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['reserve_used'] = array(
    'Big5' => '保留的帳號，並且使用中。',
    'GB2312' => '保留的帐号，并且使用中。',
    'en' => 'This is a reserved account, and it is in use.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['reserve'] = array(
    'Big5' => '保留的帳號，而不能使用。',
    'GB2312' => '保留的帐号，而不能使用。',
    'en' => 'This is a reserved account. It cannot be used.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['system_reserved'] = array(
    'Big5' => '為系統保留帳號,不允許新增或刪除。',
    'GB2312' => '为系统保留帐号,不允许新增或删除。',
    'en' => 'It is system reserved account, you cannot create or remove it.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['export_stud'] = array(
    'Big5' => '您沒有選擇個別學生，是否要全部匯出？',
    'GB2312' => '您没有选择个别学生，是否要全部导出？',
    'en' => 'You didn&#039;t select individual students. Do you want to export all?',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['select_mem'] = array(
    'Big5' => '請先勾選人員',
    'GB2312' => '请先勾选人员',
    'en' => 'Please select people first.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['select_all'] = array(
    'Big5' => '全選',
    'GB2312' => '全选',
    'en' => 'Select All',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['select_cancel'] = array(
    'Big5' => '全消',
    'GB2312' => '全消',
    'en' => 'Cancel Select',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['save_msg'] = array(
    'Big5' => '資料儲存完畢 ',
    'GB2312' => '资料保存完毕',
    'en' => 'Saved successfully!',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['export_role'] = array(
    'Big5' => '匯出的身分： ',
    'GB2312' => '导出的身分：',
    'en' => 'Role Exported:',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['alert_msg'] = array(
    'Big5' => '注意！您尚未存檔！\\n按 [確定] 不存檔直接離開\\n按 [取消] 回編輯畫面',
    'GB2312' => '注意！您尚未存档！\\n按 [确定] 不存档直接离开\\n按 [取消] 回编辑画面',
    'en' => 'You haven\\\'t saved yet. \\n Click OK to exit without saving.\\n Click Cancel to return.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['illege_access'] = array(
    'Big5' => '非法存取',
    'GB2312' => '非法存取',
    'en' => 'Access denied!',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['please_input_Big5'] = array(
    'Big5' => '請輸入分組次名稱-正體中文(Big5)',
    'GB2312' => '请输入分组次名称-繁体中文(Big5)',
    'en' => 'Please enter group name-Traditional Chinese',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['please_input_en'] = array(
    'Big5' => '請輸入分組次名稱-英文(en)',
    'GB2312' => '请输入分组次名称-英文(en)',
    'en' => 'Please enter group name-English',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['please_input_GB2312'] = array(
    'Big5' => '請輸入分組次名稱-簡體中文(GB2312)',
    'GB2312' => '请输入分组次名称-简体中文(GB2312)',
    'en' => 'Please enter group name-Simplified Chinese',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['please_input_EUC_JP'] = array(
    'Big5' => '請輸入分組次名稱-日文(EUC-JP)',
    'GB2312' => '请输入分组次名称-日文(EUC-JP)',
    'en' => 'Please enter group name-Japanese',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['please_input_user_define'] = array(
    'Big5' => '請輸入分組次名稱-自定(user_define)',
    'GB2312' => '请输入分组次名称-自定(user_define)',
    'en' => 'Please enter group name-User Define',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['last_10'] = array(
    'Big5' => ' - 最後 10 次操作紀錄',
    'GB2312' => '- 最后 10 次操作纪录',
    'en' => 'Last 10 operations',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['learn_10'] = array(
    'Big5' => ' - 最常閱讀的 10 個頁面',
    'GB2312' => '- 最常阅读的 10 个页面',
    'en' => '10 most frequently visited pages',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['login_time'] = array(
    'Big5' => '日期 / 時間',
    'GB2312' => '日期 / 时间',
    'en' => 'Date/Time',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['login_host'] = array(
    'Big5' => '來源位址',
    'GB2312' => '来源位置',
    'en' => 'Source IP',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['subject'] = array(
    'Big5' => '文章標題',
    'GB2312' => '文章标题',
    'en' => 'Subject',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['board_name'] = array(
    'Big5' => '討論板名稱',
    'GB2312' => '讨论板名称',
    'en' => 'Name of Discussion Forum',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['learn_page'] = array(
    'Big5' => '最常閱讀的網頁',
    'GB2312' => '最常阅读的网页',
    'en' => 'Most frequently visited page',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['learn_time'] = array(
    'Big5' => '閱讀時數',
    'GB2312' => '阅读时数',
    'en' => 'Total Study Time',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['learn_times'] = array(
    'Big5' => '閱讀次數',
    'GB2312' => '阅读次数',
    'en' => 'Reading Times',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['msg_page_add'] = array(
    'Big5' => '選取帳號',
    'GB2312' => '选取帐号',
    'en' => 'Account In Existence',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['msg_title02'] = array(
    'Big5' => '：',
    'GB2312' => '：',
    'en' => ': ',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['msg_title03'] = array(
    'Big5' => '請選擇',
    'GB2312' => '请选择',
    'en' => 'choice ',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['msg_title05'] = array(
    'Big5' => '請至少勾選其一',
    'GB2312' => '请至少勾选其一',
    'en' => 'Please select ',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title77'] = array(
    'Big5' => '全選 ',
    'GB2312' => '全选',
    'en' => 'Select All',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title78'] = array(
    'Big5' => '全消 ',
    'GB2312' => '全消',
    'en' => 'Cancel Select',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['username'] = array(
    'Big5' => '帳號 ',
    'GB2312' => '帐号',
    'en' => 'username',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title79'] = array(
    'Big5' => '身分',
    'GB2312' => '身分',
    'en' => 'Role',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title80'] = array(
    'Big5' => '關鍵字',
    'GB2312' => '关键字',
    'en' => 'Keywords',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title82'] = array(
    'Big5' => '查詢',
    'GB2312' => '查询',
    'en' => 'Ok',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title83'] = array(
    'Big5' => '對象',
    'GB2312' => '对象',
    'en' => 'Target',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title84'] = array(
    'Big5' => '本課學員',
    'GB2312' => '本课学员',
    'en' => 'students in this course',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title85'] = array(
    'Big5' => '所有帳號',
    'GB2312' => '所有帐号',
    'en' => 'all account',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['msg_last_updated_time'] = array(
    'Big5' => '本排行榜最近一次統計的時間為：',
    'GB2312' => '本排行榜最近一次统计的时间为：',
    'en' => 'The last updated time of the rank is :',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['msg_cron_daily_fail'] = array(
    'Big5' => '<font color="red">本系統尚未啟動每日更新的機制</font>',
    'GB2312' => '<font color="red">本系统尚未启动每日更新的机制</font>',
    'en' => '<font color="red">The daily update mechanism have not activated</font>',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['mail_roll_call_manual'] = array(
    'Big5' => '立即點名',
    'GB2312' => '立即点名',
    'en' => 'Email and Call the Roll',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['mail_roll_call_system'] = array(
    'Big5' => '自動點名設定',
    'GB2312' => '自动点名设定',
    'en' => 'Auto-Email and Call the Roll',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['mail_roll_call_system_readme'] = array(
    'Big5' => '自動化點名寄信機制，將依據您所設定的條件，在固定的時間自動寄信提醒您的學員來上課、繳交作業、參與考試...等。',
    'GB2312' => '自动化点名寄信机制，将依据您所设定的条件，在固定的时间自动寄信提醒您的学员来上课、缴交作业、参与考试...等。',
    'en' => 'Automatic notification by email will remind your students to go to class, deliver homework and take exam,  in a period of time and automatically, according to conditions you setup. ',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['mail_roll_call_system_readme2'] = array(
    'Big5' => '您可在此設定點名條件以及點名的時間、頻率。系統將會自動依據您所設定的條件進行點名，並寄送通知信給被點到名的學員。',
    'GB2312' => '您可在此设定点名条件以及点名的时间、频率。系统将会自动依据您所设定的条件进行点名，并寄送通知信给被点到名的学员。',
    'en' => 'You can setup conditions for rollcall like time, frequency. System will take rollcall according to the condition you setup and send notification email to those students need to be rollcalled. ',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['roll_call_enable'] = array(
    'Big5' => '啟用',
    'GB2312' => '启用',
    'en' => 'Enable',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['roll_call_disable'] = array(
    'Big5' => '停用',
    'GB2312' => '停用',
    'en' => 'Disable',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['roll_call_roles'] = array(
    'Big5' => '點名對象',
    'GB2312' => '点名对象',
    'en' => 'Target',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['roll_call_group'] = array(
    'Big5' => '分組次',
    'GB2312' => '分组次',
    'en' => 'Grouping',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['roll_call_cond'] = array(
    'Big5' => '點名條件',
    'GB2312' => '点名条件',
    'en' => 'Condition for Rollcall',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['roll_call_duration'] = array(
    'Big5' => '點名期間',
    'GB2312' => '点名期间',
    'en' => 'Period of Rollcall',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['roll_call_freq'] = array(
    'Big5' => '頻率',
    'GB2312' => '频率',
    'en' => 'Frequency',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['roll_call_modify'] = array(
    'Big5' => '修改',
    'GB2312' => '修改',
    'en' => 'Modify',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['roll_call_freq_once'] = array(
    'Big5' => '單次',
    'GB2312' => '单次',
    'en' => 'One time',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['roll_call_freq_day'] = array(
    'Big5' => '每天',
    'GB2312' => '每天',
    'en' => 'Once a day',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['roll_call_freq_week'] = array(
    'Big5' => '每週',
    'GB2312' => '每周',
    'en' => 'Once a week',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['roll_call_freq_month'] = array(
    'Big5' => '每月',
    'GB2312' => '每月',
    'en' => 'Once a month',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['roll_call_add'] = array(
    'Big5' => '新增',
    'GB2312' => '新增',
    'en' => 'Add',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['roll_call_rm'] = array(
    'Big5' => '刪除',
    'GB2312' => '删除',
    'en' => 'Delete',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['roll_call_select_empty'] = array(
    'Big5' => '尚未選取任何點名條件！',
    'GB2312' => '尚未选取任何点名条件！',
    'en' => 'You do not choose any condition of rollcall.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['roll_call_confirm_del'] = array(
    'Big5' => '您確定要刪除嗎？',
    'GB2312' => '您确定要删除吗？',
    'en' => 'Are you sure you want to delete?',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['roll_call_set_success'] = array(
    'Big5' => '成功！',
    'GB2312' => '成功！',
    'en' => 'Succeeded!',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['roll_call_set_fail'] = array(
    'Big5' => '失敗！',
    'GB2312' => '失败！',
    'en' => 'Failed!',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['roll_call_rull_add'] = array(
    'Big5' => '新增點名規則',
    'GB2312' => '新增点名规则',
    'en' => 'Add new rule for rollcall.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['roll_call_rull_modify'] = array(
    'Big5' => '修改點名規則',
    'GB2312' => '修改点名规则',
    'en' => 'Modify rule for rollcall.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['access_deny'] = array(
    'Big5' => '拒絕存取',
    'GB2312' => '拒绝存取',
    'en' => 'Access Deny',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['btn_save'] = array(
    'Big5' => '確定',
    'GB2312' => '确定',
    'en' => 'OK',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['btn_return'] = array(
    'Big5' => '取消',
    'GB2312' => '取消',
    'en' => 'Cancel',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['roll_call_mail_subject'] = array(
    'Big5' => '通知信標題',
    'GB2312' => '通知信标题',
    'en' => 'Notification Subject',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['roll_call_mail_cc'] = array(
    'Big5' => '副本收件者',
    'GB2312' => '副本收件者',
    'en' => 'CC',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['roll_call_mail_content'] = array(
    'Big5' => '內容',
    'GB2312' => '内容',
    'en' => 'Content',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['roll_call_mail_attach_list'] = array(
    'Big5' => '附件列表',
    'GB2312' => '附件列表',
    'en' => 'File attachment list.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['roll_call_mail_attach_upload'] = array(
    'Big5' => '附件上傳',
    'GB2312' => '附件上传',
    'en' => 'Upload attachment.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['roll_call_help1'] = array(
    'Big5' => '是否啟用這項自動點名機制',
    'GB2312' => '是否启用这项自动点名机制',
    'en' => 'Enable or disable automatic rollcall function.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['roll_call_help2'] = array(
    'Big5' => '多久點一次名',
    'GB2312' => '多久点一次名',
    'en' => 'How long is it to rollcall?',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['roll_call_help3'] = array(
    'Big5' => '在哪個期間執行自動點名',
    'GB2312' => '在哪个期间执行自动点名',
    'en' => 'Period of time to rollcall',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['roll_call_help4'] = array(
    'Big5' => '信件標題。（寄信給被點到名的人）',
    'GB2312' => '信件标题。（寄信给被点到名的人）',
    'en' => 'Subject',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['roll_call_help5'] = array(
    'Big5' => '每個檔案限%MIN_SIZE%，總和不得超過%MAX_SIZE%',
    'GB2312' => '每个档案限%MIN_SIZE%，总合不得超过%MAX_SIZE%',
    'en' => 'Each file cannot exceed %MIN_SIZE% , No more than %MAX_SIZE% in total.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['from'] = array(
    'Big5' => '從',
    'GB2312' => '從',
    'en' => 'from:',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['to'] = array(
    'Big5' => '至',
    'GB2312' => '至',
    'en' => 'to:',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['now'] = array(
    'Big5' => '即日起',
    'GB2312' => '即日起',
    'en' => 'Today',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['forever'] = array(
    'Big5' => '無限期',
    'GB2312' => '无限期',
    'en' => 'Forever',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['once'] = array(
    'Big5' => '一次',
    'GB2312' => '一次',
    'en' => 'Once',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['Monday'] = array(
    'Big5' => '週一',
    'GB2312' => '週一',
    'en' => 'Monday',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['Tuesday'] = array(
    'Big5' => '週二',
    'GB2312' => '周二',
    'en' => 'Tuesday',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['Wednesday'] = array(
    'Big5' => '週三',
    'GB2312' => '周三',
    'en' => 'Wednesday',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['Thursday'] = array(
    'Big5' => '週四',
    'GB2312' => '周四',
    'en' => 'Thursday',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['Friday'] = array(
    'Big5' => '週五',
    'GB2312' => '周五',
    'en' => 'Friday',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['Saturday'] = array(
    'Big5' => '週六',
    'GB2312' => '周六',
    'en' => 'Saturday',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['Sunday'] = array(
    'Big5' => '週日',
    'GB2312' => '周日',
    'en' => 'Sunday',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['roll_call_mail_subject_default'] = array(
    'Big5' => '%COURSE_NAME%點名通知',
    'GB2312' => '%COURSE_NAME%点名通知',
    'en' => 'Rollcall infomation from %COURSE_NAME%.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['roll_call_mail_subject_default1'] = array(
    'Big5' => '%COURSE_NAME%上課通知',
    'GB2312' => '%COURSE_NAME%上课通知',
    'en' => 'Class infomation from %COURSE_NAME%',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['roll_call_mail_content_default1'] = array(
    'Big5' => '%username%(%realname%)：<br>	你已經超過 7 日沒有到%COURSE_NAME% 來上課，<br>請把握學習機會，趕快上來看看課堂上有什麼新消息！',
    'GB2312' => '%username%(%realname%)：<br>	你已经超过 7 日没有到%COURSE_NAME% 来上课，<br>请把握学习机会，赶快上来看看课堂上有什么新消息！',
    'en' => 'Hi %username%(%realname%), <br>    You have not attend %COURSE_NAME% for seven days.<br>Please come here and see what happening in your class!',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['roll_call_mail_subject_default2'] = array(
    'Big5' => '%COURSE_NAME%測驗通知',
    'GB2312' => '%COURSE_NAME%测验通知',
    'en' => 'Exam information from %COURSE_NAME%.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['roll_call_mail_content_default2'] = array(
    'Big5' => '%username%(%realname%)：<br>你在%COURSE_NAME%中有 1個以上的測驗卷尚未完成，<br>請儘速在測驗結束日之前把握學習應考，以免影響考核成績！',
    'GB2312' => '%username%(%realname%)：<br>你在%COURSE_NAME%中有 1个以上的测验卷尚未完成，<br>请尽速在测验结束日之前把握学习应考，以免影响考核成绩！',
    'en' => 'Hi %username%(%realname%):<br> You still have one or more exam to take in class %COURSE_NAME%.<br>Please take exam as soon as the due has came in order not to affect your grade. ',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['roll_call_mail_subject_default3'] = array(
    'Big5' => '%COURSE_NAME%報告繳交通知',
    'GB2312' => '%COURSE_NAME%报告缴交通知',
    'en' => 'Homework information from %COURSE_NAME%.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['roll_call_mail_content_default3'] = array(
    'Big5' => '%username%(%realname%)：<br>你在%COURSE_NAME%中有 1份以上的報告尚未繳交，<br>請儘速在繳交結束日之前把握學習應考，以免影響考核成績！',
    'GB2312' => '%username%(%realname%)：<br>你在%COURSE_NAME%中有 1份以上的报告尚未缴交，<br>请尽速在缴交结束日之前把握学习应考，以免影响考核成绩！',
    'en' => 'Hi %username%(%realname%):<br> You still have one or more homework to hand in class %COURSE_NAME%.<br>Please take exam as soon as the due has came in order not to affect your grade. ',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['roll_call_mail_subject_default4'] = array(
    'Big5' => '%COURSE_NAME%問卷繳交通知',
    'GB2312' => '%COURSE_NAME%问卷缴交通知',
    'en' => 'Questionnaire infomation from %COURSE_NAME%.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['roll_call_mail_content_default4'] = array(
    'Big5' => '%username%(%realname%)：<br>你在%COURSE_NAME%中有 1份以上的問卷尚未繳交，<br>請儘速在繳交結束日之前把握學習應考，以免影響考核成績！',
    'GB2312' => '%username%(%realname%)：<br>你在%COURSE_NAME%中有 1份以上的问卷尚未缴交，<br>请尽速在缴交结束日之前把握学习应考，以免影响考核成绩！',
    'en' => 'Hi %username%(%realname%):<br> You still have one or more questionnaire to take in class %COURSE_NAME%.<br>Please take exam as soon as the due has came in order not to affect your grade. ',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['roll_call_mail_cc_direct'] = array(
    'Big5' => '學員的導師',
    'GB2312' => '学员的导师',
    'en' => 'Superviser of this student.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['equal'] = array(
    'Big5' => ' = ',
    'GB2312' => ' = ',
    'en' => '=',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['greater'] = array(
    'Big5' => ' > ',
    'GB2312' => ' > ',
    'en' => '>',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['smaller'] = array(
    'Big5' => ' < ',
    'GB2312' => ' < ',
    'en' => '<',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['greater_equal'] = array(
    'Big5' => ' >= ',
    'GB2312' => ' >= ',
    'en' => '>=',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['smaller_equal'] = array(
    'Big5' => ' <= ',
    'GB2312' => ' <= ',
    'en' => '<=',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['differ'] = array(
    'Big5' => ' != ',
    'GB2312' => ' != ',
    'en' => '!=',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['login_off'] = array(
    'Big5' => '未登入天數',
    'GB2312' => '未登入天数',
    'en' => 'Days of not login in.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['lesson_off'] = array(
    'Big5' => '未上課天數',
    'GB2312' => '未上课天数',
    'en' => 'Days of not attend to class.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['homework_do_yes'] = array(
    'Big5' => '已做作業：',
    'GB2312' => '已做作业：',
    'en' => 'Homework handed in.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['homework_do_no'] = array(
    'Big5' => '未做作業：',
    'GB2312' => '未做作业：',
    'en' => 'Homework not handed in.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_do_yes'] = array(
    'Big5' => '已做測驗：',
    'GB2312' => '已做测验：',
    'en' => 'Exam taken.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['exam_do_no'] = array(
    'Big5' => '未做測驗：',
    'GB2312' => '未做测验：',
    'en' => 'Exam to take.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['questionnaire_do_yes'] = array(
    'Big5' => '已做問卷：',
    'GB2312' => '已做问卷：',
    'en' => 'Questionnaire taken.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['questionnaire_do_no'] = array(
    'Big5' => '未做問卷：',
    'GB2312' => '未做问卷：',
    'en' => 'Questionnaire to take.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['msg_val1'] = array(
    'Big5' => '次',
    'GB2312' => '次',
    'en' => ' times',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['msg_val2'] = array(
    'Big5' => '小時',
    'GB2312' => '小时',
    'en' => ' hours',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['msg_val3'] = array(
    'Big5' => '天',
    'GB2312' => '天',
    'en' => ' days',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['more_attachments'] = array(
    'Big5' => '更多附檔',
    'GB2312' => '更多附件档',
    'en' => 'More attachments',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['less_attachments'] = array(
    'Big5' => '縮減附檔',
    'GB2312' => '缩减附件档',
    'en' => 'Less attachments',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['roll_call_not_modify'] = array(
    'Big5' => '資料未更動',
    'GB2312' => '资料未更动',
    'en' => '',
    'EUC-JP' => '',
    'user_define' => ''
);
$MSG['roll_minute']          = array(
    'Big5' => ' 分',
    'GB2312' => ' 分',
    'en' => '',
    'EUC-JP' => '',
    'user_define' => ''
);
$MSG['no_roll_data']         = array(
    'Big5' => '目前無任何點名設定。請按『新增』來設定自動點名的條件。',
    'GB2312' => '目前无任何点名设定。请按‘新增’来设定自动点名的条件。',
    'en' => '',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['disc_group'] = array(
    'Big5' => '小組會議',
    'GB2312' => '小组会议',
    'en' => 'Group Meeting',
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

$MSG['btn_meet_list'] = array(
    'Big5' => '列表',
    'GB2312' => '列表',
    'en' => 'Recording List',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['btn_enter'] = array(
    'Big5' => '進入',
    'GB2312' => '进入',
    'en' => 'Enter',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['msg_date_error'] = array(
    'Big5' => '結束日期必須大於開始日期，請重新設定。',
    'GB2312' => '结束日期必须大于开始日期，请重新设定。',
    'en' => 'Close date must be latter than start date. Please reset the date.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['download_all'] = array(
    'Big5' => '下載全部',
    'GB2312' => '下载全部',
    'en' => 'Download all',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['msg_no_begin_date'] = array(
    'Big5' => '請設定點名開始日期',
    'GB2312' => '',
    'en' => '',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['msg_no_end_date'] = array(
    'Big5' => '請設定點名結束日期',
    'GB2312' => '',
    'en' => '',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['go'] = array(
    'Big5' => '進入',
    'GB2312' => '进入',
    'en' => 'Enter',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['please_select_member'] = array(
    'Big5' => '請先選擇收件者',
    'GB2312' => '请先选择收件者',
    'en' => 'Please select member first',
    'EUC-JP' => '',
    'user_define' => ''
);
	$MSG['item_remark'] = array(
		'Big5'			=> '※將滑鼠移至項目名稱上可顯示統計項目說明※',
		'GB2312'		=> '※将滑鼠移至项目名称上可显示统计项目说明※',
		'en'			=> '',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['item_logins_remark'] = array(
		'Big5'			=> '統計進入平台的次數',
		'GB2312'		=> '统计进入平台的次数',
		'en'			=> '',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['item_course_remark'] = array(
		'Big5'			=> '統計進入本課程教室的次數',
		'GB2312'		=> '统计进入本课程教室的次数',
		'en'			=> '',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['item_post_remark'] = array(
		'Big5'			=> '統計本課程所有討論板張貼的文章數',
		'GB2312'		=> '统计本课程所有讨论板张贴的文章数',
		'en'			=> '',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['item_chat_remark'] = array(
		'Big5'			=> '統計進入討論室次數(必須有發言過)',
		'GB2312'		=> '统计进入讨论室次数(必须有发言过)',
		'en'			=> '',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['item_reading_time_remark'] = array(
		'Big5'			=> '統計教材節點的閱讀總時間',
		'GB2312'		=> '统计教材节点的阅读总时间',
		'en'			=> '',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['item_reading_pages_remark'] = array(
		'Big5'			=> '統計教材節點的閱讀總數',
		'GB2312'		=> '统计教材节点的阅读总数',
		'en'			=> '',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);
	
	$MSG['department'] = array(
		'Big5'			=> '部門',
		'GB2312'		=> '部门',
		'en'			=> 'Department',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);
	
	$MSG['title'] = array(
		'Big5'			=> '職稱',
		'GB2312'		=> '职称',
		'en'			=> 'Title',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);
	

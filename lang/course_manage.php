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

	$MSG['msg_waiting'] = array(
		'Big5'			=> '程式載入中，請稍後...',
		'GB2312'		=> '程序载入中，请稍后...',
		'en'			=> 'Loading, please wait...',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['msg_system_error'] = array(
		'Big5'			=> '程式執行時發生錯誤！',
		'GB2312'		=> '程序执行时发生错误！',
		'en'			=> 'System error!',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['title_manage'] = array(
		'Big5'			=> '課程管理',
		'GB2312'		=> '课程管理',
		'en'			=> 'Course Management',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['tabs_course_list'] = array(
		'Big5'			=> '課程列表',
		'GB2312'		=> '课程列表',
		'en'			=> 'Course List',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['tabs_course_detail'] = array(
		'Big5'			=> '課程詳細資料',
		'GB2312'		=> '课程详细资料',
		'en'			=> 'Course Details',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['cs_state_close'] = array(
		'Big5'			=> '關閉',
		'GB2312'		=> '关闭',
		'en'			=> 'Colse',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['cs_state_open_a'] = array(
		'Big5'			=> '開課（可旁聽與報名，且不受上課起訖日期限制）',
		'GB2312'		=> '开课（可旁听与报名，且不受上课起讫日期限制）',
		'en'			=> 'Opening(Auditing allowed and enrolled. Course is accessible between and beyond start date and end date.)',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['cs_state_open_a_date'] = array(
		'Big5'			=> '開課（可旁聽與報名，並於上課開始前或結束後關閉）',
		'GB2312'		=> '开课（可旁听与报名，并于上课开始前或结束后关闭）',
		'en'			=> 'Opening(Auditing allowed and enrolled. Course is not accessible before the course start date and after the end date.)',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['cs_state_open_n'] = array(
		'Big5'			=> '開課（不可旁聽與報名，且不受上課起訖日期限制）',
		'GB2312'		=> '开课（不可旁听与报名，且不受上课起讫日期限制）',
		'en'			=> 'Opening(Auditing and enrolled not allowed. Course is accessible between and beyond start date and end date.)',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['cs_state_open_n_date'] = array(
		'Big5'			=> '開課（不可旁聽與報名，並於上課開始前或結束後關閉）',
		'GB2312'		=> '开课（不可旁听与报名，并于上课开始前或结束后关闭）',
		'en'			=> 'Opening(Auditing and enrolled not allowed. Course is not accessible before the course start date and after the end date.)',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['cs_state_prepare'] = array(
		'Big5'			=> '準備中（限教師）',
		'GB2312'		=> '准备中（限教师）',
		'en'			=> 'In preparation(Instructors only)',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['msg_modify'] = array(
		'Big5'			=> '修改課程資料',
		'GB2312'		=> '修改课程资料',
		'en'			=> 'Modify Course Info',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['msg_cant_delete'] = array(
		'Big5'			=> '此群組中的課程不可刪除',
		'GB2312'		=> '此群组中的课程不可删除',
		'en'			=> 'Courses in this group cannot be deleted.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['msg_cant_move_to'] = array(
		'Big5'			=> '此群組中的課程不可搬移',
		'GB2312'		=> '此群组中的课程不可搬移',
		'en'			=> 'Courses in this group cannot be moved.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['msg_sel_del'] = array(
		'Big5'			=> '請勾選要刪除的課程。',
		'GB2312'		=> '请勾选要删除的课程。',
		'en'			=> 'Please select the courses you want to delete.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['msg_sel_act'] = array(
		'Big5'			=> '請勾選要動作的課程。',
		'GB2312'		=> '请勾选要动作的课程。',
		'en'			=> 'Please select courses you want modify.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['msg_sel_target'] = array(
		'Big5'			=> '請勾選目的地群組。',
		'GB2312'		=> '请勾选目的地群组。',
		'en'			=> 'Please select the destination group.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['msg_append_success'] = array(
		'Big5'			=> '課程附屬到群組成功 ',
		'GB2312'		=> '课程附属到群组成功',
		'en'			=> 'Course successfully added to group.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['msg_move_success'] = array(
		'Big5'			=> '課程搬移到群組成功 ',
		'GB2312'		=> '课程搬移到群组成功',
		'en'			=> 'Course successfully moved to group.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['msg_confirm_del'] = array(
		'Big5'			=> '從群組中移除只會將課程從群組中移除，並不會真的刪除課程。你確定要移除嗎？',
		'GB2312'		=> '从群组中移除只会将课程从群组中移除，并不会真的删除课程。你确定要移除吗？',
		'en'			=> 'This action will only remove the course from this group. It will not delete the course. Continue?',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['msg_del_success'] = array(
		'Big5'			=> '課程從群組中移除成功 ',
		'GB2312'		=> '课程从群组中移除成功',
		'en'			=> 'Course successfully removed from group.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['msg_delete_course'] = array(
		'Big5'			=> '這將會從系統中完全移除所選的課程與所包含的資料，你確定要刪除嗎？',
		'GB2312'		=> '这将会从系统中完全移除所选的课程与所包含的资料，你确定要删除吗？',
		'en'			=> 'This will remove all the selected courses from system. Are you sure you want to delete?',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['msg_delete_course_success'] = array(
		'Big5'			=> '刪除成功！',
		'GB2312'		=> '删除成功！',
		'en'			=> 'Successfully deleted!',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['msg_delete_course_fail'] = array(
		'Big5'			=> '刪除失敗！',
		'GB2312'		=> '删除失败！',
		'en'			=> 'Delete fail!',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['msg_cant_move'] = array(
		'Big5'			=> '此處不支援上下移。',
		'GB2312'		=> '此处不支持上下移。',
		'en'			=> 'Moving up and down not allowed.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['msg_sel_move'] = array(
		'Big5'			=> '請先選取要上下移的課程',
		'GB2312'		=> '请先选取要上下移的课程',
		'en'			=> 'Please select courses you want to move up or down.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['msg_cant_up'] = array(
		'Big5'			=> '無法再上移',
		'GB2312'		=> '无法再上移',
		'en'			=> 'Unable to move up anymore.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['msg_cant_down'] = array(
		'Big5'			=> '無法再下移',
		'GB2312'		=> '无法再下移',
		'en'			=> 'Unable to move down anymore.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['msg_all_course'] = array(
		'Big5'			=> '全校所有課程',
		'GB2312'		=> '全校所有课程',
		'en'			=> 'All Courses',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['msg_unlimit'] = array(
		'Big5'			=> '不限制',
		'GB2312'		=> '不限制',
		'en'			=> 'Unlimited',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['msg_unknow'] = array(
		'Big5'			=> '未知',
		'GB2312'		=> '未知',
		'en'			=> 'Unknown',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['query_course'] = array(
		'Big5'			=> '查詢課程：',
		'GB2312'		=> '查询课程：',
		'en'			=> 'Search:',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['query_string'] = array(
		'Big5'			=> '輸入課程名稱關鍵字',
		'GB2312'		=> '输入课程名称关键字',
		'en'			=> 'Enter Course Keyword',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['msg_query_result'] = array(
		'Big5'			=> '查詢結果',
		'GB2312'		=> '查询结果',
		'en'			=> 'Search Result',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['btn_ok'] = array(
		'Big5'			=> '確定',
		'GB2312'		=> '确定',
		'en'			=> 'OK',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['btn_cancel'] = array(
		'Big5'			=> '取消',
		'GB2312'		=> '取消',
		'en'			=> 'Cancel',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['btn_add_course'] = array(
		'Big5'			=> '新增課程',
		'GB2312'		=> '新增课程',
		'en'			=> 'Add Course',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['btn_alt_add_course'] = array(
		'Big5'			=> '新增一門課程',
		'GB2312'		=> '新增一门课程',
		'en'			=> 'Add Course',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['btn_del_course'] = array(
		'Big5'			=> '刪除課程',
		'GB2312'		=> '删除课程',
		'en'			=> 'Delete Course',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['btn_alt_del_course'] = array(
		'Big5'			=> '刪除一門課程',
		'GB2312'		=> '删除一门课程',
		'en'			=> 'Delete Course',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['btn_move_up'] = array(
		'Big5'			=> '上移',
		'GB2312'		=> '上移',
		'en'			=> 'Up',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['btn_move_down'] = array(
		'Big5'			=> '下移',
		'GB2312'		=> '下移',
		'en'			=> 'Down',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['btn_alt_move_up'] = array(
		'Big5'			=> '將勾選的課程往上移動',
		'GB2312'		=> '将勾选的课程往上移动',
		'en'			=> 'Move the selected course(s) up.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['btn_alt_move_down'] = array(
		'Big5'			=> '將勾選的課程往下移動',
		'GB2312'		=> '将勾选的课程往下移动',
		'en'			=> 'Move the selected course(s) down.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['btn_append'] = array(
		'Big5'			=> '附屬到群組',
		'GB2312'		=> '附属到群组',
		'en'			=> 'Add to Group',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['btn_alt_append'] = array(
		'Big5'			=> '將勾選的課程加入到其它群組內',
		'GB2312'		=> '将勾选的课程加入到其它群组内',
		'en'			=> 'Add the selected course(s) to other groups.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['btn_move'] = array(
		'Big5'			=> '搬移到群組',
		'GB2312'		=> '搬移到群组',
		'en'			=> 'Move to Group',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['btn_alt_move'] = array(
		'Big5'			=> '將勾選的課程移到到其它群組內',
		'GB2312'		=> '将勾选的课程移到到其它群组内',
		'en'			=> 'Move the selected course(s) to other groups.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['btn_delete'] = array(
		'Big5'			=> '從群組中移除',
		'GB2312'		=> '从群组中移除',
		'en'			=> 'Remove from Group',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['btn_alt_delete'] = array(
		'Big5'			=> '將勾選的課程從此群組中移除',
		'GB2312'		=> '将勾选的课程从此群组中移除',
		'en'			=> 'Remove the selected course(s) from this group.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['td_course_id'] = array(
		'Big5'			=> '課程編號',
		'GB2312'		=> '课程编号',
		'en'			=> 'Course ID',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['td_course_name'] = array(
		'Big5'			=> '課程名稱',
		'GB2312'		=> '课程名称',
		'en'			=> 'Course Title',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['td_enroll'] = array(
		'Big5'			=> '報名起迄日期',
		'GB2312'		=> '报名起迄日期',
		'en'			=> 'Registration Period',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['td_study'] = array(
		'Big5'			=> '上課起迄日期',
		'GB2312'		=> '上课起迄日期',
		'en'			=> 'Course Period',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['td_modify'] = array(
		'Big5'			=> '修改',
		'GB2312'		=> '修改',
		'en'			=> 'Modify',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['td_teacher'] = array(
		'Big5'			=> '開課教師',
		'GB2312'		=> '开课教师',
		'en'			=> 'Course Provider',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['td_content'] = array(
		'Big5'			=> '教材使用',
		'GB2312'		=> '教材使用',
		'en'			=> 'Content Used',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['td_status'] = array(
		'Big5'			=> '課程狀態',
		'GB2312'		=> '课程状态',
		'en'			=> 'Course Status',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['td_group'] = array(
		'Big5'			=> '所屬課程群組',
		'GB2312'		=> '所属课程群组',
		'en'			=> 'Group',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['td_reference'] = array(
		'Big5'			=> '教材或參考書',
		'GB2312'		=> '教材或参考书',
		'en'			=> 'References',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['td_url'] = array(
		'Big5'			=> '相關網站',
		'GB2312'		=> '相关网站',
		'en'			=> 'Reference Sites',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['td_introduce'] = array(
		'Big5'			=> '課程簡介',
		'GB2312'		=> '课程简介',
		'en'			=> 'Course Intro',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['td_credit'] = array(
		'Big5'			=> '學分數',
		'GB2312'		=> '学分数',
		'en'			=> 'Total<br>Credits',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['td_student'] = array(
		'Big5'			=> '正式生人數',
		'GB2312'		=> '正式生人数',
		'en'			=> 'Total<br>Enrolled<br>Students',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['td_auditor'] = array(
		'Big5'			=> '旁聽生人數',
		'GB2312'		=> '旁听生人数',
		'en'			=> 'Total Auditors',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['td_people'] = array(
		'Big5'			=> '人',
		'GB2312'		=> '人',
		'en'			=> 'People',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['td_quota'] = array(
		'Big5'			=> '教材空間上限',
		'GB2312'		=> '教材空间上限',
		'en'			=> 'Quota',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['td_remain'] = array(
		'Big5'			=> '剩餘可用空間',
		'GB2312'		=> '剩余可用空间',
		'en'			=> 'Space remain',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['td_sysbar'] = array(
		'Big5'			=> '功能列設定',
		'GB2312'		=> '功能列设定',
		'en'			=> 'Sysbar Settings',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['btn_sysbar'] = array(
		'Big5'			=> '設定功能列',
		'GB2312'		=> '设定功能列',
		'en'			=> 'Setup Sysbar',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['btn_return'] = array(
		'Big5'			=> '回課程列表',
		'GB2312'		=> '回课程列表',
		'en'			=> 'Return to Course List',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['msg_group_id_error'] = array(
		'Big5'			=> '群組編號錯誤',
		'GB2312'		=> '群组编号错误',
		'en'			=> 'Group ID error!',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['msg_not_use_content'] = array(
		'Big5'			=> '不使用教材庫的教材',
		'GB2312'		=> '不使用教材库的教材',
		'en'			=> 'Not to use materials from content database',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['expend'] = array(
		'Big5'			=> '展開',
		'GB2312'		=> '展开',
		'en'			=> 'Expand',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['collect'] = array(
		'Big5'			=> '收攏',
		'GB2312'		=> '收拢',
		'en'			=> 'Collapse',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['title_add_course'] = array(
		'Big5'			=> '新增課程',
		'GB2312'		=> '新增课程',
		'en'			=> 'Add Course ',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['title_modify_course'] = array(
		'Big5'			=> '修改課程',
		'GB2312'		=> '修改课程',
		'en'			=> 'Modify Course ',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['msg_access_deny'] = array(
		'Big5'			=> '拒絕存取',
		'GB2312'		=> '拒绝存取',
		'en'			=> 'Access Denied',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['msg_need_content'] = array(
		'Big5'			=> '尚未建立或開放教材，現在就要建立或開放教材嗎？',
		'GB2312'		=> '尚未建立或开放教材，现在就要建立或开放教材吗？',
		'en'			=> 'Do you want to create or share content now?',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['msg_input_course_name'] = array(
		'Big5'			=> '請至少輸入課程名稱！',
		'GB2312'		=> '请至少输入课程名称！',
		'en'			=> 'Please enter course title!',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['msg_course_name_style_error'] = array(
		'Big5'			=> '課程名稱不能有特殊字元如 / : ; &#039; &quot; { } 等字元',
		'GB2312'		=> '课程名称不能有特殊字符如 / : ; &#039; &quot; { } 等字符',
		'en'			=> 'Course titles cannot contain special characters such as  / : ; &#039; &quot; { } .',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['msg_only_digital'] = array(
		'Big5'			=> '只能填寫數字！',
		'GB2312'		=> '只能填写数字！',
		'en'			=> 'Only numbers are allowed!',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['param_close'] = array(
		'Big5'			=> '關閉',
		'GB2312'		=> '关闭',
		'en'			=> 'Close',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['param_adminstrator'] = array(
		'Big5'			=> '限管理員',
		'GB2312'		=> '限管理员',
		'en'			=> 'Admin only',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['param_open_a'] = array(
		'Big5'			=> '開課（可旁聽與報名，且不受上課起訖日期限制）',
		'GB2312'		=> '开课（可旁听与报名，且不受上课起讫日期限制）',
		'en'			=> 'Opening(Auditing and enrolled allowed. Course is accessible between and beyond start date and end date.)',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['param_open_a_date'] = array(
		'Big5'			=> '開課（可旁聽與報名，並於上課開始前或結束後關閉）',
		'GB2312'		=> '开课（可旁听与报名，并于上课开始前或结束后关闭）',
		'en'			=> 'Opening(Auditing and enrolled allowed. Course is not accessible before the course start date and after the end date.)',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['param_open_n'] = array(
		'Big5'			=> '開課（不可旁聽與報名，且不受上課起訖日期限制）',
		'GB2312'		=> '开课（不可旁听与报名，且不受上课起讫日期限制）',
		'en'			=> 'Opening(Auditing and enrolled not allowed. Course is accessible between and beyond start date and end date.)',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['param_open_n_date'] = array(
		'Big5'			=> '開課（不可旁聽與報名，並於上課開始前或結束後關閉）',
		'GB2312'		=> '开课（不可旁听与报名，并于上课开始前或结束后关闭）',
		'en'			=> 'Opening(Auditing and enrolled not allowed. Course is not accessible before the course start date and after the end date.)',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['param_prepare'] = array(
		'Big5'			=> '準備中（限教師）',
		'GB2312'		=> '准备中（限教师）',
		'en'			=> 'In preparation(Instructors only)',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['th_course_name'] = array(
		'Big5'			=> '課程名稱',
		'GB2312'		=> '课程名称',
		'en'			=> 'Course Title',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['th_review_name'] = array(
		'Big5'			=> '修課審核',
		'GB2312'		=> '修课审核',
		'en'			=> 'Course<br>Review',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['th_teacher'] = array(
		'Big5'			=> '開課教師',
		'GB2312'		=> '开课教师',
		'en'			=> 'Course<br>Provider',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['th_content'] = array(
		'Big5'			=> '教材使用',
		'GB2312'		=> '教材使用',
		'en'			=> 'Content<br>Used',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['th_enroll_begin'] = array(
		'Big5'			=> '開始報名',
		'GB2312'		=> '开始报名',
		'en'			=> 'Registration Start Date',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['th_enroll_end'] = array(
		'Big5'			=> '報名截止',
		'GB2312'		=> '报名截止',
		'en'			=> 'Registration End Date',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['th_study_begin'] = array(
		'Big5'			=> '開始上課',
		'GB2312'		=> '开始上课',
		'en'			=> 'Course Start Date',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['th_study_end'] = array(
		'Big5'			=> '課程結束',
		'GB2312'		=> '课程结束',
		'en'			=> 'Course End Date',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['th_course_status'] = array(
		'Big5'			=> '課程狀態',
		'GB2312'		=> '课程状态',
		'en'			=> 'Status',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['th_group'] = array(
		'Big5'			=> '所屬群組',
		'GB2312'		=> '所属群组',
		'en'			=> 'Group',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['th_book'] = array(
		'Big5'			=> '課本&教材',
		'GB2312'		=> '课本&教材',
		'en'			=> 'Textbooks',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['th_url'] = array(
		'Big5'			=> '相關連結',
		'GB2312'		=> '相关连结',
		'en'			=> 'Reference Sites',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['th_introduce'] = array(
		'Big5'			=> '課程簡介',
		'GB2312'		=> '课程简介',
		'en'			=> 'Course Intro',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['th_picture'] = array(
		'Big5'			=> '課程代表圖',
		'GB2312'		=> '课程代表图',
		'en'			=> 'Course represented in Figure',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['th_credit'] = array(
		'Big5'			=> '學分',
		'GB2312'		=> '学分',
		'en'			=> 'Total<br>Credits',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['th_student'] = array(
		'Big5'			=> '正式生人數',
		'GB2312'		=> '正式生人数',
		'en'			=> 'Total<br>Enrolled<br>Students',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['th_auditor'] = array(
		'Big5'			=> '旁聽生人數',
		'GB2312'		=> '旁听生人数',
		'en'			=> 'Total<br>Auditors',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['th_usage'] = array(
		'Big5'			=> '已使用空間',
		'GB2312'		=> '已使用空间',
		'en'			=> 'Space Used',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['th_quota'] = array(
		'Big5'			=> '空間限制',
		'GB2312'		=> '空间限制',
		'en'			=> 'Quota',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['th_alt_course_name'] = array(
		'Big5'			=> '* 本欄位必須填寫<br />
					    <font color="red">課程名稱不允許有 \:;&#039;&quot;{}等字元</font><br />
						長度請勿超過 254 個英文字',
		'GB2312'		=> '* 本栏位必须填写<br /><font color=red>课程名称不允许有\:;&#039;&quot;{}等字符</font><br />长度请勿超过 254 个英文字符',
		'en'			=> '*Required<br /><font color=red>Course titles cannot contain the following characters: \:;&#039;&quot;{}.</font><br/>No more than 254 Roman characters.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['th_alt_teacher'] = array(
		'Big5'			=> '僅供顯示用，限 128 個英文字',
		'GB2312'		=> '仅供显示用，限 128 个英文字符',
		'en'			=> 'Display only. No more than 128 Roman characters.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['th_alt_enroll_begin'] = array(
		'Big5'			=> '(不限定則不必填)',
		'GB2312'		=> '(不限定则不必填)',
		'en'			=> 'Leave blank if not limited.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['th_alt_book'] = array(
		'Big5'			=> '限 254 個英文字',
		'GB2312'		=> '限 254 个英文字符',
		'en'			=> 'No more than 254 Roman characters.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['th_alt_introduce'] = array(
		'Big5'			=> '限 60000 個英文字',
		'GB2312'		=> '限 60000 个英文字符',
		'en'			=> 'No more than 60000 Roman characters.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['th_alt_student'] = array(
		'Big5'			=> '(不限定則不必填或填 0)',
		'GB2312'		=> '(不限定则不必填或填 0)',
		'en'			=> '(Leave blank or add a zero if not limited.)',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['th_alt_quota'] = array(
		'Big5'			=> '* 本欄位必須填寫',
		'GB2312'		=> '* 本栏位必须填写',
		'en'			=> '*Required',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['year_1'] = array(
		'Big5'			=> '西元',
		'GB2312'		=> '公元',
		'en'			=> 'Year: ',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['year_2'] = array(
		'Big5'			=> ' 年 ',
		'GB2312'		=> '年',
		'en'			=> '/',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['month'] = array(
		'Big5'			=> ' 月 ',
		'GB2312'		=> '月',
		'en'			=> '/',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['day'] = array(
		'Big5'			=> ' 日 ',
		'GB2312'		=> '日',
		'en'			=> 'Day',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['unlimit'] = array(
		'Big5'			=> '不限',
		'GB2312'		=> '不限',
		'en'			=> 'Unlimited',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['btn_find_content'] = array(
		'Big5'			=> '尋找教材',
		'GB2312'		=> '寻找教材',
		'en'			=> 'Search Content',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['people'] = array(
		'Big5'			=> '人',
		'GB2312'		=> '人',
		'en'			=> 'People',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['btn_save'] = array(
		'Big5'			=> '儲存',
		'GB2312'		=> '保存',
		'en'			=> 'Save',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['btn_reset'] = array(
		'Big5'			=> '重新填寫',
		'GB2312'		=> '重新填写',
		'en'			=> 'Reset',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['btn_select_group'] = array(
		'Big5'			=> '挑選群組',
		'GB2312'		=> '挑选群组',
		'en'			=> 'Select Group',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['title_group_list'] = array(
		'Big5'			=> '群組列表',
		'GB2312'		=> '群组列表',
		'en'			=> 'Group List',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['discuss'] = array(
		'Big5'			=> '課程討論板',
		'GB2312'		=> '课程讨论板',
		'en'			=> 'Discussion Forum',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['bulletin'] = array(
		'Big5'			=> '課程公告板',
		'GB2312'		=> '课程公告板',
		'en'			=> 'Announcement Board',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['title_save_course'] = array(
		'Big5'			=> '儲存課程資料',
		'GB2312'		=> '保存课程资料',
		'en'			=> 'Save',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['save_successed'] = array(
		'Big5'			=> '成功 ',
		'GB2312'		=> '成功',
		'en'			=> 'Succeeded!',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['save_failed'] = array(
		'Big5'			=> '失敗或資料不需要更新 ',
		'GB2312'		=> '失败或资料不需要更新',
		'en'			=> 'Failed or no update needed.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['btn_renew'] = array(
		'Big5'			=> '繼續開課',
		'GB2312'		=> '继续开课',
		'en'			=> 'Create more courses',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['btn_setTeacher'] = array(
		'Big5'			=> '設定本課程授課教師',
		'GB2312'		=> '设定本课程授课教师',
		'en'			=> 'Instructor Setup',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['add_teacher'] = array(
		'Big5'			=> '新增授課教師',
		'GB2312'		=> '新增授课教师',
		'en'			=> 'Add Instructor',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['teacher_list'] = array(
		'Big5'			=> '目前教師、助教、講師列表',
		'GB2312'		=> '目前教师、助教、讲师列表',
		'en'			=> 'List of current instructors or TAs',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['theading_seq'] = array(
		'Big5'			=> '序號',
		'GB2312'		=> '序号',
		'en'			=> 'No.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['theading_account'] = array(
		'Big5'			=> '帳號',
		'GB2312'		=> '帐号',
		'en'			=> 'Username',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['theading_realname'] = array(
		'Big5'			=> '姓名',
		'GB2312'		=> '姓名',
		'en'			=> 'Name',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['theading_level'] = array(
		'Big5'			=> '身分',
		'GB2312'		=> '身分',
		'en'			=> 'Access Level',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['theading_delete'] = array(
		'Big5'			=> '刪除',
		'GB2312'		=> '删除',
		'en'			=> 'Delete',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['btn_add_teacher'] = array(
		'Big5'			=> '新增教師',
		'GB2312'		=> '新增教师',
		'en'			=> 'Add Instructor',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['btn_delete_teacher'] = array(
		'Big5'			=> '刪除',
		'GB2312'		=> '删除',
		'en'			=> 'Delete',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['alert_error'] = array(
		'Big5'			=> '未輸入使用者帳號，無法進行新增動作！',
		'GB2312'		=> '未输入使用者帐号，无法进行新增动作！',
		'en'			=> 'Please enter username.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['confirm_delete'] = array(
		'Big5'			=> '確定移除此位使用者教師權限',
		'GB2312'		=> '确定移除此位使用者教师权限',
		'en'			=> 'Are you sure to remove this user&#039;s instructor access?',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['server_response1'] = array(
		'Big5'			=> '已新增',
		'GB2312'		=> '已新增',
		'en'			=> ' successfully added.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['server_response2'] = array(
		'Big5'			=> '帳號不存在',
		'GB2312'		=> '帐号不存在',
		'en'			=> ' does not exist!',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['server_response3'] = array(
		'Big5'			=> '已具有課程教師的身分',
		'GB2312'		=> '已具有课程教师的身分',
		'en'			=> ' already has instructor access.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['server_response4'] = array(
		'Big5'			=> '移除權限完成',
		'GB2312'		=> '移除权限完成',
		'en'			=> ' successfully removed!',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['tabs_course_set'] = array(
		'Big5'			=> '課程設定',
		'GB2312'		=> '课程设定',
		'en'			=> 'Course Settings',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['btn_enable'] = array(
		'Big5'			=> '啟用',
		'GB2312'		=> '启用',
		'en'			=> 'Enable',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['msg_enable_date'] = array(
		'Big5'			=> '，日期：',
		'GB2312'		=> '，日期：',
		'en'			=> ', Date:',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['msg_date_error'] = array(
		'Big5'			=> '關閉日期必須大於開始日期，請重新設定。',
		'GB2312'		=> '关闭日期必须大于开始日期，请重新设定。',
		'en'			=> 'Close date must be latter than start date. Please reset the date.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);
        
        $MSG['msg_date_error2'] = array(
		'Big5'			=> '報名截止日期必須小於課程結束日期，請重新設定。',
		'GB2312'		=> '报名截止日期必须小于课程结束日期，请重新设定。',
		'en'			=> 'The deadline must be less than the course end date. Please reset the date.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['btn_return_set'] = array(
		'Big5'			=> '回課程設定',
		'GB2312'		=> '回课程设定',
		'en'			=> 'Return',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['cour_mail_send'] = array(
		'Big5'			=> '寄送信件',
		'GB2312'		=> '寄送信件',
		'en'			=> 'Send email',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['cour_mail_help2'] = array(
		'Big5'			=> '請先選取要寄送信件的課程',
		'GB2312'		=> '请先选取要寄送信件的课程',
		'en'			=> 'Please select courses which you want to send email to.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['title'] = array(
		'Big5'			=> '編輯郵件',
		'GB2312'		=> '编辑邮件',
		'en'			=> 'Edit email',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['need_to1'] = array(
		'Big5'			=> '收件者不得空白',
		'GB2312'		=> '收件者不得空白',
		'en'			=> 'Receiver field cannot be left empty.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['need_to2'] = array(
		'Big5'			=> '主旨跟內容都要填寫',
		'GB2312'		=> '主题跟内容都要填写',
		'en'			=> 'Subject and content fields should be filled in.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['accept'] = array(
		'Big5'			=> '收件群組',
		'GB2312'		=> '收件群组',
		'en'			=> 'Receiving Group',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['guest'] = array(
		'Big5'			=> '參觀者',
		'GB2312'		=> '参观者',
		'en'			=> 'Guest',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['senior'] = array(
		'Big5'			=> '學長',
		'GB2312'		=> '学长',
		'en'			=> 'Senior',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['paterfamilias'] = array(
		'Big5'			=> '家長',
		'GB2312'		=> '家长',
		'en'			=> 'Parents',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['superintendent'] = array(
		'Big5'			=> '長官/督學',
		'GB2312'		=> '督学',
		'en'			=> 'Superintendent',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['auditor'] = array(
		'Big5'			=> '旁聽生',
		'GB2312'		=> '旁听生',
		'en'			=> 'Auditor',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['student'] = array(
		'Big5'			=> '正式生',
		'GB2312'		=> '正式生',
		'en'			=> 'Enrolled Student',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['assistant'] = array(
		'Big5'			=> '助教',
		'GB2312'		=> '助教',
		'en'			=> 'TA',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['instructor'] = array(
		'Big5'			=> '講師',
		'GB2312'		=> '讲师',
		'en'			=> 'Guest Instructor',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['teacher'] = array(
		'Big5'			=> '教師',
		'GB2312'		=> '教师',
		'en'			=> 'Instructor',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['director'] = array(
		'Big5'			=> '導師',
		'GB2312'		=> '导师',
		'en'			=> 'Supervisor',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['mail_txt'] = array(
		'Big5'			=> '若要寄給其他人員，可在此填入收件者email。',
		'GB2312'		=> '若要寄给其他人员，可在此填入收件者email。',
		'en'			=> 'If you want to email other people, fill in their email addresses here.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['write_attachement'] = array(
		'Big5'			=> '附件：',
		'GB2312'		=> '附件：',
		'en'			=> 'Attachment:',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['tabs_send_result'] = array(
		'Big5'			=> '寄信發送結果',
		'GB2312'		=> '寄信发送结果',
		'en'			=> 'Sent Result',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['goto_msg_center'] = array(
		'Big5'			=> '回到寄信給群組主畫面',
		'GB2312'		=> '回到寄信给群组主画面',
		'en'			=> 'Return to Email Groups',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['fair_grade'] = array(
		'Big5'			=> '及格成績 ',
		'GB2312'		=> '及格成绩',
		'en'			=> 'Passing<br>Grade',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['title16'] = array(
		'Big5'			=> '匯出 ',
		'GB2312'		=> '导出',
		'en'			=> 'Export',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['title17'] = array(
		'Big5'			=> '請勾選要匯出的課程或課程群組。',
		'GB2312'		=> '请勾选要导出的课程或课程群组。',
		'en'			=> 'Please select courses or groups you want to export.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['title18'] = array(
		'Big5'			=> '全選 ',
		'GB2312'		=> '全选',
		'en'			=> 'Select All',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['title19'] = array(
		'Big5'			=> '全消 ',
		'GB2312'		=> '全消',
		'en'			=> 'Cancel All',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['title20'] = array(
		'Big5'			=> '全部展開 ',
		'GB2312'		=> '全部展开',
		'en'			=> 'Expand',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['title21'] = array(
		'Big5'			=> '全部收攏 ',
		'GB2312'		=> '全部收拢',
		'en'			=> 'Collapse',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['title23'] = array(
		'Big5'			=> '課程 - 匯出人員資料 ',
		'GB2312'		=> '课程 - 导出人员资料',
		'en'			=> 'Course - Export member data',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['title24'] = array(
		'Big5'			=> '回匯出人員資料 ',
		'GB2312'		=> '回导出人员资料',
		'en'			=> 'Return to Export Member Data',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

  $MSG['sync_chat_room'] = array(
		'Big5'			=> '同步討論室 ',
		'GB2312'		=> '同步讨论室',
		'en'			=> 'New Chat Room',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['from2'] = array(
		'Big5'			=> '從 ',
		'GB2312'		=> '从',
		'en'			=> 'From',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['to2'] = array(
		'Big5'			=> '到 ',
		'GB2312'		=> '到',
		'en'			=> 'To',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['now'] = array(
		'Big5'			=> '即日起',
		'GB2312'		=> '即日起',
		'en'			=> 'Now',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['forever'] = array(
		'Big5'			=> '無限期',
		'GB2312'		=> '无限期',
		'en'			=> 'Anytime',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['title39'] = array(
		'Big5'			=> '列出管理者所新增的審核規則<br />，預設為不須審核。',
		'GB2312'		=> '列出管理者所新增的审核规则<br />，预设为不须审核。',
		'en'			=> 'List all new review rules.<br /> Default: review not required.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['title40'] = array(
		'Big5'			=> '注意：<br /><font color="#ff0000;">開課數超過上限，無法開課。</font><br />本平台最多可開<%sysCourseLimit%>門課，<br />目前課程數目已經超過此數。<br />若有疑問，請洽<%sysAdmin%>。',
		'GB2312'		=> '注意：<br /><font color="#ff0000;">开课数超过上限，无法开课。</font><br />本平台最多可开<%sysCourseLimit%>门课，<br />目前课程数目已经超过此数。<br />若有疑问，请洽<%sysAdmin%>。',
		'en'			=> 'Attention please, <br /><font color="#ff0000;">The total amount  of courses is exceed its limitation, it can not add any new course.</font><br />The maxmum course amount of site is <%sysCourseLimit%>, <br /> the total amount  is about its  limitation.<br />If there is any question, please contact <%sysAdmin%>.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['title41'] = array(
		'Big5'			=> '系統管理人員',
		'GB2312'		=> '系统管理人员',
		'en'			=> 'System administrator',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['title42'] = array(
		'Big5'			=> '項目',
		'GB2312'		=> '项目',
		'en'			=> 'Item/selection',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['title43'] = array(
		'Big5'			=> '允許教師更改',
		'GB2312'		=> '允许教师更改',
		'en'			=> 'Revison permitted by instructor',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['title44'] = array(
		'Big5'			=> '內容',
		'GB2312'		=> '内容',
		'en'			=> 'Content',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['title45'] = array(
		'Big5'			=> '備註',
		'GB2312'		=> '备注',
		'en'			=> 'Remark',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['course_limit_desc'] = array(
		'Big5'			=> '注意：<br>
						目前系統課程數已達開課數上限，無法再新增課程。<br>
						本平台設定最多可建立<font color="red"> %sysCourseLimit% </font>門課，目前課程數目也已經達到此上限。<br>
						若有疑問，請洽<a href="%admin_email%">系統管理人員。</a>',
		'GB2312'		=> '备注',
		'en'			=> 'Attention,<br> the amount of courses this site is about its limitation, it can not create any new course.<<br>This site can only create  <font color="red"> %sysCourseLimit% </font>. Now it reaches its maximum number.<br> If there is any question, please contact <a href="%admin_email%">system administrator. </a>',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['title_install'] = array(
		'Big5'			=> '安裝課程',
		'GB2312'		=> '安装课程',
		'en'			=> 'Install Course',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	// 寄信點名 的 rule 預設 信件的內容 begin
	$MSG['roll_call_mail_subject_default1'] = array(
		'Big5'			=> '%COURSE_NAME%上課通知',
		'GB2312'		=> '%COURSE_NAME%上课通知',
		'en'			=> 'Class infomation from %COURSE_NAME%',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);
	$MSG['roll_call_mail_content_default1'] = array(
			'Big5'			=> '%username%(%realname%)：<br>	你已經超過 7 日沒有到%COURSE_NAME% 來上課，<br>請把握學習機會，趕快上來看看課堂上有什麼新消息！',
			'GB2312'		=> '%username%(%realname%)：<br>	你已经超过 7 日没有到%COURSE_NAME% 来上课，<br>请把握学习机会，赶快上来看看课堂上有什么新消息！',
			'en'			=> 'Hi %username%(%realname%), <br>    You have not attend %COURSE_NAME% for seven days.<br>Please come here and see what happening in your class!',
			'EUC-JP'		=> '',
			'user_define'	=> ''
		);
	$MSG['roll_call_mail_subject_default2'] = array(
			'Big5'			=> '%COURSE_NAME%測驗通知',
			'GB2312'		=> '%COURSE_NAME%测验通知',
			'en'			=> 'Exam information from %COURSE_NAME%.',
			'EUC-JP'		=> '',
			'user_define'	=> ''
		);

	$MSG['roll_call_mail_content_default2'] = array(
			'Big5'			=> '%username%(%realname%)：<br>你在%COURSE_NAME%中有 1個以上的測驗卷尚未完成，<br>請儘速在測驗結束日之前把握學習應考，以免影響考核成績！',
			'GB2312'		=> '%username%(%realname%)：<br>你在%COURSE_NAME%中有 1个以上的测验卷尚未完成，<br>请尽速在测验结束日之前把握学习应考，以免影响考核成绩！',
			'en'			=> 'Hi %username%(%realname%):<br> You still have one or more exam to take in class %COURSE_NAME%.<br>Please take exam as soon as the due has came in order not to affect your grade. ',
			'EUC-JP'		=> '',
			'user_define'	=> ''
		);
	$MSG['roll_call_mail_subject_default3'] = array(
			'Big5'			=> '%COURSE_NAME%報告繳交通知',
			'GB2312'		=> '%COURSE_NAME%报告缴交通知',
			'en'			=> 'Homework information from %COURSE_NAME%.',
			'EUC-JP'		=> '',
			'user_define'	=> ''
		);

	$MSG['roll_call_mail_content_default3'] = array(
			'Big5'			=> '%username%(%realname%)：<br>你在%COURSE_NAME%中有 1份以上的報告尚未繳交，<br>請儘速在繳交結束日之前把握學習應考，以免影響考核成績！',
			'GB2312'		=> '%username%(%realname%)：<br>你在%COURSE_NAME%中有 1份以上的报告尚未缴交，<br>请尽速在缴交结束日之前把握学习应考，以免影响考核成绩！',
			'en'			=> 'Hi %username%(%realname%):<br> You still have one or more homework to hand in class %COURSE_NAME%.<br>Please take exam as soon as the due has came in order not to affect your grade. ',
			'EUC-JP'		=> '',
			'user_define'	=> ''
		);

	$MSG['roll_call_mail_subject_default4'] = array(
			'Big5'			=> '%COURSE_NAME%問卷繳交通知',
			'GB2312'		=> '%COURSE_NAME%问卷缴交通知',
			'en'			=> 'Questionnaire infomation from %COURSE_NAME%.',
			'EUC-JP'		=> '',
			'user_define'	=> ''
		);

	$MSG['roll_call_mail_content_default4'] = array(
			'Big5'			=> '%username%(%realname%)：<br>你在%COURSE_NAME%中有 1份以上的問卷尚未繳交，<br>請儘速在繳交結束日之前把握學習應考，以免影響考核成績！',
			'GB2312'		=> '%username%(%realname%)：<br>你在%COURSE_NAME%中有 1份以上的问卷尚未缴交，<br>请尽速在缴交结束日之前把握学习应考，以免影响考核成绩！',
			'en'			=> 'Hi %username%(%realname%):<br> You still have one or more questionnaire to take in class %COURSE_NAME%.<br>Please take exam as soon as the due has came in order not to affect your grade. ',
			'EUC-JP'		=> '',
			'user_define'	=> ''
		);
	// 寄信點名 的 rule 預設 信件的內容 end

	$MSG['select_teacher'] = array(
		'Big5'			=> '選擇授課教師',
		'GB2312'		=> '选择授课教师',
		'en'			=> 'select',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['select_teacher_note'] = array(
		'Big5'			=> '可輸入多個帳號以新增多位教師，請在各帳號之間加分號(;)',
		'GB2312'		=> '可输入多个帐号以新增多位教师，请在各帐号之间加分号(;)',
		'en'			=> 'More than one instructor account can be added. Please add semicolon (;) between accounts.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['msg_picture'] = array(
		'Big5'			=> '為了使社群分享時代表圖能正確抓到，建議照片的寬高為 710*400，正確檔案需小於 200 KB。',
		'GB2312'		=> '为了使社群分享时代表图能正确抓到，建议照片的宽高为 710*400，正确档案需小于 200 KB。',
		'en'			=> 'Photo size: 710*400 & no more than 100 KB.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

    $MSG['sync_to_calendar'] = array(
        'Big5'			=> '同步到行事曆',
        'GB2312'		=> '同步到行事历',
        'en'			=> 'Sync to Calendar',
        'EUC-JP'		=> '',
        'user_define'	=> ''
    );

    $MSG['msg_modify_course_capacity'] = array(
        'Big5'			=> '修改課程容量',
        'GB2312'		=> '修改课程容量',
        'en'			=> 'Modify course capacity',
        'EUC-JP'		=> '',
        'user_define'	=> ''
    );

    $MSG['msg_modify_course_review'] = array(
        'Big5'			=> '修改課程審核方式',
        'GB2312'		=> '修改课程审核方式',
        'en'			=> 'Modify the review of course',
        'EUC-JP'		=> '',
        'user_define'	=> ''
    );

    $MSG['link_sysbar'] = array(
		'Big5'			=> '功能列',
		'GB2312'		=> '功能列',
		'en'			=> 'Sysbar',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

    $MSG['msg_set_capacity'] = array(
        'Big5'			=> '容量設定',
        'GB2312'		=> '容量设定',
        'en'			=> 'Capacity setting',
        'EUC-JP'		=> '',
        'user_define'	=> ''
    );

    $MSG['msg_set_review'] = array(
        'Big5'			=> '審核設定',
        'GB2312'		=> '审核设定',
        'en'			=> 'Review setting',
        'EUC-JP'		=> '',
        'user_define'	=> ''
    );

    $MSG['td_review_status'] = array(
        'Big5'			=> '審核狀態',
        'GB2312'		=> '审核状态',
        'en'			=> 'Approval Status',
        'EUC-JP'		=> '',
        'user_define'	=> ''
    );

    $MSG['td_course_status'] = array(
        'Big5'			=> '開課狀態',
        'GB2312'		=> '开课状态',
        'en'			=> 'Course Status',
        'EUC-JP'		=> '',
        'user_define'	=> ''
    );

    $MSG['td_creator'] = array(
        'Big5'			=> '開課者',
        'GB2312'		=> '开课者',
        'en'			=> 'Creator',
        'EUC-JP'		=> '',
        'user_define'	=> ''
    );

    $MSG['td_function'] = array(
        'Big5'			=> '功能',
        'GB2312'		=> '功能',
        'en'			=> 'Function',
        'EUC-JP'		=> '',
        'user_define'	=> ''
    );

    $MSG['sync_to_calendar_msg'] = array(
        'Big5'			=> '同步到行事曆會自動建立一個課程事件到課程行事曆中。如果需要，請到課程行事曆進行進階編輯。',
        'GB2312'		=> '同步到行事历会自动建立一个课程事件到课程行事历中。如果需要，请到课程行事历进行进阶编辑。',
        'en'			=> 'Synchronized to the calendar it will automatically establish a course of events to the course calendar. If necessary, go to the course calendar were advanced editing.',
        'EUC-JP'		=> '',
        'user_define'	=> ''
    );

    $MSG['msg_set_stop'] = array(
        'Big5'		=> '停用',
        'GB2312'	=> '停用',
        'en'		=> 'Disable',
        'EUC-JP'	=> '',
        'user_define'	=> ''
    );

    $MSG['msg_set_close'] = array(
        'Big5'		=> '關閉',
        'GB2312'	=> '关闭',
        'en'		=> 'closed',
        'EUC-JP'	=> '',
        'user_define'	=> ''
    );

    $MSG['msg_modify_course_stop'] = array(
        'Big5'		=> '停用課程',
        'GB2312'	=> '停用课程',
        'en'		=> 'Stop course',
        'EUC-JP'	=> '',
        'user_define'	=> ''
    );

    $MSG['msg_modify_course_close'] = array(
        'Big5'		=> '關閉課程',
        'GB2312'	=> '关闭课程',
        'en'		=> 'Close course',
        'EUC-JP'	=> '',
        'user_define'	=> ''
    );

    $MSG['failed'] = array(
        'Big5'		=> '失敗 ',
        'GB2312'	=> '失败',
        'en'		=> 'Failed.',
        'EUC-JP'	=> '',
        'user_define'	=> ''
    );
    
    $MSG['msg_create_success'] = array(
        'Big5'		=> '新增課程成功，繼續設定其他內容。',
        'GB2312'	=> '新增课程成功，继续设定其他内容。',
        'en'		=> 'Create course successfully.',
        'EUC-JP'	=> '',
        'user_define'	=> ''
    );
    
	/*** CUSTOM (B) by Yea ***/
	$MSG['co_year'] = array(
		'Big5'			=> '學年：',
		'GB2312'		=> '学年：',
		'en'			=> 'School year:',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['co_seme'] = array(
		'Big5'			=> '學期：',
		'GB2312'		=> '学期：',
		'en'			=> 'Semester:',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['co_year_1'] = array(
		'Big5'			=> '學年',
		'GB2312'		=> '学年',
		'en'			=> 'School year',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['co_seme_1'] = array(
		'Big5'			=> '學期',
		'GB2312'		=> '学期',
		'en'			=> 'Semester',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['co_group'] = array(
		'Big5'			=> '課程群組名稱',
		'GB2312'		=> '课程群组名称',
		'en'			=> 'Course Group Name',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['co_teacher'] = array(
		'Big5'			=> '教師帳號：',
		'GB2312'		=> '教师帐号：',
		'en'			=> 'Teachers account:',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['co_login'] = array(
		'Big5'			=> '教師登入課程次數：',
		'GB2312'		=> '教师登入课程次数：',
		'en'			=> 'Teachers sign in the number of courses:',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['co_hw'] = array(
		'Big5'			=> '作業份數',
		'GB2312'		=> '作业份数',
		'en'			=> 'Operating shares',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['co_exam'] = array(
		'Big5'			=> '測驗份數',
		'GB2312'		=> '测验份数',
		'en'			=> 'Test shares',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['co_qa'] = array(
		'Big5'			=> '問卷份數',
		'GB2312'		=> '问卷份数',
		'en'			=> 'Questionnaire shares',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['co_node'] = array(
		'Big5'			=> '總節點數',
		'GB2312'		=> '总节点数',
		'en'			=> 'The total number of nodes',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);


	$MSG['co_have_node'] = array(
		'Big5'			=> '節點數-有教材',
		'GB2312'		=> '节点数-有教材',
		'en'			=> 'Nodes - a textbook',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['co_not_node'] = array(
		'Big5'			=> '節點數-沒教材',
		'GB2312'		=> '节点数-没教材',
		'en'			=> 'Nodes - not teaching',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['co_ta'] = array(
		'Big5'			=> '老師帳號(姓名)<br>[進入課程次數(次)]',
		'GB2312'		=> '老师帐号(姓名)<br>[进入课程次数(次)]',
		'en'			=> 'Teachers account (name) <br> [enter the number of courses (at)]',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['co_limit'] = array(
		'Big5'			=> '空間限制(KB)',
		'GB2312'		=> '空间限制(KB)',
		'en'			=> 'Space constraints (KB)',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['co_used'] = array(
		'Big5'			=> '已使用空間(KB)',
		'GB2312'		=> '已使用空间(KB)',
		'en'			=> 'Has been the use of space (KB)',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['co_percent'] = array(
		'Big5'			=> '已使用率',
		'GB2312'		=> '已使用率',
		'en'			=> 'Utilization rate has been',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['co_login_times'] = array(
		'Big5'			=> '課程總登入次數',
		'GB2312'		=> '课程总登入次数',
		'en'			=> 'Course total number of log-ins',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['co_post_times'] = array(
		'Big5'			=> '課程總張貼次數',
		'GB2312'		=> '课程总张贴次数',
		'en'			=> 'The total number of courses posted',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['co_dsc_times'] = array(
		'Big5'			=> '課程總討論次數',
		'GB2312'		=> '课程总讨论次数',
		'en'			=> 'The total number of courses to discuss',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['co_export'] = array(
		'Big5'			=> '全部匯出',
		'GB2312'		=> '全部汇出',
		'en'			=> 'Export',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['msg_description'] = array(
		'Big5'			=> '<font color="red">註：【全部匯出】除了匯出畫面欄位資料,還會多出以下欄位供校方自行統計<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;【課程狀態】,【空間限制】,【已使用空間】,【課程總登入次數】,【課程總張貼次數】,【課程總討論次數】</font>',
		'GB2312'		=> '<font color="red">注：【全部汇出】除了汇出画面栏位资料,还会多出以下栏位供校方自行统计<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;【课程状态】,【空间限制】,【已使用空间】,【课程总登入次数】,【课程总张贴次数】,【课程总讨论次数】</font>',
		'en'			=> '<font color="red"> Note: In addition to [all] Export Export screen information field, but also more the following column for the school to statistics <br> [courses State], [limit], [has] the use of space, the total number of log-ins [courses], [posted total number of courses], [number] to discuss the course </ font>',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);
	/*** CUSTOM (E) by Yea ***/

	/*** CUSTOM (B) by Yea ***/
	$MSG['permissions_set'] = array(
		'Big5'			=> '查詢課程權限設定',
		'GB2312'		=> '查询课程权限设定',
		'en'			=> 'Permissions set',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);
	/*** CUSTOM (E) by Yea ***/

$MSG['msg_delete_success'] = array(
    'Big5' => '刪除成功',
    'GB2312' => '删除成功',
    'en' => 'Deleted.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['msg_delete_fail'] = array(
    'Big5' => '刪除失敗',
    'GB2312' => '删除失败',
    'en' => 'Undeleted.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['msg_delete_nothing'] = array(
    'Big5' => '無異動',
    'GB2312' => '无异动',
    'en' => 'No transaction',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['btn_clear_text'] = array(
    'Big5' => '清除',
    'GB2312' => '清除',
    'en' => 'Clear',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['ckta_caption'] = array(
    'Big5' => '課程名稱',
    'GB2312' => '课程名称',
    'en' => 'Course Title',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['ckta_content_id'] = array(
    'Big5' => '教材使用',
    'GB2312' => '教材使用',
    'en' => 'Content<br>Used',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['ckta_en_begin'] = array(
    'Big5' => '報名期間',
    'GB2312' => '报名期间',
    'en' => 'Registration',
    'EUC-JP' => '',
    'user_define' => ''
);


$MSG['ckta_st_begin'] = array(
    'Big5' => '上課期間',
    'GB2312' => '上课期间',
    'en' => 'During<br>the class',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['ckta_status'] = array(
    'Big5' => '課程狀態',
    'GB2312' => '课程状态',
    'en' => 'Status',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['ckta_review'] = array(
    'Big5' => '修課審核',
    'GB2312' => '修课审核',
    'en' => 'Course<br>Review',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['ckta_cparent'] = array(
    'Big5' => '所屬群組',
    'GB2312' => '所属群组',
    'en' => 'Group',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['ckta_texts'] = array(
    'Big5' => '課本&教材',
    'GB2312' => '课本&教材',
    'en' => 'Textbooks',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['ckta_content'] = array(
    'Big5' => '課程簡介',
    'GB2312' => '课程简介',
    'en' => 'Course Intro',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['ckta_n_limit'] = array(
    'Big5' => '正式生人數',
    'GB2312' => '正式生人数',
    'en' => 'Total<br>Enrolled<br>Students',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['ckta_a_limit'] = array(
    'Big5' => '旁聽生人數',
    'GB2312' => '旁听生人数',
    'en' => 'Total<br>Auditors',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['ckta_fair_grade'] = array(
    'Big5' => '及格成績 ',
    'GB2312' => '及格成绩',
    'en' => 'Passing<br>Grade',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['btn_disable'] = array(
    'Big5' => '未啟用',
    'GB2312' => '未启用',
    'en' => 'Disable',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['set_success'] = array(
    'Big5' => '設定成功',
    'GB2312' => '设定成功',
    'en' => 'Successfully set',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['fill_capacity'] = array(
    'Big5' => '請填寫容量',
    'GB2312' => '请填写容量',
    'en' => 'Please fill in the capacity',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['fill_numbers'] = array(
    'Big5' => '請填寫數字',
    'GB2312' => '请填写数字',
    'en' => 'Please fill in the numbers',
    'EUC-JP' => '',
    'user_define' => ''
);
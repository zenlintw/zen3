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
	require_once dirname(__FILE__) . '/exam_learn.php';

	$MSG['questionnaire_title'] = array(
		'Big5'			=> '問卷 / 投票',
		'GB2312'		=> '问卷 / 投票',
		'en'			=> 'Questionnaires / Polls',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['title2'] = array(
		'Big5'			=> '校務問卷',
		'GB2312'		=> '校务问卷',
		'en'			=> 'School Questionnaire',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['exam_type1'] = array(
		'Big5'			=> '平時問卷',
		'GB2312'		=> '平时问卷',
		'en'			=> 'Questionnaire',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['exam_type2'] = array(
		'Big5'			=> '平時問卷',
		'GB2312'		=> '平时问卷',
		'en'			=> 'Questionnaire',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['exam_type3'] = array(
		'Big5'			=> '正式投票',
		'GB2312'		=> '正式投票',
		'en'			=> 'Formal Poll',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['exam_type4'] = array(
		'Big5'			=> '線上問卷',
		'GB2312'		=> '线上问卷',
		'en'			=> 'Online Questionnaire',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['exam_type5'] = array(
		'Big5'			=> '愛上互動',
		'GB2312'		=> '爱上互动',
		'en'			=> 'Realtime Questionnaire',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['exam_type6'] = array(
		'Big5'			=> '訓前線上問卷',
		'GB2312'		=> '训前线上问卷',
		'en'			=> 'Pre-training online questionnaire',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['exam_type7'] = array(
		'Big5'			=> '訓後線上問卷',
		'GB2312'		=> '训后线上问卷',
		'en'			=> 'Post-training online questionnaire',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['exam_type8'] = array(
		'Big5'			=> '訓前問卷(只記結果)',
		'GB2312'		=> '训前问卷(只记结果)',
		'en'			=> 'Pre-training questionnaire (Results only)',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['exam_type9'] = array(
		'Big5'			=> '訓後問卷(只記結果)',
		'GB2312'		=> '训后问卷(只记结果)',
		'en'			=> 'Post-training questionnaire (Results only)',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['table_title5'] = array(
		'Big5'			=> '填寫<br>狀態',
		'GB2312'		=> '填写<br>状态',
		'en'			=> 'Attempts status',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['ready2test'] = array(
		'Big5'			=> '填寫問卷',
		'GB2312'		=> '填写问卷',
		'en'			=> 'Answer Questionnaire',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['table_title8'] = array(
		'Big5'			=> '續考',
		'GB2312'		=> '续考',
		'en'			=> 'Continue Questionnaire',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['table_title9'] = array(
		'Big5'			=> '查看結果',
		'GB2312'		=> '查看结果',
		'en'			=> 'Check Results',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['exam_over'] = array(
		'Big5'			=> '投票結束',
		'GB2312'		=> '投票结束',
		'en'			=> 'This vote is finished.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['exam_name'] = array(
		'Big5'			=> '問卷名稱：',
		'GB2312'		=> '问卷名称：',
		'en'			=> 'Questionnaire Name:',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['quit_exam'] = array(
		'Big5'			=> '放棄作答，結束問卷',
		'GB2312'		=> '放弃作答，结束问卷',
		'en'			=> 'Give up and exit questionnaire.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['terminate_exam'] = array(
		'Big5'			=> '結束問卷',
		'GB2312'		=> '结束问卷',
		'en'			=> 'Questionnaire Closed',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['enable_duration'] = array(
		'Big5'			=> '作答起訖日期與時間',
		'GB2312'		=> '作答起讫日期与时间',
		'en'			=> 'Questionnaire Period',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['modifiable'] = array(
		'Big5'			=> '是否可重繳',
		'GB2312'		=> '是否可重缴',
		'en'			=> 'Can be modified.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['can_modify'] = array(
		'Big5'			=> '可重繳',
		'GB2312'		=> '可重缴',
		'en'			=> 'Yes',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['cannot_modify'] = array(
		'Big5'			=> '不可重繳',
		'GB2312'		=> '不可重缴',
		'en'			=> 'No',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['exam_duration'] = array(
		'Big5'			=> '實施時間',
		'GB2312'		=> '实施时间',
		'en'			=> 'Total Time',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['count_type'] = array(
		'Big5'			=> '計分方式',
		'GB2312'		=> '计分方式',
		'en'			=> 'Calculating Formula',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['exam_times'] = array(
		'Big5'			=> '問卷次數',
		'GB2312'		=> '问卷次数',
		'en'			=> '# of attempts allowed',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['window_control1'] = array(
		'Big5'			=> '禁止切換至其它視窗',
		'GB2312'		=> '禁止切换至其它窗口',
		'en'			=> 'Switching windows not allowed',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['score_publish1'] = array(
		'Big5'			=> '作答完公布',
		'GB2312'		=> '作答完公布',
		'en'			=> 'Show score as questionaire delivered.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['score_publish2'] = array(
		'Big5'			=> '關閉問卷後公布',
		'GB2312'		=> '关闭问卷后公布',
		'en'			=> 'Show score as questionaire finished.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['score_publish_hint'] = array(
		'Big5'			=> '結果公佈日期',
		'GB2312'		=> '结果公布日期',
		'en'			=> 'Date on which answers are available',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['exam_info'] = array(
		'Big5'			=> '問卷資訊',
		'GB2312'		=> '问卷资讯',
		'en'			=> 'Questionnaire Info',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['prepare_to_exam'] = array(
		'Big5'			=> '準備進行問卷',
		'GB2312'		=> '准备进行问卷',
		'en'			=> 'Prepare to take a questionnaire.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['confirm_end_exam'] = array(
		'Big5'			=> '確定要繳卷，並結束問卷嗎？',
		'GB2312'		=> '确定要缴卷，并结束问卷吗？',
		'en'			=> 'Are you sure you want to submit and exit?',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['homework_content'] = array(
		'Big5'			=> '問卷內容',
		'GB2312'		=> '问卷内容',
		'en'			=> 'Content',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['from'] = array(
		'Big5'			=> '從 ',
		'GB2312'		=> '从',
		'en'			=> 'From:',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['to2'] = array(
		'Big5'			=> '到 ',
		'GB2312'		=> '到',
		'en'			=> 'To:',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['ref_url'] = array(
		'Big5'			=> '參考網址：',
		'GB2312'		=> '参考网址：',
		'en'			=> 'reference url',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['ans_files'] = array(
		'Big5'			=> '解答附檔：',
		'GB2312'		=> '解答附件档：',
		'en'			=> 'attach files',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['detail_answer'] = array(
		'Big5'			=> '詳解：',
		'GB2312'		=> '详解：',
		'en'			=> 'detail answer',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['cancel_exam'] = array(
		'Big5'			=> '不作答離開',
		'GB2312'		=> '不作答离开',
		'en'			=> 'Exit without answer',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);
	
	$MSG['attachement_msg'] = array(
		'Big5'			=> '每個檔案限%MIN_SIZE%，總和不得超過%MAX_SIZE%',
		'GB2312'		=> '每个档案限%MIN_SIZE%，总合不得超过%MAX_SIZE%',
		'en'			=> 'Each file cannot exceed %MIN_SIZE% , No more than %MAX_SIZE% in total.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);
	
	$MSG['school'] = array(
		'Big5'			=> '學校',
		'GB2312'		=> '学校',
		'en'			=> 'school',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['course'] = array(
		'Big5'			=> '課程',
		'GB2312'		=> '课程',
		'en'			=> 'course',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);
	
	$MSG['quota_full'] = array(
		'Big5'			=> '你的%TYPE%可使用的網路空間已滿，無法再上傳附檔。請清理%TYPE%中某些非必要的檔案，空出可用空間方可繼續上傳。或者聯絡管理員。',
		'GB2312'		=> '你的%TYPE%可使用的网络空间已满，无法再上传附档。请清理%TYPE%中某些非必要的档案，空出可用空间方可继续上传。或者联络管理员。',
		'en'			=> '%TYPE% quota is full',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['havent submitted yet'] = array(
		'Big5'			=> '未填寫',
		'GB2312'		=> '未填写',
		'en'			=> "haven't submitted yet",
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['have submitted'] = array(
		'Big5'			=> '已填寫',
		'GB2312'		=> '已填写',
		'en'			=> "have submitted",
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['anonymous or not'] = array(
		'Big5'			=> '是否記名',
		'GB2312'		=> '是否记名',
		'en'			=> 'anonymous or not',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['anonymous'] = array(
		'Big5'			=> '不記名',
		'GB2312'		=> '不记名',
		'en'			=> 'anonymous',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['named'] = array(
		'Big5'			=> '記名',
		'GB2312'		=> '记名',
		'en'			=> 'named',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['not for guest'] = array(
		'Big5'			=> '無此問卷，或此問卷不是開放型問卷。',
		'GB2312'		=> '无此问卷，或此问卷不是开放型问卷。',
		'en'			=> 'There is not any questionnaire for guest.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['not yet begun or closed'] = array(
		'Big5'			=> '此問卷尚未開始或已結束填寫。',
		'GB2312'		=> '此问卷尚未开始或已结束填写。',
		'en'			=> 'The questionnaire has not yet begun or closed.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['incorrect url'] = array(
		'Big5'			=> '不正確的 URL。',
		'GB2312'		=> '不正确的 URL。',
		'en'			=> 'incorrect url.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);
	
	$MSG['access mode'] = array(
		'Big5'			=> '問卷類型',
		'GB2312'		=> '问卷类型',
		'en'			=> 'Access Mode',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['public access tip'] = array(
		'Big5'			=> '開放型問卷 (URL 可供任何人直連進入填寫)',
		'GB2312'		=> '开放型问卷 (URL 可供任何人直连进入填写)',
		'en'			=> 'public access immediately for anyone (login is unnecessary).',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);
	
	$MSG['msg_exam_close'] = array(
		'Big5'			=> '問卷不在開放時間中',
		'GB2312'		=> '问卷不在开放时间中',
		'en'			=> 'The questionnaire is closed',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);
	
	$MSG['msg_upload_file_disabled'] = array(
		'Big5'			=> 'Mobile Safari 不支援檔案上傳，請使用其他瀏覽器上傳檔案。',
		'GB2312'		=> 'Mobile Safari 不支援档案上传，请使用其他浏览器上传档案。',
		'en'			=> 'Mobile Safari not support file upload.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);
    
    /* Moocs lang */
    $MSG['check_result'] = array(
        'Big5'			=> '查看結果',
        'GB2312'		=> '查看结果',
        'en'			=> 'Check Results',
        'EUC-JP'		=> '',
        'user_define'	=> ''
    );

    $MSG['modify_questionnaire'] = array(
        'Big5'			=> '修改問卷',
        'GB2312'		=> '修改问卷',
        'en'			=> 'Modify the questionnaire',
        'EUC-JP'		=> '',
        'user_define'	=> ''
    );

    $MSG['no_data'] = array(
        'Big5'			=> '目前尚無問卷',
        'GB2312'		=> '目前尚无问卷',
        'en'			=> 'There is no questionnaire.',
        'EUC-JP'		=> '',
        'user_define'	=> ''
    );
    
    $MSG['file_not_complete'] = array(
    'Big5' => '上傳附件未完成！',
    'GB2312' => '上传附件未完成！',
    'en' => 'The upload attachment is not complete!',
    'EUC-JP' => '',
    'user_define' => ''
);
?>

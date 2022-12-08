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

	$MSG['title'] = array(
		'Big5'			=> '信件標題',
		'GB2312'		=> '信件标题',
		'en'			=> 'Subject',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['message1'] = array(
		'Big5'			=> '必填，限100字元',
		'GB2312'		=> '必填，限100字符',
		'en'			=> 'Required. No more than 100 characters.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['content'] = array(
		'Big5'			=> '內容',
		'GB2312'		=> '内容',
		'en'			=> 'Content',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['message3'] = array(
		'Big5'			=> '左方信件範例內容中的<br />
		                %SCHOOL_NAME%：學校名稱，<br/>
		                %SCHOOL_HOST%：學校網址，<br />
		                %REAL_NAME%：申請者姓名，<br/>
		                %USERNAME%：申請者帳號。<br/>
		                信件寄出後系統會將正確的資料填入，請勿將這些符號刪除。',
		'GB2312'		=> '左方信件范例内容中的<br />%SCHOOL_NAME%：学校名称，<br/>%SCHOOL_HOST%：学校网址，<br />%REAL_NAME%：申请者姓名，<br/>%USERNAME%：申请者帐号。<br/>信件寄出后系统会将正确的资料填入，请勿将这些符号删除。',
		'en'			=> 'The following content,<br />%SCHOOL_NAME%: School Name<br/>%SCHOOL_HOST%: School URL<br />%REAL_NAME%: User&#039;s real name<br/>%USERNAME%: Username<br/>will be provided by system. Please do not delete those symbols.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['message4'] = array(
		'Big5'			=> '左方信件範例內容中的<br />
		                %SCHOOL_NAME%：學校名稱，<br/>
		                %SCHOOL_HOST%：學校網址，<br />
		                %REAL_NAME%：申請者姓名，<br/>
		                %USERNAME%：申請者帳號。<br/>
		                %PASSWORD%：申請者密碼。<br/>
		                信件寄出後系統會將正確的資料填入，<br>
		                請勿將這些符號刪除。',
		'GB2312'		=> '左方信件范例内容中的<br />%SCHOOL_NAME%：学校名称，<br/>%SCHOOL_HOST%：学校网址，<br />%REAL_NAME%：申请者姓名，<br/>%USERNAME%：申请者帐号。<br/>%PASSWORD%：申请者密码。<br/>信件寄出后系统会将正确的资料填入，<br>请勿将这些符号删除。',
		'en'			=> 'The following content,<br />%SCHOOL_NAME%: School Name<br/>%SCHOOL_HOST%: School URL<br />%REAL_NAME%: User&#039;s real name<br/>%USERNAME%: Username<br/>will be provided by system. Please do not delete those symbols.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['att_file'] = array(
		'Big5'			=> '附檔',
		'GB2312'		=> '附件档',
		'en'			=> 'Attachment',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['del_att_file'] = array(
		'Big5'			=> '刪除附檔',
		'GB2312'		=> '删除附件档',
		'en'			=> 'Delete Attachment',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['message2'] = array(
		'Big5'			=> '若您有多個檔案要附帶，請先按「更多檔案」，再按瀏覽。
					   單一上傳檔案size不得超過：%MIN_SIZE%,總上傳檔案size不得超過：%MAX_SIZE%
					   ',
		'GB2312'		=> '若您有多个档案要附带，请先按“更多档案”，再按浏览。单一上传档案size不得超过：%MIN_SIZE%,总上传档案size不得超过：%MAX_SIZE%',
		'en'			=> 'If you want to attach more files, please click More Attachments.The size of each upload cannot exceed %MIN_SIZE%; the size of total uploads cannot exceed %MAX_SIZE%.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['more'] = array(
		'Big5'			=> '更多附檔',
		'GB2312'		=> '更多附件档',
		'en'			=> 'More Attachments',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['sure'] = array(
		'Big5'			=> '確定儲存',
		'GB2312'		=> '确定保存',
		'en'			=> 'Save',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['edit_allow'] = array(
		'Big5'			=> '編輯核可通知信件',
		'GB2312'		=> '编辑核可通知信件',
		'en'			=> 'Edit Accept Email',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['edit_forbid'] = array(
		'Big5'			=> '編輯不核可通知信件',
		'GB2312'		=> '编辑不核可通知信件',
		'en'			=> 'Edit Deny Email',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['edit_register_mail'] = array(
		'Big5'			=> '編輯帳號通知信 ',
		'GB2312'		=> '编辑帐号通知信',
		'en'			=> 'Edit Account Notice Email',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['return_register'] = array(
		'Big5'			=> '回新增帳號 ',
		'GB2312'		=> '回新增帐号',
		'en'			=> 'Back to Add Accounts',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['return_verify'] = array(
		'Big5'			=> '回審核帳號 ',
		'GB2312'		=> '回审核帐号',
		'en'			=> 'Back to Account Review',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['cancel'] = array(
		'Big5'			=> '取消 ',
		'GB2312'		=> '取消',
		'en'			=> 'Cancel',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['create_serial_account'] = array(
		'Big5'			=> '新增連續帳號',
		'GB2312'		=> '新增连续帐号',
		'en'			=> 'Create Serial Account',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['create_discrete_account'] = array(
		'Big5'			=> '新增不規則帳號',
		'GB2312'		=> '新增不规则帐号',
		'en'			=> 'Create Discrete Account',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['import_account'] = array(
		'Big5'			=> '匯入帳號',
		'GB2312'		=> '导入帐号',
		'en'			=> 'Import Account',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['add_account_subject'] = array(
		'Big5'			=> '網路學園帳號啟用通知信',
		'GB2312'		=> '网络学园帐号启用通知信',
		'en'			=> 'Your E-campus Account',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['add_account_body'] = array(
		'Big5'			=> '<html><head></head><body><br />
				<p>==================== %SCHOOL_NAME% ============ </p>
				<p>============ http://%SCHOOL_HOST% ============ </p>
				<p>Hi！親愛的學員您好： 恭喜您已成為 %SCHOOL_NAME% 的學員。 </p>
				<p>遠距教學系統網址為 %SCHOOL_HOST%，您將可以隨時透過網路連線 ，不管在什麼時間、什麼地點都可以進行課程學習、做課程 討論、繳交作業......。
				您的學員帳號密碼如下，請謹慎保 存以免無法登入學習。 </p>
				<p>帳號：%USERNAME% </p>
				<p>密碼：%PASSWORD%</p>
				<p>網址：http://%SCHOOL_HOST% </p>
				<p>點選「學生」身分後按「登入」，即可進入遠距教學系統學習 各項教材。 </p>
				<p>============ http://%SCHOOL_HOST% ============ </p>
				<p>==================== %SCHOOL_NAME% ==========</p>
				</body>
				</html>',
		'GB2312'		=> '<html><head></head><body><br /><p>==================== %SCHOOL_NAME% ============ </p><p>============ http://%SCHOOL_HOST% ============ </p><p>Hi！亲爱的学员您好： 恭喜您已成为 %SCHOOL_NAME% 的学员。 </p><p>远距教学系统网址为 %SCHOOL_HOST%，您将可以随时透过网络连线 ，不管在什么时间、什么地点都可以进行课程学习、做课程 讨论、缴交作业......。您的学员帐号密码如下，请谨慎保 存以免无法登入学习。 </p><p>帐号：%USERNAME% </p><p>密码：%PASSWORD%</p><p>网址：http://%SCHOOL_HOST% </p><p>点选“学生”身份后按“登入”，即可进入远距教学系统学习 各项教材。 </p><p>============ http://%SCHOOL_HOST% ============ </p><p>==================== %SCHOOL_NAME% ==========</p></body></html>',
		'en'			=> '<html><head></head><body><br /><p>==================== %SCHOOL_NAME% ============ </p><p>============ http://%SCHOOL_HOST% ============ </p><p>Dear Student:</p><p>Congratulations on becoming a member of %SCHOOL_NAME% . </p><p>The URL for this e-learning site is %SCHOOL_HOST%. Your learning activities, such as reading, discussing, submitting assignments, etc., can take place anytime, anywhere via the Internet. Your student account and password are listed below. Please keep them with care for future reference.</p><p>Username:%USERNAME% </p><p>Password:%PASSWORD%</p><p>URL:http://%SCHOOL_HOST% </p><p>To access learning resources on this site, just select Student and then click Login. </p><p>============ http://%SCHOOL_HOST% ============ </p><p>==================== %SCHOOL_NAME% ==========</p></body></html>',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['verify_account_subject'] = array(
		'Big5'			=> '註冊成功通知信',
		'GB2312'		=> '注册成功通知信',
		'en'			=> 'Registration Confirmation Email',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['verify_account_body'] = array(
		'Big5'			=> '<html><head></head><body>
							<p>============== %SCHOOL_NAME% =============</p>
							<p>============ <a href="http://%school_host/">http://%SCHOOL_HOST%</a> ============ </p><p>Hi ！ ，%REAL_NAME%：</p>
							<p>恭喜您已經在 %SCHOOL_NAME% 註冊成功，請依以下說明進行下一步動作，以成為我們正式的學員。</p>
							<p>您的帳號為 【%USERNAME%】</p>
							<p>建議您將此信妥善備存，並請您務必牢記您的學員帳號與密碼。</p>
							<p>請立即到 %SCHOOL_HOST% 輸入帳號密碼。</p>
							<p>並依照網頁上的指引，填寫您的個人基本資料。</p>
							<p></body></html>',
		'GB2312'		=> '<html><head></head><body><p>============== %SCHOOL_NAME% =============</p><p>============ <a href=http://%school_host/>http://%SCHOOL_HOST%</a> ============ </p><p>Hi ！ ，%REAL_NAME%：</p><p>恭喜您已经在 %SCHOOL_NAME% 注册成功，请按以下说明进行下一步动作，以成为我们正式的学员。</p><p>您的帐号为 【%USERNAME%】</p><p>建议您将此信妥善备存，并请您务必牢记您的学员帐号与密码。</p><p>请立即到 %SCHOOL_HOST% 输入帐号密码。</p><p>并按照网页上的指引，填写您的个人基本资料。</p><p></body></html>',
		'en'			=> '<html><head></head><body><p>============== %SCHOOL_NAME% =============</p><p>============ <a href=http://%school_host/>http://%SCHOOL_HOST%</a> ============ </p><p>Hi ! ,%REAL_NAME%:</p><p>Congratulations! Your registration in %SCHOOL_NAME% has been accepted . Please take the final step to become formally registered. </p><p>Your username is [%USERNAME%]</p><p>We suggest that you keep this mail with care and be sure to memorize your student account and password.</p><p>Please enter your account and password in %SCHOOL_HOST% ,</p><p>and complete your profile as instructed.</p><p></body></html>',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['fail_account_subject'] = array(
		'Big5'			=> '註冊結果通知信',
		'GB2312'		=> '注册结果通知信',
		'en'			=> 'Your Registration Result',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['fail_account_body'] = array(
		'Big5'			=> '<html><head></head><body>
							<p>============== %SCHOOL_NAME% =============</p>
							<p>============ <a href="http://%school_host/">http://%SCHOOL_HOST%</a> ============ </p>
							<p>Hi ！ ，%REAL_NAME%：</p>
							<p>您的帳號【%USERNAME%】在 %SCHOOL_NAME% 註冊的帳號沒有經過管理者的許可，</p>
							<p>可能是因為您的資料不齊全，或某些條件不符合規定。</p>
							<p>若有任何問題，請與管理人員聯絡。</p>
							<p>============== %SCHOOL_NAME% =============</p>
							<p>============ <a href="http://%school_host/">http://%SCHOOL_HOST%</a> ============ </p>
							</body></html>',
		'GB2312'		=> '<html><head></head><body><p>============== %SCHOOL_NAME% =============</p><p>============ <a href=http://%school_host/>http://%SCHOOL_HOST%</a> ============ </p><p>Hi ！ ，%REAL_NAME%：</p><p>您的帐号【%USERNAME%】在 %SCHOOL_NAME% 注册的帐号没有经过管理者的许可，</p><p>可能是因为您的资料不齐全，或某些条件不符合规定。</p><p>若有任何问题，请与管理人员联络。</p><p>============== %SCHOOL_NAME% =============</p><p>============ <a href=http://%school_host/>http://%SCHOOL_HOST%</a> ============ </p></body></html>',
		'en'			=> '<html><head></head><body><p>============== %SCHOOL_NAME% =============</p><p>============ <a href=http://%school_host/>http://%SCHOOL_HOST%</a> ============ </p><p>Dear %REAL_NAME%:</p><p>The account,[%USERNAME%], you signed up at %SCHOOL_NAME% has been denied by system Administrator.</p><p>The problem may be caused by incomplete data.</p><p>If you have any questions, please contact system Administrator.</p><p>============== %SCHOOL_NAME% =============</p><p>============ <a href=http://%school_host/>http://%SCHOOL_HOST%</a> ============ </p></body></html>',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['add_success'] = array(
		'Big5'			=> '儲存成功 ',
		'GB2312'		=> '保存成功',
		'en'			=> 'YES',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);
	
	$MSG['upload_file_error'] = array(
	    'Big5' => '上傳失敗，檔案大小超過限制！',
	    'GB2312' => '上传失败，档案大小超过限制！',
	    'en' => 'Upload file(s) error. File size exceeds limit!',
	    'EUC-JP' => '',
	    'user_define' => ''
	);

?>

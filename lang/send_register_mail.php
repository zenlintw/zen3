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
		'Big5'			=> '寄信發送結果',
		'GB2312'		=> '寄信发送结果',
		'en'			=> 'Sent Result',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['user_account'] = array(
		'Big5'			=> '帳號',
		'GB2312'		=> '帐号',
		'en'			=> 'Username',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['password'] = array(
		'Big5'			=> '密碼 ',
		'GB2312'		=> '密码',
		'en'			=> 'Password',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['status2'] = array(
		'Big5'			=> '狀態',
		'GB2312'		=> '状态',
		'en'			=> 'Status',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['title22'] = array(
		'Big5'			=> '寄出 ',
		'GB2312'		=> '寄出',
		'en'			=> 'Sent',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['title23'] = array(
		'Big5'			=> '未寄出 ',
		'GB2312'		=> '未寄出',
		'en'			=> 'Not sent',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['return_register'] = array(
		'Big5'			=> '回新增連續帳號 ',
		'GB2312'		=> '回新增连续帐号',
		'en'			=> 'Back to Add Serial Account',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['return_register2'] = array(
		'Big5'			=> '回新增不規則帳號 ',
		'GB2312'		=> '回新增不规则帐号',
		'en'			=> 'Back to Add Discrete Account',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['send_to'] = array(
		'Big5'			=> '已寄給: ',
		'GB2312'		=> '已寄给:',
		'en'			=> 'Sent to:',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['add_account_subject'] = array(
		'Big5'			=> '網路學園帳號啟用通知信',
		'GB2312'		=> '网络学园帐号启用通知信',
		'en'			=> 'E-Learning Campus Account Notification',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['add_account_subject_for_manager'] = array(
		'Big5'			=> '學員帳號備存',
		'GB2312'		=> '学员帐号备存',
		'en'			=> 'Student account backup.',
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
		'en'			=> '<html><head></head><body><br /><p>==================== %SCHOOL_NAME% ============ </p><p>============ http://%SCHOOL_HOST% ============ </p><p>Dear Student: Congratulations on becoming a member of %SCHOOL_NAME% . </p><p>The URL for this e-Learning campus is %SCHOOL_HOST%. Your learning activities, such as reading, discussing, submitting assignments, etc., cantake place anytime, anywhere via the Internet. Your student account and password are listed below. Please keep them with care for future reference.</p><p>Username:%USERNAME% </p><p>Password:%PASSWORD%</p><p>URL:http://%SCHOOL_HOST% </p><p>To access learning resources on this site, just select Student and then click Login. </p><p>============ http://%SCHOOL_HOST% ============ </p><p>==================== %SCHOOL_NAME% ==========</p></body></html>',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

?>

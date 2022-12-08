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

	$MSG['differ_id'] = array(
		'Big5'			=> '兩個密碼不相同！',
		'GB2312'		=> '两个密码不相同！',
		'en'			=> 'The two passwords are not the same!',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['shortId'] = array(
		'Big5'			=> '密碼太短！',
		'GB2312'		=> '密码太短！',
		'en'			=> 'Password too short!',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['duplicateId'] = array(
		'Big5'			=> '密碼不能使用重複數字或字元！',
		'GB2312'		=> '密码不能使用重复数字或字符！',
		'en'			=> 'Same character cannot be used twice in a password.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['numberId'] = array(
		'Big5'			=> '密碼不能使用數字序列！',
		'GB2312'		=> '密码不能使用数字序列！',
		'en'			=> 'Passwords do not allow continuous numbers.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['errorId'] = array(
		'Big5'			=> '密碼不能使用字母序列！',
		'GB2312'		=> '密码不能使用字母序列！',
		'en'			=> 'Passwords do not allow continuous letters.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['dummyId'] = array(
		'Big5'			=> '人有時候會笨得可怕！\n請不要用範例當密碼。',
		'GB2312'		=> '人有时候会笨得可怕！\n请不要用范例当密码。',
		'en'			=> 'Examples cannot be used as your password.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['title'] = array(
		'Big5'			=> '危&nbsp; 險&nbsp; 警&nbsp; 告',
		'GB2312'		=> '危&nbsp; 险&nbsp; 警&nbsp; 告',
		'en'			=> 'Warning!',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['line1'] = array(
		'Big5'			=> '您會看到這個畫面，是因為您的',
		'GB2312'		=> '您会看到这个画面，是因为您的',
		'en'			=> 'Your password is too simple.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['line2'] = array(
		'Big5'			=> '密碼過於簡單。',
		'GB2312'		=> '密码过于简单。',
		'en'			=> 'Your password is too simple.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['line3'] = array(
		'Big5'			=> '為維護您的資料保密及系統繼續穩定提供您服務，請先修改您的密碼。密碼規範如下：',
		'GB2312'		=> '为维护您的资料保密及系统继续稳定提供您服务，请先修改您的密码。密码规范如下：',
		'en'			=> 'To ensure the security of your data, please change your password first. The rules are as follows:',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['example1'] = array(
		'Big5'			=> '最短 6 個字元，最長不限，中間可用空白，但前後空白會被忽略。',
		'GB2312'		=> '最短 6 个字符，最长不限，中间可用空白，但前后空白会被忽略。',
		'en'			=> 'No less than 6 characters. Space is allowed, but space in the front and end will be ignored.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['example2'] = array(
		'Big5'			=> '不能與帳號相同或是用帳號的反向字串',
		'GB2312'		=> '不能与帐号相同或是用帐号的反向字串',
		'en'			=> 'Password can neither be the same as username, nor its reverse string.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['example3'] = array(
		'Big5'			=> '不能用重複的數字或字母 (如：111111, aaaaaa, BBBBBB)',
		'GB2312'		=> '不能用重复的数字或字母 (如：111111, aaaaaa, BBBBBB)',
		'en'			=> 'No characters can be used more than once. For example,111111, aaaaaa, BBBBBB.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['example4'] = array(
		'Big5'			=> '不能使用序列數字或字母，反向亦不行，但大小寫交錯可。',
		'GB2312'		=> '不能使用序列数字或字母，反向亦不行，但大小写交错可。',
		'en'			=> 'Continuous numbers or letters are not accepted. Both lower and upper cases are allowed.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['example5'] = array(
		'Big5'			=> '(如：123456, abcdef, zyxwvu, 987654 不可；aBcDeF, NmLkJi 可。',
		'GB2312'		=> '(如：123456, abcdef, zyxwvu, 987654 不可；aBcDeF, NmLkJi 可。',
		'en'			=> 'For example, 123456, abcdef, zyxwvu, and 987654 are not accepted; aBcDeF and NmLkJi are accepted.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['suggest'] = array(
		'Big5'			=> '建議使用：',
		'GB2312'		=> '建议使用：',
		'en'			=> 'Suggestions:',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['suggest1'] = array(
		'Big5'			=> '數字、英文字母交錯，有標點符號更好',
		'GB2312'		=> '数字、英文字母交错，有标点符号更好',
		'en'			=> 'Mix numbers and letters. It&#039;s even better to include punctuations.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['suggest2'] = array(
		'Big5'			=> '有人喜歡用帳號、生日，建議中間用個標點符號連接，保全性即大增',
		'GB2312'		=> '有人喜欢用帐号、生日，建议中间用个标点符号连接，安全性即大增',
		'en'			=> 'If you use username or birthday as your password, it will be safter to add punctuations in the middle.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['suggest3'] = array(
		'Big5'			=> '(如：',
		'GB2312'		=> '(如：',
		'en'			=> 'For example:',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['suggest5'] = array(
		'Big5'			=> '、',
		'GB2312'		=> '、',
		'en'			=> '',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['suggest7'] = array(
		'Big5'			=> '既易記又具高安全性。)',
		'GB2312'		=> '既易记又具高安全性。)',
		'en'			=> 'Easy to remember and safe!',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['suggest8'] = array(
		'Big5'			=> '勿單純使用特別數字當密碼，如生日、身份證號',
		'GB2312'		=> '勿单纯使用特别数字当密码，如生日、身份证号',
		'en'			=> 'Do not just use such special numbers  as your birthday or ID No. as your password.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['newId'] = array(
		'Big5'			=> '重設新密碼：',
		'GB2312'		=> '重设新密码：',
		'en'			=> 'Reset password:',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['enterId'] = array(
		'Big5'			=> '再輸入一次：',
		'GB2312'		=> '再输入一次：',
		'en'			=> 'Please re-type password:',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['save'] = array(
		'Big5'			=> '儲存',
		'GB2312'		=> '保存',
		'en'			=> 'Save',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['reset'] = array(
		'Big5'			=> '重來',
		'GB2312'		=> '重来',
		'en'			=> 'Retry',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['loginAgain'] = array(
		'Big5'			=> '密碼依然太脆弱！請再設定一次。',
		'GB2312'		=> '密码依然太脆弱！请再设定一次。',
		'en'			=> 'The password is still not safe enough. Please reset!',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

?>

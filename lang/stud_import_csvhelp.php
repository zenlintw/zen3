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
		'Big5'			=> 'CSV範例說明',
		'GB2312'		=> 'CSV范例说明',
		'en'			=> 'CSV Notes',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['btn_close'] = array(
		'Big5'			=> '關閉視窗',
		'GB2312'		=> '关闭视窗',
		'en'			=> 'Close',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['td_head'] = array(
		'Big5'			=> 'csv檔案格式，每一行代表一筆資料(一個學員的資料)，不同欄位的資料用逗號隔開。',
		'GB2312'		=> 'csv档案格式，每一行代表一笔资料(一个学员的资料)，不同栏位的资料用逗号隔开。',
		'en'			=> 'In csv format, each line stands for a student&#039;s data, within which fields are separated by commas.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['td_example'] = array(
		'Big5'			=> '範例',
		'GB2312'		=> '范例',
		'en'			=> 'Examples',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['td_example2'] = array(
		'Big5'			=> 'u0001,李,小芳,F,password,u0001@sun.net,高雄市大條路100號,1975-02-17,Big5,研發部<br>
						u0002,戴,小雯,F,password,u0002@sun.net,高雄市大條路101號,1966-12-13,GB2312,財務部<br>
						u0003,林,阿庭,M,password,u0003@sun.net,高雄市大條路102號,1972-12-06,en,研發部<br>
						u0004,林,雪莉,F,password,u0004@sun.net,高雄市大條路103號,1978-03-01,EUC-JP,專案部<br>',
		'GB2312'		=> 'u0001,李,小芳,F,password,u0001@sun.net,高雄市大条路100号,1975-02-17,Big5,研发部<br>
						u0002,戴,小雯,F,password,u0002@sun.net,高雄市大条路101号,1966-12-13,GB2312,财务部<br>
						u0003,林,阿庭,M,password,u0003@sun.net,高雄市大条路102号,1972-12-06,en,研发部<br>
						u0004,林,雪莉,F,password,u0004@sun.net,高雄市大条路103号,1978-03-01,EUC-JP,专案部<br>',
		'en'			=> 'u0001,Lee,Ann,F,password,u0002@sun.net,Boston,1966-12-13,GB2312,R & D department<br>u0002,Wright,Richard,M,password,u0002@sun.net,LA,1970-07-08,en,R & D department<br>u0003,Taylor,Mary,F,password,u0002@sun.net,New York,1922-01-05,EUC-JP,R & D department<br>',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['td_conviction'] = array(
		'Big5'			=> '說明',
		'GB2312'		=> '说明',
		'en'			=> 'Notes',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['td_conviction2'] = array(
		'Big5'			=> '<ol><li>
						可匯入的資料包括：<br>
						帳號,姓名,性別,生日,身份證號或護照,電子信箱,個人網頁,行動電話<br>
 						電話 (家),傳真 (家),地址 (家),電話 (公司),傳真 (公司),地址 (公司)<br>
 						公司或學校,部門或系所,職稱<br>
						</li>
						<li>
						範例解說： 帳號,姓氏,名字,性別,密碼,email,地址,生日,慣用語系,啟用帳號
						以上各欄位順序可以調換，但每欄之間要以逗點隔開。
						</li></ol>',
		'GB2312'		=> '<ol><li>
						可汇入的资料包括：<br>
						帐号,姓名,性别,生日,身份证号或护照,电子信箱,个人网页,行动电话<br>
 						电话 (家),传真 (家),地址 (家),电话 (公司),传真 (公司),地址 (公司)<br>
 						公司或学校,部门或系所,职称<br>
						</li>
						<li>
						范例解说： 帐号,姓氏,名字,性别,密码,email,地址,生日,惯用语系,启用帐号
						以上各栏位顺序可以调换，但每栏之间要以逗点隔开。
						</li></ol>',
		'en'			=> '<ol><li>Data that can be imported include:<br>Username, Name, Gender, Birthday, ID# or Passport#, Email, Homepage, Mobile,<br>Phone(H), Fax(H), Address(H), Phone(O), Fax(O), Address(O),<br>Company or School, Department, and Job Title.<br></li><li>Note: Fields such as Username, Last Name, First Name, Password, Email, Address, Birthday, and Language can be switched, but there must be commas between fields.</li></ol>',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['td_comment'] = array(
		'Big5'			=> '備註',
		'GB2312'		=> '备注',
		'en'			=> 'Remarks',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['td_comment2'] = array(
		'Big5'			=> '<ol>
						<li>
							帳號：必須是英文或數字。中間可夾雜底線_。不允許使用底線以外的符號或者中文字。 <br>
							不得新增兩個一模一樣的帳號。
						</li>
						<li>
							密碼：建議使用6-12個字元。若無密碼，可使用「密碼由系統產生」。
						</li>
						<li>
							姓氏與名字最好分開。
						</li>
						<li>
							帳號開啟：Y 代表此帳號可使用, N代表此帳號不可使用。
						</li>
						<li>
							性別：以F代表女士，M代表男士。
						</li>
						<li>
							生日、到職日、離職日...等日期格式：yyyy-mm-dd。
						</li>
						<li>
							電話號碼：無論是公司電話號碼或家中電話號碼，均只能輸入數字，最少6碼，最多20碼。
						</li>
						<li>
							傳真號碼：無論是公司傳真號碼或家中傳真號碼，均只能輸入數字，最少6碼，最多20碼。
						</li>
						<li>
						慣用語系：Big5代表正體中文，GB2312代表簡體中文，en代表英文，<br>
						EUC_JP代表日文，user_defind代表使用者自訂的語系。如果有開啟上述語系。
						</li>
						<li>
							其餘未特別說明之項目，則不限資料格式 。
						</li></ol>',
		'GB2312'		=> '<ol>
						<li>
							帐号：必须是英文或数字。中间可夹杂底线_。不允许使用底线以外的符号或者中文字。 <br>
							不得新增两个一模一样的帐号。
						</li>
						<li>
							密码：建议使用6-12个字元。若无密码，可使用“密码由系统产生”。
						</li>
						<li>
							姓氏与名字最好分开。
						</li>
						<li>
							帐号开启：Y 代表此帐号可使用, N代表此帐号不可使用。
						</li>
						<li>
							性别：以F代表女士，M代表男士。
						</li>
						<li>
							生日、到职日、离职日...等日期格式：yyyy-mm-dd。
						</li>
						<li>
							电话号码：无论是公司电话号码或家中电话号码，均只能输入数字，最少6码，最多20码。
						</li>
						<li>
							传真号码：无论是公司传真号码或家中传真号码，均只能输入数字，最少6码，最多20码。
						</li>
						<li>
						惯用语系：Big5代表繁体中文，GB2312代表简体中文，en代表英文，<br>
						EUC_JP代表日文，user_defind代表使用者自订的语系。如果有开启上述语系。
						</li>
						<li>
							其余未特别说明之项目，则不限资料格式 。
						</li></ol>',
		'en'			=> '<ol><li>Username: Only Roman characters,numbers, and underscores are allowed.<br>Usernames cannot repeat.</li><li>Password: 6-12 characters are suggested. You can enter your own password or have the system generate one for you.</li><li>First name and last name should be separated.</li><li>Account Availability: Y means this account can be used, and N means the opposite.</li><li>Gender: F for females, and M for males.</li><li>Date format: yyyy-mm-dd</li><li>Phone: Numbers only. No less than 6 digits and no more than 20.</li><li>Fax: Numbers only. No less than 6 digits and no more than 20.</li><li>Language: Big5 for Traditional Chinese; GB2312 for Simplified Chinese; en for English;<br>EUC-JP for Japanese, and User_Define for other languaes.</li><li>Other fields do not have restrictions on formats.</li></ol>',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

?>

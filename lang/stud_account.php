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
    'Big5' => '新增帳號',
    'GB2312' => '新增帐号',
    'en' => 'Add Account',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['delete_account'] = array(
    'Big5' => '刪除帳號',
    'GB2312' => '删除帐号',
    'en' => 'Delete Account',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['create_serial_account'] = array(
    'Big5' => '新增連續帳號',
    'GB2312' => '新增连续帐号',
    'en' => 'Add Serial Account',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['create_discrete_account'] = array(
    'Big5' => '新增不規則帳號',
    'GB2312' => '新增不规则帐号',
    'en' => 'Add Discrete Account',
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

$MSG['create_help01'] = array(
    'Big5' => '範例：<br />
例如要建立新帳號 m89103001 ～ m89103050<br />
則上述欄位可填『m』『89103001』『89103050』『』『8』<br />
或者可填　　　『m89103』『1』『50』『』『3』<br />
帳號最長%MAX%字，最短%MIN%字，除了第一字元為字母、數字外,其餘的為字母、數字、底線、減號,<br />
-_ 只能出現一次, 且不可以出現在最後一個字元，大小寫有別',
    'GB2312' => '范例：<br />
例如要建立新帐号 m89103001 ～ m89103050<br />
则上述栏位可填‘m’‘89103001’‘89103050’‘’‘8’<br />
或者可填　　　‘m89103’‘1’‘50’‘’‘3’<br />
帐号最长%MAX%字，最短%MIN%字，除了第一字元为字母、数字外,其余的为字母、数字、底线、减号,<br />
-_ 只能出现一次, 且不可以出现在最后一个字元，大小写有别',
    'en' => 'Example:<br />If you want to create accounts m89103001 ~ m89103050,<br />you may fill in [m][89103001][89103050][][8]<br />or              [m89103][1][50][][3].<br />Accounts cannot contain more than 20 characters or less than 2 characters.<br /> The first character should be a Roman character or a number. The rest canbe letters, numbers, underscores, or minus signs.<br />Minus signs and underscores can only appear once. Username cannot end with a minus sign or an underscore. Capitalization matters!',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['del_help01'] = array(
    'Big5' => '範例：<br />
例如要刪除的帳號 m89103001 ～ m89103050<br />
則上述欄位可填『m』『89103001』『89103050』『』『8』<br />
或者可填　　　『m89103』『1』『50』『』『3』<br />
帳號最長20字，最短2字，除了第一字元為字母、數字外,其餘的為字母、數字、底線、減號,<br />
-_ 只能出現一次, 且不可以出現在最後一個字元，大小寫有別',
    'GB2312' => '范例：<br />
例如要删除的帐号 m89103001 ～ m89103050<br />
则上述栏位可填‘m’‘89103001’‘89103050’‘’‘8’<br />
或者可填　　　‘m89103’‘1’‘50’‘’‘3’<br />
帐号最长20字，最短2字，除了第一字元为字母、数字外,其余的为字母、数字、底线、减号,<br />
-_ 只能出现一次, 且不可以出现在最后一个字元，大小写有别',
    'en' => 'Example:<br />If you want to create accounts m89103001 ~ m89103050,<br />you may fill in [m][89103001][89103050][][8]<br />or              [m89103][1][50][][3].<br />Accounts cannot contain more than 20 characters or less than 2 characters. The first character should be a letter or a number. The rest can be letters, numbers, underscores, or minus signs.<br />Minus signs and underscores can only appear once. Username cannot end with a minus sign or an underscore. Capitalization matters!',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['create_help02'] = array(
    'Big5' => '範例：<br />具密碼：每行一個帳號、<br />一個密碼，逗點隔開。<br />',
    'GB2312' => '范例：<br />具密码：每行一个帐号、<br />一个密码，逗点隔开。<br />',
    'en' => 'Example:<br />User-supplied password:<br />One username and one password each line, with a comma in between.<br />',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['create_help03'] = array(
    'Big5' => '<br />不具密碼 (由系統亂數自取)：<br />每行一個帳號。<br />',
    'GB2312' => '<br />不具密码 (由系统乱数自取)：<br />每行一个帐号。<br />',
    'en' => 'Example:<br />System-generated password:<br />One username each line.<br />',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['create_help04'] = array(
    'Big5' => '請選擇一個CSV格式的檔案。<div style="height: 0.3em;">&nbsp;</div>如何產生CSV檔案：<br />1.CSV格式須為每行一筆資料的純文字檔案<br />2.使用記事本編輯並儲存為.CSV檔<br />3.使用EXCEL編輯並另存新檔，其存檔類型選「*.csv」格式',
    'GB2312' => '请选择一个CSV格式的档案。 <div style="height: 0.3em;">&nbsp;</div>如何产生CSV档案：<br />1.CSV格式须为每行一笔资料的纯文字档案<br />2.使用记事本编辑并储存为.CSV档<br / >3.使用EXCEL编辑并另存新档，其存档类型选「*.csv」格式',
    'en' => 'Please select a text-only file (e.g..CSV file).<br>',
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

$MSG['del_help04'] = array(
    'Big5' => '請選擇一個CSV格式的檔案。<div style="height: 0.3em;">&nbsp;</div>如何產生CSV檔案：<br />1.CSV格式須為每行一筆資料的純文字檔案<br />2.使用記事本編輯並儲存為.CSV檔<br />3.使用EXCEL編輯並另存新檔，其存檔類型選「*.csv」格式',
    'GB2312' => '请选择一个CSV格式的档案。 <div style="height: 0.3em;">&nbsp;</div>如何产生CSV档案：<br />1.CSV格式须为每行一笔资料的纯文字档案<br />2.使用记事本编辑并储存为.CSV档<br / >3.使用EXCEL编辑并另存新档，其存档类型选「*.csv」格式',
    'en' => 'Please select a text-only file (e.g..CSV file) for each entry.<br>',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['js_msg01'] = array(
    'Big5' => '請必須填寫前置字元。',
    'GB2312' => '请必须填写前置字符。',
    'en' => 'Prefix string is required.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['js_msg02'] = array(
    'Big5' => '字首必須是英文字母或數字。',
    'GB2312' => '字首必须是英文字母或数字。',
    'en' => 'The first character should be a letter.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['js_msg03'] = array(
    'Big5' => '底線只能出現一次。',
    'GB2312' => '底线只能出现一次。',
    'en' => 'Underscores can only appear once.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['js_msg04'] = array(
    'Big5' => '長度必須是數字。',
    'GB2312' => '长度必须是数字。',
    'en' => 'Enter a number for Length.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['js_msg05'] = array(
    'Big5' => '帳號個數必須是數字。',
    'GB2312' => '帐号个数必须是数字。',
    'en' => 'Enter a number for # of accounts.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['js_msg06'] = array(
    'Big5' => '帳號個數必須是數字。',
    'GB2312' => '帐号个数必须是数字。',
    'en' => 'Enter a number for # of accounts.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['js_msg07'] = array(
    'Big5' => '帳號最短不得少於 %MIN% 個字元。',
    'GB2312' => '帐号最短不得少于 %MIN% 个字符。',
    'en' => 'Username should contain no less than 2 characters.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['js_msg08'] = array(
    'Big5' => '帳號最長不得超過 %MAX% 個字元。',
    'GB2312' => '帐号最长不得超过 %MAX% 个字符。',
    'en' => 'Username should contain no more than %MAX% characters.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['js_msg09'] = array(
    'Big5' => '減號只能出現一次。',
    'GB2312' => '减号只能出现一次。',
    'en' => 'Minus signs can only appear once.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['js_msg10'] = array(
    'Big5' => '底線或減號不可以出現在最後一個字元。',
    'GB2312' => '底线或减号不可以出现在最后一个字符。',
    'en' => 'Usernames cannot end with underscores or minus signs.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['js_msg11'] = array(
    'Big5' => '帳號除了第一字元為字母、數字,其餘的為字母、數字、底線、減號, -_ 只能出現一次, 且不可以出現在最後一個字元，大小寫有別。',
    'GB2312' => '帐号除了第一字元为字母、数字,其余的为字母、数字、底线、减号, -_ 只能出现一次, 且不可以出现在最后一个字元，大小写有别。',
    'en' => 'The first character should be a letter or a number. \\n The rest can be letters, numbers, underscores, or minus signs.\\n Underscores and minus signs can only appear once and cannot appear last. \\n Capitalization matters!',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['js_msg12'] = array(
    'Big5' => 'E-mail格式不正確。',
    'GB2312' => 'E-mail格式不正确。',
    'en' => 'Incorrect Email format!',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['access_deny'] = array(
    'Big5' => '拒絕存取。',
    'GB2312' => '拒绝存取。',
    'en' => 'Access Denied.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['account_used'] = array(
    'Big5' => '帳號使用中 ',
    'GB2312' => '帐号使用中',
    'en' => 'Account in use',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['format_not_match'] = array(
    'Big5' => '帳號不符合規定 ',
    'GB2312' => '帐号不符合规定',
    'en' => 'Incorrect account format!',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['overMaxUser'] = array(
    'Big5' => '本系統為<font color="red">%max_register_user%</font>人授權版，註冊人數已達上限。無法新增此帳號，若有任何問題請洽<a href="%admin_email%">系統管理者</a>',
    'GB2312' => '本系统为<font color="red">%max_register_user%</font>人授权版，注册人数已达上限。无法新增此帐号，若有任何问题请洽<a href="%admin_email%">系统管理者</a>',
    'en' => 'System Message',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['account_list'] = array(
    'Big5' => '您所要開設的帳號如下：',
    'GB2312' => '您所要开设的帐号如下：',
    'en' => 'The accounts you want to add are as follows:',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['no_account'] = array(
    'Big5' => '無可新增的帳號 ',
    'GB2312' => '无可新增的帐号',
    'en' => 'No accounts to add!',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['ok_add'] = array(
    'Big5' => '確定新增',
    'GB2312' => '确定新增',
    'en' => 'Create',
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

$MSG['account'] = array(
    'Big5' => '帳號 ',
    'GB2312' => '帐号',
    'en' => 'Username',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['password'] = array(
    'Big5' => '密碼 ',
    'GB2312' => '密码',
    'en' => 'Password',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['status'] = array(
    'Big5' => '狀態',
    'GB2312' => '状态',
    'en' => 'Status',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['email'] = array(
    'Big5' => 'E-mail',
    'GB2312' => 'E-mail',
    'en' => 'E-mail',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['add_success'] = array(
    'Big5' => '新增成功 ',
    'GB2312' => '新增成功',
    'en' => 'Addition succeeded!',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['add_fail'] = array(
    'Big5' => '新增失敗 ',
    'GB2312' => '新增失败',
    'en' => 'Addition failed!',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['success'] = array(
    'Big5' => '成功： ',
    'GB2312' => '成功：',
    'en' => 'Succeeded:',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['fail'] = array(
    'Big5' => '失敗： ',
    'GB2312' => '失败：',
    'en' => 'Failed:',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['noexist'] = array(
    'Big5' => '不存在： ',
    'GB2312' => '不存在：',
    'en' => 'Not exist:',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['mail_backup'] = array(
    'Big5' => '郵寄給管理者備存',
    'GB2312' => '邮寄给管理者备存',
    'en' => 'Email Administrator',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['mail_student'] = array(
    'Big5' => '郵寄給學員',
    'GB2312' => '邮寄给学员',
    'en' => 'Email Students',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['print'] = array(
    'Big5' => '列印',
    'GB2312' => '列印',
    'en' => 'Print',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['del_serial_account'] = array(
    'Big5' => '刪除連續帳號',
    'GB2312' => '删除连续帐号',
    'en' => 'Delete Serial Account',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['del_discrete_account'] = array(
    'Big5' => '刪除不規則帳號',
    'GB2312' => '删除不规则帐号',
    'en' => 'Delete Discrete Account',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['import_del_account'] = array(
    'Big5' => '刪除匯入帳號',
    'GB2312' => '删除导入帐号',
    'en' => 'Delete Imported Account',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['ok_del'] = array(
    'Big5' => '確定刪除',
    'GB2312' => '确定删除',
    'en' => 'Delete',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['no_del_account'] = array(
    'Big5' => '無可刪除的帳號',
    'GB2312' => '无可删除的帐号',
    'en' => 'No accounts to delete!',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['account_not_exist'] = array(
    'Big5' => '帳號不存在 ',
    'GB2312' => '帐号不存在',
    'en' => 'Account does not exist!',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['del_list'] = array(
    'Big5' => '您所要刪除的帳號如下：',
    'GB2312' => '您所要删除的帐号如下：',
    'en' => 'The accounts you want to delete are as follows:',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['search_keyword'] = array(
    'Big5' => '搜尋 : ',
    'GB2312' => '搜索 :',
    'en' => 'Search :',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['realname'] = array(
    'Big5' => '姓名 ',
    'GB2312' => '姓名',
    'en' => 'Name',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['username'] = array(
    'Big5' => '帳號 ',
    'GB2312' => '帐号',
    'en' => 'Username',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['inside'] = array(
    'Big5' => '內有 ',
    'GB2312' => '内有',
    'en' => 'contains',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['inside1'] = array(
    'Big5' => '的人 ',
    'GB2312' => '的人',
    'en' => ' ',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['keyword'] = array(
    'Big5' => '關鍵字 ',
    'GB2312' => '关键字',
    'en' => 'Keyword',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['confirm'] = array(
    'Big5' => '確定 ',
    'GB2312' => '确定',
    'en' => 'OK',
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

$MSG['page'] = array(
    'Big5' => '頁次：',
    'GB2312' => '页码：',
    'en' => 'Page No.:',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['first1'] = array(
    'Big5' => '首頁',
    'GB2312' => '首页',
    'en' => 'First',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['prev'] = array(
    'Big5' => '上頁',
    'GB2312' => '上页',
    'en' => 'Previous',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['next'] = array(
    'Big5' => '下頁',
    'GB2312' => '下页',
    'en' => 'Next',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['last1'] = array(
    'Big5' => '末頁',
    'GB2312' => '末页',
    'en' => 'Last',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title40'] = array(
    'Big5' => '切換到第一頁',
    'GB2312' => '切换到第一页',
    'en' => 'Switch to First',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title41'] = array(
    'Big5' => '切換到上一頁',
    'GB2312' => '切换到上一页',
    'en' => 'Switch to Previous',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title42'] = array(
    'Big5' => '切換到下一頁',
    'GB2312' => '切换到下一页',
    'en' => 'Switch to Next',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['gender'] = array(
    'Big5' => '性別',
    'GB2312' => '性别',
    'en' => 'Gender',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title56'] = array(
    'Big5' => '個人資料',
    'GB2312' => '个人资料',
    'en' => 'User Profile',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title57'] = array(
    'Big5' => '修課記錄',
    'GB2312' => '修课记录',
    'en' => 'Course Records',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title58'] = array(
    'Big5' => '學習成果',
    'GB2312' => '学习成果',
    'en' => 'Learning Results',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title59'] = array(
    'Big5' => '請勾選欲刪除的學員!',
    'GB2312' => '请勾选欲删除的学员!',
    'en' => 'Please select the students you want to delete.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title60'] = array(
    'Big5' => '您確定要刪除嗎? ',
    'GB2312' => '您确定要删除吗?',
    'en' => 'Are you sure you want to delete?',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['delete_success'] = array(
    'Big5' => '刪除成功 ',
    'GB2312' => '删除成功',
    'en' => 'Deletion succeeded!',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['delete_fail'] = array(
    'Big5' => '刪除失敗 ',
    'GB2312' => '删除失败',
    'en' => 'Deletion failed!',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['query_people'] = array(
    'Big5' => '查詢人員資料 ',
    'GB2312' => '查询人员资料',
    'en' => 'Search User',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['must_select_filename'] = array(
    'Big5' => '您必須指定一個匯入的檔案',
    'GB2312' => '您必须指定一个导入的档案',
    'en' => 'You must select a file to import.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title61'] = array(
    'Big5' => '選擇刪除帳號的欄位 ',
    'GB2312' => '选择删除帐号的栏位',
    'en' => 'Please select accounts you want to delete.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title62'] = array(
    'Big5' => '序號 ',
    'GB2312' => '序号',
    'en' => 'No.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title63'] = array(
    'Big5' => '請選擇... ',
    'GB2312' => '请选择...',
    'en' => 'Please select.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title64'] = array(
    'Big5' => '檔案資料 (第一行) ',
    'GB2312' => '档案资料 (第一行)',
    'en' => 'File Data (Line 1)',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title65'] = array(
    'Big5' => '下一步 ',
    'GB2312' => '下一步',
    'en' => 'Next',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title68'] = array(
    'Big5' => '請選擇要刪除的欄位！ ',
    'GB2312' => '请选择要删除的栏位！',
    'en' => 'Please select the accounts you want to delete!',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title69'] = array(
    'Big5' => '通訊錄 ',
    'GB2312' => '通讯录',
    'en' => 'Contact Info',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['select_all'] = array(
    'Big5' => '全部選取或取消',
    'GB2312' => '全部选取或取消',
    'en' => 'Select All or Cancel All',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['send_mail'] = array(
    'Big5' => '寄信 ',
    'GB2312' => '寄信',
    'en' => 'Send Email',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['birthday'] = array(
    'Big5' => '生日 ',
    'GB2312' => '生日',
    'en' => 'Birthday',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['home_tel'] = array(
    'Big5' => '電話 (家) ',
    'GB2312' => '电话 (家)',
    'en' => 'Phone(H)',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['home_fax'] = array(
    'Big5' => '傳真 (家) ',
    'GB2312' => '传真 (家)',
    'en' => 'Fax(H)',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['home_address'] = array(
    'Big5' => '地址 (家) ',
    'GB2312' => '地址 (家)',
    'en' => 'Address(H)',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['office_tel'] = array(
    'Big5' => '電話 (公司) ',
    'GB2312' => '电话 (公司)',
    'en' => 'Phone(O)',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['office_address'] = array(
    'Big5' => '地址 (公司) ',
    'GB2312' => '地址 (公司)',
    'en' => 'Address(O)',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['cell_phone'] = array(
    'Big5' => '行動電話 ',
    'GB2312' => '移动电话',
    'en' => 'Mobile',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['hide'] = array(
    'Big5' => '隱藏 ',
    'GB2312' => '隐藏',
    'en' => 'Hide',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['person_status'] = array(
    'Big5' => '身份',
    'GB2312' => '身份',
    'en' => 'Role',
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

$MSG['student'] = array(
    'Big5' => '正式生',
    'GB2312' => '正式生',
    'en' => 'Enrolled Student',
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

$MSG['instructor'] = array(
    'Big5' => '講師',
    'GB2312' => '讲师',
    'en' => 'Guest Instructor',
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

$MSG['senior'] = array(
    'Big5' => '學長',
    'GB2312' => '学长',
    'en' => 'Senior',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['paterfamilias'] = array(
    'Big5' => '家長',
    'GB2312' => '家长',
    'en' => 'Parents',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['btn_alt_detail'] = array(
    'Big5' => '詳細資料',
    'GB2312' => '详细资料',
    'en' => 'Detailed Info',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['more_setting'] = array(
    'Big5' => '更多資料',
    'GB2312' => '更多资料',
    'en' => 'More',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['original_setting'] = array(
    'Big5' => '精簡資料',
    'GB2312' => '精简资料',
    'en' => 'Concise Info',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['thispage_sendme'] = array(
    'Big5' => '將本頁寄給自己 ',
    'GB2312' => '将本页寄给自己',
    'en' => 'Email this page to me',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['send_error'] = array(
    'Big5' => '請勾選欲寄信的對象 ',
    'GB2312' => '请勾选欲寄信的对象',
    'en' => 'Please select receivers.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['subject'] = array(
    'Big5' => '的通訊錄 ',
    'GB2312' => '的通讯录',
    'en' => 'Contact Info',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['send_to'] = array(
    'Big5' => '已寄送給: ',
    'GB2312' => '已寄送给:',
    'en' => 'Sent to:',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['unfinded'] = array(
    'Big5' => '對不起，沒有找到符合條件的人，請重新查詢。 ',
    'GB2312' => '对不起，没有找到符合条件的人，请重新查询。',
    'en' => 'Sorry. No match found. Please try again!',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['edit_register_mail'] = array(
    'Big5' => '編輯帳號通知信 ',
    'GB2312' => '编辑帐号通知信 ',
    'en' => 'Edit Account Notification Email',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['show_pic'] = array(
    'Big5' => '顯示圖片 ',
    'GB2312' => '显示图片',
    'en' => 'Show Image',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['msg_count_over'] = array(
    'Big5' => '帳號個數與數字欄位設定有誤，請修正。',
    'GB2312' => '帐号个数与数字栏位设定有误，请修正。',
    'en' => 'The length of account is not allowed. Please correct it.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title71'] = array(
    'Big5' => '回刪除連續帳號 ',
    'GB2312' => '回删除连续帐号',
    'en' => 'Back to Delete Serial Account',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title72'] = array(
    'Big5' => '回 刪除不規則帳號 ',
    'GB2312' => '回 删除不规则帐号',
    'en' => 'Back to Delete Discrete Account',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title73'] = array(
    'Big5' => '回刪除匯入帳號 ',
    'GB2312' => '回删除导入帐号',
    'en' => 'Back to Delete Imported Account',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title74'] = array(
    'Big5' => '回新增連續帳號 ',
    'GB2312' => '回新增连续帐号',
    'en' => 'Back to Add Serial Account',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title75'] = array(
    'Big5' => '回新增不規則帳號 ',
    'GB2312' => '回新增不规则帐号',
    'en' => 'Back to Add Discrete Account',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title76'] = array(
    'Big5' => '回匯入帳號 ',
    'GB2312' => '回导入帐号',
    'en' => 'Back to Import Account',
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

$MSG['title79'] = array(
    'Big5' => '變換身份 ',
    'GB2312' => '变换身份',
    'en' => 'Switch Identity',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title80'] = array(
    'Big5' => '請輸入帳號。 ',
    'GB2312' => '请输入帐号。',
    'en' => 'Please enter an account.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title81'] = array(
    'Big5' => '變換身份登入 ',
    'GB2312' => '变换身份登入',
    'en' => 'Log in as Someone Else',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title82'] = array(
    'Big5' => '以特定學員身分進入學生環境 ',
    'GB2312' => '以特定学员身份进入学生环境',
    'en' => 'Log into classroom as a certain student',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title83'] = array(
    'Big5' => '重新設定 ',
    'GB2312' => '重新设定',
    'en' => 'Reset',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title84'] = array(
    'Big5' => '這個帳號不存在，無法登入 ',
    'GB2312' => '这个帐号不存在，无法登入',
    'en' => 'Unable to login. This account doesn&#039;t exist.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title85'] = array(
    'Big5' => '不可以用另一位管理者的帳號登入。',
    'GB2312' => '不可以用另一位管理者的帐号登入。',
    'en' => 'You cannot login with the account of another system administrator.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title86'] = array(
    'Big5' => '回變換身份登入',
    'GB2312' => '回变换身份登入',
    'en' => 'Back to Log in as Someone Else',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title87'] = array(
    'Big5' => '帳號使用期限',
    'GB2312' => '帐号使用期限',
    'en' => 'Account Expiry Date',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title88'] = array(
    'Big5' => '日期的區間應 由小 到大,如 2003/10/1 至 2003/11/30 ',
    'GB2312' => '日期的区间应 由小 到大,如 2003/10/1 至 2003/11/30',
    'en' => 'Dates should be listed from an earlier date to a later date. For instance, from 2003/10/1 to 2003/11/30.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title90'] = array(
    'Big5' => '選擇匯入帳號的欄位 ',
    'GB2312' => '选择导入帐号的栏位',
    'en' => 'Please select accounts you want to import.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title91'] = array(
    'Big5' => '請選擇要匯入的欄位！ ',
    'GB2312' => '请选择要导入的栏位！',
    'en' => 'Please select accounts you want to import.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['system_reserved'] = array(
    'Big5' => '為系統保留帳號,不允許新增或刪除。',
    'GB2312' => '为系统保留帐号,不允许新增或删除。',
    'en' => ' is system reserved account. It can not be added or deleted.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title92'] = array(
    'Big5' => '每頁：',
    'GB2312' => '每页：',
    'en' => '',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title93'] = array(
    'Big5' => '筆',
    'GB2312' => '笔',
    'en' => 'entries per page',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['default_amount'] = array(
    'Big5' => '預設',
    'GB2312' => '预设',
    'en' => 'Default',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['msg_date_error'] = array(
    'Big5' => '關閉日期必須大於開始日期，請重新設定。',
    'GB2312' => '关闭日期必须大于开始日期，请重新设定。',
    'en' => 'Close date should be later than start date. Please reset.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['msg_date_start'] = array(
    'Big5' => '啟用日期',
    'GB2312' => '启用日期',
    'en' => 'Start Date',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['msg_date_stop'] = array(
    'Big5' => '結束日期',
    'GB2312' => '结束日期',
    'en' => 'End Date',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['title1'] = array(
    'Big5' => '：',
    'GB2312' => '&#039; ：',
    'en' => '',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['btn_csv_example'] = array(
    'Big5' => 'CSV範本',
    'GB2312' => 'CSV范本',
    'en' => 'CSV template.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['import_format_help'] = array(
    'Big5' => '您的檔案使用何種語言編碼（字集）？',
    'GB2312' => '您的档案使用何种语言编码（字集）？',
    'en' => 'What language format of file will you import?',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['Big5'] = array(
    'Big5' => '正體中文(Big5)',
    'GB2312' => '繁体中文(Big5)',
    'en' => 'Big5',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['GB2312'] = array(
    'Big5' => '簡體中文(GB2312)',
    'GB2312' => '简体中文(GB2312)',
    'en' => 'GB2312',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['en'] = array(
    'Big5' => '英文(en)',
    'GB2312' => '英文(en)',
    'en' => 'en',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['EUC-JP'] = array(
    'Big5' => '日文(EUC-JP)',
    'GB2312' => '日文(EUC-JP)',
    'en' => 'EUC-JP',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['UTF-8'] = array(
    'Big5' => 'UTF-8',
    'GB2312' => 'UTF-8',
    'en' => 'UTF-8',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['go_direct'] = array(
    'Big5' => '回導師管理',
    'GB2312' => '回导师管理',
    'en' => 'Return to supervisor management.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['none_class'] = array(
    'Big5' => '系統無此班級',
    'GB2312' => '系统无此班级',
    'en' => 'No such class showd in the system',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['page_title'] = array(
    'Big5' => '到',
    'GB2312' => '到',
    'en' => 'Go to page ',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['page_title2'] = array(
    'Big5' => '頁',
    'GB2312' => '页',
    'en' => '',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['go_page_input_error'] = array(
    'Big5' => '您輸入的值已超過總頁數 ( %TOTAL_PAGE% ) ，請重新輸入。',
    'GB2312' => '您输入的值已超过总页数 ( %TOTAL_PAGE% ) ，请重新输入。',
    'en' => 'The value you input is large than total page number(%TOTAL_PAGE%), please input again. ',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['go_page_input_error2'] = array(
    'Big5' => '頁數請輸入非零的正整數(1、2、3、...)',
    'GB2312' => '页数请输入非零的正整数(1、2、3、...)',
    'en' => 'Please input integer large than 0. (1,2,3...)',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['is_teacher'] = array(
    'Big5' => '此帳號具有教師/助教身份，須移除身份後才可移除',
    'GB2312' => '此帐号具有教师/助教身份，须移除身份后才可移除',
    'en' => 'Cannot remove the account before removing his(her) TEACHER/ASSISTANT permission.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['is_director'] = array(
    'Big5' => '此帳號具有導師/助教身份，須移除身份後才可移除',
    'GB2312' => '此帐号具有导师/助教身份，须移除身份后才可移除',
    'en' => 'Cannot remove the account before removing his(her) DIRECTOR/ASSISTANT permission.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['is_administrator'] = array(
    'Big5' => '此帳號具有管理者身份，須移除身份後才可移除',
    'GB2312' => '此帐号具有管理者身份，须移除身份后才可移除',
    'en' => 'Cannot remove the account before removing his(her) ADMINISTRATOR permission.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['error_account_cols'] = array(
    'Big5' => 'WM_history_user_account 與 WM_all_account 欄位不一致，無法進行備份',
    'GB2312' => 'WM_history_user_account 与 WM_all_account 栏位不一致，无法进行备份',
    'en' => 'Table "WM_history_user_account" and table "WM_all_account" fields are not consistent, can not be backed up.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['more_info'] = array(
    'Big5' => '更多資訊',
    'GB2312' => '更多资讯',
    'en' => 'more info...',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['less_info'] = array(
    'Big5' => '收合',
    'GB2312' => '收合',
    'en' => 'collapse',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['position'] = array(
    'Big5' => '身分',
    'GB2312' => '身份',
    'en' => 'position',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['homepage'] = array(
    'Big5' => '個人網站',
    'GB2312' => '个人网站',
    'en' => 'homepage',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['photo'] = array(
    'Big5' => '照片',
    'GB2312' => '照片',
    'en' => 'photo',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['co_account_bind'] = array(
    'Big5' => '帳號綁定',
    'GB2312' => '帐号绑定',
    'en' => 'Account Bind',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['co_bind_all'] = array(
    'Big5' => '全部',
    'GB2312' => '全部',
    'en' => 'All',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['co_bind_fb'] = array(
    'Big5' => 'Facebook',
    'GB2312' => 'Facebook',
    'en' => 'Facebook',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['co_bind_none'] = array(
    'Big5' => '無綁定',
    'GB2312' => '无绑定',
    'en' => 'None',
    'EUC-JP' => '',
    'user_define' => ''
);
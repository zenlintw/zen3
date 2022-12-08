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
    'Big5' => '匯入班級人員',
    'GB2312' => '导入班级人员',
    'en' => 'Import Students',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['td_title'] = array(
    'Big5' => '匯入檔案',
    'GB2312' => '导入档案',
    'en' => 'Import File',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['cvs_file_comment'] = array(
    'Big5' => '請選擇一個CSV格式的檔案。<div style="height: 0.3em;">&nbsp;</div>如何產生CSV檔案：<br />1.CSV格式須為每行一筆資料的純文字檔案<br />2.使用記事本編輯並儲存為.CSV檔<br />3.使用EXCEL編輯並另存新檔，其存檔類型選「*.csv」格式<br>
        <font color="red">每一行至少有1個欄位,至多有2個欄位。<br>
        如：成員帳號,班級代碼(可有可無)</font><br>
        Example: may<br>
        Example1: may,ELBU<br>
        ',
    'GB2312' => '请选择一个CSV格式的档案。 <div style="height: 0.3em;">&nbsp;</div>如何产生CSV档案：<br />1.CSV格式须为每行一笔资料的纯文字档案<br />2.使用记事本编辑并储存为.CSV档<br / >3.使用EXCEL编辑并另存新档，其存档类型选「*.csv」格式<br>
        <font color=red>每一行至少有1个栏位,至多有2个栏位。<br>
        如：成员帐号,班级代码(可有可无)</font><br>
        Example: may<br>
        Example1: may,ELBU<br>',
    'en' => 'Please select a text-only file (e.g..CSV file).<br><font color=red>At least one field each entry, no more than two fields.<br>For instance, Member ID and/or Class ID</font><br>',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['td_title2'] = array(
    'Big5' => '選擇欲匯入的班級',
    'GB2312' => '选择欲导入的班级',
    'en' => 'Please select the class you want to import.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['td_dep_comment'] = array(
    'Big5' => '<font color="red">
        若匯入班級的檔案無班級代碼時，<br>
        請點選某一班級，做為匯入的班級
        </font>
        ',
    'GB2312' => '<font color=red>若导入班级的档案无班级代码时，<br>请点选某一班级，做为导入的班级</font>',
    'en' => '<font color=red>If the class you want to import does not have an ID,<br>please click on the class to import.</font>',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['btn_import'] = array(
    'Big5' => '匯入',
    'GB2312' => '导入',
    'en' => 'Import',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['btn_cancel'] = array(
    'Big5' => '重設',
    'GB2312' => '重设',
    'en' => 'Reset',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['must_select_filename'] = array(
    'Big5' => '你必須指定一個匯入的檔案',
    'GB2312' => '你必须指定一个导入的档案',
    'en' => 'You have to select a file to import.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['username'] = array(
    'Big5' => '帳號',
    'GB2312' => '帐号',
    'en' => 'Username',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['dep_name'] = array(
    'Big5' => '班級名稱',
    'GB2312' => '班级名称',
    'en' => 'Class Name',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['dep_id'] = array(
    'Big5' => '班級代碼',
    'GB2312' => '班级代码',
    'en' => 'Class ID',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['account_error0'] = array(
    'Big5' => '此帳號不存在。',
    'GB2312' => '此帐号不存在。',
    'en' => 'This account does not exist!',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['account_error1'] = array(
    'Big5' => '此帳號為保留的帳號，不能使用。',
    'GB2312' => '此帐号为保留的帐号，不能使用。',
    'en' => 'This is a reserved account. It cannot be used.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['account_error3'] = array(
    'Big5' => '帳號格式不符合。',
    'GB2312' => '帐号格式不符合。',
    'en' => 'Incorrect username format!',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['account_error4'] = array(
    'Big5' => '此帳號為保留的帳號，並且使用中。',
    'GB2312' => '此帐号为保留的帐号，并且使用中。',
    'en' => 'This is a reserved account and it is in use.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['account_error5'] = array(
    'Big5' => '不可為空帳號。',
    'GB2312' => '不可为空帐号。',
    'en' => 'Non-empty account.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['result'] = array(
    'Big5' => '匯入結果',
    'GB2312' => '导入结果',
    'en' => 'Import Results',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['result_success'] = array(
    'Big5' => '匯入成功  ',
    'GB2312' => '导入成功',
    'en' => 'Imported successfully!',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['result_account_fail'] = array(
    'Big5' => '帳號已存在於此班級',
    'GB2312' => '帐号已存在于此班级',
    'en' => 'This account already exists in this class.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['result_dep_no_fail'] = array(
    'Big5' => '班級代碼不存在。',
    'GB2312' => '班级代码不存在。',
    'en' => 'This class ID does not exist!',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['result_dep_no_fail2'] = array(
    'Big5' => '您匯入的檔案未填寫班級代碼或選擇班級<br>
						請填寫班級代碼或選擇班級之後重新匯入。',
    'GB2312' => '您导入的档案未填写班级代码或选择班级<br>请填写班级代码或选择班级之后重新导入。',
    'en' => 'The file you imported doesn&#039;t have class IDs.<br>Please fill in class IDs or select classes and then import again.',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['result_fail2'] = array(
    'Big5' => '匯入失敗：',
    'GB2312' => '导入失败：',
    'en' => 'Import Failure:',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['return_preivous'] = array(
    'Big5' => '回匯入班級人員 ',
    'GB2312' => '回导入班级人员',
    'en' => 'Return',
    'EUC-JP' => '',
    'user_define' => ''
);

$MSG['import_format_title'] = array(
    'Big5' => '選擇編碼（字集）',
    'GB2312' => '选择编码（字集）',
    'en' => 'Choose character set ',
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
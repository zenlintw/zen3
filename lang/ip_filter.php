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

	$MSG['login_restrict'] = array(
		'Big5'			=> '登入管制',
		'GB2312'		=> '登入限制',
		'en'			=> 'Login Restriction',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['are_you_sure'] = array(
		'Big5'			=> '您確定要刪除所選擇的規則嗎？',
		'GB2312'		=> '您确定要删除所选择的规则吗？',
		'en'			=> 'Are you sure you want to remove the selected rules?',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['all_unselect'] = array(
		'Big5'			=> '全消',
		'GB2312'		=> '全消',
		'en'			=> 'Cancel Select',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['all_select'] = array(
		'Big5'			=> '全選',
		'GB2312'		=> '全选',
		'en'			=> 'Select All',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['rule_help'] = array(
		'Big5'			=> '<ol style="display: none">
  <li>若要禁止某帳號從任何主機登入，則填入該帳號，位址空白，並選擇 Deny。</li>
  <li>若要禁止某帳號從特定主機登入，則填入該帳號，位址填入 IP 或 Domain name，可填多個，並選擇 Deny。</li>
  <li>若要禁止所有帳號從特定主機登入，則帳號空白，位址填入 IP 或 Domain name，可填多個，並選擇 Deny。</li>
  <li>若要限定某帳號只能從特定主機登入，則填入該帳號，位址填入 IP 或 Domain name，可填多個，並選擇 Allow。</li>
  <li>若要限定所有帳號只能從特定主機登入，則帳號空白，位址填入 IP 或 Domain name，可填多個，並選擇 Allow。</li>
  <li>IP 與 Domain name 可填多個，亦可只填部份，如</li><ul>
    <li><b>61.60.5.</b> 表示 61.60.5.0~61.60.5.255 所有 C-class IP</li>
    <li><b>192.168.</b> 表示 192.168.0.0~192.168.255.255 所有 B-class IP</li>
    <li><b>.com.tv</b> 表示 *.com.tv</li>
  </ul>

  <li>檢查規則採「先符合先採用」原則。越上方的規則越先判斷。當找到第一個符合檢查的帳號或 IP，則依此決定禁止或允許。</li>
</ol>',
		'GB2312'		=> '<ol style=display: none><li>若要禁止某帐号从任何主机登入，则填入该帐号，位址空白，并选择 Deny。</li><li>若要禁止某帐号从特定主机登入，则填入该帐号，位址填入 IP 或 Domain name，可填多个，并选择 Deny。</li><li>若要禁止所有帐号从特定主机登入，则帐号空白，位址填入 IP 或 Domain name，可填多个，并选择 Deny。</li><li>若要限定某帐号只能从特定主机登入，则填入该帐号，位址填入 IP 或 Domain name，可填多个，并选择 Allow。</li><li>若要限定所有帐号只能从特定主机登入，则帐号空白，位址填入 IP 或 Domain name，可填多个，并选择 Allow。</li><li>IP 与 Domain name 可填多个，亦可只填部份，如</li><ul><li><b>61.60.5.</b> 表示 61.60.5.0~61.60.5.255 所有 C-class IP</li><li><b>192.168.</b> 表示 192.168.0.0~192.168.255.255 所有 B-class IP</li><li><b>.com.tv</b> 表示 *.com.tv</li></ul><li>检查规则采“先符合先采用”原则。越上方的规则越先判断。当找到第一个符合检查的帐号或 IP，则依此决定禁止或允许。</li></ol>',
		'en'			=> '<ol style=display: none><li>If you want to block a certain account logging in from any client PC, fill in the account, leave the URL field blank and select Deny.</li><li>If you want to block a certain account from a certain client PC, fill in the account, and enter the IP or Domain name in the URL field, and select Deny. (Multiple IP&#039;s or Domain names are allowed.)</li><li>If you want to block all the accounts from a certain client PC, leave the account field blank, enter the IP or Domain name you want to block, and select Deny. (Multiple IP&#039;s or Domain names are allowed.)</li><li>If you only allow a certain account from a certain client PC, fill in that account and IP or Domain name, and select Allow. (Multiple IP&#039;s orDomain names are allowed.)</li><li>If you only allow accounts from a certain client PC, leave the account field blank, enter the IP or Domain name, and select Allow. (Multiple IP&#039;s or Domain names are allowed.)</li><li> You can fill in multiple complete IP&#039;s or Domain names. Or you can enter a partial IP like</li><ul><li><b>61.60.5.</b> to indicate all C-class IP, from 61.60.5.0 to 61.60.5.255 </li><li><b>192.168.</b> to indicate all B-class IP, from 192.168.0.0 to 192.168.255.255 </li><li><b>.com.tv</b> stands for *.com.tv</li></ul><li>First match first apply rule is used. The upper rules will be applied first. The first matched account or IP will be used to decide whether to Deny or Allow.</li></ol>',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['addnew'] = array(
		'Big5'			=> '新增',
		'GB2312'		=> '新增',
		'en'			=> 'Add',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['remove'] = array(
		'Big5'			=> '刪除',
		'GB2312'		=> '删除',
		'en'			=> 'Delete',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['mv_up'] = array(
		'Big5'			=> '上移',
		'GB2312'		=> '上移',
		'en'			=> 'Move Up',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['mv_dn'] = array(
		'Big5'			=> '下移',
		'GB2312'		=> '下移',
		'en'			=> 'Move Down',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['save_priority'] = array(
		'Big5'			=> '儲存順序',
		'GB2312'		=> '保存顺序',
		'en'			=> 'Save',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['Pick'] = array(
		'Big5'			=> '選取',
		'GB2312'		=> '选取',
		'en'			=> 'Select',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['username'] = array(
		'Big5'			=> '帳號',
		'GB2312'		=> '帐号',
		'en'			=> 'User Account',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['host'] = array(
		'Big5'			=> '位址 (IP 或 Domain Name)',
		'GB2312'		=> '位址 (IP 或 Domain Name)',
		'en'			=> 'URL (IP or Domain Name)',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['allow_deny'] = array(
		'Big5'			=> '允許/拒絕',
		'GB2312'		=> '允许/拒绝',
		'en'			=> 'Allow/Deny',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['modify'] = array(
		'Big5'			=> '修改',
		'GB2312'		=> '修改',
		'en'			=> 'Modify',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['edit_rule'] = array(
		'Big5'			=> '編輯規則',
		'GB2312'		=> '编辑规则',
		'en'			=> 'Edit Rule',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['username_hint'] = array(
		'Big5'			=> '請填寫單一帳號',
		'GB2312'		=> '请填写单一帐号',
		'en'			=> 'Please fill in an individual account.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['host_hint'] = array(
		'Big5'			=> '請填寫 IP 或 Domain，可只寫部份。若有多個請每行一個。',
		'GB2312'		=> '请填写 IP 或 Domain，可只写部分。若有多个请每行一个。',
		'en'			=> 'Please fill in IP or Domain name. One in each line.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['deny_from'] = array(
		'Big5'			=> '由此則擋除',
		'GB2312'		=> '由此则禁止',
		'en'			=> 'Deny from here',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['allow_from'] = array(
		'Big5'			=> '由此則允許',
		'GB2312'		=> '由此则允许',
		'en'			=> 'Allow from here',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['mode_hint'] = array(
		'Big5'			=> '請選擇擋除還是允許',
		'GB2312'		=> '请选择禁止还是允许',
		'en'			=> 'Please select Deny or Allow',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['submit'] = array(
		'Big5'			=> '確定',
		'GB2312'		=> '确定',
		'en'			=> 'OK',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['reset'] = array(
		'Big5'			=> '清除重填',
		'GB2312'		=> '清除重填',
		'en'			=> 'Reset',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['cancel'] = array(
		'Big5'			=> '取消',
		'GB2312'		=> '取消',
		'en'			=> 'Cancel',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['priority_saved'] = array(
		'Big5'			=> '規則順序已儲存。',
		'GB2312'		=> '规则顺序已保存。',
		'en'			=> 'Saved successfully.',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

	$MSG['incorrect_format'] = array(
		'Big5'			=> '錯誤的規則編號！',
		'GB2312'		=> '错误的规则编号！',
		'en'			=> 'Incorrect rule number!',
		'EUC-JP'		=> '',
		'user_define'	=> ''
	);

?>

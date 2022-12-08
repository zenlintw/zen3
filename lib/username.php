<?php
	/**
	 * 兩個處理帳號的 function
	 *     1. checkUsername()：新增帳號前，檢查帳號是不是已經被註冊或不可使用
	 *     2. addUser()：新增一位使用者
	 *
	 * @author  $Author: small $
	 * @version $Id: username.php,v 1.2 2011-01-20 01:40:51 small Exp $
	 * $State: Exp $
	 */
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

	if (!defined('pwdMinLength')) define('pwdMinLength', 6);

	define('pwdOk',                    0);
	define('pwdTooShort',              1);
	define('pwdTooEasyDigitRepeat',    2);
	define('pwdTooEasyDigitSequence',  4);
	define('pwdTooEasyAlphaRepeat',    8);
	define('pwdTooEasyAlphaSequence', 16);
	define('pwdTooEasyIsId',          32);
	define('pwdTooEasyIsIdReverse',   64);

	/**
	 * checkUsername()
	 *     檢查這個帳號是否可以使用
	 *     檢查的動作：
	 *         1. 是否為保留的帳號
	 *         2. 是否已經有人使用了
	 *         3. 檢查帳號的格式是否符合我們的要求
	 * @param string $username 要檢查的帳號
	 * @param boolean $only_format 僅檢查帳號格式
	 * @return
	 *     0 : 可以使用
	 *     1 : 保留的帳號，而未使用
	 *     2 : 帳號使用中
	 *     3 : 帳號格式不符合
	 *     4 : 保留的帳號，並且使用中
	 *     5 : 帳號沒有輸入
	 **/
	function checkUsername($username, $only_format=false) {
		global $sysConn;
		static $reserve;

        if ($username === '') {
            return 5;
        }

        if ($username == 'undefined') {
        	return 1;
        }

		// 帳號格式不符合
		// 帳號的正規表示式(除了第一字元外,其餘的為字母、數字、底線、減號,- 只能出現一次,且不可以出現在最後一個字元)
		// VIP#93429 調整增刪學員支援「底線」的帳號
		if (!preg_match(Account_format, str_replace('_', '', $username)) ||
			strlen($username) < sysAccountMinLen ||
			strlen($username) > sysAccountMaxLen) {
			return 3;
		}

		if ($only_format) return 0;

		// 保留的帳號
		$isReserve = false;
		if (!isset($reserve))
		{
			if (($reserve = @file_get_contents(sysDocumentRoot . '/config/reserve_username.txt')) !== false)
			    $reserve = '/^(' . preg_replace(array('/[^\w\r\s*]/', '/[\r\s]+/', '/\*/'),
												array('', '|', '\w*'),
												trim($reserve)) .
						   ')$/i';
			else
			    $reserve = '/^(admin\w*|sysop|system|manager|root|guest|teacher|student|\w*fuck\w*|shit|damn|nobody)$/i';
		}
		if (preg_match($reserve, $username)) {
			$isReserve = true;
		}

		// 帳號使用中
		list($user_count) = dbGetStSr('WM_all_account', 'count(*)', "username='{$username}'", ADODB_FETCH_NUM);

        // 切換到 Master資料庫
        $sysConn->Execute('use ' . sysDBname);
		list($mooc_user_count) = dbGetStSr('CO_mooc_account', 'count(*)', "username='{$username}'", ADODB_FETCH_NUM);

		if (($user_count + $mooc_user_count) > 0) {
			if ($isReserve) {
				return 4;
			} else {
				return 2;
			}
		}
		if ($isReserve) return 1;

	 	return 0;
	}

	/**
	 * 處理真實姓名
	 * 1. 不管使用者的語系，只要姓氏與名字都為英文(沒有夾雜中文)，則一律顯示『名 ＋ 空格 ＋ 姓』
	 * 2. 不管使用者的語系，只要姓氏與名字中有夾雜中文，則一律顯示『姓＋名』
	 * @param string $first_name first name
	 * @param string $last_name last name
	 * @return string 處理後的真實姓名
	 */
	function checkRealname($first_name, $last_name)
	{
	    if (empty($first_name)&&empty($last_name)) return '';
	    // 若全部字元都在 7bits 範圍內，則表示沒有 multi-bytes 字元集的字，視為英文名 (modify by Wiseguy)
		return preg_match('/^[\x20-\x7E]+$/', $ret = ($first_name . ' ' . $last_name)) ?
		                  $ret : ($last_name . $first_name);
	}

	/**
	 * Passwd()
	 *     產生一組密碼
	 * @return 密碼
	 **/
	function Passwd() {
		$pwd = sprintf("%c%c%c#%03d",
			mt_rand(97, 122),
			mt_rand(97, 122),
			mt_rand(97, 122),
			mt_rand(1, 999));
		return $pwd;
	}

	/**
	 * 檢查密碼格式
	 * @param string $user 帳號
	 * @param string $pwd 密碼
	 * @return int 檢查結果
	 */
	function check_pwd($user, $pwd){
		if (strlen($pwd) < pwdMinLength)
			return pwdTooShort;										// 密碼太短

		if (!eregi('^[0-9a-z]+$', $pwd)) return pwdOk;

		if ($pwd == $user){
			return pwdTooEasyIsId;									// 密碼就是帳號
		}

		if ($pwd == strrev($user)){
			return pwdTooEasyIsIdReverse;							// 密碼就是帳號反轉
		}

		if (preg_match('/^(.)\1*$/', $pwd)){
			return (pwdTooEasyDigitRepeat | pwdTooEasyAlphaRepeat); // 密碼是相同的一個 ASCII
		}

		$pattern = '0123456789';
		if (strpos($pattern, $pwd) !== false ||
		    strpos(strrev($pattern), $pwd) !== false
		   )
			return pwdTooEasyDigitSequence;							// 密碼是數字序列

		$pattern = 'abcdefghijklmnopqrstuvwxyz';
		if (strpos($pattern, $pwd) !== false ||
		    strpos(strrev($pattern), $pwd) !== false ||
		    strpos(strtoupper($pattern), $pwd) !== false ||
		    strpos(strtoupper(strrev($pattern)), $pwd) !== false
		   )
			return pwdTooEasyAlphaSequence;							// 密碼是字母序列

		return pwdOk;
	}

	/**
	 * addUser()
	 *     新增一位使用者
	 * @param string $username : 要新增的帳號
	 * @param array  $data     : 這個使用者其它的資料
	 * @param string $enable   : 是否啟用這個帳號
	 * @param string $lang     : 指定預設的語系
	 * @param string $type     : 塞入sql檔案的階段
	 * @param string $time_stamp     : 檔案名稱用time_stamp做區隔
	 * @return
	 *     -1 : 新增成功，但需管理者審核
	 *     0  : 新增成功
	 *     1  : 保留的帳號
	 *     2  : 帳號使用中
	 *     3  : 帳號格式不符合
	 *     4  : 不可以註冊 (學校管理者設定該校不可自行註冊)
	 *          這一項還需要修改，可能會參考到權限控管的部分
	 **/
	function addUser($username, $data, $enable='', $lang='', $type='', $time_stamp='') {
		global $_SERVER, $sysConn, $sysSession;
		$username = trim($username);
		// $error_no = checkUsername($username);

		// if ($error_no > 0) return $error_no;

		// 總共需要新增到三個 Table
		// 1. WM_MASTER -> WM_sch4user
		// 1. WM_MASTER -> WM_all_account
		// 1. WM_{$school_id} -> WM_user_account
		if (empty($enable) || empty($lang)) {
			$RS = dbGetStSr('WM_school', 'language, canReg', "school_id={$sysSession->school_id} AND school_host='{$_SERVER[HTTP_HOST]}'", ADODB_FETCH_ASSOC);

			// 這邊需要依照是不是管理者或是使用者自行註冊
			if (empty($lang)) $lang = empty($RS['language']) ? sysDefaultLang : $RS['language'];
			if (empty($enable)) {
				$enable = 'N';
				if (eregi('^/academic/stud/', $_SERVER['PHP_SELF'])) {
					$enable = 'Y';
				} else {
					if ($RS['canReg'] == 'N') return 4;
					if ($RS['canReg'] == 'C') $enable = 'N';
					if ($RS['canReg'] == 'Y') $enable = 'Y';
				}
			}
		}
		// 帳號啟用時間與終止時間的預設值為NULL
		$time_ary['begin_time']  = 'NULL';
		$time_ary['expire_time'] = 'NULL';

		$values_ary['username']       = "'{$username}'";
		$values_ary['password']       = "''";
		$values_ary['enable']         = "'{$enable}'";
		$values_ary['first_name']     = "''";
		$values_ary['last_name']      = "''";
		$values_ary['gender']         = "'F'";
		$values_ary['birthday']       = 'NULL';
		$values_ary['personal_id']    = "''";
		$values_ary['email']          = "''";
		$values_ary['homepage']       = "''";
		$values_ary['home_tel']       = "''";
		$values_ary['home_fax']       = "''";
		$values_ary['home_address']   = "''";
		$values_ary['office_tel']     = "''";
		$values_ary['office_fax']     = "''";
		$values_ary['office_address'] = "''";
		$values_ary['cell_phone']     = "''";
		$values_ary['company']        = "''";
		$values_ary['department']     = "''";
		$values_ary['title']          = "''";
		$values_ary['language']       = "'{$lang}'";
		$values_ary['theme']          = "'default'";
		$values_ary['msg_reserved']   = 1;		// 是否要備份到訊息中心
		// MIS#19918 因為姓氏、名字已經無法設定不隱藏，故重新計算hid值，又避免誤算故改為『262076』(全部不顯示)
		// $values_ary['hid']            = 130971;
		$values_ary['hid']            = 262076;

		$fields = array_keys($values_ary);
/*
		$ary = array('password'  , 'enable'      , 'first_name' , 'last_name' ,'gender'         ,
					 'birthday'  , 'personal_id' , 'email'      , 'homepage'  , 'home_tel'      ,
					 'home_fax'  , 'home_address', 'office_tel' , 'office_fax', 'office_address',
					 'cell_phone', 'company'     , 'department' , 'title'     , 'language'      , 'theme');
*/
		$ary = array_slice($fields, 1, -2); // 去掉 username, msg_reserved, hid 這三個欄位
		$sql_fields = '`' . implode('`,`', $fields) . '`';

		// 帳號啟用時間與終止時間的key
		$aryTotime = array('begin_time', 'expire_time');

		if (is_array($data)) {
			while (list($key, $val) = each($data))
			{
				if (in_array($key, $ary))
				{
					if ($key == 'password')
					{
						$values_ary['password'] = "'" . $val . "'";
					}
					elseif ($key == 'gender')
					{
						if (strlen($val) > 0)
						{
							$values_ary[$key] = (($val == 'F') || ($val == 'M')) ? "'{$val}'" : "'F'";
						}
						else
						{
							$values_ary[$key] = "'F'";
						}
					}
					else
					{
						$values_ary[$key] = "'" . $val . "'";
					}
				}
				elseif (in_array($key, $aryTotime))
				{
					$time_ary[$key] = $val;
				}
			}
		}

		$fp_account_file  = sysDocumentRoot . "/base/{$sysSession->school_id}/account_{$time_stamp}.sql";
		$fp_sch4user_file = sysDocumentRoot . "/base/{$sysSession->school_id}/sch4user_{$time_stamp}.sql";
		$fp_user_file     = sysDocumentRoot . "/base/{$sysSession->school_id}/user_{$time_stamp}.sql";

		$values = implode(', ', $values_ary);
		$times  = implode(', ', $time_ary);
		if ($type == 'begin'){
			// WM_all_account
			$fp_account = fopen($fp_account_file, 'w');
			$string     = 'use ' . sysDBprefix . 'MASTER;' . "\n";
			$string    .= 'INSERT DELAYED IGNORE INTO `WM_all_account` (' . $sql_fields . ') VALUES ' . "\n";
			fputs($fp_account, $string);
			fclose($fp_account);

			// WM_sch4user
			$fp_sch4user = fopen($fp_sch4user_file, 'w');
			$string      = 'INSERT DELAYED IGNORE INTO `WM_sch4user` (`school_id`, `username`, `reg_time`, `begin_time`, `expire_time`) VALUES ' . "\n";
			fputs($fp_sch4user, $string);
			fclose($fp_sch4user);

			// WM_user_account
			$fp_user = fopen($fp_user_file, 'w');
			$string  = 'use ' . sysDBprefix . $sysSession->school_id . ';' . "\n";
			$string .= 'INSERT DELAYED IGNORE INTO `WM_user_account` (' . $sql_fields . ') VALUES ' . "\n";
			fputs($fp_user, $string);
			fclose($fp_user);
		}
		elseif ($type == 'middle')
		{
			// WM_all_account
			$fp_account = fopen($fp_account_file, 'a');
			$string     = ' (' . $values . '),';
			fputs($fp_account, $string);
			fclose($fp_account);

			// WM_sch4user
			$fp_sch4user = fopen($fp_sch4user_file,'a');
			$string      = ' (' . $sysSession->school_id . ',"' . $username . '",NOW(),' . $times . '),';
			fputs($fp_sch4user, $string);
			fclose($fp_sch4user);

			// WM_user_account
			$fp_user = fopen($fp_user_file, 'a');
			$string  = ' (' . $values . '),';
			fputs($fp_user, $string);
			fclose($fp_user);
		}
		elseif ($type == 'final')
		{
			$u = sysDBaccoount;
			$p = sysDBpassword;

			list($foo, $mysql_basedir) = $sysConn->GetRow('show variables like "basedir"');

			$mysql = $mysql_basedir . 'bin/mysql';
			if (!file_exists($mysql) || !is_executable($mysql))
			{
				$mysql = exec("sh -c 'PATH=/usr/local/mysql/bin:/usr/bin:/usr/local/bin:/home/apps/mysql/bin which mysql'");
				if (!preg_match('!^(/\w+)+$!', $mysql)) die('"mysql" not found.');
			}
			if (!file_exists($mysql) || !is_executable($mysql)) die('"mysql" not found or not executable.');
			$mysql .= (sysDBhost == 'localhost' ? ' -S /tmp/mysql.sock' : (' -h ' . str_replace(':', ' -P ', sysDBhost)));

			$fp = popen("{$mysql} -u {$u} -p{$p}", 'w');

			// WM_all_account
			$string = file_get_contents($fp_account_file);
			if (substr($string, -1) == ',') fputs($fp, substr_replace($string, ';', -1));

			// WM_sch4user
			$string = file_get_contents($fp_sch4user_file);
			if (substr($string, -1) == ',') fputs($fp, substr_replace($string, ';', -1));

			// WM_user_account
			$string = file_get_contents($fp_user_file);
			if (substr($string, -1) == ',') fputs($fp, substr_replace($string, ';', -1));

			pclose($fp);

			@unlink($fp_account_file);
			@unlink($fp_sch4user_file);
			@unlink($fp_user_file);
		}
		elseif(empty($type) && !empty($username) && is_array($data) )
		{
			$r = dbNew('WM_sch4user', 'school_id, username, reg_time', "'{$sysSession->school_id}', '{$username}', NOW()");
			if ($r)
			{
				$r = dbNew('WM_all_account', $sql_fields, $values);
				if ($r)
				{

                    // 動態取得資料表欄位
                    $tbAll = wm_gettbcols(sysDBname, 'WM_all_account');
                    $tbUser = wm_gettbcols(sysDBschool, 'WM_user_account');
                    $sameCols = array();
                    $sameCols = array_intersect($tbAll, $tbUser);
                    $sameCols = implode(',', $sameCols);

                    /* #59589 MOOC(B) 帳號中心整合，WM_user_account 改 view WM_all_account，不需再另外儲存 */
                    return ($enable == 'Y') ? 0 : -1;
                    /*
					$r = $sysConn->Execute("INSERT INTO " . sysDBprefix . "{$sysSession->school_id}.WM_user_account({$sameCols}) SELECT {$sameCols} FROM " . sysDBname . ".WM_all_account where " . sysDBname . ".WM_all_account.username='{$username}'");
					if ($r)
					{
						return ($enable == 'Y') ? 0 : -1;
					}
					else
					{
					    $m = $sysConn->ErrorNo() . ' ==> ' . $sysConn->ErrorMsg();
					    dbDel('WM_sch4user', "school_id={$sysSession->school_id} and username='{$username}' limit 1");
					    dbDel('WM_all_account', "username='{$username}' limit 1");
						die('WM_user_account : ' . $m);
					} 
                     */
                    /* #59589 MOOC(E) 帳號中心整合 */
				}
				else
				{
				    $m = $sysConn->ErrorNo() . ' ==> ' . $sysConn->ErrorMsg();
				    dbDel('WM_sch4user', "school_id={$sysSession->school_id} and username='{$username}' limit 1");
					die('WM_all_account : ' . $m);
				}
			}
			else
			{
			    $m = $sysConn->ErrorNo() . ' ==> ' . $sysConn->ErrorMsg();
				die('WM_sch4user : ' . $m);
			}
		}
	}



	/**
	 * delUser()
	 *     刪除一位使用者
	 * @param $username 要刪除的帳號
	 * @return
	 *     0  : 刪除成功
	 *     1  : 帳號不存在
	 *     2  : 刪除失敗
	 **/
	function delUser($username) {
		global $_SERVER, $sysConn, $sysSession;

		$res = -1;
		$res = checkUsername($username);
		if ($res == 0) return 1;
		if ($res == 1) return 2;
		$RS = dbDel('WM_all_account', "username='{$username}'");
		if ($RS) {
			$sysConn->Execute('use ' . sysDBname);
			dbDel('WM_sch4user'   , "username='{$username}'");
			return 0;
		} else {
			return 2;
		}
	}

	/**
	 * 取得使用者的詳細資料
	 * @version 1.0
	 * @param string $username : 帳號
	 * @return array $user : 陣列形式的使用者詳細資料
	 **/
	$userDetailData = array();
	function getUserDetailData($username) {
		global $sysSession, $userDetailData;

		$user = array();
		if (array_key_exists($username, $userDetailData)) return $userDetailData[$username];
		$user = dbGetStSr('WM_user_account', '*', "`username`='{$username}'", ADODB_FETCH_ASSOC);

        $realname = checkRealname($user['first_name'], $user['last_name']);
		// 列出真實姓名 (End)
		$user['realname'] = htmlspecialchars($realname);
		$userDetailData[$username] = $user;
		return $user;
	}



	/**
	 * 建立使用者的目錄
	 * @TODO 需要考慮在 Windows 系統上的大小寫不分的問題
	 * @param string $username : 帳號
	 * @return string : 完整的目錄名稱
	 **/
	if (!function_exists('MakeUserDir')) {
		function MakeUserDir($username) {
			$username = trim($username);
			// 取出前兩個字元
			$one = substr($username, 0, 1);
			$two = substr($username, 1, 1);

			// 檢查使用者的目錄存不存在
			$filename = sysDocumentRoot . DIRECTORY_SEPARATOR . 'user';
			if (!@is_dir($filename)) @mkdir($filename);
			$filename .= DIRECTORY_SEPARATOR . $one;
			if (!@is_dir($filename)) @mkdir($filename);
			$filename .= DIRECTORY_SEPARATOR . $two;
			if (!@is_dir($filename)) @mkdir($filename);
			$filename .= DIRECTORY_SEPARATOR . $username;
			if (!@is_dir($filename)) @mkdir($filename);

			return $filename;
		}
	}

	/**
	 * 取得個人相片
	 * @param string  $username : 帳號
	 * @param boolean $display  : 是否直接輸出
	 *     true  : 直接輸出
	 *     false : 回傳讀取的結果
	 * @return
	 **/
	function getUserPic($username, $display=true) {
		global $sysSession;
        $res = checkUsername($username);
        if (($res != 1) && ($res != 2) && ($res !=4)) {
            header('HTTP/1.1 403 Forbidden');
            exit;
        }
		list($pic) = dbGetStSr('WM_user_picture', 'picture', "username='{$username}'", ADODB_FETCH_NUM);
		$hidd = dbGetOne('WM_user_account', 'hid', "username='{$username}'", ADODB_FETCH_ASSOC);
                
		if (empty($pic)){
			$filename = sysDocumentRoot . "/theme/{$sysSession->theme}/learn/co_person.png";
			$pic      = file_get_contents($filename);
		}

		if ($_SERVER['SCRIPT_URL']=='/co_showuserpic.php' && ($hidd&32)) {
			$filename = sysDocumentRoot . "/theme/{$sysSession->theme}/learn/co_person.png";
			$pic      = file_get_contents($filename);
		}

		if (!$display) return $pic;
        
		$len = strlen($pic);
		header('Last-Modified: Thu, 01 Jan 1970 00:00:00 GMT');
		header('Expires: ' . gmdate('r', time()+259200)); // 三天時效
		if ($filename) {
			$type = mime_content_type($filename);
		} else {
			$finfo = new finfo(FILEINFO_MIME);
            $type  = $finfo->buffer($pic);
            if (strpos($type,'bmp')!==false) {
                $type  = 'image/bmp'; 
            }
		}
		header("Content-type: {$type}");
		header('Content-transfer-encoding: binary');
		header('Content-Disposition: filename=picture.jpg');
		header('Accept-Ranges: bytes');
		header("Content-Length: {$len}");
		echo $pic;
	}

    /**
     * 是否為FDA的人員
     * @param  string  $user 帳號
     * @return boolean       
     */
    function isFDAMember($user)
    {
        $rtn = checkUsername($user);
        if (($rtn != 2) && ($rtn != 4)) return false;
        $CO_fda_member = dbGetOne('WM_all_account','CO_fda_member',sprintf("username='%s'",$user));
        return ($CO_fda_member=='Y')?true:false;
    }

    /**
	*   後台判斷密碼規則
	*	@return string password error : 密碼規則錯誤
	*                  sysRootAccount : 是root，不加判斷
	*
	*
    */
    function isPassPasswordCondition($password = ''){
    	global $sysSession;
    	if($sysSession->username == sysRootAccount){
    		return sysRootAccount;
    	}else if($sysSession->username != sysRootAccount  && !empty($password) ){

    		if(strlen($password)  < 8 || strlen($password) > 20){
    			return 'password error';
    		}
		    $saveStatus = array_fill_keys(array('lower', 'upper', 'number', 'specal'), 0);
		    for($i=0; $i<strlen($password); $i++){
		        $encode = ord($password[$i]);
		        if($encode >= 97 && $encode <= 122){
		            $saveStatus['lower'] ++;
		        }else if ($encode >= 65 && $encode <= 90){
		            $saveStatus['upper'] ++;
		        }else if($encode >= 48 && $encode <= 57){
		            $saveStatus['number'] ++;
		        }else{
		            $saveStatus['specal'] ++;
		        }
		    }
		    $status = 0;
		    foreach ($saveStatus as $key => $value) {
		        if($value != 0){
		            $status ++;
		        }
		    }
		    if($status < 3){
		        return "password error";
		    }
		}else{
			 return "password error";
		}
    }

    function IsWithPassrordHistory($username, $password){
    	if($username == sysDocumentRoot) return false;
    	$p = md5($password);
    	$passwordHistory = dbGetCol("CO_password_recond", "password", "username='{$username}' ORDER BY recond_time  DESC LIMIT 5");
    	if(!empty($passwordHistory)){
    		return in_array($p, $passwordHistory);
    	}
    	return false;
    }
?>

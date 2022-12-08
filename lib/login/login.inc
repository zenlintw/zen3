<?php
	require_once(sysDocumentRoot . '/lib/lib_ini.php');
	require_once(sysDocumentRoot . '/lib/username.php');

	// 離開此程式，導向其他網頁
	function exit_func($url) {
		$url = trim($url);   // 清除多餘的空白
		// 檢查是不是從學生環境導過來的 (End)
		if ($_SERVER['HTTPS'])
		{
			if (strpos($url, 'http://') === 0)
				header('Refresh: 0; URL="' . $url . '"');
			elseif (strpos($url, '/') === 0)
				header('Refresh: 0; URL="'.(($_SERVER['SERVER_PORT'] == '443')?'https':'http').'://' . $_SERVER['HTTP_HOST'] . (($_SERVER['SERVER_PORT'] == '80' || $_SERVER['SERVER_PORT'] == '443') ? '' : (':' . $_SERVER['SERVER_PORT'])) . $url . '"');
			else
                header('Refresh: 0; URL="'.(($_SERVER['SERVER_PORT'] == '443')?'https':'http').'://' . $_SERVER['HTTP_HOST'] . (($_SERVER['SERVER_PORT'] == '80' || $_SERVER['SERVER_PORT'] == '443') ? '' : (':' . $_SERVER['SERVER_PORT'])) . dirname($_SERVER['PHP_SELF']) . $url . '"');
		}
		else
			header('Location: ' . $url);
		exit;
	}

	function exit_func_with_msg($msg, $url) {
		global $_POST;
		$url = trim($url);   // 清除多餘的空白
		echo '<script type="text/javascript" language="javascript">',
		     'window.onload = function () {',
		     'alert("', $msg, '");',
		     'location.replace("', $url, '");',
		     '};',
		     '</script>';
		exit;
	}
	/*
	 * 流程：首頁 -> 允許 guest -> 進入 [個人區 - 我的課程 - 全校課程]
	 *       選完課之後 -> 幫 user login -> 送出選課清單
	 */
	function login_func($user_act,$course_ids,$url) {
		global $_POST,$sysSession;
		$url = trim($url);   // 清除多餘的空白
		// 檢查是不是從學生環境導過來的 (Begin)
		if (isset($_POST['referer_source']) && (trim($_POST['referer_source']) == 'pass_from_learn'))
		{
			include_once(sysDocumentRoot . '/lib/interface.php');
			include_once(sysDocumentRoot . '/lang/mycourse.php');

			showXHTML_script('include', '/lib/xmlextras.js');
			showXHTML_script('include', '/learn/mycourse/course_tree.js');
			if (empty($course_ids)) {
				echo <<< BOF
<script type="text/javascript" language="javascript">
<!--
	window.onload = function () {
		opener.parent.onbeforeunload = null;
		opener.parent.location.replace("{$url}");
		window.close();
	};
//-->
</script>
BOF;
	} else {
			echo <<< BOF
<script type="text/javascript" language="javascript">
<!--
	window.onload = function () {

		var xmlVars = null, xmlHttp = null, xmlDocs = null;

		var MSG_SYS_ERROR     = "{$MSG['msg_system_error'][$sysSession->lang]}";
		var MSG_COURSE_ADD    = "{$MSG['msg_add_course'][$sysSession->lang]}";
		var MSG_COURSE_DEL    = "{$MSG['msg_del_course'][$sysSession->lang]}";
		var MSG_SUCCESS       = "{$MSG['msg_success'][$sysSession->lang]}";
		var MSG_FAIL          = "{$MSG['msg_fail'][$sysSession->lang]}";
		var act = "{$user_act}";

		var obj = null, nodes = null, node = null;
		var txt = "";
		var res = 0;
		var csObj = null;

		if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
		if ((typeof(xmlVars) != "object") || (xmlVars == null)) xmlVars = XmlDocument.create();
		if ((typeof(xmlDocs) != "object") || (xmlDocs == null)) xmlDocs = XmlDocument.create();

		txt  = "<manifest>";
		txt += "<ticket></ticket>";
		txt += "<action>"+act+"</action>";
		txt += "<course_id>" + "{$course_ids}" + "</course_id>";
		txt += "</manifest>";

		res = xmlVars.loadXML(txt);
		if (!res) {
			alert(MSG_SYS_ERROR);
			return false;
		}
		xmlHttp.open("POST", "/learn/mycourse/do_function.php", false);
		xmlHttp.send(xmlVars);
		res = xmlVars.loadXML(xmlHttp.responseText);

		if (!res) {
			alert(MSG_SYS_ERROR);
			return false;
		}

		ticket = getNodeValue(xmlVars.documentElement, "ticket");
		res = getNodeValue(xmlVars.documentElement, "result");
		txt = (act == "major_add") ? MSG_COURSE_ADD : MSG_COURSE_DEL;
		txt += (parseInt(res)) ? MSG_FAIL : MSG_SUCCESS;
		alert(txt);

		opener.parent.onbeforeunload = null;
		opener.parent.location.replace("{$url}");
		window.close();
	};
//-->
</script>
BOF;
	}
			return false;
		}
		// 檢查是不是從學生環境導過來的 (End)
		exit_func($url);
	}

	/**
	 * 檢查某人是否被禁止存取
	 * param string $user 帳號
	 * return bool true=禁止；false=可以存取
	 */
	function isDenyFromThat($user){
		global $sysSession;

		$cur_host = '';
		$rs = dbGetStMr('WM_ipfilter', 'host,mode', "username='$user' or username='' order by priority", ADODB_FETCH_NUM);
		$userip = wmGetUserIp();    // 取IP函式，宣告在 "config/db_initialize.php"

		if ($rs && $rs->RecordCount()) while(list($hosts,$mode) = $rs->FetchRow())
        {
			$isAllow = ($mode == 'allow');
            $hs = preg_split('/[\s,;]+/', $hosts, -1, PREG_SPLIT_NO_EMPTY);
            if (count($hs) == 0) $hs = array('');

			// for begin
			foreach($hs as $rhost) // 比對一個規則裡所輸入的所有 IP 或 DomainName
			{
				$i			= 0;
				$host_match = true;
				// 是否是 IP
				if (preg_match('/^([12][0-9][0-9]|[0-9]?[0-9]|\*)(\.([12][0-9][0-9]|[0-9]?[0-9]|\*)?){0,3}$/', $rhost))
				{
				    $uhosts = explode('.', $userip);
				    foreach(explode('.', $rhost) as $rh)
				    {
				        if ($rh != $uhosts[$i++] && $rh != '' && $rh != '*')
				        {
				            $host_match = false;
				            break;
				        }
				    }
				}
				// 或者是 DomainName
				elseif($rhost != '')
				{
				    if (empty($udomains))
				    {
				        $udomains = array_reverse(explode('.', gethostbyaddr($userip)));
				    }

				    foreach(array_reverse(explode('.', $rhost)) as $rh)
				    {
				        if (strcasecmp($rh, $udomains[$i++]) !== 0 && $rh != '' && $rh != '*')
				        {
				            $host_match = false;
				            break;
				        }
				    }
				}

				if ($host_match) break; // 其中一個 IP(Domain Name) 吻合則不必再比
			}
			// for end

            if ($host_match)
            {
                if ($isAllow)
                    return false;   // allow
                else
                {
                    wmSysLog($sysSession->cur_func, $sysSession->school_id ,0 ,1, 'others', $_SERVER['PHP_SELF'], '禁止登入!');
			        return true;    // deny
                }
            }
            else // 這個 else 是比較有爭議的，為了防 user 忽略了【無條件最終原則】
            {
                if ($isAllow)
                {
                    wmSysLog($sysSession->cur_func, $sysSession->school_id ,0 ,1, 'others', $_SERVER['PHP_SELF'], '禁止登入!');
			        return true;    // deny
                }
            }
		}
		return false;	// 允許存取：【無條件最終原則】
	}

	// 取得使用者在all_account的密碼
	function getUserEncPassword($user)
	{
		list($password) = dbGetStSr('WM_all_account', 'password', "username='" . $user . "'", ADODB_FETCH_NUM);

		if ($password === false){
			setLoginProcLog(3,"unknown account");
			return '';
		}
		return $password;
	}

	//將all_account的資料複製到user_account
	function syncUserinfo2School($user)
	{
		global $sysSession, $sysConn;
		dbNew('WM_sch4user', "school_id, username, reg_time, login_times", "'{$sysSession->school_id}', '{$user}', NOW(), 1");
		$sysConn->Execute("INSERT INTO " . sysDBprefix . "{$sysSession->school_id}.WM_user_account SELECT * FROM " . sysDBname . ".WM_all_account where " . sysDBname . ".WM_all_account.username='{$user}'");
	}

	// 取得學校在帳號新增的設定狀態
	function getSchoolRegStatus()
	{
		global $sysSession;
		list($canReg) = dbGetStSr('WM_school', 'canReg', "school_id='" . $sysSession->school_id . "' and school_host='".$_SERVER['HTTP_HOST']."'", ADODB_FETCH_NUM);
		return $canReg;
	}

	// 是否在此分校有此使用者的帳號
	function isSchoolUserExisted($user)
	{
		list($ct) = dbGetStSr('WM_user_account', 'count(*) as ct ', "username='" . $user . "'", ADODB_FETCH_NUM);
		return (bool)$ct;
	}

	// 由WM3 取得使用者資訊
	function &getUserInfoFromWM3($user, $skipPasswd = false, $return = false)
	{
		global $sysSession;

        if (!$skipPasswd) {
            $userpwd = getUserEncPassword($user);
            $_POST['password'] = getRestorePassword($userpwd);	// 還原前端加密的密碼

            if (md5($_POST['password']) != $userpwd) {
                sleep(5); // 延時 5 秒，避免暴力 try 密碼
                setLoginProcLog(4,'incorrect password');
                if ($return) {
                    return false;
                }
                // mooc 模組未開啟的話將網頁導向/sys/login_fault.php
                if (!defined('sysEnableMooc') || !(sysEnableMooc > 0)) {
                        exit_func('/sys/login_fault.php?' . $user);
                }else{
                    // IRS登入失敗導回原登入頁
                    if (isset($_POST['irsGoto'])){
                        exit_func('/mooc/irs/login.php?idx='.urlencode(base64_encode($user)).'&goto='.$_POST['irsGoto']);
                    }
                    // 點名報到：登入失敗導回原登入頁
                    if (isset($_POST['rollcallGoto'])){
                        exit_func('/mooc/teach/rollcall/login.php?idx='.urlencode(base64_encode($user)).'&goto='.$_POST['rollcallGoto']);
                    }
                    // 登入失敗時將課程ID放在 reurl 用GET方式回傳以便判斷從哪個課程登入
                    if(isset($_POST['reurl']) && $_POST['reurl'] !== ''){
                        exit_func('/mooc/login.php?idx='.urlencode(base64_encode($user)).'&reurl='.urlencode($_POST['reurl']));
                    }else{
                        exit_func('/mooc/login.php?idx='.urlencode(base64_encode($user)));
                    }
                }
            }
        }

        if ((!isSchoolUserExisted($user)) && ($user != 'root')) {
            /* [MOOC](B) #57892 限制分校代使用者註冊的機制 2014/12/19 By Spring */
            // 判斷學校類型
            $currDa = getConstatnt($sysSession->school-id);
            if ($currDa["is_portal"] === "1") {
                // 入口網校 (or MOOCs)
                $currSch = 0;
            } else {
                if ($currDa["is_independent"] === "0") {
                    // 內容商
                    $currSch = 2;
                } else {
                    // 獨立校 (or本校)
                    $currSch = 1;                            
                }
            }
            /* [MOOC](E) #57892 */
            // 如果是入口網校或者內容商，則依據開放註冊與否同步使用者
            // 目前使用 WM_all_account 所以不需同步使用者
            if ($currSch == 0 || $currSch == 2) {
                switch(getSchoolRegStatus())
                {
                    case "Y":
                            // syncUserinfo2School($user);
                            // dbSet("WM_user_account", "enable='Y'", "username='{$user}'");
                            break;
                    case "N":
                            if ($return) {
                                return false;
                            }
                            if (!defined('sysEnableMooc') || !(sysEnableMooc > 0)) {
                                    exit_func('/sys/login_fault.php?' . $user);
                            }else{
                                    exit_func('/mooc/login.php?idx='.urlencode(base64_encode($user)));
                            }
                            break;
                    case "C":
                            // syncUserinfo2School($user);
                            // dbSet("WM_user_account", "enable='N'", "username='{$user}'");
                            exit_func_with_msg(iconv("big5","utf-8","已將您的帳號送交審核！"),"/index.php");
                            break;
                }                
            } else {
                if ($return) {
                    return false;
                }
                if (!defined('sysEnableMooc') || !(sysEnableMooc > 0)) {
                        exit_func('/sys/login_fault.php?' . $user);
                }else{
                        exit_func('/mooc/login.php?idx='.urlencode(base64_encode($user)));
                }
            }
		}
		// 設定使用者資訊，回傳結果
		if ($user == 'root') {
			$userinfo = dbGetStSr('WM_all_account', '*', 'username="' . $user . '"', ADODB_FETCH_ASSOC);
		} else {
			$userinfo = dbGetStSr('WM_user_account', '*', 'username="' . $user . '"', ADODB_FETCH_ASSOC);
		}
		return $userinfo;
	}

	// 檢查此request是否由首頁登入, 由LoginKey
	function checkLoginKey()
	{
		global $_ENV, $_POST;
		if (!eregi('^[0-9A-Z]{32}$', $_POST['login_key']))
		{
			setLoginProcLog(1,"miss login_key");
			return false;
		}

		list($uid) = dbGetStSr('WM_prelogin', 'uid', "login_seed='{$_POST['login_key']}' and (UNIX_TIMESTAMP() - UNIX_TIMESTAMP(log_time) < 1200)", ADODB_FETCH_NUM);
		if (!eregi('^[0-9A-Z]{32}$', $uid) || (md5(sysSiteUID . sysTicketSeed . $uid) != $_POST['login_key']))
		{
		   	setLoginProcLog(2,"fake login_key");
			return false;
		}

		return true;
	}

	// 還原前端加密的密碼
	function getRestorePassword($encpwd)
	{
		global $_POST;
		$key = substr($encpwd, 0, 4) . substr($_POST['login_key'], 0, 4);
		$pwd = @mcrypt_decrypt(MCRYPT_DES, $key, base64_decode($_POST['encrypt_pwd']), MCRYPT_MODE_ECB);
		dbDel('WM_prelogin', "login_seed='{$_POST['login_key']}'");
		return trim($pwd);
	}

	// 驗證帳號使用期限
	function isUserAccountExpired()
	{
		global $sysSession, $_POST;
		$sqls = 'IF( (ISNULL(begin_time)  || (begin_time  = "0000-00-00")),-1,begin_time > CURDATE()) AS begin,' .
				'IF( (ISNULL(expire_time) || (expire_time = "0000-00-00")),-1,CURDATE() > expire_time)  AS expire ';
		$RS = dbGetStSr('WM_sch4user', $sqls, "school_id={$sysSession->school_id} AND username='{$_POST['username']}'", ADODB_FETCH_ASSOC);

		//帳號是否超過使用期限 (WM_MASTER.WM_sch4user)
		/* begin_time 與 expire_time 的狀態
		 *     -1 : 沒有設定
		 *      0 : 尚未過期或已經到了啟用日期
		 *      1 : 過期或尚未到啟用日期
		 */
		if ($RS['begin'] > 0) {
			setLoginProcLog(7,'account untimely');
			exit_func('/sys/use_date.php?'. $_POST['username']);
		}

		// 5.2. 是否過期
		if ($RS['expire'] > 0) {
			setLoginProcLog(8,'account expired');
			exit_func('/sys/use_date.php?'. $_POST['username']);
		}
	}

	//使用者是否為第一次登入
	function isFirstLogin($user)
	{
		global $sysSession, $_POST;
		list($isFirstLogin) = dbGetStSr('WM_sch4user', "login_times", "school_id={$sysSession->school_id} AND username='{$user}'", ADODB_FETCH_NUM);
		return ($isFirstLogin <= 1);
	}

	/**
	 * 判斷使用者是否資料不齊全
	 * @param string $user username
	 * @return boolean true  : incomplete
	 *                 false : complete
	 */
	function isUserDataIncomplete($user)
	{
		$user_data = dbGetRow('WM_all_account', '*', "username='{$user}'", ADODB_FETCH_ASSOC);
		if (empty($user_data['password']  ) ||
		    (strlen($user_data['first_name']) == 0) ||
		    (strlen($user_data['last_name'] ) == 0) ||
		    (strlen($user_data['email']) == 0) ||
		    ((strlen($user_data['home_tel']) == 0) && (strlen($user_data['office_tel']) == 0) && (strlen($user_data['cell_phone'])==0))
		   )
			return true;
		return false;
	}

	// 記錄登入錯誤的log
	function setLoginProcLog($result_id,$msg)
	{
            wmSysLog('600100100', $GLOBALS['sysSession']->school_id ,0 ,$result_id, 'others', "/login.php", $msg, htmlspecialchars($_POST['username']));
	}

	/**
	 * 設定個人ini檔案內容
	 *
	 * @param string $user : 使用者帳號
	 */
	function setUserIni($user)
	{
		global $_SERVER, $sysConn;

		// 建立儲存一些資訊的檔案 (Begin)
		$userDir = MakeUserDir($user);
		$filename = $userDir . '/my_configure_' . $GLOBALS['sysSession']->school_id . '.ini';
		@touch($filename);
		$objAssoc = new assoc_data();
		$objAssoc->has_sections = true;
		$objAssoc->setStorePath($filename);
		// 建立儲存一些資訊的檔案 (End)
		$objAssoc->restore();   // 恢復原本的資料
		if (isFirstLogin($user)) {
			// 有些使用者可能沒有留資料在 WM_MASTER.WM_sch4user 裡，在此將漏掉的資料補齊
			$RSS = dbNew('WM_sch4user',
						 'school_id, username, login_times, last_login, last_ip, reg_time, total_time',
						 "'{$GLOBALS['sysSession']->school_id}', '{$user}', 0, NOW(), '{$_SERVER['REMOTE_ADDR']}', NOW(), '0'");
			if ($sysConn->ErrorNo() > 0) {
				$RSS = dbSet('WM_sch4user',
							 "`login_times`=`login_times`+1, `last_login`=NOW(), `last_ip`='{$_SERVER['REMOTE_ADDR']}'",
							 "school_id={$GLOBALS['sysSession']->school_id} AND username='{$user}'");
			}

            // 動態取得資料表欄位
            $tbAll = wm_gettbcols(sysDBname, 'WM_all_account');
            $tbUser = wm_gettbcols(sysDBschool, 'WM_user_account');
            $sameCols = array();
            $sameCols = array_intersect($tbAll, $tbUser);
            $sameCols = implode(',', $sameCols);
			$sysConn->Execute(sprintf('insert into %s.WM_user_account(%s) select %s from %s.WM_all_account where `username`="%s"', sysDBschool, $sameCols, $sameCols, sysDBname, $user));
			if ($user == sysRootAccount) {
				dbNew('WM_manager', '`username`, `school_id`, `level`', "'".sysRootAccount . "', {$GLOBALS['sysSession']->school_id}, 8192");
			}
			$objAssoc->setValues('login_info', 'login_times', 1);
			$objAssoc->setValues('login_info', 'last_login' , '');
			$objAssoc->setValues('login_info', 'last_ip'    , '');
			$objAssoc->setValues('login_info', 'total_time' , '0');
		} else {
			list($lt, $ll, $li, $tt) = dbGetStSr('WM_sch4user', '`login_times`, `last_login`, `last_ip`, `total_time`', "school_id={$GLOBALS['sysSession']->school_id} AND username='{$user}'", ADODB_FETCH_NUM);
			$RSS = dbSet('WM_sch4user',
						 "`login_times`=`login_times`+1, `last_login`=NOW(), `last_ip`='{$_SERVER['REMOTE_ADDR']}'",
						 "school_id={$GLOBALS['sysSession']->school_id} AND username='{$user}'");

			$objAssoc->setValues('login_info', 'login_times', intval($lt) + 1);
			$objAssoc->setValues('login_info', 'last_login' , $ll);
			$objAssoc->setValues('login_info', 'last_ip'    , $li);
			$objAssoc->setValues('login_info', 'total_time' , $tt);
		}
		$objAssoc->store();   // 儲存
	}

	/**
	 * 移除舊的(原畫面)所傳來的$_COOKIE['idx']的Session資料。
	 * 此情形是因為使用者登出時，cookie中的idx值仍在，因此在登入時，將同畫面的
	 * Cookie值移除。
	 *
	 * @param string $old_idx : 舊的$_COOKIE['idx']
	 */
	function removeExpiredSessionIdx($old_idx)
	{
		global $_SERVER;
		dbDel('WM_session', "idx='{$old_idx}'");
		wmSysLog($GLOBALS['sysSession']->cur_func, $GLOBALS['sysSession']->school_id ,0 ,0, 'others', $_SERVER['PHP_SELF'], 'user logout!');
	}

	/**
	 * 移除使用者之前的ftp auth資料
	 * @param string $user : 使用者帳號(optional),未沒有此參數值，則全面對資料庫核對
	 */
	function removeExpiredFtpAuth($user='')
	{
		if ($user == '')
		{
		    $schools = dbGetCol('WM_school','school_id','1 group by school_id order by school_id');
		    if (count($schools) == 1) {
		        $sqls = 'select A.userid from ' . sysDBname . '.WM_auth_ftp as A left join ' . sysDBschool . '.WM_session as B on A.userid=B.username where isnull(B.username)';
		    }else{
		        $schSess = array();
		        for ($i = 0, $size=count($schools); $i < $size; $i++) {
		            $schSess[] = sprintf('(SELECT username FROM %s%d.`WM_session`)',sysDBprefix,$schools[$i]);
		        }
		        $sqls = 'select A.userid from ' . sysDBname . '.WM_auth_ftp as A left join ('. implode(' union ', $schSess) .') as B on A.userid=B.username where isnull(B.username)';
		    }

			$uids = $GLOBALS['sysConn']->GetCol($sqls);
			if (is_array($uids) && count($uids))
				dbDel('WM_auth_ftp', 'userid in ("' . implode('","', $uids) . '")');
		}else{
			dbDel('WM_auth_ftp', "userid='{$user}'");
		}
	}

	/**
	 * 累計使用者此Session在站上的累積時間，提供logout程序呼叫
	 *
	 * @param string $idx : 使用者的Session index
	 */
	function updateSiteAccTime($idx)
	{
		list($basetime) = dbGetStSr('WM_session','touch',"idx='{$idx}'", ADODB_FETCH_NUM);
		$basetime = $GLOBALS['sysConn']->UnixTimeStamp($basetime);
		$now_time = time();
		if ($now_time > $basetime)
		{
			$t = $now_time-$basetime;
			dbSet('WM_sch4user', "`total_time`=IFNULL(`total_time`, 0) + {$t}", "school_id={$GLOBALS['sysSession']->school_id} AND username='{$GLOBALS['sysSession']->username}'");
		}
	}

	/**
	 * 記錄使用者上次登入的時間 -- 提供logout程序呼叫
	 *
	 * @param unknown_type $sid
	 * @param unknown_type $user
	 */
	function setLastLoginRec($sid, $user)
	{
		global $_SERVER;
		list($ll) = dbGetStSr('WM_sch4user', '`last_login`', "school_id={$sid} AND username='{$user}'", ADODB_FETCH_NUM);
		$st = intval(time()) - intval($GLOBALS['sysConn']->UnixTimeStamp($ll));
		wmSysLog($GLOBALS['sysSession']->cur_func, $sid ,0 ,0, 'others', $_SERVER['PHP_SELF'], 'session total time: ' . $st);
	}
        
        /**
        * @name 取得使用者所註冊過的學校
        * @author spring
        *
        * @param string $username:帳號
        * @return string $rtn: array:school_id、X:沒資料
        */
        function getUserRegisteredSchoolId($username = '')
        {
            $data = array();
            
            $cols = 'school_id';
            $tb = 'WM_sch4user';
            $where  = sprintf(
                '`username` = \'%s\' ',
                $username
            );

            $rs = dbGetStMr($tb, $cols, $where, ADODB_FETCH_ASSOC);
            if ($rs && $rs->RecordCount() >= 1) {
                while (!$rs->EOF) {
                    $data[] = $rs->fields['school_id'];
                    $rs->MoveNext();
                }
            } else {
                return 'X';
            }

           return $data;
       }
?>

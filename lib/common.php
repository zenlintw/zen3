<?php
	/**
     * 將一些常用的function放置一起
     *
     * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
     *          則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
     *          照書中所限制之使用範圍使用之，否則仍以侵權論究。
     *
     * @author      EdiChen <edi@sun.net.tw>
     * @copyright   2005-2006 SunNet Tech. INC.
     * @version     CVS: $Id: common.php,v 1.1 2009-06-25 09:26:48 edi Exp $
     * @since       2007-02-14
     */
    if (!function_exists('getNodeValue')) {
	     /**
		 * 取得一個 Node 底下某一個節點中的值
		 * @param object $node : 要取值的節點
		 * @param string $tagName : 節點名稱
		 * @return string 所取得的值
		 **/
		function getNodeValue($node, $tagName) 
		{
			$result = '';
			$tmp = $node->get_elements_by_tagname($tagName);
			if (count($tmp) <= 0) return '';
			if ($tmp[0]->has_child_nodes()) 
			{
				$child = $tmp[0]->first_child();
				$result = $child->node_value();
			} else {
				$result = '';
			}
			return $result;
		}
    }

	/**
	 * 取得名稱
	 * @param string  $val  : 取得分解後的多語系標籤，且是尚未 unserialize 的
	 * @return array $lang
	 **/
	function getCaption($val) 
	{
		$lang = unserialize($val);

		if (is_array($lang)) 
		{
			foreach ($lang as $key => $val) 
			{
				$lang[$key] = htmlspecialchars($val);
			}
			foreach ($GLOBALS['sysAvailableChars'] as $key => $value) {
				if (!in_array($value,array_keys($lang))) {
					$lang[$value] = '';
				}
			}
		} else 
		{
			$lang['Big5']        = htmlspecialchars($val);
			$lang['GB2312']      = htmlspecialchars($val);
			$lang['en']          = htmlspecialchars($val);
			$lang['EUC-JP']      = htmlspecialchars($val);
			$lang['user_define'] = htmlspecialchars($val);
		}
		/*Custom 2017-11-22 *049131 (B)*/
		foreach ($lang as $key => $val) 
		{
			if(trim($val) == "" || trim($val)== "undefined" || trim($val)== "--=[unnamed]=--"){
				$lang[$key] = $lang[sysDefaultLang];
			}
		}
		/*Custom 2017-11-22 *049131 (E)*/
		return $lang;
	}

	function old_getCaption($val) 
	{
		$lang = unserialize($val);

		if (is_array($lang)) 
		{
			foreach ($lang as $key => $val) 
			{
				$lang[$key] = htmlspecialchars($val);
			}
		} else 
		{
			$lang['Big5']        = htmlspecialchars($val);
			$lang['GB2312']      = htmlspecialchars($val);
			$lang['en']          = htmlspecialchars($val);
			$lang['EUC-JP']      = htmlspecialchars($val);
			$lang['user_define'] = htmlspecialchars($val);
		}

		return $lang;
	}

	/**
	 * 取得管理者的權限
	 * @return level
	 *       0: 不具備管理者的權限
	 *    2048: 一般管理者
	 *    4096: 進階管理者
	 *	  8192: 最高管理者 (一機只有一人)
	 **/
	function getAdminLevel($username='') 
	{
		global $sysSession,$sysRoles;

		$username = trim($username);
		if (empty($username)) $username = $sysSession->username;

		$level = aclCheckRole($username, ($sysRoles['manager']|$sysRoles['administrator']|$sysRoles['root']), $sysSession->school_id, true);
		return $level ? $level : 0;
	}

	/**
	 * 檢查學校編號是否在規定的範圍內
	 * @param integer $sid : 學校編號
	 * @return integer $sid : 檢查後的學校編號
	 *         假如不在範圍內則回傳 false
	 **/
	function checkSchoolID($sid)
	{
		$sid = intval($sid);
		if (($sid <= 10000) || ($sid >= 100000)) 
		{
			return false;
		}
		return $sid;
	}

	/**
	 * 檢查班級編號是否在規定的範圍內
	 * @param integer $caid : 班級編號
	 * @return integer $caid : 檢查後的班級編號
	 *         假如不在範圍內則回傳 false
	 **/
	function checkClassID($caid) 
	{
		$caid = intval($caid);
		if (($caid <= 1000000) || ($caid >= 10000000)) 
		{
			return false;
		}
		return $caid;
	}

	/**
	 * 檢查課程編號是否在規定的範圍內
	 * @param integer $csid : 課程編號
	 * @return integer $csid : 檢查後的課程編號
	 *         假如不在範圍內則回傳 false
	 **/
	function checkCourseID($csid) 
	{
		$csid = intval($csid);
		if (($csid <= 10000000) || ($csid >= 100000000)) 
		{
			return false;
		}
		return $csid;
	}
	
	/**
	 * 檢查課程編號是否在規定的範圍內
	 * @param integer $csid : 課程編號
	 * @return integer $csid : 檢查後的課程編號
	 *         假如不在範圍內則回傳 false
	 **/
	function checkContentID($csid) 
	{
		$csid = intval($csid);
		if (($csid < 100000) || ($csid >= 10000000)) 
		{
			return false;
		}
		return $csid;
	}
	
	/**
	 * 檢查 IP 限制
	 * @param string  $username : 帳號，當帳號空白時，則讀取 $sysSession->username
	 * @param string  $env      : 環境，請參考程式最上方的說明
	 * @param integer $dsid     : 學校、班級或課程的編號
	 * @return integer
	 *     -4 : 課程編號錯誤
	 *     -3 : 班級編號錯誤
	 *     -2 : 學校編號錯誤
	 *     -1 : 缺少必要的參數
	 *      0 : 不允許
	 *      1 : 允許
	 *      2 : 沒有經過檢查直接允許
	 **/
	function checkIPLimit($username, $env, $dsid) {
		global $_SERVER, $sysSession;

		$username = trim($username);
		if (empty($username)) $username = $sysSession->username;

		$env = trim($env);
		if (empty($env)) return -1;

		$dsid = trim($dsid);

		switch ($env) {
			case 'academic' :
			case 'ep_academic' :
				if (empty($dsid)) $dsid = intval($sysSession->school_id);
				$dsid = checkSchoolID($dsid);
				if ($dsid === false) return -2;  // 學校編號錯誤
				list($hosts) = dbGetStSr('WM_manager', 'allow_ip', "`username`='{$username}' and `school_id`={$dsid}",ADODB_FETCH_NUM);
				break;

			case 'direct'   :
				if (empty($dsid)) $dsid = intval($sysSession->class_id);
				$dsid = checkClassID($dsid);
				if ($dsid === false) return -3;  // 班級編號錯誤
				list($hosts) = dbGetStSr('WM_class_director', 'allow_ip', "`username`='{$username}' and `class_id`={$dsid}",ADODB_FETCH_NUM);
				break;

			case 'teach'    :
				if (empty($dsid)) $dsid = intval($sysSession->course_id);
				$dsid = checkCourseID($dsid);
				if ($dsid === false) return -2;  // 課程編號錯誤
				list($hosts) = dbGetStSr('WM_term_teacher', 'allow_ip', "`username`='{$username}' and `course_id`={$dsid}",ADODB_FETCH_NUM);
				break;

			case 'learn'    :
				return 2;   // 目前不檢查學生環境的 IP 限制
				break;

			default:
				return -1;
		}

		// 檢查 IP 限制 (Begin)
		if ($hosts == '*')
			return 1;
		elseif ($hosts != '')
		{
			$allow = 0;
			$cur_host = '';
			foreach(preg_split('/\s+/', $hosts, -1, PREG_SPLIT_NO_EMPTY) as $host) {
				if (preg_match('/[a-zA-Z]/', $host)) {   // Domain name
					if ($cur_host == '') $cur_host = gethostbyaddr($_SERVER['REMOTE_ADDR']);
					if (strcasecmp(substr($cur_host, -strlen($host)), $host) == 0) {   // 如果 DN 後面字串相同
						$allow = 1; break;
					}
				} else {                                 // IP
					if (strpos($_SERVER['REMOTE_ADDR'], $host) === 0) {                // 如果 IP 前面字串相同
						$allow = 1; break;
					}
				}
			}
			return $allow;
		} else {
			return 2;
		}
		// 檢查 IP 限制 (End)
	}

	if (!function_exists('htmlspecialchars_decode'))
	{
	   function htmlspecialchars_decode($string, $quote_style = ENT_COMPAT)
	   {
	       return strtr($string, array_flip(get_html_translation_table(HTML_SPECIALCHARS, $quote_style)));
	   }
	}
	
	function isMobileBrowser() {
		if (isset($_SERVER)) {
			$sAgent = $_SERVER['HTTP_USER_AGENT'] ;
		} else {
			global $HTTP_SERVER_VARS;
			if (isset($HTTP_SERVER_VARS)) {
				$sAgent = $HTTP_SERVER_VARS['HTTP_USER_AGENT'] ;
			} else {
				global $HTTP_USER_AGENT;
				$sAgent = $HTTP_USER_AGENT ;
			}
		}
		
		if (preg_match("|AppleWebKit/(\d+)|i", $sAgent, $matches)) {
			if ((strpos($sAgent, 'iPad;') !== false) || (strpos($sAgent, 'iPod;') !== false) || (strpos($sAgent, 'iPhone;') !== false)) {
				return true;
			}
		}
		return false;
	}
    
    /**
     * 取指定欄位的序列化搜尋字串（限定只勾選繁簡體語系 ）
     * 
     * @param string $field
     * @param string $keyword
     * 
     * @return string $data
    **/
   function getColumnSerialQuery($field, $keyword) 
   {
//       global $sysConn;
//       $sysConn->debug = true;
       
        global $sysConn;
        // 考量簡式寫法無法應付全部情形，所以還是用寫死 5種語系的笨方法
        // a:5:{s:4:"Big5";s:33:"舊版 計算機概論--徐浩祥";s:6:"GB2312";s:9:"undefined";s:2:"en";s:9:"undefined";s:6:"EUC_JP";s:0:"";s:11:"user_define";s:0:"";}
        $data = ' (' . 
                $field . ' like ' . $sysConn->qstr('a:1:{s:4:"Big5"%s:%:"%' . $keyword . '%";}', get_magic_quotes_gpc()) . 
                ' OR ' . $field . ' like ' . $sysConn->qstr('a:1:{s:6:"GB2312"%s:%:"%' . $keyword . '%";}', get_magic_quotes_gpc()) . 
                ' OR ' . $field . ' like ' . $sysConn->qstr('a:1:{s:2:"en"%s:%:"%' . $keyword . '%";}', get_magic_quotes_gpc()) . 
                ' OR ' . $field . ' like ' . $sysConn->qstr('a:2:{s:4:"Big5"%s:%:"%' . $keyword . '%"%s:6:"GB2312"%s:%:%}', get_magic_quotes_gpc()) . 
                ' OR ' . $field . ' like ' . $sysConn->qstr('a:2:{s:4:"Big5"%s:%:%s:6:"GB2312"%s:%:"%' . $keyword . '%"%}', get_magic_quotes_gpc()) . 
                ' OR ' . $field . ' like ' . $sysConn->qstr('a:2:{s:4:"Big5"%s:%:"%' . $keyword . '%"%s:2:"en"%s:%:%}', get_magic_quotes_gpc()) . 
                ' OR ' . $field . ' like ' . $sysConn->qstr('a:2:{s:4:"Big5"%s:%:%s:2:"en"%s:%:"%' . $keyword . '%"%}', get_magic_quotes_gpc()) . 
                ' OR ' . $field . ' like ' . $sysConn->qstr('a:2:{s:6:"GB2312"%s:%:"%' . $keyword . '%"%s:2:"en"%s:%:%}', get_magic_quotes_gpc()) . 
                ' OR ' . $field . ' like ' . $sysConn->qstr('a:2:{s:6:"GB2312"%s:%:%s:2:"en"%s:%:"%' . $keyword . '%"%}', get_magic_quotes_gpc()) . 
                ' OR ' . $field . ' like ' . $sysConn->qstr('a:3:{s:4:"Big5"%s:%:"%' . $keyword . '%"%s:6:"GB2312"%s:%:%s:2:"en"%s:%:%}', get_magic_quotes_gpc()) . 
                ' OR ' . $field . ' like ' . $sysConn->qstr('a:3:{s:4:"Big5"%s:%:%s:6:"GB2312"%s:%:"%' . $keyword . '%"%s:2:"en"%s:%:%}', get_magic_quotes_gpc()) . 
                ' OR ' . $field . ' like ' . $sysConn->qstr('a:3:{s:4:"Big5"%s:%:%s:6:"GB2312"%s:%:%s:2:"en"%s:%:"%' . $keyword . '%"%}', get_magic_quotes_gpc()) . 
                ' OR ' . $field . ' like ' . $sysConn->qstr('a:4:{s:4:"Big5"%s:%:"%' . $keyword . '%"%s:6:"GB2312"%s:%:%s:2:"en"%s:%:%;s:6:"EUC_JP";s:%:"%";}', get_magic_quotes_gpc()) . 
                ' OR ' . $field . ' like ' . $sysConn->qstr('a:4:{s:4:"Big5"%s:%:%s:6:"GB2312"%s:%:"%' . $keyword . '%"%s:2:"en"%s:%:%;s:6:"EUC_JP";s:%:"%";}', get_magic_quotes_gpc()) . 
                ' OR ' . $field . ' like ' . $sysConn->qstr('a:4:{s:4:"Big5"%s:%:%s:6:"GB2312"%s:%:%s:2:"en"%s:%:"%' . $keyword . '%"%;s:6:"EUC_JP";s:%:"%";}', get_magic_quotes_gpc()) .  
                ' OR ' . $field . ' like ' . $sysConn->qstr('a:5:{s:4:"Big5"%s:%:"%' . $keyword . '%"%s:6:"GB2312"%s:%:%s:2:"en"%s:%:%;s:6:"EUC_JP";s:%:"%";s:11:"user_define";s:%:"%";}', get_magic_quotes_gpc()) . 
                ' OR ' . $field . ' like ' . $sysConn->qstr('a:5:{s:4:"Big5"%s:%:%s:6:"GB2312"%s:%:"%' . $keyword . '%"%s:2:"en"%s:%:%;s:6:"EUC_JP";s:%:"%";s:11:"user_define";s:%:"%";}', get_magic_quotes_gpc()) . 
                ' OR ' . $field . ' like ' . $sysConn->qstr('a:5:{s:4:"Big5"%s:%:%s:6:"GB2312"%s:%:%s:2:"en"%s:%:"%' . $keyword . '%"%;s:6:"EUC_JP";s:%:"%";s:11:"user_define";s:%:"%";}', get_magic_quotes_gpc()) . 
                ')';
        

        return $data;
   }
	
    /**
     * 取得 qrcode 路徑
     * @param string $url : 想要前往的網址
     * @param string $level : 等級，預設 l
     * @param integer $size : 尺寸，預設 10
     * @param string $des : 要不要加密，預設 要1
     * 
     * @return string $path : qrcode 路徑
     **/
    function getQrcodePath($url, $des = '1', $level = 'L', $size = 10, $width = '', $height = '') 
    {
        // 若要加密
        if ($des === '1') {
//            echo '<pre>';
//            var_dump(sysTicketSeed, $_COOKIE['idx']);
//            var_dump(md5(sysTicketSeed . $_COOKIE['idx']));
//            echo '</pre>';
            $key = md5(sysTicketSeed . $_COOKIE['idx']);
            $url = sysNewEncode($url . '|qrcode', $key, false);
        } else {
            $des = '0';
        }
        
        $url = urlencode($url);
        $path = '/lib/phpqrcode/generate.php?level=' . $level . '&size=' . $size . '&des=' . $des . '&data=' . $url;
        
        if ($width!='') $path .= '&width='.$width;
        if ($height!='') $path .= '&height='.$height;
        
        return $path;
    }
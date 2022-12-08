<?php
   /**
    * 檔案說明	學校/班級/課程/教材 的 Quota
    *
    * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
    *
    * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
    * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
    * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
    *
    * @package     WM3
    * @author      Edi Chen <edi@sun.net.tw>
    * @copyright   2000-2005 SunNet Tech. INC.
    * @version     CVS: $Id: quota.php,v 1.1 2010/02/24 02:39:34 saly Exp $
    * @link        http://demo.learn.com.tw/1000110138/index.html
    * @since       2006-04-26
    * 備註：學校的Quota應該是首頁空間限制，不適用在這，所以quota_limit設很大,quota_used設很小作為不限制
    */

// {{{ 函式庫引用 begin

// }}} 函式庫引用 end

// {{{ 常數定義 begin

// }}} 常數定義 end
    
// {{{ 變數宣告 begin
    
// }}} 變數宣告 end

// {{{ 函數宣告 begin
	/**
	 *	判斷要取得quota的種類(學校/班級/課程/教材)
	 *	@param int id 學校/班級/課程/教材 代碼
	 *	@return string quota種類(school, class, course, content)
	 */
	function getQuotaType($id) {
		if (empty($id)) return false;
		switch(strlen($id)) {
			case  5: return 'school'; 
			case  7: 
			case 15: return 'class';
			case  8: 
			case 16: return 'course';
			case  6: return 'content';
		}
		return false;
	}
	/**
	 *	取得某門課/班級/學校/教材 的 quota_used, quota_limit
	 * 	@param int id		學校/班級/課程/教材id(依照長度作判斷)
	 *	@param int quota_used	已使用的quota
	 *	@param int quota_limit	限制使用的quota
	 *	備註：從資料庫抓取quota_used,quota_limit
	 */
	function getQuota($id, &$quota_used, &$quota_limit) {
		global $_SERVER;
		switch(getQuotaType($id)) {
			case 'school' 	: 
				list($quota_limit, $quota_used) = dbGetStSr('WM_school', 	 'quota_limit, quota_used', 'school_id=' . $id . ' and school_host="'. $_SERVER['HTTP_HOST'] .'"', ADODB_FETCH_NUM);
				break;
			case 'class'	: 
				$id = substr($id,0,7);
				list($quota_limit, $quota_used) = dbGetStSr('WM_class_main', 'quota_limit, quota_used', 'class_id=' . $id, ADODB_FETCH_NUM);
				break;
			case 'course'	: 
				$id = substr($id,0,8);
				list($quota_limit, $quota_used) = dbGetStSr('WM_term_course','quota_limit, quota_used', 'course_id=' . $id, ADODB_FETCH_NUM);
				break;
			case 'content'	:
				list($quota_limit, $quota_used) = dbGetStSr('WM_content','quota_limit, quota_used', 'content_id=' . $id, ADODB_FETCH_NUM);
				break;
			default			: $quota_used = $quota_limit = 0;
		}
		if (empty($quota_used))  $quota_used = 0;
		if (empty($quota_limit)) $quota_limit = 0;
	}
	
	/**
	 *	取得某門課/班級/學校/教材 的 quota_used, quota_limit
	 * 	@param int id			學校/班級/課程/教材id(依照長度作判斷)
	 *	@param int quota_used	已使用的quota
	 *	@param int quota_limit	限制使用的quota
	 *	備註：實際計算quota_used,quota_limit
	 */
	function getCalQuota($id, &$quota_used, &$quota_limit) {
		global $sysSession;
		getQuota($id, $quota_used, $quota_limit);
		switch(getQuotaType($id)) {
			case 'school'	:
				$path = sysDocumentRoot . '/base/' . $id . '/door/';
				break;
			case 'class'	:
				$id = substr($id,0,7);
				$path = sysDocumentRoot . '/base/'.$sysSession->school_id.'/class/' . $id;
				break;
			case 'course'	:
				$id = substr($id,0,8);
				$path = sysDocumentRoot . '/base/'.$sysSession->school_id.'/course/' . $id.'/';
				break;
			case 'content'	:
				$path = sysDocumentRoot . '/base/'.$sysSession->school_id.'/content/'. $id;
				break;
			default	: return;
		}
		
		if(is_dir($path)) {
			$ph = popen("du -sk '$path'",'r');
			$buffer = fgets($ph, 256);
			pclose($ph);
			$bu = split("[ \t]+",$buffer);
			$quota_used = $bu[0];
			if (empty($quota_used) || $quota_used < 0) $quota_used = 0;
		}
	}
	
	/**
	 *	設定某門課/班級/學校/教材 的 quota_used, quota_limit
	 * 	@param int id			學校/班級/課程/教材 id(依照長度作判斷)
	 *	@param int quota_used	已使用的quota
	 *	@param int quota_limit	限制使用的quota
	 */
	function setQuota($id, $quota_used, $quota_limit = null) {
		global $_SERVER;
		switch(getQuotaType($id)) {
			case 'school'	:
				$table = 'WM_school';
				$where = 'school_id=' . $id . ' and school_host="'. $_SERVER['HTTP_HOST'] . '"';
				break;
			case 'class'	:
				$table = 'WM_class_main';
				$where = 'class_id=' . substr($id,0,7);
				break;
			case 'course'	:
				$table = 'WM_term_course';
				$where = 'course_id=' . substr($id,0,8);
				break;
			case 'content'	:
				$table = 'WM_content';
				$where = 'content_id='. $id;
				break;
			default : return;
		}
		if (!empty($quota_limit))
			$field = 'quota_used=' . $quota_used . ', quota_limit=' . $quota_limit;
		else
			$field = 'quota_used=' . $quota_used;
		
		return dbSet($table, $field, $where);
	}
	
	/**
	 *	取得剩餘Quota
	 *	@param int id 學校/班級/課程/教材id(依照長度作判斷)
	 *	@return int 剩餘的quota
	 *	備註：從資料庫抓取quota_used,quota_limit做計算
	 */
	function getRemainQuota($id) {
		getQuota($id, $quota_used, $quota_limit);
		return $quota_limit - $quota_used;
	}
	
	/**
	 *	取得剩餘Quota
	 *	@param int id 學校/班級/課程/教材id(依照長度作判斷)
	 *	@return int 剩餘的quota
	 *	備註：實際計算quota_used,quota_limit
	 */
	function getCalRemainQuota($id) {
		getCalQuota($id, $quota_used, $quota_limit);
		return $quota_limit - $quota_used;
	}
	
	/**
	 * getDefaultQuota()
	 *     取得各校預設課程Quota 值
	 * @return Quota 值 (MB)
	 **/
	function getDefaultQuota() {
		global $sysSession;
		list($courseQuota) = dbGetStSr('WM_school', 'courseQuota', "school_id={$sysSession->school_id}", ADODB_FETCH_NUM);
		return intval($courseQuota);
	}
	
// }}} 函數宣告 end

// {{{ 主程式 begin
	if (!function_exists('dbGetStSr'))
		die('Can\'t execute stand-alone!');
// }}} 主程式 end

?>

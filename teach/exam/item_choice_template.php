<?php
    /**
     * 選項樣版存取功能
     *
     * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
     *
     * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
     * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
     * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
     *
     * @package     WM3
     * @author      Wiseguy Liang <wiseguy@mail.wiseguy.idv.tw>
     * @copyright   2000-2006 SunNet Tech. INC.
     * @version     CVS: $Id: item_choice_template.php,v 1.1 2010/02/24 02:40:25 saly Exp $
     * @link        http://demo.learn.com.tw/1000110138/index.html
     * @since       2007-06-20
     */

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/xajax/xajax.inc.php');
	
	// 註冊 AJAX service
	$xajax_choice_temp = new xajax('item_choice_template.php');
	$xajax_choice_temp->registerFunction('save_template');
	$xajax_choice_temp->registerFunction('pick_template');
	$xajax_choice_temp->registerFunction('del_template');
	$xajax_choice_temp->registerFunction('get_template_select');

	$MSG['select a template'] = array(
		'Big5'			=> '請選擇樣版 ...',
		'GB2312'		=> '请选择样版 ...',
		'en'			=> 'select a template ...',
		'EUC-JP'		=> 'select a template ...',
		'user_define'	=> 'select a template ...'
	);

	// 判斷在哪個環境執行
	list($foo, $topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
	$owner_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id; //10000000;

	/**
	 * 儲存樣版
	 *
	 * @param   string      $title      標題
	 * @param   int         $type       題型 (2,3,61,62)
	 * @param   string      $content    選項樣版
	 */
	function save_template($title, $type, $content)
	{
	    global $owner_id, $sysSession, $sysConn, $_SERVER;
	    
	    $type    = intval($type);
	    $title   = trim($title);
	    $content = trim($content);

	    $objResponse = new xajaxResponse();
	    $r = dbNew('WM_qti_choice_template',
				   'owner_id,create_time,creator,title,type,content',
				   "$owner_id,now(),'{$sysSession->username}','$title',$type,'$content'");
		if ($r)
		{
		    // 只能存 20 個，所以 20 個以後的都刪掉 (100 只是代表值)
		    dbDel('WM_qti_choice_template',
				  "owner_id=$owner_id and type=$type order by create_time desc limit 20,100");
		    $objResponse->addScript('alert(msg6);');
			$objResponse->addScript('xajax_get_template_select(' . $type . ')');
		}
		else
            // $objResponse->addAlert('Error: ' . $sysConn->ErrorNo() . '->' . $sysConn->ErrorMsg());
            wmSysLog(
				$sysSession->cur_func, $owner_id, 0 , 2, 'auto', $_SERVER['PHP_SELF'],
				$sysSession->username .' Save template fail'
			);
            
        return $objResponse;
	}
	
	/**
	 * 取回樣版
	 *
	 * @param   int     $create_time_serno      序號(create_time)
	 * @param   int     $type                   題型
	 */
	function pick_template($create_time_serno, $type)
	{
	    global $owner_id, $sysSession, $sysConn, $_SERVER;

	    $create_time_serno = intval($create_time_serno);
	    $type              = intval($type);

        $objResponse = new xajaxResponse();
        $r = dbGetOne('WM_qti_choice_template',
					  'content',
					  "owner_id=$owner_id and create_time='" . date('Y-m-d H:i:s', $create_time_serno) . "'");
		if ($r !== false)
		{
		    $objResponse->addAssign('choice_template_panel',
									'innerHTML',
									'<table id="virtualTable">' . preg_replace('/>\s+</', '><', $r) . '</table>');
		    $objResponse->addScript("template_replace($type);");
		}
		else
		    // $objResponse->addAlert('Error: ' . $sysConn->ErrorNo() . '->' . $sysConn->ErrorMsg());
		    wmSysLog(
				$sysSession->cur_func, $owner_id, 0 , 2, 'auto', $_SERVER['PHP_SELF'],
				$sysSession->username .' Pick template fail'
			);
		    
        return $objResponse;
	}
	
	/**
	 * 取得目前的樣版 select options
	 *
	 * @param   int     $type       題型
	 * @return  string              <option></option> 串列
	 */
	function get_template_select($type)
	{
	    global $owner_id, $sysSession, $MSG;
	    
	    $type = intval($type);
	    $r = dbGetAssoc('WM_qti_choice_template', 'unix_timestamp(create_time),title', 'owner_id=' . $owner_id . ' and type=' . $type);
	    $buf = '<select id="templateSelector' . $type . '"><option>' . $MSG['select a template'][$sysSession->lang] . '</option>';
	    if (is_array($r) && count($r))
	        foreach($r as $k => $v)
	        	$buf .= '<option value="' . $k . '">' . htmlspecialchars($v) . '</option>';
		$buf .= '</select>';

        $objResponse = new xajaxResponse();
        $objResponse->addAssign('templateSelector' . $type, 'outerHTML', $buf);
        return $objResponse;
	}
	
	/**
	 * 刪除樣板
	 */
	function del_template($create_time_serno, $type)
	{
	    global $owner_id, $sysSession, $sysConn, $_SERVER;

	    $create_time_serno = intval($create_time_serno);
	    $type              = intval($type);

        $objResponse = new xajaxResponse();
		dbDel('WM_qti_choice_template',
			  "owner_id=$owner_id and create_time='" . date('Y-m-d H:i:s', $create_time_serno) . "' limit 1");
		if ($sysConn->ErrorNo())
		    // $objResponse->addAlert('Error: ' . $sysConn->ErrorNo() . '->' . $sysConn->ErrorMsg());
		    wmSysLog(
				$sysSession->cur_func, $owner_id, 0 , 2, 'auto', $_SERVER['PHP_SELF'],
				$sysSession->username .' Delete template fail'
			);
		else
		{
			$objResponse->addScript('alert(msg5);');
			$objResponse->addScript('xajax_get_template_select(' . $type . ')');
		}
        return $objResponse;
	}
	
	
	$xajax_choice_temp->processRequests();
?>

<?php
	/**
	 * 課程類別 (列表)
	 *
	 * @since   2005/04/11
	 * @author  Jeff
	 * @version $Id: select_content1.php,v 1.1 2010/02/24 02:40:09 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lang/popup_lang.php');

#====== function ===========
$CurrTrCss = "cssTrOdd";
function getNextTrCss()
{
	global $CurrTrCss;
	$CurrTrCss = ($CurrTrCss == 'cssTrOdd')?'cssTrEvn':'cssTrOdd';
	return $CurrTrCss;
}

//取得該類別的所有課程
function getContentList($id)
{
	global $sysConn, $sysSession, $ADODB_FETCH_MODE;
	$rtnArray = null;

	if ($id == 100000){
		$sqls = 'select content_id, caption from WM_content ' .
				" where content_id > 100000 and kind='content' and status!='disable'";
	}else{
		$sqls = "select content_id, caption
				 from WM_content_group T1 inner join WM_content T2 on T1.child=T2.content_id
				 where T1.parent='{$id}' and T2.kind='content' and T2.status!='disable'";
	}
	
	chkSchoolId('WM_content');
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	$rs = $sysConn->execute($sqls);
	if ($rs)
	{
		while ($data = $rs->FetchNextObj(true))
		{
			$cplang = unserialize($data->caption);
			$rtnArray[] = array($data->content_id,$cplang[$sysSession->lang]);
		}
	}
	return $rtnArray;
}

//以Html輸出課程列表
function getContentListHTML($arr)
{
	global $sysConn, $sysSession, $MSG;
	$rtns = '';

	if (count($arr) == 0)
	{
		$rtns .= '<tr class="'.getNextTrCss().'">';
		$rtns .=  '<td colspan="3" align="center">'.$MSG['msg_no_content'][$sysSession->lang].'</td>';
		$rtns .=  '</tr>'."\r\n";
	}else{

		$size = count($arr);

		for($i=0; $i < $size; $i++){
			$row = $arr[$i];
			$cour_name =$row[0].'*'. htmlspecialchars($row[1]);
			$serial = $i+1;
			$rtns .=  '<tr class="'.getNextTrCss().'">';
			$rtns .= '<td width="20"><input type="radio" name="chk" value="'.$cour_name.'"></td>';
			$rtns .= '<td align="center">' . $serial . '</td>';
			$rtns .= '<td>'. $row[1] .'</td>';
			$rtns .= '</tr>'."\r\n";
		}
	}

	return $rtns;
}

//以Javascript Array方式輸出
function getContentListJSArray($arr)
{
	$rtns = 'var CArray = new Array(';
	for($i=0, $size=count($arr); $i < $size; $i++)
	{
		$row = $arr[$i];
        //#47319 Chrome [教師/課程管理/課程設定] 選用一個教材庫之後，按下「確定」，沒有反應。：增加htmlspecialchars以解決單雙引號問題
		$rtns .= ' new Array("'.$row[0].'","'.htmlspecialchars($row[1]).'"),';
	}
	$rtns .= 'new Array("",""));';
	return $rtns;
}

#====== Main ===============
//取得該類別的教材
$ContentArray = getContentList($_GET['content_id']);


#========= Html Output =====================
	$jsCourseArray = getContentListJSArray($ContentArray);

	$js = <<< BOF

	{$jsCourseArray}
	var MSG_CONFIRM_LOSE = "{$MSG['msg_confirm_lose'][$sysSession->lang]}";

	var queryTxt = '';

	function getCaption(csid)
	{
		for(j=0; j<CArray.length-1; j++)
		{
			if (CArray[j][0] == csid) return CArray[j][1];
		}
		return '';
	}

	function ReturnWork()
	{
		// 取得回傳陣列
		var obj = document.WorkForm;
		var rtnArray = new Array();
		var len = 0;
		var scour_name = '';

		for(i=0; i<obj.elements.length; i++)
		{
			if (obj.elements[i].type == 'radio')
			{
				if (obj.elements[i].checked)
				{
					scour_name = obj.elements[i].value.split('*');
					rtnArray[0] = scour_name[0];
					rtnArray[1] = scour_name[1];
				}
			}
		}

		var hwnd = opener.getHwnd("WinContentSelect");

		if (hwnd != null)
		{
			hwnd.callback(rtnArray);
		}
		window.close();
	}

	function chgCatagory(){
		var obj = document.WorkForm;
		for(i=0; i<obj.elements.length; i++)
		{
			if (obj.elements[i].type == 'radio')
			{
				if (obj.elements[i].checked)
				{
					if (!confirm(MSG_CONFIRM_LOSE)) return false;
				}
			}
		}
		obj.submit();
	}

BOF;
// 開始呈現 HTML
	showXHTML_head_B($MSG['work_attr_title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('include', '/lib/common.js');
	if (defined('NFA'))
	{
		showXHTML_script('include', '/lib/popup/popup.js');
	}
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
		echo "<center>\n";
		showXHTML_table_B('width="400" border="0" cellspacing="0" cellpadding="0" id="ListTable"');
			showXHTML_tr_B();
				showXHTML_td_B();
					$ary[] = array($MSG['select_content_title'][$sysSession->lang], 'tabs');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E();
			showXHTML_tr_E();
			showXHTML_tr_B();
				showXHTML_td_B('valign="top" id="CGroup"');
					showXHTML_form_B('style="display:inline;" action="select_course.php" method="post" ', 'WorkForm');
						showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" id="WorkAttr_List" class="cssTable"');

							// html 標題
							showXHTML_tr_B('class="font01 cssTrHead"');
								showXHTML_td('width="20"','&nbsp;');
								showXHTML_td('width="50" align="center" nowrap',$MSG['th_seq'][$sysSession->lang]);
								showXHTML_td('width="96%" nowrap',$MSG['th_content_name'][$sysSession->lang]);
							showXHTML_tr_E();

							// 課程列表資料
							echo getContentListHTML($ContentArray);

							// 確定 & 關閉視窗
							showXHTML_tr_B('class="'.getNextTrCss().'"');
								showXHTML_td_B('colspan="3" align="center" class="font01"');
									showXHTML_input('button', '', $MSG['btn_ok'][$sysSession->lang], '', 'class="cssBtn" onclick="ReturnWork();"');
									showXHTML_input('button', '', $MSG['btn_cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="document.location.replace(\'select_content.php\');"');
								showXHTML_td_E();
							showXHTML_tr_E();
						showXHTML_table_E();

					showXHTML_form_E();
				showXHTML_td_E();
			showXHTML_tr_E();
		showXHTML_table_E();
		echo "</center>\n";

	showXHTML_body_E();
?>

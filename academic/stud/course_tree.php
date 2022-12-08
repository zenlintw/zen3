<?php
	/**
	 * 課程群組
	 *
	 * @since   2003/10/02
	 * @author  ShenTing Lin
	 * @version $Id: course_tree.php,v 1.1 2010/02/24 02:38:44 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/course_tree.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');

	$sysSession->cur_func = '4700300400';
	$sysSession->restore();
	if (!aclVerifyPermission(700300400, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	// 參數設定 (Begin)
	if (!isset($csSelCsID)) $csSelCsID = array();    // 已勾選的課程編號
	if (!isset($theme))     $theme = "/theme/$sysSession->theme/{$sysSession->env}/";    // 佈景的路徑

	$csGpTree   = array();    // 群組 Tree 的結構
	$csGpCsList = array();    // 群組與已分組課程的清單
	$csGpCsData = array();    // 課程詳細資料
	// 參數設定 (End)

///////////////////////////////////////////////////////////////////////////////

	function csCourseGroupJS() {
		global $sysSession, $MSG, $theme;
		$csJS = <<< BOF
	var theme = "{$theme}";
	var csIcon = new Array("plus.gif", "minus.gif");

	/**
	 * 展開或收攏群組
	 *     自動展開或收攏
	 * @param obj : 物件 (這是圖示的物件)
	 **/
	function csGpStatus(obj) {
		var node = null, attr = null;

		if ((typeof(obj) != "object") || (obj == null)) return false;
		attr = obj.getAttribute("attr");
		node = document.getElementById("gp" + attr);
		if ((typeof(obj) != "object") || (obj == null)) return false;
		if (node.style.display == "none") {
			obj.src = theme + csIcon[1];
			obj.title = "{$MSG['cs_tree_collect'][$sysSession->lang]}";
			node.style.display = "";
		} else {
			obj.src = theme + csIcon[0];
			obj.title = "{$MSG['cs_tree_expend'][$sysSession->lang]}";
			node.style.display = "none";
		}
	}

	/**
	 * 選取群組或課程
	 * @param array csSelCsID : 課程或群組的編號
	 **/
	function csSelectGpCs(csSelCsID) {
		var obj = null, nodes = null, attr = null;
		var csCkObj = new Object;

		if ((typeof(csSelCsID) != "object") || (csSelCsID == null)
			|| (typeof(csSelCsID.length)) || (csSelCsID.length == 0)) {
			return false;
		}

		// 轉成物件
		for (var i = 0; i < csSelCsID.length; i++) {
			csCkObj[csSelCsID[i]] = true;
		}

		obj = document.getElementById("csGpCsTree");
		if (obj == null) return false;
		nodes = obj.getElementsByTagName("input");
		for (var i = 0; i < nodes.length; i++) {
			if (nodes[i].type != "checkbox") continue;
			attr = nodes[i].value;
			nodes[i].checked = (typeof(csCkObj[attr]) != "undefined");
		}
	}

	/**
	 * 取得所選的群組或課程
	 * @return array ary : 所選的課程或群組
	 **/
	function csGetSelectGpCs() {
		var obj = null, nodes = null, attr = null;
		var ary = new Array();

		obj = document.getElementById("csGpCsTree");
		if (obj == null) return false;
		nodes = obj.getElementsByTagName("input");
		for (var i = 0; i < nodes.length; i++) {
			if (nodes[i].type != "checkbox") continue;
			if (nodes[i].checked) ary[ary.length] = nodes[i].value;
		}
		return ary;
	}
BOF;
		return $csJS;
	}

///////////////////////////////////////////////////////////////////////////////
	/**
	* 初始化
	*/
	function csDataInit()
	{
		global $csGpTree, $csGpCsList, $csGpCsData;
		// 從資料庫中取得資料 (Begin)
		$RS = dbGetStMr('`WM_term_group`', '*', '1 order by `parent`, `permute`', ADODB_FETCH_ASSOC);
		while (!$RS->EOF)
		{
			$csGpTree[$RS->fields['parent']][$RS->fields['permute']] = $RS->fields['child'];
			$csGpCsList[] = $RS->fields['parent'];
			$csGpCsList[] = $RS->fields['child'];
			$RS->MoveNext();
		}
		$csGpCsList = array_unique($csGpCsList);

		$RS = dbGetStMr('WM_term_course', '*', 'course_id > 10000000 and status<9', ADODB_FETCH_ASSOC);
		while (!$RS->EOF)
		{
			$csGpCsData[$RS->fields['course_id']] = $RS->fields;
			$RS->MoveNext();
		}
		// 從資料庫中取得資料 (Begin)
	}

	/**
	* 將群組轉成 HTML 樣式
	*
	* @param  $
	* @return
	*/
	function csGroup2HTML($csGroupID, $csCkBox, $csCkCanSel, $csShowCS, $csMousEnv, $csInitCSS)
	{
		global $sysSession, $MSG, $theme, $csGpTree, $csGpCsData, $csSelCsID;
		$res = '';

		if (!is_array($csGpTree) || !is_array($csGpTree[$csGroupID])) return $res;
		$child = $csGpTree[$csGroupID];
		ksort($child);
		reset($child);

		$out     = $csInitCSS;
		$over    = ($csInitCSS == 'cssTrEvn') ? 'cssTrOdd' : 'cssTrEvn';
		$msenv   = ($csMousEnv) ? '<span onmouseover="this.className=\'' . $over . '\'" onmouseout="this.className=\'' . $out . '\'">' : '<span>';
		$disable = $csCkCanSel ? '' : ' disabled';

		foreach($child as $val)
		{
			if ($val <= 0) continue;
			$str  = '';
			$img  = '';
			$lang = getCaption($csGpCsData[$val]['caption']);
			$cs_status = $csGpCsData[$val]['status'];

			if (($cs_status != '') && ($cs_status < 9))
			{
				if (array_key_exists($val, $csGpTree))
				{
					$str = csGroup2HTML($val, $csCkBox, $csCkCanSel, $csShowCS, $csMousEnv, $csInitCSS);
					// 圖示
					if (empty($str))
					{
						$img = '<img src="' . $theme . 'dot.gif" width="9" height="15" align="absmiddle">';
					}
					else
					{
						$img = '<img src="' . $theme . 'plus.gif" width="9" height="15" align="absmiddle" attr="' . $val . '" onclick="csGpStatus(this);" title="' . $MSG['cs_tree_expend'][$sysSession->lang] . '">';
						$str = '<ol id="gp' . $val . '" class="cssOL" style="display:none;">' . $str . '</ol>';
					}
					$res .= '<li>' . $msenv . $img;
					if (in_array('group', $csCkBox))
					{
						$ck   = in_array($val, $csSelCsID) ? ' checked' : '';
						$res .= '<input type="checkbox" name="csid[]" id="ckgp' . $val . '" value="' . $val . '"' . $ck . $disable . ' onclick="select_course(this)">';
					}
					$res .= $MSG['cs_tree_group'][$sysSession->lang] . $lang[$sysSession->lang] . '</span></li>' . $str;
				}
				else if ($csShowCS)
				{
					$img  = '<img src="' . $theme . 'dot.gif" width="9" height="15" align="absmiddle">';
					$res .= '<li>' . $msenv . $img;
					if (in_array('course', $csCkBox))
					{
						$ck   = in_array($val, $csSelCsID) ? ' checked' : '';
						$res .= '<input type="checkbox" name="csid[]" id="ckcourse' . $val . '" value="' . $val . '"' . $ck . $disable . ' onclick="select_course(this)">';
					}
					$res .= $MSG['cs_tree_course'][$sysSession->lang] . $lang[$sysSession->lang] . '</span></li>' . $str;
				}
			}
		}
		return $res;
	}

	function csGetGroupHTML($csGroupID, $csDataFileds, $csCkBox, $csCkCanSel, $csShowSchool, $csShowOtherGP, $csShowCS, $csCSState, $csMousEnv, $csInitCSS)
	{
		global $sysSession, $MSG, $theme, $csGpCsList, $csGpCsData, $csSelCsID;

		$out     = $csInitCSS;
		$over    = ($csInitCSS == 'cssTrEvn') ? 'cssTrOdd' : 'cssTrEvn';
		$msenv   = ($csMousEnv) ? '<span onmouseover="this.className=\'' . $over . '\'" onmouseout="this.className=\'' . $out . '\'">' : '<span>';
		$disable = $csCkCanSel ? '' : ' disabled';
		$res     = '<span id="csGpCsTree">';
		// 學校 (Begin)
		if ($csShowSchool)
		{
			$res .= '<ul class="cssUL"><li>' . $msenv;
			if (in_array('school', $csCkBox))
			{
				$ck   = in_array(10000000, $csSelCsID) ? ' checked' : '';
				$res .= '<input type="checkbox" name="csid[]" id="ckgp10000000" value="10000000"' . $ck . $disable . ' onclick="select_func2(\'tbGpCs\', this.checked);">';
			}
			$res .= $MSG['cs_tree_school'][$sysSession->lang] . $sysSession->school_name . '</span></li>';
		}
		$res .= '<ul class="cssOL">';
		// 學校 (End)
		$res .= csGroup2HTML($csGroupID, $csCkBox, $csCkCanSel, $csShowCS, $csMousEnv, $csInitCSS); // 已分組群組
		// 未分組群組 (Begin)
		if ($csShowOtherGP)
		{
			$str = '';
			$img = '<img src="' . $theme . 'dot.gif" width="9" height="15" align="absmiddle">';
			if ($csShowCS)
			{
				foreach($csGpCsData as $key => $val)
				{
					if (($key <= 10000000) || in_array($key, $csGpCsList)) continue;
					$lang = getCaption($val['caption']);

					$cs_status = $val['status'];

					if ((! empty($cs_status)) && ($cs_status < 9))
					{
						$str .= '<li>' . $msenv . $img;
						if (in_array('course', $csCkBox))
						{
							$ck   = in_array($key, $csSelCsID) ? ' checked' : '';
							$str .= '<input type="checkbox" name="csid[]" id="ckcourse' . $key . '" value="' . $key . '"' . $ck . $disable . '  onclick="select_course(this)">';
						}
						$str .= $MSG['cs_tree_course'][$sysSession->lang] . $lang[$sysSession->lang] . '</span></li>';
						// $str .= '<a href="javascript:;" class="cssAnchor" title="' . $MSG['cs_tree_course'][$sysSession->lang] . $lang[$sysSession->lang] . '" onclick="showDetail(\'' . $key . '\'); return false;">' . $MSG['cs_tree_course'][$sysSession->lang] . $lang[$sysSession->lang] . '</a></span></li>';
					}
				}
			}
			if (empty($str))
			{
				$img = '<img src="' . $theme . 'dot.gif" width="9" height="15" align="absmiddle">';
			}
			else
			{
				$img = '<img src="' . $theme . 'plus.gif" width="9" height="15" align="absmiddle" attr="0" onclick="csGpStatus(this);" title="' . $MSG['cs_tree_expend'][$sysSession->lang] . '">';
				$str = '<ol id="gp0" class="cssOL" style="display:none;">' . $str . '</ol>';
			}
			$res .= '<li>' . $msenv . $img . $MSG['cs_tree_group'][$sysSession->lang] . $MSG['cs_tree_other_group'][$sysSession->lang] . '</span></li>' . "\n" . $str;
		}
		// 未分組群組 (End)
		if ($csShowSchool) $res .= '</ul>'; // 學校
		$res .= '</ul><span>';

		return $res;
	}

	/**
	* 顯示課程群組
	*
	* @param integer $csGroupID : 課程群組的編號 (預設：10000000)
	*      10000000 : 全校的課程
	*             0 : 未分組的課程
	* @param string $csDataType : 回傳的資料格式 (預設：XML)
	*      HTML : HTML 格式的資料
	*       XML : XML 格式的資料
	* @param array $csDataFileds : 要取得那些欄位
	* @param array $csCkBox : 顯示 checkbox (預設：3[學校，群組])
	*      array('school', 'group', 'course')
	* @param boolean $csShowSchool : 顯示學校 (預設：TRUE)
	*      TRUE  : 顯示
	*      FALSE : 不顯示
	* @param boolean $csShowOtherGP : 顯示未分組課程 (預設：TRUE)
	*      TRUE  : 顯示
	*      FALSE : 不顯示
	* @param boolean $csShowCS : 顯示課程 (預設：FALSE)
	*      TRUE  : 顯示
	*      FALSE : 不顯示
	* @param array $csCSState : 要顯示具有某些狀態的課程 (預設：30[開課，開課(日期)，開課(身份)，開課(身份、日期)])
	*      array(關閉<1>，開課<2>，開課(日期)<4>，開課(身份)<8>，開課(身份、日期)<16>，準備中<32>，限管理者<64>)
	* @param boolean $csMousEnv : 需不需要滑鼠的一些特效 (預設：FALSE)
	*      TRUE  : 要
	*      FALSE : 不要
	* @param boolean $csInitCSS : 初始化的 CSS 名稱 (預設：cssTrEvn : 白色)
	* @return
	*/
	function csGroupLayout($csGroupID = 10000000, $csDataFileds = null, $csCkBox = null, $csCkCanSel = true,
		$csShowSchool = true, $csShowOtherGP = true, $csShowCS = false, $csCSState = null, $csMousEnv = false, $csInitCSS = 'cssTrEvn'
		)
	{
		// 初始化預設值 (Begin)
		if (is_null($csDataFileds) || !is_array($csDataFileds))
			$csDataFileds = array('course_id', 'caption');
		if (is_null($csCkBox) || !is_array($csCkBox))
			$csCkBox = array('school', 'group');
		if (is_null($csCSState) || !is_array($csCSState))
			$csCSState = array(1, 2, 3, 4);
		// 初始化預設值 (End)
		showXHTML_script('inline', csCourseGroupJS());
		return csGetGroupHTML($csGroupID, $csDataFileds, $csCkBox, $csCkCanSel, $csShowSchool, $csShowOtherGP, $csShowCS, $csCSState, $csMousEnv, $csInitCSS);
	}
	// echo csGroupLayout(10000000, 'HTML', NULL, NULL, false, true, false, NULL);
	csDataInit();
?>

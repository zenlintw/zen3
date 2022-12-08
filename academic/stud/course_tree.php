<?php
	/**
	 * �ҵ{�s��
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

	// �ѼƳ]�w (Begin)
	if (!isset($csSelCsID)) $csSelCsID = array();    // �w�Ŀ諸�ҵ{�s��
	if (!isset($theme))     $theme = "/theme/$sysSession->theme/{$sysSession->env}/";    // �G�������|

	$csGpTree   = array();    // �s�� Tree �����c
	$csGpCsList = array();    // �s�ջP�w���սҵ{���M��
	$csGpCsData = array();    // �ҵ{�ԲӸ��
	// �ѼƳ]�w (End)

///////////////////////////////////////////////////////////////////////////////

	function csCourseGroupJS() {
		global $sysSession, $MSG, $theme;
		$csJS = <<< BOF
	var theme = "{$theme}";
	var csIcon = new Array("plus.gif", "minus.gif");

	/**
	 * �i�}�Φ��l�s��
	 *     �۰ʮi�}�Φ��l
	 * @param obj : ���� (�o�O�ϥܪ�����)
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
	 * ����s�թνҵ{
	 * @param array csSelCsID : �ҵ{�θs�ժ��s��
	 **/
	function csSelectGpCs(csSelCsID) {
		var obj = null, nodes = null, attr = null;
		var csCkObj = new Object;

		if ((typeof(csSelCsID) != "object") || (csSelCsID == null)
			|| (typeof(csSelCsID.length)) || (csSelCsID.length == 0)) {
			return false;
		}

		// �ন����
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
	 * ���o�ҿ諸�s�թνҵ{
	 * @return array ary : �ҿ諸�ҵ{�θs��
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
	* ��l��
	*/
	function csDataInit()
	{
		global $csGpTree, $csGpCsList, $csGpCsData;
		// �q��Ʈw�����o��� (Begin)
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
		// �q��Ʈw�����o��� (Begin)
	}

	/**
	* �N�s���ন HTML �˦�
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
					// �ϥ�
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
		// �Ǯ� (Begin)
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
		// �Ǯ� (End)
		$res .= csGroup2HTML($csGroupID, $csCkBox, $csCkCanSel, $csShowCS, $csMousEnv, $csInitCSS); // �w���ոs��
		// �����ոs�� (Begin)
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
		// �����ոs�� (End)
		if ($csShowSchool) $res .= '</ul>'; // �Ǯ�
		$res .= '</ul><span>';

		return $res;
	}

	/**
	* ��ܽҵ{�s��
	*
	* @param integer $csGroupID : �ҵ{�s�ժ��s�� (�w�]�G10000000)
	*      10000000 : ���ժ��ҵ{
	*             0 : �����ժ��ҵ{
	* @param string $csDataType : �^�Ǫ���Ʈ榡 (�w�]�GXML)
	*      HTML : HTML �榡�����
	*       XML : XML �榡�����
	* @param array $csDataFileds : �n���o�������
	* @param array $csCkBox : ��� checkbox (�w�]�G3[�ǮաA�s��])
	*      array('school', 'group', 'course')
	* @param boolean $csShowSchool : ��ܾǮ� (�w�]�GTRUE)
	*      TRUE  : ���
	*      FALSE : �����
	* @param boolean $csShowOtherGP : ��ܥ����սҵ{ (�w�]�GTRUE)
	*      TRUE  : ���
	*      FALSE : �����
	* @param boolean $csShowCS : ��ܽҵ{ (�w�]�GFALSE)
	*      TRUE  : ���
	*      FALSE : �����
	* @param array $csCSState : �n��ܨ㦳�Y�Ǫ��A���ҵ{ (�w�]�G30[�}�ҡA�}��(���)�A�}��(����)�A�}��(�����B���)])
	*      array(����<1>�A�}��<2>�A�}��(���)<4>�A�}��(����)<8>�A�}��(�����B���)<16>�A�ǳƤ�<32>�A���޲z��<64>)
	* @param boolean $csMousEnv : �ݤ��ݭn�ƹ����@�ǯS�� (�w�]�GFALSE)
	*      TRUE  : �n
	*      FALSE : ���n
	* @param boolean $csInitCSS : ��l�ƪ� CSS �W�� (�w�]�GcssTrEvn : �զ�)
	* @return
	*/
	function csGroupLayout($csGroupID = 10000000, $csDataFileds = null, $csCkBox = null, $csCkCanSel = true,
		$csShowSchool = true, $csShowOtherGP = true, $csShowCS = false, $csCSState = null, $csMousEnv = false, $csInitCSS = 'cssTrEvn'
		)
	{
		// ��l�ƹw�]�� (Begin)
		if (is_null($csDataFileds) || !is_array($csDataFileds))
			$csDataFileds = array('course_id', 'caption');
		if (is_null($csCkBox) || !is_array($csCkBox))
			$csCkBox = array('school', 'group');
		if (is_null($csCSState) || !is_array($csCSState))
			$csCSState = array(1, 2, 3, 4);
		// ��l�ƹw�]�� (End)
		showXHTML_script('inline', csCourseGroupJS());
		return csGetGroupHTML($csGroupID, $csDataFileds, $csCkBox, $csCkCanSel, $csShowSchool, $csShowOtherGP, $csShowCS, $csCSState, $csMousEnv, $csInitCSS);
	}
	// echo csGroupLayout(10000000, 'HTML', NULL, NULL, false, true, false, NULL);
	csDataInit();
?>

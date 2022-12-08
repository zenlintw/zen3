<?php
	/**
	 * �Z�Ÿs��
	 *
	 * @since   2003/10/02
	 * @author  ShenTing Lin
	 * @version $Id: class_tree.php,v 1.1 2010/02/24 02:38:14 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/course_tree.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	
	$sysSession->cur_func='2400100400';
	$sysSession->restore();
	if (!aclVerifyPermission(2400100400, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable'))){

	}


	// �ѼƳ]�w (Begin)
	if (!isset($csSelCsID)) $csSelCsID = array();    // �w�Ŀ諸�Z�Žs��
	if (!isset($theme))     $theme = "/theme/$sysSession->theme/academic/";    // �G�������|

	// �Z�Ť����㪺���W��
	chkSchoolId('WM_class_main');
	$csCsDefFileds = $sysConn->GetCol('show columns from WM_class_main');
	$csGpTree   = array();    // �s�� Tree �����c
	$csGpCsList = array();    // �s�ջP�w���կZ�Ū��M��
	$csGpCsData = array();    // �Z�ŸԲӸ��
	// �ѼƳ]�w (End)

///////////////////////////////////////////////////////////////////////////////
	$csStyle = <<< BOF
	.cssUL {
		list-style-type : none;
		padding-left    : 0px;
		margin-left     : 0px;
		padding-bottom  : 0px;
		margin-bottom   : 0px;
	}
	.cssOL {
		list-style-type : none;
		padding-left    : 10px;
		margin-left     : 10px;
		padding-bottom  : 0px;
		margin-bottom   : 0px;
	}
BOF;

///////////////////////////////////////////////////////////////////////////////
	/**
	 * ��l��
	 **/
	function csDataInit() {
		global $csGpTree, $csGpCsList, $csGpCsData;

		// �q��Ʈw�����o��� (Begin)
		$RS = dbGetStMr('`WM_class_group`', '*', '1 order by `parent`, `permute`', ADODB_FETCH_ASSOC);
		while (!$RS->EOF) {
			$csGpTree[$RS->fields['parent']][$RS->fields['permute']] = $RS->fields['child'];
			$csGpCsList[] = $RS->fields['parent'];
			$csGpCsList[] = $RS->fields['child'];
			$RS->MoveNext();
		}
		$csGpCsList = array_unique($csGpCsList);

		$RS = dbGetStMr('WM_class_main', '*', '1', ADODB_FETCH_ASSOC);
		while (!$RS->EOF) {
			$csGpCsData[$RS->fields['class_id']] = $RS->fields;
			$RS->MoveNext();
		}
		// �q��Ʈw�����o��� (Begin)
	}
	
	/**
	 * �x�s�Z�Ÿ�s�ժ����Y���Ʈw��
	 * @param array $csGpID   : �Z�ũ��ݪ��s�հ}�C
	 * @param integer $csCsID : �Z�Žs��
	 **/
	function csSaveCs2Gp($csGpID, $csCsID) {
		global $sysConn, $sysSession, $_SERVER;

		if (!is_array($csGpID))  return false;
		if ($csCsID <= 1000000) return false;
		$arows = 0;

		foreach ($csGpID as $val) {
			// �ˬd�O�_�w�g�s�b
			$RS = dbGetStSr('WM_class_group', 'count(*) as cnt', "`parent`={$val} AND `child`={$csCsID}", ADODB_FETCH_ASSOC);
			if (intval($RS['cnt']) > 0) continue;
			// ���X�̤j�� order �åB�[�@
			$RS = dbGetStSr('WM_class_group', 'MAX(`permute`) as cnt', "parent={$val}", ADODB_FETCH_ASSOC);
			$order = $RS['cnt'] + 1;
			// �x�s
			dbNew('WM_class_group', '`parent`, `child`, `permute`', "{$val}, {$csCsID}, {$order}");
			if ($sysConn->Affected_Rows()) $arows++;
			wmSysLog($sysSession->cur_func, $sysSession->school_id ,0 ,0, 'manager', $_SERVER['PHP_SELF'], "�x�s�Z�Ÿ�s�ժ����Y,�s��({$val}):�Z��({$csCsID})");
		}
		return $arows;
	}

	/**
	 * �N�Z�Ū����ݸs�ժ����t�P�B���Ʈw��
	 * @param
	 * @return
	 **/
	function csSyncCsXML2DB($gid, $xmlDocs) {
		global $sysSession, $sysConn, $csGpTree, $csGpCsList, $csGpCsData;

		// $sysConn->debug = true;
		$gid = intval($gid);
		if ($gid <= 1000000) return false;
		$csid  = array();
		$ctx   = xpath_new_context($xmlDocs);
		$xpath = '//classes[@id="' . $gid . '"]/child::class/@id';
		$nodes = xpath_eval($ctx, $xpath);
		for ($i = 0; $i < count($nodes->nodeset); $i++) {
			if (intval($nodes->nodeset[$i]->value) > 1000000)	$csid[] = intval($nodes->nodeset[$i]->value);
		}
		// ���M���쥻�����
		$ocsid = csGetCsList($gid);
		foreach ($ocsid as $val) {
			$cid = intval($val['course_id']);
			dbDel('WM_class_group', "`parent`={$gid} AND `child`={$cid} ");
		}
		// �x�s�s���Z��
		foreach ($csid as $val) {
			csSaveCs2Gp(array($gid), $val);
		}
		// ���s���o���
		csDataInit();
// �ΨӰ���������
/*
	ob_start();
		print_r($csGpTree);
		echo "\n-----------------------------\n";
		print_r($csGpCsList);
		echo "\n-----------------------------\n";
		print_r($csGpCsData);
		echo "\n-----------------------------\n";
		print_r($csid);
		echo "\n-----------------------------\n";
		print_r($ocsid);
		$content = ob_get_contents();
	ob_end_clean();
	touch('res.txt');
	$fp = fopen('res.txt', 'w');
	fputs($fp, $content);
	fclose($fp);
*/
		return true;
	}

	/**
	 * �إ߯Z�Ū� XML ��
	 * @param array $val (�Z�Žs��, �Z�ŦW��, ���W��� (�}�l), ���W��� (����), �W�Ҥ�� (�}�l), �W�Ҥ�� (����), �w�ϥΪŶ�), ...
	 **/
	function csBuildCsXML($val) {
		global $sysSession, $MSG;
		if (!is_array($val) && !is_array($val[0])) return '';

		$result = '';
		$cnt = count($val);
		// ��X�Z�Ū� XML (Begin)
		for ($i = 0; $i < $cnt; $i++) {
			if (empty($val[$i]['course_id'])) continue;
			// �Z�ŦW��
			$lang = getCaption($val[$i]['caption']);

			// �Ч��W��
			$content_id = intval($val[$i]['content_id']);
			$RS = dbGetStSr('WM_content', 'caption', "content_id={$content_id}", ADODB_FETCH_ASSOC);
			if (!($RS === false)) {
				$content = getCaption($RS['caption']);
			}
			// ���W���
			$enroll = '';
			if (!empty($val[$i]['en_end'])) {
				$enroll  = empty($val[$i]['en_begin']) ? $MSG['cs_msg_now'][$sysSession->lang] : $MSG['cs_msg_from'][$sysSession->lang] . $val[$i]['en_begin'];
				$enroll .= $MSG['cs_msg_to'][$sysSession->lang] . $val[$i]['en_end'];

			}
			// �W�Ҥ��
			$study = '';
			if (!empty($val[$i]['st_end'])) {
				$study  = empty($val[$i]['st_begin']) ? $MSG['cs_msg_now'][$sysSession->lang] : $MSG['cs_msg_from'][$sysSession->lang] . $val[$i]['st_begin'];
				$study .= $MSG['cs_msg_to'][$sysSession->lang] . $val[$i]['st_end'];
			}
			// �w�ϥΪŶ�
			$val[$i]['quota_used'] = intval($val[$i]['quota_used']);

			$val[$i]['teacher'] = htmlspecialchars($val[$i]['teacher']);
			$val[$i]['texts']   = htmlspecialchars($val[$i]['texts']);
			$val[$i]['content'] = htmlspecialchars($val[$i]['content']);
			// ��X XML
			$result .= <<< BOF

	<class id="{$val[$i]['class_id']}" checked="false">
		<title>
			<big5>{$lang['Big5']}</big5>
			<gb2312>{$lang['GB2312']}</gb2312>
			<en>{$lang['en']}</en>
			<euc_jp>{$lang['EUC-JP']}</euc_jp>
			<user_define>{$lang['user_define']}</user_define>
		</title>
		<teacher>{$val[$i]['teacher']}</teacher>
		<content_name>
			<title>
				<big5>{$content['Big5']}</big5>
				<gb2312>{$content['GB2312']}</gb2312>
				<en>{$content['en']}</en>
				<euc_jp>{$content['EUC-JP']}</euc_jp>
				<user_define>{$content['user_define']}</user_define>
			</title>
		</content_name>
		<enroll>{$enroll}</enroll>
		<study>{$study}</study>
		<status>{$val[$i]['status']}</status>
		<texts>{$val[$i]['texts']}</texts>
		<url>&lt;a href="{$val[$i]['url']}" target="_blank"&gt;{$val[$i]['url']}&lt;/a&gt;</url>
		<content>{$val[$i]['content']}</content>
		<credit>{$val[$i]['credit']}</credit>
		<n_limit>{$val[$i]['n_limit']}</n_limit>
		<a_limit>{$val[$i]['a_limit']}</a_limit>
		<quota_used>{$val[$i]['quota_used']}</quota_used>
		<quota_limit>{$val[$i]['quota_limit']}</quota_limit>
	</class>
BOF;
		} // End for ($i = 0; $i < $cnt; $i++)
		// ��X�Z�Ū� XML (End)
		return $result;
	}

	/**
	 * ���o�s�դ��Z�Žs��
	 * @param integer $gid : �s�սs��
	 * @return array $res
	 **/
	function csGetCsList($gid) {
		global $sysSession, $sysConn, $csGpTree, $csGpCsList, $csGpCsData;

		$gid = intval($gid);
		$child = array();

		if ($gid > 1000000) {            // �s�դ����Z��
			$RS = dbGetStMr('WM_class_group', '`child`', "`parent`={$gid} order by `permute` ASC", ADODB_FETCH_ASSOC);
			while (!$RS->EOF) {
				$val = intval($RS->fields['child']);
				if (!array_key_exists($val, $csGpTree)) {
					$child[] = $csGpCsData[$val];
				}
				$RS->MoveNext();
			}
		} else if ($gid == 1000000) {    // ���կZ��
			foreach ($csGpCsData as $key => $val) {
				if (!array_key_exists($key, $csGpTree)) {
					$child[] = $val;
				}
			}
		} else {                          // �����կZ��
			foreach ($csGpCsData as $key => $val) {
				if (!in_array($key, $csGpCsList)) {
					$child[] = $val;
				}
			}
		}

		return $child;
	}

	/**
	 * ���o�s�դ��Z�Žs��
	 * @param integer $gid : �s�սs��
	 * @return
	 **/
	function csGetCsListXML($gid) {
		global $sysSession, $sysConn, $csGpTree, $csGpCsList, $csGpCsData;

		$gid   = intval($gid);
		$child = csGetCsList($gid);
		$res   = csBuildCsXML($child);
		$res   = '<' . '?xml version="1.0" encoding="UTF-8" ?' . ">\n" .
				 '<manifest>' . $res . '</manifest>';

		return $res;
	}

	/**
	 * Group2XML()
	 * @param  Array  $group      : �Z�Ÿs�ժ��}�C
	 * @param  Array  $group_name : �Z�Ÿs�ժ��W��
	 * @param  string $group_id   : �s�ժ��s��
	 * @return string $result     : xml �榡���s�ո��
	 **/
	function csGroup2XML($gid, $csShowOtherGP=FALSE, $indent = 0) {
		global $sysSession, $MSG, $csGpTree, $csGpCsData;

		$result = '';
		if (!is_array($csGpTree[$gid])) return $result;
		$child = $csGpTree[$gid];
		ksort($child);
		reset($child);

		foreach($child as $value) {
			if ($value <= 0) continue;
			//echo $value . '<br />';
			if (!array_key_exists($value, $csGpTree)) {
				$result .= '<class id="' . $value . '"></class>';
			} else {
				$lang = old_getCaption($csGpCsData[$value]['caption']);

				$result .= '<classes id="' . $value . '">' .
				           '<title default="' . $sysSession->lang                   . '">'              .
				           '<big5>'           . $lang['Big5']                       . ' </big5>'        .
				           '<gb2312>'         . $lang['GB2312']                     . ' </gb2312>'      .
				           '<en>'             . $lang['en']                         . ' </en>'          .
				           '<euc-jp>'         . $lang['EUC-JP']                     . ' </euc-jp>'      .
				           '<user-define>'    . $lang['user_define']                . ' </user-define>' .
				           '</title>'         .
				           '<dep_id>'         . htmlspecialchars($csGpCsData[$value]['dep_id']  , ENT_NOQUOTES, 'UTF-8') . '</dep_id>'   .
				           '<director>'       . htmlspecialchars($csGpCsData[$value]['director'], ENT_NOQUOTES, 'UTF-8') . '</director>' .
				           '<people_limit>'   . $csGpCsData[$value]['people_limit'] . '</people_limit>' .
				           '<quota_limit>'    . $csGpCsData[$value]['quota_limit']  . '</quota_limit>'  .
				           csGroup2XML($value, false, $indent + 1) .
				           '</classes>';
			}
		}
		if ($indent == 0) {
			$res  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
			$res .= '<manifest>' . $result;
			if ($csShowOtherGP) {
				$res .= '<classes id="0">' .
				        '<title default="' . $sysSession->lang . '">' .
				        '<big5>'        . $MSG['cs_tree_other_group']['Big5']        . ' </big5>'        .
				        '<gb2312>'      . $MSG['cs_tree_other_group']['GB2312']      . ' </gb2312>'      .
				        '<en>'          . $MSG['cs_tree_other_group']['en']          . ' </en>'          .
				        '<euc-jp>'      . $MSG['cs_tree_other_group']['EUC-JP']      . ' </euc-jp>'      .
				        '<user-define>' . $MSG['cs_tree_other_group']['user_define'] . ' </user-define>' .
				        '</title>'      .
				        '</classes>';
			}
			$res .= '</manifest>';
			$result = $res;
		}
		return $result;
	}

	/**
	 * �N�s���ন HTML �˦�
	 * @param
	 * @return
	 **/
	function csGroup2HTML($csGroupID, $csCkBox, $csCkCanSel, $csShowCS) {
		global $sysSession, $sysConn, $MSG, $theme, $csGpTree, $csGpCsList, $csGpCsData, $csSelCsID;
		$res = '';

		if (!is_array($csGpTree) || !is_array($csGpTree[$csGroupID])) return $res;
		$child = $csGpTree[$csGroupID];
		ksort($child);
		reset($child);

		$disable = $csCkCanSel ? '' : ' disabled';
		foreach($child as $val) {
			if ($val <= 0) continue;
			$str = '';
			$img = '';
			$lang = getCaption($csGpCsData[$val]['caption']);

			if (array_key_exists($val, $csGpTree)) {
				$str = csGroup2HTML($val, $csCkBox, $csCkCanSel, $csShowCS);
				// �ϥ�
				if (empty($str)) {
					$img = '<img src="' . $theme . 'dot.gif" width="9" height="15" align="absmiddle">';
				} else {
					$img = '<img src="' . $theme . 'plus.gif" width="9" height="15" align="absmiddle" attr="' . $val . '" onclick="csSelfGp(\'gp_' . $val . '\',1);">';
					$str = '<ol id="gp' . $val . '" class="cssOL" style="display:none;">' . $str . '</ol>';
				}
				$res .= '<li><span onmouseover="this.className=\'bg04\'" onmouseout="this.className=\'bg03\'">' . $img;
				if (in_array('group', $csCkBox)) {
					$ck = in_array($val, $csSelCsID) ? ' checked' : '';
					$res .= '<input type="checkbox" name="csid[]" id="ckgp' . $val . '" value="' . $val . '"' . $ck . $disable . '>';
				}
				$res .= $lang[$sysSession->lang] .  '</span></li>' . $str;
			} else if ($csShowCS){
				// $ck = in_array($value, $csid) ? ' checked' : '';
				list($class_num) = dbGetStSr('WM_class_main','count(*) as num','class_id=' . $val, ADODB_FETCH_NUM);
				if ($class_num == 0) continue;
				$img = '<img src="' . $theme . 'dot.gif" width="9" height="15" align="absmiddle">';
				$res .= '<li><span onmouseover="this.className=\'bg04\'" onmouseout="this.className=\'bg03\'">' . $img;
				if (in_array('class', $csCkBox)) {
					$ck = in_array($val, $csSelCsID) ? ' checked' : '';
					$res .= '<input type="checkbox" name="csid[]" id="ckgp' . $val . '" value="' . $val . '"' . $ck . $disable . '>';
					$res .= $lang[$sysSession->lang] . '</span></li>' . $str;
				}
			}
		}
		return $res;
	}

	function csGetGroupHTML($csGroupID, $csDataFileds, $csCkBox, $csCkCanSel, $csShowSchool, $csShowOtherGP, $csShowCS, $csCSState) {
		global $sysSession, $sysConn, $MSG, $theme, $csGpTree, $csGpCsList, $csGpCsData, $csSelCsID;

		$disable = $csCkCanSel ? '' : ' disabled';
		$res = '<span id="csGpCsTree">';
		// �Ǯ� (Begin)
		if ($csShowSchool) {

			$res  .= '<ul class="cssUL"><li><span onmouseover="this.className=\'bg04\'" onmouseout="this.className=\'bg03\'">';
			if (in_array('school', $csCkBox)) {
				$ck = in_array(1000000, $csSelCsID) ? ' checked' : '';
				$res .= '<input type="checkbox" name="csid[]" id="ckgp1000000" value="1000000"' . $ck . $disable . '>';
			}
			$res .= $MSG['cs_tree_school'][$sysSession->lang] . $sysSession->school_name . '</span></li>';
		}
		$res .= '<ul class="cssOL">';
		// �Ǯ� (End)
		$res .= csGroup2HTML($csGroupID, $csCkBox, $csCkCanSel, $csShowCS);    // �w���ոs��
		// �����ոs�� (Begin)
		if ($csShowOtherGP) {
			$str = '';
			$img = '<img src="' . $theme . 'dot.gif" width="15" height="15" align="absmiddle">';
			if ($csShowCS) {
				foreach($csGpCsData as $key => $val) {
					list($class_num) = dbGetStSr('WM_class_main','count(*) as num','class_id=' . $key, ADODB_FETCH_NUM);
					if ($class_num == 0) continue;
					if (($key <= 1000000) || in_array($key, $csGpCsList)) continue;
					$lang = getCaption($val['caption']);

					$str .= '<li><span onmouseover="this.className=\'bg04\'" onmouseout="this.className=\'bg03\'">' . $img;
					if (in_array('class', $csCkBox)) {
						$ck = in_array($key, $csSelCsID) ? ' checked' : '';
						$str .= '<input type="checkbox" name="csid[]" id="ckgp' . $key . '" value="' . $key . '"' . $ck . $disable . '>';
						$str .= $lang[$sysSession->lang] . '</span></li>';
					}
				}
			}
			if (empty($str)) {
				$img = '<img src="' . $theme . 'dot.gif" width="9" height="15" align="absmiddle">';
			} else {
				$img = '<img src="' . $theme . 'plus.gif" width="9" height="15" align="absmiddle" attr="0" onclick="csAllGpStatus(this);">';
				$str = '<ol id="gp0" class="cssOL" style="display:none;">' . $str . '</ol>';
			}
			$res .= $str;
		}
		// �����ոs�� (End)
		if ($csShowSchool) $res .= '</ul>';    // �Ǯ�
		$res .= '</ul><span>';

		return $res;
	}

	/**
	 * ��ܯZ�Ÿs��
	 * @param integer $csGroupID : �Z�Ÿs�ժ��s�� (�w�]�G1000000)
	 *     1000000 : ���ժ��Z��
	 *            0 : �����ժ��Z��
	 * @param string  $csDataType : �^�Ǫ���Ʈ榡 (�w�]�GXML)
	 *     HTML : HTML �榡�����
	 *      XML : XML �榡�����
	 * @param array $csDataFileds : �n���o�������
	 * @param array $csCkBox : ��� checkbox (�w�]�G3[�ǮաA�s��])
	 *     array('school', 'group')
	 * @param boolean $csShowSchool : ��ܾǮ� (�w�]�GTRUE)
	 *     TRUE  : ���
	 *     FALSE : �����
	 * @param boolean $csShowOtherGP : ��ܥ����կZ�� (�w�]�GTRUE)
	 *     TRUE  : ���
	 *     FALSE : �����
	 * @param boolean $csShowCS : ��ܯZ�� (�w�]�GFALSE)
	 *     TRUE  : ���
	 *     FALSE : �����
	 * @param array $csCSState : �n��ܨ㦳�Y�Ǫ��A���Z�� (�w�]�G30[�}�ҡA�}��(���)�A�}��(����)�A�}��(�����B���)])
	 *     array(����<1>�A�}��<2>�A�}��(���)<4>�A�}��(����)<8>�A�}��(�����B���)<16>�A�ǳƤ�<32>�A���޲z��<64>)
	 * @return
	 **/
	function csGroupLayout(
		$csGroupID=1000000, $csDataType='XML', $csDataFileds=NULL, $csCkBox=NULL, $csCkCanSel=TRUE,
		$csShowSchool=TRUE, $csShowOtherGP=TRUE, $csShowCS=FALSE, $csCSState=NULL
	) {
		global $sysSession, $sysConn, $MSG, $csGpTree, $csGpCsList, $csGpCsData;

		// ��l�ƹw�]�� (Begin)
		if (is_null($csDataFileds) || !is_array($csDataFileds))
			$csDataFileds = array('class_id', 'caption');
		if (is_null($csCkBox) || !is_array($csCkBox))
			$csCkBox = array('school', 'group');
		if (is_null($csCSState) || !is_array($csCSState))
			$csCSState = array(1, 2, 3, 4);
		// ��l�ƹw�]�� (End)

		$res = '';
		if ($csDataType == 'XML') {
			$res  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
			$res .= '<manifest>';
			$res .= csGroup2XML($csGroupID);
			$res .= '</manifest>';
		} else if ($csDataType == 'HTML') {
			$res = csGetGroupHTML($csGroupID, $csDataFileds, $csCkBox, $csCkCanSel, $csShowSchool, $csShowOtherGP, $csShowCS, $csCSState);
		}
		return $res;
	}

	// echo csGroupLayout(1000000, 'HTML', NULL, NULL, false, true, false, NULL);
	csDataInit();
?>

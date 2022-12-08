<?php
	/**
	 * 班級群組
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


	// 參數設定 (Begin)
	if (!isset($csSelCsID)) $csSelCsID = array();    // 已勾選的班級編號
	if (!isset($theme))     $theme = "/theme/$sysSession->theme/academic/";    // 佈景的路徑

	// 班級中完整的欄位名稱
	chkSchoolId('WM_class_main');
	$csCsDefFileds = $sysConn->GetCol('show columns from WM_class_main');
	$csGpTree   = array();    // 群組 Tree 的結構
	$csGpCsList = array();    // 群組與已分組班級的清單
	$csGpCsData = array();    // 班級詳細資料
	// 參數設定 (End)

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
	 * 初始化
	 **/
	function csDataInit() {
		global $csGpTree, $csGpCsList, $csGpCsData;

		// 從資料庫中取得資料 (Begin)
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
		// 從資料庫中取得資料 (Begin)
	}
	
	/**
	 * 儲存班級跟群組的關係到資料庫中
	 * @param array $csGpID   : 班級所屬的群組陣列
	 * @param integer $csCsID : 班級編號
	 **/
	function csSaveCs2Gp($csGpID, $csCsID) {
		global $sysConn, $sysSession, $_SERVER;

		if (!is_array($csGpID))  return false;
		if ($csCsID <= 1000000) return false;
		$arows = 0;

		foreach ($csGpID as $val) {
			// 檢查是否已經存在
			$RS = dbGetStSr('WM_class_group', 'count(*) as cnt', "`parent`={$val} AND `child`={$csCsID}", ADODB_FETCH_ASSOC);
			if (intval($RS['cnt']) > 0) continue;
			// 取出最大的 order 並且加一
			$RS = dbGetStSr('WM_class_group', 'MAX(`permute`) as cnt', "parent={$val}", ADODB_FETCH_ASSOC);
			$order = $RS['cnt'] + 1;
			// 儲存
			dbNew('WM_class_group', '`parent`, `child`, `permute`', "{$val}, {$csCsID}, {$order}");
			if ($sysConn->Affected_Rows()) $arows++;
			wmSysLog($sysSession->cur_func, $sysSession->school_id ,0 ,0, 'manager', $_SERVER['PHP_SELF'], "儲存班級跟群組的關係,群組({$val}):班級({$csCsID})");
		}
		return $arows;
	}

	/**
	 * 將班級的所屬群組的關系同步到資料庫中
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
		// 先清除原本的資料
		$ocsid = csGetCsList($gid);
		foreach ($ocsid as $val) {
			$cid = intval($val['course_id']);
			dbDel('WM_class_group', "`parent`={$gid} AND `child`={$cid} ");
		}
		// 儲存新的班級
		foreach ($csid as $val) {
			csSaveCs2Gp(array($gid), $val);
		}
		// 重新取得資料
		csDataInit();
// 用來除錯的部分
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
	 * 建立班級的 XML 檔
	 * @param array $val (班級編號, 班級名稱, 報名日期 (開始), 報名日期 (結束), 上課日期 (開始), 上課日期 (結束), 已使用空間), ...
	 **/
	function csBuildCsXML($val) {
		global $sysSession, $MSG;
		if (!is_array($val) && !is_array($val[0])) return '';

		$result = '';
		$cnt = count($val);
		// 輸出班級的 XML (Begin)
		for ($i = 0; $i < $cnt; $i++) {
			if (empty($val[$i]['course_id'])) continue;
			// 班級名稱
			$lang = getCaption($val[$i]['caption']);

			// 教材名稱
			$content_id = intval($val[$i]['content_id']);
			$RS = dbGetStSr('WM_content', 'caption', "content_id={$content_id}", ADODB_FETCH_ASSOC);
			if (!($RS === false)) {
				$content = getCaption($RS['caption']);
			}
			// 報名日期
			$enroll = '';
			if (!empty($val[$i]['en_end'])) {
				$enroll  = empty($val[$i]['en_begin']) ? $MSG['cs_msg_now'][$sysSession->lang] : $MSG['cs_msg_from'][$sysSession->lang] . $val[$i]['en_begin'];
				$enroll .= $MSG['cs_msg_to'][$sysSession->lang] . $val[$i]['en_end'];

			}
			// 上課日期
			$study = '';
			if (!empty($val[$i]['st_end'])) {
				$study  = empty($val[$i]['st_begin']) ? $MSG['cs_msg_now'][$sysSession->lang] : $MSG['cs_msg_from'][$sysSession->lang] . $val[$i]['st_begin'];
				$study .= $MSG['cs_msg_to'][$sysSession->lang] . $val[$i]['st_end'];
			}
			// 已使用空間
			$val[$i]['quota_used'] = intval($val[$i]['quota_used']);

			$val[$i]['teacher'] = htmlspecialchars($val[$i]['teacher']);
			$val[$i]['texts']   = htmlspecialchars($val[$i]['texts']);
			$val[$i]['content'] = htmlspecialchars($val[$i]['content']);
			// 輸出 XML
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
		// 輸出班級的 XML (End)
		return $result;
	}

	/**
	 * 取得群組中班級編號
	 * @param integer $gid : 群組編號
	 * @return array $res
	 **/
	function csGetCsList($gid) {
		global $sysSession, $sysConn, $csGpTree, $csGpCsList, $csGpCsData;

		$gid = intval($gid);
		$child = array();

		if ($gid > 1000000) {            // 群組中的班級
			$RS = dbGetStMr('WM_class_group', '`child`', "`parent`={$gid} order by `permute` ASC", ADODB_FETCH_ASSOC);
			while (!$RS->EOF) {
				$val = intval($RS->fields['child']);
				if (!array_key_exists($val, $csGpTree)) {
					$child[] = $csGpCsData[$val];
				}
				$RS->MoveNext();
			}
		} else if ($gid == 1000000) {    // 全校班級
			foreach ($csGpCsData as $key => $val) {
				if (!array_key_exists($key, $csGpTree)) {
					$child[] = $val;
				}
			}
		} else {                          // 未分組班級
			foreach ($csGpCsData as $key => $val) {
				if (!in_array($key, $csGpCsList)) {
					$child[] = $val;
				}
			}
		}

		return $child;
	}

	/**
	 * 取得群組中班級編號
	 * @param integer $gid : 群組編號
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
	 * @param  Array  $group      : 班級群組的陣列
	 * @param  Array  $group_name : 班級群組的名稱
	 * @param  string $group_id   : 群組的編號
	 * @return string $result     : xml 格式的群組資料
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
	 * 將群組轉成 HTML 樣式
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
				// 圖示
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
		// 學校 (Begin)
		if ($csShowSchool) {

			$res  .= '<ul class="cssUL"><li><span onmouseover="this.className=\'bg04\'" onmouseout="this.className=\'bg03\'">';
			if (in_array('school', $csCkBox)) {
				$ck = in_array(1000000, $csSelCsID) ? ' checked' : '';
				$res .= '<input type="checkbox" name="csid[]" id="ckgp1000000" value="1000000"' . $ck . $disable . '>';
			}
			$res .= $MSG['cs_tree_school'][$sysSession->lang] . $sysSession->school_name . '</span></li>';
		}
		$res .= '<ul class="cssOL">';
		// 學校 (End)
		$res .= csGroup2HTML($csGroupID, $csCkBox, $csCkCanSel, $csShowCS);    // 已分組群組
		// 未分組群組 (Begin)
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
		// 未分組群組 (End)
		if ($csShowSchool) $res .= '</ul>';    // 學校
		$res .= '</ul><span>';

		return $res;
	}

	/**
	 * 顯示班級群組
	 * @param integer $csGroupID : 班級群組的編號 (預設：1000000)
	 *     1000000 : 全校的班級
	 *            0 : 未分組的班級
	 * @param string  $csDataType : 回傳的資料格式 (預設：XML)
	 *     HTML : HTML 格式的資料
	 *      XML : XML 格式的資料
	 * @param array $csDataFileds : 要取得那些欄位
	 * @param array $csCkBox : 顯示 checkbox (預設：3[學校，群組])
	 *     array('school', 'group')
	 * @param boolean $csShowSchool : 顯示學校 (預設：TRUE)
	 *     TRUE  : 顯示
	 *     FALSE : 不顯示
	 * @param boolean $csShowOtherGP : 顯示未分組班級 (預設：TRUE)
	 *     TRUE  : 顯示
	 *     FALSE : 不顯示
	 * @param boolean $csShowCS : 顯示班級 (預設：FALSE)
	 *     TRUE  : 顯示
	 *     FALSE : 不顯示
	 * @param array $csCSState : 要顯示具有某些狀態的班級 (預設：30[開課，開課(日期)，開課(身份)，開課(身份、日期)])
	 *     array(關閉<1>，開課<2>，開課(日期)<4>，開課(身份)<8>，開課(身份、日期)<16>，準備中<32>，限管理者<64>)
	 * @return
	 **/
	function csGroupLayout(
		$csGroupID=1000000, $csDataType='XML', $csDataFileds=NULL, $csCkBox=NULL, $csCkCanSel=TRUE,
		$csShowSchool=TRUE, $csShowOtherGP=TRUE, $csShowCS=FALSE, $csCSState=NULL
	) {
		global $sysSession, $sysConn, $MSG, $csGpTree, $csGpCsList, $csGpCsData;

		// 初始化預設值 (Begin)
		if (is_null($csDataFileds) || !is_array($csDataFileds))
			$csDataFileds = array('class_id', 'caption');
		if (is_null($csCkBox) || !is_array($csCkBox))
			$csCkBox = array('school', 'group');
		if (is_null($csCSState) || !is_array($csCSState))
			$csCSState = array(1, 2, 3, 4);
		// 初始化預設值 (End)

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

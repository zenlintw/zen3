<?php
	/**
	 * ���o��ӱЧ����O�� XML
	 *
	 * �إߤ���G2002/12/12
	 * @author  ShenTing Lin
	 * @version $Id: content_group_get.php,v 1.1 2010/02/24 02:38:16 saly Exp $
	 **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	require_once(sysDocumentRoot . '/lang/content_lang.php');
	
	$sysSession->cur_func='2400100400';
	$sysSession->restore();
	if (!aclVerifyPermission(2400100400, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable'))){

	}
	$csGpTree   = array();    // �s�� Tree �����c
	$csGpCsData = array();    // �Ч��ԲӸ��
	
	/**
	 * ��l��
	 **/
	function csDataInit() {
		global $csGpTree, $csGpCsData;

		// �q��Ʈw�����o��� (Begin)
		$RS = dbGetStMr('`WM_content_group`', '*', '1 order by `parent`, `permute`', ADODB_FETCH_ASSOC);
		while (!$RS->EOF) {
			$csGpTree[$RS->fields['parent']][$RS->fields['permute']] = $RS->fields['child'];
			$RS->MoveNext();
		}

		$RS = dbGetStMr('WM_content', 'content_id, caption', '1', ADODB_FETCH_ASSOC);
		while (!$RS->EOF) {
			$csGpCsData[$RS->fields['content_id']] = $RS->fields;
			$RS->MoveNext();
		}
		// �q��Ʈw�����o��� (Begin)
	}
	
	/**
	 * Group2XML()
	 * @param  Array  $group      : �ҵ{�s�ժ��}�C
	 * @param  Array  $group_name : �ҵ{�s�ժ��W��
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
			if (!array_key_exists($value, $csGpTree)) {
				$result .= '<content id="' . $value . '"></content>';
			} else {
				$lang = old_getCaption($csGpCsData[$value]['caption']);

				$result .= '<contents id="'   . $value               . '">'              .
				           '<title default="' . $sysSession->lang    . '">'              .
				           '<big5>'           . $lang['Big5']        . '</big5>'        .
				           '<gb2312>'         . $lang['GB2312']      . '</gb2312>'      .
				           '<en>'             . $lang['en']          . '</en>'          .
				           '<euc-jp>'         . $lang['EUC-JP']      . '</euc-jp>'      .
				           '<user-define>'    . $lang['user_define'] . '</user-define>' .
				           '</title>' .
				           csGroup2XML($value, false, $indent + 1) .
				           '</contents>';
			}
		}
		if ($indent == 0) {
			$res  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
			$res .= '<manifest>' . $result;
			if ($csShowOtherGP) {
				$res .= '<contents id="0">' .
				        '<title default="' . $sysSession->lang                          . '">'              .
				        '<big5>'           . $MSG['cs_tree_other_group']['Big5']        . '</big5>'        .
				        '<gb2312>'         . $MSG['cs_tree_other_group']['GB2312']      . '</gb2312>'      .
				        '<en>'             . $MSG['cs_tree_other_group']['en']          . '</en>'          .
				        '<euc-jp>'         . $MSG['cs_tree_other_group']['EUC-JP']      . '</euc-jp>'      .
				        '<user-define>'    . $MSG['cs_tree_other_group']['user_define'] . '</user-define>' .
				        '</title>' .
				        '</contents>';
			}
			$res .= '</manifest>';
			$result = $res;
		}
		return $result;
	}
	
	header("Content-type: text/xml");
	// �o�䪺�P�_�i��|�]�� PHP ���������Ӧ����ܰ�
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if ($dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			csDataInit();
			$result = csGroup2XML(100000, false);
			if ($result != '') 
				die($result);
		}
	}
	
	die('<?xml version="1.0" encoding="UTF-8"?><manifest></manifest>');
?>

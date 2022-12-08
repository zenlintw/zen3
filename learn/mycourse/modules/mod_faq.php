<?php
	/**
	 * 常見問題
	 *
	 * @since   2004/10/06
	 * @author  Kuo Yang Tsao
	 * @version $Id: mod_faq.php,v 1.1 2010/02/24 02:39:08 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	if (!defined('MYCOURSE_MODULE') || MYCOURSE_MODULE === false) {
		include_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
		include_once(sysDocumentRoot . '/lib/interface.php');
		include_once(sysDocumentRoot . '/learn/mycourse/mycourse_lib.php');
		include_once(sysDocumentRoot . '/lib/acl_api.php');
	}
	require_once(sysDocumentRoot . '/lib/common.php');
	
	$sysSession->cur_func='700400300';
	$sysSession->restore();
	if (!aclVerifyPermission(700400300, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	/*
	 * getFAQ()
	 *    取得常見問題
	 *    @pram string $type : 'news'=最新消息 'faq'=常見問題 'syssug'=系統建議
	 *    @return array $result :
	 */
	function getFAQ() {
		global $sysSession, $sysConn;

		$xml_file = sysDocumentRoot .'/base/' . $sysSession->school_id . '/system/faq.xml';
		$result = Array();

		if(!$xml = domxml_open_file( $xml_file ))	return null;
		$root = $xml->document_element();
		$childs = $root->get_elements_by_tagname("faq");
		if(count($childs)==0)	return null;
		foreach( $childs as $child) {
			$id = $child->get_attribute('node');
			if (!preg_match('/^[0-9]{9}$/', $id)) continue;
			$result[$id] = Array('date'=>getNodeValue($child, 'time'),
								'title'=>getNodeValue($child, 'caption'),
								'author'=>getNodeValue($child, 'poster'));

		};
		return $result;
	}

		$isEdit = ($sysSession->username != 'guest');
		$lines = 3;
		$id = 'MyFAQ';
		// 主要視窗大小的設定 (Begin)
		$wd = $defSize - 10;
		$dd = intval($wd) - 5;
		$Ld = $defLSize - 15;
		$Rd = $defRSize - 15;
		// 主要視窗大小的設定 (End)
		$id = showXHTML_mytitle_B($id, $MSG['tabs_faq'][$sysSession->lang], $wd, $isEdit);

			showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" class="cssTable" id="tab_' . $id . '"');
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td_B('nowrap="nowrap"');
						echo '<div align="left" id="div_' . $id . '" style="width: ' . $dd . 'px; overflow: hidden; padding: 10px 0px 0px 15px;">';
							$theme = (empty($sysSession->theme)) ? '/theme/default/learn/' : "/theme/{$sysSession->theme}/{$sysSession->env}/";
							$img = '<img src="' . $theme . 'my_dot1.gif" width="12" height="12" border="0" align="absmiddle">';

							$RS = getFAQ();
							$cnt = 0;
							$total = 0;
						if ($RS) {
								$total = count($RS);
								foreach($RS as $k=>$v) {
									echo $img . '<a href="/learn/news/faq.php?node='.$k.'"  class="cssAnchor" title="' . $v['title'] . '">' . $v['title'] . '</a><br>';
									$cnt++;
								}
							}
							if ($cnt <= 0) {
								echo '<div style="padding: 0px 0px 10x 0px;">' . $MSG['msg_no_faq'][$sysSession->lang] . '</div>';
							} else {
								showXHTML_mytitle_more('onclick="mod_' . $id . '_more(); return false;"');
							}
						echo '</div>';
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
			$msg = ($isEdit) ? $MSG['msg_reposition_here'][$sysSession->lang] : '&nbsp;';
			showXHTML_mytitle_postit($id, $msg);
		showXHTML_mytitle_E();

		$js = <<< BOF
		// 若要 resize，則 function name 必須為 mod_{id}_resize
		function mod_{$id}_resize() {
			if (dragID != "{$id}") return false;
			var nodes = null;
			var objName = "{$id}";
			var obj = document.getElementById("div_" + objName);
			var isSmall = false;
			if ((typeof(obj) != "object") || (obj == null)) return false;
			isSmall = (parseInt(curSize) <= {$defLSize});
			obj.style.width = isSmall ? "{$Ld}px" : "{$Rd}px";
		}

		function mod_{$id}_more() {
			location.replace('/learn/news/index_faq.php');
		}
BOF;
		showXHTML_script('inline', $js);

?>

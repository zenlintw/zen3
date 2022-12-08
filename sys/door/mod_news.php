<?php
	/**
	 * 最新消息
	 *
	 *     所需樣板名稱：news.htm
	 *
	 * @since   2004/10/27
	 * @author  ShenTing Lin
	 * @version $Id: mod_news.php,v 1.1 2010/02/24 02:40:20 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/filter_spec_char.php');
	
	$sysSession->cur_func = '1300100100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	/*
	 * getNews()
	 *    取得最新消息
	 *    @return array $result :
	 */
	function getNews() {
		global $sysSession, $bid;

		$result = array();
		$xml_file = sysDocumentRoot .'/base/' . $sysSession->school_id . '/system/news.xml';
		if (!file_exists($xml_file)) return $result;

		if (!$xml = domxml_open_file($xml_file)) return $result;
		$root = $xml->document_element();
		$bid = intval($root->get_attribute('board'));
		$childs = $root->get_elements_by_tagname('news');
		if (count($childs) == 0) return $result;
		foreach ($childs as $child) {
			$id = $child->get_attribute('node');
			$RS =
			$result[$id] = array(
				'id'    => $id,
				'date'  => getNodeValue($child, 'time'),
				'title' => getNodeValue($child, 'caption'),
				'author'=> getNodeValue($child, 'poster')
			);

		};
		// ksort($result);
		return $result;
	}

	$bid = '';
	$nid = '';
	function mod_news_id($val) {
		global $nid;
		$nid = preg_replace('/\D+/', '', $val);
		return $nid;
	}

	function mod_news_data($val) {
		$time_ary = explode(' ',$val);
		return $time_ary[0];
	}

	function mod_news_content($val) {
		global $sysSession, $MSG, $sysNewsContLeng, $bid, $nid;

		list($newsCont) = dbGetStSr('WM_bbs_posts', '`content`', "`board_id`='{$bid}' AND `node`='{$nid}'", ADODB_FETCH_NUM);
		$newsCont = preg_replace(array('|--[^P]+Posting from [^<]*<br />\s*|','!\s*(<br( /)?>\s*)+$!i'),
								 array('',''),
								 strip_scr($newsCont));
		$small    = getLimitStr($newsCont, $sysNewsContLeng);
		$more     = (strlen($newsCont) <= $sysNewsContLeng) ? '' : '...<a href="javascript:;" class="cssNewsAnchor" onclick="newsReadMore(\'n' . $nid . '\'); return false;">' . $MSG['btn_news_more'][$sysSession->lang] . '</a>';
		return '<span>' . $small . '</span><span></span>' . $more . '<span id="n' . $nid . '" style="display: none;">' . $newsCont . '</span>';
	}

	function mod_news() {
		global $sysSession, $MSG;

		$tpl = getTemplate('news.htm');
		$myTemplate = new Wise_Template($tpl);
		$ary = array('<%NEWS_ID%>', '<%NEWS_DATE%>', '<%NEWS_TITLE%>', '<%NEWS_CONTENT%>');
		$oper = array(
			'id'    => 'mod_news_id("%s")',
			'date'  => 'mod_news_data("%s")',
			'author'=> 'mod_news_content("%s")'
		);
		$rs = getNews();
		if (count($rs) > 0) {
			/* // 目前平台不允許 guest 可參考 bug report NO.519 & 511
			$myTemplate->add_replacement('<%SHOW_MORE_BEGIN%>', '');
			$myTemplate->add_replacement('<%SHOW_MORE_END%>'  , '');
			*/
			if ($sysSession->username != 'guest') {
    			$myTemplate->add_replacement('<%SHOW_MORE_BEGIN%>+<%SHOW_MORE_END%>', '', true);
            }
			$myTemplate->add_replacement('<%DIV_NEWS_BEGIN%>' , '');
			$myTemplate->add_replacement('<%DIV_NEWS_END%>'   , '');
			$myTemplate->add_replacement('<%DIV_NO_NEWS_BEGIN%>+<%DIV_NO_NEWS_END%>', '', true);
			$myTemplate->add_recordset('<%NEWS_ITEM_BEGIN%>+<%NEWS_ITEM_END%>'      , $rs, $ary, $oper);
		} else {
			$myTemplate->add_replacement('<%DIV_NO_NEWS_BEGIN%>'                , '');
			$myTemplate->add_replacement('<%DIV_NO_NEWS_END%>'                  , '');
			$myTemplate->add_replacement('<%MSG_NO_NEWS%>'                      , $MSG['msg_no_news'][$sysSession->lang]);
			$myTemplate->add_replacement('<%DIV_NEWS_BEGIN%>+<%DIV_NEWS_END%>'  , '', true);
			$myTemplate->add_replacement('<%SHOW_MORE_BEGIN%>+<%SHOW_MORE_END%>', '');
		}
		$myTemplate->add_replacement('<%BTN_CLOSE%>'      , $MSG['btn_close'][$sysSession->lang]);
		$myTemplate->add_replacement('<%MSG_NEWS_DETAIL%>', $MSG['msg_news_detail'][$sysSession->lang]);
		genDefaultTrans($myTemplate);
		return $myTemplate->get_result(false);
	}

?>

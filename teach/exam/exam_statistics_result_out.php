<?php
	/**
	 * ※ 三合一統計
	 *
	 * @since   2004/09/22
	 * @author  Wiseguy Liang
	 * @version $Id: exam_statistics_result_out.php,v 1.1 2010/04/13 07:26:17 small Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/

	if (!defined('QTI_STAT_EXPORT')) {
		die('Access Deny!');
	}

    set_time_limit(0);
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/' . QTI_which . '_teach.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/filter_spec_char.php');
	require_once(sysDocumentRoot . '/teach/exam/exam_stat_class.php');
	require_once(sysDocumentRoot . '/teach/exam/QTI_transformer.php');
	require_once(sysDocumentRoot . '/lib/sync_lib.php');

	//ACL begin
	if (QTI_which == 'exam') {
		$sysSession->cur_func='1600400300';
	}
	else if (QTI_which == 'homework') {
		$sysSession->cur_func='1700400300';
	}
	else if (QTI_which == 'questionnaire') {
		$sysSession->cur_func='1800300400';
	}
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

	}
	//ACL end

	function count_percent(&$val, $key, $t)
	{
        if ($t == 0)
            $val = 0;
        else
		    $val = $val/$t * 100;
	}

// {{{ 函數宣告 begin
	/*
	 * 以下function是取至/learn/exam/item_fetch.php裡面的
	 * 由於裡面參數許多是global變數，所以先copy過來這邊單純使用，避免發生不必要的問題
	 * [FLM] NO414,NO422,NO433
	 */
	function setEncoding($xml, $encoding='UTF-8')
	{
	    $tmp = preg_replace(array('!\s*xmlns:wm="http://www.sun.net.tw/WisdomMaster"!', '!<questestinterop\b!'),
	                        array('', '<questestinterop xmlns:wm="http://www.sun.net.tw/WisdomMaster"'),
	                        $xml);

		$regs = array();
		if (preg_match('/<\?xml\b[^>]*\?>/isU', $tmp, $regs))
		{
			if (preg_match('/\bencoding\s*=\s*/isU', $regs[0]))
				return $tmp;
			else
				return preg_replace('/\?>/', ' encoding="' . $encoding . '"?>', $tmp, 1);
		}
		else
			return '<?xml version="1.0" encoding="UTF-8"?>' . $tmp;
	}

	/**
	 * 將試卷中的 <item> 轉換為真實題目
	 */
	function replaceItemToComplete(){
		global $dom,$root, $ctx, $sysConn;

		$ids = array();
		$nodes = $dom->get_elements_by_tagname('item');
		foreach($nodes as $item)
		{
			$ids[] = $item->get_attribute('id');
			$item->set_attribute('score', $item->get_attribute('score'));	// 強制設定一個 score 屬性
		}

		if ($ids){
			$idents = 'ident in ("' . implode('","', $ids) . '")';

			$real_items = $sysConn->GetAssoc('select ident,content from WM_qti_' . QTI_which . '_item where ' . $idents);
			$ids = array_flip($ids);

			$result = setEncoding($dom->dump_mem());
			$not_exist_idx = array();
			$regs = array();
			if (preg_match_all('!<item\b[^>]*\bid="([^"]+)"[^>]*(\bscore="([^"]+)"([^>]*)?)?(/>|>[^<]*</item>)!isU', $result, $regs))
			{
				// 把配分收集起來
				$scores = array();
				foreach($regs[1] as $k => $v)
				{
					$scores[$v] = $regs[3][$k];
				}

				// 將分數填進真實 item xml 中
				foreach($real_items as $k => $v)
				{
					$real_items[$k] = preg_replace('#<decvar[^>]*/>#isU', sprintf('<decvar vartype="Integer" defaultval="%.1f" />', $scores[$k]), $v);
				}

				// 用代換的方式，把試卷中的 item 代換為真實 xml
				$replaces = array();
				foreach($regs[1] as $k)
				{
					if (!$real_items[$k]) $not_exist_idx[] = $k;
					$replaces[] = $real_items[$k];
				}

				$result = str_replace($regs[0], $replaces, $result);
			}
			$dom = domxml_open_mem($result);
			$root = $dom->document_element();
			$ctx = xpath_new_context($dom);

			if (is_array($not_exist_idx)) {
				$not_exist_idx = array_flip($not_exist_idx);
				foreach($not_exist_idx as $id => $foo)
				{
					$node = getElementById($root, $id);
					if (!is_null($node))
					{
						$parent = $node->parent_node();
						$parent->remove_child($node);
					}
				}
			}
			id2ident($dom);
		}
	}

	/**
	 * 將試卷的 id 換成 ident
	 */
	function id2ident(&$doc){
		if (is_null($doc)) return;
		$secTags = array('section', 'assessment', 'objbank');
		foreach($secTags as $tag){
			$nodes = $doc->get_elements_by_tagname($tag);
			if (is_array($nodes)) foreach($nodes as $node){
				$nn = $node->get_attribute('id');
				$node->set_attribute('ident', $nn);
				$node->remove_attribute('id');
			}
		}
	}
// }}} 函數宣告 end

	if (!defined('QTI_env'))
		list($foo, $topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
	else
		$topDir = QTI_env;

	$course_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id; //10000000;


	$qti_item_types = array(1 => '/presentation//response_lid/render_choice',
							2 => '/presentation//response_str/render_fib',
							3 => '/presentation//response_num/render_fib');

	$ADODB_FETCH_MODE = ADODB_FETCH_NUM;

	/*[FLM] NO414,NO422,NO433 衍生問題
	 * 原因:測驗試卷有亂數出題的功能，導致匯出是以第一份考卷的題目為依據輸出
	 * 處理方式:先取最初的所有題目出來
	 */
	if(QTI_which == 'exam'){
		list($title, $content, $begin_time, $close_time) = dbGetStSr('WM_qti_' . QTI_which . '_test', 'title, content, begin_time, close_time', "exam_id={$_POST['lists']}", ADODB_FETCH_NUM);

		if(!$dom = domxml_open_mem(str_replace(' xmlns="http://www.imsglobal.org/question/qtiv1p2/qtiasiitemncdtd/ims_qtiasiv1p2.dtd"', '', $content))) {
			die('Error while parsing the document.');
		}

		$root = $dom->document_element();

		$ctx = xpath_new_context($dom);

		$sos = $dom->get_elements_by_tagname('selection_ordering');

		foreach ($sos as $node) {
			$pnode = $node->parent_node();
			$pnode->remove_child($node);
		}

		replaceItemToComplete();

		$dom = @domxml_open_mem(preg_replace(array('/<item\s+xmlns="http:\/\/www\.imsglobal\.org\/question\/qtiv1p2\/qtiasiitemncdtd\/ims_qtiasiv1p2\.dtd"\s+xmlns:wm="http:\/\/www\.sun\.net\.tw\/WisdomMaster"\s+/',
							  '/<item\s+xmlns:wm="http:\/\/www\.sun\.net\.tw\/WisdomMaster"\s+/',
							  '/<item\s+xmlns="http:\/\/www\.imsglobal\.org\/question\/qtiv1p2\/qtiasiitemncdtd\/ims_qtiasiv1p2\.dtd"\s+/'
							 ),
						    array('<item ',
						          '<item ',
								  '<item '
						         ),
						    setEncoding($dom->dump_mem())
						   )
				      );
	}else{
		list($title, $begin_time, $close_time) = dbGetStSr('WM_qti_' . QTI_which . '_test', 'title, begin_time, close_time', "exam_id={$_POST['lists']}", ADODB_FETCH_NUM);
	}

    $title = (strpos($title, 'a:') === 0) ?
             unserialize($title):
             array('Big5'		 => $title,
                   'GB2312'		 => $title,
                   'en'			 => $title,
                   'EUC-JP'		 => $title,
                   'user_define' => $title
             	  );

	$RS = dbGetStMr('WM_qti_' . QTI_which . '_result', 'content,examinee,time_id', "exam_id={$_POST['lists']} and status != 'break' order by submit_time DESC", ADODB_FETCH_NUM);
	if ($sysConn->ErrorNo()) {
	   $errMsg = $sysConn->ErrorNo() . ': ' . $sysConn->ErrorMsg();
	   wmSysLog($sysSession->cur_func, $course_id , 0 , 4, 'auto', $_SERVER['PHP_SELF'], $errMsg);
	   die($errMsg);
	}
	$tt = $RS->RecordCount();
	$total        = 0;
	$failure      = 0;
	$result_array = array();

	ob_start();
	if ($RS && $tt)
	{
		$wiseStat = new QTI_exam_stat();
		// $progree  = new ProgreeBar;
		// $progree->showBar($MSG['msg_parsing'][$sysSession->lang]);

		//[FLM] NO414,NO422,NO433 衍生問題
		if(QTI_which == 'exam'){
			$wiseStat->parse(preg_replace('/\sxmlns="[^"]*"/', '',setEncoding($dom->dump_mem())));
		}

		while(list($content_xml,$examinee,$time_id) = $RS->FetchRow())
		{
		    if (empty($content_xml))
			{
		       $xml_path = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/C/%09u/%s/',
				  					 $sysSession->school_id,
				  					 $sysSession->course_id,
				  					 QTI_which,
				  					 $_POST['lists'],
				  					 $examinee);
		       $file = 	$time_id.'.xml';	  	
		
		       $full_path = $xml_path.$file;
		       if (is_file($full_path)) {
		           $content_xml = file_get_contents($full_path);
		       }
			}
		    $wiseStat->parse(preg_replace('/\sxmlns="[^"]*"/', '', UTF8_decode::u8decode($content_xml)));
		    call_user_func(array(&$progree,'step'), round(++$i/$tt,2)*100);
		}
		$wiseStat->endParse();
		// $progree->close();
		$total        = $wiseStat->total;
		$failure      = $wiseStat->failure;
		$result_array = $wiseStat->result_array;
	}
	ob_end_clean();

		$ary = array(array($MSG['statistics_table'][$sysSession->lang]));
		showXHTML_tabFrame_B($ary, 1, '', 'ListTable', 'style="display: inline;"', false, false);
			showXHTML_table_B('id ="mainTable" width="740" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
			    showXHTML_tr_B('class="cssTrEvn"');
			        showXHTML_td_B('colspan="5" style="padding: 0"');
			            showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3"');
			                showXHTML_tr_B('class="cssTrOdd"');
			                    showXHTML_td('', $MSG['exam_name'][$sysSession->lang]);
			                    showXHTML_td('', htmlspecialchars($title[$sysSession->lang]));
			                showXHTML_tr_E();
			                showXHTML_tr_B('class="cssTrEvn"');
			                    showXHTML_td('', $MSG['exam_duration'][$sysSession->lang]);
			                    showXHTML_td('', $MSG['from'][$sysSession->lang] . ' ' . (strpos($begin_time, '0000') === 0 ? $MSG['now'][$sysSession->lang] : $begin_time) . ' ' . $MSG['to'][$sysSession->lang] . ' ' . (strpos($close_time, '9999') === 0 ? $MSG['forever'][$sysSession->lang]: $close_time));
			                showXHTML_tr_E();
			                showXHTML_tr_B('class="cssTrOdd"');
			                    showXHTML_td('', $MSG['total_quests'][$sysSession->lang]);
			                    showXHTML_td('', $tt+$failure);
			                showXHTML_tr_E();
			                showXHTML_tr_B('class="cssTrEvn"');
			                    showXHTML_td('', $MSG['valid_quests'][$sysSession->lang]);
			                    showXHTML_td('', $tt);
			                showXHTML_tr_E();
			                showXHTML_tr_B('class="cssTrOdd"');
			                    showXHTML_td('', $MSG['invalid_quests'][$sysSession->lang]);
			                    showXHTML_td('', $failure);
			                showXHTML_tr_E();
			            showXHTML_table_E();
			        showXHTML_td_E();
			    showXHTML_tr_E();

				showXHTML_tr_B('class="cssTrHead"');
					showXHTML_td('align="center"', $MSG['serial_no'][$sysSession->lang]);
					showXHTML_td('align="center"', $MSG['item_desc'][$sysSession->lang]);
					showXHTML_td('align="center"', $MSG['candidate_item'][$sysSession->lang]);
					showXHTML_td('align="center"', $MSG['amount'][$sysSession->lang]);
					showXHTML_td('align="center"', $MSG['percent'][$sysSession->lang]);
				showXHTML_tr_E();

				$idents = array_keys($result_array);
				if (count($idents) > 0) {
					// 取出題目的題型
					$item_type_ary = dbGetAssoc('WM_qti_' . QTI_which . '_item', 'ident, type', 'ident in ("' . implode('","', $idents) . '")');
					// 僅統計 是非題、單選題、複選題
					foreach($item_type_ary as $k => $v) {
						if (!in_array($v, array('1', '2', '3'))) {
							unset($result_array[$k]);
						}
					}
				}

				$i = 1; $o=''; $checker = true;
				foreach($result_array as $id => $item)
				{
					$first = true; $second = true; $col = false; $rn = 0;

					ob_start();
					foreach($item as $choice => $node)
					{
						if($first)
						{
							$first = false; continue;
						}

						$local_count=0; $x = array();
						ob_start();
						$rn += count($node);
						foreach ($node as $k => $v) {
							showXHTML_tr_B($checker ? 'class="cssTrEvn"' : 'class="cssTrOdd"');
								if ($second)
								{
									// $r = sprintf(' rowspan="%d" align="center"', count($item, 1)-1);
									$r = ' rowspan="ROW_SPAN" align="center"';
									showXHTML_td($r, $i++);
									showXHTML_td('align="left"'. $r, str_replace('%', '%%%%', strip_scr($item['title'])));
									$second = false;
								}
								// FUJITSU CUSTOM
								if ($item_type_ary[$id] === '1') {
									if ($v['caption'] == 'Agree')
										$v['caption'] = $MSG['msg_agree'][$sysSession->lang];
									else if ($v['caption'] == 'Disagree')
										$v['caption'] = $MSG['msg_disagree'][$sysSession->lang];
								}
								$v['caption'] = preg_replace('/&lt;br[\s\/]*&gt;/i', '<br>', $v['caption']);

								showXHTML_td($col ? 'style="background-color: #FFFFCC;"' : '', str_replace('%', '%%%%', $v['caption']));
								showXHTML_td(($col ? 'style="background-color: #FFFFCC;"' : '') . ' align="right"', $v['count']);
								showXHTML_td_B($col ? 'style="background-color: #FFFFCC;"' : '');
									showXHTML_table_B('width="100%%%%" style="display: inline"');
										showXHTML_tr_B('class="font01"');
											showXHTML_td('', '<img src="/theme/default/learn/bar-p.gif" align="absmiddle" height="13" width="%%d"><img src="/theme/default/learn/bar-p-1.gif" align="absmiddle" height="13" width="4">');
											showXHTML_td('align="right"', '%.2f%%%%');
										showXHTML_tr_E();
									showXHTML_table_E();
								showXHTML_td_E();
							showXHTML_tr_E();
							$local_count += intval($v['count']);
							$x[] = intval($v['count']);
						}
						$col ^= true;
						array_walk($x, 'count_percent', $local_count);
						$o = vsprintf(vsprintf(ob_get_contents(), $x), $x); ob_end_clean();
						echo $o; unset($x);
					}
					$o = ob_get_contents();
					ob_end_clean();

					if (strlen($o) > 0) $checker ^= true;

					echo str_replace('ROW_SPAN', $rn, $o);
					unset($o, $rn);
				}
			showXHTML_table_E();
		showXHTML_tabFrame_E();
?>

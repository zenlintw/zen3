<?php
	/**
	 * ※ 三合一統計
	 *
	 * @since   2004/09/22
	 * @author  Wiseguy Liang
	 * @version $Id: exam_statistics_result.php,v 1.11 2010-11-02 03:50:44 fins Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/

    set_time_limit(0);
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/' . QTI_which . '_teach.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/filter_spec_char.php');
	require_once(sysDocumentRoot . '/teach/exam/exam_stat_class.php');
	require_once(sysDocumentRoot . '/teach/exam/QTI_transformer.php');
	require_once(sysDocumentRoot . '/lib/sync_lib.php');
	require_once(sysDocumentRoot . '/mooc/common/common.php');

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

	if (!isset($_POST['ticket'])) {	// 檢查 ticket 是否存在
	   wmSysLog($sysSession->cur_func, $course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'Access Denied!');
	   die('Access denied.');
	}
	$ticket = md5(sysTicketSeed . $course_id . $_POST['referer']);		// 產生 ticket
	if ($ticket != $_POST['ticket']) {	// 檢查 ticket
	   wmSysLog($sysSession->cur_func, $course_id , 0 , 2, 'auto', $_SERVER['PHP_SELF'], 'Fake Ticket!');
	   die('Fake ticket.');
	}
	if (!eregi('^[0-9A-Z_]+$', $_POST['lists'])) {	// 檢查 lists
	   wmSysLog($sysSession->cur_func, $course_id , 0 , 3, 'auto', $_SERVER['PHP_SELF'], 'Fake lists!');
	   die('Fake lists.');
	}

	$ticket = md5(sysTicketSeed . $course_id . $_POST['lists']);

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
             getCaption($title):
             array('Big5'		 => $title,
                   'GB2312'		 => $title,
                   'en'			 => $title,
                   'EUC-JP'		 => $title,
                   'user_define' => $title
             	  );

	$RS = dbGetStMr('WM_qti_' . QTI_which . '_result', 'content,examinee,time_id', "exam_id={$_POST['lists']} and status != 'break' ORDER BY time_id, submit_time DESC", ADODB_FETCH_NUM);
	if ($sysConn->ErrorNo()) {
	   $errMsg = $sysConn->ErrorNo() . ': ' . $sysConn->ErrorMsg();
	   wmSysLog($sysSession->cur_func, $course_id , 0 , 4, 'auto', $_SERVER['PHP_SELF'], $errMsg);
	   die($errMsg);
	}
	$tt = $RS->RecordCount();
	$total        = 0;
	$failure      = 0;
	$result_array = array();

	if ($RS && $tt)
	{
		$wiseStat = new QTI_exam_stat();
		$progree  = new ProgreeBar;
		$progree->showBar($MSG['msg_parsing'][$sysSession->lang]);

		//[FLM] NO414,NO422,NO433 衍生問題
		if(QTI_which == 'exam'){
			$wiseStat->parse(preg_replace('/\sxmlns="[^"]*"/', '',setEncoding($dom->dump_mem())));
		}

		while (list($content_xml,$examinee,$time_id) = $RS->FetchRow()) {
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
		$progree->close();
		$total        = $wiseStat->total;
		$failure      = $wiseStat->failure;
		$result_array = $wiseStat->result_array;
	}

	$sysMailRule = sysMailRule;
	$js = <<< EOB

function ShowDetailResult(){
	var obj = document.getElementById('DetailForm');
	obj.submit();
}

function mailMe()
{
	var form = document.getElementById('OutputForm');
	var mail;
	// FUJITSU
	mail = prompt('{$MSG['msg_email_prompt'][$sysSession->lang]}', form.email.value);
	if (mail == null) return;
	if (typeof(mail) == 'undefined' || mail == '' || !{$sysMailRule}.test(mail))
	{
	    alert('{$MSG['msg_email_error'][$sysSession->lang]}');
	    return;
	}

	form.op.value = 'mail';
	form.email.value = mail;
	// form.content.value = document.getElementById('ListTable').innerHTML.replace(/<input [^>]*\btype=("button"|button)\b[^>]*>\s*/gi, '').replace(/<A href="\/wmhelp\.php[^>]*><IMG [^>]*><\/A>/i, '');
	form.submit();
}

function exportDone()
{
    var form = document.getElementById('exportForm');

    document.getElementById('exportTable').style.display='none';
    // form.content.value = document.getElementById('ListTable').innerHTML.replace(/<input [^>]*\btype=("button"|button)\b[^>]*>\s*/gi, '').replace(/<A href="\/wmhelp\.php[^>]*><IMG [^>]*><\/A>/i, '');
    form.submit();
}

function displayDialog(name)
{
	var obj = document.getElementById(name);
	// 對話框左邊對齊 = [捲動的左座標] + [該 Frame 的寬度] - [對話框寬度(480)] 再左移 10 個 pixel
	obj.style.left  = document.body.scrollLeft + document.body.offsetWidth - 460;
	// 對話框上緣對齊 = [捲動的上座標] 下移 10 個 pixel
	obj.style.top   = document.body.scrollTop  + 30;
	obj.style.display = '';
}

function checkExport()
{
	var obj = document.getElementById('exportForm');
	var elements = obj.getElementsByTagName('input');
	for(var i=0; i<elements.length; i++)
	{
		if (elements[i].type == 'checkbox' && elements[i].checked)
		{
			elements[elements.length-2].disabled = false;
			return;
		}
	}
	elements[elements.length-2].disabled = true;
}

EOB;

	showXHTML_head_B($MSG['title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	if ($profile['isPhoneDevice']) {
            echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
            echo '<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">';
            echo '<link href="/theme/default/bootstrap336/css/bootstrap.min.css" rel="stylesheet" />';
            echo '<link rel="stylesheet" href="/sys/tpl/vendor/font-awesome/css/font-awesome.css" />';
            echo '<script type="text/javascript" src="/lib/jquery/jquery.new.min.js?20200113"></script>';
            echo '<script type="text/javascript" src="/theme/default/bootstrap336/js/bootstrap.min.js"></script>';
            require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
            $smarty->display('phone/learn/exam_style.tpl');
        }
	showXHTML_script('include', '/lib/dragLayer.js');
	showXHTML_script('inline', $js);
	
	
	echo '<style>';
echo '		
		@media print {
		    #ImgL1,#ImgR1 {
                content: url("");
            }
		}
	
}
';
echo '</style>';
	showXHTML_head_E();

	showXHTML_body_B();

		$ary = array(array($MSG['statistics_table'][$sysSession->lang]));
		echo "<center>\n";
		showXHTML_tabFrame_B($ary, 1, '', 'ListTable');
			showXHTML_table_B('id ="mainTable" width="'.(($profile['isPhoneDevice'])?'100%':'740').'" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
			    showXHTML_tr_B();
					if ($topDir == 'learn')
					{
				        showXHTML_td_B('colspan="5" class="cssTrHead" align="center"');
                            showXHTML_input('button', '', $MSG['btnClose'][$sysSession->lang], '', 'class="cssBtn" onclick="self.close();"');
				        showXHTML_td_E();
					}
					else
					{
				        showXHTML_td_B('colspan="2" class="cssTrHead"');
							showXHTML_input('button', '', $MSG['btn_back_questionnaire_list'][$sysSession->lang], '', 'class="cssBtn" onclick="' . (QTI_which == 'questionnaire' ? 'location.href=\'exam_statistics.php\'' : 'location.href=\'exam_correct_list.php\'') . ';"');
							showXHTML_input('button', '', $MSG['btnDetailResult'][$sysSession->lang],  '', 'class="cssBtn" onclick="ShowDetailResult();"');
				        showXHTML_td_E();
				        showXHTML_td_B('colspan="3" class="cssTrHead" align="right"');
				            showXHTML_input('button', '', $MSG['email this page'][$sysSession->lang],  '', 'class="cssBtn" onclick="mailMe();"');
				            showXHTML_input('button', '', $MSG['print this page'][$sysSession->lang],  '', 'class="cssBtn" onclick="self.print();"');
                                            // ipad基本上不支援下載zip檔案
                                            // 需搭配使用 iDownload Pro app 貼上url，透過app下載
                                            $ipad = strstr(strtolower($_SERVER['HTTP_USER_AGENT']), 'ipad');
                                            if ($ipad === false) {
                                                showXHTML_input('button', '', $MSG['export'][$sysSession->lang],           '', 'class="cssBtn" onclick="displayDialog(\'exportTable\');"');
                                            }
				        showXHTML_td_E();
					}
			    showXHTML_tr_E();

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

                if ($topDir != 'learn')
                {
				showXHTML_tr_B('class="cssTrEvn"');
				    showXHTML_td_B('colspan="5"');
				        echo $MSG['result description'][$sysSession->lang];
				        showXHTML_input('button', '', $MSG['btnDetailResult'][$sysSession->lang],  '', 'class="cssBtn" onclick="ShowDetailResult();"');
				    showXHTML_td_E();
				showXHTML_tr_E();
				}

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
			// showXHTML_script('inline', 'document.getElementById("toolbar2").innerHTML=document.getElementById("toolbar1").innerHTML;');
		showXHTML_tabFrame_E();
		echo "</center>\n";

// 匯出選項
		$ary = array(array($MSG['export'][$sysSession->lang]));
		showXHTML_tabFrame_B($ary, 1, 'exportForm', 'exportTable', 'action="stat_output.php" method="POST" style="display: inline" target="empty"', true, false);
		  showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" class="cssTable"');
		    showXHTML_tr_B('class="cssTrEvn"');
		      showXHTML_td('', $MSG['choose the export format'][$sysSession->lang]);
		      showXHTML_td_B();
		        showXHTML_input('checkboxes', 'export_kinds[]', array('csv' => 'Excel (.csv)',
		        													  'htm' => 'HTML table (.htm)',
		        													  'xml' => 'XML (.xml)',
		                                                              'mht' => 'MHTML Document (.mht)'), array('csv'), 'onclick="checkExport();"', '<br>');
		      showXHTML_td_E();
		    showXHTML_tr_E();
		    showXHTML_tr_B('class="cssTrOdd"');
		      showXHTML_td('', $MSG['download_filename'][$sysSession->lang]);
		      showXHTML_td_B();
		      	$dl_name = QTI_which == 'exam' ? "{$course_id}_exam_state.zip" : "{$course_id}_ques_state.zip";
		        showXHTML_input('text', 'download_name', $dl_name, '', 'maxlength="60" size="40" class="box02"');
		      showXHTML_td_E();
		    showXHTML_tr_E();
		    showXHTML_tr_B('class="cssTrEvn"');
		      showXHTML_td_B('colspan="2" align="right"');
		        showXHTML_input('hidden', 'op', 'export');
			    showXHTML_input('hidden', 'content');
				showXHTML_input('hidden', 'lists', $_POST['lists']);
			    showXHTML_input('hidden', 'title', $title[$sysSession->lang]);
		        showXHTML_input('button', '', $MSG['export'][$sysSession->lang], '', 'class="cssBtn" onclick="exportDone();"');
		        showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="document.getElementById(\'exportTable\').style.display=\'none\';"');
		      showXHTML_td_E();
		    showXHTML_tr_E();
		  showXHTML_table_E();
		showXHTML_tabFrame_E();


	  showXHTML_form_B('method="POST" action="exam_statistics_result_detail.php"', 'DetailForm');
	    showXHTML_input('hidden', 'ticket', $_POST['ticket']);
	    showXHTML_input('hidden', 'referer', $_POST['referer']);
	    showXHTML_input('hidden', 'lists', $_POST['lists']);
	  showXHTML_form_E();

	  showXHTML_form_B('method="POST" action="stat_output.php" target="empty"', 'OutputForm');
	    showXHTML_input('hidden', 'op');
	    showXHTML_input('hidden', 'email', $sysSession->email);
	    showXHTML_input('hidden', 'content');
	    showXHTML_input('hidden', 'kinds');
	    showXHTML_input('hidden', 'title', $title[$sysSession->lang]);
	    showXHTML_input('hidden', 'lists', $_POST['lists']);
	  showXHTML_form_E();

	showXHTML_body_E();

?>


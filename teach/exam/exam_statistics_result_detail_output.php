<?php
	/**
	 * ※ 三合一統計
	 *
	 * @since   2004/09/22
	 * @author  Wiseguy Liang
	 * @version $Id: exam_statistics_result_detail.php,v 1.14 2010-12-08 07:12:32 fins Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	 $QTI_which='questionnaire';
	set_time_limit(0);
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/' . $QTI_which . '_teach.php');
	require_once(sysDocumentRoot . '/teach/exam/QTI_transformer.php');
	require_once(sysDocumentRoot . '/lib/attach_link.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/teach/exam/exam_stat_class.php');
	require_once(sysDocumentRoot . '/lib/sync_lib.php');
$_POST['lists']='100002890';
#=========== function ===============
	 /**
	 * 取某節點裡的最底層文字
	 * param element $element 節點
	 * return string 節點文字
	 */
	function getNodeContent($element){
		if (!is_object($element)) return '';//判斷$element是否為物件
		$node = $element;
		while($node->has_child_nodes()){
			$node = $node->first_child();
		}
		return $node->node_value();
	}

	function getUserAttachFiles($user)
	{
		global $QTI_which ,$sysSession, $save_dir;
		static $topDir;

		$rtnArr = array();

		if (!isset($topDir))
		{
			if (!defined('QTI_env'))
				list($foo, $topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
			else
				$topDir = QTI_env;

			if ($topDir == 'academic')
			{
				$save_dir = sprintf(sysDocumentRoot . '/base/%05d/%s/A/%09u/',
				  					 $sysSession->school_id,
				  					 $QTI_which,
				  					 $_POST['lists']);
			}
			else
			{
				$save_dir = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/A/%09u/',
				  					 $sysSession->school_id,
				  					 $sysSession->course_id,
				  					 $QTI_which,
				  					 $_POST['lists']);
			}
		}

		$save_path = $save_dir . $user . '/';
		$save_uri = substr($save_path, strlen(sysDocumentRoot));

		if ($d = @dir($save_path))
		{
			while (false !== ($entry = $d->read()))
			{
				if (is_file($save_path . $entry))
				{
					// [FLM] #379,380 [問卷管理]-[結果檢視]-[詳細資料]
					$rtnArr[] = genFileLink($save_uri, $entry,false);
				}
			}
			$d->close();
		}
		return $rtnArr;
	}

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
		global $QTI_which ,$dom,$root, $ctx, $sysConn;

		$ids = array();
		$nodes = $dom->get_elements_by_tagname('item');
		foreach($nodes as $item)
		{
			$ids[] = $item->get_attribute('id');
			$item->set_attribute('score', $item->get_attribute('score'));	// 強制設定一個 score 屬性
		}

		if ($ids){
			$idents = 'ident in ("' . implode('","', $ids) . '")';

			$real_items = $sysConn->GetAssoc('select ident,content from WM_qti_' . $QTI_which . '_item where ' . $idents);
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

#=========== Main ===============

		$sysSession->cur_func='1800300400';
	
	
	//ACL end

	
	
	$course_id =dbGetOne('WM_qti_' . $QTI_which . '_test', 'course_id', "exam_id={$_POST['lists']}");
	echo $course_id;
	
	$qti_item_types = array(1 => '/presentation//response_lid/render_choice',
							2 => '/presentation//response_str/render_fib',
							3 => '/presentation//response_num/render_fib',
							4 => '/presentation//response_grp//render_extension');
	$ADODB_FETCH_MODE = ADODB_FETCH_NUM;
	//取得問卷資料

	$RS = dbGetStMr('WM_qti_' . $QTI_which . '_test', 'title, begin_time, close_time, setting, content', "exam_id={$_POST['lists']}", ADODB_FETCH_NUM);
	

	if ($RS)
	{
		/*[FLM] NO414,NO422,NO433 衍生問題
		 * 原因:測驗試卷有亂數出題的功能，導致匯出是以第一份考卷的題目為依據輸出
		 * 處理方式:先取最初的所有題目出來
		 */
		 
		 
		list($title, $begin_time, $close_time, $setting, $content) = $RS->FetchRow();
		$anonymity = strpos($setting, 'anonymity') !== FALSE;
		$Title = (strpos($title, 'a:') === 0) ?
		               unserialize($title):
		               array('Big5'			=> $title,
		                     'GB2312'		=> $title,
		                     'en'			=> $title,
		                     'EUC-JP'		=> $title,
		                     'user_define'	=> $title
		               	    );
							
							
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
	}

	//取得學生的答案資料，並統計
	$forGuest = aclCheckWhetherForGuestQuest($course_id, $_POST['lists']);
	$RS = dbGetStMr('WM_qti_' . $QTI_which . '_result',
					($forGuest ? 'concat(examinee,time_id)' : 'examinee') . ',content',
					"exam_id={$_POST['lists']} and status != 'break' order by submit_time",
					ADODB_FETCH_NUM);
	if ($sysConn->ErrorNo()) {
	   wmSysLog($sysSession->cur_func, 4, 'auto', $_SERVER['PHP_SELF'], $sysConn->ErrorMsg());
	   die($sysConn->ErrorNo() . ': ' . $sysConn->ErrorMsg());
	}


	$tt                = $RS->RecordCount();
	$total             = 0;
	$failure           = 0;
	$result_array      = array();
	$user_result_array = array();
	$qtype_array       = array();		//題型陣列
	if ($RS && $tt)
	{
		$wiseStat = new QTI_exam_detail(true);
		$progree  = new ProgreeBar;
		$progree->showBar($MSG['msg_parsing'][$sysSession->lang]);
		//[FLM] NO414,NO422,NO433 衍生問題
		if($QTI_which == 'exam'){
			$wiseStat->parse('', preg_replace('/\sxmlns="[^"]*"/', '',setEncoding($dom->dump_mem())));
		}

		while(list($examinee, $content_xml) = $RS->FetchRow())
		{
		    $wiseStat->parse($examinee, preg_replace('/\sxmlns="[^"]*"/', '', UTF8_decode::u8decode($content_xml)));
		    call_user_func(array(&$progree,'step'), round(++$i/$tt,2)*100);
		}
		$wiseStat->endParse();
		$progree->close();
		$total              = $wiseStat->total;
		$failure            = $wiseStat->failure;
		$result_array       = $wiseStat->result_array;
		$user_result_array  = $wiseStat->user_result_array;
        $qtype_array        = $wiseStat->qtype_array;
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
	    alert('{$MSG['Incorrect email format.'][$sysSession->lang]}');
	    return;
	}

	form.op.value = 'mail';
	form.email.value = mail;
//	form.content.value = document.getElementById('tablePanel').innerHTML.replace(/<input [^>]*\btype=("button"|button)\b[^>]*>\s*/gi, '').replace(/<A href="\/wmhelp\.php[^>]*><IMG [^>]*><\/A>/i, '');
	form.submit();
}

function downloadAttach()
{
    var form = document.getElementById('OutputForm');
    form.op.value = 'download';
    form.content.value = '{$_POST['lists']}';
    form.submit();
}

function exportDone()
{
    var form = document.getElementById('exportForm');

    document.getElementById('exportTable').style.display='none';
//    form.content.value = document.getElementById('tablePanel').innerHTML.replace(/<input [^>]*\btype=("button"|button)\b[^>]*>\s*/gi, '').replace(/<A href="\/wmhelp\.php[^>]*><IMG [^>]*><\/A>/i, '');
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

	$progree = new ProgreeBar;
	while(ob_end_clean());

	
	showXHTML_head_B($MSG['title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('include', '/lib/dragLayer.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();

	$progree->showBar($MSG['msg_outputing'][$sysSession->lang]);

		$ary = array(array($MSG['btnDetailResult'][$sysSession->lang]));
		echo "<center id=\"tablePanel\">\n";
		showXHTML_tabFrame_B($ary, 1, '', 'ListTable');
			showXHTML_table_B('id ="mainTable" width="680" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				showXHTML_tr_B();
					showXHTML_td_B('colspan="2" class="cssTrHead"');
                        showXHTML_table_B('width="100%"');
                            showXHTML_tr_B();
                                showXHTML_td_B();
									// showXHTML_input('button', '', $MSG['print this page'][$sysSession->lang],      '', 'class="cssBtn" onclick="self.print();"');
						            showXHTML_input('button', '', $MSG['export'][$sysSession->lang],               '', 'class="cssBtn" onclick="displayDialog(\'exportTable\');"');
						            if ($QTI_which != 'exam' && is_dir($save_dir)) showXHTML_input('button', '', $MSG['download attachments'][$sysSession->lang], '', 'class="cssBtn" onclick="downloadAttach();"');
                                showXHTML_td_E();
                            showXHTML_tr_E();
                        showXHTML_table_E();
					showXHTML_td_E();
				showXHTML_tr_E();
				
				
			showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo "<br>\n";

		
		echo "</center>\n";

// 匯出選項
		$ary = array(array($MSG['export'][$sysSession->lang]));
		showXHTML_tabFrame_B($ary, 1, 'exportForm', 'exportTable', 'action="stat_output2.php" method="POST" style="display: inline" target="empty"', true, false);
		  showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" class="cssTable"');
		    showXHTML_tr_B('class="cssTrEvn"');
		      showXHTML_td('', $MSG['choose the export format'][$sysSession->lang]);
		      showXHTML_td_B();
			  	echo '關鍵字:'; showXHTML_input('text', 'keyword','ggg');
			  	echo 'limit 1:'; showXHTML_input('text', 'l1','1');
			  	echo 'limit 2:'; showXHTML_input('text', 'l2','2');
echo "<br>";
		        showXHTML_input('checkboxes', 'export_kinds[]', array('csv' => 'Excel (.csv)',
		        													  'htm' => 'HTML table (.htm)',
		        													  'xml' => 'XML (.xml)',
		                                                              'mht' => 'MHTML Document (.mht)'), array('csv'), 'onclick="checkExport();"', '<br>');
		      showXHTML_td_E();
		    showXHTML_tr_E();
		    showXHTML_tr_B('class="cssTrOdd"');
		      showXHTML_td('', $MSG['download_filename'][$sysSession->lang]);
		      showXHTML_td_B();

				$dl_name = $QTI_which == 'exam' ? "{$course_id}_exam_state.zip" : "{$course_id}_ques_state.zip";
		        showXHTML_input('text', 'download_name', $dl_name, '', 'maxlength="60" size="40" class="box02"');
		      showXHTML_td_E();
		    showXHTML_tr_E();
		    showXHTML_tr_B('class="cssTrEvn"');
		      showXHTML_td_B('colspan="2" align="right"');
		        showXHTML_input('hidden', 'op', 'export');
			    showXHTML_input('hidden', 'content');
			    showXHTML_input('hidden', 'title', $Title[$sysSession->lang]);
			    showXHTML_input('hidden', 'detail', 2);
				showXHTML_input('hidden', 'lists', $_POST['lists']);
		        showXHTML_input('button', '', $MSG['export'][$sysSession->lang], '', 'class="cssBtn" onclick="exportDone();"');
		        showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="document.getElementById(\'exportTable\').style.display=\'none\';"');
		      showXHTML_td_E();
		    showXHTML_tr_E();
		  showXHTML_table_E();
		showXHTML_tabFrame_E();


	  showXHTML_form_B('method="POST" action="exam_statistics_result.php"', 'ResultForm');
	    showXHTML_input('hidden', 'ticket', $_POST['ticket']);
	    showXHTML_input('hidden', 'referer', $_POST['referer']);
	    showXHTML_input('hidden', 'lists', $_POST['lists']);
	  showXHTML_form_E();

	  showXHTML_form_B('method="POST" action="stat_output.php" target="empty"', 'OutputForm');
	    showXHTML_input('hidden', 'op');
	    showXHTML_input('hidden', 'email', 'peterli@sun.net.tw');
	    showXHTML_input('hidden', 'content');
	    showXHTML_input('hidden', 'kinds');
	    showXHTML_input('hidden', 'title', $Title[$sysSession->lang]);
	    showXHTML_input('hidden', 'detail', 2);
		showXHTML_input('hidden', 'lists', $_POST['lists']);
	  showXHTML_form_E();

	showXHTML_body_E();

    $progree->close();
?>

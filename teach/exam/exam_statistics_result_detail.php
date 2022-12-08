<?php
	/**
	 * ※ 三合一統計
	 *
	 * @since   2004/09/22
	 * @author  Wiseguy Liang
	 * @version $Id: exam_statistics_result_detail.php,v 1.14 2010-12-08 07:12:32 fins Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	set_time_limit(0);
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/' . QTI_which . '_teach.php');
	require_once(sysDocumentRoot . '/teach/exam/QTI_transformer.php');
	require_once(sysDocumentRoot . '/lib/attach_link.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/teach/exam/exam_stat_class.php');
	require_once(sysDocumentRoot . '/lib/sync_lib.php');

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
		global $sysSession, $save_dir;
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
				  					 QTI_which,
				  					 $_POST['lists']);
			}
			else
			{
				$save_dir = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/A/%09u/',
				  					 $sysSession->school_id,
				  					 $sysSession->course_id,
				  					 QTI_which,
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

#=========== Main ===============
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

	if (!defined('QTI_env'))
		list($foo, $topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
	else
		$topDir = QTI_env;
        
        if ($topDir == 'academic') {
            $save_dir = sprintf(sysDocumentRoot . '/base/%05d/%s/A/%09u/', $sysSession->school_id, QTI_which, $_POST['lists']);
        } else {
            $save_dir = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/A/%09u/', $sysSession->school_id, $sysSession->course_id, QTI_which, $_POST['lists']);
        }

	$course_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id; //10000000;

	if (!isset($_POST['ticket'])) {	// 檢查 ticket 是否存在
	   wmSysLog($sysSession->cur_func, 1, 'auto', $_SERVER['PHP_SELF'], 'Access Denied!');
	   die('Access denied.');
	}
	$ticket = md5(sysTicketSeed . $course_id . $_POST['referer']);		// 產生 ticket
	if ($ticket != $_POST['ticket']) {	// 檢查 ticket
	   wmSysLog($sysSession->cur_func, 2, 'auto', $_SERVER['PHP_SELF'], 'Fake ticket!');
	   die('Fake ticket.');
	}
	if (!eregi('^[0-9A-Z_]+$', $_POST['lists'])) {	// 檢查 lists
	   wmSysLog($sysSession->cur_func, 3, 'auto', $_SERVER['PHP_SELF'], 'Fake lists!');
	   die('Fake lists.');
	}

	$ticket = md5(sysTicketSeed . $course_id . $_POST['lists']);

	$qti_item_types = array(1 => '/presentation//response_lid/render_choice',
							2 => '/presentation//response_str/render_fib',
							3 => '/presentation//response_num/render_fib',
							4 => '/presentation//response_grp//render_extension');
	$ADODB_FETCH_MODE = ADODB_FETCH_NUM;

	//取得問卷資料
	$RS = dbGetStMr('WM_qti_' . QTI_which . '_test', 'title, begin_time, close_time, setting, content', "exam_id={$_POST['lists']}", ADODB_FETCH_NUM);
	if ($RS)
	{
		/*[FLM] NO414,NO422,NO433 衍生問題
		 * 原因:測驗試卷有亂數出題的功能，導致匯出是以第一份考卷的題目為依據輸出
		 * 處理方式:先取最初的所有題目出來
		 */
		list($title, $begin_time, $close_time, $setting, $content) = $RS->FetchRow();
		$anonymity = strpos($setting, 'anonymity') !== FALSE;
		$Title = (strpos($title, 'a:') === 0) ?
		               getCaption($title):
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
	$RS = dbGetStMr('WM_qti_' . QTI_which . '_result',
					($forGuest ? 'concat(examinee,time_id)' : 'examinee') . ',content,examinee as user,time_id',
					"exam_id={$_POST['lists']} and status != 'break' order by submit_time DESC",
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
		if(QTI_which == 'exam'){
			$wiseStat->parse('', preg_replace('/\sxmlns="[^"]*"/', '',setEncoding($dom->dump_mem())));
		}

		while(list($examinee, $content_xml, $user, $time_id) = $RS->FetchRow())
		{
		    if (empty($content_xml))
			{
		       $xml_path = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/C/%09u/%s/',
				  					 $sysSession->school_id,
				  					 $sysSession->course_id,
				  					 QTI_which,
				  					 $_POST['lists'],
				  					 $user);
		       $file = 	$time_id.'.xml';	  	
		
		       $full_path = $xml_path.$file;
		       if (is_file($full_path)) {
		           $content_xml = file_get_contents($full_path);
		       }
			}
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
									if (strcmp($topDir,'learn')==0)
									{
									    showXHTML_input('button', '', $MSG['btn_back_questionnaire_list'][$sysSession->lang], '', 'onclick="window.close();" class="cssBtn"');
									}else{
									    showXHTML_input('button', '', $MSG['btn_back_questionnaire_list'][$sysSession->lang], '', 'onclick="location.replace(\'' . (QTI_which == 'questionnaire' ? 'exam_statistics.php' : 'exam_correct_list.php') . '\');" class="cssBtn"');
									}
				                    showXHTML_input('button', '', $MSG['btn_back_statistics_result'][$sysSession->lang], '', 'onclick="document.ResultForm.submit();" class="cssBtn"');
                                showXHTML_td_E();
                                showXHTML_td_B('align="right"');
						            showXHTML_input('button', '', $MSG['email this page'][$sysSession->lang],      '', 'class="cssBtn" onclick="mailMe();"');
						            showXHTML_input('button', '', $MSG['print this page'][$sysSession->lang],      '', 'class="cssBtn" onclick="self.print();"');
                                                            
                                                            // ipad基本上不支援下載zip檔案
                                                            // 需搭配使用 iDownload Pro app 貼上url，透過app下載
                                                            $ipad = strstr(strtolower($_SERVER['HTTP_USER_AGENT']), 'ipad');
                                                            if ($ipad === false) {
                                                                showXHTML_input('button', '', $MSG['export'][$sysSession->lang],               '', 'class="cssBtn" onclick="displayDialog(\'exportTable\');"');
                                                            }
						            if (QTI_which != 'exam' && is_dir($save_dir)) showXHTML_input('button', '', $MSG['download attachments'][$sysSession->lang], '', 'class="cssBtn" onclick="downloadAttach();"');
                                showXHTML_td_E();
                            showXHTML_tr_E();
                        showXHTML_table_E();
					showXHTML_td_E();
				showXHTML_tr_E();
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('', $MSG['exam_name'][$sysSession->lang]);
					showXHTML_td('', $Title[$sysSession->lang]);
				showXHTML_tr_E();
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('', $MSG['exam_duration'][$sysSession->lang]);
					showXHTML_td('', $MSG['from'][$sysSession->lang] . ' ' . (strpos($begin_time, '0000') === 0 ? $MSG['now'][$sysSession->lang] : $begin_time) . ' ' . $MSG['to'][$sysSession->lang] . ' ' . (strpos($close_time, '9999') === 0 ? $MSG['forever'][$sysSession->lang]: $close_time));
				showXHTML_tr_E();
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('', $MSG['total_quests'][$sysSession->lang]);
					showXHTML_td('', $tt+$failure);
				showXHTML_tr_E();
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('', $MSG['valid_quests'][$sysSession->lang]);
					showXHTML_td('', $tt);
				showXHTML_tr_E();
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('', $MSG['invalid_quests'][$sysSession->lang]);
					showXHTML_td('', $failure);
				showXHTML_tr_E();
				if (QTI_which != 'exam')
				{
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td('', $MSG['anonymous or not'][$sysSession->lang]);
						showXHTML_td('', $anonymity ? $MSG['anonymous'][$sysSession->lang] : $MSG['named'][$sysSession->lang]);
					showXHTML_tr_E();
				}
			showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo "<br>\n";

		showXHTML_table_B('id ="mainTable" width="680" border="0" cellspacing="1" cellpadding="3" class="cssTable" align="center"');

		//題目列
			showXHTML_tr_B('class="cssTrHead"');
			showXHTML_td('colspan="2" nowrap', $MSG['serial_no'][$sysSession->lang]);
			$i = 0;
			foreach($result_array as $id => $item) {
				switch($qtype_array[$id]) {
					case 1:	 //是非、選擇
					case 4:	 //配合
						$colspan = 0; // 計算筆數
						if ($qtype_array[$id] == 4) {
							$colspan = count($item) - 1;
						} else {
							foreach ($item as $k => $v) {
								if ($k == 'title') continue;
								$colspan += count($v);
							}
						}
						// showXHTML_td(sprintf('colspan="%d" nowrap', count($item) - 1), ($i+1) .'.'. $item['title']);
						showXHTML_td(sprintf('colspan="%d" nowrap', $colspan), ($i+1) .'.'. $item['title']);
						break;
					default:
						showXHTML_td(' nowrap', ($i+1) .'.'. $item['title']);
						break;
				}
				$i++;
			}
			if (QTI_which != 'exam') showXHTML_td(' nowrap', $MSG['attachments'][$sysSession->lang]);
			showXHTML_tr_E();

			//選項列
			showXHTML_tr_B('class="cssTrHead"');
			showXHTML_td(' nowrap', $MSG['candidate_item'][$sysSession->lang]);
			showXHTML_td(' nowrap', $MSG['round'][$sysSession->lang]);
			foreach($result_array as $id => $item) {
				foreach($item as $k => $node) {
					if ($k == 'title') continue;
					if (($qtype_array[$id] == 4) && (is_numeric($k))) continue;
					if (in_array($qtype_array[$id], array(2, 3, 4))) {
						showXHTML_td(' nowrap align="center"', $node['caption']);
					} else {
						foreach ($node as $n => $v) {
							// FUJITSU CUSTOM
							if ($n == 'T' || $n == 'F')
							{
								if ($v['caption'] == 'Agree')
										$v['caption'] = $MSG['msg_agree'][$sysSession->lang];
								else if ($v['caption'] == 'Disagree')
										$v['caption'] = $MSG['msg_disagree'][$sysSession->lang];
							}
							$v['caption'] = preg_replace('/&lt;br[\s\/]*&gt;/i', '<br>', $v['caption']);
							showXHTML_td(' nowrap align="center"', $v['caption']);
						}
					}
					if ($qtype_array[$id] == 2 || $qtype_array[$id] == 3) break;
				}
			}
			if (QTI_which != 'exam') showXHTML_td(' nowrap', '&nbsp;');
			showXHTML_tr_E();
		//===============

			//去除索引建立查詢帳號 array (採用奇怪的索引'_@sn@'是為了避免與帳號相同)
			foreach( array_keys($user_result_array) as $key ){
				//[FLM] NO414,NO422,NO433 衍生問題,移除空試卷
				if(QTI_which == 'exam'){
					unset($user_result_array['_@sn@1']);
				}
				$spilt = preg_split("/_@sn@/", $key);
				if(count($spilt)>1){
					$user_result_array[$key]['username'] = $spilt[0];
					$user_result_array[$key]['exam_count'] = $spilt[1];
				}
				//記錄帳號，等等要查詢
				$usernames[$spilt[0]] = true;
			}

			// 將學生真實姓名先一次取出，用查表法
			if (count($user_result_array))
			    $realnames = dbGetAssoc('WM_user_account',
										'username,first_name,last_name',
										'username in ("' . implode('","', array_keys($usernames)) . '")',
										ADODB_FETCH_NUM);

			//學生答案
			$checker = false;
			$total_amount = count($user_result_array);
			$curr_step = 0;
			foreach($user_result_array as $user => $ansArr)
			{
			    $curr_step++;
				showXHTML_tr_B(($checker ^= true) ? 'class="cssTrEvn"' : 'class="cssTrOdd"');

				$username = $user_result_array[$user]['username'];

				showXHTML_td(' nowrap', $anonymity ? $MSG['anonymous'][$sysSession->lang] : ($forGuest ? $username : "{$username}(".checkRealname($realnames[$username][0],$realnames[$username][1]).")"));
				showXHTML_td(' nowrap', $user_result_array[$user]['exam_count']);
				foreach ($result_array as $id => $item) {
					$col = true;
					foreach($item as $k => $node) {
						if ($k == 'title') continue;
						//if (($qtype_array[$id] == 4) && (!is_numeric($k))) continue;
						if (!isset($ansArr[$id]))	{ //使用者未填
							//[FLM] NO414,NO422,NO433 衍生問題
							if($qtype_array[$id] != 4){
								for ($i = 0, $c = count($node); $i < $c; $i++) {
									showXHTML_td('nowrap align="center"', '&nbsp;');
								}
								continue;
							}else{
								for ($i = 0, $c = 1; $i < $c; $i++) {
									showXHTML_td('nowrap align="center"', '&nbsp;');
								}
								continue;
							}
						}
						switch($qtype_array[$id]) {
							case 1 :		// 是非,單選,多選
								$MultiAns = explode(',',$ansArr[$id][$k]);
								$col ^= true;
								foreach ($node as $n => $v) {
									$bg = $col ? ' style="background-color: #FFFFCC;"' : '';
									if (in_array($n, $MultiAns))
										showXHTML_td(' nowrap align="center"' . $bg, 'O');
									else
										showXHTML_td(' nowrap align="center"' . $bg, '&nbsp;');
								}
								break;
							case 4 :		// 配合
								$MultiAns = explode(',',$ansArr[$id]);
									showXHTML_td(' nowrap align="center"', $MultiAns[ord($k) - 65]);
								break;
							default :		// 填充,問答
								if (QTI_which == 'questionnaire') {
									showXHTML_td(' nowrap ', strip_tags(implode(',', $ansArr[$id]), '<br>'));
								}else{
									showXHTML_td(' nowrap ', (html_entity_decode(implode(',', $ansArr[$id]))));
								}
								break;
						}
						if ($qtype_array[$id] == 2 || $qtype_array[$id] == 3) break;
					}
				}
				if (QTI_which != 'exam') showXHTML_td(' nowrap', implode('&nbsp;',getUserAttachFiles($username)));
				showXHTML_tr_E();

				$res = round($curr_step/$total_amount, 2) * 100;
				call_user_func(array(&$progree, 'step'), $res);
			}

			//統計
			showXHTML_tr_B(($checker ^= true) ? 'class="cssTrEvn"' : 'class="cssTrOdd"');
			showXHTML_td('colspan="2" nowrap', $MSG['item_sum'][$sysSession->lang]);
			foreach($result_array as $id => $item) {
				foreach($item as $k => $node) {
					if ($k == 'title') continue;
					// if (($qtype_array[$id] == 4) && (is_numeric($k))) continue;

					if($qtype_array[$id] == 1) {
						foreach ($node as $n => $v) {
							showXHTML_td(' nowrap align="center"', $v['count']);
						}
					} elseif($qtype_array[$id] == 4)
					{
					    showXHTML_td_B('style="padding: 0"');
					    showXHTML_table_B('width="100%" style="display: inline; border-collapse: collapse"');
					        showXHTML_tr_B('class="font01"');
					            if (is_array($node['count']))
					            {
					            	ksort($node['count']);
					            	foreach($node['count'] as $c)
					                	showXHTML_td('style="border-left: 1px solid silver;" nowrap align="center"', $c);
								}
								else
								    showXHTML_td(' nowrap align="center"', '&nbsp;');
					        showXHTML_tr_E();
					    showXHTML_table_E();
					    showXHTML_td_E();
					}
					else
						showXHTML_td(' nowrap align="center"', '&nbsp;');

		            if ($qtype_array[$id] == 2 || $qtype_array[$id] == 3) break;
				}
			}
			if (QTI_which != 'exam') showXHTML_td(' nowrap', '&nbsp;');
			showXHTML_tr_E();
		showXHTML_table_E();
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
			    showXHTML_input('hidden', 'title', $Title[$sysSession->lang]);
			    showXHTML_input('hidden', 'detail', 1);
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
	    showXHTML_input('hidden', 'email', $sysSession->email);
	    showXHTML_input('hidden', 'content');
	    showXHTML_input('hidden', 'kinds');
	    showXHTML_input('hidden', 'title', $Title[$sysSession->lang]);
	    showXHTML_input('hidden', 'detail', 1);
		showXHTML_input('hidden', 'lists', $_POST['lists']);
	  showXHTML_form_E();

	showXHTML_body_E();

    $progree->close();
?>

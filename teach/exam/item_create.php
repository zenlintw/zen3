<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                         *
	 *		Creation  : 2002/09/26                                                            *
	 *		work for  : Create Item                                                           *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *                                                                                                *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/' . QTI_which . '_teach.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/quota.php');
	require_once(sysDocumentRoot . '/lib/xajax/xajax.inc.php');
	require_once(sysDocumentRoot . '/lib/editor.php');

	$xajax_choice_temp = new xajax('item_choice_template.php');
	$xajax_choice_temp->registerFunction('save_template');
	$xajax_choice_temp->registerFunction('pick_template');
	$xajax_choice_temp->registerFunction('del_template');
	$xajax_choice_temp->registerFunction('get_template_select');

	//ACL begin
	if (QTI_which == 'exam') {
		$sysSession->cur_func = '1600100200';
	}
	else if (QTI_which == 'homework') {
		$sysSession->cur_func = '1700100200';
	}
	else if (QTI_which == 'questionnaire') {
		$sysSession->cur_func = '1800100200';
	}
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

	}
	//ACL end

	if (!defined('QTI_env'))
		list($foo, $topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
	else
		$topDir = QTI_env;

	$course_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id; //10000000;

	$ticket = md5($_POST['gets'] . sysTicketSeed . $course_id . $_COOKIE['idx']);
	if ($ticket != trim($_POST['ticket'])) {//ticket保護
	   wmSysLog($sysSession->cur_func, $course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'Illegal Access!');
	   die('Illegal Access !');
	}

	if (!empty($_POST['lists']))
	{
		if (!ereg('^[A-Z0-9_]+$', $_POST['lists'])) {	// 判斷 ident 序列格式
		   wmSysLog($sysSession->cur_func, $course_id , 0 , 2, 'auto', $_SERVER['PHP_SELF'], 'ID format error:'.$_POST['lists']);
		   die('ID format error !');
		}
		$RS = dbGetStSr('WM_qti_' . QTI_which . '_item', '*', "ident='{$_POST['lists']}'", ADODB_FETCH_ASSOC);
		if ($RS === false){
		    $errMsg = $sysConn->ErrorNo() . ' : ' . $sysConn->ErrorMsg();
		    wmSysLog($sysSession->cur_func, $course_id , 0 , 3, 'auto', $_SERVER['PHP_SELF'], $errMsg);
			die($errMsg);
		}
		$attach = ereg('^a:[0-9]+:{s:', $RS['attach']) ? unserialize($RS['attach']) : array();
		if ($topDir == 'academic')
			$save_path = sprintf(sysDocumentRoot . '/base/%05d/%s/Q/%s/',//設定存取目錄->/base/5位數學校id/三合一何者/Q/ident值
			  					 $sysSession->school_id,
			  					 QTI_which,
			  					 $RS['ident']);
		else
			$save_path = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/Q/%s/',//設定存取目錄->/base/5位數學校id/8位數課程id/三合一何者/Q/ident值
			  					 $sysSession->school_id,
			  					 $sysSession->course_id,
			  					 QTI_which,
			  					 $RS['ident']);

		$save_uri = substr($save_path, strlen(sysDocumentRoot));//創造一個XML DOM(XML文件程式設計的介面文件)物件
		if(!$dom = domxml_open_mem(preg_replace('/xmlns="[^"]+"/', '', $RS['content']))) {
			wmSysLog($sysSession->cur_func, $course_id , 0 , 4, 'auto', $_SERVER['PHP_SELF'], 'domxml open fail!');
			die("Error while parsing the document.\n" . $RS['content']);
		}
		$itemRoot = $dom->document_element();
		$ctx = xpath_new_context($dom);//Creates new xpath context
		$ctx->xpath_register_ns('wm','http://www.sun.net.tw/WisdomMaster');
		$init = 1;
	}
	else
	{
		$init = ($_SERVER['argv'][0] && ereg('^[1-7]$', $_SERVER['argv'][0])) ? intval($_SERVER['argv'][0]) : 1;
	}
	$ticket = md5($_POST['gets'] . $_POST['lists'] . sysTicketSeed . $course_id . $_COOKIE['idx']);


	$curr_mode = $ADODB_FETCH_MODE;
	$ADODB_FETCH_MODE = ADODB_FETCH_NUM;
	if ($topDir == 'academic')
		getQuota($sysSession->school_id, $quota_used, $quota_limit);
	else
		getQuota($sysSession->course_id, $quota_used, $quota_limit);
	settype($quota_used, 'int');
	settype($quota_limit, 'int');
	$ADODB_FETCH_MODE = $curr_mode;


	/**
	 * 判斷檔名是否為圖檔
	 * param string $fname 檔名字串
	 * return bool 是否為圖檔
	 */
	function is_pic($fname)
	{
		return eregi('\.(jpg|jpeg|jpe|gif|png|bmp)$', $fname);
	}

	/**
	 * 判斷檔名是否為影像檔
	 * param string $fname 檔名字串
	 * return bool 是否為影像檔
	 */
	function is_avi($fname)
	{
		return eregi('\.(wmv|asf|mpg|mpeg|avi|rm|ram|mov)$', $fname);
	}

	/**
	 * 判斷檔名是否為音訊檔
	 * param string $fname 檔名字串
	 * return bool 是否為音訊檔
	 */
	function is_snd($fname)
	{
		return eregi('\.(wma|mp3|wav|mid|ogg|ac3|ra)$', $fname);
	}

	function gen_link($fname, $caption)
	{
		global $MSG, $save_uri, $sysSession;
		if(is_pic($fname))
                        // 圖片最大到100％，避免ipad mini破版
			return sprintf('%s %s <img src="%s%s" align="absmiddle" alt="%s" style="max-width: 100%%">',
							$MSG['remove_origin'][$sysSession->lang],
							$fname,
							$save_uri,
							$caption,
							$fname
			              );
		elseif(is_avi($fname))
			return sprintf('%s %s <embed src="%s%s" align="absmiddle" type="video/*" mime-types="mime.types" %s autostart="false" title="%s">',
							$MSG['remove_origin'][$sysSession->lang],
							$fname,
							$save_uri,
							$caption,
							(eregi('\.(rm|ram)$', $fname) ? 'WIDTH=352 HEIGHT=276 NOJAVA=true CONTROLS="ImageWindow,ControlPanel"':''),
							$fname
			              );
		elseif(is_snd($fname)){
            /*#48163 [Chrome][教師/測驗管理/題庫維護/修改] 題目附檔若是mp3，會出現「沒有可以顯示內容的外掛」：修改寫法*/
            /*#483350 Chrome[教室/評量區/QTI/作業] 題目附檔若是wma，chrome無法播放。：修改寫法*/
			$agent = $_SERVER['HTTP_USER_AGENT'];
            if(strpos($agent, 'MSIE')){
                $browser = 'ie';
            }else if(strpos($agent, 'Firefox')){
                $browser = 'ff';
            }else if(strpos($agent, 'Chrome')){
                $browser = 'chr';
            }else if(strpos($agent, 'Safari')){
                $browser = 'sf';
            }else{
                $browser = 'op';
            }   
            $win = strpos($agent, 'Windows') ? true : false; 
            
			if (($browser === 'sf' && $win === false) || (($browser === 'chr' || $browser === 'ff' || ($browser === 'sf' && $win === true)) && strrchr($fname, '.') === '.mp3'))
			{		
                /*#48230 [IE][教室/評量區/測驗/進行測驗] 題目附檔若是mp3，會出現叉燒包：修改播放器程式碼寫法*/
                return sprintf('%s %s <audio src="%s%s" preload="auto" controls></audio>',
                                $MSG['remove_origin'][$sysSession->lang],
                                $fname,
                                $save_uri,
                                $caption
                              );

            } else {
                return sprintf('%s %s <object classid="clsid:6BF52A52-394A-11d3-B153-00C04F79FAA6" width="400" height="64" > <param name="invokeURLs" value="0" > <param name="autostart" value="0" /> <param name="url" value="%s%s"? id="abbc"/>  <embed src="%s%s" autostart="0" type="application/x-mplayer2" width="400" height="64"></embed>  </object>',
                                $MSG['remove_origin'][$sysSession->lang],
                                $fname,
                                $save_uri,
                                $caption,
                                $save_uri,
                                $caption
                              );
			}
		}elseif(strrchr($fname, '.') == '.swf')
/*			return sprintf('%s %s
<OBJECT classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0">
<PARAM NAME="movie" VALUE="%s%s">
<PARAM NAME="quality" VALUE="high">
<PARAM NAME="valign" VALUE="absmiddle">
<EMBED src="%s%s" quality="high" VALIGN="absmiddle" TYPE="application/x-shockwave-flash"
PLUGINSPAGE="http://www.macromedia.com/go/getflashplayer">
</EMBED>
</OBJECT>', $MSG['remove_origin'][$sysSession->lang], $fname, $save_uri, $caption, $save_uri, $caption);
*/
			return sprintf('%s %s<object type="application/x-shockwave-flash" data="%s%s"><param name="movie" value="%s%s" /></object>',
							$MSG['remove_origin'][$sysSession->lang], $fname, $save_uri, $caption, $save_uri, $caption);
		else
			return sprintf('%s <a href="%s%s" target=_blank" class="cssAnchor">%s</a>',
							$MSG['remove_origin'][$sysSession->lang], $save_uri, $caption, $fname);
	}


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
     /**
     *取出節點中的resprocessing標籤中的文字
     *return array 節點文字陣列
     */
	function getFillContent($node){
		global $ctx;
		$id = $node->get_attribute('ident');
		$ret = $ctx->xpath_eval("/item/resprocessing/respcondition/conditionvar/varequal[@respident='$id']");//Evaluates the XPath Location Path in the given string->秀出答案與配分
		if (is_array($ret->nodeset) && count($ret->nodeset))//確認$ret是否為陣列並計算其元素數目
			return '((' . $ret->nodeset[0]->get_content() . '))';
		else
			return '(())';
	}

	/**
	 * 秀出題目的【敘述】【附檔】
	 * param integer $idx 題型
	 * $words string 標題額外文字
	 * return void
	 */
	function ItemElementHeadPart($idx, $words=''){
		global $MSG, $sysSession, $ctx, $save_uri, $attach;//定義全域變數

		if ($ctx)
		{
			$ret = $ctx->xpath_eval('/item/presentation//mattext');//秀出敘述與附檔
			$nodes = is_array($ret->nodeset) ? $ret->nodeset : array(null);
			switch($idx)
			{
				case 4://題型為填充題的話
					$topic = '';
					foreach($nodes as $node)
					{
						$topic .= getNodeContent($node);//取節點(/item/presentation//mattext)裡的最底層文字
						$n = $node->parent_node();//到父節點
						$n = $n->next_sibling();//到旁節點
						if (is_object($n) && $n->node_name() == 'response_str') $topic .= getFillContent($n);//'response_str->文字填充
					}
					break;
				default:
					$topic = getNodeContent($nodes[0]);//取節點裡的最底層文字
					break;
			}
		}
//秀出表格
	    showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="960" style="border-collapse: collapse" class="box01"');
		  showXHTML_tr_B('class="font01 cssTrHead"');
		  	showXHTML_td('colspan="4"', sprintf($MSG['file_size_limit1'][$sysSession->lang] . '<span style="color: red; font-weight: bold">%s</span>' . $MSG['file_size_limit2'][$sysSession->lang] . '<span style="color: red; font-weight: bold">%s</span>', ini_get('upload_max_filesize'), ini_get('post_max_size')));
		  showXHTML_tr_E();
	      showXHTML_tr_B('class="bg04 font01"');
	        showXHTML_td('width="130" align="right"', $MSG['item_desc1'][$sysSession->lang] . $words);
	        showXHTML_td_B('width="630" align="left" colspan="3"');
	        	$oEditor = new wmEditor;
	        	$oEditor->addContType('isHTML', 1);
	        	$oEditor->setValue($topic);
	        	$oEditor->setConfig('ToolbarStartExpanded', false);
				//Chrome
	        	$oEditor->generate('topic_' . $idx, '700', '170');
	        	showXHTML_input('hidden', 'topic');
	        showXHTML_td_E();
	      showXHTML_tr_E();
	      showXHTML_tr_B('class="bg03 font01"');
	        showXHTML_td('align="right"', $MSG['item_file1'][$sysSession->lang]);
	        showXHTML_td_B('colspan="3"');
	          showXHTML_input('file', 'topic_files[]', '', '', 'size="36" class="box02"' . ($GLOBALS['quota_used'] < $GLOBALS['quota_limit'] ? '' : ' disabled'));
	          if (is_array($attach['topic_files']))
	          	foreach($attach['topic_files'] as $k => $v)
	          		if ($v)
	          		{
	          			echo "\n<br>";
	              		showXHTML_input('checkbox', 'topic_files_rm[]', $k);
              			echo gen_link($k, $v);
              		}
	        showXHTML_td_E();
	      showXHTML_tr_E();
	      showXHTML_tr_B('class="bg04 font01"');
	        showXHTML_td('align="right"', '');
	        showXHTML_td_B('colspan="3"');
	          showXHTML_input('button', '', $MSG['incr_file'][$sysSession->lang], '', 'class="cssBtn" ' . ($GLOBALS['quota_used'] < $GLOBALS['quota_limit'] ? 'onclick="add_choice(this);"':'disabled'));
	          showXHTML_input('button', '', $MSG['decr_file'][$sysSession->lang], '', 'class="cssBtn" onclick="rm_choice(this,'.$idx.');"');
	        showXHTML_td_E();
	      showXHTML_tr_E();
	}

	/**
	 * 秀出題目的【提示】【答案】【參考】【分類】【難度】
	 * param integer $type 題型
	 * return void
	 */
	function ItemElementTailPart($type){
		global $MSG, $sysSession, $_POST, $ticket, $dom, $ctx, $save_uri, $attach;

		if ($dom)//回答後的反饋 itemfeedback element
		{
			$nodes = $dom->get_elements_by_tagname('hint');//提示反饋屬性
			$hintEnable = !in_array(strtolower($nodes[0]->get_attribute('enable')), array('','off','no','0','false'));
			$nodes = $dom->get_elements_by_tagname('hintmaterial');
			$hint = getNodeContent($nodes[0]);

			$nodes = $dom->get_elements_by_tagname('solutionmaterial');//參考文件tag
			$solution = getNodeContent($nodes[0]);

			$nodes = $ctx->xpath_eval('//wm:refurl');//自訂wm標籤
			$refurl = getNodeContent($nodes->nodeset[0]);

			foreach(array('version', 'volume', 'chapter', 'paragraph', 'section', 'hardlevel') as $elem){
				$nodes = $ctx->xpath_eval('//wm:' . $elem);//自訂wm標籤->'version版本', 'volume集數', 'chapter章節', 'paragraph段落', 'section大題', 'hardlevel難度'
				$$elem = intval(getNodeContent($nodes->nodeset[0]));//取"字串"的整數值
			}
		}

		if (QTI_which != 'questionnaire')//如果不是問卷-開始showXhtml_*_B/S(以下為答案 詳解 解答附檔 參考網址 難易度 欄位)
		{
/*	// 答錯提示未用，暫時隱藏
	      showXHTML_tr_B('class="bg03 font01"');
	        showXHTML_td('align="right"', $MSG['error_hint1'][$sysSession->lang]);
	        showXHTML_td_B('colspan="3"');
	          showXHTML_input('checkbox', 'hintEnable', '1', '', ($hintEnable ? 'checked': '')); echo $MSG['enable'][$sysSession->lang], '<br>';
	          showXHTML_input('textarea', 'hint', $hint, '', 'rows="3" cols="40" class="box02" maxlength="1000"');
	        showXHTML_td_E();
	      showXHTML_tr_E();
*/
	      showXHTML_tr_B('class="bg04 font01"');
	        showXHTML_td('align="right"', $MSG['solution1'][$sysSession->lang]);
	        showXHTML_td_B('colspan="3"');
	        	$oEditor = new wmEditor;
	        	$oEditor->addContType('isHTML', 1);
	        	$oEditor->setValue($solution);
	        	$oEditor->setConfig('ToolbarStartExpanded', false);
				//Chrome
	        	$oEditor->generate('ans_detail_' . $type, '700', '170');
	        	showXHTML_input('hidden', 'ans_detail');
	        showXHTML_td_E();
	      showXHTML_tr_E();
	      showXHTML_tr_B('class="bg03 font01"');
	        showXHTML_td('align="right"', $MSG['sol_file1'][$sysSession->lang]);
	        showXHTML_td_B('colspan="3"');
	          showXHTML_input('file', 'ans_files[]', '', '', 'size="36" class="box02"' . ($GLOBALS['quota_used'] < $GLOBALS['quota_limit'] ? '' : ' disabled'));
	          if (is_array($attach['ans_files']))
	          	foreach($attach['ans_files'] as $k => $v)
	          	  if ($v)
	          	  {
	          	  	echo "\n<br>";
	              	showXHTML_input('checkbox', 'ans_files_rm[]', $k);
	              	echo gen_link($k, $v);
	              }
	        showXHTML_td_E();
	      showXHTML_tr_E();
	      showXHTML_tr_B('class="bg04 font01"');
	        showXHTML_td('align="right"', '');
	        showXHTML_td_B('colspan="3"');
	          showXHTML_input('button' , '', $MSG['incr_file'][$sysSession->lang], '', 'class="cssBtn" ' . ($GLOBALS['quota_used'] < $GLOBALS['quota_limit'] ? 'onclick="add_choice(this);"': 'disabled'));
	          showXHTML_input('button' , '', $MSG['decr_file'][$sysSession->lang], '', 'class="cssBtn" onclick="rm_choice(this,'.$type.');"');
	        showXHTML_td_E();
	      showXHTML_tr_E();
	      showXHTML_tr_B('class="bg03 font01"');
	        showXHTML_td('align="right"', $MSG['ref_url1'][$sysSession->lang]);
	        showXHTML_td_B('colspan="3"');
	          showXHTML_input('text', 'ref_url', ($refurl ? $refurl : 'http://'), '', 'size="36" class="box02" maxlength="500"');
	          echo '<font color="red">' , $MSG['split_by_space'][$sysSession->lang] , '</font>';
	        showXHTML_td_E();
	      showXHTML_tr_E();
	    }
	      showXHTML_tr_B((QTI_which == 'questionnaire') ? 'class="bg03 font01"' : 'class="bg04 font01"');
	        showXHTML_td('align="right"', $MSG['class1'][$sysSession->lang]);
	        showXHTML_td_B('align="left" colspan="3"');
	          showXHTML_input('text', 'version'  , $version,   '', 'size="4" class="box02" maxlength="4"'); echo $MSG['version'][$sysSession->lang];
	          showXHTML_input('text', 'volume'   , $volume,    '', 'size="4" class="box02" maxlength="4"'); echo $MSG['volume'][$sysSession->lang];
	          showXHTML_input('text', 'chapter'  , $chapter,   '', 'size="4" class="box02" maxlength="4"'); echo $MSG['chapter'][$sysSession->lang];
	          showXHTML_input('text', 'paragraph', $paragraph, '', 'size="4" class="box02" maxlength="4"'); echo $MSG['paragraph'][$sysSession->lang];
	          showXHTML_input('text', 'section'  , $section,   '', 'size="4" class="box02" maxlength="4"'); echo $MSG['section'][$sysSession->lang];
	        showXHTML_td_E();
	      showXHTML_tr_E();
		if (QTI_which != 'questionnaire')
		{
	      showXHTML_tr_B('class="bg03 font01"');
	        showXHTML_td('align="right"', $MSG['hard_level_colon'][$sysSession->lang]);
	        showXHTML_td_B('align="left" colspan="3"');
	          showXHTML_input('select', 'level', array(	1 => $MSG['hard_level1'][$sysSession->lang] . '&nbsp;',
														2 => $MSG['hard_level2'][$sysSession->lang] . '&nbsp;',
														3 => $MSG['hard_level3'][$sysSession->lang] . '&nbsp;',
														4 => $MSG['hard_level4'][$sysSession->lang] . '&nbsp;',
														5 => $MSG['hard_level5'][$sysSession->lang] . '&nbsp;'), ($hardlevel ? $hardlevel : 3), 'size="1" class="box02"');
	        showXHTML_td_E();
	      showXHTML_tr_E();
		}
	      showXHTML_tr_B('class="bg04 font01"');
	        showXHTML_td_B('align="center" colspan="4"');
	          if (!$dom)
	          {
					showXHTML_input('checkbox', 'repeat', 'true', '', 'checked');
					echo $MSG['continue_add'][$sysSession->lang], '<br>';
			  }
	          showXHTML_input('submit', '', ($dom ? $MSG['sure_modify'][$sysSession->lang] : $MSG['sure_add'][$sysSession->lang]), '', 'class="cssBtn"');
//	          showXHTML_input('button', '', ($dom ? $MSG['sure_modify'][$sysSession->lang] : $MSG['sure_add'][$sysSession->lang]), '', 'class="cssBtn" onClick="chkForm(this);"');
	          showXHTML_input('hidden', 'type', $type);
	          if ($dom)
	          {
	          	showXHTML_input('hidden', 'origin', $_POST['gets']);
				showXHTML_input('hidden', 'ident', $_POST['lists']);
	          }
	          else
	          {
	          	showXHTML_input('hidden', 'gets', $_POST['gets']);
	          }
	          showXHTML_input('hidden', 'ticket', $ticket);
	          showXHTML_input('button', '', $MSG['return_menu'][$sysSession->lang], '', 'class="cssBtn" onclick="location.replace(\'item_maintain.php?' . $_POST['gets'] . '\');"');
	        showXHTML_td_E();
	      showXHTML_tr_E();
	    showXHTML_table_E();
	}


	// 開始 output HTML
	showXHTML_head_B($dom ? $MSG['item_modify'][$sysSession->lang] : $MSG['item_create'][$sysSession->lang]);
	  showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$topDir}/wm.css");
        showXHTML_script('include', '/lib/jquery/jquery.min.js');
        showXHTML_script('include', '/lib/jquery/jquery-ui-1.8.22.custom.min.js');

	  $which = QTI_which;
	  $msgQuota = str_replace('%TYPE%', $MSG[$topDir == 'academic' ? 'school' : 'course'][$sysSession->lang], $MSG['quota_exceed'][$sysSession->lang]);
	  $scr = <<< EOB

var msg1 = '{$MSG['must_least_one'][$sysSession->lang]}';
var msg2 = '{$MSG['topic_required'][$sysSession->lang]}';
var msg3 = '{$MSG['choice_required'][$sysSession->lang]}';
var msg4 = '{$msgQuota}';
var msg5 = '{$MSG['remove_success'][$sysSession->lang]}';
var msg6 = '{$MSG['save_complete'][$sysSession->lang]}';

var isIE = true;
var isMZ = (navigator.userAgent.toLowerCase().indexOf('firefox') > -1); // 瀏覽器是否為 Mozilla

if (typeof(HTMLElement) != "undefined" && !window.opera) {
    // IE10 不支援 __defineGetter__等，暫時先拿掉
    //	HTMLElement.prototype.__defineGetter__("outerHTML",function()
    //	{
    //		var a=this.attributes, str="<"+this.tagName, i=0;
    //		for(;i<a.length;i++)
    //			if(a[i].specified) str+=" "+a[i].name+'="'+a[i].value+'"';
    //		if(!this.canHaveChildren) return str+" />";
    //		return str+">"+this.innerHTML+"</"+this.tagName+">";
    //	});
    //
    //	HTMLElement.prototype.__defineSetter__("outerHTML",function(s)
    //	{
    //		var r = this.ownerDocument.createRange();
    //		r.setStartBefore(this);
    //		var df = r.createContextualFragment(s);
    //		this.parentNode.replaceChild(df, this);
    //		return s;
    //	});
    //
    //	HTMLElement.prototype.__defineGetter__("canHaveChildren",function()
    //	{
    //		return !/^(area|base|basefont|col|frame|hr|img|br|input|isindex|link|meta|param)$/.test(this.tagName.toLowerCase());
    //	});
}

function mzInputText() {
    this.setAttribute('value', this.value);
}

function mzInputOption() {
    if (this.checked) this.setAttribute('checked', 'true');
}

function initInputInnerHTMLFix() {
    var inputs = document.getElementsByTagName('input');
    for (var i = 0; i < inputs.length; i++) {
        switch (inputs[i].type.toLowerCase()) {
            case 'text':
                inputs[i].onblur = mzInputText;
                break;
            case 'checkbox':
            case 'radio':
                inputs[i].onblur = mzInputOption;
                break;
        }
    }
}

/**
 * windows.onload() 事件處理
 */
window.onload = function() {
    rm_whitespace(document.documentElement);
    $('input, select').css('margin-right', '0.5em');
    $('input, select').css('margin-bottom', '0.3em');

    releaseInputSelect();

    isIE = (navigator.userAgent.search(/MSIE/) > -1);
    if ({$quota_used} >= {$quota_limit}) alert(msg4);
    if (document.getElementsByTagName('form').length > 6) switchTab({$init} - 1);

    var elems = document.getElementsByTagName('input');
    for (var i = 0; i < elems.length; i++)
        if (elems[i].type == 'text')
            elems[i].onchange = escape_control_chars;

    var elems = document.getElementsByTagName('textarea');
    for (var i = 0; i < elems.length; i++)
        elems[i].onchange = escape_control_chars;

    
    initInputInnerHTMLFix();
    setTimeout(function() {
        // 讓 Firefox 可以正確顯示出 FCKEditor
        var nodes = document.getElementsByTagName('iframe');
        for (var i = 0, c = nodes.length; i < c; i++) {
            if (nodes[i].parentNode.tagName !== 'TD') {
                continue;
            }
            nodes[i].parentNode.style.height = '100px';
            nodes[i].style.height = '100px';
        }
    }, 1000);
    
};

function escape_control_chars() {
    this.value = this.value.replace(/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]/g, ' ').replace(/^\s+|\s+$/g, '');
}

/**
 * 刪除 Mozilla 讀入 XML 時產生的空節點
 */
function rm_whitespace(node) {
    switch (node.nodeType) {
        case 1:
            for (var i = node.childNodes.length - 1; i >= 0; i--) rm_whitespace(node.childNodes[i]);
            break;
        case 3:
            if (node.nodeValue.search(/^\s+$/) === 0) node.parentNode.removeChild(node);
            break;
    }
}

/*
 *
 */
function releaseInputSelect() {
    var nodes = document.getElementsByTagName('INPUT');
    for (var i = 0; i < nodes.length; i++) {
        if (nodes[i].type == 'text') {
            nodes[i].onselectstart = cancelbubble;
        }
        nodes[i].onmousedown = cancelbubble;
    }
    nodes = document.getElementsByTagName('TEXTAREA');
    for (var i = 0; i < nodes.length; i++) {
        nodes[i].onselectstart = cancelbubble;
        nodes[i].onmousedown = cancelbubble;
    }
}

/*
 *
 */
function cancelbubble(e) {
    if (isIE)
        event.cancelBubble = true;
    else
        e.cancelBubble = true;
}

var cur_idx = -1;

function switchTab(n) {
    for (var i = 0; i < 7; i++) {
        if (i == n) {
            if (cur_idx != n) {
                document.getElementById('tabContent' + i).style.display = 'inline';
                var obj1 = document.getElementById('TitleID' + (i + 1));
                tabsMouseEvent(obj1, 2);
            }
        } else {
            document.getElementById('tabContent' + i).style.display = 'none';
        }
    }
    cur_idx = n;
    if (!isIE) {
        var node = document.getElementById('tabContent' + cur_idx);
        setTimeout(function() {
            // 讓 Firefox 可以正確顯示出 FCKEditor
            var h = 0,
                nodes = node.getElementsByTagName('iframe');
            for (var i = 0, c = nodes.length; i < c; i++) {
                if (typeof nodes[i].parentNode.clientHeight !== 'undefined') {
                    h = nodes[i].parentNode.clientHeight + 1;
                    if (h > 120) {
                        // #47741 Chrome [管理者/問卷管理/題庫維護/新增、修改] 題目空白時，按下「確定新增」，應該要出現「題目不可空白！」alert訊息：高度會變太小 
                        h = 170;
                    }
                } else {
                    h = 106;
                }
                nodes[i].parentNode.style.height = h + 'px';
                nodes[i].style.height = '100%';
            }
        }, 500);
    }
}

/**
 * 檢查必要欄位(case2:單選,case3:複選,case4:填充->檢查是否合((答案))規則,case6:配合)
 */
function submit_check(idx) {
    var formObj = document.getElementById('tabContent' + (idx - 1));

    formObj.topic.value = getEditorInstance("topic_" + idx).GetHTML();
    if (formObj.ans_detail)
        formObj.ans_detail.value = getEditorInstance("ans_detail_" + idx).GetHTML();
    // var formObj = obj.getElementsByTagName('form')[0];
    if (formObj.topic.value == '') {
        alert(msg2);
        return false;
    }
    switch (idx) {
        case 2:
        case 3:
            var inputs = formObj.getElementsByTagName('input');
            var choices = 0;
            var multi_choices = 0;
            for (var i = 0; i < inputs.length; i++) {
                if (inputs[i].type == 'text' && inputs[i].name.search(/^render_choices\[/) === 0 && !('{$RS['type']}')) {
                    if (inputs[i].value == '' && inputs[i + 1].value == '') {
                        if (choices == 0) {
                            alert(msg1);
                            return false;
                        } else
                            break;
                    } else {
                        choices++;
                    }
                } else if (inputs[i].name.search(/^answer/) === 0 && (inputs[i].type == 'checkbox' || inputs[i].type == 'radio')) {
                    if (inputs[i].checked) multi_choices++;
                }
            }
            if (multi_choices == 0 && '{$which}' != 'questionnaire') {
                alert(msg3);
                return false;
            }
            break;
        case 4:
            var r = '{$which}' == 'questionnaire' ? /\(\(.*\)\)/m : /\(\(.+\)\)/m;
            var tmp = formObj.topic.value.trim().replace(/ /g, "").replace(/&nbsp;/g, '');
            if (tmp.search(r) == -1) {
                alert('{$MSG['non-block'][$sysSession->lang]}');
                return false;
            }
            break;
        case 6:
            var inputs = formObj.getElementsByTagName('input');
            var choices1 = 0,
                choices2 = 0,
                answer = 0;
            for (var i = 0; i < inputs.length; i++) {
                if (inputs[i].type == 'text') {
                    if (inputs[i].name == 'answer[]') {
                        if (inputs[i].value != '') answer++;
                    } else if (inputs[i].name.search(/^render[12]_choices\[/) === 0) {
                        if (inputs[i].value != '' || inputs[i + 1].value != '') {
                            if (inputs[i].name.search(/^render1_choices\[/) === 0)
                                choices1++;
                            else
                                choices2++;
                        }
                    }
                }
            }
            if ((choices1 == 0 || choices2 == 0)) {
                alert(msg1);
                return false;
            }
            if (answer == 0 && '{$which}' != 'questionnaire') {
                alert(msg3);
                return false;
            }
            break;
        case 7:
            break;
    }
    return true;
}

/**
 * 增加附檔、選項
 */
function add_choice(obj) {
    var pobj = obj.parentNode.parentNode.previousSibling;
    var td_ih = pobj.lastChild.innerHTML;
    var newNode = pobj.cloneNode(true);
    if (td_ih.search(/render_choices\[([0-9]+)\]/) > -1)
        td_ih = td_ih.replace(/render_choices\[([0-9]+)\]/, 'render_choices[' + (parseInt(RegExp.$1) + 1) + ']');
    if (td_ih.search(/render_choice_files\[([0-9]+)\]/) > -1)
        td_ih = td_ih.replace(/render_choice_files\[([0-9]+)\]/, 'render_choice_files[' + (parseInt(RegExp.$1) + 1) + ']');
    var a = td_ih.split(/<br>/i);
    newNode.lastChild.innerHTML = (a[0].search(/type="?file"?/i) == -1) ? (a[0] + '<br>' + a[1]) : a[0];
    var nodes = newNode.getElementsByTagName('INPUT');
    for (var i = 0; i < nodes.length; i++) {
        switch (nodes[i].getAttribute('type')) {
            case 'checkbox':
            case 'radio':
                nodes[i].checked = false;
                nodes[i].value = parseInt(nodes[i].value) + 1;
                break;
            case 'text':
                nodes[i].value = '';
                // if (nodes[i].name.search(/^render_choice\[([0-9]+)\]$/) > -1)
                // 	nodes[i].name = 'render_choice[' + (parseInt(RegExp.$1)+1) + ']';
                break;
            case 'file':
                // if (nodes[i].name.search(/^render_choice_files\[([0-9]+)\]$/) > -1)
                // 	nodes[i].name = 'render_choice_files[' + (parseInt(RegExp.$1)+1) + ']';
                break;
        }
    }
    pobj.parentNode.insertBefore(newNode, pobj.nextSibling);

    
    initInputInnerHTMLFix();
    

}

/**
 * 配合題增加選項 (m ? 增加右選項 : 增加左選項)
 */
function add_choice2(obj, m) {
    if (m) {
        // match_letter
        var pobj = obj.parentNode.parentNode.previousSibling.lastChild.firstChild.firstChild;
        var newNode = pobj.lastChild.cloneNode(true);
        var td_ih = pobj.lastChild.lastChild.innerHTML;
        if (td_ih.search(/render2_choices\[([0-9]+)\]/) > -1)
            td_ih = td_ih.replace(/render2_choices\[([0-9]+)\]/, 'render2_choices[' + (parseInt(RegExp.$1) + 1) + ']');
        if (td_ih.search(/render2_choice_files\[([0-9]+)\]/) > -1)
            td_ih = td_ih.replace(/render2_choice_files\[([0-9]+)\]/, 'render2_choice_files[' + (parseInt(RegExp.$1) + 1) + ']');
        var a = td_ih.split(/<br>/i);
        newNode.lastChild.innerHTML = a[0] + '<br>' + a[1];
        var nodes = newNode.getElementsByTagName('INPUT');
        for (var i = 0; i < nodes.length; i++) {
            if (nodes[i].type == 'text') nodes[i].value = '';
        }
        pobj.appendChild(newNode);
        pobj.lastChild.firstChild.firstChild.nodeValue = pobj.childNodes.length - 1;
    } else {
        var pobj = obj.parentNode.parentNode.previousSibling.firstChild.firstChild.firstChild;
        var newNode = pobj.lastChild.cloneNode(true);
        var td_ih = pobj.lastChild.lastChild.innerHTML;
        if (td_ih.search(/render1_choices\[([0-9]+)\]/) > -1)
            td_ih = td_ih.replace(/render1_choices\[([0-9]+)\]/, 'render1_choices[' + (parseInt(RegExp.$1) + 1) + ']');
        if (td_ih.search(/render1_choice_files\[([0-9]+)\]/) > -1)
            td_ih = td_ih.replace(/render1_choice_files\[([0-9]+)\]/, 'render1_choice_files[' + (parseInt(RegExp.$1) + 1) + ']');
        var a = td_ih.split(/<br>/i);
        newNode.lastChild.innerHTML = a[0] + '<br>' + a[1];
        var nodes = newNode.getElementsByTagName('INPUT');
        for (var i = 0; i < nodes.length; i++) {
            if (nodes[i].type == 'text') nodes[i].value = '';
        }
        pobj.appendChild(newNode);
        pobj.lastChild.firstChild.firstChild.nodeValue = String.fromCharCode(pobj.childNodes.length + 63);
    }
}

/**
 * 減少附檔
 */
function rm_choice(obj,type) {
    // 上一列<tr>
    var pobj = obj.parentNode.parentNode.previousSibling;
            
    // 目前所在位置（題目或解答）
    var name = $(pobj).find("input[type='file']").prop('id');
    type = type - 1;
            
    // 依目前所在位置（題目或解答），取表單中該類別（題目或解答）全部的上傳附件筆數，如果只有1筆，則進行替換，如果多筆，則刪除上一列
    if ($("#tabContent"+type+" input[name='" + name + "']").length === 1) {
        $(pobj).find("input[type='file']").remove();
        $(pobj).find('td').last().prepend("<INPUT type=\"file\" name=\"" + name + "\" id=\"" + name + "\" onkeydown=\"return false;\" size=\"36\" class=\"box02\"  style=\"margin-right: 0.5em; margin-bottom: 0.3em;\">");
    } else {
        $(pobj).remove();
    }
}

/**
 * 減少選擇題選項
 */
function rm_choice1(obj) {
    var pobj = obj.parentNode.parentNode.previousSibling;
    var ppchild = pobj.previousSibling.firstChild.firstChild;
    if (ppchild != null && ppchild.nodeName == 'INPUT') {
        pobj.parentNode.removeChild(pobj);
    }
    // #47359 Chrome [辦公室/作業管理/題庫維護/新增] 題目附檔選擇一個檔案後，按下「減少附檔」無法清掉已選的附檔。：增加清空指令適用ie與chrome
    if (pobj.previousSibling !== null && pobj.previousSibling.firstChild.innerHTML !== '題目附檔：') {
        // pobj.lastChild.lastChild.value = "";
        var name = pobj.lastChild.lastChild.id;
        pobj.lastChild.lastChild.outerHTML = "<INPUT id=\"" + name + "\" class=\"box02\" size=\"36\" type=\"file\" name=\"" + name + "\">";
    }
}

/**
 * 配合題減少選項 (m ? 減少右選項 : 減少左選項)
 */
function rm_choice2(obj, m) {
    var pobj = m ? obj.parentNode.parentNode.previousSibling.lastChild.firstChild.firstChild :
        obj.parentNode.parentNode.previousSibling.firstChild.firstChild.firstChild;

    // #47359 Chrome [辦公室/作業管理/題庫維護/新增] 題目附檔選擇一個檔案後，按下「減少附檔」無法清掉已選的附檔。
    // 清空題目或待選項目，true待選false題目
    if (m) {
        ele = obj.parentNode.parentNode.previousSibling.lastChild.firstChild.firstChild.firstChild.nextSibling;
    } else {
        ele = obj.parentNode.parentNode.previousSibling.firstChild.firstChild.firstChild.firstChild.nextSibling;
    }
    if (ele.nextSibling === null) {
        var name = ele.lastChild.lastChild.id;
        ele.lastChild.lastChild.outerHTML = "<INPUT id=\"" + name + "\" class=\"box02\" size=\"20\" type=\"file\" name=\"" + name + "\" class=\"box02\">";
    }

    if (pobj.childNodes.length < 3) return;
    pobj.removeChild(pobj.lastChild);
}

function saveTemplate(button, type) {
    var buf = '';
    switch (type) {
        case 2: // 單選題
        case 3: // 複選題
            $(button).closest('table').find('tr.file-list input[type="text"]').each(function(index) {
                $(this).attr('value', $(this).val());
            });
            var tr = button.parentNode.parentNode;
            var table = tr.parentNode.parentNode;
            var rowNo = tr.rowIndex - 1;
            while (table.rows[rowNo].cells[0].innerHTML.replace(/^\s+|\s+$/g, '') != '') {
                buf = table.rows[rowNo].outerHTML + buf;
                rowNo--;
            }
            break;
        case 61: // 配合題 題目提示
        case 62: // 配合題 待選項目
            $(button).closest('tr').prev().find('input[type="text"]').each(function(index) {
                $(this).attr('value', $(this).val());
            });
            var table = button.parentNode.parentNode.previousSibling.cells[type - 61].firstChild;
            var rowNo = 1;
            while (rowNo < table.rows.length) {
                buf += table.rows[rowNo].outerHTML;
                rowNo++;
            }
            break;
    }

    if (buf == '') return;
    var title = prompt('{$MSG['input the title'][$sysSession->lang]}', '');
    if (title == null || title == '') return;
    xajax_save_template(title, type, buf);
}

function pickTemplate(type) {
    var v = document.getElementById('templateSelector' + type).value;
    if (v.search(/^\d+$/) == -1) return;
    v = parseInt(v);
    if (v) xajax_pick_template(v, type);
}

function delTemplate(type) {
    var v = document.getElementById('templateSelector' + type).value;
    if (v.search(/^\d+$/) == -1) return;
    v = parseInt(v);
    if (v && confirm('{$MSG['confirm to remove template'][$sysSession->lang]}')) xajax_del_template(v, type);
}

function template_replace(type) {
    var vt = document.getElementById('virtualTable');
    var trs = vt.rows;

    switch (type) {
        case 2:
        case 3:
            var tr = document.getElementById('templateSelector' + type).parentNode.parentNode;
            var table = tr.parentNode.parentNode;
            var rowNo = tr.rowIndex - 1;
            while (table.rows[rowNo].cells[0].innerHTML.replace(/^\s+|\s+$/g, '') != '') {
                table.deleteRow(rowNo);
                rowNo--;
            }

            rowNo++;
            var pn = table.rows[rowNo].parentNode;
            var tn = trs[0].parentNode;
            var tns = tn.childNodes;
            for (var i = 0; i < trs.length; i++) {
                n = tn.childNodes.item(i).cloneNode(true);
                pn.insertBefore(n, tr);
            }

            break;
        case 61:
        case 62:
            var table = document.getElementById('templateSelector' + type).parentNode.parentNode.parentNode.previousSibling.cells[type - 61].firstChild;
            for (var rowNo = table.rows.length - 1; rowNo >= 1; rowNo--) {
                table.deleteRow(rowNo);
            }

            var tn = trs[0].parentNode;
            var pn = table.rows[0].parentNode;
            for (var i = 0; i < trs.length; i++) {
                n = tn.childNodes.item(i).cloneNode(true);
                pn.appendChild(n);
            }
            break;
    }

    initInputInnerHTMLFix();
}
    

    
function chkForm(obj) {
    // 檢查latex長度，太長將會造成顯示失敗
    // 新增模式至少有12個（類型題目答案）要檢查，編輯模式有2個（題目答案）
    isSubmit = '1';
    $.each($('.cke_wysiwyg_frame'), function(key, value) {
        // 判斷題目或詳解
        if (window.console) {console.log(key);}   
        var type = '{$MSG['item_desc'][$sysSession->lang]}';
        if ((key+1) % 2 === 0) {
            type = '{$MSG['answer'][$sysSession->lang]}';
        } 
        txt = $(value).contents().find('.cke_editable').html();
        // 內文
        if (window.console) {console.log('內文', txt);}   

        // 正規表示式
        var regex = /\salt="(.*?)"/g;
        var matches = [];
        var match = regex.exec(txt);
        var i = 1;
        while (match != null) {
            // 單一比對結果
            if (window.console) {console.log(match[1]);}   
            // 長度
            if (window.console) {console.log('長度', match[1].length);}  
            if (match[1].length >= 101) {
                if (window.console) {console.log('太長');}   
                alert(type + '{$MSG['among'][$sysSession->lang]} ' + i +' {$MSG['latex_too_long'][$sysSession->lang]}');
                isSubmit = '0';
                return false;
            }
            i++;
            matches.push(match[1]);
            match = regex.exec(txt);
        }
        // 內文全部比對結果
        if (window.console) {console.log('內文全部比對結果', matches);}  
        if (window.console) {console.log('-----------------');}    
    });
    
    if (isSubmit === '1') {
        $(obj).parents('form').submit();
    }
}

EOB;
	  // if (strpos($_SERVER['HTTP_USER_AGENT'], 'Gecko/') !== false) showXHTML_script('include', '/lib/mozInnerHTML.js');
	  showXHTML_script('inline', $scr);
	  $xajax_choice_temp->printJavascript('/lib/xajax/');
	showXHTML_head_E();
	showXHTML_body_B(); // 'oncontextmenu="return false;" ondragstart="return false;" onselectstart ="return false;" onmousedown="return false;"');
	if ($dom)
	{
		$ary = array(array("{$MSG['item_modify'][$sysSession->lang]} - " . $MSG['item_type' . $RS['type']][$sysSession->lang], 'tabsSet',  '')
					);
		$anses = $dom->get_elements_by_tagname('varequal');
	}
	else
	{
		$ary = array(array($MSG['item_type1'][$sysSession->lang], 'tabsSet',  'switchTab(0)'),
		             array($MSG['item_type2'][$sysSession->lang], 'tabsSet',  'switchTab(1)'),
		             array($MSG['item_type3'][$sysSession->lang], 'tabsSet',  'switchTab(2)'),
		             array($MSG['item_type4'][$sysSession->lang], 'tabsSet',  'switchTab(3)'),
		             array($MSG['item_type5'][$sysSession->lang], 'tabsSet',  'switchTab(4)'),
		             array($MSG['item_type6'][$sysSession->lang], 'tabsSet',  'switchTab(5)'),
		             array($MSG['item_type7'][$sysSession->lang], 'tabsSet',  'switchTab(6)')
		            );
		if (QTI_which != 'exam' || !defined('sysEnableRecordingAttachmentExamType') || !sysEnableRecordingAttachmentExamType) array_pop($ary);
	}

	if ((empty($_POST['lists']))&&(QTI_which == 'exam')&&(CourseQuestionsLimit > 0))   //有題目數的限制
	{
		// 取得目前此課程的題目數
		list($now_CourseQusNum) = dbGetStSr('WM_qti_exam_item','count(*)',"course_id='{$sysSession->course_id}'", ADODB_FETCH_NUM);
	    if (intval($now_CourseQusNum) >= CourseQuestionsLimit)
	    {
	        $ary = array(array($MSG['item_type1'][$sysSession->lang], 'tabsSet',  ''),
			             array($MSG['item_type2'][$sysSession->lang], 'tabsSet',  ''),
			             array($MSG['item_type3'][$sysSession->lang], 'tabsSet',  ''),
			             array($MSG['item_type4'][$sysSession->lang], 'tabsSet',  ''),
			             array($MSG['item_type5'][$sysSession->lang], 'tabsSet',  ''),
			             array($MSG['item_type6'][$sysSession->lang], 'tabsSet',  ''),
			             array($MSG['item_type7'][$sysSession->lang], 'tabsSet',  '')
	        );
	        if (QTI_which != 'exam' || !defined('sysEnableRecordingAttachmentExamType') || !sysEnableRecordingAttachmentExamType) array_pop($ary);

	      $msg = str_replace('%questions_limit%',CourseQuestionsLimit,$MSG['msg_overQuestionLimit'][$sysSession->lang]);
	      list($admin_email) = dbGetStSr(sysDBname.'.WM_school','school_mail',"school_id='{$sysSession->school_id}'", ADODB_FETCH_NUM);
          $msg = str_replace('%admin_email%','mailto:'.$admin_email, $msg);
          showXHTML_tabFrame_B($ary, 1, 'tabContent0', '', 'method="POST" enctype="multipart/form-data" action="item_' . ($dom ? 'modify' : 'create') . '1.php" onsubmit="return submit_check(1);" style="display: inline"');
    	  showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="960" style="border-collapse: collapse" class="box01"');
		  showXHTML_tr_B('class="font01 cssTrHead"');
		  	showXHTML_td('colspan="4"', $msg);
		  showXHTML_tr_E();
		  showXHTML_tr_B('class="font01 cssTrEvn"');
		    showXHTML_td_B('colspan="4"', $msg);
		  	showXHTML_input('button', '', $MSG['return_menu'][$sysSession->lang], '', 'class="cssBtn" onclick="location.replace(\'item_maintain.php?' . $_POST['gets'] . '\');"');
		  	showXHTML_td_E();
		  showXHTML_tr_E();
       	  showXHTML_tabFrame_E();
          showXHTML_body_E();
          exit;
	    }
	}


// <!-- ================================  第一個 TAB(是非)  ================================ -->
	if ($RS['type'] == '1') $ans = getNodeContent($anses[0]);
	if (!isset($RS['type']) || $RS['type'] == '1')
	{
	  showXHTML_tabFrame_B($ary, 1, 'tabContent0', '', 'method="POST" enctype="multipart/form-data" action="item_' . ($dom ? 'modify' : 'create') . '1.php" onsubmit="return submit_check(1);" style="display: inline"');
	    ItemElementHeadPart(1);
	        showXHTML_tr_B('class="bg03 font01"' . (QTI_which == 'questionnaire' ? ' style="display: none"' : ''));
	          showXHTML_td('align="right"', $MSG['ans_colon'][$sysSession->lang]);
	          showXHTML_td_B('colspan="3"');
	            showXHTML_input('radio', 'answer', array('T' => '<img src="/theme/default/learn/right.gif" align="absmiddle">', 'F' => '<img src="/theme/default/learn/wrong.gif" align="absmiddle">'), ($ans ? $ans : 'T'));
	          showXHTML_td_E();
	        showXHTML_tr_E();
	    ItemElementTailPart(1);
	  if (!isset($RS['type'])) echo "\t\t</form>\n";
	}
	unset($ans);

// <!-- ================================  第二個 TAB(單選) ================================ -->
	$j = 4;
	if ($RS['type'] == '2')
	{
		$ans = intval(getNodeContent($anses[0]));
	    $nodes = $dom->get_elements_by_tagname('response_label');
	    $j = count($nodes);
	}
	if (!isset($RS['type']) || $RS['type'] == '2')
	{
		if ($dom)
			showXHTML_tabFrame_B($ary, 1, 'tabContent1', '', 'method="POST" enctype="multipart/form-data" action="item_modify1.php" onsubmit="return submit_check(2);" style="display: inline"');
		else
			echo '		<form id="tabContent1" name="tabContent1" accept-charset="UTF-8" lang="ZH-TW" method="POST" enctype="multipart/form-data" action="item_create1.php" onsubmit="return submit_check(2);" style="display: inline" >', "\n";

		  ItemElementHeadPart(2);
		    for($i=1; $i<=$j; $i++){
		      showXHTML_tr_B('class="bg03 font01 file-list"');
		        showXHTML_td_B('align="right"');
		          showXHTML_input('radio', 'answer', array($i => $MSG['choice_colon'][$sysSession->lang]), $ans, (QTI_which == 'questionnaire' ? 'style="display: none"' : ($i==1?'checked':'')));
		        showXHTML_td_E();
		        showXHTML_td_B('colspan="3"');
		          showXHTML_input('text', 'render_choices[' . ($i-1) . ']', ($nodes ? htmlspecialchars(getNodeContent($nodes[$i-1])) : ''), '', 'size="42" class="box02" maxlength="500"'); echo '<br>';
		          showXHTML_input('file', 'render_choice_files[' . ($i-1) . ']', '', '', 'size="36" class="box02"' . ($GLOBALS['quota_used'] < $GLOBALS['quota_limit'] ? '' : ' disabled'));
		          if (is_array($attach['render_choice_files'])){
		          	list($k, $v) = each($attach['render_choice_files']);
		          	if ($v)
		          	{
		          		echo '<br>'; showXHTML_input('checkbox', "render_choice_files_rm[]", $k);
		          		echo gen_link($k, $v);
		          	}
		          }
		        showXHTML_td_E();
		      showXHTML_tr_E();
		    }
		      showXHTML_tr_B('class="bg04 font01"');
		        showXHTML_td('align="right"', '');
		        showXHTML_td_B('colspan="3"');
		          showXHTML_input('button', '', $MSG['incr_choice'][$sysSession->lang], '', 'class="cssBtn" ' . ($GLOBALS['quota_used'] < $GLOBALS['quota_limit'] ? 'onclick="add_choice(this);"':'disabled'));
		          showXHTML_input('button', '', $MSG['decr_choice'][$sysSession->lang], '', 'class="cssBtn" onclick="rm_choice1(this);"');
		          showXHTML_input('button', '', $MSG['save as a template'][$sysSession->lang], '', 'class="cssBtn" onclick="saveTemplate(this,2);"');
				  $choice_templates = array('0' => $MSG['select a template'][$sysSession->lang]) +
		          					  dbGetAssoc('WM_qti_choice_template', 'unix_timestamp(create_time),title', 'owner_id=' . $course_id . ' and type=2');
		          showXHTML_input('select', '', $choice_templates, '', 'id="templateSelector2"');
		          showXHTML_input('button', '', $MSG['apply template'][$sysSession->lang], '', 'class="cssBtn" onclick="pickTemplate(2);"');
		          showXHTML_input('button', '', $MSG['remove template'][$sysSession->lang], '', 'class="cssBtn" onclick="delTemplate(2);"');
		        showXHTML_td_E();
		      showXHTML_tr_E();
		  ItemElementTailPart(2);
		if (!isset($RS['type'])) echo "\t\t</form>\n";
	}
	unset($j); unset($ans); unset($nodes);

// <!-- ================================  第三個 TAB(複選)  ================================ -->
	$j = 4; $ans = array();
	if ($RS['type'] == '3')
	{
		foreach($anses as $item) $ans[] = intval(getNodeContent($item));
		$nodes = $dom->get_elements_by_tagname('response_label');
	    $j = count($nodes);
	}
	if (!isset($RS['type']) || $RS['type'] == '3')
	{
		if ($dom)
			showXHTML_tabFrame_B($ary, 1, 'tabContent2', '', 'method="POST" enctype="multipart/form-data" action="item_modify1.php" onsubmit="return submit_check(3);" style="display: inline"');
		else
			echo '		<form id="tabContent2" name="tabContent2" accept-charset="UTF-8" lang="ZH-TW" method="POST" enctype="multipart/form-data" action="item_create1.php" onsubmit="return submit_check(3);" style="display: inline" >', "\n";

		  ItemElementHeadPart(3);
		    for($i=1; $i<=$j; $i++){
		      showXHTML_tr_B('class="bg03 font01"');
		        showXHTML_td_B('align="right"');
		          showXHTML_input('checkbox', 'answer[]', $i, (in_array($i, $ans)?$i:0), (QTI_which == 'questionnaire' ? 'style="display: none"' : '')); echo $MSG['choice_colon'][$sysSession->lang];
		        showXHTML_td_E();
		        showXHTML_td_B('colspan="3"');
		          showXHTML_input('text', 'render_choices[' . ($i-1) . ']', ($nodes ? htmlspecialchars(getNodeContent($nodes[$i-1])) : ''), '', 'size="42" class="box02" maxlength="500"'); echo '<br>';
		          showXHTML_input('file', 'render_choice_files[' . ($i-1) . ']', '', '', 'size="36" class="box02"' . ($GLOBALS['quota_used'] < $GLOBALS['quota_limit'] ? '' : ' disabled'));
		          if (is_array($attach['render_choice_files'])){
		          	list($k, $v) = each($attach['render_choice_files']);
		          	if ($v)
		          	{
		          	  echo '<br>'; showXHTML_input('checkbox', "render_choice_files_rm[]", $k);
		          	  echo gen_link($k, $v);
		          	}
		          }
		        showXHTML_td_E();
		      showXHTML_tr_E();
		    }
		      showXHTML_tr_B('class="bg04 font01"');
		        showXHTML_td('align="right"', '');
		        showXHTML_td_B('colspan="3"');
		          showXHTML_input('button', '', $MSG['incr_choice'][$sysSession->lang], '', 'class="cssBtn" ' . ($GLOBALS['quota_used'] < $GLOBALS['quota_limit'] ? 'onclick="add_choice(this);"':'disabled'));
		          showXHTML_input('button', '', $MSG['decr_choice'][$sysSession->lang], '', 'class="cssBtn" onclick="rm_choice1(this);"');
		          showXHTML_input('button', '', $MSG['save as a template'][$sysSession->lang], '', 'class="cssBtn" onclick="saveTemplate(this,3);"');
		          $choice_templates = array('0' => $MSG['select a template'][$sysSession->lang]) +
		          					  dbGetAssoc('WM_qti_choice_template', 'unix_timestamp(create_time),title', 'owner_id=' . $course_id . ' and type=3');
		          showXHTML_input('select', '', $choice_templates, '', 'id="templateSelector3"');
		          showXHTML_input('button', '', $MSG['apply template'][$sysSession->lang], '', 'class="cssBtn" onclick="pickTemplate(3);"');
		          showXHTML_input('button', '', $MSG['remove template'][$sysSession->lang], '', 'class="cssBtn" onclick="delTemplate(3);"');
		        showXHTML_td_E();
		      showXHTML_tr_E();
		  ItemElementTailPart(3);
		if (!isset($RS['type'])) echo "\t\t</form>\n";
	}
	unset($j); unset($ans); unset($nodes);

// <!-- ================================  第四個 TAB(填充)  ================================ -->
	if (!isset($RS['type']) || $RS['type'] == '4')
	{
		if ($dom)
			showXHTML_tabFrame_B($ary, 1, 'tabContent3', '', 'method="POST" enctype="multipart/form-data" action="item_modify1.php" onsubmit="return submit_check(4);" style="display: inline"');
		else
			echo '		<form id="tabContent3" name="tabContent3" accept-charset="UTF-8" lang="ZH-TW" method="POST" enctype="multipart/form-data" action="item_create1.php" onsubmit="return submit_check(4);" style="display: inline" >', "\n";

		ItemElementHeadPart(4, '<br><b><font color="red">' . $MSG['fill_explain'][$sysSession->lang] . '</font></b>');
		ItemElementTailPart(4);
		if (!isset($RS['type'])) echo "\t\t</form>\n";
	}

// <!-- ================================  第五個 TAB(簡答)  ================================ -->
	if (!isset($RS['type']) || $RS['type'] == '5')
	{
		if ($dom)
			showXHTML_tabFrame_B($ary, 1, 'tabContent4', '', 'method="POST" enctype="multipart/form-data" action="item_modify1.php" onsubmit="return submit_check(5);" style="display: inline"');
		else
			echo '		<form id="tabContent4" name="tabContent4" accept-charset="UTF-8" lang="ZH-TW" method="POST" enctype="multipart/form-data" action="item_create1.php" onsubmit="return submit_check(5);" style="display: inline" >', "\n";

		ItemElementHeadPart(5);
	ItemElementTailPart(5);
	if (!isset($RS['type'])) echo "\t\t</form>\n";
	}

// <!-- ================================  第六個 TAB(配合)  ================================ -->
	if (!isset($RS['type']) || $RS['type'] == '6')
	{
		$ans = array();
		if ($dom)
		{
			showXHTML_tabFrame_B($ary, 1, 'tabContent5', '', 'method="POST" enctype="multipart/form-data" action="item_modify1.php" onsubmit="return submit_check(6);" style="display: inline"');
			$left_nodes = array(); $right_node = array();
			$ret = $ctx->xpath_eval('/item/presentation//response_grp//response_label[@match_max]');
			if (is_array($ret->nodeset)) foreach($ret->nodeset as $left) $left_nodes[ord($left->get_attribute('ident'))] = getNodeContent($left);
			$ret = $ctx->xpath_eval('/item/presentation//response_grp//response_label[string(@match_max)=""]');
			if (is_array($ret->nodeset)) foreach($ret->nodeset as $right) $right_nodes[intval($right->get_attribute('ident'))] = getNodeContent($right);
			$ret = $ctx->xpath_eval('/item/resprocessing/respcondition/varsubset');
			if (is_array($ret->nodeset)) foreach($ret->nodeset as $a) $ans[] = $a->get_content();
		}
		else
		{
			$left_nodes = array(65 => '', 66 => ''); $right_nodes = array(1 => '', 2 => '');
			echo '		<form id="tabContent5" name="tabContent5" accept-charset="UTF-8" lang="ZH-TW" method="POST" enctype="multipart/form-data" action="item_create1.php" onsubmit="return submit_check(6);" style="display: inline" >', "\n";
		}

		ItemElementHeadPart(6);
                
                
		    showXHTML_tr_B('class="bg03 font01 aaa"');
		      showXHTML_td_B('colspan="4" align="right" style="padding-left: 3em;"');
                      
                      
                      echo '<table width="100%" border="0" cellpadding="3" cellspacing="0"><tr><td>';
                      
		        showXHTML_table_B('border="0" cellpadding="3" cellspacing="0" id="match_letter"');
		          showXHTML_tr_B();
		            showXHTML_td('class="font01"', (QTI_which == 'questionnaire' ? '':$MSG['correct_ans'][$sysSession->lang]));
		            showXHTML_td('class="font01"', $MSG['item_hint'][$sysSession->lang]);
		          showXHTML_tr_E('');
                          
		         // for($i=65; $i<67; $i++){
		         foreach($left_nodes as $i => $value){
		          showXHTML_tr_B();
		            showXHTML_td_B();
		              echo chr($i);
		              list($foo, $a) = each($ans);
		              showXHTML_input('text', 'answer[]', $a, '', sprintf('size="5" class="box02" maxlength="10" title="%s"%s', $MSG['input_number'][$sysSession->lang], (QTI_which == 'questionnaire' ? ' style="display: none"' : '')));
		            showXHTML_td_E();
		            showXHTML_td_B();
		              showXHTML_input('text', 'render1_choices[' . ($i-65) . ']', htmlspecialchars($value), '', 'size="20" class="box02" maxlength="500"');
		              echo '<br>';
		              showXHTML_input('file', 'render1_choice_files[' . ($i-65) . ']', '', '', 'size="20" class="box02"' . ($GLOBALS['quota_used'] < $GLOBALS['quota_limit'] ? '' : ' disabled'));
		              if($attach['render1_choice_files'])
		            if (is_array($attach['render1_choice_files'])){
		            	list($k, $v) = each($attach['render1_choice_files']);
		            	if ($v)
		            	{
		            	  echo '<br>'; showXHTML_input('checkbox', "render1_choice_files_rm[]", $k);
		            	  echo gen_link($k, $v);
		            	}
		            }
		            showXHTML_td_E();
		          showXHTML_tr_E();
		         }
		        showXHTML_table_E();
		      showXHTML_td_E();
		      showXHTML_td_B('colspan="2" width="50%"');
		        showXHTML_table_B('border="0" cellpadding="3" cellspacing="0" id="match_number"');
		          showXHTML_tr_B();
		            showXHTML_td('', '&nbsp;');
		            showXHTML_td('class="font01"', $MSG['candidate_item'][$sysSession->lang]);
		          showXHTML_tr_E();
		         // for($i=1; $i<3; $i++){
		         foreach($right_nodes as $i => $value){
		          showXHTML_tr_B();
		            showXHTML_td('align="right"', $i);
		            showXHTML_td_B();
		              showXHTML_input('text', 'render2_choices[' . ($i-1) . ']', htmlspecialchars($value), '', 'size="20" class="box02" maxlength="500"');
		              echo '<br>';
		              showXHTML_input('file', 'render2_choice_files[' . ($i-1) . ']', '', '', 'size="20" class="box02"' . ($GLOBALS['quota_used'] < $GLOBALS['quota_limit'] ? '' : ' disabled'));
		            if (is_array($attach['render2_choice_files'])){
		            	list($k, $v) = each($attach['render2_choice_files']);
		            	if ($v)
		            	{
	            		  echo '<br>'; showXHTML_input('checkbox', "render2_choice_files_rm[]", $k);
		            	  echo gen_link($k, $v);
		            	}
		            }
		            showXHTML_td_E();
		          showXHTML_tr_E();
		         }
		        showXHTML_table_E();
		      showXHTML_td_E();
		    showXHTML_tr_E();
		    showXHTML_tr_B('class="bg03 font01 aaa"');
		      showXHTML_td_B('align="left" nowrap style="padding-left: 1.4em;"');
		        showXHTML_input('button', '', $MSG['incr_choice'][$sysSession->lang], '', 'class="cssBtn" ' . ($GLOBALS['quota_used'] < $GLOBALS['quota_limit'] ? 'onclick="add_choice2(this, false);"':'disabled'));
	        	showXHTML_input('button', '', $MSG['decr_choice'][$sysSession->lang], '', 'class="cssBtn" onclick="rm_choice2(this, false);" '); echo '<br>';
				showXHTML_input('button', '', $MSG['save as a template'][$sysSession->lang], '', 'class="cssBtn" onclick="saveTemplate(this,61);"');
                          echo '<div>';
				$choice_templates = array('0' => $MSG['select a template'][$sysSession->lang]) +
									dbGetAssoc('WM_qti_choice_template', 'unix_timestamp(create_time),title', 'owner_id=' . $course_id . ' and type=61');
				showXHTML_input('select', '', $choice_templates, '', 'id="templateSelector61"');
				showXHTML_input('button', '', $MSG['apply template'][$sysSession->lang], '', 'class="cssBtn" onclick="pickTemplate(61);"');
                          echo '</div>';
				showXHTML_input('button', '', $MSG['remove template'][$sysSession->lang], '', 'class="cssBtn" onclick="delTemplate(61);"');
		      showXHTML_td_E();
		      showXHTML_td_B('colspan="2" align="left" nowrap style="padding-left: 1.4em;"');
		        showXHTML_input('button', '', $MSG['incr_choice'][$sysSession->lang], '', 'class="cssBtn" ' . ($GLOBALS['quota_used'] < $GLOBALS['quota_limit'] ? 'onclick="add_choice2(this, true);"':'disabled'));
		        showXHTML_input('button', '', $MSG['decr_choice'][$sysSession->lang], '', 'class="cssBtn" onclick="rm_choice2(this, true);" '); echo '<br>';
				showXHTML_input('button', '', $MSG['save as a template'][$sysSession->lang], '', 'class="cssBtn" onclick="saveTemplate(this,62);"');
                          echo '<div>';
				$choice_templates = array('0' => $MSG['select a template'][$sysSession->lang]) +
				                    dbGetAssoc('WM_qti_choice_template', 'unix_timestamp(create_time),title', 'owner_id=' . $course_id . ' and type=62');
				showXHTML_input('select', '', $choice_templates, '', 'id="templateSelector62"');
				showXHTML_input('button', '', $MSG['apply template'][$sysSession->lang], '', 'class="cssBtn" onclick="pickTemplate(62);"');
                          echo '</div>';
				showXHTML_input('button', '', $MSG['remove template'][$sysSession->lang], '', 'class="cssBtn" onclick="delTemplate(62);"');
                                
                                echo '</td></tr></table>';
                                
                                
                                
		      showXHTML_td_E();
		    showXHTML_tr_E('');
                    
                    
                    
                    
                    
                    
                    
		ItemElementTailPart(6);
	  if (!isset($RS['type'])) echo "\t\t</form>\n";
	}

// <!-- ================================  第七個 TAB(錄音/附檔)  ================================ -->
	if (!isset($RS['type']) || $RS['type'] == '7')
	{
	    $which_kinds = array();
		if ($dom)
		{
			showXHTML_tabFrame_B($ary, 1, 'tabContent6', '', 'method="POST" enctype="multipart/form-data" action="item_modify1.php" onsubmit="return submit_check(7);" style="display: inline"');
            if (($ret1 = $ctx->xpath_eval('count(//response_label[@ident="REC01"])')) !== false && $ret1->value)
            	$which_kinds[] = 'use_record';
			if (($ret2 = $ctx->xpath_eval('count(//response_label[@ident="FILE01"])')) !== false && $ret2->value)
			    $which_kinds[] = 'use_attach';
		}
		else
			echo '		<form id="tabContent6" name="tabContent6" accept-charset="UTF-8" lang="ZH-TW" method="POST" enctype="multipart/form-data" action="item_create1.php" onsubmit="return submit_check(7);" style="display: inline" >', "\n";

	    ItemElementHeadPart(7);
	        showXHTML_tr_B('class="bg03 font01"' . (QTI_which == 'questionnaire' ? ' style="display: none"' : ''));
	          showXHTML_td('align="right"', $MSG['ans_colon'][$sysSession->lang]);
	          showXHTML_td_B('colspan="3"');
	            showXHTML_input('checkboxes', 'render_extensions[]', array('use_record' => $MSG['response by recording'][$sysSession->lang],
																		   'use_attach' => $MSG['response by uploading'][$sysSession->lang]), $which_kinds);
	          showXHTML_td_E();
	        showXHTML_tr_E();
	    ItemElementTailPart(7);
	}

// <!-- ================================  TAB 終止 ================================ -->
	  showXHTML_tabFrame_E();

	  echo '<div id="choice_template_panel" style="display: none"></div>';

/*
	showXHTML_form_B('style="display: none"');
	    showXHTML_input('textarea', '', '', '', 'id="choice_template_panel"');
	showXHTML_form_E();
*/
	showXHTML_body_E();
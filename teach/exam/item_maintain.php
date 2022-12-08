<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *              Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *              Programmer: Wiseguy Liang                                                         *
	 *              Creation  : 2002/10/25                                                            *
	 *              work for  : Item maintain center                                                  *
	 *              work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *                                                                                                *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/' . QTI_which . '_teach.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
        // CUSTOM BY tn (B)
        function mdate($format, $microtime = null) {
            $microtime = explode(' ', ($microtime ? $microtime : microtime()));
            if (count($microtime) != 2) return false;
            $microtime[0] = $microtime[0] * 1000000;
            $format = str_replace('u', $microtime[0], $format);
            return date($format, $microtime[1]);
        }
        // CUSTOM BY tn (E)
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

    /*
	*設定每頁幾筆
	*/

	// 寫入 Cookie 每頁筆數
	function SetQTIPageCookie($rows_page,$timeout=86400) {
		setcookie('Qrows_page', $rows_page,  time()+$timeout	, '/');
	}

	// 清除 Cookie 每頁筆數
	function ClearQTIPageCookie() {
		setcookie('Qrows_page', '',  time()-1	, '/');
	}

	// 取得每頁筆數
	function GetQTIPostPerPage() {

		global $_POST,$_COOKIE, $sysSession;
		$rows_page = -1;
		if(isset($_POST['rows_page'])) {	// 檢查是否要求變動 ( -1 為要求恢復預設值 )
			$rows_page = IntVal($_POST['rows_page']);

			if($rows_page==0) {	// 未作變動
				if(isset($_COOKIE['Qrows_page'])) {
					$rows_page = IntVal($_COOKIE['Qrows_page']);
				}
			}

		} else if(isset($_COOKIE['Qrows_page'])) {	// 無 POST, 但有 COOKIE
			$rows_page = IntVal($_COOKIE['Qrows_page']);
		}

		if($rows_page <= 0) {
			$rows_page = sysPostPerPage;
			ClearQTIPageCookie();
		} else {
			SetQTIPageCookie($rows_page);
		}

		return $rows_page;
	}

	// 每頁筆數下拉框
	$rows_per_page = Array(-1=>$MSG['default'][$sysSession->lang],
				20=>20,50=>50,100=>100,200=>200,400=>400);

	// 目前每頁筆數
	$rows_page = GetQTIPostPerPage();

	if(isset($_COOKIE['SQrows_page'])) {
		$rows_page_share = IntVal($_COOKIE['SQrows_page']);
	}else{
		$rows_page_share = sysPostPerPage;
		}


	//ACL begin
	if (QTI_which == 'exam') {
		$sysSession->cur_func='1600100300';
	}
	else if (QTI_which == 'homework') {
		$sysSession->cur_func='1700100300';
	}
	else if (QTI_which == 'questionnaire') {
		$sysSession->cur_func='1800100300';
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

	$ticket = md5(trim($_SERVER['QUERY_STRING'] . sysTicketSeed . $course_id . $_COOKIE['idx']));

	$sbs = array('', 'ident', 'type', 'version', 'volume', 'chapter', 'paragraph', 'section', 'level');
	if (ereg('^[12345678]$', $_GET['s'])){
		$sb = $sbs[$_GET['s']];
		$sbv = $_GET['s'];
	}
	else{
		$sb = 'ident';
		$sbv = 1;
	}
	$dir = ($_GET['d']) ? 'DESC' : 'ASC' ;

	$type = array('',
		      $MSG['item_type1'][$sysSession->lang],
		      $MSG['item_type2'][$sysSession->lang],
		      $MSG['item_type3'][$sysSession->lang],
		      $MSG['item_type4'][$sysSession->lang],
		      $MSG['item_type5'][$sysSession->lang],
		      $MSG['item_type6'][$sysSession->lang],
		      $MSG['item_type7'][$sysSession->lang]
		     );
    if (QTI_which != 'exam' || !defined('sysEnableRecordingAttachmentExamType') || !sysEnableRecordingAttachmentExamType) array_pop($type);
	$level = array('',
		       $MSG['hard_level1'][$sysSession->lang],
		       $MSG['hard_level2'][$sysSession->lang],
		       $MSG['hard_level3'][$sysSession->lang],
		       $MSG['hard_level4'][$sysSession->lang],
		       $MSG['hard_level5'][$sysSession->lang]
		      );

	// 版、冊、章、節、段 與 難易度
	$_POST['level']     = intval($_POST['level']);
	$_POST['version']   = intval($_POST['version']);
	$_POST['volume']    = intval($_POST['volume']);
	$_POST['chapter']   = intval($_POST['chapter']);
	$_POST['paragraph'] = intval($_POST['paragraph']);
	$_POST['section']   = intval($_POST['section']);

	//搜尋條件

	if (!isset($_POST['op']))	//由item_modify1.php導回的
	{
		if (!empty($_COOKIE['QuestionItemQueryConds']))
		{
			$_POST['op'] = 'search';
			$tmpArr = unserialize(base64_decode($_COOKIE['QuestionItemQueryConds']));
			//設定查詢字串
			for($i=0, $size=count($tmpArr); $i<$size; $i++)
			{
				if (strpos($tmpArr[$i],"title like") === false)
				{
					list($key, $value) = explode('=',$tmpArr[$i],2);
					switch(trim($key))
					{
						case 'version':
							$_POST['isVersion'] = 'ON';
							$_POST['version'] = trim($value);
							break;
						case 'volume':
							$_POST['isVolume'] = 'ON';
							$_POST['volume'] = trim($value);
							break;
						case 'chapter':
							$_POST['isChapter'] = 'ON';
							$_POST['chapter'] = trim($value);
							break;
						case 'paragraph':
							$_POST['isParagraph'] = 'ON';
							$_POST['paragraph'] = trim($value);
							break;
						case 'section':
							$_POST['isSection'] = 'ON';
							$_POST['section'] = trim($value);
							break;
						case '`type`':
							$_POST['isType'] = 'ON';
							$_POST['type'] = trim($value);
							break;
						case 'level':
							$_POST['isLevel'] = 'ON';
							$_POST['level'] = trim($value);
							break;
					}
				}else{
					list($key, $value) = explode('like',$tmpArr[$i],2);
					$_POST['isFulltext'] = 'ON';
					$_POST['fulltext'] = trim($value);
				}
			}
		}
	}

	$fulltext = htmlspecialchars(htmlspecialchars($_POST['fulltext']));
	$conds = array();
	$conds[] = " course_id={$course_id} ";
	if (($_POST['isVersion']   == 'ON') && (strlen($_POST['version'])>0)) 	$conds[] = " version={$_POST['version']} ";
	if (($_POST['isVolume']    == 'ON') && (strlen($_POST['volume'])>0))	$conds[] = " volume={$_POST['volume']} ";
	if (($_POST['isChapter']   == 'ON') && (strlen($_POST['chapter'])>0))	$conds[] = " chapter={$_POST['chapter']} ";
	if (($_POST['isParagraph'] == 'ON') && (strlen($_POST['paragraph'])>0))	$conds[] = " paragraph={$_POST['paragraph']} ";
	if (($_POST['isSection']   == 'ON') && (strlen($_POST['section'])>0))  	$conds[] = " section={$_POST['section']} ";
	if (($_POST['isType']      == 'ON')) 									$conds[] = " `type`={$_POST['type']} ";
	if (($_POST['isLevel']     == 'ON'))  									$conds[] = " level={$_POST['level']} ";
	// MIS#26052 題庫題目搜尋與試卷題目搜尋不一致 by Small 2012/07/18
	// if (($_POST['isFulltext']  == 'ON') && (strlen($_POST['fulltext'])>0))  $conds[] = " title like {$fulltext} ";
	if (($_POST['isFulltext']  == 'ON') && (strlen($_POST['fulltext'])>0))  $conds[] = " locate('{$fulltext}',content) ";
	$condstr = implode(' and ', $conds);

	if (strlen($condstr)>0)  //有設定查詢條件
	{
		$cookieStr = base64_encode(serialize($conds));
		setcookie ("QuestionItemQueryConds", $cookieStr, null, "/teach/".QTI_which."/");
	}

	// MIS#26052 題庫題目搜尋與試卷題目搜尋不一致 by Small 2012/07/18
	// if (($_POST['isFulltext'] == 'ON') && (strlen($_POST['fulltext'])>0))  		$conds[count($conds)-1] = " title like '%{$fulltext}%' ";
	if (($_POST['isFulltext'] == 'ON') && (strlen($_POST['fulltext'])>0))  		$conds[count($conds)-1] = " locate('{$fulltext}',content) ";
	$condstr = implode(' and ', $conds);
	list($total_item) = dbGetStSr('WM_qti_' . QTI_which . '_item', 'count(*)', $condstr, ADODB_FETCH_NUM);

	$total_page = ceil($total_item / $rows_page);
	if ($total_page == 0) $total_page=1;
	$cur_page = intval($_GET['p']);
	if ($cur_page < 1 || $cur_page > $total_page) $cur_page = $total_page;

	$RS = dbGetStMr('WM_qti_' . QTI_which . '_item',
	                'ident,title,content,type,version,volume,chapter,paragraph,section,level',
	                "{$condstr} order by $sb $dir limit " . (($cur_page-1)*$rows_page) . ',' . $rows_page,
	                ADODB_FETCH_ASSOC);
	if ($sysConn->ErrorNo() > 0) {
		$errMsg = $sysConn->ErrorMsg();
		wmSysLog($sysSession->cur_func, $course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], $errMsg);
		die($errMsg);
	}

    // 試題限制-取得目前此課程的題目數
    $isOverQuestionNumber = 0;
    if (QTI_which == 'exam')
    {
    	list($now_CourseQusNum) = dbGetStSr('WM_qti_exam_item','count(*)',"course_id='{$sysSession->course_id}'", ADODB_FETCH_NUM);
    	if (CourseQuestionsLimit > 0 )   //有題目數的限制
    	{
    		if (intval($now_CourseQusNum)>=CourseQuestionsLimit)
    		{
    			$isOverQuestionNumber = 1;
    			$js_msg_overNum = str_replace('%questions_limit%',CourseQuestionsLimit,$MSG['msg2_overQuestionLimit'][$sysSession->lang]);
    		}
    	}
    }

	// 開始 output HTML
	showXHTML_head_B($MSG['item_maintain'][$sysSession->lang]);
	  showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$topDir}/wm.css");
	  showXHTML_script('include', '/lib/dragLayer.js');
	  showXHTML_script('include', '/lib/xmlextras.js');

	  $dir0 = $_GET['d'] ? 0 : 1;
	  $dir1 = $_GET['d'] ? 1 : 0;
	  $qtiW = QTI_which;
	  $scr = <<< EOB
var isIE = (navigator.userAgent.search(' MSIE ') > -1) ? true : false;
var sbv = {$sbv};
var cur_page = {$cur_page};
var total_page = {$total_page};
var rows_page = $rows_page;
var rows_page_share = $rows_page_share;
var hasPickedShare = false;
var msg1 = '{$MSG['select_first'][$sysSession->lang]}';
var msg2 = '{$MSG['edit_only_one'][$sysSession->lang]}';
var qti_which = '{$qtiW}';
var isOverQuestionNumber = {$isOverQuestionNumber};

var types = new Array('',
				      '{$MSG['item_type1'][$sysSession->lang]}',
				      '{$MSG['item_type2'][$sysSession->lang]}',
				      '{$MSG['item_type3'][$sysSession->lang]}',
				      '{$MSG['item_type4'][$sysSession->lang]}',
				      '{$MSG['item_type5'][$sysSession->lang]}',
				      '{$MSG['item_type6'][$sysSession->lang]}',
				      '{$MSG['item_type7'][$sysSession->lang]}'
				     );
if (qti_which!='questionnaire'){
	var srTables = new Array('<input type="checkbox" name="search_ck" id="search_ck" value="" onclick="search_selectItem(this.checked);" exclude="true">',
							 'No.',
							 '{$MSG['item_type'][$sysSession->lang]}',
							 '{$MSG['item_desc'][$sysSession->lang]}',
							 '{$MSG['version'][$sysSession->lang]}',
							 '{$MSG['volume'][$sysSession->lang]}',
							 '{$MSG['chapter'][$sysSession->lang]}',
							 '{$MSG['paragraph'][$sysSession->lang]}',
							 '{$MSG['section'][$sysSession->lang]}',
							 '{$MSG['hard_level'][$sysSession->lang]}'
							);
	}
else {
	var srTables = new Array('<input type="checkbox" name="search_ck" id="search_ck" value="" onclick="search_selectItem(this.checked);" exclude="true">',
							 'No.',
							 '{$MSG['item_type'][$sysSession->lang]}',
							 '{$MSG['item_desc'][$sysSession->lang]}',
							 '{$MSG['version'][$sysSession->lang]}',
							 '{$MSG['volume'][$sysSession->lang]}',
							 '{$MSG['chapter'][$sysSession->lang]}',
							 '{$MSG['paragraph'][$sysSession->lang]}',
							 '{$MSG['section'][$sysSession->lang]}'
							);
}
var btms = new Array('{$MSG['select'][$sysSession->lang]}',
				     '{$MSG['cancel'][$sysSession->lang]}',
				     '{$MSG['prev_step'][$sysSession->lang]}',
				     '{$MSG['next_step'][$sysSession->lang]}'
				    );
var rowspages = new Array(-1,20,50,100,200,400);

var rowspagesn = new Array('{$MSG['default'][$sysSession->lang]}',20,50,100,200,400);

var MSG_LEVEL = new Array('',
						  '{$MSG['hard_level1'][$sysSession->lang]}',
						  '{$MSG['hard_level2'][$sysSession->lang]}',
						  '{$MSG['hard_level3'][$sysSession->lang]}',
						  '{$MSG['hard_level4'][$sysSession->lang]}',
						  '{$MSG['hard_level5'][$sysSession->lang]}');

var MSG_SELECT_ALL      = "{$MSG['select_all'][$sysSession->lang]}";
var MSG_SELECT_CANCEL   = "{$MSG['cancel_all'][$sysSession->lang]}";
var MSG_SEARCHPAGE_TOP  = "{$MSG['page_first'][$sysSession->lang]}";
var MSG_SEARCHPAGE_UP   = "{$MSG['page_prev'][$sysSession->lang]}";
var MSG_SEARCHPAGE_DOWN = "{$MSG['page_next'][$sysSession->lang]}";
var MSG_SEARCHPAGE_END  = "{$MSG['page_last'][$sysSession->lang]}";
var MSG_PAGE_NUM        = "{$MSG['page'][$sysSession->lang]}";
var MSG_PAGE_EACH       = "{$MSG['each_page'][$sysSession->lang]}";
var MSG_PAGE_ITEM       = "{$MSG['item'][$sysSession->lang]}";
var MSG_BTN_DEL         = "{$MSG['remove'][$sysSession->lang]}";

var fn = parent.document.getElementById('envCourse');
if (fn == null) fn = parent.document.getElementById('workarea');
if (fn.cols != '0,*')
	fn.cols = '0,*';


window.onload = function(){
	// rm_whitespace(document);
	var obj = document.getElementById('mainTable');
	obj.rows[(obj.rows.length-1)].cells[0].innerHTML = obj.rows[1].cells[0].innerHTML;
	obj.rows[(obj.rows.length-1)].cells[1].innerHTML = obj.rows[1].cells[1].innerHTML;
};

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
function displayDialog(name)
{
	var obj = document.getElementById(name);
	// 對話框左邊對齊 = [捲動的左座標] + [該 Frame 的寬度] - [對話框寬度(480)] 再左移 10 個 pixel
	obj.style.left  = document.body.scrollLeft + document.body.offsetWidth - 460;
	// 對話框上緣對齊 = [捲動的上座標] 下移 10 個 pixel
	obj.style.top   = document.body.scrollTop  + 30;
	obj.style.display = '';
}
function exportDone()
{
    var obj = document.getElementById('exportForm');
    document.getElementById('exportTable').style.display='none';
    var ret = getElements();
    if (ret == '') {
        alert(msg1);
        return;
    }
    obj.lists.value = ret;
    obj.gets.value = location.search.substr(1);
    obj.submit();
    document.getElementById('form1').reset();
    checkWhetherAll();
}

function selectItem(selAll){
	var obj = document.getElementById('mainTable');

	var nodes = obj.getElementsByTagName('input');
	for(var i=15; i<nodes.length; i++){
		if (nodes.item(i).type == 'checkbox')
			nodes.item(i).checked = selAll;
	}

	var btn1 = document.getElementById("btnSel1");
	if (btn1 != null) btn1.value = selAll ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;

	var obj = document.getElementById('mainTable');
	obj.rows[(obj.rows.length-1)].cells[0].innerHTML = obj.rows[1].cells[0].innerHTML;
	obj.rows[(obj.rows.length-1)].cells[1].innerHTML = obj.rows[1].cells[1].innerHTML;
}

function sort(n){
	if (sbv == n){
		location.replace('item_maintain.php?s=' + n + '&d=$dir0&p=' + cur_page);
	}
	else{
		location.replace('item_maintain.php?s=' + n + '&d=$dir1&p=' + cur_page);
	}
}

function do_search_item()
{
	document.form1.action='item_maintain.php';
	document.form1.op.value = 'search';
	document.form1.submit();
}

function page(n){
	var url = 'item_maintain.php?s=' + sbv + '&d=$dir1&p=';
	switch(n){
		case -1:
			document.form1.action = url + '1';
			break;
		case -2:
			document.form1.action = url + (cur_page-1);
			break;
		case -3:
			document.form1.action = url + (cur_page+1);
			break;
		case -4:
			document.form1.action = url + total_page;
			break;
		default:
			var p = parseInt(n);
			if (p > 0 && p <= total_page){
				document.form1.action = url + p.toString();
			}
			break;
	}
	document.form1.submit();
}

function getElements(){
	var obj   = document.getElementById('form1');
	var nodes = obj.getElementsByTagName('input');
	var ret   = '';
	for(var i=1; i<nodes.length; i++){
		if (nodes.item(i).type == 'checkbox' && nodes.item(i).value && nodes.item(i).checked)
			if (nodes.item(i).name == 'sel[]')	ret += (nodes.item(i).value + ',');
	}
	return ret.replace(/,$/, '');
}

function checkWhetherAll()
{
	var obj   = document.getElementById('form1');
	var nodes = obj.getElementsByTagName('input');
	var btn1  = document.getElementById("btnSel1");

	var on=0, off=0;
	for(var i=1; i<nodes.length; i++){
		if (nodes.item(i).type == 'checkbox' && nodes.item(i).name == 'sel[]')
			if (nodes.item(i).checked) on++; else off++;
	}

	if (on > 0 && off == 0) // 全選
		selectItem(true);
	else
	{
		if (off > 0){		//   未全選所有的 checkbox
			obj  = document.getElementById("ck_box");
			obj.checked = false;

			if (btn1 != null) btn1.value = MSG_SELECT_ALL;

			var obj = document.getElementById('mainTable');
			obj.rows[(obj.rows.length-1)].cells[0].innerHTML = obj.rows[1].cells[0].innerHTML;
			obj.rows[(obj.rows.length-1)].cells[1].innerHTML = obj.rows[1].cells[1].innerHTML;

		}
	}
}

function process(n){
	var obj = document.getElementById('todoForm');
	if (n != 1){
		var ret = getElements();
		if (ret == ''){
			alert(msg1);
			return;
		}
		var selectLists = ret.split(',');
	}
	obj.gets.value = location.search.substr(1);
	obj.target = "_self";
	switch(n){
		case 1: // Addnew
			obj.action = 'item_create.php';
			obj.lists.value = '';
			obj.submit();
			break;
		case 2: // Edit
			if (selectLists.length > 1){
				alert(msg2);
				return;
			}
			obj.action = 'item_modify.php';
			obj.lists.value = ret;
			obj.submit();
			break;
		case 3: // Remove
			if (confirm('{$MSG['remove_confirm'][$sysSession->lang]}')){
				obj.action = 'item_remove.php';
				obj.lists.value = ret;
				obj.submit();
			}
			break;
		case 4: // Copy
			obj.action = 'item_copy.php';
			obj.lists.value = ret;
			obj.submit();
			break;
		case 5: // Export
                    // CUSTOM BY tn (B)
                    displayDialog('exportTable');
                    // obj.action = 'item_export.php';
                    // obj.lists.value = ret;
                    // obj.target = "empty";
                    // obj.submit();
                    // obj.target = '_self';
                    break;
                    // CUSTOM BY tn (E)
		case 6: // share
			obj.action = 'item_share.php';
			obj.target = 'IFrameItemShare';
			obj.lists.value = ret;
			obj.submit();
			selectItem(false);
			return false;
			break;
		case 7: // preview
			obj.action = 'item_preview.php';
			obj.target = '_blank';
			obj.lists.value = ret;
			obj.submit();
			obj.target = '_self';
			break;
	}
        if(n!=5) document.getElementById('form1').reset();
        checkWhetherAll();
}

function processDel(val) {
	var obj = document.getElementById('todoForm');
	obj.gets.value = location.search.substr(1);
	obj.target = "_self";
	if (confirm('{$MSG['remove_confirm'][$sysSession->lang]}')) {
		obj.action = 'item_remove.php';
		obj.lists.value = val;
		obj.submit();
	}
}

function switchTab(t){
	if (t==1 && hasPickedShare) self.location.reload();
	for(var i=1; i<4; i++){
		document.getElementById('form' + i).style.display = (t == i) ? 'inline' : 'none';
	}
	document.getElementById('srTable').style.display = (t == 3) ? 'inline' : 'none';
}

/**
 * 向 server 取得搜尋題目
 */
function search_item(blnNewSearch){
	if (typeof(blnNewSearch) == 'undefined') blnNewSearch = false;
	var topPanel = document.getElementById('form3');
	if (blnNewSearch) {
        topPanel.pages.value = 1;
	}
	var queryXml = '<form>' +
	                  '<version   selected="' + topPanel.isVersion.checked   + '">' + topPanel.version.value   + '</version>' +
	                  '<volume    selected="' + topPanel.isVolume.checked    + '">' + topPanel.volume.value    + '</volume>' +
	                  '<chapter   selected="' + topPanel.isChapter.checked   + '">' + topPanel.chapter.value   + '</chapter>' +
	                  '<paragraph selected="' + topPanel.isParagraph.checked + '">' + topPanel.paragraph.value + '</paragraph>' +
	                  '<section   selected="' + topPanel.isSection.checked   + '">' + topPanel.section.value   + '</section>' +
	                  '<type      selected="' + topPanel.isType.checked      + '">' + topPanel.type.value      + '</type>' +
	                  (
	                      typeof topPanel.isMyShare !== 'undefined' ?
	                      '<myshare   selected="' + topPanel.isMyShare.checked   + '">1</myshare>' :
	                      ''
	                  ) +
	                  (qti_which!='questionnaire'?('<level     selected="' + topPanel.isLevel.checked     + '">' + topPanel.level.value     + '</level>'):'') +
	                  '<fulltext  selected="' + topPanel.isFulltext.checked  + '">' + escape(topPanel.fulltext.value) + '\t' + topPanel.fulltext.value + '</fulltext>' +
	                  '<scope>' + topPanel.scope.value + '</scope>' +
	    			  '<rowspage>' + topPanel.rows_page_share.value + '</rowspage>'+
	    			  '<pages>' + (blnNewSearch ? 1 : topPanel.pages.value) + '</pages>'+
                     '</form>';
	var xmlHttp = XmlHttp.create();
	var xmlVars = XmlDocument.create();
	xmlVars.loadXML(queryXml);
	xmlHttp.open('POST', 'item_search.php', false);
	xmlHttp.send(xmlVars);
	var ret = xmlVars.loadXML(xmlHttp.responseText);
    // alert (xmlHttp.responseText);
   	if (ret == false) { alert('{$MSG['return_not_xml'][$sysSession->lang]}'); return;}
	var root = xmlVars.documentElement;
	if (root.tagName == 'errorlevel'){
		switch(root.firstChild.nodeValue){
			case '1':
				alert('{$MSG['incorrect_xml'][$sysSession->lang]}'); return;
				break;
			case '2':
				alert('{$MSG['incorrect_form'][$sysSession->lang]}'); return;
				break;
			case '3':
				alert('{$MSG['no_result'][$sysSession->lang]}'); return;
				break;
			default:
				alert('{$MSG['unknown_err'][$sysSession->lang]}'); return;
				break;
		}
	}
	if (root.tagName != 'questestinterop' ) { alert('Returning XML\'s root node nust <questestinterop>'); return;}
	var nodes = root.childNodes;
	var total_shareitem = nodes[0].firstChild.nodeValue;
	if (topPanel.rows_page_share.value =='-1'){var rows_page_now = '10';}else{var rows_page_now = topPanel.rows_page_share.value;}
	var pagelength = Math.ceil(total_shareitem/rows_page_now);
	var htm = '<form id="shareForm" style="display:inline"><table border="0" cellpadding="3" cellspacing="1" style="border-collapse: collapse" class="box01" id="searchTable">' +
		      '<tr class="cssTrEvn font01">'+
		      '<td align="center">' +
		      '<input type="button" value="' + MSG_SELECT_ALL + '" onclick="search_selfunc();" id="search_btnSel1" class="cssBtn">' +
		      '</td>' +
		      '<td colspan="' + (qti_which == 'questionnaire' ? 7 : 8) + '" align="center">' +
			  '<font class=font01>' + MSG_PAGE_NUM + '</font>'+
			  '<select onchange="search_page(this.value)">';
		      for(var s=1; s<pagelength+1; s++){
	    htm+= ('<option value="'+ s + '"');
	    	  if (s == topPanel.pages.value){
	    	  	  htm+= ('selected="selected"');
	    	  }
	    htm+= ('>' + s + '</option>');
	    	  }
	    htm+= '</select> '+
			  '<font class=font01>' + MSG_PAGE_EACH + '</font>'+
			  '<select name="rps" onchange="go_rowspage_share(this.value,'+
			   '' + total_shareitem + ')">';
		      for(var r=0; r<rowspages.length; r++){
	    htm+= ('<option value="'+ rowspages[r] +'"');
	    	  if (rowspages[r] == rows_page_now){
	    	  	  htm+= ('selected="selected"');
	    	  }
	    htm+= ('>' + rowspagesn[r] + '</option>');

	          }

	    htm+= '</select> '+
			  '<font class=font01>' + MSG_PAGE_ITEM + '</font> '+
		      '<input type="button" value="' + MSG_SEARCHPAGE_TOP + '"  onclick="search_page(1);" id="s_pagebtn1" class="cssBtn" ';
		if (topPanel.pages.value=='1'){
		       htm+= ('disabled');
			   }
		htm+= '> ' +
		      '<input type="button" value="' + MSG_SEARCHPAGE_UP + '"   onclick="search_page(' + topPanel.pages.value + '-1);" id="s_pagebtn2" class="cssBtn" ';
		if (topPanel.pages.value=='1'){
		       htm+= ('disabled');
			   }
		htm+= '> ' +
		      '<input type="button" value="' + MSG_SEARCHPAGE_DOWN + '" onclick="search_page(' + topPanel.pages.value + '+1);" id="s_pagebtn3" class="cssBtn" ';
		if (topPanel.pages.value==pagelength){
		       htm+= ('disabled');
			   }
		htm+= '> ' +
		      '<input type="button" value="' + MSG_SEARCHPAGE_END + '"  onclick="search_page(' + pagelength + ');" id="s_pagebtn4" class="cssBtn" ';
				if (topPanel.pages.value==pagelength){
		       htm+= ('disabled');
			   }

		if (topPanel.isMyShare) {
			htm+= '> &nbsp;&nbsp;' +
				'<input type="button" value="' + MSG_BTN_DEL + '"  onclick="shareProcessDel()" id="s_delbtn" class="cssBtn" ';
			if (!topPanel.isMyShare.checked) {
				htm += 'disabled';
			}
		}

		htm+= '> ' +
		      '</td>'+
		      '<td align="center">' +
	          '<input type="button" value="' + btms[0] + '" onclick="pickItem();" class="cssBtn">&nbsp;&nbsp;&nbsp;' +
	          '</td></tr><tr class="bg02 font01">';
		  for(var i=0; i<srTables.length; i++)
		  htm += ('<td align="center">' + srTables[i] + '</td>');
		  htm += '</tr>';
	var properties;


	var col = '';
	var serial_no = (topPanel.pages.value - 1) * rows_page_now + 1;
	for(var i=1; i<nodes.length; i++){
		if (nodes[i].tagName == 'item'){
			col = col == 'class="bg04 font01"' ? 'class="bg03 font01"' : 'class="bg04 font01"';
			htm += '<tr ' + col + '>';
			properties = nodes[i].childNodes;
			if (nodes[i].getAttribute('code') == 1) {
				if (nodes[i].getAttribute('owner') == 1) {
					htm += '<td align="center"><input type="button" value="' + MSG_BTN_DEL + '" class="cssBtn"' +
						' onclick="shareProcessDel(\'' + properties[0].firstChild.nodeValue + '\')" ></td>';
				} else {
					htm += '<td align="center">&nbsp;</td>';
				}
			} else {
				htm += '<td align="center"><input type="checkbox" name="pick[]" value="' + properties[0].firstChild.nodeValue + '" onclick="checkPick();"></td>';
			}
			htm += '<td align="right" style="padding-right: 1em">' + (serial_no++) + '</td>';
			for(var j=1; j<properties.length; j++){
                // alert (j+':'+properties[j].firstChild.nodeValue);
				switch (j) {
					case 1:
						htm += '<td width="40">' + types[properties[j].firstChild.nodeValue] + '</td>';
						break;
					case 2:
						htm += '<td width="300">';
						htm += (properties[j].firstChild) ? properties[j].firstChild.nodeValue : '&nbsp;';
						htm += '</td>';
						break;
					case 8:
						htm += (qti_which == 'questionnaire') ? '' : ('<td width="50">' + MSG_LEVEL[properties[j].firstChild.nodeValue] + '</td>');
						break;
					default:
						htm += '<td width="20">' + properties[j].firstChild.nodeValue + '</td>';
						break;
				}
			}
			htm += '</tr>';
		}
	}
	htm += '<tr class="cssTrEvn font01">'+
		   '<td align="center">' +
		   '<input type="button" value="' + MSG_SELECT_ALL + '" onclick="search_selfunc()" id="search_btnSel2" class="cssBtn">' +
		   '</td>' +
		   '<td colspan="' + (qti_which == 'questionnaire' ? 7 : 8) + '" align="center">' +
		   '</td>'+
		   '<td>'+
	       '<input type="button" value="' + btms[0] + '" onclick="pickItem();" class="cssBtn">&nbsp;&nbsp;&nbsp;' +
	       '</td></tr></table></form>';

	document.getElementById('searchResult').innerHTML = htm;
	document.getElementById('srTable').style.display='';
	var t = document.getElementById('searchTable');
	t.rows[t.rows.length-1].cells[1].innerHTML = t.rows[0].cells[1].innerHTML;
}

function showSearch()
{
	obj = document.getElementById("searchPanel");
	if (obj.style.display == 'none')
	{
		obj.style.display = '';
	}else{
		obj.style.display = 'none'
	}
}

/**
 * 題目搜尋時，若 text 有填入資料則前面 checkbox 自動勾選
 */
function checkSelect(obj){
	obj.previousSibling.previousSibling.checked = (obj.value != '');
}

/**
 * 將資源中心的題目加入自己的題庫
 */
function pickItem(){
    if (isOverQuestionNumber)
    {
        alert('{$js_msg_overNum}');
        return;
    }
	var obj = document.getElementById('searchResult');
	var nodes = obj.getElementsByTagName('input');
	var queryXml = '<form>';
	for(var i=0; i<nodes.length; i++){
		if (nodes[i].type == 'checkbox' && nodes[i].checked){
			queryXml += '<item ident="' + nodes[i].value + '" />';
		}
    }
	queryXml += '</form>';
	if (queryXml == '<form></form>') return;
	var xmlHttp = XmlHttp.create();
	var xmlVars = XmlDocument.create();
	xmlVars.loadXML(queryXml);
	xmlHttp.open('POST', 'item_getShared.php', false);
	xmlHttp.send(xmlVars);
	alert(xmlHttp.responseText);
	hasPickedShare = true;
}

function edit_item(obj)
{
	document.getElementById('form1').reset();
	obj.parentNode.parentNode.cells[0].getElementsByTagName('input')[0].checked = true;
	process(2);
}

function selfunc() {
	var obj  = document.getElementById("ck_box");
	selectItem(!obj.checked);
}

function go_rowspage(n){
	var frm = document.getElementById('form1');
	if(n=='-1') {
		new_page = cur_page;
	} else {
		cur_pos = cur_page*rows_page;	// 目前位置
		new_page= Math.ceil(cur_pos/n);	// 換算頁數
	}
	frm.rows_page.value = n;
	page(new_page);
}

function go_rowspage_share(n,m){
	var form = document.getElementById('form3');
	if (form.rows_page_share.value =='-1'){var rows_page_now = '10';}else{var rows_page_now = form.rows_page_share.value;}
	if (n =='-1'){var i = '10';}else{var i = n;}
	var h = rows_page_now*document.getElementById('pages').value;
	var newpage = Math.ceil(h/i);
	var limpage = Math.ceil(m/i);
	if (h<m){
	    document.getElementById('pages').value = newpage;
	}else{
	    document.getElementById('pages').value = limpage;
	}
	document.getElementById('rows_page_share').value = n;
	search_item();
}

function search_page(n){
var form = document.getElementById('form3');
	if(n>0){
		document.getElementById('pages').value = n;
		search_item();
	}
}

// for 題庫分享中心 (begin)
function search_selfunc() {
	var obj  = document.getElementById("search_ck");
	search_selectItem(!obj.checked);
}

function search_selectItem(selAll){
	var obj = document.getElementById('searchTable');
	var nodes = obj.getElementsByTagName('input');
	for(var i=0; i<nodes.length; i++){
		if (nodes.item(i).type == 'checkbox')
			nodes.item(i).checked = selAll;
	}

	var btn1 = document.getElementById("search_btnSel1");
	if (btn1 != null) btn1.value = selAll ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;

	var obj = document.getElementById('searchTable');
	obj.rows[(obj.rows.length-1)].cells[0].innerHTML = obj.rows[0].cells[0].innerHTML;
	obj.rows[(obj.rows.length-1)].cells[1].innerHTML = obj.rows[0].cells[1].innerHTML;

}

function checkPick()
{
	var obj = document.getElementById('searchTable');
	var nodes = obj.getElementsByTagName('input');
	var btn1 = document.getElementById("search_btnSel1");

	var on=0, off=0;
	for(var i=1; i<nodes.length; i++){
		if (nodes.item(i).type == 'checkbox' && nodes.item(i).name == 'pick[]')
			if (nodes.item(i).checked) on++; else off++;
	}

	if (on > 0 && off == 0){  // 全選
		search_selectItem(true);
	}else{

		if (off > 0){		//   未全選所有的 checkbox
			ck_obj  = document.getElementById("search_ck");
			ck_obj.checked = false;

			if (btn1 != null) btn1.value = MSG_SELECT_ALL;

			obj.rows[(obj.rows.length-1)].cells[0].innerHTML = obj.rows[0].cells[0].innerHTML;
			obj.rows[(obj.rows.length-1)].cells[1].innerHTML = obj.rows[0].cells[1].innerHTML;
		}
	}
}

// 刪除資源中心的題目
function shareProcessDel(val) {
	var obj = null, nodes = null, ret = '', ary = [], i = 0, c = 0;

	if (typeof val === 'undefined') {
		// 取得勾選的題目
		var obj = document.getElementById('shareForm');
		if (obj !== null) {
			nodes = obj.getElementsByTagName('input');
			for (i = 0, c = nodes.length; i < c; i++) {
				if ((nodes[i].type !== 'checkbox') || !nodes[i].checked) {
					continue;
				}
				if (nodes[i].id == 'search_ck') {
					continue;
				}
				ary[ary.length] = nodes[i].value;
			}
			ret = ary.join(',');
		}
	} else {
		ret = val;
	}

	if (ret == ''){
		alert(msg1);
		return;
	}

	obj = document.getElementById('todoForm');
	obj.gets.value = location.search.substr(1);
	obj.target = "_self";
	if (confirm('{$MSG['remove_confirm'][$sysSession->lang]}')) {
		obj.action = 'item_share_remove.php';
		obj.lists.value = ret;
		obj.submit();
	}
}
// for 題庫分享中心 (end)

EOB;
	  showXHTML_script('inline', $scr);
	showXHTML_head_E();
	showXHTML_body_B();
	  showXHTML_table_B('border="0" cellpadding="0" cellspacing="0" width="760" style="border-collapse: collapse"');
	    showXHTML_tr_B();
	      showXHTML_td_B();
			$ary = array(
				array($MSG['item_maintain'][$sysSession->lang], 'tabsSet',  'switchTab(1);'),
				array($MSG['import'][$sysSession->lang],        'tabsSet',  'switchTab(2);'),
				array($MSG['search_scope3'][$sysSession->lang], 'tabsSet',  'switchTab(3);')
			);
		showXHTML_tabs($ary, 1);
	      showXHTML_td_E('');
	    showXHTML_tr_E();
	    showXHTML_tr_B();
	      showXHTML_td_B('class="bg01"');
		showXHTML_form_B(' method="POST" style="display:inline" id="form1"', 'form1');
		  showXHTML_input('hidden', 'op', $_POST['op'], '', '');
		  showXHTML_input('hidden','rows_page',0);
		  showXHTML_table_B('id ="mainTable" border="0" cellpadding="3" cellspacing="1" width="760" style="border-collapse: collapse" class="box01"');
		    showXHTML_tr_B('class="bg03"');
		      showXHTML_td_B('colspan='.(QTI_which!='questionnaire'?'"10"':'"9"'));
		      	showXHTML_table_B('id ="searchPanel" border="0" cellpadding="3" cellspacing="1" width="900" style="border-collapse: collapse;" class="box01"');
	              showXHTML_tr_B('class="bg03 font01"');
	                showXHTML_td('colspan="2" align="right" rowspan="3"', $MSG['search_proviso'][$sysSession->lang]);
	                /*#47384 [Safari][教師/測驗管理/題庫維護] 搜索功能右邊框線不見：移除合併爛位*/
                    showXHTML_td_B('');
                        echo '<label>';
	                  showXHTML_input('checkbox', 'isVersion', 'ON', '', (($_POST['isVersion']=='ON') ? ' checked':'')); echo $MSG['version'][$sysSession->lang];
	                  showXHTML_input('text', 'version', $_POST['version'], '', 'size="4" onkeyup="checkSelect(this);" class="box02"'); echo str_repeat('&nbsp', 5);
                        echo '</label>';
                        echo '<label>';
	                  showXHTML_input('checkbox', 'isVolume', 'ON', '', (($_POST['isVolume']=='ON') ? ' checked':'')); echo $MSG['volume'][$sysSession->lang];
	                  showXHTML_input('text', 'volume', $_POST['volume'], '', 'size="4" onkeyup="checkSelect(this);" class="box02"'); echo str_repeat('&nbsp', 5);
                        echo '</label>';
                        echo '<label>';
	                  showXHTML_input('checkbox', 'isChapter', 'ON', '', (($_POST['isChapter']=='ON') ? ' checked':'')); echo $MSG['chapter'][$sysSession->lang];
	                  showXHTML_input('text', 'chapter', $_POST['chapter'], '', 'size="4" onkeyup="checkSelect(this);" class="box02"'); echo str_repeat('&nbsp', 5);
                        echo '</label>';
                        echo '<label>';
	                  showXHTML_input('checkbox', 'isParagraph', 'ON', '', (($_POST['isParagraph']=='ON') ? ' checked':'')); echo $MSG['paragraph'][$sysSession->lang];
	                  showXHTML_input('text', 'paragraph', $_POST['paragraph'], '', 'size="4" onkeyup="checkSelect(this);" class="box02"'); echo str_repeat('&nbsp', 5);
                        echo '</label>';
                        echo '<label>';
	                  showXHTML_input('checkbox', 'isSection', 'ON', '', (($_POST['isSection']=='ON') ? ' checked':'')); echo $MSG['section'][$sysSession->lang];
	                  showXHTML_input('text', 'section', $_POST['section'], '', 'size="4" onkeyup="checkSelect(this);" class="box02"');
                        echo '</label>';
	                showXHTML_td_E();
	              showXHTML_tr_E();

	              showXHTML_tr_B('colspan="2" class="bg04 font01"');
	                showXHTML_td_B('');
	                  showXHTML_input('checkbox', 'isType', 'ON', '', (($_POST['isType']=='ON') ? ' checked':'')); echo $MSG['item_type'][$sysSession->lang];
					  $item_types = array(1 => $MSG['item_type1'][$sysSession->lang],
  					 					  2 => $MSG['item_type2'][$sysSession->lang],
  					 					  3 => $MSG['item_type3'][$sysSession->lang],
  					 					  4 => $MSG['item_type4'][$sysSession->lang],
  					 					  5 => $MSG['item_type5'][$sysSession->lang],
  					 					  6 => $MSG['item_type6'][$sysSession->lang],
  					 					  7 => $MSG['item_type7'][$sysSession->lang]
  					 					 );
                      if (QTI_which != 'exam' || !defined('sysEnableRecordingAttachmentExamType') || !sysEnableRecordingAttachmentExamType) array_pop($item_types);
	                  showXHTML_input('select', 'type', $item_types, $_POST['type'], 'size="1" class="box02" onchange="checkSelect(this);"');
					if (QTI_which != 'questionnaire') {
					  echo str_repeat('&nbsp', 5);
					  showXHTML_input('checkbox', 'isLevel', 'ON', '', (($_POST['isLevel']=='ON') ? ' checked':'')); echo $MSG['hard_level'][$sysSession->lang];
	                  showXHTML_input('select', 'level', array(1 => $MSG['hard_level1'][$sysSession->lang],
	                  					 					   2 => $MSG['hard_level2'][$sysSession->lang],
	                  					 					   3 => $MSG['hard_level3'][$sysSession->lang],
	                  					 					   4 => $MSG['hard_level4'][$sysSession->lang],
	                  					 					   5 => $MSG['hard_level5'][$sysSession->lang]
	                  					 					  ), $_POST['level'], 'size="1" class="box02" onchange="checkSelect(this);"');
					}
	                showXHTML_td_E();
	              showXHTML_tr_E();

	              showXHTML_tr_B('colspan="2" class="bg03 font01"');
	                showXHTML_td_B('');
					  showXHTML_input('checkbox', 'isFulltext', 'ON', '', (($_POST['isFulltext']=='ON') ? ' checked':'')); echo $MSG['key_words'][$sysSession->lang];
	                  showXHTML_input('text', 'fulltext', ((strlen($_POST['fulltext'])>0) ?  $_POST['fulltext'] : $MSG['key_words_hint'][$sysSession->lang]), '', 'size="30" class="box02" onfocus="this.value=\'\';" onkeyup="checkSelect(this);"');
	                showXHTML_td_E();
	              showXHTML_tr_E();

   	              showXHTML_tr_B('class="bg04 font01"');
	                showXHTML_td('colspan="2" align="right"', $MSG['search_scope'][$sysSession->lang]);
	                showXHTML_td_B('');
	                  showXHTML_input('select', 'scope', array(1 => $MSG['search_scope1'][$sysSession->lang],
	                  					  					   2 => $MSG['search_scope2'][$sysSession->lang],
	                  					  					   3 => $MSG['search_scope3'][$sysSession->lang]
	                  					  					  ), 1, 'class="box02" style="display: none"');
	                  showXHTML_input('button', '', $MSG['start_search'][$sysSession->lang], '', 'class="cssBtn" onclick="do_search_item();"');
	                showXHTML_td_E();
	              showXHTML_tr_E();
		      	showXHTML_table_E();
		      showXHTML_td_E('');
		    showXHTML_tr_E();


		    showXHTML_tr_B('class="bg03"');
		      showXHTML_td_B('width="20"');
				showXHTML_input('button', 'btnSel1', $MSG['select_all'][$sysSession->lang], '', 'id="btnSel1" onclick="selfunc()" class="cssBtn"');
		      showXHTML_td_E();
		      showXHTML_td_B('width="100%" colspan='.(QTI_which!='questionnaire'?'"9"':'"8"'));

			showXHTML_table_B('border="0" cellpadding="3" cellspacing="0" style="border-collapse: collapse"');
			  showXHTML_tr_B();
			    showXHTML_td_B('class="bg04" noWrap');
			      echo "<font class=font01>" . $MSG['page'][$sysSession->lang] . "</font>";
			      showXHTML_input('select', '', array_range(1,$total_page), $cur_page, 'size="1" onchange="page(this.value);"');
			      echo "<font class=font01>" . $MSG['each_page'][$sysSession->lang] . "</font>";
				  showXHTML_input('select', 'rp', $rows_per_page, $rows_page==sysPostPerPage?'default':$rows_page, 'class="cssInput" onchange="go_rowspage(this.value);"');
			      echo "<font class=font01>" . $MSG['item'][$sysSession->lang] . "</font>";
				  showXHTML_input('button', '', $MSG['page_first'][$sysSession->lang], '', 'class="cssBtn" ' . ($cur_page==1?          'disabled' : 'onclick="page(-1);"'));
			      showXHTML_input('button', '', $MSG['page_prev'][$sysSession->lang] , '', 'class="cssBtn" ' . ($cur_page==1?          'disabled' : 'onclick="page(-2);"'));
			      showXHTML_input('button', '', $MSG['page_next'][$sysSession->lang] , '', 'class="cssBtn" ' . ($cur_page==$total_page?'disabled' : 'onclick="page(-3);"'));
			      showXHTML_input('button', '', $MSG['page_last'][$sysSession->lang] , '', 'class="cssBtn" ' . ($cur_page==$total_page?'disabled' : 'onclick="page(-4);"'));
			    showXHTML_td_E();
			    showXHTML_td('width="20"', '&nbsp;');
			    showXHTML_td_B('class="bg04" noWrap');
			      showXHTML_input('button', '', $MSG['addnew'][$sysSession->lang] , '', 'class="cssBtn" onclick="process(1);"');
			      showXHTML_input('button', '', $MSG['modify'][$sysSession->lang] , '', 'class="cssBtn" onclick="process(2);"');
			      showXHTML_input('button', '', $MSG['remove'][$sysSession->lang] , '', 'class="cssBtn" onclick="process(3);"');
			      showXHTML_input('button', '', $MSG['copy'][$sysSession->lang]   , '', 'class="cssBtn" onclick="process(4);"');
			      showXHTML_input('button', '', $MSG['export'][$sysSession->lang] , '', 'class="cssBtn" onclick="process(5);"');
			      showXHTML_input('button', '', $MSG['share'][$sysSession->lang]  , '', 'class="cssBtn" onclick="process(6);"');
			      showXHTML_input('button', '', $MSG['preview'][$sysSession->lang], '', 'class="cssBtn" onclick="process(7);"');
			      showXHTML_input('button', '', $MSG['search'][$sysSession->lang] , '', 'class="cssBtn" onclick="showSearch();"');
			    showXHTML_td_E();
			    showXHTML_td('width="20"','&nbsp;');
			    showXHTML_td('width="20"','&nbsp;');
			  showXHTML_tr_E();
			showXHTML_table_E(); // t3
		      showXHTML_td_E();
		    showXHTML_tr_E();
		    showXHTML_tr_B('class="bg02 font01"');
		      showXHTML_td_B('width="20" align="center" ');
		      	showXHTML_input('checkbox', '', '', '', 'id="ck_box" onclick="selectItem(this.checked);"');
		      showXHTML_td_E();
		      showXHTML_td('width="40" align="center"', '<a href="javascript:sort(1)">' . $MSG['serial_no'][$sysSession->lang]  . '</a>');
		      showXHTML_td('width="60" align="center"',             '<a href="javascript:sort(2)">' . $MSG['item_type'][$sysSession->lang]  . '</a>');
		      showXHTML_td('width='.(QTI_which!='questionnaire'?'"380"':'"460"'),                           $MSG['item_desc'][$sysSession->lang]);
		      showXHTML_td('width="32" align="center"',             '<a href="javascript:sort(3)">' . $MSG['version'][$sysSession->lang]    . '</a>');
		      showXHTML_td('width="32" align="center"',             '<a href="javascript:sort(4)">' . $MSG['volume'][$sysSession->lang]     . '</a>');
		      showXHTML_td('width="32" align="center"',             '<a href="javascript:sort(5)">' . $MSG['chapter'][$sysSession->lang]    . '</a>');
		      showXHTML_td('width="32" align="center"',             '<a href="javascript:sort(6)">' . $MSG['paragraph'][$sysSession->lang]  . '</a>');
		      showXHTML_td('width="32" align="center"',             '<a href="javascript:sort(7)">' . $MSG['section'][$sysSession->lang]    . '</a>');
		    if (QTI_which != 'questionnaire') {
		      showXHTML_td('width="80" align="center"',             '<a href="javascript:sort(8)">' . $MSG['hard_level'][$sysSession->lang] . '</a>');
		   	}
		    showXHTML_tr_E();

	$i = (($cur_page-1)*$rows_page) + 1;
	while(!$RS->EOF){
		$col = $col == 'class="bg03 font01"' ? 'class="bg04 font01"' : 'class="bg03 font01"';
			// Bug#1454 要顯示題目的全部文字 -Begin by Small 2006/10/02
			$topic = '';
			$topicError = false;
			$editTopic = '<a href="javascript:;" onclick="edit_item(this); return false;" class="cssAnchor">%s</a>';
			if (strstr($RS->fields['content'], 'xmlns')) {
				
				$RS->fields['content'] = str_replace('&nbsp;','',$RS->fields['content']);
				$dom = @domxml_open_mem(preg_replace('/xmlns="[^"]+"/', '', $RS->fields['content']));
				if ($dom) {
					$ctx = xpath_new_context($dom);
					$ret = $ctx->xpath_eval('/item/presentation//mattext');
					$nodes = is_array($ret->nodeset) ? $ret->nodeset : array(null);

					switch ($RS->fields['type']) {
						case 4://題型為填充題的話
							$topic = '';
							foreach ($nodes as $node) {
								$topic .= getNodeContent($node);//取節點(/item/presentation//mattext)裡的最底層文字
								$n = $node->parent_node();//到父節點
								$n = $n->next_sibling();//到旁節點
								if (is_object($n) && $n->node_name() == 'response_str') {
									$topic .= getFillContent($n);//'response_str->文字填充
								}
							}
						break;
						default:
							$topic = getNodeContent($nodes[0]);//取節點裡的最底層文字
						break;
					}
					$topic = sprintf($editTopic, '[' . strip_tags($topic) . ']');
				} else {
					$topic = sprintf($MSG['msg_item_parse_error'][$sysSession->lang], strip_tags($RS->fields['title']));
					$topicError = true;
				}
			} else {
				$topic = sprintf($editTopic, strip_tags($RS->fields['title']));
			}
			// Bug#1454 要顯示題目的全部文字 -End
		    showXHTML_tr_B($col);
		      showXHTML_td_B('align="center"');
				if ($topicError) {
					showXHTML_input('button', '', $MSG['remove'][$sysSession->lang] , '', 'class="cssBtn" onclick="processDel(\'' . $RS->fields['ident'] . '\');"');
				} else {
					showXHTML_input('checkbox', 'sel[]', $RS->fields['ident'], '', 'onclick="checkWhetherAll(this);"');
				}
		      showXHTML_td_E();
		      showXHTML_td('width="40" align="center"', $i++);
		      showXHTML_td('width="60" align="center"', $type[$RS->fields['type']]);
		      showXHTML_td('width='.(QTI_which!='questionnaire'?'"380"':'"460"'), $topic); // $RS->fields['title']
		      showXHTML_td('width="32" align="center"', $RS->fields['version']);
		      showXHTML_td('width="32" align="center"', $RS->fields['volume']);
		      showXHTML_td('width="32" align="center"', $RS->fields['chapter']);
		      showXHTML_td('width="32" align="center"', $RS->fields['paragraph']);
		      showXHTML_td('width="32" align="center"', $RS->fields['section']);
		    if (QTI_which != 'questionnaire') {
		      showXHTML_td('width="80" align="center"', $level[$RS->fields['level']]);
		    }
		    showXHTML_tr_E();

		$RS->MoveNext();
	}
		    showXHTML_tr_B('class="bg03"');
		      showXHTML_td('width="80" ', '&nbsp;');
		      showXHTML_td('width="640" colspan='.(QTI_which!='questionnaire'?'"9"':'"8"'), '&nbsp;');
		    showXHTML_tr_E();
		  showXHTML_table_E(); // t2
		showXHTML_form_E();

// tab2 匯入題庫 Begin
if ($isOverQuestionNumber)
{
    showXHTML_form_B(' style="display: none"', 'form2');
    showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="760" style="border-collapse: collapse" class="box01"');
    $msg = str_replace('%questions_limit%',CourseQuestionsLimit,$MSG['msg_overQuestionLimit'][$sysSession->lang]);
	list($admin_email) = dbGetStSr(sysDBname.'.WM_school','school_mail',"school_id='{$sysSession->school_id}'", ADODB_FETCH_NUM);
    $msg = str_replace('%admin_email%','mailto:'.$admin_email, $msg);
    showXHTML_tr_B('class="cssTrEvn"');
    showXHTML_td('align="left"', $msg);
    showXHTML_tr_E();
    showXHTML_table_E();
    showXHTML_form_E();
}else{
    showXHTML_form_B('action="item_import.php?' . $_SERVER['QUERY_STRING'] . '" method="POST" enctype="multipart/form-data" onsubmit="return (this.elements[0].value ? true : false);" style="display: none"', 'form2');
    showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="760" style="border-collapse: collapse" class="box01"');
    // 檔案
    showXHTML_tr_B('class="cssTrEvn"');
    showXHTML_td('align="right"', $MSG['files'][$sysSession->lang]);
    showXHTML_td_B();
    showXHTML_input('file', 'import_file', '', '', 'size="30" class="cssInput"');
    showXHTML_td_E();
    showXHTML_td('', $MSG['msg_file_import'][$sysSession->lang]);
    showXHTML_tr_E();

    // 檔案格式 (CVS or QII_XML)
    showXHTML_tr_B('class="cssTrOdd"');
    showXHTML_td('align="right"', $MSG['formats'][$sysSession->lang]);
    showXHTML_td_B();
    showXHTML_input('select', 'format', array(1 => 'QTI_XML',
    										  2 => 'CSV'  /*, 3 => 'HTML_TABLE' */
											 ), '', 'class="cssInput" style="width: 158px" onchange="document.getElementById(\'file_format\').disabled = this.value == 1 ? true : false; document.getElementById(\'csvPanel\').style.display = this.value == 1 ? \'none\' : \'\';"');
                                             
	// #47350 [chrome][管理者/問卷管理/題庫維護/匯入] 進入匯入畫面時，預設是XML格式卻出現了CSV的設定選項。-->屬性style重複兩次，chrome不接受
    showXHTML_table_B('id="csvPanel" border="0" cellpadding="3" cellspacing="1" width="100%" style="border-collapse: collapse;display: none" class="box01"');
      showXHTML_tr_B('class="cssTrEvn"');
        showXHTML_td('', $MSG['Item field separator'][$sysSession->lang]);
        showXHTML_td_B('nowrap');
          showXHTML_input('radio', 'item_separator', array(0 =>  $MSG['question_default_separator_ans'][$sysSession->lang],//$MSG['default_separator_item'][$sysSession->lang],
                                                           1 => $MSG['user_define_separator'][$sysSession->lang] . '<input type="text" size="5" maxlength="5" name="item_separator_customized" class="cssInput" style="display: none">'
		  												  ), 0, 'onclick="this.form[this.name + \'_customized\'].style.display= (this.value==\'1\') ? \'\' : \'none\'"', '<br>'
		                 );
        showXHTML_td_E();
        showXHTML_td('rowspan="4"', '<ul style="line-weight: 1.5em; margin-left: 1.5em">' . $MSG['user_define_separator_tips'][$sysSession->lang] . '</ul>');
      showXHTML_tr_E();
      showXHTML_tr_B('class="cssTrOdd"');
        showXHTML_td('', $MSG['answer separator'][$sysSession->lang]);
        showXHTML_td_B('nowrap');
          showXHTML_input('radio', 'ans_separator', array(0 => $MSG['default_separator_ans'][$sysSession->lang],
                                                          1 => $MSG['user_define_separator'][$sysSession->lang] . '<input type="text" size="5" maxlength="5" name="ans_separator_customized" class="cssInput" style="display: none">'
		  												 ), 0, 'onclick="this.form[this.name + \'_customized\'].style.display= (this.value==\'1\') ? \'\' : \'none\'"', '<br>'
		                 );
        showXHTML_td_E();
      showXHTML_tr_E();
      showXHTML_tr_B('class="cssTrEvn"');
        showXHTML_td('', $MSG['choices separator'][$sysSession->lang]);
        showXHTML_td_B('nowrap');
          showXHTML_input('radio', 'choice_separator', array(0 => $MSG['default_separator_choice'][$sysSession->lang],
                                                             1 => $MSG['user_define_separator'][$sysSession->lang] . '<input type="text" size="5" maxlength="5" name="choice_separator_customized" class="cssInput" style="display: none">'
		  												    ), 0, 'onclick="this.form[this.name + \'_customized\'].style.display= (this.value==\'1\') ? \'\' : \'none\'"', '<br>'
		                 );
        showXHTML_td_E();
      showXHTML_tr_E();
      showXHTML_tr_B('class="cssTrOdd"');
        showXHTML_td('', $MSG['tips and choices of matching separator'][$sysSession->lang]);
        showXHTML_td_B('nowrap');
          showXHTML_input('radio', 'match_separator', array(0 => $MSG['default_separator_matching'][$sysSession->lang],
                                                            1 => $MSG['user_define_separator'][$sysSession->lang] . '<input type="text" size="5" maxlength="5" name="match_separator_customized" class="cssInput" style="display: none">'
		  												   ), 0, 'onclick="this.form[this.name + \'_customized\'].style.display= (this.value==\'1\') ? \'\' : \'none\'"', '<br>'
		                 );
        showXHTML_td_E();
      showXHTML_tr_E();
	showXHTML_table_E();

    showXHTML_td_E();
    showXHTML_td_B();
    echo $MSG['msg_file_format'][$sysSession->lang], '<ul style="line-height: 22px; margin-left: 20px; margin-top: 0px; margin-bottom: 0px"><li><a href="/theme/default/teach/QTI_XML_format',($sysSession->lang=='Big5'?'':('_' . substr($sysSession->lang,0,2))),'.doc" target="_blank" class="cssAnchor">QTI_XML ', $MSG['format_description'][$sysSession->lang], '</a></li><li><a href="/theme/default/teach/CSV_format',($sysSession->lang=='Big5'?'':('_' . substr($sysSession->lang,0,2))),'.htm" target="_blank" class="cssAnchor">CSV ', $MSG['format_description'][$sysSession->lang], '</a></li></ul>';
    showXHTML_td_E();
    showXHTML_tr_E();

    // 檔案編碼
    showXHTML_tr_B('class="cssTrEvn"');
    showXHTML_td('align="right"', $MSG['import_format_title'][$sysSession->lang]);
    showXHTML_td_B();
    $file_type = array(
    'Big5'	 =>	$MSG['Big5'][$sysSession->lang],
    'GB2312' =>	$MSG['GB2312'][$sysSession->lang],
    'en'	 =>	$MSG['en'][$sysSession->lang],
    //	先不處理日文 'EUC-JP'	=>	$MSG['EUC-JP'][$sysSession->lang],
    'UTF-8'	 =>	$MSG['UTF-8'][$sysSession->lang],
    );
    showXHTML_input('select', 'file_format', $file_type, ($sysSession->lang == 'user_define' ? 'UTF-8' : $sysSession->lang), 'id="file_format" class="cssInput" style="width: 158px" disabled=true');
    showXHTML_td_E();
    showXHTML_td('', $MSG['import_format_help'][$sysSession->lang]);
    showXHTML_tr_E();

    // 按鈕
    showXHTML_tr_B('class="cssTrOdd"');
    showXHTML_td_B('colspan=3');
    showXHTML_input('hidden', 'ticket', $ticket);
    showXHTML_input('submit', '', $MSG['import'][$sysSession->lang], '', 'class="cssBtn"');
    showXHTML_td_E();
    showXHTML_tr_E();

    showXHTML_table_E();
    showXHTML_form_E();
}
// tab2 匯入題庫 End

	    showXHTML_form_B('method="POST" style="display:none"', 'form3');
		  showXHTML_input('hidden', 'pages', '1');
		  showXHTML_input('hidden', 'rows_page_share', (isset($rows_page_share)?$rows_page_share:-1));
	      showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="900" style="border-collapse: collapse" class="box01"');
	        showXHTML_tr_B('class="bg02 font01"');
	        if (!$isOverQuestionNumber)
            {
	          showXHTML_td('colspan="3"', $MSG['search_hint1'][$sysSession->lang]);
            }else{
              $msg = str_replace('%questions_limit%',CourseQuestionsLimit,$MSG['msg1_overQuestionLimit'][$sysSession->lang]);
              $msg = str_replace('%admin_email%','mailto:'.$admin_email, $msg);
              showXHTML_td('colspan="3"', $msg);
            }
	        showXHTML_tr_E();
		  	showXHTML_input('hidden','rows_page',0);
	        showXHTML_tr_B('class="bg03 font01"');
			  $rowspan = ($sysSession->env == 'teach') ? '4': '3';
	          showXHTML_td('align="right" rowspan="' . $rowspan . '"', $MSG['search_proviso'][$sysSession->lang]);
	          showXHTML_td_B('colspan="2"');
                    echo '<label>';
	            showXHTML_input('checkbox', 'isVersion'  , 'ON'); echo $MSG['version'][$sysSession->lang];
	            showXHTML_input('text'    , 'version'    , '', '', 'size="4" onkeyup="checkSelect(this);" class="box02"'); echo str_repeat('&nbsp', 5);
                    echo '</label>';
                    echo '<label>';
	            showXHTML_input('checkbox', 'isVolume'   , 'ON'); echo $MSG['volume'][$sysSession->lang];
	            showXHTML_input('text'    , 'volume'     , '', '', 'size="4" onkeyup="checkSelect(this);" class="box02"'); echo str_repeat('&nbsp', 5);
                    echo '</label>';
                    echo '<label>';
	            showXHTML_input('checkbox', 'isChapter'  , 'ON'); echo $MSG['chapter'][$sysSession->lang];
	            showXHTML_input('text'    , 'chapter'    , '', '', 'size="4" onkeyup="checkSelect(this);" class="box02"'); echo str_repeat('&nbsp', 5);
                    echo '</label>';
                    echo '<label>';
	            showXHTML_input('checkbox', 'isParagraph', 'ON'); echo $MSG['paragraph'][$sysSession->lang];
	            showXHTML_input('text'    , 'paragraph'  , '', '', 'size="4" onkeyup="checkSelect(this);" class="box02"'); echo str_repeat('&nbsp', 5);
                    echo '</label>';
                    echo '<label>';
	            showXHTML_input('checkbox', 'isSection'  , 'ON'); echo $MSG['section'][$sysSession->lang];
	            showXHTML_input('text'    , 'section'    , '', '', 'size="4" onkeyup="checkSelect(this);" class="box02"');
                    echo '</label>';
	          showXHTML_td_E();
	        showXHTML_tr_E();

	        showXHTML_tr_B('class="bg04 font01"');
	          showXHTML_td_B('colspan="2"');
	            showXHTML_input('checkbox', 'isType', 'ON'); echo $MSG['item_type'][$sysSession->lang];
	            showXHTML_input('select', 'type', $item_types, '', 'size="1" class="box02" onchange="checkSelect(this);"');
			if (QTI_which != 'questionnaire') {
				echo str_repeat('&nbsp', 5);
				showXHTML_input('checkbox', 'isLevel', 'ON'); echo $MSG['hard_level'][$sysSession->lang];
	            showXHTML_input('select', 'level', array(1 => $MSG['hard_level1'][$sysSession->lang],
														 2 => $MSG['hard_level2'][$sysSession->lang],
														 3 => $MSG['hard_level3'][$sysSession->lang],
														 4 => $MSG['hard_level4'][$sysSession->lang],
														 5 => $MSG['hard_level5'][$sysSession->lang]
														), '', 'size="1" class="box02" onchange="checkSelect(this);"');
			}
	          showXHTML_td_E();
	        showXHTML_tr_E();

	        showXHTML_tr_B('class="bg03 font01"');
	          showXHTML_td_B('colspan="2"');
	            showXHTML_input('checkbox', 'isFulltext', 'ON'); echo $MSG['key_words'][$sysSession->lang];
	            showXHTML_input('text', 'fulltext', $MSG['key_words_hint'][$sysSession->lang], '', 'size="30" class="box02" onfocus="this.value=\'\';" onkeyup="checkSelect(this);"');
	          showXHTML_td_E();
	        showXHTML_tr_E();

			if ($sysSession->env == 'teach') {
				showXHTML_tr_B('class="bg04 font01"');
				showXHTML_td_B('colspan="2"');
					showXHTML_input('checkbox', 'isMyShare', 'ON', '', 'id="isMyShare"'); echo $MSG['search_my_items'][$sysSession->lang];
				showXHTML_td_E();
				showXHTML_tr_E();
			}

	        showXHTML_tr_B(($sysSession->env == 'teach') ? 'class="bg03 font01"' : 'class="bg04 font01"');
	          showXHTML_td('align="right"', $MSG['search_scope'][$sysSession->lang]);
	          showXHTML_td_B('colspan="2"');
	            showXHTML_input('select', 'scope', array(1 => $MSG['search_scope1'][$sysSession->lang],
	            					   					 2 => $MSG['search_scope2'][$sysSession->lang],
	            					   					 3 => $MSG['search_scope3'][$sysSession->lang]
	            					  					), 3, 'class="box02" style="display: none"');


	            showXHTML_input('button', '', $MSG['start_search'][$sysSession->lang], '', 'class="cssBtn" onclick="search_item(true);"');
	          showXHTML_td_E();
	        showXHTML_tr_E();

		showXHTML_table_E();
	    showXHTML_form_E();

	    	showXHTML_td_E();
	    showXHTML_tr_E();
	  showXHTML_table_E(); // t1

            // CUSTOM BY tn (B)
            // 匯出選項
            $ary = array(array($MSG['export'][$sysSession->lang]));
            showXHTML_tabFrame_B($ary, 1, 'exportForm', 'exportTable', 'action="item_export.php" method="POST" style="display: inline" target="empty"', true, false);
                showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" class="cssTable"');
                    showXHTML_tr_B('class="cssTrEvn"');
                        showXHTML_td('', $MSG['choose the export format'][$sysSession->lang]);
                        showXHTML_td_B();
                            showXHTML_input('checkboxes', 'export_kinds[]', array('csv' => 'Excel (.csv)', 'xml' => 'XML (.xml)'), array('csv'), 'onclick="checkExport();"', '<br />');
                        showXHTML_td_E();
                    showXHTML_tr_E();
                    showXHTML_tr_B('class="cssTrOdd"');
                        showXHTML_td('', $MSG['download_filename'][$sysSession->lang]);
                        showXHTML_td_B();
                            showXHTML_input('text', 'download_name', 'WM_qti_items_'.mdate('YmdHisu').'.zip', '', 'maxlength="60" size="40" class="box02"');
                        showXHTML_td_E();
                    showXHTML_tr_E();
                    showXHTML_tr_B('class="cssTrEvn"');
                        showXHTML_td_B('colspan="2" align="right"');
                            showXHTML_input('hidden', 'lists', '');
                            showXHTML_input('hidden', 'gets', '');
                            showXHTML_input('hidden', 'ticket', $ticket);
                            showXHTML_input('button', '', $MSG['export'][$sysSession->lang], '', 'class="cssBtn" onclick="exportDone();"');
                            showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="document.getElementById(\'exportTable\').style.display=\'none\';"');
                        showXHTML_td_E();
                    showXHTML_tr_E();
                showXHTML_table_E();
            showXHTML_tabFrame_E();
            // CUSTOM BY tn (E)

// 搜尋結果
	  showXHTML_table_B('id="srTable" border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse; display:none" id="srTable"');
	    showXHTML_tr_B();
	      showXHTML_td_B();
	        $ary = array(array($MSG['search_result'][$sysSession->lang], 'tabsSet',  '')
	                    );
	        showXHTML_tabs($ary, 1);
	      showXHTML_td_E();
	    showXHTML_tr_E();
	    showXHTML_tr_B();
	      showXHTML_td_B('class="bg01" id="searchResult"', '&nbsp;');
	      showXHTML_td_E();
	    showXHTML_tr_E();
	  showXHTML_table_E();

	  showXHTML_form_B('action="" method="POST"', 'todoForm');
	    showXHTML_input('hidden', 'lists', '');
	    showXHTML_input('hidden', 'gets', '');
	    showXHTML_input('hidden', 'ticket', $ticket);
	  showXHTML_form_E();

	  echo '<iframe name="IFrameItemShare" style="display:none"></iframe>';
	showXHTML_body_E();
?>

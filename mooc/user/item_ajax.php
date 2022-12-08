<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                         *
	 *		Creation  : 2004/09/08                                                            *
	 *		work for  : grade property modify                                                         *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *		identifier: $Id: grade_modify3.php,v 1.1 2010/02/24 02:40:27 saly Exp $
	 *                                                                                                *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/filter_spec_char.php');
	
	$course_id = $_POST['csid'];
	$type      = $_POST['item_type'];
    $exam_type = $_POST['exam_type'];
    
    require_once(sysDocumentRoot . '/lang/' . $exam_type . '_teach.php');
	
	/*if (!isset($_POST['ticket'])) {	// 檢查 ticket 是否存在
	   die('Access denied.');
	}
	
	$course_id = intval(sysNewDecode($_POST['cid']));
	
	$ticket = md5(sysTicketSeed . $sysSession->username . $course_id . $_POST['referer']);		// 產生 ticket
	if ($ticket != $_POST['ticket']) {	// 檢查 ticket
	   die('Fake ticket.');
	}
	
	$school_id = (!empty($_SERVER["HTTP_USER_AGENT"]))?$sysSession->school_id:'10001';*/ 
	
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
	
	
	if (!function_exists('json_encode')) {

        function json_encode($val)
        {
            $json = new Services_JSON();
            return $json->encode($val);
        }

        function json_decode($val)
        {
            $json = new Services_JSON();
            return $json->decode($val);
        }
    }


    $arr_type = array('',
        $MSG['item_type1'][$sysSession->lang],
        $MSG['item_type2'][$sysSession->lang],
        $MSG['item_type3'][$sysSession->lang],
        $MSG['item_type4'][$sysSession->lang],
        $MSG['item_type5'][$sysSession->lang],
        $MSG['item_type6'][$sysSession->lang],
        $MSG['item_type7'][$sysSession->lang]
    );
	//$sysConn->debug=true;
	$fulltext = htmlspecialchars($_POST['fulltext']);
	if (strlen($_POST['fulltext'])>0)  {
		$conds = " and locate('{$fulltext}',content) ";
	} else {
		$conds = '';
	} 
	
	
	
    $RS = dbGetStMr('WM_qti_' . $exam_type . '_item',
	                'ident,title,content,type,version,volume,chapter,paragraph,section,level',
	                'course_id='.$course_id.' and type='.$type.$conds.' order by title',
	                ADODB_FETCH_ASSOC);
	$html = '';	                
	while(!$RS->EOF){
	    if (strstr($RS->fields['content'], 'xmlns')) {
					
			$RS->fields['content'] = str_replace('&nbsp;','',$RS->fields['content']);
			$dom = @domxml_open_mem(preg_replace('/xmlns="[^"]+"/', '', $RS->fields['content']));
			if ($dom) {
				$ctx = xpath_new_context($dom);
				$ret = $ctx->xpath_eval('/item/presentation//mattext');
				$nodes = is_array($ret->nodeset) ? $ret->nodeset : array(null);
	
				$topic = getNodeContent($nodes[0]);//取節點裡的最底層文字
				$topic = strip_tags($topic);
				$html .= '<div class="box">';
				$html .= '【'.$arr_type[$RS->fields['type']].'】'.$topic;
				$html .= '</div>';
			} 
	    } 
	    $RS->MoveNext();
	}	 
	
	$data['code'] = 1;
	$data['html'] = $html;
	
	$msg = json_encode($data);
	if ($msg != '') {
        echo $msg;
    }
	

	

?>

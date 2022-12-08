<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                         *
	 *		Creation  : 2003/03/31                                                            *
	 *		work for  : fetch item of a exam                                                  *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *                                                                                                *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lang/' . QTI_which . '_learn.php');
	require_once(sysDocumentRoot . '/learn/exam/qti_util.php');
	require_once(sysDocumentRoot . '/lib/exam_lib.php');
    require_once(sysDocumentRoot . '/mooc/models/course.php');

    $profile['isPhoneDevice']=true;
    if ($profile['isPhoneDevice']) {
        $MSG['score_assigned'][$sysSession->lang] .= '<BR />';
    }

	
	ignore_user_abort(true);
	set_time_limit(0);	
	
	$course_id = $sysSession->course_id;

	if (QTI_which == 'exam')
	{
		$sysSession->cur_func = '1600400200';
	}
	else if (QTI_which == 'homework')
	{
		$sysSession->cur_func = '1700400200';
	}
	else
	{
		$sysSession->cur_func = '1800300200';
	}

	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}


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
	 * 模擬 XMLDOM 之 swapNode METHOD 交換兩個子節點
	 */
	function swapNode(&$node1, &$node2){
		$newNode1 = $node1->clone_node(true);
		$newNode2 = $node2->clone_node(true);
		$node2->replace_node($newNode1);
		$node1->replace_node($newNode2);
	}

	/**
	 * 將 node 節點下的 <tag> 子節點順序弄亂
	 */
	function blockScramble($node, $tag){
		if ($node->has_child_nodes()){
			$nodes = $node->child_nodes();
			$len = count($nodes);
			$tags = array();
			$otag = array();
			for($i = 0; $i < $len; $i++){
				if ($nodes[$i]->node_type() == XML_ELEMENT_NODE) blockScramble($nodes[$i], $tag);
				if (method_exists($nodes[$i], 'tagname') && $nodes[$i]->tagname() == $tag)
				{
					$otag[] = $i;
					$tags[] = $i;
				}
			}
			$len = count($tags);
			shuffle($tags);

			for($i = 0; $i < $len; $i++)
				if ($otag[$i] != $tags[$i]){
					$nodes = $node->child_nodes();
					swapNode($nodes[$otag[$i]], $nodes[$tags[$i]]);
				}
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

	/**
	 * 模擬 XMLDOM 之 getElementById() method.
	 */
	function getElementById($node, $id){
		//if (!method_exists($node, 'node_type')) return null;
		if ($node->node_type() == XML_ELEMENT_NODE){
			if ($node->get_attribute('id') == $id)
				return $node;
			else
				foreach($node->child_nodes() as $child){
					$ret = getElementById($child, $id);
					if ($ret != null) return $ret;
				}
		}
		return null;
	}

	/**
	 * 將試卷中的 <selection_ordering> 取代掉
	 **/
	function replaceSectionOrder() {
		global $dom, $root, $ctx;

		$sos = $dom->get_elements_by_tagname('selection_ordering');
		foreach ($sos as $node) {
			$pnode = $node->parent_node();
			$result = $ctx->xpath_eval('./selection/selection_number[1]/text()', $node);
			$cnt = (count($result->nodeset) > 0) ? $result->nodeset[0]->node_value() : 0;
			if ($cnt > 0) {
				$result = $ctx->xpath_eval('./order/@order_type', $node);
				$order = (count($result->nodeset) > 0) ? $result->nodeset[0]->node_value() : 'Sequential';
				$nodes = $pnode->get_elements_by_tagname('item');
				$cnt = min($cnt, count($nodes));
				if ($order == 'Random') {
					// 亂數取題
					shuffle($nodes);
					for ($i = 0; $i < $cnt; $i++) {
						// 將要顯示的試題先忽略
						array_shift($nodes);
					}
					foreach ($nodes as $n) {
						// 移除不要顯示的試題
						$pnode->remove_child($n);
					}
				} else if ($order == 'Sequential') {
					// 依序取題
					for ($i = count($nodes) - 1; $i >= $cnt; $i--) {
						// 移除不要顯示的試題
						$pnode->remove_child($nodes[$i]);
					}
				}
			}
			// 移除 <selection_ordering> 區塊
			$pnode->remove_child($node);
		}
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
					$real_items[$k] = preg_replace(array('#<decvar[^>]*/>#isU','/<%ITEM_ID%>/'), array(sprintf('<decvar vartype="Integer" defaultval="%.2f" />', $scores[$k]),$k), $v);
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
	 * 取得 ISO8601 格式之時間日期
	 */
	function getISO8601_datetime($now=null){
		return (is_null($now))?date('Y-m-d\TH:i:s'):date('Y-m-d\TH:i:s', $now);
	}

	/**
	 * 取得下一頁該顯示的題目 (尋找 nextSibling 節點不是 <item_result> 的 <item> )
	 */
	function getNextPageItem($doc){
		$nodes = $doc->get_elements_by_tagname('item');
		if (is_array($nodes)) foreach($nodes as $item){
			$next = $item->next_sibling();
			if (is_null($next) || $next->tagname() != 'item_result' )
				return $item;
		}
		return null;
	}

	/**
	 * 在 $brother 這個 <item> 節點後增加一個 <item_result> 節點
	 */
	function appendItemResult(&$curItem){
		global $dom, $ctx;

		$next = $curItem->next_sibling();

		if ($curItem->tagname() != 'item' || (!is_null($next) && $next->tagname() == 'item_result')) return;
		$item_id = $curItem->get_attribute('ident');
		$now = getISO8601_datetime();

		$item_result = <<< EOB
<?xml version="1.0" encoding="UTF-8"?>
<item_result ident_ref="$item_id">
	<date>
		<type_label>Item Creation Time</type_label>
		<datetime>$now</datetime>
	</date>
	<duration />
EOB;

		// 把 <item_result> 要參考 <item> 的資訊備妥
		$ret = $ctx->xpath_eval("//item[@ident='$item_id']//*[starts-with(name(),'response_')]");
		for($i=0; $i<count($ret->nodeset); $i++){
			if ($ret->nodeset[$i]->tagname() == 'response_label') continue;
			$v1 = $ret->nodeset[$i]->get_attribute('ident');
			$v2 = (($tmp = $ret->nodeset[$i]->get_attribute('rcardinality')) == '') ? '' : " cardinality=\"$tmp\"";
			$ori_tmp = $tmp;
			$v3 = (($tmp = $ret->nodeset[$i]->get_attribute('rtiming'))      == '') ? '' : " timing=\"$tmp\"";
			$response_type = substr($ret->nodeset[$i]->tagname(), 9);

			// 取得題目類型
			$ret2 = $ctx->xpath_eval("//item[@ident='$item_id']//*[starts-with(name(),'response_')]/*[starts-with(name(),'render_')]");
			if (is_null($ret2)) die('it must have a &lt;render_???&gt; in &lt;response_???&gt;.');
			$v4 = substr($ret2->nodeset[0]->tagname(), 7);

			// 取得標準答案  ($everyAns 表示一題中，可能有多個答案 (複選、填空) )
			$ret3 = $ctx->xpath_eval("//item[@ident='$item_id']/resprocessing/respcondition[1]/varsubset");
			if (empty($ret3->nodeset))
			$ret3 = $ctx->xpath_eval("//item[@ident='$item_id']/resprocessing/respcondition[1]/conditionvar/varequal");

			if (is_array($ret3->nodeset))
				foreach($ret3->nodeset as $everyAns){
					$ans[$everyAns->get_attribute('respident')][] = $everyAns->get_content();
				}
			else
				$ans[$v1][$ret2->nodeset[0]->get_attribute('ident')] = '';

			if (is_array($ans))
			foreach($ans as $respident => $realAns){
				if ($respident != $v1) continue;
				$item_result .= <<< EOB
	<response ident_ref="$respident">
		<response_form{$v2} render_type="$v4"{$v3} response_type="{$response_type}">
EOB;
				foreach($realAns as $piece){
					$item_result .= '			<correct_response>' . htmlspecialchars($piece) . '</correct_response>';
				}
				$item_result .= <<< EOB
		</response_form>
		<num_attempts>1</num_attempts>
		<response_value />
	</response>
EOB;
                if ($ori_tmp == 'Single') break;
			}
			unset($ans);
		}
		$item_result .= <<< EOB
	<outcomes>
		<score varname="SCORE" vartype="Integer">
			<score_value />
			<score_min />
			<score_max />
			<score_cut />
		</score>
	</outcomes>
</item_result>
EOB;
		$newNode = domxml_open_mem(preg_replace('/>\s+</', '><', $item_result));
		$item_result_root = $newNode->document_element();
		$parent = $curItem->parent_node();

		// 先附加一個空節點
		$x = $dom->create_element('item_result');
		if (is_null($next))
			$nn = $parent->append_child($x);
		else
			$nn = $parent->insert_before($x, $next);
		if (is_null($nn)) die('cannot append item_result.');
		// 再用完整的 <item_result> 取代
		$nn->replace_node($item_result_root);
	}

	/**
	 * 將不是本頁要秀的題目(item)、區塊(section)、評量(assessment) 加上 visable='invisible' 的屬性
	 * 在 QTI_parser 中不會處裡這些區塊
	 */
	function hidUnnecessary(&$node, $start, $end, $cur_page=1) {
		global $ctx;

		//將start到end之間的節點加上item_result
		$ident = '';
		for ($i = $start; $i <= $end; $i++) {
			$ident .= '"'. $node[$i]->get_attribute('ident') . '",';
			appendItemResult($node[$i]);
		}
		// 處理題目附檔
		if ($ident != '') {
			$ident = 'ident in (' . preg_replace('/,$/', '', $ident) . ')';
			$rs = dbGetStMr('WM_qti_' . QTI_which . '_item', 'ident, attach, type', $ident, ADODB_FETCH_ASSOC);
			if ($rs)
			while ($row = $rs->FetchRow()) {
				if (preg_match('/^a:[0-9]+:{/', $row['attach']))
					$GLOBALS['attachments'][$row['ident']] = unserialize($row['attach']);
				$GLOBALS['item_types'][$row['ident']] = $row['type'];
			}
		}

		// 將所有start以前及end以後的節點的節點設定隱藏
		$xpath = '//item[@ident="' .
				 $node[$start]->get_attribute('ident') .
				 '"]/preceding::*[name()="item" or name()="section" or name()="assessment"] | //item[@ident="' .
				 $node[$end]->get_attribute('ident') .
				 '"]/following::*[name()="item" or name()="section" or name()="assessment"]';
		$ret = $ctx->xpath_eval($xpath);
		if (is_array($ret->nodeset))
			foreach($ret->nodeset as $item)
				$item->set_attribute('visable', 'invisible');

		// 當在第一頁時，而且第一個大題內沒有任何題目時，取消隱藏 (Begin)
		if ($cur_page == 1) {
			$xpath = '//questestinterop/section[1]//item';
			$ret = $ctx->xpath_eval($xpath);
			if (is_null($ret->nodeset) || (count($ret->nodeset) <= 0)) {
				$xpath = '//questestinterop/section[1]';
				$ret = $ctx->xpath_eval($xpath);
				if (is_array($ret->nodeset)) {
					foreach($ret->nodeset as $item) {
						$item->set_attribute('visable', '');
					}
				}
			}
		}
		// 當在第一頁時，而且第一個大題內沒有任何題目時，取消隱藏 (End)
	}

	/**
	 * 將 SQL 字串做特殊字跳脫
	 */
	function escape_sql($str){
		return str_replace(array('\\', '"', '#', '_'), array('\\\\', '\\"', '\\#', '\\_'), $str);
	}

	// 取得試卷額外的資訊
	function get_exam_satus() {
		global $sysConn, $sysSession;

		if (QTI_which != 'exam') return false;
		$subtime = 0;
		$cur_page = 1;
		list($do_interval) = dbGetStSr('WM_qti_' . QTI_which . '_test', 'do_interval', 'exam_id=' . $_SERVER['argv'][0], ADODB_FETCH_NUM);
		$row = dbGetStSr('WM_qti_' . QTI_which . '_result_extra', '*', "exam_id={$_SERVER['argv'][0]} and examinee='{$sysSession->username}' and time_id={$_SERVER['argv'][1]} limit 1");
		if (count($row) > 0) {
			// 修改
			$subtime  = $row['subtime'];
			$cur_page = $row['curpage'];

			$extra = unserialize($row['content']);
			$extra[] = array(time(), time(), $subtime, $cur_page); // 開始測驗時間, 結束測驗時間, 開始測驗剩餘時間, 目前所在頁數
			$contents = serialize($extra);
			dbSet(
				'WM_qti_' . QTI_which . '_result_extra',
				sprintf('content=\'%s\'', $contents),
				sprintf('exam_id="%s" AND examinee=%s AND time_id=%d', $_SERVER['argv'][0], $sysConn->qstr($sysSession->username), $_SERVER['argv'][1])
			);
		} else {
			// 新增
			list($do_interval) = dbGetStSr('WM_qti_' . QTI_which . '_test', 'do_interval', 'exam_id=' . $_SERVER['argv'][0], ADODB_FETCH_NUM);
			$subtime = $do_interval * 60;
			$extra = array();
			$extra[] = array(time(), time(), $subtime, 1); // 開始測驗時間, 結束測驗時間, 開始測驗剩餘時間, 目前所在頁數
			$contents = serialize($extra);
			dbNew(
				'WM_qti_' . QTI_which . '_result_extra',
				'exam_id, examinee, time_id, subtime, content',
				sprintf(
					'"%s", %s, %d, %d, \'%s\'',
					$_SERVER['argv'][0],
					$sysConn->qstr($sysSession->username),
					$_SERVER['argv'][1],
					$subtime,
					$contents
				)
			);
		}
		if ($do_interval == 0) {
			$subtime = -1;
		} else if ($subtime < 0) {
			$subtime = 0;
		}
		return array($subtime, $cur_page);
	}

	// 儲存試卷額外的資訊
	function save_exam_satus($curpage) {
		global $sysConn, $sysSession;

		$row = dbGetStSr('WM_qti_' . QTI_which . '_result_extra', '*', "exam_id={$_SERVER['argv'][0]} and examinee='{$sysSession->username}' and time_id={$_SERVER['argv'][1]} limit 1");
		if (count($row) > 0) {
			// 修改
			$extra   = unserialize($row['content']);  // 格式請看上面的註解
			$ary     = array_pop($extra);             // 取出最後一次進入時間
			$ary[1]  = time();                        // 目前的時間
			$subtime = $ary[2] - ($ary[1] - $ary[0]); // 計算剩餘的時間
			$ary[3]  = $curpage;                      // 儲存目前頁數
			$extra[] = $ary;                          // 儲存這次進入的時間與離開的時間
			$content = serialize($extra);             // 回存

			dbSet(
				'WM_qti_' . QTI_which . '_result_extra',
				sprintf('subtime=\'%s\', curpage=%d, content=\'%s\'', $subtime, $curpage, $content),
				sprintf('exam_id="%s" AND examinee=%s AND time_id=%d', $_SERVER['argv'][0], $sysConn->qstr($sysSession->username), $_SERVER['argv'][1])
			);
		}
	}

	// 檢查 Save Temporary，若有錯誤的資料則一併清除
	function check_save_temporary($items, $ticket) {
		global $sysConn, $sysSession, $course_id;

		$stid = $sysSession->cur_func . $course_id . $_SERVER['argv'][0] . $_SERVER['argv'][1];
		$stid .= ($_SERVER['argv'][2]) ? $_SERVER['argv'][2] : '';

		if (!is_numeric($stid)) return;

		list($content) = dbGetStSr('WM_save_temporary', 'content', sprintf('function_id="%s" AND username="%s"', $stid, $sysSession->username));
		if (empty($content)) return;

		$list = array();
		foreach ($items as $item) {
			if ($item->get_attribute('visable') == 'invisible') continue;
			$list[] = $item->get_attribute('ident');
		}

		$isError = false;

		// 檢查試題編號是否正確
		preg_match_all('/name=[\'"]?ans\[([^\[\]]+)\]\[/i', $content, $ary);
		$ary = array_unique($ary[1]);
		foreach ($ary as $val) {
			if (!in_array($val, $list)) {
				$isError = true;
				break;
			}
		}

		// 檢查 Ticket 是否正確

		if ($isError) {
			// 清除錯誤的 Temporary
			dbDel('WM_save_temporary', sprintf('function_id="%s" AND username="%s"', $stid, $sysSession->username));
		} else {
			// 修改 Ticket
			// <INPUT value=f0190450188e7f0887c2bf9e96b138 type=hidden name=ticket>
			// preg_match_all('/<input [^>]*name=[\'"]?ticket[\'"]?[^>]*>/i', $content, $ary);
			$content = preg_replace('/<input [^>]*name=[\'"]?ticket[\'"]?[^>]*>/i', '<INPUT value=' . $ticket . ' type=hidden name=ticket>', $content);
			$sysConn->Execute('update WM_save_temporary set content=? where function_id=? AND username=?', array($content, $stid, $sysSession->username));
		}
	}

	/**
	 * * * * * * * * * * * * * * * * * * * * * 主程式開始  * * * * * * * * * * * * * * * * * * * * *
	 */

	if ($_SERVER['argc'] < 5) die('5 arguments required.');	// 沒有兩個參數則執行失敗
	if (!preg_match('/^[0-9]+$/', $_SERVER['argv'][0]) ||				// 檢查 exam_id 格式
	    !preg_match('/^[0-9]+$/', $_SERVER['argv'][1]) ||				// 檢查 time_id 格式
	    !preg_match('/^[-]?[0-9]+$/', $_SERVER['argv'][2]) ||			// 檢查 page 格式
	    !preg_match('/^[a-z0-9]{32}$/i', $_SERVER['argv'][3])			// 檢查 ticket 格式
	   )
		die('Argument format incorrect.');
	$ticket = md5(sysTicketSeed . $_SERVER['argv'][0] . $_SERVER['argv'][1] . $_SERVER['argv'][2] . $_COOKIE['idx']);
	$isForTA = isset($_SERVER['argv'][4]) && $_SERVER['argv'][4] == '1';
	if ($ticket != $_SERVER['argv'][3]) die('Fake Ticket !');	// 檢查 ticket 正確與否

	$item_per_page = 0;
	$RS = dbGetStSr('WM_qti_' . QTI_which . '_result', '*', "exam_id={$_SERVER['argv'][0]} and examinee='{$sysSession->username}' and time_id={$_SERVER['argv'][1]} limit 1", ADODB_FETCH_ASSOC);

	if (empty($RS)){ // 第一次取題
		$tmpRS = dbGetStSr('WM_qti_' . QTI_which . '_test', '*', 'exam_id=' . $_SERVER['argv'][0], ADODB_FETCH_ASSOC);
		if (QTI_which == 'exam') {
			// 取已作答總次數 (有測驗但是不一定完成測驗)
			list($times) = dbGetStSr('WM_qti_' . QTI_which . '_result', 'count(*)', "exam_id={$_SERVER['argv'][0]} and examinee='{$sysSession->username}'", ADODB_FETCH_NUM);
		} else {
			$times = intval($_SERVER['argv'][1]);
		}
		if (checkExamWhetherTimeout($tmpRS, time(), max(0, $times - 1) ) && !$isForTA && $tmpRS['ctrl_timeout']!='mark')
		{
			die('<close />');	// 不在作答時間內
		}
		$item_per_page = $tmpRS['item_per_page'];
		$content       = $tmpRS['content'];
		$announce_type = $tmpRS['announce_type'];
		$item_cramble  = $tmpRS['item_cramble'];
		$random_pick   = $tmpRS['random_pick'];
		$ctrl_paging   = $tmpRS['ctrl_paging'];
		$GLOBALS['ctrl_paging'] = $ctrl_paging;
		if(strpos($content, '<wm_immediate_random_generate_qti') !== FALSE)
		{
			preg_match_all('!<(score|amount)\s+selected="true">([^<]*)</\1>!isU', $content, $ss);
			foreach($ss[1] as $k => $v) $keep[$v] = $ss[2][$k];

			if(substr_count($content, '<condition>') > 1)
			{
				preg_match_all('!<condition>(.+)</condition>!isU', $content, $re);
				$a = $re[1];
				unset($re);
			}
			else
				$a = array($content);

			$qs = array();	
			foreach($a as $context)
			{
				$immediate_random_pick = true; $irgs = $keep;
				if (preg_match_all('!<([^>]+)\s+selected="(true|false)">(.*)</[^>]+>!sU', $context, $regs))
					foreach($regs[1] as $k => $v)
					{
						if ($regs[2][$k]=='true') $irgs[$v] = $regs[3][$k];
					}

				$sqls = sprintf('(select ident from WM_qti_%s_item where course_id=%u ', QTI_which, $sysSession->course_id);
				
			    foreach (array('type', 'version', 'volume', 'chapter', 'paragraph', 'section', 'level') as $item) {
					if (isset($irgs[$item]) && ereg('^[0-9]+(,[0-9]+)*$', $irgs[$item])) {
						$sqls .= ('and ' . $item . ' in (' . $irgs[$item] . ') ');
					}
				}

				if (isset($irgs['fulltext'])){
					$fts = explode("\t", $irgs['fulltext'], 2);
					if (!empty($fts[0]))
						$sqls .= sprintf('and (content like ("%%%s%%") or content like ("%%%s%%")) ', escape_sql($fts[0]), escape_sql($fts[1]));
				}
				
			    if (count($qs) > 0) {
				    $exist_ident = implode ("','",$qs);
				    $sqls .= "and ident not in ('".$exist_ident."') ";
				}

				if(isset($irgs['num'])){
					$sqls .= sprintf('order by rand() limit %d) ', $irgs['num']);
				}else{
					$sqls .= sprintf('order by rand() limit %d) ', $irgs['amount']);
				}
				
			    $tmp_item = $sysConn->GetCol($sqls);
				if (count($tmp_item) > 0) {
				    foreach ($tmp_item as $val) {
				        array_push($qs,$val);
				    }
				}
			}
			/*if (count($a) > 1)
				$sqls = preg_replace('/ union $/', sprintf(' order by rand() limit %d', $irgs['amount']), $sqls);
			else
				$sqls = substr($sqls, 0, -7);

			$sqls = str_replace(' or content like ("%%")', '', $sqls);
			$sqls = str_replace('content like ("%%") or ', '', $sqls);
			$sqls = str_replace(' and (content like ("%%"))', '', $sqls);
			$sqls = preg_replace('/ in \(([\d]+)\)/', ' = \1', $sqls);

			$qs = $sysConn->GetCol($sqls);*/
			$content = '<?xml version="1.0" encoding="UTF-8"?><questestinterop xmlns:wm="http://www.sun.net.tw/WisdomMaster" wm:threshold_score=""><item id="' .
					   @implode('" score="" /><item id="', $qs) .
					   '" score="" /></questestinterop>';
            $irgs['amount'] = max(1, min(count($qs), abs(intval($irgs['amount']))));
			$sc = $irgs['amount'] ? (floor($irgs['score'] * 100 / $irgs['amount']) / 100) : 0;
            $o = sprintf('score="%f"', $sc);
			$content = str_replace(array('<item id="" score="" />', 'score=""'),
								   array('', $o),
								   $content
								  );
            if (($remnant = $irgs['score'] - ($sc * $irgs['amount'])) != 0)
            {
                $xx = explode($o, $content);
                $xxx = array_pop($xx);
                $xx[count($xx)-1] .= sprintf('score="%.2f"', $sc+$remnant) . $xxx;
                $content = implode($o, $xx);
            }
		}

		$item_cramble = explode(',', $item_cramble);
		$content = mb_convert_encoding($content, 'UTF-8', 'UTF-8');
                
		if(!$dom = domxml_open_mem(str_replace(' xmlns="http://www.imsglobal.org/question/qtiv1p2/qtiasiitemncdtd/ims_qtiasiv1p2.dtd"', '', $content))) {
			die('Error while parsing the document.');
		}

		$root = $dom->document_element();
		$ctx = xpath_new_context($dom);
		replaceSectionOrder();
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
//                echo '<pre>';
//                var_dump(htmlspecialchars(setEncoding($dom->dump_mem())));
//                echo '</pre>';
//                die();
                $rsCourse = new course();
                $ret = $rsCourse->transform_LATEX((setEncoding($dom->dump_mem())));
//                echo '<pre>';
//                var_dump(htmlspecialchars($ret));
//                echo '</pre>';
		$dom = @domxml_open_mem($ret);
//                echo '<pre>';
//                var_dump(mysql_escape_string(setEncoding($dom->dump_mem())));
//                echo '</pre>';
                
		$root = $dom->document_element();
		$ctx = xpath_new_context($dom);

		// 如果有設定亂數排列，則混亂題目
		if (in_array('enable', $item_cramble))
		{
			if (in_array('random_pick', $item_cramble) && intval($random_pick) > 0)
			{
				$nodes = $root->get_elements_by_tagname('item');
				$total_item = count($nodes);
				while($total_item > $random_pick)
				{
					$nodes[rand(0,$total_item-1)]->unlink_node();
					$nodes = $root->get_elements_by_tagname('item');
					$total_item = count($nodes);
				}
			}
			if (in_array('section', $item_cramble)) blockScramble($root, 'section');	// 大題隨機
			if (in_array('item',    $item_cramble)) blockScramble($root, 'item');		// 題目隨機
			// 因為附檔問題，所以選項隨機放在 QTI_transformer.php 做
		}

		// 存入資料庫
		$status = ($isForTA)? 'forTA' : 'break';
		dbNew('WM_qti_' . QTI_which . '_result', 'exam_id,examinee,time_id,status,begin_time,content',
		      // "{$_SERVER['argv'][0]},'{$sysSession->username}',{$_SERVER['argv'][1]},'break',now(),'" .
			  "{$_SERVER['argv'][0]},'{$sysSession->username}',{$_SERVER['argv'][1]},'{$status}',now(),'" .
		      mysql_escape_string(setEncoding($dom->dump_mem())) . "'");
		wmSysLog($sysSession->cur_func, $sysSession->course_id , $_SERVER['argv'][0] , 0, 'auto', $_SERVER['PHP_SELF'], QTI_which . ' start! Num of times: ' . $_SERVER['argv'][1]);
	}
	else{		// 非第一次取題
		$tmpRS = dbGetStSr('WM_qti_' . QTI_which . '_test', '*', 'exam_id=' . $_SERVER['argv'][0], ADODB_FETCH_ASSOC);
		if (QTI_which == 'exam') {
			// 取已作答總次數 (有測驗但是不一定完成測驗)
			list($times) = dbGetStSr('WM_qti_' . QTI_which . '_result', 'count(*)', "exam_id={$_SERVER['argv'][0]} and examinee='{$sysSession->username}'", ADODB_FETCH_NUM);
		} else {
			$times = intval($_SERVER['argv'][1]);
		}
		
		if($RS['status']=='break'){	
			if (checkExamWhetherTimeout($tmpRS, time(), max(0, $times - 1) ) && !$isForTA && $tmpRS['ctrl_timeout']=='auto_submit' && $_SERVER['argv'][2] != 0 )
			{
				die('<close />');	// 不在作答時間內
			}
		}
		$item_per_page = $tmpRS['item_per_page'];
		$announce_type = $tmpRS['announce_type'];
		$ctrl_paging   = $tmpRS['ctrl_paging'];
		$GLOBALS['ctrl_paging'] = $ctrl_paging;
		if(!$dom = domxml_open_mem($RS['content'])) {
			die('Error while parsing the document.');
		}
		$root = $dom->document_element();
		$ctx = xpath_new_context($dom);
	}

	define('QTI_DISPLAY_RESPONSE', true); // 顯示作答答案
	$exam_id  = $_SERVER['argv'][0];
	$time_id  = intval($_SERVER['argv'][1]);
	$examinee = $sysSession->username;
	$ticket   = md5(sysTicketSeed . $exam_id . $time_id);
	settype($item_per_page, 'integer');

	$items = $dom->get_elements_by_tagname('item');
	$total_item = count($items);
	$isPaging = true; // 判斷是否要分頁
	if (empty($item_per_page) || $item_per_page == 0) {
		$isPaging = false;
		$item_per_page = $total_item;
	}
	else
		$item_per_page = min($item_per_page, $total_item);

	$total_page = $isPaging ? count_page($dom, $item_per_page) : 1;

	// 指定跳到第幾頁
	// FLM 客製 分頁測驗時，回到離開的那一頁繼續測驗，而不是跳到下一頁測驗 (Begin)
	$subtime = -1;
	if ($isPaging && ($_SERVER['argv'][2] == 0)) {
		$start = getNextPageItem($root);
		if ($start != null) {
			$ary = get_exam_satus();
			if (!$isForTA && ($ary !== false)) {
				$subtime = $ary[0];
				if (($ary[1] <= 0) || ($ary[1] > $total_page)) {
					$cur_page = which_page($start->get_attribute('ident'), $dom, $item_per_page);
					$cur_page--;
				} else {
					$cur_page = $ary[1];
				}
				// $_SERVER['argv'][2] = intval($cur_page) - 1;
				$_SERVER['argv'][2] = intval($cur_page);
			}
		} else {
			if (!(isset($_SERVER['argv'][5]) && ($_SERVER['argv'][5] == 'over'))) {
				$ary = get_exam_satus();
				if (!$isForTA && ($ary !== false)) {
					$subtime = $ary[0];
					if (($ary[1] <= 0) || ($ary[1] > $total_page)) {
						$cur_page = $total_page;
					} else {
						$cur_page = $ary[1];
					}
					$_SERVER['argv'][2] = $cur_page;
				}
			}
		}
	}

	// FLM 客製 分頁測驗時，回到離開的那一頁繼續測驗，而不是跳到下一頁測驗 (End)

	$lst_ticket = md5(sysTicketSeed . $_SERVER['argv'][0] . intval($_SERVER['argv'][1]));

	if ($_SERVER['argv'][2] > 0)
	{
		$page = min(intval($_SERVER['argv'][2]), $total_page);

		if ($isPaging)
		{
			$cur_page = max(1, $page);

			$start    = find_first_item_of_page($cur_page, $dom, $item_per_page);
			$end      = find_last_item_by_first_item($start->get_attribute('ident'), $dom, $item_per_page);
			$curr_item = get_index($start, $items);

			// 隱藏其它不在這一頁秀的題目，並把要秀的題目加上 <item_result> 弟節點
			hidUnnecessary($items, $curr_item, get_index($end,$items), $cur_page);
		}
		else
		{
			$cur_page  	= 1;
			$curr_item 	= 0;
			$total_page = 1;
			hidUnnecessary($items, 0, $total_item-1, $cur_page);
		}

		if (!$isForTA) {
			save_exam_satus($page);
		}
		check_save_temporary($items, $lst_ticket);

		include_once('../../teach/exam/QTI_transformer.php');
		if ($sysConn->GetOne('select find_in_set("choice", item_cramble) from WM_qti_exam_test where exam_id=' . $_SERVER['argv'][0])) ob_start('cramble_choices');
		parseQuestestinterop($dom->dump_mem(false));		// 丟給 QTI_parser 產生題目的 html

		// 把隱藏屬性復原
		$xml_content = mysql_escape_string(str_replace(' visable="invisible"', '', setEncoding($dom->dump_mem())));

		// 存起來
		dbSet('WM_qti_' . QTI_which . '_result', "content='" . $xml_content . "'",
	    	  "exam_id={$_SERVER['argv'][0]} and examinee='{$sysSession->username}' and time_id=$time_id");

		printf('<span style="display: none"><span id="prevTicket">%s</span><span id="nextTicket">%s</span><span id="currentPage">%s</span><span id="curr_item">%s</span><span id="total_page">%s</span></span>',
			   md5(sysTicketSeed . $_SERVER['argv'][0] . $_SERVER['argv'][1] . ($cur_page-1) . $_COOKIE['idx']),
			   md5(sysTicketSeed . $_SERVER['argv'][0] . $_SERVER['argv'][1] . ($cur_page+1) . $_COOKIE['idx']),
			   $cur_page, $curr_item+1, $total_page
		      );

		// if ($subtime >= 0) {
			echo "\n" . '<span id="subtime" style="display: none;">' . $subtime . "</span>\n";
		// }

		wmSysLog($sysSession->cur_func, $sysSession->course_id , $_SERVER['argv'][0] , 0, 'auto', $_SERVER['PHP_SELF'], QTI_which . ' got page '.$cur_page.'. Num of times: ' . $_SERVER['argv'][1]);

		exit;
	}

	// 尋找下一題 (沒有 <item_result> 弟節點的 <item> )
	if ($_SERVER['argv'][2] >= 0)
	while(($start = getNextPageItem($root)) !== null){
		if ($isPaging)
		{
			$cur_page = which_page($start->get_attribute('ident'), $dom, $item_per_page);
			$end      = find_last_item_by_first_item($start->get_attribute('ident'), $dom, $item_per_page);
			$curr_item = get_index($start, $items);
			// 隱藏其它不在這一頁秀的題目，並把要秀的題目加上 <item_result> 弟節點
			hidUnnecessary($items, $curr_item, get_index($end,$items), $cur_page);
		}
		else
		{
			$cur_page  	= 1;
			$curr_item 	= 0;
			$total_page = 1;
			hidUnnecessary($items, 0, $total_item-1, $cur_page);
		}

		save_exam_satus($cur_page);
		check_save_temporary($items, $lst_ticket);

		include_once('../../teach/exam/QTI_transformer.php');
		if ($sysConn->GetOne('select find_in_set("choice", item_cramble) from WM_qti_exam_test where exam_id=' . $_SERVER['argv'][0])) ob_start('cramble_choices');
		parseQuestestinterop($dom->dump_mem(false));		// 丟給 QTI_parser 產生題目的 html

		// 把隱藏屬性復原
		$xml_content = mysql_escape_string(str_replace(' visable="invisible"', '', setEncoding($dom->dump_mem())));

		// 存起來
		dbSet('WM_qti_' . QTI_which . '_result', "content='" . $xml_content . "'",
		      "exam_id={$_SERVER['argv'][0]} and examinee='{$sysSession->username}' and time_id=$time_id");

		printf('<span style="display: none"><span id="prevTicket">%s</span><span id="nextTicket">%s</span><span id="currentPage">%s</span><span id="curr_item">%s</span><span id="total_page">%s</span></span>',
			   md5(sysTicketSeed . $_SERVER['argv'][0] . $_SERVER['argv'][1] . ($cur_page-1) . $_COOKIE['idx']),
			   md5(sysTicketSeed . $_SERVER['argv'][0] . $_SERVER['argv'][1] . ($cur_page+1) . $_COOKIE['idx']),
			   $cur_page, $curr_item+1, $total_page
		      );

		if ($subtime >= 0) {
			echo "\n" . '<span id="subtime" style="display: none;">' . $subtime . '</span>';
		}

		wmSysLog($sysSession->cur_func, $sysSession->course_id , $_SERVER['argv'][0] , 0, 'auto', $_SERVER['PHP_SELF'], QTI_which . ' got page '.$cur_page.'. Num of times: ' . $_SERVER['argv'][1]);

		exit;
	}

	if (!$isForTA)
	{
		// 計算得分
		define('QTI_DISPLAY_ANSWER',   true); // 是否顯示答案
		define('QTI_DISPLAY_OUTCOME',  true); // 是否顯示得分
		include_once('../../teach/exam/QTI_transformer.php');
		ob_start();
		parseQuestestinterop($dom->dump_mem(false));
		$result_html = ob_get_contents();
		ob_end_clean();
		if (preg_match_all('/<input\b[^>]*\bname="item_scores\b[^>]*\bvalue="(-?[0-9.]*)"/', $result_html, $regs))
			$total_score = array_sum($regs[1]);
		else
			$total_score = 0;

		// 判斷是否都是是非選擇題
		$ret1 = $ctx->xpath_eval('count(//item/presentation//render_choice)+count(//item/presentation//response_grp/render_extension)+count(//item/presentation//response_str/render_fib)');
		$ret2 = $ctx->xpath_eval('count(//item/presentation)');
		$ret3 = $ctx->xpath_eval('//item/presentation//response_str/render_fib[@prompt="Box"]');
		$status = (intval($ret1->value) < intval($ret2->value)) ? 'submit' : 'revised';
		if ($status=='revised' && count($ret3->nodeset)!=0)  $status = 'submit';
                if ($status=='revised') $update_score = ',score=' . $total_score . '';
        
		if (QTI_which == 'exam' && $_SERVER['argv'][6]==1) {
		    $ins_status = 'continue';
		} else {
		    $ins_status = 'submit';
		}
                
		dbSet('WM_qti_' . QTI_which . '_result',
		  	'status="' . $status . '",submit_time=now()' . $update_score . ',content=replace(content, "</questestinterop>", "<wm:submit_status>'.$ins_status.'</wm:submit_status></questestinterop>")',
	      	"exam_id={$_SERVER['argv'][0]} and examinee='{$sysSession->username}' and time_id=$time_id");
		if ($sysConn->Affected_Rows() < 1)
		{
			$sysConn->Execute('UPDATE /*! LOW_PRIORITY */ WM_qti_' . QTI_which . '_result SET status="submit",submit_time=now(),score=' . $total_score,
							  " WHERE exam_id={$_SERVER['argv'][0]} AND examinee='{$sysSession->username}' AND time_id=$time_id");
        	wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, '', $_SERVER['PHP_SELF'], 'Save Score Again : ' . $sysConn->Affected_Rows());
		}

		if ($status=='revised') {
			/*
		 	 * 復興航空客製段 : 直接將分數匯入成績系統
		 	 */
			include_once(sysDocumentRoot . '/teach/grade/grade_recal.php');
			if (reCalculateQTIGrade($sysSession->username, $_SERVER['argv'][0], QTI_which))
				reCalculateGrades($sysSession->course_id);
			/*
		  	 * 復興航空客製段 : 結束
		  	 */
		}
	}
	else // 教師試做的話，則把紀錄刪除
	{
		dbDel('WM_qti_' . QTI_which . '_result', "exam_id={$_SERVER['argv'][0]} and examinee='{$sysSession->username}' and time_id=$time_id");
	}
	wmSysLog($sysSession->cur_func, $sysSession->course_id , $_SERVER['argv'][0] , 0, 'auto', $_SERVER['PHP_SELF'], QTI_which . ' finish! Num of times: ' . $_SERVER['argv'][1]);
	die('<over />');	// 沒題目可秀，就是已經考完囉！
?>

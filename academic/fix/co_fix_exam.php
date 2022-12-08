#!/usr/local/bin/php
<?php
	/**
	 *	※ WM minutely 定時執行程式
	 *
	 * @since   2004/09/15
	 * @author  Wiseguy Liang
	 * @version $Id: cron_minutely.php,v 1.1 2010/02/24 02:38:56 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 *
	 **/

	// 系統設定
	require_once(dirname(__FILE__) . '/console_initialize.php');
	require_once(sysDocumentRoot . '/teach/grade/grade_recal.php');
	
	$keep = $_COOKIE['school_hash']; unset($_COOKIE['school_hash']);
	//$sysConn->debug=true;
	
	/**
	 * 換掉節點中的值
	 */
	function replace_node_content(&$node, $value)
	{
	    if (method_exists($node, 'has_child_nodes'))
	    {
	        if ($node->has_child_nodes())
				foreach ($node->child_nodes() as $child)
					$node->remove_child($child);

			$doc = $node->owner_document();
	        $node->append_child($doc->create_text_node($value));
		}
	}
	
	$type = 'exam';
	
	$sysDBprefix = sysDBprefix;
	
    $sysConn->Execute('use ' . $sysDBprefix.'10001');
		
	$exam_id = 100014988;//設定要修改的測驗id
	
	// 取得全部測驗結果
	$RS_user = dbGetStMr('WM_qti_' . $type . '_result',
					'examinee,time_id,content',
					'exam_id='.$exam_id.' order by examinee DESC',
					ADODB_FETCH_ASSOC);
					
	$arr_item_ans = array();
					
    if ($RS_user)
			
		while(!$RS_user->EOF){
			
	        $username = $RS_user->fields['examinee'];
	        $time_id = $RS_user->fields['time_id'];
			$content = $RS_user->fields['content'];
	
			if(!$dom = domxml_open_mem($content)) {
			    die('Error while parsing the document.');
			}
			
			$ctx = xpath_new_context($dom);
			
			$node = $ctx->xpath_eval('//item_result');	
			
			if(is_array($node->nodeset)) {
				foreach($node->nodeset as $nodes){
				    $ident_ref = $nodes->get_attribute('ident_ref');	
				    $ret = $ctx->xpath_eval("//item_result[@ident_ref='$ident_ref']/response/response_form[@render_type='choice' and @cardinality='Single']");//只抓單選來修正
				    if ($ret) {
				    	
				    	$result_ans = $ret->nodeset[0]->first_child()->get_content();
				    	
				    	// 取得題目正確答案
				    	if (!in_array($ident_ref,array_keys($arr_item_ans))) {
					    	$items = $sysConn->GetOne('select content from WM_qti_' . $type . '_item where ident="'.$ident_ref.'"');
					        if(!$dom1 = domxml_open_mem(str_replace(' xmlns="http://www.imsglobal.org/question/qtiv1p2/qtiasiitemncdtd/ims_qtiasiv1p2.dtd"', '', $items))) {
				                die('Error while parsing the document.');
				            }
				            $ctx1 = xpath_new_context($dom1);
				            $ret3 = $ctx1->xpath_eval("//item[@ident='$ident_ref']/resprocessing/respcondition/conditionvar/varequal");
				            if ($ret3) {
				                $item_ans = $ret3->nodeset[0]->get_content();
				            } else {
				            	$item_ans = '';
				            }
				            
				            $arr_item_ans[$ident_ref] = $item_ans;
				    	}
				    	
                        if ($result_ans!=$arr_item_ans[$ident_ref]) {
                        	
                        	// 修改item_result正確答案
                            if (is_object($ret->nodeset[0]->first_child()))
							{
							    $o = $ret->nodeset[0]->first_child();
							    $i = $arr_item_ans[$ident_ref];
							    replace_node_content($o, $i);
							}
                        	
							// 修改item正確答案
                            $ret4 = $ctx->xpath_eval("//item[@ident='$ident_ref']/resprocessing/respcondition/conditionvar/varequal");
                            if (is_object($ret4->nodeset[0]))
							{
							    $o = $ret4->nodeset[0];
							    $i = $arr_item_ans[$ident_ref];
							    replace_node_content($o, $i);
							}
                        	
                        	// 修改分數
                        	$ret2 = $ctx->xpath_eval("//item_result[@ident_ref='$ident_ref']/response/response_value");
				    	    $user_ans = $ret2->nodeset[0]->get_content();
				    	    if ($user_ans == $arr_item_ans[$ident_ref]) {
				    	    	$score_node = $ctx->xpath_eval("//item[@ident='$ident_ref']/resprocessing/outcomes/decvar");
				    	    	$score = $score_node->nodeset[0]->get_attribute('defaultval');
				    	        $score_node2 = $ctx->xpath_eval("//item_result[@ident_ref='$ident_ref']/outcomes/score/score_value");
					    	    if (is_object($score_node2->nodeset[0]))
								{
								    $o = $score_node2->nodeset[0];
								    $i = $score;
								    replace_node_content($o, $i);
								}                  	
				    	    }

                        }
			            
				    }
				    
				}
				
				$newcontent = $dom->dump_mem();
				//print($newcontent);
				dbSet('WM_qti_' . $type . '_result', "content='" . mysql_escape_string($newcontent) . "'",'exam_id='.$exam_id.' and time_id='.$time_id.' and examinee="'.$username.'"');
				define('QTI_DISPLAY_RESPONSE', true); // 顯示作答答案
				define('QTI_DISPLAY_ANSWER',   true); // 是否顯示答案
				define('QTI_DISPLAY_OUTCOME',  true); // 是否顯示得分
				include_once(sysDocumentRoot . '/teach/exam/QTI_transformer.php');
				ob_start();
				parseQuestestinterop($dom->dump_mem(false));
				$result_html = ob_get_contents();
				ob_end_clean();
							
				if (preg_match_all('/<input\b[^>]*\bname="item_scores\b[^>]*\bvalue="(-?[0-9.]*)"/', $result_html, $regs))
					$total_score = array_sum($regs[1]);
				else
					$total_score = 0;
					
					
				dbSet('WM_qti_' . $type . '_result',
				' score=' . $total_score ,"exam_id={$exam_id} and time_id={$time_id} and examinee='{$username}'");	
				
				reCalculateQTIGrade($username,$exam_id,$type);
			}

			$RS_user->MoveNext();
		}


?>

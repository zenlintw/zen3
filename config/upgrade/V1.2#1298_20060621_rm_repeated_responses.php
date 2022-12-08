<?php
	/**
	 *  #1298 修正程式 (修正修改答案時，沒把前一次答案清掉)
	 *
	 *  $Id: V1.2#1298_20060621_rm_repeated_responses.php,v 1.1 2010/02/24 02:38:56 saly Exp $
	 */
    set_time_limit(0);
	require_once('/home/wm3/config/sys_config.php');
	require_once('/home/wm3/lib/adodb/adodb.inc.php');
	$sysConn = &ADONewConnection(sysDBtype);
	$sysConn->Connect(sysDBhost, sysDBaccoount, sysDBpassword, 'WM_10001') OR die('connecting failure.');


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


	function fix_item()
	{
	    global $sysConn, $fields, $ADODB_FETCH_MODE;
	
	    $modify_flag = false;
		$dom = domxml_open_mem($fields['content']);
		$ctx = xpath_new_context($dom);

		// 修正複選題
		$ret = $ctx->xpath_eval('//item_result[response/response_form[@cardinality="Multiple" and @render_type="choice"] and count(date/type_label[.="submit answer"]) > 1]');
		if (is_array($ret->nodeset) && count($ret->nodeset))
		{
		    foreach ($ret->nodeset as $item_result)
		    {
		        $response_values = array();
		        $responses = $ctx->xpath_eval('./response/response_value', $item_result);
                if (is_array($responses->nodeset) && count($responses->nodeset) > 1)
                {
                    $parent = $responses->nodeset[0]->parent_node();
                    for ($i=count($responses->nodeset)-1; $i>=0; $i--)
					{
					    $response_value = $responses->nodeset[$i];
					    $v = $response_value->get_content();
						if (in_array($v, $response_values))
						{
							$parent->remove_child($response_value);
							$modify_flag = true;
						}
						else
						{
						    $response_values[] = $v;
						}
					}
				}
				unset($parent, $v, $response_values);
			}
		}

		// 修正配合題
		$ret = $ctx->xpath_eval('//item_result[response/response_form[@cardinality="Multiple" and @render_type="extension"] and count(date/type_label[.="submit answer"]) > 1]');
		if (is_array($ret->nodeset) && count($ret->nodeset))
		{
		    foreach ($ret->nodeset as $item_result)
		    {
				$cr = $ctx->xpath_eval('count(./response/response_form/correct_response)', $item_result);
				$correct_response_amount = (int)$cr->value;
				$cr = $ctx->xpath_eval('count(./response/response_value)', $item_result);
				$response_value_amount = (int)$cr->value;

				if ($correct_response_amount < $response_value_amount)
				{
				    $modify_flag = true;
				    $rv = $ctx->xpath_eval('./response/response_value[position() > 1 and position() < ' . ($correct_response_amount + 1) . ']', $item_result);
				    $parent = $rv->nodeset[0]->parent_node();
				    foreach ($rv->nodeset as $child)
				        $parent->remove_child($child);
				}
				unset($cr, $correct_response_amount, $response_value_amount, $rv, $parent);
			}
		}

		if ($modify_flag)
		{
			$content = addslashes($dom->dump_mem());

			$sysConn->Execute("update WM_qti_exam_result set content='{$content}' where exam_id={$fields['exam_id']} and examinee='{$fields['examinee']}' and time_id={$fields['time_id']} limit 1");
			echo "Modify : exam_id={$fields['exam_id']} and examinee='{$fields['examinee']}' and time_id={$fields['time_id']} -> ", ($sysConn->Affected_Rows() ? 'OK' : 'Fault'), "<br>\r\n";
		}

		// unset($dom, $ctx, $ret, $item_result, $content);
	}


	$sqls = 'select exam_id,examinee,time_id,content from WM_qti_exam_result';
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	$rs = $sysConn->Execute($sqls);
	if ($rs)
	    while ($fields = $rs->FetchRow())
	    {
            fix_item();
		}
	else
	    die('no record');

    $sysConn->Close();
?>
over

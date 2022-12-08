<?php
	/**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                                 *
	*                                                                                                 *
	*		Programmer: Amm Lee                                                                         *
	*		Creation  : 2003/09/23                                                                      *
	*		work for  : 教材查詢                                                                        *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                          *
	*       $Id: content_query1.php,v 1.1 2010/02/24 02:38:19 saly Exp $                                                                                          *
	**************************************************************************************************/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/content_manage.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	
	$sysSession->cur_func='2400100400';
	$sysSession->restore();
	if (!aclVerifyPermission(2400100400, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable'))){

	}

	header("Content-type: text/xml");
	// 這邊的判斷可能會因為 PHP 版本的更改而有所變動
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if($dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA']))
		{
			$gpName    = intval(getNodeValue($dom, 'gpName'));
			$kind      = getNodeValue($dom, 'kind');
			if (!in_array($kind, array('none', 'digitization', 'traditional'))) $kind = 'none';
			$searchkey = getNodeValue($dom, 'searchkey');
			$keyword   = getNodeValue($dom, 'keyword');
			$page_no   = intval(getNodeValue($dom, 'page_serial'));
			$page_num  = intval(getNodeValue($dom, 'page_num'));
			
			$data = array();

			//  從第幾筆開始抓資料
			$limit_begin = (($page_no-1)*$page_num);
			if ($page_no > 0){
				$limit_str = ' limit ' . $limit_begin . ',' . $page_num;
			}
			if ($gpName != 100000){
				$table_alis = 'T2.';
			}

			$where[] = " {$table_alis}content_id>100000 and {$table_alis}kind = 'content' ";
			if ($searchkey == 'content_id')
			{
				$where[] = " {$table_alis}content_sn like '%" . escape_LIKE_query_str(addslashes($keyword)) . "%' ";
			}else{
				$content_ids = serialized_search($keyword, 'WM_content', 'content_id,caption');
				if(count($content_ids) > 0) {
					$where[] = " {$table_alis}content_id in (" . implode(',', array_keys($content_ids)) . ')';
				} else {
					$where[] = " {$table_alis}content_id =''";
				}
			}

			if ($kind != 'none')
			{
				$where[] = " {$table_alis}content_type='{$kind}' ";
			}

			if (count($where)>0)
			{
				$wherestr = implode(' and ',$where);
			}else{
				$wherestr = 1;
			}

			if ($gpName == 100000){
				$RS = dbGetStMr('WM_content', '*', "{$wherestr} {$limit_str}", ADODB_FETCH_ASSOC);

	            if ($RS)
	            {
	            	while (!$RS->EOF)
	            	{
	                	$data[] = $RS->fields;
	                 	$RS->MoveNext();
	                 }
	            }
				list($total_row) = dbGetStSr('WM_content', 'count(*) as cnt', "{$wherestr}", ADODB_FETCH_NUM);
	        }else{
				$sqls = 'select T2.* from WM_content_group T1 inner join ' .
        			    'WM_content T2 on T2.content_id=T1.child  ' .
        			    'where T1.parent=' . $gpName .
        			    " and " . $wherestr;

                $sysConn->SetFetchMode(ADODB_FETCH_ASSOC);
				if ($page_no > 0){
					$RS = $sysConn->SelectLimit($sqls,$page_num,$limit_begin);
				}
				else {
					$RS = $sysConn->Execute($sqls);
				}

				if ($RS)
	            	while (!$RS->EOF) {
	                	$data[] = $RS->fields;
	                	$RS->MoveNext();
	            	}
	            	
	            $total_row = count($data); 	

	    	}

			$cnt = count($data);

			$result = '';

			for ($i = 0; $i < $cnt; $i++)
			{

				$Caption = getCaption($data[$i]['caption']);
				$Content_Type_Desc = ($data[$i]['content_type']=='traditional') ? $MSG['state_traditional'][$sysSession->lang] : $MSG['state_digital'][$sysSession->lang];
				if (empty($Caption)) continue;

				$result .= <<< BOF
	<content id="{$data[$i]['content_id']}" checked="false">
		<content_id>{$data[$i]['content_id']}</content_id>
		<caption>{$Caption[$sysSession->lang]}</caption>
		<content_sn>{$data[$i]['content_sn']}</content_sn>
		<content_type>{$data[$i]['content_type']}</content_type>
		<content_type_desc>{$Content_Type_Desc}</content_type_desc>
		<content_form>{$data[$i]['content_form']}</content_form>
		<content_note>{$data[$i]['content_note']}</content_note>
	</content>
BOF;
			}

			$result = '<' . '?xml version="1.0" encoding="UTF-8" ?' . ">\n" .
        	          '<manifest>' .
        	          '<total_row>' . $total_row . '</total_row>' .
        	          $result .
        	          '</manifest>';
			echo $result;

        }else{
            echo "</manifest>\n";
		    exit();
        }

	} else {
		echo "</manifest>\n";
		exit();
	}

?>

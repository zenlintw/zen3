<?php
    /**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                                 *
	*                                                                                                 *
	*		Programmer: Amm Lee                                                                         *
	*		Creation  : 2003/09/30                                                                       *
	*		work for  : 班級群組管理                                                                      *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                          *
	*       $Id: content_package_get.php,v 1.1 2010/02/24 02:38:16 saly Exp $                                                                                          *
	**************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/content_manage.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	$sysSession->cur_func='2400100400';
	$sysSession->restore();
	if (!aclVerifyPermission(2400100400, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable'))){

	}

	// 安全性檢查

	/**
	 * 建立 班級的 XML 檔 (class_id = 100000)
	 * @pram $val
	 *  ( 姓名,帳號,性別 身份)
	 *
	 **/
	function buildClassXML($val) {
	    global $MSG,$sysSession;

		if (!is_array($val) && !is_array($val[0])) return '';

		$result = '';
		$cnt = count($val);
		// 輸出班級的 XML (Begin)
		for ($i = 0; $i < $cnt; $i++) { // Begin for ($i = 0; $i < $cnt; $i++)

			// 輸出 XML

			$Caption = getCaption($val[$i]['caption']);
			$locale_caption = is_array($Caption) ? $Caption[$sysSession->lang] : $val[$i]['caption'];
			if ($locale_caption == '' && is_array($Caption)) {
				$tmp = explode(chr(9), trim(implode(chr(9), $Caption)));
				$locale_caption = $tmp[0];
			}
			$locale_caption = htmlspecialchars($locale_caption, ENT_NOQUOTES);
			$val[$i]['content_form'] = htmlspecialchars($val[$i]['content_form'], ENT_NOQUOTES);
			$val[$i]['content_note'] = htmlspecialchars($val[$i]['content_note'], ENT_NOQUOTES);

			$Content_Type_Desc = ($val[$i]['content_type']=='traditional') ? $MSG['state_traditional'][$sysSession->lang] : $MSG['state_digital'][$sysSession->lang];

			if (empty($Caption)) continue;

			$result .= <<< BOF
	<content id="{$val[$i]['content_id']}" checked="false">
		<content_id>{$val[$i]['content_id']}</content_id>
		<caption>{$locale_caption}</caption>
		<content_sn>{$val[$i]['content_sn']}</content_sn>
		<content_type>{$val[$i]['content_type']}</content_type>
		<content_type_desc>{$Content_Type_Desc}</content_type_desc>
		<content_form>{$val[$i]['content_form']}</content_form>
		<content_note>{$val[$i]['content_note']}</content_note>
	</content>
BOF;
		} // End for ($i = 0; $i < $cnt; $i++)
		// 輸出課程的 XML (End)
		return $result;
	}


/**
 * ========================================================================================
 *                                     主程式開始
 * ========================================================================================

查詢的 XML

< ?xml version="1.0" encoding="UTF-8" ? >
<manifest>
	<ticket></ticket>
	<contents_id></contents_id>     <- 查詢的 位在那個節點
	<page_serial></page_serial>   <- 第幾頁
	<page_num></page_num> <- 一頁顯示幾筆
	<sby1></sby1>   <- 排序欄位
</manifest>

**/

	// 這邊的判斷可能會因為 PHP 版本的更改而有所變動
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if (!$dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			header("Content-type: text/xml");
			echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
			echo '<manifest></manifest>';
			exit;
		}
		$data = array();

		// 群組編號
        $group_id = intval(getNodeValue($dom, 'contents_id'));

		// 第幾頁
        $page_no = intval(getNodeValue($dom, 'page_serial'));

		// 一頁顯示幾筆
        $page_num = intval(getNodeValue($dom, 'page_num'));

		//  從第幾筆開始抓資料
		$limit_begin = (($page_no-1)*$page_num);

		if ($page_no > 0){
			$limit_str = ' limit ' . $limit_begin . ',' . $page_num;
		}

		// 排序欄位
        // $sby1 = getNodeValue($dom, 'sby1');

        if ($group_id > 100000) {
        	$sqls = "select T2.* from WM_content_group T1 inner join
        			 WM_content T2 on T2.content_id=T1.child where T1.parent={$group_id} and T2.kind='content' order by content_id";

			// 抓出總筆數
			$sysConn->SetFetchMode(ADODB_FETCH_ASSOC);
            $RS = $sysConn->Execute($sqls);
            if ($RS){
				$total_row = $RS->RecordCount();
            }else{
            	$total_row = 0;
        	}

			if ($page_no > 0){
				$RS = $sysConn->SelectLimit($sqls,$page_num,$limit_begin);
			}

			if ($RS){
                while (!$RS->EOF) {
                    $data[] = $RS->fields;
                    $RS->MoveNext();
                }

	            $result = '<' . '?xml version="1.0" encoding="UTF-8" ?' . ">\n" .
	        	          '<manifest>' .
	        	          '<total_row>' . $total_row . '</total_row>' .
	        	          buildClassXML($data) .
	        	          '</manifest>';
            }

        }else{
			list($total_row) = dbGetStSr('WM_content', 'count(*)', "kind='content' and content_id>100000", ADODB_FETCH_NUM);	// 取得總筆數

            // 取得全校所有教材的資料
            $RS = dbGetStMr('WM_content', '*', "kind='content' and content_id>100000 order by content_id {$limit_str}", ADODB_FETCH_ASSOC);

            if ($RS){
                while (!$RS->EOF) {
                    $data[] = $RS->fields;
                    $RS->MoveNext();
                }

                $result = '<' . '?xml version="1.0" encoding="UTF-8" ?' . ">\n" .
        		          '<manifest>' .
        		          '<total_row>' . $total_row . '</total_row>' .
        		          buildClassXML($data) .
        		          '</manifest>';

            }
		}

		header("Content-type: text/xml");
		if (!empty($result)) {
			echo $result;
		} else {
			echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . ">\n";
			echo '<manifest />';
		}
	}
?>

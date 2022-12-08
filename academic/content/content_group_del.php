<?php
	/**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                                 *
	*                                                                                                 *
	*		Creation  : 2005/04/04                                                                      *
	*		work for  : 刪除教材類別                                                                      *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                          *
	*       $Id: content_group_del.php,v 1.1 2010/02/24 02:38:16 saly Exp $                                                                                          *
	**************************************************************************************************/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/content_lang.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');

	$sysSession->cur_func='2400100300';
	$sysSession->restore();
	if (!aclVerifyPermission(2400100300, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable'))){

	}

	$del_cids = array();
	header("Content-type: text/xml");
	// 這邊的判斷可能會因為 PHP 版本的更改而有所變動
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if($dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA']))
		{
			// 取出教材類別代碼
			$content_ids = getNodeValue($dom, 'content_id');
			$content_id  = explode(',', $content_ids);
			$num = count($content_id);
            $msg = '';
			for($i=0;$i < $num;$i++)
			{
			    $content_id2 = explode('@',$content_id[$i]);
			    $content_id2[0] = intval($content_id2[0]);
			    $content_id2[1] = intval($content_id2[1]);
    		    // 教材類別名稱 & discuss & bulletin
    			list($v1) = dbGetStSr('WM_content','caption','content_id=' . $content_id2[1], ADODB_FETCH_NUM);
    			$lang = getCaption($v1);	// 使用getCaption將caption htmlspecialchars, 以免造成xml錯誤
                $content_name = $lang[$sysSession->lang];

    			// 判斷 此 教材類別 底下  是否 有 子節點 (begin)
                list($child) = dbGetStSr('WM_content_group','child','parent=' . $content_id2[1], ADODB_FETCH_NUM);
                if ($child == 0){
                    $del_cids[] = $content_id2[1];

					list($v3) = dbGetStSr('WM_content_group','count(*)','child=' . $content_id2[1], ADODB_FETCH_NUM);
					if ($v3 == 1) { // 如果 只有 隸屬在 1 個 子節點
					    dbDel('WM_content_group', 'parent=' . $content_id2[1] . ' and child=0');
					    dbSet('WM_content_group', 'child=0', 'parent=' . $content_id2[0] . ' and child=' . $content_id2[1]);
					}else{
					    // 只刪除 勾選 要刪除的 節點
					    dbDel('WM_content_group', 'parent=' . $content_id2[0] . ' and child=' . $content_id2[1]);
					}
					$msg .= '[ ' . $content_name . ' ] '  . $MSG['title79'][$sysSession->lang] . "\r\n";
                }else{
                    $msg .= '[ ' . $content_name . ' ] '  . $MSG['title77'][$sysSession->lang] . "\r\n";
                }
                // 判斷 此 教材類別 底下  是否 有 子節點 (end)
            }

            if (count($del_cids))
                dbDel('WM_content', 'content_id in('.implode(',', $del_cids).')');

		    echo '<manifest><result>', $msg, '</result></manifest>';
        }else{
		    echo '<manifest><result>1</result></manifest>';
        }

	} else {
		echo '<manifest><result>1</result></manifest>';
	}

?>

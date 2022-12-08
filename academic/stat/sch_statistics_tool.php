<?php
	/**
	 * 學校統計資料 - 選單工具列
	 * 建立日期：2004/08/16
	 * @author  Amm Lee
	 * @version $Id: sch_statistics_tool.php,v 1.1 2010/02/24 02:38:43 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/wm_toolbar.php');
	require_once(sysDocumentRoot . '/lang/sch_statistics.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	
	$sysSession->cur_func = '1500200100';
	$sysSession->restore();
	if (!aclVerifyPermission(1500200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	
	
    function configRead($file) {
        if (file_exists($file))
        {
            $fp = fopen($file, 'r');
            // 讀出整個檔案內容
            $dec_content = fread($fp,filesize($file));
            // 解開編碼
            $org_content = other_dec($dec_content);
            $temp_array  = explode("\r\n", $org_content);
            if (is_array($temp_array))
            {
                $temp_count = count($temp_array);
                for ($i = 0; $i < $temp_count; $i++)
                {
                    $item = explode('@', trim($temp_array[$i]));

                    // $item[0] 欄位名稱
                    // $item[1] 欄位值
                    if (strpos($item[1], '(at)') !== false) $item[1] = str_replace('(at)', '@', $item[1]);
                    if ($item[0] == 'sysAvailableChars')
                        $Data[$item[0]] = explode(',', $item[1]);
                    else
                        $Data[$item[0]]= $item[1];
                }
            }
            fclose($fp);
            return $Data;
        }
    }

    // 取得目前學校常數
    $fname = sysDocumentRoot . '/base/' .$sysSession->school_id. '/config.txt';
    $Da = configRead($fname);
	
	$btns = array(
			array($MSG['title2'][$sysSession->lang]  , 'icon_new.gif', 'if(parent.main.do_fun)parent.main.do_fun(1);'),
			array($MSG['title3'][$sysSession->lang]  , 'icon_new.gif', 'if(parent.main.do_fun)parent.main.do_fun(2);'),
			array($MSG['title4'][$sysSession->lang]  , 'icon_new.gif', 'if(parent.main.do_fun)parent.main.do_fun(3);'),
//			array($MSG['title6'][$sysSession->lang]  , 'icon_new.gif', 'if(parent.main.do_fun)parent.main.do_fun(8);'),
                        array($MSG['title5'][$sysSession->lang]  , 'icon_new.gif', 'if(parent.main.do_fun)parent.main.do_fun(4);'),
			array($MSG['title101'][$sysSession->lang], 'icon_new.gif', 'if(parent.main.do_fun)parent.main.do_fun(5);'),
			array($MSG['title102'][$sysSession->lang], 'icon_new.gif', 'if(parent.main.do_fun)parent.main.do_fun(6);'),
			array($MSG['title158'][$sysSession->lang], 'icon_new.gif', 'if(parent.main.do_fun)parent.main.do_fun(7);'),
		);
		if($Da['sysEnableAppISunFuDon']){
			$btns[]=array($MSG['title159_1'][$sysSession->lang], 'icon_new.gif', 'if(parent.main.do_fun)parent.main.do_fun(9);');
			$btns[]=array($MSG['title159_2'][$sysSession->lang], 'icon_new.gif', 'if(parent.main.do_fun)parent.main.do_fun(10);');
		
		}
	showXHTML_toolbar('&nbsp;&nbsp;&nbsp;' . $MSG['title'][$sysSession->lang], '', $btns, $js, false, '', '', false); //, $showIcon=true, $headTitle='')
?>
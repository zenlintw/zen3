<?php
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
    require_once(sysDocumentRoot . '/mooc/common/common.php');
    require_once(sysDocumentRoot . '/lang/co_download_manage.php');
    
    /* 將夾檔字串轉為 Link
     * input $attach 以 Tab 隔開的夾檔字串
     * return: 一串 Link (在列表時不分行，在單一POST裡會分行)
     */
    function dl_generate_attach_link($attach) {
        return dl_generate_attach_del($attach, '', false);
    }

    /* 產生刪除夾檔的列表。
     * input : $attach 以 Tab 隔開的夾檔字串
     * return: From 字串。有 checkbox
     */
    function dl_generate_attach_del($attach, $msg='', $forDel=true) {
        global $sysSession;
        if (empty($attach)) return null;
        $type = array('avi','bmp','doc','gif','htm','html','jpg','mp3','pdf','ppt','txt','wav','xls','zip');

        //$uri = substr($pre, strlen(sysDocumentRoot));
        $a = explode(chr(9), trim($attach));
        $r = '';
        for($i=0; $i<count($a); $i++){
            if ($forDel) $r .= $msg . '<input type="checkbox" name="delAttach[]" value="' . $a[$i] . '" />&nbsp;';
            //$icon = '<img border="0" align="absmiddle" src="/theme/' . $sysSession->theme . '/filetype/' .
            //        ((($ext = strtolower(substr(strrchr($a[$i+1], '.'), 1))) && in_array($ext, $type))?
            //    $ext : 'default') . '.gif"'. ' alt="'.$a[$i].'" />';
            $icon = '<img border="0" align="absmiddle" src="/theme/' . $sysSession->theme . '/filetype/' .
                    ((($ext = strtolower(substr(strrchr($a[$i], '.'), 1))) && in_array($ext, $type))?
                $ext : 'default') . '.gif"'. (' alt="'.$a[$i].'" />');
            if (strrchr($a[$i], '.') != '.awp') {
                $filename = substr($a[$i],strpos($a[$i],'/base/'));
                $r .= '<a href="' . $filename .'" target="_blank" class="cssAnchor" download>'. $icon .'</a>'.basename($filename) .' <br/><span class="font01">(' . number_format(@filesize(sysDocumentRoot . $filename), 0, '.', ',') . ' <span style="font-size: 8pt; font-family: Arial Narrow; color: gray">bytes</span>)</span><br />';
            } else {
                $r .= '<a href="javascript:;" onClick="loadwb(\''.$pre . DIRECTORY_SEPARATOR . $a[$i] . '\'); return false;" class="cssAnchor">'.$icon.'</a>'.
                      '<input type="hidden" id="awppath" name="awppath" value="'.$pre . DIRECTORY_SEPARATOR . $a[$i].'">' . '';
            }
        }
        return $r;
    }
  	($page_no = intval($_POST['page'])) == 0 && $page_no = 1;
  	$keyword = empty($_POST['keyword']) ? '' : addslashes($_POST['keyword']);
    if($keyword != ''){
    	$total_count = dbGetOne('CO_download', 'count(*)', "delete_flag=0 and title like '%{$keyword}%'");
    }else{
    	 $total_count = dbGetOne('CO_download', 'count(*)', 'delete_flag=0');
    }
    $startCount = ($page_no - 1) * 10;
	 if($keyword == ''){
    	$downloadList = dbGetAll('CO_download D', 'D.*', "D.delete_flag=0 AND (
            (CURRENT_DATE() >= D.open_date AND CURRENT_DATE() <= D.close_date) OR
            (D.open_date = '0000-00-00' AND CURRENT_DATE() <= D.close_date) OR
            (CURRENT_DATE() >= D.open_date AND  D.close_date = '0000-00-00')OR
            (D.open_date = '0000-00-00' AND  D.close_date = '0000-00-00'))
            limit {$startCount},10");
    }else{
    	$downloadList = dbGetAll('CO_download D', 'D.*', "D.title like '%{$keyword}%' AND D.delete_flag=0 
            AND (
            (CURRENT_DATE() >= D.open_date AND CURRENT_DATE() <= D.close_date) OR
            (D.open_date = '0000-00-00' AND CURRENT_DATE() <= D.close_date) OR
            (CURRENT_DATE() >= D.open_date AND  D.close_date = '0000-00-00')OR
            (D.open_date = '0000-00-00' AND  D.close_date = '0000-00-00')) 
            limit {$startCount},10");
    }

    for($i=0, $size=count($downloadList); $i<$size; $i++) {
        $downloadList[$i]['attach_path'] = dl_generate_attach_link($downloadList[$i]['attach_path'],null,'download');
    }
	$js = <<< BOF
	var page_no = {$page_no};
	var total_count = {$total_count};
	var page_size = 10;
BOF;
    $smarty->assign('csrfToken', md5($sysSession->idx));
	$smarty->assign('inlineMajorJS', $js);
	$smarty->assign('download_keyword', htmlspecialchars($keyword));
	$smarty->assign('downloadList', $downloadList);
	$smarty->display('download.tpl');
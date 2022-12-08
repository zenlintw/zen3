<?php
    /**
     * 檔案瀏覽
     * $Id: listfiles.php,v 1.1 2010/02/24 02:40:24 saly Exp $
     *
     * define('fileCURRDIR', TRUE); = 只限本目錄下的檔案 (不能切換到其它目錄)
     * define('fileFOLDER', TRUE);  = 選目錄
     * define('fileCURRDIR', TRUE); = 只限本目錄下的檔案
     **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/lib/lib_adjust_char.php');
    require_once(sysDocumentRoot . '/lang/teach_statistics.php');

    $sysSession->cur_func = '1200200100';
    $sysSession->restore();
    if (!aclVerifyPermission(1200200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
    }

    //48325 [Chrome][教師/課程管理/學習路徑管理] 新增節點，上傳教材，沒有把教材的路徑帶回URL。Chrome上傳後傳回亂碼
    showXHTML_head_B($MSG['empty_data'][$sysSession->lang]);

    $isContent = (basename($_SERVER['PHP_SELF']) == 'listcontent.php');

    if (!isset($baseUri))
        if ($isContent)
        {
            list($content_ref) = dbGetStSr('WM_term_course', 'content_id', "course_id={$sysSession->course_id}", ADODB_FETCH_NUM);
            $baseUri  = sprintf('/base/%05d/content/%06u', $sysSession->school_id, $content_ref);
        }
        else
            $baseUri  = sprintf('/base/%05d/course/%08u/content', $sysSession->school_id, $sysSession->course_id);

    $basePath = sysDocumentRoot . $baseUri;

    $currPath = preg_replace(array('!\.\./!', '!/+!'),
                             array('','/'),
                             '/' . sysNewDecode($_GET['P']) . '/'
                            );

    if ($_FILES['upload_file'] && is_uploaded_file($_FILES['upload_file']['tmp_name']))
    {
        if (move_uploaded_file($_FILES['upload_file']['tmp_name'],
                               sprintf('%s/base/%05d/course/%08u/content/%s',
                                          sysDocumentRoot,
                                          $sysSession->school_id,
                                          $sysSession->course_id,
                                          mb_convert_encoding($_FILES['upload_file']['name'], 'utf-8', 'utf-8,cp950,gb2312,gbk,JIS,eucjp-win,sjis-win')
                                         )
                              )
           )
            die("
<script>
    window.onload = function() {
        if ((typeof(opener) != 'object') || (opener == null)) return false;
        if (typeof(event) == 'object') {
            if (typeof(opener.getReturnValue) == 'object' && opener.getReturnValue != null)
                window.attachEvent('onunload', opener.getReturnValue);
        } else {
             if (typeof(opener.getReturnValue) == 'function')
                window.addEventListener('unload', opener.getReturnValue, true);
        }
        // opener.returnValue = '/{$_FILES['upload_file']['name']}';
        //#47506 Chrome [教師/課程管理/學習路徑管理] 新增節點，上傳教材，沒有把教材的路徑帶回URL。：改為傳回指定id的欄位
        window.opener.document.getElementById('url').value = '{$_FILES['upload_file']['name']}';
        window.opener.pdfjudge();
        
        self.close();
    };
</script>");
        else {
             wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], $MSG['msg_upload'][$sysSession->lang]);
            die("
<script>
    alert('{$MSG['msg_upload'][$sysSession->lang]}');
    history.back();
</script>"); }
    }

    /**
     * 取得目前目錄所有項目
     * return array [0]=所有目錄 [1]=所有檔案 (未排序)
     */
    function getAllEntry($dir){
        $entries = array(array(), array());
        if (is_dir($dir) && is_readable($dir)){
            if($dp = opendir($dir)){
                while(($item = readdir($dp)) !== false){
                    if (is_file($dir . $item))
                        $entries[1][] = $item;
                    elseif(is_dir($dir . $item) && !preg_match('/^\.\.?$/', $item))
                        $entries[0][] = $item;
                }
                closedir($dp);
            }
            else
                return FALSE;
        }
        return $entries;
    }

    showXHTML_head_B('');
      showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
          showXHTML_script('include', "/lib/jquery/jquery.min.js");
      showXHTML_script('inline', "

window.onload = function() {
    if ((typeof(opener) != 'object') || (opener == null)) return false;
    if (typeof(event) == 'object') {
        if (typeof(opener.getReturnValue) == 'object' && opener.getReturnValue != null)
            window.attachEvent('onunload', opener.getReturnValue);
    } else {
         if (typeof(opener.getReturnValue) == 'function')
            window.addEventListener('unload', opener.getReturnValue, true);
    }
            
        // 更新右上角容量（強制重新計算）
        window.opener.parent.frames[0].updateCourseName('1');
        
        /* 判斷更新右上角容量資訊是否有超量class */
        if ($(window.opener.parent.frames[0].quota).hasClass('overload') === true) {
            $('#tabsSet2').find(\"input[type='submit']\").attr('disabled', true);
        } else {
            $('#tabsSet2').find(\"input[type='submit']\").attr('disabled', false);
        }
        
};

function releaseUnload()
{
    if ((typeof(opener) != 'object') || (opener == null)) return false;
    if (typeof(event) == 'object') {
        if (typeof(opener.getReturnValue) == 'object' && opener.getReturnValue != null)
            window.detachEvent('onunload', opener.getReturnValue);
    } else {
         if (typeof(opener.getReturnValue) == 'function')
            window.removeEventListener('unload', opener.getReturnValue, true);
    }
}

");
    showXHTML_head_E();
    showXHTML_body_B();
    if (dirname($_SERVER['PHP_SELF']) == '/teach/course')
    {
        $ary = array(array($MSG['msg_course'][$sysSession->lang]     , 'tabsSet1', ($isContent ? 'location.replace("listfiles.php");' : '')),
                     array($MSG['msg_course_data'][$sysSession->lang], 'tabsSet1', ($isContent ? '' : 'location.replace("listcontent.php");')),
                     array($MSG['msg_upload_file'][$sysSession->lang], 'tabsSet2', ''));
        if ($isContent) $ary[0][1] = ''; else $ary[1][1] = '';
    }
    else
        $ary = array(array($MSG['msg_program'][$sysSession->lang]));
      echo "<center>\n";
      showXHTML_tabFrame_B($ary, ($isContent ? 2 : 1), 'upForm', 'upTable', 'method="POST" enctype="multipart/form-data" style="display: inline"');
        showXHTML_table_B('id="tabsSet1" border="0" cellpadding="3" cellspacing="1" style="border-collapse: collapse" class="cssTable"');
          showXHTML_tr_B('class="cssTr font01"');
            showXHTML_td('width="330" colspan="2" nowrap', htmlspecialchars($MSG['msg_dir'][$sysSession->lang] . adjust_char($currPath) ));
          showXHTML_tr_E();
          if ($currPath != '/'){
            showXHTML_tr_B('class="cssTrOdd"');
              showXHTML_td('', '<img src="/theme/default/filetype/folder.gif" align="absmiddle"');
              showXHTML_td('width="300"', '<a href="' . $_SERVER['PHP_SELF'] . '?P=%2F" class="cssAnchor">/</a>');
            showXHTML_tr_E();
            showXHTML_tr_B('class="cssTrEvn"');
              showXHTML_td('', '<img src="/theme/default/filetype/folder.gif" align="absmiddle"');
              showXHTML_td('width="300"', '<a href="' . $_SERVER['PHP_SELF'] . '?P=' . sysNewEncode(str_replace('//', '/', dirname($currPath) . '/')) . '" class="cssAnchor">..</a>');
            showXHTML_tr_E();
          }

          $all = getAllEntry($basePath . $currPath);
          if (!empty($all[0]) && sort($all[0])) foreach($all[0] as $category){
              
              if (in_array(htmlspecialchars(adjust_char($category)), array('.DAV'))) {
                  continue;
              }
              
              $cln = $cln == 'class="cssTrOdd font01"' ? 'class="cssTrEvn font01"' : 'class="cssTrOdd font01"';
            showXHTML_tr_B($cln);
              showXHTML_td('', '<img src="/theme/default/filetype/folder.gif" align="absmiddle"');
              // showXHTML_td('nowrap', '<span style="width: 300px; overflow: hidden"><a href="' . $_SERVER['PHP_SELF'] . '?P=' . url_base64_encode($currPath . $category) . '" onmousedown="releaseUnload();" class="cssAnchor">' . htmlspecialchars(adjust_char($category) ) . '</a></span>');
              showXHTML_td('width="300" nowrap', '<a href="' . $_SERVER['PHP_SELF'] . '?P=' . sysNewEncode($currPath . $category) . '" onmousedown="releaseUnload();" class="cssAnchor">' . htmlspecialchars(adjust_char($category) ) . '</a>');
            showXHTML_tr_E();                                                                                                    //rawurlencode($currPath . $category)
          }

          if (!empty($all[1]) && sort($all[1])) foreach($all[1] as $category){
              $cln = $cln == 'class="cssTrOdd font01"' ? 'class="cssTrEvn font01"' : 'class="cssTrOdd font01"';
            showXHTML_tr_B($cln);
              showXHTML_td('', '<img src="/theme/default/filetype/txt.gif" align="absmiddle"');
              // showXHTML_td('nowrap', '<span style="width: 300px; overflow: hidden"><a href="javascript:opener.returnValue = \'' . (isset($content_ref)?"/$baseUri":'') . str_replace("'", "\\'", adjust_char($currPath . $category)) . '\'; self.close();" class="cssAnchor">' . htmlspecialchars(adjust_char($category) ) . '</a></span>');
              
              // #47176 修正chrome點選檔案名稱後無法回傳回原視窗
              // #47210 修正ie chrome 點選檔案後返回路徑多一個/斜線
              // #47465 [教師/課程管理/學習路徑管理] 引用教材庫的檔案，URL多一個「/」：/$baseUri-->$baseUri
              // #47465 [教師/課程管理/學習路徑管理] 引用教材庫的檔案，URL多一個「/」：檔案名稱之前增加.'/'.
              // #47467 [教師/教室管理/功能列設定][連動] sysbar上設定教材檔案之後到前台檢視，結果出現「Not Found」
              // #47329 [教師/教室管理/功能列設定] 新增一個功能，選擇「教材檔案」，選擇教材之後，URL前有加「/」，Chrome只有檔名
              // 教材庫檔名前面要加/，
              switch(strrchr($_SERVER['PHP_SELF'], '/')) {
              case '/sysbar_listcour.php':
                showXHTML_td('width="300" nowrap', '<a href="javascript:window.opener.document.getElementById(\'url\').value = \'' . (isset($content_ref)?"$baseUri" . '/':'') . '/' . substr(str_replace("'", "\\'", adjust_char($currPath . $category)), 1) . '\'; self.close();" class="cssAnchor">' . htmlspecialchars(adjust_char($category) ) . '</a>');
                break;
            
              // 校選單預設設定
              case '/sysbar_listfunc.php':
                showXHTML_td('width="300" nowrap', '<a href="javascript:window.opener.document.getElementById(\'detail\').value = \'' . ('/') . substr(str_replace("'", "\\'", adjust_char($currPath . $category)), 1) . '\'; self.close();" class="cssAnchor">' . htmlspecialchars(adjust_char($category) ) . '</a>');
                break;
              
              // 學習路徑
              default:
              case '/listfiles.php':
                showXHTML_td('width="300" nowrap', '<a href="javascript:window.opener.document.getElementById(\'url\').value = \'' . (isset($content_ref)?"$baseUri" . '/':'') . substr(str_replace("'", "\\'", adjust_char($currPath . $category)), 1) . '\'; window.opener.pdfjudge();self.close();" class="cssAnchor">' . htmlspecialchars(adjust_char($category) ) . '</a>');
                break;
              }
            showXHTML_tr_E();
          }

        showXHTML_table_E();

        showXHTML_table_B('id="tabsSet2" border="0" cellpadding="3" cellspacing="1" style="border-collapse: collapse; display: none" class="cssTable"');
          showXHTML_tr_B('class="cssTrEvn font01"');
            showXHTML_td_B('width="350"');
              showXHTML_input('file', 'upload_file', '', '', 'size="40" class="cssInput"');
            showXHTML_td_E();
          showXHTML_tr_E();
          showXHTML_tr_B('class="cssTrOdd font01"');
            showXHTML_td_B('width="350"');
                        
              showXHTML_input('submit', '', $MSG['msg_upload_file'][$sysSession->lang], '', 'class="cssBtn"');
            showXHTML_td_E();
          showXHTML_tr_E();
        showXHTML_table_E();

      showXHTML_tabFrame_E();
    showXHTML_body_E();
?>

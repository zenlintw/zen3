<?php
    /**************************************************************************************************
    *                                                                                                 *
    *        Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                                 *
    *                                                                                                 *
    *        Programmer: Amm Lee                                                                         *
    *        Creation  : 2003/09/23                                                                      *
    *        work for  : 班級查詢                                                                        *
    *        work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                          *
    *       $Id: people_query.php,v 1.1 2010/02/24 02:38:15 saly Exp $                                                                                          *
    **************************************************************************************************/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lang/people_manager.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/lib/username.php');
    require_once(sysDocumentRoot . '/lib/common.php');
    
    $sysSession->cur_func = '300100600';
    $sysSession->restore();
    if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable')))
    {
        
    }
/**
   查詢的 XML

< ?xml version="1.0" encoding="UTF-8" ? >
<manifest>
    <gpName></gpName>     <- 查詢的 位在那個節點
    <status></status>   <- 身份
    <searchkey></searchkey> <- 搜尋 (帳號 、 姓名 、 email)
    <keyword></keyword> <-  關鍵字
    <page_serial></page_serial>   <- 第幾頁
    <page_num></page_num> <- 一頁顯示幾筆
    <sby1></sby1>   <- 排序欄位
</manifest>
**/

    // 這邊的判斷可能會因為 PHP 版本的更改而有所變動
    if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
        if($dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
            $gpName    = intval(getNodeValue($dom, 'gpName'));                                      // 查詢的 位在那個節點
            $status    = getNodeValue($dom, 'status');                                              // 身份
            $searchkey = getNodeValue($dom, 'searchkey');                                            // searchkey
            $keyword   = escape_LIKE_query_str(addslashes(trim(getNodeValue($dom, 'keyword'))));    // 關鍵字
            $page_no   = intval(getNodeValue($dom, 'page_serial'));                                    // 第幾頁
            $page_num  = intval(getNodeValue($dom, 'page_num'));                                    // 一頁顯示幾筆
            $sby1      = preg_replace('/[^\w, `]+/', '', getNodeValue($dom, 'sby1'));                // 排序欄位
            
            //  從第幾筆開始抓資料
            $limit_begin = (($page_no-1)*$page_num);

            if ($page_no > 0){
                $limit_str = ' limit ' . $limit_begin . ',' . $page_num;
            }
        }
        wmSysLog($sysSession->cur_func, $sysSession->school_id ,0, 0, 'auto',$_SERVER['PHP_SELF'], '取得人員的資料 gpName='.$gpName.'; searchkey='.$searchkey.'; keyword='.$keyword.'; status='.$status.';');
        //  取得 人員的資料 (begin)
        if ($gpName == 1000000){ // 取得全校所有人的資料

            $cond = '';
            switch ($searchkey){    // 搜尋
                case 'real':      // 姓名
                        if (!empty($keyword)) $cond = ' and if(first_name REGEXP "^[0-9A-Za-z _-]$" && last_name REGEXP "^[0-9A-Za-z _-]$", concat(IFNULL(`first_name`,""), " ", IFNULL(`last_name`,"")), concat(IFNULL(`last_name`,""), IFNULL(`first_name`,""))) LIKE "%' . $keyword . '%"';
                    break;

                case 'account':      // 帳號
                        if (!empty($keyword)) $cond = ' and username like "%' . $keyword . '%"';
                    break;

                case 'email':      // email
                        if (!empty($keyword)) $cond = ' and email like "%' . $keyword . '%"';
                    break;
            }

            $RS = dbGetStMr('WM_user_account', 'username,first_name,last_name,gender,email', 'username != "'.sysRootAccount.'" ' . $cond . ' order by ' . $sby1 . $limit_str, ADODB_FETCH_ASSOC);

            //  判斷是否有資料 (begin)
            if ($RS){
                $temp = Get_Data($RS);
                list($total_row) = dbGetStSr('WM_user_account', 'count(*)', 'username != "'.sysRootAccount.'"' . $cond, ADODB_FETCH_NUM);
            }else{
                $total_row = 0;
            }
            //  判斷是否有資料 (end)

            echo '<', '?xml version="1.0" encoding="UTF-8" ?', ">\n",
                 '<manifest>',
                 '<total_row>', $total_row, '</total_row>',
                 $temp,
                 '</manifest>';

        }else{
            $cond = '';
            switch ($searchkey){  // 搜尋
                case 'real':      // 姓名
                    if ($status == 'all' && $keyword)
                        $cond = ' and if(A.first_name REGEXP "^[0-9A-Za-z _-]$" && A.last_name REGEXP "^[0-9A-Za-z _-]$", concat(A.first_name, " ", A.last_name), concat(A.last_name, A.first_name)) LIKE "%' . $keyword . '%" ';
                    else
                    {
                        if (empty($keyword)){
                            $cond = ' and (B.role & ' . $sysRoles[$status] . ')  ';
                        }else{
                            $cond = ' and (B.role & ' . $sysRoles[$status] . ') and if(A.first_name REGEXP "^[0-9A-Za-z _-]$" && A.last_name REGEXP "^[0-9A-Za-z _-]$", concat(A.first_name, " ", A.last_name), concat(A.last_name, A.first_name)) LIKE "%' . $keyword . '%" ';
                        }
                    }

                    break;
                case 'account':   // 帳號
                    if ($status == 'all' && $keyword)
                        $cond = ' and B.username like "%' . $keyword . '%" ';
                    else
                    {
                        if (empty($keyword)){
                            $cond = ' and (B.role & ' . $sysRoles[$status] . ') ';
                        }else{
                            $cond = ' and (B.role & ' . $sysRoles[$status] . ') and B.username like "%' . $keyword . '%" ';
                        }
                    }

                    break;
                case 'email':     // email
                    if ($status == 'all' && $keyword)
                        $cond = ' and A.email like "%' . $keyword . '%" ';
                    else
                    {
                        if (empty($keyword)){
                            $cond = ' and (B.role & ' . $sysRoles[$status] . ') ';
                        }else{
                            $cond = ' and (B.role & ' . $sysRoles[$status] . ') and A.email like "%' . $keyword . '%" ';
                        }
                    }

                    break;
            }

            $sqls = str_replace(array('%TABLE%','%CLASS_ID%'), array('WM_class_member',   $gpName), $Sqls['get_class']) .
                    $cond . ' order by ' . $sby1;

            $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
            $RS = $sysConn->Execute($sqls);
            $total_row = $RS ? $RS->RecordCount() : 0;
            
            if ($page_no > 0) $RS = $sysConn->SelectLimit($sqls,$page_num,$limit_begin);
            $temp = $RS ? Get_Data($RS) : '';

            //  判斷是否有資料 (end)
            echo '<', '?xml version="1.0" encoding="UTF-8" ?', ">\n",
                 '<manifest>',
                 '<total_row>', $total_row, '</total_row>',
                 $temp,
                 '</manifest>';
        }
        //  取得 人員的資料 (end)
    } else {

        die('<?xml version="1.0" encoding="UTF-8" ?>' . "\n". "<manifest></manifest>\n");

    }

//  *********************************************************************************************
/**
    產生 查詢結果的資料
**/

function Get_Data($RS){
    global $MSG,$sysSession,$sysRoles,$gpName,$sysConn,$Sqls;
    $result = '';
    if ($RS->RecordCount() > 0){    // if ($RS->RecordCount() > 0) begin
        // while begin
        while (!$RS->EOF) {
            // 姓名
            // Bug#1263 真實姓名的顯示不按照個人語系，而按照個人姓名的設定 by Small 2006/12/28
            $realname = htmlspecialchars(checkRealname($RS->fields['first_name'],$RS->fields['last_name']));
            // 性別
            switch ($RS->fields['gender']){
                case 'M':
                    $gender = '/theme/default/academic/male.gif';
                    break;
                case 'F':
                    $gender = '/theme/default/academic/female.gif';
                    break;
            }

            // if begin
            if ($gpName == 1000000){
                // 隸屬在那些班級
                $class_sqls = str_replace('%USERNAME%',$RS->fields['username'],$Sqls['user_belong_class']);
                $class_RS = $sysConn->Execute($class_sqls);
                $belong_class = '';
                if ($class_RS){
                    while ($class_RS1 = $class_RS->FetchRow()){
                        $class_ary = unserialize($class_RS1['caption']);
                        $belong_class .= htmlspecialchars($class_ary[$sysSession->lang]) . ',';
                    }
                }
                if (strlen($belong_class) > 0){
                    $belong_class = substr($belong_class,0,-1);
                }
            }else{
                if ($RS->fields['role'] >= $sysRoles['director'])
                    $belong_class = $MSG['title70'][$sysSession->lang];
                elseif ($RS->fields['role'] >= $sysRoles['assistant'])
                    $belong_class = $MSG['title67'][$sysSession->lang];
                elseif ($RS->fields['role'] >= $sysRoles['student'])
                    $belong_class = $MSG['title66'][$sysSession->lang];
                elseif ($RS->fields['role'] >= $sysRoles['paterfamilias'])
                    $belong_class = $MSG['title63'][$sysSession->lang];
                elseif ($RS->fields['role'] >= $sysRoles['senior'])
                    $belong_class = $MSG['title62'][$sysSession->lang];
                else
                    $belong_class = $MSG['title61'][$sysSession->lang];
                //   身份  (end)
            }
            // if end

            $result .= <<< BOF
                <class id="{$RS->fields['username']}" checked="false">
                    <realname>{$realname}</realname>
                    <username>{$RS->fields['username']}</username>
                    <gender>{$gender}</gender>
                    <role>{$belong_class}</role>
                </class>
BOF;

            $RS->MoveNext();
        }
        // while end
    }   // if ($RS->RecordCount() > 0) end
    return $result;
}
?>

<?php
    /**
     * 進行線上更新的列表
     * $Id: list.php,v 1.1 2010/02/24 02:38:48 saly Exp $
     **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

    //此線上更新只提供給root這帳號使用
    if ($sysSession->username != sysRootAccount)
    {
        die('This function is only for root.');
    }
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lang/wm3update.php');
    require_once(sysDocumentRoot . '/academic/wm3update/lib.php');
    #========functions =================
    function showUpdateListHtml()
    {
        global $MSG, $sysSession;
        $obj = new WM3UpdateLog();
        $arr = $obj->getLogList();
        if (count($arr) == 0)
        {
        }else{
            for($i=0; $i<count($arr); $i++)
            {
                $trcss = ($trcss == "cssTrEvn" ) ? "cssTrOdd" : "cssTrEvn";
                showXHTML_tr_B('class="'.$trcss.'"');
                showXHTML_td('align="center"', $arr[$i][0]);
                showXHTML_td('align="center"', $arr[$i][1]);
                showXHTML_td('align="center"', $arr[$i][3]);
                showXHTML_td('align="center"', $arr[$i][4]);
                $oInfo = new WM3UpdateInfo($arr[$i][2]);
                showXHTML_td('align="center"', $oInfo->getUpdateUserInfo());
                if ($arr[$i][1] == 'U')
                {
                        showXHTML_td('align="center"','<input type="button" name="btn_rollback" value="'.$MSG['btn_rollback'][$sysSession->lang].'" class="cssBtn" onclick="Rollback(\''.$arr[$i][2].'\');">');
                }else{
                        showXHTML_td('align="center"',"N/A");
                }
                showXHTML_td('align="center"','<input type="button" name="btn1" value="'.$MSG['btn_more'][$sysSession->lang].'" class="cssBtn" onClick="viewReadme(\''.$arr[$i][2].'\');">');
                showXHTML_td('align="center"','<input type="button" name="btn2" value="'.$MSG['btn_more'][$sysSession->lang].'" class="cssBtn" onClick="viewFiles(\''.$arr[$i][2].'\');">');
                showXHTML_tr_E('');
            }
        }
    }
#========main=======================
	
#========Html output ===============
$js = <<< BOF
    var msg = '{$MSG['cofirm_rollback'][$sysSession->lang]}';
    function Rollback(val)
    {
        if (confirm(msg))
        {
            document.FormRollback.rollback_id.value = val;
            document.FormRollback.submit();
        }
    }


    function viewReadme(dirname)
    {
        window.open('viewUpdateInfo.php?content=readme&which='+dirname);
    }

    function viewFiles(dirname)
    {
        window.open('viewUpdateInfo.php?content=filelist&which='+dirname);
    }
	
BOF;
    showXHTML_head_B('');
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
    showXHTML_script('inline', $js);
    showXHTML_script('include', '/lib/jquery/jquery.min.js');
    showXHTML_script('include', 'list.js');
    showXHTML_head_E('');
    
    showXHTML_body_B();
        $arry = array(array($MSG['tab_list'][$sysSession->lang], 'MySet',  ''),
            array($MSG['sys_info'][$sysSession->lang],      'MySet2',  'MySet2.style.display="table";')
        );
        showXHTML_table_B('border="0" cellpadding="0" cellspacing="0"');
            showXHTML_tr_B();
                showXHTML_td_B();
                    showXHTML_tabs($arry, 1, false, false);
                showXHTML_td_E();
            showXHTML_tr_E();
            showXHTML_tr_B('');
                showXHTML_td_B('valign="top" id="CGroup"');
                    showXHTML_table_B('width="760" align="center" border="0" cellspacing="1" cellpadding="3" id="MySet" class="cssTable"');
                        showXHTML_tr_B('class="cssTrHead"');
                            showXHTML_td_B('colspan="8" align="right"');
                                echo '<input type="button" name="btnNext" value="首頁檔案管理" onClick="document.location.href=\'/academic/explorer/manager.php\'" class="cssBtn">';
                                echo '<input type="button" name="btnNext" value="'.$MSG['btn_do_update'][$sysSession->lang].'" onClick="document.location.href=\'process.php\'" class="cssBtn">';
                            showXHTML_td_E();
                        showXHTML_tr_E('');

                        // 顯示目前是否有更新指令檔存在
                        $oUpdSess = new WM3UpdateSession("cronUpdate");
                        $insNum = $oUpdSess->hasInstruction();
                        showXHTML_tr_B('class="cssTrHead"');
                            showXHTML_td_B('colspan="8"');
                                echo $MSG['instruction_number_info'][$sysSession->lang].'<span style="font-size:16px; font-weight: bold; color:'.(($insNum == 0)?'black':'red').'">'.$insNum.'</span>';
                            showXHTML_td_E();
                        showXHTML_tr_E('');
                        showXHTML_tr_B('class="cssTrOdd"');
                        showXHTML_td('align="center"', $MSG['list_th1'][$sysSession->lang]);
                        showXHTML_td('align="center"', $MSG['list_th2'][$sysSession->lang]);
                        showXHTML_td('align="center"', $MSG['list_th3_1'][$sysSession->lang]);
                        showXHTML_td('align="center"', $MSG['list_th4'][$sysSession->lang]);
                        showXHTML_td('align="center"', $MSG['list_th4_1'][$sysSession->lang]);
                        showXHTML_td('align="center"', $MSG['list_th5'][$sysSession->lang]);
                        showXHTML_td('align="center"', $MSG['list_th6'][$sysSession->lang]);
                        showXHTML_td('align="center"', $MSG['list_th7'][$sysSession->lang]);
                        showXHTML_tr_E('');
                        echo showUpdateListHtml();
                        showXHTML_tr_B('class="cssTrHead"');
                            showXHTML_td_B('colspan="8" align="right"');
                                    echo '<input type="button" name="btnNext" value="'.$MSG['btn_do_update'][$sysSession->lang].'" onClick="document.location.href=\'process.php\'" class="cssBtn">';
                            showXHTML_td_E();
                        showXHTML_tr_E('');
                    showXHTML_table_E('');

                    showXHTML_table_B('width="760" align="center" border="0" cellspacing="1" cellpadding="3" id="MySet2" class="cssTable" style="display: none"');
                        showXHTML_tr_B('class="cssTrHead"');
                            showXHTML_td('align="center"', $MSG['th_item'][$sysSession->lang]);
                            showXHTML_td('align="center"', $MSG['th_info'][$sysSession->lang]);
                        showXHTML_tr_E('');

                        $trcss = ($trcss == "cssTrEvn" ) ? "cssTrOdd" : "cssTrEvn";
                        showXHTML_tr_B('class="'.$trcss.'"');
                            showXHTML_td('align="center"', $MSG['sys_ver'][$sysSession->lang]);
                            showXHTML_td_B('align="center"');
                                echo '<span id="wm-version"></span><span id="wm-reversion"></span>';
                            showXHTML_td_E();
                        showXHTML_tr_E('');

                        $trcss = ($trcss == "cssTrEvn" ) ? "cssTrOdd" : "cssTrEvn";
                        showXHTML_tr_B('class="'.$trcss.'"');
                            showXHTML_td('align="center"', $MSG['xmlapi_ver'][$sysSession->lang]);
                            showXHTML_td_B('align="center"');
                                echo '<span id="xmlapi-version"></span>';
                            showXHTML_td_E();
                        showXHTML_tr_E('');

//                        $trcss = ($trcss == "cssTrEvn" ) ? "cssTrOdd" : "cssTrEvn";
//                        showXHTML_tr_B('class="'.$trcss.'"');
//                            showXHTML_td('align="center"', $MSG['sys_ver'][$sysSession->lang]);
//                            showXHTML_td('align="center"', '');
//                        showXHTML_tr_E('');

                    showXHTML_table_E('');

                    showXHTML_td_E('');
            showXHTML_tr_E('');
        showXHTML_table_E();
        
        showXHTML_form_B('action="/academic/wm3update/rollback.php" method="post" enctype="multipart/form-data" style="display:inline;"', 'FormRollback');
            showXHTML_input('hidden', 'rollback_id', '', '', '');
        showXHTML_form_E('');
        
    showXHTML_body_E('');
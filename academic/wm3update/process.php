<?php
    /**
     * 進行線上更新的程序
     * $Id: process.php,v 1.1 2010/02/24 02:38:48 saly Exp $
     **/
    set_time_limit(3000);
    ignore_user_abort(true);

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

    //此線上更新只提供給root這帳號使用
    if ($sysSession->username != sysRootAccount)
    {
        header("HTTP/1.0 404 Not Found");
        exit();
    }
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lang/wm3update.php');
    require_once(sysDocumentRoot . '/academic/wm3update/lib.php');
#========functions =================
    function cmp($a, $b) {
        if ($a[2] == $b[2]) {
            return 0;
        }
        return ($a[2] < $b[2]) ? 1 : -1;
    }

    function showTgzFilesHtml()
    {
        $arr = WM3Update::getMatchTgzFileList();
        if (count($arr) == 0)
        {

        }else{
            usort($arr, 'cmp');
            for($i=0; $i<count($arr); $i++)
            {
                $checked = '';
                $red = '';
                if ($_FILES['uploadfile']['name'] === $arr[$i][0]) {
                    $checked = 'checked';
                    $red = 'color: red;';
                }
                $trcss = ($trcss == "cssTrEvn" ) ? "cssTrOdd" : "cssTrEvn";
                showXHTML_tr_B('class="'.$trcss.'" style="' . $red . '"');
                showXHTML_td('align="center"','<input type="radio" name="rdo_filename" value="'.$arr[$i][0].'" ' . $checked . '>');
                showXHTML_td('align="left"'  ,$arr[$i][0]);
                showXHTML_td('align="center"',$arr[$i][1]);
                showXHTML_td('align="center"',date("Y-m-d H:i:s",$arr[$i][2]));
                showXHTML_tr_E('');
            }
        }
    }
#========main=======================
if (!isset($_POST['op']))
{
    if (WM3Update::isLockedFileExists())
    {
        die("online update is locked.");
    }
}


//處理Upload
if ($_POST['op'] == 'upload')
{
    if (is_uploaded_file($_FILES['uploadfile']['tmp_name'])) {
            $targetfile = WM3Update::getTgzDirRealpath() . $_FILES['uploadfile'][name];
    if (!move_uploaded_file($_FILES['uploadfile']['tmp_name'], $targetfile))
    {
        die("fail to move uploaded file to ".$targetfile);
    }
    }
}
else if ($_POST['op'] == 'untar')
{
    //先檢查request的參數
    if (empty($_POST['rdo_filename'])) die("error: rdo_filename value is empty.");
    if (empty($_POST['input_md5'])) die("error: md5 value is empty.");

    //檢查所要進行的目錄與檔案
    $tgzfile = WM3Update::getTgzDirRealpath().$_POST['rdo_filename'];
    if (!file_exists($tgzfile)) die($tgzfile."is not exists!");
    if (strcmp($_POST['input_md5'],md5_of_file($tgzfile)) != 0)
    {
        echo '<html><head><script language="javascript">',
             'alert("',$MSG['error_md5'][$sysSession->lang],'");',
             'document.location.href="process.php";',
             '</script></head></html>';
        exit;
    }

    //建立系統程式更新Session
    $id = basename($_POST['rdo_filename'],'.tgz');
    $oUpdSess = new WM3UpdateSession($id);
    $oUpdSess->setTagfile($tgzfile);
    $oUpdSess->doUntar();
    $oUpdSess->addUserInfo($_POST['input_user']);

    // 要移除上傳安裝檔案
    if ($_POST['rdoRemoveTarFile'] == 'Y') {
        if (file_exists($tgzfile)) {
            unlink($tgzfile);
        }
    }

    header("Location: process2.php?update_id=".$oUpdSess->update_id."&rawfname=".$_POST['rdo_filename']);
    exit;
}

#========Html output ===============
    $js = <<< BOF
    var alert_msg_select_upload  = "{$MSG['alert_msg_select_upload'][$sysSession->lang]}";
    var alert_msg_filename_error = "{$MSG['alert_msg_filename_error'][$sysSession->lang]}";
    var alert_msg_select_one     = "{$MSG['alert_msg_select_one'][$sysSession->lang]}";
    var alert_msg_md5_notfilled  = "{$MSG['alert_msg_md5_notfilled'][$sysSession->lang]}";
    var alert_msg_user_notfilled = "{$MSG['alert_msg_user_notfilled'][$sysSession->lang]}";

    function goUpload(fobj)
    {
        var obj = document.getElementById("uploadfile");
        if (obj.value.length == 0)
        {
            alert(alert_msg_select_upload);
            obj.focus();
            return false;
        }

        var re = new RegExp("[FIX|Upgrade|Custom|Patch]+_.*\.tgz");
        if (!re.test(obj.value))
        {
            alert(alert_msg_filename_error);
            obj.focus();
            return false;
        }
        fobj.op.value = 'upload';
        fobj.submit();
    }

    function doUntar(fobj)
    {
        var bl_selected = false;
        for(i=0; i<fobj.elements.length;i++)
        {
            if (fobj.elements[i].type == 'radio')
                if (fobj.elements[i].checked){
                        bl_selected = true;
                        break;
                }
        }

        if (!bl_selected)
        {
            alert(alert_msg_select_one);
            return false;
        }

        if (fobj.input_md5.value.length == 0)
        {
            alert(alert_msg_md5_notfilled);
            fobj.input_md5.focus();
            return false;
        }

        if (fobj.input_user.value.length == 0)
        {
            alert(alert_msg_user_notfilled);
            fobj.input_user.focus();
            return false;
        }

        fobj.op.value = 'untar';
        fobj.submit();
    }
BOF;
    showXHTML_head_B("");
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
    showXHTML_script('inline', $js);
    showXHTML_head_E('');
    showXHTML_body_B();
    $arry[] = array($MSG['tab_step1'][$sysSession->lang], 'TabStep1');
    $arry[] = array($MSG['tab_step2'][$sysSession->lang], 'TabStep2', 'tabsSelect(1);');
    $arry[] = array($MSG['tab_step3'][$sysSession->lang], 'TabStep3', 'tabsSelect(1);');
    $arry[] = array($MSG['tab_step4'][$sysSession->lang], 'TabStep4', 'tabsSelect(1);');
    showXHTML_table_B('border="0" cellpadding="0" cellspacing="0"');
      showXHTML_tr_B();
        showXHTML_td_B();
          showXHTML_tabs($arry, 1, false, false);
        showXHTML_td_E();
      showXHTML_tr_E();
      showXHTML_tr_B('');
            showXHTML_td_B('valign="top" id="CGroup"');
            showXHTML_form_B('action="/academic/wm3update/process.php" method="post" enctype="multipart/form-data" style="display:inline;"', 'setForm');
                    showXHTML_input('hidden', 'op', '', '', '');
                    showXHTML_table_B('width="760" align="center" border="0" cellspacing="1" cellpadding="3" id="MySet" class="cssTable"');
                            showXHTML_tr_B('class="cssTrHead"');
                            showXHTML_td('colspan="4"',$MSG['step1_desc'][$sysSession->lang]);
                            showXHTML_tr_E('');
                            showXHTML_tr_B('class="cssTrHead"');
                                    showXHTML_td_B('colspan="4"');
                                            echo $MSG['lbl_upload_files'][$sysSession->lang].'<input type="file" id="uploadfile" name="uploadfile" class="cssBtn">&nbsp;&nbsp;&nbsp;&nbsp;';
                                            echo '<input type="button" name="btnUpload" value="'.$MSG['btn_upload_files'][$sysSession->lang].'" class="cssBtn" onClick="goUpload(this.form);">';
                                    showXHTML_td_E();
                            showXHTML_tr_E('');
                            showXHTML_tr_B('class="cssTrOdd"');
                            showXHTML_td('align="center"',$MSG['step1_th_radio'][$sysSession->lang]);
                            showXHTML_td('align="center"',$MSG['step1_th_filename'][$sysSession->lang]);
                            showXHTML_td('align="center"',$MSG['step1_th_filesize'][$sysSession->lang]);
                            showXHTML_td('align="center"',$MSG['step1_th_filemtime'][$sysSession->lang]);
                            showXHTML_tr_E('');
                            echo showTgzFilesHtml();
                            showXHTML_tr_B('class="cssTrHead"');
                                    showXHTML_td_B('colspan="4"');
                                            echo '<br>';
                                            echo $MSG['lbl_md5'][$sysSession->lang].'<input type="text" name="input_md5" value="" size="36" class="cssBtn">';
                                    showXHTML_td_E();
                            showXHTML_tr_E('');
                            showXHTML_tr_B('class="cssTrHead"');
                                    showXHTML_td_B('colspan="4"');
                                            echo '<br>';
                                            echo $MSG['lbl_user'][$sysSession->lang].'<input type="text" name="input_user" value="" size="36" class="cssBtn">';
                                    showXHTML_td_E();
                            showXHTML_tr_E('');
                            showXHTML_tr_B('class="cssTrHead"');
                                    showXHTML_td_B('colspan="4"');
                                            echo '<br>';
                                            echo $MSG['untar_and_remove'][$sysSession->lang];
                                            echo '<input type="radio" name="rdoRemoveTarFile" value="Y" checked />是';
                                            echo '<input type="radio" name="rdoRemoveTarFile" value="N" />否';
                                    showXHTML_td_E();
                            showXHTML_tr_E('');
                            showXHTML_tr_B('class="cssTrHead"');
                                    showXHTML_td_B('colspan="4" align="right"');
                                            echo '<input type="button" name="btnNext" value="'.$MSG['btn_do_step2'][$sysSession->lang].'" onClick="doUntar(this.form);" class="cssBtn">';
                                    showXHTML_td_E();
                            showXHTML_tr_E('');
                    showXHTML_table_E('');
            showXHTML_form_E('');
                    showXHTML_td_E('');
            showXHTML_tr_E('');
    showXHTML_table_E();
    showXHTML_body_E('');
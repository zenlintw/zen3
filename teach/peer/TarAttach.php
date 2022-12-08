<?php
	/**
	 * 作業附檔下載
	 *
	 * @since   2005/08/05
	 * @author  Wing
	 * @version $Id: TarAttach.php,v 1.1 2010/02/24 02:40:27 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot .'/lang/' . QTI_which . '_teach.php');
	require_once(sysDocumentRoot .'/lib/acl_api.php');
	$sysSession->cur_func='600200100';
	if (!aclVerifyPermission(600200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}
	
	if (ereg('^[0-9]{9}$', $_POST['hwid']))   // 顯示頁面
	{
    	// 開始呈現 HTML
    	/*showXHTML_head_B($MSG['attach'][$sysSession->lang]);
    	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
    	showXHTML_script('include', '/lib/common.js');
    	showXHTML_script('include', '/lib/xmlextras.js');
    	showXHTML_body_B();
    		showXHTML_table_B('id="displayPanel" border="0" cellpadding="3" cellspacing="1" style="border-collapse: collapse" class="box01"');
    		    showXHTML_tr_B('class="bg03 font01"');
    		    	showXHTML_td_B('align="center"');
                        echo $MSG['att_upload'][$sysSession->lang], '<br>',
                        '<a href="TarAttach.php?download+',
                        md5(sysTicketSeed . $_COOKIE['idx'] . $_SERVER['argv'][0]),'+',$_SERVER['argv'][0],
                        '">', $MSG['all_homeowrk'][$sysSession->lang], '</a>';
                    showXHTML_td_E();
    		    showXHTML_tr_E();
            showXHTML_table_E();
    	showXHTML_body_E();*/
    	// 開始呈現 HTML
    	
		if(empty($_POST['examinee'])){
			$fname = 'hw' . $_POST['exam_id'] . '.zip';
			$file_path = sysDocumentRoot . sprintf('/base/%5u/course/%8u/peer/A/%09u/', $sysSession->school_id, $sysSession->course_id, $_POST['hwid']);
		}else{
			$fname = 'hw' . $_POST['examinee'] . '.zip';
			$file_path = sysDocumentRoot . sprintf('/base/%5u/course/%8u/peer/A/%09u/%s/', $sysSession->school_id, $sysSession->course_id, $_POST['hwid'], $_POST['examinee']);

		} 
		
		$program = trim(exec("sh -c 'PATH=/usr/bin:/usr/sbin:/sbin:/bin:/usr/local/bin which zip'"));
		$options='-D -r -q -7';
		exec("cd {$file_path} && {$program} {$options} Coursetemp.zip .");

    }
    elseif ($_SERVER['argc'] == 3)
    {
        if ($_SERVER['argv'][1] != md5(sysTicketSeed . $_COOKIE['idx'] . $_SERVER['argv'][2])) die('Access Denied.');
        include_once($_SERVER['DOCUMENT_ROOT'] . '/lib/archive_api.php');

        while (@ob_end_clean()); // 抑制防右鍵功能
        $fname = 'hw' . $_SERVER['argv'][2] . '.zip';
        header('Content-Disposition: attachment; filename="' . $fname . '"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Type: application/zip; name="' . $fname . '"');
        $myzip = new ZipArchive_php4('', sysDocumentRoot . sprintf('/base/%5u/course/%8u/homework/A/%09u', $sysSession->school_id, $sysSession->course_id, $_SERVER['argv'][2]), true);

    }
    else
        die('Access Denied.');
?>

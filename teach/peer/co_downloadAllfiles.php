<?php
    /**
     * �U���Ҧ��@�~������
     *
     * PHP 4.4.7+, MySQL 4.0.21+, Apache 1.3.36+
     *
     * LICENSE: ���{����l�X�ɮסA�����p��ުѥ��������q�Ҧ��C�D�g���p�ѭ����v
     * �h�Y�T��ŧ�B�y��B�����B���}�����Υ������e�C�Y���o���p�ѭ����v�ѡA��o��
     * �ӮѤ��ҭ���ϥνd��ϥΤ��A�_�h���H�I�v�רs�C
     *
     * @package     WM3
     * @author      Jeff Wang <jeff@sun.net.tw>
     * @copyright   2000-2013 SunNet Tech. INC.
     * @version     CVS: $Id$
     * @link        http://demo.learn.com.tw/1000110138/index.html
     * @since       2013-03-22
     * 
     * �Ƶ��G          
     */

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/' . QTI_which . '_teach.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	include_once($_SERVER['DOCUMENT_ROOT'] . '/lib/archive_api.php');
	if(empty($_POST['examinee'])){
		$fname = 'hw' . $_POST['exam_id'] . '.zip';
		$file_path = sysDocumentRoot . sprintf('/base/%5u/course/%8u/peer/A/%09u/', $sysSession->school_id, $sysSession->course_id, $_POST['exam_id']);
	}else{
		$fname = 'hw' . $_POST['examinee'] . '.zip';
		$file_path = sysDocumentRoot . sprintf('/base/%5u/course/%8u/peer/A/%09u/%s/', $sysSession->school_id, $sysSession->course_id, $_POST['exam_id'], $_POST['examinee']);
	} 
	
	header('Content-Description: File Transfer');
	header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="'.$fname.'"');
    header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Cache-Control: private', false);
	header('Pragma: public');
	header('Content-Length: ' . filesize($file_path.'Coursetemp.zip'));
	   
	   //ob_clean();
	   //ob_end_flush();
	   //readfile($file_path.'Coursetemp.zip');

	    

    $chunkSize = 1024 * 1024;
    $handle = fopen($file_path.'Coursetemp.zip', 'rb');
    while (!feof($handle)) {
        $buffer = fread($handle, $chunkSize);
        echo $buffer;
        ob_flush();
        flush();
    }
    fclose($handle);
		

//    $myzip = new CO_ZipArchive('', sysDocumentRoot . sprintf('/base/%5u/course/%8u/homework/A/%09u/%s', $sysSession->school_id, $sysSession->course_id, $_POST['exam_id'], $_POST['examinee']), true);
    if (file_exists($file_path)) {
        exec("/bin/rm {$file_path}/Coursetemp.zip");
    }

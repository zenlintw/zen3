<?php
    /**
     * 試卷 (作業、問卷) 匯入
     *
     * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
     *
     * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
     * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
     * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
     *
     * @package     WM3
     * @author      Wiseguy Liang <wiseguy@mail.wiseguy.idv.tw>
     * @copyright   2000-2006 SunNet Tech. INC.
     * @version     CVS: $Id: exam_import.php,v 1.1 2009-06-25 09:27:42 edi Exp $
     * @link        http://demo.learn.com.tw/1000110138/index.html
     * @since       2006-06-16
     */

   	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/' . QTI_which . '_teach.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/teach/exam/qti_xml_lib.php');

	//ACL begin
	if (QTI_which == 'exam') {
		$sysSession->cur_func='1600100500';
	}
	else if (QTI_which == 'homework') {
		$sysSession->cur_func='1700100500';
	}
	else if (QTI_which == 'questionnaire') {
		$sysSession->cur_func = '1800100200';
	}
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

	}
	//ACL end
	
	$course_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id; //10000000;

	$ticket = md5(sysTicketSeed . $course_id . $_SERVER['QUERY_STRING']);

	showXHTML_head_B('');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/teach/wm.css");
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array(array($MSG['import_result'][$sysSession->lang]));
		showXHTML_tabFrame_B($ary, 1, '', 'ListTable', 'action="exam_modify.php", method="POST" style="display: inline"');
			showXHTML_table_B('id ="mainTable" width="680" border="0" cellspacing="1" cellpadding="3" class="cssTable"');

				$dom = false;
			    $rtn_state = parseXML(); // 匯入題目
			    if ($rtn_state == 'xml_root_err' || $dom === false) exit;
    
                // 使用 XSLT 將 qti_xml 轉換為 WM XML
				$xsl = domxml_xslt_stylesheet_file('../../teach/exam/qti2wm.xsl');// Creates a DomXsltStylesheet Object from a xml document in a string.
				$result =  $xsl->process($dom);
				$recall = preg_replace(array('/>\s+</', '/\s+</', '/>\s+/'), array('><', '<', '>'), $xsl->result_dump_mem($result));// Dumps the result from a XSLT-Transformation back into a string

				$wm_qti_exam = str_replace(array('WM_ITEM_TYPE[1]',
												 'WM_ITEM_TYPE[2]',
												 'WM_ITEM_TYPE[3]',
												 'WM_ITEM_TYPE[4]',
												 'WM_ITEM_TYPE[5]',
												 'WM_ITEM_TYPE[6]',
												 'WM_ITEM_TYPE[7]'),
										   array($MSG['item_type1'][$sysSession->lang],
                                                 $MSG['item_type2'][$sysSession->lang],
                                                 $MSG['item_type3'][$sysSession->lang],
                                                 $MSG['item_type4'][$sysSession->lang],
                                                 $MSG['item_type5'][$sysSession->lang],
                                                 $MSG['item_type6'][$sysSession->lang],
												 $MSG['item_type7'][$sysSession->lang]),
										   $recall);

				$ret = $ctx->xpath_eval('/questestinterop/wm:title');
				if (is_array($ret->nodeset) && count($ret->nodeset))
				{
				    $titles = $ret->nodeset[0]->get_content();
				}
				else
				{
					$title  = '[NEW_IMPORT]' . date('Y-m-d H:i:s');
					$titles = serialize(array('Big5'		=> $title,
							                  'GB2312'	    => $title,
							                  'en'		    => $title,
							                  'EUC-JP'	    => $title,
							                  'user_define' => $title));
				}
				
				$ret = $ctx->xpath_eval('/questestinterop');
				if (is_object($ret->nodeset[0])) {
		            $type = $ret->nodeset[0]->get_attribute('use_type');
		        }
				
                                
                                if (QTI_which === 'homework') {
                                    $fields = 'course_id,title,begin_time,close_time,content,create_time,type';
                                    $value  = $course_id . ',"' . addslashes($titles) . '", "0000-00-00 00:00:00", "9999-12-31 00:00:00", "' . addslashes($wm_qti_exam) . '","' . date('Y-m-d H:i:s') . '","' . $type. '"';
                                } else {
                                    $fields = 'course_id,title,begin_time,close_time,content,type';
                                    $value  = $course_id . ',"' . addslashes($titles) . '", "0000-00-00 00:00:00", "9999-12-31 00:00:00", "' . addslashes($wm_qti_exam) . '","' . $type. '"';
                                }
				
				dbNew('WM_qti_' . QTI_which . '_test', $fields, $value); // 存入試卷

			    unlink($_FILES['import_file']['tmp_name']);

			  showXHTML_tr_B('class="cssTrEvn"');
                showXHTML_td_B('align="right" colspan="2" style="padding-right: 1em"');
				  showXHTML_input('button', '', $MSG['return list'][$sysSession->lang],   '', 'class="cssBtn" onclick="location.replace(\'exam_maintain.php\');"');
				  showXHTML_input('submit', '', $MSG['edit instance'][$sysSession->lang], '', 'class="cssBtn"');
				  showXHTML_input('hidden', 'ticket', $ticket);
	    		  showXHTML_input('hidden', 'referer', $_SERVER['QUERY_STRING']);
				  showXHTML_input('hidden', 'lists', $sysConn->Insert_ID());
                showXHTML_td_E();
			  showXHTML_tr_E();

			showXHTML_table_E();
		showXHTML_tabFrame_E();
	showXHTML_body_E();

?>

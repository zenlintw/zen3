<?php
	/**
	 * 線上說明
	 *
	 * 建立日期：2004/11/24
	 * @author  Jeff Wang
	 * @version $Id: wmhelp.php,v 1.1 2010/02/24 02:38:55 saly Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/lib_wmhelp.php');

#======= Main ==============
	$o_help = new wmhelp($sysSession->school_id);
	$o_help->setHelpFilename($_GET['url']);

	//若是管理者的話，則提供編輯
	if (isHelpWriter())
	{
		if ($o_help->isHelpfileExists())
		{
			$lines = file($o_help->filepath);
			$content = implode('',$lines);
		}
		// begin html
		#===== 開始呈現 HTML
		include_once(sysDocumentRoot . '/lib/interface.php');
		include_once(sysDocumentRoot . '/lib/editor.php');
		include_once(sysDocumentRoot . '/lang/wmhelp.php');

			showXHTML_head_B($head);
			showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
			showXHTML_script('include', 'hotkey.js');
			//showXHTML_script('inline' , $js);
			showXHTML_head_E('');

			showXHTML_body_B('');
			showXHTML_form_B('method="post" action="wmhelp_write.php" enctype="multipart/form-data"', 'post1');
				showXHTML_input('hidden', 'helpfile', $_GET['url'], '', '');
				showXHTML_table_B('width="760" border="0" cellspacing="0" cellpadding="0"');
					showXHTML_tr_B('');
						showXHTML_td_B('');
							$ary = array();
							$ary[] = array($MSG['title'][$sysSession->lang], 'tabs1');
							showXHTML_tabs($ary, 1);
						showXHTML_td_E('');
					showXHTML_tr_E('');
					showXHTML_tr_B('');
						showXHTML_td_B('valign="top"');
							showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable" id="tabs1"');
								$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
								showXHTML_tr_B($col);
									showXHTML_td('align="right" nowrap="nowrap"', $MSG['helpfilename'][$sysSession->lang]);
									showXHTML_td('', $o_help->filename);
									showXHTML_td('', $msg);
								showXHTML_tr_E('');

								$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
								showXHTML_tr_B($col);
									showXHTML_td('align="right" nowrap="nowrap"', $MSG['write_content'][$sysSession->lang]);
									showXHTML_td_B('nowrap="nowrap"');
										//若是管理者的話，則提供編輯
										if (isHelpWriter()){
											$oEditor = new wmEditor;
											// $oEditor->setEditor('htmlarea');
										    preg_match_all('/\"([\w\/]+\.[gif|jpg|png]+)\"/i',$content,$arr);
											$wmhelp_base = "/base/{$sysSession->school_id}/door/wmhelp/{$sysSession->lang}/";
											$replaced_array = array();   //已置換過的圖
											if(count($arr[1])>0)
											{
											    for($i=0, $size=count($arr[1]);$i<$size;$i++)
											    {
											        if (substr($arr[1][$i],0,1) == '/') continue;
											        if (substr($arr[1][$i],0,7) == 'http://') continue;
											        if (!in_array($arr[1][$i],$replaced_array))
											        {
											             $content = str_replace($arr[1][$i],$wmhelp_base.$arr[1][$i],$content);
											             $replaced_array[] = $arr[1][$i];
											        }
											    }
											}
											$oEditor->setValue(stripslashes($content));
											$oEditor->setImageBrowser(true, '/wmhelp_manager.php');
											$oEditor->addContType('isHTML', 1);
											$oEditor->generate('content','650','400');
										}else{
											echo stripslashes($content);
										}
									showXHTML_td_E('');
									showXHTML_td('', $MSG['write_content_msg'][$sysSession->lang]);
								showXHTML_tr_E('');

								//若是管理者的話，則提供編輯
								if (isHelpWriter()){
									$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
									showXHTML_tr_B($col . ' id="upload_base"');
										showXHTML_td_B('nowrap="nowrap" colspan="3" align="center"');
											showXHTML_input('submit', '', $MSG['btn_save'][$sysSession->lang]  , '', 'class="cssBtn"');
											showXHTML_input('reset', '', $MSG['btn_reset'][$sysSession->lang]  , '', 'class="cssBtn"');
											showXHTML_input('button', '', $MSG['btn_cancel'][$sysSession->lang]  , '', 'class="cssBtn" onclick="window.close();"');

										showXHTML_td_E('');
									showXHTML_tr_E('');
								}
							showXHTML_table_E('');
						showXHTML_td_E('');
					showXHTML_tr_E('');
				showXHTML_table_E('');
			showXHTML_form_E('');
			showXHTML_body_E('');

		// end html
	}else{

		if ($o_help->isHelpfileExists())
		{

// dialog begin
$js = <<< BOF
	window.onload = function () {

		CalWin = showDialog("{$o_help->help_url}", false , "", true, "400px", "400px", "500px", "400px", "status=0, resizable=1, scrollbars=1");

	};
BOF;
	showXHTML_script('include', '/lib/common.js');
	showXHTML_script('inline', $js);
// dialog end

			// header("Location: {$o_help->help_url}");
			exit;
		}else{
			die("no help file");
		}
	}



?>
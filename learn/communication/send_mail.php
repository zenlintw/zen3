<?php
	/**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                                 *
	*                                                                                                 *
	*		Programmer: Amm Lee                                                                         *
	*		Creation  : 2003/09/23                                                                      *
	*		work for  : 寄信 給 學員                                                                       *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                          *
	*       @version $Id: send_mail.php,v 1.1 2009-06-25 09:26:25 edi Exp $*                                                                                                 *
	**************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/editor.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lang/class_manage.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '500100100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$to = $_POST['to'];
	$subject = stripslashes($_POST['subject']);
	$content = stripslashes($_POST['content']);

	$msg1 = $MSG['need_to1'][$sysSession->lang];
	$msg2 = $MSG['need_to2'][$sysSession->lang];

	// 簽名檔
	$RS = dbGetStMr('WM_user_tagline', '`serial`, `title`, `ctype`', "username='{$sysSession->username}'", ADODB_FETCH_ASSOC);
	$tagline = array();
	$tagline[-1] = $MSG['not_use_tagline'][$sysSession->lang];
	while (!$RS->EOF) {
		$tagline[$RS->fields['serial']] = $RS->fields['title'];
		$RS->MoveNext();
	}

	$js = <<< BOF
	var files = 1;
	var col = '';
	var MSG_TO1 = "{$msg1}"; MSG_TO2 = "{$msg2}";

	function trim(val) {
		var re = /\s/g;
		val = val.replace(re, '');
		return val;
	}

	/**
	 * Add a attachement
	 **/
	function more_attachs(){
		if (files >= 10){
			alert("{$MSG['msg_file_max'][$sysSession->lang]}");
			return;
		}
		var curNode = document.getElementById('upload_box');
		var nxtNode = document.getElementById('upload_base');
		var newNode = curNode.cloneNode(true);
		if (col.length == 0) {
			col = (curNode.className == "cssTrOdd") ? "cssTrOdd" : "cssTrEvn";
		}
		col = col == "cssTrOdd" ? "cssTrEvn" : "cssTrOdd";
		newNode.className = col;
		curNode.parentNode.insertBefore(newNode, nxtNode);
		newNode.getElementsByTagName("input")[0].value = "";
		files++;
	}

	/**
	 * delete a attachment
	 **/
	function cut_attachs(){
		if (files <= 1){
			alert("{$MSG['msg_file_no'][$sysSession->lang]}");
			return;
		}
		var curNode = document.getElementById('upload_base');
		var delNode = curNode.previousSibling;
		delNode.parentNode.removeChild(delNode);
		col = col == "cssTrOdd" ? "cssTrEvn" : "cssTrOdd";
		files--;
	}

	/**
	 * return message list
	 **/
	function goList() {
		location.replace("stud_list.php");
	}

	function chkData(obj) {
		var count = 0;

		var obj = document.post1;
		var to = obj.to.value;
		var s_len = obj.subject.value;
		var c_len = obj.content.value;

		if (s_len.length<=0 && c_len.length<=0){
			alert(MSG_TO2);
			return false;
		}

		var obj1 = document.getElementById('btn_submit');
		obj1.disabled = true;
		return true;
	}

	window.onload = function () {
		var obj = document.getElementById("post1");
		if (obj != null) {
			if (typeof(obj.to) == "object") {
				obj.to.focus();
			} else {
				obj.subject.focus();
			}
		}
	};

BOF;

	// 開始呈現 HTML
	showXHTML_head_B($MSG['edit_mail'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E('');

	showXHTML_body_B('');


	showXHTML_form_B('method="post" action="send_mail1.php" enctype="multipart/form-data" onsubmit="return chkData(this);"', 'post1');
		showXHTML_input('hidden', 'send_user', $_POST['send_user'], '', '');
		showXHTML_input('hidden', 'ticket', $_POST['ticket'], '', '');
		showXHTML_table_B('width="740" border="0" cellspacing="0" cellpadding="0"');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					$ary = array();
					$ary[] = array($MSG['edit_mail'][$sysSession->lang], 'tabs1');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E('');
			showXHTML_tr_E('');
			showXHTML_tr_B('');
				showXHTML_td_B('valign="top" class="cssTable"');
					showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" id="tabs1"');
						$col = ($col == 'class="cssTrOdd"') ? 'class="cssTrEvn"' : 'class="cssTrOdd"';
						showXHTML_tr_B($col);
							showXHTML_td('align="right" nowrap="nowrap"', $MSG['accept'][$sysSession->lang]);
							showXHTML_td('', $sysSession->school_name.$_POST['csid_group']);
							showXHTML_td('', '&nbsp;');
						showXHTML_tr_E('');

						$col = ($col == 'class="cssTrOdd"') ? 'class="cssTrEvn"' : 'class="cssTrOdd"';
						showXHTML_tr_B($col);
							showXHTML_td('align="right" nowrap="nowrap"', $MSG['write_from'][$sysSession->lang]);
							showXHTML_td('', "$sysSession->username ($sysSession->realname )");
							showXHTML_td('', '&nbsp;');
						showXHTML_tr_E('');

						$col = ($col == 'class="cssTrOdd"') ? 'class="cssTrEvn"' : 'class="cssTrOdd"';
						showXHTML_tr_B($col);
							showXHTML_td('align="right" ', $MSG['write_to'][$sysSession->lang]);
							showXHTML_td_B('');
								$i = 0;
								if (strlen($_POST['send_user']) > 0)
								{
									$send_array = explode(',', $_POST['send_user']);
									$num = count($send_array);

									for ($i = 0; $i < $num; $i++)
									{
										if (!preg_match(Account_format, $send_array[$i]) ||
											strlen($send_array[$i]) < sysAccountMinLen ||
											strlen($send_array[$i]) > sysAccountMaxLen) continue;
										list($last_name, $first_name) = dbGetStSr('WM_user_account', 'last_name,first_name', "username ='" . $send_array[$i] . "'", ADODB_FETCH_NUM);
										echo (($last_name . $first_name) == '') ? $send_array[$i] : checkRealname($first_name, $last_name);
										if ($i != ($num - 1))
										{
											echo ', ';
										}
									}
								}

								if ($i > 0) echo '<br>';
								showXHTML_input('text', 'to', $MSG['title12'][$sysSession->lang], '', 'id="to" class="cssInput" size="64" onclick="this.value=\'\'"');

							showXHTML_td_E('');
							showXHTML_td('', $MSG['write_to_msg'][$sysSession->lang]);
						showXHTML_tr_E('');

						$col = ($col == 'class="cssTrOdd"') ? 'class="cssTrEvn"' : 'class="cssTrOdd"';
						showXHTML_tr_B($col);
							showXHTML_td('align="right" nowrap="nowrap"', $MSG['write_subject'][$sysSession->lang]);
							showXHTML_td_B('nowrap="nowrap"');
								showXHTML_input('text', 'subject', $subject, '', 'class="cssInput" size="64" maxlength="200"');
							showXHTML_td_E('');
							showXHTML_td('', $MSG['write_subject_msg'][$sysSession->lang]);
						showXHTML_tr_E('');

						$col = ($col == 'class="cssTrOdd"') ? 'class="cssTrEvn"' : 'class="cssTrOdd"';
						showXHTML_tr_B($col);
							showXHTML_td('align="right" nowrap="nowrap"', $MSG['write_content'][$sysSession->lang]);
							showXHTML_td_B('nowrap="nowrap"');
								$oEditor = new wmEditor;
								$oEditor->setValue($content);
								$oEditor->addContType('isHTML', 1);
								$oEditor->generate('content');
							showXHTML_td_E('');
							showXHTML_td('', '');
						showXHTML_tr_E('');

                        $col = ($col == 'class="cssTrOdd"') ? 'class="cssTrEvn"' : 'class="cssTrOdd"';
						showXHTML_tr_B($col);
								showXHTML_td('align="right" nowrap="nowrap"', $MSG['write_tagline'][$sysSession->lang]);
								showXHTML_td_B('nowrap="nowrap"');
									showXHTML_input('select', 'tagline', $tagline, '', 'class="cssInput"');
								showXHTML_td_E('');
								showXHTML_td('', '');
						showXHTML_tr_E('');

						if (!isMobileBrowser()) {
							$col = ($col == 'class="cssTrOdd"') ? 'class="cssTrEvn"' : 'class="cssTrOdd"';
							showXHTML_tr_B($col . ' id="upload_box"');
								showXHTML_td('align="right" nowrap="nowrap"', $MSG['write_attachement'][$sysSession->lang]);
								showXHTML_td_B('nowrap="nowrap"');
									showXHTML_input('file', '', '', '', 'class="cssInput" size="60"');
							showXHTML_td_E('');
								// 單一上傳檔案size
								$min_size = '<span style="color: red; font-weight: bold">' . ini_get('upload_max_filesize') . '</span>';
								// 總上傳檔案size
								$max_size = '<span style="color: red; font-weight: bold">' . ini_get('post_max_size') . '</span>';

								$file_msg = str_replace('%MIN_SIZE%',$min_size,$MSG['write_attachment_msg'][$sysSession->lang]);

								$file_msg = str_replace('%MAX_SIZE%',$max_size,$file_msg);

								showXHTML_td('', $file_msg);
							showXHTML_tr_E('');
						}

						$col = ($col == 'class="cssTrOdd"') ? 'class="cssTrEvn"' : 'class="cssTrOdd"';
						showXHTML_tr_B($col . ' id="upload_base"');
							showXHTML_td_B('nowrap="nowrap" colspan="3"');
								$btn = $MSG['send'][$sysSession->lang];
								showXHTML_input('submit', '', $btn                                  , '', 'id="btn_submit" class="cssBtn"'); echo '&nbsp;&nbsp;';
								showXHTML_input('button', '', $MSG['title13'][$sysSession->lang]  , '', 'class="cssBtn" onclick="goList();"'); echo '&nbsp;&nbsp;';
								if (!isMobileBrowser()) {
									showXHTML_input('button', '', $MSG['more_attach'][$sysSession->lang], '', 'class="cssBtn" onclick="more_attachs();"'); echo '&nbsp;&nbsp;';
									showXHTML_input('button', '', $MSG['del_attach'][$sysSession->lang] , '', 'class="cssBtn" onclick="cut_attachs();"');
								}
							showXHTML_td_E('');
						showXHTML_tr_E('');

					showXHTML_table_E('');

				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');
	showXHTML_form_E('');
	showXHTML_body_E('');
?>

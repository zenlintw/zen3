<?php
	/**
	 *  �s��s�W���U�q���H
	 * @version $Id: stud_account_mail.php,v 1.1 2010/02/24 02:38:44 saly Exp $:
	 **/

	# ===================================================================================
	#
	# �P�_�ثe�n������
	#
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/editor.php');
	require_once(sysDocumentRoot . '/lib/file_api.php');

	$sysSession->cur_func = '400200100';
	$sysSession->restore();
	if (!aclVerifyPermission(400200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

    // �פJ�b������
	$target     = sysDocumentRoot . "/base/$sysSession->school_id/add_account.mail";
	$save_path  = sysDocumentRoot . "/base/$sysSession->school_id/attach/add_account";
	$arry[]     = array($MSG['edit_register_mail'][$sysSession->lang], 'addTable1');
	$go_href    = 'stud_account.php';
	$go_msg     = $MSG['return_register'][$sysSession->lang];

	# ===================================================================================
	# �R���Y�ӵ��w���ɦW
	#
	#	file_name	=>	�R���ɪ��ɦW
	#

	function deleteFile($file_name)
	{
		$target = $save_path . DIRECTORY_SEPARATOR . $file_name;
		if (is_file($target)) {
			@unlink($target);
		}
	}

	# ===================================================================================
	#	�D�{���}�l
	#
	#	mode		=>	�M�w�ثe�ާ@���A
	#

	switch ($_POST['mode'])
	{
		case 'save': // �s�ɮɪ������ʧ@
			// step.1 �B�z�W�Ǫ��ɮ׳���
			if (count($_FILES['uploads']['name']) > 0)
			{
				$ans1 = save_upload_file(trim($save_path), 0, 0);
			}
			// step.2 ���X�����զX�����@�ʧ��㪺�H
			if (empty($_POST['title']))
			{
				$Subject = $MSG['add_account_subject'][$sysSession->lang] . "\r\n";
			}
			else
			{
				$Subject = trim(stripslashes($_POST['title'])) . "\r\n";
			}

			if (empty($_POST['content']))
			{
				$Content = trim(stripslashes($MSG['add_account_subject'][$sysSession->lang]));
			}
			else
			{
				$Content = trim(stripslashes($_POST['content']));
			}
			$att_file = '';
			// step.3 �զX�s�W���ɮ�
			$file_amount = count($_FILES['uploads']['name']);
			for ($i = 0; $i < $file_amount; $i++)
			{
				if ($_FILES['uploads']['name'][$i] != '')
					$att_file = ($att_file == '')?$_FILES['uploads']['name'][$i]:$att_file . ',' . $_FILES['uploads']['name'][$i];
			}
			$whole_latter = $Subject . $Content;
			// step.4 �x�s�ɮ�
			if (file_exists($target))
			{
				unlink($target);
			}
			if ($fp = fopen($target, 'w'))
			{
				fwrite($fp, $whole_latter);
				fclose($fp);
				wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 0, 'manager', $_SERVER['PHP_SELF'], '�x�s�s�W���U�q���H success : ' . $target);
			}
			else
			{
				echo "File Not Save !!";
				wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 1, 'manager', $_SERVER['PHP_SELF'], '�x�s�s�W���U�q���H fail : ' . $target);
			}
			echo "<script>";
			echo 'window.location.replace("stud_account.php?msgtp=4");';
			echo "</script>";
			break;

		case 'delfile': // �R���ɮ׮ɪ������ʧ@
			$del_file = trim($save_path . DIRECTORY_SEPARATOR . $_POST['file_name']);
			// $del_file = iconv('UTF-8','Big5',$del_file);
			if (is_file($del_file))
			{
				deleteFile($del_file);
				wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 0, 'manager', $_SERVER['PHP_SELF'], "delete file : {$target}");
			}

			echo "<script>";
			echo 'window.location.replace("stud_account.php?msgtp=4");';
			echo "</script>";
			break;
	}

	$js = <<< BOE
	var col = '';
	function add_att(){
		var obj= document.getElementById("att_file");
        
        // #47097
        var msg='<br><input type=\"file\" name=\"uploads[]\" id=\"uploads[]\" class=\"cssInput\"><input type=\"button\" class=\"cssBtn\" value="{$MSG['del_att_file'][$sysSession->lang]}" onclick=\"delMe(this);\"><br>';
        var newdiv=document.createElement('span');
        newdiv.innerHTML=msg;
        obj.appendChild(newdiv);
        
		// var IH = obj.innerHTML;
		// var cnt = 1;	// �p�⦳�X�� (number)

       	// col = (col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';

		// IH += '<span><br><input type="file" name="uploads[]" id="uploads[]" class="cssInput"> '+
			  // '<input type="button" class="cssBtn" value="{$MSG['del_att_file'][$sysSession->lang]}"'+
			  // ' onclick="delMe(this);"><br></span>';

		// obj.innerHTML = IH;

	}

	function delMe(obj){
		 obj.parentNode.parentNode.removeChild(obj.parentNode);
	}

	function delFile(file_name){
		document.f1.mode.value = 'delfile';
		document.f1.file_name.value = file_name;
		document.f1.submit();
	}

	function chk_this(){
		document.f1.mode.value = 'save';
		document.f1.submit();
	}
BOE;
	showXHTML_script('inline', $js);

    showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" id="learn_result" class="cssTable"');
		// ��Ū���ɮפ�����l�H���ɮ�
		if (file_exists($target))
		{
			$fd = fopen($target, "r");
			// Ū�����D
			$subject = fgets($fd, 1024);
		}
		else
		{
			$subject = $MSG['add_account_subject'][$sysSession->lang];
		}

		showXHTML_input('hidden', 'deal_pages', $_POST['deal_pages'], '', '');
		showXHTML_input('hidden', 'mode', 'edit', '', '');		// �M�w�ثe�������ʧ@�Ҧ�
		showXHTML_input('hidden', 'file_name', '', '', '');		// �R���ɮ׮ɤ��ɦW
		showXHTML_input('hidden', 'msgtp', 4, '', '');

	    // �H����D
		showXHTML_tr_B('class="cssTrEvn"');
			showXHTML_td('align="right" width="12%" nowrap ',$MSG['title'][$sysSession->lang]);
			showXHTML_td_B();
				showXHTML_input('text', 'title', $subject, '', 'size="70" maxlength="100" class="cssInput"');
			showXHTML_td_E();
			showXHTML_td(' width="30%"',$MSG['message1'][$sysSession->lang]);
		showXHTML_tr_E();

        // ���e
		showXHTML_tr_B('class="cssTrOdd"');
			showXHTML_td('align="right" nowrap',$MSG['content'][$sysSession->lang]);
			showXHTML_td_B('nowrap="nowrap"');
				$oEditor = new wmEditor;
				if (file_exists($target))
				{
					while (!feof ($fd))
					{
						$content .= fgets($fd, 4096);
					}
				}
				else
				{
					$content = $MSG['add_account_body'][$sysSession->lang];
				}
				$oEditor->setValue($content);
				$oEditor->generate('content');
			showXHTML_td_E();
			showXHTML_td('valign="top"',$MSG['message4'][$sysSession->lang]);
		showXHTML_tr_E();
		// ����
		showXHTML_tr_B('class="cssTrEvn"');
			showXHTML_td('align="right" ',$MSG['att_file'][$sysSession->lang]);
			showXHTML_td_B(' id="att_file"');
				// ���C�X�w�g�������[�ɮ�
				if (is_dir($save_path))
				{
					$file_array  = getAllFile($save_path);
					$file_count  = count($file_array);
					$file_count1 = count($file_array) -1;

					for ($i = 0; $i < $file_count; $i++)
					{
						echo "<span>";
						showXHTML_input('text', "exist_$i", $file_array[$i], '', 'size="29" class="cssInput" disabled');
						showXHTML_input('button', '', $MSG['del_att_file'][$sysSession->lang], '', 'class="cssBtn" onclick="delFile(document.f1.exist_' . $i . '.value)"');
						echo "<br></span>";
					}
				}
				// �A�C�X�ݭn���[���ɮ�
				echo "<span><br>";
				showXHTML_input('file', $att_file, 'uploads', '', 'class="cssInput"');
				echo "<br></span>";
			showXHTML_td_E();

			// ��@�W���ɮ�size
			$min_size = '<span style="color: red; font-weight: bold">' . ini_get('upload_max_filesize') . '</span>';
			// �`�W���ɮ�size
			$max_size = '<span style="color: red; font-weight: bold">' . ini_get('post_max_size') . '</span>';

			$file_msg = str_replace(array('%MIN_SIZE%', '%MAX_SIZE%'),
			                        array($min_size, $max_size),
			                        $MSG['message2'][$sysSession->lang]);
			showXHTML_td('valign="top"', $file_msg);
		showXHTML_tr_E('');
		// �����
		showXHTML_tr_B('class="cssTrOdd"');
			showXHTML_td_B(' colspan="3" align="center"');
				showXHTML_input('button', '', $MSG['sure'][$sysSession->lang], '', 'class="cssBtn" align="right" valign="middle" nowrap  onclick="chk_this();"');
				showXHTML_input('reset', '', $MSG['cancel'][$sysSession->lang], '', 'class="cssBtn" align="right" valign="middle" nowrap ');
				// ��h����
				showXHTML_input('button', '', $MSG['more'][$sysSession->lang], '', 'class="cssBtn" onclick="add_att()"');
				showXHTML_input('button', '', $go_msg, '', 'class="cssBtn" align="right" valign="middle" nowrap onclick="window.location.replace(\'' . $go_href . '\');"');
			showXHTML_td_E();
		showXHTML_tr_E();

    showXHTML_table_E();
?>

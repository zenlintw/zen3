<?
	/**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                               *
	*                                                                                                 *
	*		Programmer: Amm Lee                                                                       *
	*		Creation  : 2003/09/23                                                                    *
	*		work for  : 刪除匯入帳號                                                                   *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                        *
	*       $Id: stud_remove3.php,v 1.1 2010/02/24 02:38:45 saly Exp $                                                                                          *
	**************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/stud_account.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '400300600';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	function fgetscsv($fp, $len, $delimiter){
		if (feof($fp)) return NULL;
		$data = fgets($fp, $len);
		return explode($delimiter, trim($data));
	}

#============================================================================================
	function Step1(){
		global $_FILE, $MSG, $sysSession, $_POST;

        if ($_FILES['cvsfile']['name'] == ''){
        	die('<script language="javascript">
        	        alert("' . $MSG['must_select_filename'][$sysSession->lang] . '");
        			location.replace("stud_remove.php?msgtp=3");
        	    </script>');
        }

		if (!is_uploaded_file($_FILES['cvsfile']['tmp_name'])) die('Upload file error');
		$filename = tempnam(dirname($_FILES['cvsfile']['tmp_name']), 'impf');
		rename($_FILES['cvsfile']['tmp_name'], $filename);

		$lang = ($_POST['file_format'] ? $_POST['file_format'] : $sysSession->lang);	// 設定匯入檔案所使用的語系
		$fp   = fopen($filename, 'r');
		$data = fgetscsv($fp, 4096, ',');

		//	去除UTF-8的檔頭 Begin
		if ($lang == 'UTF-8' && strtolower(bin2hex(substr($data[0], 0 , 3))) == 'efbbbf')
			$data[0] = substr($data[0], 3);
		//	去除UTF-8的檔頭 End

		$datalen = count($data);

        $js = <<< BOF

        var isSelect = false;
        function Switch_sel(){
        	isSelect = true;
        }

        function check_field(){
        	if (!isSelect) {
        		alert("{$MSG['title68'][$sysSession->lang]}");
        	}

			if (isSelect) {
	        	var obj2 = document.getElementById('btn_submit');
	        	obj2.disabled = true;
			}
        	return isSelect;
        }

        function Cancel(){
        	document.forms[0].step.value='3';
        	document.forms[0].submit();
        }
BOF;

    $arry[] = array($MSG['title61'][$sysSession->lang], 'delTable1');

    showXHTML_head_B($MSG['delete_account'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
	    showXHTML_table_B('border="0" cellspacing="0" cellpadding="0"');
            showXHTML_form_B('action="stud_remove3.php" method="post" enctype="multipart/form-data" style="display:none" onsubmit="return check_field();"', 'DelManualFm');
			showXHTML_tr_B();
				showXHTML_td_B();
                    showXHTML_tabs($arry, 1);
				showXHTML_td_E();
			showXHTML_tr_E();

            showXHTML_tr_B();
				showXHTML_td_B('valign="top" ');
                    showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" id="delTable1" style="display:block" class="cssTable"');

                        showXHTML_tr_B('class="cssTrHead"');
				            showXHTML_td('align="center" nowrap ', $MSG['title62'][$sysSession->lang]);
				            showXHTML_td('align="center" nowrap ', $MSG['title63'][$sysSession->lang]);
				            showXHTML_td('align="center" nowrap ', $MSG['title64'][$sysSession->lang]);
			            showXHTML_tr_E();

		for($i=0; $i<$datalen; $i++){
			$j = $i + 1;
			$col = $col == 'class="cssTrEvn"' ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
			$val = $data[$i];

			showXHTML_tr_B($col);
			    showXHTML_td('', $j);
			    showXHTML_td_B();
			    	showXHTML_input('radio', 'yes2im', array($i => ''), '-1', 'onclick="Switch_sel()"');
                 showXHTML_td_E();
                 showXHTML_td('', ($lang == 'Big5' || $lang == 'GB2312') ? iconv($lang,'UTF-8',$val) : $val);
			showXHTML_tr_E();
		}
		$col = $col == 'class="cssTrEvn"' ? 'class="cssTrOdd"' : 'class="cssTrEvn"';

				showXHTML_tr_B($col);
				    showXHTML_td_B('colspan="4" align="center"');
					    showXHTML_input('hidden', 'step', '2', '', '');
					    showXHTML_input('hidden', 'ticket', $_POST['ticket'], '', '');
					    showXHTML_input('hidden', 'impfile', $filename, '', '');
					    showXHTML_input('hidden', 'lang', $lang, '', '');
					    showXHTML_input('submit', '', $MSG['title65'][$sysSession->lang], '', 'id="btn_submit" class="cssBtn"');
					    showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang], '', ' onclick="Cancel();" class="cssBtn"');
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_td_E();
	showXHTML_tr_E();
	showXHTML_form_E();
showXHTML_table_E();
showXHTML_body_E();

	}

#============================================================================================
	function Step2(){
	    global $MSG, $sysSession, $_POST;

        $js = <<< BOF
        function display(){
        	var obj = document.getElementsByTagName('tr');
        	var sw = false;
        	for(i=0; i<obj.length; i++){
        		if (obj[i].className != 'cssTrOdd') continue;
        		if (sw) obj[i].className = 'cssTrEvn';
        		sw = !sw;
        	}

            if (confirm("{$MSG['title60'][$sysSession->lang]}")){
        	    var obj2 = document.getElementById('btn_submit2');
        	    obj2.disabled = true;

        	    return true;
        	}else{
                return false;
            }
        }
BOF;
        $arry[] = array($MSG['title61'][$sysSession->lang], 'delTable1');

        showXHTML_head_B($MSG['delete_account'][$sysSession->lang]);
        showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
        showXHTML_script('inline', $js);
        showXHTML_head_E();
        showXHTML_body_B();
            showXHTML_form_B('action="stud_remove2.php" method="post" enctype="multipart/form-data" style="display:block" onsubmit="return display();"', 'Delimport');
           	    showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="0" id="ListTable"');
                    showXHTML_tr_B();
           				showXHTML_td_B();
           					showXHTML_tabs($arry, 1);
           				showXHTML_td_E();
        		    showXHTML_tr_E();

                    $col = $col == 'class="cssTrEvn"' ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
        		    showXHTML_tr_B($col);
        		        showXHTML_td_B();
        		            showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" id="ClassList" class="cssTable"');
                		        $col = $col == 'class="cssTrEvn"' ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                		        showXHTML_input('hidden', 'ticket', $_POST['ticket'], '', '');
                		        showXHTML_tr_B($col);
        		                    showXHTML_td_B();
        		                        showXHTML_input('submit', '', $MSG['ok_del'][$sysSession->lang], '', 'id="btn_submit2" class="cssBtn"');
        		                    showXHTML_td_E();
                                showXHTML_tr_E();
                		        $col = $col == 'class="cssTrEvn"' ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                		        showXHTML_tr_B($col);
        		                    showXHTML_td_B();
                        		        $fp    = fopen($_POST['impfile'], 'r');
										$lang  = $_POST['lang'];	// 使用者匯入檔案的格式
										$line1 = true;
                                    	while($data = fgetscsv($fp, 4096, ',')){
                                    		if ($line1) {
                                    			if ($lang == 'UTF-8' && strtolower(bin2hex(substr($data[0], 0 , 3))) == 'efbbbf')
																$data[0] = substr($data[0], 3);
                                    			$line1 = false;
                                    		}
                                   	    	$da = $data[$_POST['yes2im']];

                                   	    	if ($da != ''){
                                    		    echo '<input type="hidden" name="nla[]" value="' . $da . '">' . $data[$_POST['yes2im']] . "<br>\n";
                                            }
                                    	}
                                    	fclose($fp);
                                     showXHTML_td_E();
                                showXHTML_tr_E();

                            showXHTML_table_E();

                        showXHTML_td_E();
                    showXHTML_tr_E();

            	showXHTML_table_E();
            showXHTML_form_E();
        showXHTML_body_E();

		@unlink($_POST['impfile']);
	}

#============================================================================================
	function Step3(){
		global $_POST;
		@unlink($_POST['impfile']);
		header('Location: /academic/stud/stud_remove.php?msgtp=3');
	}

#============================================================================================
#					主程式
#============================================================================================

	switch($_POST['step']){
		case 2:
			Step2(); break;
		case 3:
			Step3(); break;
		default:
			Step1(); break;
	}
?>

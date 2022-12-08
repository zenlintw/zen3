<?php
    /**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                               *
	*                                                                                                 *
	*		Programmer: Amm Lee                                                                       *
	*		Creation  : 2003/09/23                                                                    *
	*		work for  : 刪除連續帳號                                                                     *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                        *
	*       $Id: stud_remove_serial.php,v 1.1 2010/02/24 02:38:45 saly Exp $                                                                                          *
	**************************************************************************************************/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '1100400400';
	$sysSession->restore();
	if (!aclVerifyPermission(1100400400, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$sysAccountMinLen = sysAccountMinLen;
	$sysAccountMaxLen = sysAccountMaxLen;
	$js = <<< EOF
	var sysAccountMinLen = {$sysAccountMinLen}, sysAccountMaxLen = {$sysAccountMaxLen};

	function chkData() {
		var obj = document.getElementById("delFm1");

		var re = /^[a-zA-Z0-9]\w*$/;
		var re1 = /^[a-zA-Z0-9][a-zA-Z0-9]*[_-]?[a-zA-Z0-9]$/;
		var leng = 0;
		var str = "";

		if (obj == null) return false;

		obj.header.value = trim(obj.header.value);
		/* 檢查有沒有設定前置字元 (character) */
		if (obj.header.value.length <= 0) {
			obj.header.focus();
			alert("{$MSG['js_msg01'][$sysSession->lang]}");
			return false;
		}
		/* 檢查前置字元是不是英文字 (character)  */
		if (!re.test(obj.header.value.substr(0,1))) {
			obj.header.focus();
			alert("{$MSG['js_msg02'][$sysSession->lang]}");
			return false;
		}

		/* 檢查底線出現的次數 (number) */
		str = obj.header.value + obj.tail.value;


        if ((str.split("-")).length > 2) {
			alert("{$MSG['js_msg09'][$sysSession->lang]}");
			return false;
		}

        /* 檢查底線 and 減號 有無出現在 字尾 */
        if( (obj.tail.value.substring(obj.tail.value.length-1,obj.tail.value.length) == '_') || (obj.tail.value.substring(obj.tail.value.length-1,obj.tail.value.length) == '-')){
            obj.tail.focus();
            alert("{$MSG['js_msg10'][$sysSession->lang]}");
			return false;
        }

		/* 檢查長度是不是數字 (number) */
		re = /^\d+$/;
		obj.len.value = trim(obj.len.value);

		if (!re.test(obj.len.value)) {
			obj.len.focus();
			alert("{$MSG['js_msg04'][$sysSession->lang]}");
			return false;
		}

		/* 檢查帳號個數是不是數字 (number) */
		obj.first.value = trim(obj.first.value);
		if (!re.test(obj.first.value)) {
			obj.first.focus();
			alert("{$MSG['js_msg05'][$sysSession->lang]}");
			return false;
		}

		/* 檢查帳號個數是不是數字 (number) */
		obj.last.value = trim(obj.last.value);
		if (!re.test(obj.last.value)) {
			obj.last.focus();
			alert("{$MSG['js_msg06'][$sysSession->lang]}");
			return false;
		}

		/* 檢查帳號的總長度 (total length) */
		leng = obj.header.value.length + obj.tail.value.length + parseInt(obj.len.value);

		if (leng < sysAccountMinLen) {
			alert("{$MSG['js_msg07'][$sysSession->lang]}");
			return false;
		}
		if (leng > sysAccountMaxLen) {

			alert("{$MSG['js_msg08'][$sysSession->lang]}");
			return false;
		}

        var obj2 = document.getElementById('btn_submit');

        obj2.disabled = true;

		return true;
	}
EOF;
	showXHTML_script('inline', $js);
	showXHTML_script('include', '/lib/common.js');
    showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" id="learn_result" class="cssTable"');

        showXHTML_tr_B('class="cssTrEvn"');
        	$account_msg = str_replace(array('%MIN%', '%MAX%'),
        	                           array(sysAccountMinLen, sysAccountMaxLen),
        	                           $MSG['del_help01'][$sysSession->lang]);
			showXHTML_td('colspan="4" ', $account_msg);
		showXHTML_tr_E();
		showXHTML_tr_B('class="cssTrHead"');
			showXHTML_td('', $MSG['header'][$sysSession->lang]);
			showXHTML_td('', $MSG['number'][$sysSession->lang]);
			showXHTML_td('', $MSG['tail'][$sysSession->lang]);
			showXHTML_td('', $MSG['length'][$sysSession->lang]);
		showXHTML_tr_E();
		showXHTML_tr_B('class="cssTrEvn"');
			showXHTML_td_B();
				showXHTML_input('text', 'header', '', '', 'size="15" class="cssInput"');
			showXHTML_td_E();
			showXHTML_td_B(' nowrap');
				echo $MSG['first'][$sysSession->lang];
				showXHTML_input('text', 'first', '1', '', 'size="5" class="cssInput"');
				echo $MSG['last'][$sysSession->lang];
				showXHTML_input('text', 'last', '100', '', 'size="5" class="cssInput"');
			showXHTML_td_E();
			showXHTML_td_B(' nowrap');
				showXHTML_input('text', 'tail', '', '', 'size="15" class="cssInput"');
			showXHTML_td_E();
			showXHTML_td_B(' nowrap');
				// showXHTML_input('text', 'len', '3', '', 'size="10" class="cssInput"');
				showXHTML_input('select', 'len', array_range(1,5), 3, 'class="cssInput"');
			showXHTML_td_E($MSG['len'][$sysSession->lang]);
		showXHTML_tr_E();
		showXHTML_tr_B('class="cssTrOdd"');
			showXHTML_td_B('colspan="4" align="center"');
				$ticket = md5($sysSession->ticket . 'Delete' . $sysSession->school_id . $sysSession->username);
				showXHTML_input('hidden', 'ticket', $ticket, '', '');
				showXHTML_input('submit', '', $MSG['delete_account'][$sysSession->lang], '', 'id="btn_submit" class="cssBtn"');
			showXHTML_td_E();
		showXHTML_tr_E();

    showXHTML_table_E();
?>

<?php
    /**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                               *
	*                                                                                                 *
	*		Programmer: Amm Lee                                                                       *
	*		Creation  : 2003/09/23                                                                    *
	*		work for  : 新增連續帳號                                                                     *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                        *
	*       version   :  $Id: stud_addserial.php,v 1.1 2010/02/24 02:38:44 saly Exp $
	*                                                                                                 *
	**************************************************************************************************/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '400300300';
	$sysSession->restore();
	if (!aclVerifyPermission(400300300, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$sysAccountMinLen = sysAccountMinLen;
	$sysAccountMaxLen = sysAccountMaxLen;
	$Account_format   = Account_format;
	$min_len          = str_replace('%MIN%', sysAccountMinLen, $MSG['js_msg07'][$sysSession->lang]);	// 帳號最小的限制
	$max_len          = str_replace('%MAX%', sysAccountMaxLen, $MSG['js_msg08'][$sysSession->lang]);	// 帳號最大的限制

	$js = <<< BOE
	var MSG_COUNT_OVER = "{$MSG['msg_count_over'][$sysSession->lang]}";
	var sysAccountMinLen = {$sysAccountMinLen}, sysAccountMaxLen = {$sysAccountMaxLen};
 	var MSG_MIN_LEN = "{$min_len}";
 	var MSG_MAX_LEN = "{$max_len}";
	function chkData() {
		var obj = document.getElementById("addFm1");
		var re = /^[a-zA-Z0-9]\w*$/;
		var re1 = {$Account_format};
		var leng = 0;
		var str = "";
		var re2 = "";

		if (obj == null) return false;

		try {
			obj.header.value = trim(obj.header.value);
			/*
			*檢查有沒有設定前置字元
			*/
			if (obj.header.value.length <= 0) {
				obj.header.focus();
				throw "{$MSG['js_msg01'][$sysSession->lang]} ";
			}
			/*
			* 檢查前置字元是不是英文字
			*/
			if (!re.test(obj.header.value.substr(0,1))) {
				obj.header.focus();
				throw "{$MSG['js_msg02'][$sysSession->lang]}";
			}

			/*
			 * compute the total number of underline or minus
			*/
			str = obj.header.value + obj.tail.value;
			var underline = 0;
			var minus = 0;
			for (var i =0;i<str.length;i++){
				if (str.charAt(i) == '-'){
						minus++;
				}
		   	}
			var total_sum = underline+minus;

			if (total_sum > 1){
				throw "{$MSG['js_msg11'][$sysSession->lang]}";
			}

			/*
			 * check the total number of underline
			 */

			if (underline > 1){
				throw "{$MSG['js_msg11'][$sysSession->lang]}";
			}

			if (minus > 1){
				throw "{$MSG['js_msg11'][$sysSession->lang]}";
			}

            /*
            * 檢查底線 and 減號 有無出現在 字尾
            */
            if( (obj.tail.value.substring(obj.tail.value.length-1,obj.tail.value.length) == '_') || (obj.tail.value.substring(obj.tail.value.length-1,obj.tail.value.length) == '-')){
                obj.tail.focus();
                throw "{$MSG['js_msg10'][$sysSession->lang]}";
            }

			/*
			* 檢查長度是不是數字
			*/
			re2 = /^\d+$/;
			obj.len.value = trim(obj.len.value);
			if (!re2.test(obj.len.value)) {
				obj.len.focus();
				throw "{$MSG['js_msg04'][$sysSession->lang]}";
			}

			/*
			 * 檢查帳號個數是不是數字
			*/
			obj.first.value = trim(obj.first.value);
			if (!re2.test(obj.first.value)) {
				obj.first.focus();
				throw "{$MSG['js_msg05'][$sysSession->lang]}";
			}

			/*
			 *檢查帳號個數是不是數字
			 */
			obj.last.value = trim(obj.last.value);
			if (!re2.test(obj.last.value)) {
				obj.last.focus();
				throw "{$MSG['js_msg06'][$sysSession->lang]}";
			}

			/*
			* 檢查帳號的總長度 (由帳號個數來檢查)
			*/
			obj.tail.value = trim(obj.tail.value);

			if (obj.last.value.length > parseInt(obj.len.value)) {
				obj.last.focus();
				throw MSG_COUNT_OVER;
			}

			/*
			* 檢查帳號的總長度
			*/
			leng = obj.header.value.length + obj.tail.value.length + parseInt(obj.len.value);
			if (leng < sysAccountMinLen) {
				throw MSG_MIN_LEN;
			}
			if (leng > sysAccountMaxLen) {
				throw MSG_MAX_LEN;
			}
		} catch(ex) {
			alert(ex);
			return false;
		}

        var obj2 = document.getElementById('btn_submit');
        obj2.disabled = true;

		return true;
	}
BOE;
	showXHTML_script('include', '/lib/common.js');
	showXHTML_script('inline', $js);

    showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" id="learn_result" class="cssTable"');
        showXHTML_tr_B('class="cssTrEvn"');
        	$account_msg = str_replace(array('%MIN%', '%MAX%'),
        	                           array(sysAccountMinLen, sysAccountMaxLen),
        	                           $MSG['create_help01'][$sysSession->lang]);
			showXHTML_td('colspan="4" nowrap', $account_msg);
		showXHTML_tr_E();
		showXHTML_tr_B('class="cssTrHead"');
			showXHTML_td('', $MSG['header'][$sysSession->lang]);
			showXHTML_td('', $MSG['number'][$sysSession->lang]);
			showXHTML_td('', $MSG['tail'][$sysSession->lang]);
			showXHTML_td('', $MSG['length'][$sysSession->lang]);
		showXHTML_tr_E();
		showXHTML_tr_B('class="cssTrEvn"');
			showXHTML_td_B('nowrap');
				showXHTML_input('text', 'header', '', '', 'size="15" class="cssInput"');
			showXHTML_td_E();
			showXHTML_td_B('nowrap');
				echo $MSG['first'][$sysSession->lang];
				showXHTML_input('text', 'first', '1', '', 'size="5" class="cssInput"');
				echo $MSG['last'][$sysSession->lang];
				showXHTML_input('text', 'last', '100', '', 'size="5" class="cssInput"');
			showXHTML_td_E();
			showXHTML_td_B('nowrap');
				showXHTML_input('text', 'tail', '', '', 'size="15" class="cssInput"');
			showXHTML_td_E();
			showXHTML_td_B('nowrap');
				// showXHTML_input('text', 'len', '3', '', 'size="10" class="cssInput"');
				showXHTML_input('select', 'len', array_range(1,5), 3, 'class="cssInput"');
			showXHTML_td_E($MSG['len'][$sysSession->lang]);
		showXHTML_tr_E();
		showXHTML_tr_B('class="cssTrOdd"');
			showXHTML_td_B('colspan="4" align="center"');
				$ticket = md5($sysSession->ticket . $sysSession->school_id . $sysSession->username . 'add');
				showXHTML_input('hidden', 'ticket', $ticket, '', '');
				showXHTML_input('submit', '', $MSG['create_account'][$sysSession->lang], '', 'id="btn_submit" class="cssBtn"');
				showXHTML_input('reset', '', $MSG['cancel'][$sysSession->lang], '', 'class="cssBtn"');
			showXHTML_td_E();
		showXHTML_tr_E();
    showXHTML_table_E();
?>
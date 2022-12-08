<?php
    /**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                               *
	*                                                                                                 *
	*		Programmer: Amm Lee                                                                       *
	*		Creation  : 2003/09/23                                                                    *
	*		work for  : 新增不連續帳號                                                                     *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                        *
	*       version   :  $Id: stud_addabnormal.php,v 1.1 2010/02/24 02:38:44 saly Exp $
	*                                                                                                 *
	**************************************************************************************************/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/jscalendar/calendar.php');

	$sysSession->cur_func = '400300100';
	$sysSession->restore();
	if (!aclVerifyPermission(400300100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$sysAccountMinLen = sysAccountMinLen;
	$sysAccountMaxLen = sysAccountMaxLen;
	// 帳號最小的限制
	$min_len = str_replace('%MIN%', sysAccountMinLen, $MSG['js_msg07'][$sysSession->lang]);
	// 帳號最大的限制
	$max_len = str_replace('%MAX%', sysAccountMaxLen, $MSG['js_msg08'][$sysSession->lang]);

	$Account_format = Account_format;
	$js = <<< BOE
	var MSG_DATE_ERROR = "{$MSG['msg_date_error'][$sysSession->lang]}";
	var sysAccountMinLen = {$sysAccountMinLen}, sysAccountMaxLen = {$sysAccountMaxLen};
 	var MSG_MIN_LEN = "{$min_len}";
 	var MSG_MAX_LEN = "{$max_len}";
	function chkData2() {
		var node = document.getElementById("addFm2");
		if (node == null) return false;
		var re = {$Account_format};
		var leg;
		var i;

		// 帳號使用期限,截止日要大於開始日
		if (node.ck_begin_date.checked && node.ck_end_date.checked) {
			val1 = node.begin_date.value.replace(/[\D]/ig, '');
			val2 = node.end_date.value.replace(/[\D]/ig, '');
			if (parseInt(val1) >= parseInt(val2)) {
				alert(MSG_DATE_ERROR);
				node.begin_date.focus();
				return false;
			}
		}

		// account begin
		if (addFm2.userlist.value.length == 0){

			alert("{$MSG['title80'][$sysSession->lang]}");
			addFm2.userlist.focus();
			return false;
		}

		data = addFm2.userlist.value.split(/[\\r\\n]+/);
		for (i = 0, leg = data.length;i < leg ; i++){
			if (data[i] != ''){

				data1 = data[i].toString();
				data2 = data1.split(",");
				/* data2[0] -> account data2[1] -> password */
				data3 = data2[0].toString();

				/*
				*檢查帳號的總長度 最少 2個字元  最多 20個字元 (limit)
				*/
				if (data3.length < sysAccountMinLen){
					alert(MSG_MIN_LEN);
					return false;
				}

				if (data3.length > sysAccountMaxLen){
					alert(MSG_MAX_LEN);
					return false;
				}

				/*
				* 檢查底線 and 減號 有無出現在 字尾
				*/
				if( (data3.substring(data3.length-1,data3.length) == '_') || (data3.substring(data3.length-1,data3.length) == '-')){
					alert("{$MSG['js_msg10'][$sysSession->lang]}");
					return false;
				}

				/*
					*檢查帳號的規則是否正確
				*/
                if (!re.test(data3.replace(/_/g, ''))) {
					alert("{$MSG['js_msg11'][$sysSession->lang]}");
					return false;
				}

				/*
				* compute the total number of underline or minus
				*/
				var underline = 0;
				var minus = 0;
				var total_sum = 0;
				for (var j =0;j<data3.length;j++){
					if (data3.charAt(j) == '-'){
						minus++;
					}
				}
				total_sum = underline+minus;

				if (total_sum > 1){
					alert("{$MSG['js_msg11'][$sysSession->lang]}");
					return false;
				}

			if (underline > 1){
					alert("{$MSG['js_msg03'][$sysSession->lang]}");
					return false;
				}

				if (minus > 1){
					alert("{$MSG['js_msg09'][$sysSession->lang]}");
					return false;
				}
			}
		}

        var obj2 = document.getElementById('btn_submit2');
        obj2.disabled = true;

	    return true;
	}

	// 秀日曆的函數(checkbox)
	function showDateInput(objName, state) {
		var obj = document.getElementById(objName);
		if (obj != null) {
			obj.style.display = state ? "" : "none";
		}
	}

	// 秀日曆的函數
	function Calendar_setup(ifd, fmt, btn, shtime) {
		Calendar.setup({
			inputField  : ifd,
			ifFormat    : fmt,
			showsTime   : shtime,
			time24      : true,
			button      : btn,
			singleClick : true,
			weekNumbers : false,
			step        : 1
		});
	}

	window.onload = function() {
		Calendar_setup("begin_date", "%Y-%m-%d", "begin_date", false);
		Calendar_setup("end_date"  , "%Y-%m-%d", "end_date"  , false);
	};
BOE;
	// 產生並載入萬年曆的物件，並且設定所需的語系
	$calendar = new DHTML_Calendar('/lib/jscalendar/', $sysSession->lang, 'calendar-system');
	$calendar->load_files();
	showXHTML_script('inline', $js);

    showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" id="learn_result" class="cssTable"');
        $col = ($col == 'class="cssTrOdd"') ? 'class="cssTrEvn"' : 'class="cssTrOdd"';
		// 帳號使用期限
        showXHTML_tr_B($col);
           showXHTML_td_B('colspan="2"');
            echo $MSG['title87'][$sysSession->lang];
            echo "<br /> {$MSG['first'][$sysSession->lang]}" . $MSG['title1'][$sysSession->lang];
			$val = sprintf('%04d-%02d-%02d', $date['year'], $date['mon'], $date['mday']);
			showXHTML_input('checkbox', 'ck_begin_date', 'begin_date', '', 'id="ck_begin_date" onclick="showDateInput(\'span_begin_date' . '\', this.checked)"');
			echo $MSG['msg_date_start'][$sysSession->lang];
			echo '<span id="span_begin_date" style="display: none;">';
			showXHTML_input('text', 'begin_date', $val, '', 'id="begin_date" readonly="readonly" class="cssInput"');
			echo '</span>';
			echo '<br />'.$MSG['last'][$sysSession->lang] , $MSG['title1'][$sysSession->lang];
			showXHTML_input('checkbox', 'ck_end_date', 'begin_date', '', 'id="ck_end_date" onclick="showDateInput(\'span_end_date' . '\', this.checked)"');
			echo $MSG['msg_date_stop'][$sysSession->lang];
			echo '<span id="span_end_date" style="display: none;">';
			showXHTML_input('text', 'end_date', $val, '', 'id="end_date" readonly="readonly" class="cssInput"');
			echo '</span>';
           showXHTML_td_E();
        showXHTML_tr_E();


        $col = ($col == 'class="cssTrOdd"') ? 'class="cssTrEvn"' : 'class="cssTrOdd"';

        showXHTML_tr_B($col);
			showXHTML_td_B('valign="top" ');
				$ticket = md5('AddManual' . $sysSession->ticket . $sysSession->school_id . $sysSession->username);
				showXHTML_input('hidden', 'ticket', $ticket, '', '');
				showXHTML_input('textarea', 'userlist', '', '', 'cols="35" rows="15" class="cssInput"');
			showXHTML_td_E();
			showXHTML_td_B('valign="top" ');
                            echo '<div>';
				echo $MSG['create_help02'][$sysSession->lang];
				showXHTML_input('textarea', '', "userid1,password1\nuserid2,password2", '', 'cols="22" rows="5" disabled class="cssInput"');
                            echo '</div>';
                            echo '<div>';
				echo $MSG['create_help03'][$sysSession->lang];
				showXHTML_input('textarea', '', "userid1\nuserid2", '', 'cols="22" rows="5" disabled class="cssInput"');
                            echo '</div>';
			showXHTML_td_E();
		showXHTML_tr_E();
		showXHTML_tr_B();
			showXHTML_td_B('colspan="2" align="center" valign="top" class="cssTrOdd"');
				showXHTML_input('submit', '', $MSG['create_account'][$sysSession->lang], '', 'id="btn_submit2" class="cssBtn"');
			    showXHTML_input('reset', '', $MSG['cancel'][$sysSession->lang], '', 'class="cssBtn"');
			showXHTML_td_E();
		showXHTML_tr_E();
    showXHTML_table_E();
?>
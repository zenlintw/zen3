<?php
	/**
	 * 設定個人資料
	 *
	 * 建立日期：2003/02/21
	 * @author  ShenTing Lin
	 * @version $Id: modify_stud_info1.php,v 1.1 2010/02/24 02:38:44 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/jscalendar/calendar.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lang/personal.php');
	require_once(sysDocumentRoot . '/lang/register.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '400400300';
	$sysSession->restore();
	if (!aclVerifyPermission(400400300, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$username = $_GET['username'] ? preg_replace('/[^\w.-]+/', '', $_GET['username']) : $sysSession->username;

	if ($username == sysRootAccount && $sysSession->username != sysRootAccount) {
		wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 2, 'manager', $_SERVER['PHP_SELF'], '"' . sysRootAccount . '" account only can be modified by himself.');
		die( '"'. sysRootAccount . '" account only can be modified by himself.');
	}

	// 不能隱藏的欄位
	$not_hidden = array('last_name','first_name','email');

	// 日期
	$date = getdate();

	// mail 規則
	$mail_Rule = sysMailRule;

	$js = <<< BOF
	var MSG_NotLoad         = "{$MSG['not_load'][$sysSession->lang]}";
	var MSG_TooLarge        = "{$MSG['too_large'][$sysSession->lang]}";
	var MSG_CheckPassword   = "{$MSG['check_password'][$sysSession->lang]}";
	var MSG_FirstName       = "{$MSG['fill_first_name'][$sysSession->lang]}";
	var MSG_LastName        = "{$MSG['fill_last_name'][$sysSession->lang]}";
	var MSG_Email           = "{$MSG['fill_email'][$sysSession->lang]}";
	var MSG_Illegal         = "{$MSG['email_illegal'][$sysSession->lang]}";
	var MSG_PW_ILLEGAL      = "{$MSG['msg_passwd_illegal'][$sysSession->lang]}";
	var MSG_DATE_ERROR      = "{$MSG['msg_date_error'][$sysSession->lang]}";
	var MSG_FN_ERROR        = "{$MSG['msg_first_name_error'][$sysSession->lang]}";
	var MSG_LN_ERROR        = "{$MSG['msg_last_name_error'][$sysSession->lang]}";
	var MSG_TEL             = "{$MSG['fill_tel'][$sysSession->lang]}";

	function chgPic() {
		var re;
		var obj1 = document.getElementById("PicRoom");
		var obj2 = document.getElementById("picture");
		var node = null;
		var txt  = "";

		if ((obj1 == null) || (obj2 == null)) return false;
		if (trim(obj2.value) == '') return false;
		txt = obj2.value;
		re = /^[a-zA-Z]:/i;
		if (txt.match(re) != null) {
			re = /\\\\/ig;
			txt = "file:///" + txt.replace(re, "/");
		}
		obj1.innerHTML = "";

		node = document.createElement("img");
		node.setAttribute("id", "MyPic");
		node.setAttribute("borer", "0");
		node.setAttribute("align", "absmiddle");
		node.setAttribute("loop", "0");
		node.onload = picReSize;
		node.onerror = function () {
			if (obj1.innerHTML != "") {
				obj1.innerHTML = "";
				alert(MSG_NotLoad);
			}
		};
		obj1.appendChild(node);
		node.src = txt;
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

	function chkData() {
        // personal informaiton form name
        var obj = document.getElementById("setForm");
		if (obj == null) return false;
		var em = {$mail_Rule};
		var val1 = "", val2 = "";

		// check帳號使用期限
		if (obj.ck_begin_date.checked && obj.ck_end_date.checked) {
			val1 = obj.begin_date.value.replace(/[\D]/ig, '');
			val2 = obj.end_date.value.replace(/[\D]/ig, '');
			if (parseInt(val1) >= parseInt(val2)) {
				alert(MSG_DATE_ERROR);
				obj.begin_date.focus();
				return false;
			}
		}

		// check password
		val1 = obj.password.value;
		val2 = obj.repassword.value;
		if (val1 != val2) {
			alert(MSG_CheckPassword);
			obj.password.focus();
			obj.password.value = "";
			obj.repassword.value = "";
			return false;
		} else if (val1 != '') {
			var re = /^[\\x20-\\xFF]{6,}$/g;
			if (val1.match(re) == null) {
				alert(MSG_PW_ILLEGAL);
				obj.password.focus();
				obj.password.value = "";
				obj.repassword.value = "";
				return false;
			}
		}

        var obj2 = document.getElementById("btn_submit");
        obj2.disabled = true;
                
        document.setForm.submit();
                
	}

	function selFunc(actType){
    	var obj = document.getElementById('setForm');
    	var nodes = obj.getElementsByTagName('input');
    	var txt = '';
    	for(var i=0; i<nodes.length; i++){
    		if ((nodes.item(i).type == 'checkbox') && (nodes.item(i).name.indexOf('hid') == 0)) {

    			nodes.item(i).checked = actType;
    			selState(nodes[i]);
            }
    	}

    	//  全選 or 全消 (checkbox title)
        var ck_title = document.getElementById("ck");
        if (actType){
            ck_title.title = "{$MSG['cancel_all'][$sysSession->lang]}";
        }else{
            ck_title.title = "{$MSG['select_all'][$sysSession->lang]}";
        }

    }

	 /**
	 * 單獨點選checkbox
	 **/
	function selState(val) {
		var nodes = null, attr = null;
		var isSel = "false";
		var cnt = 0;
		var txt1 = '';

		var obj = document.getElementById('setForm');
    	nodes = obj.getElementsByTagName('input');

		for (var i = 0, m = 0; i < nodes.length; i++) {
			attr = nodes[i].getAttribute("exclude");
			if ((nodes[i].type == "checkbox") && (attr == null) && (nodes.item(i).name.indexOf('hid') == 0)) {
				m++;

				if (nodes[i].checked) cnt++;
			}
		}

		// m = (m > 0) ? m - 1 : 0
		document.getElementById("ck").checked = (m == cnt);

		//  全選 or 全消 (checkbox title)
        var ck_title = document.getElementById("ck");
        if (m == cnt){
            ck_title.title = "{$MSG['cancel_all'][$sysSession->lang]}";
        }else{
            ck_title.title = "{$MSG['select_all'][$sysSession->lang]}";
        }
	}

    $(document).ready( function() {

        // 檢查電子信箱有沒有重複
        $('input[name=email]').change(function() {

            $.ajax({
                'type': 'POST',
                'dataType': 'json',
                'data': {'action' : 'getEmailDuplicate', 'email' : $('input[name=email]').val()},
                'url': 'http://{$_SERVER['HTTP_HOST']}:{$_SERVER['SERVER_PORT']}/mooc/controllers/user_ajax.php',
                'success': function (data) {
                    switch(data) {
                        /*
                        case 2:
                            alert('{$MSG['email_used'][$sysSession->lang]}');
                            $('input[name=email]').val('').focus();
                            break;
                        */

                        case 3:
                            alert('{$MSG['msg_js_11'][$sysSession->lang]}');
                            $('input[name=email]').val('').focus();
                            break;

                        case 4:
                            alert('{$MSG['msg_js_10'][$sysSession->lang]}');
                            $('input[name=email]').val('').focus();
                            break;
                    }
                },
                'error': function () {
                    alert('Ajax Error!');
                }
            });
            
        });
    });
        
    function keypressed(){
        if(event.keyCode=='13') {
            chkData();
        } 
    } 

	window.onload = function() {
		Calendar_setup("begin_date", "%Y-%m-%d", "begin_date", false);
		Calendar_setup("end_date"  , "%Y-%m-%d", "end_date"  , false);
		Calendar_setup("birthday"  , "%Y-%m-%d", "birthday"  , false);
		var obj = document.getElementById("picture");
		/*if (obj != null) {
        
            // #48718 chrome [管理者/帳號管理/查詢人員/修改個人基本資料] 點選個人相片的「選擇檔案」沒有反應：增加瀏覽器判斷
            var browser = 'ie';
            if(navigator.userAgent.indexOf('MSIE')>0){
                browser = 'ie';
            }else if(navigator.userAgent.indexOf('Firefox')>0){
                browser = 'ff';
            }else if(navigator.userAgent.indexOf('Chrome')>0){
                browser = 'chr';
            }else if(navigator.userAgent.indexOf('Safari')>0){
                browser = 'sf';
            }else{
                browser = 'op';
            }
            
            switch(browser){
                case 'chr':
                case 'ie':
                case 'ff':
                    obj.onclick = chgPic;
                    break;
                
                case 'sf':
                    obj.onpropertychange = chgPic;
                    break;
            }
		}*/
	}
BOF;

    list($begin_time,$expire_time) = dbGetStSr('WM_sch4user', 'begin_time,expire_time', 'school_id=' . $sysSession->school_id . " and username='" . $username . "'", ADODB_FETCH_NUM);
	$RS = getUserDetailData($username);

	//(欄位型態，長度，最大長度，隱藏，欄位名稱，預設值，名稱，備註)
	$dd = array(
			array('password', 15, 20, 0, 'password',       '',                    $MSG['password'][$sysSession->lang],       $MSG['msg_password'][$sysSession->lang]),
			array('password', 15, 20, 0, 'repassword',     '',                    $MSG['repassword'][$sysSession->lang],     $MSG['msg_repassword'][$sysSession->lang]),
			array('text',     15, 20, 1, 'last_name',      $RS['last_name'],      $MSG['last_name'][$sysSession->lang],      ''),
			array('text',     15, 20, 1, 'first_name',     $RS['first_name'],     $MSG['first_name'][$sysSession->lang],     ''),
			array('radio',    15, 20, 1, 'gender',         $RS['gender'],         $MSG['gender'][$sysSession->lang],         ''),
			array('date',     15, 20, 1, 'birthday',       $RS['birthday'],       $MSG['birthday'][$sysSession->lang],       $MSG['msg_birthday'][$sysSession->lang]),
			array('text',     15, 20, 1, 'personal_id',    $RS['personal_id'],    $MSG['personal_id'][$sysSession->lang],    ''),
			array('file',     30, 50, 1, 'picture',        '',                    $MSG['picture'][$sysSession->lang],        $MSG['msg_picture'][$sysSession->lang]),
			array('text',     30, 50, 1, 'email',          $RS['email'],          $MSG['email'][$sysSession->lang],          $MSG['msg_email'][$sysSession->lang]),
			array('text',     30, 255, 1, 'homepage',       $RS['homepage'],       $MSG['homepage'][$sysSession->lang],       $MSG['msg_homepage'][$sysSession->lang]),
			array('text',     15, 20, 1, 'home_tel',       $RS['home_tel'],       $MSG['home_tel'][$sysSession->lang],       $MSG['msg_home_tel'][$sysSession->lang]),
			array('text',     15, 20, 1, 'home_fax',       $RS['home_fax'],       $MSG['home_fax'][$sysSession->lang],       $MSG['msg_home_tel'][$sysSession->lang]),
			array('text',     30, 60, 1, 'home_address',   $RS['home_address'],   $MSG['home_address'][$sysSession->lang],   $MSG['msg_home_address'][$sysSession->lang]),
			array('text',     15, 20, 1, 'office_tel',     $RS['office_tel'],     $MSG['office_tel'][$sysSession->lang],     $MSG['msg_home_tel'][$sysSession->lang]),
			array('text',     15, 20, 1, 'office_fax',     $RS['office_fax'],     $MSG['office_fax'][$sysSession->lang],     $MSG['msg_home_tel'][$sysSession->lang]),
			array('text',     30, 60, 1, 'office_address', $RS['office_address'], $MSG['office_address'][$sysSession->lang], $MSG['msg_home_address'][$sysSession->lang]),
			array('text',     15, 17, 1, 'cell_phone',     $RS['cell_phone'],     $MSG['cell_phone'][$sysSession->lang],     ''),
			array('text',     30, 60, 1, 'company',        $RS['company'],        $MSG['company'][$sysSession->lang],        ''),
			array('text',     30, 60, 1, 'department',     $RS['department'],     $MSG['department'][$sysSession->lang],     ''),
			array('text',     15, 30, 1, 'title',          $RS['title'],          $MSG['title'][$sysSession->lang],          ''),
			array('select',   15, 20, 0, 'language',       $RS['language'],       $MSG['language'][$sysSession->lang],       ''),
			array('radio'   , 15, 20, 0, 'msg_reserved'  , $RS['msg_reserved']  , $MSG['msg_reserved'][$sysSession->lang]  , $MSG['msg_msg_reserved'][$sysSession->lang]  ),
			array('select',   15, 20, 0, 'theme',          $RS['theme'],          $MSG['theme'][$sysSession->lang],          '')
		);

	showXHTML_head_B($MSG['tabs_personal'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_script('include', '/lib/filter_spec_char.js');
	showXHTML_script('include', '/academic/stud/lib.js');
	showXHTML_script('include', '/lib/common.js');
	showXHTML_script('include', '/lib/jquery/jquery.min.js', true, null, 'UTF-8');
	$calendar = new DHTML_Calendar('/lib/jscalendar/', $sysSession->lang, 'calendar-system');
	$calendar->load_files();
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();

		showXHTML_table_B('width="700" border="0" cellspacing="0" cellpadding="0" id="ListTable"');
			showXHTML_tr_B();
				showXHTML_td_B();
					$ary[] = array($MSG['tabs_personal'][$sysSession->lang], 'tabsSet',  '');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E();
			showXHTML_tr_E();
			showXHTML_tr_B();
				showXHTML_td_B('valign="top" id="CGroup" ');

					showXHTML_form_B('action="modify_stud_info2.php" method="post" enctype="multipart/form-data" style="display:inline;" onkeyup="keypressed();"', 'setForm');
					setTicket();
					$ticket = md5($username . $sysSession->school_id . $sysSession->ticket);
					showXHTML_input('hidden', 'ticket', $ticket, '', '');
					showXHTML_input('hidden', 'username', $username, '', '');
					showXHTML_input('hidden', 'begin_time', '', '', '');
					showXHTML_input('hidden', 'expire_time', '', '', '');
					showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" id="MySet" class="cssTable"');
						showXHTML_tr_B('class="cssTrHead"');
							showXHTML_td_B('colspan="4"');
							echo $RS['realname']  . '(' . $username . ') > ' . $MSG['msg_personal'][$sysSession->lang];
							showXHTML_td_E();
						showXHTML_tr_E();

                        $col = ($col == 'cssTrEvn') ? 'cssTrOdd' : 'cssTrEvn';
						showXHTML_tr_B('class="' . $col . '"');
							showXHTML_td('align="center" nowrap="noWrap"', $MSG['item'][$sysSession->lang]);
							showXHTML_td_B('align="center" nowrap="noWrap"', '');
								echo $MSG['hidden'][$sysSession->lang] . '<br>';
								showXHTML_input('checkbox', 'ck', '', '', '" onclick="selFunc(this.checked);" exclude="true"' . 'title=' . $MSG['select_all'][$sysSession->lang]);
							showXHTML_td_E();
							showXHTML_td('align="center" nowrap="noWrap"', $MSG['content'][$sysSession->lang]);
							showXHTML_td('align="center" nowrap="noWrap"', $MSG['note'][$sysSession->lang]);
						showXHTML_tr_E();

						$col = ($col == 'cssTrEvn') ? 'cssTrOdd' : 'cssTrEvn';
						showXHTML_tr_B('class="' . $col . '"');
							showXHTML_td('align="right" nowrap="noWrap"', $MSG['username'][$sysSession->lang]);
							showXHTML_td('align="center"', '&nbsp;');
							showXHTML_td('class="myname"', $username);
							showXHTML_td('', '');
						showXHTML_tr_E();

						// 帳號使用期限
						$col = ($col == 'cssTrEvn') ? 'cssTrOdd' : 'cssTrEvn';
						showXHTML_tr_B('class="' . $col . '"');
						    showXHTML_td('align="right"', $MSG['account_deadline'][$sysSession->lang]);
						    showXHTML_td('', '&nbsp');
                            showXHTML_td_B('');
                            // 從
						    echo $MSG['from2'][$sysSession->lang];
						    $tmp = intval($begin_time);
							$isCheck = (intval($tmp) <= 0) ? false : true;
							if ($isCheck)
								$val = $begin_time;
							else
								$val = sprintf('%04d-%02d-%02d', $date['year'], $date['mon'], $date['mday']);
							$ck = $isCheck ? ' checked' : '';
							$ds = $isCheck ? '' : ' style="display: none;"';
							showXHTML_input('checkbox', 'ck_begin_date', $val, '', 'id="ck_begin_date' . '" onclick="showDateInput(\'span_begin_date' . '\', this.checked)"' . $ck);
							echo $MSG['msg_date_start'][$sysSession->lang];
							echo '<span id="span_begin_date' .'"' . $ds . '>';
							showXHTML_input('text', 'begin_date', $val, '', 'id="begin_date" readonly="readonly" class="cssInput"');
							echo '</span>';
							// 至
							echo "<br /> {$MSG['to2'][$sysSession->lang]}";
							$tmp = intval($expire_time);
							$isCheck = (intval($tmp) <= 0) ? false : true;
							if ($isCheck)
								$val = $expire_time;
							else
								$val = sprintf('%04d-%02d-%02d', $date['year'], $date['mon'], $date['mday']);
							$ck = $isCheck ? ' checked' : '';
							$ds = $isCheck ? '' : ' style="display: none;"';
							showXHTML_input('checkbox', 'ck_end_date', $val, '', 'id="ck_end_date' . '" onclick="showDateInput(\'span_end_date' . '\', this.checked)"' . $ck);
							echo $MSG['msg_date_stop'][$sysSession->lang];
							echo '<span id="span_end_date' .'"' . $ds . '>';
							showXHTML_input('text', 'end_date', $val, '', 'id="end_date" readonly="readonly" class="cssInput"');
							echo '</span>';
						    showXHTML_td_E();

						    showXHTML_td('', '&nbsp;');
						showXHTML_tr_E();
						$cnt = count($dd);
						$fhid = 1;
						$hidd = $RS['hid'];
						for ($i = 0; $i < $cnt; $i++) {
							$col = ($col == 'cssTrEvn') ? 'cssTrOdd' : 'cssTrEvn';
							showXHTML_tr_B('class="' . $col . ' "');
								showXHTML_td('align="right" nowrap="noWrap"', $dd[$i][6]);
								showXHTML_td_B('align="center"');
									if ($dd[$i][3] > 0) {
										$checked = ($hidd&$fhid)? 'checked="checked"' : '';

										if (in_array($dd[$i][4],$not_hidden))
											echo '&nbsp;';
										else
											showXHTML_input('checkbox', "hid[{$i}]", $fhid, '', " onclick='selState()' " .$checked);

										$fhid = $fhid * 2;

									} else{
										echo '&nbsp;';
									}
								showXHTML_td_E();
								showXHTML_td_B('noWrap="noWrap"');
									switch ($dd[$i][0]) {
										case 'password':
										case 'text':
											showXHTML_input($dd[$i][0], $dd[$i][4], htmlspecialchars($dd[$i][5]), '', 'maxlength="' . $dd[$i][2] . '" size="' . $dd[$i][1] . '" class="cssInput"');
											break;
										case 'file':
											$enc = sysEncode($username);
											$ids = base64_encode(urlencode($enc));
											echo '<span id="PicRoom"><img src="showpic.php?a=' . $ids . '&timestamp=' . uniqid('') . '" type="image/jpeg" id="MyPic" name="MyPic" borer="0" align="absmiddle" onload="picReSize()" loop="0"></span>';
											showXHTML_input($dd[$i][0], $dd[$i][4], $dd[$i][5], '', 'id="' . $dd[$i][4] . '" class="cssInput"');
											break;
										case 'radio':
											if ($dd[$i][4] == 'gender')
											{
												$sel = array(
														'M'=>$MSG['male'][$sysSession->lang],
														'F'=>$MSG['female'][$sysSession->lang]
													);
											}else if ($dd[$i][4] == 'msg_reserved'){
												$sel = array(
														'0'=>$MSG['not_reserved'][$sysSession->lang],
														'1'=>$MSG['reserved'][$sysSession->lang]
													);
											}
											showXHTML_input($dd[$i][0], $dd[$i][4], $sel, $dd[$i][5], '');
											break;
										case 'date':
											$tmp = str_replace("-", "", $dd[$i][5]);
											$isCheck = (intval($tmp) <= 0) ? false : true;
											if ($isCheck)
												$val = $dd[$i][5];
											else
												// $val = $date['year'].'-'.$date['mon'].'-'.$date['mday'];
												$val = '';
											showXHTML_input('text', 'birthday', $val, '', 'id="birthday" readonly="readonly" class="cssInput"');
								  			break;
								  		case 'select':
								  			$sel = array();
								  			$val = '';
								  			if ($dd[$i][4] == 'language') {
												$sel = array(
														'Big5'       =>$MSG['lang_big5'][$sysSession->lang],
														'en'         =>$MSG['lang_en'][$sysSession->lang],
														'GB2312'     =>$MSG['lang_gb'][$sysSession->lang],
														'EUC-JP'     =>$MSG['lang_jp'][$sysSession->lang],
														'user_define'=>$MSG['lang_user'][$sysSession->lang]
													);
												removeUnAvailableChars($sel);
												$val = empty($RS['language']) ? $sysSession->lang : $RS['language'];
								  			} else if ($dd[$i][4] == 'theme') {
								  				$sel = array('default'=>'default');
												$val = empty($RS['theme']) ? $sysSession->theme : $RS['theme'];
								  			}
											showXHTML_input('select', $dd[$i][4], $sel, $val, 'class="cssInput"');
								  			break;
										default:
											echo '&nbsp;';
									}
								showXHTML_td_E();
								showXHTML_td('', $dd[$i][7]);
							showXHTML_tr_E();
						}
						$col = ($col == 'cssTrEvn') ? 'cssTrOdd' : 'cssTrEvn';
						showXHTML_tr_B('class="' . $col . ' "');
							showXHTML_td_B('colspan="4" align="center"');
								showXHTML_input('button', '', $MSG['btn_save'][$sysSession->lang], '', 'id="btn_submit" class="cssBtn" onclick="chkData();"');
								showXHTML_input('reset' , '', $MSG['reset'][$sysSession->lang], '', 'class="cssBtn"');
								if (! empty($ACADEMIC_CLASS_MEMBER))
									showXHTML_input('button', '', $MSG['btn_return_people_manage'][$sysSession->lang], '', 'class="cssBtn" onclick="window.location.replace(\'people_manager.php\')"');
								else
									showXHTML_input('button', '', $MSG['return_query_people'][$sysSession->lang], '', 'class="cssBtn" onclick="window.location.replace(\'stud_query.php\')"');
							showXHTML_td_E();
						showXHTML_tr_E();
					showXHTML_table_E();
					showXHTML_form_E();

				showXHTML_td_E();
			showXHTML_tr_E();
		showXHTML_table_E();
	showXHTML_body_E();
?>

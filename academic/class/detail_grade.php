<?php
	/**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                               *
	*                                                                                                 *
	*		Programmer: Amm Lee                                                                       *
	*		Creation  : 2003/09/23                                                                    *
	*		work for  :  詳細成績                                                                   *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                        *
	*       $Id: detail_grade.php,v 1.1 2010/02/24 02:38:14 saly Exp $                                                                                          *
	**************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/username.php');
 	require_once(sysDocumentRoot . '/lang/view_grade.php');
 	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '2400300600';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable')))
	{
	}

	$icon_up = sprintf('<img src="/theme/%s/%s/dude07232001up.gif" border="0" align="absmiddl">', $sysSession->theme, $sysSession->env);
	$icon_dn = sprintf('<img src="/theme/%s/%s/dude07232001down.gif" border="0" align="absmiddl">', $sysSession->theme, $sysSession->env);

	$ta = array(
		1 => 'CS.course_id',
		2 => 'CS.teacher',
		3 => 'CS.st_begin,CS.st_end',
		4 => 'CS.status',
        5 => 'CS.fair_grade',
        6 => 'WG.total',
        7 => 'CS.credit',
        8 => 'real_credit'
	);

    $sortby = $ta[intval($_POST['sortby'])];
    if (empty($sortby)) $sortby = $ta[1];

    $_POST['order'] = trim($_POST['order']);
    $order = in_array($_POST['order'], array('asc', 'desc')) ? $_POST['order'] : 'desc';

	$cs_status = array(
		$MSG['cs_state_close'][$sysSession->lang],
		$MSG['cs_state_open_a'][$sysSession->lang],
		$MSG['cs_state_open_a_date'][$sysSession->lang],
		$MSG['cs_state_open_n'][$sysSession->lang],
		$MSG['cs_state_open_n_date'][$sysSession->lang],
		$MSG['cs_state_prepare'][$sysSession->lang]
	);

	$lang = strtolower($sysSession->lang);

	if (isset($DIRECT_VIEW_GRADE))
	{
		// 序號
		$counter       = 0;
		$url_target    = 'member_grade.php';

		// 解開編碼後的帳號
		$direct_user   = trim($_POST['user']);
		$enc           = base64_decode(trim($_POST['user']));
		$username      = trim(@mcrypt_decrypt(MCRYPT_DES, sysTicketSeed . $_COOKIE['idx'], $enc, 'ecb'));
		$_POST['user'] = $username;
	}
	else
	{
		$_POST['user'] = trim($_POST['user']);
		$res           = checkUsername($_POST['user']);
		if (($res != 2) && ($res != 4)) die('Access Deny!');
		$url_target    = 'view_grade.php?a=' . $_POST['class_id'];
	}

	if (!function_exists('divMsg')) {
		/**
		 * 處理資料，過長的部份隱藏
		 * @param integer $width   : 要顯示的寬度
		 * @param string  $caption : 顯示的文字
		 * @param string  $title   : 浮動的提示文字，若沒有設定，則跟 $caption 依樣
		 * @return string : 處理後的文字
		 **/
		function divMsg($width=100, $caption='&nbsp;', $title='') {
			if (empty($title)) $title = $caption;
			return '<div style="width: ' . $width . 'px; overflow:hidden;" title="' . $title . '">' . $caption . '</div>';
		}
	}

	$js = <<< EOF
	/**
    * 回 人員列表
    **/
    function go_list() {
		window.location.replace("{$url_target}");
	}

    /*
     * 按 標題 排序
    */
    function chgPageSort(val) {

        var obj = document.getElementById("actFm");

        if ((typeof(obj) != "object") || (obj == null)) return false;

        if (trim(obj.order.value) == 'asc'){
            obj.order.value = 'desc';
        }else if (trim(obj.order.value) == 'desc'){
            obj.order.value = 'asc';
        }

		obj.sortby.value = val;

		obj.submit();
	}

    function picReSize() {
		var orgW = 0, orgH = 0;
		var demagnify = 0;
		var node = document.getElementById("MyPic");

		if ((typeof(node) != "object") || (node == null)) return false;
		orgW = parseInt(node.width);
		orgH = parseInt(node.height);
		if ((orgW > 110) || (orgH > 120)) {
			demagnify = (((orgW / 110) > (orgH / 120)) ? parseInt(orgW / 110) : parseInt(orgH / 120)) + 1;
			node.width  = parseInt(orgW / demagnify);
			node.height = parseInt(orgH / demagnify);
		}
		node.parentNode.style.height = node.height + 3;
	}

EOF;

	showXHTML_head_B($MSG['title27'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('include', '/lib/common.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E('');
	showXHTML_body_B('');
        showXHTML_table_B('width="760" border="0" cellspacing="0" cellpadding="0" id="ListTable"');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					$ary = array();
					$ary[] = array($MSG['title135'][$sysSession->lang], 'tabs');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E('');
			showXHTML_tr_E('');
			showXHTML_tr_B('');
			    showXHTML_td_B('valign="top" ');
			        showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" id="ClassList" class="cssTable"');
        				showXHTML_tr_B('class="cssTrHead"');
        				    showXHTML_td_B('colspan="'.(isset($DIRECT_VIEW_GRADE) ? 9 : 8).'" ');
								if(isset($DIRECT_VIEW_GRADE)){
									$user = getUserDetailData($username);
									echo $username . ' (' . $user['realname'] . ')' . $MSG['title136'][$sysSession->lang];
								}else{
									//  查詢帳號的資料
									if ($_POST['class_id'] == 1000000){
										$csname = $sysSession->school_name;
									}else{
										list($caption) = dbGetStSr('WM_class_main', 'caption', ' class_id = ' .  $_POST['class_id'], ADODB_FETCH_NUM);
										$lang = unserialize($caption);
										$csname = htmlspecialchars($lang[$sysSession->lang]);
									}
									echo '<font color="#FF0000">' . $MSG['title121'][$sysSession->lang] . $csname . '</font><br>';
									if (($_POST['sdate'] != '') && ($_POST['sdate'] != '0-00-00 00:00:00')){
										$a_sdate = explode(" ",$_POST['sdate']);
										$msg = $a_sdate[0] . '~';
									}
									if (($_POST['edate'] != '') && ($_POST['edate'] != '0-00-00 23:59:59')) {
										$a_edate = explode(" ",$_POST['edate']);
										$msg .= $a_edate[0];
									}
									if ($msg == ''){
										$msg = $MSG['title24'][$sysSession->lang];
									}
									echo '<font color="#003366">' . $MSG['title128'][$sysSession->lang] . $msg . '</font><br>';

									list($first_name, $last_name) = dbGetStSr('WM_user_account', 'first_name, last_name', "username='". $_POST['user'] . "'", ADODB_FETCH_NUM);
									echo checkRealname($first_name, $last_name), '(', $_POST['user'], ') ', $MSG['title136'][$sysSession->lang];
								}
                            showXHTML_td_E('');
        				showXHTML_tr_E('');
        				showXHTML_tr_B('class="cssTrHead"');
							if(isset($DIRECT_VIEW_GRADE)){
								showXHTML_td_B('align="center" onclick="chgPageSort(1);" title="' . $MSG['title108'][$sysSession->lang] . '"');
									echo $MSG['serial_no'][$sysSession->lang];
        						showXHTML_td_E('');
							}
							showXHTML_td_B('align="center" onclick="chgPageSort(1);" title="' . $MSG['title108'][$sysSession->lang] . '"');
                                echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
                                echo $MSG['title108'][$sysSession->lang];
                                echo ($sortby == 'CS.course_id') ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
                                echo '</a>';
                            showXHTML_td_E('');
                            showXHTML_td_B('align="center" onclick="chgPageSort(2);" title="' . $MSG['title138'][$sysSession->lang] . '"');
                                echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
                                echo $MSG['title138'][$sysSession->lang];
                                echo ($sortby == 'CS.teacher') ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
                                echo '</a>';
                            showXHTML_td_E('');
                            showXHTML_td_B('align="center" onclick="chgPageSort(3);" title="' . $MSG['title141'][$sysSession->lang] . '"');
                                echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
                                echo $MSG['title141'][$sysSession->lang];
                                echo ($sortby == 'CS.st_begin,CS.st_end') ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
                                echo '</a>';
                            showXHTML_td_E('');
                            showXHTML_td_B('align="center" onclick="chgPageSort(4);" title="' . $MSG['title140'][$sysSession->lang] . '"');
                                echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
                                echo $MSG['title140'][$sysSession->lang];
                                echo ($sortby == 'CS.status') ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
                                echo '</a>';
                            showXHTML_td_E('');
                            showXHTML_td_B('align="center" onclick="chgPageSort(5);" title="' . $MSG['title144'][$sysSession->lang] . '"');
                                echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
                                echo $MSG['title144'][$sysSession->lang];
                                echo ($sortby == 'CS.fair_grade') ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
                                echo '</a>';
                            showXHTML_td_E('');
                            showXHTML_td_B('align="center" onclick="chgPageSort(6);" title="' . $MSG['title139'][$sysSession->lang] . '"');
                                echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
                                echo $MSG['title139'][$sysSession->lang];
                                echo ($sortby == 'WG.total') ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
                                echo '</a>';
                            showXHTML_td_E('');
                            showXHTML_td_B('align="center" onclick="chgPageSort(7);" title="' . $MSG['title142'][$sysSession->lang] . '"');
                                echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
                                echo $MSG['title142'][$sysSession->lang];
                                echo ($sortby == 'CS.credit') ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
                                echo '</a>';
                            showXHTML_td_E('');
                            showXHTML_td_B('align="center" onclick="chgPageSort(8);" title="' . $MSG['title143'][$sysSession->lang] . '"');
                                echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
                                echo $MSG['title143'][$sysSession->lang];
                                echo ($sortby == 'real_credit') ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
                                echo '</a>';
                            showXHTML_td_E('');
	                    showXHTML_tr_E('');

        				//  抓出學生的修課記錄 (begin)
        				$sqls = str_replace('%USERNAME%', $_POST['user'], $Sqls['get_student_all_course_grade_list']);
        				if (($_POST['sdate'] != '') && ($_POST['sdate'] != '0-00-00 00:00:00')){
                            $sqls .= " and MJ.add_time >= '" . $sdate . "' ";
                        }

                        if (($_POST['edate'] != '') && ($edate != '0-00-00 23:59:59')){
                            $sqls .= " and MJ.add_time <= '" . $edate . "' ";
                        }

                        if ($sortby == 'CS.st_begin,CS.st_end'){
                            if ($order == 'asc'){
                                $cond = 'CS.st_begin asc,CS.st_end asc';
                            }else{
                                $cond = 'CS.st_begin desc,CS.st_end desc';
                            }
                        }else{
                            $cond = $sortby . ' ' .  $order;
                        }

                        $sqls .= ' order by ' . $cond;

						$sysConn->SetFetchMode(ADODB_FETCH_ASSOC);
                        $RS = $sysConn->Execute($sqls);

                        //  抓出學生的修課記錄 (end)
                        //  此學生 總共修幾門課
                        $total_course = ($RS) ? $RS->RecordCount() : 0;

                        //  $total_course > 0 begin
                        if ($total_course == 0)
                        {
                            showXHTML_tr_B('class="cssTrEvn" ');
                                showXHTML_td('align="center" nowrap="noWrap" colspan="9"', $MSG['title107'][$sysSession->lang]);
                            showXHTML_tr_E('');
                        }
                        elseif ($total_course > 0)
                        {
							//  每一個 課程總分 的 總合
							$sum_total_grade = 0;

                            while (!$RS->EOF)
                            {

                                $col=$col=='cssTrEvn'?'cssTrOdd':'cssTrEvn';

                                //  抓取課程名稱
                                $lang = getCaption($RS->fields['caption']);
                    		    $class_name = $lang[$sysSession->lang];

                                //  每一個 科目 的總分 (begin)
                                $total_grade = 0;

                                if ($RS->fields['total'] != ''){
                                    $total_grade = $RS->fields['total'];
                                }else{
                                    $total_grade = '';
                                }

                                $credit = $RS->fields['credit'];
								if(Grade_Calculate == 'Y') {
									//   總分($all_total_grade) = $total_grade * 那一個科目的學分數 (沒設學分數 則 預設值 為 1 學分credit )
									if ($total_grade == ''){
										$all_total_grade += 0;
									}else{
										if($credit != ''){
											$all_total_grade += $total_grade * intval($credit);
											$total_credit1 += intval($credit);
										}
									}
								}else{	// N : 不以學分數為加權數 sum(course score) / count(course 數)
									$sum_total_grade += $total_grade;
								}
                                //  每一個 科目 的總分 (end)

                                showXHTML_tr_B('class="' . $col . ' "');
									if(isset($DIRECT_VIEW_GRADE)){
										showXHTML_td_B('align="center" nowrap="noWrap" onclick="chgPageSort(1);" title="' . $MSG['title108'][$sysSession->lang] . '"');
											echo ++$counter;
        								showXHTML_td_E('');
									}
                                    showXHTML_td('align="left" nowrap="noWrap"', divMsg('120', $class_name));
                                    showXHTML_td('align="left" nowrap="noWrap"', divMsg('120', $RS->fields['teacher']));

                                    $msg = $MSG['from2'][$sysSession->lang] . ( ($RS->fields['st_begin'] == '') ? $MSG['now'][$sysSession->lang] : $RS->fields['st_begin'] ) . '<br>' .
						                   $MSG['to2'][$sysSession->lang] . ( ($RS->fields['st_end'] == '') ? $MSG['forever'][$sysSession->lang] : $RS->fields['st_end'] );
                                    showXHTML_td('align="center" nowrap="noWrap"', $msg);
                                    showXHTML_td('align="left" nowrap="noWrap"', divMsg('150', $cs_status[$RS->fields['status']]));
                                    showXHTML_td('align="right" ', $RS->fields['fair_grade']);
                                    showXHTML_td('align="right" nowrap="noWrap"', $total_grade);

                                    //  本科學分
                                    $credit = (empty($RS->fields['credit'])) ? '' : intval($RS->fields['credit']);
                                    showXHTML_td('align="right" ', $credit);

                                    // 實得學分
                                    $r_credit = ($RS->fields['total'] >= $RS->fields['fair_grade'] ? $credit : 0);
                                    showXHTML_td('align="right" ', $r_credit);
                                    $real_credit += $r_credit;

                                showXHTML_tr_E('');
                                $RS->MoveNext();
                            }

                            $col=$col=='cssTrEvn'?'cssTrOdd':'cssTrEvn';

                            //  回人員列表 (begin)
                            showXHTML_tr_B('class="' . $col . ' "');
								if(Grade_Calculate == 'Y') {	// Y : 以學分數為加權數
									if($total_credit1 > 0) {
										$total_average = round($all_total_grade/$total_credit1,2);
									}else{
										$total_average = 0;
									}
									$avg_note = '<br><font color="red">' . $MSG['Grade_Calculate_Y'][$sysSession->lang] . '</font>';
								}else{	// N : 不以學分數為加權數 sum(course score) / count(course 數)
									if($total_course > 0) {
										$total_average = round($sum_total_grade/$total_course,2);
									}else{
										$total_average = 0;
									}
									$avg_note = '<br><font color="red">' . $MSG['Grade_Calculate_N'][$sysSession->lang] . '</font>';
								}
								if(isset($DIRECT_VIEW_GRADE)){
									showXHTML_td('align="left" ', '');
								}
                    		    showXHTML_td('align="right" nowrap="noWrap" colspan="5"', $MSG['title115'][$sysSession->lang] . $avg_note);
								showXHTML_td('align="right" colspan="2" ',$total_average);
                    			showXHTML_td('align="right" ', $real_credit);
                    		showXHTML_tr_E('');
                        }
                        //  $total_course > 0 end

                        $col=$col=='cssTrEvn'?'cssTrOld':'cssTrEvn';
                        //  回人員列表 (begin)
                        showXHTML_tr_B('class="' . $col . ' "');
                		    showXHTML_td_B('colspan="'.(isset($DIRECT_VIEW_GRADE) ? 9 : 8).'" align="center"');
                				showXHTML_input('button', '', $MSG['title98'][$sysSession->lang], '', 'class="cssBtn" onclick="go_list();"');
                			showXHTML_td_E('');
                		showXHTML_tr_E('');
                		//  回人員列表 (end)

        			showXHTML_table_E('');
				showXHTML_td_E('');

			showXHTML_tr_E('');

		showXHTML_table_E('');

		showXHTML_form_B('action="' . $_SERVER['PHP_SELF']. '" method="post" enctype="multipart/form-data" style="display:none"', 'actFm');
	        if(isset($DIRECT_VIEW_GRADE)){
				showXHTML_input('hidden', 'user', $direct_user, '', '');
			}else{
				showXHTML_input('hidden', 'user', $_POST['user'], '', '');
			}
	        showXHTML_input('hidden', 'sortby', intval($_POST['sortby']), '', '');
	        showXHTML_input('hidden', 'order', $order, '', '');
	        showXHTML_input('hidden', 'class_id', $_POST['class_id'], '', '');
    	showXHTML_form_E();

	showXHTML_body_E('');
?>

<?php
    /**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                               *
	*                                                                                                 *
	*		Programmer: Amm Lee                                                                       *
	*		Creation  : 2003/09/23                                                                    *
	*		work for  : 學習成果                                                                      *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                        *
	*       $Id: learn_result.php,v 1.1 2010/02/24 02:38:44 saly Exp $                                                                                          *
	**************************************************************************************************/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lib/common.php');
    
	$sysSession->cur_func = '1500200100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}
    $cs_status = array(
                    $MSG['cs_state_close'][$sysSession->lang],
                    $MSG['cs_state_open_a'][$sysSession->lang],
                    $MSG['cs_state_open_a'][$sysSession->lang],
                    $MSG['cs_state_open_a'][$sysSession->lang],
                    $MSG['cs_state_open_a'][$sysSession->lang],
                    $MSG['cs_state_prepare'][$sysSession->lang]);

	$sortby  = min(6, max(1, $_POST['sortby']));	// 設定sortby
	$sortArr = array('',
		            'CS.course_id',
					'WG.total',
					'CS.credit',
					'CS.status',
                    'CS.teacher',
                    'CS.st_begin,CS.st_end');

	$icon_up = '<img src="/theme/default/academic/dude07232001up.gif" border="0" align="absmiddl">';
	$icon_dn = '<img src="/theme/default/academic/dude07232001down.gif" border="0" align="absmiddl">';

	$username = $_POST['user'] ? preg_replace('/[^\w.-]+/', '', $_POST['user']) : $sysSession->username;
    //  查詢帳號的資料
    list($first_name, $last_name) = dbGetStSr('WM_user_account', 'first_name, last_name', 'username="'.$username.'"', ADODB_FETCH_NUM);

    showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" id="learn_result" class="cssTable"');
        // 學習成果 (begin)
        showXHTML_tr_B('class="cssTrHead"');
            showXHTML_td_B('colspan="6" ');
			//  管理者 - 班次管理 - 成員管理 - 個人資料 - 顯示所在班級
			if (! empty($ACADEMIC_CLASS_MEMBER)){
				$class_id = max(1000000, intval($_POST['class_id']));
				if ($class_id == 1000000){
					$csname = $sysSession->school_name;
				}else{
					list($caption) = dbGetStSr('WM_class_main', 'caption', ' class_id = ' .  $class_id, ADODB_FETCH_NUM);
					$lang   = getCaption($caption);
					$csname = $lang[$sysSession->lang];
				}
				echo '<font color="#FF0000">' . $MSG['title121'][$sysSession->lang] . $csname . '</font><br>';
		   }
           // Bug#1263 真實姓名的顯示不按照個人語系，而按照個人姓名的設定 by Small 2006/12/28
			echo checkRealname($first_name, $last_name);
			showXHTML_td_E();
		showXHTML_tr_E();

        showXHTML_tr_B('class="cssTrHead"');
            showXHTML_td_B('align="center" nowrap="noWrap" onclick="chgPageSort(1);" title="' . $MSG['title108'][$sysSession->lang] . '"');
                echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
                echo $MSG['title108'][$sysSession->lang];
                echo ($sortby == 1) ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
                echo '</a>';
            showXHTML_td_E();
            showXHTML_td_B('align="center" nowrap="noWrap" onclick="chgPageSort(2);" title="' . $MSG['title109'][$sysSession->lang] . '"');
                echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
                echo $MSG['title109'][$sysSession->lang];
                echo ($sortby == 2) ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
                echo '</a>';
            showXHTML_td_E();

            showXHTML_td_B('align="center" nowrap="noWrap" onclick="chgPageSort(3);" title="' . $MSG['title125'][$sysSession->lang] . '"');
                echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
                echo $MSG['title125'][$sysSession->lang];
                echo ($sortby == 3) ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
                echo '</a>';
            showXHTML_td_E();

            showXHTML_td_B('align="center" nowrap="noWrap" onclick="chgPageSort(4);" title="' . $MSG['title110'][$sysSession->lang] . '"');
                echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
                echo $MSG['title110'][$sysSession->lang];
                echo ($sortby == 4) ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
                echo '</a>';
            showXHTML_td_E();

            showXHTML_td_B('align="center" nowrap="noWrap" onclick="chgPageSort(5);" title="' . $MSG['title111'][$sysSession->lang] . '"');
                echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
                echo $MSG['title111'][$sysSession->lang];
                echo ($sortby == 5) ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
                echo '</a>';
            showXHTML_td_E();

            showXHTML_td_B('align="center" nowrap="noWrap" onclick="chgPageSort(6);" title="' . $MSG['title141'][$sysSession->lang] . '"');
                echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
                echo $MSG['title112'][$sysSession->lang];
                echo ($sortby == 6) ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
                echo '</a>';
            showXHTML_td_E();
	    showXHTML_tr_E();

        // 全部 科目 的總分
        $all_total_grade = 0;
        // 全部 科目 的總學分
        $total_credit1 = 0;

        $sqls = str_replace('%USERNAME%', $username, $Sqls['get_student_all_course_grade_list']);

        if ($sortArr[$sortby] == 'CS.st_begin,CS.st_end'){
            if ($order == 'asc'){
                $cond = 'CS.st_begin asc,CS.st_end asc';
            }else{
                $cond = 'CS.st_begin desc,CS.st_end desc';
            }
        }else{
            $cond = $sortArr[$sortby] . ' ' .  $order;
        }
        $sqls .= 'order by ' . $cond;
        $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
        $RS = $sysConn->Execute($sqls);

        //  此學生 總共修幾門課
        $total_course = $RS->RecordCount();

        if ($total_course == 0){
            showXHTML_tr_B('class="cssTrEvn" ');
                showXHTML_td('align="center" nowrap="noWrap" colspan="6"', $MSG['title107'][$sysSession->lang]);
            showXHTML_tr_E();
        }else{
			//  每一個 課程總分 的 總合
			$sum_total_grade = 0;

            while (!$RS->EOF){
                $col=$col=='cssTrEvn'?'cssTrOdd':'cssTrEvn';

                //  抓取課程名稱
                $lang = getCaption($RS->fields['caption']);
    		    $csname = $lang[$sysSession->lang];

                //  每一個 科目 的總分 (begin)
                $total_grade = 0;

                if ($RS->fields['total'] != ''){
                    $total_grade = $RS->fields['total'];
                }else{
                    $total_grade = '';
                }

				// 學分數
				$credit = $RS->fields['credit'];

				if(Grade_Calculate == 'Y') {
					//   總分($all_total_grade) = $total_grade * 那一個科目的學分數 (沒設學分數 則 預設值 為 1 學分credit )
					if ($total_grade != ''){
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
                    showXHTML_td_B('align="left" nowrap="noWrap"');
                        echo '<div style="width: 200px; overflow:hidden;" title="' . $csname . '">' . $csname . '</div>';
                    showXHTML_td_E();
                    showXHTML_td('align="left" nowrap="noWrap"', $total_grade);

                    //  學分數
                    showXHTML_td('align="left" nowrap="noWrap"', $credit);
                    showXHTML_td('align="left"', $cs_status[$RS->fields['status']]);
                    showXHTML_td_B('align="left" nowrap="noWrap"');
                        echo '<div style="width: 200px; overflow:hidden;" title="' . $RS->fields['teacher'] . '">' . $RS->fields['teacher'] . '</div>';
                    showXHTML_td_E();

                    //  課程起訖 (begin)
					$msg = $MSG['from2'][$sysSession->lang] . ( ($RS->fields['st_begin'] == '') ? $MSG['now'][$sysSession->lang] : $RS->fields['st_begin'] ) . '<br>' .
						   $MSG['to2'][$sysSession->lang] . ( ($RS->fields['st_end'] == '') ? $MSG['forever'][$sysSession->lang] : $RS->fields['st_end'] );

                    showXHTML_td('align="center" nowrap ', $msg);

                    //  課程起訖 (end)

                showXHTML_tr_E();

                $RS->MoveNext();

            }

            $col=$col=='cssTrEvn'?'cssTrOdd':'cssTrEvn';

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
                showXHTML_td('align="center" nowrap="noWrap"', $MSG['title115'][$sysSession->lang] . $avg_note);
                showXHTML_td('align="left" nowrap="noWrap" colspan="6"', $total_average);

            showXHTML_tr_E();
        }

        // 學習成果 (end)

        $col=$col=='cssTrEvn'?'cssTrOdd':'cssTrEvn';

        //  回人員列表 (begin)
        showXHTML_tr_B('class="' . $col . ' "');
		    showXHTML_td_B('colspan="6" align="center"');
				if (! empty($DIRECT_MEMBER))
					$return = $MSG['btn_return_direct_member'][$sysSession->lang];
				else if (! empty($ENROLL_MEMBER))	//  導師 - 學員修課管理 - 修課指派 - 挑選人員 - 學習成果
					$return = $MSG['btn_direct_enroll_member'][$sysSession->lang];
				else if(! empty($ACADEMIC_CLASS_MEMBER))	// 管理者 - 班級管理 - 成員管理 - 學習成果
					$return = $MSG['title98'][$sysSession->lang];
				else if(! empty($ACADEMIC_DELETE_MEMBER))	// 管理者 - 帳號管理 - 刪除帳號 - 刪除不規則帳號 - 學習成果
					$return = $MSG['title138'][$sysSession->lang];
				else if(! empty($ACADEMIC_MODIFY_MEMBER))	//  管理者 - 帳號管理 - 查詢人員 - 學習成果
					$return = $MSG['title130'][$sysSession->lang];
				else
					$return = $MSG['title129'][$sysSession->lang];
				showXHTML_input('button', '', $return, '', 'class="cssBtn" onclick="go_list();"');
			showXHTML_td_E();
		showXHTML_tr_E();
		//  回人員列表 (end)
    showXHTML_table_E();
?>

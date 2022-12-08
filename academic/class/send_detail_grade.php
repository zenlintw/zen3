<?php
	/**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                               *
	*                                                                                                 *
	*		Programmer: Amm Lee                                                                       *
	*		Creation  : 2003/09/23                                                                    *
	*		work for  :  詳細成績 的 HTML file                                                                  *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                        *
	*       $Id: send_detail_grade.php,v 1.1 2010/02/24 02:38:15 saly Exp $                                                                                          *
	**************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '2400300500';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable')))
	{

	}

    /**
	 * grade_html($username,$title)
	 *   產生 某一 帳號 所修課程 的 成績 總表
	 *
	 *  parameter : $username -> 帳號
	 *              $title    -> 成績單上所要呈現的細目
	 * @return string (一個html structure  的 string )
	 **/

	function grade_html($username,$title){
	    return class_grade_html(0,$username,$title);
	}

	/**
	 * class_grade_html($class_id,$username,$title)
	 *   產生 某一 帳號 所修課程 的 成績 總表
	 *
	 *  parameter : $class_id -> 班級代碼
	 *              $username -> 帳號
	 *              $title    -> 成績單上所要呈現的細目
	 * @return string (一個html structure  的 string )
	 **/
	function class_grade_html($class_id,$username,$title){
        global $sysSession,$sysConn,$MSG,$Sqls;

        $colspan = 0; $colspan1 = 0; $colspan2 = 0;

        $colspan = substr_count($title,',');
        $colspan = $colspan + 1;

        $cs_status = array(
                    $MSG['cs_state_close'][$sysSession->lang],
                    $MSG['cs_state_open_a'][$sysSession->lang],
                    $MSG['cs_state_open_a_date'][$sysSession->lang],
                    $MSG['cs_state_open_n'][$sysSession->lang],
                    $MSG['cs_state_open_n_date'][$sysSession->lang],
                    $MSG['cs_state_prepare'][$sysSession->lang]);

		if ($class_id)
		{
	       list($caption) = dbGetStSr('WM_class_main', 'caption', ' class_id = ' .  intval($class_id), ADODB_FETCH_NUM);
        	$lang = unserialize($caption);
        	$csname = htmlspecialchars($lang[$sysSession->lang], ENT_NOQUOTES);
		}

       $sysCL = array('Big5'=>'zh-tw','en'=>'en','GB2312'=>'zh-cn');
	    $ACCEPT_LANGUAGE = $sysCL[$sysSession->lang];
        if (empty($ACCEPT_LANGUAGE)) $ACCEPT_LANGUAGE = 'zh-tw';

		$result = <<< EOB
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >
<meta http-equiv="Content-Language" content="{$ACCEPT_LANGUAGE}" >
<title>{$MSG['title6'][$sysSession->lang]}</title>
<style type="text/css">
.cssTrHead {
	font-size       : 12px;
	line-height     : 16px;
	text-decoration : none;
	letter-spacing  : 2px;
	color           : #000000;
	background-color: #CCCCE6;
	font-family     : Tahoma, "Times New Roman", Times, serif;
 }
 .cssTrEvn {
  	font-size       : 12px;
  	line-height     : 16px;
  	text-decoration : none;
  	letter-spacing  : 2px;
  	color           : #000000;
  	background-color: #FFFFFF;
  	font-family     : Tahoma, "Times New Roman", Times, serif;
}
  .cssTrOdd {
  	font-size       : 12px;
  	line-height     : 16px;
  	text-decoration : none;
  	letter-spacing  : 2px;
  	color           : #000000;
  	background-color: #EAEAF4;
  	font-family     : Tahoma, "Times New Roman", Times, serif;
  }
.font01 {
	font-size      : 12px;
	line-height    : 16px;
	color          : #000000;
	text-decoration: none ;
	letter-spacing : 2px;
}
</style>
</head>
<body>
<table class="cssTable" width="760" border="0" cellspacing="0" cellpadding="0" id="ListTable">
<tr>
  <td valign="top" class="bg01">
      <table width="100%" border="0" cellspacing="1" cellpadding="3" id="ClassList" class="box01">
EOB;

		//  查詢帳號的資料
	    list($first_name,$last_name) = dbGetStSr('WM_user_account', 'first_name,last_name', "username='{$username}'", ADODB_FETCH_NUM);
        $realname = checkRealname($first_name, $last_name);

        $result .=  '			<tr class="cssTrHead" > '.
        		    '           <td colspan="' . $colspan . '" class="font01" >' .
        		    ($class_id ? ('<font color="#FF0000">' . $MSG['title14'][$sysSession->lang] . $csname . '</font><br>') : '') .
        		    $realname . '(' . $username . ') ' . $MSG['title9'][$sysSession->lang] .
        		    '           </td>' .
        		    '         </tr>' .
        			'			<tr class="cssTrHead" > ';

        //   課程名稱
        if (strpos($title, 'course_name') !== false){
    	    $result .=    '             <td  align="center" nowrap="noWrap" >' . $MSG['course_name'][$sysSession->lang]   . '</td>';
            $colspan1++;
        }
        //  授課老師
        if (strpos($title, 'teacher') !== false){
            $result .=   '				<td  align="center" nowrap="noWrap" >' . $MSG['title'][$sysSession->lang] . '</td>';
            $colspan1++;
        }

        //  課程起訖
        if (strpos($title, 'period') !== false){
            $result .=   '				<td  align="center" nowrap="noWrap" >' . $MSG['title1'][$sysSession->lang] . '</td>';
            $colspan1++;
        }
        //  課程狀態
        if (strpos($title, 'course_state') !== false){
            $result .=  ' 			<td  align="center" nowrap="noWrap" >' . $MSG['td_status'][$sysSession->lang] . ' </td>';
            $colspan1++;
        }
        //  及格標準
        if (strpos($title, 'fair_grade') !== false){
            $result .=  '				<td  align="center" nowrap="noWrap" >' . $MSG['title4'][$sysSession->lang] . '</td>';
            $colspan1++;
        }
        //  各科總成績
        if (strpos($title, 'every_grade') !== false){
           $result .= '				<td  align="center" nowrap="noWrap" >' . $MSG['title5'][$sysSession->lang] . ' </td>';
           $colspan2++;
        }
        //  本科學分
        if (strpos($title, 'every_credit') !== false){
            $result .= '				<td  align="center" nowrap="noWrap" >' . $MSG['title2'][$sysSession->lang] . '</td>';
            $colspan2++;
        }
        //  實得學分
        if (strpos($title, 'real_credit') !== false){
            $result .= '				<td  align="center" nowrap="noWrap" >' . $MSG['title3'][$sysSession->lang] . '</td>';
        }

        $result .= '         </tr>';

        //  抓出學生的修課記錄 (begin)
        $sqls = str_replace('%USERNAME%', $username, $Sqls['get_student_all_course_grade_list']);
		$sysConn->SetFetchMode(ADODB_FETCH_ASSOC);
        $RS = $sysConn->Execute($sqls);
        //  抓出學生的修課記錄 (end)

        //  此學生 總共修幾門課
        $major_count = $RS->RecordCount();

        //  $major_count > 0 begin
        if ($major_count == 0){
            $result .= '<tr class="cssTrEvn" >' .
                       ' 	<td  align="center" colspan="' . $colspan . '" nowrap="noWrap" >' . $MSG['title7'][$sysSession->lang] . ' </td>'.
                       '</tr>';
        }else{
			while (!$RS->EOF){

                $col=$col=='cssTrEvn'?'cssTrOdd':'cssTrEvn';

                //  抓取課程名稱
                $lang = unserialize($RS->fields['caption']);
            	$class_name = $lang[$sysSession->lang];

                //  每一個 科目 的總分 (begin)
                $total_grade = intval($RS->fields['total']);

                if ($RS->fields['credit'] != ''){
                    //   總分($all_total_grade) = $total_grade * 那一個科目的學分數 (沒設學分數 則 預設值 為 1 學分credit )
                    if ($total_grade == ''){
                        $all_total_grade += 0;
                    }else{
                        $all_total_grade += $total_grade * $RS->fields['credit'];
                    }
                    $total_credit1 += $RS->fields['credit'];

                }else{
                    //   總分($all_total_grade) = $total_grade * 那一個科目的學分數 (沒設學分數 則 預設值 為 1 學分credit)
                    if ($total_grade == ''){
                        $all_total_grade += 0;
                    }else{
                        $all_total_grade += $total_grade * 1;
                    }
                    $total_credit1 += 1;
                }

                //  每一個 科目 的總分 (end)

                $result .= '<tr class="' . $col . ' font01" > ';
                if (strpos($title, 'course_name') !== false){
                    $result .= '    <td  align="center" nowrap="noWrap" >' . $class_name   . '</td>';
                }
                if (strpos($title, 'teacher') !== false){
                    $result .= '	<td  align="center" nowrap="noWrap" >' . $RS->fields['teacher'] . '</td>';
                }

                //  課程起訖 (begin)
                if (strpos($title, 'period') !== false){
                    if (($RS->fields['st_begin'] == '') && ($RS->fields['st_end'] == '')){
                        $msg = $MSG['unlimit'][$sysSession->lang];
                    }else if (($RS->fields['st_begin'] != '') && ($RS->fields['st_end'] == '')){
                               $msg = $MSG['from2'][$sysSession->lang] . $RS->fields['st_begin'] . "<br>" . $MSG['to2'][$sysSession->lang] . $MSG['unlimit'][$sysSession->lang];
                          }else if (($RS->fields['st_begin'] == '') && ($RS->fields['st_end'] != '')){
                                    $msg = $MSG['from2'][$sysSession->lang] . $MSG['unlimit'][$sysSession->lang] . '<br>' . $MSG['to2'][$sysSession->lang] . $RS->fields['st_end'];
                                }else if (($RS->fields['st_begin'] != '') && ($RS->fields['st_end'] != '')){
                                                        $msg = $MSG['from2'][$sysSession->lang] . $RS->fields['st_begin'] . "<br>" . $MSG['to2'][$sysSession->lang] . $RS->fields['st_end'];
                                }
                    $result .=  '	<td  align="center" nowrap="noWrap">' . $msg . '</td>';
                }
                //  課程起訖 (end)

                //  課程狀態
                if (strpos($title, 'course_state') !== false){
                    $result .= ' 	<td align="left" >' . $cs_status[$RS->fields['status']] . ' </td>';
                }
                //  及格標準
                if (strpos($title, 'fair_grade') !== false){
                    $result .= '	<td align="center" nowrap="noWrap" >' . $RS->fields['fair_grade'] . '</td>';
                }
                //  各科總成績
                if (strpos($title, 'every_grade') !== false){
                    $result .= '	<td align="center" nowrap="noWrap" >' . $total_grade . ' </td>';
                }
                //  本科學分
                if (strpos($title, 'every_credit') !== false){
                    if ($RS->fields['credit'] != ''){
                        $temp = $RS->fields['credit'];
                    }else{
                        $temp = 1;
                    }
                    $result .= '	<td  align="center" nowrap="noWrap" >' . $temp . '</td>';
                }

                // 實得學分
                if (strpos($title, 'real_credit') !== false){
                    $result .= '	<td  align="center" nowrap="noWrap" >' . $RS->fields['real_credit'] .'</td>';
                }
                if ($total_grade >= $RS->fields['fair_grade']){
                    $real_credit += $RS->fields['real_credit'];
                }

	            $result .= '</tr>';

                $RS->MoveNext();
		    }
		}
		//  $major_count > 0 end

        if (($total_credit1 != 0) || ($total_credit1 != '')){
            $total_avge = round($all_total_grade/$total_credit1,1);
        }else{
            $total_avge = 0;
        }

        if ($colspan1 == 0){
            $colspan1 = 1;
        }

        if ($colspan2 == 0){
            $colspan2 = 1;
        }

        $result .= '<tr class="cssTrHead" > ' .
                   '	<td  align="left" colspan="' . $colspan . '" nowrap="noWrap" >' .
                   $MSG['title8'][$sysSession->lang] . ' => ' .
                   $total_avge . '&nbsp;&nbsp;&nbsp;' .
                   $MSG['real_credit'][$sysSession->lang] . ' => ' .
                   $real_credit .
                   '</td>' .
                   '</tr>' .
				   '     </table>'.
                   ' </td>'.
	               '</tr>'.
                   '</table>';

	    return $result;
	}
?>

<?php
	/**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                               *
	*                                                                                                 *
	*		Programmer: Amm Lee                                                                       *
	*		Creation  : 2003/09/23                                                                    *
	*		work for  :  �ԲӦ��Z �� HTML file                                                                  *
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
	 *   ���� �Y�@ �b�� �ҭ׽ҵ{ �� ���Z �`��
	 *
	 *  parameter : $username -> �b��
	 *              $title    -> ���Z��W�ҭn�e�{���ӥ�
	 * @return string (�@��html structure  �� string )
	 **/

	function grade_html($username,$title){
	    return class_grade_html(0,$username,$title);
	}

	/**
	 * class_grade_html($class_id,$username,$title)
	 *   ���� �Y�@ �b�� �ҭ׽ҵ{ �� ���Z �`��
	 *
	 *  parameter : $class_id -> �Z�ťN�X
	 *              $username -> �b��
	 *              $title    -> ���Z��W�ҭn�e�{���ӥ�
	 * @return string (�@��html structure  �� string )
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

		//  �d�߱b�������
	    list($first_name,$last_name) = dbGetStSr('WM_user_account', 'first_name,last_name', "username='{$username}'", ADODB_FETCH_NUM);
        $realname = checkRealname($first_name, $last_name);

        $result .=  '			<tr class="cssTrHead" > '.
        		    '           <td colspan="' . $colspan . '" class="font01" >' .
        		    ($class_id ? ('<font color="#FF0000">' . $MSG['title14'][$sysSession->lang] . $csname . '</font><br>') : '') .
        		    $realname . '(' . $username . ') ' . $MSG['title9'][$sysSession->lang] .
        		    '           </td>' .
        		    '         </tr>' .
        			'			<tr class="cssTrHead" > ';

        //   �ҵ{�W��
        if (strpos($title, 'course_name') !== false){
    	    $result .=    '             <td  align="center" nowrap="noWrap" >' . $MSG['course_name'][$sysSession->lang]   . '</td>';
            $colspan1++;
        }
        //  �½ҦѮv
        if (strpos($title, 'teacher') !== false){
            $result .=   '				<td  align="center" nowrap="noWrap" >' . $MSG['title'][$sysSession->lang] . '</td>';
            $colspan1++;
        }

        //  �ҵ{�_�W
        if (strpos($title, 'period') !== false){
            $result .=   '				<td  align="center" nowrap="noWrap" >' . $MSG['title1'][$sysSession->lang] . '</td>';
            $colspan1++;
        }
        //  �ҵ{���A
        if (strpos($title, 'course_state') !== false){
            $result .=  ' 			<td  align="center" nowrap="noWrap" >' . $MSG['td_status'][$sysSession->lang] . ' </td>';
            $colspan1++;
        }
        //  �ή�з�
        if (strpos($title, 'fair_grade') !== false){
            $result .=  '				<td  align="center" nowrap="noWrap" >' . $MSG['title4'][$sysSession->lang] . '</td>';
            $colspan1++;
        }
        //  �U���`���Z
        if (strpos($title, 'every_grade') !== false){
           $result .= '				<td  align="center" nowrap="noWrap" >' . $MSG['title5'][$sysSession->lang] . ' </td>';
           $colspan2++;
        }
        //  ����Ǥ�
        if (strpos($title, 'every_credit') !== false){
            $result .= '				<td  align="center" nowrap="noWrap" >' . $MSG['title2'][$sysSession->lang] . '</td>';
            $colspan2++;
        }
        //  ��o�Ǥ�
        if (strpos($title, 'real_credit') !== false){
            $result .= '				<td  align="center" nowrap="noWrap" >' . $MSG['title3'][$sysSession->lang] . '</td>';
        }

        $result .= '         </tr>';

        //  ��X�ǥͪ��׽ҰO�� (begin)
        $sqls = str_replace('%USERNAME%', $username, $Sqls['get_student_all_course_grade_list']);
		$sysConn->SetFetchMode(ADODB_FETCH_ASSOC);
        $RS = $sysConn->Execute($sqls);
        //  ��X�ǥͪ��׽ҰO�� (end)

        //  ���ǥ� �`�@�״X����
        $major_count = $RS->RecordCount();

        //  $major_count > 0 begin
        if ($major_count == 0){
            $result .= '<tr class="cssTrEvn" >' .
                       ' 	<td  align="center" colspan="' . $colspan . '" nowrap="noWrap" >' . $MSG['title7'][$sysSession->lang] . ' </td>'.
                       '</tr>';
        }else{
			while (!$RS->EOF){

                $col=$col=='cssTrEvn'?'cssTrOdd':'cssTrEvn';

                //  ����ҵ{�W��
                $lang = unserialize($RS->fields['caption']);
            	$class_name = $lang[$sysSession->lang];

                //  �C�@�� ��� ���`�� (begin)
                $total_grade = intval($RS->fields['total']);

                if ($RS->fields['credit'] != ''){
                    //   �`��($all_total_grade) = $total_grade * ���@�Ӭ�ت��Ǥ��� (�S�]�Ǥ��� �h �w�]�� �� 1 �Ǥ�credit )
                    if ($total_grade == ''){
                        $all_total_grade += 0;
                    }else{
                        $all_total_grade += $total_grade * $RS->fields['credit'];
                    }
                    $total_credit1 += $RS->fields['credit'];

                }else{
                    //   �`��($all_total_grade) = $total_grade * ���@�Ӭ�ت��Ǥ��� (�S�]�Ǥ��� �h �w�]�� �� 1 �Ǥ�credit)
                    if ($total_grade == ''){
                        $all_total_grade += 0;
                    }else{
                        $all_total_grade += $total_grade * 1;
                    }
                    $total_credit1 += 1;
                }

                //  �C�@�� ��� ���`�� (end)

                $result .= '<tr class="' . $col . ' font01" > ';
                if (strpos($title, 'course_name') !== false){
                    $result .= '    <td  align="center" nowrap="noWrap" >' . $class_name   . '</td>';
                }
                if (strpos($title, 'teacher') !== false){
                    $result .= '	<td  align="center" nowrap="noWrap" >' . $RS->fields['teacher'] . '</td>';
                }

                //  �ҵ{�_�W (begin)
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
                //  �ҵ{�_�W (end)

                //  �ҵ{���A
                if (strpos($title, 'course_state') !== false){
                    $result .= ' 	<td align="left" >' . $cs_status[$RS->fields['status']] . ' </td>';
                }
                //  �ή�з�
                if (strpos($title, 'fair_grade') !== false){
                    $result .= '	<td align="center" nowrap="noWrap" >' . $RS->fields['fair_grade'] . '</td>';
                }
                //  �U���`���Z
                if (strpos($title, 'every_grade') !== false){
                    $result .= '	<td align="center" nowrap="noWrap" >' . $total_grade . ' </td>';
                }
                //  ����Ǥ�
                if (strpos($title, 'every_credit') !== false){
                    if ($RS->fields['credit'] != ''){
                        $temp = $RS->fields['credit'];
                    }else{
                        $temp = 1;
                    }
                    $result .= '	<td  align="center" nowrap="noWrap" >' . $temp . '</td>';
                }

                // ��o�Ǥ�
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

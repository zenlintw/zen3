<?php
	/**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                               *
	*                                                                                                 *
	*		Programmer: Amm Lee                                                                       *
	*		Creation  : 2003/09/23                                                                    *
	*		work for  :  基本資料 & 修課記錄 &　學習成果                                                                      *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                        *
	*       @version:$Id: stud_query1.php,v 1.1 2010/02/24 02:38:45 saly Exp $                                                                                          *
	**************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
 	require_once(sysDocumentRoot . '/lang/people_manager.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/lib_statistics.php');

	$sysSession->cur_func = '1500400100';
	$sysSession->restore();
	if (!aclVerifyPermission(1500400100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$DIRECT_MEMBER = isset($DIRECT_MEMBER) ? true : false;
	if (empty($uri_target)) {
		$uri_target = 'stud_query.php?p=1';
	}

	$username = $_POST['user'] ? preg_replace('/[^\w.-]+/', '', $_POST['user']) : $sysSession->username;
	$lang     = strtolower($sysSession->lang);
	$msgtp    = $_POST['msgtp'] ? intval($_POST['msgtp']) : 1;
	$sortby   = intval($_POST['sortby']);
	$order    = in_array($_POST['order'], array('desc', 'asc')) ? $_POST['order'] : 'desc';
	$state_val = trim($_POST['state_val']);
	$return    = $MSG['title130'][$sysSession->lang];

	//更新學員的學習記錄
	setPersonalRecrd(date("Y-m-d"), $username);

    $ACADEMIC_MODIFY_MEMBER = true;

$js = <<< EOF

	/**
    * 修改個人基本資料
    **/
	function modify(a){
	    window.location.replace('modify_stud_info1.php?username='+a);
    }

    // 個人基本資料 &　修課記錄　&　學習成果　(chgHistory)
    function chgHistory(val) {
		var obj = document.getElementById("actFm");
		if ((typeof(obj) != "object") || (obj == null)) return false;
		obj.msgtp.value = val;
		obj.sortby.value = '';
		obj.order.value = '';
		obj.submit();
	}

    /**
    * 正在修,已修過的 修課記錄排序, 學習成果排序
    **/
    function chgPageSort(val) {
        var obj = document.getElementById("actFm");
		if ((typeof(obj) != "object") || (obj == null)) return false;

		obj.order.value = obj.order.value == 'asc' ? 'desc' : 'asc';
		obj.sortby.value = val;
        obj.state_val.value = '';
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
	if (($DIRECT_MEMBER) || ($ENROLL_MEMBER) || ($ACADEMIC_CLASS_MEMBER) || ($ACADEMIC_TEACHER)) {
		$js .= $direct_js;
	} else {
		$js .= <<< EOF

    function go_list() {
		window.location.replace('{$uri_target}');
}
EOF;
	}


	showXHTML_head_B($MSG['title27'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E();

	showXHTML_body_B();
        $ary = array();

		if(! empty($ACADEMIC_TEACHER))	// 管理者 - 導師管理 - 教師查詢 - 授課記錄
			$ary[] = array($MSG['teach_record'][$sysSession->lang], 'addTable1', 'chgHistory(1)');
		else{
			$ary[] = array($MSG['title81'][$sysSession->lang], 'addTable1', 'chgHistory(1)');
			$ary[] = array($MSG['title57'][$sysSession->lang], 'addTable2', 'chgHistory(2)');
			$ary[] = array($MSG['title58'][$sysSession->lang], 'addTable3', 'chgHistory(3)');
		}
		showXHTML_tabFrame_B($ary, $msgtp, '', 'tabsMsgView');
        // 個人 (begin)
        switch ($msgtp){
            case 1:     //  基本資料
                include_once(sysDocumentRoot . '/academic/stud/modify_stud_info.php');
                break;
            case 2:     //  修課記錄
				include_once(sysDocumentRoot . '/academic/stud/course_record.php');
                break;
            case 3:     //  學習成果
                include_once(sysDocumentRoot . '/academic/stud/learn_result.php');
                break;
        }
        // 個人 (end)
        showXHTML_tabFrame_E();

        showXHTML_form_B('action="' . $_SERVER['PHP_SELF']. '" method="post" enctype="multipart/form-data" style="display:none"', 'actFm');
	        showXHTML_input('hidden', 'msgtp' , $msgtp   , '', '');
	        showXHTML_input('hidden', 'user'  , $username, '', '');
	        showXHTML_input('hidden', 'sortby', $sortby  , '', '');
			if(! empty($ACADEMIC_TEACHER)){	// 管理者 - 導師管理 - 教師查詢 - 授課記錄
				if ($_POST['cond_type'] != '') $cond_type = $_POST['cond_type'];

				if (! isset($cond_type)) $cond_type = 0;

				$query_txt = trim($_POST['queryTxt']);
				$query_txt1 = stripslashes($query_txt);

				if ($_SERVER['PHP_SELF'] == '/academic/teacher/teacher_list.php'){
				   $form_action = 'teacher_list.php';
				}else{
				  $form_action = 'teacher_show_list.php';
				}

				$cur_page = intval($_POST['page_no']);

				showXHTML_input('hidden', 'user_role', $_POST['user_role'], '', '');
				showXHTML_input('hidden', 'page_no'  , $cur_page, '', '');
    			showXHTML_input('hidden', 'cond_type', $cond_type, '', '');
    			showXHTML_input('hidden', 'queryTxt' , htmlspecialchars($query_txt1), '', '');
    			showXHTML_input('hidden', 'back_href', $form_action, '', '');
			}
	        showXHTML_input('hidden', 'order', $order, '', '');
	        showXHTML_input('hidden', 'state_val', $state_val, '', '');
			showXHTML_input('hidden', 'class_id', $_POST['class_id'], '', '');
    	showXHTML_form_E();
		if (($DIRECT_MEMBER) || ($ENROLL_MEMBER)) {
			showXHTML_form_B('method="post" enctype="multipart/form-data" style="display:none"', 'fmAction');
				showXHTML_input('hidden', 'page'   , $dt_page_no, '', '');
				showXHTML_input('hidden', 'roles'  , $dt_roles  , '', '');
				showXHTML_input('hidden', 'kind'   , $dt_kind   , '', '');
				showXHTML_input('hidden', 'keyword', $dt_keyword, '', '');
				showXHTML_input('hidden', 'lsList' , $dt_lsList , '', '');
			showXHTML_form_E();
		}

	showXHTML_body_E();
?>

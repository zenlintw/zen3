<?php
	/**
	 * 設定課程內容及審核
	 *
	 * @since   2015/06/12
	 * @author  Spring
	 * @version $Id: m_course_set.php,v 1.1 2010/02/24 02:38:19 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/lib_encrypt.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	require_once(sysDocumentRoot . '/lang/course_manage.php');
	
	$sysSession->cur_func = '700400100';
	$sysSession->restore();

	if (!aclVerifyPermission(700400100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

    $result = array(
        'success' => true,
        'id'      => 0,
        'ticket'  => '',
        'message' => $MSG['set_success'][$sysSession->lang]
    );
    if (!isset($_POST['ck'])) {
        $result['success'] = false;
        $result['message'] = 'No course select!';
        echo json_encode($result);
        die();
    }
    // 解析要設定的課程 
    foreach ($_POST['ck'] as $key => $value) {
        $ckcid = sysDecode($value);
        if (strlen($ckcid) === 8) {
            $course_array[$key] = sysDecode($value);
        }
    }
    $csids = implode(',', $course_array);
    switch($_POST['type']) {
        case 'capacity':
            $result['id'] = 'capacity';
            // 相容以前資料輸入的單位為 GB 需轉為 KB 
            dbSet('`WM_term_course`', sprintf('`quota_limit` = %d', intval($_POST['capacity']*1024*1024)), sprintf('`course_id` in (%s)', $csids));
            if ($sysConn->ErrorNo() > 0) {
                wmSysLog('700400100', $sysSession->school_id , 0 , 2, 'manager', $_SERVER['PHP_SELF'], $username.'課程容量設定失敗!');
                $result = array(
                    'success' => false,
                    'id' => 'capacity',
                    'message'=> '課程容量設定失敗'
                );
            }
            break;
        case 'verify':
            $result['id'] = 'verify';
            dbSet('`WM_review_sysidx`', sprintf('`flow_serial`= %d', intval($_POST['verify'])), sprintf('`discren_id` in(%s)', $csids));
            if ($sysConn->ErrorNo() > 0) {
                wmSysLog('700400100', $sysSession->school_id , 0 , 2, 'manager', $_SERVER['PHP_SELF'], $username.'課程權限設定失敗!');
                $result = array(
                    'success' => false,
                    'id' => 'verify',
                    'message'=> '課程權限設定失敗'
                );
            }
            break;
        default:
            die('Access denied!');
            break;
    }

    echo json_encode($result);
    die();

?>

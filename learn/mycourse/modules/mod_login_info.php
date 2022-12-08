<?php
	/**
	 * 登入訊息
	 *
	 * @since   2004/09/03
	 * @author  ShenTing Lin
	 * @version $Id: mod_login_info.php,v 1.1 2010/02/24 02:39:09 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	if (!defined('MYCOURSE_MODULE') || MYCOURSE_MODULE === false) {
		include_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
		include_once(sysDocumentRoot . '/lib/interface.php');
		include_once(sysDocumentRoot . '/lib/acl_api.php');
	}
	
	$sysSession->cur_func='700700100';
	$sysSession->restore();
	if (!aclVerifyPermission(700700100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	/**
	 * 轉換秒數為天、時、分與秒
	 * @param integer $sec : 秒數
	 * @param boolean $show_day : 是否要顯示天數
	 * @param string  $str : 自訂顯示的格式 (預設：'%d days, %2$02d:%3$02d:%4$02d')
	 * @return 格式化後的字串
	 **/
	function sec2time($sec, $show_day=true, $str='') {
		global $sysSession, $MSG;

		$tmp = intval($sec);
		$sec = $tmp % 60;
		$tmp = floor($tmp / 60);
		$min = $tmp % 60;
		$tmp = floor($tmp / 60);
		if ($show_day) {
			$hou = $tmp % 24;
			$day = floor($tmp / 24);
			if (empty($str)) $str = $MSG['days'][$sysSession->lang] . $MSG['time_str'][$sysSession->lang];
		} else {
			$hou = $tmp;
			$day = 0;
			if (empty($str)) $str = $MSG['time_str'][$sysSession->lang];
		}
		return sprintf($str, $day, $hou, $min, $sec);
	}

	$theme = (empty($sysSession->theme)) ? '/theme/default/learn/' : "/theme/{$sysSession->theme}/{$sysSession->env}/";
	$img = '<img src="' . $theme . 'my_left.gif" width="9" height="11" border="0" align="absmiddle">';

	$lt = intval($myConfig->getValues('login_info', 'login_times'));
	$tt = intval($myConfig->getValues('login_info', 'total_time' ));
	$ll = trim($myConfig->getValues('login_info', 'last_login'));
	$li = trim($myConfig->getValues('login_info', 'last_ip'   ));
	if (empty($ll)) $ll = $MSG['msg_first_login'][$sysSession->lang];
	if (empty($li)) $li = $MSG['msg_first_login'][$sysSession->lang];

	$showday = ($tt >= 86400);
	$tt = sec2time($tt, $showday);
	$ary = array(
		'count' => sprintf($MSG['login_count'][$sysSession->lang]   , '<span class="cssFont01">' . $lt . '</span>'),
		'last'  => sprintf($MSG['login_last'][$sysSession->lang]    , '<span class="cssFont01">' . $ll . '</span>'),
		'from'  => sprintf($MSG['login_from'][$sysSession->lang]    , '<span class="cssFont01">' . $li . '</span>'),
		'sum'   => sprintf($MSG['login_time_sum'][$sysSession->lang], '<span class="cssFont01">' . $tt . '</span>')
	);
	$wd = $defSize - 10;   // 主要視窗大小的設定
	showXHTML_table_B('width="' . $wd . '" border="0" cellspacing="0" cellpadding="0"');
		showXHTML_tr_B('class="cssTrEvn"');
			showXHTML_td('nowrap', $img . $sysSession->realname . $ary['count']);
			if ($defSize < $defRSize) {
				showXHTML_tr_E();
				showXHTML_tr_B('class="cssTrEvn"');
			}
			showXHTML_td('nowrap', $img . $ary['last']);
            if (!defined('sysEnableMooc') || (sysEnableMooc < 1)) {
                // 沒有啟用 MOOC 時，才顯示 help 按鈕
                // show help icon
                // copy from interface.php #513
                if ($_SERVER['PHP_SELF'] != '/wmhelp.php') {
                    $o_whicon = new wmhelp($sysSession->school_id);
                    $o_whicon->setHelpFilename($_SERVER['PHP_SELF']);
                    $pic = getThemeFile('help.gif');
                    if (empty($pic)) $pic = $theme . 'help.gif';
                    if (defined('DEMO')) {
                        showXHTML_td('nowrap rowspan="2"', '<img border="0" src="' . $pic . '" width="21" height="21" border="0" align="middle" alt="Help" title="Help">');
                    } else {
                        if (isHelpWriter()) {
                            // $o_whicon->isHelpfileExists()
                            showXHTML_td('nowrap rowspan="2"', '<a href="/wmhelp.php?url=' . urlencode($_SERVER['PHP_SELF']) . '" target="_blank"><img border="0" src="' . $pic . '" width="21" height="21" border="0" align="middle" alt="Help" title="Help"></a>');
                        } else {
                            // client 端開起 online help 檔案
                            if ($o_whicon->isHelpfileExists()){
                                showXHTML_td('nowrap rowspan="2"', '<a href="javascript:" onClick="window.open(\'' . $o_whicon->help_url . '\',\'aaa\',\'height=300,width=400,resizable=1,scrollbars=1,toolbar=0\')"><img border="0" src="' . $pic . '" width="21" height="21" border="0" align="middle" alt="Help" title="Help"></a>');
                            }
                        }
                    }
                }
            }
		showXHTML_tr_E();
		showXHTML_tr_B('class="cssTrEvn"');
			showXHTML_td('nowrap', $img . $ary['from']);
			if ($defSize < $defRSize) {
				showXHTML_tr_E();
				showXHTML_tr_B('class="cssTrEvn"');
			}
			showXHTML_td('nowrap', $img . $ary['sum']);
		showXHTML_tr_E();
	showXHTML_table_E();
?>

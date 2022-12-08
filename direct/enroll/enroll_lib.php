<?php
	/**
	 * 修課指派共用函式
	 *
	 * @since   2004/07/27
	 * @author  ShenTing Lin
	 * @version $Id: enroll_lib.php,v 1.1 2010/02/24 02:38:57 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/direct_enroll.php');
	require_once(sysDocumentRoot . '/lang/direct_member_list.php');
	require_once(sysDocumentRoot . '/lib/lib_ini.php');

	if (!function_exists('divMsg')) {
		/**
		 * 處理資料，過長的部份隱藏
		 * @param integer $width   : 要顯示的寬度
		 * @param string  $caption : 顯示的文字
		 * @param string  $title   : 浮動的提示文字，若沒有設定，則跟 $caption 依樣
		 * @return string : 處理後的文字
		 **/
		function divMsg($width=100, $caption='&nbsp;', $title='', $without_title=false) {
			if (empty($title)) $title = $caption;
			return $without_title ? ('<div style="width: ' . $width . 'px; overflow:hidden;">' . $caption . '</div>') : ('<div style="width: ' . $width . 'px; overflow:hidden;" title="' . $title . '">' . $caption . '</div>');
		}
	}

	function showStep($ary, $now) {
		if (!is_array($ary)) return false;
		$Mimura = trim($now);

		$js = <<< BOF
	var isIE = false, isMZ = false;
	var BVER = "5.0";
	/**
	 * 檢查瀏覽器 (Check Browser)
	 * @version 1.1
	 * @return
	 **/
	function chkBrowser() {
		if (navigator.userAgent.indexOf('MSIE') > -1) {
			isIE = true;
			if (navigator.userAgent.indexOf('MSIE 5.0') > -1) BVER = "5.0";
			if (navigator.userAgent.indexOf('MSIE 5.5') > -1) BVER = "5.5";
			if (navigator.userAgent.indexOf('MSIE 6.0') > -1) BVER = "6.0";
		}

		if (navigator.userAgent.indexOf('Gecko') > -1) {
			isMZ = true;
		}
	}
	chkBrowser();

	var preIdx = "{$Mimura}";
	function stepMouseEvent(evnt, val) {
		var obj = null, node = null;
		var attr = null;
		var idx = "", tag = "";
		if (isMZ) {
			if (parseInt(evnt.button) > 0) return true;
			obj = evnt.target;
		} else {
			obj = event.srcElement;
		}
		if (obj == null) return false;
		while ((tag = obj.tagName.toLowerCase()) != "body") {
			attr = obj.getAttribute("Mimura");
			if ((attr != null) && (attr = "true")) {
				idx = obj.getAttribute("MyAttr");
				if (preIdx != idx) {
					obj.className = val;
				}
				return true;
			}
			obj = obj.parentNode;
			while (obj.nodeType != 1) {
				obj = obj.parentNode;
				if (obj == null) break;
			}
			if (obj == null) break;
		}
	}

	document.onmouseover = function (evnt) {
		stepMouseEvent(evnt, "cssTbFocus");
	};

	document.onmouseout = function (evnt) {
		stepMouseEvent(evnt, "cssTbBlur");
	};

	document.onclick = function (evnt) {
		var obj = null, node = null, attr = null;
		var idx = "", tag = "";
		if (isMZ) {
			if (parseInt(evnt.button) > 0) return true;
			obj = evnt.target;
			if (evnt.target.tagName.toLowerCase() == "img") return true;
		} else {
			if (event.srcElement.tagName.toLowerCase() == "img") return true;
			obj = event.srcElement;
		}
		if (obj == null) return false;
		while ((tag = obj.tagName.toLowerCase()) != "body") {
			attr = obj.getAttribute("Mimura");
			if ((attr != null) && (attr = "true")) {
				idx = obj.getAttribute("MyAttr");
				if (preIdx != idx) {
					if ((typeof(evntLst) == "object") && (evntLst[idx] != "")) {
						var tmp = eval(evntLst[idx]);
					}
				}
				return true;
			}
			obj = obj.parentNode;
			while (obj.nodeType != 1) {
				obj = obj.parentNode;
				if (obj == null) break;
			}
			if (obj == null) break;
		}
	};
BOF;

		showXHTML_script('inline', $js);
		showXHTML_table_B('border="0" cellspacing="1" cellpadding="3"');
			showXHTML_tr_B();
				$cnt = count($ary);
				$event = array();
				for ($i = 0; $i < $cnt; $i++) {
					if (intval($ary[$i][2]) > 0) {
						if ($ary[$i][1] == $Mimura) {
							showXHTML_td('Mimura="true" MyAttr="' . $ary[$i][1] . '" class="cssTbFocus"', $ary[$i][0]);
						} else {
							$click = (isset($ary[$i][3]) && !empty($ary[$i][3])) ? addcslashes($ary[$i][3], '"') . '; ' : '';
							showXHTML_td('Mimura="true" MyAttr="' . $ary[$i][1] . '" class="cssTbBlur" onclick="' . $click . '"', '<a href="javascript:;" onclick="' . $click . 'event.cancelBubble = true; return false;" class="cssAnchor">' . $ary[$i][0] . '</a>');
						}
					} else {
						showXHTML_td('MyAttr="' . $ary[$i][1] . '" class="cssTbBlur"', $ary[$i][0]);
					}

					if ($i < ($cnt - 1)) {
						showXHTML_td('', ' --&gt; ');
					}
				}
			showXHTML_tr_E();
		showXHTML_table_E();
		return true;
	}

	/**
	 * 儲存挑選人員的資料
	 * @return
	 **/
	function storeMemberData() {
		global $_POST, $objAssoc;

		$data = trim($_POST['user']);
		$ary  = explode(',', $data);
		$objAssoc->setValues('member', '', $ary, true);

		$data = trim($_POST['lsList']);
		$ary  = explode(',', $data);
		$objAssoc->setValues('member_list', '', $ary, true);

		$data = trim($_POST['msgtp']);
		$objAssoc->setValues('member_other', 'msgtp'  , $data);
		$data = intval($_POST['page']);
		$objAssoc->setValues('member_other', 'page'   , $data);
		$data = trim($_POST['roles']);
		$objAssoc->setValues('member_other', 'roles'  , $data);
		$data = trim($_POST['kind']);
		$objAssoc->setValues('member_other', 'kind'   , $data);
		$data = trim($_POST['keyword']);
		$objAssoc->setValues('member_other', 'keyword', $data);
	}

	/**
	 * 儲存挑選課程的資料
	 * @return
	 **/
	function storeCourseData() {
		global $_POST, $objAssoc;

		$data = trim($_POST['courses']);
		$ary  = explode(',', $data);
		$objAssoc->setValues('course', '', $ary, true);

		$data = trim($_POST['lsList']);
		$ary  = explode(',', $data);
		$objAssoc->setValues('course_list', '', $ary, true);

		$data = intval($_POST['page']);
		$objAssoc->setValues('course_other', 'page'   , $data);
		$data = trim($_POST['sortby']);
		$data = in_array($data, array('csid', 'caption', 'enroll_date', 'study_date')) ? $data : 'csid';
		$objAssoc->setValues('course_other', 'sortby' , $data);
		$data = trim($_POST['order']);
		$data = in_array($data, array('asc', 'desc')) ? $data : 'asc';
		$objAssoc->setValues('course_other', 'order'  , $data);
		$data = trim($_POST['ticket']);
		$objAssoc->setValues('course_other', 'ticket' , $data);
		$data = trim($_POST['keyword']);
		$objAssoc->setValues('course_other', 'keyword', $data);
	}

	function initAssoc($isNew=TRUE) {
		global $objAssoc;

		if ($isNew) {
			$objAssoc->erase();
			$filename = $objAssoc->getStorePath();
			touch($filename);
		}
		$ary = array(
			'help'   => 1,
			'member' => 0,
			'course' => 0,
			'review' => 0,
			'result' => 0,
		);
		$objAssoc->setValues('function', '', $ary);
	}

	// 建立儲存資料的物件
	$objAssoc = new assoc_data();
	$filename = sysTempPath . '/direct_' . $_COOKIE['idx'] . '.ini';
	$objAssoc->has_sections = true;
	$objAssoc->setStorePath($filename);

?>

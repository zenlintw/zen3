<?php
    /**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: amm lee                                                         *
	 *		Creation  : 2004/01/08                                                            *
	 *		work for  : 匯出人員資料 (第二步驟 -> 課程管理)                                                                      *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.4                                *
	 *      $Id: course_set.php,v 1.1 2010/02/24 02:38:44 saly Exp $                                                                                          *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/academic/stud/course_tree.php');
	require_once(sysDocumentRoot . '/lang/course_manage.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '400500200';
	$sysSession->restore();
	if (!aclVerifyPermission(400500200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$lang = strtolower($sysSession->lang);
	$js = <<< EOF
	var theme = "{$sysSession->theme}";
	var school_name = "{$sysSession->school_name}";
	var lang = "{$lang}";
	var groupIdx = 0;
	var pageIdx = 1;   // 目前在第幾頁
	var pageNum = 1;
	var ticket = '';

	var xmlDocs = null, xmlHttp = null, xmlVars = null;

	// 訊息
	var MSG_SELECT_ALL    = "{$MSG['title18'][$sysSession->lang]}";
	var MSG_SELECT_CANCEL = "{$MSG['title19'][$sysSession->lang]}";
	var MSG_OPEN_GROUP    = "{$MSG['title20'][$sysSession->lang]}";
	var MSG_CLOSE_GROUP   = "{$MSG['title21'][$sysSession->lang]}";

    /**
	 * 全部展開或收攏
	 * @param boolean val : 展開或收攏
	 *     true  : 展開
	 *     false : 收攏
	 **/
	function csAllGpStatus(val) {
		var obj = null, nodes = null, attr = null,obj2 = null;
		obj = document.getElementById("csGpCsTree");
		nodes = obj.getElementsByTagName("img");
		for (var i = 0; i < nodes.length; i++) {
			attr = nodes[i].getAttribute("attr");
			if ((attr == null) || (attr == "")) continue;
			nodes[i].src = theme + ((val) ? csIcon[1] : csIcon[0]);
		}
		nodes = document.getElementsByTagName("ol");
		for (var i = 0; i < nodes.length; i++) {
			nodes[i].style.display = (val) ? "" : "none";
		}
		obj = document.getElementById("open_group");
		obj2 = document.getElementById("open_group2");
		if (val){
			obj.value = MSG_CLOSE_GROUP;
			obj.onclick = function () {
				csAllGpStatus(false);
			};

			obj2.value = MSG_CLOSE_GROUP;
			obj2.onclick = function () {
				csAllGpStatus(false);
			};
		}else{
			obj.value = MSG_OPEN_GROUP;
			obj.onclick = function () {
				csAllGpStatus(true);
			};
			obj2.value = MSG_OPEN_GROUP;
			obj2.onclick = function () {
				csAllGpStatus(true);
			};
		}

	}

    /**
	 * checkbox 的選取動作
	 * @pram string objName 指定在哪個物件上，不指定則預設在 document 上
	 * @pram integer actType 選取的動作
	 *     1. 0: 全部取消
	 *     2. 1: 全部選取
	 *     3. 2: 反向選取
	 * @return integer 錯誤編號
	 *     1. 0: 沒有錯誤
	 *     2. 1: 找不到指定的物件
	 *     3. 2: 找不到任何的 input 物件
	 * @access public
	 *
	 * 其它說明：新增一項屬性 exclude：在 input 中若有設定這項屬性，則程式會忽略該物件
	 * 例如：
	 *     以下的例子，本程式將會忽略該物件
	 *
	 *         <input type="checkbox" exclude> or
	 *         <input type="checkbox" exclude="true"> (建議)
	 *
	 *     而以下的例子，該物件會依指定的動作而有所動作
	 *
	 *         <input type="checkbox"> (建議) or
	 *         <input type="checkbox" exclude="false">
	 **/
	function select_func2(objName, actType) {
		var obj = null, nodes = null, attr = null,obj2 = null;
		var cnt = 0;
		var isSel = false;

		if (typeof(actType) == "boolean") {
			actType = actType ? 1 : 0;
		}
		obj = (objName.length == 0) ? document : document.getElementById(objName);
		if ((typeof(obj) != "object") || (obj == null)) return 1;
		nodes = obj.getElementsByTagName("input");
		if ((nodes == null) || (nodes.length <= 0)) return 2;
		cnt = nodes.length;
		if (parseInt(actType) < 2) {
			// 全選或全消
			isSel = (actType > 0) ? true : false;
			for (var i = 0; i < cnt; i++) {
				if (nodes[i].type == "checkbox") {
					attr = nodes[i].getAttribute("exclude");

					if ((attr == null) || (attr == "false")){
						nodes[i].checked = actType;
					}
				}
			}
		} else {
			// 反向選取
			for (var i = 0; i < cnt; i++) {
				if (nodes[i].type == "checkbox") {
					attr = nodes[i].getAttribute("exclude");
					if ((attr == null) || (attr == "false"))
						nodes[i].checked = !nodes[i].checked;
				}
			}
		}

		obj = document.getElementById("select_all");
		obj2 = document.getElementById("select_all2");

		if (actType){
		    obj.value = MSG_SELECT_CANCEL;
		    obj.onclick = function () {
                select_func2(objName,false);
            };
            obj2.value = MSG_SELECT_CANCEL;
		    obj2.onclick = function () {
                select_func2(objName,false);
            };
		}else{
		    obj.value = MSG_SELECT_ALL;
	        obj.onclick = function () {
                select_func2(objName,true);
            };
            obj2.value = MSG_SELECT_ALL;
	        obj2.onclick = function () {
                select_func2(objName,true);
            };
	    }

	}

	function chkSel(obj)
	{
		var obj1 = null, obj2 = null, nodes = null;
		var i = 0, m = 0, cnt = 0;
		try
		{
			if (typeof event == "object")
				obj2 = obj.parentNode.parentNode.parentNode.parentNode.childNodes[0].childNodes[1];
			else
				obj2 = obj.parentNode.parentNode.parentNode.previousSibling.childNodes[0].childNodes[1];
			}
		catch(ex)
		{
			obj2 = null;
		}
		if (obj2)
		{
			obj1 = document.getElementById("gp" + obj2.value);
			if (obj1)
			{
				nodes = obj1.getElementsByTagName("input");
				check_all = true;
				for (i = 0, m = 0, cnt = 0; i < nodes.length; i++)
				{
					if ((nodes[i].type == "checkbox") && (nodes[i].value != 10000000))
					{
						// m++;
						// if (nodes[i].checked) cnt++;
						if (!nodes[i].checked)
						{
						    check_all = false;
						    break;
						}
					}
				}
				if (i) obj2.checked = check_all; // (m == cnt);
				chkSel(obj2);
			}
		}

	}

	function select_course(obj)
	{
		var obj1 = null, obj2 = null, nodes = null;
		var i = 0, m = 0, cnt = 0, v = obj.checked;

		obj1  = document.getElementById("gp" + obj.value);
		if (obj1)
		{
			nodes = obj1.getElementsByTagName("input");
			for (var i = 0; i < nodes.length; i++)
			{
				if ((nodes[i].type == "checkbox") && (nodes[i].value != 10000000))
				{
					nodes[i].checked = v;
				}
			}
		}

		nodes = document.getElementById('tbGpCs').getElementsByTagName("input");
		if (nodes.length < 500) chkSel(obj);
		check_all = true;
		for (i = 0, m = 0, cnt = 0; i < nodes.length; i++)
		{
			if ((nodes[i].type == "checkbox") && (nodes[i].value != 10000000))
			{
				// m++;
				// if (nodes[i].checked) cnt++;
				if (!nodes[i].checked)
				{
				    check_all = false;
				    break;
				}
			}
		}
		if (i) {
			document.getElementById("ckgp10000000").checked = check_all; // (m == cnt);

			obj1 = document.getElementById("select_all");
			obj2 = document.getElementById("select_all2");
			if (check_all) // m == cnt)
			{
				obj1.value   = MSG_SELECT_CANCEL;
				obj1.onclick = function () {
					select_func2('tbGpCs', false);
				};
				obj2.value   = MSG_SELECT_CANCEL;
				obj2.onclick = function () {
					select_func2('tbGpCs', false);
				};
			}
			else
			{
				obj1.value   = MSG_SELECT_ALL;
				obj1.onclick = function () {
					select_func2('tbGpCs', true);
				};
				obj2.value   = MSG_SELECT_ALL;
				obj2.onclick = function () {
					select_func2('tbGpCs', true);
				};
			}
		}

	}

	/*
	 * 匯出課程資料
	*/
	function export_data(){
        var obj = null,nodes = null,obj2 = null;
        var temp = '',temp_gp = '',temp_gp2 = '',temp_course = '',txt = '',txt2 = '';


        obj = document.getElementById('tbGpCs');
        nodes = obj.getElementsByTagName("input");
	    if ((nodes == null) || (nodes.length <= 0)) return false;

        obj2 = document.getElementById("ckgp10000000");

        if (obj2.checked){
            temp_course = "10000000";

        }else{
    	    for (var i = 0, m = 0; i < nodes.length; i++) {
    	        temp = '';
    		    if (nodes[i].type == "checkbox"){
    			    if ((nodes[i].checked) && (nodes[i].value != 10000000)){

                        temp = nodes[i].id;

    			        if (temp.indexOf('ckcourse') != -1){
    			            temp_course += nodes[i].value + ',';

    			        }else if (temp.indexOf('ckgp') != -1){
    			            temp_gp += nodes[i].value + ',';
    			        }
    			    }
    		    }
    	    }

    	    if (temp_gp.length > 0){
    	        temp_gp = temp_gp.replace(/,$/,'');

    	        txt  = "<manifest>";
			    txt += "<course_id>" + temp_gp.toString() + "</course_id>";
			    txt += "</manifest>";

			    if (! xmlVars.loadXML(txt))
			        return false;

			    xmlHttp.open("POST", "query_course.php", false);
			    xmlHttp.send(xmlVars);

                xmlVars.loadXML(xmlHttp.responseText);

           //   alert('xmlHttp.responseText 261='+xmlHttp.responseText);

	       //     alert('xmlVars.xml 263='+xmlVars.xml);

	            nodes = xmlVars.getElementsByTagName("course_id");

	            if ((nodes != null) && (nodes.length > 0)) {
	                if (nodes[0].hasChildNodes()){
	                    temp_gp2 = nodes[0].firstChild.nodeValue;
	                }
	            }
    	    }

        }

	    if (temp_gp2.length > 0){
	        if (temp_course.length > 0){
	            temp_course = temp_course + temp_gp2;
	        }else{
	            temp_course = temp_gp2;
	        }
	    }else{
	        if (temp_course.length > 0){
	            temp_course = temp_course.replace(/,$/,'');
	        }
	    }

        if (temp_course.length == 0){
            alert("{$MSG['title17'][$sysSession->lang]}");
            return false;
        }

        var obj = document.getElementById("export_course");
        obj.course_id.value = temp_course;
        obj.submit();
	}


// //////////////////////////////////////////////////////////////////////////
	window.onload = function () {

		xmlHttp = XmlHttp.create();
		xmlVars = XmlDocument.create();
	}
EOF;

	showXHTML_head_B($MSG['title23'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_script('include', '/lib/xmlextras.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E('');

	showXHTML_body_B('');
		$ary[] = array($MSG['title23'][$sysSession->lang], 'tabs');
		showXHTML_tabFrame_B($ary, 1, 'fmact', 'tbGpCs', 'style="display: inline;" onsubmit="queryCourse(); return false;"');
    		showXHTML_table_B('border="0" cellspacing="1" cellpadding="3" class="cssTable"');

                showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td_B('align="center" id="toolbar1"');
						showXHTML_input('button', '', $MSG['title20'][$sysSession->lang]   , '', ' id="open_group" class="cssBtn" onclick="csAllGpStatus(true);"');
						showXHTML_input('button', '', $MSG['title18'][$sysSession->lang]   , '', ' id = "select_all" class="cssBtn" onclick="select_func2(\'tbGpCs\', true);"');
						showXHTML_input('button', '', $MSG['title16'][$sysSession->lang]           , '', 'class="cssBtn" onclick="export_data();"');
						showXHTML_input('button', '', $MSG['title24'][$sysSession->lang]           , '', 'class="cssBtn" onclick="window.location.href=\'stud_export.php\'"');
					showXHTML_td_E('');
				showXHTML_tr_E('');

    		    showXHTML_tr_B('class="cssTrOdd"');
    			    showXHTML_td_B('');
    						$ary = array('school', 'group', 'course');
    						echo csGroupLayout(10000000, NULL, $ary, TRUE, TRUE, TRUE, TRUE, NULL, FALSE, 'cssTrOdd');
    				showXHTML_td_E('');
    			showXHTML_tr_E('');

                showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td_B('align="center" id="toolbar2"');
						showXHTML_input('button', '', $MSG['title20'][$sysSession->lang]   , '', ' id="open_group2" class="cssBtn" onclick="csAllGpStatus(true);"');
						showXHTML_input('button', '', $MSG['title18'][$sysSession->lang]   , '', ' id = "select_all2" class="cssBtn" onclick="select_func2(\'tbGpCs\', true);"');
						showXHTML_input('button', '', $MSG['title16'][$sysSession->lang]           , '', 'class="cssBtn" onclick="export_data();"');
						showXHTML_input('button', '', $MSG['title24'][$sysSession->lang]           , '', 'class="cssBtn" onclick="window.location.href=\'stud_export.php\'"');
					showXHTML_td_E('');
				showXHTML_tr_E('');

            showXHTML_table_E('');
		showXHTML_tabFrame_E();

    //  匯出
    showXHTML_form_B('action="stud_field.php" method="post" enctype="multipart/form-data" style="display:none"', 'export_course');
	    $ticket = md5($sysSession->school_id . $sysSession->school_name . $sysSession->ticket . 'stud_export' . $sysSession->username);
	    showXHTML_input('hidden', 'course_id', '', '', '');
	    showXHTML_input('hidden', 'ticket', $ticket, '', '');
    showXHTML_form_E();

	showXHTML_body_E('');
?>

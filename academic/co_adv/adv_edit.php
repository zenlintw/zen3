<?php
	/**
     * 輪播廣告管理 (新增, 修改)
     *
     *
     * @since   2012/02/08
     * @author  Kuko Wang
     * @version $Id: adv_edit.php $
     * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
     **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/co_adv_manage.php');
	require_once(sysDocumentRoot . '/lang/co_lang_manage.php');
	include_once(sysDocumentRoot . '/lib/jscalendar/calendar.php');


	//接收資料處理動作
	//引入表單後端動作程式
	require_once('adv_handler.php');
	switch ($_POST['act']) {
	    //新增
	    case 'add':
	        $formData= initFormData($sysSession->username);
	        $errorCode= checkFormData($formData, $_FILES['upload']);
	        if ($errorCode=='') {
	            $errorCode=newData($formData, $_FILES['upload']);
    	        if ($errorCode=='') {
    	            header('location: adv_manage.php');
    	            exit;
    	        }
	        }
	        break;
        //修改
	    case 'update':
	        $formData= initFormData($sysSession->username);

	        $errorCode= checkFormData($formData, $_FILES['upload'], true);
	        if ($errorCode=='') {
                $errorCode=updateData($_POST['adv_id'], $formData, $_FILES['upload']);
    	        if ($errorCode=='') {
    	            header('location: adv_manage.php');
    	            exit;
    	        }
	        }
	        break;
	}

	//若操作動作為修改時，執行資料讀取
	if( $_GET['edit']=='1') {
	    $advId=intval(trim($_GET['adv_id']));
	    if ($advId>0) {
	        $data=dbGetStSr('CO_adv', '*', 'adv_id='.$advId, ADODB_FETCH_ASSOC);
	        if (!is_array($data) || count($data)==0){
	            $errorCode='id_not_found';
	        }
	    } else {
	        $errorCode='error_id';
	    }
	}

	$js = <<< EOB
	var MSG_CLOSE_DATE_ERROR ="{$MSG['close_date_error'][$sysSession->lang]}";
	var MSG_CLOSE_DATE_ALERT ="{$MSG['close_date_alert'][$sysSession->lang]}";
	var MSG_OPEN_DATE_ALERT ="{$MSG['open_date_alert'][$sysSession->lang]}";

	/**
	 *將資料放入表單
	 */
	function setValue(adv_id, open_date, close_date, img_path, open_date_flag, close_date_flag, action){
	    var obj =document.getElementById('newForm');
    	if ((obj != null) && (obj != 'undefined')){
    	    if (parseInt(adv_id)>0) {
    	        if(document.getElementById('uploaded')) {
    	            document.getElementById('uploaded').style.display = '';
        	        document.getElementById('upload_file').innerHTML = img_path;
        	        obj.img_path.value =  img_path;
        	    }
        	    obj.adv_id.value = adv_id;
        	}

    	    if (open_date_flag==1) {
    	        obj.ck_open_date[1].checked=true;
    	        document.getElementById('span_open_date').style.display='';
    	        obj.open_date.value=open_date;
    	    } else {
    	        obj.ck_open_date[0].checked=true;
    	        document.getElementById('span_open_date').style.display='none';
    	    }

    	    if (close_date_flag==1) {
    	        obj.ck_close_date[1].checked=true;
    	        document.getElementById('span_close_date').style.display='';
    	        obj.close_date.value=close_date;
    	    } else {
    	        obj.ck_close_date[0].checked=true;
    	        document.getElementById('span_close_date').style.display='none';
    	    }

    	    if(action) {
    	        obj.act.value = action;
    	    } else {
    	        obj.act.value = 'update';
    	    }
    	}
	}

    /**
     *表單送出檢查
     */
    function submitCheck(){
        var result=true;
        var obj =document.getElementById('newForm');
        var ERRORMSG='';

        if (obj.upload.value=='' && obj.act.value=='add') {
            ERRORMSG+="{$MSG['upload_empty_error'][$sysSession->lang]}"+"\\r\\n";
            result= false;
        }

        if (trim(obj.name.value)=='') {
            ERRORMSG+="{$MSG['empty_adv_name'][$sysSession->lang]}"+"\\r\\n";
            result= false;
        } else if (getTxtLength(obj.name.value)>100) {
           ERRORMSG="{$MSG['string_over_100'][$sysSession->lang]}\\r\\n";
           result=false;
        }

        if (obj.ck_open_date[0].checked && obj.ck_close_date[1].checked) {
            var myDate= new Date();
            var today = myDate.getFullYear()+'-'+ (myDate.getMonth() + 1) +'-'+myDate.getDate();;
            if (!checkDateSetting(today , obj.close_date.value)) {
              ERRORMSG+=MSG_CLOSE_DATE_ERROR + "\\r\\n";
              result= false;
           }
        } else if (obj.ck_open_date[1].checked && obj.ck_close_date[1].checked){
           if (!checkDateSetting(obj.open_date.value, obj.close_date.value)) {
              ERRORMSG+=MSG_CLOSE_DATE_ERROR+"\\r\\n";
              result= false;
           }
        }

        if (getTxtLength(obj.url.value)>1000) {
           ERRORMSG="{$MSG['string_over_1000'][$sysSession->lang]}\\r\\n";
           result=false;
        }

        if (ERRORMSG!='') {
            alert(ERRORMSG);
        }
        return result;
    }

    /**
     *初始
     */
	window.onload = function()
	{
		Calendar_setup("open_date", "%Y-%m-%d", "open_date", false);
	    Calendar_setup("close_date", "%Y-%m-%d", "close_date", false);
	}

EOB;


	// 開始頁面展現
	showXHTML_head_B('');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('include', '/lib/dragLayer.js');
	showXHTML_script('include', '/lib/common.js');
	showXHTML_script('include', '/lib/co_common.js');
	showXHTML_script('inline', $js);

    $calendar = new DHTML_Calendar('/lib/jscalendar/', $sysSession->lang, 'calendar-system');
    $calendar->load_files();

	showXHTML_head_E();
	showXHTML_body_B();
	echo '<div align="center">';
		$ary = array(array($MSG['manage_title'][$sysSession->lang], '', ''));
		showXHTML_tabFrame_B($ary, 1, 'newForm', 'newTable', 'action="adv_edit.php" method="POST" enctype="multipart/form-data"  style="display:inline"');
		  showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" class="cssTable"');
            showXHTML_tr_B('class="cssTrEvn"');
		      showXHTML_td('align="left" colspan=3', '<span style="color:red">*</span>'.$MSG['required'][$sysSession->lang]);
		    showXHTML_tr_E();
		    showXHTML_tr_B('class="cssTrOdd"');
		      showXHTML_td('align="right"', $MSG['upload_label'][$sysSession->lang].'<span style="color:red">*</span>');
		      showXHTML_td_B();
		        showXHTML_input('file', 'upload', '', '', 'size="30" class="cssInput"');
		      showXHTML_td_E();
              if ($sysSession->school_id == 10001){
                showXHTML_td('', $MSG['upload_desc'][$sysSession->lang]."<div style='color:red'>".$MSG['img_desc'][$sysSession->lang]."</div>");
              }else{
                showXHTML_td('', $MSG['upload_desc'][$sysSession->lang]."<div style='color:red'>".$MSG['img_desc4school'][$sysSession->lang]."</div>");
              }
		    showXHTML_tr_E();
		    if ($_GET['edit']==1 || $_POST['act']==='update') {
        	    showXHTML_tr_B('id="uploaded" style="display:none" class="cssTrEvn"');
        	      showXHTML_td('align="right"', $MSG['uploaded_label'][$sysSession->lang]);
        	      showXHTML_td('', '<div id="upload_file">');
        	      showXHTML_td('', '');
        	    showXHTML_tr_E();
		    }
		    showXHTML_tr_B('class="cssTrEvn"');
		      showXHTML_td('align="right"', $MSG['adv_name_label'][$sysSession->lang].'<span style="color:red">*</span>');
		      showXHTML_td_B();
		        showXHTML_input('text', 'name', $_POST['name']?$_POST['name']:($data['name']?$data['name']:''), '', 'maxlength="100" size="30" class="box02"');
		      showXHTML_td_E();
		      showXHTML_td('', $MSG['string_100_desc'][$sysSession->lang]);
		    showXHTML_tr_E();

		    showXHTML_tr_B('class="cssTrOdd"');
				showXHTML_td('align="right"', $MSG['open_date'][$sysSession->lang]);
				showXHTML_td_B('');
				    showXHTML_input(
					    'radio', 'ck_open_date',
                        array('0' => $MSG['no_limited_date'][$sysSession->lang], '1' => $MSG['btn_enable'][$sysSession->lang]),
                        isset($data['open_date_flag']) ? $data['open_date_flag'] : 0,
                        'id="ck_open_date" onclick="showDateInput(\'span_open_date\',  this.value)"'
                    );
    		        echo	'<span id="span_open_date"  style="display: none;">' . $MSG['msg_enable_date'][$sysSession->lang];
					showXHTML_input('text', 'open_date', date('Y-m-d'), '', 'id="open_date" readonly="readonly" class="cssInput"');
					echo '</span>';
    			showXHTML_td_E();
    			showXHTML_td('', $MSG['date_desc'][$sysSession->lang]);
    		showXHTML_tr_E();

    		showXHTML_tr_B('class="cssTrEvn"');
				showXHTML_td('align="right"', $MSG['close_date'][$sysSession->lang]);
				showXHTML_td_B('');
				    showXHTML_input(
					    'radio', 'ck_close_date',
                        array('0' => $MSG['no_limited_date'][$sysSession->lang], '1' => $MSG['btn_enable'][$sysSession->lang]),
                        isset($data['close_date_flag']) ? $data['close_date_flag'] : 0,
                        'id="ck_close_date" onclick="showDateInput(\'span_close_date\',  this.value)"'
                    );
    		        echo	'<span id="span_close_date"  style="display: none;">' . $MSG['msg_enable_date'][$sysSession->lang];
					showXHTML_input('text', 'close_date', date('Y-m-d'), '', 'id="close_date" readonly="readonly" class="cssInput"');
					echo '</span>';
    			showXHTML_td_E();
    			showXHTML_td('', $MSG['date_desc'][$sysSession->lang]);
    		showXHTML_tr_E();

			showXHTML_tr_B('class="cssTrOdd"');
		      showXHTML_td('align="right"', $MSG['url_label'][$sysSession->lang]);
		      showXHTML_td_B();
		        showXHTML_input('text', 'url',  $_POST['url']?$_POST['url']:($data['url']?$data['url']:''), '', 'maxlength="1000" size="40" class="box02"');
		      showXHTML_td_E();
		      showXHTML_td('', $MSG['url_desc'][$sysSession->lang]);
		    showXHTML_tr_E();

		    showXHTML_tr_B('class="cssTrEvn"');
		      showXHTML_td_B('colspan="3" align="center"');
		        showXHTML_input('button', '', ($_GET['edit']==1)?$MSG['submit_update'][$sysSession->lang]:$MSG['submit'][$sysSession->lang], '', 'class="cssBtn" onclick="if (submitCheck()){ submit();}"');
		        showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="location=\'adv_manage.php\'"');
		      showXHTML_td_E();
		    showXHTML_tr_E();
		    showXHTML_input('hidden', 'adv_id', '', '', '');
		    if(is_array($data) && count($data)>0) {
		        showXHTML_input('hidden', 'act', 'update', '', '');
		        showXHTML_input('hidden', 'img_path', '', '', '');
        	    showXHTML_script('inline', "setValue('".$data['adv_id']."',  '".$data['open_date']."', '".$data['close_date']."', '".$data['img_path']."', '".$data['open_date_flag']."', '".$data['close_date_flag']."')");
        	} elseif($_POST['act']==='update' || $_POST['act']==='add') {
        	    showXHTML_input('hidden', 'act', $_POST['act'], '', '');
        	    showXHTML_script('inline', "setValue('".$_POST['adv_id']."',  '".$_POST['open_date']."', '".$_POST['close_date']."',  '".$_POST['img_path']."', '".$_POST['ck_open_date']."', '".$_POST['ck_close_date']."', '".$_POST['act']."')");
        	} else{
        	    showXHTML_input('hidden', 'act', 'add', '', '');
        	    //新增時
        	    // showXHTML_script('inline', "initDate();");
        	}
		  showXHTML_table_E();
        showXHTML_tabFrame_E();

        if ($errorCode) {
            $errAry=array();
            $errAry=explode(',', $errorCode);
            $msgAry=array();
            for ($i=0, $size=count($errAry); $i<$size; $i++) {
                $msgAry[]=$MSG[$errAry[$i]][$sysSession->lang];
            }

            $ary = array(array($MSG['error_msg_title'][$sysSession->lang],'', ''));
    		showXHTML_tabFrame_B($ary, 1, 'errorForm', 'errorTable', 'style="display: inline;"', true);
    		  showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" class="cssTable" width="200" height="120" ');
    		    showXHTML_tr_B('class="cssTrOdd"');
    		       showXHTML_td('', implode('<br>', $msgAry));
    		    showXHTML_tr_E();

    		    showXHTML_tr_B('class="cssTrEvn"');
		           showXHTML_td_B('align="center"');
		               showXHTML_input('button', '', $MSG['submit'][$sysSession->lang], '', 'class="cssBtn" onclick="hiddenDialog(\'errorTable\');"');
		           showXHTML_td_E();
		        showXHTML_tr_E();
		      showXHTML_table_E();
            showXHTML_tabFrame_E();
            showXHTML_script('inline', "displayErrorDialog('errorTable')");
        }

    echo '</div>';
	showXHTML_body_E();
?>

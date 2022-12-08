<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *      Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                               *
	 *                                                                                                *
	 *      Programmer: Wiseguy Liang                                                                 *
	 *      Creation  : 2002/12/10                                                                    *
	 *      work for  : content directory building (imsmanifest.xml editor)                           *
	 *      work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                        *
	 *                                                                                                *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/multi_lang.php');
	require_once(sysDocumentRoot . '/lang/teach_course.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/lib_encrypt.php');
        
        /* 拖曳上傳使用 */
	require_once(sysDocumentRoot . '/teach/files/drag_drop.php');
	
	$isEnableSSS = sysEnable3S ? 'true' : 'false'; // 是否啟用 SSS

	$sysSession->cur_func = '700500400';
	$sysSession->restore();
	if (!aclVerifyPermission(700500400, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
	}

	$save_cid = sysNewEncode($sysSession->course_id);

	// 開始 output HTML
	showXHTML_head_B($MSG['learn_path'][$sysSession->lang]);
	  showXHTML_CSS('include', "/theme/{$sysSession->theme}/teach/wm.css");
      showXHTML_CSS('include', "/lib/jquery/notifIt/css/notifIt.min.css");
          // 辦公室-學習路徑管理
	  showXHTML_CSS('inline', "
#displayPanel li{
    width: 100%; 
}
.learn-path-stressg{
    font-weight: bold;
    background-color: #07fe99;
}
.move-div,.move-title{
	display:none;
	
}
.move-div{
	background-color: #07aeb0;
	color:#ffffff;
}
.learn-path-stress {
    font-weight: bold;
    background-color: #ffff99;
    font-size: 1.1em;
}
<!--
.item     { display:inline; width: 40px; vertical-align: middle; font-weight:bold; text-align: right; }
ul		  {list-style-type: none; margin-left: 14px; padding-left: 0}
li		  {cursor: default}
-->
");
        showXHTML_script('include', '/lib/dragLayer.js');
        showXHTML_script('include', '/lib/xmlextras.js');
        showXHTML_script('include', '/lib/sprintf.js');
        showXHTML_script('include', '/lib/json2.js');
      showXHTML_script('include', './sss.js');
      $xajax_save_temp->printJavascript('/lib/xajax/');
    $lcmsEnable = sysLcmsEnable ? 'true' : 'false';

	$DefaultLang=sysDefaultLang;
  	$scr = <<< EOB
var MSG_DELETE  = "{$MSG['msg_delete'][$sysSession->lang]}";
var MSG_COPY    = "{$MSG['msg_copy'][$sysSession->lang]}";
var MSG_CUT     = "{$MSG['msg_cut'][$sysSession->lang]}";
var MSG_SAVE    = "{$MSG['msg_save'][$sysSession->lang]}";
var MSG_EXIT    = "{$MSG['msg_exit'][$sysSession->lang]}";
var MSG_CONFIRM = "{$MSG['msg_confirm'][$sysSession->lang]}";
var MSG_NODE    = "{$MSG['msg_node'][$sysSession->lang]}";
var MSG_COPY1   = "{$MSG['msg_copy1'][$sysSession->lang]}";
var MSG_CUT1    = "{$MSG['msg_cut1'][$sysSession->lang]}";
var MSG_NEED2   = "{$MSG['Least two selected elements'][$sysSession->lang]}";
var MSG_EMPTY   = "{$MSG['buffer empty'][$sysSession->lang]}";
var MSG_EDGE    = "{$MSG['cannot be indented'][$sysSession->lang]}";
var MSG_ENDS    = "{$MSG['cannot be moved forward'][$sysSession->lang]}";
var MSG_ENDS2   = "{$MSG['cannot be moved backward'][$sysSession->lang]}";
var MSG_OVER    = "{$MSG['Out of range'][$sysSession->lang]}";
var MSG_REQUEST = "{$MSG['You must choice a item'][$sysSession->lang]}";
var MSG_REQ_T   = "{$MSG['title_requested'][$sysSession->lang]}";
var MSG_CONFIRM_IMPORT   = "{$MSG['msg_confirm_import'][$sysSession->lang]}";
var MSG_CONFIRM_APPEND   = "{$MSG['msg_confirm_append'][$sysSession->lang]}";
var MOD_COURSE_ID = "{$save_cid}";
var MSG_SAME_CID = "{$MSG['msg_chg_course_save'][$sysSession->lang]}";
var MSG_NO_IMPORT_FILE = "{$MSG['msg_no_import_file'][$sysSession->lang]}";
var MSG_NO_IMPORT_MODE = "{$MSG['msg_no_import_mode'][$sysSession->lang]}";
var MSG_BTN_SELECT      = "{$MSG['btn_select_lcms'][$sysSession->lang]}";
var MSG_BTN_RESELECT    = "{$MSG['btn_reselect_lcms'][$sysSession->lang]}";
var MSG_SELECTED_COURSE = "{$MSG['msg_selected_course'][$sysSession->lang]}";
var MSG_LCMS_RESOURCE =  "{$MSG['msg_lcms_resource'][$sysSession->lang]}";
var MSG_BUILD_CHILD_NODE =  "{$MSG['build_child_node'][$sysSession->lang]}";
var lcmsEnable = {$lcmsEnable};
var MSG_START_TRAFER = "{$MSG['start_trafer'][$sysSession->lang]}";
var MSG_IMPORT_PATH = "{$MSG['msg_import_path'][$sysSession->lang]}";
var MSG_APPEND_PATH = "{$MSG['msg_append_path'][$sysSession->lang]}";

var MSG_DROG_TIP = "{$MSG['msg_drog_tip'][$sysSession->lang]}";

var selectInstance                  = new Object();
	selectInstance['homework']      = new Object();
	selectInstance['exam']          = new Object();
	selectInstance['questionnaire'] = new Object();
	selectInstance['subject']       = new Object();
	selectInstance['forum']         = new Object();
	selectInstance['discuss']       = new Object();
	selectInstance['extend']        = new Object();

var lang =  '{$sysSession->lang}'; // 'big5';
var sysDefaultLang = '{$DefaultLang}';
var course_id = {$sysSession->course_id};
var cur_function = {$sysSession->cur_func};
var cur_theme = '{$sysSession->theme}';
var isEnableSSS = {$isEnableSSS};
var isMZ = (navigator.userAgent.toLowerCase().indexOf('firefox') > -1);	// 瀏覽器是否為 Mozilla

function confirmImport() {
    var frm = document.getElementById("importForm");
    var replace = frm.importMode[0].checked;
    var concatenate = frm.importMode[1].checked;

    // 未選取匯入檔案
    if (frm.importXmlFile.value == '') {
        alert(MSG_NO_IMPORT_FILE);
        return false;
    }

    // 未選取處理模式
    if ((replace + concatenate) < 1) {
        alert(MSG_NO_IMPORT_MODE);
        return false;
    }

    // 匯入前的提醒資訊
    if (($("input[name='importMode']:checked").val() === 'replace' && confirm(MSG_CONFIRM_IMPORT)) || $("input[name='importMode']:checked").val() === 'concatenate' && confirm(MSG_CONFIRM_APPEND))
        frm.submit();
    else
        return false;

}

    $(function(){
        // 變更右側提示文字
        $("input[name='importMode']").click(function(){
            switch($(this).val()) {
                case 'replace':
                    $('#import-notification').html(MSG_IMPORT_PATH);
                    break;

                case 'concatenate':
                    $('#import-notification').html(MSG_APPEND_PATH);
                    break;
            }
        });
    });
EOB;

	 $node_kinds = array('homework'      => array('WM_qti_homework_test',     				'exam_id as id, title',             "course_id={$sysSession->course_id}"),
						'exam'          => array('WM_qti_exam_test',         				'exam_id as id, title',             "course_id={$sysSession->course_id}"),
						'questionnaire' => array('WM_qti_questionnaire_test',				'exam_id as id, title',             "course_id={$sysSession->course_id}"),
						'subject'       => array('WM_term_subject as S,WM_bbs_boards as B',	'S.node_id as id,B.bname as title', "S.course_id={$sysSession->course_id} and S.board_id=B.board_id"),
						'forum'         => array('WM_bbs_boards',  							'board_id as id, bname as title',   "owner_id={$sysSession->course_id} or (length(owner_id)=16 and owner_id like '{$sysSession->course_id}%')"),
						'discuss'       => array('WM_chat_setting',							'rid as id, title',				    "owner={$sysSession->course_id} or owner like '{$sysSession->course_id}\\_%'"),
					   );
	// 捵生系統提供的六種 Instance
	foreach($node_kinds as $k => $v){
		$elements[$k] = array($MSG['please_select'][$sysSession->lang]);
		$RS = dbGetStMr($v[0], $v[1], $v[2], ADODB_FETCH_ASSOC);
		if ($RS)
			while(!$RS->EOF){
				if (preg_match('/^a:[0-9]+:{/', $RS->fields['title']))  // 如果有分語系
					$titles = unserialize($RS->fields['title']);
				else
					$titles[$sysSession->lang] = $RS->fields['title'];      // 沒分語系

				// $title = stripslashes($titles[$sysSession->lang]);
				$title = stripslashes($titles['Big5'])   . '\\t' .
					     stripslashes($titles['GB2312']) . '\\t' .
						 stripslashes($titles['en'])     . '\\t' .
						 stripslashes($titles['EUC-JP']) . '\\t' .
						 stripslashes($titles['user_define']);
				if ($titles[$sysSession->lang] == '') $titles[$sysSession->lang] = '--= unnamed =--';
				$elements[$k][$RS->fields['id']] = '&nbsp;&nbsp;|_ ' . htmlspecialchars($titles[$sysSession->lang]);
				$title = str_replace("'", "\\'", $title);
				$scr .= ($k=='discuss') ? "\tselectInstance['$k']['{$RS->fields['id']}']\t= '$title';\n" :
										  "\tselectInstance['$k'][{$RS->fields['id']}]\t= '$title';\n";
				$RS->MoveNext();
			}
	}
	unset($node_kinds);
      showXHTML_script('include', '/lib/jquery/jquery.min.js');

	  showXHTML_script('inline', $scr);

	  showXHTML_script('include', '/teach/course/cour_path.js?'.time());
          
          /* 拖曳上傳使用 */
	  showXHTML_script('include', '/lib/jQuery-File-Upload/js/vendor/jquery.ui.widget.js');
	  showXHTML_script('include', '/lib/jQuery-File-Upload/js/jquery.iframe-transport.js');
	  showXHTML_script('include', '/lib/jQuery-File-Upload/js/jquery.fileupload.js');
	  showXHTML_script('include', '/theme/default/bootstrap/js/bootstrap.min.js');
	  showXHTML_script('include', '/theme/default/fancybox/jquery.fancybox.pack.js');
	  showXHTML_script('include', '/teach/files/drag_drop.js?' . time());
      showXHTML_script('include', '/lib/jquery/notifIt/js/notifIt.min.js');
          

	  unset($scr);

	showXHTML_head_E();
	
	showXHTML_body_B('ondragover="dragover(event);" ondragleave="dragleave(event);" ondrop="drop(event,3,5);"');
	  $ary = array(array($MSG['learn_path'][$sysSession->lang], 'tabsSet',  ''));
	  showXHTML_tabFrame_B($ary, 1, 'mainForm');
		showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" class="box01"');
		  showXHTML_tr_B('class="bg03 font01"');
			showXHTML_td_B('valign="top" id="displayPanel" nowrap'); echo '<h2>Parsing XML ...</h2>';
			showXHTML_td_E();
		  showXHTML_tr_E();
		showXHTML_table_E();
	  showXHTML_tabFrame_E();

//	公用Sequencing設定
	  $ary = array(array($MSG['global_sequencing'][$sysSession->lang], 'tabsSet',  ''));
	  showXHTML_tabFrame_B($ary, 1, 'globSeqForm', 'globSeqForm', 'style="display: inline"', true);
	  	showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" class="box01" width="300" id="globSeqTable"');
	  	  showXHTML_tr_B('class="bg03 font01"');
			showXHTML_td_B('valign="top" id="globSeqPanel" nowrap');
			showXHTML_td_E();
		  showXHTML_tr_E();
		  showXHTML_tr_B('class="bg04 font01"');
			showXHTML_td_B('align="right"');
				showXHTML_input('button', '', $MSG['complete'][$sysSession->lang], '', 'class="button01" onclick="GlobSeqSetupDone(\'complete\');"');
		  		showXHTML_input('button', '', $MSG['new_global_sequencing'][$sysSession->lang], '', 'class="button01" onclick="GlobSeqSetupDone(\'new\');"');
		  		showXHTML_input('button', '', $MSG['del_global_sequencing'][$sysSession->lang],   '', 'class="button01" onclick="GlobSeqSetupDone(\'del\');"');
			showXHTML_td_E();
		  showXHTML_tr_E();
	  	showXHTML_table_E();
	  showXHTML_tabFrame_E();

// 匯出用表單
	showXHTML_form_B('id="exportForm" method="GET" action="cour_path_export.php" target="empty"');
	showXHTML_form_E();

// 節點設定
	  //echo '<div id="nodeSetupPanel" style="position: absolute; display: none">', "\n";
	$ary = array(array($MSG['node_property'][$sysSession->lang], 'tabsSet',  ''));
	showXHTML_tabFrame_B($ary, 1, 'nodeSetupForm', 'nodeSetupPanel', 'style="display: inline"', true);
	showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" style="z-index:84;" class="box01 bg04" width="680" id="nodeSetupTable" ');
		// 節點類型
		showXHTML_tr_B('class="bg03 font01"');
			showXHTML_td('width="80"', $MSG['node_type'][$sysSession->lang]);
			showXHTML_td_B('width="470" colspan="2"');
				showXHTML_input('radio','node_type',array(1 => $MSG['type1'][$sysSession->lang].$MSG['node_youtube'][$sysSession->lang],
                                    8 => $MSG['type8'][$sysSession->lang],
                                    2 => $MSG['type2'][$sysSession->lang],
                                    3 => $MSG['type3'][$sysSession->lang],
                                    4 => $MSG['type4'][$sysSession->lang],
                                    5 => $MSG['type5'][$sysSession->lang],
                                    6 => $MSG['type6'][$sysSession->lang],
                                    7 => $MSG['type7'][$sysSession->lang]), '', 'onclick="changeNodeType(this.value);"', '<br>');
			showXHTML_td_E();
		showXHTML_tr_E();
		// 節點狀態
		showXHTML_tr_B('class="bg04 font01"');
			showXHTML_td('width="80"', $MSG['node_state'][$sysSession->lang]);
			showXHTML_td_B('width="470" colspan="2"');
				showXHTML_input('checkbox', 'node_hidden', 'true', '', 'onclick="this.nextSibling.nextSibling.disabled=this.checked;"'); echo $MSG['state_hidden'][$sysSession->lang], '&nbsp;&nbsp;&nbsp;';
				showXHTML_input('checkbox', 'node_enable', 'true', '', 'onclick=""'); echo $MSG['state_enable'][$sysSession->lang];
			showXHTML_td_E();
		showXHTML_tr_E();

		$arr_names = array('Big5'		=>	'title[Big5]',
						   'GB2312'		=>	'title[GB2312]',
						   'en'			=>	'title[en]',
						   'EUC-JP'		=>	'title[EUC-JP]',
						   'user_define'=>	'title[user_define]'
						   );
		showXHTML_tr_B('class="bg03 font01"');
			showXHTML_td('rowspan="2" width="80"', $MSG['node_content'][$sysSession->lang]);
			showXHTML_td('width="50"', $MSG['title_colon'][$sysSession->lang]);
			showXHTML_td_B('');
				$multi_lang = new Multi_lang(false); // 多語系輸入框
				$multi_lang->show(true, $arr_names);
			showXHTML_td_E();
		showXHTML_tr_E();

		// URL
		showXHTML_tr_B('class="bg04 font01"');
                    showXHTML_td('', 'URL :');
                    showXHTML_td_B('');
                        // #47176 修正chrome點選檔案名稱後無法回傳回原視窗，給予id屬性，以利回傳
                        showXHTML_input('text', 'url', 'about:blank', '', 'size="45" maxlength="255" class="box02" id="url"');
                        showXHTML_input('hidden', 'func');	// 目前為新增或修改
                        showXHTML_input('hidden', 'item_id');
                        showXHTML_input('hidden', 'resource_id');
                        showXHTML_input('button', '', $MSG['browse'][$sysSession->lang], '', 'class="button01" id="browsefile" onclick="browseFile();"');
                        showXHTML_input('button', '', $MSG['btn_select_lcms'][$sysSession->lang], '', 'class="button01" id="btnSelectLcms2" onclick="selectLcmsContent(1);"');
                        echo '<span id="newwin">';
                        showXHTML_input('checkbox', 'newWin', '1'); echo $MSG['new_window'][$sysSession->lang];
                        echo '</span>';
                    showXHTML_td_E();
		showXHTML_tr_E();

		// 節點內容為homework
		showXHTML_tr_B('class="bg03 font01" style="display: none"');
			showXHTML_td('width="80"', $MSG['node_content'][$sysSession->lang]);
			showXHTML_td_B('colspan="2"');
				showXHTML_input('select', 'node_homework', $elements['homework'], 0, 'style="width: 380px"');
			showXHTML_td_E();
		showXHTML_tr_E();
		// 節點內容為exam
		showXHTML_tr_B('class="bg03 font01" style="display: none"');
			showXHTML_td('width="80"', $MSG['node_content'][$sysSession->lang]);
			showXHTML_td_B('colspan="2"');
				showXHTML_input('select', 'node_exam', $elements['exam'], 0, 'style="width: 380px"');
			showXHTML_td_E();
		showXHTML_tr_E();
		// 節點內容為questionnaire
		showXHTML_tr_B('class="bg03 font01" style="display: none"');
			showXHTML_td('width="80"', $MSG['node_content'][$sysSession->lang]);
			showXHTML_td_B('colspan="2"');
				showXHTML_input('select', 'node_questionnaire', $elements['questionnaire'], 0, 'style="width: 380px"');
			showXHTML_td_E();
		showXHTML_tr_E();
		// 節點內容為議題討論
		showXHTML_tr_B('class="bg03 font01" style="display: none"');
			showXHTML_td('width="80"', $MSG['node_content'][$sysSession->lang]);
			showXHTML_td_B('colspan="2"');
				showXHTML_input('select', 'node_subject', $elements['subject'], 0, 'style="width: 380px"');
			showXHTML_td_E();
		showXHTML_tr_E();
		// 節點內容為討論板
		showXHTML_tr_B('class="bg03 font01" style="display: none"');
			showXHTML_td('width="80"', $MSG['node_content'][$sysSession->lang]);
			showXHTML_td_B('colspan="2"');
				showXHTML_input('select', 'node_forum', $elements['forum'], 0, 'style="width: 380px"');
			showXHTML_td_E();
		showXHTML_tr_E();
		// 節點內容為討論室
		showXHTML_tr_B('class="bg03 font01" style="display: none"');
			showXHTML_td('width="80"', $MSG['node_content'][$sysSession->lang]);
			showXHTML_td_B('colspan="2"');
				showXHTML_input('select', 'node_discuss', $elements['discuss'], 0, 'style="width: 380px"');
			showXHTML_td_E();
		showXHTML_tr_E();

		// 教材下載
		showXHTML_tr_B('class="bg04 font01"');
			showXHTML_td('width="80"', $MSG['content_download'][$sysSession->lang]);
			showXHTML_td_B('width="470" colspan="2"');
				showXHTML_input('checkbox', 'node_download', 'true', '', 'id="node_download" disabled'); echo $MSG['not_allow_download'][$sysSession->lang];
			showXHTML_td_E();
		showXHTML_tr_E();

		showXHTML_tr_B('class="bg04 font01"');
			showXHTML_td_B('colspan="3" align="right"');
				showXHTML_input('button', '', $MSG['complete'][$sysSession->lang], '', 'class="button01" onclick="nodeSetupDone(true);"');
				showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang],   '', 'class="button01" onclick="nodeSetupDone(false);"');
				if ($isEnableSSS == 'true') showXHTML_input('button', '', 'Sequencing Setup',	'', 'class="button01" onclick="sequencingProperty(getItemId(this), false);"');
			showXHTML_td_E();
		showXHTML_tr_E();

	showXHTML_table_E();
	showXHTML_tabFrame_E();
        echo '<div id="droparea"><div class="gif"><img src="/public/images/upload_w.gif" width="300" height="180"><br>' . $MSG['droparea'][$sysSession->lang] . '</div></div>';
	  //echo "</div>\n";

// sequencing 設定
	  //echo '<div id="sequenceSetupPanel" style="position: absolute; display: none">', "\n";
	  $ary = array(array($MSG['sequencing_setup'][$sysSession->lang], 'tabsSet',  ''));
	  showXHTML_tabFrame_B($ary, 1, 'ssSetupForm', 'ssSetupPanel', 'style="display:inline"', true);

		echo <<< EOB
<style>
<!--
a            { text-decoration: none }
li           { font-size: 1em; }
select       { font-size: 1em; }
input        { font-size: 10pt }
textarea     { font-size: 10pt }
.LiOdd1      { background-color: #FFFFFF }
.LiOdd2      { background-color: #FFFFDD }
.LiEvn1      { background-color: #EBF3DA }
.LiEvn2      { background-color: #DDDDFF }
-->

#droparea {
    background-color:#fff;
    width:800px;
    height:300px;
    border:3px #dcdddd dashed;
    font-family: "微軟正黑體";
    font-size: 24px;
    font-weight: bold;
    color: #A5B5C3;
    text-align:center;
    /*position: fixed;
    top: 50%;
    left: 50%;
    margin-top: -250px;
    margin-left: -400px;
    z-index: -1;*/
}

.gif {
    position: relative;
    top: 50%;
    margin-top: -135;
}

</style>

  <table border="0" cellpadding="3" cellspacing="1" class="box01" width="200px">
	<tr class="bg04 font01">
	  <td nowrap>
		<span id="globSeq">
			<!--<input type="radio" value="true" name="ss_id" onclick="switchIDtype(true);" checked>-->ID =
			<input type="text" name="sequencing_id" size="20" class="box02"> <br>
		</span>
		<span id="localSeq">
			<input type="checkbox" value="true" name="ss_id" id="ss_id" onclick="switchIDRef(this.checked);">IDRef =
			<select size="1" id="sequencing_idref" name="sequencing_idref" class="box02">
			</select>
		</span>
		<input type="hidden" name="item_id">
		<input type="hidden" name="isGlobal">
	  </td>
	</tr>
	<tr class="bg03 font01">
	  <td id="ssPanel" nowrap>
		<ul>

		  <li class="LiOdd1"><input type="checkbox" name="controlMode" value="true">{$MSG['msg_control'][$sysSession->lang]}
			<ul style="display: none">
			  <li class="LiOdd2"><input type="checkbox" value="true"  name="choice" checked                 	    >{$MSG['msg_choice_read'][$sysSession->lang]} </li>
			  <li class="LiEvn2"><input type="checkbox" value="true"  name="choiceExit" checked             	    >{$MSG['msg_choicexit'][$sysSession->lang]} </li>
			  <li class="LiOdd2"><input type="checkbox" value="false" name="flow"                               	>{$MSG['msg_flow'][$sysSession->lang]} </li>
			  <li class="LiEvn2"><input type="checkbox" value="false" name="forwardOnly"                        	>{$MSG['msg_forward'][$sysSession->lang]} </li>
			  <li class="LiOdd2"><input type="checkbox" value="true"  name="useCurrentAttemptObjectiveInfo" checked >{$MSG['msg_final_info'][$sysSession->lang]}</li>
			  <li class="LiEvn2"><input type="checkbox" value="true"  name="useCurrentAttemptProgressInfo"  checked >{$MSG['msg_degree_info'][$sysSession->lang]}</li>
			</ul>
		  </li>

		  <li class="LiEvn1"><input type="checkbox" name="constrainedChoiceConsiderations" value="true">{$MSG['msg_constrain_consd'][$sysSession->lang]}
			<ul style="display: none">
			  <li class="LiOdd2"><input type="checkbox" value="false"  name="constrainChoice"   >{$MSG['msg_constrain_choice'][$sysSession->lang]} &nbsp;&nbsp;
			  <li class="LiEvn2"><input type="checkbox" value="false"  name="preventActivation" >{$MSG['msg_prevt_activation'][$sysSession->lang]} </li>
			</ul>
		  </li>

		  <li class="LiOdd1"><input type="checkbox" name="sequencingRules" value="true">{$MSG['msg_rule'][$sysSession->lang]} (sequencingRules)
			<ul style="display: none">
			  <li class="LiOdd2"><input type="checkbox" name="sequencingRule" value="true">
				<select size="1" name="sequencingRulesType" class="box02" onchange="switchRuleResult(this.parentNode, this.selectedIndex);" disabled>
				  <option value="preConditionRule">{$MSG['msg_rule1'][$sysSession->lang]}</option>
				  <option value="exitConditionRule">{$MSG['msg_rule2'][$sysSession->lang]}</option>
				  <option value="postConditionRule">{$MSG['msg_rule3'][$sysSession->lang]}</option>
				</select>&nbsp;{$MSG['msg_title'][$sysSession->lang]}
				<input type="radio" name="conditionCombination" value="any" disabled>{$MSG['msg_title1'][$sysSession->lang]}&nbsp;
				<input type="radio" name="conditionCombination" value="all" checked disabled>{$MSG['msg_title2'][$sysSession->lang]}
				<a href="javascript:;" onclick="return addSibling(this);">{$MSG['msg_add'][$sysSession->lang]}</a>&nbsp;
			    <a href="javascript:;" onclick="return rmSibling(this);">{$MSG['msg_rm'][$sysSession->lang]}</a>
			   <ul style="display: none; margin-left: 0"><table style="display: inline"></tr><td nowrap><ul type="square">

				 <script>
				 var conditions = new Array(new Array('satisfied',          			"{$MSG['msg_satisfied'][$sysSession->lang]}"),
				 			      			new Array('objectiveStatusKnown',		    "{$MSG['msg_status_know'][$sysSession->lang]}"),
				 			      			new Array('objectiveMeasureKnown',		    "{$MSG['msg_measure_know'][$sysSession->lang]}"),
				 			      			new Array('objectiveMeasureGreaterThan',	"{$MSG['msg_measure_greater'][$sysSession->lang]}"),
				 			      			new Array('objectiveMeasureLessThan',		"{$MSG['msg_measure_less'][$sysSession->lang]}"),
				 			      			new Array('completed',						"{$MSG['msg_completed'][$sysSession->lang]}"),
				 			      			new Array('activityProgressKnown',          "{$MSG['msg_progress_know'][$sysSession->lang]}"),
				 			      			new Array('attempted',                      "{$MSG['msg_attempted'][$sysSession->lang]}"),
				 			      			new Array('attemptLimitExceeded',           "{$MSG['msg_attempt_exceed'][$sysSession->lang]}"),
				 			      			new Array('timeLimitExceeded',              "{$MSG['msg_time_exceed'][$sysSession->lang]}"),
				 			      			new Array('outsideAvailableTimeRange',      "{$MSG['msg_available_time'][$sysSession->lang]}"),
				 			      			new Array('always',                         "{$MSG['msg_always'][$sysSession->lang]}")
				 				  );
				 var actions = new Array(new Array(new Array('skip',					"{$MSG['msg_skip'][$sysSession->lang]}"),
				                                   new Array('disabled',				"{$MSG['msg_disable'][$sysSession->lang]}"),
				                                   new Array('hiddenFromChoice',		"{$MSG['msg_hidden_choice'][$sysSession->lang]}"),
				                                   new Array('stopForwardTraversal',	"{$MSG['msg_stop_traversal'][$sysSession->lang]}")
				 			            ),
				                         new Array(new Array('exit',					"{$MSG['msg_exit1'][$sysSession->lang]}")
				                        ),
				                         new Array(new Array('exitParent',				"{$MSG['msg_exit_parent'][$sysSession->lang]}"),
				                                   new Array('exitAll',					"{$MSG['msg_exit_all'][$sysSession->lang]}"),
				                                   new Array('retry',					"{$MSG['msg_retry'][$sysSession->lang]}"),
				                                   new Array('retryAll',				"{$MSG['msg_retry_all'][$sysSession->lang]}"),
				                                   new Array('continue',				"{$MSG['msg_continue'][$sysSession->lang]}"),
				                                   new Array('previous',				"{$MSG['previous'][$sysSession->lang]}")
				                        )
				 			   );
				 var clsname = '';
				 for(var i=0; i<12; i++){
				 	clsname = clsname == 'LiOdd1' ? 'LiEvn1' : 'LiOdd1';
				 	document.write('<li class="' + clsname + '"><span style="width: 220"><input type="checkbox" value="' + conditions[i][0] +
				 				   '" name="condition[]" onclick="ruleAction(this);">' +
				 				   '{$MSG[msg1][$sysSession->lang]} (<input type="checkbox" value="not" name="operator" disabled>{$MSG[msg2][$sysSession->lang]})' +
				 				   conditions[i][1] + '</span>');
				 	if (i<5) document.write('<ul style="display: none">' +
				 				   '<li class="LiOdd2"><input type="checkbox" value="true" name="referencedObjective">&nbsp;{$MSG[msg_reference][$sysSession->lang]} <input type="text" class="box02" name="referencedObjectiveValue" size="20" disabled></li>' +
				 				 ((i > 2) ? '<li class="LiEvn2"><input type="checkbox" value="true" name="measureThreshold">&nbsp;{$MSG[msg_measure][$sysSession->lang]} <input type="text" class="box02" value="0.000" name="measureThresholdValue" size="20" disabled></li>' : '') +
				 				   '</ul></li>');
				 	else document.write('</li>');
				 }

				 document.write('</ul></td><td class="font01" nowrap> &nbsp;{$MSG[msg3][$sysSession->lang]}');

				 <!-- 產生Action之選項 Start -->
				 for(var j=0; j<3; j++){
					document.write('<select class="box02" size="1"' + (j ? ' style="display: none"' : '') + '" disabled>');
					for(var k=0; k<actions[j].length; k++)
						document.write('<option value="' + actions[j][k][0] + '">' + actions[j][k][1] + '</option>');
					document.write('</select>');
				 }
				 <!-- 產生Action之選項 End -->

				document.write('</td></tr></table>');

	  			 </script>
	  			 </ul>
			  </li>
			</ul>
		  </li>

		  <li class="LiEvn1"><input type="checkbox" name="limitConditions" value="true">{$MSG['msg_limit'][$sysSession->lang]}
			<ul style="display: none">
			  <li class="LiOdd2"><input type="checkbox" name="attemptLimit" value="true">{$MSG['msg_attemptlimit'][$sysSession->lang]} <input type="text" name="attemptLimitValue" size="4" class="box02" disabled>{$MSG['time'][$sysSession->lang]}</li>
			  <li class="LiEvn2"><input type="checkbox" name="attemptAbsoluteDurationLimit" value="true">
				<span style="width: 390">{$MSG['msg_attempt_absolute'][$sysSession->lang]} </span>
				<input type="text" value="0" name="attemptAbsoluteDurationLimit_hour" size="4" class="box02" disabled>{$MSG['hour'][$sysSession->lang]}
				<select size="1" name="attemptAbsoluteDurationLimit_minute" class="box02" disabled><script>generateOptions(0,59);</script></select>{$MSG['minute'][$sysSession->lang]}
				<select size="1" name="attemptAbsoluteDurationLimit_second" class="box02" disabled><script>generateOptions(0,59);</script></select>{$MSG['second'][$sysSession->lang]}
			  </li>
			  <li class="LiOdd2"><input type="checkbox" name="attemptExperiencedDurationLimit" value="true">
				<span style="width: 390">{$MSG['msg_attempt_experienced'][$sysSession->lang]} </span>
				<input type="text" value="0" name="attemptExperiencedDurationLimit_hour" size="4" class="box02" disabled>{$MSG['hour'][$sysSession->lang]}
				<select size="1" name="attemptExperiencedDurationLimit_minute" class="box02" disabled><script>generateOptions(0,59);</script></select>{$MSG['minute'][$sysSession->lang]}
				<select size="1" name="attemptExperiencedDurationLimit_second" class="box02" disabled><script>generateOptions(0,59);</script></select>{$MSG['second'][$sysSession->lang]}
			  </li>
			  <li class="LiEvn2"><input type="checkbox" name="activityAbsoluteDurationLimit" value="true">
				<span style="width: 390">{$MSG['msg_activity_absolute'][$sysSession->lang]} </span>
				<input type="text" value="0" name="activityAbsoluteDurationLimit_hour" size="4" class="box02" disabled>{$MSG['hour'][$sysSession->lang]}
				<select size="1" name="activityAbsoluteDurationLimit_minute" class="box02" disabled><script>generateOptions(0,59);</script></select>{$MSG['minute'][$sysSession->lang]}
				<select size="1" name="activityAbsoluteDurationLimit_second" class="box02" disabled><script>generateOptions(0,59);</script></select>{$MSG['second'][$sysSession->lang]}
			  </li>
			  <li class="LiOdd2"><input type="checkbox" name="activityExperiencedDurationLimit" value="true">
				<span style="width: 390">{$MSG['msg_activity_experienced'][$sysSession->lang]} </span>
				<input type="text" value="0" name="activityExperiencedDurationLimit_hour" size="4" class="box02" disabled>{$MSG['hour'][$sysSession->lang]}
				<select size="1" name="activityExperiencedDurationLimit_minute" class="box02" disabled><script>generateOptions(0,59);</script></select>{$MSG['minute'][$sysSession->lang]}
				<select size="1" name="activityExperiencedDurationLimit_second" class="box02" disabled><script>generateOptions(0,59);</script></select>{$MSG['second'][$sysSession->lang]}
			  </li>
			  <li class="LiEvn2"><input type="checkbox" name="beginTimeLimit" value="true">
				<span style="width: 220">{$MSG['msg_begin_time'][$sysSession->lang]} </span>
				<select size="1" name="beginTimeLimit_year" class="box02"   disabled><script>generateOptions(2003,2010);</script></select>{$MSG['year'][$sysSession->lang]}~
				<select size="1" name="beginTimeLimit_month"  class="box02" disabled><script>generateOptions(1,12);</script></select>{$MSG['month'][$sysSession->lang]}
				<select size="1" name="beginTimeLimit_day" class="box02"    disabled><script>generateOptions(1,31);</script></select>{$MSG['day'][$sysSession->lang]}
				<select size="1" name="beginTimeLimit_hour" class="box02"   disabled><script>generateOptions(0,23);</script></select>{$MSG['hour'][$sysSession->lang]}
				<select size="1" name="beginTimeLimit_minute" class="box02" disabled><script>generateOptions(0,59);</script></select>{$MSG['minute'][$sysSession->lang]}
				<select size="1" name="beginTimeLimit_second" class="box02" disabled><script>generateOptions(0,59);</script></select>{$MSG['second'][$sysSession->lang]}
			  </li>
			  <li class="LiOdd2"><input type="checkbox" name="endTimeLimit" value="true">
				<span style="width: 220">{$MSG['msg_end_time'][$sysSession->lang]} </span>
				<select size="1" name="endTimeLimit_year"  class="box02"  disabled><script>generateOptions(2003,2010);</script></select>{$MSG['year'][$sysSession->lang]}~
				<select size="1" name="endTimeLimit_month"  class="box02" disabled><script>generateOptions(1,12);</script></select>{$MSG['month'][$sysSession->lang]}
				<select size="1" name="endTimeLimit_day" class="box02"    disabled><script>generateOptions(1,31);</script></select>{$MSG['day'][$sysSession->lang]}
				<select size="1" name="endTimeLimit_hour" class="box02"   disabled><script>generateOptions(0,23);</script></select>{$MSG['hour'][$sysSession->lang]}
				<select size="1" name="endTimeLimit_minute" class="box02" disabled><script>generateOptions(0,59);</script></select>{$MSG['minute'][$sysSession->lang]}
				<select size="1" name="endTimeLimit_second" class="box02" disabled><script>generateOptions(0,59);</script></select>{$MSG['second'][$sysSession->lang]}</li>
			  </li>
			</ul>
		  </li>

		  <li class="LiOdd1"><input type="checkbox" name="auxiliaryResources" value="true">{$MSG['msg_resources'][$sysSession->lang]}
			<ul style="display: none">
			  <li class="LiOdd2"><input type="checkbox" value="false" name="auxiliaryResource">{$MSG['msg_resource_id'][$sysSession->lang]}<input type="text" name="auxiliaryResourceID" size="40" class="box02" disabled>&nbsp;
				<a href="javascript:;" onclick="return addSibling(this)">{$MSG['msg_add'][$sysSession->lang]}</a>&nbsp;
				<a href="javascript:;" onclick="return rmSibling(this);">{$MSG['msg_rm'][$sysSession->lang]}</a>
				<ul style="display: none">
				  <li class="LiOdd1">{$MSG['msg_purpose'][$sysSession->lang]}<input type="text" name="purpose" size="40" class="box02"></li>
				</ul>
			  </li>
			</ul>
		  </li>

		  <li class="LiEvn1"><input type="checkbox" name="rollupRules" value="true">{$MSG['msg_rule4'][$sysSession->lang]}
			<ul style="display: none">
			  <li class="LiOdd2"><input type="checkbox" name="rollupObjectiveSatisfied" value="true" checked> {$MSG['msg_rollupObjectiveSatisfied'][$sysSession->lang]}</li>
			  <li class="LiEvn2"><input type="checkbox" name="rollupProgressCompletion" value="true" checked> {$MSG['msg_rollupProgressCompletion'][$sysSession->lang]}</li>
			  <li class="LiOdd2"><input type="checkbox" name="ObjectiveMeasureWeight" value="true"> {$MSG['msg_ObjectiveMeasureWeight'][$sysSession->lang]} <input type="text" name="ObjectiveMeasureWeightValue" size="8" value="1.0000" class="box02" disabled></li>
			  <li class="LiEvn2"><input type="checkbox" name="rollupRule" value="true"> {$MSG['msg_rule5'][$sysSession->lang]}<select size="1" name="childActivitySet" class="box02" onchange="childActivitySetChange(this);" disabled>
				  <option value="all" selected>{$MSG['msg_all'][$sysSession->lang]}</option>
				  <option value="any">{$MSG['msg_any'][$sysSession->lang]}</option>
				  <option value="none">{$MSG['msg_none'][$sysSession->lang]}</option>
				  <option value="atLeastCount">{$MSG['msg_least_count'][$sysSession->lang]}</option>
				  <option value="atLeastPercent">{$MSG['msg_least_percent'][$sysSession->lang]}</option>
				</select><span style="display: none">{$MSG['msg_count'][$sysSession->lang]}
				<input type="text" name="minimunCount" size="5" value="0" class="box02"></span><span style="display: none">{$MSG['msg_percent'][$sysSession->lang]}
				<input type="text" name="minimunPercent" size="5" value="0.0000" class="box02"></span>&nbsp;
				<a href="javascript:;" onclick="return addSibling(this)">{$MSG['msg_add'][$sysSession->lang]}</a>&nbsp;<a href="javascript:;" onclick="return rmSibling(this);">{$MSG['msg_rm'][$sysSession->lang]}</a>
				<ul style="display: none">
				  <li class="LiOdd1">{$MSG['msg_title3'][$sysSession->lang]}
				    <input type="radio" name="conditionCombination2_1" value="all">{$MSG['msg_all1'][$sysSession->lang]}
					<input type="radio" name="conditionCombination2_1" value="any" checked>{$MSG['msg_title4'][$sysSession->lang]}
				  <li class="LiEvn1"><table style="display: inline">
					<tr><td style="font-size: 9pt" nowrap>{$MSG['msg1'][$sysSession->lang]}&nbsp</td>
					<td style="font-size: 9pt" nowrap>
					  <input type="checkbox" name="rollupCondition[]" value="satisfied">                (<input type="checkbox" name="operator" value="not">{$MSG['msg2'][$sysSession->lang]})&nbsp;{$MSG['msg_satisfied'][$sysSession->lang]}<br>
					  <input type="checkbox" name="rollupCondition[]" value="objectiveStatusKnown">     (<input type="checkbox" name="operator" value="not">{$MSG['msg2'][$sysSession->lang]})&nbsp;{$MSG['msg_status_know'][$sysSession->lang]}<br>
					  <input type="checkbox" name="rollupCondition[]" value="objectiveMeasureKnown">    (<input type="checkbox" name="operator" value="not">{$MSG['msg2'][$sysSession->lang]})&nbsp;{$MSG['msg_measure_know'][$sysSession->lang]}<br>
					  <input type="checkbox" name="rollupCondition[]" value="completed">                (<input type="checkbox" name="operator" value="not">{$MSG['msg2'][$sysSession->lang]})&nbsp;{$MSG['msg_completed'][$sysSession->lang]}<br>
					  <input type="checkbox" name="rollupCondition[]" value="activityProgressKnown">    (<input type="checkbox" name="operator" value="not">{$MSG['msg2'][$sysSession->lang]})&nbsp;{$MSG['msg_progress_know'][$sysSession->lang]}<br>
					  <input type="checkbox" name="rollupCondition[]" value="attempted">                (<input type="checkbox" name="operator" value="not">{$MSG['msg2'][$sysSession->lang]})&nbsp;{$MSG['msg_attempted'][$sysSession->lang]}<br>
					  <input type="checkbox" name="rollupCondition[]" value="attemptLimitExceeded">     (<input type="checkbox" name="operator" value="not">{$MSG['msg2'][$sysSession->lang]})&nbsp;{$MSG['msg_attempt_exceed'][$sysSession->lang]}<br>
					  <input type="checkbox" name="rollupCondition[]" value="timeLimitExceeded">        (<input type="checkbox" name="operator" value="not">{$MSG['msg2'][$sysSession->lang]})&nbsp;{$MSG['msg_time_exceed'][$sysSession->lang]}<br>
					  <input type="checkbox" name="rollupCondition[]" value="outsideAvailableTimeRange">(<input type="checkbox" name="operator" value="not">{$MSG['msg2'][$sysSession->lang]})&nbsp;{$MSG['msg_available_time'][$sysSession->lang]}<br>
					</td><td style="font-size: 9pt" nowrap>
					&nbsp;{$MSG['msg_config'][$sysSession->lang]}&nbsp;<select size="1" name="rollupAction" class="box02">
					  <option value="satisfied" selected>{$MSG['msg_satisfied'][$sysSession->lang]}</option>
					  <option value="notSatisfied">{$MSG['msg_notsatisfied'][$sysSession->lang]}</option>
					  <option value="completed">{$MSG['msg_complete'][$sysSession->lang]}</option>
					  <option value="incomplete">{$MSG['msg_incomplete'][$sysSession->lang]}</option>
					</select>
					</td></tr></table>
				  </li>
				</ul>
			  </li>
			</ul>
		  </li>

		  <li class="LiOdd1"><input type="checkbox" name="rollupConsiderations" value="true">{$MSG['msg_rollup1'][$sysSession->lang]}
			<ul style="display: none">
			   <li class="LiOdd2"><input type="checkbox" name="measureSatisfactionIfActive" checked=true>{$MSG['msg_rollup2'][$sysSession->lang]}</li>
			   <script>
			   var rollupConsiderationType = new Array(new Array('always',         "{$MSG['msg_always'][$sysSession->lang]}"),
								   					   new Array('ifAttempted',    "{$MSG['msg_ifAttempted'][$sysSession->lang]}"),
								   					   new Array('ifNotSkipped',   "{$MSG['msg_notSkipped'][$sysSession->lang]}"),
								   					   new Array('ifNotSuspended', "{$MSG['msg_notSuspended'][$sysSession->lang]}")
								   					   );
			   var requiredForElement = new Array (new Array('requiredForSatisfied',    "{$MSG['msg_rollup3'][$sysSession->lang]}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"),
							   				       new Array('requiredForNotSatisfied', "{$MSG['msg_rollup4'][$sysSession->lang]}&nbsp;&nbsp;&nbsp;&nbsp;"),
							   				       new Array('requiredForCompleted',    "{$MSG['msg_rollup5'][$sysSession->lang]}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"),
							   				       new Array('requiredForIncomplete',   "{$MSG['msg_rollup6'][$sysSession->lang]}&nbsp;&nbsp;&nbsp;&nbsp;")
							   				       );

			   var clsname = '';
			   for (var i = 0; i < requiredForElement.length; i++) {
			   	clsname = clsname == 'LiOdd2' ? 'LiEvn2' : 'LiOdd2';
			   	document.write('<li class="' + clsname + '"><input type="checkbox" name="'+requiredForElement[i][0]+'">' + requiredForElement[i][1]) ;
			   	document.write('<select class="box02" size="1" name="'+requiredForElement[i][0]+'value" disabled>')
			   	for (var j = 0; j < rollupConsiderationType.length; j++) {
			   		document.write('<option value="' + rollupConsiderationType[j][0] + '">' + rollupConsiderationType[j][1] + '</option>');
				}
				document.write('</select></li>');
			   }
		 	   </script>
			</ul>
		  </li>

		  <li class="LiEvn1"><input type="checkbox" name="Objectives" value="true">{$MSG['msg_objectives'][$sysSession->lang]}
			<ul style="display: none">
			  <li class="LiOdd2"><input type="hidden">{$MSG['msg_objective_id'][$sysSession->lang]}<input type="text" name="objectiveID" size="20" class="box02">{$MSG['msg_fig'][$sysSession->lang]}<input type="checkbox" name="satisfiedByMeasure" value="true">{$MSG['msg_satisfiedByMeasure'][$sysSession->lang]}
				<ul>
				  <li class="LiOdd1"><input type="checkbox" name="minNormalizedMeasure" value="true">{$MSG['msg_minNormalizedMeasure'][$sysSession->lang]} = <input type="text" name="minNormalizedMeasureValue" size="6" value="1.0000" class="box02" disabled></li>
				  <li class="LiEvn1"><input type="checkbox" name="mapInfo" value="true">{$MSG['msg_map_id'][$sysSession->lang]}
					<input type="text" name="targetObjectiveID" size="20" class="box02" disabled>&nbsp;
					<a href="javascript:;" onclick="return addSibling(this)">{$MSG['msg_add'][$sysSession->lang]}</a>&nbsp;
					<a href="javascript:;" onclick="return rmSibling(this);">{$MSG['msg_rm'][$sysSession->lang]}</a><br>
					<input type="checkbox" name="readSatisfiedStatus" value="true" checked disabled>{$MSG['msg_readSatisfiedStatus'][$sysSession->lang]}<br>
					<input type="checkbox" name="readNormalizedMeasure" value="true" checked disabled>{$MSG['readNormalizedMeasure'][$sysSession->lang]}<br>
					<input type="checkbox" name="writeSatisfiedStatus" value="true" disabled>{$MSG['writeSatisfiedStatus'][$sysSession->lang]}<br>
					<input type="checkbox" name="writeNormalizedMeasure" value="true" disabled>{$MSG['writeNormalizedMeasure'][$sysSession->lang]}</li>
				</ul>
			  </li>
			  <li class="LiEvn2"><input type="checkbox" name="objectives" value="true">{$MSG['msg_id'][$sysSession->lang]}
				<input type="text" name="objective[]" size="20" class="box02" disabled>{$MSG['msg_fig'][$sysSession->lang]}
				<input type="checkbox" name="satisfiedByMeasure" value="true" disabled>{$MSG['msg_satisfiedByMeasure'][$sysSession->lang]}&nbsp;
				<a href="javascript:;" onclick="return addSibling(this)">{$MSG['msg_add'][$sysSession->lang]}</a>&nbsp;
				<a href="javascript:;" onclick="return rmSibling(this);">{$MSG['msg_rm'][$sysSession->lang]}</a>
				<ul style="display: none">
				  <li class="LiOdd1"><input type="checkbox" name="minNormalizedMeasure" value="true">{$MSG['msg_minNormalizedMeasure'][$sysSession->lang]} = <input type="text" name="" size="6" value="1.0000" class="box02" disabled></li>
				  <li class="LiEvn1"><input type="checkbox" name="mapInfo" value="true">{$MSG['msg_map_id'][$sysSession->lang]}
					<input type="text" name="targetObjectiveID" size="20" class="box02" disabled>&nbsp;
					<a href="javascript:;" onclick="return addSibling(this)">{$MSG['msg_add'][$sysSession->lang]}</a>&nbsp;
					<a href="javascript:;" onclick="return rmSibling(this);">{$MSG['msg_rm'][$sysSession->lang]}</a><br>
					<input type="checkbox" name="readSatisfiedStatus" value="true" checked disabled>{$MSG['msg_readSatisfiedStatus'][$sysSession->lang]}<br>
					<input type="checkbox" name="readNormalizedMeasure" value="true" checked disabled>{$MSG['readNormalizedMeasure'][$sysSession->lang]}<br>
					<input type="checkbox" name="writeSatisfiedStatus" value="true" disabled>{$MSG['writeSatisfiedStatus'][$sysSession->lang]}<br>
					<input type="checkbox" name="writeNormalizedMeasure" value="true" disabled>{$MSG['writeNormalizedMeasure'][$sysSession->lang]}</li>
				</ul>
			  </li>
			</ul>
		  </li>

		  <li class="LiOdd1"><input type="checkbox" name="randomizationControls" value="true">{$MSG['msg_randomization'][$sysSession->lang]}
			<ul style="display: none">
			  <li class="LiOdd2"><input type="checkbox" name="randomizationTiming" value="true">{$MSG['msg_randomization_time'][$sysSession->lang]}
				<select size="1" name="RandomizationTimingValue" class="box02" disabled>
				  <option value="never">{$MSG['msg_never'][$sysSession->lang]}</option>
				  <option value="once">{$MSG['msg_once'][$sysSession->lang]}</option>
				  <option value="onEachNewAttempt">{$MSG['msg_each'][$sysSession->lang]}</option>
				</select></li>
			  <li class="LiEvn2"><input type="checkbox" name="reorderChildren" value="false">{$MSG['msg_randomize_children'][$sysSession->lang]}</li>
			  <li class="LiOdd2"><input type="checkbox" name="selectCount" value="true">{$MSG['msg_select_count'][$sysSession->lang]}<input type="text" name="selectCountValue" size="4" class="box02" value=0 disabled></li>
			  &nbsp;,{$MSG['msg_selection_time'][$sysSession->lang]}<select size="1" name="selectionTimingValue" class="box02" disabled>
				  <option value="never">{$MSG['msg_never'][$sysSession->lang]}</option>
				  <option value="once">{$MSG['msg_once'][$sysSession->lang]}</option>
				  <option value="onEachNewAttempt">{$MSG['msg_each'][$sysSession->lang]}</option>
				</select>
			</ul>
		  </li>

		  <li class="LiEvn1"><input type="checkbox" name="deliveryControls" value="true">{$MSG['msg_delivery_control'][$sysSession->lang]}
			<ul style="display: none">
			  <li class="LiOdd2"><input type="checkbox" name="tracked" value="true" checked>{$MSG['msg_tracked'][$sysSession->lang]}</li>
			  <li class="LiEvn2"><input type="checkbox" name="completionSetByContent" value="true">{$MSG['msg_completion_setbycontent'][$sysSession->lang]}</li>
			  <li class="LiOdd2"><input type="checkbox" name="objectiveSetByContent" value="true">{$MSG['msg_objective_setbycontent'][$sysSession->lang]}</li>
			</ul>
		  </li>

		</ul>
	  </td>
	</tr>
	<tr class="bg04 font01">
	  <td align="center" nowrap>
		<input type="button" value="{$MSG['msg_ok'][$sysSession->lang]}" class="button01" onclick="ssSetupComplete(true); if (document.getElementById('ssSetupForm').isGlobal.value) setGlobalSequencing();">
		<input type="button" value="{$MSG['cancel'][$sysSession->lang]}" class="button01" onclick="ssSetupComplete(false);">
	  </td>
	</tr>
  </table>

EOB;
	  showXHTML_tabFrame_E();

	  showXHTML_tabFrame_B(array(array($MSG['msg_import'][$sysSession->lang])), 1, 'importForm', 'importPanel', 'method="POST" action="cour_path_import.php" enctype="multipart/form-data" style="display:inline"', true);
	    showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" class="box01" width="480"');
	      showXHTML_tr_B('class="cssTrEvn"');
	        showXHTML_td('', $MSG['msg_file'][$sysSession->lang]);
	        showXHTML_td_B();
	          showXHTML_input('file', 'importXmlFile', '', '', 'class="cssInput"');
	        showXHTML_td_E();
	        showXHTML_td('', $MSG['msg_import_file'][$sysSession->lang]);
	      showXHTML_tr_E();
	      showXHTML_tr_B('class="cssTrOdd"');
	        showXHTML_td('', $MSG['msg_path'][$sysSession->lang]);
	        showXHTML_td_B();
	          showXHTML_input('radio', 'importMode', array('replace' => $MSG['msg_replace'][$sysSession->lang],'concatenate' => $MSG['msg_connect'][$sysSession->lang]), '', '', '<br>');
	        showXHTML_td_E();
	        showXHTML_td('id="import-notification"', $MSG['msg_import_path'][$sysSession->lang]);
	      showXHTML_tr_E();
	      showXHTML_tr_B('class="cssTrEvn"');
	        showXHTML_td_B('colspan="3" align="center"');
	          showXHTML_input('button', '', $MSG['btn_import'][$sysSession->lang], '', 'onclick="confirmImport();" class="cssInput"');
	          showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang], '', 'class="cssInput" onclick="CancelImport();"');
	        showXHTML_td_E();
	      showXHTML_tr_E();
	    showXHTML_table_E();
	  showXHTML_tabFrame_E();

// LCMS 節點設定
    if (sysLcmsEnable) {
        $ary = array(array($MSG['toolbtm17'][$sysSession->lang], 'tabsSet',  ''));
        showXHTML_tabFrame_B($ary, 1, 'lcmsSetupForm', 'lcmsSetupPanel', 'style="display: inline"', true);
        showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" style="z-index:84;" class="box01 bg04" width="550" id="lcmsSetupTable" ');
            // 來源
            showXHTML_tr_B('class="bg03 font01" style="display: none;"');
                showXHTML_td('width="80"', $MSG['content_source'][$sysSession->lang]);
                showXHTML_td_B('width="250"');
//                    echo '<span id="msgSelectContent" style="color: red;"></span>';
                    showXHTML_input('button', '', $MSG['btn_select_lcms'][$sysSession->lang], '', 'id="btnSelectLcms" class="button01" onclick="selectLcmsContent();"');
                showXHTML_td_E();
                showXHTML_td('width="220"', $MSG['msg_select_import_content'][$sysSession->lang]);
            showXHTML_tr_E();
            // 路徑處理
            showXHTML_tr_B('class="bg04 font01"');
                showXHTML_td('width="80"', $MSG['msg_path'][$sysSession->lang]);
                showXHTML_td_B('width="250"');
                showXHTML_input('radio', 'condition', array(
                    1 => $MSG['process_2'][$sysSession->lang],
                    2 => $MSG['process_3'][$sysSession->lang],
                ), 2, '', "<br>\n");
                showXHTML_td_E();
                showXHTML_td('width="220"', $MSG['msg_import_path'][$sysSession->lang]);
            showXHTML_tr_E();
            // 挑選結果
            showXHTML_tr_B('class="bg03 font01"');
                showXHTML_td('width="80"', $MSG['selected_results'][$sysSession->lang]);
                showXHTML_td_B('width="250"');
                    echo '<span id="msgSelectContent" style="color: red;"></span>';
                    // 改為不顯示，由左側選單點選時就直接顯示
                    // showXHTML_input('button', '', $MSG['btn_select_lcms'][$sysSession->lang], '', 'id="btnSelectLcms" class="button01" onclick="selectLcmsContent();"');
                showXHTML_td_E();
                showXHTML_td('');
            showXHTML_tr_E();

            showXHTML_tr_B('class="bg03 font01"');
                showXHTML_td_B('colspan="3" align="center"');
                    showXHTML_input('button', '', $MSG['btn_import'][$sysSession->lang], '', 'class="button01" onclick="confirmLcmsImport();"');
                    showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang],   '', 'class="button01" onclick="cancelLcmsImport();"');
                showXHTML_td_E();
            showXHTML_tr_E();

        showXHTML_table_E();
        showXHTML_tabFrame_E();
    } else {
        echo '<div id="lcmsSetupPanel"></div>';
    }

	echo '<form style="display: none"><input type=hidden" name="saveTemporaryContent" id="saveTemporaryContent"></form>';
	showXHTML_body_E();
?>

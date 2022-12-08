<?php
/**
 * 匯入課程資訊
 *
 * PHP 4.4.7+, MySQL 4.0.21+, Apache 1.3.36+
 *
 * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
 * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
 * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
 *
 * @package     WM3
 * @author      SHIH JUNG YEH <yea@sun.net.tw>
 * @copyright   2000-2008 SunNet Tech. INC.
 * @version     CVS: $Id$
 * @link        http://demo.learn.com.tw/1000110138/index.html
 * @since       2008-06-20
 *
 * 備註：
 */
// {{{ 函式庫引用 begin
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/interface.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lang/lcms_course_copy.php');
require_once(sysDocumentRoot . '/academic/course/course_lib.php');
require_once(sysDocumentRoot . '/teach/custom/co_lcms_api.php');

//一進來先檢查是否有未刪除的暫存檔, 超過3天就刪除掉
$tempFileAlifeSec = 259200; //3天
$doorPath = sysDocumentRoot ."/base/{$sysSession->school_id}/door";
$d = dir($doorPath);
while (false !== ($entry = $d->read())) {
    if( preg_match('/^lcms_course_package_[\d]+\.zip$/',$entry) ) {
        $importCourseFile = "$doorPath/$entry";
        $f = filemtime($importCourseFile);
        $s = time() - $f;
        if($s > $tempFileAlifeSec){
            @unlink($importCourseFile);
        }
    }
}
$d->close();

$fname = sysDocumentRoot . '/base/' . trim($sysSession->school_id) . '/config.txt';
if (file_exists($fname))
{
	$fp = fopen($fname, 'r');
	// 讀出整個檔案內容
	$dec_content = fread($fp,filesize($fname));
	// 解開編碼
	$org_content = other_dec($dec_content);
	$temp_array  = explode("\r\n", $org_content);
	if (is_array($temp_array))
	{
		$temp_count = count($temp_array);
		for ($i = 0; $i < $temp_count; $i++)
		{
			$item = explode('@', trim($temp_array[$i]));

			// $item[0] 欄位名稱
			// $item[1] 欄位值
			if (strpos($item[1], '(at)') !== false) $item[1] = str_replace('(at)', '@', $item[1]);
			if ($item[0] == 'sysAvailableChars')
				$Da[$item[0]] = explode(',', $item[1]);
			else
				$Da[$item[0]]= $item[1];
		}
	}
	fclose($fp);
}

//LCMS server名稱 與 URL
$lcmsServerName = 'LCMS';
$lcmsServerUrl  = $Da['sysLcmsHost'];

// }}} 函式庫引用 end
// {{{ 常數定義 begin
$sysSession->cur_func = '';
$sysSession->restore();

if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {

}
// }}} 常數定義 end
// {{{ 變數宣告 begin
// }}} 變數宣告 end
// {{{ 函數宣告 begin

// }}} 函數宣告 end
// {{{ 主程式 begin

// 檢查是否啟用 LCMS
if (defined('sysLcmsEnable') && !sysLcmsEnable) {
    header("HTTP/1.0 404 Not Found");
    echo 'Page Not Found!';
    die();
}

showXHTML_head_B($MSG['course_copy_wizard'][$sysSession->lang]);
showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
showXHTML_CSS('inline', "
ul{list-style-type: none; margin-left: 16; padding-left: 0}
li{cursor: default}
.xx{-moz-opacity: 0.4;filter:Alpha(opacity=40);}

.span3.offset0{width:130px;white-space: nowrap;overflow: hidden;display: inline-block;}
");

showXHTML_head_E();
showXHTML_body_B();
echo "<center>\n";
$ary = array(array($MSG['course_copy_wizard'][$sysSession->lang]));
showXHTML_tabFrame_B($ary, 1, 'importForm', 'ListTable', 'method="POST" action="co_qti_pack1.php" onsubmit="if (this.course_id.value == \'\') { alert(\'' . $MSG['msg_course_id_error'][$sysSession->lang] . '\'); return false;} else this.elements[this.elements.length-1].disabled=true;" style="display: inline"'); /* * * CUSTOM ** */
showXHTML_table_B('id ="mainTable" width="680" border="0" cellspacing="1" cellpadding="3" class="cssTable"');

// 取得過濾學年學期的課程名稱 begin
$RS = getCourseData($sysSession->course_id);
$lang = getCaption($RS['caption']);
$course_name = $lang[$sysSession->lang];
preg_match('/^([0-9_-]*)([^0-9_-]*)/', $course_name, $matches);
if (count($matches) && !empty($matches[2]))
    $course_name = $matches[2];
// 取得過濾學年學期的課程名稱 end
$sqls = 'select M.course_id,C.caption' .
        ' from WM_term_major as M, WM_term_course as C ' .
        ' where M.username=\'' . $sysSession->username . '\' and ' . "role & ({$sysRoles[teacher]}|{$sysRoles['assistant']})" .
        ' and M.course_id = C.course_id and M.course_id <> ' . $sysSession->course_id . // 過濾目前課程
        ' and C.status != 9 order by M.course_id desc';
$sysConn->Execute('use ' . sysDBprefix . $sysSession->school_id);
$RS = $sysConn->Execute($sqls);
$importedCourse = $sysConn->GetAssoc("select source_course_id, target_course_id from lcms_course_wizard");
if ($RS->RecordCount() > 0) {
    while ($RS1 = $RS->FetchRow()) {
        $cnames = unserialize($RS1['caption']);
        $cour_ary[$RS1['course_id']] = $cnames[$sysSession->lang];
    }
    showXHTML_tr_B('class="font01 cssTrEvn"');
    showXHTML_td('width="80"', $MSG['co_course_name'][$sysSession->lang]);
    showXHTML_td_B('width="300"');
    // showXHTML_input('select', 'course_id', $cour_ary, $course_id, 'class="cssInput" id="course_id" ');

	//目前所在的課程 已經有匯入過LCMS了, 就不能選來源課程
	$showTargetType = 0;
	if(in_array($sysSession->course_id, $importedCourse)){
		$showTargetType = 1;
		$sourceCourseId = array_search($sysSession->course_id, $importedCourse);
		echo $cour_ary[$sourceCourseId];
		echo "<input type='hidden' value='$sourceCourseId' name='course_id' id='course_id'>";
	}else{
		echo "<select name='course_id' id='course_id' class='cssInput' style='width: 40em;'>";
			foreach($cour_ary as $id => $v){
				$rel_course_name = '';
				$is_rel = 0;
				if(is_numeric($importedCourse[$id])){
					$v .= '(*)';
					$rel_course_name = $cour_ary[$importedCourse[$id]];
				}

				if(in_array($id, $importedCourse)){
					$is_rel = 1;
				}

				echo "<option value='$id' rel_course_name='{$rel_course_name}' is_rel='$is_rel'>{$v}</option>";
			}
		echo "</select>";
        echo "<span id='course_size_msg'></span>";
		echo "<div id='warring_course_source' style='color:red;white-space: nowrap;'></div>";
	}
	showXHTML_td_E();
    showXHTML_tr_E();
	showXHTML_tr_B('class="font01 cssTrOdd"');
	showXHTML_td('', $MSG['target_course_name'][$sysSession->lang]);
	showXHTML_td('', $sysSession->course_name);
	showXHTML_tr_E();
    showXHTML_tr_B('class="font01 cssTrEvn"');
    showXHTML_td('', $MSG['th_copy_content'][$sysSession->lang]);
    showXHTML_td_B('id="treePanel" class="cssTd" background="/theme/' . $sysSession->theme . '/academic/c-bg.gif"');
    echo <<< EOB
  <ul>
    <li><img src="/theme/{$sysSession->theme}/teach/icon-c.gif" border="0" align="absmiddle" onclick="expand_collapse(this);" style="cursor: pointer; cursor: hand"><input type="radio" value="standard" checked name="package_detail" id="fp1"><label for="fp1">{$MSG['copy_items'][$sysSession->lang]}</label>
      <ul>
        <li><img src="/theme/{$sysSession->theme}/teach/dot.gif" border="0" align="absmiddle"><input type="checkbox" name="course_elements[]" value="subject_board" id="fp8"><label for="fp8">{$MSG['co_forum_purpose'][$sysSession->lang]}</label></li>
        <li><img src="/theme/{$sysSession->theme}/teach/dot.gif" border="0" align="absmiddle"><input type="checkbox" name="course_elements[]" value="homework"      id="fp10"><label for="fp10">{$MSG['co_rd_homework'][$sysSession->lang]}</label></li>
        <li><img src="/theme/{$sysSession->theme}/teach/dot.gif" border="0" align="absmiddle"><input type="checkbox" name="course_elements[]" value="exam"          id="fp11"><label for="fp11">{$MSG['co_rd_exam'][$sysSession->lang]}</label></li>
        <li><img src="/theme/{$sysSession->theme}/teach/dot.gif" border="0" align="absmiddle"><input type="checkbox" name="course_elements[]" value="questionnaire" id="fp12"><label for="fp12">{$MSG['co_rd_questionnaire'][$sysSession->lang]}</label></li>
        <li><img src="/theme/{$sysSession->theme}/teach/dot.gif" border="0" align="absmiddle"><input type="checkbox" name="course_elements[]" value="node"          id="fp13"><label for="fp13">{$MSG['co_node1'][$sysSession->lang]}</label></li>
            <li class="lcms" style="margin-left:20px"><input type="radio" name="course_path_replace" value="1" id="fp14"><label for="fp14">{$MSG['course_path_replace'][$sysSession->lang]}</label></li>
            <li class="lcms" style="margin-left:20px"><input type="radio" name="course_path_replace" value="0" checked id="fp15"><label for="fp15">{$MSG['course_path_append'][$sysSession->lang]}</label></li>
      </ul>
    </li>
  </ul>
EOB;
echo "<div id='err_size_max'></div>";

    showXHTML_td_E();
    showXHTML_tr_E();

    // LCMS 轉檔設定
    // showXHTML_tr_B('class="lcms font01 cssTrEvn"');
    // showXHTML_td_B('align="left" colspan="2"');
    // echo $MSG['lcms_course_export_title'][$sysSession->lang];
    // showXHTML_td_E();
    // showXHTML_tr_E();
    showXHTML_tr_B('class="lcms font01 cssTrOdd"');
    showXHTML_td('width=100', $MSG['content_proc'][$sysSession->lang]);
    showXHTML_td_B('align="left"');
    $lcms = lcms_api('api/content/label');
    // 取LCMS系統名稱
    if(!empty($lcms['response']['title'])){
        $lcmsServerName = $lcms['response']['title'];
    }
	if($showTargetType == 1){
		echo str_replace('%LCMS_SERVER_NAME%',$lcmsServerName,$MSG['content_proc_3'][$sysSession->lang]);
		echo "<input type='hidden' name='copy_target_type' value='1' />";
	}else{
		echo "<div id='cp_type_panel'><input type='checkbox' class='copy_target_type' name='copy_target_type' value='1' checked> <span id='copy_target_type_1'>".str_replace('%LCMS_SERVER_NAME%',$lcmsServerName,$MSG['content_proc_3'][$sysSession->lang]).'</span></div>';
        echo "<div id='cp_type_msg' ></div>"; 
	}
    
		showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" class="tbody-lcms cssTable" style="display:'.($showTargetType == 1?'none':'block').'"');

			//課程擁有者
			$getTeachSql = ' select b.username,c.course_id,c.caption,a.first_name,a.last_name ' .
				' from WM_term_major as b, WM_term_course as c ,WM_user_account as a'.
				' where b.course_id = %COURSE_ID% and b.course_id = c.course_id and a.username = b.username and b.role&' .
				($sysRoles['teacher'] );
			$sqls = str_replace('%COURSE_ID%', $sysSession->course_id, $getTeachSql);
			$data = $sysConn->GetAssoc($sqls);

            showXHTML_tr_B('class="lcms font01 cssTrOdd"');
                showXHTML_td('colspan=2', str_replace('%LCMS_SERVER_NAME%',$lcmsServerName,$MSG['content_proc_note'][$sysSession->lang]));
			showXHTML_tr_E();

			showXHTML_tr_B('class="lcms font01 cssTrOdd"');
			showXHTML_td('width="100"', '<font color=red>*</font>' . $MSG['lcms_course_owner'][$sysSession->lang]);
			showXHTML_td_B('align="left"');
			$radioHtml = array();
			//check lcms user
			foreach ($data as $v) {
				$default = $v['username'] == $sysSession->username ? 'checked' : '';
				$radio = "<input class='course_owner' type='radio' name='course_owner' value='{$v['username']}' ".$default." /> {$v['username']}({$v['last_name']}{$v['first_name']})";
				echo $radio . '<br>';
			}
			showXHTML_td_E();
			showXHTML_tr_E();

			//年級
			showXHTML_tr_B('class="lcms font01 cssTrOdd"');
			showXHTML_td('', '<font color=red>*</font>' . $MSG['lcms_course_years'][$sysSession->lang]);
			showXHTML_td_B('align="left"');
			//get lcms data
			$classHtml = '<div id="sectionGrade">';
			$subjectHtml = '';
			if(!empty($lcms['response']['data'])){
						$classHtml .= '<label class="span3 offset0" style="margin-bottom: 0;">';
						$classHtml .= "<input type='checkbox' id='grade_chk_all' />";
						$classHtml .= '<span class="lcms-checkbox-text"> '.$MSG['checkbox_all'][$sysSession->lang].'</span></label>';
				foreach ($lcms['response']['data'] as $v) {
					switch ($v['id']) {
						case 'grade':
							foreach ($v['item'] as $id=>$c) {
								if(count($c['item']) > 0){
									foreach($c['item'] as $childItem){
										$classHtml .= '<label class="span3 offset0" style="margin-bottom: 0;">';
										$classHtml .= "<input class='grade_chk' name='lcms_grade[]' type='checkbox' value='{$childItem['id']}' />";
										$classHtml .= '<span class="lcms-checkbox-text"> '.$childItem['title'].'</span></label>';
									}
								}
							}
							break;
						case 'subject':
							$subjectHtml = @json_encode($v['item']);
							break;
					}
				}
						$classHtml .= '</div>';
				echo $classHtml;
			}else{
				echo '<br><font color=red>'.$MSG['lcms_err_grade_empty'][$sysSession->lang].'</font>';
			}
			showXHTML_td_E();
			showXHTML_tr_E();
			//領域
			showXHTML_tr_B('class="lcms font01 cssTrOdd"');
			showXHTML_td('', '<font color=red>*</font>' . $MSG['lcms_course_subject'][$sysSession->lang]);
			showXHTML_td_B('align="left"');
			echo "<div id='subject_select'><select id='subject_parent' name='lcms_subject'></select>";
			echo "<select id='subject_child' name='lcms_subject_child'></select></div>";
			if(empty($subjectHtml) || strcmp($subjectHtml,'[]') == 0 ){
				echo '<br><font color=red>'.$MSG['lcms_err_subject_empty'][$sysSession->lang].'</font>';
			}
			showXHTML_td_E();
			showXHTML_tr_E();
		echo "</table>";

	showXHTML_td_E();
	showXHTML_tr_E();
    //功能說明
    showXHTML_tr_B('class="font01 cssTrEvn"');
    showXHTML_td('width=100', $MSG['lcms_course_intro'][$sysSession->lang]);
    showXHTML_td_B('align="left"');
    $ft = getSupportFileType();
    if( count($ft) > 0 ){
        // 字串過長排版
        if(count($ft) > 10) $ft[9] .= '<br>';
        $supportFileStr = implode(',', $ft);
    }
    // echo str_replace('%SUPPORT_FILES%', $supportFileStr, $MSG['lcms_course_intro_detail'][$sysSession->lang]);

	 echo str_replace(array('%SUPPORT_FILES%','%LCMS_SERVER_NAME%'), array($supportFileStr,$lcmsServerName), $MSG['notice_info'][$sysSession->lang]);
	 echo "<div id='win_sf' style='display:none'><hr>".$MSG['support_file_title'][$sysSession->lang].$supportFileStr.'</div>';

	 showXHTML_td_E();
    showXHTML_tr_E();
    showXHTML_tr_B('class="font01 cssTrEvn"');
    showXHTML_td_B('align="center" colspan="2"');
    showXHTML_input('button', '', $MSG['co_btn_pack'][$sysSession->lang], '', 'id="btnSubmit" class="cssBtn"');

	if( $showTargetType == 1 ){
		echo '&nbsp;&nbsp;&nbsp;&nbsp;';
		showXHTML_input('button', '', $MSG['lcms_reset_import'][$sysSession->lang], '', 'id="btnResetImport" class="cssBtn"');
	}

	showXHTML_td_E();
    showXHTML_tr_E();
} else {
    showXHTML_tr_B('class="font01 cssTrEvn"');
    showXHTML_td('', $MSG['no_data'][$sysSession->lang]);
    showXHTML_tr_E();
}

showXHTML_table_E();
//匯入結果
echo "<div id='ImportResultContainer' style='display:none;width:680px'>";
showXHTML_table_B('id ="ImportResultContainerTable" width="680" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
	showXHTML_tr_B('class="font01 cssTrEvn"');
		showXHTML_td_B('align="center"');
			echo '<div id="ImportResultTd">'. $MSG['lcms_import_wait'][$sysSession->lang];

			echo '</div>';
		showXHTML_td_E();
	showXHTML_tr_E();
showXHTML_table_E();
echo '</div>';
showXHTML_tabFrame_E();

echo "</center>\n";
showXHTML_body_E();
// }}} 主程式 end
?>
<script src="/lib/jquery/jquery.min.js"></script>
<script>
    function expand_collapse(img)
    {
        if (img.parentNode.lastChild != null &&
            img.parentNode.lastChild.tagName != null &&
            img.parentNode.lastChild.tagName.toLowerCase() == 'ul')
        {
                if (img.parentNode.lastChild.style.display == 'none')
                {
                        img.parentNode.lastChild.style.display = '';
                        img.src = img.src.replace(/-c\./, '-cc.');
                }
                else
                {
                        img.parentNode.lastChild.style.display = 'none';
                        img.src = img.src.replace(/-cc\./, '-c.');
                }
        }
    }
	var lcmsServerName = "<?php echo $lcmsServerName ?>";
    //領域資料
    var subjectStore = <?php echo $subjectHtml ?>;
	var lang_warring_course_source = "<?php echo $MSG['warring_course_source'][$sysSession->lang]?>";
    $(function() {
		//重新挑選來源課程
		$('#btnResetImport').click(function(){
			if( confirm("<?php echo str_replace('%LCMS_SERVER_NAME%',$lcmsServerName,$MSG['lcms_reset_import_msg'][$sysSession->lang]) ?>") ){
				//ajax post del lcms course relation
				$.post('co_qti_pack1.php?api=removeRelation',{},function(res){
					location.reload();
				});
			}
		});
        NO_NEED_IMPORT_MSG = "<?php echo str_replace('%LCMS_SERVER_NAME%',$lcmsServerName,$MSG['lcms_no_import'][$sysSession->lang]) ?>";
		$('#course_id').change(function(a,b,c){
			$('.copy_target_type').attr('disabled',false);
            $('input[name=copy_target_type]').prop('checked',true);
            $('#cp_type_panel').show();
            $('#cp_type_msg').hide();
            $('#copy_target_type_1').css('color','black');
			$('#warring_course_source').html('');
			var selectItem = $(this).find('OPTION[value='+$(this).val()+']');
			var relCourseName = selectItem.attr('rel_course_name');
			if( relCourseName !== '' ){
				relCourseName = lang_warring_course_source.replace(/\%REL_COURSE_NAME\%/g, relCourseName);
				relCourseName = relCourseName.replace(/\%LCMS_SERVER_NAME\%/g, lcmsServerName);
				$('#warring_course_source').html(relCourseName);
				$('.tbody-lcms').hide();
				$('.copy_target_type').attr('disabled',true);
                $('#copy_target_type_1').css('color','#CECED1');
                $('#cp_type_panel').hide();
                $('#cp_type_msg').html(NO_NEED_IMPORT_MSG);
                $('#cp_type_msg').show();
                $('input[name=copy_target_type]').prop('checked',false);
			}else if(selectItem.attr('is_rel') == 1){	//選擇的課程已轉換LCMS
				$('.tbody-lcms').hide();
				$('.copy_target_type').attr('disabled',true);
                $('#copy_target_type_1').css('color','#CECED1');
                $('#cp_type_panel').hide();
                $('#cp_type_msg').html(NO_NEED_IMPORT_MSG);
                $('#cp_type_msg').show();
                $('#warring_course_source').html('<?php echo str_replace('%LCMS_SERVER_NAME%',$lcmsServerName,$MSG['lcms_course_is_rel'][$sysSession->lang]) ?>');
                $('input[name=copy_target_type]').prop('checked',false);
			}
            
            if( $('.copy_target_type').attr('disabled') != 'disabled' && $('.copy_target_type').attr('checked') == 'checked' ){
                $('.tbody-lcms').show();
            }
		});

		$('#support_files').click(function(){
			$('#win_sf').toggle();
		});
		$('.copy_target_type').click(function(){
			if(this.checked == false){
				$('.tbody-lcms').hide();
			}else{
				$('.tbody-lcms').show();
			}
		});

        $('input[name="course_elements[]"]').attr('checked',true);

        $('#fp13').bind('click', function() {
			$('.lcms').hide();

            if (this.checked) {
				checkMaxSize();
            }
        });

        //grade all checkbox
        var chks = $('input[name="lcms_grade[]"]');
        chks.click(function(){
            var allchk = true;
            chks.each(function(i,m){
                if(this.checked == false){
                    allchk = false;
                }
            });
            $('#grade_chk_all').get(0).checked = allchk;
        });
        $('#grade_chk_all').click(function(){
            var chkall = this;
            chks.attr('checked',chkall.checked);
        });

        //建立領域下拉選單
        $.each(subjectStore, function(idx, obj) {
            // 沒子領域的話就不顯示
            if( subjectStore[idx].item !== undefined && subjectStore[idx].item !== null && subjectStore[idx].item.length > 0 )
                $('#subject_parent').append("<option value='" + obj.id + "'>" + obj.title + "</option>");
        });
        onChangeSubject(0);
        $('#subject_parent').bind('change', function() {
            onChangeSubject(this.selectedIndex);
        });
        $('#course_id').bind('change', function() {
            //$('#fp13').attr('checked',false);
            $('.lcms').hide();
            if ($('#fp13').attr('checked')){
                checkMaxSize();
            }
        });
        $('#btnSubmit').click(function(){
			if(!validateForm()){
				return false;
			}
            this.disabled = true;
            var params = $('#importForm').serialize();
            //顯示匯入中請稍後的浮動視窗
            $('#mainTable').hide();
            $('#ImportResultContainer').show();
            $.post('co_qti_pack1.php?api=cloneCourse',params,function(res){
                //顯示匯入結果
                var resultHtml = '<?=str_replace('%MESSAGE%','',$MSG['lcms_import_ok'][$sysSession->lang])?>';
				if (res.Error != undefined ){
					alert(res.Message);
					location.reload();
                    return false;
                }else if(res.isImportLCMS) {
                    switch(res.lcmsImportResult){
                        case -1:    //匯入成功, 但不需要匯入LCMS資料(教材節點中沒有符合LCMS格式之檔案)
                            resultHtml = '<?=str_replace('%MESSAGE%','',$MSG['lcms_import_ok'][$sysSession->lang])?>';
                        break;
                        case 1: //LCMS:參數錯誤
                            //匯入失敗!傳送到LCMS的匯入資訊資料錯誤!麻煩重新操作一次!
                            resultHtml = '<?=str_replace('%MESSAGE%',$MSG['lcms_import_err_msg1'][$sysSession->lang],$MSG['lcms_import_err'][$sysSession->lang])?>';
                        break;
                        case 2: //LCMS:不是課程復製精靈的課程包
                        case 4: //LCMS:匯入課程失敗
                        case 5: //LCMS:課程包下載失敗
                            //匯入失敗!傳送到LCMS的匯入資訊在轉換過程中出現資料錯誤!麻煩重新操作一次!
                            resultHtml = '<?=str_replace('%MESSAGE%',$MSG['lcms_import_err_msg2'][$sysSession->lang],$MSG['lcms_import_err'][$sysSession->lang])?>';
                        break;
                        case 3: //LCMS:exportor不存在
                            //LCMS 匯入帳號不存在
                            resultHtml = '<?=str_replace('%MESSAGE%',$MSG['lcms_import_err_msg5'][$sysSession->lang],$MSG['lcms_import_err'][$sysSession->lang])?>';
                        break;
                        default:    //匯入成功
                            <?php
                                $msg = str_replace(array('%LCMS_SERVER_URL%','%LCMS_SERVER_NAME%'),array($lcmsServerUrl, $lcmsServerName),$MSG['lcms_import_ok_msg1'][$sysSession->lang]);
                            ?>
                            resultHtml = '<?=str_replace('%MESSAGE%',$msg,$MSG['lcms_import_ok'][$sysSession->lang])?>';

                    }
					$('#ImportResultTd').html(resultHtml);
                    return false;
                }
                resultHtml = '<?=str_replace('%MESSAGE%','',$MSG['lcms_import_ok'][$sysSession->lang])?>';
                $('#ImportResultTd').html(resultHtml);

            },'json');
        });
        
        //init
        $('#course_id').change();
        
	});
    /**
     * 父領域切換連動子領域
     */
    function onChangeSubject(idx) {
        $('#subject_child').html('');
        $.each(subjectStore[idx].item, function(sub_idx, sub_obj) {
            $('#subject_child').append("<option value='" + sub_obj.id + "'>" + sub_obj.title + "</option>");
        });
    }
    /**
     * 驗證資料
     */
	function validateForm(){
		if($('#fp13').get(0).checked){
			var hasLcmsGrade = false;
			var hasLcmsOwner = false;
			var fmData = $('#importForm').serializeArray();
			for(var i=0;i<fmData.length;i++){
				if( fmData[i].name == 'lcms_grade[]' ) {
					hasLcmsGrade = true;
				}
				if( fmData[i].name == 'course_owner' ) {
					hasLcmsOwner = true;
				}
			}

			// 將來源課程的教材複製到iSUNTW 課程 才做LCMS 表單驗證
			if($('input[name=copy_target_type]').prop('checked') === true){
				if( !hasLcmsGrade ) {
					alert('<?=$MSG['js_err_no_grade'][$sysSession->lang]?>');
					return false;
				}
				if( !hasLcmsOwner ) {
					alert('<?=$MSG['js_err_no_owner'][$sysSession->lang]?>');
					return false;
				}
			}
		}
		return true;
	}

	function checkMaxSize() {
        var msg = '<?=$MSG['lcms_course_size_use'][$sysSession->lang]?>';
        
		//$('#err_size_max').html('<br><?=$MSG['check_size'][$sysSession->lang]?>');
		//判斷課程大小是否超過課程包之限制
		var targetCourseId = $('#course_id').val();
		$.getJSON('co_qti_pack1.php?api=getQuota&course_id='+targetCourseId,function(res){
            var usedFormatMB = res.used;
            $('#course_size_msg').html(msg + ' ' + usedFormatMB);
            $('.lcms').show();
            /**
			$('#err_size_max').html('');
			if(res.used >= res.limit){
				//超過
				var sizeMaxErr = '<?=$MSG['lcms_err_max_size'][$sysSession->lang]?>';
                                sizeMaxErr = sizeMaxErr.replace('%MAX_COURSE_SIZE%', res.limit/1024+'MB');
				$('#err_size_max').html('<br><font color=red>'+sizeMaxErr+'</font>');
			}else{
				$('.lcms').show();
			}
            **/
		});
	}
</script>






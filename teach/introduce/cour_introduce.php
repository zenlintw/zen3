<?php
   /**
    * /辦公室/課程管理/課程簡介/功能首頁
    *
    * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
    *
    * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
    * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
    * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
    *
    * @package     WM3
    * @author      Edi Chen <edi@sun.net.tw>
    * @copyright   2000-2005 SunNet Tech. INC.
    * @version     CVS: $Id: cour_introduce.php,v 1.1 2010/02/24 02:40:29 saly Exp $
    * @link        http://demo.learn.com.tw/1000110138/index.html
    * @since       2005-12-12
    */

// {{{ 函式庫引用 begin
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/cour_introduce.php');
// }}} 函式庫引用 end

// {{{ 常數定義 begin

// }}} 常數定義 end
    
// {{{ 變數宣告 begin
	//	課程簡介,課程安排,教師介紹 (預設模式,是否有上傳檔案)
	$cour_intro_type = array(
								'cour_intro'	=>	array('template', false),
								'cour_arrange'	=>	array('template', false),
								'teach_intro'	=>	array('template', false)
								);
// }}} 變數宣告 end

// {{{ 函數宣告 begin
	
	/**
	 *	顯示 課程介紹/課程安排/教師介紹 的HTML
	 * @param	string $func 功能
	 */
	function showContent($func) {
		global $MSG, $sysSession, $cour_intro_type;
		showXHTML_tr_B('class="cssTrEvn"');
			showXHTML_td('rowspan="2" align="center"', $MSG[$func][$sysSession->lang]);
			showXHTML_td_B();
				echo '<input type="radio" value="template" name="',$func,'" id="',$func,'_template" ',$cour_intro_type[$func][0] == 'template' ? 'checked' : '','><label for="',$func,'_template">',$MSG['intro_template'][$sysSession->lang],'</label>';
			showXHTML_td_E();
			showXHTML_td('align="center"', '<a href="javascript:;" onclick="return doFunc(\''.$func.'\',\'edit\')"    class="cssAnchor">' . $MSG['edit'][$sysSession->lang] . '</a>');
			showXHTML_td('align="center"', '<a href="javascript:;" onclick="return doFunc(\''.$func.'\',\'preview\', \'template\')" class="cssAnchor">' . $MSG['preview'][$sysSession->lang]);
		showXHTML_tr_E();
		showXHTML_tr_B('class="cssTrOdd"');
			showXHTML_td_B();
				echo '<input type="radio" value="upload" name="',$func,'" id="',$func,'_upload" ',$cour_intro_type[$func][0] == 'upload' ? 'checked' : '','><label for="',$func,'_upload">',$MSG['intro_upload'][$sysSession->lang],'</label>';
			showXHTML_td_E();
			showXHTML_td('align="center"', '<a href="javascript:;" onclick="return doFunc(\''.$func.'\',\'upload\')"   class="cssAnchor">' . $MSG['upload'][$sysSession->lang] . '</a>');
			showXHTML_td('align="center"', $cour_intro_type[$func][1] ? ('<a href="javascript:;" onclick="return doFunc(\''.$func.'\',\'preview\',\'upload\')"  class="cssAnchor">' . $MSG['preview'][$sysSession->lang] . '</a>') : $MSG['upload_file_first'][$sysSession->lang]);
		showXHTML_tr_E();
	}
	
	/**
	 * 設定課程介紹,課程安排,教師介紹的預設模式並檢查是否有上傳檔案
	 * @param	string	$func	功能
	 *	@param	string	$content	內容
	 */
	function setCourIntro($func, $content) {
		global $cour_intro_type;
		$type = 'template';
		$isUpload = false;

		if ($xmldoc = @domxml_open_mem($content)) {
			$ctx = xpath_new_context($xmldoc);
			$nodes = $ctx->xpath_eval('/manifest/intro[@type]');
			if (count($nodes->nodeset))
				foreach($nodes->nodeset as $node) {
					if ($node->get_attribute('checked') && $node->get_attribute('checked') == 'true')
						$type = $node->get_attribute('type');
					if ($node->get_attribute('type') == 'upload' && trim($node->get_content()) != '')
						$isUpload = true;
				}
		}
		
		$cour_intro_type[$func][0] = $type;
		$cour_intro_type[$func][1] = $isUpload;
	}

// }}} 函數宣告 end

// {{{ 主程式 begin

	// 先設定課程介紹,課程安排,教師介紹的預設模式並檢查是否有上傳檔案
	$RS = dbGetStMr('WM_term_introduce', 'intro_type, content', 'course_id=' . $sysSession->course_id, ADODB_FETCH_ASSOC);
	if ($RS)
		while($row = $RS->FetchRow()) {
			switch ($row['intro_type']) {
				case 'C'	:
					setCourIntro('cour_intro', $row['content']);
					break;
				case 'R'	:
					setCourIntro('cour_arrange', $row['content']);
					break;
				case 'T'	:
					setCourIntro('teach_intro', $row['content']);
					break;
			}
		}
			
	$js = <<< EOB
	var MSG_ERROR = "{$MSG['Error'][$sysSession->lang]}";
	/**
	 *	do function : upload,edit,preview
	 * @param func string function type(cour_intro, cour_arrange, teach_intro)
	 * @param action string	action type(upload, edit, preview)
	 * @param type string upload or template
	 * @return boolean success or fail
	 */
	function doFunc(func, action, type) {
		var obj = document.getElementById('mainForm');
		if (!obj) alert(MSG_ERROR);
		switch (action) {
			case 'upload' :
				obj.action = 'cour_intro_filemanager.php';
				break;
			case 'edit' :
				obj.action = 'cour_intro_template.php';
				break;
			case 'preview' :
				obj.action = 'cour_intro_show.php';
				break;
			default :
				alert(MSG_ERROR);
				return;
		}
		obj.func.value = func;
		if (type != null) {
			obj.type.value = type;
			window.open('cour_intro_show.php?func=' + func + '&type=' + type, '', 'toolbar=0,menubar=0,scrollbars=1,status=0,width=760,height=630');
		}
		else {
			obj.submit();
		}
		return false;
	}
	
EOB;
	showXHTML_head_B('');
		showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
		showXHTML_script('include', '/lib/jquery/jquery.min.js');
		showXHTML_script('include', '/lib/jplayer/jquery.jplayer.min.js');
		showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array(array($MSG['cour_introduce'][$sysSession->lang]));
		echo "<center>\n";
		showXHTML_tabFrame_B($ary, 1, 'mainForm', 'table1', 'action="cour_introduce_save.php" method="POST" style="display: inline"');
			showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="460" style="border-collapse: collapse" class="cssTable"');
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td('colspan="4"', $MSG['intro_help01'][$sysSession->lang]);
				showXHTML_tr_E();
				showXHTML_tr_B('class="cssTrHead"');
					showXHTML_td('width="25%" align="center"', $MSG['item'][$sysSession->lang]);
					showXHTML_td('width="30%" align="center"', $MSG['type'][$sysSession->lang]);
					showXHTML_td('width="25%"', '&nbsp;');
					showXHTML_td('width="20%" align="center"', $MSG['preview'][$sysSession->lang]);
				showXHTML_tr_E();
				
				showContent('cour_intro');		// 課程介紹
				// MOOC介紹影片
				if (sysEnableMooc) {
				    showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td('width="25%" align="center"', $MSG['cour_intro_video'][$sysSession->lang]);
					showXHTML_td_B('colspan="3" align="left"');
					$courseFolder = sprintf("/base/%d/course/%d/content",$sysSession->school_id, $sysSession->course_id);
					$videoFileRelativePath = $courseFolder."/course_introduce.mp4";
					$videoFileAbsolutePath = realpath(sysDocumentRoot.$courseFolder)."/course_introduce.mp4";
					if (!file_exists($videoFileAbsolutePath)) {
					    echo $MSG['cour_intro_video_notes'][$sysSession->lang];
					}else{
                        echo '<div id="jquery_jplayer_1" style="background-color:black;"></div>
                        <div id="jp_container_1">
                         <a href="#" class="jp-play">Play</a>
                         <a href="#" class="jp-pause">Pause</a>
                        </div>';
					}
					showXHTML_td_E();
				    showXHTML_tr_E();
				}
				showContent('cour_arrange');	// 課程安排		
				showContent('teach_intro');	// 教師介紹

				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td_B('colspan="4"');
						showXHTML_input('hidden', 'func', '', '', '');	// 所執行的是哪個功能
						showXHTML_input('hidden', 'type', '', '', '');	// 所執行的是哪個功能
						showXHTML_input('submit', '', $MSG['save'][$sysSession->lang], '', 'class="cssBtn" onclick="document.getElementById(\'mainForm\').action = \'cour_introduce_save.php\'"');
					showXHTML_td_E();
				showXHTML_tr_E();
				
			showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo "</center>\n";
		if (sysEnableMooc) {
?>
	<script>
	$(document).ready(function(){
    	$("#jquery_jplayer_1").jPlayer({
    		ready: function () {
    			$(this).jPlayer("setMedia", {
        			m4v: "<?php echo $videoFileRelativePath; ?>"
       			});
    		},
    		swfPath: "/lib/jplayer",
    		supplied: "m4v",
    		size: {
    			width: "320px",
    			height: "180px",
    			cssClass: "jp-video-360p"
    		},
    		smoothPlayBar: true,
    		keyEnabled: true
    	});
    });
	</script>
<?php 
		}
	showXHTML_body_E();
// }}} 主程式 end

?>

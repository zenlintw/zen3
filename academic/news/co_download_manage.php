<?php     
	/**
     * 管理環境/公告與聯繫/下載專區管理
     *
     * PHP 4.4.7+, MySQL 4.0.21+, Apache 1.3.36+
     *
     * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
     * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
     * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
     *
     * @package     WM5
     * @author      panchih <a84155844@gmail.com>
     * @copyright   2000-2017 SunNet Tech. INC.
     * @version     SVN: $Id$
     * @since       2017-04-07
     * 
     * 備註：          
     */
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/lstable.php');
	require_once(sysDocumentRoot . '/lang/co_download_manage.php');
	function showbutton($val){
		global $sysSession, $MSG, $countNum;
		$detail = sprintf("<button class='cssBtn' onclick='showbutton(\"%s\")'>%s</button>", $val,$MSG["btn_modify"][$sysSession->lang]);
		return $detail;
	}
	function showCheckBox($val){
		global $countNum;
		return sprintf("<input type='checkbox' name='chk1' id='%s'>", $val);
	}
	function showBeginTime($val,$flag){
		global $sysSession, $MSG, $countNum;
		if($flag == 0){
			return $MSG['time_from'][$sysSession->lang];
		}
		$time = strtotime($val);
		return ($val === "0000-00-00")? $MSG['nlbegintimeshow'][$sysSession->lang] : $val;
	}
	function createImg($url){
		 $type = array('avi','bmp','doc','gif','htm','html','jpg','mp3','pdf','ppt','txt','wav','xls','zip');
		 $icon = '<img border="0" align="absmiddle" src="/theme/default/filetype/' .
                    ((($ext = strtolower(substr(strrchr($url, '.'), 1))) && in_array($ext, $type))?
                $ext : 'default') . '.gif">';
        return $icon;
	}
	function showImg($url){
		//以tab去分割
		$urls = explode(chr(9), $url);
		foreach ($urls as $values) {
			$url = strchr($values,"/base");
			$imgHtml = createImg($url);
			$img_new .= empty($url)? "" : "<a href='{$url}' target='_blank' download>{$imgHtml}</a>";
		}
		
		return $img_new;
	}
	function showEndTime($val,$flag){
		global $sysSession, $MSG;
		if($flag == 0){
			return $MSG['time_to'][$sysSession->lang];
		}
		$time = strtotime($val);
		return ($val === "0000-00-00")? $MSG['nlendtimeshow'][$sysSession->lang] : $val;
	}
	$page = isset($_GET['page']) ? $_GET['page'] : 1;
	$order = isset($_GET['order']) ? $_GET['order'] : 'asc';
	$sortBy = isset($_GET['sortby']) ? $_GET['sortby'] : 'title';
	$page_num = isset($_COOKIE['page']) ? $_COOKIE['page'] : sysPostPerPage; 
	$js = <<<EOF
	
	function addRecond() {
		window.location = "co_download_edit.php?sortby={$sortBy}&order={$order}&page={$page}";
	}
	function Page_Row(row){
			document.cookie = "page=" + row;
			location.reload();
    };
    function showList(){
    	window.open("/academic/co_item/item_list_for_news.php?type_par=download_category",'','width=760,height=500');
    }
	function removenNews(){
		var totalId = [];
		$('input[name="chk1"]:checked').each(function(i) {
			totalId.push(this.id);
		});
		if(totalId.length == 0){
			alert('{$MSG['placeselectdata'][$sysSession->lang]}');
			return ;
		}
		var result = confirm('{$MSG['checkremove'][$sysSession->lang]}');
		if(result){
			$.post("co_download_handle.php",{
				ids : totalId,
				type : 'delete'
			},function(data){
				location.reload();
			});
		}
	}

	function showbutton(u) {
		var obj = document.getElementById("queryFm");
		obj.id.value = u;
		obj.act.value = 'modify';
		obj.submit();
	};
	$(function(){
		$("input[name^='chk1']").change(function(){
			if(!$(this).prop('checked')){
				$("#selchec").prop('checked', false);
				$("#allTitle").text('全選');
			}
		});
		$("#selchec").click(function(){
			if($(this).prop('checked')){
				$("#allTitle").text('全消');
				$("input[name^='chk1']").prop('checked' , true);
			}else{
				$("#allTitle").text('全選');
				$("input[name^='chk1']").prop('checked' , false);
			}
		});
	});
EOF;
	showXHTML_head_B($MSG['epaper_title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script("include","/lib/jquery/jquery.min.js");
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array(array($MSG['epaper_title'][$sysSession->lang]));
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1, 'queryFm', '', 'action="co_download_edit.php" method="post" style="display:inline"');
			$myTable = new table();
			$myTable->extra = 'width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable"';
			showXHTML_input('hidden', 'id', '', '', '');
			showXHTML_input('hidden', 'numCount', '', '', '');
			showXHTML_input('hidden', 'act'  , '', '', '');
			showXHTML_input('hidden', 'sortBy', $sortBy , '', '');
			showXHTML_input('hidden', 'order', $order, '', '');
			showXHTML_input('hidden', 'page', $page, '', '');

			// 工具列
			$toolbar = new toolbar();
			$page_array = array(sysPostPerPage=> $MSG['default_amount'][$sysSession->lang], 20 => 20, 40 => 40, 100=> 100);
			$toolbar->add_caption($MSG['every_page'][$sysSession->lang]);
			$toolbar->add_input('select', 'page_num', $page_array, $page_num, 'class="cssInput" onchange="Page_Row(this.value)";', '');
			$toolbar->add_input('button', '', $MSG['btn_addNew'][$sysSession->lang]   , '', 'class="cssBtn" onclick="addRecond()"');
			$toolbar->add_input('button', '', $MSG['btn_removeNew'][$sysSession->lang], '', 'class="cssBtn" onclick="removenNews()"');
			// $toolbar->add_input('button', '', $MSG['category_management'][$sysSession->lang], '', 'class="cssBtn" onclick="showList()"');
			$myTable->set_def_toolbar($toolbar);
			$myTable->set_page("true",'1',$page_num);
			$myTable->add_sort('open_date', '`open_date` ASC'  , '`open_date` DESC');
			$myTable->add_sort('title'  , '`title` ASC', '`title` DESC');
			$myTable->add_sort('kind'  , '`kind` ASC', '`kind` DESC');
			$myTable->add_sort('close_date' , '`close_date` ASC', '`close_date` DESC');
			$myTable->set_sort(true, $sortBy, $order);
			// 欄位
			$checAll = "<div id='allTitle'>" . $MSG['allselect'][$sysSession->lang] . '</div>' . "<input id='selchec' type='checkbox'/>";
			$myTable->add_field($checAll , '', ''  , '%id'   , 'showCheckBox' , 'align="center" nowrap="noWrap" ');
			$myTable->add_field($MSG['epaper_theme'][$sysSession->lang]     ,'', 'title'  , '%title'       , ''             , 'style="word-break:break-all"' );
			$myTable->add_field($MSG['beginTime'][$sysSession->lang]        ,'', 'open_date'  , '%open_date,%open_date_flag'   , 'showBeginTime'             , 'align="center" nowrap="noWrap"');
			$myTable->add_field($MSG['endTime'][$sysSession->lang]          ,'', 'close_date', '%close_date,%close_date_flag'  , 'showEndTime'             , 'align="center" nowrap="noWrap"');
			$myTable->add_field($MSG['edit_file'][$sysSession->lang]        ,'', ''      , '%attach_path', 'showImg'      , 'align="center" nowrap="noWrap" ');
			$myTable->add_field($MSG['btn_modify'][$sysSession->lang]       ,'', ''      , '%id', 'showbutton'      , 'align="center" nowrap="noWrap" ');
			// SQL 查詢指令
			$tab    = 'CO_download';
			$fields = '`id`,`title`,`attach_path`,`kind`,`open_date_flag`,`open_date`,`close_date_flag`,`close_date`,`creator`';
			$where = 'delete_flag=0';
			$myTable->set_sqls($tab, $fields, $where);
			$myTable->show();
		showXHTML_tabFrame_E();
		echo '</div>';
	showXHTML_body_E();
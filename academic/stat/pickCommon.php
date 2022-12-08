<?php
	/**
	 * 檔案說明
	 *	學校統計資料 - 抓取課程群組/課程/班級群組/班級共用
	 * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
	 *
	 * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
	 * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
	 * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
	 *
	 * @package     WM3
	 * @author      Edi Chen <edi@sun.net.tw>
	 * @copyright   2000-2007 SunNet Tech. INC.
	 * @version     CVS: $Id: pickCommon.php,v 1.1 2010/02/24 02:38:43 saly Exp $
	 * @link        http://demo.learn.com.tw/1000110138/index.html
	 * @since       2007-03-20
	 * 備註: 各程式引用者須自行定義
	 * in php: function parseTree, $showTitle, $sIndex
	 * in javascript: setClick ($extra_js)
	 */
	if (!function_exists('parseTree')) die('Can\'t execute standalone!');
	
	require_once(sysDocumentRoot . '/lang/popup_lang.php');
	
	$js = <<< EOF
		/**
		 * 改變滑鼠滑過的背景色
		 */
		function chBgc(obj,mode){
			if (obj.style.backgroundColor == '#f0f0f0') return;
			obj.className = mode ? "cssTbFocus" : "cssTbBlur";
		}
		
		var expandingFlag = 'none';
		/**
		 * 全展開/收攏
		 */
		function expandingAll(flag){
			expandingFlag = flag ? '' : 'none';
			var nodes = document.getElementsByTagName('img');
			var icon = expandingFlag ? '/theme/default/learn/icon-c.gif' : '/theme/default/learn/icon-cc.gif';
            
            /*#47211[教師/課程管理/課程設定/尋找教材] 在Chrome瀏覽器之下，少了一個父節點「新增教材類別_3」：dom差異*/
            var browser = 'ie';
                
            if(navigator.userAgent.indexOf('MSIE')>0 && detectIE() <= 7){
                browser = 'ie';
            }else if(navigator.userAgent.indexOf('Firefox')>0){
                browser = 'ff';
            }else if(navigator.userAgent.indexOf('Chrome')>0 || detectIE() >= 8){
                browser = 'chr';
            }else if(navigator.userAgent.indexOf('Safari')>0){
                browser = 'sf';
            }else{
                browser = 'op';
            }
            
			for(var i=0; i<nodes.length; i++){
            
                if (browser === 'chr' || browser === 'sf' || browser === 'ff') {
            
                    /*#48584 chrome 管理者-學校管理-學校統計資料-學習路徑節點統計-群組選單，點選「+」展開節點功能沒有反應：dom結構不一樣所致*/
                    /*課程設定*/
                    if (nodes[i].parentNode.nextSibling != null && (nodes[i].parentNode.nextSibling.tagName == 'INPUT' || nodes[i].parentNode.nextSibling.tagName == 'SPAN')) {
                    
                        if (nodes[i].parentNode.parentNode.parentNode.parentNode.parentNode != null  && nodes[i].parentNode.parentNode.parentNode.parentNode.parentNode.tagName.toLowerCase() === 'ul'){
                            nodes[i].parentNode.parentNode.parentNode.parentNode.style.display = expandingFlag;
                            nodes[i].parentNode.parentNode.parentNode.parentNode.previousSibling.firstChild.firstChild.firstChild.src = icon;
                        }
                        /*#48636 chrome + patech2 管理者-課程管理-新增課程-尋找教材，點選「展開」按鈕，節點卻收攏，點選「收攏」按鈕，節點卻展開*/
                    
                    /*學校報表*/
                    } else {
                
                        if(nodes[i].parentNode.parentNode.parentNode.tagName.toLowerCase() == nodes[i].parentNode.parentNode.parentNode.parentNode.tagName.toLowerCase() && nodes[i].parentNode.parentNode.parentNode.tagName.toLowerCase() == 'ul') {
                            nodes[i].parentNode.parentNode.parentNode.style.display = expandingFlag;
                            nodes[i].parentNode.parentNode.parentNode.previousSibling.firstChild.firstChild.firstChild.src = icon;
                        }
                    }
                } else {
                    if (nodes[i].parentNode.tagName.toLowerCase() == 'a' && nodes[i].src.search(/icon-cc?\.gif$/) > -1) {
                    nodes[i].parentNode.parentNode.parentNode.lastChild.style.display = expandingFlag;
                    nodes[i].src = icon;
                    }
                }
                expandingFlag = expandingFlag ? 'none' : '';
            }
		}

		/**
		 * 單一節點展開/收攏
		 */
		function expanding(obj, mode){
        
            /*#48628 chrome 管理者-課程管理-新增課程-尋找教材，點選「+」展開節點功能沒有反應：限制有反應的點*/
            if (obj.firstChild.src.search(/icon-ccc.gif$/) != -1) {
                return false;
            }
            
            // console.log(obj);
            
            /*#47211[教師/課程管理/課程設定/尋找教材] 在Chrome瀏覽器之下，少了一個父節點「新增教材類別_3」：dom差異*/
            var browser = 'ie';
            if(navigator.userAgent.indexOf('MSIE')>0 && detectIE() <= 7){
                browser = 'ie';
            }else if(navigator.userAgent.indexOf('Firefox')>0){
                browser = 'ff';
            }else if(navigator.userAgent.indexOf('Chrome')>0 || detectIE() >= 8){
                browser = 'chr';
            }else if(navigator.userAgent.indexOf('Safari')>0){
                browser = 'sf';
            }else{
                browser = 'op';
            }
            
			/*#47464 [Safari][教師/課程管理/課程設定/尋找教材] 在Safari瀏覽器之下，少了一個父節點「新增教材類別_3」*/            
			if (browser === 'chr' || browser === 'sf' || browser === 'ff') {
                // console.log(obj.parentNode.parentNode.nextSibling.firstChild.firstChild.firstChild.tagName);
                if(obj.parentNode.parentNode.nextSibling.firstChild.firstChild.firstChild.tagName === 'A' || obj.parentNode.parentNode.nextSibling.firstChild.firstChild.firstChild.tagName === 'IMG') {
                    var ulObj = obj.parentNode.parentNode.nextSibling;
                } else {
                    var ulObj = obj.parentNode.parentNode.nextSibling.lastChild;
                }
                
                /*#48584 chrome 管理者-學校管理-學校統計資料-學習路徑節點統計-群組選單，點選「+」展開節點功能沒有反應：dom結構不一樣所致*/
                if(!(obj.nextSibling != null && (obj.nextSibling.tagName == 'INPUT' || obj.nextSibling.tagName == 'SPAN'))) {ulObj = ulObj.parentNode;}
                
                var m = (typeof(mode) == 'undefined') ? ulObj.style.display : mode;
                // console.log(ulObj);
                if (m == 'none'){
                    // console.log(obj.parentNode.parentNode.nextSibling);
                    obj.parentNode.parentNode.nextSibling.style.display = '';
                    // ulObj.style.display = '' ;
                    obj.firstChild.src = '/theme/default/learn/icon-cc.gif';
                }
                else{
                    // console.log(obj.parentNode.parentNode.nextSibling);
                    obj.parentNode.parentNode.nextSibling.style.display = 'none';
                    // ulObj.style.display = 'none' ;
                    obj.firstChild.src = '/theme/default/learn/icon-c.gif';
                    
                } 
                
            } else {
            
                var ulObj = obj.parentNode.parentNode.lastChild;
                var m = (typeof(mode) == 'undefined') ? ulObj.style.display : mode;
                
                if (m == 'none'){
                    ulObj.style.display = '' ;
                    obj.firstChild.src = '/theme/default/learn/icon-cc.gif';
                }
                else{
                    ulObj.style.display = 'none' ;
                    obj.firstChild.src = '/theme/default/learn/icon-c.gif';
                }   
            }
			return false;
		}
		
		window.onload = function() {expandingAll(false);};
EOF;

	$css = <<< EOB
		ul		  {list-style-type: none; margin-left: 14; padding-left: 0}
		li		  {cursor: default}
EOB;

	showXHTML_head_B('');
		showXHTML_CSS('include'	 , "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
		showXHTML_CSS('inline'	 , $css);
		showXHTML_script('include', '/public/js/common.js');
		showXHTML_script('inline', $js);
		showXHTML_script('inline', $extra_js);
	showXHTML_head_E();
	showXHTML_body_B('topmargin="0" leftmargin="0" marginwidth="0" marginheight="0"');
		$ary = array(array($showTitle, 'tabs_gplist'));
		showXHTML_tabFrame_B($ary, 1, 'modGpList', 'tabs_gplist', 'style="display:inline"', '', true);
			showXHTML_table_B('border="0" cellspacing="1" cellpadding="3" class="cssTable" width="100%"');
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td_B();
						showXHTML_input('button', 'btnExpand1' , $MSG['expend'][$sysSession->lang]     , '', 'onclick="expandingAll(true);"  class="cssBtn"');
						showXHTML_input('button', 'btnCollect1', $MSG['collect'][$sysSession->lang]    , '', 'onclick="expandingAll(false);" class="cssBtn"');
						if (isSet($extra_btn)) echo $extra_btn;
					showXHTML_td_E();
				showXHTML_tr_E();
				showXHTML_tr_B('class="cssTbBlur"');
					showXHTML_td_B('nowrap="nowrap"');
						parseTree($sIndex);
					showXHTML_td_E();
				showXHTML_tr_E();
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td_B();
						showXHTML_input('button', 'btnExpand1' , $MSG['expend'][$sysSession->lang]     , '', 'onclick="expandingAll(true);"  class="cssBtn"');
						showXHTML_input('button', 'btnCollect1', $MSG['collect'][$sysSession->lang]    , '', 'onclick="expandingAll(false);" class="cssBtn"');
						if (isSet($extra_btn)) echo $extra_btn;
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_tabFrame_E();
?>
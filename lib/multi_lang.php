<?php
   /**
    * 檔案說明
    *	提供多語系輸入
    * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
    *
    * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
    * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
    * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
    *
    * @package     WM3
    * @author      Edi Chen <edi@sun.net.tw>
    * @copyright   2000-2005 SunNet Tech. INC.
    * @version     CVS: $Id: multi_lang.php,v 1.2 2009-08-31 03:53:56 edi Exp $
    * @link        http://demo.learn.com.tw/1000110138/index.html
    * @since       2006-03-01
    *
    * Usage :
    * require_once(sysDocumentRoot . '/lib/multi_lang.php');
    *
    * $multi_lang = new Multi_lang(false, $langs, $first_css);
	* $multi_lang->show();
	*
	* in javascript for check data :
	* if (!chk_multi_lang_input(1, true, 'no_data_message', 'data_error_message'))
	* 	 do_something('fail');
	* else
	*	 do_something('success');
    */

// {{{ 函式庫引用 begin
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/multi_lang.php');
// }}} 函式庫引用 end

// {{{ 常數定義 begin

// }}} 常數定義 end

// {{{ 變數宣告 begin
	$tb_counts = 0;	// 判斷目前有幾個多語系輸入框

	$js_output = false;

    $default_langs = array('Big5'		=>	'undefined',
						   'GB2312'		=>	'undefined',
						   'en'			=>	'undefined',
						   'EUC-JP'		=>	'undefined',
						   'user_define'=>	'undefined'
							);

	if (function_exists('removeUnAvailableChars'))
		removeUnAvailableChars($default_langs);

	// 取得學校預設語系
	list($sch_lang) = dbGetStSr('WM_school', 'language', 'school_id=' . $sysSession->school_id . ' and school_host = "'. $_SERVER['HTTP_HOST'] .'"', ADODB_FETCH_NUM);

// }}} 變數宣告 end

// {{{ 函數宣告 begin

// }}} 函數宣告 end

	class Multi_lang {
		var $tb_id;			// 多語系table的id
		var $tb_attr;		// 多語系table的attribute
		var $arrLang;		// 多語系設定值
		var $startCss;		// 第一個語系的css
		var $multi_lang;	// 一開始show出來時是否提供多語系輸入

		/**
		 * Constructor
		 */
		function Multi_lang($multi_lang = false, $langs = '', $css = 'class="cssTrEvn"') {
			global $default_langs, $tb_counts;

			$this->multi_lang = $multi_lang;
			$this->arrLang    = $langs != '' ? (is_array($langs) ? $langs : unserialize($langs)) : $default_langs;
			$this->startCss   = $css;
			$this->tb_attr    = '" width="100%" border="0" cellspacing="1" cellpadding="3" style="background-color: #EAEAF4;border: 1px  #EAEAF4"';

			$this->tb_id      = 'tb_multi_lang_' . (++$tb_counts);
		}

		/**
		 * 設定table的attribute
		 * @param $attr string table attribute
		 */
		function set_tb_attr($attr) {
			$this->tb_attr = $attr;
		}

		function getJS() {
			global $sch_lang, $MSG, $sysSession;

			$css1 = $this->startCss == 'class="cssTrEvn"' ? 'cssTrEvn' : 'cssTrOdd';
			$css2 = $this->startCss == 'class="cssTrEvn"' ? 'cssTrOdd' : 'cssTrEvn';

			return <<< BOF
	/**
	 * 設定多語系輸入框的展開與否
	 * @param tb_id string 多語系table的id(預防同一個頁面需要多個多語系的輸入框)
	 * @param multi_lang boolean 是否展開多語系
	 */
	function chg_lang_setting(tb_id, multi_lang) {
		var obj = document.getElementById(tb_id);
		if (!obj) alert('table not exist!');

		var css = "{$css1}";
		var lang = '{$sysSession->lang}';
		var idx = '-1';

		var inputs = obj.getElementsByTagName('input');
		for (var i = 0; i < inputs.length; i++)
		{
			if (inputs[i].type != 'button') continue;
			inputs[i].onclick = function() {chg_lang_setting(tb_id, !multi_lang)};
			inputs[i].value   = multi_lang ? "{$MSG['less_lang'][$sysSession->lang]}" : "{$MSG['more_lang'][$sysSession->lang]}";
		}

		for(var i = 0; i < obj.rows.length; i++) {
			if (obj.rows[i].id == 'tr_' + tb_id + '_' + lang) idx = i;
			obj.rows[i].style.display = (multi_lang || i == idx) ? '' : 'none';

			// 控制tr css
			if (obj.rows[i].style.display == '') {
				obj.rows[i].className = css;
				css = css == "{$css1}" ? "{$css2}" : "{$css1}";
			}
		}

		if (idx != 0) {
			obj.rows[idx].cells[2].style.display = multi_lang ? 'none' : '';
			obj.rows[0].cells[2].style.display = multi_lang ? '' : 'none';
			obj.rows[0].cells[2].rowSpan = multi_lang ? obj.rows.length : '1';
		}
		else
			obj.rows[0].cells[2].rowSpan = multi_lang ? obj.rows.length : '1';

		return;
	}

	/**
	 * 目的 : 用來檢查是否有輸入資料，並且檢查格式是否正確
	 * @param int idx 多語系輸入框的編號(從1開始)
	 * @param boolean bln_chk_data 是否檢查資料格式
	 * @param string msg_no_data 當無任何輸入資料時警告訊息(可不填，以預設值警告)
	 * @param string msg_data_error 輸入資料格式有誤時的錯誤訊息(可不填，以預設值警告)
	 * @return boolean 是否有輸入且格式正確
	 */
	function chk_multi_lang_input(idx, bln_chk_data, msg_no_data, msg_data_error, max_length) {
		var lang = '{$sch_lang}';
		var obj = document.getElementById('tb_multi_lang_'+idx);

		if (!obj) alert('table not exist');

		var met = 0, blnInput = false;
		var inputs = obj.getElementsByTagName('input');
		for(var i = 0; i < inputs.length; i++) {
			if (inputs[i].type != 'text') continue;
			if (inputs[i].value != '' && inputs[i].value != 'undefined') {
				blnInput = true;
				if ( typeof(bln_chk_data) != 'undefined' && bln_chk_data && typeof(msg_data_error) != 'undefined' &&  !Filter_Spec_char(inputs[i].value) ) {
					inputs[i].select();
					alert(msg_data_error != 'DEFAULT' ? msg_data_error : "{$MSG['msg_name_style_error'][$sysSession->lang]}");
					return false;
				}
                if (max_length >= 1 && inputs[i].value.length > max_length) {
					inputs[i].select();
                    var max_msg = '{$MSG['max_length'][$sysSession->lang]}'.replace('%MAX_LENGTH%',max_length);
					alert(max_msg);
					return false;
                }
                // 同儕互評名稱修改時，點選下一步時，同步到預覽頁面
                if (document.getElementById('preview_title') !== null && document.getElementById('preview_title').value !== null) {
                    $('#preview_title').text(inputs[i].value);
                }
			}
			if (inputs[i].name == lang) met = i;
		}

		if(blnInput) return true;	// 至少有輸入一資料，並且輸入格式符合

		inputs[met].select();
		alert(msg_no_data ? msg_no_data : "{$MSG['msg_no_fill'][$sysSession->lang]}");

		return false;
	}

BOF;
		}

		/**
		 * 秀出前端提供使用者輸入多語系的UI
		 * @param $input boolean 多語系輸入框(true)或者單純顯示(false)用
		 * @param $arr_names array 各語系對應的name
		 */
		function show($input = true, $arr_names = null, $extra='') {
			global $MSG, $sysSession, $default_langs, $js_output;

			if (empty($this->arrLang)) {return;}

			if (!$js_output) {
				showXHTML_script('inline', $this->getJS());
				showXHTML_script('include', '/lib/filter_spec_char.js?'.time());
				$js_output = true;
			}

			$col = $this->startCss;
			$count = 0;
			showXHTML_table_B('id="'.$this->tb_id.'"' . $this->tb_attr);
			foreach($default_langs as $key => $val) {
				$count++;
                                // 如果設定一種語系，學校設定和學校常數都要確認有存成該語言
				showXHTML_tr_B($col . (count($default_langs) >= 2 && !$this->multi_lang && $sysSession->lang != $key ? ' style="display: none"' : '') . ' id="tr_'.$this->tb_id.'_'.$key.'"');
                    /* #56255(B) fixed IE 跑版問題 20141001 By Spring*/
					showXHTML_td('nowrap', $MSG['multi_lang_'.$key][$sysSession->lang]);
                    /* #56255(E) */
					showXHTML_td_B('');
						$tmp_name = ($arr_names && is_array($arr_names) && $arr_names[$key] && $arr_names[$key] != '') ? $arr_names[$key] : $key;
						if ($input)
							showXHTML_input('text', $tmp_name, ($this->arrLang[$key] && $this->arrLang[$key] != '') ? $this->arrLang[$key] : $val, '', 'id="' . $tmp_name . '" size="25" class="cssInput" onmouseover="if(this.value==\'undefined\')this.select();" ' . $extra);
						else
							echo ($this->arrLang[$key] && $this->arrLang[$key] != '') ? $this->arrLang[$key] : $val;
					showXHTML_td_E();
					if ($input) {
						if ($this->multi_lang){
							showXHTML_td_B($count == 1 ? 'rowSpan="5"' : 'style="display:none"');
                                        }else{
                                                    
							showXHTML_td_B($sysSession->lang == $key ? '' : 'style="display:none"');
                                                        if (is_array($default_langs) && count($default_langs) >= 2) {
                                                            showXHTML_input('button', '', $this->multi_lang ? $MSG['less_lang'][$sysSession->lang] : $MSG['more_lang'][$sysSession->lang], '', 'onclick="chg_lang_setting(\''.$this->tb_id.'\',\''.!$this->multi_lang.'\')"');
                                                        }
                                        }
                                                        
						showXHTML_td_E();
					}
				showXHTML_tr_E();
				if ($this->multi_lang || $sysSession->lang == $key)
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
			}
			showXHTML_table_E();
		}

	}

?>

<?php
/**
* 問卷、試卷之題目答題統計類別
*
* PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
*
* LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
* 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
* 照書中所限制之使用範圍使用之，否則仍以侵權論究。
*
* @package     WM3
* @author      Wiseguy Liang <wiseguy@mail.wiseguy.idv.tw>
* @copyright   2000-2005 SunNet Tech. INC.
* @version     CVS: $Id: exam_stat_class.php,v 1.9 2010-10-08 06:50:08 sj Exp $
* @link        http://demo.learn.com.tw/1000110138/index.html
* @since       2006-12-13
*/

class UTF8_decode
{
	function code2utf($num)
	{
	  if($num<128)return chr($num);
	  if($num<2048)return chr(($num>>6)+192).chr(($num&63)+128);
	  if($num<65536)return chr(($num>>12)+224).chr((($num>>6)&63)+128).chr(($num&63)+128);
	  if($num<2097152)return chr(($num>>18)+240).chr((($num>>12)&63)+128).chr((($num>>6)&63)+128) .chr(($num&63)+128);
	  return '';
	}

	function u8decode($str)
	{
	    //return preg_replace('/&#x([0-9A-F]{4});/ie', 'UTF8_decode::code2utf(hexdec("\1"))', $str);
	    $reg = '/&#x([0-9A-F]{4});/i';
	    return preg_replace_callback($reg, 'code2utf', $str);
	}
}

class QTI_exam_stat
{
	var $total;                     // 處理多少答案卷數目
	var $failure;                   // 其中無法處理的答案卷數目
	var $first;                     // 是第一份嗎？處理第一份時要取得題目與選項
	var $curr_item_id;              // 目前的 item ident
	var $curr_lid_id;               // 目前選擇題的 response_lid ident
	var $item_type;                 // 題目類型 (只處理是非、選擇)
	var $result_array;              // 統計結果陣列
	var $resp_label_1st_mattext;	// 旗標，用於辨識是否為選項
	var $response_value;            // 作答之答案
	var $xpath;                     // 目前節點的 xpath
	var $xml_parser;                // SAX parser
	var $table;						// 記錄這一題是否為選擇性項目

	/**
	 * 建構子 初始化變數
	 */
	function QTI_exam_stat()
	{
		$this->total = 0;
		$this->failure = 0;
		$this->first = true;
		$this->result_array = array();
		$this->init();

		$this->xml_parser = xml_parser_create('UTF-8');
		xml_set_object($this->xml_parser,$this);
		xml_parser_set_option($this->xml_parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parser_set_option($this->xml_parser, XML_OPTION_CASE_FOLDING, 0);
// 		xml_set_element_handler($this->xml_parser, array(&$this, 'startElement'), array(&$this, 'endElement'));
// 		xml_set_character_data_handler($this->xml_parser, array(&$this, 'characterData'));
		xml_set_element_handler($this->xml_parser, 'startElement','endElement');
		xml_set_character_data_handler($this->xml_parser, 'characterData');
	}

	/**
	 * 初始化每份答案卷處理前要初始化的變數
	 */
	function init()
	{
		$this->curr_item_id = '';
		$this->curr_lid_id = '';
		$this->item_type = 0;
		$this->resp_label_1st_mattext = false;
		$this->response_value = '';
		$this->xpath = '';
	}

	/**
	 * 進入節點之處理函式
	 *
	 * @param   resource    $parser     SAX parser resource
	 * @param   string      $name       節點的 tag 名稱
	 * @param   hash_array  $attrs      該節點之屬性關聯陣列
	 */
	function startElement($parser, $name, $attrs)
	{
	    $this->xpath .= '/' . $name;

		switch ($name)
		{
		case 'response_value':
		    $this->response_value = true;
		    return;
		    break;
        case 'item_result':
            $this->curr_item_id = $attrs['ident_ref'];
            return;
            break;
		case 'response_lid':
			$this->curr_lid_id = $attrs['ident'];
			return;
			break;
		case 'response':
			$this->curr_lid_id = $attrs['ident_ref'];
			return;
			break;
        case 'render_choice':
        	$this->table[$this->curr_item_id] = true;
            if (@strpos($this->xpath, '/response_lid/render_choice', strlen($this->xpath)-27) !== false)
            {
                $this->item_type = 1;
			}
			return;
		    break;
/*
        case 'render_fib':
            if (@strpos($this->xpath, '/response_str/render_fib', strlen($this->xpath)-24) !== false ||
                @strpos($this->xpath, '/response_num/render_fib', strlen($this->xpath)-24) !== false)
            {
                $this->item_type = 2;
			}
			break;
*/
		case 'response_label':
			if ($this->item_type == 1)
			{
                $this->resp_label_1st_mattext = $attrs['ident'];
				// $this->result_array[$this->curr_item_id][$attrs['ident']]['caption'] = '';
				// $this->result_array[$this->curr_item_id][$attrs['ident']]['count'] = 0;
				$this->result_array[$this->curr_item_id][$this->curr_lid_id][$attrs['ident']]['count'] = 0;
			}
			return;
		    break;
	    case 'item':
	        $this->curr_item_id = $attrs['ident'];
	        return;
	        break;
		}
	}

	/**
	 * 離開節點之處理函式
	 *
	 * @param   resource    $parser     SAX parser resource
	 * @param   string      $name       節點的 tag 名稱
	 */
	function endElement($parser, $name)
	{
		$this->xpath = dirname($this->xpath);

	    switch ($name)
	    {
			case 'response_value':
			    $this->response_value = false;
			    return;
			    break;
			case 'response_label':
			    $this->resp_label_1st_mattext = false;
			    return;
			    break;
            case 'item':
			case 'item_result':
		        $this->curr_item_id = '';
				$this->curr_lid_id = '';
		        $this->item_type = 0;
		        return;
		        break;
	    }
	}

	/**
	 * 遇到文字節點之處理函式
	 *
	 * @param   resource    $parser     SAX parser resource
	 * @param   string      $data       文字節點內容
	 */
	function characterData($parser, $data)
	{
	    if (($d = trim($data)) == '') return;

		// echo "<pre>cData: {$data}</pre>";

	    if ($this->first &&
	    	strpos($this->xpath, '/item/presentation/flow/material/') !== false)
	    {
	        if (isset($this->result_array[$this->curr_item_id]['title']))
	            $this->result_array[$this->curr_item_id]['title'] .= $d;
			else
			    $this->result_array[$this->curr_item_id]['title'] = $d;

			return;
		}

		if ($this->resp_label_1st_mattext &&
		    isset($this->result_array[$this->curr_item_id][$this->curr_lid_id][$this->resp_label_1st_mattext]))
        {
			if (isset($this->result_array[$this->curr_item_id][$this->curr_lid_id][$this->resp_label_1st_mattext]['caption'])) {
				$this->result_array[$this->curr_item_id][$this->curr_lid_id][$this->resp_label_1st_mattext]['caption'] .= htmlspecialchars($d);
			} else {
				$this->result_array[$this->curr_item_id][$this->curr_lid_id][$this->resp_label_1st_mattext]['caption'] = htmlspecialchars($d);
			}
			return;
		}

		if ( $this->response_value &&
		     ( isset($this->result_array[$this->curr_item_id][$this->curr_lid_id][$d]['caption']) || $this->table[$this->curr_item_id] ) )
		{
			$this->result_array[$this->curr_item_id][$this->curr_lid_id][$d]['count']++;
			return;
		}
	}

	/**
	 * 進行一份答案卷的處理
	 *
	 * @param   string      $xml        答案卷之 QTI_XML
	 */
	function parse($xml)
	{
		$this->init();
		if ($this->first)
			$xml = str_replace('<questestinterop', '<qti_stat_parse><questestinterop', $xml);
		else
		    $xml = preg_replace(array('!<\?xml\s.*\?>!isU','!<item\s.*</item>!isU'), array('',''), $xml);

		if (!xml_parse($this->xml_parser, $xml, false))
		{
		    $this->failure++;
			print(sprintf('<p>XML error: %s at line %d</p>',
		    	  xml_error_string(xml_get_error_code($this->xml_parser)),
		    	  xml_get_current_line_number($this->xml_parser)));
		}
		else
		    $this->total++;

		if ($this->first) $this->first = false;
	}

	/**
	 * 答案卷處理完畢
	 */
	function endParse()
	{
		if (!xml_parse($this->xml_parser, '</qti_stat_parse>', true))
		{
		    $this->failure++;
			print(sprintf('<p>XML error: %s at line %d</p>',
		    	  xml_error_string(xml_get_error_code($this->xml_parser)),
		    	  xml_get_current_line_number($this->xml_parser)));
		}
   		xml_parser_free($this->xml_parser);
	}
}

class QTI_exam_detail
{
	var $index_add_flag = false;	// 判斷是否在帳號自動新增流水號
	var $total;                     // 處理多少答案卷數目
	var $failure;                   // 其中無法處理的答案卷數目
	var $first;                     // 是第一份嗎？處理第一份時要取得題目與選項
	var $curr_item_id;              // 目前的 item ident
	var $curr_lid_id;               // 目前選擇題的 response_lid ident
	var $item_type;                 // 題目類型 (只處理是非、選擇)
	var $result_array;              // 統計結果陣列
	var $user_result_array;
	var $resp_label_1st_mattext;	// 旗標，用於辨識是否為選項
	var $response_value;            // 作答之答案
	var $xpath;                     // 目前節點的 xpath
	var $xml_parser;                // SAX parser
	var $examinee;
	var $response_value_count;
	var $qtype_array;
	var $counterTable;				//用來存放測驗的次數
	var $app_result_array;          // 供 app 使用的統計結果陣列

	/**
	 * 建構子 初始化變數
	 */
	function QTI_exam_detail( $flag=null )
	{
		$this->index_add_flag = $flag;
		$this->total = 0;
		$this->failure = 0;
		$this->first = true;
		$this->result_array = array();
		$this->user_result_array = array();
		$this->qtype_array = array();
		$this->app_result_array = array();
		$this->init();

		$this->xml_parser = xml_parser_create('UTF-8');
		xml_set_object($this->xml_parser,$this);
		xml_parser_set_option($this->xml_parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parser_set_option($this->xml_parser, XML_OPTION_CASE_FOLDING, 0);
	// 		xml_set_element_handler($this->xml_parser, array(&$this, 'startElement'), array(&$this, 'endElement'));
	// 		xml_set_character_data_handler($this->xml_parser, array(&$this, 'characterData'));
		xml_set_element_handler($this->xml_parser, 'startElement','endElement');
		xml_set_character_data_handler($this->xml_parser, 'characterData');
	}

	/**
	 * 初始化每份答案卷處理前要初始化的變數
	 */
	function init()
	{
		$this->curr_item_id = '';
		$this->item_type = 0;
		$this->resp_label_1st_mattext = false;
		$this->response_value = '';
		$this->xpath = '';
	}

	/**
	 * 進入節點之處理函式
	 *
	 * @param   resource    $parser     SAX parser resource
	 * @param   string      $name       節點的 tag 名稱
	 * @param   hash_array  $attrs      該節點之屬性關聯陣列
	 */
	function startElement($parser, $name, $attrs)
	{
		$this->xpath .= '/' . $name;

		switch ($name)
		{
		case 'response_value':
			$this->response_value = true;
			if (preg_match('/^PAIR\d+$/', $this->curr_lid_id)) {
				// 配合題
				$this->user_result_array[$this->examinee][$this->curr_item_id][] = '';
			}
			return;
			break;
		case 'item_result':
			$this->curr_item_id = $attrs['ident_ref'];
			$this->response_value_count = 0;
			return;
			break;
		case 'response_lid':
			$this->curr_lid_id = $attrs['ident'];
			return;
			break;
		case 'response':
			$this->curr_lid_id = $attrs['ident_ref'];
			return;
			break;
		case 'render_choice':
			if (@strpos($this->xpath, '/response_lid/render_choice', strlen($this->xpath)-27) !== false)
			{
				$this->item_type = 1;
				if (!isset($this->qtype_array[$this->curr_item_id])) $this->qtype_array[$this->curr_item_id] = 1;
			}
			return;
			break;
		case 'render_fib':
			if (@strpos($this->xpath, '/response_str/render_fib', strlen($this->xpath)-24) !== false ||
				@strpos($this->xpath, '/response_num/render_fib', strlen($this->xpath)-24) !== false)
			{
				$this->item_type = 2;
				if (!isset($this->qtype_array[$this->curr_item_id])) $this->qtype_array[$this->curr_item_id] = 2;
			}
			break;
		case 'render_extension':
			if (strpos($this->xpath, '/response_grp/'))
			{
				$this->item_type = 4;
				if (!isset($this->qtype_array[$this->curr_item_id])) $this->qtype_array[$this->curr_item_id] = 4;
			}
			break;
		case 'response_label':
			if ($this->item_type == 1)
			{
				$this->resp_label_1st_mattext = $attrs['ident'];
				// $this->result_array[$this->curr_item_id][$attrs['ident']]['caption'] = '';
				// $this->result_array[$this->curr_item_id][$attrs['ident']]['count'] = 0;
				$this->result_array[$this->curr_item_id][$this->curr_lid_id][$attrs['ident']]['count'] = 0;
			}
			elseif ($this->item_type == 4)
			{
				if ($attrs['match_max'] == '' && $attrs['match_group'] == '')
				{
					foreach($this->result_array[$this->curr_item_id] as $k => $v)
					{
						if ($k != 'title')
							$this->result_array[$this->curr_item_id][$k]['count'][$attrs['ident']] = 0;
					}
				}
				else
				{
					$this->resp_label_1st_mattext = $attrs['ident'];
					// $this->result_array[$this->curr_item_id][$attrs['ident']]['caption'] = '';
					$this->result_array[$this->curr_item_id][$attrs['ident']]['count'] = array();
				}
			}
			elseif ($this->item_type == 2)
			{
				$this->result_array[$this->curr_item_id][$attrs['ident']]['caption'] = '&nbsp;';
			}

			return;
			break;
		case 'item':
			$this->curr_item_id = $attrs['ident'];			
			$this->app_result_array[$this->curr_item_id]["id"] = $this->curr_item_id;
			return;
			break;
		}
	}

	/**
	 * 離開節點之處理函式
	 *
	 * @param   resource    $parser     SAX parser resource
	 * @param   string      $name       節點的 tag 名稱
	 */
	function endElement($parser, $name)
	{
		$this->xpath = dirname($this->xpath);

		switch ($name)
		{
			case 'response_value':
				$this->response_value = false;
				$this->response_value_count++;
				return;
				break;
			case 'response_label':
				$this->resp_label_1st_mattext = false;
				return;
				break;
			case 'item':
			case 'item_result':
				if (($name == 'item_result') && preg_match('/^PAIR\d+$/', $this->curr_lid_id)) {
					// 沒有答案時不作處理
					$itemResultImplode = ($this->user_result_array[$this->examinee][$this->curr_item_id] !== null )
							? implode(',', $this->user_result_array[$this->examinee][$this->curr_item_id])
							: $this->user_result_array[$this->examinee][$this->curr_item_id];
					// 配合題
					$this->user_result_array[$this->examinee][$this->curr_item_id] = $itemResultImplode;
				}
				$this->curr_item_id = '';
				$this->curr_lid_id = '';
				$this->item_type = 0;
				return;
				break;
		}
	}

	/**
	 * 遇到文字節點之處理函式
	 *
	 * @param   resource    $parser     SAX parser resource
	 * @param   string      $data       文字節點內容
	 */
	function characterData($parser, $data)
	{
		if (($d = trim($data)) == '') return;

		if ($this->first &&
			strpos($this->xpath, '/item/presentation/flow/material/') !== false)
		{
			if (isset($this->result_array[$this->curr_item_id]['title'])) {
				$this->result_array[$this->curr_item_id]['title'] .= $d;
				$this->app_result_array[$this->curr_item_id]['text'] .= $d;
			} else {
				$this->result_array[$this->curr_item_id]['title'] = $d;
				$this->app_result_array[$this->curr_item_id]['text'] .= $d;
			}

			return;
		}

		if ($this->resp_label_1st_mattext) {
			if ($this->qtype_array[$this->curr_item_id] == 1) {
				if (isset($this->result_array[$this->curr_item_id][$this->curr_lid_id][$this->resp_label_1st_mattext])) {
					if (isset($this->result_array[$this->curr_item_id][$this->curr_lid_id][$this->resp_label_1st_mattext]['caption'])) {
						$this->result_array[$this->curr_item_id][$this->curr_lid_id][$this->resp_label_1st_mattext]['caption'] .= htmlspecialchars($d);
						$this->app_result_array[$this->curr_item_id][$this->curr_lid_id][$this->resp_label_1st_mattext]['caption'] .= htmlspecialchars($d);
					} else {
						$this->result_array[$this->curr_item_id][$this->curr_lid_id][$this->resp_label_1st_mattext]['caption'] = htmlspecialchars($d);
						$this->app_result_array[$this->curr_item_id][$this->curr_lid_id][$this->resp_label_1st_mattext]['caption'] = htmlspecialchars($d);
					}
				}
			} else if ($this->qtype_array[$this->curr_item_id] == 4) {
				if (isset($this->result_array[$this->curr_item_id][$this->resp_label_1st_mattext])) {
					$this->result_array[$this->curr_item_id][$this->resp_label_1st_mattext]['caption'] .= htmlspecialchars($d);
					$this->app_result_array[$this->curr_item_id][$this->resp_label_1st_mattext]['caption'] .= htmlspecialchars($d);
				}
			} else {
				if (isset($this->result_array[$this->curr_item_id][$this->resp_label_1st_mattext])) {
					$this->result_array[$this->curr_item_id][$this->resp_label_1st_mattext]['caption'] = $d;
					$this->app_result_array[$this->curr_item_id][$this->resp_label_1st_mattext]['caption'] = $d;
				}
			}

		}

		if ($this->response_value)
		{
			if ($this->qtype_array[$this->curr_item_id] == 1) {
				// 是非、單選、複選、多單選
				if (!isset($this->user_result_array[$this->examinee][$this->curr_item_id][$this->curr_lid_id]))
				{
					$this->user_result_array[$this->examinee][$this->curr_item_id][$this->curr_lid_id] = $d;
				}else{
					$this->user_result_array[$this->examinee][$this->curr_item_id][$this->curr_lid_id] .= ','.$d;
				}
			} else if ($this->qtype_array[$this->curr_item_id] == 4) {
				// 配合題
				// 將資料寫到最後一個位置
				// 正常的寫法： $this->user_result_array[$this->examinee][$this->curr_item_id][ count($this->user_result_array[$this->examinee][$this->curr_item_id]) - 1 ] = $d;
				array_pop($this->user_result_array[$this->examinee][$this->curr_item_id]);
				$this->user_result_array[$this->examinee][$this->curr_item_id][] = $d;
			} else {
				//填空、簡答
				if (!isset($this->user_result_array[$this->examinee][$this->curr_item_id][$this->curr_lid_id])) {
					$this->user_result_array[$this->examinee][$this->curr_item_id][$this->curr_lid_id] = $d;
					$this->app_result_array[$this->curr_item_id][$this->curr_lid_id][$this->examinee]['caption'] = $d;
				} else {
					$this->user_result_array[$this->examinee][$this->curr_item_id][$this->curr_lid_id] .= $d;
                                        $this->app_result_array[$this->curr_item_id][$this->curr_lid_id][$this->examinee]['caption'] .= $d;
				}
			}

			if ($this->qtype_array[$this->curr_item_id] == 1) {
				$this->result_array[$this->curr_item_id][$this->curr_lid_id][$d]['count']++;
			} elseif ($this->qtype_array[$this->curr_item_id] == 4 &&
					isset($this->result_array[$this->curr_item_id][chr($this->response_value_count + 65)]['count'][$d]))
				$this->result_array[$this->curr_item_id][chr($this->response_value_count + 65)]['count'][$d]++;

			if($this->qtype_array[$this->curr_item_id] === 1 ||
					$this->qtype_array[$this->curr_item_id] === 4) {
				$this->app_result_array[$this->curr_item_id][$this->curr_lid_id][$d]["users"][] = $this->examinee;
			} else {
				$this->app_result_array[$this->curr_item_id][$this->curr_lid_id][$this->examinee]["users"][] = $this->examinee;
			}

			return;
		}

	}

	/**
	 * 進行一份答案卷的處理
	 *
	 * @param   string      $xml        答案卷之 QTI_XML
	 */
	function parse($examinee, $xml)
	{

		if($this->index_add_flag){
			//計算次數
			if( !isset($this->counterTable[$examinee]) ){
				$this->counterTable[$examinee] = 1;
			}else{
				$this->counterTable[$examinee]++;
			}
			//加入索引 (採用奇怪的索引'_@sn@'是為了避免與帳號相同)
			$this->examinee = $examinee.'_@sn@'.$this->counterTable[$examinee];
		}else{
			$this->examinee = $examinee;
		}


		$this->user_result_array[$this->examinee] = null;
		$this->init();
		if ($this->first)
			$xml = str_replace('<questestinterop', '<qti_stat_parse><questestinterop', $xml);
		else
			$xml = preg_replace(array('!<\?xml\s.*\?>!isU','!<item\s.*</item>!isU'), array('',''), $xml);

		if (!xml_parse($this->xml_parser, $xml, false))
		{
			$this->failure++;
			print(sprintf('<p>XML error: %s at line %d</p>',
				  xml_error_string(xml_get_error_code($this->xml_parser)),
				  xml_get_current_line_number($this->xml_parser)));
		}
		else
			$this->total++;

		if ($this->first) $this->first = false;
	}

	/**
	 * 答案卷處理完畢
	 */
	function endParse()
	{
		if (!xml_parse($this->xml_parser, '</qti_stat_parse>', true))
		{
			$this->failure++;
			print(sprintf('<p>XML error: %s at line %d</p>',
				  xml_error_string(xml_get_error_code($this->xml_parser)),
				  xml_get_current_line_number($this->xml_parser)));
		}
		xml_parser_free($this->xml_parser);
	}
}
?>

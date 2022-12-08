<?php
	/**
	 * Program : 匯入簡易文字格式之試題
	 *
	 * @author		Wiseguy Liang <wiseguy@mail.wiseguy.idv.tw>
	 * @since		2005-09-03
	 * @platform	Linux / Apache 1.3.33 /  PHP 4.4.0 / MySQL 4.0.25
	 * @identifier	$Id: simple_item_import.php,v 1.2 2009-07-16 10:05:52 edi Exp $
	 */

    // 僅供 include 使用，禁止直接存取本程式
    if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) die('Access Denied.');

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/' . QTI_which . '_teach.php');
	require_once(sysDocumentRoot . '/teach/exam/item_create_lib.php');

    /**
     *  直接從網路呼叫 item_create1.php 來存檔
     */
    function save_item($data)
    {
        static $headers;

		// 將 GET 資料，轉成 array
		$ary1 = explode('&', $data);
		$ary2 = array();
		foreach ($ary1 as $val) {
			$item = explode('=', $val);
			$key  = substr($item[0], -1, 2);
            $content = str_replace('&quot;' , '"', addslashes(rawurldecode($item[1])));
			if (substr($item[0], -2, 2) == '[]') {
				$key = substr($item[0], 0, -2);
				if (!array_key_exists($key, $ary2)) {
					$ary2[$key] = array();
				}
				$ary2[$key][] = $content;
			} else {
				$ary2[$item[0]] = $content;
			}
		}
		$itemCs = new itemMaintain();
		$res = $itemCs->saveItem($ary2);
		return ($res['ErrCode'] == 0);
    }

    /**
     * 把答對與答錯的訊息裁切並接在 POST 資料之後
     */
    function append_hint($str, $match_separator)
    {
        list($correct_response, $wrong_response) = explode($match_separator, $str, 2);

        return sprintf('&ans_detail=%s&hint=%s&hintEnable=1',
                       rawurlencode($correct_response),
                       rawurlencode($wrong_response)
                      );
    }


    /**
     *  ========================  程式開始  =========================
     */
    if (!is_uploaded_file($_FILES['import_file']['tmp_name']))
	{
		showXHTML_tr_B('class="cssTrEvn"');
			showXHTML_td('colspan="2" style="color: red"',  $MSG['upload_file_not_found'][$sysSession->lang]);
		showXHTML_tr_E();
		return;
	}

	// 自定分割元 begin
    $item_separator   = ($_POST['item_separator']   && $_POST['item_separator_customized']   != '') ? str_replace('\\t', chr(9), stripslashes($_POST['item_separator_customized']))   : ',';//'::'; #043988
    $ans_separator    = ($_POST['ans_separator']    && $_POST['ans_separator_customized']    != '') ? str_replace('\\t', chr(9), stripslashes($_POST['ans_separator_customized']))    : ',';
    $choice_separator = ($_POST['choice_separator'] && $_POST['choice_separator_customized'] != '') ? str_replace('\\t', chr(9), stripslashes($_POST['choice_separator_customized'])) : ' || ';
    $match_separator  = ($_POST['match_separator']  && $_POST['match_separator_customized']  != '') ? str_replace('\\t', chr(9), stripslashes($_POST['match_separator_customized']))  : ' @@ ';
    // 自定分割元 end


	//$items       = file($_FILES['import_file']['tmp_name']);
    if ($lang == 'Big5' || $lang == 'GB2312')
        file_put_contents($_FILES['import_file']['tmp_name'], mb_convert_encoding(file_get_contents($_FILES['import_file']['tmp_name']), 'UTF-8', $lang));
    $file = new SplFileObject($_FILES['import_file']['tmp_name']);
    $file->setFlags(SplFileObject::READ_CSV);
	$item_count  = array(0,0,0,0,0,0,0);
	$error_count = array(0,0,0,0,0,0,0);
	$lang        = isSet($lang) ? $lang : $sysSession->lang;
	$line1       = true;
	$i =  1;
	$error_item =array();
    foreach($file as $elements)
    //if (count($items))
      //  foreach($items as $elements)
        {

    		/*
            //	去除UTF-8的檔頭 Begin
    		if ($line1) {
				if ($lang == 'UTF-8' && strtolower(bin2hex(substr($item, 0 , 3))) == 'efbbbf')
					$item = substr($item, 3);
    			$line1 = false;
    		}
    		//	去除UTF-8的檔頭 End

    		$item = ($lang == 'Big5' || $lang == 'GB2312') ? iconv($lang, 'UTF-8', trim($item)) : trim($item);
    		
            // 「題目」欄位分隔-「自訂」時
            if ($_POST['item_separator_customized'] === '') {
                $judgeseparator = substr($item, 1, 2);
                // 擴充相容性
                // 如果偵測到第2、3個字元是雙冒號，則分隔符號改為雙冒號
                if ($judgeseparator === '::')
                    $item_separator = '::';
            }
    		
    		// 題型::答案::題目::選項::提示::分類
            // CUSTOM BY TN (B)
            $tmp_item=str_replace($item_separator,"\r",$item);
            $temp = tmpfile();
            fwrite($temp,$tmp_item);
            fseek($temp, 0);
            $elements=fgetcsv($temp,0,"\r");
            foreach($elements as $key=>$element){
                $elements[$key]=str_replace("\r",$item_separator,$element);
            }
            fclose($temp); // this removes the file
            //$elements = explode($item_separator, $item, 7);
            // CUSTOM BY TN (E)
            // 
            */
            if (empty($elements[0])) {
              continue;
            }

            if (count($elements)==1 && $_POST['item_separator_customized'] === '') {
                if (strpos($elements[0],'::')!==false) $item_separator = '::';

                $tmp_item=str_replace($item_separator,"\r",$elements[0]);
                $temp = tmpfile();
                fwrite($temp,$tmp_item);
                fseek($temp, 0);
                $elements=fgetcsv($temp,0,"\r");
                foreach($elements as $key=>$element){
                    $elements[$key]=str_replace("\r",$item_separator,$element);
                }
                fclose($temp); // this removes the file
            }
          
            $item = implode('',$elements);  
            // 如果是問卷匯入的話，答案可以不填 #1755
            if (QTI_which == 'questionnaire' &&
				in_array($elements[0], array('1', '2', '3')) &&
				$elements[1] == '')
				$elements[1] = '0';

            $hint = empty($elements[4]) ? '' : append_hint($elements[4], $match_separator);

			// 增加版冊章節段的支援
            $category = (isset($elements[5]) && preg_match('/^\d{0,9}(-\d{0,9}){0,4}$/', $elements[5])) ?
                        preg_replace('/&\w+=0\b/', '',
									 vsprintf('&version=%u&volume=%u&chapter=%u&paragraph=%u&section=%u',
									 		  array_pad(explode('-', $elements[5], 5), 5, 0))) :
						'';
			$elements[6]= trim($elements[6]);
			$item_level = (isset($elements[6]) && preg_match('/^[1-5]$/', $elements[6])) ? $elements[6] :
						'';
			
            switch(preg_replace('/\D/','',$elements[0]))
            {
                case '1':   // 是非
                    if (($elements[1] == '0' || $elements[1] == '1') && !empty($elements[2]))
                    {
		    				
						if($item_level){
								$item_level = $elements[6];
						}else
						{
								$item_level = 3;
						}
                        $data = sprintf('type=1&topic=%s&answer=%s&level='.$item_level,
                                        rawurlencode(trim($elements[2])),
                                        ($elements[1] ? 'T' : 'F')
                                       );

                        if (save_item($data . $hint . $category)) $item_count[1]++; else{
						$error_item_line[]= $i;
						$error_item[] = $item;
						$error_count[1]++;
						}
                    }
                    else{
						$error_item_line[]= $i;
						$error_item[] = $item;
                        $error_count[1]++;
					}
                    break;
                case '2':   // 單選
                    if (preg_match('/^\d+$/', $elements[1]) && !empty($elements[2]))
                    {
						$choices = explode($choice_separator, $elements[3]);
                        $amount = count($choices);
                        if ($amount > 1)
                        {
							if($item_level){
									$item_level = $elements[6];
							}else
							{
									$item_level = 3;
							}
                            $data = sprintf('type=2&topic=%s&answer=%d&level='.$item_level,
                                            rawurlencode(trim($elements[2])),
                                            $elements[1]
                                           );
						
                            foreach($choices as $choice) $data .= '&render_choices[]=' . rawurlencode(trim($choice));

                            if (save_item($data . $hint . $category)) $item_count[2]++; else
							{
						$error_item_line[]= $i;
						$error_item[] = $item;
								$error_count[2]++;
							}

                        }
                        else{
						$error_item_line[]= $i;
						$error_item[] = $item;
							$error_count[2]++;
						}
                    }
                    else{
						$error_item_line[]= $i;
						$error_item[] = $item;
                        $error_count[2]++;
					}
                    break;
                case '3':   // 複選
                    if (preg_match('/^\d+(' . preg_quote($ans_separator, '/') . '\d+)*$/', $elements[1]) && !empty($elements[2]))
                    {
                        $answers   = explode($ans_separator, $elements[1]);
                        $ans_count = count($answers);
                        $choices   = explode($choice_separator, $elements[3]);
                        $amount    = count($choices);
                        if ($amount > 1 && $ans_count > 0)
                        {
							if($item_level){
									$item_level = $elements[6];
							}else
							{
									$item_level = 3;
							}
                            $data = sprintf('type=3&topic=%s&level='.$item_level, rawurlencode(trim($elements[2]))) .
                                    vsprintf(str_repeat('&answer[]=%d', $ans_count), $answers);

                            foreach($choices as $choice) $data .= '&render_choices[]=' . rawurlencode(trim($choice));

                            if (save_item($data . $hint . $category)) $item_count[3]++; else{
						$error_item_line[]= $i;
						$error_item[] = $item;
							$error_count[3]++;
							}
                        }
                        else{
						$error_item_line[]= $i;
						$error_item[] = $item;
                            $error_count[3]++;
						}
                    }
                    else{
						$error_item_line[]= $i;
						$error_item[] = $item;
                        $error_count[3]++;
					}
                    break;
                case '4':   // 填充
                    if (!empty($elements[2]))
                    {
							if($item_level){
									$item_level = $elements[6];
							}else
							{
									$item_level = 3;
							}
                        $data = sprintf('type=4&topic=%s&level='.$item_level, rawurlencode(trim($elements[2])));

                        if (save_item($data . $category)) $item_count[4]++; else $error_count[4]++; // CUSTOM BY yea
                    }
                    else{
						$error_item_line[]= $i;
						$error_item[] = $item;
                        $error_count[4]++;
					}
                    break;
                case '5':   // 簡答
                    if (!empty($elements[2]))
                    {
							if($item_level){
									$item_level = $elements[6];
							}else
							{
									$item_level = 3;
							}
                        $data = sprintf('type=5&topic=%s&answer=%s&level='.$item_level,
                                        rawurlencode(trim($elements[2])),
                                        rawurlencode(trim($elements[1]))
                                       );
						
                        if (save_item($data . $hint . $category)) $item_count[5]++; else{
						$error_item_line[]= $i;
						$error_item[] = $item;
							$error_count[5]++;
							
						}
                    }
                    else{
						$error_item_line[]= $i;
						$error_item[] = $item;
                        $error_count[5]++;
					}
                    break;
                case '6':   // 配合
                    if ( (QTI_which == 'questionnaire' || preg_match('/^[a-zA-Z]+\{\d+\}(' . preg_quote($ans_separator, '/') . '[a-zA-Z]+\{\d+\})*$/', $elements[1]))
                        && !empty($elements[2]) && !preg_match('/^[a-zA-Z]+\{\d+\}$/', $elements[2]))
                    {
                        $choices  = explode($match_separator, $elements[3], 2);
                        $choices1 = explode($choice_separator, $choices[0]); $c1_count = count($choices1);
                        $choices2 = explode($choice_separator, $choices[1]); $c2_count = count($choices2);
                        if ($c1_count > 0 && $c2_count > 0)
                        {
							if($item_level){
									$item_level = $elements[6];
							}else
							{
									$item_level = 3;
							}
                            $data = sprintf('type=6&topic=%s&level='.$item_level, rawurlencode(trim($elements[2])));
                            // $i = 65;
                            foreach($choices1 as $choice) $data .= sprintf('&render1_choices[]=%s', rawurlencode(trim($choice)));
                            foreach($choices2 as $choice) $data .= sprintf('&render2_choices[]=%s', rawurlencode(trim($choice)));

                            if (QTI_which == 'questionnaire')
                            {
                                if (save_item($data . $hint . $category)) $item_count[6]++; else{
								$error_item_line[]= $i;
								$error_item[] = $item;
								$error_count[6]++;
								}
                                break;
                            }
                            $a = explode($ans_separator, $elements[1]); $a_count = count($a);
                            if ($a_count == $c1_count)
                            {
                                sort($a);
                                preg_match_all('/[a-zA-Z]+\{(\d+)\}/', implode($ans_separator, $a), $regs);
                                $data .= vsprintf(str_repeat('&answer[]=%d', $a_count), $regs[1]);
                                if (save_item($data . $hint . $category)) $item_count[6]++; else{ 
								$error_item_line[]= $i;
								$error_item[] = $item;
								$error_count[6]++;
								}
                            }
                            else{
								$error_item_line[]= $i;
								$error_item[] = $item;
                                $error_count[6]++;
							}
                        }
                        else{
								$error_item_line[]= $i;
								$error_item[] = $item;
                            $error_count[6]++;
						}
                    }
                    else{
						$error_item_line[]= $i;
						$error_item[] = $item;
                        $error_count[6]++;
					}
                    break;
				default:
						$error_item_line[]= $i;
						$error_item[] = $item;
						$error_count[0]++;
            }
			$i++;
        }

		
		
    showXHTML_tr_B('class="cssTrHead"');
        showXHTML_td('align="center"', $MSG['item_type'][$sysSession->lang]);
        showXHTML_td('align="center"', $MSG['import_success'][$sysSession->lang]);
        showXHTML_td('align="center"', $MSG['import_failure'][$sysSession->lang]);
    showXHTML_tr_E();

    for($i=1; $i<=6; $i++)
    {
        showXHTML_tr_B(($i & 1) ? 'class="cssTrEvn"' : 'class="cssTrOdd"');
            showXHTML_td('', $MSG['item_type' . $i][$sysSession->lang]);
            showXHTML_td('align="center"', $item_count[$i]);
            showXHTML_td('align="center"', $error_count[$i]);
        showXHTML_tr_E();
    }

    showXHTML_tr_B('class="cssTrEvn"');
        showXHTML_td('align="right"', $MSG['total'][$sysSession->lang]);
        showXHTML_td('align="center"', array_sum($item_count));
        showXHTML_td('align="center"', array_sum($error_count));
    showXHTML_tr_E();

	showXHTML_tr_B('class="cssTrHead"');
		showXHTML_td('align="center" ', '行數');
		showXHTML_td('align="center" colspan="2" ', $MSG['error_item_import'][$sysSession->lang]);
	showXHTML_tr_E();
	
	 for($i=0; $i<=count($error_item); $i++)
    {
		showXHTML_tr_B(($i & 1) ? 'class="cssTrEvn"' : 'class="cssTrOdd"');
		
		showXHTML_td('align="left"',$error_item_line[$i]);	
		showXHTML_td('align="left" colspan="2" ',$error_item[$i]);	
		showXHTML_tr_E();
	}
?>

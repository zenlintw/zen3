<?php
	/**
	 * 匯入帳號
	 * $Id: stud_import.php,v 1.1 2010/02/24 02:38:44 saly Exp $
	 **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lang/stud_import.php');
	require_once(sysDocumentRoot . '/message/collect.php');
	require_once(sysDocumentRoot . '/lib/jscalendar/calendar.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/file_api.php');

	$sysSession->cur_func = '400300500';
   	$sysSession->restore();
	if (!aclVerifyPermission(400300500, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$mail_pattern = sysDocumentRoot . "/base/$sysSession->school_id/add_account.mail";  // 信件範本
	$save_path    = sysDocumentRoot . "/base/$sysSession->school_id/door/";             // 帳號暫存檔存放位置
	$target       = $_FILES['uploads']['name'][0];                                      // 暫存檔檔名與原有名稱一致
	$loop_row     = 1000;                                                               // 分頁處理，每次處理行數
	// $insert_row = 10; // 處理到幾行後，再處理addUser中的「WM_user_account」
	$field_hash = array(	// 學生帳號匯入陣列
		'username'			=>	$MSG['username'][$sysSession->lang],		// 帳號
		'password'			=>	$MSG['password'][$sysSession->lang],		// 密碼
		'first_name'		=>	$MSG['first_name'][$sysSession->lang],		// 名
		'last_name'			=>	$MSG['last_name'][$sysSession->lang],		// 姓
		'gender'			=>	$MSG['gender'][$sysSession->lang],			// 性別
		'birthday'			=>	$MSG['birthday'][$sysSession->lang],		// 生日
		'personal_id'		=>	$MSG['personal_id'][$sysSession->lang],		// 身份證號或護照
		'email'				=>	'E-mail',			                        // E-mail
		'homepage'			=>	$MSG['homepage'][$sysSession->lang],		// homepage
		'home_tel'			=>	$MSG['home_tel'][$sysSession->lang],		// 電話 (家)
		'home_fax'			=>	$MSG['home_fax'][$sysSession->lang],		// 傳真 (家)
		'home_address'		=>	$MSG['home_address'][$sysSession->lang],	// 地址 (家)
		'office_tel'		=>	$MSG['office_tel'][$sysSession->lang],		// 電話 (公司)
		'office_fax'		=>	$MSG['office_fax'][$sysSession->lang],		// 傳真 (公司)
		'office_address'	=>	$MSG['office_address'][$sysSession->lang],	// 地址 (公司)
		'cell_phone'		=>	$MSG['cell_phone'][$sysSession->lang],		// 行動電話
		'company'			=>	$MSG['company'][$sysSession->lang],			// 公司或學校
		'department'		=>	$MSG['department'][$sysSession->lang],		// 部門或系所
		'title'				=>	$MSG['title'][$sysSession->lang],			// 職稱
		'language'			=>	$MSG['language'][$sysSession->lang]			// 語系
	);

	switch (trim($_POST['step'])){
		case '2':
			# ===================================================================================
			# 第二步 預覽匯入的資料
			# ===================================================================================
$js = <<< EOB
			function CfmSubmit(){
				var Fobj = document.preViewFm;
				var btnSmt = document.getElementById('Btn_CfmSubmit');
				btnSmt.disabled = true;
				Fobj.submit();
			}

			function PreStep(){
				var Fobj = document.preViewFm;
				Fobj.step.value = '';
				Fobj.submit();
			}

			function continue_import(val) {
				document.f3.submit();
			}
EOB;
			showXHTML_head_B($MSG['import_account'][$sysSession->lang]);
			showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
			showXHTML_script('inline', $js);
			showXHTML_head_E();
			showXHTML_body_B();
				echo "<center>\n";
				showXHTML_table_B('width="760" border="0" cellspacing="0" cellpadding="0" id="ListTable"');
					showXHTML_tr_B();
						showXHTML_td_B();
							$ary[] = array($MSG['import_account'][$sysSession->lang], 'tabs');
							showXHTML_tabs($ary, 1);
						showXHTML_td_E();
					showXHTML_tr_E();
					showXHTML_tr_B();
						showXHTML_td_B('valign="top" id="CGroup"');
							showXHTML_form_B('action="stud_import.php" method="post" enctype="multipart/form-data" ', 'preViewFm');
								showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" id="ContentList" class="cssTable"');
									showXHTML_input('hidden', 'step', '3', '', '');
									showXHTML_input('hidden', 'impfile', stripslashes($_POST['impfile']), '', '');
									showXHTML_input('hidden', 'ticket', $_POST['ticket'], '', '');
									showXHTML_input('hidden', 'PreSel', 'PreSel', '', '');
									showXHTML_input('hidden', 'lang', $_POST['lang'], '', '');
									showXHTML_input('hidden', 'begin_time', $_POST['begin_time'], '', '');
									showXHTML_input('hidden', 'expire_time', $_POST['expire_time'], '', '');
									showXHTML_input('hidden', 'sel', $_POST['sel'], '', '');
									showXHTML_input('hidden', 'enc_method', $_POST['enc_method']); // 密碼編碼方式
									$ck_cnt	= count($_POST['sel']);				// 計算點選的欄位數

									// 檔名
									$filename		= trim($_POST['impfile']);
									$lang 			= $_POST['lang'];	// 使用者匯入檔案的格式
									$fopen			= fopen($filename, 'r');

									$amount = 0;
									while($getRow=fgets($fopen, 4096)){
										$amount ++;
									}
									fclose($fopen);
									$count = ceil($amount / $loop_row);

									if ($count > 1){
										$msg_amount = str_replace(array('%total%', '%count%'),
										                          array($amount, $count),
										                          $MSG['wait_to_insert_count_more'][$sysSession->lang]);
									}
									else
										$msg_amount =  str_replace("%total%",$amount,$MSG['wait_to_insert_count_few'][$sysSession->lang]);
									showXHTML_tr_B('class="cssTrHead"');
										showXHTML_td_B('colspan="' . $ck_cnt . '"');
											echo $msg_amount, "<br /><br /><br />", $MSG['title96'][$sysSession->lang];
										showXHTML_td_E();
									showXHTML_tr_E();
									// 顯示html標題
									showXHTML_tr_B('class="cssTrHead"');
										for ($i=0; $i< $ck_cnt; $i++){			// 點選了幾個checkbox就檢查幾次
											showXHTML_td_B('align="center"');
												echo $field_hash[$_POST['sel'][$i]];
												showXHTML_input('hidden', 'chk[]', $_POST['chk'][$i], '', '');
												showXHTML_input('hidden', 'sel[]', $_POST['sel'][$i], '', '');
											showXHTML_td_E();
										}
									showXHTML_tr_E();
									// 帳號錯誤代碼
									$error_account_code = array(1,2,3,4);
									// 姓名 跟 姓氏 的長度限制
							    	$name_limit_en = 32;

									// 住址 的長度限制
							    	$addr_limit_en = 255;

									// 性別
									$gender_ary = array('F','M');
									// 語系
									$language_ary = array('Big5', 'GB2312', 'en', 'EUC-JP', 'user_define');
									$fp			  = fopen($filename, 'r');
									// read file begin & while begin
									$line1 = true;
									$this_row = 0;
									$rowArray = array();
									$i = 0;
									$account_occurreds = array();
									while($subject=fgets($fp, 4096)){						// 取得一行文字

										//	去除UTF-8的檔頭 Begin
										if ($line1) {
											if ($lang == 'UTF-8' && strtolower(bin2hex(substr($subject, 0 , 3))) == 'efbbbf')
													$subject = substr($subject, 3);
											$line1 = false;
										}
										//	去除UTF-8的檔頭 End
										$this_row ++;
										// tr begin
										showXHTML_tr_B('class="'.($bg = $bg == 'cssTrEvn'?'cssTrOdd':'cssTrEvn').'"');
										$subject_array		= explode(',',$subject);		// 分解第一行以陣列方式存放
										$subject_count		= count($subject_array);		// 計算第一行有幾個元素
										for ($i = 0; $i < $subject_count; $i++) 		    // 濾掉多餘的空白或者換行字元
											$subject_array[$i] = ($lang == 'Big5' || $lang == 'GB2312') ? iconv($lang, 'UTF-8', trim($subject_array[$i])) : trim($subject_array[$i]);
										// for begin
										$red_flag = false;
										$mark_flag = false;
										for ($i=0; $i< $ck_cnt; $i++){			// 點選了幾個checkbox就檢查幾次
											// switch begin
											switch($_POST['sel'][$i]){
												// --------------- 驗證使用者帳號欄位 ---------------
												// 驗證規則 ：	1 . '^[a-zA-Z][a-zA-Z0-9_]{3,19}$' 最少四個字元
												//				2 . 底線只能出現一次
												//				3 . 資料庫中不能有重複帳號
												case 'username':
													$user_account = $subject_array[intval($_POST['chk'][$i])];
													if (in_array($user_account, $account_occurreds))
													{
                                                        $error_no = 4;
                                                    }
													else
													{
	                                                    $error_no = checkUsername($user_account);
														$account_occurreds[] = $user_account;
													}
 													showXHTML_td_B('align="left"');
													if (in_array($error_no,$error_account_code)){
														echo '<font color="red">' . $user_account . '</font>';
														$red_flag = true;
													}else{
														echo $user_account;
													}
													showXHTML_td_E();
													break;
												// 驗證密碼欄位
												// 驗證規則 ：	1 . 使用者選擇 '未編碼'		=>	需要大於六個字元
												//				2 . 使用者選擇 '已編碼(MD5)'	=>	直接寫入資料庫
												//				3 . 使用者選擇 '由系統產生'	=>	幫使用者產生密碼欄位
												case 'password':
													$user_pwd = $subject_array[intval($_POST['chk'][$i])];

													showXHTML_td_B('align="left"');
													if (strlen($user_pwd) < 6){
														echo '<font color="red">' . $user_pwd . '</font>';
														$red_flag = true;
													}else{
														echo $user_pwd;
													}
													showXHTML_td_E();
													break;
												// 驗證名字欄位
												// 驗證規則	:	大於 16 byte的字元
												case 'first_name':
												case 'last_name':
													$user_first_name = $subject_array[intval($_POST['chk'][$i])];
													showXHTML_td_B('align="left"');
													if (strlen($user_first_name) > $name_limit_en || strlen($user_first_name) < 1){
														echo '<font color="red">' . $user_first_name . '</font>';
														$red_flag = true;
													}else{
														echo $user_first_name;
													}
													showXHTML_td_E();
													break;
												// 驗證性別欄位
												// 驗證規則	:	'[01mfMF]{1}'
												case 'gender':
													$user_gender = strtoupper($subject_array[intval($_POST['chk'][$i])]);
													showXHTML_td_B('align="left"');
													if (in_array($user_gender,$gender_ary)){
														echo $user_gender;
													}else{
														echo '<font color="red">' . $user_gender . '</font>';
														$red_flag = true;
													}
													showXHTML_td_E();
													break;
												// 驗證生日的欄位
												// 驗證規則	:	 合乎日期型態的字串
												case 'birthday':
													$user_birthday	= $subject_array[intval($_POST['chk'][$i])];
													showXHTML_td_B('align="left"');
													if (preg_match('!^\d{4}([#/-]\d{1,2}){2}$!', $user_birthday)){
														echo $user_birthday;
													}else{
														echo '<font color="red">' . $user_birthday . '</font>';
														$red_flag = true;
													}
													showXHTML_td_E();
													break;
												// 驗證身分證欄位
												// 驗證規則	:	'[a-zA-Z][12][0-9]{8}'
												case 'personal_id':
													$user_personal_id	= $subject_array[intval($_POST['chk'][$i])];
													showXHTML_td_B('align="left"');
													if (preg_match('/^\w+$/', $user_personal_id)){
														echo $user_personal_id;
													}else{
														echo '<font color="red">' . $user_personal_id . '</font>';
														$red_flag = true;
													}
													showXHTML_td_E();
													break;
												// 驗證email欄位
												// 驗證規則	:	'^[_a-z0-9]+(\.[_a-z0-9\-]+)*@[a-z0-9\-]+(\.[a-z0-9\-]+)*\.[a-z]{2,}$'
												case 'email':
												    $user_email = strtolower($subject_array[intval($_POST['chk'][$i])]);
													showXHTML_td_B('align="left"');
													if (preg_match(sysMailRule, $user_email)){
														echo $user_email;
													}else{
														echo '<font color="red">' . $user_email . '</font>';
														$red_flag = true;
													}
													showXHTML_td_E();
													break;
												// 驗證個人首頁欄位
												// 驗證規則	:	'[0-9a-zA-Z_\.]{,64}'
												case 'homepage':
												    $user_homepage = $subject_array[intval($_POST['chk'][$i])];
													showXHTML_td_B('align="left"');
													if (strlen($user_homepage) < 64){
														echo $user_homepage;
													}else{
														echo '<font color="red">' . $user_homepage . '</font>';
														$red_flag = true;
													}
													showXHTML_td_E();
													break;
												// 驗證電話(家)欄位
												// 驗證規則	:	'[0-9\-()#]{6,20}'
												case 'home_tel':
												case 'home_fax':
												case 'office_tel':
												case 'office_fax':
												   $user_home_tel = $subject_array[intval($_POST['chk'][$i])];
													showXHTML_td_B('align="left"');
													if (preg_match('/^[0-9()#-]{6,20}$/',$user_home_tel)){
														echo $user_home_tel;
													}else{
														echo '<font color="red">' . $user_home_tel . '</font>';
														$red_flag = true;
													}
													showXHTML_td_E();
													break;
												// 驗證地址(家)欄位
												// 驗證規則	:	小於 128byte 的字串
												case 'home_address':
												case 'office_address':
												case 'company':
												case 'department':
												   $user_home_address = $subject_array[intval($_POST['chk'][$i])];
												    showXHTML_td_B('align="left"');
													if (strlen($user_home_address) > $addr_limit_en){
														echo '<font color="red">' . $user_home_address . '</font>';
														$red_flag = true;
													}else{
														echo $user_home_address;
													}
													showXHTML_td_E();
													break;
												// 驗證行動電話欄位
												// 驗證規則	:	'[0-9-]{6,20}'
												case 'cell_phone':
												   $user_cell_phone = $subject_array[intval($_POST['chk'][$i])];
													showXHTML_td_B('align="left"');
													if (preg_match('/^[0-9()#-]{6,20}$/',$user_cell_phone)){
														echo $user_cell_phone;
													}else{
														echo '<font color="red">' . $user_cell_phone . '</font>';
														$red_flag = true;
													}
													showXHTML_td_E();
													break;
												// 驗證職稱欄位
												// 驗證規則	:	小於 32byte 的字串
												case 'title':
												   $user_title = $subject_array[intval($_POST['chk'][$i])];
													showXHTML_td_B('align="left"');
													if (strlen($user_title)> $addr_limit_en){
														echo '<font color="red">' . $user_title . '</font>';
														$red_flag = true;
													}else{
														echo $user_title;
													}
													showXHTML_td_E();
													break;
												// 驗證語系欄位
												// 驗證規則	:	是否為系統允許語系
												case 'language':
													$language = $subject_array[intval($_POST['chk'][$i])];
													showXHTML_td_B('align="left"');
													if (!in_array($language, $language_ary)){
														echo '<font color="red">' . $language . '</font>';
														$red_flag = true;
													}else{
														echo $language;
													}
													showXHTML_td_E();
													break;
											}
											// switch end
											// tr end
											if ($red_flag && !$mark_flag){
												$rowString .= $this_row.',';
												$mark_flag = true;
												continue;
											}
										}

										// for end
										showXHTML_tr_E();
										// tr end
									}
									// read file end & while end
									// button
									$rowString = substr($rowString,0,strlen($rowString)-1);
									if (strlen($rowString)>0)
                                        $tempArray = explode(',',$rowString);
									// echo $rowString;
									showXHTML_input('hidden', 'rowString', $rowString, '', '');
									showXHTML_input('hidden', 'total_amount', $amount, '', '');
									// 如果沒有資料可以匯入，則將『確定匯入』的button disable掉
									(count($tempArray) == $amount)? $disable=' disabled' : $disable='';
									showXHTML_tr_B('class="'.($bg = $bg == 'cssTrEvn'?'cssTrOdd':'cssTrEvn').'"');
										showXHTML_td_B('align="center" colspan="' . $ck_cnt . '"');
											showXHTML_input('button', '', '  '.$MSG['title97'][$sysSession->lang].'  ', '', 'class="cssBtn" onclick="PreStep()"');
											showXHTML_input('button', '', '  '.$MSG['title98'][$sysSession->lang].'  ', '', 'id="Btn_CfmSubmit" class="cssBtn" onclick="CfmSubmit()"'.$disable);
											showXHTML_input('button', '', '  '.$MSG['cancel'][$sysSession->lang].'  ', '', 'id="Btn_CfmSubmit" class="cssBtn" onclick="continue_import()"');
										showXHTML_td_E('');
									showXHTML_tr_B('');
							showXHTML_form_E();
						showXHTML_td_E();
					showXHTML_tr_E();
				showXHTML_table_E();
				echo "</center>\n";

				showXHTML_form_B('action="stud_account.php" method="post"', 'f3');
					showXHTML_input('hidden', 'msgtp', '3', '', '');
					showXHTML_input('hidden', 'delimport', 'delimport', '', '');
					showXHTML_input('hidden', 'impfile', stripslashes($_POST['impfile']), '', '');
				showXHTML_form_E('');
			showXHTML_body_E();

			break;

		case '3':

			# ===================================================================================
			# 第三步,確定匯入db內
			# ===================================================================================


			//  設定帳號的使用期限
			if (empty($_POST['begin_time'])){
				$begin_time = 'NULL';
			}else{
				$begin_time = "'" .  $_POST['begin_time'] . "'";
			}

			if (empty($_POST['expire_time'])){
				$expire_time = 'NULL';
			}else{
				$expire_time = "'" .  $_POST['expire_time'] . "'";
			}

			$rowString   = trim($_POST['rowString']);
			$all_amount  = trim($_POST['total_amount']);
			$cnt         = count($_POST['sel']);              // 計算點選的欄位數
			$filename    = trim($_POST['impfile']);           // 檔名
			$lang        = $_POST['lang'];                    // 使用者匯入檔案的格式
			$fp          = fopen($filename, 'r');
			$pw_gen_sel  = $_POST['enc_method'];              // 密碼產生方式
			$rowArray    = explode(",",$rowString);
			$ck_cnt      = count($_POST['sel']);
			// $loop_row = 30;

			$sysCL = array('Big5'=>'zh-tw','en'=>'en','GB2312'=>'zh-cn');
	        $ACCEPT_LANGUAGE = $sysCL[$sysSession->lang];
            if (empty($ACCEPT_LANGUAGE)) $ACCEPT_LANGUAGE = 'zh-tw';

			$js = <<< EOB

			var html_lang = "{$ACCEPT_LANGUAGE}";

			/* 如果是 Mozilla/Firefox 則加上 outerHTML/innerText 的支援 */
			if (navigator.userAgent.indexOf(' Gecko/') != -1)
			{
				HTMLElement.prototype.__defineSetter__('outerHTML', function(s){
				   var range = this.ownerDocument.createRange();
				   range.setStartBefore(this);
				   var fragment = range.createContextualFragment(s);
				   this.parentNode.replaceChild(fragment, this);
				});

				HTMLElement.prototype.__defineGetter__('outerHTML', function() {
				   return new XMLSerializer().serializeToString(this);
				});

				HTMLElement.prototype.__defineGetter__('innerText', function() {
				  return this.innerHTML.replace(/<[^>]+>/g, '');
				});
			}

			function continue_import(val) {
				document.f3.submit();
			}

			function mailData(){
                var ml = '';
                var ss = '/,$/';
                var obj = document.getElementById('addTable1');
                var col = '', tmp = '';

                /*
                   寄信給管理者備存 (begin)
                */

                tmp = '<html>'+
                      '<head>'+
                      '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >'+
                      '<meta http-equiv="Content-Language" content="' + html_lang + '" > '+
                      '<title>' + "{$actMsg}" + '</title>'+
                      '<style type="text/css">'+
                      '.cssTrHead {' +
                      '  font-size: 12px; ' +
                      ' line-height: 16px; '+
                      ' text-decoration: none; ' +
                      ' letter-spacing: 2px; '+
                      ' color: #000000; ' +
                      ' background-color: #CCCCE6; ' +
                      ' font-family: Tahoma, "Times New Roman", Times, serif;' +
                      ' }' +
                      ' .cssTrEvn { '+
                      '  	font-size: 12px;'+
                      '  	line-height: 16px;'+
                      '  	text-decoration: none;'+
                      '  	letter-spacing: 2px;'+
                      '  	color: #000000;'+
                      '  	background-color: #FFFFFF;'+
                      '  	font-family: Tahoma, "Times New Roman", Times, serif;'+
                      '}'+
                      '  .cssTrOdd {'+
                      '  	font-size: 12px;'+
                      '  	line-height: 16px;'+
                      '  	text-decoration: none;'+
                      '  	letter-spacing: 2px;'+
                      '  	color: #000000;'+
                      '  	background-color: #EAEAF4;'+
                      '  	font-family: Tahoma, "Times New Roman", Times, serif;'+
                      '  }'+
                      '.font01 {' +
                      'font-size: 12px;' +
                      'line-height: 16px;' +
                      'color: #000000; ' +
                      'text-decoration: none ;'+
                      'letter-spacing: 2px;'+
                      '}' +
                      '</style>'+
                      '</head>' +
                      '<body  >'+
                      obj.outerHTML +
               		  '</' + 'body>'+
               		  '</html>';

               /*
                  寄信給管理者備存 (end)
               */

			   document.mailFm.mail_txt.value = tmp;
               document.mailFm.submit();

           }

           function btn_disable(){
               var btn_continue_top = document.getElementById('btn_continue_top');
               var btn_continue_bot = document.getElementById('btn_continue_bot');
               // var frmImport = document.getElementById('frmImport');
               btn_continue_top.disabled = true;
               btn_continue_bot.disabled = true;
               // frmImport.submit();
           }
EOB;
			showXHTML_head_B($MSG['import_account'][$sysSession->lang]);
			showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
			showXHTML_script('inline', $js);
			showXHTML_head_E();
			showXHTML_body_B();
			$this_row = 0;
			$error_amount = 0;
			$start_row = trim($_POST['start_row']);

			$already = $_POST['start_row'] ? intval($_POST['start_row']) + intval($loop_row) : intval($loop_row);
			$remain = $all_amount - $already;

			$msg_import = str_replace(array('%already%', '%remain%'),
			                          array($already   , $remain),
			                          $MSG['already_insert_wait_to_insert'][$sysSession->lang]);

			$loop_import = 0;
			$loop_exclude = 0;
			$lb = $start_row;
			$ub = $start_row + $loop_row;
			for ($i=0 ;$i<count($rowArray) ; $i++){
				if (($rowArray[$i]>$lb) && ($rowArray[$i]<=$ub)){
					$loop_exclude ++;
				}
			}
			if ($remain >= 0){
				$loop_import = $loop_row - $loop_exclude;
			}
			else{
				$loop_import = ($all_amount%$loop_row) - $loop_exclude;
			}

			$msg_loop_import = str_replace(array('%loop_import%', '%loop_exclude%'),
			                               array($loop_import   , $loop_exclude),
			                               $MSG['already_import_amount'][$sysSession->lang]);

			$arry[] = array($MSG['import_account'][$sysSession->lang], 'addTable1');
			showXHTML_form_B('action="stud_import.php" method="post" onsubmit="btn_disable();"', 'f1');
				showXHTML_table_B('width="500" border="0" cellspacing="0" cellpadding="0"');
					showXHTML_tr_B();
						showXHTML_td_B();
							showXHTML_tabs($arry, 1);
						showXHTML_td_E();
					showXHTML_tr_E();
				showXHTML_table_E();
				showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" id="addTable1" class="cssTable"');
					if ($remain > 0){
						showXHTML_tr_B('class="cssTrHead"');
							($_POST['enc_method'] == 3)? $colspan = 'colspan="4"' : $colspan = 'colspan="3"';
							showXHTML_td_B($colspan);
								echo $msg_loop_import, '<br>', $msg_import;
								showXHTML_input('submit', 'btn_continue_top' ,$MSG['btn_continue_insert'][$sysSession->lang], '', ' id="btn_continue" name="btn_continue_top" class="cssBtn" onclick=""');
							showXHTML_td_E();
						showXHTML_tr_E();
					}
					else{
						showXHTML_tr_B('class="cssTrHead"');
							($_POST['enc_method'] == 3)? $colspan = 'colspan="4"' : $colspan = 'colspan="3"';
							showXHTML_td($colspan, $msg_loop_import);
						showXHTML_tr_E();
					}
					showXHTML_tr_B('class="cssTrHead"');
						showXHTML_td('width="40%" align="center"', $MSG['username'][$sysSession->lang]);
                        //  密碼是由系統產生 則將密碼印出來
                        if ($_POST['enc_method']=='3'){
						    showXHTML_td('width="14%" align="center"', $MSG['password'][$sysSession->lang]);
                        }
						showXHTML_td('width="14%" align="center"', $MSG['pass'][$sysSession->lang]);
						showXHTML_td('width="40%" align="center"', $MSG['info'][$sysSession->lang]);
					showXHTML_tr_E();

			// ========== 1.先讀取檔案中的原始信件檔案(每封信件共用資訊) ==========
			if (file_exists($mail_pattern)){
				$fd = fopen ($mail_pattern, "r");
				// 信件標題
				$tmp_subject = fgets($fd, 1024);
				// 讀取信件內文
				while (!feof ($fd)) {
					$buffer = fgets($fd, 4096);
					// $tmp_body 為尚未置換特殊符號的本文
					$tmp_body .= $buffer;
				}
				fclose($fd);
			}else{
				// 信件標題
				$tmp_subject = $MSG['add_account_subject'][$sysSession->lang];
				// 信件內文
				$tmp_body = $MSG['add_account_body'][$sysSession->lang];
			}
			// ==================== 結合信件的部分內容 ======================
			$school_name	   = $sysSession->school_name;			// 學校名稱
			$school_host	   = $_SERVER['HTTP_HOST'];				// 學校網址
			list($school_mail) = dbGetStSr('WM_school','school_mail',"school_id='{$sysSession->school_id}' and school_host='{$_SERVER['HTTP_HOST']}'", ADODB_FETCH_NUM);

			// 寄件者
			if (empty($school_mail)){
				$school_mail = 	'webmaster@'. $school_host;
			}
			$from = mailEncFrom($school_name ,$school_mail);

			// ================ 取出信件夾檔名稱(每封信件共用資訊) ================
			$att_file_path	= sysDocumentRoot . "/base/$sysSession->school_id/attach/add_account";		// 夾擋路徑
			if (is_dir($att_file_path)){
				// 取得所有附加檔案名稱
				$att_files		= getAllFile($att_file_path);
			}

			# ===================================================================================
			#
			# 一次讀取一行匯入帳號，驗證每攔資料的合法性
			# 如果符合各項欄位標準，則加入資料庫，並寄發通知信件
			#
			// $rtn_to_webmaster	=> 回寄給 webmaster 的訊息
			$rtn_to_webmaster	= $MSG['username'][$sysSession->lang] . ',' . $MSG['password'][$sysSession->lang] . ',' . $MSG['info'][$sysSession->lang] . "\n";

			// 姓名 跟 姓氏 的長度限制
	    	$name_limit_en = 32;

			// 住址 的長度限制
	    	$addr_limit_en = 255;

			// 性別
			$gender_array = array('M','F');
			// 語系
			$language_ary = array('Big5', 'GB2312', 'en', 'EUC-JP', 'user_define');

			// 帳號註冊上限：設定可註冊人數
	        if (sysMaxUser > 0)
	        {
	            list($now_maxuser) = dbGetStSr('WM_user_account','count(*)','1', ADODB_FETCH_NUM);
	            if ($now_maxuser >= sysMaxUser)
	            {
	                $canRegisterNum = 0;
	            }else{
	                $canRegisterNum = sysMaxUser - $now_maxuser;
	            }
	            $msg_overMaxUser = str_replace(array('%max_register_user%', '%admin_email%'),
	                                           array(sysMaxUser           , 'mailto:' . $school_mail),
	                                           $MSG['overMaxUser'][$sysSession->lang]);
	        }

	        // 找出username所在的欄位
	        $username_field_index = -1;
	        for($i=0; $i<count($_POST['sel']); $i++)
	        {
	            if ($_POST['sel'][$i] == 'username')
	            {
	                $username_field_index = $i;
	            }
	        }
	        $overMaxUser = false;   //是否超過可註冊人數
			$line1 = true;       //第一行的旗標
			$count = 0;

			// 在一開始做一個time_stamp作為檔案名稱的區隔 by Small 2006/11/15
			$time_stamp = time();

			// 本來是在迴圈內，現在拿到迴圈外，避免迴圈內每次都去判斷有沒有該sql檔 by Small 2006/11/23
			if (!file_exists(sysDocumentRoot . "/base/{$sysSession->school_id}/account_{$time_stamp}.sql")){
				$res_no = addUser('','','','','begin',$time_stamp);
			}
			while ($subject=fgets($fp, 4096))
			{
				$this_row++;
				// 判斷是不是有錯誤資料的那一行，或是在小於「繼續匯入」的行數
				// 如果是，則跳過不處理；不是的話就處理
				// if (((in_array($this_row,$rowArray)) or ($this_row <= $start_row)) and ($this_row != $all_amount)){
				if (($this_row <= $start_row) and ($this_row != $all_amount)){
					continue;
				}
				else{
					// echo $this_row.'<br><br>';
					//	去除UTF-8的檔頭 Begin
					if ($line1) {
						if ($lang == 'UTF-8' && strtolower(bin2hex(substr($subject, 0 , 3))) == 'efbbbf')
							$subject = substr($subject, 3);
						$line1 = false;
					}
					//	去除UTF-8的檔頭 End
					$data   = array();
					$subject_array		= explode(',',$subject);			// 分解第一行以陣列方式存放
					$subject_count		= count($subject_array);		// 計算第一行有幾個元素

					for ($i = 0; $i < $subject_count; $i++) 			// 濾掉多餘的空白或者換行字元
						$subject_array[$i] = ($lang == 'Big5' || $lang == 'GB2312') ? iconv($lang, 'UTF-8', trim($subject_array[$i])) : trim($subject_array[$i]);

					// 驗證是否達到帳號註冊上限
					if (sysMaxUser > 0)   //有註冊人數限制
					{
						--$canRegisterNum;    // 取得目前可註冊人數
						if ($canRegisterNum < 0)
						{
							$overMaxUser = true;
						}
					}

					if ($overMaxUser)  //已超過註冊上限
					{
						showXHTML_tr_B('class="'.($bg = $bg == 'cssTrEvn'?'cssTrOdd':'cssTrEvn').'"');
						$user_account = ($username_field_index != -1)?$subject_array[$username_field_index]:'';
						showXHTML_td('align="center"', $user_account);
						if ($_POST['enc_method']=='3') showXHTML_td('align="center"', '--');
						showXHTML_td_B(' align="center"');
						echo '<img src="/theme/' . $sysSession->theme . '/academic/icon_wrong.gif" width="23" height="20" border="0" align="absmiddle">';
						showXHTML_td_E();
						showXHTML_td('align="center"', $msg_overMaxUser);
						showXHTML_tr_E();
						continue;
					}

					// 因為已經在第二步就已經檢查過，所以，這邊就直接組合成要insert的資料
					// ========== 組成 SQL insert 的 fields 的欄位 ==========
                    for ($i=0; $i< $cnt; $i++){
                        $data[$_POST['sel'][$i]] = '';
					}

					// 若密碼欄位由系統產生，自動加入 password 欄位名稱
					if ($_POST['enc_method']=='3'){
				        $data['password'] = '';
				    }

					$email_flag = false;
					if (!in_array($this_row,$rowArray)){
						showXHTML_tr_B('class="'.($bg = $bg == 'cssTrEvn'?'cssTrOdd':'cssTrEvn').'"');
						// ========== 組成 SQL insert 的 values 的欄位 ==========
						for ($i=0; $i< $cnt; $i++){
							switch($_POST['sel'][$i]){
								case 'username':
									showXHTML_td('align="center"', $subject_array[intval($_POST['chk'][$i])]);
									$user_account		= trim($subject_array[intval($_POST['chk'][$i])]);
									// echo $user_account;
									break;
								case 'password':
									switch($_POST['enc_method']){
										case '1':		// 未編碼		=> 用MD5的方式將使用者的密碼加密
											$user_pwd = trim($subject_array[intval($_POST['chk'][$i])]);
											$data['password'] = md5($user_pwd);
											break;
										case '2':		// 已編碼(MD5)	=> 直接寫入資料庫(不處理)
											$user_pwd = trim($subject_array[intval($_POST['chk'][$i])]);
											$data['password'] = $user_pwd;
											break;
									}
									break;
								case 'email':
									$data[$_POST['sel'][$i]] = $subject_array[intval($_POST['chk'][$i])];
									$user_email = strtolower(trim($subject_array[intval($_POST['chk'][$i])]));
									$email_flag = true;
									break;
								case 'first_name':
								case 'last_name':
								case 'home_address':
								case 'office_address':
								case 'company':
								case 'department':
								case 'title':
								default:
									$data[$_POST['sel'][$i]] = addslashes($subject_array[intval($_POST['chk'][$i])]);
									break;
							}
						}

						if ($_POST['enc_method'] == '3') // 密碼由系統產生
						{
							$user_pwd = Passwd();
							$data['password'] = md5($user_pwd);
							showXHTML_td(' align="center"', $user_pwd);
						}

						// 帳號起始時間、終止時間
						$data['begin_time'] = $begin_time;
						$data['expire_time'] = $expire_time;

						$res_no = addUser($user_account, $data,'','','middle',$time_stamp);

						//-------------------
						// 1.信件標題	 => 網路學園帳號啟用通知信+學校名稱
						if ($email_flag){
							$subject	= $tmp_subject;

							// 4.信件本文處理
							$mailbody = str_replace(array('%SCHOOL_NAME%', '%SCHOOL_HOST%', '%USERNAME%', '%PASSWORD%'),
							                        array($school_name   , $school_host   , $user_account  , $user_pwd),
							                        $tmp_body);

							// =============== 結合信件的各部分內容 =================
							// 0.每次進入都必須重新宣告一個新的 mail 類別
							$mail = buildMail('', $subject, $mailbody, 'html', '', '', '', '', false);

							// 2.寄件人email	=> webmaster@學校domain
							$mail->from		= $from;
							// 3.收件人email	=> 學員email
							$mail->to		= $user_email;

							// 5.信件夾檔
							// ========== 處理附加檔案 ==========
							//	信件夾檔需要開檔後
							//  將檔案依序加入 $mail->add_attachment() 中

							$att_count		= count($att_files);
							for ($j=0; $j< $att_count; $j++){
								$attach		= $att_file_path . DIRECTORY_SEPARATOR . $att_files[$j];

								$data = file_get_contents($attach);
								$mail->add_attachment($data,$att_files[$j]);
							}

							// 6.寄出信件
							$mail->send();
							wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 0, 'manager', $_SERVER['PHP_SELF'], '寄出信件:'.$user_email);
						}


							$account_add = true;
							$verify_flag = true;
							$imgfile = $account_add?'icon_currect.gif':'icon_wrong.gif';
							showXHTML_td_B(' align="center"');
								echo '<img src="/theme/' . $sysSession->theme . '/academic/' . $imgfile . '" width="23" height="20" border="0" align="absmiddle">';
							showXHTML_td_E('');
							// 顯示提示訊息
							$verify_info = ereg_replace(',$','',$verify_info);
							if ($account_add){				// 資料進入資料庫
								if (!$verify_flag){			// 有格式不正確的欄位
									showXHTML_td('',$verify_info);
								}else{						// 所有欄位資料都正確
									showXHTML_td('',$MSG['import_succeed'][$sysSession->lang]);
								}
							}
							else{							// 資料沒有進入資料庫
								if (!$verify_account){		// 帳號重複
									showXHTML_td('',$MSG['account_double'][$sysSession->lang]);
								}elseif (!$verify_flag)		// 帳號欄位不正確
									showXHTML_td('',$verify_info);
							}
						showXHTML_tr_E();
					}
					else if (in_array($this_row,$rowArray)){
						showXHTML_tr_B('class="'.($bg = $bg == 'cssTrEvn'?'cssTrOdd':'cssTrEvn').'"');
							$user_account	 = trim($subject_array[intval('username')]);
							showXHTML_td('align="center"', $user_account);

						if ($_POST['enc_method'] == 3){
							$user_pwd = Passwd();
							showXHTML_td(' align="center"', $user_pwd);
							$data['password'] = $user_pwd;
						}

						$account_add = false;
						$verify_flag = false;
						$imgfile = $account_add?'icon_currect.gif':'icon_wrong.gif';
						showXHTML_td_B(' align="center"');
							echo '<img src="/theme/' . $sysSession->theme . '/academic/' . $imgfile . '" width="23" height="20" border="0" align="absmiddle">';
						showXHTML_td_E();
						// 顯示提示訊息
						showXHTML_td('',$MSG['error_data'][$sysSession->lang]);
					showXHTML_tr_E();
					}
					echo '<script>window.status="', str_repeat('|', $this_row >> 1), ' ', round($this_row/$loop_import, 2)*100, '%";</script>';
					flush();
				}
				echo '<script>window.status="";</script>';
				// 是不是最後一筆？ by Small
				$final = false;
				($this_row == $all_amount)? $final = true: $final=false;
				if ($final){
					$res_no = addUser('','','','','final',$time_stamp);
				}

				// 是不是到了每一個循環的最後一筆 by Small
				$stop = (($this_row % $loop_row == 0)  and ($this_row< $all_amount));
				if ($stop){
					$res_no = addUser('','','','','final',$time_stamp);
					showXHTML_tr_B();
						showXHTML_td_B();
							showXHTML_input('hidden', 'rowString'   , $rowString, '', '');
							showXHTML_input('hidden', 'start_row'   , $this_row, '', '');
							showXHTML_input('hidden', 'total_amount', $all_amount, '', '');
							showXHTML_input('hidden', 'step'        , '3', '', '');
							showXHTML_input('hidden', 'impfile'     , stripslashes($_POST['impfile']), '', '');
							showXHTML_input('hidden', 'PreSel'      , 'PreSel', '', '');
							showXHTML_input('hidden', 'lang'        , $_POST['lang'], '', '');
							showXHTML_input('hidden', 'ticket'      , $_POST['ticket'], '', '');
							showXHTML_input('hidden', 'begin_time'  , $_POST['begin_time'], '', '');
							showXHTML_input('hidden', 'expire_time' , $_POST['expire_time'], '', '');
							showXHTML_input('hidden', 'enc_method'  , $_POST['enc_method']); // 密碼編碼方式
							for ($i=0; $i< $ck_cnt; $i++){			// 點選了幾個checkbox就檢查幾次
								showXHTML_input('hidden', 'chk[]', $_POST['chk'][$i], '', '');
								showXHTML_input('hidden', 'sel[]', $_POST['sel'][$i], '', '');
							}
						showXHTML_td_E();
					showXHTML_tr_E();
					showXHTML_tr_B('class="cssTrHead"');
						($_POST['enc_method'] == 3)? $colspan = 'colspan="4"' : $colspan = 'colspan="3"';
						showXHTML_td_B($colspan);
							echo $msg_import;
							showXHTML_input('submit', 'btn_continue_bot' ,$MSG['btn_continue_insert'][$sysSession->lang], '', ' id="btn_continue_bot" name="btn_continue" class="cssBtn" onclick=""');
						showXHTML_td_E();
					showXHTML_tr_E();
					die();
				}
			}

			wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 0, 'manager', $_SERVER['PHP_SELF'], $MSG['import_account'][$sysSession->lang] . ' : ' . $log_msg);
			fclose($fp);
			// 刪除上傳的檔案
			@unlink($_POST['impfile']);

			// 4.刪除附加檔案
			$filename	=	$save_path . $MSG['backup2'][$sysSession->lang];
			@unlink($rtn_mail_name);

					showXHTML_tr_B('class="cssTrHead"');
						showXHTML_td_B(' colspan="4" align="center"');

							//  密碼是由系統產生 則將 顯示 寄給管理者備存
                            //if ($_POST['enc_method']=='3'){
                                showXHTML_input('button', '', '  '.$MSG['mail_backup'][$sysSession->lang].'  ', '', 'class="cssBtn" onclick="mailData()"');
						    //}

							showXHTML_input('button', '', '  '.$MSG['return_backup'][$sysSession->lang].'  ', '', 'class="cssBtn" onclick="continue_import()"');
						showXHTML_td_E();
					showXHTML_tr_E();
				showXHTML_table_E();
			showXHTML_form_E();
			showXHTML_form_B('action="verify_mail.php" method="post" target="_blank"', 'f2');
				showXHTML_input('hidden', 'dela_pages', 'import', '', '');
			showXHTML_form_E();
			showXHTML_form_B('action="stud_account.php" method="post"', 'f3');
				showXHTML_input('hidden', 'msgtp', '3', '', '');
			showXHTML_form_E();
			// 寄信給管理者備存
			showXHTML_form_B('action="send_adm_regmail.php" method="post" enctype="multipart/form-data" style="display:none" onsubmit="return mailData()"', 'mailFm');
			    $ticket2 = md5($sysSession->ticket . 'sendMail' . $sysSession->school_id . $sysSession->school_name . $sysSession->username);
			    showXHTML_input('hidden', 'ticket', $ticket2, '', '');
			    showXHTML_input('hidden', 'mail_txt', '', '', '');
			showXHTML_form_E();
			showXHTML_body_E();
			break;

		default:
			# ===================================================================================
			# 第一次進來自動進入第一步
			# ===================================================================================

			$ticket = md5($sysSession->ticket . 'AddImport' . $sysSession->school_id . $sysSession->username);
			if (!trim($_POST['ticket']) == $ticket)	die($MSG['title95'][$sysSession->lang]);

			while(list($k, $v) = each($field_hash))
				$fielda .= "<option value=\"$k\">$v</option>";
			// 檔名
			if ($_POST['PreSel'] != ''){
				$filename = $_POST['impfile'];
    		}else{
    			if (is_file($save_path.$target)) @unlink($save_path.$target);// 如果上次未完成上傳動作離線，先清除上次同名檔案
				if ($_FILES['cvsfile']['name'] == ''){
					echo "<script language='javascript'>alert('" . $MSG['must_select_filename'][$sysSession->lang] . "');</script>";
					echo "<script language='javascript'>location.replace('stud_account.php?msgtp=3')</script>";
				}
    			$filename = tempnam(dirname($_FILES['cvsfile']['tmp_name']), 'impf');
    			rename($_FILES['cvsfile']['tmp_name'], $filename);
    		}
    		$lang = ($_POST['file_format'] ? $_POST['file_format'] : $sysSession->lang);	// 設定匯入檔案所使用的語系
			// 讀取檔案
			$fp				= fopen($filename, 'r');
			$subject		= fgets($fp, 4096);			// 第一行文字的部分
			fclose($fp);

			//	去除UTF-8的檔頭 Begin
			if ($lang == 'UTF-8' && strtolower(bin2hex(substr($subject, 0 , 3))) == 'efbbbf')
				$subject = substr($subject, 3);
			//	去除UTF-8的檔頭 End

			$subject_array	= explode(',',$subject);		// 分解第一行以陣列方式存放
			$subject_count	= count($subject_array);	// 計算第一行有幾個元素

			# ===================================================================================
			# Javascript 程式碼開始
			# ===================================================================================

			$js = <<< EOB

			function Switch_sel(i,status){
				 document.f1.elements[i*2+5].disabled = !status;
			}

			function display(){
				var obj = document.getElementsByTagName('select');
				var tr, cellIdx;
				for(i=0; i<obj.length-1; i++){
					obj[i].selectedIndex = Math.min(obj[i].options.length,i);
					cellIdx = obj[i].parentNode.cellIndex;
					tr = obj[i].parentNode.parentNode;
					obj[i].disabled = !tr.cells[cellIdx-1].getElementsByTagName('input')[0].checked;
				}

			}

			function check_field(){
				var obj = null;
				var sdate = '', edate = '';
			    // date field
			    if (document.forms[0].ck_begin_date.checked){
                	obj = document.getElementById("begin_time");
                	sdate = obj.value;
                }
                if (document.forms[0].ck_end_date.checked){
                	obj = document.getElementById("expire_time");
                	edate = obj.value;
                }
                if ((sdate.length > 0) && (edate.length > 0)){
                	if (sdate >= edate){
            			alert("{$MSG['title88'][$sysSession->lang]}");
            	    	return false;
            		}
            	}
			    //  field data
				var xx			= new Array(25);
				var x			= 0;
				var uid_flag	= false;
				var pwd_flag	= false;
				var obj			= document.forms[0].elements;
				var enc_mth		= document.f1.enc_method.value;
				// ===== 判別是否有重複的欄位被選取 =====
				for(i=4; i<obj.length; i+=2){
					if (obj[i].type == 'checkbox' && obj[i].checked){
					    xx[x] = obj[i+1].selectedIndex;
						for(j=0; j<x; j++){
							if (xx[x] == xx[j]){
								alert('{$MSG['dup_fields'][$sysSession->lang]}');
								return false;
							}
						}
						x++;
					}
				}

				// ===== 如果選擇由系統產生，則不可以勾選密碼欄位 =====
				for (var k=0; k<x; k++){
					if (enc_mth==3 && xx[k]=='1'){
						alert('{$MSG['msg_pw_sysgen'][$sysSession->lang]}');
						return false;
					}
					if (xx[k]=='0')
						uid_flag = true;
					if (xx[k]=='1')
						pwd_flag = true;
				}
				// ===== 判別帳號欄位是否有被選取 =====
				if (!uid_flag){
					alert('{$MSG['must_account'][$sysSession->lang]}');
					return false;
				}
				// ===== 判別密碼欄位是否有被選取 =====
				if (!pwd_flag && (enc_mth!=3)){
					alert('{$MSG['must_pwd'][$sysSession->lang]}');
					return false;
				}

				return true;
			}

			function cancel() {
				document.f3.submit();
			}

			// 秀日曆的函數(checkbox)
			function showDateInput(objName, state) {
				var obj = document.getElementById(objName);
				if (obj != null) {
					obj.style.display = state ? "" : "none";
				}
			}

			// 秀日曆的函數
			function Calendar_setup(ifd, fmt, btn, shtime) {
				Calendar.setup({
					inputField  : ifd,
					ifFormat    : fmt,
					showsTime   : shtime,
					time24      : true,
					button      : btn,
					singleClick : true,
					weekNumbers : false,
					step        : 1
				});
			}

			function Onload_date() {
				Calendar_setup("begin_time", "%Y-%m-%d", "begin_date", false);
				Calendar_setup("expire_time"  , "%Y-%m-%d", "end_date"  , false);
			};
EOB;
				showXHTML_head_B($MSG['import_account'][$sysSession->lang]);
				showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
				// 產生並載入萬年曆的物件，並且設定所需的語系
				$calendar = new DHTML_Calendar('/lib/jscalendar/', $sysSession->lang, 'calendar-system');
				$calendar->load_files();
				showXHTML_script('inline', $js);
				showXHTML_head_E();
				showXHTML_body_B('onload="Onload_date(); display();"');
				$arry[] = array($MSG['import_account'][$sysSession->lang], 'addTable1');
				showXHTML_form_B('action="stud_import.php" method="post" onsubmit="return check_field()"', 'f1');
					showXHTML_table_B('width="400" border="0" cellspacing="0" cellpadding="0"');
						showXHTML_tr_B();
							showXHTML_td_B();
								showXHTML_tabs($arry, 1);
							showXHTML_td_E();
						showXHTML_tr_E();
					showXHTML_table_E();
					showXHTML_table_B('width="400" border="0" cellspacing="1" cellpadding="3" id="addTable1" class="cssTable"');
						showXHTML_tr_B('class="cssTrHead"');
							showXHTML_td(' colspan="4"', $MSG['select_fields'][$sysSession->lang].'<br>'.$MSG['msg_notice_pw'][$sysSession->lang]);
						showXHTML_tr_E();

						showXHTML_tr_B('class="'.($bg = $bg == 'cssTrEvn'?'cssTrOdd':'cssTrEvn').'"');
						    showXHTML_td_B('colspan="4"');
						    	echo $MSG['first'][$sysSession->lang];
								showXHTML_input('checkbox', 'ck_begin_date', 'begin_date', '', 'id="ck_begin_date" onclick="showDateInput(\'span_begin_time' . '\', this.checked)"');
								echo $MSG['msg_date_start'][$sysSession->lang];
								echo '<span id="span_begin_time" style="display: none;">';
								showXHTML_input('text', 'begin_time', $val, '', 'id="begin_time" readonly="readonly" class="cssInput"');
								echo '</span>';

								echo '<br />'.$MSG['last'][$sysSession->lang];
								showXHTML_input('checkbox', 'ck_end_date', 'end_date', '', 'id="ck_end_date" onclick="showDateInput(\'span_expire_time' . '\', this.checked)"');
								echo $MSG['msg_date_stop'][$sysSession->lang];
								echo '<span id="span_expire_time" style="display: none;">';
								showXHTML_input('text', 'expire_time', $val, '', 'id="expire_time" readonly="readonly" class="cssInput"');
								echo '</span>';
						    showXHTML_td_E();
						showXHTML_tr_E();

						showXHTML_tr_B('class="'.($bg = $bg == 'cssTrEvn'?'cssTrOdd':'cssTrEvn').'"');
							showXHTML_td(' align="center"', $MSG['serial'][$sysSession->lang]);
							showXHTML_td(' align="center"', $MSG['import'][$sysSession->lang]);
							showXHTML_td('', $MSG['data_fields'][$sysSession->lang]);
							showXHTML_td('', $MSG['first_line'][$sysSession->lang]);
						showXHTML_tr_E();

						for ($i=0; $i< $subject_count; $i++){
							showXHTML_tr_B('class="'.($bg = $bg == 'cssTrEvn'?'cssTrOdd':'cssTrEvn').'"');
								showXHTML_td(' align="center"', $i+1);
								showXHTML_td_B(' align="center"');
									showXHTML_input('checkbox', 'chk[]', $i, $i, 'onclick="Switch_sel('.$i.',this.checked)"' . ($i==0?' checked':''));
								showXHTML_td_E();
								showXHTML_td_B();
									echo '<select name="sel[]" disabled>', trim($fielda), '</select>';
								showXHTML_td_E();
								showXHTML_td('', ($lang == 'Big5' || $lang == 'GB2312') ? iconv($lang,'UTF-8',trim($subject_array[$i])) : trim($subject_array[$i]));
							showXHTML_tr_E();
						}
						showXHTML_tr_B('class="cssTrHead"');
							showXHTML_td_B(' colspan="4" align="center"');
								echo $MSG['pw_is'][$sysSession->lang],
								     '<select name="enc_method" class="cssInput" >',
								     '<option value="1">', $MSG['not_encode'][$sysSession->lang], '</option>',
								     '<option value="2">', $MSG['md5'][$sysSession->lang], '</option>',
								     '<option value="3">', $MSG['sys_produce'][$sysSession->lang], '</option>',
								     '</select><br>';
								showXHTML_input('submit', '', $MSG['next'][$sysSession->lang], '', 'class="cssBtn"');
								showXHTML_input('button', '', '  '.$MSG['cancel'][$sysSession->lang].'  ', '', 'class="cssBtn" onclick="cancel()"');
							showXHTML_td_E();
						showXHTML_tr_E();
					showXHTML_table_E();
					showXHTML_input('hidden', 'step', '2', '', '');
					showXHTML_input('hidden', 'impfile', $filename, '', '');
					showXHTML_input('hidden', 'lang', $lang, '', '');
					showXHTML_input('hidden', 'ticket', $_POST['ticket'], '', '');
					if ($_POST['PreSel'] != ''){
						showXHTML_input('hidden', 'PreSel', 'PreSel', '', '');
					}
				showXHTML_form_E();

				showXHTML_form_B('action="stud_account.php" method="post"', 'f3');
					showXHTML_input('hidden', 'msgtp', '3', '', '');
				showXHTML_form_E();
			showXHTML_body_E();

				break;
	}
?>

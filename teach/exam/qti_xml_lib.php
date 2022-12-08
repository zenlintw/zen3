<?php
    /**
     * QTI 匯出匯入 library
     *
     * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
     *
     * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
     * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
     * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
     *
     * @package     WM3
     * @author      Wiseguy Liang <wiseguy@mail.wiseguy.idv.tw>
     * @copyright   2000-2006 SunNet Tech. INC.
     * @version     CVS: $Id: qti_xml_lib.php,v 1.1 2010/02/24 02:40:26 saly Exp $
     * @link        http://demo.learn.com.tw/1000110138/index.html
     * @since       2006-06-16
     */

	/* 取得夾檔位置 begin */
	if (!defined('QTI_env'))
		list($foo, $topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
	else
		$topDir = QTI_env;
		
    $course_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id;

	if ($topDir == 'academic')
		$save_uri = sprintf('/base/%05d/%s/Q/',
		  					 $sysSession->school_id,
		  					 QTI_which);
	else
		$save_uri = sprintf('/base/%05d/course/%08d/%s/Q/',
		  					 $sysSession->school_id,
		  					 $sysSession->course_id,
		  					 QTI_which);
	/* 取得夾檔位置 end */


	define('MAT_TAG',  0);
	define('MAT_ATTR', 1);
	define('MAT_MIME', 2);

	$qti_item_types = array('1' => '/presentation//response_lid[@rcardinality="Single"]/render_choice/response_label[@ident="T"]',
							'2' => '/presentation//response_lid[@rcardinality="Single"]/render_choice',
							'3' => '/presentation//response_lid[@rcardinality="Multiple"]/render_choice',
							'5' => '/presentation//response_str/render_fib[@prompt="Box"]',
							'4' => '/presentation//*[name()="response_str" or name()="response_num"]/render_fib',
							'6' => '/presentation//response_grp/render_extension'
						   );

	$languages = array('Big5'			=> 1,
					   'GB2312'			=> 2,
					   'en'				=> 3,
					   'EUC-JP'			=> 4,
					   'user_define'	=> 5
					  );

	/**
	 * 哪一種夾檔
	 *
	 * @param   string      $fname      檔名
	 * @param   int         $m          {MAT_TAG | MAT_ATTR | MAT_MIME}
	 */
	function which_mat($fname, $m)
	{
		static $mats, $unknown;

		if (!isset($mats))
		{
			$mats = array(
						  '.jpg'  => array('matimage', 'imagtype',  'image/jpeg'),
						  '.jpeg' => array('matimage', 'imagtype',  'image/gif'),
						  '.jpe'  => array('matimage', 'imagtype',  'image/png'),
						  '.gif'  => array('matimage', 'imagtype',  'image/gif'),
						  '.png'  => array('matimage', 'imagtype',  'image/png'),
						  '.bmp'  => array('matimage', 'imagtype',  'image/bmp'),
						  '.wmv'  => array('matvideo', 'videotype', 'video/x-ms-wmv'),
						  '.wmx'  => array('matvideo', 'videotype', 'video/x-ms-wmx'),
						  '.asf'  => array('matvideo', 'videotype', 'video/x-ms-asf'),
						  '.rm'   => array('matvideo', 'videotype', 'application/vnd.rn-realmedia'),
						  '.avi'  => array('matvideo', 'videotype', 'video/x-msvideo'),
						  '.mov'  => array('matvideo', 'videotype', 'video/quicktime'),
						  '.qt'   => array('matvideo', 'videotype', 'video/quicktime'),
						  '.mpg'  => array('matvideo', 'videotype', 'video/mpeg'),
						  '.mpeg' => array('matvideo', 'videotype', 'video/mpeg'),
						  '.mpe'  => array('matvideo', 'videotype', 'video/mpeg'),
						  '.swf'  => array('matvideo', 'videotype', 'application/x-shockwave-flash'),
						  '.wma'  => array('mataudio', 'audiotype', 'audio/x-ms-wma'),
						  '.mp3'  => array('mataudio', 'audiotype', 'audio/mpeg'),
						  '.ra'   => array('mataudio', 'audiotype', 'audio/x-pn-realaudio'),
						  '.wav'  => array('mataudio', 'audiotype', 'audio/x-wav'),
						  '.mid'  => array('mataudio', 'audiotype', 'audio/midi')
						 );
			$unknown = array('matapplication', 'apptype', 'application/octet-stream');
		}

		$ret = $mats[strtolower(strrchr($fname, '.'))][$m];
		return empty($ret) ? $unknown[$m] : $ret;
	}

	function remove_all_children(&$node)
	{
	    foreach($node->child_nodes() as $child) $node->remove_child($child);
	}

	/**
	 * 將夾檔化為 base64 編碼插入 XML 中
	 *
	 * @param   string      $tag    要安插的 tagname
	 * @param   string      $xpath  此 tag 在 XML 的位置 (XPath 表示)
	 */
	function set_attachments($tag, $xpath, &$dom, &$ctx)
	{
		global $ats, $attach_path;

		if (is_array($ats[$tag]) && ($ret = $ctx->xpath_eval($xpath)))
		{
			$material = $ret->nodeset[0];
			foreach($ats[$tag] as $original_fname => $virtual_fname)
			{
				if ($virtual_fname && $tag = which_mat($original_fname, MAT_TAG))
				{
					if (file_exists($attach_path . $virtual_fname))
					{
						$material->append_child($dom->create_element('matbreak'));
						$newmat = $material->append_child($dom->create_element($tag));
						$newmat->set_attribute(which_mat($original_fname, MAT_ATTR), which_mat($original_fname, MAT_MIME));
						$newmat->set_attribute('label', $original_fname);
						$newmat->set_attribute('embedded', 'Base64');
						$newmat->append_child($dom->create_text_node(base64_encode(file_get_contents($attach_path . $virtual_fname))));
					}
				}
			}
		}
	}


	/**
	 * 將某個 item 轉為 QTI_XML 印出來
	 *
	 * @param   string      $id     	item 的 identifier
	 * @param   string      $content    item 的 xml
	 * @param   string      $attach     item 的夾檔
	 */
	function get_qti_item_xml($id, $content, $attach='')
	{
	    global $save_uri, $ats, $attach_path;
	
		if (!empty($attach))
		{
			if (($ats = unserialize($attach)) !== false)
			{
				if ($dom = domxml_open_mem(preg_replace(array('/>\s+</', '/\s+xmlns="[^"]*"/'), array('><', ''), $content)))
				{
					$ctx = $dom->xpath_new_context();
					$ctx->xpath_register_ns('wm','http://www.sun.net.tw/WisdomMaster');
					$attach_path = sysDocumentRoot . $save_uri . $id . '/';

					set_attachments('topic_files', '//presentation//material[1]',    $dom, $ctx);
					set_attachments('ans_files',   '//solutionmaterial/material[1]', $dom, $ctx);

					foreach(array('', '1', '2') as $kind)
					{
						if (is_array($ats['render' . $kind . '_choice_files']))
						{
							$i = 1;
							foreach($ats['render' . $kind . '_choice_files'] as $original_fname => $virtual_fname)
							{
								if ($virtual_fname && $tag = which_mat($original_fname, MAT_TAG))
								{
									$ret = $ctx->xpath_eval('//response_label' . ($kind == '2' ? "[not(@match_group)][{$i}]" : "[{$i}]") . '//material[1]');
									if (file_exists($attach_path . $virtual_fname)  && $ret !== false && count($ret->nodeset) > 0)
									{
										$material = $ret->nodeset[0];
										$material->append_child($dom->create_element('matbreak'));
										$newmat = $material->append_child($dom->create_element($tag));
										$newmat->set_attribute(which_mat($original_fname, MAT_ATTR), which_mat($original_fname, MAT_MIME));
										$newmat->set_attribute('label', $original_fname);
										$newmat->set_attribute('embedded', 'Base64');
										$newmat->append_child($dom->create_text_node(base64_encode(file_get_contents($attach_path . $virtual_fname))));
									}
								}
								$i++;
							}
						}
					}
					return preg_replace('/\s*<\?.*\?>\s*/sU', '', $dom->dump_mem(false, 'UTF-8'));
				}
				else
					return $content;
			}
			else
				return $content;
		}
		else
			return $content;
	}

	/**
	 * 傳回某個 item 的 xml
	 *
	 * @param   string      $id     	item 的 identifier
	 * @param   string      $content    item 的 xml
	 * @param   string      $attach     item 的夾檔
	 */
   	function dump_qti_item_xml($id, $content, $attach='')
	{
		echo get_qti_item_xml($id, $content, $attach);
	}


	/**
	 * 由 XML 結構，判斷題目類型
	 */
	function detect_item_type(&$ctx, $path){
		global $qti_item_types;

		foreach($qti_item_types as $index => $kind){
			$ret = $ctx->xpath_eval($path . $kind);
			if (count($ret->nodeset)) return (int)$index;
		}
		return 0;
	}

	/**
	 * 取某節點裡的最底層文字
	 * param element $element 節點
	 * return string 節點文字
	 */
	function getNodeContent($element){
		if (!is_object($element)) return '';
		$node = $element;
		while($node->has_child_nodes()){
			$node = $node->first_child();
		}
		return $node->node_value();
	}

	/**
	 * 存檔
	 */
	if (!function_exists('file_put_contents'))
	{
		function file_put_contents($filename, $data)
		{
			if ($fp = fopen($filename, 'w'))
			{
				fwrite($fp, $data);
				fclose($fp);
				return true;
			}
			else
				return false;
		}
	}

	// 匯入 XML
	function parseXML(){
		global $sysConn, $sysSession, $MSG, $course_id, $languages, $save_uri, $dom, $ctx;

		if (!is_uploaded_file($_FILES['import_file']['tmp_name']))
		{
			showXHTML_tr_B('class="cssTrEvn"');
				showXHTML_td('colspan="2" style="color: red"',  $MSG['upload_file_not_found'][$sysSession->lang]);
			showXHTML_tr_E();
			return;
		}

		$xml_content = file_get_contents($_FILES['import_file']['tmp_name']);
		$ext = pathinfo($_FILES['import_file']['name'], PATHINFO_EXTENSION);
		if($ext!='xml' || !$dom = @domxml_open_mem(preg_replace(array('/>\s+</', '/\s+xmlns="[^"]*"/'), array('><', ''), $xml_content)))
		{
			showXHTML_tr_B('class="cssTrEvn"');
				showXHTML_td('colspan="2" style="color: red"', $MSG['unknown_xml'][$sysSession->lang]);
			showXHTML_tr_E();
			return;
		}

  		$root = $dom->document_element();
  		if ($root->tagname() != 'questestinterop')
  		{
			showXHTML_tr_B('class="cssTrEvn"');
				showXHTML_td('colspan="2" style="color: red"', $MSG['xml_root'][$sysSession->lang]);
			showXHTML_tr_E();
			return 'xml_root_err';
		}
  		$ctx = xpath_new_context($dom);
  		$ctx->xpath_register_ns('wm','http://www.sun.net.tw/WisdomMaster');

		$nodes = $dom->get_elements_by_tagname('item');
		$table = 'WM_qti_' . QTI_which . '_item';
		$checker = false;
		if (QTI_which == 'exam' && CourseQuestionsLimit > 0)   //有題目數的限制
		{
			// 取得目前此課程的題目數
			list($now_CourseQusNum) = dbGetStSr('WM_qti_exam_item','count(*)',"course_id='{$course_id}'", ADODB_FETCH_NUM);
			$now_CourseQusNum = intval($now_CourseQusNum);
		}
		else
		    $now_CourseQusNum = 0;

		foreach($nodes as $node)
		{
			$checker ^= true;

            if ( QTI_which == 'exam' && CourseQuestionsLimit > 0 && $now_CourseQusNum >= CourseQuestionsLimit)
            {
				$msg = str_replace('%questions_limit%',CourseQuestionsLimit,$MSG['msg_overQuestionLimit'][$sysSession->lang]);
				list($admin_email) = dbGetStSr('WM_school','school_mail',"school_id='{$sysSession->school_id}'", ADODB_FETCH_NUM);
				$msg = str_replace('%admin_email%','mailto:'.$admin_email, $msg);

				showXHTML_tr_B($checker ? 'class="cssTrEvn"' : 'class="cssTrOdd"');
					showXHTML_td('colspan="2" style="color: red; font-weight: bold"', $msg);
				showXHTML_tr_E();
				return;
			}
			
			$t = split('[. ]', microtime());
			$count = intval(substr($t[1],0,6));
			$ident = sprintf('WM_ITEM1_%s_%u_%s_%06u', sysSiteUID, $course_id, $t[2], $count);	// 使用新產生ident的方式, 以讓ident排序上能正確
			$repeat = 0;
			while($isExist = dbGetOne('WM_qti_'.QTI_which.'_item', 'count(*)', 'ident="'.$ident.'"'))
			{
				$ident = sprintf('WM_ITEM1_%s_%u_%s_%06u', sysSiteUID, $course_id, $t[2], ++$count);
				if (++$repeat >= 100)
				{
					showXHTML_tr_B('class="cssTrEvn"');
						showXHTML_td('colspan="2" style="color: red"', $MSG['cant_gen_id'][$sysSession->lang]);
					showXHTML_tr_E();
					return;
				}
			}
			
			$node->set_attribute('ident', $ident);
			$type = detect_item_type($ctx, "//item[@ident='$ident']");
			if ($type == 0)
			{
				showXHTML_tr_B($checker ? 'class="cssTrEvn"' : 'class="cssTrOdd"');
					showXHTML_td('', "($ident) $title");
					showXHTML_td('style="color: red"', $MSG['item_not_support'][$sysSession->lang]);
				showXHTML_tr_E();
				continue;
			}

			$title = $node->get_attribute('title');
			foreach(array('version', 'volume', 'chapter', 'paragraph', 'section', 'hardlevel') as $elem){
				$elems = $ctx->xpath_eval("//item[@ident='$ident']//wm:$elem");
			$$elem = intval(getNodeContent($elems->nodeset[0]));
			}

			// 處理夾檔 begin

			$attachments = array(); $hasAttach = false;
			$attachs = $ctx->xpath_eval("count(//item[@ident='$ident']//*[self::matimage or self::matvideo or self::mataudio or self::matapplication])");
			if ($attachs && $attachs->value > 0)
			{

				$attach_path = sysDocumentRoot . $save_uri . $ident . '/';
				if (!is_dir($attach_path))
				{
					@exec('mkdir -p ' . escapeshellarg($attach_path));
					if (!is_dir($attach_path))
					{
						die('Directory not allow be creating.');
					}
				}

				// topic_files
				$files = $ctx->xpath_eval("//item[@ident='$ident']/presentation//material[not(ancestor::*[substring-before(name(), '_')='response'])]//*[self::matimage or self::matvideo or self::mataudio or self::matapplication]");
				if (is_array($files->nodeset) && count($files->nodeset))
				{
					foreach($files->nodeset as $mat_item)
					{
						$first = $mat_item->first_child();
						if (($data = base64_decode($first->node_value())) !== FALSE)
						{
							// 取得 label 屬性，當做真實檔名
							$label = $mat_item->get_attribute('label');
							$fname = tempnam($attach_path, 'WM') . strtolower(strrchr($label, '.'));
							file_put_contents($fname, $data);
							$attachments['topic_files'][$label] = basename($fname);
							$hasAttach = true;

							// 如果上一個節點是 <matbreak> 則一起刪除
							$first  = $mat_item->previous_sibling();
							$parent = $mat_item->parent_node();
							if ($first->node_name() == 'matbreak') $parent->remove_child($first);
							$parent->remove_child($mat_item);
            			}
					}
				}

				// render_choice_files
				if ($type == 2 || $type == 3)
				{
					$files = $ctx->xpath_eval("//item[@ident='$ident']/presentation/flow/*[substring-before(name(), '_')='response'][1]/*[substring-before(name(), '_')='render'][1]");
					if (is_array($files->nodeset) && count($files->nodeset))
					{
						$render = $files->nodeset[0];
						$files = $ctx->xpath_eval('./response_label', $render);
						if (is_array($files->nodeset) && count($files->nodeset))
						{
							foreach($files->nodeset as $response_label)
							{
								$mats = $ctx->xpath_eval('.//material//*[self::matimage or self::matvideo or self::mataudio or self::matapplication]', $response_label);
								if (is_array($mats->nodeset) && count($mats->nodeset))
								{
									$mat_item = $mats->nodeset[0];
									$first = $mat_item->first_child();
									if (($data = base64_decode($first->node_value())) !== FALSE)
									{
										$label = $mat_item->get_attribute('label');
										$fname = tempnam($attach_path, 'WM') . strtolower(strrchr($label, '.'));
										file_put_contents($fname, $data);
										$attachments['render_choice_files'][$label] = basename($fname);
										$hasAttach = true;

										$first  = $mat_item->previous_sibling();
										$parent = $mat_item->parent_node();
										if ($first->node_name() == 'matbreak') $parent->remove_child($first);
										$parent->remove_child($mat_item);
				            		}
									else
									{
										$attachments['render_choice_files'][] = null;
									}
								}
								else
								{
									$attachments['render_choice_files'][] = null;
								}
							}
						}
					}
				}

				if ($type == 6)
				{
					// render1_choice_files & render2_choice_files
					$files = $ctx->xpath_eval("//item[@ident='$ident']/presentation/flow/*[substring-before(name(), '_')='response'][1]/*[substring-before(name(), '_')='render'][1]");
					if (is_array($files->nodeset) && count($files->nodeset))
					{
						$render = $files->nodeset[0];

						foreach(array(1,2) as $i)
						{
							$files = $ctx->xpath_eval('.//response_label' . ($i == 1 ? '[@match_group]' : '[not(@match_group)]'), $render);
							if (is_array($files->nodeset) && count($files->nodeset))
							{
								foreach($files->nodeset as $response_label)
								{
									$mats = $ctx->xpath_eval(".//material//*[self::matimage or self::matvideo or self::mataudio or self::matapplication]", $response_label);
									if (is_array($mats->nodeset) && count($mats->nodeset))
									{
										$mat_item = $mats->nodeset[0];
										$first = $mat_item->first_child();
										if (($data = base64_decode($first->node_value())) !== FALSE)
										{
											$label = $mat_item->get_attribute('label');
											$fname = tempnam($attach_path, 'WM') . strtolower(strrchr($label, '.'));
											file_put_contents($fname, $data);
											$attachments['render' . $i . '_choice_files'][$label] = basename($fname);
											$hasAttach = true;

											$first  = $mat_item->previous_sibling();
											$parent = $mat_item->parent_node();
											if ($first->node_name() == 'matbreak') $parent->remove_child($first);
											$parent->remove_child($mat_item);
				        	    		}
										else
										{
											$attachments['render' . $i . '_choice_files'][] = null;
										}
									}
									else
									{
										$attachments['render' . $i . '_choice_files'][] = null;
									}
								}
							}
						}
					}
				}

				// ans_files
				$files = $ctx->xpath_eval("//item[@ident='$ident']//solutionmaterial//material//*[self::matimage or self::matvideo or self::mataudio or self::matapplication]");
				if (is_array($files->nodeset) && count($files->nodeset))
				{
					foreach($files->nodeset as $mat_item)
					{
						$first = $mat_item->first_child();
						if (($data = base64_decode($first->node_value())) !== FALSE)
						{
							$label = $mat_item->get_attribute('label');
							$fname = tempnam($attach_path, 'WM') . strtolower(strrchr($label, '.'));
							file_put_contents($fname, $data);
							$attachments['ans_files'][$label] = basename($fname);
							$hasAttach = true;

							$first  = $mat_item->previous_sibling();
							$parent = $mat_item->parent_node();
							if ($first->node_name() == 'matbreak') $parent->remove_child($first);
							$parent->remove_child($mat_item);
            			}
					}
				}

			}
			// 處理夾檔 end

			$xmlstr = $dom->dump_node($node);
			if (strpos($xmlstr, '<wm:') !== false && strpos($xmlstr, 'xmlns:wm=') === false)
			{
			    $xmlstr = str_replace('<item ', '<item xmlns:wm="http://www.sun.net.tw/WisdomMaster" ', $xmlstr);
			}
			dbNew($table, 'ident,title,course_id,type,version,volume,chapter,paragraph,section,level,language,author,create_time,last_modify,content,attach',
			      sprintf('"%s", "%s", %u, %d, %d, %d, %d, %d, %d, %d, %d, "%s", now(), now(), "%s", %s',
			      		  $ident,
			      		  addslashes($title),
			      		  $course_id,
			      		  $type,
						  $version,
						  $volume,
						  $chapter,
						  $paragraph,
						  $section,
						  $hardlevel,
						  $languages[$sysSession->lang],
						  $sysSession->username,
						  addslashes($xmlstr),
						  ($hasAttach ? $sysConn->qstr(serialize($attachments)) : 'null')
			      		 )
			     );

			if ($sysConn->Affected_Rows())
			{
				showXHTML_tr_B($checker ? 'class="cssTrEvn"' : 'class="cssTrOdd"');
					showXHTML_td('', "($ident) $title");
					showXHTML_td('style="color: green"', $MSG['import_success'][$sysSession->lang]);
				showXHTML_tr_E();
				wmSysLog($sysSession->cur_func, $course_id , 0 , 0, 'auto', $_SERVER['PHP_SELF'], 'import item to ' . $table . ' success');
				$now_CourseQusNum++;
			}
			else
			{
				showXHTML_tr_B($checker ? 'class="cssTrEvn"' : 'class="cssTrOdd"');
					showXHTML_td('', "($ident) $title");
					showXHTML_td('style="color: red"', sprintf('%s, %d: %s', $MSG['import_failure'][$sysSession->lang], $sysConn->ErrorNo(), $sysConn->ErrorMsg()));
				showXHTML_tr_E();
				wmSysLog($sysSession->cur_func, $course_id , 0 , 2, 'auto', $_SERVER['PHP_SELF'], 'import item to ' . $table . ' fail');
			}
		}
	}

?>

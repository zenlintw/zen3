<?php

	/**
	 * �P�_�D���ѡB�ťզ�
	 * param: $element string �r���
	 * return: boolean
	 */
	function strip_remark($element) {
		$var = trim($element);
		if (empty($var)) {
			return false;
		} else {
			return !ereg('^;', $var);
		}
	}

	/**
	 * ���o�ҵ{�ؿ����� .CRS �ɮצW��
	 *
	 * return �ɦW�F�Y�䤣��h�Ǧ^�Ŧr��
	 */
	function getCRSfile() {
		global $CoursePath;

		chdir($CoursePath);
		if ($dir = @opendir($CoursePath)) {
			while(($file = readdir($dir)) !== FALSE) {
				if (is_file($file) && substr($file, -4) == '.CRS') {
					closedir($dir);
					return $file;
				}
			}
			closedir($dir);
		}
		return '';
	}


	/**
	 * ============================= �D�{���}�l ===============================
	 */
	if (!isset($my_dir)) die('The paogram not allow running independently.');
	$CoursePath = $my_dir;

	$xmlstr = <<< EOB
<?xml version = "1.0"?>
<manifest identifier="LMSTestCourse01_Manifest" version="1.2"
       xmlns="http://www.imsproject.org/xsd/imscp_rootv1p1p2"
       xmlns:adlcp="http://www.adlnet.org/xsd/adlcp_rootv1p2"
       xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
       xsi:schemaLocation="http://www.imsproject.org/xsd/imscp_rootv1p1p2 imscp_rootv1p1p2.xsd
                           http://www.imsglobal.org/xsd/imsmd_rootv1p2p1 imsmd_rootv1p2p1.xsd
                           http://www.adlnet.org/xsd/adlcp_rootv1p2 adlcp_rootv1p2.xsd">
   <organizations>
      <organization>
      </organization>
   </organizations>
   <resources>
   </resources>
</manifest>
EOB;
	$doc = function_exists('xmldoc') ?
	       xmldoc(preg_replace('/>\s+</', '><', $xmlstr)) :
	       domxml_open_mem(preg_replace('/>\s+</', '><', $xmlstr));
	if(!is_object($doc)) die("Error while initializing the document\n");

	$root = $doc->document_element();
	$cur_node = $root->first_child();
	$cur_node = $cur_node->first_child();
	$resource = $root->last_child();


	/**
	 * ============================ Ū�� ***.CRS �ҥ� Title �� =============================
	 * input:  .CRS file
	 * output: array $CourseElement
	 */

	$fname = getCRSfile();
	if (empty($fname)) die('The course is not a AICC course.');

	$filename = $CoursePath . $fname;
	if (file_exists($filename)) {
		$lines = array_filter(file($filename), 'strip_remark');
	}
	else
		die('"' . basename($filename) . '" not exist in the course directory.');

	$lines_len = count($lines);
	$block = '';

	$CRS_Group = array('Course','Course_Behavior','Course_Description');
	$CRS_Course_kw = array(	'Course_Creator',
				'Course_ID',
				'Course_System',
				'Course_Title',
				'Max_Fields_CST',
				'Max_Fields_ORT',
				'Total_AUs',
				'Total_Blocks',
				'Total_Objectives',
				'Total_Complex_Obj',
				'Level',
				'Version'
			      );
	$CRS_Course_kw_num  = array_slice($CRS_Course_kw, 6, 4);
	$CRS_Course_kw_else = array_slice($CRS_Course_kw, 0, 6);
	$Legal_Level = array('1','2','3','3a','3b');


	// �o�ǬO default ��
	$CourseElement['Level'] = '1';
	$CourseElement['Max_Normal'] = '1';
	$CourseElement['Course_Description'] = '';

	// �}�l�ˬd�}�C
	if ($lines_len)
		while(list($k,$cell)=each($lines)) {
			$element = trim($cell);
			// �p�G�O�϶�����
			if (eregi('^\[([a-z_]+)\]$', $element, $reg)) {
				if (in_array($reg[1], $CRS_Group)) $block = $reg[1];
			}
			// ���O�϶��N�O�]�w��
			else{
				$parameter = explode('=', $element);
				$keyword = rtrim($parameter[0]);
				$value   = ltrim($parameter[1]);
				// �p�G�O [Course] �϶�
				if($block == 'Course'){
					if (in_array($keyword, $CRS_Course_kw))
						if(in_array($keyword, $CRS_Course_kw_else)){
							$CourseElement[$keyword] = $value;
						}
						elseif ($keyword == 'Level' && in_array($value, $Legal_Level)){
							$CourseElement['Level'] = $value;
						}
						elseif ($keyword == 'Version' && ereg('^[0-9]+\.[0-9]+$', $value)){
							$CourseElement['Version'] = $value;
						}
						elseif(in_array($keyword, $CRS_Course_kw_num) && ereg('^[0-9]+$', $value)){
							$CourseElement[$keyword] = $value;
						}
				}
				// �p�G�O [Course_Behavior] �϶�
				elseif($block == 'Course_Behavior'){
					if ($keyword == 'Max_Normal' && ereg('^[0-9]+$', $value)){
						$v = intval($value);
						if ($v > 99) $value = '99';
						if ($v <  1) $value = '1';
						$CourseElement[$keyword] = $value;
					}
				}
				// �p�G�O [Course_Description] �϶�
				elseif($block == 'Course_Description'){
					$CourseElement['Course_Description'] .= "$keyword\n";
				}
			}
		}

	// �ˬd���n���O�_���w�q
	if (!array_key_exists('Max_Fields_CST', $CourseElement)) die ('Keyword: Max_Fields_CST not be defined.');
	if (!array_key_exists('Max_Fields_ORT', $CourseElement)) die ('Keyword: Max_Fields_ORT not be defined.');

	// ��ƤӪ��屼
	if (strlen($CourseElement['Course_Description']) > 4096) $CourseElement['Course_Description'] = substr($CourseElement['Course_Description'], 0, 4096);

	// �M���Ȧs�ܼ�
	unset($lines_len, $block, $CRS_Group, $CRS_Course_kw, $CRS_Course_kw_num, $CRS_Course_kw_else,
		  $Legal_Level, $element, $parameter, $keyword, $value, $v, $k, $cell, $lines, $reg);

	/**
	 *
	 */
	$root->set_attribute('identifier', $CourseElement['Course_ID']);
	$root->set_attribute('version', $CourseElement['Version']);


	//============================ Ū�� ***.CST �ҵ{���c�� =============================

	$filename = ereg_replace('\.CRS$', '.CST', $filename);
	if (file_exists($filename)){
		$fp = fopen ($filename, 'r');
	}
	else
		die('"' . basename($filename) . '" not exist in the course directory.');

	$i = 0;
	while ($data = fgetcsv ($fp, 1000, ',')) {
		if (!isset($fields)){
			$fields = array_values($data);	// ���o���W��
			$fields_len = count($data);	// ���o���Ӽ�
			if (($fields_len - 1) > intval($CourseElement['Max_Fields_CST']))
				die('member length > Max_Fields_CST .');
		}
		else{
			if ($data[0] == 'root'){
				for($j=1; $j<$fields_len; $j++){
					if (!empty($data[$j])){
						$node = $doc->create_element('item');
						$newnode = $cur_node->append_child($node);
						$newnode->set_attribute('identifier', $data[$j]);
						$newnode->set_attribute('identifierref', $data[$j]);
						$node = $doc->create_element('title');
						$newnode->append_child($node);
					}
				}
			}
			else{
				$sub_node = null;
				for($j=0; $j<count($nodes); $j++){
					$attr = $nodes[$j]->get_attribute('identifier');
					if ($attr == $data[0]){
						$sub_node = $nodes[$j];
						break;
					}
				}

				if (!is_object($sub_node)) {
					die('[CST] ID not found : ' . $data[0]);
				}
				for($j=1; $j<$fields_len; $j++){
					if (!empty($data[$j])){
						$node = $doc->create_element('item');
						$newnode = $sub_node->append_child($node);
						$newnode->set_attribute('identifier', $data[$j]);
						$newnode->set_attribute('identifierref', $data[$j]);
						$node = $doc->create_element('title');
						$newnode->append_child($node);
					}
				}
			}
			$nodes = $doc->get_elements_by_tagname('item');
		}
	}
	fclose ($fp);

	// �M���Ȧs�ܼ�
	unset($i, $data, $fp, $fields, $fields_len);


	/**
	 * ============================ Ū�� ***.DES �w�q�y�z�� =============================
	 * input:  .DES file
	 * output: array $DES_table
	 */

	$filename = ereg_replace('\.CST$', '.DES', $filename);
	if (file_exists($filename)){
		$fp = fopen ($filename, 'r');
	}
	else
		die('"' . basename($filename) . '" not exist in the course directory.');

	$fields_all = array('system_id',
			    'developer_id',
			    'title',
			    'description');
	$fields_require = array_slice($fields_all, 0, 3);
	$fields_require_key = array('system_id'    => 1,
			    	    'developer_id' => 1,
			    	    'title'        => 1);
	$i = 0;
	while ($data = fgetcsv ($fp, 1000, ',')) {
		if (!isset($fields)){
			$fields = array_values($data);	// ���o���W��
			$fields_len = count($data);	// ���o���Ӽ�
			for ($j=0; $j<count($fields); $j++){
				if (in_array(strtolower($fields[$j]), $fields_require)){
					$fields_require_key[strtolower($fields[$j])] = 0;
				}
			}
			// �ˬd���n���
			if (array_sum($fields_require_key)) die('"system_id","developer_id","title" fields required.');

		}
		else{
			for($j=0; $j<$fields_len; $j++)
				$DES_table[$i][$fields[$j]] = $data[$j];
			$i++;
		}
	}
	fclose ($fp);

	for($j=0; $j<$i; $j++){
		$sub_node = null;
		for($k=0; $k<count($nodes); $k++){
			$attr = $nodes[$k]->get_attribute('identifier');
			if ($attr == $DES_table[$j]['system_id']){
				$sub_node = $nodes[$k];
				break;
			}
		}

		if (!is_object($sub_node)) {
			die('[DES] ID not found : ' . $AU_table[$j]['system_id']);
		}

		$newnode = $sub_node->first_child();
		$node = $doc->create_text_node(iconv('Big5', 'UTF-8', $DES_table[$j]['title']));
		$newnode->append_child($node);
	}

	/*
	 *
	 * ��ƿ��~�ˬd (�Ƚw) cmi001v3-5.pdf(p.185)
	 *
	 */

	// �M���Ȧs�ܼ�
	unset($i, $j, $data, $fp, $fields, $fields_len, $fields_all, $fields_require, $fields_require_key);


	/**
	 * ============================ Ū�� ***.AU Asset ���X�� =============================
	 * input:  .AU file
	 * output: array $AU_table
	 */

	$filename = ereg_replace('\.DES$', '.AU', $filename);
	if (file_exists($filename)){
		$fp = fopen ($filename, 'r');
	}
	else
		die('"' . basename($filename) . '" not exist in the course directory.');

	$fields_all = array('system_id',
			    'command_line',
			    'file_name',
			    'core_vendor',
			    'max_score',
			    'mastery_score',
			    'max_time_allowed',
			    'time_limit_action',
			    'system_vendor',
			    'type',
			    'web_launch',
			    'au_password');
	$fields_require = array_slice($fields_all, 0, 4);
	$fields_require_key = array('system_id'    => 1,
			    	    'command_line' => 1,
			    	    'file_name'    => 1,
			    	    'core_vendor'  => 1);
	$i = 0;
	while ($data = fgetcsv ($fp, 1000, ',')) {
		if (!isset($fields)){
			$fields = array_values($data);	// ���o���W��
			$fields_len = count($data);	// ���o���Ӽ�
			for ($j=0; $j<count($fields); $j++){
				if (in_array(strtolower($fields[$j]), $fields_require)){
					$fields_require_key[strtolower($fields[$j])] = 0;
				}
			}
			// �ˬd���n���
			if (array_sum($fields_require_key)) die('"system_id","command_line","file_name","core_vendor" fields required.');
		}
		else{
			for($j=0; $j<$fields_len; $j++){
				if ($fields[$j] == 'file_name'){
					$data[$j] = ereg_replace('^/', '', str_replace('\\', '/', $data[$j]));
				}
				$AU_table[$i][$fields[$j]] = $data[$j];
			}
			$i++;
		}
	}
	fclose ($fp);

	for($j=0; $j<$i; $j++){
		$node = $doc->create_element('resource');
		$newnode = $resource->append_child($node);
		$newnode->set_attribute('identifier', $AU_table[$j]['system_id']);
		$res_ids[] = $AU_table[$j]['system_id'];
		$newnode->set_attribute('type', 'webcontent');
		$newnode->set_attribute('adlcp:scormtype', 'sco');
		$url = str_replace('../pba/', '', $AU_table[$j]['file_name']);
		$newnode->set_attribute('href', empty($url) ? 'about:blank' : $url);
		$node = $doc->create_element('file');
		$newnode2 = $newnode->append_child($node);
		$node = $doc->create_text_node(str_replace('../pba/', '', $AU_table[$j]['file_name']));
		$newnode2->append_child($node);

		$sub_node = null;
		for($k=0; $k<count($nodes); $k++){
			$attr = $nodes[$k]->get_attribute('identifier');
			if ($attr == $AU_table[$j]['system_id']){
				$sub_node = $nodes[$k];
				break;
			}
		}

		if (!is_object($sub_node)) {
			die('[AU] ID not found : ' . $AU_table[$j]['system_id']);
		}

		if ($AU_table[$j]['time_limit_action']){
			$node = $doc->create_element('adlcp:timelimitaction');
			$newnode = $sub_node->append_child($node);
			$node = $doc->create_text_node($AU_table[$j]['time_limit_action']);
			$newnode->append_child($node);
		}

		if ($AU_table[$j]['mastery_score']){
			$node = $doc->create_element('adlcp:masteryscore');
			$newnode = $sub_node->append_child($node);
			$node = $doc->create_text_node($AU_table[$j]['mastery_score']);
			$newnode->append_child($node);
		}

	}

	for($i=0; $i<count($nodes); $i++){
		$attr = $nodes[$i]->get_attribute('identifierref');
		if (!in_array($attr, $res_ids)){
			$nodes[$i]->remove_attribute('identifierref');
		}
	}

	/*
	 *
	 * ��ƿ��~�ˬd (�Ƚw) cmi001v3-5.pdf(p.179)
	 *
	 */

	// �M���Ȧs�ܼ�
	unset($i, $j, $data, $fp, $fields, $fields_len, $fields_all, $fields_require, $fields_require_key);


	/*
	//============================ Ū�� ***.ORT �������p�� =============================

	$filename = ereg_replace('\.CST$', '.ORT', $filename);
	if (file_exists($filename)){
		$fp = fopen ($filename, 'r');
	}
	else
		die('"' . basename($filename) . '" not exist in the course directory.');

	$i = 0;
	while ($data = fgetcsv ($fp, 1000, ',')) {
		if (!isset($fields)){
			$fields = array_values($data);	// ���o���W��
			$fields_len = count($data);	// ���o���Ӽ�
			if (($fields_len - 1) > intval($CourseElement['Max_Fields_ORT']))
				die('member length > Max_Fields_ORT .');
		}
		else{
			$ORT_table[$data[0]] = implode(',', array_slice($data,1));
			$i++;
		}
	}
	fclose ($fp);

	// �M���Ȧs�ܼ�
	unset($i);
	unset($data);
	unset($fp);
	unset($fields);
	unset($fields_len);

	//============================ Ū�� ***.PRE �w���q�L�w�q�� =============================

	$filename = ereg_replace('\.ORT$', '.PRE', $filename);
	if (file_exists($filename)){
		$fp = fopen ($filename, 'r');
	}
	else
		die('"' . basename($filename) . '" not exist in the course directory.');

	$i = 0;
	while ($data = fgetcsv ($fp, 1000, ',')) {
		if (!isset($fields)){
			$fields = array_values($data);	// ���o���W��
			$fields_len = count($data);	// ���o���Ӽ�
		}
		else{
			$PRE_table[$data[0]] = $data[1];
			$i++;
		}
	}
	fclose ($fp);

	// �M���Ȧs�ܼ�
	unset($i);
	unset($data);
	unset($fp);
	unset($fields);
	unset($fields_len);

	//============================ Ū�� ***.CMP �q�L�w�q�� =============================

	$filename = ereg_replace('\.AU$', '.CMP', $filename);
	if (file_exists($filename)){
		$fp = fopen ($filename, 'r');
	}
	else
		die('"' . basename($filename) . '" not exist in the course directory.');

	$i = 0;
	while ($data = fgetcsv ($fp, 1000, ',')) {
		if (!isset($fields)){
			$fields = array_values($data);	// ���o���W��
			$fields_len = count($data);	// ���o���Ӽ�
		}
		else{
			for($j=0; $j<$fields_len; $j++)
				$CMP_table[$i][$fields[$j]] = $data[$j];
			$i++;
		}
	}
	fclose ($fp);

	// �M���Ȧs�ܼ�
	unset($i);
	unset($data);
	unset($fp);
	unset($fields);
	unset($fields_len);
	*/

	$filename = dirname($filename) . '/imsmanifest.xml';
	$fp = fopen($filename, 'w');
	if ($fp){
		fwrite($fp, str_replace('><', ">\n<", $doc->dump_mem()));
		fclose($fp);
	}

?>

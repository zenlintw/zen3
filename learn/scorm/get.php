<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

	function GenerateXML($value) {
		if ($value == '')
			echo '<?xml version="1.0"?><root/>';
		else
			echo '<?xml version="1.0"?><root>'. htmlspecialchars($value). '</root>';
	}

	function GenerateSCOInitialDataXML() {
		global $Course_ID, $User_ID, $SCO_ID;
		$result = '';

		// --------------------cmi_core--------------------------------------------------------------
		$rs = dbGetStMr('cmi_core', '*', "Course_ID= '{$Course_ID}' and User_ID= '{$User_ID}' and SCO_ID= '{$SCO_ID}'", ADODB_FETCH_ASSOC);
		if ($rs) {
			while ($row = $rs->FetchRow()) {
				foreach($row as $key => $value) {
					if ($key != 'last_time') {
						if ($value)
							$result .= '<' . $key . '>' . htmlspecialchars($value) . '</' . $key . '>';
						else
							$result .= "<$key></$key>";
					}
				}
			}
		}

		// --------------------cmi_objectives--------------------------------------------------------------
		$rs = dbGetStMr('cmi_objectives', '*', "Course_ID= '{$Course_ID}' and User_ID= '{$User_ID}' and SCO_ID= '{$SCO_ID}'", ADODB_FETCH_ASSOC);
		if ($rs) {
			while ($row = $rs->FetchRow()) {
				$result .= '<cmi_objectives';
				foreach($row as $key => $value) {
					if ($value)
						$result .= ' ' . $key . '="'.htmlspecialchars($value).'"';
					else
						$result .= " $key=''";
				}
				$result .= '/>';
			}
		}

		// --------------------cmi_student_preference--------------------------------------------------------------
		$rs = dbGetStMr('cmi_student_preference', '*', "Course_ID= '{$Course_ID}' and User_ID= '{$User_ID}' and SCO_ID= '{$SCO_ID}'", ADODB_FETCH_ASSOC);
		if ($rs) {
			while ($row = $rs->FetchRow()) {
				foreach($row as $key => $value) {
					if ($key != 'Course_ID' && $key != 'SCO_ID' && $key != 'User_ID') {
						if ($value)
							$result .= '<' . $key . '>' . htmlspecialchars($value) . '</' . $key . '>';
						else
							$result .= "<$key></$key>";
					}
				}
			}
		}

		// --------------------cmi.comments_from_learner--------------------------------------------------------------
		$rs = dbGetStMr('cmi_comments_from_learner', '*', "Course_ID= '{$Course_ID}' and User_ID= '{$User_ID}' and SCO_ID= '{$SCO_ID}'", ADODB_FETCH_ASSOC);
		if ($rs) {
			while ($row = $rs->FetchRow()) {
				$result .= '<cmi_comments_from_learner';
				foreach($row as $key => $value) {
					if ($value)
						$result .= ' ' . $key . '="'.htmlspecialchars($value).'"';
					else
						$result .= " $key=''";
				}
				$result .= '/>';
			}
		}

		// --------------------cmi.comments_from_lms--------------------------------------------------------------
		$rs = dbGetStMr('cmi_comments_from_lms', '*', "Course_ID= '{$Course_ID}' and User_ID= '{$User_ID}' and SCO_ID= '{$SCO_ID}'", ADODB_FETCH_ASSOC);
		if ($rs) {
			while ($row = $rs->FetchRow()) {
				$result .= '<cmi_comments_from_lms';
				foreach($row as $key => $value) {
					if ($value)
						$result .= ' ' . $key . '="'.htmlspecialchars($value).'"';
					else
						$result .= " $key=''";
				}
				$result .= '/>';
			}
		}

		// ---------------------------------------------------------------------------------------------
		if ($result == '')
			echo '<?xml version="1.0"?><root/>';
		else
			echo '<?xml version="1.0"?><root>'.$result.'</root>';
	}

	function GenerateRandomResultXML() {
		global $Course_ID, $User_ID, $SCO_ID;
		$result = '';

		// --------------------cmi_core--------------------------------------------------------------
		$row = dbGetStSr('cmi_core', 'isSuspended,attempt_count', "Course_ID= '{$Course_ID}' and User_ID= '{$User_ID}' and SCO_ID= '{$SCO_ID}'", ADODB_FETCH_ASSOC);
		if ($row)
			$result .= "<isSuspended>{$row['isSuspended']}</isSuspended><attempt_count>{$row['attempt_count']}</attempt_count>";
		else
			$result .= '<isSuspended>false</isSuspended><attempt_count>0</attempt_count>';

		// --------------------sequencing_random_result--------------------------------------------------------------
		$rs = dbGetStMr('sequencing_random_result', '*', "Course_ID= '{$Course_ID}' and User_ID= '{$User_ID}' and SCO_ID= '{$SCO_ID}'", ADODB_FETCH_ASSOC);
		if ($rs) {
			while ($row = $rs->FetchRow()) {
				foreach($row as $key => $value) {
					if ($key != 'Course_ID' && $key != 'SCO_ID' && $key != 'User_ID') {
						if ($value)
							$result .= '<' . $key . '>' . htmlspecialchars($value) . '</' . $key . '>';
						else
							$result .= "<$key></$key>";
					}
				}
			}
		}

		// ---------------------------------------------------------------------------------------------
		if ($result == '')
			echo '<?xml version="1.0"?><root Parent_ID="' . $SCO_ID . '"/>';
		else
			echo '<?xml version="1.0"?><root Parent_ID="' . $SCO_ID . '">'.$result.'</root>';
	}

	function GenerateGlobalObjecitvXML() {
		global $User_ID, $globalObjective_ID;
		$result = '';

		$rs = dbGetStMr('global_objectives', '*', "id= '{globalObjective_ID}' and User_ID= '{$User_ID}' and global_to_system='true'", ADODB_FETCH_ASSOC);
		if ($rs) {
			while ($row = $rs->FetchRow()) {
				foreach($row as $key => $value) {
					if ($key != 'Course_ID' && $key != 'SCO_ID' && $key != 'User_ID') {
						if ($value)
							$result .= '<' . $key . '>' . htmlspecialchars($value) . '</' . $key . '>';
						else
							$result .= "<$key></$key>";
					}
				}
			}
		}

		if ($result == '')
			echo '<?xml version="1.0"?><root/>';
		else
			echo '<?xml version="1.0"?><root>'.$result.'</root>';
	}

	function GenerateGlobalObjecitvXML2() {
		global $User_ID, $globalObjective_ID, $Course_ID;
		$result = '';

		$rs = dbGetStMr('global_objectives', '*', "id= '{globalObjective_ID}' and User_ID= '{$User_ID}' and Course_ID= '{$Course_ID}'", ADODB_FETCH_ASSOC);
		if ($rs) {
			while ($row = $rs->FetchRow()) {
				foreach($row as $key => $value) {
					if ($key != 'Course_ID' && $key != 'SCO_ID' && $key != 'User_ID') {
						if ($value)
							$result .= '<' . $key . '>' . htmlspecialchars($value) . '</' . $key . '>';
						else
							$result .= "<$key></$key>";
					}
				}
			}
		}

		if ($result == '')
			echo '<?xml version="1.0"?><root/>';
		else
			echo '<?xml version="1.0"?><root>'.$result.'</root>';
	}

	function GenerateInitialDataXML() {
		global $Course_ID, $User_ID;
		$GroupElement = '';
		$RecordSetElement = '';

		// ========cmi core table========
		$rs = dbGetStMr('cmi_core', '*', "Course_ID= '{$Course_ID}' and User_ID= '{$User_ID}'", ADODB_FETCH_ASSOC);
		if ($rs) {
			while ($row = $rs->FetchRow()) {
				$attrs = '';
				$texts = '';
				foreach($row as $key => $value) {
					if ($key == 'Course_ID' || $key == 'SCO_ID' || $key == 'User_ID')
						$attrs .=    ' ' . $key . '="' . ($value ? htmlspecialchars($value) : '') . '"';
					else if ($key=='lesson_status' || $key=='duration' || $key=='session_time' || $key=='score_raw' || $key=='score_normalized' ||  $key=='isSuspended' || $key=='success_status' || $key=='completion_status' || $key=='attempt_count' || $key=='isDisabled' || $key=='isHiddenFromChoice' || $key=='attempt_absolut_duration' || $key=='attempt_experienced_duration' || $key=='activity_absolut_duration' || $key=='activity_experienced_duration' ||  $key=='last_time' || $key=='exit_value')
						$texts .= '<' . $key . '>' . ($value ? htmlspecialchars($value) : '') . '</' . $key . '>';
				}
				$RecordSetElement .= '<Record' . $attrs . '>' . $texts . '</Record>';
			}
		}

		// ========cmi_objectives table========
		$rs = dbGetStMr('cmi_objectives', '*', "Course_ID = '{$Course_ID}' and User_ID = '{$User_ID}'", ADODB_FETCH_ASSOC);
		if ($rs) {
			while ($row = $rs->FetchRow()) {
				$attrs = '';
				$texts = '';
				foreach($row as $key => $value) {
					if ($key == 'Course_ID' || $key == 'SCO_ID' || $key == 'User_ID')
						$attrs .=    ' ' . $key . '="' . ($value ? htmlspecialchars($value) : '') . '"';
					else if ($key=='n' || $key=='id' || $key=='score_raw' || $key=='score_scaled' || $key=='success_status' ||  $key=='completion_status')
						$texts .= '<' . $key . '>' . ($value ? htmlspecialchars($value) : '') . '</' . $key . '>';
				}
				$RecordSetElement .= '<cmi_objectives' . $attrs . '>' . $texts . '</cmi_objectives>';
			}
		}

		// ========global_objectives table========
		$rs = dbGetStMr('global_objectives', '*', "User_ID = '{$User_ID}'", ADODB_FETCH_ASSOC);
		if ($rs) {
			while ($row = $rs->FetchRow()) {
				$attrs = '';
				$texts = '';
				foreach($row as $key => $value) {
					if ($key == 'Course_ID' || $key == 'SCO_ID' || $key == 'User_ID' || $key == 'id' || $key == 'global_to_system')
						$attrs .=    ' ' . $key . '="' . ($value ? htmlspecialchars($value) : '') . '"';
					else
						$texts .= '<' . $key . '>' . ($value ? htmlspecialchars($value) : '') . '</' . $key . '>';
				}
				$RecordSetElement .= '<global_objectives' . $attrs . '>' . $texts . '</global_objectives>';
			}
		}

		// ========sequencing_random_result table====Yunghsiao.2004.12.07======
		$rs = dbGetStMr('sequencing_random_result', '*', "Course_ID = '{$Course_ID}' and User_ID = '{$User_ID}'", ADODB_FETCH_ASSOC);
		if ($rs) {
			while ($row = $rs->FetchRow()) {
				$attrs = '';
				$texts = '';
				$suspendData = false;
				foreach($row as $key => $value) {
					if ($key == 'Course_ID' || $key == 'SCO_ID' || $key == 'User_ID')
						$attrs .=    ' ' . $key . '="' . ($value ? htmlspecialchars($value) : '') . '"';
					else {
						$texts .= '<' . $key . '>' . ($value ? htmlspecialchars($value) : '') . '</' . $key . '>';
						list($suspendData) = dbGetStSr('cmi_core', 'isSuspended', "Course_ID = '{$Course_ID}' and SCO_ID = '{$value}'", ADODB_FETCH_NUM);
					}
				}
				$RecordSetElement .= '<Randomize' . $attrs . '><IsSuspended>' . ($suspendData ? 'true' : 'false') . '</IsSuspended><Attempt_Count>0</Attempt_Count>' . $texts . '</Randomize>';
			}
		}
		// ====================================================================

		echo '<?xml version="1.0"?><root><Group><RecordSet>'.$RecordSetElement.'</RecordSet></Group></root>';
	}

	$xmlstr = $GLOBALS[HTTP_RAW_POST_DATA];
	$xmldoc = domxml_open_mem($xmlstr);
	$rootElement = $xmldoc->document_element();
	$ctx = xpath_new_context($rootElement);
	$DataModel = $ctx->xpath_eval('/DataModel');

	$Course_ID = intval($rootElement->get_attribute('course_ID'));
	$User_ID = $rootElement->get_attribute('user_ID');
	$res = checkUsername($User_ID);
	if ($res != 2)
	{
		GenerateXML('');
		die();
	}
	$DataModelText = $DataModel->nodeset[0]->get_content();

	switch ($DataModelText)	{
		case 'cmi.core.lesson_location' :
			list($lesson_location) = dbGetStSr('cmi_core', 'lesson_location', "Course_ID= '{$Course_ID}' and User_ID='{$User_ID}' order by last_time desc", ADODB_FETCH_NUM);
			GenerateXML($lesson_location);
			break;
		case 'is.first.enter.sco' :
			$SCO_ID = $rootElement->get_attribute('sco_ID');
			list($SCO_ID) = dbGetStSr('cmi_core', 'SCO_ID', "Course_ID= '{$Course_ID}' and User_ID= '{$User_ID}' and SCO_ID='{$SCO_ID}'", ADODB_FETCH_NUM);
			GenerateXML($SCO_ID);
			break;
		//================Yunghsiao.2004.12.02==========================
		case "set.initial.data" :
		  GenerateInitialDataXML();    // 此Function會至idea資料庫中cmi_core、cmi_objectives、global_objectives、sequencing_random_result四個Table取得資料並將所取得的資料組合成XML
		  break;
		//==============================================================
		case 'set.sco.initial.data' :
			$SCO_ID = $rootElement->get_attribute('sco_ID');
			GenerateSCOInitialDataXML();
			break;
		case 'CheckSuspend' :
			list($SCO_ID) = dbGetStSr('cmi_core', 'SCO_ID', "Course_ID= '{$Course_ID}' and User_ID= '{$User_ID}' and isSuspended='true'", ADODB_FETCH_NUM);
			GenerateXML($SCO_ID);
			break;
		case 'randomResult' :
			$SCO_ID = $rootElement->get_attribute('sco_ID');
			GenerateRandomResultXML();
			break;
		case 'globalObjective' : //shared objective
			$globalObjective_ID = $rootElement->get_attribute('objective_ID');
			GenerateGlobalObjecitvXML();
			break;
		case 'globalObjective2' : //shared objective globalToSystem = false
			$SCO_ID = $rootElement->get_attribute('sco_ID');
			$globalObjective_ID = $rootElement->get_attribute('objective_ID');
			GenerateGlobalObjecitvXML2();
			break;
	}
?>
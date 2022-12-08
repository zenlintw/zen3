<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lib/filter_spec_char.php');

	/**
	 * 將原本資策會的各個分散的CheckXXXXX整合成一個function
	 * function -> $table, $extra
	 * CheckPreferenceMode->cmi_student_preference, ''
	 * CheckMode->cmi_core, ''
	 * CheckObjectiveMode->cmi_objectives, n
	 * CheckCommentsFromLearnerMode->cmi_comments_from_learner, n
	 * CheckInteractionMode->cmi_interactions, n
	 * CheckInteractionObjMode->cmi_interactions_objectives, n, c
	 * CheckInteractionResMode->cmi_interactions_correct_response, n, m
	 */
	function CheckModes($table, $extra = '') {
		global $sysConn, $Course_ID, $User_ID, $SCO_ID;
		$Mode = '';

		$where = "Course_ID= '{$Course_ID}' and User_ID= '{$User_ID}' and SCO_ID= '{$SCO_ID}'";
		if ($extra != '')
			$where .= ' and ' . $extra;

		list($count) = dbGetStSr($table, 'count(*)', $where, ADODB_FETCH_NUM);

		if ($count)
			$Mode = 'Update';
		else if ($SCO_ID != '' && $User_ID != '' && $Course_ID != '')
			$Mode = 'Insert';

		return $Mode;
	}

	/**
	 * 將原本資策會的兩個CheckGLobalObjectiveMode整合成一個
	 * CheckGlobalObjectiveMode -> $extra = '';
	 * CheckGlobalObjectiveMode2 -> $extra = 'and Course_ID="' . $Course_ID . '"'
	 */
	function CheckGlobalObjectiveMode($objectiveID, $extra = false) {
		global $sysConn, $Course_ID, $User_ID, $SCO_ID;
		$Mode = '';

		if ($extra)
			list($count) = dbGetStSr('global_objectives', 'count(*)', "id= '{$objectiveID}' and User_ID= '{$User_ID}' and Course_ID = '{$Course_ID}'", ADODB_FETCH_NUM);
		else
			list($count) = dbGetStSr('global_objectives', 'count(*)', "id= '{$objectiveID}' and User_ID= '{$User_ID}'", ADODB_FETCH_NUM);

		if ($count)
			$Mode = 'Update';
		else if ($SCO_ID != '' && $User_ID != '' && $Course_ID != '')
			$Mode = 'Insert';

		return $Mode;
	}

	$xmlstr            = $GLOBALS[HTTP_RAW_POST_DATA];
	$xmldoc            = domxml_open_mem($xmlstr);
	$ctx               = xpath_new_context($xmldoc);
	$rootElement       = $xmldoc->document_element();
	$rootElement->name = '$root';
	// echo $xmldoc->dump_mem(true);
	$Scorm_Type        = $rootElement->get_attribute('Scorm_Type');
	$Message_Type      = $rootElement->get_attribute('Message_Type');
	$Course_ID         = intval($rootElement->get_attribute('course_ID'));
	$User_ID           = $rootElement->get_attribute('user_ID');
	$res               = checkUsername($User_ID);
	if ($res != 2) die();
	$SCO_ID            = Filter_Spec_char($rootElement->get_attribute('sco_ID'));

	chkSchoolId('global_objectives');
	// echo 'Message type = ' , $Message_Type, '; scorm type = ' , $Scorm_Type, '; course id = ' , $Course_ID, '; user id = ' , $User_ID, '; sco id = ' , $SCO_ID;
	// 如果是第一次進入sco就要用insert,否則就用update

	// ---------cmi core-------------------------------------------------------------------------
	// save global objectives Info
	switch ($Scorm_Type) {
		case 'globalObective' :
			$global_objective = $ctx->xpath_eval('//global_objective');
			foreach ($global_objective->nodeset as $node) {
				$objectiveID                = $node->get_attribute('objectiveID');
				$globalToSystem             = $node->get_attribute('globalToSystem');
				$objectiveProgressStatus    = $node->get_attribute('objectiveProgressStatus');
				$objectiveSatisfiedStatus   = $node->get_attribute('objectiveSatisfiedStatus');
				$objectiveMeasureStatus     = $node->get_attribute('objectiveMeasureStatus');
				$objectiveNormalizedMeasure = $node->get_attribute('objectiveNormalizedMeasure');

				if ($globalToSystem == 'true') {
					$mode = CheckGlobalObjectiveMode($objectiveID);
					if ($mode == 'Update')
						$SQLString = "Update global_objectives set global_to_system='{$globalToSystem}',ProgressStatus='{$objectiveProgressStatus}',SatisfiedStatus='{$objectiveSatisfiedStatus}',MeasureStatus='{$objectiveMeasureStatus}',NormalizedMeasure='{$objectiveNormalizedMeasure}' where id= '{$objectiveID}' and User_ID='{$User_ID}'";
					else if ($mode == 'Insert')
						$SQLString = "Insert Into global_objectives values('{$Course_ID}','{$SCO_ID}','{$User_ID}','{$objectiveID}','{$globalToSystem}','{$objectiveProgressStatus}','{$objectiveSatisfiedStatus}','{$objectiveMeasureStatus}','{$objectiveNormalizedMeasure}')";
					if ($mode != '')
						$sysConn->Execute($SQLString);
				}
				else if ($globalToSystem == 'false'){
					$mode = CheckGlobalObjectiveMode($objectiveID, true);
					if ($mode == 'Update')
						$SQLString = "Update global_objectives set global_to_system='{$globalToSystem}',ProgressStatus='{$objectiveProgressStatus}',SatisfiedStatus='{$objectiveSatisfiedStatus}',MeasureStatus='{$objectiveMeasureStatus}',NormalizedMeasure='{$objectiveNormalizedMeasure}' where id= '{$objectiveID}' and User_ID='{$User_ID}' and Course_ID = '{$Course_ID}'";
					else if ($mode == 'Insert')
						$SQLString = "Insert Into global_objectives values('{$Course_ID}','{$SCO_ID}','{$User_ID}','{$objectiveID}','{$globalToSystem}','{$objectiveProgressStatus}','{$objectiveSatisfiedStatus}','{$objectiveMeasureStatus}','{$objectiveNormalizedMeasure}')";
					if ($mode != '')
						$sysConn->Execute($SQLString);
				}

			}
			$rootElement->set_attribute('SQL', $SQLString);
			break;
		case 'Start' :
			$exit_node = $ctx->xpath_eval('/root/cmi_exit_value');
			if ($exit_node && $exit_node->nodeset[0]) {
				$exit_value1 = $exit_node->nodeset[0]->get_content();
				$SQLString = "Update cmi_core set exit_value='{$exit_value1}' where Course_ID='{$Course_ID}' and User_ID='{$User_ID}' and SCO_ID='{$SCO_ID}'";
				$sysConn->Execute($SQLString);
			}
			break;
		case 'not' :
			if ($Message_Type == 'randomResult') {
				$SQLString = "delete from sequencing_random_result where SCO_ID='{$SCO_ID}'";
				$sysConn->Execute($SQLString);
				$result = $ctx->xpath_eval('//result');
				foreach ($result->nodeset as $node) {
					$tempResultValue = $node->get_content();
					$SQLString = "Insert Into sequencing_random_result values('{$Course_ID}','{$SCO_ID}','{$User_ID}' , '{$tempResultValue}')";
					$sysConn->Execute($SQLString);
				}
			}
			break;
		case 'sco' :
			if ($Message_Type == 'LMSCommit') {
				$child_nodes = $rootElement->child_nodes();
				foreach ($child_nodes as $nodes) {
				    ${$nodes->tagname} = $nodes->get_content();
				}

				$timeString = date('Y-m-d H:i:s');

				// ------------cmi core------------------------------------------------
				$mode = CheckModes('cmi_core');
				if ($mode == 'Update') {
					$SQLString = "Update cmi_core set lesson_location='{$cmi_core_lesson_location}',credit='{$cmi_core_credit}',lesson_status ='{$cmi_core_lesson_status}',duration='{$duration}',lesson_mode='{$cmi_core_lesson_mode}',exit_value='{$cmi_core_exit_value}',session_time = '{$cmi_core_session_time}',suspend_data='{$cmi_suspend_data}',launch_data='{$cmi_launch_data}',entry='{$cmi_core_entry}',score_raw='{$cmi_core_score_raw}',score_min='{$cmi_core_score_min}',score_max='{$cmi_core_score_max}',score_normalized='{$cmi_core_score_normalized}',last_time='{$timeString}',success_status='{$cmi_core_success_status}',completion_status='{$cmi_core_completion_status}',progress_measure='{$cmi_core_progress_measure}' where Course_ID= '{$Course_ID}' and User_ID='{$User_ID}' and SCO_ID='{$SCO_ID}'";
				}
				else if ($mode == 'Insert'){
					$SQLString = "Insert Into cmi_core values('{$Course_ID}','{$SCO_ID}','{$User_ID}' , '{$Scorm_Type }' , '{$cmi_core_lesson_location}','{$cmi_core_credit}','{$cmi_core_lesson_status}','{$duration}','{$cmi_core_lesson_mode}','{$cmi_core_exit_value}','{$cmi_core_session_time}','{$cmi_suspend_data}','{$cmi_launch_data}','{$cmi_core_entry}','{$cmi_core_score_raw}','{$cmi_core_score_min}','{$cmi_core_score_max}','{$cmi_core_score_normalized}','{$timeString}','','{$cmi_core_success_status}','{$cmi_core_completion_status}','{$cmi_core_attempt_count}','{$cmi_core_isDisabled}','{$cmi_core_isHiddenFromChoice}','{$cmi_core_attempt_absolut_duration}','{$cmi_core_attempt_experienced_duration}','{$cmi_core_activity_absolut_duration}','{$cmi_core_activity_experienced_duration }','{$cmi_core_completion_threshold }','{$cmi_core_progress_measure}')";
				}
				if ($mode != '')
					$sysConn->Execute($SQLString);

				// ------------cmi student preference-----------------------------------
				$mode = CheckModes('cmi_student_preference');
				if ($mode == 'Update') {
					$SQLString = "Update cmi_student_preference set audio='{$cmi_student_preference_audio}',language='{$cmi_student_preference_language}',speed='{$cmi_student_preference_speed}',text='{$cmi_student_preference_text}' where Course_ID= '{$Course_ID}' and User_ID='{$User_ID}' and SCO_ID='{$SCO_ID}'";
				}
				else if ($mode == 'Insert'){
					$SQLString = "Insert Into cmi_student_preference values('{$Course_ID}','{$SCO_ID}','{$User_ID}','{$cmi_student_preference_audio}','{$cmi_student_preference_language}','{$cmi_student_preference_speed}','{$cmi_student_preference_text}')";
				}
				if ($mode != '')
					$sysConn->Execute($SQLString);


				// ------------cmi interaction-----------------------------------
				$tempInteraction = $ctx->xpath_eval('//cmi_interactions');
				$rootElement->set_attribute('tempInteractionLength', count($tempInteraction->nodeset));
				foreach($tempInteraction->nodeset as $node) {
					$interactions_n                = $node->get_attribute('n');
					$interactions_id               = $node->get_attribute('id');
					$interactions_timestamp        = $node->get_attribute('timestamp');
					$interactions_type             = $node->get_attribute('type');
					$interactions_weighting        = $node->get_attribute('weighting');
					$interactions_learner_response = $node->get_attribute('learner_response');
					$interactions_result           = $node->get_attribute('result');
					$interactions_latency          = $node->get_attribute('latency');
					$interactions_description      = $node->get_attribute('description');
					$interactions_objectives_c     = $node->get_attribute('objectives._count');
					$interactions_correct_res_m    = $node->get_attribute('correct_responses._count');

					$mode = CheckModes('cmi_interactions', 'n = ' . $interactions_n);
					if ($mode == 'Update') {
						$SQLString = "Update cmi_interactions set id='{$interactions_id}',timestamp='{$interactions_timestamp}',type='{$interactions_type}',weighting='{$interactions_weighting}',learner_response='{$interactions_learner_response}',result='{$interactions_result}',latency='{$interactions_latency}',description='{$interactions_description}',objectives_count={$interactions_objectives_c},correct_rese_count={$interactions_correct_res_m} where Course_ID= '{$Course_ID}' and User_ID='{$User_ID}' and SCO_ID='{$SCO_ID}' and n={$interactions_n}";
					}
					else if ($mode == 'Insert') {
						$SQLString = "Insert Into cmi_interactions values('{$Course_ID}','{$SCO_ID}','{$User_ID}',{$interactions_n},'{$interactions_id}','{$interactions_timestamp}','{$interactions_type}', '{$interactions_weighting}','{$interactions_learner_response}','{$interactions_result}','{$interactions_latency}','{$interactions_description}',{$interactions_objectives_c},{$interactions_correct_res_m})";
					}
					if ($mode != '')
						$sysConn->Execute($SQLString);
				}


				// ------------------interactions.objectives--------------------
				$tempInteraction = $ctx->xpath_eval('//cmi_interactions_objectives');
				$rootElement->set_attribute('tempInteractionLength', count($tempInteraction->nodeset));
				foreach($tempInteraction->nodeset as $node) {
					$interObj_n             = $node->get_attribute('n');
					$interObj_c             = $node->get_attribute('c');
					$interObj_id            = $node->get_attribute('id');
					$interObj_objectives_id = $node->get_attribute('objectives_id');
					$rootElement->setAttribute('SQLString', $SQLString);

					$mode = CheckModes('cmi_interactions_objectives', 'n = ' . $interObj_n . ' and c = ' . $interObj_c);
					if ($mode == 'Update') {
						$SQLString = "Update cmi_interactions_objectives set objectives_id='{$interObj_objectives_id}' where Course_ID= '{$Course_ID}' and User_ID='{$User_ID}' and SCO_ID='{$SCO_ID}' and n={$interObj_n} and c={$interObj_c}";
					}
					else if ($mode == 'Insert') {
						$SQLString = "Insert Into cmi_interactions_objectives values('{$Course_ID}','{$SCO_ID}','{$User_ID}',{$interObj_n},'{$interObj_id}',{$interObj_c},'{$interObj_objectives_id}')";
					}
					if ($mode != '')
						$sysConn->Execute($SQLString);
				}


				// -------------------interactions.correct_responses---------------------
				$tempInteraction = $ctx->xpath_eval('//cmi_interactions_correct_responses');
				$rootElement->set_attribute('tempInteractionLength', count($tempInteraction->nodeset));
				foreach($tempInteraction->nodeset as $node) {
					$interRes_n       = $node->get_attribute('n');
					$interRes_m       = $node->get_attribute('m');
					$interRes_id      = $node->get_attribute('id');
					$interRes_pattern = $node->get_attribute('pattern');

					$mode = CheckModes('cmi_interactions_correct_response', 'n = ' . $interRes_n . ' and m = ' . $interRes_m);
					if ($mode == 'Update') {
						$SQLString = "Update cmi_interactions_correct_response set pattern='{$interRes_pattern}' where Course_ID= '{$Course_ID}' and User_ID='{$User_ID}' and SCO_ID='{$SCO_ID}' and n={$interRes_n} and m={$interRes_m}";
					}
					else if ($mode == 'Insert') {
						$SQLString = "Insert Into cmi_interactions_correct_response values('{$Course_ID}','{$SCO_ID}','{$User_ID}',{$interRes_n},'{$interRes_id}',{$interRes_m},'{$interRes_pattern}')";
					}
					if ($mode != '')
						$sysConn->Execute($SQLString);
				}

				// ---------cmi objectives--------------------------------------------------------------------
				$temp = $ctx->xpath_eval('//cmi_objectives');
				$rootElement->set_attribute('templength', count($temp->nodeset));
				foreach($temp->nodeset as $node) {
					$objectives_n                 = $node->get_attribute('n');
					$objectives_id                = $node->get_attribute('id');
					$objectives_score_raw         = $node->get_attribute('score_raw');
					$objectives_score_max         = $node->get_attribute('score_max');
					$objectives_score_min         = $node->get_attribute('score_min');
					$objectives_score_scaled      = $node->get_attribute('score_scaled');
					$objectives_success_status    = $node->get_attribute('success_status');
					$objectives_completion_status = $node->get_attribute('completion_status');
					$objectives_progress_measure  = $node->get_attribute('progress_measure');
					$description                  = $node->get_attribute('description');

					$mode = CheckModes('cmi_objectives', 'n = ' . $objectives_n);
					if ($mode == 'Update') {
						$SQLString = "Update cmi_objectives set id='{$objectives_id}',score_raw='{$objectives_score_raw}',score_min='{$objectives_score_min}',score_max='{$objectives_score_max}',score_scaled='{$objectives_score_scaled}',success_status='{$objectives_success_status}',completion_status='{$objectives_completion_status}',progress_measure='{$objectives_progress_measure}',description= '{$description}' where Course_ID= '{$Course_ID }' and User_ID='{$User_ID}' and SCO_ID='{$SCO_ID}' and n={$objectives_n}";
					}
					else if ($mode == 'Insert') {
						$SQLString = "Insert Into cmi_objectives values('{$Course_ID}','{$SCO_ID}','{$User_ID}','{$objectives_n}','{$objectives_id}','{$objectives_score_raw}','{$objectives_score_min}','{$objectives_score_max}','{$objectives_score_scaled}','{$objectives_success_status}','{$objectives_completion_status}','{$objectives_progress_measure}','{$description}')";
					}
					if ($mode != '')
						$sysConn->Execute($SQLString);
				}


				// ---------cmi comments from learner-------------------------------------------------------------
				$cmi_comments_from_learner = $ctx->xpath_eval('//cmi_comments_from_learner');
				foreach($cmi_comments_from_learner->nodeset as $node) {
					$cmi_comments_from_learner_n         = $node->get_attribute('n');
					$cmi_comments_from_learner_comment   = $node->get_attribute('comment');
					$cmi_comments_from_learner_location  = $node->get_attribute('location');
					$cmi_comments_from_learner_timestamp = $node->get_attribute('timestamp');

					$mode = CheckModes('cmi_comments_from_learner', 'n = ' . $cmi_comments_from_learner_n);
					if ($mode == 'Update') {
						$SQLString = "Update cmi_comments_from_learner set comment='{$cmi_comments_from_learner_comment}',location='{$cmi_comments_from_learner_location}',timestamp='{$cmi_comments_from_learner_timestamp}' where Course_ID= '{$Course_ID}' and User_ID='{$User_ID}' and SCO_ID='{$SCO_ID}' and n={$cmi_comments_from_learner_n}";
					}
					else if ($mode == 'Insert') {
						$SQLString = "Insert Into cmi_comments_from_learner values('{$Course_ID}','{$SCO_ID}','{$User_ID}','{$cmi_comments_from_learner_n}','{$cmi_comments_from_learner_comment}','{$cmi_comments_from_learner_location}','{$cmi_comments_from_learner_timestamp}')";
					}
					if ($mode != '')
						$sysConn->Execute($SQLString);
				}

				// ----------------------------------------------------------------------------
			}
			else if ($Message_Type == 'ActivityStatus') {
				$child_nodes = $rootElement->child_nodes();
				foreach ($child_nodes as $nodes) {
				    ${$nodes->tagname} = $nodes->get_content();
				}

				$timeString = date('Y-m-d H:i:s');

				$mode = CheckModes('cmi_core');
				if ($mode == 'Update') {
					$SQLString = "Update cmi_core set isSuspended='{$isSuspended}',attempt_count='{$cmi_core_attempt_count}',isDisabled='{$cmi_core_isDisabled}',isHiddenFromChoice='{$cmi_core_isHiddenFromChoice}',attempt_absolut_duration='{$cmi_core_attempt_absolut_duration}',attempt_experienced_duration='{$cmi_core_attempt_experienced_duration}',activity_absolut_duration='{$cmi_core_activity_absolut_duration}',activity_experienced_duration='{$cmi_core_activity_experienced_duration}',progress_measure='' where Course_ID= '{$Course_ID}' and User_ID='{$User_ID}' and SCO_ID='{$SCO_ID}'";
				}
				else if ($mode == 'Insert'){
					$SQLString = "Insert Into cmi_core values('{$Course_ID}','{$SCO_ID}','{$User_ID}', '{$Scorm_Type}' ,'','','','','','','','','','','','','','','{$timeString}','{$isSuspended}','','','{$cmi_core_attempt_count}','{$cmi_core_isDisabled}','{$cmi_core_isHiddenFromChoice}','{$cmi_core_attempt_absolut_duration}','{$cmi_core_attempt_experienced_duration}','{$cmi_core_activity_absolut_duration}','{$cmi_core_activity_experienced_duration}','0','')";
				}
				if ($mode != '')
					$sysConn->Execute($SQLString);
			}
			break;
		case 'sca':
		case 'aggregation' :
			$child_nodes = $rootElement->child_nodes();
			foreach ($child_nodes as $nodes) {
			    ${$nodes->tagname} = $nodes->get_content();
			}

			$exit_value = ($isSuspended == 'true' ? 'suspend' : 'logout');
			$timeString = date('Y-m-d H:i:s');

			$mode = CheckModes('cmi_core');
			if ($mode == 'Update') {
				$SQLString = "Update cmi_core set lesson_location='',credit='',lesson_status ='',duration='',lesson_mode='',exit_value='{$exit_value}',session_time = '',suspend_data='',launch_data='',entry='',score_raw='',score_min='',score_max='',score_normalized='',last_time='{$timeString}', isSuspended='{$isSuspended}', success_status='{$cmi_core_success_status}', completion_status='{$cmi_core_completion_status}', attempt_count='{$cmi_core_attempt_count}', isDisabled='{$cmi_core_isDisabled}', isHiddenFromChoice='{$cmi_core_isHiddenFromChoice}', attempt_absolut_duration='{$cmi_core_attempt_absolut_duration}', attempt_experienced_duration='{$cmi_core_attempt_experienced_duration}', activity_absolut_duration='{$cmi_core_activity_absolut_duration}', activity_experienced_duration='{$cmi_core_activity_experienced_duration}' where Course_ID= '{$Course_ID}' and User_ID='{$User_ID}' and SCO_ID='{$SCO_ID}'";
			}
			else if ($mode == 'Insert'){
				$SQLString = "Insert Into cmi_core values('{$Course_ID}','{$SCO_ID}','{$User_ID}', '{$Scorm_Type}' ,'','','','','','{$exit_value}','','','','','','','','','{$timeString}','{$isSuspended}','','','{$cmi_core_attempt_count}','{$cmi_core_isDisabled}','{$cmi_core_isHiddenFromChoice}','{$cmi_core_attempt_absolut_duration}','{$cmi_core_attempt_experienced_duration}','{$cmi_core_activity_absolut_duration}','{$cmi_core_activity_experienced_duration}','0','')";
			}
			if ($mode != '')
				$sysConn->Execute($SQLString);
			break;
	}
?>

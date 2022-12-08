<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *      Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                               *
	 *                                                                                                *
	 *      Programmer: Wiseguy Liang                                                                 *
	 *      Creation  : 2003/09/25                                                                    *
	 *      work for  :                                                                               *
	 *      work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                        *
	 *                                                                                                *
	 **************************************************************************************************/

	ob_start();
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

/*
	if ($sysSession->username == 'joe')
	{
		$sysSession->realname = 'Student, Joe';
	}
	elseif($sysSession->username == 'mary')
	{
		$sysSession->realname = 'Learner, Mary';
	}
*/

	if (eregi('^[0-9A-Z_.-]+$', $_GET['activity_id']))
	{
		list($cmidata) = dbGetStSr('WM_scorm_cmi', 'cmi_data', sprintf('course_id=%u and username="%s" and sco_id="%s"', $sysSession->course_id, $sysSession->username, $_GET['activity_id']), ADODB_FETCH_NUM);
		if (empty($cmidata))
		{
			$scorm_version = $_GET['SCORM_VERSION'] && $_GET['SCORM_VERSION'] == '1.2' ? '/scorm_rte_data.xml' : '/scorm_cmi_2004.xml';
			$cmidata = file_get_contents(dirname(__FILE__) . $scorm_version); // die('<errorlevel>2</errorlevel>');
			if (!$dom = domxml_open_mem(preg_replace('/\bxmlns\s*=\s*"[^"]*"/isU', '', $cmidata))) die('<errorlevel>3</errorlevel>');
			$ctx = xpath_new_context($dom);

			$manifest = domxml_open_mem(preg_replace('/\bxmlns\s*=\s*"[^"]*"/isU', '', $sysConn->GetOne("select content from WM_term_path where course_id={$sysSession->course_id} order by serial desc")));
			$ctx0 = xpath_new_context($manifest);

			$ctx0->xpath_register_ns('adlcp', 'http://www.adlnet.org/xsd/adlcp_rootv1p2');
			$ret0 = $ctx0->xpath_eval('//item[@identifier="' . $_GET['activity_id'] . '"]/adlcp:*');
			if (is_array($ret0->nodeset) && count($ret0->nodeset))
			{
				foreach($ret0->nodeset as $condition)
				{
					switch($condition->node_name())
					{
						case 'maxtimeallowed':
							$ret = $ctx->xpath_eval('/sco/cmi/max_time_allowed') AND $node = $ret->nodeset[0];
							$node->set_content(vsprintf('PT%dH%dM%.2fS', explode(':', $condition->get_content())));
							break;
						case 'timelimitaction':
							$ret = $ctx->xpath_eval('/sco/cmi/time_limit_action') AND $node = $ret->nodeset[0];
							$node->set_content($condition->get_content());
							break;
						case 'datafromlms':
							$ret = $ctx->xpath_eval('/sco/cmi/launch_data') AND $node = $ret->nodeset[0];
							$node->set_content($condition->get_content());
							break;
						case 'masteryscore':
							$ret = $ctx->xpath_eval('/sco/cmi/scaled_passing_score') AND $node = $ret->nodeset[0];
							$node->set_content($condition->get_content());
							break;
					}
				}
			}
		}
		else
		{
			if (!$dom = domxml_open_mem(preg_replace('/\bxmlns\s*=\s*"[^"]*"/isU', '', $cmidata))) die('<errorlevel>3</errorlevel>');
			$ctx = xpath_new_context($dom);
		}

		$ret = $ctx->xpath_eval('/sco/cmi/credit') AND $node = $ret->nodeset[0];
		if (is_object($node) && !in_array($node->get_content(), array('credit', 'no-credit')))
		{
			list($isCredit) = dbGetStSr('WM_term_course', 'credit', "course_id={$sysSession->course_id}", ADODB_FETCH_NUM);
			if ($node->has_child_nodes()) $node->remove_child($node->first_child());
			$node->set_content($isCredit ? 'credit' : 'no-credit');
		}

		$ret = $ctx->xpath_eval('/sco/cmi/mode') AND $node = $ret->nodeset[0];
		if (is_object($node))
			if (!$node->has_child_nodes() || $node->get_content() == '')
				$node->set_content($isCredit ? 'normal' : 'browse');
			elseif($node->get_content() == 'normal')
				$node->remove_child($node->first_child()) AND $node->set_content('review');

		$ret = $ctx->xpath_eval('/sco/cmi/learner_id') AND $node = $ret->nodeset[0];
		if (is_object($node) && (!$node->has_child_nodes() || $node->get_content() == ''))
			$node->set_content($sysSession->username);

		$ret = $ctx->xpath_eval('/sco/cmi/learner_name') AND $node = $ret->nodeset[0];
		if (is_object($node) && (!$node->has_child_nodes() || $node->get_content() == ''))
			$node->set_content($sysSession->realname);

		$ret = $ctx->xpath_eval('/sco/cmi/exit') AND $node = $ret->nodeset[0];
		if (is_object($node) && $node->get_content() == 'suspend')
		{
			$ret = $ctx->xpath_eval('/sco/cmi/entry') AND $node = $ret->nodeset[0];
			if (is_object($node))
				if (!$node->has_child_nodes())
					$node->set_content('resume');
				else
					$node->remove_child($node->first_child()) AND $node->set_content('resume');
		}

		ob_end_clean();
		echo $dom->dump_mem();
	}
	else
		die('<errorlevel>1</errorlevel>');
?>

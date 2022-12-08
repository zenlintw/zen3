<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/scorm.php');
?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<script>
	if (parent.engine.location == 'about:blank')
		parent.engine.location.replace('/learn/scorm/sequencing/engine.php');
	if (parent.tocstatus.location == 'about:blank')
		parent.tocstatus.location.replace('/learn/scorm/sequencing/status_control.php');
	if (parent.check.location == 'about:blank')
		parent.check.location.replace('/learn/scorm/sequencing/check_rules.php');
	if (parent.functions.location == 'about:blank')
		parent.functions.location.replace('/learn/scorm/sequencing/engine_functions.php');
	
	course_ID = "<?=$sysSession->course_id?>";	// 給engine.php作設定
		
	function loading() {
		if (parent.tocstatus.location == 'about:blank' ||
			parent.check.location == 'about:blank'     ||
			parent.functions.location == 'about:blank' ||
			parent.engine.location == 'about:blank')
			setTimeout('loading()', '1000');
		else {
			try {	// 預防有些站台網路太慢會出現錯誤
				parent.tocstatus.init_status_control();
				parent.check.init_check_rules();
				parent.functions.init_engine_functions();
				parent.engine.init_engine();
				chkObjects();
			}
			catch(e) {
				setTimeout('loading()', '1000');
				return;
			}
			parent.s_catalog.location.replace('/learn/scorm/InitialSCORM.php');
		}
	}
	
	function chkObjects()
	{
		re_chk = false;
		if (typeof(parent.tocstatus.statusObj)        == 'undefined') {parent.tocstatus.init_status_control();   re_chk = true;}
		if (typeof(parent.check.checkrules)           == 'undefined') {parent.check.init_check_rules();          re_chk = true;}
		if (typeof(parent.engine.SequencingEngineObj) == 'undefined') {parent.engine.init_engine();              re_chk = true;}
		if (typeof(parent.functions.enfunctions)      == 'undefined') {parent.functions.init_engine_functions(); re_chk = true;}
		if (re_chk) setTimeout('chkObjects()', '1000');
	}
	
	setTimeout('loading()', '0');
	if (parent.document.getElementById('envClassRoom').cols != '200,*')
		parent.FrameExpand(1,true,0);

</script>
</head>
<body>
<h2 align="center"><br><?=$MSG['wait_msg'][$sysSession->lang]?></h2>
</body>
</html>

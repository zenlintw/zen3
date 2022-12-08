<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                         *
	 *		Creation  : 2003/04/10                                                            *
	 *		work for  : 批改試卷功能之 frame page                                             *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *                                                                                                *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/' . QTI_which . '_teach.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	//ACL begin
	if (QTI_which == 'exam') {
		$sysSession->cur_func='1600300100';
	}
	else if (QTI_which == 'homework') {
		$sysSession->cur_func='1700300100';
	}
	else if (QTI_which == 'questionnaire') {
		$sysSession->cur_func='1800300200';
	}
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

	}
	//ACL end

	if (!defined('QTI_env'))
		list($foo, $topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
	else
		$topDir = QTI_env;

	$course_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id; //10000000;

	if (!isset($_POST['ticket'])) {	// 檢查 ticket 是否存在
	   wmSysLog($sysSession->cur_func, $course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'Access Denied!');
	   die('Access denied.');
	}
	$ticket = md5(sysTicketSeed . $course_id . $_POST['referer']);		// 產生 ticket
	if ($ticket != $_POST['ticket']) {	// 檢查 ticket
	   wmSysLog($sysSession->cur_func, $course_id , 0 , 2, 'auto', $_SERVER['PHP_SELF'], 'Fake Ticket!');
	   die('Fake ticket.');
	}
	if (!eregi('^[0-9A-Z_]+$', $_POST['lists'])) {	// 檢查 lists
	   wmSysLog($sysSession->cur_func, $course_id , 0 , 3, 'auto', $_SERVER['PHP_SELF'], 'Fake lists:' . $_POST['lists']);
	   die('Fake lists.');
	}

	$ticket = md5(sysTicketSeed . $course_id . $_POST['lists']);
	
	// 開始 output HTML
    showXHTML_head_B('Correct');
	
	$js = <<< EOB
function correctOne()
	{
		var url = rtop.location.href;
		setTimeout('rtop.location.replace("' + url + '")', 2000);
		left.document.forms[0].submit();
	}
	
		function divunblock(){	
			 ua = navigator.userAgent.toLowerCase();

			 $('div.blockUI').remove();
			
		}
		function hw_all(hwid,uid){
			 ua = navigator.userAgent.toLowerCase();
			 if(typeof uid == 'undefined') {
				uid = '';
			 }
			var delay=0;
			$('#fs1').block({
				message: '<h1>{$MSG['tar_hw_wait'][$sysSession->lang]}</h1>',
		                 css: { 
                                            border: 'none', 
                                            padding: '15px', 
                                            backgroundColor: '#000', 
                                            color: '#fff',
					opacity: .5, 
                                            width : '40%'
                                    }
			});	
			$("#fs1").before($('#fs1').find('div'));
			$('.blockMsg').css('left','25%');
			$('.blockMsg').css('top','30%');
			delay = 2000;
			$.post("TarAttach.php", { hwid: hwid ,examinee : uid},
				   function(response){
					$(window.frames['left'].document).find('#examinee').val(uid);
					$(window.frames['left'].document).find('#exam_id').val(hwid);
					$(window.frames['left'].document).find("#formDownloadAll").submit();

					setTimeout("divunblock()", delay);
			});
		}

EOB;

    showXHTML_script('include', '/lib/jquery/jquery-1.7.2.min.js');	
    showXHTML_script('include', '/lib/jquery.blockUI.js');	
	showXHTML_script('inline', $js);
	showXHTML_head_E();
?>
<html>

<head>
<title>Correct</title>
<script>
	function correctOne()
	{
		var url = rtop.location.href;
		setTimeout('rtop.location.replace("' + url + '")', 2000);
		left.document.forms[0].submit();
	}
</script>
</head>

<frameset cols="200,*" framespacing="1" frameborder="no" bordercolor="black" id="fs1">
  <frame name="left" scrolling="auto" frameborder="no" target="rtop" src="exam_correct_user.php?<?php echo $ticket . '+' . $_POST['lists'];?>">
  <frameset rows="18%,*" framespacing="1" frameborder="no" bordercolor="black" noresize>
    <frame name="rtop" frameborder="no" target="rbottom" src="about:blank">
    <frame name="rbottom" frameborder="no" src="about:blank">
  </frameset>
  <noframes>
  <body>

  <p>此網頁使用框架,但是您的瀏覽器並不支援.</p>

  </body>
  </noframes>
</frameset>

</html>
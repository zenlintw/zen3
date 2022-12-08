<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lang/learn_path.php');
	require_once(sysDocumentRoot . '/lang/scorm.php');

	if (!aclVerifyPermission(700300400, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	$icon = array();
	for ($i = 1; $i <= 7; $i++)
	{
		$icon[$i] = sprintf('/learn/scorm/images/orange/%d.gif', $i);
	}

    $pw = 12;
    $ph1 = 34;
    $ph2 = 34;
    $tl = '';
    $pd = '';
    if (defined('sysEnableMooc') && (sysEnableMooc > 0)) {
        // 啟用 MOOC
        $pw = 0;
        $ph1 = 34;
        $ph2 = 45;
        $tl = ' left: 0;';
        $pd = ' style="padding: 0;"';
    }
?>
<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<?php
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
?>
<STYLE TYPE="text/css">
<!--
a:link { color: #000000; text-decoration: none }
a:active { color: #000000; text-decoration: none }
a:visited { color: #000000; text-decoration: none }
a:hover { color: #000000; text-decoration: none; position: relative; top: 2pt; left: 2pt;}
//-->
</STYLE>
<script type="text/javascript" language="JavaScript" src="/lib/xmlextras.js"></script>
<script>

var obj = window.scrollbars;
var course_ID = "<?=$sysSession->course_id?>";
var student_id = "<?=$sysSession->username?>";

if ((typeof(obj) == "object") && (obj.visible == true)) {
	obj.visible = false;
}

function adjustFrameHeight(){
	if (navigator.userAgent.indexOf('MSIE') > -1)
	{
		var height = document.body.clientHeight - document.getElementById('toolbar').clientHeight - <?=$ph2?>;
		if (height > 0) document.getElementById('treePanel').style.height = (document.getElementById('CGroup').style.height = height) + <?=$ph1?>;
		var width = document.body.clientWidth - <?=$pw?>;
		if (width > 0){
			document.getElementById('CGroup').style.width = (width-2) + 'px';
			document.getElementById('treePanel').style.width = width + 'px';
		}
	}
	else
	{
		document.getElementById('CGroup').style.height = (document.getElementById('treePanel').style.height = window.innerHeight - document.getElementById('toolbar').clientHeight) - <?=$ph2?>;
		document.getElementById('CGroup').style.width = window.innerWidth - 14;
		document.getElementById('treePanel').style.width = window.innerWidth - <?=$pw?>;
	}
}

function mm(obj, idx, isOn){
	if (obj.src.search(/-2\.gif$/) != -1) return;
	obj.src = obj.src.substring(0,obj.src.lastIndexOf('/')+2) + (isOn ? '-1.gif' : '.gif');
}

var expandFlag = false;
/* 全展開/收攏 */
function expandingAll() {
	expandFlag = !expandFlag;
	for (var i = 0; i < pathtree.tocList.length;i++) {
		if (!expandFlag && i == 0) continue;
		if (pathtree.tocList[i].itemType == "folder")
			parent.tocstatus.statusObj.unfold(i, expandFlag);
	}
}

/**
 * 將目錄隱藏/恢復
 */
function minimize(){
	var myFrame = parent.document.getElementById('envClassRoom');
	var frameCol = myFrame.cols;
	if (frameCol == '200,*'){	// collecting
		document.getElementById('toolbar').style.display = 'none';
		document.getElementById('toolbar1').style.display = '';
		document.getElementById('treePanel').style.display = 'none';
		myFrame.cols= '30,*';
	}
	else{						// expanding
		document.getElementById('toolbar').style.display = '';
		document.getElementById('toolbar1').style.display = 'none';
		document.getElementById('treePanel').style.display = '';
		myFrame.cols= '200,*';
	}
}

/**
 *	設定按鈕的顯示與否
 */
function setButtonDisplay(btnType, btnDisplay) {
    var toolbar1 = document.getElementById('toolbar');
    var toolbar2 = document.getElementById('toolbar1');

    switch (btnType) {
        case 'Previous' :
            toolbar1.rows[0].cells[0].children[0].rows[0].cells[2].style.display = btnDisplay == 'show' ? '' : 'none';
            toolbar2.rows[0].cells[0].children[0].rows[2].cells[0].style.display = btnDisplay == 'show' ? '' : 'none';
            break;
        case 'Continue' :
            toolbar1.rows[0].cells[0].children[0].rows[0].cells[3].style.display = btnDisplay == 'show' ? '' : 'none';
            toolbar2.rows[0].cells[0].children[0].rows[1].cells[0].style.display = btnDisplay == 'show' ? '' : 'none';
            break;
        default :
            return;
    }
}

function disable_control(val) {
	var toolbar1 = document.getElementById('toolbar');
	var toolbar2 = document.getElementById('toolbar1');
	toolbar1.rows[0].cells[0].children[0].rows[0].cells[1].style.display = val ? '' : 'none';
	toolbar1.rows[0].cells[0].children[0].rows[0].cells[2].style.display = val ? '' : 'none';
	toolbar1.rows[0].cells[0].children[0].rows[0].cells[3].style.display = val ? '' : 'none';
	toolbar2.rows[0].cells[0].children[0].rows[1].cells[0].style.display = val ? '' : 'none';
	toolbar2.rows[0].cells[0].children[0].rows[2].cells[0].style.display = val ? '' : 'none';
}

/**
 *	到下一個/上一個節點
 */
function nextStep(dir){
	var flag = pathtree.GlobalStateObj.CurrentActivity == '' ? 0 : Number(pathtree.GlobalStateObj.CurrentActivity);

	switch (dir) {
		case 1:
			if(pathtree.navigationInterfaceList[flag].hideContinueButton == false)
	       		parent.sequencing.location.href="sequencing/NavEventTransfer.php?scoID="+pathtree.tocList[flag].id+"&idx="+flag+"&navEvent=continue";
	    	break;
	   	case -1:
	   		if(pathtree.navigationInterfaceList[flag].hidePreviousButton == false)
	       		parent.sequencing.location.href="sequencing/NavEventTransfer.php?scoID="+pathtree.tocList[flag].id+"&idx="+flag+"&navEvent=previous";
	    	break;
	    default : return;
	}
}

function start(){
	var SuspendSCO_ID = CheckSuspend();

	if(SuspendSCO_ID != ""){
		parent.sequencing.location.href="/learn/scorm/sequencing/NavEventTransfer.php?scoID="+ SuspendSCO_ID +"&idx=&navEvent=resume_all";
	}

	var exit_value=pathtree.tocList[0].exit_value;

	if(exit_value=="start"){
		parent.sequencing.location.href="/learn/scorm/sequencing/NavEventTransfer.php?scoID="+ pathtree.tocList[0].id +"&idx=0&navEvent=start";
	}

}



function CheckSuspend(){
	var xmldoc = XmlDocument.create();
	xmldoc.async = false;
	xmldoc.loadXML("<"+"?xml version='1.0'?"+"><root/>");

	var rootElement = xmldoc.documentElement;
	rootElement.setAttribute("course_ID",course_ID);
	rootElement.setAttribute("user_ID",student_id);

	var DataModelElement = xmldoc.createElement("DataModel");
    DataModelElement.appendChild(xmldoc.createTextNode("CheckSuspend"));
	rootElement.appendChild(DataModelElement);

	var XMLHTTPObj = XmlHttp.create();
	XMLHTTPObj.open("POST",'get.php',false);
	XMLHTTPObj.setRequestHeader("Content-Type","text/xml");

	XMLHTTPObj.send(xmldoc.xml);
	xmldoc.loadXML(XMLHTTPObj.responseText);

	return xmldoc.selectSingleNode("/root").text;

}

function SuspendActivity(){
	var flag=0;
	if(pathtree.GlobalStateObj.CurrentActivity==""){
		flag = 0;
	}else{
		flag = 	Number(pathtree.GlobalStateObj.CurrentActivity);
	}
	parent.engine.SequencingEngineObj.ClearNavigationRequest();
	parent.engine.SequencingEngineObj.NavigationRequestProcess(pathtree.tocList[flag].id, flag, 'suspend_all');
 	// parent.sequencing.location.href="/learn/scorm/sequencing/NavEventTransfer.php?scoID="+pathtree.tocList[flag].id+"&idx="+flag+"&navEvent=suspend_all";
}
function SuspendConfirm(){

	if(pathtree.GlobalStateObj.CurrentActivity==""){
		// alert("<?=$MSG['cfm_msg1'][$sysSession->lang]?>")
	}else{
		var tempIndex = Number(pathtree.GlobalStateObj.CurrentActivity);
		var tempTitle = pathtree.tocList[tempIndex].title;
        //bookmark node
        SuspendActivity();
	}

}

window.onresize=function(){ adjustFrameHeight(); };

var isConfirm = false;
function doUnload()
{
	window.onbeforeunload = null;
	myFrame = parent.parent.document.getElementById('envClassRoom');
	parent.parent.document.getElementById('s_catalog').scrolling = 'auto';

	var objForm = document.getElementById('pathtree').contentWindow.fetchResourceForm;

	if (objForm.href.value != 'about:blank')
	{
		objForm.href.value = 'about:blank';
		objForm.target = 'empty';
		objForm.submit();
	}
	if (!isConfirm)
	{
		SuspendConfirm();
		isConfirm = true;
	}
}

/* window.addEventListener('beforeunload',function(){
    doUnload();
}); */
bindEvent(window, 'beforeunload', function () {
   doUnload();
});

function bindEvent(el, eventName, eventHandler) {
  if (el.addEventListener){
    el.addEventListener(eventName, eventHandler, false); 
  } else if (el.attachEvent){
    el.attachEvent('on'+eventName, eventHandler);
  }
}

</script>
<base target="s_main">
</head>

<body SCROLL="no" style="margin: 0; margin-top: 4" class="cssTbBodyBg" onLoad="start();">

<table border="0" cellpadding="0" cellspacing="0" id="toolbar">
  <tr>
    <td align="right" width="200">
	  <table border="0" cellpadding="0" cellspacing="0">
	    <tr>
	      <td width="24"><a href="javascript:;" title="<?=$MSG['btn_notebook'][$sysSession->lang];?>" onclick="window.open('/message/write_notebook.php');     return false;"><div class="divNote"></div></a></td>
	      <td width="24"><a href="javascript:;" title="<?=$MSG['btn_expand'][$sysSession->lang];  ?>" onclick="expandingAll();return false;"><div class="divExpand"></div></a></td>
	      <td width="24"><a href="javascript:;" id="backNodeBtn1" title="" onclick="nextStep(-1);  return false;"><div class="divPrev"></div></a></td>
	      <td width="24"><a href="javascript:;" id="nextNodeBtn1" title="" onclick="nextStep( 1);  return false;"><div class="divNext"></div></a></td>
	      <td width="24"><a href="javascript:;" title="<?=$MSG['btn_minimize'][$sysSession->lang];?>" onclick="minimize(  );  return false;"><div class="divMin"></div></a></td>
	    </tr>
	  </table>
    </td>
  </tr>
</table>
<table border="0" cellpadding="0" cellspacing="0" style="display: none" id="toolbar1">
  <tr>
    <td>
	  <table border="0" cellpadding="0" cellspacing="0">
	    <tr><td height="24" ><a href="javascript:;" title="<?=$MSG['btn_maximize'][$sysSession->lang];?>" onclick="minimize(  );	return false;"><div class="divMax"></div></a></td></tr>
	    <tr><td height="24" ><a href="javascript:;" id="nextNodeBtn2" title="<?=$MSG['btn_next'][$sysSession->lang];    ?>" onclick="nextStep( 1);	return false;"><div class="divNext"></div></a></td></tr>
	    <tr><td height="24" ><a href="javascript:;" id="backNodeBtn2" title="<?=$MSG['btn_prev'][$sysSession->lang];    ?>" onclick="nextStep(-1);	return false;"><div class="divPrev"></div></a></td></tr>
	    <tr><td height="24" ><a href="javascript:;" title="<?=$MSG['btn_notebook'][$sysSession->lang];?>" onclick="window.open('/message/write_notebook.php');   return false;"><div class="divNote"></div></a></td></tr>
	  </table>
    </td>
  </tr>
</table>
<div class="cssToolbar" id="treePanel" style="height: 504px; width: 190px; overflow: hidden;<?=$tl?>">
  <table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tbody>
      <tr class="cssTr">
        <td class="cssTd">
          <table border="0" cellpadding="0" cellspacing="0" width="100%">
            <tbody>
				<tr class="cssTrEvn">
					<td nowrap valign="top" width="3"><img border="0" src="/theme/<?php echo $sysSession->theme;?>/<?php echo $sysSession->env;?>/cl2.gif" width="3" height="3"></td>
					<td align="right" nowrap valign="top"><img border="0" src="/theme/<?php echo $sysSession->theme;?>/<?php echo $sysSession->env;?>/cl3.gif" width="3" height="3"></td>
				</tr>
				<tr class="cssTrEvn">
					<td class="cssTd" colspan="2" nowrap>&nbsp;<img align="absMiddle" border="0" src="/theme/<?php echo $sysSession->theme;?>/<?php echo $sysSession->env;?>/icon_book.gif" width="22" height="12">&nbsp;Learning Path&nbsp;</td>
				</tr>
            </tbody>
          </table>
        </td>
      </tr>
      <tr class="cssTr">
       <td class="cssTd">
          <table border="0" cellpadding="0" cellspacing="0" class="cssTbTable" width="100%">
            <tbody>
              <tr class="cssTbTr">
                <td class="cssTbTd" align="right" id="CGroup" nowrap<?=$pd?>><iframe width="100%" height="100%" frameborder="0" border="0" scrolling="auto" name="pathtree" id="pathtree" src="scorm_api_adapter.php"></iframe></td>
              </tr>
            </tbody>
          </table>
        </td>
      </tr>
    </tbody>
  </table>
</div>
<script>adjustFrameHeight();</script>
</body>

</html>

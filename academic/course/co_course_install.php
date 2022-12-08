<?php
	/**
	 * 包裝/安裝課程
	 * @version $Id: course_pack.php,v 1.1 2010/02/24 02:38:20 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lang/co_course_pack_install.php');
	$sysSession->cur_func='700400500';
	$sysSession->restore();

	if (!aclVerifyPermission(700400500, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	$js = <<< EOF

window.onload=function()
{
	rm_whitespace(document.getElementById('treePanel'));
	var labels = document.getElementsByTagName('label');
	for(var i=0; i< labels.length; i++)
	{
		labels[i].onmouseover=function(){this.style.backgroundColor = '#CCCCFF';};
		labels[i].onmouseout =function(){this.style.backgroundColor = '';};
	}
	labels = document.getElementsByTagName('input');
	for(var i=0; i< labels.length; i++)
	{
		if (labels[i].type == 'checkbox')
			labels[i].onclick=function(){this.className=''; checkChild(this.parentNode);checkParent(this.parentNode);};
		else if (labels[i].type == 'radio')
			labels[i].onclick=function(){this.className=''; checkChild(this.parentNode);checkBranch(this);};
	}
	checkChild(document.getElementById('fp1').parentNode);
    expand_collapse(document.getElementById('expand_img'));
};

function rm_whitespace(node)
{
	switch(node.nodeType)
	{
		case 1:
			for(var i=node.childNodes.length-1; i>=0; i--) rm_whitespace(node.childNodes.item(i));
			break;
		case 3:
			if (node.nodeValue.search(/\s+/) === 0) node.parentNode.removeChild(node);
			break;
	}
}

function checkChild(li)
{
	if (li.lastChild != null &&
	    li.lastChild.tagName != null &&
	    li.lastChild.tagName.toLowerCase() == 'ul')
	{
		var n = li.lastChild.childNodes;
		for(var i=n.length-1; i>=0; i--)
		{
			if (n.item(i).tagName != null &&
				n.item(i).tagName.toLowerCase() == 'li')
			{
				n.item(i).firstChild.nextSibling.checked = li.firstChild.nextSibling.checked;
				checkChild(n.item(i));
			}
		}
	}
}

function checkParent(li)
{
	if (li.parentNode.parentNode != null &&
	    li.parentNode.parentNode.tagName != null &&
	    li.parentNode.parentNode.tagName.toLowerCase() == 'li'
	   )
	{
		var nodes = li.parentNode.childNodes;
		var isAllDisabled = 0 ;
		var isAllEnabled  = 0 ;
		for(var i=0; i<nodes.length; i++)
		{
			if (nodes.item(i).nodeType == 1 &&
			    nodes.item(i).nodeName.toLowerCase() == 'li'
			   )
			{
				if (nodes.item(i).firstChild.nextSibling.checked)
					isAllEnabled++;
				else
					isAllDisabled++;
			}
		}

		li.parentNode.parentNode.firstChild.nextSibling.className = (isAllEnabled > 0 && isAllDisabled > 0) ? 'xx' : '';
		if (isAllEnabled == 0 && isAllDisabled > 0)
			li.parentNode.parentNode.firstChild.nextSibling.checked = false;
		else
			li.parentNode.parentNode.firstChild.nextSibling.checked = true;

		checkParent(li.parentNode.parentNode);
	}
}

function checkBranch(radio)
{
	if (radio.id == 'fp1')
		checkChild(document.getElementById('fp13').parentNode);
	else
		checkChild(document.getElementById('fp1').parentNode);
}

function expand_collapse(img)
{
	if (img.parentNode.lastChild != null &&
	    img.parentNode.lastChild.tagName != null &&
	    img.parentNode.lastChild.tagName.toLowerCase() == 'ul')
	{
		if (img.parentNode.lastChild.style.display == 'none')
		{
			img.parentNode.lastChild.style.display = '';
			img.src = img.src.replace(/-c\./, '-cc.');
		}
		else
		{
			img.parentNode.lastChild.style.display = 'none';
			img.src = img.src.replace(/-cc\./, '-c.');
		}
	}
}

function select_course(){
	var win = new WinCourseSelect('setCourseValue');
	win.run();
}

function setCourseValue(cid, courseName)
{
	document.actForm.course_id.value = cid;
}

EOF;

	showXHTML_head_B($MSG['title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_CSS('inline', "
ul		  {list-style-type: none; margin-left: 16; padding-left: 0}
li		  {cursor: default}
.xx		  {-moz-opacity: 0.4;filter:Alpha(opacity=40);}
");

	showXHTML_script('inline', $js);
	showXHTML_script('include', '/lib/popup/popup.js');
	showXHTML_head_E();

	showXHTML_body_B();
		echo "<center>\n";
		$ary = array(array($MSG['msg_install_title'][$sysSession->lang]));
		showXHTML_tabFrame_B($ary, 1, 'actForm', 'ListTable', 'method="POST" action="co_course_install1.php" onsubmit="if (this.course_id.value.search(/^[0-9]{8}$/) == -1) { alert(\'' . $MSG['msg_course_id_error'][$sysSession->lang] . '\'); return false;} else this.elements[this.elements.length-1].disabled=true;" style="display: inline"');
				showXHTML_table_B('id ="mainTable" width="500" border="0" cellspacing="1" cellpadding="3" class="cssTable"');

					showXHTML_tr_B('class="font01 cssTrEvn"');
					  showXHTML_td('width="150"',$MSG['field_src_course_id'][$sysSession->lang]);
					  showXHTML_td_B('width="300"');
					    showXHTML_input('text', 'course_id', '', '', 'calss="cssInput"');
					    showXHTML_input('button', 'btn_select_course', $MSG['select_course'][$sysSession->lang], '',' onClick="select_course();"; ');
					  showXHTML_td_E();
					  // showXHTML_td('width="300"', $MSG['msg_course_id'][$sysSession->lang]);
					showXHTML_tr_E();
					showXHTML_tr_B('class="font01 cssTrOdd"');
					  showXHTML_td('', $MSG['field_target_school'][$sysSession->lang]);
					  showXHTML_td_B();
                      $school = $sysConn->GetAssoc(sprintf("select A.school_id, A.school_name from %s.WM_school as A join %s.WM_manager as B on A.school_id = B.school_id where username = '%s'",sysDBname,sysDBname,$sysSession->username));
					    showXHTML_input('radio', 'package_how', 
                        $school, 
                        $sysSession->school_id, '', '<br>');
					  showXHTML_td_E();
					  // showXHTML_td('', $MSG['msg_course_package'][$sysSession->lang]);
					showXHTML_tr_E();
					showXHTML_tr_B('class="font01 cssTrEvn"');
					  showXHTML_td('', $MSG['th_package_content'][$sysSession->lang]);
					  showXHTML_td_B('id="treePanel" class="cssTd" background="/theme/' . $sysSession->theme . '/academic/c-bg.gif"');
					  echo <<< EOB
  <ul>
    <li><img id="expand_img" src="/theme/{$sysSession->theme}/teach/icon-c.gif" border="0" align="absmiddle" onclick="expand_collapse(this);" style="cursor: pointer; cursor: hand"><input type="radio" value="standard" checked name="package_detail" id="fp1"><label for="fp1">{$MSG['rd_package_standard'][$sysSession->lang]}</label>
      <ul style="display: none">
        <li><img src="/theme/{$sysSession->theme}/teach/icon-c.gif" border="0" align="absmiddle" onclick="expand_collapse(this);" style="cursor: pointer; cursor: hand"><input type="checkbox" id="fp2"><label for="fp2">{$MSG['rd_course_unit'][$sysSession->lang]}</label>
          <ul style="display: none">
            <li><img src="/theme/{$sysSession->theme}/teach/dot.gif" border="0" align="absmiddle"><input type="checkbox" name="course_elements[]" value="course_intro"    id="fp3"><label for="fp3">{$MSG['rd_course_introduction'][$sysSession->lang]}</label></li>
            <li><img src="/theme/{$sysSession->theme}/teach/dot.gif" border="0" align="absmiddle"><input type="checkbox" name="course_elements[]" value="course_path"     id="fp4"><label for="fp4">{$MSG['rd_course_path'][$sysSession->lang]}</label></li>
            <li><img src="/theme/{$sysSession->theme}/teach/dot.gif" border="0" align="absmiddle"><input type="checkbox" name="course_elements[]" value="course_files"    id="fp5"><label for="fp5">{$MSG['rd_course_file'][$sysSession->lang]}</label></li>
          </ul>
        </li>
        <li><img src="/theme/{$sysSession->theme}/teach/icon-c.gif" border="0" align="absmiddle" onclick="expand_collapse(this);" style="cursor: pointer; cursor: hand"><input type="checkbox" id="fp6"><label for="fp6">{$MSG['rd_forum'][$sysSession->lang]}</label>
          <ul style="display: none">
            <li><img src="/theme/{$sysSession->theme}/teach/dot.gif" border="0" align="absmiddle"><input type="checkbox" name="course_elements[]" value="course_board"  id="fp7"><label for="fp7">{$MSG['rd_forum_course'][$sysSession->lang]}</label></li>
            <li><img src="/theme/{$sysSession->theme}/teach/dot.gif" border="0" align="absmiddle"><input type="checkbox" name="course_elements[]" value="subject_board" id="fp8"><label for="fp8">{$MSG['rd_forum_purpose'][$sysSession->lang]}</label></li>
          </ul>
        </li>
        <li><img src="/theme/{$sysSession->theme}/teach/dot.gif" border="0" align="absmiddle"><input type="checkbox" name="course_elements[]" value="chatroom"      id="fp9"><label for="fp9">{$MSG['rd_chatroom'][$sysSession->lang]}</label></li>
        <li><img src="/theme/{$sysSession->theme}/teach/dot.gif" border="0" align="absmiddle"><input type="checkbox" name="course_elements[]" value="homework"      id="fp10"><label for="fp10">{$MSG['rd_homework'][$sysSession->lang]}</label></li>
        <li><img src="/theme/{$sysSession->theme}/teach/dot.gif" border="0" align="absmiddle"><input type="checkbox" name="course_elements[]" value="exam"          id="fp11"><label for="fp11">{$MSG['rd_exam'][$sysSession->lang]}</label></li>
        <li><img src="/theme/{$sysSession->theme}/teach/dot.gif" border="0" align="absmiddle"><input type="checkbox" name="course_elements[]" value="questionnaire" id="fp12"><label for="fp12">{$MSG['rd_questionnaire'][$sysSession->lang]}</label></li>
      </ul>
    </li>
    <li style="display: none"><img src="/theme/{$sysSession->theme}/teach/icon-c.gif" border="0" align="absmiddle" onclick="expand_collapse(this);" style="cursor: pointer; cursor: hand"><input type="radio" name="package_detail" value="full" id="fp13"><label for="fp13">{$MSG['rd_package_full'][$sysSession->lang]}</label>
      <ul style="display: none">
        <li><img src="/theme/{$sysSession->theme}/teach/icon-c.gif" border="0" align="absmiddle" onclick="expand_collapse(this);" style="cursor: pointer; cursor: hand"><input type="checkbox" id="fp14"><label for="fp14">{$MSG['rd_course_unit'][$sysSession->lang]}</label>
          <ul style="display: none">
            <li><img src="/theme/{$sysSession->theme}/teach/dot.gif" border="0" align="absmiddle"><input type="checkbox" name="course_elements[]" value="course_intro"    id="fp15"><label for="fp15">{$MSG['rd_course_introduction'][$sysSession->lang]}</label></li>
            <li><img src="/theme/{$sysSession->theme}/teach/dot.gif" border="0" align="absmiddle"><input type="checkbox" name="course_elements[]" value="course_path"     id="fp16"><label for="fp16">{$MSG['rd_course_path'][$sysSession->lang]}</label></li>
            <li><img src="/theme/{$sysSession->theme}/teach/dot.gif" border="0" align="absmiddle"><input type="checkbox" name="course_elements[]" value="course_files"    id="fp17"><label for="fp17">{$MSG['rd_course_file'][$sysSession->lang]}</label></li>
            <li><img src="/theme/{$sysSession->theme}/teach/dot.gif" border="0" align="absmiddle"><input type="checkbox" name="course_elements[]" value="course_log"      id="fp18"><label for="fp18">{$MSG['rd_course_log'][$sysSession->lang]}</label></li>
          </ul>
        </li>
        <li><img src="/theme/{$sysSession->theme}/teach/dot.gif" border="0" align="absmiddle"><input type="checkbox" name="course_elements[]" value="permission_acl" id="fp19"><label for="fp19">{$MSG['rd_acl'][$sysSession->lang]}</label></li>
        <li><img src="/theme/{$sysSession->theme}/teach/icon-c.gif" border="0" align="absmiddle" onclick="expand_collapse(this);" style="cursor: pointer; cursor: hand"><input type="checkbox" id="fp20"><label for="fp20">{$MSG['rd_forum'][$sysSession->lang]}</label>
          <ul style="display: none">
            <li><img src="/theme/{$sysSession->theme}/teach/dot.gif" border="0" align="absmiddle"><input type="checkbox" name="course_elements[]" value="course_board"  id="fp21"><label for="fp21">{$MSG['rd_forum_course'][$sysSession->lang]}</label></li>
            <li><img src="/theme/{$sysSession->theme}/teach/dot.gif" border="0" align="absmiddle"><input type="checkbox" name="course_elements[]" value="subject_board" id="fp22"><label for="fp22">{$MSG['rd_forum_purpose'][$sysSession->lang]}</label></li>
          </ul>
        </li>
        <li><img src="/theme/{$sysSession->theme}/teach/dot.gif" border="0" align="absmiddle"><input type="checkbox" name="course_elements[]" value="chatroom"      id="fp23"><label for="fp23">{$MSG['rd_chatroom'][$sysSession->lang]}</label></li>
        <li><img src="/theme/{$sysSession->theme}/teach/dot.gif" border="0" align="absmiddle"><input type="checkbox" name="course_elements[]" value="homework"      id="fp24"><label for="fp24">{$MSG['rd_homework'][$sysSession->lang]}</label></li>
        <li><img src="/theme/{$sysSession->theme}/teach/dot.gif" border="0" align="absmiddle"><input type="checkbox" name="course_elements[]" value="exam"          id="fp25"><label for="fp25">{$MSG['rd_exam'][$sysSession->lang]}</label></li>
        <li><img src="/theme/{$sysSession->theme}/teach/dot.gif" border="0" align="absmiddle"><input type="checkbox" name="course_elements[]" value="questionnaire" id="fp26"><label for="fp26">{$MSG['rd_questionnaire'][$sysSession->lang]}</label></li>
        <li><img src="/theme/{$sysSession->theme}/teach/icon-c.gif" border="0" align="absmiddle" onclick="expand_collapse(this);" style="cursor: pointer; cursor: hand"><input type="checkbox" id="fp27"><label for="fp27">{$MSG['rd_student'][$sysSession->lang]}</label>
          <ul style="display: none">
            <li><img src="/theme/{$sysSession->theme}/teach/dot.gif" border="0" align="absmiddle"><input type="checkbox" name="course_elements[]" value="learner_account"       id="fp28"><label for="fp28">{$MSG['rd_student_account'][$sysSession->lang]}</label></li>
            <li><img src="/theme/{$sysSession->theme}/teach/dot.gif" border="0" align="absmiddle"><input type="checkbox" name="course_elements[]" value="learner_group"         id="fp29"><label for="fp29">{$MSG['rd_student_group'][$sysSession->lang]}</label></li>
            <li><img src="/theme/{$sysSession->theme}/teach/dot.gif" border="0" align="absmiddle"><input type="checkbox" name="course_elements[]" value="learner_message"       id="fp30"><label for="fp30">{$MSG['rd_student_message'][$sysSession->lang]}</label></li>
            <li><img src="/theme/{$sysSession->theme}/teach/dot.gif" border="0" align="absmiddle"><input type="checkbox" name="course_elements[]" value="learner_study"         id="fp31"><label for="fp31">{$MSG['rd_student_path_log'][$sysSession->lang]}</label></li>
            <li><img src="/theme/{$sysSession->theme}/teach/dot.gif" border="0" align="absmiddle"><input type="checkbox" name="course_elements[]" value="learner_homework"      id="fp32"><label for="fp32">{$MSG['rd_student_homework'][$sysSession->lang]}</label></li>
            <li><img src="/theme/{$sysSession->theme}/teach/dot.gif" border="0" align="absmiddle"><input type="checkbox" name="course_elements[]" value="learner_exam"          id="fp33"><label for="fp33">{$MSG['rd_student_exam'][$sysSession->lang]}</label></li>
            <li><img src="/theme/{$sysSession->theme}/teach/dot.gif" border="0" align="absmiddle"><input type="checkbox" name="course_elements[]" value="learner_questionnaire" id="fp34"><label for="fp34">{$MSG['rd_student_questionnaire'][$sysSession->lang]}</label></li>
            <li><img src="/theme/{$sysSession->theme}/teach/dot.gif" border="0" align="absmiddle"><input type="checkbox" name="course_elements[]" value="learner_logs"          id="fp35"><label for="fp35">{$MSG['rd_student_all_log'][$sysSession->lang]}</label></li>
          </ul>
        </li>
      </ul>
    </li>
  </ul>
EOB;
					  showXHTML_td_E();
					  // showXHTML_td('', $MSG['msg_package_content'][$sysSession->lang]);
					showXHTML_tr_E();

					showXHTML_tr_B();
						showXHTML_td_B('align="center" colspan="3"');
							showXHTML_input('submit', '', $MSG['btn_package'][$sysSession->lang], '', 'class="cssBtn"');
						showXHTML_td_E();
					showXHTML_tr_E();

				showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo "</center>\n";
	showXHTML_body_E();

?>

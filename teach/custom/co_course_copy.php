<?php
    /**
     * 匯入課程資訊
     *
     * PHP 4.4.7+, MySQL 4.0.21+, Apache 1.3.36+
     *
     * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
     * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
     * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
     *
     * @package     WM3
     * @author      SHIH JUNG YEH <yea@sun.net.tw>
     * @copyright   2000-2008 SunNet Tech. INC.
     * @version     CVS: $Id$
     * @link        http://demo.learn.com.tw/1000110138/index.html
     * @since       2008-06-20
     * 
     * 備註：          
     */

// {{{ 函式庫引用 begin
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lang/course_copy.php');
	require_once(sysDocumentRoot . '/academic/course/course_lib.php');
// }}} 函式庫引用 end

// {{{ 常數定義 begin
	$sysSession->cur_func='';
	$sysSession->restore();

	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}
// }}} 常數定義 end
    
// {{{ 變數宣告 begin
    
// }}} 變數宣告 end

// {{{ 函數宣告 begin
	$js = <<< EOF

    window.onload=function()
    {
        if (document.getElementById('treePanel') != null) {  
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
        			labels[i].onclick=function(){this.className=''; checkChild(this.parentNode);};
        	}
        	checkChild(document.getElementById('fp1').parentNode);
        }	
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

EOF;

// }}} 函數宣告 end

// {{{ 主程式 begin
	showXHTML_head_B($MSG['course_copy_wizard'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_CSS('inline', "
ul		  {list-style-type: none; margin-left: 16; padding-left: 0}
li		  {cursor: default}
.xx		  {-moz-opacity: 0.4;filter:Alpha(opacity=40);}
");

	showXHTML_script('inline', $js);
	showXHTML_head_E();

	showXHTML_body_B();
		echo "<center>\n";
		$ary = array(array($MSG['course_copy_wizard'][$sysSession->lang]));
		showXHTML_tabFrame_B($ary, 1, '', 'ListTable', 'method="POST" action="co_course_copy1.php" onsubmit="if (this.course_id.value == \'\') { alert(\'' . $MSG['msg_course_id_error'][$sysSession->lang] . '\'); return false;} else this.elements[this.elements.length-1].disabled=true;" style="display: inline"'); /*** CUSTOM ***/
				showXHTML_table_B('id ="mainTable" width="680" border="0" cellspacing="1" cellpadding="3" class="cssTable"');

                    // 取得過濾學年學期的課程名稱 begin
                    $RS = getCourseData($sysSession->course_id);
                    $lang = getCaption($RS['caption']);
                    $course_name = $lang[$sysSession->lang];
                    preg_match('/^([0-9_-]*)([^0-9_-]*)/', $course_name, $matches);
                    if(count($matches) && !empty($matches[2])) $course_name = $matches[2];
                    // 取得過濾學年學期的課程名稱 end


					$sqls = 'select M.course_id,C.caption' .
						' from WM_term_major as M, WM_term_course as C ' .
						' where M.username=\'' . $sysSession->username . '\' and ' . "role & ({$sysRoles[teacher]}|{$sysRoles['assistant']})".
						' and M.course_id = C.course_id and M.course_id <> ' . $sysSession->course_id . // 過濾目前課程
						' and C.status != 9 ';
									// " and C.caption like '%$course_name%'";
                        // ' and locate(\''.$course_name.'\' , C.caption)'; // 過濾與目前課程不同的課程名稱

                    $sysConn->Execute('use ' . sysDBprefix . $sysSession->school_id);
					$RS = $sysConn->Execute($sqls);
					if($RS->RecordCount() > 0)
					{
						while($RS1 = $RS->FetchRow())
						{
							$cnames = unserialize($RS1['caption']);

							$cour_ary[$RS1['course_id']] = $cnames[$sysSession->lang];

						}
						showXHTML_tr_B('class="font01 cssTrEvn"');
					  	showXHTML_td('width="80"', $MSG['co_course_name'][$sysSession->lang]);
					  	showXHTML_td_B('width="300"');
								showXHTML_input('select', 'course_id', $cour_ary, $course_id, 'class="cssInput"');
						showXHTML_td_E();
						showXHTML_tr_E();
						
    					showXHTML_tr_B('class="font01 cssTrOdd"');
    					  showXHTML_td('', $MSG['th_copy_content'][$sysSession->lang]);
    					  showXHTML_td_B('id="treePanel" class="cssTd" background="/theme/' . $sysSession->theme . '/academic/c-bg.gif"');
    					  echo <<< EOB
  <ul>
    <li><img src="/theme/{$sysSession->theme}/teach/icon-c.gif" border="0" align="absmiddle" onclick="expand_collapse(this);" style="cursor: pointer; cursor: hand"><input type="radio" value="standard" checked name="package_detail" id="fp1"><label for="fp1">{$MSG['copy_items'][$sysSession->lang]}</label>
      <ul>
        <li><img src="/theme/{$sysSession->theme}/teach/dot.gif" border="0" align="absmiddle"><input type="checkbox" name="course_elements[]" value="subject_board" id="fp8"><label for="fp8">{$MSG['co_forum_purpose'][$sysSession->lang]}</label></li>
        <li><img src="/theme/{$sysSession->theme}/teach/dot.gif" border="0" align="absmiddle"><input type="checkbox" name="course_elements[]" value="homework"      id="fp10"><label for="fp10">{$MSG['co_rd_homework'][$sysSession->lang]}</label></li>
        <li><img src="/theme/{$sysSession->theme}/teach/dot.gif" border="0" align="absmiddle"><input type="checkbox" name="course_elements[]" value="exam"          id="fp11"><label for="fp11">{$MSG['co_rd_exam'][$sysSession->lang]}</label></li>
        <li><img src="/theme/{$sysSession->theme}/teach/dot.gif" border="0" align="absmiddle"><input type="checkbox" name="course_elements[]" value="questionnaire" id="fp12"><label for="fp12">{$MSG['co_rd_questionnaire'][$sysSession->lang]}</label></li>
        <li><img src="/theme/{$sysSession->theme}/teach/dot.gif" border="0" align="absmiddle"><input type="checkbox" name="course_elements[]" value="node"          id="fp13"><label for="fp13">{$MSG['co_node1'][$sysSession->lang]}</label></li>
      </ul>
    </li>
  </ul>
EOB;
    					  showXHTML_td_E();
    					showXHTML_tr_E();

    					showXHTML_tr_B('class="font01 cssTrEvn"');
    					  showXHTML_td('', $MSG['func_description'][$sysSession->lang]);
    					  showXHTML_td_B('');
    					      echo $MSG['func_description_content'][$sysSession->lang];
    					  showXHTML_td_E();
    					showXHTML_tr_E();
                            
    					showXHTML_tr_B('class="font01 cssTrOdd"');
    						showXHTML_td_B('align="center" colspan="2"');
    							showXHTML_input('submit', '', $MSG['co_btn_pack'][$sysSession->lang], '', 'class="cssBtn"');
    						showXHTML_td_E();
    					showXHTML_tr_E();						
					}
                    else
                    {
						showXHTML_tr_B('class="font01 cssTrEvn"');
					  	    showXHTML_td('', $MSG['no_data'][$sysSession->lang]);
						showXHTML_tr_E();
                    }

				showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo "</center>\n";
	showXHTML_body_E();
// }}} 主程式 end 

?>

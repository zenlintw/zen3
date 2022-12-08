<?php
	/**
	 * 【程式功能】
	 * 建立日期：2007/08/29
	 * @author  Wing
	 * @version $Id: co_qti_pack.php,v 1.8 2006/04/21 09:45:27 wiseguy Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lang/course_pack_install.php');  
    require_once(sysDocumentRoot . '/lib/common.php');
    //$sysConn->debug=true;     
    
    /* Custom By TN 20110222(B)MIS#020180 */
	$sysSession->cur_func='0700400500';
    /* Custom By TN 20110222(E)MIS#020180 */
	$sysSession->restore();
    //取出複製過的記錄
    /* Custom By TN 20120118(B)MIS#023740*/
    $record_course_id_ary=dbGetCol('CO_qti_pack_log', 'DISTINCT source_course_id', "destination_course_id={$sysSession->course_id} AND state='1'");
    $record_course_id_str="'".implode("','",$record_course_id_ary)."'";
    /* Custom By TN 20120118(E)MIS#023740*/
    /* Custom By TN 20120208(B)MIS#023740*/
    //取得本課程詳細名稱
    $course=dbGetRow("WM_term_course", "caption", "course_id=$sysSession->course_id");    
    $cnames=getCaption($course['caption']);
    $co_course_name=$cnames[$sysSession->lang];    
    if (!empty($course['deptnam'])) $co_course_name .=  '('. $course['deptnam'].')';
    //取出節點數
    $table = "(select course_id, max(serial) as serial from WM_term_path where course_id={$sysSession->course_id} group by course_id) as p1 
        inner join ( select course_id, serial, content from WM_term_path where course_id={$sysSession->course_id} ) as p2 
            on p1.course_id = p2.course_id and p1.serial = p2.serial";
    
    $field = 'p2.content';
    $where = "1";
    // 課程資料   
    $course_content = dbGetOne($table, $field, $where);
    if (preg_match_all('/item.*identifierref="([^"]*)"/isU', $course_content, $regs)){
        $resource_node = count($regs[0]);
    }else{
        $resource_node = 0;
    }
    
    //取得作業、試卷、問卷的份數與題數
    $hwTestCount = dbGetone('WM_qti_homework_test','count(*)',sprintf('course_id=%d',$sysSession->course_id));
    $hwQuestCount = dbGetone('WM_qti_homework_item','count(*)',sprintf('course_id=%d',$sysSession->course_id));
    $examTestCount = dbGetone('WM_qti_exam_test','count(*)',sprintf('course_id=%d',$sysSession->course_id));
    $examQuestCount = dbGetone('WM_qti_exam_item','count(*)',sprintf('course_id=%d',$sysSession->course_id));
    $qTestCount = dbGetone('WM_qti_questionnaire_test','count(*)',sprintf('course_id=%d',$sysSession->course_id));
    $qQuestCount = dbGetone('WM_qti_questionnaire_item','count(*)',sprintf('course_id=%d',$sysSession->course_id));
    
    $MSG['msg_path_exist'][$sysSession->lang]=sprintf($MSG['msg_path_exist'][$sysSession->lang],
        $co_course_name,
        $resource_node,
        $hwTestCount,
        $hwQuestCount,
        $examTestCount,
        $examQuestCount,
        $qTestCount,
        $qQuestCount
    );
    
    /* Custom By TN 20120208(E)MIS#023740*/
    
	if (!aclVerifyPermission('', aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}                     
    
	$js = <<< EOF
        /* Custom By TN 20120118(B)MIS#023740*/
        if (!Array.prototype.indexOf){
            Array.prototype.indexOf = function(elt){
              var len = this.length;
              var from = Number(arguments[1]) || 0;
              from = (from < 0)
                   ? Math.ceil(from)
                   : Math.floor(from);
              if (from < 0)
                from += len;
          
              for (; from < len; from++){
                if (from in this &&
                    this[from] === elt)
                  return from;
              }
              return -1;
            };
        }
        var record_course_ary=new Array({$record_course_id_str});  
        /* Custom By TN 20120118(E)MIS#023740*/
window.onload=function()
{
    
    /* Custom By TN 20120118(E)MIS#023740*/
    alert("{$MSG['msg_copy_start'][$sysSession->lang]}");
    /* Custom By TN 20120118(E)MIS#023740*/
    if (document.getElementById('treePanel') != null) {  
    	rm_whitespace(document.getElementById('treePanel'));
    	var labels = document.getElementsByTagName('label');	
    	for(var i=0; i< labels.length; i++)
    	{
    		labels[i].onmouseover=function(){this.style.backgroundColor = '#CCCCFF';};
    		labels[i].onmouseout =function(){this.style.backgroundColor = '';};
    	}
    	input = document.getElementsByTagName('input');
        for(var i=0; i< input.length; i++)
    	{
    		if (input[i].type == 'checkbox')
                input[i].checked=true;
    			// input[i].onclick=function(){this.className=''; checkChild(this.parentNode);checkParent(this.parentNode);};
    	}
    	/*
        for(var i=0; i< labels.length; i++)
    	{
    		if (labels[i].type == 'checkbox')
    			labels[i].onclick=function(){this.className=''; checkChild(this.parentNode);checkParent(this.parentNode);};
    	}
    	checkChild(document.getElementById('fp1').parentNode);
        */
	}
};
    function checkdata(){                                 
        var node = document.getElementById("copyform");
        if (node == null) return false;
        var selected_course_id=node.course_id.value;
        if (selected_course_id == ""){
		  alert("{$MSG['msg_course_id_error'][$sysSession->lang]}");
		  return false;
	    }
        var selectedindex=node.course_id.options.selectedIndex;
        var msg="{$MSG['msg_copy_confirm'][$sysSession->lang]}";
        msg=msg.replace("%sname%",node.course_id.options[selectedindex].innerHTML);
        msg=msg.replace("%dname%","{$sysSession->course_name}");
        if(record_course_ary.indexOf(selected_course_id)!=-1){
            msg+="\\n{$MSG['msg_copy_reapet'][$sysSession->lang]}";
        } 
        if({$resource_node}>0 && !confirm('{$MSG['msg_path_exist'][$sysSession->lang]}')) return false; 
        if(!confirm(msg)) return false;  
        document.getElementById("ListTable").style.display='none';
        document.getElementById("packMsgStartDiv").innerHTML='<h2 align="center">'+"{$MSG['co_btn_pack'][$sysSession->lang]}</h2><br />"+'<h2 align="center">'+"{$MSG['co_msg_wait'][$sysSession->lang]}</h2>";
        return true;
    }
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
		showXHTML_tabFrame_B($ary, 1, '', 'ListTable', 'id="copyform" method="POST" action="co_qti_pack1.php" onsubmit="return checkdata();" style="display: inline"'); /*** CUSTOM ***/
				showXHTML_table_B('id ="mainTable" width="800" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
                    $col = ($col == "cssTrEvn") ? "cssTrOdd" : "cssTrEvn";
                    /* Custom By TN 20120117(B)MIS#023740*/
                        showXHTML_tr_B('class="font01 '.$col.'"');
        				  showXHTML_td('', $MSG['copy_record'][$sysSession->lang]);
        				  showXHTML_td_B('');
                            $RS1=dbGetStMr('CO_qti_pack_log L LEFT JOIN WM_user_account A ON L.username=A.username', "L.*,CONCAT(IFNULL(first_name,''),IFNULL(last_name,'')) name", "destination_course_id={$sysSession->course_id} order by log_stime desc");
                            if($RS1){
                                showXHTML_table_B('width="700" border="0" cellspacing="1" cellpadding="3" class="cssTable" align="center"');
                                    showXHTML_tr_B('class="font01 cssTrEvn"');
                                      showXHTML_td('', $MSG['executor'][$sysSession->lang]);
                    				  showXHTML_td('', $MSG['title_time'][$sysSession->lang]);
                                      showXHTML_td('', $MSG['from_course'][$sysSession->lang]);
                                      showXHTML_td('', $MSG['title_status'][$sysSession->lang]);
                                      showXHTML_td('', $MSG['td_memo'][$sysSession->lang]);
                    				showXHTML_tr_E();
                            }
                            while($RS = $RS1->FetchRow()){
                                $from_caption=unserialize($RS['source_caption']);
                                $note=unserialize($RS['note']); 
                                    showXHTML_tr_B('class="font01 cssTrEvn"');
                                      showXHTML_td('', $RS['username']."<br />({$RS['name']})");
                    				  showXHTML_td('', $RS['log_stime']);
                                      showXHTML_td('', $RS['source_course_id']."<br />".$from_caption[$sysSession->lang]);
                                      showXHTML_td('', ($RS['state']==1?$MSG['success'][$sysSession->lang]:$MSG['fail'][$sysSession->lang]));
                                      $notestr=sprintf($MSG['msg_note'][$sysSession->lang],
                                        intval($note['path']),
                                        intval($note['homework']['test']),intval($note['homework']['item']),
                                        intval($note['exam']['test']),intval($note['exam']['item']),
                                        intval($note['questionnaire']['test']),intval($note['questionnaire']['item'])
                                        );
                                      showXHTML_td('', $notestr);
                    				showXHTML_tr_E();
                            }  
                            if($RS1){
                                showXHTML_table_E();
                            }
        				  showXHTML_td_E();
        				showXHTML_tr_E();
                    /* Custom By TN 20120117(E)MIS#023740*/
                    /*
                    $before_year = date('Y') - 1911 - 1; // 取得目前年

					$sqls = 'select distinct M.course_id,C.caption, IFNULL(CO.deptnam , \'\') as deptnam' .
						    ' from WM_term_major as M ' .
						    ' inner join WM_term_course as C on C.course_id=M.course_id' .
						    ' inner join CO_course AS CO on M.course_id=CO.WM_course_id and syear like \''.$before_year.'%\' ' .
						    ' where M.username=\'' . $sysSession->username . '\' and role & ' . ($sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']).
                            ' and M.course_id <> ' . $sysSession->course_id . ' and C.status != 9 '.
                            ' order by M.course_id desc';
                    */
					$sqls = 'select distinct M.course_id,C.caption' .
						    ' from WM_term_major as M ' .
						    ' inner join WM_term_course as C on C.course_id=M.course_id' .
						    ' where M.username=\'' . $sysSession->username . '\' and role & ' . ($sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']).
                            ' and M.course_id <> ' . $sysSession->course_id . ' and C.status != 9 '.
                            ' order by C.course_id desc';
                    $sysConn->Execute('use ' . sysDBprefix . $sysSession->school_id);
					$RS = $sysConn->Execute($sqls);
					if($RS->RecordCount() > 0)
					{
						while($RS1 = $RS->FetchRow()){
                            $cnames = getCaption($RS1['caption']);
                            if (!empty($RS1['deptnam'])) $cnames[$sysSession->lang] .=  '('. $RS1['deptnam'].')';
							$cour_ary[$RS1['course_id']] = $cnames[$sysSession->lang];
						}
                        $col = ($col == "cssTrEvn") ? "cssTrOdd" : "cssTrEvn";
						showXHTML_tr_B('class="font01 '.$col.'" rowspan="2"');
					  	showXHTML_td('width="80" style="color:red;"', $MSG['from_course'][$sysSession->lang]);
					  	showXHTML_td_B('colspan="2"');
                            // echo $MSG['msg_from_course'][$sysSession->lang];
                            showXHTML_table_B('border="0" cellspacing="0" cellpadding="0"');
                                showXHTML_tr_B('class="font01"');    
                                    showXHTML_td_B('rowspan="2"'); 
                                        showXHTML_input('select', 'course_id', $cour_ary, $lastCourseId, 'class="cssInput" style="width: 690px;"');       
                                    showXHTML_td_E();        
                                    showXHTML_td_B('width="130" align="center"'); 
                                        echo '<img src="/theme/'.$sysSession->theme.'/teach/icon_copyto.png" width="20" border="0" align="absmiddle">';
                                    showXHTML_td_E();  
                                    showXHTML_td_B('rowspan="2"'); 
                                        echo $co_course_name;
                                    showXHTML_td_E();                                               
    						    showXHTML_tr_E();
                                showXHTML_tr_B('class="font01"');      
                                    showXHTML_td_B(''); 
                                        echo '<span style="color:red;">'.$MSG['msg_copyto'][$sysSession->lang]."</span>";
                                    showXHTML_td_E();  
                                showXHTML_tr_E(); 
                            showXHTML_table_E();
                        showXHTML_td_E();  
                         
						showXHTML_tr_E();
						$col = ($col == "cssTrEvn") ? "cssTrOdd" : "cssTrEvn";
    					showXHTML_tr_B('class="font01 '.$col.'"');
    					  showXHTML_td('', $MSG['th_copy_content'][$sysSession->lang]);
    					  showXHTML_td_B('colspan="2" id="treePanel" class="cssTd" background="/theme/' . $sysSession->theme . '/academic/c-bg.gif"');
    					  echo <<< EOB
  <ul>
    <li><input type="radio" value="standard" checked name="package_detail" id="fp1"><label for="fp1">{$MSG['copy_items'][$sysSession->lang]}</label>
      <ol> 
        <li><input type="checkbox" name="course_elements[]" value="node"          id="fp13"><label for="fp13">{$MSG['co_node'][$sysSession->lang]}</label>
            <ul>
            <li class="lcms" style="margin-left:20px"><input type="radio" name="course_path_replace" value="1" id="fp14"><label for="fp14">{$MSG['course_path_replace'][$sysSession->lang]}</label></li>
            <li class="lcms" style="margin-left:20px"><input type="radio" name="course_path_replace" value="0" checked id="fp15"><label for="fp15">{$MSG['course_path_append'][$sysSession->lang]}</label></li>
            </ul>
        </li>  
        <li><input type="checkbox" name="course_elements[]" value="subject_board" id="fp8"><label for="fp8">{$MSG['co_forum_purpose'][$sysSession->lang]}</label></li>
        <li><input type="checkbox" name="course_elements[]" value="course_board"  id="fp9"><label for="fp9">{$MSG['co_course_board'][$sysSession->lang]}</label></li> <!-- custom by yea for mis#12346 -->
        <li><input type="checkbox" name="course_elements[]" value="homework"      id="fp10"><label for="fp10">{$MSG['co_rd_homework'][$sysSession->lang]}</label></li>
        <li><input type="checkbox" name="course_elements[]" value="exam"          id="fp11"><label for="fp11">{$MSG['co_rd_exam'][$sysSession->lang]}</label></li>
        <li><input type="checkbox" name="course_elements[]" value="questionnaire" id="fp12"><label for="fp12">{$MSG['co_rd_questionnaire'][$sysSession->lang]}</label></li>
      </ol>
    </li>
  </ul>      
EOB;
    					  showXHTML_td_E();
    					showXHTML_tr_E();
                        $col = ($col == "cssTrEvn") ? "cssTrOdd" : "cssTrEvn";
    					showXHTML_tr_B('class="font01 '.$col.'"');
    					  showXHTML_td('', $MSG['func_description'][$sysSession->lang]);
    					  showXHTML_td_B('');
    					      echo $MSG['func_description_content'][$sysSession->lang];
    					  showXHTML_td_E();
    					showXHTML_tr_E();
                        $col = ($col == "cssTrEvn") ? "cssTrOdd" : "cssTrEvn";
    					showXHTML_tr_B('class="font01 '.$col.'"');
    						showXHTML_td_B('align="center" colspan="2"');
    							showXHTML_input('submit', '', $MSG['co_btn_pack'][$sysSession->lang], '', 'class="cssBtn" id="submitbtn"');
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
        /* Custom By TN 20120201(B)MIS#020180*/
        echo "<div id='packMsgStartDiv'></div>";
        /* Custom By TN 20120201(E)MIS#020180*/
		echo "</center>\n";
	showXHTML_body_E();

?>

<?php
	/**
	 * 批改列表
	 * $Id: exam_correct_list.php,v 1.1 2010/02/24 02:40:25 saly Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/live_list.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	
	$sysSession->cur_func = '1300500100';
    $sysSession->env = 'teach';
    $sysSession->restore();
    if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
    }


	

	showXHTML_head_B($MSG['live_list'][$sysSession->lang]);
	  showXHTML_CSS('include', "/theme/{$sysSession->theme}/teach/wm.css");
	  $scr = <<< EOB

function chBgc(obj, mode){
	obj.style.backgroundColor = mode ? '#FFFFCC' : '';
}

function getParentOffset(obj, which){
	switch(obj.tagName){
		case 'HTML':
			return 0;
			break;
		case 'TABLE':
		case 'TD':
			return (which ? obj.offsetLeft : obj.offsetTop) + getParentOffset(obj.parentNode, which);
			break;
		default:
			return getParentOffset(obj.parentNode, which);
			break;
	}
}

function RemoveHTML( strText ) {

    var regEx = /<[^>]*>/g;

    return strText.replace(regEx, "");

}

var th_idx = 0;
function property(id,y){
    var TablePanel = document.getElementById('displayPanel');
	var propertyPanel = document.getElementById('editPanel');
	var inputs = propertyPanel.getElementsByTagName('input');
	
	inputs[0].value = RemoveHTML(TablePanel.rows[y].cells[1].innerHTML.replace(/&amp;/g, '&'));
	inputs[1].value = id;
	
	propertyPanel.style.left = getParentOffset(TablePanel.rows[y].cells[1], true);
	propertyPanel.style.top  = getParentOffset(TablePanel.rows[y].cells[1], false);

	propertyPanel.style.display = 'inline';
	th_idx = y;
	return false;
}

function property_complete(form, isApply)
{
    var TablePanel = document.getElementById('displayPanel');
	document.getElementById('editPanel').style.display = 'none';
	if (isApply)
	{
	    TablePanel.rows[th_idx].cells[1].setAttribute('title', form.name.value);
		TablePanel.rows[th_idx].cells[1].innerHTML = '<div style="width:400px;">'+form.name.value+'</div>';
		
		form.submit();
		form.reset();
	}
}

function del(id) {

    if (!confirm('{$MSG['are_you_sure'][$sysSession->lang]}')) return;
    var obj = document.getElementById('delForm');
	obj.id.value = id;
	obj.submit();

}

function ins(id) {

    var obj = document.getElementById('insForm');
	obj.id.value = id;
	obj.submit();

}

EOB;
	showXHTML_script('inline', $scr);
	showXHTML_head_E();
	showXHTML_body_B();
	  $ary[] = array($MSG['live_list'][$sysSession->lang]);
      $random_seat = md5(uniqid(rand(), true));
	  $ticket = md5(sysTicketSeed . $sysSession->course_id . $random_seat);
	  echo "<div align=\"center\">\n";
	  showXHTML_tabFrame_B($ary);

		  showXHTML_table_B('id="displayPanel" border="0" cellspacing="1" cellpadding="3" class="cssTable" style=" word-wrap: break-word; word-break: break-all;"');

			showXHTML_tr_B('class="cssTrHead"');
				showXHTML_td('align="center" width="60" ', $MSG['serial_number'][$sysSession->lang]);
				showXHTML_td('align="center" width="400"', $MSG['live_name'][$sysSession->lang]);
			    showXHTML_td('align="center" width="400"',  $MSG['live_url'][$sysSession->lang]);
				showXHTML_td('align="center" width="180"', $MSG['live_time'][$sysSession->lang]);
				showXHTML_td('align="center" width="60"', $MSG['status'][$sysSession->lang]);
				showXHTML_td('align="center" width="180"', $MSG['manage'][$sysSession->lang]);
			showXHTML_tr_E();

    
	$RS = dbGetStMr('APP_live_activity',
					'*',
					'course_id=' . $sysSession->course_id . ' order by begin_time desc',
					ADODB_FETCH_ASSOC);

	$num = 1;
	$i = 1;
	if ($RS)
	while($fields = $RS->FetchRow()){

		    if ($fields['status']=='on') {
		    	$status = $MSG['on'][$sysSession->lang];
		    	$disable = 'disabled';
		    } else {
		    	$status = $MSG['off'][$sysSession->lang];
		    	$disable = '';
		    }
            $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
            showXHTML_tr_B($col);
                showXHTML_td('align="center"', $num++);
                showXHTML_td('align="left"', '<div style="width:400px;">'.$fields['name'].'</div>');
                showXHTML_td('align="left"', '<div style="width:400px;"><a href="'.$fields['url'].'" target="_blank">'.$fields['url'].'</a></div>');
                showXHTML_td('align="center"', $fields['begin_time']);
                showXHTML_td('align="center"', $status);
                showXHTML_td_B('align="center"');
                    showXHTML_input('button', '', $MSG['edit'][$sysSession->lang], '', 'onclick="return property(' . $fields['id'] . ','.$i++.');"');
                    showXHTML_input('button', '', $MSG['create'][$sysSession->lang], '', 'onclick="ins(' . $fields['id'] . ');"');
                    showXHTML_input('button', '', $MSG['delete'][$sysSession->lang], '', $disable . ' onclick="del(' . $fields['id'] . ');"');
                showXHTML_td_E();
            showXHTML_tr_E();
	}
		showXHTML_table_E();
	showXHTML_tabFrame_E();
	echo "</div>\n";
	showXHTML_form_B('method="POST" action="live_list_modify.php"', 'delForm');
		showXHTML_input('hidden', 'ticket', $ticket);
		showXHTML_input('hidden', 'referer', $random_seat);
		showXHTML_input('hidden', 'action', 'delete');
		showXHTML_input('hidden', 'id', '');
	showXHTML_form_E();
	
	showXHTML_form_B('method="POST" action="live_list_modify.php"', 'insForm');
		showXHTML_input('hidden', 'ticket', $ticket);
		showXHTML_input('hidden', 'referer', $random_seat);
		showXHTML_input('hidden', 'action', 'create');
		showXHTML_input('hidden', 'id', '');
	showXHTML_form_E();
	
	$ary = array(array($MSG['edit'][$sysSession->lang]));
	  showXHTML_tabFrame_B($ary, 1, 'editForm', 'editPanel', 'action="live_list_modify.php" method="POST" target="empty" style="display: inline"', true);
		  showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" class="box01"');
			showXHTML_tr_B('class="bg03 font01"');
			  showXHTML_td('', $MSG['live_name'][$sysSession->lang]);
			  showXHTML_td_B();
			    showXHTML_input('text', 'name', '', '', 'size="30" maxlength="128" class="box02"');
			  showXHTML_td_E();
			showXHTML_tr_E();
			showXHTML_tr_B('class="bg03 font01"');
			  showXHTML_td_B('colspan="2" align="right" style="padding-right: 1em"');
			    showXHTML_input('hidden', 'id', '');
			    showXHTML_input('hidden', 'action', 'modify');
			    showXHTML_input('hidden', 'ticket', $ticket);
			    showXHTML_input('hidden', 'referer', $random_seat);
			    showXHTML_input('button', '', $MSG['sure'][$sysSession->lang]  , '', 'class="cssBtn" onclick="property_complete(this.form, true);"');
			    showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="property_complete(this.form, false);"');
			  showXHTML_td_E();
			showXHTML_tr_E();
          showXHTML_table_E();
	  showXHTML_tabFrame_E();

	showXHTML_body_E();
?>

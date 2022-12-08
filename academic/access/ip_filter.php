<?php
    /**************************************************************************************************
     *                                                                                                *
     *        Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                                  *
     *                                                                                                *
     *        Programmer: Wiseguy Liang                                                                 *
     *        Creation  : 2004/07/27                                                                     *
     *        work for  : ip filter                                                                      *
     *        work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                          *
     *        identifier: $Id: ip_filter.php,v 1.1 2010/02/24 02:38:13 saly Exp $                                *
     *                                                                                                *
     **************************************************************************************************/

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lang/ip_filter.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');

    $sysSession->cur_func = '600300100';
    $sysSession->restore();
    if (!aclVerifyPermission(600300100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
    {
    }

    $rs = dbGetStMr('WM_ipfilter', '*', '1 order by priority', ADODB_FETCH_ASSOC);

    showXHTML_head_B($MSG['login_restrict'][$sysSession->lang]);
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
    showXHTML_script('include', '/lib/dragLayer.js');
    showXHTML_script('inline', "
    var msg1 = '{$MSG['are_you_sure'][$sysSession->lang]}';
    var msg2 = '{$MSG['all_unselect'][$sysSession->lang]}';
    var msg3 = '{$MSG['all_select'][$sysSession->lang]}';

    ");
    $scr = <<< EOB

    var procTableObj;            // 規則列表 table
    var mainForm;
    var isAllSelect = false;    // 記錄目前是全選還是全消的旗標

    /**
     * 移除 HTML 間的空白節點
     * param element node XML節點
     * return void
     */
    function rm_whitespace(node)
    {
        var len = node.childNodes.length;
        var nodes = node.childNodes;

        for(var i=len-1; i>=0; i--)
            switch(nodes[i].nodeType){
                case 1:
                    rm_whitespace(nodes[i]);
                    break;
                case 3:
                    if (nodes[i].nodeValue.search(/^\s+$/) === 0) node.removeChild(nodes[i]);
                    break;
            }
    }

    /**
     * 移動規則的優先權
     * param integer direction 移動方向。-1 提高，1 降低
     * return void
     */
    function moveRules(direction)
    {
        var tr, tmp, x;
        var nodes = procTableObj.getElementsByTagName('input');
        var tbody = procTableObj.getElementsByTagName('tbody')[0];
        if (direction > 0)
            for(var i=nodes.length-1; i>=0; i--)
            {
                if (nodes[i].type=='checkbox' && nodes[i].checked)
                {
                    tr = nodes[i].parentNode.parentNode;
                    if(tr.rowIndex >= (procTableObj.rows.length-2)) continue;
                    x=tr.nextSibling.nextSibling;
                    tmp = tr.cloneNode(true);
                    tbody.removeChild(tr);
                    tmp = tbody.insertBefore(tmp, x);
                    tmp.cells[0].firstChild.checked = true;
                }
            }
        else
            for(var i=0; i<nodes.length; i++)
            {
                if (nodes[i].type=='checkbox' && nodes[i].checked)
                {
                    tr = nodes[i].parentNode.parentNode;
                    if(tr.rowIndex <= 3) continue;
                    x=tr.previousSibling;
                    tmp = tr.cloneNode(true);
                    tbody.removeChild(tr);
                    tmp = tbody.insertBefore(tmp, x);
                    tmp.cells[0].firstChild.checked = true;
                }
            }

        // normalize <tr> background color
        for(var i=3; i<procTableObj.rows.length-1; i++)
        {
            x=x=='bg03 font01' ? 'bg04 font01' : 'bg03 font01';
            procTableObj.rows[i].className = x;
        }
    }

    /**
     *
     */
    function modify_init(idx)
    {
        mainForm.reset();
        mainForm.username.value    = procTableObj.rows[idx].cells[1].innerHTML.replace(/^\s+|\s$/g, '');
        mainForm.host.value        = procTableObj.rows[idx].cells[2].innerHTML.replace(/^\s+|\s$/g, '').replace('<BR>', '\\n');
        mainForm.rule_id.value    = procTableObj.rows[idx].cells[0].firstChild.nextSibling.value.replace(/^\s+|\s$/g, '');
        if (procTableObj.rows[idx].cells[3].innerHTML.search('allow') === 0)
            mainForm.mode[1].checked = true;
        else
            mainForm.mode[0].checked = true;

        init_position('dialogTable');
    }

    /**
     *
     */
    function addnew_init()
    {
        mainForm.reset();
        init_position('dialogTable');
    }

    /**
     * 隱藏編輯規則對話框
     * param string obj_id 對話框 ID
     * return void
     */
    function hid_dialog(obj_id)
    {
        document.getElementById(obj_id).style.display = 'none';
    }

    /*Chrome版func名稱相同造成按鈕沒反應：給予新名稱*/
    function removeIP()
    {
        if (!confirm(msg1)) return;
        var objForm = document.getElementById('rmForm');
        var lists = '';
        for(var i=3; i<procTableObj.rows.length-1; i++)
            if (procTableObj.rows[i].cells[0].firstChild.checked)
                lists += procTableObj.rows[i].cells[0].firstChild.nextSibling.value + '\\n';

        if (lists)
        {
            objForm.lists.value = lists;
            objForm.submit();
        }
    }

    /**
     * 顯示編輯規則對話框，並定位在目前畫面的中央
     * param string obj_id 對話框ID
     * return void
     */
    function init_position(obj_id){
        var obj = document.getElementById(obj_id);
        // 對話框左邊對齊 = [捲動的左座標] + [該 Frame 的寬度] - [對話框寬度(500)] 再左移 250 個 pixel
        obj.style.left  = document.body.scrollLeft + document.body.offsetWidth - 750;
        // 對話框上緣對齊 = [捲動的上座標] 下移 10 個 pixel
        obj.style.top   = document.body.scrollTop  + 70;
        obj.style.display = '';
    }

    /**
     * 全選或全消
     * param element obj 按到的是哪個按鈕
     * return void
     */
    function pick(obj)
    {
        isAllSelect = !isAllSelect;
        for(var i=3; i<procTableObj.rows.length-1; i++) procTableObj.rows[i].cells[0].firstChild.checked = isAllSelect;
        obj.value = isAllSelect ? msg2 : msg3;    // 改變按鍵文字

        // 改變另一個按鍵文字
        if (obj.parentNode.parentNode.rowIndex == 1)
            procTableObj.rows[procTableObj.rows.length-1].cells[0].firstChild.value = obj.value;
        else
            procTableObj.rows[1].cells[0].firstChild.value = obj.value;
    }

    /**
     * 當 checkbox 點下時，偵測是否已全選或全消
     * param bool isChecked 點擊的那個 checkbox 是選取還是取消
     * return void
     */
    function detect_select(isChecked)
    {
        var sum;
        if (isChecked)
        {
            sum = true;
            for(var i=3; i<procTableObj.rows.length-1; i++) sum &= procTableObj.rows[i].cells[0].firstChild.checked;
            if (sum)
            {
                isAllSelect = false;
                pick(procTableObj.rows[1].cells[0].firstChild);    // 改變按鍵文字
            }
        }
        else
        {
            isAllSelect = false;
            procTableObj.rows[1].cells[0].firstChild.value = msg3;
            procTableObj.rows[procTableObj.rows.length-1].cells[0].firstChild.value = msg3;
        }
    }

    function check_rule(){
        if (mainForm.username.value == '' &&
            mainForm.host.value == '' &&
            mainForm.mode[0].checked)
        {
            return confirm('The rule will deny all of the world. Are you sure ?');
        }
        return true;
    }

    /**
     * HTML 初始化事件
     */
    window.onload=function()
    {
        toolbar2.innerHTML=toolbar1.innerHTML;
        procTableObj = document.getElementById('procTable');
        mainForm = document.getElementById('dialogForm');
        rm_whitespace(procTableObj);
    };

EOB;
    showXHTML_script('inline', $scr);
    showXHTML_head_E();
    showXHTML_body_B();
      $ary = array(array($MSG['login_restrict'][$sysSession->lang], 'tabsSet', ''));
      echo "<center>\n";
      showXHTML_tabFrame_B($ary, 1, 'procForm', '', 'action="ip_f_priority.php" method="POST" target="empty" style="display: inline"');
        showXHTML_table_B('id="procTable" border="0" cellpadding="3" cellspacing="1" style="border-collapse: collapse" class="box01"');
          showXHTML_tr_B('class="bg03 font01"');
            showXHTML_td('width="588" colspan="5"' , "
<img src=\"/theme/{$sysSession->theme}/academic/icon_explain.gif\" border=\"0\" align=\"absmiddle\"
onmouseover=\"this.nextSibling.style.display='';\"
onmouseout=\"this.nextSibling.style.display='none';\">{$MSG['rule_help'][$sysSession->lang]}");
            showXHTML_td_E();
          showXHTML_tr_E();
          showXHTML_tr_B('class="bg01 font01"');
            showXHTML_td_B('colspan="5" id="toolbar1"');
              showXHTML_input('button', '', $MSG['all_select'][$sysSession->lang],        '', 'class="cssBtn" onclick="pick(this);"'); echo str_repeat('&nbsp;', 3);
              showXHTML_input('button', '', $MSG['addnew'][$sysSession->lang],            '', 'class="cssBtn" onclick="addnew_init();"');
              /*Chrome版func名稱相同造成按鈕沒反應：給予新名稱*/
              showXHTML_input('button', '', $MSG['remove'][$sysSession->lang],            '', 'class="cssBtn" onclick="removeIP();"'); echo str_repeat('&nbsp;', 3);
              showXHTML_input('button', '', $MSG['mv_up'][$sysSession->lang],            '', 'class="cssBtn" onclick="moveRules(-1);"');
              showXHTML_input('button', '', $MSG['mv_dn'][$sysSession->lang],            '', 'class="cssBtn" onclick="moveRules(1);"');
              showXHTML_input('submit', '', $MSG['save_priority'][$sysSession->lang],    '', 'class="cssBtn"');
            showXHTML_td_E();
            showXHTML_td_E();
          showXHTML_tr_E();
          showXHTML_tr_B('class="bg02 font01"');
            showXHTML_td('width="32"' , $MSG['Pick'][$sysSession->lang]);
            showXHTML_td('width="100"', $MSG['username'][$sysSession->lang]);
            showXHTML_td('width="300"', $MSG['host'][$sysSession->lang]);
            showXHTML_td('width="80"',  $MSG['allow_deny'][$sysSession->lang]);
            showXHTML_td('width="40"',  $MSG['modify'][$sysSession->lang]);
            showXHTML_td_E();
          showXHTML_tr_E();
          if ($rs && $rs->RecordCount())
              while($fields = $rs->FetchRow())
              {
                    $cls = $cls == 'class="bg03 font01"' ? 'class="bg04 font01"' : 'class="bg03 font01"';
                    $key = base64_encode(gzcompress(implode(chr(9), $fields), 9));
                    showXHTML_tr_B($cls);
                    showXHTML_td_B('align="center"');
                      showXHTML_input('checkbox', '', '', '', 'onclick="detect_select(this.checked);"');
                      showXHTML_input('hidden', 'rules[]', $key);
                    showXHTML_td_E();
                    showXHTML_td('', $fields['username']);
                    showXHTML_td('', nl2br($fields['host']));
                    showXHTML_td('', $fields['mode']);
                  showXHTML_td_B();
                    showXHTML_input('button', '', $MSG['modify'][$sysSession->lang], '', 'class="cssBtn" onclick="modify_init(this.parentNode.parentNode.rowIndex);"');
                  showXHTML_td_E();
                  showXHTML_tr_E();
              }
          showXHTML_tr_B('class="bg01 font01"');
            showXHTML_td('colspan="5" id="toolbar2"');
          showXHTML_tr_E();

        showXHTML_table_E();
      showXHTML_tabFrame_E();
      echo "</center>\n";

      // 編輯對話框
      $ary = array(array($MSG['edit_rule'][$sysSession->lang], 'tabsSet', ''));
      showXHTML_tabFrame_B($ary, 1, 'dialogForm', 'dialogTable', 'action="ip_f_save.php" method="POST" style="display: inline" onsubmit="return check_rule();"', true);
        showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="500" style="border-collapse: collapse" class="box01"');

          showXHTML_tr_B('class="bg03 font01"');
            showXHTML_td('',  $MSG['username'][$sysSession->lang]);
            showXHTML_td_B();
              showXHTML_input('text', 'username', '', '', 'class="box02" maxlength="32"');
            showXHTML_td_E();
            showXHTML_td('', $MSG['username_hint'][$sysSession->lang]);
          showXHTML_tr_E();

          showXHTML_tr_B('class="bg04 font01"');
            showXHTML_td('', $MSG['host'][$sysSession->lang]);
            showXHTML_td_B();
              showXHTML_input('textarea', 'host', '', '', 'rows="4" cols="30" maxlength="255" class="box02"');
            showXHTML_td_E();
            showXHTML_td('', $MSG['host_hint'][$sysSession->lang]);
          showXHTML_tr_E();

          showXHTML_tr_B('class="bg03 font01"');
            showXHTML_td('', $MSG['allow_deny'][$sysSession->lang]);
            showXHTML_td_B();
              showXHTML_input('radio', 'mode', array('deny' => $MSG['deny_from'][$sysSession->lang], 'allow' => $MSG['allow_from'][$sysSession->lang]), 'deny', '', '&nbsp;&nbsp;&nbsp;');
            showXHTML_td_E();
            showXHTML_td('', $MSG['mode_hint'][$sysSession->lang]);
          showXHTML_tr_E();

          showXHTML_tr_B('class="bg04 font01"');
            showXHTML_td_B('colspan="3" align="center"');
              showXHTML_input('submit', '', $MSG['submit'][$sysSession->lang], '', 'class="cssBtn"');
              showXHTML_input('button', '', $MSG['reset'][$sysSession->lang],  '', 'class="cssBtn" onclick="xx=mainForm.rule_id.value; mainForm.reset(); mainForm.rule_id.value=xx;"');
              showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="hid_dialog(\'dialogTable\');"');
              showXHTML_input('hidden', 'rule_id');
            showXHTML_td_E();
          showXHTML_tr_E();

        showXHTML_table_E();
      showXHTML_tabFrame_E();

    echo <<< EOB
<form id="rmForm" name="rmForm" action="ip_f_remove.php" method="POST" style="display: none">
  <input type="hidden" name="lists" value="">
</form>

EOB;

    showXHTML_body_E();
?>
